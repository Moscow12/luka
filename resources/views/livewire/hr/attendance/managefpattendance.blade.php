<div>
    <div class="container-fluid">
        <!-- Page Header -->
        <div class="row mb-4">
            <div class="col-12">
                <div class="d-flex justify-content-between align-items-center">
                    <div>
                        <h4 class="mb-1">Attendance Management</h4>
                        <p class="text-muted mb-0">Monitor and manage fingerprint attendance logs</p>
                    </div>
                    <div class="text-muted">
                        <i class="bi bi-calendar-check fs-4"></i>
                    </div>
                </div>
            </div>
        </div>

        <!-- Filters Card -->
        <div class="card shadow-sm border-0 mb-4">
            <div class="card-header bg-primary text-white">
                <h5 class="mb-0">
                    <i class="bi bi-funnel me-2"></i>Filters
                </h5>
            </div>
            <div class="card-body">
                <div class="row g-3">
                    <!-- Date Range -->
                    <div class="col-md-3">
                        <label class="form-label fw-semibold">
                            <i class="bi bi-calendar-range me-1"></i>From Date
                        </label>
                        <input type="date"
                               wire:model.live="dateFrom"
                               class="form-control"
                               max="{{ now()->format('Y-m-d') }}">
                    </div>
                    <div class="col-md-3">
                        <label class="form-label fw-semibold">
                            <i class="bi bi-calendar-range me-1"></i>To Date
                        </label>
                        <input type="date"
                               wire:model.live="dateTo"
                               class="form-control"
                               max="{{ now()->format('Y-m-d') }}">
                    </div>

                    <!-- Device Filter -->
                    <div class="col-md-2">
                        <label class="form-label fw-semibold">
                            <i class="bi bi-device-hdd me-1"></i>Device
                        </label>
                        <select wire:model.live="deviceFilter" class="form-select">
                            <option value="">All Devices</option>
                            @foreach($devices as $device)
                                <option value="{{ $device }}">{{ $device }}</option>
                            @endforeach
                        </select>
                    </div>

                    <!-- Status Filter -->
                    <div class="col-md-2">
                        <label class="form-label fw-semibold">
                            <i class="bi bi-flag me-1"></i>Status
                        </label>
                        <select wire:model.live="statusFilter" class="form-select">
                            <option value="">All Status</option>
                            @foreach($statuses as $status)
                                <option value="{{ $status }}">{{ ucfirst($status) }}</option>
                            @endforeach
                        </select>
                    </div>

                    <!-- Clear Filters -->
                    <div class="col-md-2">
                        <label class="form-label fw-semibold d-block">&nbsp;</label>
                        <button wire:click="clearFilters"
                                class="btn btn-outline-secondary w-100"
                                title="Clear all filters">
                            <i class="bi bi-x-circle me-1"></i>Clear Filters
                        </button>
                    </div>
                </div>
            </div>
        </div>

        <!-- Attendance Table -->
        <div class="card shadow-sm border-0">
            <div class="card-header bg-white border-bottom">
                <div class="row align-items-center">
                    <div class="col-md-6">
                        <h5 class="mb-0">
                            <i class="bi bi-clock-history me-2 text-primary"></i>Attendance Logs
                        </h5>
                    </div>
                    <div class="col-md-6">
                        <div class="input-group">
                            <span class="input-group-text bg-light border-end-0">
                                <i class="bi bi-search"></i>
                            </span>
                            <input type="text"
                                   wire:model.live.debounce.300ms="search"
                                   class="form-control border-start-0 ps-0"
                                   placeholder="Search by user, device, timestamp...">
                            @if($search)
                                <button class="btn btn-outline-secondary"
                                        wire:click="$set('search', '')"
                                        type="button">
                                    <i class="bi bi-x"></i>
                                </button>
                            @endif
                        </div>
                    </div>
                </div>
            </div>

            <div class="card-body p-0">
                @if($attendances->count() > 0)
                    <div class="table-responsive">
                        <table class="table table-hover align-middle mb-0">
                            <thead class="bg-light">
                                <tr>
                                    <th class="px-4 py-3">User ID</th>
                                    <th class="py-3">User Name</th>
                                    <th class="py-3">Device</th>
                                    <th class="py-3">Date</th>
                                    <th class="py-3">Time</th>
                                    <th class="py-3">Timestamp</th>
                                    <th class="py-3">Status</th>
                                    <th class="py-3 text-center">Actions</th>
                                </tr>
                            </thead>
                            <tbody>
                                @foreach($attendances as $attendance)
                                    <tr wire:key="attendance-{{ $attendance->id }}">
                                        <td class="px-4 py-3">
                                            <span class="badge bg-secondary">
                                                {{ $attendance->fpuser_id }}
                                            </span>
                                        </td>
                                        <td class="py-3">
                                            <div class="d-flex align-items-center">
                                                @if($attendance->fpuser)
                                                    <div class="avatar-circle bg-success bg-opacity-10 text-success me-2">
                                                        {{ strtoupper(substr($attendance->fpuser->name, 0, 2)) }}
                                                    </div>
                                                    <span class="fw-semibold">{{ $attendance->fpuser->name }}</span>
                                                @else
                                                    <div class="avatar-circle bg-secondary bg-opacity-10 text-secondary me-2">
                                                        <i class="bi bi-person-x"></i>
                                                    </div>
                                                    <span class="text-muted">Unknown User</span>
                                                @endif
                                            </div>
                                        </td>
                                        <td class="py-3">
                                            <span class="badge bg-info bg-opacity-10 text-info">
                                                <i class="bi bi-device-hdd me-1"></i>{{ $attendance->device_id }}
                                            </span>
                                        </td>
                                        <td class="py-3">
                                            <span class="text-dark">
                                                <i class="bi bi-calendar3 me-1"></i>
                                                {{ $attendance->clockdate ? \Carbon\Carbon::parse($attendance->clockdate)->format('M d, Y') : 'N/A' }}
                                            </span>
                                        </td>
                                        <td class="py-3">
                                            <span class="text-dark">
                                                <i class="bi bi-clock me-1"></i>
                                                {{ $attendance->clocktime ? \Carbon\Carbon::parse($attendance->clocktime)->format('h:i A') : 'N/A' }}
                                            </span>
                                        </td>
                                        <td class="py-3">
                                            <small class="text-muted">{{ $attendance->clocktimestamp }}</small>
                                        </td>
                                        <td class="py-3">
                                            @if($attendance->clock_status)
                                                @php
                                                    $statusColors = [
                                                        'in' => 'success',
                                                        'out' => 'warning',
                                                        'clock_in' => 'success',
                                                        'clock_out' => 'warning',
                                                    ];
                                                    $color = $statusColors[strtolower($attendance->clock_status)] ?? 'primary';
                                                @endphp
                                                <span class="badge bg-{{ $color }}">
                                                    {{ ucfirst($attendance->clock_status) }}
                                                </span>
                                            @else
                                                <span class="text-muted">-</span>
                                            @endif
                                        </td>
                                        <td class="py-3 text-center">
                                            <button wire:click="deleteAttendance('{{ $attendance->id }}')"
                                                    wire:confirm="Are you sure you want to delete this attendance record?"
                                                    class="btn btn-sm btn-outline-danger"
                                                    title="Delete record">
                                                <i class="bi bi-trash"></i>
                                            </button>
                                        </td>
                                    </tr>
                                @endforeach
                            </tbody>
                        </table>
                    </div>

                    <!-- Pagination -->
                    <div class="card-footer bg-white border-top">
                        <div class="d-flex justify-content-between align-items-center">
                            <div class="text-muted small">
                                Showing {{ $attendances->firstItem() }} to {{ $attendances->lastItem() }} of {{ $attendances->total() }} records
                            </div>
                            <div>
                                {{ $attendances->links() }}
                            </div>
                        </div>
                    </div>
                @else
                    <div class="text-center py-5">
                        <i class="bi bi-calendar-x fs-1 text-muted"></i>
                        <p class="text-muted mt-3 mb-0">
                            @if($search || $deviceFilter || $statusFilter)
                                No attendance records found matching your filters.
                            @else
                                No attendance records found for the selected date range.
                            @endif
                        </p>
                        @if($search || $deviceFilter || $statusFilter)
                            <button wire:click="clearFilters"
                                    class="btn btn-primary mt-3">
                                <i class="bi bi-x-circle me-2"></i>Clear Filters
                            </button>
                        @endif
                    </div>
                @endif
            </div>
        </div>

        <!-- API Information Card -->
        <div class="card shadow-sm border-0 mt-4">
            <div class="card-header bg-info text-white">
                <h6 class="mb-0">
                    <i class="bi bi-info-circle me-2"></i>API Endpoint Information
                </h6>
            </div>
            <div class="card-body">
                <div class="row">
                    <div class="col-md-6">
                        <h6 class="fw-semibold mb-2">Single Attendance Log</h6>
                        <div class="bg-light p-3 rounded mb-2">
                            <code class="text-dark">POST {{ url('/api/attendance/log') }}</code>
                        </div>
                        <small class="text-muted">
                            <strong>Payload:</strong>
                            <pre class="mt-2 p-2 bg-light rounded">
{
  "fpuser_id": 123,
  "device_id": "FP001",
  "clocktimestamp": "2025-11-10 08:30:00",
  "clockdate": "2025-11-10",
  "clocktime": "08:30:00",
  "clock_status": "in"
}</pre>
                        </small>
                    </div>
                    <div class="col-md-6">
                        <h6 class="fw-semibold mb-2">Bulk Attendance Logs</h6>
                        <div class="bg-light p-3 rounded mb-2">
                            <code class="text-dark">POST {{ url('/api/attendance/log-bulk') }}</code>
                        </div>
                        <small class="text-muted">
                            <strong>Payload:</strong>
                            <pre class="mt-2 p-2 bg-light rounded">
{
  "attendances": [
    {
      "fpuser_id": 123,
      "device_id": "FP001",
      "clocktimestamp": "2025-11-10 08:30:00"
    }
  ]
}</pre>
                        </small>
                    </div>
                </div>
            </div>
        </div>
    </div>

    <style>
        .avatar-circle {
            width: 35px;
            height: 35px;
            border-radius: 50%;
            display: flex;
            align-items: center;
            justify-content: center;
            font-weight: 600;
            font-size: 12px;
        }

        .table-hover tbody tr:hover {
            background-color: rgba(0, 123, 255, 0.05);
        }

        pre {
            font-size: 11px;
            margin-bottom: 0;
        }
    </style>
</div>
