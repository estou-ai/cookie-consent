<?php

namespace Estouai\CookieConsent\Settings;

use Illuminate\Support\Facades\File;
use Statamic\Facades\Site;
use Statamic\Facades\YAML;

// CP-edited overrides live in a flat YAML file per site, not the config file
// — so updating the addon (or its config defaults) never clobbers what an
// editor set in the CP, and a site with no CP edits yet just gets the config
// defaults untouched. On a single-site install this is just one file under
// the site's own handle (Statamic still assigns a handle, "default" unless
// configured), so there's no special-casing needed for that common case.
class CookieConsentSettings
{
    public function all(?string $site = null): array
    {
        $path = $this->path($site);

        if (! File::exists($path)) {
            return config('cookie-consent');
        }

        // Top-level replace, not a deep merge: the CP screen always posts
        // the full settings shape back, so a shallow replace is enough and
        // avoids stale group/cookie entries lingering from an old default.
        return array_replace(config('cookie-consent'), YAML::parse(File::get($path)));
    }

    public function save(array $settings, ?string $site = null): void
    {
        $path = $this->path($site);

        File::ensureDirectoryExists(dirname($path));
        File::put($path, YAML::dump($settings));
    }

    protected function path(?string $site): string
    {
        $site ??= Site::current()->handle();

        return base_path("content/cookie-consent/{$site}/settings.yaml");
    }
}
