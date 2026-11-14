@php use App\Support\Engineering\Eng; use App\Support\Engineering\Lookups; @endphp

<x-engineering.page title="Job collection & assignment" subtitle="Read the fault, choose the section and hand it to an engineer">
    <x-slot:actions>
        <button type="button" class="btn btn-outline-success" wire:click="export"><i class="fa-solid fa-file-excel me-1"></i>Export to Excel</button>
    </x-slot:actions>

    <div class="row g-3 mb-3">
        <div class="col-6 col-md-3">
            <div class="card h-100"><div class="card-body py-3">
                <div class="text-muted small">Waiting for assignment</div>
                <div class="h4 mb-0">{{ $waiting }}</div>
            </div></div>
        </div>
        <div class="col-6 col-md-3">
            <div class="card h-100 {{ $emergencies ? 'border-danger' : '' }}"><div class="card-body py-3">
                <div class="text-muted small">Emergencies waiting</div>
                <div class="h4 mb-0 {{ $emergencies ? 'text-danger eng-blink' : '' }}">{{ $emergencies }}</div>
            </div></div>
        </div>
    </div>

    <div class="card mb-3">
        <div class="card-body">
            <div class="row g-2 align-items-end">
                <div class="col-md-3">
                    <label class="form-label small">Search</label>
                    <input type="search" class="form-control" placeholder="MRV, equipment, problem" wire:model.live.debounce.400ms="search">
                </div>
                <div class="col-6 col-md-2">
                    <label class="form-label small">Show</label>
                    <select class="form-select" wire:model.live="filterStatus">
                        <option value="waiting">Waiting for assignment</option>
                        <option value="open">All open jobs</option>
                    </select>
                </div>
                <div class="col-6 col-md-2">
                    <label class="form-label small">Section</label>
                    <select class="form-select" wire:model.live="filterSection">
                        <option value="">All</option>
                        @foreach($sections as $section)
                            <option value="{{ $section->id }}">{{ $section->name }}</option>
                        @endforeach
                    </select>
                </div>
                <div class="col-6 col-md-2">
                    <label class="form-label small">Priority</label>
                    <select class="form-select" wire:model.live="filterPriority">
                        <option value="">All</option>
                        @foreach(Lookups::PRIORITIES as $value => $label)
                            <option value="{{ $value }}">{{ $label }}</option>
                        @endforeach
                    </select>
                </div>
                <div class="col-6 col-md-2">
                    <label class="form-label small">Department</label>
                    <select class="form-select" wire:model.live="filterDepartment">
                        <option value="">All</option>
                        @foreach($departments as $department)
                            <option value="{{ $department->id }}">{{ $department->name }}</option>
                        @endforeach
                    </select>
                </div>
                <div class="col-6 col-md-2">
                    <label class="form-label small">From</label>
                    <input type="date" class="form-control" wire:model.live="dateFrom">
                </div>
                <div class="col-6 col-md-2">
                    <label class="form-label small">To</label>
                    <input type="date" class="form-control" wire:model.live="dateTo">
                </div>
                <div class="col-auto">
                    <button type="button" class="btn btn-outline-secondary" wire:click="resetFilters" title="Reset filters"><i class="fa-solid fa-rotate-left"></i></button>
                </div>
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
                        <th>Problem</th>
                        <th>Reported by</th>
                        <th>Location</th>
                        <th class="text-center">Waiting</th>
                        <th class="text-end">Action</th>
                    </tr>
                </thead>
                <tbody>
                    @forelse($jobs as $job)
                        @php $requisition = $job->requisition; @endphp
                        <tr wire:key="job-{{ $job->id }}" class="{{ $requisition->isEmergency() ? 'eng-row-emergency' : '' }}">
                            <td>{{ $jobs->firstItem() + $loop->index }}</td>
                            <td class="text-nowrap">
                                <span class="fw-semibold">{{ $job->number }}</span>
                                <div class="small text-muted">{{ Eng::dt($requisition->requested_at) }}</div>
                                <x-engineering.job-badges :job="$job" :status="$filterStatus !== 'waiting'" />
                            </td>
                            <td>
                                {{ $job->equipmentName() }}
                                <div class="small text-muted">
                                    @if($job->equipment){{ $job->equipment->code }} · S/N {{ $job->equipment->serial_number }}@else Not in inventory @endif
                                </div>
                            </td>
                            <td style="min-width: 220px;">
                                <span title="{{ $job->problem_description }}">{{ Str::limit($job->problem_description, 90) }}</span>
                                <div class="small text-muted">{{ $job->section->name }} · {{ $requisition->category?->name }}</div>
                            </td>
                            <td>
                                {{ $requisition->requester_name }}
                                @if($requisition->requester_phone)
                                    <div class="small"><a href="tel:{{ $requisition->requester_phone }}"><i class="fa-solid fa-phone me-1"></i>{{ $requisition->requester_phone }}</a></div>
                                @endif
                            </td>
                            <td>
                                {{ $requisition->locationLabel() }}
                                <div class="small text-muted">{{ $requisition->department?->name }}</div>
                            </td>
                            <td class="text-center text-nowrap">{{ Eng::elapsed($requisition->requested_at) }}</td>
                            <td class="text-end text-nowrap">
                                @if($job->status === \App\Enums\Engineering\JobStatus::Submitted)
                                    <button type="button" class="btn btn-sm {{ $requisition->isEmergency() ? 'btn-danger' : 'btn-primary' }}" wire:click="process('{{ $job->id }}')">
                                        Process requisition
                                    </button>
                                @else
                                    <span class="small text-muted">{{ Eng::name($job->engineer) }}</span>
                                @endif
                            </td>
                        </tr>
                    @empty
                        <x-engineering.empty colspan="8" icon="fa-mug-hot" text="The queue is clear. Nothing is waiting for assignment." />
                    @endforelse
                </tbody>
            </table>
        </div>
        @if($jobs->hasPages())
            <div class="card-footer">{{ $jobs->links() }}</div>
        @endif
    </div>

    {{-- Process requisition --}}
    @if($panelJob)
        @php $requisition = $panelJob->requisition; @endphp
        <x-engineering.modal :title="'Process requisition · '.$panelJob->number" close="closePanel" size="modal-lg" :header="$requisition->isEmergency() ? 'bg-danger text-white' : ''">
            <div class="modal-body">
                <div class="row g-3 mb-3">
                    <x-engineering.detail label="Equipment">{{ $panelJob->equipmentName() }} <span class="text-muted fw-normal">{{ $panelJob->equipment?->code }}</span></x-engineering.detail>
                    <x-engineering.detail label="Reported by">{{ $requisition->requester_name }} <span class="text-muted fw-normal">{{ $requisition->requester_phone }}</span></x-engineering.detail>
                    <x-engineering.detail label="Location">{{ $requisition->locationLabel() }}</x-engineering.detail>
                    <x-engineering.detail label="Category">{{ $requisition->category?->name ?? '—' }}</x-engineering.detail>
                    <x-engineering.detail label="Problem" class="col-12 col-md-12 col-lg-12">{{ $panelJob->problem_description }}</x-engineering.detail>
                    @if($requisition->escalation_justification)
                        <x-engineering.detail label="Why the requester calls it an emergency" class="col-12 col-md-12 col-lg-12">{{ $requisition->escalation_justification }}</x-engineering.detail>
                    @endif
                </div>

                {{-- Priority correction --}}
                <details class="border rounded p-3 mb-3">
                    <summary class="fw-semibold">
                        Priority: <span class="badge {{ $requisition->isEmergency() ? 'bg-danger' : 'bg-secondary' }}">{{ ucfirst($requisition->priority) }}</span>
                        <span class="text-muted fw-normal small ms-1">change…</span>
                    </summary>
                    <div class="row g-2 mt-2 align-items-end">
                        <div class="col-md-3">
                            <select class="form-select" wire:model="newPriority">
                                @foreach(Lookups::PRIORITIES as $value => $label)
                                    <option value="{{ $value }}">{{ $label }}</option>
                                @endforeach
                            </select>
                        </div>
                        <div class="col-md-6">
                            <input type="text" class="form-control @error('priorityReason') is-invalid @enderror" wire:model="priorityReason" placeholder="Reason for the change (recorded)">
                            @error('priorityReason') <div class="invalid-feedback">{{ $message }}</div> @enderror
                        </div>
                        <div class="col-md-3">
                            <button type="button" class="btn btn-outline-secondary w-100" wire:click="reclassify">Update priority</button>
                        </div>
                    </div>
                </details>

                <div class="row g-3 mb-3">
                    <div class="col-md-6">
                        <label class="form-label">Type of work <span class="text-danger">*</span></label>
                        <select class="form-select" wire:model="typeOfWork">
                            @foreach(Lookups::WORK_TYPES as $value => $label)
                                <option value="{{ $value }}">{{ $label }}</option>
                            @endforeach
                        </select>
                    </div>
                    <div class="col-md-6">
                        <label class="form-label">Days to be done <span class="text-danger">*</span></label>
                        <input type="number" min="1" class="form-control @error('daysToBeDone') is-invalid @enderror" wire:model="daysToBeDone">
                        @error('daysToBeDone') <div class="invalid-feedback">{{ $message }}</div> @enderror
                    </div>
                </div>

                @include('livewire.engineering.partials.assignment-fields')

                <div class="mt-3">
                    <label class="form-label">Note to the engineer</label>
                    <textarea class="form-control" rows="2" wire:model="assignmentNote" placeholder="e.g. Patient on the device, attend now"></textarea>
                </div>
            </div>
            <div class="modal-footer">
                <span class="small text-muted me-auto">Assigned by {{ auth()->user()->full_name }} · the engineer is notified by SMS and email</span>
                <button type="button" class="btn btn-secondary" wire:click="closePanel">Cancel</button>
                <button type="button" class="btn btn-primary" wire:click="assign" wire:loading.attr="disabled" wire:target="assign">
                    <i class="fa-solid fa-user-check me-1"></i>Assign job
                </button>
            </div>
        </x-engineering.modal>
    @endif
</x-engineering.page>
