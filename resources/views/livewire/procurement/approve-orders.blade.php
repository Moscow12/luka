<div>
    <!-- Breadcrumb -->
    <nav aria-label="breadcrumb">
        <ol class="breadcrumb">
            <li class="breadcrumb-item"><a href="{{ route('dashboard') }}">Dashboard</a></li>
            <li class="breadcrumb-item active" aria-current="page">Approve Store Orders</li>
        </ol>
    </nav>

    <!-- Page Header -->
    <div class="d-flex justify-content-between align-items-center mb-4">
        <div>
            <h2 class="mb-1">Approve Store Orders</h2>
            <p class="text-muted">Review and action store orders submitted across all departments</p>
        </div>
        <a href="{{ route('procurement.requisitions') }}" class="btn btn-outline-secondary btn-sm">
            <i class="fa-solid fa-file-invoice me-1"></i>View Purchase Requisitions
        </a>
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
            <button type="button" class="nav-link {{ $activeTab === 'pending' ? 'active' : '' }}" wire:click="setTab('pending')">
                Pending Requests
                @if($pendingCount > 0)
                    <span class="badge bg-danger ms-1">{{ $pendingCount }}</span>
                @endif
            </button>
        </li>
        <li class="nav-item">
            <button type="button" class="nav-link {{ $activeTab === 'requisition' ? 'active' : '' }}" wire:click="setTab('requisition')">
                Purchase Requisition
                @if($reviewCount > 0)
                    <span class="badge bg-warning text-dark ms-1">{{ $reviewCount }}</span>
                @endif
            </button>
        </li>
        <li class="nav-item">
            <button type="button" class="nav-link {{ $activeTab === 'approved' ? 'active' : '' }}" wire:click="setTab('approved')">
                Approved Requests
            </button>
        </li>
        <li class="nav-item">
            <button type="button" class="nav-link {{ $activeTab === 'rejected' ? 'active' : '' }}" wire:click="setTab('rejected')">
                Rejected Requests
            </button>
        </li>
    </ul>

    <!-- Search & Filters -->
    <div class="card shadow-sm mb-4">
        <div class="card-body">
            <div class="row g-3">
                <div class="col-md-4">
                    <input type="text" wire:model.live.debounce.300ms="search" class="form-control"
                           placeholder="Search by order number or description...">
                </div>
                <div class="col-md-3">
                    <select wire:model.live="filterDepartment" class="form-select">
                        <option value="">All Departments</option>
                        @foreach($departments as $dept)
                            <option value="{{ $dept->id }}">{{ $dept->name }}</option>
                        @endforeach
                    </select>
                </div>

                <x-forms.input type="date" name="filterDateFrom" label="From Date"
                    colMd="2" max="{{ $filterDateTo ?: now()->toDateString() }}"
                    wire:model.live="filterDateFrom" />

                <x-forms.input type="date" name="filterDateTo" label="To Date"
                    colMd="2" min="{{ $filterDateFrom }}" max="{{ now()->toDateString() }}"
                    wire:model.live="filterDateTo" />

                <div class="col-md-1 d-flex align-items-start">
                    <button type="button" class="btn btn-outline-secondary" wire:click="resetFilters" title="Clear filters">
                        <i class="fa-solid fa-rotate-left"></i>
                    </button>
                </div>
            </div>
        </div>
    </div>

    @if($activeTab === 'requisition')
        <!-- Purchase Requisition: flat item list across all orders in review -->
        <div class="card shadow-sm">
            <div class="card-header bg-white d-flex justify-content-between align-items-center flex-wrap gap-2">
                <div>
                    <h6 class="mb-0">Requested Items Under Review</h6>
                    <small class="text-muted">All items across orders awaiting final approval</small>
                </div>
                @if($isSuperAdmin || auth()->user()?->can('create-requisition'))
                    <button type="button" class="btn btn-sm btn-primary" wire:click="openCreateRequisitionModal" @disabled(count($selectedItemIds) === 0)>
                        <i class="fa-solid fa-file-invoice me-1"></i>Create Purchase Requisition ({{ count($selectedItemIds) }})
                    </button>
                @endif
            </div>
            <div class="card-body p-0">
                <div class="table-responsive">
                    <table class="table table-hover mb-0">
                        <thead class="table-light">
                            <tr>
                                <th style="width: 3%">
                                    <input type="checkbox" class="form-check-input" wire:model.live="selectAllItems">
                                </th>
                                <th>#</th>
                                <th>Order Number</th>
                                <th>Item</th>
                                <th>Description / Remarks</th>
                                <th>Quantity</th>
                                <th>Department</th>
                                <th>Date Requested</th>
                                <th>Actions</th>
                            </tr>
                        </thead>
                        <tbody>
                            @php
                                $itemNum = 1;
                            @endphp
                            @forelse ($items as $orderItem)
                                <tr>
                                    <td>
                                        @if(($orderItem->purchase_requisition_items_count ?? 0) > 0)
                                            <span class="badge bg-secondary" title="Already requisitioned">
                                                <i class="fa-solid fa-check"></i>
                                            </span>
                                        @else
                                            <input type="checkbox" class="form-check-input" wire:model.live="selectedItemIds" value="{{ $orderItem->id }}">
                                        @endif
                                    </td>
                                    <td>{{ $itemNum++ }}</td>
                                    <td class="fw-bold">{{ $orderItem->storeOrder->order_number ?? '-' }}</td>
                                    <td>{{ $orderItem->item->name ?? '-' }}</td>
                                    <td>{{ $orderItem->remarks ?: ($orderItem->storeOrder->order_description ?? '-') }}</td>
                                    <td>{{ $orderItem->quantity }}</td>
                                    <td>{{ $orderItem->storeOrder->department->name ?? '-' }}</td>
                                    <td>{{ $orderItem->storeOrder->created_at?->format('M d, Y H:i') ?? '-' }}</td>
                                    <td>
                                        <button wire:click="openViewModal('{{ $orderItem->store_order_id }}')"
                                                class="btn btn-sm btn-outline-info" title="View Order">
                                            <i class="fa-solid fa-eye"></i>
                                        </button>
                                    </td>
                                </tr>
                            @empty
                                <tr>
                                    <td colspan="9" class="text-center py-4 text-muted">
                                        <i class="fa-solid fa-inbox fa-3x mb-3 d-block"></i>
                                        No items currently under review.
                                    </td>
                                </tr>
                            @endforelse
                        </tbody>
                    </table>
                </div>
            </div>
            @if($items->hasPages())
                <div class="card-footer bg-white">
                    {{ $items->links() }}
                </div>
            @endif
        </div>
    @else
        <!-- Orders Table -->
        <div class="card shadow-sm">
            <div class="card-body p-0">
                <div class="table-responsive">
                    <table class="table table-hover mb-0">
                        <thead class="table-light">
                            <tr>
                                <th>#</th>
                                <th>Order Number</th>
                                <th>Department</th>
                                <th>Requested By</th>
                                <th>Items</th>
                                <th>Ordered</th>
                                <th>Submitted</th>
                                @if($activeTab !== 'pending')
                                    <th>Actioned By</th>
                                @endif
                                <th>Actions</th>
                            </tr>
                        </thead>
                        <tbody>
                            @php
                                $num = 1;
                            @endphp
                            @forelse ($orders as $order)
                                <tr>
                                    <td>{{ $num++ }}</td>
                                    <td class="fw-bold">{{ $order->order_number }}</td>
                                    <td>{{ $order->department->name ?? '-' }}</td>
                                    <td>{{ $order->requestedBy->full_name ?? '-' }}</td>
                                    <td>{{ $order->items->count() }}</td>
                                    <td>{{ $order->created_at->format('M d, Y H:i') }}</td>
                                    <td>{{ $order->submitted_at ? $order->submitted_at->format('M d, Y H:i') : '-' }}</td>
                                    @if($activeTab !== 'pending')
                                        <td>
                                            {{ $order->approvedBy->full_name ?? '-' }}
                                            @if($order->approved_at)
                                                <br><small class="text-muted">{{ $order->approved_at->format('M d, Y H:i') }}</small>
                                            @endif
                                        </td>
                                    @endif
                                    <td>
                                        <div class="btn-group btn-group-sm">
                                            <button wire:click="openViewModal('{{ $order->id }}')"
                                                    class="btn btn-outline-info" title="View Details">
                                                <i class="fa-solid fa-eye"></i>
                                            </button>

                                            @if($activeTab === 'pending' && $canFirstApprove)
                                                <button wire:click="approve('{{ $order->id }}')"
                                                        class="btn btn-outline-success" title="Move to Review"
                                                        onclick="return confirm('Move order {{ $order->order_number }} to review?')">
                                                    <i class="fa-solid fa-check"></i>
                                                </button>
                                                <button wire:click="openRejectModal('{{ $order->id }}')"
                                                        class="btn btn-outline-danger" title="Reject">
                                                    <i class="fa-solid fa-xmark"></i>
                                                </button>
                                            @endif
                                        </div>
                                    </td>
                                </tr>
                            @empty
                                <tr>
                                    <td colspan="9" class="text-center py-4 text-muted">
                                        <i class="fa-solid fa-inbox fa-3x mb-3 d-block"></i>
                                        No {{ $activeTab }} orders found.
                                    </td>
                                </tr>
                            @endforelse
                        </tbody>
                    </table>
                </div>
            </div>
            @if($orders->hasPages())
                <div class="card-footer bg-white">
                    {{ $orders->links() }}
                </div>
            @endif
        </div>
    @endif

    <!-- Reject Modal -->
    @if($rejectingOrderId)
        <div class="modal fade show d-block" tabindex="-1" style="background-color: rgba(0,0,0,0.5);">
            <div class="modal-dialog">
                <div class="modal-content">
                    <form wire:submit.prevent="reject">
                        <div class="modal-header bg-danger text-white">
                            <h5 class="modal-title">
                                <i class="fa-solid fa-xmark me-2"></i>Reject Order
                            </h5>
                            <button type="button" class="btn-close btn-close-white" wire:click="closeRejectModal"></button>
                        </div>
                        <div class="modal-body">
                            <label class="form-label">Reason for rejection</label>
                            <textarea class="form-control" wire:model="rejection_reason" rows="3"
                                      placeholder="Explain why this order is being rejected..."></textarea>
                            @error('rejection_reason') <small class="text-danger">{{ $message }}</small> @enderror
                        </div>
                        <div class="modal-footer">
                            <button type="button" class="btn btn-secondary" wire:click="closeRejectModal">Cancel</button>
                            <button type="submit" class="btn btn-danger">
                                <i class="fa-solid fa-xmark me-2"></i>Reject Order
                            </button>
                        </div>
                    </form>
                </div>
            </div>
        </div>
    @endif

    <!-- Assign for Remarks Modal -->
    @if($assigningOrderId)
        <div class="modal fade show d-block" tabindex="-1" style="background-color: rgba(0,0,0,0.5); z-index: 1060;">
            <div class="modal-dialog">
                <div class="modal-content">
                    <form wire:submit.prevent="assignDuty">
                        <div class="modal-header bg-primary text-white">
                            <h5 class="modal-title">
                                <i class="fa-solid fa-user-plus me-2"></i>Assign for Remarks
                            </h5>
                            <button type="button" class="btn-close btn-close-white" wire:click="closeAssignModal"></button>
                        </div>
                        <div class="modal-body">
                            <div class="mb-3">
                                <label class="form-label">Employee</label>
                                @if($this->selectedAssignEmployee)
                                    <div class="d-flex justify-content-between align-items-center border rounded p-2">
                                        <span>{{ $this->selectedAssignEmployee->getFullName() }}</span>
                                        <button type="button" class="btn btn-sm btn-outline-secondary" wire:click="$set('assignEmployeeId', '')">
                                            <i class="fa-solid fa-xmark"></i>
                                        </button>
                                    </div>
                                @else
                                    <input type="text" class="form-control" wire:model.live.debounce.300ms="assignEmployeeSearch"
                                           placeholder="Search employee by name or number...">
                                    @if($assignEmployeeSearch !== '')
                                        <div class="list-group mt-1" style="max-height: 200px; overflow-y: auto;">
                                            @forelse($this->assignableEmployees as $employee)
                                                <button type="button" class="list-group-item list-group-item-action"
                                                        wire:click="selectAssignEmployee('{{ $employee->id }}')">
                                                    {{ $employee->getFullName() }}
                                                    <small class="text-muted">({{ $employee->employee_no }})</small>
                                                </button>
                                            @empty
                                                <div class="list-group-item text-muted">No employees found.</div>
                                            @endforelse
                                        </div>
                                    @endif
                                @endif
                                @error('assignEmployeeId') <small class="text-danger d-block">{{ $message }}</small> @enderror
                            </div>

                            <div class="mb-3">
                                <label class="form-label">Priority</label>
                                <select class="form-select" wire:model="assignPriority">
                                    <option value="low">Low</option>
                                    <option value="medium">Medium</option>
                                    <option value="high">High</option>
                                    <option value="urgent">Urgent</option>
                                </select>
                                @error('assignPriority') <small class="text-danger d-block">{{ $message }}</small> @enderror
                            </div>

                            <div class="mb-3">
                                <label class="form-label">Due Date (optional)</label>
                                <input type="date" class="form-control" wire:model="assignDueDate" min="{{ now()->toDateString() }}">
                                @error('assignDueDate') <small class="text-danger d-block">{{ $message }}</small> @enderror
                            </div>

                            <div class="mb-3">
                                <label class="form-label">Notes (optional)</label>
                                <textarea class="form-control" wire:model="assignNotes" rows="2"
                                          placeholder="Any additional instructions for the assignee..."></textarea>
                                @error('assignNotes') <small class="text-danger d-block">{{ $message }}</small> @enderror
                            </div>
                        </div>
                        <div class="modal-footer">
                            <button type="button" class="btn btn-secondary" wire:click="closeAssignModal">Cancel</button>
                            <button type="submit" class="btn btn-primary">
                                <i class="fa-solid fa-user-plus me-2"></i>Assign
                            </button>
                        </div>
                    </form>
                </div>
            </div>
        </div>
    @endif

    <!-- Create Purchase Requisition Modal -->
    @if($showCreateRequisitionModal)
        <div class="modal fade show d-block" tabindex="-1" style="background-color: rgba(0,0,0,0.5);">
            <div class="modal-dialog">
                <div class="modal-content">
                    <form wire:submit.prevent="createRequisition">
                        <div class="modal-header bg-primary text-white">
                            <h5 class="modal-title">
                                <i class="fa-solid fa-file-invoice me-2"></i>Create Purchase Requisition
                            </h5>
                            <button type="button" class="btn-close btn-close-white" wire:click="closeCreateRequisitionModal"></button>
                        </div>
                        <div class="modal-body">
                            <p>You are about to create a purchase requisition for
                                <strong>{{ count($selectedItemIds) }}</strong> selected item(s).</p>
                            <div class="mb-3">
                                <label class="form-label">Notes (optional)</label>
                                <textarea class="form-control" wire:model="requisitionNotes" rows="3"
                                          placeholder="Any additional notes for this requisition..."></textarea>
                                @error('requisitionNotes') <small class="text-danger">{{ $message }}</small> @enderror
                            </div>
                        </div>
                        <div class="modal-footer">
                            <button type="button" class="btn btn-secondary" wire:click="closeCreateRequisitionModal">Cancel</button>
                            <button type="submit" class="btn btn-primary">
                                <i class="fa-solid fa-file-invoice me-2"></i>Create Requisition
                            </button>
                        </div>
                    </form>
                </div>
            </div>
        </div>
    @endif

    <!-- View/Detail Modal -->
    @if($showViewModal && $viewingOrder)
        <div class="modal fade show d-block" tabindex="-1" style="background-color: rgba(0,0,0,0.5);">
            <div class="modal-dialog modal-xl modal-dialog-scrollable">
                <div class="modal-content">
                    <div class="modal-header bg-info text-white">
                        <h5 class="modal-title">
                            <i class="fa-solid fa-file-invoice me-2"></i>
                            Order Details - {{ $viewingOrder->order_number }}
                        </h5>
                        <button type="button" class="btn-close btn-close-white" wire:click="closeViewModal"></button>
                    </div>
                    <div class="modal-body">
                        <!-- Order Information -->
                        <div class="card border-info mb-4">
                            <div class="card-header bg-info text-white">
                                <h6 class="mb-0">Order Information</h6>
                            </div>
                            <div class="card-body">
                                <div class="row">
                                    <div class="col-md-6">
                                        <p><strong>Order Number:</strong> {{ $viewingOrder->order_number }}</p>
                                        <p><strong>Department:</strong> {{ $viewingOrder->department->name ?? '-' }}</p>
                                        <p><strong>Requested By:</strong> {{ $viewingOrder->requestedBy->full_name }}</p>
                                        <p><strong>Ordered On:</strong> {{ $viewingOrder->created_at->format('M d, Y H:i') }}</p>
                                    </div>
                                    <div class="col-md-6">
                                        <p><strong>Status:</strong>
                                            @php
                                                $statusColors = [
                                                    'draft' => 'secondary',
                                                    'submitted' => 'primary',
                                                    'review' => 'warning',
                                                    'approved' => 'success',
                                                    'rejected' => 'danger',
                                                    'issued' => 'dark',
                                                ];
                                                $color = $statusColors[$viewingOrder->status] ?? 'secondary';
                                            @endphp
                                            <span class="badge bg-{{ $color }} {{ $color === 'warning' ? 'text-dark' : '' }}">{{ ucfirst($viewingOrder->status) }}</span>
                                        </p>
                                        <p><strong>Submitted:</strong> {{ $viewingOrder->submitted_at ? $viewingOrder->submitted_at->format('M d, Y H:i') : 'Not submitted' }}</p>
                                        @if($viewingOrder->approvedBy)
                                            <p>
                                                <strong>
                                                    @if($viewingOrder->status === 'rejected')
                                                        Rejected By:
                                                    @elseif($viewingOrder->status === 'review')
                                                        Moved to Review By:
                                                    @else
                                                        Approved By:
                                                    @endif
                                                </strong>
                                                {{ $viewingOrder->approvedBy->full_name }}
                                            </p>
                                        @endif
                                        @if($viewingOrder->rejection_reason)
                                            <p><strong>Rejection Reason:</strong> {{ $viewingOrder->rejection_reason }}</p>
                                        @endif
                                    </div>
                                </div>
                                @if($viewingOrder->order_description)
                                    <div class="mt-3">
                                        <strong>Description:</strong>
                                        <p class="border p-2 rounded bg-light mt-1">{{ $viewingOrder->order_description }}</p>
                                    </div>
                                @endif
                            </div>
                        </div>

                        <!-- Order Items -->
                        <div class="card border-primary mb-4">
                            <div class="card-header bg-primary text-white">
                                <h6 class="mb-0">Order Items</h6>
                            </div>
                            <div class="card-body p-0">
                                <div class="table-responsive">
                                    <table class="table table-sm mb-0">
                                        <thead class="table-light">
                                            <tr>
                                                <th style="width: 5%">#</th>
                                                <th style="width: 30%">Item</th>
                                                <th style="width: 20%">Category</th>
                                                <th style="width: 10%">Unit</th>
                                                <th style="width: 10%">Quantity</th>
                                                <th>Remarks</th>
                                            </tr>
                                        </thead>
                                        <tbody>
                                            @foreach($viewingOrder->items as $index => $orderItem)
                                                <tr>
                                                    <td>{{ $index + 1 }}</td>
                                                    <td><strong>{{ $orderItem->item->name }}</strong></td>
                                                    <td><small class="text-muted">{{ $orderItem->item->category->name ?? 'Uncategorized' }}</small></td>
                                                    <td>{{ $orderItem->item->unit ?? '-' }}</td>
                                                    <td class="text-center">{{ $orderItem->quantity }}</td>
                                                    <td><small>{{ $orderItem->remarks ?? '-' }}</small></td>
                                                </tr>
                                            @endforeach
                                        </tbody>
                                    </table>
                                </div>
                            </div>
                        </div>

                        <!-- Assigned Duties -->
                        @if($viewingOrder->assignedDuties->count())
                            <div class="card border-secondary mb-4">
                                <div class="card-header bg-secondary text-white">
                                    <h6 class="mb-0">Assigned For Remarks</h6>
                                </div>
                                <div class="card-body p-0">
                                    <table class="table table-sm mb-0">
                                        <thead class="table-light">
                                            <tr>
                                                <th>Employee</th>
                                                <th>Priority</th>
                                                <th>Status</th>
                                                <th>Due Date</th>
                                            </tr>
                                        </thead>
                                        <tbody>
                                            @foreach($viewingOrder->assignedDuties as $duty)
                                                <tr>
                                                    <td>{{ $duty->employee?->getFullName() ?? '-' }}</td>
                                                    <td><span class="badge bg-info text-dark">{{ ucfirst($duty->priority) }}</span></td>
                                                    <td>
                                                        @php
                                                            $dutyStatusColors = ['assigned' => 'warning', 'in_progress' => 'info', 'completed' => 'success', 'cancelled' => 'secondary'];
                                                        @endphp
                                                        <span class="badge bg-{{ $dutyStatusColors[$duty->status] ?? 'secondary' }}">{{ ucfirst(str_replace('_', ' ', $duty->status)) }}</span>
                                                    </td>
                                                    <td>{{ $duty->end_date ? $duty->end_date->format('M d, Y') : '-' }}</td>
                                                </tr>
                                            @endforeach
                                        </tbody>
                                    </table>
                                </div>
                            </div>
                        @endif
                    </div>
                    <div class="modal-footer">
                        <button type="button" class="btn btn-outline-primary" wire:click="openAssignModal('{{ $viewingOrder->id }}')">
                            <i class="fa-solid fa-user-plus me-2"></i>Assign for Remarks
                        </button>
                        @php
                            $canActOnThis = ($viewingOrder->canFirstApprove() && $canFirstApprove)
                                || ($viewingOrder->canFinalApprove() && $canFinalApprove);
                        @endphp
                        @if($canActOnThis)
                            <button type="button" class="btn btn-danger" wire:click="openRejectModal('{{ $viewingOrder->id }}')">
                                <i class="fa-solid fa-xmark me-2"></i>Reject
                            </button>
                            <button type="button" class="btn btn-success"
                                    wire:click="approve('{{ $viewingOrder->id }}')"
                                    onclick="return confirm('{{ $viewingOrder->canFirstApprove() ? 'Move' : 'Approve' }} order {{ $viewingOrder->order_number }}{{ $viewingOrder->canFirstApprove() ? ' to review' : '' }}?')">
                                <i class="fa-solid fa-check me-2"></i>{{ $viewingOrder->canFirstApprove() ? 'Move to Review' : 'Approve' }}
                            </button>
                        @endif
                        <button type="button" class="btn btn-secondary" wire:click="closeViewModal">Close</button>
                    </div>
                </div>
            </div>
        </div>
    @endif
</div>
