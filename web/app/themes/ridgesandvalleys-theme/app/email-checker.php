<?php

/**
 * Email deliverability checker — Ridges & Valleys Studio.
 *
 * A DNS-only tool (rv-tools/v1/email) that checks whether a domain is set up so
 * its email is trusted and hard to spoof — the single biggest reason small
 * businesses land in spam or get impersonated. It reads live DNS records with
 * PHP's dns_get_record (no external API): MX (can you receive mail), SPF, DKIM
 * (probing common selectors), and DMARC (published, enforced, reporting).
 *
 * Every check returns a status (pass | warn | fail), the value found, and a
 * plain-English "why it matters". Accepts a bare domain or a full URL.
 */

namespace App;

add_action('rest_api_init', function () {
    register_rest_route('rv-tools/v1', '/email', [
        'methods'             => 'GET',
        'permission_callback' => '__return_true',
        'args'                => ['url' => ['required' => true, 'type' => 'string']],
        'callback'            => __NAMESPACE__ . '\\rv_rest_email',
    ]);
});

/** Reduce a URL or address to a bare registrable domain. */
function emailcheck_domain(string $input): string
{
    $input = trim(strtolower($input));
    if (preg_match('#^https?://#i', $input)) {
        $input = (string) wp_parse_url($input, PHP_URL_HOST);
    }
    $input = preg_replace('#^www\.#', '', $input);
    $input = preg_replace('#[/?].*$#', '', $input);
    return trim($input);
}

/** All TXT strings for a name (empty array on failure). */
function emailcheck_txt(string $name): array
{
    $out = [];
    $recs = @dns_get_record($name, DNS_TXT);
    if (is_array($recs)) {
        foreach ($recs as $r) {
            if (! empty($r['txt'])) {
                $out[] = $r['txt'];
            } elseif (! empty($r['entries']) && is_array($r['entries'])) {
                $out[] = implode('', $r['entries']);
            }
        }
    }
    return $out;
}

function rv_rest_email(\WP_REST_Request $req)
{
    if (! function_exists('dns_get_record')) {
        return new \WP_REST_Response(['ok' => false, 'error' => __('DNS lookups aren\'t available on this server.', 'sage')], 200);
    }

    $domain = emailcheck_domain((string) $req->get_param('url'));
    if (! preg_match('/^[a-z0-9.-]+\.[a-z]{2,}$/', $domain)) {
        return new \WP_REST_Response(['ok' => false, 'error' => __('Enter a valid domain, like yourbusiness.com.', 'sage')], 200);
    }

    // ---- lookups ----
    $a  = @dns_get_record($domain, DNS_A) ?: [];
    $mx = @dns_get_record($domain, DNS_MX) ?: [];
    usort($mx, fn($x, $y) => ($x['pri'] ?? 0) <=> ($y['pri'] ?? 0));

    $txt = emailcheck_txt($domain);
    $spfRecords = array_values(array_filter($txt, fn($t) => stripos($t, 'v=spf1') === 0));
    $spf = $spfRecords[0] ?? '';
    $spfQualifier = '';
    if ($spf !== '') {
        if (preg_match('/([-~?+])all\b/i', $spf, $m)) {
            $spfQualifier = $m[1];
        }
    }

    $dmarcTxt = emailcheck_txt('_dmarc.' . $domain);
    $dmarc = '';
    foreach ($dmarcTxt as $t) {
        if (stripos($t, 'v=DMARC1') === 0) {
            $dmarc = $t;
        }
    }
    $dmarcPolicy = '';
    if ($dmarc !== '' && preg_match('/\bp\s*=\s*(none|quarantine|reject)/i', $dmarc, $m)) {
        $dmarcPolicy = strtolower($m[1]);
    }
    $dmarcRua = $dmarc !== '' && stripos($dmarc, 'rua=') !== false;

    // DKIM — probe common selectors
    $selectors = ['google', 'default', 'selector1', 'selector2', 'k1', 's1', 's2', 'mail', 'dkim', 'smtp', 'mandrill', 'zoho'];
    $dkimSelector = '';
    foreach ($selectors as $sel) {
        $recs = emailcheck_txt($sel . '._domainkey.' . $domain);
        foreach ($recs as $t) {
            if (stripos($t, 'DKIM1') !== false || stripos($t, 'k=rsa') !== false || preg_match('/\bp=[A-Za-z0-9+\/]/', $t)) {
                $dkimSelector = $sel;
                break 2;
            }
        }
    }

    // ---- categories ----
    $categories = [];
    $cat = function (string $key, string $code, string $name, string $desc) use (&$categories) {
        $categories[$key] = ['key' => $key, 'code' => $code, 'name' => $name, 'desc' => $desc, 'checks' => []];
    };
    $add = function (string $key, string $label, string $status, int $weight, string $detail, string $why) use (&$categories) {
        $categories[$key]['checks'][] = ['label' => $label, 'status' => $status, 'detail' => $detail, 'why' => $why, 'weight' => $weight];
    };

    /* --- Receiving --- */
    $cat('mx', 'MX', __('Receiving email', 'sage'), __('Whether the domain is set up to accept email at all.', 'sage'));
    $add(
        'mx',
        __('Domain resolves', 'sage'),
        ! empty($a) ? 'pass' : 'warn',
        4,
        ! empty($a) ? __('DNS is live.', 'sage') : __('No A record found.', 'sage'),
        __('If the domain doesn\'t resolve, neither your website nor your email can work reliably.', 'sage'),
    );
    $add(
        'mx',
        __('Has mail servers (MX)', 'sage'),
        ! empty($mx) ? 'pass' : 'fail',
        12,
        ! empty($mx) ? sprintf(__('%d mail server(s): %s', 'sage'), count($mx), mb_strimwidth($mx[0]['target'] ?? '', 0, 40, '…')) : __('No MX records.', 'sage'),
        __('MX records tell the world where to deliver your email. Without them, mail sent to your domain simply bounces.', 'sage'),
    );

    /* --- SPF --- */
    $cat('spf', 'SPF', __('SPF — sender authorization', 'sage'), __('Says which servers are allowed to send email as your domain.', 'sage'));
    $add(
        'spf',
        __('SPF record published', 'sage'),
        $spf !== '' ? 'pass' : 'fail',
        10,
        $spf !== '' ? mb_strimwidth($spf, 0, 50, '…') : __('No SPF record.', 'sage'),
        __('SPF is the first line of defence against spoofing — without it, anyone can send email pretending to be your domain.', 'sage'),
    );
    $add(
        'spf',
        __('Only one SPF record', 'sage'),
        count($spfRecords) <= 1 ? 'pass' : 'fail',
        4,
        sprintf(__('%d SPF records found.', 'sage'), count($spfRecords)),
        __('Two SPF records is an error that breaks SPF entirely — there must be exactly one.', 'sage'),
    );
    $add(
        'spf',
        __('SPF blocks unknown senders', 'sage'),
        in_array($spfQualifier, ['-', '~'], true) ? 'pass' : ($spf === '' ? 'fail' : ($spfQualifier === '+' ? 'fail' : 'warn')),
        6,
        $spf === '' ? __('No SPF.', 'sage') : ($spfQualifier ? sprintf(__('Ends in “%sall”.', 'sage'), $spfQualifier) : __('No “all” mechanism.', 'sage')),
        __('SPF should end in “~all” or “-all” so unauthorized senders are rejected or flagged. “+all” lets anyone spoof you.', 'sage'),
    );

    /* --- DKIM --- */
    $cat('dkim', 'DKIM', __('DKIM — message signing', 'sage'), __('A cryptographic signature that proves an email really came from you and wasn\'t altered.', 'sage'));
    $add(
        'dkim',
        __('DKIM detected', 'sage'),
        $dkimSelector !== '' ? 'pass' : 'warn',
        8,
        $dkimSelector !== '' ? sprintf(__('Found on selector “%s”.', 'sage'), $dkimSelector) : __('Not detected on common selectors.', 'sage'),
        __('DKIM lets receiving servers verify your mail is genuine. Selectors vary, so “not detected” may just mean a custom name — but many small-business domains truly lack it.', 'sage'),
    );

    /* --- DMARC --- */
    $cat('dmarc', 'DMRC', __('DMARC — anti-phishing policy', 'sage'), __('Ties SPF and DKIM together and tells inboxes what to do with mail that fails.', 'sage'));
    $add(
        'dmarc',
        __('DMARC record published', 'sage'),
        $dmarc !== '' ? 'pass' : 'fail',
        10,
        $dmarc !== '' ? mb_strimwidth($dmarc, 0, 50, '…') : __('No DMARC record.', 'sage'),
        __('DMARC is what actually stops criminals from sending phishing emails “from” your business to your customers.', 'sage'),
    );
    $add(
        'dmarc',
        __('Policy is enforced', 'sage'),
        in_array($dmarcPolicy, ['quarantine', 'reject'], true) ? 'pass' : ($dmarcPolicy === 'none' ? 'warn' : 'fail'),
        6,
        $dmarcPolicy !== '' ? sprintf(__('p=%s', 'sage'), $dmarcPolicy) : __('No policy set.', 'sage'),
        __('A policy of “none” only monitors. “quarantine” or “reject” is what actually blocks spoofed mail from reaching inboxes.', 'sage'),
    );
    $add(
        'dmarc',
        __('Aggregate reporting on', 'sage'),
        $dmarcRua ? 'pass' : 'warn',
        3,
        $dmarcRua ? __('rua reporting set.', 'sage') : __('No rua address.', 'sage'),
        __('Reports show you who is sending mail as your domain — essential for spotting abuse and safely tightening the policy.', 'sage'),
    );

    // ---- scoring ----
    $catOut = [];
    $totEarned = 0;
    $totPossible = 0;
    foreach ($categories as $c) {
        $earned = 0;
        $possible = 0;
        foreach ($c['checks'] as $chk) {
            $possible += $chk['weight'];
            $earned += $chk['status'] === 'pass' ? $chk['weight'] : ($chk['status'] === 'warn' ? $chk['weight'] * 0.5 : 0);
        }
        $score = (int) round($earned / max(1, $possible) * 100);
        $c['score'] = $score;
        $c['grade'] = rv_tools_letter($score);
        $catOut[] = $c;
        $totEarned += $earned;
        $totPossible += $possible;
    }
    $overall = (int) round($totEarned / max(1, $totPossible) * 100);

    return new \WP_REST_Response([
        'ok'         => true,
        'url'        => $domain,
        'overall'    => ['score' => $overall, 'grade' => rv_tools_letter($overall)],
        'meta'       => ['mx' => count($mx), 'spf' => $spf !== '', 'dkim' => $dkimSelector !== '', 'dmarc' => $dmarcPolicy ?: ($dmarc !== '' ? 'set' : 'none')],
        'categories' => $catOut,
    ], 200);
}
