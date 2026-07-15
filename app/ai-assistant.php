<?php

/**
 * Site Types — the "ai" tab on Appearance -> Pressroot, the consolidated
 * settings page (see app/pressroot-settings.php). (Formerly its own "AI
 * Setup Assistant" admin page, then briefly its own "Pressroot AI" page, then
 * the "Pressroot AI" tab on the consolidated page — renamed again to **Site
 * Types** once the standalone Style Kits tab was removed, since this became
 * the primary "set up your site" tab rather than one of several equally-
 * weighted options. Internal slugs/option/meta keys were left as prt_* /
 * prt-ai-* / function names like prt_pressroot_ai_tab_html() so existing
 * installs and stored post meta keep working; only user-facing labels
 * changed.)
 *
 * This whole feature is an optional theme addon — see app/theme-addons.php.
 * If "Enable Pressroot AI" is switched off in Theme Options -> Theme Addons,
 * this tab, its AJAX endpoints, and its admin-post actions all decline to
 * run (each checks prt_addon_enabled('pressroot_ai') below).
 *
 * The "reduce heavy lifting for a new theme owner" feature: four tools on
 * one admin page (the fourth, Export/Import/Reset, folded in from
 * settings-io.php when the standalone Style Kits tab was retired).
 *
 * 1. SITE TYPE PROFILES — supersedes the old plain Style Kit picker that used
 *    to live in settings-io.php as its own tab. Instead of just choosing
 *    colors/fonts, the owner picks the kind of site they're building (Agency,
 *    Freelance/Portfolio, SaaS, Blog, Marketing landing) and this applies the
 *    matching Style Kit AND creates the handful of starter pages that type of
 *    site actually needs.
 *    Every page is pre-filled with a DEDICATED pattern written specifically
 *    for that site type (registered in app/site-type-agency.php,
 *    site-type-freelance.php, site-type-saas.php, site-type-blog.php, and
 *    site-type-marketing.php — five sibling files, one per category, each
 *    with its own tailored dummy content and layout), not the generic
 *    Services/Pricing/etc. patterns from page-patterns.php. Safe to click
 *    more than once: existing pages are never touched or duplicated (matched
 *    by slug), only missing ones are created. This directly answers "clone
 *    this into other projects based on type of WordPress website someone
 *    would need" — the site-type list below is exactly that set of starting
 *    points, and is filterable so a fork of this theme can add its own
 *    without touching this file (apply_filters below).
 *
 * 2. REGENERATE — every page created by this tool has TWO hand-written
 *    variants (A/B — different layout structure and copy angle, not just a
 *    color swap; see the five site-type-*.php files). If the owner doesn't
 *    like what they got, "Regenerate" swaps that one page over to its other
 *    variant. This is deliberately NOT a live AI call re-writing arbitrary
 *    block markup on every click (an LLM reliably emitting valid Gutenberg
 *    block-comment syntax on demand isn't something a free, keyless text API
 *    can be trusted to do) — cycling between two genuinely good, hand-built
 *    variants is the robust way to give a real "try again" experience without
 *    ever risking broken block markup landing in the owner's content.
 *
 * 3. STARTER COPY GENERATOR — a one-line business description in, a hero
 *    headline + subheadline out, using the same free/no-API-key Pollinations
 *    text endpoint the Hero Image Finder (app/hero-image.php) already uses
 *    for images. This is genuinely fresh AI-generated text (unlike the
 *    pattern regeneration above), meant to be copy-pasted into whichever
 *    variant the owner ends up keeping.
 *
 * Neither the site-type tool nor the copy generator touches published
 * content without an explicit click, and nothing here calls a paid API or
 * requires an account.
 */

namespace App;

/**
 * The shared pattern category every site-type-*.php file tags its patterns
 * with. Registered once, here, since this file is the "orchestrator" for the
 * whole site-type feature — the five pattern files intentionally do NOT
 * register this themselves, to avoid the duplicate-category-registration
 * "doing_it_wrong" notice this codebase has already hit once before (see the
 * fix in app/block-patterns.php).
 */
add_action('init', function () {
    if (function_exists('register_block_pattern_category')) {
        register_block_pattern_category('prt-site-types', [
            'label' => __('AI Site Types', 'pressroot'),
        ]);
    }
}, 9);

/**
 * Site type -> {style kit slug, starter pages}. Each starter page names a
 * role (used to track which page is which when regenerating), a slug/title,
 * and TWO pattern slugs (variant A/B) registered in this site type's
 * dedicated site-type-*.php file.
 *
 * Filterable (`pressroot/site_types`) so a fork of this theme aimed at a
 * different niche can add, remove, or re-map profiles without editing this
 * file directly — the same extensibility convention prt_style_kits() uses.
 */
function prt_site_types()
{
    return apply_filters('pressroot/site_types', [
        'agency' => [
            'label' => __('Agency / Studio', 'pressroot'),
            'desc'  => __('Multi-service shop selling to clients: Services, Pricing, Contact.', 'pressroot'),
            'kit'   => 'paper_space',
            'pages' => [
                ['role' => 'services', 'slug' => 'services', 'title' => __('Services', 'pressroot'), 'pattern_a' => 'prt-site/agency-services-a', 'pattern_b' => 'prt-site/agency-services-b'],
                ['role' => 'pricing',  'slug' => 'pricing',  'title' => __('Pricing', 'pressroot'),  'pattern_a' => 'prt-site/agency-pricing-a',  'pattern_b' => 'prt-site/agency-pricing-b'],
                ['role' => 'contact',  'slug' => 'contact',  'title' => __('Contact', 'pressroot'),  'pattern_a' => 'prt-site/agency-contact-a',  'pattern_b' => 'prt-site/agency-contact-b'],
            ],
        ],
        'freelance' => [
            'label' => __('Freelance / Portfolio', 'pressroot'),
            'desc'  => __('One person selling their own work: About, Résumé, Projects.', 'pressroot'),
            'kit'   => 'editorial',
            'pages' => [
                ['role' => 'about',    'slug' => 'about',    'title' => __('About', 'pressroot'),    'pattern_a' => 'prt-site/freelance-about-a',    'pattern_b' => 'prt-site/freelance-about-b'],
                ['role' => 'resume',   'slug' => 'resume',   'title' => __('Résumé', 'pressroot'),   'pattern_a' => 'prt-site/freelance-resume-a',   'pattern_b' => 'prt-site/freelance-resume-b'],
                ['role' => 'projects', 'slug' => 'projects', 'title' => __('Projects', 'pressroot'), 'pattern_a' => 'prt-site/freelance-projects-a', 'pattern_b' => 'prt-site/freelance-projects-b'],
            ],
        ],
        'saas' => [
            'label' => __('SaaS / Startup', 'pressroot'),
            'desc'  => __('Product-led site: Features, Pricing, Contact — dark, modern default.', 'pressroot'),
            'kit'   => 'midnight',
            'pages' => [
                ['role' => 'features', 'slug' => 'features', 'title' => __('Features', 'pressroot'), 'pattern_a' => 'prt-site/saas-features-a', 'pattern_b' => 'prt-site/saas-features-b'],
                ['role' => 'pricing',  'slug' => 'pricing',  'title' => __('Pricing', 'pressroot'),  'pattern_a' => 'prt-site/saas-pricing-a',  'pattern_b' => 'prt-site/saas-pricing-b'],
                ['role' => 'contact',  'slug' => 'contact',  'title' => __('Contact', 'pressroot'),  'pattern_a' => 'prt-site/saas-contact-a',  'pattern_b' => 'prt-site/saas-contact-b'],
            ],
        ],
        'blog' => [
            'label' => __('Blog / Content site', 'pressroot'),
            'desc'  => __('Writing-first site: Blog index + About, warm neutral palette.', 'pressroot'),
            'kit'   => 'warm_sand',
            'pages' => [
                ['role' => 'blog',  'slug' => 'blog',  'title' => __('Blog', 'pressroot'),  'pattern_a' => 'prt-site/blog-index-a', 'pattern_b' => 'prt-site/blog-index-b'],
                ['role' => 'about', 'slug' => 'about', 'title' => __('About', 'pressroot'), 'pattern_a' => 'prt-site/blog-about-a', 'pattern_b' => 'prt-site/blog-about-b'],
            ],
        ],
        'marketing' => [
            'label' => __('Marketing / Landing page', 'pressroot'),
            'desc'  => __('Single-focus landing site: Home + Contact, sharp minimal palette.', 'pressroot'),
            'kit'   => 'mono_slate',
            'pages' => [
                ['role' => 'home',    'slug' => 'home',    'title' => __('Home', 'pressroot'),    'pattern_a' => 'prt-site/marketing-home-a',    'pattern_b' => 'prt-site/marketing-home-b'],
                ['role' => 'contact', 'slug' => 'contact', 'title' => __('Contact', 'pressroot'), 'pattern_a' => 'prt-site/marketing-contact-a', 'pattern_b' => 'prt-site/marketing-contact-b'],
            ],
        ],
        'affiliate' => [
            'label' => __('Affiliate Marketing', 'pressroot'),
            'desc'  => __('Review-and-recommend site: Home, Top Picks, Disclosure — trust-first.', 'pressroot'),
            'kit'   => 'paper_space',
            'pages' => [
                ['role' => 'home',       'slug' => 'home',       'title' => __('Home', 'pressroot'),       'pattern_a' => 'prt-site/affiliate-home-a',       'pattern_b' => 'prt-site/affiliate-home-b'],
                ['role' => 'reviews',    'slug' => 'top-picks',  'title' => __('Top Picks', 'pressroot'),  'pattern_a' => 'prt-site/affiliate-reviews-a',    'pattern_b' => 'prt-site/affiliate-reviews-b'],
                ['role' => 'disclosure', 'slug' => 'disclosure', 'title' => __('Disclosure', 'pressroot'), 'pattern_a' => 'prt-site/affiliate-disclosure-a', 'pattern_b' => 'prt-site/affiliate-disclosure-b'],
            ],
        ],
        'restaurant' => [
            'label' => __('Restaurant / Café', 'pressroot'),
            'desc'  => __('Food-first site: Home, Menu, Reservations — warm and appetizing.', 'pressroot'),
            'kit'   => 'warm_sand',
            'pages' => [
                ['role' => 'home',         'slug' => 'home',         'title' => __('Home', 'pressroot'),         'pattern_a' => 'prt-site/restaurant-home-a',         'pattern_b' => 'prt-site/restaurant-home-b'],
                ['role' => 'menu',         'slug' => 'menu',         'title' => __('Menu', 'pressroot'),         'pattern_a' => 'prt-site/restaurant-menu-a',         'pattern_b' => 'prt-site/restaurant-menu-b'],
                ['role' => 'reservations', 'slug' => 'reservations', 'title' => __('Reservations', 'pressroot'), 'pattern_a' => 'prt-site/restaurant-reservations-a', 'pattern_b' => 'prt-site/restaurant-reservations-b'],
            ],
        ],
        'realty' => [
            'label' => __('Real Estate', 'pressroot'),
            'desc'  => __('Property site: Home, Listings, Agents — clean and confidence-building.', 'pressroot'),
            'kit'   => 'editorial',
            'pages' => [
                ['role' => 'home',     'slug' => 'home',     'title' => __('Home', 'pressroot'),     'pattern_a' => 'prt-site/realty-home-a',     'pattern_b' => 'prt-site/realty-home-b'],
                ['role' => 'listings', 'slug' => 'listings', 'title' => __('Listings', 'pressroot'), 'pattern_a' => 'prt-site/realty-listings-a', 'pattern_b' => 'prt-site/realty-listings-b'],
                ['role' => 'agents',   'slug' => 'agents',   'title' => __('Agents', 'pressroot'),   'pattern_a' => 'prt-site/realty-agents-a',   'pattern_b' => 'prt-site/realty-agents-b'],
            ],
        ],
    ]);
}

/**
 * Live design previews for the "Choose a site type" cards.
 *
 * Renders a real, standalone HTML page for a single registered pattern —
 * using the theme's own wp_head()/wp_footer() so it picks up the actual
 * compiled CSS, fonts, and global styles, not a fake mockup — that the
 * admin screen embeds in a scaled-down <iframe> thumbnail. Gated to signed-in
 * users who can edit theme options (this is an admin-only preview surface,
 * not a public route), and only ever renders content already registered as a
 * block pattern (nothing user-supplied is rendered).
 */
add_filter('query_vars', function ($vars) {
    $vars[] = 'prt_pattern_preview';
    $vars[] = 'prt_preview_kit';
    return $vars;
});

add_action('template_redirect', function () {
    $slug = get_query_var('prt_pattern_preview');
    if (! $slug || ! prt_addon_enabled('pressroot_ai')) {
        return;
    }
    if (! is_user_logged_in() || ! current_user_can('edit_theme_options')) {
        wp_die(__('You do not have permission to view this preview.', 'pressroot'), '', ['response' => 403]);
    }

    $registry = class_exists('WP_Block_Patterns_Registry') ? \WP_Block_Patterns_Registry::get_instance() : null;
    $pattern  = $registry ? $registry->get_registered(sanitize_text_field(wp_unslash($slug))) : null;

    show_admin_bar(false);
    nocache_headers(); // design previews must never be cached stale
    header('Content-Type: text/html; charset=' . get_bloginfo('charset'));
    ?><!DOCTYPE html>
    <html <?php language_attributes(); ?>>
    <head>
        <meta charset="<?php bloginfo('charset'); ?>">
        <meta name="viewport" content="width=device-width, initial-scale=1">
        <style>html{background:#fff}body{margin:0}</style>
        <?php wp_head(); ?>
        <?php
        // The layout template normally fires prt_head_end AFTER wp_head so
        // the Customizer palette/font variables (app/customizer.php) and
        // theme vars (app/footer-content.php) override app.css. This
        // standalone preview page skipped it, which is why previews used to
        // render with the raw stylesheet defaults instead of the live
        // design. Fire it here too so previews match the real site.
        do_action('prt_head_end');

        // Per-kit previews: ?prt_preview_kit=<slug> re-skins this preview
        // with any registered Style Kit's palette + fonts, so each site
        // type's card can show its designs in its OWN kit — not whatever
        // kit the live site happens to be wearing right now.
        $previewKit = sanitize_key(get_query_var('prt_preview_kit'));
        $kits       = function_exists('App\\prt_style_kits') ? prt_style_kits() : [];

        if ($previewKit === 'bare') {
            // BAREBONES previews: no main-theme branding at all — neutral
            // grayscale so pattern structure is judged on its own. Brand
            // answers later upgrade previews to 'brand' mode automatically.
            echo '<style id="prt-preview-bare">:root{'
                . '--color-green:#3a3a44;--color-khaki:#ffffff;--color-ink:#16161a;'
                . '--color-heading:#16161a;--color-body:#46464f;'
                . '--font-display:ui-sans-serif,system-ui,sans-serif;--font-body:ui-sans-serif,system-ui,sans-serif;'
                . '--gradient-brand:linear-gradient(135deg,#3a3a44,#5a5a66);'
                . '--gradient-spectrum:linear-gradient(90deg,#c9c9d2,#e3e3ea);'
                . '}body{background:#fff}</style>';
        } elseif ($previewKit === 'brand' && function_exists('App\\prt_brand_profile')) {
            // BRAND previews: derived live from the Brand tab answers, so
            // the boxes silently re-skin themselves as settings change.
            $bp    = prt_brand_profile();
            $dark  = $bp['mode'] === 'dark';
            $accent = $bp['color'] !== '' ? $bp['color'] : '#6C4CF1';
            echo '<style id="prt-preview-brand">:root{'
                . '--color-green:' . esc_html($accent) . ';'
                . '--color-khaki:' . ($dark ? '#15122a' : '#FFF9F5') . ';'
                . '--color-ink:' . ($dark ? '#F5F2FF' : '#17151F') . ';'
                . '--color-heading:' . ($dark ? '#F5F2FF' : '#17151F') . ';'
                . '--color-body:' . ($dark ? '#CFCBE6' : '#4A4660') . ';'
                . '}body{background:' . ($dark ? '#15122a' : '#FFF9F5') . '}</style>';
        } elseif ($previewKit !== '' && isset($kits[$previewKit]['mods'])) {
            $m     = $kits[$previewKit]['mods'];
            $fonts = function_exists('App\\prt_fonts') ? prt_fonts() : [];
            $h     = $fonts[$m['prt_font_heading'] ?? ''][1] ?? "'Outfit', ui-sans-serif, sans-serif";
            $b     = $fonts[$m['prt_font_body'] ?? ''][1] ?? "'Outfit', ui-sans-serif, sans-serif";
            echo '<style id="prt-preview-kit">:root{'
                . '--color-green:' . esc_html($m['prt_color_action'] ?? '#6C4CF1') . ';'
                . '--color-khaki:' . esc_html($m['prt_color_paper'] ?? '#FFF9F5') . ';'
                . '--color-ink:' . esc_html($m['prt_color_ink'] ?? '#17151F') . ';'
                . '--color-heading:' . esc_html($m['prt_color_ink'] ?? '#17151F') . ';'
                . '--color-body:' . esc_html($m['prt_color_body'] ?? '#4A4660') . ';'
                . '--font-display:' . $h . ';'
                . '--font-body:' . $b . ';'
                . '}body{background:' . esc_html($m['prt_color_paper'] ?? '#FFF9F5') . '}</style>';
        }
        ?>
    </head>
    <body <?php body_class('prt-pattern-preview'); ?>>
        <?php if ($pattern && ! empty($pattern['content'])) : ?>
            <?php echo do_blocks($pattern['content']); ?>
        <?php else : ?>
            <p style="padding:40px;font-family:sans-serif;color:#888"><?php esc_html_e('Preview unavailable.', 'pressroot'); ?></p>
        <?php endif; ?>
        <?php wp_footer(); ?>
    </body>
    </html>
    <?php
    exit;
}, 0);

/**
 * @return string URL to embed in an <iframe> for a live preview of one
 *                registered pattern (see the template_redirect handler
 *                above), optionally re-skinned with a specific Style Kit.
 */
function prt_pattern_preview_url(string $patternSlug, string $kit = ''): string
{
    $args = ['prt_pattern_preview' => rawurlencode($patternSlug)];
    if ($kit !== '') {
        $args['prt_preview_kit'] = rawurlencode($kit);
    }
    // Cache-buster: previews must always show the CURRENT design system —
    // never a stale cached render from before the last kit/trend deal.
    $args['v'] = (string) time();
    return add_query_arg($args, home_url('/'));
}

/**
 * No longer its own admin page — this is now the "Site Types" tab (id: "ai",
 * unchanged internally) on the consolidated Appearance -> Pressroot settings
 * page (see app/pressroot-settings.php), alongside GitHub. Still fully gated
 * by the addon toggle (Theme Options -> Theme Addons — see
 * app/theme-addons.php): the tab simply doesn't render, and its JS isn't
 * enqueued, when "Enable Pressroot AI" is off.
 */

/** Load the copy-generator's JS only when the "ai" tab (Site Types) of the
 *  consolidated settings page is being viewed. Default matches
 *  prt_settings_render()'s default active tab, now "ai" since Style Kits
 *  (the old default) was removed. */
add_action('admin_enqueue_scripts', function ($hook) {
    $onSettingsPage = $hook === 'appearance_page_prt-settings';
    $onAiTab        = ($_GET['tab'] ?? 'ai') === 'ai';
    if (! $onSettingsPage || ! $onAiTab || ! prt_ai_features_enabled()) {
        return;
    }
    $path = 'resources/js/ai-assistant.js';
    wp_enqueue_script(
        'prt-ai-assistant',
        get_theme_file_uri($path),
        [],
        file_exists(get_theme_file_path($path)) ? filemtime(get_theme_file_path($path)) : '1',
        true
    );
    // ajaxUrl + nonce for the server-side prt_ai_generate_copy proxy
    // (app/ai-connectors.php) — API keys for connected models never reach
    // the browser, so generation always goes through this endpoint now,
    // Pollinations included, rather than calling text.pollinations.ai directly.
    wp_localize_script('prt-ai-assistant', 'prtAI', [
        'ajaxUrl' => admin_url('admin-ajax.php'),
        'nonce'   => wp_create_nonce('prt_ai_generate_copy'),
    ]);
});

/**
 * Find every page on the site that this tool created (tagged via post meta
 * when it was inserted), across every site type, so the "Your starter pages"
 * section can offer a Regenerate button regardless of which site type is
 * currently selected in the picker above it.
 *
 * @return array List of WP_Post objects with an extra ->prt_role / ->prt_site_type / ->prt_variant set for convenience.
 */
function prt_get_site_type_pages(): array
{
    $posts = get_posts([
        'post_type'      => 'page',
        'post_status'    => ['publish', 'draft', 'pending', 'private', 'future'],
        'numberposts'    => -1,
        'meta_key'       => '_prt_site_type',
    ]);

    foreach ($posts as $post) {
        $post->prt_site_type = get_post_meta($post->ID, '_prt_site_type', true);
        $post->prt_role      = get_post_meta($post->ID, '_prt_page_role', true);
        $post->prt_variant   = get_post_meta($post->ID, '_prt_pattern_variant', true) ?: 'a';
    }

    return $posts;
}

/**
 * The variants a site-type page definition offers, keyed by variant id.
 * Central so apply/regenerate agree on what exists if a type ever grows a
 * third variant.
 */
function prt_site_type_page_variants(array $def): array
{
    $variants = [];
    foreach (['a', 'b', 'c', 'd'] as $v) {
        if (! empty($def['pattern_' . $v])) {
            $variants[$v] = $def['pattern_' . $v];
        }
    }
    // Core-blocks-only mode (default ON): generated sites use only the C/D
    // variants, which are pure core Gutenberg + prt/smart-* theme blocks —
    // no Custom HTML blocks ever land in an owner's content. The hand-built
    // A/B patterns stay available in the inserter for developers.
    if (get_theme_mod('prt_core_blocks_only', true)) {
        $coreOnly = array_intersect_key($variants, ['c' => 1, 'd' => 1]);
        if ($coreOnly) {
            return $coreOnly;
        }
    }
    return $variants;
}

/**
 * Pick a random variant for a page — different from $current when more than
 * one exists, so "Refresh design" always visibly changes the page.
 */
function prt_pick_random_variant(array $def, string $current = ''): string
{
    $variants = array_keys(prt_site_type_page_variants($def));
    if (! $variants) {
        return 'a';
    }
    $pool = array_values(array_diff($variants, [$current]));
    if (! $pool) {
        $pool = $variants;
    }
    return $pool[array_rand($pool)];
}

/**
 * A design just changed under the reader's feet: stale inlined critical CSS
 * would keep painting the OLD design above the fold until someone remembered
 * to regenerate it. Clear it (and the defer flag that depends on it) so the
 * fresh stylesheet applies immediately — the CSS "regenerates" instantly
 * because the design system is CSS-variable driven; no build step needed.
 */
function prt_flush_design_caches(): void
{
    set_theme_mod('prt_critical_css', '');
    set_theme_mod('prt_defer_main_css', false);
}

function prt_pressroot_ai_tab_html()
{
    if (! current_user_can('edit_theme_options') || ! prt_addon_enabled('pressroot_ai')) {
        return;
    }
    $post    = admin_url('admin-post.php');
    $result  = isset($_GET['prt_site_type_result']) ? sanitize_key($_GET['prt_site_type_result']) : '';
    $created = isset($_GET['prt_created']) ? sanitize_text_field(wp_unslash($_GET['prt_created'])) : '';
    $refreshedList = isset($_GET['prt_refreshed']) ? sanitize_text_field(wp_unslash($_GET['prt_refreshed'])) : '';
    $removed = isset($_GET['prt_removed']) ? sanitize_text_field(wp_unslash($_GET['prt_removed'])) : '';
    $regenerated = isset($_GET['prt_regenerated']) ? sanitize_text_field(wp_unslash($_GET['prt_regenerated'])) : '';
    $bulkRegenerated = isset($_GET['prt_bulk_regenerated']) ? sanitize_key($_GET['prt_bulk_regenerated']) : '';
    $connectorsUpdated = isset($_GET['connectors_updated']);
    // Export/Import/Reset (app/settings-io.php) redirect back here with
    // ?prt_done=... and a #prt-settings-advanced fragment; prt_done notices
    // render inside prt_settings_backup_fields_html() itself, but this flag
    // is also used below to auto-expand that section on return, same as
    // $connectorsUpdated does for the AI Connectors section.
    $backupUpdated = isset($_GET['prt_done']);
    $types   = prt_site_types();
    ?>
        <p class="description"><?php esc_html_e('Pick the kind of site you\'re building to apply a matching design and create starter pages, regenerate any page you don\'t love, then generate hero copy to fill them in.', 'pressroot'); ?></p>

        <?php if ($result !== '' && function_exists('App\prt_build_status_bar')) {
            prt_build_status_bar([
                __('Creating & refreshing pages', 'pressroot'),
                __('Dealing a design kit + trend', 'pressroot'),
                __('Building navigation, header & footer', 'pressroot'),
                __('Re-asserting your branding', 'pressroot'),
                __('Refreshing previews', 'pressroot'),
            ], __('Jump to your pages', 'pressroot'), '#prt-pages');
        } elseif (isset($_GET['prt_bulk_regenerated']) && function_exists('App\prt_build_status_bar')) {
            prt_build_status_bar([
                __('Dealing new designs per page', 'pressroot'),
                __('Rotating kit + design trend', 'pressroot'),
                __('Refreshing previews', 'pressroot'),
            ], __('Jump to your pages', 'pressroot'), '#prt-pages');
        } ?>
        <?php if ($result !== '') : ?>
            <div class="notice notice-success is-dismissible">
                <p>
                    <?php
                    if ($created !== '') {
                        printf(
                            /* translators: %s: comma-separated list of created page titles */
                            esc_html__('Design applied. Created: %s.', 'pressroot'),
                            esc_html($created)
                        );
                    } else {
                        esc_html_e('Design applied.', 'pressroot');
                    }
                    if ($refreshedList !== '') {
                        echo ' ' . esc_html(sprintf(
                            /* translators: %s: comma-separated list of refreshed page titles */
                            __('Refreshed with a brand-new design + content: %s.', 'pressroot'),
                            $refreshedList
                        ));
                    }
                    if ($removed !== '') {
                        echo ' ' . esc_html(sprintf(
                            /* translators: %s: comma-separated list of removed page titles */
                            __('Removed from a previous site type (skipped Trash): %s.', 'pressroot'),
                            $removed
                        ));
                    }
                    ?>
                </p>
            </div>
        <?php elseif ($regenerated !== '') : ?>
            <div class="notice notice-success is-dismissible">
                <p>
                    <?php
                    printf(
                        /* translators: %s: page title */
                        esc_html__('"%s" was refreshed with a new design and content.', 'pressroot'),
                        esc_html($regenerated)
                    );
                    ?>
                </p>
            </div>
        <?php elseif ($bulkRegenerated !== '' && isset($types[$bulkRegenerated])) : ?>
            <div class="notice notice-success is-dismissible">
                <p>
                    <?php
                    printf(
                        /* translators: %s: site type label */
                        esc_html__('All "%s" starter pages were refreshed with random new designs and content.', 'pressroot'),
                        esc_html($types[$bulkRegenerated]['label'])
                    );
                    ?>
                </p>
            </div>
        <?php elseif (isset($_GET['prt_ai_filled'])) : ?>
            <div class="notice notice-success is-dismissible"><p>
                <?php
                printf(esc_html__('✨ AI wrote %d lines of copy from your brand profile.', 'pressroot'), absint($_GET['prt_ai_filled']));
                if (! empty($_GET['prt_ai_fill_errors'])) {
                    echo ' ' . esc_html(sprintf(__('(%d pages could not be written — try again or switch models.)', 'pressroot'), absint($_GET['prt_ai_fill_errors'])));
                }
                ?>
            </p></div>
        <?php elseif (isset($_GET['prt_ai_img_done'])) : ?>
            <div class="notice notice-success is-dismissible"><p><?php esc_html_e('🖼 AI image generated, attached to the page, saved in the Media Library, and set as its featured image.', 'pressroot'); ?></p></div>
        <?php elseif (isset($_GET['prt_ai_fill_error'])) : ?>
            <div class="notice notice-error is-dismissible"><p><?php echo esc_html(sanitize_text_field(wp_unslash($_GET['prt_ai_fill_error']))); ?></p></div>
        <?php elseif ($connectorsUpdated) : ?>
            <div class="notice notice-success is-dismissible">
                <p><?php esc_html_e('AI connectors saved.', 'pressroot'); ?></p>
            </div>
        <?php endif; ?>

        <h2 style="margin-top:24px"><?php esc_html_e('1. Pick your site type', 'pressroot'); ?></h2>
        <p class="description" style="max-width:640px"><?php esc_html_e('Choose the kind of site you\'re building and apply it — your pages are created (or refreshed) below, where everything else happens: previews, designs, marketing questions, AI writing, and generated media.', 'pressroot'); ?></p>
        <form method="post" action="<?php echo esc_url($post); ?>" style="display:flex;gap:10px;align-items:center;flex-wrap:wrap;margin:14px 0 6px">
            <input type="hidden" name="action" value="prt_apply_site_type">
            <?php wp_nonce_field('prt_apply_site_type'); ?>
            <label class="screen-reader-text" for="prt-site-type-pick"><?php esc_html_e('Site type', 'pressroot'); ?></label>
            <select id="prt-site-type-pick" name="site_type" style="min-width:320px">
                <?php
                $appliedTypes = array_unique(array_filter(wp_list_pluck(prt_get_site_type_pages(), 'prt_site_type')));
                foreach ($types as $id => $type) :
                ?>
                    <option value="<?php echo esc_attr($id); ?>" <?php selected(in_array($id, $appliedTypes, true)); ?>>
                        <?php echo esc_html($type['label'] . ' — ' . $type['desc'] . (in_array($id, $appliedTypes, true) ? ' ✓' : '')); ?>
                    </option>
                <?php endforeach; ?>
            </select>
            <button class="button button-primary"><?php esc_html_e('Apply site type', 'pressroot'); ?></button>
            <span class="description"><?php esc_html_e('Re-applying the same type deals every page a fresh design.', 'pressroot'); ?></span>
        </form>

        <hr>

        <h2 id="prt-pages"><?php esc_html_e('2. Your pages & designs', 'pressroot'); ?></h2>
        <?php $setupSaved = (bool) get_option('prt_setup_saved'); ?>
        <?php if (! $setupSaved) : ?>
            <div class="notice notice-info inline" style="max-width:760px"><p>
                <?php printf(wp_kses_post(__('Page previews unlock after you save <a href="%s">Theme Settings</a> once — so previews always show YOUR settings, never the theme\'s stock branding.', 'pressroot')), esc_url(prt_settings_tab_url('settings'))); ?>
            </p></div>
        <?php endif; ?>
        <?php
        $sitePages = prt_get_site_type_pages();
        $byType    = [];
        foreach ($sitePages as $sp) {
            $byType[$sp->prt_site_type][] = $sp;
        }
        ?>
        <?php if (empty($sitePages)) : ?>
            <p class="description"><?php esc_html_e('Nothing yet — choose a site type above to create your first starter pages.', 'pressroot'); ?></p>
        <?php else : ?>
            <p class="description"><?php esc_html_e('Every page here is plain Gutenberg blocks — "Edit blocks" opens it in the block editor, your page builder. 🎲 deals a random new design (layout + placeholder content), ✨ has your selected AI write the real copy from the Brand tab answers, and neither ever touches the block markup itself, so nothing can break.', 'pressroot'); ?></p>
            <?php foreach ($byType as $typeId => $pages) : ?>
                <h3 style="margin:20px 0 6px;font-size:14px">
                    <?php echo esc_html($types[$typeId]['label'] ?? $typeId); ?>
                    <form method="post" action="<?php echo esc_url($post); ?>" style="display:inline;margin-left:10px">
                        <input type="hidden" name="action" value="prt_regenerate_site_type">
                        <input type="hidden" name="site_type" value="<?php echo esc_attr($typeId); ?>">
                        <?php wp_nonce_field('prt_regenerate_site_type'); ?>
                        <button class="button button-small">🎲 <?php esc_html_e('Refresh all — random new designs', 'pressroot'); ?></button>
                    </form>
                    <?php if (function_exists('App\\prt_ai_features_enabled') && prt_ai_features_enabled()) : ?>
                        <form method="post" action="<?php echo esc_url($post); ?>" style="display:inline;margin-left:6px">
                            <input type="hidden" name="action" value="prt_ai_fill_all">
                            <input type="hidden" name="site_type" value="<?php echo esc_attr($typeId); ?>">
                            <?php wp_nonce_field('prt_ai_fill_all'); ?>
                            <button class="button button-small">✨ <?php esc_html_e('AI-write all pages', 'pressroot'); ?></button>
                        </form>
                    <?php endif; ?>
                </h3>
                <?php if (function_exists('App\\prt_type_questions_html')) {
                    prt_type_questions_html($typeId, $types[$typeId]['label'] ?? $typeId);
                } ?>
                <table class="widefat striped" style="max-width:1080px;margin-bottom:12px">
                    <thead>
                        <tr>
                            <th style="width:14%"><?php esc_html_e('Page', 'pressroot'); ?></th>
                            <th style="width:6%"><?php esc_html_e('Design', 'pressroot'); ?></th>
                            <th style="width:22%"><?php esc_html_e('Preview', 'pressroot'); ?></th>
                            <th style="width:20%"><?php esc_html_e('Generated media', 'pressroot'); ?></th>
                            <th></th>
                        </tr>
                    </thead>
                    <tbody>
                        <?php foreach ($pages as $sp) : ?>
                            <tr>
                                <td><?php echo esc_html(get_the_title($sp)); ?></td>
                                <td><?php echo esc_html(strtoupper($sp->prt_variant)); ?></td>
                                <td>
                                    <?php if ($setupSaved) : ?>
                                        <div style="width:200px;height:120px;overflow:hidden;border-radius:6px;border:1px solid #e2e2e5;background:#fff;position:relative">
                                            <iframe class="prt-preview-frame"
                                                src="<?php echo esc_url(add_query_arg('v', (string) time(), get_preview_post_link($sp->ID))); ?>"
                                                title="<?php echo esc_attr(sprintf(__('%s preview', 'pressroot'), get_the_title($sp))); ?>"
                                                loading="lazy"
                                                style="width:1200px;height:720px;transform:scale(0.1667);transform-origin:0 0;border:0;pointer-events:none"></iframe>
                                        </div>
                                    <?php else : ?>
                                        <span class="description">—</span>
                                    <?php endif; ?>
                                </td>
                                <td>
                                    <?php
                                    $media = get_children(['post_parent' => $sp->ID, 'post_type' => 'attachment', 'numberposts' => 6, 'orderby' => 'date', 'order' => 'DESC']);
                                    if ($media) {
                                        echo '<div style="display:flex;gap:4px;flex-wrap:wrap;margin-bottom:6px">';
                                        foreach ($media as $att) {
                                            $isVideo = strpos((string) $att->post_mime_type, 'video/') === 0;
                                            echo '<a href="' . esc_url(get_edit_post_link($att->ID)) . '" title="' . esc_attr(get_the_title($att)) . '">';
                                            echo $isVideo
                                                ? '<span style="display:inline-flex;width:40px;height:40px;border-radius:6px;background:#17151F;color:#fff;align-items:center;justify-content:center">🎬</span>'
                                                : wp_get_attachment_image($att->ID, [40, 40], true, ['style' => 'border-radius:6px;object-fit:cover;width:40px;height:40px']);
                                            echo '</a>';
                                        }
                                        echo '</div>';
                                    } else {
                                        echo '<span class="description" style="display:block;margin-bottom:6px">' . esc_html__('None yet', 'pressroot') . '</span>';
                                    }
                                    if (function_exists('App\\prt_ai_features_enabled') && prt_ai_features_enabled()) : ?>
                                        <form method="post" action="<?php echo esc_url($post); ?>" style="margin:0">
                                            <input type="hidden" name="action" value="prt_ai_page_image">
                                            <input type="hidden" name="page_id" value="<?php echo (int) $sp->ID; ?>">
                                            <?php wp_nonce_field('prt_ai_page_image'); ?>
                                            <button class="button button-small">🖼 <?php esc_html_e('AI image', 'pressroot'); ?></button>
                                        </form>
                                    <?php endif; ?>
                                </td>
                                <td style="display:flex;gap:6px;flex-wrap:wrap;align-items:center">
                                    <form method="post" action="<?php echo esc_url($post); ?>" style="margin:0">
                                        <input type="hidden" name="action" value="prt_regenerate_site_type_page">
                                        <input type="hidden" name="page_id" value="<?php echo (int) $sp->ID; ?>">
                                        <?php wp_nonce_field('prt_regenerate_site_type_page'); ?>
                                        <button class="button">🎲 <?php esc_html_e('New design', 'pressroot'); ?></button>
                                    </form>
                                    <?php if (function_exists('App\\prt_ai_features_enabled') && prt_ai_features_enabled()) : ?>
                                        <form method="post" action="<?php echo esc_url($post); ?>" style="margin:0">
                                            <input type="hidden" name="action" value="prt_ai_fill_page">
                                            <input type="hidden" name="page_id" value="<?php echo (int) $sp->ID; ?>">
                                            <?php wp_nonce_field('prt_ai_fill_page'); ?>
                                            <button class="button">✨ <?php esc_html_e('Write with AI', 'pressroot'); ?></button>
                                        </form>
                                    <?php endif; ?>
                                    <a class="button" href="<?php echo esc_url(get_edit_post_link($sp->ID)); ?>"><?php esc_html_e('Edit blocks', 'pressroot'); ?></a>
                                </td>
                            </tr>
                        <?php endforeach; ?>
                    </tbody>
                </table>
            <?php endforeach; ?>
        <?php endif; ?>

        <hr>

        <?php if (! prt_ai_features_enabled()) : ?>
            <p class="description" style="max-width:640px">
                <?php esc_html_e('AI features are switched off (Theme Settings → Brand → "Powered by AI — or not"). Everything above keeps working — the design generator is built into the theme and never calls an AI service. Flip the switch back on to write starter copy with AI.', 'pressroot'); ?>
            </p>
        <?php else : ?>
        <h2><?php esc_html_e('3. Generate starter hero copy', 'pressroot'); ?></h2>
        <p class="description">
            <?php esc_html_e('Describe your business or site in a sentence and get a draft headline + subheadline you can paste into your Hero pattern.', 'pressroot'); ?>
            <a href="#prt-ai-advanced"><?php esc_html_e('Connect more AI models', 'pressroot'); ?></a>
        </p>
        <div id="prt-ai-copy" style="max-width:640px;margin-top:14px">
            <label for="prt-ai-copy-input" class="screen-reader-text"><?php esc_html_e('Describe your business', 'pressroot'); ?></label>
            <textarea id="prt-ai-copy-input" rows="2" style="width:100%" placeholder="<?php echo esc_attr__('e.g. a two-person branding studio for indie game developers', 'pressroot'); ?>"><?php echo function_exists('App\\prt_brand_profile') ? esc_textarea(prt_brand_profile()['desc']) : ''; ?></textarea>
            <p>
                <label for="prt-ai-copy-model" style="font-size:12px;color:#646970;display:block;margin-bottom:4px"><?php esc_html_e('AI model', 'pressroot'); ?></label>
                <select id="prt-ai-copy-model">
                    <?php foreach (prt_ai_configured_connectors() as $slug => $connector) : ?>
                        <option value="<?php echo esc_attr($slug); ?>">
                            <?php echo esc_html($connector['label'] . (! empty($connector['model']) ? ' — ' . $connector['model'] : '')); ?>
                        </option>
                    <?php endforeach; ?>
                </select>
            </p>
            <p>
                <button type="button" id="prt-ai-copy-go" class="button button-primary"><?php esc_html_e('Generate headline & subhead', 'pressroot'); ?></button>
            </p>
            <div id="prt-ai-copy-note" style="color:#646970;font-size:13px"></div>
            <div id="prt-ai-copy-result" style="display:none;margin-top:10px;border:1px solid #dcdcde;border-radius:8px;padding:16px;background:#fff">
                <p style="margin:0 0 6px"><strong id="prt-ai-copy-headline" style="font-size:18px;display:block"></strong></p>
                <p id="prt-ai-copy-subhead" style="margin:0 0 12px;color:#3c434a"></p>
                <button type="button" class="button" data-copy="headline"><?php esc_html_e('Copy headline', 'pressroot'); ?></button>
                <button type="button" class="button" data-copy="subhead"><?php esc_html_e('Copy subhead', 'pressroot'); ?></button>
                <button type="button" class="button" id="prt-ai-copy-regen"><?php esc_html_e('Regenerate', 'pressroot'); ?></button>
            </div>
        </div>

        <details id="prt-ai-advanced" style="margin-top:28px;max-width:760px;border:1px solid #dcdcde;border-radius:8px;padding:4px 16px;background:#fff" <?php echo $connectorsUpdated ? 'open' : ''; ?>>
            <summary style="cursor:pointer;padding:12px 0;font-weight:600"><?php esc_html_e('Advanced: Connect more AI models', 'pressroot'); ?></summary>
            <div style="padding-bottom:16px">
                <?php prt_ai_connectors_fields_html(); ?>
            </div>
        </details>

        <?php endif; ?>

        <script>
        (function () {
            // Hidden live updating: whenever this admin tab becomes visible
            // again (e.g. after saving Brand/Theme Settings in another tab),
            // silently re-fetch every preview box with a fresh cache-buster.
            function refresh() {
                document.querySelectorAll('.prt-preview-frame').forEach(function (f) {
                    try {
                        var u = new URL(f.src);
                        u.searchParams.set('v', Date.now().toString());
                        f.src = u.toString();
                    } catch (e) {}
                });
            }
            document.addEventListener('visibilitychange', function () {
                if (! document.hidden) { refresh(); }
            });
        })();
        </script>

        <details id="prt-settings-advanced" style="margin-top:12px;max-width:760px;border:1px solid #dcdcde;border-radius:8px;padding:4px 16px;background:#fff" <?php echo $backupUpdated ? 'open' : ''; ?>>
            <summary style="cursor:pointer;padding:12px 0;font-weight:600"><?php esc_html_e('Advanced: Backup & restore settings', 'pressroot'); ?></summary>
            <div style="padding-bottom:16px">
                <?php prt_settings_backup_fields_html(); ?>
            </div>
        </details>
    <?php
}

/**
 * Apply a site type: set the matching Style Kit (reusing prt_apply_style_kit()
 * from settings-io.php, so this stays the one place that knows how to apply a
 * kit), then create any of its starter pages that don't already exist yet
 * (matched by post_name/slug — existing pages, published or draft, are never
 * modified or duplicated). New pages are created as drafts pre-filled with
 * variant A of that page's dedicated site-type pattern (pulled straight from
 * WP_Block_Patterns_Registry, so it always matches what a user would see if
 * they inserted that pattern by hand from the editor), and tagged with post
 * meta (_prt_site_type / _prt_page_role / _prt_pattern_variant) so the
 * Regenerate tool below can find them again later.
 *
 * SWITCHING site types: before creating the newly-chosen type's pages, every
 * page a DIFFERENT site type previously created (tracked via the same
 * _prt_site_type meta) is force-deleted — bypassing Trash entirely, not just
 * moved there — so the owner gets a clean set of starter pages for whichever
 * type they land on, instead of every type they've ever clicked piling up.
 * Only ever touches pages this tool tagged itself; anything the owner made or
 * edited by hand is left alone.
 */
add_action('admin_post_prt_apply_site_type', function () {
    prt_require_admin_post('prt_apply_site_type');
    if (! prt_addon_enabled('pressroot_ai')) {
        wp_die(__('Pressroot AI is currently disabled in Theme Options -> Theme Addons.', 'pressroot'));
    }

    $id    = isset($_POST['site_type']) ? sanitize_key($_POST['site_type']) : '';
    $types = prt_site_types();

    if (! isset($types[$id])) {
        wp_safe_redirect(prt_settings_tab_url('ai'));
        exit;
    }

    $removed = [];
    foreach (prt_get_site_type_pages() as $old) {
        if ($old->prt_site_type !== $id) {
            $removed[] = get_the_title($old);
            wp_delete_post($old->ID, true); // true = force delete, skip Trash.
        }
    }
    // Defensive: also purge anything of this tool's making already sitting in
    // Trash from an earlier manual trash action, so it never lingers there.
    foreach (get_posts(['post_type' => 'page', 'post_status' => 'trash', 'numberposts' => -1, 'meta_key' => '_prt_site_type', 'fields' => 'ids']) as $trashedId) {
        wp_delete_post($trashedId, true);
    }

    $type = $types[$id];
    // Deal a random design kit from this type's pool (brand-profile aware)
    // so the SITE-WIDE design regenerates too — falls back to the type's
    // classic kit if the remix engine is unavailable.
    if (function_exists('App\\prt_apply_random_site_kit')) {
        prt_apply_random_site_kit($id, $type);
    } else {
        prt_apply_style_kit($type['kit']);
    }

    $created   = [];
    $refreshed = [];
    $registry = class_exists('WP_Block_Patterns_Registry') ? \WP_Block_Patterns_Registry::get_instance() : null;

    foreach ($type['pages'] as $page) {
        // A random variant per page on every apply, so re-choosing the SAME
        // site type still produces a genuinely new overall design each time.
        $variant  = prt_pick_random_variant($page);
        $variants = prt_site_type_page_variants($page);
        $pattern  = $registry ? $registry->get_registered($variants[$variant] ?? $page['pattern_a']) : null;
        $content  = $pattern['content'] ?? '';

        $existing = get_posts([
            'post_type'      => 'page',
            'name'           => $page['slug'],
            'post_status'    => ['publish', 'draft', 'pending', 'private', 'future'],
            'numberposts'    => 1,
            'fields'         => 'ids',
        ]);

        if (! empty($existing)) {
            // The page already exists (from an earlier apply of this or an
            // older build of the theme): REFRESH it — overwrite its content
            // with the current pattern markup so old baked-in design never
            // lingers — instead of skipping it like this tool used to.
            $pageId = (int) $existing[0];
            wp_update_post(['ID' => $pageId, 'post_content' => $content]);
            update_post_meta($pageId, '_prt_site_type', $id);
            update_post_meta($pageId, '_prt_page_role', $page['role']);
            update_post_meta($pageId, '_prt_pattern_variant', $variant);
            $refreshed[] = $page['title'];
            continue;
        }

        $newId = wp_insert_post([
            'post_type'    => 'page',
            'post_status'  => 'draft', // Left as a draft so the owner reviews/publishes deliberately.
            'post_title'   => $page['title'],
            'post_name'    => $page['slug'],
            'post_content' => $content,
        ]);

        if ($newId && ! is_wp_error($newId)) {
            update_post_meta($newId, '_prt_site_type', $id);
            update_post_meta($newId, '_prt_page_role', $page['role']);
            update_post_meta($newId, '_prt_pattern_variant', $variant);
        }

        $created[] = $page['title'];
    }

    // Build the site CHROME from the same answers: nav menu (Home + these
    // pages), goal-driven header CTA, brand-driven footer.
    if (function_exists('App\\prt_build_site_chrome')) {
        prt_build_site_chrome($id, $type);
    }

    // Setup complete -> refresh branding last, so the owner's color/identity
    // wins over whatever kit+trend were just dealt.
    if (function_exists('App\\prt_refresh_branding')) {
        prt_refresh_branding();
    }
    if (function_exists('App\\prt_prime_smart_copy')) {
        prt_prime_smart_copy(); // smart blocks get fresh auto-copy per build
    }
    prt_flush_design_caches();

    // Honors a posted prt_return_tab/prt_return_step (the Setup wizard posts
    // these so its stage-A form comes back to step 4); defaults unchanged.
    wp_safe_redirect(prt_settings_return_url('ai', [
        'prt_site_type_result'  => $id,
        'prt_created'           => rawurlencode(implode(', ', $created)),
        'prt_refreshed'         => rawurlencode(implode(', ', $refreshed)),
        'prt_removed'           => rawurlencode(implode(', ', $removed)),
    ]));
    exit;
});

/**
 * Regenerate one starter page: look up which site type/page-role/variant it
 * was tagged with when created, swap to the OTHER variant's pattern content,
 * and flip the stored variant meta so clicking Regenerate again toggles back.
 * Only ever touches the one page whose ID was posted — every other page and
 * every other setting is untouched.
 */
add_action('admin_post_prt_regenerate_site_type_page', function () {
    prt_require_admin_post('prt_regenerate_site_type_page');
    if (! prt_addon_enabled('pressroot_ai')) {
        wp_die(__('Pressroot AI is currently disabled in Theme Options -> Theme Addons.', 'pressroot'));
    }

    $pageId = isset($_POST['page_id']) ? absint($_POST['page_id']) : 0;
    $page   = $pageId ? get_post($pageId) : null;

    if (! $page || $page->post_type !== 'page') {
        wp_safe_redirect(prt_settings_tab_url('ai'));
        exit;
    }

    $siteType = get_post_meta($pageId, '_prt_site_type', true);
    $role     = get_post_meta($pageId, '_prt_page_role', true);
    $variant  = get_post_meta($pageId, '_prt_pattern_variant', true) ?: 'a';

    $types = prt_site_types();
    $def   = null;
    foreach (($types[$siteType]['pages'] ?? []) as $candidate) {
        if ($candidate['role'] === $role) {
            $def = $candidate;
            break;
        }
    }

    if (! $def) {
        // This page wasn't created by (or no longer matches) a known site
        // type/page-role combination — nothing safe to regenerate it into.
        wp_safe_redirect(prt_settings_tab_url('ai'));
        exit;
    }

    $nextVariant = prt_pick_random_variant($def, $variant);
    $patternSlug = prt_site_type_page_variants($def)[$nextVariant] ?? $def['pattern_a'];

    $registry = class_exists('WP_Block_Patterns_Registry') ? \WP_Block_Patterns_Registry::get_instance() : null;
    $pattern  = $registry ? $registry->get_registered($patternSlug) : null;

    if ($pattern && isset($pattern['content'])) {
        // Overwrites BOTH design and content with the fresh pattern markup —
        // a full refresh, not just a re-skin.
        wp_update_post([
            'ID'           => $pageId,
            'post_content' => $pattern['content'],
        ]);
        update_post_meta($pageId, '_prt_pattern_variant', $nextVariant);
        prt_flush_design_caches();
    }

    wp_safe_redirect(prt_settings_tab_url('ai', [
        'prt_regenerated' => rawurlencode(get_the_title($pageId)),
    ]));
    exit;
});

/**
 * Regenerate EVERY starter page belonging to one site type in a single click
 * — the "different overall design" refresh option. Internally this just
 * calls the same one-page toggle logic above for each page tagged with the
 * posted site type, so it's exactly as safe: only pages tagged with that
 * site type are touched, each swapping to its other hand-built variant.
 */
add_action('admin_post_prt_regenerate_site_type', function () {
    prt_require_admin_post('prt_regenerate_site_type');
    if (! prt_addon_enabled('pressroot_ai')) {
        wp_die(__('Pressroot AI is currently disabled in Theme Options -> Theme Addons.', 'pressroot'));
    }

    $id    = isset($_POST['site_type']) ? sanitize_key($_POST['site_type']) : '';
    $types = prt_site_types();

    if (! isset($types[$id])) {
        wp_safe_redirect(prt_settings_tab_url('ai'));
        exit;
    }

    $registry = class_exists('WP_Block_Patterns_Registry') ? \WP_Block_Patterns_Registry::get_instance() : null;

    foreach (prt_get_site_type_pages() as $sp) {
        if ($sp->prt_site_type !== $id) {
            continue;
        }

        $def = null;
        foreach ($types[$id]['pages'] as $candidate) {
            if ($candidate['role'] === $sp->prt_role) {
                $def = $candidate;
                break;
            }
        }
        if (! $def) {
            continue;
        }

        // Random pick (never the page's current variant) so each "Refresh
        // design" click deals a genuinely different layout per page —
        // designs AND content are replaced with the fresh pattern markup.
        $nextVariant = prt_pick_random_variant($def, (string) $sp->prt_variant);
        $patternSlug = prt_site_type_page_variants($def)[$nextVariant] ?? $def['pattern_a'];
        $pattern     = $registry ? $registry->get_registered($patternSlug) : null;

        if ($pattern && isset($pattern['content'])) {
            wp_update_post([
                'ID'           => $sp->ID,
                'post_content' => $pattern['content'],
            ]);
            update_post_meta($sp->ID, '_prt_pattern_variant', $nextVariant);
        }
    }

    // A category refresh regenerates the whole THEME, not just the pages:
    // re-deal the site-wide design kit too (palette, fonts, radii), and
    // keep the chrome (nav / header CTA / footer) in sync.
    if (function_exists('App\\prt_apply_random_site_kit')) {
        prt_apply_random_site_kit($id, $types[$id]);
    }
    if (function_exists('App\\prt_build_site_chrome')) {
        prt_build_site_chrome($id, $types[$id]);
    }

    if (function_exists('App\\prt_refresh_branding')) {
        prt_refresh_branding();
    }
    prt_flush_design_caches();

    wp_safe_redirect(prt_settings_tab_url('ai', [
        'prt_bulk_regenerated' => $id,
    ]));
    exit;
});
