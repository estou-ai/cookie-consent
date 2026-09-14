<?php

// Settings-screen field labels/instructions — referenced from
// CookieConsentBlueprint via __('cookie-consent::messages.*'). Auto-loaded
// by Statamic\Providers\AddonServiceProvider::bootTranslations() (namespaced
// by the addon slug, "cookie-consent" — same namespace the views already
// use). Add resources/lang/{locale}/messages.php with the same keys for
// another CP language; nothing else needs to change.
return [
    'section_general' => 'General',
    'section_appearance' => 'Appearance',

    'enabled' => 'Enable',
    'enabled_instructions' => 'Off: no banner appears and every group-gated block of content is treated as allowed.',

    'version' => 'Consent version',
    'version_instructions' => 'Increase this number to make every visitor decide again.',

    'position' => 'Banner position',
    'position_bottom' => 'Bottom',
    'position_top' => 'Top',
    'position_bottom_left' => 'Bottom left',
    'position_bottom_right' => 'Bottom right',

    'theme' => 'Theme',
    'theme_auto' => 'Automatic (system)',
    'theme_light' => 'Light',
    'theme_dark' => 'Dark',

    'text' => 'Banner text',
    'text_title' => 'Title',
    'text_description' => 'Description',
    'text_accept_all' => 'Accept-all button',
    'text_reject_all' => 'Reject button',
    'text_customize' => 'Customize button',
    'text_save' => 'Save-preferences button',
    'text_privacy_policy_label' => 'Privacy policy link label',
    'text_privacy_policy_url' => 'Privacy policy URL',

    'button' => 'Floating button',
    'button_instructions' => 'Configures the preferences button that stays visible after the first decision.',
    'button_enabled' => 'Enable',
    'button_label' => 'Label',
    'button_aria_label' => 'Aria label',
    'button_position' => 'Position',
    'button_position_top_left' => 'Top left',
    'button_position_top_right' => 'Top right',
    'button_background' => 'Background color',
    'button_foreground' => 'Text color',
    'button_border' => 'Border (CSS)',
    'button_shadow' => 'Shadow (CSS)',
    'button_icon' => 'Icon',
    'button_icon_instructions' => '`cookie`, `none`, or an SVG.',
    'button_icon_background' => 'Icon background color',
    'button_icon_foreground' => 'Icon color',

    'groups' => 'Cookie groups',
    'group_handle' => 'Key',
    'group_handle_instructions' => 'Technical identifier, e.g. `analytics`. Used by `consent_mode` and `{{ cookie_consent:allowed group="" }}`.',
    'group_name' => 'Name',
    'group_description' => 'Description',
    'group_required' => 'Required',
    'group_required_instructions' => "Can't be turned off by the visitor.",
    'group_default' => 'On by default',
    'group_legal_basis' => 'Legal basis (GDPR/LGPD)',
    'legal_basis_consent' => 'Consent',
    'legal_basis_legitimate_interest' => 'Legitimate interest',
    'legal_basis_legal_obligation' => 'Legal obligation',
    'group_consent_mode' => 'Google Consent Mode signals',
    'group_cookies' => 'Cookies',
    'cookie_name' => 'Name',
    'cookie_purpose' => 'Purpose',
    'cookie_retention' => 'Retention',
];
