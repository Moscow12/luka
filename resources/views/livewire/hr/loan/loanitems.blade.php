<div>
    <div>
        <h5 class="mb-5">Loan Items</h5>
    </div>

    <div class="d-flex flex-column gap-6">
        {{-- Search and Add Button Row --}}
        <div class="d-flex flex-md-row flex-column gap-3 justify-content-between">
            <div class="d-flex flex-row gap-3 align-items-center">
                <div>
                    <input
                        class="form-control"
                        type="search"
                        wire:model.live.debounce.300ms="search"
                        placeholder="Search loan items..."
                    />
                </div>
            </div>
            <div>
                <x-forms.button-model name="ADD LOAN ITEM" />
            </div>
        </div>

        {{-- Data Table --}}
        <div>
            <div class="card card-lg overflow-hidden">
                <div class="card-body p-0">
                    {{-- Success Alert --}}
                    @if(session()->has('success'))
                        <div class="alert alert-success alert-dismissible fade show m-3" role="alert">
                            {{ session('success') }}
                            <button type="button" class="btn-close" data-bs-dismiss="alert" aria-label="Close"></button>
                        </div>
                    @endif

                    <div class="table-responsive">
                        <table class="table text-nowrap mb-0 table-centered table-hover">
                            <thead class="table-light">
                                <tr>
                                    <th class="ps-3">#</th>
                                    <th>Name</th>
                                    <th>Workstation</th>
                                    <th>Min Amount</th>
                                    <th>Max Amount</th>
                                    <th>Interest Rate</th>
                                    <th>Repayment Period</th>
                                    <th class="text-end pe-3">Actions</th>
                                </tr>
                            </thead>
                            <tbody>
                                @forelse($loanItems as $index => $item)
                                    <tr>
                                        <td class="ps-3">{{ $loanItems->firstItem() + $index }}</td>
                                        <td>
                                            <span class="fw-medium">{{ $item->name }}</span>
                                        </td>
                                        <td>
                                            <span class="badge bg-secondary-subtle text-secondary">
                                                {{ $item->workstation?->workstation_name ?? 'N/A' }}
                                            </span>
                                        </td>
                                        <td>
                                            <span class="text-muted">{{ number_format($item->min_amount, 2) }}</span>
                                        </td>
                                        <td>
                                            <span class="text-muted">{{ number_format($item->max_amount, 2) }}</span>
                                        </td>
                                        <td>
                                            <span class="badge bg-info-subtle text-info">
                                                {{ number_format($item->interest_rate, 2) }}%
                                            </span>
                                        </td>
                                        <td>
                                            <span class="badge bg-primary-subtle text-primary">
                                                {{ $item->repayment_period_months }} {{ Str::plural('month', $item->repayment_period_months) }}
                                            </span>
                                        </td>
                                        <td class="text-end pe-3">
                                            <div class="d-flex gap-2 justify-content-end">
                                                <button
                                                    class="btn btn-sm btn-outline-primary"
                                                    wire:click="openModal('edit', '{{ $item->id }}')"
                                                    title="Edit"
                                                >
                                                    <i class="fa-solid fa-pencil"></i>
                                                </button>
                                                <button
                                                    class="btn btn-sm btn-outline-danger"
                                                    wire:click="delete('{{ $item->id }}')"
                                                    wire:confirm="Are you sure you want to delete this loan item?"
                                                    title="Delete"
                                                >
                                                    <i class="fa-solid fa-trash"></i>
                                                </button>
                                            </div>
                                        </td>
                                    </tr>
                                @empty
                                    <tr>
                                        <td colspan="8" class="text-center py-5">
                                            <div class="d-flex flex-column align-items-center gap-3">
                                                <i class="fa-solid fa-inbox fa-3x text-muted"></i>
                                                <div>
                                                    <p class="text-muted mb-1">No loan items found</p>
                                                    <button
                                                        class="btn btn-sm btn-primary"
                                                        wire:click="openModal('create')"
                                                    >
                                                        <i class="fa-solid fa-plus me-1"></i>
                                                        Add your first loan item
                                                    </button>
                                                </div>
                                            </div>
                                        </td>
                                    </tr>
                                @endforelse
                            </tbody>
                        </table>
                    </div>

                    {{-- Pagination --}}
                    @if($loanItems->hasPages())
                        <div class="card-footer border-top border-dashed d-flex flex-md-row flex-column justify-content-md-between align-items-md-center gap-3">
                            <div class="text-muted small">
                                Showing {{ $loanItems->firstItem() }} to {{ $loanItems->lastItem() }} of {{ $loanItems->total() }} entries
                            </div>
                            <div>
                                {{ $loanItems->links() }}
                            </div>
                        </div>
                    @endif
                </div>
            </div>
        </div>
    </div>

    {{-- Modal for Add/Edit --}}
    <x-pages.model
        :title="$modalMode === 'edit' ? 'Edit Loan Item' : 'Add Loan Item'"
        :formaction="$modalMode === 'edit' ? 'update' : 'save'"
        :modalMode="$modalMode"
        :showModal="$showModal"
    >
        <div class="row">
            <div class="col-12">
                <div class="form-floating mb-3">
                    <select
                        class="form-select @error('workstation_id') is-invalid @enderror"
                        id="workstation_id"
                        wire:model="workstation_id"
                        required
                    >
                        <option value="">Select Workstation</option>
                        @foreach($workstations as $workstation)
                            <option value="{{ $workstation->id }}">{{ $workstation->workstation_name }}</option>
                        @endforeach
                    </select>
                    <label for="workstation_id">Workstation</label>
                    @error('workstation_id')
                        <small class="text-danger">{{ $message }}</small>
                    @enderror
                </div>
            </div>

            <div class="col-12">
                <x-forms.input
                    type="text"
                    name="name"
                    label="Loan Item Name"
                    placeholder="e.g., Personal Loan, Emergency Loan"
                    required
                />
            </div>

            <div class="col-md-6">
                <x-forms.input
                    type="number"
                    name="min_amount"
                    label="Minimum Amount"
                    placeholder="0.00"
                    step="0.01"
                    min="0"
                    required
                />
            </div>

            <div class="col-md-6">
                <x-forms.input
                    type="number"
                    name="max_amount"
                    label="Maximum Amount"
                    placeholder="0.00"
                    step="0.01"
                    min="0"
                    required
                />
            </div>

            <div class="col-md-6">
                <x-forms.input
                    type="number"
                    name="interest_rate"
                    label="Interest Rate (%)"
                    placeholder="0.00"
                    step="0.01"
                    min="0"
                    max="100"
                    required
                />
            </div>

            <div class="col-md-6">
                <x-forms.input
                    type="number"
                    name="repayment_period_months"
                    label="Repayment Period (Months)"
                    placeholder="12"
                    min="1"
                    required
                />
            </div>
        </div>
    </x-pages.model>
</div>
