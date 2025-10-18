<?php

namespace App\Livewire\Hr\Staffs;

use App\Models\Employee;
use Livewire\Component;

class Stafflist extends Component
{
    public function render()
    {
        $employees = Employee::all();
        return view('livewire.hr.staffs.stafflist', ['employees' => $employees]);
    }
}
