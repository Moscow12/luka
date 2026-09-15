<div>
    <div>
        <h5 class="mb-5">My Loan Requests</h5>
    </div>

    {{-- No Employee Account Warning --}}
    @if(!$hasEmployeeAccount)
        <div class="alert alert-danger d-flex align-items-center" role="alert">
            <i class="fa-solid fa-circle-exclamation fa-2x me-3"></i>
            <div>
                <h5 class="alert-heading mb-1">Account Not Linked</h5>
                <p class="mb-0">Your user account is not linked to any employee record. Please contact HR to link your account before you can request a loan.</p>
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
                        <div class="ms-auto">
                            @if($hasExistingLoan)
                                <span class="badge bg-warning text-dark">
                                    <i class="fa-solid fa-clock me-1"></i>
                                    {{ ucfirst(str_replace('_', ' ', $existingLoanStatus)) }} Loan
                                </span>
                            @else
                                <span class="badge bg-success">
                                    <i class="fa-solid fa-check me-1"></i>
                                    Eligible for Loan
                                </span>
                            @endif
                        </div>
                    </div>
                </div>
            </div>

            {{-- Action Row --}}
            <div class="d-flex flex-md-row flex-column gap-3 justify-content-between">
                <div>
                    <h6 class="text-muted mb-0">Your Loan History</h6>
                </div>
                <div>
                    @if($hasExistingLoan)
                        <button class="btn btn-secondary btn-sm d-flex flex-row gap-1 align-items-center" disabled>
                            <i class="fa-solid fa-ban"></i>
                            Cannot Request ({{ ucfirst(str_replace('_', ' ', $existingLoanStatus)) }} Loan Exists)
                        </button>
                    @else
                        <x-forms.button-model name="REQUEST LOAN" />
                    @endif
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
                                                @if($loan->status === 'pending')
                                                    <button
                                                        class="btn btn-sm btn-outline-danger"
                                                        wire:click="cancelRequest('{{ $loan->id }}')"
                                                        wire:confirm="Are you sure you want to cancel this loan request?"
                                                        title="Cancel Request"
                                                    >
                                                        <i class="fa-solid fa-xmark"></i>
                                                    </button>
                                                @else
                                                    <span class="text-muted">-</span>
                                                @endif
                                            </td>
                                        </tr>
                                    @empty
                                        <tr>
                                            <td colspan="9" class="text-center py-5">
                                                <div class="d-flex flex-column align-items-center gap-3">
                                                    <i class="fa-solid fa-file-invoice-dollar fa-3x text-muted"></i>
                                                    <div>
                                                        <p class="text-muted mb-1">You have no loan requests yet</p>
                                                        @if(!$hasExistingLoan)
                                                            <button
                                                                class="btn btn-sm btn-primary"
                                                                wire:click="openModal('create')"
                                                            >
                                                                <i class="fa-solid fa-plus me-1"></i>
                                                                Request your first loan
                                                            </button>
                                                        @endif
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

        {{-- Loan Request Modal --}}
        <div class="modal fade @if($showModal) show d-block @endif" tabindex="-1"
            @if($showModal) style="background: rgba(0,0,0,0.5);" @endif>
            <div class="modal-dialog modal-xl">
                <div class="modal-content">
                    <div class="modal-header">
                        <h5 class="modal-title">
                            <i class="fa-solid fa-money-bill-transfer me-2"></i>
                            Request Loan
                        </h5>
                        <button type="button" class="btn-close" wire:click="$set('showModal', false)"></button>
                    </div>
                    <form wire:submit.prevent="save">
                        <div class="modal-body">
                            <div class="row">
                                {{-- Left Column: Form --}}
                                <div class="col-lg-6">
                                    <h6 class="text-muted mb-3">
                                        <i class="fa-solid fa-user me-1"></i>
                                        Loan Details
                                    </h6>

                                    {{-- Employee Info Display (Read-only) --}}
                                    <div class="card bg-light border-0 mb-3">
                                        <div class="card-body py-3">
                                            <small class="text-muted d-block mb-1">Requesting As</small>
                                            <div class="d-flex align-items-center gap-2">
                                                <div class="avatar avatar-sm bg-primary text-white rounded-circle d-flex align-items-center justify-content-center">
                                                    <i class="fa-solid fa-user fa-xs"></i>
                                                </div>
                                                <div>
                                                    <span class="fw-medium">{{ $currentEmployee->first_name }} {{ $currentEmployee->last_name }}</span>
                                                    <small class="text-muted ms-1">({{ $currentEmployee->employee_no }})</small>
                                                </div>
                                            </div>
                                        </div>
                                    </div>

                                    {{-- Existing Loan Warning --}}
                                    @if($hasExistingLoan)
                                        <div class="alert alert-warning d-flex align-items-center mb-3">
                                            <i class="fa-solid fa-triangle-exclamation me-2"></i>
                                            <div>
                                                <strong>Cannot Request Loan</strong><br>
                                                <small>You have a <strong>{{ $existingLoanStatus }}</strong> loan. Please wait until it is completed.</small>
                                            </div>
                                        </div>
                                    @endif

                                    {{-- Loan Item Selection --}}
                                    <div class="form-floating mb-3">
                                        <select
                                            class="form-select @error('loan_item_id') is-invalid @enderror"
                                            id="loan_item_id"
                                            wire:model.live="loan_item_id"
                                            required
                                            @if($hasExistingLoan) disabled @endif
                                        >
                                            <option value="">Select Loan Type</option>
                                            @foreach($loanItems as $item)
                                                <option value="{{ $item->id }}">
                                                    {{ $item->name }} ({{ $item->interest_rate }}% interest)
                                                </option>
                                            @endforeach
                                        </select>
                                        <label for="loan_item_id">Loan Type</label>
                                        @error('loan_item_id')
                                            <small class="text-danger">{{ $message }}</small>
                                        @enderror
                                    </div>

                                    {{-- Loan Item Info --}}
                                    @if($selectedLoanItem)
                                        <div class="alert alert-info mb-3">
                                            <div class="row text-center">
                                                <div class="col-4">
                                                    <small class="text-muted d-block">Min Amount</small>
                                                    <strong>{{ number_format($min_amount, 2) }}</strong>
                                                </div>
                                                <div class="col-4">
                                                    <small class="text-muted d-block">Max Amount</small>
                                                    <strong>{{ number_format($max_amount, 2) }}</strong>
                                                </div>
                                                <div class="col-4">
                                                    <small class="text-muted d-block">Max Period</small>
                                                    <strong>{{ $max_period }} months</strong>
                                                </div>
                                            </div>
                                        </div>
                                    @endif

                                    {{-- Amount Input --}}
                                    <div class="form-floating mb-3">
                                        <input
                                            type="number"
                                            class="form-control @error('amount') is-invalid @enderror"
                                            id="amount"
                                            wire:model.live.debounce.500ms="amount"
                                            placeholder="0.00"
                                            step="1000"
                                            min="{{ $min_amount }}"
                                            max="{{ $max_amount }}"
                                            @if(!$selectedLoanItem || $hasExistingLoan) disabled @endif
                                        />
                                        <label for="amount">Loan Amount</label>
                                        @error('amount')
                                            <small class="text-danger">{{ $message }}</small>
                                        @enderror
                                    </div>

                                    {{-- Repayment Period --}}
                                    <div class="form-floating mb-3">
                                        <input
                                            type="number"
                                            class="form-control @error('repayment_period_months') is-invalid @enderror"
                                            id="repayment_period_months"
                                            wire:model.live="repayment_period_months"
                                            placeholder="12"
                                            min="1"
                                            max="{{ $max_period }}"
                                            @if(!$selectedLoanItem || $hasExistingLoan) disabled @endif
                                        />
                                        <label for="repayment_period_months">Repayment Period (Months)</label>
                                        @error('repayment_period_months')
                                            <small class="text-danger">{{ $message }}</small>
                                        @enderror
                                    </div>

                                    {{-- Interest Type --}}
                                    <div class="form-floating mb-3">
                                        <select
                                            class="form-select @error('interest_type') is-invalid @enderror"
                                            id="interest_type"
                                            wire:model.live="interest_type"
                                            @if(!$selectedLoanItem || $hasExistingLoan) disabled @endif
                                        >
                                            <option value="reducing_balance">Reducing Balance</option>
                                            <option value="flat">Flat Rate</option>
                                        </select>
                                        <label for="interest_type">Interest Calculation Method</label>
                                        @error('interest_type')
                                            <small class="text-danger">{{ $message }}</small>
                                        @enderror
                                    </div>

                                    {{-- Reason --}}
                                    <div class="form-floating mb-3">
                                        <textarea
                                            class="form-control @error('reason') is-invalid @enderror"
                                            id="reason"
                                            wire:model="reason"
                                            placeholder="Reason for loan"
                                            style="height: 100px"
                                            @if($hasExistingLoan) disabled @endif
                                        ></textarea>
                                        <label for="reason">Reason for Loan Request</label>
                                        @error('reason')
                                            <small class="text-danger">{{ $message }}</small>
                                        @enderror
                                    </div>
                                </div>

                                {{-- Right Column: Calculation Summary --}}
                                <div class="col-lg-6">
                                    <h6 class="text-muted mb-3">
                                        <i class="fa-solid fa-calculator me-1"></i>
                                        Loan Calculator
                                    </h6>

                                    @if($amount && $repayment_period_months)
                                        {{-- Summary Cards --}}
                                        <div class="row g-3 mb-4">
                                            <div class="col-6">
                                                <div class="card bg-primary text-white">
                                                    <div class="card-body text-center py-3">
                                                        <small class="d-block opacity-75">Principal Amount</small>
                                                        <h5 class="mb-0">{{ number_format($amount, 2) }}</h5>
                                                    </div>
                                                </div>
                                            </div>
                                            <div class="col-6">
                                                <div class="card bg-warning text-dark">
                                                    <div class="card-body text-center py-3">
                                                        <small class="d-block opacity-75">Interest Rate</small>
                                                        <h5 class="mb-0">{{ $interest_rate }}% p.a.</h5>
                                                    </div>
                                                </div>
                                            </div>
                                            <div class="col-6">
                                                <div class="card bg-info text-white">
                                                    <div class="card-body text-center py-3">
                                                        <small class="d-block opacity-75">Total Interest</small>
                                                        <h5 class="mb-0">{{ number_format($interest_amount, 2) }}</h5>
                                                    </div>
                                                </div>
                                            </div>
                                            <div class="col-6">
                                                <div class="card bg-success text-white">
                                                    <div class="card-body text-center py-3">
                                                        <small class="d-block opacity-75">Total Repayment</small>
                                                        <h5 class="mb-0">{{ number_format($total_repayment, 2) }}</h5>
                                                    </div>
                                                </div>
                                            </div>
                                        </div>

                                        {{-- Monthly Installment Highlight --}}
                                        <div class="card border-success mb-4">
                                            <div class="card-body text-center">
                                                <small class="text-muted d-block">Monthly Installment</small>
                                                <h3 class="text-success mb-0">{{ number_format($installment_amount, 2) }}</h3>
                                                <small class="text-muted">for {{ $repayment_period_months }} months</small>
                                            </div>
                                        </div>

                                        {{-- Payment Schedule Toggle --}}
                                        <button
                                            type="button"
                                            class="btn btn-outline-primary w-100 mb-3"
                                            wire:click="toggleSchedule"
                                        >
                                            <i class="fa-solid fa-calendar-days me-1"></i>
                                            {{ $showSchedule ? 'Hide' : 'View' }} Payment Schedule
                                        </button>

                                        {{-- Payment Schedule Table --}}
                                        @if($showSchedule && count($payment_schedule) > 0)
                                            <div class="table-responsive" style="max-height: 300px; overflow-y: auto;">
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
                                    @else
                                        <div class="text-center py-5 text-muted">
                                            <i class="fa-solid fa-chart-line fa-3x mb-3 opacity-50"></i>
                                            <p class="mb-0">Select a loan type and enter amount to see calculations</p>
                                        </div>
                                    @endif
                                </div>
                            </div>
                        </div>

                        <div class="modal-footer">
                            <button type="button" class="btn btn-secondary" wire:click="$set('showModal', false)">
                                Cancel
                            </button>
                            <button
                                type="submit"
                                class="btn btn-primary"
                                @if($hasExistingLoan || !$amount) disabled @endif
                            >
                                <i class="fa-solid fa-paper-plane me-1"></i>
                                Submit Request
                            </button>
                        </div>
                    </form>
                </div>
            </div>
        </div>
    @endif
</div>
