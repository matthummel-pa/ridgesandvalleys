<?php

namespace App;

/**
 * Dedicated block patterns for the "Affiliate Marketing" Pressroot AI site
 * type (see prt_site_types() in app/ai-assistant.php) — a review-and-
 * recommend site that lives or dies on trust: clear top-pick cards, honest
 * pros/cons, and a plain-language disclosure page.
 *
 * Built entirely in the Repofolio design language (docs/BRAND.md in the
 * repofolio repo): spectrum-topped cards (.prt-spec-card), gradient pill
 * CTAs, amber ★ ratings, lime "verified" accents, mono eyebrows.
 *
 * Three pages, two variants each, six patterns total:
 *   - prt-site/affiliate-home-a / -b        (Home: promise + top picks strip)
 *   - prt-site/affiliate-reviews-a / -b     (Top Picks: ranked review cards)
 *   - prt-site/affiliate-disclosure-a / -b  (Disclosure: how links pay us)
 *
 * Category 'prt-site-types' is registered centrally in app/ai-assistant.php.
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

    $stars = function (int $n = 5): string {
        return '<span style="color:#FFC53D; letter-spacing:2px;">' . str_repeat('★', $n) . str_repeat('<span style="opacity:.25">★</span>', 5 - $n) . '</span>';
    };

    $pick = function (string $rank, string $name, string $tag, string $desc, string $pros, string $cons, int $rating) use ($stars, $GRAD): string {
        return '<div class="prt-spec-card" style="background:#fff; border:1.5px solid #ECE6FB; border-radius:18px; padding:28px; display:flex; flex-direction:column; gap:10px;">'
            . '<div style="display:flex; align-items:center; gap:10px;">'
            . '<span style="background:' . $GRAD . '; color:#fff; font-family:var(--font-display); font-weight:800; font-size:13px; border-radius:999px; padding:5px 14px;">' . esc_html($rank) . '</span>'
            . '<span style="font-family:var(--font-mono); font-size:12px; color:#5A5676; text-transform:uppercase; letter-spacing:.08em;">' . esc_html($tag) . '</span>'
            . '</div>'
            . '<h3 style="font-family:var(--font-display); font-weight:800; font-size:22px; margin:2px 0 0; color:#17151F;">' . esc_html($name) . '</h3>'
            . '<div style="font-size:15px;">' . $stars($rating) . ' <span style="font-family:var(--font-mono); font-size:12px; color:#5A5676;">' . esc_html($rating . '.0 / 5') . '</span></div>'
            . '<p style="font-size:14.5px; color:#4A4660; line-height:1.55; margin:0;">' . esc_html($desc) . '</p>'
            . '<div style="font-size:13.5px; line-height:1.6;">'
            . '<div style="color:#178a5f;"><strong style="color:#37E29A;">✓</strong> ' . esc_html($pros) . '</div>'
            . '<div style="color:#b0325f;"><strong style="color:#FF4D9D;">✕</strong> ' . esc_html($cons) . '</div>'
            . '</div>'
            . '<div style="margin-top:auto; padding-top:10px;"><a href="#" class="prt-lift" style="text-decoration:none; display:inline-block; background:' . $GRAD . '; color:#fff; padding:12px 24px; border-radius:999px; font-weight:700; font-size:14px; font-family:var(--font-display);">Check price →</a>'
            . '<div style="font-family:var(--font-mono); font-size:11px; color:#7C75A8; margin-top:8px;">affiliate link — see disclosure</div></div>'
            . '</div>';
    };

    $patterns = [];

    /* ════════ HOME — Variant A: light promise hero + 3-pick strip ════════ */
    $heroA = '<section style="text-align:center; padding:30px 0 10px;">'
        . '<div style="font-family:var(--font-mono); letter-spacing:2px; text-transform:uppercase; font-size:13px; color:#6C4CF1; margin-bottom:16px;">Independent · Reader-supported</div>'
        . '<h1 style="font-family:var(--font-display); font-weight:800; font-size:clamp(42px,6vw,72px); line-height:1.02; letter-spacing:-.03em; margin:0 0 18px; color:#17151F;">We test it.<br><span style="background:' . $GRAD . '; -webkit-background-clip:text; background-clip:text; -webkit-text-fill-color:transparent; color:transparent;">You buy right.</span></h1>'
        . '<p style="font-size:19px; color:#4A4660; max-width:36em; margin:0 auto 28px;">Hands-on reviews and honest recommendations. When you buy through our links we may earn a commission — it never changes what we recommend.</p>'
        . '<div style="display:flex; gap:14px; justify-content:center; flex-wrap:wrap;">' . $btn('See our top picks', '/top-picks/') . $btn('How we make money', '/disclosure/', true) . '</div>'
        . '</section>';
    $picksA = '<div style="display:grid; grid-template-columns:repeat(3,1fr); gap:20px; margin-top:34px;">'
        . $pick('#1 PICK', 'Aurora Pro', 'Best overall', 'The one we reach for every day — fast, dependable, and priced right for what you get.', 'Best-in-class battery and support', 'Only three colorways', 5)
        . $pick('#2', 'Nimbus Air', 'Best budget', 'Eighty percent of the flagship experience at half the price. The value pick.', 'Unbeatable price-to-performance', 'Plastic build feels light', 4)
        . $pick('#3', 'Vertex Ultra', 'Best premium', 'Overkill in the best way — for people who want the absolute ceiling.', 'Class-leading everything', 'You will feel the price', 4)
        . '</div>';
    $patterns['prt-site/affiliate-home-a'] = [
        'title'   => __('Affiliate Home — A (light hero + top picks)', 'pressroot'),
        'content' => $wrap($html($heroA), '56px', '0px') . $wrap($html($picksA), '0px', '64px'),
    ];

    /* ════════ HOME — Variant B: dark docs-style hero + trust bar ════════ */
    $heroB = '<section style="position:relative; overflow:hidden; border-radius:28px; color:#fff; padding:64px 44px; background:radial-gradient(900px 400px at 80% -10%, rgba(108,76,241,.35), transparent 60%), radial-gradient(700px 400px at 10% 10%, rgba(255,77,157,.22), transparent 55%), linear-gradient(180deg,#201B3A,#15122a);">'
        . '<div style="font-family:var(--font-mono); letter-spacing:2px; text-transform:uppercase; font-size:13px; color:#b9a7ff;">Tested by humans, not spreadsheets</div>'
        . '<h1 style="font-family:var(--font-display); font-weight:800; font-size:clamp(40px,5.5vw,64px); line-height:1.03; letter-spacing:-.03em; margin:12px 0 14px; background:linear-gradient(90deg,#C9B8FF,#FF9DC4,#FFC08A); -webkit-background-clip:text; background-clip:text; -webkit-text-fill-color:transparent; color:transparent;">Gear worth your money.</h1>'
        . '<p style="font-size:19px; color:#e2ddf5; max-width:34em; margin:0 0 26px;">Every recommendation is bought, benchmarked, and lived with before it earns a spot. Affiliate links keep the lights on — rankings are never for sale.</p>'
        . '<div style="display:flex; gap:14px; flex-wrap:wrap;">'
        . '<a href="/top-picks/" class="prt-lift" style="text-decoration:none; display:inline-block; background:' . $GRAD . '; color:#fff; padding:15px 28px; border-radius:999px; font-weight:700; font-family:var(--font-display);">Browse top picks</a>'
        . '<a href="/disclosure/" class="prt-lift" style="text-decoration:none; display:inline-block; background:rgba(255,255,255,.08); border:1px solid rgba(255,255,255,.25); color:#fff; padding:15px 28px; border-radius:999px; font-weight:700; font-family:var(--font-display);">Our disclosure</a>'
        . '</div></section>';
    $trustB = '<div style="display:grid; grid-template-columns:repeat(4,1fr); gap:18px; margin-top:26px;">';
    foreach ([
        ['#6C4CF1', '120+', 'products tested this year'],
        ['#FF4D9D', '0', 'sponsored rankings — ever'],
        ['#37E29A', '30 days', 'minimum hands-on time'],
        ['#FFC53D', '4.8★', 'average reader rating'],
    ] as $t) {
        $trustB .= '<div class="prt-spec-card" style="background:#fff; border:1.5px solid #ECE6FB; border-radius:18px; padding:24px; text-align:center;">'
            . '<div style="font-family:var(--font-display); font-weight:900; font-size:34px; color:' . $t[0] . ';">' . esc_html($t[1]) . '</div>'
            . '<div style="font-size:13.5px; color:#5A5676; margin-top:4px;">' . esc_html($t[2]) . '</div></div>';
    }
    $trustB .= '</div>';
    $patterns['prt-site/affiliate-home-b'] = [
        'title'   => __('Affiliate Home — B (dark hero + trust stats)', 'pressroot'),
        'content' => $wrap($html($heroB), '40px', '0px') . $wrap($html($trustB), '0px', '64px'),
    ];

    /* ════════ TOP PICKS — Variant A: ranked grid ════════ */
    $reviewsA = '<section style="text-align:center; padding:20px 0 6px;">'
        . '<div style="font-family:var(--font-mono); letter-spacing:2px; text-transform:uppercase; font-size:13px; color:#6C4CF1; margin-bottom:14px;">Updated monthly</div>'
        . '<h1 style="font-family:var(--font-display); font-weight:800; font-size:clamp(38px,5vw,58px); letter-spacing:-.03em; margin:0 0 14px; color:#17151F;">Top picks, ranked.</h1>'
        . '<p style="font-size:18px; color:#4A4660; max-width:38em; margin:0 auto;">Our current recommendations in every category we cover. Rankings change when the products do.</p>'
        . '</section>';
    $gridA = '<div style="display:grid; grid-template-columns:repeat(3,1fr); gap:20px; margin-top:30px;">'
        . $pick('#1 PICK', 'Aurora Pro', 'Best overall', 'Still the benchmark. Nothing else balances speed, build, and price this well.', 'Flawless daily driver', 'Waitlist during launches', 5)
        . $pick('#2', 'Nimbus Air', 'Best budget', 'The smart-money choice — most people should start (and stop) here.', 'Half the price of the flagship', 'Skips the pro features', 4)
        . $pick('#3', 'Vertex Ultra', 'Best premium', 'When budget is no object and the spec sheet must win.', 'The ceiling, period', 'Diminishing returns for most', 4)
        . $pick('#4', 'Solstice Mini', 'Best compact', 'Everything that matters in a size that disappears into a bag.', 'Featherweight and silent', 'Small battery to match', 4)
        . $pick('#5', 'Meridian S', 'Best for beginners', 'The gentlest learning curve of anything we tested.', 'Set up in five minutes', 'You may outgrow it', 4)
        . $pick('#6', 'Cascade Duo', 'Best two-in-one', 'One purchase, two jobs done properly — rare in this category.', 'Genuinely versatile', 'Jack of both, master of one', 3)
        . '</div>';
    $patterns['prt-site/affiliate-reviews-a'] = [
        'title'   => __('Affiliate Top Picks — A (ranked grid)', 'pressroot'),
        'content' => $wrap($html($reviewsA), '56px', '0px') . $wrap($html($gridA), '0px', '64px'),
    ];

    /* ════════ TOP PICKS — Variant B: editorial list with methodology ════════ */
    $methodB = '<div class="prt-spec-card" style="background:#17151F; color:#fff; border-radius:28px; padding:44px 40px;">'
        . '<div style="font-family:var(--font-mono); font-size:13px; color:#37E29A; letter-spacing:.1em; margin-bottom:12px;">HOW WE TEST</div>'
        . '<h2 style="font-family:var(--font-display); font-weight:800; font-size:32px; letter-spacing:-.02em; margin:0 0 20px;">Bought retail. Tested for weeks. Ranked on merit.</h2>'
        . '<div style="display:grid; grid-template-columns:repeat(3,1fr); gap:24px;">'
        . '<div><div style="font-family:var(--font-display); font-weight:800; font-size:18px; color:#b9a7ff; margin-bottom:6px;">1 · We buy it</div><p style="font-size:14px; color:#CFCBE6; margin:0;">Retail units, our own money — no cherry-picked review samples.</p></div>'
        . '<div><div style="font-family:var(--font-display); font-weight:800; font-size:18px; color:#FF9DC4; margin-bottom:6px;">2 · We live with it</div><p style="font-size:14px; color:#CFCBE6; margin:0;">A month minimum of real use before a word gets written.</p></div>'
        . '<div><div style="font-family:var(--font-display); font-weight:800; font-size:18px; color:#FFC08A; margin-bottom:6px;">3 · We re-test</div><p style="font-size:14px; color:#CFCBE6; margin:0;">Rankings are revisited every month as firmware and prices move.</p></div>'
        . '</div></div>';
    $listB = '<div style="display:grid; gap:16px; margin-top:26px;">';
    foreach ([
        ['Best overall', 'Aurora Pro', 'The complete package — our editors\' unanimous pick for the second year running.', 5],
        ['Best budget', 'Nimbus Air', 'The value king. Most readers tell us this is the one they actually bought.', 4],
        ['Best premium', 'Vertex Ultra', 'Spares nothing, costs plenty. Glorious if the budget stretches.', 4],
    ] as $r) {
        $listB .= '<div class="prt-spec-card" style="background:#fff; border:1.5px solid #ECE6FB; border-radius:18px; padding:24px 28px; display:flex; align-items:center; gap:22px; flex-wrap:wrap;">'
            . '<div style="min-width:130px;"><span style="font-family:var(--font-mono); font-size:11px; text-transform:uppercase; letter-spacing:.08em; background:#EEE8FE; color:#4a2fb0; border-radius:999px; padding:4px 10px;">' . esc_html($r[0]) . '</span></div>'
            . '<div style="flex:1; min-width:220px;"><h3 style="font-family:var(--font-display); font-weight:800; font-size:20px; margin:0 0 4px; color:#17151F;">' . esc_html($r[1]) . '</h3>'
            . '<p style="font-size:14px; color:#4A4660; margin:0;">' . esc_html($r[2]) . '</p></div>'
            . '<div style="text-align:right;"><div style="font-size:14px;">' . $stars($r[3]) . '</div>'
            . '<a href="#" class="prt-lift" style="text-decoration:none; display:inline-block; margin-top:8px; background:' . $GRAD . '; color:#fff; padding:10px 20px; border-radius:999px; font-weight:700; font-size:13px; font-family:var(--font-display);">Check price →</a></div>'
            . '</div>';
    }
    $listB .= '</div>';
    $patterns['prt-site/affiliate-reviews-b'] = [
        'title'   => __('Affiliate Top Picks — B (methodology + editorial list)', 'pressroot'),
        'content' => $wrap($html($methodB), '48px', '0px') . $wrap($html($listB), '0px', '64px'),
    ];

    /* ════════ DISCLOSURE — Variant A: plain-language card ════════ */
    $discA = '<section style="max-width:760px; margin:0 auto;">'
        . '<div style="font-family:var(--font-mono); letter-spacing:2px; text-transform:uppercase; font-size:13px; color:#6C4CF1; margin-bottom:14px;">Affiliate disclosure</div>'
        . '<h1 style="font-family:var(--font-display); font-weight:800; font-size:clamp(36px,5vw,54px); letter-spacing:-.03em; margin:0 0 18px; color:#17151F;">How this site makes money.</h1>'
        . '<p style="font-size:18px; color:#4A4660; line-height:1.65;">Some links on this site are affiliate links. If you click one and buy something, the retailer pays us a small commission at <strong>no extra cost to you</strong>. That commission funds the products we buy and the time we spend testing them.</p>'
        . '<div class="prt-spec-card" style="background:#fff; border:1.5px solid #ECE6FB; border-radius:18px; padding:26px 30px; margin-top:22px;">'
        . '<h2 style="font-family:var(--font-display); font-weight:800; font-size:20px; margin:0 0 12px; color:#17151F;">Our promises</h2>'
        . '<ul style="margin:0; padding-left:0; list-style:none; font-size:15px; color:#4A4660; line-height:2;">'
        . '<li><strong style="color:#37E29A;">✓</strong> Rankings are never sold, traded, or influenced by commissions.</li>'
        . '<li><strong style="color:#37E29A;">✓</strong> We disclose affiliate links wherever they appear.</li>'
        . '<li><strong style="color:#37E29A;">✓</strong> Negative reviews stay published, affiliate deal or not.</li>'
        . '<li><strong style="color:#37E29A;">✓</strong> We buy review units at retail whenever possible.</li>'
        . '</ul></div></section>';
    $patterns['prt-site/affiliate-disclosure-a'] = [
        'title'   => __('Affiliate Disclosure — A (plain-language card)', 'pressroot'),
        'content' => $wrap($html($discA), '56px', '64px'),
    ];

    /* ════════ DISCLOSURE — Variant B: FAQ style ════════ */
    $discB = '<section style="max-width:760px; margin:0 auto;">'
        . '<h1 style="font-family:var(--font-display); font-weight:800; font-size:clamp(36px,5vw,54px); letter-spacing:-.03em; margin:0 0 8px; color:#17151F;">The money questions, answered.</h1>'
        . '<p style="font-size:17px; color:#5A5676; margin:0 0 26px;">Everything readers ask about affiliate links, in one place.</p>';
    foreach ([
        ['Do affiliate links cost me anything?', 'No. Prices are identical whether you use our link or go direct — the commission comes out of the retailer\'s side.'],
        ['Do commissions change your rankings?', 'Never. Writers don\'t know which products carry affiliate deals when they test, and several of our current #1 picks earn us nothing.'],
        ['Why should I use your links?', 'They\'re the tip jar of the review world: same price for you, and they fund the next round of retail-bought test units.'],
        ['Do brands send you free products?', 'Occasionally — always labeled, never guaranteed coverage, and loaners go back when testing ends.'],
    ] as $q) {
        $discB .= '<div class="prt-spec-card" style="background:#fff; border:1.5px solid #ECE6FB; border-radius:18px; padding:22px 26px; margin-bottom:14px;">'
            . '<h2 style="font-family:var(--font-display); font-weight:700; font-size:18px; margin:0 0 8px; color:#17151F;">' . esc_html($q[0]) . '</h2>'
            . '<p style="font-size:15px; color:#4A4660; line-height:1.6; margin:0;">' . esc_html($q[1]) . '</p></div>';
    }
    $discB .= '</section>';
    $patterns['prt-site/affiliate-disclosure-b'] = [
        'title'   => __('Affiliate Disclosure — B (FAQ style)', 'pressroot'),
        'content' => $wrap($html($discB), '56px', '64px'),
    ];

    foreach ($patterns as $slug => $def) {
        register_block_pattern($slug, [
            'title'      => $def['title'],
            'content'    => $def['content'],
            'categories' => ['prt-site-types'],
        ]);
    }
}, 11);
