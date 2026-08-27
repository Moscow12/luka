<div>
    <!-- Page Header -->
    <div class="row">
        <div class="col-lg-12 col-md-12 col-12">
            <div class="mb-5 d-md-flex justify-content-between align-items-center">
                <div>
                    <h1 class="mb-1 h2">Asset Configuration</h1>
                    <nav aria-label="breadcrumb">
                        <ol class="breadcrumb mb-0">
                            <li class="breadcrumb-item"><a href="#">Dashboard</a></li>
                            <li class="breadcrumb-item"><a href="#">Settings</a></li>
                            <li class="breadcrumb-item active" aria-current="page">Asset Configuration</li>
                        </ol>
                    </nav>
                </div>
            </div>
        </div>
    </div>

    <!-- Success Message -->
    @if (session()->has('success'))
    <div class="alert alert-success alert-dismissible fade show mb-4" role="alert">
        <i class="fa-solid fa-check-circle me-2"></i>
        {{ session('success') }}
        <button type="button" class="btn-close" data-bs-dismiss="alert" aria-label="Close"></button>
    </div>
    @endif

    <!-- Error Message -->
    @if (session()->has('error'))
    <div class="alert alert-danger alert-dismissible fade show mb-4" role="alert">
        <i class="fa-solid fa-circle-exclamation me-2"></i>
        {{ session('error') }}
        <button type="button" class="btn-close" data-bs-dismiss="alert" aria-label="Close"></button>
    </div>
    @endif

    <!-- Tabs Navigation -->
    <div class="row">
        <div class="col-12">
            <ul class="nav nav-line-bottom mb-4 text-nowrap flex-nowrap overflow-auto" id="assetConfigTab" role="tablist">
                <li class="nav-item">
                    <a class="nav-link py-3 px-4 {{ $activeTab === 'buildings' ? 'active' : '' }}"
                       wire:click.prevent="$set('activeTab', 'buildings')"
                       href="#" role="tab">
                        <i class="fa-solid fa-building me-2"></i>
                        Buildings
                        <span class="badge bg-primary-subtle text-primary ms-2">{{ $buildings->total() }}</span>
                    </a>
                </li>
                <li class="nav-item">
                    <a class="nav-link py-3 px-4 {{ $activeTab === 'locations' ? 'active' : '' }}"
                       wire:click.prevent="$set('activeTab', 'locations')"
                       href="#" role="tab">
                        <i class="fa-solid fa-location-dot me-2"></i>
                        Facility Locations
                        <span class="badge bg-success-subtle text-success ms-2">{{ $locations->total() }}</span>
                    </a>
                </li>
                <li class="nav-item">
                    <a class="nav-link py-3 px-4 {{ $activeTab === 'classes' ? 'active' : '' }}"
                       wire:click.prevent="$set('activeTab', 'classes')"
                       href="#" role="tab">
                        <i class="fa-solid fa-layer-group me-2"></i>
                        Asset Classes
                        <span class="badge bg-warning-subtle text-warning ms-2">{{ $assetClasses->total() }}</span>
                    </a>
                </li>
                <li class="nav-item">
                    <a class="nav-link py-3 px-4 {{ $activeTab === 'assets' ? 'active' : '' }}"
                       wire:click.prevent="$set('activeTab', 'assets')"
                       href="#" role="tab">
                        <i class="fa-solid fa-boxes-stacked me-2"></i>
                        Assets
                        <span class="badge bg-info-subtle text-info ms-2">{{ $assets->total() }}</span>
                    </a>
                </li>
            </ul>

            <!-- Tab Content -->
            <div class="tab-content">
                {{-- Buildings Tab --}}
                @if($activeTab === 'buildings')
                <div class="tab-pane fade show active">
                    <!-- Header -->
                    <div class="d-flex flex-column flex-md-row justify-content-between align-items-start align-items-md-center mb-4 gap-3">
                        <div>
                            <h5 class="mb-1">Buildings</h5>
                            <p class="text-muted mb-0">Manage buildings within your workstations</p>
                        </div>
                        <button class="btn btn-primary d-flex align-items-center gap-2" wire:click="openBuildingModal">
                            <i class="fa-solid fa-plus"></i>
                            Add Building
                        </button>
                    </div>

                    <!-- Search -->
                    <div class="card mb-4">
                        <div class="card-body py-3">
                            <div class="row align-items-center">
                                <div class="col-md-4">
                                    <div class="input-group">
                                        <span class="input-group-text bg-transparent border-end-0">
                                            <i class="fa-solid fa-search text-muted"></i>
                                        </span>
                                        <input type="search" class="form-control border-start-0 ps-0"
                                            wire:model.live.debounce.300ms="searchBuilding"
                                            placeholder="Search buildings...">
                                    </div>
                                </div>
                                <div class="col-md-8 text-md-end mt-3 mt-md-0">
                                    <span class="text-muted">
                                        Showing {{ $buildings->firstItem() ?? 0 }} - {{ $buildings->lastItem() ?? 0 }} of {{ $buildings->total() }} buildings
                                    </span>
                                </div>
                            </div>
                        </div>
                    </div>

                    <!-- Table -->
                    <div class="card">
                        <div class="table-responsive">
                            <table class="table table-hover table-nowrap mb-0">
                                <thead class="table-light">
                                    <tr>
                                        <th class="ps-4" style="width: 50px;">#</th>
                                        <th>Building Name</th>
                                        <th>Workstation</th>
                                        <th>Created</th>
                                        <th class="text-end pe-4">Actions</th>
                                    </tr>
                                </thead>
                                <tbody>
                                    @forelse($buildings as $index => $building)
                                    <tr wire:key="building-{{ $building->id }}">
                                        <td class="ps-4">{{ $buildings->firstItem() + $index }}</td>
                                        <td>
                                            <div class="d-flex align-items-center gap-3">
                                                <div class="bg-primary bg-opacity-10 rounded d-flex align-items-center justify-content-center"
                                                    style="width: 40px; height: 40px;">
                                                    <i class="fa-solid fa-building text-primary"></i>
                                                </div>
                                                <div>
                                                    <h6 class="mb-0">{{ $building->name }}</h6>
                                                </div>
                                            </div>
                                        </td>
                                        <td>
                                            <span class="badge bg-light text-dark">{{ $building->workstation?->workstation_name ?? 'N/A' }}</span>
                                        </td>
                                        <td>
                                            <small class="text-muted">{{ $building->created_at->format('M d, Y') }}</small>
                                        </td>
                                        <td class="text-end pe-4">
                                            <div class="d-flex justify-content-end gap-2">
                                                <button class="btn btn-sm btn-outline-primary"
                                                    wire:click="openBuildingModal('{{ $building->id }}')"
                                                    title="Edit">
                                                    <i class="fa-solid fa-pen-to-square"></i>
                                                </button>
                                                <button class="btn btn-sm btn-outline-danger"
                                                    wire:click="confirmDelete('building', '{{ $building->id }}')"
                                                    title="Delete">
                                                    <i class="fa-solid fa-trash"></i>
                                                </button>
                                            </div>
                                        </td>
                                    </tr>
                                    @empty
                                    <tr>
                                        <td colspan="5" class="text-center py-5">
                                            <div class="d-flex flex-column align-items-center">
                                                <div class="bg-light rounded-circle d-flex align-items-center justify-content-center mb-3"
                                                    style="width: 80px; height: 80px;">
                                                    <i class="fa-solid fa-building fa-2x text-muted"></i>
                                                </div>
                                                <h6 class="mb-1">No buildings found</h6>
                                                <p class="text-muted mb-3">
                                                    @if($searchBuilding)
                                                        No results match your search criteria
                                                    @else
                                                        Get started by adding your first building
                                                    @endif
                                                </p>
                                                @if(!$searchBuilding)
                                                <button class="btn btn-primary btn-sm" wire:click="openBuildingModal">
                                                    <i class="fa-solid fa-plus me-1"></i> Add Building
                                                </button>
                                                @endif
                                            </div>
                                        </td>
                                    </tr>
                                    @endforelse
                                </tbody>
                            </table>
                        </div>
                        @if($buildings->hasPages())
                        <div class="card-footer border-top">
                            {{ $buildings->links() }}
                        </div>
                        @endif
                    </div>
                </div>
                @endif

                {{-- Facility Locations Tab --}}
                @if($activeTab === 'locations')
                <div class="tab-pane fade show active">
                    <!-- Header -->
                    <div class="d-flex flex-column flex-md-row justify-content-between align-items-start align-items-md-center mb-4 gap-3">
                        <div>
                            <h5 class="mb-1">Facility Locations</h5>
                            <p class="text-muted mb-0">Manage specific locations within buildings</p>
                        </div>
                        <button class="btn btn-success d-flex align-items-center gap-2" wire:click="openLocationModal">
                            <i class="fa-solid fa-plus"></i>
                            Add Location
                        </button>
                    </div>

                    <!-- Search -->
                    <div class="card mb-4">
                        <div class="card-body py-3">
                            <div class="row align-items-center">
                                <div class="col-md-4">
                                    <div class="input-group">
                                        <span class="input-group-text bg-transparent border-end-0">
                                            <i class="fa-solid fa-search text-muted"></i>
                                        </span>
                                        <input type="search" class="form-control border-start-0 ps-0"
                                            wire:model.live.debounce.300ms="searchLocation"
                                            placeholder="Search locations...">
                                    </div>
                                </div>
                                <div class="col-md-8 text-md-end mt-3 mt-md-0">
                                    <span class="text-muted">
                                        Showing {{ $locations->firstItem() ?? 0 }} - {{ $locations->lastItem() ?? 0 }} of {{ $locations->total() }} locations
                                    </span>
                                </div>
                            </div>
                        </div>
                    </div>

                    <!-- Table -->
                    <div class="card">
                        <div class="table-responsive">
                            <table class="table table-hover table-nowrap mb-0">
                                <thead class="table-light">
                                    <tr>
                                        <th class="ps-4" style="width: 50px;">#</th>
                                        <th>Location Name</th>
                                        <th>Building</th>
                                        <th>Workstation</th>
                                        <th>Created</th>
                                        <th class="text-end pe-4">Actions</th>
                                    </tr>
                                </thead>
                                <tbody>
                                    @forelse($locations as $index => $location)
                                    <tr wire:key="location-{{ $location->id }}">
                                        <td class="ps-4">{{ $locations->firstItem() + $index }}</td>
                                        <td>
                                            <div class="d-flex align-items-center gap-3">
                                                <div class="bg-success bg-opacity-10 rounded d-flex align-items-center justify-content-center"
                                                    style="width: 40px; height: 40px;">
                                                    <i class="fa-solid fa-location-dot text-success"></i>
                                                </div>
                                                <div>
                                                    <h6 class="mb-0">{{ $location->name }}</h6>
                                                </div>
                                            </div>
                                        </td>
                                        <td>
                                            <span class="badge bg-primary-subtle text-primary">{{ $location->building?->name ?? 'N/A' }}</span>
                                        </td>
                                        <td>
                                            <span class="badge bg-light text-dark">{{ $location->workstation?->workstation_name ?? 'N/A' }}</span>
                                        </td>
                                        <td>
                                            <small class="text-muted">{{ $location->created_at->format('M d, Y') }}</small>
                                        </td>
                                        <td class="text-end pe-4">
                                            <div class="d-flex justify-content-end gap-2">
                                                <button class="btn btn-sm btn-outline-primary"
                                                    wire:click="openLocationModal('{{ $location->id }}')"
                                                    title="Edit">
                                                    <i class="fa-solid fa-pen-to-square"></i>
                                                </button>
                                                <button class="btn btn-sm btn-outline-danger"
                                                    wire:click="confirmDelete('location', '{{ $location->id }}')"
                                                    title="Delete">
                                                    <i class="fa-solid fa-trash"></i>
                                                </button>
                                            </div>
                                        </td>
                                    </tr>
                                    @empty
                                    <tr>
                                        <td colspan="6" class="text-center py-5">
                                            <div class="d-flex flex-column align-items-center">
                                                <div class="bg-light rounded-circle d-flex align-items-center justify-content-center mb-3"
                                                    style="width: 80px; height: 80px;">
                                                    <i class="fa-solid fa-location-dot fa-2x text-muted"></i>
                                                </div>
                                                <h6 class="mb-1">No facility locations found</h6>
                                                <p class="text-muted mb-3">
                                                    @if($searchLocation)
                                                        No results match your search criteria
                                                    @else
                                                        Get started by adding your first facility location
                                                    @endif
                                                </p>
                                                @if(!$searchLocation)
                                                <button class="btn btn-success btn-sm" wire:click="openLocationModal">
                                                    <i class="fa-solid fa-plus me-1"></i> Add Location
                                                </button>
                                                @endif
                                            </div>
                                        </td>
                                    </tr>
                                    @endforelse
                                </tbody>
                            </table>
                        </div>
                        @if($locations->hasPages())
                        <div class="card-footer border-top">
                            {{ $locations->links() }}
                        </div>
                        @endif
                    </div>
                </div>
                @endif

                {{-- Asset Classes Tab --}}
                @if($activeTab === 'classes')
                <div class="tab-pane fade show active">
                    <!-- Header -->
                    <div class="d-flex flex-column flex-md-row justify-content-between align-items-start align-items-md-center mb-4 gap-3">
                        <div>
                            <h5 class="mb-1">Asset Classes</h5>
                            <p class="text-muted mb-0">Define asset classifications and depreciation rates</p>
                        </div>
                        <button class="btn btn-warning d-flex align-items-center gap-2" wire:click="openClassModal">
                            <i class="fa-solid fa-plus"></i>
                            Add Asset Class
                        </button>
                    </div>

                    <!-- Search -->
                    <div class="card mb-4">
                        <div class="card-body py-3">
                            <div class="row align-items-center">
                                <div class="col-md-4">
                                    <div class="input-group">
                                        <span class="input-group-text bg-transparent border-end-0">
                                            <i class="fa-solid fa-search text-muted"></i>
                                        </span>
                                        <input type="search" class="form-control border-start-0 ps-0"
                                            wire:model.live.debounce.300ms="searchClass"
                                            placeholder="Search asset classes...">
                                    </div>
                                </div>
                                <div class="col-md-8 text-md-end mt-3 mt-md-0">
                                    <span class="text-muted">
                                        Showing {{ $assetClasses->firstItem() ?? 0 }} - {{ $assetClasses->lastItem() ?? 0 }} of {{ $assetClasses->total() }} classes
                                    </span>
                                </div>
                            </div>
                        </div>
                    </div>

                    <!-- Table -->
                    <div class="card">
                        <div class="table-responsive">
                            <table class="table table-hover table-nowrap mb-0">
                                <thead class="table-light">
                                    <tr>
                                        <th class="ps-4" style="width: 50px;">#</th>
                                        <th>Class Name</th>
                                        <th>Method</th>
                                        <th>Rate / Life</th>
                                        <th>Assets</th>
                                        <th class="text-end pe-4">Actions</th>
                                    </tr>
                                </thead>
                                <tbody>
                                    @forelse($assetClasses as $index => $class)
                                    <tr wire:key="class-{{ $class->id }}">
                                        <td class="ps-4">{{ $assetClasses->firstItem() + $index }}</td>
                                        <td>
                                            <div class="d-flex align-items-center gap-3">
                                                <div class="bg-warning bg-opacity-10 rounded d-flex align-items-center justify-content-center"
                                                    style="width: 40px; height: 40px;">
                                                    <i class="fa-solid fa-layer-group text-warning"></i>
                                                </div>
                                                <div>
                                                    <h6 class="mb-0">{{ $class->name }}</h6>
                                                    @if($class->depreciation)
                                                        <small class="text-muted">{{ $class->depreciation }}</small>
                                                    @endif
                                                </div>
                                            </div>
                                        </td>
                                        <td>
                                            <span class="badge bg-{{ $class->depreciation_method === 'straight_line' ? 'primary' : 'success' }}-subtle text-{{ $class->depreciation_method === 'straight_line' ? 'primary' : 'success' }}">
                                                {{ $class->depreciation_method === 'straight_line' ? 'Straight Line' : 'Reducing Balance' }}
                                            </span>
                                        </td>
                                        <td>
                                            <div>
                                                @if($class->depreciation_rate)
                                                    <span class="badge bg-info">{{ $class->depreciation_rate }}%</span>
                                                @endif
                                                @if($class->useful_life_years)
                                                    <span class="badge bg-secondary">{{ $class->useful_life_years }} yrs</span>
                                                @endif
                                            </div>
                                        </td>
                                        <td>
                                            <span class="badge bg-dark">{{ $class->assets_count }}</span>
                                        </td>
                                        <td class="text-end pe-4">
                                            <div class="d-flex justify-content-end gap-2">
                                                <button class="btn btn-sm btn-outline-primary"
                                                    wire:click="openClassModal('{{ $class->id }}')"
                                                    title="Edit">
                                                    <i class="fa-solid fa-pen-to-square"></i>
                                                </button>
                                                <button class="btn btn-sm btn-outline-danger"
                                                    wire:click="confirmDelete('class', '{{ $class->id }}')"
                                                    title="Delete">
                                                    <i class="fa-solid fa-trash"></i>
                                                </button>
                                            </div>
                                        </td>
                                    </tr>
                                    @empty
                                    <tr>
                                        <td colspan="6" class="text-center py-5">
                                            <div class="d-flex flex-column align-items-center">
                                                <div class="bg-light rounded-circle d-flex align-items-center justify-content-center mb-3"
                                                    style="width: 80px; height: 80px;">
                                                    <i class="fa-solid fa-layer-group fa-2x text-muted"></i>
                                                </div>
                                                <h6 class="mb-1">No asset classes found</h6>
                                                <p class="text-muted mb-3">
                                                    @if($searchClass)
                                                        No results match your search criteria
                                                    @else
                                                        Get started by adding your first asset class
                                                    @endif
                                                </p>
                                                @if(!$searchClass)
                                                <button class="btn btn-warning btn-sm" wire:click="openClassModal">
                                                    <i class="fa-solid fa-plus me-1"></i> Add Asset Class
                                                </button>
                                                @endif
                                            </div>
                                        </td>
                                    </tr>
                                    @endforelse
                                </tbody>
                            </table>
                        </div>
                        @if($assetClasses->hasPages())
                        <div class="card-footer border-top">
                            {{ $assetClasses->links() }}
                        </div>
                        @endif
                    </div>
                </div>
                @endif

                {{-- Assets Tab --}}
                @if($activeTab === 'assets')
                <div class="tab-pane fade show active">
                    <!-- Header -->
                    <div class="d-flex flex-column flex-md-row justify-content-between align-items-start align-items-md-center mb-4 gap-3">
                        <div>
                            <h5 class="mb-1">Assets</h5>
                            <p class="text-muted mb-0">Manage your organization's asset definitions</p>
                        </div>
                        <button class="btn btn-info d-flex align-items-center gap-2 text-white" wire:click="openAssetModal">
                            <i class="fa-solid fa-plus"></i>
                            Add Asset
                        </button>
                    </div>

                    <!-- Search -->
                    <div class="card mb-4">
                        <div class="card-body py-3">
                            <div class="row align-items-center">
                                <div class="col-md-4">
                                    <div class="input-group">
                                        <span class="input-group-text bg-transparent border-end-0">
                                            <i class="fa-solid fa-search text-muted"></i>
                                        </span>
                                        <input type="search" class="form-control border-start-0 ps-0"
                                            wire:model.live.debounce.300ms="searchAsset"
                                            placeholder="Search assets...">
                                    </div>
                                </div>
                                <div class="col-md-8 text-md-end mt-3 mt-md-0">
                                    <span class="text-muted">
                                        Showing {{ $assets->firstItem() ?? 0 }} - {{ $assets->lastItem() ?? 0 }} of {{ $assets->total() }} assets
                                    </span>
                                </div>
                            </div>
                        </div>
                    </div>

                    <!-- Table -->
                    <div class="card">
                        <div class="table-responsive">
                            <table class="table table-hover table-nowrap mb-0">
                                <thead class="table-light">
                                    <tr>
                                        <th class="ps-4" style="width: 50px;">#</th>
                                        <th>Asset Name</th>
                                        <th>Type</th>
                                        <th>Asset Class</th>
                                        <th>Created</th>
                                        <th class="text-end pe-4">Actions</th>
                                    </tr>
                                </thead>
                                <tbody>
                                    @forelse($assets as $index => $asset)
                                    <tr wire:key="asset-{{ $asset->id }}">
                                        <td class="ps-4">{{ $assets->firstItem() + $index }}</td>
                                        <td>
                                            <div class="d-flex align-items-center gap-3">
                                                <div class="bg-info bg-opacity-10 rounded d-flex align-items-center justify-content-center"
                                                    style="width: 40px; height: 40px;">
                                                    <i class="fa-solid fa-boxes-stacked text-info"></i>
                                                </div>
                                                <div>
                                                    <h6 class="mb-0">{{ $asset->name }}</h6>
                                                </div>
                                            </div>
                                        </td>
                                        <td>
                                            @php
                                                $typeColors = [
                                                    'current' => 'success',
                                                    'non-current' => 'warning',
                                                    'intangible' => 'info',
                                                    'physical' => 'primary',
                                                    'operating' => 'secondary',
                                                    'non-operating' => 'dark',
                                                ];
                                                $color = $typeColors[$asset->type] ?? 'secondary';
                                            @endphp
                                            <span class="badge bg-{{ $color }}-subtle text-{{ $color }}">
                                                {{ $assetTypes[$asset->type] ?? ucfirst($asset->type) }}
                                            </span>
                                        </td>
                                        <td>
                                            <span class="badge bg-warning-subtle text-warning">{{ $asset->asset_class?->name ?? 'N/A' }}</span>
                                        </td>
                                        <td>
                                            <small class="text-muted">{{ $asset->created_at->format('M d, Y') }}</small>
                                        </td>
                                        <td class="text-end pe-4">
                                            <div class="d-flex justify-content-end gap-2">
                                                <button class="btn btn-sm btn-outline-primary"
                                                    wire:click="openAssetModal('{{ $asset->id }}')"
                                                    title="Edit">
                                                    <i class="fa-solid fa-pen-to-square"></i>
                                                </button>
                                                <button class="btn btn-sm btn-outline-danger"
                                                    wire:click="confirmDelete('asset', '{{ $asset->id }}')"
                                                    title="Delete">
                                                    <i class="fa-solid fa-trash"></i>
                                                </button>
                                            </div>
                                        </td>
                                    </tr>
                                    @empty
                                    <tr>
                                        <td colspan="6" class="text-center py-5">
                                            <div class="d-flex flex-column align-items-center">
                                                <div class="bg-light rounded-circle d-flex align-items-center justify-content-center mb-3"
                                                    style="width: 80px; height: 80px;">
                                                    <i class="fa-solid fa-boxes-stacked fa-2x text-muted"></i>
                                                </div>
                                                <h6 class="mb-1">No assets found</h6>
                                                <p class="text-muted mb-3">
                                                    @if($searchAsset)
                                                        No results match your search criteria
                                                    @else
                                                        Get started by adding your first asset
                                                    @endif
                                                </p>
                                                @if(!$searchAsset)
                                                <button class="btn btn-info btn-sm text-white" wire:click="openAssetModal">
                                                    <i class="fa-solid fa-plus me-1"></i> Add Asset
                                                </button>
                                                @endif
                                            </div>
                                        </td>
                                    </tr>
                                    @endforelse
                                </tbody>
                            </table>
                        </div>
                        @if($assets->hasPages())
                        <div class="card-footer border-top">
                            {{ $assets->links() }}
                        </div>
                        @endif
                    </div>
                </div>
                @endif
            </div>
        </div>
    </div>

    {{-- Building Modal --}}
    @if($showBuildingModal)
    <div class="modal fade show d-block" tabindex="-1" style="background: rgba(0,0,0,0.5); position: fixed; inset: 0; z-index: 1050; overflow-y: auto;">
        <div class="modal-dialog modal-dialog-centered">
            <div class="modal-content">
                <form wire:submit="saveBuilding">
                    <div class="modal-header border-bottom">
                        <div>
                            <h5 class="modal-title mb-0">
                                <i class="fa-solid fa-building me-2 text-primary"></i>
                                {{ $editingBuildingId ? 'Edit Building' : 'Add New Building' }}
                            </h5>
                            <small class="text-muted">
                                {{ $editingBuildingId ? 'Update building information' : 'Fill in the details to add a new building' }}
                            </small>
                        </div>
                        <button type="button" class="btn-close" wire:click="closeModal"></button>
                    </div>

                    <div class="modal-body">
                        <div class="mb-3">
                            <label class="form-label">Building Name <span class="text-danger">*</span></label>
                            <input type="text" class="form-control @error('building_name') is-invalid @enderror"
                                wire:model="building_name"
                                placeholder="Enter building name">
                            @error('building_name')
                                <div class="invalid-feedback">{{ $message }}</div>
                            @enderror
                        </div>

                        <div class="mb-3">
                            <label class="form-label">Workstation <span class="text-danger">*</span></label>
                            <select class="form-select @error('building_workstation_id') is-invalid @enderror"
                                wire:model="building_workstation_id">
                                <option value="">Select Workstation</option>
                                @foreach($workstations as $ws)
                                    <option value="{{ $ws->id }}">{{ $ws->workstation_name }}</option>
                                @endforeach
                            </select>
                            @error('building_workstation_id')
                                <div class="invalid-feedback">{{ $message }}</div>
                            @enderror
                        </div>
                    </div>

                    <div class="modal-footer border-top">
                        <button type="button" class="btn btn-light" wire:click="closeModal">
                            <i class="fa-solid fa-times me-1"></i> Cancel
                        </button>
                        <button type="submit" class="btn btn-primary" wire:loading.attr="disabled">
                            <span wire:loading.remove wire:target="saveBuilding">
                                <i class="fa-solid fa-check me-1"></i>
                                {{ $editingBuildingId ? 'Update Building' : 'Create Building' }}
                            </span>
                            <span wire:loading wire:target="saveBuilding">
                                <span class="spinner-border spinner-border-sm me-1"></span>
                                Saving...
                            </span>
                        </button>
                    </div>
                </form>
            </div>
        </div>
    </div>
    @endif

    {{-- Facility Location Modal --}}
    @if($showLocationModal)
    <div class="modal fade show d-block" tabindex="-1" style="background: rgba(0,0,0,0.5); position: fixed; inset: 0; z-index: 1050; overflow-y: auto;">
        <div class="modal-dialog modal-dialog-centered">
            <div class="modal-content">
                <form wire:submit="saveLocation">
                    <div class="modal-header border-bottom">
                        <div>
                            <h5 class="modal-title mb-0">
                                <i class="fa-solid fa-location-dot me-2 text-success"></i>
                                {{ $editingLocationId ? 'Edit Facility Location' : 'Add New Facility Location' }}
                            </h5>
                            <small class="text-muted">
                                {{ $editingLocationId ? 'Update location information' : 'Fill in the details to add a new location' }}
                            </small>
                        </div>
                        <button type="button" class="btn-close" wire:click="closeModal"></button>
                    </div>

                    <div class="modal-body">
                        <div class="mb-3">
                            <label class="form-label">Location Name <span class="text-danger">*</span></label>
                            <input type="text" class="form-control @error('location_name') is-invalid @enderror"
                                wire:model="location_name"
                                placeholder="e.g., Room 101, Floor 2, Storage Area">
                            @error('location_name')
                                <div class="invalid-feedback">{{ $message }}</div>
                            @enderror
                        </div>

                        <div class="mb-3">
                            <label class="form-label">Building <span class="text-danger">*</span></label>
                            <select class="form-select @error('location_building_id') is-invalid @enderror"
                                wire:model="location_building_id">
                                <option value="">Select Building</option>
                                @foreach($buildingsForSelect as $b)
                                    <option value="{{ $b->id }}">{{ $b->name }}</option>
                                @endforeach
                            </select>
                            @error('location_building_id')
                                <div class="invalid-feedback">{{ $message }}</div>
                            @enderror
                        </div>

                        <div class="mb-3">
                            <label class="form-label">Workstation <span class="text-danger">*</span></label>
                            <select class="form-select @error('location_workstation_id') is-invalid @enderror"
                                wire:model="location_workstation_id">
                                <option value="">Select Workstation</option>
                                @foreach($workstations as $ws)
                                    <option value="{{ $ws->id }}">{{ $ws->workstation_name }}</option>
                                @endforeach
                            </select>
                            @error('location_workstation_id')
                                <div class="invalid-feedback">{{ $message }}</div>
                            @enderror
                        </div>
                    </div>

                    <div class="modal-footer border-top">
                        <button type="button" class="btn btn-light" wire:click="closeModal">
                            <i class="fa-solid fa-times me-1"></i> Cancel
                        </button>
                        <button type="submit" class="btn btn-success" wire:loading.attr="disabled">
                            <span wire:loading.remove wire:target="saveLocation">
                                <i class="fa-solid fa-check me-1"></i>
                                {{ $editingLocationId ? 'Update Location' : 'Create Location' }}
                            </span>
                            <span wire:loading wire:target="saveLocation">
                                <span class="spinner-border spinner-border-sm me-1"></span>
                                Saving...
                            </span>
                        </button>
                    </div>
                </form>
            </div>
        </div>
    </div>
    @endif

    {{-- Asset Class Modal --}}
    @if($showClassModal)
    <div class="modal fade show d-block" tabindex="-1" style="background: rgba(0,0,0,0.5); position: fixed; inset: 0; z-index: 1050; overflow-y: auto;">
        <div class="modal-dialog modal-dialog-centered modal-lg">
            <div class="modal-content">
                <form wire:submit="saveClass">
                    <div class="modal-header border-bottom">
                        <div>
                            <h5 class="modal-title mb-0">
                                <i class="fa-solid fa-layer-group me-2 text-warning"></i>
                                {{ $editingClassId ? 'Edit Asset Class' : 'Add New Asset Class' }}
                            </h5>
                            <small class="text-muted">
                                {{ $editingClassId ? 'Update asset class information' : 'Define a new asset classification' }}
                            </small>
                        </div>
                        <button type="button" class="btn-close" wire:click="closeModal"></button>
                    </div>

                    <div class="modal-body">
                        <div class="row g-3">
                            <div class="col-md-6">
                                <label class="form-label">Class Name <span class="text-danger">*</span></label>
                                <input type="text" class="form-control @error('class_name') is-invalid @enderror"
                                    wire:model="class_name"
                                    placeholder="e.g., Furniture, Electronics, Vehicles">
                                @error('class_name')
                                    <div class="invalid-feedback">{{ $message }}</div>
                                @enderror
                            </div>

                            <div class="col-md-6">
                                <label class="form-label">Depreciation Method <span class="text-danger">*</span></label>
                                <select class="form-select @error('class_depreciation_method') is-invalid @enderror"
                                    wire:model="class_depreciation_method">
                                    @foreach($depreciationMethods as $value => $label)
                                        <option value="{{ $value }}">{{ $label }}</option>
                                    @endforeach
                                </select>
                                @error('class_depreciation_method')
                                    <div class="invalid-feedback">{{ $message }}</div>
                                @enderror
                            </div>

                            <div class="col-md-4">
                                <label class="form-label">Useful Life (Years)</label>
                                <input type="number" class="form-control @error('class_useful_life_years') is-invalid @enderror"
                                    wire:model="class_useful_life_years"
                                    placeholder="e.g., 5"
                                    min="1">
                                @error('class_useful_life_years')
                                    <div class="invalid-feedback">{{ $message }}</div>
                                @enderror
                            </div>

                            <div class="col-md-4">
                                <label class="form-label">Depreciation Rate (%)</label>
                                <input type="number" step="0.01" class="form-control @error('class_depreciation_rate') is-invalid @enderror"
                                    wire:model="class_depreciation_rate"
                                    placeholder="e.g., 20.00"
                                    min="0"
                                    max="100">
                                @error('class_depreciation_rate')
                                    <div class="invalid-feedback">{{ $message }}</div>
                                @enderror
                            </div>

                            <div class="col-md-4">
                                <label class="form-label">Description</label>
                                <input type="text" class="form-control @error('class_depreciation') is-invalid @enderror"
                                    wire:model="class_depreciation"
                                    placeholder="e.g., Office equipment">
                                @error('class_depreciation')
                                    <div class="invalid-feedback">{{ $message }}</div>
                                @enderror
                            </div>
                        </div>

                        <div class="alert alert-info mt-3 mb-0">
                            <small>
                                <i class="fa-solid fa-info-circle me-1"></i>
                                <strong>Straight Line:</strong> Equal depreciation each year (Cost / Useful Life) |
                                <strong>Reducing Balance:</strong> Percentage of remaining value each year
                            </small>
                        </div>
                    </div>

                    <div class="modal-footer border-top">
                        <button type="button" class="btn btn-light" wire:click="closeModal">
                            <i class="fa-solid fa-times me-1"></i> Cancel
                        </button>
                        <button type="submit" class="btn btn-warning" wire:loading.attr="disabled">
                            <span wire:loading.remove wire:target="saveClass">
                                <i class="fa-solid fa-check me-1"></i>
                                {{ $editingClassId ? 'Update Class' : 'Create Class' }}
                            </span>
                            <span wire:loading wire:target="saveClass">
                                <span class="spinner-border spinner-border-sm me-1"></span>
                                Saving...
                            </span>
                        </button>
                    </div>
                </form>
            </div>
        </div>
    </div>
    @endif

    {{-- Asset Modal --}}
    @if($showAssetModal)
    <div class="modal fade show d-block" tabindex="-1" style="background: rgba(0,0,0,0.5); position: fixed; inset: 0; z-index: 1050; overflow-y: auto;">
        <div class="modal-dialog modal-dialog-centered">
            <div class="modal-content">
                <form wire:submit="saveAsset">
                    <div class="modal-header border-bottom">
                        <div>
                            <h5 class="modal-title mb-0">
                                <i class="fa-solid fa-boxes-stacked me-2 text-info"></i>
                                {{ $editingAssetId ? 'Edit Asset' : 'Add New Asset' }}
                            </h5>
                            <small class="text-muted">
                                {{ $editingAssetId ? 'Update asset information' : 'Define a new asset type' }}
                            </small>
                        </div>
                        <button type="button" class="btn-close" wire:click="closeModal"></button>
                    </div>

                    <div class="modal-body">
                        <div class="mb-3">
                            <label class="form-label">Asset Name <span class="text-danger">*</span></label>
                            <input type="text" class="form-control @error('asset_name') is-invalid @enderror"
                                wire:model="asset_name"
                                placeholder="e.g., Office Desk, Laptop, Company Car">
                            @error('asset_name')
                                <div class="invalid-feedback">{{ $message }}</div>
                            @enderror
                        </div>

                        <div class="mb-3">
                            <label class="form-label">Asset Type <span class="text-danger">*</span></label>
                            <select class="form-select @error('asset_type') is-invalid @enderror"
                                wire:model="asset_type">
                                <option value="">Select Asset Type</option>
                                @foreach($assetTypes as $value => $label)
                                    <option value="{{ $value }}">{{ $label }}</option>
                                @endforeach
                            </select>
                            @error('asset_type')
                                <div class="invalid-feedback">{{ $message }}</div>
                            @enderror
                        </div>

                        <div class="mb-3">
                            <label class="form-label">Asset Class <span class="text-danger">*</span></label>
                            <select class="form-select @error('asset_class_id') is-invalid @enderror"
                                wire:model="asset_class_id">
                                <option value="">Select Asset Class</option>
                                @foreach($assetClassesForSelect as $ac)
                                    <option value="{{ $ac->id }}">{{ $ac->name }} ({{ $ac->depreciation }})</option>
                                @endforeach
                            </select>
                            @error('asset_class_id')
                                <div class="invalid-feedback">{{ $message }}</div>
                            @enderror
                        </div>
                    </div>

                    <div class="modal-footer border-top">
                        <button type="button" class="btn btn-light" wire:click="closeModal">
                            <i class="fa-solid fa-times me-1"></i> Cancel
                        </button>
                        <button type="submit" class="btn btn-info text-white" wire:loading.attr="disabled">
                            <span wire:loading.remove wire:target="saveAsset">
                                <i class="fa-solid fa-check me-1"></i>
                                {{ $editingAssetId ? 'Update Asset' : 'Create Asset' }}
                            </span>
                            <span wire:loading wire:target="saveAsset">
                                <span class="spinner-border spinner-border-sm me-1"></span>
                                Saving...
                            </span>
                        </button>
                    </div>
                </form>
            </div>
        </div>
    </div>
    @endif

    {{-- Delete Confirmation Modal --}}
    @if($confirmingDelete)
    <div class="modal fade show d-block" tabindex="-1" style="background: rgba(0,0,0,0.5); position: fixed; inset: 0; z-index: 1055;">
        <div class="modal-dialog modal-dialog-centered modal-sm">
            <div class="modal-content">
                <div class="modal-body text-center py-4">
                    <div class="bg-danger bg-opacity-10 rounded-circle d-inline-flex align-items-center justify-content-center mb-3"
                        style="width: 60px; height: 60px;">
                        <i class="fa-solid fa-trash fa-lg text-danger"></i>
                    </div>
                    <h5 class="mb-2">Delete {{ ucfirst($deleteType) }}?</h5>
                    <p class="text-muted mb-0">This action cannot be undone. All associated data will be permanently removed.</p>
                </div>
                <div class="modal-footer border-top justify-content-center gap-2">
                    <button type="button" class="btn btn-light" wire:click="cancelDelete">
                        Cancel
                    </button>
                    @can('delete-asset')
                        <button type="button" class="btn btn-danger" wire:click="delete">
                            <i class="fa-solid fa-trash me-1"></i> Delete
                        </button>
                    @endcan
                </div>
            </div>
        </div>
    </div>
    @endif
</div>
