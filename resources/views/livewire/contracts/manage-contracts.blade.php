<div>
    <div class="container-fluid">
        <!-- Page Header -->
        <div class="row mb-4">
            <div class="col-12">
                <div class="d-flex justify-content-between align-items-center">
                    <div>
                        <h4 class="mb-1">Institutional Contract Management</h4>
                        <p class="text-muted mb-0">Centralized management of all institutional contracts</p>
                    </div>
                    <div>
                        <a href="{{ route('contracts.create') }}" class="btn btn-primary">
                            <i class="bi bi-plus-circle me-2"></i>New Contract
                        </a>
                    </div>
                </div>
            </div>
        </div>

        <!-- Statistics Cards -->
        <div class="row mb-4">
            <div class="col-md-3">
                <div class="card border-0 shadow-sm">
                    <div class="card-body">
                        <div class="d-flex justify-content-between align-items-center">
                            <div>
                                <h6 class="text-muted mb-1">Total Contracts</h6>
                                <h3 class="mb-0">{{ $stats['total'] }}</h3>
                            </div>
                            <div class="bg-primary bg-opacity-10 p-3 rounded-circle">
                                <i class="bi bi-file-earmark-text text-primary fs-4"></i>
                            </div>
                        </div>
                    </div>
                </div>
            </div>
            <div class="col-md-3">
                <div class="card border-0 shadow-sm">
                    <div class="card-body">
                        <div class="d-flex justify-content-between align-items-center">
                            <div>
                                <h6 class="text-muted mb-1">Active Contracts</h6>
                                <h3 class="mb-0">{{ $stats['active'] }}</h3>
                            </div>
                            <div class="bg-success bg-opacity-10 p-3 rounded-circle">
                                <i class="bi bi-check-circle text-success fs-4"></i>
                            </div>
                        </div>
                    </div>
                </div>
            </div>
            <div class="col-md-3">
                <div class="card border-0 shadow-sm">
                    <div class="card-body">
                        <div class="d-flex justify-content-between align-items-center">
                            <div>
                                <h6 class="text-muted mb-1">Expiring Soon</h6>
                                <h3 class="mb-0">{{ $stats['expiring_soon'] }}</h3>
                            </div>
                            <div class="bg-warning bg-opacity-10 p-3 rounded-circle">
                                <i class="bi bi-exclamation-triangle text-warning fs-4"></i>
                            </div>
                        </div>
                    </div>
                </div>
            </div>
            <div class="col-md-3">
                <div class="card border-0 shadow-sm">
                    <div class="card-body">
                        <div class="d-flex justify-content-between align-items-center">
                            <div>
                                <h6 class="text-muted mb-1">Pending Approval</h6>
                                <h3 class="mb-0">{{ $stats['pending_approval'] }}</h3>
                            </div>
                            <div class="bg-info bg-opacity-10 p-3 rounded-circle">
                                <i class="bi bi-hourglass-split text-info fs-4"></i>
                            </div>
                        </div>
                    </div>
                </div>
            </div>
        </div>

        <!-- Filters Card -->
        <div class="card shadow-sm border-0 mb-4">
            <div class="card-header bg-primary text-white">
                <h5 class="mb-0">
                    <i class="bi bi-funnel me-2"></i>Filters
                </h5>
            </div>
            <div class="card-body">
                <div class="row g-3">
                    <div class="col-md-2">
                        <label class="form-label fw-semibold">Status</label>
                        <select wire:model.live="statusFilter" class="form-select">
                            <option value="">All Status</option>
                            <option value="draft">Draft</option>
                            <option value="active">Active</option>
                            <option value="expired">Expired</option>
                            <option value="terminated">Terminated</option>
                            <option value="pending_approval">Pending Approval</option>
                        </select>
                    </div>
                    <div class="col-md-2">
                        <label class="form-label fw-semibold">Type</label>
                        <select wire:model.live="typeFilter" class="form-select">
                            <option value="">All Types</option>
                            <option value="supplier">Supplier</option>
                            <option value="service_provider">Service Provider</option>
                            <option value="agency">Agency</option>
                            <option value="partner">Partner</option>
                        </select>
                    </div>
                    <div class="col-md-2">
                        <label class="form-label fw-semibold">Department</label>
                        <select wire:model.live="departmentFilter" class="form-select">
                            <option value="">All Departments</option>
                            @foreach($departments as $dept)
                                <option value="{{ $dept->id }}">{{ $dept->name }}</option>
                            @endforeach
                        </select>
                    </div>
                    <div class="col-md-2">
                        <label class="form-label fw-semibold">Expiry</label>
                        <select wire:model.live="expiryFilter" class="form-select">
                            <option value="">All Contracts</option>
                            <option value="30">Next 30 Days</option>
                            <option value="60">Next 60 Days</option>
                            <option value="90">Next 90 Days</option>
                        </select>
                    </div>
                    <div class="col-md-3">
                        <label class="form-label fw-semibold">Search</label>
                        <input type="text"
                               wire:model.live.debounce.300ms="search"
                               class="form-control"
                               placeholder="Search contracts...">
                    </div>
                    <div class="col-md-1">
                        <label class="form-label fw-semibold d-block">&nbsp;</label>
                        <button wire:click="clearFilters"
                                class="btn btn-outline-secondary w-100">
                            <i class="bi bi-x-circle"></i>
                        </button>
                    </div>
                </div>
            </div>
        </div>

        <!-- Contracts Table -->
        <div class="card shadow-sm border-0">
            <div class="card-body p-0">
                @if($contracts->count() > 0)
                    <div class="table-responsive">
                        <table class="table table-hover align-middle mb-0">
                            <thead class="bg-light">
                                <tr>
                                    <th class="px-4 py-3">Contract #</th>
                                    <th class="py-3">Title</th>
                                    <th class="py-3">Type</th>
                                    <th class="py-3">Value</th>
                                    <th class="py-3">Duration</th>
                                    <th class="py-3">Status</th>
                                    <th class="py-3 text-center">Actions</th>
                                </tr>
                            </thead>
                            <tbody>
                                @foreach($contracts as $contract)
                                    <tr wire:key="contract-{{ $contract->id }}">
                                        <td class="px-4 py-3">
                                            <span class="badge bg-secondary">{{ $contract->contract_number }}</span>
                                        </td>
                                        <td class="py-3">
                                            <div>
                                                <span class="fw-semibold">{{ $contract->title }}</span>
                                                @if($contract->is_expiring_soon)
                                                    <span class="badge bg-warning ms-2">
                                                        <i class="bi bi-clock"></i> {{ abs($contract->days_until_expiry) }} days
                                                    </span>
                                                @endif
                                            </div>
                                            <small class="text-muted">{{ Str::limit($contract->description, 50) }}</small>
                                        </td>
                                        <td class="py-3">
                                            <span class="badge bg-info bg-opacity-10 text-info">
                                                {{ ucfirst(str_replace('_', ' ', $contract->type)) }}
                                            </span>
                                        </td>
                                        <td class="py-3">
                                            <span class="fw-semibold">{{ number_format($contract->contract_value, 2) }}</span>
                                        </td>
                                        <td class="py-3">
                                            <div class="small">
                                                <div><strong>Start:</strong> {{ $contract->start_date->format('M d, Y') }}</div>
                                                <div><strong>End:</strong> {{ $contract->end_date->format('M d, Y') }}</div>
                                            </div>
                                        </td>
                                        <td class="py-3">
                                            @php
                                                $statusColors = [
                                                    'draft' => 'secondary',
                                                    'active' => 'success',
                                                    'expired' => 'danger',
                                                    'terminated' => 'dark',
                                                    'pending_approval' => 'warning',
                                                ];
                                                $color = $statusColors[$contract->status] ?? 'primary';
                                            @endphp
                                            <span class="badge bg-{{ $color }}">
                                                {{ ucfirst(str_replace('_', ' ', $contract->status)) }}
                                            </span>
                                        </td>
                                        <td class="py-3 text-center">
                                            <div class="btn-group" role="group">
                                                <a href="{{ route('contracts.view', $contract->id) }}"
                                                   class="btn btn-sm btn-outline-primary"
                                                   title="View Details">
                                                    <i class="bi bi-eye"></i>
                                                </a>
                                                <a href="{{ route('contracts.edit', $contract->id) }}"
                                                   class="btn btn-sm btn-outline-info"
                                                   title="Edit">
                                                    <i class="bi bi-pencil"></i>
                                                </a>
                                                @if($contract->status === 'draft')
                                                    <button wire:click="initiateApproval('{{ $contract->id }}')"
                                                            class="btn btn-sm btn-outline-success"
                                                            title="Initiate Approval">
                                                        <i class="bi bi-check2-square"></i>
                                                    </button>
                                                @endif
                                                <button wire:click="deleteContract('{{ $contract->id }}')"
                                                        wire:confirm="Are you sure you want to delete this contract?"
                                                        class="btn btn-sm btn-outline-danger"
                                                        title="Delete">
                                                    <i class="bi bi-trash"></i>
                                                </button>
                                            </div>
                                        </td>
                                    </tr>
                                @endforeach
                            </tbody>
                        </table>
                    </div>

                    <!-- Pagination -->
                    <div class="card-footer bg-white border-top">
                        <div class="d-flex justify-content-between align-items-center">
                            <div class="text-muted small">
                                Showing {{ $contracts->firstItem() }} to {{ $contracts->lastItem() }} of {{ $contracts->total() }} contracts
                            </div>
                            <div>
                                {{ $contracts->links() }}
                            </div>
                        </div>
                    </div>
                @else
                    <div class="text-center py-5">
                        <i class="bi bi-file-earmark-x fs-1 text-muted"></i>
                        <p class="text-muted mt-3 mb-0">
                            @if($search || $statusFilter || $typeFilter)
                                No contracts found matching your filters.
                            @else
                                No contracts created yet. Create your first contract to get started.
                            @endif
                        </p>
                        @if($search || $statusFilter || $typeFilter)
                            <button wire:click="clearFilters" class="btn btn-primary mt-3">
                                <i class="bi bi-x-circle me-2"></i>Clear Filters
                            </button>
                        @else
                            <a href="{{ route('contracts.create') }}" class="btn btn-primary mt-3">
                                <i class="bi bi-plus-circle me-2"></i>Create Contract
                            </a>
                        @endif
                    </div>
                @endif
            </div>
        </div>
    </div>
</div>
