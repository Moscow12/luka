<div>
    <div>
        <h5 class="mb-5">Financial Years</h5>
    </div>
    <div class="d-flex flex-column gap-6">
        <div class="d-flex flex-md-row flex-column gap-2 justify-content-between">
            <div class="d-flex flex-row gap-3 align-items-center">
                <div>
                    <form>
                        <input class="form-control" type="search" wire:model.live="search" placeholder="Search" />
                    </form>
                </div>
                <button wire:click="updateCurrentYear" class="btn btn-sm btn-info" title="Update current financial year based on today's date">
                    <i class="fa-solid fa-rotate"></i>
                    <span>Update Current</span>
                </button>
            </div>
            <div>
                <x-forms.button-model name="ADD FINANCIAL YEAR" />
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

                        @if($currentYear)
                            <div class="alert alert-info mb-0 rounded-0" role="alert">
                                <i class="fa-solid fa-calendar-check"></i>
                                <strong>Current Financial Year:</strong> {{ $currentYear->name }}
                                ({{ $currentYear->date_range }})
                            </div>
                        @endif

                        <table class="table text-nowrap mb-0 table-centered table-hover" data-check-container="">
                            <thead>
                                <tr>
                                    <th>#</th>
                                    <th>Name</th>
                                    <th>Start Date</th>
                                    <th>End Date</th>
                                    <th>Is Current</th>
                                    <th>Status</th>
                                    <th>Description</th>
                                    <th>Actions</th>
                                </tr>
                            </thead>
                            <tbody class="list">
                                @php
                                    $number = 1;
                                @endphp
                                @foreach($financialYears as $financialYear)
                                <tr class="{{ $financialYear->is_current ? 'table-active' : '' }}">
                                    <td>{{ $number++ }}</td>
                                    <td class="name">
                                        <strong>{{ $financialYear->name }}</strong>
                                        @if($financialYear->is_current)
                                            <span class="badge bg-success ms-2">CURRENT</span>
                                        @endif
                                    </td>
                                    <td>{{ $financialYear->start_date->format('M d, Y') }}</td>
                                    <td>{{ $financialYear->end_date->format('M d, Y') }}</td>
                                    <td>
                                        @if($financialYear->is_current)
                                            <span class="badge bg-success">
                                                <i class="fa-solid fa-check"></i> Yes
                                            </span>
                                        @else
                                            <button wire:click="setAsCurrent('{{ $financialYear->id }}')"
                                                    class="btn btn-sm btn-outline-primary"
                                                    title="Set as current financial year">
                                                <i class="fa-solid fa-calendar-check"></i> Set Current
                                            </button>
                                        @endif
                                    </td>
                                    <td>
                                        @if($financialYear->status === 'active')
                                            <span class="badge bg-success">Active</span>
                                        @else
                                            <span class="badge bg-danger">Inactive</span>
                                        @endif
                                    </td>
                                    <td>{{ Str::limit($financialYear->description, 30) }}</td>
                                    <td>
                                        <x-forms.button-model name="EDIT" :classbtn="'fa-solid fa-pencil'" wire:click="openModal('edit', '{{ $financialYear->id }}')" />
                                        @if(!$financialYear->is_current)
                                            <button class="btn btn-sm btn-danger" wire:click="delete('{{ $financialYear->id }}')"
                                                onclick="return confirm('Delete this financial year?')">
                                                <i class="fa-solid fa-trash"></i>
                                            </button>
                                        @else
                                            <button class="btn btn-sm btn-danger" disabled title="Cannot delete current financial year">
                                                <i class="fa-solid fa-trash"></i>
                                            </button>
                                        @endif
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
                                    {{ $financialYears->links() }}
                                </div>
                            </div>
                        </div>
                    </div>
                </div>
            </div>
        </div>
    </div>

    <x-pages.model :title="$modalMode === 'edit' ? 'Edit Financial Year' : 'Add Financial Year'"
                   :formaction="$modalMode === 'edit' ? 'update' : 'save'"
                   :modalMode="$modalMode"
                   :showModal="$showModal">
        <x-forms.input type="text" name="name" label="Name" placeholder="e.g., FY 2024-2025" required />
        <x-forms.input type="date" name="start_date" label="Start Date" required />
        <x-forms.input type="date" name="end_date" label="End Date" required />
        <x-forms.input type="select" name="status" label="Status" :options="['active' => 'Active', 'inactive' => 'Inactive']" required />
        <x-forms.input type="textarea" name="description" label="Description" placeholder="Enter Description" rows="2" />

        <div class="mb-3">
            <div class="form-check">
                <input type="checkbox" class="form-check-input" wire:model="is_current" id="is_current">
                <label class="form-check-label" for="is_current">Set as Current Financial Year</label>
            </div>
            <small class="text-muted">Note: Setting this will automatically unset any other current financial year.</small>
        </div>
    </x-pages.model>
</div>
