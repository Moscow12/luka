<?php

namespace App\Livewire\Setup;

use Livewire\Component;
use Livewire\WithFileUploads;
use Livewire\WithPagination;
use Illuminate\Support\Facades\Artisan;
use Illuminate\Support\Facades\Storage;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\File;
use Carbon\Carbon;
use ZipArchive;

class Backuprecovery extends Component
{
    use WithFileUploads, WithPagination;

    // Backup Settings
    public $backup_name = '';
    public $backup_description = '';
    public $include_database = true;
    public $include_files = false;
    public $include_uploads = true;

    // Recovery
    public $restore_file;
    public $selectedBackup = null;
    public $showRestoreModal = false;
    public $restoreConfirmation = '';

    // View Settings
    public $showCreateModal = false;
    public $perPage = 10;

    protected $paginationTheme = 'bootstrap';

    protected $rules = [
        'backup_name' => 'required|string|max:255',
        'backup_description' => 'nullable|string|max:500',
    ];

    public function mount()
    {
        $this->ensureBackupDirectoryExists();
    }

    public function openCreateModal()
    {
        $this->reset(['backup_name', 'backup_description', 'include_database', 'include_files', 'include_uploads']);
        $this->include_database = true;
        $this->include_uploads = true;
        $this->backup_name = 'backup_' . now()->format('Y_m_d_His');
        $this->showCreateModal = true;
    }

    public function closeCreateModal()
    {
        $this->showCreateModal = false;
        $this->reset(['backup_name', 'backup_description']);
    }

    public function createBackup()
    {
        $this->validate();

        try {
            $backupPath = storage_path('app/backups');
            $this->ensureBackupDirectoryExists();

            $timestamp = now()->format('Y_m_d_His');
            $backupName = $this->backup_name ?: "backup_{$timestamp}";
            $zipFileName = "{$backupName}.zip";
            $zipFilePath = "{$backupPath}/{$zipFileName}";

            $zip = new ZipArchive();
            if ($zip->open($zipFilePath, ZipArchive::CREATE | ZipArchive::OVERWRITE) !== true) {
                throw new \Exception('Could not create backup archive');
            }

            // Backup Database
            if ($this->include_database) {
                $this->backupDatabase($zip, $timestamp);
            }

            // Backup Uploads
            if ($this->include_uploads) {
                $this->backupDirectory($zip, storage_path('app/public'), 'storage');
                $this->backupDirectory($zip, public_path('uploads'), 'uploads');
            }

            // Backup Files (application files)
            if ($this->include_files) {
                $this->backupDirectory($zip, base_path('app'), 'app');
                $this->backupDirectory($zip, base_path('config'), 'config');
                $this->backupDirectory($zip, base_path('routes'), 'routes');
            }

            // Add metadata
            $metadata = [
                'name' => $backupName,
                'description' => $this->backup_description,
                'created_at' => now()->toDateTimeString(),
                'created_by' => auth()->user()->name,
                'includes' => [
                    'database' => $this->include_database,
                    'files' => $this->include_files,
                    'uploads' => $this->include_uploads,
                ],
            ];
            $zip->addFromString('backup_metadata.json', json_encode($metadata, JSON_PRETTY_PRINT));

            $zip->close();

            activity()
                ->causedBy(auth()->user())
                ->log("Created system backup: {$backupName}");

            session()->flash('success', "Backup created successfully: {$backupName}");
            $this->closeCreateModal();
            $this->resetPage();
        } catch (\Exception $e) {
            session()->flash('error', 'Backup failed: ' . $e->getMessage());
        }
    }

    protected function backupDatabase($zip, $timestamp)
    {
        $database = DB::connection()->getDatabaseName();
        $username = config('database.connections.mysql.username');
        $password = config('database.connections.mysql.password');
        $host = config('database.connections.mysql.host');

        $sqlFile = storage_path("app/backups/temp_db_{$timestamp}.sql");

        $command = sprintf(
            'mysqldump -h %s -u %s -p%s %s > %s 2>&1',
            escapeshellarg($host),
            escapeshellarg($username),
            escapeshellarg($password),
            escapeshellarg($database),
            escapeshellarg($sqlFile)
        );

        exec($command, $output, $returnVar);

        if ($returnVar === 0 && file_exists($sqlFile)) {
            $zip->addFile($sqlFile, 'database/backup.sql');
            // Note: We'll delete the temp file after zip is closed
            register_shutdown_function(function () use ($sqlFile) {
                if (file_exists($sqlFile)) {
                    @unlink($sqlFile);
                }
            });
        } else {
            throw new \Exception('Database backup failed');
        }
    }

    protected function backupDirectory($zip, $source, $destination = '')
    {
        if (!file_exists($source)) {
            return;
        }

        $files = new \RecursiveIteratorIterator(
            new \RecursiveDirectoryIterator($source),
            \RecursiveIteratorIterator::LEAVES_ONLY
        );

        foreach ($files as $file) {
            if (!$file->isDir()) {
                $filePath = $file->getRealPath();
                $relativePath = $destination . '/' . substr($filePath, strlen($source) + 1);
                $zip->addFile($filePath, $relativePath);
            }
        }
    }

    protected function ensureBackupDirectoryExists()
    {
        $backupPath = storage_path('app/backups');
        if (!File::exists($backupPath)) {
            File::makeDirectory($backupPath, 0755, true);
        }
    }

    public function getBackupsProperty()
    {
        $backupPath = storage_path('app/backups');
        $this->ensureBackupDirectoryExists();

        $backups = collect(File::files($backupPath))
            ->filter(fn($file) => $file->getExtension() === 'zip')
            ->map(function ($file) {
                $metadata = $this->getBackupMetadata($file->getPathname());
                return [
                    'filename' => $file->getFilename(),
                    'path' => $file->getPathname(),
                    'size' => $file->getSize(),
                    'created_at' => Carbon::createFromTimestamp($file->getMTime()),
                    'metadata' => $metadata,
                ];
            })
            ->sortByDesc('created_at')
            ->values();

        return $backups;
    }

    protected function getBackupMetadata($zipPath)
    {
        $zip = new ZipArchive();
        if ($zip->open($zipPath) === true) {
            $metadata = $zip->getFromName('backup_metadata.json');
            $zip->close();
            return $metadata ? json_decode($metadata, true) : null;
        }
        return null;
    }

    public function downloadBackup($filename)
    {
        $path = storage_path("app/backups/{$filename}");

        if (!file_exists($path)) {
            session()->flash('error', 'Backup file not found');
            return;
        }

        activity()
            ->causedBy(auth()->user())
            ->log("Downloaded backup: {$filename}");

        return response()->download($path);
    }

    public function deleteBackup($filename)
    {
        $path = storage_path("app/backups/{$filename}");

        if (file_exists($path)) {
            unlink($path);

            activity()
                ->causedBy(auth()->user())
                ->log("Deleted backup: {$filename}");

            session()->flash('success', 'Backup deleted successfully');
            $this->resetPage();
        } else {
            session()->flash('error', 'Backup file not found');
        }
    }

    public function openRestoreModal($filename)
    {
        $this->selectedBackup = $filename;
        $this->restoreConfirmation = '';
        $this->showRestoreModal = true;
    }

    public function closeRestoreModal()
    {
        $this->showRestoreModal = false;
        $this->selectedBackup = null;
        $this->restoreConfirmation = '';
    }

    public function restoreBackup()
    {
        if ($this->restoreConfirmation !== 'RESTORE') {
            session()->flash('error', 'Please type RESTORE to confirm');
            return;
        }

        try {
            $path = storage_path("app/backups/{$this->selectedBackup}");

            if (!file_exists($path)) {
                throw new \Exception('Backup file not found');
            }

            $zip = new ZipArchive();
            if ($zip->open($path) !== true) {
                throw new \Exception('Could not open backup archive');
            }

            $extractPath = storage_path('app/backups/temp_restore_' . time());
            File::makeDirectory($extractPath, 0755, true);

            $zip->extractTo($extractPath);
            $zip->close();

            // Restore Database
            $sqlFile = "{$extractPath}/database/backup.sql";
            if (file_exists($sqlFile)) {
                $this->restoreDatabase($sqlFile);
            }

            // Restore Files (if needed)
            // Note: Be very careful with file restoration in production

            // Clean up
            File::deleteDirectory($extractPath);

            activity()
                ->causedBy(auth()->user())
                ->log("Restored backup: {$this->selectedBackup}");

            session()->flash('success', 'Backup restored successfully');
            $this->closeRestoreModal();
        } catch (\Exception $e) {
            session()->flash('error', 'Restore failed: ' . $e->getMessage());
        }
    }

    protected function restoreDatabase($sqlFile)
    {
        $database = DB::connection()->getDatabaseName();
        $username = config('database.connections.mysql.username');
        $password = config('database.connections.mysql.password');
        $host = config('database.connections.mysql.host');

        $command = sprintf(
            'mysql -h %s -u %s -p%s %s < %s 2>&1',
            escapeshellarg($host),
            escapeshellarg($username),
            escapeshellarg($password),
            escapeshellarg($database),
            escapeshellarg($sqlFile)
        );

        exec($command, $output, $returnVar);

        if ($returnVar !== 0) {
            throw new \Exception('Database restore failed');
        }
    }

    public function render()
    {
        return view('livewire.setup.backuprecovery', [
            'backups' => $this->backups,
        ]);
    }
}
