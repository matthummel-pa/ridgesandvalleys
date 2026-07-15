<?php

/**
 * Lightweight SEO + JSON-LD schema. Outputs Open Graph + Twitter meta, an
 * Organization/Person entity, WebSite (with SearchAction), Article (on posts),
 * and a BreadcrumbList. Auto-disables when Yoast or Rank Math is active so it
 * never duplicates their output. Also exposes a [prt_breadcrumbs] shortcode.
 *
 * This exists so the theme is fully functional out of the box for sites that
 * don't want to install a dedicated SEO plugin, while never fighting with one
 * if the site owner does install Yoast/Rank Math/AIOSEO later — the presence
 * check in prt_seo_plugin_active() is consulted before every piece of output
 * in this file, not just once, so activating/deactivating a plugin takes
 * effect immediately with no theme reconfiguration needed.
 */

namespace App;

/**
 * Detect whether a well-known SEO plugin is active. Used to fully defer to
 * that plugin's meta/schema output instead of emitting a duplicate,
 * conflicting set of tags (which can confuse search engines and social
 * platforms that only expect one canonical set of OG/Twitter/JSON-LD tags).
 */
function prt_seo_plugin_active()
{
    return defined('WPSEO_VERSION') || class_exists('RankMath') || function_exists('rank_math') || defined('AIOSEO_VERSION');
}

/**
 * Read a single SEO setting, falling back to sensible defaults (including
 * pulling the site name/description live from WordPress rather than a
 * hardcoded string) when no theme_mod has been saved yet.
 */
function prt_seo($k)
{
    $d = [
        'prt_seo_enable'  => true,
        'prt_seo_entity'  => 'person',
        'prt_seo_name'    => get_bloginfo('name'),
        'prt_seo_logo'    => '',
        'prt_seo_img'     => '',
        'prt_seo_twitter' => '',
    ];
    return get_theme_mod($k, $d[$k] ?? null);
}

/**
 * Register the "SEO & Schema" Customizer section. The section description
 * itself changes based on whether a competing SEO plugin is detected, so
 * users immediately understand why the controls might be a no-op — the
 * settings are still saved/shown even when a plugin is active (so nothing is
 * lost if the plugin is later removed), only the front-end output is skipped.
 */
add_action('customize_register', function ($wp) {
    // Shared guarded helper — see prt_ensure_theme_options_panel() in app/customizer.php.
    prt_ensure_theme_options_panel($wp);
    $desc = prt_seo_plugin_active()
        ? __('An SEO plugin is active, so the theme will NOT output its own meta/schema (to avoid duplicates).', 'pressroot')
        : __('Outputs Open Graph, Twitter cards, and JSON-LD structured data.', 'pressroot');
    $wp->add_section('prt_seo_section', ['title' => __('SEO & Schema', 'pressroot'), 'panel' => 'prt_theme_options', 'description' => $desc]);

    $wp->add_setting('prt_seo_enable', ['default' => true, 'sanitize_callback' => 'wp_validate_boolean']);
    $wp->add_control('prt_seo_enable', ['label' => __('Output meta + schema', 'pressroot'), 'section' => 'prt_seo_section', 'type' => 'checkbox']);

    $wp->add_setting('prt_seo_entity', ['default' => 'person', 'sanitize_callback' => 'sanitize_key']);
    $wp->add_control('prt_seo_entity', ['label' => __('Site represents a', 'pressroot'), 'section' => 'prt_seo_section', 'type' => 'select', 'choices' => ['person' => __('Person', 'pressroot'), 'organization' => __('Organization', 'pressroot')]]);

    $wp->add_setting('prt_seo_name', ['default' => get_bloginfo('name'), 'sanitize_callback' => 'sanitize_text_field']);
    $wp->add_control('prt_seo_name', ['label' => __('Name', 'pressroot'), 'section' => 'prt_seo_section', 'type' => 'text']);

    $wp->add_setting('prt_seo_logo', ['default' => '', 'sanitize_callback' => 'esc_url_raw']);
    $wp->add_control(new \WP_Customize_Image_Control($wp, 'prt_seo_logo', ['label' => __('Logo (square, for schema)', 'pressroot'), 'section' => 'prt_seo_section']));

    $wp->add_setting('prt_seo_img', ['default' => '', 'sanitize_callback' => 'esc_url_raw']);
    $wp->add_control(new \WP_Customize_Image_Control($wp, 'prt_seo_img', ['label' => __('Default social share image', 'pressroot'), 'section' => 'prt_seo_section']));

    $wp->add_setting('prt_seo_twitter', ['default' => '', 'sanitize_callback' => 'sanitize_text_field']);
    $wp->add_control('prt_seo_twitter', ['label' => __('Twitter/X handle (with @)', 'pressroot'), 'section' => 'prt_seo_section', 'type' => 'text']);
}, 25);

/**
 * Print Open Graph + Twitter meta tags and the JSON-LD @graph in <head>.
 * Bails out entirely if a real SEO plugin is active or the feature is
 * disabled in the Customizer, so this is always safe to leave hooked in.
 * Priority 5 (early) so this appears near the top of <head>, ahead of most
 * plugin/theme output that isn't itself pinned to an early priority.
 */
add_action('wp_head', function () {
    if (prt_seo_plugin_active() || ! prt_seo('prt_seo_enable')) {
        return;
    }

    $title = wp_get_document_title();
    // Falls back to reconstructing the current URL from the main $wp query
    // object for non-singular views (archives, search, etc.) where
    // get_permalink() has nothing to return.
    $url   = (is_singular() && get_permalink()) ? get_permalink() : home_url(add_query_arg([], $GLOBALS['wp']->request ?? ''));
    $desc  = get_bloginfo('description');
    if (is_singular()) {
        // Prefer a manually-written excerpt; otherwise derive one from the
        // post body so every singular page still gets a meaningful description.
        $excerpt = has_excerpt() ? get_the_excerpt() : wp_trim_words(wp_strip_all_tags(get_post_field('post_content', get_the_ID())), 30);
        if ($excerpt) {
            $desc = $excerpt;
        }
    }
    $img = '';
    if (is_singular() && has_post_thumbnail()) {
        $img = get_the_post_thumbnail_url(get_the_ID(), 'large');
    }
    if (! $img) {
        // Fall back to the site-wide default share image (Customizer) when
        // the current page has no featured image of its own.
        $img = prt_seo('prt_seo_img');
    }
    $tw = prt_seo('prt_seo_twitter');

    $m  = "\n<!-- mh SEO -->\n";
    $m .= '<meta property="og:site_name" content="' . esc_attr(get_bloginfo('name')) . '">' . "\n";
    $m .= '<meta property="og:title" content="' . esc_attr($title) . '">' . "\n";
    $m .= '<meta name="description" content="' . esc_attr(wp_trim_words(wp_strip_all_tags($desc), 32)) . '">' . "\n";
    $m .= '<meta property="og:description" content="' . esc_attr(wp_strip_all_tags($desc)) . '">' . "\n";
    $m .= '<meta property="og:type" content="' . (is_singular('post') ? 'article' : 'website') . '">' . "\n";
    $m .= '<meta property="og:url" content="' . esc_url($url) . '">' . "\n";
    if ($img) {
        $m .= '<meta property="og:image" content="' . esc_url($img) . '">' . "\n";
    }
    $m .= '<meta name="twitter:card" content="' . ($img ? 'summary_large_image' : 'summary') . '">' . "\n";
    if ($tw) {
        $m .= '<meta name="twitter:site" content="' . esc_attr($tw) . '">' . "\n";
        $m .= '<meta name="twitter:creator" content="' . esc_attr($tw) . '">' . "\n";
    }
    $m .= '<meta name="twitter:title" content="' . esc_attr($title) . '">' . "\n";
    $m .= '<meta name="twitter:description" content="' . esc_attr(wp_strip_all_tags($desc)) . '">' . "\n";
    if ($img) {
        $m .= '<meta name="twitter:image" content="' . esc_url($img) . '">' . "\n";
    }
    echo $m; // phpcs:ignore -- values escaped above

    // ---- JSON-LD graph ----
    // Every node below is linked by "@id" (e.g. '#entity', '#website') instead
    // of nesting the objects inside each other — the standard schema.org
    // "linked graph" pattern, so WebSite.publisher and Article.author can each
    // reference the same Person/Organization node without duplicating it.
    $entityType = prt_seo('prt_seo_entity') === 'organization' ? 'Organization' : 'Person';
    $sameAs = [];
    if (function_exists('App\\prt_social_links')) {
        foreach (prt_social_links() as $s) {
            $sameAs[] = $s['url'];
        }
    }
    $entity = [
        '@type' => $entityType,
        '@id'   => home_url('/#entity'),
        'name'  => prt_seo('prt_seo_name'),
        'url'   => home_url('/'),
    ];
    if (prt_seo('prt_seo_logo')) {
        $entity['logo'] = prt_seo('prt_seo_logo');
        if ($entityType === 'Person') {
            $entity['image'] = prt_seo('prt_seo_logo');
        }
    }
    if ($sameAs) {
        $entity['sameAs'] = array_values($sameAs);
    }

    $graph = [$entity, [
        '@type'           => 'WebSite',
        '@id'             => home_url('/#website'),
        'url'             => home_url('/'),
        'name'            => get_bloginfo('name'),
        'publisher'       => ['@id' => home_url('/#entity')],
        'potentialAction' => [
            '@type'       => 'SearchAction',
            'target'      => ['@type' => 'EntryPoint', 'urlTemplate' => home_url('/?s={search_term_string}')],
            'query-input' => 'required name=search_term_string',
        ],
    ]];

    // Article schema is only added for the built-in 'post' type — the
    // 'projects' custom post type (case studies) intentionally doesn't get
    // an Article node since case studies aren't blog articles.
    if (is_singular('post')) {
        $graph[] = [
            '@type'         => 'Article',
            'headline'      => get_the_title(),
            'datePublished' => get_the_date('c'),
            'dateModified'  => get_the_modified_date('c'),
            'author'        => ['@id' => home_url('/#entity')],
            'publisher'     => ['@id' => home_url('/#entity')],
            'mainEntityOfPage' => get_permalink(),
            'image'         => $img ?: null,
        ];
    }

    // Breadcrumb schema
    $crumbs = prt_breadcrumb_items();
    if (count($crumbs) > 1) {
        $items = [];
        $pos = 1;
        foreach ($crumbs as $cr) {
            $items[] = ['@type' => 'ListItem', 'position' => $pos++, 'name' => $cr['name'], 'item' => $cr['url']];
        }
        $graph[] = ['@type' => 'BreadcrumbList', 'itemListElement' => $items];
    }

    $ld = ['@context' => 'https://schema.org', '@graph' => $graph];
    echo "\n<script type=\"application/ld+json\">" . wp_json_encode($ld, JSON_UNESCAPED_SLASHES | JSON_UNESCAPED_UNICODE) . "</script>\n";
}, 5);

/**
 * Build the breadcrumb trail for the current request as a flat list of
 * ['name' => ..., 'url' => ...] items, always starting with "Home". Shared by
 * both the BreadcrumbList JSON-LD above and the visible [prt_breadcrumbs]
 * shortcode below, so the two are guaranteed to always agree with each other.
 *
 * @return array<int, array{name: string, url: string}>
 */
function prt_breadcrumb_items()
{
    $items = [['name' => __('Home', 'pressroot'), 'url' => home_url('/')]];

    if (is_singular()) {
        $post = get_queried_object();
        if (is_singular('post')) {
            $cats = get_the_category();
            if ($cats) {
                $items[] = ['name' => $cats[0]->name, 'url' => get_category_link($cats[0]->term_id)];
            }
        } elseif (! is_page() && $post && ! empty($post->post_type)) {
            // Non-'post', non-page singular types (e.g. 'projects') get an
            // extra crumb linking back to their archive — but only if that
            // post type actually registered an archive to link to.
            // NOTE(audit): the theme's only custom post type ('projects', see
            // app/setup.php) is registered with 'has_archive' => false, so
            // this branch never actually adds a crumb today; it only helps if
            // a future CPT is registered with an archive enabled.
            $pto = get_post_type_object($post->post_type);
            if ($pto && ! empty($pto->has_archive)) {
                $items[] = ['name' => $pto->labels->name, 'url' => get_post_type_archive_link($post->post_type)];
            }
        }
        if ($post) {
            $items[] = ['name' => get_the_title($post), 'url' => get_permalink($post)];
        }
    } elseif (is_category() || is_tag() || is_tax()) {
        $t = get_queried_object();
        if ($t) {
            $items[] = ['name' => single_term_title('', false), 'url' => get_term_link($t)];
        }
    } elseif (is_post_type_archive()) {
        $items[] = ['name' => post_type_archive_title('', false), 'url' => '#'];
    } elseif (is_search()) {
        $items[] = ['name' => __('Search', 'pressroot'), 'url' => '#'];
    } elseif (is_404()) {
        $items[] = ['name' => __('Not found', 'pressroot'), 'url' => '#'];
    }
    return $items;
}

/**
 * [prt_breadcrumbs] shortcode: renders the same trail as the BreadcrumbList
 * JSON-LD above, but as visible, navigable HTML for use in templates/content.
 * Returns nothing when there's only the "Home" crumb (count < 2), since a
 * single-item trail isn't useful to a visitor.
 */
add_shortcode('prt_breadcrumbs', function () {
    $items = prt_breadcrumb_items();
    if (count($items) < 2) {
        return '';
    }
    $out = '<nav class="prt-breadcrumbs" aria-label="' . esc_attr__('Breadcrumb', 'pressroot') . '"><ol>';
    $last = count($items) - 1;
    foreach ($items as $i => $c) {
        $out .= '<li>';
        $out .= ($i === $last || $c['url'] === '#')
            ? '<span aria-current="page">' . esc_html($c['name']) . '</span>'
            : '<a href="' . esc_url($c['url']) . '">' . esc_html($c['name']) . '</a>';
        $out .= '</li>';
    }
    $out .= '</ol></nav>';
    return $out;
});

/**
 * Emit the minimal CSS the [prt_breadcrumbs] shortcode needs (slash
 * separators, muted link color). Kept as its own small <style> tag on the
 * theme's `prt_head_end` action rather than in the compiled stylesheet so
 * the shortcode works even on installs that strip/replace app.css.
 */
add_action('prt_head_end', function () {
    echo "\n<style id=\"prt-breadcrumbs\">.prt-breadcrumbs ol{display:flex;flex-wrap:wrap;gap:6px;list-style:none;margin:0 0 14px;padding:0;font-size:13px;color:var(--color-muted,#5c636c);}.prt-breadcrumbs li:not(:last-child)::after{content:'/';margin-left:6px;opacity:.6;}.prt-breadcrumbs a{color:var(--color-muted,#5c636c);text-decoration:none;}.prt-breadcrumbs a:hover{color:var(--color-green,#2f6b4e);}</style>\n";
}, 18);
