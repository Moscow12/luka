<?php

namespace App\Services\Assets;

use App\Models\assetregistry;
use App\Models\AssetTransfer;
use App\Models\departments;
use App\Models\Engineering\Equipment;
use App\Models\Engineering\EquipmentStatusLog;
use App\Models\facilitylocation;
use App\Models\User;
use App\Services\Engineering\EngineeringAccess;
use App\Services\Engineering\InventoryService;
use Illuminate\Support\Facades\DB;

/**
 * Moving an asset register row (or part of a lot) to another department / location.
 * The register row and its engineering profile change only when a transfer is approved.
 */
class AssetTransferService
{
    public function __construct(protected InventoryService $inventory) {}

    public static function canRequest(User $user): bool
    {
        return $user->isSuperAdmin() || $user->can('manage-assets') || EngineeringAccess::canManageInventory($user);
    }

    public static function canApprove(User $user): bool
    {
        return $user->isSuperAdmin() || $user->can('approve-asset-transfer');
    }

    public function request(assetregistry $registry, string $toDepartmentId, string $toLocationId, int $quantity, string $reason, User $actor): AssetTransfer
    {
        if (! static::canRequest($actor)) {
            throw new AssetActionException('You do not have permission to request asset transfers.');
        }

        return DB::transaction(function () use ($registry, $toDepartmentId, $toLocationId, $quantity, $reason, $actor) {
            $registry = assetregistry::lockForUpdate()->findOrFail($registry->id);

            if ($registry->status === 'disposed') {
                throw new AssetActionException('A disposed asset cannot be transferred.');
            }

            if ($registry->pendingTransfer()->exists()) {
                throw new AssetActionException('This asset already has a transfer waiting for approval.');
            }

            $available = max(1, (int) $registry->quantity);
            if ($quantity < 1 || $quantity > $available) {
                throw new AssetActionException("Quantity must be between 1 and {$available}.");
            }

            $department = departments::find($toDepartmentId);
            if (! $department || ! $department->isActive()) {
                throw new AssetActionException('Choose an active receiving department.');
            }

            $location = facilitylocation::find($toLocationId);
            if (! $location || ! $location->isActive()) {
                throw new AssetActionException('Choose an active receiving location.');
            }

            if ($location->department_id && $location->department_id !== $toDepartmentId) {
                throw new AssetActionException('That location belongs to another department.');
            }

            if ($registry->department_id === $toDepartmentId && $registry->facility_location_id === $toLocationId) {
                throw new AssetActionException('The asset is already in that department and location.');
            }

            return AssetTransfer::create([
                'assetregistry_id' => $registry->id,
                'quantity' => $quantity,
                'from_department_id' => $registry->department_id,
                'from_facility_location_id' => $registry->facility_location_id,
                'to_department_id' => $toDepartmentId,
                'to_facility_location_id' => $toLocationId,
                'reason' => $reason,
                'status' => AssetTransfer::PENDING,
                'requested_by' => $actor->id,
            ]);
        });
    }

    public function approve(AssetTransfer $transfer, User $actor, ?string $note = null): AssetTransfer
    {
        if (! static::canApprove($actor)) {
            throw new AssetActionException('You do not have permission to approve asset transfers.');
        }

        return DB::transaction(function () use ($transfer, $actor, $note) {
            $transfer = AssetTransfer::lockForUpdate()->findOrFail($transfer->id);

            if (! $transfer->isPending()) {
                throw new AssetActionException('This transfer has already been decided.');
            }

            $registry = assetregistry::lockForUpdate()->findOrFail($transfer->assetregistry_id);
            $location = facilitylocation::withTrashed()->findOrFail($transfer->to_facility_location_id);
            $available = max(1, (int) $registry->quantity);

            if ($registry->status === 'disposed') {
                throw new AssetActionException('The asset has been disposed since the request was made.');
            }

            if ($transfer->quantity > $available) {
                throw new AssetActionException("Only {$available} item(s) remain on this asset record; reject this request and raise a new one.");
            }

            $destination = [
                'department_id' => $transfer->to_department_id,
                'facility_location_id' => $location->id,
                'building_id' => $location->building_id,
                'workstation_id' => $location->workstation_id,
            ];

            $newRegistryId = null;

            if ($transfer->quantity >= $available) {
                $registry->update($destination);
                EquipmentSync::fromRegistry($registry);
            } else {
                $newRegistryId = $this->split($registry, $transfer->quantity, $destination, $actor)->id;
            }

            $transfer->update([
                'status' => AssetTransfer::APPROVED,
                'decided_by' => $actor->id,
                'decided_at' => now(),
                'decision_note' => $note,
                'new_assetregistry_id' => $newRegistryId,
            ]);

            return $transfer;
        });
    }

    public function reject(AssetTransfer $transfer, User $actor, string $note): AssetTransfer
    {
        if (! static::canApprove($actor)) {
            throw new AssetActionException('You do not have permission to reject asset transfers.');
        }

        return $this->close($transfer, AssetTransfer::REJECTED, $actor, $note);
    }

    public function cancel(AssetTransfer $transfer, User $actor): AssetTransfer
    {
        if ($transfer->requested_by !== $actor->id) {
            throw new AssetActionException('Only the person who requested the transfer can cancel it.');
        }

        return $this->close($transfer, AssetTransfer::CANCELLED, $actor, null);
    }

    protected function close(AssetTransfer $transfer, string $status, User $actor, ?string $note): AssetTransfer
    {
        return DB::transaction(function () use ($transfer, $status, $actor, $note) {
            $transfer = AssetTransfer::lockForUpdate()->findOrFail($transfer->id);

            if (! $transfer->isPending()) {
                throw new AssetActionException('This transfer has already been decided.');
            }

            $transfer->update([
                'status' => $status,
                'decided_by' => $actor->id,
                'decided_at' => now(),
                'decision_note' => $note,
            ]);

            return $transfer;
        });
    }

    /**
     * Part of a lot moves: the original row keeps the rest, a new row carries the moved items with
     * its share of cost and depreciation. An engineering profile is copied for the new row.
     */
    protected function split(assetregistry $registry, int $quantity, array $destination, User $actor): assetregistry
    {
        $total = max(1, (int) $registry->quantity);
        $share = $quantity / $total;

        $moved = $registry->replicate();
        $moved->fill($destination + [
            'quantity' => $quantity,
            // Serial numbers are unique per row; a lot split keeps the serial on the original
            'serial_number' => null,
            'purchase_cost' => $registry->purchase_cost === null ? null : round((float) $registry->purchase_cost * $share, 2),
            'depreciation' => $registry->depreciation === null ? null : round((float) $registry->depreciation * $share, 2),
            'added_by' => $actor->id,
        ]);
        $moved->save();

        $registry->update([
            'quantity' => $total - $quantity,
            'purchase_cost' => $registry->purchase_cost === null ? null : (float) $registry->purchase_cost - (float) $moved->purchase_cost,
            'depreciation' => $registry->depreciation === null ? null : (float) $registry->depreciation - (float) $moved->depreciation,
        ]);

        EquipmentSync::fromRegistry($registry);

        $equipment = Equipment::where('asset_registry_id', $registry->id)->first();

        if ($equipment) {
            $prefix = str_contains($equipment->code, '/') ? substr($equipment->code, 0, strrpos($equipment->code, '/')) : $equipment->code;

            $copy = $equipment->replicate(['status_changed_at']);
            $copy->fill([
                'asset_registry_id' => $moved->id,
                'code' => $this->inventory->nextCode($prefix),
                'department_id' => $moved->department_id,
                'room_id' => $moved->facility_location_id,
                'quantity' => $quantity,
                'counted_by' => $actor->id,
                'counted_at' => now()->toDateString(),
                'status_changed_at' => now(),
                'flagged_for_removal' => false,
            ]);
            $copy->save();

            EquipmentStatusLog::create([
                'equipment_id' => $copy->id,
                'status' => $copy->status,
                'note' => "Split from {$equipment->code} by asset transfer.",
                'changed_by' => $actor->id,
            ]);

            $moved->update(['codeno' => $copy->code]);
        }

        return $moved;
    }
}
