<?php

/**
 * Lightweight SEO + JSON-LD schema.
 *
 * Outputs Open Graph + Twitter meta and a linked JSON-LD @graph tuned for a
 * local web-design studio: a ProfessionalService (LocalBusiness) node with the
 * service area, the founder as a Person, WebSite (with SearchAction), Article
 * on posts, and a BreadcrumbList. Auto-disables when Yoast / Rank Math / AIOSEO
 * is active so it never emits a duplicate, competing set of tags. Also exposes
 * a [rv_breadcrumbs] shortcode that shares one source of truth with the schema.
 *
 * No configuration required — sensible defaults are pulled live from WordPress
 * and the theme's social links, with a few optional `rv_seo_*` theme mods for
 * overrides.
 */

namespace App;

/** True when a dedicated SEO plugin is active — defer to it entirely. */
function seo_plugin_active(): bool
{
    return defined('WPSEO_VERSION') || class_exists('RankMath') || function_exists('rank_math') || defined('AIOSEO_VERSION');
}

/** Read an SEO setting with a sensible default. */
function seo_opt(string $key)
{
    $defaults = [
        'rv_seo_enable'    => true,
        'rv_seo_biz_name'  => get_bloginfo('name'),
        'rv_seo_founder'   => 'Matt Hummel',
        'rv_seo_area'      => 'Gettysburg, Adams County, and South Central Pennsylvania',
        'rv_seo_region'    => 'PA',
        'rv_seo_locality'  => 'Gettysburg',
        'rv_seo_price'     => '$$',
        'rv_seo_share_img' => '',
    ];
    return get_theme_mod($key, $defaults[$key] ?? null);
}

/** Social profile URLs for schema `sameAs`, drawn from the theme's social mods. */
function seo_same_as(): array
{
    $urls = [];
    foreach (array_keys(social_platforms()) as $key) {
        if ($key === 'email') {
            continue;
        }
        $url = get_theme_mod('rv_social_' . $key, '');
        if ($url) {
            $urls[] = esc_url_raw($url);
        }
    }
    return array_values(array_filter($urls));
}

/**
 * Print OG/Twitter meta + JSON-LD graph in <head>. Priority 5 so it lands
 * early. Bails when an SEO plugin is active or the feature is disabled.
 */
add_action('wp_head', function () {
    if (seo_plugin_active() || ! seo_opt('rv_seo_enable')) {
        return;
    }

    $title = wp_get_document_title();
    $url   = (is_singular() && get_permalink()) ? get_permalink() : home_url(add_query_arg([], $GLOBALS['wp']->request ?? ''));
    $desc  = get_bloginfo('description');

    if (is_singular()) {
        $excerpt = has_excerpt() ? get_the_excerpt() : wp_trim_words(wp_strip_all_tags(get_post_field('post_content', get_the_ID())), 32);
        if ($excerpt) {
            $desc = $excerpt;
        }
    }

    $img = (is_singular() && has_post_thumbnail()) ? get_the_post_thumbnail_url(get_the_ID(), 'large') : '';
    if (! $img) {
        $img = seo_opt('rv_seo_share_img');
    }

    $out  = "\n<!-- Ridges & Valleys SEO -->\n";
    $out .= '<meta name="description" content="' . esc_attr(wp_trim_words(wp_strip_all_tags($desc), 32)) . '">' . "\n";
    $out .= '<meta property="og:site_name" content="' . esc_attr(get_bloginfo('name')) . '">' . "\n";
    $out .= '<meta property="og:title" content="' . esc_attr($title) . '">' . "\n";
    $out .= '<meta property="og:description" content="' . esc_attr(wp_strip_all_tags($desc)) . '">' . "\n";
    $out .= '<meta property="og:type" content="' . (is_singular('post') ? 'article' : 'website') . '">' . "\n";
    $out .= '<meta property="og:url" content="' . esc_url($url) . '">' . "\n";
    if ($img) {
        $out .= '<meta property="og:image" content="' . esc_url($img) . '">' . "\n";
    }
    $out .= '<meta name="twitter:card" content="' . ($img ? 'summary_large_image' : 'summary') . '">' . "\n";
    $out .= '<meta name="twitter:title" content="' . esc_attr($title) . '">' . "\n";
    $out .= '<meta name="twitter:description" content="' . esc_attr(wp_strip_all_tags($desc)) . '">' . "\n";
    if ($img) {
        $out .= '<meta name="twitter:image" content="' . esc_url($img) . '">' . "\n";
    }
    echo $out; // phpcs:ignore -- all values escaped above.

    // ---- JSON-LD @graph (linked by @id) ----
    $sameAs = seo_same_as();

    $founder = [
        '@type' => 'Person',
        '@id'   => home_url('/#founder'),
        'name'  => seo_opt('rv_seo_founder'),
        'url'   => home_url('/about/'),
    ];
    if ($sameAs) {
        $founder['sameAs'] = $sameAs;
    }

    $business = [
        '@type'       => 'ProfessionalService',
        '@id'         => home_url('/#business'),
        'name'        => seo_opt('rv_seo_biz_name'),
        'url'         => home_url('/'),
        'description' => get_bloginfo('description'),
        'founder'     => ['@id' => home_url('/#founder')],
        'areaServed'  => seo_opt('rv_seo_area'),
        'priceRange'  => seo_opt('rv_seo_price'),
        'address'     => [
            '@type'           => 'PostalAddress',
            'addressLocality' => seo_opt('rv_seo_locality'),
            'addressRegion'   => seo_opt('rv_seo_region'),
            'addressCountry'  => 'US',
        ],
        'knowsAbout'  => ['Web design', 'WordPress', 'Local SEO', 'Web accessibility'],
    ];
    if ($sameAs) {
        $business['sameAs'] = $sameAs;
    }
    if (seo_opt('rv_seo_share_img')) {
        $business['image'] = seo_opt('rv_seo_share_img');
    }

    $graph = [$founder, $business, [
        '@type'           => 'WebSite',
        '@id'             => home_url('/#website'),
        'url'             => home_url('/'),
        'name'            => get_bloginfo('name'),
        'publisher'       => ['@id' => home_url('/#business')],
        'potentialAction' => [
            '@type'       => 'SearchAction',
            'target'      => ['@type' => 'EntryPoint', 'urlTemplate' => home_url('/?s={search_term_string}')],
            'query-input' => 'required name=search_term_string',
        ],
    ]];

    if (is_singular('post')) {
        $graph[] = [
            '@type'            => 'Article',
            'headline'         => get_the_title(),
            'datePublished'    => get_the_date('c'),
            'dateModified'     => get_the_modified_date('c'),
            'author'           => ['@id' => home_url('/#founder')],
            'publisher'        => ['@id' => home_url('/#business')],
            'mainEntityOfPage' => get_permalink(),
            'image'            => $img ?: null,
        ];
    }

    $crumbs = breadcrumb_items();
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
 * Breadcrumb trail for the current request (flat list, always starting "Home").
 * Shared by the BreadcrumbList schema and the [rv_breadcrumbs] shortcode.
 *
 * @return array<int, array{name: string, url: string}>
 */
function breadcrumb_items(): array
{
    $items = [['name' => __('Home', 'sage'), 'url' => home_url('/')]];

    if (is_singular()) {
        $post = get_queried_object();
        if (is_singular('post')) {
            $cats = get_the_category();
            if ($cats) {
                $items[] = ['name' => $cats[0]->name, 'url' => get_category_link($cats[0]->term_id)];
            }
        } elseif (is_singular('project')) {
            $items[] = ['name' => __('Work', 'sage'), 'url' => home_url('/work/')];
        }
        if ($post) {
            $items[] = ['name' => get_the_title($post), 'url' => get_permalink($post)];
        }
    } elseif (is_category() || is_tag() || is_tax()) {
        $t = get_queried_object();
        if ($t) {
            $items[] = ['name' => single_term_title('', false), 'url' => get_term_link($t)];
        }
    } elseif (is_search()) {
        $items[] = ['name' => __('Search', 'sage'), 'url' => '#'];
    } elseif (is_404()) {
        $items[] = ['name' => __('Not found', 'sage'), 'url' => '#'];
    }

    return $items;
}

/**
 * [rv_breadcrumbs] — visible breadcrumb trail matching the schema above.
 */
add_shortcode('rv_breadcrumbs', function () {
    $items = breadcrumb_items();
    if (count($items) < 2) {
        return '';
    }
    $out = '<nav class="rv-breadcrumbs" aria-label="' . esc_attr__('Breadcrumb', 'sage') . '"><ol>';
    $last = count($items) - 1;
    foreach ($items as $i => $c) {
        $out .= ($i === $last || $c['url'] === '#')
            ? '<li><span aria-current="page">' . esc_html($c['name']) . '</span></li>'
            : '<li><a href="' . esc_url($c['url']) . '">' . esc_html($c['name']) . '</a></li>';
    }
    return $out . '</ol></nav>';
});
