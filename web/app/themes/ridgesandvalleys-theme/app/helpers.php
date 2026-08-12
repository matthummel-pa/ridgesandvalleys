<?php

/**
 * Template helpers — returned as strings for use in Blade ({!! !!}).
 */

namespace App;

/**
 * Estimated reading time.
 */
function reading_time(?int $post_id = null): string
{
    $post_id = $post_id ?: get_the_ID();
    $words   = str_word_count(wp_strip_all_tags(get_post_field('post_content', $post_id)));
    $minutes = max(1, (int) ceil($words / 200));

    /* translators: %d: minutes. */
    return sprintf(_n('%d min read', '%d min read', $minutes, 'sage'), $minutes);
}

/**
 * Post meta line: date · reading time · category.
 */
function post_meta(): string
{
    $out = [];

    $out[] = sprintf(
        '<time class="rv-meta-date" datetime="%s">%s</time>',
        esc_attr(get_the_date(DATE_W3C)),
        esc_html(get_the_date()),
    );

    if (get_post_type() === 'post') {
        $out[] = '<span class="rv-meta-time">' . esc_html(reading_time()) . '</span>';

        $cats = get_the_category();
        if (! empty($cats)) {
            $out[] = sprintf(
                '<a class="rv-meta-cat" href="%s">%s</a>',
                esc_url(get_category_link($cats[0]->term_id)),
                esc_html($cats[0]->name),
            );
        }
    }

    return '<p class="rv-post-meta">'
        . implode('<span class="rv-dot" aria-hidden="true">·</span>', $out)
        . '</p>';
}

/**
 * Eyebrow kicker.
 */
function eyebrow(string $text): string
{
    return $text ? '<span class="rv-eyebrow">' . esc_html($text) . '</span>' : '';
}

/**
 * Registered social platforms (filterable).
 */
function social_platforms(): array
{
    return apply_filters('rv/social_platforms', [
        'facebook'  => __('Facebook', 'sage'),
        'instagram' => __('Instagram', 'sage'),
        'x'         => __('X (Twitter)', 'sage'),
        'linkedin'  => __('LinkedIn', 'sage'),
        'youtube'   => __('YouTube', 'sage'),
        'tiktok'    => __('TikTok', 'sage'),
        'pinterest' => __('Pinterest', 'sage'),
        'bluesky'   => __('Bluesky', 'sage'),
        'mastodon'  => __('Mastodon', 'sage'),
        'yelp'      => __('Yelp', 'sage'),
        'github'    => __('GitHub', 'sage'),
        'email'     => __('Email', 'sage'),
    ]);
}

/**
 * Inline SVG glyph. Returns '' for unknown names.
 */
/**
 * Off-canvas close-button icon, selectable in the Customizer
 * (Header · Mobile / off-canvas menu → Close button icon).
 */
function oc_close_svg(?string $type = null): string
{
    $type = $type ?: get_theme_mod('rv_oc_close_icon', 'x');
    $map = [
        'x'       => ['<path d="M18 6 6 18M6 6l12 12"/>', 2],
        'x-bold'  => ['<path d="M18 6 6 18M6 6l12 12"/>', 3],
        'chevron' => ['<path d="M9 6l6 6-6 6"/>', 2],
        'arrow'   => ['<path d="M5 12h14M13 6l6 6-6 6"/>', 2],
    ];
    $g = $map[$type] ?? $map['x'];

    return '<svg viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="' . $g[1]
        . '" stroke-linecap="round" stroke-linejoin="round" width="20" height="20" aria-hidden="true" focusable="false">'
        . $g[0] . '</svg>';
}

function icon(string $name): string
{
    $paths = [
        'facebook'  => '<path d="M14 8.5h2V5.7h-2c-1.9 0-3 1.1-3 3v1.3H9v2.8h2V19h2.8v-6.2H16l.4-2.8h-2.6V9c0-.4.2-.5.6-.5z"/>',
        'instagram' => '<path d="M8 3.5h8A4.5 4.5 0 0 1 20.5 8v8A4.5 4.5 0 0 1 16 20.5H8A4.5 4.5 0 0 1 3.5 16V8A4.5 4.5 0 0 1 8 3.5zm0 1.8A2.7 2.7 0 0 0 5.3 8v8A2.7 2.7 0 0 0 8 18.7h8a2.7 2.7 0 0 0 2.7-2.7V8A2.7 2.7 0 0 0 16 5.3zm4 2.9a3.8 3.8 0 1 1 0 7.6 3.8 3.8 0 0 1 0-7.6zm0 1.8a2 2 0 1 0 0 4 2 2 0 0 0 0-4zm4-2.4a.9.9 0 1 1 0 1.8.9.9 0 0 1 0-1.8z"/>',
        'linkedin'  => '<path d="M6.9 8.5H4.4V19h2.5zM5.6 4.4a1.5 1.5 0 1 0 0 3 1.5 1.5 0 0 0 0-3zM19.6 19h-2.5v-5.1c0-1.2-.4-2-1.5-2-.8 0-1.3.6-1.5 1.1-.1.2-.1.5-.1.7V19H11.5s0-8.6 0-9.5H14v1.3c.3-.5 1-1.2 2.3-1.2 1.7 0 3.3 1.1 3.3 3.6z"/>',
        'github'    => '<path d="M12 3.5a8.5 8.5 0 0 0-2.7 16.6c.4.1.6-.2.6-.4v-1.5c-2.4.5-2.9-1.1-2.9-1.1-.4-1-1-1.3-1-1.3-.8-.5 0-.5 0-.5.9.1 1.3.9 1.3.9.8 1.3 2.1.9 2.6.7.1-.6.3-.9.5-1.1-1.9-.2-3.9-1-3.9-4.2 0-.9.3-1.7.9-2.3-.1-.2-.4-1.1.1-2.3 0 0 .7-.2 2.3.9a8 8 0 0 1 4.2 0c1.6-1.1 2.3-.9 2.3-.9.5 1.2.2 2.1.1 2.3.6.6.9 1.4.9 2.3 0 3.2-2 4-3.9 4.2.3.3.6.8.6 1.6v2.4c0 .2.2.5.6.4A8.5 8.5 0 0 0 12 3.5z"/>',
        'email'     => '<path d="M4.5 6.5h15c.6 0 1 .4 1 1v9c0 .6-.4 1-1 1h-15c-.6 0-1-.4-1-1v-9c0-.6.4-1 1-1zm.9 1.8 6.6 4.3 6.6-4.3zm13.2 1.1-6.1 4a1 1 0 0 1-1.1 0l-6.1-4v6.4h13.3z"/>',
        'x'         => '<path d="M17.5 4h2.6l-5.7 6.5L21 20h-4.9l-3.8-5-4.4 5H4.3l6.1-7L3.5 4h5l3.4 4.6zm-.9 14.4h1.4L8.1 5.5H6.6z"/>',
        'youtube'   => '<path d="M21.6 8.2s-.2-1.4-.8-2c-.7-.8-1.5-.8-1.9-.9C16.1 5 12 5 12 5s-4.1 0-6.9.3c-.4.1-1.2.1-1.9.9-.6.6-.8 2-.8 2S2 9.8 2 11.5v.9c0 1.7.2 3.3.2 3.3s.2 1.4.8 2c.7.8 1.7.8 2.1.9 1.6.1 6.9.2 6.9.2s4.1 0 6.9-.3c.4-.1 1.2-.1 1.9-.9.6-.6.8-2 .8-2s.2-1.6.2-3.3v-.9c0-1.7-.2-3.3-.2-3.3zM10 14.6V9.4l4.5 2.6z"/>',
        'tiktok'    => '<path d="M16.4 3c.3 2 1.5 3.4 3.6 3.6v2.5c-1.3 0-2.5-.4-3.6-1.1v5.6c0 3.3-2.4 5.5-5.3 5.5-2.7 0-4.8-2-4.8-4.7 0-2.8 2.3-4.8 5.1-4.5v2.6c-.3-.1-.6-.1-1-.1-1.1 0-2 .9-2 2s.9 2 2.1 2c1.2 0 2.2-.9 2.2-2.3V3z"/>',
        'pinterest' => '<path d="M12 3a9 9 0 0 0-3.3 17.4c-.1-.7-.1-1.7.1-2.5l1-4.1s-.2-.5-.2-1.2c0-1.1.6-2 1.4-2 .7 0 1 .5 1 1.1 0 .7-.4 1.7-.7 2.6-.2.8.4 1.4 1.2 1.4 1.4 0 2.5-1.5 2.5-3.6 0-1.9-1.4-3.2-3.3-3.2-2.3 0-3.6 1.7-3.6 3.4 0 .7.3 1.4.6 1.8.1.1.1.1.1.2l-.2.8c0 .1-.1.2-.3.1-1-.5-1.6-1.9-1.6-3 0-2.5 1.8-4.7 5.2-4.7 2.7 0 4.9 2 4.9 4.5 0 2.7-1.7 4.9-4.1 4.9-.8 0-1.6-.4-1.8-.9l-.5 1.9c-.2.7-.7 1.6-1 2.1A9 9 0 1 0 12 3z"/>',
        'yelp'      => '<path d="M13.9 3.3c.8-.3 1.7.3 1.7 1.2v6.6c0 1.1-1.3 1.6-2 .8L9.9 8c-.5-.6-.3-1.4.4-1.7zM8.7 10.9c.7.2.9 1.1.3 1.6l-2.2 1.9c-.7.6-1.8.1-1.8-.8 0-.6 0-1.3.2-1.9.2-.9 1.2-1.3 2-1zm.7 4.2c.8-.2 1.5.5 1.4 1.3l-.3 2.9c-.1.9-1.2 1.2-1.9.6-.5-.4-.9-1-1.3-1.5-.4-.7 0-1.6.8-1.8zm5.2-.4c.4-.7 1.4-.7 1.9 0l1.8 2.3c.5.7 0 1.7-.9 1.7-.6 0-1.3-.1-1.9-.4-.8-.3-1.1-1.2-.7-2zm.8-2c-.8 0-1.3-.9-.9-1.6l1.3-2.6c.4-.8 1.5-.8 1.9 0 .3.6.5 1.2.6 1.9.1.9-.6 1.6-1.5 1.6z"/>',
        'bluesky'   => '<path d="M6.3 4.6C8.6 6.3 11 9.8 12 11.7c1-1.9 3.4-5.4 5.7-7.1 1.6-1.2 4.3-2.2 4.3.9 0 .6-.4 5.2-.6 5.9-.7 2.5-3.2 3.1-5.5 2.7 3.9.7 4.9 3 2.8 5.2-4 4.2-5.7-1.1-6.2-2.4-.1-.2-.1-.4-.2-.5-.1.1-.1.3-.2.5-.5 1.3-2.2 6.6-6.2 2.4-2.1-2.2-1.1-4.5 2.8-5.2-2.3.4-4.8-.2-5.5-2.7-.2-.7-.6-5.3-.6-5.9 0-3.1 2.7-2.1 4.3-.9z"/>',
        'mastodon'  => '<path d="M21 8.4c0-3.3-2.1-4.2-2.1-4.2C17.8 3.7 15.9 3.5 12 3.5h-.1c-3.9 0-5.8.2-6.9.7 0 0-2.1.9-2.1 4.2 0 .7 0 1.6.1 2.7.2 3.6.8 7.2 4.1 8.1 1.5.4 2.9.5 4 .4 1.8-.1 2.8-.6 2.8-.6l-.1-1.5s-1.3.4-2.7.4c-1.5-.1-3.1-.1-3.4-2 0-.2 0-.3-.1-.5 0 0 1.5.4 3.5.5 1.2.1 2.3 0 3.4-.2 2.2-.3 4.2-1.6 4.4-2.9.4-2 .3-4.9.3-4.9zm-3.5 5.5h-1.8V9.4c0-.9-.4-1.4-1.2-1.4-.9 0-1.3.6-1.3 1.7v2.4h-1.8V9.7c0-1.1-.4-1.7-1.3-1.7-.8 0-1.2.5-1.2 1.4v4.5H7.1V9.3c0-.9.2-1.6.7-2.1.5-.5 1.1-.8 1.9-.8.9 0 1.6.4 2.1 1.1l.4.7.4-.7c.5-.7 1.2-1.1 2.1-1.1.8 0 1.4.3 1.9.8.5.5.7 1.2.7 2.1v4.6z"/>',
        'menu'      => '<path d="M4 7h16M4 12h16M4 17h16" fill="none" stroke="currentColor" stroke-width="1.8" stroke-linecap="round"/>',
        'close'     => '<path d="M6 6l12 12M18 6 6 18" fill="none" stroke="currentColor" stroke-width="1.8" stroke-linecap="round"/>',
        'arrow'     => '<path d="M5 12h13m-5-5 5 5-5 5" fill="none" stroke="currentColor" stroke-width="1.8" stroke-linecap="round" stroke-linejoin="round"/>',
        'send'      => '<path d="M21 3 10.5 13.5M21 3l-6.7 18-3.8-8.5L2 8.7 21 3z" fill="none" stroke="currentColor" stroke-width="1.7" stroke-linecap="round" stroke-linejoin="round"/>',
        'chat'      => '<path d="M20.5 11.5a7.9 7.9 0 0 1-8.5 7.9 8 8 0 0 1-3.7-.8L3.5 20l1.4-4.3a7.9 7.9 0 0 1-.9-3.7 7.9 7.9 0 0 1 8-7.9 7.9 7.9 0 0 1 8.5 7.4z" fill="none" stroke="currentColor" stroke-width="1.7" stroke-linecap="round" stroke-linejoin="round"/>',
    ];

    if (empty($paths[$name])) {
        return '';
    }

    return '<svg class="rv-icon rv-icon-' . esc_attr($name) . '" viewBox="0 0 24 24" width="20" height="20" fill="currentColor" aria-hidden="true" focusable="false">' . $paths[$name] . '</svg>';
}

/**
 * Normalise a stored social "email" value into a bare address.
 *
 * The Customizer field runs sanitize_email(), which quietly drops the colon
 * from a pasted "mailto:you@example.com" and stores "mailtoyou@example.com".
 * Strip any leading mailto prefix, with or without its colon, then validate.
 * Returns an empty string when there is nothing usable, so callers can skip
 * the link rather than render a broken one.
 */
function social_email_address(string $stored): string
{
    $addr = trim($stored);
    $addr = preg_replace('/^\s*mailto:?\s*/i', '', $addr);
    $addr = sanitize_email((string) $addr);

    return is_email($addr) ? $addr : '';
}

/**
 * Social links row from Customizer values.
 */
function social_links(string $class = 'rv-social'): string
{
    $items = [];
    $order = array_values(array_filter(array_map('trim', explode(',', (string) get_theme_mod('rv_social_order', '')))));
    $pos   = array_flip($order);
    $n     = 0;

    foreach (social_platforms() as $key => $label) {
        $n++;
        $url = get_theme_mod('rv_social_' . $key, '');
        if (! $url) {
            continue;
        }
        if ($key === 'email') {
            // The Customizer field sanitizes with sanitize_email(), which strips
            // the colon out of a pasted "mailto:you@example.com" and leaves
            // "mailtoyou@example.com" behind. Prepending "mailto:" to that gives
            // a dead link, so normalise the stored value before building the href.
            $addr = social_email_address($url);
            if (! $addr) {
                continue;
            }
            $href = 'mailto:' . antispambot($addr);
        } else {
            $href = esc_url($url);
        }
        $rank = isset($pos[$key]) ? $pos[$key] : (count($order) + $n);
        $items[] = [
            'order' => $rank,
            'html'  => sprintf(
                '<li><a class="rv-social-link rv-social-%1$s" href="%2$s"%3$s aria-label="%4$s">%5$s</a></li>',
                esc_attr($key),
                $href,
                ($key === 'email') ? '' : ' target="_blank" rel="noopener noreferrer"',
                esc_attr($label),
                icon($key),
            ),
        ];
    }

    if (empty($items)) {
        return '';
    }

    // Reorder by the per-platform display order (Customizer > Social Links).
    usort($items, static fn($a, $b) => $a['order'] <=> $b['order']);
    $html = '';
    foreach ($items as $it) {
        $html .= $it['html'];
    }

    // Icon design (Customizer > Social Links > icon style) — applied on every
    // instance, so the top bar, footer, and menu always match.
    $style = sanitize_key((string) get_theme_mod('rv_social_style', 'plain'));
    $class = trim($class . ' rv-social--' . ($style ?: 'plain'));

    return '<ul class="' . esc_attr($class) . '">' . $html . '</ul>';
}

/**
 * CTA button href from a mod value (absolute or site-relative).
 */
function cta_href(string $url): string
{
    return (str_starts_with($url, 'http')) ? $url : home_url($url);
}

/**
 * Attachment IDs for the brand logos, both pulled from the Media Library:
 *  - 'light' is the core Site Identity logo (Customize → Site Identity → Logo)
 *  - 'dark'  is the optional dark-mode / on-dark logo (rv_logo_dark, registered
 *    alongside it in the same Site Identity section)
 * Either may be 0 when nothing has been chosen.
 *
 * @return array{light:int,dark:int}
 */
function brand_logo_ids(): array
{
    return [
        'light' => (int) get_theme_mod('custom_logo', 0),
        'dark'  => (int) get_theme_mod('rv_logo_dark', 0),
    ];
}

/**
 * True when at least one brand logo image has been set in the Media Library.
 */
function has_brand_logo(): bool
{
    $ids = brand_logo_ids();
    return $ids['light'] > 0 || $ids['dark'] > 0;
}

/**
 * Brand lockup markup, driven entirely by the Media Library (no hard-coded art).
 *
 * If a logo image is set it is rendered from the uploaded file; when both a light
 * and a dark logo exist they carry the .rv-logo-light / .rv-logo-dark classes so
 * the theme swaps them by color mode (and over the transparent hero header). When
 * no logo image is set at all, the site title is shown as a plain text wordmark.
 *
 * @param string $context 'header' or 'footer' (footer prefers the on-dark logo).
 */
function brand_logo(string $context = 'header'): string
{
    $home = esc_url(home_url('/'));
    $name = get_bloginfo('name');
    $ids  = brand_logo_ids();

    // No image anywhere → text-only wordmark from the site title. Reuses the
    // existing .rv-brand-name styling (which is already color-mode aware and
    // adapts over the transparent hero header).
    if ($ids['light'] < 1 && $ids['dark'] < 1) {
        return sprintf(
            '<a class="rv-brand-lockup rv-brand-text-link" href="%s" rel="home"><span class="rv-brand-name">%s</span></a>',
            $home,
            esc_html($name),
        );
    }

    // Footer sits on a dark band: prefer the dark/cream logo when one exists.
    // A purpose-made dark logo is used as-is; a light-only logo is inverted to
    // white so a dark wordmark still reads on the dark footer.
    if ($context === 'footer') {
        $id  = $ids['dark'] ?: $ids['light'];
        $cls = 'rv-logo rv-footer-logo' . ($ids['dark'] ? '' : ' rv-footer-logo--invert');
        $img = wp_get_attachment_image($id, 'full', false, ['class' => $cls, 'alt' => '']);
        return sprintf(
            '<a class="rv-brand-lockup rv-brand-logo-link" href="%s" rel="home" aria-label="%s">%s</a>',
            $home,
            esc_attr($name),
            $img,
        );
    }

    // Header: both variants → mode swap; otherwise the single logo shows in both.
    if ($ids['light'] && $ids['dark']) {
        $img = wp_get_attachment_image($ids['light'], 'full', false, ['class' => 'rv-logo rv-logo-light', 'alt' => ''])
             . wp_get_attachment_image($ids['dark'], 'full', false, ['class' => 'rv-logo rv-logo-dark', 'alt' => '']);
    } else {
        $img = wp_get_attachment_image($ids['light'] ?: $ids['dark'], 'full', false, ['class' => 'rv-logo', 'alt' => '']);
    }

    return sprintf(
        '<a class="rv-brand-lockup rv-brand-logo-link" href="%s" rel="home" aria-label="%s">%s</a>',
        $home,
        esc_attr($name),
        $img,
    );
}

/**
 * Hero background image URL for a page/post.
 *
 * Priority: the WordPress Featured Image (Media Library, rv-hero size) → the
 * optional `hero_bg` custom field → the supplied stock fallback. This lets every
 * page drive its hero art from the standard Featured Image box, with the theme's
 * bundled imagery only as a last resort. Pass $post_id to read a specific page
 * (e.g. the assigned Posts page for the blog index).
 */
function hero_bg_url(string $fallback = '', ?int $post_id = null): string
{
    $has = $post_id ? has_post_thumbnail($post_id) : has_post_thumbnail();
    if ($has) {
        $url = (string) get_the_post_thumbnail_url($post_id ?: null, 'rv-hero');
        if ($url !== '') {
            return $url;
        }
    }

    $field = function_exists(__NAMESPACE__ . '\\field') ? field('hero_bg', '', $post_id) : '';
    return ($field !== '') ? $field : $fallback;
}

/**
 * Numbered pagination markup.
 */
function pagination(): string
{
    return (string) paginate_links([
        'mid_size'  => 1,
        'prev_text' => __('&larr; Newer', 'sage'),
        'next_text' => __('Older &rarr;', 'sage'),
        'type'      => 'list',
    ]);
}

/**
 * Scenic image URLs used across the site (from the design mockups).
 *
 * Open-license imagery of the Cumberland Valley, Adams County farmland, and
 * South Mountain — hotlinked at first so the site ships without bundling large
 * binaries. Replace any entry with your own uploaded photo (Media Library URL)
 * or override the whole map via the `rv/stock_images` filter. Templates layer
 * these over a gradient placeholder, so a missing image still looks intentional.
 *
 * Wikimedia Commons images are CC BY-SA / public domain (attribution belongs on
 * the credits list); Pexels images are free to use without attribution.
 */
function stock_images(): array
{
    $commons = 'https://commons.wikimedia.org/wiki/Special:FilePath/';
    $pexels  = 'https://images.pexels.com/photos/';

    return apply_filters('rv/stock_images', [
        'hero-home'      => $commons . 'Cumberland_Valley_Pennsylvania.jpg?width=1600',
        'hero-services'  => $commons . 'Cumberland_Valley_Pennsylvania.jpg?width=1600',
        'hero-work'      => $commons . 'Gettysburg,_Wentz_farm_bildings.jpg?width=1600',
        'process'        => $commons . '2021-11-01_10_09_09_View_north_along_U.S._Route_15_Business_(Emmitsburg_Road)_at_Millerstown_Road_and_Wheatfield_Road_within_Gettysburg_National_Military_Park_in_Cumberland_Township,_Adams_County,_Pennsylvania.jpg?width=1600',
        'featured'       => $commons . 'Gettysburg,_Wentz_farm_bildings.jpg?width=1200',
        'rooted'         => $commons . 'Pennsylvania_-_Gettysburg_-_NARA_-_68148246_(cropped).jpg?width=1400',
        'about'          => $commons . 'Pennsylvania_-_Gettysburg_-_NARA_-_68148252_(cropped).jpg?width=1400',
        'work-1'         => $commons . 'Gettysburg,_Wentz_farm_bildings.jpg?width=1000',
        'work-2'         => $pexels . '4749945/pexels-photo-4749945.jpeg?auto=compress&cs=tinysrgb&w=900',
        'work-3'         => $commons . 'Journey_Through_Hallowed_Ground_Byway_-_The_Shriver_House_Museum_-_NARA_-_7719712.jpg?width=1000',
        'work-4'         => $pexels . '1098743/pexels-photo-1098743.jpeg?auto=compress&cs=tinysrgb&w=900',
    ]);
}

/**
 * Single scenic image URL by key (empty string if unknown).
 */
function stock_image(string $key): string
{
    $map = stock_images();
    return isset($map[$key]) ? esc_url($map[$key]) : '';
}

/**
 * FAQ JSON-LD schema <script> for a list of [question, answer] pairs.
 */
function faq_schema(array $faqs): string
{
    $schema = ['@context' => 'https://schema.org', '@type' => 'FAQPage', 'mainEntity' => []];
    foreach ($faqs as $f) {
        $q = is_array($f) ? (string) ($f['q'] ?? $f[0] ?? '') : '';
        $a = is_array($f) ? (string) ($f['a'] ?? $f[1] ?? '') : '';
        if ($q === '') {
            continue;
        }
        $schema['mainEntity'][] = [
            '@type'          => 'Question',
            'name'           => $q,
            'acceptedAnswer' => ['@type' => 'Answer', 'text' => $a],
        ];
    }
    return '<script type="application/ld+json">' . wp_json_encode($schema) . '</script>';
}

/**
 * Visible breadcrumb nav for single posts. Uses the canonical breadcrumb_items()
 * defined in app/seo.php (single source of truth shared with the schema).
 */
function breadcrumbs(): string
{
    if (! is_singular('post')) {
        return '';
    }

    $items = breadcrumb_items();
    $last  = count($items) - 1;
    $links = [];

    foreach ($items as $i => $it) {
        if ($i === $last) {
            $links[] = '<span aria-current="page" style="color:var(--color-ink)">' . esc_html($it['name']) . '</span>';
        } else {
            $links[] = '<a href="' . esc_url($it['url']) . '" style="color:inherit;text-decoration:none">' . esc_html($it['name']) . '</a>';
        }
    }

    return '<nav class="rv-breadcrumbs" aria-label="' . esc_attr__('Breadcrumb', 'sage')
        . '" style="font-family:var(--font-mono);font-size:.72rem;letter-spacing:.04em;text-transform:uppercase;color:var(--color-muted);margin-bottom:1.1rem;display:flex;flex-wrap:wrap;gap:.45rem;align-items:center">'
        . implode('<span aria-hidden="true" style="opacity:.55">/</span>', $links)
        . '</nav>';
}

/**
 * Related posts: up to $limit recent posts sharing a category with the current
 * post, excluding it. Falls back to recent posts if there aren't enough.
 */
function related_posts(int $limit = 3): array
{
    $post_id = get_the_ID();
    if (! $post_id) {
        return [];
    }

    $cats = wp_get_post_categories($post_id);

    $base = [
        'post_type'           => 'post',
        'posts_per_page'      => $limit,
        'post__not_in'        => [$post_id],
        'ignore_sticky_posts' => true,
        'no_found_rows'       => true,
        'orderby'             => 'date',
        'order'               => 'DESC',
    ];

    $posts = ! empty($cats)
        ? get_posts($base + ['category__in' => $cats])
        : get_posts($base);

    if (count($posts) < $limit) {
        $have = array_map(static fn($p) => $p->ID, $posts);
        $fill = get_posts([
            'post_type'           => 'post',
            'posts_per_page'      => $limit - count($posts),
            'post__not_in'        => array_merge([$post_id], $have),
            'ignore_sticky_posts' => true,
            'no_found_rows'       => true,
            'orderby'             => 'date',
            'order'               => 'DESC',
        ]);
        $posts = array_merge($posts, $fill);
    }

    return $posts;
}

/**
 * Short "the short version" summary for a post, shown in the in-article box.
 *
 * Cornerstone posts get a hand-written 2–3 sentence summary here; any other post
 * falls back to its Excerpt (editable in the post's Excerpt box) so nothing is
 * required to publish. Override or extend the map via `rv/post_summaries`.
 */
function post_summary(?int $post_id = null): string
{
    $post_id = $post_id ?: (int) get_the_ID();
    if (! $post_id) {
        return '';
    }

    // Per-post override (Journal post options meta box) wins over everything.
    $meta = get_post_meta($post_id, 'rv_f_post_summary', true);
    if (is_string($meta) && trim($meta) !== '') {
        return trim($meta);
    }

    $slug = (string) get_post_field('post_name', $post_id);

    $map = apply_filters('rv/post_summaries', [
        'small-business-website-cost-pennsylvania' => __('A professional small-business website in South Central PA usually runs from a few hundred dollars for a DIY build to several thousand for a custom one — driven mostly by page count, design, and features like booking or online sales. This walks through the real price ranges, the ongoing costs nobody mentions (hosting, domain, upkeep), and how to match the spend to what your business actually needs.', 'sage'),
        'show-up-google-maps-gettysburg-business'  => __('For local “near me” searches, the Google map pack matters even more than your website — and you can climb it yourself, for free. Claim and fully complete your Google Business Profile, earn and reply to reviews, and make sure your site backs up the listing. This is the step-by-step path for an Adams County business.', 'sage'),
        'hire-local-web-designer-or-use-wix'       => __('DIY builders like Wix are cheap and fast; a local designer costs more but saves your time, gets you found, and leaves you owning your site. The right call depends on your time, your budget, and how much the website matters to winning work. Here’s an honest way to decide — no sales pitch.', 'sage'),
    ], $post_id);

    if (! empty($map[$slug])) {
        return (string) $map[$slug];
    }

    $excerpt = get_the_excerpt($post_id);

    return $excerpt ? trim(html_entity_decode(wp_strip_all_tags($excerpt), ENT_QUOTES)) : '';
}

/**
 * Build an "in this article" table of contents from a post's H2 headings and
 * return the content with stable anchor IDs injected into those headings.
 *
 * Returns ['content' => string, 'items' => [['id' => slug, 'text' => label], …]].
 * Safe on empty content and when ext-dom is unavailable (returns content as-is,
 * no items). IDs already present on a heading are respected and reused.
 */
function post_toc_data(?string $html = null): array
{
    if ($html === null) {
        $html = apply_filters('the_content', get_the_content());
    }
    $html = (string) $html;

    if (trim($html) === '' || ! class_exists('DOMDocument')) {
        return ['content' => $html, 'items' => []];
    }

    $dom  = new \DOMDocument();
    $prev = libxml_use_internal_errors(true);
    // The XML hint keeps multibyte text intact; the flags stop DOMDocument from
    // wrapping the fragment in <html>/<body> or adding a doctype.
    $dom->loadHTML(
        '<?xml encoding="utf-8"?><div id="rv-toc-root">' . $html . '</div>',
        LIBXML_HTML_NOIMPLIED | LIBXML_HTML_NODEFDTD,
    );
    libxml_clear_errors();
    libxml_use_internal_errors($prev);

    // Snapshot the live NodeList before mutating attributes.
    $h2s = [];
    foreach ($dom->getElementsByTagName('h2') as $node) {
        $h2s[] = $node;
    }

    $items = [];
    $used  = [];

    foreach ($h2s as $h) {
        $text = trim($h->textContent);
        if ($text === '') {
            continue;
        }

        $id = $h->getAttribute('id');
        if ($id === '') {
            $base = sanitize_title($text) ?: 'section';
            $id   = $base;
            $k    = 2;
            while (isset($used[$id])) {
                $id = $base . '-' . $k;
                $k++;
            }
            $h->setAttribute('id', $id);
        } elseif (isset($used[$id])) {
            continue;
        }

        $used[$id] = true;
        $items[]   = ['id' => $id, 'text' => $text];
    }

    // Re-serialize the wrapper's children (drops the wrapper div itself).
    $root = null;
    foreach ($dom->childNodes as $node) {
        if ($node->nodeType === XML_ELEMENT_NODE && $node->nodeName === 'div') {
            $root = $node;
            break;
        }
    }

    $out = '';
    if ($root) {
        foreach ($root->childNodes as $child) {
            $out .= $dom->saveHTML($child);
        }
    } else {
        $out = $html;
    }

    return ['content' => $out, 'items' => $items];
}

if (! function_exists(__NAMESPACE__ . '\\blog_post_image')) {
    /**
     * Image URL for a blog post: the real featured image if one is set, otherwise
     * a topic-matched, openly-licensed fallback (hotlinked, like the theme heroes),
     * otherwise ''. Override or extend the map via the `rv/blog_post_images` filter.
     * Fallbacks are free Pexels photos (no attribution required).
     */
    function blog_post_image($post = null): string
    {
        $post_id = $post ? (is_object($post) ? (int) $post->ID : (int) $post) : (int) get_the_ID();

        if ($post_id && has_post_thumbnail($post_id)) {
            $url = get_the_post_thumbnail_url($post_id, 'rv-hero');
            if ($url) {
                return $url;
            }
        }

        $slug = $post_id ? (string) get_post_field('post_name', $post_id) : '';

        $map = apply_filters('rv/blog_post_images', [
            'small-business-website-cost-pennsylvania' => 'https://images.pexels.com/photos/209224/pexels-photo-209224.jpeg?auto=compress&cs=tinysrgb&w=1200',
            'show-up-google-maps-gettysburg-business'  => 'https://images.pexels.com/photos/30403062/pexels-photo-30403062.jpeg?auto=compress&cs=tinysrgb&w=1200',
            'hire-local-web-designer-or-use-wix'       => 'https://images.pexels.com/photos/7181184/pexels-photo-7181184.jpeg?auto=compress&cs=tinysrgb&w=1200',
        ]);

        // Return the raw URL; Blade's {{ }} escapes it once on output. (Using
        // esc_url() here as well would double-encode the & and break the CDN
        // sizing params, forcing full-resolution downloads.)
        return isset($map[$slug]) ? $map[$slug] : '';
    }
}

/**
 * Social share row for single posts: Facebook, X, LinkedIn, email + copy-link.
 * Icons are inline SVG; the copy button is wired up by the single-post script.
 */
function share_links(): string
{
    $permalink = get_permalink();
    if (! $permalink) {
        return '';
    }

    $url   = rawurlencode($permalink);
    $title = rawurlencode(wp_strip_all_tags(get_the_title()));

    $x_svg    = '<svg viewBox="0 0 24 24" width="18" height="18" fill="currentColor" aria-hidden="true"><path d="M17.5 4h2.6l-5.7 6.5L21 20h-4.9l-3.8-5-4.4 5H4.3l6.1-7L3.5 4h5l3.4 4.6zm-.9 14.4h1.4L8.1 5.5H6.6z"/></svg>';
    $copy_svg = '<svg viewBox="0 0 24 24" width="18" height="18" fill="none" stroke="currentColor" stroke-width="1.7" stroke-linecap="round" stroke-linejoin="round" aria-hidden="true"><path d="M10 13a5 5 0 0 0 7 0l2-2a5 5 0 1 0-7-7l-1 1"/><path d="M14 11a5 5 0 0 0-7 0l-2 2a5 5 0 1 0 7 7l1-1"/></svg>';

    $links = [
        'facebook' => ['https://www.facebook.com/sharer/sharer.php?u=' . $url, __('Share on Facebook', 'sage'), icon('facebook')],
        'x'        => ['https://twitter.com/intent/tweet?url=' . $url . '&text=' . $title, __('Share on X', 'sage'), $x_svg],
        'linkedin' => ['https://www.linkedin.com/sharing/share-offsite/?url=' . $url, __('Share on LinkedIn', 'sage'), icon('linkedin')],
        'email'    => ['mailto:?subject=' . $title . '&body=' . $url, __('Share by email', 'sage'), icon('email')],
    ];

    $out = '<div class="rv-share"><span class="rv-share-label">' . esc_html__('Share', 'sage') . '</span><div class="rv-share-btns">';
    foreach ($links as $key => $l) {
        $target = ($key === 'email') ? '' : ' target="_blank" rel="noopener noreferrer"';
        $out   .= '<a class="rv-share-btn rv-share-' . esc_attr($key) . '" href="' . esc_url($l[0]) . '"' . $target
            . ' aria-label="' . esc_attr($l[1]) . '">' . $l[2] . '</a>';
    }
    $out .= '<button type="button" class="rv-share-btn rv-share-copy" data-url="' . esc_attr($permalink) . '" aria-label="'
        . esc_attr__('Copy link', 'sage') . '">' . $copy_svg
        . '<span class="rv-share-copied" aria-hidden="true">' . esc_html__('Copied', 'sage') . '</span></button>';
    $out .= '</div></div>';

    return $out;
}

/**
 * Markup for the soft mid-article call-to-action callout.
 */
function inline_cta_html(): string
{
    if (! get_theme_mod('rv_inline_cta_enable', true)) {
        return '';
    }

    $heading = get_theme_mod('rv_inline_cta_heading', __('Not sure where your site stands?', 'sage'));
    $text    = get_theme_mod('rv_inline_cta_text', __('I’ll record a free 5-minute video walkthrough of your website — no pitch, just the first things I’d fix.', 'sage'));
    $btn     = get_theme_mod('rv_inline_cta_btn', __('Get my free audit', 'sage'));
    $url     = get_theme_mod('rv_inline_cta_url', get_theme_mod('rv_cta_url', '/contact/'));
    $href    = esc_url(cta_href($url ?: '/contact/'));

    return '<aside class="rv-inline-cta" role="complementary">'
        . '<div class="rv-inline-cta-body">'
        . '<strong>' . esc_html($heading) . '</strong>'
        . '<span>' . esc_html($text) . '</span>'
        . '</div>'
        . '<a class="rv-btn rv-btn-primary" href="' . $href . '">' . esc_html($btn) . '</a>'
        . '</aside>';
}

/**
 * Insert the inline CTA after the 3rd paragraph of post content. Only fires on
 * posts long enough that an interruption reads naturally (5+ paragraphs).
 */
function content_add_inline_cta(string $html, ?string $cta = null): string
{
    if ($cta === null) {
        $cta = inline_cta_html();
    }
    if ($cta === '' || stripos($html, '</p>') === false) {
        return $html;
    }

    $parts = explode('</p>', $html);
    $total = count($parts) - 1; // number of closed paragraphs
    if ($total < 5) {
        return $html;
    }

    $after   = 3;
    $rebuilt = '';
    foreach ($parts as $i => $chunk) {
        $rebuilt .= $chunk;
        if ($i < $total) {
            $rebuilt .= '</p>';
            if ($i + 1 === $after) {
                $rebuilt .= $cta;
            }
        }
    }

    return $rebuilt;
}

/**
 * Two inline SVGs for the header light/dark toggle: the first (.rv-sun) shows in
 * light mode, the second (.rv-moon) shows in dark mode (swap handled in app.css).
 * Style is chosen in the Customizer (Header > Toggle icon); unknown keys fall
 * back to sun & moon.
 */
function theme_toggle_icons(string $style = 'sun-moon'): string
{
    $sun  = '<circle cx="12" cy="12" r="4"/><path d="M12 2v2M12 20v2M4.93 4.93l1.41 1.41M17.66 17.66l1.41 1.41M2 12h2M20 12h2M6.34 17.66l-1.41 1.41M19.07 4.93l-1.41 1.41"/>';
    $moon = '<path d="M21 12.79A9 9 0 1 1 11.21 3 7 7 0 0 0 21 12.79z"/>';
    $bulbOff = '<path d="M9 18h6M10 21h4"/><path d="M12 3a6 6 0 0 0-3.5 10.9c.6.5.9 1.3 1 2.1h5c.1-.8.4-1.6 1-2.1A6 6 0 0 0 12 3z"/>';
    $bulbOn  = '<path d="M12 2v1.5M4.6 5.2l1 1M19.4 5.2l-1 1"/><path d="M9 18h6M10 21h4"/><path d="M12 4.5a6 6 0 0 0-3.5 10.9c.6.5.9 1.3 1 2.1h5c.1-.8.4-1.6 1-2.1A6 6 0 0 0 12 4.5z"/>';
    $halfR = '<circle cx="12" cy="12" r="9"/><path d="M12 3a9 9 0 0 1 0 18z" fill="currentColor" stroke="none"/>';
    $halfL = '<circle cx="12" cy="12" r="9"/><path d="M12 3a9 9 0 0 0 0 18z" fill="currentColor" stroke="none"/>';

    $map = [
        'sun-moon' => [$sun, $moon],
        'moon-sun' => [$moon, $sun],
        'contrast' => [$halfR, $halfL],
        'bulb'     => [$bulbOff, $bulbOn],
    ];
    [$light, $dark] = $map[$style] ?? $map['sun-moon'];

    $svg = static function (string $cls, string $paths): string {
        return '<svg class="' . $cls . '" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" '
            . 'stroke-linecap="round" stroke-linejoin="round" aria-hidden="true" focusable="false">' . $paths . '</svg>';
    };

    return $svg('rv-sun', $light) . $svg('rv-moon', $dark);
}

/**
 * Parse a Customizer textarea of "Label | /url/" lines into
 * [['label' => ..., 'url' => ...], ...]. Used by the journal sidebar.
 *
 * @param  array<int, string>  $defaultLines
 * @return array<int, array{label: string, url: string}>
 */
function mod_link_lines(string $mod, array $defaultLines): array
{
    $raw = trim((string) get_theme_mod($mod, ''));
    $lines = $raw !== '' ? preg_split('/\r\n|\r|\n/', $raw) : $defaultLines;
    $out = [];
    foreach ((array) $lines as $line) {
        $line = trim((string) $line);
        if ($line === '') {
            continue;
        }
        $parts = array_map('trim', explode('|', $line, 2));
        $out[] = ['label' => $parts[0], 'url' => $parts[1] ?? ''];
    }

    return $out;
}
