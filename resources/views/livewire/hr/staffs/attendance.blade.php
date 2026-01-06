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

    <!-- Page Title & Actions -->
    <div class="d-flex flex-column flex-md-row justify-content-between align-items-start align-items-md-center mb-4 gap-3">
        <div>
            <h5 class="mb-1">Attendance Records</h5>
            <p class="text-muted mb-0 small">Track and manage employee attendance</p>
        </div>
        <button class="btn btn-primary" wire:click="openModal('create')" @if(!$fpid) disabled @endif>
            <i class="fa-solid fa-plus me-1"></i>Add Attendance
        </button>
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
        <div class="col-6 col-md-3">
            <div class="card border-0 bg-success bg-opacity-10">
                <div class="card-body text-center py-3">
                    <div class="fs-3 fw-bold text-success">{{ $stats['present'] }}</div>
                    <small class="text-muted">Present</small>
                </div>
            </div>
        </div>
        <div class="col-6 col-md-3">
            <div class="card border-0 bg-danger bg-opacity-10">
                <div class="card-body text-center py-3">
                    <div class="fs-3 fw-bold text-danger">{{ $stats['absent'] }}</div>
                    <small class="text-muted">Absent</small>
                </div>
            </div>
        </div>
        <div class="col-6 col-md-3">
            <div class="card border-0 bg-warning bg-opacity-10">
                <div class="card-body text-center py-3">
                    <div class="fs-3 fw-bold text-warning">{{ $stats['late'] }}</div>
                    <small class="text-muted">Late</small>
                </div>
            </div>
        </div>
        <div class="col-6 col-md-3">
            <div class="card border-0 bg-info bg-opacity-10">
                <div class="card-body text-center py-3">
                    <div class="fs-3 fw-bold text-info">{{ $stats['leave'] }}</div>
                    <small class="text-muted">On Leave</small>
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
                        <th class="text-end pe-4">Actions</th>
                    </tr>
                </thead>
                <tbody>
                    @forelse($attendances as $index => $record)
                        <tr wire:key="attendance-{{ $record->id }}">
                            <td class="ps-4">{{ $attendances->firstItem() + $index }}</td>
                            <td>
                                <span class="fw-medium">{{ \Carbon\Carbon::parse($record->clockdate)->format('d M, Y') }}</span>
                            </td>
                            <td>
                                <span class="text-muted">{{ \Carbon\Carbon::parse($record->clockdate)->format('l') }}</span>
                            </td>
                            <td>
                                @if($record->clock_in)
                                    <span class="badge bg-light text-dark">
                                        <i class="fa-solid fa-right-to-bracket text-success me-1"></i>
                                        {{ \Carbon\Carbon::parse($record->clock_in)->format('h:i A') }}
                                    </span>
                                @else
                                    <span class="text-muted">-</span>
                                @endif
                            </td>
                            <td>
                                @if($record->clock_out)
                                    <span class="badge bg-light text-dark">
                                        <i class="fa-solid fa-right-from-bracket text-danger me-1"></i>
                                        {{ \Carbon\Carbon::parse($record->clock_out)->format('h:i A') }}
                                    </span>
                                @else
                                    <span class="text-muted">-</span>
                                @endif
                            </td>
                            <td>
                                @if($record->clock_in && $record->clock_out)
                                    @php
                                        $duration = \Carbon\Carbon::parse($record->clock_in)->diff(\Carbon\Carbon::parse($record->clock_out));
                                    @endphp
                                    <span class="text-muted">{{ $duration->format('%Hh %Im') }}</span>
                                @else
                                    <span class="text-muted">-</span>
                                @endif
                            </td>
                            <td>
                                @php
                                    $statusClass = match($record->clock_status) {
                                        'Present' => 'bg-success',
                                        'Absent' => 'bg-danger',
                                        'Late' => 'bg-warning',
                                        'Half Day' => 'bg-info',
                                        'On Leave' => 'bg-secondary',
                                        default => 'bg-light text-dark'
                                    };
                                @endphp
                                <span class="badge {{ $statusClass }}">{{ $record->clock_status ?? 'N/A' }}</span>
                            </td>
                            <td class="text-end pe-4">
                                <div class="d-flex justify-content-end gap-1">
                                    <button class="btn btn-sm btn-outline-primary"
                                        wire:click="openModal('edit', '{{ $record->id }}')"
                                        title="Edit">
                                        <i class="fa-solid fa-pen-to-square"></i>
                                    </button>
                                    <button class="btn btn-sm btn-outline-danger"
                                        wire:click="confirmDelete('{{ $record->id }}')"
                                        title="Delete">
                                        <i class="fa-solid fa-trash"></i>
                                    </button>
                                </div>
                            </td>
                        </tr>
                    @empty
                        <tr>
                            <td colspan="8" class="text-center py-5">
                                <div class="d-flex flex-column align-items-center">
                                    <div class="bg-light rounded-circle d-flex align-items-center justify-content-center mb-3"
                                        style="width: 80px; height: 80px;">
                                        <i class="fa-solid fa-calendar-xmark fa-2x text-muted"></i>
                                    </div>
                                    <h6 class="mb-1">No attendance records found</h6>
                                    <p class="text-muted mb-3 small">
                                        @if($filterMonth || $filterStatus)
                                            No records match your filter criteria
                                        @else
                                            Start tracking attendance by adding a record
                                        @endif
                                    </p>
                                    @if($fpid && !$filterStatus)
                                        <button class="btn btn-primary btn-sm" wire:click="openModal('create')">
                                            <i class="fa-solid fa-plus me-1"></i>Add Attendance
                                        </button>
                                    @endif
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

    <!-- Add/Edit Modal -->
    @if($showModal)
        <div class="modal fade show d-block" tabindex="-1" style="background: rgba(0,0,0,0.5);">
            <div class="modal-dialog modal-dialog-centered modal-dialog-scrollable">
                <div class="modal-content">
                    <form wire:submit="save">
                        <div class="modal-header">
                            <h5 class="modal-title">
                                <i class="fa-solid fa-calendar-check me-2"></i>
                                {{ $modalMode === 'edit' ? 'Edit Attendance' : 'Add Attendance' }}
                            </h5>
                            <button type="button" class="btn-close" wire:click="closeModal"></button>
                        </div>
                        <div class="modal-body">
                            <div class="mb-3">
                                <label class="form-label">Date <span class="text-danger">*</span></label>
                                <input type="date" class="form-control @error('date') is-invalid @enderror"
                                    wire:model="date">
                                @error('date')
                                    <div class="invalid-feedback">{{ $message }}</div>
                                @enderror
                            </div>

                            <div class="row mb-3">
                                <div class="col-6">
                                    <label class="form-label">Clock In</label>
                                    <input type="time" class="form-control @error('clock_in') is-invalid @enderror"
                                        wire:model="clock_in">
                                    @error('clock_in')
                                        <div class="invalid-feedback">{{ $message }}</div>
                                    @enderror
                                </div>
                                <div class="col-6">
                                    <label class="form-label">Clock Out</label>
                                    <input type="time" class="form-control @error('clock_out') is-invalid @enderror"
                                        wire:model="clock_out">
                                    @error('clock_out')
                                        <div class="invalid-feedback">{{ $message }}</div>
                                    @enderror
                                </div>
                            </div>

                            <div class="mb-3">
                                <label class="form-label">Status <span class="text-danger">*</span></label>
                                <select class="form-select @error('clock_status') is-invalid @enderror"
                                    wire:model="clock_status">
                                    <option value="">Select Status</option>
                                    <option value="Present">Present</option>
                                    <option value="Absent">Absent</option>
                                    <option value="Late">Late</option>
                                    <option value="Half Day">Half Day</option>
                                    <option value="On Leave">On Leave</option>
                                </select>
                                @error('clock_status')
                                    <div class="invalid-feedback">{{ $message }}</div>
                                @enderror
                            </div>
                        </div>
                        <div class="modal-footer">
                            <button type="button" class="btn btn-light" wire:click="closeModal">Cancel</button>
                            <button type="submit" class="btn btn-primary" wire:loading.attr="disabled">
                                <span wire:loading.remove wire:target="save">
                                    <i class="fa-solid fa-check me-1"></i>
                                    {{ $modalMode === 'edit' ? 'Update' : 'Save' }}
                                </span>
                                <span wire:loading wire:target="save">
                                    <span class="spinner-border spinner-border-sm me-1"></span>
                                    Saving...
                                </span>
                            </button>
                        </div>
                    </form>
                </div>
            </div>
        </div>
    @endif

    <!-- Delete Confirmation Modal -->
    @if($confirmingDelete)
        <div class="modal fade show d-block" tabindex="-1" style="background: rgba(0,0,0,0.5);">
            <div class="modal-dialog modal-dialog-centered modal-sm">
                <div class="modal-content">
                    <div class="modal-body text-center py-4">
                        <div class="bg-danger bg-opacity-10 rounded-circle d-inline-flex align-items-center justify-content-center mb-3"
                            style="width: 60px; height: 60px;">
                            <i class="fa-solid fa-trash fa-lg text-danger"></i>
                        </div>
                        <h5 class="mb-2">Delete Record?</h5>
                        <p class="text-muted mb-0 small">This attendance record will be permanently deleted.</p>
                    </div>
                    <div class="modal-footer border-top justify-content-center gap-2">
                        <button type="button" class="btn btn-light" wire:click="cancelDelete">Cancel</button>
                        <button type="button" class="btn btn-danger" wire:click="delete">
                            <i class="fa-solid fa-trash me-1"></i>Delete
                        </button>
                    </div>
                </div>
            </div>
        </div>
    @endif
</div>
