<?php

namespace App;

/**
 * Site-type REMIX engine — turns the Site Types tool from "toggle between
 * two hand-built pages" into a design generator that deals a whole new
 * THEME on every refresh:
 *
 * 1. REMIX PATTERNS (variants C + D for every page of every site type) —
 *    composed programmatically from a pool of hero / feature / CTA section
 *    builders in the Repofolio design language. Which sections a given
 *    page's C/D variant uses is seeded from the type+role, so all ~50
 *    generated patterns genuinely differ. Combined with the hand-built A/B
 *    variants, every page now has FOUR designs to deal between.
 *
 * 2. DESIGN KITS PER REFRESH — each site type maps to a POOL of style kits
 *    (see the new Repofolio-family kits in app/settings-io.php). Applying
 *    or bulk-refreshing a site type applies a random kit from the pool —
 *    different from the one currently active — so the SITE-WIDE design
 *    (palette, fonts, radii) regenerates too, not just page content. Kits
 *    are pure theme-mod/CSS-variable swaps: instant, no build step.
 *
 * 3. BRAND PROFILE — a short questionnaire on the Site Types tab (business
 *    name, one-line description, brand color, light/dark, vibe). Answers
 *    steer generation: the vibe + light/dark answers filter which kits can
 *    be dealt, the brand color overrides the kit's action color after
 *    apply, and the AI copy prompt picks the profile up automatically
 *    (see prt_ai_build_hero_prompt in app/ai-connectors.php).
 *
 * Loaded after the site-type-*.php pattern files (see functions.php).
 */

/* ─────────────────────────── Brand profile ─────────────────────────── */

/** The saved brand questionnaire answers, with safe defaults. */
function prt_brand_profile(): array
{
    return [
        'name'     => (string) get_theme_mod('prt_brand_name', ''),
        'desc'     => (string) get_theme_mod('prt_brand_desc', ''),
        'color'    => (string) get_theme_mod('prt_brand_color', ''),
        'mode'     => (string) get_theme_mod('prt_brand_mode', 'either'),   // light | dark | either
        'vibe'     => (string) get_theme_mod('prt_brand_vibe', 'any'),      // bold | minimal | warm | playful | any
        // Round 2 of the questionnaire — everything below feeds the AI
        // prompts (copy + images) and the design-trend randomizer.
        'audience' => (string) get_theme_mod('prt_brand_audience', ''),
        'industry' => (string) get_theme_mod('prt_brand_industry', ''),
        'tone'     => (string) get_theme_mod('prt_brand_tone', ''),
        'goal'     => (string) get_theme_mod('prt_brand_goal', 'leads'),    // leads | sell | book | read
        'imagery'  => (string) get_theme_mod('prt_brand_imagery', 'photo'), // photo | illustration | abstract | none
        'density'  => (string) get_theme_mod('prt_brand_density', 'balanced'), // minimal | balanced | rich
        'trend'    => (string) get_theme_mod('prt_brand_trend', 'any'),     // any | a prt_design_trends() slug
    ];
}

/** Save handler for the questionnaire (form rendered by prt_brand_tab_html() below — the "Brand" tab on Appearance -> Pressroot). */
add_action('admin_post_prt_save_brand_profile', function () {
    if (! current_user_can('edit_theme_options') || ! check_admin_referer('prt_save_brand_profile')) {
        wp_die(__('Not allowed.', 'pressroot'));
    }
    set_theme_mod('prt_brand_name', sanitize_text_field(wp_unslash($_POST['prt_brand_name'] ?? '')));
    set_theme_mod('prt_brand_desc', sanitize_text_field(wp_unslash($_POST['prt_brand_desc'] ?? '')));
    $color = sanitize_hex_color(wp_unslash($_POST['prt_brand_color'] ?? ''));
    set_theme_mod('prt_brand_color', $color ?: '');
    $mode = sanitize_key($_POST['prt_brand_mode'] ?? 'either');
    set_theme_mod('prt_brand_mode', in_array($mode, ['light', 'dark', 'either'], true) ? $mode : 'either');
    $vibe = sanitize_key($_POST['prt_brand_vibe'] ?? 'any');
    set_theme_mod('prt_brand_vibe', in_array($vibe, ['bold', 'minimal', 'warm', 'playful', 'any'], true) ? $vibe : 'any');

    // Round-2 questionnaire answers.
    set_theme_mod('prt_brand_audience', sanitize_text_field(wp_unslash($_POST['prt_brand_audience'] ?? '')));
    $industrySel = sanitize_text_field(wp_unslash($_POST['prt_brand_industry_sel'] ?? ''));
    $industry    = $industrySel === '__other'
        ? sanitize_text_field(wp_unslash($_POST['prt_brand_industry_other'] ?? ''))
        : $industrySel;
    set_theme_mod('prt_brand_industry', $industry);
    set_theme_mod('prt_brand_tone', sanitize_text_field(wp_unslash($_POST['prt_brand_tone'] ?? '')));
    $goal = sanitize_key($_POST['prt_brand_goal'] ?? 'leads');
    set_theme_mod('prt_brand_goal', in_array($goal, ['leads', 'sell', 'book', 'read'], true) ? $goal : 'leads');
    $imagery = sanitize_key($_POST['prt_brand_imagery'] ?? 'photo');
    set_theme_mod('prt_brand_imagery', in_array($imagery, ['photo', 'illustration', 'abstract', 'none'], true) ? $imagery : 'photo');
    $density = sanitize_key($_POST['prt_brand_density'] ?? 'balanced');
    set_theme_mod('prt_brand_density', in_array($density, ['minimal', 'balanced', 'rich'], true) ? $density : 'balanced');
    $trend = sanitize_key($_POST['prt_brand_trend'] ?? 'any');
    set_theme_mod('prt_brand_trend', ($trend === 'any' || isset(prt_design_trends()[$trend])) ? $trend : 'any');

    // Design changed inputs -> make sure nothing stale keeps painting.
    if (function_exists('App\\prt_flush_design_caches')) {
        prt_flush_design_caches();
    }

    // "Powered by AI — or not": the friendly switch for every feature that
    // actually calls an AI service (see prt_ai_features_enabled()).
    set_theme_mod('prt_ai_features_on', ! empty($_POST['prt_ai_features_on']));

    // Easy mode: apply the answers to the site itself, no Customizer trip
    // needed — business name becomes the Site Title, the one-liner becomes
    // the tagline. Opt-in via checkbox so we never clobber existing values
    // unasked.
    if (! empty($_POST['prt_brand_apply_site'])) {
        $name = sanitize_text_field(wp_unslash($_POST['prt_brand_name'] ?? ''));
        $desc = sanitize_text_field(wp_unslash($_POST['prt_brand_desc'] ?? ''));
        if ($name !== '') {
            update_option('blogname', $name);
        }
        if ($desc !== '') {
            update_option('blogdescription', $desc);
        }
    }

    update_option('prt_setup_saved', 1);

    wp_safe_redirect(prt_settings_tab_url('settings', ['prt_settings_saved' => '1']));
    exit;
});

/* ──────────────────── Kit pools + random kit dealing ─────────────────── */

/** Which style kits each site type may be dealt. First entry = the classic default. */
function prt_site_type_kit_pools(): array
{
    return apply_filters('pressroot/site_type_kit_pools', [
        'agency'     => ['paper_space', 'iris_dark', 'pink_pop', 'cyan_sky'],
        'freelance'  => ['editorial', 'coral_cream', 'mint_fresh', 'paper_space'],
        'saas'       => ['midnight', 'iris_dark', 'cyan_sky', 'mono_slate'],
        'blog'       => ['warm_sand', 'amber_toast', 'coral_cream', 'editorial'],
        // 'core_marketing' is RESERVED for this type — it never appears in
        // any other pool, so the core marketing look stays distinct.
        'marketing'  => ['core_marketing', 'mono_slate', 'pink_pop', 'cyan_sky'],
        'affiliate'  => ['paper_space', 'pink_pop', 'amber_toast', 'mint_fresh'],
        'restaurant' => ['warm_sand', 'coral_cream', 'amber_toast', 'iris_dark'],
        'realty'     => ['editorial', 'cyan_sky', 'mono_slate', 'paper_space'],
    ]);
}

/**
 * Deal a random style kit for a site type — never the one dealt last time,
 * filtered by the brand profile's light/dark + vibe answers when they
 * narrow the field, with the brand color applied on top afterwards.
 * Returns the applied kit slug.
 */
function prt_apply_random_site_kit(string $typeId, array $type): string
{
    $kits    = prt_style_kits();
    $pool    = prt_site_type_kit_pools()[$typeId] ?? [$type['kit'] ?? 'paper_space'];
    $pool    = array_values(array_filter($pool, fn ($slug) => isset($kits[$slug])));
    $brand   = prt_brand_profile();

    // Brand answers narrow the pool (only when the filter leaves options).
    if ($brand['mode'] !== 'either') {
        $filtered = array_values(array_filter($pool, fn ($slug) => ($kits[$slug]['mode'] ?? 'light') === $brand['mode']));
        if ($filtered) {
            $pool = $filtered;
        }
    }
    if ($brand['vibe'] !== 'any') {
        $filtered = array_values(array_filter($pool, fn ($slug) => in_array($brand['vibe'], $kits[$slug]['vibes'] ?? [], true)));
        if ($filtered) {
            $pool = $filtered;
        }
    }

    $current = (string) get_option('prt_active_site_kit', '');
    $choices = array_values(array_diff($pool, [$current])) ?: $pool;
    $slug    = $choices ? $choices[array_rand($choices)] : ($type['kit'] ?? 'paper_space');

    prt_apply_style_kit($slug);
    update_option('prt_active_site_kit', $slug);
    prt_apply_random_design_trend();

    // Brand color wins over the kit's action color — the one non-negotiable
    // piece of the owner's branding, applied after every re-deal.
    if ($brand['color'] !== '') {
        set_theme_mod('prt_color_action', $brand['color']);
    }

    return $slug;
}

/**
 * Re-assert the owner's branding AFTER a complete setup (called at the end
 * of apply/refresh in app/ai-assistant.php): whatever kit and trend were
 * just dealt, the brand color, site title/tagline (if the owner opted in
 * on the Brand tab), and fresh caches are applied last so branding always
 * wins the cascade.
 */
function prt_refresh_branding(): void
{
    $brand = prt_brand_profile();
    if ($brand['color'] !== '') {
        set_theme_mod('prt_color_action', $brand['color']);
    }
    if (function_exists('App\\prt_flush_design_caches')) {
        prt_flush_design_caches();
    }
}

/* ────────── Design trends — randomized per refresh, brand-aware ────────── */

/**
 * Current design trends the randomizer can deal. Each is a pure CSS layer
 * (see the .prt-trend-* rules in resources/css/app.css) applied via a body
 * class — instant, no build step, freely combinable with any Style Kit.
 */
function prt_design_trends(): array
{
    return apply_filters('pressroot/design_trends', [
        'bento'     => ['label' => __('Bento spectrum', 'pressroot'),  'vibes' => ['bold', 'playful'],   'desc' => __('The Repofolio signature: spectrum-topped cards, gradient pills.', 'pressroot')],
        'glass'     => ['label' => __('Glassmorphism', 'pressroot'),   'vibes' => ['minimal', 'playful'], 'desc' => __('Frosted translucent cards with soft depth.', 'pressroot')],
        'brutalist' => ['label' => __('Neo-brutalist', 'pressroot'),   'vibes' => ['bold'],               'desc' => __('Chunky ink borders, hard offset shadows, zero blur.', 'pressroot')],
        'editorial' => ['label' => __('Editorial serif', 'pressroot'), 'vibes' => ['minimal', 'warm'],    'desc' => __('Magazine rules: serif headings, hairline dividers.', 'pressroot')],
        'minimal'   => ['label' => __('Swiss minimal', 'pressroot'),   'vibes' => ['minimal'],            'desc' => __('Quiet surfaces, tight radii, no decoration.', 'pressroot')],
        'retro_pop' => ['label' => __('Retro pop', 'pressroot'),       'vibes' => ['playful', 'warm'],    'desc' => __('Thick outlines and candy offset shadows.', 'pressroot')],
    ]);
}

/**
 * Deal a design trend alongside the kit: fixed if the brand chose one,
 * otherwise random from the trends matching the brand vibe (never the one
 * currently active). Stored in prt_active_design_trend and applied on the
 * front end as a body class by the filter below.
 */
function prt_apply_random_design_trend(): string
{
    $trends = prt_design_trends();
    $brand  = prt_brand_profile();

    if ($brand['trend'] !== 'any' && isset($trends[$brand['trend']])) {
        update_option('prt_active_design_trend', $brand['trend']);
        return $brand['trend'];
    }

    $pool = array_keys($trends);
    if ($brand['vibe'] !== 'any') {
        $filtered = array_values(array_filter($pool, fn ($t) => in_array($brand['vibe'], $trends[$t]['vibes'], true)));
        if ($filtered) {
            $pool = $filtered;
        }
    }
    $current = (string) get_option('prt_active_design_trend', 'bento');
    $choices = array_values(array_diff($pool, [$current])) ?: $pool;
    $slug    = $choices[array_rand($choices)];
    update_option('prt_active_design_trend', $slug);
    return $slug;
}

/** Front end wears the active trend as a body class (styled in app.css). */
add_filter('body_class', function (array $classes): array {
    $trend = (string) get_option('prt_active_design_trend', 'bento');
    if (isset(prt_design_trends()[$trend])) {
        $classes[] = 'prt-trend-' . $trend;
    }
    return $classes;
});

/* ──────────── Per-site-type marketing questionnaires ──────────── */

/**
 * Extra questions per site type, modeled on what the category leaders
 * actually put above the fold (Wirecutter-style methodology for affiliate,
 * Zillow-style trust stats for realty, OpenTable-style logistics for
 * restaurants, Unbounce-style offer/proof for landing pages...). Answers
 * are stored per type and fed straight into every AI-write prompt, so the
 * generated copy sounds like the industry, not like a template.
 */
function prt_site_type_questions(): array
{
    return apply_filters('pressroot/site_type_questions', [
        'marketing' => [
            ['offer',     __('The offer, in one line', 'pressroot'),           __('e.g. 20% off first service for new customers', 'pressroot')],
            ['proof',     __('Best proof number', 'pressroot'),                __('e.g. 2,300 customers · 4.9★ on Google · $1.2M saved', 'pressroot')],
            ['cta',       __('What should visitors DO?', 'pressroot'),         __('e.g. book a free 15-minute call', 'pressroot')],
            ['objection', __('Biggest objection to overcome', 'pressroot'),    __('e.g. "it is probably too expensive for us"', 'pressroot')],
        ],
        'affiliate' => [
            ['niche',       __('Your niche', 'pressroot'),                     __('e.g. home espresso gear', 'pressroot')],
            ['categories',  __('Top product categories', 'pressroot'),         __('e.g. machines, grinders, accessories', 'pressroot')],
            ['networks',    __('Affiliate networks you use', 'pressroot'),     __('e.g. Amazon Associates, ShareASale, Impact', 'pressroot')],
            ['methodology', __('How you test (your Wirecutter line)', 'pressroot'), __('e.g. we buy retail and test for 30 days minimum', 'pressroot')],
        ],
        'realty' => [
            ['area',      __('Service area / neighborhoods', 'pressroot'),     __('e.g. Gettysburg + Adams County', 'pressroot')],
            ['brokerage', __('Brokerage & license', 'pressroot'),              __('e.g. RE/MAX of Gettysburg · PA license RS-123456', 'pressroot')],
            ['stats',     __('Trust stats (the Zillow bar)', 'pressroot'),     __('e.g. 340 homes sold · 11-day avg to contract · 101% of ask', 'pressroot')],
            ['valuation', __('Free-valuation offer wording', 'pressroot'),     __('e.g. free CMA within 24 hours, no strings', 'pressroot')],
        ],
        'restaurant' => [
            ['cuisine',      __('Cuisine & price range', 'pressroot'),         __('e.g. wood-fired American, $$–$$$', 'pressroot')],
            ['reservations', __('Reservations link (OpenTable/Resy)', 'pressroot'), __('paste the booking URL', 'pressroot')],
            ['hours',        __('Hours, one line', 'pressroot'),               __('e.g. Tue–Sun 5–10pm, Sunday brunch 10–2', 'pressroot')],
            ['signature',    __('Signature dish', 'pressroot'),                __('e.g. the 48-hour short rib', 'pressroot')],
        ],
        'saas' => [
            ['pricing',      __('Pricing tiers, one line', 'pressroot'),       __('e.g. Free / $19 Pro / $49 Team', 'pressroot')],
            ['trial',        __('Trial or freemium?', 'pressroot'),            __('e.g. 14-day trial, no card required', 'pressroot')],
            ['integrations', __('Key integrations', 'pressroot'),              __('e.g. Slack, HubSpot, Zapier', 'pressroot')],
        ],
        'blog' => [
            ['topics',     __('Main topics', 'pressroot'),                     __('e.g. slow living, home coffee, craft', 'pressroot')],
            ['leadmagnet', __('Newsletter hook', 'pressroot'),                 __('e.g. the Sunday 5-link letter', 'pressroot')],
        ],
        'agency' => [
            ['services',   __('Core services', 'pressroot'),                   __('e.g. brand, web design, SEO retainers', 'pressroot')],
            ['industries', __('Industries you serve best', 'pressroot'),       __('e.g. restaurants, healthcare, B2B SaaS', 'pressroot')],
            ['minimum',    __('Typical project minimum', 'pressroot'),         __('e.g. projects from $5k', 'pressroot')],
        ],
        'freelance' => [
            ['specialty', __('Specialty', 'pressroot'),                        __('e.g. WordPress performance rescues', 'pressroot')],
            ['rates',     __('Rate model', 'pressroot'),                       __('e.g. fixed-price projects from $2k', 'pressroot')],
        ],
    ]);
}

/** Saved answers for one type. */
function prt_type_answers(string $typeId): array
{
    $a = get_option('prt_typeq_' . $typeId, []);
    return is_array($a) ? $a : [];
}

add_action('admin_post_prt_save_type_questions', function () {
    if (! current_user_can('edit_theme_options') || ! check_admin_referer('prt_save_type_questions')) {
        wp_die(__('Not allowed.', 'pressroot'));
    }
    $typeId = sanitize_key($_POST['site_type'] ?? '');
    $qs     = prt_site_type_questions()[$typeId] ?? [];
    $out    = [];
    foreach ($qs as [$key]) {
        $out[$key] = sanitize_text_field(wp_unslash($_POST['q_' . $key] ?? ''));
    }
    update_option('prt_typeq_' . $typeId, $out);
    wp_safe_redirect(prt_settings_tab_url('ai', ['prt_typeq_saved' => $typeId]));
    exit;
});

/** Questionnaire card for one applied site type (rendered on the Site Types tab). */
function prt_type_questions_html(string $typeId, string $typeLabel): void
{
    $qs = prt_site_type_questions()[$typeId] ?? [];
    if (! $qs) {
        return;
    }
    $answers = prt_type_answers($typeId);
    $justSaved = isset($_GET['prt_typeq_saved']) && $_GET['prt_typeq_saved'] === $typeId;
    ?>
    <details style="max-width:760px;margin:4px 0 16px;border:1px solid #dcdcde;border-radius:8px;padding:4px 16px;background:#fff" <?php echo $justSaved ? 'open' : ''; ?>>
        <summary style="cursor:pointer;padding:10px 0;font-weight:600">📋 <?php printf(esc_html__('%s marketing questions (what the category leaders ask)', 'pressroot'), esc_html($typeLabel)); ?></summary>
        <?php if ($justSaved) : ?>
            <div class="notice notice-success inline"><p><?php esc_html_e('Saved — AI-write will use these from now on.', 'pressroot'); ?></p></div>
        <?php endif; ?>
        <form method="post" action="<?php echo esc_url(admin_url('admin-post.php')); ?>" style="padding-bottom:14px">
            <input type="hidden" name="action" value="prt_save_type_questions">
            <input type="hidden" name="site_type" value="<?php echo esc_attr($typeId); ?>">
            <?php wp_nonce_field('prt_save_type_questions'); ?>
            <table class="form-table" role="presentation" style="margin-top:0">
                <?php foreach ($qs as [$key, $label, $ph]) : ?>
                    <tr>
                        <th scope="row" style="padding:8px 10px 8px 0"><label for="q_<?php echo esc_attr($key); ?>"><?php echo esc_html($label); ?></label></th>
                        <td style="padding:8px 0"><input type="text" id="q_<?php echo esc_attr($key); ?>" name="q_<?php echo esc_attr($key); ?>" class="large-text" value="<?php echo esc_attr($answers[$key] ?? ''); ?>" placeholder="<?php echo esc_attr($ph); ?>"></td>
                    </tr>
                <?php endforeach; ?>
            </table>
            <button class="button button-primary"><?php esc_html_e('Save answers', 'pressroot'); ?></button>
            <span class="description" style="margin-left:8px"><?php esc_html_e('Feeds every ✨ AI-write for this site type.', 'pressroot'); ?></span>
        </form>
    </details>
    <?php
}

/* ─────────────── Remix pattern generation (variants C + D) ─────────────── */

/**
 * Extend every site type page definition with the generated C/D variant
 * slugs, so prt_site_type_page_variants() (app/ai-assistant.php) finds four
 * designs per page instead of two. Runs through the existing site_types
 * filter — no site-type file needs editing to participate.
 */
add_filter('pressroot/site_types', function (array $types): array {
    foreach ($types as $typeId => &$type) {
        foreach ($type['pages'] as &$page) {
            $page['pattern_c'] = 'prt-site/' . $typeId . '-' . $page['role'] . '-c';
            $page['pattern_d'] = 'prt-site/' . $typeId . '-' . $page['role'] . '-d';
        }
    }
    return $types;
}, 20);

/* ──────────────── Site chrome: nav menu, header CTA, footer ────────────────
 *
 * A site build is more than page content — this wires the WHOLE shell from
 * the same brand answers:
 *   NAV    — a generated "Pressroot Menu" (Home + every starter page, in
 *            order) assigned to the primary location. The builder only ever
 *            manages its OWN menu, so a hand-made menu is never touched.
 *   HEADER — the CTA button text/URL derive from the brand goal (Get a
 *            quote / Shop now / Book now / Subscribe) and point at the most
 *            relevant generated page.
 *   FOOTER — tagline from the brand description, ground + text colors from
 *            the light/dark answer.
 * Runs on every site-type apply/refresh and on Theme Settings auto-build.
 */

/** The header CTA, derived from brand goal + generated pages (mods override). */
function prt_header_cta(): array
{
    $b     = prt_brand_profile();
    $texts = ['leads' => __('Get a quote', 'pressroot'), 'sell' => __('Shop now', 'pressroot'), 'book' => __('Book now', 'pressroot'), 'read' => __('Subscribe', 'pressroot')];
    $text  = (string) get_theme_mod('prt_header_cta_text', '') ?: ($texts[$b['goal']] ?? $texts['leads']);
    $url   = (string) get_theme_mod('prt_header_cta_url', '');
    if ($url === '') {
        foreach (['reservations', 'contact', 'top-picks'] as $slug) {
            $pg = get_page_by_path($slug);
            if ($pg) {
                $url = get_permalink($pg);
                break;
            }
        }
        $url = $url ?: home_url('/contact');
    }
    return ['text' => $text, 'url' => $url];
}

/** Build/refresh the generated nav menu + header CTA + footer for a type. */
function prt_build_site_chrome(string $typeId = '', array $type = []): void
{
    $b = prt_brand_profile();

    // ── NAV: sync our own menu with Home + the generated pages.
    $menuName = 'Pressroot Menu';
    $menuId   = (int) get_option('prt_generated_menu_id', 0);
    $menuObj  = $menuId ? wp_get_nav_menu_object($menuId) : false;
    if (! $menuObj) {
        $existing = wp_get_nav_menu_object($menuName);
        $menuId   = $existing ? (int) $existing->term_id : (int) wp_create_nav_menu($menuName);
        update_option('prt_generated_menu_id', $menuId);
    }
    if ($menuId && ! is_wp_error($menuId)) {
        foreach (wp_get_nav_menu_items($menuId) ?: [] as $item) {
            wp_delete_post($item->ID, true);
        }
        wp_update_nav_menu_item($menuId, 0, [
            'menu-item-title'  => __('Home', 'pressroot'),
            'menu-item-url'    => home_url('/'),
            'menu-item-type'   => 'custom',
            'menu-item-status' => 'publish',
        ]);
        $pages = function_exists('App\\prt_get_site_type_pages') ? prt_get_site_type_pages() : [];
        foreach ($pages as $sp) {
            if ($typeId !== '' && $sp->prt_site_type !== $typeId) {
                continue;
            }
            wp_update_nav_menu_item($menuId, 0, [
                'menu-item-title'     => get_the_title($sp),
                'menu-item-object'    => 'page',
                'menu-item-object-id' => $sp->ID,
                'menu-item-type'      => 'post_type',
                'menu-item-status'    => 'publish',
            ]);
        }
        $locations = get_theme_mod('nav_menu_locations', []);
        $locations['primary_navigation'] = $menuId;
        set_theme_mod('nav_menu_locations', $locations);
    }

    // ── HEADER: goal-driven CTA (stored so the blade + Customizer agree).
    $texts = ['leads' => __('Get a quote', 'pressroot'), 'sell' => __('Shop now', 'pressroot'), 'book' => __('Book now', 'pressroot'), 'read' => __('Subscribe', 'pressroot')];
    set_theme_mod('prt_header_cta_text', $texts[$b['goal']] ?? $texts['leads']);
    $ctaUrl = '';
    foreach (['reservations', 'contact', 'top-picks'] as $slug) {
        $pg = get_page_by_path($slug);
        if ($pg) {
            $ctaUrl = get_permalink($pg);
            break;
        }
    }
    if ($ctaUrl !== '') {
        set_theme_mod('prt_header_cta_url', $ctaUrl);
    }

    // ── FOOTER: brand tagline + light/dark ground.
    if ($b['desc'] !== '') {
        set_theme_mod('prt_footer_tagline', $b['desc']);
    }
    set_theme_mod('prt_footer_bg', $b['mode'] === 'light' ? 'paper' : 'ink');
    set_theme_mod('prt_footer_textc', $b['mode'] === 'light' ? 'body' : 'paper');
    set_theme_mod('prt_footer_brand', true);
}

/* ─────────────── Core AI instructions (the site's system prompt) ───────────────
 *
 * Theme Settings + the brand questionnaire ARE the prompting surface for
 * the whole site. This compiles every saved answer into one persistent
 * instruction document — rebuilt on every save, stored in the
 * prt_core_ai_instructions option, and PREPENDED to every AI call the
 * theme makes (page writing, hero copy, smart blocks, image prompts), so
 * the owner's selected model always works from the same brief. The owner
 * can append free-form guidance via the "AI instructions" field.
 */
/** Plain-text word count that survives HTML/markdown input. */
function prt_word_count(string $text): int
{
    $plain = trim(wp_strip_all_tags($text));
    return $plain === '' ? 0 : count(preg_split('/\s+/u', $plain) ?: []);
}

/** Flatten to plain text and trim to a word budget (keeps prompts model-sized). */
function prt_limit_words(string $text, int $max): string
{
    $plain = trim(preg_replace('/\s+/u', ' ', wp_strip_all_tags($text)) ?? '');
    if ($plain === '') {
        return '';
    }
    $words = explode(' ', $plain);
    return count($words) <= $max ? $plain : implode(' ', array_slice($words, 0, $max));
}

function prt_core_ai_instructions(): string
{
    $b      = prt_brand_profile();
    $goalMap = ['leads' => 'generate inquiries/leads', 'sell' => 'sell products', 'book' => 'get bookings/appointments', 'read' => 'grow readership'];
    $trend  = (string) get_option('prt_active_design_trend', 'bento');
    $kit    = (string) get_option('prt_active_site_kit', '');

    $lines   = [];
    $lines[] = 'CORE SITE BRIEF (single source of truth — follow it in every response):';
    $lines[] = '- Business: ' . ($b['name'] ?: get_bloginfo('name')) . ' — ' . ($b['desc'] ?: get_bloginfo('description'));
    if ($b['industry'] !== '') {
        $lines[] = '- Industry: ' . $b['industry'];
    }
    if ($b['audience'] !== '') {
        $lines[] = '- Audience: ' . $b['audience'];
    }
    $lines[] = '- Voice: ' . ($b['tone'] !== '' ? $b['tone'] : 'confident, concise, a little playful');
    $lines[] = '- Primary goal of every page: ' . ($goalMap[$b['goal']] ?? $goalMap['leads']);
    $lines[] = '- Visual direction: ' . ($b['mode'] === 'dark' ? 'dark' : 'light') . ' UI, ' . $b['vibe'] . ' personality, "' . $trend . '" design trend' . ($kit !== '' ? ', "' . $kit . '" style kit' : '') . ', imagery style: ' . $b['imagery'] . '.';
    $lines[] = '- Copy density: ' . $b['density'] . '. Never overpromise. No jargon. Plain language.';

    // Business facts from the Setup wizard's step 1 (app/setup-wizard.php):
    // mission, description, contact, hours. Included so the AI states real
    // facts instead of inventing them; each line only appears when set.
    $mission = trim((string) get_theme_mod('prt_biz_mission', ''));
    if ($mission !== '') {
        $lines[] = '- Mission: ' . sanitize_text_field($mission);
    }
    $about = trim((string) get_theme_mod('prt_biz_about', ''));
    if ($about !== '') {
        $lines[] = '- What the business does (owner\'s own words — quote facts from here, never invent): ' . sanitize_text_field($about);
    }
    $contactBits = array_filter([
        get_theme_mod('prt_biz_email', '') ? 'email ' . sanitize_text_field((string) get_theme_mod('prt_biz_email')) : '',
        get_theme_mod('prt_biz_phone', '') ? 'phone ' . sanitize_text_field((string) get_theme_mod('prt_biz_phone')) : '',
        get_theme_mod('prt_biz_address', '') ? 'location/service area: ' . sanitize_text_field((string) get_theme_mod('prt_biz_address')) : '',
    ]);
    if ($contactBits) {
        $lines[] = '- Contact: ' . implode('; ', $contactBits);
    }
    $bizHours = get_theme_mod('prt_biz_hours', []);
    if (is_array($bizHours) && array_filter($bizHours)) {
        $hourBits = [];
        foreach (array_filter($bizHours) as $d => $h) {
            $hourBits[] = ucfirst((string) $d) . ' ' . sanitize_text_field((string) $h);
        }
        $lines[] = '- Hours: ' . implode('; ', $hourBits);
    }

    // Per-site-type marketing answers, all applied types.
    if (function_exists('App\\prt_get_site_type_pages')) {
        $applied = array_unique(array_filter(wp_list_pluck(prt_get_site_type_pages(), 'prt_site_type')));
        foreach ($applied as $typeId) {
            $answers = array_filter(prt_type_answers((string) $typeId));
            if ($answers) {
                $labels = [];
                foreach ((prt_site_type_questions()[$typeId] ?? []) as $q) {
                    $labels[$q[0]] = $q[1];
                }
                $lines[] = '- ' . $typeId . ' specifics: ' . implode('; ', array_map(
                    fn ($k, $v) => ($labels[$k] ?? $k) . ': ' . $v,
                    array_keys($answers),
                    $answers
                ));
            }
        }
    }

    $custom = trim((string) get_theme_mod('prt_ai_custom_instructions', ''));
    if ($custom !== '') {
        // WYSIWYG field: flatten formatting to plain text, capped at 1,000 words.
        $lines[] = '- Owner instructions (highest priority): ' . prt_limit_words($custom, 1000);
    }

    // 📄 Uploaded .md instruction files (Theme Settings) — stored with the
    // site and compiled into every future brief, trimmed per-doc so a long
    // recipe can't blow the prompt budget.
    $docs = get_option('prt_ai_instruction_docs', []);
    if (is_array($docs)) {
        foreach ($docs as $dname => $doc) {
            $body = prt_limit_words((string) ($doc['content'] ?? ''), 1200);
            if ($body !== '') {
                $lines[] = '- Reference document "' . $dname . '" (follow where relevant): ' . $body;
            }
        }
    }

    return implode("\n", $lines) . "\n";
}

/** Rebuild + persist the brief. Called on every Theme Settings / setup save. */
function prt_rebuild_core_instructions(): void
{
    update_option('prt_core_ai_instructions', prt_core_ai_instructions());
}

/** The stored brief for prompt consumers (rebuilds lazily if missing). */
function prt_get_core_instructions(): string
{
    $v = (string) get_option('prt_core_ai_instructions', '');
    return $v !== '' ? $v : prt_core_ai_instructions();
}

/**
 * ✨ Generate the brand with AI: from just name + one-liner (+industry if
 * chosen), the selected model proposes the remaining brand answers — tone,
 * audience, goal — and they are saved as the questionnaire values. The
 * owner reviews/edits afterwards; nothing else runs automatically.
 */
add_action('admin_post_prt_ai_suggest_brand', function () {
    if (! current_user_can('edit_theme_options') || ! check_admin_referer('prt_ai_suggest_brand')) {
        wp_die(__('Not allowed.', 'pressroot'));
    }
    if (! function_exists('App\\prt_ai_features_enabled') || ! prt_ai_features_enabled()) {
        wp_die(__('AI features are switched off in Theme Settings.', 'pressroot'));
    }
    $b = prt_brand_profile();
    $prompt = 'Business: ' . ($b['name'] ?: get_bloginfo('name')) . ' — ' . ($b['desc'] ?: get_bloginfo('description'))
        . ($b['industry'] !== '' ? ' (industry: ' . $b['industry'] . ')' : '') . ". \n"
        . "Propose brand positioning. Respond in EXACTLY this format, nothing else:\n"
        . "TONE: <three comma-separated voice words>\n"
        . "AUDIENCE: <one line describing the ideal customer>\n"
        . "GOAL: <one of: leads, sell, book, read>\n"
        . "VIBE: <one of: bold, minimal, warm, playful>";
    $res = function_exists('App\\prt_ai_generate_text') ? prt_ai_generate_text('pollinations', $prompt) : ['ok' => false];
    if (! empty($res['ok'])) {
        $t = (string) $res['text'];
        if (preg_match('/^TONE:\s*(.+)$/mi', $t, $m)) {
            set_theme_mod('prt_brand_tone', sanitize_text_field(trim($m[1])));
        }
        if (preg_match('/^AUDIENCE:\s*(.+)$/mi', $t, $m)) {
            set_theme_mod('prt_brand_audience', sanitize_text_field(trim($m[1])));
        }
        if (preg_match('/^GOAL:\s*(leads|sell|book|read)/mi', $t, $m)) {
            set_theme_mod('prt_brand_goal', strtolower($m[1]));
        }
        if (preg_match('/^VIBE:\s*(bold|minimal|warm|playful)/mi', $t, $m)) {
            set_theme_mod('prt_brand_vibe', strtolower($m[1]));
        }
        prt_rebuild_core_instructions();
    }
    wp_safe_redirect(prt_settings_tab_url('settings', ['prt_brand_suggested' => empty($res['ok']) ? '0' : '1']));
    exit;
});

/* ────────────── Auto-generated block content (smart-copy cache) ──────────────
 *
 * "Auto generate block content through the block": the prt/smart-* blocks
 * read this cached, AI-written copy at render time. It is PRIMED by one
 * fast server-side AI call whenever Theme Settings are saved or a site
 * type is applied — never during a visitor page view, so the front end
 * stays instant and free-tier friendly. Blank cache = brand-derived
 * fallbacks inside the blocks, so nothing ever renders empty.
 */
function prt_smart_copy(): array
{
    $c = get_option('prt_smart_copy', []);
    return is_array($c) ? $c : [];
}

function prt_prime_smart_copy(): void
{
    if (! function_exists('App\\prt_ai_features_enabled') || ! prt_ai_features_enabled() || ! function_exists('App\\prt_ai_generate_text')) {
        return;
    }
    $b = prt_brand_profile();
    if ($b['name'] === '' && $b['desc'] === '') {
        return; // nothing to write about yet
    }
    $prompt = prt_get_core_instructions() . "\n"
        . 'Business: ' . ($b['name'] ?: get_bloginfo('name')) . ' — ' . ($b['desc'] ?: get_bloginfo('description')) . '. '
        . ($b['industry'] ? 'Industry: ' . $b['industry'] . '. ' : '')
        . ($b['audience'] ? 'Audience: ' . $b['audience'] . '. ' : '')
        . ($b['tone'] ? 'Voice: ' . $b['tone'] . '. ' : '')
        . "Write hero copy. Respond in EXACTLY this format, nothing else:\n"
        . "HEADLINE: <under 9 words>\nLEAD: <one sentence, under 22 words>\nCTA: <button label, under 4 words>";
    $res = prt_ai_generate_text('pollinations', $prompt);
    if (empty($res['ok'])) {
        return; // keep the previous cache — priming is best-effort
    }
    $out = [];
    foreach (['HEADLINE' => 'headline', 'LEAD' => 'lead', 'CTA' => 'cta'] as $tag => $key) {
        if (preg_match('/^' . $tag . ':\s*(.+)$/mi', (string) $res['text'], $m)) {
            $out[$key] = sanitize_text_field(trim($m[1], " \"'"));
        }
    }
    if (! empty($out['headline'])) {
        update_option('prt_smart_copy', $out);
    }
}

/* ─────────── Coded theme blocks (server-rendered, brand-aware) ───────────
 *
 * Two dynamic THEME blocks the generator composes pages with. Their render
 * callbacks read the brand profile + active kit/trend at request time, so
 * the same block markup produces a different, on-brand section for every
 * generated site type — "coded blocks per generated site" without ever
 * storing raw HTML in content. Same registration convention as the other
 * prt/* dynamic blocks (app/blocks-dynamic.php).
 */
add_action('init', function () {
    register_block_type('prt/smart-hero', [
        'attributes' => [
            'align'    => ['type' => 'string', 'default' => 'left'],   // left | center
            'headline' => ['type' => 'string', 'default' => ''],       // blank = brand-derived
            'lead'     => ['type' => 'string', 'default' => ''],
        ],
        'render_callback' => function ($attrs) {
            $b     = prt_brand_profile();
            $goalC = ['leads' => __('Get a free quote', 'pressroot'), 'sell' => __('Shop now', 'pressroot'), 'book' => __('Book now', 'pressroot'), 'read' => __('Start reading', 'pressroot')];
            $smart = prt_smart_copy();
            $head  = $attrs['headline'] !== '' ? $attrs['headline']
                : (($smart['headline'] ?? '') ?: ($b['name'] !== '' ? $b['name'] : get_bloginfo('name')));
            $lead  = $attrs['lead'] !== '' ? $attrs['lead']
                : (($smart['lead'] ?? '') ?: ($b['desc'] !== '' ? $b['desc'] : get_bloginfo('description')));
            $dark   = $b['mode'] === 'dark';
            $center = ($attrs['align'] ?? 'left') === 'center';
            $bg     = $dark
                ? 'background:linear-gradient(180deg,#201B3A,#15122a);color:#fff;'
                : 'background:var(--color-cream);color:var(--color-ink);';
            $out  = '<section class="prt-smart-hero prt-spec-card" style="border-radius:28px;padding:clamp(40px,6vw,72px) clamp(24px,4vw,48px);' . $bg . ($center ? 'text-align:center;' : '') . '">';
            $out .= '<h1 style="font-family:var(--font-display);font-weight:800;font-size:clamp(38px,5.5vw,64px);line-height:1.03;letter-spacing:-.03em;margin:0 0 14px;">' . esc_html($head) . '</h1>';
            if ($lead !== '') {
                $out .= '<p style="font-size:19px;line-height:1.55;max-width:34em;margin:0 0 26px;' . ($center ? 'margin-inline:auto;' : '') . 'opacity:.85;">' . esc_html($lead) . '</p>';
            }
            $out .= '<a class="prt-btn-grad prt-lift" style="padding:15px 30px;font-size:16px;font-family:var(--font-display);" href="#contact">' . esc_html($goalC[$b['goal']] ?? $goalC['leads']) . '</a>';
            $out .= '</section>';
            return $out;
        },
    ]);

    register_block_type('prt/smart-cta', [
        'attributes' => [
            'heading' => ['type' => 'string', 'default' => ''],
            'button'  => ['type' => 'string', 'default' => ''],
            'url'     => ['type' => 'string', 'default' => ''],
        ],
        'render_callback' => function ($attrs) {
            $b     = prt_brand_profile();
            $goalH = ['leads' => __('Ready when you are.', 'pressroot'), 'sell' => __('Find your favorite.', 'pressroot'), 'book' => __('Save your spot.', 'pressroot'), 'read' => __('Never miss a post.', 'pressroot')];
            $goalB = ['leads' => __('Get in touch', 'pressroot'), 'sell' => __('Browse the shop', 'pressroot'), 'book' => __('Book now', 'pressroot'), 'read' => __('Subscribe', 'pressroot')];
            $head  = $attrs['heading'] !== '' ? $attrs['heading'] : ($goalH[$b['goal']] ?? $goalH['leads']);
            $smart = prt_smart_copy();
            $btn   = $attrs['button'] !== '' ? $attrs['button'] : (($smart['cta'] ?? '') ?: ($goalB[$b['goal']] ?? $goalB['leads']));
            $url   = $attrs['url'] !== '' ? $attrs['url'] : '#contact';
            return '<section class="prt-smart-cta prt-spec-card" style="border-radius:26px;padding:44px 40px;text-align:center;background:var(--gradient-brand);color:#fff;">'
                . '<h2 style="font-family:var(--font-display);font-weight:800;font-size:clamp(26px,3.5vw,40px);letter-spacing:-.02em;margin:0 0 16px;">' . esc_html($head) . '</h2>'
                . '<a class="prt-lift" style="display:inline-block;background:#fff;color:var(--color-green);padding:15px 32px;border-radius:999px;font-weight:800;text-decoration:none;font-family:var(--font-display);" href="' . esc_url($url) . '">' . esc_html($btn) . '</a>'
                . '</section>';
        },
    ]);
}, 12);

/* ───── Remix pattern generation (variants C + D) — CORE BLOCKS ONLY ─────
 *
 * Every generated pattern is built from WordPress core Gutenberg blocks
 * (group / columns / heading / paragraph / buttons / spacer, styled through
 * theme.json palette + gradient slugs and theme classNames) plus the two
 * prt/smart-* theme blocks above. No wp:html anywhere — pages open in the
 * block editor as fully native, individually editable blocks.
 */
add_action('init', function () {

    /* Core-block builders. Placeholder copy is intentionally generic — the
     * ✨ AI-write step replaces it with brand copy, built as well as the
     * connected model can write it. */

    $btns = function (string $label, bool $center = false): string {
        return '<!-- wp:buttons' . ($center ? ' {"layout":{"type":"flex","justifyContent":"center"}}' : '') . ' -->'
            . '<div class="wp-block-buttons">'
            . '<!-- wp:button --><div class="wp-block-button"><a class="wp-block-button__link wp-element-button">' . esc_html($label) . '</a></div><!-- /wp:button -->'
            . '</div><!-- /wp:buttons -->';
    };

    $card = function (string $title, string $text, string $extra = ''): string {
        return '<!-- wp:group {"className":"prt-spec-card is-style-prt-card","backgroundColor":"surface","layout":{"type":"constrained"}} -->'
            . '<div class="wp-block-group prt-spec-card is-style-prt-card has-surface-background-color has-background">'
            . '<!-- wp:heading {"level":3} --><h3 class="wp-block-heading">' . esc_html($title) . '</h3><!-- /wp:heading -->'
            . '<!-- wp:paragraph {"textColor":"muted"} --><p class="has-muted-color has-text-color">' . esc_html($text) . '</p><!-- /wp:paragraph -->'
            . $extra
            . '</div><!-- /wp:group -->';
    };

    $heroes = [
        // 0: smart theme block — fully brand-driven at render time.
        fn (string $t, string $l) => '<!-- wp:prt/smart-hero {"align":"center"} /-->',
        // 1: dark cover-style group (Ink gradient, paper text).
        fn (string $t, string $l) => '<!-- wp:group {"className":"prt-gen-hero","style":{"border":{"radius":"28px"},"spacing":{"padding":{"top":"64px","bottom":"64px","left":"32px","right":"32px"}}},"gradient":"ink","textColor":"paper","layout":{"type":"constrained"}} -->'
            . '<div class="wp-block-group prt-gen-hero has-paper-color has-text-color has-ink-gradient-background has-background" style="border-radius:28px;padding-top:64px;padding-right:32px;padding-bottom:64px;padding-left:32px">'
            . '<!-- wp:heading {"level":1,"fontSize":"huge"} --><h1 class="wp-block-heading has-huge-font-size">' . esc_html($t) . ' that earns its keep.</h1><!-- /wp:heading -->'
            . '<!-- wp:paragraph {"fontSize":"large"} --><p class="has-large-font-size">A clear promise to your visitor goes here — the AI writer will make it yours.</p><!-- /wp:paragraph -->'
            . $btns(__('Get started', 'pressroot'))
            . '</div><!-- /wp:group -->',
        // 2: brand-gradient band, centered.
        fn (string $t, string $l) => '<!-- wp:group {"className":"prt-gen-band","style":{"border":{"radius":"28px"},"spacing":{"padding":{"top":"56px","bottom":"56px","left":"32px","right":"32px"}}},"gradient":"green","textColor":"paper","layout":{"type":"constrained"}} -->'
            . '<div class="wp-block-group prt-gen-band has-paper-color has-text-color has-green-gradient-background has-background" style="border-radius:28px;padding-top:56px;padding-right:32px;padding-bottom:56px;padding-left:32px">'
            . '<!-- wp:heading {"level":1,"textAlign":"center","fontSize":"huge"} --><h1 class="wp-block-heading has-text-align-center has-huge-font-size">' . esc_html($t) . ' without the busywork.</h1><!-- /wp:heading -->'
            . '<!-- wp:paragraph {"align":"center","fontSize":"large"} --><p class="has-text-align-center has-large-font-size">One supporting sentence about ' . esc_html(strtolower($l)) . '.</p><!-- /wp:paragraph -->'
            . $btns(__('See how', 'pressroot'), true)
            . '</div><!-- /wp:group -->',
        // 3: split columns — copy left, spectrum panel right.
        fn (string $t, string $l) => '<!-- wp:columns {"verticalAlignment":"center"} --><div class="wp-block-columns are-vertically-aligned-center">'
            . '<!-- wp:column {"verticalAlignment":"center","width":"55%"} --><div class="wp-block-column is-vertically-aligned-center" style="flex-basis:55%">'
            . '<!-- wp:heading {"level":1,"fontSize":"huge"} --><h1 class="wp-block-heading has-huge-font-size">The ' . esc_html(strtolower($t)) . ' page people actually read.</h1><!-- /wp:heading -->'
            . '<!-- wp:paragraph {"textColor":"muted","fontSize":"large"} --><p class="has-muted-color has-text-color has-large-font-size">Same brand system, brand-new bones — refreshed by the generator.</p><!-- /wp:paragraph -->'
            . $btns(__('Learn more', 'pressroot'))
            . '</div><!-- /wp:column -->'
            . '<!-- wp:column {"verticalAlignment":"center","width":"45%"} --><div class="wp-block-column is-vertically-aligned-center" style="flex-basis:45%">'
            . '<!-- wp:group {"style":{"border":{"radius":"22px"}},"gradient":"spectrum","layout":{"type":"constrained"}} --><div class="wp-block-group has-spectrum-gradient-background has-background" style="border-radius:22px">'
            . '<!-- wp:spacer {"height":"280px"} --><div style="height:280px" aria-hidden="true" class="wp-block-spacer"></div><!-- /wp:spacer -->'
            . '</div><!-- /wp:group -->'
            . '</div><!-- /wp:column -->'
            . '</div><!-- /wp:columns -->',
    ];

    $features = [
        // 0: three spectrum cards in columns.
        fn (string $t) => '<!-- wp:columns --><div class="wp-block-columns">'
            . implode('', array_map(
                fn ($pair) => '<!-- wp:column --><div class="wp-block-column">' . $card($pair[0], $pair[1]) . '</div><!-- /wp:column -->',
                [[__('Made to fit', 'pressroot'), __('A specific, provable claim goes here.', 'pressroot')], [__('Ready today', 'pressroot'), __('What the visitor gets, in plain words.', 'pressroot')], [__('Fast by default', 'pressroot'), __('Why this beats the alternative.', 'pressroot')]]
            ))
            . '</div><!-- /wp:columns -->',
        // 1: stacked list rows.
        fn (string $t) => $card(__('1 · Start with the essentials', 'pressroot'), __('One sentence about the first step.', 'pressroot'))
            . $card(__('2 · Layer in the details', 'pressroot'), __('One sentence about the second step.', 'pressroot'))
            . $card(__('3 · Ship it and iterate', 'pressroot'), __('One sentence about the third step.', 'pressroot')),
        // 2: stat band, four columns.
        fn (string $t) => '<!-- wp:columns --><div class="wp-block-columns">'
            . implode('', array_map(
                fn ($pair) => '<!-- wp:column --><div class="wp-block-column">'
                    . '<!-- wp:group {"className":"prt-spec-card is-style-prt-card","backgroundColor":"surface","layout":{"type":"constrained"}} --><div class="wp-block-group prt-spec-card is-style-prt-card has-surface-background-color has-background">'
                    . '<!-- wp:heading {"level":3,"textAlign":"center","textColor":"' . $pair[1] . '","fontSize":"x-large"} --><h3 class="wp-block-heading has-text-align-center has-' . $pair[1] . '-color has-text-color has-x-large-font-size">' . esc_html($pair[0]) . '</h3><!-- /wp:heading -->'
                    . '<!-- wp:paragraph {"align":"center","textColor":"muted","fontSize":"small"} --><p class="has-text-align-center has-muted-color has-text-color has-small-font-size">' . esc_html__('a stat worth bragging about', 'pressroot') . '</p><!-- /wp:paragraph -->'
                    . '</div><!-- /wp:group -->'
                    . '</div><!-- /wp:column -->',
                [['10×', 'purple'], ['24/7', 'pink'], ['99.9%', 'lime'], ['5★', 'amber']]
            ))
            . '</div><!-- /wp:columns -->',
        // 3: card + dark aside.
        fn (string $t) => '<!-- wp:columns --><div class="wp-block-columns">'
            . '<!-- wp:column {"width":"60%"} --><div class="wp-block-column" style="flex-basis:60%">' . $card(__('Why it works', 'pressroot'), __('Two honest paragraphs beat ten feature bullets — this is where the page earns trust.', 'pressroot')) . '</div><!-- /wp:column -->'
            . '<!-- wp:column {"width":"40%"} --><div class="wp-block-column" style="flex-basis:40%">'
            . '<!-- wp:group {"className":"prt-spec-card","style":{"border":{"radius":"18px"}},"backgroundColor":"ink","textColor":"paper","layout":{"type":"constrained"}} --><div class="wp-block-group prt-spec-card has-paper-color has-ink-background-color has-text-color has-background" style="border-radius:18px">'
            . '<!-- wp:heading {"level":3,"textColor":"lime"} --><h3 class="wp-block-heading has-lime-color has-text-color">' . esc_html__('Good to know', 'pressroot') . '</h3><!-- /wp:heading -->'
            . '<!-- wp:paragraph --><p>' . esc_html__('A guarantee or proof point that deserves its own dark card.', 'pressroot') . '</p><!-- /wp:paragraph -->'
            . '</div><!-- /wp:group -->'
            . '</div><!-- /wp:column -->'
            . '</div><!-- /wp:columns -->',
    ];

    $ctas = [
        fn () => '<!-- wp:prt/smart-cta /-->',
        fn () => '<!-- wp:group {"className":"prt-spec-card","style":{"border":{"radius":"26px"},"spacing":{"padding":{"top":"44px","bottom":"44px","left":"40px","right":"40px"}}},"backgroundColor":"ink","textColor":"paper","layout":{"type":"constrained"}} -->'
            . '<div class="wp-block-group prt-spec-card has-paper-color has-ink-background-color has-text-color has-background" style="border-radius:26px;padding-top:44px;padding-right:40px;padding-bottom:44px;padding-left:40px">'
            . '<!-- wp:heading {"textAlign":"center"} --><h2 class="wp-block-heading has-text-align-center">' . esc_html__('One conversation. Zero pressure.', 'pressroot') . '</h2><!-- /wp:heading -->'
            . $btns(__('Say hello', 'pressroot'), true)
            . '</div><!-- /wp:group -->',
        fn () => $card(__('Questions? Good.', 'pressroot'), __('The best projects start with a few of them.', 'pressroot'), $btns(__('Start the conversation', 'pressroot'))),
    ];

    $wrap = function (string $inner): string {
        return '<!-- wp:group {"className":"prt-wrap","style":{"spacing":{"padding":{"top":"48px","bottom":"24px"}}},"layout":{"type":"constrained","contentSize":"1240px"}} -->'
            . '<div class="wp-block-group prt-wrap" style="padding-top:48px;padding-bottom:24px">' . $inner . '</div>'
            . '<!-- /wp:group -->' . "\n\n";
    };

    foreach (prt_site_types() as $typeId => $type) {
        foreach ($type['pages'] as $page) {
            foreach (['c', 'd'] as $variant) {
                $seed = crc32($typeId . '|' . $page['role'] . '|' . $variant);
                $h    = $heroes[$seed % count($heroes)];
                $f    = $features[intdiv($seed, 7) % count($features)];
                $c    = $ctas[intdiv($seed, 31) % count($ctas)];

                register_block_pattern('prt-site/' . $typeId . '-' . $page['role'] . '-' . $variant, [
                    'title'      => sprintf(
                        /* translators: 1: site type label, 2: page title, 3: variant letter */
                        __('%1$s %2$s — remix %3$s (core blocks)', 'pressroot'),
                        $type['label'],
                        $page['title'],
                        strtoupper($variant)
                    ),
                    'categories' => ['prt-site-types'],
                    'blockTypes' => ['core/post-content'],
                    'content'    => $wrap($h($page['title'], $type['label']))
                        . $wrap($f($page['title']))
                        . $wrap($c()),
                ]);
            }
        }
    }
}, 14);

/* ───────────────────────── Brand tab (settings) ───────────────────────── */

/**
 * "Brand" tab on Appearance -> Pressroot (registered in
 * app/pressroot-settings.php) — the questionnaire whose answers steer every
 * design the Site Types generator deals: kits are filtered by light/dark +
 * vibe, the brand color overrides each dealt kit's action color, and the AI
 * copy prompt reads the whole profile.
 */
function prt_brand_tab_html(): void
{
    if (! current_user_can('edit_theme_options')) {
        return;
    }
    $b = prt_brand_profile();
    if (isset($_GET['prt_brand_saved'])) {
        echo '<div class="notice notice-success is-dismissible"><p>' . esc_html__('Brand profile saved — every design refresh now follows it.', 'pressroot') . '</p></div>';
    }
    ?>
    <h2 style="margin-top:0"><?php esc_html_e('Brand', 'pressroot'); ?></h2>
    <p class="description" style="max-width:640px"><?php esc_html_e('Answer a few questions about the business and the design generator builds around them: color, light-or-dark, and personality shape every theme it deals on the Site Types tab, and the AI copywriter matches the voice. No code, no jargon — this page and the Site Types tab are all you need.', 'pressroot'); ?></p>

    <div class="prt-rf-card" style="max-width:640px;margin-top:16px;padding:18px 22px">
        <strong><?php esc_html_e('Three steps to a finished site', 'pressroot'); ?></strong>
        <ol style="margin:10px 0 0 18px;font-size:13.5px;line-height:1.9">
            <li><?php esc_html_e('Fill in this page and hit "Save brand profile".', 'pressroot'); ?></li>
            <li><?php printf(wp_kses_post(__('Open <a href="%s">Site Types</a> and pick the kind of site you\'re building — your pages are created for you.', 'pressroot')), esc_url(prt_settings_tab_url('ai'))); ?></li>
            <li><?php esc_html_e('Not in love with it? Hit 🎲 Refresh until a design clicks. Nothing breaks, ever — every click just deals a fresh look.', 'pressroot'); ?></li>
        </ol>
    </div>

    <form method="post" action="<?php echo esc_url(admin_url('admin-post.php')); ?>" style="max-width:640px;margin-top:18px">
        <input type="hidden" name="action" value="prt_save_brand_profile">
        <?php wp_nonce_field('prt_save_brand_profile'); ?>
        <table class="form-table" role="presentation">
            <tr>
                <th scope="row"><label for="prt_brand_name"><?php esc_html_e('Business name', 'pressroot'); ?></label></th>
                <td><input type="text" id="prt_brand_name" name="prt_brand_name" class="regular-text" value="<?php echo esc_attr($b['name']); ?>" placeholder="<?php esc_attr_e('e.g. Hummel & Co.', 'pressroot'); ?>"></td>
            </tr>
            <tr>
                <th scope="row"><label for="prt_brand_desc"><?php esc_html_e('What do you do?', 'pressroot'); ?></label></th>
                <td>
                    <input type="text" id="prt_brand_desc" name="prt_brand_desc" class="large-text" value="<?php echo esc_attr($b['desc']); ?>" placeholder="<?php esc_attr_e('One sentence — who you help and how', 'pressroot'); ?>">
                    <p class="description"><?php esc_html_e('Pre-fills the AI copy generator on the Site Types tab.', 'pressroot'); ?></p>
                </td>
            </tr>
            <tr>
                <th scope="row"><label for="prt_brand_color"><?php esc_html_e('Brand color', 'pressroot'); ?></label></th>
                <td>
                    <input type="color" id="prt_brand_color" name="prt_brand_color" value="<?php echo esc_attr($b['color'] ?: '#6C4CF1'); ?>" style="width:60px;height:36px;padding:2px">
                    <p class="description"><?php esc_html_e('Overrides the accent color of every design the generator deals. Leave at the default iris to let each kit keep its own.', 'pressroot'); ?></p>
                </td>
            </tr>
            <tr>
                <th scope="row"><?php esc_html_e('Light or dark?', 'pressroot'); ?></th>
                <td>
                    <?php foreach (['light' => __('Light', 'pressroot'), 'dark' => __('Dark', 'pressroot'), 'either' => __('Surprise me', 'pressroot')] as $val => $label) : ?>
                        <label style="margin-right:16px"><input type="radio" name="prt_brand_mode" value="<?php echo esc_attr($val); ?>" <?php checked($b['mode'], $val); ?>> <?php echo esc_html($label); ?></label>
                    <?php endforeach; ?>
                </td>
            </tr>
            <tr>
                <th scope="row"><?php esc_html_e('Personality', 'pressroot'); ?></th>
                <td>
                    <select name="prt_brand_vibe">
                        <?php foreach (['any' => __('Anything goes', 'pressroot'), 'bold' => __('Bold & confident', 'pressroot'), 'minimal' => __('Minimal & sharp', 'pressroot'), 'warm' => __('Warm & inviting', 'pressroot'), 'playful' => __('Playful & bright', 'pressroot')] as $val => $label) : ?>
                            <option value="<?php echo esc_attr($val); ?>" <?php selected($b['vibe'], $val); ?>><?php echo esc_html($label); ?></option>
                        <?php endforeach; ?>
                    </select>
                    <p class="description"><?php esc_html_e('Filters which design kits the generator may deal.', 'pressroot'); ?></p>
                </td>
            </tr>
            <tr>
                <th scope="row"><label for="prt_brand_audience"><?php esc_html_e('Who is it for?', 'pressroot'); ?></label></th>
                <td>
                    <input type="text" id="prt_brand_audience" name="prt_brand_audience" class="large-text" value="<?php echo esc_attr($b['audience'] ?? ''); ?>" placeholder="<?php esc_attr_e('e.g. busy parents in Adams County who need a reliable plumber', 'pressroot'); ?>">
                    <p class="description"><?php esc_html_e('The AI writes to this exact person.', 'pressroot'); ?></p>
                </td>
            </tr>
            <tr>
                <th scope="row"><label for="prt_brand_industry_sel"><?php esc_html_e('Industry', 'pressroot'); ?></label></th>
                <td>
                    <?php
                    $industries = apply_filters('pressroot/brand_industries', [
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
                <th scope="row"><label for="prt_brand_tone"><?php esc_html_e('Three words for your voice', 'pressroot'); ?></label></th>
                <td><input type="text" id="prt_brand_tone" name="prt_brand_tone" class="regular-text" value="<?php echo esc_attr($b['tone'] ?? ''); ?>" placeholder="<?php esc_attr_e('e.g. friendly, expert, no-nonsense', 'pressroot'); ?>"></td>
            </tr>
            <tr>
                <th scope="row"><?php esc_html_e('Main goal of the site', 'pressroot'); ?></th>
                <td>
                    <select name="prt_brand_goal">
                        <?php foreach (['leads' => __('Get leads / inquiries', 'pressroot'), 'sell' => __('Sell products', 'pressroot'), 'book' => __('Book appointments / reservations', 'pressroot'), 'read' => __('Grow an audience / readership', 'pressroot')] as $val => $label) : ?>
                            <option value="<?php echo esc_attr($val); ?>" <?php selected($b['goal'] ?? 'leads', $val); ?>><?php echo esc_html($label); ?></option>
                        <?php endforeach; ?>
                    </select>
                    <p class="description"><?php esc_html_e('Shapes every call-to-action the AI writes.', 'pressroot'); ?></p>
                </td>
            </tr>
            <tr>
                <th scope="row"><?php esc_html_e('Imagery style', 'pressroot'); ?></th>
                <td>
                    <select name="prt_brand_imagery">
                        <?php foreach (['photo' => __('Photography', 'pressroot'), 'illustration' => __('Illustration', 'pressroot'), 'abstract' => __('Abstract gradients', 'pressroot'), 'none' => __('No images — typography only', 'pressroot')] as $val => $label) : ?>
                            <option value="<?php echo esc_attr($val); ?>" <?php selected($b['imagery'] ?? 'photo', $val); ?>><?php echo esc_html($label); ?></option>
                        <?php endforeach; ?>
                    </select>
                    <p class="description"><?php esc_html_e('Used when AI generates images for your pages.', 'pressroot'); ?></p>
                </td>
            </tr>
            <tr>
                <th scope="row"><?php esc_html_e('Content density', 'pressroot'); ?></th>
                <td>
                    <?php foreach (['minimal' => __('Minimal — short and punchy', 'pressroot'), 'balanced' => __('Balanced', 'pressroot'), 'rich' => __('Rich — tell the whole story', 'pressroot')] as $val => $label) : ?>
                        <label style="margin-right:16px"><input type="radio" name="prt_brand_density" value="<?php echo esc_attr($val); ?>" <?php checked($b['density'] ?? 'balanced', $val); ?>> <?php echo esc_html($label); ?></label>
                    <?php endforeach; ?>
                </td>
            </tr>
            <tr>
                <th scope="row"><?php esc_html_e('Design trend', 'pressroot'); ?></th>
                <td>
                    <select name="prt_brand_trend">
                        <option value="any" <?php selected($b['trend'] ?? 'any', 'any'); ?>><?php esc_html_e('🎲 Surprise me — rotate trends on refresh', 'pressroot'); ?></option>
                        <?php foreach (prt_design_trends() as $slug => $t) : ?>
                            <option value="<?php echo esc_attr($slug); ?>" <?php selected($b['trend'] ?? 'any', $slug); ?>><?php echo esc_html($t['label'] . ' — ' . $t['desc']); ?></option>
                        <?php endforeach; ?>
                    </select>
                    <p class="description"><?php esc_html_e('"Surprise me" deals a different current design trend (bento, glassmorphism, neo-brutalist, editorial serif, Swiss minimal, retro pop) with every refresh, filtered by your personality answer.', 'pressroot'); ?></p>
                </td>
            </tr>
            <tr>
                <th scope="row"><?php esc_html_e('Site title & tagline', 'pressroot'); ?></th>
                <td>
                    <label><input type="checkbox" name="prt_brand_apply_site" value="1"> <?php esc_html_e('Use the business name and description above as my site\'s title and tagline', 'pressroot'); ?></label>
                    <p class="description"><?php printf(esc_html__('Currently: "%1$s" — "%2$s". Saves you a trip to Settings → General.', 'pressroot'), esc_html(get_bloginfo('name')), esc_html(get_bloginfo('description'))); ?></p>
                </td>
            </tr>
            <tr>
                <th scope="row"><?php esc_html_e('Powered by AI — or not', 'pressroot'); ?></th>
                <td>
                    <label style="font-weight:600"><input type="checkbox" name="prt_ai_features_on" value="1" <?php checked((bool) get_theme_mod('prt_ai_features_on', true)); ?>> <?php esc_html_e('✨ Let AI help with writing and images', 'pressroot'); ?></label>
                    <p class="description"><?php esc_html_e('On: the copy generator, AI image finder, and the editor\'s "Generate with AI" button are available. Off: nothing on this site ever calls an AI service — and the design generator keeps working either way, because every layout and color kit is built into the theme.', 'pressroot'); ?></p>
                </td>
            </tr>
        </table>
        <?php submit_button(__('Save brand profile', 'pressroot')); ?>
    </form>

    <?php if (function_exists('App\\prt_ai_features_enabled') && prt_ai_features_enabled()) : ?>
        <?php if (isset($_GET['prt_img_done'])) : ?>
            <div class="notice notice-success is-dismissible"><p><?php esc_html_e('✨ Brand image generated, saved to your Media Library, and set as the homepage hero image.', 'pressroot'); ?></p></div>
        <?php elseif (isset($_GET['prt_img_error'])) : ?>
            <div class="notice notice-error is-dismissible"><p><?php echo esc_html(sanitize_text_field(wp_unslash($_GET['prt_img_error']))); ?></p></div>
        <?php endif; ?>
        <div class="prt-rf-card" style="max-width:640px;padding:18px 22px">
            <strong><?php esc_html_e('✨ Generate a brand image with AI', 'pressroot'); ?></strong>
            <p class="description" style="margin:6px 0 12px"><?php esc_html_e('Uses your industry, imagery style, and voice answers above to generate a hero image (free, no account), saves it to the Media Library, and puts it in the homepage hero. Regenerate any time — each run is different.', 'pressroot'); ?></p>
            <form method="post" action="<?php echo esc_url(admin_url('admin-post.php')); ?>" style="margin:0">
                <input type="hidden" name="action" value="prt_ai_brand_image">
                <?php wp_nonce_field('prt_ai_brand_image'); ?>
                <button class="button button-primary"><?php esc_html_e('Generate brand image', 'pressroot'); ?></button>
            </form>
        </div>
    <?php endif; ?>
    <?php
}
