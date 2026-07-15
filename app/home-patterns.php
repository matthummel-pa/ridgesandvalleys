<?php

/**
 * Home section block patterns — Paper + Space design.
 *
 * Registers the nine homepage sections (also rendered by the
 * partials/home/* Blade files on front-page.blade.php) as insertable
 * Gutenberg patterns under the existing "pressroot" category, so they can be
 * dropped onto any page from the editor. Markup is static (wp:html); the live
 * homepage versions in partials/home/ stay dynamic (projects CPT, recent posts).
 */

namespace App;

// Registers the homepage section patterns and the assembled full-page
// pattern on 'init'. Priority 11 (before blocks-bespoke.php's 12) doesn't
// matter for these patterns since their markup is static wp:html, not
// prt/* block comments — pattern registration order is independent of
// whether the blocks they might reference are registered yet.
add_action('init', function () {

    // Per-page pattern categories so the inserter groups patterns by page.
    foreach ([
        'prt-home'      => __('Pressroot · Home', 'pressroot'),
        'prt-about'     => __('Pressroot · About', 'pressroot'),
        'prt-resume'    => __('Pressroot · Résumé', 'pressroot'),
        'prt-resources' => __('Pressroot · Resources', 'pressroot'),
        'prt-contact'   => __('Pressroot · Contact', 'pressroot'),
        'prt-sections'  => __('Pressroot · Sections', 'pressroot'),
    ] as $catSlug => $catLabel) {
        register_block_pattern_category($catSlug, ['label' => $catLabel]);
    }

    $patterns = [];

    /* ── Hero ──────────────────────────────────────────────────────── */
    $patterns['pressroot/home-hero'] = [
        'title'   => __('Home — Hero', 'pressroot'),
        'keywords' => ['hero', 'home', 'intro', 'gradient'],
        'html'    => <<<'HTML'
<section style="position:relative; overflow:hidden; padding:70px 32px 80px; background:#FFF9F5;">
  <div style="position:absolute; top:-60px; left:-40px; width:340px; height:340px; background:radial-gradient(circle at 30% 30%,#6C4CF1,#22CFEE); filter:blur(10px); opacity:.32; animation:prt-drift 16s ease-in-out infinite;"></div>
  <div style="position:absolute; bottom:-80px; right:60px; width:300px; height:300px; background:radial-gradient(circle at 60% 40%,#FF7A3D,#FF4D9D); filter:blur(14px); opacity:.28; animation:prt-drift 20s ease-in-out infinite reverse;"></div>
  <div class="prt-wrap" style="position:relative; padding-left:0; padding-right:0;">
    <div class="prt-hero-grid" style="display:grid; grid-template-columns:1.3fr 0.9fr; gap:40px; align-items:center;">
      <div>
        <div style="display:inline-flex; align-items:center; gap:9px; background:#17151F; color:#37E29A; padding:9px 18px; border-radius:999px; font-size:13px; font-weight:700; margin-bottom:28px; font-family:var(--font-display);"><span style="width:8px; height:8px; border-radius:50%; background:#37E29A;"></span> Open to side projects · 2 slots</div>
        <h1 style="font-family:var(--font-display); font-weight:800; font-size:clamp(48px,7vw,88px); line-height:.96; letter-spacing:-.035em; margin:0 0 24px; color:#17151F;">Hi there! I'm Matt — I build<br><span class="prt-gradient-text">delightful</span> <span style="font-family:var(--font-serif); font-style:italic; font-weight:400;">things</span> for the web.</h1>
        <p style="font-family:var(--font-display); font-size:21px; line-height:1.5; max-width:30em; color:#4A4660; margin:0 0 34px;">Full-stack developer building fast, accessible WordPress &amp; Sage sites and Power Platform tools — from Gettysburg, PA.</p>
        <div style="display:flex; gap:14px; flex-wrap:wrap;">
          <a href="#work" class="prt-lift" style="text-decoration:none; background:linear-gradient(135deg,#6C4CF1 0%,#FF4D9D 55%,#FF7A3D 100%); color:#fff; padding:17px 30px; border-radius:999px; font-weight:700; font-size:16px; font-family:var(--font-display);">See my work →</a>
          <a href="/contact/" class="prt-lift" style="text-decoration:none; background:#fff; border:1.5px solid #ECE6FB; color:#6C4CF1; padding:17px 30px; border-radius:999px; font-weight:700; font-size:16px; font-family:var(--font-display);">Let's chat</a>
        </div>
      </div>
      <div class="prt-hero-art" style="position:relative; height:440px;">
        <div style="position:absolute; inset:0; margin:auto; width:300px; height:380px; border:2px solid #17151F; border-radius:28px; overflow:hidden; display:flex; align-items:flex-end; padding:18px; animation:prt-blob 12s ease-in-out infinite; background:repeating-linear-gradient(135deg,#EEE8FE 0 16px,#F3EEFE 16px 32px);"><span style="font-family:var(--font-mono); font-size:12px; color:#7C75A8;">[ portrait.jpg ]</span></div>
        <div style="position:absolute; top:8px; right:14px; background:#37E29A; color:#17151F; padding:12px 16px; border-radius:16px; font-weight:800; font-size:15px; transform:rotate(8deg); box-shadow:0 8px 22px rgba(23,21,31,.16); font-family:var(--font-display);">⚡ Ships fast</div>
        <div style="position:absolute; bottom:24px; left:0; background:#FF7A3D; color:#fff; padding:12px 16px; border-radius:16px; font-weight:800; font-size:15px; transform:rotate(-6deg); box-shadow:0 8px 22px rgba(23,21,31,.16); font-family:var(--font-display);">15+ yrs building</div>
      </div>
    </div>
  </div>
</section>
HTML,
    ];

    /* ── Marquee ───────────────────────────────────────────────────── */
    $patterns['pressroot/home-marquee'] = [
        'title'   => __('Home — Skills marquee', 'pressroot'),
        'keywords' => ['marquee', 'skills', 'scroll'],
        'html'    => <<<'HTML'
<section style="background:#17151F; color:#FFF9F5; overflow:hidden; white-space:nowrap; padding:18px 0; transform:rotate(-1.2deg) scale(1.04);">
  <div style="display:inline-block; animation:prt-marq 22s linear infinite; font-family:var(--font-display); font-weight:800; font-size:24px; letter-spacing:-.01em;"><span style="padding:0 22px;">WORDPRESS</span><span style="color:#37E29A; padding:0 14px;">✦</span><span style="padding:0 22px;">POWER PLATFORM</span><span style="color:#FF7A3D; padding:0 14px;">✦</span><span style="padding:0 22px;">SAGE 11</span><span style="color:#22CFEE; padding:0 14px;">✦</span><span style="padding:0 22px;">REACT</span><span style="color:#6C4CF1; padding:0 14px;">✦</span><span style="padding:0 22px;">SEO &amp; GROWTH</span><span style="color:#37E29A; padding:0 14px;">✦</span><span style="padding:0 22px;">ACCESSIBILITY</span><span style="color:#FF7A3D; padding:0 14px;">✦</span><span style="padding:0 22px;">WORDPRESS</span><span style="color:#37E29A; padding:0 14px;">✦</span><span style="padding:0 22px;">POWER PLATFORM</span><span style="color:#FF7A3D; padding:0 14px;">✦</span><span style="padding:0 22px;">SAGE 11</span><span style="color:#22CFEE; padding:0 14px;">✦</span><span style="padding:0 22px;">REACT</span><span style="color:#6C4CF1; padding:0 14px;">✦</span></div>
</section>
HTML,
    ];

    /* ── Services ──────────────────────────────────────────────────── */
    $patterns['pressroot/home-services'] = [
        'title'   => __('Home — Services (3 cards)', 'pressroot'),
        'keywords' => ['services', 'cards', 'what i do'],
        'html'    => <<<'HTML'
<section class="prt-wrap" style="padding-top:90px; padding-bottom:30px;">
  <div style="display:flex; align-items:baseline; justify-content:space-between; flex-wrap:wrap; gap:12px; margin-bottom:36px;">
    <h2 style="font-family:var(--font-display); font-weight:800; font-size:clamp(34px,4vw,46px); letter-spacing:-.025em; margin:0; color:#17151F;">What I do <span style="font-family:var(--font-serif); font-style:italic; font-weight:400; color:#6C4CF1;">well</span></h2>
    <span style="font-family:var(--font-mono); font-size:13px; color:#7C75A8;">(three things, done right)</span>
  </div>
  <div class="prt-grid-3" style="display:grid; grid-template-columns:repeat(3,1fr); gap:22px;">
    <div class="prt-lift" style="background:#6C4CF1; color:#fff; border-radius:26px; padding:32px;"><div style="font-size:38px;">🎨</div><h3 style="font-family:var(--font-display); font-weight:700; font-size:24px; margin:18px 0 10px; color:inherit;">Front-End</h3><p style="font-size:15.5px; line-height:1.55; opacity:.93; margin:0 0 18px;">Snappy, responsive, accessible interfaces people actually enjoy using.</p><div style="font-family:var(--font-mono); font-size:12px; opacity:.8;">React · Block themes · Tailwind</div></div>
    <div class="prt-lift" style="background:#FF7A3D; color:#17151F; border-radius:26px; padding:32px;"><div style="font-size:38px;">⚙️</div><h3 style="font-family:var(--font-display); font-weight:700; font-size:24px; margin:18px 0 10px; color:inherit;">Back-End &amp; Platforms</h3><p style="font-size:15.5px; line-height:1.55; opacity:.93; margin:0 0 18px;">Custom WordPress / Sage, clean APIs, and Power Platform automations that scale.</p><div style="font-family:var(--font-mono); font-size:12px; opacity:.8;">PHP · Power Apps · Dataverse</div></div>
    <div class="prt-lift" style="background:#37E29A; color:#17151F; border-radius:26px; padding:32px;"><div style="font-size:38px;">📈</div><h3 style="font-family:var(--font-display); font-weight:700; font-size:24px; margin:18px 0 10px; color:inherit;">SEO &amp; Growth</h3><p style="font-size:15.5px; line-height:1.55; opacity:.93; margin:0 0 18px;">Performance, technical SEO, and content systems that compound over time.</p><div style="font-family:var(--font-mono); font-size:12px; opacity:.8;">Core Web Vitals · Schema · Analytics</div></div>
  </div>
</section>
HTML,
    ];

    /* ── Why me ────────────────────────────────────────────────────── */
    $patterns['pressroot/home-why-me'] = [
        'title'   => __('Home — Why work with me', 'pressroot'),
        'keywords' => ['why', 'value', 'cards'],
        'html'    => <<<'HTML'
<section class="prt-wrap" style="padding-top:80px; padding-bottom:10px;">
  <div style="margin-bottom:36px;">
    <h2 style="font-family:var(--font-display); font-weight:800; font-size:clamp(34px,4vw,46px); letter-spacing:-.025em; margin:0 0 8px; color:#17151F;">Why work <span style="font-family:var(--font-serif); font-style:italic; font-weight:400; color:#6C4CF1;">with me</span></h2>
    <p style="margin:0; font-size:17px; color:#7C75A8; max-width:40em;">No giant agency, no account managers — just a senior developer who's been shipping for 15+ years and cares about the details.</p>
  </div>
  <div class="prt-grid-4" style="display:grid; grid-template-columns:repeat(4,1fr); gap:18px;">
    <div class="prt-spec-card" style="background:#fff; border:1.5px solid #ECE6FB; border-radius:24px; padding:28px;"><div style="font-family:var(--font-display); font-weight:900; font-size:42px; letter-spacing:-.03em; color:#6C4CF1; line-height:1;">15+</div><h3 style="font-family:var(--font-display); font-weight:700; font-size:19px; margin:14px 0 8px; color:#17151F;">Years of experience</h3><p style="font-size:14.5px; color:#5A5676; line-height:1.55; margin:0;">Building accessible websites and web apps across the full stack.</p></div>
    <div class="prt-spec-card" style="background:#fff; border:1.5px solid #ECE6FB; border-radius:24px; padding:28px;"><div style="font-size:38px;">🏢</div><h3 style="font-family:var(--font-display); font-weight:700; font-size:19px; margin:14px 0 8px; color:#17151F;">Senior MS consultant</h3><p style="font-size:14.5px; color:#5A5676; line-height:1.55; margin:0;">By day I architect Power Platform solutions at Saliense Consulting.</p></div>
    <div class="prt-spec-card" style="background:#fff; border:1.5px solid #ECE6FB; border-radius:24px; padding:28px;"><div style="font-size:38px;">♿</div><h3 style="font-family:var(--font-display); font-weight:700; font-size:19px; margin:14px 0 8px; color:#17151F;">Accessibility-first</h3><p style="font-size:14.5px; color:#5A5676; line-height:1.55; margin:0;">Every build meets WCAG / Section 508 — never bolted on at the end.</p></div>
    <div class="prt-spec-card" style="background:#fff; border:1.5px solid #ECE6FB; border-radius:24px; padding:28px;"><div style="font-size:38px;">🌐</div><h3 style="font-family:var(--font-display); font-weight:700; font-size:19px; margin:14px 0 8px; color:#17151F;">Open-source proof</h3><p style="font-size:14.5px; color:#5A5676; line-height:1.55; margin:0;">My code is public — read the theme, plugin &amp; app I ship in the open.</p></div>
  </div>
</section>
HTML,
    ];

    /* ── Risk reversal ─────────────────────────────────────────────── */
    $patterns['pressroot/home-risk-reversal'] = [
        'title'   => __('Home — Risk reversal panel', 'pressroot'),
        'keywords' => ['risk', 'reassurance', 'dark panel'],
        'html'    => <<<'HTML'
<section class="prt-wrap" style="padding-top:48px; padding-bottom:30px;">
  <div style="background:#17151F; color:#fff; border-radius:34px; padding:54px 48px; position:relative; overflow:hidden;">
    <div style="position:absolute; top:-40px; right:-30px; width:200px; height:200px; background:#6C4CF1; opacity:.5; border-radius:50%; filter:blur(20px);"></div>
    <div style="position:relative;">
      <div style="font-family:var(--font-mono); font-size:13px; color:#37E29A; letter-spacing:.1em; margin-bottom:14px;">NEW TO WORKING WITH ME?</div>
      <h2 style="font-family:var(--font-display); font-weight:800; font-size:40px; letter-spacing:-.025em; margin:0 0 36px; max-width:18em; color:#fff;">I make it easy &amp; low-risk to start.</h2>
      <div class="prt-grid-4" style="display:grid; grid-template-columns:repeat(4,1fr); gap:30px;">
        <div><div style="font-family:var(--font-display); font-weight:800; font-size:20px; color:#37E29A; margin-bottom:10px;">Start small</div><p style="font-size:15px; color:#CFCBE6; line-height:1.55; margin:0;">Begin with a paid audit or a single page. Scale up only once you're confident.</p></div>
        <div><div style="font-family:var(--font-display); font-weight:800; font-size:20px; color:#22CFEE; margin-bottom:10px;">Fixed-price quotes</div><p style="font-size:15px; color:#CFCBE6; line-height:1.55; margin:0;">Approve a clear scope and price before any work begins. No surprise invoices.</p></div>
        <div><div style="font-family:var(--font-display); font-weight:800; font-size:20px; color:#FF7A3D; margin-bottom:10px;">You own everything</div><p style="font-size:15px; color:#CFCBE6; line-height:1.55; margin:0;">Code, content &amp; accounts are yours from day one. No lock-in, ever.</p></div>
        <div><div style="font-family:var(--font-display); font-weight:800; font-size:20px; color:#37E29A; margin-bottom:10px;">Built to last</div><p style="font-size:15px; color:#CFCBE6; line-height:1.55; margin:0;">Clean, documented code the next developer will actually thank you for.</p></div>
      </div>
    </div>
  </div>
</section>
HTML,
    ];

    /* ── CTA ───────────────────────────────────────────────────────── */
    $patterns['pressroot/home-cta'] = [
        'title'   => __('Home — Closing CTA', 'pressroot'),
        'keywords' => ['cta', 'contact', 'call to action'],
        'html'    => <<<'HTML'
<section class="prt-wrap" style="margin:60px auto 90px;">
  <div style="position:relative; overflow:hidden; background:#6C4CF1; border-radius:34px; padding:80px 48px; text-align:center; color:#fff;">
    <div style="position:absolute; top:-40px; left:40px; width:140px; height:140px; background:#37E29A; border-radius:50%; opacity:.85;"></div>
    <div style="position:absolute; bottom:-50px; right:60px; width:170px; height:170px; background:#FF7A3D; opacity:.85; border-radius:50%;"></div>
    <div style="position:relative;">
      <h2 style="font-family:var(--font-display); font-weight:800; font-size:clamp(34px,6vw,56px); letter-spacing:-.03em; margin:0 0 16px; line-height:1; color:#fff;">Got a project in mind?</h2>
      <p style="font-size:20px; opacity:.92; margin:0 auto 30px; max-width:34em;">Let's make something fast, useful, and a little bit delightful. I reply within a day.</p>
      <a href="/contact/" class="prt-lift" style="display:inline-flex; text-decoration:none; background:linear-gradient(135deg,#6C4CF1 0%,#FF4D9D 55%,#FF7A3D 100%); color:#fff; padding:18px 36px; border-radius:999px; font-weight:700; font-size:17px; font-family:var(--font-display);">Start the conversation →</a>
    </div>
  </div>
</section>
HTML,
    ];

    // Individual home sections are kept as building blocks for the full-page
    // pattern below, but are NOT registered on their own (the inserter only
    // exposes the curated "— Full page" patterns).

    /* ── Full homepage — one-click block version of the designed front page ──
       Insert this into the Home page (Edit page → Patterns → Pressroot →
       "Home — Full page") to make the homepage fully editable in the block
       editor. Once the page has this content, front-page.blade.php renders it
       instead of the Blade partials.

       The Selected builds, Building-in-the-open and Latest-writing sections use
       the theme's DYNAMIC blocks (prt/post-grid + prt/repo-grid), so they stay
       live (projects CPT, GitHub, recent posts) even when edited visually. */

    // Small local "template helpers" so the full-page assembly below (which
    // interleaves static design sections with live dynamic blocks) reads as
    // a list of section calls instead of repeating wrapper markup each time.

    // Builds a static section heading (title + optional italic accent word +
    // optional subtext) as a wp:html block, matching the heading style used
    // by the static sections above so dynamic-block sections look identical.
    $header = function ($title, $sub = '', $accentWord = '', $accentColor = '#6C4CF1') {
        $h = $title;
        if ($accentWord !== '') {
            $h = str_replace($accentWord, '<span style="font-family:var(--font-serif); font-style:italic; font-weight:400; color:' . $accentColor . ';">' . $accentWord . '</span>', $title);
        }
        $subHtml = $sub !== '' ? '<p style="margin:0; font-size:16px; color:#7C75A8; max-width:42em;">' . $sub . '</p>' : '';
        return "<!-- wp:html -->\n"
            . '<div class="prt-wrap" style="padding-top:80px; padding-bottom:10px;">'
            . '<h2 style="font-family:var(--font-display); font-weight:800; font-size:clamp(34px,4vw,46px); letter-spacing:-.025em; margin:0 0 8px; color:#17151F;">' . $h . '</h2>'
            . $subHtml . '</div>' . "\n<!-- /wp:html -->\n\n";
    };

    // Wraps a real block-comment string (e.g. prt/post-grid, prt/repo-grid)
    // in a constrained-width group so live/dynamic blocks line up with the
    // static wp:html sections' max-width, since those dynamic blocks don't
    // carry the prt-wrap width constraint themselves.
    $dynamic = function ($blockComment) {
        return '<!-- wp:group {"className":"prt-wrap","layout":{"type":"constrained","contentSize":"1240px"}} -->'
            . '<div class="wp-block-group prt-wrap">' . $blockComment . '</div>'
            . '<!-- /wp:group -->' . "\n\n";
    };

    // Looks up a previously-defined section's raw HTML by pattern slug and
    // wraps it in a wp:html block comment, so each static section (hero,
    // marquee, services, etc.) only has to be written once above and can be
    // reused here by name when assembling the full page.
    $section = function ($slug) use ($patterns) {
        return isset($patterns[$slug]) ? "<!-- wp:html -->\n{$patterns[$slug]['html']}\n<!-- /wp:html -->\n\n" : '';
    };

    $full  = $section('pressroot/home-hero');
    $full .= $section('pressroot/home-marquee');
    $full .= $section('pressroot/home-services');

    // Selected builds → projects CPT via prt/post-grid
    $full .= $header('Selected builds', 'Real, open-source code you can read line by line — themes, plugins &amp; apps.');
    $full .= $dynamic('<!-- wp:prt/post-grid {"postType":"projects","count":4,"columns":2,"showExcerpt":true,"showDate":false} /-->');

    // Building in the open → GitHub repos via prt/repo-grid
    $full .= $header('Building in the open', '16 repositories · contributor to the Microsoft 365 PnP community.');
    $full .= $dynamic('<!-- wp:prt/repo-grid {"username":"matthummel-pa","count":3,"columns":3} /-->');

    $full .= $section('pressroot/home-why-me');
    $full .= $section('pressroot/home-risk-reversal');

    // Latest writing → recent posts via prt/post-grid
    $full .= $header('Latest writing');
    $full .= $dynamic('<!-- wp:prt/post-grid {"postType":"post","count":3,"columns":3,"showCategory":true} /-->');

    $full .= $section('pressroot/home-cta');

    register_block_pattern('pressroot/home-full', [
        'title'      => __('Home — Full page (dynamic)', 'pressroot'),
        'description' => __('Full homepage as editable blocks. Selected builds, repos and latest writing use the live dynamic blocks (projects CPT, GitHub, recent posts).', 'pressroot'),
        'categories' => ['pressroot'],
        'keywords'   => ['home', 'full', 'landing', 'front page', 'dynamic'],
        'blockTypes' => ['core/post-content'],
        'content'    => $full,
    ]);
}, 11);
