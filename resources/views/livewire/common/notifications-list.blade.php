@php
    use App\Livewire\Common\NotificationsList;

    $icons = [
        'calendar' => 'fa-solid fa-calendar-days',
        'calendar-event' => 'fa-solid fa-calendar-check',
        'calendar-xmark' => 'fa-solid fa-calendar-xmark',
        'currency-dollar' => 'fa-solid fa-money-bill-wave',
        'gift' => 'fa-solid fa-gift',
        'file-invoice' => 'fa-solid fa-file-invoice-dollar',
        'loan' => 'fa-solid fa-hand-holding-dollar',
        'ticket' => 'fa-solid fa-ticket',
        'alert-triangle' => 'fa-solid fa-triangle-exclamation',
        'circle-check' => 'fa-solid fa-circle-check',
        'message' => 'fa-regular fa-comment-dots',
    ];

    // Group into sections, in a fixed order; anything unlabelled goes last
    $order = [NotificationsList::SECTION_APPROVALS, NotificationsList::SECTION_MY_LEAVE, NotificationsList::SECTION_TICKETS, NotificationsList::SECTION_OTHER];
    $sections = collect($pendingApprovals)
        ->groupBy(fn ($n) => $n['section'] ?? NotificationsList::SECTION_OTHER)
        ->sortBy(fn ($items, $section) => array_search($section, $order, true) === false ? 99 : array_search($section, $order, true));
@endphp
<div wire:poll.30s.visible="loadPendingApprovals">
    @forelse($sections as $section => $items)
        <div class="d-flex justify-content-between align-items-center px-4 pt-3 pb-2 bg-body-tertiary border-bottom">
            <span class="small fw-semibold text-uppercase text-secondary" style="letter-spacing: 0.04em;">{{ $section }}</span>
            <span class="badge rounded-pill bg-secondary-subtle text-secondary-emphasis">{{ $items->count() }}</span>
        </div>

        @foreach($items as $notification)
            @php
                $color = $notification['color'] ?? 'info';
                $isDismissible = $notification['dismissible'] ?? false;
                $tag = $isDismissible ? 'button' : 'a';
            @endphp

            <{{ $tag }}
                @if($isDismissible)
                    type="button"
                    wire:click="dismiss(@js($notification['key']), @js($notification['url'] ?? null))"
                @else
                    href="{{ $notification['url'] ?? '#' }}"
                @endif
                wire:key="notification-{{ $notification['key'] }}"
                class="list-group-item list-group-item-action px-4 py-3 border-bottom text-start w-100">
                <div class="d-flex gap-3 align-items-start">
                    <div class="bg-{{ $color }}-subtle text-{{ $color }}-emphasis rounded-circle d-flex align-items-center justify-content-center flex-shrink-0" style="width: 40px; height: 40px;">
                        <i class="{{ $icons[$notification['icon'] ?? ''] ?? 'fa-solid fa-bell' }}"></i>
                    </div>
                    <div class="flex-fill" style="min-width: 0;">
                        <div class="d-flex justify-content-between gap-2">
                            <span class="badge bg-{{ $color }}-subtle text-{{ $color }}-emphasis mb-1" style="font-size: 0.7rem;">{{ $notification['type'] ?? 'Notification' }}</span>
                            <small class="text-muted text-nowrap">{{ $notification['time'] }}</small>
                        </div>
                        <div class="fw-medium">{{ $notification['title'] }}</div>
                        @if(! empty($notification['detail']))
                            <small class="text-muted d-block">{{ $notification['detail'] }}</small>
                        @endif
                        @if($isDismissible)
                            <small class="text-primary d-block mt-1"><i class="fa-solid fa-arrow-right me-1"></i>Open &amp; mark as seen</small>
                        @endif
                    </div>
                </div>
            </{{ $tag }}>
        @endforeach
    @empty
        <div class="p-5 text-center text-muted">
            <i class="fa-solid fa-inbox fa-3x mb-3 opacity-25"></i>
            <p class="mb-0">No notifications</p>
            <small>You're all caught up!</small>
        </div>
    @endforelse
</div>
