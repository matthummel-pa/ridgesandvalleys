<?php

/**
 * Optimized SEO titles + meta descriptions per page, supplied in code.
 *
 * The active SEO plugin (Yoast SEO or Rank Math) owns the frontend <title> and
 * meta description. These filters
 * provide hand-written, keyword-front SEO titles (~<=60 chars) and ~155-char
 * descriptions for the studio's pages. Each override DEFERS to any value set in
 * the Rank Math UI (per-post rank_math_title / rank_math_description), so
 * editing a page's SEO in wp-admin always wins over these defaults.
 *
 * Focus keywords are noted in comments for reference. The focus keyword is a
 * SEO plugin's editor-analysis field (Yoast: _yoast_wpseo_focuskw; Rank Math: rank_math_focus_keyword) and is not
 * frontend output, so it must be set in wp-admin — see the SEO plan doc.
 *
 * Filters are registered for both Yoast and Rank Math; only the active plugin's
 * hooks ever fire.
 */

namespace App;

if (! defined('ABSPATH')) {
    exit;
}

/**
 * Meta map. Key = 'front' (homepage), 'blog' (Journal index), or a page slug.
 * Value = [seo_title, meta_description]. Focus keyword in the trailing comment.
 */
function rv_seo_meta_map(): array
{
    return [
        // kw: gettysburg web design
        'front' => [
            "Gettysburg Web Design That Gets You Found | Ridges & Valleys",
            "Gettysburg web design built to get local businesses found on Google. Fast, accessible sites you own — one fixed price, agreed up front. Get a free quote.",
        ],
        // kw: gettysburg web design blog / local seo tips
        'blog' => [
            "Web Design & Local SEO Tips for Gettysburg | Ridges & Valleys",
            "Plain-English web design and local SEO tips for Gettysburg, Adams County & South Central PA business owners — turn your site into more calls. Read the journal.",
        ],
        // kw: gettysburg web designer
        'about' => [
            "Gettysburg Web Designer | Ridges & Valleys Studio",
            "Meet Matt Hummel, a Gettysburg web designer building fast, accessible WordPress sites for Adams County. Family-owned, fixed prices, a site you own. Get a quote.",
        ],
        // kw: gettysburg web design services
        'services' => [
            "Gettysburg Web Design Services & Pricing | Ridges & Valleys",
            "Gettysburg web design services with one fixed price, agreed up front — from website rescues around \$950 to full local builds. No hourly meter. See pricing.",
        ],
        // kw: web design portfolio gettysburg
        'work' => [
            "Web Design Portfolio for Adams County | Ridges & Valleys",
            "See web design work and concept sites for Gettysburg & Adams County businesses — restaurants, inns, shops, and trades. Fast builds you own. View the work.",
        ],
        // kw: gettysburg web design faq
        'faq-2' => [
            "Gettysburg Web Design FAQ: Cost & Time | Ridges & Valleys",
            "Straight answers on what a website costs, how long it takes, Google Maps ranking, and who owns your site — for Gettysburg & Adams County businesses. Read the FAQ.",
        ],
        // kw: free website tools
        'free-tools-2' => [
            "6 Free Website Tools for Local Business | Ridges & Valleys",
            "Free website tools for Gettysburg business owners — grade your site and check SEO, speed, security, and accessibility in plain English. No signup. Try them free.",
        ],
        // kw: contact gettysburg web designer
        'contact' => [
            "Contact a Gettysburg Web Designer | Ridges & Valleys",
            "Contact Ridges & Valleys for Gettysburg & Adams County web design. Tell me about your business and get a free, fixed-price quote — no pressure, no jargon.",
        ],
        // kw: website accessibility statement
        'accessibility' => [
            "Accessibility Statement (WCAG 2.2 AA) | Ridges & Valleys",
            "Our commitment to WCAG 2.2 Level AA: readable contrast, keyboard navigation, and screen-reader-friendly structure on every page. Report an accessibility issue.",
        ],
        // kw: free website grader
        'website-grader' => [
            "Free Website Grader — Score Your Site | Ridges & Valleys",
            "Grade your website free in seconds — speed, SEO, mobile, and security scored in plain English. No email required. See exactly where your site stands today.",
        ],
        // kw: free seo checker
        'seo-checker' => [
            "Free SEO Checker for Your Website | Ridges & Valleys",
            "Check your website's SEO free — titles, meta, headings, and on-page basics graded in plain English. No signup. Find what's holding your rankings back.",
        ],
        // kw: free website security checker
        'security-checker' => [
            "Free Website Security Checker | Ridges & Valleys",
            "Check your website's security free — HTTPS, headers, and common risks scored in plain English. No account needed. See what to fix first to stay safe.",
        ],
        // kw: email deliverability checker
        'email-checker' => [
            "Free Email Deliverability Checker | Ridges & Valleys",
            "Check your email deliverability free — SPF, DKIM, and DMARC records explained in plain English. No signup. Make sure your emails actually reach the inbox.",
        ],
        // kw: local seo scorecard
        'local-seo-2' => [
            "Free Local SEO Scorecard for Business | Ridges & Valleys",
            "Score your local SEO free — Google Business Profile, reviews, and \"near me\" basics graded in plain English. No email. See how you rank in Adams County.",
        ],
        // kw: privacy policy
        'privacy-policy' => [
            "Privacy Policy | Ridges & Valleys Studio",
            "How Ridges & Valleys Studio collects, uses, and protects your information. Read the privacy policy for our Gettysburg web design studio.",
        ],
    ];
}

/** Resolve the [title, description] entry for the current request, or null. */
function rv_seo_meta_current(): ?array
{
    $map = rv_seo_meta_map();
    if (is_front_page()) {
        return $map['front'] ?? null;
    }
    if (is_home()) { // Journal (the posts page)
        return $map['blog'] ?? null;
    }
    if (is_page()) {
        $slug = (string) get_post_field('post_name', get_queried_object_id());
        if (isset($map[$slug])) {
            return $map[$slug];
        }
        // Live SEO slug for the Services template (the seed slug is `services`).
        if ($slug === 'gettysburg-web-design-services') {
            return $map['services'] ?? null;
        }
        if ($slug === 'gettysburg-web-design') {
            return $map['about'] ?? null;
        }
        return null;
    }
    return null;
}

/** True when the queried object has an explicit SEO value in any of $keys (post meta). */
function rv_seo_has_override(string ...$keys): bool
{
    $id = (int) get_queried_object_id();
    if ($id <= 0) {
        return false;
    }
    foreach ($keys as $k) {
        if (trim((string) get_post_meta($id, $k, true)) !== '') {
            return true;
        }
    }
    return false;
}

/** Optimized SEO title for the current page, or the original when overridden/unmapped. */
function rv_seo_filter_title($title)
{
    $m = rv_seo_meta_current();
    if ($m && $m[0] !== '' && ! rv_seo_has_override('_yoast_wpseo_title', 'rank_math_title')) {
        return $m[0];
    }
    return $title;
}

/** Optimized meta description for the current page, or the original when overridden/unmapped. */
function rv_seo_filter_desc($desc)
{
    $m = rv_seo_meta_current();
    if ($m && $m[1] !== '' && ! rv_seo_has_override('_yoast_wpseo_metadesc', 'rank_math_description')) {
        return $m[1];
    }
    return $desc;
}

// Yoast SEO (active): title, meta description, and matching Open Graph / Twitter.
add_filter('wpseo_title', __NAMESPACE__ . '\\rv_seo_filter_title', 20);
add_filter('wpseo_metadesc', __NAMESPACE__ . '\\rv_seo_filter_desc', 20);
add_filter('wpseo_opengraph_title', __NAMESPACE__ . '\\rv_seo_filter_title', 20);
add_filter('wpseo_opengraph_desc', __NAMESPACE__ . '\\rv_seo_filter_desc', 20);
add_filter('wpseo_twitter_title', __NAMESPACE__ . '\\rv_seo_filter_title', 20);
add_filter('wpseo_twitter_description', __NAMESPACE__ . '\\rv_seo_filter_desc', 20);

// Rank Math (kept so the meta still applies if the site switches back).
add_filter('rank_math/frontend/title', __NAMESPACE__ . '\\rv_seo_filter_title', 20);
add_filter('rank_math/frontend/description', __NAMESPACE__ . '\\rv_seo_filter_desc', 20);
