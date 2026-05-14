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
        <h5 class="mb-5">Contract details</h5>
    </div>
    <div class="d-flex flex-column gap-6">
        <div class="d-flex flex-md-row flex-column gap-2 justify-content-between">
            <div class="d-flex flex-row gap-3 align-items-center">
                <div>
                    <form>
                        <input class="form-control" type="search" value="" placeholder="Search" />
                    </form>
                </div>
                <a href="#!" class="text-inherit">
                    <i class="fa-solid fa-filter"></i>
                    <span>Filter</span>
                </a>
            </div>
            <div>
                @if($canAddNewContract)
                    <x-forms.button-model name="ADD CONTRACT" />
                @else
                    <button type="button" class="btn btn-secondary btn-sm" disabled title="{{ $activeContractMessage }}">
                        ADD CONTRACT (Disabled)
                    </button>
                @endif
            </div>
        </div>
        @if(!$canAddNewContract)
            <div class="alert alert-warning alert-dismissible fade show" role="alert">
                <i class="fa-solid fa-exclamation-triangle"></i>
                <button type="button" class="btn-close" data-bs-dismiss="alert" aria-label="Close"></button>
                {{ $activeContractMessage }}
            </div>
        @elseif($activeContractMessage)
            <div class="alert alert-info alert-dismissible fade show" role="alert">
                <i class="fa-solid fa-info-circle"></i>
                <button type="button" class="btn-close" data-bs-dismiss="alert" aria-label="Close"></button>
                {{ $activeContractMessage }}
            </div>
        @endif
        <div>
            <x-pages.card title="Contract List">
                <div class="table-responsive">
                    <table class="table table-hover align-middle">
                        <thead>
                            <tr>
                                <th>Contract Type</th>
                                <th>Status</th>
                                <th>Base Salary</th>
                                <th>Payment Frequency</th>
                                <th>Start Date</th>
                                <th>Expire Date</th>
                                <th>Notification</th>
                                <th>Attachment</th>
                                <th>Actions</th>
                            </tr>
                        </thead>
                        <tbody>
                            @foreach($contracts as $contract)
                            <tr>
                                <td>
                                    <span class="badge bg-primary">
                                        {{ ucfirst(str_replace('_', ' ', $contract->contract_type)) }}
                                    </span>
                                </td>
                                <td>
                                    <span class="badge {{ $contract->getStatusBadgeClass() }}">
                                        {{ $contract->getStatusLabel() }}
                                    </span>
                                </td>
                                <td>{{ number_format($contract->base_salary, 2) }}</td>
                                <td>{{ ucfirst(str_replace('-', ' ', $contract->payment_frequency)) }}</td>
                                <td>{{ $contract->start_date->format('d M Y') }}</td>
                                <td>{{ $contract->expire_date->format('d M Y') }}</td>
                                <td>
                                    @if($contract->expirenotification)
                                        <span class="badge bg-success">
                                            <i class="fa-solid fa-bell"></i> {{ $contract->notify_time ?? 'Enabled' }}
                                        </span>
                                    @else
                                        <span class="badge bg-secondary">Disabled</span>
                                    @endif
                                </td>
                                <td>
                                    @if($contract->attachment)
                                        <a href="{{ asset('storage/' . $contract->attachment) }}" target="_blank" class="btn btn-sm btn-outline-primary">
                                            <i class="fa-solid fa-paperclip"></i> View
                                        </a>
                                    @else
                                        <span class="text-muted">No attachment</span>
                                    @endif
                                </td>
                                <td>
                                    <div class="d-flex flex-row gap-2 justify-content-start">
                                        @if($contract->status !== 'terminated')
                                            <x-forms.button-model name="EDIT" :classbtn="'fa-solid fa-pencil'" wire:click="openModal('edit', '{{ $contract->id }}')" />

                                            @if($contract->status === 'active' && !$contract->isExpired())
                                                <button wire:click="suspendContract('{{ $contract->id }}')"
                                                        class="btn btn-sm btn-warning"
                                                        title="Suspend Contract">
                                                    <i class="fa-solid fa-pause"></i>
                                                </button>
                                                <button wire:click="openTerminateModal('{{ $contract->id }}')"
                                                        class="btn btn-sm btn-dark"
                                                        title="Terminate Contract">
                                                    <i class="fa-solid fa-ban"></i>
                                                </button>
                                            @elseif($contract->status === 'suspended')
                                                <button wire:click="activateContract('{{ $contract->id }}')"
                                                        class="btn btn-sm btn-success"
                                                        title="Activate Contract">
                                                    <i class="fa-solid fa-play"></i>
                                                </button>
                                                <button wire:click="openTerminateModal('{{ $contract->id }}')"
                                                        class="btn btn-sm btn-dark"
                                                        title="Terminate Contract">
                                                    <i class="fa-solid fa-ban"></i>
                                                </button>
                                            @endif

                                            <button wire:click="delete('{{ $contract->id }}')"
                                                    class="btn btn-sm btn-danger"
                                                    onclick="return confirm('Are you sure you want to delete this contract?')">
                                                <i class="fa-solid fa-trash"></i>
                                            </button>
                                        @else
                                            <span class="badge bg-secondary">Terminated</span>
                                            @if($contract->termination_reason)
                                                <small class="text-muted">{{ $contract->getTerminationReasonLabel() }}</small>
                                            @endif
                                        @endif
                                    </div>
                                </td>
                            </tr>
                            @endforeach
                        </tbody>                        
                    </table>
                </div>
            </x-pages.card>
        </div>
        <x-pages.model :title=" $modalMode === 'edit' ? 'Edit Contract' : 'Add Contract' " :formaction=" $modalMode ==='edit' ? 'update' : 'save' " :modalMode="$modalMode" :showModal="$showModal" :size="'lg'">
            <div class="row g-3">
                <div class="col-12 col-lg-6">
                    <x-forms.input type="select" name="workstation_id" label="Workstation" :options="$workstations->pluck('workstation_name', 'id')" required />
                </div>
                <div class="col-12 col-lg-6">
                    <x-forms.input type="select" name="department_id" label="Department" :options="$departments->pluck('name', 'id')" />
                </div>
                <div class="col-12 col-lg-6">
                    <x-forms.input type="select" name="position_id" label="Position" :options="$positions->pluck('name', 'id')" required />
                </div>
                <div class="col-12 col-lg-6">
                    <x-forms.input type="select" name="contract_type" label="Contract Type" :options="['permanent'=>'Permanent', 'temporary'=>'Temporary', 'part_time'=>'Part Time', 'probation'=>'Probation', 'internship'=>'Internship', 'consultancy'=>'Consultancy', 'other'=>'Other']" required />
                </div>
                <div class="col-12 col-lg-6">
                    <x-forms.input type="date" name="start_date" label="Start Date" required />
                </div>
                <div class="col-12 col-lg-6">
                    <x-forms.input type="date" name="expire_date" label="Expire Date" required />
                </div>
                <div class="col-12 col-lg-6">
                    <x-forms.input type="select" name="payment_frequency" label="Payment Frequency" :options="['monthly'=>'Monthly', 'weekly'=>'Weekly', 'bi-weekly'=>'Bi-Weekly', 'daily'=>'Daily', 'hourly'=>'Hourly']" required />
                </div>
                <div class="col-12 col-lg-6">
                    <x-forms.input type="number" name="base_salary" label="Base Salary" placeholder="Enter Base Salary" step="0.01" required />
                </div>
                <div class="col-12 col-lg-6">
                    <div class="mb-3">
                        <div class="form-check">
                            <input type="checkbox" class="form-check-input" wire:model="expirenotification" id="expirenotification">
                            <label class="form-check-label" for="expirenotification">Enable Expiration Notification</label>
                        </div>
                    </div>
                </div>
                <div class="col-12 col-lg-6">
                    <x-forms.input type="text" name="notify_time" label="Notification Time" placeholder="e.g., 30 days before, 1 week before" />
                </div>
                <div class="col-12">
                    <x-forms.input type="textarea" name="description" label="Descriptions" rows="2" />
                </div>
                <div class="col-12">
                    <label class="form-label">Upload attachment</label>
                    <input type="file" class="form-control" wire:model="attachment">
                    @error('attachment') <small class="text-danger">{{ $message }}</small> @enderror
                    {{-- Show loading indicator while uploading --}}
                    <div wire:loading wire:target="attachment" class="text-muted mt-2">
                        Uploading...
                    </div>
                </div>
            </div>
        </x-pages.model>

        <!-- Terminate Contract Modal -->
        @if($showTerminateModal)
        <div class="modal fade show d-block" tabindex="-1" style="background-color: rgba(0,0,0,0.5);">
            <div class="modal-dialog modal-dialog-centered">
                <div class="modal-content">
                    <div class="modal-header bg-dark text-white">
                        <h5 class="modal-title">
                            <i class="fa-solid fa-ban"></i> Terminate Contract
                        </h5>
                        <button type="button" class="btn-close btn-close-white" wire:click="closeTerminateModal"></button>
                    </div>
                    <form wire:submit.prevent="terminateContract">
                        <div class="modal-body">
                            <div class="alert alert-warning" role="alert">
                                <i class="fa-solid fa-exclamation-triangle"></i>
                                <strong>Warning:</strong> Terminating this contract will mark the employee as inactive.
                            </div>
                            <div class="row g-3">
                                <div class="col-12">
                                    <label class="form-label">Termination Reason <span class="text-danger">*</span></label>
                                    <select class="form-select" wire:model="termination_reason" required>
                                        <option value="">Select Reason</option>
                                        <option value="contract_ended">Contract Ended</option>
                                        <option value="resigned">Resigned</option>
                                        <option value="terminated">Terminated</option>
                                        <option value="deceased">Deceased</option>
                                        <option value="transferred">Transferred</option>
                                        <option value="retired">Retired</option>
                                        <option value="study_leave">Study Leave</option>
                                        <option value="absconded">Absconded</option>
                                        <option value="other">Other</option>
                                    </select>
                                    @error('termination_reason') <small class="text-danger">{{ $message }}</small> @enderror
                                </div>
                                <div class="col-12">
                                    <label class="form-label">Termination Date <span class="text-danger">*</span></label>
                                    <input type="date" class="form-control" wire:model="termination_date" required>
                                    @error('termination_date') <small class="text-danger">{{ $message }}</small> @enderror
                                </div>
                                <div class="col-12">
                                    <label class="form-label">Notes</label>
                                    <textarea class="form-control" wire:model="termination_notes" rows="3" placeholder="Enter additional notes (optional)"></textarea>
                                    @error('termination_notes') <small class="text-danger">{{ $message }}</small> @enderror
                                </div>
                            </div>
                        </div>
                        <div class="modal-footer">
                            <button type="button" class="btn btn-secondary" wire:click="closeTerminateModal">Cancel</button>
                            <button type="submit" class="btn btn-dark">
                                <i class="fa-solid fa-ban"></i> Terminate Contract
                            </button>
                        </div>
                    </form>
                </div>
            </div>
        </div>
        @endif
    </div>
</div>