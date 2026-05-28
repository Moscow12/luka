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
                                    placeholder="Search duties by name or employee..." />
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
                                @if ($search || $employeeFilter || $priorityFilter || $statusFilter)
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
                                <th class="text-center">Weight</th>
                                <th class="text-center">Priority</th>
                                <th class="text-center">Status</th>
                                <th>Period</th>
                                <th class="text-end" style="width: 120px;">Actions</th>
                            </tr>
                        </thead>
                        <tbody>
                            @forelse ($duties ?? [] as $index => $duty)
                                <tr wire:key="duty-{{ $duty->id }}">
                                    <td class="text-center text-muted">
                                        {{ $duties->firstItem() + $index }}
                                    </td>
                                    <td>
                                        <div class="d-flex align-items-center gap-2">
                                            @if ($duty->employee && $duty->employee->photo)
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
                                            <span class="fw-semibold text-dark">{{ $duty->duty_name }}</span>
                                            @if ($duty->description)
                                                <small class="text-muted">{{ Str::limit($duty->description, 40) }}</small>
                                            @endif
                                        </div>
                                    </td>
                                    <td>
                                        <span class="badge bg-info-subtle text-info">
                                            {{ ucfirst($duty->kpi_type) }}
                                        </span>
                                    </td>
                                    <td class="text-center">
                                        @if ($duty->target_value)
                                            <span class="fw-semibold">{{ number_format($duty->target_value, 2) }}</span>
                                            @if ($duty->target_unit)
                                                <small class="text-muted d-block">{{ $duty->target_unit }}</small>
                                            @endif
                                        @else
                                            <span class="text-muted">-</span>
                                        @endif
                                    </td>
                                    <td class="text-center">
                                        @if ($duty->weight)
                                            <span class="fw-semibold">{{ number_format($duty->weight, 2) }}%</span>
                                        @else
                                            <span class="text-muted">-</span>
                                        @endif
                                    </td>
                                    <td class="text-center">
                                        @php
                                            $priorityColors = [
                                                'low' => 'secondary',
                                                'medium' => 'info',
                                                'high' => 'warning',
                                                'urgent' => 'danger',
                                            ];
                                            $priorityColor = $priorityColors[$duty->priority] ?? 'secondary';
                                            $priorityIcons = [
                                                'low' => 'fa-arrow-down',
                                                'medium' => 'fa-minus',
                                                'high' => 'fa-arrow-up',
                                                'urgent' => 'fa-exclamation-triangle',
                                            ];
                                            $priorityIcon = $priorityIcons[$duty->priority] ?? 'fa-minus';
                                        @endphp
                                        <span class="badge bg-{{ $priorityColor }}">
                                            <i class="fa-solid {{ $priorityIcon }} me-1"></i>
                                            {{ ucfirst($duty->priority) }}
                                        </span>
                                    </td>
                                    <td class="text-center">
                                        @php
                                            $statusColors = [
                                                'assigned' => 'secondary',
                                                'in_progress' => 'primary',
                                                'completed' => 'success',
                                                'cancelled' => 'danger',
                                            ];
                                            $statusColor = $statusColors[$duty->status] ?? 'secondary';
                                        @endphp
                                        <div class="dropdown">
                                            <button class="badge bg-{{ $statusColor }} border-0 dropdown-toggle"
                                                type="button" data-bs-toggle="dropdown" style="cursor: pointer;">
                                                {{ ucfirst(str_replace('_', ' ', $duty->status)) }}
                                            </button>
                                            <ul class="dropdown-menu dropdown-menu-end">
                                                <li><a class="dropdown-item" href="#" wire:click.prevent="updateStatus('{{ $duty->id }}', 'assigned')">
                                                    <i class="fa-solid fa-circle text-secondary me-2" style="font-size: 8px;"></i> Assigned
                                                </a></li>
                                                <li><a class="dropdown-item" href="#" wire:click.prevent="updateStatus('{{ $duty->id }}', 'in_progress')">
                                                    <i class="fa-solid fa-circle text-primary me-2" style="font-size: 8px;"></i> In Progress
                                                </a></li>
                                                <li><a class="dropdown-item" href="#" wire:click.prevent="updateStatus('{{ $duty->id }}', 'completed')">
                                                    <i class="fa-solid fa-circle text-success me-2" style="font-size: 8px;"></i> Completed
                                                </a></li>
                                                <li><a class="dropdown-item" href="#" wire:click.prevent="updateStatus('{{ $duty->id }}', 'cancelled')">
                                                    <i class="fa-solid fa-circle text-danger me-2" style="font-size: 8px;"></i> Cancelled
                                                </a></li>
                                            </ul>
                                        </div>
                                    </td>
                                    <td>
                                        @if ($duty->start_date || $duty->end_date)
                                            <small class="text-muted">
                                                @if ($duty->start_date)
                                                    {{ $duty->start_date->format('d M Y') }}
                                                @endif
                                                @if ($duty->start_date && $duty->end_date)
                                                    -
                                                @endif
                                                @if ($duty->end_date)
                                                    {{ $duty->end_date->format('d M Y') }}
                                                @endif
                                            </small>
                                        @else
                                            <span class="text-muted">-</span>
                                        @endif
                                    </td>
                                    <td>
                                        <div class="d-flex gap-1 justify-content-end">
                                            <button wire:click="editDuty('{{ $duty->id }}')"
                                                class="btn btn-sm btn-ghost-secondary rounded-circle"
                                                title="Edit">
                                                <i class="fa-solid fa-pen-to-square"></i>
                                            </button>
                                            @if ($duty->status !== 'completed')
                                                <button wire:click="deleteDuty('{{ $duty->id }}')"
                                                    wire:confirm="Are you sure you want to delete this duty?"
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
                                    <td colspan="10" class="text-center py-5">
                                        <div class="d-flex flex-column align-items-center justify-content-center">
                                            <i class="fa-solid fa-tasks text-muted mb-3"
                                                style="font-size: 48px;"></i>
                                            <h5 class="text-muted">No Assigned Duties Found</h5>
                                            <p class="text-muted">
                                                @if ($search || $employeeFilter || $priorityFilter || $statusFilter)
                                                    Try adjusting your filters or search query
                                                @else
                                                    Start by assigning duties to employees
                                                @endif
                                            </p>
                                            @if (!$search && !$employeeFilter && !$priorityFilter && !$statusFilter)
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
                            @if ($duties->total() > 0)
                                Showing {{ $duties->firstItem() }} to {{ $duties->lastItem() }} of
                                {{ $duties->total() }} assigned duties
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
                            <div>
                                {{ $duties->links() }}
                            </div>
                        </div>
                    </div>
                </div>
            </div>
        </div>
    </div>

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
