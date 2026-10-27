{{-- Global directory search box + results. Host uses SearchesDirectory. --}}
@php $results = mb_strlen(trim($search)) >= config('chat.search_min_chars') ? $this->searchResults : null; @endphp
<div class="chat-search" x-data @keydown.escape="$wire.clearSearch()">
    <div class="position-relative">
        <i class="fa-solid fa-magnifying-glass position-absolute text-muted" style="left: 12px; top: 50%; transform: translateY(-50%); font-size: 0.85rem;"></i>
        <input type="search" class="form-control ps-5 rounded-3" placeholder="Search people, departments, groups…"
               wire:model.live.debounce.200ms="search" x-ref="search" autocomplete="off"
               @if($focusShortcut ?? false) @keydown.window.ctrl.k.prevent="$refs.search.focus()" @keydown.window.meta.k.prevent="$refs.search.focus()" @endif
               aria-label="Search the employee directory">
        @if($focusShortcut ?? false)
            <kbd class="position-absolute small d-none d-md-inline" style="right: 10px; top: 50%; transform: translateY(-50%); opacity: 0.6;">Ctrl K</kbd>
        @endif
    </div>

    @if($notice)
        <div class="alert alert-warning py-2 px-3 small mt-2 mb-0 d-flex justify-content-between">
            {{ $notice }}<button type="button" class="btn-close btn-sm" wire:click="$set('notice', null)"></button>
        </div>
    @endif

    @if(mb_strlen(trim($search)) > 0)
        <div class="chat-search-results">
            @if($results === null)
                <div class="p-3 small text-muted">Type at least {{ config('chat.search_min_chars') }} characters…</div>
            @else
                <div wire:loading.delay wire:target="search" class="p-3"><div class="chat-skeleton w-75 mb-2"></div><div class="chat-skeleton w-50"></div></div>
                <div wire:loading.remove wire:target="search">
                    @if($results['people']->isNotEmpty())
                        <div class="chat-section-title">People</div>
                        @foreach($results['people'] as $person)
                            <div class="chat-person" wire:key="person-{{ $person['user_id'] }}">
                                <button type="button" class="d-flex gap-2 align-items-center border-0 bg-transparent p-0 text-start flex-fill chat-truncate" wire:click="openPerson('{{ $person['user_id'] }}')" title="Message {{ $person['name'] }}">
                                    <span class="chat-avatar" style="width: 38px; height: 38px; font-size: 14px;">
                                        @if($person['avatar'])<img src="{{ $person['avatar'] }}" alt="">@else{{ $person['initials'] }}@endif
                                        <span class="chat-presence" x-data :class="$store.presence.status('{{ $person['user_id'] }}')"></span>
                                    </span>
                                    <span class="chat-truncate">
                                        <span class="d-block fw-semibold chat-truncate" style="font-size: 0.88rem;">{{ $person['name'] }}</span>
                                        <span class="d-block small text-muted chat-truncate">{{ $person['job_title'] ?? '' }}{{ $person['job_title'] && $person['email'] ? ' · ' : '' }}{{ $person['email'] }}</span>
                                    </span>
                                </button>
                                @if($person['department'])
                                    <button type="button" class="chat-dept-tag flex-shrink-0" wire:click="openDepartmentChannel('{{ $person['department_id'] }}')" title="Open the {{ $person['department'] }} channel">{{ $person['department'] }}</button>
                                @endif
                                <div class="chat-person-actions flex-shrink-0">
                                    <button type="button" class="chat-icon-btn" wire:click="openPerson('{{ $person['user_id'] }}')" title="Message"><i class="fa-regular fa-comment"></i></button>
                                    @if(\Illuminate\Support\Facades\Route::has('chat.call.start'))
                                        <button type="button" class="chat-icon-btn" x-data @click="window.dispatchEvent(new CustomEvent('chat-call', { detail: { userId: '{{ $person['user_id'] }}', type: 'audio' } }))" title="Start call"><i class="fa-solid fa-phone"></i></button>
                                    @endif
                                    @if(auth()->user()->isSuperAdmin() || auth()->user()->can('create-tickets'))
                                        <a class="chat-icon-btn" href="{{ route('tickets.create', ['assignee' => $person['employee_id']]) }}" title="Assign a ticket"><i class="fa-solid fa-ticket"></i></a>
                                    @endif
                                    @if(auth()->user()->isSuperAdmin() || auth()->user()->can('view-staff'))
                                        <a class="chat-icon-btn" href="{{ route('hr.staffdetails', $person['employee_id']) }}" title="View profile"><i class="fa-regular fa-id-card"></i></a>
                                    @endif
                                </div>
                            </div>
                        @endforeach
                    @endif

                    @if($results['rooms']->isNotEmpty())
                        <div class="chat-section-title">Channels &amp; groups</div>
                        @foreach($results['rooms'] as $room)
                            <button type="button" class="chat-room" wire:key="sr-{{ $room->id }}" wire:click="openConversation('{{ $room->id }}')">
                                <span class="chat-room-icon"><i class="fa-solid {{ $room->type === 'department' ? 'fa-building' : 'fa-users' }}"></i></span>
                                <span class="chat-truncate"><span class="d-block chat-room-name">{{ $room->name }}</span><span class="d-block chat-room-preview chat-truncate">{{ $room->topic }}</span></span>
                            </button>
                        @endforeach
                    @endif

                    @if($results['people']->isEmpty() && $results['rooms']->isEmpty())
                        <div class="p-3 small text-muted text-center"><i class="fa-regular fa-face-meh me-1"></i>No one matches “{{ $search }}”.</div>
                    @endif
                </div>
            @endif
        </div>
    @endif
</div>
