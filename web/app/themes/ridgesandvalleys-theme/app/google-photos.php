<?php

/**
 * Google Photos → Media Library import (Media → Google Photos).
 *
 * Google retired broad Library API access in 2025, so this uses the current,
 * policy-compliant Picker API: you connect your Google account, pick photos in
 * Google's own picker, and only the photos you select are imported into the WP
 * Media Library. Nothing else in your library is ever read.
 *
 * Flow: OAuth (authorization code) → create a Picker session → open the picker →
 * poll the session → list picked items → download each and sideload it as an
 * attachment.
 *
 * Setup (one time):
 *   1. Google Cloud Console → create an OAuth 2.0 Client ID (type: Web
 *      application). Enable the "Photos Picker API".
 *   2. Add this Authorized redirect URI (shown on the settings page):
 *        {site}/wp-admin/admin-post.php?action=rv_gp_callback
 *   3. Paste the Client ID + Client secret under "Google Photos" on the
 *      Theme APIs page (or define RV_INTEGRATION_GOOGLE_PHOTOS_CLIENT_ID /
 *      _CLIENT_SECRET in wp-config.php). For your own use the OAuth app can stay
 *      in "Testing" with you as a test user — no Google verification needed.
 */

namespace App;

const GP_SCOPE      = 'https://www.googleapis.com/auth/photospicker.mediaitems.readonly';
const GP_TOKENS_OPT = 'rv_gp_tokens';
const GP_SESSION_OPT = 'rv_gp_session';

/* -------------------------------------------------------------------------
 * Register the integration so its credentials live on the Theme APIs page.
 * ---------------------------------------------------------------------- */
add_filter('rv/integrations', function ($registry) {
    $registry['items']['google_photos'] = [
        'group'  => 'apps',
        'label'  => __('Google Photos', 'sage'),
        'help'   => __('OAuth client for importing your own photography via the Google Photos picker. Create a Web application OAuth client in Google Cloud and enable the Photos Picker API.', 'sage'),
        'fields' => [
            'client_id'     => ['label' => __('OAuth Client ID', 'sage'), 'type' => 'text', 'placeholder' => '…apps.googleusercontent.com'],
            'client_secret' => ['label' => __('OAuth Client secret', 'sage'), 'type' => 'password', 'secret' => true, 'placeholder' => 'GOCSPX-…'],
        ],
    ];
    return $registry;
});

/* Credentials + config ---------------------------------------------------- */
function gp_client_id(): string
{
    return function_exists(__NAMESPACE__ . '\\integration') ? (string) integration('google_photos', 'client_id') : '';
}
function gp_client_secret(): string
{
    return function_exists(__NAMESPACE__ . '\\integration') ? (string) integration('google_photos', 'client_secret') : '';
}
function gp_redirect_uri(): string
{
    return admin_url('admin-post.php?action=rv_gp_callback');
}
function gp_configured(): bool
{
    return gp_client_id() !== '' && gp_client_secret() !== '';
}

/* Token storage (refresh token encrypted at rest when possible) ----------- */
function gp_tokens_get(): array
{
    $t = get_option(GP_TOKENS_OPT, []);
    if (! is_array($t)) {
        return [];
    }
    if (! empty($t['refresh_token']) && function_exists(__NAMESPACE__ . '\\secret_decrypt')) {
        $t['refresh_token'] = (string) secret_decrypt((string) $t['refresh_token']);
    }
    return $t;
}
function gp_tokens_save(array $t): void
{
    if (! empty($t['refresh_token']) && function_exists(__NAMESPACE__ . '\\secret_encrypt')) {
        $t['refresh_token'] = (string) secret_encrypt((string) $t['refresh_token']);
    }
    update_option(GP_TOKENS_OPT, $t, false);
}
function gp_disconnect(): void
{
    delete_option(GP_TOKENS_OPT);
    delete_option(GP_SESSION_OPT);
}
function gp_connected(): bool
{
    $t = gp_tokens_get();
    return ! empty($t['refresh_token']) || ! empty($t['access_token']);
}

/* OAuth ------------------------------------------------------------------- */
function gp_oauth_start_url(): string
{
    return add_query_arg([
        'client_id'     => gp_client_id(),
        'redirect_uri'  => gp_redirect_uri(),
        'response_type' => 'code',
        'scope'         => GP_SCOPE,
        'access_type'   => 'offline',
        'include_granted_scopes' => 'true',
        'prompt'        => 'consent',
        'state'         => wp_create_nonce('rv_gp_oauth'),
    ], 'https://accounts.google.com/o/oauth2/v2/auth');
}

/** OAuth redirect target: exchange the code for tokens, then bounce back. */
add_action('admin_post_rv_gp_callback', function () {
    if (! current_user_can('upload_files')) {
        wp_die(esc_html__('You do not have permission.', 'sage'));
    }
    $page = admin_url('upload.php?page=rv-google-photos');

    if (! empty($_GET['error'])) {
        wp_safe_redirect(add_query_arg('rv_gp', 'denied', $page));
        exit;
    }
    $state = isset($_GET['state']) ? sanitize_text_field(wp_unslash($_GET['state'])) : '';
    if (! wp_verify_nonce($state, 'rv_gp_oauth')) {
        wp_safe_redirect(add_query_arg('rv_gp', 'badstate', $page));
        exit;
    }
    $code = isset($_GET['code']) ? sanitize_text_field(wp_unslash($_GET['code'])) : '';
    if ($code === '') {
        wp_safe_redirect(add_query_arg('rv_gp', 'nocode', $page));
        exit;
    }

    $res = wp_remote_post('https://oauth2.googleapis.com/token', [
        'timeout' => 15,
        'body'    => [
            'code'          => $code,
            'client_id'     => gp_client_id(),
            'client_secret' => gp_client_secret(),
            'redirect_uri'  => gp_redirect_uri(),
            'grant_type'    => 'authorization_code',
        ],
    ]);
    $data = is_wp_error($res) ? [] : json_decode((string) wp_remote_retrieve_body($res), true);
    if (! is_array($data) || empty($data['access_token'])) {
        wp_safe_redirect(add_query_arg('rv_gp', 'exchangefail', $page));
        exit;
    }

    gp_tokens_save([
        'access_token'  => (string) $data['access_token'],
        'refresh_token' => (string) ($data['refresh_token'] ?? (gp_tokens_get()['refresh_token'] ?? '')),
        'expires_at'    => time() + (int) ($data['expires_in'] ?? 3600) - 60,
    ]);
    wp_safe_redirect(add_query_arg('rv_gp', 'connected', $page));
    exit;
});

/** A valid access token, refreshing via the refresh token when expired. */
function gp_access_token(): string
{
    $t = gp_tokens_get();
    if (! empty($t['access_token']) && (int) ($t['expires_at'] ?? 0) > time()) {
        return (string) $t['access_token'];
    }
    if (empty($t['refresh_token'])) {
        return (string) ($t['access_token'] ?? '');
    }
    $res = wp_remote_post('https://oauth2.googleapis.com/token', [
        'timeout' => 15,
        'body'    => [
            'client_id'     => gp_client_id(),
            'client_secret' => gp_client_secret(),
            'refresh_token' => (string) $t['refresh_token'],
            'grant_type'    => 'refresh_token',
        ],
    ]);
    $data = is_wp_error($res) ? [] : json_decode((string) wp_remote_retrieve_body($res), true);
    if (! is_array($data) || empty($data['access_token'])) {
        return '';
    }
    $t['access_token'] = (string) $data['access_token'];
    $t['expires_at']   = time() + (int) ($data['expires_in'] ?? 3600) - 60;
    gp_tokens_save($t);
    return (string) $t['access_token'];
}

/* Picker API -------------------------------------------------------------- */
function gp_api(string $method, string $url, ?array $body = null): array
{
    $token = gp_access_token();
    if ($token === '') {
        return [0, ['error' => ['message' => 'Not connected.']]];
    }
    $args = [
        'method'  => $method,
        'timeout' => 20,
        'headers' => ['Authorization' => 'Bearer ' . $token, 'Content-Type' => 'application/json'],
    ];
    if ($body !== null) {
        // Empty array must serialize to a JSON object ({}), not [] — the Picker
        // sessions.create endpoint rejects an array body.
        $args['body'] = $body === [] ? '{}' : wp_json_encode($body);
    }
    $res  = wp_remote_request($url, $args);
    if (is_wp_error($res)) {
        return [0, ['error' => ['message' => $res->get_error_message()]]];
    }
    $code = (int) wp_remote_retrieve_response_code($res);
    $data = json_decode((string) wp_remote_retrieve_body($res), true);
    return [$code, is_array($data) ? $data : []];
}

function gp_create_session(): ?array
{
    [$code, $data] = gp_api('POST', 'https://photospicker.googleapis.com/v1/sessions', []);
    if ($code === 200 && ! empty($data['id'])) {
        update_option(GP_SESSION_OPT, ['id' => (string) $data['id'], 'created' => time()], false);
        return $data;
    }
    return null;
}
function gp_get_session(string $id): ?array
{
    [$code, $data] = gp_api('GET', 'https://photospicker.googleapis.com/v1/sessions/' . rawurlencode($id));
    return ($code === 200 && is_array($data)) ? $data : null;
}
function gp_list_media(string $sessionId): array
{
    $items = [];
    $page  = '';
    do {
        $url = add_query_arg(array_filter(['sessionId' => $sessionId, 'pageSize' => 100, 'pageToken' => $page]), 'https://photospicker.googleapis.com/v1/mediaItems');
        [$code, $data] = gp_api('GET', $url);
        if ($code !== 200) {
            break;
        }
        foreach ((array) ($data['mediaItems'] ?? []) as $mi) {
            $items[] = $mi;
        }
        $page = (string) ($data['nextPageToken'] ?? '');
    } while ($page !== '');
    return $items;
}

/** Download each picked photo and sideload it into the Media Library. */
function gp_import_session(string $sessionId): array
{
    require_once ABSPATH . 'wp-admin/includes/file.php';
    require_once ABSPATH . 'wp-admin/includes/media.php';
    require_once ABSPATH . 'wp-admin/includes/image.php';

    $token    = gp_access_token();
    $items    = gp_list_media($sessionId);
    $imported = 0;
    $skipped  = 0;

    foreach ($items as $mi) {
        $file = $mi['mediaFile'] ?? [];
        $base = (string) ($file['baseUrl'] ?? '');
        $mime = (string) ($file['mimeType'] ?? '');
        // Photos only (skip videos, which need =dv and are large).
        if ($base === '' || strpos($mime, 'image/') !== 0) {
            $skipped++;
            continue;
        }
        $name = (string) ($file['filename'] ?? ('google-photo-' . substr(md5($base), 0, 8) . '.jpg'));

        $res = wp_remote_get($base . '=d', [
            'timeout'  => 60,
            'headers'  => ['Authorization' => 'Bearer ' . $token],
        ]);
        if (is_wp_error($res) || (int) wp_remote_retrieve_response_code($res) !== 200) {
            $skipped++;
            continue;
        }
        $bytes = wp_remote_retrieve_body($res);
        if ($bytes === '') {
            $skipped++;
            continue;
        }

        $upload = wp_upload_bits($name, null, $bytes);
        if (! empty($upload['error'])) {
            $skipped++;
            continue;
        }
        $filetype = wp_check_filetype($upload['file']);
        $attach_id = wp_insert_attachment([
            'post_mime_type' => $filetype['type'] ?: $mime,
            'post_title'     => sanitize_file_name(pathinfo($name, PATHINFO_FILENAME)),
            'post_status'    => 'inherit',
        ], $upload['file']);
        if (is_wp_error($attach_id) || ! $attach_id) {
            $skipped++;
            continue;
        }
        wp_update_attachment_metadata($attach_id, wp_generate_attachment_metadata($attach_id, $upload['file']));
        $imported++;
    }

    return ['imported' => $imported, 'skipped' => $skipped, 'total' => count($items)];
}

/* Admin page (Media → Google Photos) -------------------------------------- */
add_action('admin_menu', function () {
    add_submenu_page(
        'upload.php',
        __('Google Photos', 'sage'),
        __('Google Photos', 'sage'),
        'upload_files',
        'rv-google-photos',
        __NAMESPACE__ . '\\render_google_photos_page'
    );
});

function render_google_photos_page(): void
{
    if (! current_user_can('upload_files')) {
        wp_die(esc_html__('You do not have permission.', 'sage'));
    }

    $notice = null;

    // Form actions.
    if ('POST' === ($_SERVER['REQUEST_METHOD'] ?? '') && isset($_POST['rv_gp_nonce'])) {
        check_admin_referer('rv_gp_action', 'rv_gp_nonce');
        $action = sanitize_key($_POST['rv_gp_do'] ?? '');
        if ($action === 'disconnect') {
            gp_disconnect();
            $notice = ['notice-success', __('Disconnected from Google Photos.', 'sage')];
        } elseif ($action === 'start') {
            $s = gp_create_session();
            $notice = $s
                ? ['notice-info', __('Picker session started — open the picker, choose your photos, then click “Import my selection”.', 'sage')]
                : ['notice-error', __('Could not start a picker session. Reconnect and try again.', 'sage')];
        } elseif ($action === 'import') {
            $sess = get_option(GP_SESSION_OPT, []);
            $sid  = is_array($sess) ? (string) ($sess['id'] ?? '') : '';
            if ($sid === '') {
                $notice = ['notice-error', __('No active picker session. Click “Start picking” first.', 'sage')];
            } else {
                $session = gp_get_session($sid);
                if (! $session || empty($session['mediaItemsSet'])) {
                    $notice = ['notice-warning', __('It looks like you haven’t finished selecting photos in the picker yet. Pick some, then click “Import my selection” again.', 'sage')];
                } else {
                    $r = gp_import_session($sid);
                    delete_option(GP_SESSION_OPT);
                    $notice = ['notice-success', sprintf(
                        /* translators: 1: imported count, 2: skipped count. */
                        __('Imported %1$d photo(s) into your Media Library. Skipped %2$d (videos or errors).', 'sage'),
                        (int) $r['imported'],
                        (int) $r['skipped']
                    )];
                }
            }
        }
    }

    // Callback query notices.
    $flag = isset($_GET['rv_gp']) ? sanitize_key($_GET['rv_gp']) : '';
    $map  = [
        'connected'    => ['notice-success', __('Connected to Google Photos.', 'sage')],
        'denied'       => ['notice-warning', __('Google sign-in was cancelled.', 'sage')],
        'badstate'     => ['notice-error', __('Security check failed — please try connecting again.', 'sage')],
        'nocode'       => ['notice-error', __('Google did not return an authorization code.', 'sage')],
        'exchangefail' => ['notice-error', __('Could not exchange the Google authorization code for a token. Check the Client ID/secret and redirect URI.', 'sage')],
    ];
    if ($flag && isset($map[$flag]) && ! $notice) {
        $notice = $map[$flag];
    }

    $configured = gp_configured();
    $connected  = gp_connected();
    $apisUrl    = admin_url('themes.php?page=rv-theme-apis');
    $sess       = get_option(GP_SESSION_OPT, []);
    $sid        = is_array($sess) ? (string) ($sess['id'] ?? '') : '';

    echo '<div class="wrap">';
    echo '<h1>' . esc_html__('Google Photos', 'sage') . '</h1>';
    echo '<p style="max-width:72ch">' . esc_html__('Import your own photography from Google Photos into the Media Library. You pick the photos in Google’s own picker; only what you select is imported — nothing else in your library is accessed.', 'sage') . '</p>';

    if ($notice) {
        printf('<div class="notice %1$s is-dismissible"><p>%2$s</p></div>', esc_attr($notice[0]), esc_html($notice[1]));
    }

    if (! $configured) {
        echo '<div class="notice notice-info inline"><p>' . sprintf(
            /* translators: %s: Theme APIs URL. */
            wp_kses_post(__('Add your Google OAuth <strong>Client ID</strong> and <strong>secret</strong> under “Google Photos” on the <a href="%s">Theme APIs</a> page to get started.', 'sage')),
            esc_url($apisUrl)
        ) . '</p></div>';
        echo '<h2>' . esc_html__('One-time setup', 'sage') . '</h2>';
        echo '<ol style="max-width:72ch">';
        echo '<li>' . wp_kses_post(__('In <strong>Google Cloud Console</strong>, enable the <strong>Photos Picker API</strong> and create an <strong>OAuth 2.0 Client ID</strong> (application type: Web application).', 'sage')) . '</li>';
        echo '<li>' . wp_kses_post(sprintf(__('Add this exact <strong>Authorized redirect URI</strong>: <code>%s</code>', 'sage'), esc_html(gp_redirect_uri()))) . '</li>';
        echo '<li>' . wp_kses_post(sprintf(__('Paste the Client ID and secret under “Google Photos” on the <a href="%s">Theme APIs</a> page.', 'sage'), esc_url($apisUrl))) . '</li>';
        echo '<li>' . esc_html__('For personal use, keep the OAuth app in “Testing” and add yourself as a test user — no Google verification required.', 'sage') . '</li>';
        echo '</ol>';
        echo '</div>';
        return;
    }

    echo '<p><span class="description">' . esc_html__('Redirect URI (must be registered in Google Cloud):', 'sage') . '</span> <code>' . esc_html(gp_redirect_uri()) . '</code></p>';

    if (! $connected) {
        echo '<p><a class="button button-primary button-hero" href="' . esc_url(gp_oauth_start_url()) . '">' . esc_html__('Connect Google Photos', 'sage') . '</a></p>';
        echo '</div>';
        return;
    }

    // Connected: pick + import controls.
    echo '<div class="notice notice-success inline"><p>' . esc_html__('✓ Connected to Google Photos.', 'sage') . '</p></div>';

    $pickerUri = '';
    if ($sid !== '') {
        $session = gp_get_session($sid);
        $pickerUri = is_array($session) ? (string) ($session['pickerUri'] ?? '') : '';
    }

    echo '<form method="post" action="" style="margin:1rem 0">';
    wp_nonce_field('rv_gp_action', 'rv_gp_nonce');
    if ($sid === '') {
        echo '<input type="hidden" name="rv_gp_do" value="start" />';
        echo '<button type="submit" class="button button-primary button-hero">' . esc_html__('Start picking photos', 'sage') . '</button>';
    } else {
        if ($pickerUri !== '') {
            echo '<p><a class="button button-primary button-hero" href="' . esc_url($pickerUri) . '" target="_blank" rel="noopener">' . esc_html__('Open the Google Photos picker', 'sage') . '</a></p>';
            echo '<p class="description">' . esc_html__('Choose your photos in the tab that opens, then come back and click below.', 'sage') . '</p>';
        }
        echo '<button type="submit" name="rv_gp_do" value="import" class="button button-primary">' . esc_html__('Import my selection', 'sage') . '</button> ';
        echo '<button type="submit" name="rv_gp_do" value="start" class="button">' . esc_html__('Start over', 'sage') . '</button>';
    }
    echo '</form>';

    echo '<form method="post" action="" style="margin-top:2rem">';
    wp_nonce_field('rv_gp_action', 'rv_gp_nonce');
    echo '<input type="hidden" name="rv_gp_do" value="disconnect" />';
    echo '<button type="submit" class="button-link delete">' . esc_html__('Disconnect Google Photos', 'sage') . '</button>';
    echo '</form>';

    echo '</div>';
}
