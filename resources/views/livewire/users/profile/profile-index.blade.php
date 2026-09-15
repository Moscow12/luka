<div>
    <div class="custom-container">
        <div class="row">
            <div class="col-lg-12 col-md-12 col-12">
                <!-- Page header -->
                <div class="mb-5">
                    <h1 class="mb-2 h2">My Profile</h1>
                    <nav aria-label="breadcrumb">
                        <ol class="breadcrumb">
                            <li class="breadcrumb-item"><a href="{{ route('dashboard') }}">Dashboard</a></li>
                            <li class="breadcrumb-item active" aria-current="page">Profile</li>
                        </ol>
                    </nav>
                </div>
            </div>
        </div>

        @if(!$employee)
            <!-- No Employee Record Found -->
            <div class="row">
                <div class="col-12">
                    <div class="alert alert-warning d-flex align-items-center" role="alert">
                        <i class="fas fa-exclamation-triangle me-2"></i>
                        <div>
                            <strong>Employee Record Not Found!</strong><br>
                            Your account is not linked to an employee record. Please contact HR to set up your employee profile.
                        </div>
                    </div>
                </div>
            </div>
        @else
            <!-- Profile Header Card -->
            <div class="row mb-4">
                <div class="col-12">
                    <div class="card shadow-sm">
                        <!-- Cover Image -->
                        <div class="pt-10 rounded-top position-relative" style="background: linear-gradient(135deg, #667eea 0%, #764ba2 100%);">
                        </div>

                        <!-- Profile Info -->
                        <div class="card-body">
                            <div class="d-flex flex-column flex-lg-row gap-4">
                                <!-- Profile Photo -->
                                <div class="position-relative" style="margin-top: -60px;">
                                    @if($employee->photo)
                                        <img src="{{ asset('storage/' . $employee->photo) }}"
                                             alt="{{ $employee->getFullName() }}"
                                             class="rounded-circle border border-4 border-white shadow-sm"
                                             style="width: 120px; height: 120px; object-fit: cover;">
                                    @else
                                        <div class="rounded-circle border border-4 border-white shadow-sm d-flex align-items-center justify-content-center bg-primary text-white"
                                             style="width: 120px; height: 120px; font-size: 48px; font-weight: bold;">
                                            {{ substr($employee->first_name, 0, 1) }}{{ substr($employee->last_name, 0, 1) }}
                                        </div>
                                    @endif
                                </div>

                                <!-- Employee Details -->
                                <div class="flex-grow-1">
                                    <div class="d-flex flex-column flex-lg-row justify-content-between align-items-start">
                                        <div>
                                            <h3 class="mb-1">{{ $employee->getFullName() }}</h3>
                                            <p class="text-muted mb-2">
                                                <i class="fas fa-briefcase me-2"></i>
                                                {{ $employee->position->name ?? 'N/A' }}
                                                @if($employee->department)
                                                    <span class="mx-2">|</span>
                                                    <i class="fas fa-building me-2"></i>
                                                    {{ $employee->department->name }}
                                                @endif
                                            </p>
                                            <p class="text-muted mb-0">
                                                <i class="fas fa-id-badge me-2"></i>
                                                Employee #{{ $employee->employee_no }}
                                                <span class="mx-2">|</span>
                                                <i class="fas fa-calendar me-2"></i>
                                                Hired: {{ $employee->hired_date ? $employee->hired_date->format('M d, Y') : 'N/A' }}
                                            </p>
                                        </div>
                                        <div class="mt-3 mt-lg-0">
                                            <span class="badge bg-{{ $employee->status === 'active' ? 'success' : 'secondary' }} px-3 py-2">
                                                <i class="fas fa-circle me-1" style="font-size: 8px;"></i>
                                                {{ ucfirst($employee->status ?? 'N/A') }}
                                            </span>
                                        </div>
                                    </div>
                                </div>
                            </div>
                        </div>
                    </div>
                </div>
            </div>

            <!-- Quick Stats Cards -->
            <div class="row g-3 mb-4">
                <!-- Leave Statistics -->
                <div class="col-xl-3 col-lg-6 col-md-6 col-12">
                    <div class="card shadow-sm h-100">
                        <div class="card-body">
                            <div class="d-flex align-items-center justify-content-between mb-3">
                                <div class="icon-shape icon-md bg-primary-soft rounded-3">
                                    <i class="fas fa-umbrella-beach text-primary fs-5"></i>
                                </div>
                                <span class="badge bg-primary-soft text-primary">{{ $stats['pending_leaves'] }} Pending</span>
                            </div>
                            <h3 class="mb-1">{{ $stats['total_leaves'] }}</h3>
                            <p class="text-muted mb-0 small">Total Leave Requests</p>
                        </div>
                    </div>
                </div>

                <!-- Roster Statistics -->
                <div class="col-xl-3 col-lg-6 col-md-6 col-12">
                    <div class="card shadow-sm h-100">
                        <div class="card-body">
                            <div class="d-flex align-items-center justify-content-between mb-3">
                                <div class="icon-shape icon-md bg-success-soft rounded-3">
                                    <i class="fas fa-calendar-alt text-success fs-5"></i>
                                </div>
                                <span class="badge bg-success-soft text-success">{{ $stats['upcoming_rosters'] }} Upcoming</span>
                            </div>
                            <h3 class="mb-1">{{ $stats['total_rosters'] }}</h3>
                            <p class="text-muted mb-0 small">Roster Assignments</p>
                        </div>
                    </div>
                </div>

                <!-- Activities Statistics -->
                <div class="col-xl-3 col-lg-6 col-md-6 col-12">
                    <div class="card shadow-sm h-100">
                        <div class="card-body">
                            <div class="d-flex align-items-center justify-content-between mb-3">
                                <div class="icon-shape icon-md bg-warning-soft rounded-3">
                                    <i class="fas fa-tasks text-warning fs-5"></i>
                                </div>
                            </div>
                            <h3 class="mb-1">{{ $stats['total_activities'] }}</h3>
                            <p class="text-muted mb-0 small">CHOP Activities</p>
                        </div>
                    </div>
                </div>

                <!-- Attendance Statistics -->
                <div class="col-xl-3 col-lg-6 col-md-6 col-12">
                    <div class="card shadow-sm h-100">
                        <div class="card-body">
                            <div class="d-flex align-items-center justify-content-between mb-3">
                                <div class="icon-shape icon-md bg-info-soft rounded-3">
                                    <i class="fas fa-clock text-info fs-5"></i>
                                </div>
                                <span class="badge bg-info-soft text-info">{{ $stats['attendance_rate'] }}%</span>
                            </div>
                            <h3 class="mb-1">{{ $stats['total_attendances'] }}</h3>
                            <p class="text-muted mb-0 small">Attendance Records</p>
                        </div>
                    </div>
                </div>
            </div>

            <!-- Tabbed Content -->
            <div class="row">
                <div class="col-12">
                    <!-- Nav Tabs -->
                    <ul class="nav nav-tabs nav-lb-tab border-bottom mb-4" role="tablist">
                        <li class="nav-item">
                            <a class="nav-link {{ $activeTab === 'personal' ? 'active' : '' }}"
                               href="#"
                               wire:click.prevent="switchTab('personal')"
                               role="tab">
                                <div class="d-flex align-items-center gap-2">
                                    <i class="fas fa-user"></i>
                                    <span>Personal Info</span>
                                </div>
                            </a>
                        </li>
                        <li class="nav-item">
                            <a class="nav-link {{ $activeTab === 'leaves' ? 'active' : '' }}"
                               href="#"
                               wire:click.prevent="switchTab('leaves')"
                               role="tab">
                                <div class="d-flex align-items-center gap-2">
                                    <i class="fas fa-umbrella-beach"></i>
                                    <span>Leave Requests</span>
                                </div>
                            </a>
                        </li>
                        <li class="nav-item">
                            <a class="nav-link {{ $activeTab === 'roster' ? 'active' : '' }}"
                               href="#"
                               wire:click.prevent="switchTab('roster')"
                               role="tab">
                                <div class="d-flex align-items-center gap-2">
                                    <i class="fas fa-calendar-alt"></i>
                                    <span>My Roster</span>
                                </div>
                            </a>
                        </li>
                        <li class="nav-item">
                            <a class="nav-link {{ $activeTab === 'activities' ? 'active' : '' }}"
                               href="#"
                               wire:click.prevent="switchTab('activities')"
                               role="tab">
                                <div class="d-flex align-items-center gap-2">
                                    <i class="fas fa-tasks"></i>
                                    <span>My Activities</span>
                                </div>
                            </a>
                        </li>
                        <li class="nav-item">
                            <a class="nav-link {{ $activeTab === 'attendance' ? 'active' : '' }}"
                               href="#"
                               wire:click.prevent="switchTab('attendance')"
                               role="tab">
                                <div class="d-flex align-items-center gap-2">
                                    <i class="fas fa-clock"></i>
                                    <span>Attendance Log</span>
                                </div>
                            </a>
                        </li>
                        <li class="nav-item">
                            <a class="nav-link {{ $activeTab === 'signature' ? 'active' : '' }}"
                               href="#"
                               wire:click.prevent="switchTab('signature')"
                               role="tab">
                                <div class="d-flex align-items-center gap-2">
                                    <i class="fas fa-signature"></i>
                                    <span>Digital Signature</span>
                                </div>
                            </a>
                        </li>
                    </ul>

                    <!-- Tab Content -->
                    <div class="tab-content">
                        <!-- Personal Information Tab -->
                        @if($activeTab === 'personal')
                            <div class="card shadow-sm">
                                <div class="card-header bg-white">
                                    <h5 class="mb-0">Personal Information</h5>
                                </div>
                                <div class="card-body">
                                    <div class="row g-4">
                                        <!-- Basic Information -->
                                        <div class="col-md-6">
                                            <h6 class="text-primary mb-3"><i class="fas fa-info-circle me-2"></i>Basic Information</h6>
                                            <table class="table table-borderless">
                                                <tbody>
                                                    <tr>
                                                        <td class="text-muted" style="width: 150px;">Full Name:</td>
                                                        <td class="fw-semibold">{{ $employee->getFullName() }}</td>
                                                    </tr>
                                                    <tr>
                                                        <td class="text-muted">Gender:</td>
                                                        <td class="fw-semibold">{{ ucfirst($employee->gender ?? 'N/A') }}</td>
                                                    </tr>
                                                    <tr>
                                                        <td class="text-muted">Date of Birth:</td>
                                                        <td class="fw-semibold">{{ $employee->dob ? $employee->dob->format('M d, Y') : 'N/A' }}</td>
                                                    </tr>
                                                    <tr>
                                                        <td class="text-muted">Age:</td>
                                                        <td class="fw-semibold">{{ $employee->getAgeAttribute() }}</td>
                                                    </tr>
                                                    <tr>
                                                        <td class="text-muted">Marital Status:</td>
                                                        <td class="fw-semibold">{{ ucfirst($employee->marital_status ?? 'N/A') }}</td>
                                                    </tr>
                                                    <tr>
                                                        <td class="text-muted">National ID:</td>
                                                        <td class="fw-semibold">{{ $employee->national_id ?? 'N/A' }}</td>
                                                    </tr>
                                                    <tr>
                                                        <td class="text-muted">TIN Number:</td>
                                                        <td class="fw-semibold">{{ $employee->tin_number ?? 'N/A' }}</td>
                                                    </tr>
                                                </tbody>
                                            </table>
                                        </div>

                                        <!-- Contact Information -->
                                        <div class="col-md-6">
                                            <h6 class="text-primary mb-3"><i class="fas fa-address-book me-2"></i>Contact Information</h6>
                                            <table class="table table-borderless">
                                                <tbody>
                                                    <tr>
                                                        <td class="text-muted" style="width: 150px;">Email:</td>
                                                        <td class="fw-semibold">{{ $employee->email ?? 'N/A' }}</td>
                                                    </tr>
                                                    <tr>
                                                        <td class="text-muted">Phone:</td>
                                                        <td class="fw-semibold">{{ $employee->phone ?? 'N/A' }}</td>
                                                    </tr>
                                                    <tr>
                                                        <td class="text-muted">Country:</td>
                                                        <td class="fw-semibold">{{ $employee->country->name ?? 'N/A' }}</td>
                                                    </tr>
                                                    <tr>
                                                        <td class="text-muted">Region:</td>
                                                        <td class="fw-semibold">{{ $employee->region->name ?? 'N/A' }}</td>
                                                    </tr>
                                                    <tr>
                                                        <td class="text-muted">District:</td>
                                                        <td class="fw-semibold">{{ $employee->district->name ?? 'N/A' }}</td>
                                                    </tr>
                                                    <tr>
                                                        <td class="text-muted">Ward:</td>
                                                        <td class="fw-semibold">{{ $employee->ward->name ?? 'N/A' }}</td>
                                                    </tr>
                                                    <tr>
                                                        <td class="text-muted">Street/Village:</td>
                                                        <td class="fw-semibold">{{ $employee->vilstreet->name ?? 'N/A' }}</td>
                                                    </tr>
                                                </tbody>
                                            </table>
                                        </div>

                                        <!-- Employment Information -->
                                        <div class="col-md-6">
                                            <h6 class="text-primary mb-3"><i class="fas fa-briefcase me-2"></i>Employment Information</h6>
                                            <table class="table table-borderless">
                                                <tbody>
                                                    <tr>
                                                        <td class="text-muted" style="width: 150px;">Employee No:</td>
                                                        <td class="fw-semibold">{{ $employee->employee_no ?? 'N/A' }}</td>
                                                    </tr>
                                                    <tr>
                                                        <td class="text-muted">Job Title:</td>
                                                        <td class="fw-semibold">{{ $employee->position->name ?? 'N/A' }}</td>
                                                    </tr>
                                                    <tr>
                                                        <td class="text-muted">Department:</td>
                                                        <td class="fw-semibold">{{ $employee->department->name ?? 'N/A' }}</td>
                                                    </tr>
                                                    <tr>
                                                        <td class="text-muted">Designation:</td>
                                                        <td class="fw-semibold">{{ $employee->designation->name ?? 'N/A' }}</td>
                                                    </tr>
                                                    <tr>
                                                        <td class="text-muted">Workstation:</td>
                                                        <td class="fw-semibold">{{ $employee->workstation->name ?? 'N/A' }}</td>
                                                    </tr>
                                                    <tr>
                                                        <td class="text-muted">Employment Type:</td>
                                                        <td class="fw-semibold">{{ ucfirst($employee->employment_type ?? 'N/A') }}</td>
                                                    </tr>
                                                    <tr>
                                                        <td class="text-muted">Hired Date:</td>
                                                        <td class="fw-semibold">{{ $employee->hired_date ? $employee->hired_date->format('M d, Y') : 'N/A' }}</td>
                                                    </tr>
                                                    <tr>
                                                        <td class="text-muted">Years of Service:</td>
                                                        <td class="fw-semibold">{{ $employee->getYearsOfServiceAttribute() }} years</td>
                                                    </tr>
                                                </tbody>
                                            </table>
                                        </div>

                                        <!-- Additional Information -->
                                        <div class="col-md-6">
                                            <h6 class="text-primary mb-3"><i class="fas fa-graduation-cap me-2"></i>Additional Information</h6>
                                            <table class="table table-borderless">
                                                <tbody>
                                                    <tr>
                                                        <td class="text-muted" style="width: 150px;">Education Level:</td>
                                                        <td class="fw-semibold">{{ ucfirst($employee->education_level ?? 'N/A') }}</td>
                                                    </tr>
                                                    <tr>
                                                        <td class="text-muted">Denomination:</td>
                                                        <td class="fw-semibold">{{ $employee->denomination->name ?? 'N/A' }}</td>
                                                    </tr>
                                                    <tr>
                                                        <td class="text-muted">FP ID:</td>
                                                        <td class="fw-semibold">{{ $employee->fpid ?? 'N/A' }}</td>
                                                    </tr>
                                                </tbody>
                                            </table>

                                            <!-- Password Change Link -->
                                            <div class="mt-4">
                                                <a href="{{ route('user.change-password') }}" class="btn btn-outline-primary">
                                                    <i class="fas fa-key me-2"></i>Change Password
                                                </a>
                                            </div>
                                        </div>
                                    </div>
                                </div>
                            </div>
                        @endif

                        <!-- Leave Requests Tab -->
                        @if($activeTab === 'leaves')
                            <div class="card shadow-sm">
                                <div class="card-header bg-white d-flex justify-content-between align-items-center">
                                    <h5 class="mb-0">Leave Requests</h5>
                                    <span class="badge bg-primary">{{ $stats['total_leaves'] }} Total</span>
                                </div>
                                <div class="card-body">
                                    @if($leaveRequests->count() > 0)
                                        <div class="table-responsive">
                                            <table class="table table-hover">
                                                <thead class="table-light">
                                                    <tr>
                                                        <th>Leave Type</th>
                                                        <th>Start Date</th>
                                                        <th>End Date</th>
                                                        <th>Days</th>
                                                        <th>Status</th>
                                                        <th>Approved By</th>
                                                        <th>Applied On</th>
                                                    </tr>
                                                </thead>
                                                <tbody>
                                                    @foreach($leaveRequests as $leave)
                                                        <tr>
                                                            <td class="fw-semibold">{{ $leave->leave->name ?? 'N/A' }}</td>
                                                            <td>{{ $leave->start_date ? date('M d, Y', strtotime($leave->start_date)) : 'N/A' }}</td>
                                                            <td>{{ $leave->end_date ? date('M d, Y', strtotime($leave->end_date)) : 'N/A' }}</td>
                                                            <td><span class="badge bg-info-soft text-info">{{ $leave->days }} days</span></td>
                                                            <td>
                                                                @if($leave->status === 'approved')
                                                                    <span class="badge bg-success"><i class="fas fa-check me-1"></i>Approved</span>
                                                                @elseif($leave->status === 'rejected')
                                                                    <span class="badge bg-danger"><i class="fas fa-times me-1"></i>Rejected</span>
                                                                @elseif($leave->status === 'pending')
                                                                    <span class="badge bg-warning"><i class="fas fa-clock me-1"></i>Pending</span>
                                                                @else
                                                                    <span class="badge bg-secondary">{{ ucfirst($leave->status) }}</span>
                                                                @endif
                                                            </td>
                                                            <td>{{ $leave->approved_by_user->full_name ?? '-' }}</td>
                                                            <td class="text-muted small">{{ $leave->created_at->format('M d, Y') }}</td>
                                                        </tr>
                                                    @endforeach
                                                </tbody>
                                            </table>
                                        </div>
                                        <div class="mt-3">
                                            {{ $leaveRequests->links() }}
                                        </div>
                                    @else
                                        <div class="text-center py-5">
                                            <i class="fas fa-umbrella-beach text-muted" style="font-size: 48px;"></i>
                                            <p class="text-muted mt-3">No leave requests found.</p>
                                        </div>
                                    @endif
                                </div>
                            </div>
                        @endif

                        <!-- Roster Tab -->
                        @if($activeTab === 'roster')
                            <div class="card shadow-sm">
                                <div class="card-header bg-white d-flex justify-content-between align-items-center">
                                    <h5 class="mb-0">My Roster Assignments</h5>
                                    <span class="badge bg-success">{{ $stats['upcoming_rosters'] }} Upcoming</span>
                                </div>
                                <div class="card-body">
                                    @if($rosterAssignments->count() > 0)
                                        <div class="table-responsive">
                                            <table class="table table-hover">
                                                <thead class="table-light">
                                                    <tr>
                                                        <th>Date</th>
                                                        <th>Shift</th>
                                                        <th>Time</th>
                                                        <th>Department</th>
                                                        <th>Type</th>
                                                        <th>Status</th>
                                                        <th>Notes</th>
                                                    </tr>
                                                </thead>
                                                <tbody>
                                                    @foreach($rosterAssignments as $roster)
                                                        <tr class="{{ $roster->roster_date >= now()->format('Y-m-d') ? 'table-success-soft' : '' }}">
                                                            <td class="fw-semibold">{{ date('M d, Y', strtotime($roster->roster_date)) }}</td>
                                                            <td>{{ $roster->shift->name ?? 'N/A' }}</td>
                                                            <td class="small">
                                                                @if($roster->shift)
                                                                    <i class="fas fa-clock text-primary me-1"></i>
                                                                    {{ date('h:i A', strtotime($roster->shift->start_time)) }} -
                                                                    {{ date('h:i A', strtotime($roster->shift->end_time)) }}
                                                                @else
                                                                    N/A
                                                                @endif
                                                            </td>
                                                            <td>{{ $roster->department->name ?? 'N/A' }}</td>
                                                            <td><span class="badge bg-info-soft text-info">{{ ucfirst($roster->shift_type ?? 'N/A') }}</span></td>
                                                            <td>
                                                                @if($roster->status === 'active')
                                                                    <span class="badge bg-success"><i class="fas fa-check-circle me-1"></i>Active</span>
                                                                @else
                                                                    <span class="badge bg-secondary">{{ ucfirst($roster->status) }}</span>
                                                                @endif
                                                            </td>
                                                            <td class="small text-muted">{{ $roster->notes ?? '-' }}</td>
                                                        </tr>
                                                    @endforeach
                                                </tbody>
                                            </table>
                                        </div>
                                        <div class="mt-3">
                                            {{ $rosterAssignments->links() }}
                                        </div>
                                    @else
                                        <div class="text-center py-5">
                                            <i class="fas fa-calendar-alt text-muted" style="font-size: 48px;"></i>
                                            <p class="text-muted mt-3">No roster assignments found.</p>
                                        </div>
                                    @endif
                                </div>
                            </div>
                        @endif

                        <!-- Activities Tab -->
                        @if($activeTab === 'activities')
                            <div class="card shadow-sm">
                                <div class="card-header bg-white d-flex justify-content-between align-items-center">
                                    <h5 class="mb-0">My CHOP Activities</h5>
                                    <span class="badge bg-warning">{{ $stats['total_activities'] }} Activities</span>
                                </div>
                                <div class="card-body">
                                    @if($chopActivities->count() > 0)
                                        <div class="table-responsive">
                                            <table class="table table-hover">
                                                <thead class="table-light">
                                                    <tr>
                                                        <th>Activity Name</th>
                                                        <th>Description</th>
                                                        <th>Type</th>
                                                        <th>Status</th>
                                                        <th>Planned Amount</th>
                                                        <th>Actual Amount</th>
                                                        <th>Progress</th>
                                                    </tr>
                                                </thead>
                                                <tbody>
                                                    @foreach($chopActivities as $activityPersonel)
                                                        @php
                                                            $activity = $activityPersonel->activity;
                                                        @endphp
                                                        <tr>
                                                            <td class="fw-semibold">{{ $activity->planned_activity ?? 'N/A' }}</td>
                                                            <td class="small">{{ \Illuminate\Support\Str::limit($activity->description ?? '', 50) }}</td>
                                                            <td>
                                                                <span class="badge bg-{{ $activity->activity_type === 'revenue' ? 'success' : 'primary' }}-soft text-{{ $activity->activity_type === 'revenue' ? 'success' : 'primary' }}">
                                                                    {{ ucfirst($activity->activity_type ?? 'N/A') }}
                                                                </span>
                                                            </td>
                                                            <td>
                                                                @if($activity->is_approved)
                                                                    <span class="badge bg-success"><i class="fas fa-check me-1"></i>Approved</span>
                                                                @else
                                                                    <span class="badge bg-warning"><i class="fas fa-clock me-1"></i>Pending</span>
                                                                @endif
                                                            </td>
                                                            <td>{{ number_format($activity->planned_amount ?? 0, 2) }}</td>
                                                            <td>{{ number_format($activity->actual_amount ?? 0, 2) }}</td>
                                                            <td>
                                                                <div class="d-flex align-items-center gap-2">
                                                                    <div class="progress flex-grow-1" style="height: 8px;">
                                                                        <div class="progress-bar bg-success"
                                                                             style="width: {{ min($activity->percentage ?? 0, 100) }}%">
                                                                        </div>
                                                                    </div>
                                                                    <span class="small fw-semibold">{{ round($activity->percentage ?? 0) }}%</span>
                                                                </div>
                                                            </td>
                                                        </tr>
                                                    @endforeach
                                                </tbody>
                                            </table>
                                        </div>
                                        <div class="mt-3">
                                            {{ $chopActivities->links() }}
                                        </div>
                                    @else
                                        <div class="text-center py-5">
                                            <i class="fas fa-tasks text-muted" style="font-size: 48px;"></i>
                                            <p class="text-muted mt-3">No CHOP activities assigned to your job title.</p>
                                        </div>
                                    @endif
                                </div>
                            </div>
                        @endif

                        <!-- Attendance Tab -->
                        @if($activeTab === 'attendance')
                            <div class="card shadow-sm">
                                <div class="card-header bg-white d-flex justify-content-between align-items-center">
                                    <h5 class="mb-0">My Attendance Log</h5>
                                    <div>
                                        <span class="badge bg-info me-2">{{ $stats['attendance_rate'] }}% Rate (Last 30 Days)</span>
                                        <span class="badge bg-primary">{{ $stats['total_attendances'] }} Total</span>
                                    </div>
                                </div>
                                <div class="card-body">
                                    @if($employee->fpid)
                                        @if($attendanceRecords->count() > 0)
                                            <div class="table-responsive">
                                                <table class="table table-hover table-sm">
                                                    <thead class="table-light">
                                                        <tr>
                                                            <th>Date</th>
                                                            <th>Time</th>
                                                            <th>Clock In</th>
                                                            <th>Clock Out</th>
                                                            <th>Status</th>
                                                            <th>Clock Status</th>
                                                        </tr>
                                                    </thead>
                                                    <tbody>
                                                        @foreach($attendanceRecords as $attendance)
                                                            <tr>
                                                                <td class="fw-semibold">{{ $attendance->clockdate ? date('M d, Y', strtotime($attendance->clockdate)) : 'N/A' }}</td>
                                                                <td class="small">{{ $attendance->clocktime ? date('h:i:s A', strtotime($attendance->clocktime)) : 'N/A' }}</td>
                                                                <td>
                                                                    @if($attendance->clock_in)
                                                                        <span class="badge bg-success-soft text-success">
                                                                            <i class="fas fa-sign-in-alt me-1"></i>{{ date('h:i A', strtotime($attendance->clock_in)) }}
                                                                        </span>
                                                                    @else
                                                                        <span class="text-muted">-</span>
                                                                    @endif
                                                                </td>
                                                                <td>
                                                                    @if($attendance->clock_out)
                                                                        <span class="badge bg-danger-soft text-danger">
                                                                            <i class="fas fa-sign-out-alt me-1"></i>{{ date('h:i A', strtotime($attendance->clock_out)) }}
                                                                        </span>
                                                                    @else
                                                                        <span class="text-muted">-</span>
                                                                    @endif
                                                                </td>
                                                                <td>
                                                                    @if($attendance->status === 'present')
                                                                        <span class="badge bg-success"><i class="fas fa-check me-1"></i>Present</span>
                                                                    @elseif($attendance->status === 'absent')
                                                                        <span class="badge bg-danger"><i class="fas fa-times me-1"></i>Absent</span>
                                                                    @elseif($attendance->status === 'late')
                                                                        <span class="badge bg-warning"><i class="fas fa-exclamation me-1"></i>Late</span>
                                                                    @else
                                                                        <span class="badge bg-secondary">{{ ucfirst($attendance->status ?? 'N/A') }}</span>
                                                                    @endif
                                                                </td>
                                                                <td class="small text-muted">{{ $attendance->clock_status ?? '-' }}</td>
                                                            </tr>
                                                        @endforeach
                                                    </tbody>
                                                </table>
                                            </div>
                                            <div class="mt-3">
                                                {{ $attendanceRecords->links() }}
                                            </div>
                                        @else
                                            <div class="text-center py-5">
                                                <i class="fas fa-clock text-muted" style="font-size: 48px;"></i>
                                                <p class="text-muted mt-3">No attendance records found.</p>
                                            </div>
                                        @endif
                                    @else
                                        <div class="alert alert-info" role="alert">
                                            <i class="fas fa-info-circle me-2"></i>
                                            Your fingerprint ID is not registered. Please contact HR to set up your fingerprint.
                                        </div>
                                    @endif
                                </div>
                            </div>
                        @endif

                        <!-- Digital Signature Tab -->
                        @if($activeTab === 'signature')
                            <div class="card shadow-sm">
                                <div class="card-header bg-white">
                                    <h5 class="mb-0">Digital Signature</h5>
                                </div>
                                <div class="card-body">
                                    <div class="row">
                                        <div class="col-md-6 mx-auto">
                                            @if($employee->signature)
                                                <div class="text-center">
                                                    <h6 class="text-muted mb-3">Your Current Signature</h6>
                                                    <div class="border rounded p-4 bg-light">
                                                        <img src="{{ asset('storage/' . $employee->signature) }}"
                                                             alt="Digital Signature"
                                                             class="img-fluid"
                                                             style="max-height: 200px;">
                                                    </div>
                                                    <div class="mt-3">
                                                        <p class="text-muted small mb-0">
                                                            <i class="fas fa-info-circle me-1"></i>
                                                            This signature is used for official documents and approvals.
                                                        </p>
                                                    </div>
                                                </div>
                                            @else
                                                <div class="text-center py-5">
                                                    <i class="fas fa-signature text-muted" style="font-size: 64px;"></i>
                                                    <h6 class="text-muted mt-4 mb-3">No Digital Signature Found</h6>
                                                    <p class="text-muted">
                                                        You don't have a digital signature on file. Please contact HR to upload your signature.
                                                    </p>
                                                    <div class="alert alert-info mt-4 text-start">
                                                        <h6 class="alert-heading">What is a Digital Signature?</h6>
                                                        <p class="small mb-0">
                                                            A digital signature is an electronic version of your handwritten signature
                                                            that is used to authenticate and approve documents within the HR system.
                                                        </p>
                                                    </div>
                                                </div>
                                            @endif
                                        </div>
                                    </div>
                                </div>
                            </div>
                        @endif
                    </div>
                </div>
            </div>
        @endif
    </div>
</div>
