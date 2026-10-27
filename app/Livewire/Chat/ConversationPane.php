<?php

namespace App\Livewire\Chat;

use App\Models\ChatAttachment;
use App\Models\ChatConversation;
use App\Models\ChatMember;
use App\Models\ChatMessage;
use App\Services\Chat\ChatAccess;
use App\Services\Chat\ChatActionException;
use App\Services\Chat\ChatService;
use App\Services\Chat\EmployeeDirectory;
use App\Support\EncryptedFileStore;
use Illuminate\Support\Facades\Auth;
use Livewire\Attributes\Locked;
use Livewire\Attributes\On;
use Livewire\Component;
use Livewire\WithFileUploads;

/**
 * One open conversation: the live message stream, composer and room details.
 * Used by both the floating dock (compact) and the full workspace.
 */
class ConversationPane extends Component
{
    use WithFileUploads;

    #[Locked]
    public string $conversationId;

    #[Locked]
    public string $userId;

    public bool $compact = false;

    public int $limit = 50;

    public bool $hasMore = false;

    // Read position when the pane opened: drives the "New messages" divider
    #[Locked]
    public ?string $readMarker = null;

    public $uploads = [];

    public $newUploads = [];

    public ?string $replyToId = null;

    public ?string $editingId = null;

    public string $editBody = '';

    public bool $showDetails = false;

    public string $detailsTab = 'members';

    public string $editName = '';

    public string $editTopic = '';

    public string $memberSearch = '';

    public ?string $notice = null;

    public function mount(string $conversationId, bool $compact = false)
    {
        $user = Auth::user();
        $member = ChatAccess::membership($user, $conversationId);
        abort_unless($member, 403);

        $this->conversationId = $conversationId;
        $this->userId = $user->id;
        $this->compact = $compact;
        $this->limit = config('chat.page_size');
        $this->readMarker = $member->last_read_at?->toIso8601String();

        app(ChatService::class)->markRead($this->conversation(), $user);
        $this->dispatch('chat-read');
    }

    protected function conversation(): ChatConversation
    {
        return ChatConversation::findOrFail($this->conversationId);
    }

    protected function message(string $id): ChatMessage
    {
        return ChatMessage::withTrashed()->where('conversation_id', $this->conversationId)->findOrFail($id);
    }

    /** Re-check membership on every request: removal from a room revokes access immediately. */
    protected function guard(): bool
    {
        if (ChatAccess::isMember(Auth::user(), $this->conversationId)) {
            return true;
        }

        $this->dispatch('chat-left', id: $this->conversationId);

        return false;
    }

    // ---------------------------------------------------------------- live updates

    #[On('echo-private:chat.conversation.{conversationId},.message.sent')]
    public function onMessageSent(): void
    {
        // Re-render picks up the new message
    }

    #[On('echo-private:chat.conversation.{conversationId},.message.changed')]
    public function onMessageChanged(): void {}

    #[On('echo-private:chat.conversation.{conversationId},.conversation.read')]
    public function onConversationRead(): void {}

    // ---------------------------------------------------------------- messages

    public function send(string $body = ''): array
    {
        if (! $this->guard()) {
            return ['error' => 'You are no longer a member of this room.'];
        }

        $this->validate(['uploads' => ['array', 'max:10'], 'uploads.*' => EncryptedFileStore::UPLOAD_RULES]);

        try {
            app(ChatService::class)->send($this->conversation(), Auth::user(), $body, $this->uploads, $this->replyToId);
        } catch (ChatActionException $e) {
            return ['error' => $e->getMessage()];
        }

        $this->reset(['uploads', 'replyToId']);
        $this->readMarker = now()->toIso8601String();

        return ['ok' => true];
    }

    public function updatedNewUploads(): void
    {
        $this->validate(['newUploads.*' => EncryptedFileStore::UPLOAD_RULES]);

        $this->uploads = array_slice(array_merge($this->uploads, $this->newUploads), 0, 10);
        $this->newUploads = [];
    }

    public function removeUpload(int $index): void
    {
        unset($this->uploads[$index]);
        $this->uploads = array_values($this->uploads);
    }

    public function loadOlder(): void
    {
        $this->limit += config('chat.page_size');
    }

    public function markRead(): void
    {
        if ($this->guard()) {
            app(ChatService::class)->markRead($this->conversation(), Auth::user());
            $this->dispatch('chat-read');
        }
    }

    public function startEdit(string $messageId): void
    {
        $message = $this->message($messageId);
        if ($message->user_id !== Auth::id() || ! $message->isEditable()) {
            $this->notice = 'Messages can only be edited within '.config('chat.edit_window_minutes').' minutes of sending.';

            return;
        }

        $this->editingId = $message->id;
        $this->editBody = (string) $message->body;
    }

    public function saveEdit(): void
    {
        if (! $this->editingId) {
            return;
        }

        $result = $this->attempt(fn (ChatService $chat) => $chat->edit($this->message($this->editingId), Auth::user(), $this->editBody));
        if (isset($result['ok'])) {
            $this->cancelEdit();
        }
    }

    public function cancelEdit(): void
    {
        $this->reset(['editingId', 'editBody']);
    }

    public function deleteMessage(string $messageId): array
    {
        return $this->attempt(fn (ChatService $chat) => $chat->delete($this->message($messageId), Auth::user()));
    }

    public function react(string $messageId, string $emoji): array
    {
        return $this->attempt(fn (ChatService $chat) => $chat->toggleReaction($this->message($messageId), Auth::user(), $emoji));
    }

    public function togglePin(string $messageId): array
    {
        return $this->attempt(fn (ChatService $chat) => $chat->togglePin($this->message($messageId), Auth::user()));
    }

    public function setReply(string $messageId): void
    {
        $this->replyToId = $this->message($messageId)->id;
    }

    public function cancelReply(): void
    {
        $this->replyToId = null;
    }

    // ---------------------------------------------------------------- room settings

    public function toggleDetails(?string $tab = null): void
    {
        if ($tab && $this->showDetails && $this->detailsTab !== $tab) {
            $this->detailsTab = $tab;

            return;
        }

        $this->showDetails = ! $this->showDetails;
        $this->detailsTab = $tab ?? $this->detailsTab;

        $conversation = $this->conversation();
        $this->editName = (string) $conversation->name;
        $this->editTopic = (string) $conversation->topic;
    }

    public function saveDetails(): array
    {
        $this->validate(['editName' => ['nullable', 'string', 'max:120'], 'editTopic' => ['nullable', 'string', 'max:500']]);

        return $this->attempt(fn (ChatService $chat) => $chat->updateDetails($this->conversation(), Auth::user(), $this->editName, $this->editTopic), 'Room details saved.');
    }

    public function togglePreference(string $key): array
    {
        $member = ChatAccess::membership(Auth::user(), $this->conversationId);

        return $this->attempt(fn (ChatService $chat) => $chat->setPreference($this->conversation(), Auth::user(), $key, $member?->{$key} !== 'yes'));
    }

    public function toggleArchive(): array
    {
        $conversation = $this->conversation();

        return $this->attempt(fn (ChatService $chat) => $chat->setArchived($conversation, Auth::user(), $conversation->status === 'active'));
    }

    public function addMember(string $userId): array
    {
        $this->memberSearch = '';

        return $this->attempt(fn (ChatService $chat) => $chat->addMembers($this->conversation(), Auth::user(), [$userId]));
    }

    public function removeMember(string $userId): array
    {
        return $this->attempt(fn (ChatService $chat) => $chat->removeMember($this->conversation(), Auth::user(), $userId));
    }

    public function setRole(string $userId, string $role): array
    {
        return $this->attempt(fn (ChatService $chat) => $chat->setRole($this->conversation(), Auth::user(), $userId, $role));
    }

    public function leave(): void
    {
        try {
            app(ChatService::class)->leave($this->conversation(), Auth::user());
            $this->dispatch('chat-left', id: $this->conversationId);
        } catch (ChatActionException $e) {
            $this->notice = $e->getMessage();
        }
    }

    /**
     * Runs a service call, turning refusals into a notice the pane shows.
     */
    protected function attempt(callable $action, ?string $success = null): array
    {
        if (! $this->guard()) {
            return ['error' => 'You are no longer a member of this room.'];
        }

        try {
            $action(app(ChatService::class));
            $this->notice = $success;

            return ['ok' => true];
        } catch (ChatActionException $e) {
            $this->notice = $e->getMessage();

            return ['error' => $e->getMessage()];
        }
    }

    // ---------------------------------------------------------------- render

    public function render()
    {
        $user = Auth::user();
        $conversation = ChatConversation::withCount('members')->findOrFail($this->conversationId);
        $myMembership = ChatMember::where('conversation_id', $conversation->id)->where('user_id', $user->id)->first();

        if (! $myMembership) {
            return view('livewire.chat.removed');
        }

        $messages = ChatMessage::withTrashed()
            ->where('conversation_id', $conversation->id)
            ->with(['user:id,first_name,surname,salutation,middle_name,profile_picture', 'attachments', 'reactions:id,message_id,user_id,emoji', 'replyTo.user:id,first_name,surname'])
            ->latest()
            ->limit($this->limit + 1)
            ->get();

        $this->hasMore = $messages->count() > $this->limit;
        $messages = $messages->take($this->limit)->reverse()->values();

        // Read receipts: everyone else's read position
        $readPositions = ChatMember::where('conversation_id', $conversation->id)
            ->where('user_id', '!=', $user->id)
            ->pluck('last_read_at')
            ->filter();

        $other = $conversation->isDirect() ? $conversation->otherUser($user->id) : null;
        $canModerate = ChatAccess::canModerate($user, $conversation);

        $details = $this->showDetails ? $this->detailsData($conversation) : [];

        return view('livewire.chat.conversation-pane', [
            'conversation' => $conversation,
            'me' => $myMembership,
            'messages' => $messages,
            'readPositions' => $readPositions,
            'other' => $other,
            'canModerate' => $canModerate,
            'canManageMembers' => ChatAccess::canManageMembers($user, $conversation),
            'canPost' => $conversation->status === 'active',
            'replyTo' => $this->replyToId ? ChatMessage::withTrashed()->with('user:id,first_name,surname')->find($this->replyToId) : null,
            'editWindow' => config('chat.edit_window_minutes'),
        ] + $details);
    }

    protected function detailsData(ChatConversation $conversation): array
    {
        $data = [];

        if ($this->detailsTab === 'members') {
            $data['members'] = ChatMember::where('conversation_id', $conversation->id)
                ->with('user:id,first_name,surname,salutation,middle_name,profile_picture,last_seen_at')
                ->orderByRaw("CASE role WHEN 'owner' THEN 0 WHEN 'admin' THEN 1 ELSE 2 END")
                ->limit(200)
                ->get();

            $data['memberCandidates'] = mb_strlen(trim($this->memberSearch)) >= config('chat.search_min_chars')
                ? app(EmployeeDirectory::class)->search($this->memberSearch, Auth::user())['people']
                    ->reject(fn ($p) => $data['members']->contains('user_id', $p['user_id']))
                : collect();
        }

        if ($this->detailsTab === 'pinned') {
            $data['pinned'] = ChatMessage::where('conversation_id', $conversation->id)
                ->whereNotNull('pinned_at')
                ->with('user:id,first_name,surname')
                ->latest('pinned_at')
                ->limit(50)
                ->get();
        }

        if ($this->detailsTab === 'files') {
            $data['files'] = ChatAttachment::whereHas('message', fn ($q) => $q->where('conversation_id', $conversation->id)->whereNull('deleted_at'))
                ->latest()
                ->limit(60)
                ->get();
        }

        if ($this->detailsTab === 'tickets') {
            // Ticket numbers mentioned recently (bodies are encrypted, so this is done in PHP)
            $numbers = ChatMessage::where('conversation_id', $conversation->id)
                ->where('ticket_ref_count', '>', 0)
                ->latest()
                ->limit(300)
                ->get(['id', 'body'])
                ->flatMap(fn ($m) => preg_match_all('/\bHRP-\d{4}-\d{5}\b/', (string) $m->body, $match) ? $match[0] : [])
                ->unique()
                ->take(30);

            $data['tickets'] = \App\Models\Ticket::whereIn('ticket_number', $numbers)->get()
                ->filter(fn ($t) => \App\Services\Tickets\TicketAccess::canView(Auth::user(), $t));
        }

        return $data;
    }

    public static function readersOf(ChatMessage $message, $readPositions): int
    {
        return $readPositions->filter(fn ($at) => $at->gte($message->created_at))->count();
    }
}
