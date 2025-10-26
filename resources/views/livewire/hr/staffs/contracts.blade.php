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
    <x-pages.empheader :age="$age" :gender="$gender" :email="$email" :getFullName="$getfullname" :editUrl="$editUrl" />
    <div class="row g-6">
        <div class="col-xl-4 col-lg-5 d-flex flex-column gap-6">
            <x-pages.card title="Contract" >
              <div class='d-flex justify-content-between align-items-center mb-6'>
                <x-forms.input label="Contract Type" placeholder="Full-time" name="contract_type" />
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