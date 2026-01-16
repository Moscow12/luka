<div class="custom-container">

    <x-pages.breadcrumn title="EDIT ROSTERS" :breadcrumbs="[
        ['label' => 'Home', 'url' => route('dashboard')],
        ['label' => 'HR', 'url' => '#'],
        ['label' => 'Edit Rosters', 'url' => '#'],
    ]">
    </x-pages.breadcrumn>

    {{-- Flash Messages --}}
    @if (session()->has('success'))
        <div class="alert alert-success alert-dismissible fade show" role="alert">
            <i class="fa-solid fa-circle-check me-2"></i>
            {{ session('success') }}
            <button type="button" class="btn-close" data-bs-dismiss="alert" aria-label="Close"></button>
        </div>
    @endif

    @if (session()->has('error'))
        <div class="alert alert-danger alert-dismissible fade show" role="alert">
            <i class="fa-solid fa-circle-exclamation me-2"></i>
            {{ session('error') }}
            <button type="button" class="btn-close" data-bs-dismiss="alert" aria-label="Close"></button>
        </div>
    @endif

    {{-- Statistics Cards --}}
    <div class="row g-3 mb-4">
        <div class="col-6 col-md-4 col-xl">
            <div class="card border-0 shadow-sm h-100">
                <div class="card-body">
                    <div class="d-flex align-items-center">
                        <div class="flex-shrink-0">
                            <div class="avatar avatar-lg bg-primary-subtle text-primary rounded">
                                <i class="fa-solid fa-calendar-days fs-4"></i>
                            </div>
                        </div>
                        <div class="flex-grow-1 ms-3">
                            <p class="text-muted mb-1 small">Total Rosters</p>
                            <h4 class="mb-0 fw-bold text-primary">{{ number_format($statistics['total']) }}</h4>
                        </div>
                    </div>
                </div>
            </div>
        </div>

        <div class="col-6 col-md-4 col-xl">
            <div class="card border-0 shadow-sm h-100">
                <div class="card-body">
                    <div class="d-flex align-items-center">
                        <div class="flex-shrink-0">
                            <div class="avatar avatar-lg bg-info-subtle text-info rounded">
                                <i class="fa-solid fa-clock fs-4"></i>
                            </div>
                        </div>
                        <div class="flex-grow-1 ms-3">
                            <p class="text-muted mb-1 small">Scheduled</p>
                            <h4 class="mb-0 fw-bold text-info">{{ number_format($statistics['scheduled']) }}</h4>
                        </div>
                    </div>
                </div>
            </div>
        </div>

        <div class="col-6 col-md-4 col-xl">
            <div class="card border-0 shadow-sm h-100">
                <div class="card-body">
                    <div class="d-flex align-items-center">
                        <div class="flex-shrink-0">
                            <div class="avatar avatar-lg bg-success-subtle text-success rounded">
                                <i class="fa-solid fa-circle-check fs-4"></i>
                            </div>
                        </div>
                        <div class="flex-grow-1 ms-3">
                            <p class="text-muted mb-1 small">Completed</p>
                            <h4 class="mb-0 fw-bold text-success">{{ number_format($statistics['completed']) }}</h4>
                        </div>
                    </div>
                </div>
            </div>
        </div>

        <div class="col-6 col-md-4 col-xl">
            <div class="card border-0 shadow-sm h-100">
                <div class="card-body">
                    <div class="d-flex align-items-center">
                        <div class="flex-shrink-0">
                            <div class="avatar avatar-lg bg-danger-subtle text-danger rounded">
                                <i class="fa-solid fa-ban fs-4"></i>
                            </div>
                        </div>
                        <div class="flex-grow-1 ms-3">
                            <p class="text-muted mb-1 small">Cancelled</p>
                            <h4 class="mb-0 fw-bold text-danger">{{ number_format($statistics['cancelled']) }}</h4>
                        </div>
                    </div>
                </div>
            </div>
        </div>

        <div class="col-6 col-md-4 col-xl">
            <div class="card border-0 shadow-sm h-100">
                <div class="card-body">
                    <div class="d-flex align-items-center">
                        <div class="flex-shrink-0">
                            <div class="avatar avatar-lg bg-warning-subtle text-warning rounded">
                                <i class="fa-solid fa-user-xmark fs-4"></i>
                            </div>
                        </div>
                        <div class="flex-grow-1 ms-3">
                            <p class="text-muted mb-1 small">No Show</p>
                            <h4 class="mb-0 fw-bold text-warning">{{ number_format($statistics['no_show']) }}</h4>
                        </div>
                    </div>
                </div>
            </div>
        </div>
    </div>

    {{-- Main Card --}}
    <div class="card card-lg border-0 shadow-sm">
        {{-- Filter Section --}}
        <div class="card-header border-bottom bg-white">
            <div class="row g-3">
                {{-- Search --}}
                <div class="col-12 col-md-4 col-lg-3">
                    <label class="form-label small text-muted">Search Employee</label>
                    <div class="input-group">
                        <span class="input-group-text bg-white">
                            <i class="fa-solid fa-magnifying-glass"></i>
                        </span>
                        <input type="search" wire:model.live.debounce.300ms="search" class="form-control"
                            placeholder="Name or Employee No..." />
                    </div>
                </div>

                {{-- Department Filter --}}
                <div class="col-6 col-md-4 col-lg-2">
                    <label class="form-label small text-muted">Department</label>
                    <select wire:model.live="filterDepartment" class="form-select">
                        <option value="">All Departments</option>
                        @foreach($departments as $dept)
                            <option value="{{ $dept->id }}">{{ $dept->name }}</option>
                        @endforeach
                    </select>
                </div>

                {{-- Employee Filter --}}
                <div class="col-6 col-md-4 col-lg-2">
                    <label class="form-label small text-muted">Employee</label>
                    <select wire:model.live="filterEmployee" class="form-select">
                        <option value="">All Employees</option>
                        @foreach($employees as $emp)
                            <option value="{{ $emp->id }}">{{ $emp->first_name }} {{ $emp->last_name }}</option>
                        @endforeach
                    </select>
                </div>

                {{-- Shift Filter --}}
                <div class="col-6 col-md-4 col-lg-2">
                    <label class="form-label small text-muted">Shift</label>
                    <select wire:model.live="filterShift" class="form-select">
                        <option value="">All Shifts</option>
                        @foreach($shifts as $shift)
                            <option value="{{ $shift->id }}">{{ $shift->name }}</option>
                        @endforeach
                    </select>
                </div>

                {{-- Status Filter --}}
                <div class="col-6 col-md-4 col-lg-2">
                    <label class="form-label small text-muted">Status</label>
                    <select wire:model.live="filterStatus" class="form-select">
                        <option value="">All Status</option>
                        <option value="scheduled">Scheduled</option>
                        <option value="completed">Completed</option>
                        <option value="cancelled">Cancelled</option>
                        <option value="no_show">No Show</option>
                    </select>
                </div>

                {{-- Date From --}}
                <div class="col-6 col-md-4 col-lg-2">
                    <label class="form-label small text-muted">Date From</label>
                    <input type="date" wire:model.live="filterDateFrom" class="form-control" />
                </div>

                {{-- Date To --}}
                <div class="col-6 col-md-4 col-lg-2">
                    <label class="form-label small text-muted">Date To</label>
                    <input type="date" wire:model.live="filterDateTo" class="form-control" />
                </div>

                {{-- Reset Filters --}}
                <div class="col-6 col-md-4 col-lg-2 d-flex align-items-end">
                    <button wire:click="resetFilters" class="btn btn-outline-secondary w-100">
                        <i class="fa-solid fa-rotate-left me-1"></i> Reset
                    </button>
                </div>
            </div>

            {{-- Bulk Actions --}}
            @if(count($selectedRosters) > 0)
                <div class="mt-3 pt-3 border-top">
                    <div class="d-flex flex-wrap align-items-center gap-2">
                        <span class="badge bg-primary fs-6">{{ count($selectedRosters) }} selected</span>
                        <button wire:click="openBulkEditModal" class="btn btn-sm btn-outline-primary">
                            <i class="fa-solid fa-pen-to-square me-1"></i> Bulk Edit
                        </button>
                        <button wire:click="bulkDelete" wire:confirm="Are you sure you want to delete {{ count($selectedRosters) }} roster(s)?" class="btn btn-sm btn-outline-danger">
                            <i class="fa-solid fa-trash me-1"></i> Delete Selected
                        </button>
                    </div>
                </div>
            @endif
        </div>

        {{-- Table --}}
        <div class="card-body p-0">
            <div class="table-responsive">
                @if($rosters->isEmpty())
                    <div class="text-center py-5">
                        <i class="fa-solid fa-calendar-xmark text-muted mb-3" style="font-size: 48px;"></i>
                        <h5 class="text-muted">No Rosters Found</h5>
                        <p class="text-muted">Try adjusting your filters or date range</p>
                    </div>
                @else
                    <table class="table table-hover mb-0 align-middle">
                        <thead class="table-light">
                            <tr>
                                <th class="ps-3" style="width: 40px;">
                                    <input type="checkbox" wire:model.live="selectAll" class="form-check-input" />
                                </th>
                                <th>Employee</th>
                                <th>Department</th>
                                <th>Date</th>
                                <th>Shift</th>
                                <th>Type</th>
                                <th>Status</th>
                                <th>Added By</th>
                                <th class="text-end pe-3">Actions</th>
                            </tr>
                        </thead>
                        <tbody>
                            @foreach($rosters as $roster)
                                <tr wire:key="roster-{{ $roster->id }}">
                                    <td class="ps-3">
                                        <input type="checkbox" wire:model.live="selectedRosters" value="{{ $roster->id }}" class="form-check-input" />
                                    </td>
                                    <td>
                                        <div class="d-flex align-items-center">
                                            <div class="avatar avatar-sm bg-primary-subtle text-primary rounded-circle me-2">
                                                <span class="avatar-initials small">
                                                    {{ substr($roster->employee->first_name ?? '', 0, 1) }}{{ substr($roster->employee->last_name ?? '', 0, 1) }}
                                                </span>
                                            </div>
                                            <div>
                                                <div class="fw-semibold small">
                                                    {{ $roster->employee->first_name ?? '' }} {{ $roster->employee->last_name ?? '' }}
                                                </div>
                                                <div class="text-muted" style="font-size: 11px;">
                                                    {{ $roster->employee->employee_no ?? 'N/A' }}
                                                </div>
                                            </div>
                                        </div>
                                    </td>
                                    <td>
                                        <span class="badge bg-secondary-subtle text-secondary">
                                            {{ $roster->employee->department->name ?? 'N/A' }}
                                        </span>
                                    </td>
                                    <td>
                                        <div class="fw-semibold">{{ \Carbon\Carbon::parse($roster->roster_date)->format('D, M d') }}</div>
                                        <div class="text-muted small">{{ \Carbon\Carbon::parse($roster->roster_date)->format('Y') }}</div>
                                    </td>
                                    <td>
                                        @if($roster->shift)
                                            <div class="fw-semibold">{{ $roster->shift->name }}</div>
                                            <div class="text-muted small">
                                                {{ \Carbon\Carbon::parse($roster->shift->start_time)->format('H:i') }} -
                                                {{ \Carbon\Carbon::parse($roster->shift->end_time)->format('H:i') }}
                                            </div>
                                        @else
                                            <span class="text-muted">No Shift</span>
                                        @endif
                                    </td>
                                    <td>
                                        @php
                                            $typeClass = match($roster->shift_type) {
                                                'overtime' => 'bg-warning-subtle text-warning',
                                                'special' => 'bg-info-subtle text-info',
                                                default => 'bg-primary-subtle text-primary'
                                            };
                                        @endphp
                                        <span class="badge {{ $typeClass }}">
                                            {{ ucfirst($roster->shift_type ?? 'regular') }}
                                        </span>
                                    </td>
                                    <td>
                                        @php
                                            $statusClass = match($roster->status) {
                                                'completed' => 'bg-success-subtle text-success',
                                                'cancelled' => 'bg-danger-subtle text-danger',
                                                'no_show' => 'bg-warning-subtle text-warning',
                                                default => 'bg-info-subtle text-info'
                                            };
                                        @endphp
                                        <span class="badge {{ $statusClass }}">
                                            {{ ucwords(str_replace('_', ' ', $roster->status ?? 'scheduled')) }}
                                        </span>
                                    </td>
                                    <td>
                                        <div class="small text-muted">
                                            {{ $roster->addedBy->name ?? 'System' }}
                                        </div>
                                    </td>
                                    <td class="text-end pe-3">
                                        <div class="btn-group btn-group-sm">
                                            <button wire:click="openEditModal('{{ $roster->id }}')" class="btn btn-outline-primary" title="Edit">
                                                <i class="fa-solid fa-pen-to-square"></i>
                                            </button>
                                            <button wire:click="openDeleteModal('{{ $roster->id }}')" class="btn btn-outline-danger" title="Delete">
                                                <i class="fa-solid fa-trash"></i>
                                            </button>
                                        </div>
                                    </td>
                                </tr>
                            @endforeach
                        </tbody>
                    </table>
                @endif
            </div>
        </div>

        {{-- Footer with Pagination --}}
        @if($rosters->hasPages())
            <div class="card-footer border-top bg-white">
                <div class="d-flex flex-wrap justify-content-between align-items-center gap-3">
                    <div class="d-flex align-items-center gap-2">
                        <span class="text-muted small">Per page:</span>
                        <select wire:model.live="perPage" class="form-select form-select-sm" style="width: auto;">
                            <option value="10">10</option>
                            <option value="20">20</option>
                            <option value="50">50</option>
                            <option value="100">100</option>
                        </select>
                    </div>
                    {{ $rosters->links() }}
                </div>
            </div>
        @endif
    </div>

    {{-- Edit Modal --}}
    @if($showEditModal)
        <div class="modal fade show d-block" tabindex="-1" style="background-color: rgba(0,0,0,0.5);">
            <div class="modal-dialog modal-dialog-centered">
                <div class="modal-content">
                    <div class="modal-header bg-primary text-white">
                        <h5 class="modal-title">
                            <i class="fa-solid fa-pen-to-square me-2"></i> Edit Roster
                        </h5>
                        <button type="button" class="btn-close btn-close-white" wire:click="closeEditModal"></button>
                    </div>
                    <div class="modal-body">
                        <div class="mb-3">
                            <label class="form-label">Roster Date <span class="text-danger">*</span></label>
                            <input type="date" wire:model="editRosterDate" class="form-control @error('editRosterDate') is-invalid @enderror" />
                            @error('editRosterDate')
                                <div class="invalid-feedback">{{ $message }}</div>
                            @enderror
                        </div>

                        <div class="mb-3">
                            <label class="form-label">Shift <span class="text-danger">*</span></label>
                            <select wire:model="editShiftId" class="form-select @error('editShiftId') is-invalid @enderror">
                                <option value="">Select Shift</option>
                                @foreach($shifts as $shift)
                                    <option value="{{ $shift->id }}">
                                        {{ $shift->name }} ({{ \Carbon\Carbon::parse($shift->start_time)->format('H:i') }} - {{ \Carbon\Carbon::parse($shift->end_time)->format('H:i') }})
                                    </option>
                                @endforeach
                            </select>
                            @error('editShiftId')
                                <div class="invalid-feedback">{{ $message }}</div>
                            @enderror
                        </div>

                        <div class="row">
                            <div class="col-md-6">
                                <div class="mb-3">
                                    <label class="form-label">Status <span class="text-danger">*</span></label>
                                    <select wire:model="editStatus" class="form-select @error('editStatus') is-invalid @enderror">
                                        <option value="scheduled">Scheduled</option>
                                        <option value="completed">Completed</option>
                                        <option value="cancelled">Cancelled</option>
                                        <option value="no_show">No Show</option>
                                    </select>
                                    @error('editStatus')
                                        <div class="invalid-feedback">{{ $message }}</div>
                                    @enderror
                                </div>
                            </div>
                            <div class="col-md-6">
                                <div class="mb-3">
                                    <label class="form-label">Shift Type <span class="text-danger">*</span></label>
                                    <select wire:model="editShiftType" class="form-select @error('editShiftType') is-invalid @enderror">
                                        <option value="regular">Regular</option>
                                        <option value="overtime">Overtime</option>
                                        <option value="special">Special</option>
                                    </select>
                                    @error('editShiftType')
                                        <div class="invalid-feedback">{{ $message }}</div>
                                    @enderror
                                </div>
                            </div>
                        </div>

                        <div class="mb-3">
                            <label class="form-label">Notes</label>
                            <textarea wire:model="editNotes" class="form-control @error('editNotes') is-invalid @enderror" rows="3" placeholder="Add notes..."></textarea>
                            @error('editNotes')
                                <div class="invalid-feedback">{{ $message }}</div>
                            @enderror
                        </div>
                    </div>
                    <div class="modal-footer">
                        <button type="button" class="btn btn-secondary" wire:click="closeEditModal">
                            <i class="fa-solid fa-times me-1"></i> Cancel
                        </button>
                        <button type="button" class="btn btn-primary" wire:click="saveRoster">
                            <i class="fa-solid fa-save me-1"></i> Save Changes
                        </button>
                    </div>
                </div>
            </div>
        </div>
    @endif

    {{-- Bulk Edit Modal --}}
    @if($showBulkEditModal)
        <div class="modal fade show d-block" tabindex="-1" style="background-color: rgba(0,0,0,0.5);">
            <div class="modal-dialog modal-dialog-centered">
                <div class="modal-content">
                    <div class="modal-header bg-info text-white">
                        <h5 class="modal-title">
                            <i class="fa-solid fa-pen-to-square me-2"></i> Bulk Edit ({{ count($selectedRosters) }} rosters)
                        </h5>
                        <button type="button" class="btn-close btn-close-white" wire:click="closeBulkEditModal"></button>
                    </div>
                    <div class="modal-body">
                        <div class="alert alert-info small">
                            <i class="fa-solid fa-circle-info me-1"></i>
                            Only filled fields will be updated. Leave a field empty to keep existing values.
                        </div>

                        <div class="mb-3">
                            <label class="form-label">Shift</label>
                            <select wire:model="bulkShiftId" class="form-select">
                                <option value="">-- Keep Current --</option>
                                @foreach($shifts as $shift)
                                    <option value="{{ $shift->id }}">
                                        {{ $shift->name }} ({{ \Carbon\Carbon::parse($shift->start_time)->format('H:i') }} - {{ \Carbon\Carbon::parse($shift->end_time)->format('H:i') }})
                                    </option>
                                @endforeach
                            </select>
                        </div>

                        <div class="row">
                            <div class="col-md-6">
                                <div class="mb-3">
                                    <label class="form-label">Status</label>
                                    <select wire:model="bulkStatus" class="form-select">
                                        <option value="">-- Keep Current --</option>
                                        <option value="scheduled">Scheduled</option>
                                        <option value="completed">Completed</option>
                                        <option value="cancelled">Cancelled</option>
                                        <option value="no_show">No Show</option>
                                    </select>
                                </div>
                            </div>
                            <div class="col-md-6">
                                <div class="mb-3">
                                    <label class="form-label">Shift Type</label>
                                    <select wire:model="bulkShiftType" class="form-select">
                                        <option value="">-- Keep Current --</option>
                                        <option value="regular">Regular</option>
                                        <option value="overtime">Overtime</option>
                                        <option value="special">Special</option>
                                    </select>
                                </div>
                            </div>
                        </div>
                    </div>
                    <div class="modal-footer">
                        <button type="button" class="btn btn-secondary" wire:click="closeBulkEditModal">
                            <i class="fa-solid fa-times me-1"></i> Cancel
                        </button>
                        <button type="button" class="btn btn-info" wire:click="saveBulkEdit">
                            <i class="fa-solid fa-save me-1"></i> Update All
                        </button>
                    </div>
                </div>
            </div>
        </div>
    @endif

    {{-- Delete Confirmation Modal --}}
    @if($showDeleteModal)
        <div class="modal fade show d-block" tabindex="-1" style="background-color: rgba(0,0,0,0.5);">
            <div class="modal-dialog modal-dialog-centered modal-sm">
                <div class="modal-content">
                    <div class="modal-header bg-danger text-white">
                        <h5 class="modal-title">
                            <i class="fa-solid fa-triangle-exclamation me-2"></i> Confirm Delete
                        </h5>
                        <button type="button" class="btn-close btn-close-white" wire:click="closeDeleteModal"></button>
                    </div>
                    <div class="modal-body text-center py-4">
                        <i class="fa-solid fa-trash-can text-danger mb-3" style="font-size: 48px;"></i>
                        <p class="mb-0">Are you sure you want to delete this roster?</p>
                        <p class="text-muted small">This action cannot be undone.</p>
                    </div>
                    <div class="modal-footer justify-content-center">
                        <button type="button" class="btn btn-secondary" wire:click="closeDeleteModal">
                            <i class="fa-solid fa-times me-1"></i> Cancel
                        </button>
                        <button type="button" class="btn btn-danger" wire:click="deleteRoster">
                            <i class="fa-solid fa-trash me-1"></i> Delete
                        </button>
                    </div>
                </div>
            </div>
        </div>
    @endif

    {{-- Loading Indicator --}}
    <div wire:loading class="position-fixed top-50 start-50 translate-middle" style="z-index: 9999;">
        <div class="spinner-border text-primary" role="status">
            <span class="visually-hidden">Loading...</span>
        </div>
    </div>

    <style>
        /* Avatar styles */
        .avatar-sm {
            width: 32px;
            height: 32px;
            display: flex;
            align-items: center;
            justify-content: center;
            font-size: 12px;
            font-weight: 600;
        }

        .avatar-lg {
            width: 48px;
            height: 48px;
            display: flex;
            align-items: center;
            justify-content: center;
        }

        .avatar-initials {
            text-transform: uppercase;
        }

        /* Modal display */
        .modal.show {
            display: block;
        }

        /* Table row hover */
        .table-hover tbody tr:hover {
            background-color: rgba(var(--bs-primary-rgb), 0.05);
        }

        /* Smooth transitions */
        .btn {
            transition: all 0.2s ease;
        }

        .badge {
            font-weight: 500;
        }

        /* Form select focus */
        .form-select:focus,
        .form-control:focus {
            border-color: var(--bs-primary);
            box-shadow: 0 0 0 0.2rem rgba(var(--bs-primary-rgb), 0.25);
        }
    </style>
</div>
