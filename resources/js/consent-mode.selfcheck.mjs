// Run: node resources/js/consent-mode.selfcheck.mjs
// Regression check for the gtag-pushes-Array-not-arguments bug.
import assert from 'node:assert';

const dataLayer = [];
function gtag() { dataLayer.push(arguments); }

gtag('consent', 'update', { ad_storage: 'granted' });

assert.strictEqual(
    Object.prototype.toString.call(dataLayer[0]),
    '[object Arguments]',
    'gtag must push an arguments object, not a plain Array, or GTM silently ignores the command'
);

console.log('ok: gtag pushes arguments, not Array');
