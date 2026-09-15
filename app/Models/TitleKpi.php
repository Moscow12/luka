<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Concerns\HasUuids;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\SoftDeletes;

class TitleKpi extends Model
{
    use HasFactory, HasUuids, SoftDeletes;

    protected $fillable = [
        'job_title_id',
        'kpi_name',
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
        'performance_indicators',
        'display_order',
        'is_mandatory',
        'is_active',
        'created_by',
    ];

    protected $casts = [
        'weight' => 'decimal:2',
        'target_value' => 'decimal:2',
        'min_acceptable' => 'decimal:2',
        'max_possible' => 'decimal:2',
        'is_mandatory' => 'boolean',
        'is_active' => 'boolean',
    ];

    public function jobtitle(): BelongsTo
    {
        return $this->belongsTo(Jobtitle::class, 'job_title_id');
    }

    public function creator(): BelongsTo
    {
        return $this->belongsTo(User::class, 'created_by');
    }
}
