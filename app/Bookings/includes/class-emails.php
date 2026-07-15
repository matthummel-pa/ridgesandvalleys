<?php

/**
 * Booking emails + ICS calendar files.
 *
 * Plain-text wp_mail, mirroring app/contact.php's conventions ([site name]
 * subject prefix, Reply-To header). The customer confirmation carries an
 * .ics attachment so "add to calendar" works in every mail client — the
 * one attachment-worthy feature all the established booking apps share.
 */

namespace PrtBookings;

defined('ABSPATH') || exit;

class Emails
{
    public function hooks(): void
    {
        add_action('pressroot/booking_created', [$this, 'on_created'], 10, 2);
        add_action('pressroot/booking_status_changed', [$this, 'on_status'], 10, 3);
    }

    protected static function notify_to(): string
    {
        $opt = Settings::get()['notify_email'];
        return $opt && is_email($opt) ? $opt : get_option('admin_email');
    }

    protected static function when_line(array $booking): string
    {
        $tz = wp_timezone();
        return wp_date(get_option('date_format') . ' ' . get_option('time_format'), $booking['start'], $tz)
            . ' (' . wp_timezone_string() . ')';
    }

    protected static function cancel_url(array $booking): string
    {
        return add_query_arg(['prt_bk' => 'cancel', 'bk_token' => rawurlencode($booking['token'])], home_url('/'));
    }

    /* ── Hooks ────────────────────────────────────────────────────────── */

    public function on_created(array $booking, array $service): void
    {
        $site    = get_bloginfo('name');
        $when    = self::when_line($booking);
        $pending = $booking['status'] === 'pending';
        $extra   = Settings::get()['success_text'];

        // Customer email.
        if ($booking['email'] && is_email($booking['email'])) {
            $subject = $pending
                ? sprintf('[%s] %s', $site, __('Booking request received', 'pressroot'))
                : sprintf('[%s] %s', $site, __('Booking confirmed', 'pressroot'));

            $lines   = [];
            $lines[] = sprintf(__('Hi %s,', 'pressroot'), $booking['name']);
            $lines[] = '';
            $lines[] = $pending
                ? __('We received your booking request — you will get another email once it is confirmed.', 'pressroot')
                : __('Your booking is confirmed. Details below:', 'pressroot');
            $lines[] = '';
            $lines[] = sprintf('%s: %s', __('Service', 'pressroot'), $service['title']);
            $lines[] = sprintf('%s: %s', __('When', 'pressroot'), $when);
            if ($service['capacity'] > 1) {
                $lines[] = sprintf('%s: %d', __('Party size', 'pressroot'), $booking['party']);
            }
            if ($service['price'] !== '') {
                $lines[] = sprintf('%s: %s', __('Price', 'pressroot'), $service['price']);
            }
            $lines[] = '';
            $lines[] = __('Need to cancel?', 'pressroot') . ' ' . self::cancel_url($booking);
            if ($extra !== '') {
                $lines[] = '';
                $lines[] = $extra;
            }
            $lines[] = '';
            $lines[] = sprintf('— %s', $site);

            $attachments = [];
            if (! $pending) {
                $ics = self::write_ics_file($booking, $service);
                if ($ics) {
                    $attachments[] = $ics;
                }
            }
            wp_mail($booking['email'], $subject, implode("\n", $lines), [], $attachments);
            if ($attachments) {
                @unlink($attachments[0]); // phpcs:ignore -- temp file cleanup
            }
        }

        // Owner notification.
        $subject = sprintf(
            '[%s] %s: %s — %s',
            $site,
            $pending ? __('New booking request', 'pressroot') : __('New booking', 'pressroot'),
            $service['title'],
            $when
        );
        $body = implode("\n", array_filter([
            sprintf('%s: %s', __('Service', 'pressroot'), $service['title']),
            sprintf('%s: %s', __('When', 'pressroot'), $when),
            sprintf('%s: %s', __('Name', 'pressroot'), $booking['name']),
            sprintf('%s: %s', __('Email', 'pressroot'), $booking['email']),
            $booking['phone'] !== '' ? sprintf('%s: %s', __('Phone', 'pressroot'), $booking['phone']) : '',
            $service['capacity'] > 1 ? sprintf('%s: %d', __('Party size', 'pressroot'), $booking['party']) : '',
            $booking['notes'] !== '' ? sprintf('%s: %s', __('Notes', 'pressroot'), $booking['notes']) : '',
            '',
            sprintf(__('Manage: %s', 'pressroot'), admin_url('edit.php?post_type=prt_booking')),
        ]));
        wp_mail(self::notify_to(), $subject, $body, ['Reply-To: ' . $booking['name'] . ' <' . $booking['email'] . '>']);
    }

    public function on_status(array $booking, string $old, string $new): void
    {
        $service = Services::get($booking['service']);
        if (! $service || ! $booking['email'] || ! is_email($booking['email'])) {
            return;
        }
        $site = get_bloginfo('name');
        $when = self::when_line($booking);

        if ($new === 'confirmed' && $old === 'pending') {
            $ics   = self::write_ics_file($booking, $service);
            $lines = [
                sprintf(__('Hi %s,', 'pressroot'), $booking['name']),
                '',
                __('Your booking is confirmed. Details below:', 'pressroot'),
                '',
                sprintf('%s: %s', __('Service', 'pressroot'), $service['title']),
                sprintf('%s: %s', __('When', 'pressroot'), $when),
                '',
                __('Need to cancel?', 'pressroot') . ' ' . self::cancel_url($booking),
                '',
                sprintf('— %s', $site),
            ];
            wp_mail($booking['email'], sprintf('[%s] %s', $site, __('Booking confirmed', 'pressroot')), implode("\n", $lines), [], $ics ? [$ics] : []);
            if ($ics) {
                @unlink($ics); // phpcs:ignore -- temp file cleanup
            }
        }

        if ($new === 'cancelled') {
            wp_mail(
                $booking['email'],
                sprintf('[%s] %s', $site, __('Booking cancelled', 'pressroot')),
                sprintf(__('Your booking for %1$s on %2$s has been cancelled.', 'pressroot'), $service['title'], $when) . "\n\n— " . $site
            );
            // Tell the owner too (unless they cancelled it themselves in wp-admin).
            if (! is_admin()) {
                wp_mail(
                    self::notify_to(),
                    sprintf('[%s] %s: %s — %s', $site, __('Booking cancelled by customer', 'pressroot'), $service['title'], $when),
                    sprintf('%s (%s)', $booking['name'], $booking['email'])
                );
            }
        }
    }

    /* ── ICS ──────────────────────────────────────────────────────────── */

    /** The .ics text for a booking (UTC times — universally portable). */
    public static function ics(array $booking, array $service): string
    {
        $fmt = fn (int $ts) => gmdate('Ymd\THis\Z', $ts);
        $esc = fn (string $s) => addcslashes($s, ",;\\");
        $out = [
            'BEGIN:VCALENDAR',
            'VERSION:2.0',
            'PRODID:-//Pressroots Reserve//EN',
            'METHOD:PUBLISH',
            'BEGIN:VEVENT',
            'UID:prt-booking-' . $booking['id'] . '@' . wp_parse_url(home_url(), PHP_URL_HOST),
            'DTSTAMP:' . $fmt(time()),
            'DTSTART:' . $fmt($booking['start']),
            'DTEND:' . $fmt($booking['end']),
            'SUMMARY:' . $esc($service['title'] . ' — ' . get_bloginfo('name')),
            'DESCRIPTION:' . $esc(sprintf(__('Booking for %s', 'pressroot'), $booking['name'])),
            'STATUS:CONFIRMED',
            'END:VEVENT',
            'END:VCALENDAR',
        ];
        return implode("\r\n", $out) . "\r\n";
    }

    /** Write the ICS to a temp file for use as a wp_mail attachment. */
    protected static function write_ics_file(array $booking, array $service): ?string
    {
        $path = get_temp_dir() . 'prt-booking-' . $booking['id'] . '.ics';
        return file_put_contents($path, self::ics($booking, $service)) !== false ? $path : null; // phpcs:ignore
    }
}
