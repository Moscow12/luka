{{--
    One text message as a WhatsApp-style bubble: mine on the right (tinted), others on the left.
    Expects $m, $grouped, $mine, $renderer, $canModerate, $editingId, $isDirect, $readPositions
--}}
@php
    $editable = $mine && $m->isEditable();
    $reactions = $m->reactions->groupBy('emoji');
    $emojiOnly = ! $m->trashed() && $m->attachments->isEmpty() && ! $m->replyTo && \App\Services\Chat\MessageRenderer::isEmojiOnly($m->body);
    $showAvatar = ! $mine && ! $isDirect;
    $readers = $mine ? \App\Livewire\Chat\ConversationPane::readersOf($m, $readPositions) : 0;
@endphp
<div class="chat-msg {{ $mine ? 'mine' : 'theirs' }} {{ $grouped ? 'grouped' : 'first' }} {{ $m->pinned_at ? 'pinned' : '' }}"
     wire:key="m-{{ $m->id }}" id="chat-msg-{{ $m->id }}"
     data-message-id="{{ $m->id }}" data-mine="{{ $mine ? 1 : 0 }}" @if($editable) data-editable="1" @endif>

    @if($showAvatar)
        <div class="chat-gutter">
            @unless($grouped)<x-chat.avatar :user="$m->user" :size="30" />@endunless
        </div>
    @endif

    <div class="chat-bubble-wrap">
        @if($showAvatar && ! $grouped)
            <div class="chat-sender">{{ $m->user?->full_name ?? 'Former member' }}</div>
        @endif

        <div class="chat-bubble {{ $emojiOnly ? 'emoji-only' : '' }} {{ $m->trashed() ? 'deleted' : '' }}">
            @if($m->trashed())
                <span class="chat-deleted"><i class="fa-solid fa-ban me-1"></i>{{ $mine ? 'You deleted this message' : 'This message was deleted' }}</span>
            @else
                @if($m->pinned_at)
                    <div class="chat-pinned-label"><i class="fa-solid fa-thumbtack me-1"></i>Pinned</div>
                @endif

                @if($m->replyTo)
                    <a href="#chat-msg-{{ $m->replyTo->id }}" class="chat-reply-quote d-block text-decoration-none">
                        <strong class="d-block">{{ $m->replyTo->user_id === auth()->id() ? 'You' : $m->replyTo->user?->first_name }}</strong>
                        <span class="chat-truncate d-block">{{ $m->replyTo->trashed() ? 'Deleted message' : (\App\Support\HtmlSanitizer::toText($renderer->render($m->replyTo), 80) ?: '📎 Attachment') }}</span>
                    </a>
                @endif

                @if($editingId === $m->id)
                    <div class="chat-edit">
                        <textarea class="form-control form-control-sm" rows="2" wire:model="editBody"
                                  x-init="$el.focus(); $el.setSelectionRange($el.value.length, $el.value.length)"
                                  @keydown.enter.prevent.exact="$wire.saveEdit()" @keydown.escape.prevent="$wire.cancelEdit()"></textarea>
                        <div class="small text-muted mt-1">
                            Enter to <a href="#" wire:click.prevent="saveEdit">save</a> · Esc to <a href="#" wire:click.prevent="cancelEdit">cancel</a>
                        </div>
                    </div>
                @else
                    @foreach($m->attachments as $file)
                        @if($file->isImage())
                            <a href="{{ route('chat.attachment', $file) }}" target="_blank" rel="noopener" class="d-block">
                                <img src="{{ route('chat.attachment', ['attachment' => $file, 'inline' => 1]) }}" class="chat-image" alt="{{ $file->original_name }}" loading="lazy"
                                     @if($file->width && $file->height) width="{{ min(320, $file->width) }}" style="aspect-ratio: {{ $file->width }} / {{ $file->height }};" @endif>
                            </a>
                        @else
                            <a href="{{ route('chat.attachment', $file) }}" class="chat-file" target="_blank" rel="noopener">
                                <span class="chat-file-ext">{{ Str::limit($file->extension(), 4, '') }}</span>
                                <span class="chat-truncate">
                                    <span class="d-block chat-truncate small fw-semibold">{{ $file->original_name }}</span>
                                    <span class="d-block small text-muted">{{ $file->humanSize() }} · {{ $file->extension() }}</span>
                                </span>
                                <i class="fa-solid fa-download ms-auto text-muted"></i>
                            </a>
                        @endif
                    @endforeach

                    @if(trim((string) $m->body) !== '')
                        <div class="chat-text {{ $emojiOnly ? 'emoji-only' : '' }}">{!! $renderer->render($m) !!}</div>
                    @endif
                @endif
            @endif

            {{-- Time, edited label and ticks sit inside the bubble, bottom-right --}}
            <span class="chat-bubble-meta">
                @if($m->edited_at && ! $m->trashed())<span title="Edited {{ $m->edited_at->format('d M H:i') }}">edited</span>@endif
                <span title="{{ $m->created_at->format('d M Y H:i') }}">{{ $m->created_at->format('H:i') }}</span>
                @if($mine && ! $m->trashed())
                    <i class="fa-solid {{ $readers > 0 ? 'fa-check-double chat-seen' : 'fa-check' }}"
                       title="{{ $readers > 0 ? ($isDirect ? 'Seen' : 'Seen by '.$readers) : 'Sent' }}"></i>
                @endif
            </span>
        </div>

        @if($reactions->isNotEmpty() && ! $m->trashed())
            <div class="chat-reactions">
                @foreach($reactions as $emoji => $group)
                    <button type="button" class="chat-reaction {{ $group->contains('user_id', auth()->id()) ? 'mine' : '' }}"
                            wire:click="react('{{ $m->id }}', '{{ $emoji }}')" title="{{ $group->count() }} reaction{{ $group->count() > 1 ? 's' : '' }}">
                        <span>{{ $emoji }}</span>@if($group->count() > 1)<span class="small">{{ $group->count() }}</span>@endif
                    </button>
                @endforeach
            </div>
        @endif

        @unless($m->trashed() || $editingId === $m->id)
            <div class="chat-actions" role="toolbar" aria-label="Message actions">
                @foreach(['👍', '❤️', '😂'] as $quick)
                    <button type="button" class="chat-icon-btn" wire:click="react('{{ $m->id }}', '{{ $quick }}')" title="React {{ $quick }}">{{ $quick }}</button>
                @endforeach
                <button type="button" class="chat-icon-btn" @click="toggleEmoji('{{ $m->id }}')" title="More reactions"><i class="fa-regular fa-face-smile"></i></button>
                <button type="button" class="chat-icon-btn" wire:click="setReply('{{ $m->id }}')" title="Reply"><i class="fa-solid fa-reply"></i></button>
                <button type="button" class="chat-icon-btn" @click="copy('{{ $m->id }}')" title="Copy text"><i class="fa-regular fa-copy"></i></button>
                @if($editable)
                    <button type="button" class="chat-icon-btn" wire:click="startEdit('{{ $m->id }}')" title="Edit"><i class="fa-solid fa-pen"></i></button>
                    <button type="button" class="chat-icon-btn" wire:click="deleteMessage('{{ $m->id }}')" wire:confirm="Delete this message for everyone?" title="Delete"><i class="fa-regular fa-trash-can"></i></button>
                @endif
                @if($canModerate)
                    <button type="button" class="chat-icon-btn {{ $m->pinned_at ? 'active' : '' }}" wire:click="togglePin('{{ $m->id }}')" title="{{ $m->pinned_at ? 'Unpin' : 'Pin' }}"><i class="fa-solid fa-thumbtack"></i></button>
                @endif
            </div>
        @endunless
    </div>
</div>
