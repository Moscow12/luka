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
    <div class="row g-6">
        <div class="col-xl-4 col-lg-5 d-flex flex-column gap-6">
            <x-pages.card title="Contract Form Information" >
              <div class='d-flex justify-content-between align-items-center mb-6'>
                <form wire:submit.prevent="storecontacts" class="row" enctype="multipart/form-data">
                    @csrf
                    <div class="row">
                        <!-- workstions -->
                        <x-forms.input type="select" name="workstation_id" label="Workstation" :options="$workstations" required />                    
                        <x-forms.input type="select" name="department_id" label="Department" :options="$departments" required />
                        <x-forms.input type="select" name="position_id" label="Position" :options="$positions->pluck('name', 'id')" required />
                        <x-forms.input type="select" name="contract_type" label="Contract Type" :options="['Permanent', 'Temporary', 'Part time']" required />
                        <x-forms.input type="date" name="start_date" label="Start Date" required />
                        <x-forms.input type="date" name="expire_date" label="Expire Date" required />
                        <x-forms.input type="select" name="expirenotification" label="Expire Notification days" :options="['30', '60', '90', '120']" required />
                        <x-forms.input type="textarea" name="description" label="Descriptions" rows="2" required />
                        <input type="file" wire:model="attachment" name="attachment" label="Attachment" />
                    </div>
                    <x-forms.button type="submit" variant="primary" block>SUBMIT</x-forms.button>
                </form>
              </div>
              
            </x-pages.card>
        </div>
        <div class="col-xl-8 col-lg-7 d-flex flex-column gap-6">
            <x-pages.card title="Contract">
                <div class='d-flex justify-content-between align-items-center mb-6'>
                    <div class='d-flex align-items-center gap-2'>
                        <span>Contract</span>
                        <span class='text-secondary'>$12,000</span>
                    </div>
                    <div>
                        <a href='#!' class='btn btn-white'>Actions</a>
                    </div>
                </div>
            </x-pages.card>
        </div>
    </div>
</div>