<div class="custom-container">
    <x-pages.breadcrumn title="ATTENDANCE CALENDAR" :breadcrumbs="[
        ['label' => 'Home', 'url' => route('dashboard')],
        ['label' => 'HR Management', 'url' => '#'],
        ['label' => 'Attendance', 'url' => '#'],
        ['label' => 'Attendance Calendar', 'url' => '#'],
    ]">
    </x-pages.breadcrumn>

    {{-- Filters Card --}}
    <div class="card card-lg mb-4">
        <div class="card-body">
            <div class="row g-3 align-items-end">
                {{-- Date Range --}}
                <div class="col-12 col-md-3">
                    <label class="form-label fw-semibold small text-muted">FROM DATE</label>
                    <input type="date"
                           wire:model.live="dateFrom"
                           class="form-control"
                           max="{{ now()->format('Y-m-d') }}">
                </div>
                <div class="col-12 col-md-3">
                    <label class="form-label fw-semibold small text-muted">TO DATE</label>
                    <input type="date"
                           wire:model.live="dateTo"
                           class="form-control"
                           max="{{ now()->format('Y-m-d') }}">
                </div>

                {{-- Search --}}
                <div class="col-12 col-md-4">
                    <label class="form-label fw-semibold small text-muted">SEARCH EMPLOYEE</label>
                    <div class="input-group">
                        <span class="input-group-text bg-white">
                            <i class="fa-solid fa-magnifying-glass text-muted"></i>
                        </span>
                        <input type="text"
                               wire:model.live.debounce.300ms="search"
                               class="form-control"
                               placeholder="Search by name or FP ID...">
                        @if($search)
                            <button wire:click="$set('search', '')" class="btn btn-outline-secondary" type="button">
                                <i class="fa-solid fa-times"></i>
                            </button>
                        @endif
                    </div>
                </div>

                {{-- Clear Filters --}}
                <div class="col-12 col-md-2">
                    <button wire:click="clearFilters" class="btn btn-outline-secondary w-100">
                        <i class="fa-solid fa-rotate-left me-1"></i> Reset
                    </button>
                </div>
            </div>
        </div>
    </div>

    {{-- Attendance Calendar Table --}}
    <div class="card card-lg">
        <div class="card-header border-bottom bg-white">
            <div class="d-flex justify-content-between align-items-center">
                <h5 class="mb-0">
                    <i class="fa-solid fa-calendar-days me-2 text-primary"></i>
                    Attendance Calendar
                    <span class="badge bg-primary-subtle text-primary ms-2">{{ $totalUsers }} Employees</span>
                </h5>
                <div class="d-flex align-items-center gap-2">
                    <span class="badge bg-success"><i class="fa-solid fa-sign-in-alt me-1"></i> IN</span>
                    <span class="badge bg-warning text-dark"><i class="fa-solid fa-sign-out-alt me-1"></i> OUT</span>
                    <span class="badge bg-danger"><i class="fa-solid fa-ban me-1"></i> NO SHOW</span>
                </div>
            </div>
        </div>

        <div class="card-body p-0">
            @if(count($attendanceMatrix) > 0)
                <div class="attendance-table-wrapper">
                    <table class="table table-bordered table-hover mb-0 attendance-table">
                        <thead class="sticky-header">
                            <tr class="bg-light">
                                <th class="sticky-col sticky-col-1 text-center" style="min-width: 50px;">#</th>
                                <th class="sticky-col sticky-col-2 text-center" style="min-width: 80px;">FP ID</th>
                                <th class="sticky-col sticky-col-3" style="min-width: 180px;">Employee Name</th>
                                @foreach($dates as $date)
                                    <th class="text-center date-col" style="min-width: 220px;">
                                        <div class="fw-bold">{{ \Carbon\Carbon::parse($date)->format('Y-m-d') }}</div>
                                        <small class="text-muted">{{ \Carbon\Carbon::parse($date)->format('l') }}</small>
                                    </th>
                                @endforeach
                                <th class="sticky-col-rate text-center" style="min-width: 140px;"
                                    title="(Actual Hours Worked / Scheduled Hours) × 100, where scheduled = 8h × {{ $dates->count() }} day(s)">
                                    Attendance Rate
                                    <div><small class="text-muted fw-normal">/ {{ $scheduledHours }}h scheduled</small></div>
                                </th>
                                <th class="sticky-col-last text-center" style="min-width: 80px;">Action</th>
                            </tr>
                        </thead>
                        <tbody>
                            @foreach($attendanceMatrix as $row)
                                <tr wire:key="row-{{ $row['user']->id }}">
                                    <td class="sticky-col sticky-col-1 text-center bg-white">
                                        <span class="text-muted">{{ $row['index'] }}</span>
                                    </td>
                                    <td class="sticky-col sticky-col-2 text-center bg-white">
                                        <span class="fw-semibold">{{ $row['user']->fpdevice_id }}</span>
                                    </td>
                                    <td class="sticky-col sticky-col-3 bg-white">
                                        <span class="fw-semibold">{{ $row['user']->name }}</span>
                                    </td>
                                    @foreach($row['dates'] as $date => $attendance)
                                        <td class="text-center attendance-cell">
                                            @if($attendance['status'] === 'no_show')
                                                <span class="badge bg-danger px-3 py-2">NO SHOW</span>
                                            @else
                                                <div class="d-flex align-items-center justify-content-center gap-1 flex-wrap">
                                                    @if($attendance['clock_in'])
                                                        <span class="badge bg-success">
                                                            IN {{ \Carbon\Carbon::parse($attendance['clock_in'])->format('H:i:s') }}
                                                        </span>
                                                    @endif

                                                    @if($attendance['clock_in'] && ($attendance['clock_out'] || !$attendance['has_checkout']))
                                                        <span class="text-muted">~~</span>
                                                    @endif

                                                    @if($attendance['clock_out'] && $attendance['has_checkout'])
                                                        <span class="badge bg-warning text-dark">
                                                            OUT {{ \Carbon\Carbon::parse($attendance['clock_out'])->format('H:i:s') }}
                                                        </span>
                                                    @elseif($attendance['clock_in'] && !$attendance['has_checkout'])
                                                        <span class="badge bg-secondary">No Checkout</span>
                                                    @elseif($attendance['clock_out'] && !$attendance['clock_in'])
                                                        <span class="badge bg-warning text-dark">
                                                            OUT {{ \Carbon\Carbon::parse($attendance['clock_out'])->format('H:i:s') }}
                                                        </span>
                                                    @endif
                                                </div>
                                            @endif
                                        </td>
                                    @endforeach
                                    <td class="sticky-col-rate text-center bg-white">
                                        @php
                                            $rate = $row['attendance_rate'];
                                            $rateColor = $rate >= 90 ? 'success' : ($rate >= 60 ? 'warning' : 'danger');
                                        @endphp
                                        <div class="fw-bold text-{{ $rateColor }}">{{ $rate }}%</div>
                                        <div class="progress mx-auto" style="height: 5px; max-width: 100px;">
                                            <div class="progress-bar bg-{{ $rateColor }}" role="progressbar"
                                                 style="width: {{ min($rate, 100) }}%"></div>
                                        </div>
                                        <small class="text-muted">{{ $row['actual_hours'] }}h worked</small>
                                    </td>
                                    <td class="sticky-col-last text-center bg-white">
                                        <button wire:click="showScore('{{ $row['user']->fpdevice_id }}')"
                                                wire:loading.attr="disabled"
                                                class="btn btn-sm btn-info text-white">
                                            <i class="fa-solid fa-chart-simple me-1"></i> SCORE
                                        </button>
                                    </td>
                                </tr>
                            @endforeach
                        </tbody>
                    </table>
                </div>

                {{-- Pagination --}}
                <div class="card-footer bg-white border-top">
                    <div class="d-flex flex-column flex-md-row justify-content-between align-items-center gap-3">
                        <div class="text-muted">
                            Showing {{ ($currentPage - 1) * $perPage + 1 }} to {{ min($currentPage * $perPage, $totalUsers) }} of {{ $totalUsers }} employees
                        </div>
                        <div class="d-flex align-items-center gap-2">
                            <button wire:click="previousPage"
                                    class="btn btn-outline-primary btn-sm"
                                    {{ $currentPage <= 1 ? 'disabled' : '' }}>
                                <i class="fa-solid fa-chevron-left me-1"></i> Previous
                            </button>
                            <span class="badge bg-primary px-3 py-2">
                                Page {{ $currentPage }} of {{ max($totalPages, 1) }}
                            </span>
                            <button wire:click="nextPage"
                                    class="btn btn-outline-primary btn-sm"
                                    {{ $currentPage >= $totalPages ? 'disabled' : '' }}>
                                Next <i class="fa-solid fa-chevron-right ms-1"></i>
                            </button>
                        </div>
                    </div>
                </div>
            @else
                <div class="text-center py-5">
                    <i class="fa-solid fa-calendar-xmark fa-3x text-muted mb-3"></i>
                    <h5 class="text-muted">No Employees Found</h5>
                    <p class="text-muted">
                        @if($search)
                            No employees found matching "{{ $search }}"
                        @else
                            No fingerprint users registered yet.
                        @endif
                    </p>
                    @if($search)
                        <button wire:click="clearFilters" class="btn btn-primary">
                            <i class="fa-solid fa-rotate-left me-1"></i> Clear Search
                        </button>
                    @endif
                </div>
            @endif
        </div>
    </div>

    {{-- Score / Attendance Summary Modal --}}
    @if($showScoreModal && !empty($score))
        <div class="modal fade show d-block" tabindex="-1" style="background: rgba(0,0,0,0.5);" wire:key="score-modal">
            <div class="modal-dialog modal-lg modal-dialog-centered modal-dialog-scrollable">
                <div class="modal-content border-0 shadow">
                    <div class="modal-header bg-primary text-white">
                        <div>
                            <h5 class="modal-title fw-semibold mb-0">
                                <i class="fa-solid fa-chart-simple me-2"></i>Attendance Summary
                            </h5>
                            <small class="opacity-75">
                                {{ \Carbon\Carbon::parse($score['date_from'])->format('d M Y') }}
                                &ndash; {{ \Carbon\Carbon::parse($score['date_to'])->format('d M Y') }}
                            </small>
                        </div>
                        <button type="button" class="btn-close btn-close-white" wire:click="closeScore"></button>
                    </div>
                    <div class="modal-body">
                        {{-- Employee + shift header --}}
                        <div class="d-flex flex-wrap justify-content-between align-items-center gap-2 mb-4">
                            <div>
                                <div class="fw-bold fs-5">{{ $score['name'] }}</div>
                                <div class="text-muted small">
                                    <span class="badge bg-info-subtle text-info me-1">
                                        <i class="fa-solid fa-hashtag"></i> FP {{ $score['fp_id'] }}
                                    </span>
                                    @if($score['employee_no'])
                                        <span class="badge bg-secondary-subtle text-secondary">{{ $score['employee_no'] }}</span>
                                    @endif
                                    @unless($score['linked'])
                                        <span class="badge bg-warning-subtle text-warning">
                                            <i class="fa-solid fa-link-slash"></i> Not linked to employee
                                        </span>
                                    @endunless
                                </div>
                            </div>
                            <div class="text-md-end">
                                <div class="fw-semibold">
                                    <i class="fa-solid fa-clock text-secondary me-1"></i>
                                    Shift {{ $score['shift_start'] ? \Carbon\Carbon::parse($score['shift_start'])->format('h:i A') : '—' }}
                                    <small class="text-muted">&middot; {{ $score['grace'] }} min grace</small>
                                </div>
                                <small>
                                    @if($score['shift_source'] === 'roster')
                                        <span class="badge bg-success-subtle text-success"><i class="fa-solid fa-calendar-check me-1"></i>From roster</span>
                                    @elseif($score['shift_source'] === 'default')
                                        <span class="badge bg-info-subtle text-info"><i class="fa-solid fa-star me-1"></i>{{ $score['shift_name'] ?? 'Default shift' }} (no roster)</span>
                                    @else
                                        <span class="badge bg-secondary-subtle text-secondary"><i class="fa-solid fa-clock me-1"></i>System default</span>
                                    @endif
                                </small>
                            </div>
                        </div>

                        {{-- Status breakdown --}}
                        <div class="row g-3 mb-4">
                            <div class="col-6 col-md-4 col-lg">
                                <div class="card border-0 bg-success bg-opacity-10 h-100">
                                    <div class="card-body text-center py-3">
                                        <div class="fs-3 fw-bold text-success">{{ $score['present'] }}</div>
                                        <small class="text-muted">Present</small>
                                    </div>
                                </div>
                            </div>
                            <div class="col-6 col-md-4 col-lg">
                                <div class="card border-0 bg-warning bg-opacity-10 h-100">
                                    <div class="card-body text-center py-3">
                                        <div class="fs-3 fw-bold text-warning">{{ $score['late'] }}</div>
                                        <small class="text-muted">Late</small>
                                    </div>
                                </div>
                            </div>
                            <div class="col-6 col-md-4 col-lg">
                                <div class="card border-0 bg-secondary bg-opacity-10 h-100">
                                    <div class="card-body text-center py-3">
                                        <div class="fs-3 fw-bold text-secondary">{{ $score['incomplete'] }}</div>
                                        <small class="text-muted">Incomplete</small>
                                    </div>
                                </div>
                            </div>
                            <div class="col-6 col-md-4 col-lg">
                                <div class="card border-0 bg-danger bg-opacity-10 h-100">
                                    <div class="card-body text-center py-3">
                                        <div class="fs-3 fw-bold text-danger">{{ $score['no_show'] }}</div>
                                        <small class="text-muted">No Show</small>
                                    </div>
                                </div>
                            </div>
                            <div class="col-6 col-md-4 col-lg">
                                <div class="card border-0 bg-info bg-opacity-10 h-100">
                                    <div class="card-body text-center py-3">
                                        <div class="fs-3 fw-bold text-info">{{ $score['leave'] }}</div>
                                        <small class="text-muted">On Leave</small>
                                    </div>
                                </div>
                            </div>
                        </div>

                        {{-- Hours summary --}}
                        <div class="row g-3">
                            <div class="col-md-4">
                                <div class="card border-0 bg-primary bg-opacity-10 h-100">
                                    <div class="card-body d-flex align-items-center gap-3 py-3">
                                        <i class="fa-solid fa-business-time fa-2x text-primary"></i>
                                        <div>
                                            <div class="fs-4 fw-bold text-primary">{{ $score['total_hours'] }} hrs</div>
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
                                            <div class="fs-4 fw-bold text-primary">{{ $score['avg_hours'] }} hrs</div>
                                            <small class="text-muted">Avg Hours / Day ({{ $score['worked_days'] }} days)</small>
                                        </div>
                                    </div>
                                </div>
                            </div>
                            <div class="col-md-4">
                                <div class="card border-0 bg-light h-100">
                                    <div class="card-body d-flex align-items-center gap-3 py-3">
                                        <i class="fa-solid fa-calendar-day fa-2x text-secondary"></i>
                                        <div>
                                            <div class="fs-4 fw-bold">{{ $score['working_days'] }}</div>
                                            <small class="text-muted">Working Days (Mon–Fri)</small>
                                        </div>
                                    </div>
                                </div>
                            </div>
                        </div>
                    </div>
                    <div class="modal-footer">
                        <button type="button" class="btn btn-secondary" wire:click="closeScore">
                            <i class="fa-solid fa-xmark me-1"></i>Close
                        </button>
                    </div>
                </div>
            </div>
        </div>
    @endif

    <style>
        .attendance-table-wrapper {
            overflow: auto;
            max-height: 70vh;
            position: relative;
        }

        .attendance-table {
            border-collapse: separate;
            border-spacing: 0;
        }

        .attendance-table th,
        .attendance-table td {
            vertical-align: middle;
            white-space: nowrap;
            border: 1px solid #dee2e6;
        }

        /* Sticky header */
        .sticky-header th {
            position: sticky;
            top: 0;
            z-index: 10;
            background: #f8f9fa !important;
        }

        /* Sticky columns */
        .sticky-col {
            position: sticky;
            z-index: 5;
        }

        .sticky-col-1 {
            left: 0;
            min-width: 50px;
            max-width: 50px;
        }

        .sticky-col-2 {
            left: 50px;
            min-width: 80px;
            max-width: 80px;
        }

        .sticky-col-3 {
            left: 130px;
            min-width: 180px;
            max-width: 180px;
            border-right: 2px solid #adb5bd !important;
        }

        /* Sticky header + column intersection */
        .sticky-header .sticky-col {
            z-index: 15;
        }

        .sticky-col-last {
            position: sticky;
            right: 0;
            z-index: 5;
            background: #fff;
            border-left: 2px solid #adb5bd !important;
        }

        .sticky-header .sticky-col-last {
            z-index: 15;
            background: #f8f9fa !important;
        }

        /* Attendance Rate sticky column (sits left of the Action column) */
        .sticky-col-rate {
            position: sticky;
            right: 80px;
            z-index: 5;
            background: #fff;
            min-width: 140px;
            max-width: 140px;
            border-left: 2px solid #adb5bd !important;
        }

        .sticky-header .sticky-col-rate {
            z-index: 15;
            background: #f8f9fa !important;
        }

        /* Date column styling */
        .date-col {
            background: #f8f9fa;
        }

        /* Attendance cell */
        .attendance-cell {
            padding: 8px 12px !important;
            min-width: 220px;
        }

        /* Badge styling */
        .attendance-cell .badge {
            font-size: 11px;
            font-weight: 500;
            padding: 5px 8px;
        }

        /* Hover effect */
        .attendance-table tbody tr:hover td {
            background-color: rgba(0, 123, 255, 0.05) !important;
        }

        .attendance-table tbody tr:hover .sticky-col,
        .attendance-table tbody tr:hover .sticky-col-rate,
        .attendance-table tbody tr:hover .sticky-col-last {
            background-color: rgba(0, 123, 255, 0.05) !important;
        }

        /* Scrollbar styling */
        .attendance-table-wrapper::-webkit-scrollbar {
            width: 10px;
            height: 10px;
        }

        .attendance-table-wrapper::-webkit-scrollbar-track {
            background: #f1f1f1;
            border-radius: 5px;
        }

        .attendance-table-wrapper::-webkit-scrollbar-thumb {
            background: #c1c1c1;
            border-radius: 5px;
        }

        .attendance-table-wrapper::-webkit-scrollbar-thumb:hover {
            background: #a1a1a1;
        }

        /* Shadow for sticky columns */
        .sticky-col-3::after {
            content: '';
            position: absolute;
            top: 0;
            right: -5px;
            bottom: 0;
            width: 5px;
            background: linear-gradient(to right, rgba(0,0,0,0.1), transparent);
        }

        .sticky-col-last::before {
            content: '';
            position: absolute;
            top: 0;
            left: -5px;
            bottom: 0;
            width: 5px;
            background: linear-gradient(to left, rgba(0,0,0,0.1), transparent);
        }
    </style>
</div>
