<?php

/**
 * Dev Mode / Standard Mode — a one-click admin-bar toggle plus a small debug
 * panel (environment, current template, query count, peak memory, load time)
 * with quick links to Theme Tools and clearing compiled views.
 *
 * Three states, stored in `prt_dev_mode`:
 *   - 'auto' (default): on for any non-production environment
 *     (wp_get_environment_type() — set WP_ENVIRONMENT_TYPE in wp-config.php),
 *     off in production.
 *   - 'on': always on, regardless of environment.
 *   - 'off': always off, regardless of environment — useful for seeing the
 *     site the way a real visitor would while working locally.
 *
 * The admin-bar toggle sets an explicit 'on'/'off' (bypassing 'auto'); the
 * Customizer control is the only place to get back to 'auto'. Everything
 * here is gated on manage_options, so it's invisible to anyone but admins.
 */

namespace App;

/**
 * Raw stored mode: 'auto' | 'on' | 'off'.
 *
 * Validates the theme_mod value against the known set rather than trusting
 * it outright, since theme_mods are just serialized options — falling back
 * to 'auto' guards against a corrupted/stale value ever silently forcing
 * Dev Mode permanently on or off.
 */
function prt_dev_mode_setting(): string
{
    $mode = get_theme_mod('prt_dev_mode', 'auto');
    return in_array($mode, ['auto', 'on', 'off'], true) ? $mode : 'auto';
}

/**
 * Whether the debug panel should be available for the current user/request.
 *
 * This is the single gate every other function in this file checks before
 * showing anything — centralizing the manage_options + mode-resolution
 * logic here means the admin-bar UI and any future consumer can't
 * accidentally diverge on who/when Dev Mode is considered "on".
 */
function prt_dev_mode_active(): bool
{
    if (! current_user_can('manage_options')) {
        return false;
    }
    $mode = prt_dev_mode_setting();
    if ($mode === 'on') {
        return true;
    }
    if ($mode === 'off') {
        return false;
    }
    $env = function_exists('wp_get_environment_type') ? wp_get_environment_type() : 'production';
    return $env !== 'production';
}

// Capture the resolved template file so the admin bar node can show it.
// PHP_INT_MAX guarantees this runs last, after any other template_include
// filters have had a chance to swap the template — so what's captured is
// the actual file WordPress ends up loading, not an intermediate value.
// The filter must pass $template through unchanged since it's a "read"
// only, not a template override.
add_filter('template_include', function ($template) {
    $GLOBALS['prt_dev_current_template'] = $template;
    return $template;
}, PHP_INT_MAX);

// Flip prt_dev_mode between 'on' and 'off' from a single nonce-protected
// link (the admin-bar node's href, built below). Runs on admin_init since
// that's the earliest hook after the nonce/capability checks are meaningful
// and before any admin page output has started, so wp_safe_redirect() below
// is guaranteed to still be able to send headers.
add_action('admin_init', function () {
    if (! isset($_GET['prt_dev_toggle'])) {
        return;
    }
    if (! current_user_can('manage_options') || ! check_admin_referer('prt_dev_toggle')) {
        wp_die(esc_html__('Not allowed.', 'pressroot'));
    }
    // Anything other than the literal 'on' collapses to 'off' — the toggle
    // only ever needs two explicit states, never 'auto' (that's Customizer-only).
    $next = sanitize_key(wp_unslash($_GET['prt_dev_toggle'])) === 'on' ? 'on' : 'off';
    set_theme_mod('prt_dev_mode', $next);

    $ref = wp_get_referer();
    wp_safe_redirect($ref ?: admin_url());
    exit;
});

// Builds the admin-bar "Dev Mode" / "Standard Mode" node and, when active,
// its debug-info children. Priority 999 (very late) so this node renders
// after core/plugin admin-bar nodes have already been added — it doesn't
// depend on any of them, but rendering last keeps it visually grouped at
// the end of the bar instead of interleaved with unrelated nodes.
add_action('admin_bar_menu', function (\WP_Admin_Bar $bar) {
    if (! is_admin_bar_showing() || ! current_user_can('manage_options')) {
        return;
    }

    $active = prt_dev_mode_active();
    $mode   = prt_dev_mode_setting();
    $next   = $active ? 'off' : 'on';
    // Route through wp-admin explicitly (not the current URL) since the
    // toggle handler runs on admin_init, which never fires on the front end
    // — the admin bar shows there too, and that's the most common place
    // someone will actually click this.
    $toggleUrl = wp_nonce_url(add_query_arg('prt_dev_toggle', $next, admin_url()), 'prt_dev_toggle');

    // Always-visible top-level node so Standard Mode can be switched to Dev
    // Mode in one click, without opening the Customizer.
    $bar->add_node([
        'id'    => 'prt-debug',
        'title' => $active ? '🟢 ' . __('Dev Mode', 'pressroot') : '⚪ ' . __('Standard Mode', 'pressroot'),
        'href'  => $toggleUrl,
        'meta'  => [
            'title' => $active
                ? __('Dev Mode is on — click to switch to Standard Mode', 'pressroot')
                : __('Standard Mode — click to switch on Dev Mode', 'pressroot'),
        ],
    ]);

    if (! $active) {
        $bar->add_node([
            'id'     => 'prt-debug-enable',
            'parent' => 'prt-debug',
            'title'  => __('Turn on Dev Mode', 'pressroot'),
            'href'   => $toggleUrl,
        ]);
        if ($mode === 'off') {
            $bar->add_node([
                'id'     => 'prt-debug-note',
                'parent' => 'prt-debug',
                'title'  => __('(forced off in Customizer -> Developer)', 'pressroot'),
                'href'   => false,
            ]);
        }
        return;
    }

    $env      = function_exists('wp_get_environment_type') ? wp_get_environment_type() : 'unknown';
    $template = $GLOBALS['prt_dev_current_template'] ?? null;
    $tplLabel = $template ? str_replace(get_theme_root() . '/', '', $template) : __('(not yet resolved)', 'pressroot');
    $queries  = function_exists('get_num_queries') ? get_num_queries() : 0;
    $memory   = size_format(memory_get_peak_usage(true));
    $loadTime = timer_stop(0, 2);

    $rows = [
        'env'      => sprintf(__('Environment: %s', 'pressroot'), $env),
        'template' => sprintf(__('Template: %s', 'pressroot'), $tplLabel),
        'queries'  => sprintf(__('DB queries so far: %s', 'pressroot'), $queries),
        'memory'   => sprintf(__('Peak memory: %s', 'pressroot'), $memory),
        'time'     => sprintf(__('Load time so far: %ss', 'pressroot'), $loadTime),
    ];
    foreach ($rows as $id => $label) {
        $bar->add_node([
            'id'     => "prt-debug-{$id}",
            'parent' => 'prt-debug',
            'title'  => $label,
            'href'   => false,
        ]);
    }

    $bar->add_node([
        'id'     => 'prt-debug-hooks',
        'parent' => 'prt-debug',
        'title'  => __('View registered hooks (WP-CLI: wp pressroot hooks)', 'pressroot'),
        'href'   => false,
    ]);
    $bar->add_node([
        'id'     => 'prt-debug-tools',
        'parent' => 'prt-debug',
        'title'  => __('Pressroot settings (Site Types / export / import)', 'pressroot'),
        'href'   => admin_url('themes.php?page=prt-settings&tab=ai'),
    ]);
    $bar->add_node([
        'id'     => 'prt-debug-clearviews',
        'parent' => 'prt-debug',
        'title'  => __('Clear compiled views', 'pressroot'),
        'href'   => add_query_arg('prt_view_clear', 1, admin_url()),
    ]);
    $bar->add_node([
        'id'     => 'prt-debug-disable',
        'parent' => 'prt-debug',
        'title'  => __('Turn off Dev Mode', 'pressroot'),
        'href'   => $toggleUrl,
    ]);
}, 999);

// Customizer: the full 3-way mode select (Auto / On / Off). This is the
// only UI that can set 'auto' — the admin-bar toggle only ever sets an
// explicit 'on'/'off' — so switching back to environment-based behavior
// always requires a trip through the Customizer.
add_action('customize_register', function (\WP_Customize_Manager $wp) {
    // Shared guarded helper — see prt_ensure_theme_options_panel() in app/customizer.php.
    prt_ensure_theme_options_panel($wp);
    $wp->add_section('prt_dev_section', [
        'title'       => __('Developer', 'pressroot'),
        'panel'       => 'prt_theme_options',
        'description' => __('Tools for whoever is building/maintaining this site. Shown to administrators only.', 'pressroot'),
    ]);

    $wp->add_setting('prt_dev_mode', ['default' => 'auto', 'sanitize_callback' => 'sanitize_key']);
    $wp->add_control('prt_dev_mode', [
        'label'       => __('Dev Mode', 'pressroot'),
        'description' => __('Auto shows the admin-bar debug panel (environment, template, query count, memory, plus quick links) only on non-production environments. On/Off override that everywhere — there\'s also a one-click toggle in the admin bar itself once you\'ve saved this once.', 'pressroot'),
        'section'     => 'prt_dev_section',
        'type'        => 'select',
        'choices'     => [
            'auto' => __('Auto (based on environment)', 'pressroot'),
            'on'   => __('On everywhere', 'pressroot'),
            'off'  => __('Off everywhere', 'pressroot'),
        ],
    ]);
}, 24);
