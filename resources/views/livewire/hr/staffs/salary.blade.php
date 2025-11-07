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
            <x-pages.card title="Set Salary Information">
                <form action="" method="post">
                    @csrf
                    <div class="row">
                        <div class="col-sm-12">
                            <div class="form-floating mb-3">
                               <x-forms.input type="text" name="salary" label="Salary" placeholder="Salary" required />
                               @foreach ($deductions as $deduction)
                                   <x-forms.checkbox name="{{ $deduction->id }}" label="{{ $deduction->name }}" value="{{ $deduction->name }}" />
                               @endforeach
                               
                            </div>
                        </div>
                    </div>

                    <!-- form group -->
                    <div class="d-grid">
                        <button type="submit" class="btn btn-primary w-100">
                            SAVE
                        </button>
                    </div>
                </form>
            </x-pages.card>
        </div>
        <div class="col-xl-4 col-lg-5 d-flex flex-column gap-6">
            <x-pages.card title="Deductions">
                
            </x-pages.card>
        </div>
        <div class="col-xl-4 col-lg-5 d-flex flex-column gap-6">
            <x-pages.card title="Allowances">
                
            </x-pages.card>
        </div>
    </div>
</div>
