<?php

/**
 * AI Builder — the "AI is the page builder" layer.
 *
 * Pressroot's page builder IS the WordPress block editor: every generated
 * page is plain Gutenberg blocks the owner can open and edit like any other
 * page. What this file adds is the AI half of that equation — it takes the
 * Brand tab's questionnaire answers and writes the actual site:
 *
 *  1. "✨ Write with AI" (per page) / "✨ AI-write all pages" (per site type)
 *     — sends every human-readable text segment of a generated page to the
 *     owner's selected AI connector along with the full brand profile
 *     (audience, industry, tone words, goal, density) and swaps in the
 *     returned copy. CRITICALLY, the AI only ever supplies TEXT: the block
 *     markup is never AI-generated or AI-edited (honoring the reliability
 *     decision documented in app/ai-assistant.php) — segments are replaced
 *     string-for-string inside the existing, valid block HTML. If the model
 *     returns anything unusable, the page simply keeps its placeholder copy.
 *
 *  2. "✨ Generate brand image" (Brand tab) — builds an image prompt from
 *     the questionnaire (imagery style + industry + personality), generates
 *     it with the free keyless Pollinations image endpoint, sideloads it
 *     into the Media Library, and sets it as the homepage hero image.
 *
 * Everything here is gated by prt_ai_features_enabled() — the Brand tab's
 * "Powered by AI — or not" switch turns this whole file off.
 */

namespace App;

/* ─────────────────────── Text-segment extraction ─────────────────────── */

/**
 * Pull the human-readable text segments out of block HTML, in order.
 * Conservative on purpose: only text nodes 4+ words long, skipping anything
 * inside block comments, so short UI strings (button glyphs, prices, meta
 * rows) survive untouched.
 *
 * @return string[] Unique segments, capped to keep prompts small.
 */
function prt_ai_page_text_segments(string $html): array
{
    $noComments = preg_replace('/<!--.*?-->/s', '', $html) ?? $html;
    preg_match_all('/>([^<>]{16,400})</u', $noComments, $m);
    $out = [];
    foreach ($m[1] as $raw) {
        $text = trim(html_entity_decode($raw, ENT_QUOTES));
        if ($text === '' || str_word_count($text) < 4 || in_array($text, $out, true)) {
            continue;
        }
        $out[] = $text;
        if (count($out) >= 24) {
            break;
        }
    }
    return $out;
}

/** The site-type questionnaire answers, formatted for prompts. */
function prt_ai_type_answers_prompt(string $typeId): string
{
    if ($typeId === '' || ! function_exists('App\\prt_type_answers')) {
        return '';
    }
    $answers = array_filter(prt_type_answers($typeId));
    if (! $answers) {
        return '';
    }
    $labels = [];
    foreach ((prt_site_type_questions()[$typeId] ?? []) as $q) {
        $labels[$q[0]] = $q[1];
    }
    $lines = 'Industry specifics (use these concrete facts in the copy):' . "\n";
    foreach ($answers as $k => $v) {
        $lines .= '- ' . ($labels[$k] ?? $k) . ': ' . $v . "\n";
    }
    return $lines;
}

/* ─────────────────────────── Page fill core ─────────────────────────── */

/**
 * Rewrite one page's copy with the selected AI connector, brand-profile
 * aware. Returns ['ok' => bool, 'replaced' => int, 'error' => string].
 */
function prt_ai_fill_page(int $pageId, string $model = 'pollinations'): array
{
    $page = get_post($pageId);
    if (! $page) {
        return ['ok' => false, 'replaced' => 0, 'error' => __('Page not found.', 'pressroot')];
    }

    $segments = prt_ai_page_text_segments($page->post_content);
    if (! $segments) {
        return ['ok' => false, 'replaced' => 0, 'error' => __('No rewritable text found on this page.', 'pressroot')];
    }

    $brand   = function_exists('App\\prt_brand_profile') ? prt_brand_profile() : [];
    $typeId  = get_post_meta($pageId, '_prt_site_type', true);
    $types   = function_exists('App\\prt_site_types') ? prt_site_types() : [];
    $typeLbl = $types[$typeId]['label'] ?? '';

    $goalMap = ['leads' => 'get inquiries', 'sell' => 'sell products', 'book' => 'get bookings', 'read' => 'grow readership'];
    $densMap = ['minimal' => 'Keep every line noticeably SHORTER than the original.', 'balanced' => 'Keep each line roughly the same length as the original.', 'rich' => 'Lines may run slightly longer than the originals where it helps the story.'];

    $core   = function_exists('App\\prt_get_core_instructions') ? prt_get_core_instructions() : '';
    $prompt = ($core !== '' ? $core . "\n" : '')
        . "You are writing website copy for this business:\n"
        . 'Business: ' . ($brand['name'] ?: 'an unnamed business') . ' — ' . ($brand['desc'] ?: 'no description given') . "\n"
        . ($brand['industry'] ? 'Industry: ' . $brand['industry'] . "\n" : '')
        . ($brand['audience'] ? 'Audience: ' . $brand['audience'] . "\n" : '')
        . ($brand['tone'] ? 'Voice: ' . $brand['tone'] . "\n" : 'Voice: confident, concise, a little playful' . "\n")
        . 'Primary goal: ' . ($goalMap[$brand['goal'] ?? 'leads'] ?? 'get inquiries') . "\n"
        . ($typeLbl ? 'Website category: ' . $typeLbl . "\n" : '')
        . 'Page: "' . $page->post_title . "\"\n"
        . prt_ai_type_answers_prompt((string) $typeId) . "\n"
        . 'Rewrite each numbered placeholder line below as real copy for this business. '
        . ($densMap[$brand['density'] ?? 'balanced'])
        . ' Never overpromise, no jargon, plain text only (no quotes, no markdown, no HTML). '
        . "Respond with ONLY a JSON array of " . count($segments) . " strings, in the same order, no other text.\n\n";
    foreach ($segments as $i => $seg) {
        $prompt .= ($i + 1) . '. ' . $seg . "\n";
    }

    $result = function_exists('App\\prt_ai_generate_text')
        ? prt_ai_generate_text($model, $prompt)
        : ['ok' => false, 'error' => __('AI connectors unavailable.', 'pressroot')];
    if (empty($result['ok'])) {
        return ['ok' => false, 'replaced' => 0, 'error' => $result['error'] ?? __('Generation failed.', 'pressroot')];
    }

    // Parse defensively: strip code fences, slice to the outermost array.
    $raw   = trim((string) $result['text']);
    $raw   = preg_replace('/^```(?:json)?|```$/m', '', $raw) ?? $raw;
    $start = strpos($raw, '[');
    $end   = strrpos($raw, ']');
    $list  = ($start !== false && $end !== false && $end > $start) ? json_decode(substr($raw, $start, $end - $start + 1), true) : null;

    if (! is_array($list) || count($list) !== count($segments)) {
        return ['ok' => false, 'replaced' => 0, 'error' => __('The AI reply wasn\'t usable (wrong shape) — placeholders kept. Try again or switch models.', 'pressroot')];
    }

    $content  = $page->post_content;
    $replaced = 0;
    foreach ($segments as $i => $seg) {
        $new = is_string($list[$i]) ? sanitize_text_field($list[$i]) : '';
        if ($new === '' || $new === $seg) {
            continue;
        }
        $needle  = esc_html($seg) === $seg ? $seg : $seg; // segments came from markup verbatim
        $updated = preg_replace('/' . preg_quote('>' . $seg . '<', '/') . '/u', '>' . esc_html($new) . '<', $content, 1, $count);
        if ($count > 0) {
            $content = $updated;
            $replaced++;
        }
    }

    if ($replaced > 0) {
        wp_update_post(['ID' => $pageId, 'post_content' => $content]);
    }

    return ['ok' => true, 'replaced' => $replaced, 'error' => ''];
}

/* ──────────── Edit-screen tools (pages & posts) + suggestions ──────────── */

/**
 * Suggested blocks per page role — what a marketer would add next, named by
 * their real inserter names so the owner can search them directly.
 */
function prt_suggested_blocks(string $typeId, string $role): array
{
    $byRole = [
        'home'         => [__('Prt Smart Hero — brand-driven hero in one block', 'pressroot'), __('Columns — three feature cards', 'pressroot'), __('Prt Smart CTA — goal-driven call to action', 'pressroot')],
        'services'     => [__('Columns — one card per service', 'pressroot'), __('Details — an FAQ per service', 'pressroot'), __('Prt Smart CTA', 'pressroot')],
        'pricing'      => [__('Table — plan comparison', 'pressroot'), __('Columns — tier cards with a featured middle', 'pressroot'), __('Details — pricing FAQ', 'pressroot')],
        'contact'      => [__('Pressroot contact form (pattern)', 'pressroot'), __('Custom HTML-free Map: use an Embed block', 'pressroot'), __('Social Icons', 'pressroot')],
        'about'        => [__('Media & Text — founder photo + story', 'pressroot'), __('Quote — a customer testimonial', 'pressroot'), __('Prt Smart CTA', 'pressroot')],
        'reviews'      => [__('Columns — ranked pick cards', 'pressroot'), __('Table — spec comparison', 'pressroot'), __('Details — how we test', 'pressroot')],
        'disclosure'   => [__('Paragraphs + List — plain-language promises', 'pressroot'), __('Separator between sections', 'pressroot')],
        'menu'         => [__('Columns — menu sections', 'pressroot'), __('Table — items and prices', 'pressroot'), __('Prt Smart CTA — reservations', 'pressroot')],
        'reservations' => [__('Buttons — booking link front and center', 'pressroot'), __('Columns — hours + private dining', 'pressroot')],
        'listings'     => [__('Query Loop — pull listing posts automatically', 'pressroot'), __('Columns — featured property cards', 'pressroot')],
        'agents'       => [__('Columns — one card per agent', 'pressroot'), __('Quote — client reviews', 'pressroot'), __('Prt Smart CTA — free valuation', 'pressroot')],
        'features'     => [__('Columns — feature grid', 'pressroot'), __('Media & Text — screenshot walkthroughs', 'pressroot'), __('Prt Smart CTA — trial signup', 'pressroot')],
        'blog'         => [__('Query Loop / Prt Post Grid — latest posts', 'pressroot'), __('Prt Smart CTA — newsletter', 'pressroot')],
        'projects'     => [__('Repofolio Repo Grid — live GitHub work', 'pressroot'), __('Columns — case-study cards', 'pressroot')],
        'resume'       => [__('Columns — experience timeline', 'pressroot'), __('List — skills', 'pressroot'), __('Buttons — download résumé', 'pressroot')],
    ];
    return $byRole[$role] ?? [__('Prt Smart Hero', 'pressroot'), __('Columns — three cards', 'pressroot'), __('Prt Smart CTA', 'pressroot')];
}

/**
 * "Pressroot AI" panel on every page/post edit screen: the same one-click
 * tools as the Site Types table (new design, AI-write, AI image) as safe
 * nonce links (no nested forms inside the editor), plus the suggested
 * blocks a marketer would add to this specific page next.
 */
add_action('add_meta_boxes', function () {
    foreach (['page', 'post'] as $pt) {
        add_meta_box('prt-ai-tools', __('Pressroot AI', 'pressroot'), function ($post) {
            $typeId = (string) get_post_meta($post->ID, '_prt_site_type', true);
            $role   = (string) get_post_meta($post->ID, '_prt_page_role', true);
            $aiOn   = function_exists('App\\prt_ai_features_enabled') && prt_ai_features_enabled();
            $link   = fn (string $action) => wp_nonce_url(admin_url('admin-post.php?action=' . $action . '&page_id=' . $post->ID . '&back=edit'), $action);
            echo '<p class="description" style="margin-top:0">' . esc_html__('Save your edits first — these actions reload the page.', 'pressroot') . '</p>';
            echo '<div style="display:flex;gap:6px;flex-wrap:wrap;margin-bottom:10px">';
            if ($typeId !== '') {
                echo '<a class="button" href="' . esc_url($link('prt_regenerate_site_type_page_get')) . '">🎲 ' . esc_html__('New design', 'pressroot') . '</a>';
            }
            if ($aiOn) {
                echo '<a class="button" href="' . esc_url($link('prt_ai_fill_page')) . '">✨ ' . esc_html__('Write with AI', 'pressroot') . '</a>';
                echo '<a class="button" href="' . esc_url($link('prt_ai_page_image')) . '">🖼 ' . esc_html__('AI image', 'pressroot') . '</a>';
            }
            echo '</div>';
            echo '<strong style="font-size:12px">' . esc_html__('Suggested blocks for this page', 'pressroot') . '</strong>';
            echo '<ul style="margin:6px 0 0 16px;font-size:12px;color:#646970">';
            foreach (prt_suggested_blocks($typeId, $role) as $sug) {
                echo '<li>' . esc_html($sug) . '</li>';
            }
            echo '</ul>';
        }, $pt, 'side', 'high');
    }
});

/**
 * GET-friendly wrappers so the edit-screen links work: same guards, same
 * work, then bounce back to the editor instead of the settings page.
 */
function prt_ai_builder_back_url(int $pageId): string
{
    return isset($_REQUEST['back']) && $_REQUEST['back'] === 'edit'
        ? (get_edit_post_link($pageId, 'url') ?: prt_settings_tab_url('ai'))
        : '';
}

add_action('admin_post_prt_regenerate_site_type_page_get', function () {
    if (! current_user_can('edit_theme_options') || ! check_admin_referer('prt_regenerate_site_type_page_get')) {
        wp_die(__('Not allowed.', 'pressroot'));
    }
    $pageId = absint($_REQUEST['page_id'] ?? 0);
    // Reuse the existing POST handler's core by simulating its input.
    $_POST['page_id'] = (string) $pageId;
    $_POST['_wpnonce'] = wp_create_nonce('prt_regenerate_site_type_page');
    $_REQUEST['_wpnonce'] = $_POST['_wpnonce'];
    $back = prt_ai_builder_back_url($pageId);
    if ($back !== '') {
        add_filter('wp_redirect', fn () => $back, 99);
    }
    do_action('admin_post_prt_regenerate_site_type_page');
});

/** Guard shared by every handler below. */
function prt_ai_builder_guard(string $nonceAction): void
{
    if (! current_user_can('edit_theme_options') || ! check_admin_referer($nonceAction)) {
        wp_die(__('Not allowed.', 'pressroot'));
    }
    if (! function_exists('App\\prt_ai_features_enabled') || ! prt_ai_features_enabled()) {
        wp_die(__('AI features are switched off — flip "Powered by AI — or not" on the Brand tab first.', 'pressroot'));
    }
}

/* ─────────────────────────── Admin actions ─────────────────────────── */

/** One page. */
add_action('admin_post_prt_ai_fill_page', function () {
    prt_ai_builder_guard('prt_ai_fill_page');
    $pageId = absint($_REQUEST['page_id'] ?? 0);
    $model  = sanitize_key($_POST['model'] ?? 'pollinations');
    $res  = prt_ai_fill_page($pageId, $model);
    $back = prt_ai_builder_back_url($pageId);
    if ($back !== '') {
        wp_safe_redirect($back);
        exit;
    }
    wp_safe_redirect(prt_settings_tab_url('ai', $res['ok']
        ? ['prt_ai_filled' => $res['replaced'], 'prt_ai_filled_title' => rawurlencode(get_the_title($pageId))]
        : ['prt_ai_fill_error' => rawurlencode($res['error'])]));
    exit;
});

/** Every page of one site type. */
add_action('admin_post_prt_ai_fill_all', function () {
    prt_ai_builder_guard('prt_ai_fill_all');
    $typeId = sanitize_key($_POST['site_type'] ?? '');
    $model  = sanitize_key($_POST['model'] ?? 'pollinations');
    $total  = 0;
    $errors = 0;
    foreach (prt_get_site_type_pages() as $sp) {
        if ($sp->prt_site_type !== $typeId) {
            continue;
        }
        $res = prt_ai_fill_page((int) $sp->ID, $model);
        $res['ok'] ? $total += $res['replaced'] : $errors++;
    }
    // prt_settings_return_url(): the Setup wizard posts prt_return_tab/step
    // so its "AI-write all pages" run lands back on wizard step 4.
    wp_safe_redirect(prt_settings_return_url('ai', ['prt_ai_filled' => $total, 'prt_ai_fill_errors' => $errors]));
    exit;
});

/**
 * Per-page AI image: generated from the page title + brand profile + the
 * site-type questionnaire answers, sideloaded into the Media Library
 * ATTACHED TO THE PAGE (so it shows in that page's "Generated media"
 * column) and set as the page's featured image.
 */
add_action('admin_post_prt_ai_page_image', function () {
    prt_ai_builder_guard('prt_ai_page_image');
    $pageId = absint($_REQUEST['page_id'] ?? 0);
    $page   = $pageId ? get_post($pageId) : null;
    if (! $page) {
        wp_safe_redirect(prt_settings_tab_url('ai', ['prt_ai_fill_error' => rawurlencode(__('Page not found.', 'pressroot'))]));
        exit;
    }

    $brand   = prt_brand_profile();
    $typeId  = (string) get_post_meta($pageId, '_prt_site_type', true);
    $stylist = [
        'photo'        => 'professional editorial photograph, natural light',
        'illustration' => 'flat modern vector illustration, bold shapes',
        'abstract'     => 'abstract gradient art, soft flowing shapes',
        'none'         => 'minimal abstract texture, subtle',
    ];
    $prompt = trim($page->post_title . ' — ' . ($brand['industry'] ?: $brand['desc'] ?: 'modern business') . ', '
        . ($stylist[$brand['imagery'] ?? 'photo'] ?? $stylist['photo'])
        . ($brand['tone'] ? ', mood: ' . $brand['tone'] : '')
        . ', website hero image, no text, no words, high quality');

    $gen = prt_ai_generate_image($prompt, 1536, 1024);
    if (empty($gen['ok'])) {
        wp_safe_redirect(prt_settings_tab_url('ai', ['prt_ai_fill_error' => rawurlencode($gen['error'] ?? 'error')]));
        exit;
    }

    require_once ABSPATH . 'wp-admin/includes/file.php';
    require_once ABSPATH . 'wp-admin/includes/media.php';
    require_once ABSPATH . 'wp-admin/includes/image.php';

    $attachId = media_handle_sideload([
        'name'     => sanitize_title($page->post_title) . '-ai-' . time() . '.jpg',
        'tmp_name' => $gen['file'],
    ], $pageId, sprintf(__('AI image for "%s"', 'pressroot'), $page->post_title));
    if (is_wp_error($attachId)) {
        wp_safe_redirect(prt_settings_tab_url('ai', ['prt_ai_fill_error' => rawurlencode($attachId->get_error_message())]));
        exit;
    }
    // Accessibility: media_handle_sideload's third arg is only the attachment
    // description — alt text must be set explicitly or every generated image
    // ships with empty alt (WCAG 1.1.1).
    update_post_meta($attachId, '_wp_attachment_image_alt', sprintf(
        /* translators: 1: page title, 2: industry/business description */
        __('%1$s — illustrative image for %2$s', 'pressroot'),
        $page->post_title,
        $brand['industry'] ?: ($brand['desc'] ?: get_bloginfo('name'))
    ));
    set_post_thumbnail($pageId, $attachId);
    $back = prt_ai_builder_back_url($pageId);
    wp_safe_redirect($back !== '' ? $back : prt_settings_tab_url('ai', ['prt_ai_img_done' => '1']));
    exit;
});

/** Brand hero image via the keyless Pollinations image endpoint. */
add_action('admin_post_prt_ai_brand_image', function () {
    prt_ai_builder_guard('prt_ai_brand_image');

    $brand   = prt_brand_profile();
    $stylist = [
        'photo'        => 'professional editorial photograph, natural light, shallow depth of field',
        'illustration' => 'flat modern vector illustration, bold shapes, friendly',
        'abstract'     => 'abstract gradient art, iris purple pink coral palette, soft flowing shapes',
        'none'         => 'minimal abstract texture, subtle, monochrome',
    ];
    $prompt = trim(($brand['industry'] ?: $brand['desc'] ?: 'modern small business') . ', '
        . ($stylist[$brand['imagery'] ?? 'photo'] ?? $stylist['photo'])
        . ($brand['tone'] ? ', mood: ' . $brand['tone'] : '')
        . ', no text, no words, high quality');

    require_once ABSPATH . 'wp-admin/includes/file.php';
    require_once ABSPATH . 'wp-admin/includes/media.php';
    require_once ABSPATH . 'wp-admin/includes/image.php';

    // Route through the image-provider registry (AI Models tab): paid
    // providers when selected + configured, automatic fallback to the free
    // keyless default on any failure — image generation never costs twice.
    $gen = function_exists('App\prt_ai_generate_image')
        ? prt_ai_generate_image($prompt, 900, 1140)
        : ['ok' => false, 'file' => '', 'error' => __('Image providers unavailable.', 'pressroot')];
    if (empty($gen['ok'])) {
        wp_safe_redirect(prt_settings_return_url('settings', ['prt_img_error' => rawurlencode($gen['error'] ?? 'error')]));
        exit;
    }
    $tmp = $gen['file'];
    $attachId = media_handle_sideload([
        'name'     => 'pressroot-brand-hero-' . time() . '.jpg',
        'tmp_name' => $tmp,
    ], 0, __('AI-generated brand hero image', 'pressroot'));
    if (is_wp_error($attachId)) {
        @unlink($tmp);
        wp_safe_redirect(prt_settings_return_url('settings', ['prt_img_error' => rawurlencode($attachId->get_error_message())]));
        exit;
    }

    // Accessibility: give the generated hero a real alt (see note in the
    // per-page handler above).
    update_post_meta($attachId, '_wp_attachment_image_alt', sprintf(
        /* translators: %s: industry or business description */
        __('Brand hero image for %s', 'pressroot'),
        $brand['industry'] ?: ($brand['desc'] ?: get_bloginfo('name'))
    ));
    set_theme_mod('prt_hero_portrait', wp_get_attachment_url($attachId));
    if (function_exists('App\\prt_flush_design_caches')) {
        prt_flush_design_caches();
    }
    // Setup-wizard runs return to step 4 (posted prt_return_tab/step);
    // Theme Settings runs keep landing on the settings tab as before.
    wp_safe_redirect(prt_settings_return_url('settings', ['prt_img_done' => '1']));
    exit;
});
