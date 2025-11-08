<?php

namespace App\Livewire\Hr\Staffs;

use App\Livewire\Setup\Jobtitles;
use App\Models\countries;
use App\Models\denominations;
use App\Models\departments;
use App\Models\designations;
use App\Models\districts;
use App\Models\Employee;
use App\Models\Jobtitle;
use App\Models\regions;
use App\Models\street;
use App\Models\User;
use App\Models\villages;
use App\Models\wards;
use App\Models\workstations;
use Illuminate\Support\Facades\Auth;
use Livewire\Component;
use Livewire\WithFileUploads;

class Addstaff extends Component
{
    use WithFileUploads;
    public $countries = [], $regions = [], $districts = [], $wards = [], $villages = [], $workstations = [], $departments = [], $jobtitles = [], $designations = [], $users = [], $denominations = [];
    public $employee_id, $title='';
    public $first_name, $middle_name, $last_name, $gender, $dob, $marital_status, $photo, $phone, $tin_number, $national_id, $email, $employment_type, $hired_date, $status = 'Active', $education_level, $title_id, $fpid, $ward_id, $district_id, $region_id, $country_id, $employee_no, $workstation_id, $department_id,  $designation_id, $user_id, $vilstreet_id, $denomination_id;



    public $modalMode = 'create'; // or 'edit'
    public $showModal = false;
    public function mount($id = null)
    {
        if ($id) {
            $this->employee_id = $id;
            $this->modalMode = 'edit';
            $employee = \App\Models\Employee::findOrFail($id);

            $this->first_name = $employee->first_name;
            $this->middle_name = $employee->middle_name;
            $this->last_name = $employee->last_name;
            $this->gender = $employee->gender;
            $this->dob = $employee->dob;
            $this->tin_number = $employee->tin_number;
            $this->department_id = $employee->department_id;
            $this->denomination_id = $employee->denomination_id;
            $this->workstation_id = $employee->workstation_id;
            $this->employee_no = $employee->employee_no;
            $this->email = $employee->email;
            $this->phone = $employee->phone;
            $this->designation_id = $employee->designation_id;
            $this->photo = $employee->photo;
            $this->employment_type = $employee->employment_type;
            $this->education_level = $employee->education_level;
            $this->status = $employee->status;
            $this->hired_date = $employee->hired_date;
            $this->marital_status = $employee->marital_status;
            $this->national_id = $employee->national_id;
            $this->user_id = $employee->user_id;
            $this->title_id = $employee->title_id;
            $this->fpid = $employee->fpid;
            $this->ward_id = $employee->ward_id;
            $this->district_id = $employee->district_id;
            $this->region_id = $employee->region_id;
            $this->country_id = $employee->country_id;
            $this->vilstreet_id = $employee->vilstreet_id;
        }
        $this->listdata();
    }

    public function listdata()
    {
        $this->countries = countries::all();
        $this->regions = regions::all();
        $this->districts = districts::all();
        $this->wards = wards::all();
        $this->villages = villages::all();
        $this->workstations = workstations::all();
        $this->departments = departments::all();
        $this->jobtitles = Jobtitle::all();
        $this->designations = designations::all();
        $this->denominations = denominations::all();
        $this->users = User::all();
    }
    public function getDenominations()
    {
        $this->denominations = denominations::where('id', $this->denomination_id)->get();
    }

    public function save()
    {
        $this->validate([
            'first_name' => 'required',
            'middle_name' => 'required',
            'last_name' => 'required',
            'gender' => 'required',
            'dob' => 'required|date',
            'phone' => 'required',
            'marital_status' => 'required',
            'email' => ['required', 'email', 'unique:employees,email,' . $this->employee_id],
            'employment_type' => 'required',
            'hired_date' => 'required|date',
            'status' => 'required',
            'education_level' => 'required',
            'national_id' => ['nullable', 'unique:employees,national_id,' . $this->employee_id],
            'user_id' => ['nullable', 'unique:employees,user_id,' . $this->employee_id],
            'employee_no' => ['nullable', 'unique:employees,employee_no,' . $this->employee_id],
        ]);
        // add photo upload
        if ($this->photo) {
            $photoPath = $this->photo->store('photos', 'public');
            $this->photo = $photoPath;
        }
        if ($this->modalMode === 'edit' && $this->employee_id) {
            $staff = \App\Models\Employee::findOrFail($this->employee_id);
            $staff->update([
                'first_name' => $this->first_name,
                'middle_name' => $this->middle_name,
                'last_name' => $this->last_name,
                'gender' => $this->gender,
                'dob' => $this->dob,
                'tin_number' => $this->tin_number,
                'department_id' => $this->department_id,
                'denomination_id' => $this->denomination_id,
                'workstation_id' => $this->workstation_id,
                'employee_no' => $this->employee_no,
                'email' => $this->email,
                'phone' => $this->phone,
                'designation_id' => $this->designation_id,
                'photo' => $this->photo,
                'employment_type' => $this->employment_type,
                'education_level' => $this->education_level,
                'status' => $this->status,
                'hired_date' => $this->hired_date,
                'marital_status' => $this->marital_status,
                'national_id' => $this->national_id,
                'user_id' => $this->user_id,
                'title_id' => $this->title_id,
                'fpid' => $this->fpid,
                'ward_id' => $this->ward_id,
                'district_id' => $this->district_id,
                'region_id' => $this->region_id,
                'country_id' => $this->country_id,
                'vilstreet_id' => $this->vilstreet_id,
            ]);

            session()->flash('success', 'Staff updated successfully!');
        } else {
            Employee::create([

                'first_name' => $this->first_name,
                'middle_name' => $this->middle_name,
                'last_name' => $this->last_name,
                'gender' => $this->gender,
                'dob' => $this->dob,
                'tin_number' => $this->tin_number,
                'department_id' => $this->department_id,
                'denomination_id' => $this->denomination_id,
                'workstation_id' => $this->workstation_id,
                'employee_no' => $this->employee_no,
                'email' => $this->email,
                'phone' => $this->phone,
                'designation_id' => $this->designation_id,
                'photo' => $this->photo,
                'employment_type' => $this->employment_type,
                'education_level' => $this->education_level,
                'status' => $this->status,
                'hired_date' => $this->hired_date,
                'marital_status' => $this->marital_status,
                'national_id' => $this->national_id,
                'user_id' => $this->user_id,
                'title_id' => $this->title_id,
                'fpid' => $this->fpid,
                'ward_id' => $this->ward_id,
                'district_id' => $this->district_id,
                'region_id' => $this->region_id,
                'country_id' => $this->country_id,
                'vilstreet_id' => $this->vilstreet_id,
                'added_by' => Auth::user()->id
            ]);
            $this->listdata();
            session()->flash('success', 'Staff added successfully!');
        }

        $this->showModal = false;
        // return to staff list redirect
        return redirect()->route('hr.stafflist');

    }

    public function updateDistricts()
    {
        if ($this->region_id) {
            $this->districts = districts::where('region_id', $this->region_id)->get();
        } else {
            $this->districts = collect(); // Clear districts if no region is selected
        }
    }

    public function updatewards()
    {
        if ($this->district_id) {
            $this->wards = wards::where('district_id', $this->district_id)->get();
        } else {
            $this->wards = collect(); // Clear wards if no district is selected
        }
    }

    // When a ward is selected
    public function updatestreet()
    {
        if ($this->ward_id) {
            $this->villages = street::where('ward_id', $this->ward_id)->orderBy('name')->get();
        } else {
            $this->villages = collect(); // Clear streets if no district is selected
        }
    }

    public function render()
    {
        return view('livewire.hr.staffs.addstaff');
    }
}
