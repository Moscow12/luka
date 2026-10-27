<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Concerns\HasUuids;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\HasMany;
use Illuminate\Database\Eloquent\SoftDeletes;

class ChatMessage extends Model
{
    use HasUuids, SoftDeletes;

    protected $fillable = [
        'conversation_id',
        'user_id',
        'body',
        'type',
        'reply_to_id',
        'ticket_ref_count',
        'edited_at',
        'pinned_at',
        'pinned_by',
    ];

    protected $casts = [
        'body' => 'encrypted',
        'edited_at' => 'datetime',
        'pinned_at' => 'datetime',
        'ticket_ref_count' => 'integer',
    ];

    public function conversation(): BelongsTo
    {
        return $this->belongsTo(ChatConversation::class, 'conversation_id');
    }

    public function user(): BelongsTo
    {
        return $this->belongsTo(User::class);
    }

    public function replyTo(): BelongsTo
    {
        return $this->belongsTo(ChatMessage::class, 'reply_to_id')->withTrashed();
    }

    public function attachments(): HasMany
    {
        return $this->hasMany(ChatAttachment::class, 'message_id');
    }

    public function reactions(): HasMany
    {
        return $this->hasMany(ChatReaction::class, 'message_id');
    }

    /** Still inside the edit/delete window. */
    public function isEditable(): bool
    {
        return $this->type === 'text'
            && ! $this->trashed()
            && $this->created_at->gt(now()->subMinutes(config('chat.edit_window_minutes')));
    }
}
