const DEFAULT_BUTTON = {
    enabled: true,
    label: 'Cookies',
    aria_label: 'Cookie preferences',
    position: 'bottom-left',
    background: '#ffffff',
    foreground: '#6b7280',
    border: 'rgba(99, 102, 241, 0.16)',
    shadow: '0 8px 24px rgba(0, 0, 0, 0.16)',
    icon: 'cookie',
    icon_background: '#6366f1',
    icon_foreground: '#ffffff',
};

function escapeHtml(value) {
    return String(value ?? '').replaceAll('&', '&amp;').replaceAll('<', '&lt;').replaceAll('>', '&gt;').replaceAll('"', '&quot;').replaceAll("'", '&#039;');
}

function positionStyles(position) {
    return {
        'top-left': 'top: 16px; left: 16px;',
        'top-right': 'top: 16px; right: 16px;',
        'bottom-right': 'bottom: 16px; right: 16px;',
        'bottom-left': 'bottom: 16px; left: 16px;',
    }[position] || 'bottom: 16px; left: 16px;';
}

function iconMarkup(settings) {
    if (!settings.icon || settings.icon === 'none') return '';
    if (settings.icon !== 'cookie') return `<span part="icon" class="icon" aria-hidden="true">${settings.icon}</span>`;

    return `<span part="icon" class="icon" aria-hidden="true"><svg width="18" height="18" viewBox="0 0 24 24" fill="none" xmlns="http://www.w3.org/2000/svg"><path d="M21 12.4A8.6 8.6 0 1 1 11.6 3a4.2 4.2 0 0 0 4.7 4.7A4.2 4.2 0 0 0 21 12.4Z" fill="currentColor"/><circle cx="8" cy="9" r="1.3" fill="var(--cc-button-icon-bg)"/><circle cx="11.5" cy="14" r="1.2" fill="var(--cc-button-icon-bg)"/><circle cx="7.5" cy="16" r="1" fill="var(--cc-button-icon-bg)"/></svg></span>`;
}

export function defineButtonElement(config = {}, store = null) {
    if (customElements.get('cookie-consent-button')) return;

    class CookieConsentButton extends HTMLElement {
        connectedCallback() {
            const settings = { ...DEFAULT_BUTTON, ...(config.button || {}) };
            if (!settings.enabled) return this.remove();

            this.attachShadow({ mode: 'open' });
            this.shadowRoot.innerHTML = `<style>:host{all:initial}button{position:fixed;${positionStyles(settings.position)}z-index:2147483000;display:inline-flex;align-items:center;gap:8px;min-width:40px;height:40px;border-radius:999px;border:1px solid ${settings.border};background:${settings.background};color:${settings.foreground};padding:5px 13px 5px 5px;cursor:pointer;font:400 14px/1 system-ui,sans-serif;box-shadow:${settings.shadow}}.icon{--cc-button-icon-bg:${settings.icon_background};display:inline-flex;width:30px;height:30px;align-items:center;justify-content:center;border-radius:999px;background:${settings.icon_background};color:${settings.icon_foreground};flex:0 0 auto}</style><button part="button" aria-label="${escapeHtml(settings.aria_label)}">${iconMarkup(settings)}<span part="label">${escapeHtml(settings.label)}</span></button>`;

            if (store && !store.hasConsented()) this.style.display = 'none';
            document.addEventListener('cookieconsent:show', () => { this.style.display = 'none'; });
            window.addEventListener('cookieconsent:change', () => { this.style.display = ''; });
            this.shadowRoot.querySelector('button').addEventListener('click', () => document.dispatchEvent(new CustomEvent('cookieconsent:show')));
        }
    }

    customElements.define('cookie-consent-button', CookieConsentButton);
}
