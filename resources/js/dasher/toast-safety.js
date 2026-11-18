/**
 * The toast library (devrabiul/laravel-toaster-magic) inserts the heading, description
 * and button into the page with innerHTML. Toast messages often contain record names
 * typed by users, so every field is HTML-escaped here before the library renders it,
 * and button links are limited to http(s) or same-site paths.
 *
 * The library creates window.toastMagic as soon as its (classic) script runs, which is
 * before this deferred module, so show() can be wrapped directly. A setter covers the
 * case where the library loads later.
 */
const escapeHtml = (value) =>
    String(value ?? '').replace(/[&<>"']/g, (c) => ({ '&': '&amp;', '<': '&lt;', '>': '&gt;', '"': '&quot;', "'": '&#39;' })[c]);

const safeLink = (value) => {
    const link = String(value ?? '').trim();

    return /^(https?:\/\/|\/(?![\/\\]))/i.test(link) ? link : '';
};

function harden(instance) {
    if (!instance || typeof instance.show !== 'function' || instance.__escaped) {
        return instance;
    }

    const show = instance.show.bind(instance);
    instance.show = (options = {}) =>
        show({
            ...options,
            heading: escapeHtml(options.heading),
            description: escapeHtml(options.description),
            customBtnText: escapeHtml(options.customBtnText),
            customBtnLink: safeLink(options.customBtnLink),
        });
    instance.__escaped = true;

    return instance;
}

if (window.toastMagic) {
    harden(window.toastMagic);
} else {
    let current;
    Object.defineProperty(window, 'toastMagic', {
        configurable: true,
        get: () => current,
        set: (value) => {
            current = harden(value);
        },
    });
}

export { escapeHtml, safeLink };
