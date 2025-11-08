<div>
    <!-- attendance -->
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
        <h5 class="mb-5">Attendance</h5>
    </div>
    <div class="d-flex flex-column gap-6">
        <div class="d-flex flex-md-row flex-column gap-2 justify-content-between">
            <div class="d-flex flex-row gap-3 align-items-center">
                <div>
                    <form>
                        <input class="form-control" type="search" value="" placeholder="Search" />
                        <input class="form-control" type="date" name="clockdate"  />
                    </form>
                </div>
                <a href="#!" class="text-inherit">
                    <i class="fa-solid fa-filter"></i>
                    <span>Filter</span>
                </a>
            </div>
            <div>
                <x-forms.button-model name="ADD ATTENDANCE"/>
            </div>
        </div>
        
        @if (session()->has('success'))
            <div class="alert alert-success alert-dismissible fade show" role="alert">
                {{ session('success') }}
                <button type="button" class="btn-close" data-bs-dismiss="alert" aria-label="Close"></button>
            </div>
        @endif
        <div>
            <x-pages.card title="Attendance">
                <div class="table-responsive">
                    <table class="table table-hover align-middle">
                        <thead>
                            <tr>
                                <th>Date</th>
                                <th>Clock In Time</th>
                                <th>Clock Out Time</th>
                                <th>Clock Status</th>
                                <th>Actions</th>
                            </tr>
                        </thead>
                        <tbody>
                            @foreach($attendances as $attendance)
                            <tr>
                                <td>{{ $attendance->date }}</td>
                                <td>{{ $attendance->clocktime }}</td>
                                <td>{{ $attendance->clock_out }}</td>
                                <td>{{ $attendance->clock_status }}</td>
                                <td>{{ $attendance->attendance }}</td>
                                <td>
                                    <x-forms.button-model name="EDIT" wire:click="openModal('edit', {{ $attendance->id }})" />
                                    <a href="#" class="btn btn-sm btn-danger" wire:click="delete({{ $attendance->id }})">Delete</a>
                                </td>
                            </tr>
                            @endforeach
                        </tbody>
                    </table>
                </div>
            </x-pages.card>
        </div>
        <x-pages.model :showModal="$showModal" :formaction="$modalMode === 'edit' ? 'update' : 'save'" :modalMode="$modalMode" :title="$modalMode === 'edit' ? 'Edit Attendance' : 'Add Attendance'">
            <x-forms.input type="date" name="date" label="Date" required />
            <x-forms.input type="select" name="attendance" label="Attendance" :options="['Present', 'Absent']" required />
        </x-pages.model>
    </div>
</div>
