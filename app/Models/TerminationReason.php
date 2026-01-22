<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Concerns\HasUuids;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\HasMany;

class TerminationReason extends Model
{
    use HasFactory, HasUuids;

    protected $fillable = [
        'name',
        'description',
        'type',
        'is_active',
        'added_by',
    ];

    protected $casts = [
        'is_active' => 'boolean',
    ];

    /**
     * Get the user who added this reason.
     */
    public function addedBy(): BelongsTo
    {
        return $this->belongsTo(User::class, 'added_by');
    }

    /**
     * Get contract requests using this reason.
     */
    public function contractRequests(): HasMany
    {
        return $this->hasMany(ContractRequest::class, 'termination_reason_id');
    }

    /**
     * Scope for active reasons.
     */
    public function scopeActive($query)
    {
        return $query->where('is_active', true);
    }

    /**
     * Scope for termination type reasons.
     */
    public function scopeForTermination($query)
    {
        return $query->whereIn('type', ['termination', 'both']);
    }

    /**
     * Scope for non-renewal type reasons.
     */
    public function scopeForNonRenewal($query)
    {
        return $query->whereIn('type', ['non_renewal', 'both']);
    }

    /**
     * Get type label for display.
     */
    public function getTypeLabelAttribute(): string
    {
        return match ($this->type) {
            'termination' => 'Termination Only',
            'non_renewal' => 'Non-Renewal Only',
            'both' => 'Both',
            default => ucfirst($this->type),
        };
    }
}
