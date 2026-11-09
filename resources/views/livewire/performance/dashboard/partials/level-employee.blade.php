{{-- Employee level. Expects: $employee, $dashboard, $num --}}
@php
    $e = $employee;
    $stage = \App\Services\Performance\PerformanceDashboard::EVALUATION_STAGES[$e['evaluation']['stage']];
    $scores = ['Self' => $e['evaluation']['self'], 'Supervisor' => $e['evaluation']['supervisor'], 'Task quality' => $e['evaluation']['task'], 'Final' => $e['evaluation']['final']];
    $entryBadge = ['verified' => 'success', 'pending' => 'warning', 'rejected' => 'danger'];
@endphp

{{-- Who, how far, how rated --}}
<div class="row g-3 mb-4">
    <div class="col-lg-7">
        <div class="card h-100"><div class="card-body">
            <div class="d-flex align-items-center gap-3 mb-3">
                <span class="rounded-circle bg-primary-subtle text-primary-emphasis d-inline-flex align-items-center justify-content-center fw-semibold fs-5 flex-shrink-0" style="width:56px;height:56px">{{ $e['initials'] }}</span>
                <div class="flex-grow-1">
                    <h5 class="mb-0">{{ $e['employee_name'] }}</h5>
                    <div class="small text-muted">{{ $e['employee_no'] }} · {{ $e['department'] }}</div>
                    <div class="small text-muted">{{ $e['plan_name'] }} <span class="badge bg-secondary-subtle text-secondary-emphasis ms-1">{{ ucfirst($e['plan_status']) }}</span></div>
                </div>
                <div class="text-end">
                    @include('livewire.performance.dashboard.partials.status', ['status' => $e['status']])
                    @if($e['inactive'])
                        <div class="mt-1"><span class="badge bg-danger-subtle text-danger-emphasis"><i class="fa-solid fa-moon me-1"></i>Inactive</span></div>
                    @endif
                </div>
            </div>
            <div class="small text-muted mb-1">Plan achievement (weighted)</div>
            @include('livewire.performance.dashboard.partials.progress', ['progress' => $e['progress'], 'status' => $e['status'], 'expected' => $dashboard->expected])
            <div class="row text-center mt-3 g-2">
                <div class="col-4"><div class="fw-semibold">{{ $e['verified'] }}</div><div class="small text-muted">Verified entries</div></div>
                <div class="col-4"><div class="fw-semibold {{ $e['pending'] ? 'text-warning-emphasis' : '' }}">{{ $e['pending'] }}</div><div class="small text-muted">Pending</div></div>
                <div class="col-4"><div class="fw-semibold">{{ $e['rejected'] }}</div><div class="small text-muted">Rejected</div></div>
            </div>
        </div></div>
    </div>
    <div class="col-lg-5">
        <div class="card h-100"><div class="card-body">
            <div class="d-flex justify-content-between align-items-center mb-3">
                <h6 class="mb-0"><i class="fa-solid fa-star me-2 text-primary"></i>Evaluation</h6>
                <span class="badge bg-{{ $stage['color'] }}-subtle text-{{ $stage['color'] }}-emphasis">{{ $stage['label'] }}</span>
            </div>
            @foreach($scores as $label => $score)
                <div class="d-flex align-items-center gap-2 mb-2">
                    <span class="small {{ $label === 'Final' ? 'fw-semibold' : 'text-muted' }}" style="width: 90px">{{ $label }}</span>
                    <div class="pd-track bg-primary-subtle flex-grow-1">
                        @if($score !== null)<div class="pd-fill bg-primary" style="width: {{ min($score, 100) }}%"></div>@endif
                    </div>
                    <span class="small fw-semibold pd-pct">{{ $score === null ? '—' : number_format($score, 1) }}</span>
                </div>
            @endforeach
            @if($e['evaluation']['rating'])
                <div class="mt-2 small">Rating: <strong>{{ $e['evaluation']['rating'] }}</strong></div>
            @endif
        </div></div>
    </div>
</div>

<div class="row">
    <div class="col-xl-8">
        {{-- Goals --}}
        <div class="card mb-4">
            <div class="card-header bg-transparent">
                <h6 class="mb-0"><i class="fa-solid fa-bullseye me-2 text-primary"></i>Goals and measurable results</h6>
                <small class="text-muted">Only work a supervisor has verified counts as achieved.</small>
            </div>
            <div class="table-responsive">
                <table class="table align-middle mb-0">
                    <thead class="table-light small">
                        <tr>
                            <th>Goal</th>
                            <th class="text-end">Weight</th>
                            <th class="text-end">Target</th>
                            <th class="text-end">Achieved</th>
                            <th>Progress</th>
                            <th class="text-center" title="Verified / pending / rejected entries">Entries</th>
                            <th class="text-end" title="Average verifier score, 1-10">Quality</th>
                        </tr>
                    </thead>
                    <tbody>
                        @forelse($e['goals'] as $goal)
                            <tr wire:key="emp-goal-{{ $goal['id'] }}">
                                <td>
                                    <div class="fw-semibold">{{ $goal['name'] }}</div>
                                    <div class="small text-muted">
                                        @if($goal['parent_id'] && isset($e['parent_names'][$goal['parent_id']]))
                                            <i class="fa-solid fa-turn-up fa-rotate-90 me-1"></i>Serves: {{ $e['parent_names'][$goal['parent_id']] }}
                                        @else
                                            <i class="fa-solid fa-link-slash me-1"></i>Not linked to a department goal
                                        @endif
                                    </div>
                                    <div class="mt-1 d-flex align-items-center gap-2">
                                        @include('livewire.performance.dashboard.partials.smart', ['smart' => $goal['smart']])
                                        @include('livewire.performance.dashboard.partials.status', ['status' => $goal['status']])
                                    </div>
                                </td>
                                <td class="text-end">{{ $num($goal['weight']) }}%</td>
                                <td class="text-end text-nowrap">{{ $num($goal['target']) }} <span class="text-muted small">{{ $goal['unit'] }}</span></td>
                                <td class="text-end">{{ $num($goal['achieved']) }}</td>
                                <td class="pd-progress-cell">@include('livewire.performance.dashboard.partials.progress', ['progress' => $goal['progress'], 'status' => $goal['status'], 'expected' => $dashboard->expected])</td>
                                <td class="text-center text-nowrap small">
                                    <span class="text-success-emphasis" title="Verified">{{ $goal['verified'] }}</span> /
                                    <span class="text-warning-emphasis" title="Pending">{{ $goal['pending'] }}</span> /
                                    <span class="text-danger-emphasis" title="Rejected">{{ $goal['rejected'] }}</span>
                                </td>
                                <td class="text-end">{{ $goal['quality'] === null ? '—' : number_format($goal['quality'], 1).'/10' }}</td>
                            </tr>
                        @empty
                            <tr><td colspan="7" class="text-center text-muted py-4">No goals in this plan yet.</td></tr>
                        @endforelse
                    </tbody>
                </table>
            </div>
        </div>
    </div>

    <div class="col-xl-4">
        {{-- Recent implementation --}}
        <div class="card mb-4">
            <div class="card-header bg-transparent">
                <h6 class="mb-0"><i class="fa-solid fa-clock-rotate-left me-2 text-primary"></i>Recent implementation</h6>
            </div>
            <div class="card-body p-0">
                @forelse($e['recent'] as $entry)
                    <div class="px-3 py-2 border-bottom" wire:key="entry-{{ $entry->id }}">
                        <div class="d-flex justify-content-between gap-2">
                            <span class="fw-semibold small">{{ $entry->activity_title }}</span>
                            <span class="badge bg-{{ $entryBadge[$entry->status] ?? 'secondary' }}-subtle text-{{ $entryBadge[$entry->status] ?? 'secondary' }}-emphasis align-self-start">{{ ucfirst($entry->status) }}</span>
                        </div>
                        <div class="small text-muted">
                            {{ $entry->implementation_date?->format('d M Y') }} · {{ $entry->employeePlanItem?->item_name }}
                            @if($entry->quantity_achieved !== null) · {{ $num($entry->quantity_achieved) }} {{ $entry->unit }} @endif
                            @if($entry->verification_score) · {{ $entry->verification_score }}/10 @endif
                            @if($entry->evidence_path)
                                · <a href="{{ route('performance.evidence', $entry->id) }}" target="_blank" rel="noopener"><i class="fa-solid fa-paperclip"></i> Evidence</a>
                            @endif
                        </div>
                    </div>
                @empty
                    <div class="px-3 py-3 small text-muted">No work has been logged against this plan yet.</div>
                @endforelse
            </div>
        </div>

        @include('livewire.performance.dashboard.partials.quality', ['smart' => $e['smart'], 'issues' => $e['issues']])
    </div>
</div>
