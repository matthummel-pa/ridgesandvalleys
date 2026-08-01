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
    if (empty($rows)) {
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

/** Which field set applies to a page (front page detected via the reading setting). */
function page_template_key(int $post_id): string
{
    if ($post_id && (int) get_option('page_on_front') === $post_id) {
        return 'front-page.blade.php';
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
            ['hero_sub', __('Hero subtitle', 'sage'), 'textarea', $sub],
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
            __('Hero', 'sage') => array_merge($hero(
                __('Gettysburg · Web design · Local growth', 'sage'),
                __('A better website, without the agency', 'sage'),
                __('drag.', 'sage'),
                __('Fast, accessible WordPress websites for Gettysburg, Adams County, and South Central PA businesses — planned with AI, refined by an experienced local developer, and launched without months of meetings.', 'sage')
            ), [
                // Optional hero side columns. Fill either/both to make the hero 2- or
                // 3-column; leave blank for the normal single-column hero.
                ['hero_col2_title', __('Hero column 2 · heading (optional)', 'sage'), 'text', ''],
                ['hero_col2_body', __('Hero column 2 · content (optional, HTML allowed)', 'sage'), 'html', ''],
                ['hero_col3_title', __('Hero column 3 · heading (optional)', 'sage'), 'text', ''],
                ['hero_col3_body', __('Hero column 3 · content (optional, HTML allowed)', 'sage'), 'html', ''],
            ]),
            __('Hero buttons', 'sage') => hero_button_rows(),
            __('Hero typography', 'sage') => hero_typography_rows(),
            __('Problems section', 'sage') => [
                ['problems_eyebrow', __('Eyebrow', 'sage'), 'text', __('If this sounds familiar', 'sage')],
                ['problems_title', __('Heading (before accent)', 'sage'), 'text', __('Your site should', 'sage')],
                ['problems_accent', __('Accent word', 'sage'), 'text', __('help', 'sage')],
                ['problems_after', __('Heading (after accent)', 'sage'), 'text', __('people act.', 'sage')],
                ['problem1_title', __('Card 1 · title', 'sage'), 'text', __('Hard to find the basics', 'sage')],
                ['problem1_text', __('Card 1 · text', 'sage'), 'textarea', __('Hours, directions, and prices are buried — or living on Facebook where you don\'t control them.', 'sage')],
                ['problem2_title', __('Card 2 · title', 'sage'), 'text', __('Dated on a phone', 'sage')],
                ['problem2_text', __('Card 2 · text', 'sage'), 'textarea', __('Most visitors show up on mobile, and the current site fights them the whole way.', 'sage')],
                ['problem3_title', __('Card 3 · title', 'sage'), 'text', __('Risky to update', 'sage')],
                ['problem3_text', __('Card 3 · text', 'sage'), 'textarea', __('Changing a price or a photo feels like it might break the whole thing.', 'sage')],
            ],
            __('Packages section', 'sage') => [
                ['pkg_eyebrow', __('Eyebrow', 'sage'), 'text', __('Clear scope. Fast build. No mystery.', 'sage')],
                ['pkg_title', __('Heading', 'sage'), 'text', __('Three ways to', 'sage')],
                ['pkg_accent', __('Accent word', 'sage'), 'text', __('start.', 'sage')],
                ['pkg1_name', __('Package 1 · name', 'sage'), 'text', __('Website Rescue', 'sage')],
                ['pkg1_price', __('Package 1 · price', 'sage'), 'text', '$950'],
                ['pkg1_desc', __('Package 1 · description', 'sage'), 'textarea', __('Audit, cleanup, broken links, mobile, speed & SEO fixes.', 'sage')],
                ['pkg2_flag', __('Package 2 · flag', 'sage'), 'text', __('Most popular', 'sage')],
                ['pkg2_name', __('Package 2 · name', 'sage'), 'text', __('Local Launch', 'sage')],
                ['pkg2_price', __('Package 2 · price', 'sage'), 'text', '$2,750'],
                ['pkg2_desc', __('Package 2 · description', 'sage'), 'textarea', __('Up to 5 pages, local SEO, analytics, accessibility, one revision.', 'sage')],
                ['pkg3_name', __('Package 3 · name', 'sage'), 'text', __('Growth Site', 'sage')],
                ['pkg3_price', __('Package 3 · price', 'sage'), 'text', '$4,500'],
                ['pkg3_desc', __('Package 3 · description', 'sage'), 'textarea', __('8–12 pages, migration, booking or ecommerce, advanced forms.', 'sage')],
                ['pkg4_name', __('Package 4 · name', 'sage'), 'text', __('Care & Grow', 'sage')],
                ['pkg4_price', __('Package 4 · price', 'sage'), 'text', '$179'],
                ['pkg4_desc', __('Package 4 · description', 'sage'), 'textarea', __('Updates, backups, security, small changes, reporting.', 'sage')],
                ['pkg_cta', __('“Compare all services” button label', 'sage'), 'text', __('Compare all services', 'sage')],
            ],
            __('Rooted section', 'sage') => [
                ['rooted_eyebrow', __('Eyebrow', 'sage'), 'text', __('Rooted in the ridges & valleys', 'sage')],
                ['rooted_title', __('Heading', 'sage'), 'text', __('Built here.', 'sage')],
                ['rooted_accent', __('Accent word', 'sage'), 'text', __('Supported', 'sage')],
                ['rooted_text', __('Paragraph', 'sage'), 'textarea', __('From the Cumberland Valley to South Mountain, Michaux, and the fields around Gettysburg — this is home. I build for the businesses that make this place what it is, and I\'m proud of the history that comes with the address.', 'sage')],
                ['rooted_chips', __('Region chips (comma-separated)', 'sage'), 'text', 'Gettysburg, Adams County, Cumberland Valley, South Mountain, Michaux'],
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
                ['hero_btn1_url', __('Hero button 1 link (“Plan my site”)', 'sage'), 'url', __('e.g. /contact/', 'sage')],
                ['hero_btn2_url', __('Hero button 2 link (“See the process”)', 'sage'), 'url', __('e.g. /services/', 'sage')],
                ['cta_button_url', __('Closing CTA button link', 'sage'), 'url', __('e.g. /contact/', 'sage')],
                ['pkg_cta_url', __('“Compare all services” button link', 'sage'), 'url', __('e.g. /services/', 'sage')],
            ],
            __('Hero trust line', 'sage') => [
                ['hero_trust', __('Trust line under the buttons', 'sage'), 'text', __('15+ yrs building for the web · Accessibility-first · WordPress · Local support', 'sage')],
            ],
            __('Included in every build', 'sage') => [
                ['included_eyebrow', __('Eyebrow', 'sage'), 'text', __('No surprises', 'sage')],
                ['included_title', __('Heading (before accent)', 'sage'), 'text', __('Included in', 'sage')],
                ['included_accent', __('Accent word', 'sage'), 'text', __('every', 'sage')],
                ['included_title_end', __('Heading (after accent)', 'sage'), 'text', __('build.', 'sage')],
                ['included_items', __('Cards', 'sage'), 'repeater', [
                    ['title' => __('Accessibility-first', 'sage'), 'text' => __('Built to WCAG 2.1 AA — readable contrast, keyboard navigation, and screen-reader-friendly structure on every page.', 'sage')],
                    ['title' => __('Mobile-first', 'sage'), 'text' => __('Designed for the phone first, because that\'s where most of your visitors actually are.', 'sage')],
                    ['title' => __('You own everything', 'sage'), 'text' => __('Your domain, your hosting account, your site. No lock-in, and you can leave a care plan anytime.', 'sage')],
                    ['title' => __('Found locally', 'sage'), 'text' => __('Google Business Profile setup, on-page SEO, and the local foundations that put you on the map.', 'sage')],
                    ['title' => __('Fast & secure', 'sage'), 'text' => __('Lean, page-builder-free code, HTTPS, backups, and a hosting setup tuned to load quickly.', 'sage')],
                    ['title' => __('A real training handoff', 'sage'), 'text' => __('A short walkthrough (and a video) so you can update the few things you\'ll actually touch.', 'sage')],
                ], [
                    ['title', __('Card title', 'sage'), 'text'],
                    ['text', __('Card text', 'sage'), 'textarea'],
                ]],
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
                ['hero_eyebrow', __('Eyebrow', 'sage'), 'text', __('Ridges & Valleys Studio', 'sage')],
                ['hero_title', __('Heading', 'sage'), 'text', __('A family-owned web studio for', 'sage')],
                ['hero_accent', __('Accent word', 'sage'), 'text', __('South Central PA.', 'sage')],
                ['about_intro', __('Intro paragraph', 'sage'), 'textarea', __('We\'re a family-owned web studio in Gettysburg, building fast, accessible websites and local SEO for small businesses across Adams County and South Central PA — from Hanover and New Oxford to Littlestown, York, Chambersburg, and every town in between. If you\'re a local business, we\'d love to work with you.', 'sage')],
                ['about_invite', __('Invitation paragraph (partners & welcome)', 'sage'), 'textarea', __('We\'re just as happy to team up with other marketing and design studios, freelancers, and photographers on a project. We\'re new to the neighborhood and all in — so come say hello, and let\'s build something good, right here at home.', 'sage')],
                ['hero_btn', __('Hero button label', 'sage'), 'text', __('Work with us', 'sage')],
                ['about_meta', __('Meta line (under the button)', 'sage'), 'text', __('Family-owned · Accessibility-first · Serving Gettysburg & South Central PA', 'sage')],
            ],
            __('The studio', 'sage') => [
                ['bio_eyebrow', __('Eyebrow', 'sage'), 'text', __('The studio', 'sage')],
                ['bio_title', __('Heading (before accent)', 'sage'), 'text', __('Small studio.', 'sage')],
                ['bio_accent', __('Accent phrase', 'sage'), 'text', __('Serious craft.', 'sage')],
                ['bio_p1', __('Paragraph 1', 'sage'), 'html', __('<strong>Ridges & Valleys is an independent, family-owned studio.</strong> No account managers, no page-builder bloat, no months of meetings — just websites that load fast, read clearly, and help local businesses get found and get work.', 'sage')],
                ['bio_p2', __('Paragraph 2', 'sage'), 'html', __('<strong>Every site is accessibility-first.</strong> Clean, standards-based WordPress, strong Core Web Vitals, and WCAG-minded builds, so the result works for everyone, on every device — and tends to rank better while it\'s at it.', 'sage')],
                ['bio_p3', __('Paragraph 3', 'sage'), 'html', __('<strong>Modern tools for speed, human hands for the craft.</strong> AI helps with research and first drafts; the design decisions, the words, the accessibility, and the final build are done by a person. And when a project needs more than a website, we can build that too.', 'sage')],
                ['bio_p4', __('Paragraph 4', 'sage'), 'html', __('<strong>Fixed pricing, full ownership, no lock-in.</strong> You own your domain, your hosting, and your site — take the keys and run it yourself, or keep us on a Care & Grow plan.', 'sage')],
                ['bio_side', __('Capability highlights', 'sage'), 'repeater', [
                    ['title' => __('Build', 'sage'), 'text' => __('Custom WordPress, no page-builder bloat — semantic, fast, and accessible.', 'sage')],
                    ['title' => __('Get found', 'sage'), 'text' => __('Local SEO, Google Business Profile, and map-ready details baked in.', 'sage')],
                    ['title' => __('Go further', 'sage'), 'text' => __('Power Platform apps and automations when a site needs to do more.', 'sage')],
                ], [
                    ['title', __('Label', 'sage'), 'text'],
                    ['text', __('Text', 'sage'), 'textarea'],
                ]],
            ],
            __('The team', 'sage') => [
                ['team_eyebrow', __('Eyebrow', 'sage'), 'text', __('Who runs the studio', 'sage')],
                ['team_title', __('Heading (before accent)', 'sage'), 'text', __('A small,', 'sage')],
                ['team_accent', __('Accent phrase', 'sage'), 'text', __('family team.', 'sage')],
                ['team_intro', __('Intro paragraph', 'sage'), 'textarea', __('Ridges & Valleys is family-owned and run — small on purpose, so every project gets real attention from the people whose name is on it.', 'sage')],
                ['team_members', __('Team members', 'sage'), 'repeater', [
                    ['name' => __('Matt Hummel', 'sage'), 'role' => __('Founder & Lead Developer', 'sage'), 'bio' => __('Fifteen years building for the web — first as a WordPress developer in a university marketing department, now splitting his days between the Microsoft Power Platform and the studio. A problem-solver first and a developer second: accessibility-first WordPress, performance, local SEO, and the occasional app or automation. Gettysburg-based, and here to stay.', 'sage'), 'photo' => ''],
                    ['name' => '', 'role' => __('Studio Assistant & Client Care', 'sage'), 'bio' => __('Keeps projects moving and clients looked after — scheduling, communication, and the behind-the-scenes details that keep every build on track and on time.', 'sage'), 'photo' => ''],
                ], [
                    ['name', __('Name', 'sage'), 'text'],
                    ['role', __('Role', 'sage'), 'text'],
                    ['bio', __('Short bio', 'sage'), 'textarea'],
                    ['photo', __('Photo URL (optional)', 'sage'), 'url'],
                ]],
                ['team_note', __('Note under the team', 'sage'), 'textarea', __('As the studio grows, we bring in trusted local specialists — photographers, writers, and more — project by project.', 'sage')],
            ],
            __('Beliefs section', 'sage') => [
                ['beliefs_eyebrow', __('Eyebrow', 'sage'), 'text', __('How we work', 'sage')],
                ['beliefs_title', __('Heading', 'sage'), 'text', __('A few things we', 'sage')],
                ['beliefs_accent', __('Accent word', 'sage'), 'text', __('believe.', 'sage')],
                ['beliefs_items', __('Belief cards', 'sage'), 'repeater', [
                    ['title' => __('Outcomes over features', 'sage'), 'text' => __('Your customers don\'t care about the tech. They care about finding you, calling you, booking you.', 'sage')],
                    ['title' => __('Accessible by default', 'sage'), 'text' => __('A fast, accessible site isn\'t a luxury — it\'s your front door. Everyone should get through it.', 'sage')],
                    ['title' => __('Local and accountable', 'sage'), 'text' => __('You get a real person in the area, not a ticket queue. Proud of the place and the history that comes with it.', 'sage')],
                ], [
                    ['title', __('Card title', 'sage'), 'text'],
                    ['text', __('Card text', 'sage'), 'textarea'],
                ]],
            ],
            __('Credentials / skills', 'sage') => [
                ['creds_eyebrow', __('Eyebrow', 'sage'), 'text', __('What\'s under the hood', 'sage')],
                ['creds_title', __('Heading (before accent)', 'sage'), 'text', __('Fifteen years of', 'sage')],
                ['creds_accent', __('Accent word', 'sage'), 'text', __('range.', 'sage')],
                ['creds_items', __('Skill cards', 'sage'), 'repeater', [
                    ['title' => __('WordPress, done right', 'sage'), 'text' => __('Custom themes and clean, page-builder-free builds — the platform that keeps you in control of your own site.', 'sage')],
                    ['title' => __('Accessibility & front-end', 'sage'), 'text' => __('WCAG 2.1 AA, semantic HTML, performance, and modern CSS/JavaScript that holds up on real devices.', 'sage')],
                    ['title' => __('Business platforms', 'sage'), 'text' => __('Experience with the Microsoft Power Platform and integrations — useful when a website needs to talk to the rest of your operation.', 'sage')],
                ], [
                    ['title', __('Card title', 'sage'), 'text'],
                    ['text', __('Card text', 'sage'), 'textarea'],
                ]],
            ],
            __('Rooted locally', 'sage') => [
                ['local_eyebrow', __('Eyebrow', 'sage'), 'text', __('Gettysburg & Adams County', 'sage')],
                ['local_title', __('Heading (before accent)', 'sage'), 'text', __('A web designer who knows', 'sage')],
                ['local_accent', __('Accent phrase', 'sage'), 'text', __('the ground.', 'sage')],
                ['local_p1', __('Paragraph 1', 'sage'), 'html', __('<strong>Based right here in Gettysburg</strong> — minutes from Lincoln Square and the edge of the National Military Park. That local knowledge shapes every build: I know the difference between the summer tourist rush on Steinwehr Avenue and a year-round Main Street shop up in Biglerville, and I build your site to speak to the customers you actually get.', 'sage')],
                ['local_p2', __('Paragraph 2', 'sage'), 'html', __('<strong>This isn\'t a market I picked off a map.</strong> After fifteen years in Virginia, I moved back to Pennsylvania — my home state — and put down roots in the Gettysburg area three years ago. It\'s home now: it\'s where I\'m raising my family, and where I plan to stay. So when I say I want local businesses here to do well, I mean it about my own neighbors.', 'sage')],
                ['local_p3', __('Paragraph 3', 'sage'), 'html', __('<strong>South Central PA runs on small business</strong> — from the orchards of the Adams County fruit belt to the inns, taverns, and tour companies downtown. I build fast, accessible, mobile-first websites that win local search, so when someone nearby pulls out their phone to decide where to eat, stay, shop, or call, you\'re the one they find.', 'sage')],
                ['local_button', __('Button label', 'sage'), 'text', __('Get found in Gettysburg', 'sage')],
                ['local_highlights', __('Highlight cards', 'sage'), 'repeater', [
                    ['title' => __('In person, not a ticket queue', 'sage'), 'text' => __('A real local you can actually reach — real meetings across Adams County and the towns around it.', 'sage')],
                    ['title' => __('Built for how people search here', 'sage'), 'text' => __('Google Business Profile, local SEO, and map-ready details so you show up for the “near me” searches that matter.', 'sage')],
                    ['title' => __('Tourists and locals, covered', 'sage'), 'text' => __('Sites tuned for both the visitor economy and the neighbors who keep you busy all year.', 'sage')],
                ], [
                    ['title', __('Card title', 'sage'), 'text'],
                    ['text', __('Card text', 'sage'), 'textarea'],
                ]],
            ],
            __('Who I build for', 'sage') => [
                ['serve_eyebrow', __('Eyebrow', 'sage'), 'text', __('Local businesses', 'sage')],
                ['serve_title', __('Heading (before accent)', 'sage'), 'text', __('Made for South Central PA', 'sage')],
                ['serve_accent', __('Accent phrase', 'sage'), 'text', __('Main Street.', 'sage')],
                ['serve_intro', __('Intro paragraph', 'sage'), 'textarea', __('Every kind of local business needs a website that gets found and earns trust. These are the ones I love building for around Gettysburg and Adams County:', 'sage')],
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
                ['serve_areas_eyebrow', __('“Areas served” eyebrow', 'sage'), 'text', __('Areas served', 'sage')],
                ['serve_areas_intro', __('“Areas served” intro', 'sage'), 'text', __('Based in Gettysburg, working with businesses across:', 'sage')],
                ['serve_towns', __('Town chips', 'sage'), 'lines', ['Gettysburg', 'Biglerville', 'Littlestown', 'New Oxford', 'McSherrystown', 'Bonneauville', 'Abbottstown', 'Fairfield', 'Cashtown', 'Arendtsville', 'East Berlin', 'York Springs', 'Aspers', 'Hanover']],
                ['serve_note', __('Footnote', 'sage'), 'textarea', __('Plus the surrounding townships and small businesses throughout Adams, York, and Franklin counties. Not in the neighborhood? Most of my work happens over a call and a shared screen — distance is rarely a dealbreaker.', 'sage')],
            ],
            __('Quote', 'sage') => [
                ['quote_text', __('Quote', 'sage'), 'textarea', __('“AI handles the blank page. Matt handles the judgment.”', 'sage')],
                ['quote_who', __('Attribution', 'sage'), 'text', __('The studio, in one line', 'sage')],
            ],
            __('Media & links', 'sage') => [
                ['about_image', __('Intro photo', 'sage'), 'image', __('The aerial photo beside the intro. Built-in until you choose one.', 'sage')],
                ['hero_btn_url', __('Intro button link (“Work with me”)', 'sage'), 'url', __('e.g. /contact/', 'sage')],
            ],
        ],

        'template-services.blade.php' => [
            __('Hero', 'sage') => $hero(
                __('Services', 'sage'),
                __('Websites that earn their', 'sage'),
                __('keep.', 'sage'),
                __('Every package leads with one outcome — more calls, more bookings, easier-to-find hours — not a feature list. Fixed scope, honest pricing, no jargon.', 'sage')
            ),
            __('Packages', 'sage') => [
                ['plans_eyebrow', __('Eyebrow', 'sage'), 'text', __('Simple, fixed pricing', 'sage')],
                ['plans_title', __('Heading (before accent)', 'sage'), 'text', __('Pick the plan that fits', 'sage')],
                ['plans_accent', __('Accent phrase', 'sage'), 'text', __('where you are.', 'sage')],
                ['plans_intro', __('Intro paragraph', 'sage'), 'textarea', __('Every package is one fixed price, agreed up front — no hourly meter, no surprise invoices. Start with a rescue, launch a full local site, or keep things growing after. Not sure which fits? Tell me about your business and I\'ll point you to the right one.', 'sage')],
                ['packages_items', __('Package cards', 'sage'), 'repeater', [
                    ['name' => __('Website Rescue', 'sage'), 'price' => __('$950–$1,500', 'sage'), 'flag' => '', 'desc' => __('For a site that\'s mostly there. Audit, content cleanup, broken links, mobile fixes, speed and SEO improvements.', 'sage'), 'features' => [__('Speed + mobile fixes', 'sage'), __('Refreshed design + content', 'sage'), __('Accessibility + SEO cleanup', 'sage')]],
                    ['name' => __('Local Launch', 'sage'), 'price' => __('$2,750–$3,750', 'sage'), 'flag' => __('Most popular', 'sage'), 'desc' => __('Up to 5 pages, contact form, local SEO foundations, analytics, accessibility review, one revision.', 'sage'), 'features' => [__('Custom design + copy', 'sage'), __('Google Business Profile setup', 'sage'), __('Launch in 7–10 days', 'sage')]],
                    ['name' => __('Growth Site', 'sage'), 'price' => __('$4,500+', 'sage'), 'flag' => '', 'desc' => __('8–12 pages, content migration, booking or ecommerce integration, advanced forms and stronger SEO.', 'sage'), 'features' => [__('Everything in Local Launch', 'sage'), __('Nearby-town pages', 'sage'), __('Blog + lead capture', 'sage')]],
                    ['name' => __('Care & Grow', 'sage'), 'price' => __('$179–$349/mo', 'sage'), 'flag' => '', 'desc' => __('Updates, backups, security checks, small content changes, reporting, priority support.', 'sage'), 'features' => [__('Managed hosting + backups', 'sage'), __('Monthly edits + updates', 'sage'), __('Ongoing SEO + reporting', 'sage')]],
                ], [
                    ['name', __('Package name', 'sage'), 'text'],
                    ['price', __('Price', 'sage'), 'text'],
                    ['flag', __('Badge (e.g. “Most popular” — leave blank for none)', 'sage'), 'text'],
                    ['desc', __('Description', 'sage'), 'textarea'],
                    ['features', __('Feature list (one per line)', 'sage'), 'lines'],
                ]],
            ],
            __('Package detail cards', 'sage') => [
                ['svc_incl_eyebrow', __('Eyebrow', 'sage'), 'text', __('The fine print, up front', 'sage')],
                ['svc_incl_title', __('Heading (before accent)', 'sage'), 'text', __('What every build', 'sage')],
                ['svc_incl_accent', __('Accent word', 'sage'), 'text', __('includes.', 'sage')],
                ['svc_detail_items', __('Detail cards', 'sage'), 'repeater', [
                    ['title' => __('What\'s always included', 'sage'), 'text' => __('Accessibility-first build, mobile-first layout, local SEO basics, analytics, and a training handoff.', 'sage')],
                    ['title' => __('What we don\'t do (yet)', 'sage'), 'text' => __('No paid ads, no full social management, no giant content retainers. We do websites, well.', 'sage')],
                    ['title' => __('The guardrails', 'sage'), 'text' => __('One decision-maker, one consolidated revision round, feedback within two business days, final payment before launch.', 'sage')],
                ], [
                    ['title', __('Card title', 'sage'), 'text'],
                    ['text', __('Card text', 'sage'), 'textarea'],
                ]],
            ],
            __('Local SEO section', 'sage') => [
                ['seo_eyebrow', __('Eyebrow', 'sage'), 'text', __('Get found in Gettysburg', 'sage')],
                ['seo_title', __('Heading (before accent)', 'sage'), 'text', __('Local SEO that puts you', 'sage')],
                ['seo_accent', __('Accent phrase', 'sage'), 'text', __('on the map.', 'sage')],
                ['seo_intro', __('Intro paragraph', 'sage'), 'html', __('<strong>Local SEO decides whether they find you.</strong> When a neighbor or visitor reaches for their phone and searches “web designer near me,” “best breakfast in Gettysburg,” or “plumber in Adams County,” you either show up — or the competitor three listings up does. It\'s the difference between a website that just exists and one that actually brings people through your door.', 'sage')],
                ['seo_items', __('SEO cards', 'sage'), 'repeater', [
                    ['title' => __('Google Business Profile', 'sage'), 'text' => __('Claimed, verified, and optimized — categories, hours, photos, and posts that help you show up in Maps and the local pack.', 'sage')],
                    ['title' => __('Local keyword research', 'sage'), 'text' => __('The exact terms people around here actually type — by town, by service, by season — built right into your pages.', 'sage')],
                    ['title' => __('Location & service-area pages', 'sage'), 'text' => __('Dedicated, indexable pages for Gettysburg and the nearby towns you serve, so you can rank in more than one place.', 'sage')],
                    ['title' => __('LocalBusiness schema', 'sage'), 'text' => __('Structured data that tells Google exactly what you are, where you are, and what you offer.', 'sage')],
                    ['title' => __('NAP consistency', 'sage'), 'text' => __('Your name, address, and phone matched across your site and listings — the signal local search trusts most.', 'sage')],
                    ['title' => __('Reviews that show up', 'sage'), 'text' => __('A simple way to earn reviews and display them, so new customers see the proof before they ever call.', 'sage')],
                    ['title' => __('Built for the map pack', 'sage'), 'text' => __('Optimized to compete for the coveted top-three local results — not buried on page two.', 'sage')],
                    ['title' => __('Fast & mobile-first', 'sage'), 'text' => __('Local search happens on phones. A site that loads instantly keeps you in the running.', 'sage')],
                ], [
                    ['title', __('Card title', 'sage'), 'text'],
                    ['text', __('Card text', 'sage'), 'textarea'],
                ]],
                ['seo_note', __('Note under the cards', 'sage'), 'text', __('Baked into every Local Launch and Growth Site — and available as a standalone tune-up for a site you already have.', 'sage')],
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
                ['after_eyebrow', __('Eyebrow', 'sage'), 'text', __('After launch', 'sage')],
                ['after_title', __('Heading (before accent)', 'sage'), 'text', __('Launch day isn\'t', 'sage')],
                ['after_accent', __('Accent word', 'sage'), 'text', __('goodbye.', 'sage')],
                ['after_items', __('Cards', 'sage'), 'repeater', [
                    ['title' => __('A workmanship warranty', 'sage'), 'text' => __('If something I built breaks in the first 30 days, I fix it — no charge, no debate.', 'sage')],
                    ['title' => __('Optional care plans', 'sage'), 'text' => __('Updates, backups, security, small edits, and reporting from $179/mo — cancel anytime, and you keep the site.', 'sage')],
                    ['title' => __('You\'re never stuck', 'sage'), 'text' => __('You own the domain, hosting, and site. Want to take it in-house or hand it to someone else? It\'s yours to move.', 'sage')],
                ], [
                    ['title', __('Card title', 'sage'), 'text'],
                    ['text', __('Card text', 'sage'), 'textarea'],
                ]],
            ],
            __('Helpful to know (FAQ)', 'sage') => [
                ['sfaq_eyebrow', __('Eyebrow', 'sage'), 'text', __('Helpful to know', 'sage')],
                ['sfaq_title', __('Heading (before accent)', 'sage'), 'text', __('Answers before you', 'sage')],
                ['sfaq_accent', __('Accent phrase', 'sage'), 'text', __('even ask.', 'sage')],
                ['sfaq_items', __('Questions & answers', 'sage'), 'repeater', [
                    ['q' => __('Do I own my website?', 'sage'), 'a' => __('Completely. The domain, the hosting, and every word and pixel are in your name. Want to move it or hand it to someone else someday? It\'s yours to take.', 'sage')],
                    ['q' => __('How fast can it launch?', 'sage'), 'a' => __('Most local sites go live in 7–10 days once I have your content and assets. Bigger builds take a little longer — and you\'ll know the timeline before we start.', 'sage')],
                    ['q' => __('What if I need changes later?', 'sage'), 'a' => __('You get one consolidated revision round during the build. After launch, a Care & Grow plan covers ongoing edits, or you can request changes as you need them.', 'sage')],
                    ['q' => __('Do you handle hosting and domains?', 'sage'), 'a' => __('Yes — I set everything up in your name and can manage it for you, or hand you the keys. Either way, you\'re never locked in.', 'sage')],
                    ['q' => __('What areas do you serve?', 'sage'), 'a' => __('Gettysburg, Adams County, and across South Central PA — Biglerville, Littlestown, New Oxford, Hanover, and beyond. Farther out? Most of the work happens over a call and a shared screen.', 'sage')],
                    ['q' => __('How does payment work?', 'sage'), 'a' => __('A fixed price agreed up front, a deposit to start, and the balance before launch. No surprise invoices, no hourly meter running.', 'sage')],
                    ['q' => __('Will my site be accessible?', 'sage'), 'a' => __('Always. Every build is WCAG-minded and tested on real devices, so it works for everyone — and accessible sites tend to rank better, too.', 'sage')],
                    ['q' => __('Can you fix my current site instead?', 'sage'), 'a' => __('Absolutely. That\'s the Website Rescue — an audit and targeted fixes for speed, mobile, accessibility, and SEO without a full rebuild.', 'sage')],
                ], [
                    ['q', __('Question', 'sage'), 'text'],
                    ['a', __('Answer', 'sage'), 'textarea'],
                ]],
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
            __('Hero', 'sage') => $hero(
                __('Selected work', 'sage'),
                __('Business owners buy', 'sage'),
                __('confidence.', 'sage'),
                __('Most of what\'s here are concept sites I designed and built myself — clearly labeled, fully clickable demos of how I\'d solve a real business problem. As client projects launch, they\'ll appear here the same way: the problem, the approach, and the result.', 'sage')
            ),
            __('Media & links', 'sage') => [
                ['hero_bg', __('Hero background image', 'sage'), 'image', __('Built-in until you choose one.', 'sage')],
                ['hero_btn1_url', __('Hero button link (“Start your project”)', 'sage'), 'url', __('e.g. /contact/', 'sage')],
            ],
            __('Proof stats strip', 'sage') => [
                ['work_stats', __('Stats', 'sage'), 'repeater', [
                    ['value' => '15+', 'unit' => '', 'label' => __('years building for the web', 'sage')],
                    ['value' => '10', 'unit' => '', 'label' => __('full concept sites to explore', 'sage')],
                    ['value' => '7–10', 'unit' => __('days', 'sage'), 'label' => __('typical launch, start to live', 'sage')],
                    ['value' => '100%', 'unit' => '', 'label' => __('yours — domain, hosting, content', 'sage')],
                ], [
                    ['value', __('Big number', 'sage'), 'text'],
                    ['unit', __('Small unit (optional)', 'sage'), 'text'],
                    ['label', __('Label', 'sage'), 'text'],
                ]],
            ],
            __('Value cards', 'sage') => [
                ['work_why_eyebrow', __('Eyebrow', 'sage'), 'text', __('Honest by default', 'sage')],
                ['work_why_title', __('Heading (before accent)', 'sage'), 'text', __('Real work you can', 'sage')],
                ['work_why_accent', __('Accent phrase', 'sage'), 'text', __('actually click.', 'sage')],
                ['work_why_intro', __('Intro paragraph', 'sage'), 'html', __('<strong>I built these myself — one per industry.</strong> Each is a live, working demo you can click around, so you can see exactly how I think, not just a pretty screenshot. No fake clients, no borrowed templates, no stock mockups dressed up as real projects.', 'sage')],
                ['work_why_items', __('Cards (numbered automatically)', 'sage'), 'repeater', [
                    ['kicker' => __('Get found', 'sage'), 'title' => __('Show up when it counts', 'sage'), 'text' => __('Google Business Profile, local SEO, and fast, clean pages so the right people in Adams County find you first — not three competitors down.', 'sage')],
                    ['kicker' => __('Earn trust', 'sage'), 'title' => __('Look as good as you are', 'sage'), 'text' => __('Clear copy, real photos, mobile-first layouts, and accessibility built in — a site that makes a first-time visitor feel safe picking up the phone.', 'sage')],
                    ['kicker' => __('Stay in control', 'sage'), 'title' => __('You own everything', 'sage'), 'text' => __('Your domain, your hosting, your content — and a short training video so you can update hours and prices yourself. No lock-in, no ransom.', 'sage')],
                ], [
                    ['kicker', __('Kicker', 'sage'), 'text'],
                    ['title', __('Card title', 'sage'), 'text'],
                    ['text', __('Card text', 'sage'), 'textarea'],
                ]],
            ],
            __('Case studies header', 'sage') => [
                ['work_cs_eyebrow', __('Eyebrow', 'sage'), 'text', __('The work', 'sage')],
                ['work_cs_title', __('Heading (before accent)', 'sage'), 'text', __('Concepts, built', 'sage')],
                ['work_cs_accent', __('Accent phrase', 'sage'), 'text', __('in full.', 'sage')],
                ['work_cs_intro', __('Intro paragraph', 'sage'), 'textarea', __('These are concept sites I designed and coded from scratch — one for each kind of local business I work with. Anything marked “Concept” is my own self-initiated demo, not a client project. Click any one for the problem it solves, my approach, and a live, working preview.', 'sage')],
                ['work_cats_label', __('“Built for” label', 'sage'), 'text', __('Built for', 'sage')],
                ['work_cats', __('Category chips', 'sage'), 'lines', [__('Hotels & inns', 'sage'), __('Restaurants', 'sage'), __('Retail & shops', 'sage'), __('Tours', 'sage'), __('Real estate', 'sage')]],
                ['work_hint', __('Note under the grid', 'sage'), 'textarea', __('Each write-up follows: Problem → Approach → What it does → Design goals. On concepts these goals are illustrative; on client projects they become measured results.', 'sage')],
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
                __('The things local owners actually ask before we start — every answer right here, no clicking around. Don\'t see yours? Just ask.', 'sage')
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
            __('Header', 'sage') => [
                ['hero_eyebrow', __('Eyebrow', 'sage'), 'text', __('Get in touch', 'sage')],
                ['hero_title', __('Heading', 'sage'), 'text', __('Let\'s build something', 'sage')],
                ['hero_accent', __('Accent word', 'sage'), 'text', __('local.', 'sage')],
                ['contact_intro', __('Intro paragraph', 'sage'), 'textarea', __('Tell me about your business and I\'ll come back with a clear, fixed-scope idea — usually within a business day. No pressure, no jargon, and a real person (me) on the other end.', 'sage')],
                ['contact_note', __('Footnote', 'sage'), 'textarea', __('The project clock starts when this and your assets are complete. Feedback within two business days keeps launch on schedule.', 'sage')],
            ],
            __('Contact details', 'sage') => [
                ['contact_email', __('Email address', 'sage'), 'text', 'matthew.r.hummel@gmail.com'],
                ['contact_phone', __('Phone (leave blank to hide)', 'sage'), 'text', ''],
                ['contact_hours', __('Hours', 'sage'), 'text', __('Mon–Fri, 9am–5pm · evenings by appointment', 'sage')],
            ],
            __('Quick ways to get in touch', 'sage') => [
                ['cway_form_title', __('Form card · title', 'sage'), 'text', __('Fill out the form', 'sage')],
                ['cway_form_desc', __('Form card · text', 'sage'), 'text', __('The fastest way to a fixed-scope quote.', 'sage')],
                ['cway_email_title', __('Email card · title', 'sage'), 'text', __('Email me directly', 'sage')],
                ['cway_call_title', __('Call card · title (shown if phone set)', 'sage'), 'text', __('Call or text', 'sage')],
                ['cway_local_title', __('Location card · title (shown if no phone)', 'sage'), 'text', __('Local to Gettysburg', 'sage')],
                ['cway_local_desc', __('Location card · text', 'sage'), 'text', __('Serving Adams County & South Central PA', 'sage')],
            ],
            __('Form column', 'sage') => [
                ['cform_eyebrow', __('Eyebrow', 'sage'), 'text', __('Project inquiry', 'sage')],
                ['cform_title', __('Heading (before accent)', 'sage'), 'text', __('Tell me about your', 'sage')],
                ['cform_accent', __('Accent word', 'sage'), 'text', __('business.', 'sage')],
            ],
            __('Contact form — fields', 'sage') => [
                ['cform_fields', __('Form fields', 'sage'), 'repeater', [
                    ['label' => __('Your name', 'sage'), 'type' => 'text', 'placeholder' => '', 'required' => '1', 'width' => 'half', 'choices' => []],
                    ['label' => __('Email', 'sage'), 'type' => 'email', 'placeholder' => '', 'required' => '1', 'width' => 'half', 'choices' => []],
                    ['label' => __('Phone', 'sage'), 'type' => 'tel', 'placeholder' => __('Optional', 'sage'), 'required' => '', 'width' => 'full', 'choices' => []],
                    ['label' => __('What can I help with?', 'sage'), 'type' => 'textarea', 'placeholder' => '', 'required' => '1', 'width' => 'full', 'choices' => []],
                ], [
                    ['label', __('Field label', 'sage'), 'text'],
                    ['type', __('Field type', 'sage'), 'select', [
                        'text'     => __('Text', 'sage'),
                        'email'    => __('Email', 'sage'),
                        'tel'      => __('Phone', 'sage'),
                        'url'      => __('Website / URL', 'sage'),
                        'number'   => __('Number', 'sage'),
                        'date'     => __('Date', 'sage'),
                        'textarea' => __('Message (multi-line)', 'sage'),
                        'select'   => __('Dropdown', 'sage'),
                        'checkbox' => __('Checkbox (agree / opt-in)', 'sage'),
                    ]],
                    ['placeholder', __('Placeholder (optional)', 'sage'), 'text'],
                    ['required', __('Required', 'sage'), 'checkbox'],
                    ['width', __('Column width', 'sage'), 'select', [
                        'full'  => __('Full width', 'sage'),
                        'half'  => __('Half (2 per row)', 'sage'),
                        'third' => __('Third (3 per row)', 'sage'),
                    ]],
                    ['choices', __('Dropdown options — one per line', 'sage'), 'lines'],
                ], 'formbuilder'],
            ],
            __('Contact form — design', 'sage') => [
                ['cform_button', __('Submit button label', 'sage'), 'text', __('Send message', 'sage')],
                ['cform_button_full', __('Full-width submit button', 'sage'), 'checkbox', '0'],
                ['cform_button_align', __('Button alignment', 'sage'), 'select', 'left', [
                    'left'   => __('Left', 'sage'),
                    'center' => __('Center', 'sage'),
                    'right'  => __('Right', 'sage'),
                ]],
                ['cform_button_icon', __('Button icon', 'sage'), 'select', 'none', [
                    'none'  => __('None', 'sage'),
                    'send'  => __('Paper plane (send)', 'sage'),
                    'email' => __('Envelope', 'sage'),
                    'chat'  => __('Chat bubble', 'sage'),
                    'arrow' => __('Arrow', 'sage'),
                ]],
                ['cform_button_icon_pos', __('Icon position', 'sage'), 'select', 'before', [
                    'before' => __('Before the label', 'sage'),
                    'after'  => __('After the label', 'sage'),
                ]],
                ['cform_label_style', __('Field labels', 'sage'), 'select', 'top', [
                    'top'    => __('Show labels above fields', 'sage'),
                    'hidden' => __('Hide labels (use placeholders)', 'sage'),
                ]],
                ['cform_field_style', __('Field style', 'sage'), 'select', 'box', [
                    'box'       => __('Boxed (rounded border)', 'sage'),
                    'soft'      => __('Soft (tinted fill)', 'sage'),
                    'underline' => __('Underline only', 'sage'),
                ]],
                ['cform_success', __('Success message', 'sage'), 'textarea', __('Thanks — your message is on its way. I\'ll reply within one business day.', 'sage')],
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
                ['cnext_eyebrow', __('Eyebrow', 'sage'), 'text', __('What happens next', 'sage')],
                ['cnext_items', __('Numbered steps', 'sage'), 'repeater', [
                    ['strong' => __('I reply — usually within a business day.', 'sage'), 'text' => __('A real, personal response, not an auto-reminder.', 'sage')],
                    ['strong' => __('A quick, no-pressure chat.', 'sage'), 'text' => __('A short call or email so I understand exactly what you need.', 'sage')],
                    ['strong' => __('A clear, fixed-scope plan.', 'sage'), 'text' => __('What I\'d build, what it costs, and how long — in writing.', 'sage')],
                ], [
                    ['strong', __('Bold lead-in', 'sage'), 'text'],
                    ['text', __('Rest of the line', 'sage'), 'text'],
                ]],
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
                ['clocal_btn1', __('Primary button label', 'sage'), 'text', __('Start the conversation', 'sage')],
                ['clocal_btn2', __('Secondary button label', 'sage'), 'text', __('Or just email me', 'sage')],
            ],
        ],

        'template-accessibility.blade.php' => [
            __('Hero', 'sage') => $hero(
                __('Accessibility · WCAG 2.1 AA · Section 508', 'sage'),
                __('A front door that opens for', 'sage'),
                __('everyone.', 'sage'),
                __('An accessible website isn\'t a nice-to-have — it\'s how you reach more customers, rank better, and stay on the right side of the law. Here\'s the full federal standard, what it means, and how I build to it on every project.', 'sage')
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
            __('Hero', 'sage') => $hero(
                __('Free website tools · No email required', 'sage'),
                __('Free tools to check your', 'sage'),
                __('website.', 'sage'),
                __('Grade your site, audit your SEO, test accessibility, and scan your security — in seconds, for free, with no signup. Built by a local developer for Gettysburg and South Central PA businesses who want to know where they really stand online.', 'sage')
            ),
            __('Call to action', 'sage') => $cta(
                __('Want a hand with what the tools turned up?', 'sage'),
                __('I fix these for Gettysburg and South Central PA businesses every week. Tell me your site and I\'ll take a real look.', 'sage'),
                __('Get a quote', 'sage')
            ),
        ],

        'template-grader.blade.php' => [
            __('Hero', 'sage') => $hero(
                __('Free website grader', 'sage'),
                __('How good is your website,', 'sage'),
                __('really?', 'sage'),
                __('Enter your URL for an instant, plain-English report card across seven areas — SEO, speed, mobile, readability, security, technical health, and social sharing. Every check explains what it found and why it matters for your business.', 'sage')
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
                __('A deep, plain-English SEO audit of any page — snippet preview, crawlability, keyword usage, structured data, links, and the technical fundamentals. Add a target keyword to see how well the page is built around it.', 'sage')
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
                __('A plain-English security check of any page — HTTPS and HSTS, the modern browser security headers, information leaks, cookie safety, and front-end risks. Every result explains the risk and why it matters for your business.', 'sage')
            ),
            __('Call to action', 'sage') => $cta(
                __('Want your site locked down and kept that way?', 'sage'),
                __('I harden the fixable items above and keep sites patched, backed up, and monitored on a Care & Grow plan.', 'sage'),
                __('Secure my site', 'sage')
            ),
        ],

        'template-email.blade.php' => [
            __('Hero', 'sage') => $hero(
                __('Free email deliverability checker', 'sage'),
                __('Is your email landing in', 'sage'),
                __('spam?', 'sage'),
                __('Enter your domain to check the DNS records that decide whether your email is trusted — SPF, DKIM, and DMARC — and whether anyone can send phishing emails pretending to be your business. Plain English, no signup.', 'sage')
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
                __('A scorecard for the signals that get a local business into Google\'s map pack and turn searchers into calls — your name, address, phone, hours, LocalBusiness schema, maps, and reviews. Built for Gettysburg and South Central PA businesses.', 'sage')
            ),
            __('Call to action', 'sage') => $cta(
                __('Want to own “near me” in Adams County?', 'sage'),
                __('I set up local SEO end to end — on-page signals, Google Business Profile, and town-by-town pages — for Gettysburg and South Central PA businesses.', 'sage'),
                __('Get found locally', 'sage')
            ),
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
        'index.blade.php'                 => ['Read the latest', 'Try the free tools'],
        'template-email.blade.php'        => ['Check my domain', 'Talk to me'],
        'template-faq.blade.php'          => ['Ask your question', 'Jump to the FAQs'],
        'template-grader.blade.php'       => ['Grade my site', 'Talk to me'],
        'template-local.blade.php'        => ['Score my page', 'Talk to me'],
        'template-security.blade.php'     => ['Check my site', 'Talk to me'],
        'template-seo.blade.php'          => ['Check my SEO', 'Talk to me'],
        'template-tools.blade.php'        => ['Browse the tools', 'Talk to me'],
        'template-work.blade.php'         => ['Start your project', 'Explore the concepts'],
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
        __('Hero', 'sage')             => __('The banner at the very top of the page: the small eyebrow label above the title, the main headline, the highlighted accent word, and the sentence beneath.', 'sage'),
        __('Problems section', 'sage') => __('The “if this sounds familiar” block — its heading and the three cards that name what’s frustrating about an outdated site.', 'sage'),
        __('Packages section', 'sage') => __('The pricing line-up: a heading plus four packages, each with a name, a price, and a one-line description.', 'sage'),
        __('Rooted section', 'sage')   => __('The local “built here” block — heading, paragraph, and the comma-separated list of region chips.', 'sage'),
        __('Testimonial', 'sage')      => __('The single highlighted client quote and who said it.', 'sage'),
        __('Call to action', 'sage')   => __('The closing banner that invites visitors to reach out — heading, subtext, and the button label.', 'sage'),
        __('Intro', 'sage')            => __('The opening block: eyebrow, the heading with its accent word, and the introduction paragraph.', 'sage'),
        __('Beliefs section', 'sage')  => __('The heading for the “how I work / a few things I believe” block. The three belief cards themselves are set in the template.', 'sage'),
        __('Quote', 'sage')            => __('The one-line studio quote and its attribution.', 'sage'),
        __('Founding offer', 'sage')   => __('The limited founding-offer banner: eyebrow, heading, the price accent, the paragraph, and the button. The “what’s included” checklist is set in the template.', 'sage'),
        __('Header', 'sage')           => __('The top of the contact page: eyebrow, the heading with its accent word, the intro paragraph, and the small note shown under the form.', 'sage'),
        __('Contact details', 'sage')  => __('Where enquiries go and how people reach you. Leave the phone blank to hide the call/text option; add a number to show it.', 'sage'),
        __('Contact form — fields', 'sage') => __('Build the form itself: add, remove, and reorder fields. For each field set a label, a type, whether it’s required, and its column width (full, half, or third). For a Dropdown, list the options one per line. Remove every field to restore the default Name / Email / Phone / Message set.', 'sage'),
        __('Contact form — design', 'sage') => __('How the form looks: the submit button label and width, whether labels show above the fields or the placeholders stand in for them, the field style, and the message shown after a successful send.', 'sage'),
        __('Contact form — auto-reply', 'sage') => __('Send an automatic confirmation email to the person who submitted the form (uses the email field they filled in). Turn it on, set the subject and message, and optionally include a copy of what they sent.', 'sage'),
        __('Contact form — consent', 'sage') => __('Add a consent / GDPR checkbox above the submit button — useful for opt-in and privacy compliance. Links are allowed in the consent text (e.g. to your privacy policy). Leave “required” on to block submission until it’s ticked.', 'sage'),
        __('Media & links', 'sage')    => __('Swap the section image and point the buttons at any page. Leave a link blank to use the site’s default; leave an image blank to keep the built-in one. Links accept a path like /contact/ or a full https:// URL.', 'sage'),
        __('Included in every build', 'sage') => __('The “included in every build” heading and its cards.', 'sage'),
        __('Process timeline', 'sage') => __('The day-by-day timeline: its heading and each step (day label, title, and one line).', 'sage'),
        __('Towns served', 'sage')     => __('The local “towns served” block — heading, intro, and the list of town chips (one town per line).', 'sage'),
        __('Bio — how I got here', 'sage') => __('The longer story section: heading, the four paragraphs (basic HTML like <strong> is allowed), and the three skill highlight cards.', 'sage'),
        __('The studio', 'sage') => __('The studio-story block: eyebrow, heading with its accent, four paragraphs (basic HTML like <strong> is allowed), and the three capability highlights.', 'sage'),
        __('The team', 'sage') => __('The “who runs the studio” block. Add a card per person with their name, role, short bio, and an optional photo URL (upload the photo to the Media Library, then paste its URL). Leave the photo blank to show a person-silhouette placeholder. Leave a name blank to show role + bio only.', 'sage'),
        __('Credentials / skills', 'sage') => __('The “what’s under the hood” heading and its three skill cards.', 'sage'),
        __('Rooted locally', 'sage')   => __('The big local-story block: heading, three paragraphs, the button label, and the three highlight cards.', 'sage'),
        __('Who I build for', 'sage')  => __('The industries you serve (cards), plus the “areas served” heading, town chips, and footnote.', 'sage'),
        __('Packages', 'sage')         => __('The four package cards. Each has a name, price, description, and a feature list (one feature per line).', 'sage'),
        __('Local SEO section', 'sage') => __('The dark local-SEO block: heading, intro, the grid of SEO cards, and the note beneath.', 'sage'),
        __('Process (services)', 'sage') => __('The services timeline: heading and each day step.', 'sage'),
        __('AI-assisted split', 'sage') => __('The “honest split” heading, the two cards, and the disclosure note.', 'sage'),
        __('Helpful to know (FAQ)', 'sage') => __('The short services FAQ — heading and each question with its answer.', 'sage'),
        __('Process section', 'sage')  => __('The numbered “how it works” flow — heading, intro, each step, and the note beneath. Steps are numbered automatically.', 'sage'),
        __('FAQ list header', 'sage')  => __('The heading and intro that sit above the full FAQ list.', 'sage'),
        __('FAQ items', 'sage')        => __('Every question and answer. Set each row’s Category to group it — questions with the same category appear together, in the order listed here.', 'sage'),
        __('Proof stats strip', 'sage') => __('The dark strip of big numbers. Each stat has a big number, an optional small unit (e.g. “days”), and a label.', 'sage'),
        __('Value cards', 'sage')      => __('The “real work you can actually click” heading and its numbered cards.', 'sage'),
        __('Case studies header', 'sage') => __('The heading above the case-study grid, the “Built for” category chips, and the note beneath.', 'sage'),
        __('Don\'t see your business', 'sage') => __('The heading, the business-type chips, the foundation panel, and its checklist.', 'sage'),
        __('How it goes', 'sage')      => __('The numbered build flow, plus the “what you walk away with” checklist and reassurance line.', 'sage'),
        __('The design craft', 'sage') => __('The heading, the numbered craft cards, and the closing paragraph.', 'sage'),
        __('Areas served (work)', 'sage') => __('The big areas-served block: heading, lede, town chips, note, and the tagline parts (one per line, shown joined with ·).', 'sage'),
        __('Quick ways to get in touch', 'sage') => __('The three quick-contact cards at the top of the page.', 'sage'),
        __('Form column', 'sage')      => __('The heading above the contact form.', 'sage'),
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

    foreach ($sub as $sf) {
        $sk     = $sf[0];
        $slabel = $sf[1];
        $stype  = $sf[2] ?? 'text';
        $fname  = $name . '[' . $index . '][' . $sk . ']';
        $val    = $row[$sk] ?? '';

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
                $h .= '<select name="' . esc_attr($fname) . '">';
                foreach ($choices as $cv => $cl) {
                    $h .= '<option value="' . esc_attr((string) $cv) . '"' . selected((string) $val, (string) $cv, false) . '>' . esc_html((string) $cl) . '</option>';
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
                    echo '<div class="rv-rep" data-rep-name="' . esc_attr($name) . '">';
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
