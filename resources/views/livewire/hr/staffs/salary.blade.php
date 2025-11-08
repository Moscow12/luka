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
    <x-pages.empheader :age="$age" :gender="$gender" :email="$email" :getFullName="$getfullname" :editUrl="$editUrl" :employee_id="$employee_id" :photo="$photo" />
    <div class="row g-6">
        <div class="col-xl-4 col-lg-5 d-flex flex-column gap-6">
            <x-pages.card title="Set Salary allowance and deductions">
                <form action="" wire:submit.prevent="saveSalary">
                    @csrf
                    <div class="row">
                        <div class="col-sm-12">
                            <div class="form-floating mb-3">
                                <x-forms.input type="text" name="salary" label="Salary" placeholder="Salary" value="{{ number_format($contacts->first()->base_salary) }}" required />
                               
                                <h5 class="mb-6">Allowances</h5>
                                <div class="bg-gray-100 p-4 rounded-3 mb-7"> 
                                    @foreach ($deductions as $deduction)                   
                                        <div class="d-flex flex-column gap-1">                        
                                        <div class="d-flex justify-content-between">                        
                                            <x-forms.checkbox name="{{ $deduction->id }}" label="{{ $deduction->name }}" value="{{ $deduction->id }}" />
                                            <span class="text-secondary"> {{ $deduction->deduction_value }}</span>
                                        </div>                      
                                        </div>
                                    @endforeach
                                </div>
                                <h5 class="mb-6">Deductions</h5>
                                <div class="bg-gray-100 p-4 rounded-3 mb-7"> 
                                @foreach ($allowances as $allowance)
                                    <div class="d-flex flex-column gap-1">                        
                                        <div class="d-flex justify-content-between">   
                                            <x-forms.checkbox name="{{ $allowance->id }}" label="{{ $allowance->name }}" value="{{ $allowance->id }}" />
                                            <span class="text-secondary"> {{ $allowance->allowance_value }}</span>
                                        </div>                      
                                    </div>
                                @endforeach
                                </div>
                            </div>
                        </div>
                    </div>
                    
                <div class="d-grid">
                    <button type="submit" class="btn btn-primary w-100">
                        SAVE
                    </button>
                </div>
        </form>
        <div class="card card-lg mb-6">
            <!-- card body -->
            <div class="card-body">
                
            </div>
        </div>
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