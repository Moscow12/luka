<div>
    <x-pages.breadcrumn title="Certificate of Service"
        :breadcrumbs="[
            ['label' => 'Home', 'url' => route('dashboard')],
            ['label' => 'Human Resources'],
            ['label' => 'Certificate of Service']
        ]">
    </x-pages.breadcrumn>

    <!-- Statistics Cards -->
    <div class="row g-3 mb-4">
        <div class="col-md-4">
            <div class="card border-0 shadow-sm bg-warning bg-opacity-10">
                <div class="card-body">
                    <div class="d-flex justify-content-between align-items-center">
                        <div>
                            <h6 class="text-warning mb-1">Pending Approval</h6>
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
                            <h6 class="text-success mb-1">Ready to Print</h6>
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
            <div class="card border-0 shadow-sm bg-info bg-opacity-10">
                <div class="card-body">
                    <div class="d-flex justify-content-between align-items-center">
                        <div>
                            <h6 class="text-info mb-1">Printed</h6>
                            <h2 class="mb-0 fw-bold">{{ $printedCount }}</h2>
                        </div>
                        <div class="bg-info bg-opacity-25 rounded-circle p-3">
                            <i class="fa-solid fa-print fa-2x text-info"></i>
                        </div>
                    </div>
                </div>
            </div>
        </div>
    </div>

    <!-- Pending Terminations Alert -->
    @if($pendingTerminations->count() > 0)
        <div class="alert alert-info mb-4">
            <h6 class="alert-heading"><i class="fa-solid fa-info-circle me-2"></i>Approved Terminations Awaiting Certificates</h6>
            <p class="mb-2">The following approved termination requests need certificates of service:</p>
            <div class="d-flex flex-wrap gap-2">
                @foreach($pendingTerminations as $termination)
                    <button class="btn btn-sm btn-outline-primary" wire:click="openModalFromRequest('{{ $termination->id }}')">
                        <i class="fa-solid fa-user me-1"></i>
                        {{ $termination->employee?->getFullName() }}
                    </button>
                @endforeach
            </div>
        </div>
    @endif

    <!-- Filters and Add Button -->
    <div class="card border-0 shadow-sm mb-4">
        <div class="card-body">
            <div class="row g-3 align-items-end">
                <div class="col-md-3">
                    <label class="form-label">Search</label>
                    <div class="input-group">
                        <span class="input-group-text"><i class="fa-solid fa-search"></i></span>
                        <input type="search" class="form-control" placeholder="Name or certificate no..."
                               wire:model.live.debounce.300ms="search">
                    </div>
                </div>
                <div class="col-md-2">
                    <label class="form-label">Status</label>
                    <select class="form-select" wire:model.live="filterStatus">
                        <option value="">All Status</option>
                        <option value="draft">Draft</option>
                        <option value="pending_approval">Pending Approval</option>
                        <option value="approved">Approved</option>
                        <option value="printed">Printed</option>
                        <option value="cancelled">Cancelled</option>
                    </select>
                </div>
                <div class="col-md-2">
                    <label class="form-label">Separation Type</label>
                    <select class="form-select" wire:model.live="filterType">
                        <option value="">All Types</option>
                        <option value="termination">Termination</option>
                        <option value="end_of_contract">End of Contract</option>
                        <option value="resignation">Resignation</option>
                        <option value="retirement">Retirement</option>
                        <option value="other">Other</option>
                    </select>
                </div>
                <div class="col-md-3">
                    <button class="btn btn-primary" wire:click="openModal()">
                        <i class="fa-solid fa-plus me-1"></i> New Certificate
                    </button>
                </div>
            </div>
        </div>
    </div>

    <!-- Certificates Table -->
    <div class="card border-0 shadow-sm">
        <div class="card-header bg-transparent">
            <h5 class="mb-0"><i class="fa-solid fa-certificate me-2"></i>Certificates of Service</h5>
        </div>
        <div class="card-body p-0">
            <div class="table-responsive">
                <table class="table table-hover mb-0">
                    <thead class="table-light">
                        <tr>
                            <th>Certificate No.</th>
                            <th>Employee</th>
                            <th>Position</th>
                            <th>Service Period</th>
                            <th>Separation Type</th>
                            <th>Status</th>
                            <th>Actions</th>
                        </tr>
                    </thead>
                    <tbody>
                        @forelse($certificates as $certificate)
                            <tr class="{{ $certificate->status === 'pending_approval' ? 'table-warning' : '' }}">
                                <td>
                                    <span class="fw-medium">{{ $certificate->certificate_number }}</span>
                                </td>
                                <td>
                                    <div class="d-flex align-items-center">
                                        <div class="bg-primary bg-opacity-10 rounded-circle d-flex align-items-center justify-content-center me-2" style="width: 35px; height: 35px;">
                                            <span class="text-primary fw-bold">{{ substr($certificate->employee?->first_name ?? 'U', 0, 1) }}</span>
                                        </div>
                                        <div>
                                            <div class="fw-medium">{{ $certificate->employee?->getFullName() ?? 'Unknown' }}</div>
                                            <small class="text-muted">{{ $certificate->department }}</small>
                                        </div>
                                    </div>
                                </td>
                                <td>{{ $certificate->position_held }}</td>
                                <td>
                                    <div>{{ $certificate->service_start_date->format('d/m/Y') }}</div>
                                    <div class="text-muted">to {{ $certificate->service_end_date->format('d/m/Y') }}</div>
                                    <small class="text-info">{{ $certificate->years_of_service }}</small>
                                </td>
                                <td>
                                    <span class="badge bg-{{ $certificate->separation_type === 'termination' ? 'danger' : ($certificate->separation_type === 'end_of_contract' ? 'warning' : 'info') }}">
                                        {{ $certificate->separation_type_label }}
                                    </span>
                                </td>
                                <td>
                                    <span class="badge {{ $certificate->status_badge_class }}">
                                        {{ $certificate->status_label }}
                                    </span>
                                    @if($certificate->approved_at)
                                        <div class="small text-muted">{{ $certificate->approved_at->format('d/m/Y') }}</div>
                                    @endif
                                </td>
                                <td>
                                    <div class="btn-group">
                                        <button class="btn btn-sm btn-outline-primary" wire:click="viewCertificate('{{ $certificate->id }}')" title="View">
                                            <i class="fa-solid fa-eye"></i>
                                        </button>
                                        @if($certificate->canBeEdited())
                                            <button class="btn btn-sm btn-outline-warning" wire:click="openModal('{{ $certificate->id }}')" title="Edit">
                                                <i class="fa-solid fa-pencil"></i>
                                            </button>
                                        @endif
                                        @if($certificate->canBePrinted())
                                            <button class="btn btn-sm btn-outline-success" wire:click="openPrintModal('{{ $certificate->id }}')" title="Print">
                                                <i class="fa-solid fa-print"></i>
                                            </button>
                                        @endif
                                        @if(in_array($certificate->status, ['draft', 'pending_approval']))
                                            <button class="btn btn-sm btn-outline-danger" wire:click="cancelCertificate('{{ $certificate->id }}')"
                                                    onclick="return confirm('Cancel this certificate?')" title="Cancel">
                                                <i class="fa-solid fa-times"></i>
                                            </button>
                                        @endif
                                    </div>
                                </td>
                            </tr>
                        @empty
                            <tr>
                                <td colspan="7" class="text-center py-5">
                                    <div class="text-muted">
                                        <i class="fa-solid fa-certificate fa-3x mb-3"></i>
                                        <p class="mb-0">No certificates found</p>
                                    </div>
                                </td>
                            </tr>
                        @endforelse
                    </tbody>
                </table>
            </div>
        </div>
        @if($certificates->hasPages())
            <div class="card-footer">
                {{ $certificates->links() }}
            </div>
        @endif
    </div>

    <!-- Create/Edit Modal -->
    @if($showModal)
        <div class="modal fade show d-block" tabindex="-1" style="background-color: rgba(0,0,0,0.5);">
            <div class="modal-dialog modal-xl">
                <div class="modal-content">
                    <div class="modal-header bg-primary text-white">
                        <h5 class="modal-title">
                            <i class="fa-solid fa-certificate me-2"></i>
                            {{ $editingId ? 'Edit Certificate of Service' : 'Create Certificate of Service' }}
                        </h5>
                        <button type="button" class="btn-close btn-close-white" wire:click="closeModal"></button>
                    </div>
                    <div class="modal-body">
                        <div class="row g-4">
                            <!-- Left Column: Employee & Contract Details -->
                            <div class="col-lg-6">
                                <h6 class="text-primary mb-3"><i class="fa-solid fa-user me-2"></i>Employee Information</h6>

                                <div class="mb-3">
                                    <label class="form-label">Employee <span class="text-danger">*</span></label>
                                    <select class="form-select @error('employee_id') is-invalid @enderror"
                                            wire:model.live="employee_id" {{ $contract_request_id ? 'disabled' : '' }}>
                                        <option value="">Select Employee...</option>
                                        @foreach($employees as $emp)
                                            <option value="{{ $emp->id }}">{{ $emp->getFullName() }} - {{ $emp->status }}</option>
                                        @endforeach
                                    </select>
                                    @error('employee_id') <div class="invalid-feedback">{{ $message }}</div> @enderror
                                </div>

                                <div class="mb-3">
                                    <label class="form-label">Contract <span class="text-danger">*</span></label>
                                    <select class="form-select @error('contract_id') is-invalid @enderror"
                                            wire:model.live="contract_id" {{ $contract_request_id ? 'disabled' : '' }}>
                                        <option value="">Select Contract...</option>
                                        @foreach($contracts as $contract)
                                            <option value="{{ $contract->id }}">
                                                {{ $contract->start_date->format('d/m/Y') }} - {{ $contract->expire_date->format('d/m/Y') }}
                                                ({{ ucfirst($contract->status) }})
                                            </option>
                                        @endforeach
                                    </select>
                                    @error('contract_id') <div class="invalid-feedback">{{ $message }}</div> @enderror
                                </div>

                                <div class="row g-3">
                                    <div class="col-md-6">
                                        <label class="form-label">Position Held <span class="text-danger">*</span></label>
                                        <input type="text" class="form-control @error('position_held') is-invalid @enderror"
                                               wire:model="position_held">
                                        @error('position_held') <div class="invalid-feedback">{{ $message }}</div> @enderror
                                    </div>
                                    <div class="col-md-6">
                                        <label class="form-label">Department <span class="text-danger">*</span></label>
                                        <input type="text" class="form-control @error('department') is-invalid @enderror"
                                               wire:model="department">
                                        @error('department') <div class="invalid-feedback">{{ $message }}</div> @enderror
                                    </div>
                                </div>

                                <div class="row g-3 mt-1">
                                    <div class="col-md-6">
                                        <label class="form-label">Service Start Date <span class="text-danger">*</span></label>
                                        <input type="date" class="form-control @error('service_start_date') is-invalid @enderror"
                                               wire:model="service_start_date">
                                        @error('service_start_date') <div class="invalid-feedback">{{ $message }}</div> @enderror
                                    </div>
                                    <div class="col-md-6">
                                        <label class="form-label">Service End Date <span class="text-danger">*</span></label>
                                        <input type="date" class="form-control @error('service_end_date') is-invalid @enderror"
                                               wire:model="service_end_date">
                                        @error('service_end_date') <div class="invalid-feedback">{{ $message }}</div> @enderror
                                    </div>
                                </div>
                            </div>

                            <!-- Right Column: Separation Details -->
                            <div class="col-lg-6">
                                <h6 class="text-primary mb-3"><i class="fa-solid fa-door-open me-2"></i>Separation Details</h6>

                                <div class="mb-3">
                                    <label class="form-label">Separation Type <span class="text-danger">*</span></label>
                                    <select class="form-select @error('separation_type') is-invalid @enderror"
                                            wire:model="separation_type">
                                        <option value="end_of_contract">End of Contract</option>
                                        <option value="termination">Termination</option>
                                        <option value="resignation">Voluntary Resignation</option>
                                        <option value="retirement">Retirement</option>
                                        <option value="other">Other</option>
                                    </select>
                                    @error('separation_type') <div class="invalid-feedback">{{ $message }}</div> @enderror
                                </div>

                                <div class="mb-3">
                                    <label class="form-label">Termination Reason</label>
                                    <select class="form-select @error('termination_reason_id') is-invalid @enderror"
                                            wire:model="termination_reason_id">
                                        <option value="">Select Reason (if applicable)...</option>
                                        @foreach($terminationReasons as $reason)
                                            <option value="{{ $reason->id }}">{{ $reason->name }}</option>
                                        @endforeach
                                    </select>
                                    @error('termination_reason_id') <div class="invalid-feedback">{{ $message }}</div> @enderror
                                </div>

                                <div class="mb-3">
                                    <label class="form-label">Workstation (for letterhead)</label>
                                    <select class="form-select @error('workstation_id') is-invalid @enderror"
                                            wire:model="workstation_id">
                                        <option value="">Select Workstation...</option>
                                        @foreach($workstations as $ws)
                                            <option value="{{ $ws->id }}">{{ $ws->name }}</option>
                                        @endforeach
                                    </select>
                                    @error('workstation_id') <div class="invalid-feedback">{{ $message }}</div> @enderror
                                </div>

                                <div class="mb-3">
                                    <label class="form-label">Duties Performed</label>
                                    <textarea class="form-control @error('duties_performed') is-invalid @enderror"
                                              wire:model="duties_performed" rows="2"
                                              placeholder="Brief description of duties..."></textarea>
                                    @error('duties_performed') <div class="invalid-feedback">{{ $message }}</div> @enderror
                                </div>

                                <div class="mb-3">
                                    <label class="form-label">Additional Remarks</label>
                                    <textarea class="form-control @error('additional_remarks') is-invalid @enderror"
                                              wire:model="additional_remarks" rows="2"
                                              placeholder="Any additional comments..."></textarea>
                                    @error('additional_remarks') <div class="invalid-feedback">{{ $message }}</div> @enderror
                                </div>
                            </div>

                            <!-- Declaration Text -->
                            <div class="col-12">
                                <h6 class="text-primary mb-3"><i class="fa-solid fa-scroll me-2"></i>Declaration</h6>
                                <div class="mb-3">
                                    <label class="form-label">Declaration Text <span class="text-danger">*</span></label>
                                    <textarea class="form-control @error('declaration_text') is-invalid @enderror"
                                              wire:model="declaration_text" rows="3"></textarea>
                                    @error('declaration_text') <div class="invalid-feedback">{{ $message }}</div> @enderror
                                    <small class="text-muted">This text will appear on the certificate</small>
                                </div>
                            </div>
                        </div>
                    </div>
                    <div class="modal-footer">
                        <button type="button" class="btn btn-secondary" wire:click="closeModal">Cancel</button>
                        <button type="button" class="btn btn-outline-primary" wire:click="saveDraft" wire:loading.attr="disabled">
                            <span wire:loading.remove wire:target="saveDraft">
                                <i class="fa-solid fa-save me-1"></i> Save Draft
                            </span>
                            <span wire:loading wire:target="saveDraft">
                                <span class="spinner-border spinner-border-sm"></span> Saving...
                            </span>
                        </button>
                        <button type="button" class="btn btn-primary" wire:click="submitForApproval" wire:loading.attr="disabled">
                            <span wire:loading.remove wire:target="submitForApproval">
                                <i class="fa-solid fa-paper-plane me-1"></i> Submit for Approval
                            </span>
                            <span wire:loading wire:target="submitForApproval">
                                <span class="spinner-border spinner-border-sm"></span> Submitting...
                            </span>
                        </button>
                    </div>
                </div>
            </div>
        </div>
    @endif

    <!-- View/Approve Modal -->
    @if($showViewModal && $viewingCertificate)
        <div class="modal fade show d-block" tabindex="-1" style="background-color: rgba(0,0,0,0.5);">
            <div class="modal-dialog modal-xl">
                <div class="modal-content">
                    <div class="modal-header bg-info text-white">
                        <h5 class="modal-title">
                            <i class="fa-solid fa-certificate me-2"></i>
                            Certificate Details - {{ $viewingCertificate->certificate_number }}
                        </h5>
                        <button type="button" class="btn-close btn-close-white" wire:click="closeViewModal"></button>
                    </div>
                    <div class="modal-body">
                        <div class="row g-4">
                            <div class="col-md-6">
                                <h6 class="text-primary mb-3">Employee Information</h6>
                                <table class="table table-sm">
                                    <tr>
                                        <th width="40%">Employee Name:</th>
                                        <td>{{ $viewingCertificate->employee?->getFullName() }}</td>
                                    </tr>
                                    <tr>
                                        <th>Position:</th>
                                        <td>{{ $viewingCertificate->position_held }}</td>
                                    </tr>
                                    <tr>
                                        <th>Department:</th>
                                        <td>{{ $viewingCertificate->department }}</td>
                                    </tr>
                                    <tr>
                                        <th>Service Period:</th>
                                        <td>{{ $viewingCertificate->service_start_date->format('d M Y') }} - {{ $viewingCertificate->service_end_date->format('d M Y') }}</td>
                                    </tr>
                                    <tr>
                                        <th>Duration:</th>
                                        <td>{{ $viewingCertificate->years_of_service }}</td>
                                    </tr>
                                </table>
                            </div>
                            <div class="col-md-6">
                                <h6 class="text-primary mb-3">Separation Details</h6>
                                <table class="table table-sm">
                                    <tr>
                                        <th width="40%">Separation Type:</th>
                                        <td><span class="badge bg-danger">{{ $viewingCertificate->separation_type_label }}</span></td>
                                    </tr>
                                    @if($viewingCertificate->terminationReason)
                                        <tr>
                                            <th>Reason:</th>
                                            <td>{{ $viewingCertificate->terminationReason->name }}</td>
                                        </tr>
                                    @endif
                                    <tr>
                                        <th>Status:</th>
                                        <td><span class="badge {{ $viewingCertificate->status_badge_class }}">{{ $viewingCertificate->status_label }}</span></td>
                                    </tr>
                                    <tr>
                                        <th>Prepared By:</th>
                                        <td>{{ $viewingCertificate->preparedBy?->name }}</td>
                                    </tr>
                                    @if($viewingCertificate->approvedBy)
                                        <tr>
                                            <th>Approved By:</th>
                                            <td>{{ $viewingCertificate->approvedBy?->name }}</td>
                                        </tr>
                                        <tr>
                                            <th>Approved At:</th>
                                            <td>{{ $viewingCertificate->approved_at->format('d M Y H:i') }}</td>
                                        </tr>
                                    @endif
                                </table>
                            </div>
                            @if($viewingCertificate->duties_performed)
                                <div class="col-12">
                                    <h6 class="text-primary mb-2">Duties Performed</h6>
                                    <p class="bg-light p-3 rounded">{{ $viewingCertificate->duties_performed }}</p>
                                </div>
                            @endif
                            <div class="col-12">
                                <h6 class="text-primary mb-2">Declaration</h6>
                                <p class="bg-light p-3 rounded fst-italic">{{ $viewingCertificate->declaration_text }}</p>
                            </div>
                            @if($viewingCertificate->additional_remarks)
                                <div class="col-12">
                                    <h6 class="text-primary mb-2">Additional Remarks</h6>
                                    <p class="bg-light p-3 rounded">{{ $viewingCertificate->additional_remarks }}</p>
                                </div>
                            @endif
                        </div>
                    </div>
                    <div class="modal-footer">
                        <button type="button" class="btn btn-secondary" wire:click="closeViewModal">Close</button>
                        @if($viewingCertificate->canBeApproved())
                            <button type="button" class="btn btn-danger" wire:click="rejectCertificate('{{ $viewingCertificate->id }}')">
                                <i class="fa-solid fa-times me-1"></i> Return for Revision
                            </button>
                            <button type="button" class="btn btn-success" wire:click="approveCertificate('{{ $viewingCertificate->id }}')">
                                <i class="fa-solid fa-check me-1"></i> Approve
                            </button>
                        @endif
                        @if($viewingCertificate->canBePrinted())
                            <button type="button" class="btn btn-primary" wire:click="openPrintModal('{{ $viewingCertificate->id }}')">
                                <i class="fa-solid fa-print me-1"></i> Print Certificate
                            </button>
                        @endif
                    </div>
                </div>
            </div>
        </div>
    @endif

    <!-- Print Modal -->
    @if($showPrintModal && $viewingCertificate)
        <div class="modal fade show d-block" tabindex="-1" style="background-color: rgba(0,0,0,0.5);">
            <div class="modal-dialog modal-xl modal-dialog-scrollable">
                <div class="modal-content">
                    <div class="modal-header bg-success text-white">
                        <h5 class="modal-title">
                            <i class="fa-solid fa-print me-2"></i>
                            Print Certificate - {{ $viewingCertificate->certificate_number }}
                        </h5>
                        <button type="button" class="btn-close btn-close-white" wire:click="closePrintModal"></button>
                    </div>
                    <div class="modal-body p-0">
                        <!-- Printable Certificate -->
                        <div id="printable-certificate" class="p-5 bg-white">
                            <div class="certificate-container" style="border: 3px double #000; padding: 40px; min-height: 800px;">
                                <!-- Header with Logo -->
                                <div class="text-center mb-4">
                                    @if($viewingCertificate->workstation)
                                        <h4 class="fw-bold text-uppercase mb-1">{{ $viewingCertificate->workstation->name }}</h4>
                                        @if($viewingCertificate->workstation->physical_address)
                                            <p class="mb-0">{{ $viewingCertificate->workstation->physical_address }}</p>
                                        @endif
                                        @if($viewingCertificate->workstation->phone_number)
                                            <p class="mb-0">Tel: {{ $viewingCertificate->workstation->phone_number }}</p>
                                        @endif
                                    @else
                                        <h4 class="fw-bold text-uppercase mb-1">[Organization Name]</h4>
                                    @endif
                                </div>

                                <hr class="my-4">

                                <!-- Certificate Title -->
                                <div class="text-center mb-4">
                                    <h2 class="fw-bold text-decoration-underline">CERTIFICATE OF SERVICE</h2>
                                    <p class="text-muted">Certificate No: {{ $viewingCertificate->certificate_number }}</p>
                                </div>

                                <!-- Certificate Body -->
                                <div class="certificate-body" style="font-size: 14px; line-height: 2;">
                                    <p>This is to certify that:</p>

                                    <table class="table table-borderless" style="width: 80%; margin: 0 auto;">
                                        <tr>
                                            <td width="30%"><strong>Name:</strong></td>
                                            <td style="border-bottom: 1px solid #000;">{{ $viewingCertificate->employee?->getFullName() }}</td>
                                        </tr>
                                        <tr>
                                            <td><strong>Position:</strong></td>
                                            <td style="border-bottom: 1px solid #000;">{{ $viewingCertificate->position_held }}</td>
                                        </tr>
                                        <tr>
                                            <td><strong>Department:</strong></td>
                                            <td style="border-bottom: 1px solid #000;">{{ $viewingCertificate->department }}</td>
                                        </tr>
                                        <tr>
                                            <td><strong>Service Period:</strong></td>
                                            <td style="border-bottom: 1px solid #000;">
                                                {{ $viewingCertificate->service_start_date->format('d F Y') }} to {{ $viewingCertificate->service_end_date->format('d F Y') }}
                                                <br><small>({{ $viewingCertificate->years_of_service }})</small>
                                            </td>
                                        </tr>
                                        <tr>
                                            <td><strong>Reason for Leaving:</strong></td>
                                            <td style="border-bottom: 1px solid #000;">
                                                {{ $viewingCertificate->separation_type_label }}
                                                @if($viewingCertificate->terminationReason)
                                                    - {{ $viewingCertificate->terminationReason->name }}
                                                @endif
                                            </td>
                                        </tr>
                                    </table>

                                    @if($viewingCertificate->duties_performed)
                                        <p class="mt-4"><strong>Duties Performed:</strong></p>
                                        <p>{{ $viewingCertificate->duties_performed }}</p>
                                    @endif

                                    <p class="mt-4">{{ $viewingCertificate->declaration_text }}</p>

                                    @if($viewingCertificate->additional_remarks)
                                        <p class="mt-3"><strong>Remarks:</strong> {{ $viewingCertificate->additional_remarks }}</p>
                                    @endif
                                </div>

                                <!-- Signature Section -->
                                <div class="row mt-5 pt-5">
                                    <div class="col-6 text-center">
                                        <div style="border-top: 1px solid #000; width: 200px; margin: 0 auto; padding-top: 5px;">
                                            <p class="mb-0"><strong>{{ $viewingCertificate->approvedBy?->name ?? '_______________' }}</strong></p>
                                            <small>Authorized Signatory</small>
                                        </div>
                                    </div>
                                    <div class="col-6 text-center">
                                        <div style="border-top: 1px solid #000; width: 200px; margin: 0 auto; padding-top: 5px;">
                                            <p class="mb-0"><strong>{{ $viewingCertificate->approved_at?->format('d F Y') ?? '_______________' }}</strong></p>
                                            <small>Date</small>
                                        </div>
                                    </div>
                                </div>

                                <!-- Footer -->
                                <div class="text-center mt-4 pt-4" style="border-top: 1px dashed #ccc;">
                                    <small class="text-muted">
                                        Issued on: {{ now()->format('d F Y') }} |
                                        Print Count: {{ $viewingCertificate->print_count + 1 }}
                                    </small>
                                </div>
                            </div>
                        </div>
                    </div>
                    <div class="modal-footer">
                        <button type="button" class="btn btn-secondary" wire:click="closePrintModal">Close</button>
                        <button type="button" class="btn btn-primary" onclick="printCertificate()" wire:click="markAsPrinted('{{ $viewingCertificate->id }}')">
                            <i class="fa-solid fa-print me-1"></i> Print
                        </button>
                    </div>
                </div>
            </div>
        </div>

        <script>
            function printCertificate() {
                var printContents = document.getElementById('printable-certificate').innerHTML;
                var originalContents = document.body.innerHTML;

                document.body.innerHTML = `
                    <html>
                    <head>
                        <title>Certificate of Service</title>
                        <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/css/bootstrap.min.css" rel="stylesheet">
                        <style>
                            @media print {
                                body { padding: 20px; }
                                .certificate-container { border: 3px double #000 !important; }
                            }
                        </style>
                    </head>
                    <body>${printContents}</body>
                    </html>
                `;

                window.print();
                document.body.innerHTML = originalContents;
                window.location.reload();
            }
        </script>
    @endif
</div>
