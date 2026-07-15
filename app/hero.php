<?php

/**
 * Hero layout + site-wide scroll animations.
 *  - Hero: columns (1–3), content flex position (H/V), side image + 2nd image,
 *    background cover (with overlay + min-height), and an entrance animation.
 *  - Animations: on-scroll reveal for sections site-wide (IntersectionObserver),
 *    with a choice of effect + speed. Respects prefers-reduced-motion and degrades
 *    gracefully without JS (content only hides once <html> is marked anim-ready).
 */

namespace App;

/**
 * Shared list of animation effect choices, used to populate both the hero's
 * "entrance animation" select and the site-wide "scroll animation effect"
 * select. Single source of truth so the two controls always offer the same
 * options and their CSS/JS (below) only needs to support one list.
 */
function prt_anim_effects()
{
    return [
        'none'        => __('None', 'pressroot'),
        'fade-up'     => __('Fade up', 'pressroot'),
        'fade-in'     => __('Fade in', 'pressroot'),
        'zoom-in'     => __('Zoom in', 'pressroot'),
        'pop'         => __('Pop', 'pressroot'),
        'blur-in'     => __('Blur in', 'pressroot'),
        'slide-left'  => __('Slide from left', 'pressroot'),
        'slide-right' => __('Slide from right', 'pressroot'),
    ];
}

// Registers the "Hero" and "Animations" Customizer sections/controls. Priority
// 24 puts it after the panel-creating modules that typically run at the
// default priority, while still creating the panel itself defensively (in
// case this file loads before any other module that also registers it).
add_action('customize_register', function ($wp) {
    // Shared guarded helper — see prt_ensure_theme_options_panel() in app/customizer.php.
    prt_ensure_theme_options_panel($wp);

    $sel = function ($id, $label, $choices, $default, $section) use ($wp) {
        $wp->add_setting($id, ['default' => $default, 'sanitize_callback' => 'sanitize_text_field']);
        $wp->add_control($id, ['label' => $label, 'section' => $section, 'type' => 'select', 'choices' => $choices]);
    };

    /* ---- Hero ---- */
    $wp->add_section('prt_hero_section', [
        'title'       => __('Hero', 'pressroot'),
        'panel'       => 'prt_theme_options',
        'description' => __('Columns, content position, images, background cover and entrance animation for the reusable "Hero" block patterns (Inserter -> Patterns -> MH · Heroes) — insert one on any page to use them. The homepage\'s own hero only reads the two copy fields below.', 'pressroot'),
    ]);

    // Editable homepage hero copy — the homepage (resources/views/partials/home/hero.blade.php)
    // has its own hand-built layout (not the .prt-hero pattern styled above).
    // GENERIC BASE-THEME DEFAULTS: this used to ship the original owner's
    // personal copy hardcoded ("Hi there! I'm Matt…", "15+ yrs building");
    // every visible hero string is now a theme_mod with a neutral default,
    // so a fresh install reads like a starting point rather than someone
    // else's portfolio. The two-tone sentence is fully editable now too:
    // opening line + gradient word + serif word + closing phrase.
    // "Eyebrow" isn't included here: the homepage's small badge above the
    // title is the availability text (prt_avail_text), read by the partial.
    $wp->add_setting('prt_hero_title', ['default' => __('Your brand in.', 'pressroot'), 'sanitize_callback' => 'sanitize_text_field']);
    $wp->add_control('prt_hero_title', [
        'label'       => __('Homepage headline (opening line)', 'pressroot'),
        'description' => __('Followed by the gradient word, serif word, and closing phrase below.', 'pressroot'),
        'section'     => 'prt_hero_section',
        'type'        => 'text',
    ]);
    $wp->add_setting('prt_hero_accent', ['default' => __('Your site', 'pressroot'), 'sanitize_callback' => 'sanitize_text_field']);
    $wp->add_control('prt_hero_accent', ['label' => __('Headline gradient word', 'pressroot'), 'section' => 'prt_hero_section', 'type' => 'text']);
    $wp->add_setting('prt_hero_serif', ['default' => __('out.', 'pressroot'), 'sanitize_callback' => 'sanitize_text_field']);
    $wp->add_control('prt_hero_serif', ['label' => __('Headline serif word', 'pressroot'), 'section' => 'prt_hero_section', 'type' => 'text']);
    $wp->add_setting('prt_hero_suffix', ['default' => '', 'sanitize_callback' => 'sanitize_text_field']);
    $wp->add_control('prt_hero_suffix', ['label' => __('Headline closing phrase', 'pressroot'), 'section' => 'prt_hero_section', 'type' => 'text']);
    $wp->add_setting('prt_hero_subtext', ['default' => __('Deep enough for developers, sharp enough for marketers, simple enough for any business owner to run solo. Pick a site type, deal a design, make it yours.', 'pressroot'), 'sanitize_callback' => 'sanitize_textarea_field']);
    $wp->add_control('prt_hero_subtext', ['label' => __('Homepage subtext (paragraph)', 'pressroot'), 'section' => 'prt_hero_section', 'type' => 'textarea']);

    // Hero CTAs + floating chips — generic by default, all editable.
    $wp->add_setting('prt_hero_btn1_text', ['default' => __('See the work →', 'pressroot'), 'sanitize_callback' => 'sanitize_text_field']);
    $wp->add_control('prt_hero_btn1_text', ['label' => __('Primary button text', 'pressroot'), 'section' => 'prt_hero_section', 'type' => 'text']);
    $wp->add_setting('prt_hero_btn1_url', ['default' => '', 'sanitize_callback' => 'esc_url_raw']);
    $wp->add_control('prt_hero_btn1_url', ['label' => __('Primary button URL (blank = projects archive)', 'pressroot'), 'section' => 'prt_hero_section', 'type' => 'url']);
    $wp->add_setting('prt_hero_btn2_text', ['default' => __("Let's talk", 'pressroot'), 'sanitize_callback' => 'sanitize_text_field']);
    $wp->add_control('prt_hero_btn2_text', ['label' => __('Secondary button text', 'pressroot'), 'section' => 'prt_hero_section', 'type' => 'text']);
    $wp->add_setting('prt_hero_btn2_url', ['default' => '', 'sanitize_callback' => 'esc_url_raw']);
    $wp->add_control('prt_hero_btn2_url', ['label' => __('Secondary button URL (blank = contact page)', 'pressroot'), 'section' => 'prt_hero_section', 'type' => 'url']);
    $wp->add_setting('prt_hero_chip1', ['default' => __('⚡ Fast by default', 'pressroot'), 'sanitize_callback' => 'sanitize_text_field']);
    $wp->add_control('prt_hero_chip1', ['label' => __('Floating chip 1 (blank to hide)', 'pressroot'), 'section' => 'prt_hero_section', 'type' => 'text']);
    $wp->add_setting('prt_hero_chip2', ['default' => __('♿ Accessible first', 'pressroot'), 'sanitize_callback' => 'sanitize_text_field']);
    $wp->add_control('prt_hero_chip2', ['label' => __('Floating chip 2 (blank to hide)', 'pressroot'), 'section' => 'prt_hero_section', 'type' => 'text']);

    $sel('prt_hero_cols', __('Columns', 'pressroot'), ['1' => '1', '2' => '2', '3' => '3'], '1', 'prt_hero_section');
    $sel('prt_hero_align_h', __('Content horizontal position', 'pressroot'), ['left' => __('Left', 'pressroot'), 'center' => __('Center', 'pressroot'), 'right' => __('Right', 'pressroot')], 'center', 'prt_hero_section');
    $sel('prt_hero_align_v', __('Content vertical position', 'pressroot'), ['top' => __('Top', 'pressroot'), 'center' => __('Center', 'pressroot'), 'bottom' => __('Bottom', 'pressroot')], 'center', 'prt_hero_section');
    $sel('prt_hero_content_maxw', __('Content max width', 'pressroot'), ['0' => __('Default', 'pressroot'), '420' => '420px', '480' => '480px', '560' => '560px', '640' => '640px', '760' => '760px', 'full' => __('Full', 'pressroot')], '0', 'prt_hero_section');
    $sel('prt_hero_content_gap', __('Content spacing', 'pressroot'), ['0' => __('Default', 'pressroot'), '8' => __('Tight', 'pressroot'), '16' => __('Normal', 'pressroot'), '26' => __('Roomy', 'pressroot'), '40' => __('Extra', 'pressroot')], '0', 'prt_hero_section');
    $cw = ['0' => __('Default', 'pressroot'), '320' => '320px', '380' => '380px', '420' => '420px', '480' => '480px', '560' => '560px', '640' => '640px', 'full' => __('Full', 'pressroot')];
    $sel('prt_hero_content_maxw_tablet', __('Content max width (tablet)', 'pressroot'), $cw, '0', 'prt_hero_section');
    $sel('prt_hero_content_maxw_mobile', __('Content max width (mobile)', 'pressroot'), $cw, '0', 'prt_hero_section');

    // Advanced flexbox controls for the hero layout container.
    $sel('prt_hero_flex_dir', __('Flexbox: direction', 'pressroot'), ['0' => __('Default', 'pressroot'), 'row' => __('Row', 'pressroot'), 'row-reverse' => __('Row reverse', 'pressroot'), 'column' => __('Column', 'pressroot'), 'column-reverse' => __('Column reverse', 'pressroot')], '0', 'prt_hero_section');
    $sel('prt_hero_flex_justify', __('Flexbox: justify (main axis)', 'pressroot'), ['0' => __('Default', 'pressroot'), 'flex-start' => __('Start', 'pressroot'), 'center' => __('Center', 'pressroot'), 'flex-end' => __('End', 'pressroot'), 'space-between' => __('Space between', 'pressroot'), 'space-around' => __('Space around', 'pressroot'), 'space-evenly' => __('Space evenly', 'pressroot')], '0', 'prt_hero_section');
    $sel('prt_hero_flex_align', __('Flexbox: align (cross axis)', 'pressroot'), ['0' => __('Default', 'pressroot'), 'flex-start' => __('Start', 'pressroot'), 'center' => __('Center', 'pressroot'), 'flex-end' => __('End', 'pressroot'), 'stretch' => __('Stretch', 'pressroot'), 'baseline' => __('Baseline', 'pressroot')], '0', 'prt_hero_section');
    $sel('prt_hero_flex_wrap', __('Flexbox: wrap', 'pressroot'), ['0' => __('Default', 'pressroot'), 'wrap' => __('Wrap', 'pressroot'), 'nowrap' => __('No wrap', 'pressroot'), 'wrap-reverse' => __('Wrap reverse', 'pressroot')], '0', 'prt_hero_section');
    $sel('prt_hero_flex_gap', __('Flexbox: gap', 'pressroot'), ['0' => __('Default', 'pressroot'), '16' => '16px', '24' => '24px', '32' => '32px', '48' => '48px', '64' => '64px'], '0', 'prt_hero_section');
    $sel('prt_hero_img_side', __('Image side (2 columns)', 'pressroot'), ['right' => __('Right', 'pressroot'), 'left' => __('Left', 'pressroot')], 'right', 'prt_hero_section');

    $wp->add_setting('prt_hero_img', ['default' => '', 'sanitize_callback' => 'esc_url_raw']);
    $wp->add_control(new \WP_Customize_Image_Control($wp, 'prt_hero_img', ['label' => __('Side image / illustration', 'pressroot'), 'section' => 'prt_hero_section']));
    $wp->add_setting('prt_hero_img2', ['default' => '', 'sanitize_callback' => 'esc_url_raw']);
    $wp->add_control(new \WP_Customize_Image_Control($wp, 'prt_hero_img2', ['label' => __('Second image (3 columns)', 'pressroot'), 'section' => 'prt_hero_section']));

    $wp->add_setting('prt_hero_bg', ['default' => '', 'sanitize_callback' => 'esc_url_raw']);
    $wp->add_control(new \WP_Customize_Image_Control($wp, 'prt_hero_bg', ['label' => __('Background cover image', 'pressroot'), 'section' => 'prt_hero_section']));
    $wp->add_setting('prt_hero_overlay', ['default' => 45, 'sanitize_callback' => 'absint']);
    $wp->add_control('prt_hero_overlay', ['label' => __('Background overlay (%)', 'pressroot'), 'section' => 'prt_hero_section', 'type' => 'number', 'input_attrs' => ['min' => 0, 'max' => 90, 'step' => 5]]);
    $sel('prt_hero_minh', __('Hero min height', 'pressroot'), ['0' => __('Default', 'pressroot'), '420' => '420px', '520' => '520px', '640' => '640px', '100vh' => __('Full screen', 'pressroot')], '0', 'prt_hero_section');

    $sel('prt_hero_anim', __('Hero entrance animation', 'pressroot'), prt_anim_effects(), 'zoom-in', 'prt_hero_section');

    /* ---- Animations (scroll reveal) ---- */
    $wp->add_section('prt_anim_section', [
        'title'       => __('Animations', 'pressroot'),
        'panel'       => 'prt_theme_options',
        'description' => __('On-scroll reveal animations applied to sections site-wide.', 'pressroot'),
    ]);
    $wp->add_setting('prt_scroll_enable', ['default' => true, 'sanitize_callback' => 'wp_validate_boolean']);
    $wp->add_control('prt_scroll_enable', ['label' => __('Enable on-scroll animations (site-wide)', 'pressroot'), 'section' => 'prt_anim_section', 'type' => 'checkbox']);
    $sel('prt_scroll_effect', __('Scroll animation effect', 'pressroot'), prt_anim_effects(), 'zoom-in', 'prt_anim_section');
    $sel('prt_scroll_speed', __('Animation speed', 'pressroot'), ['fast' => __('Fast', 'pressroot'), 'normal' => __('Normal', 'pressroot'), 'slow' => __('Slow', 'pressroot')], 'normal', 'prt_anim_section');
}, 24);

/**
 * No-flash-of-hidden-content guard: adds the `prt-anim` class to <html> as
 * early as possible (wp_head, priority 3 — before most other head output)
 * via an inline script, rather than a PHP body class. The animation CSS below
 * only hides `[data-anim]` elements under `html.prt-anim`, so if JS is
 * disabled or errors before this runs, content simply never gets the
 * "hidden" state instead of being stuck invisible forever.
 */
add_action('wp_head', function () {
    $heroAnim = get_theme_mod('prt_hero_anim', 'zoom-in');
    if (! get_theme_mod('prt_scroll_enable', true) && $heroAnim === 'none') {
        return;
    }
    echo "<script>document.documentElement.classList.add('prt-anim');</script>\n";
}, 3);

/**
 * Emits the <style id="prt-hero"> block that turns the Hero Customizer
 * settings (columns, alignment, image side, background, min-height, etc.)
 * into actual CSS rules scoped to `.prt-hero`. Hooked on the theme's custom
 * `prt_head_end` action (not wp_head directly) so it renders alongside the
 * rest of the theme's dynamic per-page style blocks in one predictable spot.
 */
add_action('prt_head_end', function () {
    $cols = max(1, min(3, (int) get_theme_mod('prt_hero_cols', 1)));
    $ah   = get_theme_mod('prt_hero_align_h', 'center');
    $av   = get_theme_mod('prt_hero_align_v', 'center');
    $bg   = esc_url(get_theme_mod('prt_hero_bg', ''));
    $ov   = max(0, min(90, absint(get_theme_mod('prt_hero_overlay', 45))));
    $minh = (string) get_theme_mod('prt_hero_minh', '0');

    $css  = '.prt-hero{position:relative;}';
    $css .= '.prt-hero .prt-hero-inner{position:relative;z-index:2;}';

    if ($cols >= 2) {
        $css .= '.prt-hero .prt-hero-inner{display:flex;gap:48px;flex-wrap:wrap;align-items:center;text-align:left;}';
        $css .= '.prt-hero .prt-hero-content{flex:1 1 360px;min-width:0;}';
        $css .= '.prt-hero .prt-hero-media{flex:1 1 300px;}';
        $css .= '.prt-hero .prt-hero-media img{width:100%;height:auto;display:block;border-radius:18px;box-shadow:0 24px 60px rgba(16,18,24,.16);}';
        if (get_theme_mod('prt_hero_img_side', 'right') === 'left') {
            $css .= '.prt-hero .prt-hero-inner{flex-direction:row-reverse;}';
        }
    }

    // Content is a flex column so the alignment moves EVERY item (eyebrow, title,
    // lead, buttons) — not just the buttons. We also neutralise the children's own
    // auto-margins / text-align (e.g. .lead has margin:0 auto + text-align:center).
    $ai = $ah === 'left' ? 'flex-start' : ($ah === 'right' ? 'flex-end' : 'center');
    $ta = $ah === 'left' ? 'left' : ($ah === 'right' ? 'right' : 'center');
    $css .= '.prt-hero .prt-hero-content{display:flex;flex-direction:column;align-items:' . $ai . ';}';
    $css .= '.prt-hero .prt-hero-content > *{margin-left:0;margin-right:0;text-align:' . $ta . ';max-width:100%;}';
    $css .= '.prt-hero .btn-row{display:flex;flex-wrap:wrap;gap:12px;justify-content:' . $ai . ';}';

    // Content max-width + (for single-column heroes) block position.
    $maxw = (string) get_theme_mod('prt_hero_content_maxw', '0');
    if ($maxw !== '0' && $maxw !== 'full') {
        $css .= '.prt-hero .prt-hero-content{max-width:' . absint($maxw) . 'px;}';
        if ($cols < 2) {
            $bm = $ah === 'left' ? '0 auto 0 0' : ($ah === 'right' ? '0 0 0 auto' : '0 auto');
            $css .= '.prt-hero .prt-hero-content{margin:' . $bm . ';}';
        }
    }

    // Content spacing (gap between copy items).
    $gap = absint(get_theme_mod('prt_hero_content_gap', 0));
    if ($gap > 0) {
        $css .= '.prt-hero .prt-hero-content{gap:' . $gap . 'px;}';
        $css .= '.prt-hero .prt-hero-content > *{margin-top:0;margin-bottom:0;}';
    }

    // Always keep comfortable side padding so content never touches the device edge.
    $css .= '.prt-hero{padding-left:clamp(20px,5vw,28px);padding-right:clamp(20px,5vw,28px);box-sizing:border-box;}';

    // Per-breakpoint content max-width (tablet 641–1024, mobile ≤640). On a single
    // column we also re-apply the block position so it stays put when narrowed.
    $bwv = function ($v) {
        if ($v === 'full') {
            return 'none';
        }
        return ($v !== '' && $v !== '0') ? absint($v) . 'px' : '';
    };
    $bm  = $ah === 'left' ? '0 auto 0 0' : ($ah === 'right' ? '0 0 0 auto' : '0 auto');
    $pos = $cols < 2 ? 'margin:' . $bm . ';' : '';
    $mt  = $bwv((string) get_theme_mod('prt_hero_content_maxw_tablet', '0'));
    if ($mt !== '') {
        $css .= '@media(min-width:641px) and (max-width:1024px){.prt-hero .prt-hero-content{max-width:' . $mt . ';' . $pos . '}}';
    }
    $mm = $bwv((string) get_theme_mod('prt_hero_content_maxw_mobile', '0'));
    if ($mm !== '') {
        $css .= '@media(max-width:640px){.prt-hero .prt-hero-content{max-width:' . $mm . ';' . $pos . '}}';
    }

    // Multi-column: tighten the column gap as it narrows and stack to full width on mobile.
    if ($cols >= 2) {
        $css .= '@media(max-width:880px){.prt-hero .prt-hero-inner{gap:26px;}}';
        $css .= '@media(max-width:640px){.prt-hero .prt-hero-inner{gap:20px;}.prt-hero .prt-hero-content,.prt-hero .prt-hero-media{flex:1 1 100%;}}';
    }

    // Advanced flexbox overrides on the hero layout container (apply when chosen).
    $clean = function ($v) {
        return preg_replace('/[^a-z-]/', '', (string) $v);
    };
    $flex = '';
    $fd = (string) get_theme_mod('prt_hero_flex_dir', '0');
    $fj = (string) get_theme_mod('prt_hero_flex_justify', '0');
    $fa = (string) get_theme_mod('prt_hero_flex_align', '0');
    $fw = (string) get_theme_mod('prt_hero_flex_wrap', '0');
    $fg = absint(get_theme_mod('prt_hero_flex_gap', 0));
    if ($fd !== '0') {
        $flex .= 'flex-direction:' . $clean($fd) . ';';
    }
    if ($fj !== '0') {
        $flex .= 'justify-content:' . $clean($fj) . ';';
    }
    if ($fa !== '0') {
        $flex .= 'align-items:' . $clean($fa) . ';';
    }
    if ($fw !== '0') {
        $flex .= 'flex-wrap:' . $clean($fw) . ';';
    }
    if ($fg > 0) {
        $flex .= 'gap:' . $fg . 'px;';
    }
    if ($flex !== '') {
        $css .= '.prt-hero .prt-hero-inner{display:flex;' . $flex . '}';
    }

    if ($minh && $minh !== '0') {
        $h     = $minh === '100vh' ? '100vh' : (absint($minh) . 'px');
        $items = $av === 'top' ? 'flex-start' : ($av === 'bottom' ? 'flex-end' : 'center');
        $css .= '.prt-hero{min-height:' . $h . ';display:flex;align-items:' . $items . ';}';
        $css .= '.prt-hero > .prt-hero-inner{width:100%;}';
    }

    if ($bg) {
        $css .= '.prt-hero{background-image:url(\'' . $bg . '\');background-size:cover;background-position:center;border-radius:20px;overflow:hidden;}';
        $css .= '.prt-hero .prt-hero-overlay{position:absolute;inset:0;z-index:1;background:rgba(8,10,14,' . ($ov / 100) . ');}';
        $css .= '.prt-hero,.prt-hero .display-title,.prt-hero .lead{color:#fff;}';
        $css .= '.prt-hero .eyebrow{color:rgba(255,255,255,.86);}';
        $css .= '.prt-hero .btn-outline{background:rgba(255,255,255,.12);color:#fff;border-color:rgba(255,255,255,.5);}';
    }

    echo "\n<style id=\"prt-hero\">" . $css . "</style>\n";
}, 16);

/**
 * Emits the <style id="prt-anim"> block defining the "hidden"/pre-reveal
 * state for each `[data-anim="..."]` effect (translate/scale/blur) plus the
 * `.is-in` state that clears it, keyed off the chosen speed. Everything is
 * scoped under `html.prt-anim` so it only applies once the no-flash guard
 * above has confirmed JS is running; also force-disables via
 * prefers-reduced-motion for users who've asked for less motion.
 */
add_action('prt_head_end', function () {
    $heroAnim = get_theme_mod('prt_hero_anim', 'zoom-in');
    if (! get_theme_mod('prt_scroll_enable', true) && $heroAnim === 'none') {
        return;
    }
    $speed = get_theme_mod('prt_scroll_speed', 'normal');
    $dur   = $speed === 'fast' ? '.42s' : ($speed === 'slow' ? '.95s' : '.66s');

    $css  = 'html.prt-anim [data-anim]{opacity:0;transition:opacity ' . $dur . ' ease,transform ' . $dur . ' cubic-bezier(.2,.75,.25,1),filter ' . $dur . ' ease;will-change:transform,opacity;}';
    $css .= 'html.prt-anim [data-anim].is-in{opacity:1;transform:none;filter:none;}';
    $css .= 'html.prt-anim [data-anim="fade-up"]{transform:translateY(34px);}';
    $css .= 'html.prt-anim [data-anim="zoom-in"]{transform:scale(.88);}';
    $css .= 'html.prt-anim [data-anim="pop"]{transform:scale(.55);transition-timing-function:cubic-bezier(.34,1.56,.64,1);}';
    $css .= 'html.prt-anim [data-anim="blur-in"]{filter:blur(14px);transform:scale(1.03);}';
    $css .= 'html.prt-anim [data-anim="slide-left"]{transform:translateX(-48px);}';
    $css .= 'html.prt-anim [data-anim="slide-right"]{transform:translateX(48px);}';
    $css .= '@media (prefers-reduced-motion: reduce){html.prt-anim [data-anim]{opacity:1!important;transform:none!important;filter:none!important;transition:none!important;}}';

    echo "\n<style id=\"prt-anim\">" . $css . "</style>\n";
}, 17);

/**
 * Prints the vanilla-JS IntersectionObserver engine that actually reveals
 * animated elements: tags site-wide sections with `data-anim` (unless
 * already tagged, e.g. by a block that sets its own effect), reveals the
 * hero immediately on first paint, and reveals everything else as it
 * scrolls into view. Hooked late (priority 50) on wp_footer so it runs after
 * the page's own scripts/markup are already in the DOM. Includes a 3s
 * failsafe timeout that force-reveals everything in case the observer never
 * fires (e.g. an unexpected layout keeps elements permanently offscreen).
 */
add_action('wp_footer', function () {
    $heroAnim = get_theme_mod('prt_hero_anim', 'zoom-in');
    $enable   = (bool) get_theme_mod('prt_scroll_enable', true);
    $effect   = get_theme_mod('prt_scroll_effect', 'zoom-in');
    if (! $enable && $heroAnim === 'none') {
        return;
    }

    // Sections that receive scroll reveal site-wide.
    $selectors = '.home-section,.section-head,.card-grid,.project-grid,.project-card,.mini-card,.cta-card,.prt-cta-band,.prt-stat-strip,.post-single-title,.post-prose > h2,.post-prose > h3,.service-card,.archive-header,.project-hero,.readme-prose,.contact-form';

    $eff = esc_js($effect);
    // NOTE: not esc_js() — it HTML-encodes '>' (&gt;) and breaks the child
    // combinators in the selector list, throwing in querySelectorAll and
    // killing every scroll reveal. The list is a theme constant; just escape
    // quotes/backslashes for the JS string literal.
    $sel = addslashes($selectors);
    $on  = $enable ? '1' : '0';

    $js  = '(function(){';
    $js .= 'var R=window.matchMedia("(prefers-reduced-motion: reduce)").matches;';
    // Hero entrance: reveal on first paint.
    $js .= 'var hero=document.querySelectorAll(".prt-hero [data-anim],.prt-hero[data-anim]");';
    $js .= 'requestAnimationFrame(function(){requestAnimationFrame(function(){hero.forEach(function(el){el.classList.add("is-in");});});});';
    $js .= 'function revealAll(){document.querySelectorAll("[data-anim]").forEach(function(el){el.classList.add("is-in");});}';
    $js .= 'if(R){revealAll();return;}';
    $js .= 'if(' . $on . '){';
    $js .= 'var eff="' . $eff . '";';
    $js .= 'if(eff!=="none"){';
    $js .= 'var nodes=[].slice.call(document.querySelectorAll("' . $sel . '"));';
    $js .= 'nodes=nodes.filter(function(el){return !el.closest(".prt-hero");});';
    $js .= 'nodes.forEach(function(el){if(!el.hasAttribute("data-anim")){el.setAttribute("data-anim",eff);}});';
    $js .= 'if("IntersectionObserver" in window){';
    $js .= 'var io=new IntersectionObserver(function(en){en.forEach(function(e){if(e.isIntersecting){e.target.classList.add("is-in");io.unobserve(e.target);}});},{threshold:0.12,rootMargin:"0px 0px -8% 0px"});';
    $js .= 'nodes.forEach(function(el){io.observe(el);});';
    $js .= '}else{nodes.forEach(function(el){el.classList.add("is-in");});}';
    $js .= '}}';
    // Safety: reveal everything after 3s in case the observer never fires.
    $js .= 'setTimeout(revealAll,3000);';
    $js .= '})();';

    echo "\n<script id=\"prt-anim-js\">" . $js . "</script>\n";
}, 50);
