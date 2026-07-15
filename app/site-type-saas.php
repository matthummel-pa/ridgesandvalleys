<?php

/**
 * SaaS / Startup site-type patterns.
 *
 * Dedicated, product-led-growth patterns for the Pressroot AI's
 * "SaaS / Startup" profile (see app/ai-assistant.php — prt_site_types()['saas']).
 * That profile applies the "Midnight" Style Kit (a dark color scheme), so
 * every hero/CTA section here is wrapped in the prt/section dark wrapper
 * ("bgColor":"ink","textColor":"light") rather than relying on the active
 * Style Kit's colors, and any core-block text nested inside those dark
 * sections deliberately omits has-body-color/has-ink-color textColor classes
 * (which assume a light background) so it inherits the section's own
 * light-on-dark CSS instead.
 *
 * Six patterns, two layout/copy variants each, for three pages:
 *   prt-site/saas-features-a | -b  — Features page
 *   prt-site/saas-pricing-a  | -b  — Pricing page
 *   prt-site/saas-contact-a  | -b  — "Talk to sales" / contact page
 *
 * Registered under the 'prt-site-types' pattern category — that category
 * itself is registered centrally elsewhere, not here.
 */

namespace App;

add_action('init', function () {

    /* ── Block-markup helpers (same convention as app/page-patterns.php) ── */

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

    // Heading/paragraph variants with NO core textColor class at all, meant
    // to be nested inside a dark prt/section wrapper so they inherit its
    // light-on-dark text color instead of a light-background-assuming one.
    $hDark = function (int $level, string $text, string $size = 'x-large'): string {
        $tag = 'h' . $level;
        return '<!-- wp:heading {"level":' . $level . ',"fontSize":"' . $size . '"} -->'
            . '<' . $tag . ' class="wp-block-heading has-' . $size . '-font-size">' . $text . '</' . $tag . '>'
            . '<!-- /wp:heading -->';
    };

    $pDark = function (string $text, string $size = 'medium'): string {
        return '<!-- wp:paragraph {"fontSize":"' . $size . '"} -->'
            . '<p class="has-' . $size . '-font-size">' . $text . '</p>'
            . '<!-- /wp:paragraph -->';
    };

    $buttons = function (array $btns): string {
        $row = '<div style="display:flex; gap:14px; flex-wrap:wrap; margin-top:6px;">';
        foreach ($btns as $b) {
            $base = 'text-decoration:none; padding:15px 28px; border-radius:999px; font-weight:700; font-size:16px; font-family:var(--font-display);';
            $row .= ! empty($b['outline'])
                ? '<a href="' . esc_url($b['url']) . '" class="prt-lift" style="' . $base . ' background:transparent; border:1.5px solid #fff; color:#fff;">' . esc_html($b['text']) . '</a>'
                : '<a href="' . esc_url($b['url']) . '" class="prt-lift" style="' . $base . ' background:linear-gradient(135deg,#6C4CF1 0%,#FF4D9D 55%,#FF7A3D 100%); color:#fff;">' . esc_html($b['text']) . '</a>';
        }
        return "<!-- wp:html -->\n" . $row . '</div>' . "\n<!-- /wp:html -->";
    };

    $dyn = function (string $name, array $attrs) use ($wrap): string {
        return $wrap('<!-- wp:prt/' . $name . ' ' . wp_json_encode($attrs) . ' /-->', '20px', '20px');
    };

    $cardGrid = function (string $cols, string $cardsHtml): string {
        return "<!-- wp:html -->\n"
            . '<div class="prt-grid-' . $cols . '" style="display:grid; grid-template-columns:repeat(' . $cols . ',1fr); gap:20px; margin-top:8px;">'
            . $cardsHtml . '</div>' . "\n<!-- /wp:html -->";
    };

    // Dark hero wrapper: gradient-blob accent (same radial-gradient technique
    // as home-patterns.php's hero) behind a prt/section ink/light section.
    $darkHero = function (string $innerHtml, string $pt = 'xl', string $pb = 'lg'): string {
        $blobs = '<!-- wp:html -->'
            . '<div style="position:relative; height:0;">'
            . '<div style="position:absolute; top:-40px; left:-60px; width:420px; height:420px; background:radial-gradient(circle at 30% 30%,#6C4CF1,#22CFEE); filter:blur(60px); opacity:.28; z-index:0;"></div>'
            . '<div style="position:absolute; top:-20px; right:-80px; width:340px; height:340px; background:radial-gradient(circle at 60% 40%,#FF4D9D,#6C4CF1); filter:blur(70px); opacity:.22; z-index:0;"></div>'
            . '</div>'
            . '<!-- /wp:html -->';
        return '<!-- wp:prt/section {"bgColor":"ink","paddingTop":"' . $pt . '","paddingBottom":"' . $pb . '","containerWidth":"contained","textColor":"light"} -->'
            . $blobs
            . $innerHtml
            . '<!-- /wp:prt/section -->' . "\n\n";
    };

    // Dark CTA band wrapper, same dark-section technique, for end-of-page CTAs.
    $darkCta = function (string $eb, string $title, string $lead, array $btns) use ($eyebrow, $hDark, $pDark, $buttons): string {
        $inner = $eyebrow($eb) . $hDark(2, $title, 'x-large') . $pDark($lead, 'large') . $buttons($btns);
        return '<!-- wp:prt/section {"bgColor":"ink","paddingTop":"lg","paddingBottom":"lg","containerWidth":"contained","textColor":"light"} -->'
            . $inner
            . '<!-- /wp:prt/section -->' . "\n\n";
    };

    $patterns = [];

    /* ═══════════════════════════════════════════════════════════════════
     * FEATURES — Variant A: icon-grid, confident corporate-SaaS tone
     * ═══════════════════════════════════════════════════════════════════ */
    $featCardsA = '';
    foreach ([
        ['⚡', 'Real-time collaboration', 'Every teammate sees every change the instant it happens — no refresh, no merge conflicts, no "who has the latest version?"'],
        ['🔌', 'One-click integrations', 'Connect [Your Product] to the tools you already run — Slack, HubSpot, Salesforce, and 40+ more — in under a minute, no engineering required.'],
        ['🛡️', 'Enterprise-grade security', 'SSO, SCIM provisioning, and granular role-based permissions, backed by SOC 2 Type II controls and regional data residency.'],
        ['📊', 'Usage analytics dashboard', 'See exactly how your team and customers use the product — adoption, drop-off, and engagement, broken down in real time.'],
        ['🧩', 'Custom workflow builder', 'Automate approvals, notifications, and hand-offs with a drag-and-drop builder — no code, no waiting on IT.'],
        ['🌐', 'Global-scale infrastructure', '99.99% uptime SLA across multi-region infrastructure, so [Your Product] is fast and available wherever your team works.'],
    ] as $f) {
        $featCardsA .= '<div class="prt-lift" style="background:#242038; border:1px solid #322c50; border-radius:20px; padding:30px;">'
            . '<div style="font-size:30px; margin-bottom:16px;">' . $f[0] . '</div>'
            . '<h3 style="font-family:var(--font-display); font-weight:700; font-size:19px; color:#fff; margin:0 0 8px;">' . $f[1] . '</h3>'
            . '<p style="font-size:14.5px; line-height:1.6; color:#B9B4D9; margin:0;">' . $f[2] . '</p></div>';
    }
    $featuresA  = $darkHero(
        $eyebrow('Features')
        . $hDark(1, 'Everything your team needs, in one product.', 'huge')
        . $pDark('[Your Product] replaces the six tools your team is stitching together today — with a single, fast, secure platform built to scale with you.', 'large')
        . $buttons([['text' => 'Start free trial', 'url' => '/contact/'], ['text' => 'Book a demo', 'url' => '/contact/', 'outline' => true]])
    );
    $featuresA .= $wrap('<!-- wp:paragraph {"className":"eyebrow","fontSize":"small","textColor":"purple"} --><p class="eyebrow has-purple-color has-text-color has-small-font-size">Built for modern teams</p><!-- /wp:paragraph -->' . $h(2, 'A platform, not a point solution.', 'x-large') . $p('Six core capabilities, all included on every plan — no add-on pricing games.', 'medium', 'muted'), '60px', '10px');
    $featuresA .= $cardGrid('3', $featCardsA);
    $featuresA .= $wrap(
        '<!-- wp:html --><div style="font-family:var(--font-mono); font-size:13px; color:#7C75A8; letter-spacing:.02em;">// built by engineers, for engineers</div><!-- /wp:html -->',
        '50px', '10px'
    );
    $featuresA .= $dyn('stat-strip', ['stats' => wp_json_encode([
        ['value' => '99.99%', 'label' => 'Uptime SLA'],
        ['value' => '<150ms', 'label' => 'Median API response'],
        ['value' => 'SOC 2', 'label' => 'Type II certified'],
        ['value' => '40+', 'label' => 'Native integrations'],
    ]), 'columns' => 4]);
    $featuresA .= $darkCta('Ready when you are', 'See it running on your own data.', 'Book a 20-minute walkthrough — no pressure, no slideware. We\'ll show [Your Product] solving your actual workflow.', [['text' => 'Book a demo', 'url' => '/contact/'], ['text' => 'View pricing', 'url' => '/pricing/', 'outline' => true]]);
    $patterns['prt-site/saas-features-a'] = [
        'title'       => __('SaaS Features — Icon grid (corporate)', 'pressroot'),
        'description' => __('Dark hero + 6-card icon grid of product capabilities, confident enterprise-SaaS tone, stat strip and demo CTA.', 'pressroot'),
        'content'     => $featuresA,
    ];

    /* ═══════════════════════════════════════════════════════════════════
     * FEATURES — Variant B: asymmetric bento, punchier one-liners
     * ═══════════════════════════════════════════════════════════════════ */
    $bentoTiles = [
        ['span' => '2', 'title' => 'Real-time collaboration', 'body' => 'Your team, in sync, always. Every edit lands instantly for everyone in the room.', 'accent' => '#6C4CF1'],
        ['span' => '1', 'title' => 'One-click integrations', 'body' => 'Slack, HubSpot, Salesforce. Connected in seconds, not sprints.', 'accent' => '#22CFEE'],
        ['span' => '1', 'title' => 'Enterprise-grade security', 'body' => 'SSO. SCIM. SOC 2 Type II. Ship to security review with confidence.', 'accent' => '#FF7A3D'],
        ['span' => '2', 'title' => 'Usage analytics dashboard', 'body' => 'Know what\'s working before your customers tell you. Live adoption and engagement data, zero setup.', 'accent' => '#FF4D9D'],
        ['span' => '1', 'title' => 'Custom workflow builder', 'body' => 'No code. No tickets. Just drag, drop, done.', 'accent' => '#37E29A'],
        ['span' => '2', 'title' => 'Global-scale infrastructure', 'body' => '99.99% uptime, multi-region by default. Built to disappear into the background.', 'accent' => '#6C4CF1'],
    ];
    $bentoHtml = '';
    foreach ($bentoTiles as $t) {
        $bentoHtml .= '<div class="prt-lift" style="grid-column:span ' . $t['span'] . '; background:#1F1B33; border:1px solid #2E2850; border-radius:22px; padding:28px; position:relative; overflow:hidden;">'
            . '<div style="position:absolute; top:0; left:0; width:5px; height:100%; background:' . $t['accent'] . ';"></div>'
            . '<h3 style="font-family:var(--font-display); font-weight:800; font-size:21px; color:#fff; margin:0 0 10px;">' . $t['title'] . '</h3>'
            . '<p style="font-size:15px; line-height:1.55; color:#B9B4D9; margin:0;">' . $t['body'] . '</p></div>';
    }
    $bentoGrid = "<!-- wp:html -->\n"
        . '<div style="display:grid; grid-template-columns:repeat(3,1fr); gap:18px; margin-top:8px;">'
        . $bentoHtml . '</div>' . "\n<!-- /wp:html -->";

    $screenshotCallout = "<!-- wp:html -->\n"
        . '<div style="background:linear-gradient(160deg,#1F1B33,#14111F); border:1px solid #2E2850; border-radius:26px; padding:8px; margin-top:10px; box-shadow:0 30px 70px rgba(0,0,0,.45);">'
        . '<div style="background:#0F0D18; border-radius:18px; padding:60px 40px; display:flex; flex-direction:column; align-items:center; justify-content:center; gap:14px; border:1px dashed #3A3460;">'
        . '<span style="font-family:var(--font-mono); font-size:13px; color:#7C75A8;">[ product-screenshot-placeholder.png ]</span>'
        . '<span style="font-family:var(--font-mono); font-size:12px; color:#5A5676;">Drop a real product screenshot or screen recording here</span>'
        . '</div></div>'
        . "\n<!-- /wp:html -->";

    $featuresB  = $darkHero(
        $eyebrow('Features')
        . $hDark(1, 'One product. Six superpowers.', 'huge')
        . $pDark('Stop duct-taping five tools together. [Your Product] gives your whole team one fast, secure home base — and it just works.', 'large')
        . $buttons([['text' => 'Try it free', 'url' => '/contact/'], ['text' => 'See a live demo', 'url' => '/contact/', 'outline' => true]])
    );
    $featuresB .= $wrap($screenshotCallout, '50px', '10px');
    $featuresB .= $wrap($h(2, "What's inside.", 'x-large') . $p('No add-on pricing tiers, no "contact sales" for the basics. Everything below ships on every plan.', 'medium', 'muted'), '50px', '10px');
    $featuresB .= $wrap($bentoGrid, '10px', '20px');
    $featuresB .= $darkCta('Built by developers', 'Docs, APIs, and a changelog you\'ll actually want to read.', 'Every feature above has a public API, real docs, and a status page. Because good software respects your time.', [['text' => 'Read the docs', 'url' => '/resources/'], ['text' => 'Talk to sales', 'url' => '/contact/', 'outline' => true]]);
    $patterns['prt-site/saas-features-b'] = [
        'title'       => __('SaaS Features — Bento + screenshot (punchy)', 'pressroot'),
        'description' => __('Dark hero with product-screenshot-placeholder callout, asymmetric bento grid, punchier one-liner copy, monospace developer-credibility CTA.', 'pressroot'),
        'content'     => $featuresB,
    ];

    /* ═══════════════════════════════════════════════════════════════════
     * PRICING — Variant A: classic 3-tier cards (Starter / Pro / Enterprise)
     * ═══════════════════════════════════════════════════════════════════ */
    $tiersA = '';
    foreach ([
        ['Starter', '$0', '/month, forever', 'For solo builders trying things out.', ['Up to 3 team members', 'Core collaboration tools', '1 integration', 'Community support'], false],
        ['Pro', '$29', '/user / month', 'For growing teams that need to move fast.', ['Unlimited team members', 'All integrations', 'Advanced analytics dashboard', 'Custom workflow builder', 'Priority email support'], true],
        ['Enterprise', 'Custom', 'annual billing', 'For orgs with security & scale requirements.', ['SSO & SCIM provisioning', 'Dedicated infrastructure', 'Custom contracts & SLA', '24/7 support & onboarding'], false],
    ] as $t) {
        [$name, $price, $sub, $desc, $feats, $featured] = $t;
        $cardStyle = $featured
            ? 'background:linear-gradient(160deg,#6C4CF1,#5B3FE0); color:#fff; box-shadow:0 26px 55px rgba(124,92,255,.38); border:1px solid rgba(255,255,255,.15);'
            : 'background:#1F1B33; color:#fff; border:1px solid #2E2850;';
        $featList = '';
        foreach ($feats as $f) {
            $featList .= '<span style="display:flex; gap:8px; align-items:flex-start; margin-bottom:10px;"><span style="opacity:.85;">✓</span><span>' . $f . '</span></span>';
        }
        $tiersA .= '<div class="prt-lift" style="' . $cardStyle . ' border-radius:26px; padding:36px; position:relative;">'
            . ($featured ? '<span style="position:absolute; top:-13px; left:50%; transform:translateX(-50%); background:#37E29A; color:#17151F; padding:6px 16px; border-radius:999px; font-size:12px; font-weight:800; font-family:var(--font-display);">MOST POPULAR</span>' : '')
            . '<h3 style="font-family:var(--font-display); font-weight:700; font-size:22px; margin:0 0 6px;">' . $name . '</h3>'
            . '<p style="font-size:14.5px; opacity:.8; margin:0 0 22px;">' . $desc . '</p>'
            . '<div style="font-family:var(--font-display); font-weight:900; font-size:46px; letter-spacing:-.03em;">' . $price . '</div>'
            . '<div style="font-size:13px; opacity:.75; margin-bottom:24px;">' . $sub . '</div>'
            . '<div style="font-size:14.5px; line-height:1.4; margin-bottom:24px;">' . $featList . '</div>'
            . '<div><a href="/contact/" class="prt-lift" style="display:block; text-align:center; text-decoration:none; padding:14px 20px; border-radius:999px; font-weight:700; font-size:15px; font-family:var(--font-display); ' . ($featured ? 'background:#fff; color:#17151F;' : 'background:transparent; border:1.5px solid #fff; color:#fff;') . '">' . ($name === 'Enterprise' ? 'Talk to sales' : 'Get started') . '</a></div>'
            . '</div>';
    }
    $pricingA  = $darkHero(
        $eyebrow('Pricing')
        . $hDark(1, 'Simple pricing that scales with you.', 'huge')
        . $pDark('Start free. Upgrade when your team grows. No hidden fees, no surprise invoices — cancel any time.', 'large')
    );
    $pricingA .= $wrap('<!-- wp:heading {"level":2,"className":"screen-reader-text"} --><h2 class="screen-reader-text">Plans</h2><!-- /wp:heading -->', '50px', '0px') . $cardGrid('3', $tiersA);
    $pricingA .= $wrap($h(2, 'Frequently asked questions', 'x-large'), '60px', '10px');
    $pricingA .= $wrap(
        "<!-- wp:html -->\n"
        . '<div style="max-width:840px; margin:0 auto;">'
        . '<details style="background:#1F1B33; border:1px solid #2E2850; border-radius:18px; padding:22px 24px; margin-bottom:14px;"><summary style="cursor:pointer; font-family:var(--font-display); font-weight:700; font-size:18px; color:#fff;">Can I change plans later?</summary><p style="font-size:15.5px; color:#B9B4D9; line-height:1.6; margin:14px 0 0;">Yes — upgrade, downgrade, or cancel at any time from your billing settings. Changes are prorated automatically.</p></details>'
        . '<details style="background:#1F1B33; border:1px solid #2E2850; border-radius:18px; padding:22px 24px; margin-bottom:14px;"><summary style="cursor:pointer; font-family:var(--font-display); font-weight:700; font-size:18px; color:#fff;">Is there a free trial on Pro?</summary><p style="font-size:15.5px; color:#B9B4D9; line-height:1.6; margin:14px 0 0;">Every Pro plan includes a 14-day free trial — no credit card required to start.</p></details>'
        . '<details style="background:#1F1B33; border:1px solid #2E2850; border-radius:18px; padding:22px 24px; margin-bottom:14px;"><summary style="cursor:pointer; font-family:var(--font-display); font-weight:700; font-size:18px; color:#fff;">What does Enterprise include?</summary><p style="font-size:15.5px; color:#B9B4D9; line-height:1.6; margin:14px 0 0;">Custom contracts, dedicated infrastructure, SSO/SCIM, and a named support team with an SLA built around your rollout.</p></details>'
        . '</div>' . "\n<!-- /wp:html -->",
        '10px', '20px'
    );
    $pricingA .= $darkCta('Still deciding?', "Talk to our team — we'll help you pick the right plan.", 'No pressure, no scripted pitch. Just a straight answer about what fits your team\'s size and stage.', [['text' => 'Talk to sales', 'url' => '/contact/'], ['text' => 'Compare features', 'url' => '/features/', 'outline' => true]]);
    $patterns['prt-site/saas-pricing-a'] = [
        'title'       => __('SaaS Pricing — 3-tier cards (Free/Pro/Enterprise)', 'pressroot'),
        'description' => __('Dark hero, classic 3-column pricing cards with a "Most popular" middle tier, FAQ accordion, and a talk-to-sales CTA.', 'pressroot'),
        'content'     => $pricingA,
    ];

    /* ═══════════════════════════════════════════════════════════════════
     * PRICING — Variant B: horizontal comparison-style tiers, punchier copy
     * ═══════════════════════════════════════════════════════════════════ */
    $tiersB = [
        ['Starter', 'Free', 'Kick the tires.', ['3 seats', 'Core features', 'Community support'], false, '#22CFEE'],
        ['Growth', '$49', 'Built to scale with you.', ['Unlimited seats', 'Every integration', 'Analytics + workflow builder', 'Priority support'], true, '#6C4CF1'],
        ['Scale', "Let's talk", 'For teams who need it locked down.', ['SSO/SCIM + custom SLA', 'Dedicated infra & support', 'Security review, handled'], false, '#FF7A3D'],
    ];
    $tiersBHtml = '';
    foreach ($tiersB as $t) {
        [$name, $price, $tag, $feats, $featured, $accent] = $t;
        $featList = '';
        foreach ($feats as $f) {
            $featList .= '<span style="display:flex; gap:8px; align-items:flex-start; margin-bottom:9px; font-size:14px;"><span style="color:' . $accent . ';">→</span><span>' . $f . '</span></span>';
        }
        $tiersBHtml .= '<div class="prt-lift" style="background:#181428; border:1px solid ' . ($featured ? $accent : '#2E2850') . '; border-radius:24px; padding:32px; ' . ($featured ? 'box-shadow:0 0 0 1px ' . $accent . ', 0 24px 60px rgba(124,92,255,.28);' : '') . '">'
            . '<div style="display:flex; align-items:center; justify-content:space-between; margin-bottom:6px;">'
            . '<span style="font-family:var(--font-mono); font-size:12px; letter-spacing:.05em; text-transform:uppercase; color:' . $accent . ';">' . $name . '</span>'
            . ($featured ? '<span style="background:' . $accent . '; color:#14111F; padding:4px 12px; border-radius:999px; font-size:11px; font-weight:800; font-family:var(--font-display);">MOST POPULAR</span>' : '')
            . '</div>'
            . '<div style="font-family:var(--font-display); font-weight:900; font-size:44px; letter-spacing:-.03em; color:#fff; margin-bottom:4px;">' . $price . ($price !== "Let's talk" && $price !== 'Free' ? '<span style="font-size:16px; font-weight:600; opacity:.6;">/mo</span>' : '') . '</div>'
            . '<p style="font-size:14.5px; color:#B9B4D9; margin:0 0 20px;">' . $tag . '</p>'
            . '<div style="color:#EDEAFB; margin-bottom:22px;">' . $featList . '</div>'
            . '<a href="/contact/" class="prt-lift" style="display:block; text-align:center; text-decoration:none; padding:14px 20px; border-radius:999px; font-weight:700; font-size:15px; font-family:var(--font-display); background:' . $accent . '; color:#14111F;">' . ($name === 'Scale' ? 'Book a call' : 'Get started free') . '</a>'
            . '</div>';
    }
    $pricingB  = $darkHero(
        $eyebrow('Pricing')
        . $hDark(1, 'Pay for what you use. Nothing you don\'t.', 'huge')
        . $pDark('Three plans. No asterisks. Switch or cancel whenever — your data always stays yours.', 'large')
        . $buttons([['text' => 'Compare plans below', 'url' => '#plans']])
    );
    $pricingB .= $wrap(
        '<!-- wp:html --><div id="plans" style="font-family:var(--font-mono); font-size:13px; color:#7C75A8; text-align:center; margin-bottom:8px;">// no credit card required to start</div><!-- /wp:html -->'
        . $cardGrid('3', $tiersBHtml),
        '50px', '10px'
    );
    $pricingB .= $wrap(
        $h(2, 'What "unlimited" actually means', 'x-large')
        . $p('No per-seat surprise fees, no feature paywalls buried three clicks deep. If it\'s on the plan, it\'s in the product — day one.', 'medium', 'muted'),
        '50px', '20px'
    );
    $pricingB .= $darkCta('Not sure which plan fits?', 'Tell us your team size — we\'ll tell you the right tier in five minutes.', 'No forms to fill out twice. Just a quick, honest conversation about what you actually need.', [['text' => 'Book a demo', 'url' => '/contact/']]);
    $patterns['prt-site/saas-pricing-b'] = [
        'title'       => __('SaaS Pricing — Comparison rail (punchy)', 'pressroot'),
        'description' => __('Dark hero with jump-link CTA, monospace-accented 3-tier comparison cards with arrow-bullet checklists, terser copy angle.', 'pressroot'),
        'content'     => $pricingB,
    ];

    /* ═══════════════════════════════════════════════════════════════════
     * CONTACT / TALK TO SALES — Variant A: enterprise form + trust signals
     * ═══════════════════════════════════════════════════════════════════ */
    $formA = "<!-- wp:html -->\n"
        . '<div style="background:#1F1B33; border:1px solid #2E2850; border-radius:26px; padding:40px; max-width:640px; margin:0 auto;">'
        . '<form>'
        . '<div style="display:grid; grid-template-columns:1fr 1fr; gap:16px; margin-bottom:16px;">'
        . '<label style="font-size:13px; color:#B9B4D9;">Work email<input type="email" placeholder="you@company.com" style="display:block; width:100%; margin-top:6px; padding:12px 14px; border-radius:10px; border:1px solid #2E2850; background:#14111F; color:#fff;"></label>'
        . '<label style="font-size:13px; color:#B9B4D9;">Company<input type="text" placeholder="Acme Inc." style="display:block; width:100%; margin-top:6px; padding:12px 14px; border-radius:10px; border:1px solid #2E2850; background:#14111F; color:#fff;"></label>'
        . '</div>'
        . '<label style="font-size:13px; color:#B9B4D9; display:block; margin-bottom:16px;">Team size<select style="display:block; width:100%; margin-top:6px; padding:12px 14px; border-radius:10px; border:1px solid #2E2850; background:#14111F; color:#fff;"><option>1–10</option><option>11–50</option><option>51–200</option><option>200+</option></select></label>'
        . '<label style="font-size:13px; color:#B9B4D9; display:block; margin-bottom:20px;">What are you hoping to solve?<textarea rows="4" placeholder="Tell us a bit about your use case…" style="display:block; width:100%; margin-top:6px; padding:12px 14px; border-radius:10px; border:1px solid #2E2850; background:#14111F; color:#fff; resize:vertical;"></textarea></label>'
        . '<button type="submit" class="prt-lift" style="width:100%; padding:15px 20px; border-radius:999px; border:none; font-weight:700; font-size:16px; font-family:var(--font-display); background:#6C4CF1; color:#fff; cursor:pointer;">Request a demo →</button>'
        . '</form></div>'
        . "\n<!-- /wp:html -->";
    $trustLogosA = "<!-- wp:html -->\n"
        . '<div style="display:flex; justify-content:center; gap:40px; flex-wrap:wrap; opacity:.6; font-family:var(--font-mono); font-size:13px; letter-spacing:.05em; text-transform:uppercase; color:#7C75A8;">'
        . '<span>[ Logo ]</span><span>[ Logo ]</span><span>[ Logo ]</span><span>[ Logo ]</span><span>[ Logo ]</span>'
        . '</div>' . "\n<!-- /wp:html -->";
    $contactA  = $darkHero(
        $eyebrow('Talk to sales')
        . $hDark(1, "Let's find the right fit for your team.", 'huge')
        . $pDark('Book a personalized walkthrough of [Your Product] with a product specialist — see how it maps to your workflow, security requirements, and rollout timeline.', 'large')
    );
    $contactA .= $wrap($formA, '50px', '10px');
    $contactA .= $wrap($p('Trusted by teams at', 'small', 'muted') . $trustLogosA, '40px', '20px');
    $contactA .= $dyn('stat-strip', ['stats' => wp_json_encode([
        ['value' => '<24 hrs', 'label' => 'Average response time'],
        ['value' => '20 min', 'label' => 'Typical first call'],
        ['value' => 'SOC 2', 'label' => 'Type II certified'],
    ]), 'columns' => 3]);
    $contactA .= $darkCta('Prefer to explore on your own first?', 'See detailed pricing and every feature before you talk to anyone.', 'No pressure — come back to sales whenever you\'re ready.', [['text' => 'View pricing', 'url' => '/pricing/'], ['text' => 'See all features', 'url' => '/features/', 'outline' => true]]);
    $patterns['prt-site/saas-contact-a'] = [
        'title'       => __('SaaS Contact — Enterprise demo form', 'pressroot'),
        'description' => __('Dark hero, full "request a demo" form card (work email, company, team size, use case), trust-logo strip, response-time stats.', 'pressroot'),
        'content'     => $contactA,
    ];

    /* ═══════════════════════════════════════════════════════════════════
     * CONTACT / TALK TO SALES — Variant B: split layout, direct booking CTA
     * ═══════════════════════════════════════════════════════════════════ */
    $reasonsB = '';
    foreach ([
        ['You\'re evaluating [Your Product] for 20+ seats', 'We\'ll walk through volume pricing and a rollout plan.'],
        ['Security or procurement needs sign-off', 'We\'ll get your team the SOC 2 report, DPA, and security answers up front.'],
        ['You need a migration from another tool', 'We\'ll scope a white-glove import so nothing gets lost switching over.'],
    ] as $r) {
        $reasonsB .= '<div style="display:flex; gap:14px; align-items:flex-start; margin-bottom:22px;">'
            . '<span style="flex:0 0 auto; width:28px; height:28px; border-radius:50%; background:#6C4CF1; color:#fff; display:flex; align-items:center; justify-content:center; font-family:var(--font-display); font-weight:800; font-size:14px;">✓</span>'
            . '<div><div style="font-family:var(--font-display); font-weight:700; font-size:16.5px; color:#fff; margin-bottom:4px;">' . $r[0] . '</div>'
            . '<div style="font-size:14.5px; color:#B9B4D9; line-height:1.5;">' . $r[1] . '</div></div></div>';
    }
    $bookingCard = "<!-- wp:html -->\n"
        . '<div style="background:linear-gradient(160deg,#242038,#14111F); border:1px solid #2E2850; border-radius:26px; padding:36px; text-align:center;">'
        . '<div style="font-family:var(--font-mono); font-size:12px; color:#7C75A8; letter-spacing:.05em; text-transform:uppercase; margin-bottom:14px;">// 20-minute call, zero pressure</div>'
        . '<h3 style="font-family:var(--font-display); font-weight:800; font-size:24px; color:#fff; margin:0 0 10px;">Pick a time that works.</h3>'
        . '<p style="font-size:14.5px; color:#B9B4D9; margin:0 0 24px; line-height:1.55;">You\'ll talk to a real person on the product team — not a script. Bring your questions.</p>'
        . '<a href="/contact/" class="prt-lift" style="display:inline-block; text-decoration:none; padding:16px 30px; border-radius:999px; font-weight:700; font-size:16px; font-family:var(--font-display); background:linear-gradient(135deg,#6C4CF1 0%,#FF4D9D 55%,#FF7A3D 100%); color:#fff;">Book a demo →</a>'
        . '<div style="margin-top:18px; font-size:13px; color:#5A5676;">or email <span style="color:#B9B4D9;">sales@example.com</span></div>'
        . '</div>' . "\n<!-- /wp:html -->";

    $contactB  = $darkHero(
        $eyebrow('Talk to sales')
        . $hDark(1, "You've got questions. We've got a person for that.", 'huge')
        . $pDark('Skip the ticket queue. Book time directly with someone who can answer pricing, security, and rollout questions in one call.', 'large')
    );
    $contactSplit = '<!-- wp:columns {"isStackedOnMobile":true,"verticalAlignment":"top"} -->'
        . '<div class="wp-block-columns are-vertically-aligned-top">'
        . '<!-- wp:column {"width":"55%"} --><div class="wp-block-column" style="flex-basis:55%">'
        . $h(2, "When it's worth a conversation", 'x-large')
        . "<!-- wp:html -->\n<div style=\"margin-top:18px;\">" . $reasonsB . '</div>' . "\n<!-- /wp:html -->"
        . '</div><!-- /wp:column -->'
        . '<!-- wp:column {"width":"45%"} --><div class="wp-block-column" style="flex-basis:45%">'
        . $bookingCard
        . '</div><!-- /wp:column -->'
        . '</div><!-- /wp:columns -->';
    $contactB .= $wrap($contactSplit, '60px', '20px');
    $contactB .= $darkCta('Not ready to book yet?', 'Explore pricing and every feature on your own time.', 'Come back and grab a slot whenever you\'re ready — the calendar link above never expires.', [['text' => 'View pricing', 'url' => '/pricing/'], ['text' => 'See all features', 'url' => '/features/', 'outline' => true]]);
    $patterns['prt-site/saas-contact-b'] = [
        'title'       => __('SaaS Contact — Split reasons + booking card', 'pressroot'),
        'description' => __('Dark hero, two-column layout: "when it\'s worth a conversation" checklist left, direct demo-booking card right — terser, more direct tone than variant A.', 'pressroot'),
        'content'     => $contactB,
    ];

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
