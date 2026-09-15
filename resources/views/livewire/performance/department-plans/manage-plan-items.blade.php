<div class="custom-container">

    <x-pages.breadcrumn title="DEPARTMENT PLAN ITEMS" :breadcrumbs="[
        ['label' => 'Home', 'url' => route('dashboard')],
        ['label' => 'Performance Management', 'url' => '#'],
        ['label' => 'Department Plans', 'url' => route('performance.dept.plans')],
        ['label' => $plan->plan_name, 'url' => '#'],
    ]">
        @if (in_array($plan->status, ['draft', 'active']))
            <button class='btn btn-primary d-md-flex align-items-center gap-2' wire:click="createItem">
                <i class="fa-solid fa-plus"></i> ADD ITEM
            </button>
        @endif
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

    <!-- Plan Info Card -->
    <div class="card card-lg mb-4">
        <div class="card-body">
            <div class="row align-items-center">
                <div class="col-md-8">
                    <h5 class="mb-1">{{ $plan->plan_name }}</h5>
                    <p class="text-muted mb-2">{{ $plan->description ?? 'No description' }}</p>
                    <div class="d-flex gap-3 flex-wrap">
                        <span class="badge bg-{{ $plan->status === 'active' ? 'primary' : ($plan->status === 'draft' ? 'secondary' : 'success') }}">
                            {{ ucfirst($plan->status) }}
                        </span>
                        <small class="text-muted">
                            <i class="fa-solid fa-building me-1"></i>
                            {{ $plan->department->name ?? 'N/A' }}
                        </small>
                        @if ($plan->organizationalPlan)
                            <small class="text-muted">
                                <i class="fa-solid fa-link me-1"></i>
                                Linked to: {{ $plan->organizationalPlan->plan_name }}
                            </small>
                        @endif
                    </div>
                </div>
                <div class="col-md-4 text-md-end mt-3 mt-md-0">
                    <div class="d-flex flex-column align-items-md-end">
                        <span class="text-muted small">Total Weight</span>
                        <h3 class="mb-0 {{ $totalWeight == 100 ? 'text-success' : ($totalWeight > 100 ? 'text-danger' : 'text-warning') }}">
                            {{ number_format($totalWeight, 2) }}%
                        </h3>
                        @if ($totalWeight != 100)
                            <small class="text-{{ $totalWeight > 100 ? 'danger' : 'warning' }}">
                                {{ $totalWeight > 100 ? 'Exceeds 100%' : 'Should sum to 100%' }}
                            </small>
                        @endif
                    </div>
                </div>
            </div>
        </div>
    </div>

    <!-- Items List Card -->
    <div class="card card-lg">
        <div class="card-header border-bottom">
            <div class="d-flex justify-content-between align-items-center">
                <h5 class="mb-0">
                    <i class="fa-solid fa-list-check me-2"></i>
                    Department Plan Items ({{ $items->count() }})
                </h5>
                <a href="{{ route('performance.dept.plans') }}" class="btn btn-outline-secondary btn-sm">
                    <i class="fa-solid fa-arrow-left me-1"></i> Back to Plans
                </a>
            </div>
        </div>

        <div class="table-responsive">
            <table class="table table-hover align-middle mb-0">
                <thead class="table-light">
                    <tr>
                        <th style="width: 50px;">#</th>
                        <th>Item Name</th>
                        <th>Source Item</th>
                        <th class="text-center">Weight</th>
                        <th class="text-center">Target</th>
                        <th class="text-center">Distributed</th>
                        <th class="text-center">Status</th>
                        <th class="text-end" style="width: 150px;">Actions</th>
                    </tr>
                </thead>
                <tbody>
                    @forelse ($items as $index => $item)
                        <tr wire:key="item-{{ $item->id }}">
                            <td class="text-muted">{{ $item->display_order }}</td>
                            <td>
                                <div class="d-flex flex-column">
                                    <span class="fw-semibold">{{ $item->item_name }}</span>
                                    @if ($item->description)
                                        <small class="text-muted">{{ Str::limit($item->description, 50) }}</small>
                                    @endif
                                    @if ($item->department_specific_notes)
                                        <small class="text-info">
                                            <i class="fa-solid fa-sticky-note me-1"></i>
                                            {{ Str::limit($item->department_specific_notes, 30) }}
                                        </small>
                                    @endif
                                </div>
                            </td>
                            <td>
                                @if ($item->organizationalPlanItem)
                                    <span class="badge bg-light text-dark">
                                        <i class="fa-solid fa-sitemap me-1"></i>
                                        {{ Str::limit($item->organizationalPlanItem->item_name, 25) }}
                                    </span>
                                @else
                                    <span class="text-muted">-</span>
                                @endif
                            </td>
                            <td class="text-center">
                                <span class="fw-semibold">{{ number_format($item->weight, 2) }}%</span>
                            </td>
                            <td class="text-center">
                                @if ($item->target_value)
                                    {{ number_format($item->target_value, 2) }} {{ $item->target_unit }}
                                @else
                                    <span class="text-muted">-</span>
                                @endif
                            </td>
                            <td class="text-center">
                                @if ($item->employee_plan_items_count > 0)
                                    <span class="badge bg-success-subtle text-success">
                                        {{ $item->employee_plan_items_count }} employee(s)
                                    </span>
                                @else
                                    <span class="badge bg-secondary-subtle text-secondary">Not distributed</span>
                                @endif
                            </td>
                            <td class="text-center">
                                <button wire:click="toggleActive('{{ $item->id }}')"
                                    class="badge border-0 bg-{{ $item->is_active ? 'success' : 'danger' }}-subtle text-{{ $item->is_active ? 'success' : 'danger' }}"
                                    style="cursor: pointer;">
                                    {{ $item->is_active ? 'Active' : 'Inactive' }}
                                </button>
                            </td>
                            <td>
                                <div class="d-flex gap-1 justify-content-end">
                                    <button wire:click="editItem('{{ $item->id }}')"
                                        class="btn btn-sm btn-ghost-secondary rounded-circle"
                                        title="Edit">
                                        <i class="fa-solid fa-pen-to-square"></i>
                                    </button>
                                    @if ($item->employee_plan_items_count == 0)
                                        <button wire:click="deleteItem('{{ $item->id }}')"
                                            wire:confirm="Are you sure you want to delete this item?"
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
                                <div class="d-flex flex-column align-items-center">
                                    <i class="fa-solid fa-clipboard-list text-muted mb-3" style="font-size: 48px;"></i>
                                    <h5 class="text-muted">No Items Found</h5>
                                    <p class="text-muted">Add items from the organizational plan or create custom items</p>
                                    @if (in_array($plan->status, ['draft', 'active']))
                                        <button class="btn btn-primary mt-2" wire:click="createItem">
                                            <i class="fa-solid fa-plus me-1"></i> Add First Item
                                        </button>
                                    @endif
                                </div>
                            </td>
                        </tr>
                    @endforelse
                </tbody>
            </table>
        </div>
    </div>

    <!-- Create/Edit Item Modal -->
    @if ($showModal)
        <div class="modal fade show d-block" tabindex="-1" style="background-color: rgba(0,0,0,0.5);">
            <div class="modal-dialog modal-lg modal-dialog-centered modal-dialog-scrollable">
                <div class="modal-content">
                    <div class="modal-header">
                        <h5 class="modal-title">
                            <i class="fa-solid fa-clipboard-list me-2"></i>
                            {{ $editingItemId ? 'Edit Department Plan Item' : 'Add Department Plan Item' }}
                        </h5>
                        <button type="button" class="btn-close" wire:click="closeModal"></button>
                    </div>
                    <form wire:submit.prevent="saveItem">
                        <div class="modal-body" style="max-height: 70vh; overflow-y: auto;">
                            <div class="row g-3">
                                <div class="col-12">
                                    <label for="orgPlanItem" class="form-label">Organizational Plan Item <span class="text-danger">*</span></label>
                                    <select wire:model.live="itemForm.organizational_plan_item_id" class="form-select @error('itemForm.organizational_plan_item_id') is-invalid @enderror" id="orgPlanItem">
                                        <option value="">Select Source Item</option>
                                        @if ($editingItemId)
                                            @php
                                                $currentItem = \App\Models\DepartmentPlanItem::find($editingItemId);
                                            @endphp
                                            @if ($currentItem && $currentItem->organizationalPlanItem)
                                                <option value="{{ $currentItem->organizational_plan_item_id }}" selected>
                                                    {{ $currentItem->organizationalPlanItem->item_name }} (Current)
                                                </option>
                                            @endif
                                        @endif
                                        @foreach ($availableOrgItems as $orgItem)
                                            <option value="{{ $orgItem->id }}">
                                                {{ $orgItem->item_name }} ({{ number_format($orgItem->weight, 2) }}%)
                                            </option>
                                        @endforeach
                                    </select>
                                    @error('itemForm.organizational_plan_item_id')
                                        <div class="invalid-feedback">{{ $message }}</div>
                                    @enderror
                                    <div class="form-text">Select an item from the organizational plan to link to</div>
                                </div>

                                <div class="col-12">
                                    <label for="itemName" class="form-label">Item Name <span class="text-danger">*</span></label>
                                    <input type="text" wire:model="itemForm.item_name" class="form-control @error('itemForm.item_name') is-invalid @enderror" id="itemName" placeholder="Enter item name">
                                    @error('itemForm.item_name')
                                        <div class="invalid-feedback">{{ $message }}</div>
                                    @enderror
                                </div>

                                <div class="col-12">
                                    <label for="description" class="form-label">Description</label>
                                    <textarea wire:model="itemForm.description" class="form-control @error('itemForm.description') is-invalid @enderror" id="description" rows="2" placeholder="Enter item description"></textarea>
                                    @error('itemForm.description')
                                        <div class="invalid-feedback">{{ $message }}</div>
                                    @enderror
                                </div>

                                <div class="col-md-4">
                                    <label for="weight" class="form-label">Weight (%) <span class="text-danger">*</span></label>
                                    <input type="number" wire:model="itemForm.weight" class="form-control @error('itemForm.weight') is-invalid @enderror" id="weight" step="0.01" min="0" max="100" placeholder="0.00">
                                    @error('itemForm.weight')
                                        <div class="invalid-feedback">{{ $message }}</div>
                                    @enderror
                                </div>

                                <div class="col-md-4">
                                    <label for="targetValue" class="form-label">Target Value</label>
                                    <input type="number" wire:model="itemForm.target_value" class="form-control @error('itemForm.target_value') is-invalid @enderror" id="targetValue" step="0.01" placeholder="0.00">
                                    @error('itemForm.target_value')
                                        <div class="invalid-feedback">{{ $message }}</div>
                                    @enderror
                                </div>

                                <div class="col-md-4">
                                    <label for="targetUnit" class="form-label">Target Unit</label>
                                    <input type="text" wire:model="itemForm.target_unit" class="form-control @error('itemForm.target_unit') is-invalid @enderror" id="targetUnit" placeholder="e.g., %, count, TZS">
                                    @error('itemForm.target_unit')
                                        <div class="invalid-feedback">{{ $message }}</div>
                                    @enderror
                                </div>

                                <div class="col-md-4">
                                    <label for="minAcceptable" class="form-label">Min Acceptable</label>
                                    <input type="number" wire:model="itemForm.min_acceptable" class="form-control @error('itemForm.min_acceptable') is-invalid @enderror" id="minAcceptable" step="0.01" placeholder="0.00">
                                    @error('itemForm.min_acceptable')
                                        <div class="invalid-feedback">{{ $message }}</div>
                                    @enderror
                                </div>

                                <div class="col-md-4">
                                    <label for="maxPossible" class="form-label">Max Possible</label>
                                    <input type="number" wire:model="itemForm.max_possible" class="form-control @error('itemForm.max_possible') is-invalid @enderror" id="maxPossible" step="0.01" placeholder="0.00">
                                    @error('itemForm.max_possible')
                                        <div class="invalid-feedback">{{ $message }}</div>
                                    @enderror
                                </div>

                                <div class="col-md-4">
                                    <label for="displayOrder" class="form-label">Display Order</label>
                                    <input type="number" wire:model="itemForm.display_order" class="form-control @error('itemForm.display_order') is-invalid @enderror" id="displayOrder" min="0" placeholder="0">
                                    @error('itemForm.display_order')
                                        <div class="invalid-feedback">{{ $message }}</div>
                                    @enderror
                                </div>

                                <div class="col-md-6">
                                    <label for="isActive" class="form-label">Status</label>
                                    <select wire:model="itemForm.is_active" class="form-select @error('itemForm.is_active') is-invalid @enderror" id="isActive">
                                        <option value="1">Active</option>
                                        <option value="0">Inactive</option>
                                    </select>
                                    @error('itemForm.is_active')
                                        <div class="invalid-feedback">{{ $message }}</div>
                                    @enderror
                                </div>

                                <div class="col-12">
                                    <label for="departmentNotes" class="form-label">Department Specific Notes</label>
                                    <textarea wire:model="itemForm.department_specific_notes" class="form-control @error('itemForm.department_specific_notes') is-invalid @enderror" id="departmentNotes" rows="2" placeholder="Add any department-specific notes or instructions..."></textarea>
                                    @error('itemForm.department_specific_notes')
                                        <div class="invalid-feedback">{{ $message }}</div>
                                    @enderror
                                </div>
                            </div>
                        </div>
                        <div class="modal-footer">
                            <button type="button" class="btn btn-secondary" wire:click="closeModal">
                                <i class="fa-solid fa-times me-1"></i> Cancel
                            </button>
                            <button type="submit" class="btn btn-primary">
                                <i class="fa-solid fa-save me-1"></i> {{ $editingItemId ? 'Update Item' : 'Save Item' }}
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
