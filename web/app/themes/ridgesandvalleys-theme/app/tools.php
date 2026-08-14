<?php

/**
 * Free tools suite — Ridges & Valleys Studio.
 *
 * Five interactive tools, each a dynamic block (server-rendered shell + TypeScript
 * behaviour in resources/js/tools.ts). The two URL-based tools (site grader,
 * accessibility checker) call REST endpoints under rv-tools/v1 that fetch and
 * analyse a page server-side (SSRF-guarded). The other three (contrast checker,
 * quote estimator, cost calculator) are pure client-side.
 *
 * The same render_* helpers back both the blocks and the "Tools" page template.
 */

namespace App;

/* =========================================================== Fetch engine === */

/** Max bytes to keep from a fetched page (filterable). */
function rv_tools_max_bytes(): int
{
    return (int) apply_filters('rv/tools_max_bytes', 524288);
}

/** Generic fetch failure — never leak WP_HTTP / cURL internals. */
function rv_tools_fetch_error(): string
{
    return __('Could not fetch that page. Check the URL is public and try again.', 'sage');
}

function rv_tools_client_ip(): string
{
    $ip = isset($_SERVER['REMOTE_ADDR']) ? sanitize_text_field(wp_unslash($_SERVER['REMOTE_ADDR'])) : '0';

    return $ip !== '' ? $ip : '0';
}

/**
 * Per-IP rate limit for public tool endpoints. Returns a 429 response when
 * exceeded, otherwise null. Shared across all rv-tools/v1 routes.
 */
function rv_tools_rate_limited(?string $bucket = null): ?\WP_REST_Response
{
    $bucket = $bucket ?: 'public';
    $max    = (int) apply_filters('rv/tools_rate_max', 12);
    $window = (int) apply_filters('rv/tools_rate_window', 120);
    $key    = 'rv_rl_' . sanitize_key($bucket) . '_' . md5(rv_tools_client_ip());
    $count  = (int) get_transient($key);
    if ($count >= max(1, $max)) {
        return new \WP_REST_Response([
            'ok'    => false,
            'error' => __('Too many checks. Wait a minute and try again.', 'sage'),
        ], 429);
    }
    set_transient($key, $count + 1, max(30, $window));

    return null;
}

function rv_tools_http_args(int $timeout, string $ua): array
{
    return [
        'timeout'             => $timeout,
        'redirection'         => 3,
        'user-agent'          => $ua,
        'headers'             => ['Accept' => 'text/html,application/xhtml+xml'],
        'limit_response_size' => rv_tools_max_bytes(),
    ];
}

function rv_tools_truncate_body(string $body): string
{
    $max = rv_tools_max_bytes();
    if ($max > 0 && strlen($body) > $max) {
        return substr($body, 0, $max);
    }

    return $body;
}

/**
 * SSRF-guarded server-side page fetch.
 */
function rv_tools_fetch(string $url): array
{
    $url = esc_url_raw(trim($url));
    if (! $url || ! preg_match('#^https?://#i', $url) || ! wp_http_validate_url($url)) {
        return ['ok' => false, 'error' => __('Enter a valid public http(s) URL.', 'sage')];
    }

    $start = microtime(true);
    $res   = wp_safe_remote_get($url, rv_tools_http_args(12, 'RidgesValleysTools/1.0 (+https://ridgesandvalleys.com)'));

    if (is_wp_error($res)) {
        return ['ok' => false, 'error' => rv_tools_fetch_error()];
    }

    $code = (int) wp_remote_retrieve_response_code($res);
    $body = rv_tools_truncate_body((string) wp_remote_retrieve_body($res));

    return [
        'ok'     => true,
        'status' => $code,
        'html'   => $body,
        'ms'     => (int) round((microtime(true) - $start) * 1000),
        'bytes'  => strlen($body),
        'url'    => $url,
    ];
}

/**
 * Parse HTML into a DOMDocument (quietly).
 */
function rv_tools_dom(string $html): \DOMDocument
{
    $dom = new \DOMDocument();
    $prev = libxml_use_internal_errors(true);
    $dom->loadHTML('<?xml encoding="UTF-8">' . $html);
    libxml_clear_errors();
    libxml_use_internal_errors($prev);
    return $dom;
}

/* =========================================================== REST routes ==== */

add_action('rest_api_init', function () {
    $common = [
        'methods'             => 'GET',
        'permission_callback' => '__return_true',
        'args'                => ['url' => ['required' => true, 'type' => 'string']],
    ];
    register_rest_route('rv-tools/v1', '/grade', $common + ['callback' => __NAMESPACE__ . '\\rv_rest_grade']);
    register_rest_route('rv-tools/v1', '/a11y', $common + ['callback' => __NAMESPACE__ . '\\rv_rest_a11y']);
});

/**
 * Site grader — score a page on quick SEO / performance / correctness signals.
 */
function rv_rest_grade(\WP_REST_Request $req)
{
    if ($limited = rv_tools_rate_limited()) {
        return $limited;
    }

    $fetch = rv_tools_fetch((string) $req->get_param('url'));
    if (! $fetch['ok']) {
        return new \WP_REST_Response(['ok' => false, 'error' => $fetch['error']], 200);
    }

    $html = $fetch['html'];
    $dom  = rv_tools_dom($html);
    $xp   = new \DOMXPath($dom);

    $title = trim((string) ($dom->getElementsByTagName('title')->item(0)->textContent ?? ''));
    $metaDesc = '';
    foreach ($xp->query('//meta[@name="description"]/@content') as $n) {
        $metaDesc = trim($n->nodeValue);
    }
    $h1     = $dom->getElementsByTagName('h1')->length;
    $h2     = $dom->getElementsByTagName('h2')->length;
    $imgs   = $dom->getElementsByTagName('img');
    $imgTotal = $imgs->length;
    $imgAlt = 0;
    foreach ($imgs as $img) {
        if ($img->hasAttribute('alt') && trim($img->getAttribute('alt')) !== '') {
            $imgAlt++;
        }
    }
    $hasViewport = $xp->query('//meta[@name="viewport"]')->length > 0;
    $hasOg       = $xp->query('//meta[starts-with(@property,"og:")]')->length > 0;
    $https       = str_starts_with($fetch['url'], 'https://');
    $lang        = $dom->documentElement && $dom->documentElement->hasAttribute('lang');
    $kb          = round($fetch['bytes'] / 1024);

    $checks = [];
    $add = function ($label, $pass, $weight, $hint = '') use (&$checks) {
        $checks[] = ['label' => $label, 'pass' => (bool) $pass, 'weight' => $weight, 'hint' => $hint];
    };

    $add(__('Serves over HTTPS', 'sage'), $https, 12);
    $add(__('Has a page title', 'sage'), $title !== '', 10, __('Add a descriptive <title>.', 'sage'));
    $add(__('Title length 20–60 chars', 'sage'), mb_strlen($title) >= 20 && mb_strlen($title) <= 60, 6, __('Aim for ~50–60 characters.', 'sage'));
    $add(__('Has a meta description', 'sage'), $metaDesc !== '', 10, __('Summarise the page in ~150 chars.', 'sage'));
    $add(__('Meta description 120–160 chars', 'sage'), mb_strlen($metaDesc) >= 120 && mb_strlen($metaDesc) <= 160, 6);
    $add(__('Exactly one H1', 'sage'), $h1 === 1, 10, __('Use a single, clear H1.', 'sage'));
    $add(__('Uses H2 subheadings', 'sage'), $h2 >= 1, 6);
    $add(__('Mobile viewport set', 'sage'), $hasViewport, 10, __('Add a responsive viewport meta tag.', 'sage'));
    $add(__('Declares a language', 'sage'), $lang, 6, __('Add lang="en" to <html>.', 'sage'));
    $add(__('Images have alt text', 'sage'), $imgTotal === 0 || $imgAlt / max(1, $imgTotal) >= 0.9, 10, __('Describe images for SEO + screen readers.', 'sage'));
    $add(__('Open Graph tags for sharing', 'sage'), $hasOg, 6);
    $add(__('Page under 1 MB', 'sage'), $kb <= 1024, 8, __('Compress images and trim scripts.', 'sage'));

    $earned = 0; $possible = 0;
    foreach ($checks as $c) { $possible += $c['weight']; if ($c['pass']) $earned += $c['weight']; }
    $score = (int) round($earned / max(1, $possible) * 100);

    return new \WP_REST_Response([
        'ok'     => true,
        'score'  => $score,
        'grade'  => rv_tools_letter($score),
        'meta'   => ['title' => $title, 'description' => $metaDesc, 'ms' => $fetch['ms'], 'kb' => $kb, 'images' => $imgTotal, 'status' => $fetch['status']],
        'checks' => $checks,
    ], 200);
}

/**
 * Accessibility checker — flag common WCAG issues in a page's markup.
 */
function rv_rest_a11y(\WP_REST_Request $req)
{
    if ($limited = rv_tools_rate_limited()) {
        return $limited;
    }

    $fetch = rv_tools_fetch((string) $req->get_param('url'));
    if (! $fetch['ok']) {
        return new \WP_REST_Response(['ok' => false, 'error' => $fetch['error']], 200);
    }

    $dom = rv_tools_dom($fetch['html']);
    $xp  = new \DOMXPath($dom);
    $findings = [];
    $add = function ($label, $status, $detail = '') use (&$findings) {
        // status: pass | warn | fail
        $findings[] = ['label' => $label, 'status' => $status, 'detail' => $detail];
    };

    // Language
    $add(__('Page language declared', 'sage'),
        ($dom->documentElement && $dom->documentElement->hasAttribute('lang')) ? 'pass' : 'fail',
        __('Screen readers need lang="…" on <html>.', 'sage'));

    // Title
    $title = trim((string) ($dom->getElementsByTagName('title')->item(0)->textContent ?? ''));
    $add(__('Document has a title', 'sage'), $title !== '' ? 'pass' : 'fail');

    // Images alt
    $imgs = $dom->getElementsByTagName('img'); $missing = 0;
    foreach ($imgs as $img) { if (! $img->hasAttribute('alt')) $missing++; }
    $add(__('Images have alt attributes', 'sage'),
        $imgs->length === 0 ? 'pass' : ($missing === 0 ? 'pass' : ($missing <= 2 ? 'warn' : 'fail')),
        $missing ? sprintf(_n('%d image missing alt.', '%d images missing alt.', $missing, 'sage'), $missing) : '');

    // Inputs labelled
    $inputs = $xp->query('//input[not(@type="hidden")] | //select | //textarea');
    $unlabelled = 0;
    foreach ($inputs as $el) {
        $id = $el->getAttribute('id');
        $has = ($el->hasAttribute('aria-label') || $el->hasAttribute('aria-labelledby')
            || ($id && $xp->query('//label[@for="' . $id . '"]')->length));
        if (! $has) $unlabelled++;
    }
    $add(__('Form fields have labels', 'sage'),
        $inputs->length === 0 ? 'pass' : ($unlabelled === 0 ? 'pass' : 'fail'),
        $unlabelled ? sprintf(_n('%d field without a label.', '%d fields without a label.', $unlabelled, 'sage'), $unlabelled) : '');

    // Single H1
    $h1 = $dom->getElementsByTagName('h1')->length;
    $add(__('Exactly one H1 heading', 'sage'), $h1 === 1 ? 'pass' : ($h1 === 0 ? 'fail' : 'warn'),
        $h1 !== 1 ? sprintf(__('Found %d.', 'sage'), $h1) : '');

    // Heading order (no skipped levels)
    $levels = [];
    foreach ($xp->query('//*[self::h1 or self::h2 or self::h3 or self::h4 or self::h5 or self::h6]') as $h) {
        $levels[] = (int) substr($h->nodeName, 1);
    }
    $skip = false;
    for ($i = 1; $i < count($levels); $i++) { if ($levels[$i] - $levels[$i - 1] > 1) { $skip = true; break; } }
    $add(__('Headings don\'t skip levels', 'sage'), $skip ? 'warn' : 'pass',
        $skip ? __('A heading jumps more than one level.', 'sage') : '');

    // Link text
    $vague = 0;
    foreach ($dom->getElementsByTagName('a') as $a) {
        $t = strtolower(trim($a->textContent));
        if (in_array($t, ['click here', 'here', 'read more', 'more', 'link'], true)) $vague++;
    }
    $add(__('Links have descriptive text', 'sage'), $vague === 0 ? 'pass' : 'warn',
        $vague ? sprintf(_n('%d vague link ("click here").', '%d vague links ("click here").', $vague, 'sage'), $vague) : '');

    // Landmarks
    $hasMain = $xp->query('//main | //*[@role="main"]')->length > 0;
    $add(__('Has a main landmark', 'sage'), $hasMain ? 'pass' : 'warn',
        $hasMain ? '' : __('Wrap primary content in <main>.', 'sage'));

    $fails = count(array_filter($findings, fn ($f) => $f['status'] === 'fail'));
    $warns = count(array_filter($findings, fn ($f) => $f['status'] === 'warn'));

    return new \WP_REST_Response([
        'ok'       => true,
        'summary'  => ['fail' => $fails, 'warn' => $warns, 'pass' => count($findings) - $fails - $warns, 'total' => count($findings)],
        'findings' => $findings,
        'meta'     => ['ms' => $fetch['ms'], 'status' => $fetch['status']],
    ], 200);
}

function rv_tools_letter(int $score): string
{
    return $score >= 90 ? 'A' : ($score >= 80 ? 'B' : ($score >= 70 ? 'C' : ($score >= 60 ? 'D' : 'F')));
}

/* =========================================================== Block markup === */

function rv_tool_open(string $slug, string $title, string $intro): string
{
    return '<div class="rv-tool" data-rv-tool="' . esc_attr($slug) . '">'
        . '<div class="rv-tool-head"><h3 class="rv-tool-title">' . esc_html($title) . '</h3>'
        . '<p class="rv-tool-intro">' . esc_html($intro) . '</p></div>';
}

function rv_render_grader(): string
{
    $ep = esc_url(rest_url('rv-tools/v1/grade'));
    ob_start(); ?>
    <?php echo rv_tool_open('grader', __('Website grader', 'sage'), __('Score any page on the quick wins that matter for getting found.', 'sage')); // phpcs:ignore ?>
        <form class="rv-tool-form" data-endpoint="<?php echo $ep; ?>">
            <label class="screen-reader-text" for="rv-grader-url"><?php esc_html_e('Website URL', 'sage'); ?></label>
            <input type="url" id="rv-grader-url" name="url" placeholder="https://your-site.com" required />
            <button type="submit" class="rv-btn rv-btn-primary"><?php esc_html_e('Grade it', 'sage'); ?></button>
        </form>
        <div class="rv-tool-result" hidden></div>
    </div>
    <?php return (string) ob_get_clean();
}

function rv_render_accessibility(): string
{
    $ep = esc_url(rest_url('rv-tools/v1/a11y'));
    ob_start(); ?>
    <?php echo rv_tool_open('a11y', __('Accessibility checker', 'sage'), __('Catch the common accessibility problems on a page before your visitors do.', 'sage')); // phpcs:ignore ?>
        <form class="rv-tool-form" data-endpoint="<?php echo $ep; ?>">
            <label class="screen-reader-text" for="rv-a11y-url"><?php esc_html_e('Website URL', 'sage'); ?></label>
            <input type="url" id="rv-a11y-url" name="url" placeholder="https://your-site.com" required />
            <button type="submit" class="rv-btn rv-btn-primary"><?php esc_html_e('Check it', 'sage'); ?></button>
        </form>
        <div class="rv-tool-result" hidden></div>
    </div>
    <?php return (string) ob_get_clean();
}

function rv_render_contrast(): string
{
    ob_start(); ?>
    <?php echo rv_tool_open('contrast', __('Color contrast checker', 'sage'), __('Check any text/background pair against WCAG AA and AAA.', 'sage')); // phpcs:ignore ?>
        <div class="rv-tool-form rv-contrast-form">
            <span class="rv-field-inline"><label for="rv-c-fg"><?php esc_html_e('Text', 'sage'); ?></label>
                <input type="color" id="rv-c-fg" value="#23201B" /><input type="text" id="rv-c-fg-hex" value="#23201B" spellcheck="false" /></span>
            <span class="rv-field-inline"><label for="rv-c-bg"><?php esc_html_e('Background', 'sage'); ?></label>
                <input type="color" id="rv-c-bg" value="#F7F1E6" /><input type="text" id="rv-c-bg-hex" value="#F7F1E6" spellcheck="false" /></span>
        </div>
        <div class="rv-tool-result" data-live="1"></div>
    </div>
    <?php return (string) ob_get_clean();
}

function rv_render_estimator(): string
{
    ob_start(); ?>
    <?php echo rv_tool_open('estimator', __('Project quote estimator', 'sage'), __('A ballpark range for a new site — not a quote, just a starting point.', 'sage')); // phpcs:ignore ?>
        <div class="rv-tool-form rv-stack">
            <label>Project type
                <select id="rv-e-type">
                    <option value="1">New website</option>
                    <option value="1.15">Redesign / rescue</option>
                    <option value="1.4">Site + local SEO</option>
                </select>
            </label>
            <label>Pages
                <select id="rv-e-pages">
                    <option value="900">1–5</option>
                    <option value="1600">6–10</option>
                    <option value="2600">11–20</option>
                </select>
            </label>
            <fieldset class="rv-checks"><legend>Add-ons</legend>
                <label><input type="checkbox" value="400" class="rv-e-add"> Booking / scheduling</label>
                <label><input type="checkbox" value="350" class="rv-e-add"> Blog / content setup</label>
                <label><input type="checkbox" value="300" class="rv-e-add"> Extra town landing pages</label>
                <label><input type="checkbox" value="500" class="rv-e-add"> Copywriting</label>
            </fieldset>
        </div>
        <div class="rv-tool-result" data-live="1"></div>
    </div>
    <?php return (string) ob_get_clean();
}

function rv_render_calculator(): string
{
    ob_start(); ?>
    <?php echo rv_tool_open('calculator', __('Care plan cost calculator', 'sage'), __('Estimate what ongoing care, hosting, and updates would run per month.', 'sage')); // phpcs:ignore ?>
        <div class="rv-tool-form rv-stack">
            <label>Care level
                <select id="rv-k-tier">
                    <option value="49">Essential — hosting, backups, updates</option>
                    <option value="99">Growth — the above + monthly edits</option>
                    <option value="179">Partner — the above + SEO + a content piece</option>
                </select>
            </label>
            <label><input type="checkbox" id="rv-k-annual"> Pay annually (save ~15%)</label>
        </div>
        <div class="rv-tool-result" data-live="1"></div>
    </div>
    <?php return (string) ob_get_clean();
}

/* =========================================================== Registration === */

add_action('init', function () {
    $blocks = [
        'accessibility-checker' => ['Accessibility Checker', __NAMESPACE__ . '\\rv_render_accessibility'],
        'site-grader'           => ['Website Grader', __NAMESPACE__ . '\\rv_render_grader'],
        'contrast-checker'      => ['Contrast Checker', __NAMESPACE__ . '\\rv_render_contrast'],
        'quote-estimator'       => ['Quote Estimator', __NAMESPACE__ . '\\rv_render_estimator'],
        'cost-calculator'       => ['Cost Calculator', __NAMESPACE__ . '\\rv_render_calculator'],
    ];
    foreach ($blocks as $slug => [$title, $cb]) {
        register_block_type('rv/' . $slug, [
            'api_version'     => 3,
            'title'           => 'RV ' . $title,
            'category'        => 'widgets',
            'icon'            => 'admin-tools',
            'supports'        => ['html' => false, 'align' => ['wide']],
            'render_callback' => $cb,
        ]);
    }
});
