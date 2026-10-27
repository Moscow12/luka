<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Concerns\HasUuids;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class TicketAttachment extends Model
{
    use HasUuids;

    protected $fillable = [
        'ticket_id',
        'ticket_message_id',
        'path',
        'original_name',
        'mime_type',
        'size',
        'uploaded_by',
    ];

    public function ticket(): BelongsTo
    {
        return $this->belongsTo(Ticket::class);
    }

    public function message(): BelongsTo
    {
        return $this->belongsTo(TicketMessage::class, 'ticket_message_id');
    }

    public function uploader(): BelongsTo
    {
        return $this->belongsTo(User::class, 'uploaded_by');
    }

    public function humanSize(): string
    {
        $size = (int) $this->size;

        return $size >= 1048576
            ? round($size / 1048576, 1).' MB'
            : max(1, round($size / 1024)).' KB';
    }
}
