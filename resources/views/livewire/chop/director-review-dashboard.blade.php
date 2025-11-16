<div>
    <!-- Breadcrumb -->
    <nav aria-label="breadcrumb">
        <ol class="breadcrumb">
            <li class="breadcrumb-item"><a href="{{ route('dashboard') }}">Dashboard</a></li>
            <li class="breadcrumb-item"><a href="{{ route('chop.settings') }}">CHOP</a></li>
            <li class="breadcrumb-item active" aria-current="page">Review Budget Requests</li>
        </ol>
    </nav>

    <!-- Page Header -->
    <div class="mb-4">
        <h2 class="mb-1">Budget Request Review Dashboard</h2>
        <p class="text-muted">Review and approve department budget requests</p>
    </div>

    <!-- Flash Messages -->
    @if (session()->has('success'))
        <div class="alert alert-success alert-dismissible fade show" role="alert">
            <i class="fa-solid fa-circle-check me-2"></i>{{ session('success') }}
            <button type="button" class="btn-close" data-bs-dismiss="alert"></button>
        </div>
    @endif

    <!-- Statistics Cards -->
    <div class="row g-3 mb-4">
        <div class="col-md-3">
            <div class="card shadow-sm border-primary">
                <div class="card-body">
                    <div class="d-flex justify-content-between align-items-center">
                        <div>
                            <h6 class="text-muted mb-1">Pending Review</h6>
                            <h3 class="mb-0">{{ $stats['pending'] }}</h3>
                        </div>
                        <div class="bg-primary bg-opacity-10 p-3 rounded">
                            <i class="fa-solid fa-clock fa-2x text-primary"></i>
                        </div>
                    </div>
                </div>
            </div>
        </div>
        <div class="col-md-3">
            <div class="card shadow-sm border-success">
                <div class="card-body">
                    <div class="d-flex justify-content-between align-items-center">
                        <div>
                            <h6 class="text-muted mb-1">Approved</h6>
                            <h3 class="mb-0">{{ $stats['approved'] }}</h3>
                        </div>
                        <div class="bg-success bg-opacity-10 p-3 rounded">
                            <i class="fa-solid fa-check-circle fa-2x text-success"></i>
                        </div>
                    </div>
                </div>
            </div>
        </div>
        <div class="col-md-3">
            <div class="card shadow-sm border-danger">
                <div class="card-body">
                    <div class="d-flex justify-content-between align-items-center">
                        <div>
                            <h6 class="text-muted mb-1">Rejected</h6>
                            <h3 class="mb-0">{{ $stats['rejected'] }}</h3>
                        </div>
                        <div class="bg-danger bg-opacity-10 p-3 rounded">
                            <i class="fa-solid fa-times-circle fa-2x text-danger"></i>
                        </div>
                    </div>
                </div>
            </div>
        </div>
        <div class="col-md-3">
            <div class="card shadow-sm border-info">
                <div class="card-body">
                    <div class="d-flex justify-content-between align-items-center">
                        <div>
                            <h6 class="text-muted mb-1">Total Requested</h6>
                            <h3 class="mb-0">{{ number_format($stats['total_requested'], 0) }}</h3>
                            <small class="text-muted">TZS</small>
                        </div>
                        <div class="bg-info bg-opacity-10 p-3 rounded">
                            <i class="fa-solid fa-money-bill-wave fa-2x text-info"></i>
                        </div>
                    </div>
                </div>
            </div>
        </div>
    </div>

    <!-- Filters -->
    <div class="card shadow-sm mb-4">
        <div class="card-body">
            <div class="row g-3">
                <div class="col-md-4">
                    <input type="text" wire:model.live="search" class="form-control"
                           placeholder="Search by request number, department, or requester...">
                </div>
                <div class="col-md-3">
                    <select class="form-select" wire:model.live="filterStatus">
                        <option value="">All Status</option>
                        @foreach($statuses as $key => $label)
                            <option value="{{ $key }}">{{ $label }}</option>
                        @endforeach
                    </select>
                </div>
                <div class="col-md-3">
                    <select class="form-select" wire:model.live="filterDepartment">
                        <option value="">All Departments</option>
                        @foreach($departments as $dept)
                            <option value="{{ $dept->id }}">{{ $dept->name }}</option>
                        @endforeach
                    </select>
                </div>
                <div class="col-md-2">
                    <select class="form-select" wire:model.live="filterFinancialYear">
                        <option value="">All Years</option>
                        @foreach($financialYears as $fy)
                            <option value="{{ $fy->id }}">{{ $fy->name }}</option>
                        @endforeach
                    </select>
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
                            <th>Request #</th>
                            <th>Department</th>
                            <th>Requested By</th>
                            <th>Financial Year</th>
                            <th>Status</th>
                            <th>Items</th>
                            <th>Requested Amount</th>
                            <th>Approved Amount</th>
                            <th>Submitted</th>
                            <th>Actions</th>
                        </tr>
                    </thead>
                    <tbody>
                        @forelse ($requests as $request)
                            <tr>
                                <td class="fw-bold">{{ $request->request_number }}</td>
                                <td>{{ $request->department->name }}</td>
                                <td>{{ $request->requestedBy->name }}</td>
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

                                        @if($request->status === 'submitted')
                                            <button wire:click="setUnderReview('{{ $request->id }}')"
                                                    class="btn btn-outline-secondary" title="Mark as Under Review">
                                                <i class="fa-solid fa-tasks"></i>
                                            </button>
                                        @endif

                                        @if(in_array($request->status, ['submitted', 'under_review']))
                                            <button wire:click="openReviewModal('{{ $request->id }}')"
                                                    class="btn btn-outline-primary" title="Review & Approve">
                                                <i class="fa-solid fa-clipboard-check"></i>
                                            </button>
                                        @endif

                                        @if($request->status === 'approved')
                                            <button onclick="document.getElementById('view-modal-{{ $request->id }}') ? printBudgetRequest() : null"
                                                    wire:click="openViewModal('{{ $request->id }}')"
                                                    class="btn btn-outline-success" title="Print Approved Budget">
                                                <i class="fa-solid fa-print"></i>
                                            </button>
                                        @endif
                                    </div>
                                </td>
                            </tr>
                        @empty
                            <tr>
                                <td colspan="10" class="text-center py-4 text-muted">
                                    <i class="fa-solid fa-inbox fa-3x mb-3 d-block"></i>
                                    No budget requests found.
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

    <!-- Review Modal -->
    @if($showReviewModal && $reviewingRequest)
        <div class="modal fade show d-block" tabindex="-1" style="background-color: rgba(0,0,0,0.5);">
            <div class="modal-dialog modal-xl modal-dialog-scrollable">
                <div class="modal-content">
                    <div class="modal-header bg-primary text-white">
                        <h5 class="modal-title">
                            <i class="fa-solid fa-clipboard-check me-2"></i>
                            Review Budget Request - {{ $reviewingRequest->request_number }}
                        </h5>
                        <button type="button" class="btn-close btn-close-white" wire:click="closeReviewModal"></button>
                    </div>
                    <div class="modal-body">
                        <!-- Request Information -->
                        <div class="card border-info mb-4">
                            <div class="card-header bg-info text-white">
                                <h6 class="mb-0">Request Information</h6>
                            </div>
                            <div class="card-body">
                                <div class="row">
                                    <div class="col-md-6">
                                        <p><strong>Department:</strong> {{ $reviewingRequest->department->name }}</p>
                                        <p><strong>Requested By:</strong> {{ $reviewingRequest->requestedBy->name }}</p>
                                        <p><strong>Financial Year:</strong> {{ $reviewingRequest->financialYear->name }}</p>
                                    </div>
                                    <div class="col-md-6">
                                        <p><strong>Status:</strong>
                                            @php
                                                $color = $statusColors[$reviewingRequest->status] ?? 'secondary';
                                            @endphp
                                            <span class="badge bg-{{ $color }}">{{ ucfirst(str_replace('_', ' ', $reviewingRequest->status)) }}</span>
                                        </p>
                                        <p><strong>Submitted:</strong> {{ $reviewingRequest->submitted_at ? $reviewingRequest->submitted_at->format('M d, Y H:i') : '-' }}</p>
                                        <p><strong>Total Requested:</strong> <span class="text-primary fw-bold">{{ number_format($reviewingRequest->total_estimated_amount, 2) }} TZS</span></p>
                                    </div>
                                </div>
                                @if($reviewingRequest->justification)
                                    <div class="mt-3">
                                        <strong>HoD Justification:</strong>
                                        <p class="border p-2 rounded bg-light mt-1">{{ $reviewingRequest->justification }}</p>
                                    </div>
                                @endif
                            </div>
                        </div>

                        <!-- Items Review -->
                        <div class="card border-primary mb-4">
                            <div class="card-header bg-primary text-white">
                                <h6 class="mb-0">Budget Items Review</h6>
                            </div>
                            <div class="card-body p-0">
                                <div class="table-responsive">
                                    <table class="table table-sm mb-0">
                                        <thead class="table-light">
                                            <tr>
                                                <th style="width: 3%">#</th>
                                                <th style="width: 15%">Item</th>
                                                <th style="width: 10%">Category</th>
                                                <th style="width: 8%">Req. Qty</th>
                                                <th style="width: 10%">Req. Price</th>
                                                <th style="width: 10%">Req. Total</th>
                                                <th style="width: 8%">App. Qty</th>
                                                <th style="width: 10%">App. Price</th>
                                                <th style="width: 10%">App. Total</th>
                                                <th style="width: 16%">Director Comment</th>
                                            </tr>
                                        </thead>
                                        <tbody>
                                            @foreach($itemModifications as $index => $item)
                                                <tr>
                                                    <td>{{ $index + 1 }}</td>
                                                    <td>
                                                        <strong>{{ $item['item_name'] }}</strong>
                                                    </td>
                                                    <td><small class="text-muted">{{ $item['category_name'] }}</small></td>
                                                    <td class="text-center">{{ $item['requested_quantity'] }}</td>
                                                    <td>{{ number_format($item['requested_price'], 2) }}</td>
                                                    <td class="fw-bold">{{ number_format($item['requested_quantity'] * $item['requested_price'], 2) }}</td>
                                                    <td>
                                                        <input type="number" class="form-control form-control-sm"
                                                               wire:model.live="itemModifications.{{ $index }}.approved_quantity"
                                                               min="0">
                                                    </td>
                                                    <td>
                                                        <input type="number" class="form-control form-control-sm"
                                                               wire:model.live="itemModifications.{{ $index }}.approved_price"
                                                               min="0" step="0.01">
                                                    </td>
                                                    <td class="fw-bold text-success">
                                                        {{ number_format(($item['approved_quantity'] ?? 0) * ($item['approved_price'] ?? 0), 2) }}
                                                    </td>
                                                    <td>
                                                        <input type="text" class="form-control form-control-sm"
                                                               wire:model="itemModifications.{{ $index }}.director_comment"
                                                               placeholder="Optional comment...">
                                                    </td>
                                                </tr>
                                            @endforeach
                                            <tr class="table-info fw-bold">
                                                <td colspan="5" class="text-end">Total Requested Amount:</td>
                                                <td>
                                                    {{ number_format(collect($itemModifications)->sum(function($item) {
                                                        return $item['requested_quantity'] * $item['requested_price'];
                                                    }), 2) }} TZS
                                                </td>
                                                <td colspan="2" class="text-end">Total Approved Amount:</td>
                                                <td colspan="2">
                                                    {{ number_format(collect($itemModifications)->sum(function($item) {
                                                        return ($item['approved_quantity'] ?? 0) * ($item['approved_price'] ?? 0);
                                                    }), 2) }} TZS
                                                </td>
                                            </tr>
                                        </tbody>
                                    </table>
                                </div>
                            </div>
                        </div>

                        <!-- Director Notes -->
                        <div class="mb-3">
                            <label class="form-label fw-bold">Director Notes</label>
                            <textarea class="form-control" wire:model="director_notes" rows="3"
                                      placeholder="Enter your notes or feedback about this request..."></textarea>
                        </div>
                    </div>
                    <div class="modal-footer bg-light">
                        <button type="button" class="btn btn-secondary" wire:click="closeReviewModal">
                            <i class="fa-solid fa-times me-2"></i>Cancel
                        </button>
                        <button type="button" class="btn btn-warning" wire:click="requestRevision"
                                onclick="return confirm('Request revision from HoD?')">
                            <i class="fa-solid fa-edit me-2"></i>Request Revision
                        </button>
                        <button type="button" class="btn btn-danger" wire:click="reject"
                                onclick="return confirm('Are you sure you want to reject this request?')">
                            <i class="fa-solid fa-times-circle me-2"></i>Reject
                        </button>
                        <button type="button" class="btn btn-success" wire:click="approve"
                                onclick="return confirm('Are you sure you want to approve this request?')">
                            <i class="fa-solid fa-check-circle me-2"></i>Approve
                        </button>
                    </div>
                </div>
            </div>
        </div>
    @endif

    <!-- View/Detail Modal -->
    @if($showViewModal && $viewingRequest)
        <div class="modal fade show d-block" tabindex="-1" style="background-color: rgba(0,0,0,0.5);">
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
                                        <strong>HoD Justification:</strong>
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
                                                <th style="width: 3%">#</th>
                                                <th style="width: 15%">Item</th>
                                                <th style="width: 10%">Category</th>
                                                <th style="width: 8%">Req. Qty</th>
                                                <th style="width: 10%">Req. Price</th>
                                                <th style="width: 10%">Req. Total</th>
                                                @if(in_array($viewingRequest->status, ['approved', 'rejected']))
                                                    <th style="width: 8%">App. Qty</th>
                                                    <th style="width: 10%">App. Price</th>
                                                    <th style="width: 10%">App. Total</th>
                                                    <th style="width: 16%">Comment</th>
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
                                                    @if(in_array($viewingRequest->status, ['approved', 'rejected']))
                                                        <td class="text-center">{{ $item->approved_quantity ?? '-' }}</td>
                                                        <td>{{ $item->approved_price ? number_format($item->approved_price, 2) : '-' }}</td>
                                                        <td class="fw-bold text-success">
                                                            {{ $item->approved_quantity && $item->approved_price ? number_format($item->approved_quantity * $item->approved_price, 2) : '-' }}
                                                        </td>
                                                        <td><small>{{ $item->director_comment ?? '-' }}</small></td>
                                                    @endif
                                                </tr>
                                            @endforeach
                                            <tr class="table-info fw-bold">
                                                <td colspan="5" class="text-end">Total Requested Amount:</td>
                                                <td>{{ number_format($viewingRequest->total_estimated_amount, 2) }} TZS</td>
                                                @if(in_array($viewingRequest->status, ['approved', 'rejected']))
                                                    <td colspan="2" class="text-end">Total Approved Amount:</td>
                                                    <td colspan="2">{{ number_format($viewingRequest->approved_amount ?? 0, 2) }} TZS</td>
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
            .no-print, .modal-header, .modal-footer, nav, .breadcrumb, button, .alert, .card:not(#printableArea .card) {
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
