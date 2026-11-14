<?php

namespace App\Models\Engineering;

use App\Enums\Engineering\JobStatus;
use App\Models\Employee;
use App\Models\Engineering\Concerns\LogsEngineeringActivity;
use App\Models\User;
use Illuminate\Database\Eloquent\Builder;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\BelongsToMany;
use Illuminate\Database\Eloquent\Relations\HasMany;
use Illuminate\Database\Eloquent\Relations\HasOne;

/**
 * One line of an MRV: assigned, worked, closed and rated on its own.
 */
class MaintenanceJob extends EngineeringModel
{
    use LogsEngineeringActivity;

    protected $table = 'eng_jobs';

    protected $casts = [
        'status' => JobStatus::class,
        'is_rework' => 'boolean',
        'not_in_inventory' => 'boolean',
        'no_spares_consumed' => 'boolean',
        'assigned_at' => 'datetime',
        'original_assigned_at' => 'datetime',
        'started_at' => 'datetime',
        'last_activity_at' => 'datetime',
        'spare_required_at' => 'datetime',
        'procurement_order_at' => 'datetime',
        'client_informed_at' => 'datetime',
        'completed_at' => 'datetime',
    ];

    /* ---------- Relationships ---------- */

    public function requisition(): BelongsTo
    {
        return $this->belongsTo(Requisition::class, 'requisition_id');
    }

    public function equipment(): BelongsTo
    {
        return $this->belongsTo(Equipment::class, 'equipment_id');
    }

    public function section(): BelongsTo
    {
        return $this->belongsTo(Section::class, 'section_id');
    }

    public function engineer(): BelongsTo
    {
        return $this->belongsTo(Employee::class, 'engineer_id');
    }

    public function assistants(): BelongsToMany
    {
        return $this->belongsToMany(Employee::class, 'eng_job_assistants', 'job_id', 'employee_id')
            ->using(JobAssistant::class)
            ->withTimestamps();
    }

    public function assignedBy(): BelongsTo
    {
        return $this->belongsTo(User::class, 'assigned_by');
    }

    public function signedOutBy(): BelongsTo
    {
        return $this->belongsTo(User::class, 'signed_out_by');
    }

    public function assignmentLogs(): HasMany
    {
        return $this->hasMany(JobAssignmentLog::class, 'job_id')->orderBy('created_at');
    }

    public function notes(): HasMany
    {
        return $this->hasMany(JobNote::class, 'job_id')->orderBy('created_at');
    }

    public function safetyTests(): HasMany
    {
        return $this->hasMany(SafetyTest::class, 'job_id');
    }

    public function feedback(): HasMany
    {
        return $this->hasMany(Feedback::class, 'job_id')->latest();
    }

    /** The rating that counts: the latest one not replaced by a later rating. */
    public function currentFeedback(): HasOne
    {
        return $this->hasOne(Feedback::class, 'job_id')->where('superseded', false)->latestOfMany();
    }

    public function sparesRequisitions(): HasMany
    {
        return $this->hasMany(SparesRequisition::class, 'job_id')->orderBy('requested_at');
    }

    public function jobCard(): HasOne
    {
        return $this->hasOne(JobCard::class, 'job_id');
    }

    public function rca(): HasOne
    {
        return $this->hasOne(RootCauseAnalysis::class, 'job_id');
    }

    public function oxygenOrder(): HasOne
    {
        return $this->hasOne(OxygenOrder::class, 'job_id');
    }

    public function ppmInstance(): HasOne
    {
        return $this->hasOne(PpmInstance::class, 'job_id');
    }

    public function technicalReport(): HasOne
    {
        return $this->hasOne(TechnicalReport::class, 'job_id');
    }

    /* ---------- Scopes ---------- */

    public function scopeOpen(Builder $query): Builder
    {
        return $query->whereIn('status', JobStatus::openValues());
    }

    public function scopeWorkable(Builder $query): Builder
    {
        return $query->whereIn('status', JobStatus::workableValues());
    }

    public function scopeFinished(Builder $query): Builder
    {
        return $query->whereIn('status', JobStatus::finishedValues());
    }

    /** Signed out by the engineer but not yet rated by the requester. */
    public function scopeAwaitingFeedback(Builder $query): Builder
    {
        return $query->whereIn('status', [JobStatus::Completed->value, JobStatus::BeyondRepair->value])
            ->whereDoesntHave('feedback', fn (Builder $q) => $q->where('superseded', false));
    }

    public function scopeEmergencyFirst(Builder $query): Builder
    {
        return $query->orderByRaw(
            "(select case priority when 'emergency' then 0 else 1 end from eng_requisitions where eng_requisitions.id = eng_jobs.requisition_id)"
        );
    }

    public function scopeSearch(Builder $query, ?string $term): Builder
    {
        return $query->when($term, fn (Builder $q) => $q->where(fn (Builder $w) => $w
            ->where('number', 'like', "%{$term}%")
            ->orWhere('equipment_text', 'like', "%{$term}%")
            ->orWhere('problem_description', 'like', "%{$term}%")
            ->orWhereHas('equipment', fn (Builder $e) => $e->where('name', 'like', "%{$term}%")->orWhere('code', 'like', "%{$term}%"))));
    }

    /* ---------- State ---------- */

    public function isOpen(): bool
    {
        return $this->status->isOpen();
    }

    /** A signed-out job is read-only for everyone; corrections go through rework. */
    public function isLocked(): bool
    {
        return ! $this->isOpen();
    }

    public function isPpm(): bool
    {
        return $this->type_of_work === 'ppm';
    }

    public function awaitsFeedback(): bool
    {
        return in_array($this->status, [JobStatus::Completed, JobStatus::BeyondRepair], true)
            && $this->currentFeedback === null;
    }

    /** Past its allowed lead time and still open. */
    public function isOverdue(): bool
    {
        return $this->isOpen()
            && $this->assigned_at !== null
            && $this->days_to_be_done !== null
            && $this->assigned_at->copy()->addDays($this->days_to_be_done)->isPast();
    }

    /* ---------- Derived values ---------- */

    public function equipmentName(): string
    {
        return $this->equipment?->name ?? $this->equipment_text ?? '—';
    }

    public function equipmentCode(): string
    {
        return $this->equipment?->code ?? '—';
    }

    /** Turn-around time in days: sign-out minus first assignment. Null while open. */
    public function tatDays(): ?float
    {
        if (! $this->completed_at || ! $this->original_assigned_at) {
            return null;
        }

        return round($this->original_assigned_at->diffInSeconds($this->completed_at) / 86400, 2);
    }

    /** Whole days the job has been with engineering (since assignment, else since request). */
    public function daysOpen(): int
    {
        $from = $this->original_assigned_at ?? $this->requisition?->requested_at ?? $this->created_at;

        return (int) $from->diffInDays($this->completed_at ?? now());
    }

    /** Whole days without attention: since assignment if never opened, else since last activity. */
    public function daysUnattended(): int
    {
        $from = $this->last_activity_at ?? $this->assigned_at ?? $this->created_at;

        return (int) $from->diffInDays(now());
    }

    public function satisfactionScore(): int
    {
        return $this->currentFeedback?->score_pct ?? 0;
    }
}
