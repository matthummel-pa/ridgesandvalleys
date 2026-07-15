<?php

/**
 * Setup wizard — the guided, six-step onboarding flow on Appearance ->
 * Pressroot (first tab: "Setup").
 *
 * Why it exists: everything a new owner needs was already IN the theme —
 * brand questionnaire (theme-settings-tab.php), AI connectors
 * (ai-connectors.php), site-type generation (ai-assistant.php +
 * site-type-remix.php), per-page AI writing (ai-builder.php) — but spread
 * across flat tabs with no order, no progress, and no finish line (generated
 * pages stayed drafts forever). The wizard sequences those existing engines
 * and fills the genuine gaps: business contact/hours/mission fields, a GA4
 * field with a guided walkthrough (no OAuth — a theme can't ship Google
 * credentials), an SEO plugin selector with one-click installs, a WordPress
 * settings automation step, a consolidated review screen, and a real
 * launch/publish action.
 *
 * The steps:
 *   1. Business information  — identity, type, brand, contact, hours, media
 *   2. Connections           — AI APIs, SEO plugin choice, Google Analytics, addons
 *   3. WordPress settings    — guided walkthrough + one-click recommended setup
 *   4. Generate your website — site type pages, AI copy, AI brand image
 *   5. Review & fine-tune    — previews + where to edit what
 *   6. Launch                — pre-flight checklist, publish drafts, go live
 *
 * State lives in ONE option (`prt_wizard_progress`: ['done' => [step => ts]])
 * so the owner can leave and resume anywhere; every step stays revisitable
 * after completion (the stepper is navigation, not a one-way gate). All data
 * fields write the SAME theme_mods/options the rest of the theme reads —
 * the wizard is a front door, never a second store, matching the
 * theme-settings-tab.php philosophy.
 */

namespace App;

/* ──────────────────────────────────────────────────────────────────────────
 * Shared vocabulary
 * ────────────────────────────────────────────────────────────────────── */

/**
 * The business-industry dropdown, shared with the Theme Settings tab (which
 * used to hardcode this list inline — it now calls this). Same filter name,
 * so existing forks keep working.
 */
function prt_brand_industries(): array
{
    return apply_filters('pressroot/brand_industries', [
        __('Home services (plumbing, HVAC, electrical)', 'pressroot'),
        __('Construction & trades', 'pressroot'),
        __('Restaurant, café & food', 'pressroot'),
        __('Real estate', 'pressroot'),
        __('Health & wellness', 'pressroot'),
        __('Fitness & coaching', 'pressroot'),
        __('Beauty & salon', 'pressroot'),
        __('Legal services', 'pressroot'),
        __('Finance & insurance', 'pressroot'),
        __('E-commerce & retail', 'pressroot'),
        __('SaaS & technology', 'pressroot'),
        __('Marketing & creative agency', 'pressroot'),
        __('Photography & events', 'pressroot'),
        __('Education & courses', 'pressroot'),
        __('Travel & hospitality', 'pressroot'),
        __('Automotive', 'pressroot'),
        __('Nonprofit & community', 'pressroot'),
        __('Content publishing & affiliate', 'pressroot'),
    ]);
}

/** Days of the week for the business-hours grid, keyed for storage. */
function prt_wizard_days(): array
{
    return [
        'mon' => __('Monday', 'pressroot'),
        'tue' => __('Tuesday', 'pressroot'),
        'wed' => __('Wednesday', 'pressroot'),
        'thu' => __('Thursday', 'pressroot'),
        'fri' => __('Friday', 'pressroot'),
        'sat' => __('Saturday', 'pressroot'),
        'sun' => __('Sunday', 'pressroot'),
    ];
}

/* ──────────────────────────────────────────────────────────────────────────
 * Wizard state
 * ────────────────────────────────────────────────────────────────────── */

/** The steps, in order. Labels double as the stepper captions. */
function prt_wizard_steps(): array
{
    return [
        1 => ['label' => __('Business info', 'pressroot'),   'icon' => '🏪'],
        2 => ['label' => __('Connections', 'pressroot'),     'icon' => '🔌'],
        3 => ['label' => __('WP settings', 'pressroot'),     'icon' => '⚙️'],
        4 => ['label' => __('Design', 'pressroot'),          'icon' => '🎨'],
        5 => ['label' => __('Generate site', 'pressroot'),   'icon' => '✨'],
        6 => ['label' => __('Review', 'pressroot'),          'icon' => '👀'],
        7 => ['label' => __('Launch', 'pressroot'),          'icon' => '🚀'],
    ];
}

/**
 * One-time progress migration: the Design step was inserted at position 4
 * (v2 layout), pushing Generate/Review/Launch from 4/5/6 to 5/6/7. Remap
 * previously-recorded completions so owners who already finished those steps
 * don't see them reset — only the new Design step shows as pending.
 */
add_action('after_setup_theme', function () {
    if (get_option('prt_wizard_layout_v') >= 2) {
        return;
    }
    $p = get_option('prt_wizard_progress', []);
    if (is_array($p) && ! empty($p['done'])) {
        $done = $p['done'];
        foreach ([6 => 7, 5 => 6, 4 => 5] as $old => $new) { // high→low, no clobber
            if (isset($done[$old])) {
                $done[$new] = $done[$old];
                unset($done[$old]);
            }
        }
        $p['done'] = $done;
        update_option('prt_wizard_progress', $p, false);
    }
    update_option('prt_wizard_layout_v', 2, false);
}, 20);

/** Progress state: ['done' => [stepNumber => timestamp]]. */
function prt_wizard_progress(): array
{
    $p = get_option('prt_wizard_progress', []);
    return is_array($p) ? array_merge(['done' => []], $p) : ['done' => []];
}

/** Mark one step complete (idempotent) and persist. */
function prt_wizard_mark_done(int $step): void
{
    $p = prt_wizard_progress();
    if (! isset($p['done'][$step])) {
        $p['done'][$step] = time();
        update_option('prt_wizard_progress', $p, false);
    }
}

/** True once every step has been completed at least once. */
function prt_wizard_is_complete(): bool
{
    $done = prt_wizard_progress()['done'];
    foreach (array_keys(prt_wizard_steps()) as $n) {
        if (empty($done[$n])) {
            return false;
        }
    }
    return true;
}

/** URL to one wizard step (always via the canonical tab-URL builder). */
function prt_wizard_url(int $step, array $extra = []): string
{
    return prt_settings_tab_url('setup', array_merge(['step' => $step], $extra));
}

/**
 * The step the owner should see when none is requested: the first
 * not-yet-completed step, so reopening the tab resumes where they left off.
 */
function prt_wizard_resume_step(): int
{
    $done = prt_wizard_progress()['done'];
    foreach (array_keys(prt_wizard_steps()) as $n) {
        if (empty($done[$n])) {
            return $n;
        }
    }
    return 7;
}

/* ──────────────────────────────────────────────────────────────────────────
 * Tab shell + stepper
 * ────────────────────────────────────────────────────────────────────── */

/** The "Setup" tab: stepper header + the active step's screen. */
function prt_setup_wizard_tab_html(): void
{
    if (! current_user_can('edit_theme_options')) {
        return;
    }
    $steps   = prt_wizard_steps();
    $current = isset($_GET['step']) ? max(1, min(count($steps), absint($_GET['step']))) : prt_wizard_resume_step();

    if (isset($_GET['prt_wiz_saved'])) {
        echo '<div class="notice notice-success is-dismissible"><p>' . esc_html__('Saved — on to the next step.', 'pressroot') . '</p></div>';
    }
    if (isset($_GET['prt_wiz_error'])) {
        echo '<div class="notice notice-error is-dismissible"><p>' . esc_html(sanitize_text_field(wp_unslash($_GET['prt_wiz_error']))) . '</p></div>';
    }
    ?>
    <h2 style="margin-top:0"><?php esc_html_e('Set up your website', 'pressroot'); ?></h2>
    <p class="description" style="max-width:680px"><?php esc_html_e('Seven guided steps from blank install to launched site. Your progress is saved as you go — leave anytime and this tab reopens where you stopped. Every step stays editable after you finish it.', 'pressroot'); ?></p>

    <?php prt_wizard_stepper($current); ?>
    <?php prt_wizard_progress_bar(); ?>
    <?php prt_wizard_saving_bar_js(); ?>

    <?php
    switch ($current) {
        case 1:
            prt_wizard_step_business();
            break;
        case 2:
            prt_wizard_step_connect();
            break;
        case 3:
            prt_wizard_step_wpsettings();
            break;
        case 4:
            prt_wizard_step_design();
            break;
        case 5:
            prt_wizard_step_generate();
            break;
        case 6:
            prt_wizard_step_review();
            break;
        case 7:
            prt_wizard_step_launch();
            break;
    }
}

/** The numbered progress chips. Done = ✓, current = highlighted, all clickable. */
function prt_wizard_stepper(int $current): void
{
    $done = prt_wizard_progress()['done'];
    ?>
    <ol class="prt-wiz-steps" aria-label="<?php esc_attr_e('Setup progress', 'pressroot'); ?>">
        <?php foreach (prt_wizard_steps() as $n => $step) :
            $isDone    = ! empty($done[$n]);
            $isCurrent = $n === $current;
            $cls       = 'prt-wiz-step' . ($isDone ? ' is-done' : '') . ($isCurrent ? ' is-current' : '');
        ?>
            <li class="<?php echo esc_attr($cls); ?>">
                <a href="<?php echo esc_url(prt_wizard_url($n)); ?>"<?php echo $isCurrent ? ' aria-current="step"' : ''; ?>>
                    <span class="prt-wiz-step-num"><?php echo $isDone ? '✓' : (int) $n; ?></span>
                    <span class="prt-wiz-step-label"><?php echo esc_html($step['icon'] . ' ' . $step['label']); ?></span>
                </a>
            </li>
        <?php endforeach; ?>
    </ol>
    <?php
}

/**
 * Overall wizard progress — a spectrum fill under the stepper showing how
 * many of the six steps are complete. Same visual language as
 * prt_build_status_bar() in app/theme-settings-tab.php.
 */
function prt_wizard_progress_bar(): void
{
    $total = count(prt_wizard_steps());
    $done  = count(array_filter(prt_wizard_progress()['done']));
    $pct   = $total ? (int) round(($done / $total) * 100) : 0;
    ?>
    <div class="prt-wiz-progress" role="progressbar" aria-valuenow="<?php echo (int) $pct; ?>" aria-valuemin="0" aria-valuemax="100" aria-label="<?php esc_attr_e('Setup progress', 'pressroot'); ?>">
        <div class="prt-wiz-progress-track"><div class="prt-wiz-progress-fill" style="width:<?php echo (int) max(2, $pct); ?>%"></div></div>
        <span class="prt-wiz-progress-text"><?php echo esc_html(sprintf(
            /* translators: 1: completed steps, 2: total steps, 3: percent */
            __('%1$d of %2$d steps complete · %3$d%%', 'pressroot'),
            $done,
            $total,
            $pct
        )); ?></span>
    </div>
    <?php
}

/**
 * Per-step "working…" status bar. Each wizard form renders one of these
 * (hidden) via prt_wizard_saving_bar() and arms it with
 * onsubmit="prtWizStart('<id>')" — the moment the form posts, the bar
 * appears and walks through step-specific labels while the server does the
 * real work (uploads, generation, publishing). The bar parks at 92% until
 * the redirect lands, so it never claims "done" before the server is.
 *
 * Same pattern as the Theme Settings tab's sticky saving bar, generalized:
 * one shared script (below), any number of bars, labels supplied per form.
 */
function prt_wizard_saving_bar(string $id, string $title, array $steps): void
{
    ?>
    <div id="<?php echo esc_attr($id); ?>" class="prt-wiz-saving" style="display:none" data-steps="<?php echo esc_attr(wp_json_encode(array_values($steps))); ?>">
        <strong data-label>🏗 <?php echo esc_html($title); ?></strong>
        <div class="prt-wiz-saving-track"><div class="prt-wiz-saving-fill" data-bar style="width:5%"></div></div>
    </div>
    <?php
}

/** The one shared driver for every saving bar on the tab (printed once). */
function prt_wizard_saving_bar_js(): void
{
    ?>
    <script>
    window.prtWizStart = window.prtWizStart || function (id) {
        var box = document.getElementById(id);
        if (! box) { return true; }
        box.style.display = 'block';
        try { box.scrollIntoView({ block: 'nearest', behavior: 'smooth' }); } catch (e) {}
        var steps = [];
        try { steps = JSON.parse(box.dataset.steps || '[]'); } catch (e) {}
        var bar = box.querySelector('[data-bar]');
        var label = box.querySelector('[data-label]');
        var i = 0;
        var t = setInterval(function () {
            if (i < steps.length) {
                label.textContent = '🏗 ' + steps[i];
                bar.style.width = Math.min(92, Math.round(((i + 1) / (steps.length + 1)) * 100)) + '%';
                i++;
            } else {
                bar.style.width = '92%'; // parks here; the redirect finishes the story
                clearInterval(t);
            }
        }, 900);
        return true; // never block the actual submit
    };
    </script>
    <?php
}

/** Nav row rendered under every step form: back link + primary submit. */
function prt_wizard_nav(int $step, string $submitLabel): void
{
    ?>
    <p class="submit" style="display:flex;gap:10px;align-items:center">
        <?php if ($step > 1) : ?>
            <a class="button" href="<?php echo esc_url(prt_wizard_url($step - 1)); ?>">← <?php esc_html_e('Back', 'pressroot'); ?></a>
        <?php endif; ?>
        <button type="submit" class="button button-primary button-hero" style="margin:0"><?php echo esc_html($submitLabel); ?></button>
    </p>
    <?php
}

/* ──────────────────────────────────────────────────────────────────────────
 * Step 1 — Business information
 * ────────────────────────────────────────────────────────────────────── */

function prt_wizard_step_business(): void
{
    $mod    = fn ($k, $d = '') => get_theme_mod($k, $d);
    $types  = function_exists('App\\prt_site_types') ? prt_site_types() : [];
    $fonts  = function_exists('App\\prt_fonts') ? prt_fonts() : [];
    $b      = function_exists('App\\prt_brand_profile') ? prt_brand_profile() : [];
    $hours  = (array) $mod('prt_biz_hours', []);
    $social = function_exists('App\\prt_social_platforms') ? prt_social_platforms() : [];

    $industries      = prt_brand_industries();
    $currentIndustry = (string) ($b['industry'] ?? '');
    $isListed        = in_array($currentIndustry, $industries, true);
    ?>
    <div class="prt-rf-card prt-wiz-card">
        <h3 style="margin-top:0">1 · <?php esc_html_e('Tell us about your business', 'pressroot'); ?></h3>
        <p class="description"><?php esc_html_e('Everything here feeds two engines: the design generator (colors, fonts, layout) and the AI writer (every word it drafts). The more you fill in, the better both get — but only the name and one-liner are required.', 'pressroot'); ?></p>

        <?php prt_wizard_saving_bar('prt-wiz-bar-business', __('Saving your business info…', 'pressroot'), [
            __('Saving business details', 'pressroot'),
            __('Uploading your logo & media', 'pressroot'),
            __('Compiling your core AI brief', 'pressroot'),
        ]); ?>
        <form method="post" action="<?php echo esc_url(admin_url('admin-post.php')); ?>" enctype="multipart/form-data" onsubmit="return prtWizStart('prt-wiz-bar-business')">
            <input type="hidden" name="action" value="prt_wizard_save_business">
            <?php wp_nonce_field('prt_wizard_save_business'); ?>

            <h4><?php esc_html_e('Identity', 'pressroot'); ?></h4>
            <table class="form-table" role="presentation">
                <tr>
                    <th scope="row"><label for="prt_wiz_name"><?php esc_html_e('Business name', 'pressroot'); ?> <span class="required">*</span></label></th>
                    <td><input type="text" id="prt_wiz_name" name="blogname" class="regular-text" required value="<?php echo esc_attr(get_option('blogname')); ?>"></td>
                </tr>
                <tr>
                    <th scope="row"><label for="prt_wiz_desc"><?php esc_html_e('What you do, in one line', 'pressroot'); ?> <span class="required">*</span></label></th>
                    <td>
                        <input type="text" id="prt_wiz_desc" name="blogdescription" class="large-text" required value="<?php echo esc_attr(get_option('blogdescription')); ?>" placeholder="<?php esc_attr_e('who you help and how — doubles as the site tagline', 'pressroot'); ?>">
                    </td>
                </tr>
                <tr>
                    <th scope="row"><label for="prt_wiz_site_type"><?php esc_html_e('Business / website type', 'pressroot'); ?></label></th>
                    <td>
                        <select id="prt_wiz_site_type" name="prt_wizard_site_type">
                            <option value=""><?php esc_html_e('— choose a type —', 'pressroot'); ?></option>
                            <?php foreach ($types as $slug => $t) : ?>
                                <option value="<?php echo esc_attr($slug); ?>" <?php selected(get_option('prt_wizard_site_type'), $slug); ?>><?php echo esc_html($t['label'] . ' — ' . $t['desc']); ?></option>
                            <?php endforeach; ?>
                        </select>
                        <p class="description"><?php esc_html_e('Decides which starter pages and layouts get generated in step 5.', 'pressroot'); ?></p>
                    </td>
                </tr>
                <tr>
                    <th scope="row"><label for="prt_wiz_industry"><?php esc_html_e('Industry', 'pressroot'); ?></label></th>
                    <td>
                        <select id="prt_wiz_industry" name="prt_brand_industry_sel" onchange="document.getElementById('prt_wiz_industry_other').style.display = this.value === '__other' ? '' : 'none'">
                            <option value=""><?php esc_html_e('— choose an industry —', 'pressroot'); ?></option>
                            <?php foreach ($industries as $ind) : ?>
                                <option value="<?php echo esc_attr($ind); ?>" <?php selected($currentIndustry, $ind); ?>><?php echo esc_html($ind); ?></option>
                            <?php endforeach; ?>
                            <option value="__other" <?php selected(! $isListed && $currentIndustry !== ''); ?>><?php esc_html_e('Other…', 'pressroot'); ?></option>
                        </select>
                        <input type="text" id="prt_wiz_industry_other" name="prt_brand_industry_other" class="regular-text" style="margin-left:8px;<?php echo ($isListed || $currentIndustry === '') ? 'display:none' : ''; ?>" value="<?php echo esc_attr($isListed ? '' : $currentIndustry); ?>" placeholder="<?php esc_attr_e('describe your industry', 'pressroot'); ?>">
                    </td>
                </tr>
                <tr>
                    <th scope="row"><label for="prt_biz_mission"><?php esc_html_e('Mission statement', 'pressroot'); ?></label></th>
                    <td><textarea id="prt_biz_mission" name="prt_biz_mission" rows="2" class="large-text" placeholder="<?php esc_attr_e('Why does your business exist? One or two sentences.', 'pressroot'); ?>"><?php echo esc_textarea($mod('prt_biz_mission')); ?></textarea></td>
                </tr>
                <tr>
                    <th scope="row"><label for="prt_biz_about"><?php esc_html_e('What your business does', 'pressroot'); ?></label></th>
                    <td>
                        <textarea id="prt_biz_about" name="prt_biz_about" rows="4" class="large-text" placeholder="<?php esc_attr_e('Services, products, specialties, service area — a short paragraph in your own words. The AI quotes facts from here instead of inventing them.', 'pressroot'); ?>"><?php echo esc_textarea($mod('prt_biz_about')); ?></textarea>
                    </td>
                </tr>
            </table>

            <h4><?php esc_html_e('Brand & voice', 'pressroot'); ?></h4>
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
                    <th scope="row"><?php esc_html_e('Personality · goal', 'pressroot'); ?></th>
                    <td style="display:flex;gap:10px;flex-wrap:wrap">
                        <select name="prt_brand_vibe">
                            <?php foreach (['any' => __('Anything goes', 'pressroot'), 'bold' => __('Bold & confident', 'pressroot'), 'minimal' => __('Minimal & sharp', 'pressroot'), 'warm' => __('Warm & inviting', 'pressroot'), 'playful' => __('Playful & bright', 'pressroot')] as $val => $label) : ?>
                                <option value="<?php echo esc_attr($val); ?>" <?php selected($b['vibe'] ?? 'any', $val); ?>><?php echo esc_html($label); ?></option>
                            <?php endforeach; ?>
                        </select>
                        <select name="prt_brand_goal">
                            <?php foreach (['leads' => __('Get leads / inquiries', 'pressroot'), 'sell' => __('Sell products', 'pressroot'), 'book' => __('Book appointments', 'pressroot'), 'read' => __('Grow an audience', 'pressroot')] as $val => $label) : ?>
                                <option value="<?php echo esc_attr($val); ?>" <?php selected($b['goal'] ?? 'leads', $val); ?>><?php echo esc_html($label); ?></option>
                            <?php endforeach; ?>
                        </select>
                    </td>
                </tr>
                <tr>
                    <th scope="row"><label for="prt_wiz_audience"><?php esc_html_e('Who is it for?', 'pressroot'); ?></label></th>
                    <td><input type="text" id="prt_wiz_audience" name="prt_brand_audience" class="large-text" value="<?php echo esc_attr($b['audience'] ?? ''); ?>" placeholder="<?php esc_attr_e('e.g. busy parents in Adams County who need a reliable plumber', 'pressroot'); ?>"></td>
                </tr>
                <tr>
                    <th scope="row"><label for="prt_wiz_tone"><?php esc_html_e('Three words for your voice', 'pressroot'); ?></label></th>
                    <td><input type="text" id="prt_wiz_tone" name="prt_brand_tone" class="regular-text" value="<?php echo esc_attr($b['tone'] ?? ''); ?>" placeholder="<?php esc_attr_e('e.g. friendly, expert, no-nonsense', 'pressroot'); ?>"></td>
                </tr>
                <tr>
                    <th scope="row"><?php esc_html_e('Colors', 'pressroot'); ?></th>
                    <td style="display:flex;gap:18px;flex-wrap:wrap">
                        <?php
                        // Per-field defaults MUST match prt_defaults() — a single
                        // shared default here once collapsed background/headings/
                        // body toward the brand purple on an untouched save,
                        // producing same-on-same (inaccessible) pages.
                        foreach ([
                            'prt_color_action' => [__('Brand color', 'pressroot'), '#6C4CF1'],
                            'prt_color_paper'  => [__('Background', 'pressroot'), '#FFF9F5'],
                            'prt_color_ink'    => [__('Headings', 'pressroot'), '#17151F'],
                            'prt_color_body'   => [__('Body text', 'pressroot'), '#4A4660'],
                        ] as $modKey => [$label, $fieldDefault]) : ?>
                            <label style="display:flex;flex-direction:column;gap:4px;font-size:12px">
                                <?php echo esc_html($label); ?>
                                <input type="color" name="<?php echo esc_attr($modKey); ?>" value="<?php echo esc_attr($mod($modKey, $fieldDefault)); ?>" style="width:56px;height:34px;padding:2px">
                            </label>
                        <?php endforeach; ?>
                        <p class="description" style="flex-basis:100%;margin:2px 0 0"><?php esc_html_e('Optional — leave as-is and the design generator picks a matching palette in step 5. Your brand color survives every re-deal.', 'pressroot'); ?></p>
                    </td>
                </tr>
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
                    <th scope="row"><label for="prt_wiz_logo"><?php esc_html_e('Logo', 'pressroot'); ?></label></th>
                    <td>
                        <?php $logoId = (int) get_theme_mod('custom_logo'); ?>
                        <?php if ($logoId) : ?>
                            <p style="margin:0 0 8px"><?php echo wp_get_attachment_image($logoId, [120, 60]); ?></p>
                        <?php endif; ?>
                        <input type="file" id="prt_wiz_logo" name="prt_wiz_logo" accept="image/png,image/jpeg,image/webp,image/svg+xml">
                        <p class="description"><?php esc_html_e('PNG with transparency works best. Used in the header, login screen, and structured data.', 'pressroot'); ?></p>
                    </td>
                </tr>
                <tr>
                    <th scope="row"><label for="prt_wiz_media"><?php esc_html_e('Photos & video', 'pressroot'); ?></label></th>
                    <td>
                        <input type="file" id="prt_wiz_media" name="prt_wiz_media[]" accept="image/*,video/mp4,video/webm" multiple>
                        <p class="description"><?php esc_html_e('Optional: upload your own photos/videos now — they land in the Media Library ready to swap into any page. No photos? Step 5 can generate brand images with AI (free by default). AI video generation ships in a future release.', 'pressroot'); ?></p>
                    </td>
                </tr>
            </table>

            <h4><?php esc_html_e('Contact & hours', 'pressroot'); ?></h4>
            <table class="form-table" role="presentation">
                <tr>
                    <th scope="row"><label for="prt_biz_email"><?php esc_html_e('Public email', 'pressroot'); ?></label></th>
                    <td><input type="email" id="prt_biz_email" name="prt_biz_email" class="regular-text" value="<?php echo esc_attr($mod('prt_biz_email')); ?>"></td>
                </tr>
                <tr>
                    <th scope="row"><label for="prt_biz_phone"><?php esc_html_e('Phone', 'pressroot'); ?></label></th>
                    <td><input type="text" id="prt_biz_phone" name="prt_biz_phone" class="regular-text" value="<?php echo esc_attr($mod('prt_biz_phone')); ?>"></td>
                </tr>
                <tr>
                    <th scope="row"><label for="prt_biz_address"><?php esc_html_e('Address / service area', 'pressroot'); ?></label></th>
                    <td><textarea id="prt_biz_address" name="prt_biz_address" rows="2" class="large-text"><?php echo esc_textarea($mod('prt_biz_address')); ?></textarea></td>
                </tr>
                <tr>
                    <th scope="row"><?php esc_html_e('Business hours', 'pressroot'); ?></th>
                    <td>
                        <div class="prt-wiz-hours">
                            <?php foreach (prt_wizard_days() as $key => $label) : ?>
                                <label>
                                    <span><?php echo esc_html($label); ?></span>
                                    <input type="text" name="prt_biz_hours[<?php echo esc_attr($key); ?>]" value="<?php echo esc_attr((string) ($hours[$key] ?? '')); ?>" placeholder="<?php esc_attr_e('9:00 AM – 5:00 PM, or Closed', 'pressroot'); ?>">
                                </label>
                            <?php endforeach; ?>
                        </div>
                        <p class="description"><?php esc_html_e('Free-form per day. Leave blank to skip — the AI and contact patterns only mention hours when set.', 'pressroot'); ?></p>
                    </td>
                </tr>
            </table>

            <h4><?php esc_html_e('Social media accounts', 'pressroot'); ?></h4>
            <table class="form-table" role="presentation">
                <tr>
                    <th scope="row"><?php esc_html_e('Profile URLs', 'pressroot'); ?></th>
                    <td>
                        <div class="prt-wiz-social">
                            <?php foreach ($social as $key => $p) : ?>
                                <label>
                                    <span><?php echo esc_html($p['label']); ?></span>
                                    <input type="url" name="prt_social[<?php echo esc_attr($key); ?>]" value="<?php echo esc_attr(get_theme_mod('prt_social_' . $key, $p['default'] ?? '')); ?>" placeholder="https://">
                                </label>
                            <?php endforeach; ?>
                        </div>
                        <p class="description"><?php esc_html_e('Shown in the footer/menu social icons. Leave any blank to hide it.', 'pressroot'); ?></p>
                    </td>
                </tr>
            </table>

            <?php prt_wizard_nav(1, __('Save & continue to Connections →', 'pressroot')); ?>
        </form>
    </div>
    <?php
}

/** Step 1 save: writes the same stores the rest of the theme reads. */
add_action('admin_post_prt_wizard_save_business', function () {
    if (! current_user_can('edit_theme_options') || ! check_admin_referer('prt_wizard_save_business')) {
        wp_die(__('Not allowed.', 'pressroot'));
    }

    // Identity — mirrored to the brand profile exactly like theme-settings-tab.php.
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

    // Business / website type — remembered for the generation step.
    $siteType = sanitize_key($_POST['prt_wizard_site_type'] ?? '');
    $types    = function_exists('App\\prt_site_types') ? prt_site_types() : [];
    if ($siteType !== '' && isset($types[$siteType])) {
        update_option('prt_wizard_site_type', $siteType, false);
    }

    // Industry (dropdown + "Other…" free text, same convention as Theme Settings).
    if (isset($_POST['prt_brand_industry_sel'])) {
        $industrySel = sanitize_text_field(wp_unslash($_POST['prt_brand_industry_sel']));
        set_theme_mod('prt_brand_industry', $industrySel === '__other'
            ? sanitize_text_field(wp_unslash($_POST['prt_brand_industry_other'] ?? ''))
            : $industrySel);
    }

    // Brand & voice.
    if (isset($_POST['prt_brand_mode'])) {
        $bmode = sanitize_key($_POST['prt_brand_mode']);
        set_theme_mod('prt_brand_mode', in_array($bmode, ['light', 'dark', 'either'], true) ? $bmode : 'either');
    }
    if (isset($_POST['prt_brand_vibe'])) {
        $vibe = sanitize_key($_POST['prt_brand_vibe']);
        set_theme_mod('prt_brand_vibe', in_array($vibe, ['bold', 'minimal', 'warm', 'playful', 'any'], true) ? $vibe : 'any');
    }
    if (isset($_POST['prt_brand_goal'])) {
        $goal = sanitize_key($_POST['prt_brand_goal']);
        set_theme_mod('prt_brand_goal', in_array($goal, ['leads', 'sell', 'book', 'read'], true) ? $goal : 'leads');
    }
    if (isset($_POST['prt_brand_audience'])) {
        set_theme_mod('prt_brand_audience', sanitize_text_field(wp_unslash($_POST['prt_brand_audience'])));
    }
    if (isset($_POST['prt_brand_tone'])) {
        set_theme_mod('prt_brand_tone', sanitize_text_field(wp_unslash($_POST['prt_brand_tone'])));
    }

    // Business facts — new fields, compiled into the core AI brief (see
    // prt_core_ai_instructions() in app/site-type-remix.php).
    foreach (['prt_biz_email' => 'sanitize_email', 'prt_biz_phone' => 'sanitize_text_field'] as $key => $fn) {
        if (isset($_POST[$key])) {
            set_theme_mod($key, call_user_func($fn, wp_unslash($_POST[$key])));
        }
    }
    foreach (['prt_biz_address', 'prt_biz_mission', 'prt_biz_about'] as $key) {
        if (isset($_POST[$key])) {
            set_theme_mod($key, sanitize_textarea_field(wp_unslash($_POST[$key])));
        }
    }
    if (isset($_POST['prt_biz_hours']) && is_array($_POST['prt_biz_hours'])) {
        $hours = [];
        foreach (prt_wizard_days() as $key => $label) {
            $v = sanitize_text_field(wp_unslash($_POST['prt_biz_hours'][$key] ?? ''));
            if ($v !== '') {
                $hours[$key] = $v;
            }
        }
        set_theme_mod('prt_biz_hours', $hours);
    }

    // Colors + fonts (same mods the Customizer/kits write).
    foreach (['prt_color_action', 'prt_color_paper', 'prt_color_ink', 'prt_color_body'] as $modKey) {
        $v = sanitize_hex_color(wp_unslash($_POST[$modKey] ?? ''));
        if ($v) {
            set_theme_mod($modKey, $v);
            if ($modKey === 'prt_color_action') {
                set_theme_mod('prt_brand_color', $v);
            }
        }
    }
    $fonts = function_exists('App\\prt_fonts') ? prt_fonts() : [];
    foreach (['prt_font_heading', 'prt_font_body'] as $modKey) {
        $v = sanitize_text_field(wp_unslash($_POST[$modKey] ?? ''));
        if ($v !== '' && isset($fonts[$v])) {
            set_theme_mod($modKey, $v);
        }
    }

    // Social profile URLs — same prt_social_{key} mods the Customizer edits.
    if (isset($_POST['prt_social']) && is_array($_POST['prt_social'])) {
        $platforms = function_exists('App\\prt_social_platforms') ? prt_social_platforms() : [];
        foreach ($platforms as $key => $p) {
            if (isset($_POST['prt_social'][$key])) {
                set_theme_mod('prt_social_' . $key, esc_url_raw(wp_unslash($_POST['prt_social'][$key])));
            }
        }
    }

    // Uploads: logo + owner media, straight into the Media Library.
    require_once ABSPATH . 'wp-admin/includes/file.php';
    require_once ABSPATH . 'wp-admin/includes/media.php';
    require_once ABSPATH . 'wp-admin/includes/image.php';

    if (! empty($_FILES['prt_wiz_logo']['name'])) {
        $logoId = media_handle_upload('prt_wiz_logo', 0);
        if (! is_wp_error($logoId)) {
            set_theme_mod('custom_logo', (int) $logoId);
            $url = wp_get_attachment_url((int) $logoId);
            if ($url) {
                set_theme_mod('prt_seo_logo', $url); // structured-data logo follows the real logo
            }
        }
    }
    if (! empty($_FILES['prt_wiz_media']['name'][0])) {
        // media_handle_upload() expects one file per key: split the multi-file
        // array into individual $_FILES entries and sideload each.
        $batch = $_FILES['prt_wiz_media'];
        foreach ((array) $batch['name'] as $i => $fname) {
            if ((int) ($batch['error'][$i] ?? UPLOAD_ERR_NO_FILE) !== UPLOAD_ERR_OK) {
                continue;
            }
            $_FILES['prt_wiz_media_single'] = [
                'name'     => $batch['name'][$i],
                'type'     => $batch['type'][$i],
                'tmp_name' => $batch['tmp_name'][$i],
                'error'    => $batch['error'][$i],
                'size'     => $batch['size'][$i],
            ];
            media_handle_upload('prt_wiz_media_single', 0);
        }
        unset($_FILES['prt_wiz_media_single']);
    }

    // Recompile the AI brief so everything above reaches every future AI call.
    if (function_exists('App\\prt_rebuild_core_instructions')) {
        prt_rebuild_core_instructions();
    }

    prt_wizard_mark_done(1);
    wp_safe_redirect(prt_wizard_url(2, ['prt_wiz_saved' => '1']));
    exit;
});

/* ──────────────────────────────────────────────────────────────────────────
 * Step 2 — Connections: AI APIs, SEO, Google Analytics, addons
 * ────────────────────────────────────────────────────────────────────── */

/**
 * The SEO plugin catalog for the selector cards. `builtin` maps to the
 * theme's own lightweight SEO layer (app/seo.php), which already defers
 * automatically the moment any of the three plugins activates.
 */
function prt_wizard_seo_plugins(): array
{
    return [
        'builtin' => [
            'label' => __('Pressroot built-in SEO', 'pressroot'),
            'desc'  => __('Already on: Open Graph/Twitter tags + JSON-LD structured data, zero setup, zero extra weight. Great default for most small-business sites.', 'pressroot'),
            'slug'  => '',
            'file'  => '',
        ],
        'yoast' => [
            'label' => __('Yoast SEO', 'pressroot'),
            'desc'  => __('The most popular SEO plugin. Traffic-light content analysis, readability checks, XML sitemaps, redirects (premium).', 'pressroot'),
            'slug'  => 'wordpress-seo',
            'file'  => 'wordpress-seo/wp-seo.php',
        ],
        'rankmath' => [
            'label' => __('Rank Math', 'pressroot'),
            'desc'  => __('Feature-rich alternative: keyword tracking, schema builder, and Google index monitoring — a generous free tier.', 'pressroot'),
            'slug'  => 'seo-by-rank-math',
            'file'  => 'seo-by-rank-math/rank-math.php',
        ],
        'aioseo' => [
            'label' => __('All in One SEO', 'pressroot'),
            'desc'  => __('Long-standing all-rounder: TruSEO page analysis, smart sitemaps, local business SEO modules.', 'pressroot'),
            'slug'  => 'all-in-one-seo-pack',
            'file'  => 'all-in-one-seo-pack/all_in_one_seo_pack.php',
        ],
    ];
}

/** Installed/active status for one catalog entry. */
function prt_wizard_plugin_status(array $def): string
{
    if ($def['file'] === '') {
        return 'builtin';
    }
    if (! function_exists('is_plugin_active')) {
        include_once ABSPATH . 'wp-admin/includes/plugin.php';
    }
    if (is_plugin_active($def['file'])) {
        return 'active';
    }
    return file_exists(WP_PLUGIN_DIR . '/' . $def['file']) ? 'installed' : 'missing';
}

function prt_wizard_step_connect(): void
{
    $ga        = (string) get_theme_mod('prt_ga4_id', '');
    $seoChoice = (string) get_theme_mod('prt_seo_choice', 'builtin');
    $aiOn      = function_exists('App\\prt_ai_features_enabled') && prt_ai_features_enabled();
    ?>
    <div class="prt-rf-card prt-wiz-card">
        <h3 style="margin-top:0">2 · <?php esc_html_e('Connect your services', 'pressroot'); ?></h3>
        <p class="description"><?php esc_html_e('All optional. AI writing works out of the box with no key (Pollinations); SEO works out of the box with the built-in layer; Analytics only needs a paste-in ID. Connect more when you want more.', 'pressroot'); ?></p>

        <?php prt_wizard_saving_bar('prt-wiz-bar-connect', __('Saving your connections…', 'pressroot'), [
            __('Storing API keys (server-side only)', 'pressroot'),
            __('Applying your SEO choice', 'pressroot'),
            __('Wiring up Google Analytics', 'pressroot'),
        ]); ?>
        <form method="post" action="<?php echo esc_url(admin_url('admin-post.php')); ?>" onsubmit="return prtWizStart('prt-wiz-bar-connect')">
            <input type="hidden" name="action" value="prt_wizard_save_connect">
            <?php wp_nonce_field('prt_wizard_save_connect'); ?>

            <h4>🤖 <?php esc_html_e('AI providers', 'pressroot'); ?></h4>
            <p class="description" style="max-width:640px"><?php esc_html_e('Pick where AI text and images come from. Pollinations is free and keyless — already connected. Add any key below to unlock stronger models; keys stay server-side and are never sent to the browser.', 'pressroot'); ?></p>
            <p class="description" style="max-width:640px"><em><?php esc_html_e('Privacy note: generating text or images sends your business details and prompts to the selected provider (Pollinations by default, which needs no account — treat prompts as public). Nothing is sent until you run a generate action, and the master AI switch in Theme Settings stops every call. See docs/THIRD-PARTY-SERVICES.md for the full inventory.', 'pressroot'); ?></em></p>
            <?php if ($aiOn && function_exists('App\\prt_ai_connectors_defs')) : ?>
                <table class="form-table" role="presentation">
                    <?php foreach (prt_ai_connectors_defs() as $slug => $def) :
                        if (empty($def['needs_key'])) {
                            continue;
                        }
                        $configured = function_exists('App\\prt_ai_is_configured') && prt_ai_is_configured($slug);
                    ?>
                        <tr>
                            <th scope="row">
                                <?php echo esc_html($def['label']); ?>
                                <?php if ($configured) : ?>
                                    <span class="prt-wiz-badge is-ok"><?php esc_html_e('Connected', 'pressroot'); ?></span>
                                <?php endif; ?>
                            </th>
                            <td>
                                <p class="description" style="margin:0 0 6px">
                                    <?php echo esc_html($def['note'] ?? ''); ?>
                                    <?php if (! empty($def['docs_url'])) : ?>
                                        <a href="<?php echo esc_url($def['docs_url']); ?>" target="_blank" rel="noopener noreferrer"><?php esc_html_e('Get a free key ↗', 'pressroot'); ?></a>
                                    <?php endif; ?>
                                </p>
                                <input type="password" class="regular-text" autocomplete="off" name="prt_ai_key_<?php echo esc_attr($slug); ?>" value="" placeholder="<?php echo $configured ? esc_attr(str_repeat('•', 12)) : esc_attr__('Paste API key…', 'pressroot'); ?>">
                                <?php if ($configured) : ?>
                                    <p class="description" style="margin:4px 0 0"><?php esc_html_e('Leave blank to keep the saved key.', 'pressroot'); ?></p>
                                <?php endif; ?>
                            </td>
                        </tr>
                    <?php endforeach; ?>
                </table>
                <p class="description"><?php printf(
                    /* translators: %s: link to the AI Models tab */
                    esc_html__('Model choices and image/video providers live on the %s tab.', 'pressroot'),
                    '<a href="' . esc_url(prt_settings_tab_url('models')) . '">' . esc_html__('AI Models', 'pressroot') . '</a>'
                ); ?></p>
            <?php else : ?>
                <p class="description"><?php esc_html_e('AI features are switched off (Theme Settings → “Let AI help with writing and images”). Turn them on to connect providers — or continue without AI; the design generator works either way.', 'pressroot'); ?></p>
            <?php endif; ?>

            <h4>🔍 <?php esc_html_e('SEO', 'pressroot'); ?></h4>
            <details class="prt-wiz-guide">
                <summary><?php esc_html_e('New to SEO? Read this first (2 minutes)', 'pressroot'); ?></summary>
                <div>
                    <p><strong><?php esc_html_e('What SEO is:', 'pressroot'); ?></strong> <?php esc_html_e('Search Engine Optimization is making your site easy for Google to understand, so it shows up when people search for what you do. It is not a trick — it is clear titles, descriptive text, fast pages, and telling Google what your business is.', 'pressroot'); ?></p>
                    <p><strong><?php esc_html_e('What matters most for a new site:', 'pressroot'); ?></strong></p>
                    <ol>
                        <li><?php esc_html_e('A clear site title and tagline (done in step 1).', 'pressroot'); ?></li>
                        <li><?php esc_html_e('Pages named what customers actually search for (“Plumbing repair in Gettysburg”, not “What we offer”).', 'pressroot'); ?></li>
                        <li><?php esc_html_e('A Google Business Profile — for local businesses this beats everything else (guide below, under Analytics).', 'pressroot'); ?></li>
                        <li><?php esc_html_e('Structured data — code that tells Google your name, logo, and social profiles. Pressroot outputs this automatically.', 'pressroot'); ?></li>
                        <li><?php esc_html_e('Patience: new sites take weeks to rank. Publish honest, useful pages and keep going.', 'pressroot'); ?></li>
                    </ol>
                    <p><?php esc_html_e('An SEO plugin adds tools on top (content analysis, sitemaps, redirects). Start with the built-in layer; add a plugin when you start writing lots of content.', 'pressroot'); ?></p>
                </div>
            </details>
            <div class="prt-wiz-seo-grid">
                <?php foreach (prt_wizard_seo_plugins() as $key => $def) :
                    $status = prt_wizard_plugin_status($def);
                ?>
                    <label class="prt-wiz-seo-card <?php echo $seoChoice === $key ? 'is-selected' : ''; ?>">
                        <input type="radio" name="prt_seo_choice" value="<?php echo esc_attr($key); ?>" <?php checked($seoChoice, $key); ?>>
                        <strong><?php echo esc_html($def['label']); ?></strong>
                        <?php if ($status === 'active') : ?>
                            <span class="prt-wiz-badge is-ok"><?php esc_html_e('Active', 'pressroot'); ?></span>
                        <?php elseif ($status === 'installed') : ?>
                            <span class="prt-wiz-badge"><?php esc_html_e('Installed, not active', 'pressroot'); ?></span>
                        <?php elseif ($status === 'builtin') : ?>
                            <span class="prt-wiz-badge is-ok"><?php esc_html_e('Always on', 'pressroot'); ?></span>
                        <?php endif; ?>
                        <p><?php echo esc_html($def['desc']); ?></p>
                        <?php if ($status === 'missing' && current_user_can('install_plugins')) : ?>
                            <a class="button" href="<?php echo esc_url(wp_nonce_url(self_admin_url('update.php?action=install-plugin&plugin=' . $def['slug']), 'install-plugin_' . $def['slug'])); ?>"><?php esc_html_e('Install', 'pressroot'); ?></a>
                        <?php elseif ($status === 'installed' && current_user_can('activate_plugins')) : ?>
                            <a class="button" href="<?php echo esc_url(wp_nonce_url(self_admin_url('plugins.php?action=activate&plugin=' . $def['file']), 'activate-plugin_' . $def['file'])); ?>"><?php esc_html_e('Activate', 'pressroot'); ?></a>
                        <?php endif; ?>
                    </label>
                <?php endforeach; ?>
            </div>
            <p class="description"><?php esc_html_e('Pressroot detects whichever plugin you activate and steps its own SEO output aside automatically — no double meta tags, ever.', 'pressroot'); ?></p>

            <h4>📈 <?php esc_html_e('Google Analytics', 'pressroot'); ?></h4>
            <table class="form-table" role="presentation">
                <tr>
                    <th scope="row"><label for="prt_ga4_id"><?php esc_html_e('GA4 Measurement ID', 'pressroot'); ?></label></th>
                    <td>
                        <input type="text" id="prt_ga4_id" name="prt_ga4_id" class="regular-text" value="<?php echo esc_attr($ga); ?>" placeholder="G-XXXXXXXXXX" pattern="[Gg]-[A-Za-z0-9]{4,16}">
                        <p class="description"><?php esc_html_e('Paste it and Pressroot injects the official gtag.js snippet on every page — nothing else to configure. Clear the field to remove tracking.', 'pressroot'); ?></p>
                    </td>
                </tr>
            </table>
            <details class="prt-wiz-guide">
                <summary><?php esc_html_e('Walkthrough: set up Google Analytics (free, ~5 minutes)', 'pressroot'); ?></summary>
                <div>
                    <ol>
                        <li><?php printf(wp_kses(__('Go to <a href="%s" target="_blank" rel="noopener noreferrer">analytics.google.com</a> and sign in with any Google account.', 'pressroot'), ['a' => ['href' => [], 'target' => [], 'rel' => []]]), 'https://analytics.google.com/'); ?></li>
                        <li><?php esc_html_e('Click “Start measuring”. Name the account after your business.', 'pressroot'); ?></li>
                        <li><?php esc_html_e('Create a Property — this represents your website. Set your time zone and currency.', 'pressroot'); ?></li>
                        <li><?php esc_html_e('Choose “Web” as the platform and enter your site address.', 'pressroot'); ?></li>
                        <li><?php esc_html_e('Google shows a Measurement ID that starts with “G-”. Copy it.', 'pressroot'); ?></li>
                        <li><?php esc_html_e('Paste it in the field above and save this step. Done — visits appear in your Analytics dashboard within a day.', 'pressroot'); ?></li>
                    </ol>
                </div>
            </details>
            <details class="prt-wiz-guide">
                <summary><?php esc_html_e('Walkthrough: register your business on Google (Business Profile)', 'pressroot'); ?></summary>
                <div>
                    <p><?php esc_html_e('For local businesses this is the single highest-impact free marketing step — it puts you on Google Maps and in the local results box.', 'pressroot'); ?></p>
                    <ol>
                        <li><?php printf(wp_kses(__('Go to <a href="%s" target="_blank" rel="noopener noreferrer">google.com/business</a> and click “Manage now”.', 'pressroot'), ['a' => ['href' => [], 'target' => [], 'rel' => []]]), 'https://www.google.com/business/'); ?></li>
                        <li><?php esc_html_e('Enter your business name and category (use the industry you chose in step 1).', 'pressroot'); ?></li>
                        <li><?php esc_html_e('Add your address — or your service area if you work at customers’ locations.', 'pressroot'); ?></li>
                        <li><?php esc_html_e('Add the phone number and website address (your new site!).', 'pressroot'); ?></li>
                        <li><?php esc_html_e('Verify ownership — Google mails a postcard with a code, or offers phone/email for some businesses.', 'pressroot'); ?></li>
                        <li><?php esc_html_e('Once verified: add photos, your business hours (step 1 has them), and ask happy customers for reviews.', 'pressroot'); ?></li>
                    </ol>
                </div>
            </details>

            <h4>🧩 <?php esc_html_e('Business addons', 'pressroot'); ?></h4>
            <table class="form-table" role="presentation">
                <tr>
                    <th scope="row"><?php esc_html_e('Theme addons', 'pressroot'); ?></th>
                    <td>
                        <label style="display:block;margin-bottom:6px"><input type="checkbox" name="prt_addon_pressroot_ai_enabled" value="1" <?php checked((bool) get_theme_mod('prt_addon_pressroot_ai_enabled', true)); ?>> <?php esc_html_e('Pressroot AI (site types, generator, AI tools)', 'pressroot'); ?></label>
                        <label style="display:block;margin-bottom:6px"><input type="checkbox" name="prt_addon_repofolio_enabled" value="1" <?php checked((bool) get_theme_mod('prt_addon_repofolio_enabled', true)); ?>> <?php printf(
                            /* translators: %s: link to the GitHub tab */
                            esc_html__('Repofolio — GitHub portfolio addon (connect your account on the %s tab)', 'pressroot'),
                            '<a href="' . esc_url(prt_settings_tab_url('github')) . '">' . esc_html__('GitHub', 'pressroot') . '</a>'
                        ); ?></label>
                        <label style="display:block"><input type="checkbox" name="prt_addon_bookings_enabled" value="1" <?php checked((bool) get_theme_mod('prt_addon_bookings_enabled', prt_addon_defaults()['bookings'] ?? true)); ?>> <?php
                            esc_html_e('Pressroots Reserve — take reservations & bookings (restaurants, hotels, meetings): a booking form block plus an admin calendar', 'pressroot');
                        ?></label>
                        <p class="description" style="margin:6px 0 0"><?php
                            printf(
                                /* translators: %s: link to the Bookings settings tab */
                                esc_html__('Turn this on to add bookable services and set your availability under Bookings. Configure it on the %s tab.', 'pressroot'),
                                '<a href="' . esc_url(prt_settings_tab_url('bookings')) . '">' . esc_html__('Bookings', 'pressroot') . '</a>'
                            );
                        ?></p>
                    </td>
                </tr>
            </table>

            <?php prt_wizard_nav(2, __('Save & continue to WordPress settings →', 'pressroot')); ?>
        </form>
    </div>
    <?php
}

/** Step 2 save. manage_options because it stores API keys, matching ai-connectors.php. */
add_action('admin_post_prt_wizard_save_connect', function () {
    if (! current_user_can('manage_options') || ! check_admin_referer('prt_wizard_save_connect')) {
        wp_die(__('Not allowed.', 'pressroot'));
    }

    // AI keys — same storage convention as the connectors save handler:
    // blank submissions keep the stored key.
    if (function_exists('App\\prt_ai_all_connector_defs')) {
        foreach (prt_ai_all_connector_defs() as $slug => $def) {
            if (empty($def['needs_key'])) {
                continue;
            }
            $field  = 'prt_ai_key_' . $slug;
            $posted = isset($_POST[$field]) ? trim(sanitize_text_field(wp_unslash($_POST[$field]))) : '';
            if ($posted !== '') {
                set_theme_mod($field, $posted);
            }
        }
    }

    // SEO plugin choice.
    $choice = sanitize_key($_POST['prt_seo_choice'] ?? 'builtin');
    if (isset(prt_wizard_seo_plugins()[$choice])) {
        set_theme_mod('prt_seo_choice', $choice);
    }

    // GA4 Measurement ID — validated, uppercased, empty clears it.
    $ga = strtoupper(trim(sanitize_text_field(wp_unslash($_POST['prt_ga4_id'] ?? ''))));
    if ($ga === '' || preg_match('/^G-[A-Z0-9]{4,16}$/', $ga)) {
        set_theme_mod('prt_ga4_id', $ga);
    } else {
        wp_safe_redirect(prt_wizard_url(2, ['prt_wiz_error' => rawurlencode(__('That Measurement ID doesn’t look right — it starts with “G-”, e.g. G-AB12CD34EF. Everything else was saved.', 'pressroot'))]));
        exit;
    }

    // Addons.
    set_theme_mod('prt_addon_pressroot_ai_enabled', ! empty($_POST['prt_addon_pressroot_ai_enabled']));
    set_theme_mod('prt_addon_repofolio_enabled', ! empty($_POST['prt_addon_repofolio_enabled']));
    set_theme_mod('prt_addon_bookings_enabled', ! empty($_POST['prt_addon_bookings_enabled']));

    prt_wizard_mark_done(2);
    wp_safe_redirect(prt_wizard_url(3, ['prt_wiz_saved' => '1']));
    exit;
});

/**
 * GA4 tracking snippet — the official gtag.js loader, printed early in
 * <head> per Google's instructions. Skipped when the same ID already
 * appears in the manual head-code field (app/integrations.php), so setting
 * both never double-counts visitors.
 */
add_action('wp_head', function () {
    $id = (string) get_theme_mod('prt_ga4_id', '');
    if ($id === '' || ! preg_match('/^G-[A-Z0-9]{4,16}$/', $id)) {
        return;
    }
    if (stripos((string) get_theme_mod('prt_code_head', ''), $id) !== false) {
        return; // already injected by hand
    }
    echo "<script async src=\"https://www.googletagmanager.com/gtag/js?id=" . esc_attr($id) . "\"></script>\n";
    echo "<script>window.dataLayer=window.dataLayer||[];function gtag(){dataLayer.push(arguments);}gtag('js',new Date());gtag('config','" . esc_js($id) . "');</script>\n";
}, 4);

/* ──────────────────────────────────────────────────────────────────────────
 * Step 3 — WordPress settings walkthrough + one-click automation
 * ────────────────────────────────────────────────────────────────────── */

function prt_wizard_step_wpsettings(): void
{
    $tz        = (string) get_option('timezone_string');
    $permalink = (string) get_option('permalink_structure');
    $iconId    = (int) get_option('site_icon');
    ?>
    <div class="prt-rf-card prt-wiz-card">
        <h3 style="margin-top:0">3 · <?php esc_html_e('WordPress settings, handled', 'pressroot'); ?></h3>
        <p class="description"><?php esc_html_e('WordPress has a handful of core settings every site should get right. This screen explains each one, shows your current value, and applies the recommended setup in one click — no hunting through Settings screens.', 'pressroot'); ?></p>

        <?php prt_wizard_saving_bar('prt-wiz-bar-wp', __('Applying WordPress settings…', 'pressroot'), [
            __('Setting timezone & defaults', 'pressroot'),
            __('Rebuilding permalink rules', 'pressroot'),
            __('Saving your site icon', 'pressroot'),
        ]); ?>
        <form method="post" action="<?php echo esc_url(admin_url('admin-post.php')); ?>" enctype="multipart/form-data" onsubmit="return prtWizStart('prt-wiz-bar-wp')">
            <input type="hidden" name="action" value="prt_wizard_save_wpsettings">
            <?php wp_nonce_field('prt_wizard_save_wpsettings'); ?>

            <table class="form-table" role="presentation">
                <tr>
                    <th scope="row"><label for="prt_wiz_tz"><?php esc_html_e('Timezone', 'pressroot'); ?></label></th>
                    <td>
                        <select id="prt_wiz_tz" name="timezone_string">
                            <?php echo wp_timezone_choice($tz ?: 'America/New_York', get_user_locale()); // phpcs:ignore -- core helper outputs safe <option> markup ?>
                        </select>
                        <p class="description"><?php esc_html_e('Controls when scheduled posts publish and how dates display. (WP Settings → General)', 'pressroot'); ?></p>
                    </td>
                </tr>
                <tr>
                    <th scope="row"><?php esc_html_e('Pretty permalinks', 'pressroot'); ?></th>
                    <td>
                        <label><input type="checkbox" name="prt_wiz_permalinks" value="1" <?php checked($permalink === '' || $permalink === '/?p=%post_id%' || strpos($permalink, 'postname') !== false); ?>>
                            <?php esc_html_e('Use “/%postname%/” URLs (recommended)', 'pressroot'); ?></label>
                        <p class="description">
                            <?php printf(
                                /* translators: %s: current permalink structure or "plain" */
                                esc_html__('Readable URLs like /services/ instead of /?p=123 — better for visitors AND search engines. Current: %s (WP Settings → Permalinks)', 'pressroot'),
                                '<code>' . esc_html($permalink !== '' ? $permalink : __('plain', 'pressroot')) . '</code>'
                            ); ?>
                        </p>
                    </td>
                </tr>
                <tr>
                    <th scope="row"><label for="prt_wiz_icon"><?php esc_html_e('Site icon (favicon)', 'pressroot'); ?></label></th>
                    <td>
                        <?php if ($iconId) : ?>
                            <p style="margin:0 0 8px"><?php echo wp_get_attachment_image($iconId, [32, 32]); ?> <span class="prt-wiz-badge is-ok"><?php esc_html_e('Set', 'pressroot'); ?></span></p>
                        <?php endif; ?>
                        <input type="file" id="prt_wiz_icon" name="prt_wiz_icon" accept="image/png,image/jpeg,image/webp">
                        <p class="description"><?php esc_html_e('The little square icon in browser tabs and bookmarks. Square image, 512×512 or larger. (WP Settings → General)', 'pressroot'); ?></p>
                    </td>
                </tr>
                <tr>
                    <th scope="row"><?php esc_html_e('Search engine visibility', 'pressroot'); ?></th>
                    <td>
                        <label><input type="checkbox" name="prt_wiz_hide_until_launch" value="1" <?php checked(! get_option('blog_public')); ?>>
                            <?php esc_html_e('Stay hidden from search engines while I build (recommended)', 'pressroot'); ?></label>
                        <p class="description"><?php esc_html_e('Step 6 flips this back on when you launch, so Google never indexes a half-finished site. (WP Settings → Reading)', 'pressroot'); ?></p>
                    </td>
                </tr>
                <tr>
                    <th scope="row"><?php esc_html_e('Comments on pages', 'pressroot'); ?></th>
                    <td>
                        <label><input type="checkbox" name="prt_wiz_close_comments" value="1" <?php checked(get_option('default_comment_status') !== 'open'); ?>>
                            <?php esc_html_e('Turn comments off for new pages/posts (recommended for business sites)', 'pressroot'); ?></label>
                        <p class="description"><?php esc_html_e('Stops spam bots targeting brochure pages. Blogs that want discussion can leave this unchecked. (WP Settings → Discussion)', 'pressroot'); ?></p>
                    </td>
                </tr>
            </table>

            <details class="prt-wiz-guide">
                <summary><?php esc_html_e('What about the rest of the Settings screens?', 'pressroot'); ?></summary>
                <div>
                    <ul>
                        <li><?php printf(wp_kses(__('<a href="%s">General</a> — site title & tagline (step 1 already set these), admin email, timezone.', 'pressroot'), ['a' => ['href' => []]]), esc_url(admin_url('options-general.php'))); ?></li>
                        <li><?php printf(wp_kses(__('<a href="%s">Reading</a> — homepage display. The generator sets your front page automatically in step 5.', 'pressroot'), ['a' => ['href' => []]]), esc_url(admin_url('options-reading.php'))); ?></li>
                        <li><?php printf(wp_kses(__('<a href="%s">Permalinks</a> — URL structure, handled above.', 'pressroot'), ['a' => ['href' => []]]), esc_url(admin_url('options-permalink.php'))); ?></li>
                        <li><?php printf(wp_kses(__('<a href="%s">Discussion</a> — comment rules, handled above.', 'pressroot'), ['a' => ['href' => []]]), esc_url(admin_url('options-discussion.php'))); ?></li>
                        <li><?php printf(wp_kses(__('<a href="%s">Media</a> and <a href="%s">Writing</a> — the defaults are fine for almost everyone.', 'pressroot'), ['a' => ['href' => []]]), esc_url(admin_url('options-media.php')), esc_url(admin_url('options-writing.php'))); ?></li>
                    </ul>
                </div>
            </details>

            <?php prt_wizard_nav(3, __('Apply & continue to Generate →', 'pressroot')); ?>
        </form>
    </div>
    <?php
}

/** Step 3 save: applies the chosen WordPress settings. */
add_action('admin_post_prt_wizard_save_wpsettings', function () {
    if (! current_user_can('manage_options') || ! check_admin_referer('prt_wizard_save_wpsettings')) {
        wp_die(__('Not allowed.', 'pressroot'));
    }

    // Timezone — only accept a real identifier from the dropdown.
    $tz = sanitize_text_field(wp_unslash($_POST['timezone_string'] ?? ''));
    if ($tz !== '' && in_array($tz, timezone_identifiers_list(), true)) {
        update_option('timezone_string', $tz);
        update_option('gmt_offset', ''); // identifier wins; clear any manual offset
    }

    // Pretty permalinks.
    if (! empty($_POST['prt_wiz_permalinks'])) {
        global $wp_rewrite;
        $wp_rewrite->set_permalink_structure('/%postname%/');
        flush_rewrite_rules();
    }

    // Site icon upload.
    if (! empty($_FILES['prt_wiz_icon']['name'])) {
        require_once ABSPATH . 'wp-admin/includes/file.php';
        require_once ABSPATH . 'wp-admin/includes/media.php';
        require_once ABSPATH . 'wp-admin/includes/image.php';
        $iconId = media_handle_upload('prt_wiz_icon', 0);
        if (! is_wp_error($iconId)) {
            update_option('site_icon', (int) $iconId);
        }
    }

    // Search visibility: hidden while building (step 6 re-enables at launch).
    update_option('blog_public', empty($_POST['prt_wiz_hide_until_launch']) ? 1 : 0);

    // Comments default.
    if (! empty($_POST['prt_wiz_close_comments'])) {
        update_option('default_comment_status', 'closed');
        update_option('default_ping_status', 'closed');
    } else {
        update_option('default_comment_status', 'open');
    }

    prt_wizard_mark_done(3);
    wp_safe_redirect(prt_wizard_url(4, ['prt_wiz_saved' => '1']));
    exit;
});

/* ──────────────────────────────────────────────────────────────────────────
 * Step 4 — Design (header / hero / footer designer)
 * ────────────────────────────────────────────────────────────────────── */

/**
 * The header & footer designer as a wizard step. The actual fields, presets,
 * and save handler live in app/design-presets.php (shared with the
 * standalone "Header & Footer" settings tab); this wrapper just frames them
 * in wizard chrome and routes the save through the step-completion flow via
 * the hidden prt_wizard_step field.
 */
function prt_wizard_step_design(): void
{
    wp_enqueue_media(); // hero image picker
    ?>
    <div class="prt-rf-card">
        <h3 style="margin-top:0">🎨 <?php esc_html_e('Design your header, hero & footer', 'pressroot'); ?></h3>
        <p class="description" style="max-width:680px"><?php esc_html_e('Pick layout presets — single bar, multi-row banner stacks, a centered logo banner, or a transparent nav floating over a full hero image. Every preset keeps text contrast at WCAG AA automatically, and everything stays adjustable later under Appearance → Pressroot → Header & Footer or in the Customizer.', 'pressroot'); ?></p>

        <form method="post" action="<?php echo esc_url(admin_url('admin-post.php')); ?>">
            <input type="hidden" name="action" value="prt_save_design">
            <input type="hidden" name="prt_wizard_step" value="4">
            <?php wp_nonce_field('prt_save_design'); ?>
            <?php if (function_exists('App\\prt_design_designer_fields')) {
                prt_design_designer_fields();
            } ?>
            <?php prt_wizard_nav(4, __('Save design — continue →', 'pressroot')); ?>
        </form>
    </div>
    <?php
}

/* ──────────────────────────────────────────────────────────────────────────
 * Step 5 — Generate the website
 * ────────────────────────────────────────────────────────────────────── */

function prt_wizard_step_generate(): void
{
    $types    = function_exists('App\\prt_site_types') ? prt_site_types() : [];
    $chosen   = (string) get_option('prt_wizard_site_type', '');
    $pages    = function_exists('App\\prt_get_site_type_pages') ? prt_get_site_type_pages() : [];
    $applied  = (bool) $pages;
    $aiOn     = function_exists('App\\prt_ai_features_enabled') && prt_ai_features_enabled();
    $writers  = ($aiOn && function_exists('App\\prt_ai_configured_connectors')) ? prt_ai_configured_connectors() : [];

    // Feedback from the generation handlers (they redirect back here).
    if (isset($_GET['prt_site_type_result'])) {
        echo '<div class="notice notice-success is-dismissible"><p>🏗 ' . esc_html__('Site generated! Pages created as drafts with a full design and sample content — AI-write them below, then continue to Review.', 'pressroot') . '</p></div>';
    }
    if (isset($_GET['prt_ai_filled'])) {
        echo '<div class="notice notice-success is-dismissible"><p>✨ ' . esc_html(sprintf(
            /* translators: %d: number of text segments rewritten */
            __('AI wrote your pages — %d text segments rewritten in your voice.', 'pressroot'),
            absint($_GET['prt_ai_filled'])
        )) . '</p></div>';
    }
    if (isset($_GET['prt_img_done'])) {
        echo '<div class="notice notice-success is-dismissible"><p>🖼 ' . esc_html__('Brand image generated and set as the homepage hero.', 'pressroot') . '</p></div>';
    }
    if (isset($_GET['prt_ai_fill_error']) || isset($_GET['prt_img_error'])) {
        echo '<div class="notice notice-error is-dismissible"><p>' . esc_html(sanitize_text_field(wp_unslash($_GET['prt_ai_fill_error'] ?? $_GET['prt_img_error'] ?? ''))) . '</p></div>';
    }
    ?>
    <div class="prt-rf-card prt-wiz-card">
        <h3 style="margin-top:0">4 · <?php esc_html_e('Generate your website', 'pressroot'); ?></h3>
        <p class="description"><?php esc_html_e('The same stages a design studio would bill you for, automated: discovery (your step-1 answers), art direction (a design kit + trend dealt to match your brand), layout (hand-built page designs), content (sample copy in every element, then AI rewritten in your voice), and imagery. Run each stage below, in order.', 'pressroot'); ?></p>

        <?php if (! $chosen && ! $applied) : ?>
            <div class="notice notice-warning inline"><p><?php printf(
                wp_kses(__('No business type chosen yet — pick one in <a href="%s">step 1</a> first.', 'pressroot'), ['a' => ['href' => []]]),
                esc_url(prt_wizard_url(1))
            ); ?></p></div>
        <?php endif; ?>

        <?php // Stage A: design + pages ?>
        <div class="prt-wiz-stage">
            <h4>🎨 <?php esc_html_e('A. Design & pages', 'pressroot'); ?> <?php if ($applied) : ?><span class="prt-wiz-badge is-ok"><?php echo esc_html(sprintf(__('%d pages generated', 'pressroot'), count($pages))); ?></span><?php endif; ?></h4>
            <p class="description"><?php esc_html_e('Deals a design (palette, fonts, layout trend) from your brand answers and creates every starter page as a draft — each pre-filled with a complete, professionally structured layout and sample content for every element. Run it again anytime for a fresh look; your text survives, the design re-rolls.', 'pressroot'); ?></p>
            <?php prt_wizard_saving_bar('prt-wiz-bar-design', __('Building your site…', 'pressroot'), [
                __('Dealing a design from your brand answers', 'pressroot'),
                __('Creating your pages with sample content', 'pressroot'),
                __('Building navigation, header & footer', 'pressroot'),
                __('Generating smart-block copy', 'pressroot'),
                __('Applying design tokens site-wide', 'pressroot'),
            ]); ?>
            <form method="post" action="<?php echo esc_url(admin_url('admin-post.php')); ?>" style="display:flex;gap:10px;align-items:center;flex-wrap:wrap" onsubmit="return prtWizStart('prt-wiz-bar-design')">
                <input type="hidden" name="action" value="prt_apply_site_type">
                <input type="hidden" name="prt_return_tab" value="setup">
                <input type="hidden" name="prt_return_step" value="5">
                <?php wp_nonce_field('prt_apply_site_type'); ?>
                <select name="site_type" required>
                    <option value=""><?php esc_html_e('— business type —', 'pressroot'); ?></option>
                    <?php foreach ($types as $slug => $t) : ?>
                        <option value="<?php echo esc_attr($slug); ?>" <?php selected($chosen, $slug); ?>><?php echo esc_html($t['label']); ?></option>
                    <?php endforeach; ?>
                </select>
                <button class="button button-primary"><?php echo $applied ? esc_html__('🎲 Regenerate design & pages', 'pressroot') : esc_html__('🏗 Generate my website', 'pressroot'); ?></button>
            </form>
        </div>

        <?php // Stage B: AI copy ?>
        <div class="prt-wiz-stage">
            <h4>✍️ <?php esc_html_e('B. Write the copy with AI', 'pressroot'); ?></h4>
            <p class="description"><?php esc_html_e('Rewrites every sample text segment on the generated pages in your brand voice, using your business facts — never touching the layout. Free with the default model; connected providers (step 2) write noticeably better.', 'pressroot'); ?></p>
            <?php if ($applied && $aiOn) :
                $appliedTypes = array_unique(array_filter(wp_list_pluck($pages, 'prt_site_type')));
            ?>
                <?php prt_wizard_saving_bar('prt-wiz-bar-copy', __('Writing your pages…', 'pressroot'), [
                    __('Sending your brief to the model', 'pressroot'),
                    __('Writing page copy in your voice', 'pressroot'),
                    __('Writing page copy in your voice (still going — models take a minute)', 'pressroot'),
                    __('Placing text back into your layouts', 'pressroot'),
                ]); ?>
                <form method="post" action="<?php echo esc_url(admin_url('admin-post.php')); ?>" style="display:flex;gap:10px;align-items:center;flex-wrap:wrap" onsubmit="return prtWizStart('prt-wiz-bar-copy')">
                    <input type="hidden" name="action" value="prt_ai_fill_all">
                    <input type="hidden" name="site_type" value="<?php echo esc_attr((string) reset($appliedTypes)); ?>">
                    <input type="hidden" name="prt_return_tab" value="setup">
                    <input type="hidden" name="prt_return_step" value="5">
                    <?php wp_nonce_field('prt_ai_fill_all'); ?>
                    <select name="model">
                        <?php foreach ($writers as $slug => $def) : ?>
                            <option value="<?php echo esc_attr($slug); ?>"><?php echo esc_html($def['label']); ?></option>
                        <?php endforeach; ?>
                    </select>
                    <button class="button button-primary">✨ <?php esc_html_e('AI-write all pages', 'pressroot'); ?></button>
                </form>
                <p class="description" style="margin-top:8px"><?php esc_html_e('Prefer to write it yourself? Skip this — every page keeps its sample content, and the editor has a per-block “Generate with AI” toolbar button plus a Pressroot AI panel for one page at a time.', 'pressroot'); ?></p>
            <?php elseif (! $applied) : ?>
                <p class="description"><em><?php esc_html_e('Generate your pages in stage A first.', 'pressroot'); ?></em></p>
            <?php else : ?>
                <p class="description"><em><?php esc_html_e('AI features are off — your pages keep their sample copy, ready to edit by hand.', 'pressroot'); ?></em></p>
            <?php endif; ?>
        </div>

        <?php // Stage C: imagery ?>
        <div class="prt-wiz-stage">
            <h4>🖼 <?php esc_html_e('C. Imagery', 'pressroot'); ?></h4>
            <p class="description"><?php esc_html_e('Generate a brand hero image from your industry + imagery style (free by default, saved to the Media Library) — or use the photos you uploaded in step 1. Per-page AI images are one click each on the Site Types tab.', 'pressroot'); ?></p>
            <?php if ($aiOn) : ?>
                <?php prt_wizard_saving_bar('prt-wiz-bar-image', __('Generating your brand image…', 'pressroot'), [
                    __('Composing an image prompt from your brand', 'pressroot'),
                    __('Generating the image (can take ~30s)', 'pressroot'),
                    __('Saving to your Media Library', 'pressroot'),
                    __('Setting the homepage hero', 'pressroot'),
                ]); ?>
                <form method="post" action="<?php echo esc_url(admin_url('admin-post.php')); ?>" onsubmit="return prtWizStart('prt-wiz-bar-image')">
                    <input type="hidden" name="action" value="prt_ai_brand_image">
                    <input type="hidden" name="prt_return_tab" value="setup">
                    <input type="hidden" name="prt_return_step" value="5">
                    <?php wp_nonce_field('prt_ai_brand_image'); ?>
                    <button class="button button-primary">🖼 <?php esc_html_e('Generate brand image', 'pressroot'); ?></button>
                </form>
            <?php else : ?>
                <p class="description"><em><?php esc_html_e('AI features are off — add your own images via the Media Library instead.', 'pressroot'); ?></em></p>
            <?php endif; ?>
        </div>

        <form method="post" action="<?php echo esc_url(admin_url('admin-post.php')); ?>">
            <input type="hidden" name="action" value="prt_wizard_mark">
            <input type="hidden" name="step" value="5">
            <?php wp_nonce_field('prt_wizard_mark'); ?>
            <?php prt_wizard_nav(5, __('Continue to Review →', 'pressroot')); ?>
        </form>
    </div>
    <?php
}

/* ──────────────────────────────────────────────────────────────────────────
 * Step 6 — Review & fine-tune
 * ────────────────────────────────────────────────────────────────────── */

function prt_wizard_step_review(): void
{
    $pages = function_exists('App\\prt_get_site_type_pages') ? prt_get_site_type_pages() : [];
    ?>
    <div class="prt-rf-card prt-wiz-card">
        <h3 style="margin-top:0">5 · <?php esc_html_e('Review your site', 'pressroot'); ?></h3>
        <p class="description"><?php esc_html_e('Look at every page like a customer would. Anything you want to change has one obvious place to change it — the map below tells you where.', 'pressroot'); ?></p>

        <h4><?php esc_html_e('Your homepage', 'pressroot'); ?></h4>
        <div class="prt-wiz-frame-wrap">
            <iframe class="prt-preview-frame" src="<?php echo esc_url(home_url('/')); ?>" title="<?php esc_attr_e('Homepage preview', 'pressroot'); ?>" loading="lazy"></iframe>
        </div>
        <p><a class="button" href="<?php echo esc_url(home_url('/')); ?>" target="_blank" rel="noopener noreferrer"><?php esc_html_e('Open full-size ↗', 'pressroot'); ?></a></p>

        <h4><?php esc_html_e('Your pages', 'pressroot'); ?></h4>
        <?php if ($pages) : ?>
            <table class="widefat striped" style="max-width:820px">
                <thead><tr>
                    <th><?php esc_html_e('Page', 'pressroot'); ?></th>
                    <th><?php esc_html_e('Status', 'pressroot'); ?></th>
                    <th><?php esc_html_e('Preview', 'pressroot'); ?></th>
                    <th><?php esc_html_e('Edit', 'pressroot'); ?></th>
                </tr></thead>
                <tbody>
                    <?php foreach ($pages as $p) :
                        $isDraft = $p->post_status !== 'publish';
                        $preview = $isDraft ? get_preview_post_link($p) : get_permalink($p);
                    ?>
                        <tr>
                            <td><strong><?php echo esc_html(get_the_title($p)); ?></strong> <span class="description">(<?php echo esc_html($p->prt_role); ?>)</span></td>
                            <td><?php echo $isDraft
                                ? '<span class="prt-wiz-badge">' . esc_html__('Draft — publishes at launch', 'pressroot') . '</span>'
                                : '<span class="prt-wiz-badge is-ok">' . esc_html__('Published', 'pressroot') . '</span>'; ?></td>
                            <td><?php if ($preview) : ?><a href="<?php echo esc_url($preview); ?>" target="_blank" rel="noopener noreferrer"><?php esc_html_e('Preview ↗', 'pressroot'); ?></a><?php endif; ?></td>
                            <td><a href="<?php echo esc_url((string) get_edit_post_link($p->ID)); ?>"><?php esc_html_e('Edit page', 'pressroot'); ?></a></td>
                        </tr>
                    <?php endforeach; ?>
                </tbody>
            </table>
        <?php else : ?>
            <p class="description"><em><?php printf(
                wp_kses(__('No generated pages yet — run <a href="%s">step 5</a> first.', 'pressroot'), ['a' => ['href' => []]]),
                esc_url(prt_wizard_url(4))
            ); ?></em></p>
        <?php endif; ?>

        <h4><?php esc_html_e('Want to change something? Here’s where', 'pressroot'); ?></h4>
        <div class="prt-wiz-map">
            <div>
                <strong>📝 <?php esc_html_e('Words', 'pressroot'); ?></strong>
                <p><?php esc_html_e('Click “Edit page”, click any text, and type over the sample content with your own — or select a block and hit its ✨ “Generate with AI” toolbar button to redraft just that piece.', 'pressroot'); ?></p>
            </div>
            <div>
                <strong>🖼 <?php esc_html_e('Images', 'pressroot'); ?></strong>
                <p><?php printf(
                    wp_kses(__('In the editor, click an image → “Replace” to swap in your own from the <a href="%s">Media Library</a> (your step-1 uploads are there). Per-page AI images: the ✨ buttons on the Site Types tab.', 'pressroot'), ['a' => ['href' => []]]),
                    esc_url(admin_url('upload.php'))
                ); ?></p>
            </div>
            <div>
                <strong>🎨 <?php esc_html_e('Whole design', 'pressroot'); ?></strong>
                <p><?php printf(
                    wp_kses(__('Don’t tweak — re-deal. <a href="%s">Step 5</a> regenerates the entire look; the <a href="%s">Site Types</a> tab re-rolls single pages (🎲). Colors & fonts: step 1 or Theme Settings.', 'pressroot'), ['a' => ['href' => []]]),
                    esc_url(prt_wizard_url(4)),
                    esc_url(prt_settings_tab_url('ai'))
                ); ?></p>
            </div>
            <div>
                <strong>🧭 <?php esc_html_e('Header, menu & footer', 'pressroot'); ?></strong>
                <p><?php printf(
                    wp_kses(__('Pick layout presets in the <a href="%s">Header & Footer designer</a> (step 4), or fine-tune with live preview in the <a href="%s">Customizer</a> (Header layout, Navigation, Footer builder sections).', 'pressroot'), ['a' => ['href' => []]]),
                    esc_url(prt_wizard_url(4)),
                    esc_url(admin_url('customize.php'))
                ); ?></p>
            </div>
        </div>

        <form method="post" action="<?php echo esc_url(admin_url('admin-post.php')); ?>">
            <input type="hidden" name="action" value="prt_wizard_mark">
            <input type="hidden" name="step" value="6">
            <?php wp_nonce_field('prt_wizard_mark'); ?>
            <?php prt_wizard_nav(6, __('Looks good — continue to Launch →', 'pressroot')); ?>
        </form>
    </div>
    <?php
}

/* ──────────────────────────────────────────────────────────────────────────
 * Step 7 — Launch
 * ────────────────────────────────────────────────────────────────────── */

/** The pre-flight checklist rows: [ok, label, fix-URL or '']. */
function prt_wizard_checklist(): array
{
    $pages   = function_exists('App\\prt_get_site_type_pages') ? prt_get_site_type_pages() : [];
    $drafts  = array_filter($pages, fn ($p) => $p->post_status !== 'publish');
    $menus   = wp_get_nav_menus();

    return [
        [
            'ok'    => get_option('blogname') !== '' && get_option('blogdescription') !== '',
            'label' => __('Business name & tagline set', 'pressroot'),
            'fix'   => prt_wizard_url(1),
        ],
        [
            'ok'    => (bool) $pages,
            'label' => __('Website pages generated', 'pressroot'),
            'fix'   => prt_wizard_url(4),
        ],
        [
            'ok'    => ! $drafts || count($drafts) < count($pages),
            'label' => $drafts
                ? sprintf(_n('%d page still in draft (published at launch below)', '%d pages still in draft (published at launch below)', count($drafts), 'pressroot'), count($drafts))
                : __('All pages published', 'pressroot'),
            'fix'   => '',
        ],
        [
            'ok'    => (bool) $menus,
            'label' => __('Navigation menu built', 'pressroot'),
            'fix'   => admin_url('customize.php?autofocus[section]=prt_nav_section'),
        ],
        [
            'ok'    => (bool) get_theme_mod('custom_logo') || (string) get_theme_mod('prt_seo_logo', '') !== '',
            'label' => __('Logo uploaded', 'pressroot'),
            'fix'   => prt_wizard_url(1),
        ],
        [
            'ok'    => (string) get_theme_mod('prt_ga4_id', '') !== '',
            'label' => __('Google Analytics connected (optional)', 'pressroot'),
            'fix'   => prt_wizard_url(2),
        ],
        [
            'ok'    => (int) get_option('site_icon') > 0,
            'label' => __('Site icon (favicon) set (optional)', 'pressroot'),
            'fix'   => prt_wizard_url(3),
        ],
    ];
}

function prt_wizard_step_launch(): void
{
    $launched = (int) get_option('prt_wizard_launched', 0);
    $pages    = function_exists('App\\prt_get_site_type_pages') ? prt_get_site_type_pages() : [];
    $drafts   = array_filter($pages, fn ($p) => $p->post_status !== 'publish');

    if (isset($_GET['prt_wiz_launched'])) {
        echo '<div class="notice notice-success is-dismissible"><p>🚀 ' . esc_html__('Your site is live! Pages published, search engines welcomed. Congratulations.', 'pressroot') . '</p></div>';
    }
    ?>
    <div class="prt-rf-card prt-wiz-card">
        <h3 style="margin-top:0">6 · <?php esc_html_e('Launch', 'pressroot'); ?></h3>

        <?php if ($launched) : ?>
            <p>🎉 <?php printf(
                /* translators: %s: launch date */
                esc_html__('Launched on %s. You can re-run any wizard step, or manage the site from the other Pressroot tabs.', 'pressroot'),
                esc_html(wp_date(get_option('date_format'), $launched))
            ); ?></p>
            <p>
                <a class="prt-rf-btn prt-rf-btn-primary" href="<?php echo esc_url(home_url('/')); ?>" target="_blank" rel="noopener noreferrer"><?php esc_html_e('View your live site ↗', 'pressroot'); ?></a>
                <a class="prt-rf-btn prt-rf-btn-ghost" href="<?php echo esc_url(prt_settings_tab_url('ai')); ?>"><?php esc_html_e('Keep improving (Site Types)', 'pressroot'); ?></a>
            </p>
        <?php endif; ?>

        <h4><?php esc_html_e('Pre-flight checklist', 'pressroot'); ?></h4>
        <ul class="prt-wiz-checklist">
            <?php foreach (prt_wizard_checklist() as $row) : ?>
                <li class="<?php echo $row['ok'] ? 'is-ok' : ''; ?>">
                    <span><?php echo $row['ok'] ? '✅' : '◻️'; ?></span>
                    <?php echo esc_html($row['label']); ?>
                    <?php if (! $row['ok'] && $row['fix'] !== '') : ?>
                        <a href="<?php echo esc_url($row['fix']); ?>"><?php esc_html_e('Fix →', 'pressroot'); ?></a>
                    <?php endif; ?>
                </li>
            <?php endforeach; ?>
        </ul>

        <?php prt_wizard_saving_bar('prt-wiz-bar-launch', __('Launching…', 'pressroot'), [
            __('Publishing your pages', 'pressroot'),
            __('Setting your front page', 'pressroot'),
            __('Opening the doors to search engines', 'pressroot'),
        ]); ?>
        <form method="post" action="<?php echo esc_url(admin_url('admin-post.php')); ?>" onsubmit="return prtWizStart('prt-wiz-bar-launch')">
            <input type="hidden" name="action" value="prt_wizard_launch">
            <?php wp_nonce_field('prt_wizard_launch'); ?>
            <table class="form-table" role="presentation">
                <tr>
                    <th scope="row"><?php esc_html_e('Publishing', 'pressroot'); ?></th>
                    <td>
                        <label><input type="checkbox" name="prt_wiz_publish_drafts" value="1" checked>
                            <?php echo esc_html($drafts
                                ? sprintf(_n('Publish my %d draft page', 'Publish my %d draft pages', count($drafts), 'pressroot'), count($drafts))
                                : __('Publish any remaining draft pages', 'pressroot')); ?></label>
                    </td>
                </tr>
                <tr>
                    <th scope="row"><?php esc_html_e('Search engines', 'pressroot'); ?></th>
                    <td>
                        <label><input type="checkbox" name="prt_wiz_go_public" value="1" checked>
                            <?php esc_html_e('Let search engines index my site (turns visibility back on)', 'pressroot'); ?></label>
                    </td>
                </tr>
            </table>
            <p class="submit" style="display:flex;gap:10px;align-items:center">
                <a class="button" href="<?php echo esc_url(prt_wizard_url(5)); ?>">← <?php esc_html_e('Back', 'pressroot'); ?></a>
                <button type="submit" class="button button-primary button-hero" style="margin:0">🚀 <?php echo $launched ? esc_html__('Re-run launch', 'pressroot') : esc_html__('Launch my website', 'pressroot'); ?></button>
            </p>
        </form>
        <p class="description"><?php esc_html_e('Not ready? Nothing forces you — drafts stay drafts until you launch, and you can come back to this step anytime.', 'pressroot'); ?></p>
    </div>
    <?php
}

/** Launch: publish the generated drafts, open to search engines, celebrate. */
add_action('admin_post_prt_wizard_launch', function () {
    if (! current_user_can('manage_options') || ! check_admin_referer('prt_wizard_launch')) {
        wp_die(__('Not allowed.', 'pressroot'));
    }

    if (! empty($_POST['prt_wiz_publish_drafts']) && function_exists('App\\prt_get_site_type_pages')) {
        foreach (prt_get_site_type_pages() as $p) {
            if ($p->post_status !== 'publish') {
                wp_update_post(['ID' => $p->ID, 'post_status' => 'publish']);
            }
        }
        // Make sure the site actually opens on a page, not the posts feed:
        // if no static front page is set, promote the tool's "home" page (or
        // a page slugged "home") the way seed-pages does on activation.
        if (get_option('show_on_front') !== 'page' || ! (int) get_option('page_on_front')) {
            $front = 0;
            foreach (prt_get_site_type_pages() as $p) {
                if ($p->prt_role === 'home') {
                    $front = (int) $p->ID;
                    break;
                }
            }
            if (! $front) {
                $byslug = get_page_by_path('home');
                $front  = $byslug ? (int) $byslug->ID : 0;
            }
            if ($front) {
                update_option('show_on_front', 'page');
                update_option('page_on_front', $front);
            }
        }
    }

    if (! empty($_POST['prt_wiz_go_public'])) {
        update_option('blog_public', 1);
    }

    update_option('prt_wizard_launched', time(), false);
    prt_wizard_mark_done(7);
    wp_safe_redirect(prt_wizard_url(6, ['prt_wiz_launched' => '1']));
    exit;
});

/* ──────────────────────────────────────────────────────────────────────────
 * Shared handlers + integration glue
 * ────────────────────────────────────────────────────────────────────── */

/** Generic "mark step done and advance" for steps whose work happens via other handlers (4, 5). */
add_action('admin_post_prt_wizard_mark', function () {
    if (! current_user_can('edit_theme_options') || ! check_admin_referer('prt_wizard_mark')) {
        wp_die(__('Not allowed.', 'pressroot'));
    }
    $step  = max(1, min(count(prt_wizard_steps()), absint($_POST['step'] ?? 0)));
    prt_wizard_mark_done($step);
    wp_safe_redirect(prt_wizard_url(min($step + 1, count(prt_wizard_steps()))));
    exit;
});

/**
 * Where should an admin-post handler redirect back to? The generation
 * handlers in ai-assistant.php / ai-builder.php call this so wizard-launched
 * runs return to the wizard step instead of their classic tab. Posting
 * nothing keeps the old behavior exactly.
 */
function prt_settings_return_url(string $defaultTab, array $args = []): string
{
    $ret  = sanitize_key($_POST['prt_return_tab'] ?? '');
    $step = absint($_POST['prt_return_step'] ?? 0);
    if ($ret !== '') {
        return prt_settings_tab_url($ret, $args + ($step ? ['step' => $step] : []));
    }
    return prt_settings_tab_url($defaultTab, $args);
}

/**
 * First-run pointer: until the wizard has been finished once, a dismissible
 * admin notice nudges toward it from the Dashboard and Themes screens (not
 * everywhere — nobody needs setup nagging on every single admin page).
 */
add_action('admin_notices', function () {
    if (! current_user_can('edit_theme_options') || prt_wizard_is_complete()) {
        return;
    }
    $screen = function_exists('get_current_screen') ? get_current_screen() : null;
    if (! $screen || ! in_array($screen->id, ['dashboard', 'themes'], true)) {
        return;
    }
    printf(
        '<div class="notice notice-info"><p><strong>%s</strong> %s <a class="button button-primary" href="%s">%s</a></p></div>',
        esc_html__('Welcome to Pressroot!', 'pressroot'),
        esc_html__('Six guided steps take you from blank install to launched site — business info, connections, and an AI-generated design.', 'pressroot'),
        esc_url(prt_settings_tab_url('setup')),
        esc_html__('Start setup', 'pressroot')
    );
});
