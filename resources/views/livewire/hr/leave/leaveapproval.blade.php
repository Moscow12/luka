<div>
    <!-- Page header -->
    <div class="row mb-4">
        <div class="col-12">
            <div class="d-flex justify-content-between align-items-center">
                <h2 class="mb-0">Leave Approvals</h2>
            </div>
        </div>
    </div>

    @if(!$canApprove)
    <!-- No Permission Warning -->
    <div class="alert alert-warning alert-dismissible fade show" role="alert">
        <h5 class="alert-heading"><i class="fa-solid fa-exclamation-triangle me-2"></i>No Approval Permission</h5>
        <p>You do not have permission to approve leave requests. Please contact your system administrator if you believe this is an error.</p>
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
                    @if(session()->has('success'))
                    <div class="alert alert-success alert-dismissible fade show" role="alert">
                        {{ session('success') }}
                        <button type="button" class="btn-close" data-bs-dismiss="alert" aria-label="Close"></button>
                    </div>
                    @endif

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
                                            <div class="avatar avatar-sm rounded-circle bg-primary-subtle text-primary-emphasis">
                                                {{ substr($leave->employee->getFullName() ?? 'U', 0, 1) }}
                                            </div>
                                            <div>
                                                <div class="fw-semibold">{{ $leave->employee->getFullName() ?? 'Unknown' }}</div>
                                                <small class="text-muted">{{ $leave->employee->employee_number }}</small>
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
                                    <td>{{ $leave->number_of_days }} {{ Str::plural('day', $leave->number_of_days) }}</td>
                                    <td>
                                        @if($leave->status === 'Awaiting')
                                        <span class="badge bg-warning-subtle text-warning-emphasis">Awaiting</span>
                                        @elseif($leave->status === 'Active')
                                        <span class="badge bg-info-subtle text-info-emphasis">In Progress</span>
                                        @elseif($leave->status === 'Approved')
                                        <span class="badge bg-success-subtle text-success-emphasis">Approved</span>
                                        @else
                                        <span class="badge bg-danger-subtle text-danger-emphasis">Rejected</span>
                                        @endif
                                    </td>
                                    <td>
                                        <div class="d-flex flex-column gap-1">
                                            @if($leave->approvalnote->count() > 0)
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
                                        @if($leave->canUserApprove && in_array($leave->status, ['Awaiting', 'Active']))
                                        <div class="d-flex gap-2">
                                            <button wire:click="openApprovalModal('{{ $leave->id }}', 'approve')"
                                                    class="btn btn-sm btn-success"
                                                    data-bs-toggle="tooltip"
                                                    title="Approve">
                                                <i class="fa-solid fa-check"></i>
                                            </button>
                                            <button wire:click="openApprovalModal('{{ $leave->id }}', 'reject')"
                                                    class="btn btn-sm btn-danger"
                                                    data-bs-toggle="tooltip"
                                                    title="Reject">
                                                <i class="fa-solid fa-times"></i>
                                            </button>
                                        </div>
                                        @else
                                        <span class="text-muted small">-</span>
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

    <!-- Approval/Rejection Modal -->
    @if($showModal && $selectedLeave)
    <x-forms.modal
        id="approvalModal"
        title="{{ $actionType === 'approve' ? 'Approve' : 'Reject' }} Leave Request"
        size="modal-lg"
        :centered="true">

        <form wire:submit.prevent="processApproval">
            <!-- Approval Level Info -->
            @if($currentApprovalLevel)
            <div class="alert alert-info mb-3">
                <strong>Approval Level:</strong> {{ $currentApprovalLevel->name }}
                <span class="badge bg-primary ms-2">Level {{ $currentApprovalLevel->level_order }}</span>
            </div>
            @endif

            <div class="mb-4">
                <div class="card bg-light">
                    <div class="card-body">
                        <h5 class="card-title mb-3">Leave Request Details</h5>
                        <div class="row g-3">
                            <div class="col-md-6">
                                <label class="form-label fw-semibold">Employee</label>
                                <p class="mb-0">{{ $selectedLeave->employee->getFullName() ?? 'Unknown' }}</p>
                            </div>
                            <div class="col-md-6">
                                <label class="form-label fw-semibold">Leave Type</label>
                                <p class="mb-0">{{ $selectedLeave->leave->name ?? 'N/A' }}</p>
                            </div>
                            <div class="col-md-6">
                                <label class="form-label fw-semibold">Start Date</label>
                                <p class="mb-0">{{ \Carbon\Carbon::parse($selectedLeave->start_date)->format('d M Y') }}</p>
                            </div>
                            <div class="col-md-6">
                                <label class="form-label fw-semibold">End Date</label>
                                <p class="mb-0">{{ \Carbon\Carbon::parse($selectedLeave->end_date)->format('d M Y') }}</p>
                            </div>
                            <div class="col-md-6">
                                <label class="form-label fw-semibold">Number of Days</label>
                                <p class="mb-0">{{ $selectedLeave->number_of_days }} {{ Str::plural('day', $selectedLeave->number_of_days) }}</p>
                            </div>
                            <div class="col-md-6">
                                <label class="form-label fw-semibold">Requested On</label>
                                <p class="mb-0">{{ $selectedLeave->created_at->format('d M Y H:i') }}</p>
                            </div>
                            <div class="col-12">
                                <label class="form-label fw-semibold">Reason</label>
                                <p class="mb-0">{{ $selectedLeave->reason ?? 'No reason provided' }}</p>
                            </div>
                        </div>
                    </div>
                </div>
            </div>

            <!-- Previous Approvals -->
            @if($selectedLeave->approvalnote->count() > 0)
            <div class="mb-4">
                <h6 class="mb-3">Approval History</h6>
                <div class="list-group">
                    @foreach($selectedLeave->approvalnote as $approval)
                    <div class="list-group-item">
                        <div class="d-flex justify-content-between align-items-start">
                            <div>
                                <h6 class="mb-1">
                                    {{ $approval->approval_level->name ?? 'N/A' }}
                                    <span class="badge bg-{{ $approval->status === 'approved' ? 'success' : 'danger' }}-subtle text-{{ $approval->status === 'approved' ? 'success' : 'danger' }}-emphasis">
                                        {{ ucfirst($approval->status) }}
                                    </span>
                                </h6>
                                <small class="text-muted">
                                    By: {{ $approval->approver->name ?? 'Unknown' }} •
                                    {{ $approval->approved_at->format('d M Y H:i') }}
                                </small>
                                @if($approval->comments)
                                <p class="mb-0 mt-2"><small>{{ $approval->comments }}</small></p>
                                @endif
                            </div>
                        </div>
                    </div>
                    @endforeach
                </div>
            </div>
            @endif

            <div class="mb-3">
                <label for="comments" class="form-label">
                    {{ $actionType === 'approve' ? 'Approval' : 'Rejection' }} Comments
                    <span class="text-muted">(Optional)</span>
                </label>
                <textarea
                    wire:model="comments"
                    class="form-control"
                    id="comments"
                    rows="3"
                    placeholder="Add any comments or notes..."></textarea>
            </div>

            <div class="alert alert-{{ $actionType === 'approve' ? 'success' : 'warning' }}" role="alert">
                <i class="fa-solid fa-{{ $actionType === 'approve' ? 'check-circle' : 'exclamation-triangle' }} me-2"></i>
                Are you sure you want to {{ $actionType }} this leave request at
                <strong>{{ $currentApprovalLevel->name ?? 'this level' }}</strong>?
            </div>

            <x-slot name="footer">
                <button type="button" class="btn btn-secondary" data-bs-dismiss="modal" wire:click="closeModal">Cancel</button>
                <button type="submit" class="btn btn-{{ $actionType === 'approve' ? 'success' : 'danger' }}">
                    {{ $actionType === 'approve' ? 'Approve' : 'Reject' }}
                </button>
            </x-slot>
        </form>
    </x-forms.modal>

    @push('scripts')
    <script>
        document.addEventListener('livewire:init', () => {
            let modalInstance = null;

            Livewire.on('open-modal', () => {
                const modalElement = document.getElementById('approvalModal');
                modalInstance = new bootstrap.Modal(modalElement);
                modalInstance.show();
            });

            Livewire.on('close-modal', () => {
                if (modalInstance) {
                    modalInstance.hide();
                    modalInstance = null;
                }
            });
        });
    </script>
    @endpush
    @endif
</div>
