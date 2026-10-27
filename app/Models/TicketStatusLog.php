<?php

namespace App\Models;

use App\Models\Concerns\WriteOnce;
use Illuminate\Database\Eloquent\Concerns\HasUuids;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class TicketStatusLog extends Model
{
    use HasUuids, WriteOnce;

    const UPDATED_AT = null;

    protected $fillable = ['ticket_id', 'from_status', 'to_status', 'acted_by', 'remarks'];

    public function actor(): BelongsTo
    {
        return $this->belongsTo(User::class, 'acted_by');
    }
}
