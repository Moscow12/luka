<div class="chat-launcher" x-data="chatLauncher({ hideBubble: @js($hideBubble) })" @chat-dock-active.window="remember($event.detail.id)">

    {{-- Toasts for new messages (browser-only) --}}
    <div class="chat-toasts" wire:ignore x-show="!open && toasts.length" x-cloak>
        <template x-for="t in toasts" :key="t.key">
            <div class="chat-toast" role="status" x-transition.opacity @click="openConversation(t.conversation_id)">
                <span class="chat-avatar" style="width: 40px; height: 40px; font-size: 14px;">
                    <template x-if="t.sender_avatar"><img :src="t.sender_avatar" alt=""></template>
                    <template x-if="!t.sender_avatar"><span x-text="initials(t.sender_name)"></span></template>
                </span>
                <div class="flex-fill chat-truncate">
                    <div class="d-flex justify-content-between gap-2">
                        <strong class="small chat-truncate" x-text="t.title"></strong>
                        <button type="button" class="btn-close" style="font-size: 0.6rem;" @click.stop="dismiss(t.key)" aria-label="Dismiss"></button>
                    </div>
                    <div class="small text-muted chat-truncate" x-text="(t.sender_name && t.title !== t.sender_name ? t.sender_name.split(' ')[0] + ': ' : '') + (t.preview ?? '')"></div>
                    <div class="mt-1">
                        <button type="button" class="btn btn-sm btn-primary py-0 px-2" style="font-size: 0.75rem;" @click.stop="openConversation(t.conversation_id)"><i class="fa-solid fa-reply me-1"></i>Reply</button>
                    </div>
                </div>
            </div>
        </template>
    </div>

    {{-- Dock --}}
    <div class="chat-dock" x-show="open" x-cloak x-transition.origin.bottom.right role="dialog" aria-label="Chat">
        @if(! $loaded)
            <div class="p-3">
                <div class="chat-skeleton w-50 mb-3"></div>
                @for($i = 0; $i < 5; $i++)
                    <div class="d-flex gap-2 align-items-center mb-3"><div class="chat-skeleton rounded-circle" style="width: 36px; height: 36px;"></div><div class="flex-fill"><div class="chat-skeleton w-75 mb-1"></div><div class="chat-skeleton w-50"></div></div></div>
                @endfor
            </div>
        @elseif($activeConversationId)
            <livewire:chat.conversation-pane :conversation-id="$activeConversationId" :compact="true" :key="'dock-'.$activeConversationId" />
        @else
            <div class="chat-dock-header">
                <h6 class="mb-0 flex-fill"><i class="fa-regular fa-comments me-2 text-primary"></i>Chats</h6>
                <button type="button" class="chat-icon-btn" :class="sound && 'active'" @click="toggleSound()" :title="sound ? 'Sound on' : 'Sound off'"><i class="fa-solid" :class="sound ? 'fa-volume-high' : 'fa-volume-xmark'"></i></button>
                <button type="button" class="chat-icon-btn" :class="desktop && 'active'" @click="toggleDesktop()" :title="desktop ? 'Desktop notifications on' : 'Turn on desktop notifications'"><i class="fa-solid fa-desktop"></i></button>
                <a href="{{ route('chat.index') }}" class="chat-icon-btn" title="Open full workspace"><i class="fa-solid fa-up-right-and-down-left-from-center"></i></a>
                <button type="button" class="chat-icon-btn" @click="close()" title="Close (Esc)"><i class="fa-solid fa-xmark"></i></button>
            </div>
            <div class="p-2">
                @include('livewire.chat.partials.search')
            </div>
            <div class="flex-fill overflow-auto px-1 pb-2">
                @include('livewire.chat.partials.conversation-list', ['conversations' => $conversations, 'activeId' => null, 'openAction' => 'openConversation', 'compact' => true])
            </div>
            <div class="border-top p-2 d-flex gap-2">
                <a href="{{ route('chat.index', ['new' => 'group']) }}" class="btn btn-sm btn-light flex-fill"><i class="fa-solid fa-users me-1"></i>New group</a>
                <a href="{{ route('chat.index') }}" class="btn btn-sm btn-light flex-fill"><i class="fa-solid fa-table-columns me-1"></i>Full workspace</a>
            </div>
        @endif
    </div>

    {{-- Floating button --}}
    @unless($hideBubble)
        <button type="button" class="chat-fab position-relative" :class="pulse && 'pulse'" @click="toggle()"
                :aria-expanded="open" aria-label="Chat{{ $totalUnread ? ', '.$totalUnread.' unread' : '' }}" title="Chat">
            <i class="fa-solid" :class="open ? 'fa-xmark' : 'fa-comments'"></i>
            @if($totalUnread > 0)
                <span class="chat-fab-badge" x-show="!open">{{ $totalUnread > 99 ? '99+' : $totalUnread }}</span>
            @endif
        </button>
    @endunless
</div>
