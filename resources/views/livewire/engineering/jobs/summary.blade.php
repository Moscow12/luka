@php
    use App\Support\Engineering\Eng;
    use App\Support\Engineering\Lookups;

    $requisition = $job->requisition;
    $spareLines = $job->sparesRequisitions->flatMap->lines;
    $feedback = $job->currentFeedback;
@endphp

<x-engineering.page :title="'Maintenance requisition · '.$job->number" subtitle="Read-only summary">
    <x-slot:actions>
        <button type="button" class="btn btn-outline-secondary" onclick="window.print()"><i class="fa-solid fa-print me-1"></i>Print / save as PDF</button>
        @if($canOpenWorkspace)
            <a href="{{ route('engineering.jobs.show', $job) }}" class="btn btn-primary"><i class="fa-solid fa-screwdriver-wrench me-1"></i>Open job</a>
        @endif
    </x-slot:actions>

    <div class="card">
        <div class="card-body">
            {{-- 1. Header --}}
            <div class="d-flex flex-wrap justify-content-between align-items-start gap-2 border-bottom pb-3 mb-3">
                <div>
                    <div class="text-uppercase small text-muted">{{ config('app.name') }} · Engineering</div>
                    <h4 class="mb-1">{{ $job->number }}</h4>
                    <div class="text-muted">Requested {{ Eng::dt($requisition->requested_at) }}</div>
                </div>
                <div class="text-end">
                    <x-engineering.job-badges :job="$job" />
                    <div class="small text-muted mt-1">{{ Lookups::label(Lookups::WORK_TYPES, $job->type_of_work, 'Type of work not set') }} · {{ $job->section->name }}</div>
                </div>
            </div>

            <div class="row g-4">
                {{-- 2. Requester --}}
                <div class="col-md-6">
                    <h6 class="text-uppercase small text-muted">Requester</h6>
                    <div class="fw-semibold">{{ $requisition->requester_name }}</div>
                    <div>{{ $requisition->requester_role }}</div>
                    <div>{{ $requisition->requester_phone }}</div>
                    <div>{{ $requisition->department?->name }} · {{ $requisition->locationLabel() }}</div>
                </div>

                {{-- 3. Equipment --}}
                <div class="col-md-6">
                    <h6 class="text-uppercase small text-muted">Equipment</h6>
                    <div class="fw-semibold">{{ $job->equipmentName() }}</div>
                    @if($job->equipment)
                        <div>Code / ID: {{ $job->equipment->code }}</div>
                        <div>Serial: {{ $job->equipment->serial_number }}</div>
                        <div>Manufacturer: {{ $job->equipment->manufacturer }}</div>
                    @else
                        <div class="text-muted">Not in the inventory</div>
                    @endif
                </div>

                {{-- 4. Problem --}}
                <div class="col-12">
                    <h6 class="text-uppercase small text-muted">Problem</h6>
                    <div>{{ $job->problem_description }}</div>
                    @if($requisition->category)<div class="small text-muted">{{ $requisition->category->name }}</div>@endif
                </div>

                {{-- 5. Assignment --}}
                <div class="col-md-6">
                    <h6 class="text-uppercase small text-muted">Assignment</h6>
                    @if($job->engineer)
                        <div><span class="fw-semibold">{{ Eng::name($job->engineer) }}</span> @if($job->engineer->phone)· {{ $job->engineer->phone }}@endif</div>
                        <div>Assistants: {{ $job->assistants->map(fn ($a) => Eng::name($a))->implode(', ') ?: 'none' }}</div>
                        <div>Assigned by {{ $job->assignedBy?->full_name ?? '—' }} on {{ Eng::dt($job->assigned_at) }}</div>
                        <div>Lead time allowed: {{ $job->days_to_be_done }} {{ Str::plural('day', $job->days_to_be_done) }}</div>
                    @else
                        <div class="text-muted">Not yet assigned</div>
                    @endif
                </div>

                {{-- 9. Closure --}}
                <div class="col-md-6">
                    <h6 class="text-uppercase small text-muted">Closure</h6>
                    @if($job->completed_at)
                        <div class="fw-semibold">{{ Lookups::label(Lookups::FINAL_STATUSES, $job->final_status) }}</div>
                        <div>Signed out by {{ $job->signedOutBy?->full_name ?? '—' }} on {{ Eng::dt($job->completed_at) }}</div>
                        <div>Turn-around time: <strong>{{ number_format($job->tatDays(), 2) }} days</strong></div>
                    @else
                        <div class="text-muted">Still open · day {{ $job->daysOpen() }}</div>
                    @endif
                    @if($job->recommendation)<div class="mt-1"><span class="text-muted">Recommendation:</span> {{ $job->recommendation }}</div>@endif
                </div>

                {{-- 6. Work done --}}
                @if($showNotes)
                    <div class="col-12">
                        <h6 class="text-uppercase small text-muted">Work done</h6>
                        @forelse($job->notes->where('kind', 'engineer') as $note)
                            <div class="mb-1"><span class="text-muted small">{{ Eng::dt($note->created_at, 'd M H:i') }} · {{ $note->author?->full_name }}:</span> {{ $note->body }}</div>
                        @empty
                            <div class="text-muted">No job notes recorded.</div>
                        @endforelse
                        <div class="small mt-1">
                            @if($job->rca)<a href="{{ route('engineering.jobs.rca', $job) }}">Root cause analysis {{ $job->rca->number }}</a> · @endif
                            @if($job->jobCard)
                                <a href="{{ route('engineering.jobs.card', $job) }}">Job card {{ $job->jobCard->number }}</a>
                                @if($job->jobCard->failure_reason) · Reason of failure: {{ Lookups::FAILURE_REASONS[$job->jobCard->failure_reason] }} @endif
                            @endif
                        </div>
                    </div>
                @endif

                {{-- 7. Spares --}}
                <div class="col-12">
                    <h6 class="text-uppercase small text-muted">Spares</h6>
                    @if($job->oxygenOrder)
                        <div>
                            {{ $job->oxygenOrder->qty_ordered }} × {{ $job->oxygenOrder->cylinder_type }} ordered
                            @if($job->oxygenOrder->isDelivered())
                                · {{ $job->oxygenOrder->qty_delivered }} delivered by {{ Eng::name($job->oxygenOrder->deliveredBy) }}, received by {{ $job->oxygenOrder->received_by }}
                            @endif
                        </div>
                    @elseif($spareLines->isEmpty())
                        <div>{{ $job->no_spares_consumed ? 'No spare(s) consumed for this service.' : 'None recorded.' }}</div>
                    @else
                        <table class="table table-sm mb-0">
                            <thead><tr><th>S/N</th><th>Spare</th><th>UoM</th><th>Item code</th><th class="text-end">Qty issued</th><th>Date</th></tr></thead>
                            <tbody>
                                @foreach($spareLines as $line)
                                    <tr>
                                        <td>{{ $loop->iteration }}</td>
                                        <td>{{ $line->spare_name }}</td>
                                        <td>{{ $line->uom }}</td>
                                        <td>{{ $line->item_code }}</td>
                                        <td class="text-end">{{ $line->qty_issued + 0 }} / {{ $line->qty_requested + 0 }}</td>
                                        <td>{{ Eng::date($line->date_consumed) }}</td>
                                    </tr>
                                @endforeach
                            </tbody>
                        </table>
                    @endif
                </div>

                {{-- 8. Safety testing --}}
                @unless($job->oxygenOrder)
                    <div class="col-md-6">
                        <h6 class="text-uppercase small text-muted">Safety testing</h6>
                        @foreach(Lookups::SAFETY_TESTS as $type => $label)
                            @php $test = $job->safetyTests->firstWhere('test_type', $type); @endphp
                            <div>
                                <i class="fa-solid {{ $test?->isSatisfied() ? 'fa-square-check text-success' : 'fa-square text-muted' }} me-1"></i>{{ $label }}
                                @if($test?->isSatisfied())
                                    <span class="small text-muted">· {{ $test->not_applicable ? 'N/A' : '' }} {{ $test->completedBy?->full_name }}, {{ Eng::dt($test->completed_at, 'd M H:i') }}</span>
                                @endif
                            </div>
                        @endforeach
                    </div>
                @endunless

                {{-- 10. Requester feedback --}}
                <div class="col-md-6">
                    <h6 class="text-uppercase small text-muted">Requester feedback</h6>
                    @if($feedback)
                        <div><span class="badge {{ $feedback->rating->badgeClass() }}">{{ $feedback->rating->label() }}</span> <strong>{{ $feedback->score_pct }}%</strong></div>
                        @if($feedback->comment)<div class="mt-1">{{ $feedback->comment }}</div>@endif
                    @else
                        <div class="text-muted">No score{{ $job->completed_at ? ' yet' : '' }} (0%)</div>
                    @endif
                    @if($job->is_rework)<div class="text-danger small mt-1">Sent back for rework {{ $job->rework_count }} {{ Str::plural('time', $job->rework_count) }}.</div>@endif
                </div>
            </div>
        </div>
    </div>
</x-engineering.page>
