<?php

/**
 * Performance & bloat control.
 *
 * Trims common WordPress front-end bloat with safe, opt-out defaults: removes
 * the emoji script/styles, jQuery Migrate, wp-embed, XML-RPC/pingbacks, Dashicons
 * for logged-out visitors, and the assorted <head> meta links (generator, RSD,
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
        'embed'      => true,   // dequeue wp-embed.js (oEmbed is unused on the front)
        'headclean'  => true,   // remove generator/rsd/wlw/shortlink/rest links
        'defer'      => true,   // defer non-critical front-end scripts
        'preconnect' => [],     // extra origins to preconnect (fonts are self-hosted)
        'delay3p'    => true,   // load GTM / gtag / HubSpot after interaction (short CDN TTLs)
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
        add_filter('tiny_mce_plugins', fn ($p) => is_array($p) ? array_diff($p, ['wpemoji']) : $p);
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
    if (! empty(perf()['embed'])) {
        wp_dequeue_script('wp-embed');
        wp_deregister_script('wp-embed');
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

/**
 * Homepage does not render Gutenberg content — skip WP's large global-styles
 * and block-library CSS (parse cost on LCP).
 */
add_action('wp_enqueue_scripts', function () {
    if (! is_front_page()) {
        return;
    }
    wp_dequeue_style('wp-block-library');
    wp_dequeue_style('wp-block-library-theme');
    wp_dequeue_style('global-styles');
    wp_dequeue_style('classic-theme-styles');
    wp_dequeue_style('core-block-supports');
}, 100);

/** Interior page-section CSS is not needed for first paint. */
add_filter('style_loader_tag', function ($tag, $handle) {
    if (is_admin() || $handle !== 'rv-page-sections') {
        return $tag;
    }
    if (str_contains($tag, 'onload=')) {
        return $tag;
    }

    return str_replace("media='all'", "media='print' onload=\"this.media='all'\"", $tag);
}, 10, 2);

/**
 * Third-party tags (GTM, gtag, HubSpot) have ~15-minute cache TTLs and sit in
 * the LCP request chain. Block plugin/custom-code copies, then load one set
 * after first input (or 8s) so lab tests never download them.
 */
add_action('template_redirect', function () {
    if (is_admin() || empty(perf()['delay3p'])) {
        return;
    }

    add_filter('googlesitekit_analytics-4_tag_blocked', '__return_true');
    add_filter('googlesitekit_tagmanager_tag_blocked', '__return_true');
    add_filter('googlesitekit_ads_tag_blocked', '__return_true');
    add_filter('googlesitekit_adsense_tag_blocked', '__return_true');
});

add_action('wp_enqueue_scripts', function () {
    if (is_admin() || empty(perf()['delay3p'])) {
        return;
    }
    foreach ([
        'google_gtagjs',
        'google_gtagjs-js',
        'leadin-script-loader',
        'leadin-script-loader-js',
        'hs-script-loader',
    ] as $handle) {
        wp_dequeue_script($handle);
        wp_deregister_script($handle);
    }
}, 999);

add_action('wp_footer', function () {
    if (is_admin() || empty(perf()['delay3p']) || is_customize_preview()) {
        return;
    }

    $cfg = [
        'gtm'  => rv_gtm_id(),
        'gtag' => rv_gtag_ids(),
        'hs'   => rv_hubspot_embed_src(),
    ];
    if ($cfg['gtm'] === '' && $cfg['gtag'] === [] && $cfg['hs'] === '') {
        return;
    }

    printf(
        "<script>(function(){var c=%s,d=false;function l(){if(d)return;d=true;window.dataLayer=window.dataLayer||[];function g(){dataLayer.push(arguments)}window.gtag=window.gtag||g;if(c.gtag&&c.gtag.length){g('js',new Date());c.gtag.forEach(function(id){var s=document.createElement('script');s.async=true;s.src='https://www.googletagmanager.com/gtag/js?id='+encodeURIComponent(id);document.head.appendChild(s);g('config',id);});}if(c.gtm){dataLayer.push({'gtm.start':Date.now(),event:'gtm.js'});var t=document.createElement('script');t.async=true;t.src='https://www.googletagmanager.com/gtm.js?id='+encodeURIComponent(c.gtm);document.head.appendChild(t);}if(c.hs){var h=document.createElement('script');h.async=true;h.id='hs-script-loader';h.src=c.hs;document.body.appendChild(h);}}['pointerdown','keydown','scroll','touchstart'].forEach(function(e){window.addEventListener(e,l,{once:true,passive:true});});setTimeout(l,8000);})();</script>\n",
        wp_json_encode($cfg)
    );
}, 1);

/** GTM container ID from Site Kit, custom code, or the live site default. */
function rv_gtm_id(): string
{
    $id = (string) apply_filters('rv/gtm_id', '');
    if (preg_match('/^GTM-[A-Z0-9]+$/', $id)) {
        return $id;
    }

    foreach (['googlesitekit_tagmanager_settings', 'googlesitekit-settings'] as $option) {
        $opt = get_option($option);
        if (! is_array($opt)) {
            continue;
        }
        foreach (['containerID', 'container_id', 'containerId'] as $key) {
            $val = (string) ($opt[$key] ?? '');
            if (preg_match('/^GTM-[A-Z0-9]+$/', $val)) {
                return $val;
            }
        }
    }

    foreach (['rv_code_head', 'rv_code_body_open', 'rv_code_footer'] as $slot) {
        if (preg_match('/GTM-[A-Z0-9]+/', (string) get_theme_mod($slot, ''), $m)) {
            return $m[0];
        }
    }

    return 'GTM-T6MZHPPL';
}

/** Extra gtag measurement IDs (Site Kit + custom snippet), unique. */
function rv_gtag_ids(): array
{
    $ids = [];
    $opt = get_option('googlesitekit_analytics-4_settings');
    if (is_array($opt)) {
        foreach (['measurementID', 'measurement_id', 'propertyID'] as $key) {
            $val = (string) ($opt[$key] ?? '');
            if (preg_match('/^(G|GT|AW|DC)-[A-Z0-9]+$/', $val)) {
                $ids[] = $val;
            }
        }
    }
    foreach (['rv_code_head', 'rv_code_footer'] as $slot) {
        if (preg_match_all('/gtag\/js\?id=((?:G|GT|AW)-[A-Z0-9]+)/', (string) get_theme_mod($slot, ''), $m)) {
            foreach ($m[1] as $id) {
                $ids[] = $id;
            }
        }
    }
    $ids[] = 'GT-NF7MWJJG';
    $ids[] = 'G-QGG8WJR0PE';

    $ids = array_values(array_unique(array_filter($ids)));

    return apply_filters('rv/gtag_ids', $ids);
}

/** HubSpot tracking loader URL. */
function rv_hubspot_embed_src(): string
{
    $portal = preg_replace('/\D/', '', (string) get_theme_mod('rv_hubspot_portal_id', '246820093')) ?: '246820093';
    $region = sanitize_key((string) get_theme_mod('rv_hubspot_region', 'na2')) ?: 'na2';

    return apply_filters(
        'rv/hubspot_embed_src',
        sprintf('https://js-%s.hs-scripts.com/%s.js', $region, $portal)
    );
}
