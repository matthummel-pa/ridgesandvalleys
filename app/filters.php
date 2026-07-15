<?php

/**
 * Theme filters.
 *
 * Small, unrelated WordPress filter/shortcode tweaks that don't warrant their
 * own file. Keep additions here narrow and self-contained — anything that
 * grows its own settings UI or multiple hooks (e.g. GitHub data, layout)
 * should get its own app/*.php file instead.
 */

namespace App;

/**
 * Replace the default "[…]" excerpt suffix with a real "… Continued" link
 * back to the post, since the stock ellipsis gives readers no way to click
 * through from an excerpt-only listing.
 *
 * @return string
 */
add_filter('excerpt_more', function () {
    return sprintf(' &hellip; <a href="%s">%s</a>', get_permalink(), __('Continued', 'pressroot'));
});

/**
 * NOTE: the [prt_github] shortcode that lived here (live cached repo data via
 * app/Github.php) was removed with the rest of the GitHub subsystem — use the
 * Repofolio plugin's repofolio/repo-profile or repofolio/repo-grid blocks
 * instead. See the note in app/setup.php.
 */
