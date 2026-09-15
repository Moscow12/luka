<div>
    <div class="row mb-4">
        <div class="col-12">
            <div class="d-md-flex justify-content-between align-items-center">
                <div>
                    <h4 class="mb-1">My Performance Evaluations</h4>
                    <p class="text-muted mb-0">Submit and view your performance evaluations</p>
                </div>
            </div>
        </div>
    </div>

    @if(!$hasEmployeeRecord)
        <div class="alert alert-warning">
            <i class="fa-solid fa-exclamation-triangle me-2"></i>
            Your user account is not linked to an employee record. Please contact HR.
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

        <div class="row">
            {{-- Plans Ready for Evaluation --}}
            <div class="col-lg-4 mb-4">
                <div class="card">
                    <div class="card-header bg-primary text-white">
                        <h6 class="mb-0"><i class="fa-solid fa-clipboard-check me-2"></i>Ready for Evaluation</h6>
                    </div>
                    <div class="card-body">
                        @if($plansReadyForEvaluation->count() > 0)
                            <p class="text-muted small mb-3">Select a plan to submit for evaluation:</p>
                            @foreach($plansReadyForEvaluation as $plan)
                                <div class="card mb-2 border">
                                    <div class="card-body p-3">
                                        <div class="d-flex justify-content-between align-items-start">
                                            <div>
                                                <strong>{{ $plan->plan_name }}</strong>
                                                <br><small class="text-muted">{{ $plan->employeePlanItems->count() }} goals</small>
                                            </div>
                                            <span class="badge bg-{{ $plan->status === 'active' ? 'success' : 'secondary' }}">
                                                {{ ucfirst($plan->status) }}
                                            </span>
                                        </div>
                                        <button class="btn btn-sm btn-primary mt-2 w-100"
                                                wire:click="openSubmitModal('{{ $plan->id }}')">
                                            <i class="fa-solid fa-paper-plane me-1"></i> Submit for Evaluation
                                        </button>
                                    </div>
                                </div>
                            @endforeach
                        @else
                            <div class="text-center text-muted py-4">
                                <i class="fa-solid fa-check-circle fa-2x mb-2 text-success"></i>
                                <p class="mb-0">All plans submitted or no active plans available</p>
                            </div>
                        @endif
                    </div>
                </div>
            </div>

            {{-- Evaluations List --}}
            <div class="col-lg-8">
                <div class="card">
                    <div class="card-header">
                        <div class="row align-items-center">
                            <div class="col-md-6">
                                <h6 class="mb-0"><i class="fa-solid fa-chart-bar me-2"></i>My Evaluations</h6>
                            </div>
                            <div class="col-md-6">
                                <select class="form-select form-select-sm" wire:model.live="filterStatus">
                                    <option value="">All Status</option>
                                    <option value="draft">Draft</option>
                                    <option value="submitted">Submitted</option>
                                    <option value="under_review">Under Review</option>
                                    <option value="approved">Approved</option>
                                    <option value="rejected">Rejected</option>
                                </select>
                            </div>
                        </div>
                    </div>
                    <div class="card-body p-0">
                        @if($evaluations->count() > 0)
                            <div class="table-responsive">
                                <table class="table table-hover mb-0">
                                    <thead class="table-light">
                                        <tr>
                                            <th>Plan</th>
                                            <th class="text-center">Self Score</th>
                                            <th class="text-center">Supervisor Score</th>
                                            <th class="text-center">Final Score</th>
                                            <th class="text-center">Status</th>
                                            <th>Submitted</th>
                                            <th>Actions</th>
                                        </tr>
                                    </thead>
                                    <tbody>
                                        @foreach($evaluations as $eval)
                                            <tr>
                                                <td>
                                                    <strong>{{ $eval->employeePlan->plan_name ?? 'N/A' }}</strong>
                                                </td>
                                                <td class="text-center">
                                                    <span class="badge bg-info fs-6">{{ number_format($eval->self_score ?? 0, 1) }}%</span>
                                                </td>
                                                <td class="text-center">
                                                    @if($eval->supervisor_score)
                                                        <span class="badge bg-primary fs-6">{{ number_format($eval->supervisor_score, 1) }}%</span>
                                                    @else
                                                        <span class="text-muted">-</span>
                                                    @endif
                                                </td>
                                                <td class="text-center">
                                                    @if($eval->final_score)
                                                        <span class="badge bg-success fs-6">{{ number_format($eval->final_score, 1) }}%</span>
                                                    @else
                                                        <span class="text-muted">-</span>
                                                    @endif
                                                </td>
                                                <td class="text-center">
                                                    <span class="badge bg-{{ $eval->status_badge }}">
                                                        {{ $eval->status_label }}
                                                    </span>
                                                </td>
                                                <td>
                                                    @if($eval->submitted_at)
                                                        <small>{{ $eval->submitted_at->format('d M Y') }}</small>
                                                    @else
                                                        <small class="text-muted">Not submitted</small>
                                                    @endif
                                                </td>
                                                <td>
                                                    <button class="btn btn-sm btn-outline-primary"
                                                            wire:click="viewEvaluation('{{ $eval->id }}')">
                                                        <i class="fa-solid fa-eye"></i> View
                                                    </button>
                                                </td>
                                            </tr>
                                            @if($eval->status === 'rejected' && $eval->rejection_reason)
                                                <tr class="table-danger">
                                                    <td colspan="7">
                                                        <small><strong>Rejection Reason:</strong> {{ $eval->rejection_reason }}</small>
                                                    </td>
                                                </tr>
                                            @endif
                                        @endforeach
                                    </tbody>
                                </table>
                            </div>
                            <div class="card-footer">
                                {{ $evaluations->links() }}
                            </div>
                        @else
                            <div class="text-center text-muted py-5">
                                <i class="fa-solid fa-chart-line fa-3x mb-3"></i>
                                <h5>No Evaluations Yet</h5>
                                <p>Submit a plan for evaluation to see your performance scores.</p>
                            </div>
                        @endif
                    </div>
                </div>
            </div>
        </div>

        {{-- Submit Evaluation Modal --}}
        @if($showSubmitModal)
            <div class="modal fade show d-block" tabindex="-1" style="background: rgba(0,0,0,0.5);">
                <div class="modal-dialog">
                    <div class="modal-content">
                        <div class="modal-header bg-primary text-white">
                            <h5 class="modal-title"><i class="fa-solid fa-paper-plane me-2"></i>Submit for Evaluation</h5>
                            <button type="button" class="btn-close btn-close-white" wire:click="$set('showSubmitModal', false)"></button>
                        </div>
                        <form wire:submit="submitForEvaluation">
                            <div class="modal-body">
                                <div class="alert alert-info">
                                    <i class="fa-solid fa-info-circle me-2"></i>
                                    Your self-score will be automatically calculated based on your recorded implementations.
                                </div>
                                <div class="mb-3">
                                    <label class="form-label">Comments (Optional)</label>
                                    <textarea class="form-control" wire:model="employee_comments" rows="4"
                                              placeholder="Add any comments about your performance during this period..."></textarea>
                                </div>
                            </div>
                            <div class="modal-footer">
                                <button type="button" class="btn btn-secondary" wire:click="$set('showSubmitModal', false)">Cancel</button>
                                <button type="submit" class="btn btn-primary">
                                    <i class="fa-solid fa-paper-plane me-1"></i> Submit
                                </button>
                            </div>
                        </form>
                    </div>
                </div>
            </div>
        @endif

        {{-- Evaluation Detail Modal --}}
        @if($showDetailModal && $selectedEvaluation)
            <div class="modal fade show d-block" tabindex="-1" style="background: rgba(0,0,0,0.5);">
                <div class="modal-dialog modal-xl">
                    <div class="modal-content">
                        <div class="modal-header bg-{{ $selectedEvaluation->status_badge }} text-white">
                            <h5 class="modal-title">
                                <i class="fa-solid fa-chart-bar me-2"></i>
                                Evaluation Details - {{ $selectedEvaluation->status_label }}
                            </h5>
                            <button type="button" class="btn-close btn-close-white" wire:click="closeDetailModal"></button>
                        </div>
                        <div class="modal-body">
                            {{-- Scores Summary --}}
                            <div class="row mb-4">
                                <div class="col-md-4">
                                    <div class="card bg-info text-white">
                                        <div class="card-body text-center">
                                            <h6>Self Score</h6>
                                            <h2>{{ number_format($selectedEvaluation->self_score ?? 0, 1) }}%</h2>
                                        </div>
                                    </div>
                                </div>
                                <div class="col-md-4">
                                    <div class="card bg-primary text-white">
                                        <div class="card-body text-center">
                                            <h6>Supervisor Score</h6>
                                            <h2>{{ $selectedEvaluation->supervisor_score ? number_format($selectedEvaluation->supervisor_score, 1) . '%' : '-' }}</h2>
                                        </div>
                                    </div>
                                </div>
                                <div class="col-md-4">
                                    <div class="card bg-success text-white">
                                        <div class="card-body text-center">
                                            <h6>Final Score</h6>
                                            <h2>{{ $selectedEvaluation->final_score ? number_format($selectedEvaluation->final_score, 1) . '%' : '-' }}</h2>
                                        </div>
                                    </div>
                                </div>
                            </div>

                            {{-- Goals Progress --}}
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
                                                    <td>{{ $item->item_name }}</td>
                                                    <td class="text-center">{{ $item->weight }}%</td>
                                                    <td class="text-center">{{ number_format($item->target_value ?? 0) }} {{ $item->target_unit }}</td>
                                                    <td class="text-center">{{ number_format($item->total_achieved ?? 0) }} {{ $item->target_unit }}</td>
                                                    <td class="text-center">
                                                        <div class="progress" style="height: 20px;">
                                                            <div class="progress-bar {{ $item->progress_percentage >= 100 ? 'bg-success' : 'bg-info' }}"
                                                                 style="width: {{ $item->progress_percentage }}%">
                                                                {{ number_format($item->progress_percentage, 0) }}%
                                                            </div>
                                                        </div>
                                                    </td>
                                                </tr>
                                            @endforeach
                                        </tbody>
                                    </table>
                                </div>
                            @endif

                            {{-- Feedback Section (only for approved) --}}
                            @if($selectedEvaluation->status === 'approved')
                                <div class="row">
                                    @if($selectedEvaluation->strengths)
                                        <div class="col-md-4 mb-3">
                                            <div class="card h-100 border-success">
                                                <div class="card-header bg-success text-white">
                                                    <i class="fa-solid fa-star me-2"></i>Strengths
                                                </div>
                                                <div class="card-body">{{ $selectedEvaluation->strengths }}</div>
                                            </div>
                                        </div>
                                    @endif
                                    @if($selectedEvaluation->areas_for_improvement)
                                        <div class="col-md-4 mb-3">
                                            <div class="card h-100 border-warning">
                                                <div class="card-header bg-warning text-dark">
                                                    <i class="fa-solid fa-chart-line me-2"></i>Areas for Improvement
                                                </div>
                                                <div class="card-body">{{ $selectedEvaluation->areas_for_improvement }}</div>
                                            </div>
                                        </div>
                                    @endif
                                    @if($selectedEvaluation->recommendations)
                                        <div class="col-md-4 mb-3">
                                            <div class="card h-100 border-info">
                                                <div class="card-header bg-info text-white">
                                                    <i class="fa-solid fa-lightbulb me-2"></i>Recommendations
                                                </div>
                                                <div class="card-body">{{ $selectedEvaluation->recommendations }}</div>
                                            </div>
                                        </div>
                                    @endif
                                </div>

                                @if($selectedEvaluation->supervisor_comments)
                                    <div class="card mb-3">
                                        <div class="card-header">
                                            <i class="fa-solid fa-comment me-2"></i>Supervisor Comments
                                        </div>
                                        <div class="card-body">{{ $selectedEvaluation->supervisor_comments }}</div>
                                    </div>
                                @endif
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
                        </div>
                    </div>
                </div>
            </div>
        @endif
    @endif
</div>
