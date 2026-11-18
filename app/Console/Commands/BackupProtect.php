<?php

namespace App\Console\Commands;

use App\Services\Backup\BackupManager;
use Illuminate\Console\Command;
use Illuminate\Support\Facades\File;
use Throwable;

/**
 * Brings existing archives up to the at-rest standard: AES-256 encrypts legacy
 * (unencrypted) archives in place and tightens file and directory permissions.
 */
class BackupProtect extends Command
{
    protected $signature = 'backup:protect {--dry-run : Only report what would change}';

    protected $description = 'Encrypt legacy backup archives and tighten backup file permissions';

    public function handle(BackupManager $backups): int
    {
        if (! $backups->isConfigured()) {
            $this->error('Set BACKUP_ARCHIVE_PASSWORD (at least 32 characters) first.');

            return self::FAILURE;
        }

        $directory = $backups->directory(); // also enforces 0700
        $converted = $already = $failed = 0;

        foreach (File::files($directory) as $file) {
            $name = $file->getFilename();

            if (! $backups->isValidFilename($name)) {
                $this->warn("Skipped (name not allow-listed, review manually): {$name}");

                continue;
            }

            if ($this->option('dry-run')) {
                $this->line("Would check: {$name}");

                continue;
            }

            try {
                if ($backups->protect($name)) {
                    $converted++;
                    $this->info("Encrypted: {$name}");
                } else {
                    $already++;
                }
            } catch (Throwable $e) {
                $failed++;
                $this->error("Failed: {$name}: {$e->getMessage()}");
            }
        }

        $this->info("Encrypted {$converted}, already protected {$already}, failed {$failed}.");

        return $failed > 0 ? self::FAILURE : self::SUCCESS;
    }
}
