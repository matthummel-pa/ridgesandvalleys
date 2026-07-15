<?php

/**
 * Navigation Customizer panel: full flexbox control for the main menu,
 * per-item height/padding/typography, and expanded popout-menu controls.
 * Emitted as CSS via prt_head_end (no rebuild needed).
 */

namespace App;

/**
 * Shared choice lists (flexbox properties, font weights, text transforms,
 * text alignment) reused by both the main nav and popout menu controls
 * below, so the two option sets stay in sync and are only defined once.
 *
 * @return array<string, array<string, string>>
 */
function prt_nav_choices()
{
    return [
        'dir'      => ['row' => 'Row', 'row-reverse' => 'Row reverse', 'column' => 'Column', 'column-reverse' => 'Column reverse'],
        'justify'  => ['flex-start' => 'Start', 'center' => 'Center', 'flex-end' => 'End', 'space-between' => 'Space between', 'space-around' => 'Space around', 'space-evenly' => 'Space evenly'],
        'align'    => ['stretch' => 'Stretch', 'flex-start' => 'Start', 'center' => 'Center', 'flex-end' => 'End', 'baseline' => 'Baseline'],
        'content'  => ['stretch' => 'Stretch', 'flex-start' => 'Start', 'center' => 'Center', 'flex-end' => 'End', 'space-between' => 'Space between', 'space-around' => 'Space around'],
        'wrap'     => ['nowrap' => 'No wrap', 'wrap' => 'Wrap', 'wrap-reverse' => 'Wrap reverse'],
        'weight'   => ['400' => 'Regular', '500' => 'Medium', '600' => 'Semibold', '700' => 'Bold'],
        'transform'=> ['none' => 'None', 'uppercase' => 'UPPERCASE', 'lowercase' => 'lowercase', 'capitalize' => 'Capitalize'],
        'textalign'=> ['left' => 'Left', 'center' => 'Center', 'right' => 'Right'],
    ];
}

/**
 * Register the "Navigation" Customizer section: flexbox container controls
 * for the main menu, per-item box/typography controls, and popout-menu
 * typography controls. Priority 26 to run after the sibling sections that
 * lazily create the shared prt_theme_options panel.
 */
add_action('customize_register', function ($wp) {
    // Shared guarded helper — see prt_ensure_theme_options_panel() in app/customizer.php.
    prt_ensure_theme_options_panel($wp);
    $wp->add_section('prt_nav_section', [
        'title'       => __('Navigation', 'pressroot'),
        'panel'       => 'prt_theme_options',
        'description' => __('Flexbox layout for the menu, per-item sizing/typography, and the popout menu.', 'pressroot'),
    ]);
    $c = prt_nav_choices();

    // Local factories for the three repeated setting+control shapes below
    // (select/number/color), keeping each control declaration to one line.
    $select = function ($wp, $id, $label, $choices, $default) {
        $wp->add_setting($id, ['default' => $default, 'sanitize_callback' => 'sanitize_key']);
        $wp->add_control($id, ['label' => $label, 'section' => 'prt_nav_section', 'type' => 'select', 'choices' => $choices]);
    };
    $number = function ($wp, $id, $label, $default, $max = 80) {
        $wp->add_setting($id, ['default' => $default, 'sanitize_callback' => 'absint']);
        $wp->add_control($id, ['label' => $label, 'section' => 'prt_nav_section', 'type' => 'number', 'input_attrs' => ['min' => 0, 'max' => $max, 'step' => 1]]);
    };
    $color = function ($wp, $id, $label) {
        $wp->add_setting($id, ['default' => '', 'sanitize_callback' => 'sanitize_hex_color']);
        $wp->add_control(new \WP_Customize_Color_Control($wp, $id, ['label' => $label, 'section' => 'prt_nav_section']));
    };

    /* Menu container — flexbox */
    $select($wp, 'prt_nav_dir', __('Menu — direction', 'pressroot'), $c['dir'], 'row');
    $select($wp, 'prt_nav_justify', __('Menu — justify content', 'pressroot'), $c['justify'], 'flex-start');
    $select($wp, 'prt_nav_align', __('Menu — align items', 'pressroot'), $c['align'], 'center');
    $select($wp, 'prt_nav_aligncontent', __('Menu — align content (wrap)', 'pressroot'), $c['content'], 'stretch');
    $select($wp, 'prt_nav_wrap', __('Menu — flex wrap', 'pressroot'), $c['wrap'], 'nowrap');
    $number($wp, 'prt_nav_gap', __('Menu — gap (px)', 'pressroot'), 26);

    /* Menu items — box + type */
    $number($wp, 'prt_nav_pad_y', __('Item — padding top/bottom (px)', 'pressroot'), 0);
    $number($wp, 'prt_nav_pad_x', __('Item — padding left/right (px)', 'pressroot'), 0);
    $number($wp, 'prt_nav_height', __('Item — min height (px, 0 = auto)', 'pressroot'), 0, 120);
    $number($wp, 'prt_nav_font', __('Item — font size (px)', 'pressroot'), 15, 40);
    $select($wp, 'prt_nav_weight', __('Item — font weight', 'pressroot'), $c['weight'], '500');
    $select($wp, 'prt_nav_transform', __('Item — text transform', 'pressroot'), $c['transform'], 'none');
    $number($wp, 'prt_nav_spacing', __('Item — letter spacing (px)', 'pressroot'), 0, 10);
    $number($wp, 'prt_nav_radius', __('Item — corner radius (px)', 'pressroot'), 0, 40);
    $color($wp, 'prt_nav_color', __('Item — color', 'pressroot'));
    $color($wp, 'prt_nav_hover', __('Item — hover color', 'pressroot'));

    /* Popout menu */
    $select($wp, 'prt_pop_align', __('Popout — text align', 'pressroot'), $c['textalign'], 'left');
    $number($wp, 'prt_pop_pad_y', __('Popout — item padding (px)', 'pressroot'), 13, 60);
    $number($wp, 'prt_pop_font', __('Popout — item font size (px)', 'pressroot'), 19, 48);
    $select($wp, 'prt_pop_weight', __('Popout — item weight', 'pressroot'), $c['weight'], '600');
    $select($wp, 'prt_pop_transform', __('Popout — item transform', 'pressroot'), $c['transform'], 'none');
    $number($wp, 'prt_pop_gap', __('Popout — gap between items (px)', 'pressroot'), 0, 40);
}, 26);

/**
 * Emit the Navigation panel's settings as a <style> block targeting the
 * real .header-nav-list / .prt-popout-menu markup. CSS-only (no rebuild)
 * so Customizer changes apply instantly; every value is re-sanitized here
 * (sanitize_key/absint/sanitize_hex_color) even though the settings were
 * already sanitized on save, since this runs on the public front end and
 * must not trust stored option values as safe-to-echo CSS. Priority 12,
 * ahead of header-layout.php (13) and menu.php's popout CSS (13), so base
 * nav sizing is in place before layout-level overrides (ordering/flex) run.
 */
add_action('prt_head_end', function () {
    $g = function ($k, $d) { return get_theme_mod($k, $d); };

    $css = '.header-nav-list{'
        . 'flex-direction:' . sanitize_key($g('prt_nav_dir', 'row')) . ';'
        . 'justify-content:' . sanitize_key($g('prt_nav_justify', 'flex-start')) . ';'
        . 'align-items:' . sanitize_key($g('prt_nav_align', 'center')) . ';'
        . 'align-content:' . sanitize_key($g('prt_nav_aligncontent', 'stretch')) . ';'
        . 'flex-wrap:' . sanitize_key($g('prt_nav_wrap', 'nowrap')) . ';'
        . 'gap:' . absint($g('prt_nav_gap', 26)) . 'px;'
        . '}';

    $h   = absint($g('prt_nav_height', 0));
    $col = sanitize_hex_color($g('prt_nav_color', ''));
    $hov = sanitize_hex_color($g('prt_nav_hover', ''));
    $item = 'display:inline-flex;align-items:center;'
        . 'padding:' . absint($g('prt_nav_pad_y', 0)) . 'px ' . absint($g('prt_nav_pad_x', 0)) . 'px;'
        . 'font-size:' . absint($g('prt_nav_font', 15)) . 'px;'
        . 'font-weight:' . absint($g('prt_nav_weight', 500)) . ';'
        . 'text-transform:' . sanitize_key($g('prt_nav_transform', 'none')) . ';'
        . 'letter-spacing:' . absint($g('prt_nav_spacing', 0)) . 'px;'
        . 'border-radius:' . absint($g('prt_nav_radius', 0)) . 'px;';
    if ($h > 0) { $item .= 'min-height:' . $h . 'px;'; }
    if ($col) { $item .= 'color:' . $col . ';'; }
    $css .= '.header-nav-list a{' . $item . '}';
    if ($hov) { $css .= '.header-nav-list a:hover{color:' . $hov . ';}'; }

    $css .= '.prt-popout-menu{text-align:' . sanitize_key($g('prt_pop_align', 'left')) . ';}';
    $pgap = absint($g('prt_pop_gap', 0));
    $pitem = 'padding-top:' . absint($g('prt_pop_pad_y', 13)) . 'px;'
        . 'padding-bottom:' . absint($g('prt_pop_pad_y', 13)) . 'px;'
        . 'font-size:' . absint($g('prt_pop_font', 19)) . 'px;'
        . 'font-weight:' . absint($g('prt_pop_weight', 600)) . ';'
        . 'text-transform:' . sanitize_key($g('prt_pop_transform', 'none')) . ';';
    if ($pgap > 0) { $pitem .= 'margin-bottom:' . $pgap . 'px;'; }
    $css .= '.prt-popout-menu a{' . $pitem . '}';

    echo "\n<style id=\"prt-nav\">" . $css . "</style>\n";
}, 12);
