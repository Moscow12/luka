<?php

namespace App\Services\Engineering;

use App\Models\assetregistry;
use App\Models\Engineering\Equipment;
use App\Models\Engineering\EquipmentStatusLog;
use App\Models\Engineering\RoomStaff;
use App\Models\facilitylocation;
use App\Models\User;
use Illuminate\Support\Facades\DB;

class InventoryService
{
    /**
     * New equipment that is not yet in the asset register: the register row and the engineering
     * profile are created together. Department, location, building and workstation come from the room.
     *
     * @param  array<string, mixed>  $registry  asset_class_id, asset_id, quantity, serial_number, model, vendor_id, vendor, purchase_date, purchase_cost, warranty_expiry_date, description
     * @param  array<string, mixed>  $profile  engineering fields (name, section_id, code, manufacturer, ...)
     */
    public function createWithRegistry(string $roomId, array $registry, array $profile, User $actor): Equipment
    {
        return DB::transaction(function () use ($roomId, $registry, $profile, $actor) {
            $room = facilitylocation::findOrFail($roomId);

            if (! $room->department_id) {
                throw new EngineeringActionException('Give this room a department before registering equipment in it.');
            }

            $row = assetregistry::create(array_merge($registry, [
                'facility_location_id' => $room->id,
                'building_id' => $room->building_id,
                'workstation_id' => $room->workstation_id,
                'department_id' => $room->department_id,
                'quantity' => max(1, (int) ($registry['quantity'] ?? 1)),
                'status' => 'active',
                'condition' => ($profile['condition_received'] ?? 'new') === 'new' ? 'new' : 'good',
                'codeno' => $profile['code'],
                'added_by' => $actor->id,
            ]));

            return $this->linkRegistry($row, $profile, $actor);
        });
    }

    /**
     * Gives an existing asset register row its engineering profile.
     *
     * @param  array<string, mixed>  $profile
     */
    public function linkRegistry(assetregistry $registry, array $profile, User $actor): Equipment
    {
        return DB::transaction(function () use ($registry, $profile, $actor) {
            $registry = assetregistry::lockForUpdate()->findOrFail($registry->id);

            if ($registry->equipment()->exists()) {
                throw new EngineeringActionException('This asset is already in the engineering inventory.');
            }

            if ($registry->status === 'disposed') {
                throw new EngineeringActionException('A disposed asset cannot be added to the engineering inventory.');
            }

            $equipment = Equipment::create(array_merge($profile, [
                'asset_registry_id' => $registry->id,
                'department_id' => $registry->department_id,
                'room_id' => $registry->facility_location_id,
                'quantity' => max(1, (int) $registry->quantity),
                'counted_by' => $actor->id,
            ]));

            if (! $registry->codeno) {
                $registry->update(['codeno' => $equipment->code]);
            }

            $this->changeStatus($equipment, 'working', null, $actor);

            return $equipment;
        });
    }

    /** Every status change is timestamped and kept: uptime is computed from this history. */
    public function changeStatus(Equipment $equipment, string $status, ?string $note, User $actor): void
    {
        if ($status !== 'working' && blank($note)) {
            throw new EngineeringActionException('Describe the defect when equipment is not fully working.');
        }

        if ($equipment->status === $status && $equipment->statusLogs()->exists()) {
            return;
        }

        DB::transaction(function () use ($equipment, $status, $note, $actor) {
            $equipment->update([
                'status' => $status,
                'status_note' => $note,
                'status_changed_at' => now(),
            ]);

            EquipmentStatusLog::create([
                'equipment_id' => $equipment->id,
                'status' => $status,
                'note' => $note,
                'changed_by' => $actor->id,
            ]);
        });
    }

    /** Next free code for a prefix: "BMC/LAB/CHM/CFM" => "BMC/LAB/CHM/CFM/003". */
    public function nextCode(string $prefix): string
    {
        $prefix = strtoupper(trim($prefix, " /\t"));

        $last = Equipment::where('code', 'like', $prefix.'/%')
            ->pluck('code')
            ->map(fn (string $code) => (int) substr($code, strlen($prefix) + 1))
            ->max() ?? 0;

        return sprintf('%s/%03d', $prefix, $last + 1);
    }

    /**
     * Makes an engineer responsible for a room, or a whole department when no room is given.
     * The previous assignment is closed, never deleted.
     */
    public function assignResponsibility(string $employeeId, string $departmentId, ?string $roomId, User $actor): RoomStaff
    {
        return DB::transaction(function () use ($employeeId, $departmentId, $roomId, $actor) {
            RoomStaff::current()
                ->where('department_id', $departmentId)
                ->where(fn ($q) => $roomId ? $q->where('room_id', $roomId) : $q->whereNull('room_id'))
                ->update(['effective_to' => now()]);

            return RoomStaff::create([
                'employee_id' => $employeeId,
                'department_id' => $departmentId,
                'room_id' => $roomId,
                'effective_from' => now(),
                'assigned_by' => $actor->id,
            ]);
        });
    }

    /** De-commissioning keeps the record and its history; it only leaves the active register. */
    public function remove(Equipment $equipment, string $reason, string $approvedBy, User $actor): void
    {
        if ($equipment->jobs()->open()->exists()) {
            throw new EngineeringActionException('This equipment has an open requisition and cannot be removed yet.');
        }

        $equipment->update([
            'removed_at' => now(),
            'removal_reason' => $reason,
            'removal_approved_by' => $approvedBy,
            'removed_by' => $actor->id,
            'flagged_for_removal' => false,
        ]);

        $equipment->ppmSchedules()->update(['status' => 'inactive']);

        // De-commissioning disposes of the register row too; it stays on record with its history
        $equipment->assetRegistry()->first()?->update(['status' => 'disposed', 'disposal_date' => now()->toDateString()]);
    }
}
