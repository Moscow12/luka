@php
    use App\Support\Engineering\Eng;

    $requisition = $job->requisition;
@endphp

<x-engineering.page :title="'Root cause analysis (5-Why)'.($rca ? ' · '.$rca->number : '')" :subtitle="$job->number.' · '.$job->equipmentName()">
    <x-slot:actions>
        @if($rca)<span class="badge fs-6 {{ $rca->status === 'draft' ? 'bg-secondary' : ($rca->status === 'completed' ? 'bg-success' : 'bg-dark') }}">{{ ucfirst($rca->status) }}</span>@endif
        <button type="button" class="btn btn-outline-secondary" onclick="window.print()"><i class="fa-solid fa-print me-1"></i>Print / PDF</button>
        <a href="{{ route('engineering.jobs.show', $job) }}" class="btn btn-outline-secondary"><i class="fa-solid fa-arrow-left me-1"></i>Back to job</a>
    </x-slot:actions>

    {{-- A. Identification --}}
    <div class="card mb-4">
        <div class="card-header"><h6 class="mb-0">A · Identification <span class="text-muted fw-normal small">(from the requisition)</span></h6></div>
        <div class="card-body">
            <div class="row g-3">
                <x-engineering.detail label="Requisition number">{{ $job->number }}</x-engineering.detail>
                <x-engineering.detail label="Requisition date">{{ Eng::dt($requisition->requested_at) }}</x-engineering.detail>
                <x-engineering.detail label="Requesting department">{{ $requisition->department?->name ?? '—' }}</x-engineering.detail>
                <x-engineering.detail label="Section">{{ $job->section->name }}</x-engineering.detail>
                <x-engineering.detail label="Reported by">{{ $requisition->requester_name }}</x-engineering.detail>
                <x-engineering.detail label="Role / responsibility">{{ $requisition->requester_role ?? '—' }}</x-engineering.detail>
                <x-engineering.detail label="Area / location / room">{{ $requisition->locationLabel() }}</x-engineering.detail>
                <x-engineering.detail label="Plant / device / machine">{{ $job->equipmentName() }} <span class="text-muted fw-normal">{{ $job->equipment?->code }}</span></x-engineering.detail>
            </div>
        </div>
    </div>

    <fieldset @disabled(! $canEdit)>
        {{-- B. Observation --}}
        <div class="card mb-4">
            <div class="card-header"><h6 class="mb-0">B · Observation</h6></div>
            <div class="card-body">
                <div class="row g-3">
                    <div class="col-md-4">
                        <label class="form-label">Session date</label>
                        <input type="date" class="form-control" wire:model="form.session_date">
                    </div>
                    <div class="col-12">
                        <label class="form-label">What indicated the problem? <span class="text-danger">*</span></label>
                        <textarea class="form-control @error('form.indication_of_problem') is-invalid @enderror" rows="2" wire:model="form.indication_of_problem" placeholder="What did you find when you arrived at the machine? Be specific about your exact observation."></textarea>
                        @error('form.indication_of_problem') <div class="invalid-feedback">{{ $message }}</div> @enderror
                    </div>
                    <div class="col-12">
                        <label class="form-label">Problem as explained by the client</label>
                        <div class="form-control bg-light" style="height: auto;">{{ $job->problem_description }}</div>
                    </div>
                    <div class="col-12">
                        <label class="form-label">What the client reported when asked <span class="text-danger">*</span></label>
                        <textarea class="form-control @error('form.client_observations') is-invalid @enderror" rows="2" wire:model="form.client_observations" placeholder="What did they see? Hear? Smell? Feel? Anything else observed?"></textarea>
                        @error('form.client_observations') <div class="invalid-feedback">{{ $message }}</div> @enderror
                    </div>
                    <div class="col-12">
                        <label class="form-label">Actions taken <span class="text-danger">*</span></label>
                        <textarea class="form-control @error('form.actions_taken') is-invalid @enderror" rows="2" wire:model="form.actions_taken" placeholder="What did you do to solve the problem, get the equipment functioning or let the process continue?"></textarea>
                        @error('form.actions_taken') <div class="invalid-feedback">{{ $message }}</div> @enderror
                    </div>
                    <div class="col-md-6">
                        <label class="form-label">Probable causes <span class="text-danger">*</span></label>
                        @foreach($probableCauses as $index => $cause)
                            <div class="input-group mb-2" wire:key="cause-{{ $index }}">
                                <span class="input-group-text">{{ $index + 1 }}</span>
                                <input type="text" class="form-control" wire:model.blur="probableCauses.{{ $index }}">
                                @if($canEdit)<button type="button" class="btn btn-outline-secondary eng-no-print" wire:click="removeCause({{ $index }})"><i class="fa-solid fa-xmark"></i></button>@endif
                            </div>
                        @endforeach
                        @if($canEdit)<button type="button" class="btn btn-sm btn-outline-primary eng-no-print" wire:click="addCause"><i class="fa-solid fa-plus me-1"></i>Add cause</button>@endif
                    </div>
                    <div class="col-md-6">
                        <label class="form-label">Actual problem: what is wrong? <span class="text-danger">*</span></label>
                        <textarea class="form-control @error('form.actual_problem') is-invalid @enderror" rows="2" wire:model.blur="form.actual_problem"></textarea>
                        @error('form.actual_problem') <div class="invalid-feedback">{{ $message }}</div> @enderror

                        <label class="form-label mt-3">Probable cause to analyse <span class="text-danger">*</span></label>
                        <select class="form-select @error('form.probable_cause_selected') is-invalid @enderror" wire:model="form.probable_cause_selected">
                            <option value="">Select one of the probable causes</option>
                            @foreach(array_filter($probableCauses) as $cause)
                                <option value="{{ $cause }}">{{ $cause }}</option>
                            @endforeach
                        </select>
                        @error('form.probable_cause_selected') <div class="invalid-feedback">{{ $message }}</div> @enderror
                    </div>
                </div>
            </div>
        </div>

        {{-- C. Participants --}}
        <div class="card mb-4">
            <div class="card-header d-flex justify-content-between align-items-center">
                <h6 class="mb-0">C · Participants</h6>
                @if($canEdit)<button type="button" class="btn btn-sm btn-outline-primary eng-no-print" wire:click="addParticipant"><i class="fa-solid fa-plus me-1"></i>Add participant</button>@endif
            </div>
            <div class="card-body">
                @error('participants') <div class="alert alert-danger py-2">{{ $message }}</div> @enderror
                @forelse($participants as $index => $participant)
                    <div class="row g-2 mb-2" wire:key="participant-{{ $index }}">
                        <div class="col-md-4"><input type="text" class="form-control @error('participants.'.$index.'.name') is-invalid @enderror" placeholder="Name" wire:model="participants.{{ $index }}.name"></div>
                        <div class="col-md-3"><input type="text" class="form-control" placeholder="Role / section" wire:model="participants.{{ $index }}.role"></div>
                        <div class="col-md-4"><input type="text" class="form-control" placeholder="Organisation" wire:model="participants.{{ $index }}.organisation"></div>
                        <div class="col-md-1 text-end">@if($canEdit)<button type="button" class="btn btn-link text-danger eng-no-print" wire:click="removeParticipant({{ $index }})"><i class="fa-solid fa-trash"></i></button>@endif</div>
                    </div>
                @empty
                    <p class="text-muted mb-0">No participants recorded.</p>
                @endforelse
            </div>
        </div>

        {{-- D. 5-Why chain --}}
        <div class="card mb-4">
            <div class="card-header d-flex justify-content-between align-items-center">
                <h6 class="mb-0">D · 5-Why chain</h6>
                @if($canEdit)<button type="button" class="btn btn-sm btn-outline-primary eng-no-print" wire:click="addWhy"><i class="fa-solid fa-plus me-1"></i>Add a why</button>@endif
            </div>
            <div class="card-body">
                @foreach($whys as $index => $why)
                    <div class="border-start border-3 border-primary ps-3 mb-4" wire:key="why-{{ $index }}">
                        <div class="d-flex justify-content-between align-items-start">
                            <div><span class="badge bg-primary me-2">Why {{ $index + 1 }}</span><span class="fw-semibold">{{ $this->question($index) }}</span></div>
                            @if($canEdit && count($whys) > 1)<button type="button" class="btn btn-sm btn-link text-danger eng-no-print" wire:click="removeWhy({{ $index }})"><i class="fa-solid fa-trash"></i></button>@endif
                        </div>
                        <div class="row g-2 mt-1">
                            <div class="col-md-6">
                                <label class="form-label small text-muted mb-1">Answer</label>
                                <textarea class="form-control @error('whys.'.$index.'.answer') is-invalid @enderror" rows="2" wire:model.blur="whys.{{ $index }}.answer"></textarea>
                                @error('whys.'.$index.'.answer') <div class="invalid-feedback">{{ $message }}</div> @enderror
                            </div>
                            <div class="col-md-6">
                                <label class="form-label small text-muted mb-1">Evidence</label>
                                <textarea class="form-control @error('whys.'.$index.'.evidence') is-invalid @enderror" rows="2" wire:model="whys.{{ $index }}.evidence" placeholder="Measurement, record, photo reference, manual extract…"></textarea>
                                @error('whys.'.$index.'.evidence') <div class="invalid-feedback">{{ $message }}</div> @enderror
                            </div>
                        </div>
                    </div>
                @endforeach

                @if(count($whys) < $fullChain)
                    <div class="mb-3">
                        <label class="form-label">Why does the chain stop before the fifth why?</label>
                        <input type="text" class="form-control @error('form.early_stop_justification') is-invalid @enderror" wire:model="form.early_stop_justification" placeholder="Required when the root cause is reached early">
                        @error('form.early_stop_justification') <div class="invalid-feedback">{{ $message }}</div> @enderror
                    </div>
                @endif

                <label class="form-label fw-semibold">Root cause <span class="text-danger">*</span></label>
                <textarea class="form-control @error('form.root_cause') is-invalid @enderror" rows="2" wire:model="form.root_cause" placeholder="The final answer, restated"></textarea>
                @error('form.root_cause') <div class="invalid-feedback">{{ $message }}</div> @enderror
            </div>
        </div>

        {{-- E. Actions --}}
        <div class="card mb-4">
            <div class="card-header"><h6 class="mb-0">E · Corrective and preventive action</h6></div>
            <div class="card-body">
                <div class="row g-3">
                    <div class="col-md-6">
                        <label class="form-label">Corrective action <span class="text-muted small">(fix this occurrence)</span></label>
                        <textarea class="form-control" rows="2" wire:model="form.corrective_action"></textarea>
                    </div>
                    <div class="col-md-6">
                        <label class="form-label">Preventive action <span class="text-muted small">(stop it recurring)</span></label>
                        <textarea class="form-control" rows="2" wire:model="form.preventive_action"></textarea>
                    </div>
                    <div class="col-md-6">
                        <label class="form-label">Responsible person</label>
                        <select class="form-select" wire:model="form.responsible_employee_id">
                            <option value="">Select</option>
                            @foreach($staff as $person)
                                <option value="{{ $person->id }}">{{ $person->first_name }} {{ $person->last_name }}</option>
                            @endforeach
                        </select>
                    </div>
                    <div class="col-md-6">
                        <label class="form-label">Target date</label>
                        <input type="date" class="form-control" wire:model="form.action_target_date">
                    </div>
                </div>
            </div>
        </div>
    </fieldset>

    <div class="d-flex flex-wrap justify-content-between align-items-center gap-2 mb-4 eng-no-print">
        <div>
            @if($rca?->action_done_at)
                <span class="badge bg-success">Action done {{ Eng::dt($rca->action_done_at) }}</span>
            @elseif($rca?->actionOverdue())
                <span class="badge bg-danger">Preventive action overdue</span>
            @endif
            @if($rca && ! $rca->action_done_at && $rca->status !== 'draft' && $rca->preventive_action)
                <button type="button" class="btn btn-sm btn-outline-success ms-1" wire:click="markActionDone">Mark action done</button>
            @endif
        </div>
        <div class="d-flex gap-2">
            @if($canEdit)
                <button type="button" class="btn btn-outline-primary" wire:click="saveDraft">Save draft</button>
                <button type="button" class="btn btn-success" wire:click="complete"><i class="fa-solid fa-check me-1"></i>Complete RCA</button>
            @endif
            @if($canReview)
                <button type="button" class="btn btn-dark" wire:click="review" wire:confirm="Close this analysis? It becomes read-only."><i class="fa-solid fa-lock me-1"></i>Review and close</button>
            @endif
        </div>
    </div>
</x-engineering.page>
