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

    // Activity Type Options
    public const ACTIVITY_TYPE_EXPENDITURE = 'expenditure';
    public const ACTIVITY_TYPE_REVENUE = 'revenue';

    // Frequency Monitoring Options
    public const FREQUENCY_WEEKLY = 'weekly';
    public const FREQUENCY_MONTHLY = 'monthly';
    public const FREQUENCY_BIMONTHLY = 'bimonthly';
    public const FREQUENCY_QUARTERLY = 'quarterly';
    public const FREQUENCY_QUADRIMONTHLY = 'quadrimonthly';
    public const FREQUENCY_BIANNUAL = 'biannual';
    public const FREQUENCY_ANNUAL = 'annual';

    public static function getActivityTypes(): array
    {
        return [
            self::ACTIVITY_TYPE_EXPENDITURE => 'Expenditure',
            self::ACTIVITY_TYPE_REVENUE => 'Revenue',
        ];
    }

    public static function getFrequencyOptions(): array
    {
        return [
            self::FREQUENCY_WEEKLY => 'Weekly',
            self::FREQUENCY_MONTHLY => 'Monthly',
            self::FREQUENCY_BIMONTHLY => 'Bi-Monthly',
            self::FREQUENCY_QUARTERLY => 'Quarterly',
            self::FREQUENCY_QUADRIMONTHLY => 'Quadri-Monthly',
            self::FREQUENCY_BIANNUAL => 'Bi-Annual',
            self::FREQUENCY_ANNUAL => 'Annual',
        ];
    }

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
        return $this->hasMany(activityitems::class, 'activity_id');
    }

    public function personels(): \Illuminate\Database\Eloquent\Relations\HasMany
    {
        return $this->hasMany(activitypersonel::class, 'activity_id');
    }
}
