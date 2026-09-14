// Server-side accountability record — RGPD Art. 7º(1) / LGPD Art. 8º require
// being able to demonstrate a visitor actually consented. localStorage alone
// (consent-store.js) only proves it to the visitor's own browser; this
// beacons every decision to the addon's log endpoint so the site has a
// record if asked to produce one. Best-effort: a failed beacon never blocks
// or reverses the consent decision itself.
export function bootConsentLog(config) {
    if (!config.log_url) return;

    window.addEventListener('cookieconsent:change', (event) => {
        fetch(config.log_url, {
            method: 'POST',
            headers: { 'Content-Type': 'application/json' },
            keepalive: true,
            body: JSON.stringify({
                version: event.detail.version,
                groups: event.detail.groups,
                source: event.detail.source || 'explicit',
                page: location.pathname,
            }),
        }).catch(() => {});
    });
}
