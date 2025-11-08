<?php

namespace App\Livewire\Hr\Staffs;

use App\Models\allowances;
use App\Models\Deduction;
use App\Models\Employee;
use App\Models\Employeecontracts;
use Livewire\Component;

class Salary extends Component
{
    public  $deductions, $is_hourly, $contract_id, $contacts, $allowances;
    public $employee_id;
    public $first_name, $middle_name, $last_name, $gender, $getfullname,$photo, $age, $email, $editUrl;
    public function mount($id=null)
    {
        $staff = Employee::findOrFail($id);
        $this->employee_id = $id;

        $this->first_name = $staff->first_name;
        $this->middle_name = $staff->middle_name;
        $this->last_name = $staff->last_name;
        $this->getfullname = $staff->getFullName();
        $this->age = $staff->getAgeAttribute();
        $this->gender = $staff->gender;
        $this->email = $staff->email;
        $this->photo = $staff->photo;
        $this->contacts = Employeecontracts::where('employee_id', $this->employee_id)->where('status', 'active')->get();
        $this->editUrl = route('hr.editstaff', $id);

        $this->allowances = allowances::where('is_active', true)->get();
        $this->deductions = Deduction::where('is_active', true)->get();

    }
    public function render()
    {
        return view('livewire.hr.staffs.salary');
    }
}
