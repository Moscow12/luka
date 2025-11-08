<div>
    <div>
        <h5 class="mb-5">Deductions</h5>
    </div>
    <div class="d-flex flex-column gap-6">
        <div class="d-flex flex-md-row flex-column gap-2 justify-content-between">
            <div class="d-flex flex-row gap-3 align-items-center">
                <div>
                    <form>
                        <input class="form-control" type="search" wire:model.live="search" placeholder="Search" />
                    </form>
                </div>
                <a href="#!" class="text-inherit">
                    <i class="fa-solid fa-filter"></i>
                    <span>Filter</span>
                </a>
            </div>
            <div>
                <x-forms.button-model name="ADD DEDUCTION" />
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
                                    <th>Type</th>
                                    <th>Value</th>
                                    <th>Applies To</th>
                                    <th>Status</th>
                                    <th>Description</th>
                                    <th>Actions</th>
                                </tr>
                            </thead>
                            <tbody class="list">
                                @php
                                $number = 1;
                                @endphp
                                @foreach($deductions as $deduction)
                                <tr>
                                    <td>{{ $number++ }}</td>
                                    <td class="name">{{ $deduction->name }}</td>
                                    <td>
                                        <span class="badge {{ $deduction->type === 'fixed' ? 'bg-primary' : 'bg-info' }}">
                                            {{ ucfirst($deduction->type) }}
                                        </span>
                                    </td>
                                    <td>{{ number_format($deduction->deduction_value, 2) }}{{ $deduction->type === 'percentage' ? '%' : '' }}</td>
                                    <td>{{ $deduction->applies_to }}</td>
                                    <td>
                                        @if($deduction->is_active)
                                            <span class="badge bg-success">Active</span>
                                        @else
                                            <span class="badge bg-danger">Inactive</span>
                                        @endif
                                    </td>
                                    <td>{{ Str::limit($deduction->description, 30) }}</td>
                                    <td>
                                        <x-forms.button-model name="EDIT" :classbtn="'fa-solid fa-pencil'" wire:click="openModal('edit', '{{ $deduction->id }}')" />
                                        <button class="btn btn-sm btn-danger" wire:click="delete('{{ $deduction->id }}')"
                                            onclick="return confirm('Delete this deduction?')">
                                            <i class="fa-solid fa-trash"></i>
                                        </button>
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

    <x-pages.model :showModal="$showModal"
                   :formaction="$modalMode === 'edit' ? 'update' : 'save'"
                   :modalMode="$modalMode"
                   :title="$modalMode === 'edit' ? 'Edit Deduction' : 'Add Deduction'">
        <x-forms.input type="text" name="name" label="Name" placeholder="Enter Deduction Name" required />
        <x-forms.input type="select" name="type" label="Type" :options="['fixed' => 'Fixed', 'percentage' => 'Percentage']" required />
        <x-forms.input type="number" name="deduction_value" label="Value" placeholder="Enter Value" step="0.01" required />
        <x-forms.input type="text" name="applies_to" label="Applies To" placeholder="e.g., All Employees, Management" required />
        <x-forms.input type="textarea" name="description" label="Description" placeholder="Enter Description" rows="2" />

        <div class="mb-3">
            <div class="form-check">
                <input type="checkbox" class="form-check-input" wire:model="is_active" id="is_active">
                <label class="form-check-label" for="is_active">Active</label>
            </div>
        </div>
    </x-pages.model>
</div>