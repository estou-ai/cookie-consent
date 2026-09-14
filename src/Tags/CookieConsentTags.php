<?php

namespace Estouai\CookieConsent\Tags;

use Estouai\CookieConsent\Settings\CookieConsentSettings;
use Illuminate\Foundation\Vite;
use Statamic\Facades\Addon;
use Statamic\Tags\Tags;

// {{ cookie_consent }} / {{ cookie_consent:button }} / {{ cookie_consent:scripts }}
// {{ cookie_consent:defaults }} / {{ cookie_consent:allowed }} / {{ cookie_consent:denied }}
// {{ cookie_consent:groups }}
class CookieConsentTags extends Tags
{
    protected static $handle = 'cookie_consent';

    public function index()
    {
        return $this->banner();
    }

    public function banner()
    {
        if (! $this->settings()['enabled'] || $this->params->bool('hidden')) {
            return '';
        }

        return '<cookie-consent-banner data-config="'.$this->configAttribute().'"></cookie-consent-banner>';
    }

    // Google requires the Consent Mode `default` command to run *before*
    // gtag.js/GTM loads, so it can hold tags back from the very first
    // pageview — a deferred module script (scripts()) is too late if GTM
    // itself is a blocking/head <script>. This is the synchronous, inline
    // counterpart: put it in <head> before your GTM/gtag.js snippet, and it
    // grants only what's required (JS module <script>, once it loads later,
    // reconciles this against real stored consent via consent-mode.js —
    // same "default now, update later" two-step Google's own guides use).
    // No JS on this page (just accepted a decision, no page reload) is fine
    // too — GTM's per-tag "Additional Consent Checks" read the same
    // gtag('consent', ...) dataLayer state this writes; nothing extra to
    // wire up on the GTM side.
    public function defaults()
    {
        if (! $this->settings()['enabled']) {
            return '';
        }

        $settings = $this->settings();
        $requiredGroups = collect($settings['groups'])->filter(fn ($group) => $group['required'] ?? false)->keys();

        $signals = [];
        foreach ($settings['consent_mode'] as $group => $keys) {
            foreach ($keys as $key) {
                $signals[$key] = $requiredGroups->contains($group) ? 'granted' : 'denied';
            }
        }

        $json = json_encode($signals, JSON_UNESCAPED_UNICODE | JSON_HEX_TAG);

        return <<<HTML
            <script>
            window.dataLayer = window.dataLayer || [];
            function gtag(){dataLayer.push(arguments);}
            gtag('consent', 'default', {$json});
            </script>
            HTML;
    }

    public function button()
    {
        if (! $this->settings()['enabled']) {
            return '';
        }

        return '<cookie-consent-button></cookie-consent-button>';
    }

    // Server can't know browser consent state, so gating normally happens
    // client-side: wrap the tag body in a <template>, and dom-gate.js swaps
    // it into (or never swaps it out of) the DOM once consent is known.
    // Keeps this static-cache safe, same as the original addon's approach.
    // When the whole mechanism is disabled (config('cookie-consent.enabled')
    // false), there's no consent to wait on: render `allowed` content
    // directly and drop `denied` content, no JS involved.
    public function allowed()
    {
        return $this->settings()['enabled'] ? $this->gate(denied: false) : $this->parse();
    }

    public function denied()
    {
        return $this->settings()['enabled'] ? $this->gate(denied: true) : '';
    }

    protected function gate(bool $denied): string
    {
        $group = $this->params->get('group');

        if (! $group) {
            throw new \Exception('{{ cookie_consent:allowed/denied }} requires a group="" param.');
        }

        $attrs = $denied ? 'data-cookie-consent-denied' : 'data-cookie-consent-allowed';

        return '<template '.$attrs.' data-cookie-consent-group="'.$group.'">'.$this->parse().'</template>';
        // skipped: original addon's cookies="" (match by individual cookie name
        // instead of group) — group covers the same use case here since every
        // group already lists its own cookies. Add cookies="" matching if a
        // site needs finer granularity than group-level gating.
    }

    public function groups()
    {
        $groups = collect($this->settings()['groups'])->map(function ($group, $handle) {
            return array_merge($group, ['handle' => $handle]);
        })->values()->all();

        return $this->parseLoop($groups);
    }

    // Same mechanism as Statamic core's own {{ vite }} tag (Statamic\Tags\Vite)
    // — the addon's assets publish to public/vendor/cookie-consent/build (the
    // addon's packageName(), not the full composer name — see
    // ServiceProvider::registerVite via the $vite property), so we point
    // Laravel's Vite helper at that build directory instead of the app's own.
    public function scripts()
    {
        if (! $this->settings()['enabled']) {
            return '';
        }

        $hotFile = dirname(__DIR__, 2).'/public/hot';

        return (string) (clone app(Vite::class))
            ->useBuildDirectory('vendor/cookie-consent/build')
            ->useHotFile($hotFile)
            ->withEntryPoints(['resources/js/addon.js'])
            ->toHtml();
    }

    protected function configAttribute(): string
    {
        // log_url isn't part of $settings (that's what the CP screen
        // saves/loads) — computed fresh each render so it always matches the
        // action route's actual URL (host, `statamic.routes.action` prefix).
        // is_pro likewise: resolved from config('statamic.editions.addons')
        // so the JS bundle can drop the "Powered by" branding on pro installs.
        $config = $this->settings() + [
            'log_url' => route('statamic.cookie-consent.log'),
            'is_pro' => Addon::get('estouai/cookie-consent')?->edition() === 'pro',
        ];

        return e(json_encode($config, JSON_UNESCAPED_UNICODE));
    }

    protected ?array $settingsCache = null;

    // Memoized: banner()/allowed()/denied()/scripts() each check 'enabled'
    // and may also need the full settings, so a single tag render can call
    // this more than once — cache to avoid re-reading + re-merging the YAML
    // file every time.
    protected function settings(): array
    {
        return $this->settingsCache ??= app(CookieConsentSettings::class)->all();
    }
}
