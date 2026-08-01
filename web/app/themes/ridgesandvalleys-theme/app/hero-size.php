<?php

/**
 * Hero copy width + font-size controls
 * (Customizer → Theme Options → Layout). Global — applies to every hero.
 *
 * Every value that reaches CSS is whitelisted (width preset) or
 * pattern-validated (custom width, font sizes), so a theme_mod can never inject
 * arbitrary CSS. Nothing user-typed is emitted verbatim.
 *
 * Controls:
 *   - rv_hero_width        : Full / Two-thirds / Half / Custom  (max-width of the copy block)
 *   - rv_hero_width_custom : the value used when "Custom" (e.g. 60%, 640px, 48rem)
 *   - rv_hero_title_size   : overall hero heading size  (px/rem/%/clamp)
 *   - rv_hero_sub_size     : overall hero subtitle size
 *
 * These are site-wide defaults. The per-page "Hero typography" fields (added in
 * page-fields.php, emitted at wp_head priority 23) still win where a page sets
 * its own sizes, because this module prints earlier (priority 20).
 */

namespace App;

/** Validate a CSS length / clamp() value; '' if not allowed. */
function hero_size_css(string $v): string
{
    $v = trim($v);
    if ($v === '') {
        return '';
    }
    if (preg_match('/^-?\d*\.?\d+(px|rem|em|vw|vh|%)$/i', $v)) {
        return $v;
    }
    if (preg_match('/^(clamp|min|max)\(\s*-?\d*\.?\d+(px|rem|em|vw|vh|%)?\s*(,\s*-?\d*\.?\d+(px|rem|em|vw|vh|%)?\s*){1,3}\)$/i', $v)) {
        return $v;
    }
    return '';
}

/** Resolve the hero copy max-width from the width preset / custom value ('' = full). */
function hero_width_value(): string
{
    $preset = (string) get_theme_mod('rv_hero_width', '');
    if ($preset === 'custom') {
        return hero_size_css((string) get_theme_mod('rv_hero_width_custom', ''));
    }
    $map = ['half' => '50%', 'twothirds' => '66%'];
    return $map[$preset] ?? '';
}

/* Register the controls inside the existing Layout section. Priority 20 so the
 * section (created by customizer.php on the default-priority hook) already exists. */
add_action('customize_register', function ($wp_customize) {
    $sec = 'rv_layout';

    $wp_customize->add_setting('rv_hero_width', ['default' => '', 'sanitize_callback' => 'sanitize_key']);
    $wp_customize->add_control('rv_hero_width', [
        'label'       => __('Hero copy width', 'sage'),
        'description' => __('How wide the hero text block may get on desktop. Pick a preset, or choose "Custom" and enter a value below. Full-width on phones regardless.', 'sage'),
        'section'     => $sec,
        'type'        => 'select',
        'priority'    => 12,
        'choices'     => [
            ''          => __('Full width', 'sage'),
            'twothirds' => __('Two-thirds', 'sage'),
            'half'      => __('Half', 'sage'),
            'custom'    => __('Custom (enter below)', 'sage'),
        ],
    ]);

    $wp_customize->add_setting('rv_hero_width_custom', ['default' => '', 'sanitize_callback' => 'sanitize_text_field']);
    $wp_customize->add_control('rv_hero_width_custom', [
        'label'       => __('Hero copy width — custom', 'sage'),
        'description' => __('Used when "Custom" is selected above. Accepts %, px, rem, em or vw — e.g. 60%, 640px, 48rem.', 'sage'),
        'section'     => $sec,
        'type'        => 'text',
        'priority'    => 13,
    ]);

    $wp_customize->add_setting('rv_hero_title_size', ['default' => '', 'sanitize_callback' => 'sanitize_text_field']);
    $wp_customize->add_control('rv_hero_title_size', [
        'label'       => __('Hero heading size', 'sage'),
        'description' => __('Overall size of the hero headline. Accepts px, rem, % or clamp() — e.g. 3rem or clamp(2rem, 5vw, 4.5rem). Blank = theme default.', 'sage'),
        'section'     => $sec,
        'type'        => 'text',
        'priority'    => 14,
    ]);

    $wp_customize->add_setting('rv_hero_sub_size', ['default' => '', 'sanitize_callback' => 'sanitize_text_field']);
    $wp_customize->add_control('rv_hero_sub_size', [
        'label'       => __('Hero subtitle size', 'sage'),
        'description' => __('Overall size of the paragraph under the hero heading. Same formats as above.', 'sage'),
        'section'     => $sec,
        'type'        => 'text',
        'priority'    => 15,
    ]);
}, 20);

/* Emit the global hero width + size CSS. Priority 20 keeps it before the
 * per-page Hero typography emitter (priority 23), so per-page sizes still win. */
add_action('wp_head', function () {
    $css = '';

    // Width caps the copy block on desktop only, so phones stay full-width/readable.
    $w = hero_width_value();
    if ($w !== '') {
        $css .= '@media(min-width:782px){.rv-hero .rv-hero-inner{max-width:' . $w . '}}';
    }

    $ts = hero_size_css((string) get_theme_mod('rv_hero_title_size', ''));
    if ($ts !== '') {
        $css .= '.rv-hero .rv-hero-title{font-size:' . $ts . '}';
    }
    $ss = hero_size_css((string) get_theme_mod('rv_hero_sub_size', ''));
    if ($ss !== '') {
        $css .= '.rv-hero .rv-hero-sub{font-size:' . $ss . '}';
    }

    if ($css !== '') {
        echo '<style id="rv-hero-size">' . $css . "</style>\n";
    }
}, 20);
