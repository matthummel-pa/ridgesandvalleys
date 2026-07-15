<?php

/**
 * Bookings settings: one option array (schedule, notice, window, blackouts,
 * confirmation behavior, notification recipient) + the settings-tab screen.
 * Saved through admin-post with capability + nonce checks, same as every
 * other settings surface in the theme.
 */

namespace PrtBookings;

defined('ABSPATH') || exit;

class Settings
{
    public const OPTION_KEY = 'prt_bookings_options';

    /** Weekday keys in wp order (0 = Sunday) — used by schedule + engine. */
    public const DAYS = ['sun', 'mon', 'tue', 'wed', 'thu', 'fri', 'sat'];

    public static function defaults(): array
    {
        return [
            // Weekly schedule: per-day enabled flag + one open/close window.
            'schedule' => [
                'sun' => ['on' => false, 'start' => '09:00', 'end' => '17:00'],
                'mon' => ['on' => true,  'start' => '09:00', 'end' => '17:00'],
                'tue' => ['on' => true,  'start' => '09:00', 'end' => '17:00'],
                'wed' => ['on' => true,  'start' => '09:00', 'end' => '17:00'],
                'thu' => ['on' => true,  'start' => '09:00', 'end' => '17:00'],
                'fri' => ['on' => true,  'start' => '09:00', 'end' => '17:00'],
                'sat' => ['on' => false, 'start' => '09:00', 'end' => '17:00'],
            ],
            'slot_step'     => 0,      // minutes between slot starts; 0 = service duration + buffer
            'notice_hours'  => 12,     // minimum advance notice
            'window_days'   => 30,     // how far ahead customers can book
            'blackouts'     => '',     // comma/newline separated Y-m-d dates
            'auto_confirm'  => true,   // false = bookings arrive as "pending"
            'notify_email'  => '',     // '' = admin_email
            'success_text'  => '',     // extra line on the confirmation panel/email
        ];
    }

    public static function get(): array
    {
        $opts = get_option(self::OPTION_KEY, []);
        $opts = is_array($opts) ? $opts : [];
        $out  = array_merge(self::defaults(), $opts);
        $out['schedule'] = array_merge(self::defaults()['schedule'], is_array($opts['schedule'] ?? null) ? $opts['schedule'] : []);
        return $out;
    }

    public function hooks(): void
    {
        add_action('admin_post_prt_bookings_save', [$this, 'save']);
    }

    /** admin-post handler: sanitize the whole option array and persist. */
    public function save(): void
    {
        if (! current_user_can('edit_theme_options') || ! check_admin_referer('prt_bookings_save')) {
            wp_die(esc_html__('Not allowed.', 'pressroot'));
        }

        $in  = wp_unslash($_POST);
        $out = self::defaults();

        foreach (self::DAYS as $day) {
            $out['schedule'][$day] = [
                'on'    => ! empty($in['sched'][$day]['on']),
                'start' => self::sanitize_time($in['sched'][$day]['start'] ?? '09:00'),
                'end'   => self::sanitize_time($in['sched'][$day]['end'] ?? '17:00'),
            ];
        }
        $out['slot_step']    = max(0, min(240, absint($in['slot_step'] ?? 0)));
        $out['notice_hours'] = max(0, min(720, absint($in['notice_hours'] ?? 12)));
        $out['window_days']  = max(1, min(365, absint($in['window_days'] ?? 30)));
        $out['auto_confirm'] = ! empty($in['auto_confirm']);
        $out['notify_email'] = sanitize_email($in['notify_email'] ?? '');
        $out['success_text'] = sanitize_textarea_field($in['success_text'] ?? '');

        // Blackout dates: keep only valid Y-m-d entries.
        $dates = preg_split('/[\s,]+/', (string) ($in['blackouts'] ?? ''));
        $valid = array_filter(array_map('trim', $dates), function ($d) {
            return (bool) preg_match('/^\d{4}-\d{2}-\d{2}$/', $d) && wp_checkdate((int) substr($d, 5, 2), (int) substr($d, 8, 2), (int) substr($d, 0, 4), $d);
        });
        $out['blackouts'] = implode(', ', $valid);

        update_option(self::OPTION_KEY, $out, false);
        wp_safe_redirect(add_query_arg('prt_bk_saved', '1', wp_get_referer() ?: admin_url('themes.php?page=prt-settings&tab=bookings')));
        exit;
    }

    protected static function sanitize_time(string $t): string
    {
        return preg_match('/^([01]\d|2[0-3]):[0-5]\d$/', $t) ? $t : '09:00';
    }

    /** The Bookings tab body (chrome-less — the settings page owns the frame). */
    public static function render(): void
    {
        $o = self::get();
        if (isset($_GET['prt_bk_saved'])) {
            echo '<div class="notice notice-success is-dismissible"><p>' . esc_html__('Booking settings saved.', 'pressroot') . '</p></div>';
        }
        $dayLabels = [
            'sun' => __('Sunday', 'pressroot'), 'mon' => __('Monday', 'pressroot'),
            'tue' => __('Tuesday', 'pressroot'), 'wed' => __('Wednesday', 'pressroot'),
            'thu' => __('Thursday', 'pressroot'), 'fri' => __('Friday', 'pressroot'),
            'sat' => __('Saturday', 'pressroot'),
        ];
        ?>
        <h2 style="margin-top:0"><?php esc_html_e('Pressroots Reserve — bookings & reservations', 'pressroot'); ?></h2>
        <p class="description" style="max-width:680px">
            <?php
            printf(
                wp_kses(__('Create services under <a href="%1$s">Bookings → Services</a>, then drop the <strong>Booking form</strong> block (or the <code>[prt_booking]</code> shortcode) on any page. Bookings arrive under <a href="%2$s">Bookings</a>. Times use the site timezone (%3$s).', 'pressroot'), ['a' => ['href' => []], 'strong' => [], 'code' => []]),
                esc_url(admin_url('edit.php?post_type=prt_service')),
                esc_url(admin_url('edit.php?post_type=prt_booking')),
                esc_html(wp_timezone_string())
            );
            ?>
        </p>

        <form method="post" action="<?php echo esc_url(admin_url('admin-post.php')); ?>">
            <input type="hidden" name="action" value="prt_bookings_save">
            <?php wp_nonce_field('prt_bookings_save'); ?>

            <h3><?php esc_html_e('Weekly availability', 'pressroot'); ?></h3>
            <table class="form-table" role="presentation" style="max-width:560px">
                <?php foreach (self::DAYS as $day) : $d = $o['schedule'][$day]; ?>
                <tr>
                    <th scope="row" style="padding:6px 10px 6px 0">
                        <label><input type="checkbox" name="sched[<?php echo esc_attr($day); ?>][on]" value="1" <?php checked($d['on']); ?>> <?php echo esc_html($dayLabels[$day]); ?></label>
                    </th>
                    <td style="padding:6px 0">
                        <input type="time" name="sched[<?php echo esc_attr($day); ?>][start]" value="<?php echo esc_attr($d['start']); ?>">
                        —
                        <input type="time" name="sched[<?php echo esc_attr($day); ?>][end]" value="<?php echo esc_attr($d['end']); ?>">
                    </td>
                </tr>
                <?php endforeach; ?>
            </table>

            <h3><?php esc_html_e('Booking rules', 'pressroot'); ?></h3>
            <table class="form-table" role="presentation" style="max-width:680px">
                <tr>
                    <th scope="row"><label for="prt_bk_notice"><?php esc_html_e('Minimum notice (hours)', 'pressroot'); ?></label></th>
                    <td><input id="prt_bk_notice" type="number" name="notice_hours" min="0" max="720" value="<?php echo esc_attr($o['notice_hours']); ?>" class="small-text">
                        <p class="description"><?php esc_html_e('How soon before a slot customers can no longer book it.', 'pressroot'); ?></p></td>
                </tr>
                <tr>
                    <th scope="row"><label for="prt_bk_window"><?php esc_html_e('Booking window (days)', 'pressroot'); ?></label></th>
                    <td><input id="prt_bk_window" type="number" name="window_days" min="1" max="365" value="<?php echo esc_attr($o['window_days']); ?>" class="small-text">
                        <p class="description"><?php esc_html_e('How far into the future customers can book.', 'pressroot'); ?></p></td>
                </tr>
                <tr>
                    <th scope="row"><label for="prt_bk_step"><?php esc_html_e('Slot interval (minutes)', 'pressroot'); ?></label></th>
                    <td><input id="prt_bk_step" type="number" name="slot_step" min="0" max="240" step="5" value="<?php echo esc_attr($o['slot_step']); ?>" class="small-text">
                        <p class="description"><?php esc_html_e('Gap between slot start times. 0 = service duration + buffer (back-to-back).', 'pressroot'); ?></p></td>
                </tr>
                <tr>
                    <th scope="row"><label for="prt_bk_blackouts"><?php esc_html_e('Blackout dates', 'pressroot'); ?></label></th>
                    <td><textarea id="prt_bk_blackouts" name="blackouts" rows="2" class="large-text" placeholder="2026-12-25, 2026-12-26"><?php echo esc_textarea($o['blackouts']); ?></textarea>
                        <p class="description"><?php esc_html_e('Dates (YYYY-MM-DD, comma separated) with no bookings — holidays, closures.', 'pressroot'); ?></p></td>
                </tr>
                <tr>
                    <th scope="row"><?php esc_html_e('Confirmation', 'pressroot'); ?></th>
                    <td><label><input type="checkbox" name="auto_confirm" value="1" <?php checked($o['auto_confirm']); ?>> <?php esc_html_e('Auto-confirm new bookings', 'pressroot'); ?></label>
                        <p class="description"><?php esc_html_e('Unchecked: bookings arrive as “pending” and you confirm each one (a confirmation email goes out when you do).', 'pressroot'); ?></p></td>
                </tr>
                <tr>
                    <th scope="row"><label for="prt_bk_notify"><?php esc_html_e('Notification email', 'pressroot'); ?></label></th>
                    <td><input id="prt_bk_notify" type="email" name="notify_email" value="<?php echo esc_attr($o['notify_email']); ?>" class="regular-text" placeholder="<?php echo esc_attr(get_option('admin_email')); ?>">
                        <p class="description"><?php esc_html_e('Where new-booking notifications go. Empty = site admin email.', 'pressroot'); ?></p></td>
                </tr>
                <tr>
                    <th scope="row"><label for="prt_bk_success"><?php esc_html_e('Extra confirmation note', 'pressroot'); ?></label></th>
                    <td><textarea id="prt_bk_success" name="success_text" rows="2" class="large-text" placeholder="<?php esc_attr_e('e.g. Parking is behind the building — ring the bell at the side door.', 'pressroot'); ?>"><?php echo esc_textarea($o['success_text']); ?></textarea>
                        <p class="description"><?php esc_html_e('Shown on the success screen and appended to confirmation emails.', 'pressroot'); ?></p></td>
                </tr>
            </table>

            <p><button type="submit" class="button button-primary"><?php esc_html_e('Save booking settings', 'pressroot'); ?></button></p>
        </form>
        <?php
    }
}
