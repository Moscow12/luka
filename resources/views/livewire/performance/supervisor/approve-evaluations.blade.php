<div>
    <div class="row mb-4">
        <div class="col-12">
            <div class="d-md-flex justify-content-between align-items-center">
                <div>
                    <h4 class="mb-1">Performance Evaluation Approvals</h4>
                    <p class="text-muted mb-0">Review and approve staff performance evaluations</p>
                </div>
            </div>
        </div>
    </div>

    @if(!$hasEmployeeRecord)
        <div class="alert alert-warning">
            <i class="fa-solid fa-exclamation-triangle me-2"></i>
            Your user account is not linked to an employee record. Please contact HR.
        </div>
    @elseif(!$canApprove)
        <div class="alert alert-info">
            <i class="fa-solid fa-info-circle me-2"></i>
            You are not assigned to any approval levels for Performance documents.
            Please contact your administrator to set up approval permissions.
        </div>
    @else
        @if(session()->has('success'))
            <div class="alert alert-success alert-dismissible fade show" role="alert">
                {{ session('success') }}
                <button type="button" class="btn-close" data-bs-dismiss="alert"></button>
            </div>
        @endif
        @if(session()->has('error'))
            <div class="alert alert-danger alert-dismissible fade show" role="alert">
                {{ session('error') }}
                <button type="button" class="btn-close" data-bs-dismiss="alert"></button>
            </div>
        @endif

        {{-- My Approval Levels Info --}}
        <div class="alert alert-primary mb-4">
            <i class="fa-solid fa-shield-check me-2"></i>
            <strong>Your Approval Levels:</strong>
            @foreach($myApprovalLevels as $level)
                <span class="badge bg-primary ms-1">{{ $level->name }} (Level {{ $level->level_order }})</span>
            @endforeach
        </div>

        {{-- Filters --}}
        <div class="card mb-4">
            <div class="card-body">
                <div class="row align-items-center">
                    <div class="col-md-4">
                        <label class="form-label">Filter by Department</label>
                        <select class="form-select" wire:model.live="filterDepartment">
                            <option value="">All Departments</option>
                            @foreach($departments as $dept)
                                <option value="{{ $dept->id }}">{{ $dept->name }}</option>
                            @endforeach
                        </select>
                    </div>
                    <div class="col-md-4">
                        <label class="form-label">Pending Count</label>
                        <div class="h4 mb-0">
                            <span class="badge bg-warning">{{ $pendingEvaluations->count() }} pending</span>
                        </div>
                    </div>
                </div>
            </div>
        </div>

        {{-- Pending Evaluations --}}
        <div class="card">
            <div class="card-header">
                <h6 class="mb-0"><i class="fa-solid fa-clipboard-list me-2"></i>Pending Evaluations</h6>
            </div>
            <div class="card-body p-0">
                @if($pendingEvaluations->count() > 0)
                    <div class="table-responsive">
                        <table class="table table-hover mb-0">
                            <thead class="table-light">
                                <tr>
                                    <th>Employee</th>
                                    <th>Department</th>
                                    <th>Plan</th>
                                    <th class="text-center">Self Score</th>
                                    <th class="text-center">Level</th>
                                    <th>Submitted</th>
                                    <th>Actions</th>
                                </tr>
                            </thead>
                            <tbody>
                                @foreach($pendingEvaluations as $eval)
                                    <tr>
                                        <td>
                                            <strong>{{ $eval->employee->getFullName() }}</strong>
                                            <br><small class="text-muted">{{ $eval->employee->employee_no }}</small>
                                        </td>
                                        <td>{{ $eval->employee->department->name ?? 'N/A' }}</td>
                                        <td>{{ $eval->employeePlan->plan_name ?? 'N/A' }}</td>
                                        <td class="text-center">
                                            <span class="badge bg-info fs-6">{{ number_format($eval->self_score ?? 0, 1) }}%</span>
                                        </td>
                                        <td class="text-center">
                                            <span class="badge bg-secondary">Level {{ $eval->current_approval_level }}</span>
                                        </td>
                                        <td>
                                            @if($eval->submitted_at)
                                                <small>{{ $eval->submitted_at->format('d M Y') }}</small>
                                                <br><small class="text-muted">{{ $eval->submitted_at->diffForHumans() }}</small>
                                            @endif
                                        </td>
                                        <td>
                                            <button class="btn btn-sm btn-outline-info me-1"
                                                    wire:click="viewEvaluation('{{ $eval->id }}')">
                                                <i class="fa-solid fa-eye"></i>
                                            </button>
                                            <button class="btn btn-sm btn-primary"
                                                    wire:click="openApprovalModal('{{ $eval->id }}')">
                                                <i class="fa-solid fa-check-circle"></i> Review
                                            </button>
                                        </td>
                                    </tr>
                                @endforeach
                            </tbody>
                        </table>
                    </div>
                @else
                    <div class="text-center text-muted py-5">
                        <i class="fa-solid fa-check-circle fa-3x mb-3 text-success"></i>
                        <h5>No Pending Evaluations</h5>
                        <p>All evaluations at your approval level have been processed.</p>
                    </div>
                @endif
            </div>
        </div>

        {{-- Approval Modal --}}
        @if($showApprovalModal)
            <div class="modal fade show d-block" tabindex="-1" style="background: rgba(0,0,0,0.5);">
                <div class="modal-dialog modal-lg">
                    <div class="modal-content">
                        <div class="modal-header bg-primary text-white">
                            <h5 class="modal-title"><i class="fa-solid fa-clipboard-check me-2"></i>Review Performance Evaluation</h5>
                            <button type="button" class="btn-close btn-close-white" wire:click="$set('showApprovalModal', false)"></button>
                        </div>
                        <form wire:submit="processApproval">
                            <div class="modal-body">
                                <div class="row mb-4">
                                    <div class="col-md-6">
                                        <label class="form-label">Supervisor Score <span class="text-danger">*</span></label>
                                        <div class="input-group">
                                            <input type="number" class="form-control @error('supervisor_score') is-invalid @enderror"
                                                   wire:model="supervisor_score" min="0" max="100" step="0.1">
                                            <span class="input-group-text">%</span>
                                        </div>
                                        @error('supervisor_score') <div class="text-danger small">{{ $message }}</div> @enderror
                                    </div>
                                    <div class="col-md-6">
                                        <label class="form-label">Decision <span class="text-danger">*</span></label>
                                        <select class="form-select @error('approval_status') is-invalid @enderror" wire:model.live="approval_status">
                                            <option value="approved">Approve</option>
                                            <option value="rejected">Reject</option>
                                        </select>
                                    </div>
                                </div>

                                @if($approval_status === 'rejected')
                                    <div class="mb-3">
                                        <label class="form-label">Rejection Reason <span class="text-danger">*</span></label>
                                        <textarea class="form-control @error('rejection_reason') is-invalid @enderror"
                                                  wire:model="rejection_reason" rows="3"
                                                  placeholder="Explain why the evaluation is being rejected..."></textarea>
                                        @error('rejection_reason') <div class="invalid-feedback">{{ $message }}</div> @enderror
                                    </div>
                                @else
                                    <div class="mb-3">
                                        <label class="form-label">Supervisor Comments</label>
                                        <textarea class="form-control" wire:model="supervisor_comments" rows="3"
                                                  placeholder="General feedback for the employee..."></textarea>
                                    </div>

                                    <div class="row">
                                        <div class="col-md-4 mb-3">
                                            <label class="form-label">Strengths</label>
                                            <textarea class="form-control" wire:model="strengths" rows="3"
                                                      placeholder="What the employee did well..."></textarea>
                                        </div>
                                        <div class="col-md-4 mb-3">
                                            <label class="form-label">Areas for Improvement</label>
                                            <textarea class="form-control" wire:model="areas_for_improvement" rows="3"
                                                      placeholder="What needs to be improved..."></textarea>
                                        </div>
                                        <div class="col-md-4 mb-3">
                                            <label class="form-label">Recommendations</label>
                                            <textarea class="form-control" wire:model="recommendations" rows="3"
                                                      placeholder="Training, development suggestions..."></textarea>
                                        </div>
                                    </div>
                                @endif
                            </div>
                            <div class="modal-footer">
                                <button type="button" class="btn btn-secondary" wire:click="$set('showApprovalModal', false)">Cancel</button>
                                <button type="submit" class="btn btn-{{ $approval_status === 'approved' ? 'success' : 'danger' }}">
                                    <i class="fa-solid fa-{{ $approval_status === 'approved' ? 'check' : 'times' }} me-1"></i>
                                    {{ $approval_status === 'approved' ? 'Approve' : 'Reject' }} Evaluation
                                </button>
                            </div>
                        </form>
                    </div>
                </div>
            </div>
        @endif

        {{-- Detail Modal --}}
        @if($showDetailModal && $selectedEvaluation)
            <div class="modal fade show d-block" tabindex="-1" style="background: rgba(0,0,0,0.5);">
                <div class="modal-dialog modal-xl">
                    <div class="modal-content">
                        <div class="modal-header bg-info text-white">
                            <h5 class="modal-title">
                                <i class="fa-solid fa-user me-2"></i>
                                {{ $selectedEvaluation->employee->getFullName() }} - Evaluation Details
                            </h5>
                            <button type="button" class="btn-close btn-close-white" wire:click="closeDetailModal"></button>
                        </div>
                        <div class="modal-body">
                            {{-- Employee Info --}}
                            <div class="row mb-4">
                                <div class="col-md-6">
                                    <div class="card">
                                        <div class="card-body">
                                            <h6 class="card-title">Employee Information</h6>
                                            <p class="mb-1"><strong>Name:</strong> {{ $selectedEvaluation->employee->getFullName() }}</p>
                                            <p class="mb-1"><strong>Employee No:</strong> {{ $selectedEvaluation->employee->employee_no }}</p>
                                            <p class="mb-1"><strong>Department:</strong> {{ $selectedEvaluation->employee->department->name ?? 'N/A' }}</p>
                                            <p class="mb-0"><strong>Position:</strong> {{ $selectedEvaluation->employee->position->title ?? 'N/A' }}</p>
                                        </div>
                                    </div>
                                </div>
                                <div class="col-md-6">
                                    <div class="card bg-light">
                                        <div class="card-body text-center">
                                            <h6>Self Score</h6>
                                            <h1 class="text-info">{{ number_format($selectedEvaluation->self_score ?? 0, 1) }}%</h1>
                                            @if($selectedEvaluation->employee_comments)
                                                <p class="text-muted small mb-0">"{{ Str::limit($selectedEvaluation->employee_comments, 100) }}"</p>
                                            @endif
                                        </div>
                                    </div>
                                </div>
                            </div>

                            {{-- Goals & Achievements --}}
                            @if($selectedEvaluation->employeePlan)
                                <h6 class="border-bottom pb-2 mb-3">Goals & Achievements</h6>
                                <div class="table-responsive mb-4">
                                    <table class="table table-sm table-bordered">
                                        <thead class="table-light">
                                            <tr>
                                                <th>Goal</th>
                                                <th class="text-center">Weight</th>
                                                <th class="text-center">Target</th>
                                                <th class="text-center">Achieved</th>
                                                <th class="text-center">Progress</th>
                                            </tr>
                                        </thead>
                                        <tbody>
                                            @foreach($selectedEvaluation->employeePlan->employeePlanItems as $item)
                                                <tr>
                                                    <td>
                                                        {{ $item->item_name }}
                                                        @if($item->description)
                                                            <br><small class="text-muted">{{ Str::limit($item->description, 50) }}</small>
                                                        @endif
                                                    </td>
                                                    <td class="text-center">{{ $item->weight }}%</td>
                                                    <td class="text-center">{{ number_format($item->target_value ?? 0) }} {{ $item->target_unit }}</td>
                                                    <td class="text-center">{{ number_format($item->total_achieved ?? 0) }} {{ $item->target_unit }}</td>
                                                    <td class="text-center" style="min-width: 150px;">
                                                        <div class="progress" style="height: 20px;">
                                                            <div class="progress-bar {{ $item->progress_percentage >= 100 ? 'bg-success' : ($item->progress_percentage >= 50 ? 'bg-info' : 'bg-warning') }}"
                                                                 style="width: {{ $item->progress_percentage }}%">
                                                                {{ number_format($item->progress_percentage, 0) }}%
                                                            </div>
                                                        </div>
                                                    </td>
                                                </tr>
                                                {{-- Show implementations if any --}}
                                                @if($item->implementations->count() > 0)
                                                    <tr class="table-light">
                                                        <td colspan="5">
                                                            <small><strong>Recent Implementations:</strong></small>
                                                            <ul class="mb-0 small">
                                                                @foreach($item->implementations->take(3) as $impl)
                                                                    <li>
                                                                        {{ $impl->activity_title }}
                                                                        ({{ number_format($impl->quantity_achieved ?? 0) }} {{ $impl->unit }})
                                                                        - {{ $impl->implementation_date->format('d M Y') }}
                                                                        <span class="badge bg-{{ $impl->status === 'verified' ? 'success' : ($impl->status === 'rejected' ? 'danger' : 'warning') }}">
                                                                            {{ $impl->status }}
                                                                        </span>
                                                                    </li>
                                                                @endforeach
                                                                @if($item->implementations->count() > 3)
                                                                    <li class="text-muted">... and {{ $item->implementations->count() - 3 }} more</li>
                                                                @endif
                                                            </ul>
                                                        </td>
                                                    </tr>
                                                @endif
                                            @endforeach
                                        </tbody>
                                    </table>
                                </div>
                            @endif

                            {{-- Approval History --}}
                            @if($selectedEvaluation->approvals->count() > 0)
                                <h6 class="border-bottom pb-2 mb-3">Approval History</h6>
                                <div class="table-responsive">
                                    <table class="table table-sm">
                                        <thead class="table-light">
                                            <tr>
                                                <th>Level</th>
                                                <th>Approved By</th>
                                                <th>Status</th>
                                                <th>Date</th>
                                                <th>Remarks</th>
                                            </tr>
                                        </thead>
                                        <tbody>
                                            @foreach($selectedEvaluation->approvals as $approval)
                                                <tr>
                                                    <td>{{ $approval->approvalLevel->name ?? 'N/A' }}</td>
                                                    <td>{{ $approval->approver->getFullName() ?? 'N/A' }}</td>
                                                    <td>
                                                        <span class="badge bg-{{ $approval->status === 'approved' ? 'success' : 'danger' }}">
                                                            {{ ucfirst($approval->status) }}
                                                        </span>
                                                    </td>
                                                    <td>{{ $approval->approved_at->format('d M Y H:i') }}</td>
                                                    <td>{{ $approval->remarks ?? '-' }}</td>
                                                </tr>
                                            @endforeach
                                        </tbody>
                                    </table>
                                </div>
                            @endif
                        </div>
                        <div class="modal-footer">
                            <button type="button" class="btn btn-secondary" wire:click="closeDetailModal">Close</button>
                            <button type="button" class="btn btn-primary" wire:click="closeDetailModal" wire:click="openApprovalModal('{{ $selectedEvaluation->id }}')">
                                <i class="fa-solid fa-clipboard-check me-1"></i> Review & Approve
                            </button>
                        </div>
                    </div>
                </div>
            </div>
        @endif
    @endif
</div>
