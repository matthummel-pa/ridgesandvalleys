<?php

/**
 * Custom code injection — header (<head>), below-body (after <body>), and
 * footer (before </body>). For analytics, search-console verification tags,
 * tag-manager snippets, chat widgets, and the like.
 *
 * Fields live in Customizer → Theme Options → Custom Code. They output RAW,
 * unescaped code on every page, so only trusted admins (edit_theme_options)
 * should edit them.
 */

namespace App;

if (! defined('ABSPATH')) {
    exit;
}

/**
 * Raw passthrough "sanitizer" for the code fields. These hold <script>, <meta>,
 * and <noscript> markup, so wp_kses/escaping would break them. Customizer
 * access already requires the edit_theme_options capability.
 */
function rv_code_keep_raw($value): string
{
    return (string) $value;
}

/** The three injection slots: setting id => [label, description]. */
function rv_code_slots(): array
{
    return [
        'rv_code_head' => [
            __('Header code (before </head>)', 'sage'),
            __('Injected inside <head> on every page — verification meta tags, analytics, fonts.', 'sage'),
        ],
        'rv_code_body_open' => [
            __('Below body code (after <body>)', 'sage'),
            __('Injected right after the opening <body> tag — e.g. Google Tag Manager noscript.', 'sage'),
        ],
        'rv_code_footer' => [
            __('Footer code (before </body>)', 'sage'),
            __('Injected just before </body> on every page — chat widgets, deferred scripts.', 'sage'),
        ],
    ];
}

add_action('customize_register', function ($wp_customize) {
    if (! $wp_customize->get_panel('rv_theme_options')) {
        $wp_customize->add_panel('rv_theme_options', [
            'title'    => __('Theme Options', 'sage'),
            'priority' => 20,
        ]);
    }

    $wp_customize->add_section('rv_custom_code', [
        'title'       => __('Custom Code', 'sage'),
        'panel'       => 'rv_theme_options',
        'priority'    => 200,
        'description' => __('Paste code to inject site-wide. Outputs raw HTML/JavaScript — add only code you trust.', 'sage'),
    ]);

    foreach (rv_code_slots() as $id => $slot) {
        [$label, $desc] = $slot;
        $wp_customize->add_setting($id, [
            'default'           => '',
            'type'              => 'theme_mod',
            'transport'         => 'refresh',
            'sanitize_callback' => __NAMESPACE__ . '\\rv_code_keep_raw',
        ]);
        $wp_customize->add_control($id, [
            'label'       => $label,
            'description' => $desc,
            'section'     => 'rv_custom_code',
            'type'        => 'textarea',
            'input_attrs' => [
                'rows'        => 6,
                'spellcheck'  => 'false',
                'style'       => 'font-family:ui-monospace,SFMono-Regular,Menlo,Consolas,monospace;white-space:pre;',
                'placeholder' => '<!-- Paste code here, e.g. <script>...</script> -->',
            ],
        ]);
    }
}, 30);

/** Echo a slot's raw code if it is set. */
function rv_code_emit(string $id): void
{
    $code = trim((string) get_theme_mod($id, ''));
    if ($code !== '') {
        // Raw by design — this is a code-injection field for trusted admins.
        echo "\n" . $code . "\n"; // phpcs:ignore WordPress.Security.EscapeOutput.OutputNotEscaped
    }
}

add_action('wp_head', function () {
    rv_code_emit('rv_code_head');
}, 100);

add_action('wp_body_open', function () {
    rv_code_emit('rv_code_body_open');
}, 10);

add_action('wp_footer', function () {
    rv_code_emit('rv_code_footer');
}, 100);
