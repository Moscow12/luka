<div>
    <div>
        <h5 class="mb-5">Deductions</h5>
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
                    ADD DEDUCTION
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
                                    <th class="listjs-sorter" data-sort="task_title">Name</th>
                                    <th class="listjs-sorter" data-sort="task_type">Mode Percentage</th>
                                    <th class="listjs-sorter" data-sort="task_type">Deduction Type</th>
                                    <th class="listjs-sorter" data-sort="task_type">Amount</th>
                                    <th>Actions</th>
                                </tr>
                            </thead>
                            <tbody class="list">
                                @php
                                $number = 1;
                                @endphp
                                @foreach($deductions as $deduction)
                                <tr>
                                    <td>
                                        {{ $number++ }}
                                    </td>
                                    <td class="name">{{ $deduction->name }}</td>
                                    <td class="task_type">{{ $deduction->modepercentage }}</td>
                                    <td class="task_type">{{ $deduction->Deduction_Type }}</td>
                                    <td class="task_type">{{ $deduction->Deduction_Type }}</td>
                                    <td class="task_type">{{ $deduction->Amount }}</td>
                                    <td>
                                        <button class="btn btn-sm btn-warning" wire:click="openModal('edit','{{ $deduction->id }}')">Edit</button>
                                        <button class="btn btn-sm btn-danger" wire:click="delete('{{ $deduction->id }}')"
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
                                    {{ $deductions->links() }}
                                </div>
                            </div>
                        </div>
                    </div>
                </div>
            </div>
        </div>
    </div>
    <!-- modal -->
    <x-pages.model :showModal="$showModal" :formaction="$modalMode === 'edit' ? 'update' : 'save'" :modalMode="$modalMode" :title="$modalMode === 'edit' ? 'Edit Deduction' : 'Add Deduction'">
        @csrf
       <x-forms.input type="text" label="Name" name="name" wireModel="name" placeholder="Enter Deduction Name" :error="$errors->first('name')" />
       
       <x-forms.input type="text" label="Mode" name="Mode" wireModel="Mode" placeholder="Enter Mode" :error="$errors->first('Mode')" required />
       <x-forms.input type="text" label="Deduction Type" name="Deduction_Type" wireModel="Deduction_Type" placeholder="Enter Deduction Type" :error="$errors->first('Deduction_Type')" />
       <x-forms.input type="text" label="Amount" name="Amount" wireModel="Amount" placeholder="Enter Amount" :error="$errors->first('Amount')" />
       <x-forms.input type="text" label="Description" name="Description" wireModel="Description" placeholder="Enter Description" :error="$errors->first('Description')" />
       <x-forms.checkbox name="modepercentage" label="Mode Percentage" value="true" wireModel="modepercentage" placeholder="Enter Mode Percentage" :error="$errors->first('modepercentage')" />
    </x-pages.model>
</div>