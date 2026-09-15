<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Concerns\HasUuids;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\HasMany;
use Illuminate\Database\Eloquent\SoftDeletes;

class DepartmentPlan extends Model
{
    use HasFactory, HasUuids, SoftDeletes;

    protected $fillable = [
        'organizational_plan_id',
        'department_id',
        'plan_name',
        'description',
        'status',
        'assigned_by',
        'assigned_at',
    ];

    protected $casts = [
        'assigned_at' => 'datetime',
    ];

    public function organizationalPlan(): BelongsTo
    {
        return $this->belongsTo(OrganizationalPlan::class);
    }

    public function department(): BelongsTo
    {
        return $this->belongsTo(departments::class);
    }

    public function assignedBy(): BelongsTo
    {
        return $this->belongsTo(User::class, 'assigned_by');
    }

    public function departmentPlanItems(): HasMany
    {
        return $this->hasMany(DepartmentPlanItem::class);
    }

    public function employeePlans(): HasMany
    {
        return $this->hasMany(EmployeePlan::class);
    }
}
