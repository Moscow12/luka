@php
    $user = auth()->user();
    $isSuperAdmin = $user?->isSuperAdmin() ?? false;

    // Check if user has an employee record (for My Performance section)
    $hasEmployeeRecord = $user ? \App\Models\Employee::where('user_id', $user->id)->exists() : false;

    // Check if user can approve performance evaluations
    $canApprovePerformance = false;
    if ($hasEmployeeRecord) {
        $employee = \App\Models\Employee::where('user_id', $user->id)->first();
        if ($employee) {
            $assignedLevels = \App\Models\approvalleveltoemployee::where('employee_id', $employee->id)
                ->where('is_active', true)
                ->pluck('approval_level_id');
            $canApprovePerformance = \App\Models\approvalleveltodocument::where('document_type', 'Performance')
                ->where('is_active', true)
                ->whereIn('approval_level_id', $assignedLevels)
                ->exists();
        }
    }

    // Define route groups for active state detection
    $rosterRoutes = ['viewroster.index', 'roster.create', 'roster.*'];
    $leaveRoutes = ['leave.leavebalance', 'leave.requestleave', 'leave.leaveapproval', 'leave.*'];
    $loanRoutes = ['loan.loanbalance', 'loan.requestloan', 'loan.loanapproval', 'loan.loanpayments', 'loan.items', 'loan.*'];
    $hrRoutes = ['hr.index', 'hr.stafflist', 'hr.addstaff', 'hr.staffdetails', 'hr.*', 'leave.leavemanagement', 'fp.attendance', 'managefpusers', 'fp.devices'];
    $payrollRoutes = ['payrollgeneration', 'allowancepayment', 'paymentreports', 'payroll.*'];
    $myPerformanceRoutes = ['performance.overview', 'performance.myplanning', 'performance.myimplementation', 'performance.myevaluation', 'performance.approve.evaluations', 'performance.myduties'];
    $performanceRoutes = ['performance.org.plans', 'performance.dept.plans', 'performance.employee.plans', 'performance.assigned.duties', 'performance.title.kpis'];
    $contractRoutes = ['contracts.list', 'contracts.create', 'contracts.*'];
    $chopRoutes = ['chop.budget.requests', 'chop.reporting', 'chop.director.review', 'chop.activities', 'chop.cost.analysis', 'chop.monitoring', 'chop.settings', 'chop.*'];
    $setupRoutes = ['setup.index', 'setup.location', 'setup.finances', 'setup.vendors', 'setup.approvalconfig', 'setup.*'];
    $aclRoutes = ['user.management', 'acl.index', 'acl.permissions', 'acl.*'];
@endphp

<ul class="navbar-nav flex-column">
    <!-- Dashboard - Always visible for authenticated users -->
    <li class="nav-item">
        <a class="nav-link {{ request()->routeIs('dashboard') ? 'active' : '' }}" href="{{ route('dashboard') }}">
            <span class="nav-icon">
                <i class="fa-solid fa-gauge-high"></i>
            </span>
            <span class="text">Dashboard</span>
        </a>
    </li>

    <!-- Roster -->
    @if($isSuperAdmin || $user?->canAny(['view-roster', 'create-roster', 'manage-roster']))
    <li class="nav-item dropdown {{ request()->routeIs($rosterRoutes) ? 'show' : '' }}">
        <a class="nav-link dropdown-toggle {{ request()->routeIs($rosterRoutes) ? 'active' : '' }}" href="{{ route('viewroster.index') }}" role="button" data-bs-toggle="dropdown" aria-expanded="{{ request()->routeIs($rosterRoutes) ? 'true' : 'false' }}">
            <span class="nav-icon">
                <i class="fa-solid fa-calendar-days"></i>
            </span>
            <span class="text">Roster</span>
        </a>
        <ul class="dropdown-menu flex-column {{ request()->routeIs($rosterRoutes) ? 'show' : '' }}">
            @if($isSuperAdmin || $user?->can('view-roster'))
            <li class="nav-item">
                <a class="nav-link {{ request()->routeIs('viewroster.index') ? 'active' : '' }}" href="{{ route('viewroster.index') }}"><i class="fa-solid fa-sliders"></i> Overview</a>
            </li>
            @endif
            @if($isSuperAdmin || $user?->can('create-roster'))
            <li class="nav-item">
                <a class="nav-link {{ request()->routeIs('roster.create') ? 'active' : '' }}" href="{{ route('roster.create') }}"><i class="fa-solid fa-calendar-days"></i> Generate Roster</a>
            </li>
            @endif
        </ul>
    </li>
    @endif

    <!-- Leave -->
    @if($isSuperAdmin || $user?->canAny(['view-leave', 'request-leave', 'approve-leave']))
    <li class="nav-item dropdown {{ request()->routeIs($leaveRoutes) ? 'show' : '' }}">
        <a class="nav-link dropdown-toggle {{ request()->routeIs($leaveRoutes) ? 'active' : '' }}" href="{{ route('leave.leavebalance') }}" role="button" data-bs-toggle="dropdown" aria-expanded="{{ request()->routeIs($leaveRoutes) ? 'true' : 'false' }}">
            <span class="nav-icon">
                <i class="fa-solid fa-umbrella-beach"></i>
            </span>
            <span class="text">Leave</span>
        </a>
        <ul class="dropdown-menu flex-column {{ request()->routeIs($leaveRoutes) ? 'show' : '' }}">
            @if($isSuperAdmin || $user?->can('view-leave'))
            <li class="nav-item">
                <a class="nav-link {{ request()->routeIs('leave.leavebalance') ? 'active' : '' }}" href="{{ route('leave.leavebalance') }}"><i class="fa-solid fa-calendar-days"></i> Overview</a>
            </li>
            @endif
            @if($isSuperAdmin || $user?->can('request-leave'))
            <li class="nav-item">
                <a class="nav-link {{ request()->routeIs('leave.requestleave') ? 'active' : '' }}" href="{{ route('leave.requestleave') }}"><i class="fa-solid fa-bed"></i> Request Leave</a>
            </li>
            @endif
            @if($isSuperAdmin || $user?->can('approve-leave'))
            <li class="nav-item">
                <a class="nav-link {{ request()->routeIs('leave.leaveapproval') ? 'active' : '' }}" href="{{ route('leave.leaveapproval') }}"><i class="fa-solid fa-check-double"></i> Leave Approval</a>
            </li>
            @endif
        </ul>
    </li>
    @endif

     <!-- Loan -->
    @if($isSuperAdmin || $user?->canAny(['view-loan', 'request-loan', 'approve-loan']))
    <li class="nav-item dropdown {{ request()->routeIs($loanRoutes) ? 'show' : '' }}">
        <a class="nav-link dropdown-toggle {{ request()->routeIs($loanRoutes) ? 'active' : '' }}" href="{{ route('loan.loanbalance') }}" role="button" data-bs-toggle="dropdown" aria-expanded="{{ request()->routeIs($loanRoutes) ? 'true' : 'false' }}">
            <span class="nav-icon">
                <i class="fa-solid fa-money-bill"></i>
            </span>
            <span class="text">Loan</span>
        </a>
        <ul class="dropdown-menu flex-column {{ request()->routeIs($loanRoutes) ? 'show' : '' }}">
            @if($isSuperAdmin || $user?->can('view-loan'))
            <li class="nav-item">
                <a class="nav-link {{ request()->routeIs('loan.loanbalance') ? 'active' : '' }}" href="{{ route('loan.loanbalance') }}"><i class="fa-solid fa-wallet"></i> My Loan Balance</a>
            </li>
            @endif
            @if($isSuperAdmin || $user?->can('request-loan'))
            <li class="nav-item">
                <a class="nav-link {{ request()->routeIs('loan.requestloan') ? 'active' : '' }}" href="{{ route('loan.requestloan') }}"><i class="fa-solid fa-hand-holding-dollar"></i> Request Loan</a>
            </li>
            @endif
            @if($isSuperAdmin || $user?->can('approve-loan'))
            <li class="nav-item">
                <a class="nav-link {{ request()->routeIs('loan.loanapproval') ? 'active' : '' }}" href="{{ route('loan.loanapproval') }}"><i class="fa-solid fa-check-double"></i> Loan Approval</a>
            </li>
            @endif
        </ul>
    </li>
    @endif

    <!-- My Performance - Available to any logged-in staff with employee record -->
    @if($hasEmployeeRecord)
    <li class="nav-item dropdown {{ request()->routeIs($myPerformanceRoutes) ? 'show' : '' }}">
        <a class="nav-link dropdown-toggle {{ request()->routeIs($myPerformanceRoutes) ? 'active' : '' }}" href="{{ route('performance.overview') }}" role="button" data-bs-toggle="dropdown" aria-expanded="{{ request()->routeIs($myPerformanceRoutes) ? 'true' : 'false' }}">
            <span class="nav-icon">
                <i class="fa-solid fa-chart-line"></i>
            </span>
            <span class="text">My Performance</span>
        </a>
        <ul class="dropdown-menu flex-column {{ request()->routeIs($myPerformanceRoutes) ? 'show' : '' }}">
            <li class="nav-item">
                <a class="nav-link {{ request()->routeIs('performance.overview') ? 'active' : '' }}" href="{{ route('performance.overview') }}"><i class="fa-solid fa-gauge-high me-2"></i>Overview</a>
            </li>
            <li class="nav-item">
                <a class="nav-link {{ request()->routeIs('performance.myplanning') ? 'active' : '' }}" href="{{ route('performance.myplanning') }}"><i class="fa-solid fa-bullseye me-2"></i>My Planning</a>
            </li>
            <li class="nav-item">
                <a class="nav-link {{ request()->routeIs('performance.myimplementation') ? 'active' : '' }}" href="{{ route('performance.myimplementation') }}"><i class="fa-solid fa-tasks me-2"></i>My Implementation</a>
            </li>
            <li class="nav-item">
                <a class="nav-link {{ request()->routeIs('performance.myevaluation') ? 'active' : '' }}" href="{{ route('performance.myevaluation') }}"><i class="fa-solid fa-star me-2"></i>My Evaluation</a>
            </li>
            <li class="nav-item">
                <a class="nav-link {{ request()->routeIs('performance.myduties') ? 'active' : '' }}" href="{{ route('performance.myduties') }}"><i class="fa-solid fa-clipboard-list me-2"></i>My Duties</a>
            </li>
            @if($canApprovePerformance)
            <li class="nav-item">
                <a class="nav-link {{ request()->routeIs('performance.approve.evaluations') ? 'active' : '' }}" href="{{ route('performance.approve.evaluations') }}"><i class="fa-solid fa-check-double me-2"></i>Approve Evaluations</a>
            </li>
            @endif
        </ul>
    </li>
    @endif

    <!-- Human Resources Section Header -->
    @if($isSuperAdmin || $user?->canAny(['view-staff', 'manage-staff', 'view-attendance', 'manage-leave', 'view-payroll', 'manage-payroll', 'view-performance']))
    <li class="nav-item">
        <div class="nav-heading">Human Resource</div>
        <hr class="mx-5 nav-line mb-1" />
    </li>
    @endif

    <!-- Human Resources -->
    @if($isSuperAdmin || $user?->canAny(['view-staff', 'manage-staff', 'view-attendance', 'manage-leave']))
    <li class="nav-item dropdown {{ request()->routeIs($hrRoutes) ? 'show' : '' }}">
        <a class="nav-link dropdown-toggle {{ request()->routeIs($hrRoutes) ? 'active' : '' }}" href="{{ route('hr.index') }}" role="button" data-bs-toggle="dropdown" aria-expanded="{{ request()->routeIs($hrRoutes) ? 'true' : 'false' }}">
            <span class="nav-icon">
                <i class="fa-solid fa-user-group"></i>
            </span>
            <span class="text">Human Resources</span>
        </a>
        <ul class="dropdown-menu flex-column {{ request()->routeIs($hrRoutes) ? 'show' : '' }}">
            @if($isSuperAdmin || $user?->can('view-staff'))
            <li class="nav-item">
                <a class="nav-link {{ request()->routeIs('hr.index') ? 'active' : '' }}" href="{{ route('hr.index') }}"><i class="fa-solid fa-sliders"></i> Overview</a>
            </li>
            @endif
            @if($isSuperAdmin || $user?->can('manage-staff'))
            <li class="nav-item">
                <a class="nav-link {{ request()->routeIs(['hr.stafflist', 'hr.addstaff', 'hr.staffdetails']) ? 'active' : '' }}" href="{{ route('hr.stafflist') }}"><i class="fa-solid fa-users"></i> Staff Registration</a>
            </li>
            @endif
            @if($isSuperAdmin || $user?->can('manage-leave'))
            <li class="nav-item">
                <a class="nav-link {{ request()->routeIs('leave.leavemanagement') ? 'active' : '' }}" href="{{ route('leave.leavemanagement') }}"><i class="fa-solid fa-umbrella-beach"></i> Leave Management</a>
            </li>
            @endif
            @if($isSuperAdmin || $user?->can('view-attendance'))
            <li class="nav-item">
                <a class="nav-link {{ request()->routeIs('fp.attendance') ? 'active' : '' }}" href="{{ route('fp.attendance') }}"><i class="fa-solid fa-clock"></i> Staff Check In/Out</a>
            </li>
            <li class="nav-item">
                <a class="nav-link {{ request()->routeIs('managefpusers') ? 'active' : '' }}" href="{{ route('managefpusers') }}"><i class="fa-solid fa-id-card"></i> Manage FP Users</a>
            </li>
            <li class="nav-item">
                <a class="nav-link {{ request()->routeIs('fp.devices') ? 'active' : '' }}" href="{{ route('fp.devices') }}"><i class="fa-solid fa-desktop"></i> Manage FP Devices</a>
            </li>
            @endif
            @if($isSuperAdmin || $user?->canAny(['view-loan', 'manage-loan']))
            <li class="nav-item">
                <a class="nav-link {{ request()->routeIs('loan.items') ? 'active' : '' }}" href="{{ route('loan.items') }}"><i class="fa-solid fa-list-ul"></i> Loan Types</a>
            </li>
            <li class="nav-item">
                <a class="nav-link {{ request()->routeIs('loan.loanapproval') ? 'active' : '' }}" href="{{ route('loan.loanapproval') }}"><i class="fa-solid fa-check-double"></i> Loan Approval</a>
            </li>
            <li class="nav-item">
                <a class="nav-link {{ request()->routeIs('loan.loanpayments') ? 'active' : '' }}" href="{{ route('loan.loanpayments') }}"><i class="fa-solid fa-money-bill-transfer"></i> Loan Payments</a>
            </li>
            @endif
        </ul>
    </li>
    @endif

    <!-- Staff Payments -->
    @if($isSuperAdmin || $user?->canAny(['view-payroll', 'manage-payroll', 'generate-payroll']))
    <li class="nav-item dropdown {{ request()->routeIs($payrollRoutes) ? 'show' : '' }}">
        <a class="nav-link dropdown-toggle {{ request()->routeIs($payrollRoutes) ? 'active' : '' }}" href="{{ route('payrollgeneration') }}" role="button" data-bs-toggle="dropdown" aria-expanded="{{ request()->routeIs($payrollRoutes) ? 'true' : 'false' }}">
            <span class="nav-icon">
                <i class="fa-solid fa-money-bill"></i>
            </span>
            <span class="text">Staff Payments</span>
        </a>
        <ul class="dropdown-menu flex-column {{ request()->routeIs($payrollRoutes) ? 'show' : '' }}">
            @if($isSuperAdmin || $user?->can('generate-payroll'))
            <li class="nav-item">
                <a class="nav-link {{ request()->routeIs('payrollgeneration') ? 'active' : '' }}" href="{{ route('payrollgeneration') }}"><i class="fa-solid fa-money-bill-wave"></i> Generate Payroll</a>
            </li>
            @endif
            @if($isSuperAdmin || $user?->can('manage-payroll'))
            <li class="nav-item">
                <a class="nav-link {{ request()->routeIs('allowancepayment') ? 'active' : '' }}" href="{{ route('allowancepayment') }}"><i class="fa-solid fa-gift"></i> Allowance Payment</a>
            </li>
            <li class="nav-item">
                <a class="nav-link {{ request()->routeIs('paymentreports') ? 'active' : '' }}" href="{{ route('paymentreports') }}"><i class="fa-solid fa-chart-bar"></i> Payments Report</a>
            </li>
            @endif
        </ul>
    </li>
    @endif

    <!-- Staff Performance -->
    @if($isSuperAdmin || $user?->canAny(['view-performance', 'manage-performance']))
    <li class="nav-item dropdown {{ request()->routeIs($performanceRoutes) ? 'show' : '' }}">
        <a class="nav-link dropdown-toggle {{ request()->routeIs($performanceRoutes) ? 'active' : '' }}" href="{{ route('performance.org.plans') }}" role="button" data-bs-toggle="dropdown" aria-expanded="{{ request()->routeIs($performanceRoutes) ? 'true' : 'false' }}">
            <span class="nav-icon">
                <i class="fa-solid fa-chart-line"></i>
            </span>
            <span class="text">Staff Performance</span>
        </a>
        <ul class="dropdown-menu flex-column {{ request()->routeIs($performanceRoutes) ? 'show' : '' }}">
            @if($isSuperAdmin || $user?->can('view-performance'))
            <li class="nav-item">
                <a class="nav-link {{ request()->routeIs('performance.org.plans') ? 'active' : '' }}" href="{{ route('performance.org.plans') }}"><i class="fa-solid fa-building"></i> Organizational Plans</a>
            </li>
            <li class="nav-item">
                <a class="nav-link {{ request()->routeIs('performance.dept.plans') ? 'active' : '' }}" href="{{ route('performance.dept.plans') }}"><i class="fa-solid fa-sitemap"></i> Department Plans</a>
            </li>
            <li class="nav-item">
                <a class="nav-link {{ request()->routeIs('performance.employee.plans') ? 'active' : '' }}" href="{{ route('performance.employee.plans') }}"><i class="fa-solid fa-user-check"></i> Employee Plans</a>
            </li>
            @endif
            @if($isSuperAdmin || $user?->can('manage-performance'))
            <li class="nav-item">
                <a class="nav-link {{ request()->routeIs('performance.assigned.duties') ? 'active' : '' }}" href="{{ route('performance.assigned.duties') }}"><i class="fa-solid fa-tasks"></i> Assigned Duties</a>
            </li>
            <li class="nav-item">
                <a class="nav-link {{ request()->routeIs('performance.title.kpis') ? 'active' : '' }}" href="{{ route('performance.title.kpis') }}"><i class="fa-solid fa-id-badge"></i> Job Title KPIs</a>
            </li>
            @endif
        </ul>
    </li>
    @endif

    <!-- Management Section Header -->
    @if($isSuperAdmin || $user?->canAny(['view-contracts', 'manage-contracts', 'approve-requests', 'view-chop', 'manage-chop']))
    <li class="nav-item">
        <div class="nav-heading">Management</div>
        <hr class="mx-5 nav-line mb-1" />
    </li>
    @endif

    <!-- Institutional Contracts -->
    @if($isSuperAdmin || $user?->canAny(['view-contracts', 'manage-contracts']))
    <li class="nav-item dropdown {{ request()->routeIs($contractRoutes) ? 'show' : '' }}">
        <a class="nav-link dropdown-toggle {{ request()->routeIs($contractRoutes) ? 'active' : '' }}" href="{{ route('contracts.list') }}" role="button" data-bs-toggle="dropdown" aria-expanded="{{ request()->routeIs($contractRoutes) ? 'true' : 'false' }}">
            <span class="nav-icon">
                <i class="fa-solid fa-file-contract"></i>
            </span>
            <span class="text">Institutional Contracts</span>
        </a>
        <ul class="dropdown-menu flex-column {{ request()->routeIs($contractRoutes) ? 'show' : '' }}">
            @if($isSuperAdmin || $user?->can('view-contracts'))
            <li class="nav-item">
                <a class="nav-link {{ request()->routeIs('contracts.list') ? 'active' : '' }}" href="{{ route('contracts.list') }}"><i class="fa-solid fa-list"></i> All Contracts</a>
            </li>
            @endif
            @if($isSuperAdmin || $user?->can('manage-contracts'))
            <li class="nav-item">
                <a class="nav-link {{ request()->routeIs('contracts.create') ? 'active' : '' }}" href="{{ route('contracts.create') }}"><i class="fa-solid fa-plus-circle"></i> New Contract</a>
            </li>
            @endif
        </ul>
    </li>
    @endif

    <!-- CHOP Management -->
    @if($isSuperAdmin || $user?->canAny(['view-chop', 'manage-chop']))
    <li class="nav-item dropdown {{ request()->routeIs($chopRoutes) ? 'show' : '' }}">
        <a class="nav-link dropdown-toggle {{ request()->routeIs($chopRoutes) ? 'active' : '' }}" href="{{ route('chop.budget.requests') }}" role="button" data-bs-toggle="dropdown" aria-expanded="{{ request()->routeIs($chopRoutes) ? 'true' : 'false' }}">
            <span class="nav-icon">
                <i class="fa-solid fa-file-invoice"></i>
            </span>
            <span class="text">CHOP Management</span>
        </a>
        <ul class="dropdown-menu flex-column {{ request()->routeIs($chopRoutes) ? 'show' : '' }}">
            @if($isSuperAdmin || $user?->can('view-chop'))
            <li class="nav-item">
                <a class="nav-link {{ request()->routeIs('chop.budget.requests') ? 'active' : '' }}" href="{{ route('chop.budget.requests') }}"><i class="fa-solid fa-file-invoice-dollar"></i> My Budget Requests</a>
            </li>
            <li class="nav-item">
                <a class="nav-link {{ request()->routeIs('chop.reporting') ? 'active' : '' }}" href="{{ route('chop.reporting') }}"><i class="fa-solid fa-file-alt"></i> My Activity Reports</a>
            </li>
            @endif
            @if($isSuperAdmin || $user?->can('manage-chop'))
            <li class="nav-item">
                <a class="nav-link {{ request()->routeIs('chop.director.review') ? 'active' : '' }}" href="{{ route('chop.director.review') }}"><i class="fa-solid fa-clipboard-check"></i> Review Requests</a>
            </li>
            <li class="nav-item">
                <a class="nav-link {{ request()->routeIs('chop.activities') ? 'active' : '' }}" href="{{ route('chop.activities') }}"><i class="fa-solid fa-plus-circle"></i> Plan CHOP Activities</a>
            </li>
            <li class="nav-item">
                <a class="nav-link {{ request()->routeIs('chop.cost.analysis') ? 'active' : '' }}" href="{{ route('chop.cost.analysis') }}"><i class="fa-solid fa-chart-line"></i> Cost Analysis</a>
            </li>
            <li class="nav-item">
                <a class="nav-link {{ request()->routeIs('chop.monitoring') ? 'active' : '' }}" href="{{ route('chop.monitoring') }}"><i class="fa-solid fa-calendar-check"></i> Monitoring & Evaluation</a>
            </li>
            <li class="nav-item">
                <a class="nav-link {{ request()->routeIs('chop.settings') ? 'active' : '' }}" href="{{ route('chop.settings') }}"><i class="fa-solid fa-cog"></i> CHOP Setup</a>
            </li>
            @endif
        </ul>
    </li>
    @endif

    <!-- Setup & Configuration Section Header -->
    @if($isSuperAdmin || $user?->canAny(['manage-settings', 'manage-users', 'manage-roles', 'manage-permissions']))
    <li class="nav-item">
        <div class="nav-heading">Setup & Configuration</div>
        <hr class="mx-5 nav-line mb-1" />
    </li>
    @endif

    <!-- Setup & Config -->
    @if($isSuperAdmin || $user?->can('manage-settings'))
    <li class="nav-item dropdown {{ request()->routeIs($setupRoutes) ? 'show' : '' }}">
        <a class="nav-link dropdown-toggle {{ request()->routeIs($setupRoutes) ? 'active' : '' }}" href="{{ route('setup.index') }}" role="button" data-bs-toggle="dropdown" aria-expanded="{{ request()->routeIs($setupRoutes) ? 'true' : 'false' }}">
            <span class="nav-icon">
                <i class="fa-solid fa-cog"></i>
            </span>
            <span class="text">Setup & Config</span>
        </a>
        <ul class="dropdown-menu flex-column {{ request()->routeIs($setupRoutes) ? 'show' : '' }}">
            <li class="nav-item">
                <a class="nav-link {{ request()->routeIs('setup.index') ? 'active' : '' }}" href="{{ route('setup.index') }}"><i class="fa-solid fa-sliders"></i> Setup</a>
            </li>
            <li class="nav-item">
                <a class="nav-link {{ request()->routeIs('setup.location') ? 'active' : '' }}" href="{{ route('setup.location') }}"><i class="fa-solid fa-location-dot"></i> Location</a>
            </li>
            <li class="nav-item">
                <a class="nav-link {{ request()->routeIs('setup.finances') ? 'active' : '' }}" href="{{ route('setup.finances') }}"><i class="fa-solid fa-money-bill"></i> Finance Setup</a>
            </li>
            <li class="nav-item">
                <a class="nav-link {{ request()->routeIs('setup.vendors') ? 'active' : '' }}" href="{{ route('setup.vendors') }}"><i class="fa-solid fa-building"></i> Manage Vendors</a>
            </li>
            <li class="nav-item">
                <a class="nav-link {{ request()->routeIs('setup.approvalconfig') ? 'active' : '' }}" href="{{ route('setup.approvalconfig') }}"><i class="fa-solid fa-clipboard-user"></i> Approval Configuration</a>
            </li>
        </ul>
    </li>
    @endif

    <!-- Users & Access -->
    @if($isSuperAdmin || $user?->canAny(['manage-users', 'manage-roles', 'manage-permissions']))
    <li class="nav-item dropdown {{ request()->routeIs($aclRoutes) ? 'show' : '' }}">
        <a class="nav-link dropdown-toggle {{ request()->routeIs($aclRoutes) ? 'active' : '' }}" href="{{ route('user.management') }}" role="button" data-bs-toggle="dropdown" aria-expanded="{{ request()->routeIs($aclRoutes) ? 'true' : 'false' }}">
            <span class="nav-icon">
                <i class="fa-solid fa-users-cog"></i>
            </span>
            <span class="text">Users & Access</span>
        </a>
        <ul class="dropdown-menu flex-column {{ request()->routeIs($aclRoutes) ? 'show' : '' }}">
            @if($isSuperAdmin || $user?->can('manage-users'))
            <li class="nav-item">
                <a class="nav-link {{ request()->routeIs('user.management') ? 'active' : '' }}" href="{{ route('user.management') }}"><i class="fa-solid fa-users"></i> Users</a>
            </li>
            @endif
            @if($isSuperAdmin || $user?->can('manage-roles'))
            <li class="nav-item">
                <a class="nav-link {{ request()->routeIs('acl.index') ? 'active' : '' }}" href="{{ route('acl.index') }}"><i class="fa-solid fa-user-shield"></i> Roles</a>
            </li>
            @endif
            @if($isSuperAdmin || $user?->can('manage-permissions'))
            <li class="nav-item">
                <a class="nav-link {{ request()->routeIs('acl.permissions') ? 'active' : '' }}" href="{{ route('acl.permissions') }}"><i class="fa-solid fa-key"></i> Permissions</a>
            </li>
            @endif
        </ul>
    </li>
    @endif
</ul>
