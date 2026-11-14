<?php

namespace App\Models\Engineering;

use App\Models\Employee;
use App\Models\User;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class FiveSScore extends EngineeringModel
{
    protected $table = 'eng_five_s_scores';

    protected $casts = [
        'week_start' => 'date',
        'score_pct' => 'float',
    ];

    public function employee(): BelongsTo
    {
        return $this->belongsTo(Employee::class, 'employee_id');
    }

    public function recordedBy(): BelongsTo
    {
        return $this->belongsTo(User::class, 'recorded_by');
    }
}
