{{-- Department level. Expects: $department, $employees (filtered), $dashboard, $num, $statuses --}}
@php($d = $department)

<div class="row g-3 mb-4">
    <div class="col-xl-2 col-md-4 col-6">
        <div class="card h-100"><div class="card-body">
            <div class="small text-muted mb-1">Department achievement</div>
            <div class="pd-kpi-value">{{ $d['progress'] === null ? '—' : number_format($d['progress'], 1).'%' }}</div>
            <div class="mt-1">@include('livewire.performance.dashboard.partials.status', ['status' => $d['status']])</div>
        </div></div>
    </div>
    <div class="col-xl-2 col-md-4 col-6">
        <div class="card h-100"><div class="card-body">
            <div class="small text-muted mb-1">Staff with plans</div>
            <div class="pd-kpi-value">{{ $d['staff_planned'] }}<span class="fs-6 text-muted"> / {{ $d['headcount'] }}</span></div>
            <div class="small text-muted mt-1">Active staff in department</div>
        </div></div>
    </div>
    <div class="col-xl-2 col-md-4 col-6">
        <div class="card h-100"><div class="card-body">
            <div class="small text-muted mb-1">Verified activities</div>
            <div class="pd-kpi-value">{{ number_format($d['activity']['verified']) }}</div>
            <div class="small text-muted mt-1">{{ number_format($d['activity']['rejected']) }} rejected</div>
        </div></div>
    </div>
    <div class="col-xl-2 col-md-4 col-6">
        <div class="card h-100"><div class="card-body">
            <div class="small text-muted mb-1">Awaiting verification</div>
            <div class="pd-kpi-value {{ $d['activity']['pending'] ? 'text-warning-emphasis' : '' }}">{{ number_format($d['activity']['pending']) }}</div>
            <div class="small text-muted mt-1">Entries supervisors must review</div>
        </div></div>
    </div>
    <div class="col-xl-2 col-md-4 col-6">
        <div class="card h-100"><div class="card-body">
            <div class="small text-muted mb-1">Inactive plans</div>
            <div class="pd-kpi-value {{ $d['activity']['inactive'] ? 'text-danger-emphasis' : '' }}">{{ $d['activity']['inactive'] }}</div>
            <div class="small text-muted mt-1">No work logged in {{ \App\Services\Performance\PerformanceDashboard::INACTIVE_AFTER_DAYS }} days</div>
        </div></div>
    </div>
    <div class="col-xl-2 col-md-4 col-6">
        <div class="card h-100"><div class="card-body">
            <div class="small text-muted mb-1">Evaluations approved</div>
            <div class="pd-kpi-value">{{ $d['evaluations']['stages']['approved']['count'] }}<span class="fs-6 text-muted"> / {{ $d['evaluations']['total'] }}</span></div>
            <div class="small text-muted mt-1">Avg score {{ $d['average_score'] === null ? '—' : number_format($d['average_score'], 1).'%' }}</div>
        </div></div>
    </div>
</div>

<div class="row">
    <div class="col-xl-8">
        {{-- Department goals --}}
        <div class="card mb-4">
            <div class="card-header bg-transparent d-flex justify-content-between align-items-start">
                <div>
                    <h6 class="mb-0"><i class="fa-solid fa-sitemap me-2 text-primary"></i>{{ $d['plan_name'] }}</h6>
                    <small class="text-muted">Department goals, built up from the employee goals that deliver them.</small>
                </div>
                <span class="badge bg-secondary-subtle text-secondary-emphasis">{{ ucfirst($d['plan_status']) }}</span>
            </div>
            <div class="table-responsive">
                <table class="table table-hover align-middle mb-0">
                    <thead class="table-light small">
                        <tr>
                            <th>Goal</th>
                            <th class="text-end">Weight</th>
                            <th class="text-end">Target</th>
                            <th class="text-end">Achieved</th>
                            <th>Progress</th>
                            <th>Status</th>
                            <th class="text-center" title="Employee goals delivering this">Staff goals</th>
                            <th class="text-end" title="Share of the target handed down to staff">Assigned</th>
                        </tr>
                    </thead>
                    <tbody>
                        @forelse($d['goals'] as $goal)
                            <tr wire:key="dept-goal-{{ $goal['id'] }}">
                                <td>
                                    <div class="fw-semibold">{{ $goal['name'] }}</div>
                                    <div class="small text-muted">
                                        @if($goal['parent_name'])
                                            <i class="fa-solid fa-turn-up fa-rotate-90 me-1"></i>Serves: {{ $goal['parent_name'] }}
                                        @else
                                            <i class="fa-solid fa-link-slash me-1"></i>Department-only goal
                                        @endif
                                    </div>
                                    <div class="mt-1">@include('livewire.performance.dashboard.partials.smart', ['smart' => $goal['smart']])</div>
                                </td>
                                <td class="text-end">{{ $num($goal['weight']) }}%</td>
                                <td class="text-end text-nowrap">{{ $num($goal['target']) }} <span class="text-muted small">{{ $goal['unit'] }}</span></td>
                                <td class="text-end">{{ $goal['achieved'] === null ? '—' : $num($goal['achieved']) }}</td>
                                <td class="pd-progress-cell">@include('livewire.performance.dashboard.partials.progress', ['progress' => $goal['progress'], 'status' => $goal['status'], 'expected' => $dashboard->expected])</td>
                                <td>@include('livewire.performance.dashboard.partials.status', ['status' => $goal['status']])</td>
                                <td class="text-center">
                                    @if($goal['contributors'] === 0)
                                        <span class="badge bg-danger-subtle text-danger-emphasis"><i class="fa-solid fa-user-slash me-1"></i>None</span>
                                    @else
                                        {{ $goal['contributors'] }}
                                    @endif
                                </td>
                                <td class="text-end {{ $goal['coverage'] !== null && $goal['coverage'] < 100 ? 'text-danger-emphasis fw-semibold' : '' }}">
                                    {{ $goal['coverage'] === null ? '—' : number_format($goal['coverage'], 0).'%' }}
                                </td>
                            </tr>
                        @empty
                            <tr><td colspan="8" class="text-center text-muted py-4">This department plan has no goals yet.</td></tr>
                        @endforelse
                    </tbody>
                </table>
            </div>
        </div>

        {{-- Staff --}}
        <div class="card mb-4">
            <div class="card-header bg-transparent">
                <div class="d-md-flex justify-content-between align-items-center gap-2">
                    <div class="mb-2 mb-md-0">
                        <h6 class="mb-0"><i class="fa-solid fa-users me-2 text-primary"></i>Staff plans</h6>
                        <small class="text-muted">Lowest progress first. Select a person to see their goals and evidence.</small>
                    </div>
                    <div class="d-flex gap-2">
                        <input type="search" class="form-control form-control-sm" placeholder="Search name or number" wire:model.live.debounce.300ms="search" aria-label="Search staff">
                        <select class="form-select form-select-sm" wire:model.live="statusFilter" aria-label="Filter by status">
                            <option value="">All statuses</option>
                            @foreach($statuses as $key => $meta)
                                <option value="{{ $key }}">{{ $meta['label'] }}</option>
                            @endforeach
                        </select>
                    </div>
                </div>
            </div>
            <div class="table-responsive">
                <table class="table align-middle mb-0">
                    <thead class="table-light small">
                        <tr>
                            <th>Employee</th>
                            <th>Progress</th>
                            <th>Status</th>
                            <th class="text-end">Verified</th>
                            <th class="text-end">Pending</th>
                            <th>Last activity</th>
                            <th>Evaluation</th>
                            <th class="text-end">Final</th>
                            <th></th>
                        </tr>
                    </thead>
                    <tbody>
                        @forelse($employees as $row)
                            @php($stage = \App\Services\Performance\PerformanceDashboard::EVALUATION_STAGES[$row['evaluation']['stage']])
                            <tr class="pd-row" wire:key="emp-{{ $row['id'] }}" wire:click="openEmployee('{{ $row['id'] }}')">
                                <td>
                                    <div class="d-flex align-items-center gap-2">
                                        <span class="pd-avatar rounded-circle bg-primary-subtle text-primary-emphasis d-inline-flex align-items-center justify-content-center fw-semibold flex-shrink-0">{{ $row['initials'] }}</span>
                                        <div>
                                            <button type="button" class="btn btn-link p-0 text-start fw-semibold text-decoration-none" wire:click.stop="openEmployee('{{ $row['id'] }}')">{{ $row['employee_name'] }}</button>
                                            <div class="small text-muted">{{ $row['employee_no'] }} · {{ count($row['goals']) }} goals</div>
                                        </div>
                                    </div>
                                </td>
                                <td class="pd-progress-cell">@include('livewire.performance.dashboard.partials.progress', ['progress' => $row['progress'], 'status' => $row['status'], 'expected' => $dashboard->expected])</td>
                                <td>@include('livewire.performance.dashboard.partials.status', ['status' => $row['status']])</td>
                                <td class="text-end">{{ $row['verified'] }}</td>
                                <td class="text-end {{ $row['pending'] ? 'text-warning-emphasis fw-semibold' : 'text-muted' }}">{{ $row['pending'] }}</td>
                                <td class="small text-nowrap">
                                    {{ $row['last_activity'] ? \Carbon\Carbon::parse($row['last_activity'])->format('d M Y') : 'Never' }}
                                    @if($row['inactive'])
                                        <div><span class="badge bg-danger-subtle text-danger-emphasis"><i class="fa-solid fa-moon me-1"></i>Inactive</span></div>
                                    @endif
                                </td>
                                <td><span class="badge bg-{{ $stage['color'] }}-subtle text-{{ $stage['color'] }}-emphasis">{{ $stage['label'] }}</span></td>
                                <td class="text-end text-nowrap">
                                    {{ $row['evaluation']['final'] === null ? '—' : number_format($row['evaluation']['final'], 1).'%' }}
                                    @if($row['evaluation']['rating'])
                                        <div class="small text-muted">{{ $row['evaluation']['rating'] }}</div>
                                    @endif
                                </td>
                                <td class="text-end text-muted"><i class="fa-solid fa-chevron-right"></i></td>
                            </tr>
                        @empty
                            <tr><td colspan="9" class="text-center text-muted py-4">
                                {{ ($search || $statusFilter) ? 'No staff match these filters.' : 'No employee plans under this department plan yet.' }}
                            </td></tr>
                        @endforelse
                    </tbody>
                </table>
            </div>
        </div>
    </div>

    <div class="col-xl-4">
        @include('livewire.performance.dashboard.partials.evaluations', ['evaluations' => $d['evaluations']])
        @include('livewire.performance.dashboard.partials.quality', ['smart' => $d['smart'], 'issues' => $d['issues']])
    </div>
</div>
