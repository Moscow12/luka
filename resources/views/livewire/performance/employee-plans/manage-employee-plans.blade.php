<div class="custom-container">

    <x-pages.breadcrumn title="EMPLOYEE PLANS" :breadcrumbs="[
        ['label' => 'Home', 'url' => route('dashboard')],
        ['label' => 'Performance Management', 'url' => '#'],
        ['label' => 'Employee Plans', 'url' => route('performance.employee.plans')],
    ]">
        <button class='btn btn-primary d-md-flex align-items-center gap-2' data-bs-toggle="modal" data-bs-target="#createEmployeePlanModal">
            <i class="fa-solid fa-plus"></i> ASSIGN PLAN TO EMPLOYEE
        </button>
    </x-pages.breadcrumn>

    <!-- Statistics Cards -->
    <div class="row g-3 mb-4">
        <div class="col-12 col-sm-6 col-xl-3">
            <div class="card card-lg border-0 shadow-sm">
                <div class="card-body">
                    <div class="d-flex align-items-center justify-content-between">
                        <div>
                            <p class="text-muted mb-1 small text-uppercase">Total Employee Plans</p>
                            <h3 class="mb-0 fw-bold">{{ $totalEmployeePlans ?? 0 }}</h3>
                        </div>
                        <div class="icon-shape icon-lg bg-primary-subtle text-primary rounded-3">
                            <i class="fa-solid fa-users fa-lg"></i>
                        </div>
                    </div>
                </div>
            </div>
        </div>

        <div class="col-12 col-sm-6 col-xl-3">
            <div class="card card-lg border-0 shadow-sm">
                <div class="card-body">
                    <div class="d-flex align-items-center justify-content-between">
                        <div>
                            <p class="text-muted mb-1 small text-uppercase">Active Plans</p>
                            <h3 class="mb-0 fw-bold text-primary">{{ $activeEmployeePlans ?? 0 }}</h3>
                        </div>
                        <div class="icon-shape icon-lg bg-primary-subtle text-primary rounded-3">
                            <i class="fa-solid fa-circle-play fa-lg"></i>
                        </div>
                    </div>
                </div>
            </div>
        </div>

        <div class="col-12 col-sm-6 col-xl-3">
            <div class="card card-lg border-0 shadow-sm">
                <div class="card-body">
                    <div class="d-flex align-items-center justify-content-between">
                        <div>
                            <p class="text-muted mb-1 small text-uppercase">Under Review</p>
                            <h3 class="mb-0 fw-bold text-warning">{{ $underReviewPlans ?? 0 }}</h3>
                        </div>
                        <div class="icon-shape icon-lg bg-warning-subtle text-warning rounded-3">
                            <i class="fa-solid fa-clock fa-lg"></i>
                        </div>
                    </div>
                </div>
            </div>
        </div>

        <div class="col-12 col-sm-6 col-xl-3">
            <div class="card card-lg border-0 shadow-sm">
                <div class="card-body">
                    <div class="d-flex align-items-center justify-content-between">
                        <div>
                            <p class="text-muted mb-1 small text-uppercase">Completed</p>
                            <h3 class="mb-0 fw-bold text-success">{{ $completedEmployeePlans ?? 0 }}</h3>
                        </div>
                        <div class="icon-shape icon-lg bg-success-subtle text-success rounded-3">
                            <i class="fa-solid fa-circle-check fa-lg"></i>
                        </div>
                    </div>
                </div>
            </div>
        </div>
    </div>

    <!-- Employee Plans List Card -->
    <div class="row">
        <div class="col-12">
            <div class="card card-lg">
                <!-- Card Header with Search and Filters -->
                <div class="card-header border-bottom">
                    <div class="row g-3 align-items-center">
                        <!-- Search Bar -->
                        <div class="col-12 col-md-4">
                            <div class="input-group">
                                <span class="input-group-text bg-white">
                                    <i class="fa-solid fa-magnifying-glass"></i>
                                </span>
                                <input type="search" wire:model.live.debounce.300ms="search" class="form-control"
                                    placeholder="Search by employee name..." />
                                @if ($search ?? false)
                                    <button wire:click="$set('search', '')" class="btn btn-outline-secondary"
                                        type="button">
                                        <i class="fa-solid fa-times"></i>
                                    </button>
                                @endif
                            </div>
                        </div>

                        <!-- Filters -->
                        <div class="col-12 col-md-8">
                            <div class="d-flex flex-wrap gap-2 justify-content-md-end">
                                <!-- Department Filter -->
                                <select wire:model.live="departmentFilter" class="form-select" style="width: auto;">
                                    <option value="">All Departments</option>
                                    @foreach ($departments ?? [] as $dept)
                                        <option value="{{ $dept->id }}">{{ $dept->name }}</option>
                                    @endforeach
                                </select>

                                <!-- Status Filter -->
                                <select wire:model.live="statusFilter" class="form-select" style="width: auto;">
                                    <option value="">All Status</option>
                                    <option value="draft">Draft</option>
                                    <option value="active">Active</option>
                                    <option value="completed">Completed</option>
                                    <option value="cancelled">Cancelled</option>
                                </select>

                                <!-- Review Status Filter -->
                                <select wire:model.live="reviewStatusFilter" class="form-select" style="width: auto;">
                                    <option value="">All Review Status</option>
                                    <option value="pending">Pending Review</option>
                                    <option value="in_review">In Review</option>
                                    <option value="reviewed">Reviewed</option>
                                    <option value="approved">Approved</option>
                                </select>

                                <!-- Reset Filters -->
                                @if ($search ?? false || $departmentFilter ?? false || $statusFilter ?? false || $reviewStatusFilter ?? false)
                                    <button wire:click="resetFilters" type="button" class="btn btn-outline-secondary">
                                        <i class="fa-solid fa-rotate-left me-1"></i> Reset
                                    </button>
                                @endif
                            </div>
                        </div>
                    </div>
                </div>

                <!-- Table Section -->
                <div class="table-responsive" style="min-height: 400px;">
                    <table class="table table-hover table-centered align-middle mb-0">
                        <thead class="table-light">
                            <tr>
                                <th class="text-center" style="width: 50px;">#</th>
                                <th>Employee Name</th>
                                <th>Plan Name</th>
                                <th>Department Plan</th>
                                <th class="text-center">Status</th>
                                <th>Assigned Date</th>
                                <th class="text-center">Review Status</th>
                                <th class="text-center">Score</th>
                                <th class="text-end" style="width: 150px;">Actions</th>
                            </tr>
                        </thead>
                        <tbody>
                            @forelse ($plans ?? [] as $index => $empPlan)
                                <tr wire:key="emp-plan-{{ $empPlan->id ?? $index }}">
                                    <td class="text-center text-muted">
                                        {{ ($plans->firstItem() ?? 0) + $index }}
                                    </td>
                                    <td>
                                        <div class="d-flex align-items-center gap-2">
                                            @if (!empty($empPlan->employee->photo))
                                                <img src="{{ asset('storage/' . $empPlan->employee->photo) }}"
                                                    alt="{{ $empPlan->employee->first_name }}"
                                                    class="rounded-circle"
                                                    width="40" height="40"
                                                    style="object-fit: cover;">
                                            @else
                                                <div class="rounded-circle bg-primary text-white d-inline-flex align-items-center justify-content-center"
                                                    style="width: 40px; height: 40px; font-size: 14px;">
                                                    {{ strtoupper(substr($empPlan->employee->first_name ?? 'U', 0, 1)) }}{{ strtoupper(substr($empPlan->employee->last_name ?? 'N', 0, 1)) }}
                                                </div>
                                            @endif
                                            <div>
                                                <span class="fw-semibold text-dark">
                                                    {{ $empPlan->employee->first_name ?? '' }} {{ $empPlan->employee->last_name ?? '' }}
                                                </span>
                                                <small class="text-muted d-block">{{ $empPlan->employee->employee_no ?? 'N/A' }}</small>
                                            </div>
                                        </div>
                                    </td>
                                    <td>
                                        <div class="d-flex flex-column">
                                            <span class="fw-semibold text-dark">{{ $empPlan->name ?? 'N/A' }}</span>
                                            @if (!empty($empPlan->description))
                                                <small class="text-muted">{{ Str::limit($empPlan->description, 30) }}</small>
                                            @endif
                                        </div>
                                    </td>
                                    <td>
                                        @if (!empty($empPlan->department_plan))
                                            <span class="badge bg-light text-dark">
                                                <i class="fa-solid fa-link me-1"></i>
                                                {{ $empPlan->department_plan->name ?? 'N/A' }}
                                            </span>
                                        @else
                                            <span class="text-muted">-</span>
                                        @endif
                                    </td>
                                    <td class="text-center">
                                        @php
                                            $statusColors = [
                                                'draft' => 'secondary',
                                                'active' => 'primary',
                                                'completed' => 'success',
                                                'cancelled' => 'danger',
                                            ];
                                            $statusColor = $statusColors[$empPlan->status ?? 'draft'] ?? 'secondary';
                                        @endphp
                                        <span class="badge bg-{{ $statusColor }}">
                                            <i class="fa-solid fa-circle me-1" style="font-size: 6px;"></i>
                                            {{ ucfirst($empPlan->status ?? 'Draft') }}
                                        </span>
                                    </td>
                                    <td>
                                        <small class="text-muted">
                                            <i class="fa-solid fa-calendar me-1"></i>
                                            {{ $empPlan->assigned_date ? \Carbon\Carbon::parse($empPlan->assigned_date)->format('d M Y') : '-' }}
                                        </small>
                                    </td>
                                    <td class="text-center">
                                        @php
                                            $reviewColors = [
                                                'pending' => 'secondary',
                                                'in_review' => 'warning',
                                                'reviewed' => 'info',
                                                'approved' => 'success',
                                            ];
                                            $reviewColor = $reviewColors[$empPlan->review_status ?? 'pending'] ?? 'secondary';
                                        @endphp
                                        <span class="badge bg-{{ $reviewColor }}">
                                            {{ ucfirst(str_replace('_', ' ', $empPlan->review_status ?? 'Pending')) }}
                                        </span>
                                    </td>
                                    <td class="text-center">
                                        @php
                                            $score = $empPlan->score ?? 0;
                                            $scoreColor = $score >= 80 ? 'success' : ($score >= 60 ? 'primary' : ($score >= 40 ? 'warning' : 'danger'));
                                        @endphp
                                        <span class="badge bg-{{ $scoreColor }}-subtle text-{{ $scoreColor }}-emphasis fs-6 px-3 py-2">
                                            {{ $score }}%
                                        </span>
                                    </td>
                                    <td>
                                        <div class="d-flex gap-1 justify-content-end">
                                            <button wire:click="viewDetails('{{ $empPlan->id ?? '' }}')"
                                                class="btn btn-sm btn-ghost-info rounded-circle"
                                                title="View Details">
                                                <i class="fa-solid fa-eye"></i>
                                            </button>
                                            <button wire:click="editEmployeePlan('{{ $empPlan->id ?? '' }}')"
                                                class="btn btn-sm btn-ghost-secondary rounded-circle"
                                                title="Edit">
                                                <i class="fa-solid fa-pen-to-square"></i>
                                            </button>
                                            <button wire:click="reviewPlan('{{ $empPlan->id ?? '' }}')"
                                                class="btn btn-sm btn-ghost-success rounded-circle"
                                                title="Review">
                                                <i class="fa-solid fa-clipboard-check"></i>
                                            </button>
                                            <button wire:click="deleteEmployeePlan('{{ $empPlan->id ?? '' }}')"
                                                type="button"
                                                class="btn btn-sm btn-ghost-danger rounded-circle"
                                                title="Delete"
                                                onclick="confirm('Are you sure you want to delete this employee plan?') || event.stopImmediatePropagation()">
                                                <i class="fa-solid fa-trash"></i>
                                            </button>
                                        </div>
                                    </td>
                                </tr>
                            @empty
                                <tr>
                                    <td colspan="9" class="text-center py-5">
                                        <div class="d-flex flex-column align-items-center justify-content-center">
                                            <i class="fa-solid fa-users text-muted mb-3"
                                                style="font-size: 48px;"></i>
                                            <h5 class="text-muted">No Employee Plans Found</h5>
                                            <p class="text-muted">
                                                @if ($search ?? false || $departmentFilter ?? false || $statusFilter ?? false || $reviewStatusFilter ?? false)
                                                    Try adjusting your filters or search query
                                                @else
                                                    Start by assigning plans to employees
                                                @endif
                                            </p>
                                            @if (!($search ?? false) && !($departmentFilter ?? false) && !($statusFilter ?? false) && !($reviewStatusFilter ?? false))
                                                <button class="btn btn-primary mt-2" data-bs-toggle="modal" data-bs-target="#createEmployeePlanModal">
                                                    <i class="fa-solid fa-plus me-1"></i> Assign Plan to Employee
                                                </button>
                                            @endif
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
                            @if (($plans->total() ?? 0) > 0)
                                Showing {{ $plans->firstItem() ?? 0 }} to {{ $plans->lastItem() ?? 0 }} of
                                {{ $plans->total() ?? 0 }} employee plans
                            @else
                                No employee plans found
                            @endif
                        </div>

                        <!-- Pagination and Per Page -->
                        <div class="d-flex flex-column flex-sm-row align-items-center gap-3">
                            <!-- Per Page Selector -->
                            <div class="d-flex align-items-center gap-2">
                                <label class="form-label mb-0 text-nowrap small">Rows per page:</label>
                                <select wire:model.live="perPage" class="form-select form-select-sm"
                                    style="width: auto;">
                                    <option value="10">10</option>
                                    <option value="25">25</option>
                                    <option value="50">50</option>
                                    <option value="100">100</option>
                                </select>
                            </div>

                            <!-- Pagination Links -->
                            @if (isset($plans) && method_exists($plans, 'links'))
                                <div>
                                    {{ $plans->links() }}
                                </div>
                            @endif
                        </div>
                    </div>
                </div>
            </div>
        </div>
    </div>

    <!-- Create/Edit Employee Plan Modal -->
    <x-forms.modal id="createEmployeePlanModal" title="{{ $editingEmployeePlanId ?? false ? 'Edit Employee Plan' : 'Assign Plan to Employee' }}" size="modal-lg" :centered="true">
        <form wire:submit.prevent="saveEmployeePlan">
            <div class="row g-3">
                <div class="col-md-6">
                    <label for="employee" class="form-label">Employee <span class="text-danger">*</span></label>
                    <select wire:model="employeePlanForm.employee_id" class="form-select @error('employeePlanForm.employee_id') is-invalid @enderror" id="employee">
                        <option value="">Select Employee</option>
                        @foreach ($employees ?? [] as $emp)
                            <option value="{{ $emp->id }}">{{ $emp->first_name }} {{ $emp->last_name }} ({{ $emp->employee_no }})</option>
                        @endforeach
                    </select>
                    @error('employeePlanForm.employee_id')
                        <div class="invalid-feedback">{{ $message }}</div>
                    @enderror
                </div>

                <div class="col-md-6">
                    <label for="deptPlan" class="form-label">Department Plan</label>
                    <select wire:model="employeePlanForm.department_plan_id" class="form-select @error('employeePlanForm.department_plan_id') is-invalid @enderror" id="deptPlan">
                        <option value="">Select Department Plan</option>
                        @foreach ($departmentPlans ?? [] as $deptPlan)
                            <option value="{{ $deptPlan->id }}">{{ $deptPlan->name }}</option>
                        @endforeach
                    </select>
                    @error('employeePlanForm.department_plan_id')
                        <div class="invalid-feedback">{{ $message }}</div>
                    @enderror
                </div>

                <div class="col-12">
                    <label for="empPlanName" class="form-label">Plan Name <span class="text-danger">*</span></label>
                    <input type="text" wire:model="employeePlanForm.name" class="form-control @error('employeePlanForm.name') is-invalid @enderror" id="empPlanName" placeholder="Enter employee plan name">
                    @error('employeePlanForm.name')
                        <div class="invalid-feedback">{{ $message }}</div>
                    @enderror
                </div>

                <div class="col-12">
                    <label for="empPlanDescription" class="form-label">Description</label>
                    <textarea wire:model="employeePlanForm.description" class="form-control @error('employeePlanForm.description') is-invalid @enderror" id="empPlanDescription" rows="3" placeholder="Enter plan description"></textarea>
                    @error('employeePlanForm.description')
                        <div class="invalid-feedback">{{ $message }}</div>
                    @enderror
                </div>

                <div class="col-md-6">
                    <label for="empAssignedDate" class="form-label">Assigned Date <span class="text-danger">*</span></label>
                    <input type="date" wire:model="employeePlanForm.assigned_date" class="form-control @error('employeePlanForm.assigned_date') is-invalid @enderror" id="empAssignedDate">
                    @error('employeePlanForm.assigned_date')
                        <div class="invalid-feedback">{{ $message }}</div>
                    @enderror
                </div>

                <div class="col-md-6">
                    <label for="empStatus" class="form-label">Status <span class="text-danger">*</span></label>
                    <select wire:model="employeePlanForm.status" class="form-select @error('employeePlanForm.status') is-invalid @enderror" id="empStatus">
                        <option value="draft">Draft</option>
                        <option value="active">Active</option>
                        <option value="completed">Completed</option>
                        <option value="cancelled">Cancelled</option>
                    </select>
                    @error('employeePlanForm.status')
                        <div class="invalid-feedback">{{ $message }}</div>
                    @enderror
                </div>

                <div class="col-md-6">
                    <label for="reviewStatus" class="form-label">Review Status</label>
                    <select wire:model="employeePlanForm.review_status" class="form-select @error('employeePlanForm.review_status') is-invalid @enderror" id="reviewStatus">
                        <option value="pending">Pending Review</option>
                        <option value="in_review">In Review</option>
                        <option value="reviewed">Reviewed</option>
                        <option value="approved">Approved</option>
                    </select>
                    @error('employeePlanForm.review_status')
                        <div class="invalid-feedback">{{ $message }}</div>
                    @enderror
                </div>

                <div class="col-md-6">
                    <label for="targetScore" class="form-label">Target Score (%)</label>
                    <input type="number" wire:model="employeePlanForm.target_score" class="form-control @error('employeePlanForm.target_score') is-invalid @enderror" id="targetScore" placeholder="0" min="0" max="100" step="1">
                    @error('employeePlanForm.target_score')
                        <div class="invalid-feedback">{{ $message }}</div>
                    @enderror
                </div>

                <div class="col-12">
                    <label for="objectives" class="form-label">Key Objectives</label>
                    <textarea wire:model="employeePlanForm.objectives" class="form-control @error('employeePlanForm.objectives') is-invalid @enderror" id="objectives" rows="3" placeholder="Enter key objectives for this employee"></textarea>
                    @error('employeePlanForm.objectives')
                        <div class="invalid-feedback">{{ $message }}</div>
                    @enderror
                </div>
            </div>

            <x-slot name="footer">
                <button type="button" class="btn btn-secondary" data-bs-dismiss="modal">
                    <i class="fa-solid fa-times me-1"></i> Cancel
                </button>
                <button type="submit" class="btn btn-primary">
                    <i class="fa-solid fa-save me-1"></i> Save Employee Plan
                </button>
            </x-slot>
        </form>
    </x-forms.modal>

    <!-- Loading Indicator -->
    <div wire:loading class="position-fixed top-50 start-50 translate-middle" style="z-index: 9999;">
        <div class="spinner-border text-primary" role="status">
            <span class="visually-hidden">Loading...</span>
        </div>
    </div>
</div>
