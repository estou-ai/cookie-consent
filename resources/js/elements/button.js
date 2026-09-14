export function defineButtonElement() {
    if (customElements.get('cookie-consent-button')) return;

    class CookieConsentButton extends HTMLElement {
        connectedCallback() {
            this.attachShadow({ mode: 'open' });
            this.shadowRoot.innerHTML = `
                <style>
                    :host { all: initial; }
                    button { position: fixed; bottom: 16px; left: 16px; z-index: 2147483000;
                        width: 44px; height: 44px; border-radius: 999px; border: 0; cursor: pointer;
                        background: #6366f1; color: #fff; font-size: 20px; box-shadow: 0 4px 16px rgba(0,0,0,.25); }
                </style>
                <button part="button" aria-label="Preferências de cookies">🍪</button>`;

            this.shadowRoot.querySelector('button').addEventListener('click', () => {
                document.dispatchEvent(new CustomEvent('cookieconsent:show'));
            });
        }
    }

    customElements.define('cookie-consent-button', CookieConsentButton);
}
