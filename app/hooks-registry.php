<?php

/**
 * Hook Registry — a single source of truth listing every custom filter and
 * action this theme exposes for other developers (child themes, plugins,
 * or mu-plugins) to hook into.
 *
 * This is metadata only (no side effects). It powers:
 *   - `wp pressroot hooks` (app/cli.php)
 *   - ARCHITECTURE.md's hook table (kept in sync by hand; run the CLI
 *     command above to check it against reality)
 *
 * When you add a new apply_filters()/do_action() call anywhere in app/,
 * add a matching row here so it stays discoverable.
 */

namespace App;

/**
 * Return the full catalog of custom filters and actions this theme exposes,
 * grouped by 'filters' and 'actions'. Each entry maps a hook name to the
 * file that fires it and a human-readable description of what a developer
 * can do by hooking into it.
 *
 * This function has no side effects and registers nothing itself — it's
 * pure data, read by `wp pressroot hooks` (app/cli.php) to print a table in
 * the terminal, and meant to be cross-checked by hand against
 * ARCHITECTURE.md's hook table. Keeping it centralized here (rather than a
 * comment next to each apply_filters()/do_action() call) means there's one
 * place a developer can scan to see everything the theme is extensible at,
 * without grepping the whole codebase.
 *
 * @return array{filters: array<string, array{file: string, desc: string}>, actions: array<string, array{file: string, desc: string}>}
 */
function prt_hook_registry(): array
{
    return [
        'filters' => [
            'pressroot/style_kits' => [
                'file' => 'app/settings-io.php',
                'desc' => 'Add or override one-click Style Kit presets (Appearance -> Theme Tools).',
            ],
            'pressroot/theme_defaults' => [
                'file' => 'app/customizer.php',
                'desc' => 'Override the theme-wide default colors, fonts, container width, and header/footer copy before any theme_mod is applied.',
            ],
            'pressroot/fonts' => [
                'file' => 'app/customizer.php',
                'desc' => 'Add, remove, or re-map the Google Fonts available in Typography controls.',
            ],
            'pressroot/width_options' => [
                'file' => 'app/customizer.php',
                'desc' => 'Add custom content-width presets to the "Content width" / "Top bar width" etc. select controls.',
            ],
            'pressroot/font_weights' => [
                'file' => 'app/typography.php',
                'desc' => 'Change the weight choices offered for headings, body, nav, and buttons.',
            ],
            'pressroot/socials_map' => [
                'file' => 'app/menu.php',
                'desc' => 'Add or rename social networks available for the popout menu / social URL fields.',
            ],
            'pressroot/social_platforms' => [
                'file' => 'app/social-links.php',
                'desc' => 'Add social platforms available for the Menu & Popout social URL fields (label + default URL).',
            ],
            'pressroot/social_icon_names' => [
                'file' => 'app/icons.php',
                'desc' => 'Map a social network key to a different Blade Icons name.',
            ],
            'pressroot/social_colors' => [
                'file' => 'app/icons.php',
                'desc' => 'Override a social network\'s official brand color (used by "Brand colors" icon style).',
            ],
            'pressroot/github_owner' => [
                'file' => 'app/seed-pages.php',
                'desc' => 'Override the default GitHub username used when seeding demo project content.',
            ],
        ],
        'actions' => [
            'prt_head_end' => [
                'file' => 'resources/views/layouts/app.blade.php',
                'desc' => 'Fires at the end of <head>, after wp_head() and the compiled app.css/app.js — the theme\'s own dynamic <style> overrides hook in here.',
            ],
            'prt_before_header' => [
                'file' => 'resources/views/layouts/app.blade.php',
                'desc' => 'Fires immediately before the site header (sections.header) is included.',
            ],
            'prt_after_header' => [
                'file' => 'resources/views/layouts/app.blade.php',
                'desc' => 'Fires immediately after the site header, before the main content wrapper.',
            ],
            'prt_before_content' => [
                'file' => 'resources/views/layouts/app.blade.php',
                'desc' => 'Fires just inside <main>, before @yield(\'content\').',
            ],
            'prt_after_content' => [
                'file' => 'resources/views/layouts/app.blade.php',
                'desc' => 'Fires just inside <main>, after @yield(\'content\'), before it closes.',
            ],
            'prt_before_footer' => [
                'file' => 'resources/views/layouts/app.blade.php',
                'desc' => 'Fires immediately before the site footer (sections.footer) is included.',
            ],
            'prt_after_footer' => [
                'file' => 'resources/views/layouts/app.blade.php',
                'desc' => 'Fires immediately after the site footer, before #app closes.',
            ],
            'prt_before_post_card' => [
                'file' => 'resources/views/partials/content.blade.php',
                'desc' => 'Fires before each post-card <article> in blog/archive listings. Receives the post ID.',
            ],
            'prt_after_post_card' => [
                'file' => 'resources/views/partials/content.blade.php',
                'desc' => 'Fires after each post-card <article> in blog/archive listings. Receives the post ID.',
            ],
        ],
    ];
}
