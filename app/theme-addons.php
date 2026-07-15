<?php

/**
 * Theme Addons — optional feature modules the owner can turn off entirely.
 *
 * Most of this theme is "always on" (layout, top bar, blocks, patterns), but
 * a few larger feature areas are better framed as opt-in add-ons rather than
 * permanent parts of the theme — starting with "Pressroot AI" (the renamed
 * AI Setup Assistant + AI Connectors settings + the block editor's "Generate
 * with AI" button; see app/ai-assistant.php, app/ai-connectors.php,
 * app/ai-content-block.php). Exposed as a checkbox in the Customizer's
 * existing "Theme Options" panel (Theme Addons section) rather than a new
 * admin page, matching this theme's convention of visual/feature toggles
 * living in the Customizer and only secrets/integration config (API keys)
 * living on plain admin pages — see the file comment in
 * app/github-settings.php for that split.
 *
 * Any future optional feature area should add itself to prt_addon_defaults()
 * and check prt_addon_enabled('slug') before registering its admin pages,
 * enqueuing its scripts, or responding to its AJAX/admin-post actions —
 * exactly like the three ai-*.php files do for 'pressroot_ai'.
 */

namespace App;

/** Default on/off state per addon slug. Defaults to enabled so installs that
 *  already had Pressroot AI working don't lose it silently on update. */
function prt_addon_defaults(): array
{
    return apply_filters('pressroot/addon_defaults', [
        'pressroot_ai' => true,
        // Repofolio — the GitHub portfolio subsystem (repo grid block, OAuth
        // connect, repofolio_project CPT), packaged back into the theme from
        // the standalone plugin. See app/repofolio-addon.php. Defaults on so
        // sites that relied on the plugin's features keep them when the
        // plugin is deactivated in favor of the addon.
        'repofolio'    => true,
        // Pressroots Reserve — the bookings & reservations subsystem
        // (services CPT, availability engine, booking widget, admin calendar).
        // See app/bookings-addon.php. Declared here rather than via the
        // pressroot/addon_defaults filter so prt_addon_enabled('bookings') is
        // already true at the top of bookings-addon.php's boot check on the
        // very first request (the filter would register too late for that).
        'bookings'     => true,
    ]);
}

/** Whether a given addon is currently enabled. Unknown slugs are treated as
 *  disabled (safer default than silently enabling something unregistered). */
function prt_addon_enabled(string $slug): bool
{
    $defaults = prt_addon_defaults();
    if (! array_key_exists($slug, $defaults)) {
        return false;
    }
    return (bool) get_theme_mod('prt_addon_' . $slug . '_enabled', $defaults[$slug]);
}

/**
 * "Powered by AI — or not." One plain-language switch (Brand tab on
 * Appearance -> Pressroot, saved as prt_ai_features_on) that turns off every
 * feature that actually CALLS an AI service: the starter-copy generator, AI
 * Connectors, the block editor's "Generate with AI" button, and the AI image
 * tab in the Customizer image finder. The Site Types design generator keeps
 * working with AI off — its patterns and design kits are built into the
 * theme and never phone anywhere.
 *
 * Distinct from the Pressroot AI ADDON toggle above (which removes the whole
 * Site Types module): this is the friendly, non-technical "no AI on my site,
 * please" switch. Both must be on for AI calls to run.
 */
function prt_ai_features_enabled(): bool
{
    return prt_addon_enabled('pressroot_ai') && (bool) get_theme_mod('prt_ai_features_on', true);
}

/** Customizer: Theme Options -> Theme Addons. */
add_action('customize_register', function ($wp) {
    // Shared guarded helper — see prt_ensure_theme_options_panel() in app/customizer.php.
    prt_ensure_theme_options_panel($wp);

    $wp->add_section('prt_addons_section', [
        'title'       => __('Theme Addons', 'pressroot'),
        'panel'       => 'prt_theme_options',
        'description' => __('Optional feature modules — turn any of these off if you don\'t want them cluttering your admin menu.', 'pressroot'),
    ]);

    $wp->add_setting('prt_addon_pressroot_ai_enabled', [
        'default'           => prt_addon_defaults()['pressroot_ai'],
        'sanitize_callback' => 'wp_validate_boolean',
    ]);
    $wp->add_control('prt_addon_pressroot_ai_enabled', [
        'label'       => __('Enable Pressroot AI', 'pressroot'),
        'description' => __('The Pressroot AI setup screen, AI Connectors settings, and the block editor\'s "Generate with AI" button.', 'pressroot'),
        'section'     => 'prt_addons_section',
        'type'        => 'checkbox',
    ]);

    $wp->add_setting('prt_addon_repofolio_enabled', [
        'default'           => prt_addon_defaults()['repofolio'],
        'sanitize_callback' => 'wp_validate_boolean',
    ]);
    $wp->add_control('prt_addon_repofolio_enabled', [
        'label'       => __('Enable Repofolio (GitHub portfolio)', 'pressroot'),
        'description' => __('The GitHub repo grid block, "Connect with GitHub" OAuth, project case-study post type, and the GitHub tab on Appearance → Pressroot. Ignored while the standalone Repofolio plugin is active — the plugin takes over. Changes apply after publishing (the addon loads at boot).', 'pressroot'),
        'section'     => 'prt_addons_section',
        'type'        => 'checkbox',
    ]);
}, 22);
