<?php

/**
 * Bespoke page-layout blocks (server-side rendered):
 *   prt/stat-strip      – stat counter row  (About page)
 *   prt/skills-grid     – skill / feature cards  (About / Services)
 *   prt/timeline        – work-history timeline  (Résumé)
 *   prt/resource-group  – curated link list  (Resources)
 *   prt/cta-band        – call-to-action section
 *   prt/project-card    – single bespoke project card
 *
 * All use render_callback so the theme's CSS variables apply automatically.
 * Editor previews use wp.serverSideRender (REST API).
 */

namespace App;

/* ── Attribute definitions ─────────────────────────────────────────── */

/**
 * Attribute schema for prt/stat-strip.
 *
 * Stats are stored as a single JSON-encoded string attribute (not a nested
 * block attribute array) so the editor can round-trip the whole list through
 * one text control / REST call instead of wiring up per-field attributes.
 *
 * @return array
 */
function prt_stat_strip_attrs(): array
{
    return [
        'stats'   => ['type' => 'string', 'default' => '[{"value":"15+","label":"Years experience"},{"value":"100+","label":"Projects delivered"},{"value":"2","label":"Platforms"},{"value":"100%","label":"Remote-friendly"}]'],
        'columns' => ['type' => 'number', 'default' => 4],
    ];
}

/**
 * Attribute schema for prt/skills-grid (same JSON-string-attribute pattern
 * as prt_stat_strip_attrs() above).
 *
 * @return array
 */
function prt_skills_grid_attrs(): array
{
    return [
        'cards'   => ['type' => 'string', 'default' => '[{"title":"Front-End","body":"HTML, CSS, JavaScript, React, Tailwind, Vite"},{"title":"Back-End","body":"PHP, WordPress, Node.js, REST APIs, Supabase"},{"title":"Accessibility","body":"WCAG 2.1 AA, Core Web Vitals, semantic HTML"}]'],
        'columns' => ['type' => 'number', 'default' => 3],
        'style'   => ['type' => 'string', 'default' => 'default'],
    ];
}

/**
 * Attribute schema for prt/timeline (entries are a JSON-encoded string,
 * same reasoning as prt_stat_strip_attrs() above).
 *
 * @return array
 */
function prt_timeline_attrs(): array
{
    return [
        'entries' => ['type' => 'string', 'default' => '[{"dates":"2021–Present","title":"Senior Power Platform Consultant","org":"Various clients · Remote","body":"Power Apps, Power Automate, SharePoint, M365 integrations."}]'],
    ];
}

/**
 * Attribute schema for prt/resource-group (links are a JSON-encoded string,
 * same reasoning as prt_stat_strip_attrs() above).
 *
 * @return array
 */
function prt_resource_group_attrs(): array
{
    return [
        'heading' => ['type' => 'string', 'default' => 'Resources'],
        'emoji'   => ['type' => 'string', 'default' => '🔗'],
        'links'   => ['type' => 'string', 'default' => '[{"label":"MDN Web Docs","url":"https://developer.mozilla.org/"}]'],
    ];
}

/**
 * Attribute schema for prt/cta-band (a simple call-to-action panel with a
 * heading, body copy, button, and colour variant).
 *
 * @return array
 */
function prt_cta_band_attrs(): array
{
    return [
        'heading' => ['type' => 'string', 'default' => 'Open to select side projects'],
        'body'    => ['type' => 'string', 'default' => "I'm available for freelance work. Let's talk."],
        'btnText' => ['type' => 'string', 'default' => 'Get in touch'],
        'btnUrl'  => ['type' => 'string', 'default' => '/contact/'],
        'variant' => ['type' => 'string', 'default' => 'dark'],
    ];
}

/**
 * Attribute schema for prt/project-card — a single flat set of scalar
 * attributes (unlike the JSON-string lists above) since a card only ever
 * represents one project, not a repeatable collection.
 *
 * @return array
 */
function prt_project_card_attrs(): array
{
    return [
        'heading'   => ['type' => 'string', 'default' => 'Project Title'],
        'excerpt'   => ['type' => 'string', 'default' => 'Short description of what this project does.'],
        'link'      => ['type' => 'string', 'default' => ''],
        'imageUrl'  => ['type' => 'string', 'default' => ''],
        'imageAlt'  => ['type' => 'string', 'default' => ''],
        'tags'      => ['type' => 'string', 'default' => 'React, Tailwind, Supabase'],
        'liveUrl'   => ['type' => 'string', 'default' => ''],
        'githubUrl' => ['type' => 'string', 'default' => ''],
    ];
}

/**
 * Full set of block "supports" so every prt/* section block exposes the native
 * editor controls — background & text colour, gradients, link colour, font
 * size & family, weight/style/letter-spacing/transform, padding/margin/gap, and
 * border colour/radius/width/style — plus alignment and an HTML anchor. The
 * render callbacks apply these via get_block_wrapper_attributes().
 */
function prt_full_block_supports(array $align = ['wide', 'full']): array
{
    return [
        'align'   => $align,
        'anchor'  => true,
        'color'   => ['background' => true, 'text' => true, 'gradients' => true, 'link' => true],
        'typography' => [
            'fontSize'                       => true,
            'lineHeight'                     => true,
            '__experimentalFontFamily'       => true,
            '__experimentalFontWeight'       => true,
            '__experimentalFontStyle'        => true,
            '__experimentalLetterSpacing'    => true,
            '__experimentalTextTransform'    => true,
            '__experimentalDefaultControls'  => ['fontSize' => true, 'fontFamily' => true],
        ],
        'spacing' => [
            'margin'                        => true,
            'padding'                       => true,
            'blockGap'                      => true,
            '__experimentalDefaultControls' => ['padding' => true, 'margin' => true],
        ],
        '__experimentalBorder' => [
            'color'                         => true,
            'radius'                        => true,
            'width'                         => true,
            'style'                         => true,
            '__experimentalDefaultControls' => ['color' => true, 'radius' => true, 'width' => true],
        ],
    ];
}

/* ── Registration ───────────────────────────────────────────────────── */

// Registers all six bespoke blocks on 'init' (the earliest hook block types
// can be registered on; priority 12 keeps it after any earlier setup that
// blocks might depend on, e.g. theme support flags in setup.php).
add_action('init', function () {
    $deps = ['wp-blocks', 'wp-element', 'wp-block-editor', 'wp-components', 'wp-server-side-render', 'wp-i18n'];

    // Slug ⇒ editor-script-file-name map, so registering N blocks doesn't
    // require N near-identical wp_register_script() calls below.
    $blocks = [
        'prt-stat-strip'     => 'stat-strip',
        'prt-skills-grid'    => 'skills-grid',
        'prt-timeline'       => 'timeline',
        'prt-resource-group' => 'resource-group',
        'prt-cta-band'       => 'cta-band',
        'prt-project-card'   => 'project-card',
    ];

    foreach ($blocks as $handle => $slug) {
        $path = "resources/js/{$handle}-editor.js";
        if (file_exists(get_theme_file_path($path))) {
            wp_register_script($handle, get_theme_file_uri($path), $deps, filemtime(get_theme_file_path($path)), true);
        }
    }

    register_block_type('prt/stat-strip', [
        'api_version'     => 2,
        'editor_script'   => 'prt-stat-strip',
        'attributes'      => prt_stat_strip_attrs(),
        'render_callback' => __NAMESPACE__ . '\\prt_stat_strip_render',
        'supports'        => prt_full_block_supports(),
        'example'         => ['viewportWidth' => 1000],
    ]);
    register_block_type('prt/skills-grid', [
        'api_version'     => 2,
        'editor_script'   => 'prt-skills-grid',
        'attributes'      => prt_skills_grid_attrs(),
        'render_callback' => __NAMESPACE__ . '\\prt_skills_grid_render',
        'supports'        => prt_full_block_supports(),
        'example'         => ['viewportWidth' => 1000],
    ]);
    register_block_type('prt/timeline', [
        'api_version'     => 2,
        'editor_script'   => 'prt-timeline',
        'attributes'      => prt_timeline_attrs(),
        'render_callback' => __NAMESPACE__ . '\\prt_timeline_render',
        'supports'        => prt_full_block_supports(['wide']),
        'example'         => ['viewportWidth' => 900],
    ]);
    register_block_type('prt/resource-group', [
        'api_version'     => 2,
        'editor_script'   => 'prt-resource-group',
        'attributes'      => prt_resource_group_attrs(),
        'render_callback' => __NAMESPACE__ . '\\prt_resource_group_render',
        'supports'        => prt_full_block_supports(['wide']),
        'example'         => ['viewportWidth' => 700],
    ]);
    register_block_type('prt/cta-band', [
        'api_version'     => 2,
        'editor_script'   => 'prt-cta-band',
        'attributes'      => prt_cta_band_attrs(),
        'render_callback' => __NAMESPACE__ . '\\prt_cta_band_render',
        'supports'        => prt_full_block_supports(),
        'example'         => ['viewportWidth' => 1000],
    ]);
    register_block_type('prt/project-card', [
        'api_version'     => 2,
        'editor_script'   => 'prt-project-card',
        'attributes'      => prt_project_card_attrs(),
        'render_callback' => __NAMESPACE__ . '\\prt_project_card_render',
        'example'         => ['viewportWidth' => 500],
        'supports'        => prt_full_block_supports(['wide']),
    ]);
}, 12);

/* ── Render callbacks ───────────────────────────────────────────────── */
/* Every render_callback below follows the same shape: merge $attrs over its
 * attrs-function defaults (so partial attribute sets from older saved
 * content don't produce PHP notices), decode any JSON-string list
 * attributes, then hand-build the HTML string using
 * get_block_wrapper_attributes() so block supports (color, spacing, etc.)
 * are applied automatically. All user-supplied text is escaped at output. */

/**
 * Server-side render for prt/stat-strip. Renders a responsive row of
 * number+label stat items, clamped to 2–4 columns since the grid CSS only
 * defines styles for that range.
 *
 * @param array $attrs Block attributes (see prt_stat_strip_attrs()).
 * @return string Block HTML.
 */
function prt_stat_strip_render(array $attrs): string
{
    $a     = wp_parse_args($attrs, array_map(fn($v) => $v['default'], prt_stat_strip_attrs()));
    $stats = json_decode($a['stats'], true) ?: [];
    $cols  = max(2, min(4, absint($a['columns'])));

    $out = '<div ' . get_block_wrapper_attributes(['class' => 'prt-stat-strip stat-grid', 'style' => 'grid-template-columns:repeat(' . $cols . ',1fr)']) . '>';
    foreach ($stats as $s) {
        $out .= '<div class="stat-item">';
        $out .= '<span class="stat-number">' . esc_html($s['value'] ?? '') . '</span>';
        $out .= '<span class="stat-label">' . esc_html($s['label'] ?? '') . '</span>';
        $out .= '</div>';
    }
    $out .= '</div>';
    return $out;
}

/**
 * Server-side render for prt/skills-grid. Renders a grid of title+body
 * cards (2–3 columns); the 'focus' style swaps in bolder card/grid classes
 * for use as a smaller, higher-emphasis feature list.
 *
 * @param array $attrs Block attributes (see prt_skills_grid_attrs()).
 * @return string Block HTML.
 */
function prt_skills_grid_render(array $attrs): string
{
    $a     = wp_parse_args($attrs, array_map(fn($v) => $v['default'], prt_skills_grid_attrs()));
    $cards = json_decode($a['cards'], true) ?: [];
    $cols  = max(2, min(3, absint($a['columns'])));
    $cls   = $a['style'] === 'focus' ? 'focus-card' : 'skill-card';

    $gridCls = 'prt-skills-grid ' . ($a['style'] === 'focus' ? 'focus-grid' : 'skills-grid');
    $out = '<div ' . get_block_wrapper_attributes(['class' => $gridCls, 'style' => 'grid-template-columns:repeat(' . $cols . ',1fr)']) . '>';
    foreach ($cards as $c) {
        $out .= '<div class="' . $cls . '">';
        if (! empty($c['title'])) {
            $out .= '<h3>' . esc_html($c['title']) . '</h3>';
        }
        if (! empty($c['body'])) {
            $out .= '<p>' . esc_html($c['body']) . '</p>';
        }
        $out .= '</div>';
    }
    $out .= '</div>';
    return $out;
}

/**
 * Server-side render for prt/timeline. Renders a vertical work-history
 * timeline (dates, title, org, body) used on the Résumé page.
 *
 * @param array $attrs Block attributes (see prt_timeline_attrs()).
 * @return string Block HTML.
 */
function prt_timeline_render(array $attrs): string
{
    $a       = wp_parse_args($attrs, array_map(fn($v) => $v['default'], prt_timeline_attrs()));
    $entries = json_decode($a['entries'], true) ?: [];

    $out = '<div ' . get_block_wrapper_attributes(['class' => 'prt-timeline resume-timeline']) . '>';
    foreach ($entries as $e) {
        $out .= '<article class="timeline-entry">';
        $out .= '<div class="timeline-meta"><span class="timeline-dates">' . esc_html($e['dates'] ?? '') . '</span></div>';
        $out .= '<div class="timeline-body">';
        if (! empty($e['title'])) {
            $out .= '<h3>' . esc_html($e['title']) . '</h3>';
        }
        if (! empty($e['org'])) {
            $out .= '<p class="timeline-org">' . esc_html($e['org']) . '</p>';
        }
        if (! empty($e['body'])) {
            $out .= '<p>' . esc_html($e['body']) . '</p>';
        }
        $out .= '</div></article>';
    }
    $out .= '</div>';
    return $out;
}

/**
 * Server-side render for prt/resource-group. Renders a titled list of
 * external links (each opened in a new tab with rel="noopener noreferrer"
 * since these always point off-site).
 *
 * @param array $attrs Block attributes (see prt_resource_group_attrs()).
 * @return string Block HTML.
 */
function prt_resource_group_render(array $attrs): string
{
    $a     = wp_parse_args($attrs, array_map(fn($v) => $v['default'], prt_resource_group_attrs()));
    $links = json_decode($a['links'], true) ?: [];

    $out  = '<div ' . get_block_wrapper_attributes(['class' => 'prt-resource-group resource-group']) . '>';
    $out .= '<h2 class="resource-group-title">' . esc_html($a['emoji']) . ' ' . esc_html($a['heading']) . '</h2>';
    $out .= '<ul class="resource-list">';
    foreach ($links as $l) {
        $url   = esc_url($l['url'] ?? '');
        $label = esc_html($l['label'] ?? $url);
        $out  .= '<li><a href="' . $url . '" target="_blank" rel="noopener noreferrer">' . $label . ' <span aria-hidden="true">↗</span></a></li>';
    }
    $out .= '</ul></div>';
    return $out;
}

/**
 * Server-side render for prt/cta-band. Renders a call-to-action panel whose
 * background/text/button colours are driven entirely by the 'variant'
 * attribute (dark/green/light) rather than block color supports, so the
 * three variants stay visually consistent regardless of what the editor's
 * color picker is set to.
 *
 * @param array $attrs Block attributes (see prt_cta_band_attrs()).
 * @return string Block HTML.
 */
function prt_cta_band_render(array $attrs): string
{
    $a   = wp_parse_args($attrs, array_map(fn($v) => $v['default'], prt_cta_band_attrs()));
    $bg  = $a['variant'] === 'green' ? 'var(--color-green)' : ($a['variant'] === 'light' ? 'var(--color-cream,#f8f7f3)' : 'var(--color-ink)');
    $fg  = $a['variant'] === 'light' ? 'var(--color-ink)' : 'var(--color-paper,#fff)';
    $btn = $a['variant'] === 'light' ? 'background:var(--color-ink);color:var(--color-paper)' : 'background:var(--color-paper,#fff);color:var(--color-ink)';

    $out  = '<div ' . get_block_wrapper_attributes(['class' => 'prt-cta-band cta-card', 'style' => 'background:' . $bg . ';color:' . $fg]) . '>';
    $out .= '<h2 style="color:' . $fg . '">' . esc_html($a['heading']) . '</h2>';
    if ($a['body']) {
        $out .= '<p>' . esc_html($a['body']) . '</p>';
    }
    if ($a['btnText'] && $a['btnUrl']) {
        $out .= '<a class="btn" href="' . esc_url($a['btnUrl']) . '" style="' . $btn . '">' . esc_html($a['btnText']) . '</a>';
    }
    $out .= '</div>';
    return $out;
}

/**
 * Server-side render for prt/project-card. Renders a single project/case-
 * study card with an optional thumbnail, tag pills (from a comma-separated
 * string, capped at 6), and Live/GitHub links. 'link' takes priority over
 * 'liveUrl' for the card's primary click-through target (title + thumbnail),
 * while 'liveUrl' still gets its own explicit CTA link further down.
 *
 * @param array $attrs Block attributes (see prt_project_card_attrs()).
 * @return string Block HTML.
 */
function prt_project_card_render(array $attrs): string
{
    $a    = wp_parse_args($attrs, array_map(fn($v) => $v['default'], prt_project_card_attrs()));
    $href = esc_url($a['link'] ?: ($a['liveUrl'] ?: '#'));

    $out  = '<article ' . get_block_wrapper_attributes(['class' => 'prt-project-card project-card']) . '>';
    if ($a['imageUrl']) {
        $out .= '<a href="' . $href . '" class="project-card-link" tabindex="-1" aria-hidden="true">';
        $out .= '<div class="project-card-thumb"><img src="' . esc_url($a['imageUrl']) . '" alt="' . esc_attr($a['imageAlt']) . '" loading="lazy"></div></a>';
    }
    $out .= '<div class="project-card-body">';
    $out .= '<h2 class="project-card-title"><a href="' . $href . '">' . esc_html($a['heading']) . '</a></h2>';
    if ($a['excerpt']) {
        $out .= '<p class="project-card-excerpt">' . esc_html($a['excerpt']) . '</p>';
    }
    if ($a['tags']) {
        $out .= '<ul class="tag-list" aria-label="Technologies">';
        foreach (array_slice(array_map('trim', explode(',', $a['tags'])), 0, 6) as $tag) {
            $out .= '<li class="tag-pill">' . esc_html($tag) . '</li>';
        }
        $out .= '</ul>';
    }
    $links = [];
    if ($a['liveUrl'])   $links[] = '<a href="' . esc_url($a['liveUrl'])   . '" target="_blank" rel="noopener" class="project-card-cta">Live ↗</a>';
    if ($a['githubUrl']) $links[] = '<a href="' . esc_url($a['githubUrl']) . '" target="_blank" rel="noopener" class="project-card-cta">GitHub ↗</a>';
    if ($links) $out .= '<p class="project-card-links">' . implode(' ', $links) . '</p>';
    $out .= '</div></article>';
    return $out;
}
