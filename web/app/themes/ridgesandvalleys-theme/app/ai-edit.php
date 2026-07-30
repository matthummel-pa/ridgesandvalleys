<?php

/**
 * "Edit with AI" for the theme's custom fields.
 *
 * Adds a small AI helper to every Page-content and Case-study field in the
 * editor. It uses the key you store on the Theme APIs page (Appearance → Theme
 * APIs) via \App\integration(), and calls the chosen provider's API directly —
 * Anthropic, OpenAI, or Google Gemini — through a scoped REST endpoint that only
 * signed-in editors can reach. No dependency on any AI plugin. When no key is
 * set the controls step aside and point the user at the Theme APIs page.
 */

namespace App;

/* ------------------------------------------------------- provider selection */

/**
 * The AI provider the editor should use: the site's chosen default first, then
 * any AI provider that has a key. Returns ['provider','key','model','org'] or null.
 */
function ai_edit_provider()
{
    if (function_exists(__NAMESPACE__ . '\\integrations_disabled') && integrations_disabled()) {
        return null;
    }
    if (! function_exists(__NAMESPACE__ . '\\integration')) {
        return null; // Theme APIs layer not loaded.
    }

    $defaults = [
        'anthropic'     => 'claude-sonnet-4-5',
        'openai'        => 'gpt-4o',
        'google_gemini' => 'gemini-1.5-pro',
    ];

    $order = [];
    if (function_exists(__NAMESPACE__ . '\\ai_default_provider')) {
        $preferred = ai_default_provider();
        if ($preferred) {
            $order[] = $preferred;
        }
    }
    foreach (array_keys($defaults) as $p) {
        if (! in_array($p, $order, true)) {
            $order[] = $p;
        }
    }

    foreach ($order as $p) {
        if (! isset($defaults[$p])) {
            continue;
        }
        $key = (string) integration($p, 'api_key');
        if ($key === '') {
            continue;
        }
        $model = (string) integration($p, 'model', $defaults[$p]);
        return [
            'provider' => $p,
            'key'      => $key,
            'model'    => $model !== '' ? $model : $defaults[$p],
            'org'      => $p === 'openai' ? (string) integration('openai', 'org_id') : '',
        ];
    }

    return null;
}

/** True when a usable AI key is configured on the Theme APIs page. */
function ai_text_ready(): bool
{
    return ai_edit_provider() !== null;
}

/** The quick-action instructions offered in the editor popover. */
function ai_edit_actions(): array
{
    return [
        'improve' => __('Rewrite it to be clearer, warmer, and more compelling while keeping the same meaning and roughly the same length.', 'sage'),
        'shorten' => __('Make it noticeably shorter and punchier without losing the key point.', 'sage'),
        'expand'  => __('Add one or two concrete, useful details while staying on message. Keep it tight.', 'sage'),
        'fix'     => __('Fix spelling, grammar, and punctuation and smooth any awkward phrasing. Keep the wording and length as close to the original as possible.', 'sage'),
        'rewrite' => __('Rewrite it in a fresh way while keeping the same meaning and a similar length.', 'sage'),
    ];
}

/* ------------------------------------------------------------ provider calls */

/**
 * Generate text with the configured provider. Returns the string, or a WP_Error.
 */
function ai_generate(string $system, string $prompt)
{
    $cfg = ai_edit_provider();
    if (! $cfg) {
        return new \WP_Error(
            'rv_ai_no_key',
            __('No AI provider key is set. Add one under Appearance → Theme APIs.', 'sage'),
            ['status' => 503]
        );
    }

    switch ($cfg['provider']) {
        case 'openai':
            return ai_call_openai($cfg, $system, $prompt);
        case 'google_gemini':
            return ai_call_gemini($cfg, $system, $prompt);
        case 'anthropic':
        default:
            return ai_call_anthropic($cfg, $system, $prompt);
    }
}

function ai_call_anthropic(array $cfg, string $system, string $prompt)
{
    $res = wp_remote_post('https://api.anthropic.com/v1/messages', [
        'timeout' => 45,
        'headers' => [
            'x-api-key'         => $cfg['key'],
            'anthropic-version' => '2023-06-01',
            'content-type'      => 'application/json',
        ],
        'body' => wp_json_encode([
            'model'      => $cfg['model'],
            'max_tokens' => 600,
            'system'     => $system,
            'messages'   => [['role' => 'user', 'content' => $prompt]],
        ]),
    ]);
    return ai_parse_response($res, function ($data) {
        return $data['content'][0]['text'] ?? null;
    });
}

function ai_call_openai(array $cfg, string $system, string $prompt)
{
    $headers = [
        'Authorization' => 'Bearer ' . $cfg['key'],
        'content-type'  => 'application/json',
    ];
    if (! empty($cfg['org'])) {
        $headers['OpenAI-Organization'] = $cfg['org'];
    }
    $res = wp_remote_post('https://api.openai.com/v1/chat/completions', [
        'timeout' => 45,
        'headers' => $headers,
        'body' => wp_json_encode([
            'model'      => $cfg['model'],
            'max_tokens' => 600,
            'messages'   => [
                ['role' => 'system', 'content' => $system],
                ['role' => 'user', 'content' => $prompt],
            ],
        ]),
    ]);
    return ai_parse_response($res, function ($data) {
        return $data['choices'][0]['message']['content'] ?? null;
    });
}

function ai_call_gemini(array $cfg, string $system, string $prompt)
{
    $url = 'https://generativelanguage.googleapis.com/v1beta/models/' . rawurlencode($cfg['model'])
        . ':generateContent?key=' . rawurlencode($cfg['key']);
    $res = wp_remote_post($url, [
        'timeout' => 45,
        'headers' => ['content-type' => 'application/json'],
        'body' => wp_json_encode([
            'systemInstruction' => ['parts' => [['text' => $system]]],
            'contents'          => [['role' => 'user', 'parts' => [['text' => $prompt]]]],
            'generationConfig'  => ['maxOutputTokens' => 600],
        ]),
    ]);
    return ai_parse_response($res, function ($data) {
        return $data['candidates'][0]['content']['parts'][0]['text'] ?? null;
    });
}

/** Shared response handling: HTTP errors, provider errors, empty results. */
function ai_parse_response($res, callable $extract)
{
    if (is_wp_error($res)) {
        return new \WP_Error('rv_ai_http', $res->get_error_message(), ['status' => 502]);
    }
    $code = (int) wp_remote_retrieve_response_code($res);
    $data = json_decode(wp_remote_retrieve_body($res), true);

    if ($code < 200 || $code >= 300) {
        $msg = '';
        if (is_array($data)) {
            $msg = $data['error']['message'] ?? (is_string($data['error'] ?? null) ? $data['error'] : '');
        }
        if (! is_string($msg) || $msg === '') {
            /* translators: %d: HTTP status code. */
            $msg = sprintf(__('The AI provider returned status %d.', 'sage'), $code);
        }
        return new \WP_Error('rv_ai_provider', $msg, ['status' => 502]);
    }

    $text = is_array($data) ? $extract($data) : null;
    if (! is_string($text) || trim($text) === '') {
        return new \WP_Error('rv_ai_empty', __('The AI returned an empty response.', 'sage'), ['status' => 502]);
    }
    return trim($text);
}

/* --------------------------------------------------------------- REST route */

add_action('rest_api_init', function () {
    register_rest_route('rv/v1', '/ai-edit', [
        'methods'             => 'POST',
        'permission_callback' => function () {
            return current_user_can('edit_posts');
        },
        'args' => [
            'text'        => ['type' => 'string', 'required' => true],
            'action'      => ['type' => 'string', 'required' => false],
            'instruction' => ['type' => 'string', 'required' => false],
            'label'       => ['type' => 'string', 'required' => false],
        ],
        'callback' => __NAMESPACE__ . '\\ai_edit_rest',
    ]);
});

function ai_edit_rest(\WP_REST_Request $request)
{
    if (! ai_text_ready()) {
        return new \WP_Error(
            'rv_ai_unavailable',
            __('No AI provider key is set. Add one under Appearance → Theme APIs.', 'sage'),
            ['status' => 503]
        );
    }

    $text        = trim((string) $request->get_param('text'));
    $action      = sanitize_key((string) $request->get_param('action'));
    $instruction = trim((string) $request->get_param('instruction'));
    $label       = sanitize_text_field((string) $request->get_param('label'));

    if ($text === '' && $instruction === '') {
        return new \WP_Error('rv_ai_empty_input', __('There is nothing to edit yet.', 'sage'), ['status' => 400]);
    }

    $actions = ai_edit_actions();
    $task    = $instruction !== '' ? $instruction : ($actions[$action] ?? $actions['improve']);

    $system = sprintf(
        /* translators: %s: parenthetical field label, e.g. ' (the "Hero title" field)'. */
        __('You are editing microcopy for the website of Ridges & Valleys Studio, a small independent web-design studio in Gettysburg and Adams County, Pennsylvania, run by Matt Hummel. Voice: warm, plain-English, confident, and local — no corporate jargon, no hype, no exclamation-point spam. You are revising a single website field%s. Return ONLY the revised text for that field: no quotation marks, no preamble, no explanation, no markdown, and no list of options. Match the original\'s format and length — if it is a short headline, keep it short; if it has no ending period, do not add one.', 'sage'),
        $label !== '' ? sprintf(__(' (the "%s" field)', 'sage'), $label) : ''
    );

    $prompt = __('Current text:', 'sage') . "\n\n" . $text . "\n\n" . __('Task:', 'sage') . ' ' . $task;

    $result = ai_generate($system, $prompt);
    if (is_wp_error($result)) {
        return $result;
    }

    // Some models wrap the answer in quotes even when asked not to — strip them.
    $out = preg_replace('/^\s*["“](.+)["”]\s*$/su', '$1', trim((string) $result));

    return rest_ensure_response(['text' => $out, 'provider' => ai_edit_provider()['provider'] ?? '']);
}

/* --------------------------------------------------------------- editor JS */

add_action('admin_enqueue_scripts', function ($hook) {
    if (! in_array($hook, ['post.php', 'post-new.php'], true)) {
        return;
    }
    $screen = get_current_screen();
    if (! $screen || ! in_array($screen->post_type, ['page', 'project'], true)) {
        return;
    }

    wp_enqueue_script(
        'rv-admin-ai',
        get_theme_file_uri('resources/js/admin-ai.js'),
        [],
        '1.2.0',
        true
    );

    $cfg           = ai_edit_provider();
    $providerNames = [
        'anthropic'     => __('Claude', 'sage'),
        'openai'        => __('OpenAI', 'sage'),
        'google_gemini' => __('Gemini', 'sage'),
    ];
    $providerLabel = $cfg ? ($providerNames[$cfg['provider']] ?? ucfirst($cfg['provider'])) : '';

    wp_localize_script('rv-admin-ai', 'rvAI', [
        'endpoint'      => esc_url_raw(rest_url('rv/v1/ai-edit')),
        'nonce'         => wp_create_nonce('wp_rest'),
        'ready'         => (bool) $cfg,
        'provider'      => $cfg ? $cfg['provider'] : '',
        'providerLabel' => $providerLabel,
        'postType'      => $screen->post_type,
        'settings'      => esc_url_raw(admin_url('themes.php?page=rv-theme-apis')),
        'i18n'     => [
            'button'    => __('Edit with AI', 'sage'),
            'connected'    => __('AI connected', 'sage'),
            'disconnected' => __('AI disconnected', 'sage'),
            'improve'   => __('Improve', 'sage'),
            'shorten'   => __('Shorten', 'sage'),
            'expand'    => __('Expand', 'sage'),
            'fix'       => __('Fix grammar', 'sage'),
            'rewrite'   => __('Rewrite', 'sage'),
            'custom'    => __('Custom instruction…', 'sage'),
            'apply'     => __('Apply', 'sage'),
            'thinking'  => __('Thinking…', 'sage'),
            'undo'      => __('Undo', 'sage'),
            'edited'    => __('AI edited — click Update to save.', 'sage'),
            'notready'  => __('Add an AI key under Appearance → Theme APIs to enable Edit with AI on these fields.', 'sage'),
            'settingsLabel' => __('Appearance → Theme APIs', 'sage'),
            'error'     => __('AI request failed.', 'sage'),
        ],
    ]);
});
