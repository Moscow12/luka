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
        <h5 class="mb-5">Disciplinary Actions</h5>
        <div class="d-flex flex-column gap-6">
            <div class="d-flex flex-md-row flex-column gap-2 justify-content-between">
                <div class="d-flex flex-row gap-3 align-items-center">
                    <div>
                        <form>
                            <input class="form-control" type="search" value="" placeholder="Search" />
                        </form>
                        <!-- success message if session has flash message -->
                        @if(session()->has('success'))
                            <div class="alert alert-success alert-dismissible fade show" role="alert">
                                {{ session('success') }}
                                <button type="button" class="btn-close" data-bs-dismiss="alert" aria-label="Close"></button>
                            </div>
                        @endif
                         
                    </div>
                    <a href="#!" class="text-inherit">
                        <i class="fa-solid fa-filter"></i>
                        <span>Filter</span>
                    </a>
                </div>
                <div>
                    <x-forms.button-model name="ADD DISPLINARY CASE"/>
                </div>
            </div>
        </div>
        <!--  -->
        <x-pages.card title="Disciplinary Actions List">
            <div class="table-responsive">
                <table class="table table-hover align-middle">
                    <thead>
                        <tr>
                            <th>Type of Violation</th>
                            <th>Violation Date</th>
                            <th>Comments</th>
                            <th>Actions</th>
                        </tr>
                    </thead>
                    <tbody>
                        @forelse($displineissues as $displineissue)
                        <tr>
                            <td>{{ $displineissue->violation->violation_type }}</td>
                            <td>{{ $displineissue->violation_date }}</td>
                            <td>{{ $displineissue->notes }}</td>
                            <td>
                                <x-forms.button-model name="EDIT" wire:click="openModal('edit', {{ $displineissue->id }})" />
                                <a href="#" class="btn btn-sm btn-danger" wire:click="delete('{{ $displineissue->id }}')">Delete</a>
                            </td>
                        </tr>
                        @empty
                        <tr>
                            <td colspan="4" class="text-center">No Disciplinary Actions found.</td>
                        </tr>
                        @endforelse
                    </tbody>
                </table>
            </div>
        </x-pages.card>
        <x-pages.model :title=" $modalMode === 'edit' ? 'Edit Disciplinary Action' : 'Add Disciplinary Action' " :formaction=" $modalMode ==='edit' ? 'update' : 'save' " :modalMode="$modalMode" :showModal="$showModal">
            <x-forms.input type="select" name="violation_id" label="Violation" :options="$violations->pluck('violation_type', 'id')" required />
            <x-forms.input type="date" name="violation_date" label="Violation Date" required />
            <x-forms.input type="textarea" name="notes" label="Notes" rows="2" required />
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
