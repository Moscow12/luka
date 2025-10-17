<div>
    <div>
        <h5 class="mb-5">Allowances</h5>
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
                    <i class="fa-solid fa-filter"></i>
                    <span>Filter</span>
                </a>
            </div>
            <div>
                <button class="btn btn-primary d-flex flex-row gap-1 align-items-center" wire:click="$set('showModal', true)">
                    <i class="fa-solid fa-plus"></i>
                    ADD ALLOWANCE
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
                                    <th class="listjs-sorter" data-sort="task_title"> Pay Grade</th>
                                    <th class="listjs-sorter" data-sort="task_type">Job Title</th>
                                    <th class="listjs-sorter" data-sort="task_type">Minimum Salary</th>
                                    <th class="listjs-sorter" data-sort="task_type">Mid Point Salary</th>
                                    <th class="listjs-sorter" data-sort="task_type">Maximum Salary</th>
                                    <th>Actions</th>
                                </tr>
                            </thead>
                            <tbody class="list">
                                @php
                                    $number = 1;
                                @endphp
                                @foreach($allowances as $allowance)
                                <tr>
                                    <td>
                                        {{ $number++ }}
                                    </td>
                                    <td class="name">{{ $allowance->pay_grade }}</td>
                                    <td class="task_type">{{ $allowance->job_title }}</td>
                                    <td class="task_type">{{ $allowance->min_salary }}</td>
                                    <td class="task_type">{{ $allowance->mid_point_salary }}</td>
                                    <td class="task_type">{{ $allowance->max_salary }}</td>
                                    <td>
                                        <button class="btn btn-sm btn-warning" wire:click="openModal('edit','{{ $allowance->id }}')">Edit</button>
                                        <button class="btn btn-sm btn-danger" wire:click="delete('{{ $allowance->id }}')"
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
                                    {{ $allowances->links() }}
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
                            {{ $modalMode === 'edit' ? 'Edit allowance' : 'Add allowance' }}
                        </h5>
                        <button type="button" class="btn-close" wire:click="$set('showModal', false)"></button>
                    </div>

                    <div class="modal-body">
                        <div class="mb-3">
                            <label>Pay Grade</label>
                            <input type="text" class="form-control" wire:model="Pay_Grade" placeholder="Enter Pay Grade">
                            @error('Pay_Grade') <small class="text-danger">{{ $message }}</small> @enderror
                        </div>
                        <div class="mb-3">
                            <label>Job Title</label>
                            <input type="text" class="form-control" wire:model="Job_Title" placeholder="Enter Job Title">
                            @error('Job_Title') <small class="text-danger">{{ $message }}</small> @enderror
                        </div>
                        <div class="mb-3">
                            <label>Minimum Salary</label>
                            <input type="text" class="form-control" wire:model="Minimum_Salary" placeholder="Enter Minimum Salary">
                            @error('Minimum_Salary') <small class="text-danger">{{ $message }}</small> @enderror
                        </div>
                        <div class="mb-3">
                            <label>Mid Point Salary</label>
                            <input type="text" class="form-control" wire:model="Mid_Point_Salary" placeholder="Enter Mid Point Salary">
                            @error('Mid_Point_Salary') <small class="text-danger">{{ $message }}</small> @enderror
                        </div>
                        <div class="mb-3">
                            <label>Maximum Salary</label>
                            <input type="text" class="form-control" wire:model="Maximum_Salary" placeholder="Enter Maximum Salary">
                            @error('Maximum_Salary') <small class="text-danger">{{ $message }}</small> @enderror
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
