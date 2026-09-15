<div>
    <div>
        <h5 class="mb-5">Financial Years Management</h5>
    </div>
    <div class="d-flex flex-column gap-6">
        <div class="d-flex flex-md-row flex-column gap-2 justify-content-between">
            <div class="d-flex flex-row gap-3 align-items-center">
                <div>
                    <form>
                        <input class="form-control" type="search" wire:model.live="search" placeholder="Search financial years" />
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
                                ({{ $currentYear->start_date->format('M d, Y') }} - {{ $currentYear->end_date->format('M d, Y') }})
                            </div>
                        @endif

                        <table class="table text-nowrap mb-0 table-centered table-hover" data-check-container="">
                            <thead>
                                <tr>
                                    <th>#</th>
                                    <th>Name</th>
                                    <th>Start Date</th>
                                    <th>End Date</th>
                                    <th>Duration (Days)</th>
                                    <th>Is Current</th>
                                    <th>Status</th>
                                    <th>Actions</th>
                                </tr>
                            </thead>
                            <tbody class="list">
                                @php
                                    $number = 1;
                                @endphp
                                @forelse($financialYears as $financialYear)
                                <tr class="{{ $financialYear->is_current ? 'table-active' : '' }}">
                                    <td>{{ $number++ }}</td>
                                    <td class="name">
                                        <strong>{{ $financialYear->name }}</strong>
                                        @if($financialYear->is_current)
                                            <span class="badge bg-success ms-2">CURRENT</span>
                                        @endif
                                        @if($financialYear->description)
                                            <br><small class="text-muted">{{ Str::limit($financialYear->description, 50) }}</small>
                                        @endif
                                    </td>
                                    <td>
                                        <strong>{{ $financialYear->start_date->format('M d, Y') }}</strong>
                                        <br><small class="text-muted">{{ $financialYear->start_date->diffForHumans() }}</small>
                                    </td>
                                    <td>
                                        <strong>{{ $financialYear->end_date->format('M d, Y') }}</strong>
                                        <br><small class="text-muted">{{ $financialYear->end_date->diffForHumans() }}</small>
                                    </td>
                                    <td>
                                        <span class="badge bg-info">
                                            {{ $financialYear->start_date->diffInDays($financialYear->end_date) }} days
                                        </span>
                                    </td>
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
                                @empty
                                <tr>
                                    <td colspan="8" class="text-center py-4">
                                        <div class="text-muted">
                                            <i class="fa-solid fa-inbox fa-3x mb-3"></i>
                                            <p>No financial years found</p>
                                            <button wire:click="openModal('create')" class="btn btn-primary">
                                                <i class="fa-solid fa-plus"></i> Add First Financial Year
                                            </button>
                                        </div>
                                    </td>
                                </tr>
                                @endforelse
                            </tbody>
                        </table>
                    </div>

                    @if($financialYears->count() > 0)
                        <div class="btn-toolbar card-footer border-top border-dashed d-flex flex-md-row flex-column justify-content-md-between align-items-md-center">
                            <div class="d-flex gap-4">
                                <div>
                                    <div class="pagination-buttons d-flex">
                                        {{ $financialYears->links() }}
                                    </div>
                                </div>
                            </div>
                        </div>
                    @endif
                </div>
            </div>
        </div>
    </div>

    <x-pages.model :title="$modalMode === 'edit' ? 'Edit Financial Year' : 'Add Financial Year'"
                   :formaction="$modalMode === 'edit' ? 'update' : 'save'"
                   :modalMode="$modalMode"
                   :showModal="$showModal"
                   size="lg">

        {{-- Info Alert --}}
        <div class="alert alert-info mb-4 py-2">
            <small>
                <i class="fa-solid fa-info-circle"></i>
                <strong class="text-danger">*</strong> indicates required fields
            </small>
        </div>

        {{-- Required Fields --}}
        <div class="border-bottom pb-3 mb-3">
            <h6 class="text-primary mb-3">
                <i class="fa-solid fa-asterisk fa-xs text-danger"></i> Required Information
            </h6>

            <div class="mb-3">
                <label class="form-label">
                    Financial Year Name <span class="text-danger">*</span>
                </label>
                <input type="text"
                       class="form-control @error('name') is-invalid @enderror"
                       wire:model="name"
                       placeholder="e.g., FY 2024-2025"
                       required>
                @error('name') <div class="invalid-feedback">{{ $message }}</div> @enderror
                <small class="text-muted">Use format: FY YYYY-YYYY</small>
            </div>

            <div class="row">
                <div class="col-md-6">
                    <div class="mb-3">
                        <label class="form-label">
                            Start Date <span class="text-danger">*</span>
                        </label>
                        <input type="date"
                               class="form-control @error('start_date') is-invalid @enderror"
                               wire:model="start_date"
                               required>
                        @error('start_date') <div class="invalid-feedback">{{ $message }}</div> @enderror
                    </div>
                </div>
                <div class="col-md-6">
                    <div class="mb-3">
                        <label class="form-label">
                            End Date <span class="text-danger">*</span>
                        </label>
                        <input type="date"
                               class="form-control @error('end_date') is-invalid @enderror"
                               wire:model="end_date"
                               required>
                        @error('end_date') <div class="invalid-feedback">{{ $message }}</div> @enderror
                        <small class="text-muted">Must be after start date</small>
                    </div>
                </div>
            </div>

            <div class="mb-3">
                <label class="form-label">
                    Status <span class="text-danger">*</span>
                </label>
                <select class="form-select @error('status') is-invalid @enderror"
                        wire:model="status"
                        required>
                    <option value="active">Active</option>
                    <option value="inactive">Inactive</option>
                </select>
                @error('status') <div class="invalid-feedback">{{ $message }}</div> @enderror
            </div>
        </div>

        {{-- Optional Fields --}}
        <div class="pb-3 mb-3">
            <h6 class="text-secondary mb-3">
                <i class="fa-solid fa-circle-info fa-xs"></i> Optional Information
            </h6>

            <div class="mb-3">
                <label class="form-label">
                    Description <small class="text-muted">(optional)</small>
                </label>
                <textarea class="form-control @error('description') is-invalid @enderror"
                          wire:model="description"
                          placeholder="Enter description"
                          rows="3"></textarea>
                @error('description') <div class="invalid-feedback">{{ $message }}</div> @enderror
            </div>
        </div>

        {{-- Current Year Toggle --}}
        <div class="mb-3">
            <div class="form-check form-switch">
                <input type="checkbox"
                       class="form-check-input"
                       wire:model="is_current"
                       id="is_current"
                       role="switch">
                <label class="form-check-label" for="is_current">
                    <strong>Set as Current Financial Year</strong>
                    <br>
                    <small class="text-warning">
                        <i class="fa-solid fa-exclamation-triangle"></i>
                        Note: Setting this will automatically unset any other current financial year.
                    </small>
                </label>
            </div>
        </div>
    </x-pages.model>
</div>
