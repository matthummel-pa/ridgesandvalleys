<?php

/**
 * Customizer housekeeping (runs last, priority 999).
 * Reorganises the Theme Options panel for a cleaner, more discoverable UI without
 * touching the source modules that register each control:
 *   - splits the overloaded "Navigation" section into focused sections
 *     (Navigation · Social Icons · Responsive), and re-parents stray controls,
 *   - orders every section logically,
 *   - adds a short description to each section.
 */

namespace App;

/**
 * Post-processes the Theme Options panel after every other module has had a
 * chance to register its own sections/controls. Priority 999 is essential:
 * WP_Customize_Manager only exposes get_section()/get_control() for controls
 * that already exist, so this has to run after all of them are registered,
 * and it mutates ->section / ->priority / ->description directly on the
 * already-registered objects rather than re-registering anything — a purely
 * cosmetic reorganization pass with no effect on what each control does.
 * Bails out entirely if the panel doesn't exist (e.g. this file loaded
 * standalone / the panel-owning module was removed).
 */
add_action('customize_register', function ($wp) {
    if (! $wp->get_panel('prt_theme_options')) {
        return;
    }

    // --- New grouping sections ---
    // "Social Icons" and "Responsive" don't have a dedicated home elsewhere;
    // create them here (defensively, in case another module already has)
    // so the controls re-parented below have somewhere to move to.
    if (! $wp->get_section('prt_social_section')) {
        $wp->add_section('prt_social_section', [
            'title'       => __('Social Icons', 'pressroot'),
            'panel'       => 'prt_theme_options',
            'description' => __('Where social icons appear, their style, and your account URLs.', 'pressroot'),
        ]);
    }
    if (! $wp->get_section('prt_responsive_section')) {
        $wp->add_section('prt_responsive_section', [
            'title'       => __('Responsive (mobile & tablet)', 'pressroot'),
            'panel'       => 'prt_theme_options',
            'description' => __('Per-device visibility and widths. Mobile ≤640px · tablet 641–1024px.', 'pressroot'),
        ]);
    }

    // --- Re-parent controls into better homes (runtime only) ---
    // Each control below was registered elsewhere (often bundled into a
    // catch-all "Navigation" section by the module that owns its setting);
    // moving it here at runtime avoids having to touch that module's code
    // just to change where the control appears in the UI.
    $move = [
        'prt_social_section' => [
            'prt_nav_social', 'prt_nav_social_align', 'prt_social_style', 'prt_social_size', 'prt_social_shape',
            'prt_social_color', 'prt_social_bg', 'prt_social_hover',
            'prt_social_gap', 'prt_social_icon_style', 'prt_social_hover_bg',
            'prt_social_border_width', 'prt_social_border_color', 'prt_social_css',
            'prt_social_linkedin', 'prt_social_github', 'prt_social_devto', 'prt_social_x', 'prt_social_bluesky',
            'prt_social_youtube', 'prt_social_instagram', 'prt_social_facebook', 'prt_social_mastodon', 'prt_social_email', 'prt_social_rss',
        ],
        'prt_responsive_section' => [
            'prt_social_nav_hide_mobile', 'prt_social_nav_hide_desktop', 'prt_social_top_hide_mobile',
            'prt_topcta_hide_mobile', 'prt_navcta_hide_mobile', 'prt_navcta_hide_tablet',
            'prt_topbar_oneline_tablet', 'prt_logo_shrink_mobile', 'prt_menu_label_hide_mobile',
            'prt_topbar_width_tablet', 'prt_topbar_width_mobile', 'prt_nav_width_tablet', 'prt_nav_width_mobile',
            'prt_msgbar_width_tablet', 'prt_msgbar_width_mobile',
        ],
        'prt_headerlayout_section' => [
            'prt_logo_align', 'prt_darkicon_align', 'prt_popbtn_align', 'prt_cta_align',
            'prt_bar_ann', 'prt_bar_top', 'prt_bar_nav',
        ],
        'prt_popout_section' => [
            'prt_popout_width', 'prt_popout_cols', 'prt_popout_block_cols',
            'prt_pop_align', 'prt_pop_pad_y', 'prt_pop_font', 'prt_pop_weight', 'prt_pop_transform', 'prt_pop_gap',
        ],
    ];
    foreach ($move as $section => $ids) {
        $base = 30; // place moved controls after a section's native controls
        foreach ($ids as $i => $id) {
            $c = $wp->get_control($id);
            if ($c) {
                // Missing controls (e.g. a referenced ID that was renamed/removed
                // elsewhere) are silently skipped rather than erroring, since this
                // is cosmetic-only cleanup and shouldn't be able to break the page.
                $c->section  = $section;
                $c->priority = $base + $i;
            }
        }
    }

    // --- Logical section order within Theme Options ---
    // Explicit priorities (rather than relying on registration order) so the
    // panel reads top-to-bottom in the order a developer would actually want
    // to configure a new site, regardless of which file registered which
    // section first.
    $order = [
        'prt_style_kit_section' => 15,
        'prt_colors' => 20, 'prt_type' => 30, 'prt_type_adv' => 35,
        'prt_headerlayout_section' => 40, 'prt_nav_section' => 45, 'prt_popout_section' => 50,
        'prt_social_section' => 55, 'prt_topbar_section' => 60, 'prt_ann_section' => 65,
        'prt_hero_section' => 70, 'prt_anim_section' => 75, 'prt_footer_section' => 80,
        'prt_content_section' => 85, 'prt_layout_section' => 90, 'prt_responsive_section' => 95,
        'prt_dark_section' => 100, 'prt_seo_section' => 110, 'prt_perf_section' => 120,
        'prt_code_section' => 130, 'prt_news_section' => 140, 'prt_cookie_section' => 150,
        'prt_extras_section' => 160, 'prt_wl_section' => 170, 'prt_dev_section' => 180,
    ];
    foreach ($order as $sid => $prio) {
        $s = $wp->get_section($sid);
        if ($s) {
            $s->priority = $prio;
        }
    }

    // --- Short, dev-friendly section descriptions (only if one isn't already set) ---
    // Only fills in a description when the section doesn't already have one, so
    // this never overwrites a more specific description set by the module that
    // owns the section.
    $desc = [
        'prt_nav_section'         => __('Primary menu layout (flexbox) and link styling.', 'pressroot'),
        'prt_popout_section'      => __('Off-canvas menu: breakpoints, panel style, columns and item styling.', 'pressroot'),
        'prt_topbar_section'      => __('Slim utility bar above the main navigation.', 'pressroot'),
        'prt_ann_section'         => __('Site-wide announcement bar — schedulable and dismissible.', 'pressroot'),
        'prt_headerlayout_section' => __('Header sizing, element placement, sticky/transparent behaviour and bar order.', 'pressroot'),
        'prt_layout_section'      => __('Content width and sidebar per content type.', 'pressroot'),
        'prt_content_section'     => __('Editable copy for the CTA band and page intros.', 'pressroot'),
        'prt_anim_section'        => __('On-scroll reveal animations applied site-wide.', 'pressroot'),
    ];
    foreach ($desc as $sid => $d) {
        $s = $wp->get_section($sid);
        if ($s && empty($s->description)) {
            $s->description = $d;
        }
    }
}, 999);
