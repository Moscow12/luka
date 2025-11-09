<div class="modal fade show d-block no-print" tabindex="-1" style="background-color: rgba(0,0,0,0.5);">
    <div class="modal-dialog modal-xl modal-dialog-centered modal-dialog-scrollable">
        <div class="modal-content">
            <div class="modal-header bg-primary text-white no-print">
                <h5 class="modal-title">
                    <i class="fa-solid fa-file-invoice"></i> Salary Slip - {{ \Carbon\Carbon::createFromFormat('Y-m', $payrollDetails->period)->format('F Y') }}
                </h5>
                <button type="button" class="btn-close btn-close-white" wire:click="closeSalarySlip"></button>
            </div>
            <div class="modal-body p-0">
                <!-- Printable Salary Slip -->
                <div id="salary-slip-print" class="p-4">
                    <!-- Header Section -->
                    <div class="text-center mb-4 pb-3 border-bottom border-2 border-primary">
                        <div class="mb-2">
                            <h2 class="fw-bold text-primary mb-0">HR MANAGEMENT SYSTEM</h2>
                            <p class="text-muted mb-0 small">Employee Payroll Department</p>
                        </div>
                        <h3 class="fw-bold mb-1 mt-3">SALARY SLIP</h3>
                        <h5 class="text-muted mb-0">Period: {{ \Carbon\Carbon::createFromFormat('Y-m', $payrollDetails->period)->format('F Y') }}</h5>
                        <p class="small text-muted mb-0 mt-1">
                            Generated on: {{ $payrollDetails->created_at->format('d F Y \a\t H:i') }}
                        </p>
                    </div>

                    <!-- Employee Information -->
                    <div class="card border mb-4">
                        <div class="card-header bg-light">
                            <h6 class="mb-0 fw-bold"><i class="fa-solid fa-user"></i> Employee Information</h6>
                        </div>
                        <div class="card-body">
                            <div class="row">
                                <div class="col-md-6">
                                    <table class="table table-sm table-borderless mb-0">
                                        <tr>
                                            <td class="text-muted" style="width: 150px;"><strong>Employee Name:</strong></td>
                                            <td class="fw-bold">{{ $payrollDetails->employee->first_name }} {{ $payrollDetails->employee->middle_name }} {{ $payrollDetails->employee->last_name }}</td>
                                        </tr>
                                        <tr>
                                            <td class="text-muted"><strong>Employee No:</strong></td>
                                            <td><span class="badge bg-secondary">{{ $payrollDetails->employee->employee_number ?? 'N/A' }}</span></td>
                                        </tr>
                                        <tr>
                                            <td class="text-muted"><strong>Department:</strong></td>
                                            <td>{{ $payrollDetails->employee->department->name ?? 'N/A' }}</td>
                                        </tr>
                                        <tr>
                                            <td class="text-muted"><strong>Designation:</strong></td>
                                            <td>{{ $payrollDetails->employee->designation->name ?? 'N/A' }}</td>
                                        </tr>
                                    </table>
                                </div>
                                <div class="col-md-6">
                                    <table class="table table-sm table-borderless mb-0">
                                        <tr>
                                            <td class="text-muted" style="width: 150px;"><strong>Pay Period:</strong></td>
                                            <td>{{ \Carbon\Carbon::createFromFormat('Y-m', $payrollDetails->period)->format('F Y') }}</td>
                                        </tr>
                                        <tr>
                                            <td class="text-muted"><strong>Payment Status:</strong></td>
                                            <td>
                                                @php
                                                    $statusColors = [
                                                        'pending' => 'warning',
                                                        'processed' => 'info',
                                                        'paid' => 'success',
                                                    ];
                                                    $statusColor = $statusColors[$payrollDetails->status] ?? 'secondary';
                                                @endphp
                                                <span class="badge bg-{{ $statusColor }}">{{ ucfirst($payrollDetails->status) }}</span>
                                            </td>
                                        </tr>
                                        <tr>
                                            <td class="text-muted"><strong>Contract Type:</strong></td>
                                            <td>{{ ucfirst(str_replace('_', ' ', $payrollDetails->contract->contract_type ?? 'N/A')) }}</td>
                                        </tr>
                                        @if($payrollDetails->processed_at)
                                        <tr>
                                            <td class="text-muted"><strong>Processed Date:</strong></td>
                                            <td>{{ \Carbon\Carbon::parse($payrollDetails->processed_at)->format('d M Y') }}</td>
                                        </tr>
                                        @endif
                                    </table>
                                </div>
                            </div>
                        </div>
                    </div>

                    <!-- Quick Summary -->
                    <div class="row mb-4">
                        <div class="col-md-4">
                            <div class="card border-success">
                                <div class="card-body text-center py-3">
                                    <p class="text-muted mb-1 small">Gross Salary</p>
                                    <h4 class="mb-0 fw-bold text-success">{{ format_tzs($payrollDetails->gross_salary) }}</h4>
                                </div>
                            </div>
                        </div>
                        <div class="col-md-4">
                            <div class="card border-danger">
                                <div class="card-body text-center py-3">
                                    <p class="text-muted mb-1 small">Total Deductions</p>
                                    <h4 class="mb-0 fw-bold text-danger">{{ format_tzs($payrollDetails->total_deductions) }}</h4>
                                </div>
                            </div>
                        </div>
                        <div class="col-md-4">
                            <div class="card border-primary border-2">
                                <div class="card-body text-center py-3">
                                    <p class="text-muted mb-1 small">Net Salary</p>
                                    <h4 class="mb-0 fw-bold text-primary">{{ format_tzs($payrollDetails->net_salary) }}</h4>
                                </div>
                            </div>
                        </div>
                    </div>

                    <!-- Salary Breakdown -->
                    <div class="row">
                        <div class="col-md-6">
                            <!-- Earnings Section -->
                            <div class="card border border-success">
                                <div class="card-header bg-success-subtle">
                                    <h6 class="mb-0 fw-bold text-success">
                                        <i class="fa-solid fa-plus-circle"></i> EARNINGS
                                    </h6>
                                </div>
                                <div class="card-body p-0">
                                    <table class="table table-sm mb-0">
                                        <thead class="table-light">
                                            <tr>
                                                <th>Description</th>
                                                <th class="text-end">Amount (TZS)</th>
                                            </tr>
                                        </thead>
                                        <tbody>
                                            <tr>
                                                <td class="fw-semibold">
                                                    <i class="fa-solid fa-money-bill text-success"></i> Basic Salary
                                                </td>
                                                <td class="text-end fw-semibold">{{ format_tzs($payrollDetails->basic_salary) }}</td>
                                            </tr>
                                            @php
                                                $allowances = $payrollDetails->items->where('type', 'allowance');
                                            @endphp
                                            @if($allowances->count() > 0)
                                                <tr class="table-light">
                                                    <td colspan="2" class="py-2">
                                                        <small class="text-muted fw-semibold">
                                                            <i class="fa-solid fa-gift"></i> ALLOWANCES ({{ $allowances->count() }})
                                                        </small>
                                                    </td>
                                                </tr>
                                                @foreach($allowances as $item)
                                                    <tr>
                                                        <td class="ps-4">
                                                            <div class="d-flex flex-column">
                                                                <span>{{ $item->name }}</span>
                                                                @if($item->contractAllowance && $item->contractAllowance->allowance)
                                                                    <small class="text-muted">{{ $item->contractAllowance->allowance->description ?? '' }}</small>
                                                                @endif
                                                            </div>
                                                        </td>
                                                        <td class="text-end">{{ format_tzs($item->amount) }}</td>
                                                    </tr>
                                                @endforeach
                                                <tr class="table-light">
                                                    <td class="fw-semibold">Total Allowances</td>
                                                    <td class="text-end fw-semibold">{{ format_tzs($payrollDetails->total_allowances) }}</td>
                                                </tr>
                                            @else
                                                <tr>
                                                    <td colspan="2" class="text-center text-muted py-2">
                                                        <small>No allowances</small>
                                                    </td>
                                                </tr>
                                            @endif
                                        </tbody>
                                        <tfoot class="bg-success-subtle">
                                            <tr class="fw-bold">
                                                <td class="text-success py-3">
                                                    <i class="fa-solid fa-calculator"></i> GROSS SALARY
                                                </td>
                                                <td class="text-end text-success py-3">{{ format_tzs($payrollDetails->gross_salary) }}</td>
                                            </tr>
                                        </tfoot>
                                    </table>
                                </div>
                            </div>
                        </div>

                        <div class="col-md-6">
                            <!-- Deductions Section -->
                            <div class="card border border-danger">
                                <div class="card-header bg-danger-subtle">
                                    <h6 class="mb-0 fw-bold text-danger">
                                        <i class="fa-solid fa-minus-circle"></i> DEDUCTIONS
                                    </h6>
                                </div>
                                <div class="card-body p-0">
                                    <table class="table table-sm mb-0">
                                        <thead class="table-light">
                                            <tr>
                                                <th>Description</th>
                                                <th class="text-end">Amount (TZS)</th>
                                            </tr>
                                        </thead>
                                        <tbody>
                                            @php
                                                $deductions = $payrollDetails->items->where('type', 'deduction');
                                            @endphp
                                            @if($deductions->count() > 0)
                                                <tr class="table-light">
                                                    <td colspan="2" class="py-2">
                                                        <small class="text-muted fw-semibold">
                                                            <i class="fa-solid fa-receipt"></i> DEDUCTIONS ({{ $deductions->count() }})
                                                        </small>
                                                    </td>
                                                </tr>
                                                @foreach($deductions as $item)
                                                    <tr>
                                                        <td class="ps-4">
                                                            <div class="d-flex flex-column">
                                                                <span>{{ $item->name }}</span>
                                                                @if($item->contractDeduction && $item->contractDeduction->deduction)
                                                                    <small class="text-muted">{{ $item->contractDeduction->deduction->description ?? '' }}</small>
                                                                @endif
                                                            </div>
                                                        </td>
                                                        <td class="text-end">{{ format_tzs($item->amount) }}</td>
                                                    </tr>
                                                @endforeach
                                            @else
                                                <tr>
                                                    <td colspan="2" class="text-center text-muted py-5">
                                                        <i class="fa-solid fa-check-circle text-success fs-3"></i>
                                                        <p class="mb-0 mt-2">No deductions for this period</p>
                                                    </td>
                                                </tr>
                                            @endif
                                        </tbody>
                                        <tfoot class="bg-danger-subtle">
                                            <tr class="fw-bold">
                                                <td class="text-danger py-3">
                                                    <i class="fa-solid fa-calculator"></i> TOTAL DEDUCTIONS
                                                </td>
                                                <td class="text-end text-danger py-3">{{ format_tzs($payrollDetails->total_deductions) }}</td>
                                            </tr>
                                        </tfoot>
                                    </table>
                                </div>
                            </div>
                        </div>
                    </div>

                    <!-- Net Salary Summary -->
                    <div class="row mt-4">
                        <div class="col-12">
                            <div class="card border-primary border-2">
                                <div class="card-body bg-primary-subtle">
                                    <div class="row align-items-center">
                                        <div class="col-md-8">
                                            <h5 class="mb-2 fw-bold text-primary">
                                                <i class="fa-solid fa-wallet"></i> NET SALARY PAYABLE
                                            </h5>
                                            <p class="mb-0 text-muted small">
                                                Gross Salary ({{ format_tzs($payrollDetails->gross_salary) }}) -
                                                Total Deductions ({{ format_tzs($payrollDetails->total_deductions) }})
                                            </p>
                                        </div>
                                        <div class="col-md-4 text-md-end">
                                            <h2 class="mb-0 fw-bold text-primary">
                                                {{ format_tzs($payrollDetails->net_salary) }}
                                            </h2>
                                        </div>
                                    </div>
                                </div>
                            </div>
                        </div>
                    </div>

                    <!-- Footer -->
                    <div class="row mt-4 pt-4 border-top">
                        <div class="col-md-6">
                            <p class="small text-muted mb-1"><strong>Note:</strong></p>
                            <p class="small text-muted">
                                This is a computer-generated salary slip. All amounts are in Tanzanian Shillings (TZS).
                                For any queries, please contact the HR department.
                            </p>
                        </div>
                        <div class="col-md-6 text-md-end">
                            <div class="mt-5 pt-3">
                                <div class="border-top border-dark d-inline-block px-4">
                                    <p class="small text-muted mb-0 mt-2">Authorized Signature</p>
                                </div>
                            </div>
                        </div>
                    </div>

                    <!-- Print Footer -->
                    <div class="d-none d-print-block mt-4 text-center">
                        <p class="small text-muted">
                            Printed on {{ now()->format('d F Y \a\t H:i') }}
                        </p>
                    </div>
                </div>
            </div>
            <div class="modal-footer border-top no-print">
                <button type="button" class="btn btn-secondary" wire:click="closeSalarySlip">
                    <i class="fa-solid fa-times"></i> Close
                </button>
                <button type="button" class="btn btn-primary" onclick="window.print()">
                    <i class="fa-solid fa-print"></i> Print Salary Slip
                </button>
            </div>
        </div>
    </div>
</div>
