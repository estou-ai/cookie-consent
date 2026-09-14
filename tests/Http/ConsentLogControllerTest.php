<?php

namespace Estouai\CookieConsent\Tests\Http;

use Estouai\CookieConsent\Tests\TestCase;
use Illuminate\Support\Facades\File;

class ConsentLogControllerTest extends TestCase
{
    protected function tearDown(): void
    {
        File::deleteDirectory(storage_path('app/cookie-consent'));

        parent::tearDown();
    }

    public function test_logs_a_consent_decision_as_a_json_line()
    {
        $this->postJson(route('statamic.cookie-consent.log'), [
            'version' => 1,
            'groups' => ['necessary', 'analytics'],
            'source' => 'gpc',
            'page' => '/precos',
        ])->assertNoContent();

        $path = storage_path('app/cookie-consent/default/'.now()->format('Y-m-d').'.jsonl');
        $this->assertFileExists($path);

        $entry = json_decode(trim(File::get($path)), true);
        $this->assertSame(1, $entry['version']);
        $this->assertSame(['necessary', 'analytics'], $entry['groups']);
        $this->assertSame('/precos', $entry['page']);
        $this->assertSame('gpc', $entry['source']);
        $this->assertSame('default', $entry['site']);
        $this->assertArrayHasKey('timestamp', $entry);
        $this->assertArrayHasKey('ip_hash', $entry);
        // hashed, not the raw testing IP (127.0.0.1)
        $this->assertNotSame('127.0.0.1', $entry['ip_hash']);
    }

    public function test_requires_version_and_groups()
    {
        $this->postJson(route('statamic.cookie-consent.log'), [])
            ->assertJsonValidationErrors(['version', 'groups']);
    }
}
