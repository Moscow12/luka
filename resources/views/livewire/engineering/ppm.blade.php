@php
    use App\Models\Engineering\PpmSchedule;
    use App\Support\Engineering\Eng;
    use App\Support\Engineering\Lookups;
@endphp

<x-engineering.page title="PPM schedule & checklists" subtitle="Planned preventive maintenance: what is due, who owns it, and the checklist for each occurrence">
    <x-slot:actions>
        <button type="button" class="btn btn-outline-success" wire:click="export"><i class="fa-solid fa-file-excel me-1"></i>Export PPM report</button>
        @if($canManage)
            <button type="button" class="btn btn-primary" wire:click="create"><i class="fa-solid fa-plus me-1"></i>Add PPM schedule</button>
        @endif
    </x-slot:actions>

    <div class="row g-3 mb-3">
        <div class="col-6 col-md-3">
            <div class="card h-100 {{ $dueSoon ? 'border-primary' : '' }}"><div class="card-body py-3">
                <div class="text-muted small">Due within {{ config('engineering.ppm_due_soon_days') }} days</div>
                <div class="h4 mb-0 {{ $dueSoon ? 'text-primary eng-blink' : '' }}">{{ $dueSoon }}</div>
            </div></div>
        </div>
        <div class="col-6 col-md-3">
            <div class="card h-100 {{ $overdue ? 'border-danger' : '' }}"><div class="card-body py-3">
                <div class="text-muted small">Overdue</div>
                <div class="h4 mb-0 {{ $overdue ? 'text-danger eng-blink' : '' }}">{{ $overdue }}</div>
            </div></div>
        </div>
    </div>

    <div class="card mb-3">
        <div class="card-body">
            <div class="row g-2 align-items-end">
                <div class="col-md-3">
                    <label class="form-label small">Search</label>
                    <input type="search" class="form-control" placeholder="Equipment, code or serial" wire:model.live.debounce.400ms="search">
                </div>
                <div class="col-6 col-md-2">
                    <label class="form-label small">Show</label>
                    <select class="form-select" wire:model.live="state">
                        <option value="attention">Due & overdue</option>
                        <option value="all">All schedules</option>
                    </select>
                </div>
                <div class="col-6 col-md-2">
                    <label class="form-label small">Section</label>
                    <select class="form-select" wire:model.live="filterSection">
                        <option value="">All</option>
                        @foreach($sections as $section)<option value="{{ $section->id }}">{{ $section->name }}</option>@endforeach
                    </select>
                </div>
                <div class="col-6 col-md-3">
                    <label class="form-label small">Department</label>
                    <select class="form-select" wire:model.live="filterDepartment">
                        <option value="">All</option>
                        @foreach($departments as $department)<option value="{{ $department->id }}">{{ $department->name }}</option>@endforeach
                    </select>
                </div>
                <div class="col-6 col-md-2">
                    <label class="form-label small">Performer</label>
                    <select class="form-select" wire:model.live="filterPerformer">
                        <option value="">All</option>
                        @foreach(Lookups::PPM_PERFORMERS as $value => $label)<option value="{{ $value }}">{{ $label }}</option>@endforeach
                    </select>
                </div>
            </div>
        </div>
    </div>

    <div class="card">
        <div class="table-responsive">
            <table class="table table-hover align-middle mb-0">
                <thead class="table-light">
                    <tr><th>S/N</th><th>Equipment</th><th>Location</th><th>Frequency</th><th>Last done</th><th>Next due</th><th>Responsible</th><th>PPM MRV</th><th class="text-end">Action</th></tr>
                </thead>
                <tbody>
                    @forelse($schedules as $schedule)
                        @php
                            $dueState = $schedule->dueState();
                            $days = $schedule->daysToDue();
                            $instance = $schedule->openInstance;
                        @endphp
                        <tr wire:key="schedule-{{ $schedule->id }}">
                            <td class="text-nowrap">
                                {{ $schedules->firstItem() + $loop->index }}
                                @if($dueState === PpmSchedule::STATE_OVERDUE)<i class="fa-solid fa-circle text-danger eng-blink ms-1" title="Overdue"></i>
                                @elseif($dueState === PpmSchedule::STATE_DUE_SOON)<i class="fa-solid fa-circle text-primary eng-blink ms-1" title="Due soon"></i>@endif
                            </td>
                            <td>
                                <span class="fw-semibold">{{ $schedule->equipment->name }}</span>
                                <div class="small text-muted">{{ $schedule->equipment->code }} · {{ $schedule->equipment->section->name }}</div>
                            </td>
                            <td>{{ $schedule->equipment->room->name }}<div class="small text-muted">{{ $schedule->equipment->department->name }}</div></td>
                            <td>
                                {{ Lookups::PPM_FREQUENCIES[$schedule->frequency] }}
                                @if($schedule->interval_hours)<span class="text-muted small">({{ number_format($schedule->interval_hours) }} h)</span>@endif
                                <div><span class="badge bg-light text-dark border">{{ Lookups::PPM_PERFORMERS[$schedule->performer] }} PPM</span></div>
                            </td>
                            <td class="small">
                                @if($schedule->last_done_at)
                                    {{ Eng::dt($schedule->last_done_at, 'd M Y') }}<div class="text-muted">{{ $schedule->lastDoneBy?->full_name }}</div>
                                @else
                                    <span class="text-muted">Never</span>
                                @endif
                            </td>
                            <td class="text-nowrap {{ $dueState === PpmSchedule::STATE_OVERDUE ? 'text-danger fw-semibold' : ($dueState === PpmSchedule::STATE_DUE_SOON ? 'text-primary fw-semibold' : '') }}">
                                {{ Eng::date($schedule->next_due_at) }}
                                @if($days !== null && $dueState !== PpmSchedule::STATE_SCHEDULED)
                                    <div class="small">{{ $days < 0 ? abs($days).' '.Str::plural('day', abs($days)).' overdue' : ($days === 0 ? 'due today' : 'in '.$days.' '.Str::plural('day', $days)) }}</div>
                                @endif
                            </td>
                            <td>{{ Eng::name($schedule->responsible) }}</td>
                            <td>
                                @if($instance?->job)
                                    <a href="{{ route('engineering.jobs.summary', $instance->job) }}" class="text-decoration-none">{{ $instance->job->number }}</a>
                                @else
                                    <span class="text-muted">—</span>
                                @endif
                            </td>
                            <td class="text-end text-nowrap">
                                @if($schedule->performer === 'engineering' && ! $instance?->job && $dueState !== PpmSchedule::STATE_SCHEDULED)
                                    <button type="button" class="btn btn-sm {{ $dueState === PpmSchedule::STATE_OVERDUE ? 'btn-danger' : 'btn-primary' }}" wire:click="generate('{{ $schedule->id }}')" wire:loading.attr="disabled">
                                        Generate PPM requisition
                                    </button>
                                @endif
                                @if($instance || $schedule->performer === 'user')
                                    <button type="button" class="btn btn-sm btn-outline-primary" wire:click="openChecklist('{{ $schedule->id }}')">
                                        <i class="fa-solid fa-list-check me-1"></i>Checklist
                                        @if($instance)<span class="badge bg-secondary ms-1">{{ $instance->entries->filter->isSatisfied()->count() }}/{{ $instance->entries->count() }}</span>@endif
                                    </button>
                                @endif
                                @if($canManage)
                                    <button type="button" class="btn btn-sm btn-outline-secondary" wire:click="edit('{{ $schedule->id }}')" title="Edit schedule"><i class="fa-solid fa-pen"></i></button>
                                @endif
                            </td>
                        </tr>
                    @empty
                        <x-engineering.empty colspan="9" icon="fa-calendar-check" :text="$state === 'attention' ? 'Nothing is due in the next '.config('engineering.ppm_due_soon_days').' days and nothing is overdue.' : 'No PPM schedules yet.'" />
                    @endforelse
                </tbody>
            </table>
        </div>
        @if($schedules->hasPages())<div class="card-footer">{{ $schedules->links() }}</div>@endif
    </div>

    {{-- Checklist --}}
    @if($instance)
        @php $equipment = $instance->schedule->equipment; @endphp
        <x-engineering.modal :title="'PPM checklist · '.$equipment->name.' '.$equipment->code" close="closeChecklist" size="modal-xl">
            <div class="modal-body">
                <div class="row g-3 mb-3">
                    <x-engineering.detail label="PPM">{{ Lookups::PPM_PERFORMERS[$instance->schedule->performer] }} · {{ Lookups::PPM_FREQUENCIES[$instance->schedule->frequency] }}</x-engineering.detail>
                    <x-engineering.detail label="Due">{{ Eng::date($instance->due_at) }}</x-engineering.detail>
                    <x-engineering.detail label="Responsible">{{ Eng::name($instance->schedule->responsible) }}</x-engineering.detail>
                    <x-engineering.detail label="PPM MRV">{{ $instance->job?->number ?? '—' }}</x-engineering.detail>
                </div>

                @if($instance->isCompleted())
                    <div class="alert alert-success"><i class="fa-solid fa-circle-check me-1"></i>Completed by {{ $instance->completedBy?->full_name }} on {{ Eng::dt($instance->completed_at) }}. This checklist is read-only.</div>
                @elseif(! $canFill)
                    <div class="alert alert-info">You can view this checklist. It is filled in by {{ $instance->schedule->performer === 'user' ? 'staff of the department that owns the equipment' : 'the engineer responsible for this PPM' }}.</div>
                @endif

                <fieldset @disabled(! $canFill)>
                    <div class="table-responsive">
                        <table class="table align-middle">
                            <thead class="table-light"><tr><th style="width: 60px;">Done</th><th>Task</th><th style="width: 150px;">Expected</th><th style="width: 150px;">Measured</th><th>Comment</th><th style="width: 60px;" title="Not applicable (comment required)">N/A</th></tr></thead>
                            <tbody>
                                @foreach($instance->entries as $entry)
                                    <tr wire:key="entry-{{ $entry->id }}" class="{{ $entry->isSatisfied() && ($entry->done || $entry->not_applicable) ? 'table-success' : '' }}">
                                        <td class="text-center"><input type="checkbox" class="form-check-input" style="transform: scale(1.4);" wire:model.live="entries.{{ $entry->id }}.done"></td>
                                        <td>
                                            {{ $entry->seq }}. {{ $entry->description }}
                                            @unless($entry->mandatory)<span class="badge bg-light text-dark border">Optional</span>@endunless
                                            @if($entry->ticked_at)<div class="small text-muted">{{ $entry->tickedBy?->full_name }} · {{ Eng::dt($entry->ticked_at, 'd M H:i') }}</div>@endif
                                        </td>
                                        <td class="text-muted">{{ $entry->expected_value ?? '—' }}</td>
                                        <td><input type="text" class="form-control form-control-sm" wire:model.blur="entries.{{ $entry->id }}.value"></td>
                                        <td><input type="text" class="form-control form-control-sm" wire:model.blur="entries.{{ $entry->id }}.comment"></td>
                                        <td class="text-center"><input type="checkbox" class="form-check-input" wire:model.live="entries.{{ $entry->id }}.not_applicable"></td>
                                    </tr>
                                @endforeach
                            </tbody>
                        </table>
                    </div>
                </fieldset>

                @if($consumablesStatus)
                    <h6 class="mt-3">Required consumables</h6>
                    <div class="table-responsive">
                        <table class="table table-sm align-middle mb-0">
                            <thead class="table-light"><tr><th>Item</th><th>Code</th><th>UoM</th><th class="text-end">Required</th><th class="text-end">In store</th><th>Status</th></tr></thead>
                            <tbody>
                                @foreach($consumablesStatus as $item)
                                    <tr>
                                        <td>{{ $item['name'] }}</td>
                                        <td>{{ $item['code'] ?? '—' }}</td>
                                        <td>{{ $item['uom'] ?? '—' }}</td>
                                        <td class="text-end">{{ $item['required'] + 0 }}</td>
                                        <td class="text-end">{{ $item['in_store'] ?? 'not a stock item' }}</td>
                                        <td><span class="badge {{ $item['short'] ? 'bg-danger' : 'bg-success' }}">{{ $item['short'] ? 'Short' : 'OK' }}</span></td>
                                    </tr>
                                @endforeach
                            </tbody>
                        </table>
                    </div>
                @endif
            </div>
            <div class="modal-footer">
                @if($canFill && $consumablesStatus && $instance->job)
                    <button type="button" class="btn btn-outline-primary me-auto" wire:click="requestConsumables" wire:confirm="Request these consumables from the engineering store?">
                        <i class="fa-solid fa-boxes-stacked me-1"></i>Request from store
                    </button>
                @endif
                <button type="button" class="btn btn-secondary" wire:click="closeChecklist">Close</button>
                @if($canFill)
                    <button type="button" class="btn btn-success" wire:click="completeChecklist"><i class="fa-solid fa-check me-1"></i>Complete PPM</button>
                @endif
            </div>
        </x-engineering.modal>
    @endif

    {{-- Schedule form --}}
    @if($formOpen)
        <x-engineering.modal :title="$editingId ? 'Edit PPM schedule' : 'Add PPM schedule'" close="closeForm" size="modal-xl">
            <form wire:submit="save">
                <div class="modal-body">
                    <div class="row g-3">
                        <div class="col-md-6">
                            <label class="form-label">Equipment <span class="text-danger">*</span></label>
                            <div class="input-group">
                                <input type="text" class="form-control @error('form.equipment_id') is-invalid @enderror" wire:model.live.debounce.300ms="equipmentSearch"
                                       placeholder="Search the inventory" @readonly($form['equipment_id'] || $editingId)>
                                @if($form['equipment_id'] && ! $editingId)
                                    <button type="button" class="btn btn-outline-secondary" wire:click="clearEquipment"><i class="fa-solid fa-xmark"></i></button>
                                @endif
                            </div>
                            @error('form.equipment_id') <div class="text-danger small mt-1">{{ $message }}</div> @enderror
                            @if($equipmentMatches->isNotEmpty())
                                <div class="list-group mt-1 shadow-sm">
                                    @foreach($equipmentMatches as $match)
                                        <button type="button" class="list-group-item list-group-item-action" wire:click="pickEquipment('{{ $match->id }}')">{{ $match->name }} <span class="text-muted small">{{ $match->code }}</span></button>
                                    @endforeach
                                </div>
                            @endif
                        </div>
                        <div class="col-md-3">
                            <label class="form-label">PPM is done by <span class="text-danger">*</span></label>
                            <select class="form-select @error('form.performer') is-invalid @enderror" wire:model="form.performer">
                                @foreach(Lookups::PPM_PERFORMERS as $value => $label)<option value="{{ $value }}">{{ $label }}</option>@endforeach
                            </select>
                            @error('form.performer') <div class="invalid-feedback">{{ $message }}</div> @enderror
                        </div>
                        <div class="col-md-3">
                            <label class="form-label">Responsible engineer <span class="text-danger">*</span></label>
                            <select class="form-select @error('form.responsible_employee_id') is-invalid @enderror" wire:model="form.responsible_employee_id">
                                <option value="">Select</option>
                                @foreach($engineers as $engineer)<option value="{{ $engineer->id }}">{{ $engineer->first_name }} {{ $engineer->last_name }}</option>@endforeach
                            </select>
                            @error('form.responsible_employee_id') <div class="invalid-feedback">{{ $message }}</div> @enderror
                        </div>
                        <div class="col-md-4">
                            <label class="form-label">Frequency <span class="text-danger">*</span></label>
                            <select class="form-select" wire:model.live="form.frequency">
                                @foreach(Lookups::PPM_FREQUENCIES as $value => $label)<option value="{{ $value }}">{{ $label }}</option>@endforeach
                            </select>
                        </div>
                        @if($form['frequency'] === 'running_hours')
                            <div class="col-md-4">
                                <label class="form-label">After how many hours of use <span class="text-danger">*</span></label>
                                <input type="number" min="1" class="form-control @error('form.interval_hours') is-invalid @enderror" wire:model="form.interval_hours">
                                @error('form.interval_hours') <div class="invalid-feedback">{{ $message }}</div> @enderror
                            </div>
                            <div class="col-md-4">
                                <label class="form-label">Expected due date <span class="text-danger">*</span></label>
                                <input type="date" class="form-control @error('form.next_due_at') is-invalid @enderror" wire:model="form.next_due_at">
                                @error('form.next_due_at') <div class="invalid-feedback">{{ $message }}</div> @enderror
                            </div>
                        @else
                            <div class="col-md-8 d-flex align-items-end"><div class="form-text">The next due date is calculated from the last PPM, or from the installation date if it was never done.</div></div>
                        @endif
                    </div>

                    <div class="d-flex justify-content-between align-items-center mt-4 mb-2">
                        <h6 class="mb-0">Checklist tasks <span class="text-muted fw-normal small">(from the manufacturer's PPM checklist)</span></h6>
                        <button type="button" class="btn btn-sm btn-outline-primary" wire:click="addTask"><i class="fa-solid fa-plus me-1"></i>Add task</button>
                    </div>
                    @error('tasks') <div class="text-danger small mb-2">{{ $message }}</div> @enderror
                    @foreach($tasks as $index => $task)
                        <div class="row g-2 mb-2 align-items-center" wire:key="task-{{ $index }}">
                            <div class="col-auto text-muted" style="width: 2rem;">{{ $index + 1 }}</div>
                            <div class="col"><input type="text" class="form-control @error('tasks.'.$index.'.description') is-invalid @enderror" placeholder="Task" wire:model="tasks.{{ $index }}.description"></div>
                            <div class="col-md-3"><input type="text" class="form-control" placeholder="Expected value / tolerance" wire:model="tasks.{{ $index }}.expected_value"></div>
                            <div class="col-auto">
                                <div class="form-check mb-0">
                                    <input class="form-check-input" type="checkbox" id="mandatory-{{ $index }}" wire:model="tasks.{{ $index }}.mandatory">
                                    <label class="form-check-label small" for="mandatory-{{ $index }}">Mandatory</label>
                                </div>
                            </div>
                            <div class="col-auto"><button type="button" class="btn btn-link text-danger" wire:click="removeTask({{ $index }})"><i class="fa-solid fa-trash"></i></button></div>
                        </div>
                    @endforeach

                    <div class="d-flex justify-content-between align-items-center mt-4 mb-2">
                        <h6 class="mb-0">Required consumables</h6>
                        <button type="button" class="btn btn-sm btn-outline-primary" wire:click="addConsumable"><i class="fa-solid fa-plus me-1"></i>Add consumable</button>
                    </div>
                    @forelse($consumables as $index => $item)
                        <div class="row g-2 mb-2" wire:key="consumable-{{ $index }}">
                            <div class="col-md-5"><input type="text" class="form-control @error('consumables.'.$index.'.item_name') is-invalid @enderror" placeholder="Item" wire:model="consumables.{{ $index }}.item_name"></div>
                            <div class="col-md-3"><input type="text" class="form-control" placeholder="Item code" wire:model="consumables.{{ $index }}.item_code"></div>
                            <div class="col-md-1"><input type="text" class="form-control" placeholder="UoM" wire:model="consumables.{{ $index }}.uom"></div>
                            <div class="col-md-2"><input type="number" min="0" step="any" class="form-control @error('consumables.'.$index.'.qty_required') is-invalid @enderror" placeholder="Qty" wire:model="consumables.{{ $index }}.qty_required"></div>
                            <div class="col-md-1 text-end"><button type="button" class="btn btn-link text-danger" wire:click="removeConsumable({{ $index }})"><i class="fa-solid fa-trash"></i></button></div>
                        </div>
                    @empty
                        <p class="text-muted small mb-0">None. Add the consumables this PPM uses so shortages show before the work starts.</p>
                    @endforelse
                </div>
                <div class="modal-footer">
                    <button type="button" class="btn btn-secondary" wire:click="closeForm">Cancel</button>
                    <button type="submit" class="btn btn-primary"><i class="fa-solid fa-floppy-disk me-1"></i>Save schedule</button>
                </div>
            </form>
        </x-engineering.modal>
    @endif
</x-engineering.page>
