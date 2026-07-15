<?php

/**
 * White-label + onboarding:
 *  - Branded login screen (logo, colors, link).
 *  - Admin footer credit + "Get started" dashboard widget (onboarding checklist).
 *  - One-click "Create starter pages" (Home/About/Projects/Contact + primary menu).
 */

namespace App;

/**
 * Register the "White Label" Customizer section (login logo/background,
 * admin footer text, dashboard-widget toggle) under the shared Theme Options
 * panel, creating that panel if no other file has registered it first.
 * Priority 29 just needs to land before prt_theme_options is read elsewhere;
 * there's no dependency on the other sections in this panel.
 */
add_action('customize_register', function ($wp) {
    // Shared guarded helper — see prt_ensure_theme_options_panel() in app/customizer.php.
    prt_ensure_theme_options_panel($wp);
    $wp->add_section('prt_wl_section', ['title' => __('White Label', 'pressroot'), 'panel' => 'prt_theme_options', 'description' => __('Brand the login screen and admin.', 'pressroot')]);

    $wp->add_setting('prt_wl_login', ['default' => '', 'sanitize_callback' => 'esc_url_raw']);
    $wp->add_control(new \WP_Customize_Image_Control($wp, 'prt_wl_login', ['label' => __('Login logo', 'pressroot'), 'section' => 'prt_wl_section']));

    $wp->add_setting('prt_wl_login_bg', ['default' => '#FFF9F5', 'sanitize_callback' => 'sanitize_hex_color']);
    $wp->add_control(new \WP_Customize_Color_Control($wp, 'prt_wl_login_bg', ['label' => __('Login background', 'pressroot'), 'section' => 'prt_wl_section']));

    $wp->add_setting('prt_wl_footer', ['default' => '', 'sanitize_callback' => 'sanitize_text_field']);
    $wp->add_control('prt_wl_footer', ['label' => __('Admin footer text', 'pressroot'), 'section' => 'prt_wl_section', 'type' => 'text']);

    $wp->add_setting('prt_wl_dash', ['default' => true, 'sanitize_callback' => 'wp_validate_boolean']);
    $wp->add_control('prt_wl_dash', ['label' => __('Show "Get started" dashboard widget', 'pressroot'), 'section' => 'prt_wl_section', 'type' => 'checkbox']);
}, 29);

/**
 * ---- Login screen ----
 * Prints inline <style> to re-skin wp-login.php: swaps the logo, background,
 * and primary-button color to match the site's brand instead of the default
 * WordPress mark. Falls back to the SEO logo (prt_seo_logo, set elsewhere)
 * when no dedicated login logo has been uploaded, so sites still look branded
 * out of the box without a second logo upload.
 */
add_action('login_enqueue_scripts', function () {
    $logo = get_theme_mod('prt_wl_login', '');
    if (! $logo && get_theme_mod('prt_seo_logo', '')) {
        $logo = get_theme_mod('prt_seo_logo', '');
    }
    $bg    = sanitize_hex_color(get_theme_mod('prt_wl_login_bg', '#FFF9F5')) ?: '#FFF9F5';
    $green = sanitize_hex_color(get_theme_mod('prt_color_action', '#6C4CF1')) ?: '#6C4CF1';

    echo '<style>';
    echo 'body.login{background:' . esc_attr($bg) . ';}';
    if ($logo) {
        echo '.login h1 a{background-image:url(' . esc_url($logo) . ');background-size:contain;background-position:center;width:auto;max-width:280px;height:72px;}';
    }
    echo '.login #backtoblog a,.login #nav a{color:#5c636c;}';
    echo '.wp-core-ui .button-primary{background:' . esc_attr($green) . ';border-color:' . esc_attr($green) . ';}';
    echo '.login form{border-radius:14px;border:1px solid #e6e2d9;}';
    echo '.login input[type=text]:focus,.login input[type=password]:focus{border-color:' . esc_attr($green) . ';box-shadow:0 0 0 1px ' . esc_attr($green) . ';}';
    echo '</style>';
});
// Point the login logo link at the site home instead of WordPress.org, and
// its title-attribute text at the site name instead of "WordPress" — both
// small but necessary parts of removing WordPress branding from the screen.
add_filter('login_headerurl', function () {
    return home_url('/');
});
add_filter('login_headertext', function () {
    return get_bloginfo('name');
});

/**
 * ---- Admin footer ----
 * Replaces the default "Thank you for creating with WordPress" footer text
 * with the site owner's custom credit line, when one has been set.
 */
add_filter('admin_footer_text', function ($text) {
    $custom = get_theme_mod('prt_wl_footer', '');
    return $custom ? esc_html($custom) : $text;
});

/**
 * ---- Dashboard "Get started" widget ----
 * Adds a checklist dashboard widget linking to the key setup screens (Style
 * Kit, identity, social, SEO, menu, performance) so a new site owner has a
 * guided first-run path instead of having to discover these settings on
 * their own. Gated on edit_theme_options so lower-privileged dashboard users
 * don't see setup steps they can't act on.
 */
add_action('wp_dashboard_setup', function () {
    if (! get_theme_mod('prt_wl_dash', true) || ! current_user_can('edit_theme_options')) {
        return;
    }
    wp_add_dashboard_widget('prt_get_started', __('Pressroot — Get started', 'pressroot'), __NAMESPACE__ . '\\prt_dashboard_widget');
});

/**
 * Renders the "Get started" dashboard widget body: the checklist links plus
 * a button into Pressroot AI for building real starter pages (see the
 * NOTE below — this used to have its own blank-page quick action).
 */
function prt_dashboard_widget()
{
    $settings = admin_url('themes.php?page=prt-settings');
    $items = [
        [__('Choose a site type & style', 'pressroot'), $settings . '&tab=ai'],
        [__('Set your logo & site identity', 'pressroot'), admin_url('customize.php?autofocus[section]=title_tagline')],
        [__('Add your social links', 'pressroot'), admin_url('customize.php?autofocus[section]=prt_popout_section')],
        [__('Configure SEO & schema', 'pressroot'), admin_url('customize.php?autofocus[section]=prt_seo_section')],
        [__('Build your menu', 'pressroot'), admin_url('nav-menus.php')],
        [__('Tune performance', 'pressroot'), admin_url('customize.php?autofocus[section]=prt_perf_section')],
    ];
    echo '<p>' . esc_html__('A few steps to make the site yours:', 'pressroot') . '</p><ol style="margin-left:18px">';
    foreach ($items as $it) {
        echo '<li style="margin:6px 0"><a href="' . esc_url($it[1]) . '">' . esc_html($it[0]) . '</a></li>';
    }
    echo '</ol>';
    // NOTE: this used to have its own "Create starter pages + menu" button
    // that inserted 4 blank pages with no content. Removed in favor of
    // pointing straight at the Site Types tab (Appearance -> Pressroot ->
    // Site Types, tab id "ai"), which builds real, pattern-filled starter
    // pages per site type instead of blank ones — no reason to keep the
    // weaker duplicate around.
    echo '<a class="button button-primary" href="' . esc_url($settings . '&tab=ai') . '">' . esc_html__('Build starter pages with Pressroot AI', 'pressroot') . '</a>';
    echo ' <a class="button" href="' . esc_url($settings) . '">' . esc_html__('Pressroot settings', 'pressroot') . '</a>';
}
