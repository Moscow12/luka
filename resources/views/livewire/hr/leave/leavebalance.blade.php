<div>
    <x-pages.breadcrumn title="Leave Balance"
        :breadcrumbs="[
        ['label' => 'Home', 'url' => route('dashboard')],
        ['label' => 'Leave Overview']
        ]">
        @if($hasEmployeeRecord)
            <a href="{{ route('leave.requestleave') }}" class="btn btn-sm btn-primary">Request Leave</a>
        @endif
    </x-pages.breadcrumn>

    <div class="d-flex flex-column gap-6">
        @if(!$hasEmployeeRecord)
            <div class="alert alert-warning alert-dismissible fade show" role="alert">
                <h5 class="alert-heading"><i class="fa-solid fa-exclamation-triangle me-2"></i>Employee Record Not Found</h5>
                <p>Your user account is not linked to an employee record in the system. You need to be associated with an employee record to view your leave balance.</p>
                <hr>
                <p class="mb-0">Please contact your system administrator to link your account to an employee record.</p>
            </div>
        @else
            <div>
                <h5 class="mb-4">My Leave Balances</h5>
            </div>

            <div class="row g-4">
                @forelse($leaveBalances as $leaveBalance)
                    <div class="col-12 col-md-6 col-lg-4">
                        <div class="card bg-light border-0 h-100">
                            <div class="card-body">
                                <h6 class="card-title text-dark fw-bold mb-3">{{ $leaveBalance['name'] }}</h6>

                                @if($leaveBalance['description'])
                                    <p class="card-text text-muted small mb-3">{{ $leaveBalance['description'] }}</p>
                                @endif

                                <div class="d-flex flex-column gap-2">
                                    <div class="d-flex justify-content-between align-items-center">
                                        <span class="text-muted">Total Days:</span>
                                        <span class="fw-semibold">{{ $leaveBalance['total_days'] }}</span>
                                    </div>

                                    <div class="d-flex justify-content-between align-items-center">
                                        <span class="text-muted">Used Days:</span>
                                        <span class="fw-semibold text-danger">{{ $leaveBalance['used_days'] }}</span>
                                    </div>

                                    <hr class="my-2">

                                    <div class="d-flex justify-content-between align-items-center">
                                        <span class="text-muted fw-bold">Balance:</span>
                                        <span class="badge
                                            @if($leaveBalance['balance'] > 0) bg-success
                                            @else bg-secondary
                                            @endif fs-6 px-3 py-2">
                                            {{ $leaveBalance['balance'] }} days
                                        </span>
                                    </div>
                                </div>

                                @if($leaveBalance['gender'])
                                    <div class="mt-3 pt-3 border-top">
                                        <small class="text-muted">
                                            <i class="fa-solid fa-info-circle me-1"></i>
                                            Applicable to: {{ ucfirst($leaveBalance['gender']) }}
                                        </small>
                                    </div>
                                @endif
                            </div>
                        </div>
                    </div>
                @empty
                    <div class="col-12">
                        <div class="alert alert-info" role="alert">
                            <i class="fa-solid fa-info-circle me-2"></i>
                            No active leave types found in the system.
                        </div>
                    </div>
                @endforelse
            </div>
        @endif
    </div>
</div>
