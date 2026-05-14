<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Concerns\HasUuids;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\HasMany;

class FpDevice extends Model
{
    use HasUuids;

    protected $fillable = [
        'name',
        'device_type',
        'ip_address',
        'port',
        'username',
        'password',
        'location',
        'description',
        'status',
        'last_sync_at',
        'last_connected_at',
        'total_synced_logs',
        'is_active',
        'created_by',
    ];

    protected $casts = [
        'port' => 'integer',
        'total_synced_logs' => 'integer',
        'is_active' => 'boolean',
        'last_sync_at' => 'datetime',
        'last_connected_at' => 'datetime',
    ];

    public function creator(): BelongsTo
    {
        return $this->belongsTo(User::class, 'created_by');
    }

    public function logs(): HasMany
    {
        return $this->hasMany(FpDeviceLog::class);
    }

    public function getStatusBadgeAttribute(): string
    {
        return match ($this->status) {
            'active' => 'success',
            'inactive' => 'secondary',
            'offline' => 'danger',
            default => 'secondary',
        };
    }
}
