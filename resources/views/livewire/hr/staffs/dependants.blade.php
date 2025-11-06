<div>
    <x-pages.breadcrumn title="Staff Details"
        :breadcrumbs="[
        ['label' => 'Home', 'url' => route('dashboard')], 
        ['label' => 'Staff List', 'url' => route('hr.stafflist')],
        ['label' => 'Staff Details']  
        ]">
        <a href="{{ route('hr.stafflist') }}" class="btn btn-sm btn-primary">Back to List</a>
    </x-pages.breadcrumn>
    <!-- row -->
    <x-pages.empheader :age="$age" :gender="$gender" :email="$email" :getFullName="$getfullname" :editUrl="$editUrl" :employee_id="$employee_id" />
    <div>
        <h5 class="mb-5">Dependants</h5>
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
                <x-forms.button-model name="ADD DEPENDANT"/>
            </div>
        </div>
        @if (session()->has('success'))
            <div class="alert alert-success alert-dismissible fade show" role="alert">
                {{ session('success') }}
                <button type="button" class="btn-close" data-bs-dismiss="alert" aria-label="Close"></button>
            </div>
        @endif
        <div>
            <x-pages.card title="Dependants List">
                <div class="table-responsive">
                    <table class="table table-hover align-middle">
                        <thead>
                            <tr>
                                <th>Name</th>
                                <th>Relation</th>
                                <th>Age</th>
                                <th>Gender</th>
                                <th>Phone</th>
                                <th>Email</th>
                                <th>Occupation</th>
                                <th>Address</th>
                                <th>Actions</th>
                            </tr>
                        </thead>
                        <tbody>
                            @foreach($dependants as $dependant)
                            <tr>
                                <td>{{ $dependant->name }}</td>
                                <td>{{ $dependant->relationship }}</td>
                                <td>{{ $dependant->age }}</td>
                                <td>{{ $dependant->gender }}</td>
                                <td>{{ $dependant->phone }}</td>
                                <td>{{ $dependant->email }}</td>
                                <td>{{ $dependant->occupation }}</td>
                                <td>{{ $dependant->address }}</td>
                                <td>
                                    <x-forms.button-model name="EDIT" wire:click="openModal('edit', {{ $dependant->id }})" />
                                    <a href="#" class="btn btn-sm btn-danger" wire:click="delete('{{ $dependant->id }}')">Delete</a>
                                </td>
                            </tr>
                            @endforeach
                        </tbody>
                    </table>
                </div>
            </x-pages.card>
        </div>
        <x-pages.model :title=" $modalMode === 'edit' ? 'Edit Dependant' : 'Add Dependant' " :formaction=" $modalMode ==='edit' ? 'update' : 'save' " :modalMode="$modalMode" :showModal="$showModal">
            <x-forms.input type="text" name="name" label="Name" required />
            <x-forms.input type="select" name="relationship" label="Relationship" :options="['Father', 'Mother', 'Brother', 'Sister', 'Son', 'Daughter', 'Other']" :value="['Father', 'Mother', 'Brother', 'Sister', 'Son', 'Daughter', 'Other']" required />
            <x-forms.input type="date" name="dob" label="Date of Birth" required />
            <x-forms.input type="text" name="phone" label="Phone" required />
            <x-forms.input type="text" name="dependantsemail" label="Email" required />
            <x-forms.input type="text" name="occupation" label="Occupation" required />
            <x-forms.input type="text" name="address" label="Address" required />            
            <x-forms.input type="select" name="gender" label="Gender" :options="['Male', 'Female', 'Other']" :value="['Male', 'Female', 'Other']" required />
            <x-forms.checkbox type="checkbox" name="is_next_of_kin" label="Is Next of Kin" required />
        </x-pages.model>
    </div>
</div>
