// Google Consent Mode v2 + a generic script/iframe gate — the "integrations
// prontas" piece. A tag just needs `data-cookie-category="analytics"` (and,
// for a remote script, `data-cookie-src="..."` instead of `src`) to be held
// back until that category is granted. No GTM/GA/Meta-specific code here.
function signalsFor(config, groups) {
    const signals = {};

    Object.entries(config.consent_mode || {}).forEach(([group, keys]) => {
        keys.forEach((key) => {
            signals[key] = groups.includes(group) ? 'granted' : 'denied';
        });
    });

    return signals;
}

function activateTaggedNodes(groups) {
    document.querySelectorAll('script[data-cookie-category]:not([data-cookie-activated])').forEach((node) => {
        if (!groups.includes(node.dataset.cookieCategory)) return;

        const script = document.createElement('script');
        [...node.attributes].forEach((attr) => {
            if (attr.name !== 'type') script.setAttribute(attr.name, attr.value);
        });
        script.dataset.cookieActivated = 'true';
        script.text = node.textContent;
        if (node.dataset.cookieSrc) script.src = node.dataset.cookieSrc;

        node.replaceWith(script);
    });

    document.querySelectorAll('iframe[data-cookie-category][data-cookie-src]').forEach((node) => {
        if (groups.includes(node.dataset.cookieCategory)) node.src = node.dataset.cookieSrc;
    });
}

export function bootConsentMode(config, store) {
    window.dataLayer = window.dataLayer || [];
    const gtag = (...args) => window.dataLayer.push(args);

    const push = (groups) => {
        gtag('consent', 'update', signalsFor(config, groups));
        window.dataLayer.push({ event: 'cookie_consent_update', cookieConsentGroups: groups });
        activateTaggedNodes(groups);
    };

    // Redundant with {{ cookie_consent:defaults }}'s inline <head> snippet
    // when a page includes it (same values, gtag 'default' calls are
    // idempotent) — kept as a fallback for a page that only has this
    // deferred bundle and skipped the head tag, so it's never worse than
    // "no default at all" per Google's Consent Mode guidance.
    gtag('consent', 'default', signalsFor(config, store.allowedGroups()));

    if (store.hasConsented()) push(store.allowedGroups());

    window.addEventListener('cookieconsent:change', (event) => push(event.detail.groups));
}
