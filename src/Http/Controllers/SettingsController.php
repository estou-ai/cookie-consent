<?php

namespace Estouai\CookieConsent\Http\Controllers;

use Estouai\CookieConsent\Settings\CookieConsentBlueprint;
use Estouai\CookieConsent\Settings\CookieConsentSettings;
use Illuminate\Http\Request;
use Illuminate\Routing\Controller;
use Statamic\Facades\Site;
use Statamic\Fields\Blueprint;

class SettingsController extends Controller
{
    public function index(Request $request, CookieConsentSettings $settings)
    {
        $site = $request->query('site', Site::current()->handle());

        $fields = $this->fields()
            ->addValues(CookieConsentBlueprint::toEditable($settings->all($site)))
            ->preProcess();

        return view('cookie-consent::settings', [
            'blueprint' => $fields->toPublishArray(),
            'values' => $fields->values()->all(),
            'meta' => $fields->meta(),
            'site' => $site,
            'sites' => Site::all(),
        ]);
    }

    // PublishForm's save pipeline POSTs the container's flat field values
    // directly as the JSON body (no wrapper key) — confirmed by reading the
    // compiled @statamic/cms/ui bundle's Request/Container save logic.
    public function update(Request $request, CookieConsentSettings $settings)
    {
        $site = $request->query('site', Site::current()->handle());

        $fields = $this->fields()->addValues($request->all());
        $fields->validate();

        $settings->save(CookieConsentBlueprint::toStorage($fields->process()->values()->all()), $site);

        return response()->json([]);
    }

    protected function fields()
    {
        return Blueprint::make()->setContents(['fields' => CookieConsentBlueprint::fields()])->fields();
    }
}
