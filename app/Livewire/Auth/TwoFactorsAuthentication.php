<?php

declare(strict_types=1);

namespace App\Livewire\Auth;

use App\Models\TrustedDevice;
use App\Models\TwoFactorToken;
use App\Models\User;
use App\Models\workstations;
use App\Notifications\TwoFactorCode;
use App\Services\DeviceFingerprinter;
use App\Support\AuthThrottle;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Session;
use Illuminate\View\View;
use Livewire\Attributes\Locked;
use Livewire\Attributes\Validate;
use Livewire\Component;
use Random\RandomException;

class TwoFactorsAuthentication extends Component
{
    #[Validate('required|string|size:6|regex:/^[0-9]+$/')]
    public string $token = '';

    public bool $remember_device = false;

    /** Display only. Actions always use session('2fa_user_email'), set after the password check. */
    #[Locked]
    public string $email = '';

    #[Locked]
    public string $maskedEmail = '';

    /**
     * @throws RandomException
     */
    public function mount(): mixed
    {
        $this->email = (string) Session::get('2fa_user_email', '');

        if (! $this->email) {
            return redirect()->route('login');
        }

        $this->maskedEmail = $this->maskEmail($this->email);

        // Send a code on first load; reloading the page counts against the send limit
        $this->sendTwoFactorCode();

        return null;
    }

    public function render(): View
    {
        $workstation = workstations::first();

        return view('livewire.auth.two-factors-authentication', [
            'workstation' => $workstation,
            'appName' => config('app.name', 'Dasher'),
        ])->layout('components.layouts.guest');
    }

    public function verifyTwoFactor(): mixed
    {
        $this->validate();

        $email = $this->pendingEmail();

        if ($email === null) {
            return redirect()->route('login');
        }

        $emailKey = AuthThrottle::key('2fa-verify', $email);
        $limits = [
            $emailKey => AuthThrottle::TWO_FACTOR_VERIFY_PER_EMAIL,
            AuthThrottle::key('2fa-verify-ip', (string) request()->ip()) => AuthThrottle::TWO_FACTOR_VERIFY_PER_IP,
        ];

        AuthThrottle::ensureNotBlocked($limits, 'token');

        $user = User::where('email', $email)->first();
        $tokenRecord = $user ? TwoFactorToken::findValidToken($email, $this->token) : null;

        // markAsUsed() is atomic, so a code can't be redeemed twice by concurrent requests
        if (! $tokenRecord || ! $tokenRecord->markAsUsed()) {
            AuthThrottle::hit($limits);

            if (AuthThrottle::exhausted($emailKey, AuthThrottle::TWO_FACTOR_VERIFY_PER_EMAIL[0])) {
                TwoFactorToken::invalidateForEmail($email);
                $this->addError('token', 'Too many incorrect attempts. Please wait, then request a new code.');

                return null;
            }

            $this->addError('token', 'Invalid or expired code. Please request a new one.');

            return null;
        }

        AuthThrottle::clear($emailKey);

        // Save device as trusted if user opted in
        if ($this->remember_device) {
            $this->saveDeviceAsTrusted($user);
        }

        session()->regenerate();
        Auth::login($user, $this->remember_device);

        // Clear 2FA session data
        Session::forget([
            '2fa_user_email',
            '2fa_device_fingerprint',
            '2fa_device_name',
            '2fa_device_ip',
            '2fa_device_user_agent',
        ]);

        return redirect()->route('dashboard');
    }

    /**
     * @throws RandomException
     */
    public function resendCode(): void
    {
        if (! $this->sendTwoFactorCode()) {
            $this->addError('token', 'Too many codes requested. Please wait a few minutes before trying again.');

            return;
        }

        $this->dispatch('toastMagic',
            status: 'success',
            title: 'Verification Code Sent',
            message: 'A new verification code has been sent to your email.'
        );
    }

    /**
     * Sends a fresh code to the pending (session) account. Returns false when throttled.
     *
     * @throws RandomException
     */
    private function sendTwoFactorCode(): bool
    {
        $email = $this->pendingEmail();

        if ($email === null) {
            return false;
        }

        $limits = [AuthThrottle::key('2fa-send', $email) => AuthThrottle::TWO_FACTOR_SEND_PER_EMAIL];

        if (AuthThrottle::blockedFor($limits) !== null) {
            return false;
        }

        AuthThrottle::hit($limits);

        $user = User::where('email', $email)->first();

        if ($user) {
            $tokenRecord = TwoFactorToken::createForEmail($email);
            $user->notify(new TwoFactorCode($tokenRecord->token));
        }

        return true;
    }

    private function pendingEmail(): ?string
    {
        $email = (string) Session::get('2fa_user_email', '');

        return $email === '' ? null : $email;
    }

    private function saveDeviceAsTrusted(User $user): void
    {
        // Get device details from session (stored during login)
        $deviceFingerprint = Session::get('2fa_device_fingerprint');
        $deviceName = Session::get('2fa_device_name');
        $ipAddress = Session::get('2fa_device_ip');
        $userAgent = Session::get('2fa_device_user_agent');

        // Fallback to current request if session data is missing
        if (! $deviceFingerprint) {
            $fingerprinter = new DeviceFingerprinter(request());
            $deviceFingerprint = $fingerprinter->generateFingerprint();
            $deviceName = $fingerprinter->generateDeviceName();
            $ipAddress = $fingerprinter->getCurrentIpAddress();
            $userAgent = request()->userAgent() ?? '';
        }

        // Check if device already exists and just needs reactivation
        $existingDevice = TrustedDevice::where('user_id', $user->id)
            ->where('device_fingerprint', $deviceFingerprint)
            ->first();

        if ($existingDevice) {
            $existingDevice->update([
                'is_active' => true,
                'expires_at' => now()->addDays(30),
                'last_used_at' => now(),
                'ip_address' => $ipAddress,
                'user_agent' => $userAgent,
            ]);
        } else {
            TrustedDevice::createForUser(
                userId: $user->id,
                deviceFingerprint: $deviceFingerprint,
                deviceName: $deviceName,
                ipAddress: $ipAddress,
                userAgent: $userAgent,
                trustDays: 30
            );
        }
    }

    private function maskEmail(string $email): string
    {
        $parts = explode('@', $email);
        $username = $parts[0];
        $domain = $parts[1] ?? '';

        if (strlen($username) <= 3) {
            $maskedUsername = str_repeat('*', strlen($username));
        } else {
            $maskedUsername = substr($username, 0, 3).str_repeat('*', strlen($username) - 3);
        }

        return $maskedUsername.'@'.$domain;
    }
}
