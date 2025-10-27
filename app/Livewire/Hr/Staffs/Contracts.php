<?php

namespace App\Livewire\Hr\Staffs;

use App\Models\{workstations, departments, Employee, Employeecontracts, Jobtitle};
use Illuminate\Support\Facades\Auth;
use Livewire\Component;
use Livewire\WithFileUploads;

class Contracts extends Component
{
    use WithFileUploads;

    public $employee_id, $workstation_id, $department_id, $position_id, $workstations, $departments, $positions, $editmode = false;
    public $first_name, $middle_name, $last_name, $gender, $getfullname, $age, $email, $editUrl;
    public $contract_type, $start_date, $expire_date, $expirenotification, $description, $attachment, $contract_id;
    public function mount($id = null)
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
        $this->workstations = workstations::pluck('workstation_name as name', 'id');
        $this->departments = departments::pluck('name', 'id');
        $this->positions = Jobtitle::all();
    }

    public function storecontacts()
    {

        $this->validate([
            'workstation_id' => 'required',
            'department_id' => 'required',
            'position_id' => 'required',
            'contract_type' => 'required',
            'start_date' => 'required',
            'expire_date' => 'required',
            'expirenotification' => 'required',
            'description' => 'required',
            'attachment' =>'nullable|file|mimes:pdf,jpg,jpeg,png|max:2048', // max 2MB
        ]);
        // store attachment in storage
        $paths = null;
        if ($this->attachment) {
            $paths = $this->attachment->store('contracts', 'public');
        }

        // save contract / edit contract if editmode is on / create new contract if editmode is off
        if ($this->editmode) {
            $contract = Employeecontracts::findOrFail($this->contract_id);
            $contract->update([
                'workstation_id' => $this->workstation_id,
                'department_id' => $this->department_id,
                'position_id' => $this->position_id,
                'contract_type' => $this->contract_type,
                'start_date' => $this->start_date,
                'expire_date' => $this->expire_date,
                'expirenotification' => $this->expirenotification,
                'description' => $this->description,
                'attachment' => $paths ?? $contract->attachment,
            ]);
            session()->flash('success', 'Contract updated successfully!');
        } else {

            Employeecontracts::create([
                'employee_id' => $this->employee_id,
                'workstation_id' => $this->workstation_id,
                'department_id' => $this->department_id,
                'position_id' => $this->position_id,
                'contract_type' => $this->contract_type,
                'start_date' => $this->start_date,
                'expire_date' => $this->expire_date,
                'expirenotification' => $this->expirenotification,
                'description' => $this->description,
                'attachment' => $paths,
                'added_by' => Auth::user()->id
            ]);
            session()->flash('success', 'Contract added successfully!');
        }

        $this->editmode = false;
    }
    public function render()
    {
        return view('livewire.hr.staffs.contracts');
    }
}
