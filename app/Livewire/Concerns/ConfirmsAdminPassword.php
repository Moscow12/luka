<?php

namespace App\Livewire\Concerns;

use App\Support\AdminPasswordConfirmation;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Hash;
use Illuminate\Support\Facades\RateLimiter;
use Livewire\Attributes\Locked;

/**
 * Re-authentication for sensitive administrative actions.
 *
 * An action calls `if (! $this->adminPasswordConfirmed()) return $this->requireAdminPassword(__FUNCTION__, func_get_args());`
 * After a correct password the original action is called again from scratch, so it
 * re-runs every authorization check. Only actions listed in adminConfirmableActions()
 * can be resumed, and the pending action/arguments are locked against client changes.
 * Include `livewire.partials.confirm-admin-password` in the component's view.
 */
trait ConfirmsAdminPassword
{
    public $adminPassword = '';

    public bool $showAdminPasswordModal = false;

    #[Locked]
    public ?string $pendingAdminAction = null;

    #[Locked]
    public array $pendingAdminArguments = [];

    /** @return list<string> methods that may be resumed after confirmation */
    abstract protected function adminConfirmableActions(): array;

    protected function adminPasswordConfirmed(): bool
    {
        return AdminPasswordConfirmation::isFresh();
    }

    protected function requireAdminPassword(string $action, array $arguments = []): null
    {
        $this->pendingAdminAction = $action;
        $this->pendingAdminArguments = array_values($arguments);
        $this->adminPassword = '';
        $this->resetValidation('adminPassword');
        $this->showAdminPasswordModal = true;

        return null;
    }

    public function confirmAdminPassword()
    {
        $user = Auth::user();
        abort_unless($user, 403);

        $this->validate(['adminPassword' => ['required', 'string']], [], ['adminPassword' => 'password']);

        $key = 'admin-confirm:'.$user->getAuthIdentifier();

        if (RateLimiter::tooManyAttempts($key, 5)) {
            $this->addError('adminPassword', 'Too many attempts. Try again in '.RateLimiter::availableIn($key).' seconds.');

            return null;
        }

        if (! Hash::check($this->adminPassword, $user->getAuthPassword())) {
            RateLimiter::hit($key, 300);
            $this->adminPassword = '';
            $this->addError('adminPassword', 'The password is incorrect.');
            activity('security')->causedBy($user)->event('admin-confirmation-failed')->log('Administrator password confirmation failed');

            return null;
        }

        RateLimiter::clear($key);
        AdminPasswordConfirmation::record();

        [$action, $arguments] = [$this->pendingAdminAction, $this->pendingAdminArguments];
        $this->cancelAdminPassword();

        if ($action === null || ! in_array($action, $this->adminConfirmableActions(), true)) {
            return null;
        }

        return $this->{$action}(...$arguments);
    }

    public function cancelAdminPassword(): void
    {
        $this->showAdminPasswordModal = false;
        $this->pendingAdminAction = null;
        $this->pendingAdminArguments = [];
        $this->adminPassword = '';
        $this->resetValidation('adminPassword');
    }
}
