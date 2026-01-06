<div class="custom-container">

    <x-pages.breadcrumn title="FINGERPRINT DEVICES" :breadcrumbs="[
        ['label' => 'Home', 'url' => route('dashboard')],
        ['label' => 'HR Management', 'url' => '#'],
        ['label' => 'Attendance', 'url' => '#'],
        ['label' => 'Fingerprint Devices', 'url' => '#'],
    ]">
        <button class='btn btn-primary d-md-flex align-items-center gap-2' wire:click="createDevice">
            <i class="fa-solid fa-plus"></i> ADD DEVICE
        </button>
    </x-pages.breadcrumn>

    {{-- Flash Messages --}}
    @if (session()->has('success'))
        <div class="alert alert-success alert-dismissible fade show" role="alert">
            <i class="fa-solid fa-circle-check me-2"></i>{{ session('success') }}
            <button type="button" class="btn-close" data-bs-dismiss="alert"></button>
        </div>
    @endif
    @if (session()->has('error'))
        <div class="alert alert-danger alert-dismissible fade show" role="alert">
            <i class="fa-solid fa-circle-xmark me-2"></i>{{ session('error') }}
            <button type="button" class="btn-close" data-bs-dismiss="alert"></button>
        </div>
    @endif

    {{-- Test Result Alert --}}
    @if ($testResult)
        <div class="alert alert-{{ $testStatus === 'success' ? 'success' : 'danger' }} alert-dismissible fade show" role="alert">
            <div class="d-flex align-items-start">
                <i class="fa-solid fa-{{ $testStatus === 'success' ? 'check-circle' : 'exclamation-triangle' }} me-2 mt-1"></i>
                <div>
                    <strong>Connection Test Result:</strong>
                    <p class="mb-0 mt-1">{{ $testResult }}</p>
                </div>
            </div>
            <button type="button" class="btn-close" wire:click="clearTestResult"></button>
        </div>
    @endif

    <!-- Statistics Cards -->
    <div class="row g-3 mb-4">
        <div class="col-12 col-sm-6 col-xl-3">
            <div class="card card-lg border-0 shadow-sm">
                <div class="card-body">
                    <div class="d-flex align-items-center justify-content-between">
                        <div>
                            <p class="text-muted mb-1 small text-uppercase">Total Devices</p>
                            <h3 class="mb-0 fw-bold">{{ $totalDevices ?? 0 }}</h3>
                        </div>
                        <div class="icon-shape icon-lg bg-primary-subtle text-primary rounded-3">
                            <i class="fa-solid fa-fingerprint fa-lg"></i>
                        </div>
                    </div>
                </div>
            </div>
        </div>

        <div class="col-12 col-sm-6 col-xl-3">
            <div class="card card-lg border-0 shadow-sm">
                <div class="card-body">
                    <div class="d-flex align-items-center justify-content-between">
                        <div>
                            <p class="text-muted mb-1 small text-uppercase">Active Devices</p>
                            <h3 class="mb-0 fw-bold text-success">{{ $activeDevices ?? 0 }}</h3>
                        </div>
                        <div class="icon-shape icon-lg bg-success-subtle text-success rounded-3">
                            <i class="fa-solid fa-circle-check fa-lg"></i>
                        </div>
                    </div>
                </div>
            </div>
        </div>

        <div class="col-12 col-sm-6 col-xl-3">
            <div class="card card-lg border-0 shadow-sm">
                <div class="card-body">
                    <div class="d-flex align-items-center justify-content-between">
                        <div>
                            <p class="text-muted mb-1 small text-uppercase">Offline Devices</p>
                            <h3 class="mb-0 fw-bold text-danger">{{ $offlineDevices ?? 0 }}</h3>
                        </div>
                        <div class="icon-shape icon-lg bg-danger-subtle text-danger rounded-3">
                            <i class="fa-solid fa-circle-xmark fa-lg"></i>
                        </div>
                    </div>
                </div>
            </div>
        </div>

        <div class="col-12 col-sm-6 col-xl-3">
            <div class="card card-lg border-0 shadow-sm">
                <div class="card-body">
                    <div class="d-flex align-items-center justify-content-between">
                        <div>
                            <p class="text-muted mb-1 small text-uppercase">Total Synced Logs</p>
                            <h3 class="mb-0 fw-bold text-info">{{ number_format($totalSyncedLogs ?? 0) }}</h3>
                        </div>
                        <div class="icon-shape icon-lg bg-info-subtle text-info rounded-3">
                            <i class="fa-solid fa-clock-rotate-left fa-lg"></i>
                        </div>
                    </div>
                </div>
            </div>
        </div>
    </div>

    <!-- Devices List Card -->
    <div class="card card-lg">
        <!-- Card Header with Search and Filters -->
        <div class="card-header border-bottom">
            <div class="row g-3 align-items-center">
                <!-- Search Bar -->
                <div class="col-12 col-md-4">
                    <div class="input-group">
                        <span class="input-group-text bg-white">
                            <i class="fa-solid fa-magnifying-glass"></i>
                        </span>
                        <input type="search" wire:model.live.debounce.300ms="search" class="form-control"
                            placeholder="Search by name, IP, or location..." />
                        @if ($search)
                            <button wire:click="$set('search', '')" class="btn btn-outline-secondary" type="button">
                                <i class="fa-solid fa-times"></i>
                            </button>
                        @endif
                    </div>
                </div>

                <!-- Filters -->
                <div class="col-12 col-md-8">
                    <div class="d-flex flex-wrap gap-2 justify-content-md-end">
                        <!-- Status Filter -->
                        <select wire:model.live="statusFilter" class="form-select" style="width: auto;">
                            <option value="">All Status</option>
                            <option value="active">Active</option>
                            <option value="inactive">Inactive</option>
                            <option value="offline">Offline</option>
                        </select>

                        <!-- Reset Filters -->
                        @if ($search || $statusFilter)
                            <button wire:click="resetFilters" type="button" class="btn btn-outline-secondary">
                                <i class="fa-solid fa-rotate-left me-1"></i> Reset
                            </button>
                        @endif
                    </div>
                </div>
            </div>
        </div>

        <!-- Devices Grid/Table -->
        <div class="card-body">
            @if ($devices->count() > 0)
                <div class="row g-4">
                    @foreach ($devices as $device)
                        <div class="col-12 col-md-6 col-xl-4" wire:key="device-{{ $device->id }}">
                            <div class="card h-100 border {{ $device->status === 'active' ? 'border-success' : ($device->status === 'offline' ? 'border-danger' : 'border-secondary') }}">
                                <div class="card-header bg-transparent d-flex justify-content-between align-items-center">
                                    <div class="d-flex align-items-center gap-2">
                                        <div class="icon-shape icon-md bg-{{ $device->status === 'active' ? 'success' : ($device->status === 'offline' ? 'danger' : 'secondary') }}-subtle text-{{ $device->status === 'active' ? 'success' : ($device->status === 'offline' ? 'danger' : 'secondary') }} rounded-2">
                                            <i class="fa-solid fa-fingerprint"></i>
                                        </div>
                                        <div>
                                            <h6 class="mb-0 fw-semibold">{{ $device->name }}</h6>
                                            <small class="text-muted">{{ $device->location ?? 'No location' }}</small>
                                        </div>
                                    </div>
                                    <span class="badge bg-{{ $device->status === 'active' ? 'success' : ($device->status === 'offline' ? 'danger' : 'secondary') }}">
                                        {{ ucfirst($device->status) }}
                                    </span>
                                </div>
                                <div class="card-body">
                                    <div class="mb-3">
                                        <div class="d-flex justify-content-between mb-2">
                                            <span class="text-muted small">IP Address:</span>
                                            <span class="fw-semibold font-monospace">{{ $device->ip_address }}</span>
                                        </div>
                                        <div class="d-flex justify-content-between mb-2">
                                            <span class="text-muted small">Port:</span>
                                            <span class="fw-semibold">{{ $device->port }}</span>
                                        </div>
                                        <div class="d-flex justify-content-between mb-2">
                                            <span class="text-muted small">Last Sync:</span>
                                            <span class="fw-semibold">
                                                {{ $device->last_sync_at ? $device->last_sync_at->diffForHumans() : 'Never' }}
                                            </span>
                                        </div>
                                        <div class="d-flex justify-content-between">
                                            <span class="text-muted small">Total Synced:</span>
                                            <span class="fw-semibold">{{ number_format($device->total_synced_logs) }} logs</span>
                                        </div>
                                    </div>

                                    @if ($device->description)
                                        <p class="text-muted small mb-0">{{ Str::limit($device->description, 60) }}</p>
                                    @endif
                                </div>
                                <div class="card-footer bg-transparent">
                                    <div class="d-flex gap-2 flex-wrap">
                                        <!-- Test Connection Button -->
                                        <button wire:click="testConnection('{{ $device->id }}')"
                                            wire:loading.attr="disabled"
                                            wire:target="testConnection('{{ $device->id }}')"
                                            class="btn btn-sm btn-outline-primary flex-grow-1">
                                            <span wire:loading.remove wire:target="testConnection('{{ $device->id }}')">
                                                <i class="fa-solid fa-plug me-1"></i> Test
                                            </span>
                                            <span wire:loading wire:target="testConnection('{{ $device->id }}')">
                                                <span class="spinner-border spinner-border-sm me-1"></span>
                                                Testing...
                                            </span>
                                        </button>

                                        <!-- Sync Button -->
                                        <button wire:click="syncAttendance('{{ $device->id }}')"
                                            wire:loading.attr="disabled"
                                            wire:target="syncAttendance('{{ $device->id }}')"
                                            class="btn btn-sm btn-outline-success flex-grow-1">
                                            <span wire:loading.remove wire:target="syncAttendance('{{ $device->id }}')">
                                                <i class="fa-solid fa-sync me-1"></i> Sync
                                            </span>
                                            <span wire:loading wire:target="syncAttendance('{{ $device->id }}')">
                                                <span class="spinner-border spinner-border-sm me-1"></span>
                                                Syncing...
                                            </span>
                                        </button>

                                        <!-- Actions Dropdown -->
                                        <div class="dropdown">
                                            <button class="btn btn-sm btn-outline-secondary dropdown-toggle" type="button" data-bs-toggle="dropdown">
                                                <i class="fa-solid fa-ellipsis-v"></i>
                                            </button>
                                            <ul class="dropdown-menu dropdown-menu-end">
                                                <li>
                                                    <a class="dropdown-item" href="#" wire:click.prevent="editDevice('{{ $device->id }}')">
                                                        <i class="fa-solid fa-pen-to-square me-2"></i> Edit
                                                    </a>
                                                </li>
                                                <li>
                                                    <a class="dropdown-item" href="#" wire:click.prevent="toggleActive('{{ $device->id }}')">
                                                        <i class="fa-solid fa-{{ $device->is_active ? 'pause' : 'play' }} me-2"></i>
                                                        {{ $device->is_active ? 'Disable' : 'Enable' }}
                                                    </a>
                                                </li>
                                                <li>
                                                    <a class="dropdown-item text-warning" href="#"
                                                        wire:click.prevent="clearDeviceLogs('{{ $device->id }}')"
                                                        wire:confirm="This will DELETE all attendance logs from the device. Make sure you have synced first! Continue?">
                                                        <i class="fa-solid fa-broom me-2"></i> Clear Device Logs
                                                    </a>
                                                </li>
                                                <li><hr class="dropdown-divider"></li>
                                                <li>
                                                    <a class="dropdown-item text-danger" href="#"
                                                        wire:click.prevent="deleteDevice('{{ $device->id }}')"
                                                        wire:confirm="Are you sure you want to delete this device?">
                                                        <i class="fa-solid fa-trash me-2"></i> Delete
                                                    </a>
                                                </li>
                                            </ul>
                                        </div>
                                    </div>
                                </div>
                            </div>
                        </div>
                    @endforeach
                </div>

                <!-- Pagination -->
                <div class="d-flex flex-column flex-md-row justify-content-between align-items-center gap-3 mt-4">
                    <div class="text-muted">
                        Showing {{ $devices->firstItem() }} to {{ $devices->lastItem() }} of {{ $devices->total() }} devices
                    </div>
                    <div class="d-flex align-items-center gap-3">
                        <div class="d-flex align-items-center gap-2">
                            <label class="form-label mb-0 text-nowrap small">Per page:</label>
                            <select wire:model.live="perPage" class="form-select form-select-sm" style="width: auto;">
                                <option value="6">6</option>
                                <option value="12">12</option>
                                <option value="24">24</option>
                            </select>
                        </div>
                        {{ $devices->links() }}
                    </div>
                </div>
            @else
                <div class="text-center py-5">
                    <i class="fa-solid fa-fingerprint text-muted mb-3" style="font-size: 64px;"></i>
                    <h5 class="text-muted">No Devices Found</h5>
                    <p class="text-muted">
                        @if ($search || $statusFilter)
                            Try adjusting your filters or search query
                        @else
                            Register your first fingerprint device to get started
                        @endif
                    </p>
                    @if (!$search && !$statusFilter)
                        <button class="btn btn-primary mt-2" wire:click="createDevice">
                            <i class="fa-solid fa-plus me-1"></i> Add First Device
                        </button>
                    @endif
                </div>
            @endif
        </div>
    </div>

    <!-- Create/Edit Device Modal -->
    @if ($showModal)
        <div class="modal fade show d-block" tabindex="-1" style="background-color: rgba(0,0,0,0.5);">
            <div class="modal-dialog modal-lg modal-dialog-centered modal-dialog-scrollable">
                <div class="modal-content">
                    <div class="modal-header">
                        <h5 class="modal-title">
                            <i class="fa-solid fa-fingerprint me-2"></i>
                            {{ $modalMode === 'edit' ? 'Edit Device' : 'Register New Device' }}
                        </h5>
                        <button type="button" class="btn-close" wire:click="closeModal"></button>
                    </div>
                    <form wire:submit.prevent="saveDevice">
                        <div class="modal-body" style="max-height: 70vh; overflow-y: auto;">
                            <div class="row g-3">
                                <div class="col-12">
                                    <label for="deviceName" class="form-label">Device Name <span class="text-danger">*</span></label>
                                    <input type="text" wire:model="deviceForm.name"
                                        class="form-control @error('deviceForm.name') is-invalid @enderror"
                                        id="deviceName" placeholder="e.g., Main Entrance Device">
                                    @error('deviceForm.name')
                                        <div class="invalid-feedback">{{ $message }}</div>
                                    @enderror
                                </div>

                                <div class="col-md-8">
                                    <label for="ipAddress" class="form-label">IP Address <span class="text-danger">*</span></label>
                                    <div class="input-group">
                                        <span class="input-group-text"><i class="fa-solid fa-network-wired"></i></span>
                                        <input type="text" wire:model="deviceForm.ip_address"
                                            class="form-control @error('deviceForm.ip_address') is-invalid @enderror"
                                            id="ipAddress" placeholder="e.g., 192.168.1.100">
                                    </div>
                                    @error('deviceForm.ip_address')
                                        <div class="invalid-feedback d-block">{{ $message }}</div>
                                    @enderror
                                    <div class="form-text">Enter the ZKTeco device IP address</div>
                                </div>

                                <div class="col-md-4">
                                    <label for="port" class="form-label">Port <span class="text-danger">*</span></label>
                                    <input type="number" wire:model="deviceForm.port"
                                        class="form-control @error('deviceForm.port') is-invalid @enderror"
                                        id="port" placeholder="4370" min="1" max="65535">
                                    @error('deviceForm.port')
                                        <div class="invalid-feedback">{{ $message }}</div>
                                    @enderror
                                    <div class="form-text">Default: 4370</div>
                                </div>

                                <div class="col-12">
                                    <label for="location" class="form-label">Location</label>
                                    <input type="text" wire:model="deviceForm.location"
                                        class="form-control @error('deviceForm.location') is-invalid @enderror"
                                        id="location" placeholder="e.g., Main Building, Floor 1">
                                    @error('deviceForm.location')
                                        <div class="invalid-feedback">{{ $message }}</div>
                                    @enderror
                                </div>

                                <div class="col-12">
                                    <label for="description" class="form-label">Description</label>
                                    <textarea wire:model="deviceForm.description"
                                        class="form-control @error('deviceForm.description') is-invalid @enderror"
                                        id="description" rows="2"
                                        placeholder="Additional notes about this device..."></textarea>
                                    @error('deviceForm.description')
                                        <div class="invalid-feedback">{{ $message }}</div>
                                    @enderror
                                </div>

                                <div class="col-12">
                                    <div class="form-check form-switch">
                                        <input class="form-check-input" type="checkbox"
                                            wire:model="deviceForm.is_active" id="isActive">
                                        <label class="form-check-label" for="isActive">
                                            Device is active
                                        </label>
                                    </div>
                                </div>

                                <!-- Connection Info Box -->
                                <div class="col-12">
                                    <div class="alert alert-info mb-0">
                                        <h6 class="alert-heading mb-2">
                                            <i class="fa-solid fa-info-circle me-1"></i> Connection Information
                                        </h6>
                                        <ul class="mb-0 small">
                                            <li>Make sure the ZKTeco device is powered on and connected to the network</li>
                                            <li>The default port for ZKTeco devices is <strong>4370</strong></li>
                                            <li>Ensure your server can reach the device IP address (check firewall rules)</li>
                                            <li>After registering, use the "Test" button to verify connectivity</li>
                                        </ul>
                                    </div>
                                </div>
                            </div>
                        </div>
                        <div class="modal-footer">
                            <button type="button" class="btn btn-secondary" wire:click="closeModal">
                                <i class="fa-solid fa-times me-1"></i> Cancel
                            </button>
                            <button type="submit" class="btn btn-primary">
                                <i class="fa-solid fa-save me-1"></i>
                                {{ $modalMode === 'edit' ? 'Update Device' : 'Register Device' }}
                            </button>
                        </div>
                    </form>
                </div>
            </div>
        </div>
    @endif

    <!-- Loading Indicator -->
    <div wire:loading.delay class="position-fixed top-50 start-50 translate-middle" style="z-index: 9999;">
        <div class="spinner-border text-primary" role="status">
            <span class="visually-hidden">Loading...</span>
        </div>
    </div>
</div>
