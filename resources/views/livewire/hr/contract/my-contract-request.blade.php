<div>
    <x-pages.breadcrumn title="My Contract Requests"
        :breadcrumbs="[
            ['label' => 'Home', 'url' => route('dashboard')],
            ['label' => 'My Contract Requests']
        ]">
    </x-pages.breadcrumn>

    @if(!$employee)
        <div class="alert alert-warning">
            <i class="fa-solid fa-exclamation-triangle me-2"></i>
            You don't have an employee profile linked to your account. Please contact HR.
        </div>
    @else
        <!-- Contract Status Alert -->
        @if($contractExpiringAlert && $currentContract)
            <div class="alert alert-warning alert-dismissible fade show mb-4" role="alert">
                <div class="d-flex align-items-center">
                    <i class="fa-solid fa-clock fa-2x me-3"></i>
                    <div>
                        <h5 class="alert-heading mb-1">Contract Expiring Soon!</h5>
                        <p class="mb-0">Your current contract will expire on <strong>{{ $currentContract->expire_date->format('d M Y') }}</strong> ({{ $daysUntilExpiry }} days remaining). Please submit a renewal, extension, or termination request.</p>
                    </div>
                </div>
                <button type="button" class="btn-close" data-bs-dismiss="alert" aria-label="Close"></button>
            </div>
        @endif

        <!-- Current Contract Card -->
        @if($currentContract)
            <div class="card border-0 shadow-sm mb-4">
                <div class="card-header bg-primary text-white">
                    <h5 class="mb-0"><i class="fa-solid fa-file-contract me-2"></i>Current Contract Details</h5>
                </div>
                <div class="card-body">
                    <div class="row">
                        <div class="col-md-3">
                            <p class="text-muted mb-1">Contract Type</p>
                            <h6>{{ ucfirst(str_replace('_', ' ', $currentContract->contract_type)) }}</h6>
                        </div>
                        <div class="col-md-3">
                            <p class="text-muted mb-1">Start Date</p>
                            <h6>{{ $currentContract->start_date->format('d M Y') }}</h6>
                        </div>
                        <div class="col-md-3">
                            <p class="text-muted mb-1">End Date</p>
                            <h6>{{ $currentContract->expire_date->format('d M Y') }}</h6>
                        </div>
                        <div class="col-md-3">
                            <p class="text-muted mb-1">Status</p>
                            <span class="badge {{ $currentContract->getStatusBadgeClass() }}">{{ $currentContract->getStatusLabel() }}</span>
                        </div>
                    </div>
                    <hr>
                    <div class="d-flex gap-2 flex-wrap">
                        <button class="btn btn-primary" wire:click="openRequestModal('renewal')">
                            <i class="fa-solid fa-rotate me-1"></i> Request Renewal
                        </button>
                        <button class="btn btn-info text-white" wire:click="openRequestModal('extension')">
                            <i class="fa-solid fa-calendar-plus me-1"></i> Request Extension
                        </button>
                        <button class="btn btn-danger" wire:click="openRequestModal('termination')">
                            <i class="fa-solid fa-door-open me-1"></i> Request Termination
                        </button>
                    </div>
                </div>
            </div>
        @else
            <div class="alert alert-info">
                <i class="fa-solid fa-info-circle me-2"></i>
                You don't have an active contract. Please contact HR if you believe this is an error.
            </div>
        @endif

        <!-- My Requests History -->
        <div class="card border-0 shadow-sm">
            <div class="card-header bg-transparent">
                <h5 class="mb-0"><i class="fa-solid fa-history me-2"></i>My Request History</h5>
            </div>
            <div class="card-body p-0">
                <div class="table-responsive">
                    <table class="table table-hover mb-0">
                        <thead class="table-light">
                            <tr>
                                <th>Request Type</th>
                                <th>Contract Period</th>
                                <th>Proposed Dates</th>
                                <th>Status</th>
                                <th>Submitted</th>
                                <th>Actions</th>
                            </tr>
                        </thead>
                        <tbody>
                            @forelse($myRequests as $request)
                                <tr>
                                    <td>
                                        <span class="badge {{ $request->getTypeBadgeClass() }}">
                                            {{ $request->getTypeLabel() }}
                                        </span>
                                    </td>
                                    <td>
                                        @if($request->contract)
                                            {{ $request->contract->start_date->format('d/m/Y') }} - {{ $request->contract->expire_date->format('d/m/Y') }}
                                        @else
                                            -
                                        @endif
                                    </td>
                                    <td>
                                        @if($request->request_type === 'termination')
                                            Last Day: {{ $request->last_working_day?->format('d/m/Y') ?? '-' }}
                                        @elseif($request->proposed_start_date && $request->proposed_end_date)
                                            {{ $request->proposed_start_date->format('d/m/Y') }} - {{ $request->proposed_end_date->format('d/m/Y') }}
                                        @elseif($request->extension_period)
                                            +{{ $request->extension_period }} months
                                        @else
                                            -
                                        @endif
                                    </td>
                                    <td>
                                        <span class="badge {{ $request->getStatusBadgeClass() }}">
                                            {{ ucfirst($request->status) }}
                                        </span>
                                    </td>
                                    <td>{{ $request->created_at->format('d/m/Y H:i') }}</td>
                                    <td>
                                        <button class="btn btn-sm btn-outline-primary" wire:click="viewRequest('{{ $request->id }}')" title="View Details">
                                            <i class="fa-solid fa-eye"></i>
                                        </button>
                                        @if($request->isPending())
                                            <button class="btn btn-sm btn-outline-warning" wire:click="editRequest('{{ $request->id }}')" title="Edit Request">
                                                <i class="fa-solid fa-pencil"></i>
                                            </button>
                                            <button class="btn btn-sm btn-outline-danger" wire:click="cancelRequest('{{ $request->id }}')"
                                                    onclick="return confirm('Are you sure you want to cancel this request?')" title="Cancel Request">
                                                <i class="fa-solid fa-times"></i>
                                            </button>
                                        @endif
                                    </td>
                                </tr>
                            @empty
                                <tr>
                                    <td colspan="6" class="text-center py-4">
                                        <div class="text-muted">
                                            <i class="fa-solid fa-inbox fa-2x mb-2"></i>
                                            <p class="mb-0">No contract requests found</p>
                                        </div>
                                    </td>
                                </tr>
                            @endforelse
                        </tbody>
                    </table>
                </div>
            </div>
            @if($myRequests->hasPages())
                <div class="card-footer">
                    {{ $myRequests->links() }}
                </div>
            @endif
        </div>
    @endif

    <!-- Request Modal -->
    @if($showModal)
        <div class="modal fade show d-block" tabindex="-1" style="background-color: rgba(0,0,0,0.5);">
            <div class="modal-dialog modal-lg">
                <div class="modal-content">
                    <div class="modal-header bg-{{ $request_type === 'termination' ? 'danger' : ($request_type === 'extension' ? 'info' : 'primary') }} text-white">
                        <h5 class="modal-title">
                            @if($request_type === 'renewal')
                                <i class="fa-solid fa-rotate me-2"></i>{{ $editingId ? 'Edit' : 'Request' }} Contract Renewal
                            @elseif($request_type === 'extension')
                                <i class="fa-solid fa-calendar-plus me-2"></i>{{ $editingId ? 'Edit' : 'Request' }} Contract Extension
                            @else
                                <i class="fa-solid fa-door-open me-2"></i>{{ $editingId ? 'Edit' : 'Request' }} Contract Termination
                            @endif
                        </h5>
                        <button type="button" class="btn-close btn-close-white" wire:click="closeModal"></button>
                    </div>
                    <form wire:submit.prevent="submitRequest">
                        <div class="modal-body">
                            <!-- Request Type Selection -->
                            <div class="mb-4">
                                <label class="form-label fw-bold">Request Type</label>
                                <div class="btn-group w-100" role="group">
                                    <input type="radio" class="btn-check" name="request_type" id="type_renewal" value="renewal" wire:model.live="request_type">
                                    <label class="btn btn-outline-primary" for="type_renewal">
                                        <i class="fa-solid fa-rotate me-1"></i> Renewal
                                    </label>

                                    <input type="radio" class="btn-check" name="request_type" id="type_extension" value="extension" wire:model.live="request_type">
                                    <label class="btn btn-outline-info" for="type_extension">
                                        <i class="fa-solid fa-calendar-plus me-1"></i> Extension
                                    </label>

                                    <input type="radio" class="btn-check" name="request_type" id="type_termination" value="termination" wire:model.live="request_type">
                                    <label class="btn btn-outline-danger" for="type_termination">
                                        <i class="fa-solid fa-door-open me-1"></i> Termination
                                    </label>
                                </div>
                                @error('request_type') <small class="text-danger">{{ $message }}</small> @enderror
                            </div>

                            <!-- Renewal Fields -->
                            @if($request_type === 'renewal')
                                <div class="row g-3">
                                    <div class="col-md-6">
                                        <label class="form-label">Proposed Start Date <span class="text-danger">*</span></label>
                                        <input type="date" class="form-control @error('proposed_start_date') is-invalid @enderror"
                                               wire:model="proposed_start_date">
                                        @error('proposed_start_date') <div class="invalid-feedback">{{ $message }}</div> @enderror
                                    </div>
                                    <div class="col-md-6">
                                        <label class="form-label">Proposed End Date <span class="text-danger">*</span></label>
                                        <input type="date" class="form-control @error('proposed_end_date') is-invalid @enderror"
                                               wire:model="proposed_end_date">
                                        @error('proposed_end_date') <div class="invalid-feedback">{{ $message }}</div> @enderror
                                    </div>
                                </div>
                            @endif

                            <!-- Extension Fields -->
                            @if($request_type === 'extension')
                                <div class="mb-3">
                                    <label class="form-label">Extension Period (Months) <span class="text-danger">*</span></label>
                                    <select class="form-select @error('extension_period') is-invalid @enderror" wire:model="extension_period">
                                        <option value="">Select period...</option>
                                        @for($i = 1; $i <= 24; $i++)
                                            <option value="{{ $i }}">{{ $i }} {{ $i === 1 ? 'month' : 'months' }}</option>
                                        @endfor
                                    </select>
                                    @error('extension_period') <div class="invalid-feedback">{{ $message }}</div> @enderror
                                </div>
                            @endif

                            <!-- Termination Fields -->
                            @if($request_type === 'termination')
                                <div class="alert alert-warning mb-3">
                                    <i class="fa-solid fa-exclamation-triangle me-2"></i>
                                    <strong>Important:</strong> By submitting a termination request, you are notifying HR that you do not wish to continue employment after your contract ends.
                                </div>
                                @if($currentContract && now()->diffInDays($currentContract->expire_date, false) > 90)
                                    <div class="alert alert-danger mb-3">
                                        <i class="fa-solid fa-info-circle me-2"></i>
                                        <strong>Early Termination Notice:</strong> Your contract is not within the notification period (more than 90 days until expiry). A termination letter/resignation letter is recommended. HR will need to provide a confirmation letter for processing.
                                    </div>
                                @endif
                                <div class="mb-3">
                                    <label class="form-label">Proposed Last Working Day <span class="text-danger">*</span></label>
                                    <input type="date" class="form-control @error('last_working_day') is-invalid @enderror"
                                           wire:model="last_working_day">
                                    @error('last_working_day') <div class="invalid-feedback">{{ $message }}</div> @enderror
                                </div>
                                <div class="mb-3">
                                    <label class="form-label">Handover Notes</label>
                                    <textarea class="form-control @error('handover_notes') is-invalid @enderror"
                                              wire:model="handover_notes" rows="3"
                                              placeholder="Describe any pending tasks, projects, or handover requirements..."></textarea>
                                    @error('handover_notes') <div class="invalid-feedback">{{ $message }}</div> @enderror
                                </div>
                                <div class="mb-3">
                                    <label class="form-label">
                                        Termination/Resignation Letter
                                        @if($currentContract && now()->diffInDays($currentContract->expire_date, false) > 90)
                                            <span class="text-danger">(Recommended for early termination)</span>
                                        @endif
                                    </label>
                                    @if($existing_attachment)
                                        <div class="alert alert-info py-2 mb-2">
                                            <i class="fa-solid fa-paperclip me-2"></i>
                                            Current attachment: <a href="{{ Storage::url($existing_attachment) }}" target="_blank" class="text-decoration-underline">View file</a>
                                            <small class="d-block text-muted">Upload a new file to replace the existing one</small>
                                        </div>
                                    @endif
                                    <input type="file" class="form-control @error('employee_attachment') is-invalid @enderror"
                                           wire:model="employee_attachment"
                                           accept=".pdf,.doc,.docx,.jpg,.jpeg,.png">
                                    @error('employee_attachment') <div class="invalid-feedback">{{ $message }}</div> @enderror
                                    <small class="text-muted">Accepted formats: PDF, DOC, DOCX, JPG, PNG (Max: 5MB)</small>
                                    <div wire:loading wire:target="employee_attachment" class="text-primary mt-1">
                                        <span class="spinner-border spinner-border-sm"></span> Uploading...
                                    </div>
                                </div>
                            @endif

                            <!-- Reason (Common to all) -->
                            <div class="mb-3">
                                <label class="form-label">
                                    @if($request_type === 'termination')
                                        Reason for Leaving <span class="text-danger">*</span>
                                    @else
                                        Reason for Request <span class="text-danger">*</span>
                                    @endif
                                </label>
                                <textarea class="form-control @error('reason') is-invalid @enderror"
                                          wire:model="reason" rows="4"
                                          placeholder="Please provide a detailed explanation for your request..."></textarea>
                                @error('reason') <div class="invalid-feedback">{{ $message }}</div> @enderror
                                <small class="text-muted">Minimum 10 characters</small>
                            </div>
                        </div>
                        <div class="modal-footer">
                            <button type="button" class="btn btn-secondary" wire:click="closeModal">Cancel</button>
                            <button type="submit" class="btn btn-{{ $request_type === 'termination' ? 'danger' : 'primary' }}" wire:loading.attr="disabled">
                                <span wire:loading.remove wire:target="submitRequest">{{ $editingId ? 'Update Request' : 'Submit Request' }}</span>
                                <span wire:loading wire:target="submitRequest">
                                    <span class="spinner-border spinner-border-sm"></span> {{ $editingId ? 'Updating...' : 'Submitting...' }}
                                </span>
                            </button>
                        </div>
                    </form>
                </div>
            </div>
        </div>
    @endif

    <!-- View Request Modal -->
    @if($showViewModal && $viewingRequest)
        <div class="modal fade show d-block" tabindex="-1" style="background-color: rgba(0,0,0,0.5);">
            <div class="modal-dialog modal-lg">
                <div class="modal-content">
                    <div class="modal-header">
                        <h5 class="modal-title">
                            <i class="fa-solid fa-file-lines me-2"></i>Request Details
                        </h5>
                        <button type="button" class="btn-close" wire:click="closeViewModal"></button>
                    </div>
                    <div class="modal-body">
                        <div class="row g-3">
                            <div class="col-md-6">
                                <p class="text-muted mb-1">Request Type</p>
                                <span class="badge {{ $viewingRequest->getTypeBadgeClass() }} fs-6">{{ $viewingRequest->getTypeLabel() }}</span>
                            </div>
                            <div class="col-md-6">
                                <p class="text-muted mb-1">Status</p>
                                <span class="badge {{ $viewingRequest->getStatusBadgeClass() }} fs-6">{{ ucfirst($viewingRequest->status) }}</span>
                            </div>
                            <div class="col-12">
                                <hr>
                            </div>
                            @if($viewingRequest->request_type !== 'termination')
                                <div class="col-md-6">
                                    <p class="text-muted mb-1">Proposed Start Date</p>
                                    <h6>{{ $viewingRequest->proposed_start_date?->format('d M Y') ?? '-' }}</h6>
                                </div>
                                <div class="col-md-6">
                                    <p class="text-muted mb-1">Proposed End Date</p>
                                    <h6>{{ $viewingRequest->proposed_end_date?->format('d M Y') ?? '-' }}</h6>
                                </div>
                            @endif
                            @if($viewingRequest->extension_period)
                                <div class="col-md-6">
                                    <p class="text-muted mb-1">Extension Period</p>
                                    <h6>{{ $viewingRequest->extension_period }} months</h6>
                                </div>
                            @endif
                            @if($viewingRequest->request_type === 'termination')
                                <div class="col-md-6">
                                    <p class="text-muted mb-1">Last Working Day</p>
                                    <h6>{{ $viewingRequest->last_working_day?->format('d M Y') ?? '-' }}</h6>
                                </div>
                            @endif
                            <div class="col-12">
                                <p class="text-muted mb-1">Reason</p>
                                <div class="p-3 bg-light rounded">{{ $viewingRequest->reason }}</div>
                            </div>
                            @if($viewingRequest->handover_notes)
                                <div class="col-12">
                                    <p class="text-muted mb-1">Handover Notes</p>
                                    <div class="p-3 bg-light rounded">{{ $viewingRequest->handover_notes }}</div>
                                </div>
                            @endif
                            @if($viewingRequest->employee_attachment)
                                <div class="col-12">
                                    <p class="text-muted mb-1">Your Attachment</p>
                                    <a href="{{ Storage::url($viewingRequest->employee_attachment) }}" target="_blank" class="btn btn-outline-primary btn-sm">
                                        <i class="fa-solid fa-paperclip me-1"></i> View Termination Letter
                                    </a>
                                </div>
                            @endif
                            @if($viewingRequest->isEarlyTermination())
                                <div class="col-12">
                                    <div class="alert alert-info mb-0">
                                        <i class="fa-solid fa-info-circle me-2"></i>
                                        <strong>Early Termination:</strong> This is an early termination request (contract not within notification period).
                                        @if($viewingRequest->hr_confirmation_letter)
                                            <br>HR has provided a confirmation letter.
                                        @else
                                            <br>HR confirmation letter is pending.
                                        @endif
                                    </div>
                                </div>
                            @endif
                            @if($viewingRequest->hr_confirmation_letter)
                                <div class="col-12">
                                    <p class="text-muted mb-1">HR Confirmation Letter</p>
                                    <a href="{{ Storage::url($viewingRequest->hr_confirmation_letter) }}" target="_blank" class="btn btn-outline-success btn-sm">
                                        <i class="fa-solid fa-file-signature me-1"></i> View Confirmation Letter
                                    </a>
                                </div>
                            @endif
                            @if($viewingRequest->reviewed_at)
                                <div class="col-12">
                                    <hr>
                                    <h6 class="text-primary"><i class="fa-solid fa-user-check me-2"></i>HR Review</h6>
                                </div>
                                <div class="col-md-6">
                                    <p class="text-muted mb-1">Reviewed By</p>
                                    <h6>{{ $viewingRequest->reviewer?->name ?? '-' }}</h6>
                                </div>
                                <div class="col-md-6">
                                    <p class="text-muted mb-1">Reviewed At</p>
                                    <h6>{{ $viewingRequest->reviewed_at->format('d M Y H:i') }}</h6>
                                </div>
                                @if($viewingRequest->review_comments)
                                    <div class="col-12">
                                        <p class="text-muted mb-1">HR Comments</p>
                                        <div class="p-3 bg-light rounded">{{ $viewingRequest->review_comments }}</div>
                                    </div>
                                @endif
                            @endif
                        </div>
                    </div>
                    <div class="modal-footer">
                        <button type="button" class="btn btn-secondary" wire:click="closeViewModal">Close</button>
                    </div>
                </div>
            </div>
        </div>
    @endif
</div>
