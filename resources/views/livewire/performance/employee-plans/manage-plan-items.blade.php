<div class="custom-container">

    <x-pages.breadcrumn title="EMPLOYEE PLAN ITEMS" :breadcrumbs="[
        ['label' => 'Home', 'url' => route('dashboard')],
        ['label' => 'Performance Management', 'url' => '#'],
        ['label' => 'Employee Plans', 'url' => route('performance.employee.plans')],
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
                <div class="col-md-6">
                    <h5 class="mb-1">{{ $plan->plan_name }}</h5>
                    <p class="text-muted mb-2">{{ $plan->description ?? 'No description' }}</p>
                    <div class="d-flex gap-3 flex-wrap">
                        <span class="badge bg-{{ $plan->status === 'active' ? 'primary' : ($plan->status === 'draft' ? 'secondary' : 'success') }}">
                            {{ ucfirst($plan->status) }}
                        </span>
                        <small class="text-muted">
                            <i class="fa-solid fa-user me-1"></i>
                            {{ $plan->employee->first_name ?? '' }} {{ $plan->employee->last_name ?? '' }}
                        </small>
                        @if ($plan->departmentPlan)
                            <small class="text-muted">
                                <i class="fa-solid fa-building me-1"></i>
                                {{ $plan->departmentPlan->department->name ?? 'N/A' }}
                            </small>
                        @endif
                    </div>
                </div>
                <div class="col-md-6 mt-3 mt-md-0">
                    <div class="row text-center">
                        <div class="col-4">
                            <span class="text-muted small d-block">Total Weight</span>
                            <h4 class="mb-0 {{ $totalWeight == 100 ? 'text-success' : ($totalWeight > 100 ? 'text-danger' : 'text-warning') }}">
                                {{ number_format($totalWeight, 1) }}%
                            </h4>
                        </div>
                        <div class="col-4">
                            <span class="text-muted small d-block">Avg Score</span>
                            <h4 class="mb-0 text-info">
                                {{ $averageScore ? number_format($averageScore, 1) : '-' }}
                            </h4>
                        </div>
                        <div class="col-4">
                            <span class="text-muted small d-block">Weighted Score</span>
                            <h4 class="mb-0 {{ $weightedScore >= 80 ? 'text-success' : ($weightedScore >= 60 ? 'text-primary' : ($weightedScore >= 40 ? 'text-warning' : 'text-danger')) }}">
                                {{ number_format($weightedScore, 1) }}%
                            </h4>
                        </div>
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
                    Employee Plan Items ({{ $items->count() }})
                </h5>
                <a href="{{ route('performance.employee.plans') }}" class="btn btn-outline-secondary btn-sm">
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
                        <th class="text-center">Weight</th>
                        <th class="text-center">Target</th>
                        <th class="text-center">Achievement</th>
                        <th class="text-center">Score</th>
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
                                    @if ($item->departmentPlanItem)
                                        <small class="text-info">
                                            <i class="fa-solid fa-link me-1"></i>
                                            Linked to dept plan
                                        </small>
                                    @endif
                                </div>
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
                                @if ($item->actual_achievement !== null)
                                    <span class="fw-semibold">{{ number_format($item->actual_achievement, 2) }} {{ $item->target_unit }}</span>
                                @else
                                    <span class="text-muted">Not recorded</span>
                                @endif
                            </td>
                            <td class="text-center">
                                @if ($item->score !== null)
                                    @php
                                        $scoreColor = $item->score >= 80 ? 'success' : ($item->score >= 60 ? 'primary' : ($item->score >= 40 ? 'warning' : 'danger'));
                                    @endphp
                                    <span class="badge bg-{{ $scoreColor }}-subtle text-{{ $scoreColor }} fs-6 px-3 py-2">
                                        {{ number_format($item->score, 1) }}%
                                    </span>
                                @else
                                    <span class="badge bg-secondary-subtle text-secondary">Not scored</span>
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
                                        title="Edit / Score">
                                        <i class="fa-solid fa-pen-to-square"></i>
                                    </button>
                                    @if ($item->score === null)
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
                                    <p class="text-muted">Add items from the department plan or create custom items</p>
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
                            {{ $editingItemId ? 'Edit / Score Plan Item' : 'Add Plan Item' }}
                        </h5>
                        <button type="button" class="btn-close" wire:click="closeModal"></button>
                    </div>
                    <form wire:submit.prevent="saveItem">
                        <div class="modal-body" style="max-height: 70vh; overflow-y: auto;">
                            <div class="row g-3">
                                @if ($plan->departmentPlan)
                                    <div class="col-12">
                                        <label for="deptPlanItem" class="form-label">Department Plan Item</label>
                                        <select wire:model.live="itemForm.department_plan_item_id" class="form-select @error('itemForm.department_plan_item_id') is-invalid @enderror" id="deptPlanItem">
                                            <option value="">Select Source Item (Optional)</option>
                                            @if ($editingItemId)
                                                @php
                                                    $currentItem = \App\Models\EmployeePlanItem::find($editingItemId);
                                                @endphp
                                                @if ($currentItem && $currentItem->departmentPlanItem)
                                                    <option value="{{ $currentItem->department_plan_item_id }}" selected>
                                                        {{ $currentItem->departmentPlanItem->item_name }} (Current)
                                                    </option>
                                                @endif
                                            @endif
                                            @foreach ($availableDeptItems as $deptItem)
                                                <option value="{{ $deptItem->id }}">
                                                    {{ $deptItem->item_name }} ({{ number_format($deptItem->weight, 2) }}%)
                                                </option>
                                            @endforeach
                                        </select>
                                        @error('itemForm.department_plan_item_id')
                                            <div class="invalid-feedback">{{ $message }}</div>
                                        @enderror
                                        <div class="form-text">Link to a department plan item or leave empty for custom item</div>
                                    </div>
                                @endif

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

                                <!-- Achievement Section -->
                                <div class="col-12">
                                    <hr class="my-2">
                                    <h6 class="text-primary mb-3">
                                        <i class="fa-solid fa-chart-line me-1"></i> Achievement & Scoring
                                    </h6>
                                </div>

                                <div class="col-md-4">
                                    <label for="actualAchievement" class="form-label">Actual Achievement</label>
                                    <input type="number" wire:model="itemForm.actual_achievement" class="form-control @error('itemForm.actual_achievement') is-invalid @enderror" id="actualAchievement" step="0.01" placeholder="0.00">
                                    @error('itemForm.actual_achievement')
                                        <div class="invalid-feedback">{{ $message }}</div>
                                    @enderror
                                </div>

                                <div class="col-md-4">
                                    <label for="score" class="form-label">Score (0-100)</label>
                                    <input type="number" wire:model="itemForm.score" class="form-control @error('itemForm.score') is-invalid @enderror" id="score" step="0.01" min="0" max="100" placeholder="0.00">
                                    @error('itemForm.score')
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

                                <div class="col-12">
                                    <label for="achievementNotes" class="form-label">Achievement Notes</label>
                                    <textarea wire:model="itemForm.achievement_notes" class="form-control @error('itemForm.achievement_notes') is-invalid @enderror" id="achievementNotes" rows="2" placeholder="Notes about the achievement..."></textarea>
                                    @error('itemForm.achievement_notes')
                                        <div class="invalid-feedback">{{ $message }}</div>
                                    @enderror
                                </div>

                                <div class="col-md-6">
                                    <label for="employeeComments" class="form-label">Employee Comments</label>
                                    <textarea wire:model="itemForm.employee_comments" class="form-control @error('itemForm.employee_comments') is-invalid @enderror" id="employeeComments" rows="2" placeholder="Employee self-assessment comments..."></textarea>
                                    @error('itemForm.employee_comments')
                                        <div class="invalid-feedback">{{ $message }}</div>
                                    @enderror
                                </div>

                                <div class="col-md-6">
                                    <label for="supervisorComments" class="form-label">Supervisor Comments</label>
                                    <textarea wire:model="itemForm.supervisor_comments" class="form-control @error('itemForm.supervisor_comments') is-invalid @enderror" id="supervisorComments" rows="2" placeholder="Supervisor review comments..."></textarea>
                                    @error('itemForm.supervisor_comments')
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
