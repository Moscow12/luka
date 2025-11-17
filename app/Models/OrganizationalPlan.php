<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Concerns\HasUuids;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\HasMany;
use Illuminate\Database\Eloquent\SoftDeletes;

class OrganizationalPlan extends Model
{
    use HasFactory, HasUuids, SoftDeletes;

    protected $fillable = [
        'plan_name',
        'description',
        'start_date',
        'end_date',
        'period_type',
        'status',
        'created_by',
        'approved_by',
        'approved_at',
    ];

    protected $casts = [
        'start_date' => 'date',
        'end_date' => 'date',
        'approved_at' => 'datetime',
    ];

    public function items(): HasMany
    {
        return $this->hasMany(OrganizationalPlanItem::class);
    }

    public function departmentPlans(): HasMany
    {
        return $this->hasMany(DepartmentPlan::class);
    }

    public function creator(): BelongsTo
    {
        return $this->belongsTo(User::class, 'created_by');
    }

    public function approver(): BelongsTo
    {
        return $this->belongsTo(User::class, 'approved_by');
    }

    public function getTotalWeightAttribute(): float
    {
        return $this->items()->sum('weight');
    }

    public function isActive(): bool
    {
        return $this->status === 'active';
    }
}
