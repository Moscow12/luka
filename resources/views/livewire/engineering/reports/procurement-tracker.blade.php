@php
    use App\Support\Engineering\Eng;
    use App\Support\Engineering\Lookups;

    // Status colours are reserved for state and always paired with an icon and a label
    $statusIcons = ['complete' => 'fa-circle-check', 'on_track' => 'fa-clock', 'delayed' => 'fa-triangle-exclamation'];
    $statusBars = ['complete' => 'bg-success', 'on_track' => 'bg-warning', 'delayed' => 'bg-danger'];
    $largest = max(1, max($summary['counts']));
@endphp

<x-engineering.page title="Procurement tracker" :subtitle="'Job cards submitted '.$periodLabel.' · as of '.$today">
    <x-slot:actions>
        <button type="button" class="btn btn-outline-success" wire:click="export"><i class="fa-solid fa-file-excel me-1"></i>Export to Excel</button>
    </x-slot:actions>

    <div class="card mb-3">
        <div class="card-body">
            <div class="row g-2 align-items-end">
                @include('livewire.engineering.partials.period-filter')
                <div class="col-6 col-md-3">
                    <label class="form-label small">Section</label>
                    <select class="form-select" wire:model.live="filterSection">
                        <option value="">All</option>
                        @foreach($sections as $section)<option value="{{ $section->id }}">{{ $section->name }}</option>@endforeach
                    </select>
                </div>
                <div class="col-6 col-md-3">
                    <label class="form-label small">Status</label>
                    <select class="form-select" wire:model.live="filterStatus">
                        <option value="">All</option>
                        @foreach(Lookups::PROCUREMENT_STATUSES as $value => $meta)<option value="{{ $value }}">{{ $meta['label'] }}</option>@endforeach
                    </select>
                </div>
            </div>
        </div>
    </div>

    {{-- Summary of status: one bar per status, count written beside it --}}
    <div class="row g-3 mb-3">
        <div class="col-lg-7">
            <div class="card h-100">
                <div class="card-header"><h6 class="mb-0">Summary of status · {{ $summary['total'] }} job {{ Str::plural('card', $summary['total']) }}</h6></div>
                <div class="card-body">
                    @foreach(Lookups::PROCUREMENT_STATUSES as $key => $meta)
                        @php $count = $summary['counts'][$key]; @endphp
                        <div class="d-flex align-items-center gap-3 mb-3" title="{{ $meta['label'] }}: {{ $count }} of {{ $summary['total'] }}">
                            <div class="text-nowrap" style="width: 110px;"><i class="fa-solid {{ $statusIcons[$key] }} me-1 text-muted"></i>{{ $meta['label'] }}</div>
                            <div class="flex-grow-1">
                                <div class="{{ $statusBars[$key] }} rounded-end" style="height: 14px; width: {{ $count ? max(2, round($count / $largest * 100)) : 0 }}%;"></div>
                            </div>
                            <div class="fw-semibold text-end" style="width: 40px;">{{ $count }}</div>
                        </div>
                    @endforeach
                </div>
            </div>
        </div>
        <div class="col-lg-5">
            <div class="card h-100">
                <div class="card-header"><h6 class="mb-0">Average days per stage</h6></div>
                <ul class="list-group list-group-flush">
                    @foreach(['submission_to_approval' => 'Submission → approval', 'approval_to_order' => 'Approval → order', 'order_to_delivery' => 'Order → delivery'] as $key => $label)
                        <li class="list-group-item d-flex justify-content-between"><span>{{ $label }}</span><span class="fw-semibold">{{ $summary[$key] ?? '—' }}</span></li>
                    @endforeach
                </ul>
            </div>
        </div>
    </div>

    <div class="card">
        <div class="table-responsive">
            <table class="table table-hover table-sm align-middle mb-0">
                <thead class="table-light">
                    <tr>
                        <th>S/N</th><th>Job card</th><th>Responsible</th><th>Equipment / problem</th><th>Submitted</th><th>Approved</th>
                        <th>Proforma</th><th>Tender / LPO</th><th>Supplier</th><th>Delivery</th><th class="text-center">Days</th><th>Status</th>
                    </tr>
                </thead>
                <tbody>
                    @forelse($cards as $card)
                        @php $status = $card->procurementStatus(); $meta = Lookups::PROCUREMENT_STATUSES[$status]; @endphp
                        <tr wire:key="card-{{ $card->id }}">
                            <td>{{ $loop->iteration }}</td>
                            <td class="text-nowrap">
                                <a href="{{ route('engineering.jobs.card', $card->job) }}" class="fw-semibold text-decoration-none">{{ $card->number }}</a>
                                <div class="small text-muted">{{ $card->job->number }}</div>
                            </td>
                            <td>{{ Eng::name($card->requestingEngineer) }}</td>
                            <td>{{ $card->job->equipmentName() }}<div class="small text-muted">{{ Str::limit($card->description ?: $card->job->problem_description, 60) }}</div></td>
                            <td class="text-nowrap">{{ Eng::dt($card->submitted_at, 'd M Y') }}</td>
                            <td class="text-nowrap">{{ Eng::dt($card->approved_at, 'd M Y') }}</td>
                            <td class="small text-nowrap">{{ Eng::date($card->proforma_requested_at, 'd M') }} → {{ Eng::date($card->proforma_received_at, 'd M') }}</td>
                            <td class="small text-nowrap">{{ Eng::date($card->tender_opened_at, 'd M') }} → {{ Eng::date($card->tender_approved_at, 'd M') }}</td>
                            <td>{{ $card->supplier_name ?? '—' }}</td>
                            <td class="small text-nowrap">Exp. {{ Eng::date($card->delivery_expected_at, 'd M') }}<div>Act. {{ Eng::date($card->delivery_actual_at, 'd M') }}</div></td>
                            <td class="text-center" title="Days since the job card was submitted">{{ $card->daysSinceSubmitted() }}</td>
                            <td><span class="badge {{ $meta['badge'] }}"><i class="fa-solid {{ $statusIcons[$status] }} me-1"></i>{{ $meta['label'] }}</span></td>
                        </tr>
                    @empty
                        <x-engineering.empty colspan="12" icon="fa-truck-ramp-box" text="No job cards were submitted in this period." />
                    @endforelse
                </tbody>
            </table>
        </div>
    </div>
</x-engineering.page>
