import { ChatState, me, throttle } from './state';

/**
 * x-data="chatPane('<conversation id>')" — the browser side of one open conversation:
 * scroll position, "↓ N new", infinite scroll, typing whispers, optimistic sends,
 * inline editing, emoji, drag-and-drop and pasted files.
 */
export function registerPane(Alpine) {
    Alpine.data('chatPane', (conversationId) => ({
        body: '',
        pending: [],
        typers: {},
        typingText: '',
        emojiFor: null,
        atBottom: true,
        newCount: 0,
        loadingOlder: false,
        dragging: false,
        uploading: false,
        uploadProgress: 0,
        error: null,

        init() {
            ChatState.show(conversationId);
            this._lastId = this.lastMessageId();

            if (window.Echo) {
                this._channel = window.Echo.private(`chat.conversation.${conversationId}`);
                this._onTyping = (e) => {
                    if (e.id === me.id) return;
                    this.typers = { ...this.typers, [e.id]: { name: e.name, until: Date.now() + 3500 } };
                    this.refreshTyping();
                };
                this._channel.listenForWhisper('typing', this._onTyping);
            }
            this._typingTimer = setInterval(() => this.refreshTyping(), 1000);
            this._whisperTyping = throttle(() => this._channel?.whisper('typing', { id: me.id, name: me.name }), 2500);
            this._markReadSoon = throttle(() => {
                if (!document.hidden) this.$wire.markRead();
            }, 1500);

            const stream = this.$refs.stream;
            stream.addEventListener('scroll', () => this.onScroll(), { passive: true });
            this._observer = new MutationObserver(() => this.onStreamChanged());
            this._observer.observe(stream, { childList: true, subtree: true });
            this._onVisible = () => {
                if (!document.hidden && this.atBottom) this._markReadSoon();
            };
            document.addEventListener('visibilitychange', this._onVisible);

            this.$nextTick(() => this.scrollToStart());
        },

        destroy() {
            ChatState.hide(conversationId);
            this._channel?.stopListeningForWhisper('typing', this._onTyping);
            clearInterval(this._typingTimer);
            this._observer?.disconnect();
            document.removeEventListener('visibilitychange', this._onVisible);
        },

        // ------------------------------------------------------------ scrolling

        lastMessage() {
            const items = this.$refs.stream.querySelectorAll('[data-message-id]');
            return items[items.length - 1] ?? null;
        },

        lastMessageId() {
            return this.lastMessage()?.dataset.messageId ?? null;
        },

        scrollToStart() {
            const divider = this.$refs.stream.querySelector('[data-new-divider]');
            divider ? divider.scrollIntoView({ block: 'center' }) : this.scrollToBottom(false);
            this.onScroll();
        },

        scrollToBottom(smooth = true) {
            const stream = this.$refs.stream;
            stream.scrollTo({ top: stream.scrollHeight, behavior: smooth ? 'smooth' : 'auto' });
            this.newCount = 0;
        },

        onScroll() {
            const s = this.$refs.stream;
            this.atBottom = s.scrollHeight - s.scrollTop - s.clientHeight < 80;
            if (this.atBottom) {
                this.newCount = 0;
                this._markReadSoon();
            }
            if (s.scrollTop < 60) this.loadOlder();
        },

        onStreamChanged() {
            const last = this.lastMessage();
            const id = last?.dataset.messageId ?? null;
            if (id === this._lastId) return;
            this._lastId = id;

            if (this.atBottom || last?.dataset.mine === '1') {
                this.$nextTick(() => this.scrollToBottom());
                this._markReadSoon();
            } else {
                this.newCount++;
            }
        },

        async loadOlder() {
            if (this.loadingOlder || !this.$wire.hasMore) return;
            this.loadingOlder = true;
            const stream = this.$refs.stream;
            const fromBottom = stream.scrollHeight - stream.scrollTop;
            await this.$wire.loadOlder();
            this.$nextTick(() => {
                stream.scrollTop = stream.scrollHeight - fromBottom;
                this.loadingOlder = false;
            });
        },

        // ------------------------------------------------------------ typing

        refreshTyping() {
            const now = Date.now();
            const names = Object.values(this.typers).filter((t) => t.until > now).map((t) => t.name.split(' ')[0]);
            this.typingText = names.length === 0 ? '' : names.length === 1 ? `${names[0]} is typing` : names.length === 2 ? `${names[0]} and ${names[1]} are typing` : 'Several people are typing';
        },

        // ------------------------------------------------------------ composer

        onInput() {
            this.autosize();
            if (this.body.trim() !== '') this._whisperTyping();
        },

        autosize() {
            const el = this.$refs.input;
            if (!el) return;
            el.style.height = 'auto';
            el.style.height = Math.min(el.scrollHeight, 180) + 'px';
        },

        onKeydown(e) {
            if (e.key === 'Enter' && !e.shiftKey && !e.isComposing) {
                e.preventDefault();
                this.submit();
            } else if (e.key === 'ArrowUp' && this.body === '') {
                const mine = [...this.$refs.stream.querySelectorAll('[data-editable="1"]')].pop();
                if (mine) {
                    e.preventDefault();
                    this.$wire.startEdit(mine.dataset.messageId);
                }
            } else if (e.key === 'Escape') {
                this.$wire.cancelReply();
            }
        },

        async submit() {
            const text = this.body.trim();
            const hasFiles = (this.$wire.uploads?.length ?? 0) > 0;
            if ((!text && !hasFiles) || this.uploading) return;

            const item = { key: Date.now() + Math.random(), text: text || '📎 Sending attachment…', raw: text, failed: false, error: null };
            this.pending.push(item);
            this.body = '';
            this.$nextTick(() => {
                this.autosize();
                this.scrollToBottom();
            });

            const result = await this.$wire.send(text);
            if (result?.error) {
                item.failed = true;
                item.error = result.error;
                this.pending = [...this.pending];
            } else {
                this.pending = this.pending.filter((p) => p.key !== item.key);
            }
        },

        retry(item) {
            this.pending = this.pending.filter((p) => p.key !== item.key);
            this.body = item.raw;
            this.submit();
        },

        discard(item) {
            this.pending = this.pending.filter((p) => p.key !== item.key);
        },

        wrap(before, after = before, placeholder = 'text') {
            const el = this.$refs.input;
            const start = el.selectionStart;
            const end = el.selectionEnd;
            const selected = this.body.slice(start, end) || placeholder;
            this.body = this.body.slice(0, start) + before + selected + after + this.body.slice(end);
            this.$nextTick(() => {
                el.focus();
                el.setSelectionRange(start + before.length, start + before.length + selected.length);
                this.autosize();
            });
        },

        prefixLines(prefix) {
            const el = this.$refs.input;
            const start = this.body.lastIndexOf('\n', el.selectionStart - 1) + 1;
            this.body = this.body.slice(0, start) + prefix + this.body.slice(start);
            this.$nextTick(() => {
                el.focus();
                this.autosize();
            });
        },

        insertText(text) {
            const el = this.$refs.input;
            const pos = el ? el.selectionStart : this.body.length;
            this.body = this.body.slice(0, pos) + text + this.body.slice(pos);
            this.$nextTick(() => {
                el?.focus();
                el?.setSelectionRange(pos + text.length, pos + text.length);
            });
        },

        // ------------------------------------------------------------ emoji & reactions

        toggleEmoji(target) {
            this.emojiFor = this.emojiFor === target ? null : target;
        },

        pickEmoji(e) {
            const emoji = e.detail?.unicode;
            if (!emoji) return;
            if (this.emojiFor === 'composer') {
                this.insertText(emoji);
            } else if (this.emojiFor) {
                this.$wire.react(this.emojiFor, emoji);
            }
            this.emojiFor = null;
        },

        // ------------------------------------------------------------ files

        onDrop(e) {
            this.dragging = false;
            if (e.dataTransfer?.files?.length) this.upload(e.dataTransfer.files);
        },

        onPaste(e) {
            const files = [...(e.clipboardData?.items ?? [])].filter((i) => i.kind === 'file').map((i) => i.getAsFile()).filter(Boolean);
            if (files.length) {
                e.preventDefault();
                this.upload(files);
            }
        },

        upload(files) {
            this.uploading = true;
            this.uploadProgress = 0;
            this.$wire.uploadMultiple(
                'newUploads',
                [...files],
                () => (this.uploading = false),
                () => {
                    this.uploading = false;
                    this.flash('Upload failed. Files must be PDF, Office, image, CSV or TXT up to 25 MB.');
                },
                (ev) => (this.uploadProgress = ev.detail.progress),
            );
        },

        // ------------------------------------------------------------ misc

        async copy(id) {
            const el = this.$refs.stream.querySelector(`[data-message-id="${id}"] .chat-text`);
            try {
                await navigator.clipboard.writeText(el?.innerText ?? '');
                this.flash('Copied to clipboard', 'info');
            } catch {
                this.flash('Copy is not available in this browser.');
            }
        },

        flash(message, kind = 'error') {
            this.error = { message, kind };
            clearTimeout(this._flashTimer);
            this._flashTimer = setTimeout(() => (this.error = null), 4000);
        },
    }));
}
