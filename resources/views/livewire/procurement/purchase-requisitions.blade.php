<div>
    <!-- Breadcrumb -->
    <nav aria-label="breadcrumb">
        <ol class="breadcrumb">
            <li class="breadcrumb-item"><a href="{{ route('dashboard') }}">Dashboard</a></li>
            <li class="breadcrumb-item"><a href="{{ route('procurement.orders.approve') }}">Approve Store Orders</a></li>
            <li class="breadcrumb-item active" aria-current="page">Purchase Requisitions</li>
        </ol>
    </nav>

    <!-- Page Header -->
    <div class="d-flex justify-content-between align-items-center mb-4">
        <div>
            <h2 class="mb-1">Purchase Requisitions</h2>
            <p class="text-muted">Approve requisitions, collect supplier quotations, and generate local purchase orders</p>
        </div>
    </div>

    <!-- Flash Messages -->
    @if (session()->has('success'))
        <div class="alert alert-success alert-dismissible fade show" role="alert">
            <i class="fa-solid fa-circle-check me-2"></i>{{ session('success') }}
            <button type="button" class="btn-close" data-bs-dismiss="alert"></button>
        </div>
    @endif

    @if (session()->has('error'))
        <div class="alert alert-danger alert-dismissible fade show" role="alert">
            <i class="fa-solid fa-circle-exclamation me-2"></i>{{ session('error') }}
            <button type="button" class="btn-close" data-bs-dismiss="alert"></button>
        </div>
    @endif

    <!-- Tabs -->
    <ul class="nav nav-tabs mb-4">
        <li class="nav-item">
            <button type="button" class="nav-link {{ $activeTab === 'draft' ? 'active' : '' }}" wire:click="setTab('draft')">
                Draft
                @if($draftCount > 0)
                    <span class="badge bg-warning text-dark ms-1">{{ $draftCount }}</span>
                @endif
            </button>
        </li>
        <li class="nav-item">
            <button type="button" class="nav-link {{ $activeTab === 'approved' ? 'active' : '' }}" wire:click="setTab('approved')">
                Approved
                @if($approvedCount > 0)
                    <span class="badge bg-success ms-1">{{ $approvedCount }}</span>
                @endif
            </button>
        </li>
    </ul>

    <!-- Search -->
    <div class="card shadow-sm mb-4">
        <div class="card-body">
            <input type="text" wire:model.live.debounce.300ms="search" class="form-control"
                   placeholder="Search by requisition number...">
        </div>
    </div>

    <!-- Requisitions Table -->
    <div class="card shadow-sm">
        <div class="card-body p-0">
            <div class="table-responsive">
                <table class="table table-hover mb-0">
                    <thead class="table-light">
                        <tr>
                            <th>#</th>
                            <th>Requisition #</th>
                            <th>Items</th>
                            <th>Created By</th>
                            <th>Created At</th>
                            <th>Status</th>
                            <th>LPO</th>
                            <th>Actions</th>
                        </tr>
                    </thead>
                    <tbody>
                        @php
                            $num = 1;
                        @endphp
                        @forelse ($requisitions as $requisition)
                            <tr>
                                <td>{{ $num++ }}</td>
                                <td class="fw-bold">{{ $requisition->requisition_number }}</td>
                                <td>{{ $requisition->items->count() }}</td>
                                <td>{{ $requisition->createdBy->full_name ?? '-' }}</td>
                                <td>{{ $requisition->created_at->format('M d, Y H:i') }}</td>
                                <td>
                                    <span class="badge bg-{{ $requisition->status === 'approved' ? 'success' : 'warning text-dark' }}">
                                        {{ ucfirst($requisition->status) }}
                                    </span>
                                </td>
                                <td>
                                    @if($requisition->localPurchaseOrder)
                                        <span class="badge bg-dark">{{ $requisition->localPurchaseOrder->lpo_number }}</span>
                                    @else
                                        <span class="text-muted">-</span>
                                    @endif
                                </td>
                                <td>
                                    <div class="btn-group btn-group-sm">
                                        <button wire:click="openViewModal('{{ $requisition->id }}')"
                                                class="btn btn-outline-info" title="View Details">
                                            <i class="fa-solid fa-eye"></i>
                                        </button>
                                        @if($requisition->canApprove() && (auth()->user()->isSuperAdmin() || auth()->user()->can('approve-requisition')))
                                            <button wire:click="approveRequisition('{{ $requisition->id }}')"
                                                    class="btn btn-outline-success" title="Approve"
                                                    onclick="return confirm('Approve requisition {{ $requisition->requisition_number }}?')">
                                                <i class="fa-solid fa-check"></i>
                                            </button>
                                        @endif
                                    </div>
                                </td>
                            </tr>
                        @empty
                            <tr>
                                <td colspan="8" class="text-center py-4 text-muted">
                                    <i class="fa-solid fa-inbox fa-3x mb-3 d-block"></i>
                                    No {{ $activeTab }} requisitions found.
                                </td>
                            </tr>
                        @endforelse
                    </tbody>
                </table>
            </div>
        </div>
        @if($requisitions->hasPages())
            <div class="card-footer bg-white">
                {{ $requisitions->links() }}
            </div>
        @endif
    </div>

    <!-- Quotation Entry Modal -->
    @if($showQuotationModal)
        <div class="modal fade show d-block" tabindex="-1" style="background-color: rgba(0,0,0,0.6); z-index: 1060;">
            <div class="modal-dialog">
                <div class="modal-content">
                    <form wire:submit.prevent="saveQuotation">
                        <div class="modal-header bg-primary text-white">
                            <h5 class="modal-title">
                                <i class="fa-solid fa-file-invoice-dollar me-2"></i>Fill Quotation Details
                            </h5>
                            <button type="button" class="btn-close btn-close-white" wire:click="closeQuotationModal"></button>
                        </div>
                        <div class="modal-body">
                            <div class="mb-3">
                                <label class="form-label">Supplier</label>
                                <select class="form-select" wire:model="editSupplierId">
                                    <option value="">-- Select Supplier --</option>
                                    @foreach($vendorOptions as $vendor)
                                        <option value="{{ $vendor->id }}">{{ $vendor->name }}</option>
                                    @endforeach
                                </select>
                                @error('editSupplierId') <small class="text-danger d-block">{{ $message }}</small> @enderror
                            </div>

                            <div class="row g-3 mb-3">
                                <div class="col-md-6">
                                    <label class="form-label">Price</label>
                                    <input type="number" step="0.01" min="0" class="form-control" wire:model="editPrice">
                                    @error('editPrice') <small class="text-danger d-block">{{ $message }}</small> @enderror
                                </div>
                                <div class="col-md-6">
                                    <label class="form-label">Quantity</label>
                                    <input type="number" step="0.01" min="0" class="form-control" wire:model="editQuantity">
                                    @error('editQuantity') <small class="text-danger d-block">{{ $message }}</small> @enderror
                                </div>
                            </div>

                            <div class="mb-3">
                                <label class="form-label">Quotation 1</label>
                                <input type="file" class="form-control" wire:model="quotation1File">
                                @error('quotation1File') <small class="text-danger d-block">{{ $message }}</small> @enderror
                            </div>
                            <div class="mb-3">
                                <label class="form-label">Quotation 2</label>
                                <input type="file" class="form-control" wire:model="quotation2File">
                                @error('quotation2File') <small class="text-danger d-block">{{ $message }}</small> @enderror
                            </div>
                            <div class="mb-3">
                                <label class="form-label">Quotation 3</label>
                                <input type="file" class="form-control" wire:model="quotation3File">
                                @error('quotation3File') <small class="text-danger d-block">{{ $message }}</small> @enderror
                            </div>
                        </div>
                        <div class="modal-footer">
                            <button type="button" class="btn btn-secondary" wire:click="closeQuotationModal">Cancel</button>
                            <button type="submit" class="btn btn-primary">
                                <i class="fa-solid fa-save me-2"></i>Save
                            </button>
                        </div>
                    </form>
                </div>
            </div>
        </div>
    @endif

    <!-- View/Detail Modal -->
    @if($showViewModal && $viewingRequisition)
        <div class="modal fade show d-block" tabindex="-1" style="background-color: rgba(0,0,0,0.5);">
            <div class="modal-dialog modal-xl modal-dialog-scrollable">
                <div class="modal-content">
                    <div class="modal-header bg-info text-white">
                        <h5 class="modal-title">
                            <i class="fa-solid fa-file-invoice me-2"></i>
                            Requisition Details - {{ $viewingRequisition->requisition_number }}
                        </h5>
                        <button type="button" class="btn-close btn-close-white" wire:click="closeViewModal"></button>
                    </div>
                    <div class="modal-body">
                        <div class="card border-info mb-4">
                            <div class="card-header bg-info text-white">
                                <h6 class="mb-0">Requisition Information</h6>
                            </div>
                            <div class="card-body">
                                <div class="row">
                                    <div class="col-md-6">
                                        <p><strong>Requisition Number:</strong> {{ $viewingRequisition->requisition_number }}</p>
                                        <p><strong>Created By:</strong> {{ $viewingRequisition->createdBy->full_name ?? '-' }}</p>
                                        <p><strong>Created On:</strong> {{ $viewingRequisition->created_at->format('M d, Y H:i') }}</p>
                                    </div>
                                    <div class="col-md-6">
                                        <p><strong>Status:</strong>
                                            <span class="badge bg-{{ $viewingRequisition->status === 'approved' ? 'success' : 'warning text-dark' }}">
                                                {{ ucfirst($viewingRequisition->status) }}
                                            </span>
                                        </p>
                                        @if($viewingRequisition->approvedBy)
                                            <p><strong>Approved By:</strong> {{ $viewingRequisition->approvedBy->full_name }}</p>
                                            <p><strong>Approved On:</strong> {{ $viewingRequisition->approved_at->format('M d, Y H:i') }}</p>
                                        @endif
                                    </div>
                                </div>
                                @if($viewingRequisition->notes)
                                    <div class="mt-3">
                                        <strong>Notes:</strong>
                                        <p class="border p-2 rounded bg-light mt-1">{{ $viewingRequisition->notes }}</p>
                                    </div>
                                @endif
                            </div>
                        </div>

                        <div class="card border-primary mb-4">
                            <div class="card-header bg-primary text-white">
                                <h6 class="mb-0">Requisition Items</h6>
                            </div>
                            <div class="card-body p-0">
                                <div class="table-responsive">
                                    <table class="table table-sm mb-0">
                                        <thead class="table-light">
                                            <tr>
                                                <th>Item</th>
                                                <th>Order #</th>
                                                <th>Department</th>
                                                <th>Remarks</th>
                                                <th>Quantity</th>
                                                <th>Supplier</th>
                                                <th>Price</th>
                                                <th>Quotations</th>
                                                <th>Status</th>
                                                <th>Item Remarks</th>
                                                <th>Action</th>
                                            </tr>
                                        </thead>
                                        <tbody>
                                            @php
                                                $itemsEditable = $viewingRequisition->isApproved() && ! $viewingRequisition->localPurchaseOrder;
                                                $itemStatusColors = ['pending' => 'secondary', 'approved' => 'success', 'active' => 'info', 'rejected' => 'danger'];
                                            @endphp
                                            @foreach($viewingRequisition->items as $item)
                                                <tr>
                                                    <td>{{ $item->storeOrderItem->item->name ?? '-' }}</td>
                                                    <td>{{ $item->storeOrder->order_number ?? '-' }}</td>
                                                    <td>{{ $item->storeOrder->department->name ?? '-' }}</td>
                                                    <td>{{ $item->storeOrderItem->remarks ?: '-' }}</td>
                                                    <td>{{ $item->quantity }}</td>
                                                    <td>{{ $item->supplier->name ?? '-' }}</td>
                                                    <td>{{ $item->price ? number_format($item->price, 2) : '-' }}</td>
                                                    <td>
                                                        @foreach(['quotation1', 'quotation2', 'quotation3'] as $index => $q)
                                                            @if($item->{$q})
                                                                <a href="{{ Storage::url($item->{$q}) }}" target="_blank" class="badge bg-secondary text-decoration-none me-1">
                                                                    Q{{ $index + 1 }}
                                                                </a>
                                                            @endif
                                                        @endforeach
                                                        @if(!$item->quotation1 && !$item->quotation2 && !$item->quotation3)
                                                            <span class="text-muted">-</span>
                                                        @endif
                                                    </td>
                                                    <td style="min-width: 130px">
                                                        @if($itemsEditable)
                                                            <select class="form-select form-select-sm" wire:model="itemEdits.{{ $item->id }}.status">
                                                                <option value="pending">Pending</option>
                                                                <option value="approved">Approved</option>
                                                                <option value="active">Active</option>
                                                                <option value="rejected">Rejected</option>
                                                            </select>
                                                        @else
                                                            <span class="badge bg-{{ $itemStatusColors[$item->status] ?? 'secondary' }}">
                                                                {{ ucfirst($item->status) }}
                                                            </span>
                                                        @endif
                                                    </td>
                                                    <td style="min-width: 150px">
                                                        @if($itemsEditable)
                                                            <input type="text" class="form-control form-control-sm"
                                                                   wire:model="itemEdits.{{ $item->id }}.remarks" placeholder="Remarks">
                                                        @else
                                                            {{ $item->remarks ?: '-' }}
                                                        @endif
                                                    </td>
                                                    <td class="text-nowrap">
                                                        @if($itemsEditable)
                                                            <button type="button" class="btn btn-sm btn-outline-success mb-1"
                                                                    wire:click="saveItemStatus('{{ $item->id }}')" title="Save status/remarks">
                                                                <i class="fa-solid fa-save"></i>
                                                            </button>
                                                        @endif
                                                        @if($viewingRequisition->isApproved())
                                                            <button type="button" class="btn btn-sm btn-outline-primary"
                                                                    wire:click="openQuotationModal('{{ $item->id }}')">
                                                                <i class="fa-solid fa-edit"></i> Fill
                                                            </button>
                                                        @else
                                                            <span class="text-muted" title="Approve requisition first">
                                                                <i class="fa-solid fa-lock"></i>
                                                            </span>
                                                        @endif
                                                    </td>
                                                </tr>
                                            @endforeach
                                        </tbody>
                                    </table>
                                </div>
                            </div>
                            @if($viewingRequisition->isApproved())
                                <div class="card-footer bg-light">
                                    @php
                                        $approvedItems = $viewingRequisition->items->where('status', 'approved');
                                        $completeCount = $approvedItems->filter(fn ($i) => $i->isQuotationComplete())->count();
                                    @endphp
                                    <small class="text-muted">
                                        {{ $completeCount }} of {{ $approvedItems->count() }} approved item(s) are quotation-complete.
                                    </small>
                                </div>
                            @endif
                        </div>

                        @if($viewingRequisition->localPurchaseOrder)
                            <div class="card border-dark mb-0">
                                <div class="card-header bg-dark text-white">
                                    <h6 class="mb-0">Local Purchase Order Generated</h6>
                                </div>
                                <div class="card-body">
                                    <p><strong>LPO Number:</strong> {{ $viewingRequisition->localPurchaseOrder->lpo_number }}</p>
                                    <p><strong>Total Amount:</strong> {{ number_format($viewingRequisition->localPurchaseOrder->total_amount, 2) }}</p>
                                    <p><strong>Generated On:</strong> {{ $viewingRequisition->localPurchaseOrder->generated_at->format('M d, Y H:i') }}</p>
                                </div>
                            </div>
                        @endif
                    </div>
                    <div class="modal-footer">
                        @if($viewingRequisition->canGenerateLpo() && (auth()->user()->isSuperAdmin() || auth()->user()->can('manage-requisition')))
                            <button type="button" class="btn btn-dark"
                                    wire:click="generateLpo('{{ $viewingRequisition->id }}')"
                                    onclick="return confirm('Generate a Local Purchase Order for this requisition?')">
                                <i class="fa-solid fa-file-contract me-2"></i>Generate LPO
                            </button>
                        @endif
                        <button type="button" class="btn btn-secondary" wire:click="closeViewModal">Close</button>
                    </div>
                </div>
            </div>
        </div>
    @endif
</div>
