<?php

/**
 * Theme filters.
 */

namespace App;

/**
 * Editorial excerpt length + ellipsis.
 */
add_filter('excerpt_length', fn() => 28);
add_filter('excerpt_more', fn() => '&hellip;');

/**
 * Zero-friction comment spam trap.
 *
 * The comment form renders a visually-hidden honeypot field named `rvc_hp`
 * (see comments.php). Real people never see or fill it; automated spam bots
 * fill every field they find. A non-empty value here is therefore spam, so we
 * stop it before it is stored — no CAPTCHA, no puzzle for real readers.
 *
 * Registered here (an always-loaded file) because comment submissions post to
 * wp-comments-post.php, which never loads the theme's comments.php template.
 */
add_filter('preprocess_comment', function ($commentdata) {
    if (! empty($_POST['rvc_hp'])) {
        wp_die(
            esc_html__('Your comment looks automated and was not posted. If you are a real person, please go back and submit again without filling the hidden field.', 'sage'),
            esc_html__('Comment blocked', 'sage'),
            ['response' => 403, 'back_link' => true],
        );
    }

    return $commentdata;
}, 1);
