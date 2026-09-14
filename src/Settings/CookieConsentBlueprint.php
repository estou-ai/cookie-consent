<?php

namespace Estouai\CookieConsent\Settings;

// Bridges the CP's blueprint-editable shape (a `groups` replicator, one row
// per group) and the shape actually stored on disk and read by
// CookieConsentTags (`groups` keyed by handle, with a sibling `consent_mode`
// map keyed by the same handles — see config/cookie-consent.php). Every other
// field (enabled/version/position/theme/text/button) round-trips as-is: the
// `group` fieldtype already stores/returns nested arrays under one handle,
// identical to how `text`/`button` are already shaped in config.
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

    protected const LEGAL_BASIS_OPTIONS = [
        'consentimento' => 'Consentimento',
        'legitimo_interesse' => 'Legítimo interesse',
        'obrigacao_legal' => 'Obrigação legal',
    ];

    public static function fields(): array
    {
        return [
            ['handle' => 'enabled', 'field' => [
                'type' => 'toggle', 'display' => 'Ativar',
                'instructions' => 'Desativado: nenhum banner aparece e todo conteúdo condicionado a grupos é tratado como permitido.',
            ]],
            ['handle' => 'version', 'field' => [
                'type' => 'integer', 'display' => 'Versão do consentimento', 'validate' => 'required|integer|min:1',
                'instructions' => 'Aumente esse número para forçar todos os visitantes a decidir de novo.',
            ]],
            ['handle' => 'position', 'field' => [
                'type' => 'select', 'display' => 'Posição do banner',
                'options' => ['bottom' => 'Inferior', 'top' => 'Superior', 'bottom-left' => 'Inferior esquerda', 'bottom-right' => 'Inferior direita'],
            ]],
            ['handle' => 'theme', 'field' => [
                'type' => 'select', 'display' => 'Tema',
                'options' => ['auto' => 'Automático (sistema)', 'light' => 'Claro', 'dark' => 'Escuro'],
            ]],
            ['handle' => 'text', 'field' => [
                'type' => 'group', 'display' => 'Textos do banner', 'fields' => [
                    ['handle' => 'title', 'field' => ['type' => 'text', 'display' => 'Título']],
                    ['handle' => 'description', 'field' => ['type' => 'textarea', 'display' => 'Descrição']],
                    ['handle' => 'accept_all', 'field' => ['type' => 'text', 'display' => 'Botão aceitar tudo']],
                    ['handle' => 'reject_all', 'field' => ['type' => 'text', 'display' => 'Botão rejeitar']],
                    ['handle' => 'customize', 'field' => ['type' => 'text', 'display' => 'Botão personalizar']],
                    ['handle' => 'save', 'field' => ['type' => 'text', 'display' => 'Botão salvar preferências']],
                    ['handle' => 'privacy_policy_label', 'field' => ['type' => 'text', 'display' => 'Rótulo do link da política']],
                    ['handle' => 'privacy_policy_url', 'field' => ['type' => 'text', 'display' => 'URL da política de privacidade']],
                ],
            ]],
            ['handle' => 'button', 'field' => [
                'type' => 'group', 'display' => 'Botão flutuante',
                'instructions' => 'Configura o botão de preferências que fica sempre visível depois da primeira decisão.',
                'fields' => [
                    ['handle' => 'enabled', 'field' => ['type' => 'toggle', 'display' => 'Ativar']],
                    ['handle' => 'label', 'field' => ['type' => 'text', 'display' => 'Rótulo']],
                    ['handle' => 'aria_label', 'field' => ['type' => 'text', 'display' => 'Aria label']],
                    ['handle' => 'position', 'field' => [
                        'type' => 'select', 'display' => 'Posição',
                        'options' => ['bottom-left' => 'Inferior esquerda', 'bottom-right' => 'Inferior direita', 'top-left' => 'Superior esquerda', 'top-right' => 'Superior direita'],
                    ]],
                    ['handle' => 'background', 'field' => ['type' => 'color', 'display' => 'Cor de fundo']],
                    ['handle' => 'foreground', 'field' => ['type' => 'color', 'display' => 'Cor do texto']],
                    // Plain text, not `color`: these are raw CSS values (rgba()/box-shadow
                    // strings, or an SVG string), and Color's config only picks a hex value.
                    ['handle' => 'border', 'field' => ['type' => 'text', 'display' => 'Borda (CSS)']],
                    ['handle' => 'shadow', 'field' => ['type' => 'text', 'display' => 'Sombra (CSS)']],
                    ['handle' => 'icon', 'field' => ['type' => 'text', 'display' => 'Ícone', 'instructions' => '`cookie`, `none`, ou um SVG.']],
                    ['handle' => 'icon_background', 'field' => ['type' => 'color', 'display' => 'Cor de fundo do ícone']],
                    ['handle' => 'icon_foreground', 'field' => ['type' => 'color', 'display' => 'Cor do ícone']],
                ],
            ]],
            ['handle' => 'groups', 'field' => [
                'type' => 'replicator', 'display' => 'Grupos de cookies',
                'sets' => ['group' => ['fields' => [
                    ['handle' => 'handle', 'field' => [
                        'type' => 'text', 'display' => 'Chave', 'validate' => 'required|alpha_dash',
                        'instructions' => 'Identificador técnico, ex.: `analytics`. Usado por `consent_mode` e `{{ cookie_consent:allowed group="" }}`.',
                    ]],
                    ['handle' => 'name', 'field' => ['type' => 'text', 'display' => 'Nome', 'validate' => 'required']],
                    ['handle' => 'description', 'field' => ['type' => 'textarea', 'display' => 'Descrição']],
                    ['handle' => 'required', 'field' => ['type' => 'toggle', 'display' => 'Obrigatório', 'instructions' => 'Não pode ser desligado pelo visitante.']],
                    ['handle' => 'default', 'field' => ['type' => 'toggle', 'display' => 'Ativo por omissão']],
                    ['handle' => 'legal_basis', 'field' => ['type' => 'select', 'display' => 'Base legal (RGPD/LGPD)', 'options' => self::LEGAL_BASIS_OPTIONS]],
                    ['handle' => 'consent_mode', 'field' => [
                        'type' => 'checkboxes', 'display' => 'Sinais do Google Consent Mode',
                        'options' => self::CONSENT_MODE_SIGNALS,
                    ]],
                    ['handle' => 'cookies', 'field' => [
                        'type' => 'grid', 'display' => 'Cookies', 'fields' => [
                            ['handle' => 'name', 'field' => ['type' => 'text', 'display' => 'Nome', 'width' => 33]],
                            ['handle' => 'purpose', 'field' => ['type' => 'text', 'display' => 'Finalidade', 'width' => 34]],
                            ['handle' => 'retention', 'field' => ['type' => 'text', 'display' => 'Retenção', 'width' => 33]],
                        ],
                    ]],
                ]]],
            ]],
        ];
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
