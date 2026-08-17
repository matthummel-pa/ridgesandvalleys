<?php

/**
 * Theme setup — Ridges & Valleys Studio (Sage 11 base).
 */

namespace App;

use Illuminate\Support\Facades\Vite;

/**
 * Self-hosted brand fonts (Outfit, Instrument Serif, JetBrains Mono).
 * Files live in public/fonts/ and are referenced via the theme URI, so the URLs
 * are correct under both Bedrock (/app/themes/…) and vanilla WP (/wp-content/…).
 */
function rv_font_face_css(): string
{
    $u = get_theme_file_uri('public/fonts');

    return "@font-face{font-family:'Outfit';font-style:normal;font-weight:100 900;font-display:optional;src:url('{$u}/Outfit-Variable.woff2') format('woff2');}";
}

add_action('wp_enqueue_scripts', function () {
    if (is_singular() && comments_open() && get_option('thread_comments')) {
        wp_enqueue_script('comment-reply');
    }
}, 100);

/**
 * Emit @font-face for the UI font. Display/heading faces live in contour_css().
 * font-display:optional avoids a late swap (forced reflow / LCP text delay).
 */
add_action('wp_head', function () {
    echo '<style id="rv-fonts">' . rv_font_face_css() . "</style>\n"; // phpcs:ignore
}, 1);

/**
 * Site icons / favicons.
 *
 * Bundled in public/favicons and emitted here as a complete set (scalable SVG,
 * Apple + Android/PWA icons, .ico fallback, web manifest, brand theme-color).
 * If a Site Icon is set in Appearance > Customize > Site Identity, WordPress's
 * own output is used instead (and this bundled set steps aside), so the favicon
 * stays editable without touching theme files.
 */
add_action('wp_head', function () {
    if (has_site_icon()) {
        return;
    }
    $u = get_theme_file_uri('public/favicons');
    printf('<link rel="icon" href="%s/favicon.ico" sizes="any">' . "\n", esc_url($u));
    printf('<link rel="icon" type="image/svg+xml" href="%s/favicon.svg">' . "\n", esc_url($u));
    printf('<link rel="icon" type="image/png" sizes="32x32" href="%s/favicon-32.png">' . "\n", esc_url($u));
    printf('<link rel="icon" type="image/png" sizes="16x16" href="%s/favicon-16.png">' . "\n", esc_url($u));
    printf('<link rel="apple-touch-icon" href="%s/apple-touch-icon.png">' . "\n", esc_url($u));
    printf('<link rel="manifest" href="%s/site.webmanifest">' . "\n", esc_url($u));
    echo '<meta name="theme-color" content="#2E5245">' . "\n";
}, 3);

/**
 * Load the same self-hosted fonts inside the block editor.
 */
add_filter('block_editor_settings_all', function ($settings) {
    $style = Vite::asset('resources/css/editor.css');
    $settings['styles'][] = ['css' => rv_font_face_css()];
    $settings['styles'][] = ['css' => "@import url('{$style}')"];
    return $settings;
});

/**
 * Inject the built editor script (registers custom blocks) into the editor.
 */
add_action('admin_head', function () {
    if (! get_current_screen()?->is_block_editor()) {
        return;
    }

    if (! Vite::isRunningHot()) {
        $dependencies = json_decode(Vite::content('editor.deps.json'));

        foreach ($dependencies as $dependency) {
            if (! wp_script_is($dependency)) {
                wp_enqueue_script($dependency);
            }
        }
    }

    echo Vite::withEntryPoints(['resources/js/editor.ts'])->toHtml();
});

/**
 * Use the generated theme.json (Tailwind tokens merged into the base).
 */
add_filter('theme_file_path', function ($path, $file) {
    return $file === 'theme.json'
        ? public_path('build/assets/theme.json')
        : $path;
}, 10, 2);

/**
 * Load CSS only for core blocks actually present on the page.
 */
add_filter('should_load_separate_core_block_assets', '__return_true');

/**
 * Theme supports, menus, image sizes.
 */
add_action('after_setup_theme', function () {
    remove_theme_support('block-templates');

    register_nav_menus([
        'primary' => __('Primary Navigation', 'sage'),
        'footer'  => __('Footer Navigation', 'sage'),
        'tools'   => __('Tools Menu (footer)', 'sage'),
    ]);

    add_theme_support('title-tag');
    add_theme_support('post-thumbnails');
    add_theme_support('responsive-embeds');
    add_theme_support('align-wide');
    add_theme_support('wp-block-styles');
    add_theme_support('customize-selective-refresh-widgets');
    add_theme_support('custom-logo', [
        'height'      => 96,
        'width'       => 96,
        'flex-height' => true,
        'flex-width'  => true,
    ]);
    add_theme_support('html5', [
        'caption', 'comment-form', 'comment-list', 'gallery', 'search-form', 'script', 'style', 'navigation-widgets',
    ]);

    add_image_size('rv-card', 720, 480, true);
    add_image_size('rv-hero-md', 960, 540, true);
    add_image_size('rv-hero', 1600, 900, true);
}, 20);

/**
 * Keep fetchpriority=high on the hero photo, not the header logo (first <img>).
 */
add_filter('wp_get_loading_optimization_attributes', function ($loading_attrs, $tag_name, $attr) {
    if ($tag_name !== 'img' || ! is_array($attr)) {
        return $loading_attrs;
    }
    $class = (string) ($attr['class'] ?? '');
    if (str_contains($class, 'rv-logo')) {
        $loading_attrs['fetchpriority'] = 'low';
    }
    if (str_contains($class, 'rv-hero-photo')) {
        $loading_attrs['fetchpriority'] = 'high';
    }

    return $loading_attrs;
}, 10, 3);

/**
 * Content width.
 */
add_action('after_setup_theme', function () {
    $GLOBALS['content_width'] = 760;
}, 0);

/**
 * Widget areas.
 */
add_action('widgets_init', function () {
    $config = [
        'before_widget' => '<section class="widget %1$s %2$s">',
        'after_widget'  => '</section>',
        'before_title'  => '<h3 class="widget-title">',
        'after_title'   => '</h3>',
    ];

    register_sidebar([
        'name' => __('Blog Sidebar', 'sage'),
        'id'   => 'sidebar-primary',
    ] + $config);

    register_sidebar([
        'name' => __('Footer', 'sage'),
        'id'   => 'sidebar-footer',
    ] + $config);
});

/**
 * Hide the WordPress toolbar on the public site. Logged-in editors then see
 * the same header and spacing as visitors. The toolbar still shows in wp-admin.
 */
add_filter('show_admin_bar', function ($show) {
    if (is_admin()) {
        return $show;
    }

    return false;
});

/**
 * Body classes for layout state.
 */
add_filter('body_class', function ($classes) {
    if (! is_active_sidebar('sidebar-primary')) {
        $classes[] = 'no-sidebar';
    }
    if (! get_theme_mod('rv_header_sticky', true)) {
        $classes[] = 'rv-nav-static';
    }
    // Transparent header only where a full-bleed hero exists at the top
    // (home, pages, Journal index, and journal posts that render .rv-entry-hero).
    $overlayHero = is_front_page() || is_page() || is_home()
        || (is_singular('post') && entry_hero_enabled());
    if (get_theme_mod('rv_header_transparent', false) && $overlayHero) {
        $classes[] = 'rv-nav-transparent';
    }
    if (get_theme_mod('rv_topbar_hide_on_scroll', false)) {
        $classes[] = 'rv-topbar-hide';
    }
    return $classes;
});

/**
 * Contour brand system.
 *
 * Applies the "Contour" identity site-wide: Young Serif for headings, Geist Mono
 * for eyebrows/labels, and a subtle topographic contour-line motif behind the
 * key section bands (homepage intro, footer CTA, footer) so the ridges-&-valleys
 * theme runs throughout the site. Fonts + topo art self-hosted in public/fonts/.
 */
function contour_font_face(): string
{
    $u = get_theme_file_uri('public/fonts');

    return "@font-face{font-family:'Young Serif';font-style:normal;font-weight:400;font-display:optional;src:url('{$u}/YoungSerif-Regular.woff2') format('woff2');}"
        . "@font-face{font-family:'Geist Mono';font-style:normal;font-weight:400;font-display:optional;src:url('{$u}/GeistMono-Regular.woff2') format('woff2');}"
        . "@font-face{font-family:'Geist Mono';font-style:normal;font-weight:500 700;font-display:optional;src:url('{$u}/GeistMono-Bold.woff2') format('woff2');}";
}

function contour_css(): string
{
    $u     = get_theme_file_uri('public/fonts');
    $pine  = "{$u}/contour-pine.svg";
    $cream = "{$u}/contour-cream.svg";

    return contour_font_face() . <<<CSS

:root:root{
  --font-display:'Young Serif', Georgia, 'Times New Roman', serif;
  --font-mono:'Geist Mono', ui-monospace, 'SFMono-Regular', Menlo, monospace;
}
h1, h2, h3, h4,
.rv-hero-title, .rv-hero-title em,
.rv-intro-title, .rv-fcta-title,
.rv-section-title, .rv-page-title, .rv-post-title,
.rv-footer-name, .wp-block-heading{
  font-family:var(--font-display) !important;
  font-weight:400 !important;
  letter-spacing:-.005em;
}
.rv-eyebrow, .rv-intro-eyebrow, .rv-topbar-note,
.rv-footer-heading, .rv-hero-note, .rv-copyright{
  font-family:var(--font-mono) !important;
}
.rv-home-intro, .rv-fcta, .rv-footer{ position:relative; isolation:isolate; }
.rv-home-intro::before, .rv-fcta::before, .rv-footer::before{
  content:""; position:absolute; inset:0; z-index:-1; pointer-events:none;
  background-repeat:no-repeat; background-position:center; background-size:cover;
}
.rv-home-intro::before{ background-image:url('{$pine}'); opacity:.06; }
.rv-fcta::before{ background-image:url('{$cream}'); opacity:.09; }
.rv-footer::before{ background-image:url('{$cream}'); opacity:.06; }

CSS;
}

add_action('wp_head', function () {
    echo '<style id="rv-contour-brand">' . contour_css() . "</style>\n"; // phpcs:ignore
}, 20);

add_filter('block_editor_settings_all', function ($settings) {
    $settings['styles'][] = ['css' => contour_font_face()];
    return $settings;
});
