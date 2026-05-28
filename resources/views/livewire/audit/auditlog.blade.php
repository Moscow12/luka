<div class="custom-container">

    <x-pages.breadcrumn title="SYSTEM & SECURITY AUDIT LOGS" :breadcrumbs="[
        ['label' => 'Home', 'url' => route('dashboard')],
        ['label' => 'System & Security', 'url' => '#'],
        ['label' => 'Audit Logs', 'url' => '#'],
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
                                <i class="fa-solid fa-list fs-4"></i>
                            </div>
                        </div>
                        <div class="flex-grow-1 ms-3">
                            <p class="text-muted mb-1 small">Total Activities</p>
                            <h4 class="mb-0 fw-bold">{{ number_format($activities->total()) }}</h4>
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
                                <i class="fa-solid fa-eye fs-4"></i>
                            </div>
                        </div>
                        <div class="flex-grow-1 ms-3">
                            <p class="text-muted mb-1 small">Showing</p>
                            <h4 class="mb-0 fw-bold text-success">{{ number_format($activities->count()) }}</h4>
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
                                <i class="fa-solid fa-file fs-4"></i>
                            </div>
                        </div>
                        <div class="flex-grow-1 ms-3">
                            <p class="text-muted mb-1 small">Current Page</p>
                            <h4 class="mb-0 fw-bold text-info">{{ $activities->currentPage() }} of {{ $activities->lastPage() }}</h4>
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
                                <i class="fa-solid fa-shield-alt fs-4"></i>
                            </div>
                        </div>
                        <div class="flex-grow-1 ms-3">
                            <p class="text-muted mb-1 small">Unique Users</p>
                            <h4 class="mb-0 fw-bold text-warning">{{ number_format(\Spatie\Activitylog\Models\Activity::distinct('causer_id')->whereNotNull('causer_id')->count('causer_id')) }}</h4>
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
                <!-- Card Header with Filters -->
                <div class="card-header border-bottom bg-white">
                    <div class="row g-3 align-items-center">
                        <!-- Date Range & Search -->
                        <div class="col-12 col-md-3">
                            <div class="input-group">
                                <span class="input-group-text bg-white">
                                    <i class="fa-solid fa-calendar-days"></i>
                                </span>
                                <input type="date" wire:model.live="date_from" class="form-control" placeholder="From" />
                            </div>
                        </div>

                        <div class="col-12 col-md-3">
                            <div class="input-group">
                                <span class="input-group-text bg-white">
                                    <i class="fa-solid fa-calendar-days"></i>
                                </span>
                                <input type="date" wire:model.live="date_to" class="form-control" placeholder="To" />
                            </div>
                        </div>

                        <div class="col-12 col-md-6">
                            <div class="input-group">
                                <span class="input-group-text bg-white">
                                    <i class="fa-solid fa-magnifying-glass"></i>
                                </span>
                                <input type="search" wire:model.live.debounce.500ms="search" class="form-control"
                                    placeholder="Search by description, user, log name, or event..." />
                                @if($search)
                                    <button wire:click="$set('search', '')" class="btn btn-outline-secondary" type="button">
                                        <i class="fa-solid fa-times"></i>
                                    </button>
                                @endif
                            </div>
                        </div>

                        <!-- Actions -->
                        <div class="col-12">
                            <div class="d-flex flex-wrap gap-2 justify-content-end">
                                <!-- Filter Toggle Button -->
                                <button wire:click="toggleFilters" type="button"
                                    class="btn {{ $showFilters ? 'btn-primary' : 'btn-white' }}">
                                    <i class="fa-solid fa-filter me-1"></i>
                                    {{ $showFilters ? 'Hide' : 'Show' }} Filters
                                    @if($log_name || $event || $causer_type || $subject_type)
                                        <span class="badge bg-danger ms-1">
                                            {{ collect([$log_name, $event, $causer_type, $subject_type])->filter()->count() }}
                                        </span>
                                    @endif
                                </button>

                                <!-- Reset Filters -->
                                @if($search || $log_name || $event || $causer_type || $subject_type || $date_from || $date_to)
                                    <button wire:click="resetFilters" type="button" class="btn btn-outline-secondary">
                                        <i class="fa-solid fa-rotate-left me-1"></i> Reset
                                    </button>
                                @endif
                            </div>
                        </div>
                    </div>

                    <!-- Advanced Filters (Collapsible) -->
                    @if($showFilters)
                        <div class="row g-3 mt-2 pt-3 border-top">
                            <div class="col-12 col-md-6 col-lg-3">
                                <label class="form-label small text-muted mb-1">Log Name</label>
                                <select wire:model.live="log_name" class="form-select">
                                    <option value="">All Log Names</option>
                                    @foreach($logNames as $name)
                                        <option value="{{ $name }}">{{ ucwords(str_replace('_', ' ', $name)) }}</option>
                                    @endforeach
                                </select>
                            </div>

                            <div class="col-12 col-md-6 col-lg-3">
                                <label class="form-label small text-muted mb-1">Event Type</label>
                                <select wire:model.live="event" class="form-select">
                                    <option value="">All Events</option>
                                    @foreach($events as $evt)
                                        <option value="{{ $evt }}">{{ ucfirst($evt) }}</option>
                                    @endforeach
                                </select>
                            </div>

                            <div class="col-12 col-md-6 col-lg-3">
                                <label class="form-label small text-muted mb-1">User Type</label>
                                <select wire:model.live="causer_type" class="form-select">
                                    <option value="">All User Types</option>
                                    @foreach($causerTypes as $type)
                                        <option value="App\Models\{{ $type }}">{{ $type }}</option>
                                    @endforeach
                                </select>
                            </div>

                            <div class="col-12 col-md-6 col-lg-3">
                                <label class="form-label small text-muted mb-1">Subject Type</label>
                                <select wire:model.live="subject_type" class="form-select">
                                    <option value="">All Subject Types</option>
                                    @foreach($subjectTypes as $type)
                                        <option value="App\Models\{{ $type }}">{{ $type }}</option>
                                    @endforeach
                                </select>
                            </div>
                        </div>
                    @endif
                </div>

                <!-- Table Section -->
                <div class="table-responsive" style="min-height: 400px;">
                    <table class="table table-hover table-centered align-middle mb-0">
                        <thead class="table-light">
                            <tr>
                                <th class="text-center" style="width: 50px;">#</th>
                                <th style="width: 140px;">Date & Time</th>
                                <th>User</th>
                                <th style="width: 120px;">Log Name</th>
                                <th style="width: 100px;">Event</th>
                                <th>Description</th>
                                <th style="width: 120px;">Subject</th>
                                <th class="text-center" style="width: 80px;">Action</th>
                            </tr>
                        </thead>
                        <tbody>
                            @forelse($activities as $index => $activity)
                                <tr wire:key="activity-{{ $activity->id }}">
                                    <td class="text-center text-muted">
                                        {{ $activities->firstItem() + $index }}
                                    </td>
                                    <td>
                                        <div class="d-flex flex-column">
                                            <span class="fw-semibold text-dark small">
                                                {{ $activity->created_at->format('M d, Y') }}
                                            </span>
                                            <small class="text-muted">
                                                {{ $activity->created_at->format('h:i A') }}
                                            </small>
                                            <small class="text-info">
                                                {{ $activity->created_at->diffForHumans() }}
                                            </small>
                                        </div>
                                    </td>
                                    <td>
                                        @if($activity->causer)
                                            <div class="d-flex flex-column">
                                                <span class="fw-semibold text-dark">
                                                    {{ $activity->causer->username ?? $activity->causer->full_name ?? '—' }}
                                                </span>
                                                @if(!empty($activity->causer->full_name) && !empty($activity->causer->username))
                                                    <small class="text-muted">{{ $activity->causer->full_name }}</small>
                                                @endif
                                                @if(!empty($activity->causer->email))
                                                    <small class="text-muted">{{ $activity->causer->email }}</small>
                                                @endif
                                            </div>
                                        @else
                                            <span class="badge bg-secondary">System</span>
                                        @endif
                                    </td>
                                    <td>
                                        <span class="badge bg-info">
                                            {{ ucwords(str_replace('_', ' ', $activity->log_name ?? 'default')) }}
                                        </span>
                                    </td>
                                    <td>
                                        @php
                                            $eventColor = match($activity->event) {
                                                'created' => 'success',
                                                'updated' => 'primary',
                                                'deleted' => 'danger',
                                                'login' => 'info',
                                                'logout' => 'warning',
                                                default => 'secondary'
                                            };
                                        @endphp
                                        <span class="badge bg-{{ $eventColor }}">
                                            {{ ucfirst($activity->event ?? 'unknown') }}
                                        </span>
                                    </td>
                                    <td>
                                        <div class="text-truncate" style="max-width: 400px;" title="{{ $activity->description }}">
                                            {{ $activity->description }}
                                        </div>
                                    </td>
                                    <td>
                                        @if($activity->subject_type)
                                            <small class="text-muted">
                                                {{ class_basename($activity->subject_type) }}
                                                @if($activity->subject_id)
                                                    <br>#{{ Str::limit($activity->subject_id, 8) }}
                                                @endif
                                            </small>
                                        @else
                                            <span class="text-muted">-</span>
                                        @endif
                                    </td>
                                    <td>
                                        <div class="d-flex gap-1 justify-content-center">
                                            <button
                                                wire:click="viewDetails({{ $activity->id }})"
                                                class="btn btn-sm btn-primary"
                                                title="View Details">
                                                <i class="fa-solid fa-eye"></i>
                                            </button>
                                        </div>
                                    </td>
                                </tr>
                            @empty
                                <tr>
                                    <td colspan="8" class="text-center py-5">
                                        <div class="d-flex flex-column align-items-center justify-content-center">
                                            <i class="fa-solid fa-inbox text-muted mb-3" style="font-size: 48px;"></i>
                                            <h5 class="text-muted">No Activity Logs Found</h5>
                                            <p class="text-muted">
                                                @if($search || $log_name || $event || $causer_type || $subject_type || $date_from || $date_to)
                                                    Try adjusting your filters or search query
                                                @else
                                                    No activity logs available in the system
                                                @endif
                                            </p>
                                        </div>
                                    </td>
                                </tr>
                            @endforelse
                        </tbody>
                    </table>
                </div>

                <!-- Card Footer with Pagination -->
                <div class="card-footer border-top bg-white">
                    <div class="d-flex flex-column flex-md-row justify-content-between align-items-center gap-3">
                        <!-- Results Info -->
                        <div class="text-muted">
                            @if($activities->total() > 0)
                                Showing {{ $activities->firstItem() }} to {{ $activities->lastItem() }} of
                                {{ $activities->total() }} activity logs
                            @else
                                No activity logs found
                            @endif
                        </div>

                        <!-- Pagination and Per Page -->
                        <div class="d-flex flex-column flex-sm-row align-items-center gap-3">
                            <!-- Per Page Selector -->
                            <div class="d-flex align-items-center gap-2">
                                <label class="form-label mb-0 text-nowrap small">Rows per page:</label>
                                <select wire:model.live="perPage" class="form-select form-select-sm" style="width: auto;">
                                    <option value="10">10</option>
                                    <option value="25">25</option>
                                    <option value="50">50</option>
                                    <option value="100">100</option>
                                </select>
                            </div>

                            <!-- Pagination Links -->
                            <div>
                                {{ $activities->links() }}
                            </div>
                        </div>
                    </div>
                </div>
            </div>
        </div>
    </div>

    {{-- Activity Details Modal --}}
    @if($showDetailsModal && $selectedActivity)
        <div class="modal fade show d-block" tabindex="-1" style="background-color: rgba(0,0,0,0.5);">
            <div class="modal-dialog modal-xl modal-dialog-scrollable">
                <div class="modal-content">
                    <div class="modal-header bg-primary text-white">
                        <h5 class="modal-title">
                            <i class="fa fa-info-circle me-2"></i>Activity Log Details
                        </h5>
                        <button type="button" class="btn-close btn-close-white" wire:click="closeDetailsModal"></button>
                    </div>
                    <div class="modal-body">
                        <div class="row g-3">
                            <!-- Basic Information -->
                            <div class="col-12 col-lg-6">
                                <div class="card border-0 shadow-sm h-100">
                                    <div class="card-header bg-light">
                                        <h6 class="mb-0"><i class="fa fa-info-circle me-2"></i>Basic Information</h6>
                                    </div>
                                    <div class="card-body">
                                        <div class="row g-3">
                                            <div class="col-6">
                                                <small class="text-muted d-block mb-1">Activity ID</small>
                                                <strong>{{ $selectedActivity->id }}</strong>
                                            </div>
                                            <div class="col-6">
                                                <small class="text-muted d-block mb-1">Log Name</small>
                                                <span class="badge bg-info">
                                                    {{ ucwords(str_replace('_', ' ', $selectedActivity->log_name ?? 'default')) }}
                                                </span>
                                            </div>
                                            <div class="col-6">
                                                <small class="text-muted d-block mb-1">Event Type</small>
                                                @php
                                                    $eventColor = match($selectedActivity->event) {
                                                        'created' => 'success',
                                                        'updated' => 'primary',
                                                        'deleted' => 'danger',
                                                        'login' => 'info',
                                                        'logout' => 'warning',
                                                        default => 'secondary'
                                                    };
                                                @endphp
                                                <span class="badge bg-{{ $eventColor }}">
                                                    {{ ucfirst($selectedActivity->event ?? 'unknown') }}
                                                </span>
                                            </div>
                                            <div class="col-6">
                                                <small class="text-muted d-block mb-1">Date & Time</small>
                                                <strong>{{ $selectedActivity->created_at->format('M d, Y h:i A') }}</strong>
                                                <br>
                                                <small class="text-info">{{ $selectedActivity->created_at->diffForHumans() }}</small>
                                            </div>
                                            <div class="col-12">
                                                <small class="text-muted d-block mb-1">Description</small>
                                                <p class="mb-0">{{ $selectedActivity->description }}</p>
                                            </div>
                                        </div>
                                    </div>
                                </div>
                            </div>

                            <!-- User Information -->
                            <div class="col-12 col-lg-6">
                                <div class="card border-0 shadow-sm h-100">
                                    <div class="card-header bg-light">
                                        <h6 class="mb-0"><i class="fa fa-user me-2"></i>User Information</h6>
                                    </div>
                                    <div class="card-body">
                                        @if($selectedActivity->causer)
                                            <div class="row g-3">
                                                <div class="col-12">
                                                    <small class="text-muted d-block mb-1">Username</small>
                                                    <strong>{{ $selectedActivity->causer->username ?? '—' }}</strong>
                                                </div>
                                                <div class="col-12">
                                                    <small class="text-muted d-block mb-1">Full Name</small>
                                                    <strong>{{ $selectedActivity->causer->full_name ?? '—' }}</strong>
                                                </div>
                                                <div class="col-12">
                                                    <small class="text-muted d-block mb-1">User Email</small>
                                                    <strong>{{ $selectedActivity->causer->email ?? 'N/A' }}</strong>
                                                </div>
                                                <div class="col-6">
                                                    <small class="text-muted d-block mb-1">User Type</small>
                                                    <span class="badge bg-secondary">
                                                        {{ class_basename($selectedActivity->causer_type) }}
                                                    </span>
                                                </div>
                                                <div class="col-6">
                                                    <small class="text-muted d-block mb-1">User ID</small>
                                                    <strong>{{ $selectedActivity->causer_id }}</strong>
                                                </div>
                                            </div>
                                        @else
                                            <p class="text-muted mb-0">System-generated activity (no user associated)</p>
                                        @endif
                                    </div>
                                </div>
                            </div>

                            <!-- Subject Information -->
                            <div class="col-12">
                                <div class="card border-0 shadow-sm">
                                    <div class="card-header bg-light">
                                        <h6 class="mb-0"><i class="fa fa-cube me-2"></i>Subject Information</h6>
                                    </div>
                                    <div class="card-body">
                                        @if($selectedActivity->subject_type)
                                            <div class="row g-3 mb-3">
                                                <div class="col-6">
                                                    <small class="text-muted d-block mb-1">Subject Type</small>
                                                    <span class="badge bg-primary">
                                                        {{ class_basename($selectedActivity->subject_type) }}
                                                    </span>
                                                </div>
                                                <div class="col-6">
                                                    <small class="text-muted d-block mb-1">Subject ID</small>
                                                    <strong>{{ $selectedActivity->subject_id }}</strong>
                                                </div>
                                            </div>
                                            @if($selectedActivity->subject)
                                                <small class="text-muted d-block mb-2">Subject Details</small>
                                                <div class="bg-light p-3 rounded">
                                                    <pre class="mb-0" style="white-space: pre-wrap; font-size: 12px;">{{ json_encode($selectedActivity->subject->toArray(), JSON_PRETTY_PRINT) }}</pre>
                                                </div>
                                            @endif
                                        @else
                                            <p class="text-muted mb-0">No subject associated with this activity</p>
                                        @endif
                                    </div>
                                </div>
                            </div>

                            <!-- Properties/Changes -->
                            @if($selectedActivity->properties && count($selectedActivity->properties) > 0)
                                <div class="col-12">
                                    <div class="card border-0 shadow-sm">
                                        <div class="card-header bg-light">
                                            <h6 class="mb-0"><i class="fa fa-exchange-alt me-2"></i>Properties & Changes</h6>
                                        </div>
                                        <div class="card-body">
                                            @if(isset($selectedActivity->properties['attributes']) || isset($selectedActivity->properties['old']))
                                                <div class="row g-3">
                                                    @if(isset($selectedActivity->properties['old']))
                                                        <div class="col-12 col-lg-6">
                                                            <h6 class="text-danger mb-2">Old Values</h6>
                                                            <div class="bg-light p-3 rounded">
                                                                <pre class="mb-0" style="white-space: pre-wrap; font-size: 12px;">{{ json_encode($selectedActivity->properties['old'], JSON_PRETTY_PRINT) }}</pre>
                                                            </div>
                                                        </div>
                                                    @endif

                                                    @if(isset($selectedActivity->properties['attributes']))
                                                        <div class="col-12 col-lg-6">
                                                            <h6 class="text-success mb-2">New Values</h6>
                                                            <div class="bg-light p-3 rounded">
                                                                <pre class="mb-0" style="white-space: pre-wrap; font-size: 12px;">{{ json_encode($selectedActivity->properties['attributes'], JSON_PRETTY_PRINT) }}</pre>
                                                            </div>
                                                        </div>
                                                    @endif
                                                </div>
                                            @else
                                                <div class="bg-light p-3 rounded">
                                                    <pre class="mb-0" style="white-space: pre-wrap; font-size: 12px;">{{ json_encode($selectedActivity->properties, JSON_PRETTY_PRINT) }}</pre>
                                                </div>
                                            @endif
                                        </div>
                                    </div>
                                </div>
                            @endif

                            <!-- Batch Information -->
                            @if($selectedActivity->batch_uuid)
                                <div class="col-12">
                                    <div class="card border-0 shadow-sm">
                                        <div class="card-header bg-light">
                                            <h6 class="mb-0"><i class="fa fa-layer-group me-2"></i>Batch Information</h6>
                                        </div>
                                        <div class="card-body">
                                            <small class="text-muted d-block mb-1">Batch UUID</small>
                                            <code>{{ $selectedActivity->batch_uuid }}</code>
                                            <br>
                                            <small class="text-muted">This activity is part of a batch operation</small>
                                        </div>
                                    </div>
                                </div>
                            @endif
                        </div>
                    </div>
                    <div class="modal-footer">
                        <button type="button" class="btn btn-secondary" wire:click="closeDetailsModal">
                            Close
                        </button>
                    </div>
                </div>
            </div>
        </div>
    @endif

    <!-- Loading Indicator -->
    <div wire:loading class="position-fixed top-50 start-50 translate-middle" style="z-index: 9999;">
        <div class="spinner-border text-primary" role="status">
            <span class="visually-hidden">Loading...</span>
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
    </style>
</div>
