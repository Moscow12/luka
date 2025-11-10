<div>
    <div class="container-fluid">
        <!-- Page Header -->
        <div class="row mb-4">
            <div class="col-12">
                <div class="d-flex justify-content-between align-items-center">
                    <div>
                        <h4 class="mb-1">Fingerprint Users Management</h4>
                        <p class="text-muted mb-0">Upload and manage fingerprint device users</p>
                    </div>
                    <div class="text-muted">
                        <i class="bi bi-fingerprint fs-4"></i>
                    </div>
                </div>
            </div>
        </div>

        <div class="row">
            <!-- Upload Card -->
            <div class="col-md-4 mb-4">
                <div class="card shadow-sm border-0 h-100">
                    <div class="card-header bg-primary text-white">
                        <h5 class="mb-0">
                            <i class="bi bi-cloud-upload me-2"></i>Upload Excel File
                        </h5>
                    </div>
                    <div class="card-body">
                        <form wire:submit.prevent="uploadExcel">
                            <!-- File Upload Area -->
                            <div class="mb-4">
                                <label class="form-label fw-semibold">Select Excel File</label>
                                <div class="border-2 border-dashed rounded-3 p-4 text-center {{ $excelFile ? 'border-success bg-success bg-opacity-10' : 'border-primary bg-light' }}"
                                     style="transition: all 0.3s ease;">
                                    <input type="file"
                                           wire:model="excelFile"
                                           class="d-none"
                                           id="excelFileInput"
                                           accept=".xlsx,.xls,.csv">

                                    <label for="excelFileInput" class="cursor-pointer w-100">
                                        @if ($excelFile)
                                            <i class="bi bi-file-earmark-excel text-success fs-1"></i>
                                            <p class="mb-0 mt-2 text-success fw-semibold">{{ $excelFile->getClientOriginalName() }}</p>
                                            <small class="text-muted">{{ number_format($excelFile->getSize() / 1024, 2) }} KB</small>
                                        @else
                                            <i class="bi bi-file-earmark-arrow-up text-primary fs-1"></i>
                                            <p class="mb-0 mt-2 text-primary fw-semibold">Click to select file</p>
                                            <small class="text-muted">Supported: .xlsx, .xls, .csv (Max: 10MB)</small>
                                        @endif
                                    </label>
                                </div>
                                @error('excelFile')
                                    <small class="text-danger mt-1">{{ $message }}</small>
                                @enderror

                                <div wire:loading wire:target="excelFile" class="mt-2">
                                    <div class="spinner-border spinner-border-sm text-primary" role="status">
                                        <span class="visually-hidden">Loading...</span>
                                    </div>
                                    <small class="text-muted ms-2">Preparing file...</small>
                                </div>
                            </div>

                            <!-- Upload Button -->
                            <button type="submit"
                                    class="btn btn-primary w-100 py-2"
                                    wire:loading.attr="disabled"
                                    wire:target="uploadExcel"
                                    {{ !$excelFile ? 'disabled' : '' }}>
                                <span wire:loading.remove wire:target="uploadExcel">
                                    <i class="bi bi-upload me-2"></i>Upload & Import
                                </span>
                                <span wire:loading wire:target="uploadExcel">
                                    <span class="spinner-border spinner-border-sm me-2" role="status"></span>
                                    Importing...
                                </span>
                            </button>
                        </form>

                        <!-- Instructions -->
                        <div class="mt-4 p-3 bg-light rounded-3">
                            <h6 class="mb-2 text-dark">
                                <i class="bi bi-info-circle me-2"></i>Excel Format
                            </h6>
                            <small class="text-muted">
                                <p class="mb-1">Your Excel file should have these columns:</p>
                                <ol class="mb-0 ps-3">
                                    <li>Name</li>
                                    <li>FP Device ID</li>
                                    <li>FP Device Address (Optional)</li>
                                </ol>
                            </small>
                        </div>
                    </div>
                </div>
            </div>

            <!-- Users List -->
            <div class="col-md-8 mb-4">
                <div class="card shadow-sm border-0">
                    <div class="card-header bg-white border-bottom">
                        <div class="row align-items-center">
                            <div class="col-md-6">
                                <h5 class="mb-0">
                                    <i class="bi bi-people me-2 text-primary"></i>Fingerprint Users
                                </h5>
                            </div>
                            <div class="col-md-6">
                                <div class="input-group">
                                    <span class="input-group-text bg-light border-end-0">
                                        <i class="bi bi-search"></i>
                                    </span>
                                    <input type="text"
                                           wire:model.live.debounce.300ms="search"
                                           class="form-control border-start-0 ps-0"
                                           placeholder="Search users...">
                                    @if($search)
                                        <button class="btn btn-outline-secondary"
                                                wire:click="$set('search', '')"
                                                type="button">
                                            <i class="bi bi-x"></i>
                                        </button>
                                    @endif
                                </div>
                            </div>
                        </div>
                    </div>

                    <div class="card-body p-0">
                        @if($users->count() > 0)
                            <div class="table-responsive">
                                <table class="table table-hover align-middle mb-0">
                                    <thead class="bg-light">
                                        <tr>
                                            <th class="px-4 py-3">Name</th>
                                            <th class="py-3">Device ID</th>
                                            <th class="py-3">Device Address</th>
                                            <th class="py-3">Added By</th>
                                            <th class="py-3 text-center">Actions</th>
                                        </tr>
                                    </thead>
                                    <tbody>
                                        @foreach($users as $user)
                                            <tr wire:key="user-{{ $user->id }}">
                                                <td class="px-4 py-3">
                                                    <div class="d-flex align-items-center">
                                                        <div class="avatar-circle bg-primary bg-opacity-10 text-primary me-3">
                                                            {{ strtoupper(substr($user->name, 0, 2)) }}
                                                        </div>
                                                        <span class="fw-semibold">{{ $user->name }}</span>
                                                    </div>
                                                </td>
                                                <td class="py-3">
                                                    <span class="badge bg-info bg-opacity-10 text-info">
                                                        {{ $user->fpdevice_id }}
                                                    </span>
                                                </td>
                                                <td class="py-3">
                                                    <span class="text-muted">{{ $user->fpdevice_address ?? 'N/A' }}</span>
                                                </td>
                                                <td class="py-3">
                                                    <small class="text-muted">
                                                        {{ $user->added_by?->name ?? 'System' }}
                                                    </small>
                                                </td>
                                                <td class="py-3 text-center">
                                                    <button wire:click="deleteUser('{{ $user->id }}')"
                                                            wire:confirm="Are you sure you want to delete this user?"
                                                            class="btn btn-sm btn-outline-danger"
                                                            title="Delete user">
                                                        <i class="bi bi-trash"></i>
                                                    </button>
                                                </td>
                                            </tr>
                                        @endforeach
                                    </tbody>
                                </table>
                            </div>

                            <!-- Pagination -->
                            <div class="card-footer bg-white border-top">
                                <div class="d-flex justify-content-between align-items-center">
                                    <div class="text-muted small">
                                        Showing {{ $users->firstItem() }} to {{ $users->lastItem() }} of {{ $users->total() }} users
                                    </div>
                                    <div>
                                        {{ $users->links() }}
                                    </div>
                                </div>
                            </div>
                        @else
                            <div class="text-center py-5">
                                <i class="bi bi-inbox fs-1 text-muted"></i>
                                <p class="text-muted mt-3 mb-0">
                                    @if($search)
                                        No users found matching "{{ $search }}"
                                    @else
                                        No fingerprint users uploaded yet. Upload an Excel file to get started.
                                    @endif
                                </p>
                            </div>
                        @endif
                    </div>
                </div>
            </div>
        </div>
    </div>

    <style>
        .avatar-circle {
            width: 40px;
            height: 40px;
            border-radius: 50%;
            display: flex;
            align-items: center;
            justify-content: center;
            font-weight: 600;
            font-size: 14px;
        }

        .cursor-pointer {
            cursor: pointer;
        }

        .border-dashed {
            border-style: dashed !important;
        }

        input[type="file"] + label {
            cursor: pointer;
            margin-bottom: 0;
        }

        .table-hover tbody tr:hover {
            background-color: rgba(0, 123, 255, 0.05);
        }
    </style>
</div>
