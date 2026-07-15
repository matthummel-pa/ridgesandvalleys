<?php

/**
 * Repofolio — GitHub portfolio addon, packaged inside the theme.
 *
 * This is the Repofolio plugin (github.com/matthummel-pa/repofolio) folded
 * back into Pressroot as an optional Theme Addon (see app/theme-addons.php).
 * The plugin's classes live verbatim-ish under app/Repofolio/includes/ (same
 * Repofolio\ namespace, same option keys, same block name), booted here in
 * "theme mode" (REPOFOLIO_THEME_MODE) which makes exactly three differences:
 *
 *   1. Settings render as the "GitHub" tab on Appearance -> Pressroot
 *      (app/pressroot-settings.php) instead of a standalone options page —
 *      Settings::page_url() follows, so OAuth/flush redirects land there.
 *   2. Assets are served from the theme URI (REPOFOLIO_URL below).
 *   3. Plugin-only wiring (plugins-list action link, plugin textdomain)
 *      is skipped.
 *
 * If the standalone Repofolio PLUGIN is active, the theme addon steps aside
 * entirely — the plugin wins, so a site can migrate either direction without
 * duplicate CPTs/blocks/settings. Option keys are identical either way, so
 * settings survive switching between plugin and addon.
 *
 * Also restores the two compat seams the theme kept calling after the
 * original GitHub subsystem was extracted (see note in app/setup.php):
 * prt_github_get() below and the App\Github facade (app/Github.php).
 */

namespace App;

use Repofolio\GitHub_Client;
use Repofolio\OAuth;
use Repofolio\Settings;

/** Whether the theme-addon build of Repofolio is booted (not the plugin). */
function prt_repofolio_active(): bool
{
    return defined('REPOFOLIO_THEME_MODE') && REPOFOLIO_THEME_MODE;
}

/** Whether Repofolio is available at all — as theme addon OR standalone plugin. */
function prt_repofolio_available(): bool
{
    return class_exists('\\Repofolio\\Settings');
}

/*
 * Boot — skipped when the standalone plugin is active (it defines
 * REPOFOLIO_VERSION at include time, before theme files load) or when the
 * owner switched the addon off in Customizer -> Theme Options -> Theme Addons.
 */
if (! defined('REPOFOLIO_VERSION') && prt_addon_enabled('repofolio')) {
    define('REPOFOLIO_VERSION', '1.0.0');
    define('REPOFOLIO_THEME_MODE', true);
    define('REPOFOLIO_DIR', trailingslashit(get_theme_file_path('app/Repofolio')));
    define('REPOFOLIO_URL', trailingslashit(get_theme_file_uri('app/Repofolio')));
    define('REPOFOLIO_BASENAME', 'repofolio/repofolio.php'); // Unused in theme mode; defined for safety.

    require_once REPOFOLIO_DIR . 'includes/helpers.php';
    require_once REPOFOLIO_DIR . 'includes/class-github-client.php';
    require_once REPOFOLIO_DIR . 'includes/class-oauth.php';
    require_once REPOFOLIO_DIR . 'includes/class-settings.php';
    require_once REPOFOLIO_DIR . 'includes/class-block.php';
    require_once REPOFOLIO_DIR . 'includes/class-projects.php';
    require_once REPOFOLIO_DIR . 'includes/class-plugin.php';

    (function () {
        $plugin = new \Repofolio\Plugin();
        $plugin->boot();
        $GLOBALS['prt_repofolio'] = $plugin;
    })();

    // First boot as an addon: seed the same defaults the plugin's activation
    // hook would have written, so Settings::get() has a complete option array.
    add_action('after_setup_theme', function () {
        if (get_option(Settings::OPTION_KEY) === false) {
            update_option(Settings::OPTION_KEY, Settings::default_options());
        }
    });
}

/*
 * ---------------------------------------------------------------------------
 * Compat seams (defined regardless of addon state so callers never fatal).
 * ---------------------------------------------------------------------------
 */

/**
 * Old GitHub-tab settings getter (app/github-settings.php, removed when the
 * subsystem was extracted) — still called by app/support-settings.php. Maps
 * the two surviving keys onto Repofolio's options:
 *
 *   prt_proj_owner       -> default repo owner (Repofolio source login,
 *                           falling back to the connected account's login)
 *   prt_proj_cache_hours -> Repofolio's fixed 6-hour response cache
 *
 * @param string $key Old option key.
 * @return mixed
 */
function prt_github_get(string $key)
{
    switch ($key) {
        case 'prt_proj_owner':
            if (prt_repofolio_available()) {
                $o = Settings::get();
                if (! empty($o['source_login'])) {
                    return (string) $o['source_login'];
                }
                $viewer = OAuth::viewer();
                if (! empty($viewer['login'])) {
                    return (string) $viewer['login'];
                }
            }
            return 'matthummel-pa';

        case 'prt_proj_cache_hours':
            return prt_repofolio_available()
                ? (int) (GitHub_Client::CACHE_TTL / HOUR_IN_SECONDS)
                : 6;
    }

    return '';
}

/**
 * "GitHub" tab on Appearance -> Pressroot (see prt_settings_tabs() in
 * app/pressroot-settings.php). Renders Repofolio's full settings screen —
 * OAuth connect, data source, display toggles, cache flush — without page
 * chrome (Settings::render() drops its own <h1>/.wrap in theme mode).
 */
function prt_repofolio_tab_html(): void
{
    if (! current_user_can('manage_options')) {
        return;
    }
    if (defined('REPOFOLIO_VERSION') && ! prt_repofolio_active()) {
        // Standalone plugin is active — point at its own settings page.
        printf(
            '<p>%s <a href="%s">%s</a></p>',
            esc_html__('The Repofolio plugin is active, so its settings live on its own page:', 'pressroot'),
            esc_url(Settings::page_url()),
            esc_html__('Settings → Repofolio', 'pressroot')
        );
        return;
    }
    if (! prt_repofolio_active() || empty($GLOBALS['prt_repofolio'])) {
        esc_html_e('The Repofolio addon is switched off — enable it under Customizer → Theme Options → Theme Addons.', 'pressroot');
        return;
    }
    $GLOBALS['prt_repofolio']->settings->render();
}

// The facade class isn't PSR-4 discovered when composer's autoloader was
// dumped optimized, so require it explicitly.
require_once __DIR__ . '/Github.php';
