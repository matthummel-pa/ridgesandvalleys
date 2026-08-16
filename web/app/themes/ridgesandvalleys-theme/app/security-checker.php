<?php

/**
 * Website security checker — Ridges & Valleys Studio.
 *
 * One REST endpoint (rv-tools/v1/security) inspects a page and its HTTP
 * response headers for the security signals a browser (and an attacker) sees:
 * HTTPS + HSTS, the modern security headers (CSP, nosniff, clickjacking,
 * Referrer-Policy, Permissions-Policy), information disclosure (server/version
 * leakage), cookie flags, and front-end risks (mixed content, tabnabbing,
 * third-party script sprawl). It also probes whether http:// redirects to
 * https://.
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
    if ($host === '' || ! wp_http_validate_url('http://' . $host . '/')) {
        return null;
    }
    $args = rv_tools_http_args(7, 'RidgesValleysSecurity/1.0');
    $args['redirection'] = 0;
    $res = wp_safe_remote_get('http://' . $host . '/', $args);
    if (is_wp_error($res)) {
        return null;
    }
    $code = (int) wp_remote_retrieve_response_code($res);
    if (in_array($code, [301, 302, 307, 308], true)) {
        return str_starts_with(strtolower((string) wp_remote_retrieve_header($res, 'location')), 'https://');
    }
    return false;
}

function rv_rest_security(\WP_REST_Request $req)
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

    // third-party script hosts
    $thirdParty = [];
    foreach ($xp->query('//script[@src]/@src') as $src) {
        $h = wp_parse_url($src->nodeValue, PHP_URL_HOST);
        if ($h && $h !== $host) $thirdParty[$h] = true;
    }
    $thirdPartyCount = count($thirdParty);

    // cookie flags (only meaningful if cookies are set)
    $hasCookies = $setCookie !== '';
    $ckSecure = stripos($setCookie, 'secure') !== false;
    $ckHttpOnly = stripos($setCookie, 'httponly') !== false;
    $ckSameSite = stripos($setCookie, 'samesite') !== false;

    $redirect = $https ? seccheck_redirects_to_https($host) : false;

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
        $add('cookies', __('No cookies set on this page', 'sage'), 'pass', 4,
            __('This response sets no cookies.', 'sage'),
            __('Fewer cookies means less to protect — a good default for public pages that don\'t need to track anything.', 'sage'));
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
    $add('frontend', __('Third-party script sprawl', 'sage'), $thirdPartyCount <= 4 ? 'pass' : ($thirdPartyCount <= 8 ? 'warn' : 'fail'), 5,
        sprintf(__('%d third-party script source(s).', 'sage'), $thirdPartyCount),
        __('Every external script is code you don\'t control running on your site — each one is a supply-chain risk if it\'s compromised.', 'sage'));

    /* ---- scoring ---- */
    $catOut = []; $totEarned = 0; $totPossible = 0; $headersPresent = 0;
    foreach ([$csp, $nosniff, ($xfo !== '' || $frameCsp), $referrer, $permissions, $hsts] as $h) {
        if ($h) $headersPresent++;
    }
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
        'url'        => $url,
        'overall'    => ['score' => $overall, 'grade' => rv_tools_letter($overall)],
        'meta'       => ['https' => $https, 'headers' => $headersPresent, 'mixed' => $mixed, 'thirdparty' => $thirdPartyCount, 'status' => $fetch['status']],
        'categories' => $catOut,
    ], 200);
}
