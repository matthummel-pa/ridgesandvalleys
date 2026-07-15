<?php

/**
 * Social Links — platform metadata (label, icon, default URL).
 *
 * This used to also register its own prt_social_{key} Customizer settings
 * with no controls attached — a duplicate of the real settings + controls
 * registered together in app/menu.php (prt_socials_map(), which now sources
 * its list from prt_social_platforms() below). That duplicate registration
 * is gone; this file now only defines the platform list + the
 * pressroot/social_platforms filter extension point.
 */

namespace App;

/**
 * Platform metadata: label, Blade Icon slug, and default URL, for every
 * social network the theme knows about out of the box. This is the single
 * catalog other modules loop over to build Customizer URL settings/controls
 * (app/menu.php) and to render icons (app/icons.php, prt_social_icon()).
 * Wrapped in the `pressroot/social_platforms` filter so a child theme or
 * plugin can add/remove/reorder platforms without editing this file —
 * important now that this theme is meant to be reused by other developers.
 */
function prt_social_platforms(): array
{
    return apply_filters('pressroot/social_platforms', [
        'github'    => ['label' => 'GitHub',      'icon' => 'si-github',    'default' => 'https://github.com/matthummel-pa'],
        'linkedin'  => ['label' => 'LinkedIn',    'icon' => 'si-linkedin',  'default' => ''],
        'devto'     => ['label' => 'Dev.to',      'icon' => 'si-devdotto',  'default' => 'https://dev.to/mattbuildsapps'],
        'x'         => ['label' => 'X (Twitter)', 'icon' => 'si-x',         'default' => ''],
        'bluesky'   => ['label' => 'Bluesky',     'icon' => 'si-bluesky',   'default' => ''],
        'instagram' => ['label' => 'Instagram',   'icon' => 'si-instagram', 'default' => ''],
        'youtube'   => ['label' => 'YouTube',     'icon' => 'si-youtube',   'default' => ''],
        'facebook'  => ['label' => 'Facebook',    'icon' => 'si-facebook',  'default' => ''],
        'mastodon'  => ['label' => 'Mastodon',    'icon' => 'si-mastodon',  'default' => ''],
        'email'     => ['label' => 'Email',       'icon' => 'si-mail',      'default' => ''],
        'rss'       => ['label' => 'RSS Feed',    'icon' => 'si-rss',       'default' => ''],
    ]);
}

// Settings + controls for prt_social_{key} are registered together in
// app/menu.php (one place, so the setting is never orphaned from its
// control). The URLs are edited under Customize -> Theme Options ->
// Menu & Popout.
