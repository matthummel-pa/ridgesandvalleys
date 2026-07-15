<?php

/**
 * "Bar item" blocks — drop-in blocks that render the SAME items configured in the
 * Customizer (and honor their show/hide toggles), so a bar's widget area can be
 * composed from them and stays in sync with the Customizer settings:
 *   prt/bar-social · prt/bar-cta · prt/bar-message · prt/bar-logo · prt/bar-nav · prt/bar-contact
 * Each reads theme settings; if an item is hidden/empty, the block renders nothing.
 */

namespace App;

/**
 * Registry mapping each `prt/bar-*` block slug to its editor label, Dashicon,
 * and PHP render callback. Single source of truth consumed both by the
 * `init` registration below (server) and, via wp_localize_script(), by the
 * JS block registration in resources/js/bar-blocks-editor.js (editor UI) —
 * so a new bar block only needs to be added here once.
 */
function prt_bar_blocks_defs()
{
    return [
        'bar-social'  => ['title' => __('Bar · Social links', 'pressroot'), 'icon' => 'share', 'cb' => 'prt_block_bar_social'],
        'bar-cta'     => ['title' => __('Bar · Button (CTA)', 'pressroot'), 'icon' => 'button', 'cb' => 'prt_block_bar_cta'],
        'bar-message' => ['title' => __('Bar · Message', 'pressroot'), 'icon' => 'megaphone', 'cb' => 'prt_block_bar_message'],
        'bar-logo'    => ['title' => __('Bar · Site logo', 'pressroot'), 'icon' => 'admin-home', 'cb' => 'prt_block_bar_logo'],
        'bar-nav'     => ['title' => __('Bar · Navigation menu', 'pressroot'), 'icon' => 'menu', 'cb' => 'prt_block_bar_nav'],
        'bar-contact' => ['title' => __('Bar · Contact text', 'pressroot'), 'icon' => 'email', 'cb' => 'prt_block_bar_contact'],
    ];
}

// Registers the editor script once and each `prt/bar-*` block's server-side
// render callback. Priority 12 (after the default 10) so it runs after any
// earlier `init` callbacks that register the block category/patterns these
// blocks may depend on.
add_action('init', function () {
    $path = 'resources/js/bar-blocks-editor.js';
    wp_register_script(
        'prt-bar-blocks',
        get_theme_file_uri($path),
        ['wp-blocks', 'wp-element', 'wp-block-editor', 'wp-components', 'wp-server-side-render', 'wp-i18n'],
        file_exists(get_theme_file_path($path)) ? filemtime(get_theme_file_path($path)) : '1',
        true
    );
    $list = [];
    foreach (prt_bar_blocks_defs() as $slug => $d) {
        $list[$slug] = ['title' => $d['title'], 'icon' => $d['icon']];
        register_block_type('prt/' . $slug, [
            'api_version'     => 2,
            'editor_script'   => 'prt-bar-blocks',
            'render_callback' => __NAMESPACE__ . '\\' . $d['cb'],
            'supports'        => ['html' => false, 'spacing' => ['margin' => true]],
        ]);
    }
    wp_localize_script('prt-bar-blocks', 'mhBarBlocks', $list);
}, 12);

/**
 * Renders a muted italic hint explaining why a bar block is empty, but only
 * during the editor's server-side-render REST preview — never on the front
 * end, where an unconfigured block should render as nothing (not a visible
 * placeholder message for site visitors).
 */
function prt_bar_rest_note($msg)
{
    return (defined('REST_REQUEST') && REST_REQUEST) ? '<span style="opacity:.6;font-style:italic">' . esc_html($msg) . '</span>' : '';
}

/**
 * Render callback for prt/bar-social. Pulls the same links as the header
 * (prt_social_links(), defined in menu.php) and honors the site-wide
 * icons-vs-text toggle (prt_social_style) so this block never drifts out of
 * sync with the Customizer settings it's meant to mirror.
 */
function prt_block_bar_social()
{
    $links = function_exists('App\\prt_social_links') ? prt_social_links() : [];
    if (empty($links)) {
        return prt_bar_rest_note(__('No social links set (Customizer -> Menu & Popout).', 'pressroot'));
    }
    $style = get_theme_mod('prt_social_style', 'icons');
    $out = '<ul class="social' . ($style === 'icons' ? ' is-icons' : '') . '" aria-label="' . esc_attr__('Social links', 'pressroot') . '">';
    foreach ($links as $s) {
        $inner = $style === 'icons' ? prt_social_icon($s['key']) : esc_html($s['label']);
        $out .= '<li><a href="' . esc_url($s['url']) . '" aria-label="' . esc_attr($s['label']) . '" rel="me noopener">' . $inner . '</a></li>';
    }
    return $out . '</ul>';
}

/**
 * Render callback for prt/bar-cta. Reads the same CTA text/URL settings the
 * header uses and respects the "show CTA" toggle so the block can be turned
 * off from the Customizer without editing the page/template.
 */
function prt_block_bar_cta()
{
    if (! get_theme_mod('prt_show_cta', true)) {
        return prt_bar_rest_note(__('Header button is hidden (Customizer).', 'pressroot'));
    }
    $t = get_theme_mod('prt_cta_text', __('Find me on Dev.to', 'pressroot'));
    $u = get_theme_mod('prt_cta_url', 'https://dev.to/mattbuildsapps');
    if (! $t || ! $u) {
        return '';
    }
    return '<a class="btn header-cta" href="' . esc_url($u) . '">' . esc_html($t) . '</a>';
}

/**
 * Render callback for prt/bar-message. Surfaces the Announcement Bar text
 * and optional link set in the Customizer; renders nothing if no message is
 * configured so the block never leaves stray markup on the front end.
 */
function prt_block_bar_message()
{
    $t = (string) get_theme_mod('prt_ann_text', '');
    if (trim($t) === '') {
        return prt_bar_rest_note(__('No message set (Customizer -> Announcement Bar).', 'pressroot'));
    }
    $out = '<span class="prt-ann-msg">' . wp_kses_post($t) . '</span>';
    $lu = get_theme_mod('prt_ann_lurl', '');
    $lt = get_theme_mod('prt_ann_ltext', '');
    if ($lu && $lt) {
        $out .= ' <a class="prt-ann-link" href="' . esc_url($lu) . '">' . esc_html($lt) . ' &rarr;</a>';
    }
    return $out;
}

/**
 * Render callback for prt/bar-logo. Falls back to the site name as a text
 * link when no custom logo is set, so the block is never empty even on a
 * freshly-installed site.
 */
function prt_block_bar_logo()
{
    if (function_exists('has_custom_logo') && has_custom_logo()) {
        return get_custom_logo();
    }
    return '<a class="brand-name" href="' . esc_url(home_url('/')) . '" rel="home">' . esc_html(get_bloginfo('name')) . '</a>';
}

/**
 * Render callback for prt/bar-nav. Wraps wp_nav_menu() for the
 * 'primary_navigation' location; shows an editor-only hint if no menu is
 * assigned yet (Appearance -> Menus) instead of failing silently.
 */
function prt_block_bar_nav()
{
    if (! has_nav_menu('primary_navigation')) {
        return prt_bar_rest_note(__('No primary menu assigned (Appearance -> Menus).', 'pressroot'));
    }
    return wp_nav_menu(['theme_location' => 'primary_navigation', 'menu_class' => 'nav', 'echo' => false, 'container' => false]);
}

/**
 * Render callback for prt/bar-contact. Outputs the free-text "contact"
 * string configured under Customizer -> Top Bar (e.g. a phone number or
 * email), allowed to contain basic HTML via wp_kses_post().
 */
function prt_block_bar_contact()
{
    $c = (string) get_theme_mod('prt_topbar_contact', '');
    if (trim($c) === '') {
        return prt_bar_rest_note(__('No contact text set (Customizer -> Top Bar).', 'pressroot'));
    }
    return '<span class="top-bar-contact">' . wp_kses_post($c) . '</span>';
}

/**
 * Editor-only CSS. The block/widgets editor canvas (incl. the Customizer "Widgets"
 * panel) doesn't load the theme's front-end stylesheet, so the SSR previews of the
 * bar blocks fall back to browser defaults — social icons render at intrinsic SVG
 * size and inherit the editor's blue link color, stacked as a bulleted list.
 * This scopes them to a sensible inline row so the preview matches the front end.
 */
add_action('enqueue_block_editor_assets', function () {
    $css = <<<'CSS'
/* mh bar-block editor previews */
[data-type^="prt/bar-"] .social{display:flex;flex-wrap:wrap;align-items:center;gap:14px;list-style:none;margin:0;padding:0;}
[data-type^="prt/bar-"] .social li{margin:0;padding:0;list-style:none;}
[data-type^="prt/bar-"] .social li::marker{content:"";}
[data-type^="prt/bar-"] .social a{display:inline-flex;align-items:center;color:#3b3f46;text-decoration:none;line-height:0;box-shadow:none;}
[data-type^="prt/bar-"] .social a:hover{color:#0f1115;}
[data-type^="prt/bar-"] .social svg{width:20px;height:20px;display:block;fill:currentColor;}
[data-type^="prt/bar-"] .social.is-icons a{padding:0;}
[data-type^="prt/bar-"] .nav{display:flex;flex-wrap:wrap;align-items:center;gap:16px;list-style:none;margin:0;padding:0;font-size:14px;}
[data-type^="prt/bar-"] .nav li{list-style:none;margin:0;}
[data-type^="prt/bar-"] .nav a{text-decoration:none;color:#17191e;}
[data-type^="prt/bar-"] .header-cta,[data-type^="prt/bar-"] .btn{display:inline-block;font-size:13px;padding:8px 16px;border-radius:6px;background:#1f6f43;color:#fff;text-decoration:none;}
[data-type^="prt/bar-"] .brand-name{font-weight:600;text-decoration:none;color:#17191e;}
CSS;
    wp_register_style('prt-bar-editor', false, [], '1');
    wp_enqueue_style('prt-bar-editor');
    wp_add_inline_style('prt-bar-editor', $css);
});
