<?php

/**
 * Expanded, categorized "section" pattern library on top of the base patterns in
 * blocks.php / patterns-extra.php. Adds organized categories so the inserter
 * groups sections by purpose (heroes, CTAs, content, social proof, dev).
 */

namespace App;

add_action('init', function () {
    if (! function_exists('register_block_pattern_category') || ! function_exists('register_block_pattern')) {
        return;
    }

    foreach ([
        'prt-heroes'      => __('Pressroot · Heroes', 'pressroot'),
        'prt-cta'         => __('Pressroot · Call to action', 'pressroot'),
        'prt-content'     => __('Pressroot · Content', 'pressroot'),
        'prt-socialproof' => __('Pressroot · Social proof', 'pressroot'),
        'prt-dev'         => __('Pressroot · Developer', 'pressroot'),
    ] as $slug => $label) {
        register_block_pattern_category($slug, ['label' => $label]);
    }

    $patterns = [];

    $patterns['pressroot/hero-centered-minimal'] = ['title' => __('Hero — centered minimal', 'pressroot'), 'categories' => ['prt-heroes', 'pressroot'], 'content' => <<<'HTML'
<!-- wp:group {"className":"prt-hero","layout":{"type":"constrained"}} -->
<div class="wp-block-group prt-hero"><!-- wp:heading {"textAlign":"center","level":1} -->
<h1 class="wp-block-heading has-text-align-center">A short, bold statement about what you do</h1>
<!-- /wp:heading -->
<!-- wp:paragraph {"align":"center"} -->
<p class="has-text-align-center">One clarifying sentence underneath. Keep it to a single idea.</p>
<!-- /wp:paragraph -->
<!-- wp:buttons {"layout":{"type":"flex","justifyContent":"center"}} -->
<div class="wp-block-buttons"><!-- wp:button --><div class="wp-block-button"><a class="wp-block-button__link wp-element-button">Primary action</a></div><!-- /wp:button --></div>
<!-- /wp:buttons --></div>
<!-- /wp:group -->
HTML];

    $patterns['pressroot/hero-dev'] = ['title' => __('Hero — developer (with repos)', 'pressroot'), 'categories' => ['prt-heroes', 'prt-dev', 'pressroot'], 'content' => <<<'HTML'
<!-- wp:group {"className":"prt-hero","layout":{"type":"constrained"}} -->
<div class="wp-block-group prt-hero"><!-- wp:paragraph {"align":"center","className":"prt-eyebrow"} -->
<p class="has-text-align-center prt-eyebrow">OPEN SOURCE · BUILDING IN PUBLIC</p>
<!-- /wp:paragraph -->
<!-- wp:heading {"textAlign":"center","level":1} -->
<h1 class="wp-block-heading has-text-align-center">I build tools developers actually use</h1>
<!-- /wp:heading -->
<!-- wp:prt/repo-grid {"count":4,"columns":2} /--></div>
<!-- /wp:group -->
HTML];

    $patterns['pressroot/cta-split'] = ['title' => __('CTA — split', 'pressroot'), 'categories' => ['prt-cta', 'pressroot'], 'content' => <<<'HTML'
<!-- wp:group {"className":"prt-cta-band","layout":{"type":"flex","flexWrap":"wrap","justifyContent":"space-between","verticalAlignment":"center"}} -->
<div class="wp-block-group prt-cta-band"><!-- wp:heading {"level":2} -->
<h2 class="wp-block-heading">Ready to start your project?</h2>
<!-- /wp:heading -->
<!-- wp:buttons --><div class="wp-block-buttons"><!-- wp:button --><div class="wp-block-button"><a class="wp-block-button__link wp-element-button">Get in touch</a></div><!-- /wp:button --></div><!-- /wp:buttons --></div>
<!-- /wp:group -->
HTML];

    $patterns['pressroot/about-two-col'] = ['title' => __('About — two column', 'pressroot'), 'categories' => ['prt-content', 'pressroot'], 'content' => <<<'HTML'
<!-- wp:columns {"verticalAlignment":"center","className":"prt-about"} -->
<div class="wp-block-columns are-vertically-aligned-center prt-about"><!-- wp:column {"verticalAlignment":"center","width":"40%"} -->
<div class="wp-block-column is-vertically-aligned-center" style="flex-basis:40%"><!-- wp:image {"className":"is-style-rounded"} -->
<figure class="wp-block-image is-style-rounded"><img alt=""/></figure>
<!-- /wp:image --></div>
<!-- /wp:column -->
<!-- wp:column {"verticalAlignment":"center"} -->
<div class="wp-block-column is-vertically-aligned-center"><!-- wp:heading {"level":2} -->
<h2 class="wp-block-heading">About</h2>
<!-- /wp:heading -->
<!-- wp:paragraph --><p>Two or three sentences about who you are, what you build, and why it matters. Keep it human and specific.</p><!-- /wp:paragraph -->
<!-- wp:buttons --><div class="wp-block-buttons"><!-- wp:button {"className":"is-style-outline"} --><div class="wp-block-button is-style-outline"><a class="wp-block-button__link wp-element-button">More about me</a></div><!-- /wp:button --></div><!-- /wp:buttons --></div>
<!-- /wp:column --></div>
<!-- /wp:columns -->
HTML];

    $patterns['pressroot/services-three'] = ['title' => __('Services — three cards', 'pressroot'), 'categories' => ['prt-content', 'pressroot'], 'content' => <<<'HTML'
<!-- wp:columns {"className":"prt-feature-grid"} -->
<div class="wp-block-columns prt-feature-grid"><!-- wp:column {"className":"is-style-prt-card"} -->
<div class="wp-block-column is-style-prt-card"><!-- wp:heading {"level":3} --><h3 class="wp-block-heading">Web design</h3><!-- /wp:heading --><!-- wp:paragraph --><p>Clean, fast, accessible sites built to last.</p><!-- /wp:paragraph --></div>
<!-- /wp:column -->
<!-- wp:column {"className":"is-style-prt-card"} -->
<div class="wp-block-column is-style-prt-card"><!-- wp:heading {"level":3} --><h3 class="wp-block-heading">Development</h3><!-- /wp:heading --><!-- wp:paragraph --><p>WordPress themes, web apps, and integrations.</p><!-- /wp:paragraph --></div>
<!-- /wp:column -->
<!-- wp:column {"className":"is-style-prt-card"} -->
<div class="wp-block-column is-style-prt-card"><!-- wp:heading {"level":3} --><h3 class="wp-block-heading">Consulting</h3><!-- /wp:heading --><!-- wp:paragraph --><p>Architecture, performance, and Power Platform.</p><!-- /wp:paragraph --></div>
<!-- /wp:column --></div>
<!-- /wp:columns -->
HTML];

    $patterns['pressroot/stats-four'] = ['title' => __('Stats — four up', 'pressroot'), 'categories' => ['prt-socialproof', 'pressroot'], 'content' => <<<'HTML'
<!-- wp:columns {"className":"prt-stat-strip"} -->
<div class="wp-block-columns prt-stat-strip"><!-- wp:column --><div class="wp-block-column"><!-- wp:heading {"textAlign":"center","level":3} --><h3 class="wp-block-heading has-text-align-center">12+</h3><!-- /wp:heading --><!-- wp:paragraph {"align":"center"} --><p class="has-text-align-center">Years</p><!-- /wp:paragraph --></div><!-- /wp:column -->
<!-- wp:column --><div class="wp-block-column"><!-- wp:heading {"textAlign":"center","level":3} --><h3 class="wp-block-heading has-text-align-center">40+</h3><!-- /wp:heading --><!-- wp:paragraph {"align":"center"} --><p class="has-text-align-center">Projects</p><!-- /wp:paragraph --></div><!-- /wp:column -->
<!-- wp:column --><div class="wp-block-column"><!-- wp:heading {"textAlign":"center","level":3} --><h3 class="wp-block-heading has-text-align-center">2</h3><!-- /wp:heading --><!-- wp:paragraph {"align":"center"} --><p class="has-text-align-center">Products</p><!-- /wp:paragraph --></div><!-- /wp:column -->
<!-- wp:column --><div class="wp-block-column"><!-- wp:heading {"textAlign":"center","level":3} --><h3 class="wp-block-heading has-text-align-center">100%</h3><!-- /wp:heading --><!-- wp:paragraph {"align":"center"} --><p class="has-text-align-center">Open source</p><!-- /wp:paragraph --></div><!-- /wp:column --></div>
<!-- /wp:columns -->
HTML];

    $patterns['pressroot/testimonial-single'] = ['title' => __('Testimonial — single large', 'pressroot'), 'categories' => ['prt-socialproof', 'pressroot'], 'content' => <<<'HTML'
<!-- wp:group {"className":"prt-quote-lg","layout":{"type":"constrained","contentSize":"720px"}} -->
<div class="wp-block-group prt-quote-lg"><!-- wp:quote {"className":"is-style-prt-card"} -->
<blockquote class="wp-block-quote is-style-prt-card"><!-- wp:paragraph {"align":"center"} --><p class="has-text-align-center">"A genuinely standout testimonial that's specific about the result and easy to believe."</p><!-- /wp:paragraph --><cite>Client name — Company</cite></blockquote>
<!-- /wp:quote --></div>
<!-- /wp:group -->
HTML];

    $patterns['pressroot/contact-cta'] = ['title' => __('Contact CTA — with socials', 'pressroot'), 'categories' => ['prt-cta', 'pressroot'], 'content' => <<<'HTML'
<!-- wp:group {"className":"prt-cta-band","layout":{"type":"constrained"}} -->
<div class="wp-block-group prt-cta-band"><!-- wp:heading {"textAlign":"center","level":2} -->
<h2 class="wp-block-heading has-text-align-center">Let's build something</h2>
<!-- /wp:heading -->
<!-- wp:paragraph {"align":"center"} --><p class="has-text-align-center">Find me on the platforms below, or send a message.</p><!-- /wp:paragraph -->
<!-- wp:prt/social-links {"align":"center","shape":"circle"} /--></div>
<!-- /wp:group -->
HTML];

    foreach ($patterns as $name => $args) {
        register_block_pattern($name, $args);
    }
});

add_action('prt_head_end', function () {
    echo "\n<style id=\"prt-sections\">.prt-about .wp-block-image img{border-radius:16px;}.prt-quote-lg blockquote{font-size:22px;}.prt-quote-lg cite{display:block;text-align:center;margin-top:12px;}</style>\n";
}, 13);
