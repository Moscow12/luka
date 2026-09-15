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
        return match($this->status) {
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
        return match($this->status) {
            'draft' => 'Draft',
            'submitted' => 'Submitted',
            'under_review' => 'Under Review',
            'approved' => 'Approved',
            'rejected' => 'Rejected',
            default => 'Unknown',
        };
    }

    public function calculateSelfScore(): float
    {
        $plan = $this->employeePlan;
        if (!$plan) return 0;

        $items = $plan->employeePlanItems;
        if ($items->isEmpty()) return 0;

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
