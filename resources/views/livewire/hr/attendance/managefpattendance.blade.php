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
                                    <td class="sticky-col-last text-center bg-white">
                                        <button class="btn btn-sm btn-info text-white">
                                            SCORE
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
