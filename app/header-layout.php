<?php

/**
 * Header Layout Customizer: full-width menu toggle, header width/height/gap,
 * and element placement (order) for logo / menu / social / button.
 * Emitted as CSS via prt_head_end (priority after the Navigation panel).
 */

namespace App;

/**
 * Register the "Header Layout" Customizer section: full-width menu toggle,
 * header max-width/height/gap, and numeric "order" fields controlling the
 * flex order (and thus visual position) of the logo/menu/social/button
 * clusters. Priority 27, after the Navigation section (26), so both panels
 * appear in a sensible order in the Customizer UI and after the shared
 * prt_theme_options panel already exists.
 */
add_action('customize_register', function ($wp) {
    // Shared guarded helper — see prt_ensure_theme_options_panel() in app/customizer.php.
    prt_ensure_theme_options_panel($wp);
    $wp->add_section('prt_headerlayout_section', [
        'title'       => __('Header Layout', 'pressroot'),
        'panel'       => 'prt_theme_options',
        'description' => __('Full-width menu, header size, and the position of the logo, menu, social links, and button.', 'pressroot'),
    ]);

    $number = function ($wp, $id, $label, $default, $min = 0, $max = 2000) {
        $wp->add_setting($id, ['default' => $default, 'sanitize_callback' => 'absint']);
        $wp->add_control($id, ['label' => $label, 'section' => 'prt_headerlayout_section', 'type' => 'number', 'input_attrs' => ['min' => $min, 'max' => $max, 'step' => 1]]);
    };

    $wp->add_setting('prt_nav_fullwidth', ['default' => false, 'sanitize_callback' => 'wp_validate_boolean']);
    $wp->add_control('prt_nav_fullwidth', ['label' => __('Full-width menu (spread items across)', 'pressroot'), 'section' => 'prt_headerlayout_section', 'type' => 'checkbox']);

    // prt_width_options() lives outside this file (shared width-choices
    // helper used by more than one Customizer panel); referenced here as a
    // fully-qualified call rather than imported since we're already in App.
    $wp->add_setting('prt_header_width', ['default' => 1180, 'sanitize_callback' => 'absint']);
    $wp->add_control('prt_header_width', ['label' => __('Header width', 'pressroot'), 'section' => 'prt_headerlayout_section', 'type' => 'select', 'choices' => \App\prt_width_options()]);
    $number($wp, 'prt_header_height', __('Header height (px, 0 = auto)', 'pressroot'), 0, 0, 240);
    $number($wp, 'prt_header_gap', __('Header gap between items (px)', 'pressroot'), 28, 0, 120);

    $number($wp, 'prt_logo_order', __('Logo position (1 = first)', 'pressroot'), 1, 1, 9);
    $number($wp, 'prt_nav_order', __('Menu position', 'pressroot'), 2, 1, 9);
    $number($wp, 'prt_social_order', __('Social links position', 'pressroot'), 3, 1, 9);
    $number($wp, 'prt_cta_order', __('Button position', 'pressroot'), 4, 1, 9);
}, 27);

/**
 * Emit the Header Layout settings as a <style> block: header container
 * sizing plus `order` on each of the four header clusters so they can be
 * rearranged purely with CSS flex order (no markup/template changes
 * needed to reposition logo/menu/social/button). Priority 13, after
 * nav-options.php's base nav styling (12), so this can layer
 * layout-level positioning on top without being overwritten by it.
 */
add_action('prt_head_end', function () {
    $g = function ($k, $d) { return get_theme_mod($k, $d); };

    $w   = absint($g('prt_header_width', 1180));
    $h   = absint($g('prt_header_height', 0));
    $gap = absint($g('prt_header_gap', 28));

    // Real header markup (resources/views/sections/header.blade.php) is
    // .site-header-inner > .brand, .header-nav, .header-actions — the CTA
    // button, dark-mode toggle, social icons, and hamburger all live inside
    // .header-actions, so "button position" reorders that whole cluster and
    // "social position" reorders social within it.
    $css = '.site-header-inner{max-width:' . $w . 'px;gap:' . $gap . 'px;';
    if ($h > 0) {
        $css .= 'min-height:' . $h . 'px;align-items:center;';
    }
    $css .= '}';

    $css .= '.site-header .brand{order:' . absint($g('prt_logo_order', 1)) . ';}';
    $css .= '.header-nav{order:' . absint($g('prt_nav_order', 2)) . ';}';
    $css .= '.header-actions{order:' . absint($g('prt_cta_order', 4)) . ';}';
    $css .= '.header-actions .header-social{order:' . absint($g('prt_social_order', 3)) . ';}';

    if ($g('prt_nav_fullwidth', false)) {
        $css .= '.header-nav{flex:1 1 0%;margin-left:24px;}';
    }

    echo "\n<style id=\"prt-headerlayout\">" . $css . "</style>\n";
}, 13);
