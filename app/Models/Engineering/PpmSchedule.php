<?php

namespace App\Models\Engineering;

use App\Models\Employee;
use App\Models\User;
use Illuminate\Database\Eloquent\Builder;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\HasMany;
use Illuminate\Database\Eloquent\Relations\HasOne;

class PpmSchedule extends EngineeringModel
{
    public const STATE_SCHEDULED = 'scheduled';

    public const STATE_DUE_SOON = 'due_soon';

    public const STATE_OVERDUE = 'overdue';

    protected $table = 'eng_ppm_schedules';

    protected $casts = [
        'last_done_at' => 'datetime',
        'next_due_at' => 'date',
    ];

    public function equipment(): BelongsTo
    {
        return $this->belongsTo(Equipment::class, 'equipment_id');
    }

    public function responsible(): BelongsTo
    {
        return $this->belongsTo(Employee::class, 'responsible_employee_id');
    }

    public function lastDoneBy(): BelongsTo
    {
        return $this->belongsTo(User::class, 'last_done_by');
    }

    public function tasks(): HasMany
    {
        return $this->hasMany(PpmTask::class, 'schedule_id')->orderBy('seq');
    }

    public function consumables(): HasMany
    {
        return $this->hasMany(PpmConsumable::class, 'schedule_id');
    }

    public function instances(): HasMany
    {
        return $this->hasMany(PpmInstance::class, 'schedule_id')->latest();
    }

    /** The checklist of the current occurrence, if one has been generated and is not finished. */
    public function openInstance(): HasOne
    {
        return $this->hasOne(PpmInstance::class, 'schedule_id')->where('state', '!=', 'completed')->latestOfMany();
    }

    public function scopeActive(Builder $query): Builder
    {
        return $query->where('status', 'active')
            ->whereHas('equipment', fn (Builder $q) => $q->whereNull('removed_at'));
    }

    /** Due within the warning window, or already overdue. */
    public function scopeNeedingAttention(Builder $query): Builder
    {
        return $query->whereNotNull('next_due_at')
            ->whereDate('next_due_at', '<=', now()->addDays(config('engineering.ppm_due_soon_days'))->toDateString());
    }

    /** scheduled | due_soon (blue) | overdue (red) */
    public function dueState(): string
    {
        if (! $this->next_due_at) {
            return self::STATE_SCHEDULED;
        }

        $daysToDue = $this->daysToDue();

        return match (true) {
            $daysToDue < 0 => self::STATE_OVERDUE,
            $daysToDue <= config('engineering.ppm_due_soon_days') => self::STATE_DUE_SOON,
            default => self::STATE_SCHEDULED,
        };
    }

    /** Negative when overdue. */
    public function daysToDue(): ?int
    {
        return $this->next_due_at
            ? (int) now()->startOfDay()->diffInDays($this->next_due_at, false)
            : null;
    }
}
