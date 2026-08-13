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

    return "@font-face{font-family:'Outfit';font-style:normal;font-weight:100 900;font-display:swap;src:url('{$u}/Outfit-Variable.woff2') format('woff2');}"
        . "@font-face{font-family:'Instrument Serif';font-style:normal;font-weight:400;font-display:swap;src:url('{$u}/InstrumentSerif-Regular.woff2') format('woff2');}"
        . "@font-face{font-family:'Instrument Serif';font-style:italic;font-weight:400;font-display:swap;src:url('{$u}/InstrumentSerif-Italic.woff2') format('woff2');}"
        . "@font-face{font-family:'JetBrains Mono';font-style:normal;font-weight:400 600;font-display:swap;src:url('{$u}/JetBrainsMono-Variable.woff2') format('woff2');}";
}

add_action('wp_enqueue_scripts', function () {
    if (is_singular() && comments_open() && get_option('thread_comments')) {
        wp_enqueue_script('comment-reply');
    }
}, 100);

/**
 * Bundled enhancement styles.
 *
 * These rules were authored in the Customizer during design and are shipped as a
 * static theme file (assets/rv-enhancements.css) so the full design travels with
 * the theme — independent of the Vite build and of the site database. Loaded
 * after the compiled stylesheet so its component styles apply as intended.
 */
add_action('wp_enqueue_scripts', function () {
    $rel  = 'assets/rv-enhancements.css';
    $path = get_theme_file_path($rel);

    wp_enqueue_style(
        'rv-enhancements',
        get_theme_file_uri($rel),
        [],
        file_exists($path) ? (string) filemtime($path) : '1.0.0'
    );
}, 20);

/**
 * Emit @font-face + preload the primary font, before the stylesheet.
 */
add_action('wp_head', function () {
    printf(
        '<link rel="preload" as="font" type="font/woff2" crossorigin href="%s/Outfit-Variable.woff2">' . "\n",
        esc_url(get_theme_file_uri('public/fonts'))
    );
    echo '<style id="rv-fonts">' . rv_font_face_css() . "</style>\n"; // phpcs:ignore
}, 1);

/**
 * Performance: preconnect to the image CDNs used by hotlinked hero imagery, and
 * preload the page's Largest Contentful Paint (LCP) image so it starts loading
 * immediately instead of being discovered late. This is the main lever for a
 * top PageSpeed / Core Web Vitals score on image-led pages.
 */
add_action('wp_head', function () {
    echo '<link rel="preconnect" href="https://images.pexels.com">' . "\n";
    echo '<link rel="preconnect" href="https://upload.wikimedia.org">' . "\n";
    echo '<link rel="dns-prefetch" href="https://commons.wikimedia.org">' . "\n";

    $lcp = '';
    if (is_singular('post')) {
        $lcp = has_post_thumbnail() ? (string) get_the_post_thumbnail_url(null, 'rv-hero') : blog_post_image();
    } elseif (is_front_page()) {
        $lcp = hero_bg_url(stock_image('hero-home'));
    } elseif (is_home()) {
        $lcp = hero_bg_url(stock_image('process'), (int) get_option('page_for_posts'));
    } elseif (is_page()) {
        // Page heroes are Featured-Image-driven; preload it when one is set.
        $lcp = hero_bg_url();
    }

    if ($lcp) {
        printf('<link rel="preload" as="image" href="%s" fetchpriority="high">' . "\n", esc_url($lcp));
    }
}, 2);

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
 * Load all core block assets (we style them globally).
 */
add_filter('should_load_separate_core_block_assets', '__return_false');

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
    add_image_size('rv-hero', 1600, 900, true);
}, 20);

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

    return "@font-face{font-family:'Young Serif';font-style:normal;font-weight:400;font-display:swap;src:url('{$u}/YoungSerif-Regular.woff2') format('woff2');}"
        . "@font-face{font-family:'Geist Mono';font-style:normal;font-weight:400;font-display:swap;src:url('{$u}/GeistMono-Regular.woff2') format('woff2');}"
        . "@font-face{font-family:'Geist Mono';font-style:normal;font-weight:500 700;font-display:swap;src:url('{$u}/GeistMono-Bold.woff2') format('woff2');}";
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
    printf(
        '<link rel="preload" as="font" type="font/woff2" crossorigin href="%s/YoungSerif-Regular.woff2">' . "\n",
        esc_url(get_theme_file_uri('public/fonts'))
    );
}, 1);

add_action('wp_head', function () {
    echo '<style id="rv-contour-brand">' . contour_css() . "</style>\n"; // phpcs:ignore
}, 20);

add_filter('block_editor_settings_all', function ($settings) {
    $settings['styles'][] = ['css' => contour_font_face()];
    return $settings;
});
