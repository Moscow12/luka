<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Concerns\HasUuids;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\HasMany;
use Illuminate\Database\Eloquent\SoftDeletes;

class DepartmentPlanItem extends Model
{
    use HasFactory, HasUuids, SoftDeletes;

    protected $fillable = [
        'department_plan_id',
        'organizational_plan_item_id',
        'item_name',
        'description',
        'weight',
        'target_value',
        'target_unit',
        'min_acceptable',
        'max_possible',
        'department_specific_notes',
        'display_order',
        'is_active',
    ];

    protected $casts = [
        'weight' => 'decimal:2',
        'target_value' => 'decimal:2',
        'min_acceptable' => 'decimal:2',
        'max_possible' => 'decimal:2',
    ];

    public function departmentPlan(): BelongsTo
    {
        return $this->belongsTo(DepartmentPlan::class);
    }

    public function organizationalPlanItem(): BelongsTo
    {
        return $this->belongsTo(OrganizationalPlanItem::class);
    }

    public function employeePlanItems(): HasMany
    {
        return $this->hasMany(EmployeePlanItem::class);
    }
}
