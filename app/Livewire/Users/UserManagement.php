<?php

namespace App\Livewire\Users;

use App\Models\Role;
use App\Models\User;
use Illuminate\Support\Facades\Hash;
use Illuminate\Validation\Rule;
use Livewire\Attributes\Url;
use Livewire\Component;
use Livewire\WithPagination;

class UserManagement extends Component
{
    use WithPagination;

    // Search and Filters
    #[Url(as: 'q')]
    public $search = '';

    #[Url]
    public $role = '';

    #[Url]
    public $gender = '';

    #[Url]
    public $status = 'active'; // active, inactive, all

    public $perPage = 10;

    public $showFilters = false;

    // Modal State
    public $showModal = false;

    public $editMode = false;

    // Form Fields
    public $userId;

    public $salutation = '';

    public $first_name = '';

    public $middle_name = '';

    public $surname = '';

    public $gender_input = '';

    public $dob = '';

    public $email = '';

    public $phone_number = '';

    public $username = '';

    public $password = '';

    public $password_confirmation = '';

    public $address = '';

    public $district = '';

    public $region = '';

    public $country = '';

    public $postal_code = '';

    public $reg_number = '';

    public $qualification = '';

    public $selectedRole = '';

    // Reset pagination when searching/filtering
    public function updatingSearch()
    {
        $this->resetPage();
    }

    public function updatingRole()
    {
        $this->resetPage();
    }

    public function updatingGender()
    {
        $this->resetPage();
    }

    public function updatingStatus()
    {
        $this->resetPage();
    }

    // Toggle filters panel
    public function toggleFilters()
    {
        $this->showFilters = ! $this->showFilters;
    }

    // Reset all filters
    public function resetFilters()
    {
        $this->reset(['search', 'role', 'gender', 'status']);
        $this->status = 'active';
        $this->resetPage();
    }

    // Open modal for adding new user
    public function createUser()
    {
        $this->resetForm();
        $this->editMode = false;
        $this->showModal = true;
    }

    // Open modal for editing existing user
    public function editUser($userId)
    {
        $this->resetForm();
        $this->editMode = true;
        $user = User::withTrashed()->findOrFail($userId);

        $this->userId = $user->id;
        $this->salutation = $user->salutation ?? '';
        $this->first_name = $user->first_name ?? '';
        $this->middle_name = $user->middle_name ?? '';
        $this->surname = $user->surname ?? '';
        $this->gender_input = $user->gender ?? '';
        $this->dob = $user->dob ? $user->dob->format('Y-m-d') : '';
        $this->email = $user->email ?? '';
        $this->phone_number = $user->phone_number ?? '';
        $this->username = $user->username ?? '';
        $this->address = $user->address ?? '';
        $this->district = $user->district ?? '';
        $this->region = $user->region ?? '';
        $this->country = $user->country ?? '';
        $this->postal_code = $user->postal_code ?? '';
        $this->reg_number = $user->reg_number ?? '';
        $this->qualification = $user->qualification ?? '';
        $this->selectedRole = $user->roles->first()?->id ?? '';

        $this->showModal = true;
    }

    // Save user (create or update)
    public function saveUser()
    {
        $rules = [
            'first_name' => 'required|string|max:255',
            'surname' => 'required|string|max:255',
            'email' => [
                'required',
                'email',
                Rule::unique('users')->ignore($this->userId),
            ],
            'username' => [
                'required',
                'string',
                'max:255',
                Rule::unique('users')->ignore($this->userId),
            ],
            'gender_input' => 'required|in:Male,Female,Other',
            'phone_number' => 'nullable|string|max:20',
            'dob' => 'nullable|date',
            'address' => 'nullable|string|max:500',
            'selectedRole' => 'required|exists:roles,id',
        ];

        if (! $this->editMode) {
            $rules['password'] = 'required|string|min:8|confirmed';
        } elseif ($this->password) {
            $rules['password'] = 'nullable|string|min:8|confirmed';
        }

        $this->validate($rules, [
            'first_name.required' => 'Please enter the first name.',
            'surname.required' => 'Please enter the surname.',
            'email.required' => 'Please enter an email address.',
            'email.email' => 'Please enter a valid email address.',
            'email.unique' => 'This email address is already in use.',
            'username.required' => 'Please enter a username.',
            'username.unique' => 'This username is already taken.',
            'gender_input.required' => 'Please select a gender.',
            'selectedRole.required' => 'Please select a role for this user.',
            'selectedRole.exists' => 'The selected role is invalid.',
            'password.required' => 'Please enter a password.',
            'password.min' => 'Password must be at least 8 characters.',
            'password.confirmed' => 'Password confirmation does not match.',
        ]);

        $userData = [
            'salutation' => $this->salutation,
            'first_name' => $this->first_name,
            'middle_name' => $this->middle_name,
            'surname' => $this->surname,
            'gender' => $this->gender_input,
            'dob' => $this->dob,
            'email' => $this->email,
            'phone_number' => $this->phone_number,
            'username' => $this->username,
            'address' => $this->address,
            'district' => $this->district,
            'region' => $this->region,
            'country' => $this->country,
            'postal_code' => $this->postal_code,
            'reg_number' => $this->reg_number,
            'qualification' => $this->qualification,
        ];

        if ($this->password) {
            $userData['password'] = Hash::make($this->password);
        }

        if ($this->editMode) {
            $user = User::withTrashed()->findOrFail($this->userId);
            $user->update($userData);
            session()->flash('message', 'User updated successfully.');
        } else {
            $user = User::create($userData);
            session()->flash('message', 'User created successfully.');
        }

        // Assign the selected role
        $role = Role::find($this->selectedRole);
        if ($role) {
            $user->syncRoles([$role]);
        }

        $this->closeModal();
        $this->resetPage();
    }

    // Disable user (soft delete)
    public function disableUser($userId)
    {
        $user = User::findOrFail($userId);
        $user->delete();
        session()->flash('message', 'User disabled successfully.');
    }

    // Enable user (restore soft deleted)
    public function enableUser($userId)
    {
        $user = User::withTrashed()->findOrFail($userId);
        $user->restore();
        session()->flash('message', 'User enabled successfully.');
    }

    // Close modal and reset form
    public function closeModal()
    {
        $this->showModal = false;
        $this->resetForm();
        $this->resetValidation();
    }

    // Reset form fields
    private function resetForm()
    {
        $this->reset([
            'userId',
            'salutation',
            'first_name',
            'middle_name',
            'surname',
            'gender_input',
            'dob',
            'email',
            'phone_number',
            'username',
            'password',
            'password_confirmation',
            'address',
            'district',
            'region',
            'country',
            'postal_code',
            'reg_number',
            'qualification',
            'selectedRole',
        ]);
    }

    public function render()
    {
        $query = User::query();

        // Apply soft delete filter based on status
        if ($this->status === 'active') {
            // Only active (not soft deleted)
        } elseif ($this->status === 'inactive') {
            $query->onlyTrashed();
        } else {
            // All users (including soft deleted)
            $query->withTrashed();
        }

        // Search filter
        if ($this->search) {
            $query->where(function ($q) {
                $q->where('first_name', 'like', '%'.$this->search.'%')
                    ->orWhere('surname', 'like', '%'.$this->search.'%')
                    ->orWhere('middle_name', 'like', '%'.$this->search.'%')
                    ->orWhere('email', 'like', '%'.$this->search.'%')
                    ->orWhere('username', 'like', '%'.$this->search.'%')
                    ->orWhere('phone_number', 'like', '%'.$this->search.'%');
            });
        }

        // Role filter
        if ($this->role) {
            $query->role($this->role);
        }

        // Gender filter
        if ($this->gender) {
            $query->where('gender', $this->gender);
        }

        $users = $query->latest()->paginate($this->perPage);
        $roles = Role::all();

        return view('livewire.users.user-management', [
            'users' => $users,
            'roles' => $roles,
        ]);
    }
}
