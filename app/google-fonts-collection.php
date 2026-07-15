<?php

namespace App;

/**
 * Google Fonts in the native WordPress Font Library.
 *
 * Registers the full Google Fonts library (1,500+ families) as a Font Collection
 * so it appears under Appearance → Editor → Styles → Typography → "Manage fonts",
 * browsable and filterable by category (sans-serif, serif, display, handwriting,
 * monospace). Fonts are downloaded and self-hosted on install (GDPR-safe) — nothing
 * is loaded from Google's servers on the front end.
 *
 * Uses WordPress core's own hosted catalog, so it stays current and requires no API key.
 */
add_action('init', function () {
    // Font Library (WP 6.5+) must be present. Older cores simply don't get
    // the collection — this is a progressive enhancement, not a hard dependency.
    if (! function_exists('wp_register_font_collection')) {
        return;
    }

    $slug = 'prt-google-fonts';

    // Don't double-register if it (or a core equivalent) is already there.
    // Registering the same slug twice would otherwise throw/warn on every
    // page load once WP core ships its own bundled Google Fonts collection.
    if (function_exists('wp_get_font_collection') && wp_get_font_collection($slug)) {
        return;
    }

    wp_register_font_collection($slug, [
        'name'          => __('Google Fonts', 'pressroot'),
        'description'   => __('The full Google Fonts library — browse and filter by type: sans-serif, serif, display, handwriting, and monospace. Installed fonts are self-hosted.', 'pressroot'),
        'font_families' => 'https://s.w.org/images/fonts/wp-6.5/collections/google-fonts-with-preview.json',
        'categories'    => [
            ['name' => __('Sans-serif', 'pressroot'), 'slug' => 'sans-serif'],
            ['name' => __('Serif', 'pressroot'),      'slug' => 'serif'],
            ['name' => __('Display', 'pressroot'),    'slug' => 'display'],
            ['name' => __('Handwriting', 'pressroot'),'slug' => 'handwriting'],
            ['name' => __('Monospace', 'pressroot'),  'slug' => 'monospace'],
        ],
    ]);
});
