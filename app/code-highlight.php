<?php

/**
 * Code highlighting for core/code blocks: Prism syntax highlighting, line
 * numbers, and an optional filename label. Prism (cdnjs) loads only on pages
 * that actually contain a code block. Editor controls add language/filename/
 * line-numbers to the standard Code block (see resources/js/code-block-editor.js).
 */

namespace App;

// Pinned Prism version/CDN base. Pinning (rather than an unversioned "latest"
// URL) keeps highlighting output stable across page loads and avoids a
// surprise breaking change from cdnjs; bump this constant deliberately when
// upgrading Prism.
const PRT_PRISM = 'https://cdnjs.cloudflare.com/ajax/libs/prism/1.29.0';

/**
 * Whether code-block syntax highlighting is enabled. Single source of truth
 * read by every hook below, backed by the Customizer checkbox registered
 * just below (default on).
 */
function prt_code_hl_on()
{
    return (bool) get_theme_mod('prt_code_highlight', true);
}

/**
 * Registers the highlighting on/off checkbox into the theme's existing
 * "Performance" Customizer section (registered by another file — this one
 * just bails out if that section doesn't exist yet rather than creating its
 * own section, since this is a minor sub-toggle of performance, not a
 * feature important enough to warrant its own section).
 */
add_action('customize_register', function ($wp) {
    if (! $wp->get_section('prt_perf_section')) {
        return;
    }
    $wp->add_setting('prt_code_highlight', ['default' => true, 'sanitize_callback' => 'wp_validate_boolean']);
    $wp->add_control('prt_code_highlight', ['label' => __('Syntax-highlight code blocks (Prism)', 'pressroot'), 'section' => 'prt_perf_section', 'type' => 'checkbox']);
}, 28);

/**
 * Loads the editor-side JS (resources/js/code-block-editor.js) that adds
 * language/filename/line-numbers controls to the standard core/code block's
 * Inspector panel. Editor-only enqueue — this doesn't touch the front end,
 * where Prism itself (registered below) does the actual highlighting.
 */
add_action('enqueue_block_editor_assets', function () {
    if (! prt_code_hl_on()) {
        return;
    }
    $path = 'resources/js/code-block-editor.js';
    wp_enqueue_script(
        'prt-code-block',
        get_theme_file_uri($path),
        ['wp-blocks', 'wp-element', 'wp-block-editor', 'wp-components', 'wp-compose', 'wp-hooks', 'wp-i18n'],
        file_exists(get_theme_file_path($path)) ? filemtime(get_theme_file_path($path)) : '1',
        true
    );
});

/**
 * Registers (but does not enqueue) the Prism CSS/JS handles from cdnjs.
 * Registering here, then enqueuing conditionally in the render_block filter
 * below, means Prism is only ever actually loaded on pages that contain a
 * highlighted code block — most pages pay zero cost for this feature.
 * Note: 'prt-prism-ln' is registered as both a style handle and a script
 * handle; WordPress keeps style and script handles in separate namespaces,
 * so reusing the name for both isn't a collision.
 */
add_action('wp_enqueue_scripts', function () {
    if (! prt_code_hl_on()) {
        return;
    }
    wp_register_style('prt-prism-theme', PRT_PRISM . '/themes/prism-tomorrow.min.css', [], '1.29.0');
    wp_register_style('prt-prism-ln', PRT_PRISM . '/plugins/line-numbers/prism-line-numbers.min.css', [], '1.29.0');
    wp_register_script('prt-prism', PRT_PRISM . '/prism.min.js', [], '1.29.0', true);
    wp_register_script('prt-prism-auto', PRT_PRISM . '/plugins/autoloader/prism-autoloader.min.js', ['prt-prism'], '1.29.0', true);
    wp_register_script('prt-prism-ln', PRT_PRISM . '/plugins/line-numbers/prism-line-numbers.min.js', ['prt-prism'], '1.29.0', true);
    // Tell Prism's autoloader plugin where to fetch per-language grammar
    // files from, since they're not bundled in prism.min.js itself.
    wp_add_inline_script('prt-prism-auto', "if(window.Prism&&Prism.plugins&&Prism.plugins.autoloader){Prism.plugins.autoloader.languages_path='" . PRT_PRISM . "/components/';}");
});

/**
 * Transform core/code on render: enqueues Prism only for requests that
 * actually rendered a highlighted/labeled code block, and wraps the block in
 * a <figure> with a filename caption when one was set. Hooking render_block
 * (rather than enqueueing Prism unconditionally on wp_enqueue_scripts) is
 * what makes the "only loads on pages with a code block" promise in the
 * file-level doc comment true — by the time this filter runs, WordPress has
 * already generated the block's final HTML, so we can inspect it to decide.
 */
add_filter('render_block', function ($content, $block) {
    if (! prt_code_hl_on() || ($block['blockName'] ?? '') !== 'core/code') {
        return $content;
    }
    // Only enhance blocks the editor tagged with a language or filename;
    // untouched core/code blocks are left completely alone.
    if (strpos($content, 'language-') === false && strpos($content, 'data-filename') === false) {
        return $content;
    }

    foreach (['prt-prism-theme', 'prt-prism-ln'] as $s) {
        wp_enqueue_style($s);
    }
    foreach (['prt-prism', 'prt-prism-auto', 'prt-prism-ln'] as $j) {
        wp_enqueue_script($j);
    }

    // Pull an optional filename and render it as a caption above the <pre>.
    if (preg_match('/data-filename="([^"]+)"/', $content, $m) && $m[1] !== '') {
        $file = esc_html($m[1]);
        $content = preg_replace('/\sdata-filename="[^"]*"/', '', $content, 1);
        $content = '<figure class="prt-code"><figcaption class="prt-code-file">' . $file . '</figcaption>' . $content . '</figure>';
    }
    return $content;
}, 10, 2);

/**
 * Styles for the filename caption + rounded-corner join between the caption
 * and the Prism-highlighted <pre>. Prism's own theme CSS (prism-tomorrow,
 * enqueued above) handles syntax colors; this covers only the theme's own
 * <figure>/<figcaption> wrapper markup, so it's kept separate from Prism's CSS.
 */
add_action('prt_head_end', function () {
    if (! prt_code_hl_on()) {
        return;
    }
    echo "\n<style id=\"prt-code-hl\">.prt-code{margin:1.5em 0;}.prt-code-file{font:600 12px/1 var(--font-mono,monospace);background:#0b0c0e;color:#9aa4b2;padding:9px 14px;border-radius:10px 10px 0 0;border:1px solid #1f242c;border-bottom:0;}.prt-code .wp-block-code,.prt-code pre{margin-top:0;border-top-left-radius:0;border-top-right-radius:0;}pre[class*=language-]{border-radius:10px;}</style>\n";
}, 14);
