<?php

namespace App\Services\Assets;

use App\Models\assetregistry;

/**
 * The asset register row owns department, location and quantity; the engineering profile keeps
 * copies (for uptime and room queries). This is the only place those copies are written.
 */
class EquipmentSync
{
    public static function fromRegistry(assetregistry $registry): void
    {
        $equipment = $registry->equipment()->first();

        if (! $equipment) {
            return;
        }

        $equipment->fill([
            'department_id' => $registry->department_id,
            'room_id' => $registry->facility_location_id,
            'quantity' => max(1, (int) $registry->quantity),
        ]);

        if ($equipment->isDirty()) {
            $equipment->save();
        }
    }
}
