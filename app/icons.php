<?php

/**
 * Blade Icons helper layer for the theme.
 * Wraps the global svg() helper (from blade-ui-kit/blade-icons) with safe
 * fallbacks and a social-network -> Simple Icons name map, plus brand colors.
 */

namespace App;

/**
 * Render a Blade icon to an HTML string. Never fatals if the icon/package
 * is missing — returns an empty string (or a generic fallback) instead.
 *
 * This is the single choke point every other icon helper in this file (and
 * Blade templates) should go through, so a missing icon set/package never
 * takes down a whole page — it just degrades to a plain circle glyph.
 *
 * @param string $name   Icon name, e.g. "simpleicon-github", "heroicon-o-moon", "prt-arrow-up-right".
 * @param string $class  CSS class(es) for the <svg>.
 * @param array  $attrs  Extra SVG attributes.
 */
function prt_icon($name, $class = '', $attrs = [])
{
    if (function_exists('svg')) {
        try {
            return svg($name, $class, $attrs)->toHtml();
        } catch (\Throwable $e) {
            // fall through to fallback
        }
    }

    // Generic fallback so the UI never breaks if a name can't be resolved.
    $cls = $class ? ' class="' . esc_attr($class) . '"' : '';
    return '<svg' . $cls . ' viewBox="0 0 24 24" aria-hidden="true" fill="none" stroke="currentColor" stroke-width="2"><circle cx="12" cy="12" r="9"/></svg>';
}

/**
 * Map a social network key (e.g. "github", "x") to a Blade icon name.
 * Brand glyphs come from Simple Icons (prefix "simpleicon"); non-brands use Heroicons.
 *
 * Filterable via `pressroot/social_icon_names` so a child theme/plugin can add
 * new networks or swap icon sets without editing this file — needed once this
 * theme is a distributed product other developers extend.
 */
function prt_social_icon_name($key)
{
    $map = apply_filters('pressroot/social_icon_names', [
        'linkedin'  => 'prt-linkedin',
        'github'    => 'si-github',
        'devto'     => 'si-devdotto',
        'x'         => 'si-x',
        'bluesky'   => 'si-bluesky',
        'youtube'   => 'si-youtube',
        'instagram' => 'si-instagram',
        'facebook'  => 'si-facebook',
        'mastodon'  => 'si-mastodon',
        'rss'       => 'si-rss',
        'email'     => 'heroicon-o-envelope',
    ]);

    return $map[$key] ?? 'prt-arrow-up-right';
}

/**
 * Official-ish brand color for a network (used by the block's "brand" style,
 * e.g. rendering the GitHub icon in GitHub's own black). Also filterable so
 * downstream sites can override without patching this file.
 */
function prt_social_color($key)
{
    $c = apply_filters('pressroot/social_colors', [
        'linkedin'  => '#0A66C2',
        'github'    => '#181717',
        'devto'     => '#0A0A0A',
        'x'         => '#000000',
        'bluesky'   => '#1185FE',
        'youtube'   => '#FF0000',
        'instagram' => '#E4405F',
        'facebook'  => '#1877F2',
        'mastodon'  => '#6364FF',
        'rss'       => '#F26522',
        'email'     => '#6C4CF1',
    ]);

    return $c[$key] ?? '#6C4CF1';
}

/** Render a social network's icon SVG by key (looks up name via prt_social_icon_name(), then delegates to prt_icon()). */
function prt_social_icon($key, $class = '', $attrs = [])
{
    return prt_icon(prt_social_icon_name($key), $class, $attrs);
}

/**
 * Size/fill rules for non-social inline SVGs. Social icon sizing/shape/color
 * (header, footer, popout, top bar) is now fully dynamic and lives in
 * app/social-icon-style.php (Customizer -> Theme Options -> Social Icons).
 *
 * Hooked to `prt_head_end` (the theme's own late-head hookpoint, fired from
 * the layout template) rather than `wp_head` directly, so this CSS lands
 * after app.css and can rely on cascade order without !important.
 */
add_action('prt_head_end', function () {
    echo "\n<style id=\"prt-icons\">"
        // dark-mode toggle uses Heroicons outline (stroke, not fill)
        . '.prt-theme-toggle svg{width:18px;height:18px;fill:none;stroke:currentColor;}'
        . "</style>\n";
}, 9);
