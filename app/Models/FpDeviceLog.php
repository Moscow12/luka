<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Concerns\HasUuids;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class FpDeviceLog extends Model
{
    use HasUuids;

    protected $fillable = [
        'fp_device_id',
        'user_id',
        'punch_time',
        'punch_type',
        'is_synced',
        'synced_at',
    ];

    protected $casts = [
        'punch_time' => 'datetime',
        'synced_at' => 'datetime',
        'is_synced' => 'boolean',
    ];

    public function device(): BelongsTo
    {
        return $this->belongsTo(FpDevice::class, 'fp_device_id');
    }

    public function getPunchTypeBadgeAttribute(): string
    {
        return match ($this->punch_type) {
            'check_in' => 'success',
            'check_out' => 'primary',
            'break_out' => 'warning',
            'break_in' => 'info',
            'overtime_in' => 'secondary',
            'overtime_out' => 'dark',
            default => 'secondary',
        };
    }
}
