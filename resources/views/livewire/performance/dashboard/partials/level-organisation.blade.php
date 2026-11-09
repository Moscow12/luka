{{-- Organisation level. Expects: $organisation, $dashboard, $num --}}
@php($o = $organisation)

{{-- Headline figures --}}
<div class="row g-3 mb-4">
    <div class="col-xl-2 col-md-4 col-6">
        <div class="card h-100"><div class="card-body">
            <div class="small text-muted mb-1">Overall achievement</div>
            <div class="pd-kpi-value">{{ $o['progress'] === null ? '—' : number_format($o['progress'], 1).'%' }}</div>
            <div class="mt-1">@include('livewire.performance.dashboard.partials.status', ['status' => $o['status']])</div>
            <div class="small text-muted mt-1">Expected {{ number_format($dashboard->expected, 0) }}% by today</div>
        </div></div>
    </div>
    <div class="col-xl-2 col-md-4 col-6">
        <div class="card h-100"><div class="card-body">
            <div class="small text-muted mb-1">Goals assigned</div>
            <div class="pd-kpi-value">{{ $o['goals_cascaded'] }}<span class="fs-6 text-muted"> / {{ count($o['goals']) }}</span></div>
            <div class="small text-muted mt-1">Organisation goals given to a department</div>
        </div></div>
    </div>
    <div class="col-xl-2 col-md-4 col-6">
        <div class="card h-100"><div class="card-body">
            <div class="small text-muted mb-1">Departments planned</div>
            <div class="pd-kpi-value">{{ count($o['departments']) }}<span class="fs-6 text-muted"> / {{ $o['department_total'] }}</span></div>
            <div class="small text-muted mt-1">Have a plan under this plan</div>
        </div></div>
    </div>
    <div class="col-xl-2 col-md-4 col-6">
        <div class="card h-100"><div class="card-body">
            <div class="small text-muted mb-1">Staff with plans</div>
            <div class="pd-kpi-value">{{ $o['staff_planned'] }}<span class="fs-6 text-muted"> / {{ $o['headcount'] }}</span></div>
            <div class="small text-muted mt-1">
                {{ $o['headcount'] ? number_format($o['staff_planned'] / $o['headcount'] * 100, 0) : 0 }}% of active staff
            </div>
        </div></div>
    </div>
    <div class="col-xl-2 col-md-4 col-6">
        <div class="card h-100"><div class="card-body">
            <div class="small text-muted mb-1">Verified activities</div>
            <div class="pd-kpi-value">{{ number_format($o['activity']['verified']) }}</div>
            <div class="small mt-1 {{ $o['activity']['pending'] ? 'text-warning-emphasis' : 'text-muted' }}">
                <i class="fa-solid fa-hourglass-half me-1"></i>{{ number_format($o['activity']['pending']) }} awaiting verification
            </div>
        </div></div>
    </div>
    <div class="col-xl-2 col-md-4 col-6">
        <div class="card h-100"><div class="card-body">
            <div class="small text-muted mb-1">Evaluations approved</div>
            <div class="pd-kpi-value">{{ $o['evaluations']['stages']['approved']['count'] }}<span class="fs-6 text-muted"> / {{ $o['evaluations']['total'] }}</span></div>
            <div class="small text-muted mt-1">Avg score {{ $o['evaluations']['average_score'] === null ? '—' : number_format($o['evaluations']['average_score'], 1).'%' }}</div>
        </div></div>
    </div>
</div>

<div class="row">
    <div class="col-xl-8">
        {{-- Organisation goals --}}
        <div class="card mb-4">
            <div class="card-header bg-transparent">
                <h6 class="mb-0"><i class="fa-solid fa-building me-2 text-primary"></i>Organisation goals</h6>
                <small class="text-muted">Achievement is built up from the department goals that deliver each one.</small>
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
                            <th class="text-center">Depts</th>
                            <th class="text-end" title="Share of the target handed down to departments">Assigned</th>
                        </tr>
                    </thead>
                    <tbody>
                        @forelse($o['goals'] as $goal)
                            <tr wire:key="org-goal-{{ $goal['id'] }}">
                                <td>
                                    <div class="fw-semibold">{{ $goal['name'] }}</div>
                                    <div class="mt-1">@include('livewire.performance.dashboard.partials.smart', ['smart' => $goal['smart']])</div>
                                </td>
                                <td class="text-end">{{ $num($goal['weight']) }}%</td>
                                <td class="text-end text-nowrap">{{ $num($goal['target']) }} <span class="text-muted small">{{ $goal['unit'] }}</span></td>
                                <td class="text-end text-nowrap">{{ $goal['achieved'] === null ? '—' : $num($goal['achieved']) }}</td>
                                <td class="pd-progress-cell">@include('livewire.performance.dashboard.partials.progress', ['progress' => $goal['progress'], 'status' => $goal['status'], 'expected' => $dashboard->expected])</td>
                                <td>@include('livewire.performance.dashboard.partials.status', ['status' => $goal['status']])</td>
                                <td class="text-center">
                                    @if($goal['departments'] === 0)
                                        <span class="badge bg-danger-subtle text-danger-emphasis" title="No department goal delivers this"><i class="fa-solid fa-link-slash me-1"></i>None</span>
                                    @else
                                        {{ $goal['departments'] }}
                                    @endif
                                </td>
                                <td class="text-end {{ $goal['coverage'] !== null && $goal['coverage'] < 100 ? 'text-danger-emphasis fw-semibold' : '' }}">
                                    {{ $goal['coverage'] === null ? '—' : number_format($goal['coverage'], 0).'%' }}
                                </td>
                            </tr>
                        @empty
                            <tr><td colspan="8" class="text-center text-muted py-4">This plan has no goals yet.</td></tr>
                        @endforelse
                    </tbody>
                </table>
            </div>
        </div>

        {{-- Department league --}}
        <div class="card mb-4">
            <div class="card-header bg-transparent">
                <h6 class="mb-0"><i class="fa-solid fa-sitemap me-2 text-primary"></i>Departments</h6>
                <small class="text-muted">Lowest progress first. Select a department to see its goals and staff.</small>
            </div>
            <div class="table-responsive">
                <table class="table align-middle mb-0">
                    <thead class="table-light small">
                        <tr>
                            <th>Department</th>
                            <th class="text-center">Staff planned</th>
                            <th>Progress</th>
                            <th>Status</th>
                            <th class="text-end">Verified</th>
                            <th class="text-end">Pending</th>
                            <th class="text-center">Evaluated</th>
                            <th class="text-end">Avg score</th>
                            <th></th>
                        </tr>
                    </thead>
                    <tbody>
                        @foreach($o['departments'] as $row)
                            <tr class="pd-row" wire:key="dept-{{ $row['id'] }}" wire:click="openDepartment('{{ $row['id'] }}')">
                                <td>
                                    <button type="button" class="btn btn-link p-0 text-start fw-semibold text-decoration-none" wire:click.stop="openDepartment('{{ $row['id'] }}')">{{ $row['department'] }}</button>
                                    <div class="small text-muted">{{ count($row['goals']) }} goals</div>
                                </td>
                                <td class="text-center {{ $row['staff_planned'] < $row['headcount'] ? 'text-warning-emphasis' : '' }}">{{ $row['staff_planned'] }} / {{ $row['headcount'] }}</td>
                                <td class="pd-progress-cell">@include('livewire.performance.dashboard.partials.progress', ['progress' => $row['progress'], 'status' => $row['status'], 'expected' => $dashboard->expected])</td>
                                <td>@include('livewire.performance.dashboard.partials.status', ['status' => $row['status']])</td>
                                <td class="text-end">{{ number_format($row['verified']) }}</td>
                                <td class="text-end {{ $row['pending'] ? 'text-warning-emphasis fw-semibold' : 'text-muted' }}">{{ number_format($row['pending']) }}</td>
                                <td class="text-center">{{ $row['evaluated'] }} / {{ $row['staff_planned'] }}</td>
                                <td class="text-end">{{ $row['average_score'] === null ? '—' : number_format($row['average_score'], 1).'%' }}</td>
                                <td class="text-end text-muted"><i class="fa-solid fa-chevron-right"></i></td>
                            </tr>
                        @endforeach
                        @foreach($o['unplanned_departments'] as $row)
                            <tr wire:key="dept-none-{{ $row['department_id'] }}" class="text-muted">
                                <td>{{ $row['department'] }}</td>
                                <td class="text-center">0 / {{ $row['headcount'] }}</td>
                                <td colspan="7"><span class="badge bg-secondary-subtle text-secondary-emphasis"><i class="fa-solid fa-circle-minus me-1"></i>No department plan</span></td>
                            </tr>
                        @endforeach
                        @if(empty($o['departments']) && empty($o['unplanned_departments']))
                            <tr><td colspan="9" class="text-center text-muted py-4">No departments found.</td></tr>
                        @endif
                    </tbody>
                </table>
            </div>
        </div>
    </div>

    <div class="col-xl-4">
        @include('livewire.performance.dashboard.partials.evaluations', ['evaluations' => $o['evaluations']])
        @include('livewire.performance.dashboard.partials.quality', ['smart' => $o['smart'], 'issues' => $o['issues']])
    </div>
</div>
