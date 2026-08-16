<?php

/**
 * Editable page content — custom fields for the theme's page templates.
 *
 * Adds a "Page content" meta box to the page editor that shows the right inputs
 * for whichever template the page uses (the homepage is auto-detected and gets
 * the Front Page fields). Each input's placeholder shows the exact copy that is
 * currently on the live page, so the edit screen mirrors the page — type to
 * replace it, or leave a field blank to keep the built-in default.
 *
 * Values are stored as post meta and read in the Blade templates with
 * \App\field('key', $default); the default in each template is the fallback.
 *
 * To make a new piece of content editable: add a row to page_field_map() under
 * the right template (with its current on-page text as the 4th element), then
 * swap the string in the template for
 * {{ \App\field('your_key', __('Existing default', 'sage')) }}.
 */

namespace App;

/**
 * Read an editable field for the current (or given) page, falling back to the
 * template's built-in default when empty.
 */
function field(string $key, string $default = '', ?int $post_id = null): string
{
    $post_id = $post_id ?: get_the_ID();
    if (! $post_id) {
        return $default;
    }
    $val   = get_post_meta($post_id, 'rv_f_' . $key, true);
    $value = ($val === '' || $val === null) ? $default : (string) $val;

    // Lets the live editor preview draft values without saving (see live-preview.php).
    $value = (string) apply_filters('rv/field_value', $value, $key, (int) $post_id, $default);

    // In the live-editor preview, wrap text fields in invisible markers so a
    // click in the preview can jump to the matching input. Skip fields used in
    // attributes (links, images, email/phone) — a marker there would break them.
    if (field_is_markable($key)
        && function_exists(__NAMESPACE__ . '\\preview_marking')
        && preview_marking()) {
        return "\x01" . $key . "\x02" . $value . "\x03";
    }

    return $value;
}

/** Text fields can be click-tagged in the preview; attribute-bound fields can't. */
function field_is_markable(string $key): bool
{
    if (str_ends_with($key, '_url') || str_ends_with($key, '_bg') || str_ends_with($key, '_image')) {
        return false;
    }
    return ! in_array($key, ['contact_email', 'contact_phone'], true);
}

/** Strip the invisible click-to-field markers field() may add (used before splitting). */
function strip_field_markers(string $s): string
{
    return (string) preg_replace('/\x01[a-z0-9_.]+\x02/', '', str_replace("\x03", '', $s));
}

/**
 * Optional hero side columns (columns 2 and 3). Returns only the columns that
 * have a heading and/or body set for the current (or given) page, so the hero
 * renders 1, 2, or 3 columns automatically. Consumed by
 * partials/hero-asides.blade.php + the .rv-hero-cols CSS in rv-enhancements.css.
 */
function hero_asides(?int $post_id = null): array
{
    $out = [];
    foreach (['2', '3'] as $n) {
        $title = field("hero_col{$n}_title", '', $post_id);
        $body  = field("hero_col{$n}_body", '', $post_id);
        if (trim(strip_field_markers($title)) !== '' || trim(strip_field_markers($body)) !== '') {
            $out[] = ['title' => $title, 'body' => $body];
        }
    }
    return $out;
}

/**
 * Scan-line bullets under “Who we are” on the homepage intro.
 *
 * @return list<string>
 */
function home_intro_point_defaults(): array
{
    return [
        __('Family-owned in Gettysburg', 'sage'),
        __('WCAG 2.2 AA, baked in', 'sage'),
        __('You own the domain, hosting, and site', 'sage'),
    ];
}

/**
 * Three process steps for the homepage intro “How we work” card.
 *
 * @return list<array{title:string,text:string}>
 */
function home_intro_step_defaults(): array
{
    return [
        ['title' => __('Short call + intake', 'sage'), 'text' => __('One conversation and one form. That’s the brief.', 'sage')],
        ['title' => __('A first draft in about 7 days', 'sage'), 'text' => __('A working site you can click through — not a slide deck.', 'sage')],
        ['title' => __('Launch in 7–10 days', 'sage'), 'text' => __('Most local sites go live the same week we start.', 'sage')],
    ];
}

/**
 * Homepage “If this sounds familiar” cards — symptom plus the fix, so the
 * section is a diagnosis, not a complaint list.
 *
 * @return list<array{title:string,text:string,fix:string}>
 */
function home_pain_defaults(): array
{
    return [
        [
            'title' => __('Invisible in local search', 'sage'),
            'text'  => __('Hours, services, and the phone number live on Facebook — or nowhere a Gettysburg search can land.', 'sage'),
            'fix'   => __('A Visit page Google can index, with the basics on every page.', 'sage'),
        ],
        [
            'title' => __('Fights a phone', 'sage'),
            'text'  => __('Most visitors arrive on mobile. Small type, slow pages, and missed tap targets send them to the next shop.', 'sage'),
            'fix'   => __('Mobile-first layout, readable type, and one clear next step.', 'sage'),
        ],
        [
            'title' => __('Scary to update', 'sage'),
            'text'  => __('Changing a price or a photo feels like it might break the layout — so the site goes stale.', 'sage'),
            'fix'   => __('A handoff you can actually use, and a Care plan if you’d rather not touch it.', 'sage'),
        ],
    ];
}

/**
 * The registered input type ('text' | 'textarea' | 'html' | 'url' | 'lines' |
 * 'select' | ...) for a scalar field key, scanning page_field_map() across all
 * templates (first match wins). Defaults to 'text'. The live preview uses this to
 * sanitize draft values the SAME way saving does, so e.g. a typed "&" in a text
 * field isn't entity-encoded to "&amp;" (which would then double-escape in Blade).
 */
function field_type(string $key): string
{
    static $index = null;
    if ($index === null) {
        $index = [];
        foreach (page_field_map() as $groups) {
            if (! is_array($groups)) {
                continue;
            }
            foreach ($groups as $rows) {
                if (! is_array($rows)) {
                    continue;
                }
                foreach ($rows as $row) {
                    if (is_array($row) && isset($row[0], $row[2]) && is_string($row[0]) && ! isset($index[$row[0]])) {
                        $index[$row[0]] = (string) $row[2];
                    }
                }
            }
        }
    }
    return $index[$key] ?? 'text';
}

/**
 * Read a "lines" field — a simple list of strings, one per line in the editor.
 * Falls back to the given default list when empty. Shares the scalar preview
 * override path (rv/field_value) so the live editor preview updates as you type.
 */
function field_lines(string $key, array $default = [], ?int $post_id = null): array
{
    $post_id = $post_id ?: get_the_ID();
    $raw     = $post_id ? get_post_meta($post_id, 'rv_f_' . $key, true) : '';

    if (is_array($raw)) {
        $stored = $raw;
    } elseif ($raw !== '' && $raw !== null) {
        $stored = preg_split('/\r\n|\r|\n/', (string) $raw);
    } else {
        $stored = $default;
    }

    $joined        = implode("\n", array_map('strval', (array) $stored));
    $joinedDefault = implode("\n", $default);
    $val           = (string) apply_filters('rv/field_value', $joined, $key, (int) $post_id, $joinedDefault);

    $items = array_values(array_filter(array_map('trim', preg_split('/\r\n|\r|\n/', $val)), static fn ($s) => $s !== ''));
    return $items ?: $default;
}

/**
 * Read a "repeater" field — a list of rows, each an associative array of
 * sub-values. Falls back to the default rows when empty. In the live-editor
 * preview, scalar sub-values are wrapped in click-to-field markers so a click
 * in the preview jumps to the matching input.
 */
function field_rows(string $key, array $default = [], ?int $post_id = null): array
{
    $post_id = $post_id ?: get_the_ID();
    $raw     = $post_id ? get_post_meta($post_id, 'rv_f_' . $key, true) : '';

    $rows = (is_array($raw) && $raw !== []) ? array_values($raw) : $default;
    $rows = apply_filters('rv/field_rows', $rows, $key, (int) $post_id, $default);
    if (! is_array($rows) || $rows === []) {
        $rows = $default;
    }

    if (function_exists(__NAMESPACE__ . '\\preview_marking') && preview_marking()) {
        foreach ($rows as $i => &$row) {
            if (! is_array($row)) {
                continue;
            }
            foreach ($row as $sk => &$sv) {
                // Only wrap non-empty plain scalar cells (skip list/attribute cells).
                if (is_string($sv) && $sv !== '' && field_is_markable($sk)) {
                    $sv = "\x01" . $key . '.' . $i . '.' . $sk . "\x02" . $sv . "\x03";
                }
            }
            unset($sv);
        }
        unset($row);
    }

    return $rows;
}

/**
 * Normalize a repeater "lines" sub-value into an array of strings. Accepts a
 * stored array or (in the preview) a newline string, and strips any stray
 * click-to-field markers before splitting.
 */
function lines($val): array
{
    if (is_array($val)) {
        return array_values(array_filter(array_map('trim', array_map('strval', $val)), static fn ($s) => $s !== ''));
    }
    $s = strip_field_markers((string) $val);
    return array_values(array_filter(array_map('trim', preg_split('/\r\n|\r|\n/', $s)), static fn ($s) => $s !== ''));
}

/**
 * Built-in Gettysburg services packages — one source of truth for the template
 * defaults and the Page content repeater. Three project packages plus a monthly
 * care plan. Keep each feature list to three lines so the cards stay scannable.
 *
 * @return list<array{name:string,price:string,flag:string,for:string,desc:string,features:list<string>,cta:string,kind:string,url:string}>
 */
function svc_package_defaults(): array
{
    return [
        [
            'name'     => __('Website Rescue', 'sage'),
            'price'    => __('$950–$1,500', 'sage'),
            'flag'     => '',
            'for'      => __('A live site that’s slow, dated, or hard to find', 'sage'),
            'desc'     => __('Fix what’s in the way — without starting over.', 'sage'),
            'features' => [
                __('Speed, mobile, and accessibility fixes', 'sage'),
                __('Content and SEO cleanup', 'sage'),
                __('A punch-list of what to do next', 'sage'),
            ],
            'cta'      => '',
            'kind'     => 'project',
        ],
        [
            'name'     => __('Local Launch', 'sage'),
            'price'    => __('$2,750–$3,750', 'sage'),
            'flag'     => __('Most popular', 'sage'),
            'for'      => __('Gettysburg shops, inns, and trades ready for a new site', 'sage'),
            'desc'     => __('A 5-page site that gets you found locally and makes it easy to call.', 'sage'),
            'features' => [
                __('Custom design and copy', 'sage'),
                __('Google Business Profile + local SEO', 'sage'),
                __('Launch in 7–10 days', 'sage'),
            ],
            'cta'      => '',
            'kind'     => 'project',
        ],
        [
            'name'     => __('Growth Site', 'sage'),
            'price'    => __('$4,500+', 'sage'),
            'flag'     => '',
            'for'      => __('Expanding into more towns or services', 'sage'),
            'desc'     => __('More pages, stronger local SEO, and room to capture leads.', 'sage'),
            'features' => [
                __('Everything in Local Launch', 'sage'),
                __('Nearby-town and service pages', 'sage'),
                __('Blog, booking, or lead capture', 'sage'),
            ],
            'cta'      => '',
            'kind'     => 'project',
        ],
        [
            'name'     => __('Care & Grow', 'sage'),
            'price'    => __('$179–$349/mo', 'sage'),
            'flag'     => '',
            'for'      => __('After launch — keep the site healthy without hiring a developer', 'sage'),
            'desc'     => __('Hosting, updates, and small edits handled for you.', 'sage'),
            'features' => [
                __('Managed hosting and backups', 'sage'),
                __('Monthly edits and security', 'sage'),
                __('SEO reporting + priority support', 'sage'),
            ],
            'cta'      => __('Start a care plan', 'sage'),
            'kind'     => 'care',
        ],
    ];
}

/**
 * Package cards for the current (or given) page — used by the services template
 * and the homepage packages strip. Saved repeater rows win; missing keys (new
 * fields like “Best for” / layout) fill from the matching default by index, then
 * from shared fallbacks so older saved cards still render.
 *
 * @return list<array{name:string,price:string,flag:string,for:string,desc:string,features:list<string>,cta:string,kind:string,url:string}>
 */
function svc_packages(?int $post_id = null): array
{
    $defaults = svc_package_defaults();
    $rows     = field_rows('packages_items', $defaults, $post_id);
    $shared   = field('packages_btn', __('Get a quote', 'sage'), $post_id);

    foreach ($rows as $i => &$row) {
        if (! is_array($row)) {
            $row = $defaults[$i] ?? ['name' => '', 'price' => '', 'flag' => '', 'for' => '', 'desc' => '', 'features' => [], 'cta' => '', 'kind' => 'project'];
            continue;
        }
        $base = $defaults[$i] ?? [];
        // Only fill keys that older saved cards never had. Name, price, flag,
        // description, and features stay exactly as stored.
        foreach (['for', 'cta', 'kind'] as $k) {
            $raw = strip_field_markers((string) ($row[$k] ?? ''));
            if ($raw === '' && isset($base[$k]) && $base[$k] !== '') {
                $row[$k] = $base[$k];
            }
        }
        $cta = strip_field_markers((string) ($row['cta'] ?? ''));
        if ($cta === '') {
            $row['cta'] = $shared;
        }
        $kind  = sanitize_key(strip_field_markers((string) ($row['kind'] ?? '')));
        $price = strip_field_markers((string) ($row['price'] ?? ''));
        // Monthly retainers always use the care bar. The Layout select’s first
        // option is “project”, so older saved rows (and a save right after the
        // field shipped) often stored kind=project even on Care & Grow.
        if (str_contains(strtolower($price), '/mo')) {
            $kind = 'care';
        } elseif ($kind === '') {
            $kind = 'project';
        }
        $row['kind'] = $kind === 'care' ? 'care' : 'project';
        $row['url']  = strip_field_markers((string) ($row['url'] ?? ''));
    }
    unset($row);

    return array_values($rows);
}

/**
 * Button href for a services package. A saved URL wins; otherwise the contact
 * form with this package’s slug in the query string (never the display name —
 * “Care & Grow” would split on &). The form looks the slug back up to a name.
 */
function svc_package_slug(array $pkg): string
{
    return sanitize_title(strip_field_markers((string) ($pkg['name'] ?? '')));
}

function svc_package_href(array $pkg): string
{
    $custom = strip_field_markers((string) ($pkg['url'] ?? ''));
    if ($custom !== '') {
        return cta_href($custom);
    }
    $base = cta_href((string) get_theme_mod('rv_cta_url', '/contact/'));
    $slug = svc_package_slug($pkg);
    if ($slug !== '') {
        $base = add_query_arg('package', $slug, $base);
    }
    if (! str_contains($base, '#')) {
        $base .= '#contact-form';
    }

    return $base;
}

/** Display name for a package slug (website-rescue → Website Rescue). */
function svc_package_name_from_slug(string $slug): string
{
    $slug = sanitize_title($slug);
    if ($slug === '') {
        return '';
    }
    foreach (svc_package_defaults() as $p) {
        $name = strip_field_markers((string) ($p['name'] ?? ''));
        if (sanitize_title($name) === $slug) {
            return $name;
        }
    }

    return '';
}

/** Published page using the Services template, or 0 if none. */
function services_page_id(): int
{
    static $id = null;
    if ($id !== null) {
        return $id;
    }
    $found = get_posts([
        'post_type'      => 'page',
        'post_status'    => 'publish',
        'posts_per_page' => 1,
        'fields'         => 'ids',
        'meta_key'       => '_wp_page_template',
        'meta_value'     => 'template-services.blade.php',
        'no_found_rows'  => true,
    ]);
    if ($found) {
        $id = (int) $found[0];

        return $id;
    }
    foreach (['gettysburg-web-design-services', 'services'] as $slug) {
        $page = get_page_by_path($slug);
        if ($page instanceof \WP_Post) {
            $id = (int) $page->ID;

            return $id;
        }
    }
    $id = 0;

    return $id;
}

/** Project packages (comparison grid) for services and the homepage strip. */
function svc_project_packages(?int $post_id = null): array
{
    return array_values(array_filter(svc_packages($post_id), static fn ($p) => ($p['kind'] ?? 'project') !== 'care'));
}

/** Care-plan packages (full-width bar) for services and the homepage strip. */
function svc_care_packages(?int $post_id = null): array
{
    return array_values(array_filter(svc_packages($post_id), static fn ($p) => ($p['kind'] ?? '') === 'care'));
}

/**
 * Homepage “Proof, not promises” metrics — studio-level fallback when the
 * featured Project has no metric fields of its own.
 *
 * @return list<array{v:string,l:string}>
 */
function home_proof_metric_defaults(): array
{
    return [
        ['v' => __('7 days', 'sage'), 'l' => __('to first draft', 'sage')],
        ['v' => __('AA', 'sage'), 'l' => __('accessibility', 'sage')],
        ['v' => __('100%', 'sage'), 'l' => __('you own the site', 'sage')],
    ];
}

/**
 * Project-specific proof stats (_rv_m1..m3). Empty when the post has none,
 * so the template can fall back to home_proof_metric_defaults().
 *
 * @return list<array{v:string,l:string}>
 */
function home_proof_project_metrics(int $post_id): array
{
    $out = [];
    for ($n = 1; $n <= 3; $n++) {
        $v = (string) get_post_meta($post_id, "_rv_m{$n}_value", true);
        $l = (string) get_post_meta($post_id, "_rv_m{$n}_label", true);
        if (trim($v) === '') {
            continue;
        }
        $out[] = ['v' => $v, 'l' => $l];
    }

    return $out;
}

/**
 * Outcome cards under the featured story (studio-level, not tied to one client).
 *
 * @return list<array{title:string,text:string}>
 */
function home_proof_point_defaults(): array
{
    return [
        [
            'title' => __('Easy to reach', 'sage'),
            'text'  => __('Hours, phone, and one clear next step on every page — not buried in a PDF.', 'sage'),
        ],
        [
            'title' => __('Built to be found', 'sage'),
            'text'  => __('Local SEO so Gettysburg and Adams County searches can land on the right page.', 'sage'),
        ],
        [
            'title' => __('Shipped in days', 'sage'),
            'text'  => __('A working first draft in about a week, not a three-month agency calendar.', 'sage'),
        ],
    ];
}

/**
 * “Built here” reasons — why a Gettysburg studio is the point, not a town dump.
 *
 * @return list<array{title:string,text:string}>
 */
function home_rooted_point_defaults(): array
{
    return [
        [
            'title' => __('Meet in Gettysburg', 'sage'),
            'text'  => __('Coffee, a shop visit, or a screen share. You talk to the person who builds the site.', 'sage'),
        ],
        [
            'title' => __('Same-day replies', 'sage'),
            'text'  => __('No account manager, no ticket queue. Questions go straight to the studio.', 'sage'),
        ],
        [
            'title' => __('We know this ground', 'sage'),
            'text'  => __('Battlefield weekends, farm-stand seasons, and the towns around Adams County.', 'sage'),
        ],
    ];
}

/**
 * About-page hero proof ribbon. Kept in PHP so the Blade template can call it
 * with a one-line @php(...) — Sage can fatally fail compiling a multi-line
 * @php/@endphp block of nested __() arrays inside @section.
 *
 * @return list<array{v:string,l:string}>
 */
function about_proof_defaults(): array
{
    return [
        ['v' => __('15+ yrs', 'sage'), 'l' => __('building for the web', 'sage')],
        ['v' => __('~7 days', 'sage'), 'l' => __('to your first draft', 'sage')],
        ['v' => __('WCAG 2.2 AA', 'sage'), 'l' => __('on every page', 'sage')],
        ['v' => __('You own it', 'sage'), 'l' => __('site, domain, hosting', 'sage')],
    ];
}

/**
 * About hero proof rows. One-arg-free helper so Blade can call it with
 * `@php($aboutProof = \App\about_proof())` — nested calls inside `@php(...)`
 * have been compiled away, leaving `$aboutProof` null and crashing `count()`.
 *
 * @return list<array{v:string,l:string}>
 */
function about_proof(?int $post_id = null): array
{
    $rows = field_rows('about_proof', about_proof_defaults(), $post_id);

    return is_array($rows) ? $rows : about_proof_defaults();
}

/**
 * Services-page hero proof ribbon. Buyer-facing stats on the hero seam,
 * same pattern as Home / About. One-line @php(...) helper so Blade
 * doesn't compile nested default arrays away.
 *
 * @return list<array{v:string,l:string}>
 */
function svc_proof_defaults(): array
{
    return [
        ['v' => __('Fixed price', 'sage'), 'l' => __('agreed up front', 'sage')],
        ['v' => __('~7 days', 'sage'), 'l' => __('to your first draft', 'sage')],
        ['v' => __('WCAG 2.2 AA', 'sage'), 'l' => __('on every page', 'sage')],
        ['v' => __('You own it', 'sage'), 'l' => __('domain, hosting, content', 'sage')],
    ];
}

/**
 * @return list<array{v:string,l:string}>
 */
function svc_proof(?int $post_id = null): array
{
    $rows = field_rows('svc_proof', svc_proof_defaults(), $post_id);

    return is_array($rows) && $rows !== [] ? $rows : svc_proof_defaults();
}

/**
 * “What you’re really paying for” rows under the services hero.
 *
 * @return list<array{kicker:string,title:string,text:string}>
 */
function svcvalue_item_defaults(): array
{
    return [
        ['kicker' => __('Get found', 'sage'), 'title' => __('A site that gets found', 'sage'), 'text' => __('Local SEO and a properly set-up Google Business Profile, baked in — so neighbors and visitors in Adams County actually find you.', 'sage')],
        ['kicker' => __('Earn trust', 'sage'), 'title' => __('A site that earns trust', 'sage'), 'text' => __('Fast, mobile-first, accessible pages with clear copy and real photos — the things that turn a first-time visitor into a phone call.', 'sage')],
        ['kicker' => __('You own it', 'sage'), 'title' => __('A site you own', 'sage'), 'text' => __('Your domain, your hosting, your content — plus training so you can run it yourself. No lock-in, no ransom.', 'sage')],
    ];
}

/**
 * Work-page hero proof ribbon. Same seam pattern as Home / About — Blade
 * calls this with a one-line @php(...) so nested default arrays don't
 * get compiled away. Key is `work_proof` (not `work_stats`) so rewritten
 * short labels ship past stale saved meta.
 *
 * @return list<array{v:string,l:string}>
 */
function work_proof_defaults(): array
{
    return [
        ['v' => __('15+ yrs', 'sage'), 'l' => __('building for the web', 'sage')],
        ['v' => __('Live demos', 'sage'), 'l' => __('one concept per industry', 'sage')],
        ['v' => __('~7 days', 'sage'), 'l' => __('typical launch', 'sage')],
        ['v' => __('You own it', 'sage'), 'l' => __('site, domain, hosting', 'sage')],
    ];
}

/**
 * Work hero proof rows, normalized to the shared ribbon shape (v + l).
 *
 * @return list<array{v:string,l:string}>
 */
function work_stats(?int $post_id = null): array
{
    $rows = field_rows('work_proof', work_proof_defaults(), $post_id);
    if (! is_array($rows) || $rows === []) {
        $rows = work_proof_defaults();
    }

    $out = [];
    foreach ($rows as $row) {
        if (! is_array($row)) {
            continue;
        }
        $value = trim((string) ($row['value'] ?? $row['v'] ?? ''));
        $unit  = trim((string) ($row['unit'] ?? ''));
        $label = trim((string) ($row['label'] ?? $row['l'] ?? ''));
        if ($value === '' && $label === '') {
            continue;
        }
        $out[] = [
            'v' => trim($value . ($unit !== '' ? ' ' . $unit : '')),
            'l' => $label,
        ];
    }

    return $out !== [] ? $out : [];
}

/**
 * Under-hero funnel steps: find your industry → click the demo → get a quote.
 * Keyed `wwhy_items` so this copy ships past saved `work_why_items` meta.
 *
 * @return list<array{kicker:string,title:string,text:string}>
 */
function work_why_item_defaults(): array
{
    return [
        ['kicker' => __('Step 1', 'sage'), 'title' => __('Find your kind of business', 'sage'), 'text' => __('Restaurants, inns, shops, tours — tap the filter that matches you. You’ll see how I’d approach a site for a business like yours in Adams County.', 'sage')],
        ['kicker' => __('Step 2', 'sage'), 'title' => __('Click the live demo', 'sage'), 'text' => __('These aren’t screenshots. Open the working site on your phone, tap through menus and hours, feel the speed. That’s the proof — not a mood board.', 'sage')],
        ['kicker' => __('Step 3', 'sage'), 'title' => __('Then talk it through', 'sage'), 'text' => __('If it feels like a fit, get a fixed-price quote. No pitch deck, no account manager — just Matt, and a clear next step.', 'sage')],
    ];
}

/**
 * Journal-page hero proof ribbon. Same seam pattern as Home / About / Work.
 *
 * @return list<array{v:string,l:string}>
 */
function journal_proof_defaults(): array
{
    return [
        ['v' => __('Plain English', 'sage'), 'l' => __('written for owners, not developers', 'sage')],
        ['v' => __('Local SEO', 'sage'), 'l' => __('Gettysburg & Adams County', 'sage')],
        ['v' => __('Free tools', 'sage'), 'l' => __('grade your site in minutes', 'sage')],
        ['v' => __('Then talk', 'sage'), 'l' => __('a fixed-price quote when you’re ready', 'sage')],
    ];
}

/**
 * @return list<array{v:string,l:string}>
 */
function journal_proof(?int $post_id = null): array
{
    $rows = field_rows('jnl_proof', journal_proof_defaults(), $post_id);

    return is_array($rows) && $rows !== [] ? $rows : journal_proof_defaults();
}

/**
 * Under-hero funnel steps on the Journal index.
 *
 * @return list<array{kicker:string,title:string,text:string}>
 */
function journal_why_item_defaults(): array
{
    return [
        ['kicker' => __('Step 1', 'sage'), 'title' => __('Find the question you actually have', 'sage'), 'text' => __('Cost, Maps, Wix vs a local designer, “is my site working?” — pick a topic. These are buying guides, not blog musings.', 'sage')],
        ['kicker' => __('Step 2', 'sage'), 'title' => __('Read the honest answer', 'sage'), 'text' => __('Plain English, written for Gettysburg and Adams County owners. No jargon, no “it depends” that leaves you stuck.', 'sage')],
        ['kicker' => __('Step 3', 'sage'), 'title' => __('Then get a quote — or grade your site', 'sage'), 'text' => __('If it sounds like you, tell me about the business. Not ready? Use a free tool. Either way you leave with a next step.', 'sage')],
    ];
}

/**
 * “What happens next” after someone reaches out from the Journal closer.
 *
 * @return list<array{strong:string,text:string}>
 */
function journal_next_defaults(): array
{
    return [
        ['strong' => __('I reply — usually within a business day.', 'sage'), 'text' => __('A real note from Matt, not an auto-reminder.', 'sage')],
        ['strong' => __('A short, no-pressure chat.', 'sage'), 'text' => __('So I understand the business, the customers, and what a win looks like.', 'sage')],
        ['strong' => __('A clear, fixed-scope plan.', 'sage'), 'text' => __('What I’d build, what it costs, and how long — in writing.', 'sage')],
    ];
}

/**
 * Contact-page hero proof ribbon. Keyed `cnt_proof` so copy ships past saved meta.
 *
 * @return list<array{v:string,l:string}>
 */
function contact_proof_defaults(): array
{
    return [
        ['v' => __('A real person', 'sage'), 'l' => __('Matt replies — not a ticket queue', 'sage')],
        ['v' => __('Fixed price', 'sage'), 'l' => __('agreed up front, in writing', 'sage')],
        ['v' => __('You own it', 'sage'), 'l' => __('domain, hosting, and the site', 'sage')],
        ['v' => __('No pressure', 'sage'), 'l' => __('a quote, an intro, or just hello', 'sage')],
    ];
}

/**
 * @return list<array{v:string,l:string}>
 */
function contact_proof(?int $post_id = null): array
{
    $rows = field_rows('cnt_proof', contact_proof_defaults(), $post_id);

    return is_array($rows) && $rows !== [] ? $rows : contact_proof_defaults();
}

/**
 * Under-hero: why this page exists, without the hard sell.
 *
 * @return list<array{kicker:string,title:string,text:string}>
 */
function contact_why_item_defaults(): array
{
    return [
        ['kicker' => __('A quote', 'sage'), 'title' => __('Tell me what you need', 'sage'), 'text' => __('A new site, a rescue, Maps, or ongoing care. A few details are enough — I’ll come back with a fixed-scope idea, usually within a business day.', 'sage')],
        ['kicker' => __('Not sure yet', 'sage'), 'title' => __('That’s a fine place to start', 'sage'), 'text' => __('Skip anything you don’t know. Grade your site first if you’d rather look around. I’ll still read whatever you send.', 'sage')],
        ['kicker' => __('Just hello', 'sage'), 'title' => __('Introductions are welcome', 'sage'), 'text' => __('Partnerships, referrals, a coffee in Gettysburg, or “I know someone who might need this.” This page isn’t only for buyers.', 'sage')],
    ];
}

/**
 * “What happens next” beside the quote form.
 *
 * @return list<array{strong:string,text:string}>
 */
function contact_next_defaults(): array
{
    return [
        ['strong' => __('I read it — usually within a business day.', 'sage'), 'text' => __('A real note from Matt. Not an auto-reply, not a calendar link.', 'sage')],
        ['strong' => __('A short, no-pressure reply.', 'sage'), 'text' => __('A couple of questions if I need them. Never a pitch deck.', 'sage')],
        ['strong' => __('A clear next step.', 'sage'), 'text' => __('A fixed-scope idea if it’s a project — or an honest pointer if it isn’t.', 'sage')],
    ];
}

/** @return list<array{title:string,text:string}> */
function about_promise_defaults(): array
{
    return [
        ['title' => __('Get found', 'sage'), 'text' => __('Local SEO and a Google Business Profile, baked in — so neighbors and visitors actually find you.', 'sage')],
        ['title' => __('Get chosen', 'sage'), 'text' => __('Clear pages, real photos, and a site that works on a phone — the things that turn a visit into a call.', 'sage')],
        ['title' => __('You own it', 'sage'), 'text' => __('Your domain, hosting, and content. Take the keys and run it, or keep us on a care plan. No lock-in.', 'sage')],
    ];
}

/** @return list<array{name:string,role:string,bio:string,photo:string}> */
function about_people_defaults(): array
{
    return [
        ['name' => __('Matt Hummel', 'sage'), 'role' => __('Founder & lead developer', 'sage'), 'bio' => __('Fifteen years building for the web — university marketing, then government apps, now a Gettysburg studio for Main Street. You’ll work with Matt, not a ticket queue.', 'sage'), 'photo' => ''],
        ['name' => __('Shannon Hummel', 'sage'), 'role' => __('Studio assistant & client care', 'sage'), 'bio' => __('Keeps projects moving and people looked after — scheduling, follow-through, and a people-first approach so working with the studio feels like a local partnership.', 'sage'), 'photo' => ''],
    ];
}

/** @return list<array{title:string,text:string}> */
function about_value_defaults(): array
{
    return [
        ['title' => __('A person, not a queue', 'sage'), 'text' => __('You talk to Matt. Questions get answers. After launch you still know who to call.', 'sage')],
        ['title' => __('Calls over chrome', 'sage'), 'text' => __('Your customers care about finding you, trusting you, and booking you. The tech stays in the background.', 'sage')],
        ['title' => __('Everyone gets in', 'sage'), 'text' => __('A fast, accessible site isn’t a luxury — it’s your front door. We build toward WCAG 2.2 AA on every page.', 'sage')],
    ];
}

/** @return list<array{n:string,title:string,text:string}> */
function about_next_defaults(): array
{
    return [
        ['n' => '01', 'title' => __('Tell me about the business', 'sage'), 'text' => __('A short note or a call. No jargon, no pressure.', 'sage')],
        ['n' => '02', 'title' => __('A fixed-scope idea', 'sage'), 'text' => __('Usually within a business day — what I’d do, and what it costs, in writing.', 'sage')],
        ['n' => '03', 'title' => __('A first draft in about a week', 'sage'), 'text' => __('You can click through it. Then we launch, and you own every bit of it.', 'sage')],
    ];
}

/**
 * Baseline inclusions for the services “What every build includes” grid.
 * Shared by the template defaults and the Page content repeater.
 *
 * @return list<array{title:string,text:string}>
 */
function svc_incl_point_defaults(): array
{
    return [
        [
            'title' => __('Accessible by default', 'sage'),
            'text'  => __('WCAG 2.2 AA-minded pages — contrast, keyboard, and screen readers. Not a bolt-on at the end.', 'sage'),
        ],
        [
            'title' => __('Built for phones first', 'sage'),
            'text'  => __('Most local searches happen on a phone. Layout, type, and tap targets start there.', 'sage'),
        ],
        [
            'title' => __('Local SEO foundations', 'sage'),
            'text'  => __('On-page titles, LocalBusiness schema, and Google Business Profile setup so Gettysburg searches can find you.', 'sage'),
        ],
        [
            'title' => __('Fast, clean code', 'sage'),
            'text'  => __('No page-builder bloat. HTTPS, caching, and hosting tuned so the page actually loads.', 'sage'),
        ],
        [
            'title' => __('Analytics in your name', 'sage'),
            'text'  => __('Analytics and Search Console on accounts you own, plus a plain-English read of what to watch.', 'sage'),
        ],
        [
            'title' => __('A handoff you keep', 'sage'),
            'text'  => __('A walkthrough and a video. Domain, hosting, and the site stay in your name — no lock-in.', 'sage'),
        ],
    ];
}

/**
 * Boundary cards under the inclusion grid (not-in-scope + how we work).
 *
 * @return list<array{title:string,text:string}>
 */
function svc_bound_defaults(): array
{
    return [
        [
            'title' => __('Not in this studio (yet)', 'sage'),
            'text'  => __('No paid ads, no full social management, no giant content retainers. We do websites, well.', 'sage'),
        ],
        [
            'title' => __('Guardrails that keep the price honest', 'sage'),
            'text'  => __('One decision-maker, one consolidated revision round, feedback within two business days, balance before launch.', 'sage'),
        ],
    ];
}

/**
 * Local SEO checklist on the services page. Six items so the grid is even
 * (3×2). “Map pack” and “mobile-first” live in the intro / included section.
 *
 * @return list<array{title:string,text:string}>
 */
function svc_seo_point_defaults(): array
{
    return [
        [
            'title' => __('Google Business Profile', 'sage'),
            'text'  => __('Claimed, verified, and kept current — categories, hours, photos, and posts that help you show in Maps and the local pack.', 'sage'),
        ],
        [
            'title' => __('The words people here actually type', 'sage'),
            'text'  => __('Town, service, and seasonal terms built into your pages — not generic “best company” copy.', 'sage'),
        ],
        [
            'title' => __('Town & service-area pages', 'sage'),
            'text'  => __('Indexable pages for Gettysburg and the nearby towns you serve, so you can rank in more than one place.', 'sage'),
        ],
        [
            'title' => __('LocalBusiness schema', 'sage'),
            'text'  => __('Structured data that tells Google what you are, where you are, and what you offer.', 'sage'),
        ],
        [
            'title' => __('Name, address, and phone that match', 'sage'),
            'text'  => __('NAP consistency across your site and listings — the signal local search trusts most.', 'sage'),
        ],
        [
            'title' => __('Reviews that show up', 'sage'),
            'text'  => __('A simple way to earn reviews and display them, so new customers see the proof before they call.', 'sage'),
        ],
    ];
}

/**
 * Where local SEO sits in the packages (baked in vs. a tune-up).
 *
 * @return list<array{title:string,text:string}>
 */
function svc_seo_bound_defaults(): array
{
    return [
        [
            'title' => __('In Local Launch & Growth Site', 'sage'),
            'text'  => __('This local SEO work is in the build — not a surprise invoice after launch.', 'sage'),
        ],
        [
            'title' => __('Already have a site?', 'sage'),
            'text'  => __('A Website Rescue or a standalone tune-up can fix Maps, listings, and on-page without starting over.', 'sage'),
        ],
    ];
}

/**
 * Services FAQ as [question, answer] pairs for JSON-LD. Defaults match the
 * template so schema stays in sync when the repeater is empty.
 *
 * @return list<array{0:string,1:string}>
 */
function svc_faq_item_defaults(): array
{
    return [
        ['q' => __('What does this actually cost?', 'sage'), 'a' => __('A fixed price, in writing, before I start. Rescue, Local Launch, or Growth Site — you pick the scope, I give you the number. No hourly meter, no surprise invoices. Not sure which fits? Tell me about the business and I’ll point you.', 'sage')],
        ['q' => __('How soon can we be live?', 'sage'), 'a' => __('Most local sites go live in 7–10 days once I have your photos, hours, and a bit about who you serve. You’ll see a real first draft in about a week. Bigger builds take a little longer — and you’ll know the timeline before we start, not after.', 'sage')],
        ['q' => __('Is the site actually mine when we’re done?', 'sage'), 'a' => __('Yes. Domain, hosting, every page — in your name. Want to run it yourself, hand it to someone else, or keep me on a care plan? It’s yours either way. No lock-in, no ransom.', 'sage')],
        ['q' => __('I already have a website. Can you just fix it?', 'sage'), 'a' => __('That’s the Website Rescue — I look at what you have and fix the things that cost you calls: speed, mobile, accessibility, local SEO. You don’t have to start over unless the current site is really in the way.', 'sage')],
        ['q' => __('What if I need to change something later?', 'sage'), 'a' => __('During the build you get one clear round of revisions — gather your notes, I apply them in a single pass. After launch, a Care & Grow plan covers small edits, or you can ask as you go. You’re never stuck waiting on a ticket queue.', 'sage')],
        ['q' => __('Do I have to deal with hosting and domains?', 'sage'), 'a' => __('No. I set both up in your name and can keep an eye on them, or hand you the keys on day one. Either way you’re never locked into me to keep the lights on.', 'sage')],
        ['q' => __('Will it work on a phone — and for everyone?', 'sage'), 'a' => __('That’s the baseline, not an add-on. Mobile-first pages, tested on a real phone, built toward WCAG 2.2 AA — so a visitor using a screen reader, or a tourist on Steinwehr with one bar of signal, can still find your hours and call you.', 'sage')],
        ['q' => __('Do you only work with Gettysburg businesses?', 'sage'), 'a' => __('Home base is Gettysburg and Adams County — Biglerville, Littlestown, New Oxford, Hanover, and the townships around them. Farther out in South Central PA is fine too. Most of the work happens over a call and a shared screen.', 'sage')],
    ];
}

/**
 * @return list<array{0:string,1:string}>
 */
function svc_faq_pairs(?int $post_id = null): array
{
    $rows = field_rows('sfaq_qs', svc_faq_item_defaults(), $post_id);

    $pairs = [];
    foreach ($rows as $f) {
        $q = trim(strip_field_markers((string) ($f['q'] ?? '')));
        $a = trim(strip_field_markers((string) ($f['a'] ?? '')));
        if ($q !== '' && $a !== '') {
            $pairs[] = [$q, $a];
        }
    }

    return $pairs;
}

/**
 * “Launch day isn’t goodbye” — included support vs optional care.
 *
 * @return list<array{kicker:string,title:string,text:string}>
 */
function svc_after_point_defaults(): array
{
    return [
        ['kicker' => __('Included', 'sage'), 'title' => __('30-day workmanship warranty', 'sage'), 'text' => __('If something I built breaks in the first month, I fix it. No charge, no debate, no ticket queue — you call Matt.', 'sage')],
        ['kicker' => __('Included', 'sage'), 'title' => __('A training handoff', 'sage'), 'text' => __('A short walkthrough so you can change hours, prices, and photos yourself. You are not waiting on me to open the shop.', 'sage')],
        ['kicker' => __('Always', 'sage'), 'title' => __('You own the Gettysburg website', 'sage'), 'text' => __('Domain, hosting, and every page stay in your name. Take it in-house, hand it off, or keep a care plan. No lock-in.', 'sage')],
    ];
}

/**
 * Security checker closer — three concrete lock-down promises next to the CTA.
 *
 * @return list<array{kicker:string,title:string,text:string}>
 */
function security_lock_item_defaults(): array
{
    return [
        ['kicker' => __('Close it', 'sage'), 'title' => __('The gaps the scan just found', 'sage'), 'text' => __('HTTPS, headers, mixed content, cookie flags. I fix what’s actually broken — and tell you what it means.', 'sage')],
        ['kicker' => __('Keep it', 'sage'), 'title' => __('Patched, backed up, current', 'sage'), 'text' => __('WordPress, plugins, and PHP stay up to date so last month’s exploit isn’t next week’s headache.', 'sage')],
        ['kicker' => __('Watch it', 'sage'), 'title' => __('A human still looking', 'sage'), 'text' => __('Malware checks, backups, and someone who notices when something’s off. Not a ticket queue.', 'sage')],
    ];
}

/** @return list<string> */
function security_cover_yes_defaults(): array
{
    return [
        __('HTTPS, the padlock, and whether http still loads', 'sage'),
        __('Security headers browsers use as free extra locks', 'sage'),
        __('Whether the server advertises its exact software', 'sage'),
        __('Cookie flags, if this page sets cookies', 'sage'),
        __('Third-party scripts, named (Tag Manager, Pixel, and the rest)', 'sage'),
        __('WordPress login and xmlrpc, when the site looks like WordPress', 'sage'),
    ];
}

/** @return list<string> */
function security_cover_no_defaults(): array
{
    return [
        __('Passwords, plugins, or malware', 'sage'),
        __('Your hosting, firewall, or backups', 'sage'),
        __('A real penetration test', 'sage'),
    ];
}

/**
 * Free Tools hub — proof ribbon under the hero.
 *
 * @return list<array{v:string,l:string}>
 */
function tools_proof_defaults(): array
{
    return [
        ['v' => __('6', 'sage'), 'l' => __('URL checkers', 'sage')],
        ['v' => __('0', 'sage'), 'l' => __('emails collected', 'sage')],
        ['v' => __('~30s', 'sage'), 'l' => __('to a first grade', 'sage')],
        ['v' => __('PA', 'sage'), 'l' => __('built in Gettysburg', 'sage')],
    ];
}

/**
 * “Not sure where to start” chooser cards.
 *
 * @return list<array{kicker:string,title:string,text:string,cta:string,url:string}>
 */
function tools_pick_defaults(): array
{
    return [
        [
            'kicker' => __('Start here', 'sage'),
            'title'  => __('Not sure what to check?', 'sage'),
            'text'   => __('Run the Website Grader. It scores seven areas and points you at the dedicated checker for whatever scored lowest.', 'sage'),
            'cta'    => __('Open the grader', 'sage'),
            'url'    => '/website-grader/',
        ],
        [
            'kicker' => __('Get found', 'sage'),
            'title'  => __('Want nearby customers to find you?', 'sage'),
            'text'   => __('Use the SEO Checker for Google’s view of a page, then the Local SEO Scorecard for Maps, NAP, and reviews.', 'sage'),
            'cta'    => __('Check search', 'sage'),
            'url'    => '/seo-checker/',
        ],
        [
            'kicker' => __('Earn trust', 'sage'),
            'title'  => __('Worried about safety or access?', 'sage'),
            'text'   => __('Security and email records protect customers. Accessibility opens the door for everyone — including Google.', 'sage'),
            'cta'    => __('Check trust', 'sage'),
            'url'    => '/security-checker/',
        ],
    ];
}

/**
 * URL checker cards on the Free Tools hub.
 *
 * @return list<array{flag:string,title:string,url:string,text:string,why:string,time:string,group:string,featured:string}>
 */
function tools_checker_defaults(): array
{
    return [
        [
            'flag'     => __('Start here', 'sage'),
            'title'    => __('Website Grader', 'sage'),
            'url'      => '/website-grader/',
            'text'     => __('Your whole site, graded across seven areas — SEO, page speed, mobile, readability, security, technical health, and social sharing.', 'sage'),
            'why'      => __('The fastest way to see where you stand and what to fix first.', 'sage'),
            'time'     => __('~30 sec', 'sage'),
            'group'    => 'search',
            'featured' => '1',
        ],
        [
            'flag'     => '',
            'title'    => __('SEO Checker', 'sage'),
            'url'      => '/seo-checker/',
            'text'     => __('A deep search audit with a live Google snippet preview, target-keyword analysis, crawlability (robots.txt + sitemap), and structured data.', 'sage'),
            'why'      => __('Shows exactly why you are — or aren’t — showing up in search.', 'sage'),
            'time'     => __('~20 sec', 'sage'),
            'group'    => 'search',
            'featured' => '0',
        ],
        [
            'flag'     => '',
            'title'    => __('Local SEO Scorecard', 'sage'),
            'url'      => '/local-seo/',
            'text'     => __('Scores the local signals — NAP, LocalBusiness schema, maps, and reviews — that get you into Google’s map pack.', 'sage'),
            'why'      => __('Where much of a local business’s real traffic comes from.', 'sage'),
            'time'     => __('~20 sec', 'sage'),
            'group'    => 'local',
            'featured' => '0',
        ],
        [
            'flag'     => '',
            'title'    => __('Accessibility Checker', 'sage'),
            'url'      => '/accessibility/',
            'text'     => __('Scan any page against WCAG 2.2 AA — the latest W3C standard — with a live, running checklist.', 'sage'),
            'why'      => __('Reach more customers and lower your legal risk.', 'sage'),
            'time'     => __('~25 sec', 'sage'),
            'group'    => 'access',
            'featured' => '0',
        ],
        [
            'flag'     => '',
            'title'    => __('Security Checker', 'sage'),
            'url'      => '/security-checker/',
            'text'     => __('HTTPS and HSTS, the modern browser security headers, cookie safety, information leaks, and front-end risks.', 'sage'),
            'why'      => __('Protect your customers’ trust — and your reputation.', 'sage'),
            'time'     => __('~15 sec', 'sage'),
            'group'    => 'trust',
            'featured' => '0',
        ],
        [
            'flag'     => '',
            'title'    => __('Email Deliverability Checker', 'sage'),
            'url'      => '/email-checker/',
            'text'     => __('Checks the DNS records — SPF, DKIM, and DMARC — that decide whether your email is trusted or lands in spam.', 'sage'),
            'why'      => __('Stop your invoices landing in junk, and stop scammers spoofing you.', 'sage'),
            'time'     => __('~15 sec', 'sage'),
            'group'    => 'trust',
            'featured' => '0',
        ],
    ];
}

/**
 * Filter chips for the checker grid. `key` must match a checker’s `group` (or `all`).
 *
 * @return list<array{key:string,label:string}>
 */
function tools_filter_defaults(): array
{
    return [
        ['key' => 'all', 'label' => __('All', 'sage')],
        ['key' => 'search', 'label' => __('Search', 'sage')],
        ['key' => 'local', 'label' => __('Local', 'sage')],
        ['key' => 'trust', 'label' => __('Trust', 'sage')],
        ['key' => 'access', 'label' => __('Access', 'sage')],
    ];
}

/**
 * How to use these tools — numbered steps.
 *
 * @return list<array{title:string,text:string}>
 */
function tools_how_defaults(): array
{
    return [
        ['title' => __('Start with the grader', 'sage'), 'text' => __('Run the Website Grader first for the big picture, then open the dedicated checker for whichever area scored lowest.', 'sage')],
        ['title' => __('Fix the reds first', 'sage'), 'text' => __('A red Fail is something a visitor or search engine hits today. Amber Review items are worth a look; greens are already working.', 'sage')],
        ['title' => __('Check a competitor', 'sage'), 'text' => __('These work on any public URL. Grade a nearby shop to see where you can leapfrog them — often speed, mobile, or local SEO.', 'sage')],
        ['title' => __('Re-check after a change', 'sage'), 'text' => __('Made a fix? Run the tool again. Scores update on the spot, so you can see progress instead of guessing.', 'sage')],
    ];
}

/** @return list<string> */
function tools_cover_yes_defaults(): array
{
    return [
        __('HTTPS, titles, meta, and headings', 'sage'),
        __('Whether search engines are allowed to index the page', 'sage'),
        __('Mobile basics, speed clues, and social sharing tags', 'sage'),
        __('Security headers, cookie flags, and email DNS (SPF / DKIM / DMARC)', 'sage'),
        __('Local signals: NAP, schema, maps, and reviews', 'sage'),
        __('Common accessibility misses a machine can see', 'sage'),
    ];
}

/** @return list<string> */
function tools_cover_no_defaults(): array
{
    return [
        __('Whether your message actually lands with a customer', 'sage'),
        __('The path from visit to a phone call or booking', 'sage'),
        __('Passwords, plugins, malware, or a real pentest', 'sage'),
        __('Screen-reader and keyboard testing a person has to do', 'sage'),
        __('Your Google Business Profile behind a login', 'sage'),
        __('Copy, photos, and whether the site sounds like you', 'sage'),
    ];
}

/**
 * Privacy / “no catch” promises.
 *
 * @return list<array{title:string,text:string}>
 */
function tools_privacy_defaults(): array
{
    return [
        ['title' => __('No email. No account.', 'sage'), 'text' => __('Enter a URL (and, for SEO, an optional keyword). Results show on the spot. Nothing is gated.', 'sage')],
        ['title' => __('We don’t keep your scores.', 'sage'), 'text' => __('Each run fetches the page to analyze it, then throws the report away. Run it as often as you like.', 'sage')],
        ['title' => __('No surprise sales call.', 'sage'), 'text' => __('The tools work with no strings. If you want the issues fixed, ask — that’s your call.', 'sage')],
    ];
}

/**
 * After-the-score next steps into paid work.
 *
 * @return list<array{title:string,text:string,cta:string,url:string}>
 */
function tools_next_defaults(): array
{
    return [
        [
            'title' => __('Website Rescue', 'sage'),
            'text'  => __('A live site that’s slow, dated, or hard to find — fix what’s in the way without starting over.', 'sage'),
            'cta'   => __('See Rescue', 'sage'),
            'url'   => '/gettysburg-web-design-services/#packages',
        ],
        [
            'title' => __('Local Launch', 'sage'),
            'text'  => __('A new 5-page site with local SEO baked in. First draft in about seven days. You own it.', 'sage'),
            'cta'   => __('See packages', 'sage'),
            'url'   => '/gettysburg-web-design-services/#packages',
        ],
        [
            'title' => __('A prioritized plan', 'sage'),
            'text'  => __('Send the URL. I’ll turn the reds into a short punch-list in plain English — and I can do the work.', 'sage'),
            'cta'   => __('Get a quote', 'sage'),
            'url'   => '/contact/',
        ],
    ];
}

/**
 * Free Tools FAQ rows.
 *
 * @return list<array{q:string,a:string}>
 */
function tools_faq_defaults(): array
{
    return [
        ['q' => __('Are these tools really free?', 'sage'), 'a' => __('Yes — completely. There’s no account to create, no email to hand over, and no credit card. Run them as often as you like, on any site you like.', 'sage')],
        ['q' => __('Do I have to give you my email or sign up?', 'sage'), 'a' => __('No. Enter a URL (and, for the SEO checker, an optional keyword) and you get your results on the spot. Nothing is gated behind a form.', 'sage')],
        ['q' => __('How accurate are the results?', 'sage'), 'a' => __('The URL tools read your page’s actual code and its server responses, so they’re reliable for the things a machine can measure — HTTPS, meta tags, headings, security headers, and the like. Judgment calls like design, message clarity, and deep accessibility still need a human. That’s the part I finish by hand.', 'sage')],
        ['q' => __('What’s the difference between the Website Grader and the SEO Checker?', 'sage'), 'a' => __('The Website Grader is a broad, seven-area report card — a quick overall health check. The SEO Checker goes deep on search specifically, with a Google snippet preview, keyword analysis, crawlability (robots.txt and sitemap), and structured data. Use the grader for the big picture, the SEO checker to dig into rankings.', 'sage')],
        ['q' => __('Will you try to sell me something?', 'sage'), 'a' => __('Only if you ask. The tools work with no strings attached. If you’d like the issues fixed for you, I’m happy to help — but that’s your call, not a catch.', 'sage')],
        ['q' => __('Can you fix what the tools find?', 'sage'), 'a' => __('Yes. Send me your URL and I’ll turn the results into a short, prioritized plan in plain English — and I can do the work, from a quick Website Rescue to a full rebuild.', 'sage')],
        ['q' => __('Do you work with businesses in my town?', 'sage'), 'a' => __('I serve Gettysburg, Adams County, and the surrounding South Central PA area — Hanover, Littlestown, New Oxford, McSherrystown, Biglerville, Fairfield, and nearby. If you’re local, we’re a fit.', 'sage')],
        ['q' => __('Do the tools store my website’s data?', 'sage'), 'a' => __('They fetch the page you enter in order to analyze it, and they don’t save your results. Each run is a fresh, on-the-spot check.', 'sage')],
        ['q' => __('What do the colors mean?', 'sage'), 'a' => __('Green is a pass — that check is working. Amber means review: not broken, but worth a look. Red is a fail: a real problem a visitor or search engine can hit today. Start with the reds.', 'sage')],
        ['q' => __('Can I run these on a site that isn’t live yet?', 'sage'), 'a' => __('They need a public URL the tool can fetch. A staging site works if it isn’t password-gated. A local-only or login-walled page won’t return a useful scan.', 'sage')],
    ];
}

/** Which field set applies to a page (front page detected via the reading setting). */
function page_template_key(int $post_id): string
{
    if ($post_id && (int) get_option('page_on_front') === $post_id) {
        return 'front-page.blade.php';
    }
    if ($post_id && (int) get_option('page_for_posts') === $post_id) {
        return 'index.blade.php';
    }
    return (string) get_post_meta($post_id, '_wp_page_template', true);
}

/**
 * The field map: template file => [ 'Group label' => [ [key, label, type, current], … ] ].
 * type is 'text' | 'textarea'; `current` is the copy shown as the input placeholder.
 */
function page_field_map(): array
{
    $hero = function (string $eyebrow, string $title, string $accent, string $sub): array {
        return [
            ['hero_eyebrow', __('Hero eyebrow', 'sage'), 'text', $eyebrow],
            ['hero_title', __('Hero title', 'sage'), 'text', $title],
            ['hero_accent', __('Hero accent word', 'sage'), 'text', $accent],
            ['hero_lede', __('Hero lede (one short paragraph)', 'sage'), 'textarea', $sub],
        ];
    };
    $cta = function (string $title, string $sub, string $button): array {
        return [
            ['cta_title', __('CTA heading', 'sage'), 'text', $title],
            ['cta_sub', __('CTA subtext', 'sage'), 'textarea', $sub],
            ['cta_button', __('CTA button label', 'sage'), 'text', $button],
        ];
    };

    $map = [
        'front-page.blade.php' => [
            __('Hero', 'sage') => [
                ['home_kicker', __('Eyebrow', 'sage'), 'text', __('Web design & local SEO · Gettysburg', 'sage')],
                ['home_headline', __('Headline (before accent)', 'sage'), 'text', __('Gettysburg web design that gets you', 'sage')],
                ['home_headline_accent', __('Headline accent', 'sage'), 'text', __('found.', 'sage')],
                ['home_lede', __('Lede (one short paragraph)', 'sage'), 'textarea', __('Fixed-scope WordPress sites for Adams County — local SEO baked in, a first draft in about seven days, and a site you own.', 'sage')],
                ['home_cta1', __('Button 1 · label', 'sage'), 'text', __('Get a quote', 'sage')],
                ['home_cta2', __('Button 2 · label', 'sage'), 'text', __('See packages', 'sage')],
                ['home_byline', __('Byline under the buttons', 'sage'), 'text', __('Family-owned in Gettysburg · Led by Matt Hummel', 'sage')],
                ['home_cta1_url', __('Button 1 · link', 'sage'), 'url', __('e.g. /contact/', 'sage')],
                ['home_cta2_url', __('Button 2 · link', 'sage'), 'url', __('e.g. /gettysburg-web-design-services/#packages', 'sage')],
                ['hero_btn1_style', __('Button 1 · style', 'sage'), 'select', '', [
                    ''        => __('Theme default', 'sage'),
                    'primary' => __('Primary (filled)', 'sage'),
                    'ghost'   => __('Ghost (outline)', 'sage'),
                ]],
                ['hero_btn2_style', __('Button 2 · style', 'sage'), 'select', '', [
                    ''        => __('Theme default', 'sage'),
                    'primary' => __('Primary (filled)', 'sage'),
                    'ghost'   => __('Ghost (outline)', 'sage'),
                ]],
                ['hero_col2_title', __('Hero column 2 · heading (optional)', 'sage'), 'text', ''],
                ['hero_col2_body', __('Hero column 2 · content (optional, HTML allowed)', 'sage'), 'html', ''],
                ['hero_col3_title', __('Hero column 3 · heading (optional)', 'sage'), 'text', ''],
                ['hero_col3_body', __('Hero column 3 · content (optional, HTML allowed)', 'sage'), 'html', ''],
            ],
            __('Intro section', 'sage') => [
                ['intro_eyebrow', __('Eyebrow', 'sage'), 'text', __('A local studio', 'sage')],
                ['intro_title', __('Heading (before accent)', 'sage'), 'text', __('Gettysburg web design that', 'sage')],
                ['intro_accent', __('Accent phrase', 'sage'), 'text', __('brings in customers.', 'sage')],
                ['intro_who_title', __('Who we are · heading', 'sage'), 'text', __('Who we are', 'sage')],
                ['intro_who_text', __('Who we are · paragraph', 'sage'), 'textarea', __('Ridges & Valleys Studio builds fast, accessible WordPress websites for small businesses across Gettysburg, Adams County, and South Central Pennsylvania. Led by Matt Hummel, a local developer with more than 15 years of experience, every site is mobile-first, built toward WCAG 2.2 AA, optimized for local search, and fully owned by you.', 'sage')],
                ['intro_who_points', __('Who we are · scan lines (one per line)', 'sage'), 'lines', home_intro_point_defaults()],
                ['intro_how_title', __('How we work · heading', 'sage'), 'text', __('How we work', 'sage')],
                ['intro_steps', __('How we work · steps', 'sage'), 'repeater', home_intro_step_defaults(), [
                    ['title', __('Step title', 'sage'), 'text'],
                    ['text', __('Step text', 'sage'), 'textarea'],
                ]],
                ['intro_how_text', __('How we work · closer', 'sage'), 'textarea', __('Clear pricing, straightforward communication, and a site designed to turn visitors into calls.', 'sage')],
                ['intro_link1', __('Link 1 · label', 'sage'), 'text', __('See web design services', 'sage')],
                ['intro_link1_url', __('Link 1 · URL', 'sage'), 'url', __('e.g. /gettysburg-web-design-services/', 'sage')],
                ['intro_link2', __('Link 2 · label', 'sage'), 'text', __('Request a quote', 'sage')],
                ['intro_link2_url', __('Link 2 · URL', 'sage'), 'url', __('e.g. /contact/', 'sage')],
            ],
            __('Problems section', 'sage') => [
                ['pain_eyebrow', __('Eyebrow', 'sage'), 'text', __('If this sounds familiar', 'sage')],
                ['pain_h2', __('Heading (before accent)', 'sage'), 'text', __('When Gettysburg customers', 'sage')],
                ['pain_h2_accent', __('Accent phrase', 'sage'), 'text', __('can’t find you.', 'sage')],
                ['pain_lede', __('Intro under the heading', 'sage'), 'textarea', __('Hours on Facebook, a site that fights a phone, a page you’re afraid to edit — that’s what we hear from Adams County shops and firms. Gettysburg web design should fix those, not add to them.', 'sage')],
                ['pain_items', __('The three snags (symptom + fix)', 'sage'), 'repeater', home_pain_defaults(), [
                    ['title', __('Snag title', 'sage'), 'text'],
                    ['text', __('What it looks like', 'sage'), 'textarea'],
                    ['fix', __('The fix (one line)', 'sage'), 'text'],
                ]],
                ['pain_close', __('Closer under the cards', 'sage'), 'text', __('If two of these are true, it’s a rebuild — not another plugin.', 'sage')],
                ['pain_cta', __('Primary button', 'sage'), 'text', __('See how we fix this', 'sage')],
                ['pain_cta_url', __('Primary button link', 'sage'), 'url', __('e.g. /gettysburg-web-design-services/', 'sage')],
                ['pain_cta2', __('Secondary button', 'sage'), 'text', __('Get a quote', 'sage')],
                ['pain_cta2_url', __('Secondary button link', 'sage'), 'url', __('e.g. /contact/', 'sage')],
            ],
            __('Packages section', 'sage') => [
                ['pkg_eyebrow', __('Eyebrow', 'sage'), 'text', __('Clear scope. Fast build. No mystery.', 'sage')],
                ['pkg_title', __('Heading', 'sage'), 'text', __('Three ways to', 'sage')],
                ['pkg_accent', __('Accent word', 'sage'), 'text', __('start.', 'sage')],
                ['pkg_cta', __('“Compare all services” button label', 'sage'), 'text', __('Compare all services', 'sage')],
                ['pkg_compare_url', __('“Compare all services” button link', 'sage'), 'url', __('e.g. /gettysburg-web-design-services/#packages', 'sage')],
            ],
            __('Featured work / proof', 'sage') => [
                ['proof_eyebrow', __('Eyebrow', 'sage'), 'text', __('Proof, not promises', 'sage')],
                ['proof_title', __('Heading (before accent)', 'sage'), 'text', __('Gettysburg web design', 'sage')],
                ['proof_accent', __('Accent word', 'sage'), 'text', __('results.', 'sage')],
                ['proof_lede_types', __('Intro under the heading', 'sage'), 'textarea', __('One live concept for each kind of Adams County business — restaurant, inn, shop, tours. Open a demo, then see what the rebuild is built to do.', 'sage')],
                ['proof_client', __('Fallback story · client line (used when no Project post exists)', 'sage'), 'text', __('Bradley Goldsmith Law · Local Launch', 'sage')],
                ['proof_story_title', __('Fallback story · heading', 'sage'), 'text', __('A clearer path from visitor to consultation.', 'sage')],
                ['proof_story_text', __('Fallback story · paragraph', 'sage'), 'textarea', __('A focused five-page rebuild that made the firm easy to reach and easy to trust — shipped in a week.', 'sage')],
                ['proof_location', __('Fallback story · location line', 'sage'), 'text', __('Gettysburg, PA · Adams County', 'sage')],
                ['proof_outcomes_label', __('Label above the three outcome cards', 'sage'), 'text', __('What the site has to do', 'sage')],
                ['proof_points', __('What the work delivers (three cards under the story)', 'sage'), 'repeater', home_proof_point_defaults(), [
                    ['title', __('Point title', 'sage'), 'text'],
                    ['text', __('Point text', 'sage'), 'textarea'],
                ]],
                ['proof_metrics', __('Fallback stats (used when the featured Project has no metric fields)', 'sage'), 'repeater', home_proof_metric_defaults(), [
                    ['v', __('Value', 'sage'), 'text'],
                    ['l', __('Label', 'sage'), 'text'],
                ]],
                ['proof_cta', __('Primary button (no Project post)', 'sage'), 'text', __('See the work', 'sage')],
                ['proof_cta_case', __('Primary button (when a Project post is featured)', 'sage'), 'text', __('See this project', 'sage')],
                ['proof_cta2', __('Secondary button', 'sage'), 'text', __('See all work', 'sage')],
            ],
            __('Rooted / local section', 'sage') => [
                ['rooted_kicker', __('Eyebrow', 'sage'), 'text', __('A local studio, not a remote agency', 'sage')],
                ['rooted_h2', __('Heading (before accent)', 'sage'), 'text', __('Built in', 'sage')],
                ['rooted_h2_accent', __('Accent word', 'sage'), 'text', __('Gettysburg.', 'sage')],
                ['rooted_h2_end', __('Heading (after accent)', 'sage'), 'text', __('Supported here.', 'sage')],
                ['rooted_lede', __('Intro under the heading', 'sage'), 'textarea', __('A family-owned Gettysburg web design studio. Meet in town when that’s easier, and get the same person after launch — not a ticket queue.', 'sage')],
                ['rooted_points', __('Why local matters', 'sage'), 'repeater', home_rooted_point_defaults(), [
                    ['title', __('Point title', 'sage'), 'text'],
                    ['text', __('Point text', 'sage'), 'textarea'],
                ]],
                ['rooted_places_intro', __('Sentence above the region chips (local SEO)', 'sage'), 'textarea', __('Web design for Gettysburg, Adams County, and the ridges around South Mountain.', 'sage')],
                ['rooted_places_label', __('Small label over the region chips', 'sage'), 'text', __('Adams County & nearby', 'sage')],
                ['rooted_regions', __('Region chips (comma-separated)', 'sage'), 'text', 'Gettysburg, Adams County, Cumberland Valley, South Mountain, Michaux'],
                ['rooted_pin', __('Location chip on the photo', 'sage'), 'text', __('Gettysburg, Pennsylvania', 'sage')],
                ['rooted_cta', __('Primary button', 'sage'), 'text', __('More about us', 'sage')],
                ['rooted_cta2', __('Secondary button', 'sage'), 'text', __('Get a quote', 'sage')],
            ],
            __('Testimonial', 'sage') => [
                ['testimonial_quote', __('Quote', 'sage'), 'textarea', __('“Matt made it painless — and now people actually call us from the site.”', 'sage')],
                ['testimonial_who', __('Attribution', 'sage'), 'text', __('— Bradley Goldsmith, Bradley Goldsmith Law', 'sage')],
            ],
            __('Call to action', 'sage') => $cta(
                __('Is your website ready for Gettysburg\'s 2026 visitors?', 'sage'),
                __('A short, no-pressure walkthrough of what a fast, accessible site could do for you.', 'sage'),
                __('Plan my site', 'sage')
            ),
            __('Media & links', 'sage') => [
                ['hero_bg', __('Hero background image', 'sage'), 'image', __('Uses the built-in photo until you choose one.', 'sage')],
                ['cta_button_url', __('Closing CTA button link', 'sage'), 'url', __('e.g. /contact/', 'sage')],
                ['proof_cta_url', __('Featured work · primary button (no Project post)', 'sage'), 'url', '/work/'],
                ['proof_cta2_url', __('Featured work · “See all work” link', 'sage'), 'url', '/work/'],
                ['rooted_cta_url', __('Rooted section · “More about us” link', 'sage'), 'url', '/gettysburg-web-design/'],
                ['rooted_cta2_url', __('Rooted section · “Get a quote” link', 'sage'), 'url', '/contact/'],
            ],
            __('Included in every build', 'sage') => [
                ['incl_eyebrow', __('Eyebrow', 'sage'), 'text', __('No surprises', 'sage')],
                ['incl_h2', __('Heading (before accent)', 'sage'), 'text', __('Included in every', 'sage')],
                ['incl_h2_accent', __('Accent word', 'sage'), 'text', __('build.', 'sage')],
                ['incl_lede', __('Intro under the heading', 'sage'), 'textarea', __('Every Gettysburg web design package — Rescue, Local Launch, or Growth — ships with the same baseline. The price changes with scope. This list does not.', 'sage')],
                ['incl_points', __('What’s included (six items)', 'sage'), 'repeater', svc_incl_point_defaults(), [
                    ['title', __('Item title', 'sage'), 'text'],
                    ['text', __('Item text', 'sage'), 'textarea'],
                ]],
                ['incl_bounds', __('Quieter notes under the grid (not-in-scope + how we work)', 'sage'), 'repeater', svc_bound_defaults(), [
                    ['title', __('Note title', 'sage'), 'text'],
                    ['text', __('Note text', 'sage'), 'textarea'],
                ]],
                ['incl_close', __('Closer under the cards', 'sage'), 'text', __('The full list, with packages, is on the services page.', 'sage')],
                ['incl_cta', __('Primary button', 'sage'), 'text', __('Compare packages', 'sage')],
                ['incl_cta_url', __('Primary button link', 'sage'), 'url', __('e.g. /gettysburg-web-design-services/#included', 'sage')],
                ['incl_cta2', __('Secondary button', 'sage'), 'text', __('Get a quote', 'sage')],
                ['incl_cta2_url', __('Secondary button link', 'sage'), 'url', __('e.g. /contact/', 'sage')],
            ],
            __('Process timeline', 'sage') => [
                ['htime_eyebrow', __('Eyebrow', 'sage'), 'text', __('Groundwork to launch', 'sage')],
                ['htime_title', __('Heading (before accent)', 'sage'), 'text', __('First draft in', 'sage')],
                ['htime_accent', __('Accent word', 'sage'), 'text', __('days,', 'sage')],
                ['htime_title_end', __('Heading (after accent)', 'sage'), 'text', __('not months.', 'sage')],
                ['htime_items', __('Timeline steps', 'sage'), 'repeater', [
                    ['day' => __('Before Day 1', 'sage'), 'title' => __('Fit & scope', 'sage'), 'text' => __('Call, signed scope, deposit.', 'sage')],
                    ['day' => __('Day 1', 'sage'), 'title' => __('One intake form', 'sage'), 'text' => __('You send info + assets.', 'sage')],
                    ['day' => __('Day 3', 'sage'), 'title' => __('Working draft', 'sage'), 'text' => __('Staging site + walkthrough.', 'sage')],
                    ['day' => __('Day 6', 'sage'), 'title' => __('One revision', 'sage'), 'text' => __('Consolidated feedback.', 'sage')],
                    ['day' => __('Days 7–10', 'sage'), 'title' => __('Launch', 'sage'), 'text' => __('Handoff + training.', 'sage')],
                ], [
                    ['day', __('Day label', 'sage'), 'text'],
                    ['title', __('Step title', 'sage'), 'text'],
                    ['text', __('Step text', 'sage'), 'text'],
                ]],
            ],
            __('Towns served', 'sage') => [
                ['htowns_eyebrow', __('Eyebrow', 'sage'), 'text', __('Local, in the real sense', 'sage')],
                ['htowns_title', __('Heading (before accent)', 'sage'), 'text', __('Serving Gettysburg & the', 'sage')],
                ['htowns_accent', __('Accent word', 'sage'), 'text', __('surrounding', 'sage')],
                ['htowns_title_end', __('Heading (after accent)', 'sage'), 'text', __('towns.', 'sage')],
                ['htowns_intro', __('Intro paragraph', 'sage'), 'textarea', __('If your customers are in Adams County or nearby South Central PA, they can find you. Ask about a town-specific page for the places you serve most.', 'sage')],
                ['htowns_list', __('Town chips', 'sage'), 'lines', ['Gettysburg', 'Hanover', 'Littlestown', 'New Oxford', 'McSherrystown', 'Biglerville', 'East Berlin', 'Fairfield', 'Cashtown', 'Aspers', 'Abbottstown', 'Bonneauville', 'Carlisle', 'Chambersburg', 'York']],
            ],
        ],

        'template-about.blade.php' => [
            __('Intro', 'sage') => [
                ['about_kicker', __('Eyebrow', 'sage'), 'text', __('Gettysburg web designer', 'sage')],
                ['about_h1', __('Heading (before accent)', 'sage'), 'text', __('A Gettysburg web designer for', 'sage')],
                ['about_h1_accent', __('Accent phrase', 'sage'), 'text', __('South Central PA.', 'sage')],
                ['about_lede', __('Hero lede (one short paragraph)', 'sage'), 'textarea', __('You work with Matt — a family studio in town — on a fast, accessible WordPress site with local SEO baked in. One fixed price. You keep the keys.', 'sage')],
                ['about_cta', __('Primary button', 'sage'), 'text', __('Get a quote', 'sage')],
                ['about_cta_url', __('Primary button link', 'sage'), 'url', __('e.g. /contact/', 'sage')],
                ['about_cta2', __('Secondary button', 'sage'), 'text', __('See packages', 'sage')],
                ['about_cta2_url', __('Secondary button link', 'sage'), 'url', __('e.g. /gettysburg-web-design-services/#packages', 'sage')],
                ['about_meta', __('Meta line (under the buttons)', 'sage'), 'text', __('Family-owned in Gettysburg · Accessibility-first · Adams County & South Central PA', 'sage')],
                ['about_proof', __('Proof ribbon (four items)', 'sage'), 'repeater', about_proof_defaults(), [
                    ['v', __('Value', 'sage'), 'text'],
                    ['l', __('Label', 'sage'), 'text'],
                ]],
            ],
            __('Why this studio', 'sage') => [
                ['bio_eyebrow', __('Eyebrow', 'sage'), 'text', __('Why this studio', 'sage')],
                ['bio_title', __('Heading (before accent)', 'sage'), 'text', __('Small on purpose.', 'sage')],
                ['bio_accent', __('Accent phrase', 'sage'), 'text', __('Serious about results.', 'sage')],
                ['studio_lede', __('Intro paragraph', 'sage'), 'textarea', __('Ridges & Valleys is a family-owned Gettysburg web design studio. No account managers, no months of meetings — just a site that loads fast, reads clearly, and helps a local business get found and get work.', 'sage')],
                ['about_promises', __('Promise cards', 'sage'), 'repeater', about_promise_defaults(), [
                    ['title', __('Title', 'sage'), 'text'],
                    ['text', __('Text', 'sage'), 'textarea'],
                ]],
            ],
            __('The team', 'sage') => [
                ['about_team_kicker', __('Eyebrow', 'sage'), 'text', __('Who you’ll work with', 'sage')],
                ['about_team_h', __('Heading (before accent)', 'sage'), 'text', __('A family studio in', 'sage')],
                ['about_team_accent', __('Accent phrase', 'sage'), 'text', __('Gettysburg.', 'sage')],
                ['about_team_lede', __('Intro paragraph', 'sage'), 'textarea', __('Small on purpose, so every project gets real attention from the people whose name is on it.', 'sage')],
                ['about_people', __('Team members', 'sage'), 'repeater', about_people_defaults(), [
                    ['name', __('Name', 'sage'), 'text'],
                    ['role', __('Role', 'sage'), 'text'],
                    ['bio', __('Short bio', 'sage'), 'textarea'],
                    ['photo', __('Photo URL (optional)', 'sage'), 'url'],
                ]],
                ['about_team_note', __('Note under the team', 'sage'), 'textarea', __('When a project needs a photographer or a writer, we bring in trusted local specialists — project by project.', 'sage')],
                ['about_invite', __('Partner note (studios & freelancers)', 'sage'), 'textarea', __('Other marketing studios, freelancers, and photographers: we’re glad to collaborate. Come say hello.', 'sage')],
            ],
            __('How we work', 'sage') => [
                ['about_how_kicker', __('Eyebrow', 'sage'), 'text', __('How we work', 'sage')],
                ['about_how_h', __('Heading (before accent)', 'sage'), 'text', __('What it’s like to', 'sage')],
                ['about_how_accent', __('Accent phrase', 'sage'), 'text', __('hire us.', 'sage')],
                ['about_values', __('Value cards', 'sage'), 'repeater', about_value_defaults(), [
                    ['title', __('Card title', 'sage'), 'text'],
                    ['text', __('Card text', 'sage'), 'textarea'],
                ]],
            ],
            __('Rooted locally', 'sage') => [
                ['about_local_kicker', __('Eyebrow', 'sage'), 'text', __('Gettysburg & Adams County', 'sage')],
                ['about_local_h', __('Heading (before accent)', 'sage'), 'text', __('This isn’t a market we', 'sage')],
                ['about_local_accent', __('Accent phrase', 'sage'), 'text', __('picked off a map.', 'sage')],
                ['about_local_lede', __('Story paragraph', 'sage'), 'html', __('<strong>I\'m originally from Pennsylvania.</strong> After fifteen years in Virginia, I wanted to come home — and I chose Gettysburg to raise my family. The studio is staying. We know the summer rush on Steinwehr from a year-round shop in Biglerville, and we build your site for the customers you actually get.', 'sage')],
                ['local_button', __('Button label', 'sage'), 'text', __('Get a quote', 'sage')],
                ['local_highlights', __('Highlight cards', 'sage'), 'repeater', [
                    ['title' => __('In person, not a ticket queue', 'sage'), 'text' => __('A real local you can reach — a call, a screen share, or a meeting around Adams County.', 'sage')],
                    ['title' => __('Built for how people search here', 'sage'), 'text' => __('Google Business Profile, local SEO, and map-ready details for the “near me” searches that matter.', 'sage')],
                    ['title' => __('Visitors and neighbors, covered', 'sage'), 'text' => __('Tuned for the visitor economy and the locals who keep you busy all year.', 'sage')],
                ], [
                    ['title', __('Card title', 'sage'), 'text'],
                    ['text', __('Card text', 'sage'), 'textarea'],
                ]],
            ],
            __('Who we build for', 'sage') => [
                ['about_fit_kicker', __('Eyebrow', 'sage'), 'text', __('Local businesses', 'sage')],
                ['about_fit_h', __('Heading (before accent)', 'sage'), 'text', __('Made for South Central PA', 'sage')],
                ['about_fit_accent', __('Accent phrase', 'sage'), 'text', __('Main Street.', 'sage')],
                ['about_fit_lede', __('Intro paragraph', 'sage'), 'textarea', __('If people nearby search for what you do, we can help them find you, trust you, and reach you.', 'sage')],
                ['serve_items', __('Industry cards', 'sage'), 'repeater', [
                    ['title' => __('Inns & B&Bs', 'sage'), 'text' => __('Direct bookings and a first impression worth the drive.', 'sage')],
                    ['title' => __('Restaurants & taverns', 'sage'), 'text' => __('Menus, hours, and reservations that stay current.', 'sage')],
                    ['title' => __('Farm markets & orchards', 'sage'), 'text' => __('Seasons, hours, and pick-your-own, front and center.', 'sage')],
                    ['title' => __('Tour operators', 'sage'), 'text' => __('Book-and-pay flows for the battlefield and beyond.', 'sage')],
                    ['title' => __('Shops & boutiques', 'sage'), 'text' => __('A downtown storefront that also sells online.', 'sage')],
                    ['title' => __('Trades & contractors', 'sage'), 'text' => __('Quote requests and service areas done right.', 'sage')],
                    ['title' => __('Realtors & agencies', 'sage'), 'text' => __('Listings and lead capture that keep buyers on your site.', 'sage')],
                    ['title' => __('Nonprofits & churches', 'sage'), 'text' => __('Clear, accessible sites that welcome everyone.', 'sage')],
                ], [
                    ['title', __('Industry', 'sage'), 'text'],
                    ['text', __('One-liner', 'sage'), 'textarea'],
                ]],
                ['about_areas_kicker', __('“Areas served” eyebrow', 'sage'), 'text', __('Areas served', 'sage')],
                ['about_towns_intro', __('“Areas served” intro', 'sage'), 'text', __('Based in Gettysburg, working with businesses across Adams County and nearby:', 'sage')],
                ['serve_towns', __('Town chips', 'sage'), 'lines', ['Gettysburg', 'Biglerville', 'Littlestown', 'New Oxford', 'McSherrystown', 'Bonneauville', 'Abbottstown', 'Fairfield', 'Cashtown', 'Arendtsville', 'East Berlin', 'York Springs', 'Aspers', 'Hanover']],
                ['about_towns_note', __('Footnote', 'sage'), 'textarea', __('Plus townships across Adams, York, and Franklin counties. Not next door? Most work happens on a call and a shared screen.', 'sage')],
            ],
            __('What happens next', 'sage') => [
                ['next_eyebrow', __('Eyebrow', 'sage'), 'text', __('What happens next', 'sage')],
                ['next_title', __('Heading (before accent)', 'sage'), 'text', __('From hello to a site', 'sage')],
                ['next_accent', __('Accent phrase', 'sage'), 'text', __('you own.', 'sage')],
                ['about_next', __('Steps', 'sage'), 'repeater', about_next_defaults(), [
                    ['n', __('Number', 'sage'), 'text'],
                    ['title', __('Step title', 'sage'), 'text'],
                    ['text', __('Step text', 'sage'), 'textarea'],
                ]],
            ],
            __('Free tools', 'sage') => [
                ['tools_eyebrow', __('Eyebrow', 'sage'), 'text', __('Try us before you hire us', 'sage')],
                ['tools_title', __('Heading (before accent)', 'sage'), 'text', __('Six free tools.', 'sage')],
                ['tools_accent', __('Accent phrase', 'sage'), 'text', __('No account, no email.', 'sage')],
                ['tools_intro', __('Intro paragraph', 'sage'), 'textarea', __('Put in your address, get a plain-English report, and fix what you like — with us or without us. The easiest way to see how we think before you spend a dollar.', 'sage')],
                ['tools_button', __('Button label', 'sage'), 'text', __('See all six free tools', 'sage')],
            ],
            __('Pricing & reach', 'sage') => [
                ['price_eyebrow', __('Eyebrow', 'sage'), 'text', __('How pricing works', 'sage')],
                ['price_title', __('Heading (before accent)', 'sage'), 'text', __('One fixed price,', 'sage')],
                ['price_accent', __('Accent phrase', 'sage'), 'text', __('agreed up front.', 'sage')],
                ['price_p1', __('Pricing intro', 'sage'), 'html', __('<strong>No hourly meter, no surprise invoices.</strong> You get the number before work starts. If you ask for something new, we agree on that price first, too.', 'sage')],
                ['price_items', __('Package lines', 'sage'), 'repeater', [
                    ['title' => __('Website Rescue', 'sage'), 'text' => __('$950–$1,500 — fix what you already have.', 'sage')],
                    ['title' => __('Local Launch', 'sage'), 'text' => __('$2,750–$3,750 — a new site built to get found locally.', 'sage')],
                    ['title' => __('Growth Site', 'sage'), 'text' => __('$4,500+ — bigger builds with more pages and moving parts.', 'sage')],
                    ['title' => __('Care & Grow', 'sage'), 'text' => __('$179–$349/mo — updates, backups, and steady improvements.', 'sage')],
                ], [
                    ['title', __('Name', 'sage'), 'text'],
                    ['text', __('Price line', 'sage'), 'text'],
                ]],
                ['price_link', __('“See full packages” label', 'sage'), 'text', __('Compare packages', 'sage')],
                ['reach_eyebrow', __('Eyebrow', 'sage'), 'text', __('How to reach us', 'sage')],
                ['reach_title', __('Heading (before accent)', 'sage'), 'text', __('Talk to a', 'sage')],
                ['reach_accent', __('Accent phrase', 'sage'), 'text', __('real person.', 'sage')],
                ['reach_note', __('Note under contact details', 'sage'), 'textarea', __('We work from a home studio and meet at your place of business, so we don’t publish a street address. Call, email, or ask for a quote — you’ll hear back from Matt, not a call center.', 'sage')],
                ['reach_button', __('Button label', 'sage'), 'text', __('Get a free quote', 'sage')],
            ],
            __('Closing CTA', 'sage') => [
                ['about_band_title', __('CTA heading', 'sage'), 'text', __('Ready when you are.', 'sage')],
                ['about_band_sub', __('CTA subtext', 'sage'), 'textarea', __('Tell me about the business. I’ll come back with a clear, fixed-scope idea — usually within a business day.', 'sage')],
                ['about_band_btn', __('CTA button', 'sage'), 'text', __('Get a quote', 'sage')],
            ],
        ],

        'template-services.blade.php' => [
            __('Hero', 'sage') => $hero(
                __('Web design & local SEO · Gettysburg', 'sage'),
                __('Gettysburg web design that earns its', 'sage'),
                __('keep.', 'sage'),
                __('Fixed-scope packages for Gettysburg and Adams County — more calls, clearer hours, a site you own. Honest pricing, no jargon.', 'sage')
            ),
            __('Hero proof ribbon', 'sage') => [
                ['svc_proof', __('Stats (four items)', 'sage'), 'repeater', svc_proof_defaults(), [
                    ['v', __('Value', 'sage'), 'text'],
                    ['l', __('Label', 'sage'), 'text'],
                ]],
            ],
            __('Before pricing', 'sage') => [
                ['svcvalue_eyebrow', __('Eyebrow', 'sage'), 'text', __('Before the pricing', 'sage')],
                ['svcvalue_title', __('Heading (before accent)', 'sage'), 'text', __('What you\'re really', 'sage')],
                ['svcvalue_accent', __('Accent phrase', 'sage'), 'text', __('paying for.', 'sage')],
                ['svcvalue_intro', __('Intro paragraph', 'sage'), 'textarea', __('Every package below buys the same three things, whatever your budget. The price changes with scope — the standards never do.', 'sage')],
                ['svcvalue_items', __('Reasons (shown as a compact list)', 'sage'), 'repeater', svcvalue_item_defaults(), [
                    ['kicker', __('Kicker (optional)', 'sage'), 'text'],
                    ['title', __('Card title', 'sage'), 'text'],
                    ['text', __('Card text', 'sage'), 'textarea'],
                ]],
            ],
            __('Packages', 'sage') => [
                ['plans_eyebrow', __('Eyebrow', 'sage'), 'text', __('Gettysburg web design packages', 'sage')],
                ['plans_title', __('Heading (before accent)', 'sage'), 'text', __('Pick the plan that fits', 'sage')],
                ['plans_accent', __('Accent phrase', 'sage'), 'text', __('where you are.', 'sage')],
                ['plans_intro', __('Intro paragraph', 'sage'), 'textarea', __('Every Gettysburg web design package is one fixed price, agreed up front — no hourly meter, no surprise invoices. Rescue a site you already have, launch a new one for Adams County, or keep it growing after. Not sure which fits? Tell me about your business and I’ll point you to the right one.', 'sage')],
                ['packages_btn', __('Default button label', 'sage'), 'text', __('Get a quote', 'sage')],
                ['packages_items', __('Package cards', 'sage'), 'repeater', svc_package_defaults(), [
                    ['name', __('Package name', 'sage'), 'text'],
                    ['price', __('Price', 'sage'), 'text'],
                    ['flag', __('Badge (e.g. “Most popular” — leave blank for none)', 'sage'), 'text'],
                    ['kind', __('Layout', 'sage'), 'select', [
                        'project' => __('Project card (in the comparison grid)', 'sage'),
                        'care'    => __('Care plan (full-width bar under the grid)', 'sage'),
                    ]],
                    ['for', __('Best for', 'sage'), 'text', null, 'wide'],
                    ['desc', __('One-line outcome', 'sage'), 'textarea'],
                    ['features', __('What’s included (one per line — keep to three)', 'sage'), 'lines'],
                    ['cta', __('Button label (blank = default button above)', 'sage'), 'text', null, 'wide'],
                    ['url', __('Button link (blank = contact form for this package)', 'sage'), 'url', null, 'wide'],
                ]],
            ],
            __('What every build includes', 'sage') => [
                ['svc_incl_eyebrow', __('Eyebrow', 'sage'), 'text', __('Same baseline, every package', 'sage')],
                ['svc_incl_title', __('Heading (before accent)', 'sage'), 'text', __('What every build', 'sage')],
                ['svc_incl_accent', __('Accent word', 'sage'), 'text', __('includes.', 'sage')],
                ['svc_incl_intro', __('Intro paragraph', 'sage'), 'textarea', __('Rescue, Local Launch, or Growth Site — the price changes with scope. Accessibility, local SEO, and a site you own do not.', 'sage')],
                ['svc_incl_points', __('Included items', 'sage'), 'repeater', svc_incl_point_defaults(), [
                    ['title', __('Item title', 'sage'), 'text'],
                    ['text', __('Item text', 'sage'), 'textarea'],
                ]],
                ['svc_bound_items', __('Boundary cards (not in scope / how we work)', 'sage'), 'repeater', svc_bound_defaults(), [
                    ['title', __('Card title', 'sage'), 'text'],
                    ['text', __('Card text', 'sage'), 'textarea'],
                ]],
            ],
            __('Local SEO section', 'sage') => [
                ['seo_eyebrow', __('Eyebrow', 'sage'), 'text', __('Get found in Gettysburg', 'sage')],
                ['seo_title', __('Heading (before accent)', 'sage'), 'text', __('Local SEO that puts you', 'sage')],
                ['seo_accent', __('Accent phrase', 'sage'), 'text', __('on the map.', 'sage')],
                ['svc_seo_intro', __('Intro paragraph', 'sage'), 'html', __('<strong>Local SEO decides whether they find you.</strong> When someone in Adams County searches “web designer near me” or “best breakfast in Gettysburg,” you either show up — or the shop three listings up does.', 'sage')],
                ['svc_seo_points', __('Local SEO items', 'sage'), 'repeater', svc_seo_point_defaults(), [
                    ['title', __('Item title', 'sage'), 'text'],
                    ['text', __('Item text', 'sage'), 'textarea'],
                ]],
                ['svc_seo_bounds', __('Where it sits (baked in / already have a site)', 'sage'), 'repeater', svc_seo_bound_defaults(), [
                    ['title', __('Card title', 'sage'), 'text'],
                    ['text', __('Card text', 'sage'), 'textarea'],
                ]],
            ],
            __('Process (services)', 'sage') => [
                ['sproc_eyebrow', __('Eyebrow', 'sage'), 'text', __('Groundwork to launch', 'sage')],
                ['sproc_title', __('Heading (before accent)', 'sage'), 'text', __('Fast comes from a', 'sage')],
                ['sproc_accent', __('Accent word', 'sage'), 'text', __('process,', 'sage')],
                ['sproc_title_end', __('Heading (after accent)', 'sage'), 'text', __('not corner-cutting.', 'sage')],
                ['sproc_items', __('Timeline steps', 'sage'), 'repeater', [
                    ['day' => __('Day 1', 'sage'), 'title' => __('Compile the brief', 'sage'), 'text' => __('One structured intake form + your assets become the core site brief.', 'sage')],
                    ['day' => __('Day 2–3', 'sage'), 'title' => __('Draft & build', 'sage'), 'text' => __('Sitemap, style direction, first copy, and a working staging site.', 'sage')],
                    ['day' => __('Day 4–5', 'sage'), 'title' => __('Human QA', 'sage'), 'text' => __('Fact-check, mobile, accessibility, SEO, performance, forms.', 'sage')],
                    ['day' => __('Day 6', 'sage'), 'title' => __('One revision', 'sage'), 'text' => __('Consolidated feedback, applied in a single round.', 'sage')],
                    ['day' => __('Day 7–10', 'sage'), 'title' => __('Launch', 'sage'), 'text' => __('Domain, analytics, search settings, backups, and handoff.', 'sage')],
                    ['day' => __('Day 30', 'sage'), 'title' => __('Warranty', 'sage'), 'text' => __('Workmanship warranty, plus an optional care plan.', 'sage')],
                ], [
                    ['day', __('Day label', 'sage'), 'text'],
                    ['title', __('Step title', 'sage'), 'text'],
                    ['text', __('Step text', 'sage'), 'text'],
                ]],
            ],
            __('AI-assisted split', 'sage') => [
                ['aisplit_eyebrow', __('Eyebrow', 'sage'), 'text', __('AI-assisted, human-finished', 'sage')],
                ['aisplit_title', __('Heading (before accent)', 'sage'), 'text', __('The', 'sage')],
                ['aisplit_accent', __('Accent word', 'sage'), 'text', __('honest', 'sage')],
                ['aisplit_title_end', __('Heading (after accent)', 'sage'), 'text', __('split.', 'sage')],
                ['aisplit_items', __('The two cards', 'sage'), 'repeater', [
                    ['title' => __('AI handles the blank page', 'sage'), 'text' => __('Summarizes intake, proposes a sitemap, drafts page copy and metadata, suggests image concepts and alt text, and builds QA checklists.', 'sage')],
                    ['title' => __('Matt handles the judgment', 'sage'), 'text' => __('Confirms the real business goal, verifies every fact, rewrites the voice, chooses search intent, tests everything, and approves what goes public.', 'sage')],
                ], [
                    ['title', __('Card title', 'sage'), 'text'],
                    ['text', __('Card text', 'sage'), 'textarea'],
                ]],
                ['aisplit_note', __('Disclosure note', 'sage'), 'textarea', __('Your agreement discloses that AI may assist with drafts while all work is reviewed. Confidential information is never placed into a third-party model without permission.', 'sage')],
            ],
            __('After launch', 'sage') => [
                ['after_kicker', __('Eyebrow', 'sage'), 'text', __('Website care · Gettysburg', 'sage')],
                ['after_title', __('Heading (before accent)', 'sage'), 'text', __('Launch day isn\'t', 'sage')],
                ['after_accent', __('Accent word', 'sage'), 'text', __('goodbye.', 'sage')],
                ['after_lede', __('Intro paragraph', 'sage'), 'textarea', __('Every Gettysburg website I build includes a 30-day workmanship warranty and a training handoff. Need hosting, backups, and small edits after that? Care & Grow is optional — and you still own the site if you cancel.', 'sage')],
                ['after_points', __('What’s included (kickers + titles)', 'sage'), 'repeater', svc_after_point_defaults(), [
                    ['kicker', __('Kicker', 'sage'), 'text'],
                    ['title', __('Heading', 'sage'), 'text'],
                    ['text', __('Text', 'sage'), 'textarea'],
                ]],
                ['after_care_kicker', __('Care panel kicker', 'sage'), 'text', __('Optional', 'sage')],
                ['after_care_title', __('Care panel heading', 'sage'), 'text', __('Care & Grow — website care from $179/mo', 'sage')],
                ['after_care_text', __('Care panel text', 'sage'), 'textarea', __('Hosting, backups, security, monthly edits, and a look at search. Cancel anytime. You keep the site.', 'sage')],
                ['after_care_btn', __('Care panel button', 'sage'), 'text', __('See Care & Grow', 'sage')],
            ],
            __('Helpful to know (FAQ)', 'sage') => [
                ['sfaq_eyebrow', __('Eyebrow', 'sage'), 'text', __('Before you pick a package', 'sage')],
                ['sfaq_title', __('Heading (before accent)', 'sage'), 'text', __('Answers before you', 'sage')],
                ['sfaq_accent', __('Accent phrase', 'sage'), 'text', __('even ask.', 'sage')],
                ['sfaq_lede', __('Intro paragraph', 'sage'), 'textarea', __('These are the things Gettysburg owners actually ask before they hire me. Open the one that’s on your mind — and if yours isn’t here, write. I answer myself.', 'sage')],
                ['sfaq_qs', __('Questions & answers (buying order)', 'sage'), 'repeater', svc_faq_item_defaults(), [
                    ['q', __('Question', 'sage'), 'text'],
                    ['a', __('Answer', 'sage'), 'textarea'],
                ]],
                ['sfaq_close', __('Invite under the answers', 'sage'), 'text', __('Still chewing on something? Ask. You’ll hear back from me — not a form letter.', 'sage')],
                ['sfaq_close_btn', __('Invite button', 'sage'), 'text', __('Ask me', 'sage')],
                ['sfaq_more', __('Link to the full FAQ page', 'sage'), 'text', __('More questions, answered', 'sage')],
                ['sfaq_more_url', __('Full FAQ page link', 'sage'), 'url', __('e.g. /faq/', 'sage')],
            ],
            __('Founding offer', 'sage') => [
                ['founding_eyebrow', __('Eyebrow', 'sage'), 'text', __('Founding offer · only 3 spots', 'sage')],
                ['founding_title', __('Heading', 'sage'), 'text', __('Founding Local Launch —', 'sage')],
                ['founding_accent', __('Accent (price)', 'sage'), 'text', __('$2,250.', 'sage')],
                ['founding_body', __('Paragraph', 'sage'), 'textarea', __('The first three local clients get the full Local Launch at a founding rate — in exchange for a case study and an honest testimonial after a successful launch.', 'sage')],
                ['founding_list', __('What\'s included (one per line)', 'sage'), 'lines', [__('Five pages', 'sage'), __('Contact form', 'sage'), __('Local SEO', 'sage'), __('Analytics', 'sage'), __('Accessibility review', 'sage'), __('One revision', 'sage'), __('Launch + training video', 'sage'), __('30 days of support', 'sage')]],
                ['founding_after', __('Line under the checklist', 'sage'), 'text', __('After that, it moves to the normal $3,250+ starting price.', 'sage')],
                ['founding_button', __('Button label', 'sage'), 'text', __('Claim a founding spot', 'sage')],
            ],
            __('Media & links', 'sage') => [
                ['hero_bg', __('Hero background image', 'sage'), 'image', __('Built-in until you choose one.', 'sage')],
            ],
        ],

        'template-work.blade.php' => [
            __('Hero', 'sage') => [
                ['work_kicker', __('Eyebrow', 'sage'), 'text', __('Gettysburg web design work', 'sage')],
                ['work_h1', __('Heading (before accent)', 'sage'), 'text', __('Gettysburg web design you can', 'sage')],
                ['work_h1_accent', __('Accent phrase', 'sage'), 'text', __('actually click.', 'sage')],
                ['work_lede', __('Hero lede (one short paragraph)', 'sage'), 'textarea', __('Live concept sites for restaurants, inns, shops, and tours around Gettysburg and Adams County. Filter by industry, click the working demo, then get a quote.', 'sage')],
                ['work_cta', __('Primary button', 'sage'), 'text', __('Get a quote', 'sage')],
                ['work_cta_url', __('Primary button link', 'sage'), 'url', __('e.g. /contact/', 'sage')],
                ['work_cta2', __('Secondary button', 'sage'), 'text', __('Browse the work', 'sage')],
                ['work_note', __('Meta line (under the buttons)', 'sage'), 'text', __('Honest concepts · Live demos · No fake clients', 'sage')],
                ['work_proof', __('Proof ribbon (four items)', 'sage'), 'repeater', work_proof_defaults(), [
                    ['v', __('Value', 'sage'), 'text'],
                    ['l', __('Label', 'sage'), 'text'],
                ]],
            ],
            __('Media & links', 'sage') => [
                ['hero_bg', __('Hero background image', 'sage'), 'image', __('Built-in until you choose one.', 'sage')],
            ],
            __('Why these concepts', 'sage') => [
                ['wwhy_eyebrow', __('Eyebrow', 'sage'), 'text', __('How to use this page', 'sage')],
                ['wwhy_title', __('Heading (before accent)', 'sage'), 'text', __('Pick your industry.', 'sage')],
                ['wwhy_accent', __('Accent phrase', 'sage'), 'text', __('Click the demo.', 'sage')],
                ['wwhy_intro', __('Intro paragraph', 'sage'), 'html', __('<strong>I built these myself — one live site per industry.</strong> Filter to a business like yours, click around the working demo, then get a quote if it feels like a fit. No fake clients, no borrowed templates, no stock mockups dressed up as case studies.', 'sage')],
                ['wwhy_jump', __('Jump link (to the grid)', 'sage'), 'text', __('Browse the concepts', 'sage')],
                ['wwhy_cta', __('Quote button', 'sage'), 'text', __('Get a quote', 'sage')],
                ['wwhy_items', __('Three steps (shown as a compact list)', 'sage'), 'repeater', work_why_item_defaults(), [
                    ['kicker', __('Kicker', 'sage'), 'text'],
                    ['title', __('Card title', 'sage'), 'text'],
                    ['text', __('Card text', 'sage'), 'textarea'],
                ]],
            ],
            __('Case studies header', 'sage') => [
                ['wcs_eyebrow', __('Eyebrow', 'sage'), 'text', __('The work', 'sage')],
                ['wcs_title', __('Heading (before accent)', 'sage'), 'text', __('Concepts for', 'sage')],
                ['wcs_accent', __('Accent phrase', 'sage'), 'text', __('Adams County businesses.', 'sage')],
                ['wcs_intro', __('Intro paragraph', 'sage'), 'textarea', __('Filter by industry, open a live demo, then read the problem → approach → result. Anything marked “Concept” is my own self-initiated demo, not a client project — so you can click the real site, not a screenshot.', 'sage')],
                ['wcs_cats_label', __('Filter label', 'sage'), 'text', __('Show me', 'sage')],
                ['wcs_honest', __('Honesty line (under the filters)', 'sage'), 'textarea', __('Concept means I built it to show my approach — not a paid client. That’s the point: you can click the working site.', 'sage')],
                ['wcs_hint', __('Note under the grid', 'sage'), 'textarea', __('Each write-up follows Problem → Approach → Result. On concepts those goals are illustrative; on client projects they become measured results.', 'sage')],
                ['wcs_cta_kicker', __('After-grid CTA eyebrow', 'sage'), 'text', __('Your turn', 'sage')],
                ['wcs_cta_h', __('After-grid CTA heading', 'sage'), 'text', __('See something close to your business?', 'sage')],
                ['wcs_cta_p', __('After-grid CTA paragraph', 'sage'), 'textarea', __('Tell me what you do in Adams County. I’ll point you to the closest concept and a fixed price — usually a reply within a business day.', 'sage')],
                ['wcs_cta_btn', __('After-grid CTA button', 'sage'), 'text', __('Get a quote', 'sage')],
            ],
            __('Don\'t see your business', 'sage') => [
                ['morebiz_eyebrow', __('Eyebrow', 'sage'), 'text', __('Your business next', 'sage')],
                ['morebiz_title', __('Heading (before accent)', 'sage'), 'text', __('Don\'t see your', 'sage')],
                ['morebiz_accent', __('Accent phrase', 'sage'), 'text', __('kind of business?', 'sage')],
                ['morebiz_intro', __('Intro paragraph', 'sage'), 'html', __('<strong>The approach works for almost any local business.</strong> The concepts above are just a starting set. If people around Gettysburg search for what you do, I can build you a site that helps them find you, trust you, and reach you. A few of the others I love building for:', 'sage')],
                ['morebiz_list', __('Business-type chips', 'sage'), 'lines', [__('Tradespeople & contractors', 'sage'), __('Salons, barbers & spas', 'sage'), __('Dentists & medical', 'sage'), __('Auto shops & repair', 'sage'), __('Fitness & yoga studios', 'sage'), __('Landscaping & lawn care', 'sage'), __('Lawyers & accountants', 'sage'), __('Cafés & bakeries', 'sage'), __('Event & wedding venues', 'sage'), __('Photographers & creatives', 'sage'), __('Cleaning & home services', 'sage'), __('Nonprofits & churches', 'sage')]],
                ['morebiz_panel_eyebrow', __('Panel eyebrow', 'sage'), 'text', __('The foundation every site gets', 'sage')],
                ['morebiz_panel_h', __('Panel heading', 'sage'), 'text', __('Whatever you do, we start from the same solid ground.', 'sage')],
                ['morebiz_panel_btn', __('Panel button label', 'sage'), 'text', __('Get a free quote', 'sage')],
                ['morebiz_panel_fine', __('Panel fine print', 'sage'), 'text', __('Tell me about your business — no jargon, no pressure, and usually a reply within a business day.', 'sage')],
                ['morebiz_check', __('Foundation checklist', 'sage'), 'lines', [__('Local SEO and a properly set-up Google Business Profile', 'sage'), __('A fast, mobile-first design that works on any phone', 'sage'), __('WCAG-minded accessibility, so everyone can use it', 'sage'), __('Full ownership — your domain, hosting, and content', 'sage'), __('A fixed price agreed up front, with no surprises', 'sage'), __('Real training and a plain-English handoff', 'sage')]],
            ],
            __('How it goes', 'sage') => [
                ['flow_eyebrow', __('Eyebrow', 'sage'), 'text', __('How it goes', 'sage')],
                ['flow_title', __('Heading (before accent)', 'sage'), 'text', __('First call to launch in about', 'sage')],
                ['flow_accent', __('Accent phrase', 'sage'), 'text', __('a week.', 'sage')],
                ['flow_intro', __('Intro paragraph', 'sage'), 'textarea', __('Most small-business sites stall for months because the process is vague. Mine isn\'t. Here\'s exactly how a build goes — and where you\'re involved so it sounds like you, not a template.', 'sage')],
                ['flow_items', __('Steps (numbered automatically)', 'sage'), 'repeater', [
                    ['title' => __('Talk it through', 'sage'), 'text' => __('A short, no-pressure call about your business, your customers, and what a win looks like. You\'ll leave knowing exactly what I\'d do and what it costs — fixed, in writing.', 'sage')],
                    ['title' => __('I build the draft', 'sage'), 'text' => __('I move fast on structure and first-draft copy, then do the judgment work by hand — the facts, the voice, the accessibility, the details that make it yours.', 'sage')],
                    ['title' => __('You review', 'sage'), 'text' => __('You see the real site early, on your phone, and tell me what to sharpen. Clear rounds of feedback, not endless ping-pong.', 'sage')],
                    ['title' => __('We launch', 'sage'), 'text' => __('I handle the technical launch — domain, SSL, speed, search setup — so it goes live clean and gets found from day one.', 'sage')],
                    ['title' => __('You\'re trained', 'sage'), 'text' => __('A short walkthrough video and a real handoff, so you can update hours, prices, and photos yourself. I\'m still a call away when you want a hand.', 'sage')],
                ], [
                    ['title', __('Step title', 'sage'), 'text'],
                    ['text', __('Step text', 'sage'), 'textarea'],
                ]],
                ['flow_outcome_eyebrow', __('Outcome eyebrow', 'sage'), 'text', __('What you walk away with', 'sage')],
                ['flow_deliverables', __('Deliverables checklist', 'sage'), 'lines', [__('A fast, mobile-first website that reads clearly and loads quickly', 'sage'), __('Google Business Profile and local SEO set up so you get found', 'sage'), __('WCAG-minded, accessible pages that work for everyone', 'sage'), __('Full ownership of your domain, hosting, and content', 'sage'), __('A training video and plain-English handoff — no lock-in', 'sage')]],
                ['flow_reassure', __('Reassurance line', 'sage'), 'text', __('Fixed price agreed up front · Built here in Adams County · A real person answers when you call.', 'sage')],
            ],
            __('The design craft', 'sage') => [
                ['craft_eyebrow', __('Eyebrow', 'sage'), 'text', __('Under the hood', 'sage')],
                ['craft_title', __('Heading (before accent)', 'sage'), 'text', __('What actually goes into', 'sage')],
                ['craft_accent', __('Accent phrase', 'sage'), 'text', __('the design.', 'sage')],
                ['craft_intro', __('Intro paragraph', 'sage'), 'textarea', __('A good small-business site isn\'t a template with your logo dropped in. Every project moves through the same design craft — so the result fits your business, your customers, and the way people actually find you.', 'sage')],
                ['craft_items', __('Craft cards (numbered automatically)', 'sage'), 'repeater', [
                    ['title' => __('Listen & research', 'sage'), 'text' => __('I start with your customers, not your competitors\' websites — who they are, what they\'re trying to do, and the words they actually use. That research shapes the structure before a single pixel is placed.', 'sage')],
                    ['title' => __('Structure & wireframe', 'sage'), 'text' => __('I map the path from “never heard of you” to “ready to call” and lay out each page around that one job. Fewer dead ends, clearer next steps, nothing decorative getting in the way.', 'sage')],
                    ['title' => __('Voice & words', 'sage'), 'text' => __('I draft copy that sounds like you — plain, warm, and specific — then refine it by hand. No jargon, no filler, no “we are a premier provider of solutions.”', 'sage')],
                    ['title' => __('Look & feel', 'sage'), 'text' => __('Type, color, spacing, and real photography chosen to feel like your business and your place — not a stock kit. Design that earns trust in the first three seconds.', 'sage')],
                    ['title' => __('Build for speed & access', 'sage'), 'text' => __('Hand-built, mobile-first pages that load fast on a phone on Main Street and meet accessibility standards, so every visitor — and Google — has a good experience.', 'sage')],
                    ['title' => __('Test, launch & hand off', 'sage'), 'text' => __('Cross-device testing, a clean technical launch, and a real training handoff so you can run it yourself. The site is yours, top to bottom.', 'sage')],
                ], [
                    ['title', __('Card title', 'sage'), 'text'],
                    ['text', __('Card text', 'sage'), 'textarea'],
                ]],
                ['craft_close', __('Closing paragraph', 'sage'), 'textarea', __('Working together is refreshingly low-drama: one point of contact (me), fixed rounds of feedback instead of endless revisions, and plain-English updates so you always know where things stand. You\'re never left guessing, and you\'re never locked in.', 'sage')],
            ],
            __('Areas served (work)', 'sage') => [
                ['wareas_eyebrow', __('Eyebrow', 'sage'), 'text', __('Serving South Central PA', 'sage')],
                ['wareas_title', __('Heading (line 1)', 'sage'), 'text', __('A web designer', 'sage')],
                ['wareas_accent', __('Heading (accent line 2)', 'sage'), 'text', __('based in Gettysburg.', 'sage')],
                ['wareas_lede', __('Lede paragraph', 'sage'), 'textarea', __('A local, independent web designer working with small businesses across Gettysburg and Adams County — restaurants, inns, shops, tour companies, tradespeople, realtors, and nonprofits. Being nearby means real meetings, real accountability, and a site built by someone who knows the towns your customers come from.', 'sage')],
                ['wareas_towns', __('Town chips', 'sage'), 'lines', ['Gettysburg', 'Biglerville', 'Littlestown', 'New Oxford', 'McSherrystown', 'Bonneauville', 'East Berlin', 'Abbottstown', 'Fairfield', 'Cashtown', 'Arendtsville', 'Aspers', 'Bendersville', 'Orrtanna', 'Hanover', 'York Springs']],
                ['wareas_note', __('Note under the towns', 'sage'), 'textarea', __('Plus the surrounding townships — Cumberland, Straban, Mount Joy, Franklin, Menallen, and Hamiltonban — and small businesses throughout Adams, York, and Franklin counties. Not in the neighborhood? Most of my work happens over a call and a shared screen, so distance is rarely a dealbreaker.', 'sage')],
                ['wareas_tagline', __('Tagline parts (one per line, joined with ·)', 'sage'), 'lines', [__('Local', 'sage'), __('Independent', 'sage'), __('Fixed price', 'sage'), __('Built for how South Central PA searches', 'sage')]],
            ],
        ],

        'template-faq.blade.php' => [
            __('Hero', 'sage') => $hero(
                __('FAQ', 'sage'),
                __('Questions,', 'sage'),
                __('answered.', 'sage'),
                __('What local owners ask before we start. Don\'t see yours? Ask.', 'sage')
            ),
            __('Process section', 'sage') => [
                ['fproc_eyebrow', __('Eyebrow', 'sage'), 'text', __('How it works', 'sage')],
                ['fproc_title', __('Heading (before accent)', 'sage'), 'text', __('Your project,', 'sage')],
                ['fproc_accent', __('Accent phrase', 'sage'), 'text', __('step by step.', 'sage')],
                ['fproc_intro', __('Intro paragraph', 'sage'), 'textarea', __('No mystery, no months of silence. Here\'s the exact path every Ridges & Valleys project follows — from the first call in Gettysburg to a site that\'s live and getting found.', 'sage')],
                ['fproc_items', __('Process steps (numbered automatically)', 'sage'), 'repeater', [
                    ['title' => __('Discover', 'sage'), 'text' => __('A short, no-pressure call to learn your business, your customers, and what a win looks like.', 'sage')],
                    ['title' => __('Plan', 'sage'), 'text' => __('I map your pages and the path from stranger to phone call — before any design begins.', 'sage')],
                    ['title' => __('Design & write', 'sage'), 'text' => __('Visuals and copy that sound like you: warm, clear, local, and built to earn trust fast.', 'sage')],
                    ['title' => __('Build', 'sage'), 'text' => __('Hand-coded, fast, mobile-first, and accessible from the very first line of markup.', 'sage')],
                    ['title' => __('Launch', 'sage'), 'text' => __('Domain, speed, security, and local SEO — your site goes live clean and ready to be found.', 'sage')],
                    ['title' => __('Support', 'sage'), 'text' => __('Training, a 30-day workmanship warranty, and a real person a phone call away.', 'sage')],
                ], [
                    ['title', __('Step title', 'sage'), 'text'],
                    ['text', __('Step text', 'sage'), 'textarea'],
                ]],
                ['fproc_note', __('Note under the steps', 'sage'), 'text', __('Fixed price agreed up front · One clear round of changes · You own everything at the end.', 'sage')],
            ],
            __('FAQ list header', 'sage') => [
                ['faqs_eyebrow', __('Eyebrow', 'sage'), 'text', __('Straight answers', 'sage')],
                ['faqs_title', __('Heading (before accent)', 'sage'), 'text', __('Everything owners ask,', 'sage')],
                ['faqs_accent', __('Accent phrase', 'sage'), 'text', __('answered in full.', 'sage')],
                ['faqs_intro', __('Intro paragraph', 'sage'), 'textarea', __('A local, independent web designer based in Gettysburg — here\'s the honest detail on cost, timing, local SEO, ownership, and how I work with businesses across Adams County and South Central PA.', 'sage')],
            ],
            __('FAQ items', 'sage') => [
                ['faq_items', __('Questions (set the Category to group them)', 'sage'), 'repeater', [
                    ['cat' => __('Pricing & timeline', 'sage'), 'q' => __('How much does a website cost?', 'sage'), 'a' => __('Most local sites land between a few hundred and a few thousand dollars, depending on pages and features. Every package is fixed-price, so you know the number before we start — try the estimator on the Tools page for a ballpark.', 'sage')],
                    ['cat' => __('Pricing & timeline', 'sage'), 'q' => __('How long does it take?', 'sage'), 'a' => __('A working first draft in about three business days, and most sites launch in 7–10 days. Speed comes from a disciplined process, not corner-cutting.', 'sage')],
                    ['cat' => __('Pricing & timeline', 'sage'), 'q' => __('How does payment work?', 'sage'), 'a' => __('A fixed price agreed up front, a deposit to start, and the balance before launch. No hourly meter, no surprise invoices.', 'sage')],
                    ['cat' => __('Working together', 'sage'), 'q' => __('Do I need to know anything technical?', 'sage'), 'a' => __('No. I handle the build, hosting setup, and launch. If you\'d like to make edits yourself, I\'ll show you the few simple things you\'ll actually use.', 'sage')],
                    ['cat' => __('Working together', 'sage'), 'q' => __('What do you need from me to start?', 'sage'), 'a' => __('Not much: a bit about your business and customers, your logo and any photos you have, and your hours and services. I turn that into a first draft and chase down the rest as we go.', 'sage')],
                    ['cat' => __('Working together', 'sage'), 'q' => __('How many rounds of changes do I get?', 'sage'), 'a' => __('One consolidated review round is built into every project — you see the real site early, gather your feedback, and I apply it in a single clean pass. A care plan covers changes after launch.', 'sage')],
                    ['cat' => __('Working together', 'sage'), 'q' => __('Is this just a "cheap AI website"?', 'sage'), 'a' => __('No. AI helps with research and first drafts to save time, but every design decision, every word, and every accessibility detail is reviewed and finished by a person — me.', 'sage')],
                    ['cat' => __('Getting found in Gettysburg', 'sage'), 'q' => __('Do you do local SEO?', 'sage'), 'a' => __('Yes — it\'s built into every site. Google Business Profile setup, on-page SEO, LocalBusiness structured data, and location pages so nearby customers across Gettysburg and Adams County find you first.', 'sage')],
                    ['cat' => __('Getting found in Gettysburg', 'sage'), 'q' => __('Will my business show up on Google Maps?', 'sage'), 'a' => __('That\'s the goal. I set up and optimize your Google Business Profile and keep your name, address, and phone consistent everywhere, so you\'re a strong candidate for the local "map pack" — the top three results people tap first.', 'sage')],
                    ['cat' => __('Getting found in Gettysburg', 'sage'), 'q' => __('Do you build pages for the towns I serve?', 'sage'), 'a' => __('When it helps, yes. If you serve more than one town — say Gettysburg, Biglerville, Littlestown, New Oxford, and Hanover — I can build clear, useful location pages so you can rank in each one, not just your home base.', 'sage')],
                    ['cat' => __('Getting found in Gettysburg', 'sage'), 'q' => __('Can you set up my Google Business Profile?', 'sage'), 'a' => __('Absolutely. I\'ll claim or clean up your profile, choose the right categories, add hours, photos, and services, and point it at your new site — often the single biggest local-search win for a small business.', 'sage')],
                    ['cat' => __('Your site, ownership & support', 'sage'), 'q' => __('Do I own my site?', 'sage'), 'a' => __('Always. You own the domain, the hosting account, and the site itself. No lock-in, and you can cancel a care plan anytime and keep everything.', 'sage')],
                    ['cat' => __('Your site, ownership & support', 'sage'), 'q' => __('Do you handle hosting and domains?', 'sage'), 'a' => __('Yes — I set both up in your name and can manage them for you, or hand you the keys. Either way it\'s yours, and you\'re never locked in.', 'sage')],
                    ['cat' => __('Your site, ownership & support', 'sage'), 'q' => __('What if I already have a website?', 'sage'), 'a' => __('Then Website Rescue may be all you need — I can repair, restyle, and speed up what you have instead of starting over.', 'sage')],
                    ['cat' => __('Your site, ownership & support', 'sage'), 'q' => __('What happens after launch?', 'sage'), 'a' => __('You get a 30-day workmanship warranty, a training video, and the option of a Care & Grow plan for updates, backups, and edits. Or take the keys and run it yourself.', 'sage')],
                    ['cat' => __('Your site, ownership & support', 'sage'), 'q' => __('Are the sites accessible?', 'sage'), 'a' => __('Accessibility-first is the standard here — I build to WCAG 2.1 AA and check every page before launch, so the site works for everyone (and tends to rank better, too).', 'sage')],
                    ['cat' => __('Local & logistics', 'sage'), 'q' => __('Do you only work with Gettysburg businesses?', 'sage'), 'a' => __('Gettysburg and Adams County are home base, but I work with small businesses throughout South Central PA — York, Franklin, and neighboring counties included. Farther away is fine too; most of the work happens over a call and a shared screen.', 'sage')],
                    ['cat' => __('Local & logistics', 'sage'), 'q' => __('Do we have to meet in person?', 'sage'), 'a' => __('Only if you want to. Being local means I can meet face-to-face around Gettysburg when it helps — but plenty of projects run entirely over calls, email, and a shared screen. Whatever\'s easiest for you.', 'sage')],
                ], [
                    ['cat', __('Category', 'sage'), 'text'],
                    ['q', __('Question', 'sage'), 'text'],
                    ['a', __('Answer', 'sage'), 'textarea'],
                ]],
            ],
        ],

        'template-contact.blade.php' => [
            __('Hero', 'sage') => [
                ['cnt_kicker', __('Eyebrow', 'sage'), 'text', __('Gettysburg web design · let’s talk', 'sage')],
                ['cnt_h1', __('Heading (before accent)', 'sage'), 'text', __('Tell me about the', 'sage')],
                ['cnt_h1_accent', __('Accent phrase', 'sage'), 'text', __('business.', 'sage')],
                ['cnt_lede', __('Hero lede (one short paragraph)', 'sage'), 'textarea', __('A new site, a second look at the one you have, or just hello — I’ll read it and reply. Usually within a business day. No jargon, no pressure.', 'sage')],
                ['cnt_cta', __('Primary button', 'sage'), 'text', __('Request a quote', 'sage')],
                ['cnt_cta2', __('Secondary button', 'sage'), 'text', __('Or email me', 'sage')],
                ['cnt_note', __('Meta line (under the buttons)', 'sage'), 'text', __('A real person answers · Fixed price · You own the site', 'sage')],
                ['cnt_proof', __('Proof ribbon (four items)', 'sage'), 'repeater', contact_proof_defaults(), [
                    ['v', __('Value', 'sage'), 'text'],
                    ['l', __('Label', 'sage'), 'text'],
                ]],
            ],
            __('Media & links', 'sage') => [
                ['hero_bg', __('Hero background image', 'sage'), 'image', __('Built-in until you choose one.', 'sage')],
            ],
            __('Contact details', 'sage') => [
                ['contact_email', __('Email address', 'sage'), 'text', 'matthew.r.hummel@gmail.com'],
                ['contact_phone', __('Phone (leave blank to hide)', 'sage'), 'text', ''],
                ['contact_hours', __('Hours', 'sage'), 'text', __('Mon–Fri, 9am–5pm · evenings by appointment', 'sage')],
            ],
            __('Under the hero', 'sage') => [
                ['cnt_why_eyebrow', __('Eyebrow', 'sage'), 'text', __('How this works', 'sage')],
                ['cnt_why_title', __('Heading (before accent)', 'sage'), 'text', __('A conversation,', 'sage')],
                ['cnt_why_accent', __('Accent phrase', 'sage'), 'text', __('not a pitch.', 'sage')],
                ['cnt_why_intro', __('Intro paragraph', 'sage'), 'html', __('<strong>I’m a one-person studio in Gettysburg.</strong> Quotes are welcome. So are introductions, partnerships, and “I’m not sure yet.” Fill in what you can — skip what you can’t. I’ll still read it.', 'sage')],
                ['cnt_why_jump', __('Jump to form', 'sage'), 'text', __('Request a quote', 'sage')],
                ['cnt_why_email', __('Email link label', 'sage'), 'text', __('Or just email me', 'sage')],
                ['cnt_why_items', __('Three cards', 'sage'), 'repeater', contact_why_item_defaults(), [
                    ['kicker', __('Kicker', 'sage'), 'text'],
                    ['title', __('Card title', 'sage'), 'text'],
                    ['text', __('Card text', 'sage'), 'textarea'],
                ]],
            ],
            __('Ways to reach me', 'sage') => [
                ['cnt_path_quote', __('Quote path · title', 'sage'), 'text', __('Request a quote', 'sage')],
                ['cnt_path_quote_d', __('Quote path · text', 'sage'), 'text', __('The form below — a few details, a real plan back.', 'sage')],
                ['cnt_path_email', __('Email path · title', 'sage'), 'text', __('Email me directly', 'sage')],
                ['cnt_path_call', __('Call path · title (if phone is set)', 'sage'), 'text', __('Call or text', 'sage')],
                ['cnt_path_tools', __('Tools path · title', 'sage'), 'text', __('Grade your site first', 'sage')],
                ['cnt_path_tools_d', __('Tools path · text', 'sage'), 'text', __('Free, no signup — look around, then decide.', 'sage')],
            ],
            __('Form column', 'sage') => [
                ['cnt_form_eyebrow', __('Eyebrow', 'sage'), 'text', __('Quote request', 'sage')],
                ['cnt_form_title', __('Heading (before accent)', 'sage'), 'text', __('A few details.', 'sage')],
                ['cnt_form_accent', __('Accent phrase', 'sage'), 'text', __('That’s enough.', 'sage')],
                ['cnt_form_intro', __('Intro under the heading', 'sage'), 'textarea', __('Skip anything you’re not sure about. Required fields are name, email, what you need, and what a win would look like.', 'sage')],
                ['cnt_form_btn', __('Submit button', 'sage'), 'text', __('Send my request', 'sage')],
                ['cnt_form_fine', __('Line under the button', 'sage'), 'text', __('No mailing list. A real reply from Matt — usually within a business day.', 'sage')],
                ['cnt_form_success', __('Success message', 'sage'), 'textarea', __('Thanks — I have it. I’ll reply within a business day, usually with a few questions and a clear next step.', 'sage')],
            ],
            __('Contact form — fields', 'sage') => [
                ['cquote_fields', __('Quote form fields', 'sage'), 'repeater', contact_field_defaults(), [
                    ['label', __('Field label', 'sage'), 'text'],
                    ['type', __('Field type', 'sage'), 'select', [
                        'heading'  => __('Section heading (not a field)', 'sage'),
                        'text'     => __('Text', 'sage'),
                        'email'    => __('Email', 'sage'),
                        'tel'      => __('Phone', 'sage'),
                        'url'      => __('Website / URL', 'sage'),
                        'number'   => __('Number', 'sage'),
                        'date'     => __('Date', 'sage'),
                        'textarea' => __('Message (multi-line)', 'sage'),
                        'select'   => __('Dropdown', 'sage'),
                        'radio'    => __('Choice cards (pick one)', 'sage'),
                        'checkbox' => __('Checkbox (agree / opt-in)', 'sage'),
                    ]],
                    ['placeholder', __('Placeholder (optional)', 'sage'), 'text'],
                    ['required', __('Required', 'sage'), 'checkbox'],
                    ['width', __('Column width', 'sage'), 'select', [
                        'full'  => __('Full width', 'sage'),
                        'half'  => __('Half (2 per row)', 'sage'),
                        'third' => __('Third (3 per row)', 'sage'),
                    ]],
                    ['choices', __('Choices — one per line (dropdown or cards)', 'sage'), 'lines'],
                ], 'formbuilder'],
            ],
            __('Contact form — auto-reply', 'sage') => [
                ['cform_ar_enable', __('Send an automatic reply to the sender', 'sage'), 'checkbox', '0'],
                ['cform_ar_subject', __('Auto-reply subject', 'sage'), 'text', __('Thanks for reaching out — Ridges & Valleys Studio', 'sage')],
                ['cform_ar_body', __('Auto-reply message', 'sage'), 'html', "Thanks for getting in touch — I have received your message and will personally reply within one business day.\n\n— Matt, Ridges & Valleys Studio"],
                ['cform_ar_copy', __('Include a copy of their message', 'sage'), 'checkbox', '1'],
            ],
            __('Contact form — consent', 'sage') => [
                ['cform_consent_enable', __('Show a consent checkbox', 'sage'), 'checkbox', '0'],
                ['cform_consent_text', __('Consent text (links allowed)', 'sage'), 'html', __('I agree to be contacted about my enquiry and have read the <a href="/privacy/">privacy policy</a>.', 'sage')],
                ['cform_consent_required', __('Consent is required to submit', 'sage'), 'checkbox', '1'],
            ],
            __('What happens next', 'sage') => [
                ['cnt_next_eyebrow', __('Eyebrow', 'sage'), 'text', __('What happens next', 'sage')],
                ['cnt_next', __('Numbered steps', 'sage'), 'repeater', contact_next_defaults(), [
                    ['strong', __('Bold lead-in', 'sage'), 'text'],
                    ['text', __('Rest of the line', 'sage'), 'text'],
                ]],
                ['cnt_open_eyebrow', __('Open-door eyebrow', 'sage'), 'text', __('Also here for', 'sage')],
                ['cnt_open_text', __('Open-door note', 'sage'), 'textarea', __('Introductions, referrals, and a coffee around Gettysburg. If you’re another designer or a local group looking to collaborate, say hello — I’m easy to reach.', 'sage')],
            ],
            __('Reach me directly', 'sage') => [
                ['creach_eyebrow', __('Eyebrow', 'sage'), 'text', __('Reach me directly', 'sage')],
                ['creach_location', __('Location line', 'sage'), 'text', __('Gettysburg, PA · serving Adams County & South Central PA', 'sage')],
            ],
            __('Local service area', 'sage') => [
                ['clocal_eyebrow', __('Eyebrow', 'sage'), 'text', __('A Gettysburg web designer', 'sage')],
                ['clocal_title', __('Heading (before accent)', 'sage'), 'text', __('Close by, and easy to', 'sage')],
                ['clocal_accent', __('Accent word', 'sage'), 'text', __('reach.', 'sage')],
                ['clocal_intro', __('Intro paragraph', 'sage'), 'html', __('<strong>An independent, one-person studio in Gettysburg.</strong> That means real meetings when you want them, quick answers, and a site built by someone who actually knows the towns your customers come from. I work with restaurants, inns, shops, tradespeople, tour operators, realtors, and nonprofits across Adams County and South Central PA.', 'sage')],
                ['clocal_towns', __('Town chips', 'sage'), 'lines', ['Gettysburg', 'Biglerville', 'Littlestown', 'New Oxford', 'McSherrystown', 'Bonneauville', 'Fairfield', 'Cashtown', 'Abbottstown', 'East Berlin', 'Hanover', 'York Springs']],
                ['clocal_note', __('Note under the towns', 'sage'), 'textarea', __('Not right in the neighborhood? No problem — plenty of projects run entirely over a call and a shared screen. Wherever you are, you get the same fixed price, the same accessibility-first build, and full ownership at the end.', 'sage')],
                ['clocal_btn1', __('Primary button label', 'sage'), 'text', __('Request a quote', 'sage')],
                ['clocal_btn2', __('Secondary button label', 'sage'), 'text', __('Or just email me', 'sage')],
            ],
        ],

        'template-accessibility.blade.php' => [
            __('Hero', 'sage') => $hero(
                __('Accessibility · WCAG 2.1 AA · Section 508', 'sage'),
                __('A front door that opens for', 'sage'),
                __('everyone.', 'sage'),
                __('WCAG 2.2 AA on every build — more customers, better rankings, and a front door that opens for everyone.', 'sage')
            ),
            __('Hero buttons', 'sage') => [
                ['a11y_hero_btn1', __('Primary button label', 'sage'), 'text', __('Run a live audit', 'sage')],
                ['a11y_hero_btn2', __('Secondary button label', 'sage'), 'text', __('Talk to me', 'sage')],
            ],
            __('Why it matters', 'sage') => [
                ['why_eyebrow', __('Eyebrow', 'sage'), 'text', __('Why it matters', 'sage')],
                ['why_title', __('Heading (before accent)', 'sage'), 'text', __('Accessibility is good business,', 'sage')],
                ['why_accent', __('Accent phrase', 'sage'), 'text', __('not just', 'sage')],
                ['why_title_end', __('Heading (after accent)', 'sage'), 'text', __('good manners.', 'sage')],
                ['why_intro', __('Intro paragraph', 'sage'), 'textarea', __('When a site is hard to use, people don\'t complain — they leave, and often they don\'t come back. Building for everyone widens your audience and protects your business.', 'sage')],
                ['why_stats', __('Statistics', 'sage'), 'repeater', [
                    ['num' => __('1 in 4', 'sage'), 'label' => __('U.S. adults live with a disability — over 70 million people.', 'sage'), 'src' => __('CDC, 2024', 'sage')],
                    ['num' => '3,117', 'label' => __('federal website-accessibility lawsuits filed in 2025 — up 27% over 2024.', 'sage'), 'src' => __('Seyfarth ADA Title III, 2026', 'sage')],
                    ['num' => '36%', 'label' => __('of all ADA Title III federal suits in 2025 were about websites.', 'sage'), 'src' => __('Seyfarth ADA Title III, 2026', 'sage')],
                    ['num' => '$490B', 'label' => __('in annual disposable income is controlled by working-age people with disabilities.', 'sage'), 'src' => __('American Institutes for Research', 'sage')],
                ], [
                    ['num', __('Big number', 'sage'), 'text'],
                    ['label', __('Label', 'sage'), 'textarea'],
                    ['src', __('Source', 'sage'), 'text'],
                ]],
                ['why_legal_note', __('Legal disclaimer', 'sage'), 'textarea', __('This page is general information, not legal advice. Which rules apply to your business depends on your situation — talk to a qualified attorney about your specific obligations.', 'sage')],
            ],
            __('The legal picture', 'sage') => [
                ['legal_eyebrow', __('Eyebrow', 'sage'), 'text', __('The rules, in plain English', 'sage')],
                ['legal_title', __('Heading (before accent)', 'sage'), 'text', __('What the federal standards actually', 'sage')],
                ['legal_accent', __('Accent word', 'sage'), 'text', __('say.', 'sage')],
                ['legal_items', __('Standard cards', 'sage'), 'repeater', [
                    ['title' => __('Section 508', 'sage'), 'text' => __('Requires federal agencies and many of their contractors to build technology to WCAG 2.0 Level AA. It\'s the oldest and most established U.S. digital-accessibility standard.', 'sage')],
                    ['title' => __('ADA Title II', 'sage'), 'text' => __('A 2024 federal rule requires state and local governments to meet WCAG 2.1 Level AA. Deadlines were extended in 2026: April 26, 2027 for larger entities (50k+), and April 26, 2028 for smaller ones and special districts.', 'sage')],
                    ['title' => __('ADA Title III', 'sage'), 'text' => __('Covers private businesses open to the public. There\'s no single codified web standard yet, but courts overwhelmingly treat WCAG 2.1 Level AA as the benchmark — which is exactly what most website lawsuits are measured against.', 'sage')],
                ], [
                    ['title', __('Card title', 'sage'), 'text'],
                    ['text', __('Card text', 'sage'), 'textarea'],
                ]],
                ['legal_through', __('Through-line paragraph', 'sage'), 'textarea', __('The through-line: WCAG 2.1 Level AA is the target. Build to it and you\'re aligned with Section 508, ready for ADA Title II, and measured favorably under Title III. That\'s the standard I build every site to — the full checklist is below.', 'sage')],
            ],
            __('Live audit heading', 'sage') => [
                ['audit_eyebrow', __('Eyebrow', 'sage'), 'text', __('Check your own site', 'sage')],
                ['audit_title', __('Heading (before accent)', 'sage'), 'text', __('Run a live', 'sage')],
                ['audit_accent', __('Accent phrase', 'sage'), 'text', __('WCAG 2.1 AA', 'sage')],
                ['audit_title_end', __('Heading (after accent)', 'sage'), 'text', __('audit.', 'sage')],
                ['audit_intro', __('Intro paragraph', 'sage'), 'textarea', __('Enter your URL and watch it run. The automated checks verify what a scanner can — 8 of the 50 criteria — and every other item is flagged for the manual review a person has to do. That\'s most of accessibility, and exactly why I audit by hand.', 'sage')],
                ['audit_note', __('Note under the tool', 'sage'), 'text', __('Automated scans can only verify a fraction of WCAG — no tool catches it all. Want the full manual audit and the fixes?', 'sage')],
            ],
            __('Business benefits', 'sage') => [
                ['benefits_eyebrow', __('Eyebrow', 'sage'), 'text', __('What you get out of it', 'sage')],
                ['benefits_title', __('Heading (before accent)', 'sage'), 'text', __('Accessible sites just', 'sage')],
                ['benefits_accent', __('Accent word', 'sage'), 'text', __('perform', 'sage')],
                ['benefits_title_end', __('Heading (after accent)', 'sage'), 'text', __('better.', 'sage')],
                ['benefits_items', __('Benefit cards', 'sage'), 'repeater', [
                    ['title' => __('A bigger audience', 'sage'), 'text' => __('One in four adults — plus their families and friends — can actually use your site. That\'s customers your competitors are turning away.', 'sage')],
                    ['title' => __('Better SEO', 'sage'), 'text' => __('Accessibility and search optimization share the same roots: clean structure, real headings, alt text, and captions. Google reads a site the way a screen reader does.', 'sage')],
                    ['title' => __('Works for everyone', 'sage'), 'text' => __('Captions in a noisy room, high contrast in bright sun, big tap targets one-handed. "Accessible" design is just better design for all of us.', 'sage')],
                    ['title' => __('Faster, cleaner code', 'sage'), 'text' => __('Building accessibly means lean, semantic markup — which also loads faster and is easier to maintain.', 'sage')],
                    ['title' => __('Trust and reputation', 'sage'), 'text' => __('A site that welcomes everyone says something about how you run your business. Exclusion is a bad look; inclusion builds loyalty.', 'sage')],
                    ['title' => __('Lower legal risk', 'sage'), 'text' => __('Most website lawsuits target sites with obvious, fixable barriers. Building to the standard is the most reliable way to stay off that list.', 'sage')],
                ], [
                    ['title', __('Card title', 'sage'), 'text'],
                    ['text', __('Card text', 'sage'), 'textarea'],
                ]],
            ],
            __('How I build accessible', 'sage') => [
                ['how_eyebrow', __('Eyebrow', 'sage'), 'text', __('How I build', 'sage')],
                ['how_title', __('Heading (before accent)', 'sage'), 'text', __('Accessibility-first, not', 'sage')],
                ['how_accent', __('Accent word', 'sage'), 'text', __('bolted-on.', 'sage')],
                ['how_intro', __('Intro paragraph', 'sage'), 'textarea', __('An accessibility overlay or "widget" won\'t make a site compliant — and can make it worse. Real accessibility is built into the markup from the first line. Here\'s what that looks like on every project.', 'sage')],
                ['how_items', __('Method cards', 'sage'), 'repeater', [
                    ['title' => __('Semantic by default', 'sage'), 'text' => __('Proper headings, landmarks, lists, and labels — so assistive tech understands the page without guesswork.', 'sage')],
                    ['title' => __('Keyboard tested', 'sage'), 'text' => __('Every menu, form, and control is operated with a keyboard alone, with a focus outline you can always see.', 'sage')],
                    ['title' => __('Contrast checked', 'sage'), 'text' => __('Colors are chosen and verified against the 4.5:1 minimum before they ship — not hoped for after.', 'sage')],
                    ['title' => __('Screen-reader passes', 'sage'), 'text' => __('Key pages are walked through with a screen reader, because automated tools catch only part of the picture.', 'sage')],
                    ['title' => __('Real alt text', 'sage'), 'text' => __('Images get descriptions that convey meaning — written by a person, not auto-generated filler.', 'sage')],
                    ['title' => __('Checked before launch', 'sage'), 'text' => __('Every page is audited against this checklist before it goes live — and you can re-check anytime with the free tools.', 'sage')],
                ], [
                    ['title', __('Card title', 'sage'), 'text'],
                    ['text', __('Card text', 'sage'), 'textarea'],
                ]],
            ],
            __('Accessibility statement', 'sage') => [
                ['stmt_eyebrow', __('Eyebrow', 'sage'), 'text', __('Our commitment', 'sage')],
                ['stmt_title', __('Heading', 'sage'), 'text', __('Accessibility statement', 'sage')],
                ['stmt_p1', __('Statement paragraph 1', 'sage'), 'textarea', __('Ridges & Valleys Studio is committed to making this website usable for as many people as possible, regardless of ability or technology. I aim to meet WCAG 2.1 Level AA across the site: readable contrast, keyboard-accessible navigation, meaningful text alternatives, clear focus states, semantic structure, and content that works with screen readers.', 'sage')],
                ['stmt_p2', __('Statement paragraph 2', 'sage'), 'textarea', __('Accessibility is ongoing work. Some third-party or embedded content may not fully meet this standard; where I find gaps, I work to fix or replace them. If you run into a barrier on this site, please tell me what happened and the page you were on — I\'ll put it right.', 'sage')],
                ['stmt_button', __('Button label', 'sage'), 'text', __('Report an accessibility issue', 'sage')],
            ],
            __('Closing CTA', 'sage') => [
                ['a11y_cta_title', __('Heading', 'sage'), 'text', __('Want to know where your site stands?', 'sage')],
                ['a11y_cta_sub', __('Subtext', 'sage'), 'textarea', __('Run the free accessibility scan, or send me your URL and I\'ll take a real look — no pressure, no jargon.', 'sage')],
                ['a11y_cta_button', __('Button label', 'sage'), 'text', __('Run a free scan', 'sage')],
            ],
        ],

        'template-tools.blade.php' => [
            __('Hero', 'sage') => array_merge($hero(
                __('Free website tools · No email required', 'sage'),
                __('Free tools to check your', 'sage'),
                __('website.', 'sage'),
                __('Grade your site, check SEO, accessibility, and security — free, in seconds, no signup.', 'sage')
            ), [
                ['hero_note', __('Trust line under the buttons', 'sage'), 'text', __('No account · No email · Instant results · Built in Gettysburg', 'sage')],
                ['tools_proof', __('Proof ribbon (four items)', 'sage'), 'repeater', tools_proof_defaults(), [
                    ['v', __('Value', 'sage'), 'text'],
                    ['l', __('Label', 'sage'), 'text'],
                ]],
            ]),
            __('Media & links', 'sage') => [
                ['hero_bg', __('Hero background image', 'sage'), 'image', __('Built-in until you choose one.', 'sage')],
            ],
            __('Why these tools', 'sage') => [
                ['intro_p1', __('Paragraph 1', 'sage'), 'textarea', __('Your website is working for you around the clock — but is it actually doing its job? Most small-business owners have no easy way to tell whether their site loads fast enough, shows up on Google, works on a phone, or keeps visitors\' information safe.', 'sage')],
                ['intro_p2', __('Paragraph 2', 'sage'), 'textarea', __('These tools answer those questions in plain English, so you can see exactly where your site stands before you spend a dollar fixing it. No catch: every checker runs instantly, with no account and no sales call required.', 'sage')],
            ],
            __('How to pick', 'sage') => [
                ['pick_eyebrow', __('Eyebrow', 'sage'), 'text', __('Not sure where to start?', 'sage')],
                ['pick_title', __('Heading (before accent)', 'sage'), 'text', __('Pick the check that', 'sage')],
                ['pick_accent', __('Accent phrase', 'sage'), 'text', __('matches the snag.', 'sage')],
                ['pick_intro', __('Intro paragraph', 'sage'), 'textarea', __('Three doors. One minute to choose. If you only run one thing today, make it the Website Grader.', 'sage')],
                ['pick_items', __('Chooser cards', 'sage'), 'repeater', tools_pick_defaults(), [
                    ['kicker', __('Kicker', 'sage'), 'text'],
                    ['title', __('Heading', 'sage'), 'text'],
                    ['text', __('Text', 'sage'), 'textarea'],
                    ['cta', __('Link label', 'sage'), 'text'],
                    ['url', __('Link', 'sage'), 'url'],
                ]],
            ],
            __('The checkers', 'sage') => [
                ['chk_eyebrow', __('Eyebrow', 'sage'), 'text', __('The checkers', 'sage')],
                ['chk_title', __('Heading (before accent)', 'sage'), 'text', __('Six instant', 'sage')],
                ['chk_accent', __('Accent phrase', 'sage'), 'text', __('report cards.', 'sage')],
                ['chk_intro', __('Intro paragraph', 'sage'), 'textarea', __('Enter your URL and get a clear, color-coded grade with every check explained — and why it matters for your business.', 'sage')],
                ['chk_legend_pass', __('Score legend · pass', 'sage'), 'text', __('Pass — already working', 'sage')],
                ['chk_legend_review', __('Score legend · review', 'sage'), 'text', __('Review — worth a look', 'sage')],
                ['chk_legend_fail', __('Score legend · fail', 'sage'), 'text', __('Fail — fix this first', 'sage')],
                ['chk_open', __('Card link label', 'sage'), 'text', __('Open the tool', 'sage')],
                ['chk_why_label', __('“Why it matters” label', 'sage'), 'text', __('Why it matters:', 'sage')],
                ['chk_filters', __('Filter chips', 'sage'), 'repeater', tools_filter_defaults(), [
                    ['key', __('Filter key (all, search, local, trust, access)', 'sage'), 'text'],
                    ['label', __('Chip label', 'sage'), 'text'],
                ]],
                ['chk_items', __('Tool cards', 'sage'), 'repeater', tools_checker_defaults(), [
                    ['flag', __('Badge (optional)', 'sage'), 'text'],
                    ['title', __('Name', 'sage'), 'text'],
                    ['url', __('Link', 'sage'), 'url'],
                    ['text', __('What it does', 'sage'), 'textarea'],
                    ['why', __('Why it matters', 'sage'), 'textarea'],
                    ['time', __('Time estimate', 'sage'), 'text'],
                    ['group', __('Filter group', 'sage'), 'select', [
                        'search' => __('Search', 'sage'),
                        'local'  => __('Local', 'sage'),
                        'trust'  => __('Trust', 'sage'),
                        'access' => __('Access', 'sage'),
                    ]],
                    ['featured', __('Featured (wide card)', 'sage'), 'checkbox'],
                ]],
            ],
            __('Quick tools', 'sage') => [
                ['quick_eyebrow', __('Eyebrow', 'sage'), 'text', __('Quick tools', 'sage')],
                ['quick_title', __('Heading (before accent)', 'sage'), 'text', __('Handy calculators &', 'sage')],
                ['quick_accent', __('Accent phrase', 'sage'), 'text', __('checks.', 'sage')],
                ['quick_intro', __('Intro paragraph', 'sage'), 'textarea', __('No URL needed — these run right here on the page. Test a color combination, ballpark a project, or estimate a care plan.', 'sage')],
            ],
            __('How to use', 'sage') => [
                ['howt_eyebrow', __('Eyebrow', 'sage'), 'text', __('Getting the most from them', 'sage')],
                ['howt_title', __('Heading (before accent)', 'sage'), 'text', __('How to use these', 'sage')],
                ['howt_accent', __('Accent phrase', 'sage'), 'text', __('tools.', 'sage')],
                ['howt_intro', __('Intro paragraph', 'sage'), 'textarea', __('A four-step loop. You can stop after step one and still walk away smarter.', 'sage')],
                ['howt_items', __('Steps (numbered automatically)', 'sage'), 'repeater', tools_how_defaults(), [
                    ['title', __('Step title', 'sage'), 'text'],
                    ['text', __('Step text', 'sage'), 'textarea'],
                ]],
            ],
            __('What they can tell you', 'sage') => [
                ['lim_eyebrow', __('Eyebrow', 'sage'), 'text', __('Honest limits', 'sage')],
                ['lim_title', __('Heading (before accent)', 'sage'), 'text', __('What a machine can', 'sage')],
                ['lim_accent', __('Accent phrase', 'sage'), 'text', __('and can’t see.', 'sage')],
                ['lim_intro', __('Intro paragraph', 'sage'), 'textarea', __('Automated checks catch the concrete stuff that holds a local site back. They cannot judge whether the site says the right thing to the right person. A perfect technical score still loses if the message is wrong — that’s the work I finish by hand.', 'sage')],
                ['lim_yes_title', __('Covers · heading', 'sage'), 'text', __('These tools look at', 'sage')],
                ['lim_yes', __('Covers · list', 'sage'), 'lines', tools_cover_yes_defaults()],
                ['lim_no_title', __('Does not cover · heading', 'sage'), 'text', __('They do not judge', 'sage')],
                ['lim_no', __('Does not cover · list', 'sage'), 'lines', tools_cover_no_defaults()],
            ],
            __('Privacy', 'sage') => [
                ['priv_eyebrow', __('Eyebrow', 'sage'), 'text', __('No catch', 'sage')],
                ['priv_title', __('Heading (before accent)', 'sage'), 'text', __('Built for local businesses,', 'sage')],
                ['priv_accent', __('Accent phrase', 'sage'), 'text', __('given away for free.', 'sage')],
                ['priv_intro', __('Intro paragraph', 'sage'), 'textarea', __('I’m Matt Hummel, a Gettysburg-based web developer with 15+ years of experience. I built these because most small businesses in Adams County are paying for websites that quietly underperform — and they have no easy way to see it. A clear checkup shouldn’t cost anything.', 'sage')],
                ['priv_items', __('Promises', 'sage'), 'repeater', tools_privacy_defaults(), [
                    ['title', __('Heading', 'sage'), 'text'],
                    ['text', __('Text', 'sage'), 'textarea'],
                ]],
            ],
            __('After the score', 'sage') => [
                ['nxt_eyebrow', __('Eyebrow', 'sage'), 'text', __('After the score', 'sage')],
                ['nxt_title', __('Heading (before accent)', 'sage'), 'text', __('Want the fixes, not just', 'sage')],
                ['nxt_accent', __('Accent phrase', 'sage'), 'text', __('the report?', 'sage')],
                ['nxt_intro', __('Intro paragraph', 'sage'), 'textarea', __('Keep the results and DIY, or hand me the URL. Either way you already know more than you did ten minutes ago.', 'sage')],
                ['nxt_items', __('Next-step cards', 'sage'), 'repeater', tools_next_defaults(), [
                    ['title', __('Heading', 'sage'), 'text'],
                    ['text', __('Text', 'sage'), 'textarea'],
                    ['cta', __('Link label', 'sage'), 'text'],
                    ['url', __('Link', 'sage'), 'url'],
                ]],
            ],
            __('FAQ', 'sage') => [
                ['tfq_eyebrow', __('Eyebrow', 'sage'), 'text', __('Common questions', 'sage')],
                ['tfq_title', __('Heading (before accent)', 'sage'), 'text', __('Free tools,', 'sage')],
                ['tfq_accent', __('Accent phrase', 'sage'), 'text', __('answered.', 'sage')],
                ['tfq_items', __('Questions', 'sage'), 'repeater', tools_faq_defaults(), [
                    ['q', __('Question', 'sage'), 'text'],
                    ['a', __('Answer', 'sage'), 'textarea'],
                ]],
            ],
            __('Call to action', 'sage') => $cta(
                __('Want a hand with what the tools turned up?', 'sage'),
                __('I fix these for Gettysburg and South Central PA businesses every week. Tell me your site and I’ll take a real look.', 'sage'),
                __('Get a quote', 'sage')
            ),
        ],

        'template-grader.blade.php' => [
            __('Hero', 'sage') => $hero(
                __('Free website grader', 'sage'),
                __('How good is your website,', 'sage'),
                __('really?', 'sage'),
                __('A plain-English report card across SEO, speed, mobile, readability, security, and more.', 'sage')
            ),
            __('Call to action', 'sage') => $cta(
                __('Want the fixes, not just the grade?', 'sage'),
                __('Send me your URL and I\'ll turn the report into a short, prioritized plan — and I can do the work for you.', 'sage'),
                __('Get my plan', 'sage')
            ),
        ],

        'template-seo.blade.php' => [
            __('Hero', 'sage') => $hero(
                __('Free SEO checker', 'sage'),
                __('Can Google actually', 'sage'),
                __('find you?', 'sage'),
                __('A plain-English audit of any page — snippets, crawlability, keywords, and structured data.', 'sage')
            ),
            __('Call to action', 'sage') => $cta(
                __('Want to actually rank, not just score well?', 'sage'),
                __('Send me your URL and the phrase you want to be found for. I\'ll turn this into a prioritized plan — and do the work.', 'sage'),
                __('Get my SEO plan', 'sage')
            ),
        ],

        'template-security.blade.php' => [
            __('Hero', 'sage') => $hero(
                __('Free security checker', 'sage'),
                __('Is your website', 'sage'),
                __('locked up?', 'sage'),
                __('HTTPS, headers, leaks, cookies — each result in plain English, and why it matters.', 'sage')
            ),
            __('What this scan covers', 'sage') => [
                ['sec_cover_eyebrow', __('Eyebrow', 'sage'), 'text', __('What this scan covers', 'sage')],
                ['sec_cover_title', __('Heading (before accent)', 'sage'), 'text', __('A hygiene check,', 'sage')],
                ['sec_cover_accent', __('Accent phrase', 'sage'), 'text', __('not a pentest.', 'sage')],
                ['sec_cover_lede', __('Intro paragraph', 'sage'), 'textarea', __('It reads what browsers — and attackers — see in your page and its headers. Free, no email. The closer below is for the work this scan cannot do.', 'sage')],
                ['sec_cover_yes_title', __('Covers · heading', 'sage'), 'text', __('This scan looks at', 'sage')],
                ['sec_cover_yes', __('Covers · list', 'sage'), 'lines', security_cover_yes_defaults()],
                ['sec_cover_no_title', __('Does not cover · heading', 'sage'), 'text', __('It does not check', 'sage')],
                ['sec_cover_no', __('Does not cover · list', 'sage'), 'lines', security_cover_no_defaults()],
            ],
            __('Lock it down', 'sage') => [
                ['sec_lock_eyebrow', __('Eyebrow', 'sage'), 'text', __('After the scan', 'sage')],
                ['sec_lock_title', __('Heading (before accent)', 'sage'), 'text', __('Want it locked down.', 'sage')],
                ['sec_lock_accent', __('Accent phrase', 'sage'), 'text', __('And kept that way.', 'sage')],
                ['sec_lock_lede', __('Intro paragraph', 'sage'), 'textarea', __('The checker finds the gaps. I close them — HTTPS, headers, backups, updates — and keep an eye on the site so it stays that way. Optional Care & Grow from $179 a month. Cancel anytime. You keep the keys.', 'sage')],
                ['sec_lock_items', __('Three promises', 'sage'), 'repeater', security_lock_item_defaults(), [
                    ['kicker', __('Kicker', 'sage'), 'text'],
                    ['title', __('Heading', 'sage'), 'text'],
                    ['text', __('Text', 'sage'), 'textarea'],
                ]],
                ['sec_lock_btn', __('Primary button', 'sage'), 'text', __('Secure my site', 'sage')],
                ['sec_lock_btn2', __('Secondary button', 'sage'), 'text', __('See Care & Grow', 'sage')],
                ['sec_lock_care_url', __('Secondary button link', 'sage'), 'url', __('e.g. /gettysburg-web-design-services/#care', 'sage')],
                ['sec_lock_fine', __('Reassurance line', 'sage'), 'text', __('Fixed price · You own the site · A real person answers', 'sage')],
            ],
        ],

        'template-email.blade.php' => [
            __('Hero', 'sage') => $hero(
                __('Free email deliverability checker', 'sage'),
                __('Is your email landing in', 'sage'),
                __('spam?', 'sage'),
                __('Check SPF, DKIM, and DMARC — and whether anyone can send mail as you. Plain English, no signup.', 'sage')
            ),
            __('Call to action', 'sage') => $cta(
                __('Want your email trusted and spoof-proof?', 'sage'),
                __('I\'ll set up SPF, DKIM, and DMARC correctly so your mail reaches inboxes and nobody can impersonate you.', 'sage'),
                __('Fix my email setup', 'sage')
            ),
        ],

        'template-local.blade.php' => [
            __('Hero', 'sage') => $hero(
                __('Free local SEO scorecard', 'sage'),
                __('Do nearby customers', 'sage'),
                __('find you?', 'sage'),
                __('Name, address, hours, schema, maps, reviews — the signals that get Gettysburg businesses in the map pack.', 'sage')
            ),
            __('Call to action', 'sage') => $cta(
                __('Want to own “near me” in Adams County?', 'sage'),
                __('I set up local SEO end to end — on-page signals, Google Business Profile, and town-by-town pages — for Gettysburg and South Central PA businesses.', 'sage'),
                __('Get found locally', 'sage')
            ),
        ],

        'index.blade.php' => [
            __('Hero', 'sage') => [
                ['jnl_kicker', __('Eyebrow', 'sage'), 'text', __('Gettysburg web design journal', 'sage')],
                ['jnl_h1', __('Heading (before accent)', 'sage'), 'text', __('Gettysburg web design advice you can', 'sage')],
                ['jnl_h1_accent', __('Accent phrase', 'sage'), 'text', __('actually use.', 'sage')],
                ['jnl_lede', __('Hero lede (one short paragraph)', 'sage'), 'textarea', __('Honest guides for Gettysburg and Adams County owners — cost, Google Maps, Wix vs a local designer, and whether your site is quietly costing you calls. Read one, then get a quote.', 'sage')],
                ['jnl_cta', __('Primary button', 'sage'), 'text', __('Get a quote', 'sage')],
                ['jnl_cta_url', __('Primary button link', 'sage'), 'url', __('e.g. /contact/', 'sage')],
                ['jnl_cta2', __('Secondary button', 'sage'), 'text', __('Read the latest', 'sage')],
                ['jnl_note', __('Meta line (under the buttons)', 'sage'), 'text', __('Plain English · Local SEO · No fluff', 'sage')],
                ['jnl_proof', __('Proof ribbon (four items)', 'sage'), 'repeater', journal_proof_defaults(), [
                    ['v', __('Value', 'sage'), 'text'],
                    ['l', __('Label', 'sage'), 'text'],
                ]],
            ],
            __('Media & links', 'sage') => [
                ['hero_bg', __('Hero background image', 'sage'), 'image', __('Built-in until you choose one.', 'sage')],
            ],
            __('How to use this journal', 'sage') => [
                ['jnl_why_eyebrow', __('Eyebrow', 'sage'), 'text', __('How to use this journal', 'sage')],
                ['jnl_why_title', __('Heading (before accent)', 'sage'), 'text', __('Not a blog.', 'sage')],
                ['jnl_why_accent', __('Accent phrase', 'sage'), 'text', __('Buying guides.', 'sage')],
                ['jnl_why_intro', __('Intro paragraph', 'sage'), 'html', __('<strong>These posts are here to help you decide.</strong> What a site should cost, whether you need one, how to show up on Maps — written for owners around Gettysburg, not developers. Pick a question, read the honest answer, then get a quote if it fits.', 'sage')],
                ['jnl_why_jump', __('Jump link (to the posts)', 'sage'), 'text', __('Browse the guides', 'sage')],
                ['jnl_why_cta', __('Quote button', 'sage'), 'text', __('Get a quote', 'sage')],
                ['jnl_why_items', __('Three steps', 'sage'), 'repeater', journal_why_item_defaults(), [
                    ['kicker', __('Kicker', 'sage'), 'text'],
                    ['title', __('Card title', 'sage'), 'text'],
                    ['text', __('Card text', 'sage'), 'textarea'],
                ]],
            ],
            __('Posts header', 'sage') => [
                ['jnl_list_eyebrow', __('Eyebrow', 'sage'), 'text', __('The journal', 'sage')],
                ['jnl_list_title', __('Heading (before accent)', 'sage'), 'text', __('Guides for', 'sage')],
                ['jnl_list_accent', __('Accent phrase', 'sage'), 'text', __('Adams County owners.', 'sage')],
                ['jnl_list_intro', __('Intro paragraph', 'sage'), 'textarea', __('Filter by topic. Read the one that matches the snag you’re in. When you’re ready to stop diagnosing and start building, the quote is one click away.', 'sage')],
                ['jnl_cats_label', __('Filter label', 'sage'), 'text', __('Show me', 'sage')],
            ],
            __('Closer', 'sage') => [
                ['jnl_close_eyebrow', __('Eyebrow', 'sage'), 'text', __('Your next step', 'sage')],
                ['jnl_close_title', __('Heading (before accent)', 'sage'), 'text', __('Done reading.', 'sage')],
                ['jnl_close_accent', __('Accent phrase', 'sage'), 'text', __('Let’s build yours.', 'sage')],
                ['jnl_close_intro', __('Intro paragraph', 'sage'), 'textarea', __('Tell me about your Gettysburg or Adams County business. I’ll come back with a fixed-scope idea — usually within a business day. No jargon, no pressure.', 'sage')],
                ['jnl_close_quote', __('Primary path · title', 'sage'), 'text', __('Get a fixed-price quote', 'sage')],
                ['jnl_close_quote_d', __('Primary path · description', 'sage'), 'text', __('The fastest way from this page to a real plan.', 'sage')],
                ['jnl_close_email_t', __('Email path · title', 'sage'), 'text', __('Email me directly', 'sage')],
                ['jnl_close_tools_t', __('Tools path · title', 'sage'), 'text', __('Grade your site first', 'sage')],
                ['jnl_close_tools_d', __('Tools path · description', 'sage'), 'text', __('Free, no signup — see where you stand.', 'sage')],
                ['jnl_close_next_eyebrow', __('What happens next · eyebrow', 'sage'), 'text', __('What happens next', 'sage')],
                ['jnl_close_next', __('What happens next', 'sage'), 'repeater', journal_next_defaults(), [
                    ['strong', __('Lead-in', 'sage'), 'text'],
                    ['text', __('Follow-on', 'sage'), 'textarea'],
                ]],
                ['jnl_close_fine', __('Reassurance line', 'sage'), 'text', __('Fixed price agreed up front · You own the site · A real person answers.', 'sage')],
            ],
        ],
    ];

    // Roll the per-page hero styling onto every template that has a hero:
    // "Hero typography" is added to all of them and rendered globally by the
    // app/hero-style.php wp_head emitter. front-page also carries "Hero buttons"
    // (added above); other templates' hero buttons are wired in their own markup.
    $heroKey = __('Hero', 'sage');
    $typoKey = __('Hero typography', 'sage');
    $btnKey  = __('Hero buttons', 'sage');

    // Per-template current hero-button labels, so each template's "Hero buttons"
    // group placeholders + Blade defaults match what's on the page. These are the
    // templates whose hero buttons are wired to hero_btn1/hero_btn2 in Blade.
    $btnLabels = [
        'template-email.blade.php'        => ['Check my domain', 'Talk to me'],
        'template-faq.blade.php'          => ['Ask your question', 'Jump to the FAQs'],
        'template-grader.blade.php'       => ['Grade my site', 'Talk to me'],
        'template-local.blade.php'        => ['Score my page', 'Talk to me'],
        'template-security.blade.php'     => ['Check my site', 'Talk to me'],
        'template-seo.blade.php'          => ['Check my SEO', 'Talk to me'],
        'template-tools.blade.php'        => ['Browse the tools', 'Talk to me'],
    ];

    foreach ($map as $tpl => &$groups) {
        if (! is_array($groups) || ! isset($groups[$heroKey])) {
            continue;
        }
        if (! isset($groups[$typoKey])) {
            $groups[$typoKey] = hero_typography_rows();
        }
        if (isset($btnLabels[$tpl]) && ! isset($groups[$btnKey])) {
            $groups[$btnKey] = hero_button_rows($btnLabels[$tpl][0], $btnLabels[$tpl][1]);
        }
    }
    unset($groups);

    return $map;
}

/**
 * A short, plain-English hint shown under each section heading in the editor,
 * so it's clear what part of the page each group of fields controls. Keyed by
 * the group label; unknown groups simply get no hint.
 */
function field_group_hint(string $label): string
{
    $hints = [
        __('Hero', 'sage')             => __('The banner at the top of the page: a short eyebrow, the headline with its accent word, one supporting sentence, and the buttons.', 'sage'),
        __('Problems section', 'sage') => __('The “if this sounds familiar” block — heading, lede, three snag cards (each with a one-line fix), and the closer buttons.', 'sage'),
        __('Packages section', 'sage') => __('The heading and Compare button for the homepage pricing strip. The cards themselves come from the Services page (Page content → Packages), so home and services stay in sync.', 'sage'),
        __('Rooted section', 'sage')   => __('The local “built here” block — heading, paragraph, and the comma-separated list of region chips.', 'sage'),
        __('Testimonial', 'sage')      => __('The single highlighted client quote and who said it.', 'sage'),
        __('Call to action', 'sage')   => __('The closing banner that invites visitors to reach out — heading, subtext, and the button label.', 'sage'),
        __('Intro', 'sage')            => __('The About hero: eyebrow, heading, one short lede, button, meta line, and the four-item proof ribbon. The longer welcome note lives in The studio.', 'sage'),
        __('Intro section', 'sage')    => __('The block directly under the hero: a heading plus two cards (Who we are / How we work). This replaced the old page-body columns and photo — edit it here, not in the WordPress content editor.', 'sage'),
        __('Beliefs section', 'sage')  => __('The heading for the “how I work / a few things I believe” block. The three belief cards themselves are set in the template.', 'sage'),
        __('Quote', 'sage')            => __('The one-line studio quote and its attribution.', 'sage'),
        __('Founding offer', 'sage')   => __('The limited founding-offer banner: eyebrow, heading, the price accent, the paragraph, and the button. The “what’s included” checklist is set in the template.', 'sage'),
        __('Header', 'sage')           => __('The contact hero: eyebrow, heading, one short lede, the button into the form, and the small note shown under the form.', 'sage'),
        __('Under the hero', 'sage')   => __('The split under the Contact hero: why this page exists (a conversation, not a pitch) and three cards — quote, not sure yet, just hello.', 'sage'),
        __('Ways to reach me', 'sage') => __('The four path cards under the intro: quote, email, call (if a number is set), and grade-your-site.', 'sage'),
        __('Contact details', 'sage')  => __('Where enquiries go and how people reach you. Leave the phone blank to hide the call/text option; add a number to show it.', 'sage'),
        __('Contact form — fields', 'sage') => __('The quote form itself. Section headings group the fields. Choice cards are the “what do you need?” row. Remove every field to restore the built-in quote form.', 'sage'),
        __('Contact form — design', 'sage') => __('How the form looks: the submit button label and width, whether labels show above the fields or the placeholders stand in for them, the field style, and the message shown after a successful send.', 'sage'),
        __('Contact form — auto-reply', 'sage') => __('Send an automatic confirmation email to the person who submitted the form (uses the email field they filled in). Turn it on, set the subject and message, and optionally include a copy of what they sent.', 'sage'),
        __('Contact form — consent', 'sage') => __('Add a consent / GDPR checkbox above the submit button — useful for opt-in and privacy compliance. Links are allowed in the consent text (e.g. to your privacy policy). Leave “required” on to block submission until it’s ticked.', 'sage'),
        __('Media & links', 'sage')    => __('Swap the section image and point the buttons at any page. Leave a link blank to use the site’s default; leave an image blank to keep the built-in one. Links accept a path like /contact/ or a full https:// URL.', 'sage'),
        __('Included in every build', 'sage') => __('The homepage baseline: heading, lede, six included items, two quieter notes, and the closer buttons.', 'sage'),
        __('Process timeline', 'sage') => __('The day-by-day timeline: its heading and each step (day label, title, and one line).', 'sage'),
        __('Towns served', 'sage')     => __('The local “towns served” block — heading, intro, and the list of town chips (one town per line).', 'sage'),
        __('Bio — how I got here', 'sage') => __('The longer story section: heading, the four paragraphs (basic HTML like <strong> is allowed), and the three skill highlight cards.', 'sage'),
        __('The studio', 'sage') => __('The studio-story block: eyebrow, heading with its accent, four paragraphs (basic HTML like <strong> is allowed), the partner welcome note, and the three capability highlights.', 'sage'),
        __('The team', 'sage') => __('The “who runs the studio” block. Add a card per person with their name, role, short bio, and an optional photo URL (upload the photo to the Media Library, then paste its URL). Leave the photo blank to show a person-silhouette placeholder. Leave a name blank to show role + bio only.', 'sage'),
        __('Credentials / skills', 'sage') => __('The “what’s under the hood” heading and its three skill cards.', 'sage'),
        __('Rooted locally', 'sage')   => __('The big local-story block: heading, three paragraphs, the button label, and the three highlight cards.', 'sage'),
        __('Who I build for', 'sage')  => __('The industries you serve (cards), plus the “areas served” heading, town chips, and footnote.', 'sage'),
        __('Before pricing', 'sage')  => __('The three value cards above the pricing — what every package actually buys, before the dollar amounts.', 'sage'),
        __('Packages', 'sage')         => __('The pricing line-up. Three project cards sit in a row; a Care plan uses the full-width bar underneath. Each card has a name, price, “best for” line, one-line outcome, and three features. Leave a button label blank to use the default button above.', 'sage'),
        __('What every build includes', 'sage') => __('The baseline under the packages: an intro, six included items, then two quieter cards for what’s not in scope and how the work runs.', 'sage'),
        __('Local SEO section', 'sage') => __('The dark local-SEO block: heading, intro, six checklist items, then two quieter cards for what’s in a new build vs. a tune-up.', 'sage'),
        __('Process (services)', 'sage') => __('The services timeline: heading and each day step.', 'sage'),
        __('AI-assisted split', 'sage') => __('The “honest split” heading, the two cards, and the disclosure note.', 'sage'),
        __('Helpful to know (FAQ)', 'sage') => __('The short services FAQ — heading and each question with its answer.', 'sage'),
        __('Process section', 'sage')  => __('The numbered “how it works” flow — heading, intro, each step, and the note beneath. Steps are numbered automatically.', 'sage'),
        __('FAQ list header', 'sage')  => __('The heading and intro that sit above the full FAQ list.', 'sage'),
        __('FAQ items', 'sage')        => __('Every question and answer. Set each row’s Category to group it — questions with the same category appear together, in the order listed here.', 'sage'),
        __('Proof stats strip', 'sage') => __('The dark strip of big numbers. Each stat has a big number, an optional small unit (e.g. “days”), and a label.', 'sage'),
        __('Value cards', 'sage')      => __('The “real work you can actually click” heading and its numbered cards.', 'sage'),
        __('Why these concepts', 'sage') => __('The split under-hero: how to use the page on the left, three funnel steps on the right, plus the jump and quote links.', 'sage'),
        __('Case studies header', 'sage') => __('The heading above the project grid, the industry filters, the honesty line, the note under the cards, and the after-grid quote strip.', 'sage'),
        __('How to use this journal', 'sage') => __('The split under the Journal hero: why these posts exist, plus three steps that turn a reader into a quote.', 'sage'),
        __('Posts header', 'sage') => __('The heading above the Journal grid and the filter label.', 'sage'),
        __('Closer', 'sage') => __('The contact-style closer under the posts (and under each article): heading, three paths, what happens next, and the reassurance line.', 'sage'),
        __('Don\'t see your business', 'sage') => __('The heading, the business-type chips, the foundation panel, and its checklist.', 'sage'),
        __('How it goes', 'sage')      => __('The numbered build flow, plus the “what you walk away with” checklist and reassurance line.', 'sage'),
        __('The design craft', 'sage') => __('The heading, the numbered craft cards, and the closing paragraph.', 'sage'),
        __('Areas served (work)', 'sage') => __('The big areas-served block: heading, lede, town chips, note, and the tagline parts (one per line, shown joined with ·).', 'sage'),
        __('Quick ways to get in touch', 'sage') => __('The three quick-contact cards at the top of the page.', 'sage'),
        __('Form column', 'sage')      => __('The heading above the quote form, the short intro, the send button, and the reassurance line under it.', 'sage'),
        __('What happens next', 'sage') => __('The numbered “what happens next” steps beside the form. Each has a bold lead-in and a follow-on line.', 'sage'),
        __('Reach me directly', 'sage') => __('The contact sidebar’s heading and location line (email, phone, and hours come from “Contact details”).', 'sage'),
        __('Local service area', 'sage') => __('The service-area block: heading, intro, town chips, note, and the two button labels.', 'sage'),
        __('Why it matters', 'sage')   => __('The heading, intro, the four statistics (number, label, source), and the legal disclaimer.', 'sage'),
        __('The legal picture', 'sage') => __('The standards heading, the three standard cards, and the through-line paragraph.', 'sage'),
        __('Live audit heading', 'sage') => __('The heading and intro above the live audit tool, and the note beneath it. (The tool and the WCAG checklist are built in.)', 'sage'),
        __('Business benefits', 'sage') => __('The “accessible sites perform better” heading and its benefit cards.', 'sage'),
        __('How I build accessible', 'sage') => __('The heading, intro, and the method cards.', 'sage'),
        __('Accessibility statement', 'sage') => __('The statement heading, its two paragraphs, and the button. (Adding page body content overrides these paragraphs.)', 'sage'),
        __('Closing CTA', 'sage')      => __('The final call-to-action banner at the bottom of the page.', 'sage'),
        __('Hero trust line', 'sage')  => __('The small trust line shown under the hero buttons.', 'sage'),
        __('Hero buttons', 'sage')     => __('The labels on the two hero buttons.', 'sage'),
        __('Why these tools', 'sage')  => __('Two short paragraphs under the hero. This page does not use the WordPress content editor.', 'sage'),
        __('How to pick', 'sage')      => __('Three chooser cards that send people to the right checker. Each has a kicker, heading, text, and a link.', 'sage'),
        __('The checkers', 'sage')     => __('The URL tool cards. Filter chips group them. Tick Featured on one card to make it wide. The WordPress block editor is not used on this page.', 'sage'),
        __('Quick tools', 'sage')      => __('Heading for the on-page contrast checker, quote estimator, and care calculator. The tools themselves are built in.', 'sage'),
        __('How to use', 'sage')       => __('Numbered steps for getting a useful result from the checkers.', 'sage'),
        __('What they can tell you', 'sage') => __('Honest limits: two lists — what the tools measure, and what still needs a person.', 'sage'),
        __('Privacy', 'sage')          => __('Why the tools are free, plus the no-email / no-saved-scores promises.', 'sage'),
        __('After the score', 'sage')  => __('Three next-step cards into Rescue, packages, or a quote.', 'sage'),
        __('FAQ', 'sage')              => __('Questions and answers on the Free Tools page. Shown as an accordion with FAQ schema.', 'sage'),
    ];
    return $hints[$label] ?? '';
}

/* ------------------------------------------------------------------ meta box */

add_action('add_meta_boxes', function () {
    add_meta_box('rv_page_content', __('Page content (theme)', 'sage'), __NAMESPACE__ . '\\render_page_fields_box', 'page', 'normal', 'high');
});

/**
 * Keep the Page-content box in the wide (normal) column. Its side-by-side live
 * preview needs the room, so don't let a stray drag strand it in the narrow
 * side area.
 */
add_filter('get_user_option_meta-box-order_page', function ($order) {
    if (! is_array($order)) {
        return $order;
    }
    $id = 'rv_page_content';
    foreach (['side', 'advanced'] as $ctx) {
        if (! empty($order[$ctx])) {
            $ids = array_values(array_filter(explode(',', $order[$ctx]), static fn ($x) => $x !== '' && $x !== $id));
            $order[$ctx] = implode(',', $ids);
        }
    }
    $normal = ! empty($order['normal']) ? explode(',', $order['normal']) : [];
    if (! in_array($id, $normal, true)) {
        array_unshift($normal, $id);
    }
    $order['normal'] = implode(',', array_filter($normal, static fn ($x) => $x !== ''));
    return $order;
});

/** Media library + image-picker script for the image fields. */
add_action('admin_enqueue_scripts', function ($hook) {
    if (! in_array($hook, ['post.php', 'post-new.php'], true)) {
        return;
    }
    $screen = get_current_screen();
    if (! $screen || $screen->post_type !== 'page') {
        return;
    }
    // Version each script by its file mtime so browsers always fetch the latest
    // after an edit (no stale cached admin JS).
    $ver = function ($rel) {
        $p = get_theme_file_path($rel);
        return file_exists($p) ? (string) filemtime($p) : '1.0.0';
    };
    wp_enqueue_media();
    wp_enqueue_script('rv-admin-media', get_theme_file_uri('resources/js/admin-media.js'), [], $ver('resources/js/admin-media.js'), true);
    wp_enqueue_script('rv-admin-repeater', get_theme_file_uri('resources/js/admin-repeater.js'), [], $ver('resources/js/admin-repeater.js'), true);
    // Visual drag-and-drop form builder (needs jQuery UI sortable).
    wp_enqueue_script('rv-admin-formbuilder', get_theme_file_uri('resources/js/admin-formbuilder.js'), ['jquery', 'jquery-ui-sortable'], $ver('resources/js/admin-formbuilder.js'), true);
});

/**
 * Render one repeater row's inputs. $index may be a real integer or the literal
 * '__i__' placeholder used inside the JS <template> for new rows.
 */
function render_rep_row(string $name, $index, array $sub, array $row): string
{
    $h  = '<div class="rv-rep-row">';
    $h .= '<div class="rv-rep-tools">';
    $h .= '<button type="button" class="button-link rv-rep-up" title="' . esc_attr__('Move up', 'sage') . '" aria-label="' . esc_attr__('Move up', 'sage') . '">↑</button>';
    $h .= '<button type="button" class="button-link rv-rep-down" title="' . esc_attr__('Move down', 'sage') . '" aria-label="' . esc_attr__('Move down', 'sage') . '">↓</button>';
    $h .= '<button type="button" class="button-link rv-rep-del" title="' . esc_attr__('Remove', 'sage') . '" aria-label="' . esc_attr__('Remove this item', 'sage') . '">✕</button>';
    $h .= '</div><div class="rv-rep-fields">';

    $heading = trim((string) ($row['name'] ?? $row['title'] ?? $row['q'] ?? ''));
    if ($heading !== '') {
        $h .= '<p class="rv-rep-title">' . esc_html($heading) . '</p>';
    }

    foreach ($sub as $sf) {
        $sk     = $sf[0];
        $slabel = $sf[1];
        $stype  = $sf[2] ?? 'text';
        $fname  = $name . '[' . $index . '][' . $sk . ']';
        $val    = $row[$sk] ?? '';
        $wide   = in_array($stype, ['textarea', 'lines'], true) || (($sf[4] ?? '') === 'wide');

        $h .= '<div class="rv-rep-field' . ($wide ? ' rv-rep-field-wide' : '') . '">';
        $h .= '<label>' . esc_html($slabel) . '</label>';
        switch ($stype) {
            case 'textarea':
                $h .= '<textarea name="' . esc_attr($fname) . '" rows="2">' . esc_textarea((string) $val) . '</textarea>';
                break;
            case 'lines':
                $lv = is_array($val) ? implode("\n", array_map('strval', $val)) : (string) $val;
                $h .= '<textarea name="' . esc_attr($fname) . '" rows="3" class="rv-lines" placeholder="' . esc_attr__('One item per line', 'sage') . '">' . esc_textarea($lv) . '</textarea>';
                break;
            case 'url':
                $h .= '<input type="url" name="' . esc_attr($fname) . '" value="' . esc_attr((string) $val) . '" placeholder="https://…  or  /page/">';
                break;
            case 'select':
                $choices = is_array($sf[3] ?? null) ? $sf[3] : [];
                $cur     = (string) $val !== '' ? (string) $val : (string) array_key_first($choices);
                $h .= '<select name="' . esc_attr($fname) . '">';
                foreach ($choices as $cv => $cl) {
                    $h .= '<option value="' . esc_attr((string) $cv) . '"' . selected($cur, (string) $cv, false) . '>' . esc_html((string) $cl) . '</option>';
                }
                $h .= '</select>';
                break;
            case 'checkbox':
                // Hidden 0 + checkbox 1 so an unchecked box still posts a value.
                $h .= '<span class="rv-rep-cb"><input type="hidden" name="' . esc_attr($fname) . '" value="0">'
                    . '<input type="checkbox" name="' . esc_attr($fname) . '" value="1"' . checked((string) $val, '1', false) . '></span>';
                break;
            default:
                $h .= '<input type="text" name="' . esc_attr($fname) . '" value="' . esc_attr((string) $val) . '">';
        }
        $h .= '</div>';
    }

    $h .= '</div></div>';
    return $h;
}

/**
 * One field card in the visual form builder. Carries the same input names as a
 * normal repeater row (rv_f_cform_fields[i][label|type|placeholder|required|
 * width|choices]) so the existing save path handles it unchanged.
 */
function render_fb_card(string $name, $index, array $sub, array $row): string
{
    $subByKey = [];
    foreach ($sub as $sf) {
        $subByKey[$sf[0]] = $sf;
    }

    $label = (string) ($row['label'] ?? '');
    $type  = (string) ($row['type'] ?? 'text');
    $ph    = (string) ($row['placeholder'] ?? '');
    $req   = ((string) ($row['required'] ?? '') === '1');
    $width = (string) ($row['width'] ?? 'full');
    if (! in_array($width, ['full', 'half', 'third'], true)) {
        $width = 'full';
    }
    $choices = $row['choices'] ?? [];
    if (is_array($choices)) {
        $choices = implode("\n", array_map('strval', $choices));
    }

    $base        = $name . '[' . $index . ']';
    $typeChoices = is_array($subByKey['type'][3] ?? null) ? $subByKey['type'][3] : [];

    $h  = '<div class="rv-fb-card rv-fb--' . esc_attr($width) . '" data-width="' . esc_attr($width) . '">';
    $h .= '<div class="rv-fb-bar">';
    $h .= '<span class="rv-fb-grip" title="' . esc_attr__('Drag to reorder', 'sage') . '" aria-hidden="true">⠿</span>';
    $h .= '<input type="text" class="rv-fb-label" name="' . esc_attr($base . '[label]') . '" value="' . esc_attr($label) . '" placeholder="' . esc_attr__('Field label', 'sage') . '">';
    $h .= '<span class="rv-fb-w" role="group" aria-label="' . esc_attr__('Column width', 'sage') . '">';
    foreach (['full' => __('Full', 'sage'), 'half' => __('½', 'sage'), 'third' => __('⅓', 'sage')] as $w => $lbl) {
        $on = ($w === $width) ? ' is-on' : '';
        $h .= '<button type="button" class="rv-fb-wbtn' . $on . '" data-w="' . esc_attr($w) . '" title="' . esc_attr($w) . '">' . esc_html($lbl) . '</button>';
    }
    $h .= '</span>';
    $h .= '<button type="button" class="rv-fb-cog" title="' . esc_attr__('Field settings', 'sage') . '" aria-label="' . esc_attr__('Field settings', 'sage') . '">⚙</button>';
    $h .= '<button type="button" class="rv-fb-del" title="' . esc_attr__('Remove field', 'sage') . '" aria-label="' . esc_attr__('Remove field', 'sage') . '">✕</button>';
    $h .= '<input type="hidden" class="rv-fb-width" name="' . esc_attr($base . '[width]') . '" value="' . esc_attr($width) . '">';
    $h .= '</div>';
    // Proportion meter: a quick visual of the column width the field will take.
    $h .= '<span class="rv-fb-meter" aria-hidden="true"></span>';

    $h .= '<div class="rv-fb-body" hidden>';
    $h .= '<label>' . esc_html__('Type', 'sage') . '</label>';
    $h .= '<select class="rv-fb-type" name="' . esc_attr($base . '[type]') . '">';
    foreach ($typeChoices as $cv => $cl) {
        $h .= '<option value="' . esc_attr((string) $cv) . '"' . selected($type, (string) $cv, false) . '>' . esc_html((string) $cl) . '</option>';
    }
    $h .= '</select>';
    $h .= '<label>' . esc_html__('Placeholder', 'sage') . '</label>';
    $h .= '<input type="text" name="' . esc_attr($base . '[placeholder]') . '" value="' . esc_attr($ph) . '">';
    $h .= '<label class="rv-fb-check"><input type="hidden" name="' . esc_attr($base . '[required]') . '" value="0"><input type="checkbox" name="' . esc_attr($base . '[required]') . '" value="1"' . checked($req, true, false) . '> ' . esc_html__('Required', 'sage') . '</label>';
    $h .= '<label>' . esc_html__('Dropdown options — one per line', 'sage') . '</label>';
    $h .= '<textarea class="rv-fb-choices" name="' . esc_attr($base . '[choices]') . '" rows="3">' . esc_textarea($choices) . '</textarea>';
    $h .= '</div>';

    $h .= '</div>';
    return $h;
}

/** The visual form-builder canvas: draggable field cards laid out in columns. */
function render_form_builder(string $name, array $rows, array $sub): string
{
    $h  = '<p class="rv-ghint">' . esc_html__('Drag the ⠿ handle to reorder fields. Use Full / ½ / ⅓ to set each field’s column width, and the ⚙ to edit its type and options. Removing every field restores the default set.', 'sage') . '</p>';
    $h .= '<div class="rv-fb" data-rep-name="' . esc_attr($name) . '">';
    $h .= '<div class="rv-fb-canvas">';
    foreach ($rows as $i => $row) {
        $h .= render_fb_card($name, (int) $i, $sub, is_array($row) ? $row : []);
    }
    $h .= '</div>';
    $h .= '<template class="rv-fb-tpl">' . render_fb_card($name, '__i__', $sub, ['width' => 'full', 'type' => 'text']) . '</template>';
    $h .= '<button type="button" class="button rv-fb-add">＋ ' . esc_html__('Add field', 'sage') . '</button>';
    $h .= '</div>';
    return $h;
}

function render_page_fields_box(\WP_Post $post): void
{
    $key = page_template_key($post->ID);
    $map = page_field_map();

    if (empty($map[$key])) {
        echo '<p>' . esc_html__('This page uses the default template, which has no editable theme fields. Choose a page template in the sidebar and click Update to edit its content here. (The site\'s homepage automatically uses the Front Page fields.)', 'sage') . '</p>';
        return;
    }

    wp_nonce_field('rv_page_fields', 'rv_page_fields_nonce');

    if ($key === 'template-tools.blade.php') {
        echo '<p class="rv-ghint" style="margin:0 0 1rem">' . esc_html__('This page is edited only in these fields. The WordPress block editor is hidden, and any leftover block content is not shown on the live page.', 'sage') . '</p>';
    }

    $permalink = get_permalink($post->ID);
    $canPreview = ! empty($permalink) && $post->post_status === 'publish';

    echo '<style>
        .rv-pf-layout{display:flex;gap:1.5rem;align-items:flex-start;flex-wrap:wrap}
        .rv-fields{flex:1 1 340px;min-width:0;max-width:660px}
        .rv-fields h4{margin:1.4em 0 .3em;padding-bottom:.3em;border-bottom:1px solid #e0e0e0;font-size:12px;text-transform:uppercase;letter-spacing:.06em;color:#50575e}
        .rv-pf-acc{border:1px solid #dcdcde;border-radius:6px;margin:.55em 0;background:#fff;overflow:hidden}
        .rv-pf-acc>summary{list-style:none;cursor:pointer;padding:.7em .85em;font-size:12px;text-transform:uppercase;letter-spacing:.06em;color:#1d2327;font-weight:600;background:#f6f7f7;display:flex;align-items:center;gap:.55em;user-select:none}
        .rv-pf-acc>summary::-webkit-details-marker{display:none}
        .rv-pf-acc>summary::before{content:"▸";color:#787c82;font-size:11px;transition:transform .15s ease;flex:none}
        .rv-pf-acc[open]>summary::before{transform:rotate(90deg)}
        .rv-pf-acc>summary:hover{background:#eef0f1}
        .rv-pf-acc>summary:focus-visible{outline:2px solid #3858e9;outline-offset:-2px}
        .rv-pf-acc-b{padding:.1em .9em .9em}
        .rv-pf-acc-b label:first-of-type{margin-top:.6em}
        .rv-fields label{display:block;font-weight:600;margin:.9em 0 .25em;font-size:13px}
        .rv-fields input[type=text],.rv-fields textarea{width:100%}
        .rv-fields textarea{min-height:56px}
        .rv-fields input::placeholder,.rv-fields textarea::placeholder{color:#8a8f94;font-style:italic}
        .rv-fields .rv-desc{color:#787c82;font-size:12px;margin:.25em 0 1em}
        .rv-fields .rv-ghint{color:#646970;font-size:12px;line-height:1.5;margin:.15em 0 .9em}
        .rv-fields .rv-urlfield{width:100%}
        .rv-fields .rv-imgfield{display:flex;align-items:center;gap:.75rem;margin:.3em 0 .1em}
        .rv-fields .rv-imgprev{width:104px;height:64px;flex:none;border:1px solid #dcdcde;border-radius:6px;background:#f0f0f1 center/cover no-repeat}
        .rv-fields .rv-imgprev.has{border-color:#c3c4c7}
        .rv-fields .rv-imgctrl{display:flex;align-items:center;gap:.5rem;flex-wrap:wrap}
        .rv-fields .rv-pf-flash{outline:2px solid #3858e9;outline-offset:2px;background:#eef1ff;border-radius:3px;transition:background .3s}
        .rv-fields textarea.rv-lines{min-height:auto;font-family:inherit;line-height:1.5}
        .rv-rep{margin:.4em 0 .2em}
        .rv-rep-rows{display:flex;flex-direction:column;gap:.7rem}
        .rv-rep-row{position:relative;display:flex;gap:.6rem;border:1px solid #dcdcde;border-left:4px solid #c3c4c7;border-radius:6px;background:#fbfbfc;padding:.7rem .8rem}
        .rv-rep-row.rv-rep-flash{border-left-color:#3858e9;background:#eef1ff}
        .rv-rep-tools{display:flex;flex-direction:column;gap:.15rem;flex:none;padding-top:.1rem}
        .rv-rep-tools .button-link{color:#646970;text-decoration:none;font-size:14px;line-height:1.1;padding:.1rem .25rem;border-radius:3px;cursor:pointer}
        .rv-rep-tools .button-link:hover{color:#2271b1;background:#f0f0f1}
        .rv-rep-tools .rv-rep-del:hover{color:#b32d2e}
        .rv-rep-fields{flex:1 1 auto;min-width:0}
        .rv-rep-fields label{margin:.5em 0 .2em;font-size:12px;color:#50575e;font-weight:600}
        .rv-rep-fields label:first-child{margin-top:0}
        .rv-rep-fields input[type=text],.rv-rep-fields input[type=url],.rv-rep-fields textarea,.rv-rep-fields select{width:100%}
        .rv-rep-title{grid-column:1/-1;margin:0 0 .15rem;font-size:13px;font-weight:700;color:#1d2327}
        .rv-rep-compact .rv-rep-fields{display:grid;grid-template-columns:1fr 1fr;gap:.15rem .75rem;align-items:start}
        .rv-rep-compact .rv-rep-field{min-width:0}
        .rv-rep-compact .rv-rep-field-wide{grid-column:1/-1}
        .rv-rep-compact .rv-rep-fields label{margin:.45em 0 .15em}
        .rv-rep-cb{display:inline-flex;align-items:center}
        .rv-rep-cb input[type=checkbox]{margin:0}
        .rv-fields select{max-width:100%}
        .rv-rep-add{margin-top:.7rem!important}
        .rv-fb{margin:.3em 0 .2em}
        .rv-fb-canvas{display:flex;flex-direction:column;gap:.5rem}
        .rv-fb-card{border:1px solid #dcdcde;border-left:4px solid #2271b1;border-radius:6px;background:#fff}
        .rv-fb-card.rv-fb-drag{opacity:.55}
        .rv-fb-ph{border:1px dashed #2271b1;border-radius:6px;background:#f0f6fc;min-height:42px;margin:.1rem 0}
        .rv-fb-bar{display:flex;align-items:center;gap:.4rem;padding:.45rem .5rem;flex-wrap:wrap}
        .rv-fb-grip{cursor:grab;color:#8c8f94;font-size:16px;line-height:1;flex:none;padding:0 .15rem}
        .rv-fb-label{flex:1 1 140px;min-width:120px;font-weight:600}
        .rv-fb-w{display:inline-flex;flex:none;border:1px solid #dcdcde;border-radius:5px;overflow:hidden}
        .rv-fb-wbtn{border:0;background:#f6f7f7;color:#50575e;font-size:11px;padding:.3rem .55rem;cursor:pointer;border-left:1px solid #e0e0e0;line-height:1.4;white-space:nowrap}
        .rv-fb-wbtn:first-child{border-left:0}
        .rv-fb-wbtn.is-on{background:#2271b1;color:#fff}
        .rv-fb-cog,.rv-fb-del{border:0;background:transparent;cursor:pointer;color:#646970;font-size:14px;padding:.2rem .35rem;border-radius:3px;flex:none}
        .rv-fb-cog:hover{background:#f0f0f1;color:#2271b1}
        .rv-fb-del:hover{background:#f0f0f1;color:#b32d2e}
        .rv-fb-meter{display:block;height:4px;margin:0 .5rem .45rem;border-radius:2px;background:#e6e6e6;overflow:hidden}
        .rv-fb-meter::before{content:"";display:block;height:100%;background:#2271b1;width:100%}
        .rv-fb--half .rv-fb-meter::before{width:50%}
        .rv-fb--third .rv-fb-meter::before{width:33.33%}
        .rv-fb-body{padding:.2rem .65rem .65rem;border-top:1px solid #f0f0f1}
        .rv-fb-body label{margin:.5em 0 .2em;font-size:12px;color:#50575e;font-weight:600;display:block}
        .rv-fb-body input[type=text],.rv-fb-body select,.rv-fb-body textarea{width:100%}
        .rv-fb-check{display:flex!important;align-items:center;gap:.4rem;font-weight:600}
        .rv-fb-check input[type=checkbox]{margin:0}
        .rv-fb-add{margin-top:.6rem!important}
        .rv-pf-preview{flex:1 1 430px;min-width:0;position:sticky;top:2rem}
        .rv-pf-bar{display:flex;align-items:center;gap:.6rem;margin-bottom:.45rem;font-size:12px;color:#50575e}
        .rv-pf-bar-t{font-weight:600;text-transform:uppercase;letter-spacing:.06em}
        .rv-pf-status{color:#787c82}
        .rv-pf-open{margin-left:auto;color:#3858e9;text-decoration:none;font-weight:600}
        .rv-pf-stage{border:1px solid #dcdcde;border-radius:8px;overflow:hidden;background:#fff;box-shadow:0 1px 2px rgba(0,0,0,.04)}
        .rv-pf-frame{display:block;width:100%;height:74vh;min-height:420px;border:0;background:#fff}
        .rv-pf-hint{color:#787c82;font-size:12px;margin:.5rem 0 0}
        @media(max-width:1100px){.rv-pf-preview{position:static;flex-basis:100%}}
    </style>';

    echo '<div class="rv-pf-layout">';

    /* ---- Fields column ---- */
    echo '<div class="rv-fields"><p class="rv-desc">' . esc_html__('The greyed text in each box is what\'s on the page now. Type to replace it; leave a box empty to keep the current wording. The preview beside it updates as you type.', 'sage') . '</p>';

    $rvGi = 0;
    foreach ($map[$key] as $group => $fields) {
        // Each section is a collapsible accordion (native <details> — no JS,
        // keyboard-accessible). The first group opens by default.
        echo '<details class="rv-pf-acc"' . ($rvGi === 0 ? ' open' : '') . '>';
        echo '<summary class="rv-pf-acc-h">' . esc_html($group) . '</summary>';
        echo '<div class="rv-pf-acc-b">';
        $rvGi++;
        if ($hint = field_group_hint($group)) {
            echo '<p class="rv-ghint">' . esc_html($hint) . '</p>';
        }
        $groupHasRepeater = false;
        foreach ($fields as $gf) {
            if (($gf[2] ?? '') === 'repeater') {
                $groupHasRepeater = true;
                break;
            }
        }
        if ($groupHasRepeater) {
            echo '<p class="rv-ghint">' . esc_html__('Use “＋ Add item” and the ↑ ↓ ✕ controls to add, reorder, or remove entries. Removing every item restores the built-in default set.', 'sage') . '</p>';
        }
        foreach ($fields as $f) {
            $k     = $f[0];
            $label = $f[1];
            $type  = $f[2];
            $place = $f[3] ?? '';
            $name  = 'rv_f_' . $k;
            $val   = get_post_meta($post->ID, $name, true);
            printf('<label for="%1$s">%2$s</label>', esc_attr($name), esc_html($label));
            switch ($type) {
                case 'textarea':
                    printf('<textarea id="%1$s" name="%1$s" rows="2" placeholder="%3$s">%2$s</textarea>', esc_attr($name), esc_textarea($val), esc_attr($place));
                    break;
                case 'html':
                    printf('<textarea id="%1$s" name="%1$s" rows="3" placeholder="%3$s">%2$s</textarea>', esc_attr($name), esc_textarea($val), esc_attr($place));
                    echo '<p class="rv-ghint" style="margin:.2rem 0 .5rem">' . esc_html__('Basic HTML is allowed here (e.g. <strong>bold</strong>).', 'sage') . '</p>';
                    break;
                case 'url':
                    printf('<input type="url" id="%1$s" name="%1$s" value="%2$s" placeholder="%3$s" class="rv-urlfield">', esc_attr($name), esc_attr($val), esc_attr($place !== '' ? $place : 'https://…  or  /page/'));
                    break;
                case 'image':
                    $has = ($val !== '');
                    echo '<div class="rv-imgfield">';
                    echo '<span class="rv-imgprev' . ($has ? ' has' : '') . '"' . ($has ? ' style="background-image:url(\'' . esc_url($val) . '\')"' : '') . '></span>';
                    echo '<span class="rv-imgctrl">';
                    printf('<input type="hidden" id="%1$s" name="%1$s" value="%2$s">', esc_attr($name), esc_attr($val));
                    echo '<button type="button" class="button rv-img-choose">' . esc_html__('Choose image', 'sage') . '</button> ';
                    echo '<button type="button" class="button-link rv-img-clear"' . ($has ? '' : ' hidden') . '>' . esc_html__('Remove', 'sage') . '</button>';
                    echo '</span></div>';
                    if ($place) {
                        echo '<p class="rv-ghint" style="margin:.25rem 0 .3rem">' . esc_html($place) . '</p>';
                    }
                    break;
                case 'lines':
                    // $place is the default list (array of strings). Show it as the
                    // starting text when nothing is saved yet; each line is one item.
                    $current = is_array($val) ? $val : (is_array($place) ? $place : []);
                    $text    = implode("\n", array_map('strval', $current));
                    printf('<textarea id="%1$s" name="%1$s" rows="%3$d" class="rv-lines">%2$s</textarea>', esc_attr($name), esc_textarea($text), max(3, count($current) + 1));
                    echo '<p class="rv-ghint" style="margin:.2rem 0 .5rem">' . esc_html__('One item per line. Add, remove, or reorder lines to change the list.', 'sage') . '</p>';
                    break;
                case 'select':
                    $choices = is_array($f[4] ?? null) ? $f[4] : [];
                    $cur     = ($val !== '' && $val !== null) ? (string) $val : (string) $place;
                    printf('<select id="%1$s" name="%1$s">', esc_attr($name));
                    foreach ($choices as $cv => $cl) {
                        printf('<option value="%1$s"%2$s>%3$s</option>', esc_attr((string) $cv), selected($cur, (string) $cv, false), esc_html((string) $cl));
                    }
                    echo '</select>';
                    break;
                case 'checkbox':
                    // $place holds the default ('1' = checked by default).
                    $checkedVal = ($val !== '' && $val !== null) ? (string) $val : ((string) $place === '1' ? '1' : '0');
                    printf('<input type="hidden" name="%1$s" value="0"><input type="checkbox" id="%1$s" name="%1$s" value="1"%2$s>', esc_attr($name), checked($checkedVal, '1', false));
                    break;
                case 'repeater':
                    $sub  = $f[4] ?? [];
                    $rows = is_array($val) && $val !== [] ? array_values($val) : (is_array($place) ? $place : []);
                    // Form-builder repeaters render as a visual drag-and-drop canvas.
                    if (($f[5] ?? '') === 'formbuilder') {
                        echo render_form_builder($name, $rows, $sub);
                        break;
                    }
                    $compact = count($sub) >= 5 ? ' rv-rep-compact' : '';
                    echo '<div class="rv-rep' . $compact . '" data-rep-name="' . esc_attr($name) . '">';
                    echo '<div class="rv-rep-rows">';
                    foreach ($rows as $i => $row) {
                        echo render_rep_row($name, (int) $i, $sub, is_array($row) ? $row : []);
                    }
                    echo '</div>';
                    echo '<template class="rv-rep-tpl">' . render_rep_row($name, '__i__', $sub, []) . '</template>';
                    echo '<button type="button" class="button rv-rep-add">＋ ' . esc_html__('Add item', 'sage') . '</button>';
                    echo '</div>';
                    break;
                default:
                    printf('<input type="text" id="%1$s" name="%1$s" value="%2$s" placeholder="%3$s">', esc_attr($name), esc_attr($val), esc_attr($place));
            }
        }
        echo '</div></details>'; // .rv-pf-acc-b / .rv-pf-acc
    }
    echo '</div>'; // .rv-fields

    /* ---- Live preview column ---- */
    if ($canPreview) {
        // Note: the form that posts into the iframe is built in JS on <body> — a
        // <form> here would be an invalid nested form (meta boxes sit inside the
        // editor's own form) and the browser would strip it. URL + nonce ride on
        // data attributes instead.
        printf(
            '<div class="rv-pf-preview" data-pf-action="%1$s" data-pf-nonce="%2$s">',
            esc_url($permalink),
            esc_attr(wp_create_nonce('rv_preview_' . $post->ID))
        );
        echo '<div class="rv-pf-bar"><span class="rv-pf-bar-t">' . esc_html__('Live preview', 'sage') . '</span><span class="rv-pf-status" aria-live="polite"></span><a class="rv-pf-open" href="' . esc_url($permalink) . '" target="_blank" rel="noopener">' . esc_html__('Open ↗', 'sage') . '</a></div>';
        echo '<div class="rv-pf-stage"><iframe id="rv_pf_frame" name="rv_pf_frame" class="rv-pf-frame" title="' . esc_attr__('Live preview', 'sage') . '"></iframe></div>';
        echo '<p class="rv-pf-hint">' . esc_html__('Draft preview — nothing here is saved until you click Update.', 'sage') . '</p>';
        echo '</div>'; // .rv-pf-preview
    } else {
        echo '<div class="rv-pf-preview"><div class="rv-pf-bar"><span class="rv-pf-bar-t">' . esc_html__('Live preview', 'sage') . '</span></div><p class="rv-pf-hint">' . esc_html__('Publish this page to see the live preview here.', 'sage') . '</p></div>';
    }

    echo '</div>'; // .rv-pf-layout
}

add_action('save_post_page', function ($post_id) {
    if (! isset($_POST['rv_page_fields_nonce']) || ! wp_verify_nonce(sanitize_text_field(wp_unslash($_POST['rv_page_fields_nonce'])), 'rv_page_fields')) {
        return;
    }
    if (defined('DOING_AUTOSAVE') && DOING_AUTOSAVE) {
        return;
    }
    if (! current_user_can('edit_page', $post_id)) {
        return;
    }

    $key = page_template_key((int) $post_id);
    if (isset($_POST['page_template']) && $_POST['page_template'] !== 'default') {
        $key = sanitize_text_field(wp_unslash($_POST['page_template']));
    }

    $map = page_field_map();
    if (empty($map[$key])) {
        return;
    }

    foreach ($map[$key] as $fields) {
        foreach ($fields as $f) {
            $k    = $f[0];
            $type = $f[2];
            $name = 'rv_f_' . $k;

            // Repeater / lines store arrays; handle them before the scalar path.
            if ($type === 'lines') {
                $raw   = isset($_POST[$name]) ? (string) wp_unslash($_POST[$name]) : '';
                $items = array_values(array_filter(array_map(
                    static fn ($s) => sanitize_text_field(trim($s)),
                    preg_split('/\r\n|\r|\n/', $raw)
                ), static fn ($s) => $s !== ''));
                if ($items) {
                    update_post_meta($post_id, $name, $items);
                } else {
                    delete_post_meta($post_id, $name);
                }
                continue;
            }

            if ($type === 'repeater') {
                $sub  = $f[4] ?? [];
                $rawRows = (isset($_POST[$name]) && is_array($_POST[$name])) ? wp_unslash($_POST[$name]) : [];
                $clean   = [];
                foreach ($rawRows as $row) {
                    if (! is_array($row)) {
                        continue;
                    }
                    $cleanRow = [];
                    $hasValue = false;
                    foreach ($sub as $sf) {
                        $sk = $sf[0];
                        $st = $sf[2] ?? 'text';
                        $sv = $row[$sk] ?? '';
                        switch ($st) {
                            case 'lines':
                                $cleanRow[$sk] = array_values(array_filter(array_map(
                                    static fn ($s) => sanitize_text_field(trim($s)),
                                    preg_split('/\r\n|\r|\n/', (string) $sv)
                                ), static fn ($s) => $s !== ''));
                                if (! empty($cleanRow[$sk])) {
                                    $hasValue = true;
                                }
                                break;
                            case 'textarea':
                                $cleanRow[$sk] = sanitize_textarea_field((string) $sv);
                                if (trim($cleanRow[$sk]) !== '') {
                                    $hasValue = true;
                                }
                                break;
                            case 'url':
                                $cleanRow[$sk] = esc_url_raw(trim((string) $sv));
                                if ($cleanRow[$sk] !== '') {
                                    $hasValue = true;
                                }
                                break;
                            case 'select':
                                // Setting-type cell — don't let it alone keep an empty row.
                                $cleanRow[$sk] = sanitize_key((string) $sv);
                                break;
                            case 'checkbox':
                                $cleanRow[$sk] = ((string) $sv === '1') ? '1' : '';
                                break;
                            default:
                                $cleanRow[$sk] = sanitize_text_field((string) $sv);
                                if (trim($cleanRow[$sk]) !== '') {
                                    $hasValue = true;
                                }
                        }
                    }
                    if ($hasValue) {
                        $clean[] = $cleanRow;
                    }
                }
                if ($clean) {
                    update_post_meta($post_id, $name, $clean);
                } else {
                    delete_post_meta($post_id, $name);
                }
                continue;
            }

            if (! isset($_POST[$name])) {
                continue;
            }
            $raw = wp_unslash($_POST[$name]);
            switch ($type) {
                case 'textarea':
                    $val = sanitize_textarea_field($raw);
                    break;
                case 'html':
                    $val = wp_kses_post((string) $raw);
                    break;
                case 'url':
                case 'image':
                    $val = esc_url_raw(trim((string) $raw));
                    break;
                case 'select':
                    $val = sanitize_key((string) $raw);
                    break;
                case 'checkbox':
                    // Store an explicit 0/1 so an unchecked box beats an on-by-default.
                    $val = ((string) $raw === '1') ? '1' : '0';
                    break;
                default:
                    $val = sanitize_text_field($raw);
            }
            if ($val === '') {
                delete_post_meta($post_id, $name);
            } else {
                update_post_meta($post_id, $name, $val);
            }
        }
    }
});

/* --------------------------------------------------------------- seed fields */

/**
 * Pre-fill each page's editable fields with the copy that's currently on the
 * page, so the "Page content" boxes open ready to edit instead of blank. Only
 * fills empty fields — never overwrites wording you've already customized —
 * and only writes the value when it matches the built-in default, so the live
 * pages render exactly the same. Idempotent.
 */
function seed_page_fields(): void
{
    $map = page_field_map();
    $pages = get_posts([
        'post_type'   => 'page',
        'post_status' => 'any',
        'numberposts' => -1,
        'fields'      => 'ids',
    ]);

    foreach ($pages as $page_id) {
        $key = page_template_key((int) $page_id);
        if (empty($map[$key])) {
            continue;
        }
        foreach ($map[$key] as $fields) {
            foreach ($fields as $f) {
                $type = $f[2] ?? 'text';
                if (in_array($type, ['url', 'image', 'lines', 'repeater'], true)) {
                    continue; // pickers / list editors pre-fill from the map, not seeded meta
                }
                $current = $f[3] ?? '';
                if ($current === '') {
                    continue; // nothing to pre-fill (e.g. optional phone number)
                }
                $name = 'rv_f_' . $f[0];
                if (get_post_meta($page_id, $name, true) === '') {
                    update_post_meta($page_id, $name, $current);
                }
            }
        }
    }
}

/**
 * Run the page-field pre-fill once per version. Bump the version to re-fill
 * newly-added fields (existing, non-empty fields are left untouched).
 */
add_action('admin_init', function () {
    if (get_option('rv_page_fields_seed_v') === '1') {
        return;
    }
    if (! current_user_can('edit_pages')) {
        return;
    }
    seed_page_fields();
    update_option('rv_page_fields_seed_v', '1');
});

/**
 * Free Tools is a field-driven template. Hide Gutenberg / classic content so
 * leftover blocks cannot silently reappear, and strip post_content on save.
 */
add_filter('use_block_editor_for_post', function ($use, $post) {
    if ($post instanceof \WP_Post && $post->post_type === 'page'
        && get_page_template_slug($post->ID) === 'template-tools.blade.php') {
        return false;
    }
    return $use;
}, 10, 2);

add_action('admin_head', function () {
    $screen = get_current_screen();
    if (! $screen || $screen->base !== 'post' || $screen->post_type !== 'page') {
        return;
    }
    global $post;
    if (! $post instanceof \WP_Post || get_page_template_slug($post->ID) !== 'template-tools.blade.php') {
        return;
    }
    echo '<style id="rv-hide-tools-editor">#postdivrich,.block-editor,#post-status-info{display:none!important}</style>';
});

add_filter('wp_insert_post_data', function ($data, $postarr) {
    if (($data['post_type'] ?? '') !== 'page') {
        return $data;
    }
    $tpl = (string) ($postarr['page_template'] ?? '');
    if ($tpl === '' && ! empty($postarr['ID'])) {
        $tpl = (string) get_page_template_slug((int) $postarr['ID']);
    }
    if ($tpl === 'template-tools.blade.php') {
        $data['post_content'] = '';
    }
    return $data;
}, 20, 2);
