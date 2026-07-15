<?php

/**
 * Style Kit presets (data + apply logic) + Import / Export / Reset of all
 * theme settings.
 *
 * The Style Kits were originally also exposed as their own manually-applied
 * tab on Appearance -> Pressroot. That tab is gone: every Site Type (see
 * app/ai-assistant.php) already applies its own matching kit automatically
 * when chosen, which made a separate "pick a kit yourself" grid redundant —
 * one obvious way to set the site's look instead of two. `prt_style_kits()`
 * and `prt_apply_style_kit()` are unchanged and still do the real work
 * (site-type selection calls `prt_apply_style_kit()` directly); only the
 * manual swatch-picker UI and its `admin_post_prt_apply_kit` handler were
 * removed.
 *
 * Export/Import/Reset are still here (still real, still useful as a backup/
 * restore/migrate tool), just relocated: `prt_settings_backup_fields_html()`
 * now renders inside a collapsed "Advanced" section on the Site Types tab
 * (app/ai-assistant.php), the same pattern already used there for AI
 * Connectors. This used to be its own "Theme Tools" page, then the "Style
 * Kits" tab. All actions are nonce-protected and capability-gated, unchanged
 * from before.
 */

namespace App;

/** Every theme mod that belongs to this theme (prt_* keys). */
function prt_owned_mods()
{
    $mods = get_theme_mods() ?: [];
    $out  = [];
    foreach ($mods as $k => $v) {
        if (strpos($k, 'prt_') === 0) {
            $out[$k] = $v;
        }
    }
    return $out;
}

/**
 * Gate an admin_post handler behind the standard nonce + capability check
 * used identically by the Export/Import/Reset actions below (the manual
 * "apply kit" action this was originally written for four of was removed —
 * see the file docblock): the current user must be able to
 * 'edit_theme_options' AND the request must carry a valid nonce for
 * $nonceAction (matching the wp_nonce_field($nonceAction) each form in
 * prt_settings_backup_fields_html() emits). On failure, dies with the same
 * "Not allowed" message every handler used before this was extracted —
 * behavior is unchanged, just no longer duplicated.
 *
 * @param string $nonceAction The nonce action string (also the admin_post 'action' value).
 * @return void Returns normally (falls through) when the check passes; wp_die()s otherwise.
 */
function prt_require_admin_post(string $nonceAction): void
{
    if (! current_user_can('edit_theme_options') || ! check_admin_referer($nonceAction)) {
        wp_die('Not allowed');
    }
}

/**
 * Apply a Style Kit's `mods` array to theme mods via set_theme_mod().
 * Called when a Site Type is chosen (admin_post_prt_apply_site_type in
 * app/ai-assistant.php, which looks up that type's `kit` slug) and by
 * `wp pressroot kit apply <slug>` (Prt_Kit_Command::apply() in app/cli.php),
 * so both call sites use the exact same lookup + loop instead of maintaining
 * independent copies. (The manual per-kit "Apply" button that used to call
 * this directly via its own admin_post_prt_apply_kit handler was removed —
 * see the file docblock above.)
 *
 * @param string $slug Kit slug, must be a key in prt_style_kits().
 * @return bool True if the slug was found and its mods were applied, false if unknown.
 */
function prt_apply_style_kit(string $slug): bool
{
    $kits = prt_style_kits();
    if (! isset($kits[$slug])) {
        return false;
    }
    foreach ($kits[$slug]['mods'] as $k => $v) {
        set_theme_mod($k, $v);
    }
    return true;
}

/** One-click design presets (palette + fonts + radius). */
function prt_style_kits()
{
    return apply_filters('pressroot/style_kits', [
        'paper_space' => [
            'mode'  => 'light', 'vibes' => ['bold', 'playful'],
            'label' => __('Paper + Space', 'pressroot'),
            'desc'  => __('Bold paper + purple/orange/lime with Outfit. The theme default.', 'pressroot'),
            'mods'  => [
                'prt_color_action' => '#6C4CF1', 'prt_color_paper' => '#FFF9F5',
                'prt_color_ink' => '#17151F', 'prt_color_body' => '#4A4660',
                'prt_font_heading' => 'Outfit', 'prt_font_body' => 'Outfit',
                'prt_btn_radius' => '999', 'prt_card_radius' => '20',
            ],
        ],
        'editorial' => [
            'mode'  => 'light', 'vibes' => ['minimal'],
            'label' => __('Editorial (green)', 'pressroot'),
            'desc'  => __('Clean paper + green — the previous theme default.', 'pressroot'),
            'mods'  => [
                'prt_color_action' => '#2f6b4e', 'prt_color_paper' => '#fbfaf7',
                'prt_color_ink' => '#17191e', 'prt_color_body' => '#2b2f36',
                'prt_font_heading' => 'Geist', 'prt_font_body' => 'Inter',
                'prt_btn_radius' => '8', 'prt_card_radius' => '16',
            ],
        ],
        'sage_classic' => [
            'mode'  => 'light', 'vibes' => ['warm', 'minimal'],
            'label' => __('Sage Classic', 'pressroot'),
            'desc'  => __('Khaki + serif — matches matthummel.com.', 'pressroot'),
            'mods'  => [
                'prt_color_action' => '#4e6b4a', 'prt_color_paper' => '#dccfa6',
                'prt_color_ink' => '#2a303b', 'prt_color_body' => '#3a3d44',
                'prt_font_heading' => 'Fraunces', 'prt_font_body' => 'Inter',
                'prt_btn_radius' => '4', 'prt_card_radius' => '14',
            ],
        ],
        'warm_sand' => [
            'mode'  => 'light', 'vibes' => ['warm'],
            'label' => __('Warm Sand', 'pressroot'),
            'desc'  => __('Terracotta + warm neutrals.', 'pressroot'),
            'mods'  => [
                'prt_color_action' => '#b4612f', 'prt_color_paper' => '#faf6ef',
                'prt_color_ink' => '#2a2422', 'prt_color_body' => '#44403c',
                'prt_font_heading' => 'Fraunces', 'prt_font_body' => 'Inter',
                'prt_btn_radius' => '12', 'prt_card_radius' => '18',
            ],
        ],
        'midnight' => [
            'mode'  => 'dark', 'vibes' => ['minimal', 'bold'],
            'label' => __('Midnight', 'pressroot'),
            'desc'  => __('Dark canvas, soft blue accent.', 'pressroot'),
            'mods'  => [
                'prt_color_action' => '#6ea8fe', 'prt_color_paper' => '#0f1420',
                'prt_color_ink' => '#f5f7fb', 'prt_color_body' => '#c7ccd6',
                'prt_font_heading' => 'Space Grotesk', 'prt_font_body' => 'Inter',
                'prt_btn_radius' => '8', 'prt_card_radius' => '16',
            ],
        ],
        'mono_slate' => [
            'mode'  => 'light', 'vibes' => ['minimal', 'bold'],
            'label' => __('Mono Slate', 'pressroot'),
            'desc'  => __('Sharp, near-black, minimal.', 'pressroot'),
            'mods'  => [
                'prt_color_action' => '#111827', 'prt_color_paper' => '#f7f7f8',
                'prt_color_ink' => '#0b0c0e', 'prt_color_body' => '#3a3d44',
                'prt_font_heading' => 'Inter Tight', 'prt_font_body' => 'Inter',
                'prt_btn_radius' => '4', 'prt_card_radius' => '8',
            ],
        ],
        'iris_dark' => [
            'mode'  => 'dark', 'vibes' => ['bold', 'playful'],
            'label' => __('Iris Dark', 'pressroot'),
            'desc'  => __('Repofolio hero colors as a full dark theme — deep space violet, light text.', 'pressroot'),
            'mods'  => [
                'prt_color_action' => '#9B5CF6', 'prt_color_paper' => '#15122a',
                'prt_color_ink' => '#F5F2FF', 'prt_color_body' => '#CFCBE6',
                'prt_font_heading' => 'Outfit', 'prt_font_body' => 'Outfit',
                'prt_btn_radius' => '999', 'prt_card_radius' => '18',
            ],
        ],
        'pink_pop' => [
            'mode'  => 'light', 'vibes' => ['playful', 'bold'],
            'label' => __('Pink Pop', 'pressroot'),
            'desc'  => __('Repofolio pink up front — energetic, bright, unmissable.', 'pressroot'),
            'mods'  => [
                'prt_color_action' => '#FF4D9D', 'prt_color_paper' => '#FFF7FA',
                'prt_color_ink' => '#1F1520', 'prt_color_body' => '#4E4152',
                'prt_font_heading' => 'Outfit', 'prt_font_body' => 'Outfit',
                'prt_btn_radius' => '999', 'prt_card_radius' => '24',
            ],
        ],
        'coral_cream' => [
            'mode'  => 'light', 'vibes' => ['warm', 'playful'],
            'label' => __('Coral Cream', 'pressroot'),
            'desc'  => __('Repofolio coral on soft cream — friendly and appetizing.', 'pressroot'),
            'mods'  => [
                'prt_color_action' => '#FF7A3D', 'prt_color_paper' => '#FFF6F0',
                'prt_color_ink' => '#241C18', 'prt_color_body' => '#4A4038',
                'prt_font_heading' => 'Outfit', 'prt_font_body' => 'Inter',
                'prt_btn_radius' => '999', 'prt_card_radius' => '22',
            ],
        ],
        'mint_fresh' => [
            'mode'  => 'light', 'vibes' => ['minimal', 'playful'],
            'label' => __('Mint Fresh', 'pressroot'),
            'desc'  => __('Repofolio lime, deepened for contrast — clean and current.', 'pressroot'),
            'mods'  => [
                'prt_color_action' => '#17B57A', 'prt_color_paper' => '#F4FCF8',
                'prt_color_ink' => '#12201A', 'prt_color_body' => '#3E4F47',
                'prt_font_heading' => 'Space Grotesk', 'prt_font_body' => 'Inter',
                'prt_btn_radius' => '12', 'prt_card_radius' => '16',
            ],
        ],
        'cyan_sky' => [
            'mode'  => 'light', 'vibes' => ['minimal', 'bold'],
            'label' => __('Cyan Sky', 'pressroot'),
            'desc'  => __('Repofolio cyan, deepened for buttons — crisp and technical.', 'pressroot'),
            'mods'  => [
                'prt_color_action' => '#0FA8C9', 'prt_color_paper' => '#F3FBFD',
                'prt_color_ink' => '#102026', 'prt_color_body' => '#3C4C52',
                'prt_font_heading' => 'Outfit', 'prt_font_body' => 'Inter',
                'prt_btn_radius' => '10', 'prt_card_radius' => '18',
            ],
        ],
        'core_marketing' => [
            'mode'  => 'light', 'vibes' => ['bold', 'playful'],
            'label' => __('Core Marketing', 'pressroot'),
            'desc'  => __('RESERVED: the theme\'s own conversion-focused marketing look — spectrum accents, pink-led CTAs. Only the Marketing/Landing site type deals this kit.', 'pressroot'),
            'mods'  => [
                'prt_color_action' => '#FF4D9D', 'prt_color_paper' => '#FFF9F5',
                'prt_color_ink' => '#17151F', 'prt_color_body' => '#4A4660',
                'prt_font_heading' => 'Outfit', 'prt_font_body' => 'Outfit',
                'prt_btn_radius' => '999', 'prt_card_radius' => '18',
            ],
        ],
        'amber_toast' => [
            'mode'  => 'light', 'vibes' => ['warm'],
            'label' => __('Amber Toast', 'pressroot'),
            'desc'  => __('Repofolio amber, toasted for contrast — cozy editorial warmth.', 'pressroot'),
            'mods'  => [
                'prt_color_action' => '#D98E00', 'prt_color_paper' => '#FFFBF2',
                'prt_color_ink' => '#201A10', 'prt_color_body' => '#4E4636',
                'prt_font_heading' => 'Fraunces', 'prt_font_body' => 'Inter',
                'prt_btn_radius' => '8', 'prt_card_radius' => '16',
            ],
        ],
    ]);
}

/**
 * Renders just the Export/Import/Reset controls — no page wrapper, no <h1>.
 * Embedded inside a collapsed "Advanced" `<details>` on the Site Types tab
 * (app/ai-assistant.php's prt_pressroot_ai_tab_html()), the same way AI
 * Connectors is. The manual Style Kits swatch grid that used to sit above
 * these controls (with its own "Apply" button per kit) was removed — see the
 * file docblock above for why.
 */
function prt_settings_backup_fields_html()
{
    if (! current_user_can('edit_theme_options')) {
        return;
    }
    $notice = isset($_GET['prt_done']) ? sanitize_key($_GET['prt_done']) : '';
    $post   = admin_url('admin-post.php');
    ?>
        <?php if ($notice === 'import') : ?>
            <div class="notice notice-success is-dismissible"><p><?php esc_html_e('Settings imported.', 'pressroot'); ?></p></div>
        <?php elseif ($notice === 'reset') : ?>
            <div class="notice notice-success is-dismissible"><p><?php esc_html_e('Theme settings reset to defaults.', 'pressroot'); ?></p></div>
        <?php elseif ($notice === 'importerr') : ?>
            <div class="notice notice-error is-dismissible"><p><?php esc_html_e('Could not import that file. Make sure it is a JSON export from this theme.', 'pressroot'); ?></p></div>
        <?php endif; ?>

        <h4 style="margin-top:0"><?php esc_html_e('Export settings', 'pressroot'); ?></h4>
        <p class="description"><?php esc_html_e('Download all theme settings as a JSON file you can re-import on another site.', 'pressroot'); ?></p>
        <form method="post" action="<?php echo esc_url($post); ?>" style="margin:12px 0 24px">
            <input type="hidden" name="action" value="prt_export_settings">
            <?php wp_nonce_field('prt_export_settings'); ?>
            <button class="button"><?php esc_html_e('Download export (.json)', 'pressroot'); ?></button>
        </form>

        <h4><?php esc_html_e('Import settings', 'pressroot'); ?></h4>
        <p class="description"><?php esc_html_e('Upload a JSON export to apply those settings here.', 'pressroot'); ?></p>
        <form method="post" enctype="multipart/form-data" action="<?php echo esc_url($post); ?>" style="margin:12px 0 24px">
            <input type="file" name="prt_import_file" accept="application/json,.json" required>
            <input type="hidden" name="action" value="prt_import_settings">
            <?php wp_nonce_field('prt_import_settings'); ?>
            <button class="button button-primary"><?php esc_html_e('Import file', 'pressroot'); ?></button>
        </form>

        <h4 style="color:#b32d2e"><?php esc_html_e('Reset', 'pressroot'); ?></h4>
        <p class="description"><?php esc_html_e('Remove all of this theme\'s settings and return to defaults. This cannot be undone, so export first.', 'pressroot'); ?></p>
        <form method="post" action="<?php echo esc_url($post); ?>" style="margin:12px 0" onsubmit="return confirm('<?php echo esc_js(__('Reset all theme settings to defaults?', 'pressroot')); ?>');">
            <input type="hidden" name="action" value="prt_reset_settings">
            <?php wp_nonce_field('prt_reset_settings'); ?>
            <button class="button" style="border-color:#b32d2e;color:#b32d2e"><?php esc_html_e('Reset theme settings', 'pressroot'); ?></button>
        </form>
    <?php
}

/**
 * Export all prt_* theme mods as JSON download — EXCEPT secrets. API keys
 * (prt_ai_key_*, anything *_key/*_token/*_secret) never leave the server:
 * the AI connectors' design promise is "keys stay server-side", and a
 * plaintext key inside a downloadable backup breaks that promise the moment
 * the file lands in Dropbox or an email attachment.
 */
add_action('admin_post_prt_export_settings', function () {
    prt_require_admin_post('prt_export_settings');
    $mods = prt_owned_mods();
    foreach (array_keys($mods) as $k) {
        if (preg_match('/(^prt_ai_key_|_key$|_token$|_secret$)/', (string) $k)) {
            unset($mods[$k]);
        }
    }
    $payload = [
        'theme'   => 'pressroot',
        'version' => wp_get_theme()->get('Version'),
        'date'    => gmdate('c'),
        'mods'    => $mods,
    ];
    nocache_headers();
    header('Content-Type: application/json; charset=utf-8');
    header('Content-Disposition: attachment; filename=pressroot-settings-' . gmdate('Ymd-His') . '.json');
    echo wp_json_encode($payload, JSON_PRETTY_PRINT);
    exit;
});

/** Import settings from an uploaded JSON file. */
add_action('admin_post_prt_import_settings', function () {
    prt_require_admin_post('prt_import_settings');
    $ok = false;
    if (! empty($_FILES['prt_import_file']['tmp_name']) && is_uploaded_file($_FILES['prt_import_file']['tmp_name'])) {
        $raw  = file_get_contents($_FILES['prt_import_file']['tmp_name']);
        $data = json_decode($raw, true);
        if (is_array($data) && isset($data['mods']) && is_array($data['mods'])) {
            // The code-injection mods (prt_code_head/body/footer) are echoed
            // RAW on the front end; the Customizer gates them through
            // prt_sanitize_code() (kses for users without unfiltered_html).
            // Import must apply the same gate, or an uploaded JSON becomes an
            // unfiltered-HTML bypass (e.g. multisite admins lack that cap).
            $codeMods = ['prt_code_head', 'prt_code_body', 'prt_code_footer'];
            foreach ($data['mods'] as $k => $v) {
                if (is_string($k) && strpos($k, 'prt_') === 0 && (is_scalar($v) || is_array($v))) {
                    if (in_array($k, $codeMods, true) && function_exists('App\\prt_sanitize_code')) {
                        $v = prt_sanitize_code((string) $v);
                    }
                    set_theme_mod($k, $v);
                }
            }
            $ok = true;
        }
    }
    wp_safe_redirect(prt_settings_tab_url('ai', ['prt_done' => $ok ? 'import' : 'importerr']) . '#prt-settings-advanced');
    exit;
});

/** Reset: remove all prt_* theme mods. */
add_action('admin_post_prt_reset_settings', function () {
    prt_require_admin_post('prt_reset_settings');
    foreach (array_keys(prt_owned_mods()) as $k) {
        remove_theme_mod($k);
    }
    wp_safe_redirect(prt_settings_tab_url('ai', ['prt_done' => 'reset']) . '#prt-settings-advanced');
    exit;
});
