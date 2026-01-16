<div>
    <div class="row mb-4">
        <div class="col-12">
            <div class="d-md-flex justify-content-between align-items-center">
                <div>
                    <h4 class="mb-1">My Assigned Duties</h4>
                    <p class="text-muted mb-0">View and track your assigned duties and KPIs</p>
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

        {{-- Statistics Cards --}}
        <div class="row mb-4">
            <div class="col-md-3 col-sm-6 mb-3">
                <div class="card bg-primary text-white h-100">
                    <div class="card-body">
                        <div class="d-flex justify-content-between align-items-center">
                            <div>
                                <h6 class="text-white-50 mb-1">Total Duties</h6>
                                <h3 class="mb-0">{{ $statistics['total'] }}</h3>
                            </div>
                            <i class="fa-solid fa-clipboard-list fa-2x opacity-50"></i>
                        </div>
                    </div>
                </div>
            </div>
            <div class="col-md-3 col-sm-6 mb-3">
                <div class="card bg-warning text-dark h-100">
                    <div class="card-body">
                        <div class="d-flex justify-content-between align-items-center">
                            <div>
                                <h6 class="text-dark-50 mb-1">Assigned</h6>
                                <h3 class="mb-0">{{ $statistics['assigned'] }}</h3>
                            </div>
                            <i class="fa-solid fa-hourglass-start fa-2x opacity-50"></i>
                        </div>
                    </div>
                </div>
            </div>
            <div class="col-md-3 col-sm-6 mb-3">
                <div class="card bg-info text-white h-100">
                    <div class="card-body">
                        <div class="d-flex justify-content-between align-items-center">
                            <div>
                                <h6 class="text-white-50 mb-1">In Progress</h6>
                                <h3 class="mb-0">{{ $statistics['in_progress'] }}</h3>
                            </div>
                            <i class="fa-solid fa-spinner fa-2x opacity-50"></i>
                        </div>
                    </div>
                </div>
            </div>
            <div class="col-md-3 col-sm-6 mb-3">
                <div class="card bg-success text-white h-100">
                    <div class="card-body">
                        <div class="d-flex justify-content-between align-items-center">
                            <div>
                                <h6 class="text-white-50 mb-1">Completed</h6>
                                <h3 class="mb-0">{{ $statistics['completed'] }}</h3>
                            </div>
                            <i class="fa-solid fa-check-circle fa-2x opacity-50"></i>
                        </div>
                    </div>
                </div>
            </div>
        </div>

        {{-- Filters --}}
        <div class="card mb-4">
            <div class="card-body">
                <div class="row g-3">
                    <div class="col-md-4">
                        <label class="form-label">Filter by Status</label>
                        <select class="form-select" wire:model.live="filterStatus">
                            <option value="">All Status</option>
                            <option value="assigned">Assigned</option>
                            <option value="in_progress">In Progress</option>
                            <option value="completed">Completed</option>
                        </select>
                    </div>
                    <div class="col-md-4">
                        <label class="form-label">Filter by Priority</label>
                        <select class="form-select" wire:model.live="filterPriority">
                            <option value="">All Priorities</option>
                            <option value="urgent">Urgent</option>
                            <option value="high">High</option>
                            <option value="medium">Medium</option>
                            <option value="low">Low</option>
                        </select>
                    </div>
                    <div class="col-md-4 d-flex align-items-end">
                        @if($statistics['high_priority'] > 0)
                            <span class="badge bg-danger p-2">
                                <i class="fa-solid fa-exclamation-circle me-1"></i>
                                {{ $statistics['high_priority'] }} High Priority Duties
                            </span>
                        @endif
                    </div>
                </div>
            </div>
        </div>

        {{-- Duties List --}}
        <div class="card">
            <div class="card-header">
                <h6 class="mb-0"><i class="fa-solid fa-tasks me-2"></i>My Duties</h6>
            </div>
            <div class="card-body p-0">
                @if($duties->count() > 0)
                    <div class="table-responsive">
                        <table class="table table-hover mb-0">
                            <thead class="table-light">
                                <tr>
                                    <th>Duty Name</th>
                                    <th class="text-center">Priority</th>
                                    <th class="text-center">Target</th>
                                    <th class="text-center">Achievement</th>
                                    <th class="text-center">Progress</th>
                                    <th class="text-center">Status</th>
                                    <th>Due Date</th>
                                    <th>Actions</th>
                                </tr>
                            </thead>
                            <tbody>
                                @foreach($duties as $duty)
                                    <tr>
                                        <td>
                                            <strong>{{ $duty->duty_name }}</strong>
                                            @if($duty->description)
                                                <br><small class="text-muted">{{ Str::limit($duty->description, 50) }}</small>
                                            @endif
                                        </td>
                                        <td class="text-center">
                                            @php
                                                $priorityColors = [
                                                    'urgent' => 'danger',
                                                    'high' => 'warning',
                                                    'medium' => 'info',
                                                    'low' => 'secondary'
                                                ];
                                            @endphp
                                            <span class="badge bg-{{ $priorityColors[$duty->priority] ?? 'secondary' }}">
                                                {{ ucfirst($duty->priority) }}
                                            </span>
                                        </td>
                                        <td class="text-center">
                                            @if($duty->target_value)
                                                {{ number_format($duty->target_value) }} {{ $duty->target_unit }}
                                            @else
                                                <span class="text-muted">-</span>
                                            @endif
                                        </td>
                                        <td class="text-center">
                                            @if($duty->actual_achievement)
                                                <span class="badge bg-primary">{{ number_format($duty->actual_achievement) }} {{ $duty->target_unit }}</span>
                                            @else
                                                <span class="text-muted">-</span>
                                            @endif
                                        </td>
                                        <td class="text-center" style="min-width: 120px;">
                                            @if($duty->target_value && $duty->target_value > 0)
                                                @php
                                                    $progress = $duty->actual_achievement ? min(($duty->actual_achievement / $duty->target_value) * 100, 100) : 0;
                                                @endphp
                                                <div class="progress" style="height: 20px;">
                                                    <div class="progress-bar {{ $progress >= 100 ? 'bg-success' : ($progress >= 50 ? 'bg-info' : 'bg-warning') }}"
                                                         style="width: {{ $progress }}%">
                                                        {{ number_format($progress, 0) }}%
                                                    </div>
                                                </div>
                                            @else
                                                <span class="text-muted">N/A</span>
                                            @endif
                                        </td>
                                        <td class="text-center">
                                            @php
                                                $statusColors = [
                                                    'assigned' => 'warning',
                                                    'in_progress' => 'info',
                                                    'completed' => 'success',
                                                    'cancelled' => 'secondary'
                                                ];
                                            @endphp
                                            <span class="badge bg-{{ $statusColors[$duty->status] ?? 'secondary' }}">
                                                {{ ucfirst(str_replace('_', ' ', $duty->status)) }}
                                            </span>
                                        </td>
                                        <td>
                                            @if($duty->end_date)
                                                @php
                                                    $isOverdue = $duty->end_date->isPast() && $duty->status !== 'completed';
                                                @endphp
                                                <small class="{{ $isOverdue ? 'text-danger fw-bold' : '' }}">
                                                    {{ $duty->end_date->format('d M Y') }}
                                                    @if($isOverdue)
                                                        <br><span class="badge bg-danger">Overdue</span>
                                                    @endif
                                                </small>
                                            @else
                                                <small class="text-muted">No deadline</small>
                                            @endif
                                        </td>
                                        <td>
                                            <div class="btn-group btn-group-sm">
                                                <button class="btn btn-outline-primary" wire:click="viewDuty('{{ $duty->id }}')" title="View Details">
                                                    <i class="fa-solid fa-eye"></i>
                                                </button>
                                                @if($duty->status !== 'completed')
                                                    <button class="btn btn-outline-success" wire:click="openUpdateModal('{{ $duty->id }}')" title="Update Progress">
                                                        <i class="fa-solid fa-edit"></i>
                                                    </button>
                                                @endif
                                            </div>
                                        </td>
                                    </tr>
                                @endforeach
                            </tbody>
                        </table>
                    </div>
                    <div class="card-footer">
                        {{ $duties->links() }}
                    </div>
                @else
                    <div class="text-center text-muted py-5">
                        <i class="fa-solid fa-clipboard-list fa-3x mb-3"></i>
                        <h5>No Duties Assigned</h5>
                        <p>You don't have any assigned duties at the moment.</p>
                    </div>
                @endif
            </div>
        </div>

        {{-- Duty Detail Modal --}}
        @if($showDetailModal && $selectedDuty)
            <div class="modal fade show d-block" tabindex="-1" style="background: rgba(0,0,0,0.5);">
                <div class="modal-dialog modal-lg">
                    <div class="modal-content">
                        <div class="modal-header bg-primary text-white">
                            <h5 class="modal-title">
                                <i class="fa-solid fa-clipboard-list me-2"></i>Duty Details
                            </h5>
                            <button type="button" class="btn-close btn-close-white" wire:click="closeDetailModal"></button>
                        </div>
                        <div class="modal-body">
                            <div class="row mb-4">
                                <div class="col-md-8">
                                    <h5>{{ $selectedDuty->duty_name }}</h5>
                                    @if($selectedDuty->description)
                                        <p class="text-muted">{{ $selectedDuty->description }}</p>
                                    @endif
                                </div>
                                <div class="col-md-4 text-end">
                                    @php
                                        $priorityColors = [
                                            'urgent' => 'danger',
                                            'high' => 'warning',
                                            'medium' => 'info',
                                            'low' => 'secondary'
                                        ];
                                        $statusColors = [
                                            'assigned' => 'warning',
                                            'in_progress' => 'info',
                                            'completed' => 'success',
                                            'cancelled' => 'secondary'
                                        ];
                                    @endphp
                                    <span class="badge bg-{{ $priorityColors[$selectedDuty->priority] ?? 'secondary' }} me-1">
                                        {{ ucfirst($selectedDuty->priority) }} Priority
                                    </span>
                                    <span class="badge bg-{{ $statusColors[$selectedDuty->status] ?? 'secondary' }}">
                                        {{ ucfirst(str_replace('_', ' ', $selectedDuty->status)) }}
                                    </span>
                                </div>
                            </div>

                            <div class="row g-3">
                                <div class="col-md-6">
                                    <div class="card h-100">
                                        <div class="card-header bg-light">
                                            <h6 class="mb-0"><i class="fa-solid fa-bullseye me-2"></i>Target & Achievement</h6>
                                        </div>
                                        <div class="card-body">
                                            <table class="table table-sm mb-0">
                                                <tr>
                                                    <td class="text-muted">KPI Type:</td>
                                                    <td><strong>{{ ucfirst($selectedDuty->kpi_type) }}</strong></td>
                                                </tr>
                                                <tr>
                                                    <td class="text-muted">Measurement:</td>
                                                    <td><strong>{{ ucfirst(str_replace('_', ' ', $selectedDuty->measurement_type)) }}</strong></td>
                                                </tr>
                                                @if($selectedDuty->weight)
                                                    <tr>
                                                        <td class="text-muted">Weight:</td>
                                                        <td><strong>{{ $selectedDuty->weight }}%</strong></td>
                                                    </tr>
                                                @endif
                                                @if($selectedDuty->target_value)
                                                    <tr>
                                                        <td class="text-muted">Target:</td>
                                                        <td><strong>{{ number_format($selectedDuty->target_value) }} {{ $selectedDuty->target_unit }}</strong></td>
                                                    </tr>
                                                @endif
                                                @if($selectedDuty->actual_achievement)
                                                    <tr>
                                                        <td class="text-muted">Achievement:</td>
                                                        <td><strong class="text-success">{{ number_format($selectedDuty->actual_achievement) }} {{ $selectedDuty->target_unit }}</strong></td>
                                                    </tr>
                                                @endif
                                                @if($selectedDuty->score)
                                                    <tr>
                                                        <td class="text-muted">Score:</td>
                                                        <td><span class="badge bg-{{ $selectedDuty->score >= 80 ? 'success' : ($selectedDuty->score >= 50 ? 'warning' : 'danger') }} fs-6">{{ number_format($selectedDuty->score, 1) }}%</span></td>
                                                    </tr>
                                                @endif
                                            </table>
                                        </div>
                                    </div>
                                </div>
                                <div class="col-md-6">
                                    <div class="card h-100">
                                        <div class="card-header bg-light">
                                            <h6 class="mb-0"><i class="fa-solid fa-calendar me-2"></i>Timeline & Assignment</h6>
                                        </div>
                                        <div class="card-body">
                                            <table class="table table-sm mb-0">
                                                @if($selectedDuty->start_date)
                                                    <tr>
                                                        <td class="text-muted">Start Date:</td>
                                                        <td><strong>{{ $selectedDuty->start_date->format('d M Y') }}</strong></td>
                                                    </tr>
                                                @endif
                                                @if($selectedDuty->end_date)
                                                    <tr>
                                                        <td class="text-muted">End Date:</td>
                                                        <td><strong>{{ $selectedDuty->end_date->format('d M Y') }}</strong></td>
                                                    </tr>
                                                @endif
                                                @if($selectedDuty->assignedBy)
                                                    <tr>
                                                        <td class="text-muted">Assigned By:</td>
                                                        <td><strong>{{ $selectedDuty->assignedBy->name }}</strong></td>
                                                    </tr>
                                                @endif
                                                @if($selectedDuty->assigned_at)
                                                    <tr>
                                                        <td class="text-muted">Assigned On:</td>
                                                        <td><strong>{{ $selectedDuty->assigned_at->format('d M Y H:i') }}</strong></td>
                                                    </tr>
                                                @endif
                                            </table>
                                        </div>
                                    </div>
                                </div>
                            </div>

                            @if($selectedDuty->scoring_criteria)
                                <div class="card mt-3">
                                    <div class="card-header bg-light">
                                        <h6 class="mb-0"><i class="fa-solid fa-list-check me-2"></i>Scoring Criteria</h6>
                                    </div>
                                    <div class="card-body">
                                        {{ $selectedDuty->scoring_criteria }}
                                    </div>
                                </div>
                            @endif

                            @if($selectedDuty->achievement_notes)
                                <div class="card mt-3">
                                    <div class="card-header bg-light">
                                        <h6 class="mb-0"><i class="fa-solid fa-sticky-note me-2"></i>Achievement Notes</h6>
                                    </div>
                                    <div class="card-body">
                                        {{ $selectedDuty->achievement_notes }}
                                    </div>
                                </div>
                            @endif

                            @if($selectedDuty->review_comments)
                                <div class="card mt-3 border-info">
                                    <div class="card-header bg-info text-white">
                                        <h6 class="mb-0"><i class="fa-solid fa-comment me-2"></i>Supervisor Review</h6>
                                    </div>
                                    <div class="card-body">
                                        <p>{{ $selectedDuty->review_comments }}</p>
                                        @if($selectedDuty->reviewedBy)
                                            <small class="text-muted">
                                                Reviewed by {{ $selectedDuty->reviewedBy->name }}
                                                @if($selectedDuty->reviewed_at)
                                                    on {{ $selectedDuty->reviewed_at->format('d M Y H:i') }}
                                                @endif
                                            </small>
                                        @endif
                                    </div>
                                </div>
                            @endif
                        </div>
                        <div class="modal-footer">
                            <button type="button" class="btn btn-secondary" wire:click="closeDetailModal">Close</button>
                            @if($selectedDuty->status !== 'completed')
                                <button type="button" class="btn btn-primary" wire:click="closeDetailModal" wire:click.prevent="openUpdateModal('{{ $selectedDuty->id }}')">
                                    <i class="fa-solid fa-edit me-1"></i>Update Progress
                                </button>
                            @endif
                        </div>
                    </div>
                </div>
            </div>
        @endif

        {{-- Update Progress Modal --}}
        @if($showUpdateModal && $selectedDuty)
            <div class="modal fade show d-block" tabindex="-1" style="background: rgba(0,0,0,0.5);">
                <div class="modal-dialog">
                    <div class="modal-content">
                        <div class="modal-header bg-success text-white">
                            <h5 class="modal-title">
                                <i class="fa-solid fa-edit me-2"></i>Update Progress
                            </h5>
                            <button type="button" class="btn-close btn-close-white" wire:click="closeUpdateModal"></button>
                        </div>
                        <form wire:submit="updateProgress">
                            <div class="modal-body">
                                <div class="alert alert-info">
                                    <strong>{{ $selectedDuty->duty_name }}</strong>
                                    @if($selectedDuty->target_value)
                                        <br><small>Target: {{ number_format($selectedDuty->target_value) }} {{ $selectedDuty->target_unit }}</small>
                                    @endif
                                </div>

                                <div class="mb-3">
                                    <label class="form-label">Actual Achievement</label>
                                    <div class="input-group">
                                        <input type="number" step="0.01" class="form-control @error('actual_achievement') is-invalid @enderror"
                                               wire:model="actual_achievement" placeholder="Enter achievement value">
                                        @if($selectedDuty->target_unit)
                                            <span class="input-group-text">{{ $selectedDuty->target_unit }}</span>
                                        @endif
                                    </div>
                                    @error('actual_achievement')
                                        <div class="invalid-feedback d-block">{{ $message }}</div>
                                    @enderror
                                </div>

                                <div class="mb-3">
                                    <label class="form-label">Status</label>
                                    <select class="form-select @error('update_status') is-invalid @enderror" wire:model="update_status">
                                        <option value="assigned">Assigned</option>
                                        <option value="in_progress">In Progress</option>
                                        <option value="completed">Completed</option>
                                    </select>
                                    @error('update_status')
                                        <div class="invalid-feedback">{{ $message }}</div>
                                    @enderror
                                </div>

                                <div class="mb-3">
                                    <label class="form-label">Notes (Optional)</label>
                                    <textarea class="form-control @error('achievement_notes') is-invalid @enderror"
                                              wire:model="achievement_notes" rows="3"
                                              placeholder="Add notes about your progress..."></textarea>
                                    @error('achievement_notes')
                                        <div class="invalid-feedback">{{ $message }}</div>
                                    @enderror
                                </div>
                            </div>
                            <div class="modal-footer">
                                <button type="button" class="btn btn-secondary" wire:click="closeUpdateModal">Cancel</button>
                                <button type="submit" class="btn btn-success">
                                    <i class="fa-solid fa-save me-1"></i>Save Progress
                                </button>
                            </div>
                        </form>
                    </div>
                </div>
            </div>
        @endif
    @endif
</div>
