<div>
    <div class="row mb-4">
        <div class="col-12">
            <div class="d-md-flex justify-content-between align-items-center">
                <div>
                    <h4 class="mb-1">My Performance Planning</h4>
                    <p class="text-muted mb-0">Set your goals based on department objectives</p>
                </div>
            </div>
        </div>
    </div>

    @if(!$hasEmployeeRecord)
        <div class="alert alert-warning">
            <i class="fa-solid fa-exclamation-triangle me-2"></i>
            Your user account is not linked to an employee record. Please contact HR.
        </div>
    @else
        @if(session()->has('success'))
            <div class="alert alert-success alert-dismissible fade show" role="alert">
                {{ session('success') }}
                <button type="button" class="btn-close" data-bs-dismiss="alert"></button>
            </div>
        @endif

        <div class="row">
            {{-- Department Goals Section --}}
            <div class="col-lg-4 mb-4">
                <div class="card h-100">
                    <div class="card-header bg-primary text-white">
                        <h6 class="mb-0"><i class="fa-solid fa-building me-2"></i>Department Goals</h6>
                    </div>
                    <div class="card-body">
                        @if($departmentPlans->count() > 0)
                            <p class="text-muted small mb-3">Select a department plan to view its goals:</p>
                            <div class="list-group">
                                @foreach($departmentPlans as $deptPlan)
                                    <button type="button"
                                            class="list-group-item list-group-item-action {{ $selectedDepartmentPlan && $selectedDepartmentPlan->id === $deptPlan->id ? 'active' : '' }}"
                                            wire:click="selectDepartmentPlan('{{ $deptPlan->id }}')">
                                        <div class="d-flex justify-content-between align-items-center">
                                            <div>
                                                <strong>{{ $deptPlan->plan_name }}</strong>
                                                <br><small class="text-muted">{{ $deptPlan->departmentPlanItems->count() }} goals</small>
                                            </div>
                                            <span class="badge bg-{{ $deptPlan->status === 'active' ? 'success' : 'secondary' }}">
                                                {{ ucfirst($deptPlan->status) }}
                                            </span>
                                        </div>
                                    </button>
                                @endforeach
                            </div>
                        @else
                            <div class="text-center text-muted py-4">
                                <i class="fa-solid fa-folder-open fa-2x mb-2"></i>
                                <p class="mb-0">No active department plans available</p>
                            </div>
                        @endif
                    </div>
                </div>

                {{-- Selected Department Plan Items --}}
                @if($selectedDepartmentPlan)
                    <div class="card mt-3">
                        <div class="card-header bg-info text-white">
                            <h6 class="mb-0"><i class="fa-solid fa-bullseye me-2"></i>{{ $selectedDepartmentPlan->plan_name }} - Goals</h6>
                        </div>
                        <div class="card-body p-0">
                            @if($departmentPlanItems->count() > 0)
                                <div class="table-responsive">
                                    <table class="table table-sm mb-0">
                                        <thead class="table-light">
                                            <tr>
                                                <th>Goal</th>
                                                <th class="text-center">Weight</th>
                                                <th class="text-center">Target</th>
                                            </tr>
                                        </thead>
                                        <tbody>
                                            @foreach($departmentPlanItems as $item)
                                                <tr>
                                                    <td>
                                                        <strong>{{ $item->item_name }}</strong>
                                                        @if($item->description)
                                                            <br><small class="text-muted">{{ Str::limit($item->description, 50) }}</small>
                                                        @endif
                                                    </td>
                                                    <td class="text-center">
                                                        <span class="badge bg-primary">{{ $item->weight }}%</span>
                                                    </td>
                                                    <td class="text-center">
                                                        {{ number_format($item->target_value ?? 0) }} {{ $item->target_unit }}
                                                    </td>
                                                </tr>
                                            @endforeach
                                        </tbody>
                                    </table>
                                </div>
                            @else
                                <div class="text-center text-muted py-3">
                                    <p class="mb-0">No goals defined for this plan</p>
                                </div>
                            @endif
                        </div>
                    </div>
                @endif
            </div>

            {{-- My Plans Section --}}
            <div class="col-lg-8">
                <div class="card">
                    <div class="card-header d-flex justify-content-between align-items-center">
                        <h6 class="mb-0"><i class="fa-solid fa-clipboard-list me-2"></i>My Plans</h6>
                        <button class="btn btn-primary btn-sm" wire:click="openPlanModal('create')">
                            <i class="fa-solid fa-plus me-1"></i> Create Plan
                        </button>
                    </div>
                    <div class="card-body">
                        @if($myPlans->count() > 0)
                            <div class="accordion" id="myPlansAccordion">
                                @foreach($myPlans as $index => $plan)
                                    <div class="accordion-item">
                                        <h2 class="accordion-header">
                                            <button class="accordion-button {{ $index > 0 ? 'collapsed' : '' }}" type="button"
                                                    data-bs-toggle="collapse" data-bs-target="#plan{{ $plan->id }}">
                                                <div class="d-flex justify-content-between align-items-center w-100 me-3">
                                                    <div>
                                                        <strong>{{ $plan->plan_name }}</strong>
                                                        <span class="badge bg-{{ $plan->status === 'active' ? 'success' : ($plan->status === 'draft' ? 'warning' : 'secondary') }} ms-2">
                                                            {{ ucfirst($plan->status) }}
                                                        </span>
                                                    </div>
                                                    <small class="text-muted">{{ $plan->employeePlanItems->count() }} goals</small>
                                                </div>
                                            </button>
                                        </h2>
                                        <div id="plan{{ $plan->id }}" class="accordion-collapse collapse {{ $index === 0 ? 'show' : '' }}"
                                             data-bs-parent="#myPlansAccordion">
                                            <div class="accordion-body">
                                                @if($plan->description)
                                                    <p class="text-muted">{{ $plan->description }}</p>
                                                @endif

                                                <div class="d-flex gap-2 mb-3">
                                                    @if($plan->status === 'draft')
                                                        <button class="btn btn-sm btn-outline-primary" wire:click="openPlanModal('edit', '{{ $plan->id }}')">
                                                            <i class="fa-solid fa-pencil"></i> Edit Plan
                                                        </button>
                                                        <button class="btn btn-sm btn-success" wire:click="submitPlanForReview('{{ $plan->id }}')"
                                                                wire:confirm="Submit this plan for review?">
                                                            <i class="fa-solid fa-paper-plane"></i> Submit for Review
                                                        </button>
                                                        <button class="btn btn-sm btn-danger" wire:click="deletePlan('{{ $plan->id }}')"
                                                                wire:confirm="Delete this plan and all its goals?">
                                                            <i class="fa-solid fa-trash"></i>
                                                        </button>
                                                    @endif
                                                    <button class="btn btn-sm btn-primary" wire:click="openItemModal('create', '{{ $plan->id }}')">
                                                        <i class="fa-solid fa-plus"></i> Add Goal
                                                    </button>
                                                </div>

                                                {{-- Plan Items/Goals --}}
                                                @if($plan->employeePlanItems->count() > 0)
                                                    <div class="table-responsive">
                                                        <table class="table table-sm table-hover">
                                                            <thead class="table-light">
                                                                <tr>
                                                                    <th>#</th>
                                                                    <th>Goal</th>
                                                                    <th class="text-center">Weight</th>
                                                                    <th class="text-center">Target</th>
                                                                    <th class="text-center">Progress</th>
                                                                    <th>Actions</th>
                                                                </tr>
                                                            </thead>
                                                            <tbody>
                                                                @foreach($plan->employeePlanItems as $i => $item)
                                                                    <tr>
                                                                        <td>{{ $i + 1 }}</td>
                                                                        <td>
                                                                            <strong>{{ $item->item_name }}</strong>
                                                                            @if($item->departmentPlanItem)
                                                                                <br><small class="text-info"><i class="fa-solid fa-link"></i> Linked to dept goal</small>
                                                                            @endif
                                                                        </td>
                                                                        <td class="text-center">
                                                                            <span class="badge bg-primary">{{ $item->weight }}%</span>
                                                                        </td>
                                                                        <td class="text-center">
                                                                            {{ number_format($item->target_value ?? 0) }} {{ $item->target_unit }}
                                                                        </td>
                                                                        <td class="text-center">
                                                                            <div class="progress" style="height: 20px;">
                                                                                <div class="progress-bar {{ $item->progress_percentage >= 100 ? 'bg-success' : 'bg-info' }}"
                                                                                     style="width: {{ $item->progress_percentage }}%">
                                                                                    {{ number_format($item->progress_percentage, 0) }}%
                                                                                </div>
                                                                            </div>
                                                                        </td>
                                                                        <td>
                                                                            <button class="btn btn-sm btn-outline-primary"
                                                                                    wire:click="openItemModal('edit', '{{ $plan->id }}', '{{ $item->id }}')">
                                                                                <i class="fa-solid fa-pencil"></i>
                                                                            </button>
                                                                            <button class="btn btn-sm btn-outline-danger"
                                                                                    wire:click="deleteItem('{{ $item->id }}')"
                                                                                    wire:confirm="Delete this goal?">
                                                                                <i class="fa-solid fa-trash"></i>
                                                                            </button>
                                                                        </td>
                                                                    </tr>
                                                                @endforeach
                                                            </tbody>
                                                            <tfoot>
                                                                <tr class="table-secondary">
                                                                    <th colspan="2">Total Weight</th>
                                                                    <th class="text-center">
                                                                        @php $totalWeight = $plan->employeePlanItems->sum('weight'); @endphp
                                                                        <span class="badge {{ $totalWeight == 100 ? 'bg-success' : 'bg-warning' }}">
                                                                            {{ $totalWeight }}%
                                                                        </span>
                                                                    </th>
                                                                    <th colspan="3"></th>
                                                                </tr>
                                                            </tfoot>
                                                        </table>
                                                    </div>
                                                @else
                                                    <div class="text-center text-muted py-3">
                                                        <i class="fa-solid fa-bullseye fa-2x mb-2"></i>
                                                        <p class="mb-0">No goals added yet. Click "Add Goal" to start.</p>
                                                    </div>
                                                @endif
                                            </div>
                                        </div>
                                    </div>
                                @endforeach
                            </div>
                        @else
                            <div class="text-center text-muted py-5">
                                <i class="fa-solid fa-clipboard-list fa-3x mb-3"></i>
                                <h5>No Plans Yet</h5>
                                <p>Create your first performance plan based on department goals.</p>
                                <button class="btn btn-primary" wire:click="openPlanModal('create')">
                                    <i class="fa-solid fa-plus me-1"></i> Create My First Plan
                                </button>
                            </div>
                        @endif
                    </div>
                </div>
            </div>
        </div>

        {{-- Plan Modal --}}
        @if($showPlanModal)
            <div class="modal fade show d-block" tabindex="-1" style="background: rgba(0,0,0,0.5);">
                <div class="modal-dialog">
                    <div class="modal-content">
                        <div class="modal-header">
                            <h5 class="modal-title">{{ $modalMode === 'edit' ? 'Edit Plan' : 'Create New Plan' }}</h5>
                            <button type="button" class="btn-close" wire:click="$set('showPlanModal', false)"></button>
                        </div>
                        <form wire:submit="savePlan">
                            <div class="modal-body">
                                <div class="mb-3">
                                    <label class="form-label">Based on Department Plan <span class="text-danger">*</span></label>
                                    <select class="form-select @error('department_plan_id') is-invalid @enderror" wire:model="department_plan_id">
                                        <option value="">-- Select Department Plan --</option>
                                        @foreach($departmentPlans as $dp)
                                            <option value="{{ $dp->id }}">{{ $dp->plan_name }}</option>
                                        @endforeach
                                    </select>
                                    @error('department_plan_id') <div class="invalid-feedback">{{ $message }}</div> @enderror
                                </div>
                                <div class="mb-3">
                                    <label class="form-label">Plan Name <span class="text-danger">*</span></label>
                                    <input type="text" class="form-control @error('plan_name') is-invalid @enderror"
                                           wire:model="plan_name" placeholder="e.g., Q1 2026 Performance Plan">
                                    @error('plan_name') <div class="invalid-feedback">{{ $message }}</div> @enderror
                                </div>
                                <div class="mb-3">
                                    <label class="form-label">Description</label>
                                    <textarea class="form-control" wire:model="plan_description" rows="3"
                                              placeholder="Describe your plan objectives..."></textarea>
                                </div>
                            </div>
                            <div class="modal-footer">
                                <button type="button" class="btn btn-secondary" wire:click="$set('showPlanModal', false)">Cancel</button>
                                <button type="submit" class="btn btn-primary">
                                    <i class="fa-solid fa-save me-1"></i> {{ $modalMode === 'edit' ? 'Update' : 'Create' }} Plan
                                </button>
                            </div>
                        </form>
                    </div>
                </div>
            </div>
        @endif

        {{-- Item/Goal Modal --}}
        @if($showItemModal)
            <div class="modal fade show d-block" tabindex="-1" style="background: rgba(0,0,0,0.5);">
                <div class="modal-dialog modal-lg">
                    <div class="modal-content">
                        <div class="modal-header">
                            <h5 class="modal-title">{{ $modalMode === 'edit' ? 'Edit Goal' : 'Add Goal' }}</h5>
                            <button type="button" class="btn-close" wire:click="$set('showItemModal', false)"></button>
                        </div>
                        <form wire:submit="saveItem">
                            <div class="modal-body">
                                @php
                                    $deptItems = $this->getDepartmentPlanItemsForPlan($employee_plan_id);
                                @endphp
                                @if($deptItems->count() > 0)
                                    <div class="mb-3">
                                        <label class="form-label">Link to Department Goal (Optional)</label>
                                        <select class="form-select" wire:model.live="department_plan_item_id">
                                            <option value="">-- Create Custom Goal --</option>
                                            @foreach($deptItems as $di)
                                                <option value="{{ $di->id }}">{{ $di->item_name }} ({{ $di->weight }}%)</option>
                                            @endforeach
                                        </select>
                                        <small class="text-muted">Selecting a department goal will auto-fill the fields below</small>
                                    </div>
                                @endif

                                <div class="mb-3">
                                    <label class="form-label">Goal Name <span class="text-danger">*</span></label>
                                    <input type="text" class="form-control @error('item_name') is-invalid @enderror"
                                           wire:model="item_name" placeholder="e.g., Complete 50 customer calls">
                                    @error('item_name') <div class="invalid-feedback">{{ $message }}</div> @enderror
                                </div>

                                <div class="mb-3">
                                    <label class="form-label">Description</label>
                                    <textarea class="form-control" wire:model="item_description" rows="2"
                                              placeholder="Describe this goal..."></textarea>
                                </div>

                                <div class="row">
                                    <div class="col-md-4 mb-3">
                                        <label class="form-label">Weight (%) <span class="text-danger">*</span></label>
                                        <input type="number" class="form-control @error('weight') is-invalid @enderror"
                                               wire:model="weight" min="0" max="100" step="0.01">
                                        @error('weight') <div class="invalid-feedback">{{ $message }}</div> @enderror
                                    </div>
                                    <div class="col-md-4 mb-3">
                                        <label class="form-label">Target Value</label>
                                        <input type="number" class="form-control" wire:model="target_value" min="0" step="0.01">
                                    </div>
                                    <div class="col-md-4 mb-3">
                                        <label class="form-label">Unit</label>
                                        <input type="text" class="form-control" wire:model="target_unit" placeholder="e.g., calls, reports">
                                    </div>
                                </div>

                                <div class="row">
                                    <div class="col-md-6 mb-3">
                                        <label class="form-label">Minimum Acceptable</label>
                                        <input type="number" class="form-control" wire:model="min_acceptable" min="0" step="0.01">
                                    </div>
                                    <div class="col-md-6 mb-3">
                                        <label class="form-label">Maximum Possible</label>
                                        <input type="number" class="form-control" wire:model="max_possible" min="0" step="0.01">
                                    </div>
                                </div>
                            </div>
                            <div class="modal-footer">
                                <button type="button" class="btn btn-secondary" wire:click="$set('showItemModal', false)">Cancel</button>
                                <button type="submit" class="btn btn-primary">
                                    <i class="fa-solid fa-save me-1"></i> {{ $modalMode === 'edit' ? 'Update' : 'Add' }} Goal
                                </button>
                            </div>
                        </form>
                    </div>
                </div>
            </div>
        @endif
    @endif
</div>
