<?php

namespace App\Services\Chat;

use App\Events\Chat\ChatActivity;
use App\Events\Chat\ConversationRead;
use App\Events\Chat\MessageChanged;
use App\Events\Chat\MessageSent;
use App\Models\ChatAttachment;
use App\Models\ChatConversation;
use App\Models\ChatMember;
use App\Models\ChatMessage;
use App\Models\ChatReaction;
use App\Models\User;
use App\Support\EncryptedFileStore;
use App\Support\HtmlSanitizer;
use Illuminate\Http\UploadedFile;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\RateLimiter;
use Illuminate\Support\Str;

/**
 * Every chat change goes through here: it checks membership, persists, and broadcasts.
 */
class ChatService
{
    public const MAX_BODY_LENGTH = 10000;

    public function __construct(protected EncryptedFileStore $files, protected MessageRenderer $renderer) {}

    // ---------------------------------------------------------------- rooms

    public function openDirect(User $user, User $other): ChatConversation
    {
        if ($user->id === $other->id) {
            throw new ChatActionException('You cannot start a conversation with yourself.');
        }

        $key = ChatConversation::directKey($user->id, $other->id);

        return DB::transaction(function () use ($user, $other, $key) {
            $conversation = ChatConversation::where('direct_key', $key)->lockForUpdate()->first();
            if ($conversation) {
                return $conversation;
            }

            $conversation = ChatConversation::create([
                'type' => 'direct',
                'direct_key' => $key,
                'created_by' => $user->id,
                'status' => 'active',
            ]);

            foreach ([$user->id, $other->id] as $id) {
                // last_read_at stays null: the first message, even in the same second, counts as unread
                ChatMember::create(['conversation_id' => $conversation->id, 'user_id' => $id, 'role' => 'member']);
            }

            return $conversation;
        });
    }

    /**
     * @param  array<int, string>  $userIds
     */
    public function createGroup(User $creator, string $name, ?string $topic, array $userIds): ChatConversation
    {
        $name = trim($name);
        if ($name === '') {
            throw new ChatActionException('Give the group a name.');
        }

        $userIds = $this->validUserIds($userIds, exclude: $creator->id);
        $this->guardCap(count($userIds) + 1);

        return DB::transaction(function () use ($creator, $name, $topic, $userIds) {
            $conversation = ChatConversation::create([
                'type' => 'group',
                'name' => Str::limit($name, 120, ''),
                'topic' => $topic ? Str::limit(trim($topic), 500, '') : null,
                'created_by' => $creator->id,
                'status' => 'active',
                'last_message_at' => now(),
            ]);

            ChatMember::create(['conversation_id' => $conversation->id, 'user_id' => $creator->id, 'role' => 'owner']);
            foreach ($userIds as $id) {
                ChatMember::create(['conversation_id' => $conversation->id, 'user_id' => $id, 'role' => 'member']);
            }

            $this->system($conversation, $creator->full_name.' created the group');
            $this->notifyMembership($userIds, $conversation);

            return $conversation;
        });
    }

    /**
     * @param  array<int, string>  $userIds
     */
    public function addMembers(ChatConversation $conversation, User $actor, array $userIds): void
    {
        $this->guardManageMembers($actor, $conversation);

        $existing = $conversation->members()->pluck('user_id')->all();
        $new = array_values(array_diff($this->validUserIds($userIds), $existing));
        if ($new === []) {
            return;
        }

        $this->guardCap(count($existing) + count($new));

        DB::transaction(function () use ($conversation, $actor, $new) {
            foreach ($new as $id) {
                ChatMember::create(['conversation_id' => $conversation->id, 'user_id' => $id, 'role' => 'member']);
            }

            $names = User::whereIn('id', $new)->get()->map->full_name->implode(', ');
            $this->system($conversation, $actor->full_name.' added '.$names);
        });

        $this->notifyMembership($new, $conversation);
    }

    public function removeMember(ChatConversation $conversation, User $actor, string $userId): void
    {
        $this->guardManageMembers($actor, $conversation);

        $member = $conversation->members()->where('user_id', $userId)->first();
        if (! $member) {
            return;
        }
        if ($member->role === 'owner') {
            throw new ChatActionException('The group owner cannot be removed.');
        }

        $member->delete();
        $this->system($conversation, $actor->full_name.' removed '.($member->user?->full_name ?? 'a member'));
        $this->notifyMembership([$userId], $conversation);
    }

    public function setRole(ChatConversation $conversation, User $actor, string $userId, string $role): void
    {
        $this->guardManageMembers($actor, $conversation);
        if (! in_array($role, ['admin', 'member'], true)) {
            throw new ChatActionException('Invalid role.');
        }

        $conversation->members()->where('user_id', $userId)->where('role', '!=', 'owner')->update(['role' => $role]);
    }

    public function leave(ChatConversation $conversation, User $user): void
    {
        if (! $conversation->isGroup()) {
            throw new ChatActionException('You can only leave group chats.');
        }

        $member = ChatAccess::membership($user, $conversation);
        if (! $member) {
            return;
        }

        DB::transaction(function () use ($conversation, $user, $member) {
            $member->delete();

            // Hand ownership to the longest-standing admin, else member
            if ($member->role === 'owner') {
                $successor = $conversation->members()->orderByRaw("CASE role WHEN 'admin' THEN 0 ELSE 1 END")->orderBy('joined_at')->first();
                $successor?->update(['role' => 'owner']);
            }

            $this->system($conversation, $user->full_name.' left the group');
        });

        $this->notifyMembership([$user->id], $conversation);
    }

    public function updateDetails(ChatConversation $conversation, User $actor, ?string $name, ?string $topic): void
    {
        if (! ChatAccess::canModerate($actor, $conversation)) {
            throw new ChatActionException('Only room admins can change the details.');
        }

        $changes = ['topic' => $topic ? Str::limit(trim($topic), 500, '') : null];
        if ($conversation->isGroup() && $name !== null && trim($name) !== '') {
            $changes['name'] = Str::limit(trim($name), 120, '');
        }

        $conversation->update($changes);
        $this->system($conversation, $actor->full_name.' updated the room details');
    }

    public function setArchived(ChatConversation $conversation, User $actor, bool $archived): void
    {
        if (! ChatAccess::canModerate($actor, $conversation)) {
            throw new ChatActionException('Only room admins can archive this room.');
        }

        $conversation->update(['status' => $archived ? 'archived' : 'active']);
        $this->system($conversation, $actor->full_name.($archived ? ' archived the room' : ' reopened the room'));
    }

    /** Personal settings: mute notifications / pin the room to the top. */
    public function setPreference(ChatConversation $conversation, User $user, string $key, bool $on): void
    {
        if (! in_array($key, ['muted', 'pinned'], true)) {
            throw new ChatActionException('Unknown preference.');
        }

        ChatAccess::membership($user, $conversation)?->update([$key => $on ? 'yes' : 'no']);
    }

    // ---------------------------------------------------------------- messages

    /**
     * @param  array<int, UploadedFile>  $files
     */
    public function send(ChatConversation $conversation, User $sender, ?string $body, array $files = [], ?string $replyToId = null): ChatMessage
    {
        if (! ChatAccess::canPost($sender, $conversation)) {
            throw new ChatActionException($conversation->status === 'archived' ? 'This room is archived and read-only.' : 'You are not a member of this room.');
        }

        $body = trim((string) $body);
        if ($body === '' && $files === []) {
            throw new ChatActionException('The message is empty.');
        }
        if (mb_strlen($body) > self::MAX_BODY_LENGTH) {
            throw new ChatActionException('Messages are limited to '.number_format(self::MAX_BODY_LENGTH).' characters.');
        }

        $limiterKey = 'chat-send:'.$sender->id;
        if (RateLimiter::tooManyAttempts($limiterKey, 40)) {
            throw new ChatActionException('You are sending messages too quickly. Please wait a moment.');
        }
        RateLimiter::hit($limiterKey, 60);

        if ($replyToId && ! ChatMessage::withTrashed()->where('id', $replyToId)->where('conversation_id', $conversation->id)->exists()) {
            $replyToId = null;
        }

        $message = DB::transaction(function () use ($conversation, $sender, $body, $files, $replyToId) {
            $message = ChatMessage::create([
                'conversation_id' => $conversation->id,
                'user_id' => $sender->id,
                'body' => $body,
                'type' => 'text',
                'reply_to_id' => $replyToId,
                'ticket_ref_count' => MessageRenderer::countTicketRefs($body),
            ]);

            foreach ($files as $file) {
                [$width, $height] = str_starts_with((string) $file->getMimeType(), 'image/')
                    ? (@getimagesize($file->getRealPath()) ?: [null, null])
                    : [null, null];

                ChatAttachment::create([
                    'message_id' => $message->id,
                    'path' => $this->files->put($file, 'chat/'.$conversation->id),
                    'original_name' => Str::limit($file->getClientOriginalName(), 250, ''),
                    'mime_type' => $file->getMimeType(),
                    'size' => $file->getSize(),
                    'width' => $width,
                    'height' => $height,
                ]);
            }

            $conversation->update(['last_message_at' => $message->created_at]);
            ChatMember::where('conversation_id', $conversation->id)->where('user_id', $sender->id)->update(['last_read_at' => $message->created_at]);

            return $message;
        });

        $this->emit(new MessageSent($message));
        $this->notifyMessage($conversation, $message, $sender, $files !== []);

        return $message;
    }

    public function edit(ChatMessage $message, User $user, string $body): ChatMessage
    {
        $this->guardOwnRecent($message, $user, 'edit');

        $body = trim($body);
        if ($body === '' && $message->attachments()->doesntExist()) {
            throw new ChatActionException('The message is empty. Delete it instead.');
        }
        if (mb_strlen($body) > self::MAX_BODY_LENGTH) {
            throw new ChatActionException('Messages are limited to '.number_format(self::MAX_BODY_LENGTH).' characters.');
        }

        if ($body !== (string) $message->body) {
            $message->update([
                'body' => $body,
                'edited_at' => now(),
                'ticket_ref_count' => MessageRenderer::countTicketRefs($body),
            ]);
            $this->emit(new MessageChanged($message->conversation_id, $message->id, 'edited'));
        }

        return $message;
    }

    /** Soft delete: the content stays for retention/audit, everyone sees "message deleted". */
    public function delete(ChatMessage $message, User $user): void
    {
        $this->guardOwnRecent($message, $user, 'delete');

        $message->delete();
        $this->emit(new MessageChanged($message->conversation_id, $message->id, 'deleted'));
    }

    public function toggleReaction(ChatMessage $message, User $user, string $emoji): void
    {
        if (! ChatAccess::isMember($user, $message->conversation_id) || $message->trashed()) {
            throw new ChatActionException('You cannot react to this message.');
        }

        $emoji = trim($emoji);
        if ($emoji === '' || mb_strlen($emoji) > 8) {
            throw new ChatActionException('Invalid reaction.');
        }

        $existing = ChatReaction::where(['message_id' => $message->id, 'user_id' => $user->id, 'emoji' => $emoji])->first();
        $existing ? $existing->delete() : ChatReaction::create(['message_id' => $message->id, 'user_id' => $user->id, 'emoji' => $emoji]);

        $this->emit(new MessageChanged($message->conversation_id, $message->id, 'reaction'));
    }

    public function togglePin(ChatMessage $message, User $user): void
    {
        if (! ChatAccess::canModerate($user, $message->conversation) || $message->trashed()) {
            throw new ChatActionException('Only room admins can pin messages.');
        }

        $message->update($message->pinned_at
            ? ['pinned_at' => null, 'pinned_by' => null]
            : ['pinned_at' => now(), 'pinned_by' => $user->id]);

        $this->emit(new MessageChanged($message->conversation_id, $message->id, 'pinned'));
    }

    public function markRead(ChatConversation $conversation, User $user): void
    {
        $member = ChatAccess::membership($user, $conversation);
        if (! $member) {
            return;
        }

        // Skip the write (and broadcast) when nothing new arrived since the last read
        if ($member->last_read_at && $conversation->last_message_at && $member->last_read_at->gte($conversation->last_message_at)) {
            return;
        }

        // Read up to the newest message (not "now"), so one arriving a moment later still counts as unread
        $readAt = $conversation->last_message_at ?? now();
        $member->update(['last_read_at' => $readAt]);
        $this->emit(new ConversationRead($conversation->id, $user->id, $readAt->toIso8601String()));
    }

    public function attachmentContents(ChatAttachment $attachment): string
    {
        return $this->files->get($attachment->path);
    }

    // ---------------------------------------------------------------- helpers

    /** Posts an automatic "X added Y" style line. */
    public function system(ChatConversation $conversation, string $text): ChatMessage
    {
        $message = ChatMessage::create([
            'conversation_id' => $conversation->id,
            'user_id' => null,
            'body' => $text,
            'type' => 'system',
        ]);
        $conversation->update(['last_message_at' => $message->created_at]);

        $this->emit(new MessageSent($message));

        return $message;
    }

    protected function notifyMessage(ChatConversation $conversation, ChatMessage $message, User $sender, bool $hasFiles): void
    {
        $members = ChatMember::where('conversation_id', $conversation->id)
            ->where('user_id', '!=', $sender->id)
            ->get(['user_id', 'muted']);

        $preview = $message->body !== ''
            ? HtmlSanitizer::toText($this->renderer->toHtml((string) $message->body), 120)
            : ($hasFiles ? '📎 Sent an attachment' : '');

        $title = $conversation->isDirect() ? $sender->full_name : ($conversation->name ?? 'Chat');

        foreach (['no' => false, 'yes' => true] as $muted => $silent) {
            $ids = $members->where('muted', $muted)->pluck('user_id')->all();
            foreach (array_chunk($ids, 100) as $chunk) {
                $this->emit(new ChatActivity($chunk, 'message', $conversation->id, $silent, $title, $preview, $sender->full_name, $sender->profile_picture_url));
            }
        }
    }

    /**
     * @param  array<int, string>  $userIds
     */
    protected function notifyMembership(array $userIds, ChatConversation $conversation): void
    {
        foreach (array_chunk($userIds, 100) as $chunk) {
            $this->emit(new ChatActivity($chunk, 'membership', $conversation->id, true));
        }
    }

    protected function guardOwnRecent(ChatMessage $message, User $user, string $action): void
    {
        if ($message->user_id !== $user->id) {
            throw new ChatActionException("You can only {$action} your own messages.");
        }

        if (! $message->isEditable()) {
            throw new ChatActionException('Messages can only be '.($action === 'edit' ? 'edited' : 'deleted').' within '.config('chat.edit_window_minutes').' minutes of sending.');
        }
    }

    /**
     * Broadcast after the surrounding transaction commits. A Reverb outage must never lose
     * the message itself, so failures are reported and swallowed.
     */
    protected function emit(object $event): void
    {
        DB::afterCommit(function () use ($event) {
            try {
                event($event);
            } catch (\Throwable $e) {
                report($e);
            }
        });
    }

    protected function guardManageMembers(User $actor, ChatConversation $conversation): void
    {
        if (! ChatAccess::canManageMembers($actor, $conversation)) {
            throw new ChatActionException($conversation->isDepartment()
                ? 'Department channel members follow HR records and cannot be changed here.'
                : 'Only group admins can change members.');
        }
    }

    protected function guardCap(int $total): void
    {
        if ($total > config('chat.group_member_cap')) {
            throw new ChatActionException('Groups are limited to '.config('chat.group_member_cap').' members.');
        }
    }

    /**
     * @param  array<int, string>  $userIds
     * @return array<int, string>
     */
    protected function validUserIds(array $userIds, ?string $exclude = null): array
    {
        $ids = array_values(array_unique(array_filter($userIds)));

        return User::whereIn('id', $ids)->when($exclude, fn ($q) => $q->where('id', '!=', $exclude))->pluck('id')->all();
    }
}
