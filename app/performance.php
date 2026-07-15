<?php

/**
 * Performance & bloat control.
 * A Customizer "Performance" section toggles common WordPress front-end
 * optimizations: disable emojis/embeds/jQuery-migrate/XML-RPC/dashicons,
 * clean wp_head, defer scripts, and add preconnect resource hints.
 */

namespace App;

/**
 * Default values for every Performance toggle, used both to pre-fill the
 * Customizer controls and as the fallback when reading a setting via
 * prt_perf(). Centralizing them here means the "safe defaults" promise in
 * the Customizer section description only needs to be true in one place.
 */
function prt_perf_defaults()
{
    return [
        'prt_perf_emojis'    => true,   // remove emoji detection script + styles
        'prt_perf_embeds'    => false,  // remove wp-embed.js + oEmbed discovery
        'prt_perf_migrate'   => true,   // drop jquery-migrate
        'prt_perf_xmlrpc'    => true,   // disable XML-RPC + pingback
        'prt_perf_dashicons' => true,   // dequeue dashicons for logged-out visitors
        'prt_perf_headclean' => true,   // remove generator/rsd/wlw/shortlink/rest links
        'prt_perf_defer'     => false,  // defer non-critical front-end scripts
        'prt_perf_preconnect' => 'https://fonts.googleapis.com, https://fonts.gstatic.com',
    ];
}

/**
 * Read a single Performance setting, falling back to its default from
 * prt_perf_defaults() if no theme_mod has been saved yet. Small wrapper so
 * every call site below doesn't have to know/repeat the default itself.
 */
function prt_perf($key)
{
    $d = prt_perf_defaults();
    return get_theme_mod($key, $d[$key] ?? null);
}

/**
 * Register the "Performance" Customizer section: a checkbox per bloat-removal
 * toggle plus a free-text preconnect-domains field.
 */
add_action('customize_register', function ($wp) {
    // Shared guarded helper — see prt_ensure_theme_options_panel() in app/customizer.php.
    prt_ensure_theme_options_panel($wp);
    $wp->add_section('prt_perf_section', [
        'title'       => __('Performance', 'pressroot'),
        'panel'       => 'prt_theme_options',
        'description' => __('Trim WordPress front-end bloat. Safe defaults are pre-selected; toggle anything that conflicts with a plugin.', 'pressroot'),
    ]);

    $d = prt_perf_defaults();
    $bools = [
        'prt_perf_emojis'    => __('Disable emoji script & styles', 'pressroot'),
        'prt_perf_embeds'    => __('Disable oEmbed / wp-embed.js', 'pressroot'),
        'prt_perf_migrate'   => __('Remove jQuery Migrate', 'pressroot'),
        'prt_perf_xmlrpc'    => __('Disable XML-RPC & pingbacks', 'pressroot'),
        'prt_perf_dashicons' => __('Dequeue Dashicons for logged-out visitors', 'pressroot'),
        'prt_perf_headclean' => __('Clean wp_head (generator, RSD, shortlink, REST links)', 'pressroot'),
        'prt_perf_defer'     => __('Defer non-critical front-end scripts', 'pressroot'),
    ];
    foreach ($bools as $id => $label) {
        $wp->add_setting($id, ['default' => $d[$id], 'sanitize_callback' => 'wp_validate_boolean']);
        $wp->add_control($id, ['label' => $label, 'section' => 'prt_perf_section', 'type' => 'checkbox']);
    }

    $wp->add_setting('prt_perf_preconnect', ['default' => $d['prt_perf_preconnect'], 'sanitize_callback' => 'sanitize_text_field']);
    $wp->add_control('prt_perf_preconnect', [
        'label'       => __('Preconnect domains', 'pressroot'),
        'description' => __('Comma-separated origins to preconnect (speeds up fonts/CDNs).', 'pressroot'),
        'section'     => 'prt_perf_section',
        'type'        => 'text',
    ]);
}, 26);

/* ---- Apply (front-end only) ---- */

/**
 * Actually perform the opt-in removals/filters, gated per-toggle by prt_perf().
 * Hooked to `init` (rather than doing this at file load time) because several
 * of the calls below are themselves add_action/add_filter registrations that
 * need the rest of WordPress core bootstrapped first, and because we need
 * is_admin() to be reliably available to bail out for wp-admin requests.
 */
add_action('init', function () {
    if (is_admin()) {
        return;
    }

    if (prt_perf('prt_perf_emojis')) {
        remove_action('wp_head', 'print_emoji_detection_script', 7);
        remove_action('wp_print_styles', 'print_emoji_styles');
        // NOTE(audit): admin_print_scripts/admin_print_styles never fire here —
        // this whole callback already returned above when is_admin() is true,
        // so these two remove_action() calls are unreachable dead code and the
        // emoji script/styles are never actually removed from wp-admin.
        remove_action('admin_print_scripts', 'print_emoji_detection_script');
        remove_action('admin_print_styles', 'print_emoji_styles');
        add_filter('tiny_mce_plugins', function ($p) {
            return is_array($p) ? array_diff($p, ['wpemoji']) : $p;
        });
        add_filter('emoji_svg_url', '__return_false');
    }

    if (prt_perf('prt_perf_embeds')) {
        // Deregistering inside wp_footer (not directly here) because wp-embed
        // is enqueued later in the request lifecycle; deregistering too early
        // would just have it re-registered by WordPress core afterward.
        add_action('wp_footer', function () {
            wp_deregister_script('wp-embed');
        });
        remove_action('wp_head', 'wp_oembed_add_discovery_links');
        remove_action('wp_head', 'wp_oembed_add_host_js');
    }

    if (prt_perf('prt_perf_xmlrpc')) {
        add_filter('xmlrpc_enabled', '__return_false');
        // Disabling XML-RPC doesn't remove the X-Pingback header on its own,
        // so it's stripped separately here to fully close the pingback surface.
        add_filter('wp_headers', function ($h) {
            unset($h['X-Pingback']);
            return $h;
        });
    }

    // Strip the assorted <head> tags that leak version/software info or add
    // links most sites never use (RSD/WLW are for offline blog editors, the
    // shortlink and REST discovery links are rarely needed once permalinks work).
    if (prt_perf('prt_perf_headclean')) {
        remove_action('wp_head', 'rsd_link');
        remove_action('wp_head', 'wlwmanifest_link');
        remove_action('wp_head', 'wp_generator');
        remove_action('wp_head', 'wp_shortlink_wp_head');
        remove_action('wp_head', 'rest_output_link_wp_head');
        remove_action('wp_head', 'wp_oembed_add_discovery_links');
        remove_action('template_redirect', 'rest_output_link_header', 11);
    }
});

/**
 * Remove jquery-migrate from jQuery's dependency list before scripts are
 * registered. Must run on `wp_default_scripts` (not `wp_enqueue_scripts`)
 * because that's the earliest point core's default script definitions
 * (including jQuery's own deps array) exist and can still be mutated before
 * anything enqueues them.
 */
add_action('wp_default_scripts', function ($scripts) {
    if (is_admin() || ! prt_perf('prt_perf_migrate')) {
        return;
    }
    if (! empty($scripts->registered['jquery'])) {
        $deps = $scripts->registered['jquery']->deps;
        $scripts->registered['jquery']->deps = array_diff($deps, ['jquery-migrate']);
    }
});

/**
 * Dequeue Dashicons for anonymous visitors — it's only needed for admin-bar
 * icons, so logged-out users pay for a font they never see. Priority 100
 * (very late) ensures this runs after any plugin/theme code that enqueues
 * dashicons on the front end, so it's actually removed rather than re-added.
 */
add_action('wp_enqueue_scripts', function () {
    if (prt_perf('prt_perf_dashicons') && ! is_user_logged_in()) {
        wp_dequeue_style('dashicons');
        wp_deregister_style('dashicons');
    }
}, 100);

/**
 * Add <link rel="preconnect"> hints for the comma-separated origins configured
 * in the Customizer (defaults to the Google Fonts domains this theme uses).
 * Only touches the 'preconnect' resource-hint relation so it doesn't
 * interfere with 'dns-prefetch'/'prefetch'/'prerender' hints from elsewhere.
 */
add_filter('wp_resource_hints', function ($hints, $relation) {
    if ($relation !== 'preconnect') {
        return $hints;
    }
    $raw = (string) prt_perf('prt_perf_preconnect');
    foreach (array_filter(array_map('trim', explode(',', $raw))) as $url) {
        $hints[] = ['href' => esc_url($url), 'crossorigin'];
    }
    return $hints;
}, 10, 2);

/**
 * Add the `defer` attribute to front-end <script> tags so parsing isn't
 * blocked, opt-in via prt_perf_defer since deferring can break scripts that
 * assume synchronous execution order (hence the explicit skip list and the
 * checks for tags that already declare defer/async).
 */
add_filter('script_loader_tag', function ($tag, $handle) {
    if (is_admin() || ! prt_perf('prt_perf_defer')) {
        return $tag;
    }
    // Keep these synchronous to avoid breakage.
    $skip = ['jquery-core', 'jquery'];
    if (in_array($handle, $skip, true) || strpos($tag, ' defer') !== false || strpos($tag, ' async') !== false) {
        return $tag;
    }
    return str_replace(' src=', ' defer src=', $tag);
}, 10, 2);
