@props([
    'employee_id',
    'photo' => null,
    'getFullName' => '',
    'age' => '',
    'gender' => '',
    'email' => '',
    'editUrl' => '#',
    'department' => null,
    'designation' => null,
    'employeeNumber' => null,
])

@php
    $menuItems = [
        ['name' => 'Contract', 'icon' => 'fa-solid fa-file-contract', 'route' => route('hr.contracts', $employee_id)],
        ['name' => 'Salary', 'icon' => 'fa-solid fa-sack-dollar', 'route' => route('hr.salary', $employee_id)],
        ['name' => 'Qualifications', 'icon' => 'fa-solid fa-graduation-cap', 'route' => route('hr.qualifications', $employee_id)],
        ['name' => 'Promotions', 'icon' => 'fa-solid fa-trophy', 'route' => route('hr.promotions', $employee_id)],
        ['name' => 'Disciplinary', 'icon' => 'fa-solid fa-scale-balanced', 'route' => route('hr.disciplinary', $employee_id)],
        ['name' => 'Attendance', 'icon' => 'fa-solid fa-calendar-check', 'route' => route('hr.attendance', $employee_id)],
        ['name' => 'Leave', 'icon' => 'fa-solid fa-umbrella-beach', 'route' => route('hr.leave', $employee_id)],
        ['name' => 'Dependants', 'icon' => 'fa-solid fa-users', 'route' => route('hr.dependants', $employee_id)],
        ['name' => 'Documents', 'icon' => 'fa-solid fa-folder-open', 'route' => route('hr.otherdocuments', $employee_id)],
        ['name' => 'Signature', 'icon' => 'fa-solid fa-signature', 'route' => route('hr.digitalsignature', $employee_id)],
    ];

    $currentRoute = request()->url();
    $activeItem = collect($menuItems)->firstWhere('route', $currentRoute);
@endphp

<!-- Employee Profile Header -->
<div class="card mb-4">
    <!-- Cover Image -->
    <div class="position-relative">
        <div class="bg-primary bg-gradient rounded-top" style="height: 120px;"></div>
        <a href="{{ $editUrl }}"
            class="btn btn-icon btn-light btn-sm position-absolute top-0 end-0 m-3 shadow-sm"
            title="Edit Profile">
            <i class="fa-solid fa-pen-to-square"></i>
        </a>
    </div>

    <!-- Profile Info -->
    <div class="card-body pt-0">
        <div class="d-flex flex-column flex-md-row gap-4" style="margin-top: -50px;">
            <!-- Avatar -->
            <div class="flex-shrink-0">
                @if($photo)
                    <img src="{{ asset('storage/' . $photo) }}"
                        alt="{{ $getFullName }}"
                        class="avatar avatar-xxl rounded-circle border border-4 border-white shadow">
                @else
                    <div class="avatar avatar-xxl rounded-circle border border-4 border-white shadow bg-primary d-flex align-items-center justify-content-center">
                        <span class="fs-1 text-white fw-bold">
                            {{ strtoupper(substr($getFullName, 0, 1)) }}
                        </span>
                    </div>
                @endif
            </div>

            <!-- Details -->
            <div class="d-flex flex-column flex-md-row justify-content-between align-items-start w-100 pt-md-5 gap-3">
                <div>
                    <h4 class="mb-1">{{ $getFullName }}</h4>
                    <div class="d-flex flex-wrap align-items-center gap-3 text-muted">
                        @if($employeeNumber)
                            <span class="badge bg-primary-subtle text-primary">
                                <i class="fa-solid fa-id-badge me-1"></i>{{ $employeeNumber }}
                            </span>
                        @endif
                        @if($designation)
                            <span>
                                <i class="fa-solid fa-briefcase me-1"></i>{{ $designation }}
                            </span>
                        @endif
                        @if($department)
                            <span>
                                <i class="fa-solid fa-building me-1"></i>{{ $department }}
                            </span>
                        @endif
                    </div>
                    <div class="d-flex flex-wrap align-items-center gap-3 mt-2 small text-muted">
                        @if($age)
                            <span><i class="fa-solid fa-cake-candles me-1"></i>{{ $age }} years</span>
                        @endif
                        @if($gender)
                            <span><i class="fa-solid fa-venus-mars me-1"></i>{{ $gender }}</span>
                        @endif
                        @if($email)
                            <span><i class="fa-solid fa-envelope me-1"></i>{{ $email }}</span>
                        @endif
                    </div>
                </div>

                <!-- Quick Actions -->
                <div class="d-flex gap-2">
                    <a href="mailto:{{ $email }}" class="btn btn-outline-primary btn-sm" title="Send Email">
                        <i class="fa-solid fa-envelope"></i>
                    </a>
                    <a href="{{ $editUrl }}" class="btn btn-primary btn-sm">
                        <i class="fa-solid fa-pen-to-square me-1"></i>Edit
                    </a>
                </div>
            </div>
        </div>
    </div>
</div>

<!-- Navigation Tabs -->
<div class="card mb-4">
    <div class="card-body p-0">
        <!-- Desktop Navigation -->
        <ul class="nav nav-tabs nav-tabs-line d-none d-lg-flex flex-nowrap overflow-auto px-3">
            @foreach ($menuItems as $item)
                <li class="nav-item">
                    <a href="{{ $item['route'] }}"
                        class="nav-link d-flex align-items-center gap-2 {{ $currentRoute === $item['route'] ? 'active' : '' }}"
                        wire:navigate>
                        <i class="{{ $item['icon'] }}"></i>
                        <span>{{ $item['name'] }}</span>
                    </a>
                </li>
            @endforeach
        </ul>

        <!-- Mobile Navigation -->
        <div class="dropdown d-lg-none p-3">
            <button class="btn btn-outline-secondary w-100 d-flex justify-content-between align-items-center"
                type="button"
                data-bs-toggle="dropdown"
                aria-expanded="false">
                <span class="d-flex align-items-center gap-2">
                    @if($activeItem)
                        <i class="{{ $activeItem['icon'] }}"></i>
                        {{ $activeItem['name'] }}
                    @else
                        <i class="fa-solid fa-bars"></i>
                        Select Section
                    @endif
                </span>
                <i class="fa-solid fa-chevron-down"></i>
            </button>
            <ul class="dropdown-menu w-100 shadow">
                @foreach ($menuItems as $item)
                    <li>
                        <a href="{{ $item['route'] }}"
                            class="dropdown-item d-flex align-items-center gap-2 {{ $currentRoute === $item['route'] ? 'active' : '' }}"
                            wire:navigate>
                            <i class="{{ $item['icon'] }}" style="width: 20px;"></i>
                            {{ $item['name'] }}
                        </a>
                    </li>
                @endforeach
            </ul>
        </div>
    </div>
</div>
