<?php
/**
 * Layout controls for pages, posts, and archives.
 *
 * Page width/alignment control plus a sidebar:
 *   1. Page      — the outer content column of the DEFAULT page/single template.
 * Plus a Sidebar position (default template only).
 *
 * Design defaults live in the Customizer (Appearance -> Customize -> Theme
 * Options -> "Layout — Content & Sidebar"); any page/post overrides them from
 * its "Layout (theme)" box. Hero controls are intentionally NOT here — pages
 * manage the hero from their own content fields.
 */

namespace App;

if (! defined('ABSPATH')) {
    exit;
}

/* ------------------------------------------------------------------ *
 * Option vocabularies
 * ------------------------------------------------------------------ */

function layout_widths(): array
{
    return [
        'full'   => __('Full width', 'sage'),
        'boxed'  => __('Boxed', 'sage'),
        'narrow' => __('Narrow (reading)', 'sage'),
    ];
}

function layout_aligns(): array
{
    return [
        'left'   => __('Left', 'sage'),
        'center' => __('Center', 'sage'),
        'right'  => __('Right', 'sage'),
    ];
}

function layout_sidebars(): array
{
    return [
        'none'  => __('No sidebar', 'sage'),
        'left'  => __('Left', 'sage'),
        'right' => __('Right', 'sage'),
    ];
}

/* ------------------------------------------------------------------ *
 * Resolver: per-entry override -> per-type Customizer default
 * ------------------------------------------------------------------ */

function entry_layout(): array
{
    static $cache = [];

    $id  = (int) get_queried_object_id();
    $key = $id . '|' . (is_singular() ? 's' : 'a');
    if (isset($cache[$key])) {
        return $cache[$key];
    }

    $widths = layout_widths();
    $aligns = layout_aligns();
    $sides  = layout_sidebars();

    $pick = function ($meta, $default, $allowed) {
        return isset($allowed[$meta]) ? $meta : $default;
    };

    if (is_singular()) {
        $type = (get_post_type($id) === 'post') ? 'post' : 'page';

        $pageWidth    = $pick((string) get_post_meta($id, '_rv_page_width', true),    (string) get_theme_mod("rv_{$type}_page_width", 'full'),      $widths);
        $pageAlign    = $pick((string) get_post_meta($id, '_rv_page_align', true),    (string) get_theme_mod("rv_{$type}_page_align", 'center'),    $aligns);
        $sidebar      = $pick((string) get_post_meta($id, '_rv_layout_sidebar', true),(string) get_theme_mod("rv_{$type}_sidebar", 'none'),         $sides);

        $hero  = (bool) get_theme_mod("rv_{$type}_hero_default", false);
        $title = get_the_title($id);
        $bg    = (string) get_the_post_thumbnail_url($id, 'rv-hero');
    } else {
        $pageWidth    = $pick('', (string) get_theme_mod('rv_archive_page_width', 'full'), $widths);
        $pageAlign    = $pick('', (string) get_theme_mod('rv_archive_page_align', 'center'), $aligns);
        $sidebar      = $pick('', (string) get_theme_mod('rv_archive_sidebar', 'none'), $sides);
        $hero = false;
        $title = '';
        $bg = '';
    }

    // A sidebar column only appears when the widget area actually has widgets.
    if ($sidebar !== 'none' && ! is_active_sidebar('sidebar-primary')) {
        $sidebar = 'none';
    }

    return $cache[$key] = [
        'page_width'    => $pageWidth,
        'page_align'    => $pageAlign,
        'sidebar'       => $sidebar,
        'hero'          => $hero,
        'hero_title'    => $title,
        'hero_bg'       => $bg,
    ];
}

/** Whether the current entry shows the full-bleed hero band (Customizer default). */
function entry_hero_enabled(): bool
{
    return (bool) entry_layout()['hero'];
}

/* ------------------------------------------------------------------ *
 * Body classes — drive the editor-content width/align on EVERY template
 * ------------------------------------------------------------------ */
add_filter('body_class', function (array $classes): array {
    if (! (is_singular() || is_archive() || is_home())) {
        return $classes;
    }
    $l = entry_layout();
    $classes[] = $l['sidebar'] === 'none' ? 'rv-no-side' : ('rv-side-' . $l['sidebar']);
    return $classes;
});

/* ------------------------------------------------------------------ *
 * Customizer: design defaults (Theme Options panel)
 * ------------------------------------------------------------------ */
add_action('customize_register', function ($wp_customize) {
    if (! $wp_customize->get_panel('rv_theme_options')) {
        $wp_customize->add_panel('rv_theme_options', [
            'title'    => __('Theme Options', 'sage'),
            'priority' => 30,
        ]);
    }

    $wp_customize->add_section('rv_layout_cs', [
        'title'       => __('Layout — Content & Sidebar', 'sage'),
        'panel'       => 'rv_theme_options',
        'description' => __('Design defaults for pages, posts, and archives. Any page/post can override these from its "Layout (theme)" box. Page width = the outer column of the default template. Bespoke templates control their own content width.', 'sage'),
    ]);

    $widths = layout_widths();
    $aligns = layout_aligns();
    $sides  = layout_sidebars();

    $sel = function ($id, $label, $default, $choices) use ($wp_customize) {
        $wp_customize->add_setting($id, ['default' => $default, 'sanitize_callback' => 'sanitize_key']);
        $wp_customize->add_control($id, ['label' => $label, 'section' => 'rv_layout_cs', 'type' => 'select', 'choices' => $choices]);
    };
    $chk = function ($id, $label, $default = false) use ($wp_customize) {
        $wp_customize->add_setting($id, ['default' => $default, 'sanitize_callback' => function ($v) { return (bool) $v; }]);
        $wp_customize->add_control($id, ['label' => $label, 'section' => 'rv_layout_cs', 'type' => 'checkbox']);
    };

    foreach (['page' => __('Pages', 'sage'), 'post' => __('Posts', 'sage')] as $t => $tl) {
        $sel("rv_{$t}_page_width",    sprintf(__('%s: page width', 'sage'), $tl),            'full',   $widths);
        $sel("rv_{$t}_page_align",    sprintf(__('%s: page alignment', 'sage'), $tl),        'center', $aligns);
        $sel("rv_{$t}_sidebar",       sprintf(__('%s: sidebar', 'sage'), $tl),               'none',   $sides);
        $chk("rv_{$t}_hero_default",  sprintf(__('%s: show hero band by default', 'sage'), $tl), false);
    }

    $sel('rv_archive_page_width', __('Archives: page width', 'sage'),  'full', $widths);
    $sel('rv_archive_page_align', __('Archives: page alignment', 'sage'), 'center', $aligns);
    $sel('rv_archive_sidebar',    __('Archives: sidebar', 'sage'),     'none', $sides);

    $wp_customize->add_setting('rv_sidebar_width', ['default' => 300, 'sanitize_callback' => 'absint']);
    $wp_customize->add_control('rv_sidebar_width', [
        'label'       => __('Sidebar width (px)', 'sage'),
        'section'     => 'rv_layout_cs',
        'type'        => 'number',
        'input_attrs' => ['min' => 220, 'max' => 420, 'step' => 10],
    ]);
}, 20);

/** Emit the sidebar-width CSS variable when it differs from the default. */
add_action('wp_head', function () {
    $w = max(220, min(420, (int) get_theme_mod('rv_sidebar_width', 300)));
    if ($w !== 300) {
        printf('<style id="rv-layout-vars">:root{--rv-sidebar-w:%dpx;}</style>' . "\n", $w);
    }
}, 20);

/* ------------------------------------------------------------------ *
 * Per-entry "Layout (theme)" box (pages + posts)
 * ------------------------------------------------------------------ */
add_action('add_meta_boxes', function () {
    foreach (['page', 'post'] as $pt) {
        add_meta_box('rv_layout_box', __('Layout (theme)', 'sage'), __NAMESPACE__ . '\\render_layout_box', $pt, 'side', 'default');
    }
});

function render_layout_box($post): void
{
    wp_nonce_field('rv_layout_box', 'rv_layout_box_nonce');

    $widthOpts = ['' => __('Default (Customizer)', 'sage')] + layout_widths();
    $alignOpts = ['' => __('Default (Customizer)', 'sage')] + layout_aligns();
    $sideOpts  = ['' => __('Default (Customizer)', 'sage')] + layout_sidebars();

    $val = function ($k) use ($post) { return (string) get_post_meta($post->ID, $k, true); };

    $field = function ($name, $label, $current, $options) {
        printf('<p style="margin:0 0 .9rem"><label for="%s"><strong>%s</strong></label><br>', esc_attr($name), esc_html($label));
        printf('<select name="%s" id="%s" style="width:100%%">', esc_attr($name), esc_attr($name));
        foreach ($options as $v => $l) {
            printf('<option value="%s"%s>%s</option>', esc_attr($v), selected($current, $v, false), esc_html($l));
        }
        echo '</select></p>';
    };

    echo '<p class="description" style="margin-top:0">' . esc_html__('The page (outer column) width and sidebar apply to the default template. Bespoke templates manage their own content width.', 'sage') . '</p>';

    echo '<p style="margin:.6rem 0 .3rem;font-size:11px;text-transform:uppercase;letter-spacing:.05em;color:#646970">' . esc_html__('Page', 'sage') . '</p>';
    $field('rv_page_width', __('Page width', 'sage'), $val('_rv_page_width'), $widthOpts);
    $field('rv_page_align', __('Page alignment', 'sage'), $val('_rv_page_align'), $alignOpts);

    echo '<hr>';
    $field('rv_layout_sidebar', __('Sidebar', 'sage'), $val('_rv_layout_sidebar'), $sideOpts);
    echo '<p class="description">' . esc_html__('Sidebar needs widgets in Appearance → Widgets → Blog Sidebar, and applies to the default template.', 'sage') . '</p>';
}

add_action('save_post', function ($post_id) {
    if (! isset($_POST['rv_layout_box_nonce'])
        || ! wp_verify_nonce(sanitize_text_field(wp_unslash($_POST['rv_layout_box_nonce'])), 'rv_layout_box')) {
        return;
    }
    if (defined('DOING_AUTOSAVE') && DOING_AUTOSAVE) {
        return;
    }
    if (! current_user_can('edit_post', $post_id)) {
        return;
    }

    $widths = layout_widths();
    $aligns = layout_aligns();
    $sides  = layout_sidebars();

    $save = function ($post_key, $meta_key, $allowed) use ($post_id) {
        $v = isset($_POST[$post_key]) ? sanitize_key(wp_unslash($_POST[$post_key])) : '';
        update_post_meta($post_id, $meta_key, isset($allowed[$v]) ? $v : '');
    };

    $save('rv_page_width',    '_rv_page_width',    $widths);
    $save('rv_page_align',    '_rv_page_align',    $aligns);
    $save('rv_layout_sidebar', '_rv_layout_sidebar', $sides);
});
