<div class="custom-container">

    <x-pages.breadcrumn title="DEPARTMENT PLANS" :breadcrumbs="[
        ['label' => 'Home', 'url' => route('dashboard')],
        ['label' => 'Performance Management', 'url' => '#'],
        ['label' => 'Department Plans', 'url' => route('performance.dept.plans')],
    ]">
        <button class='btn btn-primary d-md-flex align-items-center gap-2' data-bs-toggle="modal" data-bs-target="#createDepartmentPlanModal">
            <i class="fa-solid fa-plus"></i> ASSIGN PLAN TO DEPARTMENT
        </button>
    </x-pages.breadcrumn>

    <!-- Statistics Cards -->
    <div class="row g-3 mb-4">
        <div class="col-12 col-sm-6 col-xl-3">
            <div class="card card-lg border-0 shadow-sm">
                <div class="card-body">
                    <div class="d-flex align-items-center justify-content-between">
                        <div>
                            <p class="text-muted mb-1 small text-uppercase">Total Dept Plans</p>
                            <h3 class="mb-0 fw-bold">{{ $totalDepartmentPlans ?? 0 }}</h3>
                        </div>
                        <div class="icon-shape icon-lg bg-primary-subtle text-primary rounded-3">
                            <i class="fa-solid fa-building fa-lg"></i>
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
                            <h3 class="mb-0 fw-bold text-primary">{{ $activeDepartmentPlans ?? 0 }}</h3>
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
                            <p class="text-muted mb-1 small text-uppercase">Completed</p>
                            <h3 class="mb-0 fw-bold text-info">{{ $completedDepartmentPlans ?? 0 }}</h3>
                        </div>
                        <div class="icon-shape icon-lg bg-info-subtle text-info rounded-3">
                            <i class="fa-solid fa-circle-check fa-lg"></i>
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
                            <p class="text-muted mb-1 small text-uppercase">Departments Covered</p>
                            <h3 class="mb-0 fw-bold text-success">{{ $departmentsCovered ?? 0 }}</h3>
                        </div>
                        <div class="icon-shape icon-lg bg-success-subtle text-success rounded-3">
                            <i class="fa-solid fa-sitemap fa-lg"></i>
                        </div>
                    </div>
                </div>
            </div>
        </div>
    </div>

    <!-- Department Plans List Card -->
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
                                    placeholder="Search by plan name or department..." />
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
                                    <option value="approved">Approved</option>
                                    <option value="completed">Completed</option>
                                    <option value="cancelled">Cancelled</option>
                                </select>

                                <!-- Reset Filters -->
                                @if ($search ?? false || $departmentFilter ?? false || $statusFilter ?? false)
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
                                <th>Department</th>
                                <th>Plan Name</th>
                                <th>Org Plan Reference</th>
                                <th class="text-center">Status</th>
                                <th>Assigned Date</th>
                                <th class="text-center">Progress</th>
                                <th class="text-end" style="width: 150px;">Actions</th>
                            </tr>
                        </thead>
                        <tbody>
                            @forelse ($plans ?? [] as $index => $deptPlan)
                                <tr wire:key="dept-plan-{{ $deptPlan->id ?? $index }}">
                                    <td class="text-center text-muted">
                                        {{ ($plans->firstItem() ?? 0) + $index }}
                                    </td>
                                    <td>
                                        <div class="d-flex align-items-center gap-2">
                                            <div class="icon-shape icon-sm bg-primary-subtle text-primary rounded-2">
                                                <i class="fa-solid fa-building"></i>
                                            </div>
                                            <div>
                                                <span class="fw-semibold text-dark">{{ $deptPlan->department->name ?? 'N/A' }}</span>
                                                @if (!empty($deptPlan->department->code))
                                                    <small class="text-muted d-block">Code: {{ $deptPlan->department->code }}</small>
                                                @endif
                                            </div>
                                        </div>
                                    </td>
                                    <td>
                                        <div class="d-flex flex-column">
                                            <span class="fw-semibold text-dark">{{ $deptPlan->name ?? 'N/A' }}</span>
                                            @if (!empty($deptPlan->description))
                                                <small class="text-muted">{{ Str::limit($deptPlan->description, 40) }}</small>
                                            @endif
                                        </div>
                                    </td>
                                    <td>
                                        @if (!empty($deptPlan->organizational_plan))
                                            <span class="badge bg-light text-dark">
                                                <i class="fa-solid fa-link me-1"></i>
                                                {{ $deptPlan->organizational_plan->name ?? 'N/A' }}
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
                                                'approved' => 'success',
                                                'completed' => 'info',
                                                'cancelled' => 'danger',
                                            ];
                                            $statusColor = $statusColors[$deptPlan->status ?? 'draft'] ?? 'secondary';
                                        @endphp
                                        <span class="badge bg-{{ $statusColor }}">
                                            <i class="fa-solid fa-circle me-1" style="font-size: 6px;"></i>
                                            {{ ucfirst($deptPlan->status ?? 'Draft') }}
                                        </span>
                                    </td>
                                    <td>
                                        <small class="text-muted">
                                            <i class="fa-solid fa-calendar me-1"></i>
                                            {{ $deptPlan->assigned_date ? \Carbon\Carbon::parse($deptPlan->assigned_date)->format('d M Y') : '-' }}
                                        </small>
                                    </td>
                                    <td class="text-center">
                                        @php
                                            $progress = $deptPlan->progress ?? 0;
                                            $progressColor = $progress >= 75 ? 'success' : ($progress >= 50 ? 'primary' : ($progress >= 25 ? 'warning' : 'danger'));
                                        @endphp
                                        <div class="d-flex align-items-center gap-2">
                                            <div class="progress flex-grow-1" style="height: 8px; min-width: 80px;">
                                                <div class="progress-bar bg-{{ $progressColor }}" role="progressbar"
                                                    style="width: {{ $progress }}%"
                                                    aria-valuenow="{{ $progress }}"
                                                    aria-valuemin="0"
                                                    aria-valuemax="100">
                                                </div>
                                            </div>
                                            <small class="fw-semibold">{{ $progress }}%</small>
                                        </div>
                                    </td>
                                    <td>
                                        <div class="d-flex gap-1 justify-content-end">
                                            <button wire:click="viewDetails('{{ $deptPlan->id ?? '' }}')"
                                                class="btn btn-sm btn-ghost-info rounded-circle"
                                                title="View Details">
                                                <i class="fa-solid fa-eye"></i>
                                            </button>
                                            <button wire:click="editDepartmentPlan('{{ $deptPlan->id ?? '' }}')"
                                                class="btn btn-sm btn-ghost-secondary rounded-circle"
                                                title="Edit">
                                                <i class="fa-solid fa-pen-to-square"></i>
                                            </button>
                                            <button wire:click="deleteDepartmentPlan('{{ $deptPlan->id ?? '' }}')"
                                                type="button"
                                                class="btn btn-sm btn-ghost-danger rounded-circle"
                                                title="Delete"
                                                onclick="confirm('Are you sure you want to delete this department plan?') || event.stopImmediatePropagation()">
                                                <i class="fa-solid fa-trash"></i>
                                            </button>
                                        </div>
                                    </td>
                                </tr>
                            @empty
                                <tr>
                                    <td colspan="8" class="text-center py-5">
                                        <div class="d-flex flex-column align-items-center justify-content-center">
                                            <i class="fa-solid fa-building text-muted mb-3"
                                                style="font-size: 48px;"></i>
                                            <h5 class="text-muted">No Department Plans Found</h5>
                                            <p class="text-muted">
                                                @if ($search ?? false || $departmentFilter ?? false || $statusFilter ?? false)
                                                    Try adjusting your filters or search query
                                                @else
                                                    Start by assigning plans to departments
                                                @endif
                                            </p>
                                            @if (!($search ?? false) && !($departmentFilter ?? false) && !($statusFilter ?? false))
                                                <button class="btn btn-primary mt-2" data-bs-toggle="modal" data-bs-target="#createDepartmentPlanModal">
                                                    <i class="fa-solid fa-plus me-1"></i> Assign Plan to Department
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
                                {{ $plans->total() ?? 0 }} department plans
                            @else
                                No department plans found
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

    <!-- Create/Edit Department Plan Modal -->
    <x-forms.modal id="createDepartmentPlanModal" title="{{ $editingDepartmentPlanId ?? false ? 'Edit Department Plan' : 'Assign Plan to Department' }}" size="modal-lg" :centered="true">
        <form wire:submit.prevent="saveDepartmentPlan">
            <div class="row g-3">
                <div class="col-md-6">
                    <label for="department" class="form-label">Department <span class="text-danger">*</span></label>
                    <select wire:model="departmentPlanForm.department_id" class="form-select @error('departmentPlanForm.department_id') is-invalid @enderror" id="department">
                        <option value="">Select Department</option>
                        @foreach ($departments ?? [] as $dept)
                            <option value="{{ $dept->id }}">{{ $dept->name }}</option>
                        @endforeach
                    </select>
                    @error('departmentPlanForm.department_id')
                        <div class="invalid-feedback">{{ $message }}</div>
                    @enderror
                </div>

                <div class="col-md-6">
                    <label for="orgPlan" class="form-label">Organizational Plan <span class="text-danger">*</span></label>
                    <select wire:model="departmentPlanForm.organizational_plan_id" class="form-select @error('departmentPlanForm.organizational_plan_id') is-invalid @enderror" id="orgPlan">
                        <option value="">Select Organizational Plan</option>
                        @foreach ($organizationalPlans ?? [] as $orgPlan)
                            <option value="{{ $orgPlan->id }}">{{ $orgPlan->name }}</option>
                        @endforeach
                    </select>
                    @error('departmentPlanForm.organizational_plan_id')
                        <div class="invalid-feedback">{{ $message }}</div>
                    @enderror
                </div>

                <div class="col-12">
                    <label for="deptPlanName" class="form-label">Plan Name <span class="text-danger">*</span></label>
                    <input type="text" wire:model="departmentPlanForm.name" class="form-control @error('departmentPlanForm.name') is-invalid @enderror" id="deptPlanName" placeholder="Enter department plan name">
                    @error('departmentPlanForm.name')
                        <div class="invalid-feedback">{{ $message }}</div>
                    @enderror
                </div>

                <div class="col-12">
                    <label for="deptPlanDescription" class="form-label">Description</label>
                    <textarea wire:model="departmentPlanForm.description" class="form-control @error('departmentPlanForm.description') is-invalid @enderror" id="deptPlanDescription" rows="3" placeholder="Enter plan description"></textarea>
                    @error('departmentPlanForm.description')
                        <div class="invalid-feedback">{{ $message }}</div>
                    @enderror
                </div>

                <div class="col-md-6">
                    <label for="assignedDate" class="form-label">Assigned Date <span class="text-danger">*</span></label>
                    <input type="date" wire:model="departmentPlanForm.assigned_date" class="form-control @error('departmentPlanForm.assigned_date') is-invalid @enderror" id="assignedDate">
                    @error('departmentPlanForm.assigned_date')
                        <div class="invalid-feedback">{{ $message }}</div>
                    @enderror
                </div>

                <div class="col-md-6">
                    <label for="deptStatus" class="form-label">Status <span class="text-danger">*</span></label>
                    <select wire:model="departmentPlanForm.status" class="form-select @error('departmentPlanForm.status') is-invalid @enderror" id="deptStatus">
                        <option value="draft">Draft</option>
                        <option value="active">Active</option>
                        <option value="approved">Approved</option>
                        <option value="completed">Completed</option>
                        <option value="cancelled">Cancelled</option>
                    </select>
                    @error('departmentPlanForm.status')
                        <div class="invalid-feedback">{{ $message }}</div>
                    @enderror
                </div>

                <div class="col-12">
                    <label for="targetMetrics" class="form-label">Target Metrics/KPIs</label>
                    <textarea wire:model="departmentPlanForm.target_metrics" class="form-control @error('departmentPlanForm.target_metrics') is-invalid @enderror" id="targetMetrics" rows="2" placeholder="Enter target metrics or KPIs for this department"></textarea>
                    @error('departmentPlanForm.target_metrics')
                        <div class="invalid-feedback">{{ $message }}</div>
                    @enderror
                </div>
            </div>

            <x-slot name="footer">
                <button type="button" class="btn btn-secondary" data-bs-dismiss="modal">
                    <i class="fa-solid fa-times me-1"></i> Cancel
                </button>
                <button type="submit" class="btn btn-primary">
                    <i class="fa-solid fa-save me-1"></i> Save Department Plan
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
