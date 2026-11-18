<?php

namespace App\Console\Commands;

use App\Services\Backup\BackupException;
use App\Services\Backup\BackupManager;
use Illuminate\Console\Command;
use Throwable;

/**
 * The only way to restore a backup. Runs from the server console so it needs shell
 * access, takes a safety backup first, and asks for the file name to be retyped.
 */
class BackupRestore extends Command
{
    protected $signature = 'backup:restore
        {filename : Archive name as shown on the Backup & Recovery page, e.g. backup_2026_11_18_101500_ab12cd34.zip}
        {--force : Skip the interactive confirmation (for scripted recovery)}
        {--skip-safety-backup : Do not take a database backup of the current state first}
        {--allow-unencrypted : Permit restoring a legacy, unencrypted archive}';

    protected $description = 'Restore the database from a validated, encrypted backup archive';

    public function handle(BackupManager $backups): int
    {
        $filename = (string) $this->argument('filename');

        try {
            // Fails fast on bad names, traversal attempts and missing files
            $backups->resolve($filename);
        } catch (BackupException $e) {
            $this->error($e->getMessage());

            return self::FAILURE;
        }

        $this->warn('This replaces ALL current database data with the contents of '.$filename.'.');

        if (! $this->option('force')) {
            if (! $this->input->isInteractive()) {
                $this->error('Refusing to restore non-interactively without --force.');

                return self::FAILURE;
            }

            if ($this->ask('Type the archive name to confirm') !== $filename) {
                $this->error('Confirmation did not match. Nothing was changed.');

                return self::FAILURE;
            }
        }

        try {
            if (! $this->option('skip-safety-backup')) {
                $this->info('Taking a safety backup of the current database...');
                $safety = $backups->create(null, ['database' => true], 'Pre-restore safety backup', 'Taken automatically before restoring '.$filename);
                $this->info("Safety backup: {$safety}");
            }

            $this->info('Validating archive and restoring...');
            $result = $backups->restore($filename, (bool) $this->option('allow-unencrypted'), [
                'source' => 'console',
                'os_user' => get_current_user(),
                'host' => gethostname(),
            ]);
        } catch (BackupException $e) {
            $this->error($e->getMessage());

            return self::FAILURE;
        } catch (Throwable $e) {
            report($e);
            $this->error('Restore failed: see the application log for details.');

            return self::FAILURE;
        }

        $this->info('Restore complete ('.number_format($result['bytes']).' bytes of SQL applied).');
        $this->line('Consider clearing caches (php artisan optimize:clear). Users may need to sign in again.');

        return self::SUCCESS;
    }
}
