<?php

/**
 * Per-page hero styling: full typography control for each hero section
 * (eyebrow, heading, subtitle, buttons) plus per-button style, driven by the
 * page's own fields. Registered field rows come from hero_button_rows() /
 * hero_typography_rows() (added to a template's field map in page-fields.php);
 * this module turns the saved values into scoped, sanitized CSS at render time.
 *
 * Safety: every value that reaches CSS is whitelisted (font family, weight,
 * text-transform, button style) or pattern-validated (size, letter-spacing,
 * hex color). Nothing user-typed is emitted verbatim. The <style> block is
 * printed only on the page being viewed, so `.rv-hero …` selectors stay
 * page-scoped without needing a per-page wrapper class.
 *
 * Draft-aware: values are read through \App\field(), so the editor's live
 * preview reflects unsaved changes as you type (markers stripped for CSS use).
 */

namespace App;

/** Hero font-family choices (field value => CSS). */
function hero_font_stacks(): array
{
    return [
        'display' => 'var(--font-display)',
        'serif'   => 'var(--font-serif)',
        'mono'    => 'var(--font-mono)',
        'body'    => 'var(--font-body)',
    ];
}

/** Hero section key => CSS selector (page-scoped; the <style> only prints here). */
function hero_style_sections(): array
{
    return [
        'eyebrow' => '.rv-hero .rv-eyebrow',
        'title'   => '.rv-hero .rv-hero-title',
        'sub'     => '.rv-hero .rv-hero-sub',
        'btns'    => '.rv-hero .rv-hero-actions .rv-btn',
    ];
}

/** Validate a CSS font-size (length or clamp/min/max); '' if not allowed. */
function hero_css_size(string $v): string
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

/** Validate a CSS letter-spacing; '' if not allowed. */
function hero_css_spacing(string $v): string
{
    $v = trim($v);
    if ($v === '' ) {
        return '';
    }
    if ($v === 'normal' || preg_match('/^-?\d*\.?\d+(px|rem|em)$/i', $v)) {
        return $v;
    }
    return '';
}

/** Draft-aware raw field value with any preview markers stripped. */
function hero_style_val(string $key, ?int $post_id): string
{
    return strip_field_markers((string) field($key, '', $post_id));
}

/** Map a hero button style choice to a CSS class, falling back per template. */
function hero_btn_class(string $style, string $fallback): string
{
    $map = ['primary' => 'rv-btn-primary', 'ghost' => 'rv-btn-ghost'];
    return $map[strip_field_markers($style)] ?? $fallback;
}

/** Build the per-section hero CSS declarations for a page (empty if none set). */
function hero_style_declarations(int $post_id): string
{
    $fonts      = hero_font_stacks();
    $weights    = ['400', '500', '600', '700', '800'];
    $transforms = ['none', 'uppercase', 'lowercase', 'capitalize'];

    $css = '';
    foreach (hero_style_sections() as $sec => $selector) {
        $d = [];

        $f = hero_style_val("hero_{$sec}_font", $post_id);
        if (isset($fonts[$f])) {
            $d[] = 'font-family:' . $fonts[$f];
        }
        $size = hero_css_size(hero_style_val("hero_{$sec}_size", $post_id));
        if ($size !== '') {
            $d[] = 'font-size:' . $size;
        }
        $w = hero_style_val("hero_{$sec}_weight", $post_id);
        if (in_array($w, $weights, true)) {
            $d[] = 'font-weight:' . $w;
        }
        $ls = hero_css_spacing(hero_style_val("hero_{$sec}_ls", $post_id));
        if ($ls !== '') {
            $d[] = 'letter-spacing:' . $ls;
        }
        $tr = hero_style_val("hero_{$sec}_transform", $post_id);
        if (in_array($tr, $transforms, true)) {
            $d[] = 'text-transform:' . $tr;
        }
        $color = sanitize_hex_color(hero_style_val("hero_{$sec}_color", $post_id));
        if ($color) {
            $d[] = 'color:' . $color;
        }

        if ($d) {
            // The buttons selector needs a nudge in specificity to beat .rv-btn.
            $sel = $sec === 'btns' ? 'body ' . $selector : $selector;
            $css .= $sel . '{' . implode(';', $d) . '}';
        }
    }
    return $css;
}

/** Emit the page's hero typography CSS in <head>. */
add_action('wp_head', function () {
    if (! is_singular() && ! is_front_page()) {
        return;
    }
    $post_id = (int) get_queried_object_id();
    if (! $post_id) {
        return;
    }
    $css = hero_style_declarations($post_id);
    if ($css !== '') {
        echo '<style id="rv-hero-style">' . $css . "</style>\n";
    }
}, 23);

/* -------------------------------------------------------------------------
 * Field-map rows (added to a template's hero fields in page-fields.php).
 * ---------------------------------------------------------------------- */

/**
 * Hero button fields: editable label + fill/ghost style per button. Pass each
 * button's current on-page label so the editor placeholder matches the page.
 * A blank $b2 yields a single-button group.
 */
function hero_button_rows(string $b1 = 'Plan my site', string $b2 = 'See the process'): array
{
    $style = [
        ''        => __('Theme default', 'sage'),
        'primary' => __('Primary (filled)', 'sage'),
        'ghost'   => __('Ghost (outline)', 'sage'),
    ];
    $rows = [
        ['hero_btn1', __('Button 1 · label', 'sage'), 'text', $b1],
        ['hero_btn1_style', __('Button 1 · style', 'sage'), 'select', '', $style],
    ];
    if ($b2 !== '') {
        $rows[] = ['hero_btn2', __('Button 2 · label', 'sage'), 'text', $b2];
        $rows[] = ['hero_btn2_style', __('Button 2 · style', 'sage'), 'select', '', $style];
    }
    return $rows;
}

/** Full per-section typography controls (font, size, weight, spacing, case, color). */
function hero_typography_rows(): array
{
    $fonts = [
        ''        => __('Theme default', 'sage'),
        'display' => __('Outfit (display / sans)', 'sage'),
        'serif'   => __('Instrument Serif', 'sage'),
        'mono'    => __('JetBrains Mono', 'sage'),
        'body'    => __('Body', 'sage'),
    ];
    $weights = ['' => __('Default', 'sage'), '400' => '400', '500' => '500', '600' => '600', '700' => '700', '800' => '800'];
    $cases   = [
        ''           => __('Default', 'sage'),
        'none'       => __('None', 'sage'),
        'uppercase'  => __('UPPERCASE', 'sage'),
        'lowercase'  => __('lowercase', 'sage'),
        'capitalize' => __('Capitalize', 'sage'),
    ];

    $sections = [
        'eyebrow' => __('Eyebrow', 'sage'),
        'title'   => __('Heading', 'sage'),
        'sub'     => __('Subtitle', 'sage'),
        'btns'    => __('Buttons', 'sage'),
    ];

    $rows = [];
    foreach ($sections as $k => $label) {
        $rows[] = ["hero_{$k}_font", sprintf(__('%s · font', 'sage'), $label), 'select', '', $fonts];
        $rows[] = ["hero_{$k}_size", sprintf(__('%s · font size', 'sage'), $label), 'text', __('e.g. 1.1rem or clamp(2rem,5vw,4rem)', 'sage')];
        $rows[] = ["hero_{$k}_weight", sprintf(__('%s · weight', 'sage'), $label), 'select', '', $weights];
        $rows[] = ["hero_{$k}_ls", sprintf(__('%s · letter-spacing', 'sage'), $label), 'text', __('e.g. 0.06em or -0.02em', 'sage')];
        $rows[] = ["hero_{$k}_transform", sprintf(__('%s · text case', 'sage'), $label), 'select', '', $cases];
        $rows[] = ["hero_{$k}_color", sprintf(__('%s · color (hex)', 'sage'), $label), 'text', __('#RRGGBB', 'sage')];
    }
    return $rows;
}
