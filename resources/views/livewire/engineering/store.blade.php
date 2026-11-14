@php use App\Support\Engineering\Eng; use App\Support\Engineering\Lookups; @endphp

<x-engineering.page title="Engineering store" subtitle="Spares requisitions from jobs, and job cards on their way to purchasing">
    <x-slot:actions>
        <button type="button" class="btn btn-outline-success" wire:click="export"><i class="fa-solid fa-file-excel me-1"></i>Export to Excel</button>
    </x-slot:actions>

    <div class="d-flex flex-wrap align-items-center gap-2 mb-3">
        <ul class="nav nav-pills">
            <li class="nav-item">
                <button type="button" class="nav-link {{ $tab === 'spares' ? 'active' : '' }}" wire:click="setTab('spares')">
                    Spares requisitions @if($pendingSpares)<span class="badge rounded-pill bg-danger ms-1">{{ $pendingSpares }}</span>@endif
                </button>
            </li>
            <li class="nav-item">
                <button type="button" class="nav-link {{ $tab === 'cards' ? 'active' : '' }}" wire:click="setTab('cards')">
                    Job cards @if($pendingCards)<span class="badge rounded-pill bg-warning text-dark ms-1">{{ $pendingCards }}</span>@endif
                </button>
            </li>
        </ul>
        <div class="ms-md-auto d-flex gap-2">
            @if($tab === 'spares')
                <select class="form-select" wire:model.live="sparesFilter" style="width: auto;">
                    <option value="pending">Waiting for the store</option>
                    <option value="all">All requisitions</option>
                </select>
            @else
                <select class="form-select" wire:model.live="cardStatus" style="width: auto;">
                    <option value="">All statuses</option>
                    @foreach(collect(Lookups::JOB_CARD_STATUSES)->except('draft') as $value => $label)
                        <option value="{{ $value }}">{{ $label }}</option>
                    @endforeach
                </select>
            @endif
            <input type="search" class="form-control" placeholder="Search" wire:model.live.debounce.400ms="search" style="min-width: 200px;">
        </div>
    </div>

    @if($tab === 'spares')
        @forelse($requisitions as $sr)
            <div class="card mb-3" wire:key="sr-{{ $sr->id }}">
                <div class="card-header d-flex flex-wrap justify-content-between align-items-center gap-2">
                    <div>
                        <span class="fw-semibold">{{ $sr->number }}</span>
                        <span class="text-muted">· against</span>
                        <a href="{{ route('engineering.jobs.summary', $sr->job) }}" class="text-decoration-none">{{ $sr->job->number }}</a>
                        <span class="text-muted">· {{ $sr->job->equipmentName() }}</span>
                    </div>
                    <div class="small text-muted">
                        {{ Eng::name($sr->job->engineer) }} ({{ $sr->job->section->name }}) · {{ Eng::dt($sr->requested_at) }}
                        <span class="badge bg-light text-dark border ms-1">{{ ucfirst(str_replace('_', ' ', $sr->status)) }}</span>
                    </div>
                </div>
                <div class="table-responsive">
                    <table class="table align-middle mb-0">
                        <thead class="table-light">
                            <tr><th>S/N</th><th>Spare</th><th>UoM</th><th>Item code</th><th class="text-end">Requested</th><th class="text-end">Issued</th><th class="text-end">In store</th><th>Status</th><th class="text-end" style="min-width: 300px;">Action</th></tr>
                        </thead>
                        <tbody>
                            @foreach($sr->lines as $line)
                                @php
                                    $outstanding = $line->qty_requested - $line->qty_issued;
                                    $onHand = $line->product_id ? ($stock[$line->product_id] ?? 0) : null;
                                    $open = in_array($line->status, ['requested', 'partial']);
                                @endphp
                                <tr wire:key="line-{{ $line->id }}">
                                    <td>{{ $line->seq }}</td>
                                    <td>{{ $line->spare_name }} @unless($line->product_id)<span class="badge bg-light text-dark border">Non-stock</span>@endunless</td>
                                    <td>{{ $line->uom ?? '—' }}</td>
                                    <td>{{ $line->item_code ?? '—' }}</td>
                                    <td class="text-end">{{ $line->qty_requested + 0 }}</td>
                                    <td class="text-end">{{ $line->qty_issued + 0 }}</td>
                                    <td class="text-end {{ $onHand !== null && $onHand < $outstanding ? 'text-danger fw-semibold' : '' }}">{{ $onHand ?? '—' }}</td>
                                    <td>
                                        {{ Lookups::SPARES_LINE_STATUSES[$line->status] }}
                                        @if($line->reject_reason)<div class="small text-danger">{{ $line->reject_reason }}</div>@endif
                                    </td>
                                    <td class="text-end">
                                        @if($open && $isStoreKeeper)
                                            <div class="d-flex justify-content-end gap-1">
                                                <input type="number" min="0" max="{{ $outstanding }}" step="any" class="form-control form-control-sm" style="width: 80px;"
                                                       placeholder="{{ $outstanding + 0 }}" wire:model="issueQty.{{ $line->id }}" title="Quantity to issue (blank = all)">
                                                <button type="button" class="btn btn-sm btn-success" wire:click="issue('{{ $line->id }}')">Issue</button>
                                                <button type="button" class="btn btn-sm btn-outline-primary" wire:click="sendToProcurement('{{ $line->id }}')" title="Not in store: procure through a job card">Procure</button>
                                                <button type="button" class="btn btn-sm btn-outline-danger" wire:click="openReject('{{ $line->id }}')">Reject</button>
                                            </div>
                                        @elseif($line->issued_at)
                                            <span class="small text-muted">{{ Eng::dt($line->issued_at) }}</span>
                                        @endif
                                    </td>
                                </tr>
                            @endforeach
                        </tbody>
                    </table>
                </div>
            </div>
        @empty
            <div class="card"><div class="card-body text-center py-5 text-muted">
                <i class="fa-solid fa-boxes-stacked fa-2x mb-2 opacity-50"></i>
                <p class="mb-0">{{ $sparesFilter === 'pending' ? 'No spares requisitions are waiting for the store.' : 'No spares requisitions found.' }}</p>
            </div></div>
        @endforelse
        @if($requisitions->hasPages())<div class="mt-3">{{ $requisitions->links() }}</div>@endif
    @else
        <div class="card">
            <div class="table-responsive">
                <table class="table table-hover align-middle mb-0">
                    <thead class="table-light">
                        <tr><th>S/N</th><th>Job card</th><th>Equipment / area</th><th>Engineer</th><th>Certified</th><th>Authorised</th><th class="text-end">Est. grand total</th><th>Order / delivery</th><th>Status</th><th class="text-end">Action</th></tr>
                    </thead>
                    <tbody>
                        @forelse($cards as $card)
                            @php $certification = $card->certification(); $authorisation = $card->authorisation(); @endphp
                            <tr wire:key="card-{{ $card->id }}">
                                <td>{{ $cards->firstItem() + $loop->index }}</td>
                                <td class="text-nowrap">
                                    <span class="fw-semibold">{{ $card->number }}</span>
                                    <div class="small text-muted">{{ $card->job->number }} · {{ Eng::dt($card->submitted_at, 'd M Y') }}</div>
                                </td>
                                <td>{{ $card->job->equipmentName() }}<div class="small text-muted">{{ $card->job->section->name }}</div></td>
                                <td>{{ Eng::name($card->requestingEngineer) }}</td>
                                <td class="small">{{ $certification?->actor?->full_name ?? '—' }}<div class="text-muted">{{ $certification ? Eng::dt($certification->created_at, 'd M Y') : '' }}</div></td>
                                <td class="small">{{ $authorisation?->actor?->full_name ?? '—' }}<div class="text-muted">{{ $authorisation ? Eng::dt($authorisation->created_at, 'd M Y') : '' }}</div></td>
                                <td class="text-end text-nowrap">{{ Eng::money($card->grand_total) }}</td>
                                <td class="small">
                                    {{ $card->order_number ?? '—' }}
                                    <div class="text-muted">{{ $card->delivery_actual_at ? 'Delivered '.Eng::date($card->delivery_actual_at) : ($card->isApproved() ? $card->daysSinceSubmitted().' days outstanding' : '') }}</div>
                                </td>
                                <td><span class="badge {{ Lookups::JOB_CARD_STATUS_BADGES[$card->status] }}">{{ Lookups::JOB_CARD_STATUSES[$card->status] }}</span></td>
                                <td class="text-end"><a href="{{ route('engineering.jobs.card', $card->job) }}" class="btn btn-sm btn-outline-primary">View job card</a></td>
                            </tr>
                        @empty
                            <x-engineering.empty colspan="10" icon="fa-id-card" text="No job cards have been submitted yet." />
                        @endforelse
                    </tbody>
                </table>
            </div>
            @if($cards->hasPages())<div class="card-footer">{{ $cards->links() }}</div>@endif
        </div>
    @endif

    @if($rejectLineId)
        <x-engineering.modal title="Reject this line" close="closeReject" header="bg-danger text-white">
            <form wire:submit="reject">
                <div class="modal-body">
                    <label class="form-label">Reason <span class="text-danger">*</span></label>
                    <textarea class="form-control @error('rejectReason') is-invalid @enderror" rows="3" wire:model="rejectReason"></textarea>
                    @error('rejectReason') <div class="invalid-feedback">{{ $message }}</div> @enderror
                </div>
                <div class="modal-footer">
                    <button type="button" class="btn btn-secondary" wire:click="closeReject">Cancel</button>
                    <button type="submit" class="btn btn-danger">Reject line</button>
                </div>
            </form>
        </x-engineering.modal>
    @endif
</x-engineering.page>
