<div class="custom-container">

    <x-pages.breadcrumn title="ASSIGNED DUTIES & KPIs" :breadcrumbs="[
        ['label' => 'Home', 'url' => route('dashboard')],
        ['label' => 'Performance Management', 'url' => '#'],
        ['label' => 'Assigned Duties', 'url' => route('performance.assigned.duties')],
    ]">
        <button class='btn btn-primary d-md-flex align-items-center gap-2' data-bs-toggle="modal" data-bs-target="#createDutyModal">
            <i class="fa-solid fa-plus"></i> ASSIGN NEW DUTY
        </button>
    </x-pages.breadcrumn>

    <!-- Statistics Cards -->
    <div class="row g-3 mb-4">
        <div class="col-12 col-sm-6 col-xl-3">
            <div class="card card-lg border-0 shadow-sm">
                <div class="card-body">
                    <div class="d-flex align-items-center justify-content-between">
                        <div>
                            <p class="text-muted mb-1 small text-uppercase">Total Duties</p>
                            <h3 class="mb-0 fw-bold">{{ $totalDuties ?? 0 }}</h3>
                        </div>
                        <div class="icon-shape icon-lg bg-primary-subtle text-primary rounded-3">
                            <i class="fa-solid fa-tasks fa-lg"></i>
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
                            <p class="text-muted mb-1 small text-uppercase">In Progress</p>
                            <h3 class="mb-0 fw-bold text-primary">{{ $inProgressDuties ?? 0 }}</h3>
                        </div>
                        <div class="icon-shape icon-lg bg-primary-subtle text-primary rounded-3">
                            <i class="fa-solid fa-spinner fa-lg"></i>
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
                            <p class="text-muted mb-1 small text-uppercase">High Priority</p>
                            <h3 class="mb-0 fw-bold text-danger">{{ $highPriorityDuties ?? 0 }}</h3>
                        </div>
                        <div class="icon-shape icon-lg bg-danger-subtle text-danger rounded-3">
                            <i class="fa-solid fa-exclamation-triangle fa-lg"></i>
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
                            <h3 class="mb-0 fw-bold text-success">{{ $completedDuties ?? 0 }}</h3>
                        </div>
                        <div class="icon-shape icon-lg bg-success-subtle text-success rounded-3">
                            <i class="fa-solid fa-check-circle fa-lg"></i>
                        </div>
                    </div>
                </div>
            </div>
        </div>
    </div>

    <!-- Assigned Duties List Card -->
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
                                    placeholder="Search duties by name or employee..." />
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
                                <!-- Employee Filter -->
                                <select wire:model.live="employeeFilter" class="form-select" style="width: auto;">
                                    <option value="">All Employees</option>
                                    @foreach ($employees ?? [] as $emp)
                                        <option value="{{ $emp->id }}">{{ $emp->first_name }} {{ $emp->last_name }}</option>
                                    @endforeach
                                </select>

                                <!-- Priority Filter -->
                                <select wire:model.live="priorityFilter" class="form-select" style="width: auto;">
                                    <option value="">All Priorities</option>
                                    <option value="low">Low</option>
                                    <option value="medium">Medium</option>
                                    <option value="high">High</option>
                                    <option value="critical">Critical</option>
                                </select>

                                <!-- Status Filter -->
                                <select wire:model.live="statusFilter" class="form-select" style="width: auto;">
                                    <option value="">All Status</option>
                                    <option value="pending">Pending</option>
                                    <option value="in_progress">In Progress</option>
                                    <option value="completed">Completed</option>
                                    <option value="overdue">Overdue</option>
                                </select>

                                <!-- Reset Filters -->
                                @if ($search ?? false || $employeeFilter ?? false || $priorityFilter ?? false || $statusFilter ?? false)
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
                                <th>Employee</th>
                                <th>Duty Name</th>
                                <th>KPI Type</th>
                                <th class="text-center">Target</th>
                                <th class="text-center">Priority</th>
                                <th class="text-center">Status</th>
                                <th>Assigned Date</th>
                                <th>Due Date</th>
                                <th class="text-end" style="width: 150px;">Actions</th>
                            </tr>
                        </thead>
                        <tbody>
                            @forelse ($duties ?? [] as $index => $duty)
                                <tr wire:key="duty-{{ $duty->id ?? $index }}">
                                    <td class="text-center text-muted">
                                        {{ ($duties->firstItem() ?? 0) + $index }}
                                    </td>
                                    <td>
                                        <div class="d-flex align-items-center gap-2">
                                            @if (!empty($duty->employee->photo))
                                                <img src="{{ asset('storage/' . $duty->employee->photo) }}"
                                                    alt="{{ $duty->employee->first_name }}"
                                                    class="rounded-circle"
                                                    width="35" height="35"
                                                    style="object-fit: cover;">
                                            @else
                                                <div class="rounded-circle bg-primary text-white d-inline-flex align-items-center justify-content-center"
                                                    style="width: 35px; height: 35px; font-size: 12px;">
                                                    {{ strtoupper(substr($duty->employee->first_name ?? 'U', 0, 1)) }}{{ strtoupper(substr($duty->employee->last_name ?? 'N', 0, 1)) }}
                                                </div>
                                            @endif
                                            <div>
                                                <span class="fw-semibold text-dark">
                                                    {{ $duty->employee->first_name ?? '' }} {{ $duty->employee->last_name ?? '' }}
                                                </span>
                                            </div>
                                        </div>
                                    </td>
                                    <td>
                                        <div class="d-flex flex-column">
                                            <span class="fw-semibold text-dark">{{ $duty->duty_name ?? 'N/A' }}</span>
                                            @if (!empty($duty->description))
                                                <small class="text-muted">{{ Str::limit($duty->description, 40) }}</small>
                                            @endif
                                        </div>
                                    </td>
                                    <td>
                                        <span class="badge bg-info-subtle text-info">
                                            {{ ucfirst($duty->kpi_type ?? 'N/A') }}
                                        </span>
                                    </td>
                                    <td class="text-center">
                                        <span class="fw-semibold">{{ $duty->target ?? '-' }}</span>
                                        @if (!empty($duty->unit))
                                            <small class="text-muted d-block">{{ $duty->unit }}</small>
                                        @endif
                                    </td>
                                    <td class="text-center">
                                        @php
                                            $priorityColors = [
                                                'low' => 'secondary',
                                                'medium' => 'info',
                                                'high' => 'warning',
                                                'critical' => 'danger',
                                            ];
                                            $priorityColor = $priorityColors[$duty->priority ?? 'medium'] ?? 'secondary';
                                            $priorityIcons = [
                                                'low' => 'fa-arrow-down',
                                                'medium' => 'fa-minus',
                                                'high' => 'fa-arrow-up',
                                                'critical' => 'fa-exclamation-triangle',
                                            ];
                                            $priorityIcon = $priorityIcons[$duty->priority ?? 'medium'] ?? 'fa-minus';
                                        @endphp
                                        <span class="badge bg-{{ $priorityColor }}">
                                            <i class="fa-solid {{ $priorityIcon }} me-1"></i>
                                            {{ ucfirst($duty->priority ?? 'Medium') }}
                                        </span>
                                    </td>
                                    <td class="text-center">
                                        @php
                                            $statusColors = [
                                                'pending' => 'secondary',
                                                'in_progress' => 'primary',
                                                'completed' => 'success',
                                                'overdue' => 'danger',
                                            ];
                                            $statusColor = $statusColors[$duty->status ?? 'pending'] ?? 'secondary';
                                        @endphp
                                        <span class="badge bg-{{ $statusColor }}">
                                            <i class="fa-solid fa-circle me-1" style="font-size: 6px;"></i>
                                            {{ ucfirst(str_replace('_', ' ', $duty->status ?? 'Pending')) }}
                                        </span>
                                    </td>
                                    <td>
                                        <small class="text-muted">
                                            <i class="fa-solid fa-calendar me-1"></i>
                                            {{ $duty->assigned_date ? \Carbon\Carbon::parse($duty->assigned_date)->format('d M Y') : '-' }}
                                        </small>
                                    </td>
                                    <td>
                                        @if (!empty($duty->due_date))
                                            @php
                                                $dueDate = \Carbon\Carbon::parse($duty->due_date);
                                                $isOverdue = $dueDate->isPast() && $duty->status !== 'completed';
                                            @endphp
                                            <small class="{{ $isOverdue ? 'text-danger fw-semibold' : 'text-muted' }}">
                                                <i class="fa-solid fa-clock me-1"></i>
                                                {{ $dueDate->format('d M Y') }}
                                                @if ($isOverdue)
                                                    <br><span class="badge bg-danger-subtle text-danger mt-1">Overdue</span>
                                                @endif
                                            </small>
                                        @else
                                            <span class="text-muted">-</span>
                                        @endif
                                    </td>
                                    <td>
                                        <div class="d-flex gap-1 justify-content-end">
                                            <button wire:click="viewDuty('{{ $duty->id ?? '' }}')"
                                                class="btn btn-sm btn-ghost-info rounded-circle"
                                                title="View Details">
                                                <i class="fa-solid fa-eye"></i>
                                            </button>
                                            <button wire:click="editDuty('{{ $duty->id ?? '' }}')"
                                                class="btn btn-sm btn-ghost-secondary rounded-circle"
                                                title="Edit">
                                                <i class="fa-solid fa-pen-to-square"></i>
                                            </button>
                                            <button wire:click="deleteDuty('{{ $duty->id ?? '' }}')"
                                                type="button"
                                                class="btn btn-sm btn-ghost-danger rounded-circle"
                                                title="Delete"
                                                onclick="confirm('Are you sure you want to delete this duty?') || event.stopImmediatePropagation()">
                                                <i class="fa-solid fa-trash"></i>
                                            </button>
                                        </div>
                                    </td>
                                </tr>
                            @empty
                                <tr>
                                    <td colspan="10" class="text-center py-5">
                                        <div class="d-flex flex-column align-items-center justify-content-center">
                                            <i class="fa-solid fa-tasks text-muted mb-3"
                                                style="font-size: 48px;"></i>
                                            <h5 class="text-muted">No Assigned Duties Found</h5>
                                            <p class="text-muted">
                                                @if ($search ?? false || $employeeFilter ?? false || $priorityFilter ?? false || $statusFilter ?? false)
                                                    Try adjusting your filters or search query
                                                @else
                                                    Start by assigning duties to employees
                                                @endif
                                            </p>
                                            @if (!($search ?? false) && !($employeeFilter ?? false) && !($priorityFilter ?? false) && !($statusFilter ?? false))
                                                <button class="btn btn-primary mt-2" data-bs-toggle="modal" data-bs-target="#createDutyModal">
                                                    <i class="fa-solid fa-plus me-1"></i> Assign New Duty
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
                            @if (($duties->total() ?? 0) > 0)
                                Showing {{ $duties->firstItem() ?? 0 }} to {{ $duties->lastItem() ?? 0 }} of
                                {{ $duties->total() ?? 0 }} assigned duties
                            @else
                                No assigned duties found
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
                            @if (isset($duties) && method_exists($duties, 'links'))
                                <div>
                                    {{ $duties->links() }}
                                </div>
                            @endif
                        </div>
                    </div>
                </div>
            </div>
        </div>
    </div>

    <!-- Create/Edit Duty Modal -->
    <x-forms.modal id="createDutyModal" title="{{ $editingDutyId ?? false ? 'Edit Assigned Duty' : 'Assign New Duty' }}" size="modal-lg" :centered="true">
        <form wire:submit.prevent="saveDuty">
            <div class="row g-3">
                <div class="col-md-6">
                    <label for="dutyEmployee" class="form-label">Employee <span class="text-danger">*</span></label>
                    <select wire:model="dutyForm.employee_id" class="form-select @error('dutyForm.employee_id') is-invalid @enderror" id="dutyEmployee">
                        <option value="">Select Employee</option>
                        @foreach ($employees ?? [] as $emp)
                            <option value="{{ $emp->id }}">{{ $emp->first_name }} {{ $emp->last_name }} ({{ $emp->employee_no }})</option>
                        @endforeach
                    </select>
                    @error('dutyForm.employee_id')
                        <div class="invalid-feedback">{{ $message }}</div>
                    @enderror
                </div>

                <div class="col-md-6">
                    <label for="kpiType" class="form-label">KPI Type <span class="text-danger">*</span></label>
                    <select wire:model="dutyForm.kpi_type" class="form-select @error('dutyForm.kpi_type') is-invalid @enderror" id="kpiType">
                        <option value="">Select KPI Type</option>
                        <option value="quantitative">Quantitative</option>
                        <option value="qualitative">Qualitative</option>
                        <option value="behavioral">Behavioral</option>
                        <option value="project">Project-based</option>
                    </select>
                    @error('dutyForm.kpi_type')
                        <div class="invalid-feedback">{{ $message }}</div>
                    @enderror
                </div>

                <div class="col-12">
                    <label for="dutyName" class="form-label">Duty Name <span class="text-danger">*</span></label>
                    <input type="text" wire:model="dutyForm.duty_name" class="form-control @error('dutyForm.duty_name') is-invalid @enderror" id="dutyName" placeholder="Enter duty name">
                    @error('dutyForm.duty_name')
                        <div class="invalid-feedback">{{ $message }}</div>
                    @enderror
                </div>

                <div class="col-12">
                    <label for="dutyDescription" class="form-label">Description</label>
                    <textarea wire:model="dutyForm.description" class="form-control @error('dutyForm.description') is-invalid @enderror" id="dutyDescription" rows="3" placeholder="Enter detailed description of the duty"></textarea>
                    @error('dutyForm.description')
                        <div class="invalid-feedback">{{ $message }}</div>
                    @enderror
                </div>

                <div class="col-md-4">
                    <label for="target" class="form-label">Target <span class="text-danger">*</span></label>
                    <input type="text" wire:model="dutyForm.target" class="form-control @error('dutyForm.target') is-invalid @enderror" id="target" placeholder="Enter target value">
                    @error('dutyForm.target')
                        <div class="invalid-feedback">{{ $message }}</div>
                    @enderror
                </div>

                <div class="col-md-4">
                    <label for="unit" class="form-label">Unit</label>
                    <input type="text" wire:model="dutyForm.unit" class="form-control @error('dutyForm.unit') is-invalid @enderror" id="unit" placeholder="e.g., %, units, items">
                    @error('dutyForm.unit')
                        <div class="invalid-feedback">{{ $message }}</div>
                    @enderror
                </div>

                <div class="col-md-4">
                    <label for="weight" class="form-label">Weight (%)</label>
                    <input type="number" wire:model="dutyForm.weight" class="form-control @error('dutyForm.weight') is-invalid @enderror" id="weight" placeholder="0" min="0" max="100" step="0.1">
                    @error('dutyForm.weight')
                        <div class="invalid-feedback">{{ $message }}</div>
                    @enderror
                </div>

                <div class="col-md-4">
                    <label for="priority" class="form-label">Priority <span class="text-danger">*</span></label>
                    <select wire:model="dutyForm.priority" class="form-select @error('dutyForm.priority') is-invalid @enderror" id="priority">
                        <option value="low">Low</option>
                        <option value="medium">Medium</option>
                        <option value="high">High</option>
                        <option value="critical">Critical</option>
                    </select>
                    @error('dutyForm.priority')
                        <div class="invalid-feedback">{{ $message }}</div>
                    @enderror
                </div>

                <div class="col-md-4">
                    <label for="dutyStatus" class="form-label">Status <span class="text-danger">*</span></label>
                    <select wire:model="dutyForm.status" class="form-select @error('dutyForm.status') is-invalid @enderror" id="dutyStatus">
                        <option value="pending">Pending</option>
                        <option value="in_progress">In Progress</option>
                        <option value="completed">Completed</option>
                        <option value="overdue">Overdue</option>
                    </select>
                    @error('dutyForm.status')
                        <div class="invalid-feedback">{{ $message }}</div>
                    @enderror
                </div>

                <div class="col-md-4">
                    <label for="assignedDate" class="form-label">Assigned Date <span class="text-danger">*</span></label>
                    <input type="date" wire:model="dutyForm.assigned_date" class="form-control @error('dutyForm.assigned_date') is-invalid @enderror" id="assignedDate">
                    @error('dutyForm.assigned_date')
                        <div class="invalid-feedback">{{ $message }}</div>
                    @enderror
                </div>

                <div class="col-md-6">
                    <label for="dueDate" class="form-label">Due Date</label>
                    <input type="date" wire:model="dutyForm.due_date" class="form-control @error('dutyForm.due_date') is-invalid @enderror" id="dueDate">
                    @error('dutyForm.due_date')
                        <div class="invalid-feedback">{{ $message }}</div>
                    @enderror
                </div>

                <div class="col-md-6">
                    <label for="reviewPeriod" class="form-label">Review Period</label>
                    <select wire:model="dutyForm.review_period" class="form-select @error('dutyForm.review_period') is-invalid @enderror" id="reviewPeriod">
                        <option value="">Select Review Period</option>
                        <option value="weekly">Weekly</option>
                        <option value="monthly">Monthly</option>
                        <option value="quarterly">Quarterly</option>
                        <option value="annually">Annually</option>
                    </select>
                    @error('dutyForm.review_period')
                        <div class="invalid-feedback">{{ $message }}</div>
                    @enderror
                </div>

                <div class="col-12">
                    <label for="remarks" class="form-label">Remarks/Notes</label>
                    <textarea wire:model="dutyForm.remarks" class="form-control @error('dutyForm.remarks') is-invalid @enderror" id="remarks" rows="2" placeholder="Any additional notes or remarks"></textarea>
                    @error('dutyForm.remarks')
                        <div class="invalid-feedback">{{ $message }}</div>
                    @enderror
                </div>
            </div>

            <x-slot name="footer">
                <button type="button" class="btn btn-secondary" data-bs-dismiss="modal">
                    <i class="fa-solid fa-times me-1"></i> Cancel
                </button>
                <button type="submit" class="btn btn-primary">
                    <i class="fa-solid fa-save me-1"></i> Save Duty
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
