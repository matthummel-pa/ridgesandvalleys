<?php

/**
 * Dedicated "Agency / Studio" site-type patterns — Appearance -> AI Setup
 * Assistant.
 *
 * The Pressroot AI (app/ai-assistant.php) lets a theme owner pick a
 * site type and get starter pages pre-filled with a full-page block pattern.
 * Until now every site type (including "agency") reused the same generic,
 * personal-freelancer-voiced patterns from page-patterns.php
 * (pressroot/services-full, pressroot/pricing-full, pressroot/contact-full).
 *
 * This file gives the "Agency / Studio" profile its OWN tailored patterns —
 * written for a multi-person creative/dev agency selling to business
 * clients (confident, results-driven tone), with TWO layout+copy variants per
 * page so the "Regenerate" control in the admin UI has something meaningfully
 * different to swap to. Registered under the shared 'prt-site-types' pattern
 * category (registered centrally elsewhere — this file only tags patterns
 * with it, it does not call register_block_pattern_category() itself).
 *
 * Slugs:
 *   prt-site/agency-services-a / -b  — Services page
 *   prt-site/agency-pricing-a  / -b  — Pricing page
 *   prt-site/agency-contact-a  / -b  — Contact page
 *
 * ai-assistant.php's prt_site_types() 'agency' profile can point its
 * 'pattern' keys at these slugs instead of the generic pressroot/* ones;
 * this file only registers the patterns, it doesn't wire that mapping.
 */

namespace App;

add_action('init', function () {

    /* ── Block-markup helpers (duplicated per-file by convention — see
     * block-patterns.php / page-patterns.php for the identical originals) ── */

    $wrap = function (string $inner, string $pt = '64px', string $pb = '24px'): string {
        $style = '{"spacing":{"padding":{"top":"' . $pt . '","bottom":"' . $pb . '"}}}';
        return '<!-- wp:group {"className":"prt-wrap","style":' . $style . ',"layout":{"type":"constrained","contentSize":"1240px"}} -->'
            . '<div class="wp-block-group prt-wrap" style="padding-top:' . $pt . ';padding-bottom:' . $pb . '">'
            . $inner . '</div><!-- /wp:group -->' . "\n\n";
    };

    $eyebrow = function (string $text): string {
        return '<!-- wp:paragraph {"className":"eyebrow","fontSize":"small","textColor":"purple"} -->'
            . '<p class="eyebrow has-purple-color has-text-color has-small-font-size">' . esc_html($text) . '</p>'
            . '<!-- /wp:paragraph -->';
    };

    $h = function (int $level, string $text, string $size = 'x-large'): string {
        $tag = 'h' . $level;
        return '<!-- wp:heading {"level":' . $level . ',"fontSize":"' . $size . '"} -->'
            . '<' . $tag . ' class="wp-block-heading has-' . $size . '-font-size">' . $text . '</' . $tag . '>'
            . '<!-- /wp:heading -->';
    };

    $p = function (string $text, string $size = 'medium', string $color = 'body'): string {
        return '<!-- wp:paragraph {"fontSize":"' . $size . '","textColor":"' . $color . '"} -->'
            . '<p class="has-' . $color . '-color has-text-color has-' . $size . '-font-size">' . $text . '</p>'
            . '<!-- /wp:paragraph -->';
    };

    $buttons = function (array $btns): string {
        $row = '<div style="display:flex; gap:14px; flex-wrap:wrap; margin-top:6px;">';
        foreach ($btns as $b) {
            $base = 'text-decoration:none; padding:15px 28px; border-radius:999px; font-weight:700; font-size:16px; font-family:var(--font-display);';
            $row .= ! empty($b['outline'])
                ? '<a href="' . esc_url($b['url']) . '" class="prt-lift" style="' . $base . ' background:#fff; border:1.5px solid #ECE6FB; color:#6C4CF1;">' . esc_html($b['text']) . '</a>'
                : '<a href="' . esc_url($b['url']) . '" class="prt-lift" style="' . $base . ' background:linear-gradient(135deg,#6C4CF1 0%,#FF4D9D 55%,#FF7A3D 100%); color:#fff;">' . esc_html($b['text']) . '</a>';
        }
        return "<!-- wp:html -->\n" . $row . '</div>' . "\n<!-- /wp:html -->";
    };

    $hero = function (string $eb, string $title, string $lead, array $btns = []) use ($wrap, $eyebrow, $p, $buttons): string {
        $inner = $eyebrow($eb)
            . '<!-- wp:heading {"level":1,"style":{"typography":{"fontSize":"clamp(38px, 5vw, 60px)","lineHeight":"1.05"}}} --><h1 class="wp-block-heading" style="font-size:clamp(38px, 5vw, 60px);line-height:1.05">' . $title . '</h1><!-- /wp:heading -->'
            . $p($lead, 'large', 'body');
        if ($btns) {
            $inner .= $buttons($btns);
        }
        return $wrap($inner, '70px', '20px');
    };

    $dyn = function (string $name, array $attrs) use ($wrap): string {
        return $wrap('<!-- wp:prt/' . $name . ' ' . wp_json_encode($attrs) . ' /-->', '20px', '20px');
    };

    $faq = function (array $items) use ($wrap): string {
        $html = '<div style="max-width:840px; margin:0 auto;">';
        foreach ($items as $it) {
            $html .= '<details style="background:#fff; border:1.5px solid #ECE6FB; border-radius:18px; padding:22px 24px; margin-bottom:14px;">'
                . '<summary style="cursor:pointer; font-family:var(--font-display); font-weight:700; font-size:18px; color:#17151F;">' . esc_html($it[0]) . '</summary>'
                . '<p style="font-size:15.5px; color:#5A5676; line-height:1.6; margin:14px 0 0;">' . esc_html($it[1]) . '</p>'
                . '</details>';
        }
        $html .= '</div>';
        return $wrap("<!-- wp:html -->\n" . $html . "\n<!-- /wp:html -->", '20px', '20px');
    };

    $cardGrid = function (string $cols, string $cardsHtml): string {
        return "<!-- wp:html -->\n"
            . '<div class="prt-grid-' . $cols . '" style="display:grid; grid-template-columns:repeat(' . $cols . ',1fr); gap:20px; margin-top:8px;">'
            . $cardsHtml . '</div>' . "\n<!-- /wp:html -->";
    };

    // Gradient-blob accent, lifted from home-patterns.php's hero pattern —
    // reused here (as a wp:html section wrapper) to give the agency pages the
    // same soft, animated color-wash backdrop as the homepage hero.
    $blobSection = function (string $innerHtml, string $bg = '#FFF9F5'): string {
        return "<!-- wp:html -->\n"
            . '<section style="position:relative; overflow:hidden; padding:64px 32px 24px; background:' . $bg . ';">'
            . '<div style="position:absolute; top:-60px; left:-40px; width:340px; height:340px; background:radial-gradient(circle at 30% 30%,#6C4CF1,#22CFEE); filter:blur(10px); opacity:.28; animation:prt-drift 16s ease-in-out infinite;"></div>'
            . '<div style="position:absolute; bottom:-80px; right:60px; width:300px; height:300px; background:radial-gradient(circle at 60% 40%,#FF7A3D,#FF4D9D); filter:blur(14px); opacity:.24; animation:prt-drift 20s ease-in-out infinite reverse;"></div>'
            . '<div class="prt-wrap" style="position:relative; padding-left:0; padding-right:0; max-width:1240px; margin:0 auto;">'
            . $innerHtml
            . '</div></section>'
            . "\n<!-- /wp:html -->";
    };

    $patterns = [];

    /* ═══════════════════════════════════════════════════════════════════
     * SERVICES — Variant A: clean 3-column grid, corporate/results tone.
     * ═══════════════════════════════════════════════════════════════════ */
    $svcSkillsA = wp_json_encode([
        ['title' => 'Web Design & Development', 'body' => 'Custom-built, fully responsive sites and web apps — from marketing pages to complex client portals — engineered for speed, accessibility, and easy in-house editing.'],
        ['title' => 'Brand & Digital Strategy', 'body' => 'Positioning, identity systems, and a digital roadmap that ties every touchpoint back to measurable business goals, not just a new logo.'],
        ['title' => 'Marketing & Growth / SEO', 'body' => 'Technical SEO, content strategy, and paid + organic growth programs built to compound traffic and pipeline quarter over quarter.'],
        ['title' => 'Ongoing Support & Maintenance', 'body' => 'Proactive monitoring, security patching, performance tuning, and a real human on call — so your site stays fast and current long after launch.'],
    ]);
    $svcProcessA = '';
    foreach ([
        ['01', '#6C4CF1', '#fff', 'Discovery & Audit', 'We assess your current site, brand, and funnel to find the highest-leverage opportunities before writing a single line of a proposal.'],
        ['02', '#FF7A3D', '#17151F', 'Strategy & Scope', 'A written plan with milestones, deliverables, and a fixed or retainer price — signed off before work begins.'],
        ['03', '#22CFEE', '#06283a', 'Design & Build', 'Weekly progress reviews with your stakeholders so there are no surprises at delivery.'],
        ['04', '#37E29A', '#17151F', 'Launch & Grow', 'A structured handoff plus an optional growth retainer to keep improving results after go-live.'],
    ] as $s) {
        $svcProcessA .= '<div class="prt-lift" style="background:' . $s[1] . '; color:' . $s[2] . '; border-radius:22px; padding:28px;">'
            . '<div style="font-family:var(--font-mono); font-size:12px; opacity:.8; margin-bottom:14px;">PHASE ' . $s[0] . '</div>'
            . '<h3 style="font-family:var(--font-display); font-weight:700; font-size:20px; margin:0 0 8px;">' . $s[3] . '</h3>'
            . '<p style="font-size:14px; line-height:1.5; margin:0; opacity:.92;">' . $s[4] . '</p></div>';
    }
    $svcStatsA = wp_json_encode([
        ['value' => '4', 'label' => 'Core service lines'],
        ['value' => '120+', 'label' => 'Projects delivered for clients'],
        ['value' => '2 wks', 'label' => 'Average time to first proposal'],
        ['value' => '98%', 'label' => 'Client retention on retainer'],
    ]);
    $servicesA  = $hero('What we do', 'Full-service digital, built to perform.',
        'We&#8217;re a multi-disciplinary studio that designs, builds, and grows web experiences for ambitious businesses — one accountable team, start to finish.',
        [['text' => 'View pricing', 'url' => '/pricing/'], ['text' => 'Start a project', 'url' => '/contact/', 'outline' => true]]);
    $servicesA .= $dyn('stat-strip', ['stats' => $svcStatsA, 'columns' => 4]);
    $servicesA .= $wrap('<!-- wp:heading {"level":2,"className":"screen-reader-text"} --><h2 class="screen-reader-text">Our services</h2><!-- /wp:heading -->', '0px', '0px') . $dyn('skills-grid', ['cards' => $svcSkillsA, 'columns' => 3]);
    $servicesA .= $wrap($h(2, 'How an engagement runs', 'x-large') . $cardGrid('4', $svcProcessA), '50px', '20px');
    $servicesA .= $dyn('cta-band', ['heading' => 'Ready to brief us in?', 'body' => 'Tell us about your goals and timeline — we\'ll come back with a clear, fixed-scope proposal within days.', 'btnText' => 'Start a project →', 'btnUrl' => '/contact/', 'variant' => 'dark']);
    $patterns['prt-site/agency-services-a'] = [
        'title'       => __('Agency — Services (Grid, corporate tone)', 'pressroot'),
        'description' => __('Clean 3-column services grid with a stat strip and a 4-phase process — confident, corporate agency tone.', 'pressroot'),
        'content'     => $servicesA,
    ];

    /* ═══════════════════════════════════════════════════════════════════
     * SERVICES — Variant B: asymmetric bento layout, punchier tone.
     * ═══════════════════════════════════════════════════════════════════ */
    $bentoServices = '<div style="display:grid; grid-template-columns:1.5fr 1fr; grid-template-rows:auto auto; gap:20px; margin-top:8px;">'
        . '<div class="prt-lift" style="grid-row:span 2; background:#17151F; color:#fff; border-radius:26px; padding:36px; display:flex; flex-direction:column; justify-content:space-between;">'
        . '<div><div style="font-family:var(--font-mono); font-size:12px; color:#37E29A; margin-bottom:14px;">FLAGSHIP SERVICE</div>'
        . '<h3 style="font-family:var(--font-display); font-weight:800; font-size:clamp(24px,3vw,30px); margin:0 0 14px; line-height:1.1;">Web Design &amp; Development</h3>'
        . '<p style="font-size:16px; line-height:1.6; opacity:.9; margin:0;">Custom sites and web apps designed and engineered in-house — no templates, no outsourced dev. From first wireframe to a fast, accessible, easy-to-edit build.</p></div>'
        . '<div style="margin-top:24px; font-family:var(--font-display); font-weight:700; font-size:14px; color:#22CFEE;">→ Most requested service</div></div>'
        . '<div class="prt-lift" style="background:#EEE8FE; border-radius:26px; padding:28px;">'
        . '<h3 style="font-family:var(--font-display); font-weight:700; font-size:19px; margin:0 0 8px; color:#17151F;">Brand &amp; Digital Strategy</h3>'
        . '<p style="font-size:14.5px; line-height:1.55; margin:0; color:#4A4660;">Identity systems and positioning that make every future marketing dollar work harder.</p></div>'
        . '<div class="prt-lift prt-spec-card" style="background:#fff; border:1.5px solid #ECE6FB; border-radius:26px; padding:28px;">'
        . '<h3 style="font-family:var(--font-display); font-weight:700; font-size:19px; margin:0 0 8px; color:#17151F;">Marketing, Growth &amp; SEO</h3>'
        . '<p style="font-size:14.5px; line-height:1.55; margin:0; color:#4A4660;">Technical SEO and growth programs that compound — built to outlast any single campaign.</p></div>'
        . '<div class="prt-lift" style="grid-column:span 2; background:#FF7A3D; color:#17151F; border-radius:26px; padding:28px; display:flex; align-items:center; justify-content:space-between; flex-wrap:wrap; gap:16px;">'
        . '<div><h3 style="font-family:var(--font-display); font-weight:800; font-size:20px; margin:0 0 6px;">Ongoing Support &amp; Maintenance</h3>'
        . '<p style="font-size:14.5px; margin:0; opacity:.85;">Security, uptime, and performance — handled, so you never think about it.</p></div>'
        . '<span style="font-family:var(--font-display); font-weight:800; font-size:13px; background:#17151F; color:#fff; padding:10px 18px; border-radius:999px; white-space:nowrap;">Included on every retainer</span></div>'
        . '</div>';
    $servicesB  = $blobSection(
        $eyebrow('Services')
        . '<h1 style="font-family:var(--font-display); font-weight:800; font-size:clamp(38px,5vw,60px); line-height:1.05; margin:0 0 20px; color:#17151F;">Everything your brand needs online. Nothing it doesn&#8217;t.</h1>'
        . '<p style="font-family:var(--font-display); font-size:clamp(17px,2vw,22px); line-height:1.5; max-width:38em; color:#4A4660; margin:0 0 30px;">We&#8217;re a small studio that punches way above its size — design, dev, strategy, and growth under one roof, so nothing gets lost between vendors.</p>'
        . $buttons([['text' => "Let's scope it", 'url' => '/contact/'], ['text' => 'See pricing', 'url' => '/pricing/', 'outline' => true]])
    );
    $servicesB .= $wrap($bentoServices, '20px', '30px');
    $servicesB .= $dyn('cta-band', ['heading' => 'Got a messy brief? Good.', 'body' => 'We like turning "we need something" into a scoped, priced plan. Send us the messy version — we\'ll shape it up.', 'btnText' => "Let's talk →", 'btnUrl' => '/contact/', 'variant' => 'light']);
    $patterns['prt-site/agency-services-b'] = [
        'title'       => __('Agency — Services (Bento, punchy tone)', 'pressroot'),
        'description' => __('Asymmetric bento-grid services layout with a gradient-blob hero — punchier, more casual studio tone.', 'pressroot'),
        'content'     => $servicesB,
    ];

    /* ═══════════════════════════════════════════════════════════════════
     * PRICING — Variant A: 3-column tier cards, corporate tone.
     * ═══════════════════════════════════════════════════════════════════ */
    $tiersA = '';
    foreach ([
        ['Project', '$6,500', 'starting price, fixed scope', 'A one-off site, campaign, or rebuild with a clear start and finish.', ['Up to 8 pages', 'Custom design system', 'On-page SEO setup', '30-day post-launch support', '4–6 week delivery'], false],
        ['Growth Retainer', '$4,200', 'per month, 3-month minimum', 'Ongoing design, dev, and marketing capacity for a growing brand.', ['Dedicated project lead', 'Design + dev sprints monthly', 'SEO & content roadmap', 'Monthly reporting call', 'Priority turnaround'], true],
        ['Partner', 'Custom', 'annual engagement', 'Embedded, multi-workstream partnership for established teams.', ['Cross-functional pod (design/dev/strategy)', 'Quarterly roadmap planning', 'Analytics & growth experiments', 'Dedicated Slack channel', 'Executive quarterly review'], false],
    ] as $t) {
        [$name, $price, $sub, $desc, $feats, $featured] = $t;
        $cardStyle = $featured
            ? 'background:#6C4CF1; color:#fff; box-shadow:0 24px 50px rgba(124,92,255,.32);'
            : 'background:#fff; color:#17151F; border:1.5px solid #ECE6FB;';
        $featList = '';
        foreach ($feats as $f) {
            $featList .= '<span style="display:block; margin-bottom:10px;">✓ ' . $f . '</span>';
        }
        $tiersA .= '<div class="prt-lift" style="' . $cardStyle . ' border-radius:26px; padding:36px; position:relative;">'
            . ($featured ? '<span style="position:absolute; top:-13px; left:50%; transform:translateX(-50%); background:#37E29A; color:#17151F; padding:6px 16px; border-radius:999px; font-size:12px; font-weight:800;">MOST POPULAR</span>' : '')
            . '<h3 style="font-family:var(--font-display); font-weight:700; font-size:22px; margin:0 0 6px;">' . $name . '</h3>'
            . '<p style="font-size:14.5px; opacity:.8; margin:0 0 18px;">' . $desc . '</p>'
            . '<div style="font-family:var(--font-display); font-weight:900; font-size:42px; letter-spacing:-.03em;">' . $price . '</div>'
            . '<div style="font-size:13px; opacity:.75; margin-bottom:22px;">' . $sub . '</div>'
            . '<div style="font-size:15px; line-height:1.4;">' . $featList . '</div></div>';
    }
    $pricingA  = $hero('Pricing', 'Straightforward pricing for serious projects.',
        'No hourly guesswork. Pick the engagement model that fits where your business is right now, and we&#8217;ll scope the details together.',
        [['text' => 'Book a scoping call', 'url' => '/contact/']]);
    $pricingA .= $wrap('<!-- wp:heading {"level":2,"className":"screen-reader-text"} --><h2 class="screen-reader-text">Plans</h2><!-- /wp:heading -->', '0px', '0px') . $cardGrid('3', $tiersA);
    $pricingA .= $wrap($h(2, 'Common questions', 'x-large'), '50px', '0px');
    $pricingA .= $faq([
        ['How do you decide between a Project and a Retainer?', 'If you have a defined deliverable with a clear finish line, Project pricing usually fits best. If you need ongoing design, development, or marketing capacity, a Growth Retainer gives you a standing team without the overhead of hiring.'],
        ['Can we start with a Project and move to a Retainer later?', 'Yes — most of our long-term clients started with a single project. Once the foundation is live, moving into a retainer is a simple conversation, not a renegotiation from scratch.'],
        ['What is included in the Partner tier?', 'A dedicated cross-functional pod, quarterly roadmap planning, and direct access to our senior team — built for organizations that treat their digital presence as a core growth channel.'],
        ['Who owns the work once we pay?', 'You do. Code, designs, content, and accounts are yours outright — there is no lock-in on any tier.'],
    ]);
    $pricingA .= $dyn('cta-band', ['heading' => 'Not sure which tier fits?', 'body' => 'Send us a few details about your project and budget — we\'ll recommend the right starting point, free of charge.', 'btnText' => 'Get a recommendation →', 'btnUrl' => '/contact/', 'variant' => 'dark']);
    $patterns['prt-site/agency-pricing-a'] = [
        'title'       => __('Agency — Pricing (Tier cards, corporate tone)', 'pressroot'),
        'description' => __('3-column Project / Growth Retainer / Partner tier cards with an FAQ accordion — corporate agency tone.', 'pressroot'),
        'content'     => $pricingA,
    ];

    /* ═══════════════════════════════════════════════════════════════════
     * PRICING — Variant B: asymmetric bento comparison, punchier tone.
     * ═══════════════════════════════════════════════════════════════════ */
    $bentoPricing = '<div style="display:grid; grid-template-columns:1fr 1.3fr 1fr; gap:20px; margin-top:8px; align-items:stretch;">'
        . '<div class="prt-lift prt-spec-card" style="background:#fff; border:1.5px solid #ECE6FB; border-radius:26px; padding:32px; display:flex; flex-direction:column;">'
        . '<span style="font-family:var(--font-mono); font-size:12px; color:#7C75A8; margin-bottom:10px;">FOR A ONE-OFF</span>'
        . '<h3 style="font-family:var(--font-display); font-weight:800; font-size:22px; margin:0 0 10px; color:#17151F;">Project</h3>'
        . '<div style="font-family:var(--font-display); font-weight:900; font-size:38px; color:#17151F; letter-spacing:-.03em;">from $6.5k</div>'
        . '<p style="font-size:14px; color:#5A5676; margin:10px 0 20px; flex-grow:1;">One clear deliverable — a new site, a rebuild, a campaign microsite — priced and scoped up front.</p>'
        . '<span style="font-size:13px; color:#4A4660;">✓ Fixed scope &amp; timeline<br>✓ 30-day support window</span></div>'
        . '<div class="prt-lift" style="background:#17151F; color:#fff; border-radius:26px; padding:36px; display:flex; flex-direction:column; position:relative; box-shadow:0 24px 50px rgba(23,21,31,.28);">'
        . '<span style="position:absolute; top:-13px; left:50%; transform:translateX(-50%); background:#37E29A; color:#17151F; padding:6px 16px; border-radius:999px; font-size:12px; font-weight:800;">MOST POPULAR</span>'
        . '<span style="font-family:var(--font-mono); font-size:12px; color:#22CFEE; margin-bottom:10px;">FOR A GROWING BRAND</span>'
        . '<h3 style="font-family:var(--font-display); font-weight:800; font-size:24px; margin:0 0 10px;">Growth Retainer</h3>'
        . '<div style="font-family:var(--font-display); font-weight:900; font-size:44px; letter-spacing:-.03em;">$4.2k<span style="font-size:16px; opacity:.7;">/mo</span></div>'
        . '<p style="font-size:15px; opacity:.9; margin:12px 0 20px; flex-grow:1;">A standing design + dev + growth team, on call — for brands that need to keep shipping, not just launch once.</p>'
        . '<span style="font-size:14px;">✓ Dedicated project lead<br>✓ Monthly sprints + reporting<br>✓ Priority turnaround</span></div>'
        . '<div class="prt-lift" style="background:#EEE8FE; border-radius:26px; padding:32px; display:flex; flex-direction:column;">'
        . '<span style="font-family:var(--font-mono); font-size:12px; color:#5A3ED1; margin-bottom:10px;">FOR THE LONG HAUL</span>'
        . '<h3 style="font-family:var(--font-display); font-weight:800; font-size:22px; margin:0 0 10px; color:#17151F;">Partner</h3>'
        . '<div style="font-family:var(--font-display); font-weight:900; font-size:32px; color:#17151F; letter-spacing:-.03em;">Custom</div>'
        . '<p style="font-size:14px; color:#4A4660; margin:10px 0 20px; flex-grow:1;">An embedded, cross-functional pod that runs your digital roadmap alongside your team, quarter over quarter.</p>'
        . '<span style="font-size:13px; color:#4A4660;">✓ Dedicated Slack channel<br>✓ Quarterly roadmap review</span></div>'
        . '</div>';
    $pricingB  = $blobSection(
        $eyebrow('Pricing')
        . '<h1 style="font-family:var(--font-display); font-weight:800; font-size:clamp(38px,5vw,60px); line-height:1.05; margin:0 0 20px; color:#17151F;">Pick your speed. We&#8217;ll match it.</h1>'
        . '<p style="font-family:var(--font-display); font-size:clamp(17px,2vw,22px); line-height:1.5; max-width:36em; color:#4A4660; margin:0 0 30px;">One-and-done project, an always-on growth team, or a full embedded partnership — three ways to work with us, zero hourly-rate math.</p>'
        . $buttons([['text' => 'Talk pricing', 'url' => '/contact/']])
    );
    $pricingB .= $wrap($bentoPricing, '20px', '20px');
    $pricingB .= $faq([
        ['Why no hourly rate?', 'Hourly billing rewards slow work. We price by outcome and scope instead, so incentives stay aligned with getting you results, not racking up hours.'],
        ['Can we mix tiers — say, a Project now and a Retainer later?', 'All the time. Most Growth Retainer clients started as a Project. We\'ll flag it if we think you\'ve outgrown your current tier.'],
        ['What does "priority turnaround" actually mean?', 'Retainer and Partner clients get first slot in our sprint calendar — no waiting behind other clients\' projects for a fix or a new page.'],
    ]);
    $pricingB .= $dyn('cta-band', ['heading' => 'Still comparing options?', 'body' => 'Fifteen minutes on a call will tell you more than another hour reading pricing pages. Let\'s just talk it through.', 'btnText' => 'Book a call →', 'btnUrl' => '/contact/', 'variant' => 'light']);
    $patterns['prt-site/agency-pricing-b'] = [
        'title'       => __('Agency — Pricing (Bento comparison, punchy tone)', 'pressroot'),
        'description' => __('Asymmetric bento pricing comparison with a gradient-blob hero — punchier, faster-reading tone.', 'pressroot'),
        'content'     => $pricingB,
    ];

    /* ═══════════════════════════════════════════════════════════════════
     * CONTACT — Variant A: agency-intake form emphasis, corporate tone.
     * ═══════════════════════════════════════════════════════════════════ */
    $intakeCardsA = '';
    foreach ([
        ['🧭', 'Tell us about your project', 'Goals, timeline, and budget range — the more context, the faster and more accurate our proposal.'],
        ['📋', "We'll send a written scope", 'A clear proposal with deliverables, timeline, and price — usually within 2 business days.'],
        ['🤝', 'We kick off together', 'A kickoff call to align your team and ours, then we get to work.'],
    ] as $c) {
        $intakeCardsA .= '<div class="prt-lift prt-spec-card" style="background:#fff; border:1.5px solid #ECE6FB; border-radius:22px; padding:28px; text-align:left;">'
            . '<div style="font-size:28px; margin-bottom:12px;">' . $c[0] . '</div>'
            . '<h3 style="font-family:var(--font-display); font-weight:700; font-size:18px; margin:0 0 8px; color:#17151F;">' . $c[1] . '</h3>'
            . '<p style="font-size:14.5px; line-height:1.55; margin:0; color:#5A5676;">' . $c[2] . '</p></div>';
    }
    $contactA  = $hero('Start a project', 'Tell us about your project.',
        'The more detail you share, the more useful our first response will be. Every inquiry is read and answered by a real team member — no bots, no sales queue.',
        [['text' => 'Email the team', 'url' => '/contact/'], ['text' => 'View our work', 'url' => '/projects/', 'outline' => true]]);
    $contactA .= $wrap($h(2, 'What happens after you reach out', 'x-large') . $cardGrid('3', $intakeCardsA), '40px', '20px');
    $contactA .= $dyn('cta-band', ['heading' => 'Prefer to talk it through first?', 'body' => 'Book a free 20-minute scoping call — no pitch, just questions about what you\'re trying to build.', 'btnText' => 'Book a call →', 'btnUrl' => '/contact/', 'variant' => 'light']);
    $patterns['prt-site/agency-contact-a'] = [
        'title'       => __('Agency — Contact (Intake steps, corporate tone)', 'pressroot'),
        'description' => __('Project-intake framing with a 3-step "what happens next" grid — corporate, process-forward tone.', 'pressroot'),
        'content'     => $contactA,
    ];

    /* ═══════════════════════════════════════════════════════════════════
     * CONTACT — Variant B: bento split layout, punchier tone.
     * ═══════════════════════════════════════════════════════════════════ */
    $bentoContact = '<div style="display:grid; grid-template-columns:1.2fr 1fr; gap:20px; margin-top:8px; align-items:start;">'
        . '<div class="prt-lift" style="background:#17151F; color:#fff; border-radius:26px; padding:40px;">'
        . '<div style="font-family:var(--font-mono); font-size:12px; color:#37E29A; margin-bottom:16px;">PROJECT INTAKE</div>'
        . '<h3 style="font-family:var(--font-display); font-weight:800; font-size:26px; margin:0 0 14px;">Give us the messy version.</h3>'
        . '<p style="font-size:16px; line-height:1.6; opacity:.9; margin:0 0 20px;">Half-formed idea, a Figma file, a competitor you admire — send whatever you\'ve got. We turn rough briefs into scoped plans for a living.</p>'
        . '<span style="display:inline-block; font-family:var(--font-display); font-weight:700; font-size:14px; background:#22CFEE; color:#06283a; padding:8px 16px; border-radius:999px;">Usually replies within 1 business day</span></div>'
        . '<div style="display:flex; flex-direction:column; gap:20px;">'
        . '<div class="prt-lift" style="background:#EEE8FE; border-radius:22px; padding:26px;">'
        . '<h4 style="font-family:var(--font-display); font-weight:700; font-size:16px; margin:0 0 8px; color:#17151F;">📅 Book time directly</h4>'
        . '<p style="font-size:14px; margin:0; color:#4A4660;">Skip the back-and-forth — grab a slot on our calendar for a 20-minute intro call.</p></div>'
        . '<div class="prt-lift prt-spec-card" style="background:#fff; border:1.5px solid #ECE6FB; border-radius:22px; padding:26px;">'
        . '<h4 style="font-family:var(--font-display); font-weight:700; font-size:16px; margin:0 0 8px; color:#17151F;">💼 Working with vendors already?</h4>'
        . '<p style="font-size:14px; margin:0; color:#4A4660;">We regularly plug into an existing team as the design/dev arm — just say so in your message.</p></div>'
        . '</div></div>';
    $contactB  = $blobSection(
        $eyebrow('Contact')
        . '<h1 style="font-family:var(--font-display); font-weight:800; font-size:clamp(38px,5vw,60px); line-height:1.05; margin:0 0 20px; color:#17151F;">Let&#8217;s build the thing you keep putting off.</h1>'
        . '<p style="font-family:var(--font-display); font-size:clamp(17px,2vw,22px); line-height:1.5; max-width:36em; color:#4A4660; margin:0 0 30px;">Tell us what you&#8217;re trying to build and why now — we&#8217;ll come back with a real plan, not a form-letter reply.</p>'
    );
    $contactB .= $wrap($bentoContact, '20px', '30px');
    $contactB .= $dyn('cta-band', ['heading' => 'Not ready to write it all out?', 'body' => 'A two-line email is plenty to start. We\'ll ask the right follow-up questions.', 'btnText' => 'Say hello →', 'btnUrl' => '/contact/', 'variant' => 'dark']);
    $patterns['prt-site/agency-contact-b'] = [
        'title'       => __('Agency — Contact (Bento split, punchy tone)', 'pressroot'),
        'description' => __('Bento-style split layout with a gradient-blob hero — punchier, more casual "send us the messy version" tone.', 'pressroot'),
        'content'     => $contactB,
    ];

    /* ── Register under the shared 'prt-site-types' category ──────────── */
    foreach ($patterns as $slug => $pat) {
        register_block_pattern($slug, [
            'title'       => $pat['title'],
            'description' => $pat['description'] ?? '',
            'categories'  => ['prt-site-types'],
            'blockTypes'  => ['core/post-content'],
            'content'     => $pat['content'],
        ]);
    }
}, 13);
