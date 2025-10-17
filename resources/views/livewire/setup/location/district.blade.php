<div>
    <div>
        <h5 class="mb-5">Districts</h5>
    </div>
    <div class="d-flex flex-column gap-6">
        <div class="d-flex flex-md-row flex-column gap-2 justify-content-between">
            <div class="d-flex flex-row gap-3 align-items-center">
                <div>
                    <form>
                        <input class="form-control" type="search" wire:model.live="search" placeholder="Search">
                    </form>
                </div>
               
                <div >
                    <select name="region_id" id="region_id" class="form-select" wire:model.live="region_id">
                        <option value="">Select region</option>
                        @foreach($regions as $region)
                        <option value="{{ $region->id }}">{{ $region->name }}</option>
                        @endforeach
                    </select>
                </div>
                <a href="#!" class="text-inherit">
                    <i class="fa-solid fa-filter"></i>
                    <span>Filter</span>
                </a>
            </div>
            <div>
                <button class="btn btn-primary d-flex flex-row gap-1 align-items-center" wire:click="$set('showModal', true)">
                    <i class="fa-solid fa-plus"></i>
                    ADD district
                </button>
            </div>
        </div>
        <div>
            <div class="card card-lg overflow-hidden" id="taskTable" data-list="name">
                <div class="card-body p-0">
                    <div class="table-responsive">
                        @if (session()->has('success'))
                        <div class="alert alert-success">{{ session('success') }}</div>
                        @endif

                        <table class="table text-nowrap mb-0 table-centered table-hover" data-check-container="">
                            <thead>
                                <tr>
                                    <th>
                                        #
                                    </th>
                                    <th class="listjs-sorter" data-sort="task_title"> name</th>
                                    <th class="listjs-sorter" data-sort="task_type">Description</th>
                                    <th>Actions</th>
                                </tr>
                            </thead>
                            <tbody class="list">
                                @php
                                $number = 1;
                                @endphp
                                @foreach($districts as $district)
                                <tr>
                                    <td>
                                        {{ $number++ }}
                                    </td>
                                    <td class="name">{{ $district->name }}</td>
                                    <td class="task_type">{{ $district->regions->name ?? 'N/A' }}</td>
                                    <td>
                                        <button class="btn btn-sm btn-warning" wire:click="openModal('edit','{{ $district->id }}')">Edit</button>
                                        <button class="btn btn-sm btn-danger" wire:click="delete('{{ $district->id }}')"
                                            onclick="return confirm('Delete this department?')">Delete</button>
                                    </td>

                                </tr>
                                @endforeach


                            </tbody>
                        </table>
                    </div>

                    <div class="btn-toolbar card-footer border-top border-dashed d-flex flex-md-row flex-column justify-content-md-between align-items-md-center">

                        <div class="d-flex gap-4">
                            <div>
                                <div class="pagination-buttons d-flex">
                                    {{ $districts->links() }}
                                </div>
                            </div>
                        </div>
                    </div>
                </div>
            </div>
        </div>
    </div>

    <!-- Modal -->
    <!-- Modal -->
    <div class="modal fade @if($showModal) show d-block @endif" tabindex="-1"
        @if($showModal) style="background: rgba(0,0,0,0.5);" @endif>
        <div class="modal-dialog">
            <div class="modal-content">
                <form wire:submit.prevent="save">
                    <div class="modal-header">
                        <h5 class="modal-title">
                            {{ $modalMode === 'edit' ? 'Edit district' : 'Add district' }}
                        </h5>
                        <button type="button" class="btn-close" wire:click="$set('showModal', false)"></button>
                    </div>

                    <div class="modal-body">
                        <div class="mb-3">
                            <label>Redion</label>
                            <select class="form-select" wire:model="region_id" placeholder="region" required>
                                <option selected disabled value="">Choose...</option>
                                @foreach($regions as $region)

                                <option value="{{ $region->id }}">{{ $region->name }}</option>

                                @endforeach

                            </select>
                            @error('name') <small class="text-danger">{{ $message }}</small> @enderror
                        </div>
                        <div class="mb-3">
                            <label>district Name</label>
                            <input type="text" class="form-control" wire:model="name" placeholder="Enter district Name">
                            @error('name') <small class="text-danger">{{ $message }}</small> @enderror
                        </div>

                    </div>

                    <div class="modal-footer">
                        <button type="button" class="btn btn-secondary" wire:click="$set('showModal', false)">close</button>
                        <button type="submit" class="btn btn-primary">
                            {{ $modalMode === 'edit' ? 'Update' : 'Save' }}
                        </button>
                    </div>
                </form>
            </div>
        </div>
    </div>
</div>