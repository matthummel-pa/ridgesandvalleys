<?php

namespace App;

/**
 * Dedicated block patterns for the "Real Estate" Pressroot AI site type
 * (see prt_site_types() in app/ai-assistant.php) — a property site built on
 * confidence: listings people can scan, proof-of-results stats, and agent
 * cards that feel like people rather than headshots. Repofolio design
 * language throughout (spectrum-topped cards, gradient pill CTAs, mono
 * eyebrows), paired with the Editorial kit.
 *
 * Three pages, two variants each, six patterns total:
 *   - prt-site/realty-home-a / -b
 *   - prt-site/realty-listings-a / -b
 *   - prt-site/realty-agents-a / -b
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

    $listing = function (string $price, string $addr, string $meta, string $tag, string $tint): string {
        return '<div class="prt-spec-card prt-lift" style="background:#fff; border:1.5px solid #ECE6FB; border-radius:18px; overflow:hidden;">'
            . '<div style="aspect-ratio:16/10; background:repeating-linear-gradient(135deg,' . $tint . ' 0 16px, #F3EEFE 16px 32px); display:flex; align-items:flex-end; padding:14px;">'
            . '<span style="font-family:var(--font-mono); font-size:11px; text-transform:uppercase; letter-spacing:.08em; background:#17151F; color:#fff; border-radius:999px; padding:4px 12px;">' . esc_html($tag) . '</span></div>'
            . '<div style="padding:20px 24px 24px;">'
            . '<div style="font-family:var(--font-display); font-weight:900; font-size:24px; color:#6C4CF1;">' . esc_html($price) . '</div>'
            . '<h3 style="font-family:var(--font-display); font-weight:700; font-size:17px; margin:6px 0 4px; color:#17151F;">' . esc_html($addr) . '</h3>'
            . '<p style="font-family:var(--font-mono); font-size:12.5px; color:#5A5676; margin:0;">' . esc_html($meta) . '</p>'
            . '</div></div>';
    };

    $patterns = [];

    /* ════════ HOME — Variant A: light hero + featured listings ════════ */
    $heroA = '<section style="text-align:center; padding:30px 0 10px;">'
        . '<div style="font-family:var(--font-mono); letter-spacing:2px; text-transform:uppercase; font-size:13px; color:#6C4CF1; margin-bottom:16px;">Serving the metro area since 2009</div>'
        . '<h1 style="font-family:var(--font-display); font-weight:800; font-size:clamp(42px,6vw,72px); line-height:1.02; letter-spacing:-.03em; margin:0 0 18px; color:#17151F;">Find the door<br><span style="background:' . $GRAD . '; -webkit-background-clip:text; background-clip:text; -webkit-text-fill-color:transparent; color:transparent;">with your name on it.</span></h1>'
        . '<p style="font-size:19px; color:#4A4660; max-width:34em; margin:0 auto 28px;">Buying, selling, or just curious what your place is worth — straight answers from agents who live where you\'re looking.</p>'
        . '<div style="display:flex; gap:14px; justify-content:center; flex-wrap:wrap;">' . $btn('Browse listings', '/listings/') . $btn('Get a free valuation', '/agents/', true) . '</div>'
        . '</section>';
    $featA = '<div style="display:grid; grid-template-columns:repeat(3,1fr); gap:20px; margin-top:34px;">'
        . $listing('$489,000', '1128 Willow Bend Ct', '4 bd · 3 ba · 2,450 sqft · 0.3 acres', 'New listing', '#EEE8FE')
        . $listing('$339,900', '87 Sycamore Row #2B', '2 bd · 2 ba · 1,180 sqft · downtown', 'Open Sun 1–3', '#FFE9F3')
        . $listing('$725,000', '4 Ridgeline Overlook', '5 bd · 4 ba · 3,600 sqft · views', 'Featured', '#FFF0E6')
        . '</div>';
    $patterns['prt-site/realty-home-a'] = [
        'title'   => __('Realty Home — A (light hero + featured listings)', 'pressroot'),
        'content' => $wrap($html($heroA), '56px', '0px') . $wrap($html($featA), '0px', '64px'),
    ];

    /* ════════ HOME — Variant B: dark hero + results stats ════════ */
    $heroB = '<section style="position:relative; overflow:hidden; border-radius:28px; color:#fff; padding:64px 44px; background:radial-gradient(900px 400px at 80% -10%, rgba(108,76,241,.35), transparent 60%), radial-gradient(700px 400px at 10% 10%, rgba(34,207,238,.20), transparent 55%), linear-gradient(180deg,#201B3A,#15122a);">'
        . '<div style="font-family:var(--font-mono); letter-spacing:2px; text-transform:uppercase; font-size:13px; color:#b9a7ff;">Local experts · Licensed &amp; insured</div>'
        . '<h1 style="font-family:var(--font-display); font-weight:800; font-size:clamp(40px,5.5vw,64px); line-height:1.03; letter-spacing:-.03em; margin:12px 0 14px; background:linear-gradient(90deg,#C9B8FF,#FF9DC4,#FFC08A); -webkit-background-clip:text; background-clip:text; -webkit-text-fill-color:transparent; color:transparent;">Sold, not just listed.</h1>'
        . '<p style="font-size:19px; color:#e2ddf5; max-width:34em; margin:0 0 26px;">Our average listing goes under contract in 11 days — because pricing, prep, and photography are done right the first time.</p>'
        . '<div style="display:flex; gap:14px; flex-wrap:wrap;">'
        . '<a href="/agents/" class="prt-lift" style="text-decoration:none; display:inline-block; background:' . $GRAD . '; color:#fff; padding:15px 28px; border-radius:999px; font-weight:700; font-family:var(--font-display);">Talk to an agent</a>'
        . '<a href="/listings/" class="prt-lift" style="text-decoration:none; display:inline-block; background:rgba(255,255,255,.08); border:1px solid rgba(255,255,255,.25); color:#fff; padding:15px 28px; border-radius:999px; font-weight:700; font-family:var(--font-display);">See what\'s for sale</a>'
        . '</div></section>';
    $statsB = '<div style="display:grid; grid-template-columns:repeat(4,1fr); gap:18px; margin-top:26px;">';
    foreach ([
        ['#6C4CF1', '340+', 'homes sold'],
        ['#FF4D9D', '11 days', 'average time to contract'],
        ['#37E29A', '101%', 'average of asking price'],
        ['#FFC53D', '4.9★', 'from 200+ client reviews'],
    ] as $s) {
        $statsB .= '<div class="prt-spec-card" style="background:#fff; border:1.5px solid #ECE6FB; border-radius:18px; padding:24px; text-align:center;">'
            . '<div style="font-family:var(--font-display); font-weight:900; font-size:32px; color:' . $s[0] . ';">' . esc_html($s[1]) . '</div>'
            . '<div style="font-size:13.5px; color:#5A5676; margin-top:4px;">' . esc_html($s[2]) . '</div></div>';
    }
    $statsB .= '</div>';
    $patterns['prt-site/realty-home-b'] = [
        'title'   => __('Realty Home — B (dark hero + results stats)', 'pressroot'),
        'content' => $wrap($html($heroB), '40px', '0px') . $wrap($html($statsB), '0px', '64px'),
    ];

    /* ════════ LISTINGS — Variant A: filterable-feeling grid ════════ */
    $listHeadA = '<section style="text-align:center; padding:20px 0 6px;">'
        . '<h1 style="font-family:var(--font-display); font-weight:800; font-size:clamp(38px,5vw,58px); letter-spacing:-.03em; margin:0 0 12px; color:#17151F;">Current listings.</h1>'
        . '<p style="font-size:17px; color:#5A5676; max-width:36em; margin:0 auto 20px;">Updated the moment something hits the market — because the good ones don\'t wait.</p>'
        . '<div style="display:flex; gap:8px; justify-content:center; flex-wrap:wrap;">';
    foreach (['All', 'Under $400k', 'Single family', 'Condos', 'New this week'] as $i => $f) {
        $listHeadA .= $i === 0
            ? '<span style="font-family:var(--font-display); font-weight:700; font-size:13px; background:' . $GRAD . '; color:#fff; border-radius:999px; padding:8px 18px;">' . esc_html($f) . '</span>'
            : '<span style="font-family:var(--font-display); font-weight:600; font-size:13px; background:#fff; border:1.5px solid #ECE6FB; color:#6C4CF1; border-radius:999px; padding:8px 18px;">' . esc_html($f) . '</span>';
    }
    $listHeadA .= '</div></section>';
    $listGridA = '<div style="display:grid; grid-template-columns:repeat(3,1fr); gap:20px; margin-top:28px;">'
        . $listing('$489,000', '1128 Willow Bend Ct', '4 bd · 3 ba · 2,450 sqft', 'New listing', '#EEE8FE')
        . $listing('$339,900', '87 Sycamore Row #2B', '2 bd · 2 ba · 1,180 sqft', 'Open Sun 1–3', '#FFE9F3')
        . $listing('$725,000', '4 Ridgeline Overlook', '5 bd · 4 ba · 3,600 sqft', 'Featured', '#FFF0E6')
        . $listing('$265,000', '310 Maple St', '3 bd · 1 ba · 1,320 sqft · starter', 'Under $400k', '#E9FBF3')
        . $listing('$412,500', '22 Harborview Ln', '3 bd · 2.5 ba · 1,980 sqft', 'Price improved', '#E8F9FE')
        . $listing('$559,000', '9 Foxglove Meadow', '4 bd · 3 ba · 2,780 sqft · new build', 'Just listed', '#FFF7E2')
        . '</div>';
    $patterns['prt-site/realty-listings-a'] = [
        'title'   => __('Realty Listings — A (filter pills + grid)', 'pressroot'),
        'content' => $wrap($html($listHeadA), '56px', '0px') . $wrap($html($listGridA), '0px', '64px'),
    ];

    /* ════════ LISTINGS — Variant B: featured spotlight + list ════════ */
    $spotB = '<div class="prt-spec-card" style="background:#17151F; color:#fff; border-radius:28px; padding:46px 42px; display:grid; grid-template-columns:1.1fr .9fr; gap:32px; align-items:center;">'
        . '<div>'
        . '<div style="font-family:var(--font-mono); font-size:12px; color:#37E29A; letter-spacing:.1em; margin-bottom:10px;">LISTING OF THE WEEK</div>'
        . '<h1 style="font-family:var(--font-display); font-weight:800; font-size:clamp(30px,4vw,46px); letter-spacing:-.02em; margin:0 0 10px;">4 Ridgeline Overlook</h1>'
        . '<div style="font-family:var(--font-display); font-weight:900; font-size:28px; color:#FFC08A; margin-bottom:12px;">$725,000</div>'
        . '<p style="font-size:15px; color:#CFCBE6; line-height:1.6; margin:0 0 20px;">Five bedrooms, a wall of glass over the valley, and a kitchen that will ruin restaurants for you. Sunset showings recommended — you\'ll see why.</p>'
        . '<a href="#" class="prt-lift" style="text-decoration:none; display:inline-block; background:' . $GRAD . '; color:#fff; padding:14px 28px; border-radius:999px; font-weight:700; font-family:var(--font-display);">Schedule a showing</a>'
        . '</div>'
        . '<div style="aspect-ratio:4/3; border-radius:18px; background:repeating-linear-gradient(135deg, rgba(108,76,241,.35) 0 18px, rgba(255,77,157,.2) 18px 36px); border:1px solid rgba(255,255,255,.15);"></div>'
        . '</div>';
    $rowsB = '<div style="display:grid; gap:14px; margin-top:24px;">';
    foreach ([
        ['$489,000', '1128 Willow Bend Ct', '4 bd · 3 ba · 2,450 sqft', 'New'],
        ['$412,500', '22 Harborview Ln', '3 bd · 2.5 ba · 1,980 sqft', 'Reduced'],
        ['$339,900', '87 Sycamore Row #2B', '2 bd · 2 ba · 1,180 sqft', 'Open house'],
        ['$265,000', '310 Maple St', '3 bd · 1 ba · 1,320 sqft', 'Starter'],
    ] as $r) {
        $rowsB .= '<div class="prt-spec-card prt-lift" style="background:#fff; border:1.5px solid #ECE6FB; border-radius:18px; padding:20px 26px; display:flex; align-items:center; gap:20px; flex-wrap:wrap;">'
            . '<div style="font-family:var(--font-display); font-weight:900; font-size:22px; color:#6C4CF1; min-width:120px;">' . esc_html($r[0]) . '</div>'
            . '<div style="flex:1; min-width:200px;"><div style="font-family:var(--font-display); font-weight:700; font-size:16px; color:#17151F;">' . esc_html($r[1]) . '</div>'
            . '<div style="font-family:var(--font-mono); font-size:12px; color:#5A5676; margin-top:2px;">' . esc_html($r[2]) . '</div></div>'
            . '<span style="font-family:var(--font-mono); font-size:11px; text-transform:uppercase; letter-spacing:.08em; background:#EEE8FE; color:#4a2fb0; border-radius:999px; padding:5px 12px;">' . esc_html($r[3]) . '</span>'
            . '</div>';
    }
    $rowsB .= '</div>';
    $patterns['prt-site/realty-listings-b'] = [
        'title'   => __('Realty Listings — B (spotlight + list rows)', 'pressroot'),
        'content' => $wrap($html($spotB), '48px', '0px') . $wrap($html($rowsB), '0px', '64px'),
    ];

    /* ════════ AGENTS — Variant A: agent cards ════════ */
    $agHeadA = '<section style="text-align:center; padding:20px 0 6px;">'
        . '<h1 style="font-family:var(--font-display); font-weight:800; font-size:clamp(38px,5vw,58px); letter-spacing:-.03em; margin:0 0 12px; color:#17151F;">The team.</h1>'
        . '<p style="font-size:17px; color:#5A5676; max-width:36em; margin:0 auto;">Real people who answer their phones — even on Sundays. Especially on Sundays.</p></section>';
    $agGridA = '<div style="display:grid; grid-template-columns:repeat(3,1fr); gap:20px; margin-top:28px;">';
    foreach ([
        ['MB', 'Maya Brooks', 'Broker / Owner', '15 years · westside specialist · 4.9★', '#EEE8FE', '#6C4CF1'],
        ['DR', 'Dev Rana', 'Buyer\'s Agent', 'First-time buyer whisperer · bilingual', '#FFE9F3', '#FF4D9D'],
        ['SO', 'Sam Ortiz', 'Listing Agent', 'Staging + pricing strategy · 11-day average', '#FFF0E6', '#FF7A3D'],
    ] as $a) {
        $agGridA .= '<div class="prt-spec-card" style="background:#fff; border:1.5px solid #ECE6FB; border-radius:18px; padding:28px; text-align:center;">'
            . '<div style="width:72px; height:72px; border-radius:50%; margin:0 auto 14px; background:' . $a[4] . '; color:' . $a[5] . '; display:flex; align-items:center; justify-content:center; font-family:var(--font-display); font-weight:800; font-size:22px;">' . esc_html($a[0]) . '</div>'
            . '<h3 style="font-family:var(--font-display); font-weight:800; font-size:19px; margin:0 0 2px; color:#17151F;">' . esc_html($a[1]) . '</h3>'
            . '<div style="font-family:var(--font-mono); font-size:12px; color:#6C4CF1; margin-bottom:8px;">' . esc_html($a[2]) . '</div>'
            . '<p style="font-size:13.5px; color:#5A5676; margin:0 0 16px;">' . esc_html($a[3]) . '</p>'
            . '<a href="#" class="prt-lift" style="text-decoration:none; display:inline-block; background:#fff; border:1.5px solid #ECE6FB; color:#6C4CF1; padding:10px 22px; border-radius:999px; font-weight:700; font-size:13px; font-family:var(--font-display);">Book a call</a>'
            . '</div>';
    }
    $agGridA .= '</div>';
    $patterns['prt-site/realty-agents-a'] = [
        'title'   => __('Realty Agents — A (team cards)', 'pressroot'),
        'content' => $wrap($html($agHeadA), '56px', '0px') . $wrap($html($agGridA), '0px', '64px'),
    ];

    /* ════════ AGENTS — Variant B: valuation CTA + testimonials ════════ */
    $valB = '<div class="prt-spec-card" style="background:linear-gradient(135deg,#6C4CF1 0%,#FF4D9D 55%,#FF7A3D 100%); color:#fff; border-radius:28px; padding:54px 46px; text-align:center;">'
        . '<h1 style="font-family:var(--font-display); font-weight:800; font-size:clamp(32px,4.5vw,50px); letter-spacing:-.02em; margin:0 0 12px;">What\'s your home worth?</h1>'
        . '<p style="font-size:18px; opacity:.94; max-width:32em; margin:0 auto 24px;">A real valuation from a local agent — comps, market timing, and a prep punch-list. Free, no strings, usually within 24 hours.</p>'
        . '<a href="mailto:hello@example.com" class="prt-lift" style="text-decoration:none; display:inline-block; background:#fff; color:#6C4CF1; padding:16px 34px; border-radius:999px; font-weight:800; font-size:16px; font-family:var(--font-display); box-shadow:0 10px 26px rgba(23,21,31,.22);">Get my free valuation</a>'
        . '</div>';
    $testB = '<div style="display:grid; grid-template-columns:repeat(3,1fr); gap:18px; margin-top:24px;">';
    foreach ([
        ['"Sold in nine days, over asking. Maya called the price to within $2k."', '— The Hendersons, sellers'],
        ['"Dev toured 14 places with us and never once pushed. We\'d use him again tomorrow."', '— Priya & Colin, first-time buyers'],
        ['"Sam\'s staging plan cost us $800 and made us $30,000. Do what Sam says."', '— J. Alvarez, seller'],
    ] as $t) {
        $testB .= '<div class="prt-spec-card" style="background:#fff; border:1.5px solid #ECE6FB; border-radius:18px; padding:26px;">'
            . '<div style="color:#FFC53D; letter-spacing:2px; margin-bottom:10px;">★★★★★</div>'
            . '<p style="font-size:14.5px; color:#4A4660; line-height:1.6; margin:0 0 12px; font-style:italic;">' . $t[0] . '</p>'
            . '<div style="font-family:var(--font-mono); font-size:12px; color:#5A5676;">' . esc_html($t[1]) . '</div></div>';
    }
    $testB .= '</div>';
    $patterns['prt-site/realty-agents-b'] = [
        'title'   => __('Realty Agents — B (valuation CTA + reviews)', 'pressroot'),
        'content' => $wrap($html($valB), '48px', '0px') . $wrap($html($testB), '0px', '64px'),
    ];

    foreach ($patterns as $slug => $def) {
        register_block_pattern($slug, [
            'title'      => $def['title'],
            'content'    => $def['content'],
            'categories' => ['prt-site-types'],
        ]);
    }
}, 11);
