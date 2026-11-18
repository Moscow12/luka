<?php

namespace App\Livewire\Setup;

use App\Services\Backup\BackupException;
use App\Services\Backup\BackupManager;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Hash;
use Illuminate\Support\Facades\RateLimiter;
use Illuminate\Support\Facades\URL;
use Livewire\Attributes\Locked;
use Livewire\Component;
use Throwable;

/**
 * Backup management UI. Requires the manage-backups permission for everything, and a
 * fresh backup-specific password confirmation for create, download and delete.
 * Restore is deliberately not available here: see `php artisan backup:restore`.
 */
class Backuprecovery extends Component
{
    /** Session key holding the time of the last successful backup password confirmation. */
    public const CONFIRMED_AT = 'backup.password_confirmed_at';

    public const PERMISSION = 'manage-backups';

    // Create form. The label is free text kept inside the encrypted metadata, never in a path.
    public $backup_label = '';

    public $backup_description = '';

    public $include_database = true;

    public $include_files = false;

    public $include_uploads = true;

    public $showCreateModal = false;

    // Password confirmation
    public $confirmPassword = '';

    public $showConfirmModal = false;

    /** The action waiting for confirmation; set server-side only. */
    #[Locked]
    public ?string $pendingAction = null;

    #[Locked]
    public ?string $pendingFilename = null;

    protected function rules(): array
    {
        return [
            'backup_label' => ['nullable', 'string', 'max:100'],
            'backup_description' => ['nullable', 'string', 'max:500'],
            'include_database' => ['boolean'],
            'include_uploads' => ['boolean'],
            'include_files' => ['boolean'],
        ];
    }

    public function mount(): void
    {
        $this->authorizeBackups();
    }

    public function openCreateModal(): void
    {
        $this->authorizeBackups();
        $this->reset(['backup_label', 'backup_description', 'include_files']);
        $this->include_database = true;
        $this->include_uploads = true;
        $this->resetValidation();
        $this->showCreateModal = true;
    }

    public function closeCreateModal(): void
    {
        $this->showCreateModal = false;
        $this->reset(['backup_label', 'backup_description']);
    }

    public function createBackup()
    {
        $this->authorizeBackups();
        $this->validate();

        if (! $include = array_filter(['database' => $this->include_database, 'uploads' => $this->include_uploads, 'files' => $this->include_files])) {
            $this->addError('include_database', 'Choose at least one thing to include.');

            return null;
        }

        if (! $this->passwordRecentlyConfirmed()) {
            return $this->requireConfirmation('create');
        }

        try {
            $filename = $this->backups()->create(Auth::user(), $include, $this->backup_label, $this->backup_description);
            session()->flash('success', "Backup created: {$filename}");
            $this->closeCreateModal();
        } catch (Throwable $e) {
            $this->reportFailure($e, 'Backup failed.');
        }

        return null;
    }

    public function downloadBackup($filename)
    {
        $this->authorizeBackups();

        try {
            $this->backups()->resolve($filename);
        } catch (BackupException $e) {
            session()->flash('error', $e->getMessage());

            return null;
        }

        if (! $this->passwordRecentlyConfirmed()) {
            return $this->requireConfirmation('download', $filename);
        }

        // A short-lived signed link bound to this user; the controller re-checks everything
        return $this->redirect(URL::temporarySignedRoute(
            'setup.backups.download',
            now()->addSeconds((int) config('backup.download_link_ttl')),
            ['filename' => $filename, 'user' => Auth::id()],
        ));
    }

    public function deleteBackup($filename)
    {
        $this->authorizeBackups();

        try {
            $this->backups()->resolve($filename);
        } catch (BackupException $e) {
            session()->flash('error', $e->getMessage());

            return null;
        }

        if (! $this->passwordRecentlyConfirmed()) {
            return $this->requireConfirmation('delete', $filename);
        }

        try {
            $this->backups()->delete(Auth::user(), $filename);
            session()->flash('success', 'Backup deleted.');
        } catch (Throwable $e) {
            $this->reportFailure($e, 'The backup could not be deleted.');
        }

        return null;
    }

    public function confirmPasswordAndContinue()
    {
        $this->authorizeBackups();
        $this->validate(['confirmPassword' => ['required', 'string']], [], ['confirmPassword' => 'password']);

        $user = Auth::user();
        $key = 'backup-confirm:'.$user->getAuthIdentifier();

        if (RateLimiter::tooManyAttempts($key, 5)) {
            $this->addError('confirmPassword', 'Too many attempts. Try again in '.RateLimiter::availableIn($key).' seconds.');

            return null;
        }

        if (! Hash::check($this->confirmPassword, $user->getAuthPassword())) {
            RateLimiter::hit($key, 300);
            $this->reset('confirmPassword');
            $this->addError('confirmPassword', 'The password is incorrect.');

            activity('backup')->causedBy($user)->event('confirmation-failed')->log('Backup password confirmation failed');

            return null;
        }

        RateLimiter::clear($key);
        session()->put(self::CONFIRMED_AT, time());

        [$action, $filename] = [$this->pendingAction, $this->pendingFilename];
        $this->cancelConfirmation();

        // Re-enter the original action, which re-authorizes and re-validates from scratch
        return match ($action) {
            'create' => $this->createBackup(),
            'download' => $this->downloadBackup($filename),
            'delete' => $this->deleteBackup($filename),
            default => null,
        };
    }

    public function cancelConfirmation(): void
    {
        $this->showConfirmModal = false;
        $this->pendingAction = null;
        $this->pendingFilename = null;
        $this->reset('confirmPassword');
        $this->resetValidation('confirmPassword');
    }

    /** Shared with the download controller so both apply the same window. */
    public static function confirmationIsFresh(): bool
    {
        $confirmedAt = (int) session(self::CONFIRMED_AT, 0);

        return $confirmedAt > 0 && (time() - $confirmedAt) < (int) config('backup.password_confirmation_timeout');
    }

    protected function passwordRecentlyConfirmed(): bool
    {
        return self::confirmationIsFresh();
    }

    protected function requireConfirmation(string $action, ?string $filename = null): null
    {
        $this->pendingAction = $action;
        $this->pendingFilename = $filename;
        $this->reset('confirmPassword');
        $this->showConfirmModal = true;

        return null;
    }

    protected function backups(): BackupManager
    {
        return app(BackupManager::class);
    }

    protected function authorizeBackups(): void
    {
        abort_unless(Auth::user()?->can(self::PERMISSION), 403);
    }

    protected function reportFailure(Throwable $e, string $fallback): void
    {
        if ($e instanceof BackupException) {
            session()->flash('error', $e->getMessage());

            return;
        }

        report($e);
        session()->flash('error', $fallback.' See the application log for details.');
    }

    public function render()
    {
        return view('livewire.setup.backuprecovery', [
            'backups' => $this->backups()->list(),
            'archivePasswordConfigured' => $this->backups()->isConfigured(),
        ]);
    }
}
