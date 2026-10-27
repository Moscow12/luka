{{-- One text message. Expects $m, $grouped, $mine, $renderer, $canModerate, $editingId, $receipt --}}
@php
    $editable = $mine && $m->isEditable();
    $reactions = $m->reactions->groupBy('emoji');
@endphp
<div class="chat-msg {{ $grouped ? '' : 'first' }} {{ $m->pinned_at ? 'pinned' : '' }}"
     wire:key="m-{{ $m->id }}" id="chat-msg-{{ $m->id }}"
     data-message-id="{{ $m->id }}" data-mine="{{ $mine ? 1 : 0 }}" @if($editable) data-editable="1" @endif>
    <div class="chat-gutter">
        @if($grouped)
            <div class="chat-time-hover">{{ $m->created_at->format('H:i') }}</div>
        @else
            <x-chat.avatar :user="$m->user" :size="36" />
        @endif
    </div>

    <div class="flex-fill" style="min-width: 0;">
        @unless($grouped)
            <div class="chat-meta">
                <strong>{{ $m->user?->full_name ?? 'Former member' }}</strong>
                <span class="ms-1" title="{{ $m->created_at->format('d M Y H:i') }}">{{ $m->created_at->format('H:i') }}</span>
                @if($m->pinned_at)<i class="fa-solid fa-thumbtack ms-1 text-warning" title="Pinned"></i>@endif
            </div>
        @endunless

        @if($m->trashed())
            <div class="chat-deleted"><i class="fa-solid fa-ban me-1"></i>This message was deleted</div>
        @else
            @if($m->replyTo)
                <a href="#chat-msg-{{ $m->replyTo->id }}" class="chat-reply-quote d-block text-decoration-none">
                    <i class="fa-solid fa-reply me-1"></i><strong>{{ $m->replyTo->user?->first_name }}</strong>:
                    {{ $m->replyTo->trashed() ? 'Deleted message' : \App\Support\HtmlSanitizer::toText($renderer->render($m->replyTo), 80) }}
                </a>
            @endif

            @if($editingId === $m->id)
                <div class="mt-1">
                    <textarea class="form-control form-control-sm" rows="2" wire:model="editBody"
                              x-init="$el.focus(); $el.setSelectionRange($el.value.length, $el.value.length)"
                              @keydown.enter.prevent.exact="$wire.saveEdit()" @keydown.escape.prevent="$wire.cancelEdit()"></textarea>
                    <div class="small text-muted mt-1">
                        Enter to <a href="#" wire:click.prevent="saveEdit">save</a> · Esc to <a href="#" wire:click.prevent="cancelEdit">cancel</a>
                    </div>
                </div>
            @else
                @if(trim((string) $m->body) !== '')
                    <div class="chat-text {{ \App\Services\Chat\MessageRenderer::isEmojiOnly($m->body) ? 'emoji-only' : '' }}">{!! $renderer->render($m) !!}@if($m->edited_at) <span class="chat-edited" title="Edited {{ $m->edited_at->format('d M H:i') }}">(edited)</span>@endif</div>
                @endif

                @foreach($m->attachments as $file)
                    @if($file->isImage())
                        <a href="{{ route('chat.attachment', $file) }}" target="_blank" rel="noopener">
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
            @endif

            @if($reactions->isNotEmpty())
                <div>
                    @foreach($reactions as $emoji => $group)
                        <button type="button" class="chat-reaction {{ $group->contains('user_id', auth()->id()) ? 'mine' : '' }}"
                                wire:click="react('{{ $m->id }}', '{{ $emoji }}')" title="{{ $group->count() }} reaction{{ $group->count() > 1 ? 's' : '' }}">
                            <span>{{ $emoji }}</span><span class="small">{{ $group->count() }}</span>
                        </button>
                    @endforeach
                </div>
            @endif
        @endif

        @if($receipt)
            <div class="text-end small text-muted mt-1" style="font-size: 0.7rem;">
                <i class="fa-solid {{ str_starts_with($receipt, 'Seen') ? 'fa-check-double text-primary' : 'fa-check' }} me-1"></i>{{ $receipt }}
            </div>
        @endif
    </div>

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
