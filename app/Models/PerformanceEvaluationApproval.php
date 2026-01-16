<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Concerns\HasUuids;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class PerformanceEvaluationApproval extends Model
{
    use HasUuids;

    protected $fillable = [
        'performance_evaluation_id',
        'approval_level_id',
        'approved_by',
        'status',
        'remarks',
        'approved_at',
    ];

    protected $casts = [
        'approved_at' => 'datetime',
    ];

    public function performanceEvaluation(): BelongsTo
    {
        return $this->belongsTo(PerformanceEvaluation::class);
    }

    public function approvalLevel(): BelongsTo
    {
        return $this->belongsTo(approvallevel::class, 'approval_level_id');
    }

    public function approver(): BelongsTo
    {
        return $this->belongsTo(Employee::class, 'approved_by');
    }
}
