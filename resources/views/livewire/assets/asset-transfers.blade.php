<div>
    <div class="row">
        <div class="col-12">
            <div class="mb-5 d-md-flex justify-content-between align-items-center">
                <div>
                    <h1 class="mb-1 h2">Asset Transfers</h1>
                    <nav aria-label="breadcrumb">
                        <ol class="breadcrumb mb-0">
                            <li class="breadcrumb-item"><a href="{{ route('dashboard') }}">Dashboard</a></li>
                            <li class="breadcrumb-item active" aria-current="page">Asset Transfers</li>
                        </ol>
                    </nav>
                </div>
                <p class="text-muted small mb-0 mt-2 mt-md-0" style="max-width: 420px;">
                    An asset changes department or room only when its transfer is approved. Request a transfer from the asset register or the engineering inventory.
                </p>
            </div>
        </div>
    </div>

    @if (session()->has('success'))
        <div class="alert alert-success alert-dismissible fade show mb-4" role="alert">
            <i class="fa-solid fa-check-circle me-2"></i>{{ session('success') }}
            <button type="button" class="btn-close" data-bs-dismiss="alert" aria-label="Close"></button>
        </div>
    @endif
    @if (session()->has('error'))
        <div class="alert alert-danger alert-dismissible fade show mb-4" role="alert">
            <i class="fa-solid fa-triangle-exclamation me-2"></i>{{ session('error') }}
            <button type="button" class="btn-close" data-bs-dismiss="alert" aria-label="Close"></button>
        </div>
    @endif

    <div class="d-flex flex-wrap justify-content-between align-items-center gap-2 mb-3">
        <ul class="nav nav-pills">
            @foreach(\App\Livewire\Assets\AssetTransfers::TABS as $key => $label)
                <li class="nav-item">
                    <button type="button" class="nav-link {{ $tab === $key ? 'active' : '' }}" wire:click="$set('tab', '{{ $key }}')">
                        {{ $label }}
                        @if($key === 'pending' && $pendingCount)<span class="badge bg-warning text-dark ms-1">{{ $pendingCount }}</span>@endif
                    </button>
                </li>
            @endforeach
        </ul>
        <input type="search" class="form-control" style="max-width: 280px;" placeholder="Search item, code or serial" wire:model.live.debounce.300ms="search">
    </div>

    <div class="card">
        <div class="table-responsive">
            <table class="table table-hover align-middle mb-0">
                <thead class="table-light">
                    <tr>
                        <th>Requested</th><th>Item</th><th class="text-center">Qty</th><th>From</th><th>To</th><th>Reason</th><th>Status</th><th class="text-end">Action</th>
                    </tr>
                </thead>
                <tbody>
                    @forelse($transfers as $transfer)
                        <tr wire:key="transfer-{{ $transfer->id }}">
                            <td class="text-nowrap">{{ $transfer->created_at->format('d M Y H:i') }}<div class="small text-muted">{{ trim(($transfer->requestedBy?->first_name ?? '').' '.($transfer->requestedBy?->surname ?? '')) }}</div></td>
                            <td>{{ $transfer->assetRegistry?->label() }}
                                @if($transfer->newAssetRegistry)<div class="small text-muted">Split into new record {{ $transfer->newAssetRegistry->codeno }}</div>@endif
                            </td>
                            <td class="text-center">{{ $transfer->quantity }}</td>
                            <td>{{ $transfer->fromDepartment?->name }}<div class="small text-muted">{{ $transfer->fromLocation?->label() }}</div></td>
                            <td>{{ $transfer->toDepartment?->name }}<div class="small text-muted">{{ $transfer->toLocation?->label() }}</div></td>
                            <td class="small">{{ $transfer->reason }}</td>
                            <td>
                                <span class="badge {{ ['pending' => 'bg-warning text-dark', 'approved' => 'bg-success', 'rejected' => 'bg-danger', 'cancelled' => 'bg-secondary'][$transfer->status] }}">{{ \App\Models\AssetTransfer::STATUSES[$transfer->status] }}</span>
                                @if($transfer->decided_at)
                                    <div class="small text-muted">{{ $transfer->decided_at->format('d M Y') }} · {{ trim(($transfer->decidedBy?->first_name ?? '').' '.($transfer->decidedBy?->surname ?? '')) }}</div>
                                    @if($transfer->decision_note)<div class="small text-muted">{{ $transfer->decision_note }}</div>@endif
                                @endif
                            </td>
                            <td class="text-end text-nowrap">
                                @if($transfer->isPending())
                                    @if($canApprove)
                                        <button type="button" class="btn btn-sm btn-success" wire:click="openDecision('{{ $transfer->id }}', 'approve')"><i class="fa-solid fa-check"></i> Approve</button>
                                        <button type="button" class="btn btn-sm btn-outline-danger" wire:click="openDecision('{{ $transfer->id }}', 'reject')"><i class="fa-solid fa-xmark"></i> Reject</button>
                                    @endif
                                    @if($transfer->requested_by === auth()->id())
                                        <button type="button" class="btn btn-sm btn-outline-secondary" wire:click="cancel('{{ $transfer->id }}')" wire:confirm="Cancel this transfer request?">Cancel</button>
                                    @endif
                                @endif
                            </td>
                        </tr>
                    @empty
                        <tr><td colspan="8" class="text-center text-muted py-5"><i class="fa-solid fa-right-left fa-2x d-block mb-2 opacity-50"></i>No transfers here.</td></tr>
                    @endforelse
                </tbody>
            </table>
        </div>
        @if($transfers->hasPages())
            <div class="card-footer">{{ $transfers->links() }}</div>
        @endif
    </div>

    @if($deciding)
        <x-engineering.modal :title="($decision === 'approve' ? 'Approve' : 'Reject').' transfer'" close="closeDecision" :header="$decision === 'reject' ? 'bg-danger text-white' : ''">
            <form wire:submit="decide">
                <div class="modal-body">
                    <p class="mb-3">
                        <strong>{{ $deciding->assetRegistry?->label() }}</strong> × {{ $deciding->quantity }}
                        → {{ $deciding->toDepartment?->name }} · {{ $deciding->toLocation?->name }}
                    </p>
                    @if($decision === 'approve')
                        <p class="small text-muted">Approving updates the asset register and the engineering inventory now.</p>
                    @endif
                    <label class="form-label">Note @if($decision === 'reject')<span class="text-danger">*</span>@endif</label>
                    <textarea class="form-control @error('note') is-invalid @enderror" rows="2" wire:model="note"></textarea>
                    @error('note') <div class="invalid-feedback">{{ $message }}</div> @enderror
                </div>
                <div class="modal-footer">
                    <button type="button" class="btn btn-secondary" wire:click="closeDecision">Cancel</button>
                    <button type="submit" class="btn {{ $decision === 'approve' ? 'btn-success' : 'btn-danger' }}">{{ $decision === 'approve' ? 'Approve transfer' : 'Reject transfer' }}</button>
                </div>
            </form>
        </x-engineering.modal>
    @endif
</div>
