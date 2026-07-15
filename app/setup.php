<?php

/**
 * Theme setup.
 */

namespace App;

use Illuminate\Support\Facades\Vite;

/**
 * Inject styles into the block editor.
 *
 * @return array
 */
add_filter('block_editor_settings_all', function ($settings) {
    $style = Vite::asset('resources/css/editor.css');

    $settings['styles'][] = [
        'css' => "@import url('{$style}')",
    ];

    return $settings;
});

/**
 * Delete every compiled Acorn/Blade view file. Shared by the admin
 * ?prt_view_clear=1 shortcut below and `wp pressroot views clear` (app/cli.php)
 * so there's exactly one place that knows where compiled views live.
 */
function prt_clear_compiled_views(): int
{
    $dirs = [
        WP_CONTENT_DIR . '/uploads/acorn-views',
        WP_CONTENT_DIR . '/cache/acorn/views',
    ];
    if (function_exists('config')) {
        $cfg = config('view.compiled');
        if (is_string($cfg) && $cfg !== '') {
            $dirs[] = $cfg;
        }
    }
    $count = 0;
    foreach (array_unique($dirs) as $dir) {
        foreach ((array) glob(rtrim($dir, '/') . '/*.php') as $file) {
            if (@unlink($file)) {
                $count++;
            }
        }
    }
    return $count;
}

/**
 * Clear Acorn's compiled Blade views on demand: visit any admin URL with
 * ?prt_view_clear=1. Useful when template edits don't show because the compiled
 * views were cached (e.g. `wp acorn view:cache` / production mode).
 */
add_action('admin_init', function () {
    if (empty($_GET['prt_view_clear']) || ! current_user_can('edit_theme_options')) {
        return;
    }
    $count = prt_clear_compiled_views();
    wp_safe_redirect(add_query_arg('prt_views_cleared', $count, admin_url()));
    exit;
});

/**
 * Load the compiled design stylesheet INTO the editor as a real <link>.
 *
 * `enqueue_block_assets` runs inside the iframed editor canvas AND inside the
 * inserter's pattern/block preview iframes, so this is what makes patterns and
 * blocks preview with the full Paper + Space design (CSS variables, fonts,
 * .prt-* helpers, keyframes). The @import editor style above gets scoped to
 * `.editor-styles-wrapper` and mangled, which is why previews looked unstyled.
 * Editor-only: the front end already loads app.css via @vite.
 */
add_action('enqueue_block_assets', function () {
    if (! is_admin()) {
        return;
    }
    wp_enqueue_style(
        'matthummel-editor-design',
        Vite::asset('resources/css/editor.css'),
        [],
        null
    );
});

/**
 * Inject scripts into the block editor.
 *
 * @return void
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
    echo Vite::withEntryPoints([
        'resources/js/editor.js',
    ])->toHtml();
});

/**
 * Use the generated theme.json file.
 *
 * @return string
 */
add_filter('theme_file_path', function ($path, $file) {
    return $file === 'theme.json'
        ? public_path('build/assets/theme.json')
        : $path;
}, 10, 2);

/**
 * NOTE: `should_load_separate_core_block_assets` is intentionally NOT filtered
 * here. It's controlled from one place only — the Customizer-driven filter in
 * app/critical-css.php (setting: prt_split_block_css) — so there's a single
 * source of truth. A hardcoded '__return_false' used to live here too, but
 * since both filters run at the default priority and critical-css.php's
 * callback loads later and ignores the incoming value, this one always won
 * silently and the Customizer toggle never actually took effect. Removed.
 *
 * @link https://core.trac.wordpress.org/ticket/61965
 * @see app/critical-css.php
 */

/**
 * Register the initial theme setup.
 *
 * @return void
 */
add_action('after_setup_theme', function () {
    /**
     * Load the translation catalog. Ships as resources/lang/pressroot.pot
     * (generate/update via `npm run translate:pot`); .mo files for each
     * locale live alongside it.
     */
    load_theme_textdomain('pressroot', get_template_directory() . '/resources/lang');

    /**
     * Disable full-site editing support.
     *
     * @link https://wptavern.com/gutenberg-10-5-embeds-pdfs-adds-verse-block-color-options-and-introduces-new-patterns
     */
    remove_theme_support('block-templates');

    /**
     * Register the navigation menus.
     *
     * @link https://developer.wordpress.org/reference/functions/register_nav_menus/
     */
    register_nav_menus([
        'primary_navigation' => __('Primary Navigation', 'pressroot'),
    ]);

    /**
     * Disable the default block patterns.
     *
     * @link https://developer.wordpress.org/block-editor/developers/themes/theme-support/#disabling-the-default-block-patterns
     */
    remove_theme_support('core-block-patterns');

    /**
     * Enable plugins to manage the document title.
     *
     * @link https://developer.wordpress.org/reference/functions/add_theme_support/#title-tag
     */
    add_theme_support('title-tag');

    /**
     * Enable post thumbnail support.
     *
     * @link https://developer.wordpress.org/themes/functionality/featured-images-post-thumbnails/
     */
    add_theme_support('post-thumbnails');

    /**
     * Enable responsive embed support.
     *
     * @link https://developer.wordpress.org/block-editor/how-to-guides/themes/theme-support/#responsive-embedded-content
     */
    add_theme_support('responsive-embeds');

    /**
     * Enable HTML5 markup support.
     *
     * @link https://developer.wordpress.org/reference/functions/add_theme_support/#html5
     */
    add_theme_support('html5', [
        'caption',
        'comment-form',
        'comment-list',
        'gallery',
        'search-form',
        'script',
        'style',
    ]);

    /**
     * Enable selective refresh for widgets in customizer.
     *
     * @link https://developer.wordpress.org/reference/functions/add_theme_support/#customize-selective-refresh-widgets
     */
    add_theme_support('customize-selective-refresh-widgets');
}, 20);

/**
 * Register the theme sidebars.
 *
 * @return void
 */
add_action('widgets_init', function () {
    $config = [
        'before_widget' => '<section class="widget %1$s %2$s">',
        'after_widget' => '</section>',
        'before_title' => '<h3>',
        'after_title' => '</h3>',
    ];

    register_sidebar([
        'name' => __('Primary', 'pressroot'),
        'id' => 'sidebar-primary',
    ] + $config);

    register_sidebar([
        'name' => __('Footer', 'pressroot'),
        'id' => 'sidebar-footer',
    ] + $config);
});

/**
 * NOTE: Google Fonts used to also be enqueued here as a hardcoded
 * 'matthummel-fonts' stylesheet (Outfit/Instrument Serif/JetBrains Mono,
 * both on wp_enqueue_scripts and admin_enqueue_scripts), completely ignoring
 * the Customizer's font pickers. That duplicated — and silently fought with —
 * the real, Customizer-driven enqueue in app/customizer.php
 * ('matthummel-fonts-custom', built from prt_font_heading/prt_font_body +
 * prt_fonts()), which is the single source of truth for Google Fonts loading
 * now. Removed here to stop double-loading overlapping font families on
 * every front-end page load. See also app/typography.php's 'prt-fonts-extra'
 * (nav/button font mods) and app/fonts-local.php (self-hosted opt-out).
 */

/**
 * NOTE: the "projects" CPT + "project_categories" taxonomy that used to be
 * registered here (with the whole GitHub subsystem: app/Github.php, the
 * GitHub settings tab, OAuth connect, the prt/* GitHub blocks, and the
 * Project Details meta box) moved to the Repofolio plugin as of v1.5.0 —
 * post type `repofolio_project`, taxonomy `repofolio_project_type`, meta
 * `_repofolio_*`. Content belongs in a plugin so it survives theme switches;
 * the theme keeps only the presentation layer (Blade templates for the
 * plugin's post type, which no-op gracefully when the plugin is inactive).
 */
