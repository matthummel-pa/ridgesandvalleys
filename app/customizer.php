<?php

/**
 * Theme Options - colors, fonts, layout width, header CTA, footer text.
 * Emits CSS-variable overrides after app.css so changes apply without a rebuild.
 *
 * Design: rather than recompiling CSS per-site, app.css ships with a fixed set
 * of CSS custom properties (--color-ink, --font-display, etc.) and this file's
 * `prt_head_end` callback prints a small <style> block of var() overrides
 * built from the saved Customizer values. That keeps the build step
 * site-agnostic (same compiled app.css for every install) while still letting
 * each site fully re-brand colors/fonts/width from wp-admin.
 *
 * Also contains two one-time migration routines (search "prt_design_v2_applied"
 * and "prt_palette_force_v1") that were needed when this theme's default
 * palette changed; they're irrelevant to a fresh install but are kept so
 * existing sites upgrading don't have their saved values silently reinterpreted.
 */

namespace App;

/**
 * Register the shared `prt_theme_options` Customizer panel if it doesn't
 * already exist. Every module that adds its own Theme Options section
 * (colors, layout, header, footer, nav, SEO, performance, etc.) needs this
 * panel to exist first, but `customize_register` callbacks can run in any
 * order depending on priority and file-load order — so ~20 files each used
 * to carry their own copy of this exact "create if missing" guard. Extracted
 * here (this file loads first in functions.php's collect() list, and already
 * owns the panel's primary registration) so every module can call one
 * function instead of duplicating the guard.
 *
 * @param \WP_Customize_Manager $wp
 * @return void
 */
function prt_ensure_theme_options_panel($wp): void
{
    if (! $wp->get_panel('prt_theme_options')) {
        $wp->add_panel('prt_theme_options', [
            'title'    => __('Theme Options', 'pressroot'),
            'priority' => 30,
        ]);
    }
}

/**
 * Baseline values for every Theme Options setting registered below.
 *
 * Single source of truth for defaults — both the Customizer control
 * registration and prt_mod()'s runtime fallback read from here, so the two
 * can never drift out of sync. Filterable so a child theme/product fork can
 * ship different brand defaults without touching this file.
 */
function prt_defaults()
{
    return apply_filters('pressroot/theme_defaults', [
        'prt_color_action' => '#6C4CF1',
        'prt_color_paper'  => '#FFF9F5',
        'prt_color_ink'    => '#17151F',
        'prt_color_body'   => '#4A4660',
        'prt_font_heading' => 'Outfit',
        'prt_font_body'    => 'Outfit',
        'prt_container'    => 1240,
        'prt_show_cta'     => true,
        'prt_cta_text'     => 'Find me on GitHub',
        'prt_cta_url'      => 'https://github.com/matthummel-pa',
        'prt_footer_text'  => '',
    ]);
}

/**
 * Get a saved Theme Options value, falling back to prt_defaults() instead of
 * WordPress's usual empty-string default. Use this (not get_theme_mod()
 * directly) anywhere a prt_* mod is read, so defaults stay centralized.
 */
function prt_mod($key)
{
    $d = prt_defaults();
    return get_theme_mod($key, $d[$key] ?? '');
}

/**
 * Available font options.
 * Format: 'Display Name' => ['Google Fonts CSS2 family param (null = system)', 'CSS font stack']
 * The wp_enqueue_scripts hook below auto-loads any selected font from Google Fonts.
 */
function prt_fonts()
{
    return apply_filters('pressroot/fonts', [

        /* ── Modern Sans ───────────────────────────────────────────────── */
        'Geist'               => ['Geist:wght@400;500;600;700',                        '"Geist", system-ui, sans-serif'],
        'Inter'               => ['Inter:wght@400;500;600;700',                        '"Inter", system-ui, sans-serif'],
        'Inter Tight'         => ['Inter+Tight:wght@400;500;600;700',                  '"Inter Tight", system-ui, sans-serif'],
        'DM Sans'             => ['DM+Sans:opsz,wght@9..40,400;9..40,500;9..40,700',   '"DM Sans", system-ui, sans-serif'],
        'Plus Jakarta Sans'   => ['Plus+Jakarta+Sans:wght@400;500;600;700',            '"Plus Jakarta Sans", system-ui, sans-serif'],
        'Outfit'              => ['Outfit:wght@400;500;600;700',                        '"Outfit", system-ui, sans-serif'],
        'Nunito'              => ['Nunito:wght@400;500;600;700',                        '"Nunito", system-ui, sans-serif'],
        'Nunito Sans'         => ['Nunito+Sans:wght@400;500;600;700',                  '"Nunito Sans", system-ui, sans-serif'],

        /* ── Geometric / Grotesk ────────────────────────────────────────── */
        'Space Grotesk'       => ['Space+Grotesk:wght@400;500;600;700',                '"Space Grotesk", system-ui, sans-serif'],
        'Schibsted Grotesk'   => ['Schibsted+Grotesk:wght@400;500;600;700',            '"Schibsted Grotesk", system-ui, sans-serif'],
        'Bricolage Grotesque' => ['Bricolage+Grotesque:opsz,wght@12..96,400..800',     '"Bricolage Grotesque", system-ui, sans-serif'],
        'Sora'                => ['Sora:wght@400;500;600;700',                          '"Sora", system-ui, sans-serif'],
        'Poppins'             => ['Poppins:wght@400;500;600;700',                       '"Poppins", system-ui, sans-serif'],
        'Montserrat'          => ['Montserrat:wght@400;500;600;700',                    '"Montserrat", system-ui, sans-serif'],
        'Raleway'             => ['Raleway:wght@400;500;600;700',                       '"Raleway", system-ui, sans-serif'],

        /* ── Humanist Sans ──────────────────────────────────────────────── */
        'Work Sans'           => ['Work+Sans:wght@400;500;600;700',                    '"Work Sans", system-ui, sans-serif'],
        'Lato'                => ['Lato:wght@400;700',                                  '"Lato", system-ui, sans-serif'],
        'Open Sans'           => ['Open+Sans:wght@400;500;600;700',                    '"Open Sans", system-ui, sans-serif'],
        'Source Sans 3'       => ['Source+Sans+3:wght@400;500;600;700',                '"Source Sans 3", system-ui, sans-serif'],
        'Roboto'              => ['Roboto:wght@400;500;700',                            '"Roboto", system-ui, sans-serif'],
        'Roboto Condensed'    => ['Roboto+Condensed:wght@400;500;700',                 '"Roboto Condensed", system-ui, sans-serif'],
        'Noto Sans'           => ['Noto+Sans:wght@400;500;600;700',                    '"Noto Sans", system-ui, sans-serif'],

        /* ── Classic Serif ──────────────────────────────────────────────── */
        'Fraunces'            => ['Fraunces:opsz,wght@9..144,400..700',                '"Fraunces", Georgia, serif'],
        'Playfair Display'    => ['Playfair+Display:wght@400;500;600;700',             '"Playfair Display", Georgia, serif'],
        'Lora'                => ['Lora:wght@400;500;600;700',                          '"Lora", Georgia, serif'],
        'Merriweather'        => ['Merriweather:wght@400;700',                          '"Merriweather", Georgia, serif'],
        'Cormorant Garamond'  => ['Cormorant+Garamond:wght@400;500;600;700',           '"Cormorant Garamond", Georgia, serif'],
        'EB Garamond'         => ['EB+Garamond:wght@400;500;600;700',                  '"EB Garamond", Georgia, serif'],
        'DM Serif Display'    => ['DM+Serif+Display',                                   '"DM Serif Display", Georgia, serif'],
        'Crimson Pro'         => ['Crimson+Pro:wght@400;500;600;700',                  '"Crimson Pro", Georgia, serif'],
        'Libre Baskerville'   => ['Libre+Baskerville:wght@400;700',                    '"Libre Baskerville", Georgia, serif'],
        'PT Serif'            => ['PT+Serif:wght@400;700',                              '"PT Serif", Georgia, serif'],

        /* ── Display / Expressive ───────────────────────────────────────── */
        'Bebas Neue'          => ['Bebas+Neue',                                          '"Bebas Neue", system-ui, sans-serif'],
        'Oswald'              => ['Oswald:wght@400;500;600;700',                        '"Oswald", system-ui, sans-serif'],
        'Anton'               => ['Anton',                                               '"Anton", system-ui, sans-serif'],
        'Abril Fatface'       => ['Abril+Fatface',                                       '"Abril Fatface", Georgia, serif'],

        /* ── Monospace ──────────────────────────────────────────────────── */
        'JetBrains Mono'      => ['JetBrains+Mono:wght@400;500;700',                   '"JetBrains Mono", "Courier New", monospace'],
        'Fira Code'           => ['Fira+Code:wght@400;500;700',                         '"Fira Code", "Courier New", monospace'],
        'Source Code Pro'     => ['Source+Code+Pro:wght@400;500;700',                  '"Source Code Pro", "Courier New", monospace'],
        'IBM Plex Mono'       => ['IBM+Plex+Mono:wght@400;500;700',                    '"IBM Plex Mono", "Courier New", monospace'],
        'Roboto Mono'         => ['Roboto+Mono:wght@400;500;700',                       '"Roboto Mono", "Courier New", monospace'],

        /* ── System ─────────────────────────────────────────────────────── */
        'System'              => [null, 'system-ui, -apple-system, sans-serif'],
    ]);
}

/**
 * Register the "Theme Options" Customizer panel and all of its controls:
 * colors (base + fine-grained per-heading overrides), typography, layout
 * width, header CTA, and footer text. This is the admin-facing half of the
 * settings whose values prt_mod()/prt_defaults() read back out at render time.
 */
add_action('customize_register', function ($wp) {
    $d = prt_defaults();

    // This file loads first (see functions.php's collect() list), so in
    // practice this is always the first registration of the panel — but it
    // now goes through the same guarded helper as every other module rather
    // than assuming that ordering, in case the load order ever changes.
    prt_ensure_theme_options_panel($wp);

    /* Colors */
    $wp->add_section('prt_colors', ['title' => __('Colors', 'pressroot'), 'panel' => 'prt_theme_options']);
    $colors = [
        'prt_color_action' => __('Brand / buttons', 'pressroot'),
        'prt_color_paper'  => __('Page background', 'pressroot'),
        'prt_color_ink'    => __('Headings', 'pressroot'),
        'prt_color_body'   => __('Body text / paragraphs', 'pressroot'),
    ];
    foreach ($colors as $id => $label) {
        $wp->add_setting($id, ['default' => $d[$id], 'sanitize_callback' => 'sanitize_hex_color', 'transport' => 'refresh']);
        $wp->add_control(new \WP_Customize_Color_Control($wp, $id, ['label' => $label, 'section' => 'prt_colors']));
    }

    /* Fine-grained typography colors — every one is optional (blank = inherit
       the matching color above), so this adds per-element control without
       requiring the user to set all of them. Covers every heading level,
       paragraph links, and the small "eyebrow" label above section titles. */
    $typeColors = [
        'prt_color_h1'         => [__('Heading 1 (H1)', 'pressroot'), __('Falls back to "Headings" above.', 'pressroot')],
        'prt_color_h2'         => [__('Heading 2 (H2)', 'pressroot'), __('Falls back to "Headings" above.', 'pressroot')],
        'prt_color_h3'         => [__('Heading 3 (H3)', 'pressroot'), __('Falls back to "Headings" above.', 'pressroot')],
        'prt_color_h4'         => [__('Heading 4 (H4)', 'pressroot'), __('Falls back to "Headings" above.', 'pressroot')],
        'prt_color_h5'         => [__('Heading 5 (H5)', 'pressroot'), __('Falls back to "Headings" above.', 'pressroot')],
        'prt_color_h6'         => [__('Heading 6 / subheading (H6)', 'pressroot'), __('Falls back to "Headings" above.', 'pressroot')],
        'prt_color_link'       => [__('Link / anchor text', 'pressroot'), __('Falls back to "Brand / buttons" above. Applies to links in page/post content.', 'pressroot')],
        'prt_color_link_hover' => [__('Link hover', 'pressroot'), __('Falls back to a darker shade of the link color.', 'pressroot')],
        'prt_color_eyebrow'    => [__('Eyebrow / kicker label', 'pressroot'), __('The small uppercase label shown above section titles.', 'pressroot')],
    ];
    foreach ($typeColors as $id => $meta) {
        $wp->add_setting($id, ['default' => '', 'sanitize_callback' => 'sanitize_hex_color', 'transport' => 'refresh']);
        $wp->add_control(new \WP_Customize_Color_Control($wp, $id, [
            'label'       => $meta[0],
            'description' => $meta[1],
            'section'     => 'prt_colors',
        ]));
    }

    /* Typography */
    $wp->add_section('prt_type', ['title' => __('Typography', 'pressroot'), 'panel' => 'prt_theme_options']);
    $choices = array_combine(array_keys(prt_fonts()), array_keys(prt_fonts()));
    $wp->add_setting('prt_font_heading', ['default' => $d['prt_font_heading'], 'sanitize_callback' => 'sanitize_text_field']);
    $wp->add_control('prt_font_heading', ['label' => __('Heading font', 'pressroot'), 'section' => 'prt_type', 'type' => 'select', 'choices' => $choices]);
    $wp->add_setting('prt_font_body', ['default' => $d['prt_font_body'], 'sanitize_callback' => 'sanitize_text_field']);
    $wp->add_control('prt_font_body', ['label' => __('Body font', 'pressroot'), 'section' => 'prt_type', 'type' => 'select', 'choices' => $choices]);

    /* Layout width — 'prt_layout_section' is registered in app/theme-options.php,
       and 'prt_headerlayout_section' below is registered in app/header-layout.php.
       Referencing a section slug before that section is added is safe here:
       WP_Customize_Control only resolves its section object lazily at render
       time, not at add_control() time, so cross-file registration order
       across separate customize_register callbacks doesn't matter as long as
       every callback has run before the Customizer UI is actually displayed. */
    $wp->add_setting('prt_container', ['default' => $d['prt_container'], 'sanitize_callback' => 'absint']);
    $wp->add_control('prt_container', ['label' => __('Content width', 'pressroot'), 'section' => 'prt_layout_section', 'type' => 'select', 'choices' => prt_width_options()]);

    /* Header — 'prt_headerlayout_section' is registered in app/header-layout.php (see note above). */
    $wp->add_setting('prt_show_cta', ['default' => $d['prt_show_cta'], 'sanitize_callback' => 'wp_validate_boolean']);
    $wp->add_control('prt_show_cta', ['label' => __('Show header button', 'pressroot'), 'section' => 'prt_headerlayout_section', 'type' => 'checkbox']);
    $wp->add_setting('prt_cta_text', ['default' => $d['prt_cta_text'], 'sanitize_callback' => 'sanitize_text_field']);
    $wp->add_control('prt_cta_text', ['label' => __('Button text', 'pressroot'), 'section' => 'prt_headerlayout_section', 'type' => 'text']);
    $wp->add_setting('prt_cta_url', ['default' => $d['prt_cta_url'], 'sanitize_callback' => 'esc_url_raw']);
    $wp->add_control('prt_cta_url', ['label' => __('Button URL', 'pressroot'), 'section' => 'prt_headerlayout_section', 'type' => 'url']);

    /* Footer */
    $wp->add_setting('prt_footer_text', ['default' => $d['prt_footer_text'], 'sanitize_callback' => 'wp_kses_post']);
    $wp->add_control('prt_footer_text', ['label' => __('Footer tagline', 'pressroot'), 'section' => 'prt_footer_section', 'type' => 'textarea']);
});

/* Wire Theme Options values into the theme's existing filter hooks, so header/
   footer templates stay decoupled from the Customizer and just call a filter
   (e.g. apply_filters('pressroot/header_cta_url', ...)) without knowing or
   caring that the value happens to come from a theme_mod. */
add_filter('pressroot/header_cta_label', fn () => prt_mod('prt_cta_text'));
add_filter('pressroot/header_cta_url', fn () => prt_mod('prt_cta_url'));
add_filter('pressroot/show_header_cta', fn () => (bool) prt_mod('prt_show_cta'));
add_filter('pressroot/footer_text', fn () => prt_mod('prt_footer_text'));

/**
 * Load the selected heading + body fonts from Google Fonts.
 *
 * Only enqueues the families actually chosen in Theme Options (deduped via
 * array_unique, since heading/body may both pick the same font) rather than
 * loading the entire prt_fonts() catalog, to avoid unnecessary requests.
 * Priority 6 so it loads early, ahead of most other enqueued styles.
 */
add_action('wp_enqueue_scripts', function () {
    $fonts    = prt_fonts();
    $picked   = array_unique([prt_mod('prt_font_heading'), prt_mod('prt_font_body')]);
    $families = [];
    foreach ($picked as $p) {
        if (! empty($fonts[$p][0])) {
            $families[] = $fonts[$p][0];
        }
    }
    if ($families) {
        wp_enqueue_style(
            'matthummel-fonts-custom',
            'https://fonts.googleapis.com/css2?family=' . implode('&family=', $families) . '&display=swap',
            [],
            null
        );
    }
}, 6);

/**
 * Emit the CSS custom-property overrides that make the Customizer's saved
 * colors/fonts/width actually take visual effect, without any build step.
 * Fires via prt_head_end (the theme's late-head hookpoint in the layout
 * template) specifically so this <style> block lands AFTER app.css in the
 * cascade and its var() values win.
 */
add_action('prt_head_end', function () {
    $fonts = prt_fonts();
    $h = $fonts[prt_mod('prt_font_heading')][1] ?? $fonts['Outfit'][1];
    $b = $fonts[prt_mod('prt_font_body')][1] ?? $fonts['Outfit'][1];

    // NOTE: --color-green/--color-khaki are legacy CSS variable names from an
    // earlier green/khaki palette; app.css still defines/consumes them under
    // those names, but they now hold the "Brand / buttons" (prt_color_action)
    // and "Page background" (prt_color_paper) values respectively — i.e. the
    // variable name no longer describes the color it holds.
    $css = ':root{'
        . '--color-green:' . prt_mod('prt_color_action') . ';'
        . '--color-khaki:' . prt_mod('prt_color_paper') . ';'
        . '--color-ink:' . prt_mod('prt_color_ink') . ';'
        . '--color-heading:' . prt_mod('prt_color_ink') . ';'
        . '--color-body:' . prt_mod('prt_color_body') . ';'
        . '--font-display:' . $h . ';'
        . '--font-body:' . $b . ';'
        . '--prt-content-width:' . absint(prt_mod('prt_container')) . 'px;';

    // Fine-grained typography colors — only emitted when explicitly set, so
    // each element's var() fallback (e.g. var(--color-h2, var(--color-ink)))
    // is used otherwise. Keeps every heading level, links, and the eyebrow
    // label independently customizable without forcing a value on any of them.
    foreach (['h1', 'h2', 'h3', 'h4', 'h5', 'h6'] as $lvl) {
        $v = sanitize_hex_color(get_theme_mod("prt_color_{$lvl}", ''));
        if ($v) {
            $css .= "--color-{$lvl}:{$v};";
        }
    }
    $link  = sanitize_hex_color(get_theme_mod('prt_color_link', ''));
    $lhov  = sanitize_hex_color(get_theme_mod('prt_color_link_hover', ''));
    $eyeb  = sanitize_hex_color(get_theme_mod('prt_color_eyebrow', ''));
    if ($link) {
        $css .= '--color-link:' . $link . ';';
    }
    if ($lhov) {
        $css .= '--color-link-hover:' . $lhov . ';';
    }
    if ($eyeb) {
        $css .= '--color-eyebrow:' . $eyeb . ';';
    }

    $css .= '}'
        . '.container,.rule,.site-header-inner{max-width:' . absint(prt_mod('prt_container')) . 'px}';

    echo "\n<style id=\"prt-customizer\">" . $css . "</style>\n";
});


/**
 * One-time migration to the Paper + Space design.
 *
 * Flips any saved theme_mod that still holds an OLD default to the new value,
 * so existing installs adopt the new design without clobbering choices the user
 * deliberately customised. Runs once, then sets a flag. Use Appearance → Theme
 * Tools → Reset (or the "Paper + Space" style kit) to force a full re-apply.
 */
add_action('after_setup_theme', function () {
    if (get_option('prt_design_v2_applied')) {
        return;
    }

    // old default => new default
    $map = [
        'prt_color_action' => ['#2f6b4e', '#6C4CF1'],
        'prt_color_paper'  => ['#fbfaf7', '#FFF9F5'],
        'prt_color_ink'    => ['#17191e', '#17151F'],
        'prt_color_body'   => ['#2b2f36', '#4A4660'],
        'prt_font_heading' => ['Geist', 'Outfit'],
        'prt_font_body'    => ['Inter', 'Outfit'],
        'prt_btn_radius'   => ['8', '999'],
        'prt_card_radius'  => ['16', '20'],
        'prt_container'    => [1180, 1240],
        'prt_popout_grad2' => ['#2f6b4e', '#6C4CF1'],
        'prt_cta_text'     => ['Find me on Dev.to', 'Find me on GitHub'],
        'prt_cta_url'      => ['https://dev.to/mattbuildsapps', 'https://github.com/matthummel-pa'],
    ];

    foreach ($map as $key => [$old, $new]) {
        $current = get_theme_mod($key, null);
        // Unset (inherits the new default already) or still on the old default → set new.
        if ($current === null || (string) $current === (string) $old) {
            set_theme_mod($key, $new);
        }
    }

    // Older builds used Space Grotesk for headings; treat that as an old default too.
    if ((string) get_theme_mod('prt_font_heading', '') === 'Space Grotesk') {
        set_theme_mod('prt_font_heading', 'Outfit');
    }

    update_option('prt_design_v2_applied', 1);
}, 20);

/**
 * One-time hard reset of the brand palette/fonts to Paper + Space.
 *
 * The conditional migration above only flips values still on the OLD defaults;
 * if a green Style Kit had been saved, the header/logo stayed green. This forces
 * the new palette once so the whole UI (header CTA, brand mark, links) adopts
 * purple. Runs a single time, then never touches these again.
 */
add_action('after_setup_theme', function () {
    if (get_option('prt_palette_force_v1')) {
        return;
    }
    set_theme_mod('prt_color_action', '#6C4CF1');
    set_theme_mod('prt_color_paper', '#FFF9F5');
    set_theme_mod('prt_color_ink', '#17151F');
    set_theme_mod('prt_color_body', '#4A4660');
    set_theme_mod('prt_font_heading', 'Outfit');
    set_theme_mod('prt_font_body', 'Outfit');
    update_option('prt_palette_force_v1', 1);
}, 21);

/**
 * Standard content-width options (px) for select controls.
 *
 * Shared between this file's "Content width" control and the per-content-type
 * width overrides in app/theme-options.php ($include_preset = true there adds
 * a "use preset" choice), so the same set of width presets is offered
 * everywhere instead of duplicating the list.
 */
function prt_width_options($include_preset = false)
{
    $opts = [];
    if ($include_preset) {
        $opts['0'] = __('Use preset (default)', 'pressroot');
    }
    $opts = $opts + [
        '720'  => '720px (narrow)',
        '960'  => '960px (small)',
        '1080' => '1080px (medium)',
        '1140' => '1140px (standard)',
        '1180' => '1180px (default)',
        '1200' => '1200px',
        '1280' => '1280px (large)',
        '1320' => '1320px',
        '1440' => '1440px (extra wide)',
        '1600' => '1600px (max)',
    ];
    return apply_filters('pressroot/width_options', $opts);
}
