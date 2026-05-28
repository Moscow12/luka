<?php

namespace App\Livewire\Hr\Staffs;

use App\Models\countries;
use App\Models\denominations;
use App\Models\departments;
use App\Models\designations;
use App\Models\districts;
use App\Models\Employee;
use App\Models\Jobtitle;
use App\Models\regions;
use App\Models\Role;
use App\Models\SmsApiSetting;
use App\Models\street;
use App\Models\User;
use App\Models\villages;
use App\Models\wards;
use App\Models\workstations;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Hash;
use Livewire\Component;
use Livewire\WithFileUploads;

class Addstaff extends Component
{
    use WithFileUploads;

    public $countries = [];

    public $regions = [];

    public $districts = [];

    public $wards = [];

    public $villages = [];

    public $workstations = [];

    public $departments = [];

    public $jobtitles = [];

    public $designations = [];

    public $users = [];

    public $denominations = [];

    public $roles = [];

    public $employee_id;

    public $title = '';

    // User account creation properties
    public $createUserAccount = false;

    public $username;

    public $user_password;

    public $user_password_confirmation;

    public $selected_role;

    // SMS notification toggle
    public $sendSmsNotification = false;

    public $hasSmsProvider = false;

    public $first_name;

    public $middle_name;

    public $last_name;

    public $gender;

    public $dob;

    public $marital_status;

    public $photo;

    public $phone;

    public $tin_number;

    public $national_id;

    public $email;

    public $employment_type;

    public $hired_date;

    public $status = 'Active';

    public $education_level;

    public $title_id;

    public $fpid;

    public $ward_id;

    public $district_id;

    public $region_id;

    public $country_id;

    public $employee_no;

    public $workstation_id;

    public $department_id;

    public $designation_id;

    public $user_id;

    public $vilstreet_id;

    public $denomination_id;

    public $modalMode = 'create'; // or 'edit'

    public $showModal = false;

    public $hasExistingUser = false;

    // Searchable picker state (country, region, district, ward, village, department, title, designation)
    public $countrySearch = '';
    public $showCountryDropdown = false;

    public $regionSearch = '';
    public $showRegionDropdown = false;

    public $districtSearch = '';
    public $showDistrictDropdown = false;

    public $wardSearch = '';
    public $showWardDropdown = false;

    public $villageSearch = '';
    public $showVillageDropdown = false;

    public $departmentSearch = '';
    public $showDepartmentDropdown = false;

    public $titleSearch = '';
    public $showTitleDropdown = false;

    public $designationSearch = '';
    public $showDesignationDropdown = false;

    protected function filterCollection($list, string $term, array $keys = ['name'])
    {
        $term = trim($term);

        return collect($list)->filter(function ($item) use ($term, $keys) {
            if ($term === '') {
                return true;
            }
            foreach ($keys as $key) {
                if (str_contains(strtolower((string) data_get($item, $key)), strtolower($term))) {
                    return true;
                }
            }
            return false;
        })->take(50)->values();
    }

    protected function findIn($list, $id)
    {
        if (! $id) {
            return null;
        }
        return collect($list)->firstWhere('id', $id);
    }

    // Country
    public function getFilteredCountriesProperty()    { return $this->filterCollection($this->countries, $this->countrySearch); }
    public function getSelectedCountryProperty()      { return $this->findIn($this->countries, $this->country_id); }
    public function selectCountry($id)                { $this->country_id = $id; $this->countrySearch = ''; $this->showCountryDropdown = false; }
    public function clearCountry()                    { $this->country_id = null; $this->countrySearch = ''; $this->showCountryDropdown = true; }

    // Region
    public function getFilteredRegionsProperty()      { return $this->filterCollection($this->regions, $this->regionSearch); }
    public function getSelectedRegionProperty()       { return $this->findIn($this->regions, $this->region_id); }
    public function selectRegion($id)                 { $this->region_id = $id; $this->regionSearch = ''; $this->showRegionDropdown = false; $this->updateDistricts(); }
    public function clearRegion()                     { $this->region_id = null; $this->regionSearch = ''; $this->showRegionDropdown = true; $this->districts = collect(); $this->district_id = null; $this->wards = collect(); $this->ward_id = null; $this->villages = collect(); $this->vilstreet_id = null; }

    // District
    public function getFilteredDistrictsProperty()    { return $this->filterCollection($this->districts, $this->districtSearch); }
    public function getSelectedDistrictProperty()     { return $this->findIn($this->districts, $this->district_id); }
    public function selectDistrict($id)               { $this->district_id = $id; $this->districtSearch = ''; $this->showDistrictDropdown = false; $this->updatewards(); }
    public function clearDistrict()                   { $this->district_id = null; $this->districtSearch = ''; $this->showDistrictDropdown = true; $this->wards = collect(); $this->ward_id = null; $this->villages = collect(); $this->vilstreet_id = null; }

    // Ward
    public function getFilteredWardsProperty()        { return $this->filterCollection($this->wards, $this->wardSearch); }
    public function getSelectedWardProperty()         { return $this->findIn($this->wards, $this->ward_id); }
    public function selectWard($id)                   { $this->ward_id = $id; $this->wardSearch = ''; $this->showWardDropdown = false; $this->updatestreet(); }
    public function clearWard()                       { $this->ward_id = null; $this->wardSearch = ''; $this->showWardDropdown = true; $this->villages = collect(); $this->vilstreet_id = null; }

    // Village
    public function getFilteredVillagesProperty()     { return $this->filterCollection($this->villages, $this->villageSearch); }
    public function getSelectedVillageProperty()      { return $this->findIn($this->villages, $this->vilstreet_id); }
    public function selectVillage($id)                { $this->vilstreet_id = $id; $this->villageSearch = ''; $this->showVillageDropdown = false; }
    public function clearVillage()                    { $this->vilstreet_id = null; $this->villageSearch = ''; $this->showVillageDropdown = true; }

    // Department
    public function getFilteredDepartmentsProperty()  { return $this->filterCollection($this->departments, $this->departmentSearch); }
    public function getSelectedDepartmentProperty()   { return $this->findIn($this->departments, $this->department_id); }
    public function selectDepartment($id)             { $this->department_id = $id; $this->departmentSearch = ''; $this->showDepartmentDropdown = false; }
    public function clearDepartment()                 { $this->department_id = null; $this->departmentSearch = ''; $this->showDepartmentDropdown = true; }

    // Job Title
    public function getFilteredTitlesProperty()       { return $this->filterCollection($this->jobtitles, $this->titleSearch); }
    public function getSelectedTitleProperty()        { return $this->findIn($this->jobtitles, $this->title_id); }
    public function selectTitle($id)                  { $this->title_id = $id; $this->titleSearch = ''; $this->showTitleDropdown = false; }
    public function clearTitle()                      { $this->title_id = null; $this->titleSearch = ''; $this->showTitleDropdown = true; }

    // Designation
    public function getFilteredDesignationsProperty() { return $this->filterCollection($this->designations, $this->designationSearch); }
    public function getSelectedDesignationProperty()  { return $this->findIn($this->designations, $this->designation_id); }
    public function selectDesignation($id)            { $this->designation_id = $id; $this->designationSearch = ''; $this->showDesignationDropdown = false; }
    public function clearDesignation()                { $this->designation_id = null; $this->designationSearch = ''; $this->showDesignationDropdown = true; }

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

            // Check if staff has existing user account
            $this->hasExistingUser = ! empty($employee->user_id);
        }
        $this->listdata();

        // Check if SMS provider is configured
        $this->hasSmsProvider = SmsApiSetting::where('is_active', true)->exists();
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
        $this->roles = Role::all();
    }

    public function getDenominations()
    {
        $this->denominations = denominations::where('id', $this->denomination_id)->get();
    }

    public function save()
    {
        $rules = [
            'first_name' => ['required', 'string', 'max:100'],
            'middle_name' => ['nullable', 'string', 'max:100'],
            'last_name' => ['required', 'string', 'max:100'],
            'gender' => ['required', 'in:Male,Female,Other'],
            'dob' => ['required', 'date', 'before:today'],
            'phone' => ['required', 'string', 'max:20'],
            'marital_status' => ['required', 'string'],
            'email' => ['required', 'email', 'max:255', 'unique:employees,email,'.$this->employee_id],
            'employment_type' => ['required', 'in:Full-time,Part-time,Contract,Temporary'],
            'hired_date' => ['required', 'date'],
            'status' => ['required', 'in:active,inactive,suspended,terminated,retired,contract_ended,resigned,deceased,transferred,study_leave,absconded,other'],
            'education_level' => ['required', 'string'],
            'national_id' => ['nullable', 'string', 'max:50', 'unique:employees,national_id,'.$this->employee_id],
            'user_id' => ['nullable', 'unique:employees,user_id,'.$this->employee_id],
            'employee_no' => ['nullable', 'string', 'max:50', 'unique:employees,employee_no,'.$this->employee_id],
            'tin_number' => ['nullable', 'string', 'max:50'],
            'fpid' => ['nullable', 'string', 'max:50'],
        ];

        $messages = [
            'first_name.required' => 'First name is required.',
            'first_name.max' => 'First name cannot exceed 100 characters.',
            'last_name.required' => 'Last name is required.',
            'last_name.max' => 'Last name cannot exceed 100 characters.',
            'gender.required' => 'Please select a gender.',
            'gender.in' => 'Please select a valid gender (Male or Female).',
            'dob.required' => 'Date of birth is required.',
            'dob.date' => 'Please enter a valid date of birth.',
            'dob.before' => 'Date of birth must be in the past.',
            'phone.required' => 'Phone number is required.',
            'phone.max' => 'Phone number cannot exceed 20 characters.',
            'email.required' => 'Email address is required.',
            'email.email' => 'Please enter a valid email address.',
            'email.unique' => 'This email is already registered to another employee.',
            'employment_type.required' => 'Please select an employment type.',
            'employment_type.in' => 'Please select a valid employment type.',
            'hired_date.required' => 'Hire date is required.',
            'hired_date.date' => 'Please enter a valid hire date.',
            'status.required' => 'Please select an employment status.',
            'national_id.max' => 'National ID cannot exceed 50 characters.',
            'national_id.unique' => 'This National ID is already registered to another employee.',
            'employee_no.max' => 'Employee number cannot exceed 50 characters.',
            'employee_no.unique' => 'This employee number is already in use.',
            'tin_number.max' => 'TIN number cannot exceed 50 characters.',
            'fpid.max' => 'Fingerprint ID cannot exceed 50 characters.',
        ];

        // Add validation rules for user creation if enabled
        if ($this->createUserAccount) {
            $rules['username'] = ['required', 'string', 'min:3', 'max:50', 'unique:users,username'];
            $rules['user_password'] = ['required', 'string', 'min:8', 'confirmed'];
            $rules['selected_role'] = ['required', 'exists:roles,id'];

            // Validate email is unique in users table (it must be unique in both tables)
            $rules['email'] = ['required', 'email', 'max:255', 'unique:employees,email,'.$this->employee_id, 'unique:users,email'];

            // Validate phone is unique in users table
            $rules['phone'] = ['required', 'string', 'max:20', 'unique:users,phone_number'];

            $messages['username.required'] = 'Username is required for user account.';
            $messages['username.min'] = 'Username must be at least 3 characters.';
            $messages['username.max'] = 'Username cannot exceed 50 characters.';
            $messages['username.unique'] = 'This username is already taken.';
            $messages['user_password.required'] = 'Password is required for user account.';
            $messages['user_password.min'] = 'Password must be at least 8 characters.';
            $messages['user_password.confirmed'] = 'Password confirmation does not match.';
            $messages['selected_role.required'] = 'Please select a role for the user.';
            $messages['email.unique'] = 'This email is already registered to another user or employee.';
            $messages['phone.unique'] = 'This phone number is already registered to another user.';
        }

        $this->validate($rules, $messages);

        // add photo upload
        if ($this->photo && is_object($this->photo)) {
            $photoPath = $this->photo->store('photos', 'public');
            $this->photo = $photoPath;
        }

        $createdUser = null;
        $selectedRoleName = null;

        // Get role name before transaction if creating user
        if ($this->createUserAccount) {
            $role = Role::find($this->selected_role);
            $selectedRoleName = $role?->name;
        }

        DB::beginTransaction();
        try {
            // Create user account if requested
            if ($this->createUserAccount) {
                $createdUser = User::create([
                    'first_name' => $this->first_name,
                    'middle_name' => $this->middle_name,
                    'surname' => $this->last_name,
                    'gender' => $this->gender,
                    'dob' => $this->dob,
                    'email' => $this->email,
                    'phone_number' => $this->phone,
                    'username' => $this->username,
                    'password' => Hash::make($this->user_password),
                    'profile_picture' => $this->photo,
                ]);

                $this->user_id = $createdUser->id;
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

                DB::commit();

                // Assign role after transaction commits to avoid FK constraint issues
                if ($createdUser && $selectedRoleName) {
                    $createdUser->assignRole($selectedRoleName);
                }

                // Send SMS notification if enabled
                if ($this->createUserAccount && $this->sendSmsNotification && $this->phone) {
                    $this->sendCredentialsSms($this->phone, $this->username, $this->user_password, $this->first_name);
                }

                session()->flash('success', $this->createUserAccount ? 'Staff updated and user account created successfully!' : 'Staff updated successfully!');
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
                    'added_by' => Auth::user()->id,
                ]);

                DB::commit();

                // Assign role after transaction commits to avoid FK constraint issues
                if ($createdUser && $selectedRoleName) {
                    $createdUser->assignRole($selectedRoleName);
                }

                // Send SMS notification if enabled
                if ($this->createUserAccount && $this->sendSmsNotification && $this->phone) {
                    $this->sendCredentialsSms($this->phone, $this->username, $this->user_password, $this->first_name);
                }

                $this->listdata();
                session()->flash('success', $this->createUserAccount ? 'Staff added with user account successfully!' : 'Staff added successfully!');
            }
        } catch (\Illuminate\Database\QueryException $e) {
            DB::rollBack();

            // Handle specific database errors with user-friendly messages
            $errorCode = $e->errorInfo[1] ?? null;
            $errorMessage = match ($errorCode) {
                1406 => 'One or more fields contain data that is too long. Please check your input and try again.',
                1062 => 'A record with this information already exists. Please check for duplicates.',
                1364 => 'A required field is missing. Please fill in all required fields.',
                1452 => 'Invalid reference selected. Please ensure all selections are valid.',
                default => 'A database error occurred. Please try again or contact support.',
            };

            session()->flash('error', $errorMessage);

            return;
        } catch (\Exception $e) {
            DB::rollBack();
            session()->flash('error', 'An unexpected error occurred. Please try again or contact support.');

            return;
        }

        $this->showModal = false;

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

    /**
     * Send user credentials via SMS
     */
    private function sendCredentialsSms($phoneNumber, $username, $password, $firstName)
    {
        try {
            $message = "Dear {$firstName},\n";
            $message .= "Login Details:\n";
            $message .= "Username: {$username}\n";
            $message .= "Password: {$password}\n";
            $message .= '-HRP System Link https://hrp.stjosephhospitalmoshi.or.tz/auth/login';

            $result = send_sms($phoneNumber, $message);

            if ($result['success']) {
                session()->flash('success', session('success').' SMS notification sent successfully!');
            }
        } catch (\Exception $e) {
            // Don't fail the entire process if SMS fails, just log it
            \Illuminate\Support\Facades\Log::error('Failed to send credentials SMS: '.$e->getMessage());
        }
    }

    public function render()
    {
        return view('livewire.hr.staffs.addstaff');
    }
}
