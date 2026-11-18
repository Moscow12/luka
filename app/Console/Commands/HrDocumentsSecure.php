<?php

namespace App\Console\Commands;

use App\Services\HrDocuments\HrDocumentStore;
use App\Services\HrDocuments\HrDocumentType;
use App\Support\EncryptedFileStore;
use finfo;
use Illuminate\Console\Command;
use Illuminate\Support\Facades\File;
use Illuminate\Support\Facades\Storage;
use Illuminate\Support\Str;
use RecursiveDirectoryIterator;
use RecursiveIteratorIterator;

/**
 * Reviews files already stored on the public disk and, with --apply:
 *  - moves every referenced HR document whose content is allow-listed into encrypted
 *    private storage, repoints the record, and removes the public copy;
 *  - quarantines referenced HR files whose content is NOT allow-listed;
 *  - quarantines unreferenced files left in the old HR folders;
 *  - quarantines dangerous files (PHP, HTML, SVG, scripts) anywhere on the public disk.
 * Quarantine is storage/app/quarantine (private, 0700); nothing is deleted outright.
 */
class HrDocumentsSecure extends Command
{
    protected $signature = 'hr-documents:secure {--apply : Make the changes (default only reports)}';

    protected $description = 'Review stored files: encrypt HR documents into private storage and quarantine unsafe or orphaned public files';

    protected const DANGEROUS_EXTENSIONS = [
        'php', 'php3', 'php4', 'php5', 'php7', 'php8', 'phtml', 'pht', 'phar', 'phps',
        'html', 'htm', 'xhtml', 'shtml', 'svg', 'svgz', 'js', 'mjs', 'xml', 'xsl', 'swf',
        'htaccess', 'cgi', 'pl', 'py', 'sh', 'asp', 'aspx', 'jsp',
    ];

    protected const DANGEROUS_MIMES = [
        'text/x-php', 'application/x-php', 'application/x-httpd-php', 'text/html',
        'image/svg+xml', 'application/xhtml+xml', 'text/xml', 'application/xml',
        'application/javascript', 'text/javascript', 'application/x-sh', 'text/x-shellscript',
    ];

    protected bool $apply = false;

    protected array $counts = [];

    protected array $findings = [];

    public function handle(HrDocumentStore $store, EncryptedFileStore $files): int
    {
        $this->apply = (bool) $this->option('apply');
        $this->info($this->apply ? 'Applying changes.' : 'Report only. Re-run with --apply to make these changes.');

        $referenced = [];

        foreach (HrDocumentType::cases() as $type) {
            $this->reviewType($type, $store, $files, $referenced);
        }

        $this->reviewOrphans($referenced);
        $this->reviewDangerous();

        $this->newLine();
        $this->table(['Outcome', 'Files'], collect($this->counts)->map(fn ($n, $k) => [$k, $n])->values()->all());

        if ($this->findings !== []) {
            $this->newLine();
            $this->warn('Needs attention:');
            $this->table(['Issue', 'Location', 'Detail'], $this->findings);
        }

        return self::SUCCESS;
    }

    /** @param array<string, true> $referenced public-disk paths still referenced by records */
    protected function reviewType(HrDocumentType $type, HrDocumentStore $store, EncryptedFileStore $files, array &$referenced): void
    {
        $model = $type->model();
        $column = $type->column();

        $model::query()->whereNotNull($column)->where($column, '!=', '')->select(['id', $column])
            ->chunkById(200, function ($records) use ($type, $store, $files, $column, &$referenced) {
                foreach ($records as $record) {
                    $path = $record->{$column};

                    if ($store->isEncryptedPath($type, $path)) {
                        Storage::disk(EncryptedFileStore::DISK)->exists($path)
                            ? $this->count('already encrypted')
                            : $this->finding('missing encrypted file', "{$type->value} #{$record->id}", $path);

                        continue;
                    }

                    $absolute = $store->legacyAbsolutePath($type, $path);

                    if (! $absolute) {
                        $this->finding('unresolvable path (left as is)', "{$type->value} #{$record->id}", Str::limit((string) $path, 80));

                        continue;
                    }

                    $referenced[$path] = true;
                    $mime = (new finfo(FILEINFO_MIME_TYPE))->file($absolute) ?: 'unknown';

                    if (! in_array($mime, $type->mimeTypes(), true)) {
                        $this->finding('disallowed content in HR record', "{$type->value} #{$record->id}", "{$path} ({$mime})");

                        if ($this->apply) {
                            $this->quarantine($absolute, 'hr-documents/'.$type->value);
                            $record->newQuery()->whereKey($record->getKey())->update([$column => null]);
                        }
                        $this->count($this->apply ? 'quarantined (disallowed content)' : 'would quarantine (disallowed content)');

                        continue;
                    }

                    if ($this->apply) {
                        $newPath = $files->putContents((string) file_get_contents($absolute), $type->directory());
                        // Query builder: a storage move is not a business change, so no activity-log noise
                        $record->newQuery()->whereKey($record->getKey())->update([$column => $newPath]);
                        @unlink($absolute);
                    }
                    $this->count($this->apply ? 'encrypted & moved to private storage' : 'would encrypt & move to private storage');
                }
            });
    }

    /** Files sitting directly in an old HR folder that no record points at. */
    protected function reviewOrphans(array $referenced): void
    {
        $directories = collect(HrDocumentType::cases())->flatMap->legacyDirectories()->unique();

        foreach ($directories as $directory) {
            foreach (Storage::disk('public')->files($directory) as $path) {
                if (isset($referenced[$path]) || basename($path) === '.gitignore') {
                    continue;
                }

                if ($this->apply) {
                    $this->quarantine(Storage::disk('public')->path($path), 'orphans/'.$directory);
                }
                $this->count($this->apply ? 'quarantined (unreferenced HR file)' : 'would quarantine (unreferenced HR file)');
            }
        }
    }

    /** Anything executable or script-capable anywhere on the public disk. */
    protected function reviewDangerous(): void
    {
        $root = Storage::disk('public')->path('');

        if (! is_dir($root)) {
            return;
        }

        $iterator = new RecursiveIteratorIterator(new RecursiveDirectoryIterator($root, RecursiveDirectoryIterator::SKIP_DOTS));
        $finfo = new finfo(FILEINFO_MIME_TYPE);

        foreach ($iterator as $file) {
            if (! $file->isFile() || $file->getFilename() === '.gitignore') {
                continue;
            }

            $extension = strtolower($file->getExtension());
            $mime = $finfo->file($file->getPathname()) ?: '';

            if (! in_array($extension, self::DANGEROUS_EXTENSIONS, true) && ! in_array($mime, self::DANGEROUS_MIMES, true)) {
                continue;
            }

            $relative = ltrim(Str::after($file->getPathname(), $root), '/');
            $this->finding('dangerous file on public disk', $relative, $mime);

            if ($this->apply) {
                $this->quarantine($file->getPathname(), 'dangerous/'.dirname($relative));
            }
            $this->count($this->apply ? 'quarantined (dangerous type)' : 'would quarantine (dangerous type)');
        }
    }

    protected function quarantine(string $absolute, string $folder): void
    {
        $base = storage_path('app/quarantine');
        File::ensureDirectoryExists($base, 0700);
        @chmod($base, 0700);

        $target = $base.'/'.trim($folder, '/');
        File::ensureDirectoryExists($target, 0700);

        // Neutral name: never keeps an executable extension
        $destination = $target.'/'.basename($absolute).'.'.Str::random(6).'.quarantined';

        if (@rename($absolute, $destination)) {
            @chmod($destination, 0600);
        } else {
            $this->finding('could not quarantine', $absolute, 'check file permissions');
        }
    }

    protected function count(string $outcome): void
    {
        $this->counts[$outcome] = ($this->counts[$outcome] ?? 0) + 1;
    }

    protected function finding(string $issue, string $where, string $detail): void
    {
        $this->findings[] = [$issue, $where, $detail];
    }
}
