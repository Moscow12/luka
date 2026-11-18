<div class="custom-container">

    <x-pages.breadcrumn title="BACKUP & RECOVERY" :breadcrumbs="[
        ['label' => 'Home', 'url' => route('dashboard')],
        ['label' => 'Setup', 'url' => '#'],
        ['label' => 'Backup & Recovery', 'url' => '#'],
    ]">
    </x-pages.breadcrumn>

    @unless($archivePasswordConfigured)
        <div class="alert alert-danger d-flex align-items-start mb-4">
            <i class="fa-solid fa-lock-open me-2 mt-1"></i>
            <div>
                <strong>Backups are disabled.</strong> Set <code>BACKUP_ARCHIVE_PASSWORD</code> (at least 32 characters) in the server
                environment so archives can be encrypted, then run <code>php artisan backup:protect</code> to encrypt existing archives.
            </div>
        </div>
    @endunless

    <!-- Summary Cards -->
    <div class="row g-3 mb-4">
        <div class="col-12 col-sm-6 col-xl-3">
            <div class="card border-0 shadow-sm">
                <div class="card-body">
                    <div class="d-flex align-items-center">
                        <div class="flex-shrink-0">
                            <div class="avatar avatar-lg bg-primary-subtle text-primary rounded">
                                <i class="fa-solid fa-database fs-4"></i>
                            </div>
                        </div>
                        <div class="flex-grow-1 ms-3">
                            <p class="text-muted mb-1 small">Total Backups</p>
                            <h4 class="mb-0 fw-bold">{{ number_format($backups->count()) }}</h4>
                        </div>
                    </div>
                </div>
            </div>
        </div>

        <div class="col-12 col-sm-6 col-xl-3">
            <div class="card border-0 shadow-sm">
                <div class="card-body">
                    <div class="d-flex align-items-center">
                        <div class="flex-shrink-0">
                            <div class="avatar avatar-lg bg-success-subtle text-success rounded">
                                <i class="fa-solid fa-hard-drive fs-4"></i>
                            </div>
                        </div>
                        <div class="flex-grow-1 ms-3">
                            <p class="text-muted mb-1 small">Total Size</p>
                            <h4 class="mb-0 fw-bold text-success">{{ \Illuminate\Support\Number::fileSize($backups->sum('size')) }}</h4>
                        </div>
                    </div>
                </div>
            </div>
        </div>

        <div class="col-12 col-sm-6 col-xl-3">
            <div class="card border-0 shadow-sm">
                <div class="card-body">
                    <div class="d-flex align-items-center">
                        <div class="flex-shrink-0">
                            <div class="avatar avatar-lg bg-info-subtle text-info rounded">
                                <i class="fa-solid fa-clock fs-4"></i>
                            </div>
                        </div>
                        <div class="flex-grow-1 ms-3">
                            <p class="text-muted mb-1 small">Latest Backup</p>
                            <h4 class="mb-0 fw-bold text-info small">
                                @if($backups->count() > 0)
                                    {{ $backups->first()['created_at']->diffForHumans() }}
                                @else
                                    Never
                                @endif
                            </h4>
                        </div>
                    </div>
                </div>
            </div>
        </div>

        <div class="col-12 col-sm-6 col-xl-3">
            <div class="card border-0 shadow-sm">
                <div class="card-body">
                    <div class="d-flex align-items-center">
                        <div class="flex-shrink-0">
                            <div class="avatar avatar-lg bg-warning-subtle text-warning rounded">
                                <i class="fa-solid fa-shield-halved fs-4"></i>
                            </div>
                        </div>
                        <div class="flex-grow-1 ms-3">
                            <p class="text-muted mb-1 small">Backup Status</p>
                            <h4 class="mb-0 fw-bold text-warning small">
                                @if($backups->isEmpty())
                                    At Risk
                                @elseif($backups->contains('encrypted', false))
                                    {{ $backups->where('encrypted', false)->count() }} unencrypted
                                @else
                                    Encrypted
                                @endif
                            </h4>
                        </div>
                    </div>
                </div>
            </div>
        </div>
    </div>

    <!-- Main Card -->
    <div class="row">
        <div class="col-12">
            <div class="card card-lg border-0 shadow-sm">
                <!-- Card Header -->
                <div class="card-header border-bottom bg-white">
                    <div class="row align-items-center">
                        <div class="col">
                            <h5 class="mb-0">
                                <i class="fa-solid fa-list me-2"></i>Backup History
                            </h5>
                        </div>
                        <div class="col-auto">
                            <button wire:click="openCreateModal" class="btn btn-primary" @disabled(! $archivePasswordConfigured)>
                                <i class="fa-solid fa-plus me-1"></i>Create New Backup
                            </button>
                        </div>
                    </div>
                </div>

                <!-- Table Section -->
                <div class="table-responsive" style="min-height: 400px;">
                    <table class="table table-hover table-centered align-middle mb-0">
                        <thead class="table-light">
                            <tr>
                                <th class="text-center" style="width: 50px;">#</th>
                                <th>Backup Name</th>
                                <th>Description</th>
                                <th style="width: 150px;">Date Created</th>
                                <th style="width: 120px;">Size</th>
                                <th style="width: 150px;">Includes</th>
                                <th style="width: 120px;">Created By</th>
                                <th class="text-center" style="width: 180px;">Actions</th>
                            </tr>
                        </thead>
                        <tbody>
                            @forelse($backups as $index => $backup)
                                <tr wire:key="backup-{{ $backup['filename'] }}">
                                    <td class="text-center text-muted">
                                        {{ $index + 1 }}
                                    </td>
                                    <td>
                                        <div class="d-flex flex-column">
                                            <span class="fw-semibold text-dark">
                                                <i class="fa-solid fa-file-zipper text-primary me-1"></i>
                                                {{ $backup['metadata']['label'] ?? $backup['metadata']['name'] ?? basename($backup['filename'], '.zip') }}
                                            </span>
                                            <small class="text-muted">{{ $backup['filename'] }}</small>
                                            @if($backup['encrypted'])
                                                <span class="badge bg-success-subtle text-success mt-1 align-self-start"><i class="fa-solid fa-lock me-1"></i>AES-256 encrypted</span>
                                            @else
                                                <span class="badge bg-danger-subtle text-danger mt-1 align-self-start" title="Run php artisan backup:protect"><i class="fa-solid fa-lock-open me-1"></i>Unencrypted (legacy)</span>
                                            @endif
                                        </div>
                                    </td>
                                    <td>
                                        <span class="text-muted small">
                                            {{ $backup['metadata']['description'] ?? 'No description' }}
                                        </span>
                                    </td>
                                    <td>
                                        <div class="d-flex flex-column">
                                            <span class="fw-semibold text-dark small">
                                                {{ $backup['created_at']->format('M d, Y') }}
                                            </span>
                                            <small class="text-muted">
                                                {{ $backup['created_at']->format('h:i A') }}
                                            </small>
                                        </div>
                                    </td>
                                    <td>
                                        <span class="badge bg-light text-dark">
                                            {{ \Illuminate\Support\Number::fileSize($backup['size']) }}
                                        </span>
                                    </td>
                                    <td>
                                        <div class="d-flex flex-wrap gap-1">
                                            @if($backup['metadata']['includes']['database'] ?? false)
                                                <span class="badge bg-primary" title="Database">
                                                    <i class="fa-solid fa-database"></i>
                                                </span>
                                            @endif
                                            @if($backup['metadata']['includes']['uploads'] ?? false)
                                                <span class="badge bg-success" title="Uploads">
                                                    <i class="fa-solid fa-cloud-arrow-up"></i>
                                                </span>
                                            @endif
                                            @if($backup['metadata']['includes']['files'] ?? false)
                                                <span class="badge bg-info" title="Files">
                                                    <i class="fa-solid fa-file-code"></i>
                                                </span>
                                            @endif
                                        </div>
                                    </td>
                                    <td>
                                        <span class="text-muted small">
                                            {{ $backup['metadata']['created_by'] ?? 'System' }}
                                        </span>
                                    </td>
                                    <td>
                                        <div class="d-flex gap-1 justify-content-center">
                                            <button
                                                wire:click="downloadBackup({{ Js::from($backup['filename']) }})"
                                                class="btn btn-sm btn-success"
                                                title="Download (requires password confirmation)">
                                                <i class="fa-solid fa-download"></i>
                                            </button>
                                            <button
                                                wire:click="deleteBackup({{ Js::from($backup['filename']) }})"
                                                wire:confirm="Are you sure you want to delete this backup?"
                                                class="btn btn-sm btn-danger"
                                                title="Delete">
                                                <i class="fa-solid fa-trash"></i>
                                            </button>
                                        </div>
                                    </td>
                                </tr>
                            @empty
                                <tr>
                                    <td colspan="8" class="text-center py-5">
                                        <div class="d-flex flex-column align-items-center justify-content-center">
                                            <i class="fa-solid fa-database text-muted mb-3" style="font-size: 48px;"></i>
                                            <h5 class="text-muted">No Backups Found</h5>
                                            <p class="text-muted">Create your first backup to secure your system data</p>
                                            <button wire:click="openCreateModal" class="btn btn-primary mt-2" @disabled(! $archivePasswordConfigured)>
                                                <i class="fa-solid fa-plus me-1"></i>Create Backup
                                            </button>
                                        </div>
                                    </td>
                                </tr>
                            @endforelse
                        </tbody>
                    </table>
                </div>

                <!-- Card Footer -->
                <div class="card-footer border-top bg-light">
                    <div class="d-flex align-items-start small text-muted">
                        <i class="fa-solid fa-terminal me-2 mt-1"></i>
                        <div>
                            <strong class="text-dark">Restoring</strong> is done from the server console by an administrator with shell access, which
                            validates the archive and takes a safety backup first:
                            <code class="d-block mt-1">php artisan backup:restore &lt;backup file name&gt;</code>
                        </div>
                    </div>
                </div>
                @if($backups->count() > 0)
                <div class="card-footer border-top bg-white">
                    <div class="d-flex justify-content-between align-items-center">
                        <div class="text-muted">
                            Total {{ $backups->count() }} backup(s) found
                        </div>
                        <div class="text-muted small">
                            <i class="fa-solid fa-info-circle me-1"></i>
                            Backups are stored in: <code>storage/app/backups</code>
                        </div>
                    </div>
                </div>
                @endif
            </div>
        </div>
    </div>

    <!-- Create Backup Modal -->
    @if($showCreateModal)
        <div class="modal fade show d-block" tabindex="-1" style="background-color: rgba(0,0,0,0.5);">
            <div class="modal-dialog modal-lg">
                <div class="modal-content">
                    <div class="modal-header bg-primary text-white">
                        <h5 class="modal-title">
                            <i class="fa-solid fa-plus-circle me-2"></i>Create New Backup
                        </h5>
                        <button type="button" class="btn-close btn-close-white" wire:click="closeCreateModal"></button>
                    </div>
                    <div class="modal-body">
                        <form wire:submit.prevent="createBackup">
                            <div class="row g-3">
                                <!-- Backup Name -->
                                <div class="col-12">
                                    <label class="form-label">Label</label>
                                    <input type="text" wire:model="backup_label" maxlength="100" class="form-control @error('backup_label') is-invalid @enderror" placeholder="Optional, e.g. Before payroll run">
                                    @error('backup_label')
                                        <div class="invalid-feedback">{{ $message }}</div>
                                    @enderror
                                    <div class="form-text">The file name is generated automatically. The label and description are stored encrypted inside the archive.</div>
                                </div>

                                <!-- Description -->
                                <div class="col-12">
                                    <label class="form-label">Description</label>
                                    <textarea wire:model="backup_description" class="form-control" rows="3" placeholder="Enter backup description (optional)"></textarea>
                                    @error('backup_description')
                                        <div class="text-danger small">{{ $message }}</div>
                                    @enderror
                                </div>

                                <!-- Backup Options -->
                                <div class="col-12">
                                    <label class="form-label d-block mb-3">What to Include</label>
                                    @error('include_database')
                                        <div class="text-danger small mb-2">{{ $message }}</div>
                                    @enderror

                                    <div class="card mb-2">
                                        <div class="card-body">
                                            <div class="form-check form-switch">
                                                <input class="form-check-input" type="checkbox" wire:model="include_database" id="include_database">
                                                <label class="form-check-label" for="include_database">
                                                    <i class="fa-solid fa-database text-primary me-2"></i>
                                                    <strong>Database</strong>
                                                    <p class="mb-0 text-muted small">Include complete database backup (tables, data, structure)</p>
                                                </label>
                                            </div>
                                        </div>
                                    </div>

                                    <div class="card mb-2">
                                        <div class="card-body">
                                            <div class="form-check form-switch">
                                                <input class="form-check-input" type="checkbox" wire:model="include_uploads" id="include_uploads">
                                                <label class="form-check-label" for="include_uploads">
                                                    <i class="fa-solid fa-cloud-arrow-up text-success me-2"></i>
                                                    <strong>Uploads & Storage</strong>
                                                    <p class="mb-0 text-muted small">Include user uploads, images, documents, and storage files</p>
                                                </label>
                                            </div>
                                        </div>
                                    </div>

                                    <div class="card">
                                        <div class="card-body">
                                            <div class="form-check form-switch">
                                                <input class="form-check-input" type="checkbox" wire:model="include_files" id="include_files">
                                                <label class="form-check-label" for="include_files">
                                                    <i class="fa-solid fa-file-code text-info me-2"></i>
                                                    <strong>Application Files</strong>
                                                    <p class="mb-0 text-muted small">Include app code, config, and routes (Not recommended for large systems)</p>
                                                </label>
                                            </div>
                                        </div>
                                    </div>
                                </div>

                                <!-- Warning -->
                                <div class="col-12">
                                    <div class="alert alert-warning mb-0">
                                        <i class="fa-solid fa-triangle-exclamation me-2"></i>
                                        <strong>Important:</strong> Large backups may take several minutes to complete. Please do not close this window during the backup process.
                                    </div>
                                </div>
                            </div>
                        </form>
                    </div>
                    <div class="modal-footer">
                        <button type="button" class="btn btn-secondary" wire:click="closeCreateModal">
                            Cancel
                        </button>
                        <button type="button" class="btn btn-primary" wire:click="createBackup" wire:loading.attr="disabled">
                            <span wire:loading.remove wire:target="createBackup">
                                <i class="fa-solid fa-plus me-1"></i>Create Backup
                            </span>
                            <span wire:loading wire:target="createBackup">
                                <i class="fa-solid fa-spinner fa-spin me-1"></i>Creating...
                            </span>
                        </button>
                    </div>
                </div>
            </div>
        </div>
    @endif

    <!-- Password Confirmation Modal -->
    @if($showConfirmModal)
        <div class="modal fade show d-block" tabindex="-1" style="background-color: rgba(0,0,0,0.5);">
            <div class="modal-dialog">
                <div class="modal-content">
                    <form wire:submit="confirmPasswordAndContinue">
                        <div class="modal-header bg-warning">
                            <h5 class="modal-title">
                                <i class="fa-solid fa-user-shield me-2"></i>Confirm your password
                            </h5>
                            <button type="button" class="btn-close" wire:click="cancelConfirmation"></button>
                        </div>
                        <div class="modal-body">
                            <p class="text-muted">
                                Backups contain the full database. Re-enter your password to
                                {{ match ($pendingAction) { 'create' => 'create a backup', 'download' => 'download '.$pendingFilename, 'delete' => 'delete '.$pendingFilename, default => 'continue' } }}.
                            </p>
                            <input type="password" wire:model="confirmPassword" autocomplete="current-password" autofocus
                                   class="form-control @error('confirmPassword') is-invalid @enderror" placeholder="Your password">
                            @error('confirmPassword')
                                <div class="invalid-feedback">{{ $message }}</div>
                            @enderror
                        </div>
                        <div class="modal-footer">
                            <button type="button" class="btn btn-secondary" wire:click="cancelConfirmation">Cancel</button>
                            <button type="submit" class="btn btn-warning" wire:loading.attr="disabled">
                                <i class="fa-solid fa-check me-1"></i>Confirm
                            </button>
                        </div>
                    </form>
                </div>
            </div>
        </div>
    @endif

    <!-- Loading Indicator -->
    <div wire:loading wire:target="createBackup,confirmPasswordAndContinue" class="position-fixed top-50 start-50 translate-middle" style="z-index: 9999;">
        <div class="card shadow-lg">
            <div class="card-body text-center">
                <div class="spinner-border text-primary mb-3" role="status">
                    <span class="visually-hidden">Loading...</span>
                </div>
                <h5>Processing...</h5>
                <p class="mb-0 text-muted">Please wait, this may take a few minutes</p>
            </div>
        </div>
    </div>

    <style>
        .modal.show {
            display: block;
        }

        .avatar {
            width: 48px;
            height: 48px;
            display: flex;
            align-items: center;
            justify-content: center;
        }

        .avatar-lg {
            width: 56px;
            height: 56px;
        }

        .form-check-label {
            cursor: pointer;
            width: 100%;
        }
    </style>
</div>
