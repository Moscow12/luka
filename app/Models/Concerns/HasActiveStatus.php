<?php

namespace App\Models\Concerns;

use Illuminate\Database\Eloquent\Builder;

/**
 * Active / inactive master records. Records already referenced elsewhere are deactivated
 * instead of deleted (see App\Support\RecordUsage); pickers for new records list active ones only.
 *
 * A model whose column uses other values (designations: "Active"/"Inactive") overrides
 * the STATUS_ACTIVE / STATUS_INACTIVE constants.
 */
trait HasActiveStatus
{
    public static function activeValue(): string
    {
        return defined(static::class.'::STATUS_ACTIVE') ? static::STATUS_ACTIVE : 'active';
    }

    public static function inactiveValue(): string
    {
        return defined(static::class.'::STATUS_INACTIVE') ? static::STATUS_INACTIVE : 'inactive';
    }

    public function scopeActive(Builder $query): Builder
    {
        return $query->where($query->qualifyColumn('status'), static::activeValue());
    }

    /**
     * Active records, plus the one currently selected on the record being edited, so editing
     * something that points at a since-deactivated record does not silently drop its value.
     */
    public function scopeActiveOr(Builder $query, ?string $currentId): Builder
    {
        return $query->where(fn (Builder $q) => $q
            ->where($q->qualifyColumn('status'), static::activeValue())
            ->when($currentId, fn (Builder $w) => $w->orWhere($w->qualifyColumn($this->getKeyName()), $currentId)));
    }

    public function isActive(): bool
    {
        // A null status (rows created before the column existed) counts as active
        return $this->status === null || $this->status === static::activeValue();
    }

    public function toggleStatus(): static
    {
        $this->forceFill(['status' => $this->isActive() ? static::inactiveValue() : static::activeValue()])->save();

        return $this;
    }
}
