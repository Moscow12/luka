<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Concerns\HasUuids;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\HasMany;

/** A time-bound CHOP target for one financial year, under an objective. */
class ChopTarget extends Model
{
    use HasUuids;

    protected $fillable = ['objective_id', 'financial_year_id', 'code', 'description', 'sort_order', 'status', 'added_by'];

    protected $casts = ['sort_order' => 'integer'];

    public function objective(): BelongsTo
    {
        return $this->belongsTo(ChopObjective::class, 'objective_id');
    }

    public function financialYear(): BelongsTo
    {
        return $this->belongsTo(FinancialYear::class);
    }

    public function activities(): HasMany
    {
        return $this->hasMany(chopactivities::class, 'target_id');
    }

    public function addedBy(): BelongsTo
    {
        return $this->belongsTo(User::class, 'added_by');
    }

    public function scopeOrdered($query)
    {
        return $query->orderBy('sort_order')->orderBy('code')->orderBy('created_at');
    }

    public function scopeActive($query)
    {
        return $query->where('status', 'active');
    }

    public function scopeForYear($query, ?string $financialYearId)
    {
        return $query->when($financialYearId, fn ($q) => $q->where('financial_year_id', $financialYearId));
    }
}
