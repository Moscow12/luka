<div>
    <div>
        <h5 class="mb-5">Employee Approval Mappings</h5>
    </div>
    <div class="d-flex flex-column gap-6">
        <div class="d-flex flex-md-row flex-column gap-2 justify-content-between">
            <div class="d-flex flex-row gap-3 align-items-center">
                <div class="input-group" style="width: 300px;">
                    <span class="input-group-text bg-white">
                        <i class="fa-solid fa-magnifying-glass"></i>
                    </span>
                    <input class="form-control" type="search" wire:model.live.debounce.300ms="search"
                           placeholder="Search employee, approval level..." />
                </div>
            </div>
            <div>
                <x-forms.button-model name="ADD EMPLOYEE MAPPING" />
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
                                    <th>Approval Level</th>
                                    <th>Document Type</th>
                                    <th>Employee</th>
                                    <th>Employee No</th>
                                    <th>Status</th>
                                    <th>Actions</th>
                                </tr>
                            </thead>
                            <tbody class="list">
                                @php
                                    $number = 1;
                                @endphp
                                @foreach($employeeMappings as $mapping)
                                <tr>
                                    <td>{{ $number++ }}</td>
                                    <td>
                                        <span class="badge bg-info">
                                            {{ $mapping->approval_level->name ?? 'N/A' }}
                                        </span>
                                    </td>
                                    <td>
                                        @if($mapping->approval_level && $mapping->approval_level->approvalleveltodocuments->count() > 0)
                                            <div class="d-flex flex-wrap gap-1">
                                                @foreach($mapping->approval_level->approvalleveltodocuments as $doc)
                                                    <span class="badge bg-primary-subtle text-primary">
                                                        {{ ucwords(str_replace('_', ' ', $doc->document_type)) }}
                                                        @if($doc->document_sub_type)
                                                            <small class="text-muted">({{ ucwords(str_replace('_', ' ', $doc->document_sub_type)) }})</small>
                                                        @endif
                                                    </span>
                                                @endforeach
                                            </div>
                                        @else
                                            <span class="text-muted">-</span>
                                        @endif
                                    </td>
                                    <td class="name">{{ $mapping->employee->getFullName() ?? 'N/A' }}</td>
                                    <td>{{ $mapping->employee->employee_no ?? '-' }}</td>
                                    <td>
                                        @if($mapping->is_active)
                                            <span class="badge bg-success">Active</span>
                                        @else
                                            <span class="badge bg-danger">Inactive</span>
                                        @endif
                                    </td>
                                    <td>
                                        <x-forms.button-model name="EDIT" :classbtn="'fa-solid fa-pencil'" wire:click="openModal('edit', '{{ $mapping->id }}')" />
                                        <button class="btn btn-sm btn-danger" wire:click="delete('{{ $mapping->id }}')"
                                            onclick="return confirm('Delete this employee mapping?')">
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
                                    {{ $employeeMappings->links() }}
                                </div>
                            </div>
                        </div>
                    </div>
                </div>
            </div>
        </div>
    </div>

    <x-pages.model :title="$modalMode === 'edit' ? 'Edit Employee Mapping' : 'Add Employee Mapping'"
                   :formaction="$modalMode === 'edit' ? 'update' : 'save'"
                   :modalMode="$modalMode"
                   :showModal="$showModal">
        <x-forms.input type="select" name="approval_level_id" label="Approval Level" 
                       :options="$approvallevels->pluck('name', 'id')" required />
        {{-- Searchable employee picker --}}
        <div class="mb-3" wire:key="employee-picker">
            <label class="form-label">Employee <span class="text-danger">*</span></label>

            @if($this->selectedEmployee)
                {{-- A chosen employee — show it with a button to change --}}
                <div class="d-flex align-items-center justify-content-between border rounded p-2">
                    <span>
                        {{ $this->selectedEmployee->getFullName() }}
                        <small class="text-muted">({{ $this->selectedEmployee->employee_no }})</small>
                    </span>
                    <button type="button" class="btn btn-sm btn-outline-secondary" wire:click="clearEmployee">
                        <i class="fa-solid fa-pen me-1"></i> Change
                    </button>
                </div>
            @else
                <div class="position-relative">
                    <div class="input-group">
                        <span class="input-group-text bg-white">
                            <i class="fa-solid fa-magnifying-glass"></i>
                        </span>
                        <input type="text" class="form-control @error('employee_id') is-invalid @enderror"
                               placeholder="Search employee by name or number..."
                               wire:model.live.debounce.300ms="employeeSearch"
                               wire:focus="$set('showEmployeeDropdown', true)"
                               autocomplete="off" />
                    </div>

                    @if($showEmployeeDropdown || strlen($employeeSearch))
                        <div class="border rounded mt-1 bg-white shadow-sm position-absolute w-100"
                             style="z-index: 1056; max-height: 240px; overflow-y: auto;">
                            @forelse($this->filteredEmployees as $emp)
                                <button type="button"
                                        class="dropdown-item d-flex justify-content-between px-3 py-2 text-wrap"
                                        wire:key="emp-{{ $emp->id }}"
                                        wire:click="selectEmployee('{{ $emp->id }}')">
                                    <span>{{ $emp->getFullName() }}</span>
                                    <small class="text-muted">{{ $emp->employee_no }}</small>
                                </button>
                            @empty
                                <div class="px-3 py-2 text-muted">No employees found</div>
                            @endforelse
                        </div>
                    @endif
                </div>
            @endif

            @error('employee_id')
                <small class="text-danger d-block mt-1">{{ $message }}</small>
            @enderror
        </div>

        <div class="mb-3">
            <div class="form-check">
                <input type="checkbox" class="form-check-input" wire:model="is_active" id="is_active">
                <label class="form-check-label" for="is_active">Active</label>
            </div>
        </div>
    </x-pages.model>
</div>
