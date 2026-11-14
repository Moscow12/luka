@php use App\Support\Engineering\Eng; @endphp

<x-engineering.page title="My assigned jobs" :subtitle="$employee ? Eng::name($employee).' · only jobs assigned to you are shown' : 'Only jobs assigned to you are shown'">
    <x-slot:actions>
        <button type="button" class="btn btn-outline-success" wire:click="export"><i class="fa-solid fa-file-excel me-1"></i>Export to Excel</button>
    </x-slot:actions>

    <div class="d-flex flex-wrap align-items-center gap-2 mb-3">
        <ul class="nav nav-pills">
            <li class="nav-item">
                <button type="button" class="nav-link {{ $tab === 'open' ? 'active' : '' }}" wire:click="setTab('open')">
                    Open <span class="badge rounded-pill bg-secondary ms-1">{{ $openCount }}</span>
                    @if($overdueCount)<span class="badge rounded-pill bg-danger ms-1">{{ $overdueCount }} overdue</span>@endif
                </button>
            </li>
            <li class="nav-item">
                <button type="button" class="nav-link {{ $tab === 'finished' ? 'active' : '' }}" wire:click="setTab('finished')">Finished</button>
            </li>
        </ul>
        <div class="ms-md-auto" style="min-width: 260px;">
            <input type="search" class="form-control" placeholder="Search MRV, equipment, problem" wire:model.live.debounce.400ms="search">
        </div>
    </div>

    {{-- Cards rather than a wide table: this list is mostly read on a phone --}}
    <div class="row g-3">
        @forelse($jobs as $job)
            @php
                $requisition = $job->requisition;
                $overdue = $job->isOverdue();
            @endphp
            <div class="col-md-6 col-xl-4" wire:key="job-{{ $job->id }}">
                <div class="card h-100 {{ $job->is_rework || $requisition->isEmergency() ? 'border-danger' : ($overdue ? 'border-warning' : '') }}">
                    <div class="card-body d-flex flex-column">
                        <div class="d-flex justify-content-between align-items-start gap-2 mb-2">
                            <div>
                                <div class="fw-semibold">{{ $job->number }}</div>
                                <div class="small text-muted">Requested {{ Eng::dt($requisition->requested_at) }}</div>
                            </div>
                            <div class="text-end"><x-engineering.job-badges :job="$job" /></div>
                        </div>

                        <h6 class="mb-1">{{ $job->equipmentName() }}</h6>
                        <p class="text-muted small mb-2">{{ Str::limit($job->problem_description, 110) }}</p>

                        <div class="small mb-3">
                            <div><i class="fa-solid fa-location-dot fa-fw text-muted me-1"></i>{{ $requisition->locationLabel() }}</div>
                            <div>
                                <i class="fa-solid fa-user fa-fw text-muted me-1"></i>{{ $requisition->requester_name }}
                                @if($requisition->requester_role)<span class="text-muted">· {{ $requisition->requester_role }}</span>@endif
                            </div>
                            <div><i class="fa-solid fa-layer-group fa-fw text-muted me-1"></i>{{ $job->section->name }} · {{ Eng::name($job->engineer) }}</div>
                        </div>

                        <div class="d-flex justify-content-between align-items-center mt-auto">
                            @if($job->isOpen())
                                <span class="small {{ $overdue ? 'text-danger fw-semibold' : 'text-muted' }}">
                                    @if($overdue)<i class="fa-solid fa-triangle-exclamation me-1"></i>@endif
                                    Day {{ $job->daysOpen() }} of {{ $job->days_to_be_done }} allowed
                                </span>
                                <a href="{{ route('engineering.jobs.show', $job) }}" class="btn btn-sm {{ $requisition->isEmergency() ? 'btn-danger' : 'btn-primary' }}">
                                    {{ $job->oxygenOrder ? 'Deliver' : 'Attend' }} <i class="fa-solid fa-arrow-right ms-1"></i>
                                </a>
                            @else
                                <span class="small text-muted">
                                    TAT {{ number_format((float) $job->tatDays(), 2) }} d
                                    @if($job->currentFeedback) · {{ $job->currentFeedback->rating->label() }} @endif
                                </span>
                                <a href="{{ route('engineering.jobs.show', $job) }}" class="btn btn-sm btn-outline-secondary">View</a>
                            @endif
                        </div>
                    </div>
                </div>
            </div>
        @empty
            <div class="col-12">
                <div class="card"><div class="card-body text-center py-5 text-muted">
                    <i class="fa-solid fa-clipboard-check fa-2x mb-2 opacity-50"></i>
                    <p class="mb-0">{{ $tab === 'open' ? 'No open jobs are assigned to you.' : 'No finished jobs yet.' }}</p>
                </div></div>
            </div>
        @endforelse
    </div>
</x-engineering.page>
