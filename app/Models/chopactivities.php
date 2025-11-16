<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Concerns\HasUuids;
use Illuminate\Database\Eloquent\Model;

class chopactivities extends Model
{
    use HasUuids;
    protected $table = 'chopactivities';

    protected $primaryKey = 'id';

    protected $keyType = 'string';

    public $incrementing = false;

    protected $fillable = [
        'planned_activity',
        'actual_activity',
        'description',
        'status',
        'planned_amount',
        'actual_amount',
        'percentage',
        'is_active',
        'is_planned',
        'is_approved',
        'expected_outcome',
        'expected_outcome_date',
        'activity_type',
        'frequence_monitoring',
        'source_id',
        'category_id',
        'added_by',
        'financial_year_id',
    ];

    public function addedBy(): \Illuminate\Database\Eloquent\Relations\BelongsTo
    {
        return $this->belongsTo(User::class);
    }

    public function source(): \Illuminate\Database\Eloquent\Relations\BelongsTo
    {
        return $this->belongsTo(sourceoffunds::class);
    }

    public function category(): \Illuminate\Database\Eloquent\Relations\BelongsTo
    {
        return $this->belongsTo(chopcategoryarea::class);
    }

    public function financialYear(): \Illuminate\Database\Eloquent\Relations\BelongsTo
    {
        return $this->belongsTo(FinancialYear::class);
    }

    public function items(): \Illuminate\Database\Eloquent\Relations\HasMany
    {
        return $this->hasMany(activityitems::class);
    }

    public function personels(): \Illuminate\Database\Eloquent\Relations\HasMany
    {
        return $this->hasMany(activitypersonel::class);
    }
}
