<?php

namespace App\Models\Engineering;

use App\Models\User;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\HasMany;

class SparesRequisition extends EngineeringModel
{
    protected $table = 'eng_spares_requisitions';

    protected $casts = ['requested_at' => 'datetime'];

    public function job(): BelongsTo
    {
        return $this->belongsTo(MaintenanceJob::class, 'job_id');
    }

    public function lines(): HasMany
    {
        return $this->hasMany(SparesLine::class, 'spares_requisition_id')->orderBy('seq');
    }

    public function requestedBy(): BelongsTo
    {
        return $this->belongsTo(User::class, 'requested_by');
    }

    /** Still has lines the store keeper has not dealt with. */
    public function isPending(): bool
    {
        return in_array($this->status, ['requested', 'partially_issued'], true);
    }
}
