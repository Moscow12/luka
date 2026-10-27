<?php

use App\Models\User;
use App\Services\Chat\ChatAccess;
use Illuminate\Support\Facades\Broadcast;

// Personal chat events: unread badges, toasts, membership changes, incoming calls
Broadcast::channel('chat.user.{userId}', function (User $user, string $userId) {
    return $user->id === $userId;
});

// One room's live stream. Only members may listen (PRD 7, access separation)
Broadcast::channel('chat.conversation.{conversationId}', function (User $user, string $conversationId) {
    return ChatAccess::isMember($user, $conversationId);
});

// Who is online right now
Broadcast::channel('chat.online', function (User $user) {
    return [
        'id' => $user->id,
        'name' => $user->full_name,
        'avatar' => $user->profile_picture_url,
    ];
});
