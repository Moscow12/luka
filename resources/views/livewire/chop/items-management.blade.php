<div>
    <div>
        <h5 class="mb-5">Chop Items</h5>
    </div>
    <div class="d-flex flex-column gap-6">
        <div class="d-flex flex-md-row flex-column gap-2 justify-content-between">
            <div class="d-flex flex-row gap-3 align-items-center">
                <div>
                    <form>
                        <input class="form-control" type="search" wire:model.live="search" placeholder="Search items" />
                    </form>
                </div>
            </div>
            <div>
                <x-forms.button-model name="ADD CHOP ITEM" />
            </div>
        </div>
        <div>
            <div class="card card-lg overflow-hidden" id="taskTable" data-list="name">
                <div class="card-body p-0">
                    <div class="table-responsive">
                        @if(session()->has('success'))
                            <div class="alert alert-success alert-dismissible fade show" role="alert">
                                {{ session('success') }}
                                <button type="button" class="btn-close" data-bs-dismiss="alert" aria-label="Close"></button>
                            </div>
                        @endif

                        <table class="table text-nowrap mb-0 table-centered table-hover" data-check-container="">
                            <thead>
                                <tr>
                                    <th>#</th>
                                    <th>Name</th>
                                    <th>Category</th>
                                    <th>GFC Code</th>
                                    <th>Unit</th>
                                    <th>Quantity</th>
                                    <th>Price</th>
                                    <th>Status</th>
                                    <th>Actions</th>
                                </tr>
                            </thead>
                            <tbody class="list">
                                @php
                                    $number = 1;
                                @endphp
                                @forelse($items as $item)
                                <tr>
                                    <td>{{ $number++ }}</td>
                                    <td class="name"><strong>{{ $item->name }}</strong></td>
                                    <td>
                                        @if($item->category)
                                            <span class="badge bg-primary">{{ $item->category->name }}</span>
                                        @else
                                            <span class="badge bg-secondary">Uncategorized</span>
                                        @endif
                                    </td>
                                    <td>
                                        @if($item->gfc_code)
                                            <code class="bg-light px-2 py-1 rounded">{{ $item->gfc_code }}</code>
                                        @else
                                            <span class="text-muted">-</span>
                                        @endif
                                    </td>
                                    <td>{{ $item->unit ?? '-' }}</td>
                                    <td>{{ $item->quantity ?? '-' }}</td>
                                    <td>
                                        @if($item->price)
                                            <strong>{{ number_format($item->price, 2) }}</strong>
                                        @else
                                            <span class="text-muted">-</span>
                                        @endif
                                    </td>
                                    <td>
                                        @if($item->is_active)
                                            <span class="badge bg-success">Active</span>
                                        @else
                                            <span class="badge bg-danger">Inactive</span>
                                        @endif
                                    </td>
                                    <td>
                                        <x-forms.button-model name="EDIT" :classbtn="'fa-solid fa-pencil'" wire:click="openModal('edit', '{{ $item->id }}')" />
                                        <button class="btn btn-sm btn-danger" wire:click="delete('{{ $item->id }}')"
                                            onclick="return confirm('Delete this item?')">
                                            <i class="fa-solid fa-trash"></i>
                                        </button>
                                    </td>
                                </tr>
                                @empty
                                <tr>
                                    <td colspan="9" class="text-center py-4">
                                        <div class="text-muted">
                                            <i class="fa-solid fa-inbox fa-3x mb-3"></i>
                                            <p>No chop items found</p>
                                        </div>
                                    </td>
                                </tr>
                                @endforelse
                            </tbody>
                        </table>
                    </div>

                    <div class="btn-toolbar card-footer border-top border-dashed d-flex flex-md-row flex-column justify-content-md-between align-items-md-center">
                        <div class="d-flex gap-4">
                            <div>
                                <div class="pagination-buttons d-flex">
                                    {{ $items->links() }}
                                </div>
                            </div>
                        </div>
                    </div>
                </div>
            </div>
        </div>
    </div>

    <x-pages.model :title="$modalMode === 'edit' ? 'Edit Chop Item' : 'Add Chop Item'"
                   :formaction="$modalMode === 'edit' ? 'update' : 'save'"
                   :modalMode="$modalMode"
                   :showModal="$showModal">
        <div class="row">
            <div class="col-md-8">
                <x-forms.input type="text" name="name" label="Item Name" placeholder="Enter Item Name" required />
            </div>
            <div class="col-md-4">
                <x-forms.input type="text" name="slug" label="Slug" placeholder="item-slug" readonly />
            </div>
        </div>

        <div class="row">
            <div class="col-md-6">
                <x-forms.input type="select" name="category_id" label="Category"
                    :options="['' => 'Select Category'] + $categories->pluck('name', 'id')->toArray()" />
            </div>
            <div class="col-md-6">
                <x-forms.input type="text" name="gfc_code" label="GFC Code" placeholder="Enter GFC Code" />
            </div>
        </div>

        <div class="row">
            <div class="col-md-4">
                <x-forms.input type="text" name="unit" label="Unit" placeholder="e.g., kg, pcs" />
            </div>
            <div class="col-md-4">
                <x-forms.input type="number" name="quantity" label="Quantity" placeholder="0" min="0" />
            </div>
            <div class="col-md-4">
                <x-forms.input type="number" name="price" label="Price" placeholder="0.00" step="0.01" min="0" />
            </div>
        </div>

        <x-forms.input type="textarea" name="description" label="Description" placeholder="Enter Description" rows="3" />

        <div class="mb-3">
            <div class="form-check">
                <input type="checkbox" class="form-check-input" wire:model="is_active" id="is_active_item">
                <label class="form-check-label" for="is_active_item">Active</label>
            </div>
        </div>
    </x-pages.model>
</div>
