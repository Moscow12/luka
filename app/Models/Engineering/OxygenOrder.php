<?php

namespace App\Models\Engineering;

use App\Models\Employee;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class OxygenOrder extends EngineeringModel
{
    protected $table = 'eng_oxygen_orders';

    protected $casts = ['delivered_at' => 'datetime'];

    public function job(): BelongsTo
    {
        return $this->belongsTo(MaintenanceJob::class, 'job_id');
    }

    public function deliveredBy(): BelongsTo
    {
        return $this->belongsTo(Employee::class, 'delivered_by');
    }

    public function isDelivered(): bool
    {
        return $this->delivered_at !== null;
    }
}
