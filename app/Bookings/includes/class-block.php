<?php

/**
 * The booking widget: `prt/booking` block + `[prt_booking]` shortcode.
 *
 * Both resolve to the same server-rendered container. The container ships a
 * fresh nonce and the REST base as data-attributes; assets/js/booking.js does
 * the interactive work (service picker → date strip → slot grid → details
 * form) by calling the Rest routes. Nothing about availability is computed in
 * the browser — the JS only renders what the engine returns.
 */

namespace PrtBookings;

defined('ABSPATH') || exit;

class Block
{
    public const NAME = 'prt/booking';

    public function hooks(): void
    {
        add_action('init', [$this, 'register']);
        add_shortcode('prt_booking', [$this, 'shortcode']);
    }

    public function register(): void
    {
        wp_register_style(
            'prt-booking',
            PRT_BOOKINGS_URL . 'assets/css/booking.css',
            [],
            PRT_BOOKINGS_VERSION
        );

        wp_register_script(
            'prt-booking',
            PRT_BOOKINGS_URL . 'assets/js/booking.js',
            [],
            PRT_BOOKINGS_VERSION,
            true
        );

        // Editor: a light registration that previews via ServerSideRender.
        wp_register_script(
            'prt-booking-editor',
            PRT_BOOKINGS_URL . 'assets/js/block.js',
            ['wp-blocks', 'wp-element', 'wp-block-editor', 'wp-components', 'wp-server-side-render', 'wp-i18n'],
            PRT_BOOKINGS_VERSION,
            true
        );
        wp_localize_script('prt-booking-editor', 'PRT_BOOKING_EDITOR', [
            'services' => array_map(function ($s) {
                return ['id' => $s['id'], 'title' => $s['title']];
            }, Services::all()),
            'brand' => \PrtBookings\Plugin::BRAND,
        ]);

        register_block_type(self::NAME, [
            'api_version'     => 3,
            'title'           => __('Booking form', 'pressroot'),
            'description'     => sprintf(/* translators: %s: brand */ __('%s — let visitors book appointments or reserve a table/room.', 'pressroot'), \PrtBookings\Plugin::BRAND),
            'category'        => 'widgets',
            'icon'            => 'calendar-alt',
            'keywords'        => ['booking', 'reservation', 'appointment', 'calendar', 'reserve'],
            'editor_script'   => 'prt-booking-editor',
            'style'           => 'prt-booking',
            'render_callback' => [$this, 'render'],
            'attributes'      => [
                'service' => ['type' => 'number', 'default' => 0],  // 0 = let the visitor choose
                'accent'  => ['type' => 'string', 'default' => ''],
            ],
            'supports' => ['align' => ['wide'], 'html' => false],
        ]);
    }

    /** [prt_booking service="12"] */
    public function shortcode($atts): string
    {
        $atts = shortcode_atts(['service' => 0, 'accent' => ''], $atts, 'prt_booking');
        return $this->render(['service' => (int) $atts['service'], 'accent' => (string) $atts['accent']]);
    }

    /**
     * Server render: emits the container the front-end script hydrates. When
     * there are no published services yet, shows an owner-only hint instead of
     * an empty box.
     */
    public function render($atts, $content = '', $block = null): string
    {
        $atts    = wp_parse_args((array) $atts, ['service' => 0, 'accent' => '']);
        $service = (int) $atts['service'];

        // If a specific service was chosen but no longer exists, fall back to picker.
        if ($service > 0 && ! Services::get($service)) {
            $service = 0;
        }

        $services = Services::all();
        if (empty($services)) {
            if (current_user_can('edit_theme_options')) {
                return '<div class="prt-booking prt-booking--empty"><p>'
                    . sprintf(
                        wp_kses(/* translators: %s: link to add a service */ __('No bookable services yet. <a href="%s">Add a service</a> to switch this form on.', 'pressroot'), ['a' => ['href' => []]]),
                        esc_url(admin_url('post-new.php?post_type=' . Services::CPT))
                    )
                    . '</p></div>';
            }
            return '';
        }

        wp_enqueue_style('prt-booking');
        wp_enqueue_script('prt-booking');

        $accent = $atts['accent'] !== '' ? $atts['accent'] : (get_theme_mod('prt_brand_color', '') ?: '');

        $config = [
            'rest'   => esc_url_raw(rest_url(Rest::NS)),
            'nonce'  => wp_create_nonce('prt_booking'),
            'preset' => $service,
            'strings' => [
                'choose'    => __('Choose a service', 'pressroot'),
                'date'      => __('Pick a date', 'pressroot'),
                'time'      => __('Pick a time', 'pressroot'),
                'details'   => __('Your details', 'pressroot'),
                'name'      => __('Name', 'pressroot'),
                'email'     => __('Email', 'pressroot'),
                'phone'     => __('Phone (optional)', 'pressroot'),
                'party'     => __('Party size', 'pressroot'),
                'notes'     => __('Notes (optional)', 'pressroot'),
                'book'      => __('Confirm booking', 'pressroot'),
                'booking'   => __('Booking…', 'pressroot'),
                'back'      => __('← Back', 'pressroot'),
                'noslots'   => __('No times available on this day — try another.', 'pressroot'),
                'nodays'    => __('No availability in the booking window yet.', 'pressroot'),
                'loading'   => __('Loading…', 'pressroot'),
                'seatsleft' => __('%d left', 'pressroot'),
                'confirmed' => __('You’re booked!', 'pressroot'),
                'pending'   => __('Request received', 'pressroot'),
                'pendingmsg' => __('We’ll email you as soon as it’s confirmed.', 'pressroot'),
                'error'     => __('Something went wrong. Please try again.', 'pressroot'),
                'required'  => __('Please fill in the required fields.', 'pressroot'),
                'another'   => __('Make another booking', 'pressroot'),
                'free'      => __('Free', 'pressroot'),
            ],
            'services' => array_map(function ($s) {
                return [
                    'id'       => $s['id'],
                    'title'    => $s['title'],
                    'desc'     => $s['desc'],
                    'duration' => $s['duration'],
                    'capacity' => $s['capacity'],
                    'price'    => $s['price'],
                ];
            }, $services),
        ];

        $wrapper = function_exists('get_block_wrapper_attributes')
            ? get_block_wrapper_attributes(['class' => 'prt-booking'])
            : 'class="prt-booking"';

        $style = $accent !== '' ? ' style="--prt-bk-accent:' . esc_attr($accent) . '"' : '';

        ob_start();
        echo '<div ' . $wrapper . $style . ' data-prt-booking=\'' . esc_attr(wp_json_encode($config)) . '\'>'; // phpcs:ignore WordPress.Security.EscapeOutput
        echo '<noscript>' . esc_html__('Please enable JavaScript to book online, or contact us directly.', 'pressroot') . '</noscript>';
        echo '<div class="prt-bk-stage" aria-live="polite"></div>';
        echo '</div>';
        return ob_get_clean();
    }
}
