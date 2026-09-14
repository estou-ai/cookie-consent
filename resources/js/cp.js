// CP-only entry point — kept separate from addon.js, which also ships to
// every site visitor via {{ cookie_consent:scripts }}. Bundling
// @statamic/cms/ui (and the `Statamic` global it needs) into that public
// bundle would bloat the front-end banner script and crash outside the CP.
import SettingsForm from './components/SettingsForm.vue';

Statamic.component('cookie-consent-settings', SettingsForm);
