<div class="custom-container">

    <x-pages.breadcrumn title="PAYMENT REPORTS" :breadcrumbs="[
        ['label' => 'Home', 'url' => route('dashboard')],
        ['label' => 'Accounting', 'url' => '#'],
        ['label' => 'Payment Reports', 'url' => '#'],
    ]">
    </x-pages.breadcrumn>

    <!-- Summary Cards -->
    <div class="row g-3 mb-4">
        <div class="col-12 col-sm-6 col-xl-3">
            <div class="card border-0 shadow-sm">
                <div class="card-body">
                    <div class="d-flex align-items-center">
                        <div class="flex-shrink-0">
                            <div class="avatar avatar-lg bg-primary-subtle text-primary rounded">
                                <i class="fa-solid fa-users fs-4"></i>
                            </div>
                        </div>
                        <div class="flex-grow-1 ms-3">
                            <p class="text-muted mb-1 small">Total Payrolls</p>
                            <h4 class="mb-0 fw-bold">{{ number_format($summary['total_payrolls']) }}</h4>
                        </div>
                    </div>
                </div>
            </div>
        </div>

        <div class="col-12 col-sm-6 col-xl-3">
            <div class="card border-0 shadow-sm">
                <div class="card-body">
                    <div class="d-flex align-items-center">
                        <div class="flex-shrink-0">
                            <div class="avatar avatar-lg bg-success-subtle text-success rounded">
                                <i class="fa-solid fa-money-bill-wave fs-4"></i>
                            </div>
                        </div>
                        <div class="flex-grow-1 ms-3">
                            <p class="text-muted mb-1 small">Total Gross Salary</p>
                            <h4 class="mb-0 fw-bold text-success">{{ format_tzs($summary['total_gross']) }}</h4>
                        </div>
                    </div>
                </div>
            </div>
        </div>

        <div class="col-12 col-sm-6 col-xl-3">
            <div class="card border-0 shadow-sm">
                <div class="card-body">
                    <div class="d-flex align-items-center">
                        <div class="flex-shrink-0">
                            <div class="avatar avatar-lg bg-danger-subtle text-danger rounded">
                                <i class="fa-solid fa-minus-circle fs-4"></i>
                            </div>
                        </div>
                        <div class="flex-grow-1 ms-3">
                            <p class="text-muted mb-1 small">Total Deductions</p>
                            <h4 class="mb-0 fw-bold text-danger">{{ format_tzs($summary['total_deductions']) }}</h4>
                        </div>
                    </div>
                </div>
            </div>
        </div>

        <div class="col-12 col-sm-6 col-xl-3">
            <div class="card border-0 shadow-sm">
                <div class="card-body">
                    <div class="d-flex align-items-center">
                        <div class="flex-shrink-0">
                            <div class="avatar avatar-lg bg-info-subtle text-info rounded">
                                <i class="fa-solid fa-wallet fs-4"></i>
                            </div>
                        </div>
                        <div class="flex-grow-1 ms-3">
                            <p class="text-muted mb-1 small">Total Net Salary</p>
                            <h4 class="mb-0 fw-bold text-info">{{ format_tzs($summary['total_net']) }}</h4>
                        </div>
                    </div>
                </div>
            </div>
        </div>
    </div>

    <!-- Main Card -->
    <div class="row">
        <div class="col-12">
            <div class="card card-lg border-0 shadow-sm">
                <!-- Card Header with Filters -->
                <div class="card-header border-bottom bg-white">
                    <div class="row g-3 align-items-center">
                        <!-- Period & Search -->
                        <div class="col-12 col-md-4">
                            <div class="input-group">
                                <span class="input-group-text bg-white">
                                    <i class="fa-solid fa-calendar-days"></i>
                                </span>
                                <input type="month" wire:model.live="period" class="form-control" />
                            </div>
                        </div>

                        <div class="col-12 col-md-4">
                            <div class="input-group">
                                <span class="input-group-text bg-white">
                                    <i class="fa-solid fa-magnifying-glass"></i>
                                </span>
                                <input type="search" wire:model.live.debounce.300ms="search" class="form-control"
                                    placeholder="Search employee..." />
                                @if($search)
                                    <button wire:click="$set('search', '')" class="btn btn-outline-secondary" type="button">
                                        <i class="fa-solid fa-times"></i>
                                    </button>
                                @endif
                            </div>
                        </div>

                        <!-- Actions -->
                        <div class="col-12 col-md-4">
                            <div class="d-flex flex-wrap gap-2 justify-content-md-end">
                                <!-- Export Dropdown -->
                                <div class="btn-group" role="group">
                                    <button type="button" class="btn btn-success dropdown-toggle" data-bs-toggle="dropdown" aria-expanded="false">
                                        <i class="fa-solid fa-download me-1"></i> Export
                                    </button>
                                    <ul class="dropdown-menu">
                                        <li>
                                            <a class="dropdown-item" href="#" wire:click.prevent="exportExcel">
                                                <i class="fa-solid fa-file-excel text-success me-2"></i> Excel (.xlsx)
                                            </a>
                                        </li>
                                        <li>
                                            <a class="dropdown-item" href="#" wire:click.prevent="exportCSV">
                                                <i class="fa-solid fa-file-csv text-info me-2"></i> CSV (.csv)
                                            </a>
                                        </li>
                                        <li>
                                            <a class="dropdown-item" href="#" wire:click.prevent="exportPDF">
                                                <i class="fa-solid fa-file-pdf text-danger me-2"></i> PDF (.pdf)
                                            </a>
                                        </li>
                                    </ul>
                                </div>

                                <!-- Filter Toggle Button -->
                                <button wire:click="toggleFilters" type="button"
                                    class="btn {{ $showFilters ? 'btn-primary' : 'btn-white' }}">
                                    <i class="fa-solid fa-filter me-1"></i>
                                    {{ $showFilters ? 'Hide' : 'Show' }} Filters
                                    @if($department || $status || $allowance || $salaryMin || $salaryMax)
                                        <span class="badge bg-danger ms-1">
                                            {{ collect([$department, $status, $allowance, $salaryMin, $salaryMax])->filter()->count() }}
                                        </span>
                                    @endif
                                </button>

                                <!-- Reset Filters -->
                                @if($search || $department || $status || $allowance || $salaryMin || $salaryMax)
                                    <button wire:click="resetFilters" type="button" class="btn btn-outline-secondary">
                                        <i class="fa-solid fa-rotate-left me-1"></i> Reset
                                    </button>
                                @endif
                            </div>
                        </div>
                    </div>

                    <!-- Advanced Filters (Collapsible) -->
                    @if($showFilters)
                        <div class="row g-3 mt-2 pt-3 border-top">
                            <div class="col-12 col-md-6 col-lg-3">
                                <label class="form-label small text-muted mb-1">Department</label>
                                <select wire:model.live="department" class="form-select">
                                    <option value="">All Departments</option>
                                    @foreach($departments as $dept)
                                        <option value="{{ $dept->id }}">{{ $dept->name }}</option>
                                    @endforeach
                                </select>
                            </div>

                            <div class="col-12 col-md-6 col-lg-3">
                                <label class="form-label small text-muted mb-1">Allowance Type</label>
                                <select wire:model.live="allowance" class="form-select">
                                    <option value="">All Allowances</option>
                                    @foreach($allowances as $allow)
                                        <option value="{{ $allow->id }}">{{ $allow->name }}</option>
                                    @endforeach
                                </select>
                            </div>

                            <div class="col-12 col-md-6 col-lg-3">
                                <label class="form-label small text-muted mb-1">Status</label>
                                <select wire:model.live="status" class="form-select">
                                    <option value="">All Status</option>
                                    <option value="pending">Pending</option>
                                    <option value="processed">Processed</option>
                                    <option value="paid">Paid</option>
                                </select>
                            </div>

                            <div class="col-12 col-md-6 col-lg-3">
                                <label class="form-label small text-muted mb-1">Salary Range</label>
                                <div class="input-group">
                                    <input type="number" wire:model.live.debounce.500ms="salaryMin" class="form-control"
                                        placeholder="Min" step="0.01" min="0">
                                    <span class="input-group-text">-</span>
                                    <input type="number" wire:model.live.debounce.500ms="salaryMax" class="form-control"
                                        placeholder="Max" step="0.01" min="0">
                                </div>
                            </div>
                        </div>
                    @endif
                </div>

                <!-- Table Section -->
                <div class="table-responsive" style="min-height: 400px;">
                    <table class="table table-hover table-centered align-middle mb-0">
                        <thead class="table-light">
                            <tr>
                                <th class="text-center" style="width: 50px;">#</th>
                                <th>Employee</th>
                                <th>Department</th>
                                <th>Period</th>
                                <th class="text-end">Basic Salary</th>
                                <th class="text-end">Gross Salary</th>
                                <th class="text-end">Deductions</th>
                                <th class="text-end">Net Salary</th>
                                <th>Status</th>
                                <th class="text-center" style="width: 120px;">Action</th>
                            </tr>
                        </thead>
                        <tbody>
                            @forelse($payrolls as $index => $payroll)
                                <tr wire:key="payroll-{{ $payroll->id }}">
                                    <td class="text-center text-muted">
                                        {{ $payrolls->firstItem() + $index }}
                                    </td>
                                    <td>
                                        <div class="d-flex flex-column">
                                            <span class="fw-semibold text-dark">
                                                {{ $payroll->employee->first_name }} {{ $payroll->employee->last_name }}
                                            </span>
                                            <small class="text-muted">
                                                <i class="fa-solid fa-hashtag"></i> {{ $payroll->employee->employee_number ?? 'N/A' }}
                                            </small>
                                        </div>
                                    </td>
                                    <td>
                                        <span class="badge bg-light text-dark">
                                            {{ $payroll->employee->department->name ?? 'N/A' }}
                                        </span>
                                    </td>
                                    <td>
                                        <span class="text-muted">
                                            {{ \Carbon\Carbon::createFromFormat('Y-m', $payroll->period)->format('F Y') }}
                                        </span>
                                    </td>
                                    <td class="text-end">
                                        <span class="text-dark">{{ format_tzs($payroll->basic_salary) }}</span>
                                    </td>
                                    <td class="text-end">
                                        <span class="text-success fw-semibold">{{ format_tzs($payroll->gross_salary) }}</span>
                                    </td>
                                    <td class="text-end">
                                        <span class="text-danger fw-semibold">{{ format_tzs($payroll->total_deductions) }}</span>
                                    </td>
                                    <td class="text-end">
                                        <span class="text-info fw-bold">{{ format_tzs($payroll->net_salary) }}</span>
                                    </td>
                                    <td>
                                        @php
                                            $statusColors = [
                                                'pending' => 'warning',
                                                'processed' => 'info',
                                                'paid' => 'success',
                                            ];
                                            $statusColor = $statusColors[$payroll->status] ?? 'secondary';
                                        @endphp
                                        <span class="badge bg-{{ $statusColor }}">{{ ucfirst($payroll->status) }}</span>
                                    </td>
                                    <td>
                                        <div class="d-flex gap-1 justify-content-center">
                                            <button
                                                wire:click="viewSalarySlip('{{ $payroll->id }}')"
                                                class="btn btn-sm btn-primary"
                                                title="View Salary Slip">
                                                <i class="fa-solid fa-file-invoice"></i> View Slip
                                            </button>
                                        </div>
                                    </td>
                                </tr>
                            @empty
                                <tr>
                                    <td colspan="10" class="text-center py-5">
                                        <div class="d-flex flex-column align-items-center justify-content-center">
                                            <i class="fa-solid fa-inbox text-muted mb-3" style="font-size: 48px;"></i>
                                            <h5 class="text-muted">No Payment Records Found</h5>
                                            <p class="text-muted">
                                                @if($search || $department || $status || $allowance || $salaryMin || $salaryMax)
                                                    Try adjusting your filters or search query
                                                @else
                                                    No payment records for the selected period
                                                @endif
                                            </p>
                                        </div>
                                    </td>
                                </tr>
                            @endforelse
                        </tbody>
                    </table>
                </div>

                <!-- Card Footer with Pagination -->
                <div class="card-footer border-top bg-white">
                    <div class="d-flex flex-column flex-md-row justify-content-between align-items-center gap-3">
                        <!-- Results Info -->
                        <div class="text-muted">
                            @if($payrolls->total() > 0)
                                Showing {{ $payrolls->firstItem() }} to {{ $payrolls->lastItem() }} of
                                {{ $payrolls->total() }} payment records
                            @else
                                No payment records found
                            @endif
                        </div>

                        <!-- Pagination and Per Page -->
                        <div class="d-flex flex-column flex-sm-row align-items-center gap-3">
                            <!-- Per Page Selector -->
                            <div class="d-flex align-items-center gap-2">
                                <label class="form-label mb-0 text-nowrap small">Rows per page:</label>
                                <select wire:model.live="perPage" class="form-select form-select-sm" style="width: auto;">
                                    <option value="10">10</option>
                                    <option value="20">20</option>
                                    <option value="50">50</option>
                                    <option value="100">100</option>
                                </select>
                            </div>

                            <!-- Pagination Links -->
                            <div>
                                {{ $payrolls->links() }}
                            </div>
                        </div>
                    </div>
                </div>
            </div>
        </div>
    </div>

    {{-- Salary Slip Modal --}}
    @if($viewingPayrollId && $payrollDetails)
        <div wire:key="salary-slip-{{ $viewingPayrollId }}">
            @include('livewire.acc.payroll.partials.salary-slip-modal')
        </div>
    @endif

    <!-- Loading Indicator -->
    <div wire:loading class="position-fixed top-50 start-50 translate-middle" style="z-index: 9999;">
        <div class="spinner-border text-primary" role="status">
            <span class="visually-hidden">Loading...</span>
        </div>
    </div>

    <style>
        .modal.show {
            display: block;
        }

        .avatar {
            width: 48px;
            height: 48px;
            display: flex;
            align-items: center;
            justify-content: center;
        }

        @media print {
            /* Hide everything except the salary slip */
            body > *:not(.modal-backdrop-custom) {
                display: none !important;
            }

            /* Hide specific modal elements */
            .modal-backdrop-custom {
                background: white !important;
                position: static !important;
                z-index: auto !important;
            }

            .modal-header-print-hide,
            .modal-footer-print-hide,
            .btn,
            button,
            .no-print {
                display: none !important;
            }

            /* Reset modal structure for print */
            .modal-dialog {
                max-width: 100% !important;
                margin: 0 !important;
                position: static !important;
            }

            .modal-content {
                border: none !important;
                box-shadow: none !important;
                position: static !important;
            }

            .modal-body {
                padding: 0 !important;
            }

            /* Ensure salary slip content is visible and properly positioned */
            #salary-slip-print {
                display: block !important;
                visibility: visible !important;
                position: static !important;
                width: 100% !important;
                padding: 20px !important;
                background: white !important;
                margin: 0 !important;
            }

            /* Make sure all content inside salary slip is visible */
            #salary-slip-print * {
                display: revert !important;
                visibility: visible !important;
            }

            /* Ensure proper print layout */
            @page {
                size: A4;
                margin: 10mm;
            }

            /* Print-specific styling */
            table {
                page-break-inside: avoid;
                border-collapse: collapse !important;
                width: 100% !important;
            }

            .card {
                page-break-inside: avoid;
                border: 1px solid #dee2e6 !important;
            }

            /* Ensure text is visible */
            h1, h2, h3, h4, h5, h6, p, span, td, th, div, small {
                color: black !important;
                opacity: 1 !important;
            }

            /* Ensure colors print correctly */
            .bg-primary,
            .text-primary,
            .border-primary {
                -webkit-print-color-adjust: exact;
                print-color-adjust: exact;
            }

            /* Ensure Bootstrap colors render */
            .bg-success, .bg-danger, .bg-info, .bg-light,
            .text-success, .text-danger, .text-info, .text-muted,
            .border-success, .border-danger, .border-primary {
                -webkit-print-color-adjust: exact;
                print-color-adjust: exact;
            }
        }
    </style>
</div>
