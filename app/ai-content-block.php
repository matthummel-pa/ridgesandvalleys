<?php

/**
 * AI content generation inside the block editor.
 *
 * Adds a "Generate with AI" toolbar button to core/paragraph, core/heading,
 * and core/list-item blocks (via editor.BlockEdit, resources/js/ai-content-block.js)
 * so an author can write a short instruction and have it replace that
 * block's text — while actually editing a page/post, not just the one-time
 * Pressroot AI screen. Uses the exact same connector registry and
 * server-side prt_ai_generate_text() from app/ai-connectors.php, so it works
 * with Pollinations out of the box and with any connected Gemini/Groq/
 * OpenRouter model — API keys stay server-side here too, via a dedicated
 * AJAX endpoint (prt_ai_generate_block_content) rather than the browser
 * ever seeing a key.
 *
 * Gated at `edit_posts` (not `edit_theme_options`) since this is an everyday
 * editorial writing aid any author/contributor should be able to use, unlike
 * the Pressroot AI screen and its AI Connectors settings, which are
 * theme-owner tools. Also part of the "Pressroot AI" addon (see
 * app/theme-addons.php) — the script isn't even enqueued, and the AJAX
 * endpoint refuses requests, when that addon is switched off.
 */

namespace App;

add_action('enqueue_block_editor_assets', function () {
    if (! prt_ai_features_enabled()) {
        return;
    }
    $path = 'resources/js/ai-content-block.js';
    wp_enqueue_script(
        'prt-ai-content-block',
        get_theme_file_uri($path),
        ['wp-blocks', 'wp-element', 'wp-block-editor', 'wp-components', 'wp-compose', 'wp-hooks', 'wp-i18n'],
        file_exists(get_theme_file_path($path)) ? filemtime(get_theme_file_path($path)) : '1',
        true
    );

    $models = [];
    foreach (prt_ai_configured_connectors() as $slug => $connector) {
        $models[] = [
            'slug'  => $slug,
            'label' => $connector['label'] . (! empty($connector['model']) ? ' — ' . $connector['model'] : ''),
        ];
    }

    wp_localize_script('prt-ai-content-block', 'prtAIBlock', [
        'ajaxUrl' => admin_url('admin-ajax.php'),
        'nonce'   => wp_create_nonce('prt_ai_generate_block'),
        'models'  => $models,
    ]);
});

/**
 * AJAX endpoint the block-editor toolbar button calls. Unlike Pressroot AI's
 * hero-copy prompt (which forces a HEADLINE/SUBHEAD shape), this is a
 * free-form instruction — the response is meant to drop straight into a
 * paragraph/heading/list-item's RichText content, so the model is asked for
 * plain prose with no markdown/formatting.
 */
add_action('wp_ajax_prt_ai_generate_block_content', function () {
    if (! current_user_can('edit_posts') || ! prt_ai_features_enabled() || ! check_ajax_referer('prt_ai_generate_block', 'nonce', false)) {
        wp_send_json_error(['message' => __('Not allowed.', 'pressroot')], 403);
    }

    $prompt = isset($_POST['prompt']) ? sanitize_textarea_field(wp_unslash($_POST['prompt'])) : '';
    $model  = isset($_POST['model']) ? sanitize_key($_POST['model']) : 'pollinations';

    if ($prompt === '') {
        wp_send_json_error(['message' => __('Describe what this should say first.', 'pressroot')]);
    }

    $fullPrompt = 'Write website copy for this instruction: "' . $prompt . '". '
        . 'Respond with plain prose only — no markdown, no headings, no bullet points, '
        . 'no quotation marks, no commentary — just the text to use directly.';

    $result = prt_ai_generate_text($model, $fullPrompt);

    if (! $result['ok']) {
        wp_send_json_error(['message' => $result['error'] ?: __('Generation failed.', 'pressroot')]);
    }

    wp_send_json_success(['text' => trim($result['text'], " \t\n\r\0\x0B\"")]);
});
