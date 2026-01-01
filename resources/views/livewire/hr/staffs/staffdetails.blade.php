<div class="container-fluid">
    <!-- Breadcrumb -->
    <x-pages.breadcrumn title="Staff Details" :breadcrumbs="[
        ['label' => 'Home', 'url' => route('dashboard')],
        ['label' => 'Staff List', 'url' => route('hr.stafflist')],
        ['label' => $employee->getFullName()]
    ]">
        <div class="d-flex gap-2">
            <a href="{{ route('hr.stafflist') }}" class="btn btn-outline-secondary">
                <i class="fa-solid fa-arrow-left me-1"></i> Back
            </a>
            <a href="{{ route('hr.editstaff', $employee->id) }}" class="btn btn-primary">
                <i class="fa-solid fa-pen-to-square me-1"></i> Edit
            </a>
        </div>
    </x-pages.breadcrumn>

    <div class="row g-4">
        <!-- Left Column - Profile Card -->
        <div class="col-12 col-lg-4 col-xl-3">
            <!-- Profile Card -->
            <div class="card border-0 shadow-sm mb-4">
                <div class="card-body text-center pt-4">
                    <!-- Profile Photo -->
                    <div class="position-relative d-inline-block mb-3">
                        @if($employee->photo)
                            <img src="{{ asset('storage/' . $employee->photo) }}"
                                 alt="{{ $employee->getFullName() }}"
                                 class="rounded-circle border border-4 border-white shadow"
                                 style="width: 120px; height: 120px; object-fit: cover;">
                        @else
                            <div class="rounded-circle bg-primary d-flex align-items-center justify-content-center shadow"
                                 style="width: 120px; height: 120px;">
                                <span class="text-white fw-bold" style="font-size: 40px;">
                                    {{ strtoupper(substr($employee->first_name, 0, 1) . substr($employee->last_name, 0, 1)) }}
                                </span>
                            </div>
                        @endif
                        <!-- Status Badge -->
                        @php
                            $statusColors = [
                                'Active' => 'success',
                                'Suspended' => 'warning',
                                'Terminated' => 'danger',
                                'Retired' => 'secondary',
                            ];
                            $statusColor = $statusColors[$employee->status] ?? 'secondary';
                        @endphp
                        <span class="position-absolute bottom-0 end-0 badge bg-{{ $statusColor }} rounded-pill px-2">
                            {{ $employee->status }}
                        </span>
                    </div>

                    <!-- Name & Title -->
                    <h4 class="mb-1 fw-bold">{{ $employee->getFullName() }}</h4>
                    <p class="text-muted mb-2">{{ $employee->designation->name ?? 'No Designation' }}</p>

                    <!-- Employee Number Badge -->
                    <span class="badge bg-primary-subtle text-primary-emphasis px-3 py-2 mb-3">
                        <i class="fa-solid fa-id-badge me-1"></i>
                        {{ $employee->employee_no ?? 'N/A' }}
                    </span>

                    <!-- Department -->
                    <div class="d-flex justify-content-center gap-2 mb-3">
                        <span class="badge bg-light text-dark">
                            <i class="fa-solid fa-building me-1"></i>
                            {{ $employee->department->name ?? 'No Department' }}
                        </span>
                    </div>
                </div>

                <!-- Quick Stats -->
                <div class="card-footer bg-light border-0">
                    <div class="row g-0 text-center">
                        <div class="col-4 border-end">
                            <div class="py-2">
                                <div class="fw-bold text-primary fs-6">
                                    {{ $serviceYears }}y {{ $serviceMonths }}m {{ $serviceDays }}d
                                </div>
                                <small class="text-muted">Service</small>
                            </div>
                        </div>
                        <div class="col-4 border-end">
                            <div class="py-2">
                                <div class="fw-bold text-success fs-5">{{ $attendanceRate }}%</div>
                                <small class="text-muted">Attendance</small>
                            </div>
                        </div>
                        <div class="col-4">
                            <div class="py-2">
                                <div class="fw-bold text-info fs-5">{{ $totalLeaves }}</div>
                                <small class="text-muted">Leave Days</small>
                            </div>
                        </div>
                    </div>
                </div>
            </div>

            <!-- Contact Info Card -->
            <div class="card border-0 shadow-sm mb-4">
                <div class="card-header bg-white border-0 py-3">
                    <h6 class="mb-0 fw-bold">
                        <i class="fa-solid fa-address-book text-primary me-2"></i>Contact Information
                    </h6>
                </div>
                <div class="card-body pt-0">
                    <ul class="list-unstyled mb-0">
                        @if($employee->email)
                        <li class="d-flex align-items-center mb-3">
                            <div class="icon-shape icon-sm bg-primary-subtle text-primary rounded me-3">
                                <i class="fa-solid fa-envelope"></i>
                            </div>
                            <div>
                                <small class="text-muted d-block">Email</small>
                                <a href="mailto:{{ $employee->email }}" class="text-decoration-none">{{ $employee->email }}</a>
                            </div>
                        </li>
                        @endif
                        @if($employee->phone)
                        <li class="d-flex align-items-center mb-3">
                            <div class="icon-shape icon-sm bg-success-subtle text-success rounded me-3">
                                <i class="fa-solid fa-phone"></i>
                            </div>
                            <div>
                                <small class="text-muted d-block">Phone</small>
                                <a href="tel:{{ $employee->phone }}" class="text-decoration-none">{{ $employee->phone }}</a>
                            </div>
                        </li>
                        @endif
                        @if($employee->region || $employee->district)
                        <li class="d-flex align-items-center">
                            <div class="icon-shape icon-sm bg-info-subtle text-info rounded me-3">
                                <i class="fa-solid fa-location-dot"></i>
                            </div>
                            <div>
                                <small class="text-muted d-block">Location</small>
                                <span>{{ $employee->district->name ?? '' }}{{ $employee->district && $employee->region ? ', ' : '' }}{{ $employee->region->name ?? '' }}</span>
                            </div>
                        </li>
                        @endif
                    </ul>
                </div>
            </div>

            <!-- Quick Actions Card -->
            <div class="card border-0 shadow-sm">
                <div class="card-header bg-white border-0 py-3">
                    <h6 class="mb-0 fw-bold">
                        <i class="fa-solid fa-bolt text-warning me-2"></i>Quick Actions
                    </h6>
                </div>
                <div class="card-body pt-0">
                    <div class="d-grid gap-2">
                        <a href="{{ route('hr.contracts', $employee->id) }}" class="btn btn-outline-primary btn-sm text-start">
                            <i class="fa-solid fa-file-contract me-2"></i>Manage Contracts
                        </a>
                        <a href="{{ route('hr.salary', $employee->id) }}" class="btn btn-outline-success btn-sm text-start">
                            <i class="fa-solid fa-sack-dollar me-2"></i>View Salary
                        </a>
                        <a href="{{ route('hr.leave', $employee->id) }}" class="btn btn-outline-info btn-sm text-start">
                            <i class="fa-solid fa-calendar-days me-2"></i>Leave Management
                        </a>
                        <a href="{{ route('hr.attendance', $employee->id) }}" class="btn btn-outline-secondary btn-sm text-start">
                            <i class="fa-solid fa-clock me-2"></i>Attendance Records
                        </a>
                    </div>
                </div>
            </div>
        </div>

        <!-- Right Column - Details -->
        <div class="col-12 col-lg-8 col-xl-9">
            <!-- Tab Navigation -->
            <div class="card border-0 shadow-sm mb-4">
                <div class="card-body p-0">
                    <ul class="nav nav-pills nav-fill flex-nowrap overflow-auto px-3 py-2" style="gap: 0.5rem;">
                        <li class="nav-item">
                            <button wire:click="setTab('overview')"
                                    class="nav-link {{ $activeTab === 'overview' ? 'active' : '' }} text-nowrap">
                                <i class="fa-solid fa-user me-1"></i> Overview
                            </button>
                        </li>
                        <li class="nav-item">
                            <button wire:click="setTab('employment')"
                                    class="nav-link {{ $activeTab === 'employment' ? 'active' : '' }} text-nowrap">
                                <i class="fa-solid fa-briefcase me-1"></i> Employment
                            </button>
                        </li>
                        <li class="nav-item">
                            <button wire:click="setTab('contracts')"
                                    class="nav-link {{ $activeTab === 'contracts' ? 'active' : '' }} text-nowrap">
                                <i class="fa-solid fa-file-signature me-1"></i> Contracts
                            </button>
                        </li>
                        <li class="nav-item">
                            <button wire:click="setTab('qualifications')"
                                    class="nav-link {{ $activeTab === 'qualifications' ? 'active' : '' }} text-nowrap">
                                <i class="fa-solid fa-graduation-cap me-1"></i> Qualifications
                            </button>
                        </li>
                        <li class="nav-item">
                            <button wire:click="setTab('dependants')"
                                    class="nav-link {{ $activeTab === 'dependants' ? 'active' : '' }} text-nowrap">
                                <i class="fa-solid fa-users me-1"></i> Dependants
                            </button>
                        </li>
                        <li class="nav-item">
                            <button wire:click="setTab('leaves')"
                                    class="nav-link {{ $activeTab === 'leaves' ? 'active' : '' }} text-nowrap">
                                <i class="fa-solid fa-plane-departure me-1"></i> Leaves
                            </button>
                        </li>
                    </ul>
                </div>
            </div>

            <!-- Tab Content -->
            <div class="tab-content">
                <!-- Overview Tab -->
                @if($activeTab === 'overview')
                <div class="row g-4">
                    <!-- Personal Information -->
                    <div class="col-12 col-xl-6">
                        <div class="card border-0 shadow-sm h-100">
                            <div class="card-header bg-white border-0 py-3">
                                <h6 class="mb-0 fw-bold">
                                    <i class="fa-solid fa-user-circle text-primary me-2"></i>Personal Information
                                </h6>
                            </div>
                            <div class="card-body">
                                <div class="row g-3">
                                    <div class="col-6">
                                        <label class="form-label text-muted small mb-1">First Name</label>
                                        <p class="mb-0 fw-medium">{{ $employee->first_name ?? '-' }}</p>
                                    </div>
                                    <div class="col-6">
                                        <label class="form-label text-muted small mb-1">Middle Name</label>
                                        <p class="mb-0 fw-medium">{{ $employee->middle_name ?? '-' }}</p>
                                    </div>
                                    <div class="col-6">
                                        <label class="form-label text-muted small mb-1">Last Name</label>
                                        <p class="mb-0 fw-medium">{{ $employee->last_name ?? '-' }}</p>
                                    </div>
                                    <div class="col-6">
                                        <label class="form-label text-muted small mb-1">Gender</label>
                                        <p class="mb-0 fw-medium">{{ ucfirst($employee->gender ?? '-') }}</p>
                                    </div>
                                    <div class="col-6">
                                        <label class="form-label text-muted small mb-1">Date of Birth</label>
                                        <p class="mb-0 fw-medium">
                                            {{ $employee->dob ? $employee->dob->format('d M Y') : '-' }}
                                        </p>
                                    </div>
                                    <div class="col-6">
                                        <label class="form-label text-muted small mb-1">Age</label>
                                        <p class="mb-0 fw-medium">{{ $ageYears }} years, {{ $ageMonths }} months, {{ $ageDays }} days</p>
                                    </div>
                                    <div class="col-6">
                                        <label class="form-label text-muted small mb-1">Marital Status</label>
                                        <p class="mb-0 fw-medium">{{ ucfirst($employee->marital_status ?? '-') }}</p>
                                    </div>
                                    <div class="col-6">
                                        <label class="form-label text-muted small mb-1">National ID</label>
                                        <p class="mb-0 fw-medium">{{ $employee->national_id ?? '-' }}</p>
                                    </div>
                                </div>
                            </div>
                        </div>
                    </div>

                    <!-- Address Information -->
                    <div class="col-12 col-xl-6">
                        <div class="card border-0 shadow-sm h-100">
                            <div class="card-header bg-white border-0 py-3">
                                <h6 class="mb-0 fw-bold">
                                    <i class="fa-solid fa-map-location-dot text-success me-2"></i>Address Information
                                </h6>
                            </div>
                            <div class="card-body">
                                <div class="row g-3">
                                    <div class="col-6">
                                        <label class="form-label text-muted small mb-1">Country</label>
                                        <p class="mb-0 fw-medium">{{ $employee->country->name ?? '-' }}</p>
                                    </div>
                                    <div class="col-6">
                                        <label class="form-label text-muted small mb-1">Region</label>
                                        <p class="mb-0 fw-medium">{{ $employee->region->name ?? '-' }}</p>
                                    </div>
                                    <div class="col-6">
                                        <label class="form-label text-muted small mb-1">District</label>
                                        <p class="mb-0 fw-medium">{{ $employee->district->name ?? '-' }}</p>
                                    </div>
                                    <div class="col-6">
                                        <label class="form-label text-muted small mb-1">Ward</label>
                                        <p class="mb-0 fw-medium">{{ $employee->ward->name ?? '-' }}</p>
                                    </div>
                                    <div class="col-12">
                                        <label class="form-label text-muted small mb-1">Street/Village</label>
                                        <p class="mb-0 fw-medium">{{ $employee->vilstreet->name ?? '-' }}</p>
                                    </div>
                                </div>
                            </div>
                        </div>
                    </div>

                    <!-- Active Contract Summary -->
                    @if($employee->activeContract)
                    <div class="col-12">
                        <div class="card border-0 shadow-sm bg-primary text-white">
                            <div class="card-body">
                                <div class="d-flex justify-content-between align-items-start mb-3">
                                    <h6 class="mb-0 fw-bold">
                                        <i class="fa-solid fa-file-contract me-2"></i>Active Contract
                                    </h6>
                                    <span class="badge bg-white text-primary">
                                        {{ ucfirst(str_replace('_', ' ', $employee->activeContract->contract_type ?? 'N/A')) }}
                                    </span>
                                </div>
                                <div class="row g-4">
                                    <div class="col-6 col-md-3">
                                        <small class="opacity-75">Start Date</small>
                                        <p class="mb-0 fw-bold">{{ $employee->activeContract->start_date ? \Carbon\Carbon::parse($employee->activeContract->start_date)->format('d M Y') : '-' }}</p>
                                    </div>
                                    <div class="col-6 col-md-3">
                                        <small class="opacity-75">End Date</small>
                                        <p class="mb-0 fw-bold">{{ $employee->activeContract->expire_date ? \Carbon\Carbon::parse($employee->activeContract->expire_date)->format('d M Y') : 'Indefinite' }}</p>
                                    </div>
                                    <div class="col-6 col-md-3">
                                        <small class="opacity-75">Base Salary</small>
                                        <p class="mb-0 fw-bold">{{ format_tzs($employee->activeContract->base_salary ?? 0) }}</p>
                                    </div>
                                    <div class="col-6 col-md-3">
                                        <small class="opacity-75">Position</small>
                                        <p class="mb-0 fw-bold">{{ $employee->activeContract->position->name ?? '-' }}</p>
                                    </div>
                                </div>
                            </div>
                        </div>
                    </div>
                    @endif
                </div>
                @endif

                <!-- Employment Tab -->
                @if($activeTab === 'employment')
                <div class="card border-0 shadow-sm">
                    <div class="card-header bg-white border-0 py-3">
                        <h6 class="mb-0 fw-bold">
                            <i class="fa-solid fa-briefcase text-primary me-2"></i>Employment Details
                        </h6>
                    </div>
                    <div class="card-body">
                        <div class="row g-4">
                            <div class="col-md-6 col-lg-4">
                                <label class="form-label text-muted small mb-1">Employee Number</label>
                                <p class="mb-0 fw-medium">{{ $employee->employee_no ?? '-' }}</p>
                            </div>
                            <div class="col-md-6 col-lg-4">
                                <label class="form-label text-muted small mb-1">Employment Type</label>
                                <p class="mb-0 fw-medium">{{ ucfirst(str_replace('_', ' ', $employee->employment_type ?? '-')) }}</p>
                            </div>
                            <div class="col-md-6 col-lg-4">
                                <label class="form-label text-muted small mb-1">Hire Date</label>
                                <p class="mb-0 fw-medium">{{ $employee->hired_date ? $employee->hired_date->format('d M Y') : '-' }}</p>
                            </div>
                            <div class="col-md-6 col-lg-4">
                                <label class="form-label text-muted small mb-1">Department</label>
                                <p class="mb-0 fw-medium">{{ $employee->department->name ?? '-' }}</p>
                            </div>
                            <div class="col-md-6 col-lg-4">
                                <label class="form-label text-muted small mb-1">Designation</label>
                                <p class="mb-0 fw-medium">{{ $employee->designation->name ?? '-' }}</p>
                            </div>
                            <div class="col-md-6 col-lg-4">
                                <label class="form-label text-muted small mb-1">Job Title</label>
                                <p class="mb-0 fw-medium">{{ $employee->position->name ?? '-' }}</p>
                            </div>
                            <div class="col-md-6 col-lg-4">
                                <label class="form-label text-muted small mb-1">Workstation</label>
                                <p class="mb-0 fw-medium">{{ $employee->workstation->name ?? '-' }}</p>
                            </div>
                            <div class="col-md-6 col-lg-4">
                                <label class="form-label text-muted small mb-1">TIN Number</label>
                                <p class="mb-0 fw-medium">{{ $employee->tin_number ?? '-' }}</p>
                            </div>
                            <div class="col-md-6 col-lg-4">
                                <label class="form-label text-muted small mb-1">Education Level</label>
                                <p class="mb-0 fw-medium">{{ ucfirst(str_replace('_', ' ', $employee->education_level ?? '-')) }}</p>
                            </div>
                            <div class="col-md-6 col-lg-4">
                                <label class="form-label text-muted small mb-1">Status</label>
                                <span class="badge bg-{{ $statusColor }}">{{ $employee->status }}</span>
                            </div>
                            <div class="col-md-6 col-lg-4">
                                <label class="form-label text-muted small mb-1">Length of Service</label>
                                <p class="mb-0 fw-medium">{{ $serviceYears }} years, {{ $serviceMonths }} months, {{ $serviceDays }} days</p>
                            </div>
                        </div>
                    </div>
                </div>
                @endif

                <!-- Contracts Tab -->
                @if($activeTab === 'contracts')
                <div class="card border-0 shadow-sm">
                    <div class="card-header bg-white border-0 py-3 d-flex justify-content-between align-items-center">
                        <h6 class="mb-0 fw-bold">
                            <i class="fa-solid fa-file-signature text-primary me-2"></i>Contract History
                        </h6>
                        <a href="{{ route('hr.contracts', $employee->id) }}" class="btn btn-sm btn-primary">
                            <i class="fa-solid fa-plus me-1"></i> Manage
                        </a>
                    </div>
                    <div class="card-body p-0">
                        @if(count($contracts) > 0)
                        <div class="table-responsive">
                            <table class="table table-hover mb-0">
                                <thead class="table-light">
                                    <tr>
                                        <th>Contract Type</th>
                                        <th>Position</th>
                                        <th>Start Date</th>
                                        <th>End Date</th>
                                        <th>Base Salary</th>
                                        <th>Status</th>
                                    </tr>
                                </thead>
                                <tbody>
                                    @foreach($contracts as $contract)
                                    <tr>
                                        <td>{{ ucfirst(str_replace('_', ' ', $contract->contract_type ?? '-')) }}</td>
                                        <td>{{ $contract->position->name ?? '-' }}</td>
                                        <td>{{ $contract->start_date ? \Carbon\Carbon::parse($contract->start_date)->format('d M Y') : '-' }}</td>
                                        <td>{{ $contract->expire_date ? \Carbon\Carbon::parse($contract->expire_date)->format('d M Y') : 'Indefinite' }}</td>
                                        <td class="fw-bold text-success">{{ format_tzs($contract->base_salary ?? 0) }}</td>
                                        <td>
                                            <span class="badge bg-{{ $contract->status === 'active' ? 'success' : 'secondary' }}">
                                                {{ ucfirst($contract->status ?? 'Unknown') }}
                                            </span>
                                        </td>
                                    </tr>
                                    @endforeach
                                </tbody>
                            </table>
                        </div>
                        @else
                        <div class="text-center py-5">
                            <i class="fa-solid fa-file-circle-xmark text-muted fa-3x mb-3"></i>
                            <p class="text-muted mb-0">No contracts found</p>
                        </div>
                        @endif
                    </div>
                </div>
                @endif

                <!-- Qualifications Tab -->
                @if($activeTab === 'qualifications')
                <div class="card border-0 shadow-sm">
                    <div class="card-header bg-white border-0 py-3 d-flex justify-content-between align-items-center">
                        <h6 class="mb-0 fw-bold">
                            <i class="fa-solid fa-graduation-cap text-primary me-2"></i>Qualifications
                        </h6>
                        <a href="{{ route('hr.qualifications', $employee->id) }}" class="btn btn-sm btn-primary">
                            <i class="fa-solid fa-plus me-1"></i> Manage
                        </a>
                    </div>
                    <div class="card-body p-0">
                        @if(count($qualifications) > 0)
                        <div class="table-responsive">
                            <table class="table table-hover mb-0">
                                <thead class="table-light">
                                    <tr>
                                        <th>Education Level</th>
                                        <th>Institution</th>
                                        <th>Period</th>
                                        <th>Comments</th>
                                    </tr>
                                </thead>
                                <tbody>
                                    @foreach($qualifications as $qual)
                                    <tr>
                                        <td>{{ $qual->education_level ?? '-' }}</td>
                                        <td>{{ $qual->institution ?? '-' }}</td>
                                        <td>{{ $qual->start_date ? \Carbon\Carbon::parse($qual->start_date)->format('Y') : '-' }} - {{ $qual->end_date ? \Carbon\Carbon::parse($qual->end_date)->format('Y') : '-' }}</td>
                                        <td>{{ $qual->comments ?? '-' }}</td>
                                    </tr>
                                    @endforeach
                                </tbody>
                            </table>
                        </div>
                        @else
                        <div class="text-center py-5">
                            <i class="fa-solid fa-graduation-cap text-muted fa-3x mb-3"></i>
                            <p class="text-muted mb-0">No qualifications recorded</p>
                        </div>
                        @endif
                    </div>
                </div>
                @endif

                <!-- Dependants Tab -->
                @if($activeTab === 'dependants')
                <div class="card border-0 shadow-sm">
                    <div class="card-header bg-white border-0 py-3 d-flex justify-content-between align-items-center">
                        <h6 class="mb-0 fw-bold">
                            <i class="fa-solid fa-users text-primary me-2"></i>Dependants
                        </h6>
                        <a href="{{ route('hr.dependants', $employee->id) }}" class="btn btn-sm btn-primary">
                            <i class="fa-solid fa-plus me-1"></i> Manage
                        </a>
                    </div>
                    <div class="card-body p-0">
                        @if(count($dependants) > 0)
                        <div class="table-responsive">
                            <table class="table table-hover mb-0">
                                <thead class="table-light">
                                    <tr>
                                        <th>Name</th>
                                        <th>Relationship</th>
                                        <th>Date of Birth</th>
                                        <th>Phone</th>
                                    </tr>
                                </thead>
                                <tbody>
                                    @foreach($dependants as $dep)
                                    <tr>
                                        <td>{{ $dep->name ?? '-' }}</td>
                                        <td>{{ ucfirst($dep->relationship ?? '-') }}</td>
                                        <td>{{ $dep->dob ? \Carbon\Carbon::parse($dep->dob)->format('d M Y') : '-' }}</td>
                                        <td>{{ $dep->phone ?? '-' }}</td>
                                    </tr>
                                    @endforeach
                                </tbody>
                            </table>
                        </div>
                        @else
                        <div class="text-center py-5">
                            <i class="fa-solid fa-user-group text-muted fa-3x mb-3"></i>
                            <p class="text-muted mb-0">No dependants recorded</p>
                        </div>
                        @endif
                    </div>
                </div>
                @endif

                <!-- Leaves Tab -->
                @if($activeTab === 'leaves')
                <div class="card border-0 shadow-sm">
                    <div class="card-header bg-white border-0 py-3 d-flex justify-content-between align-items-center">
                        <h6 class="mb-0 fw-bold">
                            <i class="fa-solid fa-plane-departure text-primary me-2"></i>Recent Leaves
                        </h6>
                        <a href="{{ route('hr.leave', $employee->id) }}" class="btn btn-sm btn-primary">
                            <i class="fa-solid fa-plus me-1"></i> Manage
                        </a>
                    </div>
                    <div class="card-body p-0">
                        @if(count($recentLeaves) > 0)
                        <div class="table-responsive">
                            <table class="table table-hover mb-0">
                                <thead class="table-light">
                                    <tr>
                                        <th>Leave Type</th>
                                        <th>Start Date</th>
                                        <th>End Date</th>
                                        <th>Days</th>
                                        <th>Status</th>
                                    </tr>
                                </thead>
                                <tbody>
                                    @foreach($recentLeaves as $leave)
                                    <tr>
                                        <td>{{ $leave->leave->name ?? '-' }}</td>
                                        <td>{{ $leave->start_date ? \Carbon\Carbon::parse($leave->start_date)->format('d M Y') : '-' }}</td>
                                        <td>{{ $leave->end_date ? \Carbon\Carbon::parse($leave->end_date)->format('d M Y') : '-' }}</td>
                                        <td>{{ $leave->days ?? 0 }}</td>
                                        <td>
                                            @php
                                                $leaveStatusColors = [
                                                    'approved' => 'success',
                                                    'pending' => 'warning',
                                                    'Awaiting' => 'warning',
                                                    'rejected' => 'danger',
                                                ];
                                                $leaveColor = $leaveStatusColors[strtolower($leave->status)] ?? 'secondary';
                                            @endphp
                                            <span class="badge bg-{{ $leaveColor }}">{{ ucfirst($leave->status) }}</span>
                                        </td>
                                    </tr>
                                    @endforeach
                                </tbody>
                            </table>
                        </div>
                        @else
                        <div class="text-center py-5">
                            <i class="fa-solid fa-calendar-check text-muted fa-3x mb-3"></i>
                            <p class="text-muted mb-0">No leave records found</p>
                        </div>
                        @endif
                    </div>
                </div>
                @endif
            </div>
        </div>
    </div>

    <style>
        .icon-shape {
            display: inline-flex;
            align-items: center;
            justify-content: center;
            width: 2.5rem;
            height: 2.5rem;
        }
        .icon-shape.icon-sm {
            width: 2rem;
            height: 2rem;
            font-size: 0.875rem;
        }
        .nav-pills .nav-link {
            border-radius: 0.5rem;
            padding: 0.5rem 1rem;
            font-size: 0.875rem;
            color: #6c757d;
        }
        .nav-pills .nav-link.active {
            background-color: #0d6efd;
            color: white;
        }
        .nav-pills .nav-link:hover:not(.active) {
            background-color: #f8f9fa;
        }
    </style>
</div>
