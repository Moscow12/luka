<?php

namespace App\Models\Engineering;

use App\Models\Concerns\WriteOnce;
use App\Models\Employee;
use App\Models\User;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class JobAssignmentLog extends EngineeringModel
{
    use WriteOnce;

    public const UPDATED_AT = null;

    protected $table = 'eng_job_assignment_logs';

    public function fromEngineer(): BelongsTo
    {
        return $this->belongsTo(Employee::class, 'from_engineer_id');
    }

    public function toEngineer(): BelongsTo
    {
        return $this->belongsTo(Employee::class, 'to_engineer_id');
    }

    public function actor(): BelongsTo
    {
        return $this->belongsTo(User::class, 'actor_id');
    }
}
