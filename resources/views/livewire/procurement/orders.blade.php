<div>
    <!-- Breadcrumb -->
    <nav aria-label="breadcrumb">
        <ol class="breadcrumb">
            <li class="breadcrumb-item"><a href="{{ route('dashboard') }}">Dashboard</a></li>
            <li class="breadcrumb-item active" aria-current="page">Store Orders</li>
        </ol>
    </nav>

    <!-- Page Header -->
    <div class="d-flex justify-content-between align-items-center mb-4">
        <div>
            <h2 class="mb-1">Store Orders</h2>
            <p class="text-muted">Raise an order for stocked items, for further issuing or procurement</p>
        </div>
        <button wire:click="openModal('create')" class="btn btn-primary">
            <i class="fa-solid fa-plus me-2"></i>New Order
        </button>
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

    <!-- Search & Filters -->
    <div class="card shadow-sm mb-4">
        <div class="card-body">
            <div class="row g-3">
                <div class="col-md-{{ $isSuperAdmin ? '3' : '4' }}">
                    <input type="text" wire:model.live.debounce.300ms="search" class="form-control"
                           placeholder="Search by order number or description...">
                </div>
                @if($isSuperAdmin)
                    <div class="col-md-2">
                        <select wire:model.live="filterDepartment" class="form-select">
                            <option value="">All Departments</option>
                            @foreach($departments as $dept)
                                <option value="{{ $dept->id }}">{{ $dept->name }}</option>
                            @endforeach
                        </select>
                    </div>
                @endif
                <div class="col-md-2">
                    <select wire:model.live="filterStatus" class="form-select">
                        <option value="">All Statuses</option>
                        <option value="draft">Draft</option>
                        <option value="submitted">Submitted</option>
                        <option value="review">Review</option>
                        <option value="approved">Approved</option>
                        <option value="rejected">Rejected</option>
                        <option value="issued">Issued</option>
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

    <!-- Orders Table -->
    <div class="card shadow-sm">
        <div class="card-header bg-white">
            <h5 class="mb-0">{{ $isSuperAdmin ? 'All Store Orders' : 'Department Store Orders' }}</h5>
        </div>
        <div class="card-body p-0">
            <div class="table-responsive">
                <table class="table table-hover mb-0">
                    <thead class="table-light">
                        <tr>
                            <th>#</th>
                            <th>Order Number</th>
                            <th>Department</th>
                            <th>Requested By</th>
                            <th>Status</th>
                            <th>Items</th>
                            <th>Ordered</th>
                            <th>Submitted</th>
                            <th>Actions</th>
                        </tr>
                    </thead>
                    <tbody>
                        @php
                            $num=1;
                        @endphp
                        @forelse ($orders as $order)
                            <tr>
                                <td>{{ $num++ }}</td>
                                <td class="fw-bold">{{ $order->order_number }}</td>
                                <td>{{ $order->department->name ?? '-' }}</td>
                                <td>{{ $order->requestedBy->full_name ?? '-' }}</td>
                                <td>
                                    @php
                                        $statusColors = [
                                            'draft' => 'secondary',
                                            'submitted' => 'primary',
                                            'review' => 'warning',
                                            'approved' => 'success',
                                            'rejected' => 'danger',
                                            'issued' => 'dark',
                                        ];
                                        $color = $statusColors[$order->status] ?? 'secondary';
                                    @endphp
                                    <span class="badge bg-{{ $color }}">{{ ucfirst($order->status) }}</span>
                                </td>
                                <td>{{ $order->items->count() }}</td>
                                <td>{{ $order->created_at->format('M d, Y H:i') }}</td>
                                <td>{{ $order->submitted_at ? $order->submitted_at->format('M d, Y') : '-' }}</td>
                                <td>
                                    <div class="btn-group btn-group-sm">
                                        <button wire:click="openViewModal('{{ $order->id }}')"
                                                class="btn btn-outline-info" title="View Details">
                                            <i class="fa-solid fa-eye"></i>
                                        </button>

                                        @if($order->requested_by === auth()->id())
                                            @if($order->canEdit())
                                                <button wire:click="openModal('edit', '{{ $order->id }}')"
                                                        class="btn btn-outline-primary" title="Edit">
                                                    <i class="fa-solid fa-edit"></i>
                                                </button>
                                            @endif

                                            @if($order->canSubmit())
                                                <button wire:click="submit('{{ $order->id }}')"
                                                        class="btn btn-outline-success" title="Submit"
                                                        onclick="return confirm('Submit this order for approval?')">
                                                    <i class="fa-solid fa-paper-plane"></i>
                                                </button>
                                            @endif

                                            @if($order->isDraft())
                                                <button wire:click="delete('{{ $order->id }}')"
                                                        class="btn btn-outline-danger" title="Delete"
                                                        onclick="return confirm('Are you sure you want to delete this order?')">
                                                    <i class="fa-solid fa-trash"></i>
                                                </button>
                                            @endif
                                        @endif
                                    </div>
                                </td>
                            </tr>
                        @empty
                            <tr>
                                <td colspan="8" class="text-center py-4 text-muted">
                                    <i class="fa-solid fa-inbox fa-3x mb-3 d-block"></i>
                                    No store orders found. Click "New Order" to raise one.
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

    <!-- Create/Edit Modal -->
    @if($showModal)
        <div class="modal fade show d-block" tabindex="-1" style="background-color: rgba(0,0,0,0.5);">
            <div class="modal-dialog modal-xl modal-dialog-scrollable">
                <div class="modal-content">
                    <div class="modal-header bg-primary text-white">
                        <h5 class="modal-title">
                            <i class="fa-solid fa-cart-shopping me-2"></i>
                            {{ $modalMode === 'create' ? 'Raise New Order' : 'Edit Order' }}
                        </h5>
                        <button type="button" class="btn-close btn-close-white" wire:click="$set('showModal', false)"></button>
                    </div>
                    <div class="modal-body">
                        <form wire:submit.prevent="save">
                            <!-- Description -->
                            <div class="mb-3">
                                <label class="form-label">Order Description</label>
                                <textarea class="form-control" wire:model="order_description" rows="3"
                                          placeholder="Describe the purpose of this order..."></textarea>
                            </div>

                            <!-- Items Section -->
                            <div class="card border-primary mb-3">
                                <div class="card-header bg-primary text-white d-flex justify-content-between align-items-center">
                                    <h6 class="mb-0">Order Items</h6>
                                    <button type="button" class="btn btn-sm btn-light" wire:click="$set('showItemSelector', true)">
                                        <i class="fa-solid fa-plus me-1"></i>Add Item
                                    </button>
                                </div>
                                <div class="card-body p-0">
                                    @if(count($selectedItems) > 0)
                                        <div class="table-responsive">
                                            <table class="table table-sm mb-0">
                                                <thead class="table-light">
                                                    <tr>
                                                        <th style="width: 5%">#</th>
                                                        <th style="width: 25%">Item</th>
                                                        <th style="width: 15%">Category</th>
                                                        <th style="width: 10%">Unit</th>
                                                        <th style="width: 12%">Quantity</th>
                                                        <th style="width: 28%">Remarks</th>
                                                        <th style="width: 5%"></th>
                                                    </tr>
                                                </thead>
                                                <tbody>
                                                    @foreach($selectedItems as $index => $item)
                                                        <tr>
                                                            <td>{{ $index + 1 }}</td>
                                                            <td>{{ $item['name'] }}</td>
                                                            <td>{{ $item['category_name'] }}</td>
                                                            <td>{{ $item['unit'] ?? '-' }}</td>
                                                            <td>
                                                                <input type="number" class="form-control form-control-sm"
                                                                       wire:model.live="selectedItems.{{ $index }}.quantity"
                                                                       min="1" required>
                                                            </td>
                                                            <td>
                                                                <input type="text" class="form-control form-control-sm"
                                                                       wire:model="selectedItems.{{ $index }}.remarks"
                                                                       placeholder="Remarks (optional)">
                                                            </td>
                                                            <td>
                                                                <button type="button" class="btn btn-sm btn-outline-danger"
                                                                        wire:click="removeItem({{ $index }})" title="Remove">
                                                                    <i class="fa-solid fa-times"></i>
                                                                </button>
                                                            </td>
                                                        </tr>
                                                    @endforeach
                                                </tbody>
                                            </table>
                                        </div>
                                    @else
                                        <div class="text-center py-5 text-muted">
                                            <i class="fa-solid fa-box-open fa-3x mb-3 d-block"></i>
                                            <p>No items added yet. Click "Add Item" to get started.</p>
                                        </div>
                                    @endif
                                    @error('selectedItems')
                                        <div class="alert alert-danger mx-3 mb-3">{{ $message }}</div>
                                    @enderror
                                </div>
                            </div>
                        </form>
                    </div>
                    <div class="modal-footer">
                        <button type="button" class="btn btn-secondary" wire:click="$set('showModal', false)">Cancel</button>
                        <button type="button" class="btn btn-primary" wire:click="save">
                            <i class="fa-solid fa-save me-2"></i>Save Order
                        </button>
                    </div>
                </div>
            </div>
        </div>
    @endif

    <!-- Item Selector Modal -->
    @if($showItemSelector)
        <div class="modal fade show d-block" tabindex="-1" style="background-color: rgba(0,0,0,0.6); z-index: 1060;">
            <div class="modal-dialog modal-lg">
                <div class="modal-content">
                    <div class="modal-header bg-info text-white">
                        <h5 class="modal-title">
                            <i class="fa-solid fa-search me-2"></i>Select Stocked Item
                        </h5>
                        <button type="button" class="btn-close btn-close-white" wire:click="$set('showItemSelector', false)"></button>
                    </div>
                    <div class="modal-body">
                        <!-- Search and Filter -->
                        <div class="row g-3 mb-3">
                            <div class="col-md-8">
                                <input type="text" class="form-control" wire:model.live="itemSearch"
                                       placeholder="Search items...">
                            </div>
                            <div class="col-md-4">
                                <select class="form-select" wire:model.live="filterCategory">
                                    <option value="">All Categories</option>
                                    @foreach($categories as $category)
                                        <option value="{{ $category->id }}">{{ $category->name }}</option>
                                    @endforeach
                                </select>
                            </div>
                        </div>

                        <!-- Items List -->
                        <div class="list-group" style="max-height: 400px; overflow-y: auto;">
                            @forelse($availableItems as $item)
                                @php
                                    $alreadySelected = collect($selectedItems)->firstWhere('item_id', $item->id);
                                @endphp
                                <button type="button"
                                        class="list-group-item list-group-item-action {{ $alreadySelected ? 'disabled' : '' }}"
                                        wire:click="addItem('{{ $item->id }}', '{{ $item->name }}', '{{ $item->product_category_id }}', '{{ $item->category->name ?? 'Uncategorized' }}', '{{ $item->unit }}')"
                                        {{ $alreadySelected ? 'disabled' : '' }}>
                                    <div class="d-flex w-100 justify-content-between">
                                        <h6 class="mb-1">{{ $item->name }}</h6>
                                        @if($alreadySelected)
                                            <span class="badge bg-success">Added</span>
                                        @endif
                                    </div>
                                    <small class="text-muted">
                                        Category: {{ $item->category->name ?? 'Uncategorized' }}
                                        @if($item->unit) &middot; Unit: {{ $item->unit }} @endif
                                    </small>
                                </button>
                            @empty
                                <div class="text-center py-4 text-muted">
                                    No stocked items found matching your criteria.
                                </div>
                            @endforelse
                        </div>
                    </div>
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
                        <!-- Progress Trace -->
                        <div class="card border-secondary mb-4">
                            <div class="card-header bg-secondary text-white">
                                <h6 class="mb-0"><i class="fa-solid fa-timeline me-2"></i>Progress</h6>
                            </div>
                            <div class="card-body">
                                @php
                                    $steps = [
                                        [
                                            'label' => 'Raised',
                                            'done' => true,
                                            'by' => $viewingOrder->requestedBy->full_name ?? null,
                                            'at' => $viewingOrder->created_at,
                                            'icon' => 'fa-file-circle-plus',
                                        ],
                                        [
                                            'label' => 'Submitted',
                                            'done' => (bool) $viewingOrder->submitted_at,
                                            'by' => $viewingOrder->submitted_at ? ($viewingOrder->requestedBy->full_name ?? null) : null,
                                            'at' => $viewingOrder->submitted_at,
                                            'icon' => 'fa-paper-plane',
                                        ],
                                        [
                                            'label' => 'Review',
                                            'done' => in_array($viewingOrder->status, ['review', 'approved', 'issued']),
                                            'by' => $viewingOrder->status === 'review' ? ($viewingOrder->approvedBy->full_name ?? null) : null,
                                            'at' => $viewingOrder->status === 'review' ? $viewingOrder->approved_at : null,
                                            'icon' => 'fa-magnifying-glass',
                                            'variant' => 'warning',
                                        ],
                                        [
                                            'label' => $viewingOrder->status === 'rejected' ? 'Rejected' : 'Approved',
                                            'done' => in_array($viewingOrder->status, ['approved', 'rejected', 'issued']),
                                            'by' => in_array($viewingOrder->status, ['approved', 'rejected', 'issued']) ? ($viewingOrder->approvedBy->full_name ?? null) : null,
                                            'at' => in_array($viewingOrder->status, ['approved', 'rejected', 'issued']) ? $viewingOrder->approved_at : null,
                                            'icon' => $viewingOrder->status === 'rejected' ? 'fa-xmark' : 'fa-check',
                                            'variant' => $viewingOrder->status === 'rejected' ? 'danger' : 'success',
                                        ],
                                    ];
                                @endphp
                                <div class="d-flex flex-column flex-md-row justify-content-between">
                                    @foreach($steps as $index => $step)
                                        <div class="d-flex flex-md-column align-items-center text-center flex-fill position-relative mb-3 mb-md-0">
                                            <div class="rounded-circle d-flex align-items-center justify-content-center mb-2 me-3 me-md-0
                                                {{ $step['done'] ? 'bg-'.($step['variant'] ?? 'success').' text-white' : 'bg-light text-muted border' }}"
                                                 style="width: 40px; height: 40px; flex-shrink: 0;">
                                                <i class="fa-solid {{ $step['icon'] }}"></i>
                                            </div>
                                            <div>
                                                <div class="fw-bold {{ $step['done'] ? '' : 'text-muted' }}">{{ $step['label'] }}</div>
                                                @if($step['done'] && $step['at'])
                                                    <small class="text-muted d-block">{{ $step['at']->format('M d, Y H:i') }}</small>
                                                @endif
                                                @if($step['done'] && $step['by'])
                                                    <small class="text-muted d-block">by {{ $step['by'] }}</small>
                                                @endif
                                                @if(!$step['done'])
                                                    <small class="text-muted d-block">Pending</small>
                                                @endif
                                            </div>
                                        </div>
                                        @if($index < count($steps) - 1)
                                            <div class="d-none d-md-flex align-items-center flex-fill px-2" style="margin-top: 18px;">
                                                <div class="w-100" style="height: 2px; background-color: {{ $steps[$index + 1]['done'] ? '#198754' : '#dee2e6' }};"></div>
                                            </div>
                                        @endif
                                    @endforeach
                                </div>

                                @if($viewingOrder->status === 'rejected' && $viewingOrder->rejection_reason)
                                    <div class="alert alert-danger mt-3 mb-0">
                                        <strong>Rejection Reason:</strong> {{ $viewingOrder->rejection_reason }}
                                    </div>
                                @endif
                            </div>
                        </div>

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
                                            <span class="badge bg-{{ $color }}">{{ ucfirst($viewingOrder->status) }}</span>
                                        </p>
                                        <p><strong>Submitted:</strong> {{ $viewingOrder->submitted_at ? $viewingOrder->submitted_at->format('M d, Y H:i') : 'Not submitted' }}</p>
                                        @if($viewingOrder->approvedBy)
                                            <p><strong>{{ $viewingOrder->status === 'rejected' ? 'Rejected By' : 'Approved By' }}:</strong> {{ $viewingOrder->approvedBy->full_name }}</p>
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
                                                <th style="width: 22%">Item</th>
                                                <th style="width: 14%">Category</th>
                                                <th style="width: 8%">Unit</th>
                                                <th style="width: 8%">Quantity</th>
                                                <th style="width: 18%">Remarks</th>
                                                <th>Procurement Requisition</th>
                                            </tr>
                                        </thead>
                                        <tbody>
                                            @php
                                                $prItemStatusColors = ['pending' => 'secondary', 'approved' => 'success', 'active' => 'info', 'rejected' => 'danger'];
                                            @endphp
                                            @foreach($viewingOrder->items as $index => $orderItem)
                                                @php
                                                    $prItem = $orderItem->purchaseRequisitionItems->first();
                                                @endphp
                                                <tr>
                                                    <td>{{ $index + 1 }}</td>
                                                    <td><strong>{{ $orderItem->item->name }}</strong></td>
                                                    <td><small class="text-muted">{{ $orderItem->item->category->name ?? 'Uncategorized' }}</small></td>
                                                    <td>{{ $orderItem->item->unit ?? '-' }}</td>
                                                    <td class="text-center">{{ $orderItem->quantity }}</td>
                                                    <td><small>{{ $orderItem->remarks ?? '-' }}</small></td>
                                                    <td>
                                                        @if(! $prItem)
                                                            <span class="badge bg-light text-muted border">Not yet requisitioned</span>
                                                        @else
                                                            <div class="d-flex flex-column gap-1">
                                                                <div>
                                                                    <small class="text-muted d-block">{{ $prItem->purchaseRequisition->requisition_number ?? '-' }}</small>
                                                                    <span class="badge bg-{{ $prItemStatusColors[$prItem->status] ?? 'secondary' }}">
                                                                        {{ ucfirst($prItem->status) }}
                                                                    </span>
                                                                </div>
                                                                @if($prItem->remarks)
                                                                    <small class="text-muted">{{ $prItem->remarks }}</small>
                                                                @endif
                                                                @if($prItem->localPurchaseOrderItem)
                                                                    <span class="badge bg-dark">
                                                                        <i class="fa-solid fa-file-contract me-1"></i>{{ $prItem->localPurchaseOrderItem->localPurchaseOrder->lpo_number ?? '-' }}
                                                                    </span>
                                                                @endif
                                                            </div>
                                                        @endif
                                                    </td>
                                                </tr>
                                            @endforeach
                                        </tbody>
                                    </table>
                                </div>
                            </div>
                        </div>

                        <!-- Assigned For Remarks -->
                        @if($viewingOrder->assignedDuties->count())
                            <div class="card border-secondary mb-0">
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
                        <button type="button" class="btn btn-primary" wire:click="closeViewModal">Close</button>
                    </div>
                </div>
            </div>
        </div>
    @endif
</div>
