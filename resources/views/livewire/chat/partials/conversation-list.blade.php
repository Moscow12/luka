{{--
    Sidebar list of rooms, grouped Departments / Groups / Direct messages.
    Expects: $conversations (ChatInbox), $activeId, $openAction (Livewire method name), $compact (bool)
--}}
@php
    $me = auth()->id();
    // Pinned rooms first; sorting is stable, so recency order (from ChatInbox) is kept within each group
    $sorted = $conversations->sortBy(fn ($c) => $c->my_pinned === 'yes' ? 0 : 1);
    $sections = $compact
        ? ['Recent' => $sorted]
        : [
            'Departments' => $sorted->where('type', 'department'),
            'Groups' => $sorted->where('type', 'group'),
            'Direct messages' => $sorted->where('type', 'direct'),
        ];
@endphp

@foreach($sections as $title => $rooms)
    <div class="chat-section-title d-flex justify-content-between align-items-center">
        <span>{{ $title }}</span>
        @if($title === 'Groups' && ! $compact)
            <button type="button" class="chat-icon-btn" style="width: 24px; height: 24px;" wire:click="openNewGroup" title="New group"><i class="fa-solid fa-plus"></i></button>
        @endif
    </div>

    @forelse($rooms as $conversation)
        @php
            $last = $conversation->latestMessage;
            $unread = (int) $conversation->unread_count;
            $preview = match (true) {
                ! $last => $conversation->topic ?: 'No messages yet',
                $last->trashed() => 'Message deleted',
                $last->type !== 'text' => (string) $last->body,
                default => ($last->user_id === $me ? 'You: ' : ($conversation->isDirect() ? '' : ($last->user?->first_name.': ')))
                    .(\App\Support\HtmlSanitizer::toText((string) $last->body, 60) ?: '📎 Attachment'),
            };
        @endphp
        <button type="button" wire:key="room-{{ $conversation->id }}"
                class="chat-room {{ $activeId === $conversation->id ? 'active' : '' }} {{ $unread ? 'unread' : '' }} {{ $conversation->my_muted === 'yes' ? 'muted' : '' }}"
                wire:click="{{ $openAction }}('{{ $conversation->id }}')">
            @include('livewire.chat.partials.room-icon', ['conversation' => $conversation, 'size' => 36])
            <span class="flex-fill chat-truncate">
                <span class="d-flex justify-content-between align-items-baseline gap-2">
                    <span class="chat-room-name chat-truncate">
                        @if($conversation->type === 'department')<span class="text-muted">#</span>@endif{{ $conversation->displayName($me) }}
                        @if($conversation->my_pinned === 'yes')<i class="fa-solid fa-thumbtack ms-1 text-muted small"></i>@endif
                        @if($conversation->my_muted === 'yes')<i class="fa-solid fa-bell-slash ms-1 text-muted small"></i>@endif
                    </span>
                    @if($last)
                        <small class="text-muted flex-shrink-0" style="font-size: 0.7rem;">{{ $last->created_at->isToday() ? $last->created_at->format('H:i') : ($last->created_at->isCurrentWeek() ? $last->created_at->format('D') : $last->created_at->format('d M')) }}</small>
                    @endif
                </span>
                <span class="d-flex justify-content-between align-items-center gap-2">
                    <span class="chat-room-preview chat-truncate">{{ $preview }}</span>
                    @if($unread)<span class="chat-unread">{{ $unread > 99 ? '99+' : $unread }}</span>@endif
                </span>
            </span>
        </button>
    @empty
        <div class="px-3 py-1 small text-muted">
            {{ match ($title) { 'Departments' => 'Your department channel appears here.', 'Groups' => 'No groups yet.', default => 'Search for a colleague to start chatting.' } }}
        </div>
    @endforelse
@endforeach
