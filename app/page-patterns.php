<?php

/**
 * Full-page block patterns for the main pages.
 *
 * Provides ONE curated "— Full page" pattern per main page: Home (see
 * home-patterns.php), Services, Pricing, About, Contact, Blog, and Single Post.
 * Built from WordPress CORE blocks (headings, paragraphs, buttons, details) for
 * editable text + the theme's DYNAMIC blocks (prt/skills-grid, prt/post-grid,
 * prt/cta-band, …) for the live/structured sections.
 *
 * These full-page patterns sit ALONGSIDE the smaller section-level patterns
 * registered in block-patterns.php, patterns-extra.php, sections-library.php,
 * and blocks.php — all under the "pressroot" (or "MH ·") categories in the
 * inserter, so a site builder can start from a full page or assemble one from
 * individual sections. (A previous version of this file unregistered every
 * one of those section patterns on every page load — that's been removed; see
 * the note below the $keep array.)
 *
 * Insert from the editor: Patterns → Pressroot / Pressroot ·.
 */

namespace App;

add_action('init', function () {

    /* ── Block-markup helpers ───────────────────────────────────────── */

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

    // Pill buttons via a wp:html block (raw HTML never triggers block validation).
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

    // FAQ accordion as a wp:html block (native <details>; raw HTML avoids the
    // block-validation "Attempt recovery" that custom-styled core blocks trigger).
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

    // Raw-HTML card grid helper.
    $cardGrid = function (string $cols, string $cardsHtml): string {
        return "<!-- wp:html -->\n"
            . '<div class="prt-grid-' . $cols . '" style="display:grid; grid-template-columns:repeat(' . $cols . ',1fr); gap:20px; margin-top:8px;">'
            . $cardsHtml . '</div>' . "\n<!-- /wp:html -->";
    };

    $patterns = [];

    /* ════════════════════════════ SERVICES ════════════════════════════ */
    $svcSkills = wp_json_encode([
        ['title' => 'WordPress & Sage Development', 'body' => 'Custom Sage 11 themes with Blade, Tailwind & Vite, plus Gutenberg blocks, patterns, and headless builds your team can run.'],
        ['title' => 'Power Platform & Microsoft 365', 'body' => 'Power Apps, Power Automate, and Dataverse — turning manual, paper-based processes into reliable automation.'],
        ['title' => 'Accessibility, Performance & SEO', 'body' => 'WCAG / Section 508 compliance, Core Web Vitals tuning, and technical SEO. Fast, findable, and inclusive by default.'],
    ]);
    $svcProcess = '';
    foreach ([
        ['01', '#6C4CF1', '#fff', 'Discover', 'A quick call to understand your goals, audience, and constraints.'],
        ['02', '#FF7A3D', '#17151F', 'Plan', 'A clear scope, timeline, and fixed price — no surprises.'],
        ['03', '#22CFEE', '#06283a', 'Build', 'Weekly demos so you see progress and steer as we go.'],
        ['04', '#37E29A', '#17151F', 'Launch', "Smooth handoff, docs, and support so you're never stuck."],
    ] as $s) {
        $svcProcess .= '<div style="background:' . $s[1] . '; color:' . $s[2] . '; border-radius:22px; padding:28px;">'
            . '<div style="font-family:var(--font-mono); font-size:12px; opacity:.8; margin-bottom:14px;">STEP ' . $s[0] . '</div>'
            . '<h3 style="font-family:var(--font-display); font-weight:700; font-size:20px; margin:0 0 8px;">' . $s[3] . '</h3>'
            . '<p style="font-size:14px; line-height:1.5; margin:0; opacity:.92;">' . $s[4] . '</p></div>';
    }
    $services  = $hero('Services', 'How I can help you ship.',
        'Whether you need a brand-new site, a rescue mission, or an internal tool, here&#8217;s where I do my best work.',
        [['text' => 'See pricing', 'url' => '/pricing/'], ['text' => "Let's talk", 'url' => '/contact/', 'outline' => true]]);
    $services .= $wrap('<!-- wp:heading {"level":2,"className":"screen-reader-text"} --><h2 class="screen-reader-text">What I do</h2><!-- /wp:heading -->', '0px', '0px') . $dyn('skills-grid', ['cards' => $svcSkills, 'columns' => 3]);
    $services .= $wrap($h(2, "How we'll work together", 'x-large') . $cardGrid('4', $svcProcess), '40px', '20px');
    $services .= $dyn('cta-band', ['heading' => 'Have a project in mind?', 'body' => 'Power Platform apps, WordPress builds, and Microsoft 365 solutions — transparent, fixed-price scopes. Let\'s talk.', 'btnText' => 'Get in touch →', 'btnUrl' => '/contact/', 'variant' => 'dark']);
    $patterns['pressroot/services-full'] = ['title' => __('Services — Full page', 'pressroot'), 'content' => $services];

    /* ════════════════════════════ PRICING ═════════════════════════════ */
    $tiers = '';
    foreach ([
        ['Starter', '$2.5k', 'starting price', 'A polished landing page or small site.', ['Up to 5 pages', 'Responsive & accessible', 'Basic SEO setup', '2 weeks turnaround'], false],
        ['Studio', '$7k', 'starting price', 'A full custom WordPress build.', ['Custom block theme', 'WooCommerce ready', 'Performance & SEO', 'Editor training', '30 days support'], true],
        ['Platform', 'Custom', 'tailored quote', 'Power Platform tools & automation.', ['Power Apps & Automate', 'Dataverse modeling', 'Custom PCF controls', 'Integrations & docs'], false],
    ] as $t) {
        [$name, $price, $sub, $desc, $feats, $featured] = $t;
        $cardStyle = $featured
            ? 'background:#6C4CF1; color:#fff; box-shadow:0 24px 50px rgba(124,92,255,.32);'
            : 'background:#fff; color:#17151F; border:1.5px solid #ECE6FB;';
        $featList = '';
        foreach ($feats as $f) {
            $featList .= '<span style="display:block; margin-bottom:10px;">✓ ' . $f . '</span>';
        }
        $tiers .= '<div style="' . $cardStyle . ' border-radius:26px; padding:36px; position:relative;">'
            . ($featured ? '<span style="position:absolute; top:-13px; left:50%; transform:translateX(-50%); background:#37E29A; color:#17151F; padding:6px 16px; border-radius:999px; font-size:12px; font-weight:800;">MOST POPULAR</span>' : '')
            . '<h3 style="font-family:var(--font-display); font-weight:700; font-size:22px; margin:0 0 6px;">' . $name . '</h3>'
            . '<p style="font-size:14.5px; opacity:.8; margin:0 0 18px;">' . $desc . '</p>'
            . '<div style="font-family:var(--font-display); font-weight:900; font-size:46px; letter-spacing:-.03em;">' . $price . '</div>'
            . '<div style="font-size:13px; opacity:.75; margin-bottom:22px;">' . $sub . '</div>'
            . '<div style="font-size:15px; line-height:1.4;">' . $featList . '</div></div>';
    }
    $pricing  = $hero('Pricing', 'Simple, honest pricing.',
        'No hourly mysteries. Pick the engagement that fits, and we&#8217;ll scope it together.',
        [['text' => 'Start a project', 'url' => '/contact/']]);
    $pricing .= $wrap('<!-- wp:heading {"level":2,"className":"screen-reader-text"} --><h2 class="screen-reader-text">Plans</h2><!-- /wp:heading -->', '0px', '0px') . $cardGrid('3', $tiers);
    $pricing .= $wrap($h(2, 'Questions?', 'x-large'), '50px', '0px');
    $pricing .= $faq([
        ['How do you scope a project?', 'We start with a short discovery call, then I send a fixed-price proposal with a clear timeline and deliverables. You approve before any work begins.'],
        ['Can you take over an existing site?', 'Absolutely — rescues and migrations are some of my favorite work. I audit what you have and recommend the lightest path forward.'],
        ['What happens after launch?', 'Every project includes a support window, documentation, and editor training. Ongoing care is available via retainer.'],
        ['Who owns the code?', 'You do — code, content, and accounts are yours from day one. No lock-in, ever.'],
    ]);
    $pricing .= $dyn('cta-band', ['heading' => 'Ready to start?', 'body' => "Tell me about your project and I'll send a clear, fixed-price proposal.", 'btnText' => 'Get in touch →', 'btnUrl' => '/contact/', 'variant' => 'dark']);
    $patterns['pressroot/pricing-full'] = ['title' => __('Pricing — Full page', 'pressroot'), 'content' => $pricing];

    /* ════════════════════════════ ABOUT ═══════════════════════════════ */
    $aboutStats = wp_json_encode([
        ['value' => '15+', 'label' => 'Years building for the web'],
        ['value' => 'Front→Back', 'label' => 'Full-stack development'],
        ['value' => '2', 'label' => 'Core specialties: WordPress & Power Platform'],
        ['value' => '100%', 'label' => 'Accessibility-first'],
    ]);
    $aboutSkills = wp_json_encode([
        ['title' => 'Front-End Development', 'body' => 'HTML, CSS, JavaScript, and React. Responsive, accessible interfaces that look sharp and hold up over time.'],
        ['title' => 'Back-End Development', 'body' => 'PHP, Node.js, and APIs — the logic, data, and integrations that power a site behind the scenes.'],
        ['title' => 'Performance & Accessibility', 'body' => 'Strong Core Web Vitals and WCAG-minded builds, so every visitor gets a fast, usable experience.'],
    ]);
    $about  = $hero('About', 'About Matt Hummel — Full-Stack Web Developer',
        'Full-stack web developer in Gettysburg, PA with 15+ years building fast, accessible WordPress websites and Microsoft Power Platform solutions — from custom Power Apps and Power Automate flows to SharePoint.',
        [['text' => 'Get in touch', 'url' => '/contact/'], ['text' => 'Connect on LinkedIn', 'url' => 'https://www.linkedin.com/in/matt-hummel-pa/', 'outline' => true]]);
    $about .= $dyn('stat-strip', ['stats' => $aboutStats, 'columns' => 4]);
    $about .= $wrap($h(2, 'What I do', 'x-large') . $p('I work across the full stack — from the browser to the server — and sweat the details that make software feel fast and effortless.', 'medium', 'muted'), '40px', '8px');
    $about .= $dyn('skills-grid', ['cards' => $aboutSkills, 'columns' => 3]);
    $about .= $wrap($h(2, 'Latest from the blog', 'x-large'), '40px', '8px');
    $about .= $dyn('post-grid', ['postType' => 'post', 'count' => 3, 'columns' => 3, 'showCategory' => true]);
    $about .= $dyn('cta-band', ['heading' => 'Open to select side projects', 'body' => "I take on a small number of freelance and side projects. If you've got something interesting, I'd genuinely like to hear about it.", 'btnText' => 'Get in touch →', 'btnUrl' => '/contact/', 'variant' => 'dark']);
    $patterns['pressroot/about-full'] = ['title' => __('About — Full page', 'pressroot'), 'content' => $about];

    /* ════════════════════════════ CONTACT ═════════════════════════════ */
    $contact  = $hero('Contact', 'Let&#8217;s build something delightful.',
        'Tell me a little about your project and I&#8217;ll reply within a day. Fixed-price quotes, no surprises. Based in Gettysburg, PA.',
        [['text' => 'Email me', 'url' => '/contact/'], ['text' => 'GitHub', 'url' => 'https://github.com/matthummel-pa', 'outline' => true]]);
    $contact .= $dyn('cta-band', ['heading' => 'Prefer email or social?', 'body' => 'Find me on LinkedIn, GitHub, or Dev.to — or use the form below. I read everything and reply within about a business day.', 'btnText' => 'Connect on LinkedIn →', 'btnUrl' => 'https://www.linkedin.com/in/matt-hummel-pa/', 'variant' => 'light']);
    $patterns['pressroot/contact-full'] = ['title' => __('Contact — Full page (above form)', 'pressroot'), 'content' => $contact];

    /* ════════════════════════════ BLOG (index) ════════════════════════ */
    $blog  = $hero('The Blog', 'Latest writing.',
        'WordPress tutorials, Power Platform guides, and dev notes from Gettysburg, PA.');
    $blog .= $dyn('post-grid', ['postType' => 'post', 'count' => 9, 'columns' => 3, 'showCategory' => true, 'showExcerpt' => true, 'showDate' => true]);
    $patterns['pressroot/blog-full'] = ['title' => __('Blog — Full page', 'pressroot'), 'content' => $blog];

    /* ════════════════════════════ SINGLE POST starter ═════════════════ */
    $post  = $p('Open with a short, punchy intro that tells the reader exactly what they&#8217;ll learn and why it matters.', 'large', 'body');
    $post .= $h(2, 'The first thing to know', 'x-large');
    $post .= $p('Explain the core idea in plain language. Keep paragraphs short and concrete — show, don&#8217;t just tell.', 'medium', 'body');
    $post .= '<!-- wp:quote {"className":"is-style-default"} --><blockquote class="wp-block-quote is-style-default"><!-- wp:paragraph --><p>Pull out the single most important takeaway as a quote so it&#8217;s easy to scan.</p><!-- /wp:paragraph --></blockquote><!-- /wp:quote -->';
    $post .= $h(2, 'A step-by-step walkthrough', 'x-large');
    $post .= '<!-- wp:list --><ul class="wp-block-list"><!-- wp:list-item --><li>Step one — what to do first.</li><!-- /wp:list-item --><!-- wp:list-item --><li>Step two — the part people usually miss.</li><!-- /wp:list-item --><!-- wp:list-item --><li>Step three — how to verify it worked.</li><!-- /wp:list-item --></ul><!-- /wp:list -->';
    $post .= $p('Wrap up with the key takeaway and a clear next step for the reader.', 'medium', 'body');
    $post .= $dyn('cta-band', ['heading' => 'Found this useful?', 'body' => 'I write practical, beginner-friendly web development and Power Platform tutorials. Get in touch if you&#8217;d like to work together.', 'btnText' => 'Get in touch →', 'btnUrl' => '/contact/', 'variant' => 'dark']);
    $patterns['pressroot/single-post'] = ['title' => __('Single Post — Starter', 'pressroot'), 'content' => $post];

    /* ════════════════════════════ PROJECTS ════════════════════════════ */
    $projects  = $hero('Work', 'Projects &amp; experiments.',
        'Open-source tools and code on one side, client WordPress builds on the other. Most live demos and source live in a repo — dig in.',
        [['text' => 'View GitHub', 'url' => 'https://github.com/matthummel-pa'], ['text' => 'Start a project', 'url' => '/contact/', 'outline' => true]]);
    // Featured repo — full profile: tags, languages, version notes + changelog, README.
    $projects .= $wrap($h(2, 'Featured build', 'x-large') . $p('A deep look at one project — tags, languages, version notes, and the README, live from the repo.', 'medium', 'muted'), '50px', '8px');
    $projects .= $wrap('<!-- wp:prt/gh-repo {"owner":"matthummel-pa","repo":"pressroot"} /-->', '10px', '10px');
    $projects .= $wrap($h(2, 'Side Quests', 'x-large') . $p('Experiments, tools, and open-source code — built after hours and shipped in the open. Each opens an on-site page with the full repo details.', 'medium', 'muted'), '50px', '8px');
    $projects .= $dyn('post-grid', ['postType' => 'projects', 'term' => 'side-quests', 'count' => 6, 'columns' => 3, 'showExcerpt' => true, 'showDate' => false]);
    $projects .= $wrap($h(2, 'Selected Work', 'x-large') . $p('Client websites and WordPress builds — designed, built, and shipped end to end.', 'medium', 'muted'), '50px', '8px');
    $projects .= $dyn('post-grid', ['postType' => 'projects', 'term' => 'selected-work', 'count' => 6, 'columns' => 3, 'showExcerpt' => true, 'showDate' => false]);
    $projects .= $dyn('cta-band', ['heading' => 'Have a project in mind?', 'body' => "Power Platform apps, WordPress builds, and Microsoft 365 solutions — let's talk.", 'btnText' => 'Get in touch →', 'btnUrl' => '/contact/', 'variant' => 'dark']);
    $patterns['pressroot/projects-full'] = ['title' => __('Projects — Full page', 'pressroot'), 'content' => $projects];

    /* ════════════════════════════ RÉSUMÉ ══════════════════════════════ */
    $timeline = wp_json_encode([
        ['dates' => 'Mar 2025 – Present', 'title' => 'Senior Power Platform Consultant', 'org' => 'Saliense Consulting · Chambersburg, PA', 'body' => 'Scope, design, and deliver Power Apps and Power Automate solutions for enterprise clients. Build canvas and model-driven apps that replace manual workflows, and architect SharePoint Online as a reliable data layer.'],
        ['dates' => 'Dec 2023 – Feb 2025', 'title' => 'Applications & SharePoint Administrator', 'org' => 'All Native Group (Ho-Chunk Inc.) · Dunn Loring, VA', 'body' => 'Built custom Power Apps for forms and workflows, created Power Automate flows, managed permissions and site collections, and turned requirements into scalable solutions.'],
        ['dates' => 'Sep 2021 – Jun 2022', 'title' => 'SharePoint Web Developer', 'org' => 'Knowledge Capital Associates (USMC) · Stafford, VA', 'body' => 'Supported SharePoint site creation and permissions, collaborated on SharePoint Online migrations, and converted InfoPath forms to Power Apps and Designer workflows to Power Automate.'],
        ['dates' => 'Jul 2011 – Oct 2020', 'title' => 'Web Developer', 'org' => 'Germanna Community College · Locust Grove, VA', 'body' => 'Developed a responsive WordPress site with HTML, CSS, JavaScript, and PHP; optimized for Section 508 / WCAG 2.0; migrated the site to WordPress and handled analytics.'],
        ['dates' => 'Ongoing', 'title' => 'Technical Writing · matthummel.com', 'org' => 'Independent', 'body' => 'Writing practical, beginner-friendly web development tutorials — front-end, WordPress, automation, and the wider Microsoft stack.'],
    ]);
    $resumeSkills = wp_json_encode([
        ['title' => 'WordPress & Web', 'body' => 'Custom themes & plugins, Sage 11, Blade, Tailwind, React, REST APIs, performance & SEO.'],
        ['title' => 'Power Platform', 'body' => 'Power Apps (canvas & model-driven), Power Automate, Dataverse, SharePoint Online, M365.'],
        ['title' => 'Foundations', 'body' => 'Accessibility (Section 508 / WCAG), Core Web Vitals, Git, analytics, requirements to deployment.'],
    ]);
    $resume  = $hero('Résumé', 'Matt Hummel — Web Developer Résumé.',
        'Full-stack web developer in Gettysburg, PA with 15+ years building fast, accessible WordPress sites and Microsoft Power Platform solutions — from custom Power Apps and Power Automate flows to SharePoint.',
        [['text' => 'Get in touch', 'url' => '/contact/'], ['text' => 'Connect on LinkedIn', 'url' => 'https://www.linkedin.com/in/matt-hummel-pa/', 'outline' => true]]);
    $resume .= $wrap($h(2, 'Experience', 'x-large'), '40px', '0px');
    $resume .= $dyn('timeline', ['entries' => $timeline]);
    $resume .= $wrap($h(2, 'Skills', 'x-large'), '40px', '0px');
    $resume .= $dyn('skills-grid', ['cards' => $resumeSkills, 'columns' => 3]);
    $resume .= $dyn('cta-band', ['heading' => 'Looking for a developer?', 'body' => "I take on a small number of freelance and side projects. If you've got something interesting, I'd genuinely like to hear about it.", 'btnText' => 'Get in touch →', 'btnUrl' => '/contact/', 'variant' => 'dark']);
    $patterns['pressroot/resume-full'] = ['title' => __('Résumé — Full page', 'pressroot'), 'content' => $resume];

    /* ════════════════════════════ RESOURCES ═══════════════════════════ */
    $resources  = $hero('Resources', 'Things I return to.',
        'Tools, docs, and references that actually get used — WordPress, Power Platform, and the wider front-end. Updated as the stack evolves.',
        [['text' => 'Suggest a link', 'url' => '/contact/', 'outline' => true]]);
    $resources .= $wrap(
        '<!-- wp:prt/resource-group {"heading":"WordPress","emoji":"🌐","links":"[{\"label\":\"Roots / Sage docs\",\"url\":\"https://roots.io/sage/\"},{\"label\":\"WP Developer Hub\",\"url\":\"https://developer.wordpress.org/\"},{\"label\":\"Block Editor handbook\",\"url\":\"https://developer.wordpress.org/block-editor/\"},{\"label\":\"WooCommerce docs\",\"url\":\"https://woocommerce.com/documentation/\"}]"} /-->'
        . '<!-- wp:prt/resource-group {"heading":"Power Platform","emoji":"⚡","links":"[{\"label\":\"Microsoft Learn\",\"url\":\"https://learn.microsoft.com/\"},{\"label\":\"Power Apps docs\",\"url\":\"https://learn.microsoft.com/en-us/power-apps/\"},{\"label\":\"Power Automate docs\",\"url\":\"https://learn.microsoft.com/en-us/power-automate/\"},{\"label\":\"Community forums\",\"url\":\"https://powerusers.microsoft.com/\"}]"} /-->'
        . '<!-- wp:prt/resource-group {"heading":"Dev tools","emoji":"🔧","links":"[{\"label\":\"MDN Web Docs\",\"url\":\"https://developer.mozilla.org/\"},{\"label\":\"web.dev\",\"url\":\"https://web.dev/\"},{\"label\":\"CSS-Tricks\",\"url\":\"https://css-tricks.com/\"},{\"label\":\"Can I Use\",\"url\":\"https://caniuse.com/\"}]"} /-->',
        '30px', '30px');
    $resources .= $dyn('cta-band', ['heading' => 'Want more like this?', 'body' => 'I share what I learn as I build — tutorials, snippets, and honest notes from real projects.', 'btnText' => 'Read the blog →', 'btnUrl' => '/blog/', 'variant' => 'dark']);
    $patterns['pressroot/resources-full'] = ['title' => __('Resources — Full page', 'pressroot'), 'content' => $resources];

    /* ════════════════════════════ NOW ═════════════════════════════════ */
    $nowList  = '<!-- wp:list --><ul class="wp-block-list">'
        . '<!-- wp:list-item --><li>Shipping Pressroot v1.2 — the Paper + Space design refresh.</li><!-- /wp:list-item -->'
        . '<!-- wp:list-item --><li>Deepening Laravel + WASM PHP experiments (Sage on Playground).</li><!-- /wp:list-item -->'
        . '<!-- wp:list-item --><li>Reading <em>A Philosophy of Software Design</em>.</li><!-- /wp:list-item -->'
        . '<!-- wp:list-item --><li>Running three mornings a week.</li><!-- /wp:list-item -->'
        . '</ul><!-- /wp:list -->';
    $now  = $hero('Now', 'What I&#8217;m doing now.',
        'A snapshot of what has my attention this season — projects, learning, and life. The idea comes from nownownow.com.');
    $now .= $wrap($h(2, 'Right now', 'x-large') . $nowList, '40px', '8px');
    $now .= $wrap($h(2, 'Up next', 'x-large') . $p('Publishing the theme framework write-up, and turning the GitHub project sync into a standalone mini-plugin.', 'medium', 'muted'), '30px', '40px');
    $now .= $dyn('cta-band', ['heading' => 'Working on something similar?', 'body' => 'Always happy to compare notes on WordPress, Power Platform, or side projects.', 'btnText' => 'Say hello →', 'btnUrl' => '/contact/', 'variant' => 'dark']);
    $patterns['pressroot/now-full'] = ['title' => __('Now — Full page', 'pressroot'), 'content' => $now];

    /* ════════════════════════════ LEGAL ═══════════════════════════════ */
    $legal  = $hero('Legal', 'Privacy Policy.', 'How this site handles your data — in plain language.');
    $legal .= $wrap(
        $h(2, 'What we collect', 'large')
        . $p('Only the data needed to answer your messages: a name, an email address, and whatever you write in the contact form.')
        . $h(2, 'What we store', 'large')
        . $p('Contact form submissions are emailed and never sold. No analytics cookies are set without consent.')
        . $h(2, 'Your rights', 'large')
        . $p('Email hello@example.com to request a copy or deletion of your data at any time.'),
        '20px', '50px');
    $patterns['pressroot/legal-full'] = ['title' => __('Legal — Full page', 'pressroot'), 'content' => $legal];

    /* ── Register (single "Pressroot" pattern category) ───────────── */
    foreach ($patterns as $slug => $pat) {
        register_block_pattern($slug, [
            'title'      => $pat['title'],
            'categories' => ['pressroot'],
            'blockTypes' => ['core/post-content'],
            'content'    => $pat['content'],
        ]);
    }
}, 12);

/**
 * Late cleanup pass — runs after every pattern file has registered on `init`.
 *
 * IMPORTANT: this used to unregister EVERY pattern in the theme except the 12
 * curated "— Full page" patterns above, on every single page load. That
 * silently deleted ~27 working section-level patterns (heroes, CTAs, pricing,
 * testimonials, stat strips, etc. — from block-patterns.php, patterns-extra.php,
 * sections-library.php, and blocks.php) before a real user ever saw them in the
 * inserter. For a theme meant to be a flexible page-building toolkit for
 * developers/agencies, that's the opposite of what we want — more prebuilt
 * sections is more product value, not clutter. Removed.
 *
 * What's still worth doing here: drop the two "pressroot/about-page" and
 * "pressroot/resume-page" patterns in block-patterns.php — they're an older,
 * simpler draft of what "pressroot/about-full" and "pressroot/resume-full"
 * (above) now do better, so keeping both would just confuse a site owner
 * choosing between two "full About page" options in the inserter. Genuinely
 * superseded duplicates like these are worth unregistering explicitly, by
 * name, rather than via a blanket purge.
 */
add_action('init', function () {
    foreach (['pressroot/about-page', 'pressroot/resume-page'] as $superseded) {
        if (function_exists('unregister_block_pattern')) {
            unregister_block_pattern($superseded);
        }
    }

    // Per-page categories (prt-home, prt-about, etc.) registered in
    // home-patterns.php were designed for patterns that were never actually
    // assigned to them (see the comment there) — they're empty by design, so
    // remove them here rather than clutter the inserter with 0-pattern groups.
    foreach (['prt-home', 'prt-about', 'prt-resume', 'prt-resources', 'prt-contact', 'prt-sections'] as $cat) {
        if (class_exists('WP_Block_Pattern_Categories_Registry') && \WP_Block_Pattern_Categories_Registry::get_instance()->is_registered($cat)) {
            unregister_block_pattern_category($cat);
        }
    }
}, 99);

/* Disable WordPress core + remote (.org) patterns so only the curated set shows. */
add_action('after_setup_theme', function () {
    remove_theme_support('core-block-patterns');
}, 11);
add_filter('should_load_remote_block_patterns', '__return_false');
