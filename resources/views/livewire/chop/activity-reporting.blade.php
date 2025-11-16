<div>
    <!-- Breadcrumb -->
    <nav aria-label="breadcrumb">
        <ol class="breadcrumb">
            <li class="breadcrumb-item"><a href="{{ route('dashboard') }}">Dashboard</a></li>
            <li class="breadcrumb-item"><a href="{{ route('chop.settings') }}">CHOP</a></li>
            <li class="breadcrumb-item active" aria-current="page">My Activity Reports</li>
        </ol>
    </nav>

    <!-- Page Header -->
    <div class="mb-4">
        <h2 class="mb-1"><i class="fa-solid fa-file-alt text-primary"></i> My Activity Reports</h2>
        <p class="text-muted">Report on activities you're responsible for</p>
    </div>

    <!-- Success Message -->
    @if(session()->has('success'))
        <div class="alert alert-success alert-dismissible fade show shadow-sm mb-4" role="alert">
            <i class="fa-solid fa-circle-check"></i> {{ session('success') }}
            <button type="button" class="btn-close" data-bs-dismiss="alert"></button>
        </div>
    @endif

    @if(session()->has('error'))
        <div class="alert alert-danger alert-dismissible fade show shadow-sm mb-4" role="alert">
            <i class="fa-solid fa-circle-exclamation"></i> {{ session('error') }}
            <button type="button" class="btn-close" data-bs-dismiss="alert"></button>
        </div>
    @endif

    <!-- My Activities to Report -->
    <div class="card shadow-sm mb-4">
        <div class="card-header bg-white">
            <h5 class="mb-0"><i class="fa-solid fa-tasks"></i> Activities Assigned to Me</h5>
        </div>
        <div class="card-body">
            @if($myActivities->count() > 0)
                <div class="row g-3">
                    @foreach($myActivities as $activity)
                        <div class="col-md-6 col-lg-4">
                            <div class="card border-start border-4 h-100" style="border-left-color: {{ $activity->source->colorcode ?? '#6c757d' }} !important;">
                                <div class="card-body">
                                    <h6 class="card-title mb-2">{{ $activity->planned_activity }}</h6>
                                    <p class="card-text small text-muted mb-2">
                                        <i class="fa-solid fa-tag"></i> {{ $activity->category->name ?? 'N/A' }}
                                    </p>
                                    <p class="card-text small mb-2">
                                        <strong>Budget:</strong> {{ number_format($activity->planned_amount, 0) }} TZS
                                    </p>
                                    <p class="card-text small mb-3">
                                        <strong>Frequency:</strong>
                                        <span class="badge bg-secondary-subtle text-secondary">
                                            {{ \App\Models\chopactivities::getFrequencyOptions()[$activity->frequence_monitoring] ?? 'N/A' }}
                                        </span>
                                    </p>
                                    <button class="btn btn-sm btn-primary w-100" wire:click="openReportModal('{{ $activity->id }}')">
                                        <i class="fa-solid fa-plus"></i> Submit Report
                                    </button>
                                </div>
                            </div>
                        </div>
                    @endforeach
                </div>
            @else
                <div class="text-center py-4">
                    <i class="fa-solid fa-inbox fa-3x mb-3 text-muted opacity-25"></i>
                    <h5 class="text-muted">No Activities Assigned</h5>
                    <p class="mb-0">You don't have any activities assigned to your job title</p>
                </div>
            @endif
        </div>
    </div>

    <!-- Filters -->
    <div class="card shadow-sm mb-4">
        <div class="card-body">
            <div class="row g-3">
                <div class="col-md-4">
                    <input type="text" wire:model.live="search" class="form-control"
                           placeholder="Search activities...">
                </div>
                <div class="col-md-3">
                    <input type="month" wire:model.live="filterMonth" class="form-control">
                </div>
                <div class="col-md-2">
                    <select class="form-select" wire:model.live="filterStatus">
                        <option value="">All Status</option>
                        @foreach($statusOptions as $key => $label)
                            <option value="{{ $key }}">{{ $label }}</option>
                        @endforeach
                    </select>
                </div>
                <div class="col-md-2">
                    <select class="form-select" wire:model.live="filterFinancialYear">
                        <option value="">All FY</option>
                        @foreach($financialYears as $fy)
                            <option value="{{ $fy->id }}">{{ $fy->name }}</option>
                        @endforeach
                    </select>
                </div>
                <div class="col-md-1">
                    <button class="btn btn-secondary w-100" wire:click="$set('filterMonth', ''); $set('filterStatus', ''); $set('search', '');" title="Clear Filters">
                        <i class="fa-solid fa-filter-circle-xmark"></i>
                    </button>
                </div>
            </div>
        </div>
    </div>

    <!-- Submitted Reports -->
    <div class="card shadow-sm">
        <div class="card-header bg-white">
            <h5 class="mb-0"><i class="fa-solid fa-file-lines"></i> Submitted Reports</h5>
        </div>
        <div class="card-body p-0">
            <div class="table-responsive">
                <table class="table table-hover mb-0">
                    <thead class="table-light">
                        <tr>
                            <th>Activity</th>
                            <th>Month</th>
                            <th>Status</th>
                            <th class="text-end">Amount Spent</th>
                            <th>Completion Date</th>
                            <th>Approval Status</th>
                            <th class="text-center">Actions</th>
                        </tr>
                    </thead>
                    <tbody>
                        @forelse($reports as $report)
                            <tr>
                                <td>
                                    <div class="d-flex align-items-start">
                                        <div
                                            class="me-2"
                                            style="width: 4px; height: 40px; background-color: {{ $report->activity->source->colorcode ?? '#6c757d' }}; border-radius: 2px;"
                                        ></div>
                                        <div>
                                            <strong>{{ $report->activity->planned_activity }}</strong>
                                            <br>
                                            <small class="text-muted">{{ $report->activity->category->name ?? 'N/A' }}</small>
                                        </div>
                                    </div>
                                </td>
                                <td>{{ $report->formatted_month }}</td>
                                <td>
                                    @php
                                        $statusConfig = [
                                            'completed' => ['color' => 'success', 'icon' => 'check-circle'],
                                            'partially_completed' => ['color' => 'warning', 'icon' => 'exclamation-circle'],
                                            'not_completed' => ['color' => 'danger', 'icon' => 'times-circle'],
                                            'cancelled' => ['color' => 'secondary', 'icon' => 'ban'],
                                        ];
                                        $config = $statusConfig[$report->status] ?? ['color' => 'secondary', 'icon' => 'question'];
                                    @endphp
                                    <span class="badge bg-{{ $config['color'] }}-subtle text-{{ $config['color'] }}">
                                        <i class="fa-solid fa-{{ $config['icon'] }}"></i> {{ $statusOptions[$report->status] }}
                                    </span>
                                </td>
                                <td class="text-end">
                                    <strong class="text-danger">{{ number_format($report->amount_spent, 2) }}</strong>
                                </td>
                                <td>{{ $report->actual_completion_date?->format('d M Y') ?? '-' }}</td>
                                <td>
                                    @if($report->is_approved)
                                        <span class="badge bg-success">
                                            <i class="fa-solid fa-check-double"></i> Approved
                                        </span>
                                        <br>
                                        <small class="text-muted">{{ $report->approved_at?->format('d M Y') }}</small>
                                    @else
                                        <span class="badge bg-warning">
                                            <i class="fa-solid fa-clock"></i> Pending
                                        </span>
                                    @endif
                                </td>
                                <td class="text-center">
                                    <div class="btn-group btn-group-sm">
                                        @if(!$report->is_approved)
                                            <button class="btn btn-outline-primary" wire:click="editReport('{{ $report->id }}')" title="Edit Report">
                                                <i class="fa-solid fa-pencil"></i>
                                            </button>
                                            <button class="btn btn-outline-danger" wire:click="deleteReport('{{ $report->id }}')"
                                                    onclick="return confirm('Delete this report?')" title="Delete Report">
                                                <i class="fa-solid fa-trash"></i>
                                            </button>
                                        @else
                                            <button class="btn btn-outline-secondary btn-sm" disabled>
                                                <i class="fa-solid fa-lock"></i> Locked
                                            </button>
                                        @endif
                                    </div>
                                </td>
                            </tr>
                        @empty
                            <tr>
                                <td colspan="7" class="text-center py-5">
                                    <i class="fa-solid fa-inbox fa-3x mb-3 d-block text-muted opacity-25"></i>
                                    <h5 class="text-muted">No Reports Submitted</h5>
                                    <p class="mb-0">Submit your first activity report using the cards above</p>
                                </td>
                            </tr>
                        @endforelse
                    </tbody>
                </table>
            </div>
        </div>
        @if($reports->hasPages())
            <div class="card-footer bg-white">
                {{ $reports->links() }}
            </div>
        @endif
    </div>

    <!-- Report Modal -->
    @if($showReportModal)
    <div class="modal fade show d-block" style="background: rgba(0,0,0,0.7);" tabindex="-1">
        <div class="modal-dialog modal-lg modal-dialog-scrollable">
            <div class="modal-content border-0 shadow-lg">
                <div class="modal-header bg-gradient bg-primary text-white">
                    <h5 class="modal-title">
                        <i class="fa-solid fa-{{ $modalMode === 'edit' ? 'pencil' : 'plus-circle' }}"></i>
                        {{ $modalMode === 'edit' ? 'Edit Activity Report' : 'Submit Activity Report' }}
                    </h5>
                    <button type="button" class="btn-close btn-close-white" wire:click="$set('showReportModal', false)"></button>
                </div>

                <form wire:submit.prevent="saveReport">
                    <div class="modal-body">
                        <div class="alert alert-info border-0 mb-4">
                            <i class="fa-solid fa-info-circle"></i>
                            Fields marked with <span class="text-danger fw-bold">*</span> are required
                        </div>

                        @php
                            $selectedActivity = \App\Models\chopactivities::find($activity_id);
                        @endphp

                        @if($selectedActivity)
                            <div class="card bg-light mb-4">
                                <div class="card-body">
                                    <h6 class="mb-2"><i class="fa-solid fa-info-circle"></i> Activity Details</h6>
                                    <p class="mb-1"><strong>Activity:</strong> {{ $selectedActivity->planned_activity }}</p>
                                    <p class="mb-1"><strong>Planned Budget:</strong> {{ number_format($selectedActivity->planned_amount, 2) }} TZS</p>
                                    <p class="mb-0"><strong>Report Month:</strong> {{ \Carbon\Carbon::parse($report_month . '-01')->format('F Y') }}</p>
                                </div>
                            </div>
                        @endif

                        <div class="row g-3">
                            <div class="col-md-6">
                                <label class="form-label fw-semibold">Status <span class="text-danger">*</span></label>
                                <select class="form-select @error('status') is-invalid @enderror" wire:model="status" required>
                                    @foreach($statusOptions as $key => $label)
                                        <option value="{{ $key }}">{{ $label }}</option>
                                    @endforeach
                                </select>
                                @error('status') <div class="invalid-feedback">{{ $message }}</div> @enderror
                            </div>

                            <div class="col-md-6">
                                <label class="form-label fw-semibold">Actual Completion Date</label>
                                <input type="date" class="form-control @error('actual_completion_date') is-invalid @enderror"
                                       wire:model="actual_completion_date">
                                @error('actual_completion_date') <div class="invalid-feedback">{{ $message }}</div> @enderror
                            </div>

                            <div class="col-md-6">
                                <label class="form-label fw-semibold">Amount Spent (TZS) <span class="text-danger">*</span></label>
                                <input type="number" step="0.01" min="0" class="form-control @error('amount_spent') is-invalid @enderror"
                                       wire:model="amount_spent" placeholder="0.00" required>
                                @error('amount_spent') <div class="invalid-feedback">{{ $message }}</div> @enderror
                            </div>

                            <div class="col-md-6">
                                <label class="form-label fw-semibold">Beneficiaries Reached</label>
                                <input type="number" min="0" class="form-control @error('beneficiaries_reached') is-invalid @enderror"
                                       wire:model="beneficiaries_reached" placeholder="Number of people">
                                @error('beneficiaries_reached') <div class="invalid-feedback">{{ $message }}</div> @enderror
                            </div>

                            <div class="col-12">
                                <label class="form-label fw-semibold">Completion Notes <span class="text-danger">*</span></label>
                                <textarea class="form-control @error('completion_notes') is-invalid @enderror"
                                          wire:model="completion_notes" rows="3"
                                          placeholder="Describe what was accomplished..." required></textarea>
                                @error('completion_notes') <div class="invalid-feedback">{{ $message }}</div> @enderror
                            </div>

                            <div class="col-12">
                                <label class="form-label fw-semibold">Challenges Faced (Optional)</label>
                                <textarea class="form-control" wire:model="challenges_faced" rows="2"
                                          placeholder="Describe any challenges encountered..."></textarea>
                            </div>

                            <div class="col-12">
                                <label class="form-label fw-semibold">Outcomes Achieved (Optional)</label>
                                <textarea class="form-control" wire:model="outcomes_achieved" rows="2"
                                          placeholder="Describe the outcomes and impact..."></textarea>
                            </div>

                            <div class="col-md-6">
                                <label class="form-label fw-semibold">Payment Proof Document</label>
                                <input type="file" class="form-control @error('tempPaymentProof') is-invalid @enderror"
                                       wire:model="tempPaymentProof" accept=".pdf,.jpg,.jpeg,.png">
                                <small class="text-muted">Max 10MB (PDF, JPG, PNG)</small>
                                @error('tempPaymentProof') <div class="invalid-feedback">{{ $message }}</div> @enderror
                                @if($payment_proof_document)
                                    <div class="mt-2">
                                        <a href="{{ Storage::url($payment_proof_document) }}" target="_blank" class="btn btn-sm btn-outline-primary">
                                            <i class="fa-solid fa-file"></i> View Current File
                                        </a>
                                    </div>
                                @endif
                            </div>

                            <div class="col-md-6">
                                <label class="form-label fw-semibold">Activity Proof Document</label>
                                <input type="file" class="form-control @error('tempActivityProof') is-invalid @enderror"
                                       wire:model="tempActivityProof" accept=".pdf,.jpg,.jpeg,.png">
                                <small class="text-muted">Max 10MB (PDF, JPG, PNG)</small>
                                @error('tempActivityProof') <div class="invalid-feedback">{{ $message }}</div> @enderror
                                @if($activity_proof_document)
                                    <div class="mt-2">
                                        <a href="{{ Storage::url($activity_proof_document) }}" target="_blank" class="btn btn-sm btn-outline-primary">
                                            <i class="fa-solid fa-file"></i> View Current File
                                        </a>
                                    </div>
                                @endif
                            </div>
                        </div>
                    </div>

                    <div class="modal-footer bg-light">
                        <button type="button" class="btn btn-secondary" wire:click="$set('showReportModal', false)">
                            <i class="fa-solid fa-times"></i> Cancel
                        </button>
                        <button type="submit" class="btn btn-primary">
                            <i class="fa-solid fa-save"></i> {{ $modalMode === 'edit' ? 'Update Report' : 'Submit Report' }}
                        </button>
                    </div>
                </form>
            </div>
        </div>
    </div>
    @endif
</div>
