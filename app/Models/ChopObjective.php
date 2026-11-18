<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Concerns\HasUuids;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\HasMany;
use Illuminate\Database\Eloquent\Relations\HasManyThrough;

/** A CHOP objective; targets for each financial year sit under it. */
class ChopObjective extends Model
{
    use HasUuids;

    protected $fillable = ['code', 'name', 'description', 'sort_order', 'status', 'added_by'];

    protected $casts = ['sort_order' => 'integer'];

    public function targets(): HasMany
    {
        return $this->hasMany(ChopTarget::class, 'objective_id');
    }

    public function activities(): HasManyThrough
    {
        return $this->hasManyThrough(chopactivities::class, ChopTarget::class, 'objective_id', 'target_id');
    }

    public function addedBy(): BelongsTo
    {
        return $this->belongsTo(User::class, 'added_by');
    }

    public function scopeOrdered($query)
    {
        return $query->orderBy('sort_order')->orderBy('code')->orderBy('name');
    }

    public function scopeActive($query)
    {
        return $query->where('status', 'active');
    }

    /** "1. Improve access …" when a code is set. */
    public function getLabelAttribute(): string
    {
        return $this->code ? "{$this->code}. {$this->name}" : $this->name;
    }
}
