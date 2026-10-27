<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Concerns\HasUuids;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class ChatMember extends Model
{
    use HasUuids;

    protected $fillable = ['conversation_id', 'user_id', 'role', 'last_read_at', 'muted', 'pinned', 'joined_at'];

    protected $casts = [
        'last_read_at' => 'datetime',
        'joined_at' => 'datetime',
    ];

    public function conversation(): BelongsTo
    {
        return $this->belongsTo(ChatConversation::class, 'conversation_id');
    }

    public function user(): BelongsTo
    {
        return $this->belongsTo(User::class);
    }

    public function isManager(): bool
    {
        return in_array($this->role, ['owner', 'admin'], true);
    }
}
