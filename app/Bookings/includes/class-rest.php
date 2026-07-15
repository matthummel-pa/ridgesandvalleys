<?php

/**
 * REST API for Pressroots Reserve.
 *
 * The front-end widget talks to these routes; the admin calendar reads its
 * feed from the last one. Public routes (services/days/slots/book) are open
 * because the widget runs for logged-out visitors, but book() is guarded the
 * same way app/contact.php guards its form: a page-fresh nonce, a honeypot,
 * and a per-IP rate limit. The engine re-validates every slot at insert time,
 * so a forged timestamp can never create an out-of-window or oversold booking.
 */

namespace PrtBookings;

defined('ABSPATH') || exit;

class Rest
{
    public const NS = 'prt-bookings/v1';

    public function hooks(): void
    {
        add_action('rest_api_init', [$this, 'routes']);
    }

    public function routes(): void
    {
        register_rest_route(self::NS, '/services', [
            'methods'             => 'GET',
            'callback'            => [$this, 'services'],
            'permission_callback' => '__return_true',
        ]);

        register_rest_route(self::NS, '/days', [
            'methods'             => 'GET',
            'callback'            => [$this, 'days'],
            'permission_callback' => '__return_true',
            'args'                => [
                'service' => ['required' => true, 'sanitize_callback' => 'absint'],
            ],
        ]);

        register_rest_route(self::NS, '/slots', [
            'methods'             => 'GET',
            'callback'            => [$this, 'slots'],
            'permission_callback' => '__return_true',
            'args'                => [
                'service' => ['required' => true, 'sanitize_callback' => 'absint'],
                'date'    => ['required' => true, 'sanitize_callback' => 'sanitize_text_field'],
            ],
        ]);

        register_rest_route(self::NS, '/book', [
            'methods'             => 'POST',
            'callback'            => [$this, 'book'],
            'permission_callback' => '__return_true',
        ]);

        // Admin-only calendar feed.
        register_rest_route(self::NS, '/calendar', [
            'methods'             => 'GET',
            'callback'            => [$this, 'calendar'],
            'permission_callback' => function () {
                return current_user_can('edit_theme_options');
            },
            'args' => [
                'start' => ['required' => true, 'sanitize_callback' => 'absint'],
                'end'   => ['required' => true, 'sanitize_callback' => 'absint'],
            ],
        ]);
    }

    /* ── Public read routes ───────────────────────────────────────────── */

    public function services(\WP_REST_Request $req): \WP_REST_Response
    {
        $out = [];
        foreach (Services::all() as $s) {
            $out[] = [
                'id'       => $s['id'],
                'title'    => $s['title'],
                'desc'     => $s['desc'],
                'duration' => $s['duration'],
                'capacity' => $s['capacity'],
                'price'    => $s['price'],
                'mode'     => $s['capacity'] > 1 ? 'seats' : 'appointment',
            ];
        }
        return new \WP_REST_Response($out, 200);
    }

    public function days(\WP_REST_Request $req)
    {
        $service = Services::get((int) $req['service']);
        if (! $service) {
            return new \WP_Error('prt_bk_no_service', __('Service not found.', 'pressroot'), ['status' => 404]);
        }
        // Map -> compact list of just the open days (smaller payload).
        $open = [];
        foreach (Engine::days($service) as $date => $isOpen) {
            if ($isOpen) {
                $open[] = $date;
            }
        }
        return new \WP_REST_Response(['open' => $open], 200);
    }

    public function slots(\WP_REST_Request $req)
    {
        $service = Services::get((int) $req['service']);
        if (! $service) {
            return new \WP_Error('prt_bk_no_service', __('Service not found.', 'pressroot'), ['status' => 404]);
        }
        return new \WP_REST_Response(['slots' => Engine::slots($service, (string) $req['date'])], 200);
    }

    /* ── Public write route ───────────────────────────────────────────── */

    public function book(\WP_REST_Request $req)
    {
        $p = $req->get_json_params();
        if (! is_array($p)) {
            $p = $req->get_params();
        }

        // 1. Honeypot — a hidden field bots love to fill. Pretend success so
        //    they don't learn they were caught (mirrors contact.php).
        if (! empty($p['company'])) {
            return new \WP_REST_Response(['ok' => true, 'spam' => true], 200);
        }

        // 2. Nonce — fresh per page render, matching the theme's other public
        //    forms. Missing/expired nonce is rejected.
        $nonce = $req->get_header('X-PRT-Nonce') ?: ($p['_nonce'] ?? '');
        if (! wp_verify_nonce((string) $nonce, 'prt_booking')) {
            return new \WP_Error('prt_bk_nonce', __('Your session expired — please reload the page and try again.', 'pressroot'), ['status' => 403]);
        }

        // 3. Per-IP rate limit: one booking attempt per 15s. Fails open when
        //    REMOTE_ADDR is absent (no key = no throttle, never a lockout).
        $ip = isset($_SERVER['REMOTE_ADDR']) ? sanitize_text_field(wp_unslash($_SERVER['REMOTE_ADDR'])) : '';
        if ($ip !== '') {
            $key = 'prt_bk_rl_' . md5($ip);
            if (get_transient($key)) {
                return new \WP_Error('prt_bk_rate', __('Slow down a moment and try again.', 'pressroot'), ['status' => 429]);
            }
            set_transient($key, 1, 15);
        }

        // 4. Field validation.
        $name  = sanitize_text_field($p['name'] ?? '');
        $email = sanitize_email($p['email'] ?? '');
        if ($name === '' || ! is_email($email)) {
            return new \WP_Error('prt_bk_fields', __('Please enter your name and a valid email address.', 'pressroot'), ['status' => 422]);
        }

        $result = Engine::create([
            'service' => (int) ($p['service'] ?? 0),
            'start'   => (int) ($p['start'] ?? 0),
            'name'    => $name,
            'email'   => $email,
            'phone'   => sanitize_text_field($p['phone'] ?? ''),
            'party'   => (int) ($p['party'] ?? 1),
            'notes'   => sanitize_textarea_field($p['notes'] ?? ''),
        ]);

        if (is_wp_error($result)) {
            $code = $result->get_error_code() === 'prt_bk_slot' || $result->get_error_code() === 'prt_bk_race' ? 409 : 422;
            return new \WP_Error($result->get_error_code(), $result->get_error_message(), ['status' => $code]);
        }

        $service = Services::get($result['service']);
        return new \WP_REST_Response([
            'ok'      => true,
            'status'  => $result['status'],
            'when'    => wp_date(get_option('date_format') . ' ' . get_option('time_format'), $result['start'], wp_timezone()),
            'service' => $service ? $service['title'] : '',
            'party'   => $result['party'],
            'success_text' => Settings::get()['success_text'],
        ], 201);
    }

    /* ── Admin calendar feed ──────────────────────────────────────────── */

    public function calendar(\WP_REST_Request $req): \WP_REST_Response
    {
        $start = (int) $req['start'];
        $end   = (int) $req['end'];

        $ids = get_posts([
            'post_type'      => Engine::CPT,
            'post_status'    => 'publish',
            'posts_per_page' => 500,
            'fields'         => 'ids',
            'no_found_rows'  => true,
            'meta_query'     => [ // phpcs:ignore WordPress.DB.SlowDBQuery
                [
                    'key'     => '_prt_bk_start',
                    'value'   => [$start, $end],
                    'compare' => 'BETWEEN',
                    'type'    => 'NUMERIC',
                ],
            ],
        ]);

        $tz     = wp_timezone();
        $events = [];
        foreach ($ids as $id) {
            $b = Engine::to_array((int) $id);
            if ($b['status'] === 'cancelled') {
                continue;
            }
            $service = Services::get($b['service']);
            $events[] = [
                'id'      => $b['id'],
                'title'   => $service ? $service['title'] : __('(deleted service)', 'pressroot'),
                'name'    => $b['name'],
                'start'   => $b['start'],
                'end'     => $b['end'],
                'startISO' => wp_date('c', $b['start'], $tz),
                'endISO'  => wp_date('c', $b['end'], $tz),
                'date'    => wp_date('Y-m-d', $b['start'], $tz),
                'time'    => wp_date(get_option('time_format') ?: 'g:i a', $b['start'], $tz),
                'party'   => $b['party'],
                'status'  => $b['status'],
                'phone'   => $b['phone'],
                'email'   => $b['email'],
                'notes'   => $b['notes'],
                'edit'    => get_edit_post_link($b['id'], 'raw'),
            ];
        }

        return new \WP_REST_Response(['events' => $events], 200);
    }
}
