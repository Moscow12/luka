<div>
    <div class="row mb-4">
        <div class="col-12">
            <h4 class="mb-1">Verify Implementations</h4>
            <p class="text-muted mb-0">Check recorded work against its evidence. Only verified entries count toward an employee's score.</p>
        </div>
    </div>

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

    <div class="card mb-4">
        <div class="card-body">
            <div class="row g-3 align-items-end">
                <div class="col-md-5">
                    <label class="form-label">Search</label>
                    <input type="search" class="form-control" wire:model.live.debounce.300ms="search" placeholder="Activity, employee name or number...">
                </div>
                <div class="col-md-3">
                    <label class="form-label">Status</label>
                    <select class="form-select" wire:model.live="filterStatus">
                        <option value="pending">Pending</option>
                        <option value="verified">Verified</option>
                        <option value="rejected">Rejected</option>
                        <option value="">All</option>
                    </select>
                </div>
                <div class="col-md-4">
                    <span class="badge bg-warning fs-6">{{ $pendingCount }} pending</span>
                    @if($employeeFilter)
                        <button type="button" class="btn btn-sm btn-outline-secondary ms-2" wire:click="clearEmployeeFilter">
                            <i class="fa-solid fa-times me-1"></i> Show all employees
                        </button>
                    @endif
                </div>
            </div>
        </div>
    </div>

    <div class="card">
        <div class="card-body p-0">
            @if($entries->count() > 0)
                <div class="table-responsive">
                    <table class="table table-hover align-middle mb-0">
                        <thead class="table-light">
                            <tr>
                                <th>Employee</th>
                                <th>Date</th>
                                <th>Activity</th>
                                <th>Goal</th>
                                <th class="text-center">Achieved</th>
                                <th>Evidence</th>
                                <th class="text-center">Status</th>
                                <th class="text-end">Actions</th>
                            </tr>
                        </thead>
                        <tbody>
                            @foreach($entries as $entry)
                                @php $locked = $entry->employeePlanItem?->employeePlan?->hasApprovedEvaluation(); @endphp
                                <tr wire:key="entry-{{ $entry->id }}">
                                    <td>
                                        <strong>{{ $entry->employee?->getFullName() }}</strong>
                                        <br><small class="text-muted">{{ $entry->employee?->department?->name ?? 'N/A' }}</small>
                                    </td>
                                    <td><small>{{ $entry->implementation_date->format('d M Y') }}</small></td>
                                    <td>
                                        <strong>{{ $entry->activity_title }}</strong>
                                        @if($entry->activity_description)
                                            <br><small class="text-muted">{{ Str::limit($entry->activity_description, 80) }}</small>
                                        @endif
                                    </td>
                                    <td>
                                        <small>{{ Str::limit($entry->employeePlanItem?->item_name ?? 'N/A', 30) }}</small>
                                        @if($entry->employeePlanItem?->target_value)
                                            <br><small class="text-muted">Target: {{ number_format($entry->employeePlanItem->target_value, 2) }} {{ $entry->employeePlanItem->target_unit }}</small>
                                        @endif
                                    </td>
                                    <td class="text-center">
                                        <span class="badge bg-primary">{{ number_format($entry->quantity_achieved ?? 0, 2) }} {{ $entry->unit }}</span>
                                    </td>
                                    <td>
                                        @if($entry->evidence)
                                            <small class="d-block">{{ Str::limit($entry->evidence, 80) }}</small>
                                        @endif
                                        @if($entry->evidence_path)
                                            <a href="{{ route('performance.evidence', $entry->id) }}" target="_blank" rel="noopener" class="small">
                                                <i class="fa-solid fa-paperclip me-1"></i>{{ Str::limit($entry->evidence_name, 30) }}
                                            </a>
                                        @endif
                                        @if(! $entry->evidence && ! $entry->evidence_path)
                                            <small class="text-muted">None provided</small>
                                        @endif
                                    </td>
                                    <td class="text-center">
                                        <span class="badge bg-{{ $entry->status === 'verified' ? 'success' : ($entry->status === 'rejected' ? 'danger' : 'warning') }}">
                                            {{ ucfirst($entry->status) }}
                                        </span>
                                        @if($entry->verification_score)
                                            <span class="badge bg-primary d-block mt-1">Score {{ $entry->verification_score }}/10</span>
                                        @endif
                                        @if($entry->supervisor_remarks)
                                            <small class="d-block text-muted mt-1">{{ Str::limit($entry->supervisor_remarks, 60) }}</small>
                                        @endif
                                    </td>
                                    <td class="text-end text-nowrap">
                                        @if($locked)
                                            <span class="text-muted small"><i class="fa-solid fa-lock"></i> Locked</span>
                                        @else
                                            <button type="button" class="btn btn-sm btn-success" wire:click="openReview('{{ $entry->id }}', 'verified')">
                                                <i class="fa-solid fa-{{ $entry->status === 'verified' ? 'pen' : 'check' }} me-1"></i> {{ $entry->status === 'verified' ? 'Edit score' : 'Verify' }}
                                            </button>
                                            @if($entry->status !== 'rejected')
                                                <button type="button" class="btn btn-sm btn-outline-danger" wire:click="openReview('{{ $entry->id }}', 'rejected')">
                                                    <i class="fa-solid fa-times me-1"></i> Reject
                                                </button>
                                            @endif
                                        @endif
                                    </td>
                                </tr>
                            @endforeach
                        </tbody>
                    </table>
                </div>
                <div class="p-3">{{ $entries->links() }}</div>
            @else
                <div class="text-center text-muted py-5">
                    <i class="fa-solid fa-check-circle fa-3x mb-3 text-success"></i>
                    <h5>No Entries Found</h5>
                    <p class="mb-0">{{ $filterStatus === 'pending' ? 'Nothing is waiting for verification.' : 'Try adjusting your filters.' }}</p>
                </div>
            @endif
        </div>
    </div>

    @if($reviewingEntryId)
        <div class="modal fade show d-block" tabindex="-1" style="background: rgba(0,0,0,0.5);">
            <div class="modal-dialog">
                <div class="modal-content">
                    <div class="modal-header">
                        <h5 class="modal-title">
                            <i class="fa-solid fa-{{ $reviewDecision === 'rejected' ? 'times-circle' : 'check-circle' }} me-2"></i>
                            {{ $reviewDecision === 'rejected' ? 'Reject Entry' : 'Verify Entry' }}
                        </h5>
                        <button type="button" class="btn-close" wire:click="closeReview"></button>
                    </div>
                    <form wire:submit="saveReview">
                        <div class="modal-body">
                            @if($reviewDecision === 'verified')
                                <div class="mb-3">
                                    <label class="form-label" for="reviewScore">Task Score (1 to 10)</label>
                                    <input type="number" id="reviewScore" class="form-control @error('reviewScore') is-invalid @enderror" wire:model="reviewScore" min="1" max="10" step="1" placeholder="Optional">
                                    @error('reviewScore') <div class="invalid-feedback">{{ $message }}</div> @enderror
                                    <div class="form-text">How well the task was done. Optional, and it adds to the employee's final score.</div>
                                </div>
                            @endif
                            <label class="form-label" for="reviewComment">
                                {{ $reviewDecision === 'rejected' ? 'Reason' : 'Comment' }}
                                @if($reviewDecision === 'rejected')<span class="text-danger">*</span>@endif
                            </label>
                            <textarea id="reviewComment" class="form-control @error('reviewComment') is-invalid @enderror" wire:model="reviewComment" rows="3"
                                      placeholder="{{ $reviewDecision === 'rejected' ? 'What should the employee correct?' : 'Optional feedback for the employee...' }}"></textarea>
                            @error('reviewComment') <div class="invalid-feedback">{{ $message }}</div> @enderror
                        </div>
                        <div class="modal-footer">
                            <button type="button" class="btn btn-secondary" wire:click="closeReview">Cancel</button>
                            <button type="submit" class="btn btn-{{ $reviewDecision === 'rejected' ? 'danger' : 'success' }}">
                                {{ $reviewDecision === 'rejected' ? 'Reject Entry' : 'Save' }}
                            </button>
                        </div>
                    </form>
                </div>
            </div>
        </div>
    @endif
</div>
