<div class="custom-container">

    <x-pages.breadcrumn title="HR OVERVIEW" :breadcrumbs="[
        ['label' => 'Home', 'url' => route('dashboard')],
        ['label' => 'Human Resources', 'url' => '#'],
        ['label' => 'Overview', 'url' => route('hr.index')],
    ]">
        <a href="{{ route('hr.addstaff') }}" class='btn btn-primary d-md-flex align-items-center gap-2'>
            <i class="fa-solid fa-plus"></i> ADD NEW EMPLOYEE
        </a>
    </x-pages.breadcrumn>

    <!-- Top Statistics Cards -->
    <div class="row g-3 mb-4">
        <div class="col-12 col-sm-6 col-xl-3">
            <div class="card border-0 shadow-sm">
                <div class="card-body">
                    <div class="d-flex align-items-center justify-content-between">
                        <div>
                            <span class="text-muted small">Total Employees</span>
                            <h2 class="mb-0 mt-2">{{ $totalEmployees }}</h2>
                            <span class="badge {{ $hiringTrend >= 0 ? 'bg-success-subtle text-success' : 'bg-danger-subtle text-danger' }} mt-2">
                                <i class="fa-solid fa-arrow-{{ $hiringTrend >= 0 ? 'up' : 'down' }}"></i>
                                {{ number_format(abs($hiringTrend), 1) }}% this month
                            </span>
                        </div>
                        <div class="icon-shape icon-lg bg-primary-subtle text-primary rounded-circle">
                            <i class="fa-solid fa-users fa-2x"></i>
                        </div>
                    </div>
                </div>
            </div>
        </div>

        <div class="col-12 col-sm-6 col-xl-3">
            <div class="card border-0 shadow-sm">
                <div class="card-body">
                    <div class="d-flex align-items-center justify-content-between">
                        <div>
                            <span class="text-muted small">Departments</span>
                            <h2 class="mb-0 mt-2">{{ $totalDepartments }}</h2>
                            <span class="text-muted small mt-2 d-block">Active units</span>
                        </div>
                        <div class="icon-shape icon-lg bg-info-subtle text-info rounded-circle">
                            <i class="fa-solid fa-sitemap fa-2x"></i>
                        </div>
                    </div>
                </div>
            </div>
        </div>

        <div class="col-12 col-sm-6 col-xl-3">
            <div class="card border-0 shadow-sm">
                <div class="card-body">
                    <div class="d-flex align-items-center justify-content-between">
                        <div>
                            <span class="text-muted small">Contracts Expiring</span>
                            <h2 class="mb-0 mt-2 {{ $contractsExpiring > 0 ? 'text-warning' : '' }}">{{ $contractsExpiring }}</h2>
                            <span class="text-muted small mt-2 d-block">Next 3 months</span>
                        </div>
                        <div class="icon-shape icon-lg bg-warning-subtle text-warning rounded-circle">
                            <i class="fa-solid fa-file-contract fa-2x"></i>
                        </div>
                    </div>
                </div>
            </div>
        </div>

        <div class="col-12 col-sm-6 col-xl-3">
            <div class="card border-0 shadow-sm">
                <div class="card-body">
                    <div class="d-flex align-items-center justify-content-between">
                        <div>
                            <span class="text-muted small">Monthly Payroll</span>
                            <h2 class="mb-0 mt-2">{{ number_format($payrollStats['total_payroll'], 0) }}</h2>
                            <span class="text-muted small mt-2 d-block">TZS</span>
                        </div>
                        <div class="icon-shape icon-lg bg-success-subtle text-success rounded-circle">
                            <i class="fa-solid fa-money-bill-wave fa-2x"></i>
                        </div>
                    </div>
                </div>
            </div>
        </div>
    </div>

    <!-- Main Content Row -->
    <div class="row g-4 mb-4">
        <!-- Left Column - Age Distribution & Education Level -->
        <div class="col-xl-8">

            <!-- Employee Age Distribution Card -->
            <div class="card border-0 shadow-sm mb-4">
                <div class="card-header bg-white border-bottom">
                    <h5 class="mb-0">
                        <i class="fa-solid fa-birthday-cake text-primary me-2"></i>
                        Employee Age Distribution
                    </h5>
                </div>
                <div class="card-body">
                    <div class="border border-dashed rounded-3 p-4 bg-light">
                        <div class="row row-cols-md-5 row-cols-2 gx-1 gy-4">
                            @php
                                $ageColors = [
                                    'Under 25' => 'primary',
                                    '25-34' => 'info',
                                    '35-44' => 'success',
                                    '45-54' => 'warning',
                                    '55+' => 'danger'
                                ];
                                $totalAge = $ageDistribution->sum('count');
                            @endphp

                            @foreach(['Under 25', '25-34', '35-44', '45-54', '55+'] as $ageGroup)
                                @php
                                    $group = $ageDistribution->firstWhere('age_group', $ageGroup);
                                    $count = $group->count ?? 0;
                                    $percentage = $totalAge > 0 ? ($count / $totalAge) * 100 : 0;
                                    $color = $ageColors[$ageGroup];
                                @endphp
                                <div class="col">
                                    <div>
                                        <span class="fs-5 fw-semibold">{{ $count }}</span>
                                        <div class="bg-{{ $color }} my-3" style="height: 12px; border-radius: 6px;"></div>
                                        <div class="d-flex flex-column align-items-center gap-1">
                                            <span class="small fw-semibold">{{ $ageGroup }}</span>
                                            <span class="text-muted small">{{ number_format($percentage, 1) }}%</span>
                                        </div>
                                    </div>
                                </div>
                            @endforeach
                        </div>
                    </div>
                </div>
            </div>

            <!-- Education Level Distribution -->
            <div class="card border-0 shadow-sm mb-4">
                <div class="card-header bg-white border-bottom">
                    <h5 class="mb-0">
                        <i class="fa-solid fa-graduation-cap text-primary me-2"></i>
                        Education Level Distribution
                    </h5>
                </div>
                <div class="card-body">
                    <div class="border border-dashed rounded-3 p-4 bg-light">
                        @php
                            $educationColors = ['primary', 'success', 'info', 'warning', 'danger', 'secondary'];
                            $totalEducation = $educationDistribution->sum('count');
                        @endphp
                        <div class="row row-cols-md-3 row-cols-2 gx-2 gy-4">
                            @foreach($educationDistribution as $index => $education)
                                @php
                                    $percentage = $totalEducation > 0 ? ($education->count / $totalEducation) * 100 : 0;
                                    $color = $educationColors[$index % count($educationColors)];
                                @endphp
                                <div class="col">
                                    <div class="text-center">
                                        <span class="fs-5 fw-semibold">{{ $education->count }}</span>
                                        <div class="progress my-2" style="height: 8px;">
                                            <div class="progress-bar bg-{{ $color }}" role="progressbar" style="width: {{ $percentage }}%"></div>
                                        </div>
                                        <div class="d-flex flex-column align-items-center gap-1">
                                            <span class="small fw-semibold">{{ $education->education_level ?? 'Not Specified' }}</span>
                                            <span class="text-muted small">{{ number_format($percentage, 1) }}%</span>
                                        </div>
                                    </div>
                                </div>
                            @endforeach
                        </div>
                    </div>
                </div>
            </div>

            <!-- Contracts Near Expiry -->
            <div class="card border-0 shadow-sm">
                <div class="card-header bg-white border-bottom">
                    <div class="d-flex justify-content-between align-items-center">
                        <h5 class="mb-0">
                            <i class="fa-solid fa-clock text-warning me-2"></i>
                            Contracts Expiring Soon
                        </h5>
                        <a href="{{ route('hr.stafflist') }}" class="btn btn-sm btn-outline-primary">
                            View All
                        </a>
                    </div>
                </div>
                <div class="card-body p-0">
                    <div data-simplebar style="max-height: 400px;">
                        @forelse($expiringContracts as $contract)
                            <div class="d-flex align-items-center justify-content-between gap-3 border-bottom border-dashed p-3 hover-bg-light">
                                <div class="d-flex align-items-center gap-3">
                                    <div>
                                        <img src="{{ asset('images/avatar/avatar-1.jpg') }}" class="avatar avatar-md rounded-circle" alt="avatar" />
                                    </div>
                                    <div>
                                        <h6 class="mb-0">{{ $contract->employee->user->name ?? 'N/A' }}</h6>
                                        <div class="d-flex align-items-center gap-3 text-muted small">
                                            <span><i class="fa-solid fa-briefcase me-1"></i>{{ $contract->contracttype->type_name ?? 'N/A' }}</span>
                                            <span><i class="fa-solid fa-calendar me-1"></i>{{ \Carbon\Carbon::parse($contract->end_date)->format('M d, Y') }}</span>
                                            <span class="text-warning">
                                                <i class="fa-solid fa-clock me-1"></i>
                                                {{ \Carbon\Carbon::parse($contract->end_date)->diffForHumans() }}
                                            </span>
                                        </div>
                                    </div>
                                </div>
                                <div>
                                    @php
                                        $daysLeft = \Carbon\Carbon::now()->diffInDays(\Carbon\Carbon::parse($contract->end_date), false);
                                        $badgeClass = $daysLeft < 30 ? 'bg-danger-subtle text-danger' : ($daysLeft < 60 ? 'bg-warning-subtle text-warning' : 'bg-info-subtle text-info');
                                    @endphp
                                    <span class="badge {{ $badgeClass }}">
                                        {{ abs($daysLeft) }} days
                                    </span>
                                </div>
                            </div>
                        @empty
                            <div class="text-center py-5">
                                <i class="fa-solid fa-check-circle fa-3x text-success mb-3"></i>
                                <p class="text-muted">No contracts expiring in the next 3 months</p>
                            </div>
                        @endforelse
                    </div>
                </div>
            </div>

        </div>

        <!-- Right Column - Department Stats & Hiring Rate -->
        <div class="col-xl-4">

            <!-- Department Employee Count -->
            <div class="card border-0 shadow-sm mb-4">
                <div class="card-header bg-white border-bottom">
                    <h5 class="mb-0">
                        <i class="fa-solid fa-building text-primary me-2"></i>
                        Employees by Department
                    </h5>
                </div>
                <div class="card-body p-0">
                    <div data-simplebar style="max-height: 400px;">
                        @forelse($departmentCounts as $department)
                            <div class="d-flex align-items-center justify-content-between p-3 border-bottom border-dashed hover-bg-light">
                                <div class="d-flex align-items-center gap-3">
                                    <div class="icon-shape icon-md bg-primary-subtle text-primary rounded-circle">
                                        <i class="fa-solid fa-users"></i>
                                    </div>
                                    <div>
                                        <h6 class="mb-0">{{ $department->name }}</h6>
                                        <span class="text-muted small">{{ $department->employees_count }} {{ Str::plural('employee', $department->employees_count) }}</span>
                                    </div>
                                </div>
                                <div>
                                    <span class="badge bg-primary">{{ $department->employees_count }}</span>
                                </div>
                            </div>
                        @empty
                            <div class="text-center py-5">
                                <i class="fa-solid fa-info-circle fa-3x text-muted mb-3"></i>
                                <p class="text-muted">No departments found</p>
                            </div>
                        @endforelse
                    </div>
                </div>
            </div>

            <!-- Monthly Payroll Breakdown -->
            <div class="card border-0 shadow-sm mb-4">
                <div class="card-header bg-white border-bottom">
                    <h5 class="mb-0">
                        <i class="fa-solid fa-chart-pie text-success me-2"></i>
                        Payroll Breakdown
                    </h5>
                    <p class="text-muted small mb-0">{{ \Carbon\Carbon::now()->format('F Y') }}</p>
                </div>
                <div class="card-body">
                    <div class="mb-4">
                        <div class="d-flex justify-content-between align-items-center mb-2">
                            <span class="text-muted">Base Salaries</span>
                            <span class="fw-semibold">TZS {{ number_format($payrollStats['total_salaries'], 0) }}</span>
                        </div>
                        <div class="progress" style="height: 10px;">
                            @php
                                $salaryPercentage = $payrollStats['total_payroll'] > 0 ? ($payrollStats['total_salaries'] / $payrollStats['total_payroll']) * 100 : 0;
                            @endphp
                            <div class="progress-bar bg-primary" role="progressbar" style="width: {{ $salaryPercentage }}%"></div>
                        </div>
                    </div>

                    <div class="mb-4">
                        <div class="d-flex justify-content-between align-items-center mb-2">
                            <span class="text-muted">Allowances</span>
                            <span class="fw-semibold">TZS {{ number_format($payrollStats['total_allowances'], 0) }}</span>
                        </div>
                        <div class="progress" style="height: 10px;">
                            @php
                                $allowancePercentage = $payrollStats['total_payroll'] > 0 ? ($payrollStats['total_allowances'] / $payrollStats['total_payroll']) * 100 : 0;
                            @endphp
                            <div class="progress-bar bg-success" role="progressbar" style="width: {{ $allowancePercentage }}%"></div>
                        </div>
                    </div>

                    <div class="border-top pt-3">
                        <div class="d-flex justify-content-between align-items-center">
                            <span class="fw-semibold">Total Payroll</span>
                            <span class="fw-bold fs-5 text-success">TZS {{ number_format($payrollStats['total_payroll'], 0) }}</span>
                        </div>
                    </div>

                    <div class="mt-4">
                        <a href="{{ route('payrollgeneration') }}" class="btn btn-primary w-100">
                            <i class="fa-solid fa-money-bill-wave me-2"></i>Generate Payroll
                        </a>
                    </div>
                </div>
            </div>

            <!-- Hiring Rate Card -->
            <div class="card border-0 shadow-sm">
                <div class="card-header bg-white border-bottom">
                    <h5 class="mb-0">
                        <i class="fa-solid fa-user-plus text-info me-2"></i>
                        Hiring Rate (Last 12 Months)
                    </h5>
                </div>
                <div class="card-body">
                    @php
                        $maxHires = $hiringRate->max('count') ?: 1;
                    @endphp
                    @forelse($hiringRate as $month)
                        @php
                            $monthName = \Carbon\Carbon::parse($month->month . '-01')->format('M Y');
                            $percentage = ($month->count / $maxHires) * 100;
                        @endphp
                        <div class="mb-3">
                            <div class="d-flex justify-content-between align-items-center mb-1">
                                <span class="small text-muted">{{ $monthName }}</span>
                                <span class="badge bg-info">{{ $month->count }}</span>
                            </div>
                            <div class="progress" style="height: 8px;">
                                <div class="progress-bar bg-info" role="progressbar" style="width: {{ $percentage }}%"></div>
                            </div>
                        </div>
                    @empty
                        <div class="text-center py-4">
                            <i class="fa-solid fa-chart-line fa-3x text-muted mb-3"></i>
                            <p class="text-muted small">No hiring data available</p>
                        </div>
                    @endforelse
                </div>
            </div>

        </div>
    </div>

    <!-- Leaves Row: Monthly Trend + Recent Leaves -->
    <div class="row g-4 mt-1">
        <!-- Monthly Leave Trend -->
        <div class="col-12 col-lg-8">
            <div class="card border-0 shadow-sm h-100">
                <div class="card-header bg-white border-bottom">
                    <div class="d-flex justify-content-between align-items-center">
                        <h5 class="mb-0">
                            <i class="fa-solid fa-chart-area text-primary me-2"></i>
                            Monthly Leave Trend
                        </h5>
                        <span class="badge bg-info-subtle text-info">{{ $monthlyLeaveTrend['total'] ?? 0 }} approved this year</span>
                    </div>
                </div>
                <div class="card-body">
                    @php
                        $maxLeaves = max($monthlyLeaveTrend['counts'] ?? [0]) ?: 1;
                    @endphp
                    @if(($monthlyLeaveTrend['total'] ?? 0) > 0)
                        @foreach($monthlyLeaveTrend['labels'] as $i => $monthLabel)
                            @php $count = $monthlyLeaveTrend['counts'][$i] ?? 0; @endphp
                            <div class="mb-3">
                                <div class="d-flex justify-content-between align-items-center mb-1">
                                    <span class="small text-muted">{{ $monthLabel }}</span>
                                    <span class="badge bg-primary">{{ $count }}</span>
                                </div>
                                <div class="progress" style="height: 8px;">
                                    <div class="progress-bar bg-primary" role="progressbar"
                                         style="width: {{ ($count / $maxLeaves) * 100 }}%"></div>
                                </div>
                            </div>
                        @endforeach
                    @else
                        <div class="text-center py-4">
                            <i class="fa-solid fa-calendar-xmark fa-3x text-muted mb-3"></i>
                            <p class="text-muted small">No approved leaves this year</p>
                        </div>
                    @endif
                </div>
            </div>
        </div>

        <!-- Recent Leaves -->
        <div class="col-12 col-lg-4">
            <div class="card border-0 shadow-sm h-100">
                <div class="card-header bg-white border-bottom">
                    <h5 class="mb-0">
                        <i class="fa-solid fa-clock-rotate-left text-info me-2"></i>
                        Recent Leaves
                    </h5>
                </div>
                <div class="card-body p-0">
                    @if(count($recentLeaves) > 0)
                        <div class="list-group list-group-flush" data-simplebar style="max-height: 400px;">
                            @foreach($recentLeaves as $leave)
                                <div class="list-group-item border-0 py-2">
                                    <div class="d-flex justify-content-between align-items-start">
                                        <div>
                                            <div class="fw-medium small">{{ $leave['employee'] }}</div>
                                            <small class="text-muted">{{ $leave['type'] }} &bull; {{ $leave['days'] }} days</small>
                                        </div>
                                        <span class="badge bg-{{ $leave['status'] == 'approved' ? 'success' : ($leave['status'] == 'pending' ? 'warning' : 'danger') }}-subtle text-{{ $leave['status'] == 'approved' ? 'success' : ($leave['status'] == 'pending' ? 'warning' : 'danger') }}">
                                            {{ ucfirst($leave['status']) }}
                                        </span>
                                    </div>
                                    <small class="text-muted">{{ $leave['start'] }} - {{ $leave['end'] }}</small>
                                </div>
                            @endforeach
                        </div>
                    @else
                        <div class="text-center py-4">
                            <i class="fa-solid fa-inbox text-muted fa-2x mb-2"></i>
                            <p class="text-muted small mb-0">No recent leaves</p>
                        </div>
                    @endif
                </div>
            </div>
        </div>
    </div>

    <style>
        .icon-shape {
            width: 60px;
            height: 60px;
            display: flex;
            align-items: center;
            justify-content: center;
        }

        .hover-bg-light:hover {
            background-color: rgba(0, 0, 0, 0.02);
            transition: background-color 0.2s ease;
        }

        .card {
            border-radius: 12px;
            transition: transform 0.2s ease, box-shadow 0.2s ease;
        }

        .card:hover {
            transform: translateY(-2px);
            box-shadow: 0 8px 16px rgba(0, 0, 0, 0.1) !important;
        }

        .progress-bar {
            transition: width 0.6s ease;
        }
    </style>

</div>
