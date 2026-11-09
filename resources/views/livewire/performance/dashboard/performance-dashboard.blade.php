<div>
    @php
        // Trim trailing zeros: 120.00 -> 120, 12.50 -> 12.5
        $num = fn ($value) => $value === null ? '—' : rtrim(rtrim(number_format((float) $value, 2), '0'), '.');
    @endphp

    {{-- Header --}}
    <div class="d-md-flex justify-content-between align-items-start mb-3 gap-3">
        <div class="mb-3 mb-md-0">
            <h4 class="mb-1">Performance Dashboard</h4>
            <p class="text-muted mb-0">Plans and verified implementation, from organisation goals down to each employee.</p>
        </div>
        @if($plans->isNotEmpty())
            <div class="pd-plan-picker">
                <label for="pd-plan" class="form-label small text-muted mb-1">Organisational plan</label>
                <select id="pd-plan" class="form-select" wire:model.live="planId">
                    @foreach($plans as $option)
                        <option value="{{ $option->id }}">{{ $option->plan_name }} ({{ ucfirst($option->status) }})</option>
                    @endforeach
                </select>
            </div>
        @endif
    </div>

    @if($level === 'empty')
        <div class="card"><div class="card-body text-center py-5">
            <i class="fa-solid fa-bullseye fa-3x text-muted opacity-50 mb-3"></i>
            <h5>No organisational plan yet</h5>
            <p class="text-muted mb-0">Create an organisational plan and cascade it to departments to see progress here.</p>
        </div></div>
    @else
        {{-- Breadcrumb: where you are in the cascade --}}
        <nav aria-label="Plan level" class="mb-3">
            <ol class="breadcrumb mb-0">
                @if($canSeeOrganisation)
                    <li class="breadcrumb-item {{ $level === 'organisation' ? 'active' : '' }}">
                        @if($level === 'organisation')
                            <i class="fa-solid fa-building me-1"></i>Organisation
                        @else
                            <a href="#" wire:click.prevent="showOrganisation"><i class="fa-solid fa-building me-1"></i>Organisation</a>
                        @endif
                    </li>
                @endif
                @if(in_array($level, ['department', 'employee']) && $department)
                    <li class="breadcrumb-item {{ $level === 'department' ? 'active' : '' }}">
                        @if($level === 'department')
                            <i class="fa-solid fa-sitemap me-1"></i>{{ $department['department'] }}
                        @else
                            <a href="#" wire:click.prevent="showDepartment"><i class="fa-solid fa-sitemap me-1"></i>{{ $department['department'] }}</a>
                        @endif
                    </li>
                @endif
                @if($level === 'employee')
                    <li class="breadcrumb-item active"><i class="fa-solid fa-user me-1"></i>{{ $employee['employee_name'] }}</li>
                @endif
            </ol>
        </nav>

        {{-- Plan period: the yardstick every status on the page is measured against --}}
        <div class="card mb-4">
            <div class="card-body py-3">
                <div class="row align-items-center g-3">
                    <div class="col-md-5">
                        <div class="fw-semibold">{{ $plan->plan_name }}</div>
                        <div class="small text-muted">
                            <i class="fa-regular fa-calendar me-1"></i>{{ $plan->start_date?->format('d M Y') }} – {{ $plan->end_date?->format('d M Y') }}
                            <span class="badge bg-secondary-subtle text-secondary-emphasis ms-1">{{ ucfirst($plan->status) }}</span>
                        </div>
                    </div>
                    <div class="col-md-7">
                        <div class="d-flex justify-content-between small mb-1">
                            <span class="text-muted">Time elapsed</span>
                            <span class="fw-semibold">{{ number_format($dashboard->expected, 0) }}% of the plan period</span>
                        </div>
                        <div class="pd-track bg-primary-subtle"><div class="pd-fill bg-primary" style="width: {{ $dashboard->expected }}%"></div></div>
                        <div class="small text-muted mt-1">
                            <span class="pd-marker-key"></span> on each bar marks where a goal should be by today.
                            On track = within {{ \App\Services\Performance\PlanProgress::ON_TRACK_TOLERANCE }} points of it,
                            at risk = within {{ \App\Services\Performance\PlanProgress::AT_RISK_TOLERANCE }}.
                        </div>
                    </div>
                </div>
            </div>
        </div>

        <div wire:loading.class="opacity-50" wire:target="planId, openDepartment, openEmployee, showOrganisation, showDepartment">
            @switch($level)
                @case('organisation')
                    @include('livewire.performance.dashboard.partials.level-organisation')
                    @break
                @case('department')
                    @include('livewire.performance.dashboard.partials.level-department')
                    @break
                @case('employee')
                    @include('livewire.performance.dashboard.partials.level-employee')
                    @break
                @default
                    <div class="alert alert-info">
                        <i class="fa-solid fa-circle-info me-2"></i>
                        Your department has no plan under <strong>{{ $plan->plan_name }}</strong> yet.
                    </div>
            @endswitch
        </div>

        <details class="small text-muted mb-4">
            <summary class="mb-2">How the numbers are calculated</summary>
            <ul class="mb-0">
                <li><strong>Employee goal</strong>: verified quantity ÷ target. Pending and rejected entries do not count.</li>
                <li><strong>Department and organisation goals</strong>: for counted targets, what the goals below achieved is added up and compared with this goal's own target, so a target that was only partly handed down cannot show as complete. For percentage targets the progress of the goals below is averaged by weight.</li>
                <li><strong>Plan progress</strong>: goal progress averaged by goal weight. A goal with a target that nobody is delivering counts as 0%.</li>
                <li><strong>Target assigned</strong>: share of a goal's target that has been handed down to the level below.</li>
                <li><strong>Inactive</strong>: no work logged in the last {{ \App\Services\Performance\PerformanceDashboard::INACTIVE_AFTER_DAYS }} days.</li>
            </ul>
        </details>
    @endif

    <style>
        .pd-plan-picker { min-width: 280px; }
        .pd-track { position: relative; height: 8px; border-radius: 999px; }
        .pd-fill { height: 100%; border-radius: 999px; transition: width .4s ease; }
        .pd-marker { position: absolute; top: -4px; bottom: -4px; width: 2px; margin-left: -1px; border-radius: 1px; background: var(--bs-emphasis-color); opacity: .6; }
        .pd-marker-key { display: inline-block; width: 2px; height: 12px; vertical-align: middle; background: var(--bs-emphasis-color); opacity: .6; }
        .pd-pct { min-width: 2.75rem; text-align: right; }
        .pd-progress-cell { min-width: 170px; }
        .pd-seg { display: flex; gap: 2px; height: 10px; }
        .pd-seg > span { border-radius: 999px; min-width: 4px; }
        .pd-dot { display: inline-block; width: 8px; height: 8px; border-radius: 50%; margin-right: .5rem; }
        .pd-hbar { height: 8px; border-radius: 0 4px 4px 0; min-width: 2px; }
        .pd-rating-label { width: 84px; }
        .pd-count { width: 1.5rem; text-align: right; }
        .pd-kpi-value { font-size: 1.6rem; font-weight: 600; line-height: 1.2; }
        .pd-smart { display: inline-flex; align-items: center; justify-content: center; width: 18px; height: 18px; border-radius: 4px; font-size: 11px; font-weight: 700; }
        .pd-smart-ok { background: var(--bs-success-bg-subtle); color: var(--bs-success-text-emphasis); }
        .pd-smart-miss { background: var(--bs-secondary-bg); color: var(--bs-secondary-color); text-decoration: line-through; }
        .pd-avatar { width: 36px; height: 36px; font-size: .8rem; }
        .pd-row { cursor: pointer; }
        .pd-row:hover > td { background: var(--bs-tertiary-bg); }
    </style>
</div>
