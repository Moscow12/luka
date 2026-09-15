<div class="custom-container">

    <x-pages.breadcrumn title="DEPARTMENT PLANS" :breadcrumbs="[
        ['label' => 'Home', 'url' => route('dashboard')],
        ['label' => 'Performance Management', 'url' => '#'],
        ['label' => 'Department Plans', 'url' => route('performance.dept.plans')],
    ]">
        <button class='btn btn-primary d-md-flex align-items-center gap-2' wire:click="createPlan">
            <i class="fa-solid fa-plus"></i> ASSIGN PLAN TO DEPARTMENT
        </button>
    </x-pages.breadcrumn>

    {{-- Flash Messages --}}
    @if (session()->has('success'))
        <div class="alert alert-success alert-dismissible fade show" role="alert">
            <i class="fa-solid fa-circle-check me-2"></i>{{ session('success') }}
            <button type="button" class="btn-close" data-bs-dismiss="alert"></button>
        </div>
    @endif
    @if (session()->has('error'))
        <div class="alert alert-danger alert-dismissible fade show" role="alert">
            <i class="fa-solid fa-circle-xmark me-2"></i>{{ session('error') }}
            <button type="button" class="btn-close" data-bs-dismiss="alert"></button>
        </div>
    @endif

    <!-- Statistics Cards -->
    <div class="row g-3 mb-4">
        <div class="col-12 col-sm-6 col-xl-3">
            <div class="card card-lg border-0 shadow-sm">
                <div class="card-body">
                    <div class="d-flex align-items-center justify-content-between">
                        <div>
                            <p class="text-muted mb-1 small text-uppercase">Total Dept Plans</p>
                            <h3 class="mb-0 fw-bold">{{ $totalPlans ?? 0 }}</h3>
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
                            <h3 class="mb-0 fw-bold text-primary">{{ $activePlans ?? 0 }}</h3>
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
                            <h3 class="mb-0 fw-bold text-info">{{ $completedPlans ?? 0 }}</h3>
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
                                @if ($search)
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

                                <!-- Reset Filters -->
                                @if ($search || $departmentFilter || $statusFilter)
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
                                <th class="text-center">Items</th>
                                <th class="text-end" style="width: 180px;">Actions</th>
                            </tr>
                        </thead>
                        <tbody>
                            @forelse ($plans ?? [] as $index => $deptPlan)
                                <tr wire:key="dept-plan-{{ $deptPlan->id }}">
                                    <td class="text-center text-muted">
                                        {{ $plans->firstItem() + $index }}
                                    </td>
                                    <td>
                                        <div class="d-flex align-items-center gap-2">
                                            <div class="icon-shape icon-sm bg-primary-subtle text-primary rounded-2">
                                                <i class="fa-solid fa-building"></i>
                                            </div>
                                            <div>
                                                <span class="fw-semibold text-dark">{{ $deptPlan->department->name ?? 'N/A' }}</span>
                                            </div>
                                        </div>
                                    </td>
                                    <td>
                                        <div class="d-flex flex-column">
                                            <span class="fw-semibold text-dark">{{ $deptPlan->plan_name }}</span>
                                            @if ($deptPlan->description)
                                                <small class="text-muted">{{ Str::limit($deptPlan->description, 40) }}</small>
                                            @endif
                                        </div>
                                    </td>
                                    <td>
                                        @if ($deptPlan->organizationalPlan)
                                            <span class="badge bg-light text-dark">
                                                <i class="fa-solid fa-link me-1"></i>
                                                {{ $deptPlan->organizationalPlan->plan_name }}
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
                                                'completed' => 'info',
                                                'cancelled' => 'danger',
                                            ];
                                            $statusColor = $statusColors[$deptPlan->status] ?? 'secondary';
                                        @endphp
                                        <span class="badge bg-{{ $statusColor }}">
                                            <i class="fa-solid fa-circle me-1" style="font-size: 6px;"></i>
                                            {{ ucfirst($deptPlan->status) }}
                                        </span>
                                    </td>
                                    <td>
                                        <small class="text-muted">
                                            <i class="fa-solid fa-calendar me-1"></i>
                                            {{ $deptPlan->assigned_at ? \Carbon\Carbon::parse($deptPlan->assigned_at)->format('d M Y') : '-' }}
                                        </small>
                                    </td>
                                    <td class="text-center">
                                        <span class="badge bg-primary-subtle text-primary">
                                            {{ $deptPlan->department_plan_items_count ?? 0 }} Items
                                        </span>
                                    </td>
                                    <td>
                                        <div class="d-flex gap-1 justify-content-end">
                                            <a href="{{ route('performance.dept.plans.items', $deptPlan->id) }}"
                                                class="btn btn-sm btn-ghost-info rounded-circle"
                                                title="Manage Items">
                                                <i class="fa-solid fa-list"></i>
                                            </a>
                                            @if ($deptPlan->department_plan_items_count == 0)
                                                <button wire:click="distributePlanItems('{{ $deptPlan->id }}')"
                                                    wire:confirm="This will copy all items from the organizational plan. Continue?"
                                                    class="btn btn-sm btn-ghost-success rounded-circle"
                                                    title="Distribute Items from Org Plan">
                                                    <i class="fa-solid fa-download"></i>
                                                </button>
                                            @endif
                                            <button wire:click="editPlan('{{ $deptPlan->id }}')"
                                                class="btn btn-sm btn-ghost-secondary rounded-circle"
                                                title="Edit">
                                                <i class="fa-solid fa-pen-to-square"></i>
                                            </button>
                                            @if ($deptPlan->status === 'draft')
                                                <button wire:click="deletePlan('{{ $deptPlan->id }}')"
                                                    wire:confirm="Are you sure you want to delete this department plan?"
                                                    class="btn btn-sm btn-ghost-danger rounded-circle"
                                                    title="Delete">
                                                    <i class="fa-solid fa-trash"></i>
                                                </button>
                                            @endif
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
                                                @if ($search || $departmentFilter || $statusFilter)
                                                    Try adjusting your filters or search query
                                                @else
                                                    Start by assigning plans to departments
                                                @endif
                                            </p>
                                            @if (!$search && !$departmentFilter && !$statusFilter)
                                                <button class="btn btn-primary mt-2" wire:click="createPlan">
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
                                Showing {{ $plans->firstItem() }} to {{ $plans->lastItem() }} of
                                {{ $plans->total() }} department plans
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
                            <div>
                                {{ $plans->links() }}
                            </div>
                        </div>
                    </div>
                </div>
            </div>
        </div>
    </div>

    <!-- Create/Edit Department Plan Modal -->
    @if ($showModal)
        <div class="modal fade show d-block" tabindex="-1" style="background-color: rgba(0,0,0,0.5);">
            <div class="modal-dialog modal-lg modal-dialog-centered modal-dialog-scrollable">
                <div class="modal-content">
                    <div class="modal-header">
                        <h5 class="modal-title">
                            <i class="fa-solid fa-building me-2"></i>
                            {{ $editingPlanId ? 'Edit Department Plan' : 'Assign Plan to Department' }}
                        </h5>
                        <button type="button" class="btn-close" wire:click="closeModal"></button>
                    </div>
                    <form wire:submit.prevent="savePlan">
                        <div class="modal-body" style="max-height: 70vh; overflow-y: auto;">
                            <div class="row g-3">
                                <div class="col-md-6">
                                    <label for="department" class="form-label">Department <span class="text-danger">*</span></label>
                                    <select wire:model="planForm.department_id" class="form-select @error('planForm.department_id') is-invalid @enderror" id="department">
                                        <option value="">Select Department</option>
                                        @foreach ($departments ?? [] as $dept)
                                            <option value="{{ $dept->id }}">{{ $dept->name }}</option>
                                        @endforeach
                                    </select>
                                    @error('planForm.department_id')
                                        <div class="invalid-feedback">{{ $message }}</div>
                                    @enderror
                                </div>

                                <div class="col-md-6">
                                    <label for="orgPlan" class="form-label">Organizational Plan <span class="text-danger">*</span></label>
                                    <select wire:model="planForm.organizational_plan_id" class="form-select @error('planForm.organizational_plan_id') is-invalid @enderror" id="orgPlan">
                                        <option value="">Select Organizational Plan</option>
                                        @forelse ($organizationalPlans ?? [] as $orgPlan)
                                            <option value="{{ $orgPlan->id }}">{{ $orgPlan->plan_name }}</option>
                                        @empty
                                            <option value="" disabled>No approved plans available</option>
                                        @endforelse
                                    </select>
                                    @error('planForm.organizational_plan_id')
                                        <div class="invalid-feedback">{{ $message }}</div>
                                    @enderror
                                    @if ($organizationalPlans->isEmpty())
                                        <div class="form-text text-warning">
                                            <i class="fa-solid fa-exclamation-triangle me-1"></i>
                                            No approved organizational plans available. Please approve an organizational plan first.
                                        </div>
                                    @endif
                                </div>

                                <div class="col-12">
                                    <label for="planName" class="form-label">Plan Name <span class="text-danger">*</span></label>
                                    <input type="text" wire:model="planForm.plan_name" class="form-control @error('planForm.plan_name') is-invalid @enderror" id="planName" placeholder="Enter department plan name">
                                    @error('planForm.plan_name')
                                        <div class="invalid-feedback">{{ $message }}</div>
                                    @enderror
                                </div>

                                <div class="col-12">
                                    <label for="description" class="form-label">Description</label>
                                    <textarea wire:model="planForm.description" class="form-control @error('planForm.description') is-invalid @enderror" id="description" rows="3" placeholder="Enter plan description"></textarea>
                                    @error('planForm.description')
                                        <div class="invalid-feedback">{{ $message }}</div>
                                    @enderror
                                </div>

                                <div class="col-md-6">
                                    <label for="status" class="form-label">Status <span class="text-danger">*</span></label>
                                    <select wire:model="planForm.status" class="form-select @error('planForm.status') is-invalid @enderror" id="status">
                                        <option value="draft">Draft</option>
                                        <option value="active">Active</option>
                                        <option value="completed">Completed</option>
                                        <option value="cancelled">Cancelled</option>
                                    </select>
                                    @error('planForm.status')
                                        <div class="invalid-feedback">{{ $message }}</div>
                                    @enderror
                                </div>
                            </div>
                        </div>
                        <div class="modal-footer">
                            <button type="button" class="btn btn-secondary" wire:click="closeModal">
                                <i class="fa-solid fa-times me-1"></i> Cancel
                            </button>
                            <button type="submit" class="btn btn-primary" @if($organizationalPlans->isEmpty()) disabled @endif>
                                <i class="fa-solid fa-save me-1"></i> {{ $editingPlanId ? 'Update Plan' : 'Save Plan' }}
                            </button>
                        </div>
                    </form>
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
</div>
