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
    <!-- Status Filter + Search -->
    <div class="row mb-4 g-2 align-items-center">
        <div class="col-12 col-lg-8">
            <ul class="nav nav-pills gap-2">
                <li class="nav-item">
                    <button wire:click="$set('statusFilter', 'Awaiting')"
                            class="nav-link position-relative {{ $statusFilter === 'Awaiting' ? 'active' : '' }}">
                        Pending Approvals
                        @if($myPendingCount > 0)
                            <span class="badge bg-danger ms-1">{{ $myPendingCount }}</span>
                        @endif
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
        <div class="col-12 col-lg-4">
            <div class="input-group">
                <span class="input-group-text bg-white">
                    <i class="fa-solid fa-magnifying-glass text-muted"></i>
                </span>
                <input type="search" wire:model.live.debounce.300ms="search"
                       class="form-control"
                       placeholder="Search by employee name or number...">
                @if($search)
                <button class="btn btn-outline-secondary" type="button" wire:click="$set('search', '')">
                    <i class="fa-solid fa-times"></i>
                </button>
                @endif
            </div>
        </div>
    </div>

    <!-- Date Range Filter -->
    <div class="row mb-4 g-2 align-items-end">
        <div class="col-6 col-md-3">
            <label class="form-label small text-muted mb-1">From Date</label>
            <div class="input-group">
                <input class="form-control flatpickr"
                       type="text" placeholder="Select Date" wire:model.live="dateFrom" />
                <span class="input-group-text bg-light">
                    <i class="fa-solid fa-calendar text-muted"></i>
                </span>
            </div>
        </div>
        <div class="col-6 col-md-3">
            <label class="form-label small text-muted mb-1">To Date</label>
            <div class="input-group">
                <input class="form-control flatpickr"
                       type="text" placeholder="Select Date" wire:model.live="dateTo" />
                <span class="input-group-text bg-light">
                    <i class="fa-solid fa-calendar text-muted"></i>
                </span>
            </div>
        </div>
        <div class="col-12 col-md-3">
            @if($dateFrom || $dateTo)
            <button type="button" class="btn btn-outline-secondary" wire:click="clearDateFilter">
                <i class="fa-solid fa-rotate-left me-1"></i> Clear Dates
            </button>
            @endif
        </div>
    </div>

    @php
        $sortIcon = function ($field) use ($sortField, $sortDirection) {
            if ($sortField !== $field) {
                return 'fa-sort text-muted';
            }
            return $sortDirection === 'asc' ? 'fa-sort-up text-primary' : 'fa-sort-down text-primary';
        };
    @endphp

    <!-- Leave Requests Table -->
    <div class="row">
        <div class="col-12">
            <div class="card">
                <div class="card-body">
                    <div class="table-responsive">
                        <table class="table table-hover align-middle mb-0">
                            <thead class="table-light">
                                <tr>
                                    <th style="width: 50px;">#</th>
                                    <th>Employee</th>
                                    <th>Leave Type</th>
                                    <th role="button" wire:click="sortBy('start_date')" class="user-select-none">
                                        Period <i class="fa-solid {{ $sortIcon('start_date') }} ms-1"></i>
                                    </th>
                                    <th role="button" wire:click="sortBy('days')" class="user-select-none">
                                        Days <i class="fa-solid {{ $sortIcon('days') }} ms-1"></i>
                                    </th>
                                    <th role="button" wire:click="sortBy('status')" class="user-select-none">
                                        Status <i class="fa-solid {{ $sortIcon('status') }} ms-1"></i>
                                    </th>
                                    <th>Approval Progress</th>
                                    <th role="button" wire:click="sortBy('created_at')" class="user-select-none">
                                        Submitted <i class="fa-solid {{ $sortIcon('created_at') }} ms-1"></i>
                                    </th>
                                    <th>Actions</th>
                                </tr>
                            </thead>
                            <tbody>
                                @php
                                    $num = ($pendingLeaves->firstItem() ?? 1);
                                @endphp
                                @forelse($pendingLeaves as $leave)
                                <tr wire:key="leave-{{ $leave->id }}">
                                    <td>{{ $num++ }}</td>
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
                                        <div>{{ $leave->created_at?->format('d M Y') }}</div>
                                        <small class="text-muted">{{ $leave->created_at?->diffForHumans() }}</small>
                                    </td>
                                    <td>
                                        @if($leave->canUserApprove && in_array(strtolower($leave->status ?? ''), ['awaiting', 'active', 'pending']))
                                        <div class="d-flex gap-2">
                                            <button type="button"
                                                    wire:click="openApproveModal('{{ $leave->id }}')"
                                                    wire:loading.attr="disabled"
                                                    class="btn btn-sm btn-success">
                                                <i class="fa-solid fa-check"></i> Approve
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
                                    <td colspan="9" class="text-center py-5">
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

                    <!-- Datatable footer: results summary + per-page selector + pagination -->
                    <div class="d-flex flex-column flex-md-row justify-content-between align-items-md-center gap-3 mt-3 border-top pt-3">
                        <div class="d-flex align-items-center gap-2">
                            <label class="form-label mb-0 text-nowrap small text-muted">Rows per page:</label>
                            <select wire:model.live="perPage" class="form-select form-select-sm" style="width: auto;">
                                <option value="10">10</option>
                                <option value="25">25</option>
                                <option value="50">50</option>
                                <option value="100">100</option>
                            </select>
                        </div>

                        <div class="text-muted small">
                            @if($pendingLeaves->total() > 0)
                                Showing {{ $pendingLeaves->firstItem() }} to {{ $pendingLeaves->lastItem() }}
                                of {{ $pendingLeaves->total() }} results
                            @else
                                No results
                            @endif
                        </div>

                        <div>
                            @if($pendingLeaves->hasPages())
                                {{ $pendingLeaves->links() }}
                            @endif
                        </div>
                    </div>
                </div>
            </div>
        </div>
    </div>
    @endif

    <!-- Approve / Reject Modal -->
    @if($showModal && $selectedLeave)
    @php $isApprove = $actionType === 'approve'; @endphp
    <div class="modal fade show d-block" tabindex="-1" style="background-color: rgba(0,0,0,0.5);">
        <div class="modal-dialog modal-dialog-centered modal-dialog-scrollable">
            <div class="modal-content">
                <form wire:submit.prevent="submitDecision">
                    <div class="modal-header">
                        <h5 class="modal-title">
                            <i class="fa-solid fa-{{ $isApprove ? 'check-circle text-success' : 'times-circle text-danger' }} me-2"></i>
                            {{ $isApprove ? 'Approve' : 'Reject' }} Leave Request
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
                            <label for="comments" class="form-label">
                                {{ $isApprove ? 'Approval Comment' : 'Rejection Reason' }} <span class="text-danger">*</span>
                            </label>
                            <textarea
                                wire:model="comments"
                                class="form-control @error('comments') is-invalid @enderror"
                                id="comments"
                                rows="3"
                                placeholder="{{ $isApprove ? 'Add a comment for this approval...' : 'Please provide a reason for rejection...' }}"
                                required></textarea>
                            @error('comments')
                            <div class="invalid-feedback">{{ $message }}</div>
                            @enderror
                        </div>

                        <div class="alert {{ $isApprove ? 'alert-info' : 'alert-warning' }}" role="alert">
                            <i class="fa-solid fa-exclamation-triangle me-2"></i>
                            Are you sure you want to {{ $isApprove ? 'approve' : 'reject' }} this leave request?
                        </div>
                    </div>
                    <div class="modal-footer">
                        <button type="button" class="btn btn-secondary" wire:click="closeModal">Cancel</button>
                        <button type="submit" class="btn {{ $isApprove ? 'btn-success' : 'btn-danger' }}" wire:loading.attr="disabled">
                            <span wire:loading.remove wire:target="submitDecision">
                                <i class="fa-solid fa-{{ $isApprove ? 'check' : 'times' }} me-1"></i> {{ $isApprove ? 'Approve' : 'Reject' }}
                            </span>
                            <span wire:loading wire:target="submitDecision">
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
