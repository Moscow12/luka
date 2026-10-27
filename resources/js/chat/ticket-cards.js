import { escapeHtml } from './state';

/**
 * Hover cards for HRP-YYYY-NNNNN chips in chat messages (PRD 4.3).
 * One floating card for the whole page; results cached per ticket number.
 */
export function registerTicketCards() {
    const cache = new Map();
    let card = null;
    let hideTimer = null;
    let currentRef = null;

    const ensureCard = () => {
        if (card) return card;
        card = document.createElement('div');
        card.className = 'chat-ticket-card shadow';
        card.setAttribute('role', 'tooltip');
        card.addEventListener('mouseenter', () => clearTimeout(hideTimer));
        card.addEventListener('mouseleave', scheduleHide);
        document.body.appendChild(card);
        return card;
    };

    const scheduleHide = () => {
        clearTimeout(hideTimer);
        hideTimer = setTimeout(() => {
            card?.classList.remove('show');
            currentRef = null;
        }, 250);
    };

    const position = (ref) => {
        const rect = ref.getBoundingClientRect();
        const width = 280;
        const left = Math.min(Math.max(8, rect.left), window.innerWidth - width - 8);
        const below = rect.bottom + 180 < window.innerHeight;
        card.style.left = left + 'px';
        card.style.top = below ? rect.bottom + 6 + 'px' : '';
        card.style.bottom = below ? '' : window.innerHeight - rect.top + 6 + 'px';
    };

    const render = (number, data) => {
        if (!data) {
            return `<div class="small text-muted"><span class="spinner-border spinner-border-sm me-1"></span>Loading ${escapeHtml(number)}…</div>`;
        }
        if (data.restricted || data.missing) {
            return `<div class="fw-semibold mb-1">${escapeHtml(number)}</div>
                <div class="small text-muted"><i class="fa-solid fa-lock me-1"></i>${data.missing ? 'Ticket not found.' : 'You do not have access to this ticket.'}</div>`;
        }
        return `<div class="d-flex justify-content-between align-items-start gap-2 mb-1">
                <span class="fw-semibold">${escapeHtml(data.number)}</span>
                <span class="badge ${escapeHtml(data.status_class)}">${escapeHtml(data.status)}</span>
            </div>
            <div class="mb-2">${escapeHtml(data.subject)}</div>
            <dl class="row small mb-2 g-0">
                <dt class="col-5 fw-normal text-muted">Assignee</dt><dd class="col-7 mb-1">${escapeHtml(data.assignee ?? 'Department queue')}</dd>
                <dt class="col-5 fw-normal text-muted">Priority</dt><dd class="col-7 mb-1"><span class="badge ${escapeHtml(data.priority_class)}">${escapeHtml(data.priority)}</span></dd>
                <dt class="col-5 fw-normal text-muted">Due</dt><dd class="col-7 mb-0">${escapeHtml(data.due)}</dd>
            </dl>
            <a href="${escapeHtml(data.url)}" class="btn btn-sm btn-primary w-100"><i class="fa-solid fa-arrow-up-right-from-square me-1"></i>Open ticket</a>`;
    };

    document.addEventListener('mouseover', async (e) => {
        const ref = e.target.closest?.('.chat-ticket-ref');
        if (!ref) return;

        clearTimeout(hideTimer);
        const number = ref.dataset.ticket;
        if (currentRef === ref && card?.classList.contains('show')) return;
        currentRef = ref;

        ensureCard();
        card.innerHTML = render(number, cache.get(number));
        position(ref);
        card.classList.add('show');

        if (!cache.has(number)) {
            try {
                const res = await fetch(`/chat/ticket-card/${encodeURIComponent(number)}`, { headers: { Accept: 'application/json' } });
                cache.set(number, res.status === 404 ? { missing: true } : res.status === 403 ? { restricted: true } : await res.json());
            } catch {
                cache.set(number, { missing: true });
            }
            if (currentRef === ref) {
                card.innerHTML = render(number, cache.get(number));
                position(ref);
            }
        }
    });

    document.addEventListener('mouseout', (e) => {
        if (e.target.closest?.('.chat-ticket-ref')) scheduleHide();
    });
}
