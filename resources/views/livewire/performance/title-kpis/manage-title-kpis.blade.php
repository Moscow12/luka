<div class="custom-container">

    <x-pages.breadcrumn title="JOB TITLE KPIs" :breadcrumbs="[
        ['label' => 'Home', 'url' => route('dashboard')],
        ['label' => 'Performance Management', 'url' => '#'],
        ['label' => 'Job Title KPIs', 'url' => route('performance.title.kpis')],
    ]">
        <button class='btn btn-primary d-md-flex align-items-center gap-2' data-bs-toggle="modal" data-bs-target="#createTitleKpiModal">
            <i class="fa-solid fa-plus"></i> ADD NEW KPI
        </button>
    </x-pages.breadcrumn>

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
                                <!-- Job Title Filter -->
                                <select wire:model.live="jobTitleFilter" class="form-select" style="width: auto;">
                                    <option value="">All Job Titles</option>
                                    @foreach ($jobTitles ?? [] as $jobTitle)
                                        <option value="{{ $jobTitle->id }}">{{ $jobTitle->name }}</option>
                                    @endforeach
                                </select>

                                <!-- KPI Type Filter -->
                                <select wire:model.live="kpiTypeFilter" class="form-select" style="width: auto;">
                                    <option value="">All KPI Types</option>
                                    <option value="quantitative">Quantitative</option>
                                    <option value="qualitative">Qualitative</option>
                                    <option value="behavioral">Behavioral</option>
                                    <option value="project">Project-based</option>
                                </select>

                                <!-- Mandatory Filter -->
                                <select wire:model.live="mandatoryFilter" class="form-select" style="width: auto;">
                                    <option value="">All</option>
                                    <option value="1">Mandatory Only</option>
                                    <option value="0">Optional Only</option>
                                </select>

                                <!-- Reset Filters -->
                                @if ($search ?? false || $jobTitleFilter ?? false || $kpiTypeFilter ?? false || $mandatoryFilter ?? false)
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
                                <th class="text-center">Weight (%)</th>
                                <th class="text-center">Target</th>
                                <th class="text-center">Mandatory</th>
                                <th class="text-center">Status</th>
                                <th class="text-end" style="width: 150px;">Actions</th>
                            </tr>
                        </thead>
                        <tbody>
                            @forelse ($kpis ?? [] as $index => $kpi)
                                <tr wire:key="kpi-{{ $kpi->id ?? $index }}">
                                    <td class="text-center text-muted">
                                        {{ ($kpis->firstItem() ?? 0) + $index }}
                                    </td>
                                    <td>
                                        <div class="d-flex align-items-center gap-2">
                                            <div class="icon-shape icon-sm bg-primary-subtle text-primary rounded-2">
                                                <i class="fa-solid fa-briefcase"></i>
                                            </div>
                                            <div>
                                                <span class="fw-semibold text-dark">{{ $kpi->job_title->name ?? 'N/A' }}</span>
                                                @if (!empty($kpi->job_title->code))
                                                    <small class="text-muted d-block">Code: {{ $kpi->job_title->code }}</small>
                                                @endif
                                            </div>
                                        </div>
                                    </td>
                                    <td>
                                        <div class="d-flex flex-column">
                                            <span class="fw-semibold text-dark">{{ $kpi->kpi_name ?? 'N/A' }}</span>
                                            @if (!empty($kpi->description))
                                                <small class="text-muted">{{ Str::limit($kpi->description, 40) }}</small>
                                            @endif
                                        </div>
                                    </td>
                                    <td class="text-center">
                                        @php
                                            $kpiTypeColors = [
                                                'quantitative' => 'primary',
                                                'qualitative' => 'info',
                                                'behavioral' => 'warning',
                                                'project' => 'success',
                                            ];
                                            $kpiTypeColor = $kpiTypeColors[$kpi->kpi_type ?? 'quantitative'] ?? 'secondary';
                                        @endphp
                                        <span class="badge bg-{{ $kpiTypeColor }}-subtle text-{{ $kpiTypeColor }}-emphasis">
                                            {{ ucfirst($kpi->kpi_type ?? 'N/A') }}
                                        </span>
                                    </td>
                                    <td class="text-center">
                                        <span class="fw-semibold">{{ $kpi->weight ?? 0 }}%</span>
                                    </td>
                                    <td class="text-center">
                                        <span class="fw-semibold">{{ $kpi->target ?? '-' }}</span>
                                        @if (!empty($kpi->unit))
                                            <small class="text-muted d-block">{{ $kpi->unit }}</small>
                                        @endif
                                    </td>
                                    <td class="text-center">
                                        @if ($kpi->is_mandatory ?? false)
                                            <span class="badge bg-success">
                                                <i class="fa-solid fa-star me-1"></i>
                                                Mandatory
                                            </span>
                                        @else
                                            <span class="badge bg-light text-dark">
                                                Optional
                                            </span>
                                        @endif
                                    </td>
                                    <td class="text-center">
                                        @php
                                            $statusColor = ($kpi->is_active ?? true) ? 'success' : 'secondary';
                                        @endphp
                                        <span class="badge bg-{{ $statusColor }}">
                                            <i class="fa-solid fa-circle me-1" style="font-size: 6px;"></i>
                                            {{ ($kpi->is_active ?? true) ? 'Active' : 'Inactive' }}
                                        </span>
                                    </td>
                                    <td>
                                        <div class="d-flex gap-1 justify-content-end">
                                            <button wire:click="viewKpi('{{ $kpi->id ?? '' }}')"
                                                class="btn btn-sm btn-ghost-info rounded-circle"
                                                title="View Details">
                                                <i class="fa-solid fa-eye"></i>
                                            </button>
                                            <button wire:click="editKpi('{{ $kpi->id ?? '' }}')"
                                                class="btn btn-sm btn-ghost-secondary rounded-circle"
                                                title="Edit">
                                                <i class="fa-solid fa-pen-to-square"></i>
                                            </button>
                                            <button wire:click="toggleKpiStatus('{{ $kpi->id ?? '' }}')"
                                                class="btn btn-sm btn-ghost-{{ ($kpi->is_active ?? true) ? 'warning' : 'success' }} rounded-circle"
                                                title="{{ ($kpi->is_active ?? true) ? 'Deactivate' : 'Activate' }}">
                                                <i class="fa-solid fa-{{ ($kpi->is_active ?? true) ? 'pause' : 'play' }}"></i>
                                            </button>
                                            <button wire:click="deleteKpi('{{ $kpi->id ?? '' }}')"
                                                type="button"
                                                class="btn btn-sm btn-ghost-danger rounded-circle"
                                                title="Delete"
                                                onclick="confirm('Are you sure you want to delete this KPI?') || event.stopImmediatePropagation()">
                                                <i class="fa-solid fa-trash"></i>
                                            </button>
                                        </div>
                                    </td>
                                </tr>
                            @empty
                                <tr>
                                    <td colspan="9" class="text-center py-5">
                                        <div class="d-flex flex-column align-items-center justify-content-center">
                                            <i class="fa-solid fa-bullseye text-muted mb-3"
                                                style="font-size: 48px;"></i>
                                            <h5 class="text-muted">No KPIs Found</h5>
                                            <p class="text-muted">
                                                @if ($search ?? false || $jobTitleFilter ?? false || $kpiTypeFilter ?? false || $mandatoryFilter ?? false)
                                                    Try adjusting your filters or search query
                                                @else
                                                    Start by defining standard KPIs for job titles
                                                @endif
                                            </p>
                                            @if (!($search ?? false) && !($jobTitleFilter ?? false) && !($kpiTypeFilter ?? false) && !($mandatoryFilter ?? false))
                                                <button class="btn btn-primary mt-2" data-bs-toggle="modal" data-bs-target="#createTitleKpiModal">
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
                            @if (($kpis->total() ?? 0) > 0)
                                Showing {{ $kpis->firstItem() ?? 0 }} to {{ $kpis->lastItem() ?? 0 }} of
                                {{ $kpis->total() ?? 0 }} KPIs
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
                            @if (isset($kpis) && method_exists($kpis, 'links'))
                                <div>
                                    {{ $kpis->links() }}
                                </div>
                            @endif
                        </div>
                    </div>
                </div>
            </div>
        </div>
    </div>

    <!-- Create/Edit KPI Modal -->
    <x-forms.modal id="createTitleKpiModal" title="{{ $editingKpiId ?? false ? 'Edit Job Title KPI' : 'Add New Job Title KPI' }}" size="modal-lg" :centered="true">
        <form wire:submit.prevent="saveKpi">
            <div class="row g-3">
                <div class="col-md-6">
                    <label for="jobTitle" class="form-label">Job Title <span class="text-danger">*</span></label>
                    <select wire:model="kpiForm.job_title_id" class="form-select @error('kpiForm.job_title_id') is-invalid @enderror" id="jobTitle">
                        <option value="">Select Job Title</option>
                        @foreach ($jobTitles ?? [] as $jobTitle)
                            <option value="{{ $jobTitle->id }}">{{ $jobTitle->name }}</option>
                        @endforeach
                    </select>
                    @error('kpiForm.job_title_id')
                        <div class="invalid-feedback">{{ $message }}</div>
                    @enderror
                </div>

                <div class="col-md-6">
                    <label for="kpiTypeSelect" class="form-label">KPI Type <span class="text-danger">*</span></label>
                    <select wire:model="kpiForm.kpi_type" class="form-select @error('kpiForm.kpi_type') is-invalid @enderror" id="kpiTypeSelect">
                        <option value="">Select KPI Type</option>
                        <option value="quantitative">Quantitative</option>
                        <option value="qualitative">Qualitative</option>
                        <option value="behavioral">Behavioral</option>
                        <option value="project">Project-based</option>
                    </select>
                    @error('kpiForm.kpi_type')
                        <div class="invalid-feedback">{{ $message }}</div>
                    @enderror
                </div>

                <div class="col-12">
                    <label for="kpiName" class="form-label">KPI Name <span class="text-danger">*</span></label>
                    <input type="text" wire:model="kpiForm.kpi_name" class="form-control @error('kpiForm.kpi_name') is-invalid @enderror" id="kpiName" placeholder="Enter KPI name">
                    @error('kpiForm.kpi_name')
                        <div class="invalid-feedback">{{ $message }}</div>
                    @enderror
                </div>

                <div class="col-12">
                    <label for="kpiDescription" class="form-label">Description</label>
                    <textarea wire:model="kpiForm.description" class="form-control @error('kpiForm.description') is-invalid @enderror" id="kpiDescription" rows="3" placeholder="Enter detailed description of the KPI"></textarea>
                    @error('kpiForm.description')
                        <div class="invalid-feedback">{{ $message }}</div>
                    @enderror
                </div>

                <div class="col-md-4">
                    <label for="kpiWeight" class="form-label">Weight (%) <span class="text-danger">*</span></label>
                    <input type="number" wire:model="kpiForm.weight" class="form-control @error('kpiForm.weight') is-invalid @enderror" id="kpiWeight" placeholder="0" min="0" max="100" step="0.1">
                    @error('kpiForm.weight')
                        <div class="invalid-feedback">{{ $message }}</div>
                    @enderror
                </div>

                <div class="col-md-4">
                    <label for="kpiTarget" class="form-label">Target <span class="text-danger">*</span></label>
                    <input type="text" wire:model="kpiForm.target" class="form-control @error('kpiForm.target') is-invalid @enderror" id="kpiTarget" placeholder="Enter target value">
                    @error('kpiForm.target')
                        <div class="invalid-feedback">{{ $message }}</div>
                    @enderror
                </div>

                <div class="col-md-4">
                    <label for="kpiUnit" class="form-label">Unit</label>
                    <input type="text" wire:model="kpiForm.unit" class="form-control @error('kpiForm.unit') is-invalid @enderror" id="kpiUnit" placeholder="e.g., %, units, items">
                    @error('kpiForm.unit')
                        <div class="invalid-feedback">{{ $message }}</div>
                    @enderror
                </div>

                <div class="col-md-6">
                    <label for="measurementMethod" class="form-label">Measurement Method</label>
                    <input type="text" wire:model="kpiForm.measurement_method" class="form-control @error('kpiForm.measurement_method') is-invalid @enderror" id="measurementMethod" placeholder="How is this KPI measured?">
                    @error('kpiForm.measurement_method')
                        <div class="invalid-feedback">{{ $message }}</div>
                    @enderror
                </div>

                <div class="col-md-6">
                    <label for="frequency" class="form-label">Review Frequency</label>
                    <select wire:model="kpiForm.review_frequency" class="form-select @error('kpiForm.review_frequency') is-invalid @enderror" id="frequency">
                        <option value="">Select Frequency</option>
                        <option value="daily">Daily</option>
                        <option value="weekly">Weekly</option>
                        <option value="monthly">Monthly</option>
                        <option value="quarterly">Quarterly</option>
                        <option value="annually">Annually</option>
                    </select>
                    @error('kpiForm.review_frequency')
                        <div class="invalid-feedback">{{ $message }}</div>
                    @enderror
                </div>

                <div class="col-md-6">
                    <div class="form-check form-switch mt-4">
                        <input class="form-check-input" type="checkbox" wire:model="kpiForm.is_mandatory" id="isMandatory">
                        <label class="form-check-label" for="isMandatory">
                            <i class="fa-solid fa-star text-warning me-1"></i>
                            Mandatory KPI
                        </label>
                    </div>
                </div>

                <div class="col-md-6">
                    <div class="form-check form-switch mt-4">
                        <input class="form-check-input" type="checkbox" wire:model="kpiForm.is_active" id="isActive" checked>
                        <label class="form-check-label" for="isActive">
                            Active
                        </label>
                    </div>
                </div>

                <div class="col-12">
                    <label for="criteria" class="form-label">Success Criteria</label>
                    <textarea wire:model="kpiForm.success_criteria" class="form-control @error('kpiForm.success_criteria') is-invalid @enderror" id="criteria" rows="2" placeholder="Define what success looks like for this KPI"></textarea>
                    @error('kpiForm.success_criteria')
                        <div class="invalid-feedback">{{ $message }}</div>
                    @enderror
                </div>
            </div>

            <x-slot name="footer">
                <button type="button" class="btn btn-secondary" data-bs-dismiss="modal">
                    <i class="fa-solid fa-times me-1"></i> Cancel
                </button>
                <button type="submit" class="btn btn-primary">
                    <i class="fa-solid fa-save me-1"></i> Save KPI
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
