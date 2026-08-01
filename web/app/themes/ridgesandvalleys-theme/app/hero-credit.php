<?php

/**
 * Hero image credit / caption.
 *
 * Adds a small "Hero image credit" box to the page/post editor (a line of
 * credit, title or description text shown in the LOWER-RIGHT of the hero image),
 * a per-page show/hide toggle, and global typography styling in the Customizer
 * (Theme Options → Hero image credit): font, size, weight, letter-spacing,
 * case, and colour.
 *
 * Self-contained: the text is stored as the same rv_f_* post meta the theme's
 * field() helper reads, so nothing in page-fields.php or the Blade templates has
 * to change. The credit is injected into the first .rv-hero on the page, so it
 * works on every template that has a hero without per-template markup.
 *
 * Safety: the credit text is escaped (esc_html + line breaks) before output;
 * every styling value that reaches CSS is whitelisted (font, weight, case) or
 * pattern-validated (size, letter-spacing) or run through sanitize_hex_color().
 */

namespace App;

/* -------------------------------------------------------------------------
 * Per-page editor box: credit text + show/hide.
 * ---------------------------------------------------------------------- */

add_action('add_meta_boxes', function () {
    add_meta_box(
        'rv_hero_credit',
        __('Hero image credit', 'sage'),
        __NAMESPACE__ . '\\hero_credit_meta_box',
        ['page', 'post'],
        'side',
        'low'
    );
});

function hero_credit_meta_box($post): void
{
    wp_nonce_field('rv_hero_credit_save', 'rv_hero_credit_nonce');
    $text = (string) get_post_meta($post->ID, 'rv_f_hero_credit', true);
    $show = (string) get_post_meta($post->ID, 'rv_f_hero_credit_show', true);
    $checked = ($show === '' || $show === '1'); // default: show when text is set
    ?>
    <p style="margin-top:0">
        <label for="rv_f_hero_credit"><?php esc_html_e('Credit, title or description shown in the lower-right of the hero image. Leave blank to show nothing.', 'sage'); ?></label>
    </p>
    <textarea id="rv_f_hero_credit" name="rv_f_hero_credit" rows="3" class="widefat" placeholder="<?php esc_attr_e('e.g. Photo: Cumberland Valley, PA', 'sage'); ?>"><?php echo esc_textarea($text); ?></textarea>
    <p style="margin-bottom:0">
        <label>
            <input type="checkbox" name="rv_f_hero_credit_show" value="1" <?php checked($checked); ?>>
            <?php esc_html_e('Show this credit', 'sage'); ?>
        </label>
    </p>
    <?php
}

add_action('save_post', function ($post_id) {
    if (! isset($_POST['rv_hero_credit_nonce']) || ! wp_verify_nonce(sanitize_key($_POST['rv_hero_credit_nonce']), 'rv_hero_credit_save')) {
        return;
    }
    if (defined('DOING_AUTOSAVE') && DOING_AUTOSAVE) {
        return;
    }
    if (! current_user_can('edit_post', $post_id)) {
        return;
    }
    $text = isset($_POST['rv_f_hero_credit']) ? sanitize_textarea_field(wp_unslash($_POST['rv_f_hero_credit'])) : '';
    update_post_meta($post_id, 'rv_f_hero_credit', $text);
    update_post_meta($post_id, 'rv_f_hero_credit_show', isset($_POST['rv_f_hero_credit_show']) ? '1' : '0');
});

/* -------------------------------------------------------------------------
 * Render: inject the credit into the hero's lower-right.
 * ---------------------------------------------------------------------- */

add_action('wp_footer', function () {
    if (! is_singular()) {
        return;
    }
    $id   = (int) get_queried_object_id();
    $text = (string) get_post_meta($id, 'rv_f_hero_credit', true);
    $show = (string) get_post_meta($id, 'rv_f_hero_credit_show', true);
    if (trim($text) === '' || $show === '0') {
        return;
    }
    // Escaped text with line breaks; only <br> is markup, so this is safe.
    $html = nl2br(esc_html($text));
    echo '<script>(function(){var h=document.querySelector(".rv-hero");if(!h)return;'
        . 'if(h.querySelector(".rv-hero-credit"))return;'
        . 'var d=document.createElement("div");d.className="rv-hero-credit";'
        . 'd.innerHTML=' . wp_json_encode($html) . ';h.appendChild(d);})();</script>' . "\n";
}, 99);

/* -------------------------------------------------------------------------
 * Customizer styling (global) — Theme Options → Hero image credit.
 * ---------------------------------------------------------------------- */

/** Font-family choices for the credit (value => CSS). */
function hero_credit_fonts(): array
{
    return [
        'mono'    => 'var(--font-mono)',
        'display' => 'var(--font-display)',
        'serif'   => 'var(--font-serif)',
        'body'    => 'var(--font-body)',
    ];
}

/** Validate a CSS length (size / letter-spacing); '' if not allowed. */
function hero_credit_len(string $v): string
{
    $v = trim($v);
    if ($v === '') {
        return '';
    }
    if ($v === 'normal') {
        return $v;
    }
    return preg_match('/^-?\d*\.?\d+(px|rem|em|%)$/i', $v) ? $v : '';
}

add_action('customize_register', function ($wp_customize) {
    $wp_customize->add_section('rv_hero_credit', [
        'title' => __('Hero image credit', 'sage'),
        'panel' => 'rv_theme_options',
    ]);
    $sec = 'rv_hero_credit';

    $wp_customize->add_setting('rv_hero_credit_font', ['default' => 'mono', 'sanitize_callback' => 'sanitize_key']);
    $wp_customize->add_control('rv_hero_credit_font', [
        'label'       => __('Font', 'sage'),
        'description' => __('Typeface for the credit text in the lower-right of the hero.', 'sage'),
        'section'     => $sec,
        'type'        => 'select',
        'choices'     => [
            'mono'    => __('JetBrains Mono', 'sage'),
            'display' => __('Outfit (display / sans)', 'sage'),
            'serif'   => __('Instrument Serif', 'sage'),
            'body'    => __('Body', 'sage'),
        ],
    ]);

    $wp_customize->add_setting('rv_hero_credit_size', ['default' => '', 'sanitize_callback' => 'sanitize_text_field']);
    $wp_customize->add_control('rv_hero_credit_size', [
        'label'       => __('Font size', 'sage'),
        'description' => __('e.g. 0.72rem, 11px. Blank uses a small default.', 'sage'),
        'section'     => $sec,
        'type'        => 'text',
    ]);

    $wp_customize->add_setting('rv_hero_credit_weight', ['default' => '', 'sanitize_callback' => 'sanitize_text_field']);
    $wp_customize->add_control('rv_hero_credit_weight', [
        'label'   => __('Weight', 'sage'),
        'section' => $sec,
        'type'    => 'select',
        'choices' => ['' => __('Default', 'sage'), '400' => '400', '500' => '500', '600' => '600', '700' => '700'],
    ]);

    $wp_customize->add_setting('rv_hero_credit_ls', ['default' => '', 'sanitize_callback' => 'sanitize_text_field']);
    $wp_customize->add_control('rv_hero_credit_ls', [
        'label'       => __('Letter-spacing', 'sage'),
        'description' => __('e.g. 0.04em or normal.', 'sage'),
        'section'     => $sec,
        'type'        => 'text',
    ]);

    $wp_customize->add_setting('rv_hero_credit_transform', ['default' => '', 'sanitize_callback' => 'sanitize_key']);
    $wp_customize->add_control('rv_hero_credit_transform', [
        'label'   => __('Text case', 'sage'),
        'section' => $sec,
        'type'    => 'select',
        'choices' => [
            ''          => __('Default', 'sage'),
            'none'      => __('None', 'sage'),
            'uppercase' => __('UPPERCASE', 'sage'),
            'lowercase' => __('lowercase', 'sage'),
            'capitalize' => __('Capitalize', 'sage'),
        ],
    ]);

    $wp_customize->add_setting('rv_hero_credit_color', ['default' => '', 'sanitize_callback' => 'sanitize_hex_color']);
    $wp_customize->add_control(new \WP_Customize_Color_Control($wp_customize, 'rv_hero_credit_color', [
        'label'       => __('Colour', 'sage'),
        'description' => __('Blank uses a soft cream that reads on dark hero images.', 'sage'),
        'section'     => $sec,
    ]));
}, 20);

/** Emit the hero-credit CSS (position + typography). */
add_action('wp_head', function () {
    $fonts      = hero_credit_fonts();
    $weights    = ['400', '500', '600', '700'];
    $transforms = ['none', 'uppercase', 'lowercase', 'capitalize'];

    // Base placement + legible defaults over dark hero imagery.
    $css  = '.rv-hero{position:relative}';
    $css .= '.rv-hero-credit{position:absolute;right:clamp(1rem,3vw,2.25rem);bottom:clamp(.85rem,2.5vw,1.75rem);'
        . 'z-index:3;max-width:60%;text-align:right;line-height:1.3;pointer-events:none;'
        . 'font-family:var(--font-mono);font-size:.72rem;letter-spacing:.03em;'
        . 'color:rgba(247,241,230,.78);text-shadow:0 1px 3px rgba(0,0,0,.45)}';
    $css .= '.rv-hero-credit a{color:inherit;pointer-events:auto}';

    // Customizer overrides.
    $d = [];
    $f = (string) get_theme_mod('rv_hero_credit_font', 'mono');
    if (isset($fonts[$f])) {
        $d[] = 'font-family:' . $fonts[$f];
    }
    $size = hero_credit_len((string) get_theme_mod('rv_hero_credit_size', ''));
    if ($size !== '') {
        $d[] = 'font-size:' . $size;
    }
    $w = (string) get_theme_mod('rv_hero_credit_weight', '');
    if (in_array($w, $weights, true)) {
        $d[] = 'font-weight:' . $w;
    }
    $ls = hero_credit_len((string) get_theme_mod('rv_hero_credit_ls', ''));
    if ($ls !== '') {
        $d[] = 'letter-spacing:' . $ls;
    }
    $tr = (string) get_theme_mod('rv_hero_credit_transform', '');
    if (in_array($tr, $transforms, true)) {
        $d[] = 'text-transform:' . $tr;
    }
    $color = sanitize_hex_color((string) get_theme_mod('rv_hero_credit_color', ''));
    if ($color) {
        $d[] = 'color:' . $color;
    }
    if ($d) {
        $css .= '.rv-hero-credit{' . implode(';', $d) . '}';
    }

    echo '<style id="rv-hero-credit-css">' . $css . "</style>\n";
}, 24);
