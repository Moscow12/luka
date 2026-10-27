<?php

namespace App\Services\Chat;

use App\Models\ChatConversation;
use App\Models\ChatMessage;
use App\Models\User;
use Illuminate\Database\Eloquent\Collection as EloquentCollection;
use Illuminate\Support\Collection;

/**
 * The viewer's rooms with their membership settings and unread counts, in one query.
 */
class ChatInbox
{
    public function conversations(User $user): Collection
    {
        $unread = ChatMessage::query()
            ->selectRaw('count(*)')
            ->whereColumn('chat_messages.conversation_id', 'chat_conversations.id')
            ->where(fn ($q) => $q->whereNull('chat_messages.user_id')->orWhere('chat_messages.user_id', '!=', $user->id))
            ->where(fn ($q) => $q->whereNull('m.last_read_at')->orWhereColumn('chat_messages.created_at', '>', 'm.last_read_at'));

        $conversations = ChatConversation::query()
            ->select('chat_conversations.*', 'm.last_read_at as my_last_read_at', 'm.muted as my_muted', 'm.pinned as my_pinned', 'm.role as my_role')
            ->selectSub($unread, 'unread_count')
            ->join('chat_members as m', fn ($j) => $j->on('m.conversation_id', '=', 'chat_conversations.id')->where('m.user_id', '=', $user->id))
            ->with(['latestMessage' => fn ($q) => $q->withTrashed()->with('user:id,first_name,surname')])
            ->withCount('members')
            ->orderByDesc('chat_conversations.last_message_at')
            ->orderBy('chat_conversations.name')
            ->get();

        // Only DMs need their members (to show the other person); department rooms can be large
        (new EloquentCollection($conversations->where('type', 'direct')->all()))
            ->load('members.user:id,first_name,surname,salutation,middle_name,profile_picture,last_seen_at');

        return $conversations;
    }

    /** Total unread across rooms that are not muted (for the floating badge). */
    public function totalUnread(User $user): int
    {
        return ChatMessage::query()
            ->join('chat_members as m', 'm.conversation_id', '=', 'chat_messages.conversation_id')
            ->where('m.user_id', $user->id)
            ->where('m.muted', 'no')
            ->where(fn ($q) => $q->whereNull('chat_messages.user_id')->orWhere('chat_messages.user_id', '!=', $user->id))
            ->where(fn ($q) => $q->whereNull('m.last_read_at')->orWhereColumn('chat_messages.created_at', '>', 'm.last_read_at'))
            ->count();
    }
}
