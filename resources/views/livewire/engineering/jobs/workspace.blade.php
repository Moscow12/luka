@php
    use App\Support\Engineering\Eng;
    use App\Support\Engineering\Lookups;

    $requisition = $job->requisition;
    $overdue = $job->isOverdue();
@endphp

<x-engineering.page :title="'Maintenance & procurement · '.$job->number" :subtitle="$job->equipmentName().' · '.$requisition->locationLabel()">
    <x-slot:actions>
        <a href="{{ route('engineering.jobs.mine') }}" class="btn btn-outline-secondary"><i class="fa-solid fa-arrow-left me-1"></i>My jobs</a>
        <a href="{{ route('engineering.jobs.summary', $job) }}" class="btn btn-outline-secondary"><i class="fa-solid fa-file-lines me-1"></i>MRV summary</a>
    </x-slot:actions>

    {{-- Status strip --}}
    <div class="card mb-3 {{ $requisition->isEmergency() || $job->is_rework ? 'border-danger' : '' }}">
        <div class="card-body py-3 d-flex flex-wrap align-items-center gap-2">
            <x-engineering.job-badges :job="$job" />
            <span class="badge bg-light text-dark border">{{ Lookups::label(Lookups::WORK_TYPES, $job->type_of_work) }}</span>
            <span class="badge bg-light text-dark border">{{ $job->section->name }}</span>
            <span class="ms-md-auto {{ $overdue ? 'text-danger fw-semibold' : 'text-muted' }}">
                @if($job->isOpen())
                    @if($overdue)<i class="fa-solid fa-triangle-exclamation me-1"></i>@endif
                    Day {{ $job->daysOpen() }} of {{ $job->days_to_be_done ?? '—' }} allowed
                @else
                    Signed out {{ Eng::dt($job->completed_at) }} · TAT {{ number_format((float) $job->tatDays(), 2) }} days
                @endif
            </span>
        </div>
    </div>

    @if($job->isLocked())
        <div class="alert alert-secondary"><i class="fa-solid fa-lock me-1"></i>This job has been signed out. It is read-only for everyone; a correction is made by sending it back for rework.</div>
    @elseif(! $canWork)
        <div class="alert alert-info"><i class="fa-solid fa-eye me-1"></i>You are viewing this job. Only the assigned engineer and assistants can update it.</div>
    @endif

    <div class="row g-4">
        <div class="col-lg-8">
            {{-- Job details --}}
            <div class="card mb-4">
                <div class="card-header"><h6 class="mb-0"><i class="fa-solid fa-circle-info me-2"></i>Job details</h6></div>
                <div class="card-body">
                    <div class="row g-3">
                        <x-engineering.detail label="Requisition date">{{ Eng::dt($requisition->requested_at) }}</x-engineering.detail>
                        <x-engineering.detail label="Requesting department">{{ $requisition->department?->name ?? '—' }}</x-engineering.detail>
                        <x-engineering.detail label="Requested staff">
                            {{ $requisition->requester_name }}
                            @if($requisition->requester_phone)<a href="tel:{{ $requisition->requester_phone }}" class="ms-1"><i class="fa-solid fa-phone"></i></a>@endif
                        </x-engineering.detail>
                        <x-engineering.detail label="Administrative responsibility">{{ $requisition->requester_role ?? '—' }}</x-engineering.detail>
                        <x-engineering.detail label="Place / floor / room">{{ $requisition->locationLabel() }}</x-engineering.detail>
                        <x-engineering.detail label="Equipment">{{ $job->equipmentName() }}</x-engineering.detail>
                        <x-engineering.detail label="Inventory code / ID">{{ $job->equipmentCode() }}</x-engineering.detail>
                        <x-engineering.detail label="Serial number">{{ $job->equipment?->serial_number ?? '—' }}</x-engineering.detail>
                        <x-engineering.detail label="Requisition description" class="col-12 col-md-12 col-lg-12">{{ $job->problem_description }}</x-engineering.detail>
                    </div>
                </div>
            </div>

            {{-- Maintenance process: job notes --}}
            <div class="card mb-4">
                <div class="card-header d-flex justify-content-between align-items-center">
                    <h6 class="mb-0"><i class="fa-solid fa-screwdriver-wrench me-2"></i>Maintenance process · job notes</h6>
                    <a href="{{ route('engineering.jobs.rca', $job) }}" class="btn btn-sm btn-outline-primary">
                        <i class="fa-solid fa-diagram-project me-1"></i>5 Why analysis @if($job->rca)<span class="badge bg-primary ms-1">{{ ucfirst($job->rca->status) }}</span>@endif
                    </a>
                </div>
                <div class="card-body">
                    @forelse($job->notes as $note)
                        <div class="border-start border-3 ps-3 mb-3 {{ $note->kind === 'system' ? 'border-warning' : ($note->kind === 'requester' ? 'border-info' : 'border-primary') }}" wire:key="note-{{ $note->id }}">
                            <div class="small text-muted">
                                {{ $note->author?->full_name }}
                                @if($note->kind !== 'engineer')<span class="badge bg-light text-dark border">{{ ucfirst($note->kind) }}</span>@endif
                                · {{ Eng::dt($note->created_at) }}
                            </div>
                            <div style="white-space: pre-line;">{{ $note->body }}</div>
                        </div>
                    @empty
                        <p class="text-muted mb-3">No notes yet. Write what you found and what needs to be done.</p>
                    @endforelse

                    @if($canWork)
                        <form wire:submit="saveNote">
                            <textarea class="form-control @error('noteBody') is-invalid @enderror" rows="3" wire:model="noteBody" placeholder="What you found, what you did, what is still needed…"></textarea>
                            @error('noteBody') <div class="invalid-feedback">{{ $message }}</div> @enderror
                            <button type="submit" class="btn btn-primary mt-2"><i class="fa-solid fa-floppy-disk me-1"></i>Save note</button>
                        </form>
                    @endif
                </div>
            </div>

            @if($job->oxygenOrder)
                {{-- Oxygen delivery --}}
                <div class="card mb-4">
                    <div class="card-header"><h6 class="mb-0"><i class="fa-solid fa-lungs me-2"></i>Oxygen delivery</h6></div>
                    <div class="card-body">
                        <div class="row g-3">
                            <x-engineering.detail label="Ordered">{{ $job->oxygenOrder->qty_ordered }} × {{ $job->oxygenOrder->cylinder_type }}</x-engineering.detail>
                            @if($job->oxygenOrder->isDelivered())
                                <x-engineering.detail label="Delivered">{{ $job->oxygenOrder->qty_delivered }} on {{ Eng::dt($job->oxygenOrder->delivered_at) }}</x-engineering.detail>
                                <x-engineering.detail label="Delivered by">{{ Eng::name($job->oxygenOrder->deliveredBy) }}</x-engineering.detail>
                                <x-engineering.detail label="Received by">{{ $job->oxygenOrder->received_by }}</x-engineering.detail>
                            @endif
                        </div>
                    </div>
                </div>
            @else
                {{-- Spare parts & procurement --}}
                <div class="card mb-4">
                    <div class="card-header d-flex justify-content-between align-items-center">
                        <h6 class="mb-0"><i class="fa-solid fa-boxes-stacked me-2"></i>Spare parts & procurement</h6>
                        @if($canWork)
                            <button type="button" class="btn btn-sm btn-outline-primary" wire:click="openSpares"><i class="fa-solid fa-plus me-1"></i>Spare required</button>
                        @endif
                    </div>
                    <div class="card-body">
                        {{-- The three tracker flags --}}
                        <div class="row g-2 mb-3">
                            <div class="col-md-4">
                                <div class="border rounded p-2 h-100 {{ $job->spare_required_at ? 'border-success' : '' }}">
                                    <i class="fa-solid {{ $job->spare_required_at ? 'fa-square-check text-success' : 'fa-square text-muted' }} me-1"></i>Spare required
                                    <div class="small text-muted">{{ $job->spare_required_at ? Eng::dt($job->spare_required_at) : 'Not requested' }}</div>
                                </div>
                            </div>
                            <div class="col-md-4">
                                <div class="border rounded p-2 h-100 {{ $job->procurement_order_at ? 'border-success' : '' }}">
                                    <i class="fa-solid {{ $job->procurement_order_at ? 'fa-square-check text-success' : 'fa-square text-muted' }} me-1"></i>Procurement order made
                                    <div class="small">
                                        @if($job->jobCard)
                                            <a href="{{ route('engineering.jobs.card', $job) }}">{{ $job->jobCard->number }}</a>
                                            <span class="badge {{ Lookups::JOB_CARD_STATUS_BADGES[$job->jobCard->status] }}">{{ Lookups::JOB_CARD_STATUSES[$job->jobCard->status] }}</span>
                                        @elseif($canWork && in_array($job->status->value, ['in_progress', 'awaiting_spares']))
                                            <a href="{{ route('engineering.jobs.card', $job) }}">Raise a job card</a>
                                        @else
                                            <span class="text-muted">No job card</span>
                                        @endif
                                    </div>
                                </div>
                            </div>
                            <div class="col-md-4">
                                <div class="border rounded p-2 h-100 {{ $job->client_informed_at ? 'border-success' : '' }}">
                                    <i class="fa-solid {{ $job->client_informed_at ? 'fa-square-check text-success' : 'fa-square text-muted' }} me-1"></i>Client informed
                                    <div class="small">
                                        @if($job->client_informed_at)
                                            <span class="text-muted">{{ Eng::dt($job->client_informed_at) }}</span>
                                        @elseif($canWork && $job->spare_required_at)
                                            <a href="#" wire:click.prevent="informClient">Notify {{ $requisition->requester_name }}</a>
                                        @else
                                            <span class="text-muted">Not yet</span>
                                        @endif
                                    </div>
                                </div>
                            </div>
                        </div>

                        @forelse($job->sparesRequisitions as $sr)
                            <div class="border rounded mb-3" wire:key="sr-{{ $sr->id }}">
                                <div class="d-flex justify-content-between align-items-center px-3 py-2 bg-light rounded-top">
                                    <span class="fw-semibold">{{ $sr->number }}</span>
                                    <span class="small text-muted">{{ Eng::dt($sr->requested_at) }} · {{ ucfirst(str_replace('_', ' ', $sr->status)) }}</span>
                                </div>
                                <div class="table-responsive">
                                    <table class="table table-sm align-middle mb-0">
                                        <thead><tr><th>S/N</th><th>Spare used</th><th>UoM</th><th>Item code</th><th class="text-end">Qty</th><th class="text-end">Issued</th><th>Date consumed</th><th>Store</th></tr></thead>
                                        <tbody>
                                            @foreach($sr->lines as $line)
                                                <tr>
                                                    <td>{{ $line->seq }}</td>
                                                    <td>{{ $line->spare_name }} @unless($line->product_id)<span class="badge bg-light text-dark border">Non-stock</span>@endunless</td>
                                                    <td>{{ $line->uom ?? '—' }}</td>
                                                    <td>{{ $line->item_code ?? '—' }}</td>
                                                    <td class="text-end">{{ $line->qty_requested + 0 }}</td>
                                                    <td class="text-end">{{ $line->qty_issued + 0 }}</td>
                                                    <td>{{ Eng::date($line->date_consumed) }}</td>
                                                    <td>
                                                        {{ Lookups::SPARES_LINE_STATUSES[$line->status] }}
                                                        @if($line->reject_reason)<div class="small text-danger">{{ $line->reject_reason }}</div>@endif
                                                    </td>
                                                </tr>
                                            @endforeach
                                        </tbody>
                                    </table>
                                </div>
                            </div>
                        @empty
                            <div class="form-check">
                                <input class="form-check-input" type="checkbox" id="no-spares" @checked($job->no_spares_consumed) @disabled(! $canWork) wire:click="toggleNoSpares">
                                <label class="form-check-label fw-semibold" for="no-spares">NO SPARE(S) CONSUMED FOR THIS SERVICE</label>
                            </div>
                        @endforelse
                    </div>
                </div>

                {{-- Safety testing --}}
                <div class="card mb-4">
                    <div class="card-header d-flex justify-content-between align-items-center">
                        <h6 class="mb-0"><i class="fa-solid fa-shield-halved me-2"></i>Safety testing</h6>
                        @if($needsSafetyTests)<span class="badge bg-light text-dark border">All three required for {{ $job->section->name }}</span>@endif
                    </div>
                    <ul class="list-group list-group-flush">
                        @foreach(Lookups::SAFETY_TESTS as $type => $label)
                            @php $test = $job->safetyTests->firstWhere('test_type', $type); @endphp
                            <li class="list-group-item" wire:key="test-{{ $type }}">
                                <div class="d-flex flex-wrap align-items-center gap-2">
                                    <div class="form-check mb-0 flex-grow-1">
                                        <input class="form-check-input" type="checkbox" id="test-{{ $type }}" @checked($test?->isSatisfied()) @disabled(! $canWork) wire:click="toggleTest('{{ $type }}')">
                                        <label class="form-check-label" for="test-{{ $type }}">{{ $label }}</label>
                                        @if($test?->isSatisfied())
                                            <div class="small text-muted">
                                                @if($test->not_applicable)<span class="badge bg-secondary">N/A</span> {{ $test->comment }} · @endif
                                                {{ $test->completedBy?->full_name }} · {{ Eng::dt($test->completed_at) }}
                                                @if($test->completed && $test->comment) · {{ $test->comment }} @endif
                                            </div>
                                        @endif
                                    </div>
                                    @if($canWork && ! $test?->isSatisfied())
                                        <div class="input-group input-group-sm" style="max-width: 320px;">
                                            <input type="text" class="form-control" placeholder="Result / comment" wire:model="testComments.{{ $type }}">
                                            <button type="button" class="btn btn-outline-secondary" wire:click="markTestNotApplicable('{{ $type }}')" title="Not applicable (comment required)">N/A</button>
                                        </div>
                                    @endif
                                </div>
                            </li>
                        @endforeach
                    </ul>
                </div>
            @endif

            {{-- Final status & closure --}}
            <div class="card mb-4" id="closure">
                <div class="card-header"><h6 class="mb-0"><i class="fa-solid fa-flag-checkered me-2"></i>Final status & job closure</h6></div>
                <div class="card-body">
                    @if($job->isLocked())
                        <div class="row g-3">
                            <x-engineering.detail label="Final status">{{ Lookups::label(Lookups::FINAL_STATUSES, $job->final_status) }}</x-engineering.detail>
                            <x-engineering.detail label="Signed out by">{{ $job->signedOutBy?->full_name ?? '—' }}</x-engineering.detail>
                            <x-engineering.detail label="Closure date & time">{{ Eng::dt($job->completed_at) }}</x-engineering.detail>
                            <x-engineering.detail label="Requester rating">{{ $job->currentFeedback?->rating->label() ?? 'No score yet' }}</x-engineering.detail>
                            <x-engineering.detail label="Recommendation" class="col-12 col-md-12 col-lg-12">{{ $job->recommendation ?: '—' }}</x-engineering.detail>
                        </div>
                    @else
                        @if($job->assistance_explanation)
                            <div class="alert alert-warning"><strong>Assistance requested:</strong> {{ $job->assistance_explanation }}</div>
                        @endif

                        {{-- Pre-closure checks --}}
                        @if($blockers)
                            <div class="alert alert-light border">
                                <div class="fw-semibold mb-1">Before the job can be closed as complete:</div>
                                <ul class="mb-0 ps-3">
                                    @foreach($blockers as $blocker)<li>{{ $blocker }}</li>@endforeach
                                </ul>
                                @if($job->ppmInstance && ! $job->ppmInstance->isCompleted())
                                    <a href="{{ route('engineering.ppm', ['checklist' => $job->ppmInstance->id]) }}" class="btn btn-sm btn-outline-primary mt-2">Open PPM checklist</a>
                                @endif
                            </div>
                        @else
                            <div class="alert alert-success py-2"><i class="fa-solid fa-circle-check me-1"></i>All pre-closure checks are done.</div>
                        @endif

                        @if($canWork)
                            <form wire:submit="close">
                                <label class="form-label fw-semibold">Final status <span class="text-danger">*</span></label>
                                <div class="row g-2 mb-3">
                                    @foreach(Lookups::FINAL_STATUSES as $value => $label)
                                        <div class="col-md-4">
                                            <input type="radio" class="btn-check" id="final-{{ $value }}" value="{{ $value }}" wire:model.live="finalStatus">
                                            <label class="btn w-100 h-100 d-flex align-items-center justify-content-center {{ $value === 'job_complete' ? 'btn-outline-success' : ($value === 'beyond_repair' ? 'btn-outline-dark' : 'btn-outline-warning') }}" for="final-{{ $value }}">{{ $label }}</label>
                                        </div>
                                    @endforeach
                                </div>
                                @error('finalStatus') <div class="text-danger small mb-2">{{ $message }}</div> @enderror

                                @if($finalStatus === 'assistance_needed')
                                    <div class="mb-3">
                                        <label class="form-label">What assistance is needed? <span class="text-danger">*</span></label>
                                        <textarea class="form-control @error('explanation') is-invalid @enderror" rows="2" wire:model="explanation"></textarea>
                                        @error('explanation') <div class="invalid-feedback">{{ $message }}</div> @enderror
                                        <div class="form-text">The job stays open and the turn-around clock keeps running.</div>
                                    </div>
                                @endif

                                @if($finalStatus === 'job_complete' && $job->oxygenOrder)
                                    <div class="row g-3 mb-3">
                                        <div class="col-md-4">
                                            <label class="form-label">Quantity delivered <span class="text-danger">*</span></label>
                                            <input type="number" min="1" class="form-control" wire:model="qtyDelivered">
                                        </div>
                                        <div class="col-md-8">
                                            <label class="form-label">Received by (ward staff) <span class="text-danger">*</span></label>
                                            <input type="text" class="form-control" wire:model="receivedBy">
                                        </div>
                                    </div>
                                @endif

                                @if($finalStatus === 'job_complete' && $job->equipment)
                                    <div class="mb-3">
                                        <label class="form-label">Set equipment status to</label>
                                        <select class="form-select" wire:model="equipmentStatus">
                                            @foreach(Lookups::EQUIPMENT_STATUSES as $value => $label)
                                                <option value="{{ $value }}">{{ $label }}</option>
                                            @endforeach
                                        </select>
                                    </div>
                                @elseif($finalStatus === 'beyond_repair' && $job->equipment)
                                    <div class="alert alert-dark py-2">The equipment will be set <strong>out of service</strong> and flagged for removal from the inventory.</div>
                                @endif

                                <div class="mb-3">
                                    <label class="form-label">Comments / recommendation to the user</label>
                                    <textarea class="form-control" rows="2" wire:model="recommendation" placeholder="What the users should observe from now on"></textarea>
                                </div>

                                <div class="d-flex flex-wrap justify-content-between align-items-center gap-2">
                                    <span class="small text-muted">Signed out by {{ auth()->user()->full_name }} · date and time are stamped on save</span>
                                    <button type="submit" class="btn btn-success" wire:loading.attr="disabled" wire:target="close"><i class="fa-solid fa-floppy-disk me-1"></i>Save</button>
                                </div>
                            </form>
                        @endif
                    @endif
                </div>
            </div>
        </div>

        {{-- Side column --}}
        <div class="col-lg-4">
            <div class="card mb-4">
                <div class="card-header"><h6 class="mb-0"><i class="fa-solid fa-user-gear me-2"></i>Assignment</h6></div>
                <div class="card-body">
                    <div class="mb-2"><span class="text-muted small d-block">Assigned engineer</span><span class="fw-semibold">{{ Eng::name($job->engineer) }}</span></div>
                    <div class="mb-2">
                        <span class="text-muted small d-block">Assistant engineers</span>
                        @forelse($job->assistants as $assistant)
                            <span class="badge bg-light text-dark border">{{ Eng::name($assistant) }}</span>
                        @empty
                            <span class="text-muted">None</span>
                        @endforelse
                    </div>
                    <div class="mb-2"><span class="text-muted small d-block">Assigned by</span>{{ $job->assignedBy?->full_name ?? '—' }} · {{ Eng::dt($job->assigned_at) }}</div>
                    <div class="mb-2"><span class="text-muted small d-block">Days to be done</span>{{ $job->days_to_be_done ?? '—' }}</div>
                    @if($job->assignment_note)
                        <div class="alert alert-info py-2 mb-0 mt-2"><i class="fa-solid fa-note-sticky me-1"></i>{{ $job->assignment_note }}</div>
                    @endif
                </div>
            </div>

            <div class="list-group mb-4">
                @if($job->jobCard || ($canWork && ! $job->oxygenOrder))
                    <a href="{{ route('engineering.jobs.card', $job) }}" class="list-group-item list-group-item-action d-flex justify-content-between align-items-center">
                        <span><i class="fa-solid fa-id-card fa-fw me-2"></i>Job card</span>
                        <span class="text-muted small">{{ $job->jobCard?->number ?? 'Raise' }}</span>
                    </a>
                @endif
                <a href="{{ route('engineering.jobs.rca', $job) }}" class="list-group-item list-group-item-action d-flex justify-content-between align-items-center">
                    <span><i class="fa-solid fa-diagram-project fa-fw me-2"></i>5-Why root cause analysis</span>
                    <span class="text-muted small">{{ $job->rca?->number ?? 'Start' }}</span>
                </a>
                <a href="{{ route('engineering.reports.technical', ['job' => $job->id]) }}" class="list-group-item list-group-item-action d-flex justify-content-between align-items-center">
                    <span><i class="fa-solid fa-file-signature fa-fw me-2"></i>Technical report</span>
                    <span class="text-muted small">{{ $job->technicalReport ? 'View' : 'Write' }}</span>
                </a>
                @if($job->ppmInstance)
                    <a href="{{ route('engineering.ppm', ['checklist' => $job->ppmInstance->id]) }}" class="list-group-item list-group-item-action d-flex justify-content-between align-items-center">
                        <span><i class="fa-solid fa-list-check fa-fw me-2"></i>PPM checklist</span>
                        <span class="text-muted small">{{ $job->ppmInstance->entries->where('done', true)->count() }} / {{ $job->ppmInstance->entries->count() }} ticked</span>
                    </a>
                @endif
            </div>
        </div>
    </div>

    {{-- Spares requisition --}}
    @if($sparesOpen)
        <x-engineering.modal :title="'Spares requisition · '.$job->number" close="closeSpares" size="modal-xl">
            <div class="modal-body">
                <label class="form-label">Add items</label>
                <div class="input-group">
                    <span class="input-group-text"><i class="fa-solid fa-magnifying-glass"></i></span>
                    <input type="text" class="form-control" wire:model.live.debounce.300ms="spareSearch" placeholder="Search the store by item name or code">
                    <button type="button" class="btn btn-outline-secondary" wire:click="addNonStockItem" title="Add an item the store does not stock">
                        <i class="fa-solid fa-plus me-1"></i>Add as non-stock item
                    </button>
                </div>
                @if($productMatches->isNotEmpty())
                    <div class="list-group mt-1 shadow-sm">
                        @foreach($productMatches as $product)
                            <button type="button" class="list-group-item list-group-item-action" wire:click="addStockItem('{{ $product->id }}')" wire:key="product-{{ $product->id }}">
                                {{ $product->name }} <span class="text-muted small">{{ $product->code }} · {{ $product->unit }}</span>
                            </button>
                        @endforeach
                    </div>
                @endif
                @error('spareLines') <div class="text-danger small mt-2">{{ $message }}</div> @enderror

                @if($spareLines)
                    <div class="table-responsive mt-3">
                        <table class="table align-middle">
                            <thead class="table-light"><tr><th>S/N</th><th>Spare used</th><th>UoM</th><th>Item code</th><th style="width: 110px;">Quantity</th><th style="width: 170px;">Date consumed</th><th></th></tr></thead>
                            <tbody>
                                @foreach($spareLines as $index => $line)
                                    <tr wire:key="line-{{ $index }}">
                                        <td>{{ $index + 1 }}</td>
                                        @if($line['product_id'])
                                            <td>{{ $line['spare_name'] }}</td>
                                            <td>{{ $line['uom'] ?: '—' }}</td>
                                            <td>{{ $line['item_code'] ?: '—' }}</td>
                                        @else
                                            <td>
                                                <input type="text" class="form-control form-control-sm @error('spareLines.'.$index.'.spare_name') is-invalid @enderror" wire:model="spareLines.{{ $index }}.spare_name" placeholder="Item name">
                                                <span class="badge bg-light text-dark border mt-1">Non-stock · flagged for procurement</span>
                                            </td>
                                            <td><input type="text" class="form-control form-control-sm" wire:model="spareLines.{{ $index }}.uom" placeholder="pc"></td>
                                            <td><input type="text" class="form-control form-control-sm" wire:model="spareLines.{{ $index }}.item_code"></td>
                                        @endif
                                        <td><input type="number" min="0" step="any" class="form-control form-control-sm @error('spareLines.'.$index.'.quantity') is-invalid @enderror" wire:model="spareLines.{{ $index }}.quantity"></td>
                                        <td><input type="date" class="form-control form-control-sm" wire:model="spareLines.{{ $index }}.date_consumed"></td>
                                        <td class="text-end"><button type="button" class="btn btn-sm btn-link text-danger" wire:click="removeSpareLine({{ $index }})"><i class="fa-solid fa-trash"></i></button></td>
                                    </tr>
                                @endforeach
                            </tbody>
                        </table>
                    </div>
                @else
                    <p class="text-muted mt-3 mb-0">Search for a spare above to start the list.</p>
                @endif
            </div>
            <div class="modal-footer">
                <span class="small text-muted me-auto">Sending sets the job to “Awaiting spares”.</span>
                <button type="button" class="btn btn-secondary" wire:click="closeSpares">Cancel</button>
                <button type="button" class="btn btn-primary" wire:click="sendSpares" wire:loading.attr="disabled" wire:target="sendSpares">
                    <i class="fa-solid fa-paper-plane me-1"></i>Send to engineering store
                </button>
            </div>
        </x-engineering.modal>
    @endif
</x-engineering.page>
