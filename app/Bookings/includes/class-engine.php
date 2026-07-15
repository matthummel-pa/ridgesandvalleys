<?php

/**
 * The booking engine: bookings CPT, the slot calculator, and the two
 * state-changing operations (create, cancel).
 *
 * Time model: everything is computed in the SITE timezone (wp_timezone())
 * and stored as UTC unix timestamps in post meta, so DST transitions and
 * timezone edits can't corrupt stored bookings. A slot is identified by its
 * start timestamp.
 *
 * Double-booking guard: slot lists subtract booked seats from capacity, and
 * create() re-counts inside the insert path immediately before publishing —
 * the recheck window is milliseconds instead of the whole time the visitor
 * spends filling the form (the same optimistic strategy the big booking
 * apps use; a true lock isn't available on vanilla WP storage).
 */

namespace PrtBookings;

defined('ABSPATH') || exit;

class Engine
{
    public const CPT = 'prt_booking';

    /** Booking lifecycle states (stored in _prt_bk_status meta). */
    public const STATUSES = ['pending', 'confirmed', 'cancelled'];

    public function hooks(): void
    {
        add_action('init', [$this, 'register']);
    }

    public function register(): void
    {
        register_post_type(self::CPT, [
            'labels' => [
                'name'          => __('Bookings', 'pressroot'),
                'singular_name' => __('Booking', 'pressroot'),
                'menu_name'     => __('Bookings', 'pressroot'),
                'add_new_item'  => __('Add Booking', 'pressroot'),
                'edit_item'     => __('Edit Booking', 'pressroot'),
            ],
            'public'              => false,
            'show_ui'             => true,
            'show_in_rest'        => false,
            'supports'            => ['title'],
            'menu_icon'           => 'dashicons-calendar-alt',
            'menu_position'       => 26,
            'exclude_from_search' => true,
            'capabilities'        => ['create_posts' => 'edit_theme_options'],
            'map_meta_cap'        => true,
        ]);
    }

    /* ── Slot calculation ─────────────────────────────────────────────── */

    /**
     * Bookable days inside the window: [ 'Y-m-d' => bool hasAvailability ].
     * Cheap pass used by the widget's date strip — per-day open/closed from
     * the weekly schedule and blackouts (slot-level fullness is resolved
     * when a day is selected).
     */
    public static function days(array $service): array
    {
        $o    = Settings::get();
        $tz   = wp_timezone();
        $now  = new \DateTimeImmutable('now', $tz);
        $out  = [];
        $blackouts = array_map('trim', preg_split('/[\s,]+/', $o['blackouts']) ?: []);

        for ($i = 0; $i <= $o['window_days']; $i++) {
            $day  = $now->add(new \DateInterval("P{$i}D"));
            $key  = $day->format('Y-m-d');
            $dow  = Settings::DAYS[(int) $day->format('w')];
            $open = ! empty($o['schedule'][$dow]['on']) && ! in_array($key, $blackouts, true);
            $out[$key] = $open;
        }
        return $out;
    }

    /**
     * Available slots for a service on one date:
     * [ ['start' => ts, 'label' => '9:00 AM', 'left' => seats], ... ]
     */
    public static function slots(array $service, string $date): array
    {
        if (! preg_match('/^\d{4}-\d{2}-\d{2}$/', $date)) {
            return [];
        }
        $o  = Settings::get();
        $tz = wp_timezone();

        $blackouts = array_map('trim', preg_split('/[\s,]+/', $o['blackouts']) ?: []);
        if (in_array($date, $blackouts, true)) {
            return [];
        }

        try {
            $dayStart = new \DateTimeImmutable($date . ' 00:00:00', $tz);
        } catch (\Exception $e) {
            return [];
        }

        // Window bounds + weekly schedule for this weekday.
        $now      = new \DateTimeImmutable('now', $tz);
        $earliest = $now->add(new \DateInterval('PT' . ($o['notice_hours'] * 60) . 'M'));
        $latest   = $now->add(new \DateInterval('P' . $o['window_days'] . 'D'))->setTime(23, 59);
        $dow      = Settings::DAYS[(int) $dayStart->format('w')];
        $sched    = $o['schedule'][$dow];
        if (empty($sched['on'])) {
            return [];
        }

        [$sh, $sm] = array_map('intval', explode(':', $sched['start']));
        [$eh, $em] = array_map('intval', explode(':', $sched['end']));
        $open  = $dayStart->setTime($sh, $sm);
        $close = $dayStart->setTime($eh, $em);

        $step = $o['slot_step'] > 0 ? $o['slot_step'] : ($service['duration'] + $service['buffer']);
        $len  = new \DateInterval('PT' . $service['duration'] . 'M');
        $out  = [];

        for ($t = $open; $t->add($len) <= $close; $t = $t->add(new \DateInterval('PT' . $step . 'M'))) {
            if ($t < $earliest || $t > $latest) {
                continue;
            }
            $left = $service['capacity'] - self::booked_seats($service['id'], $t->getTimestamp());
            if ($left > 0) {
                $out[] = [
                    'start' => $t->getTimestamp(),
                    'label' => wp_date(get_option('time_format') ?: 'g:i a', $t->getTimestamp(), $tz),
                    'left'  => $left,
                ];
            }
        }
        return $out;
    }

    /** Seats already taken (pending + confirmed both hold the seat). */
    public static function booked_seats(int $service_id, int $start_ts): int
    {
        $ids = get_posts([
            'post_type'      => self::CPT,
            'post_status'    => 'publish',
            'posts_per_page' => 200,
            'fields'         => 'ids',
            'no_found_rows'  => true,
            'meta_query'     => [
                ['key' => '_prt_bk_service', 'value' => $service_id],
                ['key' => '_prt_bk_start', 'value' => $start_ts],
                ['key' => '_prt_bk_status', 'value' => ['pending', 'confirmed'], 'compare' => 'IN'],
            ],
        ]);
        $seats = 0;
        foreach ($ids as $id) {
            $seats += max(1, (int) get_post_meta($id, '_prt_bk_party', true));
        }
        return $seats;
    }

    /* ── Create / cancel ──────────────────────────────────────────────── */

    /**
     * Create a booking. $data: service (id), start (ts), name, email,
     * phone, party, notes. Returns the booking array or a WP_Error.
     */
    public static function create(array $data)
    {
        $service = Services::get((int) ($data['service'] ?? 0));
        if (! $service) {
            return new \WP_Error('prt_bk_service', __('That service is no longer available.', 'pressroot'));
        }

        $start = (int) ($data['start'] ?? 0);
        $party = max(1, min(500, (int) ($data['party'] ?? 1)));

        // The requested slot must still be one the engine would offer —
        // never trust a client-supplied timestamp on its own.
        $date  = wp_date('Y-m-d', $start, wp_timezone());
        $valid = null;
        foreach (self::slots($service, $date) as $slot) {
            if ($slot['start'] === $start) {
                $valid = $slot;
                break;
            }
        }
        if (! $valid) {
            return new \WP_Error('prt_bk_slot', __('That time was just taken — please pick another slot.', 'pressroot'));
        }
        if ($party > $valid['left']) {
            /* translators: %d: seats remaining */
            return new \WP_Error('prt_bk_party', sprintf(__('Only %d seats are left for that time.', 'pressroot'), $valid['left']));
        }
        if ($service['capacity'] === 1) {
            $party = 1;
        }

        $status = Settings::get()['auto_confirm'] ? 'confirmed' : 'pending';
        $token  = wp_generate_password(24, false, false);

        $id = wp_insert_post([
            'post_type'   => self::CPT,
            'post_status' => 'publish',
            'post_title'  => sprintf(
                '%s — %s — %s',
                sanitize_text_field($data['name']),
                $service['title'],
                wp_date('Y-m-d H:i', $start, wp_timezone())
            ),
            'meta_input' => [
                '_prt_bk_service' => $service['id'],
                '_prt_bk_start'   => $start,
                '_prt_bk_end'     => $start + $service['duration'] * 60,
                '_prt_bk_name'    => sanitize_text_field($data['name']),
                '_prt_bk_email'   => sanitize_email($data['email']),
                '_prt_bk_phone'   => sanitize_text_field($data['phone'] ?? ''),
                '_prt_bk_party'   => $party,
                '_prt_bk_notes'   => sanitize_textarea_field($data['notes'] ?? ''),
                '_prt_bk_status'  => $status,
                '_prt_bk_token'   => $token,
            ],
        ], true);

        if (is_wp_error($id)) {
            return $id;
        }

        // Post-insert capacity recheck: if a concurrent request slipped a
        // booking in between our validation and insert and the slot is now
        // oversold, the later insert (highest ID) withdraws itself.
        if (self::booked_seats($service['id'], $start) > $service['capacity']) {
            $peers = get_posts([
                'post_type' => self::CPT, 'post_status' => 'publish', 'fields' => 'ids',
                'posts_per_page' => 200, 'no_found_rows' => true,
                'meta_query' => [
                    ['key' => '_prt_bk_service', 'value' => $service['id']],
                    ['key' => '_prt_bk_start', 'value' => $start],
                    ['key' => '_prt_bk_status', 'value' => ['pending', 'confirmed'], 'compare' => 'IN'],
                ],
            ]);
            if ($peers && max($peers) === $id) {
                wp_delete_post($id, true);
                return new \WP_Error('prt_bk_race', __('That time was just taken — please pick another slot.', 'pressroot'));
            }
        }

        $booking = self::to_array($id);

        /** Fires once a booking is stored — emails hook here. */
        do_action('pressroot/booking_created', $booking, $service);

        return $booking;
    }

    /** Cancel by token (customer link) or by ID (admin). */
    public static function cancel_by_token(string $token): ?array
    {
        $ids = get_posts([
            'post_type' => self::CPT, 'post_status' => 'publish', 'fields' => 'ids',
            'posts_per_page' => 1, 'no_found_rows' => true,
            'meta_query' => [['key' => '_prt_bk_token', 'value' => $token]],
        ]);
        if (! $ids) {
            return null;
        }
        return self::set_status((int) $ids[0], 'cancelled');
    }

    public static function set_status(int $id, string $status): ?array
    {
        if (! in_array($status, self::STATUSES, true) || get_post_type($id) !== self::CPT) {
            return null;
        }
        $old = get_post_meta($id, '_prt_bk_status', true);
        if ($old === $status) {
            return self::to_array($id);
        }
        update_post_meta($id, '_prt_bk_status', $status);
        $booking = self::to_array($id);

        /** Fires on every status transition — emails hook here. */
        do_action('pressroot/booking_status_changed', $booking, $old, $status);

        return $booking;
    }

    public static function to_array(int $id): array
    {
        $m = fn (string $key) => get_post_meta($id, $key, true);
        return [
            'id'      => $id,
            'service' => (int) $m('_prt_bk_service'),
            'start'   => (int) $m('_prt_bk_start'),
            'end'     => (int) $m('_prt_bk_end'),
            'name'    => (string) $m('_prt_bk_name'),
            'email'   => (string) $m('_prt_bk_email'),
            'phone'   => (string) $m('_prt_bk_phone'),
            'party'   => max(1, (int) $m('_prt_bk_party')),
            'notes'   => (string) $m('_prt_bk_notes'),
            'status'  => (string) ($m('_prt_bk_status') ?: 'pending'),
            'token'   => (string) $m('_prt_bk_token'),
        ];
    }
}
