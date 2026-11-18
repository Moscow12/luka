<?php

declare(strict_types=1);

namespace App\Livewire\Auth;

use App\Models\workstations;
use App\Services\Auth\AccountSetupService;
use Illuminate\View\View;
use Livewire\Attributes\Locked;
use Livewire\Component;

/**
 * Lets a new staff member choose their password from a one-time setup link.
 * The token is read from the server-side session only (see AccountSetupLinkController).
 */
class SetupAccount extends Component
{
    public string $password = '';

    public string $password_confirmation = '';

    /** Display only. */
    #[Locked]
    public string $username = '';

    #[Locked]
    public bool $linkValid = false;

    public function mount(AccountSetupService $setup): void
    {
        $record = $setup->findValid(session(AccountSetupService::SESSION_KEY));

        $this->linkValid = $record !== null && $record->user !== null;
        $this->username = $this->linkValid ? (string) $record->user->username : '';
    }

    public function setPassword(AccountSetupService $setup): mixed
    {
        $this->validate([
            'password' => ['required', 'string', 'min:8', 'max:255', 'confirmed'],
        ]);

        $user = $setup->complete(session(AccountSetupService::SESSION_KEY), $this->password);

        $this->reset(['password', 'password_confirmation']);

        if (! $user) {
            session()->forget(AccountSetupService::SESSION_KEY);
            $this->linkValid = false;
            $this->addError('password', 'This setup link is invalid, expired or has already been used.');

            return null;
        }

        session()->forget(AccountSetupService::SESSION_KEY);
        session()->regenerate();
        session()->flash('status', 'Your password has been set. Please sign in with your username and new password.');

        return redirect()->route('login');
    }

    public function render(): View
    {
        return view('livewire.auth.setup-account', [
            'workstation' => workstations::first(),
            'appName' => config('app.name', 'Dasher'),
            'expireHours' => (int) config('auth.account_setup_expire_hours', 72),
        ])->layout('components.layouts.guest');
    }
}
