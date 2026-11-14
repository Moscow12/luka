{{-- Engineering menu. Requesting is open to every user; the rest follows the user's engineering role. --}}
@php
    $engineering = \App\Services\Engineering\EngineeringNavigation::for($user);
    $can = $engineering['can'];
    $badges = $engineering['badges'];
    $engineeringActive = request()->routeIs('engineering.*');

    // [route, icon, label, badge key, extra route patterns that keep the item highlighted]
    $link = function (string $route, string $icon, string $label, ?string $badge = null, array $also = []) use ($badges) {
        $active = request()->routeIs($route, ...$also) ? 'active' : '';
        $count = $badge ? ($badges[$badge] ?? 0) : 0;
        $pill = $count > 0 ? '<span class="badge bg-danger rounded-pill ms-1">'.$count.'</span>' : '';

        return '<li class="nav-item"><a class="nav-link '.$active.'" href="'.e(route($route)).'"><i class="fa-solid '.$icon.' me-2"></i>'.e($label).$pill.'</a></li>';
    };
    $heading = fn (string $label) => '<li class="nav-item"><span class="nav-link disabled text-uppercase small fw-semibold opacity-75 pb-0">'.e($label).'</span></li>';
@endphp

<li class="nav-item dropdown {{ $engineeringActive ? 'show' : '' }}">
    <a class="nav-link dropdown-toggle {{ $engineeringActive ? 'active' : '' }}" href="{{ route('engineering.request') }}" role="button" data-bs-toggle="dropdown" aria-expanded="{{ $engineeringActive ? 'true' : 'false' }}">
        <span class="nav-icon">
            <i class="fa-solid fa-screwdriver-wrench"></i>
        </span>
        <span class="text">Engineering</span>
        @if($engineering['total'] > 0)
            <span class="badge bg-danger rounded-pill ms-auto">{{ $engineering['total'] }}</span>
        @endif
    </a>
    <ul class="dropdown-menu flex-column {{ $engineeringActive ? 'show' : '' }}">
        {!! $link('engineering.request', 'fa-plus', 'Request engineering service') !!}
        {!! $link('engineering.requisitions', 'fa-list-check', 'My requisitions') !!}
        {!! $link('engineering.processed', 'fa-star', 'Processed requisitions', 'feedback') !!}
        {!! $link('engineering.oxygen', 'fa-lungs', 'Order medical oxygen') !!}

        @if($can['assign'] || $can['workload'])
            {!! $heading('Helpdesk') !!}
            @if($can['assign'])
                {!! $link('engineering.helpdesk.queue', 'fa-inbox', 'Job collection & assignment', 'queue') !!}
            @endif
            @if($can['workload'])
                {!! $link('engineering.helpdesk.workload', 'fa-people-group', 'Section workload') !!}
            @endif
            @if($can['assign'])
                {!! $link('engineering.helpdesk.reassignment', 'fa-right-left', 'Job re-assignment') !!}
            @endif
        @endif

        @if($can['jobs'])
            {!! $heading('My work') !!}
            {!! $link('engineering.jobs.mine', 'fa-clipboard-list', 'My assigned jobs', 'jobs', ['engineering.jobs.*']) !!}
        @endif

        {!! $heading('Equipment') !!}
        {!! $link('engineering.inventory', 'fa-microscope', 'Inventory', null, ['engineering.inventory.*']) !!}
        {!! $link('engineering.ppm', 'fa-calendar-check', 'PPM schedule & checklists', 'ppm') !!}
        @if($can['store'])
            {!! $link('engineering.store', 'fa-boxes-stacked', 'Engineering store', 'store') !!}
        @endif

        @if($can['reports'] || $can['procurement'])
            {!! $heading('Reports') !!}
            @if($can['reports'])
                {!! $link('engineering.reports.dashboard', 'fa-table-list', 'MRV dashboard') !!}
            @endif
            @if($can['procurement'])
                {!! $link('engineering.reports.procurement', 'fa-truck-ramp-box', 'Procurement tracker') !!}
            @endif
            @if($can['reports'])
                {!! $link('engineering.reports.technical', 'fa-file-signature', 'Engineering technical report') !!}
            @endif
            @if($can['management_reports'])
                {!! $link('engineering.reports.executive', 'fa-briefcase', 'Department executive summary') !!}
                {!! $link('engineering.reports.weekly', 'fa-calendar-week', 'MRV weekly summary') !!}
            @endif
            @if($can['reports'])
                {!! $link('engineering.reports.kpi', 'fa-gauge-high', 'Individual KPI for engineers') !!}
            @endif
        @endif

        @if($can['setup'])
            {!! $heading('Administration') !!}
            {!! $link('engineering.setup', 'fa-sliders', 'Engineering setup') !!}
        @endif
    </ul>
</li>
