<div>
    <div class="mb-4">
        <div class="d-flex justify-content-between align-items-center">
            <div>
                <h5 class="mb-1">SMS Logs</h5>
                <p class="text-muted mb-0">View all sent SMS messages and their delivery status</p>
            </div>
            <div>
                <a href="{{ route('setup.smsapis') }}" class="btn btn-outline-primary">
                    <i class="fa-solid fa-cog"></i> SMS Settings
                </a>
            </div>
        </div>
    </div>

    <!-- Statistics Cards -->
    <div class="row mb-4">
        <div class="col-md-3">
            <div class="card">
                <div class="card-body">
                    <div class="d-flex align-items-center">
                        <div class="flex-shrink-0">
                            <div class="avatar avatar-lg bg-primary-soft text-primary rounded">
                                <i class="fa-solid fa-envelope fa-lg"></i>
                            </div>
                        </div>
                        <div class="flex-grow-1 ms-3">
                            <h4 class="mb-0">{{ number_format($stats['total']) }}</h4>
                            <p class="text-muted mb-0">Total SMS</p>
                        </div>
                    </div>
                </div>
            </div>
        </div>
        <div class="col-md-3">
            <div class="card">
                <div class="card-body">
                    <div class="d-flex align-items-center">
                        <div class="flex-shrink-0">
                            <div class="avatar avatar-lg bg-success-soft text-success rounded">
                                <i class="fa-solid fa-check-circle fa-lg"></i>
                            </div>
                        </div>
                        <div class="flex-grow-1 ms-3">
                            <h4 class="mb-0">{{ number_format($stats['sent']) }}</h4>
                            <p class="text-muted mb-0">Sent</p>
                        </div>
                    </div>
                </div>
            </div>
        </div>
        <div class="col-md-3">
            <div class="card">
                <div class="card-body">
                    <div class="d-flex align-items-center">
                        <div class="flex-shrink-0">
                            <div class="avatar avatar-lg bg-danger-soft text-danger rounded">
                                <i class="fa-solid fa-times-circle fa-lg"></i>
                            </div>
                        </div>
                        <div class="flex-grow-1 ms-3">
                            <h4 class="mb-0">{{ number_format($stats['failed']) }}</h4>
                            <p class="text-muted mb-0">Failed</p>
                        </div>
                    </div>
                </div>
            </div>
        </div>
        <div class="col-md-3">
            <div class="card">
                <div class="card-body">
                    <div class="d-flex align-items-center">
                        <div class="flex-shrink-0">
                            <div class="avatar avatar-lg bg-warning-soft text-warning rounded">
                                <i class="fa-solid fa-clock fa-lg"></i>
                            </div>
                        </div>
                        <div class="flex-grow-1 ms-3">
                            <h4 class="mb-0">{{ number_format($stats['pending']) }}</h4>
                            <p class="text-muted mb-0">Pending</p>
                        </div>
                    </div>
                </div>
            </div>
        </div>
    </div>

    <!-- Filters -->
    <div class="card mb-4">
        <div class="card-body">
            <div class="row g-3">
                <div class="col-md-4">
                    <input class="form-control" type="search" wire:model.live="search" placeholder="Search phone or message..." />
                </div>
                <div class="col-md-2">
                    <select class="form-select" wire:model.live="statusFilter">
                        <option value="all">All Status</option>
                        <option value="sent">Sent</option>
                        <option value="delivered">Delivered</option>
                        <option value="failed">Failed</option>
                        <option value="pending">Pending</option>
                    </select>
                </div>
                <div class="col-md-3">
                    <input type="date" class="form-control" wire:model.live="dateFrom" placeholder="From Date" />
                </div>
                <div class="col-md-3">
                    <input type="date" class="form-control" wire:model.live="dateTo" placeholder="To Date" />
                </div>
            </div>
        </div>
    </div>

    <!-- SMS Logs Table -->
    <div class="card card-lg overflow-hidden">
        <div class="card-body p-0">
            <div class="table-responsive">
                <table class="table text-nowrap mb-0 table-centered table-hover">
                    <thead>
                        <tr>
                            <th>#</th>
                            <th>Phone Number</th>
                            <th>Message</th>
                            <th>Provider</th>
                            <th>Status</th>
                            <th>Sent By</th>
                            <th>Date</th>
                            <th>Actions</th>
                        </tr>
                    </thead>
                    <tbody>
                        @php
                            $number = ($logs->currentPage() - 1) * $logs->perPage() + 1;
                        @endphp
                        @forelse($logs as $log)
                        <tr>
                            <td>{{ $number++ }}</td>
                            <td>
                                <span class="fw-semibold">{{ $log->phone_number }}</span>
                            </td>
                            <td>
                                <span class="text-muted">{{ Str::limit($log->message, 50) }}</span>
                            </td>
                            <td>
                                @if($log->smsApiSetting)
                                    <span class="badge bg-info-soft text-info">
                                        {{ $log->smsApiSetting->provider_name }}
                                    </span>
                                @else
                                    <span class="text-muted">N/A</span>
                                @endif
                            </td>
                            <td>
                                @if($log->status === 'sent' || $log->status === 'delivered')
                                    <span class="badge bg-success">
                                        <i class="fa-solid fa-check"></i> {{ ucfirst($log->status) }}
                                    </span>
                                @elseif($log->status === 'failed')
                                    <span class="badge bg-danger">
                                        <i class="fa-solid fa-times"></i> Failed
                                    </span>
                                @else
                                    <span class="badge bg-warning">
                                        <i class="fa-solid fa-clock"></i> Pending
                                    </span>
                                @endif
                            </td>
                            <td>
                                @if($log->user)
                                    <span class="text-muted">{{ $log->user->name }}</span>
                                @else
                                    <span class="text-muted">System</span>
                                @endif
                            </td>
                            <td>
                                <span class="text-muted">{{ $log->created_at->format('M d, Y H:i') }}</span>
                            </td>
                            <td>
                                <button class="btn btn-sm btn-primary" wire:click="viewDetails('{{ $log->id }}')" title="View Details">
                                    <i class="fa-solid fa-eye"></i>
                                </button>
                            </td>
                        </tr>
                        @empty
                        <tr>
                            <td colspan="8" class="text-center py-5">
                                <div class="text-muted">
                                    <i class="fa-solid fa-inbox fa-3x mb-3"></i>
                                    <p>No SMS logs found.</p>
                                </div>
                            </td>
                        </tr>
                        @endforelse
                    </tbody>
                </table>
            </div>

            @if($logs->hasPages())
            <div class="card-footer border-top border-dashed">
                <div class="d-flex justify-content-between align-items-center">
                    <div class="text-muted">
                        Showing {{ $logs->firstItem() }} to {{ $logs->lastItem() }} of {{ $logs->total() }} entries
                    </div>
                    <div>
                        {{ $logs->links() }}
                    </div>
                </div>
            </div>
            @endif
        </div>
    </div>

    <!-- Details Modal -->
    @if($showModal && $selectedLog)
    <div class="modal fade show d-block" tabindex="-1" style="background: rgba(0,0,0,0.5);">
        <div class="modal-dialog modal-lg">
            <div class="modal-content">
                <div class="modal-header">
                    <h5 class="modal-title">SMS Details</h5>
                    <button type="button" class="btn-close" wire:click="closeModal"></button>
                </div>
                <div class="modal-body">
                    <div class="row mb-3">
                        <div class="col-md-6">
                            <strong>Phone Number:</strong>
                            <p class="mb-0">{{ $selectedLog->phone_number }}</p>
                        </div>
                        <div class="col-md-6">
                            <strong>Status:</strong>
                            <p class="mb-0">
                                @if($selectedLog->status === 'sent' || $selectedLog->status === 'delivered')
                                    <span class="badge bg-success">{{ ucfirst($selectedLog->status) }}</span>
                                @elseif($selectedLog->status === 'failed')
                                    <span class="badge bg-danger">Failed</span>
                                @else
                                    <span class="badge bg-warning">Pending</span>
                                @endif
                            </p>
                        </div>
                    </div>
                    <div class="row mb-3">
                        <div class="col-md-6">
                            <strong>Provider:</strong>
                            <p class="mb-0">{{ $selectedLog->smsApiSetting->provider_name ?? 'N/A' }}</p>
                        </div>
                        <div class="col-md-6">
                            <strong>Sent By:</strong>
                            <p class="mb-0">{{ $selectedLog->user->name ?? 'System' }}</p>
                        </div>
                    </div>
                    <div class="row mb-3">
                        <div class="col-md-6">
                            <strong>Sent At:</strong>
                            <p class="mb-0">{{ $selectedLog->created_at->format('M d, Y H:i:s') }}</p>
                        </div>
                        @if($selectedLog->delivered_at)
                        <div class="col-md-6">
                            <strong>Delivered At:</strong>
                            <p class="mb-0">{{ $selectedLog->delivered_at->format('M d, Y H:i:s') }}</p>
                        </div>
                        @endif
                    </div>
                    <div class="mb-3">
                        <strong>Message:</strong>
                        <div class="p-3 bg-light rounded mt-2">
                            {{ $selectedLog->message }}
                        </div>
                    </div>
                    @if($selectedLog->response)
                    <div class="mb-3">
                        <strong>API Response:</strong>
                        <pre class="bg-dark text-white p-3 rounded mt-2" style="max-height: 200px; overflow-y: auto;">{{ json_encode(json_decode($selectedLog->response), JSON_PRETTY_PRINT) }}</pre>
                    </div>
                    @endif
                </div>
                <div class="modal-footer">
                    <button type="button" class="btn btn-secondary" wire:click="closeModal">Close</button>
                </div>
            </div>
        </div>
    </div>
    @endif
</div>
