<div>
    {{-- Breadcrumb --}}
    <div class="mb-3">
        <nav aria-label="breadcrumb">
            <ol class="breadcrumb">
                <li class="breadcrumb-item"><a href="/" class="text-decoration-none"><i class="fa-solid fa-home"></i> Dashboard</a></li>
                <li class="breadcrumb-item"><a href="{{ route('chop.settings') }}" class="text-decoration-none">CHOP Settings</a></li>
                <li class="breadcrumb-item active" aria-current="page">Activities Management</li>
            </ol>
        </nav>
    </div>

    {{-- Header --}}
    <div class="mb-4">
        <div class="d-flex justify-content-between align-items-center">
            <div>
                <h5 class="mb-1"><i class="fa-solid fa-list-check text-primary"></i> CHOP Activities Management</h5>
                <p class="text-muted small mb-0">Plan and manage comprehensive operational activities</p>
            </div>
            <button wire:click="openModal('create')" class="btn btn-primary btn-lg shadow-sm">
                <i class="fa-solid fa-plus-circle"></i> New Activity
            </button>
        </div>
    </div>

    {{-- Search --}}
    <div class="card border-0 shadow-sm mb-4">
        <div class="card-body py-3">
            <div class="input-group">
                <span class="input-group-text bg-white border-end-0">
                    <i class="fa-solid fa-search text-muted"></i>
                </span>
                <input class="form-control border-start-0" type="search" wire:model.live="search"
                       placeholder="Search by activity name or description..." />
            </div>
        </div>
    </div>

    {{-- Success Message --}}
    @if(session()->has('success'))
        <div class="alert alert-success alert-dismissible fade show shadow-sm mb-4" role="alert">
            <i class="fa-solid fa-circle-check"></i> {{ session('success') }}
            <button type="button" class="btn-close" data-bs-dismiss="alert"></button>
        </div>
    @endif

    {{-- Activities Table --}}
    <div class="card border-0 shadow-sm">
        <div class="card-header bg-white py-3">
            <h6 class="mb-0"><i class="fa-solid fa-table-list"></i> All Activities</h6>
        </div>
        <div class="card-body p-0">
            <div class="table-responsive">
                <table class="table table-hover mb-0 align-middle">
                    <thead class="table-light">
                        <tr>
                            <th class="text-center" style="width: 50px;">#</th>
                            <th>Activity Details</th>
                            <th class="text-center">Category</th>
                            <th class="text-center">Source of Funds</th>
                            <th class="text-center">Financial Year</th>
                            <th class="text-end">Planned Amount</th>
                            <th class="text-center">Status</th>
                            <th class="text-center">Resources</th>
                            <th class="text-center" style="width: 120px;">Actions</th>
                        </tr>
                    </thead>
                    <tbody>
                        @php $number = 1; @endphp
                        @forelse($activities as $activity)
                        <tr>
                            <td class="text-center text-muted">{{ $number++ }}</td>
                            <td>
                                <div class="d-flex align-items-start">
                                    <div class="flex-grow-1">
                                        <h6 class="mb-1">{{ $activity->planned_activity }}</h6>
                                        @if($activity->description)
                                            <p class="text-muted small mb-0">{{ Str::limit($activity->description, 60) }}</p>
                                        @endif
                                        @if($activity->is_approved)
                                            <span class="badge bg-success-subtle text-success mt-1">
                                                <i class="fa-solid fa-check-circle"></i> Approved
                                            </span>
                                        @endif
                                    </div>
                                </div>
                            </td>
                            <td class="text-center">
                                @if($activity->category)
                                    <span class="badge bg-primary-subtle text-primary px-3 py-2">
                                        {{ $activity->category->name }}
                                    </span>
                                @else
                                    <span class="text-muted">-</span>
                                @endif
                            </td>
                            <td class="text-center">
                                @if($activity->source)
                                    <span class="badge px-3 py-2" style="background-color: {{ $activity->source->colorcode ?? '#6c757d' }}; color: white;">
                                        {{ $activity->source->name }}
                                    </span>
                                @else
                                    <span class="text-muted">-</span>
                                @endif
                            </td>
                            <td class="text-center">
                                @if($activity->financialYear)
                                    <span class="badge bg-info-subtle text-info px-3 py-2">
                                        {{ $activity->financialYear->name }}
                                    </span>
                                @else
                                    <span class="text-muted">-</span>
                                @endif
                            </td>
                            <td class="text-end">
                                <strong class="text-success fs-6">{{ number_format($activity->planned_amount, 2) }}</strong>
                            </td>
                            <td class="text-center">
                                @php
                                    $statusConfig = [
                                        'pending' => ['icon' => 'clock', 'color' => 'warning'],
                                        'in_progress' => ['icon' => 'spinner', 'color' => 'info'],
                                        'completed' => ['icon' => 'check-circle', 'color' => 'success'],
                                        'cancelled' => ['icon' => 'times-circle', 'color' => 'danger']
                                    ];
                                    $config = $statusConfig[$activity->status] ?? ['icon' => 'question', 'color' => 'secondary'];
                                @endphp
                                <span class="badge bg-{{ $config['color'] }}-subtle text-{{ $config['color'] }} px-3 py-2">
                                    <i class="fa-solid fa-{{ $config['icon'] }}"></i> {{ ucfirst(str_replace('_', ' ', $activity->status)) }}
                                </span>
                            </td>
                            <td class="text-center">
                                <div class="d-flex gap-1 justify-content-center">
                                    <span class="badge bg-secondary-subtle text-secondary" title="Items">
                                        <i class="fa-solid fa-box"></i> {{ $activity->items->count() }}
                                    </span>
                                    <span class="badge bg-secondary-subtle text-secondary" title="Personnel">
                                        <i class="fa-solid fa-users"></i> {{ $activity->personels->count() }}
                                    </span>
                                </div>
                            </td>
                            <td class="text-center">
                                <div class="btn-group btn-group-sm" role="group">
                                    <button class="btn btn-outline-primary" wire:click="openModal('edit', '{{ $activity->id }}')"
                                            title="Edit Activity">
                                        <i class="fa-solid fa-pencil"></i>
                                    </button>
                                    <button class="btn btn-outline-danger" wire:click="delete('{{ $activity->id }}')"
                                            onclick="return confirm('Delete this activity and all its items/personnel?')"
                                            title="Delete Activity">
                                        <i class="fa-solid fa-trash"></i>
                                    </button>
                                </div>
                            </td>
                        </tr>
                        @empty
                        <tr>
                            <td colspan="9" class="text-center py-5">
                                <div class="text-muted">
                                    <i class="fa-solid fa-folder-open fa-4x mb-3 opacity-25"></i>
                                    <h5 class="text-muted">No activities found</h5>
                                    <p class="mb-0">Start by creating your first activity</p>
                                </div>
                            </td>
                        </tr>
                        @endforelse
                    </tbody>
                </table>
            </div>
        </div>

        @if($activities->count() > 0)
            <div class="card-footer bg-white border-top">
                {{ $activities->links() }}
            </div>
        @endif
    </div>

    {{-- Modal --}}
    @if($showModal)
    <div class="modal fade show d-block" style="background: rgba(0,0,0,0.7);" tabindex="-1">
        <div class="modal-dialog modal-xl modal-dialog-scrollable">
            <div class="modal-content border-0 shadow-lg">
                <div class="modal-header bg-gradient bg-primary text-white">
                    <h5 class="modal-title">
                        <i class="fa-solid fa-{{ $modalMode === 'edit' ? 'pencil' : 'plus-circle' }}"></i>
                        {{ $modalMode === 'edit' ? 'Edit Activity' : 'Create New Activity' }}
                    </h5>
                    <button type="button" class="btn-close btn-close-white" wire:click="$set('showModal', false)"></button>
                </div>

                <form wire:submit.prevent="save">
                    <div class="modal-body" style="max-height: 70vh; overflow-y: auto;">
                        {{-- Info Alert --}}
                        <div class="alert alert-info border-0 shadow-sm mb-4">
                            <i class="fa-solid fa-info-circle"></i>
                            Fields marked with <span class="text-danger fw-bold">*</span> are required
                        </div>

                        {{-- Basic Information Card --}}
                        <div class="card border-0 shadow-sm mb-4">
                            <div class="card-header bg-primary bg-opacity-10 border-0">
                                <h6 class="mb-0 text-primary">
                                    <i class="fa-solid fa-file-lines"></i> Basic Information <span class="text-danger">*</span>
                                </h6>
                            </div>
                            <div class="card-body">
                                <div class="row g-3">
                                    <div class="col-md-6">
                                        <label class="form-label fw-semibold">Planned Activity <span class="text-danger">*</span></label>
                                        <input type="text" class="form-control @error('planned_activity') is-invalid @enderror"
                                               wire:model="planned_activity" placeholder="Enter activity name" required>
                                        @error('planned_activity') <div class="invalid-feedback">{{ $message }}</div> @enderror
                                    </div>
                                    <div class="col-md-3">
                                        <label class="form-label fw-semibold">Planned Amount <span class="text-danger">*</span></label>
                                        <input type="number" step="0.01" min="0" class="form-control @error('planned_amount') is-invalid @enderror"
                                               wire:model="planned_amount" placeholder="0.00" required>
                                        @error('planned_amount') <div class="invalid-feedback">{{ $message }}</div> @enderror
                                    </div>
                                    <div class="col-md-3">
                                        <label class="form-label fw-semibold">Status <span class="text-danger">*</span></label>
                                        <select class="form-select @error('status') is-invalid @enderror" wire:model="status" required>
                                            <option value="pending">⏱️ Pending</option>
                                            <option value="in_progress">🔄 In Progress</option>
                                            <option value="completed">✅ Completed</option>
                                            <option value="cancelled">❌ Cancelled</option>
                                        </select>
                                        @error('status') <div class="invalid-feedback">{{ $message }}</div> @enderror
                                    </div>

                                    <div class="col-md-4">
                                        <label class="form-label fw-semibold">Source of Funds <span class="text-danger">*</span></label>
                                        <select class="form-select searchable-select @error('source_id') is-invalid @enderror"
                                                wire:model="source_id" required data-placeholder="Search and select source...">
                                            <option value="">Select Source</option>
                                            @foreach($sources as $source)
                                                <option value="{{ $source->id }}">{{ $source->name }}</option>
                                            @endforeach
                                        </select>
                                        @error('source_id') <div class="invalid-feedback">{{ $message }}</div> @enderror
                                    </div>
                                    <div class="col-md-4">
                                        <label class="form-label fw-semibold">Category <span class="text-danger">*</span></label>
                                        <select class="form-select searchable-select @error('category_id') is-invalid @enderror"
                                                wire:model="category_id" required data-placeholder="Search and select category...">
                                            <option value="">Select Category</option>
                                            @foreach($categories as $category)
                                                <option value="{{ $category->id }}">{{ $category->name }}</option>
                                            @endforeach
                                        </select>
                                        @error('category_id') <div class="invalid-feedback">{{ $message }}</div> @enderror
                                    </div>
                                    <div class="col-md-4">
                                        <label class="form-label fw-semibold">Financial Year <span class="text-danger">*</span></label>
                                        <select class="form-select @error('financial_year_id') is-invalid @enderror" wire:model="financial_year_id" required>
                                            <option value="">Select FY</option>
                                            @foreach($financialYears as $fy)
                                                <option value="{{ $fy->id }}">{{ $fy->name }}</option>
                                            @endforeach
                                        </select>
                                        @error('financial_year_id') <div class="invalid-feedback">{{ $message }}</div> @enderror
                                    </div>

                                    <div class="col-12">
                                        <label class="form-label fw-semibold">Description</label>
                                        <textarea class="form-control" wire:model="description" rows="3"
                                                  placeholder="Provide detailed description of the activity..."></textarea>
                                    </div>
                                </div>
                            </div>
                        </div>

                        {{-- Activity Items Card --}}
                        <div class="card border-0 shadow-sm mb-4">
                            <div class="card-header bg-success bg-opacity-10 border-0">
                                <div class="d-flex justify-content-between align-items-center">
                                    <h6 class="mb-0 text-success">
                                        <i class="fa-solid fa-box"></i> Activity Items & Budget
                                    </h6>
                                    <button type="button" class="btn btn-sm btn-success shadow-sm" wire:click="$toggle('showItemSelector')">
                                        <i class="fa-solid fa-{{ $showItemSelector ? 'times' : 'plus' }}"></i>
                                        {{ $showItemSelector ? 'Close' : 'Add Item' }}
                                    </button>
                                </div>
                            </div>
                            <div class="card-body">
                                {{-- Item Selector --}}
                                @if($showItemSelector)
                                    <div class="card bg-light border-success mb-3">
                                        <div class="card-body">
                                            <div class="input-group mb-2">
                                                <span class="input-group-text bg-white">
                                                    <i class="fa-solid fa-search"></i>
                                                </span>
                                                <input type="text" class="form-control" wire:model.live="itemSearch"
                                                       placeholder="Search items by name...">
                                            </div>
                                            <div class="border rounded p-2" style="max-height: 250px; overflow-y: auto; background: white;">
                                                @forelse($availableItems as $item)
                                                    <div class="d-flex justify-content-between align-items-center p-2 border-bottom hover-bg-light">
                                                        <div>
                                                            <i class="fa-solid fa-cube text-muted"></i>
                                                            <strong>{{ $item->name }}</strong>
                                                        </div>
                                                        <button type="button" class="btn btn-sm btn-success"
                                                                wire:click="addItem('{{ $item->id }}', '{{ $item->name }}')">
                                                            <i class="fa-solid fa-plus"></i> Add
                                                        </button>
                                                    </div>
                                                @empty
                                                    <p class="text-muted text-center py-3 mb-0">No items found</p>
                                                @endforelse
                                            </div>
                                        </div>
                                    </div>
                                @endif

                                {{-- Selected Items --}}
                                @if(count($selectedItems) > 0)
                                    <div class="table-responsive">
                                        <table class="table table-sm table-bordered">
                                            <thead class="table-success">
                                                <tr>
                                                    <th>Item Name</th>
                                                    <th style="width: 120px;">Quantity</th>
                                                    <th style="width: 150px;">Unit Price</th>
                                                    <th style="width: 150px;">Total</th>
                                                    <th style="width: 80px;">Action</th>
                                                </tr>
                                            </thead>
                                            <tbody>
                                                @foreach($selectedItems as $index => $item)
                                                    <tr>
                                                        <td class="align-middle">
                                                            <i class="fa-solid fa-cube text-success"></i> {{ $item['name'] }}
                                                        </td>
                                                        <td>
                                                            <input type="number" class="form-control form-control-sm"
                                                                   wire:model.live="selectedItems.{{ $index }}.quantity"
                                                                   min="1">
                                                        </td>
                                                        <td>
                                                            <input type="number" class="form-control form-control-sm"
                                                                   wire:model.live="selectedItems.{{ $index }}.price"
                                                                   step="0.01" min="0">
                                                        </td>
                                                        <td class="align-middle">
                                                            <strong class="text-success">
                                                                {{ number_format(($item['quantity'] ?? 0) * ($item['price'] ?? 0), 2) }}
                                                            </strong>
                                                        </td>
                                                        <td class="text-center">
                                                            <button type="button" class="btn btn-sm btn-danger"
                                                                    wire:click="removeItem({{ $index }})" title="Remove">
                                                                <i class="fa-solid fa-trash"></i>
                                                            </button>
                                                        </td>
                                                    </tr>
                                                @endforeach
                                            </tbody>
                                            <tfoot class="table-success">
                                                <tr>
                                                    <td colspan="3" class="text-end fw-bold">TOTAL ACTUAL COST:</td>
                                                    <td colspan="2" class="fw-bold text-info fs-5">
                                                        {{ number_format($actual_amount ?? 0, 2) }} TZS
                                                    </td>
                                                </tr>
                                                <tr class="table-light">
                                                    <td colspan="3" class="text-end">Planned Amount:</td>
                                                    <td colspan="2" class="text-success">
                                                        {{ number_format($planned_amount ?? 0, 2) }} TZS
                                                    </td>
                                                </tr>
                                                <tr class="table-light">
                                                    <td colspan="3" class="text-end">Variance:</td>
                                                    <td colspan="2" class="fw-bold @if(($planned_amount ?? 0) - ($actual_amount ?? 0) >= 0) text-success @else text-danger @endif">
                                                        {{ number_format(($planned_amount ?? 0) - ($actual_amount ?? 0), 2) }} TZS
                                                        @if(($planned_amount ?? 0) - ($actual_amount ?? 0) >= 0)
                                                            <small>(Under budget)</small>
                                                        @else
                                                            <small>(Over budget)</small>
                                                        @endif
                                                    </td>
                                                </tr>
                                            </tfoot>
                                        </table>
                                    </div>
                                @else
                                    <div class="text-center py-4 bg-light rounded">
                                        <i class="fa-solid fa-box-open fa-3x text-muted opacity-25 mb-2"></i>
                                        <p class="text-muted mb-0">No items added yet. Click "Add Item" to start.</p>
                                    </div>
                                @endif
                            </div>
                        </div>

                        {{-- Personnel Card --}}
                        <div class="card border-0 shadow-sm mb-4">
                            <div class="card-header bg-info bg-opacity-10 border-0">
                                <div class="d-flex justify-content-between align-items-center">
                                    <h6 class="mb-0 text-info">
                                        <i class="fa-solid fa-users"></i> Responsible Personnel
                                    </h6>
                                    <button type="button" class="btn btn-sm btn-info shadow-sm" wire:click="$toggle('showPersonnelSelector')">
                                        <i class="fa-solid fa-{{ $showPersonnelSelector ? 'times' : 'user-plus' }}"></i>
                                        {{ $showPersonnelSelector ? 'Close' : 'Assign Personnel' }}
                                    </button>
                                </div>
                            </div>
                            <div class="card-body">
                                @if($showPersonnelSelector)
                                    <div class="card bg-light border-info mb-3">
                                        <div class="card-body">
                                            <p class="text-muted small mb-2">Select job titles for personnel responsible for this activity:</p>
                                            <div class="row g-2">
                                                @foreach($jobTitles as $title)
                                                    <div class="col-md-4 col-sm-6">
                                                        <div class="form-check p-2 border rounded hover-bg-white">
                                                            <input type="checkbox" class="form-check-input" id="title_{{ $title->id }}"
                                                                   wire:click="togglePersonnel('{{ $title->id }}')"
                                                                   @if(in_array($title->id, $selectedPersonnel)) checked @endif>
                                                            <label class="form-check-label" for="title_{{ $title->id }}">
                                                                {{ $title->name }}
                                                            </label>
                                                        </div>
                                                    </div>
                                                @endforeach
                                            </div>
                                        </div>
                                    </div>
                                @endif

                                @if(count($selectedPersonnel) > 0)
                                    <div class="d-flex flex-wrap gap-2">
                                        @foreach($selectedPersonnel as $titleId)
                                            @php $title = $jobTitles->firstWhere('id', $titleId); @endphp
                                            @if($title)
                                                <span class="badge bg-info px-3 py-2 fs-6">
                                                    <i class="fa-solid fa-user"></i> {{ $title->name }}
                                                </span>
                                            @endif
                                        @endforeach
                                    </div>
                                @else
                                    <div class="text-center py-4 bg-light rounded">
                                        <i class="fa-solid fa-users-slash fa-3x text-muted opacity-25 mb-2"></i>
                                        <p class="text-muted mb-0">No personnel assigned. Click "Assign Personnel" to add.</p>
                                    </div>
                                @endif
                            </div>
                        </div>

                        {{-- Additional Information Card --}}
                        <div class="card border-0 shadow-sm">
                            <div class="card-header bg-secondary bg-opacity-10 border-0">
                                <h6 class="mb-0 text-secondary">
                                    <i class="fa-solid fa-circle-info"></i> Additional Information (Optional)
                                </h6>
                            </div>
                            <div class="card-body">
                                <div class="row g-3">
                                    <div class="col-md-6">
                                        <label class="form-label fw-semibold">Expected Outcome</label>
                                        <textarea class="form-control" wire:model="expected_outcome" rows="3"
                                                  placeholder="Describe the expected outcome..."></textarea>
                                    </div>
                                    <div class="col-md-6">
                                        <label class="form-label fw-semibold">Expected Outcome Date</label>
                                        <input type="date" class="form-control" wire:model="expected_outcome_date">

                                        <label class="form-label fw-semibold mt-3">Activity Type</label>
                                        <select class="form-select" wire:model="activity_type">
                                            <option value="">Select Type</option>
                                            @foreach($activityTypes as $key => $type)
                                                <option value="{{ $key }}">{{ $type }}</option>
                                            @endforeach
                                        </select>

                                        <label class="form-label fw-semibold mt-3">Monitoring Frequency</label>
                                        <select class="form-select" wire:model="frequence_monitoring">
                                            <option value="">Select Frequency</option>
                                            @foreach($frequencyOptions as $key => $frequency)
                                                <option value="{{ $key }}">{{ $frequency }}</option>
                                            @endforeach
                                        </select>
                                    </div>

                                    <div class="col-12">
                                        <div class="card bg-light border-0">
                                            <div class="card-body">
                                                <p class="text-muted small mb-2">Activity Flags:</p>
                                                <div class="row g-3">
                                                    <div class="col-md-4">
                                                        <div class="form-check form-switch">
                                                            <input type="checkbox" class="form-check-input" wire:model="is_active" id="is_active">
                                                            <label class="form-check-label fw-semibold" for="is_active">
                                                                <i class="fa-solid fa-power-off text-success"></i> Active
                                                            </label>
                                                        </div>
                                                    </div>
                                                    <div class="col-md-4">
                                                        <div class="form-check form-switch">
                                                            <input type="checkbox" class="form-check-input" wire:model="is_planned" id="is_planned">
                                                            <label class="form-check-label fw-semibold" for="is_planned">
                                                                <i class="fa-solid fa-calendar-check text-primary"></i> Planned
                                                            </label>
                                                        </div>
                                                    </div>
                                                    <div class="col-md-4">
                                                        <div class="form-check form-switch">
                                                            <input type="checkbox" class="form-check-input" wire:model="is_approved" id="is_approved">
                                                            <label class="form-check-label fw-semibold" for="is_approved">
                                                                <i class="fa-solid fa-check-double text-success"></i> Approved
                                                            </label>
                                                        </div>
                                                    </div>
                                                </div>
                                            </div>
                                        </div>
                                    </div>
                                </div>
                            </div>
                        </div>
                    </div>

                    <div class="modal-footer bg-light">
                        <button type="button" class="btn btn-secondary" wire:click="$set('showModal', false)">
                            <i class="fa-solid fa-times"></i> Cancel
                        </button>
                        <button type="submit" class="btn btn-primary btn-lg shadow-sm">
                            <i class="fa-solid fa-save"></i> {{ $modalMode === 'edit' ? 'Update Activity' : 'Save Activity' }}
                        </button>
                    </div>
                </form>
            </div>
        </div>
    </div>
    @endif

    <style>
        .hover-bg-light:hover {
            background-color: #f8f9fa !important;
            cursor: pointer;
        }
        .hover-bg-white:hover {
            background-color: white !important;
        }
        .modal-dialog-scrollable .modal-body {
            scrollbar-width: thin;
            scrollbar-color: #cbd5e0 #f1f5f9;
        }
        .modal-dialog-scrollable .modal-body::-webkit-scrollbar {
            width: 8px;
        }
        .modal-dialog-scrollable .modal-body::-webkit-scrollbar-track {
            background: #f1f5f9;
            border-radius: 10px;
        }
        .modal-dialog-scrollable .modal-body::-webkit-scrollbar-thumb {
            background: #cbd5e0;
            border-radius: 10px;
        }
        .modal-dialog-scrollable .modal-body::-webkit-scrollbar-thumb:hover {
            background: #94a3b8;
        }

        /* Searchable Select Styles */
        .select-search-wrapper {
            position: relative;
        }
        .select-search-input {
            width: 100%;
            padding: 0.375rem 0.75rem;
            border: 1px solid #dee2e6;
            border-radius: 0.375rem;
            margin-bottom: 0.5rem;
        }
        .select-options-list {
            max-height: 200px;
            overflow-y: auto;
            border: 1px solid #dee2e6;
            border-radius: 0.375rem;
            background: white;
        }
        .select-option-item {
            padding: 0.5rem 0.75rem;
            cursor: pointer;
            border-bottom: 1px solid #f1f5f9;
        }
        .select-option-item:hover {
            background-color: #f8f9fa;
        }
        .select-option-item.selected {
            background-color: #0d6efd;
            color: white;
        }
    </style>

    @script
    <script>
        let choicesInstances = [];

        // Initialize Choices.js on searchable selects
        function initSearchableSelects() {
            // Destroy existing instances first
            choicesInstances.forEach(instance => {
                if (instance && typeof instance.destroy === 'function') {
                    instance.destroy();
                }
            });
            choicesInstances = [];

            const searchableSelects = document.querySelectorAll('.searchable-select');

            searchableSelects.forEach(select => {
                if (select.dataset.choicesInit) return;

                const choices = new Choices(select, {
                    searchEnabled: true,
                    searchPlaceholderValue: 'Type to search...',
                    itemSelectText: 'Click to select',
                    noResultsText: 'No results found',
                    shouldSort: false,
                    removeItemButton: false,
                    placeholder: true,
                    placeholderValue: select.dataset.placeholder || 'Select an option',
                });

                select.dataset.choicesInit = 'true';
                choicesInstances.push(choices);

                // Listen for changes and dispatch Livewire event
                select.addEventListener('change', function() {
                    this.dispatchEvent(new Event('input', { bubbles: true }));
                });
            });
        }

        // Initialize when modal opens
        document.addEventListener('livewire:init', () => {
            Livewire.hook('morph.updated', ({ el, component }) => {
                setTimeout(() => initSearchableSelects(), 100);
            });
        });

        // Initialize on page load
        window.addEventListener('load', function() {
            setTimeout(() => initSearchableSelects(), 100);
        });

        // Reinitialize when Livewire navigates
        document.addEventListener('livewire:navigated', () => {
            setTimeout(() => initSearchableSelects(), 100);
        });
    </script>
    @endscript
</div>
