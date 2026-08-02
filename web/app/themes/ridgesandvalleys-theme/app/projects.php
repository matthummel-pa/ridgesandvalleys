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
                $full = in_array($type, ['textarea', 'lines'], true) || in_array($key, ['_rv_summary', '_rv_services', '_rv_tech'], true);
                echo '<p class="' . ($full ? 'rv-mb-full' : '') . '">';
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
 * The concept-site portfolio, seeded once as Project posts so it populates the
 * Projects admin list and the Work grid. Each links to its live HTML mockup in
 * /concept/. Safe + idempotent: skips any project whose slug already exists.
 */
function concept_project_seeds(): array
{
    return [
        [
            'folder' => 'gettysburg-hotel', 'title' => 'The Lantern & Laurel Inn', 'type' => 'Hotels',
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
            'folder' => 'hotel-cupola-field', 'title' => 'The Cupola & Field Hotel', 'type' => 'Hotels',
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
            'folder' => 'gettysburg-restaurant', 'title' => 'Field & Musket Tavern', 'type' => 'Restaurants',
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
            'folder' => 'restaurant-cannon-and-crumb', 'title' => 'Cannon & Crumb', 'type' => 'Restaurants',
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
            'folder' => 'gettysburg-retail', 'title' => 'Diamond & Ridge Mercantile', 'type' => 'Retail',
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
            'folder' => 'retail-ridgeline-outfitters', 'title' => 'Ridgeline Outfitters', 'type' => 'Retail',
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
            'folder' => 'tour-hallowed-ground-tours', 'title' => 'Hallowed Ground Battlefield Tours', 'type' => 'Tours',
            'eyebrow' => 'Concept · Guided tours', 'industry' => 'Tourism · Guided tours',
            'summary' => 'A battlefield-tour concept with a five-step, book-and-pay ticketing flow.',
            'services' => 'Web design, Ticketing, Payments UX',
            'challenge' => "Tour companies lose bookings to 'call to reserve.' Visitors plan at night, on their phones, and want to pay now — not leave a voicemail and hope someone calls back.",
            'approach' => "A five-step booking-and-payment flow: pick a tour, pick a time, choose tickets with a live total, and check out — with a clean demo payment UX and an instant confirmation.",
            'result' => "A concept that captures the booking at the moment of intent, computes the total live, and confirms on the spot — no phone tag, no sales lost after hours.",
            'metrics' => [['Book + pay', 'online'], ['Live', 'ticket totals'], ['Instant', 'confirmation']],
            'deliverables' => "Tour catalog\n5-step booking flow\nTicket + payment UX\nGuide bios + FAQ\nMobile-first, accessible build",
            'tech' => 'WordPress, Sage, Stripe-ready',
        ],
        [
            'folder' => 'tour-first-shot-food-tours', 'title' => 'First Shot Food & History Tours', 'type' => 'Tours',
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
            'folder' => 'realtor-ridgeline-realty', 'title' => 'Ridgeline Realty', 'type' => 'Real Estate',
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
            'folder' => 'realtor-keystone-homes-and-land', 'title' => 'Keystone Homes & Land', 'type' => 'Real Estate',
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
        update_post_meta($post_id, '_rv_services', $c['services']);
        update_post_meta($post_id, '_rv_url', get_theme_file_uri('concept/' . $c['folder'] . '/index.html'));
        update_post_meta($post_id, '_rv_preview', get_theme_file_uri('concept/' . $c['folder'] . '/preview.jpg'));
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
    }
}

/**
 * Run the concept seed once per version. Bump the version to refresh content
 * or add newly-created concepts.
 */
add_action('admin_init', function () {
    if (get_option('rv_concepts_seed_v') === '3') {
        return;
    }
    if (! current_user_can('edit_posts')) {
        return;
    }
    seed_concept_projects();
    update_option('rv_concepts_seed_v', '3');
});

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
