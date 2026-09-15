<div>
    <!-- Page Header -->
    <div class="row">
        <div class="col-12">
            <div class="mb-5 d-md-flex justify-content-between align-items-center">
                <div>
                    <h1 class="mb-1 h2">Department Assets</h1>
                    <nav aria-label="breadcrumb">
                        <ol class="breadcrumb mb-0">
                            <li class="breadcrumb-item"><a href="{{ route('dashboard') }}">Dashboard</a></li>
                            <li class="breadcrumb-item active" aria-current="page">Department Assets</li>
                        </ol>
                    </nav>
                </div>
                <button class="btn btn-primary d-flex align-items-center gap-2" wire:click="openModal">
                    <i class="fa-solid fa-plus"></i>
                    Register Asset
                </button>
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

    <!-- Search & Filters -->
    <div class="card mb-4">
        <div class="card-body py-3">
            <div class="row g-3 align-items-center">
                <div class="col-md-3">
                    <div class="input-group">
                        <span class="input-group-text bg-transparent border-end-0">
                            <i class="fa-solid fa-search text-muted"></i>
                        </span>
                        <input type="search" class="form-control border-start-0 ps-0"
                            wire:model.live.debounce.300ms="search"
                            placeholder="Search assets...">
                    </div>
                </div>
                <div class="col-md-3">
                    <select class="form-select" wire:model.live="filterDepartment">
                        <option value="">All Departments</option>
                        @foreach($departments as $dept)
                            <option value="{{ $dept->id }}">{{ $dept->name }}</option>
                        @endforeach
                    </select>
                </div>
                <div class="col-md-2">
                    <select class="form-select" wire:model.live="filterAssetClass">
                        <option value="">All Classes</option>
                        @foreach($assetClasses as $class)
                            <option value="{{ $class->id }}">{{ $class->name }}</option>
                        @endforeach
                    </select>
                </div>
                <div class="col-md-2">
                    <select class="form-select" wire:model.live="filterStatus">
                        <option value="">All Status</option>
                        @foreach($statuses as $value => $label)
                            <option value="{{ $value }}">{{ $label }}</option>
                        @endforeach
                    </select>
                </div>
                <div class="col-md-2 text-end">
                    <span class="text-muted small">
                        {{ $registries->total() }} assets found
                    </span>
                </div>
            </div>
        </div>
    </div>

    <!-- Assets Table -->
    <div class="card">
        <div class="table-responsive">
            <table class="table table-hover mb-0">
                <thead class="table-light">
                    <tr>
                        <th class="ps-4" style="width: 50px;">#</th>
                        <th>Asset</th>
                        <th>Code/Serial</th>
                        <th>Location</th>
                        <th>Department</th>
                        <th>Condition</th>
                        <th>Status</th>
                        <th class="text-end pe-4">Actions</th>
                    </tr>
                </thead>
                <tbody>
                    @forelse($registries as $index => $registry)
                    <tr wire:key="registry-{{ $registry->id }}">
                        <td class="ps-4">{{ $registries->firstItem() + $index }}</td>
                        <td>
                            <div class="d-flex align-items-center gap-3">
                                <div class="bg-primary bg-opacity-10 rounded d-flex align-items-center justify-content-center"
                                    style="width: 40px; height: 40px;">
                                    <i class="fa-solid fa-box text-primary"></i>
                                </div>
                                <div>
                                    <h6 class="mb-0">{{ $registry->asset?->name ?? 'N/A' }}</h6>
                                    <small class="text-muted">{{ $registry->assetClass?->name ?? 'N/A' }}</small>
                                </div>
                            </div>
                        </td>
                        <td>
                            <div>
                                @if($registry->codeno)
                                    <span class="badge bg-dark">{{ $registry->codeno }}</span>
                                @endif
                                @if($registry->serial_number)
                                    <small class="d-block text-muted">S/N: {{ $registry->serial_number }}</small>
                                @endif
                            </div>
                        </td>
                        <td>
                            <div>
                                <span class="d-block">{{ $registry->building?->name ?? 'N/A' }}</span>
                                <small class="text-muted">{{ $registry->facilityLocation?->name ?? '' }}</small>
                            </div>
                        </td>
                        <td>
                            <span class="badge bg-info-subtle text-info">{{ $registry->department?->name ?? 'N/A' }}</span>
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
                                $condColor = $conditionColors[$registry->condition] ?? 'secondary';
                            @endphp
                            @if($registry->condition)
                                <span class="badge bg-{{ $condColor }}-subtle text-{{ $condColor }}">
                                    {{ ucfirst($registry->condition) }}
                                </span>
                            @else
                                <span class="text-muted">-</span>
                            @endif
                        </td>
                        <td>
                            @php
                                $statusColors = [
                                    'active' => 'success',
                                    'inactive' => 'secondary',
                                    'disposed' => 'danger',
                                    'under_maintenance' => 'warning',
                                ];
                                $statusColor = $statusColors[$registry->status] ?? 'secondary';
                            @endphp
                            <span class="badge bg-{{ $statusColor }}">
                                {{ $statuses[$registry->status] ?? ucfirst($registry->status) }}
                            </span>
                        </td>
                        <td class="text-end pe-4">
                            <div class="d-flex justify-content-end gap-2">
                                <button class="btn btn-sm btn-outline-primary"
                                    wire:click="openModal('{{ $registry->id }}')"
                                    title="Edit">
                                    <i class="fa-solid fa-pen-to-square"></i>
                                </button>
                                @can('delete-asset')
                                <button class="btn btn-sm btn-outline-danger"
                                    wire:click="confirmDelete('{{ $registry->id }}')"
                                    title="Delete">
                                    <i class="fa-solid fa-trash"></i>
                                </button>
                                @endcan
                            </div>
                        </td>
                    </tr>
                    @empty
                    <tr>
                        <td colspan="8" class="text-center py-5">
                            <div class="d-flex flex-column align-items-center">
                                <div class="bg-light rounded-circle d-flex align-items-center justify-content-center mb-3"
                                    style="width: 80px; height: 80px;">
                                    <i class="fa-solid fa-boxes-stacked fa-2x text-muted"></i>
                                </div>
                                <h6 class="mb-1">No assets registered</h6>
                                <p class="text-muted mb-3">
                                    @if($search || $filterDepartment || $filterStatus || $filterAssetClass)
                                        No results match your search criteria
                                    @else
                                        Get started by registering your first department asset
                                    @endif
                                </p>
                                @if(!$search && !$filterDepartment && !$filterStatus && !$filterAssetClass)
                                <button class="btn btn-primary btn-sm" wire:click="openModal">
                                    <i class="fa-solid fa-plus me-1"></i> Register Asset
                                </button>
                                @endif
                            </div>
                        </td>
                    </tr>
                    @endforelse
                </tbody>
            </table>
        </div>
        @if($registries->hasPages())
        <div class="card-footer border-top">
            {{ $registries->links() }}
        </div>
        @endif
    </div>

    {{-- Register/Edit Asset Modal --}}
    @if($showModal)
    <div class="modal fade show d-block" tabindex="-1" style="background: rgba(0,0,0,0.5); position: fixed; inset: 0; z-index: 1050; overflow-y: auto;">
        <div class="modal-dialog modal-xl" style="margin: 1.75rem auto;">
            <div class="modal-content">
                <form wire:submit="save">
                    <div class="modal-header border-bottom">
                        <div>
                            <h5 class="modal-title mb-0">
                                <i class="fa-solid fa-box me-2 text-primary"></i>
                                {{ $editingId ? 'Edit Asset' : 'Register New Asset' }}
                            </h5>
                            <small class="text-muted">
                                {{ $editingId ? 'Update asset information' : 'Fill in the details to register a new asset' }}
                            </small>
                        </div>
                        <button type="button" class="btn-close" wire:click="closeModal"></button>
                    </div>

                    <div class="modal-body">
                        <!-- Asset Information -->
                        <div class="mb-4">
                            <h6 class="text-primary mb-3">
                                <i class="fa-solid fa-info-circle me-2"></i>Asset Information
                            </h6>
                            <div class="row g-3">
                                <div class="col-md-4">
                                    <label class="form-label">Asset Class <span class="text-danger">*</span></label>
                                    <select class="form-select @error('asset_class_id') is-invalid @enderror"
                                        wire:model.live="asset_class_id">
                                        <option value="">Select Asset Class</option>
                                        @foreach($assetClasses as $class)
                                            <option value="{{ $class->id }}">{{ $class->name }}</option>
                                        @endforeach
                                    </select>
                                    @error('asset_class_id')
                                        <div class="invalid-feedback">{{ $message }}</div>
                                    @enderror
                                </div>
                                <div class="col-md-4">
                                    <x-forms.search-picker
                                        name="asset_id"
                                        label="Asset"
                                        :required="true"
                                        :selected="$this->selectedAsset"
                                        :items="$this->filteredAssets"
                                        :search-value="$assetSearch"
                                        :show-dropdown="$showAssetDropdown"
                                        search-prop="assetSearch"
                                        dropdown-prop="showAssetDropdown"
                                        :search-placeholder="$asset_class_id ? 'Search asset by name...' : 'Select an asset class first'"
                                        empty-text="No assets found"
                                        :disabled="!$asset_class_id"
                                        select-method="selectAsset"
                                        clear-method="clearAsset"
                                        label-key="name"
                                        sublabel-key="type"
                                        :allow-create="true"
                                        :create-mode="$newAssetMode"
                                        :create-button-text="'Add new asset' . (trim($assetSearch) !== '' ? ' &quot;'.$assetSearch.'&quot;' : '')"
                                        start-create-method="startNewAsset">
                                        <x-slot:create>
                                            <div class="mb-2">
                                                <input type="text"
                                                       class="form-control form-control-sm @error('newAssetName') is-invalid @enderror"
                                                       wire:model.live.debounce.300ms="newAssetName"
                                                       placeholder="New asset name">
                                                @error('newAssetName')
                                                    <div class="invalid-feedback">{{ $message }}</div>
                                                @enderror
                                            </div>
                                            <div class="mb-2">
                                                <select class="form-select form-select-sm @error('newAssetType') is-invalid @enderror"
                                                        wire:model="newAssetType">
                                                    <option value="">Select type</option>
                                                    @foreach($assetTypes as $value => $label)
                                                        <option value="{{ $value }}">{{ $label }}</option>
                                                    @endforeach
                                                </select>
                                                @error('newAssetType')
                                                    <div class="invalid-feedback">{{ $message }}</div>
                                                @enderror
                                            </div>
                                            @if(count($newAssetDuplicates))
                                                <div class="alert alert-warning py-2 px-2 mb-2 small">
                                                    <i class="fa-solid fa-triangle-exclamation me-1"></i>
                                                    Similar assets already exist:
                                                    <ul class="mb-0 ps-3">
                                                        @foreach($newAssetDuplicates as $dup)
                                                            <li>
                                                                <button type="button"
                                                                        class="btn btn-link btn-sm p-0 align-baseline"
                                                                        wire:click="selectAsset('{{ $dup['id'] }}')">
                                                                    {{ $dup['name'] }}
                                                                </button>
                                                                @if($dup['type'])
                                                                    <small class="text-muted">({{ $dup['type'] }})</small>
                                                                @endif
                                                            </li>
                                                        @endforeach
                                                    </ul>
                                                </div>
                                            @endif
                                            <div class="d-flex gap-2">
                                                <button type="button" class="btn btn-sm btn-primary" wire:click="createAsset">
                                                    <i class="fa-solid fa-check me-1"></i> Save
                                                </button>
                                                <button type="button" class="btn btn-sm btn-light" wire:click="cancelNewAsset">
                                                    Cancel
                                                </button>
                                            </div>
                                        </x-slot:create>
                                    </x-forms.search-picker>
                                </div>
                                <div class="col-md-4">
                                    <label class="form-label">Department <span class="text-danger">*</span></label>
                                    <select class="form-select @error('department_id') is-invalid @enderror"
                                        wire:model="department_id">
                                        <option value="">Select Department</option>
                                        @foreach($departments as $dept)
                                            <option value="{{ $dept->id }}">{{ $dept->name }}</option>
                                        @endforeach
                                    </select>
                                    @error('department_id')
                                        <div class="invalid-feedback">{{ $message }}</div>
                                    @enderror
                                </div>
                                <div class="col-md-4">
                                    <label class="form-label">Asset Code</label>
                                    <input type="text" class="form-control @error('codeno') is-invalid @enderror"
                                        wire:model="codeno"
                                        placeholder="e.g., DEPT/ICT/DESK/001">
                                    @error('codeno')
                                        <div class="invalid-feedback">{{ $message }}</div>
                                    @enderror
                                </div>
                                <div class="col-md-4">
                                    <label class="form-label">Serial Number</label>
                                    <input type="text" class="form-control @error('serial_number') is-invalid @enderror"
                                        wire:model="serial_number"
                                        placeholder="Enter serial number">
                                    @error('serial_number')
                                        <div class="invalid-feedback">{{ $message }}</div>
                                    @enderror
                                </div>
                                <div class="col-md-4">
                                    <label class="form-label">Status <span class="text-danger">*</span></label>
                                    <select class="form-select @error('status') is-invalid @enderror"
                                        wire:model="status">
                                        @foreach($statuses as $value => $label)
                                            <option value="{{ $value }}">{{ $label }}</option>
                                        @endforeach
                                    </select>
                                    @error('status')
                                        <div class="invalid-feedback">{{ $message }}</div>
                                    @enderror
                                </div>
                            </div>
                        </div>

                        <!-- Location Information -->
                        <div class="mb-4">
                            <h6 class="text-primary mb-3">
                                <i class="fa-solid fa-location-dot me-2"></i>Location Information
                            </h6>
                            <div class="row g-3">
                                <div class="col-md-4">
                                    <label class="form-label">Workstation <span class="text-danger">*</span></label>
                                    <select class="form-select @error('workstation_id') is-invalid @enderror"
                                        wire:model.live="workstation_id">
                                        <option value="">Select Workstation</option>
                                        @foreach($workstations as $ws)
                                            <option value="{{ $ws->id }}">{{ $ws->workstation_name }}</option>
                                        @endforeach
                                    </select>
                                    @error('workstation_id')
                                        <div class="invalid-feedback">{{ $message }}</div>
                                    @enderror
                                </div>
                                <div class="col-md-4">
                                    <label class="form-label">Building <span class="text-danger">*</span></label>
                                    <select class="form-select @error('building_id') is-invalid @enderror"
                                        wire:model.live="building_id"
                                        @if(!$workstation_id) disabled @endif>
                                        <option value="">Select Building</option>
                                        @foreach($buildings as $bldg)
                                            <option value="{{ $bldg->id }}">{{ $bldg->name }}</option>
                                        @endforeach
                                    </select>
                                    @error('building_id')
                                        <div class="invalid-feedback">{{ $message }}</div>
                                    @enderror
                                </div>
                                <div class="col-md-4">
                                    <label class="form-label">Facility Location <span class="text-danger">*</span></label>
                                    <select class="form-select @error('facility_location_id') is-invalid @enderror"
                                        wire:model="facility_location_id"
                                        @if(!$building_id) disabled @endif>
                                        <option value="">Select Location</option>
                                        @foreach($facilityLocations as $loc)
                                            <option value="{{ $loc->id }}">{{ $loc->name }}</option>
                                        @endforeach
                                    </select>
                                    @error('facility_location_id')
                                        <div class="invalid-feedback">{{ $message }}</div>
                                    @enderror
                                </div>
                            </div>
                        </div>

                        <!-- Physical Details -->
                        <div class="mb-4">
                            <h6 class="text-primary mb-3">
                                <i class="fa-solid fa-cog me-2"></i>Physical Details
                            </h6>
                            <div class="row g-3">
                                <div class="col-md-4">
                                    <label class="form-label">Model</label>
                                    <input type="text" class="form-control @error('model') is-invalid @enderror"
                                        wire:model="model"
                                        placeholder="e.g., Dell Latitude 5520">
                                    @error('model')
                                        <div class="invalid-feedback">{{ $message }}</div>
                                    @enderror
                                </div>
                                <div class="col-md-4">
                                    <label class="form-label">Make/Material</label>
                                    <input type="text" class="form-control @error('make') is-invalid @enderror"
                                        wire:model="make"
                                        placeholder="e.g., Wood, Steel, Aluminum">
                                    @error('make')
                                        <div class="invalid-feedback">{{ $message }}</div>
                                    @enderror
                                </div>
                                <div class="col-md-4">
                                    <label class="form-label">Condition</label>
                                    <select class="form-select @error('condition') is-invalid @enderror"
                                        wire:model="condition">
                                        <option value="">Select Condition</option>
                                        @foreach($conditions as $value => $label)
                                            <option value="{{ $value }}">{{ $label }}</option>
                                        @endforeach
                                    </select>
                                    @error('condition')
                                        <div class="invalid-feedback">{{ $message }}</div>
                                    @enderror
                                </div>
                                <div class="col-12">
                                    <label class="form-label">Description</label>
                                    <textarea class="form-control @error('description') is-invalid @enderror"
                                        wire:model="description"
                                        rows="2"
                                        placeholder="Additional details about the asset..."></textarea>
                                    @error('description')
                                        <div class="invalid-feedback">{{ $message }}</div>
                                    @enderror
                                </div>
                            </div>
                        </div>

                        <!-- Purchase Information -->
                        <div class="mb-4">
                            <h6 class="text-primary mb-3">
                                <i class="fa-solid fa-receipt me-2"></i>Purchase Information
                            </h6>
                            <div class="row g-3">
                                <div class="col-md-3">
                                    <label class="form-label">Purchase Date</label>
                                    <input type="date" class="form-control @error('purchase_date') is-invalid @enderror"
                                        wire:model="purchase_date">
                                    @error('purchase_date')
                                        <div class="invalid-feedback">{{ $message }}</div>
                                    @enderror
                                </div>
                                <div class="col-md-3">
                                    <label class="form-label">Purchase Cost</label>
                                    <input type="number" step="0.01" class="form-control @error('purchase_cost') is-invalid @enderror"
                                        wire:model="purchase_cost"
                                        placeholder="0.00">
                                    @error('purchase_cost')
                                        <div class="invalid-feedback">{{ $message }}</div>
                                    @enderror
                                </div>
                                <div class="col-md-3">
                                    <label class="form-label">Warranty Expiry</label>
                                    <input type="date" class="form-control @error('warranty_expiry_date') is-invalid @enderror"
                                        wire:model="warranty_expiry_date">
                                    @error('warranty_expiry_date')
                                        <div class="invalid-feedback">{{ $message }}</div>
                                    @enderror
                                </div>
                                <div class="col-md-3">
                                    <label class="form-label">Vendor</label>
                                    <select class="form-select @error('vendor_id') is-invalid @enderror"
                                        wire:model="vendor_id">
                                        <option value="">Select Vendor</option>
                                        @foreach($vendors as $v)
                                            <option value="{{ $v->id }}">{{ $v->name }}</option>
                                        @endforeach
                                    </select>
                                    @error('vendor_id')
                                        <div class="invalid-feedback">{{ $message }}</div>
                                    @enderror
                                </div>
                            </div>
                        </div>

                        <!-- Depreciation Information -->
                        <div>
                            <h6 class="text-primary mb-3">
                                <i class="fa-solid fa-chart-line me-2"></i>Depreciation Settings (Override)
                            </h6>
                            <div class="row g-3">
                                <div class="col-md-6">
                                    <label class="form-label">Depreciation Method</label>
                                    <select class="form-select @error('depreciation_method') is-invalid @enderror"
                                        wire:model="depreciation_method">
                                        <option value="">Use Class Default</option>
                                        @foreach($depreciationMethods as $value => $label)
                                            <option value="{{ $value }}">{{ $label }}</option>
                                        @endforeach
                                    </select>
                                    @error('depreciation_method')
                                        <div class="invalid-feedback">{{ $message }}</div>
                                    @enderror
                                    <small class="text-muted">Leave empty to use the asset class default</small>
                                </div>
                                <div class="col-md-6">
                                    <label class="form-label">Useful Life (Years)</label>
                                    <input type="number" class="form-control @error('useful_life_years') is-invalid @enderror"
                                        wire:model="useful_life_years"
                                        placeholder="e.g., 5"
                                        min="1">
                                    @error('useful_life_years')
                                        <div class="invalid-feedback">{{ $message }}</div>
                                    @enderror
                                    <small class="text-muted">Leave empty to use the asset class default</small>
                                </div>
                            </div>
                        </div>
                    </div>

                    <div class="modal-footer border-top">
                        <button type="button" class="btn btn-light" wire:click="closeModal">
                            <i class="fa-solid fa-times me-1"></i> Cancel
                        </button>
                        <button type="submit" class="btn btn-primary" wire:loading.attr="disabled">
                            <span wire:loading.remove wire:target="save">
                                <i class="fa-solid fa-check me-1"></i>
                                {{ $editingId ? 'Update Asset' : 'Register Asset' }}
                            </span>
                            <span wire:loading wire:target="save">
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
                    <h5 class="mb-2">Delete Asset?</h5>
                    <p class="text-muted mb-0">This action cannot be undone. The asset record will be permanently removed.</p>
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
