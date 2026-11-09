<div>
    <x-pages.breadcrumn title="Request Leave"
        :breadcrumbs="[
        ['label' => 'Home', 'url' => route('dashboard')],
        ['label' => 'Leave Overview', 'url' => route('leave.leavebalance')],
        ['label' => 'Leave Request']
        ]">
        
    </x-pages.breadcrumn>

    <div class="d-flex flex-column gap-6">
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

        @if(!$hasEmployeeRecord)
            <div class="alert alert-warning alert-dismissible fade show" role="alert">
                <h5 class="alert-heading"><i class="fa-solid fa-exclamation-triangle me-2"></i>Employee Record Not Found</h5>
                <p>Your user account is not linked to an employee record in the system. You need to be associated with an employee record to request leave.</p>
                <hr>
                <p class="mb-0">Please contact your system administrator to link your account to an employee record.</p>
            </div>
        @else
            @php
                $sortIcon = fn ($field) => $sortField !== $field
                    ? 'fa-sort text-muted'
                    : ($sortDirection === 'asc' ? 'fa-sort-up text-primary' : 'fa-sort-down text-primary');
                $tabs = \App\Livewire\Hr\Leave\Requestleave::STATUS_TABS;
                $sortable = ['start_date' => 'Start Date', 'end_date' => 'Reporting Date', 'days' => 'Days', 'created_at' => 'Requested', 'status' => 'Status'];
            @endphp

            {{-- Status tabs + search + new request --}}
            <div class="row g-2 align-items-center">
                <div class="col-12 col-xl-7">
                    <ul class="nav nav-pills gap-2" role="tablist" aria-label="Filter by status">
                        @foreach($tabs as $key => $tab)
                            <li class="nav-item">
                                <button type="button" wire:click="$set('statusFilter', '{{ $key }}')"
                                        class="nav-link {{ $statusFilter === $key ? 'active' : '' }}" role="tab" aria-selected="{{ $statusFilter === $key ? 'true' : 'false' }}">
                                    {{ $tab['label'] }}
                                    <span class="badge {{ $statusFilter === $key ? 'bg-white text-primary' : 'bg-secondary-subtle text-secondary-emphasis' }} ms-1">{{ $statusCounts[$key] ?? 0 }}</span>
                                </button>
                            </li>
                        @endforeach
                    </ul>
                </div>
                <div class="col-12 col-md-8 col-xl-3">
                    <div class="input-group">
                        <span class="input-group-text bg-white"><i class="fa-solid fa-magnifying-glass text-muted"></i></span>
                        <input type="search" class="form-control" wire:model.live.debounce.300ms="search"
                               placeholder="Leave type, destination, status" aria-label="Search my leave requests">
                        @if($search)
                            <button class="btn btn-outline-secondary" type="button" wire:click="$set('search', '')" aria-label="Clear search">
                                <i class="fa-solid fa-times"></i>
                            </button>
                        @endif
                    </div>
                </div>
                <div class="col-12 col-md-4 col-xl-2 text-md-end">
                    <x-forms.button-model name="REQUEST LEAVE"/>
                </div>
            </div>

            <div>
                <x-pages.card title="My Leave Requests">
                    {{-- Scrolls inside the card; the header row stays visible --}}
                    <div class="table-responsive leave-table-scroll" wire:loading.class="opacity-50"
                         wire:target="statusFilter, search, sortBy, perPage, gotoPage, nextPage, previousPage">
                        <table class="table table-hover align-middle mb-0">
                            <thead class="table-light">
                                <tr>
                                    <th scope="col" style="width: 50px;">#</th>
                                    <th scope="col">Leave Type</th>
                                    @foreach(['start_date', 'end_date', 'days'] as $field)
                                        <th scope="col" role="button" class="user-select-none text-nowrap" wire:click="sortBy('{{ $field }}')"
                                            aria-sort="{{ $sortField === $field ? ($sortDirection === 'asc' ? 'ascending' : 'descending') : 'none' }}">
                                            {{ $sortable[$field] }} <i class="fa-solid {{ $sortIcon($field) }} ms-1"></i>
                                        </th>
                                    @endforeach
                                    <th scope="col">Travel To</th>
                                    @foreach(['created_at', 'status'] as $field)
                                        <th scope="col" role="button" class="user-select-none text-nowrap" wire:click="sortBy('{{ $field }}')"
                                            aria-sort="{{ $sortField === $field ? ($sortDirection === 'asc' ? 'ascending' : 'descending') : 'none' }}">
                                            {{ $sortable[$field] }} <i class="fa-solid {{ $sortIcon($field) }} ms-1"></i>
                                        </th>
                                    @endforeach
                                    <th scope="col">Approval Progress</th>
                                    <th scope="col" class="text-end">Actions</th>
                                </tr>
                            </thead>
                            <tbody>
                                @forelse($leaves as $leave)
                                <tr wire:key="leave-{{ $leave->id }}">
                                    <td class="text-muted">{{ $leaves->firstItem() + $loop->index }}</td>
                                    <td class="fw-semibold">{{ $leave->leave->name ?? 'N/A' }}</td>
                                    <td class="text-nowrap">{{ \Carbon\Carbon::parse($leave->start_date)->format('d M Y') }}</td>
                                    <td class="text-nowrap">{{ \Carbon\Carbon::parse($leave->end_date)->format('d M Y') }}</td>
                                    <td>{{ $leave->days }}</td>
                                    <td>{{ $leave->travel_to }}</td>
                                    <td class="text-nowrap small text-muted">{{ $leave->created_at?->format('d M Y') }}</td>
                                    <td>@include('livewire.hr.leave.partials.status-badge', ['status' => $leave->status])</td>
                                    <td style="min-width: 220px;">@include('livewire.hr.leave.partials.progress-cell', ['leave' => $leave])</td>
                                    <td class="text-end text-nowrap">
                                        <button type="button" class="btn btn-sm btn-outline-primary" wire:click="viewProgress('{{ $leave->id }}')" title="View approval progress">
                                            <i class="fa-solid fa-eye me-1"></i>View
                                        </button>
                                        @if($leave->editable)
                                            <x-forms.button-model name="EDIT" wire:click="openModal('edit', '{{ $leave->id }}')" />
                                            <button type="button" class="btn btn-sm btn-danger" wire:click="delete('{{ $leave->id }}')" wire:confirm="Withdraw this leave request?">Delete</button>
                                        @endif
                                    </td>
                                </tr>
                                @empty
                                <tr>
                                    <td colspan="10" class="text-center text-muted py-5">
                                        <i class="fa-solid fa-calendar-xmark fa-2x mb-2 d-block opacity-50"></i>
                                        @if(trim($search) !== '' || $statusFilter !== '')
                                            No leave requests match these filters.
                                            <button type="button" class="btn btn-link btn-sm p-0 align-baseline" wire:click="$set('search', ''); $set('statusFilter', '')">Show all</button>
                                        @else
                                            You haven't requested any leave yet.
                                        @endif
                                    </td>
                                </tr>
                                @endforelse
                            </tbody>
                        </table>
                    </div>

                    {{-- Rows per page, range, pages --}}
                    <div class="d-flex flex-wrap justify-content-between align-items-center gap-2 mt-3">
                        <div class="d-flex align-items-center gap-2 small text-muted">
                            <label for="leave-per-page" class="mb-0">Show</label>
                            <select id="leave-per-page" wire:model.live="perPage" class="form-select form-select-sm" style="width: auto;">
                                <option value="10">10</option>
                                <option value="25">25</option>
                                <option value="50">50</option>
                                <option value="100">100</option>
                                <option value="all">All</option>
                            </select>
                            <span>rows</span>
                        </div>
                        <div class="text-muted small">
                            @if($leaves->total() > 0)
                                Showing {{ $leaves->firstItem() }} to {{ $leaves->lastItem() }} of {{ $leaves->total() }} requests
                            @else
                                No results
                            @endif
                        </div>
                        <div>
                            @if($leaves->hasPages())
                                {{ $leaves->links() }}
                            @endif
                        </div>
                    </div>
                </x-pages.card>
            </div>

            <style>
                .leave-table-scroll { max-height: 65vh; overflow-y: auto; }
                .leave-table-scroll thead th { position: sticky; top: 0; z-index: 2; box-shadow: inset 0 -1px 0 var(--bs-border-color); }
            </style>

            <x-pages.model
                :title="$modalMode === 'edit' ? 'Edit Leave Request' : 'Request Leave'"
                :formaction="$modalMode === 'edit' ? 'update' : 'save'"
                :modalMode="$modalMode"
                :showModal="$showModal"
                size="lg">
            <x-forms.input type="select" name="leave_id" label="Leave" :options="$leaveslist->pluck('name', 'id')" required wire:model.live="leave_id" />
            <x-forms.input type="date" name="start_date" label="Start Date" required  min="{{ now()->toDateString() }}" wire:model.live="start_date" />
            <x-forms.input type="number" name="days" label="Days" required  wire:model.live="days"         wire:input="calculateEndDate" />

            @if ($errorMessage)
                <div class="text-danger mt-2">{{ $errorMessage }}</div>
            @endif

                @if($available_days !== null)
                    <div class="alert alert-info">
                        Available leave days: <strong>{{ $available_days }}</strong>
                    </div>
                @endif

                <x-forms.input type="text" name="end_date" label="Reporting Date" wire:model="end_date" disabled required  error="$error" />
                <x-forms.input type="text" name="travel_to" label="Travel To" required />
                <x-forms.input type="text" name="othercontact" label="Other Contact" required />
                <x-forms.input type="textarea" name="comments" label="Comments" rows="2" required />

                @if ($showActingAssignment)
                    <div class="card bg-light mb-3">
                        <div class="card-header">
                            <strong><i class="fa fa-user-tie me-1"></i> Acting Assignment</strong>
                            @if($actingAssignmentRequired)
                                <span class="badge bg-danger">Required</span>
                            @else
                                <span class="badge bg-info">Optional</span>
                            @endif
                        </div>
                        <div class="card-body">
                            <div class="mb-3">
                                <x-forms.search-picker
                                    name="acting_employee_id"
                                    label="Acting Employee"
                                    :required="$actingAssignmentRequired"
                                    :selected="$this->selectedActingEmployee"
                                    :items="$this->filteredActingEmployees"
                                    :search-value="$actingEmployeeSearch"
                                    :show-dropdown="$showActingEmployeeDropdown"
                                    search-prop="actingEmployeeSearch"
                                    dropdown-prop="showActingEmployeeDropdown"
                                    search-placeholder="Search employee by name, number, or designation..."
                                    empty-text="No employees found"
                                    select-method="selectActingEmployee"
                                    clear-method="clearActingEmployee"
                                    label-key="name"
                                    sublabel-key="employee_no" />
                            </div>

                            <div class="row">
                                <div class="col-md-6 mb-3">
                                    <x-forms.search-picker
                                        name="acting_designation_id"
                                        label="Acting Designation (Optional)"
                                        :selected="$this->selectedActingDesignation"
                                        :items="$this->filteredActingDesignations"
                                        :search-value="$actingDesignationSearch"
                                        :show-dropdown="$showActingDesignationDropdown"
                                        search-prop="actingDesignationSearch"
                                        dropdown-prop="showActingDesignationDropdown"
                                        search-placeholder="Search designation..."
                                        empty-text="No designations found"
                                        select-method="selectActingDesignation"
                                        clear-method="clearActingDesignation" />
                                </div>
                                <div class="col-md-6 mb-3">
                                    <x-forms.search-picker
                                        name="acting_department_id"
                                        label="Acting Department (Optional)"
                                        :selected="$this->selectedActingDepartment"
                                        :items="$this->filteredActingDepartments"
                                        :search-value="$actingDepartmentSearch"
                                        :show-dropdown="$showActingDepartmentDropdown"
                                        search-prop="actingDepartmentSearch"
                                        dropdown-prop="showActingDepartmentDropdown"
                                        search-placeholder="Search department..."
                                        empty-text="No departments found"
                                        select-method="selectActingDepartment"
                                        clear-method="clearActingDepartment" />
                                </div>
                            </div>

                            <div class="mb-3">
                                <label class="form-label" for="acting_responsibilities">Responsibilities</label>
                                <textarea class="form-control" id="acting_responsibilities" wire:model.defer="acting_responsibilities" rows="3"
                                    placeholder="Describe specific duties to be performed during acting role..."></textarea>
                            </div>

                            <div class="mb-3">
                                <label class="form-label" for="acting_notes">Additional Notes</label>
                                <textarea class="form-control" id="acting_notes" wire:model.defer="acting_notes" rows="2"></textarea>
                            </div>

                            <div class="row">
                                <div class="col-md-6">
                                    <div class="form-check">
                                        <input class="form-check-input" type="checkbox" wire:model.defer="notify_acting_employee" id="notify_acting_employee">
                                        <label class="form-check-label" for="notify_acting_employee">
                                            Notify Acting Employee
                                        </label>
                                    </div>
                                </div>
                                <div class="col-md-6">
                                    <div class="form-check">
                                        <input class="form-check-input" type="checkbox" wire:model.defer="grant_system_access" id="grant_system_access">
                                        <label class="form-check-label" for="grant_system_access">
                                            Grant System Access
                                        </label>
                                    </div>
                                </div>
                            </div>
                        </div>
                    </div>
                @endif

                @if ($requiresDocument)
                    <div class="mb-3">
                        <label class="form-label" for="document">Supporting Document <span class="text-danger">*</span></label>
                        <input type="file" class="form-control" id="document" wire:model="document" accept=".pdf,.jpg,.jpeg,.png">
                        <small class="text-muted">This leave type requires supporting documentation. Accepted formats: PDF, JPG, JPEG, PNG (Max: 2MB)</small>
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
                    <div class="alert alert-danger mt-2">{{ $errorMessage }}</div>
                @endif
            </x-pages.model>

            @include('livewire.hr.leave.partials.progress-modal', ['progressLeave' => $progressLeave, 'ownRequest' => true])
        @endif
    </div>