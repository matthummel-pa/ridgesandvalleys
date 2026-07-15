<?php

/**
 * Plugin-free contact form handler + small archive tweak.
 *
 * Exists so the theme's contact form (resources/views/template-contact.blade.php)
 * works standalone, without requiring Contact Form 7 / WPForms / etc. Handles
 * validation, spam mitigation, and sending via wp_mail(), then redirects back
 * to the referring page with a `?contact=success|error` status flag that the
 * template reads to show a confirmation/error message.
 */

namespace App;

/** Clean archive titles ("Category: Foo" -> "Foo"); the theme's archive
 *  templates already show the term name prominently, so the "Category:"
 *  prefix is redundant. */
add_filter('get_the_archive_title_prefix', '__return_empty_string');

/**
 * Handle the contact form submission (template-contact.blade.php).
 *
 * Hooked to `init` (not `admin_post_*`) because the form posts back to the
 * same public front-end page it was submitted from, not to wp-admin — this
 * runs on every request, so it bails immediately unless the specific
 * `action=prt_contact` POST field is present.
 */
add_action('init', function () {
    if (! isset($_POST['action']) || $_POST['action'] !== 'prt_contact') {
        return;
    }

    // Always return the visitor to the page they submitted from, stripping any
    // stale ?contact= status so redirects don't stack up across resubmits.
    $back = wp_get_referer() ?: home_url('/');
    $back = remove_query_arg('contact', $back);

    $redirect = function ($status) use ($back) {
        wp_safe_redirect(add_query_arg('contact', $status, $back));
        exit;
    };

    $nonce = isset($_POST['prt_contact_nonce']) ? $_POST['prt_contact_nonce'] : '';
    if (! wp_verify_nonce($nonce, 'prt_contact')) {
        $redirect('error');
    }

    // Honeypot: bots fill this hidden field; pretend success and bail so bots
    // don't learn their submission was rejected (and don't get real emails sent).
    if (! empty($_POST['prt_hp'])) {
        $redirect('success');
    }

    $name    = sanitize_text_field($_POST['prt_name'] ?? '');
    $email   = sanitize_email($_POST['prt_email'] ?? '');
    $subject = sanitize_text_field($_POST['prt_subject'] ?? '');
    $message = sanitize_textarea_field($_POST['prt_message'] ?? '');

    if ($name === '' || ! is_email($email) || $message === '') {
        $redirect('error');
    }

    // Rate limit: the endpoint is nonce-protected, but nonces are page-scoped
    // rather than per-submission, so a scraped page could still be replayed
    // as a spam relay. One message per IP per 30 seconds is invisible to
    // humans and fatal to scripts. Fails open when REMOTE_ADDR is missing —
    // no key means no throttle, never a lockout.
    $ip = isset($_SERVER['REMOTE_ADDR']) ? sanitize_text_field(wp_unslash($_SERVER['REMOTE_ADDR'])) : '';
    if ($ip !== '') {
        $throttleKey = 'prt_contact_' . md5($ip);
        if (get_transient($throttleKey)) {
            $redirect('error');
        }
        set_transient($throttleKey, 1, 30);
    }

    // Sent to the site admin email rather than a configurable address — there's
    // no Customizer/settings field for a custom recipient, so admin_email is
    // the only destination.
    $to      = get_option('admin_email');
    $subject = $subject !== '' ? $subject : __('New contact form message', 'pressroot');
    $body    = sprintf(
        /* translators: 1: sender name, 2: sender email, 3: message */
        __("Name: %1\$s\nEmail: %2\$s\n\n%3\$s", 'pressroot'),
        $name,
        $email,
        $message
    );
    $headers = ['Reply-To: ' . $name . ' <' . $email . '>'];

    // Subject prefix comes from the SITE, not the theme author — this was a
    // hardcoded '[matthummel.com]' before, which branded every install's
    // outgoing mail for the author's own site.
    $prefix = wp_specialchars_decode(get_bloginfo('name'), ENT_QUOTES);
    wp_mail($to, ($prefix !== '' ? '[' . $prefix . '] ' : '') . $subject, $body, $headers);

    $redirect('success');
});
