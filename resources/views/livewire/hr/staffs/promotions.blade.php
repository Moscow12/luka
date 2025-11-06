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
        <h5 class="mb-5">Promotions</h5>
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
                <!-- success message if session has flash message -->
                @if(session()->has('success'))
                    <div class="alert alert-success alert-dismissible fade show" role="alert">
                        {{ session('success') }}
                        <button type="button" class="btn-close" data-bs-dismiss="alert" aria-label="Close"></button>
                    </div>
                @endif
                <div>
                    <x-forms.button-model name="ADD PROMOTION"/>
                </div>
            </div>
        </div>
        <x-pages.card title="Promotions List">
            <div class="table-responsive">
                <table class="table table-hover align-middle">
                    <thead>
                        <tr>
                            <th>Promotion Title</th>
                            <th>Promotion Date</th>
                            <th>Comments</th>
                            <th>Preview Attachment</th>
                            <th>Actions</th>
                        </tr>
                    </thead>
                    <tbody>
                        @forelse($promotions as $promotion)
                        <tr>
                            <td>{{ $promotion->title->name }}</td>
                            <td>{{ $promotion->start_date }}</td>
                            <td>{{ $promotion->comments }}</td>
                            <td>
                                <a href="{{ asset('storage/' . $promotion->attachment) }}" target="_blank">
                                        Preview
                                    </a>
                            </td>
                            <td>
                                <x-forms.button-model name="EDIT" wire:click="openModal('edit', {{ $promotion->id }})" />
                                <a href="#" class="btn btn-sm btn-danger" wire:click="delete('{{ $promotion->id }}')">Delete</a>
                            </td>
                        </tr>
                        @empty
                            <tr>
                                <td colspan="4">No data available.</td>
                            </tr>
                        @endforelse
                    </tbody>
                </table>
            </div>
        </x-pages.card>   
        <x-pages.model :title=" $modalMode === 'edit' ? 'Edit Promotion' : 'Add Promotion' " :formaction=" $modalMode ==='edit' ? 'update' : 'save' " :modalMode="$modalMode" :showModal="$showModal">
            <x-forms.input type="select" name="title_id" label="Title" :options="$titles->pluck('name', 'id')" required />
            <x-forms.input type="select" name="workstation_id" label="Workstation" :options="$workstations->pluck('workstation_name', 'id')" required />
            <x-forms.input type="select" name="department_id" label="Department" :options="$departments->pluck('name', 'id')" required />
            <x-forms.input type="date" name="start_date" label="Start Date" required />
            <x-forms.input type="textarea" name="comments" label="comments" rows="2" required />

            <label class="form-label">Upload attachment</label>
            <input type="file" class="form-control" wire:model="attachment">
            @error('attachment') <small class="text-danger">{{ $message }}</small> @enderror
            {{-- Show loading indicator while uploading --}}
            <div wire:loading wire:target="attachment" class="text-muted mt-2">
                Uploading...
            </div>
        </x-pages.model>
</div>
