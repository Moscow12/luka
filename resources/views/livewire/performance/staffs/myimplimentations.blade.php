<div>
    <div class="row mb-4">
        <div class="col-12">
            <div class="d-md-flex justify-content-between align-items-center">
                <div>
                    <h4 class="mb-1">My Implementations</h4>
                    <p class="text-muted mb-0">Record your actual progress towards your goals</p>
                </div>
                @if($hasEmployeeRecord)
                    <button class="btn btn-primary" wire:click="openModal('create')">
                        <i class="fa-solid fa-plus me-1"></i> Record Implementation
                    </button>
                @endif
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
        @if(session()->has('error'))
            <div class="alert alert-danger alert-dismissible fade show" role="alert">
                {{ session('error') }}
                <button type="button" class="btn-close" data-bs-dismiss="alert"></button>
            </div>
        @endif

        <div class="row">
            {{-- Plans Overview (Left Sidebar) --}}
            <div class="col-lg-4 mb-4">
                <div class="card">
                    <div class="card-header bg-primary text-white">
                        <h6 class="mb-0"><i class="fa-solid fa-clipboard-list me-2"></i>My Plans & Progress</h6>
                    </div>
                    <div class="card-body p-0">
                        @if($myPlans->count() > 0)
                            <div class="list-group list-group-flush">
                                @foreach($myPlans as $plan)
                                    <button type="button"
                                            class="list-group-item list-group-item-action {{ $selectedPlan && $selectedPlan->id === $plan->id ? 'active' : '' }}"
                                            wire:click="selectPlan('{{ $plan->id }}')">
                                        <div class="d-flex justify-content-between align-items-center">
                                            <div>
                                                <strong>{{ $plan->plan_name }}</strong>
                                                <br><small>{{ $plan->employeePlanItems->count() }} goals</small>
                                            </div>
                                            <span class="badge bg-{{ $plan->status === 'active' ? 'success' : 'secondary' }}">
                                                {{ ucfirst($plan->status) }}
                                            </span>
                                        </div>
                                    </button>
                                @endforeach
                            </div>
                        @else
                            <div class="text-center text-muted py-4">
                                <i class="fa-solid fa-folder-open fa-2x mb-2"></i>
                                <p class="mb-0">No active plans found</p>
                                <a href="{{ route('performance.myplanning') }}" class="btn btn-sm btn-primary mt-2">
                                    Create a Plan
                                </a>
                            </div>
                        @endif
                    </div>
                </div>

                {{-- Selected Plan Goals Progress --}}
                @if($selectedPlan && $selectedPlanItems->count() > 0)
                    <div class="card mt-3">
                        <div class="card-header bg-info text-white">
                            <h6 class="mb-0"><i class="fa-solid fa-chart-line me-2"></i>Goals Progress</h6>
                        </div>
                        <div class="card-body">
                            @foreach($selectedPlanItems as $item)
                                <div class="mb-3">
                                    <div class="d-flex justify-content-between mb-1">
                                        <small class="fw-bold">{{ Str::limit($item->item_name, 30) }}</small>
                                        <small>{{ number_format($item->total_achieved) }}/{{ number_format($item->target_value ?? 0) }} {{ $item->target_unit }}</small>
                                    </div>
                                    <div class="progress" style="height: 10px;">
                                        <div class="progress-bar {{ $item->progress_percentage >= 100 ? 'bg-success' : ($item->progress_percentage >= 50 ? 'bg-info' : 'bg-warning') }}"
                                             style="width: {{ $item->progress_percentage }}%"></div>
                                    </div>
                                    <small class="text-muted">{{ number_format($item->progress_percentage, 1) }}% complete</small>
                                </div>
                            @endforeach
                        </div>
                    </div>
                @endif
            </div>

            {{-- Implementations List --}}
            <div class="col-lg-8">
                <div class="card">
                    <div class="card-header">
                        <div class="row align-items-center">
                            <div class="col-md-4">
                                <h6 class="mb-0"><i class="fa-solid fa-tasks me-2"></i>Implementation Records</h6>
                            </div>
                            <div class="col-md-8">
                                <div class="row g-2">
                                    <div class="col">
                                        <input type="search" class="form-control form-control-sm"
                                               wire:model.live.debounce.300ms="search"
                                               placeholder="Search activities...">
                                    </div>
                                    <div class="col-auto">
                                        <select class="form-select form-select-sm" wire:model.live="filterStatus">
                                            <option value="">All Status</option>
                                            <option value="pending">Pending</option>
                                            <option value="verified">Verified</option>
                                            <option value="rejected">Rejected</option>
                                        </select>
                                    </div>
                                    <div class="col-auto">
                                        <button class="btn btn-sm btn-outline-secondary" wire:click="$set('filterPlanId', '')">
                                            Clear Filters
                                        </button>
                                    </div>
                                </div>
                            </div>
                        </div>
                    </div>
                    <div class="card-body p-0">
                        @if($implementations->count() > 0)
                            <div class="table-responsive">
                                <table class="table table-hover mb-0">
                                    <thead class="table-light">
                                        <tr>
                                            <th>Date</th>
                                            <th>Activity</th>
                                            <th>Goal</th>
                                            <th class="text-center">Achieved</th>
                                            <th class="text-center">Status</th>
                                            <th>Actions</th>
                                        </tr>
                                    </thead>
                                    <tbody>
                                        @foreach($implementations as $impl)
                                            <tr>
                                                <td>
                                                    <small>{{ $impl->implementation_date->format('d M Y') }}</small>
                                                </td>
                                                <td>
                                                    <strong>{{ $impl->activity_title }}</strong>
                                                    @if($impl->activity_description)
                                                        <br><small class="text-muted">{{ Str::limit($impl->activity_description, 50) }}</small>
                                                    @endif
                                                </td>
                                                <td>
                                                    <small class="text-info">
                                                        {{ Str::limit($impl->employeePlanItem->item_name ?? 'N/A', 25) }}
                                                    </small>
                                                </td>
                                                <td class="text-center">
                                                    <span class="badge bg-primary">
                                                        {{ number_format($impl->quantity_achieved ?? 0) }} {{ $impl->unit }}
                                                    </span>
                                                </td>
                                                <td class="text-center">
                                                    <span class="badge bg-{{ $impl->status === 'verified' ? 'success' : ($impl->status === 'rejected' ? 'danger' : 'warning') }}">
                                                        {{ ucfirst($impl->status) }}
                                                    </span>
                                                </td>
                                                <td>
                                                    @if($impl->status !== 'verified')
                                                        <button class="btn btn-sm btn-outline-primary"
                                                                wire:click="openModal('edit', '{{ $impl->id }}')">
                                                            <i class="fa-solid fa-pencil"></i>
                                                        </button>
                                                        <button class="btn btn-sm btn-outline-danger"
                                                                wire:click="delete('{{ $impl->id }}')"
                                                                wire:confirm="Delete this implementation record?">
                                                            <i class="fa-solid fa-trash"></i>
                                                        </button>
                                                    @else
                                                        <span class="text-muted small">
                                                            <i class="fa-solid fa-lock"></i> Locked
                                                        </span>
                                                    @endif
                                                </td>
                                            </tr>
                                            @if($impl->supervisor_remarks)
                                                <tr class="table-light">
                                                    <td colspan="6">
                                                        <small><strong>Supervisor Remarks:</strong> {{ $impl->supervisor_remarks }}</small>
                                                    </td>
                                                </tr>
                                            @endif
                                        @endforeach
                                    </tbody>
                                </table>
                            </div>
                            <div class="card-footer">
                                {{ $implementations->links() }}
                            </div>
                        @else
                            <div class="text-center text-muted py-5">
                                <i class="fa-solid fa-clipboard-check fa-3x mb-3"></i>
                                <h5>No Implementations Yet</h5>
                                <p>Start recording your progress towards your goals.</p>
                                <button class="btn btn-primary" wire:click="openModal('create')">
                                    <i class="fa-solid fa-plus me-1"></i> Record First Implementation
                                </button>
                            </div>
                        @endif
                    </div>
                </div>
            </div>
        </div>

        {{-- Implementation Modal --}}
        @if($showModal)
            <div class="modal fade show d-block" tabindex="-1" style="background: rgba(0,0,0,0.5);">
                <div class="modal-dialog modal-lg">
                    <div class="modal-content">
                        <div class="modal-header">
                            <h5 class="modal-title">
                                {{ $modalMode === 'edit' ? 'Edit Implementation' : 'Record New Implementation' }}
                            </h5>
                            <button type="button" class="btn-close" wire:click="$set('showModal', false)"></button>
                        </div>
                        <form wire:submit="save">
                            <div class="modal-body">
                                <div class="row">
                                    <div class="col-md-6 mb-3">
                                        <label class="form-label">Goal <span class="text-danger">*</span></label>
                                        <select class="form-select @error('employee_plan_item_id') is-invalid @enderror"
                                                wire:model.live="employee_plan_item_id">
                                            <option value="">-- Select Goal --</option>
                                            @foreach($allPlanItems as $item)
                                                <option value="{{ $item->id }}">
                                                    {{ $item->item_name }} ({{ $item->employeePlan->plan_name ?? 'N/A' }})
                                                </option>
                                            @endforeach
                                        </select>
                                        @error('employee_plan_item_id') <div class="invalid-feedback">{{ $message }}</div> @enderror
                                    </div>
                                    <div class="col-md-6 mb-3">
                                        <label class="form-label">Implementation Date <span class="text-danger">*</span></label>
                                        <input type="date" class="form-control @error('implementation_date') is-invalid @enderror"
                                               wire:model="implementation_date" max="{{ date('Y-m-d') }}">
                                        @error('implementation_date') <div class="invalid-feedback">{{ $message }}</div> @enderror
                                    </div>
                                </div>

                                <div class="mb-3">
                                    <label class="form-label">Activity Title <span class="text-danger">*</span></label>
                                    <input type="text" class="form-control @error('activity_title') is-invalid @enderror"
                                           wire:model="activity_title" placeholder="e.g., Completed customer support calls">
                                    @error('activity_title') <div class="invalid-feedback">{{ $message }}</div> @enderror
                                </div>

                                <div class="mb-3">
                                    <label class="form-label">Activity Description</label>
                                    <textarea class="form-control" wire:model="activity_description" rows="2"
                                              placeholder="Describe what you did..."></textarea>
                                </div>

                                <div class="row">
                                    <div class="col-md-6 mb-3">
                                        <label class="form-label">Quantity Achieved</label>
                                        <input type="number" class="form-control" wire:model="quantity_achieved"
                                               min="0" step="0.01" placeholder="e.g., 25">
                                    </div>
                                    <div class="col-md-6 mb-3">
                                        <label class="form-label">Unit</label>
                                        <input type="text" class="form-control" wire:model="unit"
                                               placeholder="e.g., calls, reports, tasks">
                                    </div>
                                </div>

                                <div class="mb-3">
                                    <label class="form-label">Evidence/Supporting Documents</label>
                                    <textarea class="form-control" wire:model="evidence" rows="2"
                                              placeholder="Links to documents, screenshots, references..."></textarea>
                                </div>

                                <div class="row">
                                    <div class="col-md-6 mb-3">
                                        <label class="form-label">Challenges Faced</label>
                                        <textarea class="form-control" wire:model="challenges" rows="2"
                                                  placeholder="Any obstacles or difficulties..."></textarea>
                                    </div>
                                    <div class="col-md-6 mb-3">
                                        <label class="form-label">Lessons Learned</label>
                                        <textarea class="form-control" wire:model="lessons_learned" rows="2"
                                                  placeholder="Key takeaways or insights..."></textarea>
                                    </div>
                                </div>
                            </div>
                            <div class="modal-footer">
                                <button type="button" class="btn btn-secondary" wire:click="$set('showModal', false)">Cancel</button>
                                <button type="submit" class="btn btn-primary">
                                    <i class="fa-solid fa-save me-1"></i>
                                    {{ $modalMode === 'edit' ? 'Update' : 'Save' }} Implementation
                                </button>
                            </div>
                        </form>
                    </div>
                </div>
            </div>
        @endif
    @endif
</div>
