<div>
    <!-- Page Header -->
    <div class="row mb-4">
        <div class="col-12">
            <div class="d-flex flex-column flex-md-row justify-content-between align-items-start align-items-md-center gap-3">
                <div>
                    <h1 class="h2 mb-1">Fixed Asset Register</h1>
                    <nav aria-label="breadcrumb">
                        <ol class="breadcrumb mb-0">
                            <li class="breadcrumb-item"><a href="{{ route('dashboard') }}">Dashboard</a></li>
                            <li class="breadcrumb-item"><a href="{{ route('assets.index') }}">Assets</a></li>
                            <li class="breadcrumb-item active">Reports</li>
                        </ol>
                    </nav>
                </div>
                <div class="d-flex gap-2">
                    <button class="btn btn-outline-danger d-flex align-items-center gap-2" wire:click="exportPdf" wire:loading.attr="disabled">
                        <span wire:loading.remove wire:target="exportPdf">
                            <i class="fa-solid fa-file-pdf"></i> Export PDF
                        </span>
                        <span wire:loading wire:target="exportPdf">
                            <span class="spinner-border spinner-border-sm"></span> Generating...
                        </span>
                    </button>
                    <button class="btn btn-outline-success d-flex align-items-center gap-2" wire:click="exportExcel" wire:loading.attr="disabled">
                        <span wire:loading.remove wire:target="exportExcel">
                            <i class="fa-solid fa-file-excel"></i> Export Excel
                        </span>
                        <span wire:loading wire:target="exportExcel">
                            <span class="spinner-border spinner-border-sm"></span> Generating...
                        </span>
                    </button>
                </div>
            </div>
        </div>
    </div>

    <!-- Statistics Cards -->
    <div class="row g-3 mb-4">
        <div class="col-6 col-lg-3">
            <div class="card border-0 shadow-sm h-100">
                <div class="card-body">
                    <div class="d-flex align-items-center">
                        <div class="flex-shrink-0">
                            <div class="bg-primary bg-opacity-10 rounded-3 p-3">
                                <i class="fa-solid fa-boxes-stacked fa-lg text-primary"></i>
                            </div>
                        </div>
                        <div class="flex-grow-1 ms-3">
                            <h3 class="mb-0 fw-bold">{{ number_format($statistics['total_assets']) }}</h3>
                            <p class="text-muted mb-0 small">Total Assets</p>
                        </div>
                    </div>
                </div>
            </div>
        </div>
        <div class="col-6 col-lg-3">
            <div class="card border-0 shadow-sm h-100">
                <div class="card-body">
                    <div class="d-flex align-items-center">
                        <div class="flex-shrink-0">
                            <div class="bg-success bg-opacity-10 rounded-3 p-3">
                                <i class="fa-solid fa-money-bill-wave fa-lg text-success"></i>
                            </div>
                        </div>
                        <div class="flex-grow-1 ms-3">
                            <h3 class="mb-0 fw-bold">{{ number_format($statistics['total_value'], 0) }}</h3>
                            <p class="text-muted mb-0 small">Total Value (TZS)</p>
                        </div>
                    </div>
                </div>
            </div>
        </div>
        <div class="col-6 col-lg-3">
            <div class="card border-0 shadow-sm h-100">
                <div class="card-body">
                    <div class="d-flex align-items-center">
                        <div class="flex-shrink-0">
                            <div class="bg-warning bg-opacity-10 rounded-3 p-3">
                                <i class="fa-solid fa-chart-line fa-lg text-warning"></i>
                            </div>
                        </div>
                        <div class="flex-grow-1 ms-3">
                            <h3 class="mb-0 fw-bold">{{ number_format($statistics['total_depreciation'], 0) }}</h3>
                            <p class="text-muted mb-0 small">Acc. Depreciation</p>
                        </div>
                    </div>
                </div>
            </div>
        </div>
        <div class="col-6 col-lg-3">
            <div class="card border-0 shadow-sm h-100">
                <div class="card-body">
                    <div class="d-flex align-items-center">
                        <div class="flex-shrink-0">
                            <div class="bg-info bg-opacity-10 rounded-3 p-3">
                                <i class="fa-solid fa-calculator fa-lg text-info"></i>
                            </div>
                        </div>
                        <div class="flex-grow-1 ms-3">
                            <h3 class="mb-0 fw-bold">{{ number_format($statistics['net_book_value'], 0) }}</h3>
                            <p class="text-muted mb-0 small">Net Book Value</p>
                        </div>
                    </div>
                </div>
            </div>
        </div>
    </div>

    <!-- Status Overview -->
    <div class="row g-3 mb-4">
        <div class="col-md-8">
            <div class="card border-0 shadow-sm">
                <div class="card-header bg-transparent border-0 pb-0">
                    <h6 class="mb-0">Asset Status Distribution</h6>
                </div>
                <div class="card-body">
                    <div class="row text-center">
                        <div class="col-3">
                            <div class="border rounded p-3">
                                <div class="display-6 fw-bold text-success">{{ $statistics['active_assets'] }}</div>
                                <small class="text-muted">Active</small>
                            </div>
                        </div>
                        <div class="col-3">
                            <div class="border rounded p-3">
                                <div class="display-6 fw-bold text-warning">{{ $statistics['under_maintenance'] }}</div>
                                <small class="text-muted">Maintenance</small>
                            </div>
                        </div>
                        <div class="col-3">
                            <div class="border rounded p-3">
                                <div class="display-6 fw-bold text-danger">{{ $statistics['disposed_assets'] }}</div>
                                <small class="text-muted">Disposed</small>
                            </div>
                        </div>
                        <div class="col-3">
                            <div class="border rounded p-3">
                                <div class="display-6 fw-bold text-secondary">{{ $statistics['total_assets'] - $statistics['active_assets'] - $statistics['under_maintenance'] - $statistics['disposed_assets'] }}</div>
                                <small class="text-muted">Inactive</small>
                            </div>
                        </div>
                    </div>
                </div>
            </div>
        </div>
        <div class="col-md-4">
            <div class="card border-0 shadow-sm h-100">
                <div class="card-header bg-transparent border-0 pb-0">
                    <h6 class="mb-0">Condition Overview</h6>
                </div>
                <div class="card-body">
                    <div class="d-flex justify-content-between align-items-center mb-2">
                        <span class="text-muted small">New</span>
                        <span class="badge bg-success">{{ $statistics['by_condition']['new'] }}</span>
                    </div>
                    <div class="d-flex justify-content-between align-items-center mb-2">
                        <span class="text-muted small">Good</span>
                        <span class="badge bg-primary">{{ $statistics['by_condition']['good'] }}</span>
                    </div>
                    <div class="d-flex justify-content-between align-items-center mb-2">
                        <span class="text-muted small">Fair</span>
                        <span class="badge bg-info">{{ $statistics['by_condition']['fair'] }}</span>
                    </div>
                    <div class="d-flex justify-content-between align-items-center">
                        <span class="text-muted small">Poor/Bad</span>
                        <span class="badge bg-danger">{{ $statistics['by_condition']['poor'] }}</span>
                    </div>
                </div>
            </div>
        </div>
    </div>

    <!-- Filters -->
    <div class="card border-0 shadow-sm mb-4">
        <div class="card-header bg-transparent border-0">
            <div class="d-flex justify-content-between align-items-center">
                <h6 class="mb-0"><i class="fa-solid fa-filter me-2"></i>Report Filters</h6>
                @if(count($activeFilters) > 0)
                <button class="btn btn-sm btn-outline-secondary" wire:click="clearFilters">
                    <i class="fa-solid fa-times me-1"></i> Clear Filters
                </button>
                @endif
            </div>
        </div>
        <div class="card-body">
            <div class="row g-3">
                <div class="col-md-3">
                    <label class="form-label small text-muted">Search</label>
                    <div class="input-group">
                        <span class="input-group-text bg-transparent border-end-0">
                            <i class="fa-solid fa-search text-muted"></i>
                        </span>
                        <input type="search" class="form-control border-start-0 ps-0"
                            wire:model.live.debounce.300ms="search"
                            placeholder="Code, name, serial...">
                    </div>
                </div>
                <div class="col-md-3">
                    <label class="form-label small text-muted">Department</label>
                    <select class="form-select" wire:model.live="filterDepartment">
                        <option value="">All Departments</option>
                        @foreach($departments as $dept)
                            <option value="{{ $dept->id }}">{{ $dept->name }}</option>
                        @endforeach
                    </select>
                </div>
                <div class="col-md-3">
                    <label class="form-label small text-muted">Asset Class</label>
                    <select class="form-select" wire:model.live="filterAssetClass">
                        <option value="">All Classes</option>
                        @foreach($assetClasses as $class)
                            <option value="{{ $class->id }}">{{ $class->name }}</option>
                        @endforeach
                    </select>
                </div>
                <div class="col-md-3">
                    <label class="form-label small text-muted">Workstation</label>
                    <select class="form-select" wire:model.live="filterWorkstation">
                        <option value="">All Workstations</option>
                        @foreach($workstations as $ws)
                            <option value="{{ $ws->id }}">{{ $ws->workstation_name }}</option>
                        @endforeach
                    </select>
                </div>
                <div class="col-md-2">
                    <label class="form-label small text-muted">Status</label>
                    <select class="form-select" wire:model.live="filterStatus">
                        <option value="">All Status</option>
                        @foreach($statuses as $value => $label)
                            <option value="{{ $value }}">{{ $label }}</option>
                        @endforeach
                    </select>
                </div>
                <div class="col-md-2">
                    <label class="form-label small text-muted">Condition</label>
                    <select class="form-select" wire:model.live="filterCondition">
                        <option value="">All Conditions</option>
                        @foreach($conditions as $value => $label)
                            <option value="{{ $value }}">{{ $label }}</option>
                        @endforeach
                    </select>
                </div>
                <div class="col-md-2">
                    <label class="form-label small text-muted">Building</label>
                    <select class="form-select" wire:model.live="filterBuilding">
                        <option value="">All Buildings</option>
                        @foreach($buildings as $bldg)
                            <option value="{{ $bldg->id }}">{{ $bldg->name }}</option>
                        @endforeach
                    </select>
                </div>
                <div class="col-md-3">
                    <label class="form-label small text-muted">Facility Location</label>
                    <select class="form-select" wire:model.live="filterFacilityLocation">
                        <option value="">All Locations</option>
                        @foreach($facilityLocations as $location)
                            <option value="{{ $location->id }}">{{ $location->name }}</option>
                        @endforeach
                    </select>
                </div>
                <div class="col-md-3">
                    <label class="form-label small text-muted">Purchase Date From</label>
                    <input type="date" class="form-control" wire:model.live="filterDateFrom">
                </div>
                <div class="col-md-3">
                    <label class="form-label small text-muted">Purchase Date To</label>
                    <input type="date" class="form-control" wire:model.live="filterDateTo">
                </div>
            </div>

            <!-- Active Filters Display -->
            @if(count($activeFilters) > 0)
            <div class="mt-3 pt-3 border-top">
                <span class="text-muted small me-2">Active filters:</span>
                @foreach($activeFilters as $key => $value)
                <span class="badge bg-primary-subtle text-primary me-1">{{ $key }}: {{ $value }}</span>
                @endforeach
            </div>
            @endif
        </div>
    </div>

    <!-- Asset Register Table -->
    <div class="card border-0 shadow-sm" id="printable-report">
        <div class="card-header bg-transparent border-0">
            <div class="d-flex justify-content-between align-items-center">
                <div>
                    <h5 class="mb-0">Fixed Asset Register Report</h5>
                    <small class="text-muted">Generated: {{ now()->format('F d, Y H:i') }}</small>
                </div>
                <span class="badge bg-dark">{{ $assets->total() }} records</span>
            </div>
        </div>
        <div class="table-responsive" style="max-height: 600px; overflow-y: auto;">
            <table class="table table-hover table-striped mb-0 small">
                <thead class="table-dark" style="position: sticky; top: 0; z-index: 10;">
                    <tr>
                        <th class="ps-3" style="width: 40px;">#</th>
                        <th style="cursor: pointer;" wire:click="sortBy('codeno')">
                            Asset Code
                            @if($sortField === 'codeno')
                                <i class="fa-solid fa-sort-{{ $sortDirection === 'asc' ? 'up' : 'down' }} ms-1"></i>
                            @endif
                        </th>
                        <th>Asset Name</th>
                        <th>Class</th>
                        <th>Serial No.</th>
                        <th>Department</th>
                        <th>Location</th>
                        <th>Status</th>
                        <th>Condition</th>
                        <th style="cursor: pointer;" wire:click="sortBy('purchase_date')">
                            Purchase Date
                            @if($sortField === 'purchase_date')
                                <i class="fa-solid fa-sort-{{ $sortDirection === 'asc' ? 'up' : 'down' }} ms-1"></i>
                            @endif
                        </th>
                        <th class="text-end" style="cursor: pointer;" wire:click="sortBy('purchase_cost')">
                            Cost (TZS)
                            @if($sortField === 'purchase_cost')
                                <i class="fa-solid fa-sort-{{ $sortDirection === 'asc' ? 'up' : 'down' }} ms-1"></i>
                            @endif
                        </th>
                        <th class="text-center">Depr. Method</th>
                        <th class="text-center">Life (Yrs)</th>
                        <th class="text-end">Acc. Depr.</th>
                        <th class="text-end pe-3">NBV</th>
                    </tr>
                </thead>
                <tbody>
                    @forelse($assets as $index => $asset)
                    <tr wire:key="asset-{{ $asset->id }}">
                        <td class="ps-3">{{ $assets->firstItem() + $index }}</td>
                        <td>
                            @if($asset->codeno)
                                <span class="badge bg-dark font-monospace">{{ $asset->codeno }}</span>
                            @else
                                <span class="text-muted">-</span>
                            @endif
                        </td>
                        <td>
                            <div class="fw-medium">{{ $asset->asset?->name ?? '-' }}</div>
                            @if($asset->model)
                                <small class="text-muted">{{ $asset->model }}</small>
                            @endif
                        </td>
                        <td>
                            <span class="badge bg-warning-subtle text-warning">{{ $asset->assetClass?->name ?? '-' }}</span>
                        </td>
                        <td class="font-monospace small">{{ $asset->serial_number ?? '-' }}</td>
                        <td>{{ $asset->department?->name ?? '-' }}</td>
                        <td>
                            <span title="{{ $asset->building?->name }}">{{ $asset->facilityLocation?->name ?? '-' }}</span>
                        </td>
                        <td>
                            @php
                                $statusColors = [
                                    'active' => 'success',
                                    'inactive' => 'secondary',
                                    'disposed' => 'danger',
                                    'under_maintenance' => 'warning',
                                ];
                                $statusColor = $statusColors[$asset->status] ?? 'secondary';
                            @endphp
                            <span class="badge bg-{{ $statusColor }}">{{ ucfirst($asset->status) }}</span>
                        </td>
                        <td>
                            @php
                                $conditionColors = [
                                    'new' => 'success',
                                    'good' => 'primary',
                                    'fair' => 'info',
                                    'bad' => 'warning',
                                    'poor' => 'orange',
                                    'worse' => 'danger',
                                ];
                                $condColor = $conditionColors[$asset->condition] ?? 'secondary';
                            @endphp
                            @if($asset->condition)
                                <span class="badge bg-{{ $condColor }}-subtle text-{{ $condColor }}">{{ ucfirst($asset->condition) }}</span>
                            @else
                                <span class="text-muted">-</span>
                            @endif
                        </td>
                        <td>{{ $asset->purchase_date?->format('d/m/Y') ?? '-' }}</td>
                        <td class="text-end font-monospace">{{ number_format($asset->purchase_cost ?? 0, 2) }}</td>
                        <td class="text-center">
                            @php
                                $effectiveMethod = $this->getEffectiveDepreciationMethod($asset);
                                $hasOverride = $asset->depreciation_method !== null;
                            @endphp
                            <span class="badge bg-{{ $effectiveMethod === 'straight_line' ? 'info' : 'purple' }}-subtle text-{{ $effectiveMethod === 'straight_line' ? 'info' : 'purple' }}"
                                  title="{{ $hasOverride ? 'Asset-level override' : 'From Asset Class' }}">
                                {{ $effectiveMethod === 'straight_line' ? 'SL' : 'RB' }}
                                @if($hasOverride)
                                    <i class="fa-solid fa-asterisk fa-xs"></i>
                                @endif
                            </span>
                        </td>
                        <td class="text-center">
                            @php
                                $effectiveLife = $this->getEffectiveUsefulLife($asset);
                                $hasLifeOverride = $asset->useful_life_years !== null;
                            @endphp
                            <span title="{{ $hasLifeOverride ? 'Asset-level override' : 'From Asset Class' }}">
                                {{ $effectiveLife }}
                                @if($hasLifeOverride)
                                    <i class="fa-solid fa-asterisk fa-xs text-primary"></i>
                                @endif
                            </span>
                        </td>
                        @php
                            $calculatedDepreciation = $this->calculateDepreciation($asset);
                            $netBookValue = ($asset->purchase_cost ?? 0) - $calculatedDepreciation;
                        @endphp
                        <td class="text-end font-monospace text-danger">{{ number_format($calculatedDepreciation, 2) }}</td>
                        <td class="text-end font-monospace fw-medium pe-3">{{ number_format($netBookValue, 2) }}</td>
                    </tr>
                    @empty
                    <tr>
                        <td colspan="15" class="text-center py-5">
                            <div class="d-flex flex-column align-items-center">
                                <div class="bg-light rounded-circle d-flex align-items-center justify-content-center mb-3" style="width: 80px; height: 80px;">
                                    <i class="fa-solid fa-folder-open fa-2x text-muted"></i>
                                </div>
                                <h6 class="mb-1">No assets found</h6>
                                <p class="text-muted mb-0">Try adjusting your filters to find assets</p>
                            </div>
                        </td>
                    </tr>
                    @endforelse
                </tbody>
                @if($assets->count() > 0)
                <tfoot class="table-light fw-bold">
                    <tr>
                        <td colspan="11" class="text-end">Totals:</td>
                        <td colspan="2" class="text-center small text-muted">
                            <i class="fa-solid fa-asterisk fa-xs"></i> = Asset Override
                        </td>
                        <td class="text-end font-monospace text-danger">{{ number_format($statistics['total_depreciation'], 2) }}</td>
                        <td class="text-end font-monospace pe-3">{{ number_format($statistics['net_book_value'], 2) }}</td>
                    </tr>
                </tfoot>
                @endif
            </table>
        </div>
        <div class="card-footer border-top bg-transparent">
            <div class="d-flex flex-column flex-md-row justify-content-between align-items-center gap-3">
                <!-- Results Info -->
                <div class="text-muted">
                    @if ($assets->total() > 0)
                        Showing {{ $assets->firstItem() }} to {{ $assets->lastItem() }} of
                        {{ $assets->total() }} assets
                    @else
                        No assets found
                    @endif
                </div>

                <!-- Rows Per Page Only -->
                <div class="d-flex align-items-center gap-2">
                    <label class="form-label mb-0 text-nowrap small">Rows per page:</label>
                    <select wire:model.live="perPage" class="form-select form-select-sm"
                        style="width: auto;">
                        <option value="50">50</option>
                        <option value="100">100</option>
                        <option value="250">250</option>
                        <option value="999999">All</option>
                    </select>
                </div>
            </div>
        </div>
    </div>

    <!-- Report Footer -->
    <div class="mt-4 text-center text-muted small d-print-block">
        <hr>
        <p class="mb-1">
            <strong>Fixed Asset Register Report</strong> | Generated on {{ now()->format('F d, Y') }} at {{ now()->format('H:i:s') }}
        </p>
        <p class="mb-0">
            This report contains {{ $assets->total() }} asset records with a total value of TZS {{ number_format($statistics['total_value'], 2) }}
        </p>
    </div>

    <style>
        @media print {
            .no-print, .card-footer, nav, .btn, .form-select, .form-control, .input-group {
                display: none !important;
            }
            .card {
                border: none !important;
                box-shadow: none !important;
            }
            .table {
                font-size: 10px !important;
            }
            body {
                print-color-adjust: exact;
                -webkit-print-color-adjust: exact;
            }
        }
    </style>
</div>
