<?php

namespace App\Models\Engineering;

use App\Models\User;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\HasMany;

/**
 * The checklist of one PPM occurrence: a copy of the schedule's tasks at the time it was raised.
 */
class PpmInstance extends EngineeringModel
{
    protected $table = 'eng_ppm_instances';

    protected $casts = [
        'due_at' => 'date',
        'completed_at' => 'datetime',
    ];

    public function schedule(): BelongsTo
    {
        return $this->belongsTo(PpmSchedule::class, 'schedule_id');
    }

    public function job(): BelongsTo
    {
        return $this->belongsTo(MaintenanceJob::class, 'job_id');
    }

    public function entries(): HasMany
    {
        return $this->hasMany(PpmEntry::class, 'instance_id')->orderBy('seq');
    }

    public function completedBy(): BelongsTo
    {
        return $this->belongsTo(User::class, 'completed_by');
    }

    public function isCompleted(): bool
    {
        return $this->state === 'completed';
    }
}
