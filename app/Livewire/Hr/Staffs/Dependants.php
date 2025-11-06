<?php

namespace App\Livewire\Hr\Staffs;

use App\Models\Employee;
use App\Models\Employeedependants;
use Illuminate\Support\Facades\Auth;
use Livewire\Component;

class Dependants extends Component
{
    public $search = '';
    public $modalMode = 'create';
    public $showModal = false;
    public $dependant_id, $name, $relationship,$dob, $phone, $dependantsemail, $occupation, $address;
    public $first_name, $middle_name, $last_name, $gender, $getfullname, $age, $email, $editUrl;
    public $employee_id, $dependants=[];
    public bool $is_next_of_kin = false;

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
        $this->listdata();
    }

    public function listdata()
    {
        $this->dependants = Employeedependants::where('employee_id', $this->employee_id)->get();
    }

    public function openModal($mode = 'create', $id = null)
    {
        $this->resetErrorBag();
        $this->resetValidation();
        $this->modalMode = $mode;
        $this->showModal = true;
        if ($mode === 'edit' && $id) {
            $dependant = Employeedependants::findOrFail($id);
            $this->dependant_id = $id;
            $this->name = $dependant->name;
            $this->relationship = $dependant->relationship;
            $this->dob = $dependant->dob;
            $this->gender = $dependant->gender;
            $this->phone = $dependant->phone;
            $this->dependantsemail = $dependant->dependantsemail;
            $this->occupation = $dependant->occupation;
            $this->address = $dependant->address;
            $this->is_next_of_kin = $dependant->is_next_of_kin;
        } else {
            $this->reset(['name', 'relationship', 'age', 'gender', 'dob', 'phone', 'dependantsemail', 'occupation', 'address', 'is_next_of_kin']);
        }
    }
    
    public function save()
    {
        $this->validate([
            'name' => ['required', 'string', 'max:255'],
            'relationship' => 'required|max:255',
            'gender' => 'required|string|max:255',
            'dob' => 'required|date',
            'phone' => 'nullable|string',
            'dependantsemail' => 'nullable|string',
            'occupation' => 'nullable|string',
            'address' => 'nullable|string',
            'is_next_of_kin' => 'nullable|boolean',
        ]);

        if ($this->modalMode === 'edit' && $this->dependant_id) {
            $dependant = Employeedependants::findOrFail($this->dependant_id);
            $dependant->update(['name' => $this->name, 'relationship' => $this->relationship, 'dob' => $this->dob, 'gender' => $this->gender, 'phone' => $this->phone, 'email' => $this->dependantsemail, 'occupation' => $this->occupation, 'address' => $this->address, 'is_next_of_kin' => $this->is_next_of_kin]);
            $this->listdata();
            session()->flash('success', 'Dependant updated successfully!');
        } else {
            Employeedependants::create([
                'employee_id' => $this->employee_id,
                'name' => $this->name,
                'relationship' => $this->relationship,
                'dob' => $this->dob,
                'gender' => $this->gender,
                'phone' => $this->phone,
                'email' => $this->dependantsemail,
                'occupation' => $this->occupation,
                'address' => $this->address,
                'is_next_of_kin' => $this->is_next_of_kin

            ]);
            $this->listdata();
            session()->flash('success', 'Dependant added successfully!');
        }
        $this->showModal = false;
        $this->reset(['name', 'relationship', 'age', 'gender', 'dob', 'phone', 'dependantsemail', 'occupation', 'address', 'is_next_of_kin']);
    }
    public function delete($uuid)
    {
        $dependant = Employeedependants::findOrFail($uuid);
        $dependant->delete();
        $this->listdata();
        session()->flash('success', 'Dependant deleted successfully!');
    }
    public function render()
    {
        return view('livewire.hr.staffs.dependants');
    }
}
