<div>
    <div>
        <h5 class="mb-5">Source of Funds</h5>
    </div>
    <div class="d-flex flex-column gap-6">
        {{-- Filters and Add Button --}}
        <div class="row g-3">
            <div class="col-md-4">
                <label class="form-label small text-muted">Search</label>
                <input class="form-control" type="search" wire:model.live="search"
                       placeholder="Search by name or slug..." />
            </div>
            <div class="col-md-4">
                <label class="form-label small text-muted">Filter by Financial Year</label>
                <select class="form-select" wire:model.live="filterFinancialYear">
                    <option value="">All Financial Years</option>
                    @foreach($financialYears as $fy)
                        <option value="{{ $fy->id }}">
                            {{ $fy->name }}
                            @if($fy->is_current) ⭐ @endif
                        </option>
                    @endforeach
                </select>
            </div>
            <div class="col-md-4 d-flex align-items-end">
                <x-forms.button-model name="ADD SOURCE OF FUNDS" />
            </div>
        </div>

        {{-- Summary Card --}}
        <div class="row">
            <div class="col-md-6">
                <div class="card border-0 shadow-sm bg-gradient bg-primary text-white">
                    <div class="card-body">
                        <div class="d-flex justify-content-between align-items-center">
                            <div>
                                <h6 class="text-white-50 mb-2">
                                    <i class="fa-solid fa-calculator"></i> Total Estimated Cost
                                </h6>
                                <h2 class="mb-0 fw-bold">
                                    {{ number_format($totalEstimatedCost, 2) }} TZS
                                </h2>
                                <small class="text-white-50">
                                    @if($filterFinancialYear)
                                        For {{ $financialYears->firstWhere('id', $filterFinancialYear)?->name ?? 'Selected FY' }}
                                    @else
                                        Across all financial years
                                    @endif
                                </small>
                            </div>
                            <div class="text-end">
                                <i class="fa-solid fa-money-bill-wave fa-3x opacity-25"></i>
                            </div>
                        </div>
                    </div>
                </div>
            </div>
            <div class="col-md-3">
                <div class="card border-0 shadow-sm">
                    <div class="card-body">
                        <h6 class="text-muted mb-2">
                            <i class="fa-solid fa-list"></i> Total Sources
                        </h6>
                        <h3 class="mb-0">{{ $sources->total() }}</h3>
                    </div>
                </div>
            </div>
            <div class="col-md-3">
                <div class="card border-0 shadow-sm">
                    <div class="card-body">
                        <h6 class="text-muted mb-2">
                            <i class="fa-solid fa-check-circle"></i> Active Sources
                        </h6>
                        <h3 class="mb-0 text-success">
                            {{ $sources->filter(fn($s) => $s->is_active)->count() }}
                        </h3>
                    </div>
                </div>
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
                                    <th>Financial Year</th>
                                    <th>Color</th>
                                    <th>Estimated Cost</th>
                                    <th>Status</th>
                                    <th>Description</th>
                                    <th>Actions</th>
                                </tr>
                            </thead>
                            <tbody class="list">
                                @php
                                    $number = 1;
                                @endphp
                                @forelse($sources as $source)
                                <tr>
                                    <td>{{ $number++ }}</td>
                                    <td class="name">
                                        <strong>{{ $source->name }}</strong>
                                        <br>
                                        <small class="text-muted"><code>{{ $source->slug }}</code></small>
                                    </td>
                                    <td>
                                        @if($source->financialYear)
                                            <span class="badge bg-primary">{{ $source->financialYear->name }}</span>
                                        @else
                                            <span class="badge bg-secondary">No FY</span>
                                        @endif
                                    </td>
                                    <td>
                                        @if($source->colorcode)
                                            <div class="d-flex align-items-center gap-2">
                                                <div style="width: 30px; height: 30px; background-color: {{ $source->colorcode }}; border-radius: 4px; border: 1px solid #ddd;"></div>
                                                <code>{{ $source->colorcode }}</code>
                                            </div>
                                        @else
                                            <span class="text-muted">-</span>
                                        @endif
                                    </td>
                                    <td>
                                        @if($source->estimated_cost)
                                            <strong>{{ $source->estimated_cost }}</strong>
                                        @else
                                            <span class="text-muted">-</span>
                                        @endif
                                    </td>
                                    <td>
                                        @if($source->is_active)
                                            <span class="badge bg-success">Active</span>
                                        @else
                                            <span class="badge bg-danger">Inactive</span>
                                        @endif
                                    </td>
                                    <td>{{ Str::limit($source->description, 30) }}</td>
                                    <td>
                                        <x-forms.button-model name="EDIT" :classbtn="'fa-solid fa-pencil'" wire:click="openModal('edit', '{{ $source->id }}')" />
                                        <button class="btn btn-sm btn-danger" wire:click="delete('{{ $source->id }}')"
                                            onclick="return confirm('Delete this source of funds?')">
                                            <i class="fa-solid fa-trash"></i>
                                        </button>
                                    </td>
                                </tr>
                                @empty
                                <tr>
                                    <td colspan="8" class="text-center py-4">
                                        <div class="text-muted">
                                            <i class="fa-solid fa-inbox fa-3x mb-3"></i>
                                            <p>No sources of funds found</p>
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
                                    {{ $sources->links() }}
                                </div>
                            </div>
                        </div>
                    </div>
                </div>
            </div>
        </div>
    </div>

    <x-pages.model :title="$modalMode === 'edit' ? 'Edit Source of Funds' : 'Add Source of Funds'"
                   :formaction="$modalMode === 'edit' ? 'update' : 'save'"
                   :modalMode="$modalMode"
                   :showModal="$showModal">

        {{-- Header with legend --}}
        <div class="alert alert-info mb-4 py-2">
            <small>
                <i class="fa-solid fa-info-circle"></i>
                <strong class="text-danger">*</strong> indicates required fields
            </small>
        </div>

        {{-- Required Fields Section --}}
        <div class="border-bottom pb-3 mb-3">
            <h6 class="text-primary mb-3">
                <i class="fa-solid fa-asterisk fa-xs text-danger"></i> Required Information
            </h6>

            <div class="row">
                <div class="col-md-8">
                    <div class="mb-3">
                        <label class="form-label">
                            Name <span class="text-danger">*</span>
                        </label>
                        <input type="text" class="form-control @error('name') is-invalid @enderror"
                               wire:model.blur="name"
                               placeholder="Enter Source of Funds Name"
                               required>
                        @error('name') <div class="invalid-feedback">{{ $message }}</div> @enderror
                    </div>
                </div>
                <div class="col-md-4">
                    <div class="mb-3">
                        <label class="form-label">
                            Slug <span class="text-danger">*</span>
                        </label>
                        <input type="text" class="form-control @error('slug') is-invalid @enderror"
                               wire:model="slug"
                               placeholder="auto-generated"
                               readonly
                               required>
                        @error('slug') <div class="invalid-feedback">{{ $message }}</div> @enderror
                    </div>
                </div>
            </div>

            <div class="mb-3">
                <label class="form-label">
                    Financial Year <span class="text-danger">*</span>
                </label>
                @if($financialYears->isEmpty())
                    <div class="alert alert-warning" role="alert">
                        <i class="fa-solid fa-exclamation-triangle"></i>
                        <strong>No financial years found!</strong>
                        <p class="mb-0">Please create a financial year first before adding a source of funds.</p>
                        <a href="/setup/setup/finance" class="btn btn-sm btn-primary mt-2">
                            <i class="fa-solid fa-plus"></i> Create Financial Year
                        </a>
                    </div>
                @else
                    <select class="form-select @error('financial_year_id') is-invalid @enderror"
                            wire:model.live="financial_year_id"
                            required>
                        <option value="">-- Select Financial Year --</option>
                        @foreach($financialYears as $fy)
                            <option value="{{ $fy->id }}" @selected($financial_year_id == $fy->id)>
                                {{ $fy->name }}
                                @if($fy->is_current)
                                    ⭐ (Current)
                                @endif
                                - {{ $fy->start_date->format('Y') }} to {{ $fy->end_date->format('Y') }}
                            </option>
                        @endforeach
                    </select>
                    @error('financial_year_id')
                        <div class="invalid-feedback d-block">{{ $message }}</div>
                    @enderror
                    @if($financial_year_id)
                        <small class="text-muted">
                            <i class="fa-solid fa-check-circle text-success"></i>
                            Selected: {{ $financialYears->firstWhere('id', $financial_year_id)?->name }}
                        </small>
                    @endif
                @endif
            </div>
        </div>

        {{-- Optional Fields Section --}}
        <div class="pb-3 mb-3">
            <h6 class="text-secondary mb-3">
                <i class="fa-solid fa-circle-info fa-xs"></i> Optional Information
            </h6>

            <div class="row">
                <div class="col-md-6">
                    <div class="mb-3">
                        <label class="form-label">
                            Color Code <small class="text-muted">(optional)</small>
                        </label>
                        <div class="input-group">
                            <input type="color"
                                   class="form-control form-control-color @error('colorcode') is-invalid @enderror"
                                   wire:model="colorcode"
                                   title="Choose color">
                            <input type="text"
                                   class="form-control @error('colorcode') is-invalid @enderror"
                                   wire:model="colorcode"
                                   placeholder="#000000"
                                   maxlength="7">
                        </div>
                        @error('colorcode') <div class="invalid-feedback">{{ $message }}</div> @enderror
                        <small class="text-muted">Used for visual identification</small>
                    </div>
                </div>
                <div class="col-md-6">
                    <div class="mb-3">
                        <label class="form-label">
                            Estimated Cost <small class="text-muted">(optional)</small>
                        </label>
                        <input type="text"
                               class="form-control @error('estimated_cost') is-invalid @enderror"
                               wire:model="estimated_cost"
                               placeholder="e.g., 1,000,000 TZS">
                        @error('estimated_cost') <div class="invalid-feedback">{{ $message }}</div> @enderror
                    </div>
                </div>
            </div>

            <div class="mb-3">
                <label class="form-label">
                    Description <small class="text-muted">(optional)</small>
                </label>
                <textarea class="form-control @error('description') is-invalid @enderror"
                          wire:model="description"
                          placeholder="Enter detailed description"
                          rows="3"></textarea>
                @error('description') <div class="invalid-feedback">{{ $message }}</div> @enderror
            </div>
        </div>

        {{-- Status Toggle --}}
        <div class="mb-3">
            <div class="form-check form-switch">
                <input type="checkbox"
                       class="form-check-input"
                       wire:model="is_active"
                       id="is_active"
                       role="switch">
                <label class="form-check-label" for="is_active">
                    <strong>Active Status</strong>
                    <br>
                    <small class="text-muted">
                        {{ $is_active ? 'This source of funds is currently active' : 'This source of funds is inactive' }}
                    </small>
                </label>
            </div>
        </div>
    </x-pages.model>
</div>
