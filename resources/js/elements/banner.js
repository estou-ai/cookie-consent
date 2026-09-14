function requiredGroups(config) {
    return Object.entries(config.groups)
        .filter(([, group]) => group.required)
        .map(([handle]) => handle);
}

function positionStyles(position) {
    switch (position) {
        case 'top':
            return 'top: 16px; left: 16px; right: 16px;';
        case 'bottom-left':
            return 'bottom: 16px; left: 16px;';
        case 'bottom-right':
            return 'bottom: 16px; right: 16px;';
        default:
            return 'bottom: 16px; left: 16px; right: 16px;';
    }
}

// ponytail: `theme: 'auto'` follows prefers-color-scheme; 'light'/'dark'
// forcing isn't wired up yet. Add a [data-theme] host attribute + a second
// @media-free rule block when a site actually needs to force one.
function styles(config) {
    return `
        :host { all: initial; }
        .dialog { position: fixed; ${positionStyles(config.position)} z-index: 2147483000; max-width: 420px;
            background: #16181d; color: #f4f4f5; border-radius: 12px; padding: 20px;
            font: 14px/1.5 system-ui, sans-serif; box-shadow: 0 10px 40px rgba(0,0,0,.35); }
        .text { margin: 0 0 12px; }
        .text a { color: inherit; text-decoration: underline; }
        .groups { display: grid; gap: 10px; margin: 0 0 14px; max-height: 220px; overflow-y: auto; }
        .group { display: flex; gap: 8px; align-items: flex-start; }
        .group input { margin-top: 3px; }
        .actions { display: flex; gap: 8px; flex-wrap: wrap; justify-content: flex-end; }
        .branding { margin: 12px 0 0; font-size: 11px; opacity: .72; text-align: right; }
        .branding a { color: inherit; text-decoration: underline; }
        button { cursor: pointer; border: 0; border-radius: 8px; padding: 8px 14px; font: inherit; font-weight: inherit; }
        /* Accept/reject must carry equal visual weight — same size, same
           solid style — per CNIL and the wider EU 2026 guidance ("reject"
           can't read as the secondary option). Only "customize" (not itself
           a decision) stays a lighter, outlined style. */
        button[data-action="accept"], button[data-action="save"] { background: #6366f1; color: #fff; }
        button[data-action="reject"] { background: #52525b; color: #fff; }
        button[data-action="customize"] { background: transparent; color: inherit; border: 1px solid rgba(255,255,255,.25); }
        @media (prefers-color-scheme: light) {
            .dialog { background: #fff; color: #111; box-shadow: 0 10px 40px rgba(0,0,0,.15); }
            button[data-action="customize"] { border-color: rgba(0,0,0,.15); }
        }
    `;
}

function groupsMarkup(store) {
    return `<div part="groups" class="groups">${Object.entries(store.config.groups).map(([handle, group]) => `
        <label class="group">
            <input type="checkbox" data-group="${handle}" ${group.required ? 'checked disabled' : store.isAllowed(handle) ? 'checked' : ''}>
            <span><strong>${group.name}</strong><br>${group.description}</span>
        </label>`).join('')}</div>`;
}

export function defineBannerElement(store) {
    if (customElements.get('cookie-consent-banner')) return;

    class CookieConsentBanner extends HTMLElement {
        connectedCallback() {
            this.customizing = false;
            this.attachShadow({ mode: 'open' });
            this.render();

            if (store.hasConsented()) this.style.display = 'none';

            document.addEventListener('cookieconsent:show', () => {
                this.customizing = false;
                this.style.display = '';
                this.render();
            });
        }

        render() {
            const { config } = store;

            this.shadowRoot.innerHTML = `
                <style>${styles(config)}</style>
                <div part="dialog" class="dialog">
                    <p part="text" class="text">
                        <strong>${config.text.title}</strong><br>${config.text.description}
                        ${config.text.privacy_policy_url ? ` <a part="link" href="${config.text.privacy_policy_url}">${config.text.privacy_policy_label}</a>` : ''}
                    </p>
                    ${this.customizing ? groupsMarkup(store) : ''}
                    <div class="actions">
                        ${this.customizing
                            ? `<button part="button" data-action="save">${config.text.save}</button>`
                            : `<button part="button" data-action="customize">${config.text.customize}</button>
                               <button part="button" data-action="reject">${config.text.reject_all}</button>
                               <button part="button" data-action="accept">${config.text.accept_all}</button>`}
                    </div>
                    ${config.is_pro ? '' : `<p part="branding" class="branding">Powered by <a href="https://estou.ai" target="_blank" rel="noopener">estou.ai</a></p>`}
                </div>`;

            this.shadowRoot.querySelectorAll('[data-action]').forEach((button) => {
                button.addEventListener('click', () => this.handle(button.dataset.action));
            });
        }

        handle(action) {
            if (action === 'customize') {
                this.customizing = true;
                return this.render();
            }

            if (action === 'accept') store.acceptAll();
            if (action === 'reject') store.rejectAll();

            if (action === 'save') {
                const checked = [...this.shadowRoot.querySelectorAll('input[type=checkbox]:checked')].map((i) => i.dataset.group);
                store.set([...requiredGroups(store.config), ...checked]);
            }

            this.style.display = 'none';
        }
    }

    customElements.define('cookie-consent-banner', CookieConsentBanner);
}
