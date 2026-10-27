import { ChatState, chime, me, storage } from './state';

/**
 * x-data="chatLauncher" — the floating bubble, dock and toasts present on every page.
 */
export function registerLauncher(Alpine) {
    Alpine.data('chatLauncher', (options = {}) => ({
        // On the full workspace page the bubble is hidden, so the dock stays closed there
        open: !options.hideBubble && storage.get('dock.open', false),
        sound: storage.get('sound', true),
        desktop: storage.get('desktop', false) && 'Notification' in window && Notification.permission === 'granted',
        toasts: [],
        pulse: false,

        init() {
            if (this.open) this.$wire.loadDock(storage.get('dock.active'));

            if (window.Echo && me.id) {
                window.Echo.private(`chat.user.${me.id}`).listen('.chat.activity', (e) => this.onActivity(e));
            }

            window.addEventListener('keydown', (e) => {
                if (e.key === 'Escape' && this.open && !document.querySelector('.modal.show, .modal.d-block')) this.open = false;
            });

            // Anything on the page can open a conversation in the dock: $dispatch('chat-open', { id })
            window.addEventListener('chat-open', (e) => this.openConversation(e.detail.id));
        },

        toggle() {
            this.open = !this.open;
            storage.set('dock.open', this.open);
            if (this.open) this.$wire.loadDock(storage.get('dock.active'));
        },

        close() {
            this.open = false;
            storage.set('dock.open', false);
        },

        remember(id) {
            storage.set('dock.active', id || null);
        },

        openConversation(id) {
            this.open = true;
            storage.set('dock.open', true);
            this.$wire.openConversation(id);
            this.toasts = this.toasts.filter((t) => t.conversation_id !== id);
        },

        onActivity(e) {
            if (e.kind !== 'message' || e.silent || ChatState.isViewing(e.conversation_id)) return;

            this.pulse = true;
            setTimeout(() => (this.pulse = false), 1400);
            this.toast(e);
            if (this.sound) chime();

            if (this.desktop && document.hidden && 'Notification' in window && Notification.permission === 'granted') {
                const n = new Notification(e.title ?? 'New message', { body: e.preview ?? '', icon: e.sender_avatar || undefined, tag: e.conversation_id });
                n.onclick = () => {
                    window.focus();
                    this.openConversation(e.conversation_id);
                    n.close();
                };
            }
        },

        toast(e) {
            const toast = { ...e, key: Date.now() + Math.random() };
            this.toasts = [toast, ...this.toasts.filter((t) => t.conversation_id !== e.conversation_id)].slice(0, 3);
            setTimeout(() => this.dismiss(toast.key), 8000);
        },

        dismiss(key) {
            this.toasts = this.toasts.filter((t) => t.key !== key);
        },

        toggleSound() {
            this.sound = !this.sound;
            storage.set('sound', this.sound);
            if (this.sound) chime();
        },

        async toggleDesktop() {
            if (this.desktop) {
                this.desktop = false;
            } else if ('Notification' in window) {
                this.desktop = (await Notification.requestPermission()) === 'granted';
            }
            storage.set('desktop', this.desktop);
        },

        initials(name) {
            return (name ?? '?')
                .split(' ')
                .filter(Boolean)
                .slice(0, 2)
                .map((p) => p[0])
                .join('')
                .toUpperCase();
        },
    }));
}
