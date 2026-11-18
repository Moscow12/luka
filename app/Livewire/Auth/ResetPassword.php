<?php

declare(strict_types=1);

namespace App\Livewire\Auth;

use App\Models\PasswordResetToken;
use App\Models\TrustedDevice;
use App\Models\User;
use App\Models\workstations;
use App\Support\AuthThrottle;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Hash;
use Illuminate\Support\Str;
use Illuminate\View\View;
use Livewire\Attributes\Locked;
use Livewire\Attributes\Validate;
use Livewire\Component;
use Spatie\Activitylog\Facades\CauserResolver;

class ResetPassword extends Component
{
    /** Display only; the authoritative address is session('password_reset_email'). */
    #[Locked]
    public string $email = '';

    #[Validate('required|string|size:6')]
    public string $token = '';

    #[Validate('required|string|min:8|confirmed')]
    public string $password = '';

    public string $password_confirmation = '';

    #[Locked]
    public bool $showTokenForm = true;

    public function mount(): mixed
    {
        $this->email = (string) session('password_reset_email', '');

        if ($this->email === '') {
            return redirect()->route('forgot-password');
        }

        $this->showTokenForm = ! $this->verifiedToken();

        return null;
    }

    public function render(): View
    {
        return view('livewire.auth.reset-password', [
            'workstation' => workstations::first(),
            'appName' => config('app.name', 'Dasher'),
        ])->layout('components.layouts.guest');
    }

    public function verifyToken(): void
    {
        $this->validate(['token' => 'required|string|size:6']);

        $email = $this->sessionEmail();
        $emailKey = AuthThrottle::key('reset-verify', $email);
        $limits = [
            $emailKey => AuthThrottle::RESET_VERIFY_PER_EMAIL,
            AuthThrottle::key('reset-verify-ip', (string) request()->ip()) => AuthThrottle::RESET_VERIFY_PER_IP,
        ];

        AuthThrottle::ensureNotBlocked($limits, 'token');

        $resetToken = PasswordResetToken::findValidToken($email, $this->token);

        if (! $resetToken) {
            AuthThrottle::hit($limits);

            if (AuthThrottle::exhausted($emailKey, AuthThrottle::RESET_VERIFY_PER_EMAIL[0])) {
                // Too many wrong guesses: the code is burned and a new one must be requested
                PasswordResetToken::invalidateForEmail($email);
                $this->addError('token', 'Too many incorrect attempts. Please request a new reset code.');

                return;
            }

            $this->addError('token', 'The reset code is invalid or has expired.');

            return;
        }

        AuthThrottle::clear($emailKey);

        // Step 2 trusts this server-side marker, not anything the client sends back
        session(['password_reset_verified' => ['email' => $email, 'token' => $resetToken->token]]);

        $this->token = '';
        $this->showTokenForm = false;
        $this->dispatch('toastMagic',
            status: 'success',
            title: 'Code Verified',
            message: 'Please enter your new password.'
        );
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

        $resetToken = $this->verifiedToken();
        $user = $resetToken ? User::where('email', $resetToken->email)->first() : null;

        if (! $resetToken || ! $user || ! $resetToken->markAsUsed()) {
            session()->forget('password_reset_verified');
            $this->showTokenForm = true;
            $this->addError('token', 'The reset session has expired. Please request a new reset code.');

            return null;
        }

        // The causer is set explicitly since the user isn't authenticated in the reset flow
        CauserResolver::setCauser($user);

        $user->forceFill([
            'password' => Hash::make($this->password),
            'remember_token' => Str::random(60), // kills "remember me" cookies
        ])->save();

        PasswordResetToken::invalidateForEmail($user->email);

        // A password change revokes every existing trust: devices and sessions
        TrustedDevice::where('user_id', $user->id)->update(['is_active' => false]);

        if (config('session.driver') === 'database') {
            DB::connection(config('session.connection'))
                ->table(config('session.table', 'sessions'))
                ->where('user_id', $user->id)
                ->delete();
        }

        // No automatic login and no trusted-device grant: the next login goes through
        // the normal password + 2FA flow.
        session()->forget(['password_reset_email', 'password_reset_verified']);
        session()->regenerate();
        session()->flash('status', 'Your password has been updated. Please sign in with your new password.');

        $this->dispatch('toastMagic',
            status: 'success',
            title: 'Password Updated',
            message: 'Please sign in with your new password.'
        );

        return redirect()->route('login');
    }

    private function sessionEmail(): string
    {
        $email = (string) session('password_reset_email', '');

        abort_if($email === '', 419, 'Your reset session has expired. Please request a new reset code.');

        return $email;
    }

    private function verifiedToken(): ?PasswordResetToken
    {
        $marker = session('password_reset_verified');
        $email = (string) session('password_reset_email', '');

        if (! is_array($marker) || $email === '' || ($marker['email'] ?? null) !== $email) {
            return null;
        }

        return PasswordResetToken::findValidToken($email, (string) ($marker['token'] ?? ''));
    }
}
