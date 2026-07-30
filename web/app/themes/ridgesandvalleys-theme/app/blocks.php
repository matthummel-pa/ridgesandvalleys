<?php

/**
 * Custom blocks.
 *
 * Dynamic (server-rendered) blocks: PHP owns the markup via render_callback,
 * the editor UI is a TypeScript component in resources/js/blocks/* registered
 * from resources/js/editor.ts. This is the reusable block-dev pattern for a
 * classic Sage theme — no FSE required.
 */

namespace App;

add_action('init', function () {
    register_block_type('rv/ridgeline-cta', [
        'api_version'     => 3,
        'title'           => __('Ridgeline CTA', 'sage'),
        'category'        => 'design',
        'icon'            => 'megaphone',
        'description'     => __('A full-width call-to-action band with the ridgeline gradient.', 'sage'),
        'keywords'        => ['cta', 'call to action', 'ridge'],
        'supports'        => ['html' => false, 'align' => ['full']],
        'attributes'      => [
            'heading'     => ['type' => 'string', 'default' => __('Ready to be easy to find?', 'sage')],
            'text'        => ['type' => 'string', 'default' => __('Tell me about your business. I\'ll tell you exactly what I\'d do — no jargon, no pressure.', 'sage')],
            'buttonLabel' => ['type' => 'string', 'default' => __('Get a quote', 'sage')],
            'buttonUrl'   => ['type' => 'string', 'default' => '/contact/'],
        ],
        'render_callback' => __NAMESPACE__ . '\\render_ridgeline_cta',
    ]);
});

/**
 * Server render for rv/ridgeline-cta.
 *
 * @param array $attr
 */
function render_ridgeline_cta($attr): string
{
    $heading = isset($attr['heading']) ? $attr['heading'] : '';
    $text    = isset($attr['text']) ? $attr['text'] : '';
    $label   = isset($attr['buttonLabel']) ? $attr['buttonLabel'] : '';
    $url     = isset($attr['buttonUrl']) ? $attr['buttonUrl'] : '/contact/';
    $href    = cta_href($url);

    $wrapper = get_block_wrapper_attributes(['class' => 'rv-cta-band alignfull']);

    ob_start();
    ?>
    <section <?php echo $wrapper; // phpcs:ignore ?>>
        <div class="rv-shell rv-cta-inner">
            <?php if ($heading) : ?>
                <h2 class="rv-cta-title"><?php echo esc_html($heading); ?></h2>
            <?php endif; ?>
            <?php if ($text) : ?>
                <p class="rv-cta-sub"><?php echo esc_html($text); ?></p>
            <?php endif; ?>
            <?php if ($label) : ?>
                <a class="rv-btn rv-btn-on-dark" href="<?php echo esc_url($href); ?>"><?php echo esc_html($label); ?></a>
            <?php endif; ?>
        </div>
    </section>
    <?php
    return (string) ob_get_clean();
}
