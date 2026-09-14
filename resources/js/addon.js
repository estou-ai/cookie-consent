import { ConsentStore } from './consent-store.js';
import { bootConsentMode } from './consent-mode.js';
import { bootConsentLog } from './consent-log.js';
import { bootDomGate } from './dom-gate.js';
import { defineBannerElement } from './elements/banner.js';
import { defineButtonElement } from './elements/button.js';

function boot() {
    const bannerEl = document.querySelector('cookie-consent-banner[data-config]');

    // {{ cookie_consent:scripts }} with no {{ cookie_consent }} tag on the
    // page (e.g. a page that only uses {{ cookie_consent:allowed }}) — still
    // define the custom elements so they work if injected later, but there's
    // no config to boot consent-mode/dom-gate from.
    if (!bannerEl) {
        defineButtonElement();
        return;
    }

    const config = JSON.parse(bannerEl.dataset.config);
    const store = new ConsentStore(config);

    defineButtonElement(config, store);
    bootConsentMode(config, store);
    bootConsentLog(config);
    bootDomGate(store);

    // Global Privacy Control — a browser-level opt-out signal (CCPA/CPRA,
    // increasingly honored in the EU too). Runs after the listeners above so
    // they react to it like any other decision, but before the banner
    // element is defined/connected so it never flashes open just to
    // auto-close a moment later.
    if (!store.hasConsented() && navigator.globalPrivacyControl === true) {
        store.rejectAll('gpc');
    }

    defineBannerElement(store);

    window.CookieConsent = {
        version: config.version,
        get preferences() { return store.state; },
        get allowedGroups() { return store.allowedGroups(); },
        get allowedCookies() { return store.allowedCookies(); },
        // showDialog(true) reloads the page once the user saves new
        // preferences — for a site whose server-rendered output (ads,
        // embeds) needs a fresh request to reflect the change.
        showDialog: (reload = false) => {
            if (reload) window.addEventListener('cookieconsent:change', () => location.reload(), { once: true });
            document.dispatchEvent(new CustomEvent('cookieconsent:show'));
        },
        on: (event, callback) => window.addEventListener(`cookieconsent:${event}`, callback),
    };
}

if (document.readyState === 'loading') {
    document.addEventListener('DOMContentLoaded', boot);
} else {
    boot();
}
