<?php

/**
 * Header / Footer designer: Kadence-style layout presets + WCAG contrast guard.
 *
 * Three jobs, one file:
 *
 * 1. CONTRAST GUARD (accessibility). The palette mods (prt_color_paper/ink/
 *    body) and nav colors are user- and AI-writable, so nothing used to stop
 *    a light heading color from landing on the light header bar. A late
 *    `prt_head_end` style block (priority 17) re-derives readable text colors
 *    from the *actual* background luminance and overrides anything that would
 *    fall below WCAG AA (4.5:1 text, 3:1 UI), without touching the DB.
 *
 * 2. PRESETS. prt_header_presets() / prt_footer_presets() are batches of the
 *    theme_mods that already drive the header stack (announcement bar → top
 *    bar → main bar), transparent overlay behavior, and the footer builder.
 *    Applying a preset = set_theme_mod() loop; the blades/hooks already react.
 *
 * 3. DESIGNER UI. A shared renderer used by BOTH the Setup wizard "Design"
 *    step and the standalone "Header & Footer" tab on the Pressroot settings
 *    page (filter pressroot/settings_tabs). Saves through admin-post like
 *    every other settings surface (prt_save_design).
 */

namespace App;

/* ──────────────────────────────────────────────────────────────────────────
 * Contrast math (WCAG 2.1 relative luminance)
 * ────────────────────────────────────────────────────────────────────── */

/** '#abc' / '#aabbcc' → [r,g,b] 0-255, or null when unparseable. */
function prt_hex_rgb(string $hex): ?array
{
    $hex = ltrim(trim($hex), '#');
    if (strlen($hex) === 3) {
        $hex = $hex[0] . $hex[0] . $hex[1] . $hex[1] . $hex[2] . $hex[2];
    }
    if (! preg_match('/^[0-9a-fA-F]{6}$/', $hex)) {
        return null;
    }
    return [hexdec(substr($hex, 0, 2)), hexdec(substr($hex, 2, 2)), hexdec(substr($hex, 4, 2))];
}

/** WCAG relative luminance, 0 (black) … 1 (white). */
function prt_luminance(string $hex): float
{
    $rgb = prt_hex_rgb($hex);
    if (! $rgb) {
        return 0.5;
    }
    $ch = array_map(function ($c) {
        $c /= 255;
        return $c <= 0.03928 ? $c / 12.92 : pow(($c + 0.055) / 1.055, 2.4);
    }, $rgb);
    return 0.2126 * $ch[0] + 0.7152 * $ch[1] + 0.0722 * $ch[2];
}

/** WCAG contrast ratio between two hex colors, 1 … 21. */
function prt_contrast(string $a, string $b): float
{
    $l1 = prt_luminance($a);
    $l2 = prt_luminance($b);
    return (max($l1, $l2) + 0.05) / (min($l1, $l2) + 0.05);
}

/** Is this surface "dark" for text-color purposes? */
function prt_is_dark(string $hex): bool
{
    return prt_luminance($hex) < 0.4;
}

/** Lighten (+) or darken (−) a hex color by a 0-100 percentage. */
function prt_shade(string $hex, int $percent): string
{
    $rgb = prt_hex_rgb($hex);
    if (! $rgb) {
        return $hex;
    }
    $out = array_map(function ($c) use ($percent) {
        $c = $percent >= 0
            ? $c + (255 - $c) * ($percent / 100)
            : $c * (1 + $percent / 100);
        return str_pad(dechex((int) round(max(0, min(255, $c)))), 2, '0', STR_PAD_LEFT);
    }, $rgb);
    return '#' . implode('', $out);
}

/** The theme's readable text color for a given background (ink or paper). */
function prt_readable_on(string $bg): string
{
    $ink   = get_theme_mod('prt_color_ink', '#17151F');
    $paper = '#FFF9F5';
    // Whichever of the two theme text colors clears AA wins; ink preferred.
    if (prt_contrast($ink, $bg) >= 4.5) {
        return $ink;
    }
    if (prt_contrast($paper, $bg) >= 4.5) {
        return $paper;
    }
    // Neither configured color works (e.g. mid-gray bg): fall back to pure.
    return prt_is_dark($bg) ? '#FFFFFF' : '#17151F';
}

/**
 * Return $fg if it clears $min contrast on $bg, otherwise the nearest theme
 * color that does. Used to sanitize palette output without rewriting mods.
 */
function prt_ensure_contrast(string $fg, string $bg, float $min = 4.5): string
{
    if (prt_hex_rgb($fg) && prt_contrast($fg, $bg) >= $min) {
        return $fg;
    }
    return prt_readable_on($bg);
}

/* ──────────────────────────────────────────────────────────────────────────
 * Preset registries
 * ────────────────────────────────────────────────────────────────────── */

/**
 * Header layout presets (Kadence header-builder-style starting points).
 * 'mods' are plain set_theme_mod() batches — every key already exists in the
 * theme except the prt_header_* designer mods registered further down.
 */
function prt_header_presets(): array
{
    return [
        'classic' => [
            'label' => __('Classic bar', 'pressroot'),
            'desc'  => __('Single row — logo left, menu center, actions right. The default.', 'pressroot'),
            'mods'  => [
                'prt_header_centered' => false, 'prt_header_minimal' => false,
                'prt_topbar_enable' => false, 'prt_ann_enable' => false,
                'prt_header_transparent' => 'none', 'prt_header_scheme' => 'auto',
                'prt_header_sticky' => true, 'prt_header_shrink' => false, 'prt_header_scrim' => false,
            ],
        ],
        'topbar' => [
            'label' => __('Top bar + nav', 'pressroot'),
            'desc'  => __('Slim contact/social bar above the main navigation row.', 'pressroot'),
            'mods'  => [
                'prt_header_centered' => false, 'prt_header_minimal' => false,
                'prt_topbar_enable' => true, 'prt_ann_enable' => false,
                'prt_header_transparent' => 'none', 'prt_header_scheme' => 'auto',
                'prt_header_sticky' => true, 'prt_header_shrink' => false, 'prt_header_scrim' => false,
            ],
        ],
        'banner-stack' => [
            'label' => __('Banner stack', 'pressroot'),
            'desc'  => __('Three rows — announcement banner, contact bar, then logo + navigation.', 'pressroot'),
            'mods'  => [
                'prt_header_centered' => false, 'prt_header_minimal' => false,
                'prt_topbar_enable' => true, 'prt_ann_enable' => true,
                'prt_header_transparent' => 'none', 'prt_header_scheme' => 'auto',
                'prt_header_sticky' => true, 'prt_header_shrink' => false, 'prt_header_scrim' => false,
            ],
        ],
        'logo-center' => [
            'label' => __('Centered logo banner', 'pressroot'),
            'desc'  => __('Big centered logo row with the menu on its own row underneath.', 'pressroot'),
            'mods'  => [
                'prt_header_centered' => true, 'prt_header_minimal' => false,
                'prt_topbar_enable' => false, 'prt_ann_enable' => false,
                'prt_header_transparent' => 'none', 'prt_header_scheme' => 'auto',
                'prt_header_sticky' => false, 'prt_header_shrink' => false, 'prt_header_scrim' => false,
            ],
        ],
        'overlay-hero' => [
            'label' => __('Transparent over hero', 'pressroot'),
            'desc'  => __('See-through nav floating on the hero image; turns solid on scroll.', 'pressroot'),
            'mods'  => [
                'prt_header_centered' => false, 'prt_header_minimal' => false,
                'prt_topbar_enable' => false, 'prt_ann_enable' => false,
                'prt_header_transparent' => 'front', 'prt_header_scheme' => 'light',
                'prt_header_sticky' => true, 'prt_header_shrink' => false, 'prt_header_scrim' => true,
            ],
        ],
        'minimal' => [
            'label' => __('Minimal', 'pressroot'),
            'desc'  => __('Slim quiet bar — logo and menu only, no social icons or button.', 'pressroot'),
            'mods'  => [
                'prt_header_centered' => false, 'prt_header_minimal' => true,
                'prt_topbar_enable' => false, 'prt_ann_enable' => false,
                'prt_header_transparent' => 'none', 'prt_header_scheme' => 'auto',
                'prt_header_sticky' => true, 'prt_header_shrink' => true, 'prt_header_scrim' => false,
            ],
        ],
    ];
}

/**
 * Footer presets. Palette pairs are pre-checked AA combinations of the
 * existing prt_footer_bg/textc palette keys (resolved by prt_palette_value()).
 */
function prt_footer_presets(): array
{
    return [
        'columns' => [
            'label' => __('Column grid', 'pressroot'),
            'desc'  => __('Brand + link columns on dark ink, split bottom row. The default.', 'pressroot'),
            'mods'  => [
                'prt_footer_cols' => 3, 'prt_footer_col_layout' => 'equal',
                'prt_footer_pad' => 'cozy', 'prt_footer_width' => 'contained',
                'prt_footer_bottom_layout' => 'split',
                'prt_footer_bg' => 'ink', 'prt_footer_textc' => 'paper',
                'prt_footer_divider' => true, 'prt_footer_border' => false,
            ],
        ],
        'centered' => [
            'label' => __('Centered', 'pressroot'),
            'desc'  => __('Single centered column — brand, menu, social — stacked bottom row.', 'pressroot'),
            'mods'  => [
                'prt_footer_cols' => 1, 'prt_footer_col_layout' => 'equal',
                'prt_footer_pad' => 'spacious', 'prt_footer_width' => 'contained',
                'prt_footer_bottom_layout' => 'center',
                'prt_footer_bg' => 'cream', 'prt_footer_textc' => 'ink',
                'prt_footer_divider' => false, 'prt_footer_border' => true,
            ],
        ],
        'mega' => [
            'label' => __('Mega footer', 'pressroot'),
            'desc'  => __('Wide brand column plus four link columns, roomy padding.', 'pressroot'),
            'mods'  => [
                'prt_footer_cols' => 4, 'prt_footer_col_layout' => 'wide-first',
                'prt_footer_pad' => 'spacious', 'prt_footer_width' => 'contained',
                'prt_footer_bottom_layout' => 'split',
                'prt_footer_bg' => 'ink', 'prt_footer_textc' => 'paper',
                'prt_footer_divider' => true, 'prt_footer_border' => false,
            ],
        ],
        'minimal' => [
            'label' => __('Minimal strip', 'pressroot'),
            'desc'  => __('Just the bottom row — copyright, legal menu, social.', 'pressroot'),
            'mods'  => [
                'prt_footer_cols' => 1, 'prt_footer_col_layout' => 'equal',
                'prt_footer_pad' => 'compact', 'prt_footer_width' => 'contained',
                'prt_footer_bottom_layout' => 'split',
                'prt_footer_bg' => 'paper', 'prt_footer_textc' => 'ink',
                'prt_footer_divider' => false, 'prt_footer_border' => true,
            ],
        ],
    ];
}

/** Apply one preset (validated key) — a plain mods batch + remembers choice. */
function prt_apply_header_preset(string $key): bool
{
    $all = prt_header_presets();
    if (! isset($all[$key])) {
        return false;
    }
    foreach ($all[$key]['mods'] as $mod => $value) {
        set_theme_mod($mod, $value);
    }
    set_theme_mod('prt_header_preset', $key);
    return true;
}

function prt_apply_footer_preset(string $key): bool
{
    $all = prt_footer_presets();
    if (! isset($all[$key])) {
        return false;
    }
    foreach ($all[$key]['mods'] as $mod => $value) {
        set_theme_mod($mod, $value);
    }
    set_theme_mod('prt_footer_preset', $key);
    return true;
}

/* ──────────────────────────────────────────────────────────────────────────
 * Customizer registration for the designer-owned mods
 * ────────────────────────────────────────────────────────────────────── */

add_action('customize_register', function ($wp) {
    prt_ensure_theme_options_panel($wp);

    $wp->add_setting('prt_header_scheme', ['default' => 'auto', 'sanitize_callback' => 'sanitize_key']);
    $wp->add_control('prt_header_scheme', [
        'label'       => __('Header text scheme', 'pressroot'),
        'description' => __('Auto derives a readable text color from the header background (WCAG AA). Force light for headers overlaying dark photos.', 'pressroot'),
        'section'     => 'prt_headerlayout_section',
        'type'        => 'select',
        'choices'     => ['auto' => __('Auto (contrast-safe)', 'pressroot'), 'dark' => __('Dark text', 'pressroot'), 'light' => __('Light text', 'pressroot')],
    ]);

    $wp->add_setting('prt_header_scrim', ['default' => false, 'sanitize_callback' => 'wp_validate_boolean']);
    $wp->add_control('prt_header_scrim', [
        'label'       => __('Scrim behind transparent header', 'pressroot'),
        'description' => __('Soft dark gradient under the see-through header so light text stays readable on any hero image.', 'pressroot'),
        'section'     => 'prt_headerlayout_section',
        'type'        => 'checkbox',
    ]);

    $wp->add_setting('prt_header_centered', ['default' => false, 'sanitize_callback' => 'wp_validate_boolean']);
    $wp->add_control('prt_header_centered', [
        'label'   => __('Centered logo banner (menu row below)', 'pressroot'),
        'section' => 'prt_headerlayout_section',
        'type'    => 'checkbox',
    ]);

    $wp->add_setting('prt_header_minimal', ['default' => false, 'sanitize_callback' => 'wp_validate_boolean']);
    $wp->add_control('prt_header_minimal', [
        'label'   => __('Minimal header (hide social icons + button)', 'pressroot'),
        'section' => 'prt_headerlayout_section',
        'type'    => 'checkbox',
    ]);

    /* Homepage hero background image + overlay (real photos in the hero). */
    $wp->add_setting('prt_home_hero_bg', ['default' => '', 'sanitize_callback' => 'esc_url_raw']);
    if (class_exists('WP_Customize_Image_Control')) {
        $wp->add_control(new \WP_Customize_Image_Control($wp, 'prt_home_hero_bg', [
            'label'       => __('Homepage hero background image', 'pressroot'),
            'description' => __('A real photo behind the homepage hero. The overlay below keeps text at WCAG AA on any image.', 'pressroot'),
            'section'     => 'prt_hero_section',
        ]));
    }

    $wp->add_setting('prt_home_hero_overlay', ['default' => 60, 'sanitize_callback' => 'absint']);
    $wp->add_control('prt_home_hero_overlay', [
        'label'       => __('Hero image overlay strength (%)', 'pressroot'),
        'description' => __('Dark overlay over the hero photo. 55%+ keeps white text readable on bright images.', 'pressroot'),
        'section'     => 'prt_hero_section',
        'type'        => 'number',
        'input_attrs' => ['min' => 0, 'max' => 90, 'step' => 5],
    ]);
}, 24);

/* ──────────────────────────────────────────────────────────────────────────
 * CSS: contrast guard + preset structure
 * ────────────────────────────────────────────────────────────────────── */

/**
 * Late head CSS (priority 17 — after the palette/:root emit at 13 and the
 * header behaviors at 15, before dark-mode's !important pass at 20).
 */
add_action('prt_head_end', function () {
    $paper  = get_theme_mod('prt_color_paper', '#FFF9F5') ?: '#FFF9F5';
    $ink    = get_theme_mod('prt_color_ink', '#17151F') ?: '#17151F';
    $body   = get_theme_mod('prt_color_body', '#4A4660') ?: '#4A4660';
    $scheme = get_theme_mod('prt_header_scheme', 'auto');
    $css    = '';

    /* 1 ── Palette contrast guard (auto-repairs light-on-light setups).
       If saved heading/body colors can't hit AA on the page background, emit
       corrected :root vars AFTER the palette emit so the page stays readable
       even when the stored mods are broken (bad kit / AI palette / wizard). */
    $fixInk  = prt_ensure_contrast($ink, $paper, 4.5);
    $fixBody = prt_ensure_contrast($body, $paper, 4.5);
    if (strcasecmp($fixInk, $ink) !== 0) {
        $css .= ':root{--color-ink:' . $fixInk . ';--color-heading:' . $fixInk . ';}';
    }
    if (strcasecmp($fixBody, $body) !== 0) {
        $css .= ':root{--color-body:' . $fixBody . ';}';
    }

    /* 2 ── Header bar follows the page background, text derived from it.
       The base stylesheet hardcodes a warm-white bar; recolor it from the
       actual paper mod and pair it with a guaranteed-readable text color.
       NOTE: the solid bar's text is ALWAYS derived from the bar's actual
       background — the light/dark "scheme" only applies to the transparent
       overlay state (section 3), where the bar floats over a hero image.
       Honoring "light" on a light solid bar would recreate the exact
       light-on-light failure this file exists to prevent. */
    $rgb = prt_hex_rgb($paper) ?: [255, 249, 245];
    $barRgba  = 'rgba(' . $rgb[0] . ',' . $rgb[1] . ',' . $rgb[2] . ',.92)';
    $hdrText  = prt_readable_on($paper);
    $hdrIsDark = prt_is_dark($paper);
    $css .= 'html:not(.prt-dark) .site-header{background:' . $barRgba . ';}';
    $css .= 'html:not(.prt-dark) .site-header .brand-name,'
        . 'html:not(.prt-dark) .header-nav-list li a,'
        . 'html:not(.prt-dark) .prt-theme-toggle,'
        . 'html:not(.prt-dark) .menu-toggle{color:' . $hdrText . ';}';
    if ($hdrIsDark) {
        // Muted icons + hover pills assume a light bar; re-derive for dark.
        $css .= 'html:not(.prt-dark) .header-social a{color:rgba(255,249,245,.8);}';
        $css .= 'html:not(.prt-dark) .header-nav-list li a:hover{background:rgba(255,255,255,.12);color:#fff;}';
    }
    // Nav color mod guard: an explicitly-set nav color that fails 3:1 on the
    // bar is ignored in favor of the derived color (UI component minimum).
    $navColor = get_theme_mod('prt_nav_color', '');
    if ($navColor && prt_hex_rgb($navColor) && prt_contrast($navColor, $paper) < 3) {
        $css .= 'html:not(.prt-dark) .header-nav-list li a{color:' . $hdrText . ' !important;}';
    }

    /* 2b ── Block palette utilities follow the palette. theme.json bakes
       "surface" (white), "paper", and "cream" into generated utility classes
       as fixed hex — on an inverted (dark-paper) palette those stay light
       while headings go light too. Re-derive the light surfaces from the
       actual paper color so cards always sit near the page background. */
    if ($hdrIsDark) {
        $css .= 'html:not(.prt-dark) .has-paper-background-color{background-color:' . $paper . ' !important;}';
        $css .= 'html:not(.prt-dark) .has-surface-background-color{background-color:' . prt_shade($paper, 8) . ' !important;}';
        $css .= 'html:not(.prt-dark) .has-cream-background-color{background-color:' . prt_shade($paper, 5) . ' !important;}';
        $css .= 'html:not(.prt-dark) .has-line-background-color{background-color:' . prt_shade($paper, 14) . ' !important;}';
        $css .= 'html:not(.prt-dark) .has-ink-color{color:' . $fixInk . ' !important;}';
        $css .= 'html:not(.prt-dark) .has-body-color{color:' . $fixBody . ' !important;}';
    }

    /* 2c ── Fine-grained heading/link color mods: emitted raw by the
       Customizer, so guard them against the page background too. */
    foreach (['h1', 'h2', 'h3', 'h4', 'h5', 'h6', 'link', 'eyebrow'] as $lvl) {
        $v = get_theme_mod("prt_color_{$lvl}", '');
        if ($v && prt_hex_rgb($v) && prt_contrast($v, $paper) < 4.5) {
            $css .= ':root{--color-' . $lvl . ':' . prt_readable_on($paper) . ';}';
        }
    }

    /* 2d ── Top bar + footer pairs: their bg/text mods are palette keys
       resolved elsewhere into raw values with no contrast check between
       them. Resolve both here and override the text variable when the
       saved pair can't reach AA. */
    $resolve = function (string $key, string $custom = '') use ($paper, $ink, $body): string {
        switch ($key) {
            case 'ink':
                return $ink;
            case 'green':
                return get_theme_mod('prt_color_action', '#6C4CF1') ?: '#6C4CF1';
            case 'cream':
                return prt_is_dark($paper) ? prt_shade($paper, 5) : '#F3EEFE';
            case 'paper':
                return $paper;
            case 'body':
                return $body;
            case 'white':
                return '#FFFFFF';
            case 'custom':
                return $custom ?: $paper;
            default:
                return $paper;
        }
    };
    $ftBg   = $resolve(get_theme_mod('prt_footer_bg', 'ink'), (string) get_theme_mod('prt_footer_bg_custom', ''));
    $ftText = $resolve(get_theme_mod('prt_footer_textc', 'paper'), (string) get_theme_mod('prt_footer_text_custom', ''));
    if (prt_contrast($ftText, $ftBg) < 4.5) {
        $css .= ':root{--prt-footer-text:' . prt_readable_on($ftBg) . ';}';
    }
    // Footer brand reuses .brand-name (colored --color-ink globally), which
    // can vanish against the footer surface — pin it to the footer text var.
    $css .= '.content-info .brand-name{color:var(--prt-footer-text);}';

    $tbBg   = $resolve((string) get_theme_mod('prt_topbar_bg', 'ink'));
    $tbText = $resolve((string) get_theme_mod('prt_topbar_text', 'white'));
    if (prt_contrast($tbText, $tbBg) < 4.5) {
        $css .= ':root{--prt-topbar-text:' . prt_readable_on($tbBg) . ';}';
    }

    /* 3 ── Transparent overlay: true overlay + light text + optional scrim. */
    $tr = get_theme_mod('prt_header_transparent', 'none');
    if ($tr !== 'none') {
        $css .= 'body.prt-transparent:not(.prt-scrolled) .site-header{position:fixed;left:0;right:0;top:0;}';
        $css .= 'body.prt-transparent.admin-bar:not(.prt-scrolled) .site-header{top:32px;}';
        $css .= 'body.prt-transparent.prt-scrolled .site-header{position:fixed;left:0;right:0;top:0;}';
        $css .= 'body.prt-transparent.admin-bar.prt-scrolled .site-header{top:32px;}';
        $css .= '@media(max-width:782px){body.prt-transparent.admin-bar .site-header{top:46px;}}';
        // header-behaviors.php hardcodes a warm-white solid-on-scroll bar;
        // out-rank it (same specificity, later in source) with the bar color
        // derived from the actual palette so dark-paper sites stay dark.
        $css .= 'html:not(.prt-dark) body.prt-transparent.prt-scrolled .site-header{background:' . $barRgba . ';}';
        if ($scheme !== 'dark') {
            // Over a hero image the bar is see-through — light text + AA-safe
            // hover states, flipping back to derived colors once solid.
            $css .= 'body.prt-transparent:not(.prt-scrolled) .site-header .brand-name,'
                . 'body.prt-transparent:not(.prt-scrolled) .header-nav-list li a,'
                . 'body.prt-transparent:not(.prt-scrolled) .prt-theme-toggle,'
                . 'body.prt-transparent:not(.prt-scrolled) .menu-toggle{color:#FFF9F5;}';
            $css .= 'body.prt-transparent:not(.prt-scrolled) .header-social a{color:rgba(255,249,245,.85);}';
            $css .= 'body.prt-transparent:not(.prt-scrolled) .header-nav-list li a:hover{background:rgba(255,255,255,.14);color:#fff;}';
        }
        if (get_theme_mod('prt_header_scrim', false)) {
            $css .= 'body.prt-transparent:not(.prt-scrolled) .site-header::before{content:"";position:absolute;inset:0 0 -34px;pointer-events:none;z-index:-1;background:linear-gradient(180deg,rgba(10,8,20,.55),rgba(10,8,20,0));}';
            $css .= 'body.prt-transparent:not(.prt-scrolled) .site-header{isolation:isolate;}';
        }
    }

    /* 4 ── Centered logo banner (two visual rows inside the one flex bar). */
    if (get_theme_mod('prt_header_centered', false)) {
        $css .= '.site-header-inner{flex-wrap:wrap;row-gap:2px;padding-top:16px;padding-bottom:10px;}'
            . '.site-header .brand{flex-basis:100%;justify-content:center;order:0 !important;}'
            . '.site-header .brand-name{font-size:24px;}'
            . '.site-header .brand-mark svg{width:44px;height:44px;}'
            . '.header-nav{margin-left:0;display:flex;justify-content:center;order:1 !important;}'
            . '.header-actions{order:2 !important;margin-left:auto;}'
            . '@media(min-width:769px){.header-nav{flex:1;}}';
    }

    /* 5 ── Minimal header. */
    if (get_theme_mod('prt_header_minimal', false)) {
        $css .= '.site-header .header-social,.site-header .btn-hire{display:none !important;}'
            . '.site-header-inner{min-height:52px;}';
    }

    /* 6 ── Minimal footer strip: bottom row only (the footer builder clamps
       columns to ≥1, so the top section is suppressed here instead). */
    if (get_theme_mod('prt_footer_preset', 'columns') === 'minimal') {
        $css .= '.content-info .footer-top{display:none;}';
    }

    if ($css !== '') {
        echo "\n<style id=\"prt-design\">" . $css . "</style>\n";
    }
}, 17);

/* ──────────────────────────────────────────────────────────────────────────
 * Designer UI (shared by wizard step + settings tab)
 * ────────────────────────────────────────────────────────────────────── */

/** Tiny schematic SVG previews for the preset cards (Kadence-style). */
function prt_design_preview_svg(string $kind, string $key): string
{
    $bar  = '#c9bff2';
    $ink  = '#4A4660';
    $soft = '#ece6fb';
    $svgo = '<svg viewBox="0 0 120 64" width="120" height="64" role="img" aria-hidden="true" style="display:block;background:#fff;border-radius:6px">';

    if ($kind === 'header') {
        switch ($key) {
            case 'topbar':
                return $svgo . '<rect x="0" y="0" width="120" height="7" fill="' . $ink . '"/><rect x="0" y="7" width="120" height="16" fill="' . $soft . '"/><rect x="6" y="12" width="18" height="6" rx="2" fill="' . $ink . '"/><rect x="70" y="13" width="42" height="4" rx="2" fill="' . $bar . '"/><rect x="0" y="30" width="120" height="30" fill="#f7f4ff"/></svg>';
            case 'banner-stack':
                return $svgo . '<rect x="0" y="0" width="120" height="8" fill="#6C4CF1"/><rect x="0" y="8" width="120" height="8" fill="' . $ink . '"/><rect x="0" y="16" width="120" height="18" fill="' . $soft . '"/><rect x="6" y="22" width="18" height="6" rx="2" fill="' . $ink . '"/><rect x="66" y="23" width="46" height="4" rx="2" fill="' . $bar . '"/><rect x="0" y="40" width="120" height="20" fill="#f7f4ff"/></svg>';
            case 'logo-center':
                return $svgo . '<rect x="0" y="0" width="120" height="34" fill="' . $soft . '"/><rect x="48" y="6" width="24" height="10" rx="3" fill="' . $ink . '"/><rect x="30" y="22" width="60" height="4" rx="2" fill="' . $bar . '"/><rect x="0" y="40" width="120" height="20" fill="#f7f4ff"/></svg>';
            case 'overlay-hero':
                return $svgo . '<rect x="0" y="0" width="120" height="64" fill="#201B3A"/><circle cx="92" cy="40" r="22" fill="#2d2650"/><rect x="6" y="6" width="16" height="6" rx="2" fill="#fff"/><rect x="62" y="7" width="50" height="4" rx="2" fill="rgba(255,255,255,.75)"/><rect x="10" y="30" width="52" height="6" rx="2" fill="#fff"/><rect x="10" y="42" width="34" height="4" rx="2" fill="rgba(255,255,255,.6)"/></svg>';
            case 'minimal':
                return $svgo . '<rect x="0" y="0" width="120" height="14" fill="' . $soft . '"/><rect x="6" y="4" width="14" height="6" rx="2" fill="' . $ink . '"/><rect x="78" y="5" width="36" height="4" rx="2" fill="' . $bar . '"/><rect x="0" y="20" width="120" height="40" fill="#f7f4ff"/></svg>';
            default: // classic
                return $svgo . '<rect x="0" y="0" width="120" height="20" fill="' . $soft . '"/><rect x="6" y="6" width="18" height="8" rx="2" fill="' . $ink . '"/><rect x="34" y="8" width="40" height="4" rx="2" fill="' . $bar . '"/><rect x="96" y="5" width="18" height="9" rx="4" fill="#6C4CF1"/><rect x="0" y="26" width="120" height="34" fill="#f7f4ff"/></svg>';
        }
    }

    switch ($key) {
        case 'centered':
            return $svgo . '<rect x="0" y="0" width="120" height="24" fill="#fff"/><rect x="0" y="24" width="120" height="40" fill="' . $soft . '"/><rect x="46" y="30" width="28" height="6" rx="2" fill="' . $ink . '"/><rect x="34" y="41" width="52" height="4" rx="2" fill="' . $bar . '"/><rect x="44" y="51" width="32" height="4" rx="2" fill="' . $bar . '"/></svg>';
        case 'mega':
            return $svgo . '<rect x="0" y="0" width="120" height="12" fill="#fff"/><rect x="0" y="12" width="120" height="52" fill="' . $ink . '"/><rect x="6" y="18" width="30" height="20" rx="2" fill="#5A5676"/><rect x="44" y="18" width="16" height="26" rx="2" fill="#5A5676"/><rect x="64" y="18" width="16" height="26" rx="2" fill="#5A5676"/><rect x="84" y="18" width="16" height="26" rx="2" fill="#5A5676"/><rect x="6" y="52" width="108" height="4" rx="2" fill="#7C75A8"/></svg>';
        case 'minimal':
            return $svgo . '<rect x="0" y="0" width="120" height="46" fill="#fff"/><rect x="0" y="46" width="120" height="18" fill="' . $soft . '"/><rect x="6" y="53" width="36" height="4" rx="2" fill="' . $ink . '"/><rect x="84" y="53" width="30" height="4" rx="2" fill="' . $bar . '"/></svg>';
        default: // columns
            return $svgo . '<rect x="0" y="0" width="120" height="16" fill="#fff"/><rect x="0" y="16" width="120" height="48" fill="' . $ink . '"/><rect x="6" y="22" width="30" height="24" rx="2" fill="#5A5676"/><rect x="46" y="22" width="30" height="24" rx="2" fill="#5A5676"/><rect x="84" y="22" width="30" height="24" rx="2" fill="#5A5676"/><rect x="6" y="54" width="108" height="4" rx="2" fill="#7C75A8"/></svg>';
    }
}

/**
 * The designer form fields — preset cards + fine-tune controls. Rendered
 * inside a <form> owned by the caller (wizard step or settings tab).
 */
function prt_design_designer_fields(): void
{
    $hdrCurrent = get_theme_mod('prt_header_preset', 'classic');
    $ftrCurrent = get_theme_mod('prt_footer_preset', 'columns');
    ?>
    <style>
        .prt-design-grid{display:grid;grid-template-columns:repeat(auto-fill,minmax(190px,1fr));gap:12px;margin:10px 0 4px}
        .prt-design-card{position:relative;border:2px solid #e2dcf5;border-radius:10px;background:#fff;padding:10px;cursor:pointer;transition:border-color .12s, box-shadow .12s;display:block}
        .prt-design-card:hover{border-color:#b9a7ff}
        .prt-design-card input{position:absolute;opacity:0;pointer-events:none}
        .prt-design-card:has(input:checked){border-color:#6C4CF1;box-shadow:0 0 0 3px rgba(108,76,241,.18)}
        .prt-design-card:has(input:focus-visible){outline:2px solid #6C4CF1;outline-offset:2px}
        .prt-design-card svg{width:100%;height:auto;border:1px solid #eee9fb;border-radius:6px}
        .prt-design-card strong{display:block;margin:8px 0 2px;font-size:13px}
        .prt-design-card span.d{display:block;font-size:11.5px;line-height:1.45;color:#5A5676}
        .prt-design-fine{display:flex;gap:22px;flex-wrap:wrap;margin:14px 0 6px;padding:12px 14px;background:#faf8ff;border:1px solid #ece6fb;border-radius:8px}
        .prt-design-fine label{display:flex;align-items:center;gap:7px;font-size:12.5px}
    </style>

    <h3 style="margin:18px 0 2px">🧭 <?php esc_html_e('Header layout', 'pressroot'); ?></h3>
    <p class="description"><?php esc_html_e('Pick a starting layout — every option stays adjustable afterwards (Customizer → Header Layout). All presets keep text at WCAG AA contrast automatically.', 'pressroot'); ?></p>
    <div class="prt-design-grid" role="radiogroup" aria-label="<?php esc_attr_e('Header layout presets', 'pressroot'); ?>">
        <?php foreach (prt_header_presets() as $key => $preset) : $m = $preset['mods']; ?>
            <label class="prt-design-card">
                <input type="radio" name="prt_header_preset" value="<?php echo esc_attr($key); ?>" <?php checked($hdrCurrent, $key); ?>
                    data-sticky="<?php echo $m['prt_header_sticky'] ? 1 : 0; ?>"
                    data-scrim="<?php echo $m['prt_header_scrim'] ? 1 : 0; ?>"
                    data-transparent="<?php echo esc_attr($m['prt_header_transparent']); ?>"
                    data-scheme="<?php echo esc_attr($m['prt_header_scheme']); ?>">
                <?php echo prt_design_preview_svg('header', $key); // phpcs:ignore -- built above from static strings ?>
                <strong><?php echo esc_html($preset['label']); ?></strong>
                <span class="d"><?php echo esc_html($preset['desc']); ?></span>
            </label>
        <?php endforeach; ?>
    </div>

    <script>
    // Picking a header preset syncs the fine-tune fields below to that
    // preset's values — otherwise the (stale) fine-tune fields would win on
    // save and silently neuter the preset the owner just chose.
    document.addEventListener('change', function (e) {
        if (!e.target.matches('input[name="prt_header_preset"]')) return;
        var d = e.target.dataset, f = e.target.closest('form');
        if (!f) return;
        var set = function (sel, fn) { var el = f.querySelector(sel); el && fn(el); };
        set('input[name="prt_header_sticky"]', function (el) { el.checked = d.sticky === '1'; });
        set('input[name="prt_header_scrim"]',  function (el) { el.checked = d.scrim === '1'; });
        set('select[name="prt_header_transparent"]', function (el) { el.value = d.transparent; });
        set('select[name="prt_header_scheme"]',      function (el) { el.value = d.scheme; });
    });
    </script>

    <div class="prt-design-fine">
        <label><input type="checkbox" name="prt_header_sticky" value="1" <?php checked(get_theme_mod('prt_header_sticky', true)); ?>> <?php esc_html_e('Sticky header', 'pressroot'); ?></label>
        <label><input type="checkbox" name="prt_header_scrim" value="1" <?php checked(get_theme_mod('prt_header_scrim', false)); ?>> <?php esc_html_e('Scrim under transparent header', 'pressroot'); ?></label>
        <label><?php esc_html_e('Transparent on:', 'pressroot'); ?>
            <select name="prt_header_transparent">
                <?php foreach (['none' => __('Off', 'pressroot'), 'front' => __('Front page', 'pressroot'), 'all' => __('All pages', 'pressroot')] as $v => $l) : ?>
                    <option value="<?php echo esc_attr($v); ?>" <?php selected(get_theme_mod('prt_header_transparent', 'none'), $v); ?>><?php echo esc_html($l); ?></option>
                <?php endforeach; ?>
            </select>
        </label>
        <label><?php esc_html_e('Header text:', 'pressroot'); ?>
            <select name="prt_header_scheme">
                <?php foreach (['auto' => __('Auto (contrast-safe)', 'pressroot'), 'dark' => __('Dark', 'pressroot'), 'light' => __('Light', 'pressroot')] as $v => $l) : ?>
                    <option value="<?php echo esc_attr($v); ?>" <?php selected(get_theme_mod('prt_header_scheme', 'auto'), $v); ?>><?php echo esc_html($l); ?></option>
                <?php endforeach; ?>
            </select>
        </label>
    </div>

    <h3 style="margin:22px 0 2px">🖼️ <?php esc_html_e('Hero image', 'pressroot'); ?></h3>
    <p class="description"><?php esc_html_e('A real photo behind the homepage hero. The dark overlay keeps the white headline readable on any image — pair it with the "Transparent over hero" header for the full-bleed look.', 'pressroot'); ?></p>
    <?php $heroBg = get_theme_mod('prt_home_hero_bg', ''); ?>
    <div style="display:flex;gap:16px;align-items:flex-start;flex-wrap:wrap;margin:10px 0">
        <div id="prt-hero-bg-preview" style="width:220px;height:110px;border:1px solid #e2dcf5;border-radius:8px;background:<?php echo $heroBg ? "url('" . esc_url($heroBg) . "') center/cover" : '#f4f1fd'; ?>;display:flex;align-items:center;justify-content:center;color:#7C75A8;font-size:12px">
            <?php echo $heroBg ? '' : esc_html__('No image yet', 'pressroot'); ?>
        </div>
        <div style="display:flex;flex-direction:column;gap:8px">
            <input type="hidden" name="prt_home_hero_bg" id="prt_home_hero_bg" value="<?php echo esc_url($heroBg); ?>">
            <span>
                <button type="button" class="button" id="prt-hero-bg-pick"><?php esc_html_e('Choose from Media Library', 'pressroot'); ?></button>
                <button type="button" class="button" id="prt-hero-bg-clear" <?php echo $heroBg ? '' : 'disabled'; ?>><?php esc_html_e('Remove', 'pressroot'); ?></button>
            </span>
            <label style="font-size:12.5px"><?php esc_html_e('Overlay strength', 'pressroot'); ?>
                <input type="range" name="prt_home_hero_overlay" min="0" max="90" step="5" value="<?php echo esc_attr(get_theme_mod('prt_home_hero_overlay', 60)); ?>" oninput="this.nextElementSibling.textContent=this.value+'%'">
                <output><?php echo esc_html(get_theme_mod('prt_home_hero_overlay', 60)); ?>%</output>
            </label>
            <p class="description" style="margin:0"><?php esc_html_e('Tip: the Customizer\'s image finder (Hero section) can also pull free photos from Openverse/Unsplash/Pexels or generate one with AI.', 'pressroot'); ?></p>
        </div>
    </div>
    <script>
    (function(){
        var pick = document.getElementById('prt-hero-bg-pick'), clear = document.getElementById('prt-hero-bg-clear'),
            input = document.getElementById('prt_home_hero_bg'), prev = document.getElementById('prt-hero-bg-preview'), frame;
        if (pick && window.wp && wp.media) {
            pick.addEventListener('click', function(){
                frame = frame || wp.media({title:'<?php echo esc_js(__('Choose hero image', 'pressroot')); ?>', multiple:false, library:{type:'image'}});
                frame.off('select').on('select', function(){
                    var url = frame.state().get('selection').first().toJSON().url;
                    input.value = url; prev.style.background = "url('"+url+"') center/cover"; prev.textContent=''; clear.disabled=false;
                });
                frame.open();
            });
        }
        clear && clear.addEventListener('click', function(){ input.value=''; prev.style.background='#f4f1fd'; prev.textContent='<?php echo esc_js(__('No image yet', 'pressroot')); ?>'; clear.disabled=true; });
    })();
    </script>

    <h3 style="margin:22px 0 2px">🦶 <?php esc_html_e('Footer layout', 'pressroot'); ?></h3>
    <p class="description"><?php esc_html_e('Footer designs are pre-paired background/text combinations that pass WCAG AA. Columns fill from Appearance → Widgets (Footer 1–4) and the footer menus.', 'pressroot'); ?></p>
    <div class="prt-design-grid" role="radiogroup" aria-label="<?php esc_attr_e('Footer layout presets', 'pressroot'); ?>">
        <?php foreach (prt_footer_presets() as $key => $preset) : ?>
            <label class="prt-design-card">
                <input type="radio" name="prt_footer_preset" value="<?php echo esc_attr($key); ?>" <?php checked($ftrCurrent, $key); ?>>
                <?php echo prt_design_preview_svg('footer', $key); // phpcs:ignore -- built above from static strings ?>
                <strong><?php echo esc_html($preset['label']); ?></strong>
                <span class="d"><?php echo esc_html($preset['desc']); ?></span>
            </label>
        <?php endforeach; ?>
    </div>
    <?php
}

/** Shared save logic for both surfaces. */
function prt_design_save_post(): void
{
    $hdr = sanitize_key($_POST['prt_header_preset'] ?? '');
    if ($hdr) {
        prt_apply_header_preset($hdr);
    }
    $ftr = sanitize_key($_POST['prt_footer_preset'] ?? '');
    if ($ftr) {
        prt_apply_footer_preset($ftr);
    }

    // Fine-tune overrides land AFTER the preset batch so they win.
    set_theme_mod('prt_header_sticky', ! empty($_POST['prt_header_sticky']));
    set_theme_mod('prt_header_scrim', ! empty($_POST['prt_header_scrim']));

    $tr = sanitize_key($_POST['prt_header_transparent'] ?? 'none');
    set_theme_mod('prt_header_transparent', in_array($tr, ['none', 'front', 'all'], true) ? $tr : 'none');

    $scheme = sanitize_key($_POST['prt_header_scheme'] ?? 'auto');
    set_theme_mod('prt_header_scheme', in_array($scheme, ['auto', 'dark', 'light'], true) ? $scheme : 'auto');

    set_theme_mod('prt_home_hero_bg', esc_url_raw(wp_unslash($_POST['prt_home_hero_bg'] ?? '')));
    set_theme_mod('prt_home_hero_overlay', min(90, absint($_POST['prt_home_hero_overlay'] ?? 60)));
}

/* ──────────────────────────────────────────────────────────────────────────
 * Settings tab ("Header & Footer") + admin-post save
 * ────────────────────────────────────────────────────────────────────── */

add_filter('pressroot/settings_tabs', function (array $tabs): array {
    $out = [];
    foreach ($tabs as $key => $tab) {
        $out[$key] = $tab;
        if ($key === 'setup') { // designer sits right after the wizard tab
            $out['design'] = [
                'label'   => __('Header & Footer', 'pressroot'),
                'render'  => __NAMESPACE__ . '\\prt_design_tab_html',
                'visible' => true,
            ];
        }
    }
    if (! isset($out['design'])) {
        $out['design'] = ['label' => __('Header & Footer', 'pressroot'), 'render' => __NAMESPACE__ . '\\prt_design_tab_html', 'visible' => true];
    }
    return $out;
});

/** Media modal is needed for the hero image picker on the settings page. */
add_action('admin_enqueue_scripts', function ($hook) {
    if ($hook === 'appearance_page_prt-settings') {
        wp_enqueue_media();
    }
});

/** The standalone settings-tab surface. */
function prt_design_tab_html(): void
{
    if (! current_user_can('edit_theme_options')) {
        return;
    }
    if (isset($_GET['prt_design_saved'])) {
        echo '<div class="notice notice-success is-dismissible"><p>' . esc_html__('Design saved — check the front end.', 'pressroot') . '</p></div>';
    }
    ?>
    <h2 style="margin-top:0"><?php esc_html_e('Header & Footer designer', 'pressroot'); ?></h2>
    <p class="description" style="max-width:680px"><?php esc_html_e('Layout presets in the spirit of Kadence\'s header/footer builder — pick a design, fine-tune, save. Everything maps to regular Customizer settings underneath.', 'pressroot'); ?></p>
    <form method="post" action="<?php echo esc_url(admin_url('admin-post.php')); ?>">
        <input type="hidden" name="action" value="prt_save_design">
        <input type="hidden" name="prt_return_tab" value="design">
        <?php wp_nonce_field('prt_save_design'); ?>
        <?php prt_design_designer_fields(); ?>
        <p style="margin-top:18px"><button type="submit" class="button button-primary button-hero"><?php esc_html_e('Save design', 'pressroot'); ?></button>
        <a class="button" target="_blank" href="<?php echo esc_url(home_url('/')); ?>"><?php esc_html_e('View site ↗', 'pressroot'); ?></a></p>
    </form>
    <?php
}

add_action('admin_post_prt_save_design', function () {
    if (! current_user_can('edit_theme_options') || ! check_admin_referer('prt_save_design')) {
        wp_die(esc_html__('Not allowed.', 'pressroot'));
    }
    prt_design_save_post();

    // Wizard context: mark the Design step done and continue; otherwise
    // bounce back to the settings tab with a success notice.
    $step = absint($_POST['prt_wizard_step'] ?? 0);
    if ($step && function_exists('App\\prt_wizard_mark_done')) {
        prt_wizard_mark_done($step);
        wp_safe_redirect(prt_wizard_url($step + 1, ['prt_wiz_saved' => '1']));
        exit;
    }
    wp_safe_redirect(prt_settings_tab_url('design', ['prt_design_saved' => '1']));
    exit;
});
