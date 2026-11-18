<div>
    <!-- Breadcrumb -->
    <nav aria-label="breadcrumb">
        <ol class="breadcrumb">
            <li class="breadcrumb-item"><a href="{{ route('dashboard') }}">Dashboard</a></li>
            <li class="breadcrumb-item"><a href="{{ route('chop.budget.requests') }}">CHOP</a></li>
            <li class="breadcrumb-item active" aria-current="page">Requested Items</li>
        </ol>
    </nav>

    <!-- Page Header -->
    <div class="d-flex flex-wrap justify-content-between align-items-center gap-2 mb-4">
        <div>
            <h2 class="mb-1">Requested Items</h2>
            <p class="text-muted mb-0">All items requested in department budget requests</p>
        </div>
        <button wire:click="export" wire:loading.attr="disabled" wire:target="export" class="btn btn-success">
            <span wire:loading.remove wire:target="export"><i class="fa-solid fa-file-excel me-2"></i>Export to Excel</span>
            <span wire:loading wire:target="export"><span class="spinner-border spinner-border-sm me-2"></span>Exporting...</span>
        </button>
    </div>

    <!-- Flash Messages -->
    @if (session()->has('success'))
        <div class="alert alert-success alert-dismissible fade show" role="alert">
            <i class="fa-solid fa-circle-check me-2"></i>{{ session('success') }}
            <button type="button" class="btn-close" data-bs-dismiss="alert"></button>
        </div>
    @endif

    <!-- Filters -->
    <div class="card shadow-sm mb-4">
        <div class="card-body">
            <div class="row g-3">
                <div class="col-md-4">
                    <label class="form-label small text-muted">Item name</label>
                    <input type="text" wire:model.live.debounce.400ms="search" class="form-control" placeholder="Search by item name...">
                </div>
                <div class="col-md-3">
                    <label class="form-label small text-muted">Department</label>
                    <select wire:model.live="filterDepartment" class="form-select">
                        <option value="">All departments</option>
                        @foreach ($departments as $department)
                            <option value="{{ $department->id }}">{{ $department->name }}</option>
                        @endforeach
                    </select>
                </div>
                <div class="col-md-2">
                    <label class="form-label small text-muted">Financial year</label>
                    <select wire:model.live="filterFinancialYear" class="form-select">
                        <option value="">All years</option>
                        @foreach ($financialYears as $fy)
                            <option value="{{ $fy->id }}">{{ $fy->name }}</option>
                        @endforeach
                    </select>
                </div>
                <div class="col-md-2">
                    <label class="form-label small text-muted">Request status</label>
                    <select wire:model.live="filterStatus" class="form-select">
                        <option value="">All statuses</option>
                        @foreach ($statuses as $value => $label)
                            <option value="{{ $value }}">{{ $label }}</option>
                        @endforeach
                    </select>
                </div>
                <div class="col-md-1 d-flex align-items-end">
                    <button wire:click="clearFilters" class="btn btn-outline-secondary w-100" title="Clear filters">
                        <i class="fa-solid fa-rotate-left"></i>
                    </button>
                </div>
            </div>
        </div>
    </div>

    <!-- Department Budget -->
    <div class="card shadow-sm mb-4">
        <div class="card-header bg-white d-flex flex-wrap justify-content-between align-items-center gap-2">
            <h5 class="mb-0"><i class="fa-solid fa-building me-2"></i>Department Budget</h5>
            <div class="small">
                <span class="me-3">Requested: <strong>{{ number_format($grandRequested, 2) }} TZS</strong></span>
                <span>Approved: <strong class="text-success">{{ number_format($grandApproved, 2) }} TZS</strong></span>
            </div>
        </div>
        <div class="card-body p-0">
            <div class="table-responsive">
                <table class="table table-sm mb-0">
                    <thead class="table-light">
                        <tr>
                            <th>Department</th>
                            <th class="text-end">Items</th>
                            <th class="text-end">Requested Budget</th>
                            <th class="text-end">Approved Budget</th>
                            <th class="text-end">Share of Requested</th>
                        </tr>
                    </thead>
                    <tbody>
                        @forelse ($departmentBudgets as $budget)
                            @php $share = $grandRequested > 0 ? ($budget->requested_total / $grandRequested) * 100 : 0; @endphp
                            <tr wire:key="budget-{{ $budget->id }}">
                                <td>
                                    <a href="#" wire:click.prevent="$set('filterDepartment', {{ Js::from((string) $budget->id) }})" class="text-decoration-none">
                                        {{ $budget->name }}
                                    </a>
                                </td>
                                <td class="text-end">{{ number_format($budget->items_count) }}</td>
                                <td class="text-end">{{ number_format($budget->requested_total, 2) }}</td>
                                <td class="text-end text-success">{{ number_format($budget->approved_total, 2) }}</td>
                                <td class="text-end" style="min-width: 140px;">
                                    <div class="d-flex align-items-center justify-content-end gap-2">
                                        <div class="progress flex-grow-1" style="height: 6px; max-width: 80px;">
                                            <div class="progress-bar" style="width: {{ round($share, 1) }}%"></div>
                                        </div>
                                        <small>{{ number_format($share, 1) }}%</small>
                                    </div>
                                </td>
                            </tr>
                        @empty
                            <tr>
                                <td colspan="5" class="text-center text-muted py-3">No department budgets for the selected filters.</td>
                            </tr>
                        @endforelse
                    </tbody>
                </table>
            </div>
        </div>
    </div>

    <!-- Items Table -->
    <div class="card shadow-sm">
        <div class="card-header bg-white d-flex justify-content-between align-items-center">
            <h5 class="mb-0">Items</h5>
            <small class="text-muted">{{ number_format($items->total()) }} item(s)</small>
        </div>
        <div class="card-body p-0">
            <div class="table-responsive">
                <table class="table table-hover align-middle mb-0">
                    <thead class="table-light">
                        <tr>
                            <th>Item</th>
                            <th>Department</th>
                            <th>Request</th>
                            <th class="text-end">Quantity</th>
                            <th class="text-end" style="min-width: 200px;">Unit Price (TZS)</th>
                            <th class="text-end">Total (TZS)</th>
                            <th class="text-end">Approved (TZS)</th>
                        </tr>
                    </thead>
                    <tbody>
                        @php
                            $statusColors = [
                                'draft' => 'secondary',
                                'submitted' => 'primary',
                                'under_review' => 'info',
                                'approved' => 'success',
                                'rejected' => 'danger',
                                'revision_required' => 'warning',
                            ];
                        @endphp
                        @forelse ($items as $item)
                            <tr wire:key="item-{{ $item->id }}">
                                <td>
                                    <div class="fw-semibold">{{ $item->item_name }}</div>
                                    @if ($item->item_unit)
                                        <small class="text-muted">{{ $item->item_unit }}</small>
                                    @endif
                                </td>
                                <td>{{ $item->department_name }}</td>
                                <td>
                                    <div>{{ $item->request_number }}</div>
                                    <span class="badge bg-{{ $statusColors[$item->request_status] ?? 'secondary' }}">{{ $statuses[$item->request_status] ?? $item->request_status }}</span>
                                </td>
                                <td class="text-end">{{ rtrim(rtrim(number_format($item->requested_quantity, 2), '0'), '.') }}</td>
                                <td class="text-end">
                                    @if ($editingItemId === $item->id)
                                        <form wire:submit="savePrice" class="d-flex justify-content-end gap-1">
                                            <input type="number" step="0.01" min="0" wire:model="editPrice"
                                                   class="form-control form-control-sm text-end @error('editPrice') is-invalid @enderror"
                                                   style="max-width: 130px;" autofocus
                                                   x-on:keydown.escape="$wire.cancelEdit()">
                                            <button type="submit" class="btn btn-sm btn-success" title="Save">
                                                <i class="fa-solid fa-check"></i>
                                            </button>
                                            <button type="button" wire:click="cancelEdit" class="btn btn-sm btn-outline-secondary" title="Cancel">
                                                <i class="fa-solid fa-xmark"></i>
                                            </button>
                                        </form>
                                        @error('editPrice')
                                            <div class="text-danger small">{{ $message }}</div>
                                        @enderror
                                    @else
                                        <span>{{ number_format($item->requested_price ?? 0, 2) }}</span>
                                        <button wire:click="startEdit({{ Js::from((string) $item->id) }})" class="btn btn-sm btn-link p-0 ms-2" title="Edit price">
                                            <i class="fa-solid fa-pen"></i>
                                        </button>
                                    @endif
                                </td>
                                <td class="text-end fw-semibold">{{ number_format($item->requested_total, 2) }}</td>
                                <td class="text-end">
                                    @if ($item->approved_price !== null)
                                        <span class="text-success">{{ number_format($item->approved_total, 2) }}</span>
                                        <div><small class="text-muted">{{ rtrim(rtrim(number_format($item->approved_quantity ?? 0, 2), '0'), '.') }} × {{ number_format($item->approved_price, 2) }}</small></div>
                                    @else
                                        <span class="text-muted">-</span>
                                    @endif
                                </td>
                            </tr>
                        @empty
                            <tr>
                                <td colspan="7" class="text-center py-4 text-muted">
                                    <i class="fa-solid fa-inbox fa-3x mb-3 d-block"></i>
                                    No requested items match your filters.
                                </td>
                            </tr>
                        @endforelse
                    </tbody>
                </table>
            </div>
        </div>
        @if ($items->hasPages())
            <div class="card-footer bg-white">
                {{ $items->links() }}
            </div>
        @endif
    </div>
</div>
