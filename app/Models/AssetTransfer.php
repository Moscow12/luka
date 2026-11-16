<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Builder;
use Illuminate\Database\Eloquent\Concerns\HasUuids;
use Illuminate\Database\Eloquent\Model;
use Spatie\Activitylog\LogOptions;
use Spatie\Activitylog\Traits\LogsActivity;

/**
 * A request to move a register row (or part of a lot) to another department / location.
 * The register row only changes when the transfer is approved (AssetTransferService).
 */
class AssetTransfer extends Model
{
    use HasUuids, LogsActivity;

    public const PENDING = 'pending';

    public const APPROVED = 'approved';

    public const REJECTED = 'rejected';

    public const CANCELLED = 'cancelled';

    public const STATUSES = [
        self::PENDING => 'Pending',
        self::APPROVED => 'Approved',
        self::REJECTED => 'Rejected',
        self::CANCELLED => 'Cancelled',
    ];

    protected $guarded = [];

    protected $casts = [
        'quantity' => 'integer',
        'decided_at' => 'datetime',
    ];

    public function getActivitylogOptions(): LogOptions
    {
        return LogOptions::defaults()->logOnly(['status', 'quantity', 'to_department_id', 'to_facility_location_id', 'decision_note'])->logOnlyDirty()->useLogName('asset-transfer');
    }

    public function assetRegistry()
    {
        return $this->belongsTo(assetregistry::class, 'assetregistry_id')->withTrashed();
    }

    public function newAssetRegistry()
    {
        return $this->belongsTo(assetregistry::class, 'new_assetregistry_id')->withTrashed();
    }

    public function fromDepartment()
    {
        return $this->belongsTo(departments::class, 'from_department_id');
    }

    public function toDepartment()
    {
        return $this->belongsTo(departments::class, 'to_department_id');
    }

    public function fromLocation()
    {
        return $this->belongsTo(facilitylocation::class, 'from_facility_location_id')->withTrashed();
    }

    public function toLocation()
    {
        return $this->belongsTo(facilitylocation::class, 'to_facility_location_id')->withTrashed();
    }

    public function requestedBy()
    {
        return $this->belongsTo(User::class, 'requested_by');
    }

    public function decidedBy()
    {
        return $this->belongsTo(User::class, 'decided_by');
    }

    public function scopePending(Builder $query): Builder
    {
        return $query->where('status', self::PENDING);
    }

    public function isPending(): bool
    {
        return $this->status === self::PENDING;
    }
}
