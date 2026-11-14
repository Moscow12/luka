<?php

namespace App\Models\Engineering;

use App\Models\departments;
use App\Models\Engineering\Concerns\LogsEngineeringActivity;
use App\Models\User;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\HasMany;

/**
 * Maintenance Requisition Voucher (MRV) header. Each of its 1-5 lines is a MaintenanceJob.
 */
class Requisition extends EngineeringModel
{
    use LogsEngineeringActivity;

    protected $table = 'eng_requisitions';

    protected $casts = ['requested_at' => 'datetime'];

    public function jobs(): HasMany
    {
        return $this->hasMany(MaintenanceJob::class, 'requisition_id')->orderBy('seq');
    }

    public function section(): BelongsTo
    {
        return $this->belongsTo(Section::class, 'section_id');
    }

    public function category(): BelongsTo
    {
        return $this->belongsTo(ProblemCategory::class, 'category_id');
    }

    public function requester(): BelongsTo
    {
        return $this->belongsTo(User::class, 'requested_by');
    }

    public function department(): BelongsTo
    {
        return $this->belongsTo(departments::class, 'department_id');
    }

    public function room(): BelongsTo
    {
        return $this->belongsTo(Room::class, 'room_id');
    }

    public function isEmergency(): bool
    {
        return $this->priority === 'emergency';
    }

    /** Floor / room / place, from the room register or as typed by the requester. */
    public function locationLabel(): string
    {
        return collect([$this->room?->label(), $this->location_text])->filter()->implode(' · ') ?: '—';
    }
}
