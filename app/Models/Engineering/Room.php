<?php

namespace App\Models\Engineering;

use App\Models\departments;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\HasMany;

class Room extends EngineeringModel
{
    protected $table = 'eng_rooms';

    public function department(): BelongsTo
    {
        return $this->belongsTo(departments::class, 'department_id');
    }

    public function equipment(): HasMany
    {
        return $this->hasMany(Equipment::class, 'room_id');
    }

    /** "Block A / 1st floor / LAB-12" */
    public function label(): string
    {
        return collect([$this->block, $this->floor, $this->name])->filter()->implode(' / ');
    }
}
