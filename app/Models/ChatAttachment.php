<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Concerns\HasUuids;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class ChatAttachment extends Model
{
    use HasUuids;

    protected $fillable = ['message_id', 'path', 'original_name', 'mime_type', 'size', 'width', 'height'];

    public function message(): BelongsTo
    {
        return $this->belongsTo(ChatMessage::class, 'message_id')->withTrashed();
    }

    public function isImage(): bool
    {
        return in_array($this->mime_type, ['image/png', 'image/jpeg', 'image/gif', 'image/webp'], true);
    }

    public function humanSize(): string
    {
        $size = (int) $this->size;

        return $size >= 1048576
            ? round($size / 1048576, 1).' MB'
            : max(1, round($size / 1024)).' KB';
    }

    public function extension(): string
    {
        return strtoupper(pathinfo($this->original_name, PATHINFO_EXTENSION) ?: 'file');
    }
}
