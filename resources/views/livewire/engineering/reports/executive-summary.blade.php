@php use App\Support\Engineering\Eng; use App\Support\Engineering\Lookups; use App\Livewire\Engineering\Reports\ExecutiveSummary; @endphp

<x-engineering.page title="Department executive summary" :subtitle="'Engineering department report · '.$periodLabel">
    <x-slot:actions>
        <button type="button" class="btn btn-outline-secondary" onclick="window.print()"><i class="fa-solid fa-print me-1"></i>Print / PDF</button>
        <button type="button" class="btn btn-outline-success" wire:click="export"><i class="fa-solid fa-file-excel me-1"></i>Export to Excel</button>
    </x-slot:actions>

    <div class="card mb-3 eng-no-print">
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
            </div>
        </div>
    </div>

    <div class="card">
        <div class="table-responsive">
            <table class="table table-hover table-sm align-middle mb-0">
                <thead class="table-light">
                    <tr><th>S/N</th><th>MRV No.</th><th>Reported</th><th>Approved</th><th>Job card</th><th>Section</th><th>Department</th><th>Description</th><th>Sent to PSO</th><th>Status</th><th>Technician</th><th>Remarks</th><th>Satisfaction</th></tr>
                </thead>
                <tbody>
                    @forelse($jobs as $job)
                        @php $card = $job->jobCard; @endphp
                        <tr wire:key="job-{{ $job->id }}">
                            <td>{{ $jobs->firstItem() + $loop->index }}</td>
                            <td class="text-nowrap"><a href="{{ route('engineering.jobs.summary', $job) }}" class="fw-semibold text-decoration-none">{{ $job->number }}</a></td>
                            <td class="text-nowrap">{{ Eng::dt($job->requisition->requested_at, 'd M') }}</td>
                            <td class="text-nowrap">{{ Eng::dt($card?->approved_at, 'd M') }}</td>
                            <td class="text-nowrap">{{ $card?->number ?? '—' }}</td>
                            <td>{{ $job->section->name }}</td>
                            <td>{{ $job->requisition->department?->name ?? '—' }}</td>
                            <td style="min-width: 220px;">{{ $job->equipmentName() }}<div class="small text-muted">{{ Str::limit($job->problem_description, 70) }}</div></td>
                            <td class="text-nowrap">
                                @if($card?->isApproved())
                                    {{ Eng::dt($card->approved_at, 'd M') }}
                                    @php $meta = Lookups::PROCUREMENT_STATUSES[$card->procurementStatus()]; @endphp
                                    <div><span class="badge {{ $meta['badge'] }}">{{ $meta['label'] }}</span></div>
                                @else — @endif
                            </td>
                            <td><x-engineering.job-badges :job="$job" /></td>
                            <td>{{ Eng::name($job->engineer) }}</td>
                            <td class="small" style="min-width: 160px;">{{ Str::limit(ExecutiveSummary::remarks($job), 90) ?: '—' }}</td>
                            <td>{{ $job->currentFeedback?->rating->label() ?? 'No score' }}</td>
                        </tr>
                    @empty
                        <x-engineering.empty colspan="13" text="No requisitions were raised in this period." />
                    @endforelse
                </tbody>
            </table>
        </div>
        @if($jobs->hasPages())<div class="card-footer eng-no-print">{{ $jobs->links() }}</div>@endif
    </div>
</x-engineering.page>
