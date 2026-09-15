<div class="custom-container">

    <x-pages.breadcrumn title="SHIFT MANAGEMENT" :breadcrumbs="[
        ['label' => 'Home', 'url' => route('dashboard')],
        ['label' => 'Setup', 'url' => '#'],
        ['label' => 'Shift Management', 'url' => '#'],
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

    {{-- Summary Cards --}}
    <div class="row g-3 mb-4">
        <div class="col-12 col-sm-6 col-xl-4">
            <div class="card border-0 shadow-sm">
                <div class="card-body">
                    <div class="d-flex align-items-center">
                        <div class="flex-shrink-0">
                            <div class="avatar avatar-lg bg-primary-subtle text-primary rounded">
                                <i class="fa-solid fa-clock fs-4"></i>
                            </div>
                        </div>
                        <div class="flex-grow-1 ms-3">
                            <p class="text-muted mb-1 small">Total Shifts</p>
                            <h4 class="mb-0 fw-bold">{{ number_format($summary['total']) }}</h4>
                        </div>
                    </div>
                </div>
            </div>
        </div>

        <div class="col-12 col-sm-6 col-xl-4">
            <div class="card border-0 shadow-sm">
                <div class="card-body">
                    <div class="d-flex align-items-center">
                        <div class="flex-shrink-0">
                            <div class="avatar avatar-lg bg-success-subtle text-success rounded">
                                <i class="fa-solid fa-circle-check fs-4"></i>
                            </div>
                        </div>
                        <div class="flex-grow-1 ms-3">
                            <p class="text-muted mb-1 small">Active Shifts</p>
                            <h4 class="mb-0 fw-bold text-success">{{ number_format($summary['active']) }}</h4>
                        </div>
                    </div>
                </div>
            </div>
        </div>

        <div class="col-12 col-sm-6 col-xl-4">
            <div class="card border-0 shadow-sm">
                <div class="card-body">
                    <div class="d-flex align-items-center">
                        <div class="flex-shrink-0">
                            <div class="avatar avatar-lg bg-danger-subtle text-danger rounded">
                                <i class="fa-solid fa-circle-xmark fs-4"></i>
                            </div>
                        </div>
                        <div class="flex-grow-1 ms-3">
                            <p class="text-muted mb-1 small">Inactive Shifts</p>
                            <h4 class="mb-0 fw-bold text-danger">{{ number_format($summary['inactive']) }}</h4>
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
                {{-- Card Header with Filters --}}
                <div class="card-header border-bottom bg-white">
                    <div class="row g-3 align-items-center">
                        {{-- Search --}}
                        <div class="col-12 col-md-4">
                            <div class="input-group">
                                <span class="input-group-text bg-white">
                                    <i class="fa-solid fa-magnifying-glass"></i>
                                </span>
                                <input type="search" wire:model.live.debounce.300ms="search" class="form-control"
                                    placeholder="Search shifts..." />
                                @if($search)
                                    <button wire:click="$set('search', '')" class="btn btn-outline-secondary" type="button">
                                        <i class="fa-solid fa-times"></i>
                                    </button>
                                @endif
                            </div>
                        </div>

                        {{-- Status Filter --}}
                        <div class="col-12 col-md-3">
                            <select wire:model.live="statusFilter" class="form-select">
                                <option value="">All Status</option>
                                <option value="active">Active</option>
                                <option value="inactive">Inactive</option>
                            </select>
                        </div>

                        {{-- Actions --}}
                        <div class="col-12 col-md-5">
                            <div class="d-flex flex-wrap gap-2 justify-content-md-end">
                                {{-- Reset Filters --}}
                                @if($search || $statusFilter)
                                    <button wire:click="resetFilters" type="button" class="btn btn-outline-secondary">
                                        <i class="fa-solid fa-rotate-left me-1"></i> Reset
                                    </button>
                                @endif

                                {{-- Add Shift Button --}}
                                <button wire:click="openModal('create')" class="btn btn-primary">
                                    <i class="fa-solid fa-plus me-1"></i> Add Shift
                                </button>
                            </div>
                        </div>
                    </div>
                </div>

                {{-- Table Section --}}
                <div class="table-responsive" style="min-height: 400px;">
                    <table class="table table-hover table-centered align-middle mb-0">
                        <thead class="table-light">
                            <tr>
                                <th class="text-center" style="width: 50px;">#</th>
                                <th wire:click="sortBy('name')" style="cursor: pointer;">
                                    Shift Name
                                    @if($sortField === 'name')
                                        <i class="fa-solid fa-sort-{{ $sortDirection === 'asc' ? 'up' : 'down' }} ms-1"></i>
                                    @endif
                                </th>
                                <th>Description</th>
                                <th wire:click="sortBy('start_time')" style="cursor: pointer;">
                                    Time Range
                                    @if($sortField === 'start_time')
                                        <i class="fa-solid fa-sort-{{ $sortDirection === 'asc' ? 'up' : 'down' }} ms-1"></i>
                                    @endif
                                </th>
                                <th class="text-center">Early Count</th>
                                <th class="text-center">Late Count</th>
                                <th wire:click="sortBy('status')" style="cursor: pointer;">
                                    Status
                                    @if($sortField === 'status')
                                        <i class="fa-solid fa-sort-{{ $sortDirection === 'asc' ? 'up' : 'down' }} ms-1"></i>
                                    @endif
                                </th>
                                <th class="text-center" style="width: 150px;">Actions</th>
                            </tr>
                        </thead>
                        <tbody>
                            @forelse($shifttypes as $index => $shift)
                                <tr wire:key="shift-{{ $shift->id }}">
                                    <td class="text-center text-muted">
                                        {{ $shifttypes->firstItem() + $index }}
                                    </td>
                                    <td>
                                        <div class="d-flex align-items-center">
                                            <div class="avatar avatar-sm bg-primary-subtle text-primary rounded-circle me-2">
                                                <i class="fa-solid fa-clock"></i>
                                            </div>
                                            <span class="fw-semibold text-dark">{{ $shift->name }}</span>
                                            @if($shift->is_default)
                                                <span class="badge bg-primary ms-2" title="Used when an employee has no roster">
                                                    <i class="fa-solid fa-star"></i> Default
                                                </span>
                                            @endif
                                        </div>
                                    </td>
                                    <td>
                                        @if($shift->description)
                                            <span class="text-muted small">{{ Str::limit($shift->description, 50) }}</span>
                                        @else
                                            <span class="text-muted">-</span>
                                        @endif
                                    </td>
                                    <td>
                                        <div class="d-flex align-items-center gap-2">
                                            <span class="badge bg-info-subtle text-info">
                                                <i class="fa-solid fa-sun"></i>
                                                {{ \Carbon\Carbon::parse($shift->start_time)->format('H:i') }}
                                            </span>
                                            <i class="fa-solid fa-arrow-right text-muted"></i>
                                            <span class="badge bg-warning-subtle text-warning">
                                                <i class="fa-solid fa-moon"></i>
                                                {{ \Carbon\Carbon::parse($shift->end_time)->format('H:i') }}
                                            </span>
                                        </div>
                                        <small class="text-muted">
                                            @php
                                                $minutes = $shift->durationInMinutes();
                                            @endphp
                                            ({{ intdiv($minutes, 60) }}h {{ $minutes % 60 }}m)
                                            @if($shift->crossesMidnight())
                                                <span class="badge bg-info-subtle text-info ms-1">
                                                    <i class="fa-solid fa-moon"></i> Overnight
                                                </span>
                                            @endif
                                        </small>
                                    </td>
                                    <td class="text-center">
                                        <span class="badge bg-success-subtle text-success">
                                            <i class="fa-solid fa-hourglass-start"></i>
                                            {{ $shift->count_early }} min
                                        </span>
                                    </td>
                                    <td class="text-center">
                                        <span class="badge bg-danger-subtle text-danger">
                                            <i class="fa-solid fa-hourglass-end"></i>
                                            {{ $shift->count_late }} min
                                        </span>
                                    </td>
                                    <td>
                                        @if($shift->status === 'active')
                                            <span class="badge bg-success">
                                                <i class="fa-solid fa-circle-check"></i> Active
                                            </span>
                                        @else
                                            <span class="badge bg-danger">
                                                <i class="fa-solid fa-circle-xmark"></i> Inactive
                                            </span>
                                        @endif
                                    </td>
                                    <td>
                                        <div class="d-flex gap-1 justify-content-center">
                                            {{-- Edit Button --}}
                                            <button
                                                wire:click="openModal('edit', '{{ $shift->id }}')"
                                                class="btn btn-sm btn-primary"
                                                title="Edit">
                                                <i class="fa-solid fa-edit"></i>
                                            </button>

                                            {{-- Toggle Status Button --}}
                                            <button
                                                wire:click="toggleStatus('{{ $shift->id }}')"
                                                class="btn btn-sm btn-{{ $shift->status === 'active' ? 'warning' : 'success' }}"
                                                title="{{ $shift->status === 'active' ? 'Deactivate' : 'Activate' }}">
                                                <i class="fa-solid fa-{{ $shift->status === 'active' ? 'ban' : 'check' }}"></i>
                                            </button>

                                            {{-- Set Default Button --}}
                                            @unless($shift->is_default)
                                                <button
                                                    wire:click="setDefault('{{ $shift->id }}')"
                                                    wire:confirm="Make this the default shift? It will be used for employees without a roster."
                                                    class="btn btn-sm btn-outline-primary"
                                                    title="Set as default shift">
                                                    <i class="fa-regular fa-star"></i>
                                                </button>
                                            @endunless

                                            {{-- Delete Button --}}
                                            <button
                                                wire:click="delete('{{ $shift->id }}')"
                                                wire:confirm="Are you sure you want to delete this shift?"
                                                class="btn btn-sm btn-danger"
                                                title="Delete">
                                                <i class="fa-solid fa-trash"></i>
                                            </button>
                                        </div>
                                    </td>
                                </tr>
                            @empty
                                <tr>
                                    <td colspan="8" class="text-center py-5">
                                        <div class="d-flex flex-column align-items-center justify-content-center">
                                            <i class="fa-solid fa-clock text-muted mb-3" style="font-size: 48px;"></i>
                                            <h5 class="text-muted">No Shifts Found</h5>
                                            <p class="text-muted">
                                                @if($search || $statusFilter)
                                                    Try adjusting your filters or search query
                                                @else
                                                    Click "Add Shift" to create your first shift
                                                @endif
                                            </p>
                                        </div>
                                    </td>
                                </tr>
                            @endforelse
                        </tbody>
                    </table>
                </div>

                {{-- Card Footer with Pagination --}}
                <div class="card-footer border-top bg-white">
                    <div class="d-flex flex-column flex-md-row justify-content-between align-items-center gap-3">
                        <!-- Results Info -->
                        <div class="text-muted">
                            @if($shifttypes->total() > 0)
                                Showing {{ $shifttypes->firstItem() }} to {{ $shifttypes->lastItem() }} of
                                {{ $shifttypes->total() }} shifts
                            @else
                                No shifts found
                            @endif
                        </div>

                        <!-- Pagination and Per Page -->
                        <div class="d-flex flex-column flex-sm-row align-items-center gap-3">
                            <!-- Per Page Selector -->
                            <div class="d-flex align-items-center gap-2">
                                <label class="form-label mb-0 text-nowrap small">Rows per page:</label>
                                <select wire:model.live="perPage" class="form-select form-select-sm" style="width: auto;">
                                    <option value="5">5</option>
                                    <option value="10">10</option>
                                    <option value="25">25</option>
                                    <option value="50">50</option>
                                </select>
                            </div>

                            <!-- Pagination Links -->
                            <div>
                                {{ $shifttypes->links() }}
                            </div>
                        </div>
                    </div>
                </div>
            </div>
        </div>
    </div>

    {{-- Add/Edit Modal --}}
    @if($showModal)
        <div class="modal fade show d-block" tabindex="-1" style="background-color: rgba(0,0,0,0.5);">
            <div class="modal-dialog modal-lg modal-dialog-centered modal-dialog-scrollable">
                <div class="modal-content">
                    <form wire:submit.prevent="save">
                        <div class="modal-header bg-primary text-white">
                            <h5 class="modal-title">
                                <i class="fa-solid fa-{{ $modalMode === 'edit' ? 'edit' : 'plus' }} me-2"></i>
                                {{ $modalMode === 'edit' ? 'Edit Shift' : 'Add New Shift' }}
                            </h5>
                            <button type="button" class="btn-close btn-close-white" wire:click="closeModal"></button>
                        </div>

                        <div class="modal-body">
                            <div class="row g-3">
                                {{-- Shift Name --}}
                                <div class="col-md-6">
                                    <label class="form-label">
                                        Shift Name <span class="text-danger">*</span>
                                    </label>
                                    <input type="text" wire:model="name"
                                        class="form-control @error('name') is-invalid @enderror"
                                        placeholder="e.g., Morning Shift">
                                    @error('name')
                                        <div class="invalid-feedback">{{ $message }}</div>
                                    @enderror
                                </div>

                                {{-- Status --}}
                                <div class="col-md-6">
                                    <label class="form-label">
                                        Status <span class="text-danger">*</span>
                                    </label>
                                    <select wire:model="status" class="form-select @error('status') is-invalid @enderror">
                                        <option value="active">Active</option>
                                        <option value="inactive">Inactive</option>
                                    </select>
                                    @error('status')
                                        <div class="invalid-feedback">{{ $message }}</div>
                                    @enderror
                                </div>

                                {{-- Default Shift --}}
                                <div class="col-12">
                                    <div class="form-check">
                                        <input type="checkbox" wire:model="is_default"
                                            class="form-check-input" id="shiftIsDefault">
                                        <label class="form-check-label" for="shiftIsDefault">
                                            Use as <strong>default shift</strong>
                                        </label>
                                    </div>
                                    <small class="text-muted">
                                        Applied to employees who have no roster. Only one shift can be the default.
                                    </small>
                                </div>

                                {{-- Description --}}
                                <div class="col-12">
                                    <label class="form-label">Description</label>
                                    <textarea wire:model="description"
                                        class="form-control @error('description') is-invalid @enderror"
                                        rows="2"
                                        placeholder="Brief description of this shift"></textarea>
                                    @error('description')
                                        <div class="invalid-feedback">{{ $message }}</div>
                                    @enderror
                                </div>

                                {{-- Start Time --}}
                                <div class="col-md-6">
                                    <label class="form-label">
                                        Start Time <span class="text-danger">*</span>
                                    </label>
                                    <input type="time" wire:model.live="start_time"
                                        class="form-control @error('start_time') is-invalid @enderror">
                                    @error('start_time')
                                        <div class="invalid-feedback">{{ $message }}</div>
                                    @enderror
                                </div>

                                {{-- End Time --}}
                                <div class="col-md-6">
                                    <label class="form-label">
                                        End Time <span class="text-danger">*</span>
                                    </label>
                                    <input type="time" wire:model.live="end_time"
                                        class="form-control @error('end_time') is-invalid @enderror">
                                    @error('end_time')
                                        <div class="invalid-feedback">{{ $message }}</div>
                                    @enderror
                                    @if($this->crossesMidnight)
                                        <small class="text-info d-block mt-1">
                                            <i class="fa-solid fa-moon"></i>
                                            Overnight shift — ends the next day.
                                        </small>
                                    @endif
                                </div>

                                {{-- Count Early (Minutes) --}}
                                <div class="col-md-6">
                                    <label class="form-label">
                                        Count Early (Minutes) <span class="text-danger">*</span>
                                    </label>
                                    <div class="input-group">
                                        <input type="number" wire:model="count_early"
                                            class="form-control @error('count_early') is-invalid @enderror"
                                            placeholder="15"
                                            min="0"
                                            max="999">
                                        <span class="input-group-text">min</span>
                                    </div>
                                    <small class="text-muted">Employees arriving this many minutes early will be marked as early</small>
                                    @error('count_early')
                                        <div class="invalid-feedback d-block">{{ $message }}</div>
                                    @enderror
                                </div>

                                {{-- Count Late (Minutes) --}}
                                <div class="col-md-6">
                                    <label class="form-label">
                                        Count Late (Minutes) <span class="text-danger">*</span>
                                    </label>
                                    <div class="input-group">
                                        <input type="number" wire:model="count_late"
                                            class="form-control @error('count_late') is-invalid @enderror"
                                            placeholder="15"
                                            min="0"
                                            max="999">
                                        <span class="input-group-text">min</span>
                                    </div>
                                    <small class="text-muted">Employees arriving this many minutes late will be marked as late</small>
                                    @error('count_late')
                                        <div class="invalid-feedback d-block">{{ $message }}</div>
                                    @enderror
                                </div>
                            </div>
                        </div>

                        <div class="modal-footer">
                            <button type="button" class="btn btn-secondary" wire:click="closeModal">
                                <i class="fa-solid fa-times"></i> Cancel
                            </button>
                            <button type="submit" class="btn btn-primary">
                                <i class="fa-solid fa-save"></i>
                                {{ $modalMode === 'edit' ? 'Update Shift' : 'Create Shift' }}
                            </button>
                        </div>
                    </form>
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
            align-items: center;
            justify-content: center;
            font-size: 14px;
            font-weight: 600;
        }

        .avatar-lg {
            width: 48px;
            height: 48px;
            display: flex;
            align-items: center;
            justify-content: center;
        }

        .modal.show {
            display: block;
        }

        /* Smooth transitions */
        tr {
            transition: background-color 0.2s ease;
        }

        .btn {
            transition: all 0.2s ease;
        }

        /* Sortable column hover */
        th[wire\:click] {
            user-select: none;
        }

        th[wire\:click]:hover {
            background-color: #f0f0f0;
        }
    </style>
</div>
