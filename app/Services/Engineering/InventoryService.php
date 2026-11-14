<?php

namespace App\Services\Engineering;

use App\Models\Engineering\Equipment;
use App\Models\Engineering\EquipmentStatusLog;
use App\Models\Engineering\RoomStaff;
use App\Models\User;
use Illuminate\Support\Facades\DB;

class InventoryService
{
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
    }
}
