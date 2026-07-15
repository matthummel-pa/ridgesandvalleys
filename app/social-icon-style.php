<?php

/**
 * Social Icon Style — one shared, dynamic design system for every place
 * social icons render site-wide: the header (.header-social), the off-canvas
 * popout (.prt-popout-socials), the footer (.footer-socials), and the
 * bar-blocks composition used in the top bar / navbar (.top-bar-social /
 * .social, when "Social links display" = Icons).
 *
 * Reuses the size/shape/color/bg/hover settings already registered in
 * header-elements.php (Customizer -> Theme Options -> Social Icons) and adds
 * a few more knobs (gap, mono/brand color mode, hover background, border,
 * and a raw "Additional CSS" field) so nothing about how these icons look is
 * hardcoded — it all flows from one place and applies everywhere at once.
 */

namespace App;

/**
 * Sanitize callback for the "Additional CSS" Customizer field. Strips HTML
 * tags (this is a CSS field, not a place for markup) and neutralises any
 * "</style" sequence so a malicious or careless value can't close the
 * surrounding <style> tag early and inject arbitrary HTML/JS into the page
 * when it's echoed raw in the prt_head_end emitter below.
 */
function prt_sanitize_inline_css($css)
{
    $css = wp_strip_all_tags((string) $css);
    return str_ireplace('</style', '', $css);
}

/**
 * Resolve the current site-wide social icon design as a plain array of
 * validated/clamped values. Single place where every icon Customizer mod is
 * read and defended against bad input (e.g. size/gap clamped to sane ranges,
 * shape restricted to a known whitelist) so the CSS emitter below never has
 * to re-validate anything itself.
 */
function prt_social_design()
{
    $shape = get_theme_mod('prt_social_shape', 'none');

    return [
        'size'     => max(10, absint(get_theme_mod('prt_social_size', 18))),
        'gap'      => max(0, absint(get_theme_mod('prt_social_gap', 14))),
        'shape'    => in_array($shape, ['none', 'circle', 'rounded', 'square'], true) ? $shape : 'none',
        'style'    => get_theme_mod('prt_social_icon_style', 'mono') === 'brand' ? 'brand' : 'mono',
        'color'    => sanitize_hex_color(get_theme_mod('prt_social_color', '')),
        'bg'       => sanitize_hex_color(get_theme_mod('prt_social_bg', '')),
        'hover'    => sanitize_hex_color(get_theme_mod('prt_social_hover', '')),
        'hoverBg'  => sanitize_hex_color(get_theme_mod('prt_social_hover_bg', '')),
        'bw'       => max(0, min(6, absint(get_theme_mod('prt_social_border_width', 0)))),
        'bc'       => sanitize_hex_color(get_theme_mod('prt_social_border_color', '')) ?: 'currentColor',
    ];
}

/**
 * Inline `style="color:#..."` attribute for a single social icon when the
 * "Brand colors" mode is active (each network gets its own official color,
 * looked up via prt_social_color() in icons.php); returns '' in mono mode
 * since mono icons are colored entirely via the shared CSS emitted below.
 */
function prt_social_item_style_attr($key)
{
    $d = prt_social_design();
    if ($d['style'] !== 'brand') {
        return '';
    }
    return ' style="color:' . esc_attr(prt_social_color($key)) . '"';
}

/* ── Additional Customizer controls (size/shape/color/bg/hover already live
   in header-elements.php; they share the same "Social Icons" section). ──── */
// Priority 24: after header-elements.php's default-priority registration of
// the "Social Icons" section/panel, so get_panel()/get_section() below can
// safely add to what already exists instead of racing to create it first.
add_action('customize_register', function (\WP_Customize_Manager $wp) {
    // Shared guarded helper — see prt_ensure_theme_options_panel() in app/customizer.php.
    prt_ensure_theme_options_panel($wp);
    if (! $wp->get_section('prt_social_section')) {
        $wp->add_section('prt_social_section', [
            'title'       => __('Social Icons', 'pressroot'),
            'panel'       => 'prt_theme_options',
            'description' => __('Where social icons appear, their style, and your account URLs.', 'pressroot'),
        ]);
    }

    $wp->add_setting('prt_social_gap', ['default' => 14, 'sanitize_callback' => 'absint']);
    $wp->add_control('prt_social_gap', [
        'label'       => __('Gap between icons (px)', 'pressroot'),
        'section'     => 'prt_social_section',
        'type'        => 'number',
        'input_attrs' => ['min' => 0, 'max' => 48],
    ]);

    $wp->add_setting('prt_social_icon_style', ['default' => 'mono', 'sanitize_callback' => 'sanitize_key']);
    $wp->add_control('prt_social_icon_style', [
        'label'       => __('Icon color mode', 'pressroot'),
        'description' => __('Brand colors uses each network\'s official color (GitHub black, LinkedIn blue, etc.) instead of a single color.', 'pressroot'),
        'section'     => 'prt_social_section',
        'type'        => 'select',
        'choices'     => ['mono' => __('Mono (single color)', 'pressroot'), 'brand' => __('Brand colors', 'pressroot')],
    ]);

    $wp->add_setting('prt_social_hover_bg', ['default' => '', 'sanitize_callback' => 'sanitize_hex_color']);
    $wp->add_control(new \WP_Customize_Color_Control($wp, 'prt_social_hover_bg', [
        'label'   => __('Hover chip background', 'pressroot'),
        'section' => 'prt_social_section',
    ]));

    $wp->add_setting('prt_social_border_width', ['default' => 0, 'sanitize_callback' => 'absint']);
    $wp->add_control('prt_social_border_width', [
        'label'       => __('Border width (px)', 'pressroot'),
        'section'     => 'prt_social_section',
        'type'        => 'number',
        'input_attrs' => ['min' => 0, 'max' => 6],
    ]);

    $wp->add_setting('prt_social_border_color', ['default' => '', 'sanitize_callback' => 'sanitize_hex_color']);
    $wp->add_control(new \WP_Customize_Color_Control($wp, 'prt_social_border_color', [
        'label'   => __('Border color', 'pressroot'),
        'section' => 'prt_social_section',
    ]));

    $wp->add_setting('prt_social_css', [
        'default'           => '',
        'sanitize_callback' => __NAMESPACE__ . '\\prt_sanitize_inline_css',
    ]);
    $wp->add_control('prt_social_css', [
        'label'       => __('Additional CSS (advanced)', 'pressroot'),
        'description' => __('Raw CSS injected after the settings above. Target .header-social, .footer-socials, .prt-popout-socials, .top-bar-social, or .social to fine-tune beyond the controls here.', 'pressroot'),
        'section'     => 'prt_social_section',
        'type'        => 'textarea',
    ]);
}, 24);

/* ── One CSS emitter for every social icon location on the site. ─────────── */
// Hooked on the theme's custom `prt_head_end` action (alongside the other
// dynamic per-page style blocks, e.g. hero.php) rather than wp_head, so all
// Customizer-driven CSS prints together in one predictable place.
add_action('prt_head_end', function () {
    $d = prt_social_design();

    // Header, footer, and popout socials are always icons. The bar-blocks
    // composition (top bar / navbar) can be switched to text-only, so only
    // include its selectors when that toggle is set to "Icons".
    $containers = ['body .header-social', 'body .footer-socials', 'body .prt-popout-socials'];
    if (get_theme_mod('prt_social_style', 'icons') === 'icons') {
        $containers[] = 'body .top-bar-social.is-icons';
        $containers[] = 'body .social.is-icons';
    }

    $join = function ($suffix) use ($containers) {
        return implode(',', array_map(fn ($c) => $c . $suffix, $containers));
    };

    $chip   = $d['shape'] !== 'none';
    $pad    = $chip ? max(5, (int) round($d['size'] * 0.55)) : 0;
    $radius = $d['shape'] === 'circle' ? '50%' : ($d['shape'] === 'rounded' ? max(4, (int) round($d['size'] * 0.35)) . 'px' : '0');

    $css  = $join('') . '{display:inline-flex;flex-wrap:wrap;align-items:center;gap:' . $d['gap'] . 'px;list-style:none;margin:0;padding:0;}';
    $css .= $join(' li') . '{margin:0;padding:0;list-style:none;}';
    $css .= $join(' a') . '{display:inline-flex;align-items:center;justify-content:center;width:auto;height:auto;line-height:0;text-decoration:none;padding:' . $pad . 'px;border-radius:' . $radius . ';transition:color .15s ease,background .15s ease,transform .15s ease,border-color .15s ease;'
        . ($d['bw'] > 0 ? 'border:' . $d['bw'] . 'px solid ' . $d['bc'] . ';' : '')
        . ($d['style'] === 'mono' ? 'color:' . ($d['color'] ?: 'currentColor') . ';' : '')
        . ($chip ? 'background:' . ($d['bg'] ?: 'transparent') . ';' : '')
        . '}';
    $css .= $join(' a svg') . '{width:' . $d['size'] . 'px;height:' . $d['size'] . 'px;fill:currentColor;display:block;}';

    if ($d['style'] === 'mono') {
        $css .= $join(' a:hover') . '{transform:translateY(-2px);'
            . ($d['hover'] ? 'color:' . $d['hover'] . ';' : '')
            . ($chip && $d['hoverBg'] ? 'background:' . $d['hoverBg'] . ';' : '')
            . '}';
    } else {
        // Brand mode: each icon's color is set inline per-item; hover only
        // adjusts the chip/opacity, since per-icon hover colors would just
        // be the same brand color again.
        $css .= $join(' a:hover') . '{transform:translateY(-2px);'
            . ($chip ? 'filter:brightness(.92);' : 'opacity:.85;')
            . ($chip && $d['hoverBg'] ? 'background:' . $d['hoverBg'] . ';' : '')
            . '}';
    }

    $custom = trim((string) get_theme_mod('prt_social_css', ''));
    if ($custom !== '') {
        $css .= "\n" . $custom;
    }

    echo "\n<style id=\"prt-social-design\">" . $css . "</style>\n";
}, 16);
