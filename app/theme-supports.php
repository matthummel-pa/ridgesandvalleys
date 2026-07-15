<?php

/**
 * Additional WordPress theme supports (block editor + media features).
 *
 * Kept separate from app/setup.php's main `after_setup_theme` callback purely
 * for organization — these are the "block editor / media" flavored supports
 * (wide alignment, block styles, custom logo, etc.) vs. setup.php's more
 * structural ones (nav menus, title-tag, HTML5). Both run on the same hook.
 */

namespace App;

/**
 * Register theme supports needed for the block editor and media features.
 *
 * Runs at priority 20 (same as the block in app/setup.php) so it executes
 * after WordPress core's own `after_setup_theme` default-priority (10)
 * callbacks, avoiding any race where core hasn't finished its own setup yet.
 *
 * @return void
 */
add_action('after_setup_theme', function () {
    // Allows blocks to use wide/full alignment (needed for full-bleed images, etc.).
    add_theme_support('align-wide');
    // Lets core block style variations (e.g. rounded button) apply.
    add_theme_support('wp-block-styles');
    // Loads editor.css inside the block editor iframe so blocks preview with theme styles.
    add_theme_support('editor-styles');
    add_theme_support('automatic-feed-links');
    // Enables the post-format taxonomy UI for these formats; templates decide how (or whether) to use them.
    add_theme_support('post-formats', ['aside', 'gallery', 'link', 'image', 'quote', 'video', 'audio', 'status']);
    // Site icon in header/nav; flex dimensions let uploads of other aspect ratios still work.
    add_theme_support('custom-logo', [
        'height'      => 60,
        'width'       => 200,
        'flex-height' => true,
        'flex-width'  => true,
    ]);
}, 20);
