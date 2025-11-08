<div>
    <x-pages.breadcrumn title="Request Leave"
        :breadcrumbs="[
        ['label' => 'Home', 'url' => route('dashboard')],
        ['label' => 'Leave Overview', 'url' => route('leave.leavebalance')],
        ['label' => 'Leave Request']
        ]">
        @if($hasEmployeeRecord)
            <x-forms.button-model name="REQUEST LEAVE"/>
        @endif
    </x-pages.breadcrumn>

    <div class="d-flex flex-column gap-6">
        @if (session()->has('success'))
            <div class="alert alert-success alert-dismissible fade show" role="alert">
                {{ session('success') }}
                <button type="button" class="btn-close" data-bs-dismiss="alert" aria-label="Close"></button>
            </div>
        @endif

        @if(!$hasEmployeeRecord)
            <div class="alert alert-warning alert-dismissible fade show" role="alert">
                <h5 class="alert-heading"><i class="fa-solid fa-exclamation-triangle me-2"></i>Employee Record Not Found</h5>
                <p>Your user account is not linked to an employee record in the system. You need to be associated with an employee record to request leave.</p>
                <hr>
                <p class="mb-0">Please contact your system administrator to link your account to an employee record.</p>
            </div>
        @else
            <div class="d-flex flex-md-row flex-column gap-2 justify-content-between">
                <div class="d-flex flex-row gap-3 align-items-center">
                    <div>
                        <form>
                            <input class="form-control" type="search" wire:model.live="search" placeholder="Search" />
                        </form>
                    </div>
                    <a href="#!" class="text-inherit">
                        <i class="fa-solid fa-filter"></i>
                        <span>Filter</span>
                    </a>
                </div>
                <div>
                    <x-forms.button-model name="REQUEST LEAVE"/>
                </div>
            </div>

            <div>
                <x-pages.card title="My Leave Requests">
                    <div class="table-responsive">
                        <table class="table table-hover align-middle">
                            <thead>
                                <tr>
                                    <th>Leave Type</th>
                                    <th>Start Date</th>
                                    <th>Reporting Date</th>
                                    <th>Days</th>
                                    <th>Travel To</th>
                                    <th>Status</th>
                                    <th>Actions</th>
                                </tr>
                            </thead>
                            <tbody>
                                @forelse($leaves as $leave)
                                <tr>
                                    <td>{{ $leave->leave->name ?? 'N/A' }}</td>
                                    <td>{{ \Carbon\Carbon::parse($leave->start_date)->format('d M Y') }}</td>
                                    <td>{{ \Carbon\Carbon::parse($leave->end_date)->format('d M Y') }}</td>
                                    <td>{{ $leave->days }}</td>
                                    <td>{{ $leave->travel_to }}</td>
                                    <td>
                                        <span class="badge
                                            @if($leave->status === 'Approved') bg-success
                                            @elseif($leave->status === 'Rejected') bg-danger
                                            @else bg-warning
                                            @endif">
                                            {{ $leave->status }}
                                        </span>
                                    </td>
                                    <td>
                                        @if($leave->status === 'Awaiting')
                                            <x-forms.button-model name="EDIT" wire:click="openModal('edit', '{{ $leave->id }}')" />
                                            <a href="#" class="btn btn-sm btn-danger" wire:click.prevent="delete('{{ $leave->id }}')">Delete</a>
                                        @else
                                            <span class="text-muted">{{ $leave->status }}</span>
                                        @endif
                                    </td>
                                </tr>
                                @empty
                                <tr>
                                    <td colspan="7" class="text-center text-muted">No leave requests found</td>
                                </tr>
                                @endforelse
                            </tbody>
                        </table>
                    </div>
                </x-pages.card>
            </div>

            <x-pages.model
                :title="$modalMode === 'edit' ? 'Edit Leave Request' : 'Request Leave'"
                :formaction="$modalMode === 'edit' ? 'update' : 'save'"
                :modalMode="$modalMode"
                :showModal="$showModal">
            <x-forms.input type="select" name="leave_id" label="Leave" :options="$leaveslist->pluck('name', 'id')" required />
            <x-forms.input type="date" name="start_date" label="Start Date" required  min="{{ now()->toDateString() }}" wire:model="start_date" />
            <x-forms.input type="number" name="days" label="Days" required  wire:model.live="days"         wire:input="calculateEndDate" />
            
            @if ($errorMessage)
                <div class="text-danger mt-2">{{ $errorMessage }}</div>
            @endif

                @if($available_days !== null)
                    <div class="alert alert-info">
                        Available leave days: <strong>{{ $available_days }}</strong>
                    </div>
                @endif

                <x-forms.input type="text" name="end_date" label="End Date /Reporting Date" wire:model="end_date" disabled required  error="$error" />            
                <x-forms.input type="text" name="travel_to" label="Travel To" required />
                <x-forms.input type="text" name="othercontact" label="Other Contact" required />
                <x-forms.input type="textarea" name="comments" label="Comments" rows="2" required />

                @if ($errorMessage)
                    <div class="alert alert-danger mt-2">{{ $errorMessage }}</div>
                @endif
            </x-pages.model>
        @endif
    </div>