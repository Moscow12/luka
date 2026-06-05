<div>
    <!-- Breadcrumb -->
    <x-pages.breadcrumn title="Staff Attendance"
        :breadcrumbs="[
            ['label' => 'Home', 'url' => route('dashboard')],
            ['label' => 'Staff List', 'url' => route('hr.stafflist')],
            ['label' => $getfullname, 'url' => route('hr.staffdetails', $employee_id)],
            ['label' => 'Attendance']
        ]">
        <a href="{{ route('hr.stafflist') }}" class="btn btn-sm btn-outline-primary">
            <i class="fa-solid fa-arrow-left me-1"></i>Back to List
        </a>
    </x-pages.breadcrumn>

    <!-- Employee Header -->
    <x-pages.empheader
        :employee_id="$employee_id"
        :photo="$photo"
        :getFullName="$getfullname"
        :age="$age"
        :gender="$gender"
        :email="$email"
        :editUrl="$editUrl"
        :department="$department"
        :designation="$designation"
        :employeeNumber="$employeeNumber" />

    <!-- Page Title -->
    <div class="mb-4">
        <h5 class="mb-1">Attendance Records</h5>
        <p class="text-muted mb-0 small">Track and manage employee attendance</p>
    </div>

    <!-- Alert Messages -->
    @if (session()->has('success'))
        <div class="alert alert-success alert-dismissible fade show mb-4" role="alert">
            <i class="fa-solid fa-check-circle me-2"></i>{{ session('success') }}
            <button type="button" class="btn-close" data-bs-dismiss="alert"></button>
        </div>
    @endif

    @if (session()->has('error'))
        <div class="alert alert-danger alert-dismissible fade show mb-4" role="alert">
            <i class="fa-solid fa-exclamation-circle me-2"></i>{{ session('error') }}
            <button type="button" class="btn-close" data-bs-dismiss="alert"></button>
        </div>
    @endif

    <!-- No Fingerprint Warning -->
    @if(!$fpid)
        <div class="alert alert-warning mb-4">
            <div class="d-flex align-items-center gap-3">
                <i class="fa-solid fa-fingerprint fa-2x"></i>
                <div>
                    <h6 class="mb-1">Fingerprint ID Not Assigned</h6>
                    <p class="mb-0 small">This employee doesn't have a fingerprint ID assigned. Attendance tracking requires a fingerprint device enrollment.</p>
                </div>
            </div>
        </div>
    @endif

    <!-- Statistics Cards -->
    <div class="row g-3 mb-4">
        <div class="col-6 col-md-4 col-lg">
            <div class="card border-0 bg-success bg-opacity-10 h-100">
                <div class="card-body text-center py-3">
                    <div class="fs-3 fw-bold text-success">{{ $stats['present'] }}</div>
                    <small class="text-muted">Present</small>
                </div>
            </div>
        </div>
        <div class="col-6 col-md-4 col-lg">
            <div class="card border-0 bg-warning bg-opacity-10 h-100">
                <div class="card-body text-center py-3">
                    <div class="fs-3 fw-bold text-warning">{{ $stats['late'] }}</div>
                    <small class="text-muted">Late</small>
                </div>
            </div>
        </div>
        <div class="col-6 col-md-4 col-lg">
            <div class="card border-0 bg-secondary bg-opacity-10 h-100">
                <div class="card-body text-center py-3">
                    <div class="fs-3 fw-bold text-secondary">{{ $stats['incomplete'] }}</div>
                    <small class="text-muted">Incomplete</small>
                </div>
            </div>
        </div>
        <div class="col-6 col-md-4 col-lg">
            <div class="card border-0 bg-danger bg-opacity-10 h-100">
                <div class="card-body text-center py-3">
                    <div class="fs-3 fw-bold text-danger">{{ $stats['absent'] }}</div>
                    <small class="text-muted">Absent</small>
                </div>
            </div>
        </div>
        <div class="col-6 col-md-4 col-lg">
            <div class="card border-0 bg-info bg-opacity-10 h-100">
                <div class="card-body text-center py-3">
                    <div class="fs-3 fw-bold text-info">{{ $stats['leave'] }}</div>
                    <small class="text-muted">On Leave</small>
                </div>
            </div>
        </div>
    </div>

    <!-- Work Hours Summary -->
    <div class="row g-3 mb-4">
        <div class="col-md-4">
            <div class="card border-0 bg-primary bg-opacity-10 h-100">
                <div class="card-body d-flex align-items-center gap-3 py-3">
                    <i class="fa-solid fa-business-time fa-2x text-primary"></i>
                    <div>
                        <div class="fs-4 fw-bold text-primary">{{ $stats['total_hours'] }} hrs</div>
                        <small class="text-muted">Total Hours Worked</small>
                    </div>
                </div>
            </div>
        </div>
        <div class="col-md-4">
            <div class="card border-0 bg-primary bg-opacity-10 h-100">
                <div class="card-body d-flex align-items-center gap-3 py-3">
                    <i class="fa-solid fa-gauge-high fa-2x text-primary"></i>
                    <div>
                        <div class="fs-4 fw-bold text-primary">{{ $stats['avg_hours'] }} hrs</div>
                        <small class="text-muted">Avg Hours / Day ({{ $stats['worked_days'] }} days)</small>
                    </div>
                </div>
            </div>
        </div>
        <div class="col-md-4">
            <div class="card border-0 bg-light h-100">
                <div class="card-body d-flex align-items-center gap-3 py-3">
                    <i class="fa-solid fa-clock fa-2x text-secondary"></i>
                    <div>
                        <div class="fw-semibold">
                            Shift Start: {{ $shiftStart ? \Carbon\Carbon::parse($shiftStart)->format('h:i A') : '—' }}
                        </div>
                        <small class="text-muted d-block">
                            {{ $shiftName ?? 'System default' }} &middot; {{ $shiftGraceMinutes }} min grace
                        </small>
                        <small class="d-block">
                            @if($shiftSource === 'roster')
                                <span class="badge bg-success-subtle text-success">
                                    <i class="fa-solid fa-calendar-check me-1"></i>From roster
                                </span>
                            @elseif($shiftSource === 'default')
                                <span class="badge bg-info-subtle text-info" title="No roster found — using the default shift">
                                    <i class="fa-solid fa-star me-1"></i>Default shift (no roster)
                                </span>
                            @else
                                <span class="badge bg-secondary-subtle text-secondary" title="No roster and no default shift configured">
                                    <i class="fa-solid fa-clock me-1"></i>System default (no roster)
                                </span>
                            @endif
                        </small>
                    </div>
                </div>
            </div>
        </div>
    </div>

    <!-- Filters -->
    <div class="card mb-4">
        <div class="card-body py-3">
            <div class="row g-3 align-items-end">
                <div class="col-md-3">
                    <label class="form-label small text-muted">Month</label>
                    <select class="form-select form-select-sm" wire:model.live="filterMonth">
                        <option value="">All Months</option>
                        @for($m = 1; $m <= 12; $m++)
                            <option value="{{ str_pad($m, 2, '0', STR_PAD_LEFT) }}">
                                {{ date('F', mktime(0, 0, 0, $m, 1)) }}
                            </option>
                        @endfor
                    </select>
                </div>
                <div class="col-md-2">
                    <label class="form-label small text-muted">Year</label>
                    <select class="form-select form-select-sm" wire:model.live="filterYear">
                        @for($y = now()->year; $y >= now()->year - 5; $y--)
                            <option value="{{ $y }}">{{ $y }}</option>
                        @endfor
                    </select>
                </div>
                <div class="col-md-3">
                    <label class="form-label small text-muted">Status</label>
                    <select class="form-select form-select-sm" wire:model.live="filterStatus">
                        <option value="">All Status</option>
                        <option value="Present">Present</option>
                        <option value="Incomplete">Incomplete</option>
                        <option value="Absent">Absent</option>
                        <option value="Late">Late</option>
                        <option value="Half Day">Half Day</option>
                        <option value="On Leave">On Leave</option>
                    </select>
                </div>
                <div class="col-md-4 text-md-end">
                    <button class="btn btn-sm btn-outline-secondary" wire:click="clearFilters">
                        <i class="fa-solid fa-times me-1"></i>Clear Filters
                    </button>
                </div>
            </div>
        </div>
    </div>

    <!-- Attendance Table -->
    <div class="card">
        <div class="table-responsive">
            <table class="table table-hover align-middle mb-0">
                <thead class="table-light">
                    <tr>
                        <th class="ps-4" style="width: 50px;">#</th>
                        <th>Date</th>
                        <th>Day</th>
                        <th>Clock In</th>
                        <th>Clock Out</th>
                        <th>Duration</th>
                        <th>Status</th>
                    </tr>
                </thead>
                <tbody>
                    @forelse($attendances as $index => $record)
                        <tr wire:key="attendance-{{ $record['id'] }}">
                            <td class="ps-4">{{ $attendances->firstItem() + $index }}</td>
                            <td>
                                <span class="fw-medium">{{ \Carbon\Carbon::parse($record['date'])->format('d M, Y') }}</span>
                            </td>
                            <td>
                                <span class="text-muted">{{ \Carbon\Carbon::parse($record['date'])->format('l') }}</span>
                            </td>
                            <td>
                                @if($record['clock_in'])
                                    <span class="badge bg-light text-dark">
                                        <i class="fa-solid fa-right-to-bracket text-success me-1"></i>
                                        {{ \Carbon\Carbon::parse($record['clock_in'])->format('h:i A') }}
                                    </span>
                                @else
                                    <span class="text-muted">-</span>
                                @endif
                            </td>
                            <td>
                                @if($record['clock_out'])
                                    <span class="badge bg-light text-dark">
                                        <i class="fa-solid fa-right-from-bracket text-danger me-1"></i>
                                        {{ \Carbon\Carbon::parse($record['clock_out'])->format('h:i A') }}
                                    </span>
                                @else
                                    <span class="text-muted">-</span>
                                @endif
                            </td>
                            <td>
                                @if($record['clock_in'] && $record['clock_out'])
                                    @php
                                        $duration = \Carbon\Carbon::parse($record['clock_in'])->diff(\Carbon\Carbon::parse($record['clock_out']));
                                    @endphp
                                    <span class="text-muted">{{ $duration->format('%Hh %Im') }}</span>
                                @else
                                    <span class="text-muted">-</span>
                                @endif
                            </td>
                            <td>
                                @php
                                    $statusClass = match($record['clock_status']) {
                                        'Present' => 'bg-success',
                                        'Absent' => 'bg-danger',
                                        'Late' => 'bg-warning',
                                        'Half Day' => 'bg-info',
                                        'On Leave' => 'bg-secondary',
                                        'Incomplete' => 'bg-warning',
                                        default => 'bg-light text-dark'
                                    };
                                @endphp
                                <span class="badge {{ $statusClass }}">{{ $record['clock_status'] ?? 'N/A' }}</span>
                                @if(($record['punches'] ?? 0) > 0)
                                    <span class="badge bg-light text-dark ms-1" title="Punches recorded">
                                        <i class="fa-solid fa-fingerprint me-1"></i>{{ $record['punches'] }}
                                    </span>
                                @endif
                            </td>
                        </tr>
                    @empty
                        <tr>
                            <td colspan="7" class="text-center py-5">
                                <div class="d-flex flex-column align-items-center">
                                    <div class="bg-light rounded-circle d-flex align-items-center justify-content-center mb-3"
                                        style="width: 80px; height: 80px;">
                                        <i class="fa-solid fa-calendar-xmark fa-2x text-muted"></i>
                                    </div>
                                    <h6 class="mb-1">No attendance records found</h6>
                                    <p class="text-muted mb-0 small">
                                        @if($filterMonth || $filterStatus)
                                            No records match your filter criteria
                                        @else
                                            No attendance records available for this employee
                                        @endif
                                    </p>
                                </div>
                            </td>
                        </tr>
                    @endforelse
                </tbody>
            </table>
        </div>

        @if($attendances instanceof \Illuminate\Pagination\LengthAwarePaginator && $attendances->hasPages())
            <div class="card-footer border-top">
                {{ $attendances->links() }}
            </div>
        @endif
    </div>
</div>
