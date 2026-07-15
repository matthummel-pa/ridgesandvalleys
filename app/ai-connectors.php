<?php

/**
 * AI Connectors — provider registry + key storage for Pressroot AI.
 *
 * Lets the theme owner optionally connect additional free AI text-generation
 * providers beyond the built-in Pollinations endpoint (used by the Hero Image
 * Finder and, until now, the only option for Pressroot AI's starter hero-copy
 * generator). Pollinations stays the always-on, no-signup default — nothing
 * here is required for Pressroot AI to keep working exactly as it did before.
 *
 * This USED to be its own "AI Connectors" admin page; it's now folded into
 * Appearance -> Pressroot AI as a collapsed "Advanced" section (see
 * prt_ai_connectors_fields_html() below and its embedding in
 * app/ai-assistant.php) so the whole addon lives on one screen. This file no
 * longer registers an admin_menu page of its own.
 *
 * Providers included are the best currently-available FREE (no credit card)
 * AI text APIs as of mid-2026:
 *   - Google Gemini  — generous, indefinite free tier via Google AI Studio.
 *   - Groq           — very fast free-tier inference (Llama/Qwen/DeepSeek).
 *   - OpenRouter     — one key, an aggregator with several always-free models.
 * Each needs a free API key from the provider (linked below) — this theme
 * never talks to a paid API or requires a credit card anywhere.
 *
 * SECURITY: API keys are stored as theme_mods (same pattern the existing
 * GitHub token uses in app/github-settings.php — see the NOTE(audit) there
 * about theme_mods not being the most secure secret store available). Keys
 * are NEVER sent to the browser: Pressroot AI's "Generate" button calls a
 * server-side AJAX endpoint (prt_ai_generate_copy, below) that reads the
 * stored key and calls the provider from PHP — the key never appears in any
 * page source, request the browser makes, or client-side JS.
 */

namespace App;

/**
 * The connector registry. 'pollinations' is always available and needs no
 * key/settings row (kept out of the settings table below); the other three
 * are optional and only show up as a model choice once a key is saved.
 *
 * @return array<string, array{label:string, needs_key:bool, model_default:string, docs_url:string, note:string}>
 */
function prt_ai_connectors_defs(): array
{
    return apply_filters('pressroot/ai_connectors', [
        'pollinations' => [
            'label'         => __('Pollinations (default)', 'pressroot'),
            'needs_key'     => false,
            'model_default' => '',
            'docs_url'      => 'https://pollinations.ai/',
            'note'          => __('Free, no signup, no API key — already used by the Hero Image Finder. Always available.', 'pressroot'),
        ],
        'gemini' => [
            'label'         => __('Google Gemini', 'pressroot'),
            'needs_key'     => true,
            'model_default' => 'gemini-2.0-flash',
            'models'        => ['gemini-2.0-flash', 'gemini-2.5-flash', 'gemini-2.5-pro'],
            'docs_url'      => 'https://ai.google.dev/gemini-api/docs',
            'note'          => __('Free tier via Google AI Studio — no credit card required. Get a key at aistudio.google.com/apikey.', 'pressroot'),
        ],
        'groq' => [
            'label'         => __('Groq', 'pressroot'),
            'needs_key'     => true,
            'model_default' => 'llama-3.3-70b-versatile',
            'models'        => ['llama-3.3-70b-versatile', 'llama-3.1-8b-instant', 'deepseek-r1-distill-llama-70b'],
            'docs_url'      => 'https://console.groq.com/keys',
            'note'          => __('Free tier, very fast inference (Llama/Qwen/DeepSeek). Get a key at console.groq.com/keys.', 'pressroot'),
        ],
        'anthropic' => [
            'label'         => __('Anthropic Claude', 'pressroot'),
            'needs_key'     => true,
            'model_default' => 'claude-sonnet-4-5',
            'models'        => ['claude-opus-4-8', 'claude-sonnet-4-5', 'claude-haiku-4-5-20251001'],
            'docs_url'      => 'https://platform.claude.com/',
            'note'          => __('The website-generator pick: strongest at structured copy + long instructions, so AI-write results need the least editing. Paid API (no free tier) — optional; the keyless default stays free.', 'pressroot'),
        ],
        'openai' => [
            'label'         => __('OpenAI (ChatGPT)', 'pressroot'),
            'needs_key'     => true,
            'model_default' => 'gpt-4o-mini',
            'models'        => ['gpt-4o-mini', 'gpt-4o', 'gpt-4.1-mini', 'gpt-4.1'],
            'docs_url'      => 'https://platform.openai.com/api-keys',
            'note'          => __('gpt-4o-mini is fast and inexpensive for site copy. Paid API — optional.', 'pressroot'),
        ],
        'openrouter' => [
            'label'         => __('OpenRouter', 'pressroot'),
            'needs_key'     => true,
            'model_default' => 'openrouter/free',
            'models'        => ['openrouter/free', 'meta-llama/llama-3.3-70b-instruct:free', 'google/gemini-2.0-flash-exp:free', 'deepseek/deepseek-chat:free'],
            'docs_url'      => 'https://openrouter.ai/keys',
            'note'          => __('One key, several always-free models (auto-routed by default). Get a key at openrouter.ai/keys.', 'pressroot'),
        ],
    ]);
}

function prt_ai_get_key(string $slug): string
{
    return get_theme_mod('prt_ai_key_' . $slug, '');
}

function prt_ai_get_model(string $slug): string
{
    $defs = function_exists('App\\prt_ai_all_connector_defs') ? prt_ai_all_connector_defs() : prt_ai_connectors_defs();
    $default = $defs[$slug]['model_default'] ?? '';
    $saved   = get_theme_mod('prt_ai_model_' . $slug, $default) ?: $default;
    // If the provider publishes a model list, only honor saved values on it.
    if (! empty($defs[$slug]['models']) && ! in_array($saved, $defs[$slug]['models'], true)) {
        return $default;
    }
    return $saved;
}

/** A model <select> for one provider (used by both the writing table and the AI Models tab). */
function prt_ai_model_select_html(string $slug, array $def): void
{
    if (empty($def['models'])) {
        return;
    }
    $current = prt_ai_get_model($slug);
    echo '<label style="display:block;margin-top:8px;font-size:12px;color:#646970">' . esc_html__('Model', 'pressroot') . ' ';
    echo '<select name="prt_ai_model_' . esc_attr($slug) . '">';
    foreach ($def['models'] as $m) {
        echo '<option value="' . esc_attr($m) . '" ' . selected($current, $m, false) . '>' . esc_html($m) . '</option>';
    }
    echo '</select></label>';
}

/** A connector is "configured" if it needs no key (Pollinations) or has one saved. */
function prt_ai_is_configured(string $slug): bool
{
    $defs = prt_ai_connectors_defs();
    if (! isset($defs[$slug])) {
        return false;
    }
    return empty($defs[$slug]['needs_key']) || prt_ai_get_key($slug) !== '';
}

/**
 * @return array<string, array> Every configured connector (Pollinations
 *                               always first), each entry merged with its
 *                               saved key/model, ready for the dropdown.
 */
function prt_ai_configured_connectors(): array
{
    $out = [];
    foreach (prt_ai_connectors_defs() as $slug => $def) {
        if (! prt_ai_is_configured($slug)) {
            continue;
        }
        $out[$slug] = array_merge($def, [
            'model' => prt_ai_get_model($slug),
        ]);
    }
    return $out;
}

/**
 * Renders just the connectors settings table + its own <form> — no page
 * wrapper, no <h1> — so it can be dropped into an <details> "Advanced"
 * section on the Pressroot AI screen (see app/ai-assistant.php). Kept as its
 * own function/file (rather than inlined there) because it's genuinely about
 * the connector registry, not page-creation/regeneration.
 */
function prt_ai_connectors_fields_html(): void
{
    ?>
    <p class="description">
        <?php esc_html_e('Pollinations is always on and needs nothing set up here. Connect any of these free providers to add them as extra model choices above. Every key stays server-side and is never sent to the browser.', 'pressroot'); ?>
    </p>
    <form method="post" action="<?php echo esc_url(admin_url('admin-post.php')); ?>">
        <input type="hidden" name="action" value="prt_save_ai_connectors">
        <input type="hidden" name="prt_return_tab" value="<?php echo esc_attr(sanitize_key(wp_unslash($_GET['tab'] ?? 'ai'))); ?>">
        <?php wp_nonce_field('prt_save_ai_connectors'); ?>
        <table class="form-table" role="presentation">
            <?php foreach (prt_ai_connectors_defs() as $slug => $def) :
                if (empty($def['needs_key'])) {
                    continue; // Pollinations: nothing to configure.
                }
                $configured = prt_ai_is_configured($slug);
            ?>
                <tr>
                    <th scope="row">
                        <?php echo esc_html($def['label']); ?>
                        <?php if ($configured) : ?>
                            <span style="display:inline-block;margin-left:6px;padding:2px 8px;border-radius:999px;background:#edfaef;color:#1e7a34;font-size:11px;font-weight:600">
                                <?php esc_html_e('Connected', 'pressroot'); ?>
                            </span>
                        <?php endif; ?>
                    </th>
                    <td>
                        <p class="description" style="margin:0 0 8px">
                            <?php echo esc_html($def['note']); ?>
                            <a href="<?php echo esc_url($def['docs_url']); ?>" target="_blank" rel="noopener noreferrer"><?php esc_html_e('Get a free key ↗', 'pressroot'); ?></a>
                        </p>
                        <label for="prt_ai_key_<?php echo esc_attr($slug); ?>" class="screen-reader-text"><?php esc_html_e('API key', 'pressroot'); ?></label>
                        <input
                            type="password"
                            class="regular-text"
                            autocomplete="off"
                            id="prt_ai_key_<?php echo esc_attr($slug); ?>"
                            name="prt_ai_key_<?php echo esc_attr($slug); ?>"
                            value=""
                            placeholder="<?php echo $configured ? esc_attr(str_repeat('•', 12)) : esc_attr__('Paste API key…', 'pressroot'); ?>"
                        >
                        <?php if ($configured) : ?>
                            <p class="description" style="margin:4px 0 0"><?php esc_html_e('Leave blank to keep the saved key.', 'pressroot'); ?></p>
                        <?php endif; ?>
                        <?php prt_ai_model_select_html($slug, $def); ?>
                    </td>
                </tr>
            <?php endforeach; ?>
        </table>
        <p class="submit"><button class="button button-primary"><?php esc_html_e('Save connectors', 'pressroot'); ?></button></p>
    </form>
    <?php
}

/** Persist the AI Connectors form as theme_mods (same convention as GitHub
 *  settings). Redirects back to the merged Pressroot AI screen, which shows
 *  the notice and re-opens the Advanced section (see app/ai-assistant.php). */
add_action('admin_post_prt_save_ai_connectors', function () {
    if (! current_user_can('manage_options') || ! prt_ai_features_enabled()) {
        wp_die(__('You do not have permission to do this.', 'pressroot'));
    }
    check_admin_referer('prt_save_ai_connectors');

    foreach (prt_ai_all_connector_defs() as $slug => $def) {
        $keyField   = 'prt_ai_key_' . $slug;
        $modelField = 'prt_ai_model_' . $slug;
        // Keys: only for providers that have one. Blank submissions keep
        // the stored key (so saving one section doesn't wipe another's),
        // matching the Repofolio secret pattern.
        if (! empty($def['needs_key'])) {
            $postedKey = isset($_POST[$keyField]) ? trim(sanitize_text_field(wp_unslash($_POST[$keyField]))) : '';
            if ($postedKey !== '') {
                set_theme_mod($keyField, $postedKey);
            }
        }
        // Models: saved for EVERY provider, keyless ones included — the
        // old needs_key skip silently dropped Pollinations' model choice.
        if (isset($_POST[$modelField])) {
            $postedModel = sanitize_text_field(wp_unslash($_POST[$modelField]));
            // Providers with a published list only accept listed models.
            if (empty($def['models']) || in_array($postedModel, $def['models'], true)) {
                set_theme_mod($modelField, $postedModel);
            }
        }
    }

    // Image-provider selection (AI Models tab).
    if (isset($_POST['prt_ai_image_model'])) {
        $img = sanitize_key($_POST['prt_ai_image_model']);
        if (isset(prt_ai_image_connectors_defs()[$img])) {
            set_theme_mod('prt_ai_image_model', $img);
        }
    }

    $ret = sanitize_key($_POST['prt_return_tab'] ?? '');
    if (isset($_POST['prt_models_group']) || isset($_POST['prt_ai_image_model']) || $ret === 'models') {
        $dest = prt_settings_tab_url('models', ['connectors_updated' => '1']);
    } else {
        $dest = prt_settings_tab_url('ai', ['connectors_updated' => '1']) . '#prt-ai-advanced';
    }
    wp_safe_redirect($dest);
    exit;
});

/**
 * Build the same fixed-shape prompt Pressroot AI's starter-copy
 * generator has always used (see the matching comment that used to live only
 * in resources/js/ai-assistant.js) — now shared here so every provider,
 * Pollinations included, gets identical instructions regardless of which
 * model generates the response.
 */
function prt_ai_build_hero_prompt(string $description, string $siteType = ''): string
{
    // Anchor the copy to the applied Site Type category so generated copy
    // matches the design that was just generated for it, and demand a fresh
    // angle each run so "Regenerate" produces genuinely new copy — pairing
    // with the Site Types tool that deals a random new design on every
    // refresh (see app/ai-assistant.php).
    $typeLine = '';
    if ($siteType !== '' && function_exists('App\\prt_site_types')) {
        $types = prt_site_types();
        if (isset($types[$siteType]['label'])) {
            $typeLine = 'This is a "' . $types[$siteType]['label'] . '" website — write in the voice that category expects. ';
        }
    }
    // Fold in the Brand tab's questionnaire answers (app/site-type-remix.php)
    // so generated copy is anchored to the actual business, not just the
    // one-line description typed into the generator.
    $brandLine = '';
    if (function_exists('App\\prt_brand_profile')) {
        $brand = prt_brand_profile();
        if ($brand['name'] !== '') {
            $brandLine .= 'The business is called "' . $brand['name'] . '". ';
        }
        $vibes = ['bold' => 'bold and confident', 'minimal' => 'minimal and sharp', 'warm' => 'warm and inviting', 'playful' => 'playful and bright'];
        if (isset($vibes[$brand['vibe']])) {
            $brandLine .= 'Brand personality: ' . $vibes[$brand['vibe']] . '. ';
        }
    }
    $core = function_exists('App\\prt_get_core_instructions') ? prt_get_core_instructions() : '';
    return ($core !== '' ? $core . "\n" : '')
        . 'Write website hero copy for this business: "' . $description . '". '
        . $typeLine
        . $brandLine
        . 'Voice: confident, concise, a little playful — lead with the outcome, no jargon, never overpromise. '
        . 'Take a completely fresh angle every time you are asked; do not repeat phrasings from earlier runs (variation seed: ' . wp_rand(1000, 9999) . '). '
        . "Respond in exactly this format with no extra commentary:\n"
        . "HEADLINE: <a punchy headline, under 10 words>\n"
        . 'SUBHEAD: <one supporting sentence, under 25 words>';
}

/**
 * Call one connector server-side and return its raw text response (or an
 * error). Every provider keeps its secret key in this one PHP function —
 * it never reaches the browser. Returns ['ok' => bool, 'text' => string] on
 * success or ['ok' => false, 'error' => string] on failure.
 */
function prt_ai_generate_text(string $slug, string $prompt): array
{
    $defs = prt_ai_connectors_defs();
    if (! isset($defs[$slug]) || ! prt_ai_is_configured($slug)) {
        return ['ok' => false, 'error' => __('That model isn\'t connected.', 'pressroot')];
    }

    if ($slug === 'pollinations') {
        $response = wp_remote_get('https://text.pollinations.ai/' . rawurlencode($prompt), ['timeout' => 20]);
        if (is_wp_error($response)) {
            return ['ok' => false, 'error' => $response->get_error_message()];
        }
        return ['ok' => true, 'text' => wp_remote_retrieve_body($response)];
    }

    $key   = prt_ai_get_key($slug);
    $model = prt_ai_get_model($slug);

    if ($slug === 'gemini') {
        $url = 'https://generativelanguage.googleapis.com/v1beta/models/' . rawurlencode($model) . ':generateContent?key=' . rawurlencode($key);
        $response = wp_remote_post($url, [
            'timeout' => 20,
            'headers' => ['Content-Type' => 'application/json'],
            'body'    => wp_json_encode([
                'contents' => [['parts' => [['text' => $prompt]]]],
            ]),
        ]);
        if (is_wp_error($response)) {
            return ['ok' => false, 'error' => $response->get_error_message()];
        }
        $body = json_decode(wp_remote_retrieve_body($response), true);
        $text = $body['candidates'][0]['content']['parts'][0]['text'] ?? '';
        if ($text === '' && ! empty($body['error']['message'])) {
            return ['ok' => false, 'error' => $body['error']['message']];
        }
        return ['ok' => true, 'text' => $text];
    }

    if ($slug === 'anthropic') {
        $response = wp_remote_post('https://api.anthropic.com/v1/messages', [
            'timeout' => 30,
            'headers' => [
                'Content-Type'      => 'application/json',
                'x-api-key'         => $key,
                'anthropic-version' => '2023-06-01',
            ],
            'body' => wp_json_encode([
                'model'      => $model,
                'max_tokens' => 2048,
                'messages'   => [['role' => 'user', 'content' => $prompt]],
            ]),
        ]);
        if (is_wp_error($response)) {
            return ['ok' => false, 'error' => $response->get_error_message()];
        }
        $body = json_decode(wp_remote_retrieve_body($response), true);
        $text = $body['content'][0]['text'] ?? '';
        if ($text === '' && ! empty($body['error']['message'])) {
            return ['ok' => false, 'error' => $body['error']['message']];
        }
        return ['ok' => true, 'text' => $text];
    }

    if ($slug === 'groq' || $slug === 'openrouter' || $slug === 'openai') {
        // All three speak the same OpenAI-compatible chat-completions shape.
        $endpoint = [
            'groq'       => 'https://api.groq.com/openai/v1/chat/completions',
            'openrouter' => 'https://openrouter.ai/api/v1/chat/completions',
            'openai'     => 'https://api.openai.com/v1/chat/completions',
        ][$slug];

        $headers = [
            'Content-Type'  => 'application/json',
            'Authorization' => 'Bearer ' . $key,
        ];
        if ($slug === 'openrouter') {
            // Recommended (not required) by OpenRouter so requests are attributable.
            $headers['HTTP-Referer'] = home_url('/');
            $headers['X-Title']      = get_bloginfo('name');
        }

        $response = wp_remote_post($endpoint, [
            'timeout' => 20,
            'headers' => $headers,
            'body'    => wp_json_encode([
                'model'    => $model,
                'messages' => [['role' => 'user', 'content' => $prompt]],
            ]),
        ]);
        if (is_wp_error($response)) {
            return ['ok' => false, 'error' => $response->get_error_message()];
        }
        $body = json_decode(wp_remote_retrieve_body($response), true);
        $text = $body['choices'][0]['message']['content'] ?? '';
        if ($text === '' && ! empty($body['error']['message'])) {
            return ['ok' => false, 'error' => $body['error']['message']];
        }
        return ['ok' => true, 'text' => $text];
    }

    return ['ok' => false, 'error' => __('Unknown model.', 'pressroot')];
}

/**
 * AJAX endpoint Pressroot AI's copy generator calls instead of hitting
 * Pollinations directly from the browser, so keyed providers' secret keys
 * never have to leave the server. Same admin-only gate as the rest of the
 * Pressroot AI screen, plus the addon on/off switch (app/theme-addons.php).
 */
add_action('wp_ajax_prt_ai_generate_copy', function () {
    if (! current_user_can('edit_theme_options') || ! prt_ai_features_enabled() || ! check_ajax_referer('prt_ai_generate_copy', 'nonce', false)) {
        wp_send_json_error(['message' => __('Not allowed.', 'pressroot')], 403);
    }

    $description = isset($_POST['description']) ? sanitize_text_field(wp_unslash($_POST['description'])) : '';
    $model       = isset($_POST['model']) ? sanitize_key($_POST['model']) : 'pollinations';

    if ($description === '') {
        wp_send_json_error(['message' => __('Describe your business first.', 'pressroot')]);
    }

    // Which site type is live right now? Detected from the starter pages the
    // Site Types tool tagged, so the prompt stays category-aware without the
    // browser needing to send anything extra.
    $activeType = '';
    if (function_exists('App\\prt_get_site_type_pages')) {
        foreach (prt_get_site_type_pages() as $sp) {
            if (! empty($sp->prt_site_type)) {
                $activeType = (string) $sp->prt_site_type;
                break;
            }
        }
    }

    $prompt = prt_ai_build_hero_prompt($description, $activeType);
    $result = prt_ai_generate_text($model, $prompt);

    if (! $result['ok']) {
        wp_send_json_error(['message' => $result['error'] ?: __('Generation failed.', 'pressroot')]);
    }

    wp_send_json_success(['text' => $result['text']]);
});


/* ─────────────────────── Image & video model registries ─────────────────────── */

/**
 * Image-generation providers. Pollinations stays the always-on keyless
 * default (the money-saving path); the paid providers are strictly optional
 * upgrades. Keys use the same theme_mod convention as the text connectors
 * (prt_ai_key_{slug}), saved by the same handler.
 */
function prt_ai_image_connectors_defs(): array
{
    return apply_filters('pressroot/ai_image_connectors', [
        'pollinations_img' => [
            'label'         => __('Pollinations Images (default)', 'pressroot'),
            'needs_key'     => false,
            'model_default' => 'flux',
            'models'        => ['flux', 'turbo'],
            'docs_url'      => 'https://pollinations.ai/',
            'note'          => __('Free, keyless image generation — already powers "Generate brand image" and the Customizer image finder. Always available.', 'pressroot'),
        ],
        'openai_img' => [
            'label'         => __('OpenAI Images (gpt-image-1)', 'pressroot'),
            'needs_key'     => true,
            'model_default' => 'gpt-image-1',
            'models'        => ['gpt-image-1'],
            'docs_url'      => 'https://platform.openai.com/api-keys',
            'note'          => __('Higher-fidelity brand imagery. Uses the same OpenAI key as the ChatGPT text connector if you saved one there.', 'pressroot'),
        ],
        'stability' => [
            'label'         => __('Stability AI (SD 3.5)', 'pressroot'),
            'needs_key'     => true,
            'model_default' => 'sd3.5-large-turbo',
            'models'        => ['sd3.5-large-turbo', 'sd3.5-large', 'sd3.5-medium', 'core'],
            'docs_url'      => 'https://platform.stability.ai/account/keys',
            'note'          => __('Stable Diffusion 3.5 — strong photographic + illustration styles.', 'pressroot'),
        ],
    ]);
}

/**
 * Video-generation providers. Keys are stored and validated NOW; the theme
 * feature that consumes them (AI Video Hero — a generated looping hero
 * background) is the next milestone, and this registry is where it will
 * look. Being upfront: saving a key here does not generate video yet.
 */
function prt_ai_video_connectors_defs(): array
{
    return apply_filters('pressroot/ai_video_connectors', [
        'luma' => [
            'label'         => __('Luma Dream Machine', 'pressroot'),
            'needs_key'     => true,
            'model_default' => 'ray-2',
            'models'        => ['ray-2', 'ray-flash-2'],
            'docs_url'      => 'https://lumalabs.ai/api',
            'note'          => __('For the upcoming AI Video Hero (looping hero backgrounds). Key stored + kept server-side now; generation ships next milestone.', 'pressroot'),
        ],
        'runway' => [
            'label'         => __('Runway', 'pressroot'),
            'needs_key'     => true,
            'model_default' => 'gen4_turbo',
            'models'        => ['gen4_turbo', 'gen3a_turbo'],
            'docs_url'      => 'https://dev.runwayml.com/',
            'note'          => __('Alternative video provider for the same upcoming feature.', 'pressroot'),
        ],
    ]);
}

/** Every connector def across text/image/video — used by the save handler. */
function prt_ai_all_connector_defs(): array
{
    return prt_ai_connectors_defs() + prt_ai_image_connectors_defs() + prt_ai_video_connectors_defs();
}

/** The image provider currently selected for generation (Brand tab image + future block fills). */
function prt_ai_active_image_connector(): string
{
    $slug = (string) get_theme_mod('prt_ai_image_model', 'pollinations_img');
    $defs = prt_ai_image_connectors_defs();
    if (! isset($defs[$slug]) || (! empty($defs[$slug]['needs_key']) && prt_ai_get_key($slug) === '' && prt_ai_get_key('openai') === '')) {
        return 'pollinations_img';
    }
    return $slug;
}

/**
 * Generate one image server-side. Returns ['ok'=>bool, 'file'=>tmp path] —
 * callers sideload the temp file into the Media Library. Falls back to the
 * keyless default on any provider failure so image generation never costs
 * a click twice.
 */
function prt_ai_generate_image(string $prompt, int $w = 1024, int $h = 1024, string $slug = ''): array
{
    require_once ABSPATH . 'wp-admin/includes/file.php';
    $slug = $slug !== '' ? $slug : prt_ai_active_image_connector();

    if ($slug === 'openai_img') {
        $key = prt_ai_get_key('openai_img') ?: prt_ai_get_key('openai');
        $response = wp_remote_post('https://api.openai.com/v1/images/generations', [
            'timeout' => 90,
            'headers' => ['Content-Type' => 'application/json', 'Authorization' => 'Bearer ' . $key],
            'body'    => wp_json_encode(['model' => prt_ai_get_model('openai_img'), 'prompt' => $prompt, 'size' => $w >= $h ? '1536x1024' : '1024x1536']),
        ]);
        $body = ! is_wp_error($response) ? json_decode(wp_remote_retrieve_body($response), true) : null;
        $b64  = $body['data'][0]['b64_json'] ?? '';
        if ($b64 !== '') {
            $tmp = wp_tempnam('prt-ai-image.png');
            file_put_contents($tmp, base64_decode($b64));
            return ['ok' => true, 'file' => $tmp];
        }
        // fall through to keyless default
    }

    if ($slug === 'stability') {
        $sdModel  = prt_ai_get_model('stability');
        $sdPath   = $sdModel === 'core' ? 'core' : 'sd3';
        $response = wp_remote_post('https://api.stability.ai/v2beta/stable-image/generate/' . $sdPath, [
            'timeout' => 90,
            'headers' => ['Authorization' => 'Bearer ' . prt_ai_get_key('stability'), 'Accept' => 'image/*'],
            'body'    => array_filter(['prompt' => $prompt, 'aspect_ratio' => $w >= $h ? '3:2' : '2:3', 'output_format' => 'jpeg', 'model' => $sdPath === 'sd3' ? $sdModel : null]),
        ]);
        if (! is_wp_error($response) && (int) wp_remote_retrieve_response_code($response) === 200) {
            $tmp = wp_tempnam('prt-ai-image.jpg');
            file_put_contents($tmp, wp_remote_retrieve_body($response));
            return ['ok' => true, 'file' => $tmp];
        }
        // fall through to keyless default
    }

    // Keyless default (and universal fallback): Pollinations.
    $url = 'https://image.pollinations.ai/prompt/' . rawurlencode($prompt) . '?width=' . absint($w) . '&height=' . absint($h) . '&nologo=true&model=' . rawurlencode(prt_ai_get_model('pollinations_img'));
    $tmp = download_url($url, 90);
    if (is_wp_error($tmp)) {
        return ['ok' => false, 'file' => '', 'error' => $tmp->get_error_message()];
    }
    return ['ok' => true, 'file' => $tmp];
}

/* ─────────────────────────── AI Models tab ─────────────────────────── */

/**
 * "AI Models" tab on Appearance -> Pressroot: writing, image, and video
 * providers in one place (previously buried in an Advanced accordion).
 * Philosophy printed right on the tab: the free keyless defaults do the
 * whole job — paid keys are optional quality upgrades, never requirements.
 */
function prt_ai_models_tab_html(): void
{
    if (! current_user_can('manage_options')) {
        return;
    }
    if (! function_exists('App\\prt_ai_features_enabled') || ! prt_ai_features_enabled()) {
        echo '<h2 style="margin-top:0">' . esc_html__('AI Models', 'pressroot') . '</h2>';
        echo '<p class="description">' . esc_html__('AI features are switched off (Brand tab → "Powered by AI — or not"). Flip them on to connect models.', 'pressroot') . '</p>';
        return;
    }
    if (isset($_GET['connectors_updated'])) {
        echo '<div class="notice notice-success is-dismissible"><p>' . esc_html__('AI models saved.', 'pressroot') . '</p></div>';
    }
    ?>
    <h2 style="margin-top:0"><?php esc_html_e('AI Models', 'pressroot'); ?></h2>
    <p class="description" style="max-width:680px"><?php esc_html_e('Built to save you money: the keyless free models already power everything — copywriting, images, the editor button. Add a key only where you want a quality upgrade. Every key is stored server-side and never reaches the browser.', 'pressroot'); ?></p>

    <h3 style="margin:22px 0 4px">✍️ <?php esc_html_e('Writing', 'pressroot'); ?></h3>
    <p class="description"><?php esc_html_e('Used by AI-write, the copy generator, and the block editor\'s "Generate with AI".', 'pressroot'); ?></p>
    <?php prt_ai_connectors_fields_html(); ?>

    <h3 style="margin:26px 0 4px">🎨 <?php esc_html_e('Images', 'pressroot'); ?></h3>
    <p class="description"><?php esc_html_e('Used by "Generate brand image" and the Customizer image finder.', 'pressroot'); ?></p>
    <form method="post" action="<?php echo esc_url(admin_url('admin-post.php')); ?>">
        <input type="hidden" name="action" value="prt_save_ai_connectors">
        <input type="hidden" name="prt_models_group" value="image">
        <?php wp_nonce_field('prt_save_ai_connectors'); ?>
        <table class="form-table" role="presentation">
            <tr>
                <th scope="row"><?php esc_html_e('Generate images with', 'pressroot'); ?></th>
                <td>
                    <select name="prt_ai_image_model">
                        <?php foreach (prt_ai_image_connectors_defs() as $slug => $def) : ?>
                            <option value="<?php echo esc_attr($slug); ?>" <?php selected(get_theme_mod('prt_ai_image_model', 'pollinations_img'), $slug); ?>><?php echo esc_html($def['label']); ?></option>
                        <?php endforeach; ?>
                    </select>
                    <p class="description"><?php esc_html_e('Falls back to the free default automatically if a paid provider errors.', 'pressroot'); ?></p>
                </td>
            </tr>
            <tr>
                <th scope="row"><?php echo esc_html(prt_ai_image_connectors_defs()['pollinations_img']['label']); ?></th>
                <td>
                    <p class="description" style="margin:0 0 8px"><?php echo esc_html(prt_ai_image_connectors_defs()['pollinations_img']['note']); ?></p>
                    <?php prt_ai_model_select_html('pollinations_img', prt_ai_image_connectors_defs()['pollinations_img']); ?>
                </td>
            </tr>
            <?php foreach (prt_ai_image_connectors_defs() as $slug => $def) :
                if (empty($def['needs_key'])) { continue; } ?>
                <tr>
                    <th scope="row"><?php echo esc_html($def['label']); ?><?php if (prt_ai_is_configured($slug) || ($slug === 'openai_img' && prt_ai_get_key('openai') !== '')) : ?> <span style="display:inline-block;margin-left:6px;padding:2px 8px;border-radius:999px;background:#edfaef;color:#1e7a34;font-size:11px;font-weight:600"><?php esc_html_e('Connected', 'pressroot'); ?></span><?php endif; ?></th>
                    <td>
                        <p class="description" style="margin:0 0 8px"><?php echo esc_html($def['note']); ?> <a href="<?php echo esc_url($def['docs_url']); ?>" target="_blank" rel="noopener noreferrer"><?php esc_html_e('Get a key ↗', 'pressroot'); ?></a></p>
                        <input type="password" class="regular-text" autocomplete="off" name="prt_ai_key_<?php echo esc_attr($slug); ?>" value="" placeholder="<?php echo prt_ai_get_key($slug) !== '' ? esc_attr(str_repeat('•', 12)) : ''; ?>">
                        <?php prt_ai_model_select_html($slug, $def); ?>
                    </td>
                </tr>
            <?php endforeach; ?>
            <?php foreach (prt_ai_video_connectors_defs() as $slug => $def) : ?>
                <?php if ($slug === array_key_first(prt_ai_video_connectors_defs())) : ?>
                    <tr><th colspan="2" style="padding-bottom:0"><h3 style="margin:14px 0 0">🎬 <?php esc_html_e('Video (keys stored now — AI Video Hero ships next milestone)', 'pressroot'); ?></h3></th></tr>
                <?php endif; ?>
                <tr>
                    <th scope="row"><?php echo esc_html($def['label']); ?><?php if (prt_ai_get_key($slug) !== '') : ?> <span style="display:inline-block;margin-left:6px;padding:2px 8px;border-radius:999px;background:#edfaef;color:#1e7a34;font-size:11px;font-weight:600"><?php esc_html_e('Saved', 'pressroot'); ?></span><?php endif; ?></th>
                    <td>
                        <p class="description" style="margin:0 0 8px"><?php echo esc_html($def['note']); ?> <a href="<?php echo esc_url($def['docs_url']); ?>" target="_blank" rel="noopener noreferrer"><?php esc_html_e('Get a key ↗', 'pressroot'); ?></a></p>
                        <input type="password" class="regular-text" autocomplete="off" name="prt_ai_key_<?php echo esc_attr($slug); ?>" value="" placeholder="<?php echo prt_ai_get_key($slug) !== '' ? esc_attr(str_repeat('•', 12)) : ''; ?>">
                        <?php prt_ai_model_select_html($slug, $def); ?>
                    </td>
                </tr>
            <?php endforeach; ?>
        </table>
        <?php submit_button(__('Save image & video models', 'pressroot')); ?>
    </form>
    <?php
}
