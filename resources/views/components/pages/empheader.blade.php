<div class="card card-lg overflow-hidden">
    <div class="pt-16 rounded-top position-relative"
        style="background: url({{ asset('../../assets/images/background/profile-cover.jpg') }}) no-repeat; background-size: cover">
        <div class="position-absolute top-0 end-0 m-4">
            <a href="{{ $editUrl }}" class="icon-shape icon-md bg-white rounded-circle">
                <i class="ti ti-pencil"></i>
            </a>
        </div>
    </div>
    <div class="card-body">
        <div class="d-flex flex-column flex-lg-row gap-4">
            <div>

                @if($photo)
                <img src="{{ asset('storage/'.$photo) }}" alt="" class="rounded-circle avatar avatar-xl" />
                @else
                <img src="{{ asset('../../assets/images/avatar/avatar-blank.png') }}" alt="" class="rounded-circle avatar avatar-xl" />
                @endif
            </div>
            <div class="d-flex flex-column flex-lg-row justify-content-between w-100 gap-2">
                <div class="d-lg-flex flex-lg-column">
                    <h3 class="mb-0"> {{ $getFullName }}</h3>
                    <div class="d-lg-flex align-items-center gap-2">
                        <span> {{ $age }}</span>
                        <span class="text-secondary"> {{ $gender }} </span>
                        <span class="text-secondary"> {{ $email }}</span>
                    </div>

                </div>
                <div class="d-flex align-items-center gap-10">
                    <div class="d-flex flex-column">
                        <span class="fw-semibold fs-5">12,500</span>
                        <span class="text-secondary">Followers</span>
                    </div>
                    <div class="d-flex flex-column">
                        <span class="fw-semibold fs-5">350</span>
                        <span class="text-secondary">Following</span>
                    </div>
                </div>
            </div>
        </div>
    </div>
</div>
@props(['employee_id'])
@php
$menuItems = [
['name' => 'Contract', 'icon' => 'ti ti-briefcase', 'route' => route('hr.contracts', $employee_id)],
['name' => 'Salary', 'icon' => 'fa-solid fa-sack-dollar', 'route' => route('hr.salary', $employee_id)],
['name' => 'Qualifications', 'icon' => 'ti ti-certificate', 'route' => route('hr.qualifications', $employee_id)],
['name' => 'Promotions', 'icon' => 'fa-solid fa-trophy', 'route' => route('hr.promotions', $employee_id)],
['name' => 'Disciplinary', 'icon' => 'fa-solid fa-scale-balanced', 'route' => route('hr.disciplinary', $employee_id)],
['name' => 'Attendance', 'icon' => 'fa-regular fa-calendar-check', 'route' => route('hr.attendance', $employee_id)],
['name' => 'Leave Management', 'icon' => 'ti ti-briefcase', 'route' => route('hr.leave', $employee_id)],
['name' => 'Dependants', 'icon' => 'fa-solid fa-users', 'route' => route('hr.dependants', $employee_id)],
['name' => 'Other Documents', 'icon' => 'ti ti-file', 'route' => route('hr.otherdocuments', $employee_id)],
['name' => 'Digital Signature', 'icon' => 'ti ti-pencil', 'route' => route('hr.digitalsignature', $employee_id)],
];
@endphp

<div class="row mb-8">
    <div class="col-12">
        <!-- Desktop Tabs -->
        <ul class="nav nav-lb-tab border-bottom d-none d-md-flex flex-wrap">
            @foreach ($menuItems as $item)
            <x-pages.empli
                :name="$item['name']"
                :icon="$item['icon']"
                :href="$item['route']"
                :class="request()->url() === $item['route'] ? 'active' : ''"
                wire:navigate />
            @endforeach
        </ul>

        <!-- Mobile Dropdown -->
        <div class="dropdown d-md-none">
            <button class="btn btn-outline-secondary w-100 d-flex justify-content-between align-items-center" type="button"
                data-bs-toggle="dropdown" aria-expanded="false">
                <span id="mobileMenuTitle">
                    {{ collect($menuItems)->firstWhere('route', request()->url())['name'] ?? 'Menu' }}
                </span>
                <i class="ti ti-chevron-down"></i>
            </button>
            <ul class="dropdown-menu w-100">
                @foreach ($menuItems as $item)
                <li>
                    <a class="dropdown-item {{ request()->url() === $item['route'] ? 'active' : '' }}"
                        href="{{ $item['route'] }}">
                        <i class="{{ $item['icon'] }} me-2"></i> {{ $item['name'] }}
                    </a>
                </li>
                @endforeach
            </ul>
        </div>
    </div>
</div>