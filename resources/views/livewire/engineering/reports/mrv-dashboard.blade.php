@php use App\Support\Engineering\Eng; use App\Support\Engineering\Lookups; @endphp

<x-engineering.page title="MRV dashboard" :subtitle="'Every requisition raised · '.$periodLabel">
    <x-slot:actions>
        <button type="button" class="btn btn-outline-secondary" onclick="window.print()"><i class="fa-solid fa-print me-1"></i>Print / PDF</button>
        <button type="button" class="btn btn-outline-success" wire:click="export"><i class="fa-solid fa-file-excel me-1"></i>Export all columns</button>
    </x-slot:actions>

    <div class="row g-3 mb-3">
        @foreach([
            ['Requisitions', $totals['all'], ''],
            ['Still open', $totals['open'], ''],
            ['Finished', $totals['finished'], ''],
            ['Average TAT', $totals['tat'] !== null ? number_format($totals['tat'], 2).' days' : '—', ''],
            ['Average satisfaction', $totals['satisfaction'] !== null ? $totals['satisfaction'].'%' : '—', ''],
            ['Sent back for rework', $totals['rework'], $totals['rework'] ? 'text-danger' : ''],
        ] as [$label, $value, $class])
            <div class="col-6 col-md-4 col-xl-2">
                <div class="card h-100"><div class="card-body py-3">
                    <div class="text-muted small">{{ $label }}</div>
                    <div class="h5 mb-0 {{ $class }}">{{ $value }}</div>
                </div></div>
            </div>
        @endforeach
    </div>

    <div class="card mb-3 eng-no-print">
        <div class="card-body">
            <div class="row g-2 align-items-end">
                @include('livewire.engineering.partials.period-filter')
                <div class="col-6 col-md-2">
                    <label class="form-label small">Section</label>
                    <select class="form-select" wire:model.live="filterSection">
                        <option value="">All</option>
                        @foreach($sections as $section)<option value="{{ $section->id }}">{{ $section->name }}</option>@endforeach
                    </select>
                </div>
                <div class="col-6 col-md-2">
                    <label class="form-label small">Status</label>
                    <select class="form-select" wire:model.live="filterStatus">
                        <option value="">All</option>
                        @foreach($statuses as $value => $label)<option value="{{ $value }}">{{ $label }}</option>@endforeach
                    </select>
                </div>
                <div class="col-md-2">
                    <label class="form-label small">Search</label>
                    <input type="search" class="form-control" wire:model.live.debounce.400ms="search" placeholder="MRV, equipment">
                </div>
            </div>
        </div>
    </div>

    <div class="card">
        <div class="table-responsive">
            <table class="table table-hover table-sm align-middle mb-0">
                <thead class="table-light">
                    <tr>
                        <th>S/N</th><th>MRV</th><th>Equipment</th><th>Location / requester</th><th>Work</th><th>Engineer</th>
                        <th>Assigned</th><th>Closed</th><th class="text-center">TAT</th><th>Status</th><th>Satisfaction</th><th class="text-center" title="Repairs of this equipment this week / month / year">Repeats</th>
                    </tr>
                </thead>
                <tbody>
                    @forelse($jobs as $job)
                        @php $repeat = $repeats[$job->equipment_id] ?? null; @endphp
                        <tr wire:key="job-{{ $job->id }}" class="{{ $job->is_rework ? 'eng-row-rework' : '' }}">
                            <td>{{ $jobs->firstItem() + $loop->index }}</td>
                            <td class="text-nowrap">
                                <a href="{{ route('engineering.jobs.summary', $job) }}" class="fw-semibold text-decoration-none">{{ $job->number }}</a>
                                <div class="small text-muted">{{ Eng::dt($job->requisition->requested_at, 'd M H:i') }}</div>
                            </td>
                            <td>{{ $job->equipmentName() }}<div class="small text-muted">{{ $job->equipment?->code }}</div></td>
                            <td>{{ $job->requisition->locationLabel() }}<div class="small text-muted">{{ $job->requisition->requester_name }}</div></td>
                            <td>{{ Lookups::label(Lookups::WORK_TYPES, $job->type_of_work) }}<div class="small text-muted">{{ $job->section->name }}</div></td>
                            <td>{{ Eng::name($job->engineer) }}<div class="small text-muted">by {{ $job->assignedBy?->full_name ?? '—' }}</div></td>
                            <td class="text-nowrap">{{ Eng::dt($job->assigned_at, 'd M H:i') }}</td>
                            <td class="text-nowrap">{{ Eng::dt($job->completed_at, 'd M H:i') }}</td>
                            <td class="text-center text-nowrap">
                                @if($job->tatDays() !== null){{ number_format($job->tatDays(), 2) }}
                                @else<span class="text-muted" title="Days open">{{ $job->daysOpen() }} d open</span>@endif
                            </td>
                            <td><x-engineering.job-badges :job="$job" /></td>
                            <td>
                                @if($job->currentFeedback)
                                    <span class="badge {{ $job->currentFeedback->rating->badgeClass() }}">{{ $job->currentFeedback->rating->label() }}</span> {{ $job->currentFeedback->score_pct }}%
                                @else
                                    <span class="text-muted">No score · 0%</span>
                                @endif
                            </td>
                            <td class="text-center text-nowrap">
                                @if($repeat)
                                    <span class="{{ $repeat['month'] >= \App\Livewire\Engineering\Reports\MrvDashboard::REPEAT_OFFENDER ? 'badge bg-danger' : '' }}">{{ $repeat['week'] }} / {{ $repeat['month'] }} / {{ $repeat['year'] }}</span>
                                @else — @endif
                            </td>
                        </tr>
                    @empty
                        <x-engineering.empty colspan="12" text="No requisitions were raised in this period." />
                    @endforelse
                </tbody>
            </table>
        </div>
        @if($jobs->hasPages())<div class="card-footer eng-no-print">{{ $jobs->links() }}</div>@endif
    </div>
    <p class="small text-muted mt-2 mb-0">TAT is measured from assignment to sign-out. Repeats count non-PPM requisitions for the equipment this week / month / year; {{ \App\Livewire\Engineering\Reports\MrvDashboard::REPEAT_OFFENDER }} or more in a month is flagged. The Excel export carries all report columns.</p>
</x-engineering.page>
