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
    <x-pages.empheader :age="$age" :gender="$gender" :email="$email" :getFullName="$getfullname" :editUrl="$editUrl" :employee_id="$employee_id" />
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
                                <th>Start Date</th>
                                <th>Expire Date</th>
                                <th>Expire Notification</th>
                                <th>Attachment</th>
                                <th>Actions</th>
                            </tr>
                        </thead>
                        <tbody>
                            @foreach($contracts as $contract)
                            <tr>
                                <td>{{ $contract->contract_type }}</td>
                                <td>
                                    <span class="badge {{ $contract->getStatusBadgeClass() }}">
                                        {{ $contract->getStatusLabel() }}
                                    </span>
                                </td>
                                <td>{{ $contract->start_date->format('d M Y') }}</td>
                                <td>{{ $contract->expire_date->format('d M Y') }}</td>
                                <td>{{ $contract->expirenotification }} days</td>
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
                                        <x-forms.button-model name="EDIT" :classbtn="'fa-solid fa-pencil'" wire:click="openModal('edit', '{{ $contract->id }}')" />

                                        @if($contract->status === 'active' && !$contract->isExpired())
                                            <button wire:click="suspendContract('{{ $contract->id }}')"
                                                    class="btn btn-sm btn-warning"
                                                    title="Suspend Contract">
                                                <i class="fa-solid fa-pause"></i>
                                            </button>
                                        @elseif($contract->status === 'suspended')
                                            <button wire:click="activateContract('{{ $contract->id }}')"
                                                    class="btn btn-sm btn-success"
                                                    title="Activate Contract">
                                                <i class="fa-solid fa-play"></i>
                                            </button>
                                        @endif

                                        <button wire:click="delete('{{ $contract->id }}')"
                                                class="btn btn-sm btn-danger"
                                                onclick="return confirm('Are you sure you want to delete this contract?')">
                                            <i class="fa-solid fa-trash"></i>
                                        </button>
                                    </div>
                                </td>
                            </tr>
                            @endforeach
                        </tbody>                        
                    </table>
                </div>
            </x-pages.card>
        </div>
        <x-pages.model :title=" $modalMode === 'edit' ? 'Edit Contract' : 'Add Contract' " :formaction=" $modalMode ==='edit' ? 'update' : 'save' " :modalMode="$modalMode" :showModal="$showModal">
            <x-forms.input type="select" name="workstation_id" label="Workstation" :options="$workstations->pluck('workstation_name', 'id')" required />
            <x-forms.input type="select" name="department_id" label="Department" :options="$departments->pluck('name', 'id')" required />
            <x-forms.input type="select" name="position_id" label="Position" :options="$positions->pluck('name', 'id')" required />
            <x-forms.input type="select" name="contract_type" label="Contract Type" :options="['Permanent'=>'Permanent', 'Temporary'=>'Temporary', 'Part time'=>'Part time']" required />
            <x-forms.input type="date" name="start_date" label="Start Date" required />
            <x-forms.input type="date" name="expire_date" label="Expire Date" required />
            <x-forms.input type="select" name="expirenotification" label="Expire Notification days" :options="['30'=>'30', '60'=>'60', '90'=>'90', '120'=>'120']" required />
            <x-forms.input type="textarea" name="description" label="Descriptions" rows="2" required />
            <label class="form-label">Upload attachment</label>
            <input type="file" class="form-control" wire:model="attachment">
            @error('attachment') <small class="text-danger">{{ $message }}</small> @enderror
            {{-- Show loading indicator while uploading --}}
            <div wire:loading wire:target="attachment" class="text-muted mt-2">
                Uploading...
            </div>
        </x-pages.model>
    </div>
</div>