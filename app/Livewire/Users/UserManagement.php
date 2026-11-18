<?php

namespace App\Livewire\Users;

use App\Livewire\Concerns\ConfirmsAdminPassword;
use App\Models\Role;
use App\Models\User;
use App\Services\Acl\AccessControl;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Hash;
use Illuminate\Support\Facades\Schema;
use Illuminate\Support\Str;
use Illuminate\Validation\Rule;
use Illuminate\Validation\Rules\Password;
use Livewire\Attributes\Locked;
use Livewire\Attributes\Url;
use Livewire\Component;
use Livewire\WithPagination;

/**
 * User administration. Requires manage-users on every request; a non-super-admin can only
 * manage accounts whose access is within their own, can only grant roles they could
 * themselves hold (no escalation), can never change their own role or disable themselves,
 * and must re-enter their password before any change is saved.
 */
class UserManagement extends Component
{
    use ConfirmsAdminPassword, WithPagination;

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

    // Form Fields. The id of the account being edited is set server-side only.
    #[Locked]
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

    /** Runs on every request (initial render and every action), not just on mount. */
    public function boot(): void
    {
        abort_unless(Auth::user()?->can(AccessControl::MANAGE_USERS), 403);
    }

    protected function adminConfirmableActions(): array
    {
        return ['saveUser', 'disableUser', 'enableUser'];
    }

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
        $user = User::withTrashed()->findOrFail($userId);

        if (! AccessControl::canManageUser(Auth::user(), $user)) {
            session()->flash('error', 'You are not permitted to manage this account.');

            return;
        }

        $this->resetForm();
        $this->editMode = true;

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

        $strongPassword = Password::min(12)->letters()->mixedCase()->numbers()->symbols();

        if (! $this->editMode) {
            $rules['password'] = ['required', 'string', 'confirmed', $strongPassword];
        } elseif ($this->password) {
            $rules['password'] = ['nullable', 'string', 'confirmed', $strongPassword];
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
            'password.min' => 'Password must be at least 12 characters.',
            'password.confirmed' => 'Password confirmation does not match.',
        ]);

        $actor = Auth::user();
        $role = Role::findOrFail($this->selectedRole);
        $target = $this->editMode ? User::withTrashed()->findOrFail($this->userId) : null;

        if ($target && ! AccessControl::canManageUser($actor, $target)) {
            session()->flash('error', 'You are not permitted to manage this account.');
            $this->closeModal();

            return null;
        }

        // Saving would sync the account to exactly this one role, so for yourself it must already be exactly your role
        if ($target && $target->is($actor) && $target->getRoleNames()->implode(', ') !== $role->name) {
            $this->addError('selectedRole', 'You cannot change your own role. Ask another administrator.');

            return null;
        }

        if (! AccessControl::canGrantRole($actor, $role)
            && ! ($target && $target->roles->pluck('id')->contains($role->id))) {
            $this->addError('selectedRole', 'You can only grant roles whose permissions you hold yourself.');

            return null;
        }

        if (! $this->adminPasswordConfirmed()) {
            return $this->requireAdminPassword('saveUser');
        }

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

        if ($target) {
            $user = $target;
            $user->update($userData);
            session()->flash('message', 'User updated successfully.');
        } else {
            $user = User::create($userData);
            session()->flash('message', 'User created successfully.');
        }

        $previousRoleNames = $user->getRoleNames()->implode(', ') ?: 'none';

        // Only touch roles when they actually change, so a user's own (unchanged) role is never re-synced
        if ($previousRoleNames !== $role->name) {
            $user->syncRoles([$role]);

            activity('user')
                ->causedBy($actor)
                ->performedOn($user)
                ->event('updated')
                ->withProperties(['old' => ['role' => $previousRoleNames], 'attributes' => ['role' => $role->name]])
                ->log("User role changed from {$previousRoleNames} to {$role->name}");
        }

        if ($target && $this->password) {
            // An administrator reset the password: end the user's existing sessions
            $this->endSessionsOf($user);
            activity('user')->causedBy($actor)->performedOn($user)->event('password-reset')->log('Password reset by administrator');
        }

        $this->closeModal();
        $this->resetPage();

        return null;
    }

    // Disable user (soft delete)
    public function disableUser($userId)
    {
        $user = User::findOrFail($userId);

        if ($user->is(Auth::user())) {
            session()->flash('error', 'You cannot disable your own account.');

            return null;
        }

        if (! AccessControl::canManageUser(Auth::user(), $user)) {
            session()->flash('error', 'You are not permitted to manage this account.');

            return null;
        }

        if (! $this->adminPasswordConfirmed()) {
            return $this->requireAdminPassword('disableUser', [$userId]);
        }

        $user->delete();
        $this->endSessionsOf($user);
        activity('user')->causedBy(Auth::user())->performedOn($user)->event('disabled')->log('User disabled');
        session()->flash('message', 'User disabled successfully.');

        return null;
    }

    // Enable user (restore soft deleted)
    public function enableUser($userId)
    {
        $user = User::withTrashed()->findOrFail($userId);

        if (! AccessControl::canManageUser(Auth::user(), $user)) {
            session()->flash('error', 'You are not permitted to manage this account.');

            return null;
        }

        if (! $this->adminPasswordConfirmed()) {
            return $this->requireAdminPassword('enableUser', [$userId]);
        }

        $user->restore();
        activity('user')->causedBy(Auth::user())->performedOn($user)->event('enabled')->log('User enabled');
        session()->flash('message', 'User enabled successfully.');

        return null;
    }

    /** Logs the user out everywhere: database sessions and remember-me cookies. */
    protected function endSessionsOf(User $user): void
    {
        if (config('session.driver') === 'database' && Schema::hasTable(config('session.table', 'sessions'))) {
            DB::table(config('session.table', 'sessions'))->where('user_id', $user->getKey())->delete();
        }

        $user->forceFill(['remember_token' => Str::random(60)])->saveQuietly();
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
        $actor = Auth::user();
        $grantable = AccessControl::grantableRoles($actor);

        // The role dropdown offers only grantable roles, plus whatever the edited user already has
        $currentRoles = $this->userId ? Role::whereHas('users', fn ($q) => $q->whereKey($this->userId))->get() : collect();
        $roleOptions = $grantable->concat($currentRoles)->unique('id')->sortBy('name')->values();

        return view('livewire.users.user-management', [
            'users' => $users,
            'roles' => Role::orderBy('name')->get(), // filter list
            'roleOptions' => $roleOptions,
            'editingSelf' => $this->userId && (string) $this->userId === (string) $actor->getKey(),
            'manageableIds' => $users->getCollection()->filter(fn ($u) => AccessControl::canManageUser($actor, $u))->pluck('id')->all(),
        ]);
    }
}
