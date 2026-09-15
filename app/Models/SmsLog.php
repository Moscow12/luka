<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Concerns\HasUuids;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class SmsLog extends Model
{
    use HasUuids;

    protected $fillable = [
        'sms_api_setting_id',
        'phone_number',
        'message',
        'status',
        'response',
        'message_id',
        'sent_by',
        'delivered_at',
    ];

    protected $casts = [
        'delivered_at' => 'datetime',
    ];

    public function smsApiSetting(): BelongsTo
    {
        return $this->belongsTo(SmsApiSetting::class, 'sms_api_setting_id');
    }

    public function user(): BelongsTo
    {
        return $this->belongsTo(User::class, 'sent_by');
    }

    /**
     * Check if SMS was successfully sent
     */
    public function isSent(): bool
    {
        return in_array($this->status, ['sent', 'delivered']);
    }

    /**
     * Check if SMS failed
     */
    public function isFailed(): bool
    {
        return $this->status === 'failed';
    }

    /**
     * Mark SMS as delivered
     */
    public function markAsDelivered(): void
    {
        $this->update([
            'status' => 'delivered',
            'delivered_at' => now(),
        ]);
    }
}
