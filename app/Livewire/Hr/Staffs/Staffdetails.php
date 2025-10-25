<?php

namespace App\Livewire\Hr\Staffs;

use App\Models\Employee;
use Livewire\Component;

class Staffdetails extends Component
{
    public $employee_id;
    public $first_name, $middle_name, $last_name, $gender;
    public function mount($id=null)
    {
        $staff = Employee::findOrFail($id);
        $this->employee_id = $id;
        $this->first_name = $staff->first_name;
        $this->middle_name = $staff->middle_name;
        $this->last_name = $staff->last_name;
        $this->gender = $staff->gender;
    }
    public function render()
    {
        $employee = Employee::findOrFail($this->employee_id);
        return view('livewire.hr.staffs.staffdetails', ['employee' => $employee]);
    }
}
