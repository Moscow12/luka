<?php

namespace App\Events\Chat;

use Illuminate\Broadcasting\InteractsWithSockets;
use Illuminate\Broadcasting\PrivateChannel;
use Illuminate\Contracts\Broadcasting\ShouldBroadcastNow;
use Illuminate\Foundation\Events\Dispatchable;

/**
 * Personal nudge to members' own channels: refresh unread badges and, unless silent, show a toast.
 * kind: message | membership
 */
class ChatActivity implements ShouldBroadcastNow
{
    use Dispatchable, InteractsWithSockets;

    /**
     * @param  array<int, string>  $userIds
     */
    public function __construct(
        public array $userIds,
        public string $kind,
        public string $conversationId,
        public bool $silent = false,
        public ?string $title = null,
        public ?string $preview = null,
        public ?string $senderName = null,
        public ?string $senderAvatar = null,
    ) {}

    public function broadcastOn(): array
    {
        return array_map(fn ($id) => new PrivateChannel('chat.user.'.$id), array_values($this->userIds));
    }

    public function broadcastAs(): string
    {
        return 'chat.activity';
    }

    public function broadcastWith(): array
    {
        return [
            'kind' => $this->kind,
            'conversation_id' => $this->conversationId,
            'silent' => $this->silent,
            'title' => $this->title,
            'preview' => $this->preview,
            'sender_name' => $this->senderName,
            'sender_avatar' => $this->senderAvatar,
        ];
    }
}
