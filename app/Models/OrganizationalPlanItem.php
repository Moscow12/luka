<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Concerns\HasUuids;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\HasMany;
use Illuminate\Database\Eloquent\SoftDeletes;

class OrganizationalPlanItem extends Model
{
    use HasFactory, HasUuids, SoftDeletes;

    protected $fillable = [
        'organizational_plan_id',
        'item_name',
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
        'display_order',
        'is_active',
    ];

    protected $casts = [
        'weight' => 'decimal:2',
        'target_value' => 'decimal:2',
        'min_acceptable' => 'decimal:2',
        'max_possible' => 'decimal:2',
    ];

    public function organizationalPlan(): BelongsTo
    {
        return $this->belongsTo(OrganizationalPlan::class);
    }

    public function departmentPlanItems(): HasMany
    {
        return $this->hasMany(DepartmentPlanItem::class);
    }
}
