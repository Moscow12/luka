<?php

namespace App\Livewire\Users;

use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Hash;
use Illuminate\Validation\Rules\Password;
use Livewire\Component;

class ChangePassword extends Component
{
    public $current_password;
    public $new_password;
    public $new_password_confirmation;

    public $showCurrentPassword = false;
    public $showNewPassword = false;
    public $showConfirmPassword = false;

    // Real-time validation properties
    public $validateInRealTime = false;

    protected function rules()
    {
        return [
            'current_password' => ['required', 'string', 'current_password'],
            'new_password' => ['required', 'string', Password::min(8)
                ->letters()
                ->mixedCase()
                ->numbers()
                ->symbols(), 'different:current_password'],
            'new_password_confirmation' => ['required', 'same:new_password'],
        ];
    }

    protected $messages = [
        'current_password.required' => 'Current password is required',
        'current_password.current_password' => 'The current password is incorrect',
        'new_password.required' => 'New password is required',
        'new_password.min' => 'Password must be at least 8 characters',
        'new_password.different' => 'New password must be different from current password',
        'new_password_confirmation.required' => 'Please confirm your new password',
        'new_password_confirmation.same' => 'Password confirmation does not match',
    ];

    public function toggleCurrentPassword()
    {
        $this->showCurrentPassword = !$this->showCurrentPassword;
    }

    public function toggleNewPassword()
    {
        $this->showNewPassword = !$this->showNewPassword;
    }

    public function toggleConfirmPassword()
    {
        $this->showConfirmPassword = !$this->showConfirmPassword;
    }

    public function updated($propertyName)
    {
        $this->validateInRealTime = true;
        $this->validateOnly($propertyName);
    }

    public function updatePassword()
    {
        $this->validate();

        // Update the user's password
        $user = Auth::user();
        $user->password = Hash::make($this->new_password);
        $user->save();

        // Reset form fields
        $this->reset(['current_password', 'new_password', 'new_password_confirmation']);
        $this->resetValidation();

        // Flash success message
        session()->flash('success', 'Password updated successfully!');

        // Optional: Log out other sessions
        // Auth::logoutOtherDevices($this->current_password);
    }

    public function render()
    {
        return view('livewire.users.change-password');
    }
}
