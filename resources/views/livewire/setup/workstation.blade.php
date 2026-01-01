<div>
    <!-- Header -->
    <div class="d-flex flex-column flex-md-row justify-content-between align-items-start align-items-md-center mb-4 gap-3">
        <div>
            <h4 class="mb-1">Workstations</h4>
            <p class="text-muted mb-0">Manage your organization's workstations and branches</p>
        </div>
        <button class="btn btn-primary d-flex align-items-center gap-2" wire:click="openModal('create')">
            <i class="fa-solid fa-plus"></i>
            Add Workstation
        </button>
    </div>

    <!-- Search & Filters -->
    <div class="card mb-4">
        <div class="card-body py-3">
            <div class="row align-items-center">
                <div class="col-md-4">
                    <div class="input-group">
                        <span class="input-group-text bg-transparent border-end-0">
                            <i class="fa-solid fa-search text-muted"></i>
                        </span>
                        <input type="search" class="form-control border-start-0 ps-0"
                            wire:model.live.debounce.300ms="search"
                            placeholder="Search workstations...">
                    </div>
                </div>
                <div class="col-md-8 text-md-end mt-3 mt-md-0">
                    <span class="text-muted">
                        Showing {{ $workstations->firstItem() ?? 0 }} - {{ $workstations->lastItem() ?? 0 }} of {{ $workstations->total() }} workstations
                    </span>
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

    <!-- Workstations Table -->
    <div class="card">
        <div class="table-responsive">
            <table class="table table-hover table-nowrap mb-0">
                <thead class="table-light">
                    <tr>
                        <th class="ps-4" style="width: 50px;">#</th>
                        <th>Workstation</th>
                        <th>Contact</th>
                        <th>Location</th>
                        <th>TIN Number</th>
                        <th class="text-end pe-4">Actions</th>
                    </tr>
                </thead>
                <tbody>
                    @forelse($workstations as $index => $station)
                    <tr wire:key="station-{{ $station->id }}">
                        <td class="ps-4">{{ $workstations->firstItem() + $index }}</td>
                        <td>
                            <div class="d-flex align-items-center gap-3">
                                @if($station->logo)
                                <img src="{{ asset('storage/' . $station->logo) }}"
                                    alt="{{ $station->workstation_name }}"
                                    class="rounded"
                                    style="width: 40px; height: 40px; object-fit: cover;">
                                @else
                                <div class="bg-primary bg-opacity-10 rounded d-flex align-items-center justify-content-center"
                                    style="width: 40px; height: 40px;">
                                    <i class="fa-solid fa-building text-primary"></i>
                                </div>
                                @endif
                                <div>
                                    <h6 class="mb-0">{{ $station->workstation_name }}</h6>
                                    <small class="text-muted">{{ $station->postal_code }}</small>
                                </div>
                            </div>
                        </td>
                        <td>
                            <div>
                                <div class="d-flex align-items-center gap-2 mb-1">
                                    <i class="fa-solid fa-envelope text-muted" style="width: 14px;"></i>
                                    <small>{{ $station->email_address }}</small>
                                </div>
                                <div class="d-flex align-items-center gap-2">
                                    <i class="fa-solid fa-phone text-muted" style="width: 14px;"></i>
                                    <small>{{ $station->phone_number }}</small>
                                </div>
                            </div>
                        </td>
                        <td>
                            <div>
                                <span class="d-block">{{ $station->district?->name }}, {{ $station->region?->name }}</span>
                                <small class="text-muted">{{ $station->ward?->name }}, {{ $station->country?->name }}</small>
                            </div>
                        </td>
                        <td>
                            <span class="badge bg-light text-dark">{{ $station->tin_number }}</span>
                        </td>
                        <td class="text-end pe-4">
                            <div class="d-flex justify-content-end gap-2">
                                <button class="btn btn-sm btn-outline-primary"
                                    wire:click="openModal('edit', '{{ $station->id }}')"
                                    title="Edit">
                                    <i class="fa-solid fa-pen-to-square"></i>
                                </button>
                                <button class="btn btn-sm btn-outline-danger"
                                    wire:click="confirmDelete('{{ $station->id }}')"
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
                                    <i class="fa-solid fa-building fa-2x text-muted"></i>
                                </div>
                                <h6 class="mb-1">No workstations found</h6>
                                <p class="text-muted mb-3">
                                    @if($search)
                                        No results match your search criteria
                                    @else
                                        Get started by adding your first workstation
                                    @endif
                                </p>
                                @if(!$search)
                                <button class="btn btn-primary btn-sm" wire:click="openModal('create')">
                                    <i class="fa-solid fa-plus me-1"></i> Add Workstation
                                </button>
                                @endif
                            </div>
                        </td>
                    </tr>
                    @endforelse
                </tbody>
            </table>
        </div>

        @if($workstations->hasPages())
        <div class="card-footer border-top">
            {{ $workstations->links() }}
        </div>
        @endif
    </div>

    <!-- Create/Edit Modal -->
    @if($showModal)
    <div class="modal fade show d-block" tabindex="-1" style="background: rgba(0,0,0,0.5); position: fixed; inset: 0; z-index: 1050; overflow-y: auto;">
        <div class="modal-dialog modal-xl" style="margin: 1.75rem auto; max-width: 1140px;">
            <div class="modal-content">
                <form wire:submit="save">
                    <div class="modal-header border-bottom">
                        <div>
                            <h5 class="modal-title mb-0">
                                {{ $modalMode === 'edit' ? 'Edit Workstation' : 'Add New Workstation' }}
                            </h5>
                            <small class="text-muted">
                                {{ $modalMode === 'edit' ? 'Update workstation information' : 'Fill in the details to create a new workstation' }}
                            </small>
                        </div>
                        <button type="button" class="btn-close" wire:click="closeModal"></button>
                    </div>

                    <div class="modal-body">
                        <!-- Basic Information -->
                        <div class="mb-4">
                            <h6 class="text-primary mb-3">
                                <i class="fa-solid fa-info-circle me-2"></i>Basic Information
                            </h6>
                            <div class="row g-3">
                                <div class="col-md-6">
                                    <label class="form-label">Workstation Name <span class="text-danger">*</span></label>
                                    <input type="text" class="form-control @error('workstation_name') is-invalid @enderror"
                                        wire:model="workstation_name"
                                        placeholder="Enter workstation name">
                                    @error('workstation_name')
                                        <div class="invalid-feedback">{{ $message }}</div>
                                    @enderror
                                </div>
                                <div class="col-md-6">
                                    <label class="form-label">TIN Number <span class="text-danger">*</span></label>
                                    <input type="text" class="form-control @error('tin_number') is-invalid @enderror"
                                        wire:model="tin_number"
                                        placeholder="Enter TIN number">
                                    @error('tin_number')
                                        <div class="invalid-feedback">{{ $message }}</div>
                                    @enderror
                                </div>
                                <div class="col-md-6">
                                    <label class="form-label">Email Address <span class="text-danger">*</span></label>
                                    <input type="email" class="form-control @error('email_address') is-invalid @enderror"
                                        wire:model="email_address"
                                        placeholder="Enter email address">
                                    @error('email_address')
                                        <div class="invalid-feedback">{{ $message }}</div>
                                    @enderror
                                </div>
                                <div class="col-md-6">
                                    <label class="form-label">Phone Number <span class="text-danger">*</span></label>
                                    <input type="tel" class="form-control @error('phone_number') is-invalid @enderror"
                                        wire:model="phone_number"
                                        placeholder="Enter phone number">
                                    @error('phone_number')
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
                                <div class="col-md-6">
                                    <label class="form-label">Country <span class="text-danger">*</span></label>
                                    <select class="form-select @error('country_id') is-invalid @enderror" wire:model="country_id">
                                        <option value="">Select Country</option>
                                        @foreach($countries as $country)
                                            <option value="{{ $country->id }}">{{ $country->name }}</option>
                                        @endforeach
                                    </select>
                                    @error('country_id')
                                        <div class="invalid-feedback">{{ $message }}</div>
                                    @enderror
                                </div>
                                <div class="col-md-6">
                                    <label class="form-label">Region <span class="text-danger">*</span></label>
                                    <select class="form-select @error('region_id') is-invalid @enderror" wire:model.live="region_id">
                                        <option value="">Select Region</option>
                                        @foreach($regions as $region)
                                            <option value="{{ $region->id }}">{{ $region->name }}</option>
                                        @endforeach
                                    </select>
                                    @error('region_id')
                                        <div class="invalid-feedback">{{ $message }}</div>
                                    @enderror
                                </div>
                                <div class="col-md-6">
                                    <label class="form-label">District <span class="text-danger">*</span></label>
                                    <select class="form-select @error('district_id') is-invalid @enderror"
                                        wire:model.live="district_id"
                                        @if(count($districts) === 0) disabled @endif>
                                        <option value="">Select District</option>
                                        @foreach($districts as $district)
                                            <option value="{{ $district->id }}">{{ $district->name }}</option>
                                        @endforeach
                                    </select>
                                    @error('district_id')
                                        <div class="invalid-feedback">{{ $message }}</div>
                                    @enderror
                                </div>
                                <div class="col-md-6">
                                    <label class="form-label">Ward <span class="text-danger">*</span></label>
                                    <select class="form-select @error('ward_id') is-invalid @enderror"
                                        wire:model="ward_id"
                                        @if(count($wards) === 0) disabled @endif>
                                        <option value="">Select Ward</option>
                                        @foreach($wards as $ward)
                                            <option value="{{ $ward->id }}">{{ $ward->name }}</option>
                                        @endforeach
                                    </select>
                                    @error('ward_id')
                                        <div class="invalid-feedback">{{ $message }}</div>
                                    @enderror
                                </div>
                                <div class="col-md-4">
                                    <label class="form-label">Postal Code <span class="text-danger">*</span></label>
                                    <input type="text" class="form-control @error('postal_code') is-invalid @enderror"
                                        wire:model="postal_code"
                                        placeholder="e.g., P.O. Box 123">
                                    @error('postal_code')
                                        <div class="invalid-feedback">{{ $message }}</div>
                                    @enderror
                                </div>
                                <div class="col-md-8">
                                    <label class="form-label">Location/Branch Name</label>
                                    <input type="text" class="form-control @error('location') is-invalid @enderror"
                                        wire:model="location"
                                        placeholder="e.g., Main Branch, Downtown Office">
                                    @error('location')
                                        <div class="invalid-feedback">{{ $message }}</div>
                                    @enderror
                                </div>
                                <div class="col-12">
                                    <label class="form-label">Physical Address <span class="text-danger">*</span></label>
                                    <textarea class="form-control @error('physical_address') is-invalid @enderror"
                                        wire:model="physical_address"
                                        rows="2"
                                        placeholder="Enter detailed physical address"></textarea>
                                    @error('physical_address')
                                        <div class="invalid-feedback">{{ $message }}</div>
                                    @enderror
                                </div>
                            </div>
                        </div>

                        <!-- Branding & Documents -->
                        <div>
                            <h6 class="text-primary mb-3">
                                <i class="fa-solid fa-image me-2"></i>Branding & Documents
                            </h6>
                            <div class="row g-3">
                                <!-- Logo -->
                                <div class="col-md-4">
                                    <label class="form-label">Logo</label>
                                    <div class="border rounded p-3 text-center bg-light">
                                        @if($logo)
                                            <img src="{{ $logo->temporaryUrl() }}" class="img-fluid mb-2 rounded" style="max-height: 100px;">
                                            <button type="button" class="btn btn-sm btn-outline-danger d-block mx-auto" wire:click="removeFile('logo')">
                                                <i class="fa-solid fa-times me-1"></i> Remove
                                            </button>
                                        @elseif($existing_logo)
                                            <img src="{{ asset('storage/' . $existing_logo) }}" class="img-fluid mb-2 rounded" style="max-height: 100px;">
                                            <small class="text-muted d-block">Current logo</small>
                                        @else
                                            <div class="py-3">
                                                <i class="fa-solid fa-cloud-upload-alt fa-2x text-muted mb-2"></i>
                                                <p class="mb-0 small text-muted">Upload logo</p>
                                            </div>
                                        @endif
                                        <input type="file" class="form-control form-control-sm mt-2 @error('logo') is-invalid @enderror"
                                            wire:model="logo" accept="image/*">
                                        @error('logo')
                                            <div class="invalid-feedback">{{ $message }}</div>
                                        @enderror
                                    </div>
                                </div>

                                <!-- Official Stamp -->
                                <div class="col-md-4">
                                    <label class="form-label">Official Stamp</label>
                                    <div class="border rounded p-3 text-center bg-light">
                                        @if($official_stamp)
                                            <img src="{{ $official_stamp->temporaryUrl() }}" class="img-fluid mb-2 rounded" style="max-height: 100px;">
                                            <button type="button" class="btn btn-sm btn-outline-danger d-block mx-auto" wire:click="removeFile('stamp')">
                                                <i class="fa-solid fa-times me-1"></i> Remove
                                            </button>
                                        @elseif($existing_stamp)
                                            <img src="{{ asset('storage/' . $existing_stamp) }}" class="img-fluid mb-2 rounded" style="max-height: 100px;">
                                            <small class="text-muted d-block">Current stamp</small>
                                        @else
                                            <div class="py-3">
                                                <i class="fa-solid fa-stamp fa-2x text-muted mb-2"></i>
                                                <p class="mb-0 small text-muted">Upload stamp</p>
                                            </div>
                                        @endif
                                        <input type="file" class="form-control form-control-sm mt-2 @error('official_stamp') is-invalid @enderror"
                                            wire:model="official_stamp" accept="image/*">
                                        @error('official_stamp')
                                            <div class="invalid-feedback">{{ $message }}</div>
                                        @enderror
                                    </div>
                                </div>

                                <!-- Letter Head -->
                                <div class="col-md-4">
                                    <label class="form-label">Letter Head</label>
                                    <div class="border rounded p-3 text-center bg-light">
                                        @if($letter_head)
                                            <img src="{{ $letter_head->temporaryUrl() }}" class="img-fluid mb-2 rounded" style="max-height: 100px;">
                                            <button type="button" class="btn btn-sm btn-outline-danger d-block mx-auto" wire:click="removeFile('letterhead')">
                                                <i class="fa-solid fa-times me-1"></i> Remove
                                            </button>
                                        @elseif($existing_letterhead)
                                            <img src="{{ asset('storage/' . $existing_letterhead) }}" class="img-fluid mb-2 rounded" style="max-height: 100px;">
                                            <small class="text-muted d-block">Current letterhead</small>
                                        @else
                                            <div class="py-3">
                                                <i class="fa-solid fa-file-alt fa-2x text-muted mb-2"></i>
                                                <p class="mb-0 small text-muted">Upload letterhead</p>
                                            </div>
                                        @endif
                                        <input type="file" class="form-control form-control-sm mt-2 @error('letter_head') is-invalid @enderror"
                                            wire:model="letter_head" accept="image/*">
                                        @error('letter_head')
                                            <div class="invalid-feedback">{{ $message }}</div>
                                        @enderror
                                    </div>
                                </div>
                            </div>
                            <small class="text-muted mt-2 d-block">
                                <i class="fa-solid fa-info-circle me-1"></i>
                                Accepted formats: JPG, PNG, GIF. Maximum size: 2MB
                            </small>
                        </div>
                    </div>

                    <div class="modal-footer border-top">
                        <button type="button" class="btn btn-light" wire:click="closeModal">
                            <i class="fa-solid fa-times me-1"></i> Cancel
                        </button>
                        <button type="submit" class="btn btn-primary" wire:loading.attr="disabled">
                            <span wire:loading.remove wire:target="save">
                                <i class="fa-solid fa-check me-1"></i>
                                {{ $modalMode === 'edit' ? 'Update Workstation' : 'Create Workstation' }}
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

    <!-- Delete Confirmation Modal -->
    @if($confirmingDelete)
    <div class="modal fade show d-block" tabindex="-1" style="background: rgba(0,0,0,0.5);">
        <div class="modal-dialog modal-dialog-centered modal-sm">
            <div class="modal-content">
                <div class="modal-body text-center py-4">
                    <div class="bg-danger bg-opacity-10 rounded-circle d-inline-flex align-items-center justify-content-center mb-3"
                        style="width: 60px; height: 60px;">
                        <i class="fa-solid fa-trash fa-lg text-danger"></i>
                    </div>
                    <h5 class="mb-2">Delete Workstation?</h5>
                    <p class="text-muted mb-0">This action cannot be undone. All associated data will be permanently removed.</p>
                </div>
                <div class="modal-footer border-top justify-content-center gap-2">
                    <button type="button" class="btn btn-light" wire:click="cancelDelete">
                        Cancel
                    </button>
                    <button type="button" class="btn btn-danger" wire:click="delete">
                        <i class="fa-solid fa-trash me-1"></i> Delete
                    </button>
                </div>
            </div>
        </div>
    </div>
    @endif
</div>
