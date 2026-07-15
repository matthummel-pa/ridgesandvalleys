<?php

/**
 * Extra header/navigation controls (added to the Customizer "Navigation" section):
 *  - Per-element alignment for the logo, dark-mode icon, and popout button (L/C/R).
 *  - Reorder the three stacked bars: announcement, top bar, navigation.
 *  - Popout width + multi-column popout menu on desktop.
 * All opt-in: nothing is emitted until a setting is changed, so the default
 * layout is untouched.
 */

namespace App;

add_action('customize_register', function ($wp) {
    // Shared guarded helper — see prt_ensure_theme_options_panel() in app/customizer.php.
    prt_ensure_theme_options_panel($wp);
    if (! $wp->get_section('prt_nav_section')) {
        $wp->add_section('prt_nav_section', ['title' => __('Navigation', 'pressroot'), 'panel' => 'prt_theme_options']);
    }

    $sel = function ($wp, $id, $label, $choices, $default) {
        $wp->add_setting($id, ['default' => $default, 'sanitize_callback' => 'sanitize_key']);
        $wp->add_control($id, ['label' => $label, 'section' => 'prt_nav_section', 'type' => 'select', 'choices' => $choices]);
    };
    // postMessage variant — updates preview instantly without a full reload.
    $selPM = function ($wp, $id, $label, $choices, $default) {
        $wp->add_setting($id, ['default' => $default, 'sanitize_callback' => 'sanitize_key', 'transport' => 'postMessage']);
        $wp->add_control($id, ['label' => $label, 'section' => 'prt_nav_section', 'type' => 'select', 'choices' => $choices]);
    };

    $align = ['none' => __('Default', 'pressroot'), 'left' => __('Left', 'pressroot'), 'center' => __('Center', 'pressroot'), 'right' => __('Right', 'pressroot')];
    $sel($wp, 'prt_logo_align', __('Logo position', 'pressroot'), $align, 'none');
    $sel($wp, 'prt_darkicon_align', __('Dark-mode icon position', 'pressroot'), $align, 'none');
    $sel($wp, 'prt_popbtn_align', __('Menu (popout) button position', 'pressroot'), $align, 'none');
    $sel($wp, 'prt_cta_align', __('Header button (CTA) position', 'pressroot'), $align, 'none');
    // Navbar social: show/hide uses refresh; position uses postMessage for instant preview.
    $wp->add_setting('prt_nav_social', ['default' => true, 'sanitize_callback' => 'wp_validate_boolean']);
    $wp->add_control('prt_nav_social', ['label' => __('Show social icons in navigation bar', 'pressroot'), 'section' => 'prt_nav_section', 'type' => 'checkbox']);
    $selPM($wp, 'prt_nav_social_align', __('Navigation social position', 'pressroot'), $align, 'none');
    // Hide social icons on mobile (<=640px) per bar.
    $wp->add_setting('prt_social_nav_hide_mobile', ['default' => false, 'sanitize_callback' => 'wp_validate_boolean']);
    $wp->add_control('prt_social_nav_hide_mobile', ['label' => __('Hide navigation social on mobile', 'pressroot'), 'section' => 'prt_nav_section', 'type' => 'checkbox']);
    $wp->add_setting('prt_social_nav_hide_desktop', ['default' => false, 'sanitize_callback' => 'wp_validate_boolean']);
    $wp->add_control('prt_social_nav_hide_desktop', ['label' => __('Hide navigation social on desktop', 'pressroot'), 'section' => 'prt_nav_section', 'type' => 'checkbox']);
    $wp->add_setting('prt_social_top_hide_mobile', ['default' => false, 'sanitize_callback' => 'wp_validate_boolean']);
    $wp->add_control('prt_social_top_hide_mobile', ['label' => __('Hide top bar social on mobile', 'pressroot'), 'section' => 'prt_nav_section', 'type' => 'checkbox']);
    // Hide CTA buttons on mobile (<=640px) per bar.
    $wp->add_setting('prt_topcta_hide_mobile', ['default' => false, 'sanitize_callback' => 'wp_validate_boolean']);
    $wp->add_control('prt_topcta_hide_mobile', ['label' => __('Hide top bar button on mobile', 'pressroot'), 'section' => 'prt_nav_section', 'type' => 'checkbox']);
    $wp->add_setting('prt_navcta_hide_mobile', ['default' => false, 'sanitize_callback' => 'wp_validate_boolean']);
    $wp->add_control('prt_navcta_hide_mobile', ['label' => __('Hide navbar button on mobile', 'pressroot'), 'section' => 'prt_nav_section', 'type' => 'checkbox']);
    $wp->add_setting('prt_navcta_hide_tablet', ['default' => false, 'sanitize_callback' => 'wp_validate_boolean']);
    $wp->add_control('prt_navcta_hide_tablet', ['label' => __('Hide navbar button on tablet', 'pressroot'), 'section' => 'prt_nav_section', 'type' => 'checkbox']);
    // Keep the top bar on one line at tablet widths (641–1024px) instead of wrapping.
    $wp->add_setting('prt_topbar_oneline_tablet', ['default' => false, 'sanitize_callback' => 'wp_validate_boolean']);
    $wp->add_control('prt_topbar_oneline_tablet', ['label' => __('Keep top bar on one line (tablet)', 'pressroot'), 'description' => __('Prevents the top bar from wrapping at 641–1024px.', 'pressroot'), 'section' => 'prt_nav_section', 'type' => 'checkbox']);
    // Shrink the logo area on mobile so the header fits on one row.
    $wp->add_setting('prt_logo_shrink_mobile', ['default' => false, 'sanitize_callback' => 'wp_validate_boolean']);
    $wp->add_control('prt_logo_shrink_mobile', ['label' => __('Shrink logo on mobile (fit one row)', 'pressroot'), 'description' => __('Smaller logo mark + name and hides the tagline at ≤640px.', 'pressroot'), 'section' => 'prt_nav_section', 'type' => 'checkbox']);
    // Hide the "Menu" text on the popout button on mobile (icon only).
    $wp->add_setting('prt_menu_label_hide_mobile', ['default' => false, 'sanitize_callback' => 'wp_validate_boolean']);
    $wp->add_control('prt_menu_label_hide_mobile', ['label' => __('Hide "Menu" label on mobile (icon only)', 'pressroot'), 'section' => 'prt_nav_section', 'type' => 'checkbox']);
    $sel($wp, 'prt_social_style', __('Social links display', 'pressroot'), ['text' => __('Text', 'pressroot'), 'icons' => __('Icons', 'pressroot')], 'icons');
    $sel($wp, 'prt_social_size', __('Social icon size', 'pressroot'), ['14' => '14px', '16' => '16px', '18' => '18px', '20' => '20px', '24' => '24px', '28' => '28px'], '18');
    $sel($wp, 'prt_social_shape', __('Social icon shape', 'pressroot'), ['none' => __('Plain', 'pressroot'), 'circle' => __('Circle', 'pressroot'), 'rounded' => __('Rounded', 'pressroot'), 'square' => __('Square', 'pressroot')], 'none');
    foreach ([['prt_social_color', __('Social icon color', 'pressroot')], ['prt_social_bg', __('Social icon background (chip)', 'pressroot')], ['prt_social_hover', __('Social icon hover color', 'pressroot')]] as $cc) {
        $wp->add_setting($cc[0], ['default' => '', 'sanitize_callback' => 'sanitize_hex_color']);
        $wp->add_control(new \WP_Customize_Color_Control($wp, $cc[0], ['label' => $cc[1], 'section' => 'prt_nav_section']));
    }

    $ord = ['1' => __('1 (top)', 'pressroot'), '2' => '2', '3' => __('3 (bottom)', 'pressroot')];
    $sel($wp, 'prt_bar_ann', __('Stack order: Announcement bar', 'pressroot'), $ord, '1');
    $sel($wp, 'prt_bar_top', __('Stack order: Top bar', 'pressroot'), $ord, '2');
    $sel($wp, 'prt_bar_nav', __('Stack order: Navigation bar', 'pressroot'), $ord, '3');

    $sel($wp, 'prt_popout_width', __('Popout width', 'pressroot'), [
        '0' => __('Default', 'pressroot'), '320' => '320px', '360' => '360px', '420' => '420px',
        '520' => '520px', '640' => '640px', '760' => '760px', '900' => '900px',
    ], '0');
    $sel($wp, 'prt_popout_cols', __('Popout MENU columns (desktop)', 'pressroot'), ['1' => '1', '2' => '2', '3' => '3', '4' => '4'], '1');
    $sel($wp, 'prt_popout_block_cols', __('Popout BLOCK columns (desktop)', 'pressroot'), ['1' => '1', '2' => '2', '3' => '3', '4' => '4'], '1');

    // Item alignment for the Top bar (added to the Top Bar section) and Message bar (Announcement section).
    $jal = ['none' => __('Default', 'pressroot'), 'left' => __('Left', 'pressroot'), 'center' => __('Center', 'pressroot'), 'right' => __('Right', 'pressroot'), 'between' => __('Space between', 'pressroot')];
    $wp->add_setting('prt_topbar_align', ['default' => 'none', 'sanitize_callback' => 'sanitize_key']);
    $wp->add_control('prt_topbar_align', ['label' => __('Top bar item alignment', 'pressroot'), 'section' => 'prt_topbar_section', 'type' => 'select', 'choices' => $jal]);
    $wp->add_setting('prt_msgbar_align', ['default' => 'none', 'sanitize_callback' => 'sanitize_key']);
    $wp->add_control('prt_msgbar_align', ['label' => __('Message bar item alignment', 'pressroot'), 'section' => 'prt_ann_section', 'type' => 'select', 'choices' => $jal]);

    $wopts = ['0' => __('Default', 'pressroot'), 'full' => __('Full width', 'pressroot')] + (function_exists('App\\prt_width_options') ? prt_width_options() : []);
    $wp->add_setting('prt_topbar_width', ['default' => '0', 'sanitize_callback' => 'sanitize_text_field']);
    $wp->add_control('prt_topbar_width', ['label' => __('Top bar width', 'pressroot'), 'section' => 'prt_topbar_section', 'type' => 'select', 'choices' => $wopts]);
    $wp->add_setting('prt_msgbar_width', ['default' => '0', 'sanitize_callback' => 'sanitize_text_field']);
    $wp->add_control('prt_msgbar_width', ['label' => __('Message bar width', 'pressroot'), 'section' => 'prt_ann_section', 'type' => 'select', 'choices' => $wopts]);

    // Per-breakpoint inner widths for the three bars (constrain inner content; bg stays full-width).
    $barw = [
        'prt_topbar_width_tablet' => __('Top bar width (tablet)', 'pressroot'),
        'prt_topbar_width_mobile' => __('Top bar width (mobile)', 'pressroot'),
        'prt_nav_width_tablet'    => __('Navbar width (tablet)', 'pressroot'),
        'prt_nav_width_mobile'    => __('Navbar width (mobile)', 'pressroot'),
        'prt_msgbar_width_tablet' => __('Message bar width (tablet)', 'pressroot'),
        'prt_msgbar_width_mobile' => __('Message bar width (mobile)', 'pressroot'),
    ];
    foreach ($barw as $id => $label) {
        $wp->add_setting($id, ['default' => '0', 'sanitize_callback' => 'sanitize_text_field']);
        $wp->add_control($id, ['label' => $label, 'section' => 'prt_nav_section', 'type' => 'select', 'choices' => $wopts]);
    }
}, 13);

add_action('prt_head_end', function () {
    $css = '';

    $map = function ($v) {
        if ($v === 'left')   return 'margin-right:auto;';
        if ($v === 'right')  return 'margin-left:auto;';
        if ($v === 'center') return 'margin-left:auto;margin-right:auto;';
        return '';
    };
    foreach ([
        'prt_logo_align'     => '.site-header .brand',
        'prt_darkicon_align' => '.site-header .prt-theme-toggle',
        'prt_popbtn_align'   => '.site-header .menu-toggle',
        'prt_cta_align'      => '.header-actions .btn-hire',
    ] as $mod => $sel) {
        $m = $map(get_theme_mod($mod, 'none'));
        if ($m !== '') {
            $css .= $sel . '{' . $m . '}';
        }
    }
    $sa = $map(get_theme_mod('prt_nav_social_align', 'none'));
    if ($sa !== '') {
        $css .= '.site-header .header-social{' . $sa . '}';
    }

    // Hide social icons on mobile (<=640px) per bar.
    if (get_theme_mod('prt_social_nav_hide_mobile', false)) {
        $css .= '@media(max-width:640px){.site-header .header-social{display:none!important;}}';
    }
    if (get_theme_mod('prt_social_nav_hide_desktop', false)) {
        $css .= '@media(min-width:641px){.site-header .header-social{display:none!important;}}';
    }
    if (get_theme_mod('prt_social_top_hide_mobile', false)) {
        $css .= '@media(max-width:640px){.top-bar-social{display:none!important;}}';
    }
    if (get_theme_mod('prt_topcta_hide_mobile', false)) {
        $css .= '@media(max-width:640px){.top-bar-cta{display:none!important;}}';
    }
    if (get_theme_mod('prt_navcta_hide_mobile', false)) {
        $css .= '@media(max-width:640px){.header-actions .btn-hire{display:none!important;}}';
    }
    if (get_theme_mod('prt_navcta_hide_tablet', false)) {
        $css .= '@media(min-width:641px) and (max-width:1024px){.header-actions .btn-hire{display:none!important;}}';
    }
    if (get_theme_mod('prt_topbar_oneline_tablet', false)) {
        $css .= '@media(min-width:641px) and (max-width:1024px){.top-bar-inner{flex-wrap:nowrap;gap:12px;}.top-bar-contact{flex-wrap:nowrap;white-space:nowrap;}}';
    }
    if (get_theme_mod('prt_logo_shrink_mobile', false)) {
        $css .= '@media(max-width:640px){'
            . '.site-header-inner{gap:14px;}'
            . '.site-header .brand{gap:8px;}'
            . '.site-header .brand-mark svg{width:30px;height:30px;border-radius:8px;}'
            . '.site-header .brand-name{font-size:16px;}'
            . '}';
    }
    if (get_theme_mod('prt_menu_label_hide_mobile', false)) {
        $css .= '@media(max-width:640px){.menu-toggle .menu-toggle-label{display:none;}.menu-toggle{gap:0;}}';
    }

    // Social icon design (size/shape/color/gap/border/custom CSS) is handled
    // centrally for every location — header, footer, popout, and this bar —
    // in app/social-icon-style.php, so the rules aren't duplicated here.

    // Reorder the three top bars (only when changed from default 1/2/3).
    $ann = absint(get_theme_mod('prt_bar_ann', 1));
    $top = absint(get_theme_mod('prt_bar_top', 2));
    $nav = absint(get_theme_mod('prt_bar_nav', 3));
    if (! ($ann === 1 && $top === 2 && $nav === 3)) {
        $css .= '#app{display:flex;flex-direction:column;}';
        $css .= '#app > a:first-child{order:-10;}';
        $css .= '.prt-ann{order:' . $ann . ';}.top-bar{order:' . $top . ';}.site-header{order:' . $nav . ';}';
        $css .= '.prt-popout-overlay,#prt-popout{order:0;}';
        $css .= '.main-wrap{order:90;}.content-info{order:91;}';
    }

    // Popout width + desktop columns.
    $w = absint(get_theme_mod('prt_popout_width', 0));
    if ($w) {
        $css .= '#prt-popout.prt-popout{width:' . $w . 'px;max-width:92vw;}';
    }
    $cols = absint(get_theme_mod('prt_popout_cols', 1));
    if ($cols > 1) {
        $css .= '@media(min-width:1024px){#prt-popout .prt-popout-menu{display:grid;grid-template-columns:repeat(' . $cols . ',minmax(0,1fr));gap:6px 28px;align-items:start;}}';
    }

    // Top bar / message bar item alignment.
    $jmap = function ($v) {
        switch ($v) {
            case 'left':    return 'flex-start';
            case 'center':  return 'center';
            case 'right':   return 'flex-end';
            case 'between': return 'space-between';
        }
        return '';
    };
    $tba = $jmap(get_theme_mod('prt_topbar_align', 'none'));
    if ($tba) {
        $css .= '.top-bar-inner{display:flex;align-items:center;gap:16px;justify-content:' . $tba . ';}';
    }
    $mba = $jmap(get_theme_mod('prt_msgbar_align', 'none'));
    if ($mba) {
        $css .= '.prt-ann-inner{display:flex;align-items:center;gap:14px;text-align:left;justify-content:' . $mba . ';}';
    }

    // Bar widths (constrain the inner content; background stays full-width).
    $bw = function ($v) {
        if ($v === 'full') {
            return 'max-width:none;';
        }
        if ($v && $v !== '0') {
            return 'max-width:' . absint($v) . 'px;';
        }
        return '';
    };
    $tw = $bw(get_theme_mod('prt_topbar_width', '0'));
    if ($tw !== '') {
        $css .= '.top-bar .top-bar-inner{' . $tw . '}';
    }
    $mw = $bw(get_theme_mod('prt_msgbar_width', '0'));
    if ($mw !== '') {
        $css .= '.prt-ann .prt-ann-inner{' . $mw . '}';
    }

    // Per-breakpoint bar widths (tablet 641–1024px, mobile ≤640px). Navbar = .site-header-inner.
    $tabletW = '';
    foreach ([['prt_topbar_width_tablet', '.top-bar .top-bar-inner'], ['prt_nav_width_tablet', '.site-header-inner'], ['prt_msgbar_width_tablet', '.prt-ann .prt-ann-inner']] as $r) {
        $v = $bw(get_theme_mod($r[0], '0'));
        if ($v !== '') {
            $tabletW .= $r[1] . '{' . $v . '}';
        }
    }
    if ($tabletW !== '') {
        $css .= '@media(min-width:641px) and (max-width:1024px){' . $tabletW . '}';
    }
    $mobileW = '';
    foreach ([['prt_topbar_width_mobile', '.top-bar .top-bar-inner'], ['prt_nav_width_mobile', '.site-header-inner'], ['prt_msgbar_width_mobile', '.prt-ann .prt-ann-inner']] as $r) {
        $v = $bw(get_theme_mod($r[0], '0'));
        if ($v !== '') {
            $mobileW .= $r[1] . '{' . $v . '}';
        }
    }
    if ($mobileW !== '') {
        $css .= '@media(max-width:640px){' . $mobileW . '}';
    }

    // Tablet: push the menu (popout) button to the right edge so its inset mirrors
    // the logo's left padding (both sit at the header's 28px side padding).
    $css .= '@media(min-width:641px) and (max-width:1024px){'
        . '.site-header .brand{margin-right:auto;}'
        . '.site-header .menu-toggle{margin-left:0;padding-right:0;}'
        . '}';

    // Base styling for blocks placed in the bars. This used to be gated behind
    // is_active_sidebar('topbar' | 'messagebar' | 'navbar'), but those sidebar
    // IDs are never registered anywhere in the theme (see the comment below,
    // "the top bar, message bar, and navigation bar are configured via Theme
    // Options, not widgets") — so that check was always false and this CSS
    // never actually shipped. The bar-item blocks (prt/bar-social, prt/bar-cta,
    // etc. in app/bar-blocks.php) can be placed directly in the bars via the
    // block editor without any widget area, so the rules are now unconditional.
    $css .= '.top-bar-blocks,.prt-ann-blocks,.nav-blocks{display:inline-flex;align-items:center;gap:14px;}'
        . '.top-bar-blocks *,.prt-ann-blocks *,.nav-blocks *{color:inherit;}'
        . '.nav-blocks{margin-left:8px;}';

    if ($css !== '') {
        echo "\n<style id=\"prt-header-elements\">" . $css . "</style>\n";
    }
}, 14);


/* ── postMessage preview handler for nav social position ─────────────────── */
add_action('customize_preview_init', function () {
    wp_add_inline_script(
        'customize-preview',
        "(function(){
            var alignMap = { left: 'margin-right:auto;', right: 'margin-left:auto;', center: 'margin-left:auto;margin-right:auto;' };
            wp.customize('prt_nav_social_align', function(setting){
                setting.bind(function(val){
                    var el = document.getElementById('prt-nav-social-pos');
                    if (!el) {
                        el = document.createElement('style');
                        el.id = 'prt-nav-social-pos';
                        document.head.appendChild(el);
                    }
                    el.textContent = alignMap[val] ? '.site-header .header-social{' + alignMap[val] + '}' : '';
                });
            });
        })();"
    );
});

/** Block widget columns for the off-canvas popout (Appearance -> Widgets). */
add_action('widgets_init', function () {
    for ($i = 1; $i <= 4; $i++) {
        register_sidebar([
            'name'          => sprintf(__('Popout column %d', 'pressroot'), $i),
            'id'            => "popout-{$i}",
            'description'   => __('Blocks shown in the off-canvas popout panel. Column count is set in Customize -> Navigation.', 'pressroot'),
            'before_widget' => '<div class="prt-pop-widget %2$s">',
            'after_widget'  => '</div>',
            'before_title'  => '<h4 class="prt-pop-widget-title">',
            'after_title'   => '</h4>',
        ]);
    }
    // The top bar, message bar, and navigation bar are configured via Theme Options
    // (Customizer), not widgets — so no bar widget areas are registered here.
});

/** Popout block-column layout (only when popout widgets exist). */
add_action('prt_head_end', function () {
    $active = false;
    for ($i = 1; $i <= 4; $i++) {
        if (is_active_sidebar("popout-{$i}")) { $active = true; break; }
    }
    if (! $active) {
        return;
    }
    echo "\n<style id=\"prt-popout-blocks\">"
        . '.prt-popout-blocks{display:grid;gap:18px 28px;margin-top:22px;}'
        . '.prt-popout-blocks,.prt-popout-blocks a,.prt-popout-blocks *{color:inherit;}'
        . '.prt-pop-widget-title{font-size:13px;text-transform:uppercase;letter-spacing:.08em;opacity:.7;margin:0 0 8px;}'
        . '@media(min-width:1024px){.prt-popout-blocks--cols-2{grid-template-columns:repeat(2,1fr);}.prt-popout-blocks--cols-3{grid-template-columns:repeat(3,1fr);}.prt-popout-blocks--cols-4{grid-template-columns:repeat(4,1fr);}}'
        . "</style>\n";
}, 15);
