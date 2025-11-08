@forelse($pendingApprovals as $notification)
<a href="{{ $notification['url'] ?? '#' }}" class="list-group-item list-group-item-action p-5 border-dashed border-bottom">
    <div class="d-flex justify-content-between">
        <div class="d-flex gap-4 align-items-center">
            <div class="icon-shape icon-md bg-{{ $notification['color'] ?? 'info' }}-subtle text-{{ $notification['color'] ?? 'info' }}-emphasis rounded-circle">
                <svg xmlns="http://www.w3.org/2000/svg" width="20" height="20" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="1.5" stroke-linecap="round" stroke-linejoin="round" class="icon icon-tabler icons-tabler-outline icon-tabler-{{ $notification['icon'] ?? 'bell' }}">
                    <path stroke="none" d="M0 0h24v24H0z" fill="none" />
                    @if($notification['icon'] === 'calendar')
                    <path d="M4 7a2 2 0 0 1 2 -2h12a2 2 0 0 1 2 2v12a2 2 0 0 1 -2 2h-12a2 2 0 0 1 -2 -2v-12z" />
                    <path d="M16 3v4" />
                    <path d="M8 3v4" />
                    <path d="M4 11h16" />
                    @endif
                </svg>
            </div>
            <div class="d-flex flex-column gap-1">
                <div>{{ $notification['title'] }}</div>
                <small class="text-secondary">{{ $notification['time'] }}</small>
            </div>
        </div>
        <div>
            <svg xmlns="http://www.w3.org/2000/svg" width="10" height="10" viewBox="0 0 24 24" fill="currentColor" class="icon icon-tabler icons-tabler-filled icon-tabler-circle text-info">
                <path stroke="none" d="M0 0h24v24H0z" fill="none" />
                <path d="M7 3.34a10 10 0 1 1 -4.995 8.984l-.005 -.324l.005 -.324a10 10 0 0 1 4.995 -8.336z" />
            </svg>
        </div>
    </div>
</a>
@empty
<div class="p-5 text-center text-muted">
    <p>No pending approvals</p>
</div>
@endforelse
