<?php

namespace App\Models\Engineering;

use App\Models\Concerns\WriteOnce;
use App\Models\User;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class JobCardApproval extends EngineeringModel
{
    use WriteOnce;

    public const UPDATED_AT = null;

    protected $table = 'eng_job_card_approvals';

    public function actor(): BelongsTo
    {
        return $this->belongsTo(User::class, 'actor_id');
    }
}
