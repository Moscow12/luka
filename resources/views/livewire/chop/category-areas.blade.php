<div>
    <div>
        <h5 class="mb-5">Category Areas</h5>
    </div>
    <div class="d-flex flex-column gap-6">
        <div class="d-flex flex-md-row flex-column gap-2 justify-content-between">
            <div class="d-flex flex-row gap-3 align-items-center">
                <div>
                    <form>
                        <input class="form-control" type="search" wire:model.live="search" placeholder="Search categories" />
                    </form>
                </div>
            </div>
            <div>
                <x-forms.button-model name="ADD CATEGORY AREA" />
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

                        @if(session()->has('error'))
                            <div class="alert alert-danger alert-dismissible fade show" role="alert">
                                {{ session('error') }}
                                <button type="button" class="btn-close" data-bs-dismiss="alert" aria-label="Close"></button>
                            </div>
                        @endif

                        <table class="table text-nowrap mb-0 table-centered table-hover" data-check-container="">
                            <thead>
                                <tr>
                                    <th>#</th>
                                    <th>Name</th>
                                    <th>Slug</th>
                                    <th>Items Count</th>
                                    <th>Description</th>
                                    <th>Actions</th>
                                </tr>
                            </thead>
                            <tbody class="list">
                                @php
                                    $number = 1;
                                @endphp
                                @forelse($categories as $category)
                                <tr>
                                    <td>{{ $number++ }}</td>
                                    <td class="name"><strong>{{ $category->name }}</strong></td>
                                    <td><code>{{ $category->slug }}</code></td>
                                    <td>
                                        <span class="badge bg-info">{{ $category->chop_items_count }} items</span>
                                    </td>
                                    <td>{{ Str::limit($category->description, 40) }}</td>
                                    <td>
                                        <x-forms.button-model name="EDIT" :classbtn="'fa-solid fa-pencil'" wire:click="openModal('edit', '{{ $category->id }}')" />
                                        
                                    </td>
                                </tr>
                                @empty
                                <tr>
                                    <td colspan="6" class="text-center py-4">
                                        <div class="text-muted">
                                            <i class="fa-solid fa-inbox fa-3x mb-3"></i>
                                            <p>No category areas found</p>
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
                                    {{ $categories->links() }}
                                </div>
                            </div>
                        </div>
                    </div>
                </div>
            </div>
        </div>
    </div>

    <x-pages.model :title="$modalMode === 'edit' ? 'Edit Category Area' : 'Add Category Area'"
                   :formaction="$modalMode === 'edit' ? 'update' : 'save'"
                   :modalMode="$modalMode"
                   :showModal="$showModal">
        <x-forms.input type="text" name="name" label="Name" placeholder="Enter Category Name" required />
        <x-forms.input type="text" name="slug" label="Slug" placeholder="category-slug" readonly />
        <x-forms.input type="textarea" name="description" label="Description" placeholder="Enter Description" rows="3" />
    </x-pages.model>
</div>
