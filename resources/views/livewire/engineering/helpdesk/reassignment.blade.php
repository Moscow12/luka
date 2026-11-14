@php use App\Support\Engineering\Eng; @endphp

<x-engineering.page title="Job re-assignment" subtitle="Move an open job when its engineer cannot attend it">
    <x-slot:actions>
        <button type="button" class="btn btn-outline-success" wire:click="export"><i class="fa-solid fa-file-excel me-1"></i>Export to Excel</button>
    </x-slot:actions>

    <div class="card mb-3">
        <div class="card-body">
            <div class="row g-2 align-items-end">
                <div class="col-md-4">
                    <label class="form-label small">Search</label>
                    <input type="search" class="form-control" placeholder="MRV, equipment, problem" wire:model.live.debounce.400ms="search">
                </div>
                <div class="col-6 col-md-4">
                    <label class="form-label small">Section</label>
                    <select class="form-select" wire:model.live="filterSection">
                        <option value="">All</option>
                        @foreach($sections as $section)
                            <option value="{{ $section->id }}">{{ $section->name }}</option>
                        @endforeach
                    </select>
                </div>
                <div class="col-6 col-md-4">
                    <label class="form-label small">Engineer</label>
                    <select class="form-select" wire:model.live="filterEngineer">
                        <option value="">All</option>
                        @foreach($engineers as $engineer)
                            <option value="{{ $engineer->id }}">{{ $engineer->first_name }} {{ $engineer->last_name }}</option>
                        @endforeach
                    </select>
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
                        <th>Reported by</th>
                        <th>Location</th>
                        <th>Assigned engineer</th>
                        <th>Status</th>
                        <th class="text-end">Action</th>
                    </tr>
                </thead>
                <tbody>
                    @forelse($jobs as $job)
                        <tr wire:key="job-{{ $job->id }}" class="{{ $job->is_rework ? 'eng-row-rework' : '' }}">
                            <td>{{ $jobs->firstItem() + $loop->index }}</td>
                            <td class="text-nowrap">
                                <a href="{{ route('engineering.jobs.show', $job) }}" class="fw-semibold text-decoration-none">{{ $job->number }}</a>
                                <div class="small text-muted">{{ Eng::dt($job->requisition->requested_at) }}</div>
                            </td>
                            <td>{{ $job->equipmentName() }}</td>
                            <td>{{ $job->requisition->requester_name }}<div class="small text-muted">{{ $job->requisition->department?->name }}</div></td>
                            <td>{{ $job->requisition->locationLabel() }}</td>
                            <td>{{ Eng::name($job->engineer) }}<div class="small text-muted">{{ $job->section->name }} · {{ $job->daysOpen() }} {{ Str::plural('day', $job->daysOpen()) }}</div></td>
                            <td><x-engineering.job-badges :job="$job" /></td>
                            <td class="text-end">
                                <button type="button" class="btn btn-sm btn-outline-primary" wire:click="open('{{ $job->id }}')"><i class="fa-solid fa-right-left me-1"></i>Reassign</button>
                            </td>
                        </tr>
                    @empty
                        <x-engineering.empty colspan="8" text="No open jobs are assigned at the moment." />
                    @endforelse
                </tbody>
            </table>
        </div>
        @if($jobs->hasPages())
            <div class="card-footer">{{ $jobs->links() }}</div>
        @endif
    </div>

    @if($panelJob)
        <x-engineering.modal :title="'Reassign '.$panelJob->number" close="closeAssignmentForm" size="modal-lg">
            <form wire:submit="reassign">
                <div class="modal-body">
                    <p class="mb-3">
                        Currently with <strong>{{ Eng::name($panelJob->engineer) }}</strong> ({{ $panelJob->section->name }}) ·
                        {{ $panelJob->equipmentName() }}.
                        <span class="text-muted">Job notes, spares and safety tests stay with the job.</span>
                    </p>

                    @include('livewire.engineering.partials.assignment-fields')

                    <div class="mt-3">
                        <label class="form-label">Reason for re-assignment <span class="text-danger">*</span></label>
                        <textarea class="form-control @error('reason') is-invalid @enderror" rows="2" wire:model="reason" placeholder="e.g. On leave from 06/11, handed over"></textarea>
                        @error('reason') <div class="invalid-feedback">{{ $message }}</div> @enderror
                    </div>
                    <div class="form-check form-switch mt-3">
                        <input class="form-check-input" type="checkbox" role="switch" id="keep-clock" wire:model="keepClock">
                        <label class="form-check-label" for="keep-clock">Keep the turn-around clock running from the original assignment</label>
                    </div>
                </div>
                <div class="modal-footer">
                    <button type="button" class="btn btn-secondary" wire:click="closeAssignmentForm">Cancel</button>
                    <button type="submit" class="btn btn-primary"><i class="fa-solid fa-right-left me-1"></i>Re-assign job</button>
                </div>
            </form>
        </x-engineering.modal>
    @endif
</x-engineering.page>
