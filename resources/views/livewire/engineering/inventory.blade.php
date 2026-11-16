@php
    use App\Support\Engineering\Eng;
    use App\Support\Engineering\Lookups;

    $uptime = $total > 0 ? round($working / $total * 100, 1) : null;
@endphp

<x-engineering.page title="Inventory" subtitle="Equipment register per department and room">
    <x-slot:actions>
        <a href="{{ route('engineering.inventory.rooms') }}" class="btn btn-outline-secondary"><i class="fa-solid fa-door-open me-1"></i>Rooms & responsible staff</a>
        <a href="{{ route('assets.transfers') }}" class="btn btn-outline-secondary"><i class="fa-solid fa-right-left me-1"></i>Transfers</a>
        <button type="button" class="btn btn-outline-success" wire:click="export"><i class="fa-solid fa-file-excel me-1"></i>Export to Excel</button>
        @if($canManage)
            <button type="button" class="btn btn-primary" wire:click="create"><i class="fa-solid fa-plus me-1"></i>Add equipment</button>
        @endif
    </x-slot:actions>

    <ul class="nav nav-pills mb-3 flex-nowrap overflow-auto">
        @foreach(\App\Livewire\Engineering\Inventory::VIEWS as $key => $label)
            <li class="nav-item"><button type="button" class="nav-link text-nowrap {{ $view === $key ? 'active' : '' }}" wire:click="setView('{{ $key }}')">{{ $label }}</button></li>
        @endforeach
    </ul>

    <div class="card mb-3">
        <div class="card-body">
            <div class="row g-2 align-items-end">
                <div class="col-md-3">
                    <label class="form-label small">Search</label>
                    <input type="search" class="form-control" placeholder="Name, ID code or serial number" wire:model.live.debounce.400ms="search">
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
                    <label class="form-label small">Room</label>
                    <select class="form-select" wire:model.live="filterRoom" @disabled($filterRooms->isEmpty())>
                        <option value="">All</option>
                        @foreach($filterRooms as $room)<option value="{{ $room->id }}">{{ $room->name }}</option>@endforeach
                    </select>
                </div>
                <div class="col-6 col-md-2">
                    <label class="form-label small">Status</label>
                    <select class="form-select" wire:model.live="filterStatus">
                        <option value="">All</option>
                        @foreach(Lookups::EQUIPMENT_STATUSES as $value => $label)<option value="{{ $value }}">{{ $label }}</option>@endforeach
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
                        <th>S/N</th><th>Code</th><th>Equipment</th><th>Serial</th><th>Location</th><th class="text-center">Qty</th><th>Status</th>
                        <th>{{ $view === 'removed' ? 'Removed' : ($view === 'replacement' ? 'Replace by' : 'Next PPM') }}</th>
                        <th class="text-end">Action</th>
                    </tr>
                </thead>
                <tbody>
                    @forelse($equipment as $item)
                        @php $nextPpm = $item->ppmSchedules->where('status', 'active')->pluck('next_due_at')->filter()->min(); @endphp
                        <tr wire:key="equipment-{{ $item->id }}">
                            <td>{{ $paginated ? $equipment->firstItem() + $loop->index : $loop->iteration }}</td>
                            <td class="text-nowrap"><a href="#" class="fw-semibold text-decoration-none" wire:click.prevent="showDetail('{{ $item->id }}')">{{ $item->code }}</a></td>
                            <td>{{ $item->name }}<div class="small text-muted">{{ $item->section->name }} · {{ $item->manufacturer }}</div></td>
                            <td>{{ $item->serial_number }}</td>
                            <td>{{ $item->room->name }}<div class="small text-muted">{{ $item->department->name }}</div></td>
                            <td class="text-center">{{ $item->quantity }}</td>
                            <td>
                                <span class="badge {{ Lookups::EQUIPMENT_STATUS_BADGES[$item->status] }}">{{ Lookups::EQUIPMENT_STATUSES[$item->status] }}</span>
                                @if($item->flagged_for_removal && $view !== 'removed')<span class="badge bg-dark">Flagged</span>@endif
                            </td>
                            <td class="text-nowrap">
                                @if($view === 'removed')
                                    {{ Eng::dt($item->removed_at, 'd M Y') }}<div class="small text-muted">{{ $item->removal_reason }} · approved by {{ $item->removal_approved_by }}</div>
                                @elseif($view === 'replacement')
                                    <span class="{{ $item->replacementDate()->isPast() ? 'text-danger fw-semibold' : '' }}">{{ Eng::date($item->replacementDate()) }}</span>
                                @else
                                    <span class="{{ $nextPpm && $nextPpm->isPast() ? 'text-danger fw-semibold' : '' }}">{{ Eng::date($nextPpm) }}</span>
                                @endif
                            </td>
                            <td class="text-end text-nowrap">
                                <button type="button" class="btn btn-sm btn-outline-secondary" wire:click="showDetail('{{ $item->id }}')" title="View"><i class="fa-solid fa-eye"></i></button>
                                @if($canManage && $view !== 'removed')
                                    <button type="button" class="btn btn-sm btn-outline-secondary" wire:click="openStatus('{{ $item->id }}')" title="Change status"><i class="fa-solid fa-heart-pulse"></i></button>
                                    <button type="button" class="btn btn-sm btn-outline-secondary" wire:click="edit('{{ $item->id }}')" title="Edit"><i class="fa-solid fa-pen"></i></button>
                                    <button type="button" class="btn btn-sm btn-outline-secondary" wire:click="requestTransfer('{{ $item->id }}')" title="Request transfer to another department / room"><i class="fa-solid fa-right-left"></i></button>
                                    @if($canRemove)
                                        <button type="button" class="btn btn-sm btn-outline-danger" wire:click="openRemove('{{ $item->id }}')" title="Remove from inventory"><i class="fa-solid fa-box-archive"></i></button>
                                    @endif
                                @endif
                            </td>
                        </tr>
                    @empty
                        <x-engineering.empty colspan="9" icon="fa-microscope" text="No equipment matches. Adjust the filters or add equipment." />
                    @endforelse
                </tbody>
            </table>
        </div>
        <div class="card-footer d-flex flex-wrap justify-content-between align-items-center gap-2">
            <div>
                @if($uptime !== null && $view !== 'removed')
                    Uptime of this selection: <strong>{{ $working }}</strong> working / <strong>{{ $total }}</strong> total =
                    <span class="badge {{ $uptime >= $uptimeTarget ? 'bg-success' : 'bg-warning text-dark' }}">{{ number_format($uptime, 1) }}%</span>
                    <span class="text-muted small">(target {{ $uptimeTarget }}%)</span>
                @endif
            </div>
            @if($paginated && $equipment->hasPages()){{ $equipment->links() }}@endif
        </div>
    </div>

    {{-- Detail --}}
    @if($detail)
        <x-engineering.modal :title="$detail->name.' · '.$detail->code" close="showDetail(null)" size="modal-lg">
            <div class="modal-body">
                <div class="row g-3">
                    <x-engineering.detail label="Section">{{ $detail->section->name }}</x-engineering.detail>
                    <x-engineering.detail label="Serial number">{{ $detail->serial_number }}</x-engineering.detail>
                    <x-engineering.detail label="Model">{{ $detail->model ?? '—' }}</x-engineering.detail>
                    <x-engineering.detail label="Quantity">{{ $detail->quantity }}</x-engineering.detail>
                    <x-engineering.detail label="Manufacturer">{{ $detail->manufacturer }}</x-engineering.detail>
                    <x-engineering.detail label="Manufacturer contact" class="col-12 col-md-8 col-lg-9">{{ $detail->manufacturerContact() }}</x-engineering.detail>
                    <x-engineering.detail label="Supplier">{{ $detail->supplier ?? '—' }} @if($detail->under_contract)<span class="badge bg-info">Under contract{{ $detail->contract_expiry ? ' to '.Eng::date($detail->contract_expiry) : '' }}</span>@endif</x-engineering.detail>
                    <x-engineering.detail label="Location">{{ $detail->department->name }} · {{ $detail->room->label() }}
                        @if($pending = $detail->assetRegistry?->pendingTransfer)<div class="small text-warning">Transfer pending to {{ $pending->toDepartment?->name }} · {{ $pending->toLocation?->name }}</div>@endif
                    </x-engineering.detail>
                    <x-engineering.detail label="Installed">{{ Eng::date($detail->installation_date) }}</x-engineering.detail>
                    <x-engineering.detail label="Condition when received">{{ strtoupper($detail->condition_received) }}</x-engineering.detail>
                    <x-engineering.detail label="Predicted replacement">{{ Eng::date($detail->replacementDate()) }}</x-engineering.detail>
                    <x-engineering.detail label="Service manual">{{ $detail->manual_location }}</x-engineering.detail>
                    <x-engineering.detail label="Purchase cost">{{ Eng::money($detail->purchase_cost) }}</x-engineering.detail>
                    <x-engineering.detail label="Current value">{{ Eng::money($detail->current_value) }}</x-engineering.detail>
                    <x-engineering.detail label="Inventory done by">{{ $detail->countedBy?->full_name }} · {{ Eng::date($detail->counted_at) }}</x-engineering.detail>
                </div>

                <h6 class="mt-4">PPM</h6>
                @forelse($detail->ppmSchedules as $schedule)
                    <div class="small">{{ Lookups::PPM_PERFORMERS[$schedule->performer] }} PPM · {{ Lookups::PPM_FREQUENCIES[$schedule->frequency] }} · next due {{ Eng::date($schedule->next_due_at) }} · {{ Eng::name($schedule->responsible) }}</div>
                @empty
                    <div class="small text-muted">No PPM schedule. Add one under PPM schedule & checklists.</div>
                @endforelse

                <h6 class="mt-4">Status history</h6>
                <ul class="list-unstyled small mb-0">
                    @foreach($detail->statusLogs as $log)
                        <li class="mb-1">
                            <span class="badge {{ Lookups::EQUIPMENT_STATUS_BADGES[$log->status] }}">{{ Lookups::EQUIPMENT_STATUSES[$log->status] }}</span>
                            {{ Eng::dt($log->created_at) }} · {{ $log->changedBy?->full_name }} @if($log->note)· {{ $log->note }}@endif
                        </li>
                    @endforeach
                </ul>
            </div>
        </x-engineering.modal>
    @endif

    {{-- Add / edit --}}
    @if($formOpen)
        <x-engineering.modal :title="$editingId ? 'Edit equipment' : 'Add equipment'" close="closeForm" size="modal-xl">
            <form wire:submit="save">
                <div class="modal-body">
                    @if(! $editingId)
                        <div class="btn-group mb-3" role="group">
                            <button type="button" class="btn {{ $source === 'new' ? 'btn-primary' : 'btn-outline-primary' }}" wire:click="setSource('new')"><i class="fa-solid fa-plus me-1"></i>New item</button>
                            <button type="button" class="btn {{ $source === 'register' ? 'btn-primary' : 'btn-outline-primary' }}" wire:click="setSource('register')"><i class="fa-solid fa-boxes-stacked me-1"></i>From asset register</button>
                        </div>
                        <p class="small text-muted">
                            {{ $source === 'new'
                                ? 'The item is added to the asset register and the engineering inventory together.'
                                : 'Give an item already in the asset register its engineering details. Only engineering asset classes are listed.' }}
                        </p>
                    @endif

                    <div class="row g-3">
                        @if(! $editingId && $source === 'register')
                            <div class="col-12">
                                <label class="form-label">Asset register item <span class="text-danger">*</span></label>
                                @if($pickedRegistry)
                                    <div class="d-flex align-items-center gap-2 border rounded p-2">
                                        <div class="flex-grow-1">
                                            <strong>{{ $pickedRegistry->label() }}</strong>
                                            <div class="small text-muted">{{ $pickedRegistry->department?->name }} · {{ $pickedRegistry->facilityLocation?->label() }} · Qty {{ $pickedRegistry->quantity }} @if($pickedRegistry->serial_number)· S/N {{ $pickedRegistry->serial_number }}@endif</div>
                                        </div>
                                        <button type="button" class="btn btn-sm btn-outline-secondary" wire:click="clearRegistry">Change</button>
                                    </div>
                                @else
                                    <input type="search" class="form-control @error('form.asset_registry_id') is-invalid @enderror" placeholder="Search by item name, code, serial or model" wire:model.live.debounce.400ms="registrySearch">
                                    @error('form.asset_registry_id') <div class="invalid-feedback">{{ $message }}</div> @enderror
                                    <div class="list-group mt-1" style="max-height: 240px; overflow-y: auto;">
                                        @forelse($registryCandidates as $row)
                                            <button type="button" class="list-group-item list-group-item-action" wire:click="pickRegistry('{{ $row->id }}')" wire:key="reg-{{ $row->id }}">
                                                <strong>{{ $row->label() }}</strong> <span class="badge bg-light text-dark">{{ $row->assetClass?->name }}</span>
                                                <div class="small text-muted">{{ $row->department?->name }} · {{ $row->facilityLocation?->label() }} · Qty {{ $row->quantity }} @if($row->serial_number)· S/N {{ $row->serial_number }}@endif</div>
                                            </button>
                                        @empty
                                            <div class="list-group-item small text-muted">No register items without engineering details. Only classes linked to an engineering section (Asset configuration) are listed.</div>
                                        @endforelse
                                    </div>
                                @endif
                            </div>
                        @endif

                        @if(! $editingId && $source === 'new')
                            <div class="col-md-4">
                                <label class="form-label">Asset class <span class="text-danger">*</span></label>
                                <select class="form-select @error('form.asset_class_id') is-invalid @enderror" wire:model.live="form.asset_class_id">
                                    <option value="">{{ $engineeringClasses->isEmpty() ? 'No class is linked to an engineering section yet' : 'Select' }}</option>
                                    @foreach($engineeringClasses as $class)<option value="{{ $class->id }}">{{ $class->name }} ({{ $class->engSection?->name }})</option>@endforeach
                                </select>
                                @error('form.asset_class_id') <div class="invalid-feedback">{{ $message }}</div> @enderror
                            </div>
                            <div class="col-md-4">
                                <label class="form-label">Catalogue item <span class="text-danger">*</span></label>
                                <select class="form-select @error('form.asset_id') is-invalid @enderror" wire:model.live="form.asset_id" @disabled(! $form['asset_class_id'])>
                                    <option value="">Select, or type a new item name</option>
                                    @foreach($catalogue as $item)<option value="{{ $item->id }}">{{ $item->name }}</option>@endforeach
                                </select>
                                @error('form.asset_id') <div class="invalid-feedback">{{ $message }}</div> @enderror
                            </div>
                            <div class="col-md-4">
                                <label class="form-label">New catalogue item</label>
                                <input type="text" class="form-control @error('form.new_asset_name') is-invalid @enderror" wire:model="form.new_asset_name" @disabled($form['asset_id']) placeholder="e.g. Infusion pump">
                                @error('form.new_asset_name') <div class="invalid-feedback">{{ $message }}</div> @enderror
                            </div>
                        @endif

                        <div class="col-md-5">
                            <label class="form-label">Equipment name <span class="text-danger">*</span></label>
                            <input type="text" class="form-control @error('form.name') is-invalid @enderror" wire:model="form.name">
                            @error('form.name') <div class="invalid-feedback">{{ $message }}</div> @enderror
                        </div>
                        <div class="col-md-3">
                            <label class="form-label">Section <span class="text-danger">*</span></label>
                            <select class="form-select @error('form.section_id') is-invalid @enderror" wire:model="form.section_id">
                                <option value="">Select</option>
                                @foreach($sections as $section)<option value="{{ $section->id }}">{{ $section->name }}</option>@endforeach
                            </select>
                            @error('form.section_id') <div class="invalid-feedback">{{ $message }}</div> @enderror
                        </div>
                        <div class="col-md-4">
                            <label class="form-label">Serial number <span class="text-danger">*</span></label>
                            <input type="text" class="form-control @error('form.serial_number') is-invalid @enderror" wire:model="form.serial_number">
                            @error('form.serial_number') <div class="invalid-feedback">{{ $message }}</div> @enderror
                        </div>

                        <div class="col-md-5">
                            <label class="form-label">Code prefix</label>
                            <div class="input-group">
                                <input type="text" class="form-control text-uppercase @error('codePrefix') is-invalid @enderror" wire:model="codePrefix" placeholder="BMC/LAB/CHM/CFM">
                                <button type="button" class="btn btn-outline-secondary" wire:click="suggestCode">Next free number</button>
                            </div>
                            @error('codePrefix') <div class="text-danger small mt-1">{{ $message }}</div> @enderror
                            <div class="form-text">Class / department / sub-area / equipment type.</div>
                        </div>
                        <div class="col-md-4">
                            <label class="form-label">Code / ID number <span class="text-danger">*</span></label>
                            <input type="text" class="form-control text-uppercase @error('form.code') is-invalid @enderror" wire:model="form.code" placeholder="BMC/LAB/CHM/CFM/001">
                            @error('form.code') <div class="invalid-feedback">{{ $message }}</div> @enderror
                        </div>
                        <div class="col-md-3">
                            <label class="form-label">Model</label>
                            <input type="text" class="form-control" wire:model="form.model">
                        </div>

                        @if(! $editingId && $source === 'new')
                            <div class="col-md-4">
                                <label class="form-label">Department <span class="text-danger">*</span></label>
                                <select class="form-select @error('form.department_id') is-invalid @enderror" wire:model.live="form.department_id">
                                    <option value="">Select</option>
                                    @foreach($formDepartments as $department)<option value="{{ $department->id }}">{{ $department->name }}</option>@endforeach
                                </select>
                                @error('form.department_id') <div class="invalid-feedback">{{ $message }}</div> @enderror
                            </div>
                            <div class="col-md-4">
                                <label class="form-label">Room <span class="text-danger">*</span></label>
                                <select class="form-select @error('form.room_id') is-invalid @enderror" wire:model="form.room_id" @disabled($formRooms->isEmpty())>
                                    <option value="">{{ $form['department_id'] && $formRooms->isEmpty() ? 'Register a room for this department first' : 'Select' }}</option>
                                    @foreach($formRooms as $room)<option value="{{ $room->id }}">{{ $room->label() }}</option>@endforeach
                                </select>
                                @error('form.room_id') <div class="invalid-feedback">{{ $message }}</div> @enderror
                            </div>
                            <div class="col-6 col-md-2">
                                <label class="form-label">Quantity <span class="text-danger">*</span></label>
                                <input type="number" min="1" class="form-control @error('form.quantity') is-invalid @enderror" wire:model="form.quantity">
                                <div class="form-text">1 for a numbered unit.</div>
                            </div>
                        @elseif($editingId)
                            <div class="col-md-10">
                                <label class="form-label">Location</label>
                                <div class="d-flex align-items-center gap-2 border rounded p-2 bg-light">
                                    <div class="flex-grow-1">
                                        {{ $editingEquipment?->department?->name }} · {{ $editingEquipment?->room?->label() }} · Qty {{ $editingEquipment?->quantity }}
                                        @if($editingEquipment?->assetRegistry?->pendingTransfer)<span class="badge bg-warning text-dark ms-1">Transfer pending</span>@endif
                                    </div>
                                    @unless($editingEquipment?->assetRegistry?->pendingTransfer)
                                        <button type="button" class="btn btn-sm btn-outline-primary" wire:click="requestTransfer('{{ $editingId }}')"><i class="fa-solid fa-right-left me-1"></i>Request transfer</button>
                                    @endunless
                                </div>
                                <div class="form-text">Department, room and quantity change only through an approved asset transfer.</div>
                            </div>
                        @endif
                        <div class="col-6 col-md-2">
                            <label class="form-label">Condition <span class="text-danger">*</span></label>
                            <select class="form-select" wire:model="form.condition_received">
                                <option value="new">New</option>
                                <option value="old">Old</option>
                            </select>
                        </div>

                        <div class="col-md-4">
                            <label class="form-label">Installed / put into service <span class="text-danger">*</span></label>
                            <input type="date" class="form-control @error('form.installation_date') is-invalid @enderror" wire:model="form.installation_date">
                            @error('form.installation_date') <div class="invalid-feedback">{{ $message }}</div> @enderror
                        </div>
                        <div class="col-md-4">
                            <label class="form-label">Expected life (years)</label>
                            <input type="number" min="1" class="form-control" wire:model="form.expected_life_years">
                            <div class="form-text">Used to predict the replacement date.</div>
                        </div>
                        <div class="col-md-4">
                            <label class="form-label">Service manual location <span class="text-danger">*</span></label>
                            <input type="text" class="form-control @error('form.manual_location') is-invalid @enderror" wire:model="form.manual_location" placeholder="Biomedical office, cabinet 3, file B">
                            @error('form.manual_location') <div class="invalid-feedback">{{ $message }}</div> @enderror
                        </div>

                        <div class="col-md-4">
                            <label class="form-label">Manufacturer <span class="text-danger">*</span></label>
                            <input type="text" class="form-control @error('form.manufacturer') is-invalid @enderror" wire:model="form.manufacturer">
                            @error('form.manufacturer') <div class="invalid-feedback">{{ $message }}</div> @enderror
                        </div>
                        <div class="col-md-4">
                            <label class="form-label">Manufacturer email</label>
                            <input type="email" class="form-control @error('form.manufacturer_email') is-invalid @enderror" wire:model="form.manufacturer_email">
                        </div>
                        <div class="col-md-4">
                            <label class="form-label">Manufacturer phone</label>
                            <input type="text" class="form-control" wire:model="form.manufacturer_phone">
                        </div>
                        <div class="col-md-4">
                            <label class="form-label">Manufacturer postal address</label>
                            <input type="text" class="form-control" wire:model="form.manufacturer_postal">
                        </div>
                        <div class="col-md-4">
                            <label class="form-label">Supplier</label>
                            <input type="text" class="form-control" wire:model="form.vendor">
                        </div>
                        <div class="col-md-4">
                            <label class="form-label">Service contract</label>
                            <div class="input-group">
                                <div class="input-group-text"><input class="form-check-input mt-0" type="checkbox" wire:model.live="form.under_contract" title="Under contract"></div>
                                <input type="date" class="form-control" wire:model="form.contract_expiry" @disabled(! $form['under_contract']) title="Contract expiry">
                            </div>
                        </div>

                        <div class="col-md-4">
                            <label class="form-label">Purchase cost</label>
                            <input type="number" min="0" step="any" class="form-control" wire:model="form.purchase_cost">
                            <div class="form-text">Total for the quantity. Current value is worked out from asset depreciation.</div>
                        </div>
                        <div class="col-md-4">
                            <label class="form-label">Date of inventory count <span class="text-danger">*</span></label>
                            <input type="date" class="form-control @error('form.counted_at') is-invalid @enderror" wire:model="form.counted_at">
                        </div>
                    </div>
                </div>
                <div class="modal-footer">
                    <button type="button" class="btn btn-secondary" wire:click="closeForm">Cancel</button>
                    <button type="submit" class="btn btn-primary"><i class="fa-solid fa-floppy-disk me-1"></i>Save equipment</button>
                </div>
            </form>
        </x-engineering.modal>
    @endif

    {{-- Status change --}}
    @if($statusEquipment)
        <x-engineering.modal :title="'Status of '.$statusEquipment->name" close="closeStatus">
            <form wire:submit="saveStatus">
                <div class="modal-body">
                    @foreach([
                        'working' => 'Running smoothly, no defect or fault of any kind.',
                        'partly_defective' => 'In use, but has one or more defects needing engineering or the supplier.',
                        'out_of_service' => 'Cannot be used on a patient; must go to engineering.',
                    ] as $value => $help)
                        <div class="form-check mb-2">
                            <input class="form-check-input" type="radio" id="status-{{ $value }}" value="{{ $value }}" wire:model.live="newStatus">
                            <label class="form-check-label" for="status-{{ $value }}">
                                <span class="badge {{ Lookups::EQUIPMENT_STATUS_BADGES[$value] }}">{{ Lookups::EQUIPMENT_STATUSES[$value] }}</span>
                                <span class="small text-muted d-block">{{ $help }}</span>
                            </label>
                        </div>
                    @endforeach
                    <label class="form-label mt-2">Note @if($newStatus !== 'working')<span class="text-danger">*</span>@endif</label>
                    <textarea class="form-control" rows="2" wire:model="statusNote" placeholder="Describe the defect"></textarea>
                    @if($newStatus !== 'working')
                        <div class="form-text">After saving, <a href="{{ route('engineering.request') }}">raise a requisition</a> so engineering attends to it.</div>
                    @endif
                </div>
                <div class="modal-footer">
                    <button type="button" class="btn btn-secondary" wire:click="closeStatus">Cancel</button>
                    <button type="submit" class="btn btn-primary">Save status</button>
                </div>
            </form>
        </x-engineering.modal>
    @endif

    {{-- Removal --}}
    @if($removeEquipment)
        <x-engineering.modal :title="'Remove '.$removeEquipment->name.' from the inventory'" close="closeRemove" header="bg-danger text-white">
            <form wire:submit="remove">
                <div class="modal-body">
                    <p class="text-muted">The record and its history are kept and move to the <em>Removed</em> view. Removal is blocked while the equipment has an open requisition.</p>
                    <div class="mb-3">
                        <label class="form-label">Reason <span class="text-danger">*</span></label>
                        <input type="text" class="form-control @error('removalReason') is-invalid @enderror" wire:model="removalReason" placeholder="Beyond repair, disposed, transferred, lost…">
                        @error('removalReason') <div class="invalid-feedback">{{ $message }}</div> @enderror
                    </div>
                    <div>
                        <label class="form-label">Approved by (responsible authority) <span class="text-danger">*</span></label>
                        <input type="text" class="form-control @error('removalApprovedBy') is-invalid @enderror" wire:model="removalApprovedBy" placeholder="Name and role">
                        @error('removalApprovedBy') <div class="invalid-feedback">{{ $message }}</div> @enderror
                    </div>
                </div>
                <div class="modal-footer">
                    <button type="button" class="btn btn-secondary" wire:click="closeRemove">Cancel</button>
                    <button type="submit" class="btn btn-danger">Remove from inventory</button>
                </div>
            </form>
        </x-engineering.modal>
    @endif

    <livewire:assets.transfer-request />
</x-engineering.page>
