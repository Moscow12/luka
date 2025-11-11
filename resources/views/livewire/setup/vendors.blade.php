<div>
    <div class="container-fluid">
        <!-- Page Header -->
        <div class="row mb-4">
            <div class="col-12">
                <div class="d-flex justify-content-between align-items-center">
                    <div>
                        <h4 class="mb-1">Vendor Management</h4>
                        <p class="text-muted mb-0">Manage vendors, suppliers, and service providers</p>
                    </div>
                    <div>
                        <button wire:click="openModal('create')" class="btn btn-primary">
                            <i class="bi bi-plus-circle me-2"></i>Add Vendor
                        </button>
                    </div>
                </div>
            </div>
        </div>

        <!-- Filters Card -->
        <div class="card shadow-sm border-0 mb-4">
            <div class="card-body">
                <div class="row g-3">
                    <div class="col-md-3">
                        <label class="form-label fw-semibold">Status</label>
                        <select wire:model.live="statusFilter" class="form-select">
                            <option value="">All Status</option>
                            <option value="active">Active</option>
                            <option value="inactive">Inactive</option>
                        </select>
                    </div>
                    <div class="col-md-3">
                        <label class="form-label fw-semibold">Vendor Type</label>
                        <select wire:model.live="typeFilter" class="form-select">
                            <option value="">All Types</option>
                            @foreach($vendorTypes as $type)
                                <option value="{{ $type }}">{{ $type }}</option>
                            @endforeach
                        </select>
                    </div>
                    <div class="col-md-5">
                        <label class="form-label fw-semibold">Search</label>
                        <input type="text"
                               wire:model.live.debounce.300ms="search"
                               class="form-control"
                               placeholder="Search by name, number, email, phone...">
                    </div>
                    <div class="col-md-1">
                        <label class="form-label fw-semibold d-block">&nbsp;</label>
                        <button wire:click="$set('search', '')" class="btn btn-outline-secondary w-100">
                            <i class="bi bi-x-circle"></i>
                        </button>
                    </div>
                </div>
            </div>
        </div>

        <!-- Vendors Table -->
        <div class="card shadow-sm border-0">
            <div class="card-body p-0">
                @if($vendors->count() > 0)
                    <div class="table-responsive">
                        <table class="table table-hover align-middle mb-0">
                            <thead class="bg-light">
                                <tr>
                                    <th class="px-4 py-3">Vendor #</th>
                                    <th class="py-3">Name</th>
                                    <th class="py-3">Type</th>
                                    <th class="py-3">Contact Person</th>
                                    <th class="py-3">Email</th>
                                    <th class="py-3">Phone</th>
                                    <th class="py-3">Location</th>
                                    <th class="py-3">Status</th>
                                    <th class="py-3 text-center">Actions</th>
                                </tr>
                            </thead>
                            <tbody>
                                @foreach($vendors as $vendor)
                                    <tr wire:key="vendor-{{ $vendor->id }}">
                                        <td class="px-4 py-3">
                                            <span class="badge bg-secondary">{{ $vendor->vendor_number }}</span>
                                        </td>
                                        <td class="py-3">
                                            <strong>{{ $vendor->name }}</strong>
                                        </td>
                                        <td class="py-3">
                                            <span class="badge bg-info bg-opacity-10 text-info">
                                                {{ $vendor->vendor_type }}
                                            </span>
                                        </td>
                                        <td class="py-3">{{ $vendor->contact_person }}</td>
                                        <td class="py-3">{{ $vendor->email ?? 'N/A' }}</td>
                                        <td class="py-3">{{ $vendor->phone ?? 'N/A' }}</td>
                                        <td class="py-3">
                                            <small>{{ $vendor->district->name ?? 'N/A' }}, {{ $vendor->region->name ?? 'N/A' }}</small>
                                        </td>
                                        <td class="py-3">
                                            <span class="badge bg-{{ $vendor->status === 'active' ? 'success' : 'secondary' }}">
                                                {{ ucfirst($vendor->status) }}
                                            </span>
                                        </td>
                                        <td class="py-3 text-center">
                                            <div class="btn-group" role="group">
                                                <button wire:click="openModal('edit', '{{ $vendor->id }}')"
                                                        class="btn btn-sm btn-outline-primary"
                                                        title="Edit">
                                                    <i class="bi bi-pencil"></i>
                                                </button>
                                                <button wire:click="deleteVendor('{{ $vendor->id }}')"
                                                        wire:confirm="Are you sure you want to delete this vendor?"
                                                        class="btn btn-sm btn-outline-danger"
                                                        title="Delete">
                                                    <i class="bi bi-trash"></i>
                                                </button>
                                            </div>
                                        </td>
                                    </tr>
                                @endforeach
                            </tbody>
                        </table>
                    </div>

                    <!-- Pagination -->
                    <div class="card-footer bg-white border-top">
                        <div class="d-flex justify-content-between align-items-center">
                            <div class="text-muted small">
                                Showing {{ $vendors->firstItem() }} to {{ $vendors->lastItem() }} of {{ $vendors->total() }} vendors
                            </div>
                            <div>
                                {{ $vendors->links() }}
                            </div>
                        </div>
                    </div>
                @else
                    <div class="text-center py-5">
                        <i class="bi bi-building fs-1 text-muted"></i>
                        <p class="text-muted mt-3 mb-0">
                            @if($search)
                                No vendors found matching your search.
                            @else
                                No vendors added yet. Click "Add Vendor" to create your first vendor.
                            @endif
                        </p>
                    </div>
                @endif
            </div>
        </div>
    </div>

    <!-- Modal -->
    @if($showModal)
        <div class="modal fade show d-block" tabindex="-1" style="background-color: rgba(0,0,0,0.5);">
            <div class="modal-dialog modal-lg modal-dialog-scrollable">
                <div class="modal-content">
                    <div class="modal-header bg-primary text-white">
                        <h5 class="modal-title">
                            {{ $modalMode === 'create' ? 'Add New Vendor' : 'Edit Vendor' }}
                        </h5>
                        <button type="button" class="btn-close btn-close-white" wire:click="closeModal"></button>
                    </div>
                    <div class="modal-body">
                        <form wire:submit.prevent="save">
                            <div class="row g-3">
                                <!-- Basic Information -->
                                <div class="col-12">
                                    <h6 class="border-bottom pb-2 mb-3">Basic Information</h6>
                                </div>

                                <div class="col-md-6">
                                    <label class="form-label">Vendor Name <span class="text-danger">*</span></label>
                                    <input type="text" wire:model="name" class="form-control" required>
                                    @error('name') <small class="text-danger">{{ $message }}</small> @enderror
                                </div>

                                <div class="col-md-6">
                                    <label class="form-label">Vendor Type <span class="text-danger">*</span></label>
                                    <select wire:model="vendor_type" class="form-select" required>
                                        <option value="">Select Type</option>
                                        <option value="Supplier">Supplier</option>
                                        <option value="Service Provider">Service Provider</option>
                                        <option value="Contractor">Contractor</option>
                                        <option value="Consultant">Consultant</option>
                                        <option value="Other">Other</option>
                                    </select>
                                    @error('vendor_type') <small class="text-danger">{{ $message }}</small> @enderror
                                </div>

                                <div class="col-md-6">
                                    <label class="form-label">Vendor Number <span class="text-danger">*</span></label>
                                    <input type="text" wire:model="vendor_number" class="form-control" required>
                                    @error('vendor_number') <small class="text-danger">{{ $message }}</small> @enderror
                                </div>

                                <div class="col-md-6">
                                    <label class="form-label">Status</label>
                                    <select wire:model="status" class="form-select">
                                        <option value="active">Active</option>
                                        <option value="inactive">Inactive</option>
                                    </select>
                                </div>

                                <div class="col-md-6">
                                    <label class="form-label">Email</label>
                                    <input type="email" wire:model="email" class="form-control">
                                    @error('email') <small class="text-danger">{{ $message }}</small> @enderror
                                </div>

                                <div class="col-md-6">
                                    <label class="form-label">Phone</label>
                                    <input type="text" wire:model="phone" class="form-control">
                                </div>

                                <!-- Contact Person -->
                                <div class="col-12 mt-4">
                                    <h6 class="border-bottom pb-2 mb-3">Contact Person</h6>
                                </div>

                                <div class="col-md-4">
                                    <label class="form-label">Contact Person <span class="text-danger">*</span></label>
                                    <input type="text" wire:model="contact_person" class="form-control" required>
                                    @error('contact_person') <small class="text-danger">{{ $message }}</small> @enderror
                                </div>

                                <div class="col-md-4">
                                    <label class="form-label">Contact Email</label>
                                    <input type="email" wire:model="contact_email" class="form-control">
                                    @error('contact_email') <small class="text-danger">{{ $message }}</small> @enderror
                                </div>

                                <div class="col-md-4">
                                    <label class="form-label">Contact Phone</label>
                                    <input type="text" wire:model="contact_phone" class="form-control">
                                </div>

                                <!-- Location -->
                                <div class="col-12 mt-4">
                                    <h6 class="border-bottom pb-2 mb-3">Location</h6>
                                </div>

                                <div class="col-md-6">
                                    <label class="form-label">Country <span class="text-danger">*</span></label>
                                    <select wire:model.live="country_id" class="form-select" required>
                                        <option value="">Select Country</option>
                                        @foreach($countries as $country)
                                            <option value="{{ $country->id }}">{{ $country->name }}</option>
                                        @endforeach
                                    </select>
                                    @error('country_id') <small class="text-danger">{{ $message }}</small> @enderror
                                </div>

                                <div class="col-md-6">
                                    <label class="form-label">Region <span class="text-danger">*</span></label>
                                    <select wire:model.live="region_id" class="form-select" required>
                                        <option value="">Select Region</option>
                                        @foreach($regions as $region)
                                            <option value="{{ $region->id }}">{{ $region->name }}</option>
                                        @endforeach
                                    </select>
                                    @error('region_id') <small class="text-danger">{{ $message }}</small> @enderror
                                </div>

                                <div class="col-md-6">
                                    <label class="form-label">District <span class="text-danger">*</span></label>
                                    <select wire:model.live="district_id" class="form-select" required>
                                        <option value="">Select District</option>
                                        @foreach($districts as $district)
                                            <option value="{{ $district->id }}">{{ $district->name }}</option>
                                        @endforeach
                                    </select>
                                    @error('district_id') <small class="text-danger">{{ $message }}</small> @enderror
                                </div>

                                <div class="col-md-6">
                                    <label class="form-label">Ward</label>
                                    <select wire:model.live="ward_id" class="form-select">
                                        <option value="">Select Ward</option>
                                        @foreach($wards as $ward)
                                            <option value="{{ $ward->id }}">{{ $ward->name }}</option>
                                        @endforeach
                                    </select>
                                </div>

                                <div class="col-md-12">
                                    <label class="form-label">Street/Village</label>
                                    <select wire:model="vilstreet_id" class="form-select">
                                        <option value="">Select Street/Village</option>
                                        @foreach($streets as $street)
                                            <option value="{{ $street->id }}">{{ $street->name }}</option>
                                        @endforeach
                                    </select>
                                </div>

                                <div class="col-md-12">
                                    <label class="form-label">Address</label>
                                    <textarea wire:model="address" class="form-control" rows="2"></textarea>
                                </div>

                                <!-- Description -->
                                <div class="col-12">
                                    <label class="form-label">Description</label>
                                    <textarea wire:model="description" class="form-control" rows="3"></textarea>
                                </div>
                            </div>
                        </form>
                    </div>
                    <div class="modal-footer">
                        <button type="button" class="btn btn-secondary" wire:click="closeModal">Cancel</button>
                        <button type="button" class="btn btn-primary" wire:click="save" wire:loading.attr="disabled">
                            <span wire:loading.remove wire:target="save">
                                <i class="bi bi-save me-2"></i>{{ $modalMode === 'create' ? 'Create Vendor' : 'Update Vendor' }}
                            </span>
                            <span wire:loading wire:target="save">
                                <span class="spinner-border spinner-border-sm me-2"></span>Saving...
                            </span>
                        </button>
                    </div>
                </div>
            </div>
        </div>
    @endif
</div>
