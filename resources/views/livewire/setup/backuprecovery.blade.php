<div class="custom-container">

    <x-pages.breadcrumn title="BACKUP & RECOVERY" :breadcrumbs="[
        ['label' => 'Home', 'url' => route('dashboard')],
        ['label' => 'Setup', 'url' => '#'],
        ['label' => 'Backup & Recovery', 'url' => '#'],
    ]">
    </x-pages.breadcrumn>

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
                                @if($backups->count() > 0)
                                    Protected
                                @else
                                    At Risk
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
                            <button wire:click="openCreateModal" class="btn btn-primary">
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
                                <tr wire:key="backup-{{ $index }}">
                                    <td class="text-center text-muted">
                                        {{ $index + 1 }}
                                    </td>
                                    <td>
                                        <div class="d-flex flex-column">
                                            <span class="fw-semibold text-dark">
                                                <i class="fa-solid fa-file-zipper text-primary me-1"></i>
                                                {{ $backup['metadata']['name'] ?? basename($backup['filename'], '.zip') }}
                                            </span>
                                            <small class="text-muted">{{ $backup['filename'] }}</small>
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
                                                wire:click="downloadBackup('{{ $backup['filename'] }}')"
                                                class="btn btn-sm btn-success"
                                                title="Download">
                                                <i class="fa-solid fa-download"></i>
                                            </button>
                                            <button
                                                wire:click="openRestoreModal('{{ $backup['filename'] }}')"
                                                class="btn btn-sm btn-warning"
                                                title="Restore">
                                                <i class="fa-solid fa-rotate-left"></i>
                                            </button>
                                            <button
                                                wire:click="deleteBackup('{{ $backup['filename'] }}')"
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
                                            <button wire:click="openCreateModal" class="btn btn-primary mt-2">
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
                                    <label class="form-label">Backup Name <span class="text-danger">*</span></label>
                                    <input type="text" wire:model="backup_name" class="form-control @error('backup_name') is-invalid @enderror" placeholder="Enter backup name">
                                    @error('backup_name')
                                        <div class="invalid-feedback">{{ $message }}</div>
                                    @enderror
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

    <!-- Restore Backup Modal -->
    @if($showRestoreModal && $selectedBackup)
        <div class="modal fade show d-block" tabindex="-1" style="background-color: rgba(0,0,0,0.5);">
            <div class="modal-dialog modal-lg">
                <div class="modal-content">
                    <div class="modal-header bg-danger text-white">
                        <h5 class="modal-title">
                            <i class="fa-solid fa-triangle-exclamation me-2"></i>Restore Backup - Confirmation Required
                        </h5>
                        <button type="button" class="btn-close btn-close-white" wire:click="closeRestoreModal"></button>
                    </div>
                    <div class="modal-body">
                        <div class="alert alert-danger">
                            <h5 class="alert-heading">
                                <i class="fa-solid fa-exclamation-triangle me-2"></i>
                                Critical Warning
                            </h5>
                            <p class="mb-0">Restoring a backup will <strong>REPLACE ALL CURRENT DATA</strong> with the backup data. This action cannot be undone!</p>
                        </div>

                        <div class="card mb-3">
                            <div class="card-header bg-light">
                                <h6 class="mb-0">Backup Information</h6>
                            </div>
                            <div class="card-body">
                                <div class="row g-3">
                                    <div class="col-12">
                                        <strong>Backup File:</strong>
                                        <p class="mb-0 text-muted">{{ $selectedBackup }}</p>
                                    </div>
                                </div>
                            </div>
                        </div>

                        <div class="card mb-3 border-danger">
                            <div class="card-body">
                                <h6 class="text-danger mb-3">What will happen:</h6>
                                <ul class="mb-0">
                                    <li>All current database data will be replaced</li>
                                    <li>All users will be logged out</li>
                                    <li>Recent changes will be lost</li>
                                    <li>System will be restored to the backup state</li>
                                </ul>
                            </div>
                        </div>

                        <div class="mb-3">
                            <label class="form-label">
                                Type <code class="text-danger">RESTORE</code> to confirm this action:
                            </label>
                            <input type="text" wire:model="restoreConfirmation" class="form-control form-control-lg text-center" placeholder="Type RESTORE" autocomplete="off">
                        </div>
                    </div>
                    <div class="modal-footer">
                        <button type="button" class="btn btn-secondary" wire:click="closeRestoreModal">
                            Cancel
                        </button>
                        <button type="button" class="btn btn-danger" wire:click="restoreBackup" wire:loading.attr="disabled" {{ $restoreConfirmation !== 'RESTORE' ? 'disabled' : '' }}>
                            <span wire:loading.remove wire:target="restoreBackup">
                                <i class="fa-solid fa-rotate-left me-1"></i>Restore Backup
                            </span>
                            <span wire:loading wire:target="restoreBackup">
                                <i class="fa-solid fa-spinner fa-spin me-1"></i>Restoring...
                            </span>
                        </button>
                    </div>
                </div>
            </div>
        </div>
    @endif

    <!-- Loading Indicator -->
    <div wire:loading wire:target="createBackup,restoreBackup" class="position-fixed top-50 start-50 translate-middle" style="z-index: 9999;">
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
