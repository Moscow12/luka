<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Concerns\HasUuids;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\SoftDeletes;

class EmployeeAssignedDuty extends Model
{
    use HasFactory, HasUuids, SoftDeletes;

    protected $fillable = [
        'employee_id',
        'duty_name',
        'description',
        'kpi_type',
        'measurement_type',
        'weight',
        'target_value',
        'target_unit',
        'min_acceptable',
        'max_possible',
        'rating_scale_max',
        'scoring_criteria',
        'start_date',
        'end_date',
        'priority',
        'actual_achievement',
        'score',
        'achievement_notes',
        'status',
        'assigned_by',
        'assigned_at',
        'reviewed_by',
        'reviewed_at',
        'review_comments',
        'is_active',
    ];

    protected $casts = [
        'weight' => 'decimal:2',
        'target_value' => 'decimal:2',
        'min_acceptable' => 'decimal:2',
        'max_possible' => 'decimal:2',
        'actual_achievement' => 'decimal:2',
        'score' => 'decimal:2',
        'is_active' => 'boolean',
        'start_date' => 'date',
        'end_date' => 'date',
        'assigned_at' => 'datetime',
        'reviewed_at' => 'datetime',
    ];

    public function employee(): BelongsTo
    {
        return $this->belongsTo(Employee::class);
    }

    public function assignedBy(): BelongsTo
    {
        return $this->belongsTo(User::class, 'assigned_by');
    }

    public function reviewedBy(): BelongsTo
    {
        return $this->belongsTo(User::class, 'reviewed_by');
    }
}
