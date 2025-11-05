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
                <x-forms.button-model name="ADD QUALIFICATION"/>
            </div>
        </div>
    </div>
    <div>
        <div class="card card-lg overflow-hidden" id="taskTable" data-list="name">
            <div class="card-body p-0">
                <div class="table-responsive">

                </div>
            </div>
        </div>
    </div>
    <x-pages.model :showModal="$showModal" :formaction="$modalMode === 'edit' ? 'update' : 'save'" :modalMode="$modalMode" :title="$modalMode === 'edit' ? 'Edit Deduction' : 'Add Deduction'">
        @csrf
        
    </x-pages.model>
</div>