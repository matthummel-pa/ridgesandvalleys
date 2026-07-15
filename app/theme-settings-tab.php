<?php

/**
 * Theme Settings — the first tab on Appearance -> Pressroot.
 *
 * The Customizer stays the ENGINE (it's where WordPress persists theme_mods
 * and where live preview lives), but business owners shouldn't need to learn
 * it: this tab is the new front door. It edits the exact same theme_mods
 * with plain native fields, grouped the way an owner thinks — Identity,
 * Design, Typography & Layout, Features, Addons — and deep-links into the
 * Customizer only for the long tail of advanced controls (header/nav/footer
 * builders, per-breakpoint responsive tweaks) where live preview genuinely
 * helps. Saving here = saving in the Customizer; the two never fight.
 *
 * Design philosophy (the money-saver): no page builder needed. Global
 * settings + the Site Types generator + AI-write cover the whole "make my
 * site look right" job — page-level fiddling stays optional in Gutenberg.
 */

namespace App;

/** The known Customizer sections worth deep-linking for advanced work. */
function prt_customizer_deep_links(): array
{
    return [
        ['prt_hero_section',         __('Hero layout (columns, images, background, animation)', 'pressroot')],
        ['prt_headerlayout_section', __('Header layout & sticky behaviors', 'pressroot')],
        ['prt_nav_section',          __('Navigation & popout menu', 'pressroot')],
        ['prt_footer_section',       __('Footer builder', 'pressroot')],
        ['prt_content_section',      __('Content & CTA text overrides', 'pressroot')],
        ['prt_anim_section',         __('Scroll animations', 'pressroot')],
        ['prt_addons_section',       __('Theme Addons', 'pressroot')],
    ];
}

/** Save handler — writes the same theme_mods the Customizer writes. */
add_action('admin_post_prt_save_theme_settings', function () {
    if (! current_user_can('edit_theme_options') || ! check_admin_referer('prt_save_theme_settings')) {
        wp_die(__('Not allowed.', 'pressroot'));
    }

    // Identity — CONSOLIDATED with the brand profile: the site title IS the
    // business name and the tagline IS the one-line description. One field
    // each, written to both stores, so nothing can drift apart.
    if (isset($_POST['blogname'])) {
        $name = sanitize_text_field(wp_unslash($_POST['blogname']));
        update_option('blogname', $name);
        set_theme_mod('prt_brand_name', $name);
    }
    if (isset($_POST['blogdescription'])) {
        $desc = sanitize_text_field(wp_unslash($_POST['blogdescription']));
        update_option('blogdescription', $desc);
        set_theme_mod('prt_brand_desc', $desc);
    }

    // Brand questionnaire (moved here from the former Brand tab).
    if (isset($_POST['prt_brand_mode'])) {
        $bmode = sanitize_key($_POST['prt_brand_mode']);
        set_theme_mod('prt_brand_mode', in_array($bmode, ['light', 'dark', 'either'], true) ? $bmode : 'either');
    }
    if (isset($_POST['prt_brand_vibe'])) {
        $vibe = sanitize_key($_POST['prt_brand_vibe']);
        set_theme_mod('prt_brand_vibe', in_array($vibe, ['bold', 'minimal', 'warm', 'playful', 'any'], true) ? $vibe : 'any');
    }
    if (isset($_POST['prt_brand_audience'])) {
        set_theme_mod('prt_brand_audience', sanitize_text_field(wp_unslash($_POST['prt_brand_audience'])));
    }
    if (isset($_POST['prt_brand_industry_sel'])) {
        $industrySel = sanitize_text_field(wp_unslash($_POST['prt_brand_industry_sel']));
        set_theme_mod('prt_brand_industry', $industrySel === '__other'
            ? sanitize_text_field(wp_unslash($_POST['prt_brand_industry_other'] ?? ''))
            : $industrySel);
    }
    if (isset($_POST['prt_brand_tone'])) {
        set_theme_mod('prt_brand_tone', sanitize_text_field(wp_unslash($_POST['prt_brand_tone'])));
    }
    if (isset($_POST['prt_brand_goal'])) {
        $goal = sanitize_key($_POST['prt_brand_goal']);
        set_theme_mod('prt_brand_goal', in_array($goal, ['leads', 'sell', 'book', 'read'], true) ? $goal : 'leads');
    }
    if (isset($_POST['prt_brand_imagery'])) {
        $imagery = sanitize_key($_POST['prt_brand_imagery']);
        set_theme_mod('prt_brand_imagery', in_array($imagery, ['photo', 'illustration', 'abstract', 'none'], true) ? $imagery : 'photo');
    }
    if (isset($_POST['prt_brand_density'])) {
        $density = sanitize_key($_POST['prt_brand_density']);
        set_theme_mod('prt_brand_density', in_array($density, ['minimal', 'balanced', 'rich'], true) ? $density : 'balanced');
    }
    if (isset($_POST['prt_brand_trend'])) {
        $trend = sanitize_key($_POST['prt_brand_trend']);
        $trends = function_exists('App\prt_design_trends') ? prt_design_trends() : [];
        set_theme_mod('prt_brand_trend', ($trend === 'any' || isset($trends[$trend])) ? $trend : 'any');
    }
    set_theme_mod('prt_ai_features_on', ! empty($_POST['prt_ai_features_on']));
    if (isset($_POST['prt_ai_custom_instructions'])) {
        // WYSIWYG field: keep safe formatting for editing comfort, but hard-cap
        // at 1,000 words (the compiled brief must stay prompt-sized). Over-limit
        // input is truncated at the cap rather than rejected.
        $instr = wp_kses_post(wp_unslash($_POST['prt_ai_custom_instructions']));
        if (function_exists('App\\prt_word_count') && function_exists('App\\prt_limit_words') && prt_word_count($instr) > 1000) {
            $instr = prt_limit_words($instr, 1000);
        }
        set_theme_mod('prt_ai_custom_instructions', $instr);
    }

    // 📄 Instruction files (.md) — stored server-side in one option and
    // appended to the core brief, so every FUTURE build and AI call keeps
    // using them without re-uploading.
    $docs = get_option('prt_ai_instruction_docs', []);
    if (! is_array($docs)) {
        $docs = [];
    }
    foreach ((array) ($_POST['prt_ai_doc_remove'] ?? []) as $rm) {
        unset($docs[sanitize_file_name(wp_unslash((string) $rm))]);
    }
    if (! empty($_FILES['prt_ai_docs']['name'][0])) {
        foreach ((array) $_FILES['prt_ai_docs']['name'] as $i => $fname) {
            $tmp = (string) ($_FILES['prt_ai_docs']['tmp_name'][$i] ?? '');
            $err = (int) ($_FILES['prt_ai_docs']['error'][$i] ?? UPLOAD_ERR_NO_FILE);
            $ext = strtolower(pathinfo((string) $fname, PATHINFO_EXTENSION));
            if ($err !== UPLOAD_ERR_OK || ! is_uploaded_file($tmp)
                || ! in_array($ext, ['md', 'markdown', 'txt'], true)
                || (int) ($_FILES['prt_ai_docs']['size'][$i] ?? 0) > 262144) { // 256 KB cap
                continue;
            }
            $content = str_replace(chr(0), '', wp_check_invalid_utf8((string) file_get_contents($tmp), true));
            if (trim($content) === '') {
                continue;
            }
            $docs[sanitize_file_name((string) $fname)] = [
                'content' => $content,
                'words'   => function_exists('App\\prt_word_count') ? prt_word_count($content) : str_word_count(wp_strip_all_tags($content)),
                'added'   => current_time('mysql'),
            ];
        }
    }
    update_option('prt_ai_instruction_docs', $docs, false);
    set_theme_mod('prt_autobuild_on_save', ! empty($_POST['prt_autobuild_on_save']));

    // Design colors (same mods the Customizer + Style Kits write).
    // CONSOLIDATED: "Brand / buttons" is also the brand color the design
    // generator protects across every kit deal — one picker, both stores.
    foreach (['prt_color_action', 'prt_color_paper', 'prt_color_ink', 'prt_color_body'] as $mod) {
        $v = sanitize_hex_color(wp_unslash($_POST[$mod] ?? ''));
        if ($v) {
            set_theme_mod($mod, $v);
            if ($mod === 'prt_color_action') {
                set_theme_mod('prt_brand_color', $v);
            }
        }
    }

    // Optional one-click kit apply (overrides the four colors above).
    $kit = sanitize_key($_POST['prt_apply_kit'] ?? '');
    if ($kit !== '' && function_exists('App\\prt_apply_style_kit')) {
        prt_apply_style_kit($kit);
        update_option('prt_active_site_kit', $kit);
    }

    // Typography + layout.
    $fonts = function_exists('App\\prt_fonts') ? prt_fonts() : [];
    foreach (['prt_font_heading', 'prt_font_body'] as $mod) {
        $v = sanitize_text_field(wp_unslash($_POST[$mod] ?? ''));
        if ($v !== '' && isset($fonts[$v])) {
            set_theme_mod($mod, $v);
        }
    }
    if (isset($_POST['prt_container'])) {
        set_theme_mod('prt_container', max(720, min(1920, absint($_POST['prt_container']))));
    }
    foreach (['prt_btn_radius', 'prt_card_radius'] as $mod) {
        if (isset($_POST[$mod])) {
            set_theme_mod($mod, (string) max(0, min(999, absint($_POST[$mod]))));
        }
    }

    // Hero copy (the homepage's own hero).
    foreach (['prt_hero_title', 'prt_hero_accent', 'prt_hero_serif', 'prt_hero_suffix', 'prt_hero_btn1_text', 'prt_hero_btn2_text', 'prt_hero_chip1', 'prt_hero_chip2', 'prt_avail_text'] as $mod) {
        if (isset($_POST[$mod])) {
            set_theme_mod($mod, sanitize_text_field(wp_unslash($_POST[$mod])));
        }
    }
    if (isset($_POST['prt_hero_subtext'])) {
        set_theme_mod('prt_hero_subtext', sanitize_textarea_field(wp_unslash($_POST['prt_hero_subtext'])));
    }
    if (isset($_POST['prt_avail_text'])) { // hero section was on the form (post-build fine-tuning)
        set_theme_mod('prt_avail_open', ! empty($_POST['prt_avail_open']));
    }

    // Features.
    set_theme_mod('prt_dark_enable', ! empty($_POST['prt_dark_enable']));
    $darkDefault = sanitize_key($_POST['prt_dark_default'] ?? 'light');
    set_theme_mod('prt_dark_default', in_array($darkDefault, ['light', 'dark', 'auto'], true) ? $darkDefault : 'light');
    set_theme_mod('prt_scroll_enable', ! empty($_POST['prt_scroll_enable']));
    set_theme_mod('prt_split_block_css', ! empty($_POST['prt_split_block_css']));
    set_theme_mod('prt_core_blocks_only', ! empty($_POST['prt_core_blocks_only']));

    // Addons.
    set_theme_mod('prt_addon_pressroot_ai_enabled', ! empty($_POST['prt_addon_pressroot_ai_enabled']));
    set_theme_mod('prt_addon_repofolio_enabled', ! empty($_POST['prt_addon_repofolio_enabled']));

    if (function_exists('App\\prt_flush_design_caches')) {
        prt_flush_design_caches();
    }

    // ── GENERATE, separately, after the branding save ────────────────
    // 1. Rebuild the core AI instructions (the site's system prompt).
    if (function_exists('App\\prt_rebuild_core_instructions')) {
        prt_rebuild_core_instructions();
    }
    // 2. Re-deal the DESIGN from the fresh brand answers (kit + trend for
    //    every applied site type) so saving branding visibly updates the
    //    site's design — opt-out via the auto-build toggle.
    if (get_theme_mod('prt_autobuild_on_save', true)
        && function_exists('App\\prt_apply_random_site_kit')
        && function_exists('App\\prt_get_site_type_pages')
        && function_exists('App\\prt_site_types')) {
        $appliedTypes = array_unique(array_filter(wp_list_pluck(prt_get_site_type_pages(), 'prt_site_type')));
        $allTypes     = prt_site_types();
        foreach ($appliedTypes as $tid) {
            if (isset($allTypes[$tid])) {
                prt_apply_random_site_kit((string) $tid, $allTypes[$tid]);
                break; // one site-wide deal is enough
            }
        }
        if (function_exists('App\\prt_refresh_branding')) {
            prt_refresh_branding();
        }
        // Chrome follows the fresh answers too: header CTA per goal,
        // footer per light/dark + description, generated menu synced.
        if (function_exists('App\\prt_build_site_chrome')) {
            prt_build_site_chrome();
        }
    }
    // 3. Auto-generate the smart-block copy cache from the fresh answers
    //    (one fast server-side call; blocks read it at render time).
    if (function_exists('App\\prt_prime_smart_copy')) {
        prt_prime_smart_copy();
    }

    // First save unlocks page previews on the Site Types tab — previews
    // always reflect the owner's settings, never stock theme branding.
    update_option('prt_setup_saved', 1);

    wp_safe_redirect(prt_settings_tab_url('settings', ['prt_settings_saved' => '1']));
    exit;
});

/**
 * The building status bar — spectrum progress with labeled steps, shown
 * after saving Theme Settings and after applying/refreshing a site type.
 * The steps do real work where they can: the "refreshing previews" step
 * re-fetches every .prt-preview-frame with a cache-buster.
 */
function prt_build_status_bar(array $steps, string $doneLabel, string $doneUrl): void
{
    $id = 'prt-build-' . wp_rand(100, 999);
    ?>
    <div id="<?php echo esc_attr($id); ?>" class="prt-rf-card" style="max-width:720px;padding:16px 22px;margin:12px 0 18px">
        <div style="display:flex;justify-content:space-between;align-items:baseline;gap:12px">
            <strong data-label>🏗 <?php esc_html_e('Building your site…', 'pressroot'); ?></strong>
            <a data-done href="<?php echo esc_url($doneUrl); ?>" style="display:none;font-weight:600"><?php echo esc_html($doneLabel); ?> →</a>
        </div>
        <div style="height:8px;border-radius:999px;background:#ece6fb;overflow:hidden;margin-top:10px">
            <div data-bar style="height:100%;width:4%;border-radius:999px;background:var(--gradient-spectrum,linear-gradient(90deg,#6C4CF1,#FF4D9D,#FF7A3D,#FFC53D,#37E29A,#22CFEE));transition:width .5s ease"></div>
        </div>
        <script>
        (function () {
            var box = document.getElementById('<?php echo esc_js($id); ?>');
            if (! box) { return; }
            var steps = <?php echo wp_json_encode(array_values($steps)); ?>;
            var bar = box.querySelector('[data-bar]');
            var label = box.querySelector('[data-label]');
            var i = 0;
            function tick() {
                if (i < steps.length) {
                    label.textContent = '🏗 ' + steps[i];
                    bar.style.width = Math.round(((i + 1) / (steps.length + 1)) * 100) + '%';
                    if (i === steps.length - 1) {
                        document.querySelectorAll('.prt-preview-frame').forEach(function (f) {
                            try { var u = new URL(f.src); u.searchParams.set('v', Date.now().toString()); f.src = u.toString(); } catch (e) {}
                        });
                    }
                    i++;
                    setTimeout(tick, 700);
                } else {
                    bar.style.width = '100%';
                    label.textContent = '✅ <?php echo esc_js(__('Built — designed as well as your setup and model allow. Keep refreshing until you love it.', 'pressroot')); ?>';
                    box.querySelector('[data-done]').style.display = '';
                }
            }
            setTimeout(tick, 300);
        })();
        </script>
    </div>
    <?php
}

/** The tab. */
function prt_theme_settings_tab_html(): void
{
    if (! current_user_can('edit_theme_options')) {
        return;
    }
    if (isset($_GET['prt_settings_saved'])) {
        echo '<div class="notice notice-success is-dismissible"><p>' . esc_html__('Theme settings saved — the site is already wearing them (no caches, no build step).', 'pressroot') . '</p></div>';
        prt_build_status_bar([
            __('Saving your settings', 'pressroot'),
            __('Compiling your core AI brief', 'pressroot'),
            __('Dealing a design from your brand answers', 'pressroot'),
            __('Applying design tokens site-wide', 'pressroot'),
            __('Building navigation, header & footer', 'pressroot'),
            __('Generating smart-block copy', 'pressroot'),
            __('Refreshing previews', 'pressroot'),
        ], __('Open Site Types', 'pressroot'), prt_settings_tab_url('ai'));
    }
    $fonts = function_exists('App\\prt_fonts') ? prt_fonts() : [];
    $kits  = function_exists('App\\prt_style_kits') ? prt_style_kits() : [];
    $mod   = fn ($k, $d = '') => get_theme_mod($k, $d);
    // Design stays GENERATED until the first branding save: the auto-build
    // writes kit/colors/fonts/radii in the backend, so hand controls would
    // only invite fighting the generator. They unlock as fine-tuning after
    // the first build completes.
    $setupDone = (bool) get_option('prt_setup_saved');
    ?>
    <h2 style="margin-top:0"><?php esc_html_e('Theme Settings', 'pressroot'); ?></h2>
    <p class="description" style="max-width:680px"><?php esc_html_e('Everything an owner actually changes, in one place — no Customizer required, no page builder to buy. These save the same settings the Customizer uses, so nothing conflicts. The full advanced controls stay one click away below.', 'pressroot'); ?></p>

    <div id="prt-saving-bar" style="display:none;position:sticky;top:32px;z-index:99;max-width:720px;background:#fff;border:1.5px solid #ece6fb;border-radius:12px;padding:12px 18px;margin-bottom:12px">
        <strong>🏗 <?php esc_html_e('Saving & generating your site…', 'pressroot'); ?></strong>
        <div style="height:8px;border-radius:999px;background:#ece6fb;overflow:hidden;margin-top:8px">
            <div id="prt-saving-bar-fill" style="height:100%;width:6%;border-radius:999px;background:var(--gradient-spectrum,linear-gradient(90deg,#6C4CF1,#FF4D9D,#FF7A3D,#FFC53D,#37E29A,#22CFEE));transition:width .8s ease"></div>
        </div>
        <p class="description" style="margin:6px 0 0"><?php esc_html_e('Writing settings → compiling your core AI brief → dealing a design → generating smart copy…', 'pressroot'); ?></p>
    </div>
    <form method="post" action="<?php echo esc_url(admin_url('admin-post.php')); ?>" enctype="multipart/form-data" style="max-width:720px" onsubmit="(function(){var b=document.getElementById('prt-saving-bar');if(b){b.style.display='block';var f=document.getElementById('prt-saving-bar-fill'),w=6;setInterval(function(){w=Math.min(w+9,92);f.style.width=w+'%';},700);}})();">
        <input type="hidden" name="action" value="prt_save_theme_settings">
        <?php wp_nonce_field('prt_save_theme_settings'); ?>

        <h3><?php esc_html_e('Identity', 'pressroot'); ?></h3>
        <table class="form-table" role="presentation">
            <tr>
                <th scope="row"><label for="blogname"><?php esc_html_e('Business / site name', 'pressroot'); ?></label></th>
                <td>
                    <input type="text" id="blogname" name="blogname" class="regular-text" value="<?php echo esc_attr(get_option('blogname')); ?>">
                    <p class="description"><?php esc_html_e('One field, two jobs: your site title AND the business name the AI writes about.', 'pressroot'); ?></p>
                </td>
            </tr>
            <tr>
                <th scope="row"><label for="blogdescription"><?php esc_html_e('What you do, in one line', 'pressroot'); ?></label></th>
                <td>
                    <input type="text" id="blogdescription" name="blogdescription" class="large-text" value="<?php echo esc_attr(get_option('blogdescription')); ?>" placeholder="<?php esc_attr_e('who you help and how — doubles as the site tagline', 'pressroot'); ?>">
                    <p class="description"><?php esc_html_e('Doubles as the tagline and pre-fills the AI copy generator.', 'pressroot'); ?></p>
                </td>
            </tr>
        </table>

        <h3><?php esc_html_e('Brand', 'pressroot'); ?></h3>
        <p class="description" style="max-width:640px"><?php esc_html_e('These answers steer the design generator (which kits and trends get dealt) and give the AI its voice. No wrong answers — refresh until it feels right.', 'pressroot'); ?></p>
        <?php $b = function_exists('App\prt_brand_profile') ? prt_brand_profile() : []; ?>
        <table class="form-table" role="presentation">
            <tr>
                <th scope="row"><?php esc_html_e('Light or dark?', 'pressroot'); ?></th>
                <td>
                    <?php foreach (['light' => __('Light', 'pressroot'), 'dark' => __('Dark', 'pressroot'), 'either' => __('Surprise me', 'pressroot')] as $val => $label) : ?>
                        <label style="margin-right:16px"><input type="radio" name="prt_brand_mode" value="<?php echo esc_attr($val); ?>" <?php checked($b['mode'] ?? 'either', $val); ?>> <?php echo esc_html($label); ?></label>
                    <?php endforeach; ?>
                </td>
            </tr>
            <tr>
                <th scope="row"><?php esc_html_e('Personality', 'pressroot'); ?></th>
                <td>
                    <select name="prt_brand_vibe">
                        <?php foreach (['any' => __('Anything goes', 'pressroot'), 'bold' => __('Bold & confident', 'pressroot'), 'minimal' => __('Minimal & sharp', 'pressroot'), 'warm' => __('Warm & inviting', 'pressroot'), 'playful' => __('Playful & bright', 'pressroot')] as $val => $label) : ?>
                            <option value="<?php echo esc_attr($val); ?>" <?php selected($b['vibe'] ?? 'any', $val); ?>><?php echo esc_html($label); ?></option>
                        <?php endforeach; ?>
                    </select>
                </td>
            </tr>
            <tr>
                <th scope="row"><label for="prt_brand_industry_sel"><?php esc_html_e('Industry', 'pressroot'); ?></label></th>
                <td>
                    <?php
                    // Shared list (same `pressroot/brand_industries` filter) —
                    // lives in app/setup-wizard.php so the wizard's step 1 and
                    // this tab can never drift apart.
                    $industries = prt_brand_industries();
                    $currentIndustry = (string) ($b['industry'] ?? '');
                    $isListed = in_array($currentIndustry, $industries, true);
                    ?>
                    <select id="prt_brand_industry_sel" name="prt_brand_industry_sel" onchange="document.getElementById('prt_brand_industry_other').style.display = this.value === '__other' ? '' : 'none'">
                        <option value=""><?php esc_html_e('— choose an industry —', 'pressroot'); ?></option>
                        <?php foreach ($industries as $ind) : ?>
                            <option value="<?php echo esc_attr($ind); ?>" <?php selected($currentIndustry, $ind); ?>><?php echo esc_html($ind); ?></option>
                        <?php endforeach; ?>
                        <option value="__other" <?php selected(! $isListed && $currentIndustry !== ''); ?>><?php esc_html_e('Other…', 'pressroot'); ?></option>
                    </select>
                    <input type="text" id="prt_brand_industry_other" name="prt_brand_industry_other" class="regular-text" style="margin-left:8px;<?php echo ($isListed || $currentIndustry === '') ? 'display:none' : ''; ?>" value="<?php echo esc_attr($isListed ? '' : $currentIndustry); ?>" placeholder="<?php esc_attr_e('describe your industry', 'pressroot'); ?>">
                </td>
            </tr>
            <tr>
                <th scope="row"><label for="prt_brand_audience"><?php esc_html_e('Who is it for?', 'pressroot'); ?></label></th>
                <td><input type="text" id="prt_brand_audience" name="prt_brand_audience" class="large-text" value="<?php echo esc_attr($b['audience'] ?? ''); ?>" placeholder="<?php esc_attr_e('e.g. busy parents in Adams County who need a reliable plumber', 'pressroot'); ?>"></td>
            </tr>
            <tr>
                <th scope="row"><label for="prt_brand_tone"><?php esc_html_e('Three words for your voice', 'pressroot'); ?></label></th>
                <td><input type="text" id="prt_brand_tone" name="prt_brand_tone" class="regular-text" value="<?php echo esc_attr($b['tone'] ?? ''); ?>" placeholder="<?php esc_attr_e('e.g. friendly, expert, no-nonsense', 'pressroot'); ?>"></td>
            </tr>
            <tr>
                <th scope="row"><?php esc_html_e('Main goal · imagery · density', 'pressroot'); ?></th>
                <td style="display:flex;gap:10px;flex-wrap:wrap">
                    <select name="prt_brand_goal">
                        <?php foreach (['leads' => __('Get leads / inquiries', 'pressroot'), 'sell' => __('Sell products', 'pressroot'), 'book' => __('Book appointments', 'pressroot'), 'read' => __('Grow an audience', 'pressroot')] as $val => $label) : ?>
                            <option value="<?php echo esc_attr($val); ?>" <?php selected($b['goal'] ?? 'leads', $val); ?>><?php echo esc_html($label); ?></option>
                        <?php endforeach; ?>
                    </select>
                    <select name="prt_brand_imagery">
                        <?php foreach (['photo' => __('Photography', 'pressroot'), 'illustration' => __('Illustration', 'pressroot'), 'abstract' => __('Abstract gradients', 'pressroot'), 'none' => __('Typography only', 'pressroot')] as $val => $label) : ?>
                            <option value="<?php echo esc_attr($val); ?>" <?php selected($b['imagery'] ?? 'photo', $val); ?>><?php echo esc_html($label); ?></option>
                        <?php endforeach; ?>
                    </select>
                    <select name="prt_brand_density">
                        <?php foreach (['minimal' => __('Short & punchy copy', 'pressroot'), 'balanced' => __('Balanced copy', 'pressroot'), 'rich' => __('Rich, story-driven copy', 'pressroot')] as $val => $label) : ?>
                            <option value="<?php echo esc_attr($val); ?>" <?php selected($b['density'] ?? 'balanced', $val); ?>><?php echo esc_html($label); ?></option>
                        <?php endforeach; ?>
                    </select>
                </td>
            </tr>
            <tr>
                <th scope="row"><?php esc_html_e('Design trend', 'pressroot'); ?></th>
                <td>
                    <select name="prt_brand_trend">
                        <option value="any" <?php selected($b['trend'] ?? 'any', 'any'); ?>><?php esc_html_e('🎲 Surprise me — rotate trends on refresh', 'pressroot'); ?></option>
                        <?php if (function_exists('App\prt_design_trends')) : foreach (prt_design_trends() as $slug => $t) : ?>
                            <option value="<?php echo esc_attr($slug); ?>" <?php selected($b['trend'] ?? 'any', $slug); ?>><?php echo esc_html($t['label'] . ' — ' . $t['desc']); ?></option>
                        <?php endforeach; endif; ?>
                    </select>
                </td>
            </tr>
            <tr>
                <th scope="row"><label for="prt_ai_custom_instructions"><?php esc_html_e('AI instructions', 'pressroot'); ?></label></th>
                <td>
                    <?php
                    wp_editor(get_theme_mod('prt_ai_custom_instructions', ''), 'prt_ai_custom_instructions', [
                        'textarea_name' => 'prt_ai_custom_instructions',
                        'textarea_rows' => 6,
                        'media_buttons' => false,
                        'teeny'         => true,
                        'quicktags'     => true,
                        'editor_height' => 160,
                    ]);
                    ?>
                    <p class="description"><span id="prt-instr-count" style="font-weight:600"></span> · <?php esc_html_e('Limit: 1,000 words — anything longer is trimmed on save. Formatting helps YOU organize; the AI receives it flattened to plain text.', 'pressroot'); ?></p>
                    <script>
                    (function () {
                        var out = document.getElementById('prt-instr-count');
                        if (! out) { return; }
                        function count() {
                            var txt = '';
                            if (window.tinymce && tinymce.get('prt_ai_custom_instructions') && ! tinymce.get('prt_ai_custom_instructions').isHidden()) {
                                txt = tinymce.get('prt_ai_custom_instructions').getContent({ format: 'text' });
                            } else {
                                var ta = document.getElementById('prt_ai_custom_instructions');
                                txt = ta ? ta.value.replace(/<[^>]*>/g, ' ') : '';
                            }
                            var words = (txt.trim().match(/\S+/g) || []).length;
                            out.textContent = words + ' / 1000 <?php echo esc_js(__('words', 'pressroot')); ?>';
                            out.style.color = words > 1000 ? '#d63638' : '#37a56b';
                        }
                        setInterval(count, 1200);
                        count();
                    })();
                    </script>
                    <p class="description"><?php esc_html_e('Theme Settings + Brand are the core prompt for your entire site. Everything on this page compiles into one saved brief that is sent with EVERY AI call — this field is appended as your highest-priority instructions.', 'pressroot'); ?></p>

                    <div style="margin-top:14px;padding-top:12px;border-top:1px solid #ece6fb">
                        <strong style="font-size:13px">📄 <?php esc_html_e('Instruction files (.md)', 'pressroot'); ?></strong>
                        <p class="description" style="margin:4px 0 8px"><?php esc_html_e('Upload Markdown docs — build recipes, brand guides, menus, house rules. Stored with the site and appended to the core brief, so every future build and AI call keeps using them. Max 256 KB each; long docs are trimmed to ~1,200 words in the brief.', 'pressroot'); ?></p>
                        <?php $prtDocs = get_option('prt_ai_instruction_docs', []); ?>
                        <?php if (is_array($prtDocs) && $prtDocs) : ?>
                            <ul style="margin:0 0 8px">
                                <?php foreach ($prtDocs as $dname => $doc) : ?>
                                    <li style="margin-bottom:4px">
                                        <code><?php echo esc_html($dname); ?></code>
                                        <span class="description">— <?php echo esc_html(number_format_i18n((int) ($doc['words'] ?? 0))); ?> <?php esc_html_e('words', 'pressroot'); ?></span>
                                        <label style="margin-left:10px;color:#d63638;font-size:12px"><input type="checkbox" name="prt_ai_doc_remove[]" value="<?php echo esc_attr($dname); ?>"> <?php esc_html_e('remove on save', 'pressroot'); ?></label>
                                    </li>
                                <?php endforeach; ?>
                            </ul>
                        <?php endif; ?>
                        <input type="file" name="prt_ai_docs[]" accept=".md,.markdown,.txt" multiple>
                    </div>
                    <?php if (function_exists('App\prt_get_core_instructions')) : ?>
                        <details style="margin-top:8px"><summary style="cursor:pointer;font-size:12px;color:#646970"><?php esc_html_e('View the compiled core brief the AI receives', 'pressroot'); ?></summary>
                            <pre style="white-space:pre-wrap;font-size:11px;background:#f6f4fd;border:1px solid #ece6fb;border-radius:8px;padding:10px;margin-top:6px"><?php echo esc_html(prt_get_core_instructions()); ?></pre>
                        </details>
                    <?php endif; ?>
                </td>
            </tr>
            <tr>
                <th scope="row"><?php esc_html_e('Auto-build on save', 'pressroot'); ?></th>
                <td>
                    <label><input type="checkbox" name="prt_autobuild_on_save" value="1" <?php checked((bool) get_theme_mod('prt_autobuild_on_save', true)); ?>> <?php esc_html_e('After saving, regenerate the design (kit + trend) and smart-block copy from these answers', 'pressroot'); ?></label>
                </td>
            </tr>
            <tr>
                <th scope="row"><?php esc_html_e('Powered by AI — or not', 'pressroot'); ?></th>
                <td>
                    <label style="font-weight:600"><input type="checkbox" name="prt_ai_features_on" value="1" <?php checked((bool) get_theme_mod('prt_ai_features_on', true)); ?>> <?php esc_html_e('✨ Let AI help with writing and images', 'pressroot'); ?></label>
                    <p class="description"><?php esc_html_e('Off: nothing on this site ever calls an AI service. The design generator keeps working either way.', 'pressroot'); ?></p>
                </td>
            </tr>
        </table>

        <h3><?php esc_html_e('Design', 'pressroot'); ?></h3>
        <div class="prt-rf-card" style="padding:18px 22px;margin:4px 0 18px;background:#f6f4fd">
            <strong>✨ <?php esc_html_e('Design is generated for you', 'pressroot'); ?></strong>
            <p class="description" style="margin:6px 0 0;max-width:600px">
                <?php echo $setupDone
                    ? esc_html__('Your brand answers above drive it: every save writes the design settings in the backend — kit, colors, fonts, corners, hero copy — and the status bar shows the build. Fine-tune manually below only if you must.', 'pressroot')
                    : esc_html__('Fill in Identity + Brand above (including AI instructions) and hit Save. The build writes every design setting in the backend — kit, colors, fonts, corners, hero copy — from your answers, with a status bar while it runs. Manual fine-tuning unlocks here after the first build.', 'pressroot'); ?>
            </p>
        </div>
        <?php if ($setupDone) : ?>
        <details style="margin:0 0 18px">
        <summary style="cursor:pointer;font-weight:600;padding:6px 0"><?php esc_html_e('Advanced design fine-tuning (optional — the generator manages these)', 'pressroot'); ?></summary>
        <table class="form-table" role="presentation">
            <tr>
                <th scope="row"><?php esc_html_e('Apply a design kit', 'pressroot'); ?></th>
                <td>
                    <select name="prt_apply_kit">
                        <option value=""><?php esc_html_e('— keep current colors —', 'pressroot'); ?></option>
                        <?php foreach ($kits as $slug => $kit) : ?>
                            <option value="<?php echo esc_attr($slug); ?>" <?php selected(get_option('prt_active_site_kit'), $slug); ?>><?php echo esc_html($kit['label']); ?></option>
                        <?php endforeach; ?>
                    </select>
                    <p class="description"><?php esc_html_e('One-click palette + fonts + radii. The colors below fine-tune afterwards.', 'pressroot'); ?></p>
                </td>
            </tr>
            <tr>
                <th scope="row"><?php esc_html_e('Colors', 'pressroot'); ?></th>
                <td style="display:flex;gap:18px;flex-wrap:wrap">
                    <?php foreach ([
                        'prt_color_action' => __('Brand color (buttons + generator)', 'pressroot'),
                        'prt_color_paper'  => __('Background', 'pressroot'),
                        'prt_color_ink'    => __('Headings', 'pressroot'),
                        'prt_color_body'   => __('Body text', 'pressroot'),
                    ] as $modKey => $label) : ?>
                        <label style="display:flex;flex-direction:column;gap:4px;font-size:12px">
                            <?php echo esc_html($label); ?>
                            <input type="color" name="<?php echo esc_attr($modKey); ?>" value="<?php echo esc_attr($mod($modKey, '#6C4CF1')); ?>" style="width:56px;height:34px;padding:2px">
                        </label>
                    <?php endforeach; ?>
                </td>
            </tr>
            <tr>
                <th scope="row"><?php esc_html_e('Corners', 'pressroot'); ?></th>
                <td>
                    <label><?php esc_html_e('Buttons', 'pressroot'); ?> <input type="number" name="prt_btn_radius" min="0" max="999" class="small-text" value="<?php echo esc_attr($mod('prt_btn_radius', '999')); ?>"> px</label>
                    &nbsp;&nbsp;
                    <label><?php esc_html_e('Cards', 'pressroot'); ?> <input type="number" name="prt_card_radius" min="0" max="60" class="small-text" value="<?php echo esc_attr($mod('prt_card_radius', '18')); ?>"> px</label>
                </td>
            </tr>
        </table>

        <h3><?php esc_html_e('Typography & layout', 'pressroot'); ?></h3>
        <table class="form-table" role="presentation">
            <tr>
                <th scope="row"><?php esc_html_e('Fonts', 'pressroot'); ?></th>
                <td>
                    <label><?php esc_html_e('Headings', 'pressroot'); ?>
                        <select name="prt_font_heading">
                            <?php foreach ($fonts as $name => $def) : ?>
                                <option value="<?php echo esc_attr($name); ?>" <?php selected($mod('prt_font_heading', 'Outfit'), $name); ?>><?php echo esc_html($name); ?></option>
                            <?php endforeach; ?>
                        </select>
                    </label>
                    &nbsp;&nbsp;
                    <label><?php esc_html_e('Body', 'pressroot'); ?>
                        <select name="prt_font_body">
                            <?php foreach ($fonts as $name => $def) : ?>
                                <option value="<?php echo esc_attr($name); ?>" <?php selected($mod('prt_font_body', 'Outfit'), $name); ?>><?php echo esc_html($name); ?></option>
                            <?php endforeach; ?>
                        </select>
                    </label>
                </td>
            </tr>
            <tr>
                <th scope="row"><label for="prt_container"><?php esc_html_e('Content width', 'pressroot'); ?></label></th>
                <td><input type="number" id="prt_container" name="prt_container" min="720" max="1920" step="10" class="small-text" value="<?php echo esc_attr($mod('prt_container', '1180')); ?>"> px</td>
            </tr>
        </table>

        <h3><?php esc_html_e('Homepage hero copy', 'pressroot'); ?></h3>
        <table class="form-table" role="presentation">
            <tr>
                <th scope="row"><?php esc_html_e('Headline pieces', 'pressroot'); ?></th>
                <td style="display:flex;gap:8px;flex-wrap:wrap">
                    <input type="text" name="prt_hero_title" placeholder="<?php esc_attr_e('Opening line', 'pressroot'); ?>" value="<?php echo esc_attr($mod('prt_hero_title', __('Your brand in.', 'pressroot'))); ?>">
                    <input type="text" name="prt_hero_accent" placeholder="<?php esc_attr_e('Gradient word', 'pressroot'); ?>" value="<?php echo esc_attr($mod('prt_hero_accent', __('Your site', 'pressroot'))); ?>">
                    <input type="text" name="prt_hero_serif" placeholder="<?php esc_attr_e('Serif word', 'pressroot'); ?>" value="<?php echo esc_attr($mod('prt_hero_serif', __('out.', 'pressroot'))); ?>">
                    <input type="text" name="prt_hero_suffix" placeholder="<?php esc_attr_e('Closing phrase', 'pressroot'); ?>" value="<?php echo esc_attr($mod('prt_hero_suffix', '')); ?>">
                </td>
            </tr>
            <tr>
                <th scope="row"><label for="prt_hero_subtext"><?php esc_html_e('Supporting paragraph', 'pressroot'); ?></label></th>
                <td><textarea id="prt_hero_subtext" name="prt_hero_subtext" rows="2" class="large-text"><?php echo esc_textarea($mod('prt_hero_subtext', '')); ?></textarea></td>
            </tr>
            <tr>
                <th scope="row"><?php esc_html_e('Buttons & chips', 'pressroot'); ?></th>
                <td style="display:flex;gap:8px;flex-wrap:wrap">
                    <input type="text" name="prt_hero_btn1_text" placeholder="<?php esc_attr_e('Primary button', 'pressroot'); ?>" value="<?php echo esc_attr($mod('prt_hero_btn1_text', __('See the work →', 'pressroot'))); ?>">
                    <input type="text" name="prt_hero_btn2_text" placeholder="<?php esc_attr_e('Secondary button', 'pressroot'); ?>" value="<?php echo esc_attr($mod('prt_hero_btn2_text', __("Let's talk", 'pressroot'))); ?>">
                    <input type="text" name="prt_hero_chip1" placeholder="<?php esc_attr_e('Chip 1', 'pressroot'); ?>" value="<?php echo esc_attr($mod('prt_hero_chip1', __('⚡ Fast by default', 'pressroot'))); ?>">
                    <input type="text" name="prt_hero_chip2" placeholder="<?php esc_attr_e('Chip 2', 'pressroot'); ?>" value="<?php echo esc_attr($mod('prt_hero_chip2', __('♿ Accessible first', 'pressroot'))); ?>">
                </td>
            </tr>
            <tr>
                <th scope="row"><?php esc_html_e('Availability badge', 'pressroot'); ?></th>
                <td>
                    <label><input type="checkbox" name="prt_avail_open" value="1" <?php checked((bool) $mod('prt_avail_open', true)); ?>> <?php esc_html_e('Show', 'pressroot'); ?></label>
                    <input type="text" name="prt_avail_text" class="regular-text" style="margin-left:10px" value="<?php echo esc_attr($mod('prt_avail_text', '')); ?>" placeholder="<?php esc_attr_e('Available for new projects', 'pressroot'); ?>">
                </td>
            </tr>
        </table>
        </details>
        <?php endif; // $setupDone — Design/Typography/Hero fine-tuning unlocks after first build ?>

        <h3><?php esc_html_e('Features & addons', 'pressroot'); ?></h3>
        <table class="form-table" role="presentation">
            <tr>
                <th scope="row"><?php esc_html_e('Dark mode', 'pressroot'); ?></th>
                <td>
                    <label><input type="checkbox" name="prt_dark_enable" value="1" <?php checked((bool) $mod('prt_dark_enable', true)); ?>> <?php esc_html_e('Enable toggle', 'pressroot'); ?></label>
                    <select name="prt_dark_default" style="margin-left:10px">
                        <?php foreach (['light' => __('Default: light', 'pressroot'), 'dark' => __('Default: dark', 'pressroot'), 'auto' => __('Default: follow device', 'pressroot')] as $v => $l) : ?>
                            <option value="<?php echo esc_attr($v); ?>" <?php selected($mod('prt_dark_default', 'light'), $v); ?>><?php echo esc_html($l); ?></option>
                        <?php endforeach; ?>
                    </select>
                </td>
            </tr>
            <tr>
                <th scope="row"><?php esc_html_e('Toggles', 'pressroot'); ?></th>
                <td>
                    <label style="display:block;margin-bottom:6px"><input type="checkbox" name="prt_scroll_enable" value="1" <?php checked((bool) $mod('prt_scroll_enable', true)); ?>> <?php esc_html_e('On-scroll reveal animations', 'pressroot'); ?></label>
                    <label style="display:block;margin-bottom:6px"><input type="checkbox" name="prt_split_block_css" value="1" <?php checked((bool) $mod('prt_split_block_css', true)); ?>> <?php esc_html_e('Load block CSS only when used (faster pages)', 'pressroot'); ?></label>
                    <label style="display:block;margin-bottom:6px"><input type="checkbox" name="prt_core_blocks_only" value="1" <?php checked((bool) $mod('prt_core_blocks_only', true)); ?>> <?php esc_html_e('Generated pages use only core Gutenberg + Pressroot theme blocks (recommended — no Custom HTML blocks)', 'pressroot'); ?></label>
                    <label style="display:block;margin-bottom:6px"><input type="checkbox" name="prt_addon_pressroot_ai_enabled" value="1" <?php checked((bool) $mod('prt_addon_pressroot_ai_enabled', true)); ?>> <?php esc_html_e('Pressroot AI module (Site Types, generator, AI tools)', 'pressroot'); ?></label>
                    <label style="display:block"><input type="checkbox" name="prt_addon_repofolio_enabled" value="1" <?php checked((bool) $mod('prt_addon_repofolio_enabled', true)); ?>> <?php esc_html_e('Repofolio addon (GitHub portfolio)', 'pressroot'); ?></label>
                </td>
            </tr>
        </table>

        <?php submit_button(__('Save theme settings', 'pressroot')); ?>
    </form>

    <?php if (isset($_GET['prt_brand_suggested'])) : ?>
        <?php if ($_GET['prt_brand_suggested'] === '1') : ?>
            <div class="notice notice-success is-dismissible"><p><?php esc_html_e('✨ AI drafted your voice, audience, goal, and personality from the name + description — review the Brand section above and adjust anything that feels off, then Save.', 'pressroot'); ?></p></div>
        <?php else : ?>
            <div class="notice notice-error is-dismissible"><p><?php esc_html_e('The AI could not draft the brand right now — try again or switch models.', 'pressroot'); ?></p></div>
        <?php endif; ?>
    <?php endif; ?>

    <?php if (function_exists('App\prt_ai_features_enabled') && prt_ai_features_enabled()) : ?>
        <div class="prt-rf-card" style="max-width:720px;padding:18px 22px;margin-bottom:14px">
            <strong><?php esc_html_e('✨ Generate my brand with AI', 'pressroot'); ?></strong>
            <p class="description" style="margin:6px 0 12px"><?php esc_html_e('Only have a name and a one-liner? Save those two fields first, then let the AI draft your voice words, audience, goal, and personality — you stay in charge and can edit everything it suggests.', 'pressroot'); ?></p>
            <form method="post" action="<?php echo esc_url(admin_url('admin-post.php')); ?>" style="margin:0">
                <input type="hidden" name="action" value="prt_ai_suggest_brand">
                <?php wp_nonce_field('prt_ai_suggest_brand'); ?>
                <button class="button button-primary"><?php esc_html_e('Draft my brand answers', 'pressroot'); ?></button>
            </form>
        </div>
        <?php if (isset($_GET['prt_img_done'])) : ?>
            <div class="notice notice-success is-dismissible"><p><?php esc_html_e('✨ Brand image generated, saved to your Media Library, and set as the homepage hero image.', 'pressroot'); ?></p></div>
        <?php elseif (isset($_GET['prt_img_error'])) : ?>
            <div class="notice notice-error is-dismissible"><p><?php echo esc_html(sanitize_text_field(wp_unslash($_GET['prt_img_error']))); ?></p></div>
        <?php endif; ?>
        <div class="prt-rf-card" style="max-width:720px;padding:18px 22px;margin-bottom:14px">
            <strong><?php esc_html_e('✨ Generate a brand image with AI', 'pressroot'); ?></strong>
            <p class="description" style="margin:6px 0 12px"><?php esc_html_e('Uses your industry, imagery style, and voice answers above (free by default), saves to the Media Library, and sets the homepage hero image. Each run is different.', 'pressroot'); ?></p>
            <form method="post" action="<?php echo esc_url(admin_url('admin-post.php')); ?>" style="margin:0">
                <input type="hidden" name="action" value="prt_ai_brand_image">
                <?php wp_nonce_field('prt_ai_brand_image'); ?>
                <button class="button button-primary"><?php esc_html_e('Generate brand image', 'pressroot'); ?></button>
            </form>
        </div>
    <?php endif; ?>

    <div class="prt-rf-card" style="max-width:720px;padding:18px 22px">
        <strong><?php esc_html_e('Advanced controls (opens the Customizer with live preview)', 'pressroot'); ?></strong>
        <p class="description" style="margin:6px 0 10px"><?php esc_html_e('The long tail lives in the Customizer because live preview genuinely helps there. Everything above stays in sync with it either way.', 'pressroot'); ?></p>
        <ul style="margin:0;columns:2;font-size:13px">
            <?php foreach (prt_customizer_deep_links() as [$section, $label]) : ?>
                <li style="margin-bottom:6px"><a href="<?php echo esc_url(admin_url('customize.php?autofocus[section]=' . $section)); ?>"><?php echo esc_html($label); ?> ↗</a></li>
            <?php endforeach; ?>
            <li style="margin-bottom:6px"><a href="<?php echo esc_url(admin_url('customize.php')); ?>"><?php esc_html_e('Open the full Customizer', 'pressroot'); ?> ↗</a></li>
        </ul>
    </div>
    <?php
}
