<?php

/**
 * Per-post options (blog posts).
 *
 * A small, core-WordPress meta box on the post editor — no blocks, no page
 * builder. Currently exposes the "short version" summary shown in the in-article
 * summary box; the post's Featured Image (native WP) is used as its hero/card
 * image. Stored in the same rv_f_* meta namespace as the page fields.
 */

namespace App;

add_action('add_meta_boxes', function () {
    add_meta_box(
        'rv_post_options',
        __('Journal post options', 'sage'),
        __NAMESPACE__ . '\\render_post_options_box',
        'post',
        'side',
        'default',
    );
});

function render_post_options_box(\WP_Post $post): void
{
    wp_nonce_field('rv_post_options_save', 'rv_post_options_nonce');
    $summary = (string) get_post_meta($post->ID, 'rv_f_post_summary', true);
    ?>
    <p style="margin-top:0">
        <label for="rv_f_post_summary"><strong><?php esc_html_e('The short version (summary)', 'sage'); ?></strong></label>
    </p>
    <textarea id="rv_f_post_summary" name="rv_f_post_summary" rows="5" style="width:100%"><?php echo esc_textarea($summary); ?></textarea>
    <p class="description"><?php esc_html_e('2–3 sentences shown in the summary box at the top of the post. Leave blank to fall back to the post excerpt.', 'sage'); ?></p>
    <p class="description" style="margin-top:.7em"><?php esc_html_e('Tip: set a Featured Image (below) to use as this post’s hero and card image.', 'sage'); ?></p>
    <?php
}

add_action('save_post_post', function ($post_id) {
    if (! isset($_POST['rv_post_options_nonce'])
        || ! wp_verify_nonce(sanitize_key($_POST['rv_post_options_nonce']), 'rv_post_options_save')) {
        return;
    }
    if (defined('DOING_AUTOSAVE') && DOING_AUTOSAVE) {
        return;
    }
    if (! current_user_can('edit_post', $post_id)) {
        return;
    }

    if (isset($_POST['rv_f_post_summary'])) {
        update_post_meta(
            $post_id,
            'rv_f_post_summary',
            wp_kses_post(wp_unslash($_POST['rv_f_post_summary'])),
        );
    }
});
