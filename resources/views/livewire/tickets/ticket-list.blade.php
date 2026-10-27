<div>
    <div class="row mb-4">
        <div class="col-12 d-md-flex justify-content-between align-items-center">
            <div>
                <h4 class="mb-1">{{ $title[0] }}</h4>
                <p class="text-muted mb-0">{{ $title[1] }}</p>
            </div>
            @if($canCreate)
                <a href="{{ route('tickets.create') }}" class="btn btn-primary mt-2 mt-md-0">
                    <i class="fa-solid fa-plus me-1"></i>New Ticket
                </a>
            @endif
        </div>
    </div>

    @include('livewire.tickets.partials.flash')

    {{-- Buckets --}}
    <div class="row mb-3 g-3">
        @foreach($buckets as $key => $b)
            <div class="col-6 col-md-3">
                <button type="button" wire:click="setBucket('{{ $key }}')"
                        class="card w-100 text-start h-100 {{ $bucket === $key ? 'border-primary border-2' : '' }} {{ $key === 'overdue' && $b['count'] > 0 ? 'border-danger' : '' }}">
                    <div class="card-body py-3">
                        <div class="text-muted small">{{ $b['label'] }}</div>
                        <div class="h4 mb-0 {{ $key === 'overdue' && $b['count'] > 0 ? 'text-danger' : '' }}">{{ $b['count'] }}</div>
                    </div>
                </button>
            </div>
        @endforeach
    </div>

    {{-- Department workload --}}
    @if($scope === 'department' && $workload && $workload->isNotEmpty())
        <div class="card mb-4">
            <div class="card-header"><h6 class="mb-0"><i class="fa-solid fa-people-group me-2"></i>Workload by officer</h6></div>
            <div class="card-body p-0">
                <div class="table-responsive">
                    <table class="table table-sm align-middle mb-0">
                        <thead class="table-light">
                            <tr><th>Officer</th><th class="text-center">Active</th><th class="text-center">Overdue</th><th class="text-center">Resolved/Closed</th><th style="width: 35%">Load</th></tr>
                        </thead>
                        <tbody>
                            @php $maxActive = max(1, $workload->max('active')); @endphp
                            @foreach($workload as $row)
                                <tr>
                                    <td>{{ $row['name'] }}</td>
                                    <td class="text-center">{{ $row['active'] }}</td>
                                    <td class="text-center">
                                        @if($row['overdue'] > 0)<span class="badge bg-danger">{{ $row['overdue'] }}</span>@else 0 @endif
                                    </td>
                                    <td class="text-center">{{ $row['resolved'] }}</td>
                                    <td>
                                        <div class="progress" style="height: 8px;">
                                            <div class="progress-bar {{ $row['overdue'] > 0 ? 'bg-danger' : 'bg-primary' }}" style="width: {{ round($row['active'] / $maxActive * 100) }}%"></div>
                                        </div>
                                    </td>
                                </tr>
                            @endforeach
                        </tbody>
                    </table>
                </div>
            </div>
        </div>
    @endif

    {{-- Filters --}}
    <div class="card mb-4">
        <div class="card-body">
            <div class="row g-2 align-items-end">
                <div class="col-md-3">
                    <label class="form-label small">Search</label>
                    <input type="search" class="form-control" placeholder="Number or subject" wire:model.live.debounce.400ms="search">
                </div>
                <div class="col-6 col-md-2">
                    <label class="form-label small">Status</label>
                    <select class="form-select" wire:model.live="filterStatus">
                        <option value="">All</option>
                        @foreach($statuses as $value => $label)
                            <option value="{{ $value }}">{{ $label }}</option>
                        @endforeach
                    </select>
                </div>
                <div class="col-6 col-md-2">
                    <label class="form-label small">Priority</label>
                    <select class="form-select" wire:model.live="filterPriority">
                        <option value="">All</option>
                        <option value="critical">Critical</option>
                        <option value="high">High</option>
                        <option value="medium">Medium</option>
                        <option value="low">Low</option>
                    </select>
                </div>
                <div class="col-6 col-md-2">
                    <label class="form-label small">Category</label>
                    <select class="form-select" wire:model.live="filterCategory">
                        <option value="">All</option>
                        @foreach($categories as $category)
                            <option value="{{ $category->id }}">{{ $category->name }}</option>
                        @endforeach
                    </select>
                </div>
                @if($departmentOptions->isNotEmpty())
                    <div class="col-6 col-md-2">
                        <label class="form-label small">Department</label>
                        <select class="form-select" wire:model.live="filterDepartment">
                            <option value="">All</option>
                            @foreach($departmentOptions as $department)
                                <option value="{{ $department->id }}">{{ $department->name }}</option>
                            @endforeach
                        </select>
                    </div>
                @endif
                @if($scope === 'all')
                    <div class="col-6 col-md-2">
                        <label class="form-label small">Created from</label>
                        <input type="date" class="form-control" wire:model.live="dateFrom">
                    </div>
                    <div class="col-6 col-md-2">
                        <label class="form-label small">Created to</label>
                        <input type="date" class="form-control" wire:model.live="dateTo">
                    </div>
                @endif
                <div class="col-auto">
                    <button type="button" class="btn btn-outline-secondary" wire:click="resetFilters" title="Reset filters"><i class="fa-solid fa-rotate-left"></i></button>
                </div>
            </div>
        </div>
    </div>

    {{-- Tickets --}}
    <div class="card">
        <div class="card-body p-0">
            <div class="table-responsive">
                <table class="table table-hover align-middle mb-0">
                    <thead class="table-light">
                        <tr>
                            <th>Ticket</th>
                            <th>Category</th>
                            <th class="text-center">Priority</th>
                            <th class="text-center">Status</th>
                            <th>{{ $scope === 'assigned' ? 'Requested by' : 'Assignee' }}</th>
                            <th>Due</th>
                            <th class="text-end">Actions</th>
                        </tr>
                    </thead>
                    <tbody>
                        @forelse($tickets as $ticket)
                            @php
                                $me = $ticket->participants->first();
                                $unread = $ticket->last_message_at && (! $me?->last_read_at || $me->last_read_at->lt(\Illuminate\Support\Carbon::parse($ticket->last_message_at)));
                                $overdue = $ticket->isOverdue();
                            @endphp
                            <tr wire:key="ticket-{{ $ticket->id }}">
                                <td style="min-width: 260px;">
                                    <a href="{{ route('tickets.view', $ticket) }}" class="fw-semibold text-decoration-none">
                                        @if($unread)<span class="badge rounded-pill bg-primary me-1" title="Unread messages">&nbsp;</span>@endif
                                        {{ $ticket->ticket_number }}
                                    </a>
                                    <div>{{ Str::limit($ticket->subject, 70) }}</div>
                                    @if($ticket->latestPublicMessage)
                                        <small class="text-muted">
                                            <i class="fa-regular fa-comment me-1"></i>{{ $ticket->latestPublicMessage->user?->first_name }}:
                                            {{ \App\Support\HtmlSanitizer::toText($ticket->latestPublicMessage->body, 60) }}
                                        </small>
                                    @endif
                                </td>
                                <td>
                                    {{ $ticket->category?->name }}
                                    @if($scope !== 'mine')<br><small class="text-muted">{{ $ticket->department?->name }}</small>@endif
                                </td>
                                <td class="text-center"><span class="badge {{ $ticket->priorityBadgeClass() }}">{{ ucfirst($ticket->priority) }}</span></td>
                                <td class="text-center"><span class="badge {{ $ticket->status->badgeClass() }}">{{ $ticket->status->label() }}</span></td>
                                <td>
                                    @if($scope === 'assigned')
                                        {{ $ticket->creator?->first_name }} {{ $ticket->creator?->surname }}
                                    @else
                                        {{ $ticket->assignee ? $ticket->assignee->first_name.' '.$ticket->assignee->last_name : '— queue —' }}
                                    @endif
                                </td>
                                <td class="{{ $overdue ? 'text-danger fw-semibold' : '' }}">
                                    {{ $ticket->due_date->format('d M Y') }}
                                    @if($overdue)<br><small><i class="fa-solid fa-triangle-exclamation me-1"></i>{{ $ticket->due_date->diffForHumans(null, true) }} overdue</small>@endif
                                </td>
                                <td class="text-end text-nowrap">
                                    @if($scope === 'mine' && $ticket->status === \App\Enums\TicketStatus::Resolved)
                                        <button class="btn btn-sm btn-success" wire:click="openAction('{{ $ticket->id }}', 'close')" title="Verify and close"><i class="fa-solid fa-check"></i></button>
                                        <button class="btn btn-sm btn-outline-danger" wire:click="openAction('{{ $ticket->id }}', 'reopen')" title="Reopen"><i class="fa-solid fa-rotate-left"></i></button>
                                    @endif
                                    <a href="{{ route('tickets.view', $ticket) }}" class="btn btn-sm btn-outline-primary" title="Open"><i class="fa-solid fa-eye"></i></a>
                                </td>
                            </tr>
                        @empty
                            <tr>
                                <td colspan="7" class="text-center py-5 text-muted">
                                    <i class="fa-solid fa-inbox fa-2x mb-2 opacity-50"></i>
                                    <p class="mb-0">No tickets found.</p>
                                </td>
                            </tr>
                        @endforelse
                    </tbody>
                </table>
            </div>
        </div>
        @if($tickets->hasPages())
            <div class="card-footer">{{ $tickets->links() }}</div>
        @endif
    </div>

    {{-- Quick close / reopen --}}
    @if($actionTicketId)
        <div class="modal fade show d-block" tabindex="-1" style="background: rgba(0,0,0,0.5);">
            <div class="modal-dialog">
                <div class="modal-content">
                    <div class="modal-header {{ $actionType === 'close' ? 'bg-success' : 'bg-danger' }} text-white">
                        <h5 class="modal-title">{{ $actionType === 'close' ? 'Verify and close' : 'Reopen ticket' }}</h5>
                        <button type="button" class="btn-close btn-close-white" wire:click="closeAction"></button>
                    </div>
                    <form wire:submit="submitAction">
                        <div class="modal-body">
                            @include('livewire.tickets.partials.close-reopen-fields', ['type' => $actionType])
                        </div>
                        <div class="modal-footer">
                            <button type="button" class="btn btn-secondary" wire:click="closeAction">Cancel</button>
                            <button type="submit" class="btn {{ $actionType === 'close' ? 'btn-success' : 'btn-danger' }}">{{ $actionType === 'close' ? 'Close ticket' : 'Reopen' }}</button>
                        </div>
                    </form>
                </div>
            </div>
        </div>
    @endif
</div>
