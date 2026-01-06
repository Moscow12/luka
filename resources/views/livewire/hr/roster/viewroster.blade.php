<div class="custom-container">

    <x-pages.breadcrumn title="EMPLOYEE ROSTER CALENDAR" :breadcrumbs="[
        ['label' => 'Home', 'url' => route('dashboard')],
        ['label' => 'HR', 'url' => '#'],
        ['label' => 'Roster Calendar', 'url' => '#'],
    ]">
    </x-pages.breadcrumn>

    {{-- Flash Messages --}}
    @if (session()->has('success'))
        <div class="alert alert-success alert-dismissible fade show" role="alert">
            <i class="fa-solid fa-circle-check me-2"></i>
            {{ session('success') }}
            <button type="button" class="btn-close" data-bs-dismiss="alert" aria-label="Close"></button>
        </div>
    @endif

    @if (session()->has('error'))
        <div class="alert alert-danger alert-dismissible fade show" role="alert">
            <i class="fa-solid fa-circle-exclamation me-2"></i>
            {{ session('error') }}
            <button type="button" class="btn-close" data-bs-dismiss="alert" aria-label="Close"></button>
        </div>
    @endif

    @if (session()->has('info'))
        <div class="alert alert-info alert-dismissible fade show" role="alert">
            <i class="fa-solid fa-circle-info me-2"></i>
            {{ session('info') }}
            <button type="button" class="btn-close" data-bs-dismiss="alert" aria-label="Close"></button>
        </div>
    @endif

    {{-- Summary Cards --}}
    <div class="row g-3 mb-4">
        <div class="col-12 col-sm-6 col-xl-4">
            <div class="card border-0 shadow-sm">
                <div class="card-body">
                    <div class="d-flex align-items-center">
                        <div class="flex-shrink-0">
                            <div class="avatar avatar-lg bg-primary-subtle text-primary rounded">
                                <i class="fa-solid fa-calendar-days fs-4"></i>
                            </div>
                        </div>
                        <div class="flex-grow-1 ms-3">
                            <p class="text-muted mb-1 small">Current Period</p>
                            <h5 class="mb-0 fw-bold">{{ $summary['month_name'] }}</h5>
                        </div>
                    </div>
                </div>
            </div>
        </div>

        <div class="col-12 col-sm-6 col-xl-4">
            <div class="card border-0 shadow-sm">
                <div class="card-body">
                    <div class="d-flex align-items-center">
                        <div class="flex-shrink-0">
                            <div class="avatar avatar-lg bg-info-subtle text-info rounded">
                                <i class="fa-solid fa-users fs-4"></i>
                            </div>
                        </div>
                        <div class="flex-grow-1 ms-3">
                            <p class="text-muted mb-1 small">Total Employees</p>
                            <h4 class="mb-0 fw-bold text-info">{{ number_format($summary['total_employees']) }}</h4>
                        </div>
                    </div>
                </div>
            </div>
        </div>

        <div class="col-12 col-sm-6 col-xl-4">
            <div class="card border-0 shadow-sm">
                <div class="card-body">
                    <div class="d-flex align-items-center">
                        <div class="flex-shrink-0">
                            <div class="avatar avatar-lg bg-success-subtle text-success rounded">
                                <i class="fa-solid fa-calendar-check fs-4"></i>
                            </div>
                        </div>
                        <div class="flex-grow-1 ms-3">
                            <p class="text-muted mb-1 small">Roster Entries</p>
                            <h4 class="mb-0 fw-bold text-success">{{ number_format($summary['total_rosters']) }}</h4>
                        </div>
                    </div>
                </div>
            </div>
        </div>
    </div>

    {{-- Main Calendar Card --}}
    <div class="row">
        <div class="col-12">
            <div class="card card-lg border-0 shadow-sm">

                {{-- Filter Bar --}}
                <div class="card-header border-bottom bg-white">
                    <div class="row g-3 align-items-center">
                        {{-- Month/Year Navigator --}}
                        <div class="col-12 col-md-4">
                            <div class="d-flex align-items-center gap-2">
                                <button wire:click="previousMonth" class="btn btn-outline-primary btn-sm" title="Previous Month">
                                    <i class="fa-solid fa-chevron-left"></i>
                                </button>

                                <div class="flex-grow-1">
                                    <select wire:model.live="selectedMonth" class="form-select form-select-sm">
                                        @for($m = 1; $m <= 12; $m++)
                                            <option value="{{ str_pad($m, 2, '0', STR_PAD_LEFT) }}">
                                                {{ \Carbon\Carbon::create()->month($m)->format('F') }}
                                            </option>
                                        @endfor
                                    </select>
                                </div>

                                <div style="width: 100px;">
                                    <select wire:model.live="selectedYear" class="form-select form-select-sm">
                                        @for($y = now()->year - 2; $y <= now()->year + 2; $y++)
                                            <option value="{{ $y }}">{{ $y }}</option>
                                        @endfor
                                    </select>
                                </div>

                                <button wire:click="nextMonth" class="btn btn-outline-primary btn-sm" title="Next Month">
                                    <i class="fa-solid fa-chevron-right"></i>
                                </button>
                            </div>
                        </div>

                        {{-- Department Filter --}}
                        <div class="col-12 col-md-3">
                            <select wire:model.live="department" class="form-select form-select-sm">
                                <option value="">All Departments</option>
                                @foreach($departments as $dept)
                                    <option value="{{ $dept->id }}">{{ $dept->name }}</option>
                                @endforeach
                            </select>
                        </div>

                        {{-- Search --}}
                        <div class="col-12 col-md-3">
                            <div class="input-group input-group-sm">
                                <span class="input-group-text bg-white">
                                    <i class="fa-solid fa-magnifying-glass"></i>
                                </span>
                                <input type="search" wire:model.live.debounce.300ms="search" class="form-control"
                                    placeholder="Search employee..." />
                            </div>
                        </div>

                        {{-- Action Buttons --}}
                        <div class="col-12 col-md-2">
                            <div class="dropdown">
                                <button class="btn btn-primary btn-sm dropdown-toggle w-100" type="button" data-bs-toggle="dropdown">
                                    <i class="fa-solid fa-ellipsis-vertical"></i> Actions
                                </button>
                                <ul class="dropdown-menu">
                                    <li>
                                        <a class="dropdown-item" href="{{ route('roster.create') }}">
                                            <i class="fa-solid fa-plus text-success"></i> Generate Roster
                                        </a>
                                    </li>
                                    <li>
                                        <button class="dropdown-item" wire:click="copyPreviousMonth" wire:confirm="Copy all rosters from previous month?">
                                            <i class="fa-solid fa-copy text-info"></i> Copy Previous Month
                                        </button>
                                    </li>
                                    <li><hr class="dropdown-divider"></li>
                                    <li>
                                        <button class="dropdown-item" wire:click="exportPDF">
                                            <i class="fa-solid fa-file-pdf text-danger"></i> Export PDF
                                        </button>
                                    </li>
                                    <li>
                                        <button class="dropdown-item" wire:click="exportExcel">
                                            <i class="fa-solid fa-file-excel text-success"></i> Export Excel
                                        </button>
                                    </li>
                                </ul>
                            </div>
                        </div>
                    </div>

                    {{-- Legend --}}
                    @if($showLegend)
                        <div class="mt-3 pt-3 border-top">
                            <div class="d-flex flex-wrap align-items-center gap-3">
                                <small class="text-muted fw-semibold">Legend:</small>
                                @foreach($shifts as $shift)
                                    @php
                                        $colors = ['primary', 'success', 'info', 'warning', 'purple', 'danger'];
                                        $colorIndex = $loop->index % count($colors);
                                        $color = $colors[$colorIndex];
                                    @endphp
                                    <span class="badge bg-{{ $color }}-subtle text-{{ $color }} border border-{{ $color }}">
                                        {{ strtoupper(substr($shift->name, 0, 1)) }} - {{ $shift->name }}
                                    </span>
                                @endforeach
                                <span class="badge bg-light text-dark border">OFF - Day Off</span>
                            </div>
                        </div>
                    @endif
                </div>

                {{-- Calendar Table --}}
                <div class="card-body p-0">
                    <div class="table-responsive" style="max-height: 600px; overflow: auto;">
                        @if($employees->isEmpty())
                            <div class="text-center py-5">
                                <i class="fa-solid fa-users-slash text-muted mb-3" style="font-size: 48px;"></i>
                                <h5 class="text-muted">No Employees Found</h5>
                                <p class="text-muted">Try adjusting your filters or search query</p>
                            </div>
                        @else
                            <table class="table table-bordered table-hover mb-0 roster-calendar">
                                <thead class="table-light sticky-top" style="z-index: 10;">
                                    <tr>
                                        <th class="sticky-col" style="min-width: 200px; z-index: 11;">Employee</th>
                                        @foreach($dates as $date)
                                            <th class="text-center" style="min-width: 45px;">
                                                <div class="small">{{ $date->format('D') }}</div>
                                                <div class="fw-bold">{{ $date->format('d') }}</div>
                                            </th>
                                        @endforeach
                                        <th class="sticky-col-right text-center" style="min-width: 150px; z-index: 11;">Summary</th>
                                    </tr>
                                </thead>
                                <tbody>
                                    @foreach($employees as $employee)
                                        <tr wire:key="employee-{{ $employee->id }}">
                                            {{-- Employee Name (Sticky) --}}
                                            <td class="sticky-col bg-white">
                                                <div class="d-flex align-items-center">
                                                    <div class="avatar avatar-sm bg-primary-subtle text-primary rounded-circle me-2">
                                                        <span class="avatar-initials small">
                                                            {{ substr($employee->first_name, 0, 1) }}{{ substr($employee->last_name, 0, 1) }}
                                                        </span>
                                                    </div>
                                                    <div>
                                                        <div class="fw-semibold small text-dark">
                                                            {{ $employee->first_name }} {{ $employee->last_name }}
                                                        </div>
                                                        <div class="text-muted" style="font-size: 11px;">
                                                            {{ $employee->department->name ?? 'N/A' }}
                                                        </div>
                                                    </div>
                                                </div>
                                            </td>

                                            {{-- Date Cells --}}
                                            @foreach($dates as $date)
                                                @php
                                                    $dateStr = $date->format('Y-m-d');
                                                    $key = $employee->id . '_' . $dateStr;
                                                    $roster = $rosterData->get($key)?->first();

                                                    if ($roster && $roster->shift) {
                                                        $shiftAbbr = strtoupper(substr($roster->shift->name, 0, 1));
                                                        $colors = ['primary', 'success', 'info', 'warning', 'purple', 'danger'];
                                                        $shifts_array = $shifts->toArray();
                                                        $shift_index = array_search($roster->shift->id, array_column($shifts_array, 'id'));
                                                        $colorIndex = $shift_index !== false ? $shift_index % count($colors) : 0;
                                                        $bgColor = $colors[$colorIndex];

                                                        // Status indicator
                                                        $statusClass = match($roster->status) {
                                                            'completed' => 'border-success border-2',
                                                            'cancelled' => 'border-danger border-2',
                                                            'no_show' => 'border-warning border-2',
                                                            default => ''
                                                        };
                                                    } else {
                                                        $shiftAbbr = '';
                                                        $bgColor = 'light';
                                                        $statusClass = '';
                                                    }
                                                @endphp
                                                <td class="text-center p-0 roster-cell {{ $statusClass }}"
                                                    style="cursor: pointer; vertical-align: middle;"
                                                    wire:click="editCell({{ $employee->id }}, '{{ $dateStr }}')"
                                                    title="{{ $roster ? ($roster->shift->name ?? 'No shift') . ' - ' . ucfirst($roster->status) : 'Click to assign shift' }}">
                                                    @if($shiftAbbr)
                                                        <div class="badge bg-{{ $bgColor }} text-white fw-bold" style="font-size: 11px; padding: 4px 6px;">
                                                            {{ $shiftAbbr }}
                                                        </div>
                                                    @else
                                                        <div class="text-muted" style="font-size: 18px;">-</div>
                                                    @endif
                                                </td>
                                            @endforeach

                                            {{-- Summary (Sticky) --}}
                                            <td class="sticky-col-right bg-light text-center">
                                                <div class="small">
                                                    @php
                                                        $summary = $employeeSummaries[$employee->id] ?? ['shifts' => [], 'off_days' => 0];
                                                    @endphp
                                                    @if(!empty($summary['shifts']))
                                                        @foreach($summary['shifts'] as $shiftName => $count)
                                                            <div class="mb-1">
                                                                <span class="badge bg-primary-subtle text-primary">
                                                                    {{ substr($shiftName, 0, 1) }}: {{ $count }}
                                                                </span>
                                                            </div>
                                                        @endforeach
                                                        @if($summary['off_days'] > 0)
                                                            <div>
                                                                <span class="badge bg-light text-dark">
                                                                    OFF: {{ $summary['off_days'] }}
                                                                </span>
                                                            </div>
                                                        @endif
                                                    @else
                                                        <span class="text-muted">No shifts</span>
                                                    @endif
                                                </div>
                                            </td>
                                        </tr>
                                    @endforeach
                                </tbody>
                            </table>
                        @endif
                    </div>
                </div>

                {{-- Footer --}}
                <div class="card-footer border-top bg-white">
                    <div class="d-flex justify-content-between align-items-center">
                        <div class="text-muted small">
                            <i class="fa-solid fa-info-circle"></i>
                            Click on any cell to assign or edit shifts
                        </div>
                        <div class="text-muted small">
                            Showing {{ $employees->count() }} employees
                        </div>
                    </div>
                </div>
            </div>
        </div>
    </div>

    {{-- Edit Modal --}}
    @if($editingCell)
        <div class="modal fade show d-block" tabindex="-1" style="background-color: rgba(0,0,0,0.5);">
            <div class="modal-dialog modal-dialog-centered modal-dialog-scrollable">
                <div class="modal-content">
                    <div class="modal-header bg-primary text-white">
                        <h5 class="modal-title">
                            <i class="fa-solid fa-calendar-plus me-2"></i> Assign Shift
                        </h5>
                        <button type="button" class="btn-close btn-close-white" wire:click="closeEditModal"></button>
                    </div>
                    <div class="modal-body">
                        <div class="mb-3">
                            <label class="form-label">Shift <span class="text-danger">*</span></label>
                            <select wire:model="editShiftId" class="form-select @error('editShiftId') is-invalid @enderror">
                                <option value="">Select Shift</option>
                                @foreach($shifts as $shift)
                                    <option value="{{ $shift->id }}">
                                        {{ $shift->name }} ({{ \Carbon\Carbon::parse($shift->start_time)->format('H:i') }} - {{ \Carbon\Carbon::parse($shift->end_time)->format('H:i') }})
                                    </option>
                                @endforeach
                            </select>
                            @error('editShiftId')
                                <div class="invalid-feedback">{{ $message }}</div>
                            @enderror
                        </div>

                        <div class="mb-3">
                            <label class="form-label">Status <span class="text-danger">*</span></label>
                            <select wire:model="editStatus" class="form-select @error('editStatus') is-invalid @enderror">
                                <option value="scheduled">Scheduled</option>
                                <option value="completed">Completed</option>
                                <option value="cancelled">Cancelled</option>
                                <option value="no_show">No Show</option>
                            </select>
                            @error('editStatus')
                                <div class="invalid-feedback">{{ $message }}</div>
                            @enderror
                        </div>

                        <div class="mb-3">
                            <label class="form-label">Notes</label>
                            <textarea wire:model="editNotes" class="form-control @error('editNotes') is-invalid @enderror" rows="3" placeholder="Add notes..."></textarea>
                            @error('editNotes')
                                <div class="invalid-feedback">{{ $message }}</div>
                            @enderror
                        </div>
                    </div>
                    <div class="modal-footer">
                        @php
                            list($employeeId, $dateStr) = explode('_', $editingCell);
                        @endphp

                        @if($editShiftId)
                            <button type="button" class="btn btn-danger me-auto"
                                wire:click="deleteCell({{ $employeeId }}, '{{ $dateStr }}')"
                                wire:confirm="Are you sure you want to remove this shift assignment?">
                                <i class="fa-solid fa-trash"></i> Remove
                            </button>
                        @endif

                        <button type="button" class="btn btn-secondary" wire:click="closeEditModal">
                            <i class="fa-solid fa-times"></i> Cancel
                        </button>
                        <button type="button" class="btn btn-primary" wire:click="saveCell({{ $employeeId }}, '{{ $dateStr }}')">
                            <i class="fa-solid fa-save"></i> Save
                        </button>
                    </div>
                </div>
            </div>
        </div>
    @endif

    <!-- Loading Indicator -->
    <div wire:loading class="position-fixed top-50 start-50 translate-middle" style="z-index: 9999;">
        <div class="spinner-border text-primary" role="status">
            <span class="visually-hidden">Loading...</span>
        </div>
    </div>

    <style>
        /* Sticky columns */
        .sticky-col {
            position: sticky;
            left: 0;
            background-color: white;
            z-index: 2;
            box-shadow: 2px 0 5px rgba(0,0,0,0.1);
        }

        .sticky-col-right {
            position: sticky;
            right: 0;
            background-color: #f8f9fa;
            z-index: 2;
            box-shadow: -2px 0 5px rgba(0,0,0,0.1);
        }

        /* Roster cell hover effect */
        .roster-cell:hover {
            background-color: #f8f9fa;
            transform: scale(1.05);
            transition: all 0.2s ease;
        }

        /* Avatar styles */
        .avatar-sm {
            width: 32px;
            height: 32px;
            display: flex;
            align-items: center;
            justify-content: center;
            font-size: 12px;
            font-weight: 600;
        }

        .avatar-lg {
            width: 48px;
            height: 48px;
            display: flex;
            align-items: center;
            justify-content: center;
        }

        .avatar-initials {
            text-transform: uppercase;
        }

        /* Modal display */
        .modal.show {
            display: block;
        }

        /* Custom badge colors */
        .bg-purple {
            background-color: #6f42c1 !important;
        }

        .bg-purple-subtle {
            background-color: rgba(111, 66, 193, 0.1) !important;
        }

        .text-purple {
            color: #6f42c1 !important;
        }

        .border-purple {
            border-color: #6f42c1 !important;
        }

        /* Table styling */
        .roster-calendar thead th {
            font-weight: 600;
            font-size: 12px;
            padding: 8px 4px;
            white-space: nowrap;
        }

        .roster-calendar tbody td {
            padding: 4px;
            height: 50px;
        }

        /* Smooth transitions */
        tr {
            transition: background-color 0.2s ease;
        }

        .btn {
            transition: all 0.2s ease;
        }

        /* Scrollbar styling */
        .table-responsive::-webkit-scrollbar {
            height: 8px;
            width: 8px;
        }

        .table-responsive::-webkit-scrollbar-track {
            background: #f1f1f1;
        }

        .table-responsive::-webkit-scrollbar-thumb {
            background: #888;
            border-radius: 4px;
        }

        .table-responsive::-webkit-scrollbar-thumb:hover {
            background: #555;
        }
    </style>
</div>
