<div x-data @chat-url.window="history.replaceState(null, '', $event.detail.url)">
    <div class="chat-workspace {{ $activeConversationId ? 'has-active' : '' }}">
        {{-- Left: search + rooms --}}
        <nav class="chat-nav" aria-label="Conversations">
            <div class="p-3 pb-2">
                <div class="d-flex align-items-center justify-content-between mb-2">
                    <h5 class="mb-0">Chat</h5>
                    <button type="button" class="btn btn-sm btn-primary" wire:click="openNewGroup" title="New group"><i class="fa-solid fa-pen-to-square me-1"></i>New group</button>
                </div>
                @include('livewire.chat.partials.search', ['focusShortcut' => true])
            </div>
            <div class="chat-nav-scroll">
                @include('livewire.chat.partials.conversation-list', ['conversations' => $conversations, 'activeId' => $activeConversationId, 'openAction' => 'openConversation', 'compact' => false])
            </div>
        </nav>

        {{-- Centre + right: the open conversation --}}
        <main class="chat-main">
            @if($activeConversationId)
                <livewire:chat.conversation-pane :conversation-id="$activeConversationId" :key="'ws-'.$activeConversationId" />
            @else
                <div class="chat-empty">
                    <span class="chat-room-icon mb-2" style="width: 72px; height: 72px; border-radius: 20px; font-size: 1.8rem;"><i class="fa-regular fa-comments"></i></span>
                    <h5 class="mb-0 text-body">Welcome to Workspace Chat</h5>
                    <p class="mb-2" style="max-width: 360px;">Pick a conversation, search for a colleague, or start a group for your project.</p>
                    <div class="d-flex gap-2">
                        <button type="button" class="btn btn-primary btn-sm" wire:click="openNewGroup"><i class="fa-solid fa-users me-1"></i>New group</button>
                    </div>
                    <div class="small mt-3"><kbd>Ctrl</kbd> + <kbd>K</kbd> to search · <kbd>Enter</kbd> to send · <kbd>↑</kbd> to edit your last message</div>
                </div>
            @endif
        </main>
    </div>

    {{-- New group --}}
    @if($showNewGroup)
        <div class="modal fade show d-block" tabindex="-1" style="background: rgba(0,0,0,0.5);" @keydown.escape.window="$wire.closeNewGroup()">
            <div class="modal-dialog modal-dialog-centered">
                <div class="modal-content">
                    <div class="modal-header">
                        <h5 class="modal-title"><i class="fa-solid fa-users me-2"></i>New group</h5>
                        <button type="button" class="btn-close" wire:click="closeNewGroup"></button>
                    </div>
                    <form wire:submit="createGroup">
                        <div class="modal-body">
                            <div class="mb-3">
                                <label class="form-label">Group name <span class="text-danger">*</span></label>
                                <input type="text" class="form-control @error('groupName') is-invalid @enderror" wire:model="groupName" maxlength="120" placeholder="e.g. Payroll migration taskforce" autofocus>
                                @error('groupName') <div class="invalid-feedback">{{ $message }}</div> @enderror
                            </div>
                            <div class="mb-3">
                                <label class="form-label">Purpose</label>
                                <input type="text" class="form-control" wire:model="groupTopic" maxlength="500" placeholder="What is this group for?">
                            </div>
                            <label class="form-label">Members <span class="text-danger">*</span></label>
                            <div class="d-flex flex-wrap gap-1 mb-2">
                                @foreach($groupMembers as $id => $name)
                                    <span class="badge rounded-pill bg-primary-subtle text-primary-emphasis border d-inline-flex align-items-center gap-1 py-2" wire:key="gm-{{ $id }}">
                                        {{ $name }}
                                        <button type="button" class="btn-close" style="font-size: 0.5rem;" wire:click="removeGroupMember('{{ $id }}')" aria-label="Remove {{ $name }}"></button>
                                    </span>
                                @endforeach
                            </div>
                            <div class="position-relative">
                                <input type="search" class="form-control @error('groupMembers') is-invalid @enderror" placeholder="Search by name, title or department…" wire:model.live.debounce.250ms="groupSearch">
                                @error('groupMembers') <div class="invalid-feedback">{{ $message }}</div> @enderror
                                @if($groupCandidates->isNotEmpty())
                                    <div class="chat-search-results">
                                        @foreach($groupCandidates as $person)
                                            <button type="button" class="chat-room" wire:key="gc-{{ $person['user_id'] }}" wire:click="addGroupMember('{{ $person['user_id'] }}')">
                                                <span class="chat-avatar" style="width: 32px; height: 32px; font-size: 12px;">@if($person['avatar'])<img src="{{ $person['avatar'] }}" alt="">@else{{ $person['initials'] }}@endif</span>
                                                <span class="chat-truncate"><span class="d-block small fw-semibold">{{ $person['name'] }}</span><span class="d-block small text-muted chat-truncate">{{ $person['job_title'] }} · {{ $person['department'] }}</span></span>
                                                <i class="fa-solid fa-plus ms-auto text-primary"></i>
                                            </button>
                                        @endforeach
                                    </div>
                                @endif
                            </div>
                            <div class="form-text">Up to {{ config('chat.group_member_cap') }} members. You can add more later.</div>
                        </div>
                        <div class="modal-footer">
                            <button type="button" class="btn btn-secondary" wire:click="closeNewGroup">Cancel</button>
                            <button type="submit" class="btn btn-primary"><i class="fa-solid fa-check me-1"></i>Create group</button>
                        </div>
                    </form>
                </div>
            </div>
        </div>
    @endif
</div>
