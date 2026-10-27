@php
    use Illuminate\Support\Carbon;

    $renderer = app(\App\Services\Chat\MessageRenderer::class);
    $meId = auth()->id();
    $marker = $readMarker ? Carbon::parse($readMarker) : null;
    $lastMine = $messages->where('user_id', $meId)->where('type', 'text')->whereNull('deleted_at')->last();
    $title = $conversation->displayName($meId);
    $placeholder = $conversation->isDirect() ? 'Message '.($other?->first_name ?? $title) : 'Message '.($conversation->isDepartment() ? '#' : '').$title;
@endphp
<div class="d-flex flex-fill position-relative" style="min-height: 0; min-width: 0;"
     x-data="chatPane('{{ $conversation->id }}')"
     @dragover.prevent="dragging = true" @dragleave.self="dragging = false" @drop.prevent="onDrop($event)">

    <section class="chat-pane" aria-label="{{ $title }}">
        {{-- Header --}}
        <header class="chat-pane-header">
            @if($compact)
                <button type="button" class="chat-icon-btn" wire:click="$parent.closeConversation" title="Back"><i class="fa-solid fa-arrow-left"></i></button>
            @else
                <button type="button" class="chat-icon-btn d-md-none" wire:click="$parent.closeConversation" title="Back"><i class="fa-solid fa-arrow-left"></i></button>
            @endif

            @include('livewire.chat.partials.room-icon', ['conversation' => $conversation, 'size' => $compact ? 32 : 40])

            <button type="button" class="border-0 bg-transparent p-0 text-start flex-fill chat-truncate" wire:click="toggleDetails('members')" title="Room details">
                <div class="fw-semibold chat-truncate">
                    @if($conversation->isDepartment())<span class="text-muted">#</span>@endif{{ $title }}
                    @if($conversation->status === 'archived')<span class="badge bg-secondary ms-1">Archived</span>@endif
                </div>
                <div class="small text-muted chat-truncate">
                    @if($other)
                        <span x-data x-text="$store.presence.status('{{ $other->id }}') === 'offline' ? @js($other->last_seen_at ? 'Last seen '.$other->last_seen_at->diffForHumans() : 'Offline') : $store.presence.label('{{ $other->id }}')"></span>
                    @else
                        {{ $conversation->members_count }} {{ Str::plural('member', $conversation->members_count) }}@if($conversation->topic) · {{ $conversation->topic }}@endif
                    @endif
                </div>
            </button>

            @if(\Illuminate\Support\Facades\Route::has('chat.call.start') && $other)
                <button type="button" class="chat-icon-btn" @click="window.dispatchEvent(new CustomEvent('chat-call', { detail: { userId: '{{ $other->id }}', type: 'audio' } }))" title="Voice call"><i class="fa-solid fa-phone"></i></button>
                <button type="button" class="chat-icon-btn" @click="window.dispatchEvent(new CustomEvent('chat-call', { detail: { userId: '{{ $other->id }}', type: 'video' } }))" title="Video call"><i class="fa-solid fa-video"></i></button>
            @endif

            @if($compact)
                <a href="{{ route('chat.index', $conversation) }}" class="chat-icon-btn" title="Open in full workspace"><i class="fa-solid fa-up-right-and-down-left-from-center"></i></a>
            @endif
            <button type="button" class="chat-icon-btn {{ $showDetails ? 'active' : '' }}" wire:click="toggleDetails" title="Details"><i class="fa-solid fa-circle-info"></i></button>
        </header>

        @if($notice)
            <div class="alert alert-warning alert-dismissible py-2 px-3 small m-2 mb-0">
                {{ $notice }}<button type="button" class="btn-close btn-sm py-2" wire:click="$set('notice', null)"></button>
            </div>
        @endif

        {{-- Message stream --}}
        <div class="chat-stream" x-ref="stream" role="log" aria-live="polite">
            @if($hasMore)
                <div class="py-2 px-2" aria-hidden="true">
                    <div class="chat-skeleton w-50 mb-2"></div>
                    <div class="chat-skeleton w-25"></div>
                </div>
            @elseif($messages->isNotEmpty())
                <div class="text-center small text-muted py-3">
                    <i class="fa-regular fa-comments me-1"></i>This is the beginning of {{ $conversation->isDirect() ? 'your conversation with '.$title : $title }}.
                </div>
            @endif

            @forelse($messages as $m)
                @php
                    $newDay = ! isset($prev) || ! $prev->created_at->isSameDay($m->created_at);
                    $showDivider = empty($dividerShown) && $marker && $m->user_id !== $meId && $m->type === 'text' && $m->created_at->gt($marker);
                    if ($showDivider) {
                        $dividerShown = true;
                    }
                    $grouped = isset($prev) && ! $newDay && ! $showDivider && $prev->type === 'text' && $m->type === 'text'
                        && $prev->user_id === $m->user_id && $prev->created_at->diffInMinutes($m->created_at) < 5;

                    $receipt = null;
                    if ($lastMine && $lastMine->id === $m->id) {
                        $readers = \App\Livewire\Chat\ConversationPane::readersOf($m, $readPositions);
                        $receipt = $conversation->isDirect()
                            ? ($readers > 0 ? 'Seen' : 'Sent')
                            : ($readers > 0 ? 'Seen by '.$readers : 'Sent');
                    }
                @endphp

                @if($newDay)
                    <div class="chat-day" wire:key="day-{{ $m->created_at->toDateString() }}">
                        <span>{{ $m->created_at->isToday() ? 'Today' : ($m->created_at->isYesterday() ? 'Yesterday' : $m->created_at->format('l, d M Y')) }}</span>
                    </div>
                @endif
                @if($showDivider)
                    <div class="chat-new-divider" data-new-divider wire:key="new-divider">New messages</div>
                @endif

                @if($m->type === 'text')
                    @include('livewire.chat.partials.message', ['m' => $m, 'grouped' => $grouped, 'mine' => $m->user_id === $meId, 'receipt' => $receipt])
                @else
                    <div class="chat-system" wire:key="m-{{ $m->id }}" data-message-id="{{ $m->id }}">
                        <i class="fa-solid {{ $m->type === 'call' ? 'fa-phone' : 'fa-circle-info' }} me-1"></i>{{ $m->body }} · {{ $m->created_at->format('H:i') }}
                    </div>
                @endif
                @php $prev = $m; @endphp
            @empty
                <div class="chat-empty">
                    @if($other)
                        <x-chat.avatar :user="$other" :size="64" :presence="true" />
                        <div class="fw-semibold mt-2">{{ $other->full_name }}</div>
                        <div class="small">Say hello 👋 — messages here are private between the two of you.</div>
                    @else
                        <i class="fa-regular fa-comments fa-3x opacity-50"></i>
                        <div class="fw-semibold">No messages yet</div>
                        <div class="small">Start the conversation in {{ $title }}.</div>
                    @endif
                </div>
            @endforelse

            {{-- Browser-only: messages being sent right now --}}
            <div wire:ignore>
                <template x-for="p in pending" :key="p.key">
                    <div class="chat-msg chat-pending" :class="p.failed && 'failed'">
                        <div class="chat-gutter"></div>
                        <div class="flex-fill" style="min-width: 0;">
                            <div class="chat-text" style="white-space: pre-wrap;" x-text="p.text"></div>
                            <div class="small text-muted" x-show="!p.failed"><span class="spinner-border spinner-border-sm me-1" style="width: 0.7rem; height: 0.7rem;"></span>Sending…</div>
                            <div class="small text-danger" x-show="p.failed">
                                <i class="fa-solid fa-circle-exclamation me-1"></i><span x-text="p.error"></span>
                                <button type="button" class="btn btn-link btn-sm p-0 ms-2" @click="retry(p)">Retry</button>
                                <button type="button" class="btn btn-link btn-sm p-0 ms-2 text-muted" @click="discard(p)">Discard</button>
                            </div>
                        </div>
                    </div>
                </template>
            </div>
        </div>

        {{-- Browser-only overlays: typing, jump to latest, flash, drop zone, emoji picker --}}
        <div wire:ignore>
            <div class="chat-typing" aria-live="polite">
                <template x-if="typingText">
                    <span><span x-text="typingText"></span><span class="chat-typing-dots ms-1"><span></span><span></span><span></span></span></span>
                </template>
            </div>
            <button type="button" class="btn btn-primary btn-sm chat-jump" x-show="newCount > 0" x-cloak @click="scrollToBottom()">
                <i class="fa-solid fa-arrow-down me-1"></i><span x-text="newCount"></span> new
            </button>
            <div class="position-absolute start-50 translate-middle-x" style="bottom: 130px; z-index: 6;" x-show="error" x-cloak x-transition>
                <div class="alert py-2 px-3 small shadow mb-0" :class="error?.kind === 'info' ? 'alert-info' : 'alert-danger'" x-text="error?.message"></div>
            </div>
            <div class="chat-drop" x-show="dragging" x-cloak>
                <i class="fa-solid fa-cloud-arrow-up fa-2x"></i>Drop files to attach
            </div>
            <div class="chat-emoji-pop" x-show="emojiFor" x-cloak @click.outside="emojiFor = null" @keydown.escape.window="emojiFor = null">
                <emoji-picker @emoji-click="pickEmoji($event)" :class="document.documentElement.getAttribute('data-bs-theme') === 'dark' ? 'dark' : 'light'"></emoji-picker>
            </div>
        </div>

        {{-- Composer --}}
        @if($canPost)
            <div class="chat-composer">
                @if($replyTo)
                    <div class="d-flex align-items-center gap-2 px-3 pt-2 small">
                        <i class="fa-solid fa-reply text-primary"></i>
                        <span class="chat-truncate flex-fill text-muted">Replying to <strong>{{ $replyTo->user?->first_name }}</strong>: {{ \App\Support\HtmlSanitizer::toText($renderer->render($replyTo), 80) }}</span>
                        <button type="button" class="chat-icon-btn" style="width: 24px; height: 24px;" wire:click="cancelReply" title="Cancel reply"><i class="fa-solid fa-xmark"></i></button>
                    </div>
                @endif

                @if(count($uploads))
                    <div class="chat-staged">
                        @foreach($uploads as $i => $file)
                            <div class="chat-staged-item" wire:key="up-{{ $i }}-{{ $file->getFilename() }}">
                                @if(str_starts_with((string) $file->getMimeType(), 'image/') && $file->isPreviewable())
                                    <img src="{{ $file->temporaryUrl() }}" alt="">
                                @else
                                    <span class="chat-file-ext" style="width: 40px; height: 40px;">{{ strtoupper(Str::limit($file->getClientOriginalExtension(), 4, '')) }}</span>
                                @endif
                                <span class="chat-truncate">{{ $file->getClientOriginalName() }}</span>
                                <button type="button" class="chat-icon-btn" style="width: 20px; height: 20px;" wire:click="removeUpload({{ $i }})" title="Remove"><i class="fa-solid fa-xmark small"></i></button>
                            </div>
                        @endforeach
                    </div>
                @endif
                @error('uploads.*') <div class="text-danger small px-3 pt-1">{{ $message }}</div> @enderror
                @error('newUploads.*') <div class="text-danger small px-3 pt-1">{{ $message }}</div> @enderror

                <div wire:ignore>
                    <textarea x-ref="input" x-model="body" rows="1" placeholder="{{ $placeholder }}" aria-label="{{ $placeholder }}"
                              @input="onInput()" @keydown="onKeydown($event)" @paste="onPaste($event)"></textarea>
                    <div class="chat-composer-bar">
                        @unless($compact)
                            <button type="button" class="chat-icon-btn" @click="wrap('**')" title="Bold (Markdown **text**)"><i class="fa-solid fa-bold"></i></button>
                            <button type="button" class="chat-icon-btn" @click="wrap('_')" title="Italic"><i class="fa-solid fa-italic"></i></button>
                            <button type="button" class="chat-icon-btn" @click="wrap('~~')" title="Strikethrough"><i class="fa-solid fa-strikethrough"></i></button>
                            <button type="button" class="chat-icon-btn" @click="wrap('`', '`', 'code')" title="Code"><i class="fa-solid fa-code"></i></button>
                            <button type="button" class="chat-icon-btn" @click="prefixLines('- ')" title="Bulleted list"><i class="fa-solid fa-list-ul"></i></button>
                            <button type="button" class="chat-icon-btn" @click="wrap('[', '](https://)', 'link text')" title="Link"><i class="fa-solid fa-link"></i></button>
                            <span class="vr mx-1 my-2"></span>
                        @endunless
                        <button type="button" class="chat-icon-btn" @click="toggleEmoji('composer')" title="Emoji"><i class="fa-regular fa-face-smile"></i></button>
                        <label class="chat-icon-btn mb-0" title="Attach files (or drag & drop / paste)" style="cursor: pointer;">
                            <i class="fa-solid fa-paperclip"></i>
                            <input type="file" class="d-none" multiple accept=".pdf,.doc,.docx,.xls,.xlsx,.ppt,.pptx,.png,.jpg,.jpeg,.gif,.webp,.csv,.txt" @change="upload($event.target.files); $event.target.value = ''">
                        </label>
                        @if(auth()->user()->isSuperAdmin() || auth()->user()->can('create-tickets'))
                            <a class="chat-icon-btn" href="{{ route('tickets.create', array_filter(['subject' => Str::limit('Follow-up: '.$title, 140, ''), 'assignee' => $other ? \App\Models\Employee::where('user_id', $other->id)->value('id') : null])) }}" title="Create a ticket from this conversation"><i class="fa-solid fa-ticket"></i></a>
                        @endif
                        <span class="small text-muted ms-1" x-show="uploading" x-cloak><span class="spinner-border spinner-border-sm me-1" style="width: 0.7rem; height: 0.7rem;"></span><span x-text="uploadProgress + '%'"></span></span>
                        <span class="small text-muted ms-auto d-none d-lg-inline me-2" x-show="body.length > 0" x-cloak style="font-size: 0.7rem;">Shift+Enter for new line</span>
                        <button type="button" class="chat-send" :class="body.length > 0 ? '' : 'ms-auto'" @click="submit()" :disabled="uploading || (body.trim() === '' && ($wire.uploads?.length ?? 0) === 0)" title="Send (Enter)">
                            <i class="fa-solid fa-paper-plane"></i>
                        </button>
                    </div>
                </div>
            </div>
        @else
            <div class="text-center small text-muted p-3 border-top"><i class="fa-solid fa-box-archive me-1"></i>This room is archived and read-only.</div>
        @endif
    </section>

    {{-- Details panel --}}
    @if($showDetails)
        @include('livewire.chat.partials.details')
    @endif
</div>
