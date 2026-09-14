// Resolves {{ cookie_consent:allowed group="..." }} / {{ cookie_consent:denied
// group="..." }} markers — see CookieConsentTags::gate(). Server can't know
// browser consent, so the tag emits inert <template> content and this swaps
// it into (or out of) the DOM once consent is known / changes.
export function bootDomGate(store) {
    function resolve() {
        document.querySelectorAll('template[data-cookie-consent-group]').forEach((template) => {
            const isDeniedVariant = template.hasAttribute('data-cookie-consent-denied');
            const allowed = store.isAllowed(template.dataset.cookieConsentGroup);
            const shouldRender = isDeniedVariant ? !allowed : allowed;
            const rendered = template.nextElementSibling?.dataset?.cookieConsentRendered;

            if (shouldRender && !rendered) {
                const wrapper = document.createElement('div');
                wrapper.dataset.cookieConsentRendered = 'true';
                wrapper.style.display = 'contents';
                wrapper.append(template.content.cloneNode(true));
                template.after(wrapper);
            } else if (!shouldRender && rendered) {
                template.nextElementSibling.remove();
            }
        });
    }

    resolve();
    window.addEventListener('cookieconsent:change', resolve);
}
