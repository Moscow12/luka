<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Concerns\HasUuids;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\HasMany;

class fpusers extends Model
{
    use HasUuids;

    protected $table = 'fpusers';

    protected $fillable = [
        'name',
        'fpdevice_id',
        'fpdevice_address',
        'added_by',
    ];

    public function addedBy(): BelongsTo
    {
        return $this->belongsTo(User::class, 'added_by');
    }

    public function attendances(): HasMany
    {
        return $this->hasMany(employeeattendances::class, 'fpuser_id', 'fpdevice_id');
    }

    public function device(): BelongsTo
    {
        return $this->belongsTo(FpDevice::class, 'fpdevice_address', 'ip_address');
    }
}
