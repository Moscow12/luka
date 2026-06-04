<div>
    <div class="container-fluid">
        <!-- Page Header -->
        <div class="d-flex flex-column flex-md-row justify-content-between align-items-start align-items-md-center mb-4 gap-3">
            <div>
                <h4 class="mb-1 fw-bold">
                    <i class="fa-solid fa-fingerprint text-primary me-2"></i>Fingerprint Users
                </h4>
                <p class="text-muted mb-0">Upload and manage fingerprint device users</p>
            </div>
            <div class="d-flex gap-2">
                <span class="badge bg-primary bg-opacity-10 text-primary px-3 py-2">
                    <i class="fa-solid fa-users me-1"></i>{{ $users->total() }} Users
                </span>
            </div>
        </div>

        <!-- Alert Messages -->
        @if (session()->has('success'))
            <div class="alert alert-success alert-dismissible fade show mb-4" role="alert">
                <i class="fa-solid fa-check-circle me-2"></i>{{ session('success') }}
                <button type="button" class="btn-close" data-bs-dismiss="alert"></button>
            </div>
        @endif

        @if (session()->has('error'))
            <div class="alert alert-danger alert-dismissible fade show mb-4" role="alert">
                <i class="fa-solid fa-exclamation-circle me-2"></i>{{ session('error') }}
                <button type="button" class="btn-close" data-bs-dismiss="alert"></button>
            </div>
        @endif

        <div class="row">
            <!-- Upload Card -->
            <div class="col-lg-4 mb-4">
                <div class="card border-0 shadow-sm h-100">
                    <div class="card-header bg-gradient bg-primary text-white py-3">
                        <h5 class="mb-0 fw-semibold">
                            <i class="fa-solid fa-cloud-arrow-up me-2"></i>Upload Excel File
                        </h5>
                    </div>
                    <div class="card-body">
                        <form wire:submit.prevent="uploadExcel">
                            <!-- File Upload Area -->
                            <div class="mb-4">
                                <label class="form-label fw-semibold">
                                    <i class="fa-solid fa-file-excel text-success me-1"></i>Select Excel File
                                </label>
                                <div class="upload-zone border-2 rounded-3 p-4 text-center position-relative {{ $excelFile ? 'border-success bg-success bg-opacity-10' : 'border-primary border-opacity-50 bg-light' }}"
                                     style="border-style: dashed; transition: all 0.3s ease; cursor: pointer;">
                                    <input type="file"
                                           wire:model="excelFile"
                                           class="position-absolute top-0 start-0 w-100 h-100 opacity-0"
                                           style="cursor: pointer;"
                                           id="excelFileInput"
                                           accept=".xlsx,.xls,.csv">

                                    @if ($excelFile)
                                        <div class="py-2">
                                            <div class="bg-success bg-opacity-10 rounded-circle d-inline-flex align-items-center justify-content-center mb-3" style="width: 60px; height: 60px;">
                                                <i class="fa-solid fa-file-excel fa-2x text-success"></i>
                                            </div>
                                            <p class="mb-1 text-success fw-semibold">{{ $excelFile->getClientOriginalName() }}</p>
                                            <small class="text-muted">
                                                <i class="fa-solid fa-hard-drive me-1"></i>{{ number_format($excelFile->getSize() / 1024, 2) }} KB
                                            </small>
                                        </div>
                                    @else
                                        <div class="py-2">
                                            <div class="bg-primary bg-opacity-10 rounded-circle d-inline-flex align-items-center justify-content-center mb-3" style="width: 60px; height: 60px;">
                                                <i class="fa-solid fa-cloud-arrow-up fa-2x text-primary"></i>
                                            </div>
                                            <p class="mb-1 text-primary fw-semibold">Click or drag file here</p>
                                            <small class="text-muted">
                                                <i class="fa-solid fa-file-circle-check me-1"></i>.xlsx, .xls, .csv (Max: 10MB)
                                            </small>
                                        </div>
                                    @endif
                                </div>
                                @error('excelFile')
                                    <div class="text-danger mt-2 small">
                                        <i class="fa-solid fa-circle-exclamation me-1"></i>{{ $message }}
                                    </div>
                                @enderror

                                <div wire:loading wire:target="excelFile" class="mt-3 text-center">
                                    <div class="spinner-border spinner-border-sm text-primary" role="status"></div>
                                    <small class="text-muted ms-2">Preparing file...</small>
                                </div>
                            </div>

                            <!-- Upload Button -->
                            <button type="submit"
                                    class="btn btn-primary w-100 py-2 fw-semibold"
                                    wire:loading.attr="disabled"
                                    wire:target="uploadExcel"
                                    {{ !$excelFile ? 'disabled' : '' }}>
                                <span wire:loading.remove wire:target="uploadExcel">
                                    <i class="fa-solid fa-upload me-2"></i>Upload & Import
                                </span>
                                <span wire:loading wire:target="uploadExcel">
                                    <span class="spinner-border spinner-border-sm me-2"></span>
                                    Importing...
                                </span>
                            </button>
                        </form>

                        <!-- Instructions -->
                        <div class="mt-4 p-3 bg-light rounded-3 border">
                            <h6 class="mb-3 text-dark fw-semibold">
                                <i class="fa-solid fa-circle-info text-info me-2"></i>Excel Format Required
                            </h6>
                            <div class="small text-muted">
                                <p class="mb-2">Your Excel file should have these columns:</p>
                                <div class="d-flex flex-column gap-1">
                                    <div class="d-flex align-items-center">
                                        <span class="badge bg-primary rounded-pill me-2">1</span>
                                        <span>Name</span>
                                    </div>
                                    <div class="d-flex align-items-center">
                                        <span class="badge bg-primary rounded-pill me-2">2</span>
                                        <span>FP Device ID</span>
                                    </div>
                                    <div class="d-flex align-items-center">
                                        <span class="badge bg-secondary rounded-pill me-2">3</span>
                                        <span>FP Device Address <small class="text-muted">(Optional)</small></span>
                                    </div>
                                </div>
                            </div>
                        </div>
                    </div>
                </div>
            </div>

            <!-- Users List -->
            <div class="col-lg-8 mb-4">
                <div class="card border-0 shadow-sm">
                    <div class="card-header bg-white py-3 border-bottom">
                        <div class="row align-items-center g-3">
                            <div class="col-md-5">
                                <h5 class="mb-0 fw-semibold">
                                    <i class="fa-solid fa-users text-primary me-2"></i>Registered Users
                                </h5>
                            </div>
                            <div class="col-md-7">
                                <div class="input-group">
                                    <span class="input-group-text bg-light border-end-0">
                                        <i class="fa-solid fa-magnifying-glass text-muted"></i>
                                    </span>
                                    <input type="text"
                                           wire:model.live.debounce.300ms="search"
                                           class="form-control border-start-0 ps-0"
                                           placeholder="Search by name, device ID...">
                                    @if($search)
                                        <button class="btn btn-outline-secondary border-start-0"
                                                wire:click="$set('search', '')"
                                                type="button">
                                            <i class="fa-solid fa-xmark"></i>
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
                                            <th class="ps-4 py-3 fw-semibold text-muted">
                                                <i class="fa-solid fa-user me-1"></i>Name
                                            </th>
                                            <th class="py-3 fw-semibold text-muted">
                                                <i class="fa-solid fa-fingerprint me-1"></i>Device ID
                                            </th>
                                            <th class="py-3 fw-semibold text-muted">
                                                <i class="fa-solid fa-network-wired me-1"></i>Address
                                            </th>
                                            <th class="py-3 fw-semibold text-muted">
                                                <i class="fa-solid fa-link me-1"></i>Linked Employee
                                            </th>
                                            <th class="py-3 fw-semibold text-muted">
                                                <i class="fa-solid fa-user-plus me-1"></i>Added By
                                            </th>
                                            <th class="py-3 text-center fw-semibold text-muted">
                                                <i class="fa-solid fa-gear me-1"></i>Actions
                                            </th>
                                        </tr>
                                    </thead>
                                    <tbody>
                                        @foreach($users as $user)
                                            <tr wire:key="user-{{ $user->id }}">
                                                <td class="ps-4 py-3">
                                                    <div class="d-flex align-items-center">
                                                        <div class="avatar-circle bg-primary bg-opacity-10 text-primary me-3">
                                                            <i class="fa-solid fa-user"></i>
                                                        </div>
                                                        <span class="fw-semibold">{{ $user->name }}</span>
                                                    </div>
                                                </td>
                                                <td class="py-3">
                                                    <span class="badge bg-info px-3 py-2">
                                                        <i class="fa-solid fa-hashtag me-1"></i>{{ $user->fpdevice_id }}
                                                    </span>
                                                </td>
                                                <td class="py-3">
                                                    @if($user->fpdevice_address)
                                                        <span class="text-muted">
                                                            <i class="fa-solid fa-server me-1"></i>{{ $user->fpdevice_address }}
                                                        </span>
                                                    @else
                                                        <span class="text-muted">
                                                            <i class="fa-solid fa-minus"></i>
                                                        </span>
                                                    @endif
                                                </td>
                                                <td class="py-3">
                                                    @if($linked->has($user->fpdevice_id))
                                                        <span class="badge bg-success bg-opacity-10 text-success px-3 py-2">
                                                            <i class="fa-solid fa-circle-check me-1"></i>{{ $linked->get($user->fpdevice_id) }}
                                                        </span>
                                                    @else
                                                        <span class="badge bg-warning bg-opacity-10 text-warning px-3 py-2">
                                                            <i class="fa-solid fa-link-slash me-1"></i>Not linked
                                                        </span>
                                                    @endif
                                                </td>
                                                <td class="py-3">
                                                    <small class="text-muted">
                                                        <i class="fa-solid fa-circle-user me-1"></i>{{ $user->addedBy?->name ?? 'System' }}
                                                    </small>
                                                </td>
                                                <td class="py-3 text-center">
                                                    <div class="d-inline-flex gap-2">
                                                        <button wire:click="openLink('{{ $user->id }}')"
                                                                class="btn btn-sm btn-outline-primary rounded-pill px-3"
                                                                title="Link to employee">
                                                            <i class="fa-solid fa-link me-1"></i>Link
                                                        </button>
                                                        <button wire:click="deleteUser('{{ $user->id }}')"
                                                                wire:confirm="Are you sure you want to delete this user?"
                                                                class="btn btn-sm btn-outline-danger rounded-pill px-3"
                                                                title="Delete user">
                                                            <i class="fa-solid fa-trash-can"></i>
                                                        </button>
                                                    </div>
                                                </td>
                                            </tr>
                                        @endforeach
                                    </tbody>
                                </table>
                            </div>

                            <!-- Pagination -->
                            @if($users->hasPages())
                                <div class="card-footer bg-white border-top py-3">
                                    <div class="d-flex flex-column flex-md-row justify-content-between align-items-center gap-2">
                                        <div class="text-muted small">
                                            <i class="fa-solid fa-list-ol me-1"></i>
                                            Showing <span class="fw-semibold">{{ $users->firstItem() }}</span> to
                                            <span class="fw-semibold">{{ $users->lastItem() }}</span> of
                                            <span class="fw-semibold">{{ $users->total() }}</span> users
                                        </div>
                                        <div>
                                            {{ $users->links() }}
                                        </div>
                                    </div>
                                </div>
                            @endif
                        @else
                            <div class="text-center py-5">
                                <div class="bg-light rounded-circle d-inline-flex align-items-center justify-content-center mb-3" style="width: 80px; height: 80px;">
                                    @if($search)
                                        <i class="fa-solid fa-magnifying-glass fa-2x text-muted"></i>
                                    @else
                                        <i class="fa-solid fa-users-slash fa-2x text-muted"></i>
                                    @endif
                                </div>
                                <h6 class="mb-1">
                                    @if($search)
                                        No users found
                                    @else
                                        No fingerprint users yet
                                    @endif
                                </h6>
                                <p class="text-muted mb-0 small">
                                    @if($search)
                                        No users found matching "<strong>{{ $search }}</strong>"
                                    @else
                                        Upload an Excel file to get started with fingerprint user management.
                                    @endif
                                </p>
                            </div>
                        @endif
                    </div>
                </div>
            </div>
        </div>
    </div>

    <!-- Link to Employee Modal -->
    @if($showLinkModal)
        <div class="modal fade show d-block" tabindex="-1" style="background: rgba(0,0,0,0.5);" wire:key="link-modal">
            <div class="modal-dialog modal-lg modal-dialog-centered modal-dialog-scrollable">
                <div class="modal-content border-0 shadow">
                    <div class="modal-header bg-primary text-white">
                        <h5 class="modal-title fw-semibold">
                            <i class="fa-solid fa-link me-2"></i>Link Fingerprint User to Employee
                        </h5>
                        <button type="button" class="btn-close btn-close-white" wire:click="closeLink"></button>
                    </div>
                    <div class="modal-body">
                        <div class="alert alert-info d-flex align-items-center gap-3 mb-4">
                            <i class="fa-solid fa-fingerprint fa-2x"></i>
                            <div>
                                <div class="fw-semibold">Device User: {{ $linkingUserName }}</div>
                                <small>
                                    Device ID:
                                    <span class="badge bg-info"><i class="fa-solid fa-hashtag me-1"></i>{{ $linkingDeviceId }}</span>
                                </small>
                            </div>
                        </div>

                        <p class="text-muted small mb-3">
                            <i class="fa-solid fa-circle-info me-1"></i>
                            Click <strong>Update FP ID</strong> to set an employee's fingerprint ID to
                            <strong>{{ $linkingDeviceId }}</strong>, or <strong>Unlink</strong> to remove it.
                            Use the search to find any employee.
                        </p>

                        <!-- Search employees inside the modal -->
                        <div class="input-group mb-3">
                            <span class="input-group-text bg-light border-end-0">
                                <i class="fa-solid fa-magnifying-glass text-muted"></i>
                            </span>
                            <input type="text"
                                   wire:model.live.debounce.300ms="modalSearch"
                                   class="form-control border-start-0 ps-0"
                                   placeholder="Search all employees by name or employee number...">
                            @if($modalSearch)
                                <button class="btn btn-outline-secondary border-start-0"
                                        wire:click="$set('modalSearch', '')"
                                        type="button">
                                    <i class="fa-solid fa-xmark"></i>
                                </button>
                            @endif
                        </div>

                        <div wire:loading.delay wire:target="modalSearch" class="text-center mb-2">
                            <span class="spinner-border spinner-border-sm text-primary"></span>
                            <small class="text-muted ms-1">Searching...</small>
                        </div>

                        @if(count($candidates) > 0)
                            <div class="table-responsive">
                                <table class="table table-hover align-middle">
                                    <thead class="bg-light">
                                        <tr>
                                            <th class="fw-semibold text-muted">Employee</th>
                                            <th class="fw-semibold text-muted">Emp No.</th>
                                            <th class="fw-semibold text-muted text-center">Match</th>
                                            <th class="fw-semibold text-muted">Current FP ID</th>
                                            <th class="fw-semibold text-muted text-center">Action</th>
                                        </tr>
                                    </thead>
                                    <tbody>
                                        @foreach($candidates as $candidate)
                                            <tr wire:key="cand-{{ $candidate['id'] }}">
                                                <td class="fw-semibold">{{ $candidate['name'] }}</td>
                                                <td><small class="text-muted">{{ $candidate['employee_no'] ?? '—' }}</small></td>
                                                <td class="text-center">
                                                    @if($candidate['score'] !== null)
                                                        <span class="badge {{ $candidate['score'] >= 67 ? 'bg-success' : ($candidate['score'] >= 34 ? 'bg-warning text-dark' : 'bg-secondary') }}">
                                                            {{ $candidate['score'] }}%
                                                        </span>
                                                    @else
                                                        <span class="text-muted small">—</span>
                                                    @endif
                                                </td>
                                                <td>
                                                    @if($candidate['current_fpid'])
                                                        <span class="badge bg-light text-dark border">{{ $candidate['current_fpid'] }}</span>
                                                    @else
                                                        <span class="text-muted small">None</span>
                                                    @endif
                                                </td>
                                                <td class="text-center">
                                                    @if($candidate['already_linked'])
                                                        <div class="d-inline-flex align-items-center gap-2">
                                                            <span class="badge bg-success">
                                                                <i class="fa-solid fa-check me-1"></i>Linked
                                                            </span>
                                                            <button wire:click="unlinkEmployee('{{ $candidate['id'] }}')"
                                                                    wire:confirm="Unlink this employee from device ID {{ $linkingDeviceId }}?"
                                                                    class="btn btn-sm btn-outline-danger rounded-pill px-3">
                                                                <i class="fa-solid fa-link-slash me-1"></i>Unlink
                                                            </button>
                                                        </div>
                                                    @elseif($candidate['current_fpid'])
                                                        {{-- Linked to a DIFFERENT device id --}}
                                                        <button wire:click="updateEmployeeFpid('{{ $candidate['id'] }}')"
                                                                wire:confirm="This employee is already on FP ID {{ $candidate['current_fpid'] }}. Re-link to {{ $linkingDeviceId }}?"
                                                                class="btn btn-sm btn-outline-primary rounded-pill px-3">
                                                            <i class="fa-solid fa-rotate me-1"></i>Re-link
                                                        </button>
                                                    @else
                                                        <button wire:click="updateEmployeeFpid('{{ $candidate['id'] }}')"
                                                                wire:confirm="Set this employee's fingerprint ID to {{ $linkingDeviceId }}?"
                                                                class="btn btn-sm btn-primary rounded-pill px-3">
                                                            <i class="fa-solid fa-link me-1"></i>Update FP ID
                                                        </button>
                                                    @endif
                                                </td>
                                            </tr>
                                        @endforeach
                                    </tbody>
                                </table>
                            </div>
                        @else
                            <div class="text-center py-4">
                                <i class="fa-solid fa-user-slash fa-2x text-muted mb-2"></i>
                                <p class="text-muted mb-0">
                                    @if($modalSearch)
                                        No employees found matching "<strong>{{ $modalSearch }}</strong>".
                                    @else
                                        No employees with a matching name were found. Use the search above to find one.
                                    @endif
                                </p>
                            </div>
                        @endif
                    </div>
                    <div class="modal-footer">
                        <button type="button" class="btn btn-secondary" wire:click="closeLink">
                            <i class="fa-solid fa-xmark me-1"></i>Close
                        </button>
                    </div>
                </div>
            </div>
        </div>
    @endif

    <style>
        .avatar-circle {
            width: 38px;
            height: 38px;
            border-radius: 50%;
            display: flex;
            align-items: center;
            justify-content: center;
            font-size: 14px;
        }

        .upload-zone:hover {
            border-color: var(--bs-primary) !important;
            background-color: rgba(var(--bs-primary-rgb), 0.05) !important;
        }

        .table-hover tbody tr:hover {
            background-color: rgba(var(--bs-primary-rgb), 0.03);
        }

        .bg-opacity-15 {
            --bs-bg-opacity: 0.15;
        }
    </style>
</div>
