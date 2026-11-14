<?php

namespace App\Models\Engineering;

use App\Models\User;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class PpmEntry extends EngineeringModel
{
    protected $table = 'eng_ppm_entries';

    protected $casts = [
        'mandatory' => 'boolean',
        'done' => 'boolean',
        'not_applicable' => 'boolean',
        'ticked_at' => 'datetime',
    ];

    public function tickedBy(): BelongsTo
    {
        return $this->belongsTo(User::class, 'ticked_by');
    }

    /** Ticked, or marked not applicable with a comment. Optional tasks never block completion. */
    public function isSatisfied(): bool
    {
        return ! $this->mandatory || $this->done || ($this->not_applicable && filled($this->comment));
    }
}
