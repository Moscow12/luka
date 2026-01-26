<div>
    <x-pages.breadcrumn title="Acting Assignment Management"
        :breadcrumbs="[
        ['label' => 'Home', 'url' => route('dashboard')],
        ['label' => 'Leave Management', 'url' => route('leave.leavemanagement')],
        ['label' => 'Acting Assignments']
        ]">
        <a href="{{ route('leave.acting-settings') }}" class="btn btn-sm btn-outline-primary">
            <i class="fa fa-cog"></i> Settings
        </a>
    </x-pages.breadcrumn>

    <div class="row">
        <div class="col-12">
            @if (session()->has('success'))
                <div class="alert alert-success alert-dismissible fade show" role="alert">
                    {{ session('success') }}
                    <button type="button" class="btn-close" data-bs-dismiss="alert" aria-label="Close"></button>
                </div>
            @endif

            @if (session()->has('error'))
                <div class="alert alert-danger alert-dismissible fade show" role="alert">
                    {{ session('error') }}
                    <button type="button" class="btn-close" data-bs-dismiss="alert" aria-label="Close"></button>
                </div>
            @endif

            <x-pages.card title="Acting Assignments">
                <div class="d-flex flex-md-row flex-column gap-2 justify-content-between mb-4">
                    <div class="d-flex flex-row gap-3 align-items-center">
                        <div>
                            <input class="form-control" type="search" wire:model.live="search" placeholder="Search employees..." />
                        </div>
                        <div>
                            <select class="form-select" wire:model.live="statusFilter">
                                <option value="all">All Status</option>
                                <option value="pending">Pending</option>
                                <option value="approved">Approved</option>
                                <option value="active">Active</option>
                                <option value="completed">Completed</option>
                                <option value="rejected">Rejected</option>
                                <option value="cancelled">Cancelled</option>
                            </select>
                        </div>
                    </div>
                </div>

                <div class="table-responsive">
                    <table class="table table-hover align-middle">
                        <thead>
                            <tr>
                                <th>Employee on Leave</th>
                                <th>Acting Employee</th>
                                <th>Acting Role</th>
                                <th>Period</th>
                                <th>Duration</th>
                                <th>Status</th>
                                <th>Actions</th>
                            </tr>
                        </thead>
                        <tbody>
                            @forelse($assignments as $assignment)
                            <tr>
                                <td>
                                    <div>
                                        <strong>{{ $assignment->employeeOnLeave->first_name }} {{ $assignment->employeeOnLeave->last_name }}</strong>
                                        <br>
                                        <small class="text-muted">{{ $assignment->employeeOnLeave->designation->name ?? 'N/A' }}</small>
                                    </div>
                                </td>
                                <td>
                                    <div>
                                        <strong>{{ $assignment->actingEmployee->first_name }} {{ $assignment->actingEmployee->last_name }}</strong>
                                        <br>
                                        <small class="text-muted">{{ $assignment->actingEmployee->designation->name ?? 'N/A' }}</small>
                                    </div>
                                </td>
                                <td>
                                    <div>
                                        {{ $assignment->actingDesignation->name ?? 'Same Position' }}
                                        @if($assignment->actingDepartment)
                                            <br><small class="text-muted">{{ $assignment->actingDepartment->name }}</small>
                                        @endif
                                    </div>
                                </td>
                                <td>
                                    {{ \Carbon\Carbon::parse($assignment->start_date)->format('d M Y') }}
                                    <br>
                                    <small class="text-muted">to {{ \Carbon\Carbon::parse($assignment->end_date)->format('d M Y') }}</small>
                                </td>
                                <td>{{ $assignment->duration_in_days }} days</td>
                                <td>
                                    <span class="badge
                                        @if($assignment->status === 'approved') bg-info
                                        @elseif($assignment->status === 'active') bg-success
                                        @elseif($assignment->status === 'completed') bg-secondary
                                        @elseif($assignment->status === 'rejected') bg-danger
                                        @elseif($assignment->status === 'cancelled') bg-dark
                                        @else bg-warning
                                        @endif">
                                        {{ ucfirst($assignment->status) }}
                                    </span>
                                </td>
                                <td>
                                    <button class="btn btn-sm btn-primary" wire:click="viewAssignmentDetails('{{ $assignment->id }}')">
                                        <i class="fa fa-eye"></i> View
                                    </button>
                                </td>
                            </tr>
                            @empty
                            <tr>
                                <td colspan="7" class="text-center text-muted">No acting assignments found</td>
                            </tr>
                            @endforelse
                        </tbody>
                    </table>
                </div>

                <div class="mt-3">
                    {{ $assignments->links() }}
                </div>
            </x-pages.card>
        </div>
    </div>

    <!-- Assignment Details Modal -->
    @if($showModal && $selectedAssignment)
    <div class="modal fade show d-block" tabindex="-1" style="background: rgba(0,0,0,0.5);">
        <div class="modal-dialog modal-lg">
            <div class="modal-content">
                <div class="modal-header">
                    <h5 class="modal-title">Acting Assignment Details</h5>
                    <button type="button" class="btn-close" wire:click="closeModal"></button>
                </div>
                <div class="modal-body">
                    <div class="row">
                        <div class="col-md-6 mb-3">
                            <label class="form-label text-muted">Employee on Leave</label>
                            <div class="fw-bold">
                                {{ $selectedAssignment->employeeOnLeave->first_name }} {{ $selectedAssignment->employeeOnLeave->last_name }}
                            </div>
                            <small class="text-muted">{{ $selectedAssignment->employeeOnLeave->designation->name ?? 'N/A' }}</small>
                        </div>
                        <div class="col-md-6 mb-3">
                            <label class="form-label text-muted">Acting Employee</label>
                            <div class="fw-bold">
                                {{ $selectedAssignment->actingEmployee->first_name }} {{ $selectedAssignment->actingEmployee->last_name }}
                            </div>
                            <small class="text-muted">{{ $selectedAssignment->actingEmployee->designation->name ?? 'N/A' }}</small>
                        </div>
                    </div>

                    <div class="row">
                        <div class="col-md-6 mb-3">
                            <label class="form-label text-muted">Acting Designation</label>
                            <div>{{ $selectedAssignment->actingDesignation->name ?? 'Same as current' }}</div>
                        </div>
                        <div class="col-md-6 mb-3">
                            <label class="form-label text-muted">Acting Department</label>
                            <div>{{ $selectedAssignment->actingDepartment->name ?? 'Same as current' }}</div>
                        </div>
                    </div>

                    <div class="row">
                        <div class="col-md-6 mb-3">
                            <label class="form-label text-muted">Period</label>
                            <div>
                                {{ \Carbon\Carbon::parse($selectedAssignment->start_date)->format('d M Y') }}
                                to
                                {{ \Carbon\Carbon::parse($selectedAssignment->end_date)->format('d M Y') }}
                            </div>
                            <small class="text-muted">{{ $selectedAssignment->duration_in_days }} days</small>
                        </div>
                        <div class="col-md-6 mb-3">
                            <label class="form-label text-muted">Status</label>
                            <div>
                                <span class="badge
                                    @if($selectedAssignment->status === 'approved') bg-info
                                    @elseif($selectedAssignment->status === 'active') bg-success
                                    @elseif($selectedAssignment->status === 'completed') bg-secondary
                                    @elseif($selectedAssignment->status === 'rejected') bg-danger
                                    @elseif($selectedAssignment->status === 'cancelled') bg-dark
                                    @else bg-warning
                                    @endif">
                                    {{ ucfirst($selectedAssignment->status) }}
                                </span>
                            </div>
                        </div>
                    </div>

                    @if($selectedAssignment->responsibilities)
                    <div class="mb-3">
                        <label class="form-label text-muted">Responsibilities</label>
                        <div class="bg-light p-3 rounded">{{ $selectedAssignment->responsibilities }}</div>
                    </div>
                    @endif

                    @if($selectedAssignment->notes)
                    <div class="mb-3">
                        <label class="form-label text-muted">Additional Notes</label>
                        <div class="bg-light p-3 rounded">{{ $selectedAssignment->notes }}</div>
                    </div>
                    @endif

                    @if($selectedAssignment->rejection_reason)
                    <div class="mb-3">
                        <label class="form-label text-muted">Rejection Reason</label>
                        <div class="bg-danger bg-opacity-10 p-3 rounded text-danger">{{ $selectedAssignment->rejection_reason }}</div>
                    </div>
                    @endif

                    @if($selectedAssignment->approved_by)
                    <div class="mb-3">
                        <label class="form-label text-muted">
                            {{ $selectedAssignment->status === 'approved' ? 'Approved' : 'Actioned' }} By
                        </label>
                        <div>{{ $selectedAssignment->approvedBy->name ?? 'N/A' }} on {{ $selectedAssignment->approved_at?->format('d M Y H:i') }}</div>
                    </div>
                    @endif

                    <div class="row">
                        <div class="col-md-6 mb-3">
                            <div class="form-check">
                                <input class="form-check-input" type="checkbox" disabled {{ $selectedAssignment->notify_acting_employee ? 'checked' : '' }}>
                                <label class="form-check-label text-muted">Notify Acting Employee</label>
                            </div>
                        </div>
                        <div class="col-md-6 mb-3">
                            <div class="form-check">
                                <input class="form-check-input" type="checkbox" disabled {{ $selectedAssignment->grant_system_access ? 'checked' : '' }}>
                                <label class="form-check-label text-muted">Grant System Access</label>
                            </div>
                        </div>
                    </div>
                </div>
                <div class="modal-footer">
                    @if($canApprove)
                        @if($selectedAssignment->status === 'pending')
                            <button class="btn btn-success" wire:click="approveAssignment('{{ $selectedAssignment->id }}')">
                                <i class="fa fa-check"></i> Approve
                            </button>
                            <button class="btn btn-danger" wire:click="openRejectModal('{{ $selectedAssignment->id }}')">
                                <i class="fa fa-times"></i> Reject
                            </button>
                        @endif
                        @if($selectedAssignment->status === 'approved' && $selectedAssignment->start_date <= now())
                            <button class="btn btn-primary" wire:click="activateAssignment('{{ $selectedAssignment->id }}')">
                                <i class="fa fa-play"></i> Activate
                            </button>
                        @endif
                        @if($selectedAssignment->status === 'active' && $selectedAssignment->end_date < now())
                            <button class="btn btn-secondary" wire:click="completeAssignment('{{ $selectedAssignment->id }}')">
                                <i class="fa fa-check-circle"></i> Mark Complete
                            </button>
                        @endif
                        @if(in_array($selectedAssignment->status, ['pending', 'approved']))
                            <button class="btn btn-warning" wire:click="cancelAssignment('{{ $selectedAssignment->id }}')">
                                <i class="fa fa-ban"></i> Cancel
                            </button>
                        @endif
                    @endif
                    <button type="button" class="btn btn-secondary" wire:click="closeModal">Close</button>
                </div>
            </div>
        </div>
    </div>
    @endif

    <!-- Reject Modal -->
    @if($showRejectModal)
    <div class="modal fade show d-block" tabindex="-1" style="background: rgba(0,0,0,0.5);">
        <div class="modal-dialog">
            <div class="modal-content">
                <div class="modal-header">
                    <h5 class="modal-title">Reject Acting Assignment</h5>
                    <button type="button" class="btn-close" wire:click="closeRejectModal"></button>
                </div>
                <div class="modal-body">
                    <div class="mb-3">
                        <label for="rejectionReason" class="form-label">Rejection Reason <span class="text-danger">*</span></label>
                        <textarea class="form-control" id="rejectionReason" rows="4" wire:model="rejectionReason"
                            placeholder="Please provide a reason for rejecting this assignment..."></textarea>
                        @error('rejectionReason')
                            <span class="text-danger d-block mt-1">{{ $message }}</span>
                        @enderror
                    </div>
                </div>
                <div class="modal-footer">
                    <button type="button" class="btn btn-secondary" wire:click="closeRejectModal">Cancel</button>
                    <button type="button" class="btn btn-danger" wire:click="rejectAssignment">
                        <i class="fa fa-times"></i> Reject Assignment
                    </button>
                </div>
            </div>
        </div>
    </div>
    @endif
</div>
