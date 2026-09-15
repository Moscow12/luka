<div>
    <x-pages.breadcrumn title="Termination Reasons"
        :breadcrumbs="[
            ['label' => 'Home', 'url' => route('dashboard')],
            ['label' => 'Settings'],
            ['label' => 'Termination Reasons']
        ]">
    </x-pages.breadcrumn>

    <div class="d-flex flex-column gap-4">
        <!-- Header with Search and Add Button -->
        <div class="d-flex flex-md-row flex-column gap-2 justify-content-between">
            <div class="d-flex flex-row gap-3 align-items-center">
                <div>
                    <input class="form-control" type="search" wire:model.live.debounce.300ms="search"
                           placeholder="Search reasons..." style="width: 250px;" />
                </div>
            </div>
            <div>
                <button class="btn btn-primary d-flex flex-row gap-1 align-items-center" wire:click="openModal()">
                    <i class="fa-solid fa-plus"></i>
                    Add Termination Reason
                </button>
            </div>
        </div>

        <!-- Info Card -->
        <div class="alert alert-info mb-0">
            <i class="fa-solid fa-info-circle me-2"></i>
            <strong>Note:</strong> Termination reasons are used when employees request contract termination or when contracts are not renewed.
            You can set the type to control when each reason is applicable.
        </div>

        <!-- Reasons Table -->
        <div class="card border-0 shadow-sm">
            <div class="card-body p-0">
                @if (session()->has('success'))
                    <div class="alert alert-success m-3 mb-0">
                        <i class="fa-solid fa-check-circle me-2"></i>{{ session('success') }}
                    </div>
                @endif
                @if (session()->has('error'))
                    <div class="alert alert-danger m-3 mb-0">
                        <i class="fa-solid fa-exclamation-circle me-2"></i>{{ session('error') }}
                    </div>
                @endif

                <div class="table-responsive">
                    <table class="table table-hover mb-0">
                        <thead class="table-light">
                            <tr>
                                <th style="width: 50px;">#</th>
                                <th>Name</th>
                                <th>Description</th>
                                <th>Type</th>
                                <th>Status</th>
                                <th style="width: 150px;">Actions</th>
                            </tr>
                        </thead>
                        <tbody>
                            @forelse($reasons as $index => $reason)
                                <tr>
                                    <td>{{ $reasons->firstItem() + $index }}</td>
                                    <td class="fw-medium">{{ $reason->name }}</td>
                                    <td>
                                        <span class="text-muted">{{ Str::limit($reason->description, 50) ?? '-' }}</span>
                                    </td>
                                    <td>
                                        @if($reason->type === 'termination')
                                            <span class="badge bg-danger">Termination Only</span>
                                        @elseif($reason->type === 'non_renewal')
                                            <span class="badge bg-warning">Non-Renewal Only</span>
                                        @else
                                            <span class="badge bg-info">Both</span>
                                        @endif
                                    </td>
                                    <td>
                                        <div class="form-check form-switch">
                                            <input class="form-check-input" type="checkbox" role="switch"
                                                   wire:click="toggleStatus('{{ $reason->id }}')"
                                                   {{ $reason->is_active ? 'checked' : '' }}>
                                            <label class="form-check-label">
                                                {{ $reason->is_active ? 'Active' : 'Inactive' }}
                                            </label>
                                        </div>
                                    </td>
                                    <td>
                                        <button class="btn btn-sm btn-outline-primary" wire:click="openModal('{{ $reason->id }}')" title="Edit">
                                            <i class="fa-solid fa-pencil"></i>
                                        </button>
                                        <button class="btn btn-sm btn-outline-danger" wire:click="delete('{{ $reason->id }}')"
                                                onclick="return confirm('Are you sure you want to delete this termination reason?')" title="Delete">
                                            <i class="fa-solid fa-trash"></i>
                                        </button>
                                    </td>
                                </tr>
                            @empty
                                <tr>
                                    <td colspan="6" class="text-center py-5">
                                        <div class="text-muted">
                                            <i class="fa-solid fa-folder-open fa-3x mb-3"></i>
                                            <p class="mb-0">No termination reasons found</p>
                                            <p class="small">Click "Add Termination Reason" to create one</p>
                                        </div>
                                    </td>
                                </tr>
                            @endforelse
                        </tbody>
                    </table>
                </div>
            </div>
            @if($reasons->hasPages())
                <div class="card-footer">
                    {{ $reasons->links() }}
                </div>
            @endif
        </div>
    </div>

    <!-- Add/Edit Modal -->
    @if($showModal)
        <div class="modal fade show d-block" tabindex="-1" style="background-color: rgba(0,0,0,0.5);">
            <div class="modal-dialog">
                <div class="modal-content">
                    <form wire:submit.prevent="save">
                        <div class="modal-header bg-primary text-white">
                            <h5 class="modal-title">
                                <i class="fa-solid fa-{{ $editingId ? 'pencil' : 'plus' }} me-2"></i>
                                {{ $editingId ? 'Edit Termination Reason' : 'Add Termination Reason' }}
                            </h5>
                            <button type="button" class="btn-close btn-close-white" wire:click="closeModal"></button>
                        </div>

                        <div class="modal-body">
                            <div class="mb-3">
                                <label class="form-label">Reason Name <span class="text-danger">*</span></label>
                                <input type="text" class="form-control @error('name') is-invalid @enderror"
                                       wire:model="name" placeholder="e.g., Resignation, Retirement, Redundancy">
                                @error('name') <div class="invalid-feedback">{{ $message }}</div> @enderror
                            </div>

                            <div class="mb-3">
                                <label class="form-label">Description</label>
                                <textarea class="form-control @error('description') is-invalid @enderror"
                                          wire:model="description" rows="3"
                                          placeholder="Provide a brief description of this termination reason..."></textarea>
                                @error('description') <div class="invalid-feedback">{{ $message }}</div> @enderror
                            </div>

                            <div class="mb-3">
                                <label class="form-label">Applicable For <span class="text-danger">*</span></label>
                                <select class="form-select @error('type') is-invalid @enderror" wire:model="type">
                                    <option value="both">Both Termination & Non-Renewal</option>
                                    <option value="termination">Termination Only</option>
                                    <option value="non_renewal">Non-Renewal Only</option>
                                </select>
                                @error('type') <div class="invalid-feedback">{{ $message }}</div> @enderror
                                <small class="text-muted">Select when this reason can be used</small>
                            </div>

                            <div class="mb-3">
                                <div class="form-check form-switch">
                                    <input class="form-check-input" type="checkbox" role="switch"
                                           wire:model="is_active" id="is_active">
                                    <label class="form-check-label" for="is_active">Active</label>
                                </div>
                                <small class="text-muted">Inactive reasons won't appear in selection dropdowns</small>
                            </div>
                        </div>

                        <div class="modal-footer">
                            <button type="button" class="btn btn-secondary" wire:click="closeModal">Cancel</button>
                            <button type="submit" class="btn btn-primary" wire:loading.attr="disabled">
                                <span wire:loading.remove wire:target="save">
                                    {{ $editingId ? 'Update' : 'Save' }}
                                </span>
                                <span wire:loading wire:target="save">
                                    <span class="spinner-border spinner-border-sm"></span> Saving...
                                </span>
                            </button>
                        </div>
                    </form>
                </div>
            </div>
        </div>
    @endif
</div>
