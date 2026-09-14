<?php

// Defaults written for the EU's RGPD/GDPR (Regulamento (UE) 2016/679). A
// host app overrides any of this either by editing this published config
// file, or via the CP settings screen — CP edits win (see
// Settings\CookieConsentSettings) and are stored separately per site under
// content/cookie-consent/{site}/settings.yaml, so upgrading the addon never
// clobbers a site's own copy/groups. A site under a different regime (e.g.
// Brazil's LGPD) just edits its own copy in the CP — `legal_basis`'s three
// values (consentimento / legitimo_interesse / obrigacao_legal) map to both
// RGPD Art. 6º and LGPD Art. 7º, so the field itself doesn't need to change.
return [

    // Off entirely — banner/button/scripts tags render nothing, and every
    // group counts as allowed (no consent needed). Useful for a staging site
    // or a jurisdiction where the banner shouldn't show.
    'enabled' => true,

    'version' => 1,

    // Bumping this invalidates every visitor's stored consent, forcing the
    // banner to show again — same mechanism the original addon uses.
    'text' => [
        'title' => 'Utilizamos cookies',
        'description' => 'Utilizamos cookies para melhorar a sua experiência, analisar a utilização do site e, com o seu consentimento, personalizar conteúdos e anúncios, em conformidade com o Regulamento Geral sobre a Proteção de Dados (RGPD).',
        'accept_all' => 'Aceitar tudo',
        'reject_all' => 'Recusar não essenciais',
        'customize' => 'Personalizar',
        'save' => 'Guardar preferências',
        'privacy_policy_label' => 'Política de Privacidade',
        'privacy_policy_url' => '/politica-de-privacidade',
    ],

    'position' => 'bottom', // bottom | top | bottom-left | bottom-right
    'theme' => 'auto', // auto | light | dark

    'button' => [
        'enabled' => true,
        'label' => 'Cookies',
        'aria_label' => 'Preferências de cookies',
        'position' => 'bottom-left', // bottom-left | bottom-right | top-left | top-right
        'background' => '#ffffff',
        'foreground' => '#6b7280',
        'border' => 'rgba(99, 102, 241, 0.16)',
        'shadow' => '0 8px 24px rgba(0, 0, 0, 0.16)',
        'icon' => 'cookie', // cookie | none | any HTML/SVG string
        'icon_background' => '#6366f1',
        'icon_foreground' => '#ffffff',
    ],

    'groups' => [
        'necessary' => [
            'name' => 'Necessários',
            'description' => 'Essenciais para o funcionamento do site. Não podem ser desligados.',
            'required' => true,
            'default' => true,
            'legal_basis' => 'obrigacao_legal',
            'cookies' => [
                [
                    'name' => 'cookie_consent',
                    'purpose' => 'Guarda as suas preferências de cookies.',
                    'retention' => '12 meses',
                ],
            ],
        ],
        'analytics' => [
            'name' => 'Analíticos',
            'description' => 'Ajudam-nos a perceber como o site é utilizado, de forma agregada.',
            'required' => false,
            'default' => false,
            'legal_basis' => 'legitimo_interesse',
            'cookies' => [],
        ],
        'marketing' => [
            'name' => 'Marketing',
            'description' => 'Utilizados para personalizar anúncios e medir campanhas.',
            'required' => false,
            'default' => false,
            'legal_basis' => 'consentimento',
            'cookies' => [],
        ],
    ],

    // Maps a cookie group to the Google Consent Mode v2 signals it grants.
    // consent-mode.js reads this straight through — no Google-specific PHP.
    'consent_mode' => [
        'necessary' => ['security_storage'],
        'analytics' => ['analytics_storage'],
        'marketing' => ['ad_storage', 'ad_user_data', 'ad_personalization'],
    ],

];
