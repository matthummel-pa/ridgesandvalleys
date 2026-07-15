<?php

/**
 * prt/section — page-builder-style section wrapper.
 *
 * Wraps InnerBlocks with background colour/image, padding, and container
 * width controls. Server-side rendered so CSS variables always apply.
 *
 * Also registers the 'pressroot' block category so all prt/* blocks
 * are grouped together in the inserter.
 */

namespace App;

/* ── Block category ─────────────────────────────────────────────────── */

// Prepends the "pressroot" category to the inserter's category list (rather
// than appending) so all prt/* blocks sort to the top, above core/WP
// categories, making them easy to find.
add_filter('block_categories_all', function (array $cats, $context): array {
    array_unshift($cats, [
        'slug'  => 'pressroot',
        'title' => __('Pressroot', 'pressroot'),
        'icon'  => null,
    ]);
    return $cats;
}, 10, 2);

/* ── Section block attributes ───────────────────────────────────────── */

/**
 * Attribute schema for prt/section. These are deliberately separate from
 * WordPress's built-in color/spacing block supports (which are turned off
 * below) because a full-bleed section needs a background *image* with an
 * overlay and named padding/width presets — options the native supports
 * panel doesn't offer.
 *
 * @return array
 */
function prt_section_attrs(): array
{
    return [
        'bgColor'        => ['type' => 'string',  'default' => ''],
        'bgImageUrl'     => ['type' => 'string',  'default' => ''],
        'bgOverlay'      => ['type' => 'number',  'default' => 40],
        'paddingTop'     => ['type' => 'string',  'default' => 'md'],
        'paddingBottom'  => ['type' => 'string',  'default' => 'md'],
        'containerWidth' => ['type' => 'string',  'default' => 'contained'],
        'textColor'      => ['type' => 'string',  'default' => ''],
        'hasRule'        => ['type' => 'boolean', 'default' => false],
        'anchor'         => ['type' => 'string',  'default' => ''],
    ];
}

/* ── Registration ───────────────────────────────────────────────────── */

// Registers prt/section on 'init' (the standard hook for block type
// registration; priority 10 is the WordPress default — no ordering
// dependency on the other prt/* blocks, which register at priority 12).
add_action('init', function () {
    $path = 'resources/js/prt-section-editor.js';
    if (file_exists(get_theme_file_path($path))) {
        wp_register_script(
            'prt-section',
            get_theme_file_uri($path),
            ['wp-blocks', 'wp-element', 'wp-block-editor', 'wp-components', 'wp-i18n'],
            filemtime(get_theme_file_path($path)),
            true
        );
    }

    register_block_type('prt/section', [
        'api_version'     => 2,
        'editor_script'   => 'prt-section',
        'attributes'      => prt_section_attrs(),
        'render_callback' => __NAMESPACE__ . '\\prt_section_render',
        'supports'        => [
            'align'   => ['full'],
            'anchor'  => true,
            'html'    => false,
            'spacing' => false,
            'color'   => false,
        ],
    ]);
}, 10);

/* ── Render callback ────────────────────────────────────────────────── */

/**
 * Server-side render for prt/section. Wraps InnerBlocks ($content) in a
 * <section> whose look is driven entirely by utility classes (prt-section--*)
 * mapped from attributes, so the CSS variables/theme (colours, spacing
 * scale) stay the single source of truth — only background-image (which
 * can't be expressed as a class) falls back to an inline style.
 *
 * @param array  $attrs   Block attributes (see prt_section_attrs()).
 * @param string $content Rendered InnerBlocks HTML.
 * @return string Block HTML.
 */
function prt_section_render(array $attrs, string $content): string
{
    $a = wp_parse_args($attrs, array_map(fn($v) => $v['default'], prt_section_attrs()));

    // Build class list
    $classes = ['prt-section'];
    if ($a['bgColor'])        $classes[] = 'prt-section--bg-' . sanitize_html_class($a['bgColor']);
    if ($a['paddingTop'])     $classes[] = 'prt-section--pt-' . sanitize_html_class($a['paddingTop']);
    if ($a['paddingBottom'])  $classes[] = 'prt-section--pb-' . sanitize_html_class($a['paddingBottom']);
    if ($a['containerWidth']) $classes[] = 'prt-section--width-' . sanitize_html_class($a['containerWidth']);
    if ($a['textColor'])      $classes[] = 'prt-section--text-' . sanitize_html_class($a['textColor']);

    // Inline style (only for bg images — colours use CSS vars via class)
    $style = '';
    if (!empty($a['bgImageUrl'])) {
        // Capped at 80 (not 100) so the background image is never fully
        // obscured by the overlay — some image is always visible.
        $overlay = max(0, min(80, absint($a['bgOverlay'])));
        $alpha   = round($overlay / 100, 2);
        $style   = sprintf(
            'background-image:linear-gradient(rgba(0,0,0,%s),rgba(0,0,0,%s)),url(%s);background-size:cover;background-position:center;',
            $alpha,
            $alpha,
            esc_url($a['bgImageUrl'])
        );
    }

    // Optional anchor
    $id = !empty($a['anchor']) ? ' id="' . esc_attr($a['anchor']) . '"' : '';

    // Optional rule above section
    $rule = !empty($a['hasRule']) ? '<hr class="rule" aria-hidden="true">' . "\n" : '';

    return $rule
        . '<section class="' . esc_attr(implode(' ', $classes)) . '"'
        . $id
        . ($style ? ' style="' . esc_attr($style) . '"' : '')
        . '>'
        . '<div class="prt-section-inner">'
        . $content
        . '</div>'
        . '</section>';
}
