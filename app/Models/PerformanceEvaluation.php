<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Concerns\HasUuids;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\HasMany;
use Illuminate\Database\Eloquent\SoftDeletes;

class PerformanceEvaluation extends Model
{
    use HasUuids, SoftDeletes;

    protected $fillable = [
        'employee_plan_id',
        'employee_id',
        'self_score',
        'supervisor_score',
        'task_score',
        'final_score',
        'employee_comments',
        'supervisor_comments',
        'strengths',
        'areas_for_improvement',
        'recommendations',
        'status',
        'current_approval_level',
        'submitted_by',
        'submitted_at',
        'approved_by',
        'approved_at',
        'rejection_reason',
    ];

    protected $casts = [
        'self_score' => 'decimal:2',
        'supervisor_score' => 'decimal:2',
        'task_score' => 'decimal:2',
        'final_score' => 'decimal:2',
        'submitted_at' => 'datetime',
        'approved_at' => 'datetime',
    ];

    public function employeePlan(): BelongsTo
    {
        return $this->belongsTo(EmployeePlan::class);
    }

    public function employee(): BelongsTo
    {
        return $this->belongsTo(Employee::class);
    }

    public function submittedBy(): BelongsTo
    {
        return $this->belongsTo(User::class, 'submitted_by');
    }

    public function approvedBy(): BelongsTo
    {
        return $this->belongsTo(User::class, 'approved_by');
    }

    public function approvals(): HasMany
    {
        return $this->hasMany(PerformanceEvaluationApproval::class);
    }

    public function getStatusBadgeAttribute(): string
    {
        return match ($this->status) {
            'draft' => 'secondary',
            'submitted' => 'info',
            'under_review' => 'warning',
            'approved' => 'success',
            'rejected' => 'danger',
            default => 'secondary',
        };
    }

    public function getStatusLabelAttribute(): string
    {
        return match ($this->status) {
            'draft' => 'Draft',
            'submitted' => 'Submitted',
            'under_review' => 'Under Review',
            'approved' => 'Approved',
            'rejected' => 'Rejected',
            default => 'Unknown',
        };
    }

    /** Entries on this plan still waiting for a supervisor to verify or reject them. */
    public function pendingEntriesCount(): int
    {
        return EmployeePlanImplementation::where('status', 'pending')
            ->whereHas('employeePlanItem', fn ($q) => $q->where('employee_plan_id', $this->employee_plan_id))
            ->count();
    }

    /**
     * Out of 100, from the 1 to 10 scores verifiers gave to verified entries: entry scores are
     * averaged per goal, then combined by goal weight. Null when no entry has been scored, and
     * goals with no scored entries are left out rather than counted as zero.
     */
    public function calculateTaskScore(): ?float
    {
        $weightedTotal = 0;
        $totalWeight = 0;

        foreach ($this->employeePlan?->employeePlanItems ?? [] as $item) {
            $average = $item->implementations()
                ->where('status', 'verified')
                ->whereNotNull('verification_score')
                ->avg('verification_score');

            if ($average !== null && $item->weight > 0) {
                $weightedTotal += $average * 10 * $item->weight;
                $totalWeight += $item->weight;
            }
        }

        return $totalWeight > 0 ? round($weightedTotal / $totalWeight, 2) : null;
    }

    /** The final score is the plain average of whichever parts exist. */
    public static function combineScores(?float ...$scores): float
    {
        $parts = array_filter($scores, fn ($score) => $score !== null);

        return round(array_sum($parts) / max(count($parts), 1), 2);
    }

    public function calculateSelfScore(): float
    {
        $plan = $this->employeePlan;
        if (! $plan) {
            return 0;
        }

        $items = $plan->employeePlanItems;
        if ($items->isEmpty()) {
            return 0;
        }

        $totalWeightedScore = 0;
        $totalWeight = $items->sum('weight');

        foreach ($items as $item) {
            if ($item->target_value && $item->target_value > 0) {
                $achieved = $item->total_achieved ?? 0;
                $percentage = min(($achieved / $item->target_value) * 100, 100);
                $weightedScore = ($percentage * $item->weight) / 100;
                $totalWeightedScore += $weightedScore;
            }
        }

        return $totalWeight > 0 ? round(($totalWeightedScore / $totalWeight) * 100, 2) : 0;
    }
}
