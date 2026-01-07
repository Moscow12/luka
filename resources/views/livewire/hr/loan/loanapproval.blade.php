<div>
    <div>
        <h5 class="mb-5">Loan Approvals</h5>
    </div>

    {{-- No Employee Account Warning --}}
    @if(!$hasEmployeeAccount)
        <div class="alert alert-danger d-flex align-items-center" role="alert">
            <i class="fa-solid fa-circle-exclamation fa-2x me-3"></i>
            <div>
                <h5 class="alert-heading mb-1">Account Not Linked</h5>
                <p class="mb-0">Your user account is not linked to any employee record. Please contact HR to link your account before you can approve loans.</p>
            </div>
        </div>
    @else
        <div class="d-flex flex-column gap-6">
            {{-- Approver Info Card --}}
            <div class="card bg-light border-0">
                <div class="card-body py-3">
                    <div class="d-flex align-items-center gap-3">
                        <div class="avatar avatar-lg bg-success text-white rounded-circle d-flex align-items-center justify-content-center">
                            <i class="fa-solid fa-user-check"></i>
                        </div>
                        <div>
                            <h6 class="mb-0">{{ $currentEmployee->first_name }} {{ $currentEmployee->middle_name }} {{ $currentEmployee->last_name }}</h6>
                            <small class="text-muted">
                                <span class="badge bg-success me-1">Loan Approver</span>
                                {{ $currentEmployee->department?->name ?? 'No Department' }}
                            </small>
                        </div>
                    </div>
                </div>
            </div>

            {{-- Status Tabs --}}
            <ul class="nav nav-pills gap-2" role="tablist">
                <li class="nav-item">
                    <button
                        class="nav-link {{ $statusFilter === 'pending' ? 'active' : '' }}"
                        wire:click="$set('statusFilter', 'pending')"
                    >
                        <i class="fa-solid fa-clock me-1"></i>
                        Pending
                        <span class="badge bg-warning text-dark ms-1">{{ $pendingCount }}</span>
                    </button>
                </li>
                <li class="nav-item">
                    <button
                        class="nav-link {{ $statusFilter === 'approved' ? 'active' : '' }}"
                        wire:click="$set('statusFilter', 'approved')"
                    >
                        <i class="fa-solid fa-check me-1"></i>
                        Approved
                        <span class="badge bg-success ms-1">{{ $approvedCount }}</span>
                    </button>
                </li>
                <li class="nav-item">
                    <button
                        class="nav-link {{ $statusFilter === 'rejected' ? 'active' : '' }}"
                        wire:click="$set('statusFilter', 'rejected')"
                    >
                        <i class="fa-solid fa-xmark me-1"></i>
                        Rejected
                        <span class="badge bg-danger ms-1">{{ $rejectedCount }}</span>
                    </button>
                </li>
            </ul>

            {{-- Search --}}
            <div class="d-flex flex-md-row flex-column gap-3 justify-content-between">
                <div>
                    <input
                        class="form-control"
                        type="search"
                        wire:model.live.debounce.300ms="search"
                        placeholder="Search by employee name or ID..."
                    />
                </div>
            </div>

            {{-- Alerts --}}
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

            {{-- Data Table --}}
            <div>
                <div class="card card-lg overflow-hidden">
                    <div class="card-body p-0">
                        <div class="table-responsive">
                            <table class="table text-nowrap mb-0 table-centered table-hover">
                                <thead class="table-light">
                                    <tr>
                                        <th class="ps-3">#</th>
                                        <th>Employee</th>
                                        <th>Loan Type</th>
                                        <th>Amount</th>
                                        <th>Interest</th>
                                        <th>Period</th>
                                        <th>Installment</th>
                                        <th>Status</th>
                                        <th>Request Date</th>
                                        <th class="text-end pe-3">Actions</th>
                                    </tr>
                                </thead>
                                <tbody>
                                    @forelse($loanRequests as $index => $loan)
                                        <tr>
                                            <td class="ps-3">{{ $loanRequests->firstItem() + $index }}</td>
                                            <td>
                                                <div class="d-flex flex-column">
                                                    <span class="fw-medium">
                                                        {{ $loan->employee?->first_name }} {{ $loan->employee?->last_name }}
                                                    </span>
                                                    <small class="text-muted">{{ $loan->employee?->employee_no }}</small>
                                                </div>
                                            </td>
                                            <td>
                                                <span class="badge bg-secondary-subtle text-secondary">
                                                    {{ $loan->loan_item?->name ?? 'N/A' }}
                                                </span>
                                            </td>
                                            <td class="fw-medium">{{ number_format($loan->amount, 2) }}</td>
                                            <td>
                                                <span class="badge bg-info-subtle text-info">
                                                    {{ $loan->interest_rate }}% ({{ $loan->interest_type === 'flat' ? 'Flat' : 'R/B' }})
                                                </span>
                                            </td>
                                            <td>
                                                <span class="badge bg-primary-subtle text-primary">
                                                    {{ $loan->repayment_period_months }} {{ Str::plural('month', $loan->repayment_period_months) }}
                                                </span>
                                            </td>
                                            <td class="fw-medium text-success">{{ number_format($loan->installment_amount, 2) }}</td>
                                            <td>
                                                @php
                                                    $statusColors = [
                                                        'pending' => 'warning',
                                                        'under_review' => 'info',
                                                        'approved' => 'success',
                                                        'rejected' => 'danger',
                                                        'disbursed' => 'primary',
                                                        'active' => 'success',
                                                        'closed' => 'secondary',
                                                        'cancelled' => 'dark',
                                                    ];
                                                    $color = $statusColors[$loan->status] ?? 'secondary';
                                                @endphp
                                                <span class="badge bg-{{ $color }}">
                                                    {{ ucfirst(str_replace('_', ' ', $loan->status)) }}
                                                </span>
                                            </td>
                                            <td>{{ \Carbon\Carbon::parse($loan->request_date)->format('d M Y') }}</td>
                                            <td class="text-end pe-3">
                                                @if(in_array($loan->status, ['pending', 'under_review']))
                                                    <div class="d-flex gap-1 justify-content-end">
                                                        @if($loan->status === 'pending')
                                                            <button
                                                                class="btn btn-sm btn-outline-info"
                                                                wire:click="markUnderReview('{{ $loan->id }}')"
                                                                title="Mark Under Review"
                                                            >
                                                                <i class="fa-solid fa-eye"></i>
                                                            </button>
                                                        @endif
                                                        <button
                                                            class="btn btn-sm btn-success"
                                                            wire:click="openApproveModal('{{ $loan->id }}')"
                                                            title="Approve"
                                                        >
                                                            <i class="fa-solid fa-check"></i>
                                                        </button>
                                                        <button
                                                            class="btn btn-sm btn-danger"
                                                            wire:click="openRejectModal('{{ $loan->id }}')"
                                                            title="Reject"
                                                        >
                                                            <i class="fa-solid fa-xmark"></i>
                                                        </button>
                                                    </div>
                                                @else
                                                    @if($loan->approved_amount)
                                                        <small class="text-muted">
                                                            Approved: {{ number_format($loan->approved_amount, 2) }}
                                                        </small>
                                                    @else
                                                        <span class="text-muted">-</span>
                                                    @endif
                                                @endif
                                            </td>
                                        </tr>
                                    @empty
                                        <tr>
                                            <td colspan="10" class="text-center py-5">
                                                <div class="d-flex flex-column align-items-center gap-3">
                                                    <i class="fa-solid fa-clipboard-check fa-3x text-muted"></i>
                                                    <div>
                                                        <p class="text-muted mb-0">No {{ $statusFilter }} loan requests found</p>
                                                    </div>
                                                </div>
                                            </td>
                                        </tr>
                                    @endforelse
                                </tbody>
                            </table>
                        </div>

                        {{-- Pagination --}}
                        @if($loanRequests->hasPages())
                            <div class="card-footer border-top border-dashed d-flex flex-md-row flex-column justify-content-md-between align-items-md-center gap-3">
                                <div class="text-muted small">
                                    Showing {{ $loanRequests->firstItem() }} to {{ $loanRequests->lastItem() }} of {{ $loanRequests->total() }} entries
                                </div>
                                <div>
                                    {{ $loanRequests->links() }}
                                </div>
                            </div>
                        @endif
                    </div>
                </div>
            </div>
        </div>

        {{-- Approval/Rejection Modal --}}
        @if($showModal && $selectedLoan)
            <div class="modal fade show d-block" tabindex="-1" style="background: rgba(0,0,0,0.5);">
                <div class="modal-dialog {{ $modalMode === 'approve' ? 'modal-xl' : 'modal-md' }}">
                    <div class="modal-content">
                        <div class="modal-header {{ $modalMode === 'approve' ? 'bg-success text-white' : 'bg-danger text-white' }}">
                            <h5 class="modal-title">
                                @if($modalMode === 'approve')
                                    <i class="fa-solid fa-check-circle me-2"></i>
                                    Approve Loan Request
                                @else
                                    <i class="fa-solid fa-times-circle me-2"></i>
                                    Reject Loan Request
                                @endif
                            </h5>
                            <button type="button" class="btn-close btn-close-white" wire:click="$set('showModal', false)"></button>
                        </div>
                        <form wire:submit.prevent="{{ $modalMode === 'approve' ? 'approve' : 'reject' }}">
                            <div class="modal-body">
                                {{-- Employee Info --}}
                                <div class="card bg-light border-0 mb-4">
                                    <div class="card-body">
                                        <div class="row">
                                            <div class="col-md-6">
                                                <small class="text-muted d-block">Employee</small>
                                                <strong>{{ $selectedLoan->employee?->first_name }} {{ $selectedLoan->employee?->last_name }}</strong>
                                                <small class="text-muted">({{ $selectedLoan->employee?->employee_no }})</small>
                                            </div>
                                            <div class="col-md-6">
                                                <small class="text-muted d-block">Loan Type</small>
                                                <strong>{{ $selectedLoan->loan_item?->name ?? 'N/A' }}</strong>
                                            </div>
                                        </div>
                                        <hr class="my-3">
                                        <div class="row text-center">
                                            <div class="col-4">
                                                <small class="text-muted d-block">Requested Amount</small>
                                                <strong class="text-primary">{{ number_format($selectedLoan->amount, 2) }}</strong>
                                            </div>
                                            <div class="col-4">
                                                <small class="text-muted d-block">Interest Rate</small>
                                                <strong>{{ $selectedLoan->interest_rate }}%</strong>
                                                <small class="text-muted">({{ $selectedLoan->interest_type === 'flat' ? 'Flat' : 'R/B' }})</small>
                                            </div>
                                            <div class="col-4">
                                                <small class="text-muted d-block">Period</small>
                                                <strong>{{ $selectedLoan->repayment_period_months }} months</strong>
                                            </div>
                                        </div>
                                        <hr class="my-3">
                                        <div>
                                            <small class="text-muted d-block">Reason for Loan</small>
                                            <p class="mb-0">{{ $selectedLoan->reason }}</p>
                                        </div>
                                    </div>
                                </div>

                                @if($modalMode === 'approve')
                                    <div class="row">
                                        {{-- Left Column: Approval Form --}}
                                        <div class="col-lg-5">
                                            <h6 class="text-muted mb-3">
                                                <i class="fa-solid fa-edit me-1"></i>
                                                Approval Details
                                            </h6>

                                            {{-- Approved Amount --}}
                                            <div class="form-floating mb-3">
                                                <input
                                                    type="number"
                                                    class="form-control @error('approved_amount') is-invalid @enderror"
                                                    id="approved_amount"
                                                    wire:model.live.debounce.500ms="approved_amount"
                                                    placeholder="0.00"
                                                    step="1000"
                                                    min="1"
                                                    max="{{ $selectedLoan->amount }}"
                                                />
                                                <label for="approved_amount">Approved Amount (Max: {{ number_format($selectedLoan->amount, 2) }})</label>
                                                @error('approved_amount')
                                                    <small class="text-danger">{{ $message }}</small>
                                                @enderror
                                            </div>

                                            {{-- Remarks --}}
                                            <div class="form-floating mb-3">
                                                <textarea
                                                    class="form-control @error('remarks') is-invalid @enderror"
                                                    id="remarks"
                                                    wire:model="remarks"
                                                    placeholder="Remarks"
                                                    style="height: 100px"
                                                ></textarea>
                                                <label for="remarks">Remarks (Optional)</label>
                                                @error('remarks')
                                                    <small class="text-danger">{{ $message }}</small>
                                                @enderror
                                            </div>
                                        </div>

                                        {{-- Right Column: Calculation Summary --}}
                                        <div class="col-lg-7">
                                            <h6 class="text-muted mb-3">
                                                <i class="fa-solid fa-calculator me-1"></i>
                                                New Loan Calculation
                                            </h6>

                                            @if($approved_amount)
                                                {{-- Summary Cards --}}
                                                <div class="row g-3 mb-4">
                                                    <div class="col-6">
                                                        <div class="card bg-primary text-white">
                                                            <div class="card-body text-center py-2">
                                                                <small class="d-block opacity-75">Approved Amount</small>
                                                                <h6 class="mb-0">{{ number_format($approved_amount, 2) }}</h6>
                                                            </div>
                                                        </div>
                                                    </div>
                                                    <div class="col-6">
                                                        <div class="card bg-info text-white">
                                                            <div class="card-body text-center py-2">
                                                                <small class="d-block opacity-75">Total Interest</small>
                                                                <h6 class="mb-0">{{ number_format($interest_amount, 2) }}</h6>
                                                            </div>
                                                        </div>
                                                    </div>
                                                    <div class="col-6">
                                                        <div class="card bg-secondary text-white">
                                                            <div class="card-body text-center py-2">
                                                                <small class="d-block opacity-75">Total Repayment</small>
                                                                <h6 class="mb-0">{{ number_format($total_repayment, 2) }}</h6>
                                                            </div>
                                                        </div>
                                                    </div>
                                                    <div class="col-6">
                                                        <div class="card bg-success text-white">
                                                            <div class="card-body text-center py-2">
                                                                <small class="d-block opacity-75">Monthly Installment</small>
                                                                <h6 class="mb-0">{{ number_format($installment_amount, 2) }}</h6>
                                                            </div>
                                                        </div>
                                                    </div>
                                                </div>

                                                {{-- Payment Schedule Toggle --}}
                                                <button
                                                    type="button"
                                                    class="btn btn-outline-primary btn-sm w-100 mb-3"
                                                    wire:click="toggleSchedule"
                                                >
                                                    <i class="fa-solid fa-calendar-days me-1"></i>
                                                    {{ $showSchedule ? 'Hide' : 'View' }} Payment Schedule
                                                </button>

                                                {{-- Payment Schedule Table --}}
                                                @if($showSchedule && count($payment_schedule) > 0)
                                                    <div class="table-responsive" style="max-height: 200px; overflow-y: auto;">
                                                        <table class="table table-sm table-bordered mb-0">
                                                            <thead class="table-light sticky-top">
                                                                <tr>
                                                                    <th>#</th>
                                                                    <th>Date</th>
                                                                    <th>Principal</th>
                                                                    <th>Interest</th>
                                                                    <th>Payment</th>
                                                                    <th>Balance</th>
                                                                </tr>
                                                            </thead>
                                                            <tbody>
                                                                @foreach($payment_schedule as $schedule)
                                                                    <tr>
                                                                        <td>{{ $schedule['month'] }}</td>
                                                                        <td>{{ $schedule['date'] }}</td>
                                                                        <td>{{ number_format($schedule['principal'], 2) }}</td>
                                                                        <td>{{ number_format($schedule['interest'], 2) }}</td>
                                                                        <td class="fw-medium">{{ number_format($schedule['payment'], 2) }}</td>
                                                                        <td>{{ number_format($schedule['balance'], 2) }}</td>
                                                                    </tr>
                                                                @endforeach
                                                            </tbody>
                                                        </table>
                                                    </div>
                                                @endif
                                            @endif
                                        </div>
                                    </div>
                                @else
                                    {{-- Rejection Form --}}
                                    <div class="form-floating mb-3">
                                        <textarea
                                            class="form-control @error('remarks') is-invalid @enderror"
                                            id="remarks"
                                            wire:model="remarks"
                                            placeholder="Reason for rejection"
                                            style="height: 120px"
                                            required
                                        ></textarea>
                                        <label for="remarks">Reason for Rejection *</label>
                                        @error('remarks')
                                            <small class="text-danger">{{ $message }}</small>
                                        @enderror
                                    </div>
                                    <div class="alert alert-warning">
                                        <i class="fa-solid fa-triangle-exclamation me-1"></i>
                                        <small>Please provide a clear reason for rejecting this loan request.</small>
                                    </div>
                                @endif
                            </div>

                            <div class="modal-footer">
                                <button type="button" class="btn btn-secondary" wire:click="$set('showModal', false)">
                                    Cancel
                                </button>
                                @if($modalMode === 'approve')
                                    <button type="submit" class="btn btn-success" @if(!$approved_amount) disabled @endif>
                                        <i class="fa-solid fa-check me-1"></i>
                                        Approve Loan
                                    </button>
                                @else
                                    <button type="submit" class="btn btn-danger">
                                        <i class="fa-solid fa-xmark me-1"></i>
                                        Reject Loan
                                    </button>
                                @endif
                            </div>
                        </form>
                    </div>
                </div>
            </div>
        @endif
    @endif
</div>
