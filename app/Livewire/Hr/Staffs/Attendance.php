<?php

namespace App\Livewire\Hr\Staffs;

use App\Models\Employee;
use App\Models\employeeattendances;
use Illuminate\Support\Facades\Auth;
use Livewire\Component;

class Attendance extends Component
{
    public $search = '';
    public $modalMode = 'create';
    public $showModal = false;
    public $attendance_id, $date, $attendance, $employee_id, $attendances=[];
    public $first_name, $middle_name, $last_name, $gender, $getfullname, $age, $email, $editUrl, $photo;
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
        $this->listdata();
    }

    public function openModal($mode = 'create', $id = null)
    {
        $this->resetErrorBag();
        $this->resetValidation();
        $this->modalMode = $mode;
        $this->showModal = true;
        if ($mode === 'edit' && $id) {
            $attendance = employeeattendances::findOrFail($id);
            $this->attendance_id = $id;
            $this->date = $attendance->date;
            $this->attendance = $attendance->attendance;
        } else {
            $this->reset(['date', 'attendance']);
        }
    }
    
    public function save()
    {
        $this->validate([
            'date' => ['required', 'date'],
            'attendance' => ['required', 'string', 'max:255'],

        ]);

        if ($this->modalMode === 'edit' && $this->attendance_id) {
            $attendance = employeeattendances::findOrFail($this->attendance_id);
            $attendance->update(['date' => $this->date, 'attendance' => $this->attendance]);
            $this->listdata();
            session()->flash('success', 'Attendance updated successfully!');
        } else {
            employeeattendances::create([
                'date' => $this->date,
                'attendance' => $this->attendance,
                'added_by' => Auth::user()->id,
                'employee_id' => $this->employee_id,

            ]);
            $this->listdata();
            session()->flash('success', 'Attendance added successfully!');
        }
        $this->showModal = false;
        $this->reset(['date', 'attendance']);
    }
    public function delete($uuid)
    {
        $attendance = employeeattendances::findOrFail($uuid);
        $attendance->delete();
        $this->listdata();
        session()->flash('success', 'Attendance deleted successfully!');
    }

    public function listdata()
    {
        $this->attendances = employeeattendances::query()
            ->where('employee_id', $this->employee_id)
            ->get();
    }
    public function render()
    {
        return view('livewire.hr.staffs.attendance');
    }
}
