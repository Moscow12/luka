<?php

namespace App\Models\Engineering;

use App\Models\Employee;
use App\Models\User;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class RootCauseAnalysis extends EngineeringModel
{
    protected $table = 'eng_rcas';

    protected $casts = [
        'session_date' => 'date',
        'action_target_date' => 'date',
        'action_done_at' => 'datetime',
        'completed_at' => 'datetime',
        'probable_causes' => 'array',
        'participants' => 'array',
        'whys' => 'array',
    ];

    public function job(): BelongsTo
    {
        return $this->belongsTo(MaintenanceJob::class, 'job_id');
    }

    public function facilitator(): BelongsTo
    {
        return $this->belongsTo(User::class, 'facilitator_id');
    }

    public function responsible(): BelongsTo
    {
        return $this->belongsTo(Employee::class, 'responsible_employee_id');
    }

    public function isEditable(): bool
    {
        return $this->status === 'draft';
    }

    /** A preventive action past its target date and not yet done. */
    public function actionOverdue(): bool
    {
        return $this->action_target_date && ! $this->action_done_at && $this->action_target_date->isPast();
    }
}
