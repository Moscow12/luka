<?php

namespace App\Services\Backup;

use App\Models\User;
use FilesystemIterator;
use Illuminate\Support\Carbon;
use Illuminate\Support\Collection;
use Illuminate\Support\Facades\File;
use Illuminate\Support\Facades\Process;
use RecursiveDirectoryIterator;
use RecursiveIteratorIterator;
use RuntimeException;
use Throwable;
use ZipArchive;

/**
 * Creates, lists, deletes, protects and restores system backups.
 *
 * - Names are generated here and nowhere else; callers can only refer to an existing
 *   archive by a name that matches FILENAME_PATTERN and resolves inside the directory.
 * - Every archive entry is AES-256 encrypted with config('backup.archive_password');
 *   archives are 0600 inside a 0700 directory.
 * - Archives are validated entry-by-entry (zip-slip, symlinks, unexpected roots, size)
 *   before anything is read out of them, and restore never calls extractTo().
 * - Database credentials reach mysqldump/mysql through a 0600 option file, never argv.
 */
class BackupManager
{
    /** backup_YYYY_MM_DD_HHMMSS_<8 hex>.zip; the suffix-less form is what the old module produced. */
    public const FILENAME_PATTERN = '/^backup_\d{4}_\d{2}_\d{2}_\d{6}(?:_[a-f0-9]{8})?\.zip$/D';

    public const METADATA_ENTRY = 'backup_metadata.json';

    public const DATABASE_ENTRY = 'database/backup.sql';

    public const FORMAT_VERSION = 2;

    /** Top-level folders an archive may contain besides the two fixed entries above. */
    protected const ALLOWED_ROOTS = ['storage', 'uploads', 'app', 'config', 'routes'];

    protected const S_IFMT = 0170000;

    protected const S_IFLNK = 0120000;

    // ---------------------------------------------------------------- names & paths

    public function isValidFilename(mixed $filename): bool
    {
        return is_string($filename) && preg_match(self::FILENAME_PATTERN, $filename) === 1;
    }

    /** A fresh, unpredictable, pattern-conforming name. User input never contributes to it. */
    public function generateFilename(): string
    {
        do {
            $name = 'backup_'.now()->format('Y_m_d_His').'_'.bin2hex(random_bytes(4)).'.zip';
        } while (file_exists($this->directory().DIRECTORY_SEPARATOR.$name));

        return $name;
    }

    /**
     * Canonical absolute path of an existing archive, or an exception. The name must pass
     * the allow-list, must not be a symlink, and its realpath must sit directly in the
     * backup directory.
     */
    public function resolve(mixed $filename): string
    {
        if (! $this->isValidFilename($filename)) {
            throw new BackupException('Invalid backup file name.');
        }

        $directory = $this->directory();
        $candidate = $directory.DIRECTORY_SEPARATOR.$filename;

        if (is_link($candidate) || ! is_file($candidate)) {
            throw new BackupException('Backup file not found.');
        }

        $real = realpath($candidate);

        if ($real === false || dirname($real) !== $directory) {
            throw new BackupException('Backup file not found.');
        }

        return $real;
    }

    /** Canonical backup directory, created 0700 if missing and tightened to 0700 if looser. */
    public function directory(): string
    {
        $configured = rtrim((string) config('backup.directory'), DIRECTORY_SEPARATOR);

        if ($configured === '') {
            throw new BackupException('Backup directory is not configured.');
        }

        if (! is_dir($configured)) {
            File::makeDirectory($configured, 0700, true);
        }

        if ((fileperms($configured) & 0777) !== 0700) {
            @chmod($configured, 0700);
        }

        $real = realpath($configured);

        if ($real === false) {
            throw new BackupException('Backup directory is not available.');
        }

        return $real;
    }

    public function isConfigured(): bool
    {
        return strlen((string) config('backup.archive_password')) >= 32;
    }

    protected function archivePassword(): string
    {
        if (! $this->isConfigured()) {
            throw new BackupException('Backups are disabled until BACKUP_ARCHIVE_PASSWORD (at least 32 characters) is configured.');
        }

        return (string) config('backup.archive_password');
    }

    // ---------------------------------------------------------------- listing

    /**
     * Archives in the backup directory, newest first. Files whose names are not
     * allow-listed are ignored. Paths are deliberately not included.
     *
     * @return Collection<int, array{filename: string, size: int, created_at: Carbon, encrypted: bool, readable: bool, metadata: ?array}>
     */
    public function list(): Collection
    {
        return collect(File::files($this->directory()))
            ->filter(fn ($file) => ! $file->isLink() && $this->isValidFilename($file->getFilename()))
            ->map(fn ($file) => [
                'filename' => $file->getFilename(),
                'size' => $file->getSize(),
                'created_at' => Carbon::createFromTimestamp($file->getMTime()),
                ...$this->inspect($file->getPathname()),
            ])
            ->sortByDesc('created_at')
            ->values();
    }

    /** @return array{encrypted: bool, readable: bool, metadata: ?array} */
    protected function inspect(string $path): array
    {
        $zip = new ZipArchive;

        if ($zip->open($path, ZipArchive::RDONLY) !== true) {
            return ['encrypted' => false, 'readable' => false, 'metadata' => null];
        }

        try {
            $encrypted = $this->isFullyEncrypted($zip);

            if ($encrypted && $this->isConfigured()) {
                $zip->setPassword($this->archivePassword());
            }

            $json = $zip->getFromName(self::METADATA_ENTRY);
            $metadata = is_string($json) ? json_decode($json, true) : null;

            return ['encrypted' => $encrypted, 'readable' => true, 'metadata' => is_array($metadata) ? $metadata : null];
        } finally {
            $zip->close();
        }
    }

    /** True when the archive has entries and every file entry is AES-256 encrypted. */
    public function isFullyEncrypted(ZipArchive $zip): bool
    {
        $files = 0;

        for ($i = 0; $i < $zip->numFiles; $i++) {
            $stat = $zip->statIndex($i);

            if ($stat === false) {
                return false;
            }

            if (str_ends_with($stat['name'], '/')) {
                continue;
            }

            $files++;

            if (($stat['encryption_method'] ?? ZipArchive::EM_NONE) !== ZipArchive::EM_AES_256) {
                return false;
            }
        }

        return $files > 0;
    }

    // ---------------------------------------------------------------- create / delete

    /**
     * Builds a new encrypted archive and returns its server-generated file name.
     * The label and description are stored only inside the (encrypted) metadata.
     *
     * @param  array{database?: bool, uploads?: bool, files?: bool}  $include
     */
    public function create(?User $actor, array $include, ?string $label = null, ?string $description = null): string
    {
        $password = $this->archivePassword();
        $include = [
            'database' => (bool) ($include['database'] ?? false),
            'uploads' => (bool) ($include['uploads'] ?? false),
            'files' => (bool) ($include['files'] ?? false),
        ];

        $directory = $this->directory();
        $filename = $this->generateFilename();
        $final = $directory.DIRECTORY_SEPARATOR.$filename;
        $partial = $final.'.part';
        $workDir = $this->makeWorkDir();

        $zip = new ZipArchive;
        $opened = false;

        try {
            if ($zip->open($partial, ZipArchive::CREATE | ZipArchive::EXCL) !== true) {
                throw new BackupException('Could not create the backup archive.');
            }
            $opened = true;

            if ($include['database']) {
                $this->addFile($zip, $this->dumpDatabase($workDir), self::DATABASE_ENTRY, $password);
            }

            if ($include['uploads']) {
                $this->addDirectory($zip, storage_path('app/public'), 'storage', $password);
                $this->addDirectory($zip, public_path('uploads'), 'uploads', $password);
            }

            if ($include['files']) {
                foreach (['app', 'config', 'routes'] as $folder) {
                    $this->addDirectory($zip, base_path($folder), $folder, $password);
                }
            }

            $metadata = [
                'format_version' => self::FORMAT_VERSION,
                'label' => $label !== null && trim($label) !== '' ? trim($label) : null,
                'description' => $description !== null && trim($description) !== '' ? trim($description) : null,
                'created_at' => now()->toIso8601String(),
                'created_by' => $actor?->full_name,
                'created_by_id' => $actor?->getKey(),
                'includes' => $include,
                'encrypted' => true,
            ];

            if (! $zip->addFromString(self::METADATA_ENTRY, json_encode($metadata, JSON_PRETTY_PRINT | JSON_THROW_ON_ERROR))
                || ! $zip->setEncryptionName(self::METADATA_ENTRY, ZipArchive::EM_AES_256, $password)) {
                throw new BackupException('Could not write backup metadata.');
            }

            $opened = false;
            if (! $zip->close()) {
                throw new BackupException('Could not finalise the backup archive.');
            }

            @chmod($partial, 0600);

            if (! rename($partial, $final)) {
                throw new BackupException('Could not finalise the backup archive.');
            }
        } catch (Throwable $e) {
            if ($opened) {
                // Discard pending entries so close() (or the destructor) writes nothing
                $zip->unchangeAll();
                @$zip->close();
            }
            @unlink($partial);

            throw $e;
        } finally {
            File::deleteDirectory($workDir);
        }

        $this->log($actor, 'created', $filename, ['includes' => $include, 'label' => $metadata['label']]);

        return $filename;
    }

    public function delete(?User $actor, string $filename): void
    {
        $path = $this->resolve($filename);

        if (! @unlink($path)) {
            throw new BackupException('The backup could not be deleted.');
        }

        $this->log($actor, 'deleted', $filename);
    }

    /** Validated path for streaming a download; also records who downloaded it. */
    public function pathForDownload(?User $actor, string $filename): string
    {
        $path = $this->resolve($filename);
        $this->log($actor, 'downloaded', $filename);

        return $path;
    }

    // ---------------------------------------------------------------- restore & protect

    /**
     * Replaces the current database with the dump inside the archive. Console only.
     * The whole archive is validated first; only the single expected entry is read,
     * by name, into a private temp file. Nothing is extracted with extractTo().
     *
     * @return array{bytes: int, encrypted: bool}
     */
    public function restore(string $filename, bool $allowUnencrypted = false, array $context = []): array
    {
        $path = $this->resolve($filename);
        $zip = $this->openForReading($path);
        $workDir = null;

        try {
            $this->assertSafeArchive($zip);

            $encrypted = $this->isFullyEncrypted($zip);

            if (! $encrypted && ! $allowUnencrypted) {
                throw new BackupException('This archive is not encrypted (legacy format). Run `php artisan backup:protect` first, or pass --allow-unencrypted after verifying where it came from.');
            }

            $stat = $zip->statName(self::DATABASE_ENTRY);

            if ($stat === false) {
                throw new BackupException('The archive does not contain a database dump.');
            }

            if ($encrypted) {
                $zip->setPassword($this->archivePassword());
            }

            $databaseConfig = $this->databaseConfig();
            $workDir = $this->makeWorkDir();
            $sqlFile = $workDir.DIRECTORY_SEPARATOR.'restore.sql';
            $this->extractEntry($zip, self::DATABASE_ENTRY, $sqlFile, (int) $stat['size']);

            $this->importDatabase($sqlFile, $workDir, $databaseConfig);

            $this->log(null, 'restored', $filename, ['encrypted' => $encrypted] + $context);

            return ['bytes' => (int) $stat['size'], 'encrypted' => $encrypted];
        } finally {
            $zip->close();

            if ($workDir !== null) {
                File::deleteDirectory($workDir);
            }
        }
    }

    /**
     * Re-packs a legacy (unencrypted) archive as an AES-256 encrypted one in place,
     * after validating every entry. Returns false when it was already encrypted.
     */
    public function protect(string $filename): bool
    {
        $password = $this->archivePassword();
        $path = $this->resolve($filename);
        $source = $this->openForReading($path);

        try {
            $this->assertSafeArchive($source);

            if ($this->isFullyEncrypted($source)) {
                $source->close();
                @chmod($path, 0600);

                return false;
            }
        } catch (Throwable $e) {
            $source->close();

            throw $e;
        }

        $mtime = filemtime($path);
        $partial = $path.'.part';
        $workDir = $this->makeWorkDir();
        $target = new ZipArchive;
        $opened = false;

        try {
            if ($target->open($partial, ZipArchive::CREATE | ZipArchive::EXCL) !== true) {
                throw new BackupException('Could not create the protected archive.');
            }
            $opened = true;

            for ($i = 0; $i < $source->numFiles; $i++) {
                $stat = $source->statIndex($i);

                if (str_ends_with($stat['name'], '/')) {
                    continue;
                }

                // Temp files are named by index, so entry names never touch the filesystem
                $temp = $workDir.DIRECTORY_SEPARATOR.$i;
                $this->extractEntry($source, $stat['name'], $temp, (int) $stat['size']);
                $this->addFile($target, $temp, $stat['name'], $password);
            }

            $opened = false;
            if (! $target->close()) {
                throw new BackupException('Could not finalise the protected archive.');
            }

            $source->close();
            @chmod($partial, 0600);

            if (! rename($partial, $path)) {
                throw new BackupException('Could not replace the legacy archive.');
            }

            if ($mtime !== false) {
                @touch($path, $mtime); // keep its place in the history
            }
        } catch (Throwable $e) {
            if ($opened) {
                $target->unchangeAll();
                @$target->close();
            }
            @unlink($partial);
            @$source->close();

            throw $e;
        } finally {
            File::deleteDirectory($workDir);
        }

        $this->log(null, 'protected', $filename);

        return true;
    }

    // ---------------------------------------------------------------- archive validation

    /**
     * Rejects the archive unless every entry is safe: no traversal, absolute or drive
     * paths, backslashes or control characters, no symlinks, only expected top-level
     * folders, and bounded entry count and uncompressed size.
     */
    public function assertSafeArchive(ZipArchive $zip): void
    {
        $count = $zip->numFiles;

        if ($count < 1) {
            throw new BackupException('The archive is empty.');
        }

        if ($count > (int) config('backup.max_entries')) {
            throw new BackupException('The archive has too many entries.');
        }

        $total = 0;
        $max = (int) config('backup.max_uncompressed_bytes');

        for ($i = 0; $i < $count; $i++) {
            $stat = $zip->statIndex($i, ZipArchive::FL_UNCHANGED);

            if ($stat === false) {
                throw new BackupException('The archive contains an unreadable entry.');
            }

            $this->assertSafeEntryName($stat['name']);

            if ($zip->getExternalAttributesIndex($i, $opsys, $attributes, ZipArchive::FL_UNCHANGED)
                && $opsys === ZipArchive::OPSYS_UNIX
                && (($attributes >> 16) & self::S_IFMT) === self::S_IFLNK) {
                throw new BackupException('The archive contains a symbolic link: '.json_encode($stat['name']));
            }

            $total += (int) $stat['size'];

            if ($total > $max) {
                throw new BackupException('The archive expands beyond the allowed size.');
            }
        }
    }

    public function assertSafeEntryName(mixed $name): void
    {
        $reject = fn () => throw new BackupException('The archive contains an unsafe entry: '.json_encode($name));

        if (! is_string($name) || $name === '' || strlen($name) > 1024
            || str_starts_with($name, '/')
            || preg_match('/[\x00-\x1F\x7F\\\\:]/', $name)) {
            $reject();
        }

        if ($name === self::METADATA_ENTRY || $name === self::DATABASE_ENTRY) {
            return;
        }

        $isDirectory = str_ends_with($name, '/');
        $segments = explode('/', $isDirectory ? substr($name, 0, -1) : $name);

        foreach ($segments as $segment) {
            if ($segment === '' || $segment === '.' || $segment === '..') {
                $reject();
            }
        }

        if (! in_array($segments[0], self::ALLOWED_ROOTS, true) || (count($segments) < 2 && ! $isDirectory)) {
            $reject();
        }
    }

    // ---------------------------------------------------------------- internals

    protected function openForReading(string $path): ZipArchive
    {
        $zip = new ZipArchive;

        if ($zip->open($path, ZipArchive::RDONLY | ZipArchive::CHECKCONS) !== true) {
            throw new BackupException('The archive could not be opened or is damaged.');
        }

        return $zip;
    }

    /** Streams one entry, by exact name, to a new 0600 file and checks the full size arrived. */
    protected function extractEntry(ZipArchive $zip, string $entry, string $target, int $expectedSize): void
    {
        $in = $zip->getStream($entry);

        if ($in === false) {
            throw new BackupException('Could not read '.$entry.' from the archive (wrong BACKUP_ARCHIVE_PASSWORD or damaged archive).');
        }

        $out = fopen($target, 'xb');

        if ($out === false) {
            fclose($in);
            throw new BackupException('Could not create a temporary file for the restore.');
        }

        @chmod($target, 0600);

        try {
            $copied = stream_copy_to_stream($in, $out);
        } finally {
            fclose($in);
            fclose($out);
        }

        // AES entries carry an HMAC; a wrong key or tampering shows up as a short read
        if ($copied === false || $copied !== $expectedSize) {
            throw new BackupException('Could not read '.$entry.' from the archive (wrong BACKUP_ARCHIVE_PASSWORD, tampering or damage).');
        }
    }

    protected function addFile(ZipArchive $zip, string $path, string $entry, string $password): void
    {
        $this->assertSafeEntryName($entry);

        if (! $zip->addFile($path, $entry) || ! $zip->setEncryptionName($entry, ZipArchive::EM_AES_256, $password)) {
            throw new BackupException('Could not add '.$entry.' to the archive.');
        }
    }

    /** Adds regular files only; symlinks (files or folders) are skipped, never followed. */
    protected function addDirectory(ZipArchive $zip, string $source, string $prefix, string $password): void
    {
        $base = realpath($source);

        if ($base === false || ! is_dir($base)) {
            return;
        }

        $backupDirectory = $this->directory().DIRECTORY_SEPARATOR;
        $files = new RecursiveIteratorIterator(
            new RecursiveDirectoryIterator($base, FilesystemIterator::SKIP_DOTS),
            RecursiveIteratorIterator::LEAVES_ONLY
        );

        foreach ($files as $file) {
            if ($file->isLink() || ! $file->isFile() || str_starts_with($file->getPathname(), $backupDirectory)) {
                continue;
            }

            $relative = str_replace(DIRECTORY_SEPARATOR, '/', substr($file->getPathname(), strlen($base) + 1));
            $this->addFile($zip, $file->getPathname(), $prefix.'/'.$relative, $password);
        }
    }

    protected function dumpDatabase(string $workDir): string
    {
        $config = $this->databaseConfig();
        $target = $workDir.DIRECTORY_SEPARATOR.'database.sql';

        $result = Process::timeout((int) config('backup.process_timeout'))->run([
            (string) config('backup.binaries.mysqldump'),
            '--defaults-extra-file='.$this->writeClientOptionFile($workDir, $config),
            ...$this->connectionArguments($config),
            '--single-transaction',
            '--quick',
            '--routines',
            '--triggers',
            '--no-tablespaces',
            '--result-file='.$target,
            (string) $config['database'],
        ]);

        if (! $result->successful() || ! is_file($target)) {
            report(new RuntimeException('mysqldump failed: '.$result->errorOutput()));

            throw new BackupException('The database dump failed. See the application log for details.');
        }

        @chmod($target, 0600);

        return $target;
    }

    protected function importDatabase(string $sqlFile, string $workDir, array $config): void
    {
        $input = fopen($sqlFile, 'rb');

        try {
            $result = Process::timeout((int) config('backup.process_timeout'))
                ->input($input)
                ->run([
                    (string) config('backup.binaries.mysql'),
                    '--defaults-extra-file='.$this->writeClientOptionFile($workDir, $config),
                    ...$this->connectionArguments($config),
                    (string) $config['database'],
                ]);
        } finally {
            if (is_resource($input)) {
                fclose($input);
            }
        }

        if (! $result->successful()) {
            report(new RuntimeException('mysql restore failed: '.$result->errorOutput()));

            throw new BackupException('The database restore failed. See the application log for details.');
        }
    }

    /** @return array<string, mixed> */
    protected function databaseConfig(): array
    {
        $connection = (string) config('database.default');
        $config = (array) config("database.connections.{$connection}");

        if (! in_array($config['driver'] ?? null, ['mysql', 'mariadb'], true)) {
            throw new BackupException('Database backups support MySQL/MariaDB only (current connection: '.$connection.').');
        }

        return $config;
    }

    /** @return list<string> */
    protected function connectionArguments(array $config): array
    {
        $arguments = ['--user='.$config['username']];

        if (! empty($config['unix_socket'])) {
            $arguments[] = '--socket='.$config['unix_socket'];
        } else {
            $arguments[] = '--host='.$config['host'];
            $arguments[] = '--port='.($config['port'] ?? 3306);
        }

        return $arguments;
    }

    /** MySQL option file holding the password, so it never appears in argv or the environment. */
    protected function writeClientOptionFile(string $workDir, array $config): string
    {
        $path = $workDir.DIRECTORY_SEPARATOR.'client.cnf';

        if (! is_file($path)) {
            $handle = fopen($path, 'xb');
            @chmod($path, 0600);
            fwrite($handle, "[client]\npassword=\"".addcslashes((string) ($config['password'] ?? ''), '\\"')."\"\n");
            fclose($handle);
        }

        return $path;
    }

    protected function makeWorkDir(): string
    {
        $path = $this->directory().DIRECTORY_SEPARATOR.'.work_'.bin2hex(random_bytes(8));

        if (! mkdir($path, 0700)) {
            throw new BackupException('Could not create a private working directory.');
        }

        return $path;
    }

    protected function log(?User $actor, string $event, string $filename, array $properties = []): void
    {
        activity('backup')
            ->causedBy($actor)
            ->event($event)
            ->withProperties(['file' => $filename] + $properties)
            ->log("Backup {$event}: {$filename}");
    }
}
