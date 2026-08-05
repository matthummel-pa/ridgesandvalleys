<?php
/**
 * Layout controls: per-entry Hero, Content width, and Sidebar for the default
 * page / single / archive templates.
 *
 * Design defaults live in the Customizer (Appearance -> Customize -> Theme
 * Options -> "Layout — Content & Sidebar"). Any page or post can override them
 * from its "Layout & Hero (theme)" box. Clients edit content; the developer
 * sets the design defaults. Nothing here is hard-coded per page.
 */

namespace App;

if (! defined('ABSPATH')) {
    exit;
}

/* ------------------------------------------------------------------ *
 * Option vocabularies
 * ------------------------------------------------------------------ */

/** Allowed content-width modes. */
function layout_widths(): array
{
    return [
        'full'   => __('Full width', 'sage'),
        'boxed'  => __('Boxed', 'sage'),
        'narrow' => __('Narrow (reading)', 'sage'),
    ];
}

/** Allowed sidebar positions. */
function layout_sidebars(): array
{
    return [
        'none'  => __('No sidebar', 'sage'),
        'left'  => __('Left', 'sage'),
        'right' => __('Right', 'sage'),
    ];
}

/* ------------------------------------------------------------------ *
 * Resolver: per-entry override -> per-type Customizer default
 * ------------------------------------------------------------------ */

/**
 * Resolve the effective layout for the current view.
 *
 * @return array{hero:bool,hero_title:string,hero_sub:string,hero_bg:string,width:string,sidebar:string}
 */
function entry_layout(): array
{
    static $cache = [];

    $id  = (int) get_queried_object_id();
    $key = $id . '|' . (is_singular() ? 's' : 'a');
    if (isset($cache[$key])) {
        return $cache[$key];
    }

    $widths   = layout_widths();
    $sidebars = layout_sidebars();

    if (is_singular()) {
        $type = (get_post_type($id) === 'post') ? 'post' : 'page';

        $heroDefault    = (bool) get_theme_mod("rv_{$type}_hero_default", false);
        $widthDefault   = (string) get_theme_mod("rv_{$type}_width", 'full');
        $sidebarDefault = (string) get_theme_mod("rv_{$type}_sidebar", 'none');

        $heroMeta = (string) get_post_meta($id, '_rv_layout_hero', true);
        $hero = $heroMeta === 'show' ? true : ($heroMeta === 'hide' ? false : $heroDefault);

        $widthMeta   = (string) get_post_meta($id, '_rv_layout_width', true);
        $sidebarMeta = (string) get_post_meta($id, '_rv_layout_sidebar', true);

        $width   = isset($widths[$widthMeta]) ? $widthMeta : $widthDefault;
        $sidebar = isset($sidebars[$sidebarMeta]) ? $sidebarMeta : $sidebarDefault;

        $title = (string) get_post_meta($id, '_rv_hero_title', true);
        if ($title === '') {
            $title = get_the_title($id);
        }
        $sub = (string) get_post_meta($id, '_rv_hero_sub', true);

        $bg = (string) get_the_post_thumbnail_url($id, 'rv-hero');
        if ($bg === '') {
            $bg = (string) get_post_meta($id, '_rv_hero_bg', true);
        }
    } else {
        $hero    = false;
        $title   = '';
        $sub     = '';
        $bg      = '';
        $width   = (string) get_theme_mod('rv_archive_width', 'full');
        $sidebar = (string) get_theme_mod('rv_archive_sidebar', 'none');
    }

    if (! isset($widths[$width])) {
        $width = 'full';
    }
    if (! isset($sidebars[$sidebar])) {
        $sidebar = 'none';
    }

    // A sidebar column only appears when the widget area actually has widgets.
    if ($sidebar !== 'none' && ! is_active_sidebar('sidebar-primary')) {
        $sidebar = 'none';
    }

    return $cache[$key] = [
        'hero'       => (bool) $hero,
        'hero_title' => $title,
        'hero_sub'   => $sub,
        'hero_bg'    => $bg,
        'width'      => $width,
        'sidebar'    => $sidebar,
    ];
}

/** Whether the current entry shows the full-bleed hero band. */
function entry_hero_enabled(): bool
{
    return entry_layout()['hero'];
}

/* ------------------------------------------------------------------ *
 * Body classes (width + sidebar state for global CSS hooks)
 * ------------------------------------------------------------------ */
add_filter('body_class', function (array $classes): array {
    if (is_singular() || is_archive() || is_home()) {
        $l = entry_layout();
        $classes[] = 'rv-w-' . $l['width'];
        $classes[] = $l['sidebar'] === 'none' ? 'rv-no-side' : ('rv-side-' . $l['sidebar']);
    }
    return $classes;
});

/* ------------------------------------------------------------------ *
 * Customizer: design defaults (Theme Options panel)
 * ------------------------------------------------------------------ */
add_action('customize_register', function ($wp_customize) {
    if (! $wp_customize->get_panel('rv_theme_options')) {
        $wp_customize->add_panel('rv_theme_options', [
            'title'    => __('Theme Options', 'sage'),
            'priority' => 30,
        ]);
    }

    $wp_customize->add_section('rv_layout_cs', [
        'title'       => __('Layout — Content & Sidebar', 'sage'),
        'panel'       => 'rv_theme_options',
        'description' => __('Design defaults for the standard page, post, and archive templates. Any page or post can override these from its "Layout & Hero" box.', 'sage'),
    ]);

    $widthChoices   = layout_widths();
    $sidebarChoices = layout_sidebars();

    $add_select = function ($id, $label, $default, $choices) use ($wp_customize) {
        $wp_customize->add_setting($id, [
            'default'           => $default,
            'sanitize_callback' => 'sanitize_key',
        ]);
        $wp_customize->add_control($id, [
            'label'   => $label,
            'section' => 'rv_layout_cs',
            'type'    => 'select',
            'choices' => $choices,
        ]);
    };

    $add_toggle = function ($id, $label, $default = false) use ($wp_customize) {
        $wp_customize->add_setting($id, [
            'default'           => $default,
            'sanitize_callback' => function ($v) { return (bool) $v; },
        ]);
        $wp_customize->add_control($id, [
            'label'   => $label,
            'section' => 'rv_layout_cs',
            'type'    => 'checkbox',
        ]);
    };

    // Pages
    $add_toggle('rv_page_hero_default', __('Pages: show hero band by default', 'sage'), false);
    $add_select('rv_page_width', __('Pages: content width', 'sage'), 'full', $widthChoices);
    $add_select('rv_page_sidebar', __('Pages: sidebar', 'sage'), 'none', $sidebarChoices);

    // Posts
    $add_toggle('rv_post_hero_default', __('Posts: show hero band by default', 'sage'), false);
    $add_select('rv_post_width', __('Posts: content width', 'sage'), 'full', $widthChoices);
    $add_select('rv_post_sidebar', __('Posts: sidebar', 'sage'), 'none', $sidebarChoices);

    // Archives (blog index, categories, tags, search)
    $add_select('rv_archive_width', __('Archives: content width', 'sage'), 'full', $widthChoices);
    $add_select('rv_archive_sidebar', __('Archives: sidebar', 'sage'), 'none', $sidebarChoices);

    // Sidebar column width
    $wp_customize->add_setting('rv_sidebar_width', [
        'default'           => 300,
        'sanitize_callback' => 'absint',
    ]);
    $wp_customize->add_control('rv_sidebar_width', [
        'label'       => __('Sidebar width (px)', 'sage'),
        'section'     => 'rv_layout_cs',
        'type'        => 'number',
        'input_attrs' => ['min' => 220, 'max' => 420, 'step' => 10],
    ]);
}, 20);

/** Emit the sidebar-width CSS variable when it differs from the default. */
add_action('wp_head', function () {
    $w = max(220, min(420, (int) get_theme_mod('rv_sidebar_width', 300)));
    if ($w !== 300) {
        printf('<style id="rv-layout-vars">:root{--rv-sidebar-w:%dpx;}</style>' . "\n", $w);
    }
}, 20);

/* ------------------------------------------------------------------ *
 * Per-entry "Layout & Hero" box (pages + posts)
 * ------------------------------------------------------------------ */
add_action('add_meta_boxes', function () {
    foreach (['page', 'post'] as $pt) {
        add_meta_box(
            'rv_layout_box',
            __('Layout & Hero (theme)', 'sage'),
            __NAMESPACE__ . '\\render_layout_box',
            $pt,
            'side',
            'default'
        );
    }
});

add_action('admin_enqueue_scripts', function ($hook) {
    if (in_array($hook, ['post.php', 'post-new.php'], true)) {
        wp_enqueue_media();
    }
});

function render_layout_box($post): void
{
    wp_nonce_field('rv_layout_box', 'rv_layout_box_nonce');

    $hero    = (string) get_post_meta($post->ID, '_rv_layout_hero', true);
    $width   = (string) get_post_meta($post->ID, '_rv_layout_width', true);
    $sidebar = (string) get_post_meta($post->ID, '_rv_layout_sidebar', true);
    $hTitle  = (string) get_post_meta($post->ID, '_rv_hero_title', true);
    $hSub    = (string) get_post_meta($post->ID, '_rv_hero_sub', true);
    $hBg     = (string) get_post_meta($post->ID, '_rv_hero_bg', true);

    $heroOpts = [
        ''     => __('Default (Customizer)', 'sage'),
        'show' => __('Show hero band', 'sage'),
        'hide' => __('Hide hero band', 'sage'),
    ];
    $widthOpts   = ['' => __('Default (Customizer)', 'sage')] + layout_widths();
    $sidebarOpts = ['' => __('Default (Customizer)', 'sage')] + layout_sidebars();

    $select = function ($name, $current, $options) {
        printf('<select name="%s" id="%s" style="width:100%%">', esc_attr($name), esc_attr($name));
        foreach ($options as $val => $lab) {
            printf('<option value="%s"%s>%s</option>', esc_attr($val), selected($current, $val, false), esc_html($lab));
        }
        echo '</select>';
    };

    echo '<p><label for="rv_layout_hero"><strong>' . esc_html__('Hero band', 'sage') . '</strong></label><br>';
    $select('rv_layout_hero', $hero, $heroOpts);
    echo '</p>';

    echo '<p><label for="rv_hero_title">' . esc_html__('Hero title (optional)', 'sage') . '</label><br>';
    printf('<input type="text" name="rv_hero_title" id="rv_hero_title" value="%s" style="width:100%%" placeholder="%s"></p>', esc_attr($hTitle), esc_attr__('Defaults to the page title', 'sage'));

    echo '<p><label for="rv_hero_sub">' . esc_html__('Hero subtitle (optional)', 'sage') . '</label><br>';
    printf('<textarea name="rv_hero_sub" id="rv_hero_sub" rows="2" style="width:100%%">%s</textarea></p>', esc_textarea($hSub));

    echo '<p><label for="rv_hero_bg">' . esc_html__('Hero background image', 'sage') . '</label><br>';
    printf('<input type="url" name="rv_hero_bg" id="rv_hero_bg" value="%s" style="width:100%%" placeholder="https://…"><br>', esc_attr($hBg));
    echo '<button type="button" class="button rv-hero-bg-pick" style="margin-top:.4rem">' . esc_html__('Choose image', 'sage') . '</button> ';
    echo '<button type="button" class="button-link rv-hero-bg-clear">' . esc_html__('Clear', 'sage') . '</button>';
    echo '<span class="description" style="display:block;margin-top:.35rem">' . esc_html__('Falls back to the Featured Image when empty.', 'sage') . '</span></p>';

    echo '<hr>';

    echo '<p><label for="rv_layout_width"><strong>' . esc_html__('Content width', 'sage') . '</strong></label><br>';
    $select('rv_layout_width', $width, $widthOpts);
    echo '</p>';

    echo '<p><label for="rv_layout_sidebar"><strong>' . esc_html__('Sidebar', 'sage') . '</strong></label><br>';
    $select('rv_layout_sidebar', $sidebar, $sidebarOpts);
    echo '<span class="description" style="display:block;margin-top:.35rem">' . esc_html__('Needs widgets in Appearance → Widgets → Blog Sidebar. Applies to the default template.', 'sage') . '</span></p>';
    ?>
    <script>
    (function () {
      var pick = document.querySelector('.rv-hero-bg-pick');
      var clear = document.querySelector('.rv-hero-bg-clear');
      var field = document.getElementById('rv_hero_bg');
      if (pick && window.wp && wp.media) {
        var frame;
        pick.addEventListener('click', function (e) {
          e.preventDefault();
          if (frame) { frame.open(); return; }
          frame = wp.media({ title: 'Hero background', button: { text: 'Use image' }, multiple: false });
          frame.on('select', function () {
            var a = frame.state().get('selection').first().toJSON();
            field.value = (a.sizes && a.sizes.large ? a.sizes.large.url : a.url) || '';
          });
          frame.open();
        });
      }
      if (clear && field) { clear.addEventListener('click', function (e) { e.preventDefault(); field.value = ''; }); }
    })();
    </script>
    <?php
}

add_action('save_post', function ($post_id) {
    if (! isset($_POST['rv_layout_box_nonce'])
        || ! wp_verify_nonce(sanitize_text_field(wp_unslash($_POST['rv_layout_box_nonce'])), 'rv_layout_box')) {
        return;
    }
    if (defined('DOING_AUTOSAVE') && DOING_AUTOSAVE) {
        return;
    }
    if (! current_user_can('edit_post', $post_id)) {
        return;
    }

    $widths   = layout_widths();
    $sidebars = layout_sidebars();

    $hero = isset($_POST['rv_layout_hero']) ? sanitize_key(wp_unslash($_POST['rv_layout_hero'])) : '';
    update_post_meta($post_id, '_rv_layout_hero', in_array($hero, ['show', 'hide'], true) ? $hero : '');

    $width = isset($_POST['rv_layout_width']) ? sanitize_key(wp_unslash($_POST['rv_layout_width'])) : '';
    update_post_meta($post_id, '_rv_layout_width', isset($widths[$width]) ? $width : '');

    $sidebar = isset($_POST['rv_layout_sidebar']) ? sanitize_key(wp_unslash($_POST['rv_layout_sidebar'])) : '';
    update_post_meta($post_id, '_rv_layout_sidebar', isset($sidebars[$sidebar]) ? $sidebar : '');

    $title = isset($_POST['rv_hero_title']) ? sanitize_text_field(wp_unslash($_POST['rv_hero_title'])) : '';
    update_post_meta($post_id, '_rv_hero_title', $title);

    $sub = isset($_POST['rv_hero_sub']) ? sanitize_textarea_field(wp_unslash($_POST['rv_hero_sub'])) : '';
    update_post_meta($post_id, '_rv_hero_sub', $sub);

    $bg = isset($_POST['rv_hero_bg']) ? esc_url_raw(wp_unslash($_POST['rv_hero_bg'])) : '';
    update_post_meta($post_id, '_rv_hero_bg', $bg);
});
