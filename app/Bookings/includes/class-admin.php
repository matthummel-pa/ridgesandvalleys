<?php

/**
 * Admin surfaces for Pressroots Reserve.
 *
 *  - The Bookings list table (prt_booking CPT): a status column, when/who/party
 *    columns, and row-action quick toggles (confirm / cancel) that fire through
 *    admin-post with nonces and reuse Engine::set_status() so confirmation and
 *    cancellation emails go out exactly as they would from the front end.
 *  - A read-only detail meta box on the single-booking screen plus a status
 *    selector (the one place an owner edits a booking by hand).
 *  - The Calendar page — a submenu under Bookings with Month / Week / Day /
 *    List views, driven by assets/js/calendar.js against the REST feed.
 */

namespace PrtBookings;

defined('ABSPATH') || exit;

class Admin
{
    private const PARENT = 'edit.php?post_type=prt_booking';

    public function hooks(): void
    {
        add_action('admin_menu', [$this, 'menu']);
        add_filter('manage_' . Engine::CPT . '_posts_columns', [$this, 'columns']);
        add_action('manage_' . Engine::CPT . '_posts_custom_column', [$this, 'column'], 10, 2);
        add_filter('post_row_actions', [$this, 'row_actions'], 10, 2);
        add_action('admin_post_prt_booking_status', [$this, 'handle_status']);
        add_action('add_meta_boxes_' . Engine::CPT, [$this, 'meta_box']);
        add_action('save_post_' . Engine::CPT, [$this, 'save_meta'], 10, 2);
        add_action('admin_enqueue_scripts', [$this, 'assets']);
        add_filter('parent_file', [$this, 'keep_menu_open']);
    }

    /* ── Menus ─────────────────────────────────────────────────────────── */

    public function menu(): void
    {
        add_submenu_page(
            self::PARENT,
            sprintf(/* translators: %s: brand */ __('%s Calendar', 'pressroot'), Plugin::BRAND),
            __('Calendar', 'pressroot'),
            'edit_theme_options',
            'prt-bookings-calendar',
            [$this, 'calendar_page']
        );
    }

    /** Keep the Bookings menu highlighted on the calendar screen. */
    public function keep_menu_open($parent_file)
    {
        $screen = get_current_screen();
        if ($screen && $screen->id === 'prt_booking_page_prt-bookings-calendar') {
            return self::PARENT;
        }
        return $parent_file;
    }

    /* ── Bookings list table ───────────────────────────────────────────── */

    public function columns(array $cols): array
    {
        $out = ['cb' => $cols['cb'] ?? ''];
        $out['prt_when']    = __('When', 'pressroot');
        $out['prt_service'] = __('Service', 'pressroot');
        $out['prt_who']     = __('Guest', 'pressroot');
        $out['prt_party']   = __('Party', 'pressroot');
        $out['prt_status']  = __('Status', 'pressroot');
        return $out;
    }

    public function column(string $col, int $id): void
    {
        $b = Engine::to_array($id);
        switch ($col) {
            case 'prt_when':
                $when = wp_date(get_option('date_format') . ' · ' . (get_option('time_format') ?: 'g:i a'), $b['start'], wp_timezone());
                $link = get_edit_post_link($id);
                echo $link
                    ? '<strong><a class="row-title" href="' . esc_url($link) . '">' . esc_html($when) . '</a></strong>'
                    : '<strong>' . esc_html($when) . '</strong>';
                break;
            case 'prt_service':
                $s = Services::get($b['service']);
                echo esc_html($s ? $s['title'] : '—');
                break;
            case 'prt_who':
                echo '<strong>' . esc_html($b['name']) . '</strong>';
                if ($b['email']) {
                    echo '<br><a href="mailto:' . esc_attr($b['email']) . '">' . esc_html($b['email']) . '</a>';
                }
                if ($b['phone']) {
                    echo '<br><span class="prt-bk-phone">' . esc_html($b['phone']) . '</span>';
                }
                break;
            case 'prt_party':
                echo esc_html((string) $b['party']);
                break;
            case 'prt_status':
                self::status_pill($b['status']);
                break;
        }
    }

    public static function status_pill(string $status): void
    {
        $labels = [
            'confirmed' => __('Confirmed', 'pressroot'),
            'pending'   => __('Pending', 'pressroot'),
            'cancelled' => __('Cancelled', 'pressroot'),
        ];
        $label = $labels[$status] ?? $status;
        echo '<span class="prt-bk-pill prt-bk-pill--' . esc_attr($status) . '">' . esc_html($label) . '</span>';
    }

    public function row_actions(array $actions, \WP_Post $post): array
    {
        if ($post->post_type !== Engine::CPT) {
            return $actions;
        }
        $status = get_post_meta($post->ID, '_prt_bk_status', true) ?: 'pending';

        if ($status === 'pending') {
            $actions['prt_confirm'] = '<a href="' . esc_url(self::status_url($post->ID, 'confirmed')) . '">' . esc_html__('Confirm', 'pressroot') . '</a>';
        }
        if ($status !== 'cancelled') {
            $actions['prt_cancel'] = '<a href="' . esc_url(self::status_url($post->ID, 'cancelled')) . '" style="color:#b32d2e">' . esc_html__('Cancel', 'pressroot') . '</a>';
        }
        if ($status === 'cancelled') {
            $actions['prt_reinstate'] = '<a href="' . esc_url(self::status_url($post->ID, 'confirmed')) . '">' . esc_html__('Reinstate', 'pressroot') . '</a>';
        }
        return $actions;
    }

    protected static function status_url(int $id, string $status): string
    {
        return wp_nonce_url(
            admin_url('admin-post.php?action=prt_booking_status&booking=' . $id . '&status=' . $status),
            'prt_booking_status_' . $id
        );
    }

    public function handle_status(): void
    {
        $id     = isset($_GET['booking']) ? absint($_GET['booking']) : 0;
        $status = isset($_GET['status']) ? sanitize_key($_GET['status']) : '';
        if (! $id || ! current_user_can('edit_theme_options') || ! check_admin_referer('prt_booking_status_' . $id)) {
            wp_die(esc_html__('Not allowed.', 'pressroot'));
        }
        if (in_array($status, Engine::STATUSES, true)) {
            Engine::set_status($id, $status);
        }
        wp_safe_redirect(wp_get_referer() ?: admin_url(self::PARENT));
        exit;
    }

    /* ── Single-booking meta box ───────────────────────────────────────── */

    public function meta_box(): void
    {
        add_meta_box('prt_bk_detail', __('Booking', 'pressroot'), [$this, 'meta_box_html'], Engine::CPT, 'normal', 'high');
    }

    public function meta_box_html(\WP_Post $post): void
    {
        $b = Engine::to_array($post->ID);
        $s = Services::get($b['service']);
        wp_nonce_field('prt_bk_meta', 'prt_bk_meta_nonce');
        $when = $b['start'] ? wp_date(get_option('date_format') . ' ' . get_option('time_format'), $b['start'], wp_timezone()) : '—';
        ?>
        <table class="form-table prt-bk-detail">
            <tr><th><?php esc_html_e('Service', 'pressroot'); ?></th><td><?php echo esc_html($s ? $s['title'] : '—'); ?></td></tr>
            <tr><th><?php esc_html_e('When', 'pressroot'); ?></th><td><?php echo esc_html($when . ' (' . wp_timezone_string() . ')'); ?></td></tr>
            <tr><th><?php esc_html_e('Guest', 'pressroot'); ?></th><td><?php echo esc_html($b['name']); ?></td></tr>
            <tr><th><?php esc_html_e('Email', 'pressroot'); ?></th><td><?php echo $b['email'] ? '<a href="mailto:' . esc_attr($b['email']) . '">' . esc_html($b['email']) . '</a>' : '—'; ?></td></tr>
            <tr><th><?php esc_html_e('Phone', 'pressroot'); ?></th><td><?php echo esc_html($b['phone'] ?: '—'); ?></td></tr>
            <tr><th><?php esc_html_e('Party size', 'pressroot'); ?></th><td><?php echo esc_html((string) $b['party']); ?></td></tr>
            <tr><th><?php esc_html_e('Notes', 'pressroot'); ?></th><td><?php echo nl2br(esc_html($b['notes'] ?: '—')); ?></td></tr>
            <tr><th><label for="prt_bk_status"><?php esc_html_e('Status', 'pressroot'); ?></label></th>
                <td>
                    <select id="prt_bk_status" name="prt_bk_status">
                        <?php foreach (Engine::STATUSES as $st) : ?>
                            <option value="<?php echo esc_attr($st); ?>" <?php selected($b['status'], $st); ?>><?php echo esc_html(ucfirst($st)); ?></option>
                        <?php endforeach; ?>
                    </select>
                    <p class="description"><?php esc_html_e('Changing to Confirmed or Cancelled emails the guest, just like the row actions do.', 'pressroot'); ?></p>
                </td>
            </tr>
        </table>
        <?php
    }

    public function save_meta(int $post_id, \WP_Post $post): void
    {
        if (
            ! isset($_POST['prt_bk_meta_nonce'])
            || ! wp_verify_nonce(sanitize_key($_POST['prt_bk_meta_nonce']), 'prt_bk_meta')
            || ! current_user_can('edit_theme_options')
            || (defined('DOING_AUTOSAVE') && DOING_AUTOSAVE)
        ) {
            return;
        }
        $new = sanitize_key($_POST['prt_bk_status'] ?? '');
        if (in_array($new, Engine::STATUSES, true)) {
            // set_status() fires the transition hooks (emails); it no-ops when unchanged.
            Engine::set_status($post_id, $new);
        }
    }

    /* ── Calendar page ─────────────────────────────────────────────────── */

    public function calendar_page(): void
    {
        if (! current_user_can('edit_theme_options')) {
            return;
        }
        ?>
        <div class="wrap prt-cal-wrap">
            <h1 class="prt-cal-h1">
                <?php echo esc_html(sprintf(/* translators: %s: brand */ __('%s Calendar', 'pressroot'), Plugin::BRAND)); ?>
                <a href="<?php echo esc_url(admin_url('post-new.php?post_type=' . Services::CPT)); ?>" class="page-title-action"><?php esc_html_e('Add service', 'pressroot'); ?></a>
            </h1>
            <div id="prt-calendar"
                 data-rest="<?php echo esc_attr(esc_url_raw(rest_url(Rest::NS . '/calendar'))); ?>"
                 data-nonce="<?php echo esc_attr(wp_create_nonce('wp_rest')); ?>"
                 data-start-of-week="<?php echo esc_attr((int) get_option('start_of_week', 0)); ?>"
                 data-today="<?php echo esc_attr(wp_date('Y-m-d', null, wp_timezone())); ?>">
                <p class="prt-cal-loading"><?php esc_html_e('Loading calendar…', 'pressroot'); ?></p>
            </div>
        </div>
        <?php
    }

    /* ── Assets ────────────────────────────────────────────────────────── */

    public function assets($hook): void
    {
        $screen = get_current_screen();
        if (! $screen) {
            return;
        }
        $is_list     = $screen->id === 'edit-' . Engine::CPT;
        $is_edit     = $screen->id === Engine::CPT;
        $is_calendar = $screen->id === 'prt_booking_page_prt-bookings-calendar';

        if ($is_list || $is_edit || $is_calendar) {
            wp_enqueue_style('prt-bookings-admin', PRT_BOOKINGS_URL . 'assets/css/admin.css', [], PRT_BOOKINGS_VERSION);
        }

        if ($is_calendar) {
            wp_enqueue_script('prt-bookings-calendar', PRT_BOOKINGS_URL . 'assets/js/calendar.js', [], PRT_BOOKINGS_VERSION, true);
            wp_localize_script('prt-bookings-calendar', 'PRT_CAL_I18N', [
                'views'   => [
                    'month' => __('Month', 'pressroot'),
                    'week'  => __('Week', 'pressroot'),
                    'day'   => __('Day', 'pressroot'),
                    'list'  => __('List', 'pressroot'),
                ],
                'today'   => __('Today', 'pressroot'),
                'prev'    => __('Previous', 'pressroot'),
                'next'    => __('Next', 'pressroot'),
                'noEvents' => __('No bookings in this range.', 'pressroot'),
                'party'   => __('party of %d', 'pressroot'),
                'more'    => __('+%d more', 'pressroot'),
                'allDay'  => __('All day', 'pressroot'),
                'pending' => __('Pending', 'pressroot'),
                'confirmed' => __('Confirmed', 'pressroot'),
                'edit'    => __('Open booking', 'pressroot'),
                'months'  => [
                    __('January', 'pressroot'), __('February', 'pressroot'), __('March', 'pressroot'),
                    __('April', 'pressroot'), __('May', 'pressroot'), __('June', 'pressroot'),
                    __('July', 'pressroot'), __('August', 'pressroot'), __('September', 'pressroot'),
                    __('October', 'pressroot'), __('November', 'pressroot'), __('December', 'pressroot'),
                ],
                'days'    => [
                    __('Sun', 'pressroot'), __('Mon', 'pressroot'), __('Tue', 'pressroot'),
                    __('Wed', 'pressroot'), __('Thu', 'pressroot'), __('Fri', 'pressroot'), __('Sat', 'pressroot'),
                ],
            ]);
        }
    }
}
