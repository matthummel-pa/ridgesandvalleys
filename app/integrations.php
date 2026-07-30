<?php

/**
 * Third-party integrations: AI providers and connected apps.
 *
 * Two surfaces, split by sensitivity:
 *   - Customizer → "Integrations & Connections": enable toggles and NON-secret
 *     config (model names, IDs, embed/webhook-adjacent options) for AI services
 *     (Anthropic, OpenAI, Google Gemini) and apps (Notion, Asana, Google
 *     Calendar, HubSpot, Slack, Zapier, Make).
 *   - Appearance → "Theme APIs": the hardened home for the actual secrets. Keys
 *     are write-only (shown masked after saving), encrypted at rest, overridable
 *     and lockable via wp-config.php constants.
 *
 * Everything is driven by one filterable registry, so the theme is deliberately
 * third-party friendly.
 *
 * EXTENDING (for plugins / other developers):
 *   - Register or modify integrations with the `rv/integrations` filter. Anything
 *     you add automatically gets its own Customizer section and helper access —
 *     no other code changes needed:
 *
 *         add_filter('rv/integrations', function ($registry) {
 *             $registry['items']['airtable'] = [
 *                 'group'  => 'apps',
 *                 'label'  => 'Airtable',
 *                 'help'   => 'Create a personal access token at airtable.com/create/tokens.',
 *                 'fields' => [
 *                     'token'   => ['label' => 'Access token', 'type' => 'password', 'secret' => true],
 *                     'base_id' => ['label' => 'Base ID', 'type' => 'text'],
 *                 ],
 *             ];
 *             return $registry;
 *         });
 *
 *   - Read a stored value anywhere server-side:
 *         $key = \App\integration('anthropic', 'api_key');
 *         if (\App\integration_enabled('notion')) { ... }
 *
 * SECURITY:
 *   - Values flagged 'secret' are for SERVER-SIDE use only (API calls made from
 *     PHP). Never echo them into page markup, inline scripts, or REST responses.
 *   - Secrets entered in the UI are encrypted at rest (libsodium + a key derived
 *     from this site's WordPress salt) and are never rendered back in full.
 *   - MOST SECURE: define secrets as wp-config.php constants. A defined constant
 *     overrides the database value AND locks the field. Constant name is
 *     RV_INTEGRATION_<PROVIDER>_<FIELD> in upper case, e.g.:
 *         define('RV_INTEGRATION_ANTHROPIC_API_KEY', getenv('ANTHROPIC_API_KEY'));
 *   - LOCK DOWN: define('RV_INTEGRATIONS_LOCK', true) makes the whole Theme APIs
 *     page read-only (keys only from wp-config.php). define('RV_INTEGRATIONS_-
 *     DISABLED', true) is a master kill-switch that turns every integration off.
 *   - Access to the Theme APIs page requires `manage_options` (filterable via
 *     `rv/api_keys_capability`); Customizer settings require the same.
 */

namespace App;

/**
 * The integration registry: grouped metadata + the integrations themselves.
 * Filterable via `rv/integrations`.
 */
function integrations(): array
{
    $groups = [
        'ai' => [
            'label'       => __('AI Providers', 'sage'),
            'description' => __('API keys for AI services. Stored on your site and used server-side to power AI features.', 'sage'),
        ],
        'apps' => [
            'label'       => __('Connected Apps', 'sage'),
            'description' => __('Connect the tools you already use. Tokens are stored on your site and used server-side.', 'sage'),
        ],
        'automation' => [
            'label'       => __('Automation & Webhooks', 'sage'),
            'description' => __('Send events to no-code automation tools.', 'sage'),
        ],
    ];

    $items = [
        /* ---- AI providers ---- */
        'anthropic' => [
            'group'  => 'ai',
            'label'  => __('Anthropic (Claude)', 'sage'),
            'help'   => __('Create a key at console.anthropic.com → API Keys.', 'sage'),
            'fields' => [
                'api_key' => ['label' => __('API key', 'sage'), 'type' => 'password', 'secret' => true, 'placeholder' => 'sk-ant-…'],
                'model'   => ['label' => __('Default model', 'sage'), 'type' => 'text', 'placeholder' => 'claude-sonnet-4-5'],
            ],
        ],
        'openai' => [
            'group'  => 'ai',
            'label'  => __('OpenAI', 'sage'),
            'help'   => __('Create a key at platform.openai.com → API keys.', 'sage'),
            'fields' => [
                'api_key' => ['label' => __('API key', 'sage'), 'type' => 'password', 'secret' => true, 'placeholder' => 'sk-…'],
                'org_id'  => ['label' => __('Organization ID (optional)', 'sage'), 'type' => 'text'],
                'model'   => ['label' => __('Default model', 'sage'), 'type' => 'text', 'placeholder' => 'gpt-4o'],
            ],
        ],
        'google_gemini' => [
            'group'  => 'ai',
            'label'  => __('Google Gemini', 'sage'),
            'help'   => __('Create a key in Google AI Studio → Get API key.', 'sage'),
            'fields' => [
                'api_key' => ['label' => __('API key', 'sage'), 'type' => 'password', 'secret' => true],
                'model'   => ['label' => __('Default model', 'sage'), 'type' => 'text', 'placeholder' => 'gemini-1.5-pro'],
            ],
        ],

        /* ---- Connected apps ---- */
        'notion' => [
            'group'  => 'apps',
            'label'  => __('Notion', 'sage'),
            'help'   => __('Create an internal integration at notion.so/my-integrations, then share the database with it.', 'sage'),
            'fields' => [
                'token'       => ['label' => __('Internal integration token', 'sage'), 'type' => 'password', 'secret' => true, 'placeholder' => 'ntn_…'],
                'database_id' => ['label' => __('Database ID', 'sage'), 'type' => 'text'],
            ],
        ],
        'asana' => [
            'group'  => 'apps',
            'label'  => __('Asana', 'sage'),
            'help'   => __('Create a personal access token in Asana → My Settings → Apps → Developer apps.', 'sage'),
            'fields' => [
                'pat'          => ['label' => __('Personal access token', 'sage'), 'type' => 'password', 'secret' => true],
                'workspace_id' => ['label' => __('Workspace / project ID (optional)', 'sage'), 'type' => 'text'],
            ],
        ],
        'google_calendar' => [
            'group'  => 'apps',
            'label'  => __('Google Calendar', 'sage'),
            'help'   => __('Use a calendar ID plus an API key, or paste a public embed URL.', 'sage'),
            'fields' => [
                'calendar_id' => ['label' => __('Calendar ID', 'sage'), 'type' => 'text', 'placeholder' => 'you@gmail.com'],
                'api_key'     => ['label' => __('API key', 'sage'), 'type' => 'password', 'secret' => true],
                'embed_url'   => ['label' => __('Public embed URL (optional)', 'sage'), 'type' => 'url'],
            ],
        ],
        'hubspot' => [
            'group'  => 'apps',
            'label'  => __('HubSpot', 'sage'),
            'help'   => __('Create a private app in HubSpot → Settings → Integrations → Private Apps.', 'sage'),
            'fields' => [
                'access_token' => ['label' => __('Private app token', 'sage'), 'type' => 'password', 'secret' => true, 'placeholder' => 'pat-…'],
                'portal_id'    => ['label' => __('Hub ID / Portal ID', 'sage'), 'type' => 'text'],
            ],
        ],
        'slack' => [
            'group'  => 'apps',
            'label'  => __('Slack', 'sage'),
            'help'   => __('Create an incoming webhook at api.slack.com/apps.', 'sage'),
            'fields' => [
                'webhook_url' => ['label' => __('Incoming webhook URL', 'sage'), 'type' => 'url', 'secret' => true, 'placeholder' => 'https://hooks.slack.com/services/…'],
            ],
        ],
        'github' => [
            'group'  => 'apps',
            'label'  => __('GitHub', 'sage'),
            'help'   => __('Optional. A token with public-repo read access raises the API rate limit for the GitHub social-proof section. Choose what to display on the GitHub settings page (Appearance → GitHub).', 'sage'),
            'fields' => [
                'token' => ['label' => __('Access token (optional)', 'sage'), 'type' => 'password', 'secret' => true, 'placeholder' => 'github_pat_… / ghp_…'],
            ],
        ],

        /* ---- Automation ---- */
        'zapier' => [
            'group'  => 'automation',
            'label'  => __('Zapier', 'sage'),
            'help'   => __('Paste a "Catch Hook" trigger URL from your Zap.', 'sage'),
            'fields' => [
                'webhook_url' => ['label' => __('Zapier webhook URL', 'sage'), 'type' => 'url', 'secret' => true, 'placeholder' => 'https://hooks.zapier.com/hooks/catch/…'],
            ],
        ],
        'make' => [
            'group'  => 'automation',
            'label'  => __('Make (Integromat)', 'sage'),
            'help'   => __('Paste a custom webhook URL from a Make scenario.', 'sage'),
            'fields' => [
                'webhook_url' => ['label' => __('Make webhook URL', 'sage'), 'type' => 'url', 'secret' => true, 'placeholder' => 'https://hook.make.com/…'],
            ],
        ],
    ];

    return apply_filters('rv/integrations', compact('groups', 'items'));
}

/**
 * Option key for a single integration field.
 */
function integration_option_key(string $provider, string $field): string
{
    return 'rv_integration_' . $provider . '_' . $field;
}

/**
 * Constant name that can override a stored value (kept out of the database).
 */
function integration_constant_name(string $provider, string $field): string
{
    return 'RV_INTEGRATION_' . strtoupper($provider) . '_' . strtoupper($field);
}

/**
 * Read an integration value (server-side use).
 *
 * Resolution order: wp-config constant → stored option → $default, then the
 * `rv/integration_value` filter (for secret managers / programmatic overrides).
 */
function integration(string $provider, string $field = 'api_key', $default = '')
{
    if (integration_is_constant($provider, $field)) {
        $value = constant(integration_constant_name($provider, $field));
    } else {
        $stored = get_option(integration_option_key($provider, $field), null);
        if ($stored === null || $stored === '') {
            $value = $default;
        } elseif (integration_field_is_secret($provider, $field)) {
            $value = secret_decrypt((string) $stored);
        } else {
            $value = $stored;
        }
    }

    return apply_filters('rv/integration_value', $value, $provider, $field);
}

/**
 * Whether a given field is flagged as a secret in the registry.
 */
function integration_field_is_secret(string $provider, string $field): bool
{
    $items = integrations()['items'];
    return ! empty($items[$provider]['fields'][$field]['secret']);
}

/**
 * Whether the value for a field is locked by a wp-config constant.
 */
function integration_is_constant(string $provider, string $field): bool
{
    $const = integration_constant_name($provider, $field);
    return defined($const) && constant($const) !== '' && constant($const) !== false;
}

/**
 * Whether an integration has been switched on. Filterable via
 * `rv/integration_enabled`.
 */
function integration_enabled(string $provider): bool
{
    if (integrations_disabled()) {
        return false;
    }
    $on = (bool) get_option(integration_option_key($provider, 'enabled'), false);
    return (bool) apply_filters('rv/integration_enabled', $on, $provider);
}

/**
 * LOCKDOWN: keys are managed in wp-config.php only. When on, the Theme APIs
 * page becomes read-only and the form refuses to write any secret to the
 * database. Enforced by a constant so it cannot be flipped off from the admin
 * (e.g. by an attacker who gained a login).
 *
 *     define('RV_INTEGRATIONS_LOCK', true);
 */
function integrations_locked(): bool
{
    $locked = defined('RV_INTEGRATIONS_LOCK') && constant('RV_INTEGRATIONS_LOCK');
    return (bool) apply_filters('rv/integrations_locked', $locked);
}

/**
 * MASTER KILL-SWITCH: turn every integration off at once, regardless of its
 * individual toggle. Enforced by a constant so it survives a compromised admin.
 *
 *     define('RV_INTEGRATIONS_DISABLED', true);
 */
function integrations_disabled(): bool
{
    $off = defined('RV_INTEGRATIONS_DISABLED') && constant('RV_INTEGRATIONS_DISABLED');
    return (bool) apply_filters('rv/integrations_disabled', $off);
}

/**
 * Capability required to view/edit the Theme APIs page. Filterable so access
 * can be tightened further (e.g. to a single super-admin role).
 */
function api_keys_capability(): string
{
    return (string) apply_filters('rv/api_keys_capability', 'manage_options');
}

/**
 * The site's preferred AI provider key (falls back to the first AI provider).
 */
function ai_default_provider(): string
{
    $registry = integrations();
    $aiKeys   = array_keys(array_filter(
        $registry['items'],
        fn ($i) => ($i['group'] ?? '') === 'ai'
    ));

    $chosen = (string) get_option('rv_integration_ai_default', $aiKeys[0] ?? '');
    return in_array($chosen, $aiKeys, true) ? $chosen : ($aiKeys[0] ?? '');
}

/**
 * Diagnostic snapshot of every integration with secrets masked — safe to show
 * in an admin screen or log. Never contains raw credentials.
 */
function integrations_status(): array
{
    $registry = integrations();
    $out      = [];

    foreach ($registry['items'] as $key => $item) {
        $fields = [];
        foreach ($item['fields'] as $field => $def) {
            $raw           = (string) integration($key, $field);
            $isSecret      = ! empty($def['secret']);
            $fields[$field] = [
                'set'         => $raw !== '',
                'from_config' => integration_is_constant($key, $field),
                'value'       => ($raw === '' || $isSecret) ? '' : $raw,
                'masked'      => $raw !== '' ? ($isSecret ? mask_secret($raw) : $raw) : '',
            ];
        }
        $out[$key] = [
            'label'   => $item['label'],
            'group'   => $item['group'] ?? 'apps',
            'enabled' => integration_enabled($key),
            'fields'  => $fields,
        ];
    }

    return $out;
}

/**
 * Mask a secret, leaving only the last few characters (e.g. "••••••3a9f").
 */
function mask_secret(string $value, int $visible = 4): string
{
    $len = strlen($value);
    if ($len <= $visible) {
        return str_repeat('•', max(1, $len));
    }
    return str_repeat('•', min(8, $len - $visible)) . substr($value, -$visible);
}

/**
 * 32-byte encryption key derived from this site's secret WordPress salt.
 * Because the key lives in wp-config.php (not the database), an attacker with
 * only a database dump cannot decrypt stored secrets.
 */
function secret_key(): string
{
    return hash('sha256', wp_salt('secure_auth') . '|rv-integrations', true);
}

/**
 * Encrypt a secret for storage (authenticated encryption via libsodium).
 * Falls back to a clearly-flagged base64 blob only if libsodium is unavailable.
 */
function secret_encrypt(string $plain): string
{
    if ($plain === '') {
        return '';
    }
    if (! function_exists('sodium_crypto_secretbox')) {
        return 'b64:' . base64_encode($plain);
    }
    $nonce  = random_bytes(SODIUM_CRYPTO_SECRETBOX_NONCEBYTES);
    $cipher = sodium_crypto_secretbox($plain, $nonce, secret_key());

    return 'v1:' . base64_encode($nonce . $cipher);
}

/**
 * Decrypt a stored secret. Unknown/legacy formats are returned as-is for
 * backward compatibility. A decryption failure (e.g. salts changed) yields ''.
 */
function secret_decrypt(string $stored): string
{
    if ($stored === '') {
        return '';
    }
    if (strpos($stored, 'b64:') === 0) {
        return (string) base64_decode(substr($stored, 4));
    }
    if (strpos($stored, 'v1:') === 0) {
        if (! function_exists('sodium_crypto_secretbox_open')) {
            return '';
        }
        $raw = base64_decode(substr($stored, 3), true);
        if ($raw === false || strlen($raw) <= SODIUM_CRYPTO_SECRETBOX_NONCEBYTES) {
            return '';
        }
        $nonce  = substr($raw, 0, SODIUM_CRYPTO_SECRETBOX_NONCEBYTES);
        $cipher = substr($raw, SODIUM_CRYPTO_SECRETBOX_NONCEBYTES);
        $plain  = sodium_crypto_secretbox_open($cipher, $nonce, secret_key());

        return $plain === false ? '' : $plain;
    }

    return $stored; // legacy plaintext option
}

/**
 * Sanitize callback name for a given field type.
 */
function integration_sanitizer(string $type): string
{
    switch ($type) {
        case 'url':
            return 'esc_url_raw';
        case 'email':
            return 'sanitize_email';
        case 'textarea':
            return 'sanitize_textarea_field';
        case 'checkbox':
            return 'rest_sanitize_boolean';
        default: // text, password
            return 'sanitize_text_field';
    }
}

/**
 * Register the Integrations panel, its sections, and their controls.
 */
add_action('customize_register', function ($wp_customize) {
    $registry = integrations();
    $groups   = $registry['groups'];
    $items    = $registry['items'];

    $apisUrl = admin_url('themes.php?page=rv-theme-apis');

    $panelDesc = sprintf(
        /* translators: %s: URL of the Theme APIs admin page. */
        __('Enable the apps you use and set non-secret options here. 🔑 API keys and tokens are kept out of this screen for safety — they are hidden and stored encrypted on the <a href="%s">Theme APIs</a> page.', 'sage'),
        esc_url($apisUrl)
    );
    if (integrations_locked()) {
        $panelDesc .= ' ' . __('🔒 Keys are currently locked to wp-config.php and cannot be changed from the admin.', 'sage');
    }
    if (integrations_disabled()) {
        $panelDesc .= ' ' . __('⛔ All integrations are currently switched off by a site constant.', 'sage');
    }

    $wp_customize->add_panel('rv_integrations', [
        'title'       => __('Integrations & Connections', 'sage'),
        'description' => $panelDesc,
        'priority'    => 30,
    ]);

    /* AI default-provider picker (its own small section, shown first). */
    $aiKeys = array_keys(array_filter($items, fn ($i) => ($i['group'] ?? '') === 'ai'));
    if (! empty($aiKeys)) {
        $wp_customize->add_section('rv_int_ai_settings', [
            'title'       => __('AI — Preferences', 'sage'),
            'description' => $groups['ai']['description'] ?? '',
            'panel'       => 'rv_integrations',
            'priority'    => 5,
        ]);

        $choices = [];
        foreach ($aiKeys as $k) {
            $choices[$k] = $items[$k]['label'];
        }

        $wp_customize->add_setting('rv_integration_ai_default', [
            'type'              => 'option',
            'default'           => $aiKeys[0],
            'capability'        => 'manage_options',
            'sanitize_callback' => function ($value) use ($choices) {
                return array_key_exists($value, $choices) ? $value : '';
            },
        ]);
        $wp_customize->add_control('rv_integration_ai_default', [
            'label'       => __('Preferred AI provider', 'sage'),
            'description' => __('Used by default when an AI feature needs a provider.', 'sage'),
            'section'     => 'rv_int_ai_settings',
            'type'        => 'select',
            'choices'     => $choices,
        ]);
    }

    /* One section per integration, ordered by group. */
    $groupBase = ['ai' => 10, 'apps' => 100, 'automation' => 200];
    $cursor    = [];

    foreach ($items as $key => $item) {
        $group = $item['group'] ?? 'apps';
        if (! isset($cursor[$group])) {
            $cursor[$group] = $groupBase[$group] ?? 300;
        }

        $sectionId  = 'rv_int_' . $key;
        $groupLabel = $groups[$group]['label'] ?? ucfirst($group);

        $sectionDesc = $item['help'] ?? '';
        $hasSecret   = (bool) array_filter($item['fields'], fn ($d) => ! empty($d['secret']));
        if ($hasSecret) {
            $sectionDesc = trim($sectionDesc . ' ' . sprintf(
                /* translators: %s: URL of the Theme APIs admin page. */
                __('🔑 This service’s key is hidden and stored securely on the <a href="%s">Theme APIs</a> page.', 'sage'),
                esc_url($apisUrl)
            ));
        }

        $wp_customize->add_section($sectionId, [
            /* translators: 1: integration name, 2: group name. */
            'title'       => sprintf(__('%1$s (%2$s)', 'sage'), $item['label'], $groupLabel),
            'description' => $sectionDesc,
            'panel'       => 'rv_integrations',
            'priority'    => $cursor[$group],
        ]);
        $cursor[$group]++;

        /* Enable toggle. */
        $enabledId = integration_option_key($key, 'enabled');
        $wp_customize->add_setting($enabledId, [
            'type'              => 'option',
            'default'           => false,
            'capability'        => 'manage_options',
            'sanitize_callback' => 'rest_sanitize_boolean',
        ]);
        $wp_customize->add_control($enabledId, [
            /* translators: %s: integration name. */
            'label'   => sprintf(__('Enable %s', 'sage'), $item['label']),
            'section' => $sectionId,
            'type'    => 'checkbox',
        ]);

        /* Non-secret config fields only. Secrets live on the Theme APIs page. */
        foreach ($item['fields'] as $field => $def) {
            if (! empty($def['secret'])) {
                continue;
            }
            $optionId    = integration_option_key($key, $field);
            $type        = $def['type'] ?? 'text';
            $description = $def['help'] ?? '';

            if (integration_is_constant($key, $field)) {
                $note = __('Currently set by a constant in wp-config.php — this field is ignored.', 'sage');
                $description = $description ? trim($description) . ' ' . $note : $note;
            }

            $wp_customize->add_setting($optionId, [
                'type'              => 'option',
                'default'           => $def['default'] ?? '',
                'capability'        => 'manage_options',
                'sanitize_callback' => integration_sanitizer($type),
            ]);

            $inputAttrs = [];
            if (! empty($def['placeholder'])) {
                $inputAttrs['placeholder'] = $def['placeholder'];
            }
            if (! empty($def['secret'])) {
                $inputAttrs['autocomplete'] = 'new-password';
                $inputAttrs['spellcheck']   = 'false';
            }

            $wp_customize->add_control($optionId, [
                'label'       => $def['label'],
                'description' => $description,
                'section'     => $sectionId,
                'type'        => ($type === 'password') ? 'password' : $type,
                'input_attrs' => $inputAttrs,
            ]);
        }
    }
});

/* ---------------------------------------------------------------------------
 * Theme APIs — a dedicated, hardened admin page just for secrets.
 *
 * Why a separate page (not the Customizer):
 *   - Secrets are write-only: once saved they are never rendered back in full,
 *     only shown masked (e.g. "••••••3a9f").
 *   - Values are encrypted at rest (see secret_encrypt) so a database dump does
 *     not expose them.
 *   - wp-config.php constants take precedence and lock the matching field.
 *   - A site can be locked down entirely with RV_INTEGRATIONS_LOCK (read-only)
 *     or RV_INTEGRATIONS_DISABLED (master kill-switch).
 * ------------------------------------------------------------------------- */

add_action('admin_menu', function () {
    add_theme_page(
        __('Theme APIs', 'sage'),
        __('Theme APIs', 'sage'),
        api_keys_capability(),
        'rv-theme-apis',
        __NAMESPACE__ . '\\render_theme_apis_page'
    );
});

/**
 * Save handler + renderer for the Theme APIs page.
 */
function render_theme_apis_page(): void
{
    if (! current_user_can(api_keys_capability())) {
        wp_die(esc_html__('You do not have permission to manage API keys.', 'sage'));
    }

    $registry = integrations();
    $items    = $registry['items'];
    $groups   = $registry['groups'];
    $locked   = integrations_locked();
    $disabled = integrations_disabled();
    $notice   = null;

    /* ---- Save ---- */
    if ('POST' === ($_SERVER['REQUEST_METHOD'] ?? '') && isset($_POST['rv_api_keys_nonce'])) {
        check_admin_referer('rv_save_api_keys', 'rv_api_keys_nonce');

        if ($locked) {
            $notice = ['notice-error', __('Keys are locked to wp-config.php — no changes were saved.', 'sage')];
        } else {
            $saved = 0;
            foreach ($items as $key => $item) {
                foreach ($item['fields'] as $field => $def) {
                    if (empty($def['secret']) || integration_is_constant($key, $field)) {
                        continue;
                    }
                    $optionId = integration_option_key($key, $field);

                    if (! empty($_POST['rv_remove'][$key][$field])) {
                        delete_option($optionId);
                        $saved++;
                        continue;
                    }

                    $raw = isset($_POST['rv_secret'][$key][$field])
                        ? trim((string) wp_unslash($_POST['rv_secret'][$key][$field]))
                        : '';
                    if ($raw === '') {
                        continue; // blank = keep whatever is stored
                    }
                    $clean = (($def['type'] ?? '') === 'url')
                        ? esc_url_raw($raw)
                        : sanitize_text_field($raw);
                    if ($clean !== '') {
                        update_option($optionId, secret_encrypt($clean), false);
                        $saved++;
                    }
                }
            }
            $notice = ['notice-success', $saved
                ? __('API settings saved and encrypted.', 'sage')
                : __('No changes to save.', 'sage')];
        }
    }

    /* ---- Render ---- */
    echo '<div class="wrap rv-apis">';
    echo '<h1>' . esc_html__('Theme APIs', 'sage') . '</h1>';
    echo '<p style="max-width:70ch">' . esc_html__('API keys, tokens, and webhook URLs live here — separate from the Customizer. Keys are hidden after saving (shown masked only), encrypted at rest, and never exposed on the front end. The most secure option is to define them in wp-config.php using the snippet under each field; a value defined there overrides and locks the field.', 'sage') . '</p>';

    if ($notice) {
        printf('<div class="notice %1$s is-dismissible"><p>%2$s</p></div>', esc_attr($notice[0]), esc_html($notice[1]));
    }

    if ($disabled) {
        echo '<div class="notice notice-error"><p><strong>' . esc_html__('⛔ All integrations are switched off.', 'sage') . '</strong> ' . esc_html__('RV_INTEGRATIONS_DISABLED is set in wp-config.php. No integration will run until it is removed.', 'sage') . '</p></div>';
    }
    if ($locked) {
        echo '<div class="notice notice-warning"><p><strong>' . esc_html__('🔒 Locked down.', 'sage') . '</strong> ' . esc_html__('RV_INTEGRATIONS_LOCK is set in wp-config.php. Keys can only be managed there; the fields below are read-only.', 'sage') . '</p></div>';
    }

    echo '<style>
        .rv-apis .rv-int{background:#fff;border:1px solid #dcdcde;border-radius:8px;padding:1rem 1.25rem;margin:0 0 1rem;max-width:820px}
        .rv-apis .rv-int h3{margin:.2rem 0 .1rem}
        .rv-apis .rv-help{color:#646970;margin:.1rem 0 .75rem}
        .rv-apis .rv-field{padding:.6rem 0;border-top:1px solid #f0f0f1}
        .rv-apis .rv-field label.rv-lbl{font-weight:600;display:block;margin-bottom:.25rem}
        .rv-apis .rv-badge{display:inline-block;font-size:11px;line-height:1.6;padding:0 .5rem;border-radius:999px;margin-left:.35rem;vertical-align:middle}
        .rv-apis .rv-badge.set{background:#e6f4ea;color:#0a6b2e}
        .rv-apis .rv-badge.cfg{background:#e7edfb;color:#123a9e}
        .rv-apis .rv-badge.off{background:#f0f0f1;color:#646970}
        .rv-apis input[type=password].rv-secret{width:100%;max-width:520px}
        .rv-apis code.rv-snip{display:inline-block;background:#f6f7f7;border:1px solid #e0e0e0;border-radius:4px;padding:.15rem .4rem;margin-top:.35rem;font-size:12px;color:#1d2327;word-break:break-all}
        .rv-apis .rv-mask{font-family:monospace;color:#1d2327}
    </style>';

    echo '<form method="post" action="">';
    wp_nonce_field('rv_save_api_keys', 'rv_api_keys_nonce');

    $groupBase = ['ai' => 0, 'apps' => 1, 'automation' => 2];
    uksort($items, function ($a, $b) use ($items, $groupBase) {
        $ga = $groupBase[$items[$a]['group'] ?? 'apps'] ?? 9;
        $gb = $groupBase[$items[$b]['group'] ?? 'apps'] ?? 9;
        return $ga <=> $gb;
    });

    $currentGroup = null;
    foreach ($items as $key => $item) {
        $secretFields = array_filter($item['fields'], fn ($d) => ! empty($d['secret']));
        if (empty($secretFields)) {
            continue;
        }

        $group = $item['group'] ?? 'apps';
        if ($group !== $currentGroup) {
            $currentGroup = $group;
            echo '<h2>' . esc_html($groups[$group]['label'] ?? ucfirst($group)) . '</h2>';
        }

        echo '<div class="rv-int">';
        echo '<h3>' . esc_html($item['label']) . '</h3>';
        if (! empty($item['help'])) {
            echo '<p class="rv-help">' . esc_html($item['help']) . '</p>';
        }

        foreach ($secretFields as $field => $def) {
            $constName = integration_constant_name($key, $field);
            $isConst   = integration_is_constant($key, $field);
            $stored    = (string) get_option(integration_option_key($key, $field), '');
            $hasValue  = $isConst || $stored !== '';
            $current   = $hasValue ? (string) integration($key, $field) : '';
            $inputName = 'rv_secret[' . esc_attr($key) . '][' . esc_attr($field) . ']';
            $removeNm  = 'rv_remove[' . esc_attr($key) . '][' . esc_attr($field) . ']';

            echo '<div class="rv-field">';
            echo '<label class="rv-lbl">' . esc_html($def['label']);
            if ($isConst) {
                echo '<span class="rv-badge cfg">' . esc_html__('set in wp-config.php', 'sage') . '</span>';
            } elseif ($stored !== '') {
                echo '<span class="rv-badge set">' . esc_html__('stored · encrypted', 'sage') . '</span>';
            } else {
                echo '<span class="rv-badge off">' . esc_html__('not set', 'sage') . '</span>';
            }
            echo '</label>';

            if ($hasValue) {
                echo '<div class="rv-mask">' . esc_html(mask_secret($current)) . '</div>';
            }

            if ($isConst || $locked) {
                $why = $isConst
                    ? __('Managed in wp-config.php.', 'sage')
                    : __('Locked — edit in wp-config.php.', 'sage');
                echo '<p class="rv-help">' . esc_html($why) . '</p>';
            } else {
                $ph = $hasValue
                    ? __('Leave blank to keep the current value…', 'sage')
                    : ($def['placeholder'] ?? __('Paste key…', 'sage'));
                printf(
                    '<input type="password" class="rv-secret" name="%1$s" value="" autocomplete="new-password" spellcheck="false" placeholder="%2$s" />',
                    esc_attr($inputName),
                    esc_attr($ph)
                );
                if ($stored !== '') {
                    echo '<p><label><input type="checkbox" name="' . esc_attr($removeNm) . '" value="1" /> ' . esc_html__('Remove this key', 'sage') . '</label></p>';
                }
            }

            /* Recommended wp-config.php line (most secure). */
            echo '<div><code class="rv-snip">' . esc_html("define('" . $constName . "', '…');") . '</code></div>';

            echo '</div>'; // .rv-field
        }

        echo '</div>'; // .rv-int
    }

    if (! $locked) {
        submit_button(__('Save API keys', 'sage'));
    }
    echo '</form>';

    /* ---- Lockdown guidance ---- */
    echo '<hr />';
    echo '<h2>' . esc_html__('Lock things down', 'sage') . '</h2>';
    echo '<p style="max-width:70ch">' . esc_html__('For a production site, add either of these to wp-config.php. Because they are constants, they cannot be switched off from the WordPress admin — even by someone who gains a login.', 'sage') . '</p>';
    echo '<p><code class="rv-snip">' . esc_html("define('RV_INTEGRATIONS_LOCK', true);") . '</code> — ' . esc_html__('read-only: keys can only be set in wp-config.php.', 'sage') . '</p>';
    echo '<p><code class="rv-snip">' . esc_html("define('RV_INTEGRATIONS_DISABLED', true);") . '</code> — ' . esc_html__('master kill-switch: turns every integration off at once.', 'sage') . '</p>';

    echo '</div>'; // .wrap
}
