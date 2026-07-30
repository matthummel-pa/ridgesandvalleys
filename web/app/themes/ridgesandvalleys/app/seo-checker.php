<?php

/**
 * Advanced SEO checker — Ridges & Valleys Studio.
 *
 * A focused, deeper companion to the site grader: one REST endpoint
 * (rv-tools/v1/seo) that fetches a page (and its robots.txt) and analyses it
 * purely for search performance. It accepts an optional target keyword and
 * grades keyword usage across the places that matter, builds a Google-style
 * snippet preview, and checks crawlability (robots.txt, XML sitemap, canonical,
 * clean URLs), heading structure, the link profile, structured data, and the
 * technical SEO fundamentals.
 *
 * Every check returns a status (pass | warn | fail), the specific value found,
 * and a plain-English "why it matters". Reuses the tools fetch/DOM helpers and
 * the grader's letter-grade scale.
 */

namespace App;

/** Case-insensitive substring test. */
function seocheck_has(string $haystack, string $needle): bool
{
    $needle = trim($needle);
    return $needle !== '' && mb_stripos($haystack, $needle) !== false;
}

add_action('rest_api_init', function () {
    register_rest_route('rv-tools/v1', '/seo', [
        'methods'             => 'GET',
        'permission_callback' => '__return_true',
        'args'                => [
            'url'     => ['required' => true, 'type' => 'string'],
            'keyword' => ['required' => false, 'type' => 'string'],
        ],
        'callback'            => __NAMESPACE__ . '\\rv_rest_seo',
    ]);
});

/**
 * Fetch a same-origin robots.txt for the audited URL (SSRF-guarded).
 */
function seocheck_robots(string $pageUrl): array
{
    $parts = wp_parse_url($pageUrl);
    if (empty($parts['scheme']) || empty($parts['host'])) {
        return ['ok' => false, 'body' => '', 'sitemaps' => []];
    }
    $robots = $parts['scheme'] . '://' . $parts['host'] . (isset($parts['port']) ? ':' . $parts['port'] : '') . '/robots.txt';
    $res = wp_safe_remote_get($robots, ['timeout' => 8, 'redirection' => 2, 'user-agent' => 'RidgesValleysSEO/1.0']);
    if (is_wp_error($res) || (int) wp_remote_retrieve_response_code($res) !== 200) {
        return ['ok' => false, 'body' => '', 'sitemaps' => []];
    }
    $body = (string) wp_remote_retrieve_body($res);
    $sitemaps = [];
    if (preg_match_all('/^\s*sitemap:\s*(\S+)/im', $body, $m)) {
        $sitemaps = $m[1];
    }
    return ['ok' => true, 'body' => $body, 'sitemaps' => $sitemaps];
}

function rv_rest_seo(\WP_REST_Request $req)
{
    $fetch = grader_fetch((string) $req->get_param('url'));
    if (! $fetch['ok']) {
        return new \WP_REST_Response(['ok' => false, 'error' => $fetch['error']], 200);
    }

    $keyword = trim((string) $req->get_param('keyword'));
    $html = $fetch['html'];
    $dom  = rv_tools_dom($html);
    $xp   = new \DOMXPath($dom);
    $url  = $fetch['final'];
    $parts = wp_parse_url($url);
    $host  = $parts['host'] ?? '';
    $path  = $parts['path'] ?? '/';
    $https = str_starts_with($url, 'https://');
    $kb    = (int) round($fetch['bytes'] / 1024);

    /* ---- signals ---- */
    $title    = trim((string) ($dom->getElementsByTagName('title')->item(0)->textContent ?? ''));
    $titleLen = mb_strlen($title);
    $metaDesc = '';
    foreach ($xp->query('//meta[@name="description"]/@content') as $n) {
        $metaDesc = trim($n->nodeValue);
    }
    $descLen = mb_strlen($metaDesc);

    // headings
    $h1nodes = $dom->getElementsByTagName('h1');
    $h1 = $h1nodes->length;
    $h1text = $h1 ? trim($h1nodes->item(0)->textContent) : '';
    $h2 = $dom->getElementsByTagName('h2')->length;
    $h3 = $dom->getElementsByTagName('h3')->length;
    $levels = [];
    foreach ($xp->query('//*[self::h1 or self::h2 or self::h3 or self::h4 or self::h5 or self::h6]') as $h) {
        $levels[] = (int) substr($h->nodeName, 1);
    }
    $skipped = false;
    for ($i = 1; $i < count($levels); $i++) {
        if ($levels[$i] - $levels[$i - 1] > 1) { $skipped = true; break; }
    }

    // content text
    $clean = preg_replace('#<(script|style|noscript)[^>]*>.*?</\1>#is', ' ', $html);
    $text  = trim(preg_replace('/\s+/', ' ', wp_strip_all_tags((string) $clean)));
    $words = $text === '' ? [] : explode(' ', $text);
    $wordCount = count($words);
    $intro = implode(' ', array_slice($words, 0, 100));

    // images
    $imgs = $dom->getElementsByTagName('img');
    $imgTotal = $imgs->length; $imgAlt = 0; $kwInAlt = false;
    foreach ($imgs as $img) {
        $alt = trim($img->getAttribute('alt'));
        if ($alt !== '') $imgAlt++;
        if ($keyword && seocheck_has($alt, $keyword)) $kwInAlt = true;
    }

    // links
    $internal = 0; $external = 0; $vague = 0; $nofollow = 0;
    foreach ($dom->getElementsByTagName('a') as $a) {
        $href = trim($a->getAttribute('href'));
        if ($href === '' || str_starts_with($href, '#') || str_starts_with($href, 'mailto:') || str_starts_with($href, 'tel:')) {
            continue;
        }
        $lh = wp_parse_url($href, PHP_URL_HOST);
        if (! $lh || $lh === $host) { $internal++; } else { $external++; }
        if (stripos($a->getAttribute('rel'), 'nofollow') !== false) $nofollow++;
        $t = strtolower(trim($a->textContent));
        if (in_array($t, ['click here', 'here', 'read more', 'more', 'link', 'this'], true)) $vague++;
    }

    // meta / indexing
    $canonical = '';
    foreach ($xp->query('//link[@rel="canonical"]/@href') as $c) { $canonical = trim($c->nodeValue); }
    $robotsNoindex = false; $robotsNofollow = false;
    foreach ($xp->query('//meta[@name="robots"]/@content') as $r) {
        if (stripos($r->nodeValue, 'noindex') !== false) $robotsNoindex = true;
        if (stripos($r->nodeValue, 'nofollow') !== false) $robotsNofollow = true;
    }
    $viewport = $xp->query('//meta[@name="viewport"]')->length > 0;
    $lang     = $dom->documentElement && $dom->documentElement->hasAttribute('lang') && trim($dom->documentElement->getAttribute('lang')) !== '';
    $charset  = $xp->query('//meta[@charset]')->length > 0;
    $hreflang = $xp->query('//link[@rel="alternate"][@hreflang]')->length;

    // structured data & social
    $jsonld = $xp->query('//script[@type="application/ld+json"]')->length;
    $schemaTypes = [];
    foreach ($xp->query('//script[@type="application/ld+json"]') as $s) {
        if (preg_match_all('/"@type"\s*:\s*"([^"]+)"/', $s->textContent, $mm)) {
            foreach ($mm[1] as $t) { $schemaTypes[$t] = true; }
        }
    }
    $ogTitle = $xp->query('//meta[@property="og:title"]')->length > 0;
    $ogDesc  = $xp->query('//meta[@property="og:description"]')->length > 0;
    $ogImage = $xp->query('//meta[@property="og:image"]')->length > 0;
    $twCard  = $xp->query('//meta[@name="twitter:card"]')->length > 0;
    $hasBreadcrumb = isset($schemaTypes['BreadcrumbList']);

    // url quality
    $urlClean = ! preg_match('/[_%]| /', $path) && strlen(rtrim($url, '/')) <= 100 && empty($parts['query']);

    // robots.txt / sitemap
    $robots = seocheck_robots($url);
    $sitemapRef = ! empty($robots['sitemaps']) || $xp->query('//link[contains(@rel,"sitemap")]')->length > 0;

    // keyword analysis
    $kwCount = 0; $density = 0.0;
    if ($keyword) {
        $kwCount = preg_match_all('/\b' . preg_quote($keyword, '/') . '\b/i', $text);
        $density = $wordCount > 0 ? round($kwCount / $wordCount * 100, 1) : 0.0;
    }

    /* ---- build categories ---- */
    $categories = [];
    $cat = function (string $key, string $code, string $name, string $desc) use (&$categories) {
        $categories[$key] = ['key' => $key, 'code' => $code, 'name' => $name, 'desc' => $desc, 'checks' => []];
    };
    $add = function (string $key, string $label, string $status, int $weight, string $detail, string $why) use (&$categories) {
        $categories[$key]['checks'][] = ['label' => $label, 'status' => $status, 'detail' => $detail, 'why' => $why, 'weight' => $weight];
    };
    $band = fn (bool $ok, bool $warn = false): string => $ok ? 'pass' : ($warn ? 'warn' : 'fail');

    /* --- Snippet & meta --- */
    $cat('meta', 'TAG', __('Search snippet & meta', 'sage'), __('The title and description that decide how you look — and whether people click — in Google.', 'sage'));
    $add('meta', __('Title tag present', 'sage'), $title !== '' ? 'pass' : 'fail', 12,
        $title !== '' ? sprintf(__('“%s”', 'sage'), mb_strimwidth($title, 0, 70, '…')) : __('No <title>.', 'sage'),
        __('The title is your headline in search results and the strongest single on-page ranking signal.', 'sage'));
    $add('meta', __('Title length 30–60 characters', 'sage'), ($titleLen >= 30 && $titleLen <= 60) ? 'pass' : ($title !== '' ? 'warn' : 'fail'), 6,
        sprintf(__('%d characters.', 'sage'), $titleLen),
        __('Under ~60 characters shows in full; longer titles get truncated with an ellipsis mid-thought.', 'sage'));
    $add('meta', __('Meta description present', 'sage'), $metaDesc !== '' ? 'pass' : 'fail', 9,
        $metaDesc !== '' ? sprintf(__('%d characters.', 'sage'), $descLen) : __('None.', 'sage'),
        __('It\'s the ad copy under your result. Google may rewrite it, but a good one still lifts click-through.', 'sage'));
    $add('meta', __('Description length 120–160', 'sage'), ($descLen >= 120 && $descLen <= 160) ? 'pass' : ($metaDesc !== '' ? 'warn' : 'fail'), 4,
        sprintf(__('%d characters.', 'sage'), $descLen),
        __('~150 characters fills the space Google shows without being cut off.', 'sage'));
    $add('meta', __('Canonical URL set', 'sage'), $canonical !== '' ? 'pass' : 'warn', 5,
        $canonical !== '' ? mb_strimwidth($canonical, 0, 60, '…') : __('No canonical tag.', 'sage'),
        __('Canonical tags prevent duplicate-content dilution by naming the one authoritative URL for the page.', 'sage'));

    /* --- Indexability & crawlability --- */
    $cat('crawl', 'CRL', __('Indexability & crawlability', 'sage'), __('Whether search engines are allowed and able to reach, read, and index the page.', 'sage'));
    $add('crawl', __('Indexable (no noindex)', 'sage'), $robotsNoindex ? 'fail' : 'pass', 12,
        $robotsNoindex ? __('Page is set to noindex!', 'sage') : __('Allowed to be indexed.', 'sage'),
        __('A stray noindex removes the page from Google entirely — a common, silent, and costly mistake.', 'sage'));
    $add('crawl', __('Links are followable', 'sage'), $robotsNofollow ? 'warn' : 'pass', 4,
        $robotsNofollow ? __('Meta robots nofollow is set.', 'sage') : __('No page-level nofollow.', 'sage'),
        __('A page-wide nofollow stops link equity flowing through your own site — rarely what you want.', 'sage'));
    $add('crawl', __('robots.txt found', 'sage'), $robots['ok'] ? 'pass' : 'warn', 5,
        $robots['ok'] ? __('Present.', 'sage') : __('No robots.txt reachable.', 'sage'),
        __('robots.txt guides crawlers and is where your sitemap is usually announced.', 'sage'));
    $add('crawl', __('XML sitemap referenced', 'sage'), $sitemapRef ? 'pass' : 'warn', 6,
        $sitemapRef ? __('Sitemap declared.', 'sage') : __('No sitemap found in robots.txt.', 'sage'),
        __('A sitemap helps search engines discover every page quickly — especially new or deep ones.', 'sage'));
    $add('crawl', __('Clean, readable URL', 'sage'), $urlClean ? 'pass' : 'warn', 4,
        sprintf(__('%s', 'sage'), mb_strimwidth($path, 0, 50, '…')),
        __('Short, word-based URLs without query strings or underscores are easier for people and engines to parse.', 'sage'));
    $add('crawl', __('Secure (HTTPS)', 'sage'), $https ? 'pass' : 'fail', 6,
        $https ? __('https.', 'sage') : __('http — not secure.', 'sage'),
        __('HTTPS is a confirmed ranking signal and a trust requirement — non-secure pages are flagged in the browser.', 'sage'));

    /* --- Content & keywords --- */
    $cat('content', 'KW', __('Content & keywords', 'sage'), $keyword
        ? sprintf(__('How well the page is built around “%s”.', 'sage'), $keyword)
        : __('Depth, structure, and keyword signals in the page copy.', 'sage'));
    $add('content', __('Substantial content', 'sage'), $band($wordCount >= 300, $wordCount >= 150), 8,
        sprintf(__('~%d words.', 'sage'), $wordCount),
        __('Pages with thin content rarely rank — there\'s little for Google to understand or reward.', 'sage'));
    $add('content', __('Exactly one H1', 'sage'), $h1 === 1 ? 'pass' : 'warn', 6,
        $h1 === 1 ? mb_strimwidth($h1text, 0, 50, '…') : sprintf(__('Found %d H1s.', 'sage'), $h1),
        __('One H1 states the page\'s topic clearly; zero or many muddies the signal.', 'sage'));
    $add('content', __('Logical heading order', 'sage'), $skipped ? 'warn' : 'pass', 4,
        $skipped ? __('A heading skips a level.', 'sage') : sprintf(__('%d H2, %d H3.', 'sage'), $h2, $h3),
        __('A clean H1→H2→H3 outline helps engines (and readers) grasp how your content is organised.', 'sage'));
    $add('content', __('Images described (alt)', 'sage'), ($imgTotal === 0 || $imgAlt / max(1, $imgTotal) >= 0.9) ? 'pass' : 'warn', 4,
        $imgTotal ? sprintf(__('%d of %d.', 'sage'), $imgAlt, $imgTotal) : __('No images.', 'sage'),
        __('Alt text feeds image search and reinforces a page\'s topic to crawlers.', 'sage'));

    if ($keyword) {
        $add('content', __('Keyword in title', 'sage'), seocheck_has($title, $keyword) ? 'pass' : 'fail', 9,
            seocheck_has($title, $keyword) ? __('Present.', 'sage') : sprintf(__('“%s” not in title.', 'sage'), $keyword),
            __('The title is the highest-weight place to include your target term — near the front is best.', 'sage'));
        $add('content', __('Keyword in H1', 'sage'), seocheck_has($h1text, $keyword) ? 'pass' : 'warn', 6,
            seocheck_has($h1text, $keyword) ? __('Present.', 'sage') : __('Not in the H1.', 'sage'),
            __('Echoing the term in the main heading reinforces the page\'s topic for search engines.', 'sage'));
        $add('content', __('Keyword in meta description', 'sage'), seocheck_has($metaDesc, $keyword) ? 'pass' : 'warn', 4,
            seocheck_has($metaDesc, $keyword) ? __('Present.', 'sage') : __('Not in the description.', 'sage'),
            __('Google bolds matching terms in the snippet, which draws the eye and lifts clicks.', 'sage'));
        $add('content', __('Keyword in first paragraph', 'sage'), seocheck_has($intro, $keyword) ? 'pass' : 'warn', 5,
            seocheck_has($intro, $keyword) ? __('Present early.', 'sage') : __('Not in the opening.', 'sage'),
            __('Using the term early confirms relevance before a reader (or crawler) loses interest.', 'sage'));
        $add('content', __('Keyword in URL', 'sage'), seocheck_has($path, str_replace(' ', '-', $keyword)) || seocheck_has($path, $keyword) ? 'pass' : 'warn', 4,
            seocheck_has($path, str_replace(' ', '-', $keyword)) ? __('In the slug.', 'sage') : __('Not in the URL.', 'sage'),
            __('A keyword-bearing slug is a small, durable relevance signal that also reads well when shared.', 'sage'));
        $add('content', __('Keyword density is natural', 'sage'), ($density >= 0.3 && $density <= 3.0) ? 'pass' : ($kwCount > 0 ? 'warn' : 'fail'), 5,
            sprintf(__('%d uses · %s%% density.', 'sage'), $kwCount, number_format($density, 1)),
            __('Aim for natural use (~0.5–2%). Too little reads off-topic; too much looks like keyword stuffing.', 'sage'));
        $add('content', __('Keyword in image alt', 'sage'), ($imgTotal === 0 || $kwInAlt) ? 'pass' : 'warn', 3,
            $imgTotal === 0 ? __('No images.', 'sage') : ($kwInAlt ? __('Present.', 'sage') : __('Not in any alt text.', 'sage')),
            __('One descriptive, keyword-relevant alt attribute helps you show up in image search too.', 'sage'));
    }

    /* --- Structured data & social --- */
    $cat('rich', 'RCH', __('Structured data & social', 'sage'), __('Markup that unlocks rich results and controls how links look when shared.', 'sage'));
    $add('rich', __('Structured data (schema)', 'sage'), $jsonld > 0 ? 'pass' : 'warn', 8,
        $jsonld > 0 ? sprintf(__('%d block(s): %s', 'sage'), $jsonld, implode(', ', array_slice(array_keys($schemaTypes), 0, 4)) ?: 'JSON-LD') : __('None found.', 'sage'),
        __('Schema can earn rich results — ratings, FAQs, business info, breadcrumbs — that make your listing bigger and more clickable.', 'sage'));
    $add('rich', __('Breadcrumb schema', 'sage'), $hasBreadcrumb ? 'pass' : 'warn', 3,
        $hasBreadcrumb ? __('Present.', 'sage') : __('No BreadcrumbList.', 'sage'),
        __('Breadcrumbs replace the raw URL in results with a clean, navigable path.', 'sage'));
    $add('rich', __('Open Graph tags', 'sage'), ($ogTitle && $ogDesc) ? 'pass' : ($ogTitle || $ogDesc ? 'warn' : 'fail'), 6,
        ($ogTitle && $ogDesc) ? __('Title & description set.', 'sage') : __('Missing OG title/description.', 'sage'),
        __('Without Open Graph, shared links show a random title and no summary — they look broken.', 'sage'));
    $add('rich', __('Social share image', 'sage'), $ogImage ? 'pass' : 'fail', 6,
        $ogImage ? __('og:image set.', 'sage') : __('No share image.', 'sage'),
        __('The preview image is most of what people notice when your link appears in a feed.', 'sage'));
    $add('rich', __('Twitter/X card', 'sage'), $twCard ? 'pass' : 'warn', 2,
        $twCard ? __('Set.', 'sage') : __('No twitter:card.', 'sage'),
        __('Controls how your link expands on X into a rich preview instead of a bare URL.', 'sage'));

    /* --- Links --- */
    $cat('links', 'LNK', __('Link profile', 'sage'), __('How the page connects to the rest of your site and the web.', 'sage'));
    $add('links', __('Has internal links', 'sage'), $band($internal >= 3, $internal >= 1), 7,
        sprintf(__('%d internal links.', 'sage'), $internal),
        __('Internal links spread ranking strength and guide visitors to your key pages (services, contact).', 'sage'));
    $add('links', __('Descriptive anchor text', 'sage'), $vague === 0 ? 'pass' : ($vague <= 2 ? 'warn' : 'fail'), 6,
        $vague ? sprintf(__('%d vague links (“click here”).', 'sage'), $vague) : __('Anchors are descriptive.', 'sage'),
        __('Anchor text tells Google what the linked page is about — “click here” tells it nothing.', 'sage'));
    $add('links', __('Links to relevant sources', 'sage'), $band($external >= 1), 3,
        sprintf(__('%d external links.', 'sage'), $external),
        __('A few outbound links to trustworthy sources can reinforce a page\'s topical context.', 'sage'));

    /* --- Technical --- */
    $cat('tech', 'TEC', __('Technical SEO', 'sage'), __('The under-the-hood fundamentals search engines expect.', 'sage'));
    $add('tech', __('Mobile viewport', 'sage'), $viewport ? 'pass' : 'fail', 8,
        $viewport ? __('Responsive viewport set.', 'sage') : __('No viewport meta.', 'sage'),
        __('Google indexes the mobile version of your site first — no viewport means a broken mobile experience.', 'sage'));
    $add('tech', __('Fast response', 'sage'), $band($fetch['ms'] <= 800, $fetch['ms'] <= 1500), 6,
        sprintf(__('%d ms.', 'sage'), $fetch['ms']),
        __('Speed is a ranking factor and a bounce factor — slow pages lose both rank and visitors.', 'sage'));
    $add('tech', __('Reasonable page weight', 'sage'), $band($kb <= 150, $kb <= 400), 4,
        sprintf(__('%d KB of HTML.', 'sage'), $kb),
        __('Heavy pages crawl slower and load slower, especially on the mobile connections Google prioritises.', 'sage'));
    $add('tech', __('Language declared', 'sage'), $lang ? 'pass' : 'warn', 3,
        $lang ? __('lang set.', 'sage') : __('No lang attribute.', 'sage'),
        __('Declaring the page language helps search engines serve it to the right audience.', 'sage'));
    $add('tech', __('Character encoding', 'sage'), $charset ? 'pass' : 'warn', 2,
        $charset ? __('charset set.', 'sage') : __('No charset.', 'sage'),
        __('A declared charset prevents garbled characters that hurt readability and trust.', 'sage'));

    /* ---- scoring ---- */
    $catOut = []; $totEarned = 0; $totPossible = 0;
    foreach ($categories as $c) {
        $earned = 0; $possible = 0;
        foreach ($c['checks'] as $chk) {
            $possible += $chk['weight'];
            $earned += $chk['status'] === 'pass' ? $chk['weight'] : ($chk['status'] === 'warn' ? $chk['weight'] * 0.5 : 0);
        }
        $score = (int) round($earned / max(1, $possible) * 100);
        $c['score'] = $score;
        $c['grade'] = rv_tools_letter($score);
        $catOut[] = $c;
        $totEarned += $earned; $totPossible += $possible;
    }
    $overall = (int) round($totEarned / max(1, $totPossible) * 100);

    // SERP preview
    $displayUrl = $host . ($path === '/' ? '' : rtrim($path, '/'));
    $preview = [
        'title'       => $title !== '' ? $title : __('(no title tag)', 'sage'),
        'description' => $metaDesc !== '' ? $metaDesc : __('No meta description — Google will pull a snippet from the page, which you don\'t control.', 'sage'),
        'url'         => $displayUrl,
        'titleCut'    => $titleLen > 60,
        'descCut'     => $descLen > 160,
    ];

    return new \WP_REST_Response([
        'ok'         => true,
        'url'        => $url,
        'keyword'    => $keyword,
        'overall'    => ['score' => $overall, 'grade' => rv_tools_letter($overall)],
        'preview'    => $preview,
        'meta'       => ['ms' => $fetch['ms'], 'kb' => $kb, 'words' => $wordCount, 'internal' => $internal, 'external' => $external, 'status' => $fetch['status']],
        'categories' => $catOut,
    ], 200);
}
