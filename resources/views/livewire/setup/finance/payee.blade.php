<div>
    <div>
        <h5 class="mb-3">PAYE Tax Calculator (Tanzania Mainland)</h5>
        <p class="text-muted small mb-5">Pay As You Earn (PAYE) is calculated based on progressive tax brackets set by the Tanzania Revenue Authority (TRA).</p>
    </div>

    {{-- PAYE Calculator Section --}}
    <div class="row mb-5">
        <div class="col-lg-5">
            <div class="card border-primary">
                <div class="card-header bg-primary text-white">
                    <h6 class="mb-0"><i class="fa-solid fa-calculator me-2"></i>Calculate PAYE</h6>
                </div>
                <div class="card-body">
                    <div class="mb-3">
                        <label for="salary_input" class="form-label fw-bold">Enter Monthly Gross Salary (TZS)</label>
                        <input type="number"
                               class="form-control form-control-lg"
                               id="salary_input"
                               wire:model.live.debounce.300ms="salary_input"
                               placeholder="e.g., 1,500,000"
                               min="0"
                               step="1000">
                    </div>
                    <button class="btn btn-primary w-100" wire:click="calculatePaye">
                        <i class="fa-solid fa-calculator me-2"></i>Calculate PAYE
                    </button>
                </div>
            </div>
        </div>

        <div class="col-lg-7">
            @if($calculation_result)
            <div class="card border-success">
                <div class="card-header bg-success text-white">
                    <h6 class="mb-0"><i class="fa-solid fa-receipt me-2"></i>Calculation Results</h6>
                </div>
                <div class="card-body">
                    <div class="row g-3 mb-4">
                        <div class="col-md-6">
                            <div class="p-3 bg-light rounded">
                                <small class="text-muted">Gross Salary</small>
                                <h4 class="mb-0 text-primary">TZS {{ number_format($calculation_result['taxable_income'], 2) }}</h4>
                            </div>
                        </div>
                        <div class="col-md-6">
                            <div class="p-3 bg-light rounded">
                                <small class="text-muted">Total PAYE Tax</small>
                                <h4 class="mb-0 text-danger">TZS {{ number_format($calculation_result['total_tax'], 2) }}</h4>
                            </div>
                        </div>
                        <div class="col-md-6">
                            <div class="p-3 bg-light rounded">
                                <small class="text-muted">Net Salary (After PAYE)</small>
                                <h4 class="mb-0 text-success">TZS {{ number_format($calculation_result['net_income'], 2) }}</h4>
                            </div>
                        </div>
                        <div class="col-md-6">
                            <div class="p-3 bg-light rounded">
                                <small class="text-muted">Effective Tax Rate</small>
                                <h4 class="mb-0 text-info">{{ number_format($calculation_result['effective_rate'], 2) }}%</h4>
                            </div>
                        </div>
                    </div>

                    @if(count($calculation_result['breakdown']) > 0)
                    <h6 class="border-bottom pb-2 mb-3"><i class="fa-solid fa-list me-2"></i>Tax Breakdown by Bracket</h6>
                    <div class="table-responsive">
                        <table class="table table-sm table-bordered mb-0">
                            <thead class="table-light">
                                <tr>
                                    <th>Bracket</th>
                                    <th class="text-end">Rate</th>
                                    <th class="text-end">Taxable Amount</th>
                                    <th class="text-end">Tax</th>
                                </tr>
                            </thead>
                            <tbody>
                                @foreach($calculation_result['breakdown'] as $item)
                                <tr>
                                    <td>{{ $item['bracket'] }}</td>
                                    <td class="text-end">{{ number_format($item['rate'], 0) }}%</td>
                                    <td class="text-end">TZS {{ number_format($item['taxable_in_bracket'], 2) }}</td>
                                    <td class="text-end fw-bold">TZS {{ number_format($item['tax_amount'], 2) }}</td>
                                </tr>
                                @endforeach
                            </tbody>
                            <tfoot class="table-secondary">
                                <tr>
                                    <th colspan="3" class="text-end">Total PAYE:</th>
                                    <th class="text-end text-danger">TZS {{ number_format($calculation_result['total_tax'], 2) }}</th>
                                </tr>
                            </tfoot>
                        </table>
                    </div>
                    @else
                    <div class="alert alert-info mb-0">
                        <i class="fa-solid fa-info-circle me-2"></i>
                        No PAYE brackets configured. Please add tax brackets below.
                    </div>
                    @endif
                </div>
            </div>
            @else
            <div class="card h-100">
                <div class="card-body d-flex align-items-center justify-content-center">
                    <div class="text-center text-muted">
                        <i class="fa-solid fa-calculator fa-3x mb-3"></i>
                        <p>Enter a salary amount to calculate PAYE tax</p>
                    </div>
                </div>
            </div>
            @endif
        </div>
    </div>

    {{-- How PAYE is Calculated Section --}}
    <div class="card mb-5 border-info">
        <div class="card-header bg-info text-white">
            <h6 class="mb-0"><i class="fa-solid fa-graduation-cap me-2"></i>How PAYE is Calculated (Tanzania Mainland)</h6>
        </div>
        <div class="card-body">
            <p>PAYE in Tanzania uses a <strong>progressive tax system</strong> where income is taxed at increasing rates as it moves through different brackets:</p>
            <ol class="mb-3">
                <li><strong>First TZS 270,000:</strong> Tax-free (0%)</li>
                <li><strong>TZS 270,001 - 520,000:</strong> Taxed at 8% on the amount above 270,000</li>
                <li><strong>TZS 520,001 - 760,000:</strong> Taxed at 20% on the amount above 520,000</li>
                <li><strong>TZS 760,001 - 1,000,000:</strong> Taxed at 25% on the amount above 760,000</li>
                <li><strong>Above TZS 1,000,000:</strong> Taxed at 30% on the amount above 1,000,000</li>
            </ol>
            <div class="alert alert-warning mb-0">
                <i class="fa-solid fa-lightbulb me-2"></i>
                <strong>Example:</strong> For a salary of TZS 1,500,000:
                <ul class="mb-0 mt-2">
                    <li>First 270,000 = TZS 0 (0%)</li>
                    <li>Next 250,000 (270,001-520,000) = TZS 20,000 (8%)</li>
                    <li>Next 240,000 (520,001-760,000) = TZS 48,000 (20%)</li>
                    <li>Next 240,000 (760,001-1,000,000) = TZS 60,000 (25%)</li>
                    <li>Remaining 500,000 (above 1,000,000) = TZS 150,000 (30%)</li>
                    <li><strong>Total PAYE = TZS 278,000</strong></li>
                </ul>
            </div>
        </div>
    </div>

    {{-- PAYE Brackets Management Section --}}
    <div class="d-flex flex-column gap-6">
        <div class="d-flex flex-md-row flex-column gap-2 justify-content-between">
            <div class="d-flex flex-row gap-3 align-items-center">
                <div>
                    <h5 class="mb-0">PAYE Tax Brackets</h5>
                </div>
                <div>
                    <form>
                        <input class="form-control" type="search" wire:model.live="search" placeholder="Search brackets..." />
                    </form>
                </div>
            </div>
            <div>
                <x-forms.button-model name="ADD BRACKET" />
            </div>
        </div>
        <div>
            <div class="card card-lg overflow-hidden" id="bracketsTable">
                <div class="card-body p-0">
                    <div class="table-responsive">
                        @if(session()->has('success'))
                            <div class="alert alert-success alert-dismissible fade show m-3" role="alert">
                                {{ session('success') }}
                                <button type="button" class="btn-close" data-bs-dismiss="alert" aria-label="Close"></button>
                            </div>
                        @endif

                        <table class="table text-nowrap mb-0 table-centered table-hover">
                            <thead>
                                <tr>
                                    <th>Order</th>
                                    <th>Min Amount (TZS)</th>
                                    <th>Max Amount (TZS)</th>
                                    <th>Tax Rate</th>
                                    <th>Description</th>
                                    <th>Status</th>
                                    <th>Actions</th>
                                </tr>
                            </thead>
                            <tbody>
                                @forelse($brackets as $bracket)
                                <tr class="{{ !$bracket->is_active ? 'table-secondary' : '' }}">
                                    <td>
                                        <span class="badge bg-secondary">{{ $bracket->order }}</span>
                                    </td>
                                    <td>{{ number_format($bracket->min_amount, 2) }}</td>
                                    <td>
                                        @if($bracket->max_amount)
                                            {{ number_format($bracket->max_amount, 2) }}
                                        @else
                                            <span class="text-muted">No Limit</span>
                                        @endif
                                    </td>
                                    <td>
                                        <span class="badge {{ $bracket->rate == 0 ? 'bg-success' : 'bg-primary' }} fs-6">
                                            {{ number_format($bracket->rate, 0) }}%
                                        </span>
                                    </td>
                                    <td>{{ $bracket->description ?? '-' }}</td>
                                    <td>
                                        <button class="btn btn-sm {{ $bracket->is_active ? 'btn-success' : 'btn-outline-secondary' }}"
                                                wire:click="toggleActive('{{ $bracket->id }}')"
                                                title="Click to toggle">
                                            @if($bracket->is_active)
                                                <i class="fa-solid fa-check"></i> Active
                                            @else
                                                <i class="fa-solid fa-times"></i> Inactive
                                            @endif
                                        </button>
                                    </td>
                                    <td>
                                        <x-forms.button-model name="EDIT" :classbtn="'fa-solid fa-pencil'" wire:click="openModal('edit', '{{ $bracket->id }}')" />
                                        <button class="btn btn-sm btn-danger"
                                                wire:click="delete('{{ $bracket->id }}')"
                                                wire:confirm="Are you sure you want to delete this tax bracket?">
                                            <i class="fa-solid fa-trash"></i>
                                        </button>
                                    </td>
                                </tr>
                                @empty
                                <tr>
                                    <td colspan="7" class="text-center py-4">
                                        <div class="text-muted">
                                            <i class="fa-solid fa-folder-open fa-2x mb-2"></i>
                                            <p class="mb-0">No PAYE brackets found. Click "ADD BRACKET" to create one.</p>
                                        </div>
                                    </td>
                                </tr>
                                @endforelse
                            </tbody>
                        </table>
                    </div>

                    @if($brackets->hasPages())
                    <div class="btn-toolbar card-footer border-top border-dashed d-flex flex-md-row flex-column justify-content-md-between align-items-md-center">
                        <div class="d-flex gap-4">
                            <div>
                                <div class="pagination-buttons d-flex">
                                    {{ $brackets->links() }}
                                </div>
                            </div>
                        </div>
                    </div>
                    @endif
                </div>
            </div>
        </div>
    </div>

    {{-- Modal for Add/Edit Bracket --}}
    <x-pages.model :title="$modalMode === 'edit' ? 'Edit PAYE Bracket' : 'Add PAYE Bracket'"
                   :formaction="$modalMode === 'edit' ? 'save' : 'save'"
                   :modalMode="$modalMode"
                   :showModal="$showModal">
        <div class="row">
            <div class="col-md-6">
                <x-forms.input type="number" name="min_amount" label="Minimum Amount (TZS)" placeholder="e.g., 270000" step="1" min="0" required />
            </div>
            <div class="col-md-6">
                <x-forms.input type="number" name="max_amount" label="Maximum Amount (TZS)" placeholder="Leave empty for no limit" step="1" min="0" />
            </div>
        </div>
        <div class="row">
            <div class="col-md-6">
                <x-forms.input type="number" name="rate" label="Tax Rate (%)" placeholder="e.g., 8" step="0.01" min="0" max="100" required />
            </div>
            <div class="col-md-6">
                <x-forms.input type="number" name="order" label="Order (Priority)" placeholder="e.g., 1" min="1" required />
            </div>
        </div>
        <x-forms.input type="text" name="description" label="Description" placeholder="e.g., First TZS 270,000 (Tax Free)" />

        <div class="mb-3">
            <div class="form-check">
                <input type="checkbox" class="form-check-input" wire:model="is_active" id="is_active">
                <label class="form-check-label" for="is_active">Active</label>
            </div>
        </div>
    </x-pages.model>
</div>
