<?php

namespace Estouai\CookieConsent\Tests;

use Estouai\CookieConsent\ServiceProvider;
use Statamic\Testing\AddonTestCase;

abstract class TestCase extends AddonTestCase
{
    protected string $addonServiceProvider = ServiceProvider::class;
}
