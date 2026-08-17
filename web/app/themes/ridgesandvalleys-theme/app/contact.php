<?php

/**
 * Plugin-free, editor-driven contact form.
 *
 * The fields are defined per page in the editor (Page content → “Contact form —
 * fields”, a repeater of label / type / required / column-width / options) and
 * stored as post meta. Both the renderer and the submit handler read that same
 * definition, so the form is fully dynamic — add, remove, reorder, and lay out
 * fields with no code and no form plugin. Security stays built in: nonce +
 * honeypot + per-IP rate limit + wp_mail.
 * Deploy retry 2026-08-17: prior rsync timed out.
 */

namespace App;

/** Field types the builder understands (whitelist — anything else becomes text). */
function contact_field_types(): array
{
    return ['text', 'email', 'tel', 'url', 'number', 'date', 'textarea', 'select', 'radio', 'checkbox', 'heading'];
}

/** The built-in quote form, used when a page has no custom fields yet. */
function contact_field_defaults(): array
{
    return [
        ['label' => __('About you', 'sage'), 'type' => 'heading', 'placeholder' => '', 'required' => '', 'width' => 'full', 'choices' => []],
        ['label' => __('Your name', 'sage'), 'type' => 'text', 'placeholder' => '', 'required' => '1', 'width' => 'half', 'choices' => []],
        ['label' => __('Email', 'sage'), 'type' => 'email', 'placeholder' => '', 'required' => '1', 'width' => 'half', 'choices' => []],
        ['label' => __('Phone', 'sage'), 'type' => 'tel', 'placeholder' => __('Optional — if you’d rather a call', 'sage'), 'required' => '', 'width' => 'half', 'choices' => []],
        ['label' => __('Business name', 'sage'), 'type' => 'text', 'placeholder' => __('Optional', 'sage'), 'required' => '', 'width' => 'half', 'choices' => []],
        ['label' => __('Town', 'sage'), 'type' => 'text', 'placeholder' => __('Gettysburg, Hanover…', 'sage'), 'required' => '', 'width' => 'half', 'choices' => []],
        ['label' => __('How should I reach you?', 'sage'), 'type' => 'select', 'placeholder' => __('Email is fine', 'sage'), 'required' => '', 'width' => 'half', 'choices' => [
            __('Email', 'sage'),
            __('Phone / text', 'sage'),
            __('Either', 'sage'),
        ]],
        ['label' => __('About the project', 'sage'), 'type' => 'heading', 'placeholder' => '', 'required' => '', 'width' => 'full', 'choices' => []],
        ['label' => __('What do you need?', 'sage'), 'type' => 'radio', 'placeholder' => '', 'required' => '1', 'width' => 'full', 'choices' => [
            __('A new website', 'sage'),
            __('Fix or rescue the one I have', 'sage'),
            __('Local SEO / Google Maps', 'sage'),
            __('Care & Grow (ongoing)', 'sage'),
            __('Just saying hello', 'sage'),
            __('Not sure yet', 'sage'),
        ]],
        ['label' => __('Current website', 'sage'), 'type' => 'url', 'placeholder' => __('https:// — if you have one', 'sage'), 'required' => '', 'width' => 'full', 'choices' => []],
        ['label' => __('When would you like to launch?', 'sage'), 'type' => 'select', 'placeholder' => __('No rush', 'sage'), 'required' => '', 'width' => 'half', 'choices' => [
            __('As soon as we can', 'sage'),
            __('This month', 'sage'),
            __('In the next few months', 'sage'),
            __('Just exploring for now', 'sage'),
        ]],
        ['label' => __('Which package sounds right?', 'sage'), 'type' => 'select', 'placeholder' => __('Not sure yet — that’s fine', 'sage'), 'required' => '', 'width' => 'half', 'choices' => [
            __('Website Rescue', 'sage'),
            __('Local Launch', 'sage'),
            __('Growth Site', 'sage'),
            __('Care & Grow', 'sage'),
            __('Not sure yet', 'sage'),
        ]],
        ['label' => __('The story', 'sage'), 'type' => 'heading', 'placeholder' => '', 'required' => '', 'width' => 'full', 'choices' => []],
        ['label' => __('What would a win look like?', 'sage'), 'type' => 'textarea', 'placeholder' => __('A few sentences is plenty — the customers, the snag, what you’d like the site to do.', 'sage'), 'required' => '1', 'width' => 'full', 'choices' => []],
    ];
}

/**
 * Resolve the normalized field list for a page: the saved definition or, when
 * none exists, the defaults. Every row is normalized to a known shape/type.
 */
function contact_fields(?int $post_id = null): array
{
    // `cquote_fields` is a new key so a real quote form ships past any saved
    // four-field `cform_fields` meta on the Contact page.
    $rows = field_rows('cquote_fields', contact_field_defaults(), $post_id);

    $out = [];
    foreach ($rows as $row) {
        if (! is_array($row)) {
            continue;
        }
        // Strip the live-preview click-to-edit markers field_rows() adds — left in,
        // they break comparisons (type/width) and, worse, corrupt the placeholder
        // attribute (a marker becomes a <span> inside placeholder="…").
        $label = trim(strip_field_markers((string) ($row['label'] ?? '')));
        if ($label === '') {
            continue; // a field with no label can't be rendered or labelled
        }
        $type = strip_field_markers((string) ($row['type'] ?? 'text'));
        if (! in_array($type, contact_field_types(), true)) {
            $type = 'text';
        }
        $choices = $row['choices'] ?? [];
        if (! is_array($choices)) {
            $choices = strip_field_markers((string) $choices);
            $choices = array_values(array_filter(array_map('trim', preg_split('/\r\n|\r|\n/', $choices))));
        }
        $choices = array_map(fn ($c) => strip_field_markers((string) $c), $choices);
        $width = strip_field_markers((string) ($row['width'] ?? 'full'));
        if (! in_array($width, ['full', 'half', 'third'], true)) {
            $width = 'full';
        }

        $out[] = [
            'label'       => $label,
            'type'        => $type,
            'placeholder' => strip_field_markers((string) ($row['placeholder'] ?? '')),
            'required'    => ((string) ($row['required'] ?? '') === '1'),
            'width'       => $width,
            'choices'     => array_values(array_map('strval', $choices)),
        ];
    }

    return $out ?: contact_fields_normalize(contact_field_defaults());
}

/**
 * Package name from a services-page CTA (?package= or posted rv_package).
 * Empty when the visitor arrived at contact without picking a plan.
 */
function contact_inquiry_package(): string
{
    $raw = '';
    if (isset($_POST['rv_package'])) { // phpcs:ignore WordPress.Security.NonceVerification.Missing
        $raw = (string) wp_unslash($_POST['rv_package']);
    } elseif (isset($_GET['package'])) { // phpcs:ignore WordPress.Security.NonceVerification.Recommended
        $raw = (string) wp_unslash($_GET['package']);
    }
    $raw = sanitize_text_field($raw);
    $raw = trim($raw);
    if ($raw === '' || strlen($raw) > 80 || preg_match('~https?://|www\.~i', $raw)) {
        return '';
    }

    $from_slug = svc_package_name_from_slug($raw);
    if ($from_slug !== '') {
        return $from_slug;
    }

    return $raw;
}

/** Normalize a raw default set (used as the ultimate fallback). */
function contact_fields_normalize(array $rows): array
{
    $out = [];
    foreach ($rows as $row) {
        $out[] = [
            'label'       => (string) ($row['label'] ?? ''),
            'type'        => (string) ($row['type'] ?? 'text'),
            'placeholder' => (string) ($row['placeholder'] ?? ''),
            'required'    => ((string) ($row['required'] ?? '') === '1'),
            'width'       => (string) ($row['width'] ?? 'full'),
            'choices'     => array_values(array_map('strval', (array) ($row['choices'] ?? []))),
        ];
    }
    return $out;
}

/** Public studio inbox used for quotes and mailto links. */
function contact_studio_email(): string
{
    return 'matt@ridgesandvalleys.com';
}

/**
 * Where quote requests are delivered. Studio inbox plus Matt’s personal
 * address so guest submissions are not lost if one mailbox is quiet.
 *
 * @return list<string>
 */
function contact_recipient_emails(): array
{
    $emails = [];
    foreach ([
        get_theme_mod('rv_contact_email', ''),
        contact_studio_email(),
        'matt@matthummel.com',
    ] as $raw) {
        $email = strtolower(sanitize_email((string) $raw));
        if (is_email($email)) {
            $emails[$email] = $email;
        }
    }

    return array_values($emails);
}

function contact_recipient_email(): string
{
    $emails = contact_recipient_emails();

    return $emails[0] ?? contact_studio_email();
}

/**
 * From address the mail server will accept. Guest (non-domain) addresses
 * cannot be used as From on SiteGround — they go in Reply-To instead.
 */
function contact_from_email_for_visitor(string $visitor_email): string
{
    $visitor_email = strtolower(sanitize_email($visitor_email));
    if (is_email($visitor_email) && str_ends_with($visitor_email, '@ridgesandvalleys.com')) {
        return $visitor_email;
    }

    return contact_studio_email();
}

/**
 * Send one contact-form message with an explicit From / Reply-To.
 * Quote notices use the visitor’s address as From so they don’t show as
 * wordpress@ridgesandvalleys.com. The SMTP envelope stays on our domain
 * so SiteGround will still accept the send.
 */
function contact_mail(
    string|array $to,
    string $subject,
    string $body,
    string $from_email,
    string $from_name = '',
    string $reply_email = '',
    string $reply_name = ''
): bool {
    $tos = is_array($to) ? $to : [$to];
    $tos = array_values(array_unique(array_filter(array_map('sanitize_email', $tos), 'is_email')));
    if ($tos === [] || ! is_email($from_email)) {
        return false;
    }

    $from_name   = contact_safe_display_name($from_name);
    $reply_name  = contact_safe_display_name($reply_name);
    $reply_email = ($reply_email !== '' && is_email($reply_email)) ? $reply_email : $from_email;
    $envelope    = contact_studio_email();
    $from_label  = $from_name !== '' ? $from_name : $from_email;

    $from_filter = static function () use ($from_email) {
        return $from_email;
    };
    $name_filter = static function () use ($from_label) {
        return $from_label;
    };
    $phpmailer_cb = static function ($phpmailer) use ($from_email, $from_label, $reply_email, $reply_name, $envelope) {
        try {
            $phpmailer->setFrom($from_email, $from_label, false);
        } catch (\Throwable $e) {
            return;
        }
        if (is_email($envelope)) {
            $phpmailer->Sender = $envelope;
        }
        $phpmailer->clearReplyTos();
        if (is_email($reply_email)) {
            try {
                $phpmailer->addReplyTo($reply_email, $reply_name !== '' ? $reply_name : $reply_email);
            } catch (\Throwable $e) {
                // Reply-To is best-effort; the From address still identifies the visitor.
            }
        }
    };

    add_filter('wp_mail_from', $from_filter, PHP_INT_MAX);
    add_filter('wp_mail_from_name', $name_filter, PHP_INT_MAX);
    add_action('phpmailer_init', $phpmailer_cb, PHP_INT_MAX);

    $from_header = ($from_label !== '' && $from_label !== $from_email)
        ? sprintf('%s <%s>', $from_label, $from_email)
        : $from_email;
    $sent = wp_mail($tos, $subject, $body, [
        'Content-Type: text/plain; charset=UTF-8',
        'From: ' . $from_header,
    ]);

    remove_filter('wp_mail_from', $from_filter, PHP_INT_MAX);
    remove_filter('wp_mail_from_name', $name_filter, PHP_INT_MAX);
    remove_action('phpmailer_init', $phpmailer_cb, PHP_INT_MAX);

    return (bool) $sent;
}

/** Public email shown on the Contact page (never a leftover Gmail default). */
function contact_display_email(?int $post_id = null): string
{
    $studio = contact_studio_email();
    $saved  = sanitize_email((string) field('contact_email', $studio, $post_id));
    if (! is_email($saved) || preg_match('/@(gmail|googlemail)\./i', $saved)) {
        return $studio;
    }

    return $saved;
}

/** True when a form label is the visitor’s name, not a business/company name. */
function contact_label_is_person_name(string $label): bool
{
    $label = strtolower(trim(strip_field_markers($label)));
    if ($label === '') {
        return false;
    }
    if (preg_match('/business|company|organization|studio|brand/', $label)) {
        return false;
    }

    return (bool) preg_match('/\bname\b/', $label);
}

/** Strip characters that would break a From: display name. */
function contact_safe_display_name(string $name): string
{
    $name = trim(str_replace(["\r", "\n"], ' ', $name));
    $name = str_replace(['<', '>', '"'], '', $name);

    return trim($name);
}

/** @return list<array{strong:string,text:string}> */
function contact_success_steps(?int $post_id = null): array
{
    $rows = field_rows('cnt_next', contact_next_defaults(), $post_id);

    return is_array($rows) && $rows !== [] ? $rows : contact_next_defaults();
}

/** Plain-text confirmation emailed back to the person who submitted the form. */
function contact_sender_confirmation_body(string $name, array $lines, ?int $post_id = null): string
{
    $hello = $name !== ''
        ? sprintf(__('Hi %s,', 'sage'), $name)
        : __('Hi,', 'sage');

    $lede = field(
        'cnt_form_success',
        __('Your request reached Matt at Ridges & Valleys Studio. This is a confirmation that it was sent — you’ll get a personal reply, not a ticket.', 'sage'),
        $post_id
    );
    $when = field(
        'cnt_form_success_when',
        __('Expect a reply within one business day (Monday–Friday). Messages sent on a weekend or holiday get a note Monday morning.', 'sage'),
        $post_id
    );
    $note = field(
        'cnt_form_success_note',
        sprintf(__('Need to add something? Email %s from the address you used on the form.', 'sage'), contact_studio_email()),
        $post_id
    );

    $parts = [$hello, '', wp_strip_all_tags((string) $lede), '', wp_strip_all_tags((string) $when), ''];
    $parts[] = __('What happens next', 'sage');
    foreach (contact_success_steps($post_id) as $step) {
        $strong = strip_field_markers(trim((string) ($step['strong'] ?? '')));
        $text   = strip_field_markers(trim((string) ($step['text'] ?? '')));
        if ($strong === '' && $text === '') {
            continue;
        }
        $parts[] = '• ' . trim($strong . ' ' . $text);
    }
    $parts[] = '';
    $parts[] = wp_strip_all_tags((string) $note);
    $parts[] = '';
    $parts[] = __('Here’s a copy of what you sent:', 'sage');
    $parts[] = implode("\n", $lines);
    $parts[] = '';
    $parts[] = __('— Matt', 'sage');
    $parts[] = __('Ridges & Valleys Studio', 'sage');
    $parts[] = contact_studio_email();

    return implode("\n", $parts) . "\n";
}

/** Sanitize one submitted value by its field type (with a sane length cap). */
function contact_sanitize_value(string $type, $raw): string
{
    $raw = wp_unslash((string) $raw);
    switch ($type) {
        case 'textarea':
            $val = sanitize_textarea_field($raw);
            $max = 5000;
            break;
        case 'email':
            $val = sanitize_email($raw);
            $max = 254;
            break;
        case 'url':
            $val = esc_url_raw(trim($raw));
            $max = 2000;
            break;
        case 'number':
            $val = (string) preg_replace('/[^0-9.\-]/', '', $raw);
            $max = 50;
            break;
        case 'checkbox':
            return ($raw === '1') ? '1' : '';
        default:
            $val = sanitize_text_field($raw);
            $max = 300;
    }

    // Cap length to block oversized / abusive payloads.
    if (function_exists('mb_substr')) {
        if (mb_strlen($val) > $max) {
            $val = mb_substr($val, 0, $max);
        }
    } elseif (strlen($val) > $max) {
        $val = substr($val, 0, $max);
    }

    return $val;
}

/** Client-hint attributes that improve mobile keyboards + semantics. */
function contact_inputmode(string $type): string
{
    if ($type === 'tel') {
        return ' inputmode="tel"';
    }
    if ($type === 'number') {
        return ' inputmode="decimal"';
    }
    if ($type === 'url') {
        return ' inputmode="url"';
    }
    return '';
}

/**
 * Handle a submission. Reads the field definition for the posting page, so the
 * validation and the emailed message always match whatever the form shows.
 */
add_action('init', function () {
    if (empty($_POST['rv_contact_submit'])) {
        return;
    }

    $redirect = wp_get_referer() ?: home_url('/');

    if (! isset($_POST['rv_contact_nonce']) ||
        ! wp_verify_nonce(sanitize_key($_POST['rv_contact_nonce']), 'rv_contact')) {
        wp_safe_redirect(add_query_arg('contact', 'error', $redirect));
        exit;
    }

    // Honeypot: real people leave this empty.
    if (! empty($_POST['rv_website'])) {
        wp_safe_redirect(add_query_arg('contact', 'spam', $redirect));
        exit;
    }

    // Time-trap: a signed render-time token blocks instant bot posts and forged
    // timestamps. Must be at least 2s old (humans take longer) and not stale.
    $tok     = isset($_POST['rv_t']) ? (string) wp_unslash($_POST['rv_t']) : '';
    $time_ok = false;
    if (strpos($tok, '.') !== false) {
        [$ts, $sig] = explode('.', $tok, 2);
        if (ctype_digit($ts) && hash_equals(wp_hash($ts), $sig)) {
            $elapsed = time() - (int) $ts;
            $time_ok = ($elapsed >= 2 && $elapsed <= DAY_IN_SECONDS);
        }
    }
    if (! $time_ok) {
        wp_safe_redirect(add_query_arg('contact', 'spam', $redirect));
        exit;
    }

    // Per-IP rate limit.
    $ip  = isset($_SERVER['REMOTE_ADDR']) ? sanitize_text_field(wp_unslash($_SERVER['REMOTE_ADDR'])) : 'x';
    $key = 'rv_contact_' . md5($ip);
    if (get_transient($key)) {
        wp_safe_redirect(add_query_arg('contact', 'spam', $redirect));
        exit;
    }

    $page_id = isset($_POST['rv_form_page']) ? absint($_POST['rv_form_page']) : 0;
    if ($page_id && ($perm = get_permalink($page_id))) {
        $redirect = $perm;
    }
    $fields  = contact_fields($page_id ?: null);

    $lines      = [];
    $reply_email = '';
    $reply_name  = '';

    foreach ($fields as $i => $f) {
        if (($f['type'] ?? '') === 'heading') {
            continue;
        }

        $raw   = $_POST['rvf_' . $i] ?? '';
        $value = contact_sanitize_value($f['type'], $raw);

        // Constrain dropdowns and radio groups to their offered options.
        if (in_array($f['type'], ['select', 'radio'], true) && $f['choices'] && ! in_array($value, $f['choices'], true)) {
            $value = '';
        }

        // Required-field validation.
        if ($f['required']) {
            if ($f['type'] === 'checkbox' && $value !== '1') {
                wp_safe_redirect(add_query_arg('contact', 'error', $redirect));
                exit;
            }
            if ($f['type'] !== 'checkbox' && $value === '') {
                wp_safe_redirect(add_query_arg('contact', 'error', $redirect));
                exit;
            }
        }

        // A filled email field must be a valid address.
        if ($f['type'] === 'email' && $value !== '' && ! is_email($value)) {
            wp_safe_redirect(add_query_arg('contact', 'error', $redirect));
            exit;
        }

        // Short text fields (names, subjects) shouldn't carry links — classic spam.
        if (in_array($f['type'], ['text', 'tel'], true) && $value !== ''
            && preg_match('~https?://|www\.~i', $value)) {
            wp_safe_redirect(add_query_arg('contact', 'spam', $redirect));
            exit;
        }

        // Capture reply-to details from the first email / name-ish fields.
        if ($f['type'] === 'email' && $reply_email === '' && is_email($value)) {
            $reply_email = $value;
        }
        if ($reply_name === '' && $f['type'] === 'text' && $value !== ''
            && contact_label_is_person_name($f['label'])) {
            $reply_name = contact_safe_display_name($value);
        }

        // Build the emailed message.
        if ($f['type'] === 'checkbox') {
            $lines[] = $f['label'] . ': ' . ($value === '1' ? __('Yes', 'sage') : __('No', 'sage'));
        } elseif ($value !== '') {
            $lines[] = $f['label'] . ': ' . $value;
        }
    }

    if (empty($lines)) {
        wp_safe_redirect(add_query_arg('contact', 'error', $redirect));
        exit;
    }

    $inquiry = contact_inquiry_package();
    if ($inquiry !== '') {
        array_unshift($lines, __('Package', 'sage') . ': ' . $inquiry);
    }

    // Consent / GDPR gate.
    if (field('cform_consent_enable', '0', $page_id) === '1'
        && field('cform_consent_required', '1', $page_id) === '1') {
        if (empty($_POST['rv_consent']) || $_POST['rv_consent'] !== '1') {
            wp_safe_redirect(add_query_arg('contact', 'error', $redirect));
            exit;
        }
    }
    if (! empty($_POST['rv_consent'])) {
        $lines[] = __('Consent given', 'sage') . ': ' . __('Yes', 'sage');
    }

    // Honor WordPress's disallowed-content list (Settings → Discussion).
    if (function_exists('wp_check_comment_disallowed_list')
        && wp_check_comment_disallowed_list($reply_name, $reply_email, '', implode("\n", $lines), $ip, '')) {
        wp_safe_redirect(add_query_arg('contact', 'spam', $redirect));
        exit;
    }

    // Belt-and-suspenders against email header injection.
    $reply_name  = contact_safe_display_name($reply_name);
    $reply_email = str_replace(["\r", "\n"], '', $reply_email);

    $to = contact_recipient_emails();
    /* translators: %s: site name. */
    $subject = sprintf(__('[%s] New quote request', 'sage'), wp_specialchars_decode(get_bloginfo('name')));
    if ($reply_name !== '') {
        $subject .= ' — ' . $reply_name;
    }
    if ($inquiry !== '') {
        $subject .= ' — ' . $inquiry;
    }
    $body = implode("\n", $lines) . "\n";
    if ($reply_email !== '') {
        $body .= "\n" . __('Visitor email (Reply-To)', 'sage') . ': ' . $reply_email . "\n";
    }

    $from_name    = $reply_name !== '' ? $reply_name : ($reply_email !== '' ? $reply_email : __('Website visitor', 'sage'));
    $from_address = contact_from_email_for_visitor($reply_email);
    $sent         = contact_mail(
        $to,
        $subject,
        $body,
        $from_address,
        $from_name,
        $reply_email,
        $from_name
    );

    if ($sent) {
        set_transient($key, 1, 30);
    }

    if ($sent && $reply_email !== '') {
        $studio = contact_studio_email();
        $ar_subject = field(
            'cform_ar_subject',
            __('Your quote request was sent — Ridges & Valleys Studio', 'sage'),
            $page_id
        );
        $custom_body = wp_strip_all_tags((string) field('cform_ar_body', '', $page_id));
        $ar_body     = $custom_body !== ''
            ? $custom_body . "\n\n" . __('Here’s a copy of what you sent:', 'sage') . "\n" . implode("\n", $lines)
            : contact_sender_confirmation_body($reply_name, $lines, $page_id);

        contact_mail(
            $reply_email,
            $ar_subject,
            trim($ar_body) . "\n",
            $studio,
            __('Matt Hummel', 'sage'),
            $studio,
            __('Ridges & Valleys Studio', 'sage')
        );
    }

    wp_safe_redirect(add_query_arg('contact', $sent ? 'success' : 'error', $redirect));
    exit;
});

/**
 * Render the contact form (echoes). Called from the Contact Blade template.
 * Fields and design come from the page's editor settings.
 */
function contact_form_success(?int $post_id = null): void
{
    $title = field('cnt_form_success_title', __('Your request was sent', 'sage'), $post_id);
    $lede  = field(
        'cnt_form_success',
        __('Thanks — it’s in Matt’s inbox. You’ll get a personal reply from Ridges & Valleys Studio, not a ticket.', 'sage'),
        $post_id
    );
    $when = field(
        'cnt_form_success_when',
        __('Expect a reply within one business day (Monday–Friday). If you wrote on a weekend or holiday, look for a note Monday morning.', 'sage'),
        $post_id
    );
    $note = field(
        'cnt_form_success_note',
        sprintf(__('Need to add something? Email %s from the address you used on the form.', 'sage'), contact_studio_email()),
        $post_id
    );

    echo '<div class="rv-form-sent" id="rv-form-sent" tabindex="-1" role="status">';
    echo '<p class="rv-form-sent-kicker">' . esc_html__('Confirmation', 'sage') . '</p>';
    echo '<h2 class="rv-form-sent-title">' . esc_html($title) . '</h2>';
    echo '<p class="rv-form-sent-lede">' . esc_html($lede) . '</p>';
    echo '<p class="rv-form-sent-when">' . esc_html($when) . '</p>';
    echo '<h3 class="rv-form-sent-next">' . esc_html__('What to expect next', 'sage') . '</h3>';
    echo '<ol class="rv-form-sent-steps">';
    foreach (contact_success_steps($post_id) as $step) {
        $strong = strip_field_markers(trim((string) ($step['strong'] ?? '')));
        $text   = strip_field_markers(trim((string) ($step['text'] ?? '')));
        if ($strong === '' && $text === '') {
            continue;
        }
        echo '<li><strong>' . esc_html($strong) . '</strong> ' . esc_html($text) . '</li>';
    }
    echo '</ol>';
    echo '<p class="rv-form-sent-note">' . esc_html($note) . '</p>';
    echo '</div>';
    echo '<script>requestAnimationFrame(function(){var el=document.getElementById("rv-form-sent");if(!el)return;el.focus({preventScroll:true});el.scrollIntoView({behavior:"smooth",block:"start"});});</script>';
}

/**
 * Render the contact form (echoes). Called from the Contact Blade template.
 * Fields and design come from the page's editor settings.
 */
function contact_form(?int $post_id = null): void
{
    $post_id = $post_id ?: (int) get_the_ID();
    $fields  = contact_fields($post_id ?: null);

    // Design options (Page content → “Contact form — design”).
    // strip_field_markers() removes the live-preview click-to-edit markers that
    // field() adds; without it these logic comparisons never match in the preview.
    $btn_label   = field('cnt_form_btn', __('Send my request', 'sage'), $post_id) ?: __('Send my request', 'sage');
    $btn_full    = true;
    $btn_align   = 'left';
    $btn_icon    = 'send';
    $btn_icon_pos = 'before';
    $label_style = strip_field_markers((string) field('cform_label_style', 'top', $post_id));
    $field_style = strip_field_markers((string) field('cform_field_style', 'box', $post_id));

    $nolabels = ($label_style === 'hidden');
    if (! in_array($field_style, ['box', 'soft', 'underline'], true)) {
        $field_style = 'box';
    }

    $status = isset($_GET['contact']) ? sanitize_key(wp_unslash($_GET['contact'])) : ''; // phpcs:ignore
    $inquiry = contact_inquiry_package();

    if ($status === 'success') {
        contact_form_success($post_id);
        return;
    }
    if ($status === 'error') {
        echo '<div class="rv-note rv-note-err" role="alert">' . esc_html__('Something went wrong. Please check the fields and try again.', 'sage') . '</div>';
    } elseif ($status === 'spam') {
        echo '<div class="rv-note rv-note-err" role="alert">' . esc_html__('Please wait a moment before sending again.', 'sage') . '</div>';
    } elseif ($inquiry !== '') {
        echo '<p class="rv-form-pkg" role="status">' . sprintf(
            /* translators: %s: package name, e.g. Local Launch */
            esc_html__('Inquiring about %s — add a few details and I’ll send a fixed-scope quote.', 'sage'),
            '<strong>' . esc_html($inquiry) . '</strong>'
        ) . '</p>';
    }

    $form_class = 'rv-form rv-form--dynamic rv-form--' . $field_style . ($nolabels ? ' rv-form--nolabels' : '');

    printf(
        '<form class="%s" method="post" action="%s" aria-label="%s" novalidate>',
        esc_attr($form_class),
        esc_url(home_url(add_query_arg([]))),
        esc_attr__('Quote request', 'sage')
    );
    wp_nonce_field('rv_contact', 'rv_contact_nonce');
    printf('<input type="hidden" name="rv_form_page" value="%d">', (int) $post_id);
    if ($inquiry !== '') {
        printf('<input type="hidden" name="rv_package" value="%s">', esc_attr($inquiry));
    }
    // Signed render-time token for the server-side time-trap.
    $t = time();
    printf('<input type="hidden" name="rv_t" value="%s">', esc_attr($t . '.' . wp_hash((string) $t)));

    $prefilled_message = false;
    $inquiry_slug = sanitize_title($inquiry);
    $has_pkg_select = false;
    if ($inquiry_slug !== '') {
        foreach ($fields as $cf) {
            if (($cf['type'] ?? '') !== 'select') {
                continue;
            }
            foreach ($cf['choices'] as $opt) {
                if (sanitize_title((string) $opt) === $inquiry_slug) {
                    $has_pkg_select = true;
                    break 2;
                }
            }
        }
    }
    foreach ($fields as $i => $f) {
        $id    = 'rvf_' . $i;
        $req   = $f['required'];
        $ph    = $f['placeholder'];
        if ($nolabels && $ph === '') {
            $ph = $f['label'];
        }
        $reqAttr  = $req ? ' required' : '';
        $reqMark  = $req ? ' <span class="rv-req" aria-hidden="true">*</span>' : '';
        $reqAria  = $req ? ' aria-required="true"' : '';
        $auto     = contact_autocomplete($f);

        if ($f['type'] === 'heading') {
            echo '<div class="rv-ff rv-ff--full rv-ff-heading">';
            echo '<h3>' . esc_html($f['label']) . '</h3>';
            echo '</div>';
            continue;
        }

        if ($f['type'] === 'radio') {
            $legend_id = $id . '-legend';
            echo '<div class="rv-ff rv-ff--' . esc_attr($f['width']) . ' rv-ff-radio-wrap">';
            printf('<span class="rv-ff-legend" id="%s">%s%s</span>', esc_attr($legend_id), esc_html($f['label']), $reqMark); // phpcs:ignore
            echo '<div class="rv-ff-radios" role="radiogroup" aria-labelledby="' . esc_attr($legend_id) . '"' . ($req ? ' aria-required="true"' : '') . '>';
            $inquiry_slug = sanitize_title($inquiry);
            $first = true;
            foreach ($f['choices'] as $opt) {
                $sel = ($inquiry_slug !== '' && (sanitize_title((string) $opt) === $inquiry_slug || strcasecmp((string) $opt, $inquiry) === 0));
                printf(
                    '<label class="rv-ff-radio"><input type="radio" name="%1$s" value="%2$s"%3$s%4$s> <span>%5$s</span></label>',
                    esc_attr($id),
                    esc_attr($opt),
                    $sel ? ' checked' : '',
                    ($first && $req) ? ' required' : '',
                    esc_html($opt)
                );
                $first = false;
            }
            echo '</div></div>';
            continue;
        }

        // Checkbox renders as an inline control with its label beside it.
        if ($f['type'] === 'checkbox') {
            echo '<div class="rv-ff rv-ff--' . esc_attr($f['width']) . ' rv-ff-check">';
            printf(
                '<label class="rv-ff-checkline"><input type="checkbox" id="%1$s" name="%1$s" value="1"%2$s> <span>%3$s%4$s</span></label>',
                esc_attr($id),
                $reqAttr . $reqAria,
                esc_html($f['label']),
                $reqMark // phpcs:ignore
            );
            echo '</div>';
            continue;
        }

        echo '<div class="rv-ff rv-ff--' . esc_attr($f['width']) . '">';
        printf('<label for="%s">%s%s</label>', esc_attr($id), esc_html($f['label']), $reqMark); // phpcs:ignore

        switch ($f['type']) {
            case 'textarea':
                $ta_val = '';
                if ($inquiry !== '' && $status !== 'success' && ! $prefilled_message && ! $has_pkg_select) {
                    $ta_val = sprintf(__("I'm interested in %s.", 'sage'), $inquiry);
                    $prefilled_message = true;
                }
                printf(
                    '<textarea id="%1$s" name="%1$s" rows="6"%2$s placeholder="%3$s">%4$s</textarea>',
                    esc_attr($id),
                    $reqAttr . $reqAria,
                    esc_attr($ph),
                    esc_textarea($ta_val)
                );
                break;
            case 'select':
                printf('<select id="%1$s" name="%1$s"%2$s>', esc_attr($id), $reqAttr . $reqAria);
                printf('<option value="">%s</option>', esc_html($ph !== '' ? $ph : __('Choose…', 'sage')));
                $inquiry_slug = sanitize_title($inquiry);
                foreach ($f['choices'] as $opt) {
                    $sel = ($inquiry_slug !== '' && (sanitize_title((string) $opt) === $inquiry_slug || strcasecmp((string) $opt, $inquiry) === 0));
                    printf('<option value="%1$s"%2$s>%1$s</option>', esc_attr($opt), selected($sel, true, false));
                }
                echo '</select>';
                break;
            default:
                printf(
                    '<input type="%1$s" id="%2$s" name="%2$s"%3$s%4$s%5$s placeholder="%6$s">',
                    esc_attr($f['type']),
                    esc_attr($id),
                    $reqAttr . $reqAria,
                    $auto,
                    contact_inputmode($f['type']),
                    esc_attr($ph)
                );
        }

        echo '</div>';
    }

    // Consent / GDPR checkbox (optional, sits just above the submit button).
    if (strip_field_markers((string) field('cform_consent_enable', '0', $post_id)) === '1') {
        $consent_req  = strip_field_markers((string) field('cform_consent_required', '1', $post_id)) === '1';
        $consent_text = field('cform_consent_text', '', $post_id);
        echo '<div class="rv-ff rv-ff--full rv-ff-check rv-ff-consent">';
        printf(
            '<label class="rv-ff-checkline"><input type="checkbox" name="rv_consent" value="1"%1$s> <span>%2$s%3$s</span></label>',
            $consent_req ? ' required aria-required="true"' : '',
            wp_kses_post($consent_text), // phpcs:ignore
            $consent_req ? ' <span class="rv-req" aria-hidden="true">*</span>' : ''
        );
        echo '</div>';
    }

    // Honeypot (kept out of the visual + a11y flow).
    echo '<p class="rv-hp" aria-hidden="true">';
    echo '<label for="rv_website">' . esc_html__('Leave this field empty', 'sage') . '</label>';
    echo '<input type="text" id="rv_website" name="rv_website" tabindex="-1" autocomplete="off"></p>';

    // Optional send-related icon, placed before or after the label.
    $icon_svg = $btn_icon !== '' ? icon($btn_icon) : '';
    $btn_inner = esc_html($btn_label);
    if ($icon_svg !== '') {
        $btn_inner = ($btn_icon_pos === 'after')
            ? '<span>' . esc_html($btn_label) . '</span>' . $icon_svg
            : $icon_svg . '<span>' . esc_html($btn_label) . '</span>';
    }

    printf('<div class="rv-ff rv-ff--full rv-ff-actions rv-ff-actions--%s">', esc_attr($btn_align));
    printf(
        '<button type="submit" name="rv_contact_submit" value="1" class="rv-btn rv-btn-primary%s">%s</button>',
        $btn_full ? ' rv-ff-btn--full' : '',
        $btn_inner // phpcs:ignore -- icon() is a trusted whitelisted SVG; label is escaped above
    );
    echo '</div>';

    echo '</form>';
}

/** Sensible autocomplete hints for common field types. */
function contact_autocomplete(array $f): string
{
    if ($f['type'] === 'email') {
        return ' autocomplete="email"';
    }
    if ($f['type'] === 'tel') {
        return ' autocomplete="tel"';
    }
    if ($f['type'] === 'text' && stripos($f['label'], 'name') !== false) {
        if (stripos($f['label'], 'business') !== false) {
            return ' autocomplete="organization"';
        }
        return ' autocomplete="name"';
    }
    if ($f['type'] === 'text' && (stripos($f['label'], 'town') !== false || stripos($f['label'], 'city') !== false)) {
        return ' autocomplete="address-level2"';
    }
    return '';
}
