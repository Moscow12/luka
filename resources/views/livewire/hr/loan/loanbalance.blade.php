<div>
    <div>
        <h5 class="mb-5">My Loan Balance</h5>
    </div>

    {{-- No Employee Account Warning --}}
    @if(!$hasEmployeeAccount)
        <div class="alert alert-danger d-flex align-items-center" role="alert">
            <i class="fa-solid fa-circle-exclamation fa-2x me-3"></i>
            <div>
                <h5 class="alert-heading mb-1">Account Not Linked</h5>
                <p class="mb-0">Your user account is not linked to any employee record. Please contact HR to link your account.</p>
            </div>
        </div>
    @else
        <div class="d-flex flex-column gap-6">
            {{-- Employee Info Card --}}
            <div class="card bg-light border-0">
                <div class="card-body py-3">
                    <div class="d-flex align-items-center gap-3">
                        <div class="avatar avatar-lg bg-primary text-white rounded-circle d-flex align-items-center justify-content-center">
                            <i class="fa-solid fa-user"></i>
                        </div>
                        <div>
                            <h6 class="mb-0">{{ $currentEmployee->first_name }} {{ $currentEmployee->middle_name }} {{ $currentEmployee->last_name }}</h6>
                            <small class="text-muted">
                                <span class="badge bg-secondary me-1">{{ $currentEmployee->employee_no }}</span>
                                {{ $currentEmployee->department?->name ?? 'No Department' }}
                            </small>
                        </div>
                    </div>
                </div>
            </div>

            {{-- Summary Cards --}}
            <div class="row g-4">
                <div class="col-md-4">
                    <div class="card border-0 bg-primary text-white h-100">
                        <div class="card-body">
                            <div class="d-flex justify-content-between align-items-center">
                                <div>
                                    <p class="mb-1 opacity-75">Active Loans</p>
                                    <h2 class="mb-0">{{ $totalActiveLoans }}</h2>
                                </div>
                                <div class="opacity-50">
                                    <i class="fa-solid fa-file-invoice-dollar fa-3x"></i>
                                </div>
                            </div>
                        </div>
                    </div>
                </div>
                <div class="col-md-4">
                    <div class="card border-0 bg-danger text-white h-100">
                        <div class="card-body">
                            <div class="d-flex justify-content-between align-items-center">
                                <div>
                                    <p class="mb-1 opacity-75">Outstanding Balance</p>
                                    <h2 class="mb-0">{{ number_format($totalOutstandingBalance, 2) }}</h2>
                                </div>
                                <div class="opacity-50">
                                    <i class="fa-solid fa-scale-unbalanced fa-3x"></i>
                                </div>
                            </div>
                        </div>
                    </div>
                </div>
                <div class="col-md-4">
                    <div class="card border-0 bg-warning text-dark h-100">
                        <div class="card-body">
                            <div class="d-flex justify-content-between align-items-center">
                                <div>
                                    <p class="mb-1 opacity-75">Monthly Installment</p>
                                    <h2 class="mb-0">{{ number_format($totalMonthlyInstallment, 2) }}</h2>
                                </div>
                                <div class="opacity-50">
                                    <i class="fa-solid fa-calendar-check fa-3x"></i>
                                </div>
                            </div>
                        </div>
                    </div>
                </div>
            </div>

            {{-- Loans List --}}
            <div>
                <h6 class="text-muted mb-3">
                    <i class="fa-solid fa-list me-1"></i>
                    My Loans
                </h6>

                @if($loans->count() > 0)
                    <div class="row g-4">
                        @foreach($loans as $loan)
                            <div class="col-lg-6">
                                <div class="card h-100 {{ $loan->status === 'closed' ? 'border-success' : '' }}">
                                    <div class="card-header bg-transparent d-flex justify-content-between align-items-center">
                                        <div>
                                            <span class="badge bg-secondary-subtle text-secondary">
                                                {{ $loan->loan_item?->name ?? 'Loan' }}
                                            </span>
                                            @php
                                                $statusColors = [
                                                    'approved' => 'info',
                                                    'disbursed' => 'primary',
                                                    'active' => 'success',
                                                    'closed' => 'secondary',
                                                ];
                                                $color = $statusColors[$loan->status] ?? 'secondary';
                                            @endphp
                                            <span class="badge bg-{{ $color }} ms-1">
                                                {{ ucfirst($loan->status) }}
                                            </span>
                                        </div>
                                        <small class="text-muted">
                                            {{ \Carbon\Carbon::parse($loan->request_date)->format('d M Y') }}
                                        </small>
                                    </div>
                                    <div class="card-body">
                                        {{-- Loan Summary --}}
                                        <div class="row g-3 mb-3">
                                            <div class="col-6">
                                                <small class="text-muted d-block">Total Loan</small>
                                                <strong class="text-primary">{{ number_format($loan->progress['total'], 2) }}</strong>
                                            </div>
                                            <div class="col-6">
                                                <small class="text-muted d-block">Monthly Installment</small>
                                                <strong class="text-info">{{ number_format($loan->progress['installment'], 2) }}</strong>
                                            </div>
                                            <div class="col-6">
                                                <small class="text-muted d-block">Total Paid</small>
                                                <strong class="text-success">{{ number_format($loan->progress['paid'], 2) }}</strong>
                                            </div>
                                            <div class="col-6">
                                                <small class="text-muted d-block">Remaining Balance</small>
                                                <strong class="text-danger">{{ number_format($loan->progress['balance'], 2) }}</strong>
                                            </div>
                                        </div>

                                        {{-- Progress Bar --}}
                                        <div class="mb-3">
                                            <div class="d-flex justify-content-between mb-1">
                                                <small class="text-muted">Payment Progress</small>
                                                <small class="fw-medium">{{ $loan->progress['progress'] }}%</small>
                                            </div>
                                            <div class="progress" style="height: 10px;">
                                                <div
                                                    class="progress-bar {{ $loan->progress['progress'] >= 100 ? 'bg-success' : 'bg-primary' }}"
                                                    role="progressbar"
                                                    style="width: {{ $loan->progress['progress'] }}%"
                                                ></div>
                                            </div>
                                        </div>

                                        {{-- Payment Info --}}
                                        <div class="d-flex justify-content-between align-items-center mb-3">
                                            <div>
                                                <small class="text-muted">Payments Made:</small>
                                                <span class="badge bg-success ms-1">{{ $loan->progress['payments_made'] }}</span>
                                            </div>
                                            <div>
                                                <small class="text-muted">Payments Remaining:</small>
                                                <span class="badge bg-warning text-dark ms-1">{{ $loan->progress['payments_remaining'] }}</span>
                                            </div>
                                        </div>

                                        {{-- Interest Info --}}
                                        <div class="d-flex gap-2 text-muted small">
                                            <span>
                                                <i class="fa-solid fa-percent me-1"></i>
                                                {{ $loan->interest_rate }}% p.a.
                                            </span>
                                            <span>|</span>
                                            <span>
                                                <i class="fa-solid fa-calculator me-1"></i>
                                                {{ $loan->interest_type === 'flat' ? 'Flat Rate' : 'Reducing Balance' }}
                                            </span>
                                            <span>|</span>
                                            <span>
                                                <i class="fa-solid fa-calendar me-1"></i>
                                                {{ $loan->repayment_period_months }} months
                                            </span>
                                        </div>
                                    </div>
                                    <div class="card-footer bg-transparent">
                                        <button
                                            class="btn btn-sm btn-outline-primary w-100"
                                            wire:click="openDetailsModal('{{ $loan->id }}')"
                                        >
                                            <i class="fa-solid fa-eye me-1"></i>
                                            View Details & Payment History
                                        </button>
                                    </div>
                                </div>
                            </div>
                        @endforeach
                    </div>
                @else
                    <div class="card">
                        <div class="card-body text-center py-5">
                            <i class="fa-solid fa-file-invoice-dollar fa-3x text-muted mb-3"></i>
                            <p class="text-muted mb-3">You don't have any loans yet</p>
                            <a href="{{ route('loan.requestloan') }}" class="btn btn-primary btn-sm">
                                <i class="fa-solid fa-plus me-1"></i>
                                Request a Loan
                            </a>
                        </div>
                    </div>
                @endif
            </div>
        </div>

        {{-- Loan Details Modal --}}
        @if($showDetailsModal && $selectedLoan)
            <div class="modal fade show d-block" tabindex="-1" style="background: rgba(0,0,0,0.5);">
                <div class="modal-dialog modal-xl">
                    <div class="modal-content">
                        <div class="modal-header bg-primary text-white">
                            <h5 class="modal-title">
                                <i class="fa-solid fa-file-invoice-dollar me-2"></i>
                                Loan Details - {{ $selectedLoan->loan_item?->name ?? 'Loan' }}
                            </h5>
                            <button type="button" class="btn-close btn-close-white" wire:click="$set('showDetailsModal', false)"></button>
                        </div>
                        <div class="modal-body">
                            {{-- Loan Summary --}}
                            <div class="row g-3 mb-4">
                                <div class="col-md-3 col-6">
                                    <div class="card bg-primary text-white h-100">
                                        <div class="card-body text-center py-3">
                                            <small class="d-block opacity-75">Total Loan</small>
                                            <h5 class="mb-0">{{ number_format($total_loan, 2) }}</h5>
                                        </div>
                                    </div>
                                </div>
                                <div class="col-md-3 col-6">
                                    <div class="card bg-success text-white h-100">
                                        <div class="card-body text-center py-3">
                                            <small class="d-block opacity-75">Total Paid</small>
                                            <h5 class="mb-0">{{ number_format($total_paid, 2) }}</h5>
                                        </div>
                                    </div>
                                </div>
                                <div class="col-md-3 col-6">
                                    <div class="card bg-danger text-white h-100">
                                        <div class="card-body text-center py-3">
                                            <small class="d-block opacity-75">Balance</small>
                                            <h5 class="mb-0">{{ number_format($remaining_balance, 2) }}</h5>
                                        </div>
                                    </div>
                                </div>
                                <div class="col-md-3 col-6">
                                    <div class="card bg-info text-white h-100">
                                        <div class="card-body text-center py-3">
                                            <small class="d-block opacity-75">Installment</small>
                                            <h5 class="mb-0">{{ number_format($expected_installment, 2) }}</h5>
                                        </div>
                                    </div>
                                </div>
                            </div>

                            {{-- Progress --}}
                            <div class="mb-4">
                                @php
                                    $progress = $total_loan > 0 ? min(100, ($total_paid / $total_loan) * 100) : 0;
                                @endphp
                                <div class="d-flex justify-content-between mb-2">
                                    <span>
                                        <strong>{{ $payments_made }}</strong> payments made
                                    </span>
                                    <span>
                                        <strong>{{ $payments_remaining }}</strong> payments remaining
                                    </span>
                                </div>
                                <div class="progress" style="height: 20px;">
                                    <div
                                        class="progress-bar {{ $progress >= 100 ? 'bg-success' : 'bg-primary' }}"
                                        role="progressbar"
                                        style="width: {{ $progress }}%"
                                    >
                                        {{ round($progress, 1) }}%
                                    </div>
                                </div>
                            </div>

                            <div class="row">
                                {{-- Payment History --}}
                                <div class="col-lg-6">
                                    <h6 class="text-muted mb-3">
                                        <i class="fa-solid fa-history me-1"></i>
                                        Payment History ({{ count($paymentHistory) }})
                                    </h6>

                                    @if(count($paymentHistory) > 0)
                                        <div class="table-responsive" style="max-height: 300px; overflow-y: auto;">
                                            <table class="table table-sm table-bordered mb-0">
                                                <thead class="table-light sticky-top">
                                                    <tr>
                                                        <th>#</th>
                                                        <th>Date</th>
                                                        <th>Amount</th>
                                                        <th>Status</th>
                                                    </tr>
                                                </thead>
                                                <tbody>
                                                    @foreach($paymentHistory as $index => $payment)
                                                        <tr>
                                                            <td>{{ $index + 1 }}</td>
                                                            <td>{{ \Carbon\Carbon::parse($payment['payment_date'])->format('d M Y') }}</td>
                                                            <td class="text-success fw-medium">{{ number_format($payment['amount'], 2) }}</td>
                                                            <td>
                                                                <span class="badge bg-success">{{ ucfirst($payment['status']) }}</span>
                                                            </td>
                                                        </tr>
                                                    @endforeach
                                                </tbody>
                                            </table>
                                        </div>
                                    @else
                                        <div class="text-center py-4 text-muted bg-light rounded">
                                            <i class="fa-solid fa-receipt fa-2x mb-2 opacity-50"></i>
                                            <p class="mb-0 small">No payments recorded yet</p>
                                        </div>
                                    @endif
                                </div>

                                {{-- Remaining Schedule --}}
                                <div class="col-lg-6">
                                    <h6 class="text-muted mb-3">
                                        <i class="fa-solid fa-calendar-alt me-1"></i>
                                        Upcoming Payments (Next {{ count($remainingSchedule) }})
                                    </h6>

                                    @if(count($remainingSchedule) > 0)
                                        <div class="table-responsive" style="max-height: 300px; overflow-y: auto;">
                                            <table class="table table-sm table-bordered mb-0">
                                                <thead class="table-light sticky-top">
                                                    <tr>
                                                        <th>#</th>
                                                        <th>Due Date</th>
                                                        <th>Amount</th>
                                                        <th>Balance After</th>
                                                    </tr>
                                                </thead>
                                                <tbody>
                                                    @foreach($remainingSchedule as $schedule)
                                                        <tr>
                                                            <td>{{ $schedule['month'] }}</td>
                                                            <td>{{ $schedule['date'] }}</td>
                                                            <td class="text-primary fw-medium">{{ number_format($schedule['payment'], 2) }}</td>
                                                            <td>{{ number_format($schedule['balance'], 2) }}</td>
                                                        </tr>
                                                    @endforeach
                                                </tbody>
                                            </table>
                                        </div>
                                    @else
                                        <div class="text-center py-4 bg-success-subtle text-success rounded">
                                            <i class="fa-solid fa-check-circle fa-2x mb-2"></i>
                                            <p class="mb-0 small">Loan fully paid!</p>
                                        </div>
                                    @endif
                                </div>
                            </div>

                            {{-- Loan Details --}}
                            <div class="mt-4">
                                <h6 class="text-muted mb-3">
                                    <i class="fa-solid fa-info-circle me-1"></i>
                                    Loan Information
                                </h6>
                                <div class="row g-3">
                                    <div class="col-md-3 col-6">
                                        <small class="text-muted d-block">Principal Amount</small>
                                        <strong>{{ number_format($selectedLoan->approved_amount ?: $selectedLoan->amount, 2) }}</strong>
                                    </div>
                                    <div class="col-md-3 col-6">
                                        <small class="text-muted d-block">Interest Rate</small>
                                        <strong>{{ $selectedLoan->interest_rate }}% p.a.</strong>
                                    </div>
                                    <div class="col-md-3 col-6">
                                        <small class="text-muted d-block">Interest Type</small>
                                        <strong>{{ $selectedLoan->interest_type === 'flat' ? 'Flat Rate' : 'Reducing Balance' }}</strong>
                                    </div>
                                    <div class="col-md-3 col-6">
                                        <small class="text-muted d-block">Tenure</small>
                                        <strong>{{ $selectedLoan->repayment_period_months }} months</strong>
                                    </div>
                                    <div class="col-md-6">
                                        <small class="text-muted d-block">Request Date</small>
                                        <strong>{{ \Carbon\Carbon::parse($selectedLoan->request_date)->format('d M Y') }}</strong>
                                    </div>
                                    <div class="col-md-6">
                                        <small class="text-muted d-block">Approval Date</small>
                                        <strong>{{ $selectedLoan->approval_date ? \Carbon\Carbon::parse($selectedLoan->approval_date)->format('d M Y') : '-' }}</strong>
                                    </div>
                                    @if($selectedLoan->reason)
                                        <div class="col-12">
                                            <small class="text-muted d-block">Reason</small>
                                            <p class="mb-0">{{ $selectedLoan->reason }}</p>
                                        </div>
                                    @endif
                                </div>
                            </div>
                        </div>

                        <div class="modal-footer">
                            <button type="button" class="btn btn-secondary" wire:click="$set('showDetailsModal', false)">
                                Close
                            </button>
                        </div>
                    </div>
                </div>
            </div>
        @endif
    @endif
</div>
