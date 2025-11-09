<div class="custom-container">

    <x-pages.breadcrumn title="PAYROLL GENERATION" :breadcrumbs="[
        ['label' => 'Home', 'url' => route('dashboard')],
        ['label' => 'Accounting', 'url' => '#'],
        ['label' => 'Payroll Generation', 'url' => '#'],
    ]">
        @if(count($selectedEmployees) > 0)
            <button wire:click="generatePayrollForSelected" class="btn btn-success d-md-flex align-items-center gap-2">
                <i class="fa-solid fa-cash-register"></i> GENERATE PAYROLL ({{ count($selectedEmployees) }})
            </button>
        @endif
    </x-pages.breadcrumn>

    <!-- Payroll Generation Card -->
    <div class="row">
        <div class="col-12">
            <div class="card card-lg">
                <!-- Card Header with Search and Filters -->
                <div class="card-header border-bottom">
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
                                    placeholder="Search by name, number, email..." />
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
                                <!-- Filter Toggle Button -->
                                <button wire:click="toggleFilters" type="button"
                                    class="btn {{ $showFilters ? 'btn-primary' : 'btn-white' }}">
                                    <i class="fa-solid fa-filter me-1"></i>
                                    {{ $showFilters ? 'Hide' : 'Show' }} Filters
                                    @if($department || $contractType || $contractStatus !== 'active' || $employeeStatus !== 'Active')
                                        <span class="badge bg-danger ms-1">
                                            {{ collect([$department, $contractType, $contractStatus !== 'active' ? 1 : null, $employeeStatus !== 'Active' ? 1 : null])->filter()->count() }}
                                        </span>
                                    @endif
                                </button>

                                <!-- Reset Filters -->
                                @if($search || $department || $contractType || $contractStatus !== 'active' || $employeeStatus !== 'Active')
                                    <button wire:click="resetFilters" type="button" class="btn btn-outline-secondary">
                                        <i class="fa-solid fa-rotate-left me-1"></i> Reset
                                    </button>
                                @endif

                                <!-- Select All -->
                                <button wire:click="$toggle('selectAll')" type="button"
                                    class="btn {{ $selectAll ? 'btn-info' : 'btn-outline-info' }}">
                                    <i class="fa-solid fa-check-double me-1"></i>
                                    {{ $selectAll ? 'Deselect' : 'Select' }} All
                                </button>
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
                                <label class="form-label small text-muted mb-1">Contract Type</label>
                                <select wire:model.live="contractType" class="form-select">
                                    <option value="">All Types</option>
                                    @foreach($contractTypes as $type)
                                        <option value="{{ $type }}">{{ ucfirst(str_replace('_', ' ', $type)) }}</option>
                                    @endforeach
                                </select>
                            </div>

                            <div class="col-12 col-md-6 col-lg-3">
                                <label class="form-label small text-muted mb-1">Contract Status</label>
                                <select wire:model.live="contractStatus" class="form-select">
                                    <option value="">All Contracts</option>
                                    <option value="active">Has Active Contract</option>
                                    <option value="no_active">No Active Contract</option>
                                </select>
                            </div>

                            <div class="col-12 col-md-6 col-lg-3">
                                <label class="form-label small text-muted mb-1">Employee Status</label>
                                <select wire:model.live="employeeStatus" class="form-select">
                                    <option value="">All Status</option>
                                    <option value="Active">Active</option>
                                    <option value="Suspended">Suspended</option>
                                    <option value="Terminated">Terminated</option>
                                    <option value="Retired">Retired</option>
                                </select>
                            </div>
                        </div>
                    @endif
                </div>

                <!-- Table Section -->
                <div class="table-responsive" style="min-height: 400px;">
                    <table class="table table-hover table-centered align-middle mb-0">
                        <thead class="table-light">
                            <tr>
                                <th class="text-center" style="width: 50px;">
                                    <input type="checkbox" wire:model.live="selectAll" class="form-check-input">
                                </th>
                                <th class="text-center" style="width: 50px;">#</th>
                                <th>Employee</th>
                                <th>Department</th>
                                <th>Contract Type</th>
                                <th>Base Salary</th>
                                <th>Contract Details</th>
                                <th>Status</th>
                                <th class="text-end" style="width: 120px;">Action</th>
                            </tr>
                        </thead>
                        <tbody>
                            @forelse($employees as $index => $employee)
                                <tr wire:key="employee-{{ $employee->id }}">
                                    <td class="text-center">
                                        <input type="checkbox"
                                            wire:model.live="selectedEmployees"
                                            value="{{ $employee->id }}"
                                            class="form-check-input"
                                            @if(!$employee->activeContract) disabled @endif>
                                    </td>
                                    <td class="text-center text-muted">
                                        {{ $employees->firstItem() + $index }}
                                    </td>
                                    <td>
                                        <div class="d-flex flex-column">
                                            <span class="fw-semibold text-dark">
                                                {{ $employee->first_name }} {{ $employee->last_name }}
                                            </span>
                                            <small class="text-muted">
                                                <i class="fa-solid fa-hashtag"></i> {{ $employee->employee_number ?? 'N/A' }}
                                            </small>
                                        </div>
                                    </td>
                                    <td>
                                        <span class="badge bg-light text-dark">
                                            {{ $employee->department->name ?? 'N/A' }}
                                        </span>
                                    </td>
                                    <td>
                                        @if($employee->activeContract)
                                            <span class="badge bg-info text-white">
                                                {{ ucfirst(str_replace('_', ' ', $employee->activeContract->contract_type)) }}
                                            </span>
                                            <small class="text-muted d-block">
                                                <i class="fa-solid fa-calendar"></i>
                                                {{ \Carbon\Carbon::parse($employee->activeContract->start_date)->format('M d, Y') }}
                                            </small>
                                        @else
                                            <span class="badge bg-warning text-dark">No Active Contract</span>
                                        @endif
                                    </td>
                                    <td>
                                        @if($employee->activeContract)
                                            <strong class="text-success">
                                                {{ format_tzs($employee->activeContract->base_salary) }}
                                            </strong>
                                        @else
                                            <span class="text-muted">-</span>
                                        @endif
                                    </td>
                                    <td>
                                        @if($employee->activeContract)
                                            @php
                                                $allowancesCount = \App\Models\ContractAllowance::where('contract_id', $employee->activeContract->id)
                                                    ->where('is_active', true)->count();
                                                $deductionsCount = \App\Models\ContractDeduction::where('contract_id', $employee->activeContract->id)
                                                    ->where('is_active', true)->count();
                                            @endphp
                                            <div class="d-flex gap-2">
                                                <span class="badge bg-success-subtle text-success">
                                                    <i class="fa-solid fa-plus"></i> {{ $allowancesCount }}
                                                </span>
                                                <span class="badge bg-danger-subtle text-danger">
                                                    <i class="fa-solid fa-minus"></i> {{ $deductionsCount }}
                                                </span>
                                            </div>
                                        @else
                                            <span class="text-muted">-</span>
                                        @endif
                                    </td>
                                    <td>
                                        @php
                                            $statusColors = [
                                                'Active' => 'success',
                                                'Suspended' => 'warning',
                                                'Terminated' => 'danger',
                                                'Retired' => 'secondary',
                                            ];
                                            $statusColor = $statusColors[$employee->status] ?? 'secondary';
                                        @endphp
                                        <span class="badge bg-{{ $statusColor }}">{{ $employee->status }}</span>
                                    </td>
                                    <td>
                                        <div class="d-flex gap-1 justify-content-end">
                                            @if($employee->activeContract)
                                                <button
                                                    wire:click="viewContractDetails('{{ $employee->activeContract->id }}')"
                                                    class="btn btn-sm btn-ghost-primary rounded-circle"
                                                    title="View Contract Details">
                                                    <i class="fa-solid fa-eye"></i>
                                                </button>
                                            @else
                                                <button class="btn btn-sm btn-ghost-secondary rounded-circle" disabled title="No Contract">
                                                    <i class="fa-solid fa-ban"></i>
                                                </button>
                                            @endif
                                        </div>
                                    </td>
                                </tr>
                            @empty
                                <tr>
                                    <td colspan="9" class="text-center py-5">
                                        <div class="d-flex flex-column align-items-center justify-content-center">
                                            <i class="fa-solid fa-users text-muted mb-3" style="font-size: 48px;"></i>
                                            <h5 class="text-muted">No Employees Found</h5>
                                            <p class="text-muted">
                                                @if($search || $department || $contractType || $contractStatus !== 'active' || $employeeStatus !== 'Active')
                                                    Try adjusting your filters or search query
                                                @else
                                                    No employees with active contracts for selected period
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
                <div class="card-footer border-top">
                    <div class="d-flex flex-column flex-md-row justify-content-between align-items-center gap-3">
                        <!-- Results Info -->
                        <div class="text-muted">
                            @if($employees->total() > 0)
                                Showing {{ $employees->firstItem() }} to {{ $employees->lastItem() }} of
                                {{ $employees->total() }} employees
                                @if(count($selectedEmployees) > 0)
                                    <span class="badge bg-primary ms-2">{{ count($selectedEmployees) }} selected</span>
                                @endif
                            @else
                                No employees found
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
                                {{ $employees->links() }}
                            </div>
                        </div>
                    </div>
                </div>
            </div>
        </div>
    </div>

    {{-- Contract Details Modal --}}
    @if($viewingContractId)
        <div class="modal fade show d-block" tabindex="-1" style="background-color: rgba(0,0,0,0.5);">
            <div class="modal-dialog modal-lg modal-dialog-centered modal-dialog-scrollable">
                <div class="modal-content">
                    <div class="modal-header bg-primary text-white">
                        <h5 class="modal-title">
                            <i class="fa-solid fa-file-contract"></i> Contract Payroll Details
                        </h5>
                        <button type="button" class="btn-close btn-close-white" wire:click="closeContractDetails"></button>
                    </div>
                    <div class="modal-body">
                        {{-- Contract Allowances --}}
                        <div class="mb-4">
                            <h6 class="fw-bold text-success d-flex align-items-center gap-2">
                                <i class="fa-solid fa-circle-plus"></i> Allowances
                            </h6>
                            @if($contractAllowances->count() > 0)
                                <div class="table-responsive">
                                    <table class="table table-sm table-bordered">
                                        <thead class="table-light">
                                            <tr>
                                                <th>Allowance Name</th>
                                                <th class="text-end">Amount</th>
                                                <th class="text-center">Status</th>
                                            </tr>
                                        </thead>
                                        <tbody>
                                            @foreach($contractAllowances as $allowance)
                                                <tr>
                                                    <td>{{ $allowance->allowance->name ?? 'N/A' }}</td>
                                                    <td class="text-end fw-bold text-success">
                                                        {{ format_tzs($allowance->amount_override ?? $allowance->allowance->amount ?? 0) }}
                                                    </td>
                                                    <td class="text-center">
                                                        <span class="badge bg-success">Active</span>
                                                    </td>
                                                </tr>
                                            @endforeach
                                        </tbody>
                                        <tfoot class="table-light">
                                            <tr>
                                                <th>Total Allowances</th>
                                                <th class="text-end text-success">
                                                    {{ format_tzs($contractAllowances->sum(fn($a) => $a->amount_override ?? $a->allowance->amount ?? 0)) }}
                                                </th>
                                                <th></th>
                                            </tr>
                                        </tfoot>
                                    </table>
                                </div>
                            @else
                                <div class="alert alert-light text-center">
                                    <i class="fa-solid fa-circle-info"></i>
                                    No allowances assigned to this contract
                                </div>
                            @endif
                        </div>

                        {{-- Contract Deductions --}}
                        <div>
                            <h6 class="fw-bold text-danger d-flex align-items-center gap-2">
                                <i class="fa-solid fa-circle-minus"></i> Deductions
                            </h6>
                            @if($contractDeductions->count() > 0)
                                <div class="table-responsive">
                                    <table class="table table-sm table-bordered">
                                        <thead class="table-light">
                                            <tr>
                                                <th>Deduction Name</th>
                                                <th class="text-end">Amount</th>
                                                <th class="text-center">Status</th>
                                            </tr>
                                        </thead>
                                        <tbody>
                                            @foreach($contractDeductions as $deduction)
                                                <tr>
                                                    <td>{{ $deduction->deduction->name ?? 'N/A' }}</td>
                                                    <td class="text-end fw-bold text-danger">
                                                        {{ format_tzs($deduction->amount_override ?? $deduction->deduction->amount ?? 0) }}
                                                    </td>
                                                    <td class="text-center">
                                                        <span class="badge bg-success">Active</span>
                                                    </td>
                                                </tr>
                                            @endforeach
                                        </tbody>
                                        <tfoot class="table-light">
                                            <tr>
                                                <th>Total Deductions</th>
                                                <th class="text-end text-danger">
                                                    {{ format_tzs($contractDeductions->sum(fn($d) => $d->amount_override ?? $d->deduction->amount ?? 0)) }}
                                                </th>
                                                <th></th>
                                            </tr>
                                        </tfoot>
                                    </table>
                                </div>
                            @else
                                <div class="alert alert-light text-center">
                                    <i class="fa-solid fa-circle-info"></i>
                                    No deductions assigned to this contract
                                </div>
                            @endif
                        </div>

                        <div class="alert alert-info mt-3">
                            <i class="fa-solid fa-info-circle"></i>
                            <strong>Note:</strong> PAYE tax will be automatically calculated and added to deductions during payroll generation based on Tanzania tax brackets.
                        </div>
                    </div>
                    <div class="modal-footer">
                        <button type="button" class="btn btn-secondary" wire:click="closeContractDetails">
                            <i class="fa-solid fa-times"></i> Close
                        </button>
                    </div>
                </div>
            </div>
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
    </style>
</div>
