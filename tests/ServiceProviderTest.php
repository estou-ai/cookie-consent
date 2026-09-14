<?php

namespace Estouai\CookieConsent\Tests;

use Statamic\Facades\Permission;

class ServiceProviderTest extends TestCase
{
    public function test_config_defaults_are_registered()
    {
        $this->assertSame(['necessary', 'analytics', 'marketing'], array_keys(config('cookie-consent.groups')));
        $this->assertTrue(config('cookie-consent.groups.necessary.required'));
    }

    public function test_manage_permission_is_registered()
    {
        $this->assertContains('manage cookie consent settings', Permission::all()->map->value()->all());
    }
}
