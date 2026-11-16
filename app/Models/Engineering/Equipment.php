<?php

namespace App\Models\Engineering;

use App\Models\assetregistry;
use App\Models\departments;
use App\Models\Engineering\Concerns\LogsEngineeringActivity;
use App\Models\User;
use Illuminate\Database\Eloquent\Builder;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\HasMany;
use Illuminate\Support\Carbon;

class Equipment extends EngineeringModel
{
    use LogsEngineeringActivity;

    protected $table = 'eng_equipment';

    protected $casts = [
        'under_contract' => 'boolean',
        'flagged_for_removal' => 'boolean',
        'contract_expiry' => 'date',
        'installation_date' => 'date',
        'counted_at' => 'date',
        'status_changed_at' => 'datetime',
        'removed_at' => 'datetime',
        'quantity' => 'integer',
    ];

    /**
     * The asset register row this is the engineering profile of. Serial, model, vendor and cost
     * live there; department_id, room_id and quantity here are copies kept in step by EquipmentSync.
     */
    public function assetRegistry(): BelongsTo
    {
        return $this->belongsTo(assetregistry::class, 'asset_registry_id')->withTrashed();
    }

    public function getSerialNumberAttribute(): ?string
    {
        return $this->assetRegistry?->serial_number;
    }

    public function getModelAttribute(): ?string
    {
        return $this->assetRegistry?->model;
    }

    public function getSupplierAttribute(): ?string
    {
        $registry = $this->assetRegistry;

        return $registry?->vendorRelation?->name ?? $registry?->vendor;
    }

    public function getPurchaseCostAttribute(): ?string
    {
        return $this->assetRegistry?->purchase_cost;
    }

    /** Purchase cost less accumulated depreciation, from the asset register. */
    public function getCurrentValueAttribute(): ?float
    {
        $registry = $this->assetRegistry;

        return $registry?->purchase_cost === null ? null : max(0, (float) $registry->purchase_cost - (float) $registry->depreciation);
    }

    public function section(): BelongsTo
    {
        return $this->belongsTo(Section::class, 'section_id');
    }

    public function department(): BelongsTo
    {
        return $this->belongsTo(departments::class, 'department_id');
    }

    public function room(): BelongsTo
    {
        return $this->belongsTo(Room::class, 'room_id');
    }

    public function countedBy(): BelongsTo
    {
        return $this->belongsTo(User::class, 'counted_by');
    }

    public function statusLogs(): HasMany
    {
        return $this->hasMany(EquipmentStatusLog::class, 'equipment_id')->latest('created_at');
    }

    public function ppmSchedules(): HasMany
    {
        return $this->hasMany(PpmSchedule::class, 'equipment_id');
    }

    public function jobs(): HasMany
    {
        return $this->hasMany(MaintenanceJob::class, 'equipment_id');
    }

    /** Still in the register (not de-commissioned). */
    public function scopeInService(Builder $query): Builder
    {
        return $query->whereNull('removed_at');
    }

    public function scopeSearch(Builder $query, ?string $term): Builder
    {
        return $query->when($term, fn (Builder $q) => $q->where(fn (Builder $w) => $w
            ->where('name', 'like', "%{$term}%")
            ->orWhere('code', 'like', "%{$term}%")
            ->orWhereHas('assetRegistry', fn (Builder $r) => $r
                ->where('serial_number', 'like', "%{$term}%")
                ->orWhere('model', 'like', "%{$term}%"))));
    }

    /** Installation date plus expected life; null when the life span is unknown. */
    public function replacementDate(): ?Carbon
    {
        return $this->expected_life_years
            ? $this->installation_date->copy()->addYears($this->expected_life_years)
            : null;
    }

    public function isDueForReplacement(): bool
    {
        $date = $this->replacementDate();

        return $date !== null && $date->lte(now()->addMonths(config('engineering.replacement_warning_months')));
    }

    public function manufacturerContact(): string
    {
        $contact = collect([$this->manufacturer_email, $this->manufacturer_phone, $this->manufacturer_postal])->filter();

        return $contact->isEmpty() ? 'Contact not available' : $contact->implode(' · ');
    }
}
