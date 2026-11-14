<x-engineering.page title="My requisitions" subtitle="Follow up what you asked engineering to do">
    <x-slot:actions>
        <button type="button" class="btn btn-outline-success" wire:click="export"><i class="fa-solid fa-file-excel me-1"></i>Export to Excel</button>
        <a href="{{ route('engineering.request') }}" class="btn btn-primary"><i class="fa-solid fa-plus me-1"></i>New request</a>
    </x-slot:actions>

    {{-- Tabs --}}
    <ul class="nav nav-pills mb-3 flex-nowrap overflow-auto">
        @foreach(\App\Livewire\Engineering\MyRequisitions::TABS as $key => $label)
            <li class="nav-item">
                <button type="button" class="nav-link text-nowrap {{ $tab === $key ? 'active' : '' }}" wire:click="setTab('{{ $key }}')">
                    {{ $label }}
                    @if(($counts[$key] ?? 0) > 0)
                        <span class="badge rounded-pill ms-1 {{ $key === 'unattended' ? 'bg-secondary' : 'bg-danger' }} {{ $key === 'feedback' ? 'eng-blink' : '' }}">{{ $counts[$key] }}</span>
                    @endif
                </button>
            </li>
        @endforeach
    </ul>

    <div class="card mb-3">
        <div class="card-body">
            <div class="row g-2 align-items-end">
                <div class="col-md-5">
                    <label class="form-label small">Search</label>
                    <input type="search" class="form-control" placeholder="MRV number, equipment or problem" wire:model.live.debounce.400ms="search">
                </div>
                @if($tab === 'all')
                    <div class="col-6 col-md-3">
                        <label class="form-label small">From</label>
                        <input type="date" class="form-control" wire:model.live="dateFrom">
                    </div>
                    <div class="col-6 col-md-3">
                        <label class="form-label small">To</label>
                        <input type="date" class="form-control" wire:model.live="dateTo">
                    </div>
                @endif
            </div>
        </div>
    </div>

    <div class="card">
        <div class="table-responsive">
            <table class="table table-hover align-middle mb-0">
                <thead class="table-light">
                    <tr>
                        <th>S/N</th>
                        <th>Requisition</th>
                        <th>Equipment</th>
                        <th>Reported by</th>
                        <th>Location</th>
                        <th>Status</th>
                        <th class="text-center">Days open</th>
                        <th>Assigned to</th>
                        <th class="text-end">Action</th>
                    </tr>
                </thead>
                <tbody>
                    @forelse($jobs as $job)
                        @php
                            $needsFeedback = $job->awaitsFeedback();
                            $stale = $job->isOpen() && $job->daysOpen() >= $staleDays;
                        @endphp
                        <tr wire:key="job-{{ $job->id }}" class="{{ $job->is_rework ? 'eng-row-rework' : ($needsFeedback ? 'eng-row-alert' : '') }}">
                            <td>{{ $jobs->firstItem() + $loop->index }}</td>
                            <td class="text-nowrap">
                                <a href="{{ route('engineering.jobs.summary', $job) }}" class="fw-semibold text-decoration-none">{{ $job->number }}</a>
                                <div class="small text-muted">{{ \App\Support\Engineering\Eng::dt($job->requisition->requested_at) }}</div>
                            </td>
                            <td style="min-width: 200px;">
                                {{ $job->equipmentName() }}
                                <div class="small text-muted">{{ Str::limit($job->problem_description, 70) }}</div>
                            </td>
                            <td>
                                {{ $job->requisition->requester_name }}
                                <div class="small text-muted">{{ $job->requisition->department?->name }}</div>
                            </td>
                            <td>{{ $job->requisition->locationLabel() }}</td>
                            <td><x-engineering.job-badges :job="$job" /></td>
                            <td class="text-center {{ $stale ? 'text-warning fw-semibold' : '' }}">
                                {{ $job->daysOpen() }}
                                @if($stale)<i class="fa-solid fa-triangle-exclamation ms-1" title="Open for {{ $staleDays }} days or more"></i>@endif
                            </td>
                            <td>
                                @if($job->engineer)
                                    {{ \App\Support\Engineering\Eng::name($job->engineer) }}
                                    <div class="small text-muted">
                                        @if($job->engineer->phone)<a href="tel:{{ $job->engineer->phone }}" class="text-muted">{{ $job->engineer->phone }}</a> · @endif
                                        {{ $job->days_to_be_done }} {{ Str::plural('day', $job->days_to_be_done) }} allowed
                                    </div>
                                    @if($job->spare_required_at)<span class="badge bg-light text-dark border">Spare ordered</span>@endif
                                @else
                                    <span class="text-muted">Not yet assigned</span>
                                @endif
                            </td>
                            <td class="text-end text-nowrap">
                                @if($needsFeedback)
                                    <a href="{{ route('engineering.processed', ['rate' => $job->id]) }}" class="btn btn-sm btn-primary eng-blink">
                                        <i class="fa-solid fa-star me-1"></i>Job complete - rate it
                                    </a>
                                @elseif($canAssign && $job->status === \App\Enums\Engineering\JobStatus::Submitted)
                                    <a href="{{ route('engineering.helpdesk.queue', ['job' => $job->id]) }}" class="btn btn-sm btn-outline-primary">Assign job</a>
                                @endif
                                @if($job->isOpen())
                                    <button type="button" class="btn btn-sm btn-outline-secondary" wire:click="openNote('{{ $job->id }}')" title="Add a comment"><i class="fa-regular fa-comment"></i></button>
                                @endif
                                <a href="{{ route('engineering.jobs.summary', $job) }}" class="btn btn-sm btn-outline-secondary" title="View"><i class="fa-solid fa-eye"></i></a>
                            </td>
                        </tr>
                    @empty
                        <x-engineering.empty colspan="9" :text="$tab === 'unattended' ? 'Nothing outstanding. All your requests have been attended to.' : 'No requisitions here.'" />
                    @endforelse
                </tbody>
            </table>
        </div>
        @if($jobs->hasPages())
            <div class="card-footer">{{ $jobs->links() }}</div>
        @endif
    </div>

    <p class="small text-muted mt-2 mb-0">
        <span class="badge bg-primary">Job complete</span> means the work is finished and your feedback is required ·
        <i class="fa-solid fa-triangle-exclamation text-warning"></i> open for {{ $staleDays }} days or more
    </p>

    @if($noteJobId)
        <x-engineering.modal title="Add a comment" close="closeNote">
            <form wire:submit="saveNote">
                <div class="modal-body">
                    <textarea class="form-control @error('noteBody') is-invalid @enderror" rows="4" wire:model="noteBody" placeholder="Anything engineering should know. This does not change the status."></textarea>
                    @error('noteBody') <div class="invalid-feedback">{{ $message }}</div> @enderror
                </div>
                <div class="modal-footer">
                    <button type="button" class="btn btn-secondary" wire:click="closeNote">Cancel</button>
                    <button type="submit" class="btn btn-primary">Save comment</button>
                </div>
            </form>
        </x-engineering.modal>
    @endif
</x-engineering.page>
