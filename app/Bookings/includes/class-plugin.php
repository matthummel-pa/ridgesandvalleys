<?php

/**
 * Pressroots Reserve — plugin bootstrap.
 *
 * Wires the pieces together (mirrors Repofolio's Plugin::boot()): news up
 * each subsystem, calls its hooks(), seeds default options on first run, and
 * owns the one front-end route that isn't a block — the tokenized
 * cancel-confirmation screen a customer reaches from their confirmation email.
 */

namespace PrtBookings;

defined('ABSPATH') || exit;

class Plugin
{
    /** Product/brand name — the "Repofolio" of this addon. */
    public const BRAND = 'Pressroots Reserve';

    /** @var Settings */ public $settings;
    /** @var Services */ public $services;
    /** @var Engine */   public $engine;
    /** @var Emails */   public $emails;
    /** @var Rest */     public $rest;
    /** @var Block */    public $block;
    /** @var Admin */    public $admin;

    public function boot(): void
    {
        $this->settings = new Settings();
        $this->services = new Services();
        $this->engine   = new Engine();
        $this->emails   = new Emails();
        $this->rest     = new Rest();
        $this->block    = new Block();
        $this->admin    = new Admin();

        // First run as an addon: make sure the option row exists so every
        // Settings::get() has the full default shape without a merge miss.
        if (get_option(Settings::OPTION_KEY) === false) {
            update_option(Settings::OPTION_KEY, Settings::defaults(), false);
        }

        $this->settings->hooks();
        $this->services->hooks();
        $this->engine->hooks();
        $this->emails->hooks();
        $this->rest->hooks();
        $this->block->hooks();
        $this->admin->hooks();

        add_action('template_redirect', [$this, 'handle_cancel']);
    }

    /* ── Front-end cancel-confirmation screen ─────────────────────────────
     *
     * The confirmation email's cancel link is a plain GET URL. Mail scanners
     * and link-preview bots fetch GET links, so the GET view only *shows* a
     * confirm button — the actual cancel happens on the POST it submits
     * (nonce-guarded). This is the "confirm screen so mail scanners can't
     * auto-cancel" promised in the addon header.
     */

    public function handle_cancel(): void
    {
        if (! isset($_GET['prt_bk']) || $_GET['prt_bk'] !== 'cancel') {
            return;
        }

        $token = isset($_GET['bk_token']) ? sanitize_text_field(wp_unslash($_GET['bk_token'])) : '';
        if ($token === '') {
            $this->cancel_page(__('Missing cancellation link.', 'pressroot'), __('That link is incomplete. Please use the exact link from your confirmation email.', 'pressroot'));
        }

        $booking = self::find_by_token($token);

        // POST = the customer clicked "Yes, cancel".
        if (($_SERVER['REQUEST_METHOD'] ?? 'GET') === 'POST') {
            if (! isset($_POST['prt_bk_cancel_nonce']) || ! wp_verify_nonce(sanitize_key($_POST['prt_bk_cancel_nonce']), 'prt_bk_cancel_' . $token)) {
                $this->cancel_page(__('Link expired', 'pressroot'), __('This cancellation link has expired. Please reload the page from your email and try again.', 'pressroot'));
            }
            if (! $booking) {
                $this->cancel_page(__('Booking not found', 'pressroot'), __('We couldn’t find that booking — it may already have been cancelled.', 'pressroot'));
            }
            if ($booking['status'] === 'cancelled') {
                $this->cancel_page(__('Already cancelled', 'pressroot'), __('This booking was already cancelled. Nothing else to do.', 'pressroot'));
            }
            Engine::cancel_by_token($token);
            $this->cancel_page(
                __('Booking cancelled', 'pressroot'),
                __('Your booking has been cancelled and the time released. Thanks for letting us know.', 'pressroot'),
                false
            );
        }

        // GET = show the confirm screen.
        if (! $booking) {
            $this->cancel_page(__('Booking not found', 'pressroot'), __('We couldn’t find that booking — it may already have been cancelled.', 'pressroot'));
        }
        if ($booking['status'] === 'cancelled') {
            $this->cancel_page(__('Already cancelled', 'pressroot'), __('This booking has already been cancelled.', 'pressroot'), false);
        }

        $service = Services::get($booking['service']);
        $when    = wp_date(get_option('date_format') . ' ' . get_option('time_format'), $booking['start'], wp_timezone());
        $detail  = ($service ? $service['title'] . ' — ' : '') . $when;

        $form = '<p class="prt-bkc-detail">' . esc_html($detail) . '</p>'
            . '<form method="post" class="prt-bkc-actions">'
            . wp_nonce_field('prt_bk_cancel_' . $token, 'prt_bk_cancel_nonce', true, false)
            . '<button type="submit" class="prt-bkc-btn prt-bkc-btn--danger">' . esc_html__('Yes, cancel my booking', 'pressroot') . '</button> '
            . '<a class="prt-bkc-btn prt-bkc-btn--ghost" href="' . esc_url(home_url('/')) . '">' . esc_html__('Keep it', 'pressroot') . '</a>'
            . '</form>';

        $this->cancel_page(__('Cancel this booking?', 'pressroot'), $form, false, true);
    }

    /** Locate a booking post by its cancel token. */
    protected static function find_by_token(string $token): ?array
    {
        if ($token === '') {
            return null;
        }
        $ids = get_posts([
            'post_type'      => Engine::CPT,
            'post_status'    => 'publish',
            'posts_per_page' => 1,
            'fields'         => 'ids',
            'no_found_rows'  => true,
            'meta_query'     => [['key' => '_prt_bk_token', 'value' => $token]], // phpcs:ignore WordPress.DB.SlowDBQuery
        ]);
        return $ids ? Engine::to_array((int) $ids[0]) : null;
    }

    /**
     * Render a minimal, self-contained confirmation page and exit. Kept
     * standalone (rather than get_header()/get_footer()) so it renders
     * identically regardless of the active page template, and always in the
     * brand palette.
     *
     * @param string $title  Heading.
     * @param string $body   Pre-escaped HTML body (callers escape their own).
     * @param bool   $is_error Tints the accent red when true.
     * @param bool   $raw_body When true $body is trusted HTML (a form); otherwise it's plain text and gets wrapped/escaped.
     */
    protected function cancel_page(string $title, string $body, bool $is_error = true, bool $raw_body = false): void
    {
        nocache_headers();
        status_header($is_error ? 400 : 200);

        $accent = $is_error ? '#E5484D' : (get_theme_mod('prt_brand_color', '#6C4CF1') ?: '#6C4CF1');
        $site   = get_bloginfo('name');
        $home   = home_url('/');

        if (! $raw_body) {
            $body = '<p class="prt-bkc-detail">' . esc_html($body) . '</p>'
                . '<p><a class="prt-bkc-btn prt-bkc-btn--ghost" href="' . esc_url($home) . '">' . esc_html__('Back to site', 'pressroot') . '</a></p>';
        }

        header('Content-Type: text/html; charset=utf-8');
        echo '<!doctype html><html ' . get_language_attributes() . '><head><meta charset="utf-8">';
        echo '<meta name="viewport" content="width=device-width, initial-scale=1"><meta name="robots" content="noindex">';
        echo '<title>' . esc_html($title . ' · ' . $site) . '</title>';
        echo '<style>
            :root{--prt-accent:' . esc_attr($accent) . '}
            *{box-sizing:border-box}
            body{margin:0;min-height:100vh;display:flex;align-items:center;justify-content:center;
                font:16px/1.55 -apple-system,BlinkMacSystemFont,"Segoe UI",Roboto,Helvetica,Arial,sans-serif;
                background:#0f0d17;color:#17151f;padding:24px}
            .prt-bkc-card{background:#fff;max-width:460px;width:100%;border-radius:16px;
                box-shadow:0 24px 60px rgba(0,0,0,.28);padding:32px 30px;text-align:center}
            .prt-bkc-card h1{margin:0 0 12px;font-size:22px;line-height:1.25}
            .prt-bkc-detail{color:#57536b;margin:0 0 22px}
            .prt-bkc-actions{margin:0}
            .prt-bkc-btn{display:inline-block;border:0;border-radius:10px;padding:12px 18px;font-size:15px;
                font-weight:600;cursor:pointer;text-decoration:none}
            .prt-bkc-btn--danger{background:var(--prt-accent);color:#fff}
            .prt-bkc-btn--ghost{background:transparent;color:#57536b;border:1px solid #d9d6e4}
            .prt-bkc-brand{margin-top:22px;font-size:12px;color:#9c98ad;letter-spacing:.02em}
        </style></head><body><main class="prt-bkc-card">';
        echo '<h1>' . esc_html($title) . '</h1>';
        echo $body; // phpcs:ignore WordPress.Security.EscapeOutput -- callers pre-escape; form built with wp_nonce_field.
        echo '<p class="prt-bkc-brand">' . esc_html(sprintf(/* translators: %s: site name */ __('%s bookings', 'pressroot'), $site)) . '</p>';
        echo '</main></body></html>';
        exit;
    }
}
