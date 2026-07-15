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
    wp_die(__('Error locating autoloader. Please run <code>composer install</code>.', 'pressroot'));
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

collect(['setup', 'filters', 'theme-supports', 'icons', 'customizer', 'contact', 'theme-options', 'menu', 'footer-content', 'dark-mode', 'blocks', 'reading', 'nav-options', 'header-layout', 'extras', 'social-block', 'settings-io', 'performance', 'patterns-extra', 'blocks-dynamic', 'announcement', 'header-behaviors', 'menu-icons', 'typography', 'seo', 'integrations', 'whitelabel', 'fonts-local', 'google-fonts-collection', 'code-highlight', 'sections-library', 'critical-css', 'header-elements', 'bar-blocks', 'hero', 'hero-image', 'customizer-cleanup', 'social-links', 'social-icon-style', 'quick-setup', 'theme-addons', 'ai-assistant', 'ai-connectors', 'ai-content-block', 'ai-builder', 'site-type-agency', 'site-type-freelance', 'site-type-saas', 'site-type-blog', 'site-type-marketing', 'site-type-affiliate', 'site-type-restaurant', 'site-type-realty', 'site-type-remix', 'blocks-bespoke', 'block-section', 'block-patterns', 'home-patterns', 'page-patterns', 'seed-pages', 'pattern-library', 'hooks-registry', 'cli', 'dev-mode', 'repofolio-addon', 'bookings-addon', 'design-presets', 'setup-wizard', 'theme-settings-tab', 'support-settings', 'pressroot-settings'])
    ->each(function ($file) {
        if (! locate_template($file = "app/{$file}.php", true, true)) {
            wp_die(
                /* translators: %s is replaced with the relative file path */
                sprintf(__('Error locating <code>%s</code> for inclusion.', 'pressroot'), $file)
            );
        }
    });
