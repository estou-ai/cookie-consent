<?php

namespace Estouai\CookieConsent\Tests\Http;

use Estouai\CookieConsent\Settings\CookieConsentBlueprint;
use Estouai\CookieConsent\Tests\TestCase;
use Illuminate\Support\Facades\File;
use Statamic\Facades\User;

class SettingsControllerTest extends TestCase
{
    protected function setUp(): void
    {
        parent::setUp();

        $user = User::make()->email('editor@example.com')->set('super', true);
        $user->save();

        $this->actingAs($user);
    }

    protected function tearDown(): void
    {
        File::deleteDirectory(base_path('content/cookie-consent'));
        File::deleteDirectory(__DIR__.'/../__fixtures__/users');

        parent::tearDown();
    }

    public function test_index_exposes_groups_as_a_list_keyed_by_handle_field()
    {
        $response = $this->get(cp_route('cookie-consent.index'))->assertOk();

        $groups = $response->viewData('values')['groups'];

        $this->assertSame(['necessary', 'analytics', 'marketing'], array_column($groups, 'handle'));
        // consent_mode folded into each row from the top-level map.
        $this->assertSame(['security_storage'], collect($groups)->firstWhere('handle', 'necessary')['consent_mode']);
    }

    public function test_update_round_trips_groups_back_into_the_stored_keyed_map_shape()
    {
        $values = CookieConsentBlueprint::toEditable(config('cookie-consent'));

        // Flip one group's consent_mode — the thing the old JSON-textarea UI
        // made easy to get out of sync with `groups`.
        $marketing = collect($values['groups'])->firstWhere('handle', 'marketing');
        $marketing['consent_mode'] = ['ad_storage'];
        $values['groups'] = collect($values['groups'])
            ->map(fn ($group) => $group['handle'] === 'marketing' ? $marketing : $group)
            ->all();

        $this->postJson(cp_route('cookie-consent.update'), $values)->assertOk();

        $stored = app(\Estouai\CookieConsent\Settings\CookieConsentSettings::class)->all();

        $this->assertArrayHasKey('necessary', $stored['groups']);
        $this->assertArrayNotHasKey('handle', $stored['groups']['necessary']);
        $this->assertArrayNotHasKey('consent_mode', $stored['groups']['necessary']);
        $this->assertSame(['ad_storage'], $stored['consent_mode']['marketing']);
    }

    public function test_update_rejects_a_group_with_no_handle()
    {
        $values = CookieConsentBlueprint::toEditable(config('cookie-consent'));
        $values['groups'][0]['handle'] = '';

        $this->postJson(cp_route('cookie-consent.update'), $values)
            ->assertStatus(422);
    }
}
