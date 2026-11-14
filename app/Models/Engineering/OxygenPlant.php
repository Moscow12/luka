<?php

namespace App\Models\Engineering;

use App\Models\Employee;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\HasMany;

class OxygenPlant extends EngineeringModel
{
    protected $table = 'eng_oxygen_plants';

    public function responsible(): BelongsTo
    {
        return $this->belongsTo(Employee::class, 'responsible_employee_id');
    }

    public function readings(): HasMany
    {
        return $this->hasMany(OxygenReading::class, 'plant_id');
    }
}
