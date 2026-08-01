<?php

/**
 * Open Images — search & import openly-licensed / public-domain photography
 * straight into the Media Library (Media → Open Images).
 *
 * Powered by the Openverse API (api.openverse.org) — WordPress's own open-media
 * search, which aggregates 800M+ CC-licensed and public-domain images from many
 * open sources (Wikimedia Commons, Flickr, NASA, Rawpixel, museums, and more).
 * No API key is required for normal use.
 *
 * Search by keyword, filter by licence (including Public Domain / CC0) and by
 * source, then import an image with one click. Attribution — title, creator,
 * licence and source link — is saved onto the attachment (caption + alt text +
 * a source-URL meta), so credit travels with the image and pairs naturally with
 * the theme's hero / photo credit fields.
 *
 * Safety: all output is escaped; imports are nonce-protected and limited to
 * users who can upload files; only the chosen image URL is fetched and sideloaded.
 */

namespace App;

const OI_API      = 'https://api.openverse.org/v1/images/';
const OI_STATS    = 'https://api.openverse.org/v1/images/stats/';
const OI_PAGESIZE = 30;

/** Request headers — a descriptive User-Agent is required; the default
 *  WordPress UA is rejected by Openverse's edge, which is why a server-side
 *  call fails where a browser call (with a normal UA) succeeds. */
function oi_headers(): array
{
    return [
        'Accept'     => 'application/json',
        'User-Agent' => 'RidgesAndValleysStudio/1.0 (+' . home_url('/') . ')',
    ];
}

/* -------------------------------------------------------------------------
 * Options for the search filters.
 * ---------------------------------------------------------------------- */

/** Licence presets → Openverse query params. */
function oi_license_presets(): array
{
    return [
        ''       => __('All open licences', 'sage'),
        'pd'     => __('Public domain (CC0 + PD Mark)', 'sage'),
        'comm'   => __('Free for commercial use', 'sage'),
        'commod' => __('Commercial use + can modify', 'sage'),
    ];
}

/** Apply a licence preset to the query args. */
function oi_apply_license(string $preset, array $args): array
{
    if ($preset === 'pd') {
        $args['license'] = 'cc0,pdm';
    } elseif ($preset === 'comm') {
        $args['license_type'] = 'commercial';
    } elseif ($preset === 'commod') {
        $args['license_type'] = 'commercial,modification';
    }
    return $args;
}

/** Source slug => display name, fetched from Openverse (cached 12h). */
function oi_sources(): array
{
    $cached = get_transient('rv_oi_sources');
    if (is_array($cached) && $cached) {
        return $cached;
    }
    $sources = [];
    $res = wp_remote_get(OI_STATS, ['timeout' => 12, 'headers' => oi_headers()]);
    if (! is_wp_error($res) && (int) wp_remote_retrieve_response_code($res) === 200) {
        $data = json_decode((string) wp_remote_retrieve_body($res), true);
        foreach ((array) $data as $row) {
            $slug = (string) ($row['source_name'] ?? '');
            $name = (string) ($row['display_name'] ?? $slug);
            if ($slug !== '') {
                $sources[$slug] = $name;
            }
        }
        asort($sources);
    }
    if (! $sources) {
        // Fallback set of well-known open sources if the stats call fails.
        $sources = [
            'flickr' => 'Flickr', 'wikimedia' => 'Wikimedia Commons', 'nasa' => 'NASA',
            'rawpixel' => 'Rawpixel', 'stocksnap' => 'StockSnap', 'met' => 'The Met',
        ];
    }
    set_transient('rv_oi_sources', $sources, 12 * HOUR_IN_SECONDS);
    return $sources;
}

/* -------------------------------------------------------------------------
 * Search + import.
 * ---------------------------------------------------------------------- */

/** Run an Openverse image search. Returns [results[], total, error]. */
function oi_search(string $q, string $license, string $source, int $page): array
{
    $args = array_filter([
        'q'         => $q,
        'page'      => max(1, $page),
        'page_size' => OI_PAGESIZE,
        'source'    => $source,
        'mature'    => 'false',
    ], static fn ($v) => $v !== '' && $v !== null);
    $args = oi_apply_license($license, $args);

    $res = wp_remote_get(add_query_arg($args, OI_API), [
        'timeout' => 20,
        'headers' => oi_headers(),
    ]);
    if (is_wp_error($res)) {
        return [[], 0, $res->get_error_message()];
    }
    $code = (int) wp_remote_retrieve_response_code($res);
    $data = json_decode((string) wp_remote_retrieve_body($res), true);
    if ($code === 429) {
        return [[], 0, __('Openverse is rate-limiting requests right now — wait a minute and try again.', 'sage')];
    }
    if ($code !== 200 || ! is_array($data)) {
        $detail = is_array($data) ? (string) ($data['detail'] ?? '') : '';
        return [[], 0, sprintf(__('Search failed (HTTP %1$d).%2$s', 'sage'), $code, $detail !== '' ? ' ' . $detail : '')];
    }
    return [(array) ($data['results'] ?? []), (int) ($data['result_count'] ?? 0), ''];
}

/** Build a human attribution line for an Openverse result. */
function oi_attribution(array $r): string
{
    $title   = (string) ($r['title'] ?? __('Untitled', 'sage'));
    $creator = (string) ($r['creator'] ?? '');
    $license = strtoupper((string) ($r['license'] ?? ''));
    $version = (string) ($r['license_version'] ?? '');
    $source  = (string) ($r['source'] ?? '');

    $bits = ['"' . $title . '"'];
    if ($creator !== '') {
        $bits[] = sprintf(__('by %s', 'sage'), $creator);
    }
    if ($license !== '') {
        $bits[] = trim($license . ' ' . $version);
    }
    if ($source !== '') {
        $bits[] = sprintf(__('via %s', 'sage'), $source);
    }
    return implode(' · ', $bits);
}

/** Sideload one Openverse image (by its full-size URL) into the Media Library. */
function oi_import(array $r): array
{
    require_once ABSPATH . 'wp-admin/includes/file.php';
    require_once ABSPATH . 'wp-admin/includes/media.php';
    require_once ABSPATH . 'wp-admin/includes/image.php';

    $url = (string) ($r['url'] ?? '');
    if ($url === '' || ! wp_http_validate_url($url)) {
        return ['ok' => false, 'msg' => __('That image has no usable URL.', 'sage')];
    }

    $tmp = download_url($url, 60);
    if (is_wp_error($tmp)) {
        return ['ok' => false, 'msg' => $tmp->get_error_message()];
    }

    $name = sanitize_file_name((string) ($r['title'] ?? 'open-image'));
    $ext  = (string) ($r['filetype'] ?? 'jpg');
    $file = ['name' => ($name !== '' ? $name : 'open-image') . '.' . ($ext ?: 'jpg'), 'tmp_name' => $tmp];

    $attribution = oi_attribution($r);
    $attach_id = media_handle_sideload($file, 0, $attribution);
    if (is_wp_error($attach_id)) {
        @unlink($tmp);
        return ['ok' => false, 'msg' => $attach_id->get_error_message()];
    }

    // Store credit so it travels with the image.
    update_post_meta($attach_id, '_wp_attachment_image_alt', $attribution);
    wp_update_post([
        'ID'           => $attach_id,
        'post_excerpt' => $attribution, // caption
        'post_content' => (string) ($r['foreign_landing_url'] ?? ($r['license_url'] ?? '')),
    ]);
    update_post_meta($attach_id, 'rv_open_image_source', esc_url_raw((string) ($r['foreign_landing_url'] ?? '')));

    return ['ok' => true, 'id' => (int) $attach_id, 'msg' => $attribution];
}

/* -------------------------------------------------------------------------
 * Admin page (Media → Open Images).
 * ---------------------------------------------------------------------- */

add_action('admin_menu', function () {
    add_submenu_page(
        'upload.php',
        __('Open Images', 'sage'),
        __('Open Images', 'sage'),
        'upload_files',
        'rv-open-images',
        __NAMESPACE__ . '\\render_open_images_page'
    );
});

function render_open_images_page(): void
{
    if (! current_user_can('upload_files')) {
        wp_die(esc_html__('You do not have permission.', 'sage'));
    }

    $notice = null;

    // Import (POST).
    if (($_SERVER['REQUEST_METHOD'] ?? '') === 'POST' && isset($_POST['rv_oi_nonce'])) {
        check_admin_referer('rv_oi_import', 'rv_oi_nonce');
        $payload = isset($_POST['rv_oi_item']) ? json_decode((string) wp_unslash($_POST['rv_oi_item']), true) : null;
        if (is_array($payload)) {
            $r = oi_import($payload);
            $notice = $r['ok']
                ? ['notice-success', sprintf(__('Imported into your Media Library: %s', 'sage'), $r['msg'])]
                : ['notice-error', sprintf(__('Import failed: %s', 'sage'), $r['msg'])];
        }
    }

    $q       = isset($_GET['q']) ? sanitize_text_field(wp_unslash($_GET['q'])) : '';
    $license = isset($_GET['license']) ? sanitize_key($_GET['license']) : '';
    $source  = isset($_GET['source']) ? sanitize_text_field(wp_unslash($_GET['source'])) : '';
    $page    = isset($_GET['pg']) ? max(1, (int) $_GET['pg']) : 1;

    echo '<div class="wrap">';
    echo '<h1>' . esc_html__('Open Images', 'sage') . '</h1>';
    echo '<p style="max-width:74ch">' . esc_html__('Search openly-licensed and public-domain photography from across the open web (Wikimedia Commons, Flickr, NASA, museums and more, via Openverse) and import straight into your Media Library. Credit is saved onto each image automatically. Always double-check a licence before commercial use.', 'sage') . '</p>';

    if ($notice) {
        printf('<div class="notice %1$s is-dismissible"><p>%2$s</p></div>', esc_attr($notice[0]), wp_kses_post($notice[1]));
    }

    // Search form.
    echo '<form method="get" action="" style="margin:1rem 0;display:flex;gap:.5rem;flex-wrap:wrap;align-items:flex-end">';
    echo '<input type="hidden" name="page" value="rv-open-images" />';
    echo '<label>' . esc_html__('Search', 'sage') . '<br><input type="search" name="q" value="' . esc_attr($q) . '" class="regular-text" placeholder="' . esc_attr__('e.g. Pennsylvania farmland', 'sage') . '" style="min-width:280px" /></label>';

    echo '<label>' . esc_html__('Licence', 'sage') . '<br><select name="license">';
    foreach (oi_license_presets() as $val => $lab) {
        printf('<option value="%s"%s>%s</option>', esc_attr($val), selected($license, $val, false), esc_html($lab));
    }
    echo '</select></label>';

    echo '<label>' . esc_html__('Source', 'sage') . '<br><select name="source"><option value="">' . esc_html__('All sources', 'sage') . '</option>';
    foreach (oi_sources() as $slug => $name) {
        printf('<option value="%s"%s>%s</option>', esc_attr($slug), selected($source, $slug, false), esc_html($name));
    }
    echo '</select></label>';

    echo '<button type="submit" class="button button-primary">' . esc_html__('Search', 'sage') . '</button>';
    echo '</form>';

    if ($q === '') {
        echo '<p class="description">' . esc_html__('Enter a search term to begin.', 'sage') . '</p></div>';
        return;
    }

    [$results, $total, $err] = oi_search($q, $license, $source, $page);

    if ($err !== '') {
        echo '<div class="notice notice-error inline"><p>' . esc_html($err) . '</p></div></div>';
        return;
    }
    if (! $results) {
        echo '<p>' . esc_html__('No results — try a different search or a broader licence filter.', 'sage') . '</p></div>';
        return;
    }

    printf('<p class="description">%s</p>', esc_html(sprintf(__('About %d results.', 'sage'), $total)));

    echo '<div style="display:grid;grid-template-columns:repeat(auto-fill,minmax(210px,1fr));gap:1rem;margin-top:1rem">';
    foreach ($results as $r) {
        $thumb = (string) ($r['thumbnail'] ?? ($r['url'] ?? ''));
        $land  = (string) ($r['foreign_landing_url'] ?? '');
        $lic   = strtoupper((string) ($r['license'] ?? '')) . ' ' . (string) ($r['license_version'] ?? '');
        $ttl   = (string) ($r['title'] ?? __('Untitled', 'sage'));
        // Compact payload for import (avoid shipping the whole object).
        $payload = wp_json_encode([
            'url' => $r['url'] ?? '', 'title' => $ttl, 'creator' => $r['creator'] ?? '',
            'license' => $r['license'] ?? '', 'license_version' => $r['license_version'] ?? '',
            'license_url' => $r['license_url'] ?? '', 'source' => $r['source'] ?? '',
            'foreign_landing_url' => $land, 'filetype' => $r['filetype'] ?? 'jpg',
        ]);

        echo '<div style="border:1px solid #dcdcde;border-radius:8px;overflow:hidden;background:#fff;display:flex;flex-direction:column">';
        printf(
            '<div style="aspect-ratio:4/3;background:#f0f0f1 center/cover no-repeat url(%s)"></div>',
            esc_url($thumb)
        );
        echo '<div style="padding:.6rem .7rem;display:flex;flex-direction:column;gap:.35rem;flex:1">';
        printf('<strong style="font-size:.85rem;line-height:1.25;overflow:hidden;display:-webkit-box;-webkit-line-clamp:2;-webkit-box-orient:vertical">%s</strong>', esc_html($ttl));
        echo '<span class="description" style="font-size:.72rem">' . esc_html(trim($lic)) . '</span>';
        echo '<div style="margin-top:auto;display:flex;gap:.4rem;align-items:center">';
        echo '<form method="post" action="" style="margin:0">';
        wp_nonce_field('rv_oi_import', 'rv_oi_nonce');
        echo '<input type="hidden" name="rv_oi_item" value="' . esc_attr($payload) . '" />';
        echo '<button type="submit" class="button button-small button-primary">' . esc_html__('Import', 'sage') . '</button>';
        echo '</form>';
        if ($land !== '') {
            echo '<a href="' . esc_url($land) . '" target="_blank" rel="noopener" class="button button-small">' . esc_html__('View', 'sage') . '</a>';
        }
        echo '</div></div></div>';
    }
    echo '</div>';

    // Pager.
    $base = remove_query_arg('pg');
    echo '<p style="margin-top:1.2rem;display:flex;gap:.5rem">';
    if ($page > 1) {
        echo '<a class="button" href="' . esc_url(add_query_arg('pg', $page - 1, $base)) . '">&larr; ' . esc_html__('Previous', 'sage') . '</a>';
    }
    if (count($results) >= OI_PAGESIZE) {
        echo '<a class="button" href="' . esc_url(add_query_arg('pg', $page + 1, $base)) . '">' . esc_html__('Next', 'sage') . ' &rarr;</a>';
    }
    echo '</p>';

    echo '</div>';
}
