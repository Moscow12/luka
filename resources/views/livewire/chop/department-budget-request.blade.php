<div>
    <!-- Breadcrumb -->
    <nav aria-label="breadcrumb">
        <ol class="breadcrumb">
            <li class="breadcrumb-item"><a href="{{ route('dashboard') }}">Dashboard</a></li>
            <li class="breadcrumb-item"><a href="{{ route('chop.settings') }}">CHOP</a></li>
            <li class="breadcrumb-item active" aria-current="page">My Budget Requests</li>
        </ol>
    </nav>

    <!-- Page Header -->
    <div class="d-flex justify-content-between align-items-center mb-4">
        <div>
            <h2 class="mb-1">My Budget Requests</h2>
            <p class="text-muted">Create and manage your department's budget requests</p>
        </div>
        <button wire:click="openModal('create')" class="btn btn-primary">
            <i class="fa-solid fa-plus me-2"></i>New Request
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

    <!-- Revision Required Alert -->
    @php
        $revisionRequests = $requests->where('status', 'revision_required');
    @endphp
    @if($revisionRequests->count() > 0)
        <div class="alert alert-warning alert-dismissible fade show" role="alert">
            <h5 class="alert-heading">
                <i class="fa-solid fa-exclamation-triangle me-2"></i>Action Required: Revision Needed
            </h5>
            <p class="mb-2">
                You have <strong>{{ $revisionRequests->count() }}</strong> budget request(s) that need revision.
                The Director has requested changes. Please review the director's notes, make necessary changes, and re-submit.
            </p>
            <hr>
            <ul class="mb-0">
                @foreach($revisionRequests as $req)
                    <li>
                        <strong>{{ $req->request_number }}</strong> - {{ $req->financialYear->name }}
                        @if($req->director_notes)
                            <br><small class="text-muted">Notes: {{ $req->director_notes }}</small>
                        @endif
                    </li>
                @endforeach
            </ul>
            <button type="button" class="btn-close" data-bs-dismiss="alert"></button>
        </div>
    @endif

    <!-- Search and Filters -->
    <div class="card shadow-sm mb-4">
        <div class="card-body">
            <div class="row g-3">
                <div class="col-md-12">
                    <input type="text" wire:model.live="search" class="form-control"
                           placeholder="Search by request number or financial year...">
                </div>
            </div>
        </div>
    </div>

    <!-- Requests Table -->
    <div class="card shadow-sm">
        <div class="card-header bg-white">
            <h5 class="mb-0">Budget Requests</h5>
        </div>
        <div class="card-body p-0">
            <div class="table-responsive">
                <table class="table table-hover mb-0">
                    <thead class="table-light">
                        <tr>
                            <th>Request Number</th>
                            <th>Financial Year</th>
                            <th>Status</th>
                            <th>Items</th>
                            <th>Estimated Amount</th>
                            <th>Approved Amount</th>
                            <th>Submitted</th>
                            <th>Actions</th>
                        </tr>
                    </thead>
                    <tbody>
                        @forelse ($requests as $request)
                            <tr class="{{ $request->status === 'revision_required' ? 'table-warning' : '' }}">
                                <td class="fw-bold">
                                    {{ $request->request_number }}
                                    @if($request->status === 'revision_required')
                                        <i class="fa-solid fa-exclamation-triangle text-warning ms-1" title="Revision Required"></i>
                                    @endif
                                </td>
                                <td>{{ $request->financialYear->name }}</td>
                                <td>
                                    @php
                                        $statusColors = [
                                            'draft' => 'secondary',
                                            'submitted' => 'primary',
                                            'under_review' => 'info',
                                            'approved' => 'success',
                                            'rejected' => 'danger',
                                            'revision_required' => 'warning',
                                        ];
                                        $color = $statusColors[$request->status] ?? 'secondary';
                                    @endphp
                                    <span class="badge bg-{{ $color }}">{{ ucfirst(str_replace('_', ' ', $request->status)) }}</span>
                                </td>
                                <td>{{ $request->items->count() }}</td>
                                <td>{{ number_format($request->total_estimated_amount, 2) }} TZS</td>
                                <td>
                                    @if($request->approved_amount)
                                        <span class="text-success fw-bold">{{ number_format($request->approved_amount, 2) }} TZS</span>
                                    @else
                                        <span class="text-muted">-</span>
                                    @endif
                                </td>
                                <td>{{ $request->submitted_at ? $request->submitted_at->format('M d, Y') : '-' }}</td>
                                <td>
                                    <div class="btn-group btn-group-sm">
                                        <!-- View Button - Always available -->
                                        <button wire:click="openViewModal('{{ $request->id }}')"
                                                class="btn btn-outline-info" title="View Details">
                                            <i class="fa-solid fa-eye"></i>
                                        </button>

                                        @if($request->canEdit())
                                            <button wire:click="openModal('edit', '{{ $request->id }}')"
                                                    class="btn btn-outline-primary" title="Edit">
                                                <i class="fa-solid fa-edit"></i>
                                            </button>
                                        @endif

                                        @if($request->canSubmit())
                                            <button wire:click="submit('{{ $request->id }}')"
                                                    class="btn btn-outline-success"
                                                    title="{{ $request->status === 'revision_required' ? 'Re-submit to Director' : 'Submit to Director' }}"
                                                    onclick="return confirm('{{ $request->status === 'revision_required' ? 'Re-submit this request to the Director?' : 'Submit this request to the Director for review?' }}')">
                                                <i class="fa-solid fa-paper-plane"></i>
                                                @if($request->status === 'revision_required')
                                                    <span class="d-none d-md-inline ms-1">Re-submit</span>
                                                @endif
                                            </button>
                                        @endif

                                        <!-- Print Button for Approved Requests -->
                                        @if($request->status === 'approved')
                                            <button wire:click="printRequest('{{ $request->id }}')"
                                                    class="btn btn-outline-secondary" title="Print">
                                                <i class="fa-solid fa-print"></i>
                                            </button>
                                        @endif

                                        @if($request->isDraft())
                                            <button wire:click="delete('{{ $request->id }}')"
                                                    class="btn btn-outline-danger" title="Delete"
                                                    onclick="return confirm('Are you sure you want to delete this request?')">
                                                <i class="fa-solid fa-trash"></i>
                                            </button>
                                        @endif

                                        @if($request->director_notes && in_array($request->status, ['revision_required', 'rejected']))
                                            <button type="button" class="btn btn-outline-warning"
                                                    title="View Director Notes"
                                                    data-bs-toggle="tooltip"
                                                    onclick="alert('Director Notes:\n\n{{ addslashes($request->director_notes) }}')">
                                                <i class="fa-solid fa-comment"></i>
                                            </button>
                                        @endif
                                    </div>
                                </td>
                            </tr>
                        @empty
                            <tr>
                                <td colspan="8" class="text-center py-4 text-muted">
                                    <i class="fa-solid fa-inbox fa-3x mb-3 d-block"></i>
                                    No budget requests found. Click "New Request" to create one.
                                </td>
                            </tr>
                        @endforelse
                    </tbody>
                </table>
            </div>
        </div>
        @if($requests->hasPages())
            <div class="card-footer bg-white">
                {{ $requests->links() }}
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
                            <i class="fa-solid fa-file-invoice-dollar me-2"></i>
                            {{ $modalMode === 'create' ? 'Create New Budget Request' : 'Edit Budget Request' }}
                        </h5>
                        <button type="button" class="btn-close btn-close-white" wire:click="$set('showModal', false)"></button>
                    </div>
                    <div class="modal-body">
                        <form wire:submit.prevent="save">
                            <!-- Financial Year -->
                            <div class="mb-3">
                                <label class="form-label">Financial Year <span class="text-danger">*</span></label>
                                <select class="form-select @error('financial_year_id') is-invalid @enderror"
                                        wire:model="financial_year_id" required>
                                    <option value="">Select Financial Year</option>
                                    @foreach($financialYears as $fy)
                                        <option value="{{ $fy->id }}">{{ $fy->name }}</option>
                                    @endforeach
                                </select>
                                @error('financial_year_id')
                                    <div class="invalid-feedback">{{ $message }}</div>
                                @enderror
                            </div>

                            <!-- Justification -->
                            <div class="mb-3">
                                <label class="form-label">Overall Justification</label>
                                <textarea class="form-control" wire:model="justification" rows="3"
                                          placeholder="Provide an overall justification for this budget request..."></textarea>
                            </div>

                            <!-- Items Section -->
                            <div class="card border-primary mb-3">
                                <div class="card-header bg-primary text-white d-flex justify-content-between align-items-center">
                                    <h6 class="mb-0">Budget Items</h6>
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
                                                        <th style="width: 20%">Item</th>
                                                        <th style="width: 15%">Category</th>
                                                        <th style="width: 10%">Quantity</th>
                                                        <th style="width: 15%">Price (TZS)</th>
                                                        <th style="width: 15%">Total</th>
                                                        <th style="width: 15%">Justification</th>
                                                        <th style="width: 5%"></th>
                                                    </tr>
                                                </thead>
                                                <tbody>
                                                    @foreach($selectedItems as $index => $item)
                                                        <tr>
                                                            <td>{{ $index + 1 }}</td>
                                                            <td>{{ $item['name'] }}</td>
                                                            <td>{{ $item['category_name'] }}</td>
                                                            <td>
                                                                <input type="number" class="form-control form-control-sm"
                                                                       wire:model.live="selectedItems.{{ $index }}.quantity"
                                                                       min="1" required>
                                                            </td>
                                                            <td>
                                                                <input type="number" class="form-control form-control-sm"
                                                                       wire:model.live="selectedItems.{{ $index }}.price"
                                                                       min="0" step="0.01">
                                                            </td>
                                                            <td class="fw-bold">
                                                                {{ number_format(($item['quantity'] ?? 0) * ($item['price'] ?? 0), 2) }}
                                                            </td>
                                                            <td>
                                                                <input type="text" class="form-control form-control-sm"
                                                                       wire:model="selectedItems.{{ $index }}.justification"
                                                                       placeholder="Why needed?">
                                                            </td>
                                                            <td>
                                                                <button type="button" class="btn btn-sm btn-outline-danger"
                                                                        wire:click="removeItem({{ $index }})" title="Remove">
                                                                    <i class="fa-solid fa-times"></i>
                                                                </button>
                                                            </td>
                                                        </tr>
                                                    @endforeach
                                                    <tr class="table-info fw-bold">
                                                        <td colspan="5" class="text-end">Total Estimated Amount:</td>
                                                        <td colspan="3">
                                                            {{ number_format(collect($selectedItems)->sum(function($item) {
                                                                return ($item['quantity'] ?? 0) * ($item['price'] ?? 0);
                                                            }), 2) }} TZS
                                                        </td>
                                                    </tr>
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
                            <i class="fa-solid fa-save me-2"></i>Save Request
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
                            <i class="fa-solid fa-search me-2"></i>Select Item
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
                                        wire:click="addItem('{{ $item->id }}', '{{ $item->name }}', '{{ $item->category_id }}', '{{ $item->category->name }}')"
                                        {{ $alreadySelected ? 'disabled' : '' }}>
                                    <div class="d-flex w-100 justify-content-between">
                                        <h6 class="mb-1">{{ $item->name }}</h6>
                                        @if($alreadySelected)
                                            <span class="badge bg-success">Added</span>
                                        @endif
                                    </div>
                                    <small class="text-muted">Category: {{ $item->category->name }}</small>
                                </button>
                            @empty
                                <div class="text-center py-4 text-muted">
                                    No items found matching your criteria.
                                </div>
                            @endforelse
                        </div>
                    </div>
                </div>
            </div>
        </div>
    @endif

    <!-- View/Detail Modal -->
    @if($showViewModal && $viewingRequest)
        <div class="modal fade show d-block" tabindex="-1" style="background-color: rgba(0,0,0,0.5);" id="viewModal">
            <div class="modal-dialog modal-xl modal-dialog-scrollable">
                <div class="modal-content">
                    <div class="modal-header bg-info text-white">
                        <h5 class="modal-title">
                            <i class="fa-solid fa-file-invoice me-2"></i>
                            Budget Request Details - {{ $viewingRequest->request_number }}
                        </h5>
                        <button type="button" class="btn-close btn-close-white" wire:click="closeViewModal"></button>
                    </div>
                    <div class="modal-body" id="printableArea">
                        <!-- Request Information -->
                        <div class="card border-info mb-4">
                            <div class="card-header bg-info text-white">
                                <h6 class="mb-0">Request Information</h6>
                            </div>
                            <div class="card-body">
                                <div class="row">
                                    <div class="col-md-6">
                                        <p><strong>Request Number:</strong> {{ $viewingRequest->request_number }}</p>
                                        <p><strong>Department:</strong> {{ $viewingRequest->department->name }}</p>
                                        <p><strong>Financial Year:</strong> {{ $viewingRequest->financialYear->name }}</p>
                                        <p><strong>Requested By:</strong> {{ $viewingRequest->requestedBy->name }}</p>
                                    </div>
                                    <div class="col-md-6">
                                        <p><strong>Status:</strong>
                                            @php
                                                $statusColors = [
                                                    'draft' => 'secondary',
                                                    'submitted' => 'primary',
                                                    'under_review' => 'info',
                                                    'approved' => 'success',
                                                    'rejected' => 'danger',
                                                    'revision_required' => 'warning',
                                                ];
                                                $color = $statusColors[$viewingRequest->status] ?? 'secondary';
                                            @endphp
                                            <span class="badge bg-{{ $color }}">{{ ucfirst(str_replace('_', ' ', $viewingRequest->status)) }}</span>
                                        </p>
                                        <p><strong>Submitted:</strong> {{ $viewingRequest->submitted_at ? $viewingRequest->submitted_at->format('M d, Y H:i') : 'Not submitted' }}</p>
                                        <p><strong>Reviewed:</strong> {{ $viewingRequest->reviewed_at ? $viewingRequest->reviewed_at->format('M d, Y H:i') : 'Not reviewed' }}</p>
                                        @if($viewingRequest->reviewedBy)
                                            <p><strong>Reviewed By:</strong> {{ $viewingRequest->reviewedBy->name }}</p>
                                        @endif
                                    </div>
                                </div>
                                @if($viewingRequest->justification)
                                    <div class="mt-3">
                                        <strong>Justification:</strong>
                                        <p class="border p-2 rounded bg-light mt-1">{{ $viewingRequest->justification }}</p>
                                    </div>
                                @endif
                                @if($viewingRequest->director_notes)
                                    <div class="mt-3">
                                        <strong>Director Notes:</strong>
                                        <p class="border p-2 rounded bg-warning bg-opacity-10 mt-1">{{ $viewingRequest->director_notes }}</p>
                                    </div>
                                @endif
                            </div>
                        </div>

                        <!-- Budget Items -->
                        <div class="card border-primary mb-4">
                            <div class="card-header bg-primary text-white">
                                <h6 class="mb-0">Budget Items</h6>
                            </div>
                            <div class="card-body p-0">
                                <div class="table-responsive">
                                    <table class="table table-sm mb-0">
                                        <thead class="table-light">
                                            <tr>
                                                <th style="width: 5%">#</th>
                                                <th style="width: 20%">Item</th>
                                                <th style="width: 15%">Category</th>
                                                <th style="width: 10%">Requested Qty</th>
                                                <th style="width: 12%">Requested Price</th>
                                                <th style="width: 12%">Requested Total</th>
                                                @if($viewingRequest->status === 'approved')
                                                    <th style="width: 10%">Approved Qty</th>
                                                    <th style="width: 12%">Approved Price</th>
                                                    <th style="width: 12%">Approved Total</th>
                                                @endif
                                                @if($viewingRequest->status === 'approved' || $viewingRequest->status === 'revision_required')
                                                    <th>Director Comment</th>
                                                @endif
                                            </tr>
                                        </thead>
                                        <tbody>
                                            @foreach($viewingRequest->items as $index => $item)
                                                <tr>
                                                    <td>{{ $index + 1 }}</td>
                                                    <td><strong>{{ $item->item->name }}</strong></td>
                                                    <td><small class="text-muted">{{ $item->category->name }}</small></td>
                                                    <td class="text-center">{{ $item->requested_quantity }}</td>
                                                    <td>{{ number_format($item->requested_price, 2) }}</td>
                                                    <td class="fw-bold">{{ number_format($item->requested_quantity * $item->requested_price, 2) }}</td>
                                                    @if($viewingRequest->status === 'approved')
                                                        <td class="text-center">{{ $item->approved_quantity ?? '-' }}</td>
                                                        <td>{{ $item->approved_price ? number_format($item->approved_price, 2) : '-' }}</td>
                                                        <td class="fw-bold text-success">
                                                            {{ $item->approved_quantity && $item->approved_price ? number_format($item->approved_quantity * $item->approved_price, 2) : '-' }}
                                                        </td>
                                                    @endif
                                                    @if($viewingRequest->status === 'approved' || $viewingRequest->status === 'revision_required')
                                                        <td><small>{{ $item->director_comment ?? '-' }}</small></td>
                                                    @endif
                                                </tr>
                                            @endforeach
                                            <tr class="table-info fw-bold">
                                                <td colspan="5" class="text-end">Total Requested Amount:</td>
                                                <td>{{ number_format($viewingRequest->total_estimated_amount, 2) }} TZS</td>
                                                @if($viewingRequest->status === 'approved')
                                                    <td colspan="2" class="text-end">Total Approved Amount:</td>
                                                    <td>{{ number_format($viewingRequest->approved_amount ?? 0, 2) }} TZS</td>
                                                @endif
                                            </tr>
                                        </tbody>
                                    </table>
                                </div>
                            </div>
                        </div>
                    </div>
                    <div class="modal-footer no-print">
                        @if($viewingRequest->status === 'approved')
                            <button type="button" class="btn btn-secondary" onclick="printBudgetRequest()">
                                <i class="fa-solid fa-print me-2"></i>Print
                            </button>
                        @endif
                        <button type="button" class="btn btn-primary" wire:click="closeViewModal">Close</button>
                    </div>
                </div>
            </div>
        </div>
    @endif

    <!-- Print Styles and Scripts (Always loaded) -->
    <style>
        @media print {
            .no-print, .modal-header, .modal-footer, nav, .breadcrumb, button, .alert {
                display: none !important;
            }
            .modal {
                position: static !important;
                display: block !important;
                background: white !important;
            }
            .modal-dialog {
                max-width: 100% !important;
                margin: 0 !important;
            }
            .modal-content {
                border: none !important;
                box-shadow: none !important;
            }
            .modal-backdrop {
                display: none !important;
            }
            body {
                background: white !important;
            }
            .card {
                page-break-inside: avoid;
                border: 1px solid #ddd !important;
            }
            .card-header {
                background: #f8f9fa !important;
                color: #000 !important;
                -webkit-print-color-adjust: exact;
                print-color-adjust: exact;
            }
        }
    </style>

    <script>
        function printBudgetRequest() {
            window.print();
        }
    </script>
</div>
