<?php

namespace App;

/**
 * Dedicated block patterns for the "Blog / Content site" Pressroot AI
 * site type (see prt_site_types() in app/ai-assistant.php).
 *
 * Unlike the other site types — which currently reuse the generic full-page
 * patterns from page-patterns.php — this site type gets its own tailored,
 * editorial/warm-toned patterns built for a writing-first personal or niche
 * content site. Paired with the theme's "Warm Sand" Style Kit, these lean
 * into generous whitespace, a serif accent for headlines/pull-quotes, warm
 * neutral tones (paper/cream/ink/orange), soft rounded cards, and the
 * .prt-lift hover-lift class — rather than the bolder agency aesthetic used
 * elsewhere in the theme.
 *
 * Two pages, two variants each, four patterns total:
 *   - prt-site/blog-index-a, prt-site/blog-index-b  (Blog archive/index)
 *   - prt-site/blog-about-a, prt-site/blog-about-b  (About/author bio)
 *
 * Registered under the 'prt-site-types' pattern category. That category is
 * registered centrally elsewhere — this file intentionally does NOT call
 * register_block_pattern_category() itself.
 */

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

    $dyn = function (string $name, array $attrs) use ($wrap): string {
        return $wrap('<!-- wp:prt/' . $name . ' ' . wp_json_encode($attrs) . ' /-->', '20px', '20px');
    };

    $patterns = [];

    /* ════════════════════ BLOG INDEX — Variant A ═══════════════════════
     * Centered editorial intro: eyebrow, serif headline, lead paragraph,
     * a "what I write about" topic-tag row — then the live post grid.
     */
    $topicsA = '<div style="display:flex; flex-wrap:wrap; gap:10px; justify-content:center; margin-top:22px;">';
    foreach (['Design', 'Technology', 'Slow living', 'Craft & making'] as $topic) {
        $topicsA .= '<span style="display:inline-block; background:#F3EEFE; color:#17151F; border:1.5px solid #ECE6FB; border-radius:999px; padding:8px 18px; font-family:var(--font-display); font-size:14px; font-weight:600;">' . esc_html($topic) . '</span>';
    }
    $topicsA .= '</div>';

    $indexA = $wrap(
        '<div style="text-align:center; max-width:760px; margin:0 auto;">'
        . $eyebrow('The Journal')
        . '<!-- wp:heading {"level":1,"style":{"typography":{"fontSize":"clamp(40px, 6vw, 68px)","lineHeight":"1.08","fontFamily":"var(--font-serif)"}}} --><h1 class="wp-block-heading" style="font-size:clamp(40px, 6vw, 68px);line-height:1.08;font-family:var(--font-serif)">Notes on [Your Topic]<br>from [Your Name].</h1><!-- /wp:heading -->'
        . $p('A newsletter and journal about design, technology, and slow living &#8212; for people who want to build a thoughtful life and a thoughtful career, one honest essay at a time.', 'large', 'muted')
        . $topicsA
        . '</div>',
        '76px', '30px'
    );
    $indexA .= $wrap($h(2, 'Recent posts', 'x-large') . $p('Fresh from the archive &#8212; start anywhere.', 'medium', 'muted'), '10px', '10px');
    $indexA .= $dyn('post-grid', ['postType' => 'post', 'count' => 6, 'columns' => 3, 'showImage' => true, 'showExcerpt' => true, 'showDate' => true, 'showCategory' => true]);
    $patterns['prt-site/blog-index-a'] = [
        'title'       => __('Blog Index — Editorial intro + grid', 'pressroot'),
        'description' => __('Centered serif headline, lead paragraph, and topic tags above a live 3-column post grid.', 'pressroot'),
        'content'     => $indexA,
    ];

    /* ════════════════════ BLOG INDEX — Variant B ═══════════════════════
     * Split layout: philosophy/pull-quote statement on one side, a
     * "what you'll find here" list on the other — then the live post grid.
     */
    $findHereB = '<!-- wp:list --><ul class="wp-block-list">'
        . '<!-- wp:list-item --><li>Long-form essays on design, technology, and slow living.</li><!-- /wp:list-item -->'
        . '<!-- wp:list-item --><li>Field notes from projects, experiments, and things I&#8217;m making.</li><!-- /wp:list-item -->'
        . '<!-- wp:list-item --><li>The occasional reading list &#8212; books, links, and other people&#8217;s good ideas.</li><!-- /wp:list-item -->'
        . '<!-- wp:list-item --><li>No noise, no growth-hacking &#8212; just what I&#8217;m actually thinking about.</li><!-- /wp:list-item -->'
        . '</ul><!-- /wp:list -->';

    $splitB = "<!-- wp:columns -->\n<div class=\"wp-block-columns\">"
        . "<!-- wp:column {\"width\":\"58%\"} -->\n<div class=\"wp-block-column\" style=\"flex-basis:58%\">"
        . $eyebrow('[Your Site Name]')
        . '<!-- wp:heading {"level":1,"style":{"typography":{"fontSize":"clamp(34px, 4.5vw, 52px)","lineHeight":"1.15","fontFamily":"var(--font-serif)"}}} --><h1 class="wp-block-heading" style="font-size:clamp(34px, 4.5vw, 52px);line-height:1.15;font-family:var(--font-serif)">Writing is how I think out loud.</h1><!-- /wp:heading -->'
        . '<!-- wp:quote {"className":"is-style-default"} --><blockquote class="wp-block-quote is-style-default"><!-- wp:paragraph {"style":{"typography":{"fontFamily":"var(--font-serif)","fontSize":"26px","lineHeight":"1.4"}},"fontStyle":"italic"} --><p style="font-family:var(--font-serif);font-size:26px;line-height:1.4;font-style:italic">&#8220;I write about design, technology, and slow living &#8212; not to have all the answers, but to think more clearly in public.&#8221;</p><!-- /wp:paragraph --></blockquote><!-- /wp:quote -->'
        . "</div>\n<!-- /wp:column -->"
        . "<!-- wp:column {\"width\":\"42%\"} -->\n<div class=\"wp-block-column\" style=\"flex-basis:42%\">"
        . '<div style="background:#FFF9F5; border:1.5px solid #ECE6FB; border-radius:24px; padding:34px;">'
        . $h(3, 'What you&#8217;ll find here', 'large')
        . $findHereB
        . '</div>'
        . "</div>\n<!-- /wp:column -->"
        . "</div>\n<!-- /wp:columns -->";

    $indexB = $wrap($splitB, '70px', '30px');
    $indexB .= $wrap($h(2, 'Latest', 'x-large') . $p('New here? These are the most recent posts &#8212; jump in.', 'medium', 'muted'), '10px', '10px');
    $indexB .= $dyn('post-grid', ['postType' => 'post', 'count' => 6, 'columns' => 3, 'showImage' => true, 'showExcerpt' => true, 'showDate' => true, 'showCategory' => true]);
    $patterns['prt-site/blog-index-b'] = [
        'title'       => __('Blog Index — Split philosophy + grid', 'pressroot'),
        'description' => __('Two-column layout with a pull-quote philosophy statement and a "what you\'ll find here" list, above a live 3-column post grid.', 'pressroot'),
        'content'     => $indexB,
    ];

    /* ════════════════════ ABOUT / AUTHOR — Variant A ═══════════════════
     * Centered warm bio intro, serif headline, "what to expect" copy,
     * then a newsletter-style CTA band.
     */
    $aboutA = $wrap(
        '<div style="text-align:center; max-width:720px; margin:0 auto;">'
        . $eyebrow('About the writer')
        . '<!-- wp:heading {"level":1,"style":{"typography":{"fontSize":"clamp(36px, 5vw, 56px)","lineHeight":"1.1","fontFamily":"var(--font-serif)"}}} --><h1 class="wp-block-heading" style="font-size:clamp(36px, 5vw, 56px);line-height:1.1;font-family:var(--font-serif)">Hi, I&#8217;m [Your Name].</h1><!-- /wp:heading -->'
        . $p("I write about design, technology, and slow living &#8212; the small decisions that add up to a life and a career you actually want. This started as a way to think out loud, and it&#8217;s slowly become a little corner of the internet for people who care about the same things I do.", 'large', 'body')
        . '</div>',
        '70px', '20px'
    );
    $aboutA .= $wrap(
        $h(2, 'What to expect', 'x-large')
        . $p('Roughly every week or two, I share something worth reading &#8212; an essay, a field note, or a short list of things I&#8217;ve been paying attention to. No sponsored posts, no growth hacks, just honest writing.', 'medium', 'muted'),
        '30px', '10px'
    );
    $aboutA .= $dyn('cta-band', [
        'heading' => 'Get new posts in your inbox',
        'body'    => "Join the newsletter for design, technology, and slow living &#8212; delivered every couple of weeks, unsubscribe any time.",
        'btnText' => 'Subscribe →',
        'btnUrl'  => '/contact/',
        'variant' => 'dark',
    ]);
    $patterns['prt-site/blog-about-a'] = [
        'title'       => __('About / Author — Centered bio + newsletter CTA', 'pressroot'),
        'description' => __('Centered serif headline and warm personal bio, an expectations section, and a newsletter-signup CTA band.', 'pressroot'),
        'content'     => $aboutA,
    ];

    /* ════════════════════ ABOUT / AUTHOR — Variant B ═══════════════════
     * Split layout: photo-placeholder card + bio on one side, a "topics
     * & background" fact list on the other, then a newsletter CTA band.
     */
    $factsB = '<!-- wp:list --><ul class="wp-block-list">'
        . '<!-- wp:list-item --><li><strong>Based in:</strong> [Your City]</li><!-- /wp:list-item -->'
        . '<!-- wp:list-item --><li><strong>Writing since:</strong> [Year]</li><!-- /wp:list-item -->'
        . '<!-- wp:list-item --><li><strong>Usually writing about:</strong> design, technology, and slow living</li><!-- /wp:list-item -->'
        . '<!-- wp:list-item --><li><strong>Also find me:</strong> [social / newsletter link]</li><!-- /wp:list-item -->'
        . '</ul><!-- /wp:list -->';

    $splitAboutB = "<!-- wp:columns -->\n<div class=\"wp-block-columns\">"
        . "<!-- wp:column {\"width\":\"55%\"} -->\n<div class=\"wp-block-column\" style=\"flex-basis:55%\">"
        . $eyebrow('About')
        . '<!-- wp:heading {"level":1,"style":{"typography":{"fontSize":"clamp(32px, 4vw, 46px)","lineHeight":"1.15","fontFamily":"var(--font-serif)"}}} --><h1 class="wp-block-heading" style="font-size:clamp(32px, 4vw, 46px);line-height:1.15;font-family:var(--font-serif)">The person behind the words.</h1><!-- /wp:heading -->'
        . $p("[Your Name] here. By day I [do your day job / craft], and in the margins I write about design, technology, and slow living &#8212; trying to notice things more carefully and share what I learn along the way.", 'medium', 'body')
        . $p("If you're new: start with whatever post title catches your eye. There's no required reading order here.", 'medium', 'muted')
        . "</div>\n<!-- /wp:column -->"
        . "<!-- wp:column {\"width\":\"45%\"} -->\n<div class=\"wp-block-column\" style=\"flex-basis:45%\">"
        . '<div style="background:#F3EEFE; border-radius:24px; padding:34px;">'
        . '<div style="width:100%; aspect-ratio:1/1; border-radius:18px; background:#FFF9F5; border:1.5px dashed #ECE6FB; display:flex; align-items:center; justify-content:center; font-family:var(--font-display); font-size:14px; color:#7C75A8; margin-bottom:22px;">[Your photo here]</div>'
        . $h(3, 'The short version', 'large')
        . $factsB
        . '</div>'
        . "</div>\n<!-- /wp:column -->"
        . "</div>\n<!-- /wp:columns -->";

    $aboutB = $wrap($splitAboutB, '70px', '20px');
    $aboutB .= $dyn('cta-band', [
        'heading' => 'Never miss a new post',
        'body'    => 'A short, honest newsletter about design, technology, and slow living &#8212; straight to your inbox, no spam.',
        'btnText' => 'Sign up →',
        'btnUrl'  => '/contact/',
        'variant' => 'light',
    ]);
    $patterns['prt-site/blog-about-b'] = [
        'title'       => __('About / Author — Split bio + facts + newsletter CTA', 'pressroot'),
        'description' => __('Two-column layout with a photo placeholder and quick-facts list beside the bio copy, plus a newsletter CTA band.', 'pressroot'),
        'content'     => $aboutB,
    ];

    /* ── Register ─────────────────────────────────────────────────────── */
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
