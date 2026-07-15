<?php

/**
 * Layout engine: per-content-type width preset + custom width + sidebar,
 * driven by theme mods, exposed in the Customizer (Theme Options -> Layout),
 * and applied via body classes + a precise inline width override.
 *
 * Despite the filename, this file covers two mostly-independent features that
 * both live under the "Theme Options" Customizer panel:
 *   1. Per-content-type layout (width/sidebar) for pages, posts, archives,
 *      and the "projects" CPT — see prt_layout_defaults() / prt_active_layout().
 *   2. The optional "Top Bar" utility strip above the main nav (contact info,
 *      social links, CTA) — see prt_topbar() / prt_topbar_render().
 * Both are registered against the same 'prt_theme_options' panel that
 * app/customizer.php creates, defensively re-creating it here in case this
 * file's customize_register callback runs first.
 */

namespace App;

/** Fallback width/sidebar settings per content-type bucket, used whenever no
 *  theme_mod has been saved yet (fresh install) or a specific type key is
 *  missing. Single source of truth read by both prt_active_layout() and the
 *  Customizer control registration below. */
function prt_layout_defaults()
{
    return [
        'page'    => ['width' => 'default', 'maxwidth' => 0, 'sidebar' => false],
        'post'    => ['width' => 'narrow',  'maxwidth' => 0, 'sidebar' => false],
        'archive' => ['width' => 'default', 'maxwidth' => 0, 'sidebar' => false],
        'project' => ['width' => 'default', 'maxwidth' => 0, 'sidebar' => false],
    ];
}

/** Choices for the "width preset" select control; the CSS these map to lives
 *  in resources/css/app.css (prt-w-* body classes), not here. */
function prt_width_choices()
{
    return [
        'default' => __('Default', 'pressroot'),
        'full'    => __('Full width', 'pressroot'),
        'narrow'  => __('Narrow', 'pressroot'),
        'boxed'   => __('Boxed', 'pressroot'),
    ];
}

/** Human-readable labels for each layout bucket, used as Customizer control labels. */
function prt_layout_labels()
{
    return [
        'page'    => __('Pages', 'pressroot'),
        'post'    => __('Posts', 'pressroot'),
        'archive' => __('Archives', 'pressroot'),
        'project' => __('Projects', 'pressroot'),
    ];
}

/** Which layout bucket the current view falls into. Projects target the CPT only. */
function prt_current_layout_type()
{
    if (is_singular('projects') || is_post_type_archive('projects')) {
        return 'project';
    }
    if (is_singular('post')) {
        return 'post';
    }
    if (is_page()) {
        return 'page';
    }
    if (is_search() || is_home() || is_archive()) {
        return 'archive';
    }
    return 'page';
}

/**
 * Resolve the effective width/sidebar settings for whatever is currently
 * being viewed. This is the one function templates/hooks should call to read
 * layout state — it already knows which bucket applies (via
 * prt_current_layout_type()) and has fallen back to defaults, so callers
 * never need to know about theme_mod keys directly.
 */
function prt_active_layout()
{
    $d = prt_layout_defaults();
    $t = prt_current_layout_type();
    return [
        'type'     => $t,
        'width'    => get_theme_mod("prt_layout_{$t}_width", $d[$t]['width']),
        'maxwidth' => absint(get_theme_mod("prt_layout_{$t}_maxwidth", $d[$t]['maxwidth'])),
        'sidebar'  => (bool) get_theme_mod("prt_layout_{$t}_sidebar", $d[$t]['sidebar']),
    ];
}

/** Layout classes on <body>, so app.css can scope width/sidebar CSS rules
 *  purely by class (e.g. `.prt-w-narrow`) without any inline styles. */
add_filter('body_class', function ($classes) {
    $l = prt_active_layout();
    $classes[] = 'prt-w-' . sanitize_html_class($l['width']);
    $classes[] = 'prt-type-' . sanitize_html_class($l['type']);
    if ($l['sidebar']) {
        $classes[] = 'prt-has-sidebar';
    }
    return $classes;
});

/**
 * Page edit screen: offer only the Default template. The theme builds pages from
 * block patterns and sets content width via the Customizer, not per-page Blade
 * templates. Existing template assignments still render — this only cleans up
 * the "Template" dropdown so authors aren't offered the internal templates.
 */
add_filter('theme_page_templates', function ($templates) {
    return [];
});

/**
 * Per-type custom width override, emitted as a small <style> block (after
 * app.css, via prt_head_end) rather than a Customizer-managed CSS var,
 * because this needs a real max-width value in px scoped per content-type
 * body class — a single :root variable couldn't vary by page type.
 */
add_action('prt_head_end', function () {
    $d = prt_layout_defaults();
    $css = '';
    foreach (array_keys($d) as $type) {
        $w = absint(get_theme_mod("prt_layout_{$type}_maxwidth", $d[$type]['maxwidth']));
        if ($w > 0) {
            $css .= "body.prt-type-{$type} .main .container{max-width:{$w}px}";
        }
    }
    if ($css !== '') {
        echo "\n<style id=\"prt-layout-widths\">" . $css . "</style>\n";
    }
});

/**
 * Primary (right) sidebar widget area used when a layout enables it.
 *
 * NOTE(audit): app/setup.php also registers a sidebar with the same id
 * 'sidebar-primary' (different config: 'name' => 'Primary', before/after
 * markup using %1$s/%2$s vs. this one's %2$s-only and an <h2> vs. <h3>
 * title wrap). Since both hook into `widgets_init`, whichever callback runs
 * last overwrites the earlier entry in $wp_registered_sidebars — the two
 * definitions are not merged, so one of them is dead code and any widgets
 * assigned to "Primary Sidebar" may silently render with the other
 * registration's wrapper markup depending on hook order.
 */
add_action('widgets_init', function () {
    register_sidebar([
        'name'          => __('Primary Sidebar', 'pressroot'),
        'id'            => 'sidebar-primary',
        'description'   => __('Shown on the right when a layout has its sidebar enabled.', 'pressroot'),
        'before_widget' => '<section class="widget %2$s">',
        'after_widget'  => '</section>',
        'before_title'  => '<h2 class="widget-title">',
        'after_title'   => '</h2>',
    ]);
});

/** Customizer: Theme Options -> Layout. Section id 'prt_layout_section' is
 *  what app/customizer.php's "Content width" control (prt_container) also
 *  targets — see the note there about cross-file section registration order. */
add_action('customize_register', function ($wp) {
    // Shared guarded helper — see prt_ensure_theme_options_panel() in app/customizer.php.
    prt_ensure_theme_options_panel($wp);

    $wp->add_section('prt_layout_section', [
        'title'       => __('Layout', 'pressroot'),
        'panel'       => 'prt_theme_options',
        'description' => __('Set a width preset and/or an exact custom width per content type. Standard widths: 1140, 1200, 1280, 1320, 1440 px.', 'pressroot'),
    ]);

    $d = prt_layout_defaults();
    foreach (prt_layout_labels() as $type => $label) {
        $wp->add_setting("prt_layout_{$type}_width", [
            'default'           => $d[$type]['width'],
            'sanitize_callback' => 'sanitize_key',
        ]);
        $wp->add_control("prt_layout_{$type}_width", [
            'label'   => sprintf(__('%s — width preset', 'pressroot'), $label),
            'section' => 'prt_layout_section',
            'type'    => 'select',
            'choices' => prt_width_choices(),
        ]);

        $wp->add_setting("prt_layout_{$type}_maxwidth", [
            'default'           => $d[$type]['maxwidth'],
            'sanitize_callback' => 'absint',
        ]);
        $wp->add_control("prt_layout_{$type}_maxwidth", [
            'label'       => sprintf(__('%s — custom width', 'pressroot'), $label),
            'description' => __('Overrides the preset above when set. "Use preset" follows the width preset.', 'pressroot'),
            'section'     => 'prt_layout_section',
            'type'        => 'select',
            'choices'     => \App\prt_width_options(true),
        ]);

        $wp->add_setting("prt_layout_{$type}_sidebar", [
            'default'           => $d[$type]['sidebar'],
            'sanitize_callback' => 'wp_validate_boolean',
        ]);
        $wp->add_control("prt_layout_{$type}_sidebar", [
            'label'   => sprintf(__('%s — show sidebar', 'pressroot'), $label),
            'section' => 'prt_layout_section',
            'type'    => 'checkbox',
        ]);
    }
}, 20);

/* ----------------------------------------------------------------------
   Top utility bar (above the main nav)
---------------------------------------------------------------------- */

/**
 * Map a palette choice (from a Customizer select control) to a CSS value —
 * either one of the theme's global CSS variables or, for 'custom', the
 * paired color-picker value. Lets top-bar bg/text pick up live theme colors
 * automatically instead of needing their own separate color pickers.
 *
 * Note: 'green' and 'paper' map to --color-green/--color-khaki, which are the
 * legacy variable names for the "Brand / buttons" and "Page background"
 * colors respectively (see the note in app/customizer.php's prt_head_end
 * callback) — the CSS variable names don't match their current meaning.
 */
function prt_palette_value($choice, $custom = '')
{
    $map = [
        'ink'    => 'var(--color-ink)',
        'green'  => 'var(--color-green)',
        'cream'  => 'var(--color-cream)',
        'paper'  => 'var(--color-khaki)',
        'body'   => 'var(--color-body)',
        'white'  => '#ffffff',
    ];
    if ($choice === 'custom') {
        return $custom !== '' ? $custom : 'transparent';
    }
    return $map[$choice] ?? 'transparent';
}

/** Human-readable labels for the palette select control (used for top-bar bg/text).
 *  NOTE(audit): 'green' is labeled "Brand green" here, but prt_palette_value()
 *  maps 'green' to --color-green, which (per the note above) now actually
 *  holds the purple brand/action color after the Paper + Space rebrand — the
 *  admin-facing label is stale and would confuse anyone picking "Brand green"
 *  expecting an actual green. */
function prt_palette_choices()
{
    return [
        'ink'    => __('Ink (dark)', 'pressroot'),
        'green'  => __('Brand green', 'pressroot'),
        'cream'  => __('Cream', 'pressroot'),
        'paper'  => __('Paper', 'pressroot'),
        'white'  => __('White', 'pressroot'),
        'custom' => __('Custom…', 'pressroot'),
    ];
}

/** Resolve all Top Bar settings into one array of ready-to-use values
 *  (already run through prt_palette_value()), so the renderer below doesn't
 *  need to know about theme_mod keys or palette resolution. */
function prt_topbar()
{
    return [
        'enable'      => (bool) get_theme_mod('prt_topbar_enable', false),
        'contact'     => get_theme_mod('prt_topbar_contact', ''),
        'show_social' => (bool) get_theme_mod('prt_topbar_show_social', true),
        'cta_text'    => get_theme_mod('prt_topbar_cta_text', ''),
        'cta_url'     => get_theme_mod('prt_topbar_cta_url', ''),
        'bg'          => prt_palette_value(get_theme_mod('prt_topbar_bg', 'ink'), get_theme_mod('prt_topbar_bg_custom', '')),
        'text'        => prt_palette_value(get_theme_mod('prt_topbar_text', 'white'), get_theme_mod('prt_topbar_text_custom', '')),
    ];
}

/**
 * Render the top utility bar. The Customizer section above (and prt_topbar())
 * has existed for a while, but nothing ever echoed the markup — this is that
 * missing piece. Markup matches the .top-bar / .top-bar-inner / .top-bar-social
 * / .top-bar-cta CSS already in resources/css/app.css.
 */
function prt_topbar_render()
{
    // Guards against double output if this function is ever called more than
    // once per request (e.g. a template explicitly calling it in addition to
    // the prt_before_header hook below).
    static $done = false;
    if ($done) {
        return;
    }
    $done = true;

    $tb = prt_topbar();
    if (! $tb['enable']) {
        return;
    }

    $contact = trim((string) $tb['contact']);
    $links   = ($tb['show_social'] && function_exists('App\\prt_social_links')) ? prt_social_links() : [];
    $ctaText = trim((string) $tb['cta_text']);
    $ctaUrl  = trim((string) $tb['cta_url']);
    $hasCta  = $ctaText !== '' && $ctaUrl !== '';

    if ($contact === '' && empty($links) && ! $hasCta) {
        return;
    }

    $style = get_theme_mod('prt_social_style', 'icons');

    echo '<div class="top-bar" style="background:' . esc_attr($tb['bg']) . ';color:' . esc_attr($tb['text']) . '">';
    echo '<div class="top-bar-inner">';

    if ($contact !== '') {
        echo '<div class="top-bar-contact">' . wp_kses_post($contact) . '</div>';
    }

    if (! empty($links) || $hasCta) {
        echo '<div class="top-bar-right">';
        if (! empty($links)) {
            echo '<ul class="top-bar-social' . ($style === 'icons' ? ' is-icons' : '') . '" aria-label="' . esc_attr__('Social links', 'pressroot') . '">';
            foreach ($links as $s) {
                $inner = $style === 'icons' && function_exists('App\\prt_social_icon') ? prt_social_icon($s['key']) : esc_html($s['label']);
                echo '<li><a href="' . esc_url($s['url']) . '" aria-label="' . esc_attr($s['label']) . '" rel="me noopener" target="_blank">' . $inner . '</a></li>';
            }
            echo '</ul>';
        }
        if ($hasCta) {
            echo '<a class="top-bar-cta" href="' . esc_url($ctaUrl) . '">' . esc_html($ctaText) . '</a>';
        }
        echo '</div>';
    }

    echo '</div>';
    echo '</div>';
}
// Priority 8: after the announcement bar (5), before .site-header is included —
// matches the default stack order (announcement, top bar, nav) used by the
// "Stack order" reorder settings in app/header-elements.php.
add_action('prt_before_header', __NAMESPACE__ . '\\prt_topbar_render', 8);

/**
 * Customizer: Theme Options -> Top Bar. Registers the controls that
 * prt_topbar() reads back at render time. Priority 21 (vs. the layout
 * section's priority 20 above) simply keeps sections appearing in a
 * predictable order in the panel; it has no functional effect since
 * add_control() resolves sections lazily (see the cross-file note in
 * app/customizer.php).
 */
add_action('customize_register', function ($wp) {
    // Shared guarded helper — see prt_ensure_theme_options_panel() in app/customizer.php.
    prt_ensure_theme_options_panel($wp);
    $wp->add_section('prt_topbar_section', [
        'title'       => __('Top Bar', 'pressroot'),
        'panel'       => 'prt_theme_options',
        'description' => __('A slim utility bar above the main nav for contact info, social links, and a CTA.', 'pressroot'),
    ]);

    $controls = [
        ['prt_topbar_enable', __('Enable top bar', 'pressroot'), 'checkbox', false, 'wp_validate_boolean'],
        ['prt_topbar_contact', __('Contact text (left)', 'pressroot'), 'text', '', 'sanitize_text_field'],
        ['prt_topbar_show_social', __('Show social links (right)', 'pressroot'), 'checkbox', true, 'wp_validate_boolean'],
        ['prt_topbar_cta_text', __('CTA text (right)', 'pressroot'), 'text', '', 'sanitize_text_field'],
        ['prt_topbar_cta_url', __('CTA URL', 'pressroot'), 'text', '', 'esc_url_raw'],
    ];
    foreach ($controls as $c) {
        $wp->add_setting($c[0], ['default' => $c[3], 'sanitize_callback' => $c[4]]);
        $wp->add_control($c[0], ['label' => $c[1], 'section' => 'prt_topbar_section', 'type' => $c[2]]);
    }

    foreach (['bg' => __('Background color', 'pressroot'), 'text' => __('Text color', 'pressroot')] as $slug => $label) {
        $def = $slug === 'bg' ? 'ink' : 'white';
        $wp->add_setting("prt_topbar_{$slug}", ['default' => $def, 'sanitize_callback' => 'sanitize_key']);
        $wp->add_control("prt_topbar_{$slug}", ['label' => $label, 'section' => 'prt_topbar_section', 'type' => 'select', 'choices' => prt_palette_choices()]);
        $wp->add_setting("prt_topbar_{$slug}_custom", ['default' => '', 'sanitize_callback' => 'sanitize_hex_color']);
        $wp->add_control(new \WP_Customize_Color_Control($wp, "prt_topbar_{$slug}_custom", ['label' => $label . ' ' . __('(custom)', 'pressroot'), 'section' => 'prt_topbar_section']));
    }
}, 21);
