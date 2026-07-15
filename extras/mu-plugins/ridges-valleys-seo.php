<?php
/**
 * Plugin Name:  Ridges & Valleys — SEO, Performance & Accessibility
 * Description:  Local-business schema, sensible meta/OpenGraph defaults, performance trims, and a11y helpers for ridgesandvalleys.com. Drop into wp-content/mu-plugins/ (auto-loads). Safe alongside an SEO plugin — disable overlapping meta output if you run Yoast/Rank Math/SEOPress.
 * Author:       Matt Hummel — Ridges & Valleys Studio
 * Version:      0.1.0
 * License:      MIT
 *
 * NOTE: This is intentionally framework-agnostic and standalone so it works whether or not
 * a dedicated SEO plugin is active. If you install Rank Math / Yoast, set
 * define('RV_SEO_META', false); in wp-config.php to hand meta/OG output to the plugin,
 * and keep only the LocalBusiness schema + performance/a11y bits here.
 */

if (!defined('ABSPATH')) { exit; }

if (!defined('RV_SEO_META')) { define('RV_SEO_META', true); } // set false when using Yoast/Rank Math

/* ------------------------------------------------------------------ *
 * 1. Business identity — edit these to taste
 * ------------------------------------------------------------------ */
function rv_business() {
    return [
        'name'      => 'Ridges & Valleys Studio',
        'url'       => 'https://ridgesandvalleys.com',
        'email'     => 'hello@ridgesandvalleys.com',
        'tagline'   => 'Fast, accessible WordPress websites for Gettysburg & South Central PA.',
        'city'      => 'Gettysburg',
        'region'    => 'PA',
        'country'   => 'US',
        'areas'     => ['Gettysburg', 'Adams County', 'Cumberland Valley', 'South Central Pennsylvania'],
        'sameAs'    => [
            // add real profiles as they go live
            'https://www.instagram.com/ridgesandvalleys',
            'https://www.facebook.com/ridgesandvalleys',
            'https://github.com/matthummel-pa',
        ],
        'founder'   => 'Matt Hummel',
    ];
}

/* ------------------------------------------------------------------ *
 * 2. LocalBusiness (ProfessionalService) JSON-LD  — great for local SEO
 * ------------------------------------------------------------------ */
add_action('wp_head', function () {
    $b = rv_business();
    $schema = [
        '@context' => 'https://schema.org',
        '@type'    => 'ProfessionalService',
        '@id'      => $b['url'] . '/#studio',
        'name'     => $b['name'],
        'url'      => $b['url'],
        'email'    => $b['email'],
        'description' => $b['tagline'],
        'areaServed'  => array_map(fn($a) => ['@type' => 'AdministrativeArea', 'name' => $a], $b['areas']),
        'address'  => [
            '@type' => 'PostalAddress',
            'addressLocality' => $b['city'],
            'addressRegion'   => $b['region'],
            'addressCountry'  => $b['country'],
        ],
        'founder'  => ['@type' => 'Person', 'name' => $b['founder']],
        'sameAs'   => array_values($b['sameAs']),
        'priceRange' => '$$',
        'knowsAbout' => ['WordPress', 'Web design', 'Local SEO', 'Web accessibility'],
    ];
    echo "\n<script type=\"application/ld+json\">" . wp_json_encode($schema, JSON_UNESCAPED_SLASHES | JSON_UNESCAPED_UNICODE) . "</script>\n";
}, 5);

/* ------------------------------------------------------------------ *
 * 3. Meta description + Open Graph + Twitter (skip if an SEO plugin owns this)
 * ------------------------------------------------------------------ */
add_action('wp_head', function () {
    if (!RV_SEO_META) { return; }
    $b = rv_business();

    if (is_singular()) {
        $desc = has_excerpt() ? get_the_excerpt() : wp_trim_words(wp_strip_all_tags(get_post_field('post_content', get_queried_object_id())), 30, '…');
        $title = get_the_title();
        $url   = get_permalink();
        $img   = get_the_post_thumbnail_url(null, 'large');
    } else {
        $desc  = $b['tagline'];
        $title = wp_get_document_title();
        $url   = home_url(add_query_arg([], $GLOBALS['wp']->request ?? ''));
        $img   = '';
    }
    $desc = trim($desc) ?: $b['tagline'];

    printf("<meta name=\"description\" content=\"%s\">\n", esc_attr($desc));
    printf("<meta property=\"og:site_name\" content=\"%s\">\n", esc_attr($b['name']));
    printf("<meta property=\"og:type\" content=\"%s\">\n", is_singular() ? 'article' : 'website');
    printf("<meta property=\"og:title\" content=\"%s\">\n", esc_attr($title));
    printf("<meta property=\"og:description\" content=\"%s\">\n", esc_attr($desc));
    printf("<meta property=\"og:url\" content=\"%s\">\n", esc_url($url));
    if ($img) { printf("<meta property=\"og:image\" content=\"%s\">\n", esc_url($img)); }
    printf("<meta name=\"twitter:card\" content=\"%s\">\n", $img ? 'summary_large_image' : 'summary');
    printf("<meta name=\"twitter:title\" content=\"%s\">\n", esc_attr($title));
    printf("<meta name=\"twitter:description\" content=\"%s\">\n", esc_attr($desc));
    if ($img) { printf("<meta name=\"twitter:image\" content=\"%s\">\n", esc_url($img)); }
    printf("<meta name=\"theme-color\" content=\"%s\">\n", '#2E5245');
}, 6);

// Canonical for singular (WP adds rel_canonical already; keep for safety when SEO plugin absent)
add_action('wp_head', function () {
    if (RV_SEO_META && is_singular()) { printf("<link rel=\"canonical\" href=\"%s\">\n", esc_url(get_permalink())); }
}, 7);

/* ------------------------------------------------------------------ *
 * 4. Performance trims (safe, reversible)
 * ------------------------------------------------------------------ */
// Remove emoji detection scripts/styles (saves a request + JS)
add_action('init', function () {
    remove_action('wp_head', 'print_emoji_detection_script', 7);
    remove_action('wp_print_styles', 'print_emoji_styles');
    remove_action('admin_print_scripts', 'print_emoji_detection_script');
    remove_action('admin_print_styles', 'print_emoji_styles');
    add_filter('emoji_svg_url', '__return_false');
});
// Drop the pointless XML-RPC pingback header / RSD / wlwmanifest links
add_filter('wp_headers', function ($h) { unset($h['X-Pingback']); return $h; });
remove_action('wp_head', 'rsd_link');
remove_action('wp_head', 'wlwmanifest_link');
remove_action('wp_head', 'wp_generator');
// Preload the display font so the H1 doesn't flash (self-hosted in the theme)
add_action('wp_head', function () {
    $u = get_stylesheet_directory_uri() . '/resources/fonts/Outfit-Variable.woff2';
    printf("<link rel=\"preload\" href=\"%s\" as=\"font\" type=\"font/woff2\" crossorigin>\n", esc_url($u));
}, 1);
// Add rel=preconnect (harmless even with self-hosted fonts; helps any 3rd-party)
add_filter('wp_resource_hints', function ($hints, $rel) {
    if ('dns-prefetch' === $rel) { $hints[] = 'https://www.google-analytics.com'; }
    return $hints;
}, 10, 2);

/* ------------------------------------------------------------------ *
 * 5. Accessibility helpers
 * ------------------------------------------------------------------ */
// Ensure a visible focus outline everywhere (belt-and-suspenders over theme CSS)
add_action('wp_head', function () {
    echo "<style id=\"rv-a11y\">:focus-visible{outline:3px solid #E0A73C;outline-offset:2px;} .skip-link:focus{position:fixed;left:8px;top:8px;z-index:100000;background:#23201B;color:#F7F1E6;padding:10px 14px;border-radius:8px;}</style>\n";
}, 20);
// Add a skip link if the theme doesn't already output one
add_action('wp_body_open', function () {
    echo '<a class="skip-link screen-reader-text" href="#main">' . esc_html__('Skip to content', 'pressroot') . '</a>';
}, 1);
