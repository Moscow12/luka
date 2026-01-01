@php
    $user = auth()->user();
    $isSuperAdmin = $user?->isSuperAdmin() ?? false;
@endphp

<ul class="navbar-nav flex-column">
    <!-- Dashboard - Always visible for authenticated users -->
    <li class="nav-item">
        <a class='nav-link {{ request()->routeIs('dashboard') ? 'active' : '' }}' href="{{ route('dashboard') }}">
            <span class="nav-icon">
                <i class="fa-solid fa-gauge-high"></i>
            </span>
            <span class="text">Dashboard</span>
        </a>
    </li>

    <!-- Roster -->
    @if($isSuperAdmin || $user?->canAny(['view-roster', 'create-roster', 'manage-roster']))
    <li class="nav-item dropdown">
        <a class="nav-link dropdown-toggle" href="{{ route('viewroster.index') }}" role="button" data-bs-toggle="dropdown" aria-expanded="false">
            <span class="nav-icon">
                <i class="fa-solid fa-calendar-days"></i>
            </span>
            <span class="text">Roster</span>
        </a>
        <ul class="dropdown-menu flex-column">
            @if($isSuperAdmin || $user?->can('view-roster'))
            <li class="nav-item">
                <a class='nav-link' href="{{ route('viewroster.index') }}"><i class="fa-solid fa-sliders"></i> Overview</a>
            </li>
            @endif
            @if($isSuperAdmin || $user?->can('create-roster'))
            <li class="nav-item">
                <a class='nav-link' href="{{ route('roster.create') }}"><i class="fa-solid fa-calendar-days"></i> Generate Roster</a>
            </li>
            @endif
        </ul>
    </li>
    @endif

    <!-- Leave -->
    @if($isSuperAdmin || $user?->canAny(['view-leave', 'request-leave', 'approve-leave']))
    <li class="nav-item dropdown">
        <a class="nav-link dropdown-toggle" href="{{ route('leave.leavebalance') }}" role="button" data-bs-toggle="dropdown" aria-expanded="false">
            <span class="nav-icon">
                <i class="fa-solid fa-umbrella-beach"></i>
            </span>
            <span class="text">Leave</span>
        </a>
        <ul class="dropdown-menu flex-column">
            @if($isSuperAdmin || $user?->can('view-leave'))
            <li class="nav-item">
                <a class='nav-link' href="{{ route('leave.leavebalance') }}"><i class="fa-solid fa-calendar-days"></i> Overview</a>
            </li>
            @endif
            @if($isSuperAdmin || $user?->can('request-leave'))
            <li class="nav-item">
                <a class='nav-link' href="{{ route('leave.requestleave') }}"><i class="fa-solid fa-bed"></i> Request Leave</a>
            </li>
            @endif
            @if($isSuperAdmin || $user?->can('approve-leave'))
            <li class="nav-item">
                <a class='nav-link' href="{{ route('leave.leaveapproval') }}"><i class="fa-solid fa-check-double"></i> Leave Approval</a>
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
    <li class="nav-item dropdown">
        <a class="nav-link dropdown-toggle" href="{{ route('hr.index') }}" role="button" data-bs-toggle="dropdown" aria-expanded="false">
            <span class="nav-icon">
                <i class="fa-solid fa-user-group"></i>
            </span>
            <span class="text">Human Resources</span>
        </a>
        <ul class="dropdown-menu flex-column">
            @if($isSuperAdmin || $user?->can('view-staff'))
            <li class="nav-item">
                <a class='nav-link' href="{{ route('hr.index') }}"><i class="fa-solid fa-sliders"></i> Overview</a>
            </li>
            @endif
            @if($isSuperAdmin || $user?->can('manage-staff'))
            <li class="nav-item">
                <a class='nav-link' href="{{ route('hr.stafflist') }}"><i class="fa-solid fa-users"></i> Staff Registration</a>
            </li>
            @endif
            @if($isSuperAdmin || $user?->can('manage-leave'))
            <li class="nav-item">
                <a class='nav-link' href="{{ route('leave.leavemanagement') }}"><i class="fa-solid fa-umbrella-beach"></i> Leave Management</a>
            </li>
            @endif
            @if($isSuperAdmin || $user?->can('view-attendance'))
            <li class="nav-item">
                <a class='nav-link' href="{{ route('fp.attendance') }}"><i class="fa-solid fa-clock"></i> Staff Check In/Out</a>
            </li>
            <li class="nav-item">
                <a class='nav-link' href="{{ route('managefpusers') }}"><i class="fa-solid fa-id-card"></i> Manage FP Users</a>
            </li>
            @endif
        </ul>
    </li>
    @endif

    <!-- Staff Payments -->
    @if($isSuperAdmin || $user?->canAny(['view-payroll', 'manage-payroll', 'generate-payroll']))
    <li class="nav-item dropdown">
        <a class="nav-link dropdown-toggle" href="{{ route('hr.index') }}" role="button" data-bs-toggle="dropdown" aria-expanded="false">
            <span class="nav-icon">
                <i class="fa-solid fa-money-bill"></i>
            </span>
            <span class="text">Staff Payments</span>
        </a>
        <ul class="dropdown-menu flex-column">
            @if($isSuperAdmin || $user?->can('view-payroll'))
            <li class="nav-item">
                <a class='nav-link' href="{{ route('hr.index') }}"><i class="fa-solid fa-sliders"></i> Overview</a>
            </li>
            @endif
            @if($isSuperAdmin || $user?->can('generate-payroll'))
            <li class="nav-item">
                <a class='nav-link' href="{{ route('payrollgeneration') }}"><i class="fa-solid fa-money-bill-wave"></i> Generate Payroll</a>
            </li>
            @endif
            @if($isSuperAdmin || $user?->can('manage-payroll'))
            <li class="nav-item">
                <a class='nav-link' href="{{ route('allowancepayment') }}"><i class="fa-solid fa-gift"></i> Allowance Payment</a>
            </li>
            <li class="nav-item">
                <a class='nav-link' href="{{ route('paymentreports') }}"><i class="fa-solid fa-chart-bar"></i> Payments Report</a>
            </li>
            @endif
        </ul>
    </li>
    @endif

    <!-- Staff Performance -->
    @if($isSuperAdmin || $user?->canAny(['view-performance', 'manage-performance']))
    <li class="nav-item dropdown">
        <a class="nav-link dropdown-toggle" href="{{ route('performance.org.plans') }}" role="button" data-bs-toggle="dropdown" aria-expanded="false">
            <span class="nav-icon">
                <i class="fa-solid fa-chart-line"></i>
            </span>
            <span class="text">Staff Performance</span>
        </a>
        <ul class="dropdown-menu flex-column">
            @if($isSuperAdmin || $user?->can('view-performance'))
            <li class="nav-item">
                <a class='nav-link' href="{{ route('performance.org.plans') }}"><i class="fa-solid fa-building"></i> Organizational Plans</a>
            </li>
            <li class="nav-item">
                <a class='nav-link' href="{{ route('performance.dept.plans') }}"><i class="fa-solid fa-sitemap"></i> Department Plans</a>
            </li>
            <li class="nav-item">
                <a class='nav-link' href="{{ route('performance.employee.plans') }}"><i class="fa-solid fa-user-check"></i> Employee Plans</a>
            </li>
            @endif
            @if($isSuperAdmin || $user?->can('manage-performance'))
            <li class="nav-item">
                <a class='nav-link' href="{{ route('performance.assigned.duties') }}"><i class="fa-solid fa-tasks"></i> Assigned Duties</a>
            </li>
            <li class="nav-item">
                <a class='nav-link' href="{{ route('performance.title.kpis') }}"><i class="fa-solid fa-id-badge"></i> Job Title KPIs</a>
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
    <li class="nav-item dropdown">
        <a class="nav-link dropdown-toggle" href="{{ route('contracts.list') }}" role="button" data-bs-toggle="dropdown" aria-expanded="false">
            <span class="nav-icon">
                <i class="fa-solid fa-file-contract"></i>
            </span>
            <span class="text">Institutional Contracts</span>
        </a>
        <ul class="dropdown-menu flex-column">
            @if($isSuperAdmin || $user?->can('view-contracts'))
            <li class="nav-item">
                <a class='nav-link' href="{{ route('contracts.list') }}"><i class="fa-solid fa-list"></i> All Contracts</a>
            </li>
            @endif
            @if($isSuperAdmin || $user?->can('manage-contracts'))
            <li class="nav-item">
                <a class='nav-link' href="{{ route('contracts.create') }}"><i class="fa-solid fa-plus-circle"></i> New Contract</a>
            </li>
            @endif
        </ul>
    </li>
    @endif

    <!-- Approval Requests -->
    @if($isSuperAdmin || $user?->can('approve-requests'))
    <li class="nav-item dropdown">
        <a class="nav-link dropdown-toggle" href="#" role="button" data-bs-toggle="dropdown" aria-expanded="false">
            <span class="nav-icon">
                <i class="fa-solid fa-clipboard-check"></i>
            </span>
            <span class="text">Approval Requests</span>
        </a>
        <ul class="dropdown-menu flex-column">
            <li class="nav-item">
                <a class='nav-link' href="#"><i class="fa-solid fa-umbrella-beach"></i> Leave Approval</a>
            </li>
            <li class="nav-item">
                <a class='nav-link' href="#"><i class="fa-solid fa-calendar-days"></i> Roster Approval</a>
            </li>
            <li class="nav-item">
                <a class='nav-link' href="#"><i class="fa-solid fa-money-bill"></i> Payroll Approval</a>
            </li>
            <li class="nav-item">
                <a class='nav-link' href="#"><i class="fa-solid fa-gift"></i> Allowance Approval</a>
            </li>
        </ul>
    </li>
    @endif

    <!-- CHOP Management -->
    @if($isSuperAdmin || $user?->canAny(['view-chop', 'manage-chop']))
    <li class="nav-item dropdown">
        <a class="nav-link dropdown-toggle" href="#" role="button" data-bs-toggle="dropdown" aria-expanded="false">
            <span class="nav-icon">
                <i class="fa-solid fa-file-invoice"></i>
            </span>
            <span class="text">CHOP Management</span>
        </a>
        <ul class="dropdown-menu flex-column">
            @if($isSuperAdmin || $user?->can('view-chop'))
            <li class="nav-item">
                <a class='nav-link' href="{{ route('chop.budget.requests') }}"><i class="fa-solid fa-file-invoice-dollar"></i> My Budget Requests</a>
            </li>
            <li class="nav-item">
                <a class='nav-link' href="{{ route('chop.reporting') }}"><i class="fa-solid fa-file-alt"></i> My Activity Reports</a>
            </li>
            @endif
            @if($isSuperAdmin || $user?->can('manage-chop'))
            <li class="nav-item">
                <a class='nav-link' href="{{ route('chop.director.review') }}"><i class="fa-solid fa-clipboard-check"></i> Review Requests</a>
            </li>
            <li class="nav-item">
                <a class='nav-link' href="{{ route('chop.activities') }}"><i class="fa-solid fa-plus-circle"></i> Plan CHOP Activities</a>
            </li>
            <li class="nav-item">
                <a class='nav-link' href="{{ route('chop.cost.analysis') }}"><i class="fa-solid fa-chart-line"></i> Cost Analysis</a>
            </li>
            <li class="nav-item">
                <a class='nav-link' href="{{ route('chop.monitoring') }}"><i class="fa-solid fa-calendar-check"></i> Monitoring & Evaluation</a>
            </li>
            <li class="nav-item">
                <a class='nav-link' href="{{ route('chop.settings') }}"><i class="fa-solid fa-cog"></i> CHOP Setup</a>
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
    <li class="nav-item dropdown">
        <a class="nav-link dropdown-toggle" href="#" role="button" data-bs-toggle="dropdown" aria-expanded="false">
            <span class="nav-icon">
                <i class="fa-solid fa-cog"></i>
            </span>
            <span class="text">Setup & Config</span>
        </a>
        <ul class="dropdown-menu flex-column">
            <li class="nav-item">
                <a class='nav-link' href="{{ route('setup.index') }}"><i class="fa-solid fa-sliders"></i> Setup</a>
            </li>
            <li class="nav-item">
                <a class='nav-link' href="{{ route('setup.location') }}"><i class="fa-solid fa-location-dot"></i> Location</a>
            </li>
            <li class="nav-item">
                <a class='nav-link' href="{{ route('setup.finances') }}"><i class="fa-solid fa-money-bill"></i> Finance Setup</a>
            </li>
            <li class="nav-item">
                <a class='nav-link' href="{{ route('setup.vendors') }}"><i class="fa-solid fa-building"></i> Manage Vendors</a>
            </li>
            <li class="nav-item">
                <a class='nav-link' href="{{ route('setup.approvalconfig') }}"><i class="fa-solid fa-clipboard-user"></i> Approval Configuration</a>
            </li>
        </ul>
    </li>
    @endif

    <!-- Users & Access -->
    @if($isSuperAdmin || $user?->canAny(['manage-users', 'manage-roles', 'manage-permissions']))
    <li class="nav-item dropdown">
        <a class="nav-link dropdown-toggle" href="{{ route('user.management') }}" role="button" data-bs-toggle="dropdown" aria-expanded="false">
            <span class="nav-icon">
                <i class="fa-solid fa-users-cog"></i>
            </span>
            <span class="text">Users & Access</span>
        </a>
        <ul class="dropdown-menu flex-column">
            @if($isSuperAdmin || $user?->can('manage-users'))
            <li class="nav-item">
                <a class='nav-link' href="{{ route('user.management') }}"><i class="fa-solid fa-users"></i> Users</a>
            </li>
            @endif
            @if($isSuperAdmin || $user?->can('manage-roles'))
            <li class="nav-item">
                <a class='nav-link' href="{{ route('acl.index') }}"><i class="fa-solid fa-user-shield"></i> Roles</a>
            </li>
            @endif
            @if($isSuperAdmin || $user?->can('manage-permissions'))
            <li class="nav-item">
                <a class='nav-link' href="{{ route('acl.permissions') }}"><i class="fa-solid fa-key"></i> Permissions</a>
            </li>
            @endif
        </ul>
    </li>
    @endif
</ul>
