<div class="custom-container">

    <x-pages.breadcrumn title="GENERATE ROSTER" :breadcrumbs="[
        ['label' => 'Home', 'url' => route('dashboard')],
        ['label' => 'HR', 'url' => '#'],
        ['label' => 'Generate Roster', 'url' => '#'],
    ]">
    </x-pages.breadcrumn>

    {{-- Success/Error Messages --}}
    @if (session()->has('success'))
        <div class="alert alert-success alert-dismissible fade show" role="alert">
            <i class="fa-solid fa-circle-check me-2"></i>
            {{ session('success') }}
            <button type="button" class="btn-close" data-bs-dismiss="alert" aria-label="Close"></button>
        </div>
    @endif

    @if (session()->has('errors'))
        <div class="alert alert-warning alert-dismissible fade show" role="alert">
            <i class="fa-solid fa-triangle-exclamation me-2"></i>
            <strong>Some rosters could not be created:</strong>
            <ul class="mb-0 mt-2">
                @foreach (session('errors') as $error)
                    <li>{{ $error }}</li>
                @endforeach
            </ul>
            <button type="button" class="btn-close" data-bs-dismiss="alert" aria-label="Close"></button>
        </div>
    @endif

    {{-- Validation Errors --}}
    @if ($errors->any())
        <div class="alert alert-danger alert-dismissible fade show" role="alert">
            <i class="fa-solid fa-circle-exclamation me-2"></i>
            <strong>Please fix the following errors:</strong>
            <ul class="mb-0 mt-2">
                @foreach ($errors->all() as $error)
                    <li>{{ $error }}</li>
                @endforeach
            </ul>
            <button type="button" class="btn-close" data-bs-dismiss="alert" aria-label="Close"></button>
        </div>
    @endif

    {{-- Mode Toggle Buttons --}}
    <div class="row mb-3">
        <div class="col-12">
            <div class="btn-group w-100" role="group">
                <button type="button"
                        wire:click="toggleAutoGenerateMode"
                        class="btn {{ !$autoGenerateMode ? 'btn-primary' : 'btn-outline-primary' }}">
                    <i class="fa-solid fa-hand-pointer me-2"></i> Manual Generate
                </button>
                <button type="button"
                        wire:click="toggleAutoGenerateMode"
                        class="btn {{ $autoGenerateMode ? 'btn-success' : 'btn-outline-success' }}">
                    <i class="fa-solid fa-robot me-2"></i> Auto Generate Mode
                </button>
            </div>
        </div>
    </div>

    {{-- Roster Generation Form --}}
    <div class="row mb-4">
        <div class="col-12">
            <div class="card border-0 shadow-sm">
                <div class="card-header {{ $autoGenerateMode ? 'bg-success' : 'bg-primary' }} bg-gradient text-white">
                    <h5 class="mb-0">
                        <i class="fa-solid fa-{{ $autoGenerateMode ? 'robot' : 'calendar-days' }} me-2"></i>
                        {{ $autoGenerateMode ? 'Auto Generate Roster' : 'Manual Roster Details' }}
                    </h5>
                </div>
                <div class="card-body">
                    @if(!$autoGenerateMode)
                        {{-- Manual Generate Mode --}}
                        <div class="row g-3">
                            <!-- Roster Date -->
                            <div class="col-md-3">
                                <label class="form-label">
                                    <i class="fa-solid fa-calendar text-primary"></i> Roster Date
                                    <span class="text-danger">*</span>
                                </label>
                                <input type="text" wire:model="rosterDate" class="form-control flatpickr @error('rosterDate') is-invalid @enderror" placeholder="Select Date">
                                @error('rosterDate')
                                    <div class="invalid-feedback">{{ $message }}</div>
                                @enderror
                            </div>

                            <!-- Shift Selection -->
                            <div class="col-md-3">
                                <label class="form-label">
                                    <i class="fa-solid fa-clock text-primary"></i> Shift
                                    <span class="text-danger">*</span>
                                </label>
                                <select wire:model="shift" class="form-select @error('shift') is-invalid @enderror">
                                    <option value="">Select Shift</option>
                                    @foreach($shifts as $shiftOption)
                                        <option value="{{ $shiftOption->id }}">
                                            {{ $shiftOption->name }} ({{ \Carbon\Carbon::parse($shiftOption->start_time)->format('H:i') }} - {{ \Carbon\Carbon::parse($shiftOption->end_time)->format('H:i') }})
                                        </option>
                                    @endforeach
                                </select>
                                @error('shift')
                                    <div class="invalid-feedback">{{ $message }}</div>
                                @enderror
                            </div>

                            <!-- Shift Type -->
                            <div class="col-md-3">
                                <label class="form-label">
                                    <i class="fa-solid fa-tag text-primary"></i> Shift Type
                                </label>
                                <select wire:model="shiftType" class="form-select">
                                    <option value="regular">Regular</option>
                                    <option value="overtime">Overtime</option>
                                    <option value="special">Special</option>
                                </select>
                            </div>

                            <!-- Notes -->
                            <div class="col-md-3">
                                <label class="form-label">
                                    <i class="fa-solid fa-note-sticky text-primary"></i> Notes (Optional)
                                </label>
                                <input type="text" wire:model="notes" class="form-control" placeholder="Add notes...">
                            </div>

                            <!-- Generate Button -->
                            <div class="col-12">
                                <button
                                    wire:click="generateRoster"
                                    class="btn btn-success btn-lg w-100"
                                    @if(count($selectedEmployees) === 0) disabled @endif>
                                    <i class="fa-solid fa-plus-circle me-2"></i>
                                    Generate Roster for {{ count($selectedEmployees) }} Selected Employee(s)
                                </button>
                            </div>
                        </div>
                    @else
                        {{-- Auto Generate Mode --}}
                        <div class="row g-3">
                            <!-- Date Range -->
                            <div class="col-md-6">
                                <div class="alert alert-info mb-3">
                                    <i class="fa-solid fa-info-circle me-2"></i>
                                    <strong>Auto Generate:</strong> System will distribute selected employees across dates and shifts based on capacity settings.
                                </div>

                                <div class="row g-2">
                                    <div class="col-6">
                                        <label class="form-label">
                                            <i class="fa-solid fa-calendar-day text-success"></i> Start Date
                                            <span class="text-danger">*</span>
                                        </label>
                                        <input type="text" wire:model="startDate" class="form-control flatpickr @error('startDate') is-invalid @enderror" placeholder="Start Date">
                                        @error('startDate')
                                            <div class="invalid-feedback">{{ $message }}</div>
                                        @enderror
                                    </div>
                                    <div class="col-6">
                                        <label class="form-label">
                                            <i class="fa-solid fa-calendar-check text-success"></i> End Date
                                            <span class="text-danger">*</span>
                                        </label>
                                        <input type="text" wire:model="endDate" class="form-control flatpickr @error('endDate') is-invalid @enderror" placeholder="End Date">
                                        @error('endDate')
                                            <div class="invalid-feedback">{{ $message }}</div>
                                        @enderror
                                    </div>
                                </div>
                            </div>

                            <!-- Shift Capacities -->
                            <div class="col-md-6">
                                <label class="form-label">
                                    <i class="fa-solid fa-users-cog text-success"></i> Shift Capacities (Staff per Shift)
                                    <span class="text-danger">*</span>
                                </label>
                                <div class="border rounded p-3 bg-light">
                                    @foreach($shifts as $shiftOption)
                                        <div class="mb-2">
                                            <div class="d-flex align-items-center justify-content-between">
                                                <label class="form-label mb-0 small">
                                                    <i class="fa-solid fa-clock me-1"></i>
                                                    {{ $shiftOption->name }}
                                                    <span class="text-muted">({{ \Carbon\Carbon::parse($shiftOption->start_time)->format('H:i') }} - {{ \Carbon\Carbon::parse($shiftOption->end_time)->format('H:i') }})</span>
                                                </label>
                                                <input type="number"
                                                       wire:model="shiftCapacities.{{ $shiftOption->id }}"
                                                       class="form-control form-control-sm"
                                                       style="width: 80px;"
                                                       min="0"
                                                       max="{{ count($selectedEmployees) }}"
                                                       placeholder="0">
                                            </div>
                                        </div>
                                    @endforeach
                                    @error('shiftCapacities')
                                        <small class="text-danger">{{ $message }}</small>
                                    @enderror
                                </div>
                            </div>

                            <!-- Distribution Method & Shift Type -->
                            <div class="col-md-6">
                                <label class="form-label">
                                    <i class="fa-solid fa-sitemap text-success"></i> Distribution Method
                                </label>
                                <select wire:model="distributionMethod" class="form-select">
                                    <option value="round_robin">Round Robin (Fair Distribution)</option>
                                    <option value="random">Random Assignment</option>
                                    <option value="balanced">Balanced Load</option>
                                </select>
                            </div>

                            <div class="col-md-6">
                                <label class="form-label">
                                    <i class="fa-solid fa-tag text-success"></i> Shift Type
                                </label>
                                <select wire:model="shiftType" class="form-select">
                                    <option value="regular">Regular</option>
                                    <option value="overtime">Overtime</option>
                                    <option value="special">Special</option>
                                </select>
                            </div>

                            <!-- Summary Card -->
                            @if(count($selectedEmployees) > 0 && $startDate && $endDate)
                                @php
                                    $start = \Carbon\Carbon::parse($startDate);
                                    $end = \Carbon\Carbon::parse($endDate);
                                    $daysDiff = $start->diffInDays($end) + 1;
                                    $totalCapacity = array_sum($shiftCapacities);
                                    $estimatedRosters = $daysDiff * $totalCapacity;
                                @endphp
                                <div class="col-12">
                                    <div class="card bg-success-subtle border-success">
                                        <div class="card-body">
                                            <h6 class="card-title text-success">
                                                <i class="fa-solid fa-chart-line me-2"></i> Generation Summary
                                            </h6>
                                            <div class="row g-3 mt-1">
                                                <div class="col-md-3">
                                                    <div class="text-center">
                                                        <div class="fs-4 fw-bold text-success">{{ count($selectedEmployees) }}</div>
                                                        <div class="small text-muted">Selected Employees</div>
                                                    </div>
                                                </div>
                                                <div class="col-md-3">
                                                    <div class="text-center">
                                                        <div class="fs-4 fw-bold text-success">{{ $daysDiff }}</div>
                                                        <div class="small text-muted">Days</div>
                                                    </div>
                                                </div>
                                                <div class="col-md-3">
                                                    <div class="text-center">
                                                        <div class="fs-4 fw-bold text-success">{{ $totalCapacity }}</div>
                                                        <div class="small text-muted">Staff per Day</div>
                                                    </div>
                                                </div>
                                                <div class="col-md-3">
                                                    <div class="text-center">
                                                        <div class="fs-4 fw-bold text-success">~{{ $estimatedRosters }}</div>
                                                        <div class="small text-muted">Est. Rosters</div>
                                                    </div>
                                                </div>
                                            </div>
                                        </div>
                                    </div>
                                </div>
                            @endif

                            <!-- Auto Generate Button -->
                            <div class="col-12">
                                <button
                                    wire:click="autoGenerateRoster"
                                    class="btn btn-success btn-lg w-100"
                                    @if(count($selectedEmployees) === 0) disabled @endif>
                                    <i class="fa-solid fa-robot me-2"></i>
                                    Auto Generate Roster for {{ count($selectedEmployees) }} Selected Employee(s)
                                </button>
                            </div>
                        </div>
                    @endif
                </div>
            </div>
        </div>
    </div>

    {{-- Employee Selection --}}
    <div class="row">
        <div class="col-12">
            <div class="card card-lg border-0 shadow-sm">
                <!-- Card Header with Filters -->
                <div class="card-header border-bottom bg-white">
                    <div class="row g-3 align-items-center">
                        <!-- Search -->
                        <div class="col-12 col-md-4">
                            <div class="input-group">
                                <span class="input-group-text bg-white">
                                    <i class="fa-solid fa-magnifying-glass"></i>
                                </span>
                                <input type="search" wire:model.live.debounce.300ms="search" class="form-control"
                                    placeholder="Search employees..." />
                                @if($search)
                                    <button wire:click="$set('search', '')" class="btn btn-outline-secondary" type="button">
                                        <i class="fa-solid fa-times"></i>
                                    </button>
                                @endif
                            </div>
                        </div>

                        <!-- Department Filter -->
                        <div class="col-12 col-md-4">
                            <select wire:model.live="department" class="form-select">
                                <option value="">All Departments</option>
                                @foreach($departments as $dept)
                                    <option value="{{ $dept->id }}">{{ $dept->name }}</option>
                                @endforeach
                            </select>
                        </div>

                        <!-- Actions -->
                        <div class="col-12 col-md-4">
                            <div class="d-flex flex-wrap gap-2 justify-content-md-end">
                                <!-- Reset Filters -->
                                @if($search || $department)
                                    <button wire:click="resetFilters" type="button" class="btn btn-outline-secondary">
                                        <i class="fa-solid fa-rotate-left me-1"></i> Reset
                                    </button>
                                @endif

                                <!-- Selection Counter -->
                                @if(count($selectedEmployees) > 0)
                                    <span class="badge bg-primary fs-6 d-flex align-items-center">
                                        {{ count($selectedEmployees) }} Selected
                                    </span>
                                @endif
                            </div>
                        </div>
                    </div>
                </div>

                <!-- Table Section -->
                <div class="table-responsive" style="min-height: 400px;">
                    <table class="table table-hover table-centered align-middle mb-0">
                        <thead class="table-light">
                            <tr>
                                <th style="width: 50px;">
                                    <div class="form-check">
                                        <input
                                            class="form-check-input"
                                            type="checkbox"
                                            wire:model.live="selectAll"
                                            id="selectAll">
                                        <label class="form-check-label" for="selectAll"></label>
                                    </div>
                                </th>
                                <th>#</th>
                                <th>Employee</th>
                                <th>Employee No.</th>
                                <th>Department</th>
                                <th>Designation</th>
                                <th>Status</th>
                            </tr>
                        </thead>
                        <tbody>
                            @forelse($employees as $index => $employee)
                                <tr wire:key="employee-{{ $employee->id }}"
                                    class="{{ in_array($employee->id, $selectedEmployees) ? 'table-active' : '' }}">
                                    <td>
                                        <div class="form-check">
                                            <input
                                                class="form-check-input"
                                                type="checkbox"
                                                wire:model.live="selectedEmployees"
                                                value="{{ $employee->id }}"
                                                id="employee-{{ $employee->id }}">
                                            <label class="form-check-label" for="employee-{{ $employee->id }}"></label>
                                        </div>
                                    </td>
                                    <td class="text-muted">
                                        {{ $employees->firstItem() + $index }}
                                    </td>
                                    <td>
                                        <div class="d-flex align-items-center">
                                            <div class="avatar avatar-sm bg-primary-subtle text-primary rounded-circle me-2">
                                                <span class="avatar-initials">
                                                    {{ substr($employee->first_name, 0, 1) }}{{ substr($employee->last_name, 0, 1) }}
                                                </span>
                                            </div>
                                            <div>
                                                <span class="fw-semibold text-dark">
                                                    {{ $employee->first_name }} {{ $employee->middle_name }} {{ $employee->last_name }}
                                                </span>
                                            </div>
                                        </div>
                                    </td>
                                    <td>
                                        <span class="badge bg-secondary">
                                            {{ $employee->employee_number ?? 'N/A' }}
                                        </span>
                                    </td>
                                    <td>
                                        <span class="badge bg-light text-dark">
                                            {{ $employee->department->name ?? 'N/A' }}
                                        </span>
                                    </td>
                                    <td>
                                        <span class="text-muted">
                                            {{ $employee->designation->name ?? 'N/A' }}
                                        </span>
                                    </td>
                                    <td>
                                        @php
                                            $statusColors = [
                                                'Active' => 'success',
                                                'Suspended' => 'warning',
                                                'Terminated' => 'danger',
                                                'Retired' => 'secondary',
                                            ];
                                            $statusColor = $statusColors[$employee->status] ?? 'secondary';
                                        @endphp
                                        <span class="badge bg-{{ $statusColor }}">{{ $employee->status }}</span>
                                    </td>
                                </tr>
                            @empty
                                <tr>
                                    <td colspan="7" class="text-center py-5">
                                        <div class="d-flex flex-column align-items-center justify-content-center">
                                            <i class="fa-solid fa-users-slash text-muted mb-3" style="font-size: 48px;"></i>
                                            <h5 class="text-muted">No Employees Found</h5>
                                            <p class="text-muted">
                                                @if($search || $department)
                                                    Try adjusting your filters or search query
                                                @else
                                                    No employees available
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
                            @if($employees->total() > 0)
                                Showing {{ $employees->firstItem() }} to {{ $employees->lastItem() }} of
                                {{ $employees->total() }} employees
                            @else
                                No employees found
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
                                {{ $employees->links() }}
                            </div>
                        </div>
                    </div>
                </div>
            </div>
        </div>
    </div>

    <!-- Loading Indicator -->
    <div wire:loading class="position-fixed top-50 start-50 translate-middle" style="z-index: 9999;">
        <div class="spinner-border text-primary" role="status">
            <span class="visually-hidden">Loading...</span>
        </div>
    </div>

    <style>
        .avatar-sm {
            width: 36px;
            height: 36px;
            display: flex;
            align-items: center;
            justify-content: center;
            font-size: 14px;
            font-weight: 600;
        }

        .avatar-initials {
            text-transform: uppercase;
        }

        .table-active {
            background-color: rgba(13, 110, 253, 0.1) !important;
        }

        .form-check-input:checked {
            background-color: #0d6efd;
            border-color: #0d6efd;
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
