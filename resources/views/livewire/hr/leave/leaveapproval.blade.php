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
                        Pending Requests
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
                                    <th>Start Date</th>
                                    <th>End Date</th>
                                    <th>Days</th>
                                    <th>Reason</th>
                                    <th>Requested On</th>
                                    <th>Status</th>
                                    @if($statusFilter === 'Awaiting')
                                    <th>Actions</th>
                                    @endif
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
                                    <td>{{ \Carbon\Carbon::parse($leave->start_date)->format('d M Y') }}</td>
                                    <td>{{ \Carbon\Carbon::parse($leave->end_date)->format('d M Y') }}</td>
                                    <td>{{ $leave->number_of_days }} {{ Str::plural('day', $leave->number_of_days) }}</td>
                                    <td>
                                        <span class="text-truncate d-inline-block" style="max-width: 200px;"
                                              data-bs-toggle="tooltip" title="{{ $leave->reason }}">
                                            {{ $leave->reason ?? '-' }}
                                        </span>
                                    </td>
                                    <td>{{ $leave->created_at->format('d M Y') }}</td>
                                    <td>
                                        @if($leave->status === 'Awaiting')
                                        <span class="badge bg-warning-subtle text-warning-emphasis">Pending</span>
                                        @elseif($leave->status === 'Approved')
                                        <span class="badge bg-success-subtle text-success-emphasis">Approved</span>
                                        @else
                                        <span class="badge bg-danger-subtle text-danger-emphasis">Rejected</span>
                                        @endif
                                    </td>
                                    @if($statusFilter === 'Awaiting')
                                    <td>
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
                                    </td>
                                    @endif
                                </tr>
                                @empty
                                <tr>
                                    <td colspan="{{ $statusFilter === 'Awaiting' ? 9 : 8 }}" class="text-center py-5">
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

            <div class="mb-3">
                <label for="approvalNote" class="form-label">
                    {{ $actionType === 'approve' ? 'Approval' : 'Rejection' }} Note
                    <span class="text-muted">(Optional)</span>
                </label>
                <textarea
                    wire:model="approvalNote"
                    class="form-control"
                    id="approvalNote"
                    rows="3"
                    placeholder="Add any comments or notes..."></textarea>
            </div>

            <div class="alert alert-{{ $actionType === 'approve' ? 'success' : 'warning' }}" role="alert">
                <i class="fa-solid fa-{{ $actionType === 'approve' ? 'check-circle' : 'exclamation-triangle' }} me-2"></i>
                Are you sure you want to {{ $actionType }} this leave request?
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
