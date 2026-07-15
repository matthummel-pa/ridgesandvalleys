<?php

/**
 * Self-host Google Fonts. Downloads the active families' woff2 files into
 * wp-content/uploads/prt-fonts/ (server-side, via the WordPress HTTP API),
 * writes a local @font-face stylesheet, and — when enabled — serves that and
 * removes every Google Fonts request from the page.
 *
 * Appearance -> Local Fonts.
 */

namespace App;

/**
 * Where self-hosted font files and the generated stylesheet live: a
 * dedicated prt-fonts folder inside wp-content/uploads/ (not inside the theme
 * folder), so downloaded fonts survive a theme update/reinstall and don't
 * need to be write-protected the way theme files typically are.
 */
function prt_fonts_paths()
{
    $up = wp_upload_dir();
    return [
        'dir' => trailingslashit($up['basedir']) . 'prt-fonts',
        'url' => trailingslashit($up['baseurl']) . 'prt-fonts',
    ];
}

/**
 * Builds the list of families to download: name => css2 family slug (the
 * query-string fragment Google's css2 endpoint expects), for every font
 * currently selected in the Customizer (heading/body/nav/button) plus the
 * theme's hardcoded defaults (Geist, Inter) and the mono font used for code
 * blocks. Always including the defaults means switching away from a custom
 * font and back doesn't require re-downloading; JetBrains Mono is added
 * unconditionally since code-highlight.php can't self-host its own font.
 */
function prt_fonts_to_host()
{
    $fonts = function_exists('App\\prt_fonts') ? prt_fonts() : [];
    $names = array_unique(array_filter([
        get_theme_mod('prt_font_heading', 'Geist'),
        get_theme_mod('prt_font_body', 'Inter'),
        get_theme_mod('prt_font_nav', 'Default'),
        get_theme_mod('prt_font_button', 'Default'),
        'Geist', 'Inter',
    ]));
    $slugs = [];
    foreach ($names as $n) {
        if ($n !== 'Default' && ! empty($fonts[$n][0])) {
            $slugs[$n] = $fonts[$n][0];
        }
    }
    $slugs['JetBrains Mono'] = 'JetBrains+Mono:wght@400;500;700';
    return $slugs;
}

/** Registers the Appearance -> Local Fonts admin page. */
add_action('admin_menu', function () {
    add_theme_page(__('Local Fonts', 'pressroot'), __('Local Fonts', 'pressroot'), 'manage_options', 'prt-local-fonts', __NAMESPACE__ . '\\prt_fonts_page');
});

/**
 * Renders the Local Fonts admin page: current cache status, the families
 * that will be downloaded, a "download/re-download" button (admin-post ->
 * prt_build_fonts), and a toggle to actually start serving the local copy
 * (admin-post -> prt_toggle_fonts). The toggle is disabled until a build has
 * completed at least once, so a site can't switch on self-hosting before any
 * font files actually exist to serve.
 */
function prt_fonts_page()
{
    if (! current_user_can('manage_options')) {
        return;
    }
    $on    = (bool) get_option('prt_selfhost_on', false);
    $built = (int) get_option('prt_local_fonts_built', 0);
    $count = (int) get_option('prt_local_fonts_count', 0);
    $post  = admin_url('admin-post.php');
    $notice = isset($_GET['prt_fonts']) ? sanitize_key($_GET['prt_fonts']) : '';
    ?>
    <div class="wrap">
        <h1><?php esc_html_e('Local Fonts', 'pressroot'); ?></h1>
        <?php if ($notice === 'built') : ?>
            <div class="notice notice-success is-dismissible"><p><?php esc_html_e('Fonts downloaded and self-hosted.', 'pressroot'); ?></p></div>
        <?php elseif ($notice === 'builderr') : ?>
            <div class="notice notice-error is-dismissible"><p><?php esc_html_e('Could not download some fonts. Check that the server can reach fonts.googleapis.com.', 'pressroot'); ?></p></div>
        <?php endif; ?>

        <p class="description" style="max-width:640px">
            <?php esc_html_e('Download the fonts your theme uses and serve them from your own server. This removes the external request to Google, improves privacy, and speeds up first paint.', 'pressroot'); ?>
        </p>

        <table class="form-table"><tbody>
            <tr>
                <th><?php esc_html_e('Status', 'pressroot'); ?></th>
                <td>
                    <?php if ($built) : ?>
                        <span class="dashicons dashicons-yes-alt" style="color:#2f6b4e"></span>
                        <?php printf(esc_html__('%d font files cached, last built %s ago.', 'pressroot'), $count, esc_html(human_time_diff($built))); ?>
                    <?php else : ?>
                        <em><?php esc_html_e('No local fonts yet.', 'pressroot'); ?></em>
                    <?php endif; ?>
                </td>
            </tr>
            <tr>
                <th><?php esc_html_e('Families', 'pressroot'); ?></th>
                <td><code><?php echo esc_html(implode(', ', array_keys(prt_fonts_to_host()))); ?></code></td>
            </tr>
        </tbody></table>

        <form method="post" action="<?php echo esc_url($post); ?>" style="display:inline-block;margin-right:10px">
            <input type="hidden" name="action" value="prt_build_fonts">
            <?php wp_nonce_field('prt_build_fonts'); ?>
            <button class="button button-primary"><?php echo $built ? esc_html__('Re-download fonts', 'pressroot') : esc_html__('Download fonts now', 'pressroot'); ?></button>
        </form>

        <form method="post" action="<?php echo esc_url($post); ?>" style="display:inline-block">
            <input type="hidden" name="action" value="prt_toggle_fonts">
            <?php wp_nonce_field('prt_toggle_fonts'); ?>
            <label class="prt-switch" style="vertical-align:middle">
                <input type="checkbox" name="prt_selfhost_on" value="1" <?php checked($on); ?> <?php disabled(! $built); ?> onchange="this.form.submit()">
                <?php esc_html_e('Serve fonts locally (and remove Google requests)', 'pressroot'); ?>
            </label>
        </form>
    </div>
    <?php
}

/**
 * Download handler: for each family in prt_fonts_to_host(), fetches Google's
 * css2 stylesheet, rewrites every gstatic woff2 URL to point at a locally
 * downloaded copy (fetching + caching that file on disk if it isn't already
 * present, keyed by an md5 of the source URL so re-runs skip files already
 * downloaded), and concatenates the results into one fonts.css. That combined
 * file is what prt-local-fonts serves on the front end once self-hosting is
 * enabled below.
 */
add_action('admin_post_prt_build_fonts', function () {
    if (! current_user_can('manage_options') || ! check_admin_referer('prt_build_fonts')) {
        wp_die('Not allowed');
    }
    $paths = prt_fonts_paths();
    if (! wp_mkdir_p($paths['dir'])) {
        wp_safe_redirect(admin_url('themes.php?page=prt-local-fonts&prt_fonts=builderr'));
        exit;
    }

    // Google's css2 endpoint serves modern woff2 @font-face rules only to
    // browser-like User-Agents; WordPress's default HTTP API UA gets served
    // an older/less complete CSS format instead. Spoofing a Chrome UA here
    // guarantees we always get the woff2 URLs the regex below expects.
    $ua = 'Mozilla/5.0 (Windows NT 10.0; Win64; x64) AppleWebKit/537.36 (KHTML, like Gecko) Chrome/120.0 Safari/537.36';
    $css_all = "/* Self-hosted by the matthummel theme. Do not edit; regenerate from Appearance -> Local Fonts. */\n";
    $count = 0;
    $ok = true;

    foreach (prt_fonts_to_host() as $name => $slug) {
        $r = wp_remote_get('https://fonts.googleapis.com/css2?family=' . $slug . '&display=swap', [
            'timeout' => 20,
            'headers' => ['User-Agent' => $ua],
        ]);
        if (is_wp_error($r) || wp_remote_retrieve_response_code($r) !== 200) {
            $ok = false;
            continue;
        }
        $css = wp_remote_retrieve_body($r);
        // Rewrite each url(https://fonts.gstatic.com/.../*.woff2) reference to
        // the local copy, downloading it first if we haven't cached it yet.
        $css = preg_replace_callback('#url\((https://fonts\.gstatic\.com/[^)]+\.woff2)\)#', function ($m) use ($paths, &$count, &$ok) {
            $url  = $m[1];
            $file = md5($url) . '.woff2';
            $dest = $paths['dir'] . '/' . $file;
            if (! file_exists($dest)) {
                $f = wp_remote_get($url, ['timeout' => 25]);
                if (is_wp_error($f) || wp_remote_retrieve_response_code($f) !== 200) {
                    $ok = false;
                    return $m[0];
                }
                file_put_contents($dest, wp_remote_retrieve_body($f));
            }
            $count++;
            return 'url(' . $paths['url'] . '/' . $file . ')';
        }, $css);
        $css_all .= "\n/* {$name} */\n" . $css;
    }

    file_put_contents($paths['dir'] . '/fonts.css', $css_all);
    update_option('prt_local_fonts_url', $paths['url'] . '/fonts.css');
    update_option('prt_local_fonts_built', time());
    update_option('prt_local_fonts_count', $count);

    wp_safe_redirect(admin_url('themes.php?page=prt-local-fonts&prt_fonts=' . ($ok && $count ? 'built' : 'builderr')));
    exit;
});

/**
 * Toggle handler: flips the "serve locally" switch on/off. Deliberately
 * doesn't require a build to exist (the page-level checkbox already disables
 * itself until $built is true, but this handler doesn't re-check that), so a
 * direct POST without a prior download would just enable serving from a URL
 * that doesn't exist yet.
 */
add_action('admin_post_prt_toggle_fonts', function () {
    if (! current_user_can('manage_options') || ! check_admin_referer('prt_toggle_fonts')) {
        wp_die('Not allowed');
    }
    update_option('prt_selfhost_on', isset($_POST['prt_selfhost_on']));
    wp_safe_redirect(admin_url('themes.php?page=prt-local-fonts'));
    exit;
});

/**
 * Front-end: when self-hosting is on, enqueue the generated fonts.css.
 * Priority 4 — before setup.php's Google Fonts enqueue at its default
 * priority — isn't what prevents the Google stylesheet from loading (that's
 * the style_loader_tag filter below); it just makes sure the local stylesheet
 * itself is registered/output early, alongside other early-priority styles.
 */
add_action('wp_enqueue_scripts', function () {
    if (! get_option('prt_selfhost_on', false)) {
        return;
    }
    $url = get_option('prt_local_fonts_url', '');
    if ($url) {
        wp_enqueue_style('prt-local-fonts', $url, [], (int) get_option('prt_local_fonts_built', 1));
    }
}, 4);

/**
 * Remove any Google Fonts <link> tag from the rendered page when self-hosting
 * is on. This is the actual mechanism that stops the external Google request;
 * it works regardless of which file enqueued the Google stylesheet (theme
 * setup.php, a plugin, etc.) since it matches by the output URL, not by handle.
 */
add_filter('style_loader_tag', function ($tag, $handle, $href) {
    if (get_option('prt_selfhost_on', false) && strpos((string) $href, 'fonts.googleapis.com') !== false) {
        return '';
    }
    return $tag;
}, 10, 3);

/**
 * Drop the Google Fonts preconnect resource hints too when self-hosting is
 * on — otherwise the browser would still open an early connection to
 * fonts.gstatic.com/googleapis.com for a request that no longer happens,
 * which is a wasted connection and a small privacy leak the whole feature is
 * meant to avoid.
 */
add_filter('wp_resource_hints', function ($hints, $relation) {
    if ($relation === 'preconnect' && get_option('prt_selfhost_on', false)) {
        $hints = array_filter($hints, function ($h) {
            $href = is_array($h) ? ($h['href'] ?? '') : $h;
            return strpos((string) $href, 'fonts.g') === false;
        });
    }
    return $hints;
}, 20, 2);
