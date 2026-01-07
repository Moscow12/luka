<div>
    <div>
        <h5 class="mb-5">Loan Payments</h5>
    </div>

    {{-- No Employee Account Warning --}}
    @if(!$hasEmployeeAccount)
        <div class="alert alert-danger d-flex align-items-center" role="alert">
            <i class="fa-solid fa-circle-exclamation fa-2x me-3"></i>
            <div>
                <h5 class="alert-heading mb-1">Account Not Linked</h5>
                <p class="mb-0">Your user account is not linked to any employee record. Please contact HR to link your account before you can record payments.</p>
            </div>
        </div>
    @else
        <div class="d-flex flex-column gap-6">
            {{-- Recorder Info Card --}}
            <div class="card bg-light border-0">
                <div class="card-body py-3">
                    <div class="d-flex align-items-center gap-3">
                        <div class="avatar avatar-lg bg-primary text-white rounded-circle d-flex align-items-center justify-content-center">
                            <i class="fa-solid fa-money-bill-wave"></i>
                        </div>
                        <div>
                            <h6 class="mb-0">{{ $currentEmployee->first_name }} {{ $currentEmployee->middle_name }} {{ $currentEmployee->last_name }}</h6>
                            <small class="text-muted">
                                <span class="badge bg-primary me-1">Payment Recorder</span>
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
                        class="nav-link {{ $statusFilter === 'active' ? 'active' : '' }}"
                        wire:click="$set('statusFilter', 'active')"
                    >
                        <i class="fa-solid fa-spinner me-1"></i>
                        Active Loans
                        <span class="badge bg-primary ms-1">{{ $activeCount }}</span>
                    </button>
                </li>
                <li class="nav-item">
                    <button
                        class="nav-link {{ $statusFilter === 'closed' ? 'active' : '' }}"
                        wire:click="$set('statusFilter', 'closed')"
                    >
                        <i class="fa-solid fa-check-circle me-1"></i>
                        Closed Loans
                        <span class="badge bg-secondary ms-1">{{ $closedCount }}</span>
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
                                        <th>Total Loan</th>
                                        <th>Paid</th>
                                        <th>Balance</th>
                                        <th>Progress</th>
                                        <th>Status</th>
                                        <th class="text-end pe-3">Actions</th>
                                    </tr>
                                </thead>
                                <tbody>
                                    @forelse($loans as $index => $loan)
                                        <tr>
                                            <td class="ps-3">{{ $loans->firstItem() + $index }}</td>
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
                                            <td class="fw-medium">{{ number_format($loan->progress['total'], 2) }}</td>
                                            <td class="text-success fw-medium">{{ number_format($loan->progress['paid'], 2) }}</td>
                                            <td class="text-danger fw-medium">{{ number_format($loan->progress['balance'], 2) }}</td>
                                            <td style="min-width: 150px;">
                                                <div class="d-flex align-items-center gap-2">
                                                    <div class="progress flex-grow-1" style="height: 8px;">
                                                        <div
                                                            class="progress-bar {{ $loan->progress['progress'] >= 100 ? 'bg-success' : 'bg-primary' }}"
                                                            role="progressbar"
                                                            style="width: {{ $loan->progress['progress'] }}%"
                                                        ></div>
                                                    </div>
                                                    <small class="text-muted">{{ $loan->progress['progress'] }}%</small>
                                                </div>
                                            </td>
                                            <td>
                                                @php
                                                    $statusColors = [
                                                        'approved' => 'info',
                                                        'disbursed' => 'primary',
                                                        'active' => 'success',
                                                        'closed' => 'secondary',
                                                    ];
                                                    $color = $statusColors[$loan->status] ?? 'secondary';
                                                @endphp
                                                <span class="badge bg-{{ $color }}">
                                                    {{ ucfirst($loan->status) }}
                                                </span>
                                            </td>
                                            <td class="text-end pe-3">
                                                <div class="d-flex gap-1 justify-content-end">
                                                    <button
                                                        class="btn btn-sm btn-outline-info"
                                                        wire:click="openHistoryModal('{{ $loan->id }}')"
                                                        title="View Payment History"
                                                    >
                                                        <i class="fa-solid fa-history"></i>
                                                    </button>
                                                    @if($loan->status === 'approved')
                                                        <button
                                                            class="btn btn-sm btn-warning"
                                                            wire:click="disburse('{{ $loan->id }}')"
                                                            wire:confirm="Mark this loan as disbursed?"
                                                            title="Mark as Disbursed"
                                                        >
                                                            <i class="fa-solid fa-hand-holding-dollar"></i>
                                                        </button>
                                                    @endif
                                                    @if(in_array($loan->status, ['approved', 'disbursed', 'active']) && $loan->progress['balance'] > 0)
                                                        <button
                                                            class="btn btn-sm btn-success"
                                                            wire:click="openPaymentModal('{{ $loan->id }}')"
                                                            title="Record Payment"
                                                        >
                                                            <i class="fa-solid fa-plus"></i>
                                                        </button>
                                                    @endif
                                                </div>
                                            </td>
                                        </tr>
                                    @empty
                                        <tr>
                                            <td colspan="9" class="text-center py-5">
                                                <div class="d-flex flex-column align-items-center gap-3">
                                                    <i class="fa-solid fa-money-bill-transfer fa-3x text-muted"></i>
                                                    <div>
                                                        <p class="text-muted mb-0">No {{ $statusFilter }} loans found</p>
                                                    </div>
                                                </div>
                                            </td>
                                        </tr>
                                    @endforelse
                                </tbody>
                            </table>
                        </div>

                        {{-- Pagination --}}
                        @if($loans->hasPages())
                            <div class="card-footer border-top border-dashed d-flex flex-md-row flex-column justify-content-md-between align-items-md-center gap-3">
                                <div class="text-muted small">
                                    Showing {{ $loans->firstItem() }} to {{ $loans->lastItem() }} of {{ $loans->total() }} entries
                                </div>
                                <div>
                                    {{ $loans->links() }}
                                </div>
                            </div>
                        @endif
                    </div>
                </div>
            </div>
        </div>

        {{-- Payment Modal --}}
        @if($showModal && $selectedLoan)
            <div class="modal fade show d-block" tabindex="-1" style="background: rgba(0,0,0,0.5);">
                <div class="modal-dialog modal-lg">
                    <div class="modal-content">
                        <div class="modal-header bg-success text-white">
                            <h5 class="modal-title">
                                <i class="fa-solid fa-money-bill-wave me-2"></i>
                                Record Payment
                            </h5>
                            <button type="button" class="btn-close btn-close-white" wire:click="$set('showModal', false)"></button>
                        </div>
                        <form wire:submit.prevent="recordPayment">
                            <div class="modal-body">
                                {{-- Employee & Loan Info --}}
                                <div class="card bg-light border-0 mb-4">
                                    <div class="card-body">
                                        <div class="row mb-3">
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

                                        {{-- Loan Summary Cards --}}
                                        <div class="row g-3">
                                            <div class="col-3">
                                                <div class="text-center p-2 rounded bg-white">
                                                    <small class="text-muted d-block">Total Loan</small>
                                                    <strong class="text-primary">{{ number_format($total_loan, 2) }}</strong>
                                                </div>
                                            </div>
                                            <div class="col-3">
                                                <div class="text-center p-2 rounded bg-white">
                                                    <small class="text-muted d-block">Total Paid</small>
                                                    <strong class="text-success">{{ number_format($total_paid, 2) }}</strong>
                                                </div>
                                            </div>
                                            <div class="col-3">
                                                <div class="text-center p-2 rounded bg-white">
                                                    <small class="text-muted d-block">Balance</small>
                                                    <strong class="text-danger">{{ number_format($remaining_balance, 2) }}</strong>
                                                </div>
                                            </div>
                                            <div class="col-3">
                                                <div class="text-center p-2 rounded bg-white">
                                                    <small class="text-muted d-block">Installment</small>
                                                    <strong class="text-info">{{ number_format($expected_installment, 2) }}</strong>
                                                </div>
                                            </div>
                                        </div>

                                        {{-- Progress Bar --}}
                                        <div class="mt-3">
                                            @php
                                                $progress = $total_loan > 0 ? min(100, ($total_paid / $total_loan) * 100) : 0;
                                            @endphp
                                            <div class="d-flex justify-content-between mb-1">
                                                <small class="text-muted">Payment Progress</small>
                                                <small class="text-muted">{{ round($progress, 1) }}%</small>
                                            </div>
                                            <div class="progress" style="height: 10px;">
                                                <div
                                                    class="progress-bar bg-success"
                                                    role="progressbar"
                                                    style="width: {{ $progress }}%"
                                                ></div>
                                            </div>
                                        </div>
                                    </div>
                                </div>

                                {{-- Payment Form --}}
                                <h6 class="text-muted mb-3">
                                    <i class="fa-solid fa-edit me-1"></i>
                                    Payment Details
                                </h6>

                                <div class="row">
                                    <div class="col-md-6">
                                        <div class="form-floating mb-3">
                                            <input
                                                type="number"
                                                class="form-control @error('payment_amount') is-invalid @enderror"
                                                id="payment_amount"
                                                wire:model="payment_amount"
                                                placeholder="0.00"
                                                step="0.01"
                                                min="1"
                                                max="{{ $remaining_balance }}"
                                            />
                                            <label for="payment_amount">Payment Amount</label>
                                            @error('payment_amount')
                                                <small class="text-danger">{{ $message }}</small>
                                            @enderror
                                            <small class="text-muted">Max: {{ number_format($remaining_balance, 2) }}</small>
                                        </div>
                                    </div>
                                    <div class="col-md-6">
                                        <div class="form-floating mb-3">
                                            <input
                                                type="date"
                                                class="form-control @error('payment_date') is-invalid @enderror"
                                                id="payment_date"
                                                wire:model="payment_date"
                                                max="{{ now()->format('Y-m-d') }}"
                                            />
                                            <label for="payment_date">Payment Date</label>
                                            @error('payment_date')
                                                <small class="text-danger">{{ $message }}</small>
                                            @enderror
                                        </div>
                                    </div>
                                </div>

                                <div class="form-floating mb-3">
                                    <textarea
                                        class="form-control @error('remarks') is-invalid @enderror"
                                        id="remarks"
                                        wire:model="remarks"
                                        placeholder="Remarks"
                                        style="height: 80px"
                                    ></textarea>
                                    <label for="remarks">Remarks (Optional)</label>
                                    @error('remarks')
                                        <small class="text-danger">{{ $message }}</small>
                                    @enderror
                                </div>

                                {{-- Quick Amount Buttons --}}
                                <div class="d-flex flex-wrap gap-2 mb-3">
                                    <small class="text-muted w-100">Quick Select:</small>
                                    <button
                                        type="button"
                                        class="btn btn-sm btn-outline-primary"
                                        wire:click="$set('payment_amount', {{ $expected_installment }})"
                                    >
                                        1 Installment ({{ number_format($expected_installment, 2) }})
                                    </button>
                                    @if($remaining_balance >= $expected_installment * 2)
                                        <button
                                            type="button"
                                            class="btn btn-sm btn-outline-primary"
                                            wire:click="$set('payment_amount', {{ $expected_installment * 2 }})"
                                        >
                                            2 Installments ({{ number_format($expected_installment * 2, 2) }})
                                        </button>
                                    @endif
                                    <button
                                        type="button"
                                        class="btn btn-sm btn-outline-success"
                                        wire:click="$set('payment_amount', {{ $remaining_balance }})"
                                    >
                                        Full Balance ({{ number_format($remaining_balance, 2) }})
                                    </button>
                                </div>
                            </div>

                            <div class="modal-footer">
                                <button type="button" class="btn btn-secondary" wire:click="$set('showModal', false)">
                                    Cancel
                                </button>
                                <button type="submit" class="btn btn-success" @if(!$payment_amount) disabled @endif>
                                    <i class="fa-solid fa-check me-1"></i>
                                    Record Payment
                                </button>
                            </div>
                        </form>
                    </div>
                </div>
            </div>
        @endif

        {{-- Payment History Modal --}}
        @if($showHistoryModal && $selectedLoan)
            <div class="modal fade show d-block" tabindex="-1" style="background: rgba(0,0,0,0.5);">
                <div class="modal-dialog modal-lg">
                    <div class="modal-content">
                        <div class="modal-header bg-info text-white">
                            <h5 class="modal-title">
                                <i class="fa-solid fa-history me-2"></i>
                                Payment History
                            </h5>
                            <button type="button" class="btn-close btn-close-white" wire:click="$set('showHistoryModal', false)"></button>
                        </div>
                        <div class="modal-body">
                            {{-- Employee & Loan Info --}}
                            <div class="card bg-light border-0 mb-4">
                                <div class="card-body">
                                    <div class="row mb-3">
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

                                    {{-- Loan Summary --}}
                                    <div class="row g-3">
                                        <div class="col-4">
                                            <div class="text-center p-2 rounded bg-white">
                                                <small class="text-muted d-block">Total Loan</small>
                                                <strong class="text-primary">{{ number_format($total_loan, 2) }}</strong>
                                            </div>
                                        </div>
                                        <div class="col-4">
                                            <div class="text-center p-2 rounded bg-white">
                                                <small class="text-muted d-block">Total Paid</small>
                                                <strong class="text-success">{{ number_format($total_paid, 2) }}</strong>
                                            </div>
                                        </div>
                                        <div class="col-4">
                                            <div class="text-center p-2 rounded bg-white">
                                                <small class="text-muted d-block">Balance</small>
                                                <strong class="text-danger">{{ number_format($remaining_balance, 2) }}</strong>
                                            </div>
                                        </div>
                                    </div>

                                    {{-- Progress Bar --}}
                                    <div class="mt-3">
                                        @php
                                            $progress = $total_loan > 0 ? min(100, ($total_paid / $total_loan) * 100) : 0;
                                        @endphp
                                        <div class="progress" style="height: 10px;">
                                            <div
                                                class="progress-bar {{ $progress >= 100 ? 'bg-success' : 'bg-primary' }}"
                                                role="progressbar"
                                                style="width: {{ $progress }}%"
                                            ></div>
                                        </div>
                                        <div class="text-center mt-1">
                                            <small class="text-muted">{{ round($progress, 1) }}% Complete</small>
                                        </div>
                                    </div>
                                </div>
                            </div>

                            {{-- Payment History Table --}}
                            <h6 class="text-muted mb-3">
                                <i class="fa-solid fa-list me-1"></i>
                                Payment Records ({{ count($paymentHistory) }})
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
                                                <th>Recorded By</th>
                                                <th>Remarks</th>
                                            </tr>
                                        </thead>
                                        <tbody>
                                            @foreach($paymentHistory as $index => $payment)
                                                <tr>
                                                    <td>{{ $index + 1 }}</td>
                                                    <td>{{ \Carbon\Carbon::parse($payment['payment_date'])->format('d M Y') }}</td>
                                                    <td class="fw-medium text-success">{{ number_format($payment['amount'], 2) }}</td>
                                                    <td>
                                                        <span class="badge bg-success">{{ ucfirst($payment['status']) }}</span>
                                                    </td>
                                                    <td>
                                                        @if($payment['recorded_by'])
                                                            {{ $payment['recorded_by']['first_name'] ?? '' }} {{ $payment['recorded_by']['last_name'] ?? '' }}
                                                        @else
                                                            -
                                                        @endif
                                                    </td>
                                                    <td>
                                                        <small class="text-muted">{{ $payment['remarks'] ?? '-' }}</small>
                                                    </td>
                                                </tr>
                                            @endforeach
                                        </tbody>
                                        <tfoot class="table-light">
                                            <tr>
                                                <td colspan="2" class="text-end fw-bold">Total Paid:</td>
                                                <td class="fw-bold text-success">{{ number_format($total_paid, 2) }}</td>
                                                <td colspan="3"></td>
                                            </tr>
                                        </tfoot>
                                    </table>
                                </div>
                            @else
                                <div class="text-center py-4 text-muted">
                                    <i class="fa-solid fa-receipt fa-3x mb-3 opacity-50"></i>
                                    <p class="mb-0">No payments recorded yet</p>
                                </div>
                            @endif
                        </div>

                        <div class="modal-footer">
                            <button type="button" class="btn btn-secondary" wire:click="$set('showHistoryModal', false)">
                                Close
                            </button>
                            @if(in_array($selectedLoan->status, ['approved', 'disbursed', 'active']) && $remaining_balance > 0)
                                <button
                                    type="button"
                                    class="btn btn-success"
                                    wire:click="$set('showHistoryModal', false)"
                                    x-on:click="$nextTick(() => $wire.openPaymentModal('{{ $selectedLoan->id }}'))"
                                >
                                    <i class="fa-solid fa-plus me-1"></i>
                                    Record Payment
                                </button>
                            @endif
                        </div>
                    </div>
                </div>
            </div>
        @endif
    @endif
</div>
