<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Concerns\HasUuids;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\HasMany;
use Illuminate\Database\Eloquent\SoftDeletes;

class EmployeePlanItem extends Model
{
    use HasFactory, HasUuids, SoftDeletes;

    protected $fillable = [
        'employee_plan_id',
        'department_plan_item_id',
        'item_name',
        'description',
        'weight',
        'target_value',
        'target_unit',
        'min_acceptable',
        'max_possible',
        'actual_achievement',
        'score',
        'achievement_notes',
        'employee_comments',
        'supervisor_comments',
        'display_order',
        'is_active',
    ];

    protected $casts = [
        'weight' => 'decimal:2',
        'target_value' => 'decimal:2',
        'min_acceptable' => 'decimal:2',
        'max_possible' => 'decimal:2',
        'actual_achievement' => 'decimal:2',
        'score' => 'decimal:2',
    ];

    public function employeePlan(): BelongsTo
    {
        return $this->belongsTo(EmployeePlan::class);
    }

    public function departmentPlanItem(): BelongsTo
    {
        return $this->belongsTo(DepartmentPlanItem::class);
    }

    public function implementations(): HasMany
    {
        return $this->hasMany(EmployeePlanImplementation::class);
    }

    public function getTotalAchievedAttribute(): float
    {
        return $this->implementations()->where('status', '!=', 'rejected')->sum('quantity_achieved') ?? 0;
    }

    public function getProgressPercentageAttribute(): float
    {
        if (!$this->target_value || $this->target_value == 0) {
            return 0;
        }
        $percentage = ($this->total_achieved / $this->target_value) * 100;
        return min($percentage, 100);
    }
}
