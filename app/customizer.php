<?php

/**
 * Theme Options in the Customizer.
 */

namespace App;

/**
 * Shared theme-color palette for the navigation dropdowns. Instead of raw color
 * pickers, the nav color controls choose from the site's palette tokens; the
 * token is mapped to a CSS custom property at render time. A blank value means
 * "theme default" (no override emitted). `transparent` is offered only where a
 * fill/background can be see-through.
 */
function rv_nav_color_choices(bool $with_transparent = false): array
{
    $choices = ['' => __('Theme default', 'sage')];
    if ($with_transparent) {
        $choices['transparent'] = __('Transparent', 'sage');
    }
    return $choices + [
        'ink'   => __('Ink (dark)', 'sage'),
        'pine'  => __('Pine (green)', 'sage'),
        'clay'  => __('Clay (orange)', 'sage'),
        'wheat' => __('Wheat (gold)', 'sage'),
        'sage'  => __('Sage (soft green)', 'sage'),
        'slate' => __('Slate (blue-grey)', 'sage'),
        'cream' => __('Cream (light)', 'sage'),
        'paper' => __('Paper (page background)', 'sage'),
        'line'  => __('Line (subtle grey)', 'sage'),
        'white' => __('White', 'sage'),
    ];
}

/** Map a palette token to its CSS value. Unknown/blank tokens return ''. */
function rv_nav_color_value(string $token): string
{
    $map = [
        'ink'         => 'var(--color-ink)',
        'pine'        => 'var(--color-pine)',
        'clay'        => 'var(--color-clay)',
        'wheat'       => 'var(--color-wheat)',
        'sage'        => 'var(--color-sage)',
        'slate'       => 'var(--color-slate)',
        'cream'       => 'var(--color-cream)',
        'paper'       => 'var(--color-paper)',
        'line'        => 'var(--color-line)',
        'white'       => '#ffffff',
        'transparent' => 'transparent',
    ];
    return $map[$token] ?? '';
}

/** Sanitizer factory: keep the value only if it's an allowed palette token. */
function rv_nav_color_sanitize(bool $with_transparent = false): callable
{
    return function ($v) use ($with_transparent) {
        return array_key_exists((string) $v, rv_nav_color_choices($with_transparent)) ? (string) $v : '';
    };
}

add_action('customize_register', function ($wp_customize) {
    /**
     * Drag-and-drop control for the social icon order. Renders a sortable list of
     * platforms; the order is saved as a comma-separated list of keys in the
     * hidden, Customizer-bound input (jQuery UI Sortable is wired up in the
     * customize_controls_enqueue_scripts hook below).
     */
    if (! class_exists(__NAMESPACE__ . '\\RV_Social_Order_Control') && class_exists('WP_Customize_Control')) {
        class RV_Social_Order_Control extends \WP_Customize_Control
        {
            public $type = 'rv_social_order';

            public function render_content(): void
            {
                $platforms = social_platforms();
                $saved     = array_filter(array_map('trim', explode(',', (string) $this->value())));
                $keys      = array_values(array_intersect($saved, array_keys($platforms)));
                foreach (array_keys($platforms) as $k) {
                    if (! in_array($k, $keys, true)) {
                        $keys[] = $k;
                    }
                }
                ?>
                <span class="customize-control-title"><?php echo esc_html($this->label); ?></span>
                <?php if ($this->description) : ?>
                    <span class="description customize-control-description"><?php echo esc_html($this->description); ?></span>
                <?php endif; ?>
                <ul class="rv-sortable">
                    <?php foreach ($keys as $k) : ?>
                        <li class="rv-sortable-item" data-key="<?php echo esc_attr($k); ?>">
                            <span class="dashicons dashicons-move rv-sortable-handle" aria-hidden="true"></span>
                            <?php echo esc_html($platforms[$k]); ?>
                        </li>
                    <?php endforeach; ?>
                </ul>
                <input type="hidden" class="rv-sortable-value" <?php $this->link(); ?> value="<?php echo esc_attr(implode(',', $keys)); ?>">
                <?php
            }
        }
    }

    $wp_customize->add_panel('rv_theme_options', [
        'title'    => __('Theme Options', 'sage'),
        'priority' => 20,
    ]);

    /**
     * Optional dark-mode logo, next to the core Site Identity logo. Both come from
     * the Media Library. When set, the header shows it in dark mode and over the
     * transparent hero header; the footer (dark band) prefers it too. If no logo
     * image is set at all, the theme falls back to the site title as text.
     */
    $wp_customize->add_setting('rv_logo_dark', ['default' => 0, 'sanitize_callback' => 'absint']);
    $wp_customize->add_control(new \WP_Customize_Media_Control($wp_customize, 'rv_logo_dark', [
        'label'       => __('Dark-mode logo (optional)', 'sage'),
        'description' => __('Shown in dark mode, over the transparent hero header, and in the footer. Leave empty to use the main logo in both modes. With no logo set anywhere, the site title is used as text.', 'sage'),
        'section'     => 'title_tagline',
        'mime_type'   => 'image',
        'priority'    => 9,
    ]));

    /* Colors */
    $wp_customize->add_section('rv_colors', ['title' => __('Colors', 'sage'), 'panel' => 'rv_theme_options']);
    $colors = [
        'rv_color_brand'  => [__('Brand / action (Ridge Pine)', 'sage'), '#2E5245'],
        'rv_color_accent' => [__('Accent (Gettysburg Clay)', 'sage'), '#B0553A'],
        'rv_color_wheat'  => [__('Highlight (Wheat Gold)', 'sage'), '#E0A73C'],
        'rv_color_sage'   => [__('Soft accent (Sage)', 'sage'), '#97A88E'],
        'rv_color_paper'  => [__('Page background (Cream Paper)', 'sage'), '#F7F1E6'],
        'rv_color_ink'    => [__('Headings (Bark Ink)', 'sage'), '#23201B'],
    ];
    foreach ($colors as $id => $data) {
        $wp_customize->add_setting($id, ['default' => $data[1], 'sanitize_callback' => 'sanitize_hex_color']);
        $wp_customize->add_control(new \WP_Customize_Color_Control($wp_customize, $id, [
            'label'   => $data[0],
            'section' => 'rv_colors',
        ]));
    }

    /* Layout */
    $wp_customize->add_section('rv_layout', ['title' => __('Layout', 'sage'), 'panel' => 'rv_theme_options']);
    $wp_customize->add_setting('rv_container_width', ['default' => 1180, 'sanitize_callback' => 'absint']);
    $wp_customize->add_control('rv_container_width', [
        'label'       => __('Content shell width (px)', 'sage'),
        'section'     => 'rv_layout',
        'type'        => 'number',
        'input_attrs' => ['min' => 960, 'max' => 1440, 'step' => 20],
    ]);

    /* Hero positioning (flexbox) */
    $wp_customize->add_setting('rv_hero_align', ['default' => 'left', 'sanitize_callback' => 'sanitize_key']);
    $wp_customize->add_control('rv_hero_align', [
        'label'       => __('Hero content alignment', 'sage'),
        'description' => __('Positions the hero eyebrow, heading, text, and buttons with flexbox.', 'sage'),
        'section'     => 'rv_layout',
        'type'        => 'select',
        'choices'     => ['left' => __('Left', 'sage'), 'center' => __('Center', 'sage'), 'right' => __('Right', 'sage')],
    ]);
    $wp_customize->add_setting('rv_hero_valign', ['default' => 'center', 'sanitize_callback' => 'sanitize_key']);
    $wp_customize->add_control('rv_hero_valign', [
        'label'   => __('Hero vertical position', 'sage'),
        'section' => 'rv_layout',
        'type'    => 'select',
        'choices' => ['top' => __('Top', 'sage'), 'center' => __('Center', 'sage'), 'bottom' => __('Bottom', 'sage')],
    ]);

    /* Header */
    $wp_customize->add_section('rv_header', ['title' => __('Header', 'sage'), 'panel' => 'rv_theme_options']);
    // Header is split into focused accordion sections for easier scanning. The
    // controls below are registered against 'rv_header' as before, then moved
    // into these sub-sections by ID at the end of the header block — so each
    // group is its own open/close accordion in the Customizer.
    $wp_customize->add_section('rv_header_topbar', ['title' => __('Header · Top bar', 'sage'), 'panel' => 'rv_theme_options']);
    $wp_customize->add_section('rv_header_nav', ['title' => __('Header · Navigation', 'sage'), 'panel' => 'rv_theme_options']);
    $wp_customize->add_section('rv_header_toggle', ['title' => __('Header · Light / Dark toggle', 'sage'), 'panel' => 'rv_theme_options']);
    $wp_customize->add_section('rv_header_mobile', ['title' => __('Header · Mobile / off-canvas menu', 'sage'), 'panel' => 'rv_theme_options']);
    $wp_customize->add_setting('rv_cta_text', ['default' => __('Get a quote', 'sage'), 'sanitize_callback' => 'sanitize_text_field']);
    $wp_customize->add_control('rv_cta_text', ['label' => __('Header button label', 'sage'), 'section' => 'rv_header', 'type' => 'text']);
    $wp_customize->add_setting('rv_cta_url', ['default' => '/contact/', 'sanitize_callback' => 'sanitize_text_field']);
    $wp_customize->add_control('rv_cta_url', ['label' => __('Header button link', 'sage'), 'section' => 'rv_header', 'type' => 'text']);
    $wp_customize->add_setting('rv_topbar_text', ['default' => __('Gettysburg &amp; South Central PA', 'sage'), 'sanitize_callback' => 'wp_kses_post']);
    $wp_customize->add_control('rv_topbar_text', ['label' => __('Top bar text', 'sage'), 'section' => 'rv_header', 'type' => 'text']);
    $wp_customize->add_setting('rv_topbar_social', ['default' => true, 'sanitize_callback' => 'rest_sanitize_boolean']);
    $wp_customize->add_control('rv_topbar_social', [
        'label'       => __('Show social icons in the top bar', 'sage'),
        'description' => __('Shows an icon for each Social Links URL you’ve filled in (Theme Options → Social Links).', 'sage'),
        'section'     => 'rv_header',
        'type'        => 'checkbox',
    ]);
    $wp_customize->add_setting('rv_topbar_color', [
        'default'           => 'pine',
        'sanitize_callback' => function ($v) {
            return in_array($v, ['pine', 'clay', 'ink', 'wheat', 'cream'], true) ? $v : 'pine';
        },
    ]);
    $wp_customize->add_control('rv_topbar_color', [
        'label'   => __('Top bar color', 'sage'),
        'section' => 'rv_header',
        'type'    => 'select',
        'choices' => [
            'pine'  => __('Green (deep pine)', 'sage'),
            'clay'  => __('Orange (clay)', 'sage'),
            'ink'   => __('Dark (ink)', 'sage'),
            'wheat' => __('Gold (wheat)', 'sage'),
            'cream' => __('Light (cream)', 'sage'),
        ],
    ]);
    $wp_customize->add_setting('rv_topbar_font_size', ['default' => 12, 'sanitize_callback' => 'absint']);
    $wp_customize->add_control('rv_topbar_font_size', [
        'label'       => __('Top bar text size (px)', 'sage'),
        'section'     => 'rv_header',
        'type'        => 'range',
        'input_attrs' => ['min' => 10, 'max' => 18, 'step' => 1],
    ]);
    $wp_customize->add_setting('rv_topbar_font_weight', [
        'default'           => '400',
        'sanitize_callback' => function ($v) {
            return in_array($v, ['400', '500', '600', '700'], true) ? $v : '400';
        },
    ]);
    $wp_customize->add_control('rv_topbar_font_weight', [
        'label'   => __('Top bar text weight', 'sage'),
        'section' => 'rv_header',
        'type'    => 'select',
        'choices' => [
            '400' => __('Normal', 'sage'),
            '500' => __('Medium', 'sage'),
            '600' => __('Semibold', 'sage'),
            '700' => __('Bold', 'sage'),
        ],
    ]);
    $wp_customize->add_setting('rv_topbar_hide_on_scroll', ['default' => false, 'sanitize_callback' => 'rest_sanitize_boolean']);
    $wp_customize->add_control('rv_topbar_hide_on_scroll', [
        'label'       => __('Hide top bar on scroll', 'sage'),
        'description' => __('Collapses the top bar once the visitor scrolls down, leaving just the main navigation. It reappears at the top of the page.', 'sage'),
        'section'     => 'rv_header',
        'type'        => 'checkbox',
    ]);
    $wp_customize->add_setting('rv_header_sticky', ['default' => true, 'sanitize_callback' => 'rest_sanitize_boolean']);
    $wp_customize->add_control('rv_header_sticky', [
        'label'       => __('Fixed (sticky) navigation', 'sage'),
        'description' => __('Keeps the header pinned to the top as visitors scroll.', 'sage'),
        'section'     => 'rv_header',
        'type'        => 'checkbox',
    ]);
    $wp_customize->add_setting('rv_header_transparent', ['default' => false, 'sanitize_callback' => 'rest_sanitize_boolean']);
    $wp_customize->add_control('rv_header_transparent', [
        'label'       => __('Transparent header over hero', 'sage'),
        'description' => __('On pages with a hero, the header overlays the hero image with light text, then turns solid as you scroll.', 'sage'),
        'section'     => 'rv_header',
        'type'        => 'checkbox',
    ]);

    /* Header layout & responsive */
    $wp_customize->add_setting('rv_header_height', ['default' => 72, 'sanitize_callback' => 'absint']);
    $wp_customize->add_control('rv_header_height', [
        'label'       => __('Header height (px)', 'sage'),
        'section'     => 'rv_header',
        'type'        => 'range',
        'input_attrs' => ['min' => 56, 'max' => 104, 'step' => 1],
    ]);
    $wp_customize->add_setting('rv_logo_max_height', ['default' => 52, 'sanitize_callback' => 'absint']);
    $wp_customize->add_control('rv_logo_max_height', [
        'label'       => __('Logo max height (px)', 'sage'),
        'description' => __('Caps the header logo height so tall logos don’t stretch the header.', 'sage'),
        'section'     => 'rv_header',
        'type'        => 'range',
        'input_attrs' => ['min' => 24, 'max' => 72, 'step' => 1],
    ]);
    $wp_customize->add_setting('rv_nav_breakpoint', ['default' => 860, 'sanitize_callback' => 'absint']);
    $wp_customize->add_control('rv_nav_breakpoint', [
        'label'       => __('Mobile menu breakpoint (px)', 'sage'),
        'description' => __('Screen width at/below which the menu collapses into the hamburger. Raise it if a long menu crowds the header on tablets.', 'sage'),
        'section'     => 'rv_header',
        'type'        => 'range',
        'input_attrs' => ['min' => 720, 'max' => 1100, 'step' => 10],
    ]);
    $wp_customize->add_setting('rv_cta_mobile', ['default' => false, 'sanitize_callback' => 'rest_sanitize_boolean']);
    $wp_customize->add_control('rv_cta_mobile', [
        'label'       => __('Keep header button on mobile', 'sage'),
        'description' => __('The header CTA button hides on small screens by default. Turn on to keep it next to the menu icon.', 'sage'),
        'section'     => 'rv_header',
        'type'        => 'checkbox',
    ]);
    $wp_customize->add_setting('rv_header_shadow', ['default' => false, 'sanitize_callback' => 'rest_sanitize_boolean']);
    $wp_customize->add_control('rv_header_shadow', [
        'label'       => __('Header shadow on scroll', 'sage'),
        'description' => __('Adds a soft drop shadow under the header once the visitor scrolls.', 'sage'),
        'section'     => 'rv_header',
        'type'        => 'checkbox',
    ]);

    /* Top bar — alignment & responsive (shown in the Header · Top bar accordion) */
    $wp_customize->add_setting('rv_topbar_align', [
        'default'           => 'between',
        'sanitize_callback' => function ($v) {
            return in_array($v, ['between', 'left', 'center', 'right'], true) ? $v : 'between';
        },
    ]);
    $wp_customize->add_control('rv_topbar_align', [
        'label'   => __('Top bar alignment', 'sage'),
        'section' => 'rv_header_topbar',
        'type'    => 'select',
        'choices' => [
            'between' => __('Text left, social right (default)', 'sage'),
            'left'    => __('All left', 'sage'),
            'center'  => __('All centered', 'sage'),
            'right'   => __('All right', 'sage'),
        ],
    ]);
    $wp_customize->add_setting('rv_topbar_hide_mobile', ['default' => false, 'sanitize_callback' => 'rest_sanitize_boolean']);
    $wp_customize->add_control('rv_topbar_hide_mobile', [
        'label'       => __('Hide top bar on mobile', 'sage'),
        'description' => __('Hides the whole top bar on small screens (≤640px) for a cleaner mobile header.', 'sage'),
        'section'     => 'rv_header_topbar',
        'type'        => 'checkbox',
    ]);

    /* Mobile / off-canvas menu (Header · Mobile / off-canvas menu accordion) */
    $wp_customize->add_setting('rv_nav_mode', [
        'default'           => 'responsive',
        'sanitize_callback' => function ($v) {
            return in_array($v, ['responsive', 'hamburger'], true) ? $v : 'responsive';
        },
    ]);
    $wp_customize->add_control('rv_nav_mode', [
        'label'       => __('Navigation style', 'sage'),
        'description' => __('Regular menu switches to the off-canvas menu only on small screens. Always off-canvas uses the menu icon and slide-in panel at every screen size, including desktop.', 'sage'),
        'section'     => 'rv_header_mobile',
        'type'        => 'select',
        'choices'     => [
            'responsive' => __('Regular menu (off-canvas on mobile)', 'sage'),
            'hamburger'  => __('Always off-canvas (menu icon everywhere)', 'sage'),
        ],
    ]);
    $wp_customize->add_setting('rv_oc_side', [
        'default'           => 'right',
        'sanitize_callback' => function ($v) {
            return in_array($v, ['right', 'left'], true) ? $v : 'right';
        },
    ]);
    $wp_customize->add_control('rv_oc_side', [
        'label'   => __('Panel slides in from', 'sage'),
        'section' => 'rv_header_mobile',
        'type'    => 'select',
        'choices' => ['right' => __('Right', 'sage'), 'left' => __('Left', 'sage')],
    ]);
    $wp_customize->add_setting('rv_oc_width', ['default' => 360, 'sanitize_callback' => 'absint']);
    $wp_customize->add_control('rv_oc_width', [
        'label'       => __('Panel width (px)', 'sage'),
        'description' => __('Capped to 92% of the screen so it never runs off small screens.', 'sage'),
        'section'     => 'rv_header_mobile',
        'type'        => 'range',
        'input_attrs' => ['min' => 240, 'max' => 480, 'step' => 10],
    ]);
    $wp_customize->add_setting('rv_oc_bg', [
        'default'           => 'night',
        'sanitize_callback' => function ($v) {
            return in_array($v, ['night', 'ink', 'pine', 'clay', 'paper', 'white'], true) ? $v : 'night';
        },
    ]);
    $wp_customize->add_control('rv_oc_bg', [
        'label'       => __('Panel background', 'sage'),
        'description' => __('If you pick a light background (Paper/White), also set a dark item color below.', 'sage'),
        'section'     => 'rv_header_mobile',
        'type'        => 'select',
        'choices'     => [
            'night' => __('Night (dark, default)', 'sage'),
            'ink'   => __('Ink (dark)', 'sage'),
            'pine'  => __('Pine (green)', 'sage'),
            'clay'  => __('Clay (orange)', 'sage'),
            'paper' => __('Paper (light)', 'sage'),
            'white' => __('White', 'sage'),
        ],
    ]);
    $wp_customize->add_setting('rv_oc_item_color', ['default' => '', 'sanitize_callback' => rv_nav_color_sanitize()]);
    $wp_customize->add_control('rv_oc_item_color', [
        'label'   => __('Menu item color', 'sage'),
        'section' => 'rv_header_mobile',
        'type'    => 'select',
        'choices' => rv_nav_color_choices(),
    ]);
    $wp_customize->add_setting('rv_oc_hover_color', ['default' => '', 'sanitize_callback' => rv_nav_color_sanitize()]);
    $wp_customize->add_control('rv_oc_hover_color', [
        'label'   => __('Menu item hover color', 'sage'),
        'section' => 'rv_header_mobile',
        'type'    => 'select',
        'choices' => rv_nav_color_choices(),
    ]);
    $wp_customize->add_setting('rv_oc_font_size', ['default' => 22, 'sanitize_callback' => 'absint']);
    $wp_customize->add_control('rv_oc_font_size', [
        'label'       => __('Menu item font size (px)', 'sage'),
        'section'     => 'rv_header_mobile',
        'type'        => 'range',
        'input_attrs' => ['min' => 14, 'max' => 30, 'step' => 1],
    ]);
    $wp_customize->add_setting('rv_oc_align', [
        'default'           => 'left',
        'sanitize_callback' => function ($v) {
            return in_array($v, ['left', 'center'], true) ? $v : 'left';
        },
    ]);
    $wp_customize->add_control('rv_oc_align', [
        'label'   => __('Menu item alignment', 'sage'),
        'section' => 'rv_header_mobile',
        'type'    => 'select',
        'choices' => ['left' => __('Left', 'sage'), 'center' => __('Center', 'sage')],
    ]);
    $wp_customize->add_setting('rv_oc_dividers', ['default' => true, 'sanitize_callback' => 'rest_sanitize_boolean']);
    $wp_customize->add_control('rv_oc_dividers', [
        'label'       => __('Show dividers between items', 'sage'),
        'section'     => 'rv_header_mobile',
        'type'        => 'checkbox',
    ]);

    // Off-canvas close button
    $wp_customize->add_setting('rv_oc_close_icon', [
        'default'           => 'x',
        'sanitize_callback' => function ($v) {
            return in_array($v, ['x', 'x-bold', 'chevron', 'arrow'], true) ? $v : 'x';
        },
    ]);
    $wp_customize->add_control('rv_oc_close_icon', [
        'label'   => __('Close button icon', 'sage'),
        'section' => 'rv_header_mobile',
        'type'    => 'select',
        'choices' => [
            'x'       => __('Cross (×)', 'sage'),
            'x-bold'  => __('Cross — bold', 'sage'),
            'chevron' => __('Chevron (›)', 'sage'),
            'arrow'   => __('Arrow (→)', 'sage'),
        ],
    ]);
    $wp_customize->add_setting('rv_oc_close_shape', [
        'default'           => 'square',
        'sanitize_callback' => function ($v) {
            return in_array($v, ['square', 'circle', 'plain'], true) ? $v : 'square';
        },
    ]);
    $wp_customize->add_control('rv_oc_close_shape', [
        'label'   => __('Close button shape', 'sage'),
        'section' => 'rv_header_mobile',
        'type'    => 'select',
        'choices' => [
            'square' => __('Rounded square (tinted)', 'sage'),
            'circle' => __('Circle (tinted)', 'sage'),
            'plain'  => __('Plain (icon only)', 'sage'),
        ],
    ]);
    $wp_customize->add_setting('rv_oc_close_size', ['default' => 42, 'sanitize_callback' => 'absint']);
    $wp_customize->add_control('rv_oc_close_size', [
        'label'       => __('Close button size (px)', 'sage'),
        'section'     => 'rv_header_mobile',
        'type'        => 'range',
        'input_attrs' => ['min' => 30, 'max' => 60, 'step' => 1],
    ]);
    $wp_customize->add_setting('rv_oc_close_color', ['default' => '', 'sanitize_callback' => rv_nav_color_sanitize()]);
    $wp_customize->add_control('rv_oc_close_color', [
        'label'   => __('Close icon color', 'sage'),
        'section' => 'rv_header_mobile',
        'type'    => 'select',
        'choices' => rv_nav_color_choices(),
    ]);
    $wp_customize->add_setting('rv_oc_close_position', [
        'default'           => 'right',
        'sanitize_callback' => function ($v) {
            return in_array($v, ['right', 'left'], true) ? $v : 'right';
        },
    ]);
    $wp_customize->add_control('rv_oc_close_position', [
        'label'   => __('Close button position', 'sage'),
        'section' => 'rv_header_mobile',
        'type'    => 'select',
        'choices' => ['right' => __('Right', 'sage'), 'left' => __('Left', 'sage')],
    ]);

    /* Primary navigation styling — colors use theme-palette dropdowns */

    // Text colors
    $rv_nav_text_colors = [
        'rv_nav_color'        => __('Nav item color', 'sage'),
        'rv_nav_hover_color'  => __('Nav item hover color', 'sage'),
        'rv_nav_active_color' => __('Nav item active color', 'sage'),
    ];
    foreach ($rv_nav_text_colors as $id => $label) {
        $wp_customize->add_setting($id, ['default' => '', 'sanitize_callback' => rv_nav_color_sanitize()]);
        $wp_customize->add_control($id, [
            'label'   => $label,
            'section' => 'rv_header',
            'type'    => 'select',
            'choices' => rv_nav_color_choices(),
        ]);
    }

    $wp_customize->add_setting('rv_nav_size', ['default' => 16, 'sanitize_callback' => 'absint']);
    $wp_customize->add_control('rv_nav_size', [
        'label'       => __('Nav text size (px)', 'sage'),
        'section'     => 'rv_header',
        'type'        => 'range',
        'input_attrs' => ['min' => 13, 'max' => 22, 'step' => 1],
    ]);

    $wp_customize->add_setting('rv_nav_weight', [
        'default'           => '600',
        'sanitize_callback' => function ($v) {
            return in_array($v, ['300', '400', '500', '600', '700'], true) ? $v : '600';
        },
    ]);
    $wp_customize->add_control('rv_nav_weight', [
        'label'   => __('Nav text weight', 'sage'),
        'section' => 'rv_header',
        'type'    => 'select',
        'choices' => [
            '300' => __('Light', 'sage'),
            '400' => __('Regular', 'sage'),
            '500' => __('Medium', 'sage'),
            '600' => __('Semibold', 'sage'),
            '700' => __('Bold', 'sage'),
        ],
    ]);

    // Spacing — item padding and the gap between items
    $wp_customize->add_setting('rv_nav_pad_y', ['default' => 6, 'sanitize_callback' => 'absint']);
    $wp_customize->add_control('rv_nav_pad_y', [
        'label'       => __('Nav item vertical padding (px)', 'sage'),
        'section'     => 'rv_header',
        'type'        => 'range',
        'input_attrs' => ['min' => 0, 'max' => 20, 'step' => 1],
    ]);
    $wp_customize->add_setting('rv_nav_pad_x', ['default' => 0, 'sanitize_callback' => 'absint']);
    $wp_customize->add_control('rv_nav_pad_x', [
        'label'       => __('Nav item horizontal padding (px)', 'sage'),
        'description' => __('Inner spacing left/right of each item. Adds room around a fill or border, and insets the underline.', 'sage'),
        'section'     => 'rv_header',
        'type'        => 'range',
        'input_attrs' => ['min' => 0, 'max' => 28, 'step' => 1],
    ]);
    $wp_customize->add_setting('rv_nav_gap', ['default' => 24, 'sanitize_callback' => 'absint']);
    $wp_customize->add_control('rv_nav_gap', [
        'label'       => __('Space between nav items (px)', 'sage'),
        'description' => __('Outer gap between menu items.', 'sage'),
        'section'     => 'rv_header',
        'type'        => 'range',
        'input_attrs' => ['min' => 4, 'max' => 48, 'step' => 1],
    ]);

    // Fill / background colors (theme palette; fills allow Transparent)
    $wp_customize->add_setting('rv_nav_fill_color', ['default' => '', 'sanitize_callback' => rv_nav_color_sanitize(true)]);
    $wp_customize->add_control('rv_nav_fill_color', [
        'label'       => __('Nav item fill color', 'sage'),
        'description' => __('Resting background of each nav item. Choose Transparent for none.', 'sage'),
        'section'     => 'rv_header',
        'type'        => 'select',
        'choices'     => rv_nav_color_choices(true),
    ]);
    $wp_customize->add_setting('rv_nav_hover_bg', ['default' => '', 'sanitize_callback' => rv_nav_color_sanitize(true)]);
    $wp_customize->add_control('rv_nav_hover_bg', [
        'label'       => __('Nav item hover background color', 'sage'),
        'description' => __('Solid background shown on hover and on the current page.', 'sage'),
        'section'     => 'rv_header',
        'type'        => 'select',
        'choices'     => rv_nav_color_choices(true),
    ]);
    $wp_customize->add_setting('rv_nav_hover_fill_color', ['default' => '', 'sanitize_callback' => rv_nav_color_sanitize()]);
    $wp_customize->add_control('rv_nav_hover_fill_color', [
        'label'       => __('Nav item background hover fill color', 'sage'),
        'description' => __('Color of the animated background fill (used when Hover animation style is “Background fill”).', 'sage'),
        'section'     => 'rv_header',
        'type'        => 'select',
        'choices'     => rv_nav_color_choices(),
    ]);

    // Border
    $wp_customize->add_setting('rv_nav_border_size', ['default' => 0, 'sanitize_callback' => 'absint']);
    $wp_customize->add_control('rv_nav_border_size', [
        'label'       => __('Nav item border size (px)', 'sage'),
        'description' => __('0 = no border.', 'sage'),
        'section'     => 'rv_header',
        'type'        => 'range',
        'input_attrs' => ['min' => 0, 'max' => 4, 'step' => 1],
    ]);
    $wp_customize->add_setting('rv_nav_border_color', ['default' => '', 'sanitize_callback' => rv_nav_color_sanitize()]);
    $wp_customize->add_control('rv_nav_border_color', [
        'label'   => __('Nav item border color', 'sage'),
        'section' => 'rv_header',
        'type'    => 'select',
        'choices' => rv_nav_color_choices(),
    ]);
    $wp_customize->add_setting('rv_nav_border_hover_color', ['default' => '', 'sanitize_callback' => rv_nav_color_sanitize()]);
    $wp_customize->add_control('rv_nav_border_hover_color', [
        'label'       => __('Nav item border hover color', 'sage'),
        'description' => __('Border color on hover and on the current page.', 'sage'),
        'section'     => 'rv_header',
        'type'        => 'select',
        'choices'     => rv_nav_color_choices(),
    ]);

    $wp_customize->add_setting('rv_nav_radius', ['default' => 8, 'sanitize_callback' => 'absint']);
    $wp_customize->add_control('rv_nav_radius', [
        'label'       => __('Nav item border radius (px)', 'sage'),
        'description' => __('Applies to the item fill, border, and background hover fill.', 'sage'),
        'section'     => 'rv_header',
        'type'        => 'range',
        'input_attrs' => ['min' => 0, 'max' => 24, 'step' => 1],
    ]);

    // Hover animation
    $wp_customize->add_setting('rv_nav_hover_anim', [
        'default'           => 'line',
        'sanitize_callback' => function ($v) {
            return in_array($v, ['none', 'color', 'line', 'fill', 'highlight', 'lift'], true) ? $v : 'line';
        },
    ]);
    $wp_customize->add_control('rv_nav_hover_anim', [
        'label'       => __('Nav item hover animation style', 'sage'),
        'description' => __('Overall hover treatment. “Underline / line” uses the two line options below; “Background fill” uses the hover fill color.', 'sage'),
        'section'     => 'rv_header',
        'type'        => 'select',
        'choices'     => [
            'none'      => __('None', 'sage'),
            'color'     => __('Color change only', 'sage'),
            'line'      => __('Underline / line', 'sage'),
            'fill'      => __('Background fill', 'sage'),
            'highlight' => __('Highlight (marker sweep)', 'sage'),
            'lift'      => __('Lift', 'sage'),
        ],
    ]);
    $wp_customize->add_setting('rv_nav_hover_line_anim', [
        'default'           => 'slide',
        'sanitize_callback' => function ($v) {
            return in_array($v, ['slide', 'center', 'fade', 'draw'], true) ? $v : 'slide';
        },
    ]);
    $wp_customize->add_control('rv_nav_hover_line_anim', [
        'label'       => __('Nav item hover line animation', 'sage'),
        'description' => __('How the line appears (used when the style is “Underline / line”).', 'sage'),
        'section'     => 'rv_header',
        'type'        => 'select',
        'choices'     => [
            'slide'  => __('Slide in (from left)', 'sage'),
            'center' => __('Grow from center', 'sage'),
            'fade'   => __('Fade in', 'sage'),
            'draw'   => __('Draw (left to right)', 'sage'),
        ],
    ]);
    $wp_customize->add_setting('rv_nav_hover_line_pos', [
        'default'           => 'bottom',
        'sanitize_callback' => function ($v) {
            return in_array($v, ['bottom', 'top', 'baseline'], true) ? $v : 'bottom';
        },
    ]);
    $wp_customize->add_control('rv_nav_hover_line_pos', [
        'label'       => __('Nav item hover line position', 'sage'),
        'description' => __('Where the line sits (used when the style is “Underline / line”).', 'sage'),
        'section'     => 'rv_header',
        'type'        => 'select',
        'choices'     => [
            'bottom'   => __('Bottom', 'sage'),
            'top'      => __('Top', 'sage'),
            'baseline' => __('Under the text', 'sage'),
        ],
    ]);
    $wp_customize->add_setting('rv_nav_line_size', ['default' => 2, 'sanitize_callback' => 'absint']);
    $wp_customize->add_control('rv_nav_line_size', [
        'label'       => __('Nav item line thickness (px)', 'sage'),
        'description' => __('Thickness of the underline / line.', 'sage'),
        'section'     => 'rv_header',
        'type'        => 'range',
        'input_attrs' => ['min' => 1, 'max' => 6, 'step' => 1],
    ]);

    /* Light / dark mode toggle */
    $wp_customize->add_setting('rv_dark_toggle_enable', ['default' => true, 'sanitize_callback' => 'rest_sanitize_boolean']);
    $wp_customize->add_control('rv_dark_toggle_enable', [
        'label'       => __('Light / dark mode toggle', 'sage'),
        'description' => __('Show the header toggle and let visitors switch modes. Turn off to force the light theme site-wide.', 'sage'),
        'section'     => 'rv_header',
        'type'        => 'checkbox',
    ]);
    $wp_customize->add_setting('rv_dark_toggle_icon', [
        'default'           => 'sun-moon',
        'sanitize_callback' => function ($v) {
            return in_array($v, ['sun-moon', 'moon-sun', 'contrast', 'bulb'], true) ? $v : 'sun-moon';
        },
    ]);
    $wp_customize->add_control('rv_dark_toggle_icon', [
        'label'   => __('Toggle icon', 'sage'),
        'section' => 'rv_header',
        'type'    => 'select',
        'choices' => [
            'sun-moon' => __('Sun & moon', 'sage'),
            'moon-sun' => __('Moon & sun', 'sage'),
            'contrast' => __('Contrast (half circle)', 'sage'),
            'bulb'     => __('Lightbulb', 'sage'),
        ],
    ]);
    $wp_customize->add_setting('rv_toggle_style', [
        'default'           => 'button',
        'sanitize_callback' => function ($v) {
            return in_array($v, ['button', 'pill', 'soft', 'plain'], true) ? $v : 'button';
        },
    ]);
    $wp_customize->add_control('rv_toggle_style', [
        'label'       => __('Toggle button style', 'sage'),
        'description' => __('Bordered = outlined box, Pill = fully rounded, Soft = filled tint (no border), Plain = icon only.', 'sage'),
        'section'     => 'rv_header',
        'type'        => 'select',
        'choices'     => [
            'button' => __('Bordered button', 'sage'),
            'pill'   => __('Pill', 'sage'),
            'soft'   => __('Soft (filled)', 'sage'),
            'plain'  => __('Plain (icon only)', 'sage'),
        ],
    ]);

    /* Toggle colors + advanced. Blank color = keep the theme default. */
    $toggle_colors = [
        'rv_toggle_icon_color'  => __('Toggle icon color', 'sage'),
        'rv_toggle_bg'          => __('Toggle background', 'sage'),
        'rv_toggle_border_color'=> __('Toggle border color', 'sage'),
        'rv_toggle_hover_icon'  => __('Icon color on hover', 'sage'),
        'rv_toggle_hover_bg'    => __('Background on hover', 'sage'),
    ];
    foreach ($toggle_colors as $id => $label) {
        $wp_customize->add_setting($id, ['default' => '', 'sanitize_callback' => 'sanitize_hex_color']);
        $wp_customize->add_control(new \WP_Customize_Color_Control($wp_customize, $id, [
            'label'   => $label,
            'section' => 'rv_header',
        ]));
    }

    $wp_customize->add_setting('rv_toggle_size', ['default' => 44, 'sanitize_callback' => 'absint']);
    $wp_customize->add_control('rv_toggle_size', [
        'label'       => __('Toggle button size (px)', 'sage'),
        'section'     => 'rv_header',
        'type'        => 'range',
        'input_attrs' => ['min' => 32, 'max' => 56, 'step' => 1],
    ]);

    $wp_customize->add_setting('rv_toggle_radius', ['default' => 14, 'sanitize_callback' => 'absint']);
    $wp_customize->add_control('rv_toggle_radius', [
        'label'       => __('Toggle corner radius (px)', 'sage'),
        'description' => __('Applies to the Bordered, Soft, and Plain styles. Pill is always fully rounded.', 'sage'),
        'section'     => 'rv_header',
        'type'        => 'range',
        'input_attrs' => ['min' => 0, 'max' => 28, 'step' => 1],
    ]);

    $wp_customize->add_setting('rv_toggle_icon_size', ['default' => 20, 'sanitize_callback' => 'absint']);
    $wp_customize->add_control('rv_toggle_icon_size', [
        'label'       => __('Toggle icon size (px)', 'sage'),
        'section'     => 'rv_header',
        'type'        => 'range',
        'input_attrs' => ['min' => 14, 'max' => 30, 'step' => 1],
    ]);

    $wp_customize->add_setting('rv_toggle_border_width', ['default' => 2, 'sanitize_callback' => 'absint']);
    $wp_customize->add_control('rv_toggle_border_width', [
        'label'       => __('Toggle border width (px)', 'sage'),
        'description' => __('For the Bordered and Pill styles. Set 0 for no border.', 'sage'),
        'section'     => 'rv_header',
        'type'        => 'range',
        'input_attrs' => ['min' => 0, 'max' => 4, 'step' => 1],
    ]);

    $wp_customize->add_setting('rv_default_mode', [
        'default'           => 'auto',
        'sanitize_callback' => function ($v) {
            return in_array($v, ['auto', 'light', 'dark'], true) ? $v : 'auto';
        },
    ]);
    $wp_customize->add_control('rv_default_mode', [
        'label'       => __('Default color mode', 'sage'),
        'description' => __('Which mode the site loads in for a first-time visitor. “Follow system” uses their device setting; visitors who use the toggle keep their own choice.', 'sage'),
        'section'     => 'rv_header',
        'type'        => 'select',
        'choices'     => [
            'auto'  => __('Follow system', 'sage'),
            'light' => __('Light', 'sage'),
            'dark'  => __('Dark', 'sage'),
        ],
    ]);

    /*
     * Re-parent the header controls into their accordion sub-sections. Done by
     * ID here (rather than on each add_control call) so the control definitions
     * above stay untouched; each list keeps its registration order within the
     * section. Anything left on 'rv_header' stays in the main Header accordion
     * (button label/link, sticky, transparent header).
     */
    $rv_header_groups = [
        'rv_header_topbar' => [
            'rv_topbar_text', 'rv_topbar_social', 'rv_topbar_color',
            'rv_topbar_font_size', 'rv_topbar_font_weight', 'rv_topbar_hide_on_scroll',
        ],
        'rv_header_nav' => [
            'rv_nav_color', 'rv_nav_hover_color', 'rv_nav_active_color',
            'rv_nav_size', 'rv_nav_weight',
            'rv_nav_pad_y', 'rv_nav_pad_x', 'rv_nav_gap',
            'rv_nav_fill_color', 'rv_nav_hover_bg', 'rv_nav_hover_fill_color',
            'rv_nav_border_size', 'rv_nav_border_color', 'rv_nav_border_hover_color',
            'rv_nav_radius',
            'rv_nav_hover_anim', 'rv_nav_hover_line_anim', 'rv_nav_hover_line_pos', 'rv_nav_line_size',
        ],
        'rv_header_toggle' => [
            'rv_dark_toggle_enable', 'rv_dark_toggle_icon', 'rv_toggle_style',
            'rv_toggle_icon_color', 'rv_toggle_bg', 'rv_toggle_border_color',
            'rv_toggle_hover_icon', 'rv_toggle_hover_bg', 'rv_toggle_size',
            'rv_toggle_radius', 'rv_toggle_icon_size', 'rv_toggle_border_width',
            'rv_default_mode',
        ],
    ];
    foreach ($rv_header_groups as $rv_section => $rv_control_ids) {
        foreach ($rv_control_ids as $rv_cid) {
            $rv_ctrl = $wp_customize->get_control($rv_cid);
            if ($rv_ctrl) {
                $rv_ctrl->section = $rv_section;
            }
        }
    }

    /* Site Buttons — primary & secondary button designs */
    $wp_customize->add_section('rv_buttons', [
        'title'       => __('Site Buttons', 'sage'),
        'description' => __('Colors and shape for the primary and secondary buttons used across the site — header CTA, hero, cards, and forms all follow these.', 'sage'),
        'panel'       => 'rv_theme_options',
    ]);

    // Shared palette for both button roles (matches the site color tokens).
    $rv_btn_colors = [
        'pine'  => __('Green (Ridge Pine)', 'sage'),
        'clay'  => __('Orange (Gettysburg Clay)', 'sage'),
        'wheat' => __('Gold (Wheat)', 'sage'),
        'sage'  => __('Sage', 'sage'),
        'ink'   => __('Dark (Ink)', 'sage'),
        'cream' => __('Light (Cream)', 'sage'),
    ];
    $rv_btn_color_keys = array_keys($rv_btn_colors);
    $rv_btn_color_sanitize = function ($v) use ($rv_btn_color_keys) {
        return in_array($v, $rv_btn_color_keys, true) ? $v : 'pine';
    };

    $wp_customize->add_setting('rv_btn_primary_color', ['default' => 'pine', 'sanitize_callback' => $rv_btn_color_sanitize]);
    $wp_customize->add_control('rv_btn_primary_color', [
        'label'       => __('Primary button color', 'sage'),
        'description' => __('Fill color for the main call-to-action buttons.', 'sage'),
        'section'     => 'rv_buttons',
        'type'        => 'select',
        'choices'     => $rv_btn_colors,
    ]);

    $wp_customize->add_setting('rv_btn_secondary_style', [
        'default'           => 'outline',
        'sanitize_callback' => function ($v) {
            return in_array($v, ['outline', 'solid', 'soft'], true) ? $v : 'outline';
        },
    ]);
    $wp_customize->add_control('rv_btn_secondary_style', [
        'label'   => __('Secondary button style', 'sage'),
        'section' => 'rv_buttons',
        'type'    => 'select',
        'choices' => [
            'outline' => __('Outline', 'sage'),
            'solid'   => __('Solid fill', 'sage'),
            'soft'    => __('Soft tint', 'sage'),
        ],
    ]);
    $wp_customize->add_setting('rv_btn_secondary_color', ['default' => 'pine', 'sanitize_callback' => $rv_btn_color_sanitize]);
    $wp_customize->add_control('rv_btn_secondary_color', [
        'label'       => __('Secondary button color', 'sage'),
        'description' => __('Accent for outline / soft styles, or the fill for solid.', 'sage'),
        'section'     => 'rv_buttons',
        'type'        => 'select',
        'choices'     => $rv_btn_colors,
    ]);

    $wp_customize->add_setting('rv_btn_shape', [
        'default'           => 'pill',
        'sanitize_callback' => function ($v) {
            return in_array($v, ['pill', 'rounded', 'soft', 'square'], true) ? $v : 'pill';
        },
    ]);
    $wp_customize->add_control('rv_btn_shape', [
        'label'   => __('Corner shape', 'sage'),
        'section' => 'rv_buttons',
        'type'    => 'select',
        'choices' => [
            'pill'    => __('Pill (fully rounded)', 'sage'),
            'rounded' => __('Rounded', 'sage'),
            'soft'    => __('Soft corners', 'sage'),
            'square'  => __('Square', 'sage'),
        ],
    ]);

    $wp_customize->add_setting('rv_btn_border', ['default' => 2, 'sanitize_callback' => 'absint']);
    $wp_customize->add_control('rv_btn_border', [
        'label'       => __('Border thickness (px)', 'sage'),
        'description' => __('Applies to the outline style and the button edge.', 'sage'),
        'section'     => 'rv_buttons',
        'type'        => 'range',
        'input_attrs' => ['min' => 0, 'max' => 4, 'step' => 1],
    ]);

    $wp_customize->add_setting('rv_btn_font', ['default' => 16, 'sanitize_callback' => 'absint']);
    $wp_customize->add_control('rv_btn_font', [
        'label'       => __('Text size (px)', 'sage'),
        'section'     => 'rv_buttons',
        'type'        => 'range',
        'input_attrs' => ['min' => 13, 'max' => 22, 'step' => 1],
    ]);

    $wp_customize->add_setting('rv_btn_weight', [
        'default'           => '700',
        'sanitize_callback' => function ($v) {
            return in_array($v, ['600', '700', '800'], true) ? $v : '700';
        },
    ]);
    $wp_customize->add_control('rv_btn_weight', [
        'label'   => __('Text weight', 'sage'),
        'section' => 'rv_buttons',
        'type'    => 'select',
        'choices' => [
            '600' => __('Medium', 'sage'),
            '700' => __('Bold', 'sage'),
            '800' => __('Extra bold', 'sage'),
        ],
    ]);

    $wp_customize->add_setting('rv_btn_pad_y', ['default' => 13, 'sanitize_callback' => 'absint']);
    $wp_customize->add_control('rv_btn_pad_y', [
        'label'       => __('Vertical padding (px)', 'sage'),
        'section'     => 'rv_buttons',
        'type'        => 'range',
        'input_attrs' => ['min' => 8, 'max' => 20, 'step' => 1],
    ]);

    $wp_customize->add_setting('rv_btn_pad_x', ['default' => 22, 'sanitize_callback' => 'absint']);
    $wp_customize->add_control('rv_btn_pad_x', [
        'label'       => __('Horizontal padding (px)', 'sage'),
        'section'     => 'rv_buttons',
        'type'        => 'range',
        'input_attrs' => ['min' => 14, 'max' => 44, 'step' => 1],
    ]);

    $wp_customize->add_setting('rv_btn_uppercase', ['default' => false, 'sanitize_callback' => 'rest_sanitize_boolean']);
    $wp_customize->add_control('rv_btn_uppercase', [
        'label'   => __('Uppercase button text', 'sage'),
        'section' => 'rv_buttons',
        'type'    => 'checkbox',
    ]);

    /* Footer */
    $wp_customize->add_section('rv_footer', ['title' => __('Footer', 'sage'), 'panel' => 'rv_theme_options']);
    $wp_customize->add_setting('rv_footer_tagline', [
        'default'           => __('Websites that help South Central PA businesses get found and get work.', 'sage'),
        'sanitize_callback' => 'wp_kses_post',
    ]);
    $wp_customize->add_control('rv_footer_tagline', ['label' => __('Footer tagline', 'sage'), 'section' => 'rv_footer', 'type' => 'textarea']);

    /* Footer call-to-action band */
    $wp_customize->add_setting('rv_footer_cta_enable', ['default' => true, 'sanitize_callback' => 'rest_sanitize_boolean']);
    $wp_customize->add_control('rv_footer_cta_enable', [
        'label'   => __('Show the footer call-to-action', 'sage'),
        'section' => 'rv_footer',
        'type'    => 'checkbox',
    ]);
    $wp_customize->add_setting('rv_footer_cta_style', [
        'default'           => 'pine',
        'sanitize_callback' => function ($v) {
            return in_array($v, ['pine', 'clay', 'split', 'outline'], true) ? $v : 'pine';
        },
    ]);
    $wp_customize->add_control('rv_footer_cta_style', [
        'label'       => __('Design', 'sage'),
        'description' => __('Colour treatment for the footer call-to-action.', 'sage'),
        'section'     => 'rv_footer',
        'type'        => 'select',
        'choices'     => [
            'pine'    => __('Green (Ridge Pine)', 'sage'),
            'clay'    => __('Orange (Gettysburg Clay)', 'sage'),
            'split'   => __('Green → Orange gradient', 'sage'),
            'outline' => __('Light with green & orange accents', 'sage'),
        ],
    ]);
    $fcta_text = [
        ['rv_footer_cta_eyebrow', __('CTA eyebrow', 'sage'), __('Let’s build something', 'sage'), 'text'],
        ['rv_footer_cta_title', __('CTA heading', 'sage'), __('Ready for a website that works as hard as you do?', 'sage'), 'text'],
        ['rv_footer_cta_sub', __('CTA subtext', 'sage'), __('Tell me about your business and I’ll show you what’s possible — no pressure, no jargon.', 'sage'), 'textarea'],
        ['rv_footer_cta_btn', __('CTA button label', 'sage'), __('Start your project', 'sage'), 'text'],
        ['rv_footer_cta_url', __('CTA button link', 'sage'), '/contact/', 'text'],
    ];
    foreach ($fcta_text as [$id, $label, $default, $type]) {
        $wp_customize->add_setting($id, [
            'default'           => $default,
            'sanitize_callback' => ($type === 'textarea') ? 'wp_kses_post' : 'sanitize_text_field',
        ]);
        $wp_customize->add_control($id, ['label' => $label, 'section' => 'rv_footer', 'type' => $type]);
    }

    // Footer layout & content options
    $wp_customize->add_setting('rv_footer_hide_browse', ['default' => false, 'sanitize_callback' => 'rest_sanitize_boolean']);
    $wp_customize->add_control('rv_footer_hide_browse', [
        'label'       => __('Hide Archives & Categories in footer', 'sage'),
        'description' => __('Removes the auto-generated Archives and Categories columns from the footer.', 'sage'),
        'section'     => 'rv_footer',
        'type'        => 'checkbox',
    ]);
    $wp_customize->add_setting('rv_footer_logo_invert', ['default' => true, 'sanitize_callback' => 'rest_sanitize_boolean']);
    $wp_customize->add_control('rv_footer_logo_invert', [
        'label'       => __('Invert footer logo to white', 'sage'),
        'description' => __('The footer sits on a dark band. Leave on for dark logos; turn off if your logo is already light or full-color.', 'sage'),
        'section'     => 'rv_footer',
        'type'        => 'checkbox',
    ]);
    $wp_customize->add_setting('rv_footer_bottom_text', [
        'default'           => __('Built local in Adams County, PA.', 'sage'),
        'sanitize_callback' => 'wp_kses_post',
    ]);
    $wp_customize->add_control('rv_footer_bottom_text', [
        'label'       => __('Footer bottom line', 'sage'),
        'description' => __('The small line shown next to the copyright at the very bottom.', 'sage'),
        'section'     => 'rv_footer',
        'type'        => 'text',
    ]);

    /* Contact */
    $wp_customize->add_section('rv_contact', ['title' => __('Contact', 'sage'), 'panel' => 'rv_theme_options']);
    $wp_customize->add_setting('rv_contact_email', ['default' => get_option('admin_email'), 'sanitize_callback' => 'sanitize_email']);
    $wp_customize->add_control('rv_contact_email', ['label' => __('Where contact-form messages are sent', 'sage'), 'section' => 'rv_contact', 'type' => 'email']);

    /* Blog & Journal — editable text + show/hide for the post enhancements */
    $wp_customize->add_section('rv_blog', ['title' => __('Blog & Journal', 'sage'), 'panel' => 'rv_theme_options']);

    $blog_text = [
        ['rv_inline_cta_heading', __('In-article CTA — heading', 'sage'), __('Not sure where your site stands?', 'sage'), 'text'],
        ['rv_inline_cta_text', __('In-article CTA — text', 'sage'), __('I’ll record a free 5-minute video walkthrough of your website — no pitch, just the first things I’d fix.', 'sage'), 'textarea'],
        ['rv_inline_cta_btn', __('In-article CTA — button label', 'sage'), __('Get my free audit', 'sage'), 'text'],
        ['rv_inline_cta_url', __('In-article CTA — button link', 'sage'), '/contact/', 'text'],
        ['rv_float_cta_title', __('Floating CTA — title', 'sage'), __('Want a second set of eyes on your site?', 'sage'), 'text'],
        ['rv_float_cta_btn', __('Floating CTA — button label', 'sage'), __('Get a free 5-min audit', 'sage'), 'text'],
        ['rv_audit_btn_text', __('End-of-post button label', 'sage'), __('Get a free 5-minute audit', 'sage'), 'text'],
        ['rv_post_author_bio', __('Post author bio', 'sage'), __('Founder of Ridges & Valleys Studio. 15 years as a WordPress developer, now building fast, accessible websites for Gettysburg and South Central PA.', 'sage'), 'textarea'],
    ];
    foreach ($blog_text as [$id, $label, $default, $type]) {
        $wp_customize->add_setting($id, [
            'default'           => $default,
            'sanitize_callback' => ($type === 'textarea') ? 'wp_kses_post' : 'sanitize_text_field',
        ]);
        $wp_customize->add_control($id, ['label' => $label, 'section' => 'rv_blog', 'type' => $type]);
    }

    $blog_toggles = [
        ['rv_share_enable', __('Show share buttons', 'sage'), true],
        ['rv_helpful_enable', __('Show “Was this helpful?”', 'sage'), true],
        ['rv_inline_cta_enable', __('Show the in-article CTA', 'sage'), true],
        ['rv_float_cta_enable', __('Show the floating CTA', 'sage'), true],
    ];
    foreach ($blog_toggles as [$id, $label, $default]) {
        $wp_customize->add_setting($id, ['default' => $default, 'sanitize_callback' => 'rest_sanitize_boolean']);
        $wp_customize->add_control($id, ['label' => $label, 'section' => 'rv_blog', 'type' => 'checkbox']);
    }

    /* Social */
    $wp_customize->add_section('rv_social', ['title' => __('Social Links', 'sage'), 'panel' => 'rv_theme_options']);
    $wp_customize->add_setting('rv_social_style', [
        'default'           => 'plain',
        'sanitize_callback' => function ($v) {
            return in_array($v, ['plain', 'circle', 'square', 'soft'], true) ? $v : 'plain';
        },
    ]);
    $wp_customize->add_control('rv_social_style', [
        'label'       => __('Icon design', 'sage'),
        'description' => __('How the icons look everywhere they appear — top bar, footer, and menu stay in sync.', 'sage'),
        'section'     => 'rv_social',
        'type'        => 'select',
        'choices'     => [
            'plain'  => __('Plain', 'sage'),
            'circle' => __('Circle outline', 'sage'),
            'square' => __('Rounded square', 'sage'),
            'soft'   => __('Soft chip', 'sage'),
        ],
    ]);
    $wp_customize->add_setting('rv_social_size', ['default' => 34, 'sanitize_callback' => 'absint']);
    $wp_customize->add_control('rv_social_size', [
        'label'       => __('Icon size (px)', 'sage'),
        'description' => __('Applies everywhere the icons appear.', 'sage'),
        'section'     => 'rv_social',
        'type'        => 'range',
        'input_attrs' => ['min' => 26, 'max' => 52, 'step' => 2],
    ]);

    // Drag-and-drop icon order — a comma-separated list of platform keys.
    $wp_customize->add_setting('rv_social_order', [
        'default'           => '',
        'sanitize_callback' => function ($v) {
            $valid = array_keys(social_platforms());
            $keys  = array_filter(array_map('trim', explode(',', (string) $v)));
            return implode(',', array_values(array_unique(array_intersect($keys, $valid))));
        },
    ]);
    $wp_customize->add_control(new RV_Social_Order_Control($wp_customize, 'rv_social_order', [
        'label'       => __('Icon order', 'sage'),
        'description' => __('Drag the platforms into the order you want. Only the ones with a URL will show.', 'sage'),
        'section'     => 'rv_social',
    ]));

    foreach (social_platforms() as $key => $label) {
        $id = 'rv_social_' . $key;
        $wp_customize->add_setting($id, ['default' => '', 'sanitize_callback' => ($key === 'email') ? 'sanitize_email' : 'esc_url_raw']);
        $wp_customize->add_control($id, [
            /* translators: %s: platform name. */
            'label'   => sprintf(__('%s URL', 'sage'), $label),
            'section' => 'rv_social',
            'type'    => ($key === 'email') ? 'email' : 'url',
        ]);
    }
});

/**
 * Emit design-token overrides after the stylesheet.
 */
add_action('wp_head', function () {
    $pine  = get_theme_mod('rv_color_brand', '#2E5245');
    $clay  = get_theme_mod('rv_color_accent', '#B0553A');
    $wheat = get_theme_mod('rv_color_wheat', '#E0A73C');
    $sage  = get_theme_mod('rv_color_sage', '#97A88E');
    $paper = get_theme_mod('rv_color_paper', '#F7F1E6');
    $ink   = get_theme_mod('rv_color_ink', '#23201B');
    $width = (int) get_theme_mod('rv_container_width', 1180);

    // The `:root:root` / `:not([data-theme=dark])` selectors raise specificity so
    // these values win over app.css's own :root defaults (which load afterwards).
    // Accent hues are shared across light/dark; paper + ink are light-mode only,
    // so dark mode's own surface/text tokens still apply.
    printf(
        '<style id="rv-customizer">:root:root{--color-pine:%1$s;--color-clay:%2$s;--color-wheat:%3$s;--color-sage:%4$s;--container:%7$dpx;}:root:not([data-theme="dark"]){--color-paper:%5$s;--color-ink:%6$s;}</style>' . "\n",
        esc_attr($pine),
        esc_attr($clay),
        esc_attr($wheat),
        esc_attr($sage),
        esc_attr($paper),
        esc_attr($ink),
        absint($width)
    );
}, 20);

/**
 * Emit hero positioning CSS (flexbox) from the Customizer controls. Values are
 * mapped through a whitelist, so the theme_mod can never inject arbitrary CSS.
 * Add more sizing/alignment controls the same way: register a setting above,
 * then map + print its CSS here.
 */
add_action('wp_head', function () {
    $amap = [
        // key => [align-items, text-align, margin-left, margin-right]
        'left'   => ['flex-start', 'left',   '0',    '0'],
        'center' => ['center',     'center', 'auto', 'auto'],
        'right'  => ['flex-end',   'right',  'auto', '0'],
    ];
    $vmap = ['top' => 'flex-start', 'center' => 'center', 'bottom' => 'flex-end'];

    $a = $amap[get_theme_mod('rv_hero_align', 'left')] ?? $amap['left'];
    $v = $vmap[get_theme_mod('rv_hero_valign', 'center')] ?? 'center';

    printf(
        '<style id="rv-hero-align">.rv-hero .rv-hero-inner{display:flex;flex-direction:column;align-items:%1$s;justify-content:%2$s;text-align:%3$s}.rv-hero .rv-hero-title,.rv-hero .rv-hero-sub{margin-left:%4$s;margin-right:%5$s}.rv-hero .rv-hero-actions{justify-content:%1$s}</style>' . "\n",
        $a[0],
        $v,
        $a[1],
        $a[2],
        $a[3]
    );
}, 21);

/**
 * Social icon size (Customizer > Social Links > Icon size). The `.rv-social`
 * descendant selectors out-specify app.css, so the size wins in every context,
 * and the glyph scales with the button.
 */
add_action('wp_head', function () {
    $size = (int) get_theme_mod('rv_social_size', 34);
    if ($size < 20 || $size > 80) {
        $size = 34;
    }
    $glyph = (int) round($size * 0.56);

    printf(
        '<style id="rv-social-size">.rv-social .rv-social-link{width:%1$dpx;height:%1$dpx}.rv-social .rv-social-link svg,.rv-social .rv-social-link .rv-icon{width:%2$dpx;height:%2$dpx}</style>' . "\n",
        $size,
        $glyph
    );
}, 22);

/**
 * Top bar color (Customizer > Header > Top bar color). Values are whitelist-
 * mapped [background, text, social-icon] and printed with `.rv-banner`-scoped
 * selectors so they out-specify app.css's defaults.
 */
add_action('wp_head', function () {
    $map = [
        // key => [background, text color, --social-color]
        'pine'  => ['var(--color-pine-deep)', '#e9efe8', '#cfe0cf'],
        'clay'  => ['var(--color-clay)', '#ffffff', '#ffe4d8'],
        'ink'   => ['var(--color-ink)', '#f3efe6', '#c9c4ba'],
        'wheat' => ['var(--color-wheat)', '#23201b', '#5a4b23'],
        'cream' => ['var(--color-paper)', 'var(--color-ink)', 'var(--color-muted)'],
    ];
    $c = $map[get_theme_mod('rv_topbar_color', 'pine')] ?? $map['pine'];

    printf(
        '<style id="rv-topbar-color">.rv-banner .rv-topbar{background:%1$s;color:%2$s}.rv-banner .rv-topbar-note,.rv-banner .rv-topbar a{color:%2$s}.rv-banner .rv-topbar-social{--social-color:%3$s}</style>' . "\n",
        $c[0],
        $c[1],
        $c[2]
    );
}, 23);

/**
 * Top bar typography (Customizer > Header > Top bar text size / weight). The
 * `.rv-banner`-scoped selector out-specifies app.css's `.rv-topbar-note` default.
 */
add_action('wp_head', function () {
    $size = min(18, max(10, (int) get_theme_mod('rv_topbar_font_size', 12)));
    $weight = get_theme_mod('rv_topbar_font_weight', '400');
    if (! in_array($weight, ['400', '500', '600', '700'], true)) {
        $weight = '400';
    }

    printf(
        '<style id="rv-topbar-type">.rv-banner .rv-topbar-note,.rv-banner .rv-topbar-note a{font-size:%1$dpx;font-weight:%2$s}</style>' . "\n",
        $size,
        $weight
    );
}, 25);

/**
 * Site button designs (Customizer > Site Buttons). All values are whitelist-
 * mapped, so a theme_mod can never inject arbitrary CSS. The `body`-prefixed
 * selectors out-specify app.css's `.rv-btn` rules (which load earlier), so the
 * chosen geometry + colors win site-wide. The hero's own ghost-button rules are
 * re-asserted here at higher specificity so buttons stay legible over the hero
 * image regardless of the secondary style chosen.
 */
add_action('wp_head', function () {
    // key => [fill / accent, text-on-fill, hover fill]
    $colors = [
        'pine'  => ['var(--color-pine)',  '#ffffff',           'var(--color-pine-deep)'],
        'clay'  => ['var(--color-clay)',  '#ffffff',           '#8f4430'],
        'wheat' => ['var(--color-wheat)', '#23201b',           '#c9902f'],
        'sage'  => ['var(--color-sage)',  '#23201b',           '#7f9175'],
        'ink'   => ['var(--color-ink)',   '#f3efe6',           '#3a352d'],
        'cream' => ['var(--color-paper)', 'var(--color-ink)',  'color-mix(in srgb, var(--color-paper) 88%, #000)'],
    ];
    $shapes = ['pill' => '999px', 'rounded' => '12px', 'soft' => '6px', 'square' => '0'];

    $pc     = $colors[get_theme_mod('rv_btn_primary_color', 'pine')] ?? $colors['pine'];
    $sc     = $colors[get_theme_mod('rv_btn_secondary_color', 'pine')] ?? $colors['pine'];
    $sStyle = get_theme_mod('rv_btn_secondary_style', 'outline');
    if (! in_array($sStyle, ['outline', 'solid', 'soft'], true)) {
        $sStyle = 'outline';
    }

    $shape  = $shapes[get_theme_mod('rv_btn_shape', 'pill')] ?? '999px';
    $border = min(4, max(0, (int) get_theme_mod('rv_btn_border', 2)));
    $font   = min(22, max(13, (int) get_theme_mod('rv_btn_font', 16)));
    $weight = get_theme_mod('rv_btn_weight', '700');
    if (! in_array($weight, ['600', '700', '800'], true)) {
        $weight = '700';
    }
    $py    = min(20, max(8, (int) get_theme_mod('rv_btn_pad_y', 13)));
    $px    = min(44, max(14, (int) get_theme_mod('rv_btn_pad_x', 22)));
    $upper = (bool) get_theme_mod('rv_btn_uppercase', false);
    $tt    = $upper ? 'uppercase' : 'none';
    $ls    = $upper ? '.06em' : 'normal';

    // Shared geometry for every button.
    $css = sprintf(
        'body .rv-btn{font-size:%1$dpx;border-width:%2$dpx;border-radius:%3$s;padding:%4$dpx %5$dpx;font-weight:%6$s;text-transform:%7$s;letter-spacing:%8$s}',
        $font,
        $border,
        $shape,
        $py,
        $px,
        $weight,
        $tt,
        $ls
    );

    // Primary button.
    $css .= sprintf(
        'body .rv-btn-primary{background:%1$s;color:%2$s;border-color:transparent;box-shadow:0 6px 16px color-mix(in srgb,%1$s 30%%,transparent)}body .rv-btn-primary:hover{background:%3$s;color:%2$s}',
        $pc[0],
        $pc[1],
        $pc[2]
    );

    // Secondary button (.rv-btn-ghost).
    if ($sStyle === 'solid') {
        $css .= sprintf(
            'body .rv-btn-ghost{background:%1$s;color:%2$s;border-color:transparent}body .rv-btn-ghost:hover{background:%3$s;color:%2$s}',
            $sc[0],
            $sc[1],
            $sc[2]
        );
    } elseif ($sStyle === 'soft') {
        $css .= sprintf(
            'body .rv-btn-ghost{background:color-mix(in srgb,%1$s 14%%,transparent);color:%1$s;border-color:transparent}body .rv-btn-ghost:hover{background:color-mix(in srgb,%1$s 22%%,transparent);color:%1$s}',
            $sc[0]
        );
    } else { // outline
        $css .= sprintf(
            'body .rv-btn-ghost{background:transparent;color:%1$s;border-color:%1$s}body .rv-btn-ghost:hover{background:color-mix(in srgb,%1$s 10%%,transparent);color:%1$s;border-color:%1$s}',
            $sc[0]
        );
    }

    // Keep hero ghost buttons white-on-image regardless of the secondary style.
    $css .= 'body .rv-hero-actions .rv-btn-ghost{background:transparent;color:#fff;border-color:rgba(255,255,255,.35)}'
        . 'body .rv-hero-actions .rv-btn-ghost:hover{background:rgba(255,255,255,.08);color:#fff;border-color:#fff}';

    printf('<style id="rv-buttons">%s</style>' . "\n", $css); // phpcs:ignore
}, 24);

/**
 * Primary navigation styling (Customizer > Header · Navigation). Every color is
 * chosen from the theme palette (a dropdown token → CSS custom property), never
 * a raw picker, so a theme_mod can never inject arbitrary CSS. Selectors double
 * the `.rv-nav-list` class so they out-specify app.css's nav rules in BOTH light
 * and dark mode. Hover treatment is applied to `:hover` and `.current-menu-item`
 * so the active page mirrors it; the "active color" overrides text on that item.
 */
add_action('wp_head', function () {
    $item_color   = rv_nav_color_value(get_theme_mod('rv_nav_color', ''));
    $hover_color  = rv_nav_color_value(get_theme_mod('rv_nav_hover_color', ''));
    $active_color = rv_nav_color_value(get_theme_mod('rv_nav_active_color', ''));
    $fill_color   = rv_nav_color_value(get_theme_mod('rv_nav_fill_color', ''));
    $hover_bg     = rv_nav_color_value(get_theme_mod('rv_nav_hover_bg', ''));
    $hover_fill   = rv_nav_color_value(get_theme_mod('rv_nav_hover_fill_color', ''));
    $border_color = rv_nav_color_value(get_theme_mod('rv_nav_border_color', ''));
    $border_hover = rv_nav_color_value(get_theme_mod('rv_nav_border_hover_color', ''));

    $size   = min(22, max(13, (int) get_theme_mod('rv_nav_size', 16)));
    $radius = min(24, max(0, (int) get_theme_mod('rv_nav_radius', 8)));
    $bsize  = min(4, max(0, (int) get_theme_mod('rv_nav_border_size', 0)));
    $pad_y  = min(20, max(0, (int) get_theme_mod('rv_nav_pad_y', 6)));
    $pad_x  = min(28, max(0, (int) get_theme_mod('rv_nav_pad_x', 0)));
    $gap    = min(48, max(4, (int) get_theme_mod('rv_nav_gap', 24)));
    $lsize  = min(6, max(1, (int) get_theme_mod('rv_nav_line_size', 2)));
    $weight = get_theme_mod('rv_nav_weight', '600');
    if (! in_array($weight, ['300', '400', '500', '600', '700'], true)) {
        $weight = '600';
    }

    $anim = get_theme_mod('rv_nav_hover_anim', 'line');
    if (! in_array($anim, ['none', 'color', 'line', 'fill', 'highlight', 'lift'], true)) {
        $anim = 'line';
    }
    $lineA = get_theme_mod('rv_nav_hover_line_anim', 'slide');
    if (! in_array($lineA, ['slide', 'center', 'fade', 'draw'], true)) {
        $lineA = 'slide';
    }
    $lineP = get_theme_mod('rv_nav_hover_line_pos', 'bottom');
    if (! in_array($lineP, ['bottom', 'top', 'baseline'], true)) {
        $lineP = 'bottom';
    }

    // Doubled-class selectors so nav rules win over app.css in light AND dark.
    $b   = '.rv-header .rv-nav-list.rv-nav-list a';
    $bh  = $b . ':hover';
    $bc  = '.rv-header .rv-nav-list.rv-nav-list .current-menu-item>a';
    $bon = $bh . ',' . $bc;

    $has_box_bg = ($fill_color !== '' && $fill_color !== 'transparent');
    $boxed = $has_box_bg || $hover_bg !== '' || $hover_fill !== '' || $bsize > 0
        || in_array($anim, ['fill', 'highlight'], true);

    // Base item. Padding and gap are always applied (defaults match the theme's
    // original spacing), so they work with or without a fill/border.
    $css = $b . '{font-size:' . $size . 'px;font-weight:' . $weight . ';position:relative;';
    $css .= 'padding:' . $pad_y . 'px ' . $pad_x . 'px;';
    if ($item_color) {
        $css .= 'color:' . $item_color . ';';
    }
    if ($fill_color) {
        $css .= 'background:' . $fill_color . ';'; // 'transparent' is a valid value
    }
    if ($bsize > 0) {
        $css .= 'border:' . $bsize . 'px solid ' . ($border_color ?: 'currentColor') . ';';
    }
    if ($boxed) {
        $css .= 'border-radius:' . $radius . 'px;';
    }
    $css .= 'transition:color .18s ease,background-color .22s ease,border-color .18s ease,transform .18s ease;}';
    // Gap between items (outer spacing).
    $css .= '.rv-header .rv-nav-list.rv-nav-list{gap:' . $gap . 'px;}';

    // Hover / active text colors.
    if ($hover_color) {
        $css .= $bh . '{color:' . $hover_color . ';}';
    }
    $active = $active_color ?: $hover_color;
    if ($active) {
        $css .= $bc . '{color:' . $active . ';}';
    }
    // Hover / active background + border color (both states).
    if ($hover_bg) {
        $css .= $bon . '{background:' . $hover_bg . ';}';
    }
    if ($bsize > 0 && $border_hover) {
        $css .= $bon . '{border-color:' . $border_hover . ';}';
    }

    // Hover animation.
    $line_color = $hover_color ?: ($item_color ?: 'currentColor');
    if ($anim === 'line') {
        $inset = $pad_x . 'px';
        if ($lineP === 'top') {
            $pos = 'top:' . ($pad_y > 0 ? '0.15rem' : '-2px') . ';left:' . $inset . ';right:' . $inset . ';';
        } elseif ($lineP === 'baseline') {
            $pos = 'bottom:' . ($pad_y > 0 ? '0.15rem' : '2px') . ';left:' . $inset . ';right:' . $inset . ';';
        } else { // bottom
            $pos = 'bottom:' . ($pad_y > 0 ? '0.15rem' : '-2px') . ';left:' . $inset . ';right:' . $inset . ';';
        }
        if ($lineA === 'slide') {
            $rest = 'transform:scaleX(0);transform-origin:left;transition:transform .24s ease;';
            $on = 'transform:scaleX(1);';
        } elseif ($lineA === 'center') {
            $rest = 'transform:scaleX(0);transform-origin:center;transition:transform .24s ease;';
            $on = 'transform:scaleX(1);';
        } elseif ($lineA === 'fade') {
            $rest = 'opacity:0;transition:opacity .24s ease;';
            $on = 'opacity:1;';
        } else { // draw — grow width from the left; drop the right anchor
            $pos = str_replace('right:' . $inset . ';', '', $pos);
            $rest = 'width:0;transition:width .28s ease;';
            $on = 'width:calc(100% - ' . (2 * $pad_x) . 'px);';
        }
        $css .= $b . '::after{content:"";position:absolute;' . $pos . 'height:' . $lsize . 'px;background:' . $line_color . ';border-radius:2px;' . $rest . '}';
        $css .= $bh . '::after,' . $bc . '::after{' . $on . '}';
    } else {
        // Every non-line style hides the theme's built-in current-item underline
        // so "None", "Color", fill, highlight and lift truly have no stray line.
        $css .= $bc . '::after{content:none;}';
    }
    if ($anim === 'fill') {
        $fillc = $hover_fill ?: 'var(--color-pine)';
        $ftext = $hover_color ?: '#fff';
        $css .= $b . '{overflow:hidden;z-index:0;}';
        $css .= $b . '::before{content:"";position:absolute;inset:0;background:' . $fillc . ';border-radius:' . $radius . 'px;z-index:-1;transform:scaleX(0);transform-origin:center;transition:transform .28s ease;}';
        $css .= $bh . '::before,' . $bc . '::before{transform:scaleX(1);}';
        $css .= $bon . '{color:' . $ftext . ';}';
    } elseif ($anim === 'highlight') {
        $fillc = $hover_fill ?: 'var(--color-pine)';
        $ftext = $hover_color ?: '#fff';
        $css .= $b . '{background-image:linear-gradient(' . $fillc . ',' . $fillc . ');background-repeat:no-repeat;background-position:center;background-size:0% 100%;transition:background-size .25s ease,color .18s ease;}';
        $css .= $bon . '{background-size:100% 100%;color:' . $ftext . ';}';
    } elseif ($anim === 'lift') {
        $css .= $bon . '{transform:translateY(-2px);}';
    }

    printf('<style id="rv-nav-style">%s</style>' . "\n", $css); // phpcs:ignore
}, 26);

/**
 * Dark / light toggle button styling (Customizer > Header). The preset styles
 * (button / pill / soft / plain) are emitted as modifier-class rules — the base
 * theme never defined them, so before this they all looked identical. Custom
 * colors are layered AFTER the presets so they override, and hover rules use a
 * doubled class so they out-specify the theme's dark-mode hover
 * (`html[data-theme] .rv-theme-toggle:hover`). Dark-mode "soft" tints are only
 * emitted when the matching custom color is blank, so an explicit choice governs
 * both modes. All values are whitelist-clamped or hex-sanitized.
 */
add_action('wp_head', function () {
    $size   = min(56, max(32, (int) get_theme_mod('rv_toggle_size', 44)));
    $radius = min(28, max(0, (int) get_theme_mod('rv_toggle_radius', 14)));
    $isize  = min(30, max(14, (int) get_theme_mod('rv_toggle_icon_size', 20)));
    $bw     = min(4, max(0, (int) get_theme_mod('rv_toggle_border_width', 2)));

    $icon   = sanitize_hex_color(get_theme_mod('rv_toggle_icon_color', ''));
    $bg     = sanitize_hex_color(get_theme_mod('rv_toggle_bg', ''));
    $border = sanitize_hex_color(get_theme_mod('rv_toggle_border_color', ''));
    $hicon  = sanitize_hex_color(get_theme_mod('rv_toggle_hover_icon', ''));
    $hbg    = sanitize_hex_color(get_theme_mod('rv_toggle_hover_bg', ''));

    $t  = '.rv-header .rv-theme-toggle';
    $td = '.rv-header .rv-theme-toggle.rv-theme-toggle'; // doubled class for hover

    // Geometry (applies to every preset).
    $css  = $t . '{width:' . $size . 'px;height:' . $size . 'px;border-radius:' . $radius . 'px;border-width:' . $bw . 'px;transition:background-color .18s ease,border-color .18s ease,color .18s ease;}';
    $css .= $t . ' svg{width:' . $isize . 'px;height:' . $isize . 'px;}';

    // Presets.
    $css .= $t . '--pill{border-radius:999px;}';
    $css .= $t . '--soft{border-color:transparent;';
    if (! $bg) {
        $css .= 'background:color-mix(in srgb,var(--color-pine) 12%,transparent);';
    }
    if (! $icon) {
        $css .= 'color:var(--color-pine);';
    }
    $css .= '}';
    $softDark = 'html[data-theme="dark"] ' . $t . '--soft{';
    if (! $bg) {
        $softDark .= 'background:color-mix(in srgb,var(--color-wheat) 16%,transparent);';
    }
    if (! $icon) {
        $softDark .= 'color:var(--color-wheat);';
    }
    $softDark .= '}';
    $css .= $softDark;
    $css .= $t . '--plain{background:transparent;border-color:transparent;}';
    if (! $hbg) {
        $css .= $td . '--plain:hover{background:color-mix(in srgb,var(--color-pine) 10%,transparent);}';
    }

    // Custom colors override the presets (layered after).
    if ($bg) {
        $css .= $t . '{background:' . $bg . ';}';
    }
    if ($border) {
        $css .= $t . '{border-color:' . $border . ';border-style:solid;}';
    }
    if ($icon) {
        $css .= $t . '{color:' . $icon . ';}';
    }
    if ($hbg) {
        $css .= $td . ':hover{background:' . $hbg . ';}';
    }
    if ($hicon) {
        $css .= $td . ':hover{color:' . $hicon . ';border-color:' . $hicon . ';}';
    }

    printf('<style id="rv-toggle-style">%s</style>' . "\n", $css); // phpcs:ignore
}, 27);

/**
 * Header / top bar / footer layout + responsive options (Customizer). Pure CSS,
 * whitelist-clamped. The nav-collapse breakpoint and CTA rules use !important
 * inside media queries so they reliably override the theme's fixed 860px switch
 * at any chosen width.
 */
add_action('wp_head', function () {
    $hh   = min(104, max(56, (int) get_theme_mod('rv_header_height', 72)));
    $logo = min(72, max(24, (int) get_theme_mod('rv_logo_max_height', 52)));
    $bp   = min(1100, max(720, (int) get_theme_mod('rv_nav_breakpoint', 860)));
    $ctaM = (bool) get_theme_mod('rv_cta_mobile', false);
    $shad = (bool) get_theme_mod('rv_header_shadow', false);

    $talign = get_theme_mod('rv_topbar_align', 'between');
    $tmap   = ['between' => 'space-between', 'left' => 'flex-start', 'center' => 'center', 'right' => 'flex-end'];
    $tjust  = $tmap[$talign] ?? 'space-between';
    $thide  = (bool) get_theme_mod('rv_topbar_hide_mobile', false);

    $fbrowse = (bool) get_theme_mod('rv_footer_hide_browse', false);
    $finvert = (bool) get_theme_mod('rv_footer_logo_invert', true);

    // Header size + logo cap.
    $css  = '.rv-header .rv-header-inner{min-height:' . $hh . 'px;}';
    $css .= '.rv-header .rv-brand img,.rv-header .rv-brand .custom-logo{max-height:' . $logo . 'px;}';
    if ($shad) {
        $css .= '.rv-scrolled .rv-header{box-shadow:0 8px 24px rgba(24,22,18,.10);}';
    }

    // Navigation display mode. 'hamburger' forces the off-canvas menu at every
    // width; 'responsive' switches at the breakpoint (overriding the theme's
    // fixed 860px rule). CTA hides below the breakpoint unless kept on mobile.
    $mode = get_theme_mod('rv_nav_mode', 'responsive');
    if ($mode !== 'hamburger') {
        $mode = 'responsive';
    }
    if ($mode === 'hamburger') {
        $css .= '.rv-header .rv-nav{display:none!important}.rv-header .rv-menu-toggle{display:inline-flex!important}';
        $css .= '@media(min-width:' . ($bp + 1) . 'px){.rv-header .rv-btn-cta{display:inline-flex!important}}';
        $css .= '@media(max-width:' . $bp . 'px){.rv-header .rv-btn-cta{display:' . ($ctaM ? 'inline-flex' : 'none') . '!important}}';
    } else {
        $css .= '@media(max-width:' . $bp . 'px){.rv-header .rv-nav{display:none!important}.rv-header .rv-menu-toggle{display:inline-flex!important}}';
        $css .= '@media(min-width:' . ($bp + 1) . 'px){.rv-header .rv-nav{display:block!important}.rv-header .rv-menu-toggle{display:none!important}.rv-header .rv-btn-cta{display:inline-flex!important}}';
        $css .= '@media(max-width:' . $bp . 'px){.rv-header .rv-btn-cta{display:' . ($ctaM ? 'inline-flex' : 'none') . '!important}}';
    }

    // Top bar alignment + optional mobile hide.
    $css .= '.rv-banner .rv-topbar-inner{justify-content:' . $tjust . ';}';
    if ($thide) {
        $css .= '@media(max-width:640px){.rv-banner .rv-topbar{display:none!important}}';
    }

    // Footer options.
    if ($fbrowse) {
        $css .= '.rv-footer .rv-footer-browse{display:none;}';
    }
    if (! $finvert) {
        $css .= '.rv-footer.rv-footer img,.rv-footer.rv-footer .custom-logo{filter:none;}';
    }

    // Social icons wrap instead of overflowing when there are many links.
    $css .= '.rv-social{flex-wrap:wrap;}';

    // ---- Off-canvas (mobile) menu panel ----
    $oc_side  = get_theme_mod('rv_oc_side', 'right') === 'left' ? 'left' : 'right';
    $oc_w     = min(480, max(240, (int) get_theme_mod('rv_oc_width', 360)));
    $oc_fs    = min(30, max(14, (int) get_theme_mod('rv_oc_font_size', 22)));
    $oc_align = get_theme_mod('rv_oc_align', 'left') === 'center' ? 'center' : 'left';
    $oc_div   = (bool) get_theme_mod('rv_oc_dividers', true);
    $oc_item  = rv_nav_color_value(get_theme_mod('rv_oc_item_color', ''));
    $oc_hover = rv_nav_color_value(get_theme_mod('rv_oc_hover_color', ''));
    $oc_bgmap = [
        'night' => 'var(--night)',
        'ink'   => 'var(--color-ink)',
        'pine'  => 'var(--color-pine)',
        'clay'  => 'var(--color-clay)',
        'paper' => 'var(--color-paper)',
        'white' => '#ffffff',
    ];
    $oc_bgk = get_theme_mod('rv_oc_bg', 'night');
    $oc_bg  = $oc_bgmap[$oc_bgk] ?? 'var(--night)';

    // Panel: width (capped to 92vw so it never runs off-screen), background, and
    // slide-in side. Higher specificity than app.css via the .rv-offcanvas prefix.
    $css .= '.rv-offcanvas .rv-offcanvas-panel{width:min(' . $oc_w . 'px,92vw);background:' . $oc_bg . ';';
    if ($oc_side === 'left') {
        $css .= 'left:0;right:auto;transform:translateX(-100%);box-shadow:20px 0 40px rgba(0,0,0,.25);';
    } else {
        $css .= 'right:0;left:auto;transform:translateX(100%);box-shadow:-20px 0 40px rgba(0,0,0,.25);';
    }
    $css .= '}';
    // Assert the open state explicitly. Our closed-transform rule is unlayered and
    // would otherwise beat the theme's (Tailwind-layered) `.is-open` rule on the
    // cascade-layer order, leaving the panel stuck off-screen. The transition still
    // animates translateX(±100%) → 0.
    $css .= '.rv-offcanvas.is-open .rv-offcanvas-panel{transform:translateX(0)!important;}';
    // Menu items: size, alignment, color, dividers, and long-word safety.
    $css .= '.rv-offcanvas .rv-offcanvas-list{text-align:' . $oc_align . ';}';
    $css .= '.rv-offcanvas .rv-offcanvas-list a{font-size:' . $oc_fs . 'px;overflow-wrap:anywhere;';
    if ($oc_item) {
        $css .= 'color:' . $oc_item . ';';
    }
    if (! $oc_div) {
        $css .= 'border-bottom:0;';
    }
    $css .= '}';
    if ($oc_hover) {
        $css .= '.rv-offcanvas .rv-offcanvas-list a:hover,.rv-offcanvas .rv-offcanvas-list a:focus{color:' . $oc_hover . ';}';
    }
    // Nested sub-menus: indent and de-emphasize so depth-2 items read clearly.
    $css .= '.rv-offcanvas .rv-offcanvas-list .sub-menu{list-style:none;margin:0.15rem 0 0.35rem;padding-left:1rem;}';
    $css .= '.rv-offcanvas .rv-offcanvas-list .sub-menu a{font-size:0.8em;font-weight:600;opacity:.85;border-bottom:0;}';
    // Social row in the panel wraps and follows the chosen alignment.
    $css .= '.rv-offcanvas .rv-offcanvas-social{flex-wrap:wrap;' . ($oc_align === 'center' ? 'justify-content:center;' : '') . '}';

    // Close button: shape, size, color, side.
    $cl_shape = get_theme_mod('rv_oc_close_shape', 'square');
    if (! in_array($cl_shape, ['square', 'circle', 'plain'], true)) {
        $cl_shape = 'square';
    }
    $cl_size  = min(60, max(30, (int) get_theme_mod('rv_oc_close_size', 42)));
    $cl_color = rv_nav_color_value(get_theme_mod('rv_oc_close_color', ''));
    $cl_pos   = get_theme_mod('rv_oc_close_position', 'right') === 'left' ? 'left' : 'right';

    $css .= '.rv-offcanvas .rv-offcanvas-close{width:' . $cl_size . 'px;height:' . $cl_size . 'px;';
    $css .= ($cl_pos === 'left') ? 'margin-left:0;margin-right:auto;' : 'margin-left:auto;margin-right:0;';
    if ($cl_shape === 'circle') {
        $css .= 'border-radius:999px;';
    } elseif ($cl_shape === 'plain') {
        $css .= 'border-radius:0;background:transparent;';
    } else {
        $css .= 'border-radius:14px;';
    }
    if ($cl_color) {
        $css .= 'color:' . $cl_color . ';';
    }
    $css .= '}';
    if ($cl_shape === 'plain') {
        $css .= '.rv-offcanvas .rv-offcanvas-close:hover{background:transparent;opacity:.7;}';
    }
    // Close icon sizes with the button.
    $css .= '.rv-offcanvas .rv-offcanvas-close svg{width:' . max(16, (int) round($cl_size * 0.45)) . 'px;height:' . max(16, (int) round($cl_size * 0.45)) . 'px;}';

    printf('<style id="rv-layout">%s</style>' . "\n", $css); // phpcs:ignore
}, 28);

/**
 * Wire jQuery UI Sortable to the social order control (Customizer screen only).
 */
add_action('customize_controls_enqueue_scripts', function () {
    wp_enqueue_script('jquery-ui-sortable');
    $js = <<<'JS'
(function ($) {
  function init($ul) {
    if (!$ul || !$ul.length || $ul.data('rvInit') || !$.fn.sortable) return;
    $ul.data('rvInit', true);
    var $input = $ul.siblings('.rv-sortable-value');
    $ul.sortable({
      handle: '.rv-sortable-handle',
      axis: 'y',
      tolerance: 'pointer',
      update: function () {
        var keys = $ul.find('.rv-sortable-item').map(function () { return $(this).attr('data-key'); }).get();
        $input.val(keys.join(',')).trigger('change');
      }
    });
  }
  if (window.wp && wp.customize && wp.customize.control) {
    wp.customize.control('rv_social_order', function (control) {
      control.deferred.embedded.done(function () {
        init(control.container.find('.rv-sortable'));
      });
    });
  }
})(jQuery);
JS;
    wp_add_inline_script('customize-controls', $js);
});

add_action('customize_controls_print_styles', function () {
    echo '<style id="rv-sortable-css">'
        . '.rv-sortable{margin:6px 0 0;padding:0;list-style:none;border:1px solid #dcdcde;border-radius:4px;overflow:hidden}'
        . '.rv-sortable-item{display:flex;align-items:center;gap:8px;padding:9px 10px;background:#fff;border-bottom:1px solid #f0f0f1;font-size:13px;cursor:grab}'
        . '.rv-sortable-item:last-child{border-bottom:0}'
        . '.rv-sortable-item:hover{background:#f6f7f7}'
        . '.rv-sortable-handle{color:#8c8f94;cursor:grab;font-size:18px;width:20px;height:20px}'
        . '.rv-sortable .ui-sortable-helper{box-shadow:0 3px 10px rgba(0,0,0,.18)}'
        . '.rv-sortable .ui-sortable-placeholder{visibility:visible!important;background:#f0f6fc;border-bottom:1px solid #f0f0f1}'
        . '</style>';
});
