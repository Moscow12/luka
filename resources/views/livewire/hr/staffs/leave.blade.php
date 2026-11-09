<div>
    <x-pages.breadcrumn title="Staff Details"
        :breadcrumbs="[
        ['label' => 'Home', 'url' => route('dashboard')], 
        ['label' => 'Staff List', 'url' => route('hr.stafflist')],
        ['label' => 'Staff Contacts']  
        ]">
        <a href="{{ route('hr.stafflist') }}" class="btn btn-sm btn-primary">Back to List</a>
    </x-pages.breadcrumn>
    <!-- row -->
    <x-pages.empheader :age="$age" :gender="$gender" :email="$email" :getFullName="$getfullname" :editUrl="$editUrl" :employee_id="$employee_id" :photo="$photo" />
    <div>
        <h5 class="mb-5">Employee Leaves</h5>
    </div>
    <div class="d-flex flex-column gap-6">
        <div class="d-flex flex-md-row flex-column gap-2 justify-content-between">
            <div></div>
            <div>
                <x-forms.button-model name="ADD LEAVE"/>
            </div>
        </div>
        
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

        <div>
            <x-pages.card title="Employee Leaves List">
                <div class="table-responsive">
                    <table class="table table-hover align-middle">
                        <thead>
                            <tr>
                                <th scope="col">#</th>
                                <th>Leave Type</th>
                                <th>Start Date</th>
                                <th>Reporting Date</th>
                                <th>Days</th>
                                <th>Status</th>
                                <th>Approval Progress</th>
                                <th class="text-end">Actions</th>
                            </tr>
                        </thead>
                        <tbody>
                            @php
                                $num=1;
                            @endphp
                            @forelse($leaves as $leave)
                            <tr wire:key="staff-leave-{{ $leave->id }}">
                                <td>{{ $num++ }}</td>
                                <td>{{ $leave->leave->name ?? 'N/A' }}</td>
                                <td class="text-nowrap">{{ \Carbon\Carbon::parse($leave->start_date)->format('d M Y') }}</td>
                                <td class="text-nowrap">{{ \Carbon\Carbon::parse($leave->end_date)->format('d M Y') }}</td>
                                <td>{{ $leave->days }}</td>
                                <td>@include('livewire.hr.leave.partials.status-badge', ['status' => $leave->status])</td>
                                <td style="min-width: 220px;">@include('livewire.hr.leave.partials.progress-cell', ['leave' => $leave])</td>
                                <td class="text-end text-nowrap">
                                    <button type="button" class="btn btn-sm btn-outline-primary" wire:click="viewProgress('{{ $leave->id }}')" title="View approval progress">
                                        <i class="fa-solid fa-eye me-1"></i>View
                                    </button>
                                    @if($canManage)
                                        <x-forms.button-model name="EDIT" wire:click="openModal('edit', '{{ $leave->id }}')" />
                                        <button type="button" class="btn btn-sm btn-danger" wire:click="delete('{{ $leave->id }}')" wire:confirm="Delete this leave record? This cannot be undone.">Delete</button>
                                    @endif
                                </td>
                            </tr>
                            @empty
                            <tr><td colspan="7" class="text-center text-muted py-4">No leave records for this employee.</td></tr>
                            @endforelse
                        </tbody>
                    </table>
                </div>
            </x-pages.card>
        </div>
        <x-pages.model :title="$modalMode === 'edit' ? 'Edit Leave' : 'Request Leave'" formaction="save" :modalMode="$modalMode" :showModal="$showModal">
            <x-forms.input type="select" name="leave_id" label="Leave" :options="$leaveslist->pluck('name', 'id')" required wire:model.live="leave_id" />
            <x-forms.input type="date" name="start_date" label="Start Date" required  min="{{ now()->toDateString() }}" wire:model="start_date" />
            <x-forms.input type="number" name="days" label="Days" required  wire:model.live="days"         wire:input="calculateEndDate" />
            <x-forms.input type="text" name="end_date" label="Reporting Date" wire:model="end_date" disabled required  error="$error" />
            <x-forms.input type="text" name="travel_to" label="Travel To" required />
            <x-forms.input type="text" name="othercontact" label="Other Contact" required />
            <x-forms.input type="textarea" name="comments" label="Comments" rows="2" required />
            @if ($requiresDocument)
                <div class="mb-3">
                    <label class="form-label" for="document">Supporting Document <span class="text-danger">*</span></label>
                    <input type="file" class="form-control" id="document" wire:model="document" accept=".pdf,.jpg,.jpeg,.png">
                    <small class="text-muted">Accepted formats: PDF, JPG, JPEG, PNG (Max: 2MB)</small>
                    @error('document')
                        <span class="text-danger d-block mt-1">{{ $message }}</span>
                    @enderror
                    @if ($document)
                        <div class="mt-2 text-success">
                            <i class="fa fa-check-circle"></i> File selected: {{ $document->getClientOriginalName() }}
                        </div>
                    @endif
                </div>
            @endif
            @if ($errorMessage)
                <div class="text-danger mt-2">{{ $errorMessage }}</div>
            @endif
        </x-pages.model>

        @include('livewire.hr.leave.partials.progress-modal', ['progressLeave' => $progressLeave, 'ownRequest' => false])
    </div>
</div>
