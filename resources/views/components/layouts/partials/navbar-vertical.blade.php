<ul class="navbar-nav flex-column  ">
  <!-- Nav item -->
  <li class="nav-item">
    <a class='nav-link active' href="{{ route('dashboard') }}"><span class="nav-icon"><svg xmlns="http://www.w3.org/2000/svg" width="20" height="20" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="1.5" stroke-linecap="round" stroke-linejoin="round" class="icon icon-tabler icons-tabler-outline icon-tabler-files">
          <path stroke="none" d="M0 0h24v24H0z" fill="none" />
          <path d="M15 3v4a1 1 0 0 0 1 1h4" />
          <path d="M18 17h-7a2 2 0 0 1 -2 -2v-10a2 2 0 0 1 2 -2h4l5 5v7a2 2 0 0 1 -2 2z" />
          <path d="M16 17v2a2 2 0 0 1 -2 2h-7a2 2 0 0 1 -2 -2v-10a2 2 0 0 1 2 -2h2" />
        </svg> <span class="text">Project</span></a>
  </li>
  <!-- Nav item -->
  <li class="nav-item dropdown">
    <a class="nav-link dropdown-toggle" href="{{ route('viewroster.index') }}" role="button" data-bs-toggle="dropdown" aria-expanded="false">
      <span class="nav-icon">
        <i class="fa-solid fa-calendar-days"></i>
      </span>
      <span class="text">Roster</span>
    </a>
    <ul class="dropdown-menu flex-column">
      <li class="nav-item"><a class='nav-link' href="{{ route('viewroster.index') }}"><i class="fa-solid fa-sliders"></i> Overview</a></li>
      <li class="nav-item">
        <a class='nav-link' href="{{ route('roster.create') }}"><i class="fa-solid fa-calendar-days"></i> Generate Roster</a>
      </li>
    </ul>
  </li>
  <!-- Nav item -->
  <li class="nav-item dropdown">
    <a class="nav-link dropdown-toggle" href="{{ route('hr.index') }}" role="button" data-bs-toggle="dropdown" aria-expanded="false">
      <span class="nav-icon">
        <i class="fa-solid fa-umbrella-beach"></i>
      </span>
      <span class="text">Leave</span>
    </a>
    <ul class="dropdown-menu flex-column">
      <li class="nav-item"><a class='nav-link' href="{{ route('leave.leavebalance') }}"><i class="fa-solid fa-calendar-days"></i> Overview</a></li>
      <li class="nav-item">
        <a class='nav-link' href="{{ route('leave.requestleave') }}"><i class="fa-solid fa-bed"></i> Request Leave</a>
      </li>
      <li class="nav-item">
        <a class='nav-link' href="{{ route('leave.leaveapproval') }}"><i class="fa-solid fa-check-double"></i> Leave Approval </a>
      </li>
    </ul>
  </li>
  <!-- Nav item -->
  <li class="nav-item">
    <div class="nav-heading">Human Resources</div>
    <hr class="mx-5 nav-line mb-1" />
  </li>
  <!-- Nav item -->
  <li class="nav-item dropdown">
    <a class="nav-link dropdown-toggle" href="{{ route('hr.index') }}" role="button" data-bs-toggle="dropdown" aria-expanded="false">
      <span class="nav-icon">
        <i class="fa-solid fa-user-group"></i>
      </span>
      <span class="text">Human Resources</span>
    </a>
    <ul class="dropdown-menu flex-column">
      <li class="nav-item"><a class='nav-link' href="{{ route('hr.index') }}"><i class="fa-solid fa-sliders"></i> Overview</a></li>
      <li class="nav-item">
        <a class='nav-link' href="{{ route('hr.stafflist') }}"><i class="fa-solid fa-sliders"></i> Staff Registration</a>
      </li>
      <li class="nav-item">
        <a class='nav-link' href="{{ route('leave.leavemanagement') }}"><i class="fa-solid fa-location-dot"></i> Leave Management</a>
      </li>
      <li class="nav-item">
        <a class='nav-link' href="{{ route('setup.finances') }}"><i class="fa-solid fa-clipboard-user"></i> Roster Management</a>
      </li>
      <li class="nav-item">
        <a class='nav-link' href="{{ route('fp.attendance') }}"><i class="fa-solid fa-clock"></i> Staff Check In/Out</a>
      </li>
      <li class="nav-item">
        <a class='nav-link' href="{{ route('managefpusers') }}"><i class="fa-solid fa-id-card"></i> Manage FP Users</a>
      </li>
    </ul>
  </li>
  <!-- Nav item -->
    <li class="nav-item dropdown">
    <a class="nav-link dropdown-toggle" href="{{ route('hr.index') }}" role="button" data-bs-toggle="dropdown" aria-expanded="false">
      <span class="nav-icon">
        <i class="fa-solid fa-money-bill"></i>
      </span>
      <span class="text">Staff Payments</span>
    </a>
    <ul class="dropdown-menu flex-column">
      <li class="nav-item"><a class='nav-link' href="{{ route('hr.index') }}"><i class="fa-solid fa-sliders"></i> Overview</a></li>
      <li class="nav-item">
        <a class='nav-link' href="{{ route('payrollgeneration') }}"><i class="fa-solid fa-money-bill-wave"></i> Generate Payroll</a>
      </li>
      <li class="nav-item"><a class='nav-link' href="{{ route('allowancepayment') }}"><i class="fa-solid fa-gift"></i> Allowance Payment</a></li>
      <li class="nav-item">
        <a class='nav-link' href='apps/e-commerce/ecommerce-products-details.html'><i class="fa-solid fa-location-dot"></i> Staff Overtime</a>
      </li>
      <li class="nav-item">
        <a class='nav-link' href="{{ route('setup.finances') }}"><i class="fa-solid fa-clipboard-user"></i> Loan Management</a>
      </li>
      <li class="nav-item">
        <a class='nav-link' href="{{ route('paymentreports') }}"><i class="fa-solid fa-clock"></i> Payments Report</a>
      </li>
    </ul>
  </li>
  <!-- Nav item -->
  <li class="nav-item dropdown">
    <a class="nav-link dropdown-toggle" href="{{ route('hr.index') }}" role="button" data-bs-toggle="dropdown" aria-expanded="false">
      <span class="nav-icon">
        <i class="fa-solid fa-money-bill"></i>
      </span>
      <span class="text">Apprisal Management</span>
    </a>
    <ul class="dropdown-menu flex-column">
      <li class="nav-item"><a class='nav-link' href="{{ route('hr.index') }}"><i class="fa-solid fa-sliders"></i> Overview</a></li>
      <li class="nav-item">
        <a class='nav-link' href="{{ route('hr.stafflist') }}"><i class="fa-solid fa-sliders"></i> Apprisal Management</a>
      </li>
      <li class="nav-item">
        <a class='nav-link' href='apps/e-commerce/ecommerce-products-details.html'><i class="fa-solid fa-location-dot"></i> Planning</a>
      </li>
      <li class="nav-item">
        <a class='nav-link' href="{{ route('setup.finances') }}"><i class="fa-solid fa-clipboard-user"></i> Apprisal Report</a>
      </li>
    </ul>
  </li>
  <li class="nav-item">
    <div class="nav-heading">Contract Management</div>
    <hr class="mx-5 nav-line mb-1" />
  </li>
  <!-- Nav item -->
    <li class="nav-item dropdown">
    <a class="nav-link dropdown-toggle" href="{{ route('contracts.list') }}" role="button" data-bs-toggle="dropdown" aria-expanded="false">
      <span class="nav-icon">
        <i class="fa-solid fa-file-contract"></i>
      </span>
      <span class="text">Institutional Contracts</span>
    </a>
    <ul class="dropdown-menu flex-column">
      <li class="nav-item"><a class='nav-link' href="{{ route('contracts.list') }}"><i class="fa-solid fa-list"></i> All Contracts</a></li>
      <li class="nav-item">
        <a class='nav-link' href="{{ route('contracts.create') }}"><i class="fa-solid fa-plus-circle"></i> New Contract</a>
      </li>
      <li class="nav-item">
        <a class='nav-link' href="{{ route('contracts.list') }}"><i class="fa-solid fa-clock"></i> Expiring Soon</a>
      </li>
      <li class="nav-item">
        <a class='nav-link' href="{{ route('contracts.list') }}"><i class="fa-solid fa-check-double"></i> Pending Approvals</a>
      </li>
      <li class="nav-item">
        <a class='nav-link' href="{{ route('contracts.list') }}"><i class="fa-solid fa-chart-bar"></i> Contract Reports</a>
      </li>
    </ul>
  </li>
  <!-- Nav item -->
  <li class="nav-item">
    <div class="nav-heading">Setup & Configuration</div>
    <hr class="mx-5 nav-line mb-1" />
  </li>

  <!-- Nav item -->
  <li class="nav-item dropdown">
    <a class="nav-link dropdown-toggle" href="#" role="button" data-bs-toggle="dropdown" aria-expanded="false">
      <span class="nav-icon">
        <svg xmlns="http://www.w3.org/2000/svg" width="20" height="20" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="1.5" stroke-linecap="round" stroke-linejoin="round" class="icon icon-tabler icons-tabler-outline icon-tabler-settings">
          <path stroke="none" d="M0 0h24v24H0z" fill="none" />
          <path d="M10.325 4.317c.426 -1.756 2.924 -1.756 3.35 0a1.724 1.724 0 0 0 2.573 1.066c1.543 -.94 3.31 .826 2.37 2.37a1.724 1.724 0 0 0 1.065 2.572c1.756 .426 1.756 2.924 0 3.35a1.724 1.724 0 0 0 -1.066 2.573c.94 1.543 -.826 3.31 -2.37 2.37a1.724 1.724 0 0 0 -2.572 1.065c-.426 1.756 -2.924 1.756 -3.35 0a1.724 1.724 0 0 0 -2.573 -1.066c-1.543 .94 -3.31 -.826 -2.37 -2.37a1.724 1.724 0 0 0 -1.065 -2.572c-1.756 -.426 -1.756 -2.924 0 -3.35a1.724 1.724 0 0 0 1.066 -2.573c-.94 -1.543 .826 -3.31 2.37 -2.37c1 .608 2.296 .07 2.572 -1.065z" />
          <path d="M9 12a3 3 0 1 0 6 0a3 3 0 0 0 -6 0" />
        </svg>
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
        <a class='nav-link' href="{{ route('setup.approvalconfig') }}"><i class="fa-solid fa-clipboard-user"></i> Approval Configuration</a>
      </li>
    </ul>
  </li>
  <!-- Nav item -->
  <li class="nav-item">
    <div class="nav-heading">Users & Permissions</div>

    <hr class="mx-5 nav-line mb-1" />
  </li>

  <!-- Nav item -->
  <li class="nav-item">
    <a class='nav-link' href="{{ route('user.management') }}">
      <span class="nav-icon">
        <svg xmlns="http://www.w3.org/2000/svg" width="20" height="20" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="1.5" stroke-linecap="round" stroke-linejoin="round" class="icon icon-tabler icons-tabler-outline icon-tabler-users-group">
          <path stroke="none" d="M0 0h24v24H0z" fill="none"></path>
          <path d="M10 13a2 2 0 1 0 4 0a2 2 0 0 0 -4 0"></path>
          <path d="M8 21v-1a2 2 0 0 1 2 -2h4a2 2 0 0 1 2 2v1"></path>
          <path d="M15 5a2 2 0 1 0 4 0a2 2 0 0 0 -4 0"></path>
          <path d="M17 10h2a2 2 0 0 1 2 2v1"></path>
          <path d="M5 5a2 2 0 1 0 4 0a2 2 0 0 0 -4 0"></path>
          <path d="M3 13v-1a2 2 0 0 1 2 -2h2"></path>
        </svg>
      </span>
      <span class="text">Users</span>
    </a>
  </li>
  <!-- Nav item -->
  <li class="nav-item">
    <a class='nav-link' href="{{ route('acl.index') }}">
      <span class="nav-icon">
        <svg xmlns="http://www.w3.org/2000/svg" width="20" height="20" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="1.5" stroke-linecap="round" stroke-linejoin="round" class="icon icon-tabler icons-tabler-outline icon-tabler-lock">
          <path stroke="none" d="M0 0h24v24H0z" fill="none"></path>
          <path d="M5 13a2 2 0 0 1 2 -2h10a2 2 0 0 1 2 2v6a2 2 0 0 1 -2 2h-10a2 2 0 0 1 -2 -2v-6z"></path>
          <path d="M11 16a1 1 0 1 0 2 0a1 1 0 0 0 -2 0"></path>
          <path d="M8 11v-4a4 4 0 1 1 8 0v4"></path>
        </svg>
      </span>
      <span class="text">Permissions</span>
    </a>
  </li>
  <!-- Nav item -->
  <li class="nav-item dropdown">
    <a class="nav-link dropdown-toggle" href="#e-mail" role="button" data-bs-toggle="dropdown" aria-expanded="false">
      <span class="nav-icon">
        <svg xmlns="http://www.w3.org/2000/svg" width="20" height="20" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="1.5" stroke-linecap="round" stroke-linejoin="round" class="icon icon-tabler icons-tabler-outline icon-tabler-mail">
          <path stroke="none" d="M0 0h24v24H0z" fill="none" />
          <path d="M3 7a2 2 0 0 1 2 -2h14a2 2 0 0 1 2 2v10a2 2 0 0 1 -2 2h-14a2 2 0 0 1 -2 -2v-10z" />
          <path d="M3 7l9 6l9 -6" />
        </svg>
      </span>
      <span class="text">Email</span>
    </a>
    <ul class="dropdown-menu flex-column">
      <li class="nav-item">
        <a class='nav-link' href='apps/email/mail.html'>Inbox</a>
      </li>
      <li class="nav-item">
        <a class='nav-link' href='apps/email/mail-details.html'>Email Detail</a>
      </li>
      <li class="nav-item">
        <a class='nav-link' href='apps/email/compose.html'>Compose</a>
      </li>
    </ul>
  </li>

  <!-- Nav item -->
  <li>
    <div class="text-center py-5 upgrade-ui ">
      <div>
        <img src="{{ asset('images/avatar/avatar-1.jpg') }} " alt="" class="avatar avatar-md rounded-circle">
        <div class="my-3">
          <h5 class="mb-1 fs-6">Jitu Chauhan</h5>
          <span class="text-secondary">Free Version - 1 Month</span>
        </div>
        <a href="#!" class="btn btn-primary">Upgrade</a>

      </div>

    </div>
  </li>

</ul>