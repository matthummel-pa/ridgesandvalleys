<?php

/**
 * Services CPT — the bookable things. A service with capacity 1 behaves like
 * a Calendly appointment type (one customer per slot); capacity >1 behaves
 * like an OpenTable seating (N seats per slot, customers bring a party size
 * that consumes seats).
 */

namespace PrtBookings;

defined('ABSPATH') || exit;

class Services
{
    public const CPT = 'prt_service';

    public function hooks(): void
    {
        add_action('init', [$this, 'register']);
        add_action('add_meta_boxes', [$this, 'meta_box']);
        add_action('save_post_' . self::CPT, [$this, 'save_meta'], 10, 2);
        add_filter('manage_' . self::CPT . '_posts_columns', [$this, 'columns']);
        add_action('manage_' . self::CPT . '_posts_custom_column', [$this, 'column_content'], 10, 2);
    }

    public function register(): void
    {
        register_post_type(self::CPT, [
            'labels' => [
                'name'          => __('Services', 'pressroot'),
                'singular_name' => __('Service', 'pressroot'),
                'add_new_item'  => __('Add Service', 'pressroot'),
                'edit_item'     => __('Edit Service', 'pressroot'),
            ],
            'public'              => false,
            'show_ui'             => true,
            'show_in_menu'        => 'edit.php?post_type=prt_booking',
            'show_in_rest'        => false,
            'supports'            => ['title', 'editor'],
            'menu_icon'           => 'dashicons-clipboard',
            'exclude_from_search' => true,
        ]);
    }

    /** Published services in menu order, with resolved meta. */
    public static function all(): array
    {
        $posts = get_posts([
            'post_type'      => self::CPT,
            'post_status'    => 'publish',
            'posts_per_page' => 50,
            'orderby'        => 'menu_order title',
            'order'          => 'ASC',
        ]);
        return array_map([self::class, 'to_array'], $posts);
    }

    public static function get(int $id): ?array
    {
        $post = get_post($id);
        if (! $post || $post->post_type !== self::CPT || $post->post_status !== 'publish') {
            return null;
        }
        return self::to_array($post);
    }

    protected static function to_array(\WP_Post $post): array
    {
        return [
            'id'       => $post->ID,
            'title'    => $post->post_title,
            'desc'     => wp_strip_all_tags(get_the_excerpt($post) ?: wp_trim_words($post->post_content, 28)),
            'duration' => max(5, (int) (get_post_meta($post->ID, '_prt_svc_duration', true) ?: 60)),
            'buffer'   => max(0, (int) get_post_meta($post->ID, '_prt_svc_buffer', true)),
            'capacity' => max(1, (int) (get_post_meta($post->ID, '_prt_svc_capacity', true) ?: 1)),
            'price'    => (string) get_post_meta($post->ID, '_prt_svc_price', true),
        ];
    }

    /* ── Admin meta box ────────────────────────────────────────────────── */

    public function meta_box(): void
    {
        add_meta_box('prt_svc_details', __('Booking details', 'pressroot'), [$this, 'meta_box_html'], self::CPT, 'side');
    }

    public function meta_box_html(\WP_Post $post): void
    {
        wp_nonce_field('prt_svc_meta', 'prt_svc_nonce');
        $duration = get_post_meta($post->ID, '_prt_svc_duration', true) ?: 60;
        $buffer   = get_post_meta($post->ID, '_prt_svc_buffer', true) ?: 0;
        $capacity = get_post_meta($post->ID, '_prt_svc_capacity', true) ?: 1;
        $price    = get_post_meta($post->ID, '_prt_svc_price', true);
        ?>
        <p><label><strong><?php esc_html_e('Duration (minutes)', 'pressroot'); ?></strong><br>
            <input type="number" name="prt_svc_duration" min="5" max="1440" step="5" value="<?php echo esc_attr($duration); ?>" style="width:100%"></label></p>
        <p><label><strong><?php esc_html_e('Buffer after (minutes)', 'pressroot'); ?></strong><br>
            <input type="number" name="prt_svc_buffer" min="0" max="240" step="5" value="<?php echo esc_attr($buffer); ?>" style="width:100%"></label>
            <span class="description"><?php esc_html_e('Cleanup/travel time before the next slot.', 'pressroot'); ?></span></p>
        <p><label><strong><?php esc_html_e('Capacity per slot', 'pressroot'); ?></strong><br>
            <input type="number" name="prt_svc_capacity" min="1" max="500" value="<?php echo esc_attr($capacity); ?>" style="width:100%"></label>
            <span class="description"><?php esc_html_e('1 = one-on-one appointment. Higher = seats per time slot (customers pick a party size), like a restaurant or class.', 'pressroot'); ?></span></p>
        <p><label><strong><?php esc_html_e('Price label', 'pressroot'); ?></strong><br>
            <input type="text" name="prt_svc_price" value="<?php echo esc_attr($price); ?>" placeholder="<?php esc_attr_e('e.g. $75 · Free consult', 'pressroot'); ?>" style="width:100%"></label>
            <span class="description"><?php esc_html_e('Display only — no payment is collected.', 'pressroot'); ?></span></p>
        <?php
    }

    public function save_meta(int $post_id, \WP_Post $post): void
    {
        if (
            ! isset($_POST['prt_svc_nonce'])
            || ! wp_verify_nonce(sanitize_key($_POST['prt_svc_nonce']), 'prt_svc_meta')
            || ! current_user_can('edit_post', $post_id)
            || (defined('DOING_AUTOSAVE') && DOING_AUTOSAVE)
        ) {
            return;
        }
        update_post_meta($post_id, '_prt_svc_duration', max(5, min(1440, absint($_POST['prt_svc_duration'] ?? 60))));
        update_post_meta($post_id, '_prt_svc_buffer', max(0, min(240, absint($_POST['prt_svc_buffer'] ?? 0))));
        update_post_meta($post_id, '_prt_svc_capacity', max(1, min(500, absint($_POST['prt_svc_capacity'] ?? 1))));
        update_post_meta($post_id, '_prt_svc_price', sanitize_text_field(wp_unslash($_POST['prt_svc_price'] ?? '')));
    }

    /* ── List-table columns ────────────────────────────────────────────── */

    public function columns(array $cols): array
    {
        $date = $cols['date'] ?? null;
        unset($cols['date']);
        $cols['prt_duration'] = __('Duration', 'pressroot');
        $cols['prt_capacity'] = __('Capacity', 'pressroot');
        $cols['prt_price']    = __('Price', 'pressroot');
        if ($date) {
            $cols['date'] = $date;
        }
        return $cols;
    }

    public function column_content(string $col, int $post_id): void
    {
        switch ($col) {
            case 'prt_duration':
                echo esc_html(sprintf(/* translators: %d: minutes */ __('%d min', 'pressroot'), (int) (get_post_meta($post_id, '_prt_svc_duration', true) ?: 60)));
                break;
            case 'prt_capacity':
                $cap = max(1, (int) (get_post_meta($post_id, '_prt_svc_capacity', true) ?: 1));
                echo $cap === 1 ? esc_html__('1 (appointment)', 'pressroot') : esc_html(sprintf(/* translators: %d: seats */ __('%d seats', 'pressroot'), $cap));
                break;
            case 'prt_price':
                echo esc_html(get_post_meta($post_id, '_prt_svc_price', true) ?: '—');
                break;
        }
    }
}
