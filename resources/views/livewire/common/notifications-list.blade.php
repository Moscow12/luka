<div>
    @forelse($pendingApprovals as $notification)
    <a href="{{ $notification['url'] ?? '#' }}" class="list-group-item list-group-item-action p-4 border-bottom">
        <div class="d-flex justify-content-between align-items-start">
            <div class="d-flex gap-3 align-items-center">
                <div class="icon-shape icon-md bg-{{ $notification['color'] ?? 'info' }}-subtle text-{{ $notification['color'] ?? 'info' }}-emphasis rounded-circle d-flex align-items-center justify-content-center" style="width: 40px; height: 40px; min-width: 40px;">
                    @switch($notification['icon'] ?? 'bell')
                        @case('calendar')
                            <i class="fa-solid fa-calendar-days"></i>
                            @break
                        @case('calendar-event')
                            <i class="fa-solid fa-calendar-check"></i>
                            @break
                        @case('currency-dollar')
                            <i class="fa-solid fa-money-bill-wave"></i>
                            @break
                        @case('gift')
                            <i class="fa-solid fa-gift"></i>
                            @break
                        @case('file-invoice')
                            <i class="fa-solid fa-file-invoice-dollar"></i>
                            @break
                        @case('loan')
                            <i class="fa-solid fa-hand-holding-dollar"></i>
                            @break
                        @default
                            <i class="fa-solid fa-bell"></i>
                    @endswitch
                </div>
                <div class="d-flex flex-column">
                    <span class="badge bg-{{ $notification['color'] ?? 'info' }}-subtle text-{{ $notification['color'] ?? 'info' }}-emphasis mb-1" style="width: fit-content; font-size: 0.7rem;">
                        {{ $notification['type'] ?? 'Notification' }}
                    </span>
                    <div class="fw-medium">{{ $notification['title'] }}</div>
                    <small class="text-muted">{{ $notification['time'] }}</small>
                </div>
            </div>
            <div>
                <span class="badge rounded-pill bg-{{ $notification['color'] ?? 'info' }}" style="width: 8px; height: 8px; padding: 0;"></span>
            </div>
        </div>
    </a>
    @empty
    <div class="p-5 text-center text-muted">
        <i class="fa-solid fa-inbox fa-3x mb-3 opacity-25"></i>
        <p class="mb-0">No pending approvals</p>
        <small>You're all caught up!</small>
    </div>
    @endforelse
</div>
