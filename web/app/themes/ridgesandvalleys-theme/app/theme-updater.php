<?php

/**
 * Theme updater — push the latest committed theme to the live site from
 * wp-admin. Lives at Appearance → "Update Theme".
 *
 * Clicking "Update from GitHub" triggers the repo's deploy-sage-theme.yml workflow
 * via the GitHub Actions API (workflow_dispatch). CI then builds the Sage theme
 * and rsyncs it to the live theme folder over SSH — the exact same pipeline a
 * `git push` uses. Code only; it never touches the database, uploads, or content.
 *
 * Auth reuses the GitHub token from Appearance → Theme APIs
 * (\App\integration('github','token')) or the RV_INTEGRATION_GITHUB_TOKEN
 * wp-config constant. For dispatch to work the token must be a fine-grained PAT
 * with these permissions on this repository:
 *   - Actions:  Read and write   (dispatch the workflow + read run status)
 *   - Contents: Read-only         (resolve the workflow on the default branch)
 */

namespace App;

/** Repo + workflow the updater deploys. All filterable. */
function updater_repo(): array
{
    return [
        'owner'    => (string) apply_filters('rv/updater_owner', function_exists(__NAMESPACE__ . '\\github_owner') ? github_owner() : 'matthummel-pa'),
        'repo'     => (string) apply_filters('rv/updater_repo', 'ridgesandvalleys'),
        'workflow' => (string) apply_filters('rv/updater_workflow', 'deploy-sage-theme.yml'),
        'ref'      => (string) apply_filters('rv/updater_ref', 'main'),
    ];
}

/** POST JSON to the GitHub API with the theme's GitHub auth headers. */
function updater_api_post(string $url, array $body): array
{
    $res = wp_remote_post($url, [
        'timeout' => 15,
        'headers' => array_merge(github_headers(), ['Content-Type' => 'application/json']),
        'body'    => wp_json_encode($body),
    ]);
    if (is_wp_error($res)) {
        return [0, ['message' => $res->get_error_message()]];
    }
    $code = (int) wp_remote_retrieve_response_code($res);
    $data = json_decode((string) wp_remote_retrieve_body($res), true);
    return [$code, is_array($data) ? $data : []];
}

/** The most recent run of the deploy workflow, or null. */
function updater_latest_run(): ?array
{
    $r   = updater_repo();
    $url = 'https://api.github.com/repos/' . rawurlencode($r['owner']) . '/' . rawurlencode($r['repo'])
        . '/actions/workflows/' . rawurlencode($r['workflow']) . '/runs?per_page=1';
    $data = github_get($url);
    if (! $data || empty($data['workflow_runs'][0]) || ! is_array($data['workflow_runs'][0])) {
        return null;
    }
    $run = $data['workflow_runs'][0];
    return [
        'status'     => (string) ($run['status'] ?? ''),      // queued | in_progress | completed
        'conclusion' => (string) ($run['conclusion'] ?? ''),  // success | failure | cancelled | ''
        'html_url'   => (string) ($run['html_url'] ?? ''),
        'created_at' => (string) ($run['created_at'] ?? ''),
        'event'      => (string) ($run['event'] ?? ''),
        'number'     => (int) ($run['run_number'] ?? 0),
    ];
}

/** Trigger the deploy workflow. Returns [bool ok, string message]. */
function updater_dispatch(): array
{
    $r     = updater_repo();
    $token = github_token();
    if ($token === '') {
        return [false, __('No GitHub token is set. Add one under “GitHub” on the Theme APIs page first.', 'sage')];
    }
    $url = 'https://api.github.com/repos/' . rawurlencode($r['owner']) . '/' . rawurlencode($r['repo'])
        . '/actions/workflows/' . rawurlencode($r['workflow']) . '/dispatches';

    [$code, $data] = updater_api_post($url, ['ref' => $r['ref']]);

    if ($code === 204) {
        return [true, __('Deploy triggered. The live theme updates in about 1–2 minutes — watch the status below.', 'sage')];
    }

    $msg = isset($data['message']) ? (string) $data['message'] : __('Unknown error.', 'sage');
    if ($code === 401 || $code === 403) {
        $msg .= ' ' . __('The token likely lacks “Actions: Read and write” on this repository.', 'sage');
    } elseif ($code === 404) {
        $msg .= ' ' . __('Check the repo name and that the workflow exists on the default branch.', 'sage');
    }
    /* translators: 1: HTTP status code, 2: error detail from GitHub. */
    return [false, sprintf(__('GitHub returned %1$d: %2$s', 'sage'), $code, $msg)];
}

/** Appearance → Update Theme. */
add_action('admin_menu', function () {
    add_theme_page(
        __('Update Theme', 'sage'),
        __('Update Theme', 'sage'),
        'update_themes',
        'rv-theme-update',
        __NAMESPACE__ . '\\render_theme_updater_page'
    );
});

/** Save handler + renderer for the updater page. */
function render_theme_updater_page(): void
{
    if (! current_user_can('update_themes') && ! current_user_can('edit_theme_options')) {
        wp_die(esc_html__('You do not have permission to update the theme.', 'sage'));
    }

    $notice = null;
    if ('POST' === ($_SERVER['REQUEST_METHOD'] ?? '') && isset($_POST['rv_updater_nonce'])) {
        check_admin_referer('rv_theme_update', 'rv_updater_nonce');
        [$ok, $msg] = updater_dispatch();
        $notice = [$ok ? 'notice-success' : 'notice-error', $msg];
    }

    $r        = updater_repo();
    $hasToken = github_token() !== '';
    $apisUrl  = admin_url('themes.php?page=rv-theme-apis');
    $run      = $hasToken ? updater_latest_run() : null;

    echo '<div class="wrap">';
    echo '<h1>' . esc_html__('Update Theme', 'sage') . '</h1>';
    echo '<p style="max-width:70ch">' . esc_html__('Deploy the latest committed theme to the live site. This runs the same build-and-SSH pipeline a git push uses, so it updates theme files only — never your content, database, or uploads.', 'sage') . '</p>';

    if ($notice) {
        printf('<div class="notice %1$s is-dismissible"><p>%2$s</p></div>', esc_attr($notice[0]), esc_html($notice[1]));
    }

    // Current deploy status.
    if ($run) {
        if ($run['status'] !== 'completed') {
            $label = esc_html__('A deploy is running now…', 'sage');
            $cls   = 'notice-warning';
        } elseif ($run['conclusion'] === 'success') {
            $label = esc_html__('✓ Last deploy succeeded.', 'sage');
            $cls   = 'notice-success';
        } else {
            /* translators: %s: run conclusion (failure, cancelled). */
            $label = esc_html(sprintf(__('Last deploy: %s.', 'sage'), $run['conclusion'] ?: 'unknown'));
            $cls   = 'notice-error';
        }
        $when = $run['created_at'] ? esc_html(human_time_diff(strtotime($run['created_at'])) . ' ' . __('ago', 'sage')) : '';
        printf(
            '<div class="notice %1$s inline"><p>%2$s <span class="description">#%3$d · %4$s · %5$s</span> — <a href="%6$s" target="_blank" rel="noopener">%7$s</a></p></div>',
            esc_attr($cls),
            $label,
            (int) $run['number'],
            esc_html($run['event']),
            $when,
            esc_url($run['html_url']),
            esc_html__('view run on GitHub', 'sage')
        );
    }

    echo '<hr />';

    if (! $hasToken) {
        echo '<div class="notice notice-info inline"><p>' . sprintf(
            /* translators: %s: URL of Theme APIs page. */
            wp_kses_post(__('To enable one-click updates, add a GitHub token under <strong>GitHub</strong> on the <a href="%s">Theme APIs</a> page (or define <code>RV_INTEGRATION_GITHUB_TOKEN</code> in wp-config.php).', 'sage')),
            esc_url($apisUrl)
        ) . '</p></div>';
        echo '<h2>' . esc_html__('Token setup (one time)', 'sage') . '</h2>';
        echo '<ol style="max-width:70ch">';
        echo '<li>' . wp_kses_post(__('Create a <strong>fine-grained personal access token</strong> at GitHub → Settings → Developer settings → Fine-grained tokens, scoped to only the <code>ridgesandvalleys</code> repository.', 'sage')) . '</li>';
        echo '<li>' . wp_kses_post(__('Give it these repository permissions: <strong>Actions: Read and write</strong> and <strong>Contents: Read-only</strong>.', 'sage')) . '</li>';
        echo '<li>' . wp_kses_post(sprintf(__('Paste it into <strong>GitHub → Access token</strong> on the <a href="%s">Theme APIs</a> page and save.', 'sage'), esc_url($apisUrl))) . '</li>';
        echo '</ol>';
    } else {
        echo '<form method="post" action="">';
        wp_nonce_field('rv_theme_update', 'rv_updater_nonce');
        printf(
            '<p><button type="submit" class="button button-primary button-hero">%1$s</button></p>',
            esc_html__('Update theme from GitHub', 'sage')
        );
        printf(
            '<p class="description">%s</p>',
            esc_html(sprintf(
                /* translators: 1: owner/repo, 2: branch, 3: workflow file. */
                __('Deploys %1$s@%2$s via %3$s. Anyone can also trigger this by pushing to the branch.', 'sage'),
                $r['owner'] . '/' . $r['repo'],
                $r['ref'],
                $r['workflow']
            ))
        );
        echo '</form>';
        echo '<p style="margin-top:1rem"><a class="button" href="' . esc_url(admin_url('themes.php?page=rv-theme-update')) . '">' . esc_html__('Refresh status', 'sage') . '</a></p>';
    }

    echo '</div>';
}
