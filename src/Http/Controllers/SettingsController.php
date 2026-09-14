<?php

namespace Estouai\CookieConsent\Http\Controllers;

use Estouai\CookieConsent\Settings\CookieConsentSettings;
use Illuminate\Http\Request;
use Illuminate\Routing\Controller;
use Statamic\Facades\Site;

class SettingsController extends Controller
{
    public function index(Request $request, CookieConsentSettings $settings)
    {
        $site = $request->query('site', Site::current()->handle());

        return view('cookie-consent::settings', [
            'settings' => $settings->all($site),
            'site' => $site,
            'sites' => Site::all(),
        ]);
    }

    public function update(Request $request, CookieConsentSettings $settings)
    {
        $data = $request->validate([
            'site' => 'required|string',
            'enabled' => 'boolean',
            'version' => 'required|integer|min:1',
            'position' => 'required|string',
            'theme' => 'required|string',
            'text' => 'required|array',
            'consent_mode' => 'required|array',
            'groups_json' => 'required|json',
        ]);

        $data['enabled'] = $request->boolean('enabled');
        $data['groups'] = json_decode($data['groups_json'], true, flags: JSON_THROW_ON_ERROR);
        $site = $data['site'];
        unset($data['groups_json'], $data['site']);

        $settings->save($data, $site);

        return back()->with('success', 'Configurações de cookies salvas.');
    }
}
