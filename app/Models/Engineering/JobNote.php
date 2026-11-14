<?php

namespace App\Models\Engineering;

use App\Models\Concerns\WriteOnce;
use App\Models\User;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class JobNote extends EngineeringModel
{
    use WriteOnce;

    public const UPDATED_AT = null;

    protected $table = 'eng_job_notes';

    public function author(): BelongsTo
    {
        return $this->belongsTo(User::class, 'created_by');
    }
}
