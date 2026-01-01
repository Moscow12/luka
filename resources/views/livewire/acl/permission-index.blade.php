<div class="custom-container">
    <x-pages.breadcrumn title="Permission Management"
        :breadcrumbs="[
            ['label' => 'Home', 'url' => route('dashboard')],
            ['label' => 'Access Control', 'url' => route('acl.index')],
            ['label' => 'Permissions']
        ]">
        <div class="d-flex gap-2">
            <button type="button" class="btn btn-outline-primary btn-sm" wire:click="openCategoryModal('create')">
                <i class="fa-solid fa-folder-plus me-1"></i> New Category
            </button>
            <button type="button" class="btn btn-primary btn-sm" wire:click="openPermissionModal('create')">
                <i class="fa-solid fa-plus me-1"></i> New Permission
            </button>
        </div>
    </x-pages.breadcrumn>

    <!-- Alerts -->
    @if (session()->has('success'))
        <div class="alert alert-success alert-dismissible fade show" role="alert">
            <i class="fa-solid fa-check-circle me-2"></i>
            {{ session('success') }}
            <button type="button" class="btn-close" data-bs-dismiss="alert" aria-label="Close"></button>
        </div>
    @endif

    @if (session()->has('error'))
        <div class="alert alert-danger alert-dismissible fade show" role="alert">
            <i class="fa-solid fa-exclamation-circle me-2"></i>
            {{ session('error') }}
            <button type="button" class="btn-close" data-bs-dismiss="alert" aria-label="Close"></button>
        </div>
    @endif

    <!-- Search Bar -->
    <div class="card shadow-sm mb-4">
        <div class="card-body py-3">
            <div class="row align-items-center">
                <div class="col-md-6">
                    <div class="input-group">
                        <span class="input-group-text bg-light border-end-0">
                            <i class="fa-solid fa-search text-muted"></i>
                        </span>
                        <input type="text" class="form-control border-start-0 ps-0"
                            placeholder="Search permissions or categories..."
                            wire:model.live.debounce.300ms="search">
                        @if($search)
                            <button class="btn btn-outline-secondary" type="button" wire:click="$set('search', '')">
                                <i class="fa-solid fa-times"></i>
                            </button>
                        @endif
                    </div>
                </div>
                <div class="col-md-6 text-md-end mt-3 mt-md-0">
                    <span class="text-muted">
                        <i class="fa-solid fa-layer-group me-1"></i>
                        {{ $categories->count() }} Categories,
                        {{ $categories->sum(fn($c) => $c->permissions->count()) }} Permissions
                    </span>
                </div>
            </div>
        </div>
    </div>

    <!-- Categories & Permissions -->
    @forelse ($categories as $category)
        <div class="card shadow-sm mb-4">
            <div class="card-header bg-light d-flex justify-content-between align-items-center py-3">
                <div class="d-flex align-items-center">
                    <div class="bg-primary bg-opacity-10 rounded-circle p-2 me-3">
                        <i class="fa-solid fa-folder text-primary"></i>
                    </div>
                    <div>
                        <h5 class="mb-0 fw-semibold">{{ $category->name }}</h5>
                        <small class="text-muted">{{ $category->permissions->count() }} permission(s)</small>
                    </div>
                </div>
                <div class="d-flex gap-2">
                    <button type="button" class="btn btn-sm btn-outline-success"
                        wire:click="openPermissionModal('create', null, '{{ $category->id }}')"
                        title="Add Permission">
                        <i class="fa-solid fa-plus"></i>
                    </button>
                    <button type="button" class="btn btn-sm btn-outline-primary"
                        wire:click="openCategoryModal('edit', '{{ $category->id }}')"
                        title="Edit Category">
                        <i class="fa-solid fa-edit"></i>
                    </button>
                    <button type="button" class="btn btn-sm btn-outline-danger"
                        wire:click="confirmDelete('category', '{{ $category->id }}', '{{ $category->name }}')"
                        title="Delete Category">
                        <i class="fa-solid fa-trash"></i>
                    </button>
                </div>
            </div>
            <div class="card-body p-0">
                @if($category->permissions->count() > 0)
                    <div class="table-responsive">
                        <table class="table table-hover mb-0">
                            <thead class="table-light">
                                <tr>
                                    <th class="ps-4" style="width: 40%;">Permission Name</th>
                                    <th style="width: 45%;">Description</th>
                                    <th class="text-center pe-4" style="width: 15%;">Actions</th>
                                </tr>
                            </thead>
                            <tbody>
                                @foreach($category->permissions as $permission)
                                    <tr>
                                        <td class="ps-4">
                                            <div class="d-flex align-items-center">
                                                <i class="fa-solid fa-key text-muted me-2"></i>
                                                <span class="fw-medium">{{ $permission->name }}</span>
                                            </div>
                                        </td>
                                        <td>
                                            <span class="text-muted">{{ $permission->description ?? '-' }}</span>
                                        </td>
                                        <td class="text-center pe-4">
                                            <div class="btn-group btn-group-sm">
                                                <button type="button" class="btn btn-outline-primary"
                                                    wire:click="openPermissionModal('edit', '{{ $permission->id }}')"
                                                    title="Edit">
                                                    <i class="fa-solid fa-edit"></i>
                                                </button>
                                                <button type="button" class="btn btn-outline-danger"
                                                    wire:click="confirmDelete('permission', '{{ $permission->id }}', '{{ $permission->name }}')"
                                                    title="Delete">
                                                    <i class="fa-solid fa-trash"></i>
                                                </button>
                                            </div>
                                        </td>
                                    </tr>
                                @endforeach
                            </tbody>
                        </table>
                    </div>
                @else
                    <div class="p-4 text-center text-muted">
                        <i class="fa-solid fa-inbox fa-2x mb-2"></i>
                        <p class="mb-0">No permissions in this category</p>
                        <button type="button" class="btn btn-sm btn-link"
                            wire:click="openPermissionModal('create', null, '{{ $category->id }}')">
                            Add the first permission
                        </button>
                    </div>
                @endif
            </div>
        </div>
    @empty
        <div class="card shadow-sm">
            <div class="card-body p-5 text-center">
                <i class="fa-solid fa-folder-open fa-4x text-muted mb-3"></i>
                <h4 class="text-muted">No Categories Found</h4>
                <p class="text-muted mb-4">Start by creating a permission category to organize your permissions.</p>
                <button type="button" class="btn btn-primary" wire:click="openCategoryModal('create')">
                    <i class="fa-solid fa-folder-plus me-2"></i> Create First Category
                </button>
            </div>
        </div>
    @endforelse

    <!-- Category Modal -->
    @if($showCategoryModal)
    <div class="modal fade show d-block" tabindex="-1" style="background-color: rgba(0,0,0,0.5);">
        <div class="modal-dialog modal-dialog-centered">
            <div class="modal-content">
                <div class="modal-header bg-primary bg-opacity-10">
                    <h5 class="modal-title">
                        <i class="fa-solid fa-folder{{ $categoryModalMode === 'edit' ? '-open' : '-plus' }} me-2"></i>
                        {{ $categoryModalMode === 'edit' ? 'Edit Category' : 'New Category' }}
                    </h5>
                    <button type="button" class="btn-close" wire:click="closeCategoryModal"></button>
                </div>
                <form wire:submit.prevent="saveCategory">
                    <div class="modal-body">
                        <div class="mb-3">
                            <label class="form-label fw-medium">
                                Category Name <span class="text-danger">*</span>
                            </label>
                            <input type="text" class="form-control @error('category_name') is-invalid @enderror"
                                wire:model="category_name"
                                placeholder="e.g., User Management, Reports, Settings">
                            @error('category_name')
                                <div class="invalid-feedback">{{ $message }}</div>
                            @enderror
                        </div>
                    </div>
                    <div class="modal-footer">
                        <button type="button" class="btn btn-outline-secondary" wire:click="closeCategoryModal">
                            Cancel
                        </button>
                        <button type="submit" class="btn btn-primary">
                            <span wire:loading.remove wire:target="saveCategory">
                                <i class="fa-solid fa-save me-1"></i>
                                {{ $categoryModalMode === 'edit' ? 'Update' : 'Create' }}
                            </span>
                            <span wire:loading wire:target="saveCategory">
                                <span class="spinner-border spinner-border-sm me-1"></span> Saving...
                            </span>
                        </button>
                    </div>
                </form>
            </div>
        </div>
    </div>
    @endif

    <!-- Permission Modal -->
    @if($showPermissionModal)
    <div class="modal fade show d-block" tabindex="-1" style="background-color: rgba(0,0,0,0.5);">
        <div class="modal-dialog modal-dialog-centered">
            <div class="modal-content">
                <div class="modal-header bg-success bg-opacity-10">
                    <h5 class="modal-title">
                        <i class="fa-solid fa-key me-2"></i>
                        {{ $permissionModalMode === 'edit' ? 'Edit Permission' : 'New Permission' }}
                    </h5>
                    <button type="button" class="btn-close" wire:click="closePermissionModal"></button>
                </div>
                <form wire:submit.prevent="savePermission">
                    <div class="modal-body">
                        <div class="mb-3">
                            <label class="form-label fw-medium">
                                Category <span class="text-danger">*</span>
                            </label>
                            <select class="form-select @error('permission_category_id') is-invalid @enderror"
                                wire:model="permission_category_id">
                                <option value="">Select Category</option>
                                @foreach($allCategories as $cat)
                                    <option value="{{ $cat->id }}">{{ $cat->name }}</option>
                                @endforeach
                            </select>
                            @error('permission_category_id')
                                <div class="invalid-feedback">{{ $message }}</div>
                            @enderror
                        </div>

                        <div class="mb-3">
                            <label class="form-label fw-medium">
                                Permission Name <span class="text-danger">*</span>
                            </label>
                            <input type="text" class="form-control @error('permission_name') is-invalid @enderror"
                                wire:model="permission_name"
                                placeholder="e.g., view-users, create-reports">
                            @error('permission_name')
                                <div class="invalid-feedback">{{ $message }}</div>
                            @enderror
                            <small class="text-muted">Use lowercase with hyphens (e.g., view-users)</small>
                        </div>

                        <div class="mb-3">
                            <label class="form-label fw-medium">Description</label>
                            <textarea class="form-control @error('permission_description') is-invalid @enderror"
                                wire:model="permission_description"
                                rows="3"
                                placeholder="Describe what this permission allows..."></textarea>
                            @error('permission_description')
                                <div class="invalid-feedback">{{ $message }}</div>
                            @enderror
                        </div>
                    </div>
                    <div class="modal-footer">
                        <button type="button" class="btn btn-outline-secondary" wire:click="closePermissionModal">
                            Cancel
                        </button>
                        <button type="submit" class="btn btn-success">
                            <span wire:loading.remove wire:target="savePermission">
                                <i class="fa-solid fa-save me-1"></i>
                                {{ $permissionModalMode === 'edit' ? 'Update' : 'Create' }}
                            </span>
                            <span wire:loading wire:target="savePermission">
                                <span class="spinner-border spinner-border-sm me-1"></span> Saving...
                            </span>
                        </button>
                    </div>
                </form>
            </div>
        </div>
    </div>
    @endif

    <!-- Delete Confirmation Modal -->
    @if($showDeleteModal)
    <div class="modal fade show d-block" tabindex="-1" style="background-color: rgba(0,0,0,0.5);">
        <div class="modal-dialog modal-dialog-centered modal-sm">
            <div class="modal-content">
                <div class="modal-header bg-danger bg-opacity-10">
                    <h5 class="modal-title text-danger">
                        <i class="fa-solid fa-exclamation-triangle me-2"></i>
                        Confirm Delete
                    </h5>
                    <button type="button" class="btn-close" wire:click="closeDeleteModal"></button>
                </div>
                <div class="modal-body text-center py-4">
                    <i class="fa-solid fa-trash-alt fa-3x text-danger mb-3"></i>
                    <p class="mb-1">Are you sure you want to delete this {{ $deleteType }}?</p>
                    <p class="fw-bold text-dark mb-0">"{{ $deleteName }}"</p>
                    @if($deleteType === 'category')
                        <small class="text-danger d-block mt-2">
                            <i class="fa-solid fa-warning me-1"></i>
                            This will also delete all permissions in this category!
                        </small>
                    @endif
                </div>
                <div class="modal-footer justify-content-center">
                    <button type="button" class="btn btn-outline-secondary" wire:click="closeDeleteModal">
                        Cancel
                    </button>
                    <button type="button" class="btn btn-danger" wire:click="delete">
                        <span wire:loading.remove wire:target="delete">
                            <i class="fa-solid fa-trash me-1"></i> Delete
                        </span>
                        <span wire:loading wire:target="delete">
                            <span class="spinner-border spinner-border-sm me-1"></span> Deleting...
                        </span>
                    </button>
                </div>
            </div>
        </div>
    </div>
    @endif
</div>
