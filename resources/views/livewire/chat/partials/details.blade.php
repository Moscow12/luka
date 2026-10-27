{{-- Room details side panel (members, pinned, files, tickets, settings). Rendered inside ConversationPane. --}}
<aside class="chat-details" aria-label="Room details">
    <div class="d-flex align-items-center justify-content-between p-3 border-bottom">
        <h6 class="mb-0">Details</h6>
        <button type="button" class="chat-icon-btn" wire:click="toggleDetails" title="Close"><i class="fa-solid fa-xmark"></i></button>
    </div>

    <div class="p-3 border-bottom text-center">
        @include('livewire.chat.partials.room-icon', ['conversation' => $conversation, 'size' => 56])
        <div class="fw-semibold mt-2">{{ $title }}</div>
        @if($other)
            <div class="small text-muted">{{ $other->email }}</div>
        @elseif($conversation->topic)
            <div class="small text-muted">{{ $conversation->topic }}</div>
        @endif

        <div class="d-flex justify-content-center gap-2 mt-3">
            <button type="button" class="btn btn-sm {{ $me->muted === 'yes' ? 'btn-secondary' : 'btn-outline-secondary' }}" wire:click="togglePreference('muted')">
                <i class="fa-solid {{ $me->muted === 'yes' ? 'fa-bell-slash' : 'fa-bell' }} me-1"></i>{{ $me->muted === 'yes' ? 'Muted' : 'Mute' }}
            </button>
            <button type="button" class="btn btn-sm {{ $me->pinned === 'yes' ? 'btn-secondary' : 'btn-outline-secondary' }}" wire:click="togglePreference('pinned')">
                <i class="fa-solid fa-thumbtack me-1"></i>{{ $me->pinned === 'yes' ? 'Pinned' : 'Pin' }}
            </button>
        </div>
    </div>

    <ul class="nav nav-tabs nav-fill small px-2 pt-2">
        @foreach(['members' => $conversation->isDirect() ? 'People' : 'Members', 'pinned' => 'Pinned', 'files' => 'Files', 'tickets' => 'Tickets'] as $tab => $label)
            <li class="nav-item"><button type="button" class="nav-link py-1 {{ $detailsTab === $tab ? 'active' : '' }}" wire:click="$set('detailsTab', '{{ $tab }}')">{{ $label }}</button></li>
        @endforeach
    </ul>

    <div class="p-2">
        @if($detailsTab === 'members')
            @if($canManageMembers)
                <div class="position-relative mb-2">
                    <input type="search" class="form-control form-control-sm" placeholder="Add people…" wire:model.live.debounce.250ms="memberSearch">
                    @if(($memberCandidates ?? collect())->isNotEmpty())
                        <div class="chat-search-results">
                            @foreach($memberCandidates as $person)
                                <button type="button" class="chat-room" wire:key="cand-{{ $person['user_id'] }}" wire:click="addMember('{{ $person['user_id'] }}')">
                                    <span class="chat-avatar" style="width: 28px; height: 28px; font-size: 11px;">@if($person['avatar'])<img src="{{ $person['avatar'] }}" alt="">@else{{ $person['initials'] }}@endif</span>
                                    <span class="chat-truncate small">{{ $person['name'] }} <span class="text-muted">· {{ $person['department'] }}</span></span>
                                    <i class="fa-solid fa-plus ms-auto text-primary"></i>
                                </button>
                            @endforeach
                        </div>
                    @endif
                </div>
            @endif

            @foreach($members as $member)
                <div class="chat-person rounded-3" wire:key="mem-{{ $member->id }}">
                    <x-chat.avatar :user="$member->user" :size="32" :presence="true" />
                    <div class="flex-fill chat-truncate">
                        <div class="small fw-semibold chat-truncate">{{ $member->user?->full_name }} @if($member->user_id === auth()->id())<span class="text-muted fw-normal">(you)</span>@endif</div>
                        @if($member->role !== 'member')<div class="small text-muted">{{ $conversation->isDepartment() && $member->role === 'admin' ? 'Supervisor' : ucfirst($member->role) }}</div>@endif
                    </div>
                    @if($canManageMembers && $member->role !== 'owner' && $member->user_id !== auth()->id())
                        <div class="chat-person-actions">
                            <button type="button" class="chat-icon-btn" wire:click="setRole('{{ $member->user_id }}', '{{ $member->role === 'admin' ? 'member' : 'admin' }}')" title="{{ $member->role === 'admin' ? 'Remove admin' : 'Make admin' }}"><i class="fa-solid fa-user-shield"></i></button>
                            <button type="button" class="chat-icon-btn" wire:click="removeMember('{{ $member->user_id }}')" wire:confirm="Remove {{ $member->user?->first_name }} from the group?" title="Remove"><i class="fa-solid fa-user-minus"></i></button>
                        </div>
                    @endif
                </div>
            @endforeach
            @if($conversation->members_count > $members->count())
                <div class="small text-muted text-center py-2">and {{ $conversation->members_count - $members->count() }} more</div>
            @endif
            @if($conversation->isDepartment())
                <div class="small text-muted px-2 pt-2"><i class="fa-solid fa-circle-info me-1"></i>Membership follows HR records automatically.</div>
            @endif
        @elseif($detailsTab === 'pinned')
            @forelse($pinned as $p)
                <a href="#chat-msg-{{ $p->id }}" class="d-block chat-file mw-100 mb-2 small">
                    <span class="chat-truncate"><strong>{{ $p->user?->first_name }}</strong>: {{ \App\Support\HtmlSanitizer::toText($renderer->render($p), 120) ?: '📎 Attachment' }}</span>
                </a>
            @empty
                <div class="chat-empty small py-4"><i class="fa-solid fa-thumbtack fa-lg opacity-50"></i>Nothing pinned yet{{ $canModerate ? ' — hover a message and click the pin.' : '.' }}</div>
            @endforelse
        @elseif($detailsTab === 'files')
            @php $images = $files->filter->isImage(); $docs = $files->reject->isImage(); @endphp
            @if($images->isNotEmpty())
                <div class="d-grid gap-1 mb-2" style="grid-template-columns: repeat(3, 1fr);">
                    @foreach($images as $img)
                        <a href="{{ route('chat.attachment', $img) }}" target="_blank"><img src="{{ route('chat.attachment', ['attachment' => $img, 'inline' => 1]) }}" class="w-100 rounded-2" style="aspect-ratio: 1; object-fit: cover;" alt="{{ $img->original_name }}" loading="lazy"></a>
                    @endforeach
                </div>
            @endif
            @foreach($docs as $file)
                <a href="{{ route('chat.attachment', $file) }}" class="chat-file mw-100" target="_blank">
                    <span class="chat-file-ext">{{ Str::limit($file->extension(), 4, '') }}</span>
                    <span class="chat-truncate"><span class="d-block small fw-semibold chat-truncate">{{ $file->original_name }}</span><span class="d-block small text-muted">{{ $file->humanSize() }} · {{ $file->created_at->format('d M') }}</span></span>
                </a>
            @endforeach
            @if($files->isEmpty())
                <div class="chat-empty small py-4"><i class="fa-regular fa-folder-open fa-lg opacity-50"></i>No files shared yet.</div>
            @endif
        @elseif($detailsTab === 'tickets')
            @forelse($tickets as $ticket)
                <a href="{{ route('tickets.view', $ticket) }}" class="chat-file mw-100 mb-2" target="_blank">
                    <span class="chat-truncate">
                        <span class="d-block small fw-semibold">{{ $ticket->ticket_number }}</span>
                        <span class="d-block small text-muted chat-truncate">{{ $ticket->subject }}</span>
                    </span>
                    <span class="badge {{ $ticket->status->badgeClass() }} ms-auto">{{ $ticket->status->label() }}</span>
                </a>
            @empty
                <div class="chat-empty small py-4"><i class="fa-solid fa-ticket fa-lg opacity-50"></i>Ticket numbers mentioned here (like HRP-2026-00001) will be listed.</div>
            @endforelse
        @endif
    </div>

    @if($canModerate || $conversation->isGroup())
        <div class="p-3 border-top">
            @if($canModerate)
                <form wire:submit="saveDetails" class="mb-3">
                    @if($conversation->isGroup())
                        <label class="form-label small mb-1">Group name</label>
                        <input type="text" class="form-control form-control-sm mb-2" wire:model="editName" maxlength="120">
                    @endif
                    <label class="form-label small mb-1">Topic</label>
                    <input type="text" class="form-control form-control-sm mb-2" wire:model="editTopic" maxlength="500" placeholder="What is this room about?">
                    <button type="submit" class="btn btn-sm btn-outline-primary w-100">Save details</button>
                </form>
                <button type="button" class="btn btn-sm btn-outline-secondary w-100 mb-2" wire:click="toggleArchive" wire:confirm="{{ $conversation->status === 'active' ? 'Archive this room? It becomes read-only for everyone.' : 'Reopen this room?' }}">
                    <i class="fa-solid fa-box-archive me-1"></i>{{ $conversation->status === 'active' ? 'Archive room' : 'Reopen room' }}
                </button>
            @endif
            @if($conversation->isGroup())
                <button type="button" class="btn btn-sm btn-outline-danger w-100" wire:click="leave" wire:confirm="Leave this group? You will lose access to its history.">
                    <i class="fa-solid fa-right-from-bracket me-1"></i>Leave group
                </button>
            @endif
        </div>
    @endif
</aside>
