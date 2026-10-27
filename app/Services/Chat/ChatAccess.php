<?php

namespace App\Services\Chat;

use App\Models\ChatConversation;
use App\Models\ChatMember;
use App\Models\User;

/**
 * Room-level authorisation. Membership is the only way to read a room (PRD 7, access separation).
 */
class ChatAccess
{
    public static function membership(User $user, ChatConversation|string $conversation): ?ChatMember
    {
        $id = $conversation instanceof ChatConversation ? $conversation->id : $conversation;

        return ChatMember::where('conversation_id', $id)->where('user_id', $user->id)->first();
    }

    public static function isMember(User $user, ChatConversation|string $conversation): bool
    {
        return static::membership($user, $conversation) !== null;
    }

    /** Owners/admins of a group, supervisors (admins) of a department channel, or chat administrators. */
    public static function canModerate(User $user, ChatConversation $conversation): bool
    {
        if ($conversation->isDirect()) {
            return false;
        }

        return (bool) static::membership($user, $conversation)?->isManager() || $user->can('manage-chat');
    }

    /** Adding/removing people is only for ad-hoc groups; department rosters follow HR records. */
    public static function canManageMembers(User $user, ChatConversation $conversation): bool
    {
        return $conversation->isGroup() && static::canModerate($user, $conversation);
    }

    public static function canPost(User $user, ChatConversation $conversation): bool
    {
        return $conversation->status === 'active' && static::isMember($user, $conversation);
    }
}
