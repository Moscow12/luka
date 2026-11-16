<?php

namespace App\Livewire\Assets;

use App\Models\assetregistry;
use App\Models\departments;
use App\Models\facilitylocation;
use App\Services\Assets\AssetActionException;
use App\Services\Assets\AssetTransferService;
use Illuminate\Support\Facades\Auth;
use Illuminate\Validation\Rule;
use Livewire\Attributes\On;
use Livewire\Component;

/**
 * Request form for moving an asset register row (or part of a lot) to another department / room.
 * Embedded on the asset register and the engineering inventory; opened with the
 * "open-asset-transfer" event carrying the register row id.
 */
class TransferRequest extends Component
{
    public ?string $registryId = null;

    public string $toDepartmentId = '';

    public string $toLocationId = '';

    public int $quantity = 1;

    public string $reason = '';

    public ?string $notice = null;

    #[On('open-asset-transfer')]
    public function open(string $registryId): void
    {
        abort_unless(AssetTransferService::canRequest(Auth::user()), 403);

        $registry = assetregistry::findOrFail($registryId);

        $this->resetErrorBag();
        $this->registryId = $registry->id;
        $this->toDepartmentId = '';
        $this->toLocationId = '';
        $this->quantity = max(1, (int) $registry->quantity);
        $this->reason = '';
        $this->notice = null;
    }

    public function close(): void
    {
        $this->registryId = null;
    }

    public function updatedToDepartmentId(): void
    {
        $this->toLocationId = '';
    }

    public function submit(AssetTransferService $service): void
    {
        $registry = assetregistry::findOrFail($this->registryId);

        $this->validate([
            'toDepartmentId' => ['required', Rule::exists('departments', 'id')],
            'toLocationId' => ['required', Rule::exists('facilitylocations', 'id')->whereNull('deleted_at')],
            'quantity' => ['required', 'integer', 'min:1', 'max:'.max(1, (int) $registry->quantity)],
            'reason' => ['required', 'string', 'min:5', 'max:500'],
        ], [], ['toDepartmentId' => 'receiving department', 'toLocationId' => 'receiving location']);

        try {
            $service->request($registry, $this->toDepartmentId, $this->toLocationId, $this->quantity, $this->reason, Auth::user());
        } catch (AssetActionException $e) {
            $this->addError('reason', $e->getMessage());

            return;
        }

        $this->notice = "Transfer of {$registry->label()} requested. It moves once approved under Assets > Transfers.";
        $this->registryId = null;
        $this->dispatch('asset-transfer-requested');
    }

    public function render()
    {
        $registry = $this->registryId
            ? assetregistry::with(['asset:id,name', 'department:id,name', 'facilityLocation.building:id,name'])->find($this->registryId)
            : null;

        return view('livewire.assets.transfer-request', [
            'registry' => $registry,
            'departments' => $registry ? departments::active()->orderBy('name')->get(['id', 'name']) : collect(),
            'locations' => $registry && $this->toDepartmentId
                ? facilitylocation::active()->forDepartment($this->toDepartmentId)->with('building:id,name')->orderBy('name')->get()
                : collect(),
        ]);
    }
}
