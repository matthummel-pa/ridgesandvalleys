<?php

/**
 * One-time page seeding.
 *
 * On theme activation (and once for already-active installs), pre-fills the key
 * pages with their designed block patterns so they're ready to view and edit:
 *
 *   Home      → pressroot/home-full       (set as the static front page)
 *   About     → pressroot/about-full      (Canvas template)
 *   Résumé    → pressroot/resume-full     (Canvas template)
 *   Resources → pressroot/resources  (Canvas template)
 *
 * NON-DESTRUCTIVE: an existing page is only filled if its content is empty, and
 * its template is only changed when we fill it — so real content and settings
 * are never clobbered. Contact is intentionally skipped (its designed template
 * already renders the working form).
 */

namespace App;

/**
 * Build a page's markup from a registered block pattern.
 *
 * Looks the pattern up at call time (not cached) since patterns are
 * registered on 'init' by home-patterns.php / pattern-library.php and this
 * function is only ever invoked later, from 'admin_init' callbacks below.
 *
 * @param string $slug Registered pattern name, e.g. 'pressroot/home-full'.
 * @return string Pattern content HTML, or '' if the pattern isn't registered.
 */
function prt_seed_pattern_content(string $slug): string
{
    if (! class_exists('WP_Block_Patterns_Registry')) {
        return '';
    }
    $p = \WP_Block_Patterns_Registry::get_instance()->get_registered($slug);
    return ($p && ! empty($p['content'])) ? $p['content'] : '';
}

/**
 * Find an existing page by any of the given slugs.
 *
 * Accepts multiple candidate slugs (not just one) because some pages were
 * renamed over the site's lifetime (e.g. Resources used to live at
 * 'power-platform-learning-resources') — checking all known slugs avoids
 * creating a duplicate page for installs that still use the old one.
 *
 * @param string[] $slugs Candidate page slugs to check, in priority order.
 * @return \WP_Post|null The first matching page, or null if none exist.
 */
function prt_seed_find_page(array $slugs)
{
    foreach ($slugs as $slug) {
        $page = get_page_by_path($slug);
        if ($page) {
            return $page;
        }
    }
    return null;
}

/**
 * Seed (or top-up) the four core marketing pages with their designed block
 * patterns, and set Home as the static front page.
 *
 * This is the theme's single "make the site look right on first activation"
 * entry point — it's what turns a bare WordPress install into the fully
 * designed matthummel site without the admin having to manually create
 * pages or hunt for the right pattern. Safe to call repeatedly: existing
 * pages are only touched if their content is still empty (see the
 * `trim(...) === ''` guard below), so nothing here ever clobbers real edits.
 *
 * @return void
 */
function prt_seed_pages_now(): void
{
    // NOTE(audit): none of these page definitions set a 'template' key, so
    // the `! empty($def['template'])` checks below (and the matching
    // update_post_meta() calls) are always false and never run. Looks like
    // dead code left over from an earlier version that set page templates
    // per page; template assignment is instead handled by the separate
    // "detach from Canvas template" pass further down this file.
    $pages = [
        'home' => [
            'slugs'    => ['home'],
            'title'    => __('Home', 'pressroot'),
            'pattern'  => 'pressroot/home-full',
            'front'    => true,
        ],
        'about' => [
            'slugs'    => ['about'],
            'title'    => __('About', 'pressroot'),
            'pattern'  => 'pressroot/about-full',
        ],
        'resume' => [
            'slugs'    => ['resume'],
            'title'    => __('Résumé', 'pressroot'),
            'pattern'  => 'pressroot/resume-full',
        ],
        'resources' => [
            'slugs'    => ['resources', 'power-platform-learning-resources'],
            'title'    => __('Resources', 'pressroot'),
            'pattern'  => 'pressroot/resources-full',
        ],
    ];

    $homeId = 0;

    foreach ($pages as $def) {
        $body     = prt_seed_pattern_content($def['pattern']);
        $existing = prt_seed_find_page($def['slugs']);

        if ($existing) {
            $id = (int) $existing->ID;
            if (trim((string) $existing->post_content) === '' && $body !== '') {
                wp_update_post(['ID' => $id, 'post_content' => wp_slash($body)]);
                if (! empty($def['template'])) {
                    update_post_meta($id, '_wp_page_template', $def['template']);
                }
            }
        } else {
            $id = wp_insert_post([
                'post_title'   => $def['title'],
                'post_name'    => $def['slugs'][0],
                'post_status'  => 'publish',
                'post_type'    => 'page',
                'post_content' => wp_slash($body !== '' ? $body : '<!-- wp:paragraph --><p></p><!-- /wp:paragraph -->'),
            ]);
            if (! is_wp_error($id) && ! empty($def['template'])) {
                update_post_meta($id, '_wp_page_template', $def['template']);
            }
        }

        if (! empty($def['front']) && ! empty($id) && ! is_wp_error($id)) {
            $homeId = (int) $id;
        }
    }

    // Set the static front page only if one isn't already configured.
    if ($homeId && get_option('show_on_front') !== 'page') {
        update_option('show_on_front', 'page');
        update_option('page_on_front', $homeId);
        $blog = get_page_by_path('blog');
        if ($blog) {
            update_option('page_for_posts', (int) $blog->ID);
        }
    }
}

/** Re-arm seeding whenever the theme is (re)activated. */
add_action('after_switch_theme', function () {
    delete_option('prt_pages_seeded_v1');
});

// Run once, in the admin, after block patterns are registered (init:12).
// This is the first of several "one-time task" admin_init callbacks in this
// file; they all share the same shape: a versioned `prt_*_v#` option acts as
// a run-once flag (checked first, set last), and admin_init is used instead
// of a dedicated activation hook because patterns/CPTs/taxonomies registered
// on 'init' aren't guaranteed ready yet during after_switch_theme itself.
// Bumping the option's `_v#` suffix (e.g. v1 -> v2) is the mechanism for
// intentionally re-running a task after the underlying pattern/logic changes.
add_action('admin_init', function () {
    if (get_option('prt_pages_seeded_v1')) {
        return;
    }
    if (! current_user_can('edit_theme_options')) {
        return;
    }
    prt_seed_pages_now();
    update_option('prt_pages_seeded_v1', 1);
});

/**
 * One-time refresh: add the "Latest from the blog" section to an already-seeded
 * About page. Only touches our auto-seeded version (it contains the stat-strip
 * block and no post-grid yet), so manual edits are left untouched.
 */
add_action('admin_init', function () {
    if (get_option('prt_about_blog_v1')) {
        return;
    }
    if (! current_user_can('edit_theme_options')) {
        return;
    }
    $about = prt_seed_find_page(['about']);
    if ($about) {
        $content = (string) $about->post_content;
        if (strpos($content, 'wp:prt/stat-strip') !== false && strpos($content, 'wp:prt/post-grid') === false) {
            $fresh = prt_seed_pattern_content('pressroot/about-full');
            if ($fresh !== '') {
                wp_update_post(['ID' => (int) $about->ID, 'post_content' => wp_slash($fresh)]);
            }
        }
    }
    update_option('prt_about_blog_v1', 1);
});

/**
 * One-time refresh (v2): re-apply the corrected full-page patterns to seeded
 * pages so they pick up fixes (e.g. valid core button markup) without manual
 * re-insertion. Only pages that still contain our auto-seeded signature block
 * are touched, so hand-edited pages are left alone.
 */
add_action('admin_init', function () {
    if (get_option('prt_pages_refresh_v4')) {
        return;
    }
    if (! current_user_can('edit_theme_options')) {
        return;
    }
    $targets = [
        ['slugs' => ['about'],                                       'pattern' => 'pressroot/about-full',     'sig' => 'wp:prt/stat-strip'],
        ['slugs' => ['resume'],                                      'pattern' => 'pressroot/resume-full',     'sig' => 'wp:prt/timeline'],
        ['slugs' => ['resources', 'power-platform-learning-resources'], 'pattern' => 'pressroot/resources-full', 'sig' => 'wp:prt/resource-group'],
        ['slugs' => ['home'],                                        'pattern' => 'pressroot/home-full',       'sig' => 'wp:prt/repo-grid'],
    ];
    foreach ($targets as $t) {
        $page = prt_seed_find_page($t['slugs']);
        if (! $page || strpos((string) $page->post_content, $t['sig']) === false) {
            continue;
        }
        $fresh = prt_seed_pattern_content($t['pattern']);
        if ($fresh !== '') {
            wp_update_post(['ID' => (int) $page->ID, 'post_content' => wp_slash($fresh)]);
        }
    }
    update_option('prt_pages_refresh_v4', 1);
});

/**
 * One-time: detach seeded pages from the custom "Canvas" page template and let
 * them use the default page template (the page-header partial now hides the
 * duplicate title on designed pattern pages). Avoids any dependency on the
 * custom template being registered/compiled by Acorn.
 */
add_action('admin_init', function () {
    if (get_option('prt_pages_template_reset_v1')) {
        return;
    }
    if (! current_user_can('edit_theme_options')) {
        return;
    }
    foreach ([['about'], ['resume'], ['resources', 'power-platform-learning-resources']] as $slugs) {
        $page = prt_seed_find_page($slugs);
        if ($page && get_post_meta($page->ID, '_wp_page_template', true) === 'template-canvas.blade.php') {
            delete_post_meta($page->ID, '_wp_page_template');
        }
    }
    update_option('prt_pages_template_reset_v1', 1);
});

/**
 * One-time: create the two project categories — "Side Quests" (dev experiments
 * / GitHub) and "Selected Work" (WordPress & web-design builds).
 */
add_action('admin_init', function () {
    if (get_option('prt_project_terms_v1')) {
        return;
    }
    if (! current_user_can('edit_theme_options') || ! taxonomy_exists('project_categories')) {
        return;
    }
    foreach (['Side Quests' => 'side-quests', 'Selected Work' => 'selected-work'] as $name => $slug) {
        if (! term_exists($slug, 'project_categories')) {
            wp_insert_term($name, 'project_categories', ['slug' => $slug]);
        }
    }
    update_option('prt_project_terms_v1', 1);
});

/**
 * One-time: seed the Projects and Résumé pages with their patterns (only if the
 * page is empty), detaching them from any custom template so the pattern renders.
 */
add_action('admin_init', function () {
    if (get_option('prt_seed_proj_resume_v4')) {
        return;
    }
    if (! current_user_can('edit_theme_options')) {
        return;
    }
    $map = [
        ['slugs' => ['projects'], 'title' => __('Projects', 'pressroot'), 'pattern' => 'pressroot/projects-full'],
        ['slugs' => ['resume'],   'title' => __('Résumé', 'pressroot'),   'pattern' => 'pressroot/resume-full'],
    ];
    foreach ($map as $def) {
        $body = prt_seed_pattern_content($def['pattern']);
        if ($body === '') {
            continue;
        }
        $page = prt_seed_find_page($def['slugs']);
        if (! $page) {
            wp_insert_post([
                'post_title'   => $def['title'],
                'post_name'    => $def['slugs'][0],
                'post_status'  => 'publish',
                'post_type'    => 'page',
                'post_content' => wp_slash($body),
            ]);
        } else {
            // Apply the current pattern and use the default template (one-time).
            wp_update_post(['ID' => (int) $page->ID, 'post_content' => wp_slash($body)]);
            delete_post_meta((int) $page->ID, '_wp_page_template');
        }
    }
    update_option('prt_seed_proj_resume_v4', 1);
});

/**
 * One-time fix: dynamic blocks with JSON-string attributes (stat-strip, skills,
 * timeline) lost their escaping when earlier seeding saved unslashed content,
 * so they rendered empty. Re-apply the affected pages' patterns with wp_slash()
 * so the block-comment JSON survives the save.
 */
add_action('admin_init', function () {
    if (get_option('prt_pages_reslash_v1')) {
        return;
    }
    if (! current_user_can('edit_theme_options')) {
        return;
    }
    $map = [
        ['slugs' => ['about'],  'pattern' => 'pressroot/about-full'],
        ['slugs' => ['resume'], 'pattern' => 'pressroot/resume-full'],
    ];
    foreach ($map as $def) {
        $page = prt_seed_find_page($def['slugs']);
        if (! $page) {
            continue;
        }
        $body = prt_seed_pattern_content($def['pattern']);
        if ($body !== '') {
            wp_update_post(['ID' => (int) $page->ID, 'post_content' => wp_slash($body)]);
            delete_post_meta((int) $page->ID, '_wp_page_template');
        }
    }
    update_option('prt_pages_reslash_v1', 1);
});

/**
 * One-time: sync the owner's public GitHub repos into the `projects` CPT under
 * the "Side Quests" category, so each repo gets an on-site single page that
 * pulls the full repo profile. Retries on a later load if GitHub is unreachable.
 */
add_action('admin_init', function () {
    if (get_option('prt_gh_projects_synced_v1')) {
        return;
    }
    if (! current_user_can('edit_theme_options') || ! post_type_exists('projects') || ! taxonomy_exists('project_categories')) {
        return;
    }
    $owner = apply_filters('pressroot/github_owner', 'matthummel-pa');
    $repos = \App\Github::fetchRepos($owner, 12, 'updated');
    if (empty($repos)) {
        return; // GitHub not reachable right now — don't set the flag, try again next load.
    }

    $term   = term_exists('side-quests', 'project_categories') ?: wp_insert_term('Side Quests', 'project_categories', ['slug' => 'side-quests']);
    $termId = is_array($term) ? (int) $term['term_id'] : (int) $term;

    foreach ($repos as $r) {
        $slug     = sanitize_title($r['name']);
        $existing = get_page_by_path($slug, OBJECT, 'projects');
        if ($existing) {
            $pid = (int) $existing->ID;
        } else {
            $pid = wp_insert_post([
                'post_type'    => 'projects',
                'post_status'  => 'publish',
                'post_title'   => $r['name'],
                'post_name'    => $slug,
                'post_excerpt' => (string) ($r['desc'] ?? ''),
            ]);
        }
        if ($pid && ! is_wp_error($pid)) {
            update_post_meta($pid, '_prt_gh_owner', $owner);
            update_post_meta($pid, '_prt_gh_repo', $r['name']);
            update_post_meta($pid, '_prt_eyebrow', 'Side Quest');
            if ($termId) {
                wp_set_object_terms($pid, [$termId], 'project_categories', false);
            }
        }
    }
    update_option('prt_gh_projects_synced_v1', 1);
});

/**
 * On-demand re-seed: push the CURRENT pattern markup into the live pages.
 *
 * Seeded pages store a frozen copy of the pattern in the database, so edits to
 * the pattern files don't appear until the page content is refreshed. Visit any
 * admin URL with ?prt_reseed=1 (all main pages) or ?prt_reseed=projects (one page)
 * to overwrite the page content with the latest pattern. edit_theme_options only.
 */
add_action('admin_init', function () {
    if (empty($_GET['prt_reseed']) || ! current_user_can('edit_theme_options')) {
        return;
    }

    $map = [
        'home'      => ['slugs' => ['home'],                                        'pattern' => 'pressroot/home-full'],
        'projects'  => ['slugs' => ['projects'],                                    'pattern' => 'pressroot/projects-full'],
        'about'     => ['slugs' => ['about'],                                       'pattern' => 'pressroot/about-full'],
        'resume'    => ['slugs' => ['resume'],                                      'pattern' => 'pressroot/resume-full'],
        'services'  => ['slugs' => ['services'],                                    'pattern' => 'pressroot/services-full'],
        'pricing'   => ['slugs' => ['pricing'],                                     'pattern' => 'pressroot/pricing-full'],
        'contact'   => ['slugs' => ['contact'],                                     'pattern' => 'pressroot/contact-full'],
        'resources' => ['slugs' => ['resources', 'power-platform-learning-resources'], 'pattern' => 'pressroot/resources-full'],
    ];

    $which = sanitize_key((string) $_GET['prt_reseed']);
    $todo  = ($which === '1' || $which === '' || ! isset($map[$which])) ? $map : [$which => $map[$which]];

    $done = 0;
    foreach ($todo as $def) {
        $body = prt_seed_pattern_content($def['pattern']);
        if ($body === '') {
            continue;
        }
        $page = prt_seed_find_page($def['slugs']);
        if (! $page) {
            continue;
        }
        wp_update_post(['ID' => (int) $page->ID, 'post_content' => wp_slash($body)]);
        delete_post_meta((int) $page->ID, '_wp_page_template');
        $done++;
    }

    wp_safe_redirect(add_query_arg('prt_reseeded', $done, admin_url()));
    exit;
});
