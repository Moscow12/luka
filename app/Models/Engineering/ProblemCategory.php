<?php

namespace App\Models\Engineering;

use Illuminate\Database\Eloquent\Relations\BelongsTo;

class ProblemCategory extends EngineeringModel
{
    protected $table = 'eng_problem_categories';

    public function section(): BelongsTo
    {
        return $this->belongsTo(Section::class, 'section_id');
    }

    /** On the emergency criteria list: choosing it forces EMERGENCY priority. */
    public function isEmergency(): bool
    {
        return filled($this->emergency_code);
    }
}
