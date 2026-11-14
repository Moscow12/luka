<?php

namespace App\Models\Engineering;

use App\Enums\Engineering\SatisfactionRating;
use App\Models\User;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class Feedback extends EngineeringModel
{
    protected $table = 'eng_feedback';

    protected $casts = [
        'rating' => SatisfactionRating::class,
        'resent_for_rework' => 'boolean',
        'superseded' => 'boolean',
    ];

    public function job(): BelongsTo
    {
        return $this->belongsTo(MaintenanceJob::class, 'job_id');
    }

    public function givenBy(): BelongsTo
    {
        return $this->belongsTo(User::class, 'given_by');
    }
}
