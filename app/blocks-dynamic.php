<?php

/**
 * Dynamic Blade-powered blocks:
 *  - prt/icon       : insert any Blade icon by name (Simple Icons, Heroicons, Lucide, local prt-).
 *  - prt/post-grid  : query posts or projects into a responsive card grid.
 * Server-rendered (render_callback) with plain-JS editors (ServerSideRender previews).
 *
 * "Dynamic" here means the block markup is generated in PHP on every render
 * (via render_callback) rather than saved as static HTML in post_content, so
 * editing the render function or theme styles updates every existing usage
 * of the block with no re-saving needed. The block editor UI for both blocks
 * is plain JS (not React/JSX build step) using wp.serverSideRender to preview
 * the real PHP output, keeping this file framework-independent.
 */

namespace App;

/**
 * Attribute schema for prt/icon. Defines both the block.json-equivalent
 * attributes (for register_block_type) and doubles as the source of default
 * values used by prt_icon_block_render() when attrs are missing.
 */
function prt_icon_block_attrs()
{
    return [
        'name'  => ['type' => 'string', 'default' => 'heroicon-o-sparkles'],
        'size'  => ['type' => 'number', 'default' => 32],
        'color' => ['type' => 'string', 'default' => ''],
        'align' => ['type' => 'string', 'default' => 'left'],
        'label' => ['type' => 'string', 'default' => ''],
    ];
}

/**
 * Attribute schema for prt/post-grid, covering both which content to query
 * (postType/count/orderby/order/term) and which card fields to show
 * (showImage/showExcerpt/showDate/showCategory).
 */
function prt_postgrid_attrs()
{
    return [
        'postType'    => ['type' => 'string', 'default' => 'post'],
        'count'       => ['type' => 'number', 'default' => 6],
        'columns'     => ['type' => 'number', 'default' => 3],
        'orderby'     => ['type' => 'string', 'default' => 'date'],
        'order'       => ['type' => 'string', 'default' => 'DESC'],
        'showImage'   => ['type' => 'boolean', 'default' => true],
        'showExcerpt' => ['type' => 'boolean', 'default' => true],
        'showDate'    => ['type' => 'boolean', 'default' => true],
        'showCategory'=> ['type' => 'boolean', 'default' => false],
        'term'        => ['type' => 'string', 'default' => ''],
    ];
}

/**
 * Register the editor scripts and the two block types on `init` (the
 * standard WordPress hook for register_block_type/register_post_type calls).
 * Priority 11 to run after anything at the default priority (10) that might
 * need to exist first, e.g. prt_full_block_supports() below being defined by
 * whichever file declares it having already hooked its own init callback.
 */
add_action('init', function () {
    foreach (['prt-icon-block' => 'icon', 'prt-postgrid-block' => 'postgrid'] as $handle => $slug) {
        $path = "resources/js/{$handle}-editor.js";
        // filemtime as the version string busts the browser cache on every
        // deploy without needing a manual version bump; falls back to '1' if
        // the file is missing so this never throws during local dev.
        wp_register_script(
            $handle,
            get_theme_file_uri($path),
            ['wp-blocks', 'wp-element', 'wp-block-editor', 'wp-components', 'wp-server-side-render', 'wp-i18n'],
            file_exists(get_theme_file_path($path)) ? filemtime(get_theme_file_path($path)) : '1',
            true
        );
    }

    register_block_type('prt/icon', [
        'api_version'     => 2,
        'editor_script'   => 'prt-icon-block',
        'attributes'      => prt_icon_block_attrs(),
        'render_callback' => __NAMESPACE__ . '\\prt_icon_block_render',
        'supports'        => ['align' => true, 'spacing' => ['margin' => true]],
        'example'         => ['attributes' => ['name' => 'email', 'size' => 40, 'color' => '#6C4CF1'], 'viewportWidth' => 200],
    ]);

    register_block_type('prt/post-grid', [
        'api_version'     => 2,
        'editor_script'   => 'prt-postgrid-block',
        'attributes'      => prt_postgrid_attrs(),
        'render_callback' => __NAMESPACE__ . '\\prt_postgrid_render',
        // Prefer the theme-wide supports helper (shared "wide/full align + margin/padding"
        // config used by other blocks) if it's loaded; otherwise fall back to an inline
        // equivalent so this block still registers correctly on its own.
        'supports'        => function_exists('App\\prt_full_block_supports') ? prt_full_block_supports() : ['align' => ['wide', 'full'], 'spacing' => ['margin' => true, 'padding' => true]],
        'example'         => ['attributes' => ['postType' => 'post', 'count' => 3, 'columns' => 3, 'showCategory' => true], 'viewportWidth' => 1000],
    ]);
}, 11);

/**
 * render_callback for prt/icon: outputs a single inline SVG icon wrapped in a
 * flex container so alignment/size/color are controlled without editing CSS.
 * Attribute values are re-validated here (not just trusted from the editor)
 * since render_callback also runs for content pasted/imported from elsewhere.
 */
function prt_icon_block_render($attrs)
{
    $d = [];
    foreach (prt_icon_block_attrs() as $k => $v) {
        $d[$k] = $v['default'];
    }
    $a = wp_parse_args($attrs, $d);

    // Icon names follow the "<set>-<variant>-<icon>" convention (e.g.
    // heroicon-o-sparkles), so only letters/digits/dashes are ever valid;
    // stripping anything else prevents the name reaching prt_icon() (which
    // resolves it into a Blade include path) from being used for path traversal.
    $name  = preg_replace('/[^a-z0-9\-]/i', '', (string) $a['name']);
    $size  = max(12, absint($a['size']));
    $color = sanitize_hex_color($a['color']);
    $align = in_array($a['align'], ['left', 'center', 'right'], true) ? $a['align'] : 'left';
    $just  = $align === 'center' ? 'center' : ($align === 'right' ? 'flex-end' : 'flex-start');

    $style = 'display:flex;justify-content:' . $just . ';line-height:0;';
    $istyle = 'width:' . $size . 'px;height:' . $size . 'px;' . ($color ? 'color:' . $color . ';' : '');
    $svg = prt_icon($name, 'prt-icon-svg', ['style' => 'width:' . $size . 'px;height:' . $size . 'px;']);

    $aria = $a['label'] ? ' role="img" aria-label="' . esc_attr($a['label']) . '"' : '';
    return '<div class="wp-block-prt-icon" style="' . esc_attr($style) . '"><span class="prt-icon-wrap" style="' . esc_attr($istyle) . '"' . $aria . '>' . $svg . '</span></div>';
}

/**
 * render_callback for prt/post-grid: runs a WP_Query for the configured post
 * type and prints a responsive CSS-grid of cards. CSS is emitted inline
 * (scoped to a unique per-instance id) instead of a stylesheet because each
 * placement can have a different column count, and this keeps the block
 * fully self-contained with no separate enqueue/build step.
 */
function prt_postgrid_render($attrs)
{
    $d = [];
    foreach (prt_postgrid_attrs() as $k => $v) {
        $d[$k] = $v['default'];
    }
    $a = wp_parse_args($attrs, $d);

    $pt      = in_array($a['postType'], ['post', 'projects', 'page'], true) ? $a['postType'] : 'post';
    $count   = max(1, min(24, absint($a['count'])));
    $cols    = max(1, min(4, absint($a['columns'])));
    $orderby = in_array($a['orderby'], ['date', 'title', 'rand', 'menu_order'], true) ? $a['orderby'] : 'date';
    $order   = strtoupper($a['order']) === 'ASC' ? 'ASC' : 'DESC';

    $args = [
        'post_type'           => $pt,
        'posts_per_page'      => $count,
        'orderby'             => $orderby,
        'order'               => $order,
        'ignore_sticky_posts' => true,
        'no_found_rows'       => true,
    ];
    // Optional category filter (project_categories for projects, category for posts).
    if (! empty($a['term'])) {
        $tax = $pt === 'projects' ? 'project_categories' : 'category';
        $args['tax_query'] = [[
            'taxonomy' => $tax,
            'field'    => 'slug',
            'terms'    => sanitize_title($a['term']),
        ]];
    }
    $q = new \WP_Query($args);

    if (! $q->have_posts()) {
        wp_reset_postdata();
        // The block editor's ServerSideRender preview calls this render_callback
        // over the REST API, so an empty return there would look like a broken/
        // blank preview with no feedback. On the front end (no REST_REQUEST) an
        // empty string is preferred so an unmatched filter doesn't show placeholder
        // text to real visitors.
        return (defined('REST_REQUEST') && REST_REQUEST)
            ? '<p style="opacity:.7;font-style:italic">' . esc_html__('No items found for this post type.', 'pressroot') . '</p>'
            : '';
    }

    // Unique DOM id per block instance so the grid's column count and card
    // styles are scoped with a plain <style> tag instead of a class collision
    // when multiple post-grid blocks with different settings appear on one page.
    $uid = 'prt-pg-' . wp_unique_id();
    $css = "#{$uid}{display:grid;grid-template-columns:repeat({$cols},1fr);gap:20px;}"
        . "@media(max-width:900px){#{$uid}{grid-template-columns:repeat(2,1fr);}}"
        . "@media(max-width:560px){#{$uid}{grid-template-columns:1fr;}}"
        . "#{$uid} .prt-pg-card{display:flex;flex-direction:column;border:1.5px solid var(--color-line,#ECE6FB);border-radius:18px;overflow:hidden;background:var(--color-card,#fff);transition:transform .25s,box-shadow .25s;}"
        . "#{$uid} .prt-pg-card:hover{transform:translateY(-6px);box-shadow:0 18px 40px rgba(23,21,31,.14);}"
        . "#{$uid} .prt-pg-thumb{aspect-ratio:16/10;object-fit:cover;width:100%;display:block;}"
        . "#{$uid} .prt-pg-body{padding:22px 22px 24px;}"
        . "#{$uid} .prt-pg-meta{font-family:var(--font-mono,ui-monospace,monospace);font-size:12px;text-transform:uppercase;letter-spacing:.05em;color:var(--color-purple,#6C4CF1);margin:0 0 8px;}"
        . "#{$uid} .prt-pg-title{font-family:var(--font-display,system-ui,sans-serif);font-weight:700;font-size:20px;letter-spacing:-.01em;margin:0 0 8px;line-height:1.25;}"
        . "#{$uid} .prt-pg-title a{text-decoration:none;color:var(--color-ink,#17151F);}"
        . "#{$uid} .prt-pg-title a:hover{color:var(--color-purple,#6C4CF1);}"
        . "#{$uid} .prt-pg-ex{font-size:14.5px;line-height:1.55;color:var(--color-muted,#5A5676);margin:0;}";

    $out = '<style>' . $css . '</style><div ' . get_block_wrapper_attributes(['id' => $uid, 'class' => 'prt-pg']) . '>';
    while ($q->have_posts()) {
        $q->the_post();
        $out .= '<article class="prt-pg-card">';
        if (! empty($a['showImage']) && has_post_thumbnail()) {
            $out .= '<a href="' . esc_url(get_permalink()) . '" tabindex="-1" aria-hidden="true">' . get_the_post_thumbnail(get_the_ID(), 'medium_large', ['class' => 'prt-pg-thumb', 'loading' => 'lazy']) . '</a>';
        }
        $out .= '<div class="prt-pg-body">';
        $meta = [];
        if (! empty($a['showDate'])) {
            $meta[] = esc_html(get_the_date());
        }
        if (! empty($a['showCategory']) && $pt === 'post') {
            $cats = get_the_category();
            if ($cats) {
                $meta[] = esc_html($cats[0]->name);
            }
        }
        if ($meta) {
            $out .= '<p class="prt-pg-meta">' . implode(' &middot; ', $meta) . '</p>';
        }
        $out .= '<h3 class="prt-pg-title"><a href="' . esc_url(get_permalink()) . '">' . esc_html(get_the_title()) . '</a></h3>';
        if (! empty($a['showExcerpt'])) {
            $out .= '<p class="prt-pg-ex">' . esc_html(wp_trim_words(get_the_excerpt(), 22)) . '</p>';
        }
        $out .= '</div></article>';
    }
    $out .= '</div>';
    wp_reset_postdata();
    return $out;
}
