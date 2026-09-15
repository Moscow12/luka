<div class="custom-container">

    <x-pages.breadcrumn title="ASSIGNED DUTIES & KPIs" :breadcrumbs="[
        ['label' => 'Home', 'url' => route('dashboard')],
        ['label' => 'Performance Management', 'url' => '#'],
        ['label' => 'Assigned Duties', 'url' => route('performance.assigned.duties')],
    ]">
        <button class='btn btn-primary d-md-flex align-items-center gap-2' wire:click="createDuty">
            <i class="fa-solid fa-plus"></i> ASSIGN NEW DUTY
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
                                    placeholder="Search employees..." />
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
                                <!-- Priority Filter -->
                                <select wire:model.live="priorityFilter" class="form-select" style="width: auto;">
                                    <option value="">All Priorities</option>
                                    <option value="low">Low</option>
                                    <option value="medium">Medium</option>
                                    <option value="high">High</option>
                                    <option value="urgent">Urgent</option>
                                </select>

                                <!-- Status Filter -->
                                <select wire:model.live="statusFilter" class="form-select" style="width: auto;">
                                    <option value="">All Status</option>
                                    <option value="assigned">Assigned</option>
                                    <option value="in_progress">In Progress</option>
                                    <option value="completed">Completed</option>
                                    <option value="cancelled">Cancelled</option>
                                </select>

                                <!-- Reset Filters -->
                                @if ($search || $priorityFilter || $statusFilter)
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
                                <th class="text-center">Total Tasks</th>
                                <th style="width: 30%;">Progress</th>
                                <th class="text-end" style="width: 120px;">Actions</th>
                            </tr>
                        </thead>
                        <tbody>
                            @forelse ($employeeSummaries ?? [] as $index => $summary)
                                @php
                                    $percent = $summary->total_tasks > 0
                                        ? (int) round($summary->completed_tasks / $summary->total_tasks * 100)
                                        : 0;
                                    $barColor = $percent === 100 ? 'success' : ($percent >= 50 ? 'info' : 'warning');
                                @endphp
                                <tr wire:key="employee-{{ $summary->id }}">
                                    <td class="text-center text-muted">
                                        {{ $employeeSummaries->firstItem() + $index }}
                                    </td>
                                    <td>
                                        <div class="d-flex align-items-center gap-2">
                                            @if ($summary->photo)
                                                <img src="{{ asset('storage/' . $summary->photo) }}"
                                                    alt="{{ $summary->first_name }}"
                                                    class="rounded-circle"
                                                    width="35" height="35"
                                                    style="object-fit: cover;">
                                            @else
                                                <div class="rounded-circle bg-primary text-white d-inline-flex align-items-center justify-content-center"
                                                    style="width: 35px; height: 35px; font-size: 12px;">
                                                    {{ strtoupper(substr($summary->first_name ?? 'U', 0, 1)) }}{{ strtoupper(substr($summary->last_name ?? 'N', 0, 1)) }}
                                                </div>
                                            @endif
                                            <div>
                                                <span class="fw-semibold text-dark">
                                                    {{ $summary->first_name }} {{ $summary->last_name }}
                                                </span>
                                            </div>
                                        </div>
                                    </td>
                                    <td class="text-center">
                                        <span class="fw-semibold">{{ $summary->total_tasks }}</span>
                                    </td>
                                    <td>
                                        <div class="d-flex align-items-center gap-2">
                                            <div class="progress flex-grow-1" style="height: 8px;">
                                                <div class="progress-bar bg-{{ $barColor }}" role="progressbar"
                                                    style="width: {{ $percent }}%;"
                                                    aria-valuenow="{{ $percent }}" aria-valuemin="0" aria-valuemax="100">
                                                </div>
                                            </div>
                                            <small class="text-muted text-nowrap">
                                                {{ $summary->completed_tasks }}/{{ $summary->total_tasks }} ({{ $percent }}%)
                                            </small>
                                        </div>
                                    </td>
                                    <td>
                                        <div class="d-flex gap-1 justify-content-end">
                                            <button wire:click="openEmployeeTasksModal('{{ $summary->id }}')"
                                                class="btn btn-sm btn-outline-primary"
                                                title="View Tasks">
                                                <i class="fa-solid fa-eye me-1"></i> View Tasks
                                            </button>
                                        </div>
                                    </td>
                                </tr>
                            @empty
                                <tr>
                                    <td colspan="5" class="text-center py-5">
                                        <div class="d-flex flex-column align-items-center justify-content-center">
                                            <i class="fa-solid fa-tasks text-muted mb-3"
                                                style="font-size: 48px;"></i>
                                            <h5 class="text-muted">No Assigned Duties Found</h5>
                                            <p class="text-muted">
                                                @if ($search || $priorityFilter || $statusFilter)
                                                    Try adjusting your filters or search query
                                                @else
                                                    Start by assigning duties to employees
                                                @endif
                                            </p>
                                            @if (!$search && !$priorityFilter && !$statusFilter)
                                                <button class="btn btn-primary mt-2" wire:click="createDuty">
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
                            @if ($employeeSummaries->total() > 0)
                                Showing {{ $employeeSummaries->firstItem() }} to {{ $employeeSummaries->lastItem() }} of
                                {{ $employeeSummaries->total() }} employees
                            @else
                                No employees with assigned duties found
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
                                {{ $employeeSummaries->links() }}
                            </div>
                        </div>
                    </div>
                </div>
            </div>
        </div>
    </div>

    <!-- Employee Tasks Modal -->
    @if ($showEmployeeTasksModal)
        <div class="modal fade show d-block" tabindex="-1" style="background-color: rgba(0,0,0,0.5);">
            <div class="modal-dialog modal-lg modal-dialog-centered modal-dialog-scrollable">
                <div class="modal-content">
                    <div class="modal-header">
                        <h5 class="modal-title">
                            <i class="fa-solid fa-list-check me-2"></i>
                            {{ $this->viewingEmployee?->getFullName() }} - Tasks
                        </h5>
                        <button type="button" class="btn-close" wire:click="closeEmployeeTasksModal"></button>
                    </div>
                    <div class="modal-body" style="max-height: 70vh; overflow-y: auto;">
                        <div class="row g-3 mb-3">
                            <x-forms.input type="date" name="employeeTaskFilterDateFrom" label="From Date"
                                colMd="6" max="{{ $employeeTaskFilterDateTo ?: now()->toDateString() }}"
                                wire:model.live="employeeTaskFilterDateFrom" />

                            <x-forms.input type="date" name="employeeTaskFilterDateTo" label="To Date"
                                colMd="6" min="{{ $employeeTaskFilterDateFrom }}" max="{{ now()->toDateString() }}"
                                wire:model.live="employeeTaskFilterDateTo" />
                        </div>

                        @php
                            $priorityColors = [
                                'low' => 'secondary',
                                'medium' => 'info',
                                'high' => 'warning',
                                'urgent' => 'danger',
                            ];
                            $statusColors = [
                                'assigned' => 'secondary',
                                'in_progress' => 'primary',
                                'completed' => 'success',
                                'cancelled' => 'danger',
                            ];
                        @endphp

                        @forelse ($this->viewingEmployeeTasks as $task)
                            <div class="card mb-2" wire:key="task-{{ $task->id }}">
                                <div class="card-body py-2 px-3">
                                    <div class="d-flex justify-content-between align-items-start gap-2">
                                        <div>
                                            <span class="fw-semibold text-dark d-block">{{ $task->duty_name }}</span>
                                            @if ($task->description)
                                                <small class="text-muted d-block">{{ Str::limit($task->description, 60) }}</small>
                                            @endif
                                            <div class="d-flex align-items-center gap-2 mt-1">
                                                <span class="badge bg-{{ $priorityColors[$task->priority] ?? 'secondary' }}">
                                                    {{ ucfirst($task->priority) }}
                                                </span>
                                                <span class="badge bg-{{ $statusColors[$task->status] ?? 'secondary' }}">
                                                    {{ ucfirst(str_replace('_', ' ', $task->status)) }}
                                                </span>
                                                @if ($task->start_date || $task->end_date)
                                                    <small class="text-muted">
                                                        @if ($task->start_date){{ $task->start_date->format('d M Y') }}@endif
                                                        @if ($task->start_date && $task->end_date) - @endif
                                                        @if ($task->end_date){{ $task->end_date->format('d M Y') }}@endif
                                                    </small>
                                                @endif
                                            </div>
                                        </div>
                                        <div class="text-end" style="min-width: 160px;">
                                            @if ($task->status === 'completed' && ! $task->reviewed_at)
                                                <button wire:click="openApproveModal('{{ $task->id }}')"
                                                    class="btn btn-sm btn-success">
                                                    <i class="fa-solid fa-check me-1"></i> Approve
                                                </button>
                                            @elseif ($task->reviewed_at)
                                                <small class="text-success d-block">
                                                    <i class="fa-solid fa-circle-check me-1"></i>Approved
                                                </small>
                                                <small class="text-muted d-block">
                                                    by {{ $task->reviewedBy?->full_name }} on {{ $task->reviewed_at->format('d M Y') }}
                                                </small>
                                                @if ($task->approval_score)
                                                    <span class="badge bg-primary-subtle text-primary mt-1">
                                                        <i class="fa-solid fa-star me-1"></i>{{ $task->approval_score }}/10
                                                    </span>
                                                @endif
                                                @if ($task->review_comments)
                                                    <small class="text-muted d-block mt-1 fst-italic">"{{ Str::limit($task->review_comments, 60) }}"</small>
                                                @endif
                                            @endif
                                        </div>
                                    </div>
                                </div>
                            </div>
                        @empty
                            <div class="text-center text-muted py-4">
                                <i class="fa-solid fa-tasks mb-2" style="font-size: 32px;"></i>
                                <p class="mb-0">No tasks in this date range</p>
                            </div>
                        @endforelse
                    </div>
                    <div class="modal-footer">
                        <button type="button" class="btn btn-secondary" wire:click="closeEmployeeTasksModal">
                            Close
                        </button>
                    </div>
                </div>
            </div>
        </div>
    @endif

    <!-- Approve Task Modal (score + comments) -->
    @if ($approvingDutyId)
        <div class="modal fade show d-block" tabindex="-1" style="background-color: rgba(0,0,0,0.6); z-index: 1060;">
            <div class="modal-dialog modal-dialog-centered">
                <div class="modal-content">
                    <form wire:submit.prevent="submitApproval">
                        <div class="modal-header bg-success text-white">
                            <h5 class="modal-title">
                                <i class="fa-solid fa-check-circle me-2"></i>Approve Task
                            </h5>
                            <button type="button" class="btn-close btn-close-white" wire:click="closeApproveModal"></button>
                        </div>
                        <div class="modal-body">
                            <div class="mb-3">
                                <label class="form-label">Performance Score (1-10, optional)</label>
                                <input type="number" min="1" max="10" step="1"
                                    class="form-control @error('approval_score') is-invalid @enderror"
                                    wire:model="approval_score" placeholder="Rate how the task was performed">
                                @error('approval_score')
                                    <div class="invalid-feedback">{{ $message }}</div>
                                @enderror
                            </div>
                            <div class="mb-3">
                                <label class="form-label">Comments (optional)</label>
                                <textarea class="form-control @error('approval_comments') is-invalid @enderror"
                                    wire:model="approval_comments" rows="3"
                                    placeholder="Any remarks about how this task was carried out..."></textarea>
                                @error('approval_comments')
                                    <div class="invalid-feedback">{{ $message }}</div>
                                @enderror
                            </div>
                        </div>
                        <div class="modal-footer">
                            <button type="button" class="btn btn-secondary" wire:click="closeApproveModal">Cancel</button>
                            <button type="submit" class="btn btn-success">
                                <i class="fa-solid fa-check me-1"></i> Approve
                            </button>
                        </div>
                    </form>
                </div>
            </div>
        </div>
    @endif

    <!-- Create/Edit Duty Modal -->
    @if ($showModal)
        <div class="modal fade show d-block" tabindex="-1" style="background-color: rgba(0,0,0,0.5);">
            <div class="modal-dialog modal-lg modal-dialog-centered modal-dialog-scrollable">
                <div class="modal-content">
                    <div class="modal-header">
                        <h5 class="modal-title">
                            <i class="fa-solid fa-tasks me-2"></i>
                            {{ $modalMode === 'edit' ? 'Edit Assigned Duty' : 'Assign New Duty' }}
                        </h5>
                        <button type="button" class="btn-close" wire:click="closeModal"></button>
                    </div>
                    <form wire:submit.prevent="saveDuty">
                        <div class="modal-body" style="max-height: 70vh; overflow-y: auto;">
                            <div class="row g-3">
                                <div class="col-md-6">
                                    <x-forms.search-picker
                                        name="dutyForm.employee_id"
                                        label="Employee"
                                        :required="true"
                                        :selected="$this->selectedDutyEmployee"
                                        :items="$this->filteredDutyEmployees"
                                        :search-value="$dutyEmployeeSearch"
                                        :show-dropdown="$showDutyEmployeeDropdown"
                                        search-prop="dutyEmployeeSearch"
                                        dropdown-prop="showDutyEmployeeDropdown"
                                        search-placeholder="Search employee by name or number..."
                                        empty-text="No employees found"
                                        select-method="selectDutyEmployee"
                                        clear-method="clearDutyEmployee"
                                        label-key="name"
                                        sublabel-key="employee_no" />
                                </div>

                                <div class="col-md-6">
                                    <label for="kpiType" class="form-label">KPI Type <span class="text-danger">*</span></label>
                                    <select wire:model="dutyForm.kpi_type" class="form-select @error('dutyForm.kpi_type') is-invalid @enderror" id="kpiType">
                                        <option value="quantitative">Quantitative</option>
                                        <option value="qualitative">Qualitative</option>
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
                                    <textarea wire:model="dutyForm.description" class="form-control @error('dutyForm.description') is-invalid @enderror" id="dutyDescription" rows="2" placeholder="Enter detailed description of the duty"></textarea>
                                    @error('dutyForm.description')
                                        <div class="invalid-feedback">{{ $message }}</div>
                                    @enderror
                                </div>

                                <div class="col-md-6">
                                    <label for="measurementType" class="form-label">Measurement Type <span class="text-danger">*</span></label>
                                    <select wire:model="dutyForm.measurement_type" class="form-select @error('dutyForm.measurement_type') is-invalid @enderror" id="measurementType">
                                        <option value="numeric">Numeric</option>
                                        <option value="boolean">Boolean (Yes/No)</option>
                                        <option value="percentage">Percentage</option>
                                        <option value="rating_scale">Rating Scale</option>
                                    </select>
                                    @error('dutyForm.measurement_type')
                                        <div class="invalid-feedback">{{ $message }}</div>
                                    @enderror
                                </div>

                                <div class="col-md-6">
                                    <label for="weight" class="form-label">Weight (%)</label>
                                    <input type="number" wire:model="dutyForm.weight" class="form-control @error('dutyForm.weight') is-invalid @enderror" id="weight" placeholder="0" min="0" max="100" step="0.01">
                                    @error('dutyForm.weight')
                                        <div class="invalid-feedback">{{ $message }}</div>
                                    @enderror
                                </div>

                                <div class="col-md-6">
                                    <label for="targetValue" class="form-label">Target Value</label>
                                    <input type="number" wire:model="dutyForm.target_value" class="form-control @error('dutyForm.target_value') is-invalid @enderror" id="targetValue" placeholder="Enter target value" step="0.01">
                                    @error('dutyForm.target_value')
                                        <div class="invalid-feedback">{{ $message }}</div>
                                    @enderror
                                </div>

                                <div class="col-md-6">
                                    <label for="targetUnit" class="form-label">Target Unit</label>
                                    <input type="text" wire:model="dutyForm.target_unit" class="form-control @error('dutyForm.target_unit') is-invalid @enderror" id="targetUnit" placeholder="e.g., %, count, TZS">
                                    @error('dutyForm.target_unit')
                                        <div class="invalid-feedback">{{ $message }}</div>
                                    @enderror
                                </div>

                                <div class="col-md-6">
                                    <label for="priority" class="form-label">Priority <span class="text-danger">*</span></label>
                                    <select wire:model="dutyForm.priority" class="form-select @error('dutyForm.priority') is-invalid @enderror" id="priority">
                                        <option value="low">Low</option>
                                        <option value="medium">Medium</option>
                                        <option value="high">High</option>
                                        <option value="urgent">Urgent</option>
                                    </select>
                                    @error('dutyForm.priority')
                                        <div class="invalid-feedback">{{ $message }}</div>
                                    @enderror
                                </div>

                                <div class="col-md-6">
                                    <label for="dutyStatus" class="form-label">Status <span class="text-danger">*</span></label>
                                    <select wire:model="dutyForm.status" class="form-select @error('dutyForm.status') is-invalid @enderror" id="dutyStatus">
                                        <option value="assigned">Assigned</option>
                                        <option value="in_progress">In Progress</option>
                                        <option value="completed">Completed</option>
                                        <option value="cancelled">Cancelled</option>
                                    </select>
                                    @error('dutyForm.status')
                                        <div class="invalid-feedback">{{ $message }}</div>
                                    @enderror
                                </div>

                                <div class="col-md-6">
                                    <label for="startDate" class="form-label">Start Date</label>
                                    <input type="date" wire:model="dutyForm.start_date" class="form-control @error('dutyForm.start_date') is-invalid @enderror" id="startDate">
                                    @error('dutyForm.start_date')
                                        <div class="invalid-feedback">{{ $message }}</div>
                                    @enderror
                                </div>

                                <div class="col-md-6">
                                    <label for="endDate" class="form-label">End Date</label>
                                    <input type="date" wire:model="dutyForm.end_date" class="form-control @error('dutyForm.end_date') is-invalid @enderror" id="endDate">
                                    @error('dutyForm.end_date')
                                        <div class="invalid-feedback">{{ $message }}</div>
                                    @enderror
                                </div>

                                <div class="col-12">
                                    <label for="scoringCriteria" class="form-label">Scoring Criteria</label>
                                    <textarea wire:model="dutyForm.scoring_criteria" class="form-control @error('dutyForm.scoring_criteria') is-invalid @enderror" id="scoringCriteria" rows="2" placeholder="Describe how this duty will be scored/evaluated"></textarea>
                                    @error('dutyForm.scoring_criteria')
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
                                <i class="fa-solid fa-save me-1"></i> {{ $modalMode === 'edit' ? 'Update Duty' : 'Save Duty' }}
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
