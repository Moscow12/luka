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

    public $workstations = [];

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

    public $status = 'active';

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

    protected function dbSearch($model, string $term, ?string $scope = null, ?string $scopeValue = null, string $column = 'name')
    {
        $query = $model::query()->select('id', $column.' as name');
        if ($scope && $scopeValue) {
            $query->where($scope, $scopeValue);
        }
        if (trim($term) !== '') {
            $query->where($column, 'like', '%'.trim($term).'%');
        }

        return $query->orderBy($column)->limit(50)->get();
    }

    // Country
    public function getFilteredCountriesProperty()
    {
        return $this->dbSearch(countries::class, $this->countrySearch);
    }

    public function getSelectedCountryProperty()
    {
        return $this->country_id ? countries::select('id', 'name')->find($this->country_id) : null;
    }

    public function selectCountry($id)
    {
        $this->country_id = $id;
        $this->countrySearch = '';
        $this->showCountryDropdown = false;
    }

    public function clearCountry()
    {
        $this->country_id = null;
        $this->countrySearch = '';
        $this->showCountryDropdown = true;
    }

    // Region
    public function getFilteredRegionsProperty()
    {
        return $this->dbSearch(regions::class, $this->regionSearch);
    }

    public function getSelectedRegionProperty()
    {
        return $this->region_id ? regions::select('id', 'name')->find($this->region_id) : null;
    }

    public function selectRegion($id)
    {
        $this->region_id = $id;
        $this->regionSearch = '';
        $this->showRegionDropdown = false;
        $this->district_id = null;
        $this->ward_id = null;
        $this->vilstreet_id = null;
    }

    public function clearRegion()
    {
        $this->region_id = null;
        $this->regionSearch = '';
        $this->showRegionDropdown = true;
        $this->district_id = null;
        $this->districtSearch = '';
        $this->ward_id = null;
        $this->wardSearch = '';
        $this->vilstreet_id = null;
        $this->villageSearch = '';
    }

    // District — scoped to selected region
    public function getFilteredDistrictsProperty()
    {
        return $this->dbSearch(districts::class, $this->districtSearch, 'region_id', $this->region_id);
    }

    public function getSelectedDistrictProperty()
    {
        return $this->district_id ? districts::select('id', 'name')->find($this->district_id) : null;
    }

    public function selectDistrict($id)
    {
        $this->district_id = $id;
        $this->districtSearch = '';
        $this->showDistrictDropdown = false;
        $this->ward_id = null;
        $this->vilstreet_id = null;
    }

    public function clearDistrict()
    {
        $this->district_id = null;
        $this->districtSearch = '';
        $this->showDistrictDropdown = true;
        $this->ward_id = null;
        $this->wardSearch = '';
        $this->vilstreet_id = null;
        $this->villageSearch = '';
    }

    // Ward — scoped to selected district
    public function getFilteredWardsProperty()
    {
        return $this->dbSearch(wards::class, $this->wardSearch, 'district_id', $this->district_id);
    }

    public function getSelectedWardProperty()
    {
        return $this->ward_id ? wards::select('id', 'name')->find($this->ward_id) : null;
    }

    public function selectWard($id)
    {
        $this->ward_id = $id;
        $this->wardSearch = '';
        $this->showWardDropdown = false;
        $this->vilstreet_id = null;
    }

    public function clearWard()
    {
        $this->ward_id = null;
        $this->wardSearch = '';
        $this->showWardDropdown = true;
        $this->vilstreet_id = null;
        $this->villageSearch = '';
    }

    // Village/Street — scoped to selected ward
    public function getFilteredVillagesProperty()
    {
        return $this->dbSearch(street::class, $this->villageSearch, 'ward_id', $this->ward_id);
    }

    public function getSelectedVillageProperty()
    {
        return $this->vilstreet_id ? street::select('id', 'name')->find($this->vilstreet_id) : null;
    }

    public function selectVillage($id)
    {
        $this->vilstreet_id = $id;
        $this->villageSearch = '';
        $this->showVillageDropdown = false;
    }

    public function clearVillage()
    {
        $this->vilstreet_id = null;
        $this->villageSearch = '';
        $this->showVillageDropdown = true;
    }

    // Department
    public function getFilteredDepartmentsProperty()
    {
        return $this->dbSearch(departments::class, $this->departmentSearch);
    }

    public function getSelectedDepartmentProperty()
    {
        return $this->department_id ? departments::select('id', 'name')->find($this->department_id) : null;
    }

    public function selectDepartment($id)
    {
        $this->department_id = $id;
        $this->departmentSearch = '';
        $this->showDepartmentDropdown = false;
    }

    public function clearDepartment()
    {
        $this->department_id = null;
        $this->departmentSearch = '';
        $this->showDepartmentDropdown = true;
    }

    // Job Title
    public function getFilteredTitlesProperty()
    {
        return $this->dbSearch(Jobtitle::class, $this->titleSearch);
    }

    public function getSelectedTitleProperty()
    {
        return $this->title_id ? Jobtitle::select('id', 'name')->find($this->title_id) : null;
    }

    public function selectTitle($id)
    {
        $this->title_id = $id;
        $this->titleSearch = '';
        $this->showTitleDropdown = false;
    }

    public function clearTitle()
    {
        $this->title_id = null;
        $this->titleSearch = '';
        $this->showTitleDropdown = true;
    }

    // Designation
    public function getFilteredDesignationsProperty()
    {
        return $this->dbSearch(designations::class, $this->designationSearch);
    }

    public function getSelectedDesignationProperty()
    {
        return $this->designation_id ? designations::select('id', 'name')->find($this->designation_id) : null;
    }

    public function selectDesignation($id)
    {
        $this->designation_id = $id;
        $this->designationSearch = '';
        $this->showDesignationDropdown = false;
    }

    public function clearDesignation()
    {
        $this->designation_id = null;
        $this->designationSearch = '';
        $this->showDesignationDropdown = true;
    }

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
        $this->workstations = workstations::orderBy('workstation_name')->get();
        $this->denominations = denominations::orderBy('name')->get();
        $this->users = User::orderBy('first_name')->get();
        $this->roles = Role::orderBy('name')->get();
    }

    public function getDenominations()
    {
        $this->denominations = denominations::where('id', $this->denomination_id)->get();
    }

    /**
     * Map a database column name to a human-readable field label for error messages.
     */
    private function humanFieldName(string $columnName): string
    {
        $fieldMap = [
            'department_id' => 'Department',
            'designation_id' => 'Designation',
            'workstation_id' => 'Workstation',
            'title_id' => 'Job Title',
            'ward_id' => 'Ward',
            'district_id' => 'District',
            'region_id' => 'Region',
            'country_id' => 'Country',
            'vilstreet_id' => 'Village/Street',
            'denomination_id' => 'Denomination',
            'user_id' => 'Linked User',
            'added_by' => 'Current user (session)',
        ];

        return $fieldMap[$columnName] ?? ucwords(str_replace('_id', '', str_replace('_', ' ', $columnName)));
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
            'department_id' => ['required', 'exists:departments,id'],
            'title_id' => ['required', 'exists:jobtitles,id'],
            'designation_id' => ['required', 'exists:designations,id'],
            'workstation_id' => ['required', 'exists:workstations,id'],
            'denomination_id' => ['required', 'exists:denominations,id'],
            'country_id' => ['required', 'exists:countries,id'],
            'region_id' => ['required', 'exists:regions,id'],
            'district_id' => ['required', 'exists:districts,id'],
            'ward_id' => ['required', 'exists:wards,id'],
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
            'department_id.required' => 'Please select a department.',
            'department_id.exists' => 'The selected department is invalid. Please re-select it.',
            'title_id.required' => 'Please select a job title.',
            'title_id.exists' => 'The selected job title is invalid. Please re-select it.',
            'designation_id.required' => 'Please select a designation.',
            'designation_id.exists' => 'The selected designation is invalid. Please re-select it.',
            'workstation_id.required' => 'Please select a workstation.',
            'workstation_id.exists' => 'The selected workstation is invalid. Please re-select it.',
            'denomination_id.required' => 'Please select a denomination.',
            'denomination_id.exists' => 'The selected denomination is invalid. Please re-select it.',
            'country_id.required' => 'Please select a country.',
            'country_id.exists' => 'The selected country is invalid. Please re-select it.',
            'region_id.required' => 'Please select a region.',
            'region_id.exists' => 'The selected region is invalid. Please re-select it.',
            'district_id.required' => 'Please select a district.',
            'district_id.exists' => 'The selected district is invalid. Please re-select it.',
            'ward_id.required' => 'Please select a ward.',
            'ward_id.exists' => 'The selected ward is invalid. Please re-select it.',
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
                try {
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
                } catch (\Illuminate\Database\QueryException $e) {
                    DB::rollBack();
                    $errorCode = $e->errorInfo[1] ?? null;
                    $userErrorMessage = match ($errorCode) {
                        1062 => 'Failed to create user account: a user with this email, username, or phone number already exists.',
                        1406 => 'Failed to create user account: one or more fields contain data that is too long.',
                        default => 'Failed to create user account. Please verify the username, email, and phone number are not already in use.',
                    };
                    session()->flash('error', $userErrorMessage);

                    return;
                } catch (\Exception $e) {
                    DB::rollBack();
                    session()->flash('error', 'Failed to create user account: '.$e->getMessage());

                    return;
                }

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

            $errorCode = $e->errorInfo[1] ?? null;

            if ($errorCode === 1452) {
                $msg = $e->getMessage();
                // MySQL 1452 message contains: FOREIGN KEY (`column_name`) REFERENCES `table`
                // Match the column name from backticks after "FOREIGN KEY"
                $detected = null;
                if (preg_match('/FOREIGN KEY \(`([^`]+)`\)/', $msg, $matches)) {
                    $detected = $this->humanFieldName($matches[1]);
                }
                $errorMessage = $detected
                    ? "Save failed: the selected \"{$detected}\" is invalid or does not exist. Please re-select it and try again."
                    : 'Save failed: an invalid reference was selected. Please review all dropdown selections and try again.';
            } elseif ($errorCode === 1048) {
                $msg = $e->getMessage();
                // MySQL 1048 message contains: Column 'column_name' cannot be null
                $detected = null;
                if (preg_match("/Column '([^']+)' cannot be null/", $msg, $matches)) {
                    $detected = $this->humanFieldName($matches[1]);
                }
                $errorMessage = $detected
                    ? "Save failed: \"{$detected}\" is required but was left empty. Please fill it in and try again."
                    : 'Save failed: a required field was left empty. Please review the form and fill in all required fields.';
            } else {
                $errorMessage = match ($errorCode) {
                    1406 => 'One or more fields contain data that is too long. Please check your input and try again.',
                    1062 => 'A record with this information already exists. Please check for duplicates.',
                    1364 => 'A required field is missing. Please fill in all required fields.',
                    default => 'A database error occurred. Please try again or contact support.',
                };
            }

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
