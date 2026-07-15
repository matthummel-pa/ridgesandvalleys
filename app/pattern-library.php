<?php

/**
 * Pattern Library admin page.
 *
 * Adds Appearance → Pattern Library that shows:
 *  - All registered patterns in the 'pressroot' category
 *  - A link to the Synced Patterns (wp_block) editor
 *  - Block inserter tips
 *
 * Mirrors the UX of Kadence's "Design Library" but lives entirely
 * within WordPress with no external API calls.
 */

namespace App;

// Adds "Pattern Library" under Appearance. Uses 'edit_posts' (not
// 'edit_theme_options') as the capability since this page is read-only
// (browse/copy pattern names) — any contributor who can write content
// benefits from seeing what patterns exist, not just admins.
add_action('admin_menu', function () {
    add_theme_page(
        __('Pattern Library', 'pressroot'),
        __('Pattern Library', 'pressroot'),
        'edit_posts',
        'prt-pattern-library',
        __NAMESPACE__ . '\\prt_pattern_library_page'
    );
});

/**
 * Renders the Pattern Library admin page: a grid of every registered
 * 'pressroot' pattern (title, description, keywords, and a "copy pattern
 * name" button) plus a shortcut to Synced Patterns and a short how-to.
 *
 * Exists so a non-technical editor can discover what patterns are
 * available and how to insert them without needing to already know the
 * "Patterns" tab exists in the block inserter, or memorize pattern names —
 * this mirrors the UX of page-builder "design libraries" but needs no
 * external API since everything here is read from the local pattern
 * registry.
 *
 * @return void
 */
function prt_pattern_library_page(): void
{
    $all_patterns      = \WP_Block_Patterns_Registry::get_instance()->get_all_registered();
    // Only this theme's own patterns — core/plugin patterns aren't shown
    // here since they already have their own discovery UI in the inserter.
    $theme_patterns    = array_filter($all_patterns, function ($p) {
        return in_array('pressroot', $p['categories'] ?? [], true);
    });

    $synced_url = admin_url('edit.php?post_type=wp_block');
    ?>
    <div class="wrap prt-pattern-library">

        <h1 class="wp-heading-inline">
            <?php esc_html_e('Pattern Library', 'pressroot'); ?>
        </h1>
        <a href="<?php echo esc_url($synced_url); ?>" class="page-title-action">
            <?php esc_html_e('Manage Synced Patterns', 'pressroot'); ?>
        </a>
        <hr class="wp-header-end">

        <p class="description" style="margin-bottom:1.5rem;">
            <?php esc_html_e(
                'Pre-built block patterns for the Pressroot theme. Open any page in the block editor, click the + Inserter, then choose Patterns → Pressroot to insert these layouts.',
                'pressroot'
            ); ?>
        </p>

        <?php if (empty($theme_patterns)) : ?>
            <div class="notice notice-warning inline">
                <p><?php esc_html_e('No matthummel patterns registered yet.', 'pressroot'); ?></p>
            </div>
        <?php else : ?>

        <div class="prt-pl-grid" style="
            display:grid;
            grid-template-columns:repeat(auto-fill,minmax(300px,1fr));
            gap:1.5rem;
            margin-top:1.5rem;">

            <?php foreach ($theme_patterns as $pattern) :
                $name  = $pattern['name']  ?? '';
                $title = $pattern['title'] ?? $name;
                $desc  = $pattern['description'] ?? '';
                $kws   = implode(', ', $pattern['keywords'] ?? []);
            ?>
            <div class="prt-pl-card" style="
                background:#fff;
                border:1px solid #e0e0e0;
                border-radius:8px;
                overflow:hidden;
                display:flex;
                flex-direction:column;">

                <!-- Preview area -->
                <div class="prt-pl-preview" style="
                    background:#f6f7f7;
                    padding:1rem;
                    min-height:120px;
                    border-bottom:1px solid #e0e0e0;
                    display:flex;
                    align-items:center;
                    justify-content:center;">
                    <span style="font-size:2.5rem;">🧩</span>
                </div>

                <!-- Info -->
                <div style="padding:1rem 1.25rem 1.25rem;flex:1;display:flex;flex-direction:column;gap:.35rem;">
                    <strong style="font-size:.95rem;"><?php echo esc_html($title); ?></strong>
                    <?php if ($desc) : ?>
                        <p style="margin:0;font-size:.8rem;color:#666;"><?php echo esc_html($desc); ?></p>
                    <?php endif; ?>
                    <?php if ($kws) : ?>
                        <p style="margin:.25rem 0 0;font-size:.7rem;color:#999;">
                            <?php echo esc_html($kws); ?>
                        </p>
                    <?php endif; ?>

                    <!-- Copy button -->
                    <button
                        class="button button-secondary prt-copy-pattern"
                        data-pattern="<?php echo esc_attr($name); ?>"
                        style="margin-top:auto;width:100%;">
                        <?php esc_html_e('Copy pattern name', 'pressroot'); ?>
                    </button>
                </div>

            </div>
            <?php endforeach; ?>

        </div>
        <?php endif; ?>

        <!-- Synced patterns section -->
        <hr style="margin:2.5rem 0 1.5rem;">
        <h2><?php esc_html_e('Synced Patterns (Reusable Blocks)', 'pressroot'); ?></h2>
        <p class="description">
            <?php esc_html_e(
                'Synced Patterns let you save any block layout and reuse it across multiple pages. Editing the original updates it everywhere it appears — perfect for headers, CTAs, and footers.',
                'pressroot'
            ); ?>
        </p>
        <p>
            <a href="<?php echo esc_url($synced_url); ?>" class="button button-primary">
                <?php esc_html_e('Manage Synced Patterns →', 'pressroot'); ?>
            </a>
        </p>

        <!-- Quick tips -->
        <hr style="margin:2.5rem 0 1.5rem;">
        <h2><?php esc_html_e('How to use patterns', 'pressroot'); ?></h2>
        <ol style="max-width:640px;line-height:1.8;">
            <li><?php esc_html_e('Open any page or post in the block editor.', 'pressroot'); ?></li>
            <li><?php esc_html_e('Click the blue + button (top-left or in the document body).', 'pressroot'); ?></li>
            <li><?php esc_html_e('Switch to the "Patterns" tab.', 'pressroot'); ?></li>
            <li><?php esc_html_e('Choose "Pressroot" from the category list.', 'pressroot'); ?></li>
            <li><?php esc_html_e('Click any pattern to insert it, then customise the content.', 'pressroot'); ?></li>
        </ol>

    </div>

    <?php /* Inline (not enqueued) since this markup/script only exists on this one
             admin page and is trivial — not worth a separate asset + wp_enqueue_script. */ ?>
    <script>
    (function () {
        document.querySelectorAll('.prt-copy-pattern').forEach(function (btn) {
            btn.addEventListener('click', function () {
                var name = this.getAttribute('data-pattern');
                navigator.clipboard.writeText(name).then(function () {
                    btn.textContent = '✓ Copied!';
                    setTimeout(function () {
                        btn.textContent = '<?php echo esc_js(__('Copy pattern name', 'pressroot')); ?>';
                    }, 1800);
                });
            });
        });
    })();
    </script>
    <?php
}
