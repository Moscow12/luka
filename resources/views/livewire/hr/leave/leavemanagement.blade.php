<div>
    <!-- Page Header -->
    <div class="row mb-4">
        <div class="col-12">
            <div class="d-flex justify-content-between align-items-center">
                <div>
                    <h2 class="mb-0">Leave Management</h2>
                    <p class="text-muted mb-0">View and manage all employee leave requests</p>
                </div>
            </div>
        </div>
    </div>

    <!-- Filters -->
    <div class="row mb-4">
        <div class="col-12">
            <div class="card">
                <div class="card-body">
                    <div class="row g-3">
                        <!-- Search -->
                        <div class="col-md-4">
                            <label class="form-label">Search Employee</label>
                            <div class="input-group">
                                <span class="input-group-text">
                                    <i class="fa-solid fa-search"></i>
                                </span>
                                <input type="text"
                                       class="form-control"
                                       wire:model.live.debounce.300ms="search"
                                       placeholder="Name or employee number...">
                            </div>
                        </div>

                        <!-- Status Filter -->
                        <div class="col-md-4">
                            <label class="form-label">Filter by Status</label>
                            <select class="form-select" wire:model.live="statusFilter">
                                <option value="all">All Statuses</option>
                                <option value="Awaiting">Awaiting Approval</option>
                                <option value="Active">In Progress</option>
                                <option value="Approved">Approved</option>
                                <option value="Rejected">Rejected</option>
                            </select>
                        </div>

                        <!-- Stats Summary -->
                        <div class="col-md-4">
                            <div class="d-flex gap-2 h-100 align-items-end">
                                <div class="text-center flex-fill">
                                    <div class="fs-4 fw-bold text-warning">{{ $leaves->where('status', 'Awaiting')->count() }}</div>
                                    <small class="text-muted">Pending</small>
                                </div>
                                <div class="text-center flex-fill">
                                    <div class="fs-4 fw-bold text-info">{{ $leaves->where('status', 'Active')->count() }}</div>
                                    <small class="text-muted">In Progress</small>
                                </div>
                                <div class="text-center flex-fill">
                                    <div class="fs-4 fw-bold text-success">{{ $leaves->where('status', 'Approved')->count() }}</div>
                                    <small class="text-muted">Approved</small>
                                </div>
                            </div>
                        </div>
                    </div>
                </div>
            </div>
        </div>
    </div>

    <!-- Leave Requests List -->
    <div class="row">
        <div class="col-12">
            <div class="card">
                <div class="card-body">
                    <div class="table-responsive">
                        <table class="table table-hover align-middle">
                            <thead class="table-light">
                                <tr>
                                    <th>Employee</th>
                                    <th>Leave Type</th>
                                    <th>Period</th>
                                    <th>Duration</th>
                                    <th>Leave Balance</th>
                                    <th>Status</th>
                                    <th>Approval Progress</th>
                                    <th>Actions</th>
                                </tr>
                            </thead>
                            <tbody>
                                @forelse($leaves as $leave)
                                <tr wire:key="leave-{{ $leave->id }}">
                                    <td>
                                        <div class="d-flex align-items-center gap-3">
                                            <div class="avatar avatar-md rounded-circle bg-primary-subtle text-primary-emphasis d-flex align-items-center justify-content-center">
                                                <span class="fw-bold">{{ substr($leave->employee->getFullName() ?? 'U', 0, 2) }}</span>
                                            </div>
                                            <div>
                                                <div class="fw-semibold">{{ $leave->employee->getFullName() ?? 'Unknown' }}</div>
                                                <small class="text-muted">{{ $leave->employee->employee_number }}</small>
                                            </div>
                                        </div>
                                    </td>
                                    <td>
                                        <span class="badge bg-info-subtle text-info-emphasis px-3 py-2">
                                            <i class="fa-solid fa-calendar-days me-1"></i>
                                            {{ $leave->leave->name ?? 'N/A' }}
                                        </span>
                                    </td>
                                    <td>
                                        <div>
                                            <i class="fa-solid fa-calendar-check text-success me-1"></i>
                                            {{ \Carbon\Carbon::parse($leave->start_date)->format('d M Y') }}
                                        </div>
                                        <small class="text-muted">
                                            <i class="fa-solid fa-arrow-right mx-1"></i>
                                            {{ \Carbon\Carbon::parse($leave->end_date)->format('d M Y') }}
                                        </small>
                                    </td>
                                    <td>
                                        <span class="badge bg-secondary-subtle text-secondary-emphasis">
                                            {{ $leave->days }} {{ Str::plural('day', $leave->days) }}
                                        </span>
                                    </td>
                                    <td>
                                        <div class="d-flex flex-column gap-1">
                                            <div class="d-flex justify-content-between align-items-center">
                                                <small class="text-muted">Entitled:</small>
                                                <span class="badge bg-primary-subtle text-primary-emphasis">{{ $leave->leaveBalance['entitled'] }}</span>
                                            </div>
                                            <div class="d-flex justify-content-between align-items-center">
                                                <small class="text-muted">Used:</small>
                                                <span class="badge bg-warning-subtle text-warning-emphasis">{{ $leave->leaveBalance['used'] }}</span>
                                            </div>
                                            <div class="d-flex justify-content-between align-items-center">
                                                <small class="fw-semibold">Balance:</small>
                                                <span class="badge bg-success-subtle text-success-emphasis">{{ $leave->leaveBalance['balance'] }}</span>
                                            </div>
                                        </div>
                                    </td>
                                    <td>
                                        @if($leave->status === 'Awaiting')
                                        <span class="badge bg-warning text-dark">
                                            <i class="fa-solid fa-clock me-1"></i>Awaiting
                                        </span>
                                        @elseif($leave->status === 'Active')
                                        <span class="badge bg-info">
                                            <i class="fa-solid fa-hourglass-half me-1"></i>In Progress
                                        </span>
                                        @elseif($leave->status === 'Approved')
                                        <span class="badge bg-success">
                                            <i class="fa-solid fa-check-circle me-1"></i>Approved
                                        </span>
                                        @else
                                        <span class="badge bg-danger">
                                            <i class="fa-solid fa-times-circle me-1"></i>Rejected
                                        </span>
                                        @endif
                                    </td>
                                    <td>
                                        @if($leave->approvalnote->count() > 0)
                                        <div class="d-flex flex-column gap-1">
                                            @foreach($leave->approvalnote->take(2) as $approval)
                                            <small class="d-flex align-items-center gap-1">
                                                <i class="fa-solid fa-{{ $approval->status === 'approved' ? 'check text-success' : 'times text-danger' }}"></i>
                                                <span class="text-truncate" style="max-width: 120px;">
                                                    {{ $approval->approval_level->name ?? 'N/A' }}
                                                </span>
                                            </small>
                                            @endforeach
                                            @if($leave->approvalnote->count() > 2)
                                            <small class="text-muted">+{{ $leave->approvalnote->count() - 2 }} more</small>
                                            @endif
                                        </div>
                                        @else
                                        <small class="text-muted">No approvals yet</small>
                                        @endif
                                    </td>
                                    <td>
                                        <div class="d-flex gap-1">
                                            <button wire:click="viewLeaveDetails('{{ $leave->id }}')"
                                                    class="btn btn-sm btn-primary">
                                                <i class="fa-solid fa-eye"></i>
                                            </button>
                                            @if($canApprove && in_array($leave->status, ['Awaiting', 'pending']))
                                            <button wire:click="approveLeave('{{ $leave->id }}')"
                                                    wire:confirm="Are you sure you want to approve this leave request?"
                                                    class="btn btn-sm btn-success">
                                                <i class="fa-solid fa-check"></i>
                                            </button>
                                            <button wire:click="openRejectModal('{{ $leave->id }}')"
                                                    class="btn btn-sm btn-danger">
                                                <i class="fa-solid fa-times"></i>
                                            </button>
                                            @endif
                                        </div>
                                    </td>
                                </tr>
                                @empty
                                <tr>
                                    <td colspan="8" class="text-center py-5">
                                        <div class="text-muted">
                                            <i class="fa-solid fa-inbox fa-3x mb-3 d-block"></i>
                                            <p class="mb-0">No leave requests found.</p>
                                            @if($search || $statusFilter !== 'all')
                                            <small>Try adjusting your filters</small>
                                            @endif
                                        </div>
                                    </td>
                                </tr>
                                @endforelse
                            </tbody>
                        </table>
                    </div>

                    <!-- Pagination -->
                    @if($leaves->hasPages())
                    <div class="mt-4">
                        {{ $leaves->links() }}
                    </div>
                    @endif
                </div>
            </div>
        </div>
    </div>

    <!-- Leave Details Modal -->
    @if($showModal && $selectedLeave)
    <x-forms.modal
        id="leaveDetailsModal"
        title="Leave Request Details"
        size="modal-xl"
        :centered="true">

        <div class="row g-4">
            <!-- Left Column - Employee & Leave Info -->
            <div class="col-md-6">
                <div class="card h-100">
                    <div class="card-header bg-primary text-white">
                        <h6 class="mb-0"><i class="fa-solid fa-user me-2"></i>Employee Information</h6>
                    </div>
                    <div class="card-body">
                        <div class="d-flex align-items-center gap-3 mb-4">
                            <div class="avatar avatar-lg rounded-circle bg-primary-subtle text-primary-emphasis d-flex align-items-center justify-content-center">
                                <span class="fs-4 fw-bold">{{ substr($selectedLeave->employee->getFullName() ?? 'U', 0, 2) }}</span>
                            </div>
                            <div>
                                <h5 class="mb-0">{{ $selectedLeave->employee->getFullName() ?? 'Unknown' }}</h5>
                                <small class="text-muted">{{ $selectedLeave->employee->employee_number }}</small>
                            </div>
                        </div>

                        <div class="border-top pt-3">
                            <div class="row g-3">
                                <div class="col-12">
                                    <label class="form-label fw-semibold text-muted small">LEAVE TYPE</label>
                                    <div class="d-flex align-items-center gap-2">
                                        <span class="badge bg-info-subtle text-info-emphasis px-3 py-2">
                                            {{ $selectedLeave->leave->name ?? 'N/A' }}
                                        </span>
                                    </div>
                                </div>
                                <div class="col-6">
                                    <label class="form-label fw-semibold text-muted small">START DATE</label>
                                    <div><i class="fa-solid fa-calendar-check text-success me-2"></i>{{ \Carbon\Carbon::parse($selectedLeave->start_date)->format('d M Y') }}</div>
                                </div>
                                <div class="col-6">
                                    <label class="form-label fw-semibold text-muted small">END DATE</label>
                                    <div><i class="fa-solid fa-calendar-xmark text-danger me-2"></i>{{ \Carbon\Carbon::parse($selectedLeave->end_date)->format('d M Y') }}</div>
                                </div>
                                <div class="col-6">
                                    <label class="form-label fw-semibold text-muted small">DURATION</label>
                                    <div class="badge bg-secondary-subtle text-secondary-emphasis px-3 py-2">
                                        {{ $selectedLeave->days }} {{ Str::plural('day', $selectedLeave->days) }}
                                    </div>
                                </div>
                                <div class="col-6">
                                    <label class="form-label fw-semibold text-muted small">REQUESTED ON</label>
                                    <div>{{ $selectedLeave->created_at->format('d M Y, H:i') }}</div>
                                </div>
                                @if($selectedLeave->reason)
                                <div class="col-12">
                                    <label class="form-label fw-semibold text-muted small">REASON</label>
                                    <div class="p-3 bg-light rounded">{{ $selectedLeave->reason }}</div>
                                </div>
                                @endif
                            </div>
                        </div>

                        <!-- Leave Balance Summary -->
                        <div class="border-top mt-4 pt-4">
                            <h6 class="mb-3"><i class="fa-solid fa-chart-pie me-2"></i>Leave Balance Summary</h6>
                            <div class="row g-2">
                                <div class="col-4">
                                    <div class="card bg-primary-subtle border-0">
                                        <div class="card-body text-center py-2">
                                            <div class="fs-4 fw-bold text-primary">{{ $selectedLeave->leaveBalance['entitled'] }}</div>
                                            <small class="text-muted">Entitled</small>
                                        </div>
                                    </div>
                                </div>
                                <div class="col-4">
                                    <div class="card bg-warning-subtle border-0">
                                        <div class="card-body text-center py-2">
                                            <div class="fs-4 fw-bold text-warning">{{ $selectedLeave->leaveBalance['used'] }}</div>
                                            <small class="text-muted">Used</small>
                                        </div>
                                    </div>
                                </div>
                                <div class="col-4">
                                    <div class="card bg-success-subtle border-0">
                                        <div class="card-body text-center py-2">
                                            <div class="fs-4 fw-bold text-success">{{ $selectedLeave->leaveBalance['balance'] }}</div>
                                            <small class="text-muted">Balance</small>
                                        </div>
                                    </div>
                                </div>
                            </div>
                        </div>
                    </div>
                </div>
            </div>

            <!-- Right Column - Approval History -->
            <div class="col-md-6">
                <div class="card h-100">
                    <div class="card-header bg-success text-white">
                        <h6 class="mb-0"><i class="fa-solid fa-list-check me-2"></i>Approval History</h6>
                    </div>
                    <div class="card-body">
                        <!-- Current Status -->
                        <div class="alert alert-{{ $selectedLeave->status === 'Approved' ? 'success' : ($selectedLeave->status === 'Rejected' ? 'danger' : 'warning') }} mb-4">
                            <div class="d-flex align-items-center justify-content-between">
                                <div>
                                    <strong>Current Status:</strong>
                                    @if($selectedLeave->status === 'Awaiting')
                                    <span class="ms-2">Awaiting Approval</span>
                                    @elseif($selectedLeave->status === 'Active')
                                    <span class="ms-2">In Progress</span>
                                    @elseif($selectedLeave->status === 'Approved')
                                    <span class="ms-2">Fully Approved</span>
                                    @else
                                    <span class="ms-2">Rejected</span>
                                    @endif
                                </div>
                                <i class="fa-solid fa-{{ $selectedLeave->status === 'Approved' ? 'check-circle' : ($selectedLeave->status === 'Rejected' ? 'times-circle' : 'clock') }} fa-2x"></i>
                            </div>
                        </div>

                        <!-- Approval Timeline -->
                        @if($selectedLeave->approvalnote->count() > 0)
                        <div class="position-relative">
                            @foreach($selectedLeave->approvalnote as $index => $approval)
                            <div class="d-flex gap-3 mb-4 position-relative">
                                <!-- Timeline Line -->
                                @if(!$loop->last)
                                <div class="position-absolute" style="left: 18px; top: 40px; bottom: -20px; width: 2px; background: #dee2e6;"></div>
                                @endif

                                <!-- Timeline Icon -->
                                <div class="flex-shrink-0">
                                    <div class="avatar avatar-md rounded-circle bg-{{ $approval->status === 'approved' ? 'success' : 'danger' }} text-white d-flex align-items-center justify-content-center" style="z-index: 1; position: relative;">
                                        <i class="fa-solid fa-{{ $approval->status === 'approved' ? 'check' : 'times' }}"></i>
                                    </div>
                                </div>

                                <!-- Approval Details -->
                                <div class="flex-grow-1">
                                    <div class="card border-{{ $approval->status === 'approved' ? 'success' : 'danger' }}">
                                        <div class="card-body p-3">
                                            <div class="d-flex justify-content-between align-items-start mb-2">
                                                <div>
                                                    <h6 class="mb-1">
                                                        {{ $approval->approval_level->name ?? 'N/A' }}
                                                        <span class="badge bg-{{ $approval->status === 'approved' ? 'success' : 'danger' }}-subtle text-{{ $approval->status === 'approved' ? 'success' : 'danger' }}-emphasis ms-2">
                                                            {{ ucfirst($approval->status) }}
                                                        </span>
                                                    </h6>
                                                    <small class="text-muted">
                                                        <i class="fa-solid fa-user me-1"></i>{{ $approval->approver->name ?? 'Unknown' }}
                                                    </small>
                                                </div>
                                                <small class="text-muted">
                                                    <i class="fa-solid fa-clock me-1"></i>{{ $approval->approved_at->diffForHumans() }}
                                                </small>
                                            </div>
                                            @if($approval->comments)
                                            <div class="border-top pt-2 mt-2">
                                                <small class="text-muted d-block mb-1"><i class="fa-solid fa-comment me-1"></i>Comments:</small>
                                                <p class="mb-0 small">{{ $approval->comments }}</p>
                                            </div>
                                            @endif
                                        </div>
                                    </div>
                                </div>
                            </div>
                            @endforeach
                        </div>
                        @else
                        <div class="text-center py-5 text-muted">
                            <i class="fa-solid fa-hourglass-half fa-3x mb-3 d-block opacity-25"></i>
                            <p class="mb-0">No approvals recorded yet</p>
                            <small>This leave request is pending approval</small>
                        </div>
                        @endif
                    </div>
                </div>
            </div>
        </div>

        <x-slot name="footer">
            <button type="button" class="btn btn-secondary" data-bs-dismiss="modal" wire:click="closeModal">
                Close
            </button>
            @if($canApprove && in_array($selectedLeave->status, ['Awaiting', 'pending']))
            <button type="button" class="btn btn-danger" wire:click="openRejectModal('{{ $selectedLeave->id }}')">
                <i class="fa-solid fa-times me-1"></i> Reject
            </button>
            <button type="button" class="btn btn-success"
                    wire:click="approveLeave('{{ $selectedLeave->id }}')"
                    wire:confirm="Are you sure you want to approve this leave request?">
                <i class="fa-solid fa-check me-1"></i> Approve
            </button>
            @endif
        </x-slot>
    </x-forms.modal>
    @endif

    <!-- Rejection Reason Modal -->
    @if($showRejectModal)
    <div class="modal fade show d-block" tabindex="-1" style="background-color: rgba(0,0,0,0.5);">
        <div class="modal-dialog modal-dialog-centered">
            <div class="modal-content">
                <div class="modal-header bg-danger text-white">
                    <h5 class="modal-title">
                        <i class="fa-solid fa-times-circle me-2"></i>Reject Leave Request
                    </h5>
                    <button type="button" class="btn-close btn-close-white" wire:click="closeRejectModal"></button>
                </div>
                <div class="modal-body">
                    <div class="mb-3">
                        <label class="form-label fw-semibold">Reason for Rejection <span class="text-danger">*</span></label>
                        <textarea wire:model="rejectionReason"
                                  class="form-control @error('rejectionReason') is-invalid @enderror"
                                  rows="4"
                                  placeholder="Please provide a reason for rejecting this leave request..."></textarea>
                        @error('rejectionReason')
                        <div class="invalid-feedback">{{ $message }}</div>
                        @enderror
                    </div>
                    <div class="alert alert-warning mb-0">
                        <i class="fa-solid fa-exclamation-triangle me-2"></i>
                        <small>This action cannot be undone. The employee will be notified of the rejection.</small>
                    </div>
                </div>
                <div class="modal-footer">
                    <button type="button" class="btn btn-secondary" wire:click="closeRejectModal">Cancel</button>
                    <button type="button" class="btn btn-danger" wire:click="rejectLeave">
                        <i class="fa-solid fa-times me-1"></i> Confirm Rejection
                    </button>
                </div>
            </div>
        </div>
    </div>
    @endif

    @script
    <script>
        let modalInstance = null;

        $wire.on('open-leave-modal', () => {
            setTimeout(() => {
                const modalElement = document.getElementById('leaveDetailsModal');
                if (modalElement) {
                    modalInstance = new bootstrap.Modal(modalElement);
                    modalInstance.show();
                }
            }, 100);
        });
    </script>
    @endscript
</div>
