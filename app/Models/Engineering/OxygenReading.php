<?php

namespace App\Models\Engineering;

use App\Models\User;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class OxygenReading extends EngineeringModel
{
    protected $table = 'eng_oxygen_readings';

    protected $casts = [
        'reading_at' => 'datetime',
        'purity_pct' => 'float',
    ];

    public function plant(): BelongsTo
    {
        return $this->belongsTo(OxygenPlant::class, 'plant_id');
    }

    public function recordedBy(): BelongsTo
    {
        return $this->belongsTo(User::class, 'recorded_by');
    }

    public function isBelowMinimum(): bool
    {
        return $this->purity_pct < config('engineering.oxygen.min_purity');
    }
}
