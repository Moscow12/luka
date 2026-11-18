<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Concerns\HasUuids;
use Illuminate\Database\Eloquent\Model;

class sourceoffunds extends Model
{
    use HasUuids;

    protected $table = 'sourceoffunds';

    protected $primaryKey = 'id';

    protected $keyType = 'string';

    public $incrementing = false;

    protected $fillable = [
        'name',
        'slug',
        'description',
        'colorcode',
        'is_active',
        'estimated_cost',
        'financial_year_id',
        'added_by',
    ];

    public function financialYear(): \Illuminate\Database\Eloquent\Relations\BelongsTo
    {
        return $this->belongsTo(FinancialYear::class);
    }

    public function addedBy(): \Illuminate\Database\Eloquent\Relations\BelongsTo
    {
        return $this->belongsTo(User::class);
    }

    /** Search by name/slug and limit to a financial year: shared by the list and its printout. */
    public function scopeFiltered($query, ?string $search, ?string $financialYearId)
    {
        $search = trim((string) $search);

        return $query
            ->when($search !== '', fn ($q) => $q->where(fn ($q) => $q
                ->where('name', 'like', '%'.$search.'%')
                ->orWhere('slug', 'like', '%'.$search.'%')))
            ->when($financialYearId, fn ($q) => $q->where('financial_year_id', $financialYearId));
    }

    /** estimated_cost is free text ("1,200,000 TZS"); this is its numeric value. */
    public function getEstimatedCostValueAttribute(): float
    {
        return (float) preg_replace('/[^0-9.]/', '', $this->estimated_cost ?? '0');
    }
}
