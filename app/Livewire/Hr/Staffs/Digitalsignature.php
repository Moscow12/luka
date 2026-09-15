<?php

namespace App\Livewire\Hr\Staffs;

use App\Models\Employee;
use Livewire\Component;

class Digitalsignature extends Component
{
    public $seach = '';

    public $first_name, $middle_name, $last_name, $gender, $getfullname, $age, $email, $editUrl, $photo;
    public $employee_id;
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
        $this->editUrl = route('hr.editstaff', $id);
    }
    public function render()
    {
        return view('livewire.hr.staffs.digitalsignature');
    }
}
