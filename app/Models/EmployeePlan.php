<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Concerns\HasUuids;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\HasMany;
use Illuminate\Database\Eloquent\Relations\HasOne;
use Illuminate\Database\Eloquent\SoftDeletes;

class EmployeePlan extends Model
{
    use HasFactory, HasUuids, SoftDeletes;

    protected $fillable = [
        'department_plan_id',
        'employee_id',
        'plan_name',
        'description',
        'status',
        'assigned_by',
        'assigned_at',
        'reviewed_by',
        'reviewed_at',
        'review_comments',
    ];

    protected $casts = [
        'assigned_at' => 'datetime',
        'reviewed_at' => 'datetime',
    ];

    public function departmentPlan(): BelongsTo
    {
        return $this->belongsTo(DepartmentPlan::class);
    }

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

    public function employeePlanItems(): HasMany
    {
        return $this->hasMany(EmployeePlanItem::class);
    }

    public function performanceEvaluation(): HasOne
    {
        return $this->hasOne(PerformanceEvaluation::class);
    }
}
