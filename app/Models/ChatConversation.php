<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Concerns\HasUuids;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\HasMany;
use Illuminate\Database\Eloquent\Relations\HasOne;

class ChatConversation extends Model
{
    use HasUuids;

    protected $fillable = [
        'type',
        'name',
        'topic',
        'department_id',
        'direct_key',
        'created_by',
        'last_message_at',
        'status',
    ];

    protected $casts = [
        'last_message_at' => 'datetime',
    ];

    public function members(): HasMany
    {
        return $this->hasMany(ChatMember::class, 'conversation_id');
    }

    public function messages(): HasMany
    {
        return $this->hasMany(ChatMessage::class, 'conversation_id');
    }

    public function latestMessage(): HasOne
    {
        return $this->hasOne(ChatMessage::class, 'conversation_id')->latestOfMany();
    }

    public function department(): BelongsTo
    {
        return $this->belongsTo(departments::class, 'department_id');
    }

    public function creator(): BelongsTo
    {
        return $this->belongsTo(User::class, 'created_by');
    }

    public function isDirect(): bool
    {
        return $this->type === 'direct';
    }

    public function isDepartment(): bool
    {
        return $this->type === 'department';
    }

    public function isGroup(): bool
    {
        return $this->type === 'group';
    }

    public static function directKey(string $userA, string $userB): string
    {
        $ids = [$userA, $userB];
        sort($ids);

        return implode(':', $ids);
    }

    /** For a DM, the other person; null otherwise. */
    public function otherUser(string $userId): ?User
    {
        if (! $this->isDirect()) {
            return null;
        }

        $member = $this->relationLoaded('members')
            ? $this->members->firstWhere(fn (ChatMember $m) => $m->user_id !== $userId)
            : $this->members()->where('user_id', '!=', $userId)->with('user')->first();

        return $member?->user;
    }

    /** Display name from the viewer's point of view. */
    public function displayName(string $viewerId): string
    {
        if ($this->isDirect()) {
            return $this->otherUser($viewerId)?->full_name ?: 'Former member';
        }

        return $this->name ?: 'Untitled';
    }
}
