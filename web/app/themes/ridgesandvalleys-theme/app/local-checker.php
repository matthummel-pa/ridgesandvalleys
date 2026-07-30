<?php

/**
 * Local SEO scorecard — Ridges & Valleys Studio.
 *
 * One REST endpoint (rv-tools/v1/local) reads a page for the signals that help
 * a local business show up in the Google map pack and get contacted: NAP (name,
 * address, phone), click-to-call, business hours, LocalBusiness structured data,
 * an embedded map, a Google Business Profile link, a location signal in the
 * title, and reviews. It complements the on-page SEO checker with a specifically
 * local lens — the traffic that matters most for a Main Street business.
 *
 * Every check returns status (pass | warn | fail), the value found, and a
 * plain-English "why it matters". Signals it can't see from the page (a claimed
 * Google Business Profile, review counts, citation consistency) are named in the
 * page copy so the tool stays honest.
 */

namespace App;

add_action('rest_api_init', function () {
    register_rest_route('rv-tools/v1', '/local', [
        'methods'             => 'GET',
        'permission_callback' => '__return_true',
        'args'                => ['url' => ['required' => true, 'type' => 'string']],
        'callback'            => __NAMESPACE__ . '\\rv_rest_local',
    ]);
});

/** Recursively scan decoded JSON-LD for local-business signals. */
function localcheck_scan($node, array &$flags): void
{
    if (! is_array($node)) {
        return;
    }
    if (isset($node['@type'])) {
        foreach ((array) $node['@type'] as $t) {
            $flags['types'][strtolower((string) $t)] = true;
        }
    }
    foreach (['address', 'telephone', 'openingHours', 'openingHoursSpecification', 'geo', 'aggregateRating', 'review'] as $k) {
        if (! empty($node[$k])) {
            $flags[$k] = true;
        }
    }
    if (! empty($node['address']['addressLocality'])) {
        $flags['locality'] = (string) $node['address']['addressLocality'];
    }
    foreach ($node as $v) {
        localcheck_scan($v, $flags);
    }
}

function rv_rest_local(\WP_REST_Request $req)
{
    $fetch = grader_fetch((string) $req->get_param('url'));
    if (! $fetch['ok']) {
        return new \WP_REST_Response(['ok' => false, 'error' => $fetch['error']], 200);
    }

    $html = $fetch['html'];
    $dom  = rv_tools_dom($html);
    $xp   = new \DOMXPath($dom);
    $https = str_starts_with($fetch['url'], 'https://');

    $title = trim((string) ($dom->getElementsByTagName('title')->item(0)->textContent ?? ''));
    $h1nodes = $dom->getElementsByTagName('h1');
    $h1text = $h1nodes->length ? trim($h1nodes->item(0)->textContent) : '';
    $text = trim(preg_replace('/\s+/', ' ', wp_strip_all_tags(preg_replace('#<(script|style)[^>]*>.*?</\1>#is', ' ', $html))));

    // structured data flags
    $flags = ['types' => []];
    foreach ($xp->query('//script[@type="application/ld+json"]') as $s) {
        $data = json_decode($s->textContent, true);
        if (is_array($data)) {
            localcheck_scan($data, $flags);
        }
    }
    $localSet = ['localbusiness', 'store', 'restaurant', 'professionalservice', 'homeandconstructionbusiness', 'medicalbusiness', 'dentist', 'attorney', 'legalservice', 'autorepair', 'plumber', 'electrician', 'realestateagent', 'foodestablishment', 'cafe', 'bakery', 'bar', 'lodgingbusiness', 'healthandbeautybusiness', 'financialservice', 'generalcontractor', 'roofingcontractor', 'childcare', 'automotivebusiness', 'entertainmentbusiness', 'sportsactivitylocation'];
    $schemaTypes = array_keys($flags['types']);
    $hasLocalSchema = (bool) array_intersect($schemaTypes, $localSet)
        || (! empty($flags['address']) && (! empty($flags['telephone']) || ! empty($flags['openingHours']) || ! empty($flags['openingHoursSpecification'])));
    $schemaAddress = ! empty($flags['address']);
    $schemaPhone   = ! empty($flags['telephone']);
    $schemaHours   = ! empty($flags['openingHours']) || ! empty($flags['openingHoursSpecification']);
    $schemaGeo     = ! empty($flags['geo']);
    $schemaRating  = ! empty($flags['aggregateRating']) || ! empty($flags['review']);
    $locality      = $flags['locality'] ?? '';

    // page signals
    $tel = $xp->query('//a[starts-with(translate(@href,"TEL","tel"),"tel:")]')->length;
    $addressTag = $dom->getElementsByTagName('address')->length > 0;
    $zipText = (bool) preg_match('/\b[A-Z]{2}\s+\d{5}(-\d{4})?\b/', $text);
    $hoursText = $schemaHours || (bool) preg_match('/\b(mon|tue|wed|thu|fri|sat|sun)[a-z]*\b.*?\d/i', $text) || (bool) preg_match('/\bhours?\b.*?(\d|closed)/i', $text);

    // maps / GBP / directions
    $embeddedMap = false;
    foreach ($xp->query('//iframe/@src') as $src) {
        if (preg_match('#(google\.[a-z.]+/maps|maps\.google|maps\.googleapis)#i', $src->nodeValue)) {
            $embeddedMap = true;
            break;
        }
    }
    $gbpLink = false; $directions = false;
    foreach ($xp->query('//a') as $a) {
        $href = strtolower($a->getAttribute('href'));
        $t = strtolower(trim($a->textContent));
        if (preg_match('#(g\.page|business\.google|goo\.gl/maps|maps\.app\.goo\.gl|google\.[a-z.]+/maps)#', $href)) $gbpLink = true;
        if (strpos($href, 'maps') !== false || strpos($t, 'direction') !== false) $directions = true;
    }

    // social / review profiles
    $social = 0;
    foreach ($xp->query('//a/@href') as $href) {
        if (preg_match('#(facebook\.com|instagram\.com|yelp\.com|tripadvisor\.|nextdoor\.com)#i', $href->nodeValue)) $social++;
    }

    // location signal in title/H1
    $titleH1 = strtolower($title . ' ' . $h1text);
    $locInTitle = $locality !== '' ? (stripos($titleH1, strtolower($locality)) !== false)
        : (bool) preg_match('/,\s*[a-z]{2}\b|\b(pennsylvania|gettysburg|adams county|near me)\b/i', $titleH1);

    $viewport = $xp->query('//meta[@name="viewport"]')->length > 0;

    // ---- categories ----
    $categories = [];
    $cat = function (string $key, string $code, string $name, string $desc) use (&$categories) {
        $categories[$key] = ['key' => $key, 'code' => $code, 'name' => $name, 'desc' => $desc, 'checks' => []];
    };
    $add = function (string $key, string $label, string $status, int $weight, string $detail, string $why) use (&$categories) {
        $categories[$key]['checks'][] = ['label' => $label, 'status' => $status, 'detail' => $detail, 'why' => $why, 'weight' => $weight];
    };
    $yn = fn (bool $b): string => $b ? 'pass' : 'fail';

    /* --- Contact essentials (NAP) --- */
    $cat('nap', 'NAP', __('Contact essentials', 'sage'), __('The name, address, phone, and hours a nearby customer needs to reach you.', 'sage'));
    $add('nap', __('Click-to-call phone number', 'sage'), $tel > 0 ? 'pass' : 'fail', 12,
        $tel > 0 ? sprintf(__('%d tap-to-call link(s).', 'sage'), $tel) : __('No tel: link.', 'sage'),
        __('On a phone, a tap-to-call number turns an interested visitor into a call in one touch. A plain text number makes them work for it.', 'sage'));
    $add('nap', __('Address on the page', 'sage'), ($addressTag || $zipText || $schemaAddress) ? 'pass' : 'warn', 8,
        ($addressTag || $zipText || $schemaAddress) ? __('Address found.', 'sage') : __('No clear street address.', 'sage'),
        __('A visible, consistent address builds trust and is a core local-ranking signal — Google matches it against your other listings.', 'sage'));
    $add('nap', __('Business hours shown', 'sage'), $hoursText ? 'pass' : 'warn', 6,
        $hoursText ? __('Hours present.', 'sage') : __('No hours found.', 'sage'),
        __('“Are they open right now?” is one of the most common local searches. Missing hours sends that customer to a competitor.', 'sage'));

    /* --- Local structured data --- */
    $cat('schema', 'SCH', __('Local structured data', 'sage'), __('Machine-readable business details that power map results and knowledge panels.', 'sage'));
    $add('schema', __('LocalBusiness schema', 'sage'), $hasLocalSchema ? 'pass' : 'fail', 10,
        $hasLocalSchema ? sprintf(__('Present: %s', 'sage'), implode(', ', array_slice(array_intersect($schemaTypes, $localSet), 0, 3)) ?: 'LocalBusiness') : __('No LocalBusiness schema.', 'sage'),
        __('This is how you hand Google your name, address, phone, and hours in a format it fully trusts — the backbone of local rich results.', 'sage'));
    $add('schema', __('Address in schema', 'sage'), $yn($schemaAddress), 4,
        $schemaAddress ? __('PostalAddress set.', 'sage') : __('No address in schema.', 'sage'),
        __('A structured address is matched against your Google Business Profile and directory citations to confirm you\'re the same business.', 'sage'));
    $add('schema', __('Phone in schema', 'sage'), $yn($schemaPhone), 3,
        $schemaPhone ? __('telephone set.', 'sage') : __('No phone in schema.', 'sage'),
        __('A structured phone number reinforces your NAP consistency and can surface a call button in results.', 'sage'));
    $add('schema', __('Hours in schema', 'sage'), $schemaHours ? 'pass' : 'warn', 3,
        $schemaHours ? __('openingHours set.', 'sage') : __('No hours in schema.', 'sage'),
        __('Structured hours let Google show “Open now / Closes 5 PM” right in the result — a strong nudge to visit or call.', 'sage'));
    $add('schema', __('Map coordinates (geo)', 'sage'), $schemaGeo ? 'pass' : 'warn', 2,
        $schemaGeo ? __('geo set.', 'sage') : __('No geo coordinates.', 'sage'),
        __('Latitude/longitude pins your exact location, helping “near me” searches place you correctly.', 'sage'));

    /* --- Get found on the map --- */
    $cat('map', 'MAP', __('Get found & get directions', 'sage'), __('The links and cues that put you on the map and guide customers to your door.', 'sage'));
    $add('map', __('Location in the page title', 'sage'), $locInTitle ? 'pass' : 'warn', 6,
        $locInTitle ? __('Town/region in title.', 'sage') : __('No location in title/H1.', 'sage'),
        __('Naming your town in the title (“Bakery in Gettysburg, PA”) is a simple, strong signal for local search intent.', 'sage'));
    $add('map', __('Embedded map', 'sage'), $embeddedMap ? 'pass' : 'warn', 4,
        $embeddedMap ? __('Google Map embedded.', 'sage') : __('No embedded map.', 'sage'),
        __('An embedded map confirms your location to visitors at a glance and reinforces your address to search engines.', 'sage'));
    $add('map', __('Google Business Profile link', 'sage'), $gbpLink ? 'pass' : 'warn', 5,
        $gbpLink ? __('Links to Google Maps/Profile.', 'sage') : __('No Business Profile link.', 'sage'),
        __('Your Google Business Profile drives much of your local traffic and reviews. Linking to it connects your site and your listing.', 'sage'));
    $add('map', __('Directions available', 'sage'), $directions ? 'pass' : 'warn', 3,
        $directions ? __('Directions/map link found.', 'sage') : __('No directions link.', 'sage'),
        __('A one-tap “Get directions” link removes friction for the customer who has already decided to come in.', 'sage'));
    $add('map', __('Mobile-ready', 'sage'), $viewport && $https ? 'pass' : 'warn', 4,
        ($viewport && $https) ? __('Responsive & secure.', 'sage') : __('Check mobile/HTTPS.', 'sage'),
        __('Local searches are overwhelmingly on phones. A fast, secure, mobile-friendly page is the baseline for showing up at all.', 'sage'));

    /* --- Reviews & trust --- */
    $cat('reviews', 'REV', __('Reviews & trust', 'sage'), __('The social proof that turns a search result into a chosen business.', 'sage'));
    $add('reviews', __('Review / rating schema', 'sage'), $schemaRating ? 'pass' : 'warn', 6,
        $schemaRating ? __('Rating markup present.', 'sage') : __('No review schema.', 'sage'),
        __('AggregateRating markup can add gold review stars to your search result — one of the biggest boosts to click-through there is.', 'sage'));
    $add('reviews', __('Social / review profiles linked', 'sage'), $social > 0 ? 'pass' : 'warn', 4,
        $social > 0 ? sprintf(__('%d profile link(s).', 'sage'), $social) : __('No Facebook/Yelp/etc. links.', 'sage'),
        __('Links to your active Facebook, Yelp, or Instagram show you\'re a real, current business and give customers more ways to trust you.', 'sage'));

    // ---- scoring ----
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

    return new \WP_REST_Response([
        'ok'         => true,
        'url'        => $fetch['final'],
        'overall'    => ['score' => $overall, 'grade' => rv_tools_letter($overall)],
        'meta'       => ['schema' => $hasLocalSchema, 'tel' => $tel, 'locality' => $locality, 'reviews' => $schemaRating],
        'categories' => $catOut,
    ], 200);
}
