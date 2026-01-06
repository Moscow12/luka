<div>
    <!-- Page header -->
    <div class="row mb-4">
        <div class="col-12">
            <div class="d-flex justify-content-between align-items-center">
                <h2 class="mb-0">Leave Approvals</h2>
            </div>
        </div>
    </div>

    @if(session()->has('success'))
    <div class="alert alert-success alert-dismissible fade show" role="alert">
        {{ session('success') }}
        <button type="button" class="btn-close" data-bs-dismiss="alert" aria-label="Close"></button>
    </div>
    @endif

    @if(session()->has('error'))
    <div class="alert alert-danger alert-dismissible fade show" role="alert">
        {{ session('error') }}
        <button type="button" class="btn-close" data-bs-dismiss="alert" aria-label="Close"></button>
    </div>
    @endif

    @if(!$canApprove)
    <!-- No Permission Warning -->
    <div class="alert alert-warning" role="alert">
        <h5 class="alert-heading"><i class="fa-solid fa-exclamation-triangle me-2"></i>No Approval Permission</h5>
        <p class="mb-0">You do not have permission to approve leave requests. Please contact your system administrator if you believe this is an error.</p>
    </div>
    @else
    <!-- Status Filter -->
    <div class="row mb-4">
        <div class="col-12">
            <ul class="nav nav-pills gap-2">
                <li class="nav-item">
                    <button wire:click="$set('statusFilter', 'Awaiting')"
                            class="nav-link {{ $statusFilter === 'Awaiting' ? 'active' : '' }}">
                        Pending Approvals
                    </button>
                </li>
                <li class="nav-item">
                    <button wire:click="$set('statusFilter', 'Active')"
                            class="nav-link {{ $statusFilter === 'Active' ? 'active' : '' }}">
                        In Progress
                    </button>
                </li>
                <li class="nav-item">
                    <button wire:click="$set('statusFilter', 'Approved')"
                            class="nav-link {{ $statusFilter === 'Approved' ? 'active' : '' }}">
                        Approved
                    </button>
                </li>
                <li class="nav-item">
                    <button wire:click="$set('statusFilter', 'Rejected')"
                            class="nav-link {{ $statusFilter === 'Rejected' ? 'active' : '' }}">
                        Rejected
                    </button>
                </li>
            </ul>
        </div>
    </div>

    <!-- Leave Requests Table -->
    <div class="row">
        <div class="col-12">
            <div class="card">
                <div class="card-body">
                    <div class="table-responsive">
                        <table class="table table-hover">
                            <thead>
                                <tr>
                                    <th>Employee</th>
                                    <th>Leave Type</th>
                                    <th>Period</th>
                                    <th>Days</th>
                                    <th>Status</th>
                                    <th>Approval Progress</th>
                                    <th>Actions</th>
                                </tr>
                            </thead>
                            <tbody>
                                @forelse($pendingLeaves as $leave)
                                <tr>
                                    <td>
                                        <div class="d-flex align-items-center gap-2">
                                            <div class="avatar avatar-sm rounded-circle bg-primary-subtle text-primary-emphasis d-flex align-items-center justify-content-center" style="width: 36px; height: 36px;">
                                                {{ substr($leave->employee->first_name ?? 'U', 0, 1) }}
                                            </div>
                                            <div>
                                                <div class="fw-semibold">{{ $leave->employee->first_name ?? '' }} {{ $leave->employee->last_name ?? '' }}</div>
                                                <small class="text-muted">{{ $leave->employee->employee_no ?? '' }}</small>
                                            </div>
                                        </div>
                                    </td>
                                    <td>
                                        <span class="badge bg-info-subtle text-info-emphasis">
                                            {{ $leave->leave->name ?? 'N/A' }}
                                        </span>
                                    </td>
                                    <td>
                                        <div>{{ \Carbon\Carbon::parse($leave->start_date)->format('d M Y') }}</div>
                                        <small class="text-muted">to {{ \Carbon\Carbon::parse($leave->end_date)->format('d M Y') }}</small>
                                    </td>
                                    <td>{{ $leave->days ?? 0 }} {{ Str::plural('day', $leave->days ?? 0) }}</td>
                                    <td>
                                        @php
                                            $status = strtolower($leave->status ?? '');
                                        @endphp
                                        @if(in_array($status, ['awaiting', 'pending']))
                                        <span class="badge bg-warning-subtle text-warning-emphasis">Awaiting</span>
                                        @elseif($status === 'active')
                                        <span class="badge bg-info-subtle text-info-emphasis">In Progress</span>
                                        @elseif($status === 'approved')
                                        <span class="badge bg-success-subtle text-success-emphasis">Approved</span>
                                        @else
                                        <span class="badge bg-danger-subtle text-danger-emphasis">Rejected</span>
                                        @endif
                                    </td>
                                    <td>
                                        <div class="d-flex flex-column gap-1">
                                            @if($leave->approvalnote && $leave->approvalnote->count() > 0)
                                                @foreach($leave->approvalnote as $approval)
                                                <small>
                                                    <i class="fa-solid fa-{{ $approval->status === 'approved' ? 'check text-success' : 'times text-danger' }}"></i>
                                                    {{ $approval->approval_level->name ?? 'N/A' }}
                                                    <span class="text-muted">({{ $approval->approver->name ?? 'Unknown' }})</span>
                                                </small>
                                                @endforeach
                                            @endif
                                            @if($leave->nextApprovalLevel)
                                                <small class="text-primary">
                                                    <i class="fa-solid fa-clock"></i>
                                                    Next: {{ $leave->nextApprovalLevel->name }}
                                                </small>
                                            @endif
                                        </div>
                                    </td>
                                    <td>
                                        @if($leave->canUserApprove && in_array(strtolower($leave->status ?? ''), ['awaiting', 'active', 'pending']))
                                        <div class="d-flex gap-2">
                                            <button type="button"
                                                    wire:click="approveLeave('{{ $leave->id }}')"
                                                    wire:loading.attr="disabled"
                                                    class="btn btn-sm btn-success">
                                                <span wire:loading.remove wire:target="approveLeave('{{ $leave->id }}')">
                                                    <i class="fa-solid fa-check"></i> Approve
                                                </span>
                                                <span wire:loading wire:target="approveLeave('{{ $leave->id }}')">
                                                    <i class="fa-solid fa-spinner fa-spin"></i>
                                                </span>
                                            </button>
                                            <button type="button"
                                                    wire:click="openRejectModal('{{ $leave->id }}')"
                                                    wire:loading.attr="disabled"
                                                    class="btn btn-sm btn-danger">
                                                <i class="fa-solid fa-times"></i> Reject
                                            </button>
                                        </div>
                                        @else
                                        <span class="text-muted small">
                                            @if(!$leave->nextApprovalLevel)
                                                Fully processed
                                            @else
                                                Awaiting other approver
                                            @endif
                                        </span>
                                        @endif
                                    </td>
                                </tr>
                                @empty
                                <tr>
                                    <td colspan="7" class="text-center py-5">
                                        <div class="text-muted">
                                            <i class="fa-solid fa-inbox fa-3x mb-3"></i>
                                            <p>No {{ strtolower($statusFilter) }} leave requests found.</p>
                                        </div>
                                    </td>
                                </tr>
                                @endforelse
                            </tbody>
                        </table>
                    </div>

                    <!-- Pagination -->
                    @if($pendingLeaves->hasPages())
                    <div class="mt-3">
                        {{ $pendingLeaves->links() }}
                    </div>
                    @endif
                </div>
            </div>
        </div>
    </div>
    @endif

    <!-- Rejection Modal -->
    @if($showModal && $selectedLeave)
    <div class="modal fade show d-block" tabindex="-1" style="background-color: rgba(0,0,0,0.5);">
        <div class="modal-dialog modal-dialog-centered modal-dialog-scrollable">
            <div class="modal-content">
                <form wire:submit.prevent="rejectLeave">
                    <div class="modal-header">
                        <h5 class="modal-title">
                            <i class="fa-solid fa-times-circle text-danger me-2"></i>
                            Reject Leave Request
                        </h5>
                        <button type="button" class="btn-close" wire:click="closeModal"></button>
                    </div>
                    <div class="modal-body">
                        <div class="mb-3">
                            <div class="card bg-light">
                                <div class="card-body py-2">
                                    <p class="mb-1"><strong>Employee:</strong> {{ $selectedLeave->employee->first_name ?? '' }} {{ $selectedLeave->employee->last_name ?? '' }}</p>
                                    <p class="mb-1"><strong>Leave Type:</strong> {{ $selectedLeave->leave->name ?? 'N/A' }}</p>
                                    <p class="mb-0"><strong>Period:</strong> {{ \Carbon\Carbon::parse($selectedLeave->start_date)->format('d M Y') }} - {{ \Carbon\Carbon::parse($selectedLeave->end_date)->format('d M Y') }}</p>
                                </div>
                            </div>
                        </div>

                        <div class="mb-3">
                            <label for="comments" class="form-label">Rejection Reason <span class="text-danger">*</span></label>
                            <textarea
                                wire:model="comments"
                                class="form-control @error('comments') is-invalid @enderror"
                                id="comments"
                                rows="3"
                                placeholder="Please provide a reason for rejection..."
                                required></textarea>
                            @error('comments')
                            <div class="invalid-feedback">{{ $message }}</div>
                            @enderror
                        </div>

                        <div class="alert alert-warning" role="alert">
                            <i class="fa-solid fa-exclamation-triangle me-2"></i>
                            Are you sure you want to reject this leave request?
                        </div>
                    </div>
                    <div class="modal-footer">
                        <button type="button" class="btn btn-secondary" wire:click="closeModal">Cancel</button>
                        <button type="submit" class="btn btn-danger" wire:loading.attr="disabled">
                            <span wire:loading.remove wire:target="rejectLeave">
                                <i class="fa-solid fa-times me-1"></i> Reject
                            </span>
                            <span wire:loading wire:target="rejectLeave">
                                <i class="fa-solid fa-spinner fa-spin me-1"></i> Processing...
                            </span>
                        </button>
                    </div>
                </form>
            </div>
        </div>
    </div>
    @endif
</div>
