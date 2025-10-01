<?php

declare(strict_types=1);

namespace App\Livewire\Auth;

use App\Models\PasswordResetToken;
use App\Models\User;
use Devrabiul\ToastMagic\Facades\ToastMagic;
use Illuminate\Support\Facades\Hash;
use Illuminate\View\View;
use Livewire\Attributes\Validate;
use Livewire\Component;
use Psr\Container\ContainerExceptionInterface;
use Psr\Container\NotFoundExceptionInterface;

class ResetPassword extends Component
{
    public string $email = '';

    #[Validate('required|string|size:5')]
    public string $token = '';

    #[Validate('required|string|min:8|confirmed')]
    public string $password = '';

    public string $password_confirmation = '';

    public bool $showTokenForm = true;

    /**
     * @throws ContainerExceptionInterface
     * @throws NotFoundExceptionInterface
     */
    public function mount(): void
    {
        $this->email = request()->get('email', '');
    }

    public function render(): View
    {
        return view('livewire.auth.reset-password')
            ->layout('components.layouts.guest');
    }

    public function verifyToken(): void
    {
        $this->validate(['token' => 'required|string|size:5']);

        $resetToken = PasswordResetToken::findValidToken($this->email, $this->token);

        if (! $resetToken) {
            $this->addError('token', 'The reset code is invalid or has expired.');

            return;
        }

        $this->showTokenForm = false;
        ToastMagic::success('Code Verified', 'Please enter your new password.');
    }

    public function resetPassword(): mixed
    {
        if ($this->showTokenForm) {
            $this->verifyToken();

            return null;
        }

        $this->validate([
            'password' => 'required|string|min:8|confirmed',
        ]);

        $resetToken = PasswordResetToken::findValidToken($this->email, $this->token);

        if (! $resetToken) {
            $this->addError('token', 'The reset session has expired. Please request a new reset code.');

            return null;
        }

        $user = User::where('email', $this->email)->first();

        if (! $user) {
            $this->addError('email', 'User not found.');

            return null;
        }

        $user->update([
            'password' => Hash::make($this->password),
        ]);

        PasswordResetToken::query()->where('email', $this->email)->update(['used' => true]);

        ToastMagic::success('Password Reset', 'Your password has been successfully reset.');

        return redirect()->route('login');
    }
}
