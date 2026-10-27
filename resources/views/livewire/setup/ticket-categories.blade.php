<div>
    <div class="row mb-4">
        <div class="col-12 d-md-flex justify-content-between align-items-center">
            <div>
                <h4 class="mb-1">Ticket Categories</h4>
                <p class="text-muted mb-0">Categories, default routing and SLA for tickets</p>
            </div>
            <button class="btn btn-primary mt-2 mt-md-0" wire:click="openModal"><i class="fa-solid fa-plus me-1"></i>Add Category</button>
        </div>
    </div>

    @include('livewire.tickets.partials.flash')

    <div class="card">
        <div class="card-header">
            <input type="search" class="form-control" style="max-width: 320px;" placeholder="Search categories" wire:model.live.debounce.400ms="search">
        </div>
        <div class="card-body p-0">
            <div class="table-responsive">
                <table class="table table-hover align-middle mb-0">
                    <thead class="table-light">
                        <tr><th>Name</th><th>Default department</th><th class="text-center">SLA (hours)</th><th class="text-center">Tickets</th><th class="text-center">Status</th><th class="text-end">Actions</th></tr>
                    </thead>
                    <tbody>
                        @forelse($categories as $category)
                            <tr wire:key="cat-{{ $category->id }}">
                                <td><strong>{{ $category->name }}</strong>@if($category->description)<br><small class="text-muted">{{ Str::limit($category->description, 60) }}</small>@endif</td>
                                <td>{{ $category->defaultDepartment?->name ?? '—' }}</td>
                                <td class="text-center">{{ $category->default_sla_hours ?? '—' }}</td>
                                <td class="text-center">{{ $category->tickets_count }}</td>
                                <td class="text-center">
                                    <button class="badge border-0 {{ $category->status === 'active' ? 'bg-success' : 'bg-secondary' }}" wire:click="toggleStatus('{{ $category->id }}')">{{ ucfirst($category->status) }}</button>
                                </td>
                                <td class="text-end"><button class="btn btn-sm btn-outline-primary" wire:click="openModal('{{ $category->id }}')"><i class="fa-solid fa-pen"></i></button></td>
                            </tr>
                        @empty
                            <tr><td colspan="6" class="text-center text-muted py-4">No categories yet.</td></tr>
                        @endforelse
                    </tbody>
                </table>
            </div>
        </div>
        @if($categories->hasPages())<div class="card-footer">{{ $categories->links() }}</div>@endif
    </div>

    @if($showModal)
        <div class="modal fade show d-block" tabindex="-1" style="background: rgba(0,0,0,0.5);">
            <div class="modal-dialog">
                <div class="modal-content">
                    <div class="modal-header"><h5 class="modal-title">{{ $editingId ? 'Edit' : 'Add' }} Category</h5><button type="button" class="btn-close" wire:click="closeModal"></button></div>
                    <form wire:submit="save">
                        <div class="modal-body">
                            <div class="mb-3">
                                <label class="form-label">Name <span class="text-danger">*</span></label>
                                <input type="text" class="form-control @error('name') is-invalid @enderror" wire:model="name" placeholder="e.g. IT Support, Payroll, Procurement">
                                @error('name') <div class="invalid-feedback">{{ $message }}</div> @enderror
                            </div>
                            <div class="mb-3">
                                <label class="form-label">Description</label>
                                <textarea class="form-control" rows="2" wire:model="description"></textarea>
                            </div>
                            <div class="mb-3">
                                <label class="form-label">Default department</label>
                                <select class="form-select" wire:model="default_department_id">
                                    <option value="">None</option>
                                    @foreach($departments as $d)<option value="{{ $d->id }}">{{ $d->name }}</option>@endforeach
                                </select>
                                <div class="form-text">Pre-selected when someone picks this category.</div>
                            </div>
                            <div class="mb-3">
                                <label class="form-label">Default SLA (hours)</label>
                                <input type="number" min="1" class="form-control @error('default_sla_hours') is-invalid @enderror" wire:model="default_sla_hours">
                                @error('default_sla_hours') <div class="invalid-feedback">{{ $message }}</div> @enderror
                                <div class="form-text">Used to suggest the due date.</div>
                            </div>
                            <div>
                                <label class="form-label">Status</label>
                                <select class="form-select" wire:model="status"><option value="active">Active</option><option value="inactive">Inactive</option></select>
                            </div>
                        </div>
                        <div class="modal-footer">
                            <button type="button" class="btn btn-secondary" wire:click="closeModal">Cancel</button>
                            <button type="submit" class="btn btn-primary">Save</button>
                        </div>
                    </form>
                </div>
            </div>
        </div>
    @endif
</div>
