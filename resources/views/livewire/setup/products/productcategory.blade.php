<div>
    <!-- Page Header -->
    <div class="row">
        <div class="col-lg-12 col-md-12 col-12">
            <div class="mb-5 d-md-flex justify-content-between align-items-center">
                <div>
                    <h1 class="mb-1 h2">Product Configuration</h1>
                    <nav aria-label="breadcrumb">
                        <ol class="breadcrumb mb-0">
                            <li class="breadcrumb-item"><a href="#">Dashboard</a></li>
                            <li class="breadcrumb-item"><a href="#">Settings</a></li>
                            <li class="breadcrumb-item active" aria-current="page">Product Configuration</li>
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

    <!-- Tabs Navigation -->
    <div class="row">
        <div class="col-12">
            <ul class="nav nav-line-bottom mb-4 text-nowrap flex-nowrap overflow-auto" id="productConfigTab" role="tablist">
                <li class="nav-item">
                    <a class="nav-link py-3 px-4 {{ $activeTab === 'categories' ? 'active' : '' }}"
                       wire:click.prevent="$set('activeTab', 'categories')"
                       href="#" role="tab">
                        <i class="fa-solid fa-tags me-2"></i>
                        Product Categories
                        <span class="badge bg-primary-subtle text-primary ms-2">{{ $categories->total() }}</span>
                    </a>
                </li>
                <li class="nav-item">
                    <a class="nav-link py-3 px-4 {{ $activeTab === 'products' ? 'active' : '' }}"
                       wire:click.prevent="$set('activeTab', 'products')"
                       href="#" role="tab">
                        <i class="fa-solid fa-box me-2"></i>
                        Products
                        <span class="badge bg-success-subtle text-success ms-2">{{ $products->total() }}</span>
                    </a>
                </li>
            </ul>

            <!-- Tab Content -->
            <div class="tab-content">
                {{-- Product Categories Tab --}}
                @if($activeTab === 'categories')
                <div class="tab-pane fade show active">
                    <!-- Header -->
                    <div class="d-flex flex-column flex-md-row justify-content-between align-items-start align-items-md-center mb-4 gap-3">
                        <div>
                            <h5 class="mb-1">Product/items Categories</h5>
                            <p class="text-muted mb-0">Organize your products into meaningful categories</p>
                        </div>
                        <button class="btn btn-primary d-flex align-items-center gap-2" wire:click="openCategoryModal">
                            <i class="fa-solid fa-plus"></i>
                            Add Category
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
                                            wire:model.live.debounce.300ms="searchCategory"
                                            placeholder="Search categories...">
                                    </div>
                                </div>
                                <div class="col-md-8 text-md-end mt-3 mt-md-0">
                                    <span class="text-muted">
                                        Showing {{ $categories->firstItem() ?? 0 }} - {{ $categories->lastItem() ?? 0 }} of {{ $categories->total() }} categories
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
                                        <th>Category Name</th>
                                        <th>Code</th>
                                        <th>Type</th>
                                        <th>Status</th>
                                        <th>Products</th>
                                        <th class="text-end pe-4">Actions</th>
                                    </tr>
                                </thead>
                                <tbody>
                                    @forelse($categories as $index => $category)
                                    <tr wire:key="category-{{ $category->id }}">
                                        <td class="ps-4">{{ $categories->firstItem() + $index }}</td>
                                        <td>
                                            <div class="d-flex align-items-center gap-3">
                                                <div class="bg-primary bg-opacity-10 rounded d-flex align-items-center justify-content-center"
                                                    style="width: 40px; height: 40px;">
                                                    <i class="fa-solid fa-tags text-primary"></i>
                                                </div>
                                                <div>
                                                    <h6 class="mb-0">{{ $category->name }}</h6>
                                                </div>
                                            </div>
                                        </td>
                                        <td>
                                            <span class="badge bg-secondary-subtle text-secondary">{{ $category->code ?? 'N/A' }}</span>
                                        </td>
                                        <td>
                                            @php
                                                $typeColors = [
                                                    'good' => 'primary',
                                                    'asset' => 'warning',
                                                    'service' => 'info',
                                                    'work' => 'success',
                                                    'mixed' => 'secondary',
                                                ];
                                                $color = $typeColors[$category->type] ?? 'secondary';
                                            @endphp
                                            <span class="badge bg-{{ $color }}-subtle text-{{ $color }}">{{ ucfirst($category->type) }}</span>
                                        </td>
                                        <td>
                                            <span class="badge bg-{{ $category->status === 'active' ? 'success' : 'danger' }}">{{ ucfirst($category->status) }}</span>
                                        </td>
                                        <td>
                                            <span class="badge bg-dark">{{ $category->products_count }}</span>
                                        </td>
                                        <td class="text-end pe-4">
                                            <div class="d-flex justify-content-end gap-2">
                                                <button class="btn btn-sm btn-outline-primary"
                                                    wire:click="openCategoryModal('{{ $category->id }}')"
                                                    title="Edit">
                                                    <i class="fa-solid fa-pen-to-square"></i>
                                                </button>
                                                <button class="btn btn-sm btn-outline-danger"
                                                    wire:click="confirmDelete('category', '{{ $category->id }}')"
                                                    title="Delete">
                                                    <i class="fa-solid fa-trash"></i>
                                                </button>
                                            </div>
                                        </td>
                                    </tr>
                                    @empty
                                    <tr>
                                        <td colspan="7" class="text-center py-5">
                                            <div class="d-flex flex-column align-items-center">
                                                <div class="bg-light rounded-circle d-flex align-items-center justify-content-center mb-3"
                                                    style="width: 80px; height: 80px;">
                                                    <i class="fa-solid fa-tags fa-2x text-muted"></i>
                                                </div>
                                                <h6 class="mb-1">No categories found</h6>
                                                <p class="text-muted mb-3">
                                                    @if($searchCategory)
                                                        No results match your search criteria
                                                    @else
                                                        Get started by adding your first product category
                                                    @endif
                                                </p>
                                                @if(!$searchCategory)
                                                <button class="btn btn-primary btn-sm" wire:click="openCategoryModal">
                                                    <i class="fa-solid fa-plus me-1"></i> Add Category
                                                </button>
                                                @endif
                                            </div>
                                        </td>
                                    </tr>
                                    @endforelse
                                </tbody>
                            </table>
                        </div>
                        @if($categories->hasPages())
                        <div class="card-footer border-top">
                            {{ $categories->links() }}
                        </div>
                        @endif
                    </div>
                </div>
                @endif

                {{-- Products Tab --}}
                @if($activeTab === 'products')
                <div class="tab-pane fade show active">
                    <!-- Header -->
                    <div class="d-flex flex-column flex-md-row justify-content-between align-items-start align-items-md-center mb-4 gap-3">
                        <div>
                            <h5 class="mb-1">Products</h5>
                            <p class="text-muted mb-0">Manage your product catalog and inventory items</p>
                        </div>
                        <button class="btn btn-success d-flex align-items-center gap-2" wire:click="openProductModal">
                            <i class="fa-solid fa-plus"></i>
                            Add Product
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
                                            wire:model.live.debounce.300ms="searchProduct"
                                            placeholder="Search products...">
                                    </div>
                                </div>
                                <div class="col-md-8 text-md-end mt-3 mt-md-0">
                                    <span class="text-muted">
                                        Showing {{ $products->firstItem() ?? 0 }} - {{ $products->lastItem() ?? 0 }} of {{ $products->total() }} products
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
                                        <th>Product Name</th>
                                        <th>Code/Barcode</th>
                                        <th>Category</th>
                                        <th>Type</th>
                                        <th>Price</th>
                                        <th>Status</th>
                                        <th class="text-end pe-4">Actions</th>
                                    </tr>
                                </thead>
                                <tbody>
                                    @forelse($products as $index => $product)
                                    <tr wire:key="product-{{ $product->id }}">
                                        <td class="ps-4">{{ $products->firstItem() + $index }}</td>
                                        <td>
                                            <div class="d-flex align-items-center gap-3">
                                                <div class="bg-success bg-opacity-10 rounded d-flex align-items-center justify-content-center"
                                                    style="width: 40px; height: 40px;">
                                                    <i class="fa-solid fa-box text-success"></i>
                                                </div>
                                                <div>
                                                    <h6 class="mb-0">{{ $product->name }}</h6>
                                                </div>
                                            </div>
                                        </td>
                                        <td>
                                            <div>
                                                @if($product->code)
                                                    <span class="badge bg-secondary-subtle text-secondary">{{ $product->code }}</span>
                                                @endif
                                                @if($product->barcode)
                                                    <span class="badge bg-info-subtle text-info">{{ $product->barcode }}</span>
                                                @endif
                                                @if(!$product->code && !$product->barcode)
                                                    <span class="text-muted">N/A</span>
                                                @endif
                                            </div>
                                        </td>
                                        <td>
                                            <span class="badge bg-primary-subtle text-primary">{{ $product->category?->name ?? 'Uncategorized' }}</span>
                                        </td>
                                        <td>
                                            @php
                                                $typeColors = [
                                                    'good' => 'primary',
                                                    'asset' => 'warning',
                                                    'service' => 'info',
                                                    'work' => 'success',
                                                    'mixed' => 'secondary',
                                                ];
                                                $color = $typeColors[$product->type] ?? 'secondary';
                                            @endphp
                                            <span class="badge bg-{{ $color }}-subtle text-{{ $color }}">{{ ucfirst($product->type) }}</span>
                                        </td>
                                        <td>
                                            <div class="text-nowrap">
                                                <small class="text-muted d-block">Cost: {{ $product->cost_price !== null ? number_format($product->cost_price, 2) : '-' }}</small>
                                                <small class="fw-semibold">Sell: {{ $product->selling_price !== null ? number_format($product->selling_price, 2) : '-' }}</small>
                                            </div>
                                        </td>
                                        <td>
                                            <span class="badge bg-{{ $product->status === 'active' ? 'success' : 'danger' }}">{{ ucfirst($product->status) }}</span>
                                        </td>
                                        <td class="text-end pe-4">
                                            <div class="d-flex justify-content-end gap-2">
                                                <button class="btn btn-sm btn-outline-primary"
                                                    wire:click="openProductModal('{{ $product->id }}')"
                                                    title="Edit">
                                                    <i class="fa-solid fa-pen-to-square"></i>
                                                </button>
                                                <button class="btn btn-sm btn-outline-danger"
                                                    wire:click="confirmDelete('product', '{{ $product->id }}')"
                                                    title="Delete">
                                                    <i class="fa-solid fa-trash"></i>
                                                </button>
                                            </div>
                                        </td>
                                    </tr>
                                    @empty
                                    <tr>
                                        <td colspan="8" class="text-center py-5">
                                            <div class="d-flex flex-column align-items-center">
                                                <div class="bg-light rounded-circle d-flex align-items-center justify-content-center mb-3"
                                                    style="width: 80px; height: 80px;">
                                                    <i class="fa-solid fa-box fa-2x text-muted"></i>
                                                </div>
                                                <h6 class="mb-1">No products found</h6>
                                                <p class="text-muted mb-3">
                                                    @if($searchProduct)
                                                        No results match your search criteria
                                                    @else
                                                        Get started by adding your first product
                                                    @endif
                                                </p>
                                                @if(!$searchProduct)
                                                <button class="btn btn-success btn-sm" wire:click="openProductModal">
                                                    <i class="fa-solid fa-plus me-1"></i> Add Product
                                                </button>
                                                @endif
                                            </div>
                                        </td>
                                    </tr>
                                    @endforelse
                                </tbody>
                            </table>
                        </div>
                        @if($products->hasPages())
                        <div class="card-footer border-top">
                            {{ $products->links() }}
                        </div>
                        @endif
                    </div>
                </div>
                @endif
            </div>
        </div>
    </div>

    {{-- Category Modal --}}
    @if($showCategoryModal)
    <div class="modal fade show d-block" tabindex="-1" style="background: rgba(0,0,0,0.5); position: fixed; inset: 0; z-index: 1050; overflow-y: auto;">
        <div class="modal-dialog modal-dialog-centered">
            <div class="modal-content">
                <form wire:submit="saveCategory">
                    <div class="modal-header border-bottom">
                        <div>
                            <h5 class="modal-title mb-0">
                                <i class="fa-solid fa-tags me-2 text-primary"></i>
                                {{ $editingCategoryId ? 'Edit Category' : 'Add New Category' }}
                            </h5>
                            <small class="text-muted">
                                {{ $editingCategoryId ? 'Update category information' : 'Fill in the details to add a new category' }}
                            </small>
                        </div>
                        <button type="button" class="btn-close" wire:click="closeModal"></button>
                    </div>

                    <div class="modal-body">
                        <div class="row g-3">
                            <div class="col-md-8">
                                <label class="form-label">Category Name <span class="text-danger">*</span></label>
                                <input type="text" class="form-control @error('category_name') is-invalid @enderror"
                                    wire:model="category_name"
                                    placeholder="e.g., Electronics, Furniture, Stationery">
                                @error('category_name')
                                    <div class="invalid-feedback">{{ $message }}</div>
                                @enderror
                            </div>

                            <div class="col-md-4">
                                <label class="form-label">Code</label>
                                <input type="text" class="form-control @error('category_code') is-invalid @enderror"
                                    wire:model="category_code"
                                    placeholder="e.g., CAT-001">
                                @error('category_code')
                                    <div class="invalid-feedback">{{ $message }}</div>
                                @enderror
                            </div>

                            <div class="col-md-6">
                                <label class="form-label">Type <span class="text-danger">*</span></label>
                                <select class="form-select @error('category_type') is-invalid @enderror"
                                    wire:model="category_type">
                                    <option value="">Select Type</option>
                                    <option value="good">Good</option>
                                    <option value="asset">Asset</option>
                                    <option value="service">Service</option>
                                    <option value="work">Work</option>
                                    <option value="mixed">Mixed</option>
                                </select>
                                @error('category_type')
                                    <div class="invalid-feedback">{{ $message }}</div>
                                @enderror
                            </div>

                            <div class="col-md-6">
                                <label class="form-label">Status <span class="text-danger">*</span></label>
                                <select class="form-select @error('category_status') is-invalid @enderror"
                                    wire:model="category_status">
                                    <option value="active">Active</option>
                                    <option value="inactive">Inactive</option>
                                </select>
                                @error('category_status')
                                    <div class="invalid-feedback">{{ $message }}</div>
                                @enderror
                            </div>

                            <div class="col-12">
                                <label class="form-label">Description</label>
                                <textarea class="form-control @error('category_description') is-invalid @enderror"
                                    wire:model="category_description"
                                    rows="3"
                                    placeholder="Enter category description (optional)"></textarea>
                                @error('category_description')
                                    <div class="invalid-feedback">{{ $message }}</div>
                                @enderror
                            </div>
                        </div>
                    </div>

                    <div class="modal-footer border-top">
                        <button type="button" class="btn btn-light" wire:click="closeModal">
                            <i class="fa-solid fa-times me-1"></i> Cancel
                        </button>
                        <button type="submit" class="btn btn-primary" wire:loading.attr="disabled">
                            <span wire:loading.remove wire:target="saveCategory">
                                <i class="fa-solid fa-check me-1"></i>
                                {{ $editingCategoryId ? 'Update Category' : 'Create Category' }}
                            </span>
                            <span wire:loading wire:target="saveCategory">
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

    {{-- Product Modal --}}
    @if($showProductModal)
    <div class="modal fade show d-block" tabindex="-1" style="background: rgba(0,0,0,0.5); position: fixed; inset: 0; z-index: 1050; overflow-y: auto;">
        <div class="modal-dialog modal-dialog-centered modal-lg">
            <div class="modal-content">
                <form wire:submit="saveProduct">
                    <div class="modal-header border-bottom">
                        <div>
                            <h5 class="modal-title mb-0">
                                <i class="fa-solid fa-box me-2 text-success"></i>
                                {{ $editingProductId ? 'Edit Product' : 'Add New Product' }}
                            </h5>
                            <small class="text-muted">
                                {{ $editingProductId ? 'Update product information' : 'Fill in the details to add a new product' }}
                            </small>
                        </div>
                        <button type="button" class="btn-close" wire:click="closeModal"></button>
                    </div>

                    <div class="modal-body">
                        <div class="row g-3">
                            <div class="col-md-12">
                                <label class="form-label">Product Name <span class="text-danger">*</span></label>
                                <input type="text" class="form-control @error('product_name') is-invalid @enderror"
                                    wire:model="product_name"
                                    placeholder="Enter product name">
                                @error('product_name')
                                    <div class="invalid-feedback">{{ $message }}</div>
                                @enderror
                            </div>

                            <div class="col-md-4">
                                <label class="form-label">Product Code</label>
                                <input type="text" class="form-control @error('product_code') is-invalid @enderror"
                                    wire:model="product_code"
                                    placeholder="e.g., PRD-001">
                                @error('product_code')
                                    <div class="invalid-feedback">{{ $message }}</div>
                                @enderror
                            </div>

                            <div class="col-md-4">
                                <label class="form-label">Barcode</label>
                                <input type="text" class="form-control @error('product_barcode') is-invalid @enderror"
                                    wire:model="product_barcode"
                                    placeholder="Enter barcode">
                                @error('product_barcode')
                                    <div class="invalid-feedback">{{ $message }}</div>
                                @enderror
                            </div>

                            <div class="col-md-4">
                                <label class="form-label">Unit</label>
                                <input type="text" class="form-control @error('product_unit') is-invalid @enderror"
                                    wire:model="product_unit"
                                    placeholder="e.g., pcs, kg, box">
                                @error('product_unit')
                                    <div class="invalid-feedback">{{ $message }}</div>
                                @enderror
                            </div>

                            <div class="col-md-6">
                                <label class="form-label">Category <span class="text-danger">*</span></label>
                                <select class="form-select @error('product_category_id') is-invalid @enderror"
                                    wire:model="product_category_id">
                                    <option value="">Select Category</option>
                                    @foreach($categoriesForSelect as $cat)
                                        <option value="{{ $cat->id }}">{{ $cat->name }}</option>
                                    @endforeach
                                </select>
                                @error('product_category_id')
                                    <div class="invalid-feedback">{{ $message }}</div>
                                @enderror
                            </div>

                            <div class="col-md-6">
                                <label class="form-label">Type <span class="text-danger">*</span></label>
                                <select class="form-select @error('product_type') is-invalid @enderror"
                                    wire:model="product_type">
                                    <option value="">Select Type</option>
                                    <option value="good">Good</option>
                                    <option value="asset">Asset</option>
                                    <option value="service">Service</option>
                                    <option value="work">Work</option>
                                    <option value="mixed">Mixed</option>
                                </select>
                                @error('product_type')
                                    <div class="invalid-feedback">{{ $message }}</div>
                                @enderror
                            </div>

                            <div class="col-md-4">
                                <label class="form-label">Cost Price</label>
                                <input type="number" step="0.01" class="form-control @error('product_cost_price') is-invalid @enderror"
                                    wire:model="product_cost_price"
                                    placeholder="0.00">
                                @error('product_cost_price')
                                    <div class="invalid-feedback">{{ $message }}</div>
                                @enderror
                            </div>

                            <div class="col-md-4">
                                <label class="form-label">Selling Price</label>
                                <input type="number" step="0.01" class="form-control @error('product_selling_price') is-invalid @enderror"
                                    wire:model="product_selling_price"
                                    placeholder="0.00">
                                @error('product_selling_price')
                                    <div class="invalid-feedback">{{ $message }}</div>
                                @enderror
                            </div>

                            <div class="col-md-4">
                                <label class="form-label">Reorder Level</label>
                                <input type="number" class="form-control @error('product_reorder_level') is-invalid @enderror"
                                    wire:model="product_reorder_level"
                                    placeholder="0">
                                @error('product_reorder_level')
                                    <div class="invalid-feedback">{{ $message }}</div>
                                @enderror
                            </div>

                            <div class="col-md-4">
                                <label class="form-label">Useful Life (Years)</label>
                                <input type="number" class="form-control @error('product_useful_life') is-invalid @enderror"
                                    wire:model="product_useful_life"
                                    placeholder="0">
                                @error('product_useful_life')
                                    <div class="invalid-feedback">{{ $message }}</div>
                                @enderror
                            </div>

                            <div class="col-md-4">
                                <label class="form-label">Status <span class="text-danger">*</span></label>
                                <select class="form-select @error('product_status') is-invalid @enderror"
                                    wire:model="product_status">
                                    <option value="active">Active</option>
                                    <option value="inactive">Inactive</option>
                                </select>
                                @error('product_status')
                                    <div class="invalid-feedback">{{ $message }}</div>
                                @enderror
                            </div>

                            <div class="col-md-4">
                                <label class="form-label d-block mb-2">Options</label>
                                <div class="form-check form-check-inline">
                                    <input class="form-check-input" type="checkbox" id="track_stock" wire:model="product_track_stock">
                                    <label class="form-check-label" for="track_stock">Track Stock</label>
                                </div>
                                <div class="form-check form-check-inline">
                                    <input class="form-check-input" type="checkbox" id="is_serialized" wire:model="product_is_serialized">
                                    <label class="form-check-label" for="is_serialized">Serialized</label>
                                </div>
                                <div class="form-check form-check-inline">
                                    <input class="form-check-input" type="checkbox" id="requires_approval" wire:model="product_requires_approval">
                                    <label class="form-check-label" for="requires_approval">Requires Approval</label>
                                </div>
                            </div>

                            <div class="col-12">
                                <label class="form-label">Description</label>
                                <textarea class="form-control @error('product_description') is-invalid @enderror"
                                    wire:model="product_description"
                                    rows="3"
                                    placeholder="Enter product description (optional)"></textarea>
                                @error('product_description')
                                    <div class="invalid-feedback">{{ $message }}</div>
                                @enderror
                            </div>
                        </div>
                    </div>

                    <div class="modal-footer border-top">
                        <button type="button" class="btn btn-light" wire:click="closeModal">
                            <i class="fa-solid fa-times me-1"></i> Cancel
                        </button>
                        <button type="submit" class="btn btn-success" wire:loading.attr="disabled">
                            <span wire:loading.remove wire:target="saveProduct">
                                <i class="fa-solid fa-check me-1"></i>
                                {{ $editingProductId ? 'Update Product' : 'Create Product' }}
                            </span>
                            <span wire:loading wire:target="saveProduct">
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
                    <button type="button" class="btn btn-danger" wire:click="delete">
                        <i class="fa-solid fa-trash me-1"></i> Delete
                    </button>
                </div>
            </div>
        </div>
    </div>
    @endif
</div>
