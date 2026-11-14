<?php

namespace App\Models\Engineering;

use App\Models\User;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class SafetyTest extends EngineeringModel
{
    protected $table = 'eng_safety_tests';

    protected $casts = [
        'completed' => 'boolean',
        'not_applicable' => 'boolean',
        'completed_at' => 'datetime',
    ];

    public function completedBy(): BelongsTo
    {
        return $this->belongsTo(User::class, 'completed_by');
    }

    /** Done, or explicitly marked not applicable. */
    public function isSatisfied(): bool
    {
        return $this->completed || $this->not_applicable;
    }
}
