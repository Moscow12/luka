<?php

namespace App\Models\Engineering;

use App\Models\User;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class TechnicalReport extends EngineeringModel
{
    protected $table = 'eng_technical_reports';

    protected $casts = ['date_reported' => 'date'];

    public function job(): BelongsTo
    {
        return $this->belongsTo(MaintenanceJob::class, 'job_id');
    }

    public function reportedBy(): BelongsTo
    {
        return $this->belongsTo(User::class, 'reported_by');
    }
}
