<?php

/**
 * Pressroot — the one consolidated settings page for the theme.
 *
 * Appearance -> Pressroot. Used to be four separate admin pages (Theme
 * Tools, Starter Sites, Pressroot AI, GitHub), each with its own <h1> and
 * its own spot in the Appearance submenu. Consolidated into one branded
 * page with a section per area, so the theme has a single, obvious home
 * instead of scattering settings across the WordPress admin menu:
 *
 *   - Site Types — app/ai-assistant.php     (prt_pressroot_ai_tab_html())
 *   - Support    — app/support-settings.php (prt_support_tab_html())
 *
 * The GitHub tab briefly moved out to the standalone Repofolio plugin
 * (Settings -> Repofolio) along with the rest of the GitHub subsystem — see
 * the note in app/setup.php. Repofolio has since been packaged back INTO the
 * theme as a Theme Addon (app/repofolio-addon.php, classes under
 * app/Repofolio/), so the GitHub tab is back here:
 *
 *   - GitHub     — app/repofolio-addon.php  (prt_repofolio_tab_html())
 *
 * Navigation is a left sidebar with the active section's content on the
 * right (prt_settings_render() below) rather than the top nav-tab-wrapper
 * this page used before — same sections, different chrome, so every
 * `prt_settings_tab_url($tab, $extra)` call across these files still works
 * unchanged; only the page-level layout markup changed.
 *
 * REMOVED as part of this consolidation: the old "Starter Sites" demo
 * importer (app/demo-import.php) is gone. It offered 2 personas (portfolio,
 * agency) built from a fixed set of pattern slugs. CORRECTION: an earlier
 * note here claimed most of those pattern slugs no longer existed — that
 * was checked again and was wrong; all 11 are still registered (mostly in
 * app/sections-library.php, a few in app/patterns-extra.php), so the demo
 * importer would still have worked. The actual reason it was removed
 * stands on its own: it was a strictly weaker duplicate of Pressroot AI's
 * site-type picker (5 personas, 2 hand-built variants each, live design
 * previews, regenerate, dedicated per-type patterns) that solves the exact
 * same "give me a starter site" job better — 2 fixed personas vs. 5, no
 * regenerate, no previews. A placeholder "Starter" tab briefly explained
 * the retirement and linked to Pressroot AI; that tab has since been
 * removed too — Pressroot AI is reachable directly from its own tab, so
 * the explainer had nothing left to do. The dashboard widget's old "Create
 * starter pages + menu" button (blank pages, no design) was removed the
 * same way, in app/whitelabel.php — that reasoning (blank vs. designed
 * pages) was and still is accurate.
 *
 * ALSO REMOVED: the standalone "Style Kits" tab (app/settings-io.php). Every
 * Site Type already applies its own matching Style Kit automatically when
 * chosen, so a separate manual "pick a kit yourself" grid was a second way to
 * do the same thing. The former "Pressroot AI" tab was renamed **Site
 * Types** to reflect that it's now the primary "set up your site" tab — it
 * still surfaces AI (starter hero copy + the Advanced "Connect more AI
 * models" section), per its own docblock in app/ai-assistant.php. Style
 * Kits' Export/Import/Reset controls weren't dropped, just relocated: they
 * now render inside a collapsed "Advanced" section on the Site Types tab
 * (`prt_settings_backup_fields_html()` in app/settings-io.php), same pattern
 * as AI Connectors.
 *
 * Each tab's render function was originally a full `prt_..._render()` with
 * its own `<div class="wrap"><h1>`; those were extracted down to
 * `prt_..._tab_html()` functions with no page chrome, callable from here.
 * Every admin-post/AJAX handler those files already had is unchanged — only
 * where their forms redirect back to changed, via prt_settings_tab_url()
 * below (the one place that knows this page's slug + tab query var).
 */

namespace App;

/** This page's slug, in one place, so nothing else needs to hardcode it. */
const PRT_SETTINGS_SLUG = 'prt-settings';

/**
 * Build a URL back to one tab of this page, optionally with extra query args
 * (e.g. a result/notice flag). Every admin-post handler across the four
 * consolidated files uses this instead of building `themes.php?page=...` by
 * hand, so there is exactly one place that knows this page's slug.
 */
function prt_settings_tab_url(string $tab, array $extra = []): string
{
    return add_query_arg(array_merge([
        'page' => PRT_SETTINGS_SLUG,
        'tab'  => $tab,
    ], $extra), admin_url('themes.php'));
}

/**
 * The tabs, in display order. Each maps to a render callback (no args,
 * echoes HTML). Filterable (`pressroot/settings_tabs`) so addons and forks
 * can register their own sections without editing this file — the registry
 * used to be hardcoded, which forced exactly that.
 */
function prt_settings_tabs(): array
{
    return apply_filters('pressroot/settings_tabs', [
        'setup' => [
            'label'    => __('Setup', 'pressroot'),
            'render'   => __NAMESPACE__ . '\\prt_setup_wizard_tab_html',
            // The six-step guided onboarding wizard (app/setup-wizard.php):
            // business info → connections → WP settings → generate → review
            // → launch. First tab AND the default landing tab until the
            // wizard has been completed once (see prt_settings_render()).
            'visible'  => function_exists('App\\prt_setup_wizard_tab_html'),
        ],
        'models' => [
            'label'    => __('AI Models', 'pressroot'),
            'render'   => __NAMESPACE__ . '\\prt_ai_models_tab_html',
            // Writing / image / video providers in one place. Free keyless
            // defaults always work; keys are optional upgrades.
            'visible'  => function_exists('App\\prt_ai_models_tab_html') && prt_addon_enabled('pressroot_ai'),
        ],
        'settings' => [
            'label'    => __('Theme Settings', 'pressroot'),
            'render'   => __NAMESPACE__ . '\\prt_theme_settings_tab_html',
            // The Customizer's front door: same theme_mods, native fields,
            // no live-preview detour needed for the common 90%.
            'visible'  => function_exists('App\\prt_theme_settings_tab_html'),
        ],
        'ai' => [
            'label'    => __('Site Types', 'pressroot'),
            'render'   => __NAMESPACE__ . '\\prt_pressroot_ai_tab_html',
            'visible'  => function_exists('App\\prt_addon_enabled') ? prt_addon_enabled('pressroot_ai') : true,
        ],
        'github' => [
            'label'    => __('GitHub', 'pressroot'),
            'render'   => __NAMESPACE__ . '\\prt_repofolio_tab_html',
            // The GitHub tab is back (it moved out with the Repofolio plugin,
            // see the old note at the top of this file) — Repofolio now ships
            // inside the theme as an addon (app/repofolio-addon.php), and its
            // settings render here. Hidden when the addon is off AND the
            // standalone plugin isn't picking up the slack; visible in plugin
            // mode so the tab can point people at the plugin's settings page.
            'visible'  => function_exists('App\\prt_repofolio_tab_html')
                && (prt_addon_enabled('repofolio') || defined('REPOFOLIO_VERSION')),
        ],
        'support' => [
            'label'    => __('Support', 'pressroot'),
            'render'   => __NAMESPACE__ . '\\prt_support_tab_html',
            // Always visible — not gated by the Pressroot AI addon toggle,
            // since getting help/docs shouldn't depend on an unrelated
            // feature being on. Still capability-gated inside its own
            // render function (edit_theme_options), same as every tab.
            'visible'  => true,
        ],
    ]);
}

/** Single admin page registration, replacing the four it consolidates. */
add_action('admin_menu', function () {
    add_theme_page(
        __('Pressroot', 'pressroot'),
        __('Pressroot', 'pressroot'),
        'edit_theme_options',
        PRT_SETTINGS_SLUG,
        __NAMESPACE__ . '\\prt_settings_render'
    );
});

/**
 * Page chrome stylesheet — a straight port of the Repofolio docs site's
 * design system (https://matthummel-pa.github.io/repofolio/, docs/index.html
 * in the repofolio repo): spectrum top bar, dark radial hero with gradient
 * headline + mono eyebrow, pill buttons, spectrum-topped cards. Static CSS
 * served from the theme (no build step), scoped under .prt-rf.
 */
add_action('admin_enqueue_scripts', function ($hook) {
    if ($hook !== 'appearance_page_' . PRT_SETTINGS_SLUG) {
        return;
    }
    wp_enqueue_style(
        'prt-settings-admin',
        get_theme_file_uri('resources/css/admin-settings.css'),
        [],
        (string) @filemtime(get_theme_file_path('resources/css/admin-settings.css')) ?: (string) wp_get_theme()->get('Version')
    );
});

/**
 * The Repofolio "repo card" mark, inline (docs site .brand .mark SVG):
 * a white card with the spectrum language bar. No image asset needed.
 */
function prt_settings_mark_svg(): string
{
    return '<svg width="24" height="24" viewBox="0 0 512 512" xmlns="http://www.w3.org/2000/svg" aria-hidden="true">'
        . '<defs><linearGradient id="prt-rf-s" x1="0" y1="0" x2="1" y2="0">'
        . '<stop offset="0" stop-color="#6C4CF1"/><stop offset=".28" stop-color="#FF4D9D"/><stop offset=".52" stop-color="#FF7A3D"/>'
        . '<stop offset=".72" stop-color="#FFC53D"/><stop offset=".88" stop-color="#37E29A"/><stop offset="1" stop-color="#22CFEE"/>'
        . '</linearGradient></defs>'
        . '<rect x="60" y="150" width="392" height="212" rx="34" fill="#fff"/>'
        . '<rect x="96" y="196" width="320" height="22" rx="11" fill="url(#prt-rf-s)"/>'
        . '<rect x="96" y="244" width="240" height="16" rx="8" fill="#CFC9E6"/>'
        . '<rect x="96" y="278" width="180" height="16" rx="8" fill="#E4E0F1"/>'
        . '<circle cx="396" cy="300" r="16" fill="#FFC53D"/>'
        . '</svg>';
}

/**
 * Branded hero shown above every tab — the docs site's header, adapted:
 * brand row (mark + name + doc links), mono eyebrow, gradient display
 * headline, lead line, pill CTAs (Docs/Support, editable via the same
 * prt_docs_url / prt_support_url theme_mods as before) and feature pills.
 */
function prt_settings_header(): void
{
    $docsUrl    = get_theme_mod('prt_docs_url', 'https://github.com/matthummel-pa/pressroot#readme');
    $supportUrl = get_theme_mod('prt_support_url', 'https://github.com/matthummel-pa/pressroot/issues');
    ?>
    <div class="prt-rf-hero">
        <div class="prt-rf-brandrow">
            <div class="prt-rf-brand">
                <span class="prt-rf-mark"><?php echo prt_settings_mark_svg(); // phpcs:ignore -- static inline SVG. ?></span>
                <?php esc_html_e('Pressroot', 'pressroot'); ?>
            </div>
            <div class="prt-rf-navlinks">
                <?php if ($docsUrl !== '') : ?>
                    <a href="<?php echo esc_url($docsUrl); ?>" target="_blank" rel="noopener noreferrer"><?php esc_html_e('Docs', 'pressroot'); ?></a>
                <?php endif; ?>
                <?php if ($supportUrl !== '') : ?>
                    <a href="<?php echo esc_url($supportUrl); ?>" target="_blank" rel="noopener noreferrer"><?php esc_html_e('Support', 'pressroot'); ?></a>
                <?php endif; ?>
                <a href="https://github.com/matthummel-pa/pressroot" target="_blank" rel="noopener noreferrer"><?php esc_html_e('GitHub', 'pressroot'); ?></a>
            </div>
        </div>

        <div class="prt-rf-eyebrow"><?php esc_html_e('WordPress theme', 'pressroot'); ?></div>
        <h1 class="prt-rf-title"><?php esc_html_e('Pressroot', 'pressroot'); ?></h1>
        <p class="prt-rf-lead"><?php esc_html_e('Site types, AI, and integrations — all in one place.', 'pressroot'); ?></p>
        <p class="prt-rf-byline" style="margin:-6px 0 18px;font-size:13px;opacity:.85"><?php printf(
            /* translators: %s: theme author link */
            esc_html__('Created by %s', 'pressroot'),
            '<a href="https://github.com/matthummel-pa" target="_blank" rel="author noopener noreferrer" style="font-weight:600;text-decoration:underline;color:inherit">matthummel ↗</a>'
        ); ?></p>

        <div class="prt-rf-cta">
            <?php if ($docsUrl !== '') : ?>
                <a class="prt-rf-btn prt-rf-btn-primary" href="<?php echo esc_url($docsUrl); ?>" target="_blank" rel="noopener noreferrer"><?php esc_html_e('Documentation', 'pressroot'); ?></a>
            <?php endif; ?>
            <?php if ($supportUrl !== '') : ?>
                <a class="prt-rf-btn prt-rf-btn-ghost" href="<?php echo esc_url($supportUrl); ?>" target="_blank" rel="noopener noreferrer"><?php esc_html_e('Get support', 'pressroot'); ?></a>
            <?php endif; ?>
        </div>

        <div class="prt-rf-pills">
            <span class="prt-rf-pill"><?php esc_html_e('Site types', 'pressroot'); ?></span><span class="prt-rf-pill"><?php esc_html_e('Pressroot AI', 'pressroot'); ?></span><span class="prt-rf-pill"><?php esc_html_e('GitHub portfolio', 'pressroot'); ?></span><span class="prt-rf-pill"><?php esc_html_e('Block patterns', 'pressroot'); ?></span>
        </div>

        <details>
            <summary><?php esc_html_e('Edit links', 'pressroot'); ?></summary>
            <form method="post" action="<?php echo esc_url(admin_url('admin-post.php')); ?>">
                <input type="hidden" name="action" value="prt_save_meta_links">
                <?php wp_nonce_field('prt_save_meta_links'); ?>
                <label class="screen-reader-text" for="prt_docs_url"><?php esc_html_e('Documentation URL', 'pressroot'); ?></label>
                <input type="url" id="prt_docs_url" name="prt_docs_url" class="regular-text" placeholder="<?php esc_attr_e('Documentation URL', 'pressroot'); ?>" value="<?php echo esc_attr($docsUrl); ?>">
                <label class="screen-reader-text" for="prt_support_url"><?php esc_html_e('Support URL', 'pressroot'); ?></label>
                <input type="url" id="prt_support_url" name="prt_support_url" class="regular-text" placeholder="<?php esc_attr_e('Support URL', 'pressroot'); ?>" value="<?php echo esc_attr($supportUrl); ?>">
                <button class="button"><?php esc_html_e('Save', 'pressroot'); ?></button>
            </form>
        </details>
    </div>
    <?php
}

/** Persist the Docs/Support links. Purely cosmetic/navigational — no secrets. */
add_action('admin_post_prt_save_meta_links', function () {
    if (! current_user_can('edit_theme_options') || ! check_admin_referer('prt_save_meta_links')) {
        wp_die(__('Not allowed.', 'pressroot'));
    }
    set_theme_mod('prt_docs_url', isset($_POST['prt_docs_url']) ? esc_url_raw(wp_unslash($_POST['prt_docs_url'])) : '');
    set_theme_mod('prt_support_url', isset($_POST['prt_support_url']) ? esc_url_raw(wp_unslash($_POST['prt_support_url'])) : '');
    wp_safe_redirect(wp_get_referer() ?: prt_settings_tab_url('ai'));
    exit;
});

/**
 * Render the page: branded header, then a left-sidebar menu with the active
 * section's content on the right (replacing the top nav-tab-wrapper this
 * page used to use — same sections, same prt_settings_tab_url() links,
 * just a different chrome, more like a typical settings app than a row of
 * WordPress admin tabs).
 *
 * Defaults to the "ai" (Site Types) tab — the primary one since Style Kits
 * was removed. If that's not visible (Pressroot AI addon switched off) or an
 * invalid ?tab= was passed, falls through to the first tab that IS visible,
 * rather than assuming any specific tab always is.
 */
function prt_settings_render(): void
{
    if (! current_user_can('edit_theme_options')) {
        return;
    }
    $tabs   = prt_settings_tabs();
    // Default landing tab: the Setup wizard until it's been completed once,
    // then Site Types (the primary day-to-day tab) as before.
    $default = (function_exists('App\\prt_wizard_is_complete') && ! prt_wizard_is_complete()) ? 'setup' : 'ai';
    $active  = isset($_GET['tab']) ? sanitize_key($_GET['tab']) : $default;
    if (! isset($tabs[$active]) || empty($tabs[$active]['visible'])) {
        $active = '';
        foreach ($tabs as $id => $t) {
            if (! empty($t['visible'])) {
                $active = $id;
                break;
            }
        }
    }
    ?>
    <div class="wrap prt-rf">
        <?php prt_settings_header(); ?>

        <div class="prt-rf-layout">
            <nav class="prt-rf-nav" aria-label="<?php esc_attr_e('Pressroot settings sections', 'pressroot'); ?>">
                <ul>
                    <?php foreach ($tabs as $id => $tab) :
                        if (empty($tab['visible'])) {
                            continue;
                        }
                        $isActive = $id === $active;
                    ?>
                        <li>
                            <a href="<?php echo esc_url(prt_settings_tab_url($id)); ?>"<?php echo $isActive ? ' class="is-active" aria-current="page"' : ''; ?>>
                                <?php echo esc_html($tab['label']); ?>
                            </a>
                        </li>
                    <?php endforeach; ?>
                </ul>
            </nav>

            <div class="prt-rf-content">
                <?php
                if (isset($tabs[$active]) && is_callable($tabs[$active]['render'])) {
                    call_user_func($tabs[$active]['render']);
                }
                ?>
            </div>
        </div>
    </div>
    <?php
}
