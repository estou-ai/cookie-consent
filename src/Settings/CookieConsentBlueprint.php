<?php

namespace Estouai\CookieConsent\Settings;

use function Statamic\trans as __;

// Bridges the CP's blueprint-editable shape (a `groups` replicator, one row
// per group) and the shape actually stored on disk and read by
// CookieConsentTags (`groups` keyed by handle, with a sibling `consent_mode`
// map keyed by the same handles — see config/cookie-consent.php). Every other
// field (enabled/version/position/theme/text/button) round-trips as-is: the
// `group` fieldtype already stores/returns nested arrays under one handle,
// identical to how `text`/`button` are already shaped in config.
//
// Field labels/instructions live in resources/lang/{locale}/messages.php
// (cookie-consent::messages.*) — add another locale file with the same keys
// to translate the settings screen; nothing here needs to change.
class CookieConsentBlueprint
{
    // Google Consent Mode v2 signals — see
    // https://developers.google.com/tag-platform/security/guides/consent
    protected const CONSENT_MODE_SIGNALS = [
        'ad_storage' => 'ad_storage',
        'ad_user_data' => 'ad_user_data',
        'ad_personalization' => 'ad_personalization',
        'analytics_storage' => 'analytics_storage',
        'functionality_storage' => 'functionality_storage',
        'personalization_storage' => 'personalization_storage',
        'security_storage' => 'security_storage',
    ];

    // Grouped into sections (one Statamic tab, several headed field groups —
    // see Fields/Tab.php's `sections()`) matching the original hand-rolled
    // screen's cards. fields() below flattens this back to a plain field
    // list for Fields::process()/validate()/etc., which don't care about
    // section structure.
    public static function sections(): array
    {
        return [
            ['display' => __('cookie-consent::messages.section_general'), 'fields' => [
                ['handle' => 'enabled', 'field' => [
                    'type' => 'toggle', 'display' => __('cookie-consent::messages.enabled'),
                    'instructions' => __('cookie-consent::messages.enabled_instructions'),
                ]],
                ['handle' => 'version', 'field' => [
                    'type' => 'integer', 'display' => __('cookie-consent::messages.version'), 'validate' => 'required|integer|min:1',
                    'instructions' => __('cookie-consent::messages.version_instructions'),
                ]],
            ]],
            ['display' => __('cookie-consent::messages.section_appearance'), 'fields' => [
                ['handle' => 'position', 'field' => [
                    'type' => 'select', 'display' => __('cookie-consent::messages.position'),
                    'options' => [
                        'bottom' => __('cookie-consent::messages.position_bottom'),
                        'top' => __('cookie-consent::messages.position_top'),
                        'bottom-left' => __('cookie-consent::messages.position_bottom_left'),
                        'bottom-right' => __('cookie-consent::messages.position_bottom_right'),
                    ],
                ]],
                ['handle' => 'theme', 'field' => [
                    'type' => 'select', 'display' => __('cookie-consent::messages.theme'),
                    'options' => [
                        'auto' => __('cookie-consent::messages.theme_auto'),
                        'light' => __('cookie-consent::messages.theme_light'),
                        'dark' => __('cookie-consent::messages.theme_dark'),
                    ],
                ]],
            ]],
            ['display' => __('cookie-consent::messages.text'), 'fields' => [self::textField()]],
            ['display' => __('cookie-consent::messages.button'), 'fields' => [self::buttonField()]],
            ['display' => __('cookie-consent::messages.groups'), 'fields' => [self::groupsField()]],
        ];
    }

    public static function fields(): array
    {
        return collect(self::sections())->flatMap->fields->all();
    }

    protected static function textField(): array
    {
        return ['handle' => 'text', 'field' => [
            'type' => 'group', 'display' => __('cookie-consent::messages.text'), 'fields' => [
                ['handle' => 'title', 'field' => ['type' => 'text', 'display' => __('cookie-consent::messages.text_title')]],
                ['handle' => 'description', 'field' => ['type' => 'textarea', 'display' => __('cookie-consent::messages.text_description')]],
                ['handle' => 'accept_all', 'field' => ['type' => 'text', 'display' => __('cookie-consent::messages.text_accept_all')]],
                ['handle' => 'reject_all', 'field' => ['type' => 'text', 'display' => __('cookie-consent::messages.text_reject_all')]],
                ['handle' => 'customize', 'field' => ['type' => 'text', 'display' => __('cookie-consent::messages.text_customize')]],
                ['handle' => 'save', 'field' => ['type' => 'text', 'display' => __('cookie-consent::messages.text_save')]],
                ['handle' => 'privacy_policy_label', 'field' => ['type' => 'text', 'display' => __('cookie-consent::messages.text_privacy_policy_label')]],
                ['handle' => 'privacy_policy_url', 'field' => ['type' => 'text', 'display' => __('cookie-consent::messages.text_privacy_policy_url')]],
            ],
        ]];
    }

    protected static function buttonField(): array
    {
        return ['handle' => 'button', 'field' => [
            'type' => 'group', 'display' => __('cookie-consent::messages.button'),
            'instructions' => __('cookie-consent::messages.button_instructions'),
            'fields' => [
                ['handle' => 'enabled', 'field' => ['type' => 'toggle', 'display' => __('cookie-consent::messages.button_enabled')]],
                ['handle' => 'label', 'field' => ['type' => 'text', 'display' => __('cookie-consent::messages.button_label')]],
                ['handle' => 'aria_label', 'field' => ['type' => 'text', 'display' => __('cookie-consent::messages.button_aria_label')]],
                ['handle' => 'position', 'field' => [
                    'type' => 'select', 'display' => __('cookie-consent::messages.button_position'),
                    'options' => [
                        'bottom-left' => __('cookie-consent::messages.position_bottom_left'),
                        'bottom-right' => __('cookie-consent::messages.position_bottom_right'),
                        'top-left' => __('cookie-consent::messages.button_position_top_left'),
                        'top-right' => __('cookie-consent::messages.button_position_top_right'),
                    ],
                ]],
                ['handle' => 'background', 'field' => ['type' => 'color', 'display' => __('cookie-consent::messages.button_background')]],
                ['handle' => 'foreground', 'field' => ['type' => 'color', 'display' => __('cookie-consent::messages.button_foreground')]],
                // Plain text, not `color`: these are raw CSS values (rgba()/box-shadow
                // strings, or an SVG string), and Color's config only picks a hex value.
                ['handle' => 'border', 'field' => ['type' => 'text', 'display' => __('cookie-consent::messages.button_border')]],
                ['handle' => 'shadow', 'field' => ['type' => 'text', 'display' => __('cookie-consent::messages.button_shadow')]],
                ['handle' => 'icon', 'field' => [
                    'type' => 'text', 'display' => __('cookie-consent::messages.button_icon'),
                    'instructions' => __('cookie-consent::messages.button_icon_instructions'),
                ]],
                ['handle' => 'icon_background', 'field' => ['type' => 'color', 'display' => __('cookie-consent::messages.button_icon_background')]],
                ['handle' => 'icon_foreground', 'field' => ['type' => 'color', 'display' => __('cookie-consent::messages.button_icon_foreground')]],
            ],
        ]];
    }

    protected static function groupsField(): array
    {
        return ['handle' => 'groups', 'field' => [
            'type' => 'replicator', 'display' => __('cookie-consent::messages.groups'),
            'sets' => ['group' => ['fields' => [
                ['handle' => 'handle', 'field' => [
                    'type' => 'text', 'display' => __('cookie-consent::messages.group_handle'), 'validate' => 'required|alpha_dash',
                    'instructions' => __('cookie-consent::messages.group_handle_instructions'),
                ]],
                ['handle' => 'name', 'field' => ['type' => 'text', 'display' => __('cookie-consent::messages.group_name'), 'validate' => 'required']],
                ['handle' => 'description', 'field' => ['type' => 'textarea', 'display' => __('cookie-consent::messages.group_description')]],
                ['handle' => 'required', 'field' => [
                    'type' => 'toggle', 'display' => __('cookie-consent::messages.group_required'),
                    'instructions' => __('cookie-consent::messages.group_required_instructions'),
                ]],
                ['handle' => 'default', 'field' => ['type' => 'toggle', 'display' => __('cookie-consent::messages.group_default')]],
                ['handle' => 'legal_basis', 'field' => [
                    'type' => 'select', 'display' => __('cookie-consent::messages.group_legal_basis'),
                    // Values are the stored data (consumed by tags/README as-is), not
                    // translated — only their display labels are.
                    'options' => [
                        'consentimento' => __('cookie-consent::messages.legal_basis_consent'),
                        'legitimo_interesse' => __('cookie-consent::messages.legal_basis_legitimate_interest'),
                        'obrigacao_legal' => __('cookie-consent::messages.legal_basis_legal_obligation'),
                    ],
                ]],
                ['handle' => 'consent_mode', 'field' => [
                    'type' => 'checkboxes', 'display' => __('cookie-consent::messages.group_consent_mode'),
                    'options' => self::CONSENT_MODE_SIGNALS,
                ]],
                ['handle' => 'cookies', 'field' => [
                    'type' => 'grid', 'display' => __('cookie-consent::messages.group_cookies'), 'fields' => [
                        ['handle' => 'name', 'field' => ['type' => 'text', 'display' => __('cookie-consent::messages.cookie_name'), 'width' => 33]],
                        ['handle' => 'purpose', 'field' => ['type' => 'text', 'display' => __('cookie-consent::messages.cookie_purpose'), 'width' => 34]],
                        ['handle' => 'retention', 'field' => ['type' => 'text', 'display' => __('cookie-consent::messages.cookie_retention'), 'width' => 33]],
                    ],
                ]],
            ]]],
        ]];
    }

    public static function toEditable(array $settings): array
    {
        $consentMode = $settings['consent_mode'] ?? [];

        $settings['groups'] = collect($settings['groups'] ?? [])
            ->map(fn (array $group, string $handle) => array_merge($group, [
                // `type` names the replicator set (see fields() — a single
                // "group" set) — required on every row even with one set.
                'type' => 'group',
                'handle' => $handle,
                'consent_mode' => $consentMode[$handle] ?? [],
            ]))
            ->values()
            ->all();

        return $settings;
    }

    public static function toStorage(array $values): array
    {
        $rows = collect($values['groups'] ?? []);

        $values['consent_mode'] = $rows->mapWithKeys(fn (array $row) => [
            $row['handle'] => $row['consent_mode'] ?? [],
        ])->all();

        $values['groups'] = $rows->mapWithKeys(function (array $row) {
            $handle = $row['handle'];
            // `type`/`_id`/`enabled` are the replicator fieldtype's own
            // bookkeeping (set name, row id, per-row enable toggle — see
            // Replicator::processRow()), not fields this blueprint defines.
            unset($row['type'], $row['handle'], $row['consent_mode'], $row['_id'], $row['enabled']);

            return [$handle => $row];
        })->all();

        return $values;
    }
}
