<?php

/**
 * Block patterns (Callout, CTA band, Stat strip, FAQ) + a "Card" block style.
 * Composed from core blocks and styled by the theme's design tokens.
 */

namespace App;

add_action('init', function () {
    if (function_exists('register_block_pattern_category')) {
        register_block_pattern_category('pressroot', ['label' => __('Pressroot', 'pressroot')]);
    }
    if (! function_exists('register_block_pattern')) {
        return;
    }

    register_block_pattern('pressroot/callout', [
        'title'      => __('Callout', 'pressroot'),
        'categories' => ['pressroot'],
        'content'    => <<<'HTML'
<!-- wp:group {"className":"prt-callout"} -->
<div class="wp-block-group prt-callout"><!-- wp:paragraph -->
<p><strong>Heads up:</strong> Use this callout to highlight an important note, tip, or warning.</p>
<!-- /wp:paragraph --></div>
<!-- /wp:group -->
HTML,
    ]);

    register_block_pattern('pressroot/cta-band', [
        'title'      => __('CTA band', 'pressroot'),
        'categories' => ['pressroot'],
        'content'    => <<<'HTML'
<!-- wp:group {"className":"prt-cta-band","layout":{"type":"constrained"}} -->
<div class="wp-block-group prt-cta-band"><!-- wp:heading {"textAlign":"center"} -->
<h2 class="wp-block-heading has-text-align-center">Have a project in mind?</h2>
<!-- /wp:heading -->
<!-- wp:paragraph {"align":"center"} -->
<p class="has-text-align-center">Tell me what you're building and let's talk.</p>
<!-- /wp:paragraph -->
<!-- wp:buttons {"layout":{"type":"flex","justifyContent":"center"}} -->
<div class="wp-block-buttons"><!-- wp:button -->
<div class="wp-block-button"><a class="wp-block-button__link wp-element-button">Start a conversation</a></div>
<!-- /wp:button --></div>
<!-- /wp:buttons --></div>
<!-- /wp:group -->
HTML,
    ]);

    register_block_pattern('pressroot/stat-strip', [
        'title'      => __('Stat strip', 'pressroot'),
        'categories' => ['pressroot'],
        'content'    => <<<'HTML'
<!-- wp:columns {"className":"prt-stat-strip"} -->
<div class="wp-block-columns prt-stat-strip"><!-- wp:column -->
<div class="wp-block-column"><!-- wp:heading {"textAlign":"center","level":3} -->
<h3 class="wp-block-heading has-text-align-center">12+</h3>
<!-- /wp:heading -->
<!-- wp:paragraph {"align":"center"} -->
<p class="has-text-align-center">Years building</p>
<!-- /wp:paragraph --></div>
<!-- /wp:column -->
<!-- wp:column -->
<div class="wp-block-column"><!-- wp:heading {"textAlign":"center","level":3} -->
<h3 class="wp-block-heading has-text-align-center">30+</h3>
<!-- /wp:heading -->
<!-- wp:paragraph {"align":"center"} -->
<p class="has-text-align-center">Projects shipped</p>
<!-- /wp:paragraph --></div>
<!-- /wp:column -->
<!-- wp:column -->
<div class="wp-block-column"><!-- wp:heading {"textAlign":"center","level":3} -->
<h3 class="wp-block-heading has-text-align-center">2</h3>
<!-- /wp:heading -->
<!-- wp:paragraph {"align":"center"} -->
<p class="has-text-align-center">Open-source tools</p>
<!-- /wp:paragraph --></div>
<!-- /wp:column --></div>
<!-- /wp:columns -->
HTML,
    ]);

    register_block_pattern('pressroot/faq', [
        'title'      => __('FAQ', 'pressroot'),
        'categories' => ['pressroot'],
        'content'    => <<<'HTML'
<!-- wp:group {"className":"prt-faq"} -->
<div class="wp-block-group prt-faq"><!-- wp:details -->
<details class="wp-block-details"><summary>What do you build?</summary><!-- wp:paragraph -->
<p>Websites, WordPress themes, and Power Platform solutions.</p>
<!-- /wp:paragraph --></details>
<!-- /wp:details -->
<!-- wp:details -->
<details class="wp-block-details"><summary>How can we work together?</summary><!-- wp:paragraph -->
<p>Reach out via the contact page and tell me about your project.</p>
<!-- /wp:paragraph --></details>
<!-- /wp:details --></div>
<!-- /wp:group -->
HTML,
    ]);
});

/** "Card" block style for groups + columns. */
add_action('init', function () {
    if (! function_exists('register_block_style')) {
        return;
    }
    register_block_style('core/group', ['name' => 'prt-card', 'label' => __('Card', 'pressroot')]);
    register_block_style('core/column', ['name' => 'prt-card', 'label' => __('Card', 'pressroot')]);
});
