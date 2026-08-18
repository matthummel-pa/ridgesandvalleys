<?php

/**
 * Projects custom post type + Project Type taxonomy + case-study meta fields.
 *
 * Projects are the studio's proof — full case studies rendered by
 * resources/views/single-project.blade.php as a marketing funnel. The meta
 * fields below feed that page; all are optional and degrade gracefully.
 */

namespace App;

add_action('init', function () {
    register_post_type('project', [
        'labels' => [
            'name'          => __('Projects', 'sage'),
            'singular_name' => __('Project', 'sage'),
            'add_new_item'  => __('Add New Project', 'sage'),
            'edit_item'     => __('Edit Project', 'sage'),
            'menu_name'     => __('Projects', 'sage'),
        ],
        'public'       => true,
        'has_archive'  => false,
        'menu_icon'    => 'dashicons-portfolio',
        'rewrite'      => ['slug' => 'work', 'with_front' => false],
        'supports'     => ['title', 'editor', 'excerpt', 'thumbnail', 'revisions'],
        'show_in_rest' => true,
    ]);

    register_taxonomy('project_type', 'project', [
        'labels' => [
            'name'          => __('Project Types', 'sage'),
            'singular_name' => __('Project Type', 'sage'),
        ],
        'public'            => true,
        'hierarchical'      => true,
        'show_admin_column' => true,
        'rewrite'           => ['slug' => 'work-type'],
        'show_in_rest'      => true,
    ]);
});

add_action('pre_get_posts', function ($query) {
    if (! is_admin() && $query->is_main_query() && is_post_type_archive('project')) {
        $query->set('posts_per_page', 12);
    }
});

/**
 * The full set of case-study fields, grouped for the editor and reused by the
 * save handler. Each field: key => [label, type, placeholder].
 * type: text | url | textarea | lines
 */
function project_field_groups(): array
{
    return [
        __('Project type', 'sage') => [
            '_rv_is_concept' => [__('This is a concept / demo project', 'sage'), 'checkbox', ''],
        ],
        __('At a glance', 'sage') => [
            '_rv_eyebrow'  => [__('Eyebrow label', 'sage'), 'text', __('e.g. Local Launch · Real client', 'sage')],
            '_rv_client'   => [__('Client', 'sage'), 'text', __('e.g. Bradley Goldsmith Law', 'sage')],
            '_rv_summary'  => [__('One-line summary', 'sage'), 'text', __('The result in a sentence — shown under the title.', 'sage')],
            '_rv_industry' => [__('Industry / sector', 'sage'), 'text', __('e.g. Law firm · Professional services', 'sage')],
            '_rv_location' => [__('Location', 'sage'), 'text', __('e.g. Gettysburg, PA', 'sage')],
            '_rv_timeline' => [__('Timeline', 'sage'), 'text', __('e.g. 7 days, design to launch', 'sage')],
            '_rv_services' => [__('Services (comma-separated)', 'sage'), 'text', __('e.g. Web design, SEO, Copywriting', 'sage')],
            '_rv_url'      => [__('Live site URL', 'sage'), 'url', 'https://'],
        ],
        __('The story', 'sage') => [
            '_rv_challenge' => [__('The challenge', 'sage'), 'textarea', __('What was getting in the way before? What was the business losing?', 'sage')],
            '_rv_approach'  => [__('The approach', 'sage'), 'textarea', __('What did you do, and why? The constraints and the plan.', 'sage')],
            '_rv_result'    => [__('The result', 'sage'), 'textarea', __('What changed after launch? Be concrete.', 'sage')],
        ],
        __('Results (up to three)', 'sage') => [
            '_rv_m1_value' => [__('Metric 1 — value', 'sage'), 'text', __('e.g. 7 days', 'sage')],
            '_rv_m1_label' => [__('Metric 1 — label', 'sage'), 'text', __('e.g. design to launch', 'sage')],
            '_rv_m2_value' => [__('Metric 2 — value', 'sage'), 'text', __('e.g. AA', 'sage')],
            '_rv_m2_label' => [__('Metric 2 — label', 'sage'), 'text', __('e.g. accessibility', 'sage')],
            '_rv_m3_value' => [__('Metric 3 — value', 'sage'), 'text', __('e.g. 100%', 'sage')],
            '_rv_m3_label' => [__('Metric 3 — label', 'sage'), 'text', __('e.g. mobile-ready', 'sage')],
        ],
        __('In their words', 'sage') => [
            '_rv_quote'        => [__('Testimonial quote', 'sage'), 'textarea', __('A short quote from the client.', 'sage')],
            '_rv_quote_author' => [__('Quote — name', 'sage'), 'text', __('e.g. Bradley Goldsmith', 'sage')],
            '_rv_quote_role'   => [__('Quote — role / company', 'sage'), 'text', __('e.g. Owner, Goldsmith Law', 'sage')],
        ],
        __('Under the hood', 'sage') => [
            '_rv_deliverables' => [__('What was delivered (one per line)', 'sage'), 'lines', __("5-page website\nGoogle Business Profile\nContact form + spam protection", 'sage')],
            '_rv_tech'         => [__('Tech & tools (comma-separated)', 'sage'), 'text', __('e.g. WordPress, Sage, Cloudflare', 'sage')],
        ],
        __('Call to action (optional override)', 'sage') => [
            '_rv_cta_text' => [__('CTA button label', 'sage'), 'text', __('Defaults to “Start a project”.', 'sage')],
            '_rv_cta_url'  => [__('CTA link', 'sage'), 'text', __('Defaults to your contact page.', 'sage')],
        ],
    ];
}

/**
 * A short, plain-English hint shown under each section heading in the Case
 * Study meta box, so it's clear what each group of fields is for. Keyed by the
 * group label; unknown groups get no hint.
 */
function project_group_hint(string $label): string
{
    $hints = [
        __('Project type', 'sage')                       => __('Tick this for concept or demo projects (like the restaurant and inn concepts). It turns on the “Open the live demo” and “Get a site like this” buttons and the “not a real business” note, and credits Ridges & Valleys as the designer. Leave it off for real client work.', 'sage'),
        __('At a glance', 'sage')                        => __('The quick facts shown at the top of the case study and on its card in the Work grid: client, one-line summary, industry, location, timeline, and the live URL.', 'sage'),
        __('The story', 'sage')                          => __('The heart of the case study — what was getting in the way before, what you did about it, and what changed after. A short paragraph in each box works best.', 'sage'),
        __('Results (up to three)', 'sage')              => __('The big numbers in the dark results band. Give each a short value (e.g. “7 days”) and a label (e.g. “design to launch”). Leave a value/label pair blank to hide it.', 'sage'),
        __('In their words', 'sage')                     => __('An optional client testimonial — the quote plus who said it. Leave blank for concept projects that don’t have a real client.', 'sage'),
        __('Under the hood', 'sage')                     => __('What was delivered (one item per line) and the tech or tools used (comma-separated). Shown lower down the case-study page.', 'sage'),
        __('Call to action (optional override)', 'sage') => __('Optional. Change the button label and link for this one project — leave both blank to use the site-wide contact call-to-action.', 'sage'),
    ];
    return $hints[$label] ?? '';
}

/** Sanitize callback for a field type. */
function project_field_sanitizer(string $type): string
{
    switch ($type) {
        case 'url':
            return 'esc_url_raw';
        case 'textarea':
        case 'lines':
            return 'sanitize_textarea_field';
        default:
            return 'sanitize_text_field';
    }
}

/**
 * Case Study meta box (below the editor).
 */
add_action('add_meta_boxes', function () {
    add_meta_box('rv_project_case', __('Case Study Details', 'sage'), function ($post) {
        wp_nonce_field('rv_project_details', 'rv_project_details_nonce');

        echo '<style>
            .rv-mb h4{margin:1.4rem 0 .5rem;padding-bottom:.35rem;border-bottom:1px solid #dcdcde;font-size:13px;text-transform:uppercase;letter-spacing:.04em;color:#50575e}
            .rv-mb h4:first-child{margin-top:.3rem}
            .rv-mb .rv-mb-acc{border:1px solid #dcdcde;border-radius:6px;margin:.55rem 0;background:#fff;overflow:hidden}
            .rv-mb .rv-mb-acc>summary{list-style:none;cursor:pointer;padding:.6rem .75rem;font-size:13px;text-transform:uppercase;letter-spacing:.04em;color:#1d2327;font-weight:600;background:#f6f7f7;display:flex;align-items:center;gap:.5rem;user-select:none}
            .rv-mb .rv-mb-acc>summary::-webkit-details-marker{display:none}
            .rv-mb .rv-mb-acc>summary::before{content:"▸";color:#787c82;font-size:11px;transition:transform .15s ease;flex:none}
            .rv-mb .rv-mb-acc[open]>summary::before{transform:rotate(90deg)}
            .rv-mb .rv-mb-acc>summary:hover{background:#eef0f1}
            .rv-mb .rv-mb-acc>summary:focus-visible{outline:2px solid #3858e9;outline-offset:-2px}
            .rv-mb .rv-mb-acc-b{padding:.7rem .85rem .85rem}
            .rv-mb .rv-mb-grid{display:grid;grid-template-columns:1fr 1fr;gap:.75rem 1.25rem}
            .rv-mb .rv-mb-full{grid-column:1 / -1}
            .rv-mb label{display:block;font-weight:600;margin-bottom:.2rem;font-size:12px}
            .rv-mb input[type=text],.rv-mb input[type=url],.rv-mb textarea{width:100%}
            .rv-mb textarea{min-height:80px}
            .rv-mb .description{color:#787c82;font-weight:400}
            .rv-mb .rv-mb-hint{color:#646970;font-size:12px;line-height:1.5;margin:.1rem 0 .7rem;font-weight:400}
        </style>';

        echo '<div class="rv-mb">';
        echo '<p class="description">' . esc_html__('Everything here is optional. Filled fields power the case-study page; empty ones are simply skipped, and the editor content below still shows as the write-up.', 'sage') . '</p>';

        $rvGi = 0;
        foreach (project_field_groups() as $group => $fields) {
            // Collapsible section (native <details> — no JS, keyboard-accessible).
            echo '<details class="rv-mb-acc"' . ($rvGi === 0 ? ' open' : '') . '>';
            echo '<summary class="rv-mb-acc-h">' . esc_html($group) . '</summary>';
            echo '<div class="rv-mb-acc-b">';
            $rvGi++;
            if ($hint = project_group_hint($group)) {
                echo '<p class="rv-mb-hint">' . esc_html($hint) . '</p>';
            }
            echo '<div class="rv-mb-grid">';
            foreach ($fields as $key => $def) {
                [$label, $type, $placeholder] = $def;
                $val  = get_post_meta($post->ID, $key, true);
                $full = in_array($type, ['textarea', 'lines', 'checkbox'], true) || in_array($key, ['_rv_summary', '_rv_services', '_rv_tech'], true);
                echo '<p class="' . ($full ? 'rv-mb-full' : '') . '">';
                if ($type === 'checkbox') {
                    printf('<label for="%1$s" style="font-weight:600"><input type="checkbox" id="%1$s" name="%1$s" value="1" %2$s style="margin-right:.45rem"> %3$s</label>', esc_attr($key), checked($val, '1', false), esc_html($label));
                    echo '</p>';
                    continue;
                }
                printf('<label for="%1$s">%2$s</label>', esc_attr($key), esc_html($label));
                if (in_array($type, ['textarea', 'lines'], true)) {
                    printf('<textarea id="%1$s" name="%1$s" placeholder="%3$s">%2$s</textarea>', esc_attr($key), esc_textarea($val), esc_attr($placeholder));
                } else {
                    printf('<input type="%4$s" id="%1$s" name="%1$s" value="%2$s" placeholder="%3$s" />', esc_attr($key), esc_attr($val), esc_attr($placeholder), $type === 'url' ? 'url' : 'text');
                }
                echo '</p>';
            }
            echo '</div>'; // .rv-mb-grid
            echo '</div></details>'; // .rv-mb-acc-b / .rv-mb-acc
        }
        echo '</div>';
    }, 'project', 'normal', 'high');
});

add_action('save_post_project', function ($post_id) {
    if (! isset($_POST['rv_project_details_nonce']) ||
        ! wp_verify_nonce(sanitize_key($_POST['rv_project_details_nonce']), 'rv_project_details')) {
        return;
    }
    if (defined('DOING_AUTOSAVE') && DOING_AUTOSAVE) {
        return;
    }
    if (! current_user_can('edit_post', $post_id)) {
        return;
    }

    foreach (project_field_groups() as $fields) {
        foreach ($fields as $key => $def) {
            if ($def[1] === 'checkbox') {
                isset($_POST[$key]) ? update_post_meta($post_id, $key, '1') : delete_post_meta($post_id, $key);
                continue;
            }
            if (! isset($_POST[$key])) {
                continue;
            }
            $sanitize = project_field_sanitizer($def[1]);
            $value    = call_user_func($sanitize, wp_unslash($_POST[$key]));
            $value === '' ? delete_post_meta($post_id, $key) : update_post_meta($post_id, $key, $value);
        }
    }
});

/**
 * Admin list columns for Projects: Client + Live link.
 */
add_filter('manage_project_posts_columns', function ($cols) {
    $out = [];
    foreach ($cols as $key => $label) {
        $out[$key] = $label;
        if ($key === 'title') {
            $out['rv_client'] = __('Client', 'sage');
        }
    }
    // Move the date column to the end, after our Live column.
    $date = $out['date'] ?? null;
    unset($out['date']);
    $out['rv_live'] = __('Live', 'sage');
    if ($date) {
        $out['date'] = $date;
    }
    return $out;
});

add_action('manage_project_posts_custom_column', function ($col, $post_id) {
    if ($col === 'rv_client') {
        $client  = get_post_meta($post_id, '_rv_client', true);
        $concept = get_post_meta($post_id, '_rv_is_concept', true);
        echo esc_html($client ?: '—');
        if ($concept) {
            echo ' <span style="display:inline-block;font-size:10px;letter-spacing:.04em;text-transform:uppercase;background:#eef3ee;color:#2E5245;border-radius:999px;padding:1px 7px;margin-left:4px">' . esc_html__('Concept', 'sage') . '</span>';
        }
    } elseif ($col === 'rv_live') {
        $url = get_post_meta($post_id, '_rv_url', true);
        echo $url
            ? '<a href="' . esc_url($url) . '" target="_blank" rel="noopener noreferrer">' . esc_html__('View ↗', 'sage') . '</a>'
            : '—';
    }
}, 10, 2);

/**
 * GitHub Pages origin for a concept's named repo.
 * Filter `rv/concept_pages_origin` to override (e.g. a custom domain host).
 */
function concept_pages_origin(): string
{
    return apply_filters('rv/concept_pages_origin', 'https://matthummel-pa.github.io');
}

/**
 * Named GitHub repo that holds one Work-page concept for independent updates.
 */
function concept_repo_name(string $folder): string
{
    return $folder . '-theme';
}

/**
 * Live demo URL for a concept after it is published to its named GitHub repo.
 * Filter `rv/concept_demo_url` to override a single site.
 */
function concept_demo_url(string $folder, string $file = ''): string
{
    $url = rtrim(concept_pages_origin(), '/') . '/' . rawurlencode(concept_repo_name($folder)) . '/';
    if ($file !== '') {
        $url .= ltrim($file, '/');
    }

    return apply_filters('rv/concept_demo_url', $url, $folder, $file);
}

/**
 * Deployed thumbnail for a concept folder.
 *
 * Full concept sites stay off SiteGround (`concept/` is rsync-excluded) and
 * live on GitHub Pages. Thumbnails are copied to assets/concept-previews/ so
 * Home / Work cards do not 404.
 */
function concept_preview_uri(string $folder): string
{
    $folder = sanitize_file_name($folder);
    if ($folder === '' || $folder === '.' || $folder === '..') {
        return '';
    }
    $deployed = 'assets/concept-previews/' . $folder . '.jpg';
    if (is_file(get_theme_file_path($deployed))) {
        return get_theme_file_uri($deployed);
    }
    $legacy = 'concept/' . $folder . '/preview.jpg';
    if (is_file(get_theme_file_path($legacy))) {
        return get_theme_file_uri($legacy);
    }

    return '';
}

/**
 * Map a stored preview URL onto a file that actually deploys.
 * Existing Project meta still points at theme/concept/{folder}/preview.jpg.
 */
function resolve_preview_url(string $url): string
{
    if ($url !== '' && preg_match('#/concept/([^/]+)/preview\.jpg#', $url, $m)) {
        $mapped = concept_preview_uri(rawurldecode($m[1]));
        if ($mapped !== '') {
            return $mapped;
        }
    }

    return $url;
}

/**
 * The concept-site portfolio, seeded once as Project posts so it populates the
 * Projects admin list and the Work grid. Each live demo lives in its own
 * GitHub repo (`{folder}-theme`) and deploys to GitHub Pages.
 */
function concept_project_seeds(): array
{
    return [
        [
            'folder' => 'gettysburg-hotel', 'repo' => 'gettysburg-hotel-theme', 'title' => 'The Lantern & Laurel Inn', 'type' => 'Hotels',
            'eyebrow' => 'Concept · Boutique inn', 'industry' => 'Hospitality · Boutique inn',
            'summary' => 'A nine-room heritage inn concept with a direct booking bar and a warm forest-and-brass identity.',
            'services' => 'Web design, Reservations UX, Copywriting, Accessibility',
            'challenge' => "Small Gettysburg inns bleed 15–20% of every booking to the big travel sites — and those sites bury the property's own story. Guests couldn't even check dates without a phone call during business hours.",
            'approach' => "A warm, heritage-forward design that puts a direct booking bar front and center, so guests reserve on the inn's own site with no commission and no phone tag. Rooms, rates, and the neighborhood are all one scroll away.",
            'result' => "A concept that could recover commission on every direct booking, reads beautifully on a phone, and lets a first-time guest feel the candlelit welcome before they ever arrive.",
            'metrics' => [['0%', 'booking commission'], ['9', 'rooms, book direct'], ['24/7', 'online booking']],
            'deliverables' => "Direct booking bar\nRoom & rate pages\nGoogle Business Profile\nMobile-first, accessible build\nTraining video + handoff",
            'tech' => 'WordPress, Sage, Cloudflare',
        ],
        [
            'folder' => 'hotel-cupola-field', 'repo' => 'hotel-cupola-field-theme', 'title' => 'The Cupola & Field Hotel', 'type' => 'Hotels',
            'eyebrow' => 'Concept · Modern hotel', 'industry' => 'Hospitality · Hotel',
            'summary' => 'A modern boutique hotel concept with a live reservation engine and concierge + guest-request tools.',
            'services' => 'Web design, Booking engine, Customer-service tools',
            'challenge' => "A hotel site has to answer two questions instantly: 'is my date open?' and 'can someone help me right now?' Most local hotel sites answer neither — no live rates, no way to reach the front desk after hours.",
            'approach' => "A live reservation engine that recalculates the nightly rate and total as guests change dates and rooms, paired with a concierge chat widget and a guest-request checklist so service starts before check-in.",
            'result' => "A concept that turns browsing into booking with instant pricing, and turns questions into answers with an always-on concierge — the two moments that win or lose a reservation.",
            'metrics' => [['Live', 'rate + total'], ['28', 'boutique rooms'], ['24/7', 'concierge chat']],
            'deliverables' => "Live reservation engine\nConcierge chat widget\nGuest-request tools\nFAQ + front-desk contact\nMobile-first, accessible build",
            'tech' => 'WordPress, Sage, custom JS',
        ],
        [
            'folder' => 'gettysburg-restaurant', 'repo' => 'gettysburg-restaurant-theme', 'title' => 'Field & Musket Tavern', 'type' => 'Restaurants',
            'eyebrow' => 'Concept · Farm-to-table tavern', 'industry' => 'Food & drink · Restaurant',
            'summary' => 'A seasonal tavern concept with an editorial, always-current menu and a clear reservation path.',
            'services' => 'Web design, Menu design, Reservations',
            'challenge' => "Diners decide on their phones, and a menu trapped in a PDF or a photo is the fastest way to lose them. This tavern's seasonal menu changed faster than its old site could keep up.",
            'approach' => "An editorial, easy-to-read menu the owner can update in minutes, plus a one-tap reservation path and the farm-to-table story that justifies the price — all in a warm, appetizing brand.",
            'result' => "A concept where the menu is always current, reservations are a tap away, and the local-sourcing story does the selling — no PDF, no stale specials.",
            'metrics' => [['1-tap', 'reservations'], ['Minutes', 'to update the menu'], ['100%', 'mobile-first']],
            'deliverables' => "Editorial menu system\nReservations flow\nStory & sourcing pages\nGoogle Business Profile\nTraining video + handoff",
            'tech' => 'WordPress, Sage',
        ],
        [
            'folder' => 'restaurant-cannon-and-crumb', 'repo' => 'restaurant-cannon-and-crumb-theme', 'title' => 'Cannon & Crumb', 'type' => 'Restaurants',
            'eyebrow' => 'Concept · Cafe & bakery', 'industry' => 'Food & drink · Cafe',
            'summary' => 'An all-day cafe concept with a tabbed, filterable menu and a real online-ordering cart.',
            'services' => 'Web design, Online ordering, Menu tools',
            'challenge' => "A busy cafe leaves money on the counter without online ordering — and a wall-of-text menu makes it worse. Regulars wanted to order ahead; first-timers wanted to filter for vegan and gluten-free.",
            'approach' => "An interactive, tabbed and filterable menu with a working online-ordering cart — pickup or delivery, running total, and a clean checkout — so the morning rush moves to the app, not the line.",
            'result' => "A concept that captures ahead-orders, answers dietary questions instantly, and turns a static menu into a working storefront that pays for itself.",
            'metrics' => [['Order-ahead', 'pickup or delivery'], ['Filterable', 'dietary tags'], ['7 days', 'a week']],
            'deliverables' => "Tabbed, filterable menu\nOnline-ordering cart\nPickup/delivery toggle\nCatering + newsletter\nMobile-first, accessible build",
            'tech' => 'WordPress, Sage, custom JS',
        ],
        [
            'folder' => 'gettysburg-retail', 'repo' => 'gettysburg-retail-theme', 'title' => 'Diamond & Ridge Mercantile', 'type' => 'Retail',
            'eyebrow' => 'Concept · Boutique retail', 'industry' => 'Retail · E-commerce',
            'summary' => 'A downtown mercantile concept with a full product shop, slide-in cart, and checkout.',
            'services' => 'Web design, E-commerce, Cart & checkout',
            'challenge' => "A downtown shop with foot traffic but no online store misses every visitor who wanted 'that candle' shipped home. Generic marketplace listings hid the local, handmade story entirely.",
            'approach' => "A full e-commerce storefront with a slide-in cart and clean checkout, wrapped in a brand that leads with 'locally made' — so the shop keeps selling to visitors long after they've left town.",
            'result' => "A concept that opens a second register online, keeps the maker story front and center, and lets a one-time visitor become a repeat customer from anywhere.",
            'metrics' => [['24/7', 'online store'], ['Local-first', 'branding'], ['+1', 'register, online']],
            'deliverables' => "Product shop + cart\nCheckout flow\nCollections & story\nNewsletter capture\nMobile-first, accessible build",
            'tech' => 'WordPress, WooCommerce-ready',
        ],
        [
            'folder' => 'retail-ridgeline-outfitters', 'repo' => 'retail-ridgeline-outfitters-theme', 'title' => 'Ridgeline Outfitters', 'type' => 'Retail',
            'eyebrow' => 'Concept · Outdoor gear shop', 'industry' => 'Retail · E-commerce',
            'summary' => 'An outdoor gear shop concept with filters, quick-view, wishlist, and a cart with a free-shipping bar.',
            'services' => 'Web design, E-commerce, Product filtering',
            'challenge' => "Outdoor shoppers compare gear across ten browser tabs. A store with no filters, sizes, or wishlist loses them to Amazon before they ever notice the local expertise.",
            'approach' => "A gear-forward shop with category filters, quick-view, size selection, a wishlist, and a cart with a free-shipping progress bar — the conversion tools shoppers now expect, in a rugged local brand.",
            'result' => "A concept that competes with the big retailers on convenience while winning on local knowledge and battlefield-trail credibility.",
            'metrics' => [['Quick-view', '+ filters'], ['Wishlist', '+ cart'], ['Free-ship', 'progress bar']],
            'deliverables' => "Filterable gear grid\nQuick-view + wishlist\nCart + free-ship bar\nCollections & story\nMobile-first, accessible build",
            'tech' => 'WordPress, WooCommerce-ready, custom JS',
        ],
        [
            'folder' => 'tour-hallowed-ground-tours', 'repo' => 'tour-hallowed-ground-tours-theme', 'title' => 'Hallowed Ground Battlefield Tours', 'type' => 'Tours',
            'eyebrow' => 'Concept · Licensed-guide tours', 'industry' => 'Tourism · Guided tours',
            'summary' => 'A Gettysburg licensed guide website concept: historical vs after-dark pathways, five tours, an OpenLayers battlefield map, and a Sage + WooCommerce handoff.',
            'services' => 'Web design, Tour catalog UX, Booking concept, Interactive map, Local SEO',
            'timeline' => 'Static HTML concept · Sage later',
            'challenge' => "A Gettysburg licensed guide website has to sell small-group walking, bus, hike, lantern, and private sunrise tours without looking like a food-tour sibling brand. Call-to-reserve sites lose the people who plan at night on their phones; a checkout that pretends to charge a card is worse.",
            'approach' => "I built the working HTML as the front-end contract for a later Sage + WooCommerce theme. Home splits daylight field walks vs lantern walks; the catalog filters all five tours; guides stay licensed roles, not invented people; The Area is an OpenLayers map (~800 OSM monuments, satellite, guest itinerary PDF). Sage comments in the markup mark the Blade handoff. Until that theme ships, booking and contact stay honest demos — they do not charge a card or send production email unless Netlify Forms is on.",
            'result' => "A clickable GitHub Pages / Netlify demo with a distinct identity from First Shot. NAP is labeled fiction (100 Sample Street, (717) 555-0100, tours@hallowedground.test). Parking copy stays generic downtown lots. When WordPress ships, WooCommerce picks up the same pages.",
            'metrics' => [['5', 'named tours'], ['~800', 'map monuments'], ['Sage + Woo', 'intended stack']],
            'deliverables' => "Home with historical / after-dark pathways\nFive-tour catalog + filters\nLicensed-guide positioning (roles, not invented bios)\nThe Area map (OpenLayers, satellite, itinerary PDF)\nMulti-step booking concept (demo — no card charge)\nContact + FAQ (demo form)\nSage comments for the WordPress handoff",
            'tech' => 'Static HTML, OpenLayers, OSM, Esri World Imagery, Netlify Forms, Sage-ready',
            'set_thumbnail' => true,
            'seo_title' => 'Gettysburg Licensed Guide Website | Ridges & Valleys',
            'seo_desc' => 'Gettysburg licensed guide website concept for small-group battlefield tours — five tours, day and lantern walks, a field map. Open the live demo.',
            'seo_focus' => 'gettysburg licensed guide website',
            'seo_image_alt' => 'Gettysburg licensed guide website concept — Hallowed Ground homepage nav and hero',
            'seo_content' => <<<'HTML'
<!-- wp:paragraph -->
<p>This Gettysburg licensed guide website is a working HTML concept for a small-group battlefield tour company — walking, bus, hike, lantern, and private sunrise tours led by Association of Licensed Battlefield Guides. It's a concept — Hallowed Ground Battlefield Tours isn't a real ticket office — but it's built the way a real Gettysburg operator would need it: a field-dispatch brand, honest demo booking, and a Sage + WooCommerce handoff when the WordPress theme ships.</p>
<!-- /wp:paragraph -->

<!-- wp:image {"id":{{IMAGE_ID}},"sizeSlug":"large","linkDestination":"none"} -->
<figure class="wp-block-image size-large"><img src="{{IMAGE_URL}}" alt="{{IMAGE_ALT}}" class="wp-image-{{IMAGE_ID}}"/></figure>
<!-- /wp:image -->

<!-- wp:heading -->
<h2 class="wp-block-heading">What a Gettysburg licensed guide website has to get right</h2>
<!-- /wp:heading -->

<!-- wp:paragraph -->
<p>Visitors plan Gettysburg tours at night, on their phones. A Gettysburg licensed guide website has to show daylight vs after-dark pathways immediately, name the five tours, and never pretend to charge a card until a real storefront exists. It also has to work for everyone, which is why this concept is written for WCAG-minded type (Archivo Black, Atkinson Hyperlegible, IBM Plex Mono) and follows the accessibility ideas in the <a href="https://www.w3.org/WAI/standards-guidelines/wcag/">W3C Web Accessibility Initiative</a>. Call-to-reserve copy loses those after-hours planners; a fake checkout is worse.</p>
<!-- /wp:paragraph -->

<!-- wp:heading -->
<h2 class="wp-block-heading">How the concept came together</h2>
<!-- /wp:heading -->

<!-- wp:paragraph -->
<p>The demo is static HTML — the front-end contract for a later Roots Sage theme with WooCommerce. Home splits historical field walks and lantern walks. The catalog filters all five products. Guides stay licensed roles, not invented personal bios. The Area is an OpenLayers map of the battlefield: OSM streets, Esri satellite, about 800 clickable monuments, and a guest itinerary guests can save as a PDF. Sage comments in the markup mark where Blade templates would take over.</p>
<!-- /wp:paragraph -->

<!-- wp:paragraph -->
<p>Until that theme ships, booking and contact forms stay demos. They do not charge a card or send production email unless Netlify Forms is on. Parking copy stays generic downtown lots. NAP is labeled fiction: 100 Sample Street, Gettysburg, PA 17325, (717) 555-0100, tours@hallowedground.test. The look is compass, ticket-cut cards, and slate + gold — distinct from the sister food-tour concept.</p>
<!-- /wp:paragraph -->

<!-- wp:heading -->
<h2 class="wp-block-heading">Why this matters for Adams County tour operators</h2>
<!-- /wp:heading -->

<!-- wp:paragraph -->
<p>Gettysburg runs on visitors who compare tours on a phone before they ever call. A licensed-guide company that only says “leave a voicemail” loses the booking to whoever made times, meeting points, and the after-dark option obvious. This concept proves the pages: dual pathways, a filterable catalog, a map guests can actually use, and a checkout that stays honest until WooCommerce is real.</p>
<!-- /wp:paragraph -->

<!-- wp:heading -->
<h2 class="wp-block-heading">Built to be found in local search</h2>
<!-- /wp:heading -->

<!-- wp:paragraph -->
<p>Titles, meta, Open Graph, JSON-LD, and Adams County town copy stay in sync on the demo. Canonical tags point at the GitHub Pages origin so SEO has one URL until you choose otherwise. A live WordPress build would keep that same local-search discipline: Google Business Profile, location copy, and structured data — not a second brand that collides with a food-tour sister site.</p>
<!-- /wp:paragraph -->

<!-- wp:heading -->
<h2 class="wp-block-heading">What's inside the concept</h2>
<!-- /wp:heading -->

<!-- wp:list -->
<ul class="wp-block-list"><!-- wp:list-item -->
<li>Home with historical and after-dark pathways</li>
<!-- /wp:list-item -->

<!-- wp:list-item -->
<li>Five named tours with catalog filters</li>
<!-- /wp:list-item -->

<!-- wp:list-item -->
<li>Licensed-guide positioning — roles, not invented bios</li>
<!-- /wp:list-item -->

<!-- wp:list-item -->
<li>The Area map (OpenLayers, satellite, itinerary PDF)</li>
<!-- /wp:list-item -->

<!-- wp:list-item -->
<li>Multi-step booking and contact as demos — no card charge</li>
<!-- /wp:list-item --></ul>
<!-- /wp:list -->

<!-- wp:heading -->
<h2 class="wp-block-heading">Make it your Gettysburg licensed guide website</h2>
<!-- /wp:heading -->

<!-- wp:paragraph -->
<p>This is a concept, but the pages are real and clickable. Browse the other <a href="/work/">concept sites</a> made for local Gettysburg businesses, then <a href="/contact/">tell me about your tours</a>. You'll get one fixed price agreed up front, a fast and accessible site, and full ownership of everything when it's done.</p>
<!-- /wp:paragraph -->
HTML,
        ],
        [
            'folder' => 'tour-first-shot-food-tours', 'repo' => 'tour-first-shot-food-tours-theme', 'title' => 'First Shot Food & History Tours', 'type' => 'Tours',
            'eyebrow' => 'Concept · Walking tours', 'industry' => 'Tourism · Walking tours',
            'summary' => 'A walking-tour concept with a calendar booking flow, add-ons, and demo payment.',
            'services' => 'Web design, Calendar booking, Payments UX',
            'challenge' => "A tour with limited daily slots needs to show what's actually available — a static 'contact us' form oversells sold-out dates and frustrates guests before they even arrive.",
            'approach' => "A calendar-driven booking flow: pick an available date, see real time slots, add tickets and upgrades with a live total, and check out — so availability is honest and booking is effortless.",
            'result' => "A concept that turns a scheduling headache into a self-serve calendar, upsells add-ons at checkout, and confirms instantly.",
            'metrics' => [['Calendar', 'real availability'], ['Add-ons', 'at checkout'], ['Instant', 'confirmation']],
            'deliverables' => "Availability calendar\nSlot + add-on booking\nPayment UX\nTour lineup + FAQ\nMobile-first, accessible build",
            'tech' => 'WordPress, Sage, Stripe-ready',
        ],
        [
            'folder' => 'realtor-ridgeline-realty', 'repo' => 'realtor-ridgeline-realty-theme', 'title' => 'Ridgeline Realty', 'type' => 'Real Estate',
            'eyebrow' => 'Concept · Real estate agency', 'industry' => 'Real estate · Agency',
            'summary' => 'A realty concept with filterable listings, a live mortgage calculator, and support tools.',
            'services' => 'Web design, Listing search, Lead capture',
            'challenge' => "Buyers browse listings on their phones and bounce when a site is slow, unfilterable, or missing the numbers. Those leads go straight to Zillow — and the agency never sees them.",
            'approach' => "A fast, filterable listings experience with a search bar, a live mortgage calculator, and easy 'schedule a showing' and 'contact an agent' tools — keeping buyers on the agency's own site.",
            'result' => "A concept that holds buyers with real search and real numbers, then converts them with one-tap showing requests and direct agent contact.",
            'metrics' => [['Filterable', 'listings'], ['Live', 'mortgage calc'], ['1-tap', 'showings']],
            'deliverables' => "Filterable listings\nMortgage calculator\nShowing + contact tools\nAgent profiles\nMobile-first, accessible build",
            'tech' => 'WordPress, Sage, IDX-ready',
        ],
        [
            'folder' => 'realtor-keystone-homes-and-land', 'repo' => 'realtor-keystone-homes-and-land-theme', 'title' => 'Keystone Homes & Land', 'type' => 'Real Estate',
            'eyebrow' => 'Concept · Land & farms agency', 'industry' => 'Real estate · Land & rural',
            'summary' => 'A land-and-farms concept with grid/map listings, acreage filters, and financing tools.',
            'services' => 'Web design, Map listings, Support tools',
            'challenge' => "Land and farm buyers need different filters — acreage, township, lot type — and a map. A generic home-search template can't show a 40-acre parcel the way it deserves.",
            'approach' => "A grid-and-map listings tool built for rural property, with acreage and township filters, a land-loan estimate, financing pre-qualification, and a call scheduler — the tools land buyers actually use.",
            'result' => "A concept that speaks land buyers' language, shows parcels on a map, and moves serious buyers from browsing to a booked call.",
            'metrics' => [['Grid + map', 'listings'], ['Acreage', '+ township filters'], ['Pre-qual', '+ call scheduler']],
            'deliverables' => "Grid/map listings\nAcreage + township filters\nLand-loan estimate\nPre-qual + call scheduler\nMobile-first, accessible build",
            'tech' => 'WordPress, Sage, IDX-ready',
        ],
    ];
}

/**
 * Find-or-create each concept project and (re)write its meta so re-running the
 * seed refreshes content on existing posts without duplicating them.
 */
function seed_concept_projects(): void
{
    foreach (concept_project_seeds() as $c) {
        $slug = 'concept-' . $c['folder'];
        $existing = get_page_by_path($slug, OBJECT, 'project');

        if ($existing) {
            $post_id = $existing->ID;
            wp_update_post([
                'ID'           => $post_id,
                'post_title'   => $c['title'],
                'post_excerpt' => $c['summary'],
            ]);
        } else {
            $post_id = wp_insert_post([
                'post_type'    => 'project',
                'post_status'  => 'publish',
                'post_title'   => $c['title'],
                'post_name'    => $slug,
                'post_excerpt' => $c['summary'],
            ], true);
            if (is_wp_error($post_id) || ! $post_id) {
                continue;
            }
        }

        update_post_meta($post_id, '_rv_eyebrow', $c['eyebrow']);
        update_post_meta($post_id, '_rv_client', $c['title']);
        update_post_meta($post_id, '_rv_summary', $c['summary']);
        update_post_meta($post_id, '_rv_industry', $c['industry']);
        update_post_meta($post_id, '_rv_location', 'Gettysburg, PA');
        if (! empty($c['timeline'])) {
            update_post_meta($post_id, '_rv_timeline', $c['timeline']);
        }
        update_post_meta($post_id, '_rv_services', $c['services']);
        update_post_meta($post_id, '_rv_url', concept_demo_url($c['folder']));
        update_post_meta($post_id, '_rv_preview', concept_preview_uri($c['folder']));
        update_post_meta($post_id, '_rv_is_concept', '1');
        update_post_meta($post_id, '_rv_challenge', $c['challenge']);
        update_post_meta($post_id, '_rv_approach', $c['approach']);
        update_post_meta($post_id, '_rv_result', $c['result']);
        update_post_meta($post_id, '_rv_deliverables', $c['deliverables']);
        update_post_meta($post_id, '_rv_tech', $c['tech']);

        foreach ([1, 2, 3] as $i) {
            $metric = $c['metrics'][$i - 1] ?? null;
            update_post_meta($post_id, "_rv_m{$i}_value", $metric[0] ?? '');
            update_post_meta($post_id, "_rv_m{$i}_label", $metric[1] ?? '');
        }

        wp_set_object_terms($post_id, $c['type'], 'project_type');

        if (! empty($c['set_thumbnail'])) {
            seed_concept_thumbnail((int) $post_id, (string) $c['folder']);
        }

        seed_concept_rank_math((int) $post_id, $c);
        seed_concept_writeup((int) $post_id, $c);
    }
}

/**
 * Sideload a concept homepage screenshot as the Project featured image.
 * Skips when the current thumbnail already matches this theme file.
 */
function seed_concept_thumbnail(int $post_id, string $folder): void
{
    $folder = sanitize_file_name($folder);
    $path = get_theme_file_path('assets/concept-previews/' . $folder . '.jpg');
    if ($folder === '' || ! is_file($path)) {
        return;
    }
    $hash = md5_file($path);
    if ($hash === false) {
        return;
    }
    $existing = (int) get_post_thumbnail_id($post_id);
    if ($existing && (string) get_post_meta($post_id, '_rv_thumb_hash', true) === $hash) {
        return;
    }

    require_once ABSPATH . 'wp-admin/includes/file.php';
    require_once ABSPATH . 'wp-admin/includes/media.php';
    require_once ABSPATH . 'wp-admin/includes/image.php';

    $tmp = wp_tempnam($path);
    if (! $tmp || ! copy($path, $tmp)) {
        return;
    }

    $attach_id = media_handle_sideload([
        'name'     => $folder . '-home-hero.jpg',
        'tmp_name' => $tmp,
    ], $post_id, sprintf(
        /* translators: %s: project title */
        __('%s homepage — navigation and hero', 'sage'),
        get_the_title($post_id)
    ));
    if (is_wp_error($attach_id)) {
        @unlink($tmp);

        return;
    }

    set_post_thumbnail($post_id, (int) $attach_id);
    update_post_meta($post_id, '_rv_thumb_hash', $hash);
    if ($existing && $existing !== (int) $attach_id) {
        wp_delete_attachment($existing, true);
    }
}

/**
 * Write Rank Math title, description, focus keyword, and social images.
 */
function seed_concept_rank_math(int $post_id, array $c): void
{
    $title = trim((string) ($c['seo_title'] ?? ''));
    $desc  = trim((string) ($c['seo_desc'] ?? ''));
    $focus = trim((string) ($c['seo_focus'] ?? ''));
    $alt   = trim((string) ($c['seo_image_alt'] ?? ''));
    if ($title === '' && $desc === '' && $focus === '') {
        return;
    }
    if ($title !== '') {
        update_post_meta($post_id, 'rank_math_title', $title);
        update_post_meta($post_id, 'rank_math_facebook_title', $title);
    }
    if ($desc !== '') {
        update_post_meta($post_id, 'rank_math_description', $desc);
        update_post_meta($post_id, 'rank_math_facebook_description', $desc);
    }
    if ($focus !== '') {
        update_post_meta($post_id, 'rank_math_focus_keyword', $focus);
    }

    $thumb = (int) get_post_thumbnail_id($post_id);
    if ($thumb <= 0) {
        return;
    }
    $url = wp_get_attachment_image_url($thumb, 'full');
    if (! $url) {
        return;
    }
    update_post_meta($post_id, 'rank_math_facebook_image', $url);
    update_post_meta($post_id, 'rank_math_facebook_image_id', $thumb);
    update_post_meta($post_id, 'rank_math_twitter_use_facebook', 'on');
    update_post_meta($post_id, 'rank_math_twitter_image', $url);
    update_post_meta($post_id, 'rank_math_twitter_image_id', $thumb);
    if ($alt !== '') {
        update_post_meta($thumb, '_wp_attachment_image_alt', $alt);
    }
}

/**
 * Gutenberg write-up Rank Math scores in the editor (keyword in first
 * paragraph, H2, image alt, internal + external links).
 */
function seed_concept_writeup(int $post_id, array $c): void
{
    $html = (string) ($c['seo_content'] ?? '');
    if (trim($html) === '') {
        return;
    }
    $thumb = (int) get_post_thumbnail_id($post_id);
    $url = $thumb ? (string) wp_get_attachment_image_url($thumb, 'large') : '';
    if ($url === '' && $thumb) {
        $url = (string) wp_get_attachment_image_url($thumb, 'full');
    }
    $alt = trim((string) ($c['seo_image_alt'] ?? ''));
    $html = str_replace(
        ['{{IMAGE_ID}}', '{{IMAGE_URL}}', '{{IMAGE_ALT}}'],
        [(string) $thumb, esc_url($url), esc_attr($alt)],
        $html
    );
    wp_update_post([
        'ID'           => $post_id,
        'post_content' => $html,
        'post_excerpt' => $c['summary'] ?? get_post_field('post_excerpt', $post_id),
    ]);
}

/**
 * Run the concept seed once per version. Bump the version to refresh content
 * or add newly-created concepts. Front-end init (not only wp-admin) so a
 * deploy can refresh live Project posts on the next public request.
 * v7: Hallowed Ground Rank Math title / description / social image / write-up.
 */
function maybe_seed_concept_projects(): void
{
    if (get_option('rv_concepts_seed_v') === '7') {
        return;
    }
    if (get_transient('rv_concepts_seeding')) {
        return;
    }
    set_transient('rv_concepts_seeding', '1', MINUTE_IN_SECONDS);
    seed_concept_projects();
    update_option('rv_concepts_seed_v', '7');
    delete_transient('rv_concepts_seeding');
}

add_action('init', __NAMESPACE__ . '\\maybe_seed_concept_projects', 30);
add_action('admin_init', __NAMESPACE__ . '\\maybe_seed_concept_projects');

/**
 * Flush rewrite rules on theme switch so /work/ resolves.
 */
add_action('after_switch_theme', function () {
    flush_rewrite_rules();
});

/**
 * Expose the case-study meta fields to the REST API (read + write for editors)
 * so a Project can be populated programmatically. Protected (underscore-
 * prefixed) meta needs an auth_callback before REST will allow writes.
 */
add_action('init', function () {
    $keys = [
        '_rv_eyebrow', '_rv_client', '_rv_summary', '_rv_industry', '_rv_location',
        '_rv_timeline', '_rv_services', '_rv_url', '_rv_challenge', '_rv_approach',
        '_rv_result', '_rv_m1_value', '_rv_m1_label', '_rv_m2_value', '_rv_m2_label',
        '_rv_m3_value', '_rv_m3_label', '_rv_quote', '_rv_quote_author', '_rv_quote_role',
        '_rv_deliverables', '_rv_tech', '_rv_cta_text', '_rv_cta_url',
        '_rv_is_concept', '_rv_preview', '_rv_designer',
    ];
    foreach ($keys as $k) {
        register_post_meta('project', $k, [
            'type'          => 'string',
            'single'        => true,
            'show_in_rest'  => true,
            'auth_callback' => function () {
                return current_user_can('edit_posts');
            },
        ]);
    }
});

/**
 * Industry buckets for the Work page filters. Order is the studio's preferred
 * pill order; keyword lists are matched against industry / eyebrow / client / title.
 *
 * @return list<array{slug:string,label:string,kw:list<string>}>
 */
function work_industry_defs(): array
{
    return [
        ['slug' => 'restaurants', 'label' => __('Restaurants', 'sage'), 'kw' => ['restaurant', 'kitchen', 'tavern', 'bistro', 'eatery', 'dining', 'food & drink', 'bakery', 'cafe', 'café', 'coffee', 'pizzeria', 'grill', 'pub', 'brewery']],
        ['slug' => 'inns', 'label' => __('Hotels & inns', 'sage'), 'kw' => ['inn', 'hotel', 'b&b', 'bed and breakfast', 'bed & breakfast', 'lodging', 'lodge', 'cottage', 'guesthouse', 'motel', 'hospitality', 'stay']],
        ['slug' => 'retail', 'label' => __('Retail & shops', 'sage'), 'kw' => ['retail', 'shop', 'store', 'mercantile', 'boutique', 'goods', 'market', 'thread', 'apparel', 'gift']],
        ['slug' => 'tours', 'label' => __('Tours', 'sage'), 'kw' => ['tour', 'tours', 'battlefield', 'history', 'guide', 'trail', 'experience', 'sightseeing']],
        ['slug' => 'realestate', 'label' => __('Real estate', 'sage'), 'kw' => ['real estate', 'realty', 'realtor', 'property', 'properties', 'homes for sale', 'broker']],
        ['slug' => 'services', 'label' => __('Professional services', 'sage'), 'kw' => ['law', 'legal', 'attorney', 'lawyer', 'accountant', 'accounting', 'dental', 'dentist', 'medical', 'clinic', 'consult', 'agency', 'financial', 'insurance', 'professional service']],
    ];
}

/**
 * Canonical industry bucket for a project (slug + label).
 *
 * @return array{slug:string,label:string}
 */
function work_project_category(int $id): array
{
    $hay = strtolower(trim(
        (string) get_post_meta($id, '_rv_industry', true) . ' ' .
        (string) get_post_meta($id, '_rv_eyebrow', true) . ' ' .
        (string) get_post_meta($id, '_rv_client', true) . ' ' .
        (string) get_the_title($id)
    ));
    foreach (work_industry_defs() as $d) {
        foreach ($d['kw'] as $k) {
            if ($k !== '' && strpos($hay, $k) !== false) {
                return ['slug' => $d['slug'], 'label' => $d['label']];
            }
        }
    }

    return ['slug' => 'other', 'label' => __('Other', 'sage')];
}

/**
 * Present filter pills for a set of project posts, in preferred order.
 *
 * @param list<\WP_Post> $posts
 * @return array<string, array{label:string,count:int}>
 */
function work_filter_categories(array $posts): array
{
    $present = [];
    foreach ($posts as $p) {
        $c = work_project_category((int) $p->ID);
        if (! isset($present[$c['slug']])) {
            $present[$c['slug']] = ['label' => $c['label'], 'count' => 0];
        }
        $present[$c['slug']]['count']++;
    }
    $ordered = [];
    foreach (work_industry_defs() as $d) {
        if (isset($present[$d['slug']])) {
            $ordered[$d['slug']] = $present[$d['slug']];
        }
    }
    foreach ($present as $slug => $row) {
        if (! isset($ordered[$slug])) {
            $ordered[$slug] = $row;
        }
    }

    return $ordered;
}

/** How many project cards the homepage proof grid shows at once. */
function home_proof_grid_limit(): int
{
    return 3;
}

/**
 * Published projects for the homepage proof grid (filterable, max three shown).
 * Concepts first within each industry; order follows work_industry_defs().
 *
 * @return list<\WP_Post>
 */
function home_proof_type_projects(): array
{
    $q = new \WP_Query([
        'post_type'      => 'project',
        'post_status'    => 'publish',
        'posts_per_page' => 24,
        'no_found_rows'  => true,
        'orderby'        => 'date',
        'order'          => 'DESC',
    ]);
    $by = [];
    foreach ($q->posts as $p) {
        $slug = work_project_category((int) $p->ID)['slug'];
        if (! isset($by[$slug])) {
            $by[$slug] = ['concepts' => [], 'rest' => []];
        }
        if (get_post_meta($p->ID, '_rv_is_concept', true) === '1') {
            $by[$slug]['concepts'][] = $p;
        } else {
            $by[$slug]['rest'][] = $p;
        }
    }

    $picked = [];
    $seen = [];
    $slugs = array_map(static fn ($d) => $d['slug'], work_industry_defs());
    $slugs[] = 'other';
    foreach ($slugs as $slug) {
        if (! isset($by[$slug])) {
            continue;
        }
        foreach (array_merge($by[$slug]['concepts'], $by[$slug]['rest']) as $p) {
            if (isset($seen[$p->ID])) {
                continue;
            }
            $picked[] = $p;
            $seen[$p->ID] = true;
        }
    }

    return $picked;
}

/**
 * Template data for one homepage proof project box.
 *
 * @return array{id:int,cat:string,type:string,kicker:string,title:string,where:string,text:string,href:string,img:string,alt:string,show_credit:bool,metrics:list<array{v:string,l:string}>}
 */
function home_proof_box_data(\WP_Post $post, string $fallback_img = ''): array
{
    $id = (int) $post->ID;
    $cat = work_project_category($id);
    $title = wp_strip_all_tags(html_entity_decode(get_the_title($id), ENT_QUOTES, 'UTF-8'));
    $excerpt = trim((string) get_the_excerpt($post));
    $summary = trim((string) get_post_meta($id, '_rv_summary', true));
    $text = $excerpt !== '' ? $excerpt : $summary;
    $kicker = trim((string) get_post_meta($id, '_rv_eyebrow', true));
    if ($kicker === '') {
        $kicker = trim((string) get_post_meta($id, '_rv_client', true));
    }
    if ($kicker === '') {
        $kicker = __('Featured work', 'sage');
    }
    $img = (string) get_the_post_thumbnail_url($id, 'full');
    if ($img === '') {
        $img = resolve_preview_url((string) get_post_meta($id, '_rv_preview', true));
    }
    $used_fallback = $img === '';
    if ($used_fallback) {
        $img = $fallback_img;
    }

    return [
        'id'          => $id,
        'cat'         => $cat['slug'],
        'type'        => $cat['label'],
        'kicker'      => $kicker,
        'title'       => $title,
        'where'       => trim((string) get_post_meta($id, '_rv_location', true)),
        'text'        => $text,
        'href'        => (string) get_permalink($id),
        'img'         => $img,
        'alt'         => sprintf(/* translators: %s: project title */ __('Gettysburg web design case study: %s', 'sage'), $title),
        'show_credit' => $used_fallback,
        'metrics'     => home_proof_project_metrics($id),
    ];
}

/**
 * Card fields for one project on the Work grid.
 *
 * @return array{cat:array{slug:string,label:string},concept:bool,url:string,eyebrow:string,summary:string,preview:string,industry:string,location:string,metric:string}
 */
function work_card_data(int $id): array
{
    $cat = work_project_category($id);
    $url = trim((string) get_post_meta($id, '_rv_url', true));
    if ($url !== '' && ! preg_match('#^https?://#i', $url)) {
        $url = '';
    }
    $m1v = trim((string) get_post_meta($id, '_rv_m1_value', true));
    $m1l = trim((string) get_post_meta($id, '_rv_m1_label', true));
    $eyebrow = trim((string) get_post_meta($id, '_rv_eyebrow', true));
    if ($eyebrow === '') {
        $eyebrow = trim((string) get_post_meta($id, '_rv_client', true));
    }
    if ($eyebrow === '') {
        $eyebrow = __('Case study', 'sage');
    }
    $summary = trim((string) get_post_meta($id, '_rv_summary', true));
    $industry = trim((string) get_post_meta($id, '_rv_industry', true));

    return [
        'cat'      => $cat,
        'concept'  => get_post_meta($id, '_rv_is_concept', true) === '1',
        'url'      => $url,
        'eyebrow'  => $eyebrow,
        'summary'  => $summary,
        'preview'  => resolve_preview_url((string) get_post_meta($id, '_rv_preview', true)),
        'industry' => $industry !== '' ? $industry : $cat['label'],
        'location' => trim((string) get_post_meta($id, '_rv_location', true)),
        'metric'   => $m1v !== '' ? trim($m1v . ($m1l !== '' ? ' · ' . $m1l : '')) : '',
    ];
}

/**
 * CollectionPage + ItemList JSON-LD for the Work index.
 *
 * @param list<\WP_Post> $posts
 */
function work_itemlist_jsonld(array $posts): string
{
    $elements = [];
    $pos = 0;
    foreach ($posts as $p) {
        $pos++;
        $elements[] = [
            '@type'    => 'ListItem',
            'position' => $pos,
            'url'      => get_permalink($p),
            'name'     => html_entity_decode(wp_strip_all_tags(get_the_title($p)), ENT_QUOTES | ENT_HTML5, 'UTF-8'),
        ];
    }
    if ($elements === []) {
        return '';
    }
    $schema = [
        '@context'   => 'https://schema.org',
        '@type'      => 'CollectionPage',
        'name'       => __('Gettysburg web design work', 'sage'),
        'url'        => (string) get_permalink(),
        'mainEntity' => [
            '@type'           => 'ItemList',
            'itemListElement' => $elements,
        ],
    ];

    return '<script type="application/ld+json">' . wp_json_encode($schema, JSON_UNESCAPED_SLASHES | JSON_UNESCAPED_UNICODE) . '</script>';
}
