<div>
    <x-pages.breadcrumn title="Contract Request Management"
        :breadcrumbs="[
            ['label' => 'Home', 'url' => route('dashboard')],
            ['label' => 'Human Resources'],
            ['label' => 'Contract Requests']
        ]">
    </x-pages.breadcrumn>

    <!-- Statistics Cards -->
    <div class="row g-3 mb-4">
        <div class="col-md-4">
            <div class="card border-0 shadow-sm bg-warning bg-opacity-10">
                <div class="card-body">
                    <div class="d-flex justify-content-between align-items-center">
                        <div>
                            <h6 class="text-warning mb-1">Pending Requests</h6>
                            <h2 class="mb-0 fw-bold">{{ $pendingCount }}</h2>
                        </div>
                        <div class="bg-warning bg-opacity-25 rounded-circle p-3">
                            <i class="fa-solid fa-clock fa-2x text-warning"></i>
                        </div>
                    </div>
                </div>
            </div>
        </div>
        <div class="col-md-4">
            <div class="card border-0 shadow-sm bg-success bg-opacity-10">
                <div class="card-body">
                    <div class="d-flex justify-content-between align-items-center">
                        <div>
                            <h6 class="text-success mb-1">Approved (This Month)</h6>
                            <h2 class="mb-0 fw-bold">{{ $approvedCount }}</h2>
                        </div>
                        <div class="bg-success bg-opacity-25 rounded-circle p-3">
                            <i class="fa-solid fa-check-circle fa-2x text-success"></i>
                        </div>
                    </div>
                </div>
            </div>
        </div>
        <div class="col-md-4">
            <div class="card border-0 shadow-sm bg-danger bg-opacity-10">
                <div class="card-body">
                    <div class="d-flex justify-content-between align-items-center">
                        <div>
                            <h6 class="text-danger mb-1">Rejected (This Month)</h6>
                            <h2 class="mb-0 fw-bold">{{ $rejectedCount }}</h2>
                        </div>
                        <div class="bg-danger bg-opacity-25 rounded-circle p-3">
                            <i class="fa-solid fa-times-circle fa-2x text-danger"></i>
                        </div>
                    </div>
                </div>
            </div>
        </div>
    </div>

    <!-- Filters -->
    <div class="card border-0 shadow-sm mb-4">
        <div class="card-body">
            <div class="row g-3 align-items-end">
                <div class="col-md-4">
                    <label class="form-label">Search Employee</label>
                    <div class="input-group">
                        <span class="input-group-text"><i class="fa-solid fa-search"></i></span>
                        <input type="search" class="form-control" placeholder="Search by name..."
                               wire:model.live.debounce.300ms="search">
                    </div>
                </div>
                <div class="col-md-3">
                    <label class="form-label">Status</label>
                    <select class="form-select" wire:model.live="filterStatus">
                        <option value="">All Status</option>
                        <option value="pending">Pending</option>
                        <option value="approved">Approved</option>
                        <option value="rejected">Rejected</option>
                        <option value="cancelled">Cancelled</option>
                    </select>
                </div>
                <div class="col-md-3">
                    <label class="form-label">Request Type</label>
                    <select class="form-select" wire:model.live="filterType">
                        <option value="">All Types</option>
                        <option value="renewal">Renewal</option>
                        <option value="extension">Extension</option>
                        <option value="termination">Termination</option>
                    </select>
                </div>
                <div class="col-md-2">
                    <button class="btn btn-outline-secondary w-100" wire:click="$set('filterStatus', 'pending')">
                        <i class="fa-solid fa-refresh me-1"></i> Reset
                    </button>
                </div>
            </div>
        </div>
    </div>

    <!-- Requests Table -->
    <div class="card border-0 shadow-sm">
        <div class="card-header bg-transparent">
            <h5 class="mb-0"><i class="fa-solid fa-file-contract me-2"></i>Contract Requests</h5>
        </div>
        <div class="card-body p-0">
            <div class="table-responsive">
                <table class="table table-hover mb-0">
                    <thead class="table-light">
                        <tr>
                            <th>Employee</th>
                            <th>Request Type</th>
                            <th>Current Contract</th>
                            <th>Proposed/Details</th>
                            <th>Status</th>
                            <th>Submitted</th>
                            <th>Actions</th>
                        </tr>
                    </thead>
                    <tbody>
                        @forelse($requests as $request)
                            <tr class="{{ $request->isPending() ? 'table-warning' : '' }}">
                                <td>
                                    <div class="d-flex align-items-center">
                                        <div class="bg-primary bg-opacity-10 rounded-circle d-flex align-items-center justify-content-center me-2" style="width: 40px; height: 40px;">
                                            <span class="text-primary fw-bold">{{ substr($request->employee?->first_name ?? 'U', 0, 1) }}</span>
                                        </div>
                                        <div>
                                            <div class="fw-medium">{{ $request->employee?->getFullName() ?? 'Unknown' }}</div>
                                            <small class="text-muted">{{ $request->employee?->department?->name ?? '-' }}</small>
                                        </div>
                                    </div>
                                </td>
                                <td>
                                    <span class="badge {{ $request->getTypeBadgeClass() }} fs-6">
                                        @if($request->request_type === 'renewal')
                                            <i class="fa-solid fa-rotate me-1"></i>
                                        @elseif($request->request_type === 'extension')
                                            <i class="fa-solid fa-calendar-plus me-1"></i>
                                        @else
                                            <i class="fa-solid fa-door-open me-1"></i>
                                        @endif
                                        {{ $request->getTypeLabel() }}
                                    </span>
                                </td>
                                <td>
                                    @if($request->contract)
                                        <div>{{ $request->contract->start_date->format('d/m/Y') }}</div>
                                        <div class="text-muted">to {{ $request->contract->expire_date->format('d/m/Y') }}</div>
                                    @else
                                        -
                                    @endif
                                </td>
                                <td>
                                    @if($request->request_type === 'termination')
                                        <div class="text-danger">
                                            <i class="fa-solid fa-calendar-xmark me-1"></i>
                                            Last Day: {{ $request->last_working_day?->format('d/m/Y') ?? '-' }}
                                        </div>
                                    @elseif($request->extension_period)
                                        <div class="text-info">
                                            <i class="fa-solid fa-calendar-plus me-1"></i>
                                            +{{ $request->extension_period }} months
                                        </div>
                                    @elseif($request->proposed_start_date && $request->proposed_end_date)
                                        <div>{{ $request->proposed_start_date->format('d/m/Y') }}</div>
                                        <div class="text-muted">to {{ $request->proposed_end_date->format('d/m/Y') }}</div>
                                    @else
                                        -
                                    @endif
                                </td>
                                <td>
                                    <span class="badge {{ $request->getStatusBadgeClass() }}">
                                        {{ ucfirst($request->status) }}
                                    </span>
                                    @if($request->reviewed_at)
                                        <div class="small text-muted mt-1">
                                            {{ $request->reviewed_at->format('d/m/Y') }}
                                        </div>
                                    @endif
                                </td>
                                <td>
                                    <div>{{ $request->created_at->format('d/m/Y') }}</div>
                                    <small class="text-muted">{{ $request->created_at->format('H:i') }}</small>
                                </td>
                                <td>
                                    @if($request->isPending())
                                        <button class="btn btn-sm btn-primary" wire:click="openReviewModal('{{ $request->id }}')" title="Review Request">
                                            <i class="fa-solid fa-eye me-1"></i> Review
                                        </button>
                                    @else
                                        <button class="btn btn-sm btn-outline-secondary" wire:click="openReviewModal('{{ $request->id }}')" title="View Details">
                                            <i class="fa-solid fa-eye"></i>
                                        </button>
                                    @endif
                                </td>
                            </tr>
                        @empty
                            <tr>
                                <td colspan="7" class="text-center py-5">
                                    <div class="text-muted">
                                        <i class="fa-solid fa-inbox fa-3x mb-3"></i>
                                        <p class="mb-0">No contract requests found</p>
                                    </div>
                                </td>
                            </tr>
                        @endforelse
                    </tbody>
                </table>
            </div>
        </div>
        @if($requests->hasPages())
            <div class="card-footer">
                {{ $requests->links() }}
            </div>
        @endif
    </div>

    <!-- Review Modal -->
    @if($showReviewModal && $reviewingRequest)
        <div class="modal fade show d-block" tabindex="-1" style="background-color: rgba(0,0,0,0.5);">
            <div class="modal-dialog modal-xl">
                <div class="modal-content">
                    <div class="modal-header bg-{{ $reviewingRequest->request_type === 'termination' ? 'danger' : ($reviewingRequest->request_type === 'extension' ? 'info' : 'primary') }} text-white">
                        <h5 class="modal-title">
                            <i class="fa-solid fa-file-contract me-2"></i>
                            Review {{ ucfirst($reviewingRequest->request_type) }} Request
                        </h5>
                        <button type="button" class="btn-close btn-close-white" wire:click="closeReviewModal"></button>
                    </div>
                    <div class="modal-body">
                        <div class="row">
                            <!-- Left Column: Request Details -->
                            <div class="col-lg-6">
                                <h6 class="text-primary mb-3"><i class="fa-solid fa-user me-2"></i>Employee Information</h6>
                                <div class="bg-light rounded p-3 mb-4">
                                    <div class="row g-2">
                                        <div class="col-6">
                                            <p class="text-muted mb-1 small">Employee Name</p>
                                            <p class="fw-medium mb-0">{{ $reviewingRequest->employee?->getFullName() ?? 'Unknown' }}</p>
                                        </div>
                                        <div class="col-6">
                                            <p class="text-muted mb-1 small">Department</p>
                                            <p class="fw-medium mb-0">{{ $reviewingRequest->employee?->department?->name ?? '-' }}</p>
                                        </div>
                                        <div class="col-6">
                                            <p class="text-muted mb-1 small">Position</p>
                                            <p class="fw-medium mb-0">{{ $reviewingRequest->contract?->position?->name ?? '-' }}</p>
                                        </div>
                                        <div class="col-6">
                                            <p class="text-muted mb-1 small">Employee Status</p>
                                            <p class="fw-medium mb-0">
                                                <span class="badge bg-{{ $reviewingRequest->employee?->status === 'active' ? 'success' : 'secondary' }}">
                                                    {{ ucfirst($reviewingRequest->employee?->status ?? 'unknown') }}
                                                </span>
                                            </p>
                                        </div>
                                    </div>
                                </div>

                                <h6 class="text-primary mb-3"><i class="fa-solid fa-file-signature me-2"></i>Current Contract</h6>
                                <div class="bg-light rounded p-3 mb-4">
                                    <div class="row g-2">
                                        <div class="col-6">
                                            <p class="text-muted mb-1 small">Contract Type</p>
                                            <p class="fw-medium mb-0">{{ ucfirst(str_replace('_', ' ', $reviewingRequest->contract?->contract_type ?? '-')) }}</p>
                                        </div>
                                        <div class="col-6">
                                            <p class="text-muted mb-1 small">Contract Status</p>
                                            <span class="badge {{ $reviewingRequest->contract?->getStatusBadgeClass() ?? 'bg-secondary' }}">
                                                {{ $reviewingRequest->contract?->getStatusLabel() ?? '-' }}
                                            </span>
                                        </div>
                                        <div class="col-6">
                                            <p class="text-muted mb-1 small">Start Date</p>
                                            <p class="fw-medium mb-0">{{ $reviewingRequest->contract?->start_date?->format('d M Y') ?? '-' }}</p>
                                        </div>
                                        <div class="col-6">
                                            <p class="text-muted mb-1 small">End Date</p>
                                            <p class="fw-medium mb-0">{{ $reviewingRequest->contract?->expire_date?->format('d M Y') ?? '-' }}</p>
                                        </div>
                                    </div>
                                </div>
                            </div>

                            <!-- Right Column: Request Details -->
                            <div class="col-lg-6">
                                <h6 class="text-primary mb-3"><i class="fa-solid fa-clipboard-list me-2"></i>Request Details</h6>
                                <div class="bg-light rounded p-3 mb-4">
                                    <div class="row g-2">
                                        <div class="col-6">
                                            <p class="text-muted mb-1 small">Request Type</p>
                                            <span class="badge {{ $reviewingRequest->getTypeBadgeClass() }} fs-6">
                                                {{ $reviewingRequest->getTypeLabel() }}
                                            </span>
                                        </div>
                                        <div class="col-6">
                                            <p class="text-muted mb-1 small">Current Status</p>
                                            <span class="badge {{ $reviewingRequest->getStatusBadgeClass() }} fs-6">
                                                {{ ucfirst($reviewingRequest->status) }}
                                            </span>
                                        </div>
                                        <div class="col-12"><hr class="my-2"></div>
                                        @if($reviewingRequest->request_type === 'termination')
                                            @if($reviewingRequest->terminationReason)
                                                <div class="col-12">
                                                    <p class="text-muted mb-1 small">Termination Reason</p>
                                                    <p class="fw-medium mb-0">
                                                        <span class="badge bg-danger">{{ $reviewingRequest->terminationReason->name }}</span>
                                                        @if($reviewingRequest->terminationReason->description)
                                                            <small class="text-muted d-block mt-1">{{ $reviewingRequest->terminationReason->description }}</small>
                                                        @endif
                                                    </p>
                                                </div>
                                            @endif
                                            <div class="col-12">
                                                <p class="text-muted mb-1 small">Requested Last Working Day</p>
                                                <p class="fw-medium mb-0 text-danger">
                                                    <i class="fa-solid fa-calendar-xmark me-1"></i>
                                                    {{ $reviewingRequest->last_working_day?->format('d M Y') ?? '-' }}
                                                </p>
                                            </div>
                                            @if($reviewingRequest->isEarlyTermination())
                                                <div class="col-12 mt-2">
                                                    <div class="alert alert-warning py-2 mb-0">
                                                        <i class="fa-solid fa-exclamation-triangle me-1"></i>
                                                        <strong>Early Termination:</strong> Contract not in notification period (>90 days until expiry).
                                                        Confirmation letter is required.
                                                    </div>
                                                </div>
                                            @endif
                                            @if($reviewingRequest->employee_attachment)
                                                <div class="col-12 mt-2">
                                                    <p class="text-muted mb-1 small">Employee's Termination Letter</p>
                                                    <a href="{{ Storage::url($reviewingRequest->employee_attachment) }}" target="_blank" class="btn btn-outline-primary btn-sm">
                                                        <i class="fa-solid fa-paperclip me-1"></i> View Attachment
                                                    </a>
                                                </div>
                                            @endif
                                            @if($reviewingRequest->handover_notes)
                                                <div class="col-12 mt-2">
                                                    <p class="text-muted mb-1 small">Handover Notes</p>
                                                    <div class="border rounded p-2 bg-white">{{ $reviewingRequest->handover_notes }}</div>
                                                </div>
                                            @endif
                                        @else
                                            <div class="col-6">
                                                <p class="text-muted mb-1 small">Proposed Start Date</p>
                                                <p class="fw-medium mb-0">{{ $reviewingRequest->proposed_start_date?->format('d M Y') ?? '-' }}</p>
                                            </div>
                                            <div class="col-6">
                                                <p class="text-muted mb-1 small">Proposed End Date</p>
                                                <p class="fw-medium mb-0">{{ $reviewingRequest->proposed_end_date?->format('d M Y') ?? '-' }}</p>
                                            </div>
                                            @if($reviewingRequest->extension_period)
                                                <div class="col-12">
                                                    <p class="text-muted mb-1 small">Extension Period</p>
                                                    <p class="fw-medium mb-0 text-info">+{{ $reviewingRequest->extension_period }} months</p>
                                                </div>
                                            @endif
                                        @endif
                                    </div>
                                </div>

                                <h6 class="text-primary mb-3"><i class="fa-solid fa-comment me-2"></i>Employee's Reason</h6>
                                <div class="bg-light rounded p-3 mb-4">
                                    <p class="mb-0">{{ $reviewingRequest->reason ?? 'No reason provided' }}</p>
                                </div>

                                <p class="text-muted small mb-2">
                                    <i class="fa-solid fa-clock me-1"></i>
                                    Submitted: {{ $reviewingRequest->created_at->format('d M Y \a\t H:i') }}
                                </p>
                            </div>
                        </div>

                        @if($reviewingRequest->isPending())
                            <hr>
                            <h6 class="text-primary mb-3"><i class="fa-solid fa-gavel me-2"></i>HR Decision</h6>
                            <div class="mb-3">
                                <label class="form-label">Comments / Notes</label>
                                <textarea class="form-control @error('review_comments') is-invalid @enderror"
                                          wire:model="review_comments" rows="3"
                                          placeholder="Add any comments or notes for this decision..."></textarea>
                                @error('review_comments') <div class="invalid-feedback">{{ $message }}</div> @enderror
                            </div>

                            @if($reviewingRequest->request_type === 'termination')
                                <div class="alert alert-danger">
                                    <i class="fa-solid fa-exclamation-triangle me-2"></i>
                                    <strong>Warning:</strong> Approving this termination request will:
                                    <ul class="mb-0 mt-2">
                                        <li>Update the employee's status to <strong>Terminated</strong></li>
                                        <li>Terminate the current employment contract</li>
                                    </ul>
                                </div>
                                @if($reviewingRequest->isEarlyTermination())
                                    <div class="mb-3">
                                        <label class="form-label">
                                            HR Confirmation Letter <span class="text-danger">*</span>
                                            <small class="text-muted">(Required for early termination)</small>
                                        </label>
                                        <input type="file" class="form-control @error('hr_confirmation_letter') is-invalid @enderror"
                                               wire:model="hr_confirmation_letter"
                                               accept=".pdf,.doc,.docx">
                                        @error('hr_confirmation_letter') <div class="invalid-feedback">{{ $message }}</div> @enderror
                                        <small class="text-muted">Accepted formats: PDF, DOC, DOCX (Max: 5MB)</small>
                                        <div wire:loading wire:target="hr_confirmation_letter" class="text-primary mt-1">
                                            <span class="spinner-border spinner-border-sm"></span> Uploading...
                                        </div>
                                    </div>
                                @else
                                    <div class="mb-3">
                                        <label class="form-label">
                                            HR Confirmation Letter <small class="text-muted">(Optional)</small>
                                        </label>
                                        <input type="file" class="form-control @error('hr_confirmation_letter') is-invalid @enderror"
                                               wire:model="hr_confirmation_letter"
                                               accept=".pdf,.doc,.docx">
                                        @error('hr_confirmation_letter') <div class="invalid-feedback">{{ $message }}</div> @enderror
                                        <small class="text-muted">Accepted formats: PDF, DOC, DOCX (Max: 5MB)</small>
                                        <div wire:loading wire:target="hr_confirmation_letter" class="text-primary mt-1">
                                            <span class="spinner-border spinner-border-sm"></span> Uploading...
                                        </div>
                                    </div>
                                @endif
                            @elseif($reviewingRequest->request_type === 'renewal' || $reviewingRequest->request_type === 'extension')
                                <div class="alert alert-info">
                                    <i class="fa-solid fa-info-circle me-2"></i>
                                    <strong>Note:</strong> After approving this request, you will need to create a new contract for this employee with the proposed dates.
                                </div>
                            @endif
                        @else
                            <!-- Show previous review details -->
                            @if($reviewingRequest->reviewed_at)
                                <hr>
                                <h6 class="text-primary mb-3"><i class="fa-solid fa-history me-2"></i>Review History</h6>
                                <div class="bg-light rounded p-3">
                                    <div class="row g-2">
                                        <div class="col-md-4">
                                            <p class="text-muted mb-1 small">Reviewed By</p>
                                            <p class="fw-medium mb-0">{{ $reviewingRequest->reviewer?->name ?? 'Unknown' }}</p>
                                        </div>
                                        <div class="col-md-4">
                                            <p class="text-muted mb-1 small">Decision</p>
                                            <span class="badge {{ $reviewingRequest->getStatusBadgeClass() }}">{{ ucfirst($reviewingRequest->status) }}</span>
                                        </div>
                                        <div class="col-md-4">
                                            <p class="text-muted mb-1 small">Reviewed On</p>
                                            <p class="fw-medium mb-0">{{ $reviewingRequest->reviewed_at->format('d M Y H:i') }}</p>
                                        </div>
                                        @if($reviewingRequest->review_comments)
                                            <div class="col-12 mt-2">
                                                <p class="text-muted mb-1 small">Comments</p>
                                                <p class="mb-0">{{ $reviewingRequest->review_comments }}</p>
                                            </div>
                                        @endif
                                        @if($reviewingRequest->hr_confirmation_letter)
                                            <div class="col-12 mt-2">
                                                <p class="text-muted mb-1 small">HR Confirmation Letter</p>
                                                <a href="{{ Storage::url($reviewingRequest->hr_confirmation_letter) }}" target="_blank" class="btn btn-outline-success btn-sm">
                                                    <i class="fa-solid fa-file-signature me-1"></i> View Confirmation Letter
                                                </a>
                                            </div>
                                        @endif
                                        @if($reviewingRequest->employee_attachment)
                                            <div class="col-12 mt-2">
                                                <p class="text-muted mb-1 small">Employee Attachment</p>
                                                <a href="{{ Storage::url($reviewingRequest->employee_attachment) }}" target="_blank" class="btn btn-outline-primary btn-sm">
                                                    <i class="fa-solid fa-paperclip me-1"></i> View Attachment
                                                </a>
                                            </div>
                                        @endif
                                    </div>
                                </div>
                            @endif
                        @endif
                    </div>
                    <div class="modal-footer">
                        <button type="button" class="btn btn-secondary" wire:click="closeReviewModal">Close</button>
                        @if($reviewingRequest->isPending())
                            <button type="button" class="btn btn-danger" wire:click="rejectRequest" wire:loading.attr="disabled">
                                <span wire:loading.remove wire:target="rejectRequest">
                                    <i class="fa-solid fa-times me-1"></i> Reject
                                </span>
                                <span wire:loading wire:target="rejectRequest">
                                    <span class="spinner-border spinner-border-sm"></span> Processing...
                                </span>
                            </button>
                            <button type="button" class="btn btn-success" wire:click="approveRequest" wire:loading.attr="disabled">
                                <span wire:loading.remove wire:target="approveRequest">
                                    <i class="fa-solid fa-check me-1"></i> Approve
                                </span>
                                <span wire:loading wire:target="approveRequest">
                                    <span class="spinner-border spinner-border-sm"></span> Processing...
                                </span>
                            </button>
                        @endif
                        @if($reviewingRequest->isApproved() && $reviewingRequest->request_type === 'termination' && !$reviewingRequest->certificate)
                            <a href="{{ route('hr.certificates-of-service') }}?request={{ $reviewingRequest->id }}" class="btn btn-info">
                                <i class="fa-solid fa-certificate me-1"></i> Create Certificate of Service
                            </a>
                        @endif
                    </div>
                </div>
            </div>
        </div>
    @endif
</div>
