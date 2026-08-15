<?php

/**
 * Website security checker — Ridges & Valleys Studio.
 *
 * One REST endpoint (rv-tools/v1/security) inspects a page and its HTTP
 * response headers for the security signals a browser (and an attacker) sees:
 * HTTPS + HSTS, the modern security headers (CSP, nosniff, clickjacking,
 * Referrer-Policy, Permissions-Policy), information disclosure (server/version
 * leakage), cookie flags, named third-party scripts, and — when the page looks
 * like WordPress — whether wp-login.php and xmlrpc.php sit at the default URLs.
 * It also probes whether http:// redirects to https://. Cookie checks are N/A
 * when the page sets none, so they do not inflate the grade.
 *
 * Every check returns status (pass | warn | fail), the value found, and a
 * plain-English "why it matters". This reads headers and markup — it is not a
 * penetration test — so it's an honest first-pass hygiene check.
 */

namespace App;

add_action('rest_api_init', function () {
    register_rest_route('rv-tools/v1', '/security', [
        'methods'             => 'GET',
        'permission_callback' => '__return_true',
        'args'                => ['url' => ['required' => true, 'type' => 'string']],
        'callback'            => __NAMESPACE__ . '\\rv_rest_security',
    ]);
});

/**
 * Does http://host/ redirect to https? true / false / null (couldn't tell).
 */
function seccheck_redirects_to_https(string $host): ?bool
{
    if ($host === '') {
        return null;
    }
    $res = wp_safe_remote_get('http://' . $host . '/', [
        'timeout'     => 7,
        'redirection' => 0,
        'user-agent'  => 'RidgesValleysSecurity/1.0',
    ]);
    if (is_wp_error($res)) {
        return null;
    }
    $code = (int) wp_remote_retrieve_response_code($res);
    if (in_array($code, [301, 302, 307, 308], true)) {
        return str_starts_with(strtolower((string) wp_remote_retrieve_header($res, 'location')), 'https://');
    }
    return false;
}

/** HTTP status for a URL, or null if the request failed. Does not follow redirects. */
function seccheck_probe(string $url): ?int
{
    $res = wp_safe_remote_get($url, [
        'timeout'     => 6,
        'redirection' => 0,
        'user-agent'  => 'RidgesValleysSecurity/1.0',
    ]);
    if (is_wp_error($res)) {
        return null;
    }

    return (int) wp_remote_retrieve_response_code($res);
}

/** Owner-facing name for a third-party script host. */
function seccheck_script_label(string $host): string
{
    $host = strtolower($host);
    $map  = [
        'googletagmanager.com'     => __('Google Tag Manager', 'sage'),
        'google-analytics.com'     => __('Google Analytics', 'sage'),
        'googlesyndication.com'    => __('Google Ads', 'sage'),
        'googleadservices.com'     => __('Google Ads', 'sage'),
        'doubleclick.net'          => __('Google Ads', 'sage'),
        'gstatic.com'              => __('Google', 'sage'),
        'googleapis.com'           => __('Google APIs', 'sage'),
        'recaptcha.net'            => __('reCAPTCHA', 'sage'),
        'connect.facebook.net'     => __('Facebook Pixel', 'sage'),
        'facebook.net'             => __('Facebook Pixel', 'sage'),
        'facebook.com'             => __('Facebook', 'sage'),
        'hotjar.com'               => __('Hotjar', 'sage'),
        'clarity.ms'               => __('Microsoft Clarity', 'sage'),
        'hs-scripts.com'           => __('HubSpot', 'sage'),
        'hsforms.net'              => __('HubSpot Forms', 'sage'),
        'hs-banner.com'            => __('HubSpot', 'sage'),
        'hubspot.com'              => __('HubSpot', 'sage'),
        'hscollectedforms.net'     => __('HubSpot', 'sage'),
        'stripe.com'               => __('Stripe', 'sage'),
        'js.stripe.com'            => __('Stripe', 'sage'),
        'youtube.com'              => __('YouTube', 'sage'),
        'youtu.be'                 => __('YouTube', 'sage'),
        'ytimg.com'                => __('YouTube', 'sage'),
        'vimeo.com'                => __('Vimeo', 'sage'),
        'maps.googleapis.com'      => __('Google Maps', 'sage'),
        'maps.google.com'          => __('Google Maps', 'sage'),
        'cloudflareinsights.com'   => __('Cloudflare Insights', 'sage'),
        'cdnjs.cloudflare.com'     => __('Cloudflare CDN', 'sage'),
        'ajax.googleapis.com'      => __('Google CDN', 'sage'),
        'code.jquery.com'          => __('jQuery', 'sage'),
        'snap.licdn.com'           => __('LinkedIn Insight', 'sage'),
        'ads-twitter.com'          => __('X / Twitter ads', 'sage'),
        'platform.twitter.com'     => __('X / Twitter', 'sage'),
        'instagram.com'            => __('Instagram', 'sage'),
        'tiktok.com'               => __('TikTok', 'sage'),
        'chimpstatic.com'          => __('Mailchimp', 'sage'),
        'list-manage.com'          => __('Mailchimp', 'sage'),
        'mcauto-images-production.sendgrid.net' => __('SendGrid', 'sage'),
        'shopify.com'              => __('Shopify', 'sage'),
        'wp.com'                   => __('WordPress.com', 'sage'),
        'stats.wp.com'             => __('Jetpack Stats', 'sage'),
        'jetpack.com'              => __('Jetpack', 'sage'),
        'cookiebot.com'            => __('Cookiebot', 'sage'),
        'cookielaw.org'            => __('OneTrust', 'sage'),
        'onetrust.com'             => __('OneTrust', 'sage'),
        'cdn.userway.org'          => __('UserWay', 'sage'),
        'widget.trustpilot.com'    => __('Trustpilot', 'sage'),
        'static.addtoany.com'      => __('AddToAny', 'sage'),
        'js-na1.hs-scripts.com'    => __('HubSpot', 'sage'),
    ];
    foreach ($map as $needle => $label) {
        if ($host === $needle) {
            return $label;
        }
    }
    foreach ($map as $needle => $label) {
        if (str_ends_with($host, '.' . $needle)) {
            return $label;
        }
    }

    return preg_replace('/^www\./', '', $host) ?: $host;
}

function rv_rest_security(\WP_REST_Request $req)
{
    $fetch = grader_fetch(tools_normalize_url((string) $req->get_param('url')));
    if (! $fetch['ok']) {
        return new \WP_REST_Response(['ok' => false, 'error' => $fetch['error']], 200);
    }

    $html = $fetch['html'];
    $hdr  = $fetch['header'];
    $dom  = rv_tools_dom($html);
    $xp   = new \DOMXPath($dom);
    $url   = $fetch['final'];
    $parts = wp_parse_url($url);
    $host  = $parts['host'] ?? '';
    $https = str_starts_with($url, 'https://');

    /* ---- header signals ---- */
    $hsts       = $hdr('strict-transport-security');
    $csp        = $hdr('content-security-policy');
    $nosniff    = stripos($hdr('x-content-type-options'), 'nosniff') !== false;
    $xfo        = $hdr('x-frame-options');
    $frameCsp   = stripos($csp, 'frame-ancestors') !== false;
    $referrer   = $hdr('referrer-policy');
    $permissions = $hdr('permissions-policy') ?: $hdr('feature-policy');
    $server     = $hdr('server');
    $poweredBy  = $hdr('x-powered-by');
    $setCookie  = $hdr('set-cookie');

    // version leakage: server string with digits, x-powered-by, generator meta
    $generator = '';
    foreach ($xp->query('//meta[@name="generator"]/@content') as $g) {
        $generator = trim($g->nodeValue);
    }
    $serverLeaks = (bool) preg_match('#/\d#', $server);        // e.g. "Apache/2.4.57"
    $versionLeak = $serverLeaks || $poweredBy !== '' || $generator !== '';

    /* ---- markup signals ---- */
    $mixed = $https ? (int) preg_match_all('#\s(?:src|href)\s*=\s*["\']http://#i', $html) : 0;

    // insecure form actions
    $insecureForms = 0;
    foreach ($dom->getElementsByTagName('form') as $f) {
        if (stripos($f->getAttribute('action'), 'http://') === 0) $insecureForms++;
    }

    // target=_blank without rel noopener/noreferrer (reverse tabnabbing)
    $tabnab = 0;
    foreach ($xp->query('//a[@target="_blank"]') as $a) {
        $rel = strtolower($a->getAttribute('rel'));
        if (strpos($rel, 'noopener') === false && strpos($rel, 'noreferrer') === false) $tabnab++;
    }

    // inline event handlers + inline scripts (CSP hurdles / XSS surface)
    $inlineHandlers = (int) preg_match_all('/\son[a-z]+\s*=\s*["\']/i', $html);
    $inlineScripts = 0;
    foreach ($xp->query('//script[not(@src)]') as $s) {
        $type = strtolower($s->getAttribute('type'));
        if ($type === 'application/ld+json' || $type === 'application/json') continue;
        if (trim($s->textContent) !== '') $inlineScripts++;
    }

    // third-party script hosts → owner-facing names
    $thirdParty = [];
    foreach ($xp->query('//script[@src]/@src') as $src) {
        $h = wp_parse_url($src->nodeValue, PHP_URL_HOST);
        if ($h && strcasecmp($h, $host) !== 0) {
            $thirdParty[$h] = true;
        }
    }
    $scriptLabels = [];
    foreach (array_keys($thirdParty) as $h) {
        $scriptLabels[seccheck_script_label((string) $h)] = true;
    }
    $scriptLabels     = array_keys($scriptLabels);
    natcasesort($scriptLabels);
    $scriptLabels     = array_values($scriptLabels);
    $thirdPartyCount  = count($thirdParty);
    $scriptDetail     = __('None found.', 'sage');
    if ($scriptLabels !== []) {
        $shown        = array_slice($scriptLabels, 0, 4);
        $extra        = count($scriptLabels) - count($shown);
        $scriptDetail = implode(', ', $shown) . ($extra > 0 ? sprintf(__(' + %d more', 'sage'), $extra) : '');
    }

    // cookie flags (only meaningful if cookies are set)
    $hasCookies = $setCookie !== '';
    $ckSecure = stripos($setCookie, 'secure') !== false;
    $ckHttpOnly = stripos($setCookie, 'httponly') !== false;
    $ckSameSite = stripos($setCookie, 'samesite') !== false;

    $redirect = $https ? seccheck_redirects_to_https($host) : false;

    $origin  = (($https ? 'https://' : 'http://') . $host);
    $looksWp = (bool) preg_match('/wordpress/i', $generator)
        || str_contains($html, '/wp-content/')
        || str_contains($html, '/wp-includes/')
        || str_contains($html, 'wp-json')
        || (bool) preg_match('/wordpress/i', $poweredBy);

    /* ---- categories ---- */
    $categories = [];
    $cat = function (string $key, string $code, string $name, string $desc) use (&$categories) {
        $categories[$key] = ['key' => $key, 'code' => $code, 'name' => $name, 'desc' => $desc, 'checks' => []];
    };
    $add = function (string $key, string $label, string $status, int $weight, string $detail, string $why) use (&$categories) {
        $categories[$key]['checks'][] = ['label' => $label, 'status' => $status, 'detail' => $detail, 'why' => $why, 'weight' => $weight];
    };

    /* --- Transport / HTTPS --- */
    $cat('https', 'TLS', __('HTTPS & transport', 'sage'), __('Whether traffic between your visitors and your site is encrypted and can\'t be tampered with.', 'sage'));
    $add('https', __('Serves over HTTPS', 'sage'), $https ? 'pass' : 'fail', 14,
        $https ? __('Encrypted connection.', 'sage') : __('Site is served over plain http.', 'sage'),
        __('Without HTTPS, anything typed on your site — messages, logins — travels in the clear and browsers label you “Not secure.”', 'sage'));
    $add('https', __('http redirects to https', 'sage'), $redirect === true ? 'pass' : ($redirect === null ? 'warn' : 'fail'), 8,
        $redirect === true ? __('Redirects to the secure version.', 'sage') : ($redirect === null ? __('Couldn\'t verify.', 'sage') : __('http is served without redirecting.', 'sage')),
        __('If the insecure http version still loads, visitors and old links can land on an unencrypted page. Force everyone to https.', 'sage'));
    $add('https', __('HSTS enabled', 'sage'), $hsts !== '' ? 'pass' : 'warn', 8,
        $hsts !== '' ? mb_strimwidth($hsts, 0, 48, '…') : __('No Strict-Transport-Security header.', 'sage'),
        __('HSTS tells browsers to only ever connect over https, closing the brief window an attacker could use to downgrade the connection.', 'sage'));
    $add('https', __('No mixed content', 'sage'), $mixed === 0 ? 'pass' : 'warn', 6,
        $mixed ? sprintf(__('%d insecure resource(s) on a secure page.', 'sage'), $mixed) : __('All resources load securely.', 'sage'),
        __('An https page pulling http:// images or scripts breaks the padlock and can be blocked or tampered with in transit.', 'sage'));
    $add('https', __('Forms submit securely', 'sage'), $insecureForms === 0 ? 'pass' : 'fail', 6,
        $insecureForms ? sprintf(__('%d form posts to http.', 'sage'), $insecureForms) : __('No insecure form actions.', 'sage'),
        __('A form that submits over http sends whatever a visitor typed — including personal details — unencrypted.', 'sage'));

    /* --- Security headers --- */
    $cat('headers', 'HDR', __('Security headers', 'sage'), __('The response headers modern browsers use to defend your visitors from common attacks.', 'sage'));
    $add('headers', __('Content-Security-Policy', 'sage'), $csp !== '' ? 'pass' : 'warn', 8,
        $csp !== '' ? __('CSP present.', 'sage') : __('No CSP header.', 'sage'),
        __('A CSP restricts what scripts and resources can run, which is the strongest defence against cross-site scripting (XSS).', 'sage'));
    $add('headers', __('X-Content-Type-Options', 'sage'), $nosniff ? 'pass' : 'warn', 5,
        $nosniff ? __('nosniff set.', 'sage') : __('Not set.', 'sage'),
        __('Stops browsers from “guessing” a file\'s type, a trick used to smuggle scripts past filters.', 'sage'));
    $add('headers', __('Clickjacking protection', 'sage'), ($xfo !== '' || $frameCsp) ? 'pass' : 'warn', 6,
        ($xfo !== '' || $frameCsp) ? __('Frame protection set.', 'sage') : __('No X-Frame-Options / frame-ancestors.', 'sage'),
        __('Prevents another site from embedding yours invisibly to trick your visitors into clicking things they can\'t see.', 'sage'));
    $add('headers', __('Referrer-Policy', 'sage'), $referrer !== '' ? 'pass' : 'warn', 3,
        $referrer !== '' ? mb_strimwidth($referrer, 0, 40, '…') : __('Not set.', 'sage'),
        __('Controls how much of your URL is shared when a visitor clicks away — a small but real privacy leak if left open.', 'sage'));
    $add('headers', __('Permissions-Policy', 'sage'), $permissions !== '' ? 'pass' : 'warn', 3,
        $permissions !== '' ? __('Set.', 'sage') : __('Not set.', 'sage'),
        __('Lets you switch off browser features (camera, microphone, geolocation) your site doesn\'t use, shrinking the attack surface.', 'sage'));

    /* --- Information disclosure --- */
    $cat('disclosure', 'INF', __('Information disclosure', 'sage'), __('How much your site tells strangers about the exact software it runs.', 'sage'));
    $add('disclosure', __('Server software version hidden', 'sage'), $serverLeaks ? 'warn' : 'pass', 5,
        $serverLeaks ? sprintf(__('Exposes: %s', 'sage'), mb_strimwidth($server, 0, 40, '…')) : __('No version in Server header.', 'sage'),
        __('Broadcasting your exact server version hands attackers a shortlist of known exploits to try against you.', 'sage'));
    $add('disclosure', __('No X-Powered-By header', 'sage'), $poweredBy === '' ? 'pass' : 'warn', 4,
        $poweredBy === '' ? __('Not exposed.', 'sage') : sprintf(__('Exposes: %s', 'sage'), mb_strimwidth($poweredBy, 0, 40, '…')),
        __('This header leaks your language/framework version — useful to nobody but an attacker.', 'sage'));
    $add('disclosure', __('CMS version not advertised', 'sage'), $generator === '' ? 'pass' : 'warn', 4,
        $generator === '' ? __('No generator meta.', 'sage') : sprintf(__('Meta generator: %s', 'sage'), mb_strimwidth($generator, 0, 40, '…')),
        __('A “generator” tag naming your CMS and version tells bots exactly which vulnerabilities to probe for.', 'sage'));

    /* --- Cookies --- */
    $cat('cookies', 'CKY', __('Cookies', 'sage'), __('Whether cookies your site sets are protected from theft and cross-site abuse.', 'sage'));
    if ($hasCookies) {
        $add('cookies', __('Secure flag on cookies', 'sage'), $ckSecure ? 'pass' : 'warn', 6,
            $ckSecure ? __('Secure flag present.', 'sage') : __('Missing Secure flag.', 'sage'),
            __('The Secure flag stops cookies from being sent over unencrypted http, where they could be intercepted.', 'sage'));
        $add('cookies', __('HttpOnly flag on cookies', 'sage'), $ckHttpOnly ? 'pass' : 'warn', 5,
            $ckHttpOnly ? __('HttpOnly present.', 'sage') : __('Missing HttpOnly flag.', 'sage'),
            __('HttpOnly hides cookies from JavaScript, so a script injected via XSS can\'t steal a logged-in session.', 'sage'));
        $add('cookies', __('SameSite attribute', 'sage'), $ckSameSite ? 'pass' : 'warn', 4,
            $ckSameSite ? __('SameSite set.', 'sage') : __('No SameSite attribute.', 'sage'),
            __('SameSite limits when cookies are sent cross-site, blunting cross-site request forgery (CSRF) attacks.', 'sage'));
    } else {
        $add('cookies', __('No cookies set on this page', 'sage'), 'na', 0,
            __('This response sets no cookies — so there is nothing to grade here.', 'sage'),
            __('A public page with no cookies is fine. It is not the same as “cookies are locked down.” Logins and forms on other pages may still set them.', 'sage'));
    }

    /* --- Front-end risks --- */
    $cat('frontend', 'APP', __('Front-end risks', 'sage'), __('Patterns in the page itself that widen the attack surface or leak visitors between sites.', 'sage'));
    $add('frontend', __('External links are safe', 'sage'), $tabnab === 0 ? 'pass' : ($tabnab <= 3 ? 'warn' : 'fail'), 6,
        $tabnab ? sprintf(__('%d target="_blank" links without rel="noopener".', 'sage'), $tabnab) : __('New-tab links protected.', 'sage'),
        __('Links that open a new tab without rel="noopener" let the destination page quietly redirect your original tab (tabnabbing).', 'sage'));
    $add('frontend', __('Few inline event handlers', 'sage'), $inlineHandlers === 0 ? 'pass' : ($inlineHandlers <= 8 ? 'warn' : 'fail'), 5,
        $inlineHandlers ? sprintf(__('%d inline handlers (onclick, etc.).', 'sage'), $inlineHandlers) : __('None found.', 'sage'),
        __('Inline handlers force a looser Content-Security-Policy and are a classic foothold for injected scripts.', 'sage'));
    $add('frontend', __('Limited inline scripts', 'sage'), $inlineScripts <= 3 ? 'pass' : ($inlineScripts <= 8 ? 'warn' : 'fail'), 4,
        sprintf(__('%d inline <script> blocks.', 'sage'), $inlineScripts),
        __('Lots of inline JavaScript makes a strict CSP hard to adopt and is harder to audit for tampering.', 'sage'));
    $add('frontend', __('Third-party scripts', 'sage'), $thirdPartyCount <= 4 ? 'pass' : ($thirdPartyCount <= 8 ? 'warn' : 'fail'), 5,
        $thirdPartyCount === 0 ? $scriptDetail : sprintf(
            /* translators: 1: count, 2: named sources */
            _n('%d source: %s', '%d sources: %s', $thirdPartyCount, 'sage'),
            $thirdPartyCount,
            $scriptDetail
        ),
        __('Every external script is code you don\'t control running on your site — each one is a supply-chain risk if it\'s compromised.', 'sage'));

    /* --- WordPress fingerprint (only when the page looks like WP) --- */
    if ($looksWp) {
        $loginCode  = seccheck_probe($origin . '/wp-login.php');
        $xmlrpcCode = seccheck_probe($origin . '/xmlrpc.php');
        $loginOpen  = in_array($loginCode, [200, 301, 302, 303, 307, 308], true);
        $xmlrpcOpen = in_array($xmlrpcCode, [200, 405], true);
        $xmlrpcOff  = in_array($xmlrpcCode, [404, 403, 410], true);

        $cat('wordpress', 'WP', __('Looks like WordPress', 'sage'), __('A fingerprint, not a hack test — the usual login and xmlrpc addresses that most WordPress sites still expose.', 'sage'));
        $add('wordpress', __('Login not sitting at the default URL', 'sage'), $loginOpen ? 'warn' : ($loginCode === null ? 'na' : 'pass'), $loginCode === null ? 0 : 4,
            $loginOpen ? __('wp-login.php loads (or redirects there).', 'sage') : ($loginCode === null ? __('Couldn\'t check.', 'sage') : sprintf(__('HTTP %d at wp-login.php.', 'sage'), (int) $loginCode)),
            __('Bots hammer /wp-login.php. Moving or gating it does not replace updates and strong passwords — it just takes you off the easiest list.', 'sage'));
        $add('wordpress', __('xmlrpc.php not wide open', 'sage'), $xmlrpcOpen ? 'warn' : ($xmlrpcCode === null ? 'na' : ($xmlrpcOff ? 'pass' : 'warn')), $xmlrpcCode === null ? 0 : 5,
            $xmlrpcOpen ? __('xmlrpc.php responds — bots use this to brute-force logins.', 'sage') : ($xmlrpcCode === null ? __('Couldn\'t check.', 'sage') : sprintf(__('HTTP %d at xmlrpc.php.', 'sage'), (int) $xmlrpcCode)),
            __('xmlrpc is an old WordPress API. If you do not need the WordPress mobile app talking to the site, turning it off removes a noisy attack path.', 'sage'));
    }

    /* ---- scoring ---- */
    $catOut = []; $totEarned = 0; $totPossible = 0; $headersPresent = 0;
    foreach ([$csp, $nosniff, ($xfo !== '' || $frameCsp), $referrer, $permissions, $hsts] as $h) {
        if ($h) $headersPresent++;
    }
    foreach ($categories as $c) {
        $earned = 0; $possible = 0;
        foreach ($c['checks'] as $chk) {
            if (($chk['status'] ?? '') === 'na' || (int) ($chk['weight'] ?? 0) <= 0) {
                continue;
            }
            $possible += $chk['weight'];
            $earned += $chk['status'] === 'pass' ? $chk['weight'] : ($chk['status'] === 'warn' ? $chk['weight'] * 0.5 : 0);
        }
        if (($c['key'] ?? '') === 'wordpress') {
            $c['score'] = 0;
            $c['grade'] = 'Info';
            $catOut[] = $c;
            continue;
        }
        if ($possible <= 0) {
            $c['score'] = 0;
            $c['grade'] = 'N/A';
        } else {
            $score = (int) round($earned / $possible * 100);
            $c['score'] = $score;
            $c['grade'] = rv_tools_letter($score);
            $totEarned += $earned;
            $totPossible += $possible;
        }
        $catOut[] = $c;
    }
    $overall = (int) round($totEarned / max(1, $totPossible) * 100);

    return new \WP_REST_Response([
        'ok'         => true,
        'url'        => $url,
        'overall'    => ['score' => $overall, 'grade' => rv_tools_letter($overall)],
        'meta'       => [
            'https'      => $https,
            'headers'    => $headersPresent,
            'mixed'      => $mixed,
            'thirdparty' => $thirdPartyCount,
            'scripts'    => $scriptLabels,
            'wordpress'  => $looksWp,
            'status'     => $fetch['status'],
        ],
        'categories' => $catOut,
    ], 200);
}
