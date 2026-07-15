<?php
/**
 * Dev-only: seed preview pages for the pressroot templates that the theme's
 * own seed-pages.php doesn't cover, plus a few sample posts. Option-guarded,
 * non-destructive (skips any page whose slug already exists).
 */
add_action('admin_init', function () {
    if (get_option('prt_preview_seed_v1') || wp_get_theme()->get('TextDomain') !== 'pressroot') {
        return;
    }
    if (! current_user_can('edit_theme_options')) {
        return;
    }

    $pattern = function (string $slug): string {
        $p = \WP_Block_Patterns_Registry::get_instance()->get_registered($slug);
        return ($p && ! empty($p['content'])) ? $p['content'] : '';
    };

    // NOTE(fix): 'services'/'pricing'/'blog'/'contact' were removed from this
    // list — the AI Setup Assistant (app/ai-assistant.php) now owns those
    // exact page slugs with type-specific patterns. Seeding them here first
    // permanently blocked the Assistant's "don't touch existing pages" safety
    // check from ever creating its own versions. 'now' and
    // 'privacy-policy-preview' are untouched by the Assistant, so they stay.
    $pages = [
        ['slug' => 'now',       'title' => 'Now',       'template' => 'template-now.blade.php',       'pattern' => ''],
        ['slug' => 'privacy-policy-preview', 'title' => 'Privacy Policy', 'template' => 'template-legal.blade.php', 'pattern' => ''],
    ];

    $fallbacks = [
        'now' => '<!-- wp:paragraph --><p><strong>Updated ' . esc_html(date_i18n('F Y')) . '.</strong> Building a premium WordPress theme framework, deepening Laravel + WASM experiments, and writing more in the open.</p><!-- /wp:paragraph -->'
            . '<!-- wp:heading {"level":2} --><h2 class="wp-block-heading">Right now</h2><!-- /wp:heading -->'
            . '<!-- wp:list --><ul class="wp-block-list"><!-- wp:list-item --><li>Shipping Pressroot v1.2 — the Paper + Space design refresh.</li><!-- /wp:list-item --><!-- wp:list-item --><li>Reading <em>A Philosophy of Software Design</em>.</li><!-- /wp:list-item --><!-- wp:list-item --><li>Running three mornings a week.</li><!-- /wp:list-item --></ul><!-- /wp:list -->',
        'privacy-policy-preview' => '<!-- wp:paragraph --><p>This sample policy shows the Legal template typography. This site collects only the data needed to answer your messages: a name, an email address, and whatever you write in the form.</p><!-- /wp:paragraph -->'
            . '<!-- wp:heading {"level":2} --><h2 class="wp-block-heading">What we store</h2><!-- /wp:heading -->'
            . '<!-- wp:paragraph --><p>Contact form submissions are emailed and never sold. No analytics cookies are set without consent.</p><!-- /wp:paragraph -->'
            . '<!-- wp:heading {"level":2} --><h2 class="wp-block-heading">Your rights</h2><!-- /wp:heading -->'
            . '<!-- wp:paragraph --><p>Email hello@example.com to request a copy or deletion of your data at any time.</p><!-- /wp:paragraph -->',
    ];

    foreach ($pages as $def) {
        if (get_page_by_path($def['slug'])) {
            continue;
        }
        $body = $def['pattern'] ? $pattern($def['pattern']) : ($fallbacks[$def['slug']] ?? '');
        $id = wp_insert_post([
            'post_title'   => $def['title'],
            'post_name'    => $def['slug'],
            'post_type'    => 'page',
            'post_status'  => 'publish',
            'post_content' => $body,
        ]);
        if (! is_wp_error($id) && $def['template']) {
            update_post_meta($id, '_wp_page_template', $def['template']);
        }
    }

    // Blog: posts page + three sample posts using the single-post starter pattern.
    if (($blog = get_page_by_path('blog')) && ! get_option('page_for_posts')) {
        update_option('page_for_posts', $blog->ID);
        update_option('show_on_front', 'page');
    }
    if (! get_posts(['post_type' => 'post', 'numberposts' => 1, 'post_status' => 'publish', 'category' => 0])) {
        $starter = $pattern('pressroot/single-post');
        $samples = [
            ['Shipping a Sage theme on WASM PHP', 'wasm-php-sage', '-3 days'],
            ['Design tokens that survive a rebrand', 'design-tokens-rebrand', '-10 days'],
            ['Live GitHub data without a plugin', 'live-github-data', '-21 days'],
        ];
        foreach ($samples as [$title, $slug, $when]) {
            wp_insert_post([
                'post_title'   => $title,
                'post_name'    => $slug,
                'post_type'    => 'post',
                'post_status'  => 'publish',
                'post_date'    => gmdate('Y-m-d H:i:s', strtotime($when)),
                'post_content' => $starter ?: '<!-- wp:paragraph --><p>Sample post body for previewing the blog templates.</p><!-- /wp:paragraph -->',
            ]);
        }
    }

    update_option('prt_preview_seed_v1', 1);
});

/** v2: replace default WP content with sample posts. */
add_action('admin_init', function () {
    if (get_option('prt_preview_seed_v2') || wp_get_theme()->get('TextDomain') !== 'pressroot') {
        return;
    }
    if (! current_user_can('edit_theme_options')) {
        return;
    }
    foreach (['hello-world' => 'post', 'sample-page' => 'page'] as $slug => $type) {
        if ($p = get_page_by_path($slug, OBJECT, $type)) {
            wp_delete_post($p->ID, true);
        }
    }
    $reg = \WP_Block_Patterns_Registry::get_instance()->get_registered('pressroot/single-post');
    $starter = ($reg && ! empty($reg['content'])) ? $reg['content'] : '';
    $samples = [
        ['Shipping a Sage theme on WASM PHP', 'wasm-php-sage', '-3 days'],
        ['Design tokens that survive a rebrand', 'design-tokens-rebrand', '-10 days'],
        ['Live GitHub data without a plugin', 'live-github-data', '-21 days'],
    ];
    foreach ($samples as [$title, $slug, $when]) {
        if (get_page_by_path($slug, OBJECT, 'post')) {
            continue;
        }
        wp_insert_post([
            'post_title'   => $title,
            'post_name'    => $slug,
            'post_type'    => 'post',
            'post_status'  => 'publish',
            'post_date'    => gmdate('Y-m-d H:i:s', strtotime($when)),
            'post_content' => $starter ?: '<!-- wp:paragraph --><p>Sample post body for previewing the blog templates.</p><!-- /wp:paragraph -->',
        ]);
    }
    update_option('prt_preview_seed_v2', 1);
});

/** v3: reading settings, identity, permalinks. */
add_action('admin_init', function () {
    if (get_option('prt_preview_seed_v3') || wp_get_theme()->get('TextDomain') !== 'pressroot') {
        return;
    }
    if (! current_user_can('manage_options')) {
        return;
    }
    if ($home = get_page_by_path('home')) {
        update_option('show_on_front', 'page');
        update_option('page_on_front', $home->ID);
    }
    if ($blog = get_page_by_path('blog')) {
        update_option('page_for_posts', $blog->ID);
    }
    update_option('blogname', 'Matt Hummel');
    update_option('blogdescription', 'Full-Stack Developer');
    if (! get_option('permalink_structure')) {
        update_option('permalink_structure', '/%postname%/');
        flush_rewrite_rules();
    }
    update_option('prt_preview_seed_v3', 1);
});

/** v4: fill empty Resources page; de-duplicate Contact hero. */
add_action('admin_init', function () {
    if (get_option('prt_preview_seed_v4') || wp_get_theme()->get('TextDomain') !== 'pressroot') {
        return;
    }
    if (! current_user_can('edit_theme_options')) {
        return;
    }
    if (($res = get_page_by_path('resources')) && trim($res->post_content) === '') {
        $p = \WP_Block_Patterns_Registry::get_instance()->get_registered('pressroot/resources');
        if ($p && ! empty($p['content'])) {
            wp_update_post(['ID' => $res->ID, 'post_content' => $p['content']]);
        }
    }
    // NOTE(fix): skip pages the AI Setup Assistant created and owns — this
    // step used to unconditionally blank any 'contact' page's content, which
    // would wipe out a fresh Assistant-generated Contact page after a
    // database reset (see the matching notes on v1/v6/v11 in this file).
    if (($c = get_page_by_path('contact')) && ! get_post_meta($c->ID, '_prt_site_type', true) && trim($c->post_content) !== '') {
        wp_update_post(['ID' => $c->ID, 'post_content' => '']);
    }
    update_option('prt_preview_seed_v4', 1);
});

/** v5: refill Resources from resources-full; restore Contact template. */
add_action('admin_init', function () {
    if (get_option('prt_preview_seed_v5') || wp_get_theme()->get('TextDomain') !== 'pressroot') {
        return;
    }
    if (! current_user_can('edit_theme_options')) {
        return;
    }
    $reg = \WP_Block_Patterns_Registry::get_instance()->get_registered('pressroot/resources-full');
    if (($res = get_page_by_path('resources')) && $reg && ! empty($reg['content']) && trim(wp_strip_all_tags($res->post_content)) === '') {
        wp_update_post(['ID' => $res->ID, 'post_content' => $reg['content']]);
    }
    if ($c = get_page_by_path('contact')) {
        update_post_meta($c->ID, '_wp_page_template', 'template-contact.blade.php');
    }
    update_option('prt_preview_seed_v5', 1);
});

/** v6: re-apply patterns with wp_slash (unslashed insert corrupted \" JSON attrs). */
add_action('admin_init', function () {
    if (get_option('prt_preview_seed_v6') || wp_get_theme()->get('TextDomain') !== 'pressroot') {
        return;
    }
    if (! current_user_can('edit_theme_options')) {
        return;
    }
    // NOTE(fix): 'services'/'pricing'/'blog' removed from this map — those
    // slugs now belong to the AI Setup Assistant. Re-stamping them here with
    // the old generic patterns would overwrite type-specific Assistant
    // content on every fresh database reset. 'resources' isn't an Assistant
    // slug, so it's unaffected.
    $map = [
        'resources' => 'pressroot/resources-full',
    ];
    foreach ($map as $slug => $pat) {
        $reg = \WP_Block_Patterns_Registry::get_instance()->get_registered($pat);
        if (($page = get_page_by_path($slug)) && $reg && ! empty($reg['content'])) {
            wp_update_post(['ID' => $page->ID, 'post_content' => wp_slash($reg['content'])]);
        }
    }
    update_option('prt_preview_seed_v6', 1);
});

/** v7: pattern-first pages — refill Now + Privacy with their full-page patterns. */
add_action('admin_init', function () {
    if (get_option('prt_preview_seed_v7') || wp_get_theme()->get('TextDomain') !== 'pressroot') {
        return;
    }
    if (! current_user_can('edit_theme_options')) {
        return;
    }
    $map = [
        'now'                    => 'pressroot/now-full',
        'privacy-policy-preview' => 'pressroot/legal-full',
    ];
    foreach ($map as $slug => $pat) {
        $reg = \WP_Block_Patterns_Registry::get_instance()->get_registered($pat);
        if (($page = get_page_by_path($slug)) && $reg && ! empty($reg['content'])) {
            wp_update_post(['ID' => $page->ID, 'post_content' => wp_slash($reg['content'])]);
        }
    }
    update_option('prt_preview_seed_v7', 1);
});

/** v8: footer preview — create + assign footer navigation and legal menus. */
add_action('admin_init', function () {
    if (get_option('prt_preview_seed_v8') || wp_get_theme()->get('TextDomain') !== 'pressroot') {
        return;
    }
    if (! current_user_can('edit_theme_options')) {
        return;
    }
    $mk = function (string $name, array $items) {
        $menu = wp_get_nav_menu_object($name);
        $id = $menu ? $menu->term_id : wp_create_nav_menu($name);
        if (is_wp_error($id)) {
            return 0;
        }
        if (! $menu) {
            foreach ($items as [$title, $slug]) {
                $page = get_page_by_path($slug);
                if (! $page) {
                    continue;
                }
                wp_update_nav_menu_item($id, 0, [
                    'menu-item-title'     => $title,
                    'menu-item-object'    => 'page',
                    'menu-item-object-id' => $page->ID,
                    'menu-item-type'      => 'post_type',
                    'menu-item-status'    => 'publish',
                ]);
            }
        }
        return (int) $id;
    };
    $nav   = $mk('Footer Navigation', [
        ['Projects', 'projects'], ['About', 'about'], ['Résumé', 'resume'],
        ['Services', 'services'], ['Blog', 'blog'], ['Contact', 'contact'],
    ]);
    $legal = $mk('Footer Legal', [
        ['Privacy Policy', 'privacy-policy-preview'], ['Now', 'now'],
    ]);
    $locs = get_theme_mod('nav_menu_locations', []);
    if ($nav)   { $locs['footer_navigation'] = $nav; }
    if ($legal) { $locs['footer_legal'] = $legal; }
    set_theme_mod('nav_menu_locations', $locs);
    update_option('prt_preview_seed_v8', 1);
});

/** v9: move WP's default block widgets out of the footer sidebar. */
add_action('admin_init', function () {
    if (get_option('prt_preview_seed_v9') || wp_get_theme()->get('TextDomain') !== 'pressroot') {
        return;
    }
    if (! current_user_can('edit_theme_options')) {
        return;
    }
    $sw = get_option('sidebars_widgets', []);
    foreach (['sidebar-footer'] as $area) {
        if (! empty($sw[$area])) {
            $sw['wp_inactive_widgets'] = array_merge($sw['wp_inactive_widgets'] ?? [], $sw[$area]);
            $sw[$area] = [];
        }
    }
    update_option('sidebars_widgets', $sw);
    update_option('prt_preview_seed_v9', 1);
});

/** v10: reading settings, late + self-healing (v3 could run before Home existed). */
add_action('admin_init', function () {
    if (wp_get_theme()->get('TextDomain') !== 'pressroot' || ! current_user_can('manage_options')) {
        return;
    }
    if (get_option('show_on_front') !== 'page' || ! get_option('page_on_front')) {
        if ($home = get_page_by_path('home')) {
            update_option('show_on_front', 'page');
            update_option('page_on_front', $home->ID);
        }
    }
    if (! get_option('page_for_posts') && ($blog = get_page_by_path('blog'))) {
        update_option('page_for_posts', $blog->ID);
    }
}, 99);

/**
 * v11: re-apply Services/Pricing patterns (SR-only h2 heading fix).
 *
 * NOTE(fix): disabled — 'services'/'pricing' are now owned by the AI Setup
 * Assistant, and re-stamping the old generic patterns here would overwrite
 * type-specific Assistant content on every fresh database reset. The version
 * flag is still set so this stays a one-time no-op rather than re-running.
 */
add_action('admin_init', function () {
    if (get_option('prt_preview_seed_v11') || wp_get_theme()->get('TextDomain') !== 'pressroot') {
        return;
    }
    update_option('prt_preview_seed_v11', 1);
});
