<div>
    <div class="container-fluid">
        <!-- Header -->
        <div class="row mb-4">
            <div class="col-12">
                <div class="d-flex justify-content-between align-items-center">
                    <div>
                        <h4 class="mb-1">{{ $contract->title }}</h4>
                        <p class="text-muted mb-0">Contract #{{ $contract->contract_number }}</p>
                    </div>
                    <div>
                        <a href="{{ route('contracts.list') }}" class="btn btn-outline-secondary">
                            <i class="bi bi-arrow-left me-2"></i>Back to List
                        </a>
                    </div>
                </div>
            </div>
        </div>

        <!-- Contract Summary Card -->
        <div class="card shadow-sm border-0 mb-4">
            <div class="card-body">
                <div class="row">
                    <div class="col-md-3">
                        <h6 class="text-muted mb-2">Contract Value</h6>
                        <h4 class="mb-0">{{ number_format($contract->contract_value, 2) }}</h4>
                    </div>
                    <div class="col-md-3">
                        <h6 class="text-muted mb-2">Status</h6>
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
                        <h5><span class="badge bg-{{ $color }}">{{ ucfirst(str_replace('_', ' ', $contract->status)) }}</span></h5>
                    </div>
                    <div class="col-md-3">
                        <h6 class="text-muted mb-2">Duration</h6>
                        <p class="mb-0">{{ $contract->start_date->format('M d, Y') }} - {{ $contract->end_date->format('M d, Y') }}</p>
                    </div>
                    <div class="col-md-3">
                        <h6 class="text-muted mb-2">Days Until Expiry</h6>
                        <h4 class="mb-0 {{ $contract->is_expiring_soon ? 'text-warning' : '' }}">
                            {{ $contract->days_until_expiry > 0 ? $contract->days_until_expiry : 'Expired' }}
                        </h4>
                    </div>
                </div>
            </div>
        </div>

        <!-- Tabs -->
        <div class="card shadow-sm border-0">
            <div class="card-header bg-white">
                <ul class="nav nav-tabs card-header-tabs" role="tablist">
                    <li class="nav-item">
                        <a class="nav-link {{ $activeTab === 'overview' ? 'active' : '' }}"
                           wire:click="setActiveTab('overview')"
                           role="tab"
                           style="cursor: pointer;">
                            <i class="bi bi-info-circle me-2"></i>Overview
                        </a>
                    </li>
                    <li class="nav-item">
                        <a class="nav-link {{ $activeTab === 'parties' ? 'active' : '' }}"
                           wire:click="setActiveTab('parties')"
                           role="tab"
                           style="cursor: pointer;">
                            <i class="bi bi-people me-2"></i>Parties ({{ $contract->parties->count() }})
                        </a>
                    </li>
                    <li class="nav-item">
                        <a class="nav-link {{ $activeTab === 'documents' ? 'active' : '' }}"
                           wire:click="setActiveTab('documents')"
                           role="tab"
                           style="cursor: pointer;">
                            <i class="bi bi-file-earmark-text me-2"></i>Documents ({{ $contract->documents->count() }})
                        </a>
                    </li>
                    <li class="nav-item">
                        <a class="nav-link {{ $activeTab === 'deliverables' ? 'active' : '' }}"
                           wire:click="setActiveTab('deliverables')"
                           role="tab"
                           style="cursor: pointer;">
                            <i class="bi bi-list-check me-2"></i>Deliverables ({{ $contract->deliverables->count() }})
                        </a>
                    </li>
                    <li class="nav-item">
                        <a class="nav-link {{ $activeTab === 'renewals' ? 'active' : '' }}"
                           wire:click="setActiveTab('renewals')"
                           role="tab"
                           style="cursor: pointer;">
                            <i class="bi bi-arrow-repeat me-2"></i>Renewals ({{ $contract->renewals->count() }})
                        </a>
                    </li>
                    <li class="nav-item">
                        <a class="nav-link {{ $activeTab === 'approvals' ? 'active' : '' }}"
                           wire:click="setActiveTab('approvals')"
                           role="tab"
                           style="cursor: pointer;">
                            <i class="bi bi-check2-square me-2"></i>Approvals ({{ $contract->approvals->count() }})
                        </a>
                    </li>
                </ul>
            </div>

            <div class="card-body">
                {{-- Overview Tab --}}
                @if($activeTab === 'overview')
                    <div class="row">
                        <div class="col-md-6">
                            <h5 class="mb-3">Contract Details</h5>
                            <table class="table table-borderless">
                                <tr>
                                    <td class="text-muted" width="40%">Contract Number:</td>
                                    <td><strong>{{ $contract->contract_number }}</strong></td>
                                </tr>
                                <tr>
                                    <td class="text-muted">Title:</td>
                                    <td><strong>{{ $contract->title }}</strong></td>
                                </tr>
                                <tr>
                                    <td class="text-muted">Type:</td>
                                    <td><span class="badge bg-info">{{ ucfirst(str_replace('_', ' ', $contract->type)) }}</span></td>
                                </tr>
                                <tr>
                                    <td class="text-muted">Department:</td>
                                    <td>{{ $contract->department->name ?? 'N/A' }}</td>
                                </tr>
                                <tr>
                                    <td class="text-muted">Start Date:</td>
                                    <td>{{ $contract->start_date->format('F d, Y') }}</td>
                                </tr>
                                <tr>
                                    <td class="text-muted">End Date:</td>
                                    <td>{{ $contract->end_date->format('F d, Y') }}</td>
                                </tr>
                                <tr>
                                    <td class="text-muted">Contract Value:</td>
                                    <td><strong class="text-success">{{ number_format($contract->contract_value, 2) }}</strong></td>
                                </tr>
                                <tr>
                                    <td class="text-muted">Added By:</td>
                                    <td>{{ $contract->addedBy->name }}</td>
                                </tr>
                            </table>
                        </div>
                        <div class="col-md-6">
                            <h5 class="mb-3">Description</h5>
                            <p class="text-muted">{{ $contract->description ?? 'No description provided.' }}</p>
                        </div>
                    </div>
                @endif

                {{-- Parties Tab --}}
                @if($activeTab === 'parties')
                    <div class="row mb-4">
                        <div class="col-md-12">
                            <button class="btn btn-sm btn-primary" data-bs-toggle="collapse" data-bs-target="#addPartyForm">
                                <i class="bi bi-plus-circle me-2"></i>Add Party
                            </button>
                        </div>
                    </div>

                    <div class="collapse mb-4" id="addPartyForm">
                        <div class="card">
                            <div class="card-body">
                                <form wire:submit.prevent="addParty">
                                    <div class="row g-3">
                                        <div class="col-md-4">
                                            <label class="form-label">Party Type*</label>
                                            <select wire:model="partyType" class="form-select" required>
                                                <option value="">Select Type</option>
                                                <option value="vendor">Vendor</option>
                                                <option value="service_provider">Service Provider</option>
                                                <option value="agency">Agency</option>
                                                <option value="partner_institution">Partner Institution</option>
                                                <option value="other">Other</option>
                                            </select>
                                        </div>
                                        <div class="col-md-4">
                                            <label class="form-label">Party Name*</label>
                                            <input type="text" wire:model="partyName" class="form-control" required>
                                        </div>
                                        <div class="col-md-4">
                                            <label class="form-label">Contact Person</label>
                                            <input type="text" wire:model="contactPerson" class="form-control">
                                        </div>
                                        <div class="col-md-4">
                                            <label class="form-label">Email</label>
                                            <input type="email" wire:model="partyEmail" class="form-control">
                                        </div>
                                        <div class="col-md-4">
                                            <label class="form-label">Phone</label>
                                            <input type="text" wire:model="partyPhone" class="form-control">
                                        </div>
                                        <div class="col-md-4">
                                            <label class="form-label">Address</label>
                                            <input type="text" wire:model="partyAddress" class="form-control">
                                        </div>
                                        <div class="col-12">
                                            <button type="submit" class="btn btn-primary">
                                                <i class="bi bi-save me-2"></i>Add Party
                                            </button>
                                        </div>
                                    </div>
                                </form>
                            </div>
                        </div>
                    </div>

                    <div class="table-responsive">
                        <table class="table table-hover">
                            <thead class="bg-light">
                                <tr>
                                    <th>Party Name</th>
                                    <th>Type</th>
                                    <th>Contact Person</th>
                                    <th>Email</th>
                                    <th>Phone</th>
                                    <th>Status</th>
                                </tr>
                            </thead>
                            <tbody>
                                @forelse($contract->parties as $party)
                                    <tr>
                                        <td><strong>{{ $party->party_name }}</strong></td>
                                        <td><span class="badge bg-info">{{ ucfirst(str_replace('_', ' ', $party->party_type)) }}</span></td>
                                        <td>{{ $party->contact_person ?? 'N/A' }}</td>
                                        <td>{{ $party->email ?? 'N/A' }}</td>
                                        <td>{{ $party->phone ?? 'N/A' }}</td>
                                        <td><span class="badge bg-{{ $party->status === 'active' ? 'success' : 'secondary' }}">{{ ucfirst($party->status) }}</span></td>
                                    </tr>
                                @empty
                                    <tr>
                                        <td colspan="6" class="text-center text-muted py-4">No parties added yet.</td>
                                    </tr>
                                @endforelse
                            </tbody>
                        </table>
                    </div>
                @endif

                {{-- Documents Tab --}}
                @if($activeTab === 'documents')
                    <div class="row mb-4">
                        <div class="col-md-12">
                            <button class="btn btn-sm btn-primary" data-bs-toggle="collapse" data-bs-target="#uploadDocumentForm">
                                <i class="bi bi-cloud-upload me-2"></i>Upload Document
                            </button>
                        </div>
                    </div>

                    <div class="collapse mb-4" id="uploadDocumentForm">
                        <div class="card">
                            <div class="card-body">
                                <form wire:submit.prevent="uploadDocument">
                                    <div class="row g-3">
                                        <div class="col-md-6">
                                            <label class="form-label">Document Name*</label>
                                            <input type="text" wire:model="documentName" class="form-control" required>
                                        </div>
                                        <div class="col-md-6">
                                            <label class="form-label">Document Number</label>
                                            <input type="text" wire:model="documentNumber" class="form-control">
                                        </div>
                                        <div class="col-md-12">
                                            <label class="form-label">Description</label>
                                            <textarea wire:model="documentDescription" class="form-control" rows="2"></textarea>
                                        </div>
                                        <div class="col-md-12">
                                            <label class="form-label">File* (PDF, DOC, DOCX - Max 10MB)</label>
                                            <input type="file" wire:model="document" class="form-control" accept=".pdf,.doc,.docx">
                                            @error('document') <span class="text-danger small">{{ $message }}</span> @enderror
                                        </div>
                                        <div class="col-12">
                                            <button type="submit" class="btn btn-primary" wire:loading.attr="disabled" wire:target="document,uploadDocument">
                                                <span wire:loading.remove wire:target="uploadDocument"><i class="bi bi-upload me-2"></i>Upload Document</span>
                                                <span wire:loading wire:target="uploadDocument">Uploading...</span>
                                            </button>
                                        </div>
                                    </div>
                                </form>
                            </div>
                        </div>
                    </div>

                    <div class="table-responsive">
                        <table class="table table-hover">
                            <thead class="bg-light">
                                <tr>
                                    <th>Document Name</th>
                                    <th>Number</th>
                                    <th>Version</th>
                                    <th>Status</th>
                                    <th>Uploaded By</th>
                                    <th>Date</th>
                                    <th>Action</th>
                                </tr>
                            </thead>
                            <tbody>
                                @forelse($contract->documents as $doc)
                                    <tr>
                                        <td><strong>{{ $doc->document_name }}</strong></td>
                                        <td>{{ $doc->document_number ?? 'N/A' }}</td>
                                        <td><span class="badge bg-secondary">v{{ $doc->version }}</span></td>
                                        <td><span class="badge bg-{{ $doc->status === 'active' ? 'success' : 'secondary' }}">{{ ucfirst($doc->status) }}</span></td>
                                        <td>{{ $doc->addedBy->name }}</td>
                                        <td>{{ $doc->created_at->format('M d, Y') }}</td>
                                        <td>
                                            <a href="{{ Storage::url($doc->file_path) }}" target="_blank" class="btn btn-sm btn-outline-primary">
                                                <i class="bi bi-download"></i>
                                            </a>
                                        </td>
                                    </tr>
                                @empty
                                    <tr>
                                        <td colspan="7" class="text-center text-muted py-4">No documents uploaded yet.</td>
                                    </tr>
                                @endforelse
                            </tbody>
                        </table>
                    </div>
                @endif

                {{-- Deliverables Tab --}}
                @if($activeTab === 'deliverables')
                    <div class="row mb-4">
                        <div class="col-md-12">
                            <button class="btn btn-sm btn-primary" data-bs-toggle="collapse" data-bs-target="#addDeliverableForm">
                                <i class="bi bi-plus-circle me-2"></i>Add Deliverable
                            </button>
                        </div>
                    </div>

                    <div class="collapse mb-4" id="addDeliverableForm">
                        <div class="card">
                            <div class="card-body">
                                <form wire:submit.prevent="addDeliverable">
                                    <div class="row g-3">
                                        <div class="col-md-6">
                                            <label class="form-label">Deliverable Name*</label>
                                            <input type="text" wire:model="deliverableName" class="form-control" required>
                                        </div>
                                        <div class="col-md-6">
                                            <label class="form-label">Type*</label>
                                            <input type="text" wire:model="deliverableType" class="form-control" required>
                                        </div>
                                        <div class="col-md-6">
                                            <label class="form-label">KPI</label>
                                            <input type="text" wire:model="deliverableKpi" class="form-control">
                                        </div>
                                        <div class="col-md-6">
                                            <label class="form-label">Due Date</label>
                                            <input type="date" wire:model="deliverableDueDate" class="form-control">
                                        </div>
                                        <div class="col-md-12">
                                            <label class="form-label">Description</label>
                                            <textarea wire:model="deliverableDescription" class="form-control" rows="2"></textarea>
                                        </div>
                                        <div class="col-12">
                                            <button type="submit" class="btn btn-primary">
                                                <i class="bi bi-save me-2"></i>Add Deliverable
                                            </button>
                                        </div>
                                    </div>
                                </form>
                            </div>
                        </div>
                    </div>

                    <div class="table-responsive">
                        <table class="table table-hover">
                            <thead class="bg-light">
                                <tr>
                                    <th>Deliverable</th>
                                    <th>Type</th>
                                    <th>KPI</th>
                                    <th>Due Date</th>
                                    <th>Status</th>
                                </tr>
                            </thead>
                            <tbody>
                                @forelse($contract->deliverables as $deliverable)
                                    <tr>
                                        <td><strong>{{ $deliverable->deliverable_name }}</strong></td>
                                        <td>{{ $deliverable->deliverable_type }}</td>
                                        <td>{{ $deliverable->kpi ?? 'N/A' }}</td>
                                        <td>{{ $deliverable->due_date ? \Carbon\Carbon::parse($deliverable->due_date)->format('M d, Y') : 'N/A' }}</td>
                                        <td>
                                            @php
                                                $deliverableColors = [
                                                    'pending' => 'warning',
                                                    'in_progress' => 'info',
                                                    'completed' => 'success',
                                                    'under_review' => 'primary',
                                                ];
                                                $color = $deliverableColors[$deliverable->status] ?? 'secondary';
                                            @endphp
                                            <span class="badge bg-{{ $color }}">{{ ucfirst(str_replace('_', ' ', $deliverable->status)) }}</span>
                                        </td>
                                    </tr>
                                @empty
                                    <tr>
                                        <td colspan="5" class="text-center text-muted py-4">No deliverables defined yet.</td>
                                    </tr>
                                @endforelse
                            </tbody>
                        </table>
                    </div>
                @endif

                {{-- Renewals Tab --}}
                @if($activeTab === 'renewals')
                    <div class="row mb-4">
                        <div class="col-md-12">
                            <button class="btn btn-sm btn-primary" data-bs-toggle="collapse" data-bs-target="#renewContractForm">
                                <i class="bi bi-arrow-repeat me-2"></i>Renew Contract
                            </button>
                        </div>
                    </div>

                    <div class="collapse mb-4" id="renewContractForm">
                        <div class="card">
                            <div class="card-body">
                                <form wire:submit.prevent="renewContract">
                                    <div class="row g-3">
                                        <div class="col-md-6">
                                            <label class="form-label">New End Date*</label>
                                            <input type="date" wire:model="newEndDate" class="form-control" required>
                                        </div>
                                        <div class="col-md-6">
                                            <label class="form-label">New Contract Value</label>
                                            <input type="number" step="0.01" wire:model="newValue" class="form-control">
                                        </div>
                                        <div class="col-md-12">
                                            <label class="form-label">Remarks</label>
                                            <textarea wire:model="renewalRemarks" class="form-control" rows="3"></textarea>
                                        </div>
                                        <div class="col-12">
                                            <button type="submit" class="btn btn-primary">
                                                <i class="bi bi-arrow-repeat me-2"></i>Renew Contract
                                            </button>
                                        </div>
                                    </div>
                                </form>
                            </div>
                        </div>
                    </div>

                    <div class="table-responsive">
                        <table class="table table-hover">
                            <thead class="bg-light">
                                <tr>
                                    <th>Old End Date</th>
                                    <th>New End Date</th>
                                    <th>New Value</th>
                                    <th>Remarks</th>
                                    <th>Renewed By</th>
                                    <th>Date</th>
                                </tr>
                            </thead>
                            <tbody>
                                @forelse($contract->renewals as $renewal)
                                    <tr>
                                        <td>{{ \Carbon\Carbon::parse($renewal->old_end_date)->format('M d, Y') }}</td>
                                        <td><strong>{{ \Carbon\Carbon::parse($renewal->new_end_date)->format('M d, Y') }}</strong></td>
                                        <td>{{ $renewal->new_value ? number_format($renewal->new_value, 2) : 'N/A' }}</td>
                                        <td>{{ $renewal->remarks ?? 'N/A' }}</td>
                                        <td>{{ $renewal->addedBy->name }}</td>
                                        <td>{{ $renewal->created_at->format('M d, Y') }}</td>
                                    </tr>
                                @empty
                                    <tr>
                                        <td colspan="6" class="text-center text-muted py-4">No renewal history.</td>
                                    </tr>
                                @endforelse
                            </tbody>
                        </table>
                    </div>
                @endif

                {{-- Approvals Tab --}}
                @if($activeTab === 'approvals')
                    <div class="table-responsive">
                        <table class="table table-hover">
                            <thead class="bg-light">
                                <tr>
                                    <th>Stage</th>
                                    <th>Approver</th>
                                    <th>Status</th>
                                    <th>Comments</th>
                                    <th>Date</th>
                                    <th>Action</th>
                                </tr>
                            </thead>
                            <tbody>
                                @forelse($contract->approvals as $approval)
                                    <tr>
                                        <td><strong>{{ $approval->stage }}</strong></td>
                                        <td>{{ $approval->approver_name }}</td>
                                        <td>
                                            @php
                                                $approvalColors = [
                                                    'pending' => 'warning',
                                                    'approved' => 'success',
                                                    'rejected' => 'danger',
                                                ];
                                                $color = $approvalColors[$approval->status] ?? 'secondary';
                                            @endphp
                                            <span class="badge bg-{{ $color }}">{{ ucfirst($approval->status) }}</span>
                                        </td>
                                        <td>{{ $approval->comments ?? 'N/A' }}</td>
                                        <td>{{ $approval->updated_at->format('M d, Y H:i') }}</td>
                                        <td>
                                            @if($approval->status === 'pending')
                                                <button wire:click="approveContract('{{ $approval->id }}')"
                                                        class="btn btn-sm btn-success"
                                                        wire:confirm="Are you sure you want to approve this stage?">
                                                    <i class="bi bi-check-circle"></i> Approve
                                                </button>
                                                <button wire:click="rejectContract('{{ $approval->id }}')"
                                                        class="btn btn-sm btn-danger"
                                                        wire:confirm="Are you sure you want to reject this stage?">
                                                    <i class="bi bi-x-circle"></i> Reject
                                                </button>
                                            @else
                                                <span class="text-muted">-</span>
                                            @endif
                                        </td>
                                    </tr>
                                @empty
                                    <tr>
                                        <td colspan="6" class="text-center text-muted py-4">No approval workflow initiated.</td>
                                    </tr>
                                @endforelse
                            </tbody>
                        </table>
                    </div>
                @endif
            </div>
        </div>
    </div>
</div>
