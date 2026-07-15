<?php

/**
 * Style Kits — a one-click "apply a full palette + font + radius preset"
 * control, living inside the main Theme Options panel.
 *
 * This used to be one section of a separate "⚡ Quick Setup" panel that
 * duplicated controls already in Theme Options (branding colors, typography,
 * header CTA, social URLs, footer tagline) via shared setting keys. Those
 * were removed so there's a single, consolidated Theme Options list instead
 * of two overlapping panels — only the Style Kits picker (which doesn't
 * exist anywhere else as a live-preview control) stayed, moved here.
 */

namespace App;

add_action('customize_register', function (\WP_Customize_Manager $wp) {
    // Shared guarded helper — see prt_ensure_theme_options_panel() in app/customizer.php.
    prt_ensure_theme_options_panel($wp);

    $wp->add_section('prt_style_kit_section', [
        'title'       => __('Style Kits', 'pressroot'),
        'panel'       => 'prt_theme_options',
        'description' => __('Apply a one-click design preset (palette + fonts + radius), then fine-tune anything below. Full import/export tools live at Appearance → Pressroot → Site Types → Advanced.', 'pressroot'),
    ]);

    $kits = function_exists('App\\prt_style_kits') ? array_combine(
        array_keys(prt_style_kits()),
        array_column(prt_style_kits(), 'label')
    ) : [];
    $kits = array_merge(['' => __('— Choose a preset —', 'pressroot')], $kits);

    $wp->add_setting('prt_qs_apply_kit', [
        'default'           => '',
        'sanitize_callback' => 'sanitize_key',
        'transport'         => 'postMessage',
    ]);
    $wp->add_control('prt_qs_apply_kit', [
        'label'       => __('Apply style kit', 'pressroot'),
        'description' => __('Selecting a kit applies its colors and fonts immediately. Save to persist.', 'pressroot'),
        'section'     => 'prt_style_kit_section',
        'type'        => 'select',
        'choices'     => $kits,
    ]);
}, 24);

/* ── Style-Kit controls-pane handler ─────────────────────────────────────── *
 * Must run in the CONTROLS pane (customize_controls_enqueue_scripts), not the
 * preview iframe. Calling wp.customize(key).set() from the controls side
 * updates both the control UI and triggers the transport, so the change is
 * actually queued for Save.
 */
add_action('customize_controls_enqueue_scripts', function () {
    if (! function_exists('App\\prt_style_kits')) {
        return;
    }
    $kits_json = wp_json_encode(prt_style_kits());
    wp_add_inline_script(
        'customize-controls',
        "(function(){
            var kits = {$kits_json};
            wp.customize('prt_qs_apply_kit', function(setting){
                setting.bind(function(kit){
                    if (!kit || !kits[kit]) return;
                    var mods = kits[kit].mods || {};
                    Object.keys(mods).forEach(function(key){
                        var s = wp.customize(key);
                        if (s) { s.set(mods[key]); }
                    });
                    // Reset selector to placeholder so picking the same kit again still fires
                    setTimeout(function(){ setting.set(''); }, 100);
                });
            });
        })();"
    );
});
