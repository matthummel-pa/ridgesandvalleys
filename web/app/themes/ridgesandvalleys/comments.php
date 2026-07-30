<?php

/**
 * Comment thread + form for single posts.
 *
 * Loaded by comments_template() from partials/content-single.blade.php. Built on
 * WordPress's semantic HTML5 comment markup (styled in the Customizer under
 * Additional CSS) and progressively enhanced with small vanilla scripts:
 * relative timestamps, a newest/oldest sort toggle, and a live character count.
 * Threaded replies use core's comment-reply script (enqueued in app/setup.php).
 * The `rvc_hp` honeypot pairs with the preprocess_comment guard in app/filters.php.
 */

if (! defined('ABSPATH')) {
    exit;
}

// Never expose comments on a password-protected post until it's unlocked.
if (post_password_required()) {
    return;
}

$rv_count     = (int) get_comments_number();
$rv_commenter = wp_get_current_commenter();
$rv_require   = (bool) get_option('require_name_email');
$rv_req_mark  = $rv_require ? ' <span class="rvc-req" aria-hidden="true">*</span>' : '';
?>
<section id="comments" class="rv-comments rv-reading" aria-label="<?php esc_attr_e('Reader comments', 'sage'); ?>">

    <?php if (have_comments()) : ?>
        <header class="rv-comments-head">
            <h2 class="rv-comments-title">
                <?php
                if ($rv_count === 1) {
                    esc_html_e('One response', 'sage');
                } else {
                    printf(
                        /* translators: %s: number of comments. */
                        esc_html__('%s responses', 'sage'),
                        '<span class="rv-comments-count">' . esc_html(number_format_i18n($rv_count)) . '</span>'
                    );
                }
                ?>
            </h2>

            <?php if ($rv_count > 1) : ?>
                <div class="rv-comments-sort" role="group" aria-label="<?php esc_attr_e('Sort comments', 'sage'); ?>">
                    <button type="button" class="rv-sort-btn is-active" data-sort="oldest"><?php esc_html_e('Oldest', 'sage'); ?></button>
                    <button type="button" class="rv-sort-btn" data-sort="newest"><?php esc_html_e('Newest', 'sage'); ?></button>
                </div>
            <?php endif; ?>
        </header>

        <ol class="rv-comment-list comment-list">
            <?php
            wp_list_comments([
                'style'       => 'ol',
                'format'      => 'html5',
                'avatar_size' => 52,
                'short_ping'  => true,
                'reply_text'  => __('Reply', 'sage'),
            ]);
            ?>
        </ol>

        <?php
        the_comments_pagination([
            'prev_text'          => __('&larr; Older comments', 'sage'),
            'next_text'          => __('Newer comments &rarr;', 'sage'),
            'screen_reader_text' => __('Comments navigation', 'sage'),
            'class'              => 'rv-comments-pagination',
        ]);
        ?>
    <?php endif; ?>

    <?php if (! comments_open() && $rv_count > 0) : ?>
        <p class="rv-comments-closed">
            <?php esc_html_e('Comments are closed on this post — but you can always reach me directly if a question comes up.', 'sage'); ?>
        </p>
    <?php endif; ?>

    <?php
    if (comments_open()) :
        // Put the message box last (WordPress prepends it by default), so the
        // form reads name → email → website → message like most modern blogs.
        add_filter('comment_form_fields', function ($fields) {
            if (isset($fields['comment'])) {
                $comment = $fields['comment'];
                unset($fields['comment']);
                $fields['comment'] = $comment;
            }
            return $fields;
        });

        $rv_fields = [
            'author' => '<p class="rvc-field rvc-field-half comment-form-author">'
                . '<label for="author">' . esc_html__('Name', 'sage') . $rv_req_mark . '</label>'
                . '<input id="author" name="author" type="text" autocomplete="name" maxlength="245" value="'
                . esc_attr($rv_commenter['comment_author']) . '"' . ($rv_require ? ' required' : '') . '></p>',

            'email' => '<p class="rvc-field rvc-field-half comment-form-email">'
                . '<label for="email">' . esc_html__('Email', 'sage') . $rv_req_mark . '</label>'
                . '<input id="email" name="email" type="email" autocomplete="email" maxlength="100" value="'
                . esc_attr($rv_commenter['comment_author_email']) . '"' . ($rv_require ? ' required' : '') . '>'
                . '<span class="rvc-field-hint">' . esc_html__('Never shown publicly.', 'sage') . '</span></p>',

            'url' => '<p class="rvc-field comment-form-url">'
                . '<label for="url">' . esc_html__('Website', 'sage')
                . ' <span class="rvc-optional">' . esc_html__('optional', 'sage') . '</span></label>'
                . '<input id="url" name="url" type="url" autocomplete="url" maxlength="200" value="'
                . esc_attr($rv_commenter['comment_author_url']) . '"></p>',

            // Honeypot — visually hidden, ignored by people, filled by bots.
            // Namespaced (rvc_) so it never clashes with the contact form's own trap.
            'rvc_hp' => '<p class="rvc-hp" aria-hidden="true">'
                . '<label for="rvc_hp">' . esc_html__('Leave this field empty', 'sage') . '</label>'
                . '<input id="rvc_hp" name="rvc_hp" type="text" tabindex="-1" autocomplete="off" value=""></p>',

            'cookies' => '<p class="rvc-field rvc-field-check comment-form-cookies-consent">'
                . '<input id="wp-comment-cookies-consent" name="wp-comment-cookies-consent" type="checkbox" value="yes"'
                . (empty($rv_commenter['comment_author_email']) ? '' : ' checked') . '>'
                . '<label for="wp-comment-cookies-consent">' . esc_html__('Save my name and email in this browser for next time.', 'sage') . '</label></p>',
        ];

        $rv_comment_field = '<p class="rvc-field rvc-field-comment comment-form-comment">'
            . '<label for="comment">' . esc_html__('Comment', 'sage') . ' <span class="rvc-req" aria-hidden="true">*</span></label>'
            . '<textarea id="comment" name="comment" rows="6" maxlength="65525" required'
            . ' placeholder="' . esc_attr__('Share a thought, a question, or your own experience…', 'sage') . '"></textarea>'
            . '<span class="rvc-charcount" aria-hidden="true"></span></p>';

        comment_form([
            'class_form'           => 'rv-comment-form',
            'title_reply'          => __('Join the conversation', 'sage'),
            'title_reply_to'       => __('Reply to %s', 'sage'),
            'title_reply_before'   => '<h3 id="reply-title" class="rv-form-title">',
            'title_reply_after'    => '</h3>',
            'cancel_reply_before'  => ' <span class="rv-cancel-reply">',
            'cancel_reply_after'   => '</span>',
            'comment_notes_before' => '<p class="rv-form-note">'
                . esc_html__('Your email stays private. Comments are moderated and kind, on-topic notes are always welcome.', 'sage') . '</p>',
            'comment_notes_after'  => '',
            'label_submit'         => __('Post comment', 'sage'),
            'class_submit'         => 'rv-btn rv-btn-primary rv-comment-submit',
            'fields'               => $rv_fields,
            'comment_field'        => $rv_comment_field,
        ]);
    endif;
    ?>
</section>

<script>
(function () {
    var root = document.getElementById('comments');
    if (!root) return;

    /* Relative timestamps — keep the exact date on hover/focus via title. */
    var rel = function (ms) {
        var s = (Date.now() - ms) / 1000;
        if (s < 45) return 'just now';
        var m = s / 60; if (m < 60) return Math.round(m) + 'm ago';
        var h = m / 60; if (h < 24) return Math.round(h) + 'h ago';
        var d = h / 24; if (d < 7) return Math.round(d) + 'd ago';
        var w = d / 7;  if (w < 5) return Math.round(w) + 'w ago';
        var mo = d / 30; if (mo < 12) return Math.round(mo) + 'mo ago';
        return Math.round(d / 365) + 'y ago';
    };
    root.querySelectorAll('.comment-metadata time[datetime]').forEach(function (t) {
        var dt = new Date(t.getAttribute('datetime'));
        if (isNaN(dt)) return;
        if (!t.title) t.title = t.textContent.trim();
        t.textContent = rel(dt.getTime());
    });

    /* Newest / oldest sort — reorders top-level comments only. */
    var list = root.querySelector('.rv-comment-list');
    root.querySelectorAll('.rv-sort-btn').forEach(function (btn) {
        btn.addEventListener('click', function () {
            if (!list) return;
            var dir = btn.getAttribute('data-sort');
            root.querySelectorAll('.rv-sort-btn').forEach(function (b) {
                b.classList.toggle('is-active', b === btn);
                b.setAttribute('aria-pressed', b === btn ? 'true' : 'false');
            });
            var items = Array.prototype.slice.call(list.children);
            items.sort(function (a, b) {
                var ta = a.querySelector('time[datetime]'), tb = b.querySelector('time[datetime]');
                ta = ta ? ta.getAttribute('datetime') : '';
                tb = tb ? tb.getAttribute('datetime') : '';
                if (ta === tb) return 0;
                return (dir === 'newest') ? (ta < tb ? 1 : -1) : (ta < tb ? -1 : 1);
            });
            items.forEach(function (i) { list.appendChild(i); });
        });
    });

    /* Live character count under the message box. */
    var ta = document.getElementById('comment');
    var cc = root.querySelector('.rvc-charcount');
    if (ta && cc) {
        var upd = function () { cc.textContent = ta.value.length ? ta.value.length.toLocaleString() + ' characters' : ''; };
        ta.addEventListener('input', upd); upd();
    }
})();
</script>
