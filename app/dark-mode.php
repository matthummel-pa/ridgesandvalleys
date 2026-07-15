<?php

/**
 * Dark mode: a header toggle that flips the CSS-variable palette,
 * persists the choice (localStorage), and supports system-auto.
 */

namespace App;

/** Customizer: Dark Mode toggle + default mode (light/dark/auto). */
add_action('customize_register', function ($wp) {
    // Shared guarded helper — see prt_ensure_theme_options_panel() in app/customizer.php.
    prt_ensure_theme_options_panel($wp);
    $wp->add_section('prt_dark_section', ['title' => __('Dark Mode', 'pressroot'), 'panel' => 'prt_theme_options']);
    $wp->add_setting('prt_dark_enable', ['default' => true, 'sanitize_callback' => 'wp_validate_boolean']);
    $wp->add_control('prt_dark_enable', ['label' => __('Show dark mode toggle', 'pressroot'), 'section' => 'prt_dark_section', 'type' => 'checkbox']);
    $wp->add_setting('prt_dark_default', ['default' => 'light', 'sanitize_callback' => 'sanitize_key']);
    $wp->add_control('prt_dark_default', [
        'label'           => __('Default mode', 'pressroot'),
        'section'         => 'prt_dark_section',
        'type'            => 'select',
        'choices'         => ['light' => __('Light', 'pressroot'), 'dark' => __('Dark', 'pressroot'), 'auto' => __('Auto (system)', 'pressroot')],
        'active_callback' => function ($control) {
            $s = $control->manager->get_setting('prt_dark_enable');
            return $s && $s->value();
        },
    ]);
}, 25);

/**
 * No-flash: set the `prt-dark` class on <html> as early as possible, before
 * CSS/first paint. Runs inline in wp_head at priority 2 (very early) rather
 * than as an enqueued/deferred script — waiting for DOMContentLoaded or an
 * external file would let the page paint in light mode first and then flash
 * to dark, which is the exact "flash of wrong theme" this exists to avoid.
 * Reads localStorage first (explicit user choice persists across visits),
 * falling back to the Customizer default, resolving 'auto' via the OS
 * prefers-color-scheme media query.
 */
add_action('wp_head', function () {
    if (! get_theme_mod('prt_dark_enable', true)) {
        return;
    }
    $def = esc_js(get_theme_mod('prt_dark_default', 'light'));
    echo "<script>(function(){try{var d='{$def}';var m=localStorage.getItem('prt-theme');if(!m){m=(d==='auto')?(matchMedia('(prefers-color-scheme: dark)').matches?'dark':'light'):d;}if(m==='dark'){document.documentElement.classList.add('prt-dark');}}catch(e){}})();</script>\n";
}, 2);

/**
 * Wire up the .prt-theme-toggle button click handler: flips the `prt-dark`
 * class, persists the explicit choice to localStorage (read by the no-flash
 * script above on the next page load), and keeps aria-pressed in sync for
 * screen readers. Runs late in wp_footer (priority 60) so it fires after the
 * toggle button markup itself has definitely been output by the header.
 */
add_action('wp_footer', function () {
    if (! get_theme_mod('prt_dark_enable', true)) {
        return;
    }
    echo "<script>(function(){var b=document.querySelector('.prt-theme-toggle');if(!b)return;function set(d){document.documentElement.classList.toggle('prt-dark',d);try{localStorage.setItem('prt-theme',d?'dark':'light');}catch(e){}b.setAttribute('aria-pressed',d?'true':'false');}b.setAttribute('aria-pressed',document.documentElement.classList.contains('prt-dark')?'true':'false');b.addEventListener('click',function(){set(!document.documentElement.classList.contains('prt-dark'));});})();</script>\n";
}, 60);

/**
 * Dark-mode navbar surface. By default the .site-header is transparent-ish and
 * just blends into the dark page background, so it doesn't read as a distinct
 * bar. Give it an explicit darker tone + guaranteed light text/icons in dark
 * mode. Emitted at a late prt_head_end priority and with !important so it also
 * wins over the sticky-header background (which is hard-coded light in
 * resources/css/page-templates.css).
 */
add_action('prt_head_end', function () {
    if (! get_theme_mod('prt_dark_enable', true)) {
        return;
    }
    echo "\n<style id=\"prt-dark-navbar\">"
        . 'html.prt-dark .site-header{background:#1c1f24!important;border-bottom-color:#2c2f36;}'
        . 'html.prt-dark .site-header,html.prt-dark .site-header .brand,html.prt-dark .site-header .brand-name,'
        . 'html.prt-dark .header-nav-list a,html.prt-dark .menu-toggle,'
        . 'html.prt-dark .prt-theme-toggle,html.prt-dark .header-social a{color:#f3f1ea;}'
        . 'html.prt-dark .header-nav-list a:hover{color:#fff;background:rgba(255,255,255,.08);}'
        . "</style>\n";
}, 20);
