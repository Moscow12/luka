<?php

namespace App\Livewire\Assets;

use App\Models\AssetTransfer;
use App\Services\Assets\AssetActionException;
use App\Services\Assets\AssetTransferService;
use Illuminate\Database\Eloquent\Builder;
use Illuminate\Support\Facades\Auth;
use Livewire\Attributes\On;
use Livewire\Attributes\Url;
use Livewire\Component;
use Livewire\WithPagination;

/**
 * Asset transfers between departments / locations: approvers decide pending requests,
 * requesters follow (and may cancel) their own.
 */
class AssetTransfers extends Component
{
    use WithPagination;

    protected $paginationTheme = 'bootstrap';

    public const TABS = ['pending' => 'Pending', 'mine' => 'My requests', 'history' => 'History'];

    #[Url]
    public string $tab = 'pending';

    public string $search = '';

    // Decision modal
    public ?string $decidingId = null;

    public string $decision = 'approve';

    public string $note = '';

    public function mount(): void
    {
        $user = Auth::user();
        abort_unless(AssetTransferService::canApprove($user) || AssetTransferService::canRequest($user), 403);

        if (! array_key_exists($this->tab, self::TABS)) {
            $this->tab = 'pending';
        }
    }

    public function updating(string $name): void
    {
        if (in_array($name, ['tab', 'search'], true)) {
            $this->resetPage();
        }
    }

    #[On('asset-transfer-requested')]
    public function refreshList(): void
    {
        // re-render
    }

    public function openDecision(string $transferId, string $decision): void
    {
        abort_unless(AssetTransferService::canApprove(Auth::user()), 403);

        $this->resetErrorBag();
        $this->decidingId = $transferId;
        $this->decision = $decision === 'reject' ? 'reject' : 'approve';
        $this->note = '';
    }

    public function closeDecision(): void
    {
        $this->reset(['decidingId', 'note']);
    }

    public function decide(AssetTransferService $service): void
    {
        $this->validate([
            'note' => [$this->decision === 'reject' ? 'required' : 'nullable', 'string', 'max:500'],
        ], ['note.required' => 'Give the reason for rejecting.']);

        $transfer = AssetTransfer::findOrFail($this->decidingId);

        try {
            $this->decision === 'approve'
                ? $service->approve($transfer, Auth::user(), $this->note ?: null)
                : $service->reject($transfer, Auth::user(), $this->note);
        } catch (AssetActionException $e) {
            $this->addError('note', $e->getMessage());

            return;
        }

        session()->flash('success', $this->decision === 'approve' ? 'Transfer approved; the asset register has been updated.' : 'Transfer rejected.');
        $this->closeDecision();
    }

    public function cancel(string $transferId, AssetTransferService $service): void
    {
        try {
            $service->cancel(AssetTransfer::findOrFail($transferId), Auth::user());
            session()->flash('success', 'Transfer request cancelled.');
        } catch (AssetActionException $e) {
            session()->flash('error', $e->getMessage());
        }
    }

    public function render()
    {
        $user = Auth::user();
        $canApprove = AssetTransferService::canApprove($user);

        $transfers = AssetTransfer::query()
            ->with([
                'assetRegistry.asset:id,name', 'newAssetRegistry:id,codeno',
                'fromDepartment:id,name', 'toDepartment:id,name',
                'fromLocation.building:id,name', 'toLocation.building:id,name',
                'requestedBy:id,first_name,surname', 'decidedBy:id,first_name,surname',
            ])
            ->when($this->tab === 'pending', fn (Builder $q) => $q->pending()->when(! $canApprove, fn (Builder $w) => $w->where('requested_by', $user->id)))
            ->when($this->tab === 'mine', fn (Builder $q) => $q->where('requested_by', $user->id))
            ->when($this->tab === 'history', fn (Builder $q) => $q->where('status', '!=', AssetTransfer::PENDING)->when(! $canApprove, fn (Builder $w) => $w->where('requested_by', $user->id)))
            ->when(trim($this->search), fn (Builder $q, $term) => $q->whereHas('assetRegistry', fn (Builder $r) => $r
                ->where('codeno', 'like', "%{$term}%")
                ->orWhere('serial_number', 'like', "%{$term}%")
                ->orWhereHas('asset', fn (Builder $a) => $a->where('name', 'like', "%{$term}%"))))
            ->latest()
            ->paginate(15);

        return view('livewire.assets.asset-transfers', [
            'transfers' => $transfers,
            'canApprove' => $canApprove,
            'pendingCount' => $canApprove ? AssetTransfer::pending()->count() : AssetTransfer::pending()->where('requested_by', $user->id)->count(),
            'deciding' => $this->decidingId ? AssetTransfer::with(['assetRegistry.asset:id,name', 'toDepartment:id,name', 'toLocation'])->find($this->decidingId) : null,
        ]);
    }
}
