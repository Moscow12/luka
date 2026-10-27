import { me, throttle } from './state';

const AWAY_AFTER_MS = 5 * 60 * 1000;

/**
 * $store.presence: who is online/away, from the chat.online presence channel.
 * Away is announced by whisper when the tab is hidden or idle for 5 minutes.
 */
export function registerPresence(Alpine) {
    Alpine.store('presence', {
        users: {},
        status(id) {
            return this.users[id] ?? 'offline';
        },
        label(id) {
            return { online: 'Online', away: 'Away', offline: 'Offline' }[this.status(id)];
        },
        set(id, status) {
            this.users = { ...this.users, [id]: status };
        },
        remove(id) {
            const users = { ...this.users };
            delete users[id];
            this.users = users;
        },
    });

    const store = Alpine.store('presence');
    if (!window.Echo || !me.id) {
        return;
    }

    let current = 'online';
    const channel = window.Echo.join('chat.online')
        .here((users) => {
            const map = {};
            users.forEach((u) => (map[u.id] = 'online'));
            store.users = map;
            store.set(me.id, current);
        })
        .joining((u) => {
            store.set(u.id, 'online');
            // Newcomers can't know we're away unless we tell them
            if (current === 'away') {
                channel.whisper('status', { id: me.id, status: current });
            }
        })
        .leaving((u) => store.remove(u.id))
        .listenForWhisper('status', (e) => store.set(e.id, e.status));

    const setStatus = (status) => {
        if (status === current) return;
        current = status;
        store.set(me.id, status);
        channel.whisper('status', { id: me.id, status });
    };

    let idleTimer = null;
    const activity = () => {
        setStatus(document.hidden ? 'away' : 'online');
        clearTimeout(idleTimer);
        idleTimer = setTimeout(() => setStatus('away'), AWAY_AFTER_MS);
    };

    const onActivity = throttle(activity, 5000);
    ['mousemove', 'keydown', 'click', 'touchstart', 'focus'].forEach((evt) => window.addEventListener(evt, onActivity, { passive: true }));
    document.addEventListener('visibilitychange', activity);
    activity();
}
