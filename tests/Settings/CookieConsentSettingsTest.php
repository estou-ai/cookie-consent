<?php

namespace Estouai\CookieConsent\Tests\Settings;

use Estouai\CookieConsent\Settings\CookieConsentSettings;
use Estouai\CookieConsent\Tests\TestCase;
use Illuminate\Support\Facades\File;

class CookieConsentSettingsTest extends TestCase
{
    protected function tearDown(): void
    {
        // save() writes a real file, and testbench's skeleton app isn't wiped
        // between separate CLI test runs — clean up so it can't leak into an
        // unrelated test/run and change what "no override yet" looks like.
        File::deleteDirectory(base_path('content/cookie-consent'));

        parent::tearDown();
    }

    public function test_falls_back_to_config_defaults_when_no_override_file_exists()
    {
        $settings = (new CookieConsentSettings)->all();

        $this->assertSame(config('cookie-consent'), $settings);
    }

    public function test_saved_override_replaces_top_level_keys()
    {
        $repo = new CookieConsentSettings;

        $repo->save(array_merge(config('cookie-consent'), ['version' => 7]));

        $this->assertSame(7, $repo->all()['version']);
        // save() always writes the full shape back, so untouched keys still
        // round-trip through the saved file itself.
        $this->assertArrayHasKey('necessary', $repo->all()['groups']);
    }

    public function test_settings_are_isolated_per_site()
    {
        $repo = new CookieConsentSettings;

        $repo->save(array_merge(config('cookie-consent'), ['version' => 3]), 'nl');

        $this->assertSame(3, $repo->all('nl')['version']);
        $this->assertSame(config('cookie-consent.version'), $repo->all('en')['version']);
    }
}
