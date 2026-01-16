<div>
    <div class="row mb-4">
        <div class="col-12">
            <div class="d-md-flex justify-content-between align-items-center">
                <div>
                    <h4 class="mb-1">Performance Overview</h4>
                    <p class="text-muted mb-0">Track your performance, planning progress, and achievements</p>
                </div>
            </div>
        </div>
    </div>

    @if(!$hasEmployeeRecord)
        <div class="alert alert-warning">
            <i class="fa-solid fa-exclamation-triangle me-2"></i>
            Your user account is not linked to an employee record. Please contact HR.
        </div>
    @else
        {{-- Employee Info & Ranking Card --}}
        <div class="row mb-4">
            <div class="col-lg-8">
                <div class="card h-100">
                    <div class="card-body">
                        <div class="d-flex align-items-center">
                            <div class="flex-shrink-0">
                                @if($employee->photo)
                                    <img src="{{ asset('storage/' . $employee->photo) }}" alt="Profile" class="rounded-circle" style="width: 80px; height: 80px; object-fit: cover;">
                                @else
                                    <div class="bg-primary rounded-circle d-flex align-items-center justify-content-center" style="width: 80px; height: 80px;">
                                        <span class="text-white fs-3">{{ substr($employee->first_name, 0, 1) }}{{ substr($employee->last_name, 0, 1) }}</span>
                                    </div>
                                @endif
                            </div>
                            <div class="flex-grow-1 ms-3">
                                <h4 class="mb-1">{{ $employee->getFullName() }}</h4>
                                <p class="text-muted mb-0">
                                    {{ $employee->position->name ?? 'N/A' }} |
                                    {{ $employee->department->name ?? 'N/A' }}
                                </p>
                                <small class="text-muted">Employee No: {{ $employee->employee_no }}</small>
                            </div>
                            <div class="text-end">
                                <div class="d-flex align-items-center gap-3">
                                    {{-- Overall Progress Circle --}}
                                    <div class="text-center">
                                        <div class="position-relative d-inline-block">
                                            <svg width="80" height="80" viewBox="0 0 36 36">
                                                <path d="M18 2.0845 a 15.9155 15.9155 0 0 1 0 31.831 a 15.9155 15.9155 0 0 1 0 -31.831"
                                                      fill="none" stroke="#e9ecef" stroke-width="3"/>
                                                <path d="M18 2.0845 a 15.9155 15.9155 0 0 1 0 31.831 a 15.9155 15.9155 0 0 1 0 -31.831"
                                                      fill="none" stroke="{{ $planningStats['overall_progress'] >= 80 ? '#198754' : ($planningStats['overall_progress'] >= 50 ? '#0dcaf0' : '#ffc107') }}"
                                                      stroke-width="3" stroke-dasharray="{{ $planningStats['overall_progress'] }}, 100"/>
                                                <text x="18" y="20.5" text-anchor="middle" font-size="8" fill="#333">{{ $planningStats['overall_progress'] }}%</text>
                                            </svg>
                                        </div>
                                        <small class="d-block text-muted">Overall Progress</small>
                                    </div>
                                    {{-- Department Ranking --}}
                                    <div class="text-center border-start ps-3">
                                        <h3 class="mb-0 text-primary">#{{ $ranking['rank'] }}</h3>
                                        <small class="text-muted">of {{ $ranking['total_employees'] }} in Dept</small>
                                        <div class="mt-1">
                                            <span class="badge bg-{{ $ranking['percentile'] >= 75 ? 'success' : ($ranking['percentile'] >= 50 ? 'info' : 'warning') }}">
                                                Top {{ 100 - $ranking['percentile'] }}%
                                            </span>
                                        </div>
                                    </div>
                                </div>
                            </div>
                        </div>
                    </div>
                </div>
            </div>
            <div class="col-lg-4">
                <div class="card h-100 bg-gradient" style="background: linear-gradient(135deg, #667eea 0%, #764ba2 100%);">
                    <div class="card-body text-white">
                        <h6 class="text-white-50 mb-3"><i class="fa-solid fa-star me-2"></i>Latest Evaluation</h6>
                        @if($evaluationStats['latest_evaluation'])
                            <div class="d-flex justify-content-between align-items-center mb-2">
                                <span>{{ $evaluationStats['latest_evaluation']->employeePlan->plan_name ?? 'N/A' }}</span>
                                <span class="badge bg-{{ $evaluationStats['latest_evaluation']->status === 'approved' ? 'success' : ($evaluationStats['latest_evaluation']->status === 'rejected' ? 'danger' : 'warning') }}">
                                    {{ ucfirst($evaluationStats['latest_evaluation']->status) }}
                                </span>
                            </div>
                            @if($evaluationStats['latest_evaluation']->final_score)
                                <h2 class="mb-0">{{ number_format($evaluationStats['latest_evaluation']->final_score, 1) }}%</h2>
                                <small class="text-white-50">Final Score</small>
                            @else
                                <h4 class="mb-0">{{ number_format($evaluationStats['latest_evaluation']->self_score ?? 0, 1) }}%</h4>
                                <small class="text-white-50">Self Score (Pending Review)</small>
                            @endif
                        @else
                            <p class="mb-0">No evaluations yet</p>
                            <small class="text-white-50">Submit a plan for evaluation</small>
                        @endif
                        <div class="mt-3">
                            <small>Avg Score: <strong>{{ $evaluationStats['average_score'] }}%</strong> | Total: <strong>{{ $evaluationStats['total_evaluations'] }}</strong></small>
                        </div>
                    </div>
                </div>
            </div>
        </div>

        {{-- Quick Stats Row --}}
        <div class="row mb-4">
            <div class="col-md-3 col-sm-6 mb-3">
                <div class="card border-start border-primary border-4 h-100">
                    <div class="card-body">
                        <div class="d-flex justify-content-between">
                            <div>
                                <h6 class="text-muted mb-2">Total Plans</h6>
                                <h3 class="mb-0">{{ $planningStats['total_plans'] }}</h3>
                                <small class="text-success">{{ $planningStats['active_plans'] }} Active</small>
                            </div>
                            <div class="align-self-center">
                                <i class="fa-solid fa-bullseye fa-2x text-primary opacity-50"></i>
                            </div>
                        </div>
                    </div>
                </div>
            </div>
            <div class="col-md-3 col-sm-6 mb-3">
                <div class="card border-start border-success border-4 h-100">
                    <div class="card-body">
                        <div class="d-flex justify-content-between">
                            <div>
                                <h6 class="text-muted mb-2">Implementations</h6>
                                <h3 class="mb-0">{{ $implementationStats['total_implementations'] }}</h3>
                                <small class="text-info">{{ $implementationStats['this_month'] }} This Month</small>
                            </div>
                            <div class="align-self-center">
                                <i class="fa-solid fa-tasks fa-2x text-success opacity-50"></i>
                            </div>
                        </div>
                    </div>
                </div>
            </div>
            <div class="col-md-3 col-sm-6 mb-3">
                <div class="card border-start border-info border-4 h-100">
                    <div class="card-body">
                        <div class="d-flex justify-content-between">
                            <div>
                                <h6 class="text-muted mb-2">Assigned Duties</h6>
                                <h3 class="mb-0">{{ $dutiesStats['total'] }}</h3>
                                <small class="{{ $dutiesStats['overdue'] > 0 ? 'text-danger' : 'text-success' }}">
                                    @if($dutiesStats['overdue'] > 0)
                                        {{ $dutiesStats['overdue'] }} Overdue
                                    @else
                                        {{ $dutiesStats['completed'] }} Completed
                                    @endif
                                </small>
                            </div>
                            <div class="align-self-center">
                                <i class="fa-solid fa-clipboard-list fa-2x text-info opacity-50"></i>
                            </div>
                        </div>
                    </div>
                </div>
            </div>
            <div class="col-md-3 col-sm-6 mb-3">
                <div class="card border-start border-warning border-4 h-100">
                    <div class="card-body">
                        <div class="d-flex justify-content-between">
                            <div>
                                <h6 class="text-muted mb-2">Attendance Rate</h6>
                                <h3 class="mb-0">{{ $attendanceStats['attendance_rate'] }}%</h3>
                                <small class="text-muted">{{ $attendanceStats['present_days'] }}/{{ $attendanceStats['total_days'] }} Days</small>
                            </div>
                            <div class="align-self-center">
                                <i class="fa-solid fa-clock fa-2x text-warning opacity-50"></i>
                            </div>
                        </div>
                    </div>
                </div>
            </div>
        </div>

        {{-- Plans Progress Section --}}
        <div class="row mb-4">
            <div class="col-lg-8">
                <div class="card h-100">
                    <div class="card-header d-flex justify-content-between align-items-center">
                        <h6 class="mb-0"><i class="fa-solid fa-chart-line me-2"></i>Plans Implementation Progress</h6>
                        <a href="{{ route('performance.myplanning') }}" class="btn btn-sm btn-outline-primary">View All</a>
                    </div>
                    <div class="card-body">
                        @if($activePlans->count() > 0)
                            @foreach($activePlans as $plan)
                                <div class="mb-4">
                                    <div class="d-flex justify-content-between align-items-center mb-2">
                                        <div>
                                            <strong>{{ $plan->plan_name }}</strong>
                                            <span class="badge bg-{{ $plan->status === 'active' ? 'success' : 'secondary' }} ms-2">{{ ucfirst($plan->status) }}</span>
                                        </div>
                                        <div class="text-end">
                                            <span class="badge bg-primary">{{ $plan->goal_count }} Goals</span>
                                            <small class="text-muted ms-2">{{ $plan->days_active }} days</small>
                                        </div>
                                    </div>
                                    <div class="progress" style="height: 25px;">
                                        <div class="progress-bar {{ $plan->calculated_progress >= 100 ? 'bg-success' : ($plan->calculated_progress >= 75 ? 'bg-info' : ($plan->calculated_progress >= 50 ? 'bg-warning' : 'bg-danger')) }}"
                                             style="width: {{ $plan->calculated_progress }}%">
                                            <span class="fw-bold">{{ $plan->calculated_progress }}%</span>
                                        </div>
                                    </div>
                                    <div class="d-flex justify-content-between mt-1">
                                        <small class="text-muted">Started: {{ $plan->assigned_at ? $plan->assigned_at->format('d M Y') : $plan->created_at->format('d M Y') }}</small>
                                        <small class="text-{{ $plan->calculated_progress >= 80 ? 'success' : ($plan->calculated_progress >= 50 ? 'info' : 'warning') }}">
                                            @if($plan->calculated_progress >= 100)
                                                <i class="fa-solid fa-check-circle"></i> Target Achieved
                                            @elseif($plan->calculated_progress >= 80)
                                                <i class="fa-solid fa-arrow-up"></i> On Track
                                            @elseif($plan->calculated_progress >= 50)
                                                <i class="fa-solid fa-minus"></i> Moderate Progress
                                            @else
                                                <i class="fa-solid fa-arrow-down"></i> Needs Attention
                                            @endif
                                        </small>
                                    </div>
                                </div>
                            @endforeach
                        @else
                            <div class="text-center text-muted py-4">
                                <i class="fa-solid fa-bullseye fa-3x mb-3 opacity-50"></i>
                                <p class="mb-0">No active plans yet</p>
                                <a href="{{ route('performance.myplanning') }}" class="btn btn-primary btn-sm mt-2">Create a Plan</a>
                            </div>
                        @endif
                    </div>
                </div>
            </div>
            <div class="col-lg-4">
                <div class="card h-100">
                    <div class="card-header">
                        <h6 class="mb-0"><i class="fa-solid fa-history me-2"></i>Implementation Timeline</h6>
                    </div>
                    <div class="card-body">
                        @if($implementationStats['days_since_first'] > 0)
                            <div class="text-center mb-3">
                                <div class="display-4 text-primary">{{ $implementationStats['days_since_first'] }}</div>
                                <small class="text-muted">Days Since First Implementation</small>
                            </div>
                        @endif

                        <div class="row text-center mb-3">
                            <div class="col-6 border-end">
                                <h4 class="text-success mb-0">{{ $implementationStats['verified'] }}</h4>
                                <small class="text-muted">Verified</small>
                            </div>
                            <div class="col-6">
                                <h4 class="text-warning mb-0">{{ $implementationStats['pending_verification'] }}</h4>
                                <small class="text-muted">Pending</small>
                            </div>
                        </div>

                        @if($implementationStats['recent_implementations']->count() > 0)
                            <h6 class="border-bottom pb-2 mb-2">Recent Activities</h6>
                            <ul class="list-unstyled mb-0">
                                @foreach($implementationStats['recent_implementations'] as $impl)
                                    <li class="mb-2 pb-2 border-bottom">
                                        <div class="d-flex justify-content-between">
                                            <small><strong>{{ Str::limit($impl->activity_title, 25) }}</strong></small>
                                            <span class="badge bg-{{ $impl->status === 'verified' ? 'success' : 'warning' }} badge-sm">{{ ucfirst($impl->status) }}</span>
                                        </div>
                                        <small class="text-muted">{{ Carbon\Carbon::parse($impl->implementation_date)->format('d M Y') }}</small>
                                    </li>
                                @endforeach
                            </ul>
                        @else
                            <div class="text-center text-muted py-3">
                                <p class="mb-0">No implementations yet</p>
                            </div>
                        @endif
                        <div class="mt-3">
                            <a href="{{ route('performance.myimplementation') }}" class="btn btn-outline-primary btn-sm w-100">
                                <i class="fa-solid fa-plus me-1"></i>Record Implementation
                            </a>
                        </div>
                    </div>
                </div>
            </div>
        </div>

        {{-- Duties & Attendance Row --}}
        <div class="row mb-4">
            {{-- Assigned Duties Overview --}}
            <div class="col-lg-6">
                <div class="card h-100">
                    <div class="card-header d-flex justify-content-between align-items-center">
                        <h6 class="mb-0"><i class="fa-solid fa-clipboard-list me-2"></i>Assigned Duties Overview</h6>
                        <a href="{{ route('performance.myduties') }}" class="btn btn-sm btn-outline-primary">View All</a>
                    </div>
                    <div class="card-body">
                        {{-- Duties Progress Bar --}}
                        <div class="mb-4">
                            <div class="d-flex justify-content-between mb-2">
                                <span>Completion Rate</span>
                                <strong>{{ $dutiesStats['completion_rate'] }}%</strong>
                            </div>
                            <div class="progress" style="height: 10px;">
                                <div class="progress-bar bg-success" style="width: {{ $dutiesStats['completion_rate'] }}%"></div>
                            </div>
                        </div>

                        {{-- Duties Stats --}}
                        <div class="row text-center mb-3">
                            <div class="col-3">
                                <div class="border rounded p-2">
                                    <h5 class="mb-0 text-warning">{{ $dutiesStats['assigned'] }}</h5>
                                    <small class="text-muted">Assigned</small>
                                </div>
                            </div>
                            <div class="col-3">
                                <div class="border rounded p-2">
                                    <h5 class="mb-0 text-info">{{ $dutiesStats['in_progress'] }}</h5>
                                    <small class="text-muted">In Progress</small>
                                </div>
                            </div>
                            <div class="col-3">
                                <div class="border rounded p-2">
                                    <h5 class="mb-0 text-success">{{ $dutiesStats['completed'] }}</h5>
                                    <small class="text-muted">Completed</small>
                                </div>
                            </div>
                            <div class="col-3">
                                <div class="border rounded p-2">
                                    <h5 class="mb-0 text-danger">{{ $dutiesStats['overdue'] }}</h5>
                                    <small class="text-muted">Overdue</small>
                                </div>
                            </div>
                        </div>

                        {{-- Recent Duties List --}}
                        @if($dutiesStats['recent_duties']->count() > 0)
                            <h6 class="border-bottom pb-2 mb-2">Priority Duties</h6>
                            <div class="list-group list-group-flush">
                                @foreach($dutiesStats['recent_duties'] as $duty)
                                    <div class="list-group-item px-0 py-2">
                                        <div class="d-flex justify-content-between align-items-center">
                                            <div>
                                                <span class="badge bg-{{ $duty->priority === 'urgent' ? 'danger' : ($duty->priority === 'high' ? 'warning' : 'secondary') }} me-2">
                                                    {{ ucfirst($duty->priority) }}
                                                </span>
                                                <span>{{ Str::limit($duty->duty_name, 30) }}</span>
                                            </div>
                                            <span class="badge bg-{{ $duty->status === 'completed' ? 'success' : ($duty->status === 'in_progress' ? 'info' : 'secondary') }}">
                                                {{ ucfirst(str_replace('_', ' ', $duty->status)) }}
                                            </span>
                                        </div>
                                        @if($duty->end_date)
                                            <small class="text-{{ $duty->end_date->isPast() && $duty->status !== 'completed' ? 'danger' : 'muted' }}">
                                                Due: {{ $duty->end_date->format('d M Y') }}
                                                @if($duty->end_date->isPast() && $duty->status !== 'completed')
                                                    (Overdue)
                                                @endif
                                            </small>
                                        @endif
                                    </div>
                                @endforeach
                            </div>
                        @else
                            <div class="text-center text-muted py-3">
                                <i class="fa-solid fa-check-circle fa-2x mb-2 text-success"></i>
                                <p class="mb-0">No pending duties</p>
                            </div>
                        @endif
                    </div>
                </div>
            </div>

            {{-- Attendance Overview --}}
            <div class="col-lg-6">
                <div class="card h-100">
                    <div class="card-header">
                        <h6 class="mb-0"><i class="fa-solid fa-clock me-2"></i>Attendance This Month</h6>
                    </div>
                    <div class="card-body">
                        @if($attendanceStats['total_days'] > 0)
                            {{-- Attendance Rate Circle --}}
                            <div class="text-center mb-4">
                                <div class="position-relative d-inline-block">
                                    <svg width="120" height="120" viewBox="0 0 36 36">
                                        <path d="M18 2.0845 a 15.9155 15.9155 0 0 1 0 31.831 a 15.9155 15.9155 0 0 1 0 -31.831"
                                              fill="none" stroke="#e9ecef" stroke-width="3"/>
                                        <path d="M18 2.0845 a 15.9155 15.9155 0 0 1 0 31.831 a 15.9155 15.9155 0 0 1 0 -31.831"
                                              fill="none" stroke="{{ $attendanceStats['attendance_rate'] >= 90 ? '#198754' : ($attendanceStats['attendance_rate'] >= 75 ? '#0dcaf0' : '#ffc107') }}"
                                              stroke-width="3" stroke-dasharray="{{ $attendanceStats['attendance_rate'] }}, 100"/>
                                        <text x="18" y="20.5" text-anchor="middle" font-size="7" fill="#333" font-weight="bold">{{ $attendanceStats['attendance_rate'] }}%</text>
                                    </svg>
                                </div>
                                <p class="mb-0 mt-2 text-muted">Attendance Rate</p>
                            </div>

                            {{-- Attendance Stats --}}
                            <div class="row text-center mb-3">
                                <div class="col-4">
                                    <div class="border rounded p-2 bg-light">
                                        <h5 class="mb-0 text-success">{{ $attendanceStats['present_days'] }}</h5>
                                        <small class="text-muted">Present</small>
                                    </div>
                                </div>
                                <div class="col-4">
                                    <div class="border rounded p-2 bg-light">
                                        <h5 class="mb-0 text-danger">{{ $attendanceStats['absent_days'] }}</h5>
                                        <small class="text-muted">Absent</small>
                                    </div>
                                </div>
                                <div class="col-4">
                                    <div class="border rounded p-2 bg-light">
                                        <h5 class="mb-0 text-warning">{{ $attendanceStats['late_days'] }}</h5>
                                        <small class="text-muted">Late</small>
                                    </div>
                                </div>
                            </div>

                            {{-- Recent Attendance --}}
                            @if($attendanceStats['recent_attendance']->count() > 0)
                                <h6 class="border-bottom pb-2 mb-2">Recent Check-ins</h6>
                                <div class="table-responsive">
                                    <table class="table table-sm mb-0">
                                        <thead class="table-light">
                                            <tr>
                                                <th>Date</th>
                                                <th>Time</th>
                                                <th>Status</th>
                                            </tr>
                                        </thead>
                                        <tbody>
                                            @foreach($attendanceStats['recent_attendance']->take(5) as $record)
                                                <tr>
                                                    <td><small>{{ Carbon\Carbon::parse($record->clockdate)->format('d M') }}</small></td>
                                                    <td><small>{{ $record->clocktime }}</small></td>
                                                    <td>
                                                        <span class="badge bg-{{ $record->clock_status === 'in' ? 'success' : 'secondary' }}">
                                                            {{ ucfirst($record->clock_status ?? 'check') }}
                                                        </span>
                                                    </td>
                                                </tr>
                                            @endforeach
                                        </tbody>
                                    </table>
                                </div>
                            @endif
                        @else
                            <div class="text-center text-muted py-5">
                                <i class="fa-solid fa-fingerprint fa-3x mb-3 opacity-50"></i>
                                <p class="mb-0">No attendance records</p>
                                <small>Your fingerprint may not be registered</small>
                            </div>
                        @endif
                    </div>
                </div>
            </div>
        </div>

        {{-- Quick Actions --}}
        <div class="row">
            <div class="col-12">
                <div class="card">
                    <div class="card-body">
                        <h6 class="mb-3"><i class="fa-solid fa-bolt me-2"></i>Quick Actions</h6>
                        <div class="d-flex flex-wrap gap-2">
                            <a href="{{ route('performance.myplanning') }}" class="btn btn-outline-primary">
                                <i class="fa-solid fa-bullseye me-1"></i>My Planning
                            </a>
                            <a href="{{ route('performance.myimplementation') }}" class="btn btn-outline-success">
                                <i class="fa-solid fa-tasks me-1"></i>Record Implementation
                            </a>
                            <a href="{{ route('performance.myevaluation') }}" class="btn btn-outline-info">
                                <i class="fa-solid fa-star me-1"></i>My Evaluations
                            </a>
                            <a href="{{ route('performance.myduties') }}" class="btn btn-outline-warning">
                                <i class="fa-solid fa-clipboard-list me-1"></i>My Duties
                            </a>
                        </div>
                    </div>
                </div>
            </div>
        </div>
    @endif
</div>
