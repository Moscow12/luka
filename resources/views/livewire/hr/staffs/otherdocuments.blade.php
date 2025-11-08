<div>
    <!-- Others documents attachement   -->
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
        <h5 class="mb-5">Other Documents</h5>
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
                <x-forms.button-model name="ADD OTHER DOCUMENT"/>
            </div>
        </div>
        @if (session()->has('success'))
            <div class="alert alert-success alert-dismissible fade show" role="alert">
                {{ session('success') }}
                <button type="button" class="btn-close" data-bs-dismiss="alert" aria-label="Close"></button>
            </div>
        @endif
        <div>
            <x-pages.card title="Other Documents List">
                <div class="table-responsive">
                    <table class="table table-hover align-middle">
                        <thead>
                            <tr>
                                <th>Name</th>
                                <th>Type</th>
                                <th>Preview Attachment</th>
                                <th>Actions</th>
                            </tr>
                        </thead>
                        <tbody>
                            @foreach($otherdocuments as $otherdocument)
                            <tr>
                                <td>{{ $otherdocument->type }}</td>
                                <td>{{ $otherdocument->description }}</td>
                                <td>
                                    <a href="{{ asset('storage/' . $otherdocument->attachment) }}" target="_blank">
                                        Preview
                                    </a>

                                </td>
                                <td>
                                    <x-forms.button-model name="EDIT" wire:click="openModal('edit', {{ $otherdocument->id }})" />
                                    <a href="#" class="btn btn-sm btn-danger" wire:click="delete('{{ $otherdocument->id }}')">Delete</a>
                                </td>
                            </tr>
                            @endforeach
                        </tbody>
                    </table>
                </div>
            </x-pages.card>
        </div>
        <x-pages.model :title=" $modalMode === 'edit' ? 'Edit Other Document' : 'Add Other Document' " :formaction=" $modalMode ==='edit' ? 'update' : 'save' " :modalMode="$modalMode" :showModal="$showModal">           
            <x-forms.input type="select" name="type" label="Type" :options="['Passport', 'Driving License', 'ID Card', 'Visa', 'Others']" required />
            <x-forms.input type="textarea" name="description" label="description" rows="2" required />
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
