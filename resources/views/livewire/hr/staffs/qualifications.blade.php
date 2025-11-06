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
        <h5 class="mb-5">Qualification</h5>
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
                <x-forms.button-model :classbtn="'fa-solid fa-plus'" name="ADD QUALIFICATION"/>
            </div>
        </div>
    </div>
    <div>
        <div class="card card-lg overflow-hidden" id="taskTable" data-list="name">
            <div class="card-body p-0">
                <div class="table-responsive">
                    <table class="table table-hover align-middle">
                        <thead>
                            <tr>
                                <th>Education Level</th>
                                <th>Institution</th>
                                <th>Start Date</th>
                                <th>End Date</th>
                                <th>Attachment</th>
                                <th>Comments</th>
                                <th>Actions</th>
                            </tr>
                        </thead>
                        <tbody>
                            @foreach($qualifications as $qualification)
                            <tr>
                                <td>{{ $qualification->education_level }}</td>
                                <td>{{ $qualification->institution }}</td>
                                <td>{{ $qualification->start_date }}</td>
                                <td>{{ $qualification->end_date }}</td>
                                <td>
                                    <a href="{{ asset('storage/' . $qualification->attachment) }}" target="_blank">
                                        Preview
                                    </a>
                                </td>
                                <td>{{ $qualification->comments }}</td>
                                <td>
                                    <x-forms.button-model name="EDIT" :classbtn="'fa-solid fa-pencil'" wire:click="openModal('edit', {{ $qualification->id }})" />
                                    <a href="#" class="btn btn-sm btn-danger" wire:click="delete({{ $qualification->id }})">Delete</a>
                                </td>
                            </tr>
                            @endforeach
                        </tbody>
                    </table>
                </div>
            </div>
        </div>
    </div>
    <x-pages.model :showModal="$showModal" :formaction="$modalMode === 'edit' ? 'update' : 'save'" :modalMode="$modalMode" :title="$modalMode === 'edit' ? 'Edit Qualification' : 'Add Qualification'">
       
        <div class="row">
            <x-forms.input type="select" name="education_level" label="Education Level" :options="['Certificate', 'Diploma', 'Degree', 'Masters', 'PhD', 'Others']"  :value="$education_level" required />
            <x-forms.input type="text" name="institution" label="Institution" required />
            <x-forms.input type="date" name="start_date" label="Start Date" required />
            <x-forms.input type="date" name="end_date" label="End Date" required />
            <x-forms.input type="file" name="attachment" label="Attachment" />
            <x-forms.input type="textarea" name="comments" label="Comments" rows="3" />
        </div>
        
    </x-pages.model>
</div>