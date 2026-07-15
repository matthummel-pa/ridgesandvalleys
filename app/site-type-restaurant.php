<?php

namespace App;

/**
 * Dedicated block patterns for the "Restaurant / Café" Pressroot AI site
 * type (see prt_site_types() in app/ai-assistant.php) — a food-first site:
 * appetite-appeal hero, a menu people can actually read, and a low-friction
 * reservations page. Repofolio design language throughout (spectrum-topped
 * cards, gradient pill CTAs, mono eyebrows), paired with the Warm Sand kit.
 *
 * Three pages, two variants each, six patterns total:
 *   - prt-site/restaurant-home-a / -b
 *   - prt-site/restaurant-menu-a / -b
 *   - prt-site/restaurant-reservations-a / -b
 */

add_action('init', function () {

    $GRAD = 'linear-gradient(135deg,#6C4CF1 0%,#FF4D9D 55%,#FF7A3D 100%)';

    $wrap = function (string $inner, string $pt = '64px', string $pb = '24px'): string {
        $style = '{"spacing":{"padding":{"top":"' . $pt . '","bottom":"' . $pb . '"}}}';
        return '<!-- wp:group {"className":"prt-wrap","style":' . $style . ',"layout":{"type":"constrained","contentSize":"1240px"}} -->'
            . '<div class="wp-block-group prt-wrap" style="padding-top:' . $pt . ';padding-bottom:' . $pb . '">'
            . $inner . '</div><!-- /wp:group -->' . "\n\n";
    };

    $html = function (string $markup): string {
        return "<!-- wp:html -->\n" . $markup . "\n<!-- /wp:html -->";
    };

    $btn = function (string $text, string $url, bool $ghost = false) use ($GRAD): string {
        $base = 'text-decoration:none; padding:15px 28px; border-radius:999px; font-weight:700; font-size:16px; font-family:var(--font-display); display:inline-block;';
        return $ghost
            ? '<a href="' . esc_url($url) . '" class="prt-lift" style="' . $base . ' background:#fff; border:1.5px solid #ECE6FB; color:#6C4CF1;">' . esc_html($text) . '</a>'
            : '<a href="' . esc_url($url) . '" class="prt-lift" style="' . $base . ' background:' . $GRAD . '; color:#fff;">' . esc_html($text) . '</a>';
    };

    $dish = function (string $name, string $desc, string $price, string $tag = ''): string {
        return '<div class="prt-spec-card" style="background:#fff; border:1.5px solid #ECE6FB; border-radius:18px; padding:22px 26px;">'
            . '<div style="display:flex; align-items:baseline; justify-content:space-between; gap:14px;">'
            . '<h3 style="font-family:var(--font-display); font-weight:800; font-size:19px; margin:0; color:#17151F;">' . esc_html($name)
            . ($tag !== '' ? ' <span style="font-family:var(--font-mono); font-size:10px; text-transform:uppercase; letter-spacing:.08em; background:#EEE8FE; color:#4a2fb0; border-radius:999px; padding:3px 9px; vertical-align:middle;">' . esc_html($tag) . '</span>' : '')
            . '</h3>'
            . '<span style="font-family:var(--font-mono); font-weight:600; font-size:16px; color:#6C4CF1; white-space:nowrap;">' . esc_html($price) . '</span>'
            . '</div>'
            . '<p style="font-size:14px; color:#5A5676; line-height:1.55; margin:8px 0 0;">' . esc_html($desc) . '</p>'
            . '</div>';
    };

    $patterns = [];

    /* ════════ HOME — Variant A: dark evening hero + tonight's plates ════════ */
    $heroA = '<section style="position:relative; overflow:hidden; border-radius:28px; color:#fff; padding:70px 44px; text-align:center; background:radial-gradient(900px 400px at 80% -10%, rgba(255,122,61,.30), transparent 60%), radial-gradient(700px 400px at 10% 10%, rgba(255,77,157,.22), transparent 55%), linear-gradient(180deg,#201B3A,#15122a);">'
        . '<div style="font-family:var(--font-mono); letter-spacing:2px; text-transform:uppercase; font-size:13px; color:#FFC08A;">Seasonal · Local · Open Tue–Sun</div>'
        . '<h1 style="font-family:var(--font-display); font-weight:800; font-size:clamp(42px,6vw,72px); line-height:1.02; letter-spacing:-.03em; margin:14px 0 14px; background:linear-gradient(90deg,#FFC08A,#FF9DC4,#C9B8FF); -webkit-background-clip:text; background-clip:text; -webkit-text-fill-color:transparent; color:transparent;">Dinner, done properly.</h1>'
        . '<p style="font-size:19px; color:#e2ddf5; max-width:32em; margin:0 auto 28px;">A small menu that changes with the market, a wood fire that never goes out, and a table waiting for you.</p>'
        . '<div style="display:flex; gap:14px; justify-content:center; flex-wrap:wrap;">'
        . '<a href="/reservations/" class="prt-lift" style="text-decoration:none; display:inline-block; background:' . $GRAD . '; color:#fff; padding:16px 30px; border-radius:999px; font-weight:700; font-family:var(--font-display);">Book a table</a>'
        . '<a href="/menu/" class="prt-lift" style="text-decoration:none; display:inline-block; background:rgba(255,255,255,.08); border:1px solid rgba(255,255,255,.25); color:#fff; padding:16px 30px; border-radius:999px; font-weight:700; font-family:var(--font-display);">See the menu</a>'
        . '</div></section>';
    $platesA = '<div style="display:grid; grid-template-columns:repeat(3,1fr); gap:20px; margin-top:30px;">'
        . $dish('Ember-roasted chicken', 'Half bird, charred lemon, pan jus over sourdough.', '$28', 'signature')
        . $dish('Market crudo', 'Whatever swam in this morning, olive oil, citrus, salt.', '$19', 'today only')
        . $dish('Smoked beet tartare', 'Beets from two farms over, rye crisps, horseradish cream.', '$16', 'vegan')
        . '</div>';
    $patterns['prt-site/restaurant-home-a'] = [
        'title'   => __('Restaurant Home — A (evening hero + tonight\'s plates)', 'pressroot'),
        'content' => $wrap($html($heroA), '40px', '0px') . $wrap($html($platesA), '0px', '64px'),
    ];

    /* ════════ HOME — Variant B: light hero + hours/location cards ════════ */
    $heroB = '<section style="text-align:center; padding:30px 0 10px;">'
        . '<div style="font-family:var(--font-mono); letter-spacing:2px; text-transform:uppercase; font-size:13px; color:#6C4CF1; margin-bottom:16px;">Neighborhood kitchen &amp; bar</div>'
        . '<h1 style="font-family:var(--font-display); font-weight:800; font-size:clamp(42px,6vw,72px); line-height:1.02; letter-spacing:-.03em; margin:0 0 18px; color:#17151F;">Come hungry.<br><span style="background:' . $GRAD . '; -webkit-background-clip:text; background-clip:text; -webkit-text-fill-color:transparent; color:transparent;">Leave happy.</span></h1>'
        . '<p style="font-size:19px; color:#4A4660; max-width:34em; margin:0 auto 28px;">Honest cooking, fair prices, and a room that gets loud in the best way. Walk-ins welcome at the bar.</p>'
        . '<div style="display:flex; gap:14px; justify-content:center; flex-wrap:wrap;">' . $btn('Reserve a table', '/reservations/') . $btn('Tonight\'s menu', '/menu/', true) . '</div>'
        . '</section>';
    $infoB = '<div style="display:grid; grid-template-columns:repeat(3,1fr); gap:18px; margin-top:34px;">';
    foreach ([
        ['🕔', 'Hours', 'Tue–Thu 5–10 · Fri–Sat 5–11 · Sun brunch 10–2'],
        ['📍', 'Find us', '214 Chestnut St — two blocks from the square, parking in the rear'],
        ['🍷', 'Happy hour', 'Tue–Fri 5–6:30 · half-off snacks and house pours at the bar'],
    ] as $c) {
        $infoB .= '<div class="prt-spec-card" style="background:#fff; border:1.5px solid #ECE6FB; border-radius:18px; padding:26px; text-align:center;">'
            . '<div style="font-size:34px;">' . $c[0] . '</div>'
            . '<h3 style="font-family:var(--font-display); font-weight:800; font-size:18px; margin:10px 0 6px; color:#17151F;">' . esc_html($c[1]) . '</h3>'
            . '<p style="font-size:14px; color:#5A5676; margin:0; line-height:1.55;">' . esc_html($c[2]) . '</p></div>';
    }
    $infoB .= '</div>';
    $patterns['prt-site/restaurant-home-b'] = [
        'title'   => __('Restaurant Home — B (light hero + hours/location)', 'pressroot'),
        'content' => $wrap($html($heroB), '56px', '0px') . $wrap($html($infoB), '0px', '64px'),
    ];

    /* ════════ MENU — Variant A: two-column card menu ════════ */
    $menuHeadA = '<section style="text-align:center; padding:20px 0 6px;">'
        . '<div style="font-family:var(--font-mono); letter-spacing:2px; text-transform:uppercase; font-size:13px; color:#6C4CF1; margin-bottom:14px;">Changes with the seasons</div>'
        . '<h1 style="font-family:var(--font-display); font-weight:800; font-size:clamp(38px,5vw,58px); letter-spacing:-.03em; margin:0 0 12px; color:#17151F;">The menu.</h1>'
        . '<p style="font-size:17px; color:#5A5676; max-width:34em; margin:0 auto;">Printed nightly. Ask about allergies — the kitchen is happy to adapt almost anything.</p></section>';
    $menuA = '<div style="display:grid; grid-template-columns:repeat(2,1fr); gap:18px; margin-top:28px;">'
        . $dish('Sourdough & cultured butter', 'Baked at 3pm, gone by 9.', '$7')
        . $dish('Crispy potatoes', 'Garlic, rosemary, aggressive amounts of both.', '$11')
        . $dish('Ember-roasted chicken', 'Half bird, charred lemon, pan jus over sourdough.', '$28', 'signature')
        . $dish('Flat-iron steak frites', 'Grass-fed, café butter, a pile of shoestrings.', '$34')
        . $dish('Squash agnolotti', 'Brown butter, sage, aged parm. Vegetarian and proud.', '$24', 'vegetarian')
        . $dish('Burnt basque cheesecake', 'The crack is the point.', '$12', 'dessert')
        . '</div>';
    $patterns['prt-site/restaurant-menu-a'] = [
        'title'   => __('Restaurant Menu — A (two-column cards)', 'pressroot'),
        'content' => $wrap($html($menuHeadA), '56px', '0px') . $wrap($html($menuA), '0px', '64px'),
    ];

    /* ════════ MENU — Variant B: dark tasting-menu board ════════ */
    $menuB = '<div class="prt-spec-card" style="background:#17151F; color:#fff; border-radius:28px; padding:50px 46px;">'
        . '<div style="text-align:center; margin-bottom:34px;">'
        . '<div style="font-family:var(--font-mono); font-size:13px; color:#37E29A; letter-spacing:.1em;">FIVE COURSES · $75 · WINE PAIRING +$45</div>'
        . '<h1 style="font-family:var(--font-display); font-weight:800; font-size:clamp(34px,4.5vw,52px); letter-spacing:-.02em; margin:10px 0 0;">Tonight\'s tasting.</h1>'
        . '</div><div style="max-width:640px; margin:0 auto; display:grid; gap:22px;">';
    foreach ([
        ['ONE', 'Market crudo', 'citrus · olive oil · flake salt'],
        ['TWO', 'Smoked beet tartare', 'rye · horseradish · dill'],
        ['THREE', 'Squash agnolotti', 'brown butter · sage · parm'],
        ['FOUR', 'Ember-roasted chicken', 'charred lemon · jus · sourdough'],
        ['FIVE', 'Burnt basque cheesecake', 'that\'s it — that\'s the course'],
    ] as $c) {
        $menuB .= '<div style="display:flex; gap:18px; align-items:baseline; border-bottom:1px solid rgba(255,255,255,.12); padding-bottom:16px;">'
            . '<span style="font-family:var(--font-mono); font-size:11px; color:#b9a7ff; min-width:52px; letter-spacing:.1em;">' . esc_html($c[0]) . '</span>'
            . '<div><div style="font-family:var(--font-display); font-weight:700; font-size:19px;">' . esc_html($c[1]) . '</div>'
            . '<div style="font-family:var(--font-mono); font-size:12px; color:#CFCBE6; margin-top:2px;">' . esc_html($c[2]) . '</div></div></div>';
    }
    $menuB .= '</div><div style="text-align:center; margin-top:30px;">'
        . '<a href="/reservations/" class="prt-lift" style="text-decoration:none; display:inline-block; background:' . $GRAD . '; color:#fff; padding:15px 30px; border-radius:999px; font-weight:700; font-family:var(--font-display);">Book the tasting</a></div></div>';
    $patterns['prt-site/restaurant-menu-b'] = [
        'title'   => __('Restaurant Menu — B (dark tasting board)', 'pressroot'),
        'content' => $wrap($html($menuB), '48px', '64px'),
    ];

    /* ════════ RESERVATIONS — Variant A: booking card + policies ════════ */
    $resA = '<section style="max-width:720px; margin:0 auto; text-align:center;">'
        . '<h1 style="font-family:var(--font-display); font-weight:800; font-size:clamp(38px,5vw,56px); letter-spacing:-.03em; margin:0 0 12px; color:#17151F;">Book a table.</h1>'
        . '<p style="font-size:17px; color:#5A5676; margin:0 0 26px;">Parties of 7+ and buyouts: email us and we\'ll take care of you.</p>'
        . '<div class="prt-spec-card" style="background:#fff; border:1.5px solid #ECE6FB; border-radius:18px; padding:32px; text-align:left;">'
        . '<p style="font-size:15px; color:#4A4660; margin:0 0 18px;">Call, or use the buttons below — most nights we can seat you within the hour.</p>'
        . '<div style="display:flex; gap:12px; flex-wrap:wrap;">' . $btn('Call (555) 214-0214', 'tel:5552140214') . $btn('Email the host stand', 'mailto:tables@example.com', true) . '</div>'
        . '<p style="font-family:var(--font-mono); font-size:12px; color:#7C75A8; margin:18px 0 0;">15-minute grace period · card hold for parties of 5+ · cancel free up to 3 hours out</p>'
        . '</div></section>';
    $patterns['prt-site/restaurant-reservations-a'] = [
        'title'   => __('Restaurant Reservations — A (booking card)', 'pressroot'),
        'content' => $wrap($html($resA), '56px', '64px'),
    ];

    /* ════════ RESERVATIONS — Variant B: split hours + private dining ════════ */
    $resB = '<div style="display:grid; grid-template-columns:1.1fr .9fr; gap:20px;">'
        . '<div class="prt-spec-card" style="background:#fff; border:1.5px solid #ECE6FB; border-radius:18px; padding:32px;">'
        . '<div style="font-family:var(--font-mono); letter-spacing:2px; text-transform:uppercase; font-size:12px; color:#6C4CF1; margin-bottom:10px;">Reservations</div>'
        . '<h1 style="font-family:var(--font-display); font-weight:800; font-size:clamp(30px,4vw,44px); letter-spacing:-.02em; margin:0 0 14px; color:#17151F;">Save your seat.</h1>'
        . '<p style="font-size:15px; color:#4A4660; line-height:1.6; margin:0 0 20px;">Dinner service Tue–Sun. The bar is always first-come, first-served — and honestly, it\'s the best seat in the house.</p>'
        . '<div style="display:flex; gap:12px; flex-wrap:wrap;">' . $btn('Book online', '#') . $btn('Call us', 'tel:5552140214', true) . '</div></div>'
        . '<div class="prt-spec-card" style="background:#17151F; color:#fff; border-radius:18px; padding:32px;">'
        . '<div style="font-family:var(--font-mono); font-size:12px; color:#37E29A; letter-spacing:.1em; margin-bottom:10px;">PRIVATE DINING</div>'
        . '<h2 style="font-family:var(--font-display); font-weight:800; font-size:26px; margin:0 0 12px;">The back room seats 24.</h2>'
        . '<p style="font-size:14.5px; color:#CFCBE6; line-height:1.6; margin:0 0 18px;">Set menus, its own fireplace, and a door that closes. Birthdays, rehearsals, quarterly "team dinners" that go long.</p>'
        . '<a href="mailto:events@example.com" class="prt-lift" style="text-decoration:none; display:inline-block; background:rgba(255,255,255,.1); border:1px solid rgba(255,255,255,.25); color:#fff; padding:12px 24px; border-radius:999px; font-weight:700; font-size:14px; font-family:var(--font-display);">Plan an event</a></div>'
        . '</div>';
    $patterns['prt-site/restaurant-reservations-b'] = [
        'title'   => __('Restaurant Reservations — B (split + private dining)', 'pressroot'),
        'content' => $wrap($html($resB), '56px', '64px'),
    ];

    foreach ($patterns as $slug => $def) {
        register_block_pattern($slug, [
            'title'      => $def['title'],
            'content'    => $def['content'],
            'categories' => ['prt-site-types'],
        ]);
    }
}, 11);
