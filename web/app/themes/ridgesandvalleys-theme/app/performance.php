<?php

/**
 * Performance & bloat control.
 *
 * Trims common WordPress front-end bloat with safe, opt-out defaults: removes
 * the emoji script/styles, jQuery Migrate, XML-RPC/pingbacks, Dashicons for
 * logged-out visitors, and the assorted <head> meta links (generator, RSD,
 * WLW, shortlink, REST discovery). Each toggle is filterable via
 * `rv/perf` so a site can flip any of them off without editing the theme:
 *
 *   add_filter('rv/perf', fn ($o) => array_merge($o, ['emojis' => false]));
 */

namespace App;

/** Performance toggles (filterable). */
function perf(): array
{
    return apply_filters('rv/perf', [
        'emojis'     => true,   // remove emoji detection script + styles
        'migrate'    => true,   // drop jquery-migrate
        'xmlrpc'     => true,   // disable XML-RPC + pingback
        'dashicons'  => true,   // dequeue dashicons for logged-out visitors
        'headclean'  => true,   // remove generator/rsd/wlw/shortlink/rest links
        'defer'      => true,   // defer non-critical front-end scripts
        'preconnect' => [],     // extra origins to preconnect (fonts are self-hosted)
    ]);
}

/** Front-end bloat removal. */
add_action('init', function () {
    if (is_admin()) {
        return;
    }
    $o = perf();

    if (! empty($o['emojis'])) {
        remove_action('wp_head', 'print_emoji_detection_script', 7);
        remove_action('wp_print_styles', 'print_emoji_styles');
        add_filter('tiny_mce_plugins', fn($p) => is_array($p) ? array_diff($p, ['wpemoji']) : $p);
        add_filter('emoji_svg_url', '__return_false');
    }

    if (! empty($o['xmlrpc'])) {
        add_filter('xmlrpc_enabled', '__return_false');
        add_filter('wp_headers', function ($h) {
            unset($h['X-Pingback']);
            return $h;
        });
    }

    if (! empty($o['headclean'])) {
        remove_action('wp_head', 'rsd_link');
        remove_action('wp_head', 'wlwmanifest_link');
        remove_action('wp_head', 'wp_generator');
        remove_action('wp_head', 'wp_shortlink_wp_head');
        remove_action('wp_head', 'rest_output_link_wp_head');
        remove_action('template_redirect', 'rest_output_link_header', 11);
    }
});

/** Remove jQuery Migrate from jQuery's dependency chain (front-end only). */
add_action('wp_default_scripts', function ($scripts) {
    if (is_admin() || empty(perf()['migrate'])) {
        return;
    }
    if (! empty($scripts->registered['jquery'])) {
        $scripts->registered['jquery']->deps = array_diff($scripts->registered['jquery']->deps, ['jquery-migrate']);
    }
});

/** Dequeue Dashicons for anonymous visitors (only the admin bar needs it). */
add_action('wp_enqueue_scripts', function () {
    if (! empty(perf()['dashicons']) && ! is_user_logged_in()) {
        wp_dequeue_style('dashicons');
        wp_deregister_style('dashicons');
    }
}, 100);

/** Preconnect resource hints for any configured origins. */
add_filter('wp_resource_hints', function ($hints, $relation) {
    if ($relation !== 'preconnect') {
        return $hints;
    }
    foreach ((array) (perf()['preconnect'] ?? []) as $url) {
        if ($url) {
            $hints[] = ['href' => esc_url($url), 'crossorigin'];
        }
    }
    return $hints;
}, 10, 2);

/** Defer non-critical front-end scripts (keeps jQuery synchronous). */
add_filter('script_loader_tag', function ($tag, $handle) {
    if (is_admin() || empty(perf()['defer'])) {
        return $tag;
    }
    $skip = ['jquery-core', 'jquery'];
    if (in_array($handle, $skip, true) || strpos($tag, ' defer') !== false || strpos($tag, ' async') !== false) {
        return $tag;
    }
    return str_replace(' src=', ' defer src=', $tag);
}, 10, 2);
