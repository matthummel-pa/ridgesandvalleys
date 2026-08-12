<?php

/**
 * Starter content — Ridges & Valleys Studio.
 *
 * On the first admin load, creates the site's pages (with their templates), sets
 * the front + blog pages, builds the Primary and Footer menus, adds blog
 * categories, and publishes a few SEO/marketing starter posts. Runs once
 * (guarded by an option) and is idempotent (existing items are matched by slug,
 * never duplicated). Also runs on theme activation.
 */

namespace App;

/* ---------------------------------------------------------------- pages ----- */

function rv_starter_page_map(): array
{
    return [
        'home'          => ['Home', ''],
        'about'         => ['About', 'template-about.blade.php'],
        'services'      => ['Services', 'template-services.blade.php'],
        'work'          => ['Work', 'template-work.blade.php'],
        'faq'           => ['FAQ', 'template-faq.blade.php'],
        'free-tools'     => ['Free Tools', 'template-tools.blade.php'],
        'website-grader'   => ['Website Grader', 'template-grader.blade.php'],
        'seo-checker'      => ['SEO Checker', 'template-seo.blade.php'],
        'security-checker' => ['Security Checker', 'template-security.blade.php'],
        'email-checker'    => ['Email Deliverability Checker', 'template-email.blade.php'],
        'local-seo'        => ['Local SEO Scorecard', 'template-local.blade.php'],
        'contact'          => ['Contact', 'template-contact.blade.php'],
        'accessibility'  => ['Accessibility', 'template-accessibility.blade.php'],
        'journal'        => ['Journal', ''],
    ];
}

function rv_ensure_page(string $slug, string $title, string $template): int
{
    $existing = get_page_by_path($slug);
    if ($existing) {
        $id = (int) $existing->ID;
    } else {
        $id = (int) wp_insert_post([
            'post_type'    => 'page',
            'post_status'  => 'publish',
            'post_title'   => $title,
            'post_name'    => $slug,
            'post_content' => '',
        ]);
    }
    if ($id && $template) {
        update_post_meta($id, '_wp_page_template', $template);
    }
    return $id;
}

/* ---------------------------------------------------------------- menus ----- */

function rv_ensure_menu(string $name, string $location, array $page_ids): void
{
    $menu = wp_get_nav_menu_object($name);
    if (! $menu) {
        $menu_id = wp_create_nav_menu($name);
    } else {
        $menu_id = (int) $menu->term_id;
        // Only (re)build if empty, so we never duplicate a user's edits.
        if (! empty(wp_get_nav_menu_items($menu_id))) {
            rv_assign_menu_location($menu_id, $location);
            return;
        }
    }
    if (is_wp_error($menu_id)) {
        return;
    }
    foreach ($page_ids as $pid) {
        if ($pid) {
            wp_update_nav_menu_item($menu_id, 0, [
                'menu-item-object-id' => $pid,
                'menu-item-object'    => 'page',
                'menu-item-type'      => 'post_type',
                'menu-item-status'    => 'publish',
            ]);
        }
    }
    rv_assign_menu_location($menu_id, $location);
}

function rv_assign_menu_location(int $menu_id, string $location): void
{
    $locations = get_theme_mod('nav_menu_locations', []);
    $locations[$location] = $menu_id;
    set_theme_mod('nav_menu_locations', $locations);
}

/* --------------------------------------------------------- categories ------ */

function rv_ensure_category(string $name, string $slug, string $desc = ''): int
{
    $term = get_term_by('slug', $slug, 'category');
    if ($term) {
        return (int) $term->term_id;
    }
    $res = wp_insert_term($name, 'category', ['slug' => $slug, 'description' => $desc]);
    return is_wp_error($res) ? 0 : (int) $res['term_id'];
}

/* --------------------------------------------------------- blog posts ------ */

function rv_starter_posts(): array
{
    $cta = "\n\n<p><strong>Want a hand?</strong> Ridges &amp; Valleys Studio builds fast, accessible websites for Gettysburg and South Central PA businesses. <a href=\"/contact/\">Tell me about your business</a> and I'll tell you exactly what I'd do — no jargon, no pressure.</p>";

    return [
        [
            'title'    => 'How much does a small-business website cost in Pennsylvania?',
            'slug'     => 'small-business-website-cost-pennsylvania',
            'category' => 'money-decisions',
            'excerpt'  => 'A plain-English breakdown of what a professional local website actually costs in South Central PA — and what drives the number up or down.',
            'content'  => "<p><strong>Short answer:</strong> most small-business websites in South Central PA land somewhere between a few hundred and a few thousand dollars, plus a small monthly cost to keep it online and healthy. The range is wide because \"a website\" can mean very different things.</p>\n<h2>What you're actually paying for</h2>\n<p>A price tag usually covers some mix of design, the pages themselves, copywriting, setup (domain, hosting, email), and getting you found on Google. The more of those you hand off, the higher the number — and the less of your own time it costs you.</p>\n<h2>What moves the price</h2>\n<ul><li><strong>Number of pages.</strong> A tidy five-page site costs less than a twenty-page site with a blog and town-by-town landing pages.</li><li><strong>Custom vs. template.</strong> A design built around your business reads better than a stock template — and helps you stand out from the shop down the road using the same theme.</li><li><strong>Content.</strong> If you have photos and copy ready, that saves time. If you need writing and images, budget for it.</li><li><strong>Local SEO.</strong> Getting into the Google map pack (Google Business Profile, local pages, reviews) is where a lot of the real value is for a local business.</li></ul>\n<h2>Don't forget the ongoing cost</h2>\n<p>Hosting, backups, and updates run a modest amount each month. Skipping them is how sites get slow, broken, or hacked. A care plan keeps the thing you paid for actually working.</p>\n<h2>The honest version</h2>\n<p>Fixed-price packages beat hourly guesswork for a small business — you should know the number before anyone starts. If you want a ballpark right now, the <a href=\"/free-tools/\">project estimator</a> on our tools page gives you a range in about ten seconds.</p>" . $cta,
        ],
        [
            'title'    => 'How to show up on Google Maps for your Gettysburg business',
            'slug'     => 'show-up-google-maps-gettysburg-business',
            'category' => 'getting-found',
            'excerpt'  => 'The map pack is where local customers actually start. Here is the free, do-it-yourself path to showing up for "near me" searches in Adams County.',
            'content'  => "<p>When someone nearby searches for what you sell, Google shows a little map with three businesses pinned on it — the \"map pack.\" For a local business, that block is prime real estate, and you can earn a spot in it without spending a dime.</p>\n<h2>1. Claim your Google Business Profile</h2>\n<p>This is the single most important local lever, and it's free. Search your business name, claim or create the profile, and verify it. This is what makes you eligible to appear on the map at all.</p>\n<h2>2. Fill it out completely</h2>\n<p>Pick the most accurate category, set your service area (Gettysburg, Adams County, and the nearby towns you serve), add your hours, and write a real description. Add good photos — profiles with photos get noticeably more clicks and calls.</p>\n<h2>3. Get reviews, deliberately</h2>\n<p>Reviews are the currency of local search and trust. Ask every happy customer, in writing, for a Google review and a sentence you can quote. A steady trickle of honest reviews beats a burst of them.</p>\n<h2>4. Keep your name, address, and phone consistent</h2>\n<p>List the exact same details on your website, your profile, and a handful of directories (Bing Places, the Chamber of Commerce, a couple of PA business listings). Consistency matters more than quantity.</p>\n<h2>5. Back it with a fast, local website</h2>\n<p>Your website and your map profile work together. Town-specific pages (\"web design in Hanover,\" \"in Littlestown\") and a site that loads fast on a phone both help you rank — and give people a reason to choose you once they find you.</p>" . $cta,
        ],
        [
            'title'    => 'Hire a local web designer or use Wix? An honest comparison',
            'slug'     => 'hire-local-web-designer-or-use-wix',
            'category' => 'money-decisions',
            'excerpt'  => 'DIY builders are cheap and quick. A local designer costs more but saves your time and gets you found. Here is how to decide — without the sales pitch.',
            'content'  => "<p>If you run a small business, you've probably wondered whether to build your own site on Wix or Squarespace, or hire someone. Both are legitimate. Here's the honest trade-off.</p>\n<h2>When a DIY builder makes sense</h2>\n<p>If your budget is tight, you enjoy tinkering, and you mostly need a simple online business card, a builder can get you live this weekend. That's a real option — don't let anyone shame you out of it.</p>\n<h2>Where DIY quietly costs you</h2>\n<ul><li><strong>Your time.</strong> The subscription is cheap; the hours you spend fighting the editor are not.</li><li><strong>Getting found.</strong> Builders make a page. They don't do the local-SEO groundwork that gets you into the Google map pack.</li><li><strong>Speed and accessibility.</strong> Template sites are often slow and hard to use for people with disabilities — both hurt your ranking and turn away customers.</li><li><strong>Looking like everyone else.</strong> The shop down the street is probably using the same template.</li></ul>\n<h2>What a local designer actually adds</h2>\n<p>A designer who knows the area handles the whole thing — design, copy, setup, accessibility, and getting you found — so you can run your business. And \"local\" means a real person you can reach, not a support ticket in another time zone.</p>\n<h2>A fair way to decide</h2>\n<p>If the website is a nice-to-have, DIY is fine. If it's a real source of customers — bookings, calls, jobs — it's worth having it built right and built to be found. That's the whole reason this studio exists.</p>" . $cta,
        ],
    ];
}

function rv_ensure_posts(): void
{
    foreach (rv_starter_posts() as $p) {
        if (get_page_by_path($p['slug'], OBJECT, 'post')) {
            continue;
        }
        $cat_map = [
            'money-decisions' => rv_ensure_category('Money & Decisions', 'money-decisions', 'Straight answers on cost, options, and getting the most from your website budget.'),
            'getting-found'   => rv_ensure_category('Getting Found', 'getting-found', 'Local SEO, Google Business Profile, and how nearby customers find you.'),
        ];
        $post_id = wp_insert_post([
            'post_type'    => 'post',
            'post_status'  => 'publish',
            'post_title'   => $p['title'],
            'post_name'    => $p['slug'],
            'post_excerpt' => $p['excerpt'],
            'post_content' => $p['content'],
        ]);
        if ($post_id && ! is_wp_error($post_id) && ! empty($cat_map[$p['category']])) {
            wp_set_post_categories($post_id, [$cat_map[$p['category']]]);
        }
    }
}

/* ---------------------------------------------------------------- run ------- */

function rv_run_starter(): void
{
    $ids = [];
    foreach (rv_starter_page_map() as $slug => [$title, $tpl]) {
        $ids[$slug] = rv_ensure_page($slug, $title, $tpl);
    }

    update_option('show_on_front', 'page');
    if (! empty($ids['home'])) {
        update_option('page_on_front', $ids['home']);
    }
    if (! empty($ids['journal'])) {
        update_option('page_for_posts', $ids['journal']);
    }

    rv_ensure_menu('Primary', 'primary', [$ids['about'], $ids['services'], $ids['work'], $ids['faq'], $ids['free-tools'], $ids['contact']]);
    rv_ensure_menu('Footer', 'footer', [$ids['services'], $ids['work'], $ids['journal'], $ids['accessibility'], $ids['contact']]);
    rv_ensure_menu('Tools', 'tools', [$ids['free-tools'], $ids['website-grader'], $ids['seo-checker'], $ids['security-checker'], $ids['email-checker'], $ids['local-seo']]);

    rv_ensure_posts();

    if (! get_option('permalink_structure')) {
        update_option('permalink_structure', '/%postname%/');
    }
    flush_rewrite_rules();
}

// First admin load (guard set up-front to avoid re-entry), and on activation.
add_action('admin_init', function () {
    if (get_option('rv_starter_v1')) {
        return;
    }
    update_option('rv_starter_v1', 1);
    rv_run_starter();
    set_transient('rv_starter_notice', 1, 120);
});

add_action('after_switch_theme', function () {
    if (! get_option('rv_starter_v1')) {
        update_option('rv_starter_v1', 1);
        rv_run_starter();
        set_transient('rv_starter_notice', 1, 120);
    }
});

/**
 * Ensure a page exists (by slug) and is linked in the Footer menu. Idempotent —
 * used by the migrations below to add tool pages to sites set up before them.
 */
function rv_ensure_footer_page(string $slug, string $title, string $tpl): void
{
    $id = rv_ensure_page($slug, $title, $tpl);
    if (! $id) {
        return;
    }
    $menu = wp_get_nav_menu_object('Footer');
    if ($menu) {
        $linked = false;
        foreach ((wp_get_nav_menu_items($menu->term_id) ?: []) as $it) {
            if ((int) $it->object_id === $id && $it->object === 'page') {
                $linked = true;
                break;
            }
        }
        if (! $linked) {
            wp_update_nav_menu_item($menu->term_id, 0, [
                'menu-item-object-id' => $id,
                'menu-item-object'    => 'page',
                'menu-item-type'      => 'post_type',
                'menu-item-status'    => 'publish',
            ]);
        }
    }
    flush_rewrite_rules();
}

// v2 — Website Grader page (added after initial setup on existing sites).
add_action('admin_init', function () {
    if (get_option('rv_starter_v2')) {
        return;
    }
    update_option('rv_starter_v2', 1);
    rv_ensure_footer_page('website-grader', __('Website Grader', 'sage'), 'template-grader.blade.php');
});

// v3 — SEO Checker page.
add_action('admin_init', function () {
    if (get_option('rv_starter_v3')) {
        return;
    }
    update_option('rv_starter_v3', 1);
    rv_ensure_footer_page('seo-checker', __('SEO Checker', 'sage'), 'template-seo.blade.php');
});

// v4 — Security Checker page.
add_action('admin_init', function () {
    if (get_option('rv_starter_v4')) {
        return;
    }
    update_option('rv_starter_v4', 1);
    rv_ensure_footer_page('security-checker', __('Security Checker', 'sage'), 'template-security.blade.php');
});

// v5 — Email Deliverability Checker page.
add_action('admin_init', function () {
    if (get_option('rv_starter_v5')) {
        return;
    }
    update_option('rv_starter_v5', 1);
    rv_ensure_footer_page('email-checker', __('Email Deliverability Checker', 'sage'), 'template-email.blade.php');
});

// v6 — Local SEO Scorecard page.
add_action('admin_init', function () {
    if (get_option('rv_starter_v6')) {
        return;
    }
    update_option('rv_starter_v6', 1);
    rv_ensure_footer_page('local-seo', __('Local SEO Scorecard', 'sage'), 'template-local.blade.php');
});

/**
 * Force-(re)build a menu by name: clears its items, adds the given pages in
 * order, and assigns it to a theme location. Used to restructure menus on
 * existing installs (guard each call with a version option so it runs once).
 */
function rv_rebuild_menu(string $name, string $location, array $page_ids): void
{
    $menu = wp_get_nav_menu_object($name);
    if ($menu) {
        $menu_id = (int) $menu->term_id;
        foreach ((wp_get_nav_menu_items($menu_id) ?: []) as $item) {
            wp_delete_post($item->ID, true);
        }
    } else {
        $menu_id = wp_create_nav_menu($name);
        if (is_wp_error($menu_id)) {
            return;
        }
    }
    foreach ($page_ids as $pid) {
        if ($pid) {
            wp_update_nav_menu_item($menu_id, 0, [
                'menu-item-object-id' => $pid,
                'menu-item-object'    => 'page',
                'menu-item-type'      => 'post_type',
                'menu-item-status'    => 'publish',
            ]);
        }
    }
    rv_assign_menu_location($menu_id, $location);
}

// v7 — split tools into their own footer "Tools" menu; trim the Footer/Explore
// menu back to the main site pages. Runs once on existing installs.
add_action('admin_init', function () {
    if (get_option('rv_starter_v7')) {
        return;
    }
    update_option('rv_starter_v7', 1);

    $id = function (string $slug): int {
        $p = get_page_by_path($slug);
        return $p ? (int) $p->ID : 0;
    };
    rv_rebuild_menu('Footer', 'footer', array_filter([$id('services'), $id('work'), $id('journal'), $id('accessibility'), $id('contact')]));
    rv_rebuild_menu('Tools', 'tools', array_filter([$id('free-tools'), $id('website-grader'), $id('seo-checker'), $id('security-checker'), $id('email-checker'), $id('local-seo')]));
    flush_rewrite_rules();
});

add_action('admin_notices', function () {
    if (! get_transient('rv_starter_notice')) {
        return;
    }
    delete_transient('rv_starter_notice');
    echo '<div class="notice notice-success is-dismissible"><p><strong>' . esc_html__('Ridges &amp; Valleys starter content is ready.', 'sage') . '</strong> '
        . esc_html__('Pages, menus, and a few starter blog posts were created. Review them under Pages and Posts.', 'sage') . '</p></div>';
});
