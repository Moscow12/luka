/**
 * Delegated replacements for inline on* handlers, which the Content-Security-Policy blocks.
 *
 *   data-print                     -> window.print()
 *   data-print="fnName"            -> window.fnName() (a print helper defined by the page)
 *   data-print-if-exists="elId"    -> only print when that element is present
 *   data-alert="text"              -> alert(text)
 *   data-livewire-dispatch="event" -> Livewire.dispatch(event)
 *   data-prevent-default           -> stops "#" links from navigating
 *   data-hover-bg="#color"         -> background colour while hovered
 *
 * Values come from data attributes and are never evaluated as code.
 */
document.addEventListener('click', (event) => {
    const target = event.target instanceof Element ? event.target : null;
    if (!target) return;

    const printEl = target.closest('[data-print]');
    if (printEl) {
        const guardId = printEl.dataset.printIfExists;
        if (!guardId || document.getElementById(guardId)) {
            const fnName = printEl.dataset.print;
            if (fnName && /^[A-Za-z_$][\w$]*$/.test(fnName) && typeof window[fnName] === 'function') {
                window[fnName]();
            } else if (!fnName) {
                window.print();
            }
        }
    }

    const alertEl = target.closest('[data-alert]');
    if (alertEl) {
        window.alert(alertEl.dataset.alert);
    }

    const dispatchEl = target.closest('[data-livewire-dispatch]');
    if (dispatchEl && window.Livewire) {
        event.preventDefault();
        window.Livewire.dispatch(dispatchEl.dataset.livewireDispatch);
    }

    if (target.closest('[data-prevent-default]')) {
        event.preventDefault();
    }
});

document.addEventListener('mouseover', (event) => {
    const el = event.target instanceof Element ? event.target.closest('[data-hover-bg]') : null;
    if (el && !el.dataset.hoverBgOriginal) {
        el.dataset.hoverBgOriginal = el.style.backgroundColor || 'initial';
        el.style.backgroundColor = el.dataset.hoverBg;
    }
});

document.addEventListener('mouseout', (event) => {
    const el = event.target instanceof Element ? event.target.closest('[data-hover-bg]') : null;
    if (el && el.dataset.hoverBgOriginal && !el.contains(event.relatedTarget)) {
        el.style.backgroundColor = el.dataset.hoverBgOriginal === 'initial' ? '' : el.dataset.hoverBgOriginal;
        delete el.dataset.hoverBgOriginal;
    }
});
