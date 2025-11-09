<div class="custom-container">

    <x-pages.breadcrumn title="STAFF LIST" :breadcrumbs="[
        ['label' => 'Home', 'url' => route('dashboard')],
        ['label' => 'HR Overview', 'url' => route('hr.index')],
        ['label' => 'Staff List', 'url' => route('hr.stafflist')],
    ]">
        <a class='btn btn-primary d-md-flex align-items-center gap-2' href="{{ route('hr.addstaff') }}">
            <i class="fa-solid fa-plus"></i> ADD NEW STAFF
        </a>
    </x-pages.breadcrumn>
    <!-- Staff List Card -->
    <div class="row">
        <div class="col-12">
            <div class="card card-lg">
                <!-- Card Header with Search and Filters -->
                <div class="card-header border-bottom">
                    <div class="row g-3 align-items-center">
                        <!-- Search Bar -->
                        <div class="col-12 col-md-4">
                            <div class="input-group">
                                <span class="input-group-text bg-white">
                                    <i class="fa-solid fa-magnifying-glass"></i>
                                </span>
                                <input type="search" wire:model.live.debounce.300ms="search" class="form-control"
                                    placeholder="Search staff by name, employee no, email..." />
                                @if ($search)
                                    <button wire:click="$set('search', '')" class="btn btn-outline-secondary"
                                        type="button">
                                        <i class="fa-solid fa-times"></i>
                                    </button>
                                @endif
                            </div>
                        </div>

                        <!-- Actions -->
                        <div class="col-12 col-md-8">
                            <div class="d-flex flex-wrap gap-2 justify-content-md-end">
                                <!-- Filter Toggle Button -->
                                <button wire:click="toggleFilters" type="button"
                                    class="btn {{ $showFilters ? 'btn-primary' : 'btn-white' }}">
                                    <i class="fa-solid fa-filter me-1"></i>
                                    {{ $showFilters ? 'Hide' : 'Show' }} Filters
                                    @if ($department || $designation || $workstation || $status || $gender || $employmentType)
                                        <span class="badge bg-danger ms-1">
                                            {{ collect([$department, $designation, $workstation, $status, $gender, $employmentType])->filter()->count() }}
                                        </span>
                                    @endif
                                </button>

                                <!-- Reset Filters -->
                                @if ($search || $department || $designation || $workstation || $status || $gender || $employmentType)
                                    <button wire:click="resetFilters" type="button" class="btn btn-outline-secondary">
                                        <i class="fa-solid fa-rotate-left me-1"></i> Reset
                                    </button>
                                @endif

                                <!-- Export Dropdown -->
                                <div class="dropdown">
                                    <button class="btn btn-white dropdown-toggle" type="button"
                                        data-bs-toggle="dropdown" aria-expanded="false">
                                        <i class="fa-solid fa-download me-1"></i> Export
                                    </button>
                                    <ul class="dropdown-menu dropdown-menu-end">
                                        <li><a class="dropdown-item" href="#"><i
                                                    class="fa-solid fa-file-csv me-2"></i>Download as CSV</a></li>
                                        <li><a class="dropdown-item" href="#"><i
                                                    class="fa-solid fa-file-excel me-2"></i>Download as Excel</a></li>
                                        <li><a class="dropdown-item" href="#"><i
                                                    class="fa-solid fa-file-pdf me-2"></i>Download as PDF</a></li>
                                        <li>
                                            <hr class="dropdown-divider">
                                        </li>
                                        <li><a class="dropdown-item" href="#"><i
                                                    class="fa-solid fa-print me-2"></i>Print</a></li>
                                    </ul>
                                </div>
                            </div>
                        </div>
                    </div>

                    <!-- Advanced Filters (Collapsible) -->
                    @if ($showFilters)
                        <div class="row g-3 mt-2 pt-3 border-top">
                            <div class="col-12 col-md-6 col-lg-4">
                                <label class="form-label small text-muted mb-1">Department</label>
                                <select wire:model.live="department" class="form-select">
                                    <option value="">All Departments</option>
                                    @foreach ($departments as $dept)
                                        <option value="{{ $dept->id }}">{{ $dept->name }}</option>
                                    @endforeach
                                </select>
                            </div>

                            <div class="col-12 col-md-6 col-lg-4">
                                <label class="form-label small text-muted mb-1">Designation</label>
                                <select wire:model.live="designation" class="form-select">
                                    <option value="">All Designations</option>
                                    @foreach ($designations as $desig)
                                        <option value="{{ $desig->id }}">{{ $desig->name }}</option>
                                    @endforeach
                                </select>
                            </div>

                            <div class="col-12 col-md-6 col-lg-4">
                                <label class="form-label small text-muted mb-1">Workstation</label>
                                <select wire:model.live="workstation" class="form-select">
                                    <option value="">All Workstations</option>
                                    @foreach ($workstations as $ws)
                                        <option value="{{ $ws->id }}">{{ $ws->workstation_name }}</option>
                                    @endforeach
                                </select>
                            </div>

                            <div class="col-12 col-md-6 col-lg-4">
                                <label class="form-label small text-muted mb-1">Status</label>
                                <select wire:model.live="status" class="form-select">
                                    <option value="">All Statuses</option>
                                    <option value="Active">Active</option>
                                    <option value="Suspended">Suspended</option>
                                    <option value="Terminated">Terminated</option>
                                    <option value="Retired">Retired</option>
                                </select>
                            </div>

                            <div class="col-12 col-md-6 col-lg-4">
                                <label class="form-label small text-muted mb-1">Gender</label>
                                <select wire:model.live="gender" class="form-select">
                                    <option value="">All Genders</option>
                                    <option value="Male">Male</option>
                                    <option value="Female">Female</option>
                                    <option value="Other">Other</option>
                                </select>
                            </div>

                            <div class="col-12 col-md-6 col-lg-4">
                                <label class="form-label small text-muted mb-1">Employment Type</label>
                                <select wire:model.live="employmentType" class="form-select">
                                    <option value="">All Types</option>
                                    <option value="Full-time">Full-time</option>
                                    <option value="Part-time">Part-time</option>
                                    <option value="Contract">Contract</option>
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
                                <th style="width: 70px;">Photo</th>
                                <th>Name</th>
                                <th>Employee No</th>
                                <th>Gender</th>
                                <th>Age</th>
                                <th>Department</th>
                                <th>Designation</th>
                                <th>Phone</th>
                                <th>Status</th>
                                <th class="text-end" style="width: 120px;">Action</th>
                            </tr>
                        </thead>
                        <tbody>
                            @forelse ($employees as $index => $employee)
                                <tr wire:key="employee-{{ $employee->id }}">
                                    <td class="text-center text-muted">
                                        {{ $employees->firstItem() + $index }}
                                    </td>
                                    <td>
                                        @if ($employee->photo)
                                            <img src="{{ asset('storage/' . $employee->photo) }}"
                                                alt="{{ $employee->first_name }}" class="rounded-circle"
                                                width="40" height="40"
                                                style="object-fit: cover;">
                                        @else
                                            <div class="rounded-circle bg-primary text-white d-inline-flex align-items-center justify-content-center"
                                                style="width: 40px; height: 40px; font-size: 14px;">
                                                {{ strtoupper(substr($employee->first_name, 0, 1)) }}{{ strtoupper(substr($employee->last_name, 0, 1)) }}
                                            </div>
                                        @endif
                                    </td>
                                    <td>
                                        <div class="d-flex flex-column">
                                            <span class="fw-semibold text-dark">
                                                {{ $employee->first_name }}
                                                {{ $employee->middle_name }}
                                                {{ $employee->last_name }}
                                            </span>
                                            @if ($employee->email)
                                                <small class="text-muted">{{ $employee->email }}</small>
                                            @endif
                                        </div>
                                    </td>
                                    <td>
                                        <span class="badge bg-light text-dark">{{ $employee->employee_no }}</span>
                                    </td>
                                    <td>
                                        @if ($employee->gender === 'Male')
                                            <i class="fa-solid fa-mars text-primary me-1"></i>
                                        @elseif($employee->gender === 'Female')
                                            <i class="fa-solid fa-venus text-danger me-1"></i>
                                        @endif
                                        {{ $employee->gender }}
                                    </td>
                                    <td>
                                        @if ($employee->dob)
                                            {{ \Carbon\Carbon::parse($employee->dob)->age }} yrs
                                            <small
                                                class="text-muted d-block">{{ \Carbon\Carbon::parse($employee->dob)->format('d/m/Y') }}</small>
                                        @else
                                            <span class="text-muted">-</span>
                                        @endif
                                    </td>
                                    <td>
                                        <span class="text-dark">{{ $employee->department->name ?? '-' }}</span>
                                    </td>
                                    <td>
                                        <span class="text-dark">{{ $employee->designation->name ?? '-' }}</span>
                                    </td>
                                    <td>
                                        @if ($employee->phone)
                                            <a href="tel:{{ $employee->phone }}"
                                                class="text-decoration-none text-dark">
                                                <i class="fa-solid fa-phone me-1"></i>{{ $employee->phone }}
                                            </a>
                                        @else
                                            <span class="text-muted">-</span>
                                        @endif
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
                                    <td>
                                        <div class="d-flex gap-1 justify-content-end">
                                            <a href="{{ route('hr.staffdetails', $employee->id) }}"
                                                class="btn btn-sm btn-ghost-primary rounded-circle"
                                                title="View Details">
                                                <i class="fa-solid fa-eye"></i>
                                            </a>
                                            <a href="{{ route('hr.editstaff', $employee->id) }}"
                                                class="btn btn-sm btn-ghost-secondary rounded-circle" title="Edit">
                                                <i class="fa-solid fa-pen-to-square"></i>
                                            </a>
                                            <button type="button"
                                                class="btn btn-sm btn-ghost-danger rounded-circle"
                                                title="Delete"
                                                onclick="confirm('Are you sure you want to delete this employee?') || event.stopImmediatePropagation()">
                                                <i class="fa-solid fa-trash"></i>
                                            </button>
                                        </div>
                                    </td>
                                </tr>
                            @empty
                                <tr>
                                    <td colspan="11" class="text-center py-5">
                                        <div class="d-flex flex-column align-items-center justify-content-center">
                                            <i class="fa-solid fa-users text-muted mb-3"
                                                style="font-size: 48px;"></i>
                                            <h5 class="text-muted">No Staff Found</h5>
                                            <p class="text-muted">
                                                @if ($search || $department || $designation || $workstation || $status || $gender || $employmentType)
                                                    Try adjusting your filters or search query
                                                @else
                                                    Start by adding new staff members
                                                @endif
                                            </p>
                                            @if (!$search && !$department && !$designation && !$workstation && !$status && !$gender && !$employmentType)
                                                <a href="{{ route('hr.addstaff') }}"
                                                    class="btn btn-primary mt-2">
                                                    <i class="fa-solid fa-plus me-1"></i> Add New Staff
                                                </a>
                                            @endif
                                        </div>
                                    </td>
                                </tr>
                            @endforelse
                        </tbody>
                    </table>
                </div>

                <!-- Card Footer with Pagination -->
                <div class="card-footer border-top">
                    <div class="d-flex flex-column flex-md-row justify-content-between align-items-center gap-3">
                        <!-- Results Info -->
                        <div class="text-muted">
                            @if ($employees->total() > 0)
                                Showing {{ $employees->firstItem() }} to {{ $employees->lastItem() }} of
                                {{ $employees->total() }} staff members
                            @else
                                No staff members found
                            @endif
                        </div>

                        <!-- Pagination and Per Page -->
                        <div class="d-flex flex-column flex-sm-row align-items-center gap-3">
                            <!-- Per Page Selector -->
                            <div class="d-flex align-items-center gap-2">
                                <label class="form-label mb-0 text-nowrap small">Rows per page:</label>
                                <select wire:model.live="perPage" class="form-select form-select-sm"
                                    style="width: auto;">
                                    <option value="5">5</option>
                                    <option value="10">10</option>
                                    <option value="25">25</option>
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
</div>