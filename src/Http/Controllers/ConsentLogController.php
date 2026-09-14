<?php

namespace Estouai\CookieConsent\Http\Controllers;

use Estouai\CookieConsent\ConsentLog\ConsentLogger;
use Illuminate\Http\Request;
use Illuminate\Routing\Controller;
use Statamic\Facades\Site;

class ConsentLogController extends Controller
{
    public function store(Request $request, ConsentLogger $logger)
    {
        $data = $request->validate([
            'version' => 'required|integer',
            'groups' => 'required|array',
            'groups.*' => 'string',
            'source' => 'nullable|in:explicit,gpc',
            'page' => 'nullable|string|max:2048',
        ]);

        $data['source'] ??= 'explicit';

        $logger->record([
            ...$data,
            'site' => Site::current()->handle(),
            'timestamp' => now()->toIso8601String(),
            // Hashed, not raw — enough to correlate/dedupe without storing a
            // visitor's real IP long-term.
            'ip_hash' => hash('sha256', $request->ip().config('app.key')),
            'user_agent' => substr((string) $request->userAgent(), 0, 255),
        ]);

        return response()->noContent();
    }
}
