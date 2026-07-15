<?php

/**
 * Advanced typography: assign fonts + weights per element (headings, body, nav,
 * buttons), nav casing, body letter-spacing, and responsive base font sizes.
 * Reuses \App\prt_fonts() for the font list + CSS stacks and loads any extra
 * Google families the nav/buttons need.
 *
 * This is deliberately split from the main "Typography" Customizer section
 * (heading/body font family, in customizer.php) into its own "Typography
 * (advanced)" section, so casual users aren't overwhelmed by weight/casing/
 * responsive-size controls most sites never touch. All functions here are
 * defensive about \App\prt_fonts() not existing (checked via function_exists)
 * so this file degrades gracefully if the font-registry file is ever removed
 * or reordered in the collect()->each() bootstrap list in functions.php.
 */

namespace App;

/**
 * Build the <select> choices list for "which registered font" controls:
 * "Default (inherit)" plus every font name known to \App\prt_fonts().
 * Falls back to just the Default option if prt_fonts() isn't available yet.
 */
function prt_type_font_choices()
{
    $choices = ['Default' => __('Default (inherit)', 'pressroot')];
    if (function_exists('App\\prt_fonts')) {
        foreach (array_keys(prt_fonts()) as $n) {
            $choices[$n] = $n;
        }
    }
    return $choices;
}

/**
 * Register the "Typography (advanced)" Customizer section: nav/button font
 * pickers, per-element weight selects, nav letter-casing, body letter-
 * spacing, and tablet/mobile base-size overrides.
 */
add_action('customize_register', function ($wp) {
    // Shared guarded helper — see prt_ensure_theme_options_panel() in app/customizer.php.
    prt_ensure_theme_options_panel($wp);
    $wp->add_section('prt_type_adv', [
        'title' => __('Typography (advanced)', 'pressroot'),
        'panel' => 'prt_theme_options',
        'description' => __('Fine-grained control per element. Heading and body font families live in the main Typography section; this adds nav/buttons, weights, and responsive sizes.', 'pressroot'),
    ]);

    $fontChoices = prt_type_font_choices();
    // Local helper so each select control below is a single line instead of
    // a repeated add_setting()/add_control() pair.
    $sel = function ($wp, $id, $label, $choices, $default) {
        $wp->add_setting($id, ['default' => $default, 'sanitize_callback' => 'sanitize_text_field']);
        $wp->add_control($id, ['label' => $label, 'section' => 'prt_type_adv', 'type' => 'select', 'choices' => $choices]);
    };

    $sel($wp, 'prt_font_nav', __('Navigation font', 'pressroot'), $fontChoices, 'Default');
    $sel($wp, 'prt_font_button', __('Button font', 'pressroot'), $fontChoices, 'Default');

    // NOTE(audit): filter tag uses the 'pressroot/' vendor prefix while every
    // other extension point in this theme uses 'prt_'/'pressroot' naming
    // (e.g. prt_full_block_supports, prt-* CSS classes). Likely a leftover
    // from before the theme was renamed to Pressroot — worth aligning if this
    // becomes a public filter other developers are expected to hook into.
    $weights = apply_filters('pressroot/font_weights', ['300' => '300 Light', '400' => '400 Regular', '500' => '500 Medium', '600' => '600 Semibold', '700' => '700 Bold', '800' => '800 Extrabold']);
    $sel($wp, 'prt_weight_heading', __('Heading weight', 'pressroot'), $weights, '600');
    $sel($wp, 'prt_weight_body', __('Body weight', 'pressroot'), ['300' => '300 Light', '400' => '400 Regular', '500' => '500 Medium'], '400');
    $sel($wp, 'prt_weight_nav', __('Nav weight', 'pressroot'), $weights, '500');
    $sel($wp, 'prt_weight_button', __('Button weight', 'pressroot'), $weights, '600');

    $sel($wp, 'prt_nav_case', __('Nav letter case', 'pressroot'), ['none' => __('Normal', 'pressroot'), 'uppercase' => __('UPPERCASE', 'pressroot'), 'lowercase' => __('lowercase', 'pressroot')], 'none');
    $sel($wp, 'prt_ls_body', __('Body letter spacing', 'pressroot'), ['-0.01em' => 'Tight', '0' => 'Normal', '0.01em' => 'Loose'], '0');

    $px = ['auto' => __('Auto (use base)', 'pressroot'), '14' => '14px', '15' => '15px', '16' => '16px', '17' => '17px', '18' => '18px'];
    $sel($wp, 'prt_base_tablet', __('Base font on tablet', 'pressroot'), $px, 'auto');
    $sel($wp, 'prt_base_mobile', __('Base font on mobile', 'pressroot'), $px, 'auto');
}, 24);

/**
 * Enqueue a second Google Fonts stylesheet for the nav/button fonts, but only
 * when a site owner has picked something other than "Default" for either —
 * the main heading/body fonts are already loaded elsewhere (customizer.php),
 * so this avoids requesting the same family twice or loading fonts nobody
 * selected. $want is keyed by Google Fonts family query string to naturally
 * de-duplicate if nav and button end up using the same font.
 */
add_action('wp_enqueue_scripts', function () {
    if (! function_exists('App\\prt_fonts')) {
        return;
    }
    $fonts = prt_fonts();
    $want  = [];
    foreach (['prt_font_nav', 'prt_font_button'] as $k) {
        $name = get_theme_mod($k, 'Default');
        if ($name !== 'Default' && isset($fonts[$name]) && ! empty($fonts[$name][0])) {
            $want[$fonts[$name][0]] = true;
        }
    }
    if ($want) {
        wp_enqueue_style(
            'prt-fonts-extra',
            'https://fonts.googleapis.com/css2?family=' . implode('&family=', array_keys($want)) . '&display=swap',
            [],
            null
        );
    }
}, 9);

/**
 * Emit the CSS that actually applies every advanced-typography setting: nav/
 * button font-family + weight, nav casing, heading/body weight, body letter-
 * spacing, and tablet/mobile responsive base sizes. Hooked to the theme's
 * custom `prt_head_end` action at priority 17 (after extras.php's priority-14
 * base styles) so these more specific per-element rules win the cascade
 * without needing !important.
 */
add_action('prt_head_end', function () {
    $fonts = function_exists('App\\prt_fonts') ? prt_fonts() : [];
    $stack = function ($name) use ($fonts) {
        return ($name !== 'Default' && isset($fonts[$name][1])) ? $fonts[$name][1] : '';
    };
    $css = '';

    $nav = $stack(get_theme_mod('prt_font_nav', 'Default'));
    $btn = $stack(get_theme_mod('prt_font_button', 'Default'));
    $navCss = ($nav ? 'font-family:' . $nav . ';' : '') . 'font-weight:' . absint(get_theme_mod('prt_weight_nav', 500)) . ';';
    $case = get_theme_mod('prt_nav_case', 'none');
    if (in_array($case, ['uppercase', 'lowercase'], true)) {
        // Uppercase nav text reads as visually tighter, so a small positive
        // letter-spacing bump is added automatically to keep it legible;
        // lowercase doesn't need the same compensation.
        $navCss .= 'text-transform:' . $case . ';' . ($case === 'uppercase' ? 'letter-spacing:.04em;' : '');
    }
    $css .= '.header-nav-list a{' . $navCss . '}';

    $btnCss = ($btn ? 'font-family:' . $btn . ';' : '') . 'font-weight:' . absint(get_theme_mod('prt_weight_button', 600)) . ';';
    $css .= '.btn,.btn-outline,.wp-element-button,.wp-block-button__link,button.btn{' . $btnCss . '}';

    $css .= 'h1,h2,h3,h4,h5,h6{font-weight:' . absint(get_theme_mod('prt_weight_heading', 600)) . ';}';

    // Whitelist-strip the letter-spacing value to digits/dot/minus/"em" (same
    // technique as extras.php) so an unsanitized theme_mod can't break out of
    // the inline <style> tag; falls back to '0' if stripping leaves nothing.
    $ls = preg_replace('/[^0-9.\-em]/', '', (string) get_theme_mod('prt_ls_body', '0'));
    $css .= 'body{font-weight:' . absint(get_theme_mod('prt_weight_body', 400)) . ';letter-spacing:' . ($ls === '' ? '0' : $ls) . ';}';

    // 1024px/600px match the tablet/mobile breakpoints used elsewhere in the
    // theme's compiled CSS, kept in sync manually since this file emits raw
    // inline CSS rather than pulling from a shared SASS breakpoint variable.
    $t = get_theme_mod('prt_base_tablet', 'auto');
    $m = get_theme_mod('prt_base_mobile', 'auto');
    if ($t !== 'auto') {
        $css .= '@media(max-width:1024px){body{font-size:' . absint($t) . 'px;}}';
    }
    if ($m !== 'auto') {
        $css .= '@media(max-width:600px){body{font-size:' . absint($m) . 'px;}}';
    }

    echo "\n<style id=\"prt-typography\">" . $css . "</style>\n";
}, 17);
