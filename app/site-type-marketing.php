<?php

/**
 * Dedicated "Marketing / Landing page" site-type patterns — Appearance ->
 * Pressroot AI.
 *
 * The Pressroot AI (app/ai-assistant.php) lets a theme owner pick a
 * site type and get starter pages pre-filled with a full-page block pattern.
 * Until now the "marketing" profile reused the same generic, personal-
 * freelancer-voiced patterns from page-patterns.php (pressroot/home-full,
 * pressroot/contact-full) — built for a multi-page brochure site, not a
 * single-focus conversion page.
 *
 * This file gives the "Marketing / Landing page" profile its OWN tailored
 * patterns, written for a single product/service landing page built to
 * convert: one strong repeated CTA, heavy social proof, an FAQ, high
 * contrast. This profile is assigned the theme's "Mono Slate" Style Kit
 * (sharp, near-black, minimal), so the copy/layout here leans hard into
 * that — near-black bands, no soft gradient blobs, blunt confident copy.
 *
 * TWO layout+copy variants per page so the "Regenerate" control in the
 * admin UI has something meaningfully different to swap to. Registered
 * under the shared 'prt-site-types' pattern category (registered centrally
 * elsewhere — this file only tags patterns with it, it does not call
 * register_block_pattern_category() itself).
 *
 * Slugs:
 *   prt-site/marketing-home-a    / -b  — full one-page landing site (Home)
 *   prt-site/marketing-contact-a / -b  — lead-capture Contact page
 *
 * ai-assistant.php's prt_site_types() 'marketing' profile can point its
 * 'pattern' keys at these slugs instead of the generic pressroot/* ones;
 * this file only registers the patterns, it doesn't wire that mapping.
 */

namespace App;

add_action('init', function () {

    /* ── Block-markup helpers (duplicated per-file by convention — see
     * page-patterns.php / site-type-agency.php for the identical originals) ── */

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

    /* ── Marketing-specific helpers ─────────────────────────────────── */

    // Near-black full-bleed band — the "Mono Slate" signature section wrapper,
    // used for the sticky-feeling repeated CTA moments and dark proof bands.
    $darkBand = function (string $innerHtml, string $pt = '64px', string $pb = '64px'): string {
        return "<!-- wp:html -->\n"
            . '<section style="background:#0b0c0e; padding:' . $pt . ' 32px ' . $pb . ';">'
            . '<div class="prt-wrap" style="max-width:1240px; margin:0 auto; padding:0;">'
            . $innerHtml
            . '</div></section>'
            . "\n<!-- /wp:html -->";
    };

    // Logo-cloud style social proof strip — plain-text "logos" (no fake image
    // assets) set in mono/caps to read as a trust band without inventing brands.
    $logoCloud = function (array $names): string {
        $items = '';
        foreach ($names as $n) {
            $items .= '<span style="font-family:var(--font-mono); font-weight:600; font-size:14px; letter-spacing:.06em; text-transform:uppercase; color:#7C75A8; white-space:nowrap;">' . esc_html($n) . '</span>';
        }
        return "<!-- wp:html -->\n"
            . '<div style="display:flex; flex-wrap:wrap; gap:32px 44px; align-items:center; justify-content:center; padding:28px 0;">'
            . $items . '</div>'
            . "\n<!-- /wp:html -->";
    };

    // Stat-strip style trust band (numbers, not logos) — the alternate proof
    // format used by variant B so the two home variants don't just re-skin
    // the same logo row.
    $statStrip = function (array $stats): string {
        $items = '';
        foreach ($stats as $s) {
            $items .= '<div style="text-align:center; padding:12px;">'
                . '<div style="font-family:var(--font-display); font-weight:800; font-size:clamp(28px,3.4vw,40px); color:#fff; letter-spacing:-.02em;">' . esc_html($s[0]) . '</div>'
                . '<div style="font-family:var(--font-mono); font-size:12.5px; color:#9a9aa2; margin-top:6px; letter-spacing:.02em;">' . esc_html($s[1]) . '</div>'
                . '</div>';
        }
        return "<!-- wp:html -->\n"
            . '<div style="display:grid; grid-template-columns:repeat(' . count($stats) . ',1fr); gap:12px;">'
            . $items . '</div>'
            . "\n<!-- /wp:html -->";
    };

    // Feature highlight cards — sharp square corners (not the rounded/soft
    // agency style) to keep the Mono Slate "no-nonsense" edge.
    $featureCards = function (array $feats): string {
        $out = '';
        foreach ($feats as $f) {
            $out .= '<div class="prt-lift prt-spec-card" style="background:#fff; border:1.5px solid #ECE6FB; border-radius:18px; padding:30px;">'
                . '<div style="font-size:26px; margin-bottom:14px;">' . $f[0] . '</div>'
                . '<h3 style="font-family:var(--font-display); font-weight:700; font-size:19px; margin:0 0 10px; color:#0b0c0e;">' . esc_html($f[1]) . '</h3>'
                . '<p style="font-size:14.5px; line-height:1.6; margin:0; color:#4A4660;">' . esc_html($f[2]) . '</p></div>';
        }
        return "<!-- wp:html -->\n"
            . '<div style="display:grid; grid-template-columns:repeat(' . count($feats) . ',1fr); gap:18px; margin-top:8px;">'
            . $out . '</div>'
            . "\n<!-- /wp:html -->";
    };

    // Testimonial pull-quote cards — square corners, quote mark accent,
    // clearly-labeled placeholder attribution.
    $testimonialCards = function (array $quotes): string {
        $out = '';
        foreach ($quotes as $q) {
            $out .= '<div class="prt-spec-card" style="background:#fff; border:1.5px solid #ECE6FB; border-radius:18px; padding:28px;">'
                . '<div style="font-family:var(--font-display); font-size:34px; line-height:1; color:#0b0c0e; margin-bottom:6px;">&#8220;</div>'
                . '<p style="font-size:15.5px; line-height:1.65; color:#17151F; margin:0 0 16px;">' . esc_html($q[0]) . '</p>'
                . '<p style="font-family:var(--font-mono); font-size:12.5px; color:#7C75A8; margin:0;">' . esc_html($q[1]) . '</p></div>';
        }
        return "<!-- wp:html -->\n"
            . '<div style="display:grid; grid-template-columns:repeat(' . count($quotes) . ',1fr); gap:18px; margin-top:8px;">'
            . $out . '</div>'
            . "\n<!-- /wp:html -->";
    };

    // A single large pull-quote (variant B lede testimonial) — one strong
    // quote before the feature highlights, per the "problem/solution" framing.
    $bigPullQuote = function (string $quote, string $attribution): string {
        return "<!-- wp:html -->\n"
            . '<div style="max-width:820px; margin:0 auto; text-align:center;">'
            . '<div style="font-family:var(--font-display); font-size:56px; line-height:1; color:#fff; opacity:.35; margin-bottom:4px;">&#8220;</div>'
            . '<p style="font-family:var(--font-display); font-weight:600; font-size:clamp(22px,3vw,30px); line-height:1.35; color:#fff; margin:0 0 20px;">' . esc_html($quote) . '</p>'
            . '<p style="font-family:var(--font-mono); font-size:13px; color:#9a9aa2; margin:0;">' . esc_html($attribution) . '</p>'
            . '</div>'
            . "\n<!-- /wp:html -->";
    };

    $patterns = [];

    /* ═══════════════════════════════════════════════════════════════════
     * HOME — Variant A: bold benefit statement + logo-cloud social proof
     * up top, feature highlights, testimonial cards, FAQ, final CTA.
     * ═══════════════════════════════════════════════════════════════════ */
    $heroA = $hero(
        '[Your Product/Service]',
        'Get more done in half the time — starting today.',
        'The single tool your team needs to stop juggling spreadsheets, emails, and sticky notes. Set up in minutes. No credit card required to try it.',
        [['text' => 'Start free trial →', 'url' => '/contact/'], ['text' => 'See how it works', 'url' => '#how-it-works', 'outline' => true]]
    );

    $homeA  = $heroA;
    $homeA .= $darkBand(
        '<p style="text-align:center; font-family:var(--font-mono); font-size:12.5px; letter-spacing:.08em; text-transform:uppercase; color:#7C75A8; margin:0 0 4px;">Trusted by teams at</p>'
        . $logoCloud(['Northwind', 'Alder & Co.', 'Brightline', 'Kestrel Labs', 'Fernway', 'Cobalt Studio']),
        '36px', '36px'
    );
    $homeA .= $wrap(
        $h(2, 'Everything you need. Nothing you don\'t.', 'x-large')
        . $p('Built to solve the four things that actually slow teams down — not a hundred features nobody uses.', 'medium', 'muted'),
        '60px', '10px'
    );
    $homeA .= $wrap($featureCards([
        ['⚡', 'Set up in minutes, not weeks', 'No IT ticket, no onboarding call required. Connect your accounts and you\'re working inside your first session.'],
        ['📊', 'One dashboard, the full picture', 'Stop tab-switching between four tools to answer one question. Everything that matters lives in a single view.'],
        ['🔒', 'Enterprise-grade security, from day one', 'SOC 2-ready infrastructure, encrypted data at rest and in transit, and granular permissions — even on the free plan.'],
        ['🤝', 'Support from real people', 'Live chat with a real human, average reply time under 10 minutes during business hours — not a bot loop.'],
    ]), '10px', '30px');
    $homeA .= $wrap(
        $h(2, 'Don\'t take our word for it', 'x-large')
        . $p('A few examples of the kind of results teams see in their first month.', 'medium', 'muted'),
        '50px', '10px'
    );
    $homeA .= $wrap($testimonialCards([
        ['"This saved us 10 hours a week on reporting alone." — Dana R., Operations Manager, Example Co.', '— Dana R., Example Co.'],
        ['"We were live the same afternoon we signed up. Genuinely that easy." — Marcus T., Founder, Sample Studio', '— Marcus T., Sample Studio'],
        ['"Support answered a question in four minutes flat. Unheard of." — Priya S., Team Lead, Placeholder Inc.', '— Priya S., Placeholder Inc.'],
    ]), '10px', '30px');
    $homeA .= $wrap($h(2, 'Frequently asked questions', 'x-large'), '50px', '0px');
    $homeA .= $faq([
        ['How much does it cost?', 'Plans start on a free tier for individuals, with paid plans starting at a low monthly rate as your team grows. Every paid plan includes a 14-day free trial — no credit card needed to start.'],
        ['How does it actually work?', 'Connect your existing tools, invite your team, and you\'re set up in one guided session — most people are fully working inside it in under 15 minutes.'],
        ['How do I get started?', 'Click "Start free trial" above, create your account, and follow the 3-step setup checklist. No sales call required unless you want one.'],
        ['What kind of support do you offer?', 'Every plan includes live chat support with real people, plus a full help center. Paid plans add priority response times and onboarding assistance.'],
    ]);
    $homeA .= $dyn('cta-band', [
        'heading' => 'Ready to get more done?',
        'body'    => 'Join the teams already saving hours every week with [Your Product/Service]. Start free — upgrade only when you\'re ready.',
        'btnText' => 'Start free trial →',
        'btnUrl'  => '/contact/',
        'variant' => 'dark',
    ]);
    $patterns['prt-site/marketing-home-a'] = [
        'title'       => __('Marketing — Home (Benefit-first + logo cloud)', 'pressroot'),
        'description' => __('Full one-page landing site leading with a bold benefit headline and logo-cloud social proof, then feature highlights, testimonials, FAQ, and a final CTA.', 'pressroot'),
        'content'     => $homeA,
    ];

    /* ═══════════════════════════════════════════════════════════════════
     * HOME — Variant B: problem/solution framing + single strong
     * testimonial pull-quote before the feature highlights, stat-strip
     * proof instead of a logo cloud, FAQ, final CTA.
     * ═══════════════════════════════════════════════════════════════════ */
    $heroB = $hero(
        'There\'s a better way',
        'Still doing this the hard way?',
        '[Your Product/Service] replaces the spreadsheets, sticky notes, and back-and-forth emails with one simple system your whole team actually wants to use.',
        [['text' => 'Fix it now →', 'url' => '/contact/'], ['text' => 'Watch a 2-min demo', 'url' => '#how-it-works', 'outline' => true]]
    );

    $homeB  = $heroB;
    $homeB .= $darkBand($bigPullQuote(
        'We went from three separate tools and a shared spreadsheet to one clean workflow. Our team actually enjoys using it now.',
        '— Jordan K., Head of Ops, Example Co.'
    ), '56px', '56px');
    $homeB .= $wrap(
        $h(2, 'The old way was never going to scale', 'x-large')
        . $p('Manual tracking, scattered tools, and no single source of truth cost your team hours every week — and it only gets worse as you grow. Here\'s what changes.', 'medium', 'muted'),
        '60px', '10px'
    );
    $homeB .= $wrap($featureCards([
        ['⚡', 'Replace five tools with one', 'Stop stitching together spreadsheets, chat threads, and a project tool that doesn\'t quite fit. One system, one source of truth.'],
        ['📊', 'See problems before they\'re expensive', 'Real-time visibility means you catch a slipping deadline or a budget overrun while there\'s still time to fix it.'],
        ['🔒', 'Bank-level security, built in', 'Encrypted data, granular permissions, and audit logs — so security is never the reason a deal stalls.'],
        ['🤝', 'A team that answers', 'Real support from people who use the product, not a ticket queue. Most questions are answered same-day.'],
    ]), '10px', '30px');
    $homeB .= $darkBand(
        $h(2, 'Numbers our customers report', 'x-large')
        . $statStrip([['10 hrs', 'saved per team, per week'], ['48 hrs', 'average time to full rollout'], ['4.8/5', 'average customer rating'], ['<10 min', 'average support reply time']]),
        '50px', '50px'
    );
    $homeB .= $wrap($h(2, 'Frequently asked questions', 'x-large'), '50px', '0px');
    $homeB .= $faq([
        ['What does it cost to switch?', 'Plans start free for individuals and small teams, with paid tiers scaling as you grow. Every paid plan includes a 14-day trial, and we\'ll help you import your existing data at no extra charge.'],
        ['How long does it take to switch over?', 'Most teams are fully migrated and working in the new system within 48 hours, using our guided import tools and a short onboarding call.'],
        ['How do we get started?', 'Book a short setup call or jump straight into a free trial — either way, a real person checks in during your first week to make sure nothing falls through the cracks.'],
        ['What if my team gets stuck?', 'Live chat support is included on every plan, plus a searchable help center and short video walkthroughs for the most common workflows.'],
    ]);
    $homeB .= $dyn('cta-band', [
        'heading' => 'Stop working around the problem.',
        'body'    => 'Switch to [Your Product/Service] and give your team back the hours they\'re losing to manual work — starting this week.',
        'btnText' => 'Fix it now →',
        'btnUrl'  => '/contact/',
        'variant' => 'dark',
    ]);
    $patterns['prt-site/marketing-home-b'] = [
        'title'       => __('Marketing — Home (Problem/solution + pull-quote)', 'pressroot'),
        'description' => __('Full one-page landing site leading with problem/solution framing and a single strong testimonial pull-quote, then feature highlights, a stat-strip proof band, FAQ, and a final CTA.', 'pressroot'),
        'content'     => $homeB,
    ];

    /* ═══════════════════════════════════════════════════════════════════
     * CONTACT — Variant A: single-field lead-capture framing, direct tone.
     * ═══════════════════════════════════════════════════════════════════ */
    $contactA  = $hero(
        'Get started',
        'Get your free demo of [Your Product/Service].',
        'Tell us a little about your team and we\'ll show you exactly how it fits into your workflow — no generic sales pitch, just your use case.',
        [['text' => 'Request my demo →', 'url' => '#lead-form']]
    );
    $reasonsA = '';
    foreach ([
        ['⏱️', '15-minute walkthrough', 'A focused call built around your workflow, not a canned slide deck.'],
        ['🎯', 'No pressure, no pitch', 'You\'ll leave knowing exactly whether this is the right fit — even if the answer is no.'],
        ['📩', 'A reply within one business day', 'Real humans read every submission. You won\'t sit in a queue.'],
    ] as $r) {
        $reasonsA .= '<div class="prt-lift prt-spec-card" style="background:#fff; border:1.5px solid #ECE6FB; border-radius:18px; padding:26px; text-align:left;">'
            . '<div style="font-size:24px; margin-bottom:10px;">' . $r[0] . '</div>'
            . '<h3 style="font-family:var(--font-display); font-weight:700; font-size:17px; margin:0 0 8px; color:#0b0c0e;">' . $r[1] . '</h3>'
            . '<p style="font-size:14px; line-height:1.55; margin:0; color:#5A5676;">' . $r[2] . '</p></div>';
    }
    $contactA .= $wrap($cardGrid('3', $reasonsA), '10px', '30px');
    $contactA .= $dyn('cta-band', [
        'heading' => 'Prefer to just start a free trial?',
        'body'    => 'Skip the call — create an account and be working inside [Your Product/Service] in under five minutes.',
        'btnText' => 'Start free trial →',
        'btnUrl'  => '/contact/',
        'variant' => 'light',
    ]);
    $patterns['prt-site/marketing-contact-a'] = [
        'title'       => __('Marketing — Contact (Demo request, direct tone)', 'pressroot'),
        'description' => __('Single-purpose "request a demo" lead-capture page with a 3-reason trust grid — direct, no-pressure tone.', 'pressroot'),
        'content'     => $contactA,
    ];

    /* ═══════════════════════════════════════════════════════════════════
     * CONTACT — Variant B: dark-band split layout, urgency-forward tone.
     * ═══════════════════════════════════════════════════════════════════ */
    $splitContact = '<div style="display:grid; grid-template-columns:1.1fr 1fr; gap:20px; margin-top:8px; align-items:start;">'
        . '<div class="prt-lift" style="background:#0b0c0e; color:#fff; border-radius:4px; padding:40px;">'
        . '<div style="font-family:var(--font-mono); font-size:12px; color:#9a9aa2; letter-spacing:.06em; text-transform:uppercase; margin-bottom:16px;">Limited onboarding slots this month</div>'
        . '<h3 style="font-family:var(--font-display); font-weight:800; font-size:24px; margin:0 0 14px;">Claim your spot before your team falls further behind.</h3>'
        . '<p style="font-size:15.5px; line-height:1.6; opacity:.9; margin:0 0 20px;">Leave your details and we\'ll reach out within one business day to get you set up — no obligation, cancel anytime during the trial.</p>'
        . '<span style="display:inline-block; font-family:var(--font-display); font-weight:700; font-size:13px; background:#fff; color:#0b0c0e; padding:8px 16px; border-radius:999px;">Average reply time: under 10 minutes</span></div>'
        . '<div style="display:flex; flex-direction:column; gap:20px;">'
        . '<div class="prt-lift prt-spec-card" style="background:#fff; border:1.5px solid #ECE6FB; border-radius:18px; padding:26px;">'
        . '<h4 style="font-family:var(--font-display); font-weight:700; font-size:16px; margin:0 0 8px; color:#0b0c0e;">💬 Talk to a human first</h4>'
        . '<p style="font-size:14px; margin:0; color:#4A4660;">Not ready to commit? Ask questions via chat before you leave any details.</p></div>'
        . '<div class="prt-lift prt-spec-card" style="background:#fff; border:1.5px solid #ECE6FB; border-radius:18px; padding:26px;">'
        . '<h4 style="font-family:var(--font-display); font-weight:700; font-size:16px; margin:0 0 8px; color:#0b0c0e;">🔒 Your info stays private</h4>'
        . '<p style="font-size:14px; margin:0; color:#4A4660;">We never sell or share your details. Used only to follow up about your account.</p></div>'
        . '</div></div>';
    $contactB  = $hero(
        'Don\'t wait',
        'Your team is losing hours every week to the old way.',
        'Leave your details below and we\'ll show you exactly how [Your Product/Service] fixes it — fast, focused, and worth the five minutes.',
        [['text' => 'Claim my spot →', 'url' => '#lead-form']]
    );
    $contactB .= $wrap($splitContact, '10px', '30px');
    $contactB .= $dyn('cta-band', [
        'heading' => 'Still on the fence?',
        'body'    => 'Start a free trial instead — no call required, no commitment, cancel anytime.',
        'btnText' => 'Start free trial →',
        'btnUrl'  => '/contact/',
        'variant' => 'dark',
    ]);
    $patterns['prt-site/marketing-contact-b'] = [
        'title'       => __('Marketing — Contact (Urgency split, dark band)', 'pressroot'),
        'description' => __('Urgency-forward lead-capture page with a dark split layout and supporting trust cards — punchier, momentum-driven tone.', 'pressroot'),
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
