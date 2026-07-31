<?php

/**
 * Live preview for the "Page content" editor.
 *
 * The field editor posts the current (unsaved) field values into an <iframe>
 * pointed at the page's own front-end URL. This handler renders that page
 * normally through the theme templates, but swaps in the posted draft values via
 * the `rv/field_value` filter (added in page-fields.php's field()). The result:
 * an accurate, real-template preview that updates as you type — beside the fields.
 *
 * It is strictly read-only: signed-in editors only, nonce-checked, admin bar
 * hidden, marked noindex, and it never writes anything.
 */

namespace App;

/**
 * Draft overrides for the current preview request: ['post_id' => int, 'values' => array].
 * Set once per request from the posted data; empty on normal front-end requests.
 */
function preview_overrides(?array $set = null): array
{
    static $store = [];
    if ($set !== null) {
        $store = $set;
    }
    return $store;
}

/** Whether the current render should tag fields for click-to-edit (preview only). */
function preview_marking(?bool $set = null): bool
{
    static $on = false;
    if ($set !== null) {
        $on = $set;
    }
    return $on;
}

/** Turn the invisible field markers added by field() into click targets. */
function preview_unmark(string $html): string
{
    // Keys may be plain (hero_title) or compound for repeater cells (beliefs.0.title).
    $html = preg_replace('/\x01([a-z0-9_.]+)\x02/', '<span class="rv-pf-f" data-rv-field="$1">', $html);
    return str_replace("\x03", '</span>', $html);
}

/** Recursively sanitize a posted repeater value while preserving its structure. */
function preview_sanitize_rows($value)
{
    if (is_array($value)) {
        $out = [];
        foreach ($value as $k => $v) {
            $out[is_int($k) ? $k : sanitize_key((string) $k)] = preview_sanitize_rows($v);
        }
        return $out;
    }
    return wp_kses_post((string) $value);
}

add_action('wp', function () {
    if (is_admin() || empty($_POST['rv_preview'])) {
        return;
    }

    $post_id = (int) get_queried_object_id();
    if (! $post_id || ! current_user_can('edit_post', $post_id)) {
        return;
    }

    $nonce = isset($_POST['rv_preview_nonce']) ? sanitize_text_field(wp_unslash($_POST['rv_preview_nonce'])) : '';
    if (! wp_verify_nonce($nonce, 'rv_preview_' . $post_id)) {
        return;
    }

    $raw    = (isset($_POST['rv_pf']) && is_array($_POST['rv_pf'])) ? wp_unslash($_POST['rv_pf']) : [];
    $values = [];
    $rows   = [];
    foreach ($raw as $k => $v) {
        $key = sanitize_key($k);
        if (is_array($v)) {
            // A repeater's rows arrive as a nested array under its key.
            $rows[$key] = array_values(preview_sanitize_rows($v));
        } else {
            // Scalar + "lines" fields (field_lines splits on newlines). Sanitize by
            // the field's registered type — the SAME way saving does — so the preview
            // matches the saved/live output. Using wp_kses_post for everything would
            // entity-encode a typed "&" to "&amp;", which Blade's {{ }} then escapes
            // again, showing a literal "&amp;". Only real "html" fields (output raw
            // with {!! !!}) should keep wp_kses_post.
            $type = field_type($key);
            if ($type === 'html') {
                $values[$key] = wp_kses_post((string) $v);
            } elseif ($type === 'url') {
                $values[$key] = esc_url_raw(trim((string) $v));
            } else {
                $values[$key] = sanitize_textarea_field((string) $v);
            }
        }
    }

    preview_overrides(['post_id' => $post_id, 'values' => $values, 'rows' => $rows]);

    // Tag text fields in the render, then convert the markers to click targets.
    preview_marking(true);
    ob_start(__NAMESPACE__ . '\\preview_unmark');

    add_filter('show_admin_bar', '__return_false');
    add_action('wp_head', function () {
        echo '<meta name="robots" content="noindex,nofollow">' . "\n";
    }, 0);
});

/**
 * Swap saved field values for the draft ones during a preview render. Falls back
 * to the template default when a draft value is empty (matching how the live
 * page behaves when a field is left blank).
 */
add_filter('rv/field_value', function ($value, $key, $post_id, $default) {
    $ov = preview_overrides();
    if (! empty($ov) && (int) $ov['post_id'] === (int) $post_id && array_key_exists($key, $ov['values'])) {
        $draft = $ov['values'][$key];
        return $draft !== '' ? $draft : $default;
    }
    return $value;
}, 10, 4);

/**
 * Swap saved repeater rows for the draft ones during a preview render. An empty
 * draft (all rows removed) falls back to the template default, matching the live
 * page's behavior when a repeater has no saved rows.
 */
add_filter('rv/field_rows', function ($rows, $key, $post_id, $default) {
    $ov = preview_overrides();
    if (! empty($ov) && (int) $ov['post_id'] === (int) $post_id && isset($ov['rows'][$key])) {
        $draft = $ov['rows'][$key];
        return ! empty($draft) ? $draft : $default;
    }
    return $rows;
}, 10, 4);

/**
 * Editor script that mirrors the fields into the preview iframe (debounced).
 */
add_action('admin_enqueue_scripts', function ($hook) {
    if (! in_array($hook, ['post.php', 'post-new.php'], true)) {
        return;
    }
    $screen = get_current_screen();
    if (! $screen || $screen->post_type !== 'page') {
        return;
    }
    $pv_path = get_theme_file_path('resources/js/admin-preview.js');
    $pv_ver  = file_exists($pv_path) ? (string) filemtime($pv_path) : '1.1.0';
    wp_enqueue_script('rv-admin-preview', get_theme_file_uri('resources/js/admin-preview.js'), [], $pv_ver, true);
});
