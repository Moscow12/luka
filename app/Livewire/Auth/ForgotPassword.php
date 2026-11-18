<?php

declare(strict_types=1);

namespace App\Livewire\Auth;

use App\Models\PasswordResetToken;
use App\Models\User;
use App\Models\workstations;
use App\Notifications\ResetPasswordCode;
use App\Support\AuthThrottle;
use Illuminate\Support\Facades\Log;
use Illuminate\View\View;
use Livewire\Component;
use Random\RandomException;

class ForgotPassword extends Component
{
    public const GENERIC_MESSAGE = 'If an account exists for that email address, a reset code has been sent to it.';

    public string $email = '';

    // No `exists` rule: the response must not reveal whether an account exists
    protected array $rules = [
        'email' => 'required|email|max:255',
    ];

    public function render(): View
    {
        return view('livewire.auth.forgot-password', [
            'workstation' => workstations::first(),
            'appName' => config('app.name', 'Dasher'),
        ])->layout('components.layouts.guest');
    }

    /**
     * @throws RandomException
     */
    public function performPasswordReset(): mixed
    {
        $this->validate();

        $email = trim($this->email); // throttle keys are lower-cased by AuthThrottle::key()
        $limits = [
            AuthThrottle::key('reset-request', $email) => AuthThrottle::RESET_REQUEST_PER_EMAIL,
            AuthThrottle::key('reset-request-ip', (string) request()->ip()) => AuthThrottle::RESET_REQUEST_PER_IP,
        ];

        // Counted whether or not the account exists, so throttling reveals nothing either
        AuthThrottle::ensureNotBlocked($limits, 'email');
        AuthThrottle::hit($limits);

        $user = User::where('email', $email)->first();

        if ($user) {
            $resetToken = PasswordResetToken::createForEmail($user->email);

            try {
                $user->notify(new ResetPasswordCode($resetToken->token));
            } catch (\Throwable $e) {
                Log::error('Password reset code could not be sent', ['user_id' => $user->id, 'error' => $e->getMessage()]);
            }
        }

        // The reset page reads the address from the server-side session, never from the URL
        session()->forget('password_reset_verified');
        session(['password_reset_email' => $user?->email ?? $email]);

        session()->flash('status', self::GENERIC_MESSAGE);
        $this->dispatch('toastMagic',
            status: 'success',
            title: 'Check Your Email',
            message: self::GENERIC_MESSAGE
        );

        return redirect()->route('reset-password');
    }
}
