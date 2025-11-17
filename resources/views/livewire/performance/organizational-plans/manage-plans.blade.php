<div class="custom-container">

    <x-pages.breadcrumn title="ORGANIZATIONAL PLANS" :breadcrumbs="[
        ['label' => 'Home', 'url' => route('dashboard')],
        ['label' => 'Performance Management', 'url' => '#'],
        ['label' => 'Organizational Plans', 'url' => route('performance.org.plans')],
    ]">
        <button class='btn btn-primary d-md-flex align-items-center gap-2' data-bs-toggle="modal" data-bs-target="#createPlanModal">
            <i class="fa-solid fa-plus"></i> CREATE NEW PLAN
        </button>
    </x-pages.breadcrumn>

    <!-- Statistics Cards -->
    <div class="row g-3 mb-4">
        <div class="col-12 col-sm-6 col-xl-3">
            <div class="card card-lg border-0 shadow-sm">
                <div class="card-body">
                    <div class="d-flex align-items-center justify-content-between">
                        <div>
                            <p class="text-muted mb-1 small text-uppercase">Total Plans</p>
                            <h3 class="mb-0 fw-bold">{{ $totalPlans ?? 0 }}</h3>
                        </div>
                        <div class="icon-shape icon-lg bg-primary-subtle text-primary rounded-3">
                            <i class="fa-solid fa-clipboard-list fa-lg"></i>
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
                            <p class="text-muted mb-1 small text-uppercase">Approved</p>
                            <h3 class="mb-0 fw-bold text-success">{{ $approvedPlans ?? 0 }}</h3>
                        </div>
                        <div class="icon-shape icon-lg bg-success-subtle text-success rounded-3">
                            <i class="fa-solid fa-circle-check fa-lg"></i>
                        </div>
                    </div>
                </div>
            </div>
        </div>
    </div>

    <!-- Plans List Card -->
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
                                    placeholder="Search plans by name..." />
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
                                <!-- Status Filter -->
                                <select wire:model.live="statusFilter" class="form-select" style="width: auto;">
                                    <option value="">All Status</option>
                                    <option value="draft">Draft</option>
                                    <option value="active">Active</option>
                                    <option value="approved">Approved</option>
                                    <option value="completed">Completed</option>
                                    <option value="cancelled">Cancelled</option>
                                </select>

                                <!-- Period Type Filter -->
                                <select wire:model.live="periodTypeFilter" class="form-select" style="width: auto;">
                                    <option value="">All Period Types</option>
                                    <option value="annual">Annual</option>
                                    <option value="quarterly">Quarterly</option>
                                    <option value="monthly">Monthly</option>
                                </select>

                                <!-- Reset Filters -->
                                @if ($search ?? false || $statusFilter ?? false || $periodTypeFilter ?? false)
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
                                <th>Plan Name</th>
                                <th>Period</th>
                                <th>Type</th>
                                <th class="text-center">Status</th>
                                <th class="text-center">Total Weight</th>
                                <th class="text-center">Items Count</th>
                                <th class="text-end" style="width: 200px;">Actions</th>
                            </tr>
                        </thead>
                        <tbody>
                            @forelse ($plans ?? [] as $index => $plan)
                                <tr wire:key="plan-{{ $plan->id ?? $index }}">
                                    <td class="text-center text-muted">
                                        {{ ($plans->firstItem() ?? 0) + $index }}
                                    </td>
                                    <td>
                                        <div class="d-flex flex-column">
                                            <span class="fw-semibold text-dark">{{ $plan->name ?? 'N/A' }}</span>
                                            @if (!empty($plan->description))
                                                <small class="text-muted">{{ Str::limit($plan->description, 50) }}</small>
                                            @endif
                                        </div>
                                    </td>
                                    <td>
                                        <div class="d-flex flex-column">
                                            <small class="text-muted">
                                                <i class="fa-solid fa-calendar-check text-success me-1"></i>
                                                {{ $plan->start_date ? \Carbon\Carbon::parse($plan->start_date)->format('d M Y') : '-' }}
                                            </small>
                                            <small class="text-muted">
                                                <i class="fa-solid fa-calendar-xmark text-danger me-1"></i>
                                                {{ $plan->end_date ? \Carbon\Carbon::parse($plan->end_date)->format('d M Y') : '-' }}
                                            </small>
                                        </div>
                                    </td>
                                    <td>
                                        <span class="badge bg-light text-dark">{{ ucfirst($plan->period_type ?? 'N/A') }}</span>
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
                                            $statusColor = $statusColors[$plan->status ?? 'draft'] ?? 'secondary';
                                        @endphp
                                        <span class="badge bg-{{ $statusColor }}">
                                            <i class="fa-solid fa-circle me-1" style="font-size: 6px;"></i>
                                            {{ ucfirst($plan->status ?? 'Draft') }}
                                        </span>
                                    </td>
                                    <td class="text-center">
                                        <span class="fw-semibold">{{ $plan->total_weight ?? 0 }}%</span>
                                    </td>
                                    <td class="text-center">
                                        <span class="badge bg-primary-subtle text-primary">
                                            {{ $plan->items_count ?? 0 }} Items
                                        </span>
                                    </td>
                                    <td>
                                        <div class="d-flex gap-1 justify-content-end">
                                            <button wire:click="manageItems('{{ $plan->id ?? '' }}')"
                                                class="btn btn-sm btn-ghost-info rounded-circle"
                                                title="View Items">
                                                <i class="fa-solid fa-list"></i>
                                            </button>
                                            <button wire:click="editPlan('{{ $plan->id ?? '' }}')"
                                                class="btn btn-sm btn-ghost-secondary rounded-circle"
                                                title="Edit">
                                                <i class="fa-solid fa-pen-to-square"></i>
                                            </button>
                                            @if (($plan->status ?? '') === 'active')
                                                <button wire:click="approvePlan('{{ $plan->id ?? '' }}')"
                                                    class="btn btn-sm btn-ghost-success rounded-circle"
                                                    title="Approve">
                                                    <i class="fa-solid fa-check"></i>
                                                </button>
                                            @endif
                                            <button wire:click="deletePlan('{{ $plan->id ?? '' }}')"
                                                type="button"
                                                class="btn btn-sm btn-ghost-danger rounded-circle"
                                                title="Delete"
                                                onclick="confirm('Are you sure you want to delete this plan?') || event.stopImmediatePropagation()">
                                                <i class="fa-solid fa-trash"></i>
                                            </button>
                                        </div>
                                    </td>
                                </tr>
                            @empty
                                <tr>
                                    <td colspan="8" class="text-center py-5">
                                        <div class="d-flex flex-column align-items-center justify-content-center">
                                            <i class="fa-solid fa-clipboard-list text-muted mb-3"
                                                style="font-size: 48px;"></i>
                                            <h5 class="text-muted">No Plans Found</h5>
                                            <p class="text-muted">
                                                @if ($search ?? false || $statusFilter ?? false || $periodTypeFilter ?? false)
                                                    Try adjusting your filters or search query
                                                @else
                                                    Start by creating a new organizational plan
                                                @endif
                                            </p>
                                            @if (!($search ?? false) && !($statusFilter ?? false) && !($periodTypeFilter ?? false))
                                                <button class="btn btn-primary mt-2" data-bs-toggle="modal" data-bs-target="#createPlanModal">
                                                    <i class="fa-solid fa-plus me-1"></i> Create New Plan
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
                                {{ $plans->total() ?? 0 }} plans
                            @else
                                No plans found
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

    <!-- Create/Edit Plan Modal -->
    <x-forms.modal id="createPlanModal" title="{{ $editingPlanId ?? false ? 'Edit Plan' : 'Create New Plan' }}" size="modal-lg" :centered="true">
        <form wire:submit.prevent="savePlan">
            <div class="row g-3">
                <div class="col-12">
                    <label for="planName" class="form-label">Plan Name <span class="text-danger">*</span></label>
                    <input type="text" wire:model="planForm.name" class="form-control @error('planForm.name') is-invalid @enderror" id="planName" placeholder="Enter plan name">
                    @error('planForm.name')
                        <div class="invalid-feedback">{{ $message }}</div>
                    @enderror
                </div>

                <div class="col-12">
                    <label for="planDescription" class="form-label">Description</label>
                    <textarea wire:model="planForm.description" class="form-control @error('planForm.description') is-invalid @enderror" id="planDescription" rows="3" placeholder="Enter plan description"></textarea>
                    @error('planForm.description')
                        <div class="invalid-feedback">{{ $message }}</div>
                    @enderror
                </div>

                <div class="col-md-6">
                    <label for="startDate" class="form-label">Start Date <span class="text-danger">*</span></label>
                    <input type="date" wire:model="planForm.start_date" class="form-control @error('planForm.start_date') is-invalid @enderror" id="startDate">
                    @error('planForm.start_date')
                        <div class="invalid-feedback">{{ $message }}</div>
                    @enderror
                </div>

                <div class="col-md-6">
                    <label for="endDate" class="form-label">End Date <span class="text-danger">*</span></label>
                    <input type="date" wire:model="planForm.end_date" class="form-control @error('planForm.end_date') is-invalid @enderror" id="endDate">
                    @error('planForm.end_date')
                        <div class="invalid-feedback">{{ $message }}</div>
                    @enderror
                </div>

                <div class="col-md-6">
                    <label for="periodType" class="form-label">Period Type <span class="text-danger">*</span></label>
                    <select wire:model="planForm.period_type" class="form-select @error('planForm.period_type') is-invalid @enderror" id="periodType">
                        <option value="">Select Period Type</option>
                        <option value="annual">Annual</option>
                        <option value="quarterly">Quarterly</option>
                        <option value="monthly">Monthly</option>
                    </select>
                    @error('planForm.period_type')
                        <div class="invalid-feedback">{{ $message }}</div>
                    @enderror
                </div>

                <div class="col-md-6">
                    <label for="status" class="form-label">Status <span class="text-danger">*</span></label>
                    <select wire:model="planForm.status" class="form-select @error('planForm.status') is-invalid @enderror" id="status">
                        <option value="draft">Draft</option>
                        <option value="active">Active</option>
                        <option value="approved">Approved</option>
                        <option value="completed">Completed</option>
                        <option value="cancelled">Cancelled</option>
                    </select>
                    @error('planForm.status')
                        <div class="invalid-feedback">{{ $message }}</div>
                    @enderror
                </div>
            </div>

            <x-slot name="footer">
                <button type="button" class="btn btn-secondary" data-bs-dismiss="modal">
                    <i class="fa-solid fa-times me-1"></i> Cancel
                </button>
                <button type="submit" class="btn btn-primary">
                    <i class="fa-solid fa-save me-1"></i> Save Plan
                </button>
            </x-slot>
        </form>
    </x-forms.modal>

    <!-- Items Management Modal -->
    <x-forms.modal id="itemsModal" title="Manage Plan Items" size="modal-xl" :centered="true">
        <div class="row g-3">
            <!-- Items List -->
            <div class="col-12">
                <div class="d-flex justify-content-between align-items-center mb-3">
                    <h6 class="mb-0">Plan Items</h6>
                    <button class="btn btn-sm btn-primary" wire:click="addNewItem">
                        <i class="fa-solid fa-plus me-1"></i> Add Item
                    </button>
                </div>

                <div class="table-responsive">
                    <table class="table table-sm table-hover align-middle">
                        <thead class="table-light">
                            <tr>
                                <th>Item Name</th>
                                <th>Description</th>
                                <th class="text-center">Weight (%)</th>
                                <th class="text-center">Target</th>
                                <th class="text-end">Actions</th>
                            </tr>
                        </thead>
                        <tbody>
                            @forelse ($planItems ?? [] as $index => $item)
                                <tr>
                                    <td>{{ $item->name ?? 'N/A' }}</td>
                                    <td>{{ Str::limit($item->description ?? '', 40) }}</td>
                                    <td class="text-center">
                                        <span class="badge bg-primary">{{ $item->weight ?? 0 }}%</span>
                                    </td>
                                    <td class="text-center">{{ $item->target ?? '-' }}</td>
                                    <td>
                                        <div class="d-flex gap-1 justify-content-end">
                                            <button wire:click="editItem('{{ $item->id ?? '' }}')" class="btn btn-sm btn-ghost-secondary" title="Edit">
                                                <i class="fa-solid fa-pen"></i>
                                            </button>
                                            <button wire:click="deleteItem('{{ $item->id ?? '' }}')" class="btn btn-sm btn-ghost-danger" title="Delete">
                                                <i class="fa-solid fa-trash"></i>
                                            </button>
                                        </div>
                                    </td>
                                </tr>
                            @empty
                                <tr>
                                    <td colspan="5" class="text-center py-4 text-muted">
                                        <i class="fa-solid fa-inbox fa-2x mb-2 d-block"></i>
                                        No items added yet
                                    </td>
                                </tr>
                            @endforelse
                        </tbody>
                    </table>
                </div>

                <!-- Item Form (shown when adding/editing) -->
                @if ($showItemForm ?? false)
                    <div class="border-top pt-3 mt-3">
                        <h6 class="mb-3">{{ $editingItemId ?? false ? 'Edit Item' : 'Add New Item' }}</h6>
                        <form wire:submit.prevent="saveItem">
                            <div class="row g-3">
                                <div class="col-md-6">
                                    <label class="form-label">Item Name <span class="text-danger">*</span></label>
                                    <input type="text" wire:model="itemForm.name" class="form-control @error('itemForm.name') is-invalid @enderror" placeholder="Enter item name">
                                    @error('itemForm.name')
                                        <div class="invalid-feedback">{{ $message }}</div>
                                    @enderror
                                </div>

                                <div class="col-md-6">
                                    <label class="form-label">Weight (%) <span class="text-danger">*</span></label>
                                    <input type="number" wire:model="itemForm.weight" class="form-control @error('itemForm.weight') is-invalid @enderror" placeholder="0" min="0" max="100" step="0.01">
                                    @error('itemForm.weight')
                                        <div class="invalid-feedback">{{ $message }}</div>
                                    @enderror
                                </div>

                                <div class="col-12">
                                    <label class="form-label">Description</label>
                                    <textarea wire:model="itemForm.description" class="form-control @error('itemForm.description') is-invalid @enderror" rows="2" placeholder="Enter item description"></textarea>
                                    @error('itemForm.description')
                                        <div class="invalid-feedback">{{ $message }}</div>
                                    @enderror
                                </div>

                                <div class="col-md-6">
                                    <label class="form-label">Target</label>
                                    <input type="text" wire:model="itemForm.target" class="form-control @error('itemForm.target') is-invalid @enderror" placeholder="Enter target value">
                                    @error('itemForm.target')
                                        <div class="invalid-feedback">{{ $message }}</div>
                                    @enderror
                                </div>

                                <div class="col-md-6">
                                    <label class="form-label">Measurement Unit</label>
                                    <input type="text" wire:model="itemForm.unit" class="form-control @error('itemForm.unit') is-invalid @enderror" placeholder="e.g., %, units, days">
                                    @error('itemForm.unit')
                                        <div class="invalid-feedback">{{ $message }}</div>
                                    @enderror
                                </div>

                                <div class="col-12">
                                    <div class="d-flex gap-2">
                                        <button type="submit" class="btn btn-primary">
                                            <i class="fa-solid fa-save me-1"></i> Save Item
                                        </button>
                                        <button type="button" wire:click="cancelItemForm" class="btn btn-secondary">
                                            Cancel
                                        </button>
                                    </div>
                                </div>
                            </div>
                        </form>
                    </div>
                @endif
            </div>
        </div>

        <x-slot name="footer">
            <button type="button" class="btn btn-secondary" data-bs-dismiss="modal">
                Close
            </button>
        </x-slot>
    </x-forms.modal>

    <!-- Loading Indicator -->
    <div wire:loading class="position-fixed top-50 start-50 translate-middle" style="z-index: 9999;">
        <div class="spinner-border text-primary" role="status">
            <span class="visually-hidden">Loading...</span>
        </div>
    </div>
</div>
