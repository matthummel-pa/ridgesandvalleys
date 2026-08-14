<?php

/**
 * Image credits / captions (hero + content photos).
 *
 * Adds an "Image credits" box to the page/post editor:
 *   - Hero image credit: a line shown in the LOWER-RIGHT of the hero banner,
 *     with a show/hide toggle. On the static front page this field lives in the
 *     "Home page layout" box instead (co-located with the hero), so it is
 *     hidden here to avoid a duplicate input; app/home-sections.php saves it.
 *   - Content photo credits: one credit per line, each captioning the next
 *     content photo down the page (every theme photo sits in .rv-split-media),
 *     shown in the lower-right of that photo. Leave a line blank to skip a photo.
 *
 * Global typography styling for ALL of these lives in the Customizer
 * (Theme Options → Image credits): font, size, weight, letter-spacing, case,
 * and colour — applied to hero and content credits alike.
 *
 * Self-contained: text is stored as rv_f_* post meta (the same store the theme's
 * field() helper reads); credits are injected client-side into .rv-hero and each
 * .rv-split-media, so no Blade template or page-fields.php change is needed.
 *
 * Safety: credit text is escaped before output; every styling value that reaches
 * CSS is whitelisted (font, weight, case), pattern-validated (size, spacing) or
 * run through sanitize_hex_color().
 */

namespace App;

/* -------------------------------------------------------------------------
 * Per-page editor box: hero credit + show/hide, and content-photo credits.
 * ---------------------------------------------------------------------- */

add_action('add_meta_boxes', function () {
    add_meta_box(
        'rv_hero_credit',
        __('Image credits', 'sage'),
        __NAMESPACE__ . '\\hero_credit_meta_box',
        ['page', 'post'],
        'side',
        'low'
    );
});

function hero_credit_meta_box($post): void
{
    wp_nonce_field('rv_hero_credit_save', 'rv_hero_credit_nonce');
    $imgs     = (string) get_post_meta($post->ID, 'rv_f_img_credits', true);
    $is_front = (function_exists(__NAMESPACE__ . '\\home_page_id') && home_page_id() === (int) $post->ID);

    if (! $is_front) {
        $text    = (string) get_post_meta($post->ID, 'rv_f_hero_credit', true);
        $show    = (string) get_post_meta($post->ID, 'rv_f_hero_credit_show', true);
        $checked = ($show === '' || $show === '1'); // default: show when text is set
        ?>
        <input type="hidden" name="rv_hero_credit_hero_present" value="1">
        <p style="margin-top:0"><strong><?php esc_html_e('Hero image', 'sage'); ?></strong></p>
        <p style="margin-top:.25rem">
            <label for="rv_f_hero_credit"><?php esc_html_e('Credit / caption shown in the lower-right of the hero banner. Blank shows nothing.', 'sage'); ?></label>
        </p>
        <textarea id="rv_f_hero_credit" name="rv_f_hero_credit" rows="2" class="widefat" placeholder="<?php esc_attr_e('e.g. Photo: Cumberland Valley, PA', 'sage'); ?>"><?php echo esc_textarea($text); ?></textarea>
        <p style="margin-bottom:1rem">
            <label>
                <input type="checkbox" name="rv_f_hero_credit_show" value="1" <?php checked($checked); ?>>
                <?php esc_html_e('Show hero credit', 'sage'); ?>
            </label>
        </p>
        <?php
    } else {
        ?>
        <p style="margin-top:0"><strong><?php esc_html_e('Hero image', 'sage'); ?></strong></p>
        <p class="description" style="margin:.25rem 0 1rem"><?php esc_html_e('The hero banner’s lower-right caption is set under “Home page layout” → Hero image (banner).', 'sage'); ?></p>
        <?php
    }
    ?>
    <p style="margin-top:0"><strong><?php esc_html_e('Content photos', 'sage'); ?></strong></p>
    <p style="margin-top:.25rem">
        <label for="rv_f_img_credits"><?php esc_html_e('One credit per line — each line captions the next photo down the page, in the lower-right of that photo. Leave a line blank to skip a photo.', 'sage'); ?></label>
    </p>
    <textarea id="rv_f_img_credits" name="rv_f_img_credits" rows="4" class="widefat" placeholder="<?php esc_attr_e("Photo 1 credit\nPhoto 2 credit", 'sage'); ?>"><?php echo esc_textarea($imgs); ?></textarea>
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

    // Hero credit is edited here only when its own input was rendered (i.e. not
    // the front page). On the front page it lives in the "Home page layout" box,
    // so touching it here would clobber that value — skip it.
    if (isset($_POST['rv_hero_credit_hero_present'])) {
        $text = isset($_POST['rv_f_hero_credit']) ? sanitize_textarea_field(wp_unslash($_POST['rv_f_hero_credit'])) : '';
        update_post_meta($post_id, 'rv_f_hero_credit', $text);
        update_post_meta($post_id, 'rv_f_hero_credit_show', isset($_POST['rv_f_hero_credit_show']) ? '1' : '0');
    }

    $imgs = isset($_POST['rv_f_img_credits']) ? sanitize_textarea_field(wp_unslash($_POST['rv_f_img_credits'])) : '';
    update_post_meta($post_id, 'rv_f_img_credits', $imgs);
});

/* -------------------------------------------------------------------------
 * Render: inject credits into the hero and each content photo, lower-right.
 * ---------------------------------------------------------------------- */

add_action('wp_footer', function () {
    if (! is_singular() || is_front_page()) {
        return;
    }
    $id = (int) get_queried_object_id();

    $lines = preg_split('/\r\n|\r|\n/', (string) get_post_meta($id, 'rv_f_img_credits', true)) ?: [];
    $imgHtml = array_map(static fn ($l) => trim($l) === '' ? '' : esc_html(trim($l)), $lines);

    if (! array_filter($imgHtml)) {
        return;
    }

    $add = 'function(host,html){if(!host||!html||host.querySelector(".rv-img-credit,.rv-hero-credit"))return;'
        . 'var c=host.classList.contains("rv-hero")?"rv-hero-credit":"rv-img-credit";'
        . 'var d=document.createElement("div");d.className=c;d.innerHTML=html;host.appendChild(d);}';

    echo '<script>(function(){var add=' . $add . ';'
        . 'var creds=' . wp_json_encode(array_values($imgHtml)) . ';'
        . 'var m=document.querySelectorAll(".rv-split-media");'
        . 'for(var i=0;i<m.length;i++){add(m[i],creds[i]||"");}})();</script>' . "\n";
}, 99);

/* -------------------------------------------------------------------------
 * Customizer styling (global) — Theme Options → Image credits.
 * ---------------------------------------------------------------------- */

/** Font-family choices for credits (value => CSS). */
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
        'title' => __('Image credits', 'sage'),
        'panel' => 'rv_theme_options',
    ]);
    $sec = 'rv_hero_credit';

    $wp_customize->add_setting('rv_hero_credit_font', ['default' => 'mono', 'sanitize_callback' => 'sanitize_key']);
    $wp_customize->add_control('rv_hero_credit_font', [
        'label'       => __('Font', 'sage'),
        'description' => __('Typeface for hero and content-photo credits.', 'sage'),
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
            ''           => __('Default', 'sage'),
            'none'       => __('None', 'sage'),
            'uppercase'  => __('UPPERCASE', 'sage'),
            'lowercase'  => __('lowercase', 'sage'),
            'capitalize' => __('Capitalize', 'sage'),
        ],
    ]);

    $wp_customize->add_setting('rv_hero_credit_color', ['default' => '', 'sanitize_callback' => 'sanitize_hex_color']);
    $wp_customize->add_control(new \WP_Customize_Color_Control($wp_customize, 'rv_hero_credit_color', [
        'label'       => __('Colour', 'sage'),
        'description' => __('Blank uses a soft cream that reads on dark imagery.', 'sage'),
        'section'     => $sec,
    ]));
}, 20);

/** Emit the credit CSS (placement + typography) for hero + content photos. */
add_action('wp_head', function () {
    $fonts      = hero_credit_fonts();
    $weights    = ['400', '500', '600', '700'];
    $transforms = ['none', 'uppercase', 'lowercase', 'capitalize'];

    // Positioning contexts + shared legible defaults over imagery.
    $css  = '.rv-hero,.rv-split-media{position:relative}';
    $css .= '.rv-hero-credit,.rv-img-credit{position:absolute;z-index:3;text-align:right;line-height:1.3;'
        . 'pointer-events:none;font-family:var(--font-mono);letter-spacing:.03em;'
        . 'color:rgba(247,241,230,.88);text-shadow:0 1px 3px rgba(0,0,0,.5);'
        // Slight transparent gradient scrim so the credit stays legible over
        // any part of the image (light skies, busy detail). Darkest toward the
        // bottom-right corner it sits in, fading out toward the top-left.
        . 'padding:.34em .64em;border-radius:5px;'
        . 'background:linear-gradient(to top left,rgba(14,19,17,.52),rgba(14,19,17,.24) 65%,rgba(14,19,17,.08))}';
    $css .= '.rv-hero-credit a,.rv-img-credit a{color:inherit;pointer-events:auto}';
    // Hero sits on a big banner: larger insets.
    $css .= '.rv-hero-credit{right:clamp(1rem,3vw,2.25rem);bottom:clamp(.85rem,2.5vw,1.75rem);font-size:.72rem;max-width:60%}';
    // Content photos are smaller: tighter insets, slightly smaller.
    $css .= '.rv-img-credit{right:.65rem;bottom:.55rem;font-size:.66rem;max-width:80%}';

    // Customizer overrides — applied to hero and content credits alike.
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
        $css .= '.rv-hero-credit,.rv-img-credit{' . implode(';', $d) . '}';
    }

    echo '<style id="rv-hero-credit-css">' . $css . "</style>\n";
}, 24);
