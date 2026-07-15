<?php

/**
 * Footer Builder — a full, Customizer-driven footer system:
 *
 *   Layout   width (contained/full) · 1–4 widget columns · column weighting ·
 *            padding scale
 *   Brand    logo/site title column · tagline · social icons
 *   Menus    footer navigation + legal (bottom bar) menu locations
 *   Bottom   copyright with {year}/{site} tokens · credit line · layout
 *            (split / centered / stacked) · divider
 *   Style    background & text via theme palette or custom hex · top border
 *
 * Also keeps the global CTA + per-template intro controls.
 */

namespace App;

/**
 * Resolve every Customizer setting for the footer into one plain array
 * (style, layout, brand column, nav, bottom bar). Single source of truth
 * for the Blade view so the template never calls get_theme_mod() directly
 * and all the "which default wins" / validation logic lives in one place.
 *
 * @return array{
 *   bg:string,text:string,border:bool,divider:bool,
 *   width:string,cols:int,col_layout:string,pad:string,
 *   brand:bool,tagline:string,show_social:bool,
 *   show_menu:bool,menu_title:string,
 *   copyright:string,credit:bool,bottom_layout:string
 * }
 */
function prt_footer()
{
    $copy = (string) get_theme_mod('prt_footer_copyright', '');
    if ($copy === '') {
        $copy = '© {year} {site}. ' . __('All rights reserved.', 'pressroot');
    }
    $copy = strtr($copy, [
        '{year}' => date('Y'),
        '{site}' => get_bloginfo('name'),
    ]);

    return [
        // Style. Defaults flipped to the Repofolio docs-site footer (dark
        // Ink ground, light text — matthummel-pa.github.io/repofolio); the
        // Customizer still overrides both.
        'bg'          => prt_palette_value(get_theme_mod('prt_footer_bg', 'ink'), get_theme_mod('prt_footer_bg_custom', '')),
        'text'        => prt_palette_value(get_theme_mod('prt_footer_textc', 'paper'), get_theme_mod('prt_footer_text_custom', '')),
        'border'      => (bool) get_theme_mod('prt_footer_border', true),
        'divider'     => (bool) get_theme_mod('prt_footer_divider', true),

        // Layout. Values are re-validated against an allow-list (not just
        // defaulted) because get_theme_mod() returns whatever was last saved
        // verbatim — if a setting's allowed choices ever change, or the mod
        // is edited directly (e.g. via a migration), a stale/invalid string
        // would otherwise reach the Blade view and its CSS class lookup.
        'width'       => in_array(get_theme_mod('prt_footer_width', 'contained'), ['contained', 'full'], true)
                            ? get_theme_mod('prt_footer_width', 'contained') : 'contained',
        'cols'        => max(1, min(4, (int) get_theme_mod('prt_footer_cols', 3))),
        'col_layout'  => in_array(get_theme_mod('prt_footer_col_layout', 'equal'), ['equal', 'wide-first', 'wide-last'], true)
                            ? get_theme_mod('prt_footer_col_layout', 'equal') : 'equal',
        'pad'         => in_array(get_theme_mod('prt_footer_pad', 'cozy'), ['compact', 'cozy', 'spacious'], true)
                            ? get_theme_mod('prt_footer_pad', 'cozy') : 'cozy',

        // Brand column (tagline: new setting → legacy prt_footer_text → site tagline)
        'brand'       => (bool) get_theme_mod('prt_footer_brand', true),
        'tagline'     => (string) (get_theme_mod('prt_footer_tagline', '')
                            ?: (get_theme_mod('prt_footer_text', '')
                            ?: get_bloginfo('description'))),
        'show_social' => (bool) get_theme_mod('prt_footer_social', true),

        // Footer navigation
        'show_menu'   => (bool) get_theme_mod('prt_footer_menu', true),
        'menu_title'  => (string) get_theme_mod('prt_footer_menu_title', __('Explore', 'pressroot')),

        // Bottom bar
        'copyright'     => $copy,
        'credit'        => (bool) get_theme_mod('prt_footer_credit', true),
        'bottom_layout' => in_array(get_theme_mod('prt_footer_bottom_layout', 'split'), ['split', 'center', 'stacked'], true)
                            ? get_theme_mod('prt_footer_bottom_layout', 'split') : 'split',
    ];
}

/**
 * Register the footer nav menu locations. Runs at priority 12 (after the
 * default after_setup_theme actions in app/setup.php, which registers
 * 'primary_navigation' at the default priority via a separate hook) so all
 * nav menu locations end up registered before the Customizer needs them.
 */
add_action('after_setup_theme', function () {
    register_nav_menus([
        'footer_navigation' => __('Footer Navigation', 'pressroot'),
        'footer_legal'      => __('Footer Legal (bottom bar)', 'pressroot'),
    ]);
}, 12);

/**
 * Wire the global CTA block (cta.blade.php) to Customizer values via
 * filters rather than reading get_theme_mod() straight from the view, so
 * the Blade template stays agnostic of where its default copy comes from
 * and other code can override the CTA by hooking the same filters.
 */
add_filter('pressroot/cta_heading', function ($d) { $v = get_theme_mod('prt_cta_heading', ''); return $v !== '' ? $v : $d; });
add_filter('pressroot/cta_text', function ($d) { $v = get_theme_mod('prt_cta_body', ''); return $v !== '' ? $v : $d; });
add_filter('pressroot/cta_label', function ($d) { $v = get_theme_mod('prt_cta_btn_label', ''); return $v !== '' ? $v : $d; });
add_filter('pressroot/cta_url', function ($d) { $v = get_theme_mod('prt_cta_btn_url', ''); return $v !== '' ? $v : $d; });

/**
 * Register every Customizer control for the Footer Builder (layout, brand
 * column, nav, bottom bar, colors) plus the separate "CTA & Intros" section.
 * Priority 23 to run after sibling panels (menu.php's popout section at 22,
 * etc.) that lazily create the shared prt_theme_options panel, avoiding any
 * ordering dependency on which file happens to create it first.
 */
add_action('customize_register', function ($wp) {
    // Shared guarded helper — see prt_ensure_theme_options_panel() in app/customizer.php.
    prt_ensure_theme_options_panel($wp);

    /* ── Footer Builder ─────────────────────────────────────────────── */
    $wp->add_section('prt_footer_section', [
        'title'       => __('Footer Builder', 'pressroot'),
        'panel'       => 'prt_theme_options',
        'description' => __('Layout, brand column, menus, bottom bar, and colors. Column content comes from Appearance → Widgets (Footer Column 1–4); menus from Appearance → Menus.', 'pressroot'),
    ]);

    // Local factories for the three repeated setting+control shapes used
    // throughout this section, so each control below is a single line
    // instead of a duplicated add_setting()+add_control() pair.
    $select = function ($id, $label, $choices, $default, $description = '') use ($wp) {
        $wp->add_setting($id, ['default' => $default, 'sanitize_callback' => 'sanitize_key']);
        $wp->add_control($id, ['label' => $label, 'description' => $description, 'section' => 'prt_footer_section', 'type' => 'select', 'choices' => $choices]);
    };
    $toggle = function ($id, $label, $default, $description = '') use ($wp) {
        $wp->add_setting($id, ['default' => $default, 'sanitize_callback' => 'wp_validate_boolean']);
        $wp->add_control($id, ['label' => $label, 'description' => $description, 'section' => 'prt_footer_section', 'type' => 'checkbox']);
    };
    $text = function ($id, $label, $default = '', $type = 'text', $description = '') use ($wp) {
        $wp->add_setting($id, ['default' => $default, 'sanitize_callback' => $type === 'textarea' ? 'wp_kses_post' : 'sanitize_text_field']);
        $wp->add_control($id, ['label' => $label, 'description' => $description, 'section' => 'prt_footer_section', 'type' => $type]);
    };

    /* Layout */
    $select('prt_footer_width', __('Footer width', 'pressroot'), [
        'contained' => __('Contained (matches content width)', 'pressroot'),
        'full'      => __('Full width', 'pressroot'),
    ], 'contained');

    $wp->add_setting('prt_footer_cols', ['default' => 3, 'sanitize_callback' => 'absint']);
    $wp->add_control('prt_footer_cols', [
        'label'       => __('Widget columns', 'pressroot'),
        'description' => __('Add blocks to each column in Appearance → Widgets (Footer Column 1–4).', 'pressroot'),
        'section'     => 'prt_footer_section',
        'type'        => 'select',
        'choices'     => [1 => '1 column', 2 => '2 columns', 3 => '3 columns', 4 => '4 columns'],
    ]);

    $select('prt_footer_col_layout', __('Column layout', 'pressroot'), [
        'equal'      => __('Equal widths', 'pressroot'),
        'wide-first' => __('Wide first column', 'pressroot'),
        'wide-last'  => __('Wide last column', 'pressroot'),
    ], 'equal');

    $select('prt_footer_pad', __('Vertical padding', 'pressroot'), [
        'compact'  => __('Compact', 'pressroot'),
        'cozy'     => __('Cozy (default)', 'pressroot'),
        'spacious' => __('Spacious', 'pressroot'),
    ], 'cozy');

    // Factory for Customizer active_callback closures: show a control only
    // when another setting ($id) currently equals $value, so e.g. the custom
    // color picker only appears once "custom" is selected in the background
    // dropdown. Returns a closure because active_callback must be a callable
    // bound to a specific ($id, $value) pair at registration time.
    $activeWhen = function ($id, $value = true) use ($wp) {
        return function ($control) use ($id, $value) {
            $s = $control->manager->get_setting($id);
            return $s && $s->value() === $value;
        };
    };

    /* Brand column */
    $toggle('prt_footer_brand', __('Show brand column (logo, tagline, social)', 'pressroot'), true);
    $text('prt_footer_tagline', __('Brand tagline', 'pressroot'), '', 'textarea', __('Defaults to the site tagline when empty.', 'pressroot'));
    $toggle('prt_footer_social', __('Show social icons', 'pressroot'), true);
    foreach (['prt_footer_tagline', 'prt_footer_social'] as $id) {
        $wp->get_control($id)->active_callback = $activeWhen('prt_footer_brand');
    }

    /* Footer navigation */
    $toggle('prt_footer_menu', __('Show footer navigation column', 'pressroot'), true, __('Uses the "Footer Navigation" menu location; falls back to your pages when no menu is assigned.', 'pressroot'));
    $text('prt_footer_menu_title', __('Footer navigation heading', 'pressroot'), __('Explore', 'pressroot'));
    $wp->get_control('prt_footer_menu_title')->active_callback = $activeWhen('prt_footer_menu');

    /* Bottom bar */
    $text('prt_footer_copyright', __('Copyright text', 'pressroot'), '', 'text', __('Tokens: {year} and {site}. Empty = "© {year} {site}. All rights reserved."', 'pressroot'));
    $toggle('prt_footer_credit', __('Show "Built with Sage" credit', 'pressroot'), true);
    $select('prt_footer_bottom_layout', __('Bottom bar layout', 'pressroot'), [
        'split'   => __('Copyright left · legal menu right', 'pressroot'),
        'center'  => __('Centered', 'pressroot'),
        'stacked' => __('Stacked', 'pressroot'),
    ], 'split', __('The legal menu uses the "Footer Legal" menu location.', 'pressroot'));

    /* Style */
    $wp->add_setting('prt_footer_bg', ['default' => 'paper', 'sanitize_callback' => 'sanitize_key']);
    $wp->add_control('prt_footer_bg', ['label' => __('Footer background', 'pressroot'), 'section' => 'prt_footer_section', 'type' => 'select', 'choices' => prt_palette_choices()]);
    $wp->add_setting('prt_footer_bg_custom', ['default' => '', 'sanitize_callback' => 'sanitize_hex_color']);
    $wp->add_control(new \WP_Customize_Color_Control($wp, 'prt_footer_bg_custom', [
        'label'           => __('Footer background (custom)', 'pressroot'),
        'section'         => 'prt_footer_section',
        'active_callback' => $activeWhen('prt_footer_bg', 'custom'),
    ]));

    $wp->add_setting('prt_footer_textc', ['default' => 'body', 'sanitize_callback' => 'sanitize_key']);
    $wp->add_control('prt_footer_textc', ['label' => __('Footer text', 'pressroot'), 'section' => 'prt_footer_section', 'type' => 'select', 'choices' => prt_palette_choices()]);
    $wp->add_setting('prt_footer_text_custom', ['default' => '', 'sanitize_callback' => 'sanitize_hex_color']);
    $wp->add_control(new \WP_Customize_Color_Control($wp, 'prt_footer_text_custom', [
        'label'           => __('Footer text (custom)', 'pressroot'),
        'section'         => 'prt_footer_section',
        'active_callback' => $activeWhen('prt_footer_textc', 'custom'),
    ]));

    $toggle('prt_footer_border', __('Show top border', 'pressroot'), true);
    $toggle('prt_footer_divider', __('Show divider above bottom bar', 'pressroot'), true);

    /* ── CTA & intros (unchanged) ───────────────────────────────────── */
    $wp->add_section('prt_content_section', ['title' => __('CTA & Intros', 'pressroot'), 'panel' => 'prt_theme_options', 'description' => __('Edit the global project CTA and the intro text on the Projects and Contact templates.', 'pressroot')]);

    $content = [
        ['prt_cta_heading', __('CTA heading', 'pressroot'), 'text'],
        ['prt_cta_body', __('CTA text', 'pressroot'), 'textarea'],
        ['prt_cta_btn_label', __('CTA button label', 'pressroot'), 'text'],
        ['prt_cta_btn_url', __('CTA button URL', 'pressroot'), 'url'],
        ['prt_projects_intro', __('Projects archive intro', 'pressroot'), 'textarea'],
        ['prt_contact_intro', __('Contact intro (above form)', 'pressroot'), 'textarea'],
    ];
    foreach ($content as $c) {
        $san = $c[2] === 'url' ? 'esc_url_raw' : ($c[2] === 'textarea' ? 'wp_kses_post' : 'sanitize_text_field');
        $wp->add_setting($c[0], ['default' => '', 'sanitize_callback' => $san]);
        $wp->add_control($c[0], ['label' => $c[1], 'section' => 'prt_content_section', 'type' => $c[2]]);
    }
}, 23);

/**
 * Emit Customizer-driven theme colors (topbar, popout menu, footer) as CSS
 * custom properties on :root, so the stylesheet can reference
 * var(--prt-footer-bg) etc. instead of the Blade view writing inline styles.
 * function_exists() guards on prt_topbar()/prt_popout() because those live
 * in other theme files (header/menu) that may not have loaded their
 * functions yet in every load order — falls back to sane hardcoded colors
 * if so. Priority 11, ahead of the other prt_head_end consumers (dark-mode
 * at 20, nav-options at 12, header-layout at 13, menu.php popout CSS at 13)
 * so these base variables exist before anything that might reference them.
 */
add_action('prt_head_end', function () {
    $tb = function_exists('App\\prt_topbar') ? prt_topbar() : ['bg' => 'var(--color-ink)', 'text' => '#fff'];
    $po = function_exists('App\\prt_popout') ? prt_popout() : ['bg' => '#17191e', 'text' => '#fff'];
    $ft = prt_footer();
    $css = ':root{'
        . '--prt-topbar-bg:' . $tb['bg'] . ';--prt-topbar-text:' . $tb['text'] . ';'
        . '--prt-popout-bg:' . $po['bg'] . ';--prt-popout-text:' . $po['text'] . ';'
        . '--prt-footer-bg:' . $ft['bg'] . ';--prt-footer-text:' . $ft['text'] . ';'
        . '}';
    echo "\n<style id=\"prt-theme-vars\">" . $css . "</style>\n";
}, 11);

/**
 * Register the four block-based footer widget areas ("Footer Column 1-4").
 * Always registers all 4 regardless of the "Widget columns" setting so
 * content isn't lost if the user later increases the column count; prt_footer()
 * above decides at render time how many of these are actually displayed.
 */
add_action('widgets_init', function () {
    for ($i = 1; $i <= 4; $i++) {
        register_sidebar([
            'name'          => sprintf(__('Footer Column %d', 'pressroot'), $i),
            'id'            => "footer-{$i}",
            'description'   => __('Drop any blocks here. Shown when "Widget columns" includes this column.', 'pressroot'),
            'before_widget' => '<section class="widget %2$s">',
            'after_widget'  => '</section>',
            'before_title'  => '<h2 class="footer-widget-title">',
            'after_title'   => '</h2>',
        ]);
    }
});
