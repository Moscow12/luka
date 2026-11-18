<?php

namespace App\Http\Controllers;

use App\Livewire\Setup\Backuprecovery;
use App\Services\Backup\BackupException;
use App\Services\Backup\BackupManager;
use Illuminate\Http\Request;

/**
 * Streams a backup archive. Reached only through a short-lived signed URL issued by the
 * Backup & Recovery page; every check is repeated here because a URL can be replayed.
 */
class BackupDownloadController extends Controller
{
    public function __invoke(Request $request, BackupManager $backups, string $filename)
    {
        $user = $request->user();

        abort_unless($user->can(Backuprecovery::PERMISSION), 403);
        abort_unless(hash_equals((string) $user->getAuthIdentifier(), (string) $request->query('user')), 403);
        abort_unless(Backuprecovery::confirmationIsFresh(), 403, 'Password confirmation has expired.');

        try {
            $path = $backups->pathForDownload($user, $filename);
        } catch (BackupException) {
            abort(404);
        }

        return response()->download($path, $filename, [
            'Content-Type' => 'application/zip',
            'X-Content-Type-Options' => 'nosniff',
            'Cache-Control' => 'private, no-store',
        ]);
    }
}
