<?php

namespace App\Livewire\Auth;

use App\Models\User;
use Livewire\Component;
use Illuminate\View\View;
use Jenssegers\Agent\Agent;
use App\Models\LoginActivity;
use App\Models\TrustedDevice;
use Livewire\Attributes\Validate;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Hash;
use App\Services\DeviceFingerprinter;
use Devrabiul\ToastMagic\Facades\ToastMagic;
use Illuminate\Validation\ValidationException;

class Login extends Component
{
    #[Validate('required')]
    public string $login = ''; // email | phone_number | username

    #[Validate('required')]
    public string $password = '';

    /**
     * @throws ValidationException
     */
    public function loginUser(): mixed
    {

        $this->validate();

        // Find the user by email, username, or phone
        $user = User::query()
            ->where('email', $this->login)
            ->orWhere('username', $this->login)
            ->orWhere('phone_number', $this->login)
            ->first();

        if (! $user || ! Hash::check($this->password, $user->password)) {
            throw ValidationException::withMessages([
                'login' => __('The provided credentials are incorrect.'),
            ]);
        }

        session()->regenerate();

        // Check if this device is trusted
        $fingerprinter = new DeviceFingerprinter(request());

        $deviceFingerprint = $fingerprinter->generateFingerprint();

        $trustedDevice = TrustedDevice::findValidDevice($user->id, $deviceFingerprint);

        // $agent = new Agent;
        // LoginActivity::create([
        //     'user_id' => $user->id,
        //     'ip_address' => request()->ip(),
        //     'user_agent' => request()->userAgent(),
        //     'platform' => $agent->platform(),
        //     'browser' => $agent->browser(),
        //     'device' => $agent->device(),
        //     'login_at' => now(),
        // ]);

        if ($trustedDevice) {
            // Device is trusted - skip 2FA and log in directly
            $trustedDevice->updateLastUsed();
            Auth::login($user);

            ToastMagic::success('Welcome Back', 'Logged in from trusted device');

            return redirect()->route('dashboard');
        }

        // Device is not trusted - enforce 2FA
        // Store device details in session for potential saving after 2FA
        session([
            '2fa_user_email' => $user->email,
            '2fa_device_fingerprint' => $deviceFingerprint,
            '2fa_device_name' => $fingerprinter->generateDeviceName(),
            '2fa_device_ip' => $fingerprinter->getCurrentIpAddress(),
            '2fa_device_user_agent' => request()->userAgent() ?? '',
        ]);

        ToastMagic::success('Credentials Verified', 'Please check your email for the verification code');

        return redirect()->route('2fa');
    }

    public function render(): View
    {
        return view('livewire.auth.login')
            ->layout('components.layouts.guest');
    }
}

