<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Concerns\HasUuids;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\SoftDeletes;

class SmsApiSetting extends Model
{
    use HasUuids, SoftDeletes;

    protected $fillable = [
        'provider_name',
        'sender_id',
        'sending_url',
        'delivery_report_url',
        'sender_name_url',
        'api_key',
        'secret_key',
        'is_default',
        'is_active',
        'added_by',
    ];

    protected $casts = [
        'is_default' => 'boolean',
        'is_active' => 'boolean',
    ];

    protected $hidden = [
        'secret_key',
    ];

    public function user(): BelongsTo
    {
        return $this->belongsTo(User::class, 'added_by');
    }
}
