<?php

namespace App\Livewire\Hr\Staffs;

use App\Models\Employee;
use Livewire\Component;

class Contracts extends Component
{
    public $employee_id;
    public $first_name, $middle_name, $last_name, $gender, $getfullname, $age, $email, $editUrl;
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
        $this->editUrl = route('hr.editstaff', $id);
    }
    public function render()
    {
        return view('livewire.hr.staffs.contracts');
    }
}
