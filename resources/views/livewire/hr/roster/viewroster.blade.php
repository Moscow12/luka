<div class="custom-container">

    <x-pages.breadcrumn title="VIEW ROSTERS" :breadcrumbs="[
        ['label' => 'Home', 'url' => route('dashboard')],
        ['label' => 'HR', 'url' => '#'],
        ['label' => 'View Rosters', 'url' => '#'],
    ]">
    </x-pages.breadcrumn>

    {{-- Success Messages --}}
    @if (session()->has('success'))
        <div class="alert alert-success alert-dismissible fade show" role="alert">
            <i class="fa-solid fa-circle-check me-2"></i>
            {{ session('success') }}
            <button type="button" class="btn-close" data-bs-dismiss="alert" aria-label="Close"></button>
        </div>
    @endif

    {{-- Summary Cards --}}
    <div class="row g-3 mb-4">
        <div class="col-12 col-sm-6 col-xl-3">
            <div class="card border-0 shadow-sm">
                <div class="card-body">
                    <div class="d-flex align-items-center">
                        <div class="flex-shrink-0">
                            <div class="avatar avatar-lg bg-primary-subtle text-primary rounded">
                                <i class="fa-solid fa-calendar-check fs-4"></i>
                            </div>
                        </div>
                        <div class="flex-grow-1 ms-3">
                            <p class="text-muted mb-1 small">Total Rosters</p>
                            <h4 class="mb-0 fw-bold">{{ number_format($summary['total']) }}</h4>
                        </div>
                    </div>
                </div>
            </div>
        </div>

        <div class="col-12 col-sm-6 col-xl-3">
            <div class="card border-0 shadow-sm">
                <div class="card-body">
                    <div class="d-flex align-items-center">
                        <div class="flex-shrink-0">
                            <div class="avatar avatar-lg bg-info-subtle text-info rounded">
                                <i class="fa-solid fa-clock fs-4"></i>
                            </div>
                        </div>
                        <div class="flex-grow-1 ms-3">
                            <p class="text-muted mb-1 small">Scheduled</p>
                            <h4 class="mb-0 fw-bold text-info">{{ number_format($summary['scheduled']) }}</h4>
                        </div>
                    </div>
                </div>
            </div>
        </div>

        <div class="col-12 col-sm-6 col-xl-3">
            <div class="card border-0 shadow-sm">
                <div class="card-body">
                    <div class="d-flex align-items-center">
                        <div class="flex-shrink-0">
                            <div class="avatar avatar-lg bg-success-subtle text-success rounded">
                                <i class="fa-solid fa-circle-check fs-4"></i>
                            </div>
                        </div>
                        <div class="flex-grow-1 ms-3">
                            <p class="text-muted mb-1 small">Completed</p>
                            <h4 class="mb-0 fw-bold text-success">{{ number_format($summary['completed']) }}</h4>
                        </div>
                    </div>
                </div>
            </div>
        </div>

        <div class="col-12 col-sm-6 col-xl-3">
            <div class="card border-0 shadow-sm">
                <div class="card-body">
                    <div class="d-flex align-items-center">
                        <div class="flex-shrink-0">
                            <div class="avatar avatar-lg bg-danger-subtle text-danger rounded">
                                <i class="fa-solid fa-circle-xmark fs-4"></i>
                            </div>
                        </div>
                        <div class="flex-grow-1 ms-3">
                            <p class="text-muted mb-1 small">Cancelled</p>
                            <h4 class="mb-0 fw-bold text-danger">{{ number_format($summary['cancelled']) }}</h4>
                        </div>
                    </div>
                </div>
            </div>
        </div>
    </div>

    {{-- Main Card --}}
    <div class="row">
        <div class="col-12">
            <div class="card card-lg border-0 shadow-sm">
                <!-- Card Header with Filters -->
                <div class="card-header border-bottom bg-white">
                    <div class="row g-3 align-items-center">
                        <!-- Roster Date -->
                        <div class="col-12 col-md-3">
                            <div class="input-group">
                                <span class="input-group-text bg-white">
                                    <i class="fa-solid fa-calendar"></i>
                                </span>
                                <input type="text" wire:model.live="rosterDate" class="form-control flatpickr" placeholder="Select Date" />
                                @if($rosterDate)
                                    <button wire:click="$set('rosterDate', '')" class="btn btn-outline-secondary" type="button">
                                        <i class="fa-solid fa-times"></i>
                                    </button>
                                @endif
                            </div>
                        </div>

                        <!-- Search -->
                        <div class="col-12 col-md-3">
                            <div class="input-group">
                                <span class="input-group-text bg-white">
                                    <i class="fa-solid fa-magnifying-glass"></i>
                                </span>
                                <input type="search" wire:model.live.debounce.300ms="search" class="form-control"
                                    placeholder="Search employee..." />
                                @if($search)
                                    <button wire:click="$set('search', '')" class="btn btn-outline-secondary" type="button">
                                        <i class="fa-solid fa-times"></i>
                                    </button>
                                @endif
                            </div>
                        </div>

                        <!-- Department Filter -->
                        <div class="col-12 col-md-3">
                            <select wire:model.live="department" class="form-select">
                                <option value="">All Departments</option>
                                @foreach($departments as $dept)
                                    <option value="{{ $dept->id }}">{{ $dept->name }}</option>
                                @endforeach
                            </select>
                        </div>

                        <!-- Actions -->
                        <div class="col-12 col-md-3">
                            <div class="d-flex flex-wrap gap-2 justify-content-md-end">
                                <!-- Filter Toggle Button -->
                                <button wire:click="toggleFilters" type="button"
                                    class="btn {{ $showFilters ? 'btn-primary' : 'btn-white' }}">
                                    <i class="fa-solid fa-filter me-1"></i>
                                    {{ $showFilters ? 'Hide' : 'Show' }} Filters
                                    @if($shift || $status || $shiftType)
                                        <span class="badge bg-danger ms-1">
                                            {{ collect([$shift, $status, $shiftType])->filter()->count() }}
                                        </span>
                                    @endif
                                </button>

                                <!-- Reset Filters -->
                                @if($search || $department || $shift || $status || $shiftType)
                                    <button wire:click="resetFilters" type="button" class="btn btn-outline-secondary">
                                        <i class="fa-solid fa-rotate-left me-1"></i> Reset
                                    </button>
                                @endif
                            </div>
                        </div>
                    </div>

                    <!-- Advanced Filters (Collapsible) -->
                    @if($showFilters)
                        <div class="row g-3 mt-2 pt-3 border-top">
                            <div class="col-12 col-md-4">
                                <label class="form-label small text-muted mb-1">Date From</label>
                                <input type="text" wire:model.live="dateFrom" class="form-control flatpickr" placeholder="Select Date">
                            </div>

                            <div class="col-12 col-md-4">
                                <label class="form-label small text-muted mb-1">Date To</label>
                                <input type="text" wire:model.live="dateTo" class="form-control flatpickr" placeholder="Select Date">
                            </div>

                            <div class="col-12 col-md-4">
                                <label class="form-label small text-muted mb-1">Shift</label>
                                <select wire:model.live="shift" class="form-select">
                                    <option value="">All Shifts</option>
                                    @foreach($shifts as $shiftOption)
                                        <option value="{{ $shiftOption->id }}">{{ $shiftOption->name }}</option>
                                    @endforeach
                                </select>
                            </div>

                            <div class="col-12 col-md-4">
                                <label class="form-label small text-muted mb-1">Status</label>
                                <select wire:model.live="status" class="form-select">
                                    <option value="">All Status</option>
                                    <option value="scheduled">Scheduled</option>
                                    <option value="completed">Completed</option>
                                    <option value="cancelled">Cancelled</option>
                                    <option value="no_show">No Show</option>
                                </select>
                            </div>

                            <div class="col-12 col-md-4">
                                <label class="form-label small text-muted mb-1">Shift Type</label>
                                <select wire:model.live="shiftType" class="form-select">
                                    <option value="">All Types</option>
                                    <option value="regular">Regular</option>
                                    <option value="overtime">Overtime</option>
                                    <option value="special">Special</option>
                                </select>
                            </div>
                        </div>
                    @endif
                </div>

                <!-- Table Section -->
                <div class="table-responsive" style="min-height: 400px;">
                    <table class="table table-hover table-centered align-middle mb-0">
                        <thead class="table-light">
                            <tr>
                                <th class="text-center" style="width: 50px;">#</th>
                                <th>Employee</th>
                                <th>Department</th>
                                <th>Roster Date</th>
                                <th>Shift</th>
                                <th>Shift Time</th>
                                <th>Type</th>
                                <th>Status</th>
                                <th>Notes</th>
                                <th class="text-center" style="width: 120px;">Actions</th>
                            </tr>
                        </thead>
                        <tbody>
                            @forelse($rosters as $index => $roster)
                                <tr wire:key="roster-{{ $roster->id }}">
                                    <td class="text-center text-muted">
                                        {{ $rosters->firstItem() + $index }}
                                    </td>
                                    <td>
                                        <div class="d-flex align-items-center">
                                            <div class="avatar avatar-sm bg-primary-subtle text-primary rounded-circle me-2">
                                                <span class="avatar-initials">
                                                    {{ substr($roster->employee->first_name, 0, 1) }}{{ substr($roster->employee->last_name, 0, 1) }}
                                                </span>
                                            </div>
                                            <div>
                                                <span class="fw-semibold text-dark d-block">
                                                    {{ $roster->employee->first_name }} {{ $roster->employee->last_name }}
                                                </span>
                                                <small class="text-muted">
                                                    <i class="fa-solid fa-hashtag"></i> {{ $roster->employee->employee_number ?? 'N/A' }}
                                                </small>
                                            </div>
                                        </div>
                                    </td>
                                    <td>
                                        <span class="badge bg-light text-dark">
                                            {{ $roster->employee->department->name ?? 'N/A' }}
                                        </span>
                                    </td>
                                    <td>
                                        <span class="text-dark">
                                            <i class="fa-solid fa-calendar-day text-primary"></i>
                                            {{ \Carbon\Carbon::parse($roster->roster_date)->format('d M Y') }}
                                        </span>
                                        <br>
                                        <small class="text-muted">{{ \Carbon\Carbon::parse($roster->roster_date)->diffForHumans() }}</small>
                                    </td>
                                    <td>
                                        <span class="badge bg-info text-white">
                                            {{ $roster->shift->name ?? 'N/A' }}
                                        </span>
                                    </td>
                                    <td>
                                        @if($roster->shift)
                                            <small class="text-muted">
                                                <i class="fa-solid fa-clock"></i>
                                                {{ \Carbon\Carbon::parse($roster->shift->start_time)->format('H:i') }} -
                                                {{ \Carbon\Carbon::parse($roster->shift->end_time)->format('H:i') }}
                                            </small>
                                        @else
                                            <span class="text-muted">N/A</span>
                                        @endif
                                    </td>
                                    <td>
                                        @php
                                            $typeColors = [
                                                'regular' => 'secondary',
                                                'overtime' => 'warning',
                                                'special' => 'purple',
                                            ];
                                            $typeColor = $typeColors[$roster->shift_type] ?? 'secondary';
                                        @endphp
                                        <span class="badge bg-{{ $typeColor }}">{{ ucfirst($roster->shift_type) }}</span>
                                    </td>
                                    <td>
                                        @php
                                            $statusColors = [
                                                'scheduled' => 'info',
                                                'completed' => 'success',
                                                'cancelled' => 'danger',
                                                'no_show' => 'warning',
                                            ];
                                            $statusColor = $statusColors[$roster->status] ?? 'secondary';
                                        @endphp
                                        <span class="badge bg-{{ $statusColor }}">{{ ucfirst(str_replace('_', ' ', $roster->status)) }}</span>
                                    </td>
                                    <td>
                                        @if($roster->notes)
                                            <span class="text-muted small" title="{{ $roster->notes }}">
                                                {{ Str::limit($roster->notes, 30) }}
                                            </span>
                                        @else
                                            <span class="text-muted">-</span>
                                        @endif
                                    </td>
                                    <td>
                                        <div class="d-flex gap-1 justify-content-center">
                                            <button
                                                wire:click="editRoster('{{ $roster->id }}')"
                                                class="btn btn-sm btn-primary"
                                                title="Edit">
                                                <i class="fa-solid fa-edit"></i>
                                            </button>
                                            <button
                                                wire:click="deleteRoster('{{ $roster->id }}')"
                                                wire:confirm="Are you sure you want to delete this roster?"
                                                class="btn btn-sm btn-danger"
                                                title="Delete">
                                                <i class="fa-solid fa-trash"></i>
                                            </button>
                                        </div>
                                    </td>
                                </tr>
                            @empty
                                <tr>
                                    <td colspan="10" class="text-center py-5">
                                        <div class="d-flex flex-column align-items-center justify-content-center">
                                            <i class="fa-solid fa-calendar-xmark text-muted mb-3" style="font-size: 48px;"></i>
                                            <h5 class="text-muted">No Rosters Found</h5>
                                            <p class="text-muted">
                                                @if($search || $department || $shift || $status || $shiftType)
                                                    Try adjusting your filters or search query
                                                @else
                                                    No rosters have been generated yet
                                                @endif
                                            </p>
                                        </div>
                                    </td>
                                </tr>
                            @endforelse
                        </tbody>
                    </table>
                </div>

                <!-- Card Footer with Pagination -->
                <div class="card-footer border-top bg-white">
                    <div class="d-flex flex-column flex-md-row justify-content-between align-items-center gap-3">
                        <!-- Results Info -->
                        <div class="text-muted">
                            @if($rosters->total() > 0)
                                Showing {{ $rosters->firstItem() }} to {{ $rosters->lastItem() }} of
                                {{ $rosters->total() }} rosters
                            @else
                                No rosters found
                            @endif
                        </div>

                        <!-- Pagination and Per Page -->
                        <div class="d-flex flex-column flex-sm-row align-items-center gap-3">
                            <!-- Per Page Selector -->
                            <div class="d-flex align-items-center gap-2">
                                <label class="form-label mb-0 text-nowrap small">Rows per page:</label>
                                <select wire:model.live="perPage" class="form-select form-select-sm" style="width: auto;">
                                    <option value="10">10</option>
                                    <option value="20">20</option>
                                    <option value="50">50</option>
                                    <option value="100">100</option>
                                </select>
                            </div>

                            <!-- Pagination Links -->
                            <div>
                                {{ $rosters->links() }}
                            </div>
                        </div>
                    </div>
                </div>
            </div>
        </div>
    </div>

    {{-- Edit Modal --}}
    @if($editingRosterId)
        <div class="modal fade show d-block" tabindex="-1" style="background-color: rgba(0,0,0,0.5);">
            <div class="modal-dialog modal-dialog-centered">
                <div class="modal-content">
                    <div class="modal-header bg-primary text-white">
                        <h5 class="modal-title">
                            <i class="fa-solid fa-edit me-2"></i> Edit Roster
                        </h5>
                        <button type="button" class="btn-close btn-close-white" wire:click="closeEditModal"></button>
                    </div>
                    <div class="modal-body">
                        <div class="mb-3">
                            <label class="form-label">Status</label>
                            <select wire:model="editStatus" class="form-select @error('editStatus') is-invalid @enderror">
                                <option value="">Select Status</option>
                                <option value="scheduled">Scheduled</option>
                                <option value="completed">Completed</option>
                                <option value="cancelled">Cancelled</option>
                                <option value="no_show">No Show</option>
                            </select>
                            @error('editStatus')
                                <div class="invalid-feedback">{{ $message }}</div>
                            @enderror
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
                            <i class="fa-solid fa-times"></i> Cancel
                        </button>
                        <button type="button" class="btn btn-primary" wire:click="updateRoster">
                            <i class="fa-solid fa-save"></i> Update Roster
                        </button>
                    </div>
                </div>
            </div>
        </div>
    @endif

    <!-- Loading Indicator -->
    <div wire:loading class="position-fixed top-50 start-50 translate-middle" style="z-index: 9999;">
        <div class="spinner-border text-primary" role="status">
            <span class="visually-hidden">Loading...</span>
        </div>
    </div>

    <style>
        .avatar {
            width: 48px;
            height: 48px;
            display: flex;
            align-items: center;
            justify-content: center;
        }

        .avatar-sm {
            width: 36px;
            height: 36px;
            display: flex;
            align-items-center;
            justify-content: center;
            font-size: 14px;
            font-weight: 600;
        }

        .avatar-lg {
            width: 48px;
            height: 48px;
            display: flex;
            align-items-center;
            justify-content: center;
        }

        .avatar-initials {
            text-transform: uppercase;
        }

        .modal.show {
            display: block;
        }

        /* Custom badge color for purple */
        .bg-purple {
            background-color: #6f42c1 !important;
            color: white;
        }

        /* Smooth transitions */
        tr {
            transition: background-color 0.2s ease;
        }

        .btn {
            transition: all 0.2s ease;
        }
    </style>
</div>
