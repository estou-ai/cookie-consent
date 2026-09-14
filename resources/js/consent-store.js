const STORAGE_KEY = 'cookie_consent';

function requiredGroups(config) {
    return Object.entries(config.groups)
        .filter(([, group]) => group.required)
        .map(([handle]) => handle);
}

// localStorage-backed consent, versioned: bumping config.version (in
// config/cookie-consent.php, or the CP settings screen) invalidates every
// visitor's stored choice and the banner shows again.
export class ConsentStore {
    constructor(config) {
        this.config = config;
        this.state = this.load();
    }

    load() {
        try {
            const stored = JSON.parse(localStorage.getItem(STORAGE_KEY));

            return stored && stored.version === this.config.version ? stored : null;
        } catch {
            return null;
        }
    }

    hasConsented() {
        return this.state !== null;
    }

    allowedGroups() {
        return this.state ? this.state.groups : requiredGroups(this.config);
    }

    isAllowed(group) {
        return this.allowedGroups().includes(group);
    }

    // Individual cookie names across every currently-allowed group — mirrors
    // the original addon's `CookieDialog.allowedCookies`.
    allowedCookies() {
        return this.allowedGroups().flatMap((group) => (this.config.groups[group]?.cookies || []).map((c) => c.name));
    }

    acceptAll(source = 'explicit') {
        this.set(Object.keys(this.config.groups), source);
    }

    rejectAll(source = 'explicit') {
        this.set(requiredGroups(this.config), source);
    }

    // `source` distinguishes an actual banner click ('explicit') from an
    // auto-applied decision ('gpc') in the consent log — see addon.js's
    // Global Privacy Control check.
    set(groups, source = 'explicit') {
        this.state = {
            version: this.config.version,
            timestamp: Date.now(),
            source,
            groups: Array.from(new Set([...requiredGroups(this.config), ...groups])),
        };

        localStorage.setItem(STORAGE_KEY, JSON.stringify(this.state));
        window.dispatchEvent(new CustomEvent('cookieconsent:change', { detail: this.state }));
    }
}
