<div>
    <div class="container-fluid">
        <!-- Page Header -->
        <div class="row mb-4">
            <div class="col-12">
                <nav aria-label="breadcrumb">
                    <ol class="breadcrumb">
                        <li class="breadcrumb-item"><a href="{{ route('contracts.list') }}">Contracts</a></li>
                        <li class="breadcrumb-item active">{{ $modalMode === 'create' ? 'Create Contract' : 'Edit Contract' }}</li>
                    </ol>
                </nav>
                <div class="d-flex justify-content-between align-items-center">
                    <div>
                        <h4 class="mb-1">{{ $modalMode === 'create' ? 'Create New Contract' : 'Edit Contract' }}</h4>
                        <p class="text-muted mb-0">Fill in the contract details below</p>
                    </div>
                </div>
            </div>
        </div>

        <!-- Form Card -->
        <div class="row">
            <div class="col-12">
                <form wire:submit.prevent="save">
                    <div class="card shadow-sm border-0 mb-4">
                        <div class="card-header bg-primary text-white">
                            <h5 class="mb-0">Contract Information</h5>
                        </div>
                        <div class="card-body">
                            <div class="row g-4">
                                <!-- Basic Information Section -->
                                <div class="col-12">
                                    <h6 class="border-bottom pb-2 mb-3">Basic Information</h6>
                                </div>

                                <div class="col-md-6">
                                    <label class="form-label">Contract Number <span class="text-danger">*</span></label>
                                    <input type="text"
                                           wire:model="contract_number"
                                           class="form-control @error('contract_number') is-invalid @enderror"
                                           {{ $modalMode === 'edit' ? 'readonly' : '' }}
                                           required>
                                    @error('contract_number') <div class="invalid-feedback">{{ $message }}</div> @enderror
                                    <small class="form-text text-muted">Auto-generated contract number</small>
                                </div>

                                <div class="col-md-6">
                                    <label class="form-label">Contract Title <span class="text-danger">*</span></label>
                                    <input type="text"
                                           wire:model="title"
                                           class="form-control @error('title') is-invalid @enderror"
                                           required>
                                    @error('title') <div class="invalid-feedback">{{ $message }}</div> @enderror
                                </div>

                                <div class="col-md-6">
                                    <label class="form-label">Contract Type <span class="text-danger">*</span></label>
                                    <select wire:model="type" class="form-select @error('type') is-invalid @enderror" required>
                                        <option value="">Select Type</option>
                                        <option value="supplier">Supplier</option>
                                        <option value="service_provider">Service Provider</option>
                                        <option value="agency">Agency</option>
                                        <option value="partner">Partner Institution</option>
                                    </select>
                                    @error('type') <div class="invalid-feedback">{{ $message }}</div> @enderror
                                </div>

                                <div class="col-md-6">
                                    <label class="form-label">Status <span class="text-danger">*</span></label>
                                    <select wire:model="status" class="form-select @error('status') is-invalid @enderror" required>
                                        <option value="draft">Draft</option>
                                        <option value="active">Active</option>
                                        <option value="pending_approval">Pending Approval</option>
                                        <option value="expired">Expired</option>
                                        <option value="terminated">Terminated</option>
                                    </select>
                                    @error('status') <div class="invalid-feedback">{{ $message }}</div> @enderror
                                </div>

                                <!-- Relationships Section -->
                                <div class="col-12 mt-4">
                                    <h6 class="border-bottom pb-2 mb-3">Relationships</h6>
                                </div>

                                <div class="col-md-6">
                                    <label class="form-label">Department</label>
                                    <select wire:model="department_id" class="form-select @error('department_id') is-invalid @enderror">
                                        <option value="">Select Department</option>
                                        @foreach($departments as $dept)
                                            <option value="{{ $dept->id }}">{{ $dept->name }}</option>
                                        @endforeach
                                    </select>
                                    @error('department_id') <div class="invalid-feedback">{{ $message }}</div> @enderror
                                </div>

                                <div class="col-md-6">
                                    <label class="form-label">Vendor/Supplier</label>
                                    <div class="input-group">
                                        <select wire:model="vendor_id" class="form-select @error('vendor_id') is-invalid @enderror">
                                            <option value="">Select Vendor</option>
                                            @foreach($vendors as $vendor)
                                                <option value="{{ $vendor->id }}">{{ $vendor->name }} ({{ $vendor->vendor_number }})</option>
                                            @endforeach
                                        </select>
                                        <a href="{{ route('setup.vendors') }}" target="_blank" class="btn btn-outline-secondary" title="Manage Vendors">
                                            <i class="bi bi-box-arrow-up-right"></i>
                                        </a>
                                    </div>
                                    @error('vendor_id') <div class="invalid-feedback">{{ $message }}</div> @enderror
                                    @if($vendors->count() === 0)
                                        <small class="form-text text-warning">
                                            <i class="bi bi-exclamation-triangle"></i> No vendors available.
                                            <a href="{{ route('setup.vendors') }}" target="_blank">Add a vendor first</a>
                                        </small>
                                    @endif
                                </div>

                                <!-- Contract Value & Dates Section -->
                                <div class="col-12 mt-4">
                                    <h6 class="border-bottom pb-2 mb-3">Financial & Timeline</h6>
                                </div>

                                <div class="col-md-4">
                                    <label class="form-label">Contract Value (TZS) <span class="text-danger">*</span></label>
                                    <input type="number"
                                           step="0.01"
                                           wire:model="contract_value"
                                           class="form-control @error('contract_value') is-invalid @enderror"
                                           required>
                                    @error('contract_value') <div class="invalid-feedback">{{ $message }}</div> @enderror
                                </div>

                                <div class="col-md-4">
                                    <label class="form-label">Start Date <span class="text-danger">*</span></label>
                                    <input type="date"
                                           wire:model="start_date"
                                           class="form-control @error('start_date') is-invalid @enderror"
                                           required>
                                    @error('start_date') <div class="invalid-feedback">{{ $message }}</div> @enderror
                                </div>

                                <div class="col-md-4">
                                    <label class="form-label">End Date <span class="text-danger">*</span></label>
                                    <input type="date"
                                           wire:model="end_date"
                                           class="form-control @error('end_date') is-invalid @enderror"
                                           required>
                                    @error('end_date') <div class="invalid-feedback">{{ $message }}</div> @enderror
                                </div>

                                <!-- Notification Settings -->
                                <div class="col-12 mt-4">
                                    <h6 class="border-bottom pb-2 mb-3">Notification Settings</h6>
                                </div>

                                <div class="col-md-6">
                                    <label class="form-label">Expiry Notification (Days Before) <span class="text-danger">*</span></label>
                                    <select wire:model="notification_time" class="form-select @error('notification_time') is-invalid @enderror" required>
                                        <option value="90">90 Days</option>
                                        <option value="60">60 Days</option>
                                        <option value="30">30 Days</option>
                                    </select>
                                    @error('notification_time') <div class="invalid-feedback">{{ $message }}</div> @enderror
                                    <small class="form-text text-muted">System will alert when contract is approaching expiry</small>
                                </div>

                                <!-- Description -->
                                <div class="col-12 mt-4">
                                    <h6 class="border-bottom pb-2 mb-3">Additional Information</h6>
                                </div>

                                <div class="col-12">
                                    <label class="form-label">Description</label>
                                    <textarea wire:model="description"
                                              class="form-control @error('description') is-invalid @enderror"
                                              rows="4"
                                              placeholder="Enter contract description, terms, and any additional notes..."></textarea>
                                    @error('description') <div class="invalid-feedback">{{ $message }}</div> @enderror
                                </div>
                            </div>
                        </div>

                        <div class="card-footer bg-light">
                            <div class="d-flex justify-content-end gap-2">
                                <button type="button" wire:click="cancel" class="btn btn-outline-secondary">
                                    <i class="bi bi-x-circle me-2"></i>Cancel
                                </button>
                                <button type="submit" class="btn btn-primary" wire:loading.attr="disabled">
                                    <span wire:loading.remove wire:target="save">
                                        <i class="bi bi-save me-2"></i>{{ $modalMode === 'create' ? 'Create Contract' : 'Update Contract' }}
                                    </span>
                                    <span wire:loading wire:target="save">
                                        <span class="spinner-border spinner-border-sm me-2"></span>Saving...
                                    </span>
                                </button>
                            </div>
                        </div>
                    </div>
                </form>
            </div>
        </div>

        <!-- Help Card -->
        <div class="row">
            <div class="col-12">
                <div class="card shadow-sm border-0 bg-info bg-opacity-10">
                    <div class="card-body">
                        <h6 class="text-info mb-2">
                            <i class="bi bi-info-circle me-2"></i>Contract Creation Tips
                        </h6>
                        <ul class="mb-0 small text-muted">
                            <li>Contract numbers are auto-generated based on the current year</li>
                            <li>Add vendors from Setup → Manage Vendors if not listed</li>
                            <li>Use "Draft" status while preparing contracts</li>
                            <li>Change to "Pending Approval" to initiate approval workflow</li>
                            <li>After creation, you can add documents, deliverables, and parties from the contract details page</li>
                        </ul>
                    </div>
                </div>
            </div>
        </div>
    </div>
</div>
