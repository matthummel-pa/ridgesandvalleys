<?php

/**
 * Pressroot Bookings — appointments & reservations addon.
 *
 * Follows the Repofolio addon pattern exactly: gated on the Add-ons toggle
 * (Customizer → Theme Options → Add-ons), classes under app/Bookings/ with
 * plain require_once loading, booted through a Plugin object, and settings
 * surfaced as a tab on the consolidated Appearance → Pressroot page via the
 * pressroot/settings_tabs filter.
 *
 * Feature set (the "worthwhile core" of established booking apps —
 * Calendly / Acuity / OpenTable — without the bloat):
 *  - Services CPT: duration, price label, buffer, capacity (capacity 1 =
 *    appointment mode; capacity >1 = seats-per-slot reservation mode with a
 *    party-size field, OpenTable-style).
 *  - Weekly availability schedule + blackout dates + minimum notice +
 *    booking window, all timezone-aware (site timezone).
 *  - Slot engine that subtracts existing bookings from capacity — no
 *    double-booking, re-checked at insert time.
 *  - Front-end widget (block `prt/booking` + `[prt_booking]` shortcode):
 *    service picker → date strip → slot grid → details form. Nonce +
 *    honeypot + per-IP rate limit, mirroring app/contact.php.
 *  - Emails: customer confirmation with ICS calendar attachment and a
 *    tokenized cancel link (with a confirm screen so mail scanners can't
 *    auto-cancel), owner notification on book/cancel.
 *  - Admin: bookings list with status quick-actions (pending → confirmed →
 *    cancelled), manual bookings, and a Bookings settings tab.
 */

namespace App;

if (! defined('PRT_BOOKINGS_VERSION') && function_exists('App\\prt_addon_enabled') && prt_addon_enabled('bookings')) {
    define('PRT_BOOKINGS_VERSION', '1.0.0');
    define('PRT_BOOKINGS_DIR', get_theme_file_path('app/Bookings/'));
    define('PRT_BOOKINGS_URL', get_theme_file_uri('app/Bookings/'));

    require_once PRT_BOOKINGS_DIR . 'includes/class-settings.php';
    require_once PRT_BOOKINGS_DIR . 'includes/class-services.php';
    require_once PRT_BOOKINGS_DIR . 'includes/class-engine.php';
    require_once PRT_BOOKINGS_DIR . 'includes/class-emails.php';
    require_once PRT_BOOKINGS_DIR . 'includes/class-rest.php';
    require_once PRT_BOOKINGS_DIR . 'includes/class-block.php';
    require_once PRT_BOOKINGS_DIR . 'includes/class-admin.php';
    require_once PRT_BOOKINGS_DIR . 'includes/class-plugin.php';

    add_action('after_setup_theme', function () {
        $plugin = new \PrtBookings\Plugin();
        $plugin->boot();
        $GLOBALS['prt_bookings'] = $plugin;
    }, 11);
}

/** True when the Bookings addon is booted this request. */
function prt_bookings_active(): bool
{
    return defined('PRT_BOOKINGS_VERSION');
}

/* ── Add-on toggle (Customizer → Theme Options → Add-ons) ──────────────── */

// The 'bookings' default lives in prt_addon_defaults() (app/theme-addons.php)
// rather than being filtered in here — the boot check at the top of this file
// runs before any filter added below would register, so the default must be
// present at include time.

add_action('customize_register', function ($wp) {
    prt_ensure_theme_options_panel($wp);
    $wp->add_setting('prt_addon_bookings_enabled', ['default' => true, 'sanitize_callback' => 'wp_validate_boolean']);
    $wp->add_control('prt_addon_bookings_enabled', [
        'label'       => __('Pressroots Reserve (bookings & reservations)', 'pressroot'),
        'description' => __('Appointment scheduling and table/seat reservations: services, availability, a booking form block, admin calendar, and confirmation emails. Changes apply after publishing (the addon loads at boot).', 'pressroot'),
        'section'     => 'prt_addons_section',
        'type'        => 'checkbox',
    ]);
}, 23);

/* ── Settings tab on Appearance → Pressroot ────────────────────────────── */

add_filter('pressroot/settings_tabs', function (array $tabs): array {
    $tabs['bookings'] = [
        'label'   => __('Bookings', 'pressroot'),
        'render'  => __NAMESPACE__ . '\\prt_bookings_tab_html',
        'visible' => prt_bookings_active(),
    ];
    return $tabs;
});

/** Chrome-less tab body for the consolidated settings page. */
function prt_bookings_tab_html(): void
{
    if (! current_user_can('edit_theme_options')) {
        return;
    }
    if (! prt_bookings_active()) {
        echo '<p>' . esc_html__('The Bookings addon is switched off. Enable it under Customizer → Theme Options → Add-ons.', 'pressroot') . '</p>';
        return;
    }
    \PrtBookings\Settings::render();
}
