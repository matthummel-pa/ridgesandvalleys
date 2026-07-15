<?php

/**
 * "Freelance / Portfolio" site-type starter patterns — Pressroot AI.
 *
 * Dedicated, GENERIC (not tied to any real person) block patterns for a solo
 * freelancer / independent consultant site, used by the "Freelance / Portfolio"
 * profile in app/ai-assistant.php. Two layout + copy variants per page so the
 * assistant's "Regenerate" action can swap A ⇄ B when an owner doesn't like
 * what they got:
 *
 *   prt-site/freelance-about-a     — About:   split hero + terminal-box bio card.
 *   prt-site/freelance-about-b     — About:   bento stat-forward, punchier tone.
 *   prt-site/freelance-resume-a    — Résumé:  classic hero + prt/timeline + skills.
 *   prt-site/freelance-resume-b    — Résumé:  bento "career at a glance" + timeline.
 *   prt-site/freelance-projects-a  — Projects: clean hero + featured/grid split.
 *   prt-site/freelance-projects-b  — Projects: bento showcase, stat strip lead.
 *
 * All copy uses placeholder tokens ("[Your Name]", "[Your role/discipline]",
 * "Project One" …) so any freelancer — designer, developer, writer, consultant
 * — can drop in their own details. Registered under the 'prt-site-types'
 * pattern category (registered centrally elsewhere; this file only tags its
 * patterns with that slug, per the established per-file convention already
 * used in page-patterns.php / home-patterns.php).
 */

namespace App;

add_action('init', function () {

    /* ── Block-markup helpers (duplicated per-file — established convention) ── */

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

    $cardGrid = function (string $cols, string $cardsHtml): string {
        return "<!-- wp:html -->\n"
            . '<div class="prt-grid-' . $cols . '" style="display:grid; grid-template-columns:repeat(' . $cols . ',1fr); gap:20px; margin-top:8px;">'
            . $cardsHtml . '</div>' . "\n<!-- /wp:html -->";
    };

    $patterns = [];

    /* ══════════════════════════ ABOUT — VARIANT A ══════════════════════════
     * Split hero (intro text left) + signature terminal-box "whoami" card
     * right — this theme's developer-portfolio motif, reused generically.
     */
    $aboutATerminal = '<!-- wp:html -->'
        . '<div class="terminal-box" style="max-width:100%;">'
        . '<div class="term-bar"><div class="term-dot term-dot-red"></div><div class="term-dot term-dot-amber"></div><div class="term-dot term-dot-green"></div></div>'
        . '<p class="term-prompt">whoami</p>'
        . '<p>[Your Name]</p>'
        . '<p class="term-info">role: [Your role/discipline]</p>'
        . '<p class="term-dim">based: [Your city, remote-friendly]</p>'
        . '<p class="term-dim">focus: independent &amp; freelance work</p>'
        . '<p class="term-success">status: available ✓</p>'
        . '</div>'
        . '<!-- /wp:html -->';

    $aboutA  = '<!-- wp:columns {"isStackedOnMobile":true,"verticalAlignment":"center"} -->'
        . '<div class="wp-block-columns are-vertically-aligned-center">'
        . '<!-- wp:column {"width":"58%"} --><div class="wp-block-column" style="flex-basis:58%">'
        . $eyebrow('About')
        . '<!-- wp:heading {"level":1,"style":{"typography":{"fontSize":"clamp(36px, 4.6vw, 54px)","lineHeight":"1.08"}}} --><h1 class="wp-block-heading" style="font-size:clamp(36px, 4.6vw, 54px);line-height:1.08">Hi, I&#8217;m [Your Name].<br>I help people ship good work.</h1><!-- /wp:heading -->'
        . $p('I&#8217;m an independent [your role/discipline] working with small businesses, startups, and teams who need someone dependable to think through a problem and deliver — no agency layers, no handoffs, just direct collaboration from kickoff to launch.', 'large', 'body')
        . $buttons([['text' => 'Get in touch', 'url' => '/contact/'], ['text' => 'See my work', 'url' => '/projects/', 'outline' => true]])
        . '</div><!-- /wp:column -->'
        . '<!-- wp:column {"width":"42%"} --><div class="wp-block-column" style="flex-basis:42%">'
        . $aboutATerminal
        . '</div><!-- /wp:column -->'
        . '</div><!-- /wp:columns -->';
    $aboutA  = $wrap($aboutA, '70px', '30px');

    $aboutASkills = wp_json_encode([
        ['title' => 'What I actually do', 'body' => 'Plain-language version: I take a project from a rough idea or a messy first draft to something finished, considered, and ready to use.'],
        ['title' => 'How I work', 'body' => 'Short discovery conversation, a clear written scope, then regular check-ins so you always know where things stand — no disappearing for weeks.'],
        ['title' => 'Why work with me', 'body' => 'You get one accountable person who cares about the outcome, not a rotating cast — direct communication and no unnecessary overhead.'],
    ]);
    $aboutA .= $wrap($h(2, 'What I do &amp; why it works', 'x-large'), '10px', '0px');
    $aboutA .= $dyn('skills-grid', ['cards' => $aboutASkills, 'columns' => 3]);

    $aboutA .= $wrap(
        $h(2, 'A little more about me', 'x-large')
        . $p('[Add a few personal sentences here — how you got started, what you care about in your work, what you do outside of it. This is the part that makes a portfolio feel like a person instead of a brochure. Keep it warm, specific, and true to how you actually talk.]', 'medium', 'muted'),
        '30px', '10px'
    );

    $aboutA .= $dyn('cta-band', [
        'heading' => 'Have a project in mind?',
        'body'    => "I take on a limited number of freelance projects at a time so I can give each one real attention. If that sounds like a fit, I'd love to hear from you.",
        'btnText' => 'Start a conversation →',
        'btnUrl'  => '/contact/',
        'variant' => 'dark',
    ]);
    $patterns['prt-site/freelance-about-a'] = [
        'title'       => __('Freelance — About (split hero + terminal card)', 'pressroot'),
        'description' => __('Personal split hero with the signature terminal "whoami" card, skills grid, and a personal-note section.', 'pressroot'),
        'content'     => $aboutA,
    ];

    /* ══════════════════════════ ABOUT — VARIANT B ══════════════════════════
     * Bento-grid, stat-forward, punchier tone.
     */
    $aboutBStats = wp_json_encode([
        ['value' => '[X]+', 'label' => 'Years doing this'],
        ['value' => '[XX]+', 'label' => 'Projects completed'],
        ['value' => '1', 'label' => 'Person you actually talk to'],
        ['value' => '100%', 'label' => 'Independent — no subcontracting'],
    ]);
    $aboutB  = $hero(
        'About',
        '[Your Name] — [your role/discipline], for hire.',
        'No account managers, no junior hand-offs. You work directly with the person doing the work — that&#8217;s the whole pitch.',
        [['text' => 'Work with me', 'url' => '/contact/'], ['text' => 'View projects', 'url' => '/projects/', 'outline' => true]]
    );
    $aboutB .= $dyn('stat-strip', ['stats' => $aboutBStats, 'columns' => 4]);

    $aboutBBento = '<!-- wp:columns {"isStackedOnMobile":true} -->'
        . '<div class="wp-block-columns">'
        . '<!-- wp:column {"width":"55%","className":"bento-card bento-card--dark"} --><div class="wp-block-column bento-card bento-card--dark" style="flex-basis:55%; border-radius:24px; padding:34px;">'
        . '<h3 style="font-family:var(--font-display); font-weight:800; font-size:26px; margin:0 0 12px; color:#fff;">The short version</h3>'
        . '<p style="font-size:16px; line-height:1.6; color:rgba(255,255,255,.86); margin:0;">[Your Name] is an independent [your role/discipline] who helps clients turn a vague idea into something real — fast, clear communication and work you can actually use, not just admire.</p>'
        . '</div><!-- /wp:column -->'
        . '<!-- wp:column {"width":"22.5%","className":"bento-card"} --><div class="wp-block-column bento-card" style="flex-basis:22.5%; border-radius:24px; padding:28px; background:#fff; border:1.5px solid #ECE6FB;">'
        . '<div class="prt-serif" style="font-size:34px; margin-bottom:8px;">🎯</div>'
        . '<h3 style="font-family:var(--font-display); font-weight:700; font-size:18px; margin:0 0 8px; color:#17151F;">Focused</h3>'
        . '<p style="font-size:14.5px; line-height:1.5; color:#5A5676; margin:0;">A small client roster by design — enough to give each project my full attention.</p>'
        . '</div><!-- /wp:column -->'
        . '<!-- wp:column {"width":"22.5%","className":"bento-card bento-card--tint"} --><div class="wp-block-column bento-card bento-card--tint" style="flex-basis:22.5%; border-radius:24px; padding:28px; background:#EEE8FE;">'
        . '<div style="font-size:34px; margin-bottom:8px;">⚡</div>'
        . '<h3 style="font-family:var(--font-display); font-weight:700; font-size:18px; margin:0 0 8px; color:#17151F;">Direct</h3>'
        . '<p style="font-size:14.5px; line-height:1.5; color:#4A4660; margin:0;">No layers between you and the work — just straightforward answers and steady progress.</p>'
        . '</div><!-- /wp:column -->'
        . '</div><!-- /wp:columns -->';
    $aboutB .= $wrap($aboutBBento, '40px', '10px');

    $aboutB .= $wrap(
        $h(2, 'Why people hire me', 'x-large')
        . $p('[Write 2-3 punchy sentences here about your track record, your specialty, or the kind of client you love working with. Make it specific — swap in a real result, a niche you own, or a problem you solve better than most.]', 'large', 'body'),
        '30px', '10px'
    );

    $aboutB .= $dyn('cta-band', [
        'heading' => "Let's build something worth showing off.",
        'body'    => 'Tell me what you&#8217;re working on — I read every message myself and reply quickly.',
        'btnText' => 'Say hello →',
        'btnUrl'  => '/contact/',
        'variant' => 'dark',
    ]);
    $patterns['prt-site/freelance-about-b'] = [
        'title'       => __('Freelance — About (bento stats, punchy)', 'pressroot'),
        'description' => __('Stat-strip led hero, asymmetric bento "short version" card, and a punchier confident tone.', 'pressroot'),
        'content'     => $aboutB,
    ];

    /* ══════════════════════════ RÉSUMÉ — VARIANT A ══════════════════════════
     * Classic hero + prt/timeline + skills grid — a clean, scannable CV page.
     */
    $resumeATimeline = wp_json_encode([
        ['dates' => '2023 – Present', 'title' => 'Senior [Role] · Independent', 'org' => 'Self-employed · Remote', 'body' => 'Working directly with clients on [type of project] — from initial scope through delivery. Example: replace this line with a real outcome, e.g. "Delivered 12 projects for clients across 4 industries."'],
        ['dates' => '2020 – 2023', 'title' => '[Role Title]', 'org' => '[Company Name] · [City]', 'body' => 'Describe your main responsibility and one concrete result — a number, a launch, a process you improved. Keep it to 1-2 sentences.'],
        ['dates' => '2017 – 2020', 'title' => '[Earlier Role Title]', 'org' => '[Company Name] · [City]', 'body' => 'This is where your career started to take shape — what you learned, what you shipped, or what you were trusted with.'],
        ['dates' => '[Year] – [Year]', 'title' => '[Education / Certification]', 'org' => '[School or Program Name]', 'body' => 'Degree, bootcamp, or certification relevant to your discipline — swap this row out if it doesn&#8217;t apply.'],
    ]);
    $resumeASkills = wp_json_encode([
        ['title' => 'Core skills', 'body' => 'List 3-5 tools, languages, or methods you use daily — e.g. specific software, frameworks, or techniques central to your discipline.'],
        ['title' => 'Working style', 'body' => 'Clear communication, realistic timelines, and deliverables you can actually use — not just drafts that need more work.'],
        ['title' => 'Availability', 'body' => 'Currently taking on [X] new projects per [month/quarter] — reach out to check current availability.'],
    ]);
    $resumeA  = $hero(
        'Résumé',
        '[Your Name] — [Your role/discipline].',
        'A quick look at where I&#8217;ve worked, what I&#8217;ve built, and what I bring to a new project. Full details available on request.',
        [['text' => 'Get in touch', 'url' => '/contact/'], ['text' => 'Download résumé (PDF)', 'url' => '#', 'outline' => true]]
    );
    $resumeA .= $wrap($h(2, 'Experience', 'x-large'), '40px', '0px');
    $resumeA .= $dyn('timeline', ['entries' => $resumeATimeline]);
    $resumeA .= $wrap($h(2, 'Skills &amp; availability', 'x-large'), '40px', '0px');
    $resumeA .= $dyn('skills-grid', ['cards' => $resumeASkills, 'columns' => 3]);
    $resumeA .= $dyn('cta-band', [
        'heading' => 'Looking for someone to bring this to life?',
        'body'    => "I take on a small number of freelance projects at a time. If you've got something in mind, I'd genuinely like to hear about it.",
        'btnText' => 'Get in touch →',
        'btnUrl'  => '/contact/',
        'variant' => 'dark',
    ]);
    $patterns['prt-site/freelance-resume-a'] = [
        'title'       => __('Freelance — Résumé (classic timeline)', 'pressroot'),
        'description' => __('Straightforward hero + prt/timeline career history + a 3-up skills/availability grid.', 'pressroot'),
        'content'     => $resumeA,
    ];

    /* ══════════════════════════ RÉSUMÉ — VARIANT B ══════════════════════════
     * Bento "career at a glance" stat strip up top, then timeline — punchier,
     * numbers-first framing of the same CV content.
     */
    $resumeBStats = wp_json_encode([
        ['value' => '[X]+', 'label' => 'Years of experience'],
        ['value' => '[XX]+', 'label' => 'Projects delivered'],
        ['value' => '[X]', 'label' => 'Industries served'],
        ['value' => '100%', 'label' => 'Direct, no middlemen'],
    ]);
    $resumeBTimeline = wp_json_encode([
        ['dates' => '2023 – Present', 'title' => 'Independent [Role]', 'org' => 'Self-employed · Remote', 'body' => 'Runs my own client roster end to end: scoping, delivery, and the relationship. Swap in your real headline win from this period.'],
        ['dates' => '2020 – 2023', 'title' => '[Role Title]', 'org' => '[Company Name]', 'body' => 'One or two sentences on scope and a measurable result — traffic, revenue, timeline, satisfaction, whatever proves impact in your field.'],
        ['dates' => '2017 – 2020', 'title' => '[Earlier Role Title]', 'org' => '[Company Name]', 'body' => 'Where the foundation got built — the skills or systems you learned that you still rely on today.'],
    ]);
    $resumeB  = $hero(
        'Résumé',
        'Career highlights, at a glance.',
        '[Your Name] — [your role/discipline] with a track record of shipping real work for real clients. The numbers below, then the full story underneath.',
        [['text' => 'Hire me', 'url' => '/contact/']]
    );
    $resumeB .= $dyn('stat-strip', ['stats' => $resumeBStats, 'columns' => 4]);

    $resumeBHighlight = '<!-- wp:columns {"isStackedOnMobile":true} -->'
        . '<div class="wp-block-columns">'
        . '<!-- wp:column {"width":"33.33%"} --><div class="wp-block-column" style="flex-basis:33.33%; background:#17151F; border-radius:22px; padding:30px; color:#fff;">'
        . '<div style="font-family:var(--font-mono); font-size:12px; opacity:.75; margin-bottom:10px;">HEADLINE PROJECT</div>'
        . '<h3 style="font-family:var(--font-display); font-weight:700; font-size:20px; margin:0 0 8px;">[Project or client name]</h3>'
        . '<p style="font-size:14.5px; line-height:1.55; margin:0; opacity:.9;">One sentence on the biggest, most impressive thing you&#8217;ve shipped — make it concrete and specific.</p>'
        . '</div><!-- /wp:column -->'
        . '<!-- wp:column {"width":"33.33%"} --><div class="wp-block-column" style="flex-basis:33.33%; background:#FF7A3D; border-radius:22px; padding:30px; color:#17151F;">'
        . '<div style="font-family:var(--font-mono); font-size:12px; opacity:.75; margin-bottom:10px;">SPECIALTY</div>'
        . '<h3 style="font-family:var(--font-display); font-weight:700; font-size:20px; margin:0 0 8px;">[Your niche]</h3>'
        . '<p style="font-size:14.5px; line-height:1.55; margin:0; opacity:.9;">The specific problem or client type you solve better than most — the thing you want to be known for.</p>'
        . '</div><!-- /wp:column -->'
        . '<!-- wp:column {"width":"33.33%"} --><div class="wp-block-column" style="flex-basis:33.33%; background:#22CFEE; border-radius:22px; padding:30px; color:#06283a;">'
        . '<div style="font-family:var(--font-mono); font-size:12px; opacity:.75; margin-bottom:10px;">RIGHT NOW</div>'
        . '<h3 style="font-family:var(--font-display); font-weight:700; font-size:20px; margin:0 0 8px;">Open for work</h3>'
        . '<p style="font-size:14.5px; line-height:1.55; margin:0; opacity:.9;">Currently booking [X] new projects for [month/quarter] — get in touch to check availability.</p>'
        . '</div><!-- /wp:column -->'
        . '</div><!-- /wp:columns -->';
    $resumeB .= $wrap($resumeBHighlight, '40px', '10px');

    $resumeB .= $wrap($h(2, 'The full story', 'x-large'), '30px', '0px');
    $resumeB .= $dyn('timeline', ['entries' => $resumeBTimeline]);
    $resumeB .= $dyn('cta-band', [
        'heading' => 'Ready to add your project to this list?',
        'body'    => "Let's talk about what you need and whether I'm the right fit.",
        'btnText' => "Let's talk →",
        'btnUrl'  => '/contact/',
        'variant' => 'dark',
    ]);
    $patterns['prt-site/freelance-resume-b'] = [
        'title'       => __('Freelance — Résumé (bento highlights, punchy)', 'pressroot'),
        'description' => __('Stat strip + a 3-up colored "headline project / specialty / availability" bento row, then the full timeline.', 'pressroot'),
        'content'     => $resumeB,
    ];

    /* ══════════════════════════ PROJECTS — VARIANT A ══════════════════════════
     * Clean hero + one featured project card + a 3-up grid of prt/project-card.
     */
    $projectsA  = $hero(
        'Projects',
        'A few things I&#8217;ve made.',
        'A sample of recent work — replace these with your own case studies, each linking to more detail if you have it.',
        [['text' => 'Start a project', 'url' => '/contact/']]
    );

    $projectsA .= $wrap($h(2, 'Featured project', 'x-large') . $p('Swap this for your strongest, most representative piece of work.', 'medium', 'muted'), '40px', '8px');
    $projectsA .= $dyn('project-card', [
        'heading'   => 'Project One — a brief one-line description of what this project was and its impact.',
        'excerpt'   => 'Example: replace with a short paragraph on the challenge, what you did, and the outcome — one or two sentences is enough.',
        'link'      => '#',
        'imageUrl'  => '',
        'imageAlt'  => 'Project One preview',
        'tags'      => '[Tool/skill], [Tool/skill], [Tool/skill]',
        'liveUrl'   => '#',
        'githubUrl' => '',
    ]);

    $projectsA .= $wrap($h(2, 'More work', 'x-large') . $p('A grid of smaller examples — good for range, not just depth.', 'medium', 'muted'), '40px', '8px');
    $moreGrid = '';
    foreach ([
        ['Project Two', 'A brief one-line description of what this project was and its impact.', '[Tool/skill], [Tool/skill]'],
        ['Project Three', 'A brief one-line description of what this project was and its impact.', '[Tool/skill], [Tool/skill]'],
        ['Project Four', 'A brief one-line description of what this project was and its impact.', '[Tool/skill], [Tool/skill]'],
    ] as $proj) {
        [$title, $desc, $tags] = $proj;
        $moreGrid .= '<div class="prt-lift prt-spec-card" style="background:#fff; border:1.5px solid #ECE6FB; border-radius:20px; padding:26px;">'
            . '<h3 style="font-family:var(--font-display); font-weight:700; font-size:19px; margin:0 0 8px; color:#17151F;">' . esc_html($title) . '</h3>'
            . '<p style="font-size:14.5px; line-height:1.55; color:#5A5676; margin:0 0 14px;">' . esc_html($desc) . '</p>'
            . '<p style="font-family:var(--font-mono); font-size:12px; color:#7C75A8; margin:0;">' . esc_html($tags) . '</p>'
            . '</div>';
    }
    $projectsA .= $cardGrid('3', $moreGrid);

    $projectsA .= $dyn('cta-band', [
        'heading' => 'Have a project in mind?',
        'body'    => "I take on a limited number of freelance projects at a time. If you've got something interesting, let's talk.",
        'btnText' => 'Get in touch →',
        'btnUrl'  => '/contact/',
        'variant' => 'dark',
    ]);
    $patterns['prt-site/freelance-projects-a'] = [
        'title'       => __('Freelance — Projects (featured + grid)', 'pressroot'),
        'description' => __('Hero, one prt/project-card "featured project" section, then a 3-up card grid for smaller examples.', 'pressroot'),
        'content'     => $projectsA,
    ];

    /* ══════════════════════════ PROJECTS — VARIANT B ══════════════════════════
     * Stat-strip lead + asymmetric bento showcase — bolder, portfolio-as-proof.
     */
    $projectsBStats = wp_json_encode([
        ['value' => '[XX]+', 'label' => 'Projects shipped'],
        ['value' => '[X]', 'label' => 'Industries worked in'],
        ['value' => '[X]★', 'label' => 'Average client rating'],
        ['value' => '100%', 'label' => 'Delivered on time'],
    ]);
    $projectsB  = $hero(
        'Projects',
        'Proof, not promises.',
        'Every project below started as a rough brief and ended as something a client actually uses. Here&#8217;s a sample — swap in your own.',
        [['text' => 'See how I work', 'url' => '/about/'], ['text' => 'Start a project', 'url' => '/contact/', 'outline' => true]]
    );
    $projectsB .= $dyn('stat-strip', ['stats' => $projectsBStats, 'columns' => 4]);

    $bento = '<!-- wp:columns {"isStackedOnMobile":true} -->'
        . '<div class="wp-block-columns">'
        . '<!-- wp:column {"width":"60%"} --><div class="wp-block-column prt-lift" style="flex-basis:60%; background:#17151F; border-radius:24px; padding:34px; color:#fff;">'
        . '<div style="font-family:var(--font-mono); font-size:12px; opacity:.7; margin-bottom:12px;">PROJECT ONE — FEATURED</div>'
        . '<h3 style="font-family:var(--font-display); font-weight:800; font-size:26px; margin:0 0 10px;">Project One</h3>'
        . '<p style="font-size:15.5px; line-height:1.6; margin:0 0 16px; opacity:.9;">A brief one-line description of what this project was and its impact — replace with your own case study summary, ideally with a concrete result.</p>'
        . '<p style="font-family:var(--font-mono); font-size:12.5px; opacity:.75; margin:0;">[Tool/skill] · [Tool/skill] · [Tool/skill]</p>'
        . '</div><!-- /wp:column -->'
        . '<!-- wp:column {"width":"40%"} --><div class="wp-block-column prt-lift" style="flex-basis:40%; background:#37E29A; border-radius:24px; padding:30px; color:#17151F;">'
        . '<div style="font-family:var(--font-mono); font-size:12px; opacity:.75; margin-bottom:12px;">PROJECT TWO</div>'
        . '<h3 style="font-family:var(--font-display); font-weight:800; font-size:22px; margin:0 0 10px;">Project Two</h3>'
        . '<p style="font-size:14.5px; line-height:1.6; margin:0; opacity:.85;">A brief one-line description of what this project was and its impact.</p>'
        . '</div><!-- /wp:column -->'
        . '</div><!-- /wp:columns -->';
    $bento .= '<!-- wp:columns {"isStackedOnMobile":true} -->'
        . '<div class="wp-block-columns" style="margin-top:20px;">'
        . '<!-- wp:column {"width":"40%"} --><div class="wp-block-column prt-lift" style="flex-basis:40%; background:#22CFEE; border-radius:24px; padding:30px; color:#06283a;">'
        . '<div style="font-family:var(--font-mono); font-size:12px; opacity:.75; margin-bottom:12px;">PROJECT THREE</div>'
        . '<h3 style="font-family:var(--font-display); font-weight:800; font-size:22px; margin:0 0 10px;">Project Three</h3>'
        . '<p style="font-size:14.5px; line-height:1.6; margin:0; opacity:.85;">A brief one-line description of what this project was and its impact.</p>'
        . '</div><!-- /wp:column -->'
        . '<!-- wp:column {"width":"60%"} --><div class="wp-block-column prt-lift" style="flex-basis:60%; background:#fff; border:1.5px solid #ECE6FB; border-radius:24px; padding:34px; color:#17151F;">'
        . '<div style="font-family:var(--font-mono); font-size:12px; color:#7C75A8; margin-bottom:12px;">PROJECT FOUR</div>'
        . '<h3 style="font-family:var(--font-display); font-weight:800; font-size:26px; margin:0 0 10px;">Project Four</h3>'
        . '<p style="font-size:15.5px; line-height:1.6; margin:0 0 16px; color:#5A5676;">A brief one-line description of what this project was and its impact — replace with your own case study summary.</p>'
        . '<p style="font-family:var(--font-mono); font-size:12.5px; color:#7C75A8; margin:0;">[Tool/skill] · [Tool/skill]</p>'
        . '</div><!-- /wp:column -->'
        . '</div><!-- /wp:columns -->';

    $projectsB .= $wrap($h(2, 'Selected work', 'x-large'), '40px', '10px');
    $projectsB .= $wrap("<!-- wp:html -->\n" . $bento . "\n<!-- /wp:html -->", '0px', '10px');

    $projectsB .= $dyn('cta-band', [
        'heading' => 'Want results like these?',
        'body'    => "I'm currently taking on new freelance projects — let's talk about what you're building.",
        'btnText' => 'Start a project →',
        'btnUrl'  => '/contact/',
        'variant' => 'dark',
    ]);
    $patterns['prt-site/freelance-projects-b'] = [
        'title'       => __('Freelance — Projects (bento showcase, stat-led)', 'pressroot'),
        'description' => __('Stat strip lead-in, then an asymmetric colored bento showcase of 4 sample projects — bolder, proof-forward tone.', 'pressroot'),
        'content'     => $projectsB,
    ];

    /* ── Register ────────────────────────────────────────────────────── */
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
