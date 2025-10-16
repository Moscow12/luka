<div>
    <div>
        <h5 class="mb-5">Shifts</h5>
    </div>
    <div class="d-flex flex-column gap-6">
        <div class="d-flex flex-md-row flex-column gap-2 justify-content-between">
            <div class="d-flex flex-row gap-3 align-items-center">
                <div>
                    <form>
                        <input class="form-control" type="search" value="" placeholder="Search" />
                    </form>
                </div>
                <a href="#!" class="text-inherit">
                    <span>
                        <svg
                            xmlns="http://www.w3.org/2000/svg"
                            width="14"
                            height="14"
                            viewBox="0 0 24 24"
                            fill="none"
                            stroke="currentColor"
                            stroke-width="1.5"
                            stroke-linecap="round"
                            stroke-linejoin="round"
                            class="icon icon-tabler icons-tabler-outline icon-tabler-adjustments">
                            <path stroke="none" d="M0 0h24v24H0z" fill="none" />
                            <path d="M4 10a2 2 0 1 0 4 0a2 2 0 0 0 -4 0" />
                            <path d="M6 4v4" />
                            <path d="M6 12v8" />
                            <path d="M10 16a2 2 0 1 0 4 0a2 2 0 0 0 -4 0" />
                            <path d="M12 4v10" />
                            <path d="M12 18v2" />
                            <path d="M16 7a2 2 0 1 0 4 0a2 2 0 0 0 -4 0" />
                            <path d="M18 4v1" />
                            <path d="M18 9v11" />
                        </svg>
                    </span>
                    <span>Filter</span>
                </a>
            </div>
            <div>
                <button class="btn btn-primary d-flex flex-row gap-1 align-items-center" wire:click="$set('showModal', true)">
                    <i class="fa-solid fa-plus"></i>
                    ADD SHIFT TYPE
                </button>
            </div>
        </div>
        <div>
            <div class="card card-lg overflow-hidden" id="taskTable" data-list="shift_title,shift_type,shift_assigned,shift_date,shift_priority">
                <div class="card-body p-0">
                    <div class="table-responsive">
                        @if (session()->has('success'))
                            <div class="alert alert-success dismissible fade show" role="alert" data-bs-dismiss="alert" aria-label="Close">
                                {{ session('success') }}
                                
                            </div>
                        @endif
                        <table class="table text-nowrap mb-0 table-centered table-hover" data-check-container="">
                            <thead>
                                <tr>
                                    <th>
                                       #
                                    </th>
                                    <th class="listjs-sorter" data-sort="task_title">Shift name</th>
                                    <th class="listjs-sorter" data-sort="task_type">Start Time</th>
                                    <th class="listjs-sorter" data-sort="task_assigned">End Time</th>
                                    <th class="listjs-sorter" data-sort="task_assigned">Count late at</th>
                                    <th class="listjs-sorter" data-sort="task_date">Count late by</th>
                                    <th>Actions</th>
                                </tr>
                            </thead>
                            <tbody class="list">
                                @php
                                    $number = 1;
                                @endphp
                                @foreach($shifttypes as $shift)
                                    <tr>
                                        <td class="pe-0">
                                            {{ $number++ }}
                                        </td>
                                        <td class="task_title">{{ $shift->name }}</td>
                                        <td class="task_type">{{ $shift->start_time }}</td>
                                        <td class="task_assigned">{{ $shift->end_time }}</td>
                                        <td class="task_date">{{ $shift->count_early }}</td>
                                        <td class="task_priority">{{ $shift->count_late }}</td>
                                        <td class="text-center">
                                            <button class="btn btn-sm btn-warning" wire:click="openModal('edit','{{ $shift->id }}')"><i class="fa fa-edit"></i></button>
                                            <button class="btn btn-sm btn-danger" wire:click="delete('{{ $shift->id }}')" 
                                                onclick="return confirm('Delete this shift?')"><i class="fa fa-trash"></i></button>
                                        </td>
                                    </tr>
                                @endforeach
                                
                                
                            </tbody>
                        </table>
                    </div>

                    <div class="btn-toolbar card-footer border-top border-dashed d-flex flex-md-row flex-column justify-content-md-between align-items-md-center">
                        <p class="mb-0 listjs-showing-items-label"></p>
                        <div class="d-flex gap-4">
                            <div class="d-flex align-items-center gap-2">
                                <label class="form-label text-nowrap mb-0">Rows per page:</label>
                                <select class="form-select listjs-items-per-page" data-choices="">
                                    <option value="5" selected>5</option>
                                </select>
                            </div>
                            <div>
                                <div class="pagination-buttons d-flex">
                                    <button class="btn btn-white prev">Previous</button>
                                    <ul class="pagination mb-0 ms-1"></ul>
                                    <button class="btn btn-white next">Next</button>
                                </div>
                            </div>
                        </div>
                    </div>
                </div>
            </div>
        </div>
    </div>
    <!-- Modal -->
    <div class="modal fade @if($showModal) show d-block @endif" tabindex="-1"
        @if($showModal) style="background: rgba(0,0,0,0.5);" @endif>
        <div class="modal-dialog">
            <div class="modal-content">
                <form wire:submit.prevent="save">
                    <div class="modal-header">
                        <h5 class="modal-title">
                            {{ $modalMode === 'edit' ? 'Edit Shift Type' : 'Add Shift Type' }}
                        </h5>
                        <button type="button" class="btn-close" wire:click="$set('showModal', false)"></button>
                    </div>

                    <div class="modal-body">
                        <form wire:submit.prevent="save">
                            @csrf
                            <div class="mb-3">
                                <label class="form-label" for="contact-name-field">Name</label>
                                <input type="text" class="form-control" id="contact-name-field" wire:model="name">
                                @error('name')
                                    <span class="invalid-feedback" role="alert">
                                        <strong>{{ $message }}</strong>
                                    </span>
                                @enderror
                            </div>
                            <!-- use shift model -->
                             <div class="mb-3">
                                <label class="form-label" for="contact-email-field">Start Time</label>
                                <input type="time" class="form-control"  wire:model="start_time">
                                @error('start_time')
                                    <span class="invalid-feedback" role="alert">
                                        <strong>{{ $message }}</strong>
                                    </span>
                                @enderror
                            </div>
                            <div class="mb-3">
                                <label class="form-label" for="contact-email-field">End Time</label>
                                <input type="time" class="form-control"  wire:model="end_time">
                                @error('end_time')
                                    <span class="invalid-feedback" role="alert">
                                        <strong>{{ $message }}</strong>
                                    </span>
                                @enderror
                            </div>
                            <div class="mb-3">
                                <label class="form-label" for="contact-email-field">Count Early</label>
                                <input type="text" class="form-control"  wire:model="count_early">
                                @error('count_early')
                                    <span class="invalid-feedback" role="alert">
                                        <strong>{{ $message }}</strong>
                                    </span>
                                @enderror
                            </div>
                            <div class="mb-3">
                                <label class="form-label" for="contact-email-field">Count Late</label>
                                <input type="text" class="form-control"  wire:model="count_late">
                                @error('count_late')
                                    <span class="invalid-feedback" role="alert">
                                        <strong>{{ $message }}</strong>
                                    </span>
                                @enderror
                            </div>
                            
                        </form>
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
