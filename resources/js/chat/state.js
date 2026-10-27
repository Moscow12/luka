// Shared browser-side chat state.
export const me = {
    id: document.querySelector('meta[name="chat-user-id"]')?.content ?? null,
    name: document.querySelector('meta[name="chat-user-name"]')?.content ?? '',
};

// Conversations currently on screen (dock or workspace). No toast for those.
const visible = new Map();

export const ChatState = {
    show(conversationId) {
        visible.set(conversationId, (visible.get(conversationId) ?? 0) + 1);
    },
    hide(conversationId) {
        const n = (visible.get(conversationId) ?? 1) - 1;
        n > 0 ? visible.set(conversationId, n) : visible.delete(conversationId);
    },
    isViewing(conversationId) {
        return !document.hidden && visible.has(conversationId);
    },
};

window.ChatState = ChatState;

export const storage = {
    get(key, fallback = null) {
        try {
            const v = window.localStorage.getItem('chat.' + key);
            return v === null ? fallback : JSON.parse(v);
        } catch {
            return fallback;
        }
    },
    set(key, value) {
        try {
            window.localStorage.setItem('chat.' + key, JSON.stringify(value));
        } catch {
            // Private mode / blocked storage: preferences simply aren't remembered
        }
    },
};

export function escapeHtml(value) {
    return String(value ?? '').replace(/[&<>"']/g, (c) => ({ '&': '&amp;', '<': '&lt;', '>': '&gt;', '"': '&quot;', "'": '&#39;' })[c]);
}

export function throttle(fn, wait) {
    let last = 0;
    return (...args) => {
        const now = Date.now();
        if (now - last >= wait) {
            last = now;
            fn(...args);
        }
    };
}

// A short, soft two-tone chime generated on the fly (no audio file needed)
export function chime() {
    try {
        const Ctx = window.AudioContext || window.webkitAudioContext;
        const ctx = new Ctx();
        [660, 880].forEach((freq, i) => {
            const osc = ctx.createOscillator();
            const gain = ctx.createGain();
            osc.frequency.value = freq;
            osc.type = 'sine';
            gain.gain.setValueAtTime(0.0001, ctx.currentTime + i * 0.12);
            gain.gain.exponentialRampToValueAtTime(0.08, ctx.currentTime + i * 0.12 + 0.02);
            gain.gain.exponentialRampToValueAtTime(0.0001, ctx.currentTime + i * 0.12 + 0.25);
            osc.connect(gain).connect(ctx.destination);
            osc.start(ctx.currentTime + i * 0.12);
            osc.stop(ctx.currentTime + i * 0.12 + 0.3);
        });
        setTimeout(() => ctx.close(), 800);
    } catch {
        // Audio not available
    }
}
