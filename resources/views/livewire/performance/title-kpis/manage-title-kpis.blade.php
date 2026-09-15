<div class="custom-container">

    <x-pages.breadcrumn title="JOB TITLE KPIs" :breadcrumbs="[
        ['label' => 'Home', 'url' => route('dashboard')],
        ['label' => 'Performance Management', 'url' => '#'],
        ['label' => 'Job Title KPIs', 'url' => route('performance.title.kpis')],
    ]">
        <button class='btn btn-primary d-md-flex align-items-center gap-2' wire:click="openCreateModal">
            <i class="fa-solid fa-plus"></i> ADD NEW KPI
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
                            <p class="text-muted mb-1 small text-uppercase">Total KPIs</p>
                            <h3 class="mb-0 fw-bold">{{ $totalKpis ?? 0 }}</h3>
                        </div>
                        <div class="icon-shape icon-lg bg-primary-subtle text-primary rounded-3">
                            <i class="fa-solid fa-bullseye fa-lg"></i>
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
                            <p class="text-muted mb-1 small text-uppercase">Active KPIs</p>
                            <h3 class="mb-0 fw-bold text-primary">{{ $activeKpis ?? 0 }}</h3>
                        </div>
                        <div class="icon-shape icon-lg bg-primary-subtle text-primary rounded-3">
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
                            <p class="text-muted mb-1 small text-uppercase">Mandatory KPIs</p>
                            <h3 class="mb-0 fw-bold text-success">{{ $mandatoryKpis ?? 0 }}</h3>
                        </div>
                        <div class="icon-shape icon-lg bg-success-subtle text-success rounded-3">
                            <i class="fa-solid fa-star fa-lg"></i>
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
                            <p class="text-muted mb-1 small text-uppercase">Job Titles Covered</p>
                            <h3 class="mb-0 fw-bold text-info">{{ $jobTitlesCovered ?? 0 }}</h3>
                        </div>
                        <div class="icon-shape icon-lg bg-info-subtle text-info rounded-3">
                            <i class="fa-solid fa-briefcase fa-lg"></i>
                        </div>
                    </div>
                </div>
            </div>
        </div>
    </div>

    <!-- Job Title KPIs List Card -->
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
                                    placeholder="Search KPIs by name..." />
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
                                <!-- Job Title Filter -->
                                <select wire:model.live="jobTitleFilter" class="form-select" style="width: auto;">
                                    <option value="">All Job Titles</option>
                                    @foreach ($jobTitles ?? [] as $jobTitle)
                                        <option value="{{ $jobTitle->id }}">{{ $jobTitle->name }}</option>
                                    @endforeach
                                </select>

                                <!-- KPI Type Filter -->
                                <select wire:model.live="typeFilter" class="form-select" style="width: auto;">
                                    <option value="">All KPI Types</option>
                                    <option value="quantitative">Quantitative</option>
                                    <option value="qualitative">Qualitative</option>
                                </select>

                                <!-- Mandatory Filter -->
                                <select wire:model.live="mandatoryFilter" class="form-select" style="width: auto;">
                                    <option value="">All</option>
                                    <option value="1">Mandatory Only</option>
                                    <option value="0">Optional Only</option>
                                </select>

                                <!-- Reset Filters -->
                                @if ($search || $jobTitleFilter || $typeFilter || $mandatoryFilter !== '')
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
                                <th>Job Title</th>
                                <th>KPI Name</th>
                                <th class="text-center">KPI Type</th>
                                <th class="text-center">Measurement</th>
                                <th class="text-center">Weight (%)</th>
                                <th class="text-center">Target</th>
                                <th class="text-center">Mandatory</th>
                                <th class="text-center">Status</th>
                                <th class="text-end" style="width: 120px;">Actions</th>
                            </tr>
                        </thead>
                        <tbody>
                            @forelse ($kpis ?? [] as $index => $kpi)
                                <tr wire:key="kpi-{{ $kpi->id }}">
                                    <td class="text-center text-muted">
                                        {{ $kpis->firstItem() + $index }}
                                    </td>
                                    <td>
                                        <div class="d-flex align-items-center gap-2">
                                            <div class="icon-shape icon-sm bg-primary-subtle text-primary rounded-2">
                                                <i class="fa-solid fa-briefcase"></i>
                                            </div>
                                            <div>
                                                <span class="fw-semibold text-dark">{{ $kpi->jobtitle->name ?? 'N/A' }}</span>
                                            </div>
                                        </div>
                                    </td>
                                    <td>
                                        <div class="d-flex flex-column">
                                            <span class="fw-semibold text-dark">{{ $kpi->kpi_name }}</span>
                                            @if ($kpi->description)
                                                <small class="text-muted">{{ Str::limit($kpi->description, 40) }}</small>
                                            @endif
                                        </div>
                                    </td>
                                    <td class="text-center">
                                        <span class="badge bg-{{ $kpi->kpi_type === 'quantitative' ? 'primary' : 'info' }}-subtle text-{{ $kpi->kpi_type === 'quantitative' ? 'primary' : 'info' }}">
                                            {{ ucfirst($kpi->kpi_type) }}
                                        </span>
                                    </td>
                                    <td class="text-center">
                                        <span class="badge bg-secondary-subtle text-secondary">
                                            {{ ucfirst($kpi->measurement_type) }}
                                        </span>
                                    </td>
                                    <td class="text-center">
                                        <span class="fw-semibold">{{ number_format($kpi->weight, 2) }}%</span>
                                    </td>
                                    <td class="text-center">
                                        @if ($kpi->target_value)
                                            <span class="fw-semibold">{{ number_format($kpi->target_value, 2) }}</span>
                                            @if ($kpi->target_unit)
                                                <small class="text-muted d-block">{{ $kpi->target_unit }}</small>
                                            @endif
                                        @else
                                            <span class="text-muted">-</span>
                                        @endif
                                    </td>
                                    <td class="text-center">
                                        @if ($kpi->is_mandatory)
                                            <span class="badge bg-success">
                                                <i class="fa-solid fa-star me-1"></i> Mandatory
                                            </span>
                                        @else
                                            <span class="badge bg-light text-dark">Optional</span>
                                        @endif
                                    </td>
                                    <td class="text-center">
                                        <button wire:click="toggleActive('{{ $kpi->id }}')"
                                            class="badge border-0 bg-{{ $kpi->is_active ? 'success' : 'danger' }}-subtle text-{{ $kpi->is_active ? 'success' : 'danger' }}"
                                            style="cursor: pointer;">
                                            {{ $kpi->is_active ? 'Active' : 'Inactive' }}
                                        </button>
                                    </td>
                                    <td>
                                        <div class="d-flex gap-1 justify-content-end">
                                            <button wire:click="openEditModal('{{ $kpi->id }}')"
                                                class="btn btn-sm btn-ghost-secondary rounded-circle"
                                                title="Edit">
                                                <i class="fa-solid fa-pen-to-square"></i>
                                            </button>
                                            <button wire:click="delete('{{ $kpi->id }}')"
                                                wire:confirm="Are you sure you want to delete this KPI?"
                                                class="btn btn-sm btn-ghost-danger rounded-circle"
                                                title="Delete">
                                                <i class="fa-solid fa-trash"></i>
                                            </button>
                                        </div>
                                    </td>
                                </tr>
                            @empty
                                <tr>
                                    <td colspan="10" class="text-center py-5">
                                        <div class="d-flex flex-column align-items-center justify-content-center">
                                            <i class="fa-solid fa-bullseye text-muted mb-3"
                                                style="font-size: 48px;"></i>
                                            <h5 class="text-muted">No KPIs Found</h5>
                                            <p class="text-muted">
                                                @if ($search || $jobTitleFilter || $typeFilter || $mandatoryFilter !== '')
                                                    Try adjusting your filters or search query
                                                @else
                                                    Start by defining standard KPIs for job titles
                                                @endif
                                            </p>
                                            @if (!$search && !$jobTitleFilter && !$typeFilter && $mandatoryFilter === '')
                                                <button class="btn btn-primary mt-2" wire:click="openCreateModal">
                                                    <i class="fa-solid fa-plus me-1"></i> Add New KPI
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
                            @if ($kpis->total() > 0)
                                Showing {{ $kpis->firstItem() }} to {{ $kpis->lastItem() }} of
                                {{ $kpis->total() }} KPIs
                            @else
                                No KPIs found
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
                                {{ $kpis->links() }}
                            </div>
                        </div>
                    </div>
                </div>
            </div>
        </div>
    </div>

    <!-- Create/Edit KPI Modal -->
    @if ($showModal)
        <div class="modal fade show d-block" tabindex="-1" style="background-color: rgba(0,0,0,0.5);">
            <div class="modal-dialog modal-lg modal-dialog-centered modal-dialog-scrollable">
                <div class="modal-content">
                    <div class="modal-header">
                        <h5 class="modal-title">
                            <i class="fa-solid fa-bullseye me-2"></i>
                            {{ $modalMode === 'edit' ? 'Edit Job Title KPI' : 'Add New Job Title KPI' }}
                        </h5>
                        <button type="button" class="btn-close" wire:click="closeModal"></button>
                    </div>
                    <form wire:submit.prevent="save">
                        <div class="modal-body" style="max-height: 70vh; overflow-y: auto;">
                            <div class="row g-3">
                                <div class="col-md-6">
                                    <label for="jobTitle" class="form-label">Job Title <span class="text-danger">*</span></label>
                                    <select wire:model="job_title_id" class="form-select @error('job_title_id') is-invalid @enderror" id="jobTitle">
                                        <option value="">Select Job Title</option>
                                        @foreach ($jobTitles ?? [] as $jobTitle)
                                            <option value="{{ $jobTitle->id }}">{{ $jobTitle->name }}</option>
                                        @endforeach
                                    </select>
                                    @error('job_title_id')
                                        <div class="invalid-feedback">{{ $message }}</div>
                                    @enderror
                                </div>

                                <div class="col-md-6">
                                    <label for="kpiType" class="form-label">KPI Type <span class="text-danger">*</span></label>
                                    <select wire:model="kpi_type" class="form-select @error('kpi_type') is-invalid @enderror" id="kpiType">
                                        <option value="quantitative">Quantitative</option>
                                        <option value="qualitative">Qualitative</option>
                                    </select>
                                    @error('kpi_type')
                                        <div class="invalid-feedback">{{ $message }}</div>
                                    @enderror
                                </div>

                                <div class="col-12">
                                    <label for="kpiName" class="form-label">KPI Name <span class="text-danger">*</span></label>
                                    <input type="text" wire:model="kpi_name" class="form-control @error('kpi_name') is-invalid @enderror" id="kpiName" placeholder="Enter KPI name">
                                    @error('kpi_name')
                                        <div class="invalid-feedback">{{ $message }}</div>
                                    @enderror
                                </div>

                                <div class="col-12">
                                    <label for="description" class="form-label">Description</label>
                                    <textarea wire:model="description" class="form-control @error('description') is-invalid @enderror" id="description" rows="2" placeholder="Enter detailed description of the KPI"></textarea>
                                    @error('description')
                                        <div class="invalid-feedback">{{ $message }}</div>
                                    @enderror
                                </div>

                                <div class="col-md-6">
                                    <label for="measurementType" class="form-label">Measurement Type <span class="text-danger">*</span></label>
                                    <select wire:model="measurement_type" class="form-select @error('measurement_type') is-invalid @enderror" id="measurementType">
                                        <option value="numeric">Numeric</option>
                                        <option value="percentage">Percentage</option>
                                        <option value="rating">Rating</option>
                                        <option value="binary">Binary (Yes/No)</option>
                                        <option value="text">Text</option>
                                    </select>
                                    @error('measurement_type')
                                        <div class="invalid-feedback">{{ $message }}</div>
                                    @enderror
                                </div>

                                <div class="col-md-6">
                                    <label for="weight" class="form-label">Weight (%) <span class="text-danger">*</span></label>
                                    <input type="number" wire:model="weight" class="form-control @error('weight') is-invalid @enderror" id="weight" placeholder="0" min="0" max="100" step="0.01">
                                    @error('weight')
                                        <div class="invalid-feedback">{{ $message }}</div>
                                    @enderror
                                </div>

                                <div class="col-md-6">
                                    <label for="targetValue" class="form-label">Target Value</label>
                                    <input type="number" wire:model="target_value" class="form-control @error('target_value') is-invalid @enderror" id="targetValue" placeholder="Enter target value" step="0.01">
                                    @error('target_value')
                                        <div class="invalid-feedback">{{ $message }}</div>
                                    @enderror
                                </div>

                                <div class="col-md-6">
                                    <label for="targetUnit" class="form-label">Target Unit</label>
                                    <input type="text" wire:model="target_unit" class="form-control @error('target_unit') is-invalid @enderror" id="targetUnit" placeholder="e.g., %, count, TZS">
                                    @error('target_unit')
                                        <div class="invalid-feedback">{{ $message }}</div>
                                    @enderror
                                </div>

                                <div class="col-md-6">
                                    <label for="displayOrder" class="form-label">Display Order</label>
                                    <input type="number" wire:model="display_order" class="form-control @error('display_order') is-invalid @enderror" id="displayOrder" placeholder="0" min="0">
                                    @error('display_order')
                                        <div class="invalid-feedback">{{ $message }}</div>
                                    @enderror
                                </div>

                                <div class="col-md-6">
                                    <div class="form-check form-switch mt-4">
                                        <input class="form-check-input" type="checkbox" wire:model="is_mandatory" id="isMandatory">
                                        <label class="form-check-label" for="isMandatory">
                                            <i class="fa-solid fa-star text-warning me-1"></i>
                                            Mandatory KPI
                                        </label>
                                    </div>
                                </div>

                                <div class="col-12">
                                    <label for="scoringCriteria" class="form-label">Scoring Criteria</label>
                                    <textarea wire:model="scoring_criteria" class="form-control @error('scoring_criteria') is-invalid @enderror" id="scoringCriteria" rows="2" placeholder="Define how this KPI will be scored/evaluated"></textarea>
                                    @error('scoring_criteria')
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
                                <i class="fa-solid fa-save me-1"></i> {{ $modalMode === 'edit' ? 'Update KPI' : 'Save KPI' }}
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
