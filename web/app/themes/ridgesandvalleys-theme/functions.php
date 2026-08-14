<?php

use App\Providers\ThemeServiceProvider;
use Roots\Acorn\Application;

/*
|--------------------------------------------------------------------------
| Register The Auto Loader
|--------------------------------------------------------------------------
|
| Composer provides a convenient, automatically generated class loader for
| our theme. We will simply require it into the script here so that we
| don't have to worry about manually loading any of our classes later on.
|
*/

if (! file_exists($composer = __DIR__.'/vendor/autoload.php')) {
    wp_die(__('Error locating autoloader. Please run <code>composer install</code>.', 'sage'));
}

require $composer;

/*
|--------------------------------------------------------------------------
| Register The Bootloader
|--------------------------------------------------------------------------
|
| The first thing we will do is schedule a new Acorn application container
| to boot when WordPress is finished loading the theme. The application
| serves as the "glue" for all the components of Laravel and is
| the IoC container for the system binding all of the various parts.
|
*/

Application::configure()
    ->withProviders([
        ThemeServiceProvider::class,
    ])
    ->boot();

/*
|--------------------------------------------------------------------------
| Register Sage Theme Files
|--------------------------------------------------------------------------
|
| Out of the box, Sage ships with categorically named theme files
| containing common functionality and setup to be bootstrapped with your
| theme. Simply add (or remove) files from the array below to change what
| is registered alongside Sage.
|
*/

collect(['setup', 'filters', 'helpers', 'projects', 'contact', 'customizer', 'custom-code', 'integrations', 'blocks', 'tools', 'grader', 'seo-checker', 'security-checker', 'email-checker', 'local-checker', 'page-fields', 'post-fields', 'layout', 'hero-style', 'hero-size', 'hero-credit', 'home-sections', 'ai-edit', 'live-preview', 'seo', 'seo-meta', 'performance', 'github', 'theme-updater', 'google-photos', 'open-images', 'starter'])
    ->each(function ($file) {
        if (! locate_template($file = "app/{$file}.php", true, true)) {
            wp_die(
                /* translators: %s is replaced with the relative file path */
                sprintf(__('Error locating <code>%s</code> for inclusion.', 'sage'), $file)
            );
        }
    });

/*
|--------------------------------------------------------------------------
| Page content sections (added)
|--------------------------------------------------------------------------
|
| Styles the editor-authored content sections on the interior pages (Services,
| Work, About, FAQ, Free Tools, and any default-template page) to match the
| intro band under the home-page hero, and adds a gentle reveal-on-scroll.
| Ships as static theme files (assets/rv-page-sections.css/.js) that travel
| with the theme, independent of the Vite build. Uses only global WordPress
| functions, so it is safe to register here in the (global-namespace)
| functions.php.
|
*/

add_action('wp_enqueue_scripts', function () {
    if (is_front_page() || is_home()) {
        return;
    }
    if (! is_page() && ! is_singular(['post', 'project'])) {
        return;
    }

    $cssRel  = 'assets/rv-page-sections.css';
    $cssPath = get_theme_file_path($cssRel);
    wp_enqueue_style(
        'rv-page-sections',
        get_theme_file_uri($cssRel),
        ['rv-enhancements'],
        file_exists($cssPath) ? (string) filemtime($cssPath) : '1.0.0'
    );

    $jsRel  = 'assets/rv-page-sections.js';
    $jsPath = get_theme_file_path($jsRel);
    wp_enqueue_script(
        'rv-page-sections',
        get_theme_file_uri($jsRel),
        [],
        file_exists($jsPath) ? (string) filemtime($jsPath) : '1.0.0',
        true
    );
}, 21);

add_action('wp_head', function () {
    echo "<script>document.documentElement.classList.add('rv-js');</script>\n"; // phpcs:ignore
}, 0);

