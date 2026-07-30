<?php

/**
 * GitHub social proof: profile + public repositories.
 *
 * Pulls a GitHub account's profile (avatar, bio, follower / repo / star counts)
 * and its public repositories via the REST API and renders them as social
 * proof. No OAuth and no block editor.
 *
 * Responses are cached in transients (6 hours) so the API is hit at most a few
 * times a day, well inside GitHub's rate limit. On any error it degrades to an
 * empty result (cached briefly) so the page never breaks.
 *
 * Configuration:
 *   - Everything you can display, plus GitHub's own repo-endpoint parameters and
 *     a live repository picker: Appearance → GitHub.
 *   - Optional API token (higher rate limit): stored encrypted on the Theme APIs
 *     page, i.e. \App\integration('github', 'token'), or the
 *     RV_INTEGRATION_GITHUB_TOKEN wp-config constant.
 *
 * Display it with the [github_social_proof] shortcode, or call the render
 * helpers directly in a template:
 *     {!! \App\github_profile_card() !!}
 *     {!! \App\github_repos_grid() !!}
 */

namespace App;

/** Allowed values for the GitHub repo-endpoint parameters we expose. */
const GITHUB_TYPES      = ['owner', 'member', 'all'];
const GITHUB_SORTS      = ['pushed', 'updated', 'created', 'name', 'stars'];
const GITHUB_DIRECTIONS = ['desc', 'asc'];

/** Boolean display/fetch toggles and their defaults. */
function github_toggle_defaults(): array
{
    return [
        'include_forks'    => false,
        'include_archived' => false,
        'show_profile'     => true,
        'show_avatar'      => true,
        'show_bio'         => true,
        'show_meta'        => true,
        'stat_followers'   => true,
        'stat_following'   => false,
        'stat_repos'       => true,
        'stat_stars'       => true,
        'stat_gists'       => false,
        'show_desc'        => true,
        'show_language'    => true,
        'show_stars'       => true,
        'show_forks'       => false,
        'show_topics'      => false,
        'show_updated'     => false,
    ];
}

/**
 * Merged GitHub settings (saved option → theme-mod → default).
 */
function github_settings(): array
{
    $o = get_option('rv_github_settings', []);
    if (! is_array($o)) {
        $o = [];
    }

    $bool = fn (string $k, bool $d) => array_key_exists($k, $o) ? (bool) $o[$k] : $d;

    $typeV = $o['type'] ?? 'owner';
    $sortV = $o['sort'] ?? 'pushed';
    $dirV  = $o['direction'] ?? 'desc';
    $modeV = $o['mode'] ?? 'auto';
    $type  = in_array($typeV, GITHUB_TYPES, true) ? $typeV : 'owner';
    $sort  = in_array($sortV, GITHUB_SORTS, true) ? $sortV : 'pushed';
    $dir   = in_array($dirV, GITHUB_DIRECTIONS, true) ? $dirV : 'desc';
    $mode  = in_array($modeV, ['auto', 'pick'], true) ? $modeV : 'auto';

    $settings = [
        // Account + API
        'owner'     => (string) ($o['owner'] ?? get_theme_mod('rv_github_owner', 'matthummel-pa')),
        'type'      => $type,
        'sort'      => $sort,
        'direction' => $dir,
        'min_stars' => max(0, (int) ($o['min_stars'] ?? 0)),
        // Selection
        'mode'      => $mode,
        'count'     => min(100, max(1, (int) ($o['count'] ?? 6))),
        'selected'  => array_values(array_filter(array_map('strval', (array) ($o['selected'] ?? [])))),
        'featured'  => github_csv_to_list($o['featured'] ?? ''),
        'exclude'   => github_csv_to_list($o['exclude'] ?? ''),
        // Text
        'headline'  => (string) ($o['headline'] ?? ''),
        'intro'     => (string) ($o['intro'] ?? ''),
    ];

    foreach (github_toggle_defaults() as $k => $d) {
        $settings[$k] = $bool($k, $d);
    }

    return $settings;
}

/** Split a comma/space/newline separated string into a clean list. */
function github_csv_to_list($value): array
{
    $parts = preg_split('/[\s,]+/', (string) $value, -1, PREG_SPLIT_NO_EMPTY) ?: [];
    return array_values(array_unique(array_map('trim', $parts)));
}

/** GitHub account whose profile and public repos are shown. */
function github_owner(): string
{
    return (string) apply_filters('rv/github_owner', github_settings()['owner']);
}

/** Optional access token (from the encrypted Theme APIs store or a constant). */
function github_token(): string
{
    $token = function_exists('App\\integration') ? (string) integration('github', 'token') : '';
    return (string) apply_filters('rv/github_token', $token);
}

/** Request headers for the GitHub API, adding auth only when a token exists. */
function github_headers(): array
{
    $headers = [
        'Accept'               => 'application/vnd.github+json',
        'X-GitHub-Api-Version' => '2022-11-28',
        'User-Agent'           => 'RidgesAndValleysTheme/1.0 (+' . home_url('/') . ')',
    ];
    $token = github_token();
    if ($token !== '') {
        $headers['Authorization'] = 'Bearer ' . $token;
    }
    return $headers;
}

/** GET a GitHub API URL; returns decoded data or null on any failure. */
function github_get(string $url)
{
    $res = wp_remote_get($url, ['timeout' => 8, 'headers' => github_headers()]);
    if (is_wp_error($res) || (int) wp_remote_retrieve_response_code($res) !== 200) {
        return null;
    }
    $data = json_decode(wp_remote_retrieve_body($res), true);
    return is_array($data) ? $data : null;
}

/** Cache version, bumped whenever settings are saved. */
function github_cache_ver(): int
{
    return (int) get_option('rv_github_cachev', 1);
}

/** Transient key, namespaced by owner, fetch params, auth, and cache version. */
function github_cache_key(string $what): string
{
    $s   = github_settings();
    $sig = md5(implode('|', [
        github_owner(),
        $s['type'],
        $s['sort'],
        $s['direction'],
        github_token() !== '' ? 'auth' : 'anon',
        github_cache_ver(),
    ]));
    return 'rv_gh_' . $what . '_' . $sig;
}

/**
 * The configured account's public profile.
 */
function github_profile(): ?array
{
    $owner = github_owner();
    if (! $owner) {
        return null;
    }

    $key    = github_cache_key('profile');
    $cached = get_transient($key);
    if (is_array($cached)) {
        return $cached ?: null;
    }

    $data = github_get('https://api.github.com/users/' . rawurlencode($owner));
    if (! $data || empty($data['login'])) {
        set_transient($key, [], 15 * MINUTE_IN_SECONDS);
        return null;
    }

    $profile = [
        'login'        => (string) ($data['login'] ?? ''),
        'name'         => (string) ($data['name'] ?? ''),
        'avatar'       => (string) ($data['avatar_url'] ?? ''),
        'bio'          => (string) ($data['bio'] ?? ''),
        'url'          => (string) ($data['html_url'] ?? ''),
        'followers'    => (int) ($data['followers'] ?? 0),
        'following'    => (int) ($data['following'] ?? 0),
        'public_repos' => (int) ($data['public_repos'] ?? 0),
        'public_gists' => (int) ($data['public_gists'] ?? 0),
        'company'      => (string) ($data['company'] ?? ''),
        'location'     => (string) ($data['location'] ?? ''),
        'blog'         => (string) ($data['blog'] ?? ''),
    ];

    set_transient($key, $profile, 6 * HOUR_IN_SECONDS);
    return $profile;
}

/**
 * All public repositories (normalized), fetched with the configured GitHub
 * repo-endpoint parameters and cached. Fork/archived flags are retained so the
 * picker can list them; display-time filtering decides what actually shows.
 */
function github_all_repos(): array
{
    $owner = github_owner();
    if (! $owner) {
        return [];
    }

    $key    = github_cache_key('repos');
    $cached = get_transient($key);
    if (is_array($cached)) {
        return $cached;
    }

    $s = github_settings();
    // Map our sort choices onto the values the API understands.
    $apiSort = in_array($s['sort'], ['pushed', 'updated', 'created'], true)
        ? $s['sort']
        : ($s['sort'] === 'name' ? 'full_name' : 'pushed');

    $endpoint = add_query_arg(
        [
            'per_page'  => 100,
            'type'      => $s['type'],
            'sort'      => $apiSort,
            'direction' => $s['direction'],
        ],
        'https://api.github.com/users/' . rawurlencode($owner) . '/repos'
    );

    $data = github_get($endpoint);
    if (! is_array($data)) {
        set_transient($key, [], 15 * MINUTE_IN_SECONDS);
        return [];
    }

    $repos = [];
    foreach ($data as $repo) {
        if (! is_array($repo) || ! empty($repo['private'])) {
            continue;
        }
        $name    = (string) ($repo['name'] ?? '');
        $license = '';
        if (! empty($repo['license']) && is_array($repo['license'])) {
            $license = (string) ($repo['license']['spdx_id'] ?? $repo['license']['name'] ?? '');
        }
        $repos[] = [
            'name'     => $name,
            'title'    => ucwords(trim(str_replace(['-', '_'], ' ', $name))),
            'desc'     => (string) ($repo['description'] ?? ''),
            'url'      => (string) ($repo['html_url'] ?? ''),
            'homepage' => (string) ($repo['homepage'] ?? ''),
            'language' => (string) ($repo['language'] ?? ''),
            'stars'    => (int) ($repo['stargazers_count'] ?? 0),
            'forks'    => (int) ($repo['forks_count'] ?? 0),
            'updated'  => (string) ($repo['pushed_at'] ?? $repo['updated_at'] ?? ''),
            'license'  => $license,
            'topics'   => array_values(array_filter((array) ($repo['topics'] ?? []))),
            'fork'     => ! empty($repo['fork']),
            'archived' => ! empty($repo['archived']),
        ];
    }

    // Client-side sort for options the API can't do directly.
    if ($s['sort'] === 'stars') {
        usort($repos, fn ($a, $b) => $s['direction'] === 'asc' ? $a['stars'] <=> $b['stars'] : $b['stars'] <=> $a['stars']);
    }

    set_transient($key, $repos, 6 * HOUR_IN_SECONDS);
    return $repos;
}

/**
 * Curated repositories to display, honoring mode (auto/pick), filters, and count.
 */
function github_repos(?int $count = null): array
{
    $s   = github_settings();
    $all = github_all_repos();
    if (empty($all)) {
        return [];
    }

    // Pick mode: exactly the chosen repos, in the fetched order.
    if ($s['mode'] === 'pick') {
        if (empty($s['selected'])) {
            return [];
        }
        $selected = array_flip($s['selected']);
        return array_values(array_filter($all, fn ($r) => isset($selected[$r['name']])));
    }

    // Auto mode: apply filters, pin featured, sort already applied, then slice.
    $repos = array_values(array_filter($all, function ($r) use ($s) {
        if (! $s['include_forks'] && $r['fork']) {
            return false;
        }
        if (! $s['include_archived'] && $r['archived']) {
            return false;
        }
        if ($s['min_stars'] > 0 && $r['stars'] < $s['min_stars']) {
            return false;
        }
        if (in_array($r['name'], $s['exclude'], true)) {
            return false;
        }
        return true;
    }));

    $legacyExclude = (array) apply_filters('rv/github_exclude', []);
    if ($legacyExclude) {
        $repos = array_values(array_filter($repos, fn ($r) => ! in_array($r['name'], $legacyExclude, true)));
    }

    if ($s['sort'] === 'name') {
        usort($repos, fn ($a, $b) => $s['direction'] === 'asc' ? strcasecmp($a['title'], $b['title']) : strcasecmp($b['title'], $a['title']));
    }

    if (! empty($s['featured'])) {
        $byName = [];
        foreach ($repos as $r) {
            $byName[$r['name']] = $r;
        }
        $pinned = [];
        foreach ($s['featured'] as $fname) {
            if (isset($byName[$fname])) {
                $pinned[] = $byName[$fname];
                unset($byName[$fname]);
            }
        }
        $repos = array_merge($pinned, array_values($byName));
    }

    return array_slice($repos, 0, max(1, (int) ($count ?? $s['count'])));
}

/** Total stars across the account's own (non-fork) public repositories. */
function github_total_stars(): int
{
    return array_sum(array_map(
        fn ($r) => $r['fork'] ? 0 : (int) $r['stars'],
        github_all_repos()
    ));
}

/** Bump the cache version so cached responses are re-fetched. */
function github_flush_cache(): void
{
    update_option('rv_github_cachev', github_cache_ver() + 1);
}

/**
 * The profile "social proof" card, honoring the display toggles.
 */
function github_profile_card(): string
{
    $p = github_profile();
    if (! $p) {
        return '';
    }
    $s = github_settings();

    github_print_styles();

    $stats = [];
    if ($s['stat_followers']) {
        $stats[] = [number_format_i18n($p['followers']), _n('follower', 'followers', $p['followers'], 'sage')];
    }
    if ($s['stat_following']) {
        $stats[] = [number_format_i18n($p['following']), __('following', 'sage')];
    }
    if ($s['stat_repos']) {
        $stats[] = [number_format_i18n($p['public_repos']), __('public repos', 'sage')];
    }
    if ($s['stat_stars']) {
        $stars = github_total_stars();
        if ($stars > 0) {
            $stats[] = ['★ ' . number_format_i18n($stars), __('stars earned', 'sage')];
        }
    }
    if ($s['stat_gists']) {
        $stats[] = [number_format_i18n($p['public_gists']), __('public gists', 'sage')];
    }

    $statsHtml = '';
    foreach ($stats as $st) {
        $statsHtml .= '<span class="rv-gh-stat"><strong>' . esc_html($st[0]) . '</strong>' . esc_html($st[1]) . '</span>';
    }

    $displayName = $p['name'] ?: $p['login'];
    $meta        = $s['show_meta'] ? array_filter([$p['company'], $p['location']]) : [];

    return '<div class="rv-gh-profile">'
        . ($s['show_avatar'] && $p['avatar'] ? '<img class="rv-gh-avatar" src="' . esc_url($p['avatar']) . '&s=160" alt="" width="80" height="80" loading="lazy" />' : '')
        . '<div class="rv-gh-body">'
        . '<div class="rv-gh-name">' . esc_html($displayName)
        . ' <a href="' . esc_url($p['url']) . '" target="_blank" rel="noopener noreferrer">@' . esc_html($p['login']) . '</a></div>'
        . ($s['show_bio'] && $p['bio'] ? '<p class="rv-gh-bio">' . esc_html($p['bio']) . '</p>' : '')
        . ($meta ? '<p class="rv-gh-subtle">' . esc_html(implode(' · ', $meta)) . '</p>' : '')
        . ($statsHtml ? '<div class="rv-gh-stats">' . $statsHtml . '</div>' : '')
        . '</div></div>';
}

/**
 * Render the GitHub repos as work-style cards (empty string if none),
 * honoring the per-field display toggles.
 */
function github_repos_grid(?int $count = null): string
{
    $repos = github_repos($count);
    if (empty($repos)) {
        return '';
    }
    $s = github_settings();

    $cards = '';
    foreach ($repos as $r) {
        $link = github_repo_permalink($r['name']);

        $meta = [];
        if ($s['show_language'] && $r['language']) {
            $meta[] = esc_html($r['language']);
        }
        if ($s['show_stars'] && $r['stars'] > 0) {
            $meta[] = '★ ' . (int) $r['stars'];
        }
        if ($s['show_forks'] && $r['forks'] > 0) {
            $meta[] = '⑂ ' . (int) $r['forks'];
        }
        if ($s['show_updated'] && $r['updated']) {
            $ts = strtotime($r['updated']);
            if ($ts) {
                /* translators: %s: human-readable time difference. */
                $meta[] = esc_html(sprintf(__('updated %s ago', 'sage'), human_time_diff($ts)));
            }
        }
        $eyebrow = $meta ? implode(' · ', $meta) : __('GitHub', 'sage');

        $topics = '';
        if ($s['show_topics'] && ! empty($r['topics'])) {
            $chips = '';
            foreach (array_slice($r['topics'], 0, 4) as $t) {
                $chips .= '<span class="rv-mchip">' . esc_html($t) . '</span>';
            }
            $topics = '<span class="rv-chips" style="margin-top:.6rem">' . $chips . '</span>';
        }

        $cards .= '<article class="rv-card rv-work-card">'
            . '<a class="rv-work-link rv-work-link-nothumb" href="' . esc_url($link) . '">'
            . '<span class="rv-work-body">'
            . '<span class="rv-eyebrow">' . esc_html($eyebrow) . '</span>'
            . '<span class="rv-work-title">' . esc_html($r['title']) . '</span>'
            . ($s['show_desc'] && $r['desc'] ? '<span class="rv-work-excerpt">' . esc_html($r['desc']) . '</span>' : '')
            . $topics
            . '</span></a></article>';
    }

    return '<div class="rv-grid" style="grid-template-columns:repeat(auto-fit,minmax(320px,1fr))">' . $cards . '</div>';
}

/**
 * Full social-proof section (profile card + repo grid), used by the shortcode.
 */
function github_social_proof(array $args = []): string
{
    $settings = github_settings();
    $count    = isset($args['count']) ? (int) $args['count'] : null;
    $showProf = isset($args['show_profile'])
        ? filter_var($args['show_profile'], FILTER_VALIDATE_BOOLEAN)
        : $settings['show_profile'];

    $profile = $showProf ? github_profile_card() : '';
    $grid    = github_repos_grid($count);

    if ($profile === '' && $grid === '') {
        return '';
    }

    $headline = $args['headline'] ?? $settings['headline'];
    $intro    = $args['intro'] ?? $settings['intro'];

    $inner = '';
    if ($headline !== '') {
        $inner .= '<h2 class="rv-section-title">' . esc_html($headline) . '</h2>';
    }
    if ($intro !== '') {
        $inner .= '<p class="rv-page-intro">' . esc_html($intro) . '</p>';
    }
    if ($profile) {
        $inner .= '<div style="margin-top:1.5rem">' . $profile . '</div>';
    }
    if ($grid) {
        $inner .= '<div style="margin-top:1.75rem">' . $grid . '</div>';
    }

    return '<div class="rv-gh-socialproof">' . $inner . '</div>';
}

add_shortcode('github_social_proof', function ($atts) {
    $atts = shortcode_atts([
        'count'        => '',
        'show_profile' => '',
        'headline'     => '',
        'intro'        => '',
    ], $atts, 'github_social_proof');

    $args = [];
    if ($atts['count'] !== '') {
        $args['count'] = (int) $atts['count'];
    }
    if ($atts['show_profile'] !== '') {
        $args['show_profile'] = $atts['show_profile'];
    }
    if ($atts['headline'] !== '') {
        $args['headline'] = $atts['headline'];
    }
    if ($atts['intro'] !== '') {
        $args['intro'] = $atts['intro'];
    }

    return github_social_proof($args);
});

/** Print the small style block for the profile card once per request. */
function github_print_styles(): void
{
    static $done = false;
    if ($done) {
        return;
    }
    $done = true;

    echo '<style id="rv-gh-styles">'
        . '.rv-gh-profile{display:flex;gap:1.25rem;align-items:flex-start;background:color-mix(in srgb,var(--color-pine,#2E5245) 6%,transparent);border:1px solid color-mix(in srgb,var(--color-pine,#2E5245) 18%,transparent);border-radius:14px;padding:1.25rem 1.4rem}'
        . '.rv-gh-avatar{border-radius:50%;flex:0 0 auto;width:80px;height:80px;object-fit:cover}'
        . '.rv-gh-name{font-weight:700;font-size:1.15rem;line-height:1.2}'
        . '.rv-gh-name a{color:var(--color-clay,#B0553A);text-decoration:none;font-weight:600;font-size:.95rem}'
        . '.rv-gh-bio{margin:.35rem 0 0;opacity:.9}'
        . '.rv-gh-subtle{margin:.25rem 0 0;font-size:.85rem;opacity:.7}'
        . '.rv-gh-stats{display:flex;flex-wrap:wrap;gap:1.25rem;margin-top:.9rem}'
        . '.rv-gh-stat{display:flex;flex-direction:column;font-size:.8rem;opacity:.75;line-height:1.25}'
        . '.rv-gh-stat strong{font-size:1.3rem;opacity:1;font-weight:700}'
        . '@media(max-width:640px){.rv-gh-profile{flex-direction:column}}'
        . '</style>';
}

/* ---------------------------------------------------------------------------
 * Appearance → GitHub : settings page for the social-proof display.
 * ------------------------------------------------------------------------- */

add_action('admin_menu', function () {
    add_theme_page(
        __('GitHub', 'sage'),
        __('GitHub', 'sage'),
        'edit_theme_options',
        'rv-github',
        __NAMESPACE__ . '\\render_github_settings_page'
    );
});

/**
 * Save handler + renderer for the GitHub settings page.
 */
function render_github_settings_page(): void
{
    if (! current_user_can('edit_theme_options')) {
        wp_die(esc_html__('You do not have permission to manage this page.', 'sage'));
    }

    $notice = null;

    if ('POST' === ($_SERVER['REQUEST_METHOD'] ?? '') && isset($_POST['rv_github_nonce'])) {
        check_admin_referer('rv_save_github', 'rv_github_nonce');

        $in  = isset($_POST['rv_github']) && is_array($_POST['rv_github']) ? wp_unslash($_POST['rv_github']) : [];
        $out = [
            'owner'     => sanitize_text_field($in['owner'] ?? ''),
            'type'      => in_array($in['type'] ?? 'owner', GITHUB_TYPES, true) ? ($in['type'] ?? 'owner') : 'owner',
            'sort'      => in_array($in['sort'] ?? 'pushed', GITHUB_SORTS, true) ? ($in['sort'] ?? 'pushed') : 'pushed',
            'direction' => in_array($in['direction'] ?? 'desc', GITHUB_DIRECTIONS, true) ? ($in['direction'] ?? 'desc') : 'desc',
            'min_stars' => max(0, (int) ($in['min_stars'] ?? 0)),
            'mode'      => in_array($in['mode'] ?? 'auto', ['auto', 'pick'], true) ? ($in['mode'] ?? 'auto') : 'auto',
            'count'     => min(100, max(1, (int) ($in['count'] ?? 6))),
            'selected'  => array_values(array_filter(array_map('sanitize_text_field', (array) ($in['selected'] ?? [])))),
            'featured'  => sanitize_text_field($in['featured'] ?? ''),
            'exclude'   => sanitize_text_field($in['exclude'] ?? ''),
            'headline'  => sanitize_text_field($in['headline'] ?? ''),
            'intro'     => sanitize_textarea_field($in['intro'] ?? ''),
        ];
        foreach (github_toggle_defaults() as $k => $d) {
            $out[$k] = ! empty($in[$k]) ? 1 : 0;
        }
        update_option('rv_github_settings', $out);
        github_flush_cache();
        $notice = ['notice-success', __('GitHub settings saved. The live data cache was refreshed.', 'sage')];
    }

    $s        = github_settings();
    $token    = github_token();
    $hasToken = $token !== '';
    $profile  = github_profile();
    $available = github_all_repos();
    $apisUrl  = admin_url('themes.php?page=rv-theme-apis');

    // Small render helpers.
    $checkbox = function (string $key, string $label) use ($s) {
        printf(
            '<label style="display:block;margin:.25rem 0"><input type="checkbox" name="rv_github[%1$s]" value="1" %2$s /> %3$s</label>',
            esc_attr($key),
            checked(! empty($s[$key]), true, false),
            esc_html($label)
        );
    };
    $select = function (string $key, array $choices) use ($s) {
        printf('<select name="rv_github[%s]">', esc_attr($key));
        foreach ($choices as $val => $label) {
            printf('<option value="%1$s" %2$s>%3$s</option>', esc_attr($val), selected($s[$key] ?? '', $val, false), esc_html($label));
        }
        echo '</select>';
    };

    echo '<div class="wrap">';
    echo '<h1>' . esc_html__('GitHub', 'sage') . '</h1>';
    echo '<p style="max-width:72ch">' . esc_html__('Show your GitHub profile and public repositories as social proof. Data is pulled live from GitHub and cached for six hours. It appears on your Work page and anywhere you place the [github_social_proof] shortcode.', 'sage') . '</p>';

    if ($notice) {
        printf('<div class="notice %1$s is-dismissible"><p>%2$s</p></div>', esc_attr($notice[0]), esc_html($notice[1]));
    }

    if ($s['owner'] === '') {
        echo '<div class="notice notice-info inline"><p>' . esc_html__('Enter a GitHub username below to get started.', 'sage') . '</p></div>';
    } elseif ($profile) {
        printf(
            '<div class="notice notice-success inline"><p><strong>%1$s</strong> %2$s · %3$s · %4$s</p></div>',
            sprintf(
                /* translators: 1: display name, 2: @login. */
                esc_html__('✓ Connected as %1$s (@%2$s).', 'sage'),
                esc_html($profile['name'] ?: $profile['login']),
                esc_html($profile['login'])
            ),
            sprintf(/* translators: %s: count. */ esc_html__('%s followers', 'sage'), esc_html(number_format_i18n($profile['followers']))),
            sprintf(/* translators: %s: count. */ esc_html__('%s public repos', 'sage'), esc_html(number_format_i18n($profile['public_repos']))),
            sprintf(/* translators: %s: count. */ esc_html__('★ %s stars', 'sage'), esc_html(number_format_i18n(github_total_stars())))
        );
    } else {
        echo '<div class="notice notice-warning inline"><p>' . sprintf(
            /* translators: 1: username, 2: URL of Theme APIs page. */
            wp_kses_post(__('Couldn’t reach GitHub for <strong>%1$s</strong> right now. Check the username, or you may be hitting the unauthenticated rate limit — adding a token on the <a href="%2$s">Theme APIs</a> page raises it.', 'sage')),
            esc_html($s['owner']),
            esc_url($apisUrl)
        ) . '</p></div>';
    }

    echo '<form method="post" action="">';
    wp_nonce_field('rv_save_github', 'rv_github_nonce');

    /* ---- Account & API parameters ---- */
    echo '<h2>' . esc_html__('Account & GitHub API', 'sage') . '</h2>';
    echo '<p class="description" style="max-width:72ch">' . esc_html__('These map directly to GitHub’s repositories endpoint parameters.', 'sage') . '</p>';
    echo '<table class="form-table" role="presentation"><tbody>';

    printf(
        '<tr><th scope="row"><label for="rv_gh_owner">%1$s</label></th><td><input name="rv_github[owner]" id="rv_gh_owner" type="text" class="regular-text" value="%2$s" placeholder="octocat" /><p class="description">%3$s</p></td></tr>',
        esc_html__('GitHub username or org', 'sage'),
        esc_attr($s['owner']),
        esc_html__('The account whose profile and public repositories are shown.', 'sage')
    );

    echo '<tr><th scope="row">' . esc_html__('Repository type', 'sage') . '</th><td>';
    $select('type', ['owner' => __('Owner', 'sage'), 'member' => __('Member', 'sage'), 'all' => __('All', 'sage')]);
    echo '<p class="description">' . esc_html__('GitHub “type” — which repositories the account is associated with.', 'sage') . '</p></td></tr>';

    echo '<tr><th scope="row">' . esc_html__('Sort by', 'sage') . '</th><td>';
    $select('sort', [
        'pushed'  => __('Last pushed', 'sage'),
        'updated' => __('Last updated', 'sage'),
        'created' => __('Created', 'sage'),
        'name'    => __('Name', 'sage'),
        'stars'   => __('Stars', 'sage'),
    ]);
    echo ' ';
    $select('direction', ['desc' => __('Descending', 'sage'), 'asc' => __('Ascending', 'sage')]);
    echo '</td></tr>';

    printf(
        '<tr><th scope="row"><label for="rv_gh_minstars">%1$s</label></th><td><input name="rv_github[min_stars]" id="rv_gh_minstars" type="number" min="0" value="%2$d" class="small-text" /><p class="description">%3$s</p></td></tr>',
        esc_html__('Minimum stars', 'sage'),
        (int) $s['min_stars'],
        esc_html__('Hide repositories below this star count (auto mode).', 'sage')
    );

    echo '<tr><th scope="row">' . esc_html__('Include', 'sage') . '</th><td>';
    $checkbox('include_forks', __('Forked repositories', 'sage'));
    $checkbox('include_archived', __('Archived repositories', 'sage'));
    echo '</td></tr>';

    echo '</tbody></table>';

    /* ---- Which repositories ---- */
    echo '<h2>' . esc_html__('Which repositories', 'sage') . '</h2>';
    echo '<table class="form-table" role="presentation"><tbody>';

    echo '<tr><th scope="row">' . esc_html__('Selection', 'sage') . '</th><td>';
    printf(
        '<label style="display:block;margin:.25rem 0"><input type="radio" name="rv_github[mode]" value="auto" %s /> %s</label>',
        checked($s['mode'], 'auto', false),
        esc_html__('Automatic — top repositories by the sort above', 'sage')
    );
    printf(
        '<label style="display:block;margin:.25rem 0"><input type="radio" name="rv_github[mode]" value="pick" %s /> %s</label>',
        checked($s['mode'], 'pick', false),
        esc_html__('Pick specific repositories (checklist below)', 'sage')
    );
    echo '</td></tr>';

    printf(
        '<tr><th scope="row"><label for="rv_gh_count">%1$s</label></th><td><input name="rv_github[count]" id="rv_gh_count" type="number" min="1" max="100" value="%2$d" class="small-text" /> <span class="description">%3$s</span></td></tr>',
        esc_html__('How many to show', 'sage'),
        (int) $s['count'],
        esc_html__('Automatic mode only.', 'sage')
    );

    // Repo picker checklist.
    echo '<tr><th scope="row">' . esc_html__('Pick repositories', 'sage') . '</th><td>';
    if (! empty($available)) {
        $selected = array_flip($s['selected']);
        echo '<div style="max-height:280px;overflow:auto;border:1px solid #dcdcde;border-radius:6px;padding:.5rem .85rem;max-width:640px">';
        foreach ($available as $r) {
            $badges = [];
            if ($r['stars'] > 0) {
                $badges[] = '★' . (int) $r['stars'];
            }
            if ($r['fork']) {
                $badges[] = __('fork', 'sage');
            }
            if ($r['archived']) {
                $badges[] = __('archived', 'sage');
            }
            printf(
                '<label style="display:block;margin:.2rem 0"><input type="checkbox" name="rv_github[selected][]" value="%1$s" %2$s /> <strong>%3$s</strong>%4$s</label>',
                esc_attr($r['name']),
                checked(isset($selected[$r['name']]), true, false),
                esc_html($r['name']),
                $badges ? ' <span class="description">(' . esc_html(implode(' · ', $badges)) . ')</span>' : ''
            );
        }
        echo '</div><p class="description">' . esc_html__('Used when “Pick specific repositories” is selected above.', 'sage') . '</p>';
    } else {
        echo '<p class="description">' . esc_html__('Repository list will appear here once GitHub can be reached.', 'sage') . '</p>';
    }
    echo '</td></tr>';

    printf(
        '<tr><th scope="row"><label for="rv_gh_featured">%1$s</label></th><td><input name="rv_github[featured]" id="rv_gh_featured" type="text" class="regular-text" value="%2$s" /><p class="description">%3$s</p></td></tr>',
        esc_html__('Featured repos (pinned first)', 'sage'),
        esc_attr(implode(', ', $s['featured'])),
        esc_html__('Automatic mode: comma-separated names to show first, in order.', 'sage')
    );

    printf(
        '<tr><th scope="row"><label for="rv_gh_exclude">%1$s</label></th><td><input name="rv_github[exclude]" id="rv_gh_exclude" type="text" class="regular-text" value="%2$s" /><p class="description">%3$s</p></td></tr>',
        esc_html__('Hidden repos', 'sage'),
        esc_attr(implode(', ', $s['exclude'])),
        esc_html__('Automatic mode: comma-separated names to hide.', 'sage')
    );

    echo '</tbody></table>';

    /* ---- What to show ---- */
    echo '<h2>' . esc_html__('What to show', 'sage') . '</h2>';
    echo '<table class="form-table" role="presentation"><tbody>';

    echo '<tr><th scope="row">' . esc_html__('Profile card', 'sage') . '</th><td>';
    $checkbox('show_profile', __('Show the profile card', 'sage'));
    $checkbox('show_avatar', __('Avatar', 'sage'));
    $checkbox('show_bio', __('Bio', 'sage'));
    $checkbox('show_meta', __('Company & location', 'sage'));
    echo '</td></tr>';

    echo '<tr><th scope="row">' . esc_html__('Profile stats', 'sage') . '</th><td>';
    $checkbox('stat_followers', __('Followers', 'sage'));
    $checkbox('stat_following', __('Following', 'sage'));
    $checkbox('stat_repos', __('Public repos', 'sage'));
    $checkbox('stat_stars', __('Stars earned', 'sage'));
    $checkbox('stat_gists', __('Public gists', 'sage'));
    echo '</td></tr>';

    echo '<tr><th scope="row">' . esc_html__('Repository cards', 'sage') . '</th><td>';
    $checkbox('show_desc', __('Description', 'sage'));
    $checkbox('show_language', __('Language', 'sage'));
    $checkbox('show_stars', __('Star count', 'sage'));
    $checkbox('show_forks', __('Fork count', 'sage'));
    $checkbox('show_topics', __('Topics', 'sage'));
    $checkbox('show_updated', __('Last updated', 'sage'));
    echo '</td></tr>';

    echo '</tbody></table>';

    /* ---- Section text ---- */
    echo '<h2>' . esc_html__('Section text (shortcode)', 'sage') . '</h2>';
    echo '<table class="form-table" role="presentation"><tbody>';
    printf(
        '<tr><th scope="row"><label for="rv_gh_headline">%1$s</label></th><td><input name="rv_github[headline]" id="rv_gh_headline" type="text" class="regular-text" value="%2$s" placeholder="%3$s" /></td></tr>',
        esc_html__('Section headline', 'sage'),
        esc_attr($s['headline']),
        esc_attr__('Code, in the open.', 'sage')
    );
    printf(
        '<tr><th scope="row"><label for="rv_gh_intro">%1$s</label></th><td><textarea name="rv_github[intro]" id="rv_gh_intro" rows="2" class="large-text">%2$s</textarea></td></tr>',
        esc_html__('Section intro', 'sage'),
        esc_textarea($s['intro'])
    );
    echo '</tbody></table>';

    submit_button(__('Save GitHub settings', 'sage'));
    echo '</form>';

    /* ---- Token + placement help ---- */
    echo '<hr />';
    echo '<h2>' . esc_html__('Rate limit & token', 'sage') . '</h2>';
    if ($hasToken) {
        echo '<p>' . esc_html__('✓ An access token is configured, so requests are authenticated (higher rate limit).', 'sage') . '</p>';
    } else {
        echo '<p>' . sprintf(
            /* translators: %s: URL of Theme APIs page. */
            wp_kses_post(__('No token is set. GitHub allows ~60 unauthenticated requests per hour per server — usually fine with 6-hour caching. To raise the limit, add a token under <strong>GitHub</strong> on the <a href="%s">Theme APIs</a> page.', 'sage')),
            esc_url($apisUrl)
        ) . '</p>';
    }

    echo '<h2>' . esc_html__('Where it shows', 'sage') . '</h2>';
    echo '<p>' . wp_kses_post(__('The GitHub section already appears on your <strong>Work</strong> page. To place it elsewhere, add this shortcode to any page or post:', 'sage')) . '</p>';
    echo '<p><code>[github_social_proof]</code> &nbsp; ' . esc_html__('optional attributes:', 'sage') . ' <code>count="6"</code> <code>show_profile="true"</code> <code>headline="…"</code></p>';

    echo '</div>';
}

/* ---------------------------------------------------------------------------
 * Single repository detail pages — /code/{repo}/
 *
 * Each repo card links to an internal detail page that shows full metadata plus
 * the repository's rendered README, with a button through to GitHub itself.
 * ------------------------------------------------------------------------- */

/** Internal permalink for a repository's detail page. */
function github_repo_permalink(string $name): string
{
    return home_url('/code/' . rawurlencode($name) . '/');
}

/** Full detail for a single repository (cached). */
function github_repo(string $name): ?array
{
    $owner = github_owner();
    if ($owner === '' || $name === '') {
        return null;
    }

    $key    = 'rv_gh_repo_' . md5($owner . '|' . $name . '|' . (github_token() !== '' ? 'auth' : 'anon') . '|' . github_cache_ver());
    $cached = get_transient($key);
    if (is_array($cached)) {
        return $cached ?: null;
    }

    $data = github_get('https://api.github.com/repos/' . rawurlencode($owner) . '/' . rawurlencode($name));
    if (! $data || empty($data['name'])) {
        set_transient($key, [], 15 * MINUTE_IN_SECONDS);
        return null;
    }

    $license = '';
    if (! empty($data['license']) && is_array($data['license'])) {
        $license = (string) ($data['license']['spdx_id'] ?? $data['license']['name'] ?? '');
        if ($license === 'NOASSERTION') {
            $license = (string) ($data['license']['name'] ?? '');
        }
    }

    $repo = [
        'name'        => (string) $data['name'],
        'full_name'   => (string) ($data['full_name'] ?? ''),
        'title'       => ucwords(trim(str_replace(['-', '_'], ' ', (string) $data['name']))),
        'desc'        => (string) ($data['description'] ?? ''),
        'url'         => (string) ($data['html_url'] ?? ''),
        'homepage'    => (string) ($data['homepage'] ?? ''),
        'language'    => (string) ($data['language'] ?? ''),
        'stars'       => (int) ($data['stargazers_count'] ?? 0),
        'forks'       => (int) ($data['forks_count'] ?? 0),
        'watchers'    => (int) ($data['subscribers_count'] ?? ($data['watchers_count'] ?? 0)),
        'open_issues' => (int) ($data['open_issues_count'] ?? 0),
        'topics'      => array_values(array_filter((array) ($data['topics'] ?? []))),
        'license'     => $license,
        'created'     => (string) ($data['created_at'] ?? ''),
        'updated'     => (string) ($data['pushed_at'] ?? ($data['updated_at'] ?? '')),
        'branch'      => (string) ($data['default_branch'] ?? 'main'),
        'archived'    => ! empty($data['archived']),
        'fork'        => ! empty($data['fork']),
    ];

    set_transient($key, $repo, 6 * HOUR_IN_SECONDS);
    return $repo;
}

/** Rendered, sanitized README HTML for a repository (empty string if none). */
function github_readme_html(string $name): string
{
    $owner = github_owner();
    if ($owner === '' || $name === '') {
        return '';
    }

    $key    = 'rv_gh_readme_' . md5($owner . '|' . $name . '|' . (github_token() !== '' ? 'auth' : 'anon') . '|' . github_cache_ver());
    $cached = get_transient($key);
    if (is_string($cached)) {
        return $cached;
    }

    // The html+json media type asks GitHub to render the Markdown to HTML for us.
    $res = wp_remote_get(
        'https://api.github.com/repos/' . rawurlencode($owner) . '/' . rawurlencode($name) . '/readme',
        [
            'timeout' => 10,
            'headers' => array_merge(github_headers(), ['Accept' => 'application/vnd.github.html+json']),
        ]
    );

    if (is_wp_error($res) || 200 !== (int) wp_remote_retrieve_response_code($res)) {
        set_transient($key, '', 15 * MINUTE_IN_SECONDS);
        return '';
    }

    $repo   = github_repo($name);
    $branch = is_array($repo) && ! empty($repo['branch']) ? $repo['branch'] : 'main';
    $html   = github_absolutize_readme((string) wp_remote_retrieve_body($res), $owner, $name, $branch);
    $html   = wp_kses_post($html);

    set_transient($key, $html, 6 * HOUR_IN_SECONDS);
    return $html;
}

/**
 * Rewrite repo-relative image sources and links in README HTML to absolute
 * GitHub URLs so they resolve when displayed on our own site.
 */
function github_absolutize_readme(string $html, string $owner, string $repo, string $branch): string
{
    $raw  = 'https://raw.githubusercontent.com/' . $owner . '/' . $repo . '/' . $branch . '/';
    $blob = 'https://github.com/' . $owner . '/' . $repo . '/blob/' . $branch . '/';

    $isAbsolute = fn (string $url) => $url === ''
        || $url[0] === '#'
        || (bool) preg_match('#^(https?:)?//#i', $url)
        || (bool) preg_match('#^(data:|mailto:|tel:)#i', $url);

    // Image sources → raw.githubusercontent.com
    $html = preg_replace_callback('/(<img\b[^>]*\bsrc=)("|\')(.*?)\2/i', function ($m) use ($raw, $isAbsolute) {
        if ($isAbsolute($m[3])) {
            return $m[0];
        }
        return $m[1] . $m[2] . $raw . preg_replace('#^(?:\./|/)+#', '', $m[3]) . $m[2];
    }, $html) ?? $html;

    // Anchor links → github.com blob view
    $html = preg_replace_callback('/(<a\b[^>]*\bhref=)("|\')(.*?)\2/i', function ($m) use ($blob, $isAbsolute) {
        if ($isAbsolute($m[3])) {
            return $m[0];
        }
        return $m[1] . $m[2] . $blob . preg_replace('#^(?:\./|/)+#', '', $m[3]) . $m[2];
    }, $html) ?? $html;

    return $html;
}

/** Register the /code/{repo}/ rewrite (and flush once when it changes). */
add_action('init', function () {
    add_rewrite_tag('%rv_repo%', '([^/]+)');
    add_rewrite_rule('^code/([^/]+)/?$', 'index.php?rv_repo=$matches[1]', 'top');

    if (get_option('rv_gh_rewrite_v') !== '1') {
        flush_rewrite_rules(false);
        update_option('rv_gh_rewrite_v', '1');
    }
});

/** A repo detail URL is a real 200 page, not a 404. */
add_action('template_redirect', function () {
    if ((string) get_query_var('rv_repo') === '') {
        return;
    }
    global $wp_query;
    $wp_query->is_404 = false;
    status_header(200);
});

/** Don't let canonical redirection bounce our virtual URL. */
add_filter('redirect_canonical', function ($redirect) {
    return (string) get_query_var('rv_repo') !== '' ? false : $redirect;
});

/** Render the repo detail via the Sage view pipeline. */
add_filter('template_include', function ($template) {
    $name = (string) get_query_var('rv_repo');
    if ($name === '') {
        return $template;
    }

    $repo = github_repo($name);

    app()->instance('sage.view', 'template-github-repo');
    app()->instance('sage.data', [
        'repoName' => $name,
        'repo'     => $repo,
        'readme'   => $repo ? github_readme_html($name) : '',
    ]);

    return get_theme_file_path('index.php');
}, 999);
