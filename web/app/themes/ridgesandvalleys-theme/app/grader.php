<?php

/**
 * Advanced website grader — Ridges & Valleys Studio.
 *
 * A single REST endpoint (rv-tools/v1/audit) fetches a public page server-side
 * (SSRF-guarded, reusing the tools fetch/DOM helpers) and grades it across seven
 * areas: SEO, Performance, Mobile & Responsiveness, Readability & Content,
 * Security & Trust, Technical best-practices, and Social & Sharing.
 *
 * Every check returns a status (pass | warn | fail), a specific detail drawn
 * from the page, and a plain-English "why it matters" — so a business owner
 * sees not just a score but what to fix and what it costs them today. The
 * response carries an overall score/grade plus a per-category score.
 *
 * This is intentionally a lightweight, honest signal read — it inspects the
 * HTML and response headers, not a full headless render — so it complements,
 * rather than replaces, a hands-on audit.
 */

namespace App;

/* =========================================================== fetch (w/ headers) */

/**
 * Fetch a page and return body, timing, status, and normalised headers.
 */
function grader_fetch(string $url): array
{
    $url = esc_url_raw(trim($url));
    if (! $url || ! preg_match('#^https?://#i', $url) || ! wp_http_validate_url($url)) {
        return ['ok' => false, 'error' => __('Enter a valid public http(s) URL.', 'sage')];
    }

    $start = microtime(true);
    $args  = rv_tools_http_args(14, 'RidgesValleysGrader/1.0 (+https://ridgesandvalleys.com)');
    $args['headers']['Accept-Encoding'] = 'gzip, deflate, br';
    $res = wp_safe_remote_get($url, $args);
    if (is_wp_error($res)) {
        return ['ok' => false, 'error' => rv_tools_fetch_error()];
    }

    $headers = wp_remote_retrieve_headers($res);
    $get = function (string $key) use ($headers): string {
        $v = '';
        if (is_object($headers)) {
            $v = isset($headers[$key]) ? $headers[$key] : '';
        } elseif (is_array($headers)) {
            $v = $headers[strtolower($key)] ?? ($headers[$key] ?? '');
        }
        return is_array($v) ? implode(', ', $v) : (string) $v;
    };

    $html = rv_tools_truncate_body((string) wp_remote_retrieve_body($res));

    return [
        'ok'      => true,
        'status'  => (int) wp_remote_retrieve_response_code($res),
        'html'    => $html,
        'ms'      => (int) round((microtime(true) - $start) * 1000),
        'bytes'   => strlen($html),
        'url'     => $url,
        'header'  => $get,
        'final'   => $url,
    ];
}

/** Rough syllable estimate for a single word (readability). */
function grader_syllables(string $word): int
{
    $word = strtolower(preg_replace('/[^a-z]/i', '', $word));
    if ($word === '') {
        return 0;
    }
    $count = preg_match_all('/[aeiouy]+/', $word);
    if (preg_match('/e$/', $word)) {
        $count = max(1, $count - 1);
    }
    return max(1, (int) $count);
}

/* =========================================================== REST route ====== */

add_action('rest_api_init', function () {
    register_rest_route('rv-tools/v1', '/audit', [
        'methods'             => 'GET',
        'permission_callback' => '__return_true',
        'args'                => ['url' => ['required' => true, 'type' => 'string']],
        'callback'            => __NAMESPACE__ . '\\rv_rest_audit',
    ]);
});

/**
 * The audit: build seven scored categories of checks for a URL.
 */
function rv_rest_audit(\WP_REST_Request $req)
{
    if ($limited = rv_tools_rate_limited()) {
        return $limited;
    }

    $fetch = grader_fetch((string) $req->get_param('url'));
    if (! $fetch['ok']) {
        return new \WP_REST_Response(['ok' => false, 'error' => $fetch['error']], 200);
    }

    $html = $fetch['html'];
    $hdr  = $fetch['header'];
    $dom  = rv_tools_dom($html);
    $xp   = new \DOMXPath($dom);
    $https = str_starts_with($fetch['url'], 'https://');
    $kb   = (int) round($fetch['bytes'] / 1024);

    /* ---- signals ---- */
    $title    = trim((string) ($dom->getElementsByTagName('title')->item(0)->textContent ?? ''));
    $titleLen = mb_strlen($title);
    $metaDesc = '';
    foreach ($xp->query('//meta[@name="description"]/@content') as $n) {
        $metaDesc = trim($n->nodeValue);
    }
    $descLen = mb_strlen($metaDesc);
    $h1 = $dom->getElementsByTagName('h1')->length;
    $h2 = $dom->getElementsByTagName('h2')->length;

    $imgs = $dom->getElementsByTagName('img');
    $imgTotal = $imgs->length;
    $imgAlt = 0; $imgLazy = 0; $imgDims = 0; $imgSrcset = 0;
    foreach ($imgs as $img) {
        if ($img->hasAttribute('alt') && trim($img->getAttribute('alt')) !== '') $imgAlt++;
        if (strtolower($img->getAttribute('loading')) === 'lazy') $imgLazy++;
        if ($img->hasAttribute('width') && $img->hasAttribute('height')) $imgDims++;
        if ($img->hasAttribute('srcset')) $imgSrcset++;
    }

    $viewportNode = $xp->query('//meta[@name="viewport"]/@content')->item(0);
    $viewport     = $viewportNode ? strtolower($viewportNode->nodeValue) : '';
    $hasViewport  = $viewport !== '';
    $zoomBlocked  = $hasViewport && (strpos($viewport, 'user-scalable=no') !== false || strpos($viewport, 'maximum-scale=1') !== false);

    $ogTitle = $xp->query('//meta[@property="og:title"]')->length > 0;
    $ogDesc  = $xp->query('//meta[@property="og:description"]')->length > 0;
    $ogImage = $xp->query('//meta[@property="og:image"]')->length > 0;
    $twCard  = $xp->query('//meta[@name="twitter:card"]')->length > 0;

    $canonical = $xp->query('//link[@rel="canonical"]')->length > 0;
    $jsonld    = $xp->query('//script[@type="application/ld+json"]')->length;
    $robotsNoindex = false;
    foreach ($xp->query('//meta[@name="robots"]/@content') as $r) {
        if (stripos($r->nodeValue, 'noindex') !== false) $robotsNoindex = true;
    }
    $lang    = $dom->documentElement && $dom->documentElement->hasAttribute('lang') && trim($dom->documentElement->getAttribute('lang')) !== '';
    $charset = $xp->query('//meta[@charset]')->length > 0 || $xp->query('//meta[translate(@http-equiv,"CT","ct")="content-type"]')->length > 0;
    $doctype = (bool) preg_match('/^\s*<!doctype\s+html/i', $html);
    $favicon = $xp->query('//link[contains(translate(@rel,"ICON","icon"),"icon")]')->length > 0;
    $appleIcon = $xp->query('//link[@rel="apple-touch-icon"]')->length > 0;

    $scripts = $xp->query('//script[@src]')->length;
    $styles  = $xp->query('//link[@rel="stylesheet"]')->length;
    $requests = 1 + $imgTotal + $scripts + $styles;

    // vague link text
    $vague = 0;
    foreach ($dom->getElementsByTagName('a') as $a) {
        $t = strtolower(trim($a->textContent));
        if (in_array($t, ['click here', 'here', 'read more', 'more', 'link', 'this'], true)) $vague++;
    }

    // headers
    $enc   = strtolower($hdr('content-encoding'));
    $cache = $hdr('cache-control') . ' ' . $hdr('expires');
    $hsts  = $hdr('strict-transport-security') !== '';
    $nosniff = stripos($hdr('x-content-type-options'), 'nosniff') !== false;
    $csp   = $hdr('content-security-policy') !== '';
    $xfo   = $hdr('x-frame-options') !== '' || stripos($hdr('content-security-policy'), 'frame-ancestors') !== false;
    $poweredBy = $hdr('x-powered-by');
    $generator = '';
    foreach ($xp->query('//meta[@name="generator"]/@content') as $g) {
        $generator = trim($g->nodeValue);
    }

    // mixed content on https pages
    $mixed = 0;
    if ($https) {
        $mixed = preg_match_all('#\s(?:src|href)\s*=\s*["\']http://#i', $html);
    }

    // readability (cap work)
    $clean = preg_replace('#<(script|style|noscript)[^>]*>.*?</\1>#is', ' ', $html);
    $textAll = trim(preg_replace('/\s+/', ' ', wp_strip_all_tags((string) $clean)));
    $wordsAll = $textAll === '' ? [] : explode(' ', $textAll);
    $wordCount = count($wordsAll);
    $sample = array_slice($wordsAll, 0, 2000);
    $sc = max(1, count($sample));
    $sentences = max(1, (int) preg_match_all('/[.!?]+/', implode(' ', $sample)));
    $syl = 0;
    foreach ($sample as $w) { $syl += grader_syllables($w); }
    $flesch = 206.835 - 1.015 * ($sc / $sentences) - 84.6 * ($syl / $sc);
    $flesch = max(0, min(100, round($flesch)));
    $codeRatio = strlen($html) > 0 ? round(strlen($textAll) / strlen($html) * 100) : 0;

    /* ---- build categories ---- */
    $categories = [];
    $cat = function (string $key, string $code, string $name, string $desc) use (&$categories) {
        $categories[$key] = ['key' => $key, 'code' => $code, 'name' => $name, 'desc' => $desc, 'checks' => []];
    };
    $add = function (string $key, string $label, string $status, int $weight, string $detail, string $why) use (&$categories) {
        $categories[$key]['checks'][] = ['label' => $label, 'status' => $status, 'detail' => $detail, 'why' => $why, 'weight' => $weight];
    };
    $band = function (bool $ok, bool $warn = false): string {
        return $ok ? 'pass' : ($warn ? 'warn' : 'fail');
    };

    /* --- SEO --- */
    $cat('seo', 'SEO', __('Search visibility', 'sage'), __('Whether search engines can understand your page and show it to the right people.', 'sage'));
    $add('seo', __('Has a page title', 'sage'), $title !== '' ? 'pass' : 'fail', 12,
        $title !== '' ? sprintf(__('“%s”', 'sage'), mb_strimwidth($title, 0, 70, '…')) : __('No <title> found.', 'sage'),
        __('The title is the clickable headline in Google results and the browser tab — it\'s the single biggest on-page SEO signal.', 'sage'));
    $add('seo', __('Title length is 30–60 characters', 'sage'), ($titleLen >= 30 && $titleLen <= 60) ? 'pass' : ($title !== '' ? 'warn' : 'fail'), 5,
        sprintf(__('%d characters.', 'sage'), $titleLen),
        __('Too short wastes the space; too long gets cut off in results. ~50–60 characters shows in full and reads as a real headline.', 'sage'));
    $add('seo', __('Has a meta description', 'sage'), $metaDesc !== '' ? 'pass' : 'fail', 9,
        $metaDesc !== '' ? sprintf(__('%d characters.', 'sage'), $descLen) : __('No meta description.', 'sage'),
        __('This is the summary under your link in search results. A good one is your ad copy — it drives whether people click you or a competitor.', 'sage'));
    $add('seo', __('Meta description is 120–160 characters', 'sage'), ($descLen >= 120 && $descLen <= 160) ? 'pass' : ($metaDesc !== '' ? 'warn' : 'fail'), 4,
        sprintf(__('%d characters.', 'sage'), $descLen),
        __('Around 150 characters fills the space Google gives you without being truncated mid-sentence.', 'sage'));
    $add('seo', __('Exactly one H1 heading', 'sage'), $h1 === 1 ? 'pass' : 'warn', 8,
        sprintf(__('Found %d.', 'sage'), $h1),
        __('The H1 tells search engines the page\'s main topic. One clear H1 avoids mixed signals about what the page is about.', 'sage'));
    $add('seo', __('Uses H2 subheadings', 'sage'), $h2 >= 1 ? 'pass' : 'warn', 4,
        sprintf(__('%d H2 headings.', 'sage'), $h2),
        __('Subheadings structure your content for skimming readers and help search engines understand its sections.', 'sage'));
    $add('seo', __('Images have alt text', 'sage'), ($imgTotal === 0 || $imgAlt / max(1, $imgTotal) >= 0.9) ? 'pass' : ($imgAlt / max(1, $imgTotal) >= 0.5 ? 'warn' : 'fail'), 6,
        $imgTotal ? sprintf(__('%d of %d images described.', 'sage'), $imgAlt, $imgTotal) : __('No images.', 'sage'),
        __('Alt text is how image search and screen readers understand pictures — free SEO and accessibility in one attribute.', 'sage'));
    $add('seo', __('Canonical link set', 'sage'), $canonical ? 'pass' : 'warn', 4,
        $canonical ? __('Present.', 'sage') : __('No canonical tag.', 'sage'),
        __('A canonical tag tells Google which version of a page is the “real” one, preventing duplicate-content confusion.', 'sage'));
    $add('seo', __('Structured data (schema)', 'sage'), $jsonld > 0 ? 'pass' : 'warn', 4,
        $jsonld > 0 ? sprintf(__('%d JSON-LD blocks.', 'sage'), $jsonld) : __('None found.', 'sage'),
        __('Schema markup can earn rich results — star ratings, business hours, FAQs — that make your listing stand out.', 'sage'));
    $add('seo', __('Indexable by search engines', 'sage'), $robotsNoindex ? 'fail' : 'pass', 8,
        $robotsNoindex ? __('This page is set to noindex!', 'sage') : __('No noindex directive.', 'sage'),
        __('A stray “noindex” tag hides a page from Google entirely — a surprisingly common and costly accident.', 'sage'));

    /* --- Performance --- */
    $cat('speed', 'SPD', __('Page speed', 'sage'), __('How fast the page responds and how heavy it is — speed keeps visitors and helps rankings.', 'sage'));
    $add('speed', __('Fast server response', 'sage'), $band($fetch['ms'] <= 600, $fetch['ms'] <= 1200), 12,
        sprintf(__('%d ms to first byte.', 'sage'), $fetch['ms']),
        __('Slow response time delays everything else. Every extra second measurably increases the share of visitors who give up and leave.', 'sage'));
    $add('speed', __('Lean HTML document', 'sage'), $band($kb <= 100, $kb <= 300), 6,
        sprintf(__('%d KB of HTML.', 'sage'), $kb),
        __('A bloated HTML file is slow to download and parse — often a sign of a page builder stuffing the page with markup.', 'sage'));
    $add('speed', __('Compression enabled', 'sage'), ($enc !== '') ? 'pass' : 'warn', 8,
        $enc !== '' ? sprintf(__('%s.', 'sage'), strtoupper($enc)) : __('No gzip/brotli detected.', 'sage'),
        __('Text compression (gzip/brotli) can shrink pages 70%+ over the wire — one of the cheapest speed wins there is.', 'sage'));
    $add('speed', __('Browser caching headers', 'sage'), (trim($cache) !== '') ? 'pass' : 'warn', 5,
        trim($cache) !== '' ? __('Cache headers present.', 'sage') : __('No cache-control/expires.', 'sage'),
        __('Caching lets repeat visitors reuse files instead of re-downloading them, so the second visit feels instant.', 'sage'));
    $add('speed', __('Reasonable request count', 'sage'), $band($requests <= 40, $requests <= 80), 5,
        sprintf(__('~%d resources (%d scripts, %d styles, %d images).', 'sage'), $requests, $scripts, $styles, $imgTotal),
        __('Each file is a separate round-trip. Fewer, bundled files load faster — especially on mobile networks.', 'sage'));
    $add('speed', __('Images set width & height', 'sage'), ($imgTotal === 0 || $imgDims / max(1, $imgTotal) >= 0.8) ? 'pass' : 'warn', 4,
        $imgTotal ? sprintf(__('%d of %d images sized.', 'sage'), $imgDims, $imgTotal) : __('No images.', 'sage'),
        __('Dimensions reserve space so the page doesn\'t jump around as images load — a Core Web Vitals (CLS) factor Google measures.', 'sage'));
    $add('speed', __('Lazy-loads images', 'sage'), ($imgTotal <= 3 || $imgLazy > 0) ? 'pass' : 'warn', 3,
        $imgTotal ? sprintf(__('%d lazy-loaded.', 'sage'), $imgLazy) : __('No images.', 'sage'),
        __('Lazy loading defers off-screen images so the visible part of the page paints sooner.', 'sage'));

    /* --- Mobile --- */
    $cat('mobile', 'MOB', __('Mobile & responsive', 'sage'), __('Whether the page works well on phones — where most local searches happen.', 'sage'));
    $add('mobile', __('Mobile viewport set', 'sage'), $hasViewport ? 'pass' : 'fail', 12,
        $hasViewport ? __('Responsive viewport present.', 'sage') : __('No viewport meta tag.', 'sage'),
        __('Without this tag phones render the desktop layout shrunk down — tiny text, sideways scrolling, instant bounce.', 'sage'));
    $add('mobile', __('Pinch-zoom allowed', 'sage'), $zoomBlocked ? 'fail' : 'pass', 6,
        $zoomBlocked ? __('Zoom is disabled.', 'sage') : __('Users can zoom.', 'sage'),
        __('Blocking zoom locks out anyone who needs to enlarge text — an accessibility failure and a frustration for older customers.', 'sage'));
    $add('mobile', __('Responsive images (srcset)', 'sage'), ($imgTotal === 0 || $imgSrcset > 0) ? 'pass' : 'warn', 5,
        $imgTotal ? sprintf(__('%d use srcset.', 'sage'), $imgSrcset) : __('No images.', 'sage'),
        __('srcset serves phone-sized images to phones instead of shipping full desktop images over cell data.', 'sage'));
    $add('mobile', __('Content fits without side-scroll', 'sage'), $mixed >= 0 && $hasViewport ? 'pass' : 'warn', 4,
        $hasViewport ? __('Viewport enables reflow.', 'sage') : __('Layout may overflow.', 'sage'),
        __('Horizontal scrolling on a phone is a top signal of a non-responsive, dated site.', 'sage'));

    /* --- Readability --- */
    $cat('content', 'TXT', __('Readability & content', 'sage'), __('Whether there\'s enough clear content for visitors and search engines to work with.', 'sage'));
    $add('content', __('Enough page content', 'sage'), $band($wordCount >= 300, $wordCount >= 150), 8,
        sprintf(__('~%d words.', 'sage'), $wordCount),
        __('Thin pages struggle to rank and rarely answer a customer\'s question. Real, useful content is what earns trust and traffic.', 'sage'));
    $add('content', __('Reads clearly', 'sage'), $band($flesch >= 60, $flesch >= 45), 6,
        sprintf(__('Reading ease %d/100 (%s).', 'sage'), $flesch, grader_reading_label((int) $flesch)),
        __('Most customers skim. Plain, short sentences (around a 7th–8th grade level) get read; dense corporate prose gets skipped.', 'sage'));
    $add('content', __('Descriptive link text', 'sage'), $vague === 0 ? 'pass' : ($vague <= 2 ? 'warn' : 'fail'), 5,
        $vague ? sprintf(__('%d vague links like “click here”.', 'sage'), $vague) : __('Links are descriptive.', 'sage'),
        __('“Click here” tells neither a customer nor Google where a link goes. Descriptive links help both.', 'sage'));
    $add('content', __('Healthy content-to-code ratio', 'sage'), $band($codeRatio >= 10, $codeRatio >= 5), 4,
        sprintf(__('%d%% of the page is text.', 'sage'), $codeRatio),
        __('A very low ratio means lots of markup wrapping very little content — often bloated, builder-heavy pages.', 'sage'));

    /* --- Security --- */
    $cat('security', 'SEC', __('Security & trust', 'sage'), __('Signals that tell browsers and customers your site is safe to use.', 'sage'));
    $add('security', __('Serves over HTTPS', 'sage'), $https ? 'pass' : 'fail', 12,
        $https ? __('Encrypted (https).', 'sage') : __('Not secure (http).', 'sage'),
        __('Browsers flag non-HTTPS sites as “Not secure,” and forms on them warn users. It\'s table stakes for trust and SEO.', 'sage'));
    $add('security', __('No mixed content', 'sage'), $mixed === 0 ? 'pass' : 'warn', 6,
        $mixed ? sprintf(__('%d insecure resource(s) on a secure page.', 'sage'), $mixed) : __('All resources secure.', 'sage'),
        __('An HTTPS page loading http:// images or scripts can break the padlock and get blocked by the browser.', 'sage'));
    $add('security', __('HSTS enabled', 'sage'), $hsts ? 'pass' : 'warn', 4,
        $hsts ? __('Strict-Transport-Security set.', 'sage') : __('No HSTS header.', 'sage'),
        __('HSTS forces browsers to always use the encrypted version, closing a window attackers can exploit.', 'sage'));
    $add('security', __('Content-type protection', 'sage'), $nosniff ? 'pass' : 'warn', 3,
        $nosniff ? __('nosniff set.', 'sage') : __('No X-Content-Type-Options.', 'sage'),
        __('This header stops browsers from guessing file types, a common trick in certain attacks.', 'sage'));
    $add('security', __('Clickjacking protection', 'sage'), $xfo ? 'pass' : 'warn', 3,
        $xfo ? __('Frame protection set.', 'sage') : __('No X-Frame-Options / frame-ancestors.', 'sage'),
        __('Prevents other sites from embedding yours in a hidden frame to trick your visitors.', 'sage'));
    $add('security', __('Doesn\'t advertise its software', 'sage'), ($poweredBy === '' && $generator === '') ? 'pass' : 'warn', 2,
        ($poweredBy || $generator) ? trim(__('Exposes: ', 'sage') . trim($poweredBy . ' ' . $generator)) : __('No version headers exposed.', 'sage'),
        __('Broadcasting your exact platform/version hands attackers a shortlist of known exploits to try.', 'sage'));

    /* --- Technical --- */
    $cat('tech', 'TEC', __('Technical foundation', 'sage'), __('The quiet fundamentals that keep a site rendering correctly everywhere.', 'sage'));
    $add('tech', __('Modern doctype', 'sage'), $doctype ? 'pass' : 'warn', 5,
        $doctype ? __('HTML5 doctype.', 'sage') : __('Missing/legacy doctype.', 'sage'),
        __('The HTML5 doctype puts browsers in standards mode so your layout renders predictably.', 'sage'));
    $add('tech', __('Character encoding declared', 'sage'), $charset ? 'pass' : 'warn', 4,
        $charset ? __('Charset set.', 'sage') : __('No charset meta.', 'sage'),
        __('Declaring UTF-8 prevents garbled characters (curly quotes, accents, emoji) across browsers.', 'sage'));
    $add('tech', __('Language declared', 'sage'), $lang ? 'pass' : 'warn', 4,
        $lang ? __('lang attribute set.', 'sage') : __('No lang on <html>.', 'sage'),
        __('The page language helps screen readers pronounce content and helps search engines serve the right audience.', 'sage'));
    $add('tech', __('Has a favicon', 'sage'), $favicon ? 'pass' : 'warn', 3,
        $favicon ? __('Favicon linked.', 'sage') : __('No favicon.', 'sage'),
        __('The little tab icon is a small but real trust and brand-recognition cue in a sea of open tabs.', 'sage'));
    $add('tech', __('Valid HTTP status', 'sage'), ($fetch['status'] >= 200 && $fetch['status'] < 300) ? 'pass' : 'fail', 4,
        sprintf(__('Returned HTTP %d.', 'sage'), $fetch['status']),
        __('A page that answers with an error or redirect chain wastes crawl budget and frustrates visitors.', 'sage'));

    /* --- Social --- */
    $cat('social', 'SOC', __('Social & sharing', 'sage'), __('How your links look when shared on Facebook, LinkedIn, iMessage, and the rest.', 'sage'));
    $add('social', __('Open Graph title & description', 'sage'), ($ogTitle && $ogDesc) ? 'pass' : ($ogTitle || $ogDesc ? 'warn' : 'fail'), 8,
        ($ogTitle && $ogDesc) ? __('Both present.', 'sage') : __('Missing OG title or description.', 'sage'),
        __('Without Open Graph tags, a shared link shows a random title and no summary — it looks broken and gets fewer clicks.', 'sage'));
    $add('social', __('Social share image', 'sage'), $ogImage ? 'pass' : 'fail', 8,
        $ogImage ? __('og:image set.', 'sage') : __('No share image.', 'sage'),
        __('The preview image is most of what people notice in a feed. No image means a tiny, ignorable text link.', 'sage'));
    $add('social', __('Twitter/X card', 'sage'), $twCard ? 'pass' : 'warn', 3,
        $twCard ? __('Card type set.', 'sage') : __('No twitter:card.', 'sage'),
        __('Twitter cards control how your link expands on X — a rich preview instead of a bare URL.', 'sage'));
    $add('social', __('Apple touch icon', 'sage'), $appleIcon ? 'pass' : 'warn', 2,
        $appleIcon ? __('Set.', 'sage') : __('None.', 'sage'),
        __('This is the icon used when someone saves your site to their phone home screen.', 'sage'));

    /* ---- scoring ---- */
    $catOut = [];
    $totEarned = 0; $totPossible = 0;
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

    return new \WP_REST_Response([
        'ok'         => true,
        'url'        => $fetch['final'],
        'overall'    => ['score' => $overall, 'grade' => rv_tools_letter($overall)],
        'meta'       => ['ms' => $fetch['ms'], 'kb' => $kb, 'status' => $fetch['status'], 'requests' => $requests, 'words' => $wordCount],
        'categories' => $catOut,
    ], 200);
}

/** Human label for a Flesch reading-ease score. */
function grader_reading_label(int $score): string
{
    if ($score >= 80) return __('very easy', 'sage');
    if ($score >= 70) return __('easy', 'sage');
    if ($score >= 60) return __('plain', 'sage');
    if ($score >= 50) return __('fairly hard', 'sage');
    if ($score >= 30) return __('hard', 'sage');
    return __('very hard', 'sage');
}
