<?php

/**
 * Header behaviors: sticky, shrink-on-scroll, and transparent (overlay) header.
 * Controls live in the consolidated "Header Layout" section (prt_headerlayout_section).
 * Adds body classes + scoped CSS/JS. No template edits required.
 *
 * This file only registers the Customizer *controls* for these three
 * behaviors and reacts to them (body classes, inline CSS/JS); the
 * `prt_headerlayout_section` section itself is registered elsewhere
 * (app/header-layout.php) so several header-related files can all add
 * controls into one consolidated "Header Layout" panel section instead of
 * fragmenting it across multiple Customizer sections.
 */

namespace App;

/**
 * Add this file's controls (sticky / shrink / transparent) into the shared
 * "Header Layout" Customizer section.
 *
 * NOTE(audit): this callback runs at priority 23, but header-layout.php
 * registers the `prt_headerlayout_section` section itself at priority 27 —
 * i.e. this file's add_control() calls reference a section that doesn't
 * exist yet at the point they run. The Core Customizer tolerates this
 * because sections/controls are resolved into a tree lazily when the panel
 * is rendered/saved rather than at registration time, so it works today, but
 * the priority ordering is backwards from what you'd expect and is fragile
 * if that lazy-resolution behavior ever changes.
 */
add_action('customize_register', function ($wp) {
    // Shared guarded helper — see prt_ensure_theme_options_panel() in app/customizer.php.
    prt_ensure_theme_options_panel($wp);

    $wp->add_setting('prt_header_sticky', ['default' => false, 'sanitize_callback' => 'wp_validate_boolean']);
    $wp->add_control('prt_header_sticky', ['label' => __('Sticky header', 'pressroot'), 'section' => 'prt_headerlayout_section', 'type' => 'checkbox']);

    $wp->add_setting('prt_header_shrink', ['default' => false, 'sanitize_callback' => 'wp_validate_boolean']);
    $wp->add_control('prt_header_shrink', ['label' => __('Shrink on scroll (needs sticky)', 'pressroot'), 'section' => 'prt_headerlayout_section', 'type' => 'checkbox']);

    $wp->add_setting('prt_header_transparent', ['default' => 'none', 'sanitize_callback' => 'sanitize_key']);
    $wp->add_control('prt_header_transparent', [
        'label'   => __('Transparent overlay header', 'pressroot'),
        'section' => 'prt_headerlayout_section',
        'type'    => 'select',
        'choices' => ['none' => __('Off', 'pressroot'), 'front' => __('Front page only', 'pressroot'), 'all' => __('All pages', 'pressroot')],
    ]);
}, 23);

/**
 * Translate the three Customizer toggles into <body> classes that the
 * theme's CSS/JS hook into (.prt-sticky, .prt-shrink, .prt-transparent).
 * Using body classes (rather than inline styles) lets the shared stylesheet
 * define the actual visual rules and keeps this file limited to on/off logic.
 */
add_filter('body_class', function ($c) {
    if (get_theme_mod('prt_header_sticky', false)) {
        $c[] = 'prt-sticky';
    }
    // Shrink-on-scroll only makes sense combined with sticky positioning, so
    // it's gated on both settings even though they're stored independently.
    if (get_theme_mod('prt_header_sticky', false) && get_theme_mod('prt_header_shrink', false)) {
        $c[] = 'prt-shrink';
    }
    $tr = get_theme_mod('prt_header_transparent', 'none');
    if ($tr === 'all' || ($tr === 'front' && is_front_page())) {
        $c[] = 'prt-transparent';
    }
    return $c;
});

/**
 * Emit the CSS for the scroll-triggered visual states (shadow, shrink,
 * transparent-to-solid) plus the tiny scroll listener that toggles the
 * `.prt-scrolled` class those rules key off of. Hooked to the theme's custom
 * `prt_head_end` action (fired late in <head>) at priority 15, and bails
 * out entirely when neither sticky nor transparent is enabled so pages with
 * default header behavior don't get an empty <style> tag.
 */
add_action('prt_head_end', function () {
    $sticky = get_theme_mod('prt_header_sticky', false);
    $tr     = get_theme_mod('prt_header_transparent', 'none');
    if (! $sticky && $tr === 'none') {
        return;
    }
    $css = '';
    // Note: .site-header is already sticky by default (page-templates.css), so
    // the checkbox mainly gates the extra shrink/box-shadow behavior below.
    if ($sticky) {
        $css .= 'body.prt-sticky.prt-scrolled .site-header{box-shadow:0 4px 20px rgba(23,25,30,.08);}';
        $css .= 'body.prt-shrink.prt-scrolled .site-header-inner{padding-top:8px;padding-bottom:8px;min-height:0;}';
    }
    if ($tr !== 'none') {
        $css .= 'body.prt-transparent .site-header{background:transparent;backdrop-filter:none;-webkit-backdrop-filter:none;border-bottom-color:transparent;}';
        $css .= 'body.prt-transparent.prt-scrolled .site-header{background:rgba(251,250,247,.92);backdrop-filter:blur(12px);-webkit-backdrop-filter:blur(12px);border-bottom-color:var(--color-line);}';
    }
    echo "\n<style id=\"prt-headerbe\">" . $css . "</style>\n";
    echo "<script>(function(){function s(){document.body.classList.toggle('prt-scrolled',window.scrollY>10);}window.addEventListener('scroll',s,{passive:true});s();})();</script>";
}, 15);
