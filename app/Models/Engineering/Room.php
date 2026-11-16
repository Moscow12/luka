<?php

namespace App\Models\Engineering;

use App\Models\facilitylocation;
use Illuminate\Database\Eloquent\Relations\HasMany;

/**
 * Engineering's view of a facility location (the shared room register).
 */
class Room extends facilitylocation
{
    /** label() shows the building, so it is always loaded with the room (select building_id when narrowing columns). */
    protected $with = ['building:id,name'];

    public function equipment(): HasMany
    {
        return $this->hasMany(Equipment::class, 'room_id');
    }
}
