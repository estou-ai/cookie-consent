<?php

namespace Estouai\CookieConsent;

use Statamic\Facades\CP\Nav;
use Statamic\Facades\Permission;
use Statamic\Providers\AddonServiceProvider;
use Statamic\Statamic;

class ServiceProvider extends AddonServiceProvider
{
    protected $vite = [
        'input' => ['resources/js/addon.js'],
    ];

    protected $routes = [
        'cp' => __DIR__.'/../routes/cp.php',
        'actions' => __DIR__.'/../routes/actions.php',
    ];

    public function register()
    {
        $this->mergeConfigFrom(__DIR__.'/../config/cookie-consent.php', 'cookie-consent');

        $this->publishes([
            __DIR__.'/../config/cookie-consent.php' => config_path('cookie-consent.php'),
        ], 'cookie-consent-config');
    }

    // Same split as Weave's bootPublishAfterInstall: compiled assets are
    // always force-republished (generated output, never hand-edited by a
    // host app); config is never forced (it's the one file a site is meant
    // to customize).
    protected function bootPublishAfterInstall()
    {
        Statamic::afterInstalled(function ($command) {
            $command->call('vendor:publish', ['--tag' => 'cookie-consent', '--force' => true]);
            $command->call('vendor:publish', ['--tag' => 'cookie-consent-config']);
        });

        return $this;
    }

    public function bootAddon()
    {
        $this->loadViewsFrom(__DIR__.'/../resources/views', 'cookie-consent');

        Permission::register('manage cookie consent settings')
            ->label('Manage Cookie Consent Settings')
            ->group('Cookie Consent');

        Nav::extend(function ($nav) {
            $nav->tools('Cookie Consent')
                ->route('cookie-consent.index')
                ->icon('cookie')
                ->can('manage cookie consent settings');
        });
    }
}
