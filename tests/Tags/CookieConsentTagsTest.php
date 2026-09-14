<?php

namespace Estouai\CookieConsent\Tests\Tags;

use Estouai\CookieConsent\Settings\CookieConsentSettings;
use Estouai\CookieConsent\Tests\TestCase;
use Illuminate\Support\Facades\File;
use Statamic\Facades\Antlers;

class CookieConsentTagsTest extends TestCase
{
    protected function tearDown(): void
    {
        File::deleteDirectory(base_path('content/cookie-consent'));

        parent::tearDown();
    }

    public function test_renders_the_banner_element_with_inline_config()
    {
        $html = (string) Antlers::parse('{{ cookie_consent }}', [], true);

        $this->assertStringContainsString('<cookie-consent-banner data-config="', $html);
        $this->assertStringContainsString('necessary', $html);
        // proves consent-log.js has somewhere to beacon decisions to
        $this->assertStringContainsString('log_url', $html);
    }

    public function test_hides_the_banner_when_hidden_param_is_true()
    {
        $this->assertSame('', (string) Antlers::parse('{{ cookie_consent hidden="true" }}', [], true));
    }

    public function test_renders_the_floating_button_element()
    {
        $this->assertSame(
            '<cookie-consent-button></cookie-consent-button>',
            (string) Antlers::parse('{{ cookie_consent:button }}', [], true)
        );
    }

    public function test_wraps_allowed_and_denied_content_in_a_template_marker()
    {
        $allowed = (string) Antlers::parse('{{ cookie_consent:allowed group="marketing" }}pixel{{ /cookie_consent:allowed }}', [], true);
        $denied = (string) Antlers::parse('{{ cookie_consent:denied group="marketing" }}fallback{{ /cookie_consent:denied }}', [], true);

        $this->assertSame('<template data-cookie-consent-allowed data-cookie-consent-group="marketing">pixel</template>', $allowed);
        $this->assertSame('<template data-cookie-consent-denied data-cookie-consent-group="marketing">fallback</template>', $denied);
    }

    public function test_iterates_cookie_groups()
    {
        $html = (string) Antlers::parse('{{ cookie_consent:groups }}{{ handle }}|{{ /cookie_consent:groups }}', [], true);

        $this->assertSame('necessary|analytics|marketing|', $html);
    }

    public function test_defaults_grants_only_required_groups_consent_mode_signals()
    {
        $html = (string) Antlers::parse('{{ cookie_consent:defaults }}', [], true);

        $this->assertStringContainsString("gtag('consent', 'default',", $html);
        // necessary (required) -> security_storage granted
        $this->assertStringContainsString('"security_storage":"granted"', $html);
        // analytics/marketing (not required) -> denied
        $this->assertStringContainsString('"analytics_storage":"denied"', $html);
        $this->assertStringContainsString('"ad_storage":"denied"', $html);
    }

    public function test_disabling_the_dialog_skips_the_banner_button_scripts_and_defaults()
    {
        app(CookieConsentSettings::class)->save(array_merge(config('cookie-consent'), ['enabled' => false]));

        $this->assertSame('', (string) Antlers::parse('{{ cookie_consent }}', [], true));
        $this->assertSame('', (string) Antlers::parse('{{ cookie_consent:button }}', [], true));
        $this->assertSame('', (string) Antlers::parse('{{ cookie_consent:scripts }}', [], true));
        $this->assertSame('', (string) Antlers::parse('{{ cookie_consent:defaults }}', [], true));
    }

    public function test_disabling_the_dialog_treats_allowed_content_as_always_shown()
    {
        app(CookieConsentSettings::class)->save(array_merge(config('cookie-consent'), ['enabled' => false]));

        $allowed = (string) Antlers::parse('{{ cookie_consent:allowed group="marketing" }}pixel{{ /cookie_consent:allowed }}', [], true);
        $denied = (string) Antlers::parse('{{ cookie_consent:denied group="marketing" }}fallback{{ /cookie_consent:denied }}', [], true);

        $this->assertSame('pixel', $allowed);
        $this->assertSame('', $denied);
    }
}
