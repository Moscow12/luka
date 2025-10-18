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
    public $countries =[], $regions =[], $districts =[], $wards =[], $villages =[], $workstations =[], $departments =[], $jobtitles =[], $designations =[], $users=[], $denominations=[];
    public $employee_id;
    public $first_name, $middle_name, $last_name, $gender, $dob, $marital_status, $photo, $phone, $tin_number, $national_id, $email, $employment_type, $hired_date, $status='Active', $education_level, $title_id, $fpid,$ward_id, $district_id, $region_id, $country_id, $employee_no, $workstation_id, $department_id,  $designation_id, $user_id, $vilstreet_id ;
   
   

    public $modalMode = 'create'; // or 'edit'
    public $showModal = false;
    public function mount()
    {
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
    public function save(){
        $this->validate([
            'first_name' => 'required',
            'middle_name' => 'required',
            'last_name' => 'required',
            'gender' => 'required',
            'dob' => 'required',
            'phone' => 'required',
            'gender' => 'required',
            'marital_status' => 'required',
            'email' => 'required',
            'employment_type' => 'required',
            'hired_date' => 'required',
            'status' => 'required',
            'education_level' => 'required',
            'email' => 'required',
        ]);

        if ($this->modalMode === 'edit' && $this->employee_id) {
            $staff = Employee::findOrFail($this->employee_id);
            $staff->update(['first_name' => $this->first_name, 'middle_name' => $this->middle_name, 'last_name' => $this->last_name, 'gender' => $this->gender, 'dob' => $this->dob,  'district_id' => $this->district_id, 'region_id' => $this->region_id, 'country_id' => $this->country_id,  'email' => $this->email, 'phone_number' => $this->phone_number, 'username' => $this->username, 'password' => $this->password, 'provider_type' => $this->provider_type, 'reg_number' => $this->reg_number, 'photo' => $this->photo]);
            $this->listdata();
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
                'title_id' => $this->title_id ?? '0199f305-7443-710b-b7ae-c475732a76a8',
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
        $this->reset(['first_name', 'middle_name','gender',]);
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
