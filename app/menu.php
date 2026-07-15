<?php

/**
 * Off-canvas popout menu + social icon system.
 * - Hamburger toggle, shown per breakpoint (desktop/tablet/mobile).
 * - Slide-in panel with solid or gradient background + text color.
 * - Social icons are rendered as inline SVGs via Blade Icons (Simple Icons),
 *   each with a custom URL. (Font Awesome removed.)
 */

namespace App;

/**
 * Available social/icon networks: key => [label, default url].
 *
 * Sourced from prt_social_platforms() (app/social-links.php) so there's one
 * list of networks, not two — that file used to also register its own
 * (identical) set of prt_social_{key} settings with no controls attached,
 * shadowing the real ones registered below; that duplicate registration was
 * removed, and pressroot/social_platforms is now the actual extension point
 * for this list (pressroot/socials_map still runs after, for back-compat).
 */
function prt_socials_map()
{
    $out = [];
    foreach (prt_social_platforms() as $key => $p) {
        $out[$key] = [$p['label'], $p['default']];
    }
    return apply_filters('pressroot/socials_map', $out);
}

/**
 * Resolved list of social links the user has actually filled in (skips
 * networks whose URL is still blank), for the popout panel and footer to
 * loop over without each having to re-check every platform themselves.
 *
 * @return array<int, array{key:string,label:string,url:string,icon:string}>
 */
function prt_social_links()
{
    $out = [];
    foreach (prt_socials_map() as $key => $info) {
        $url = get_theme_mod("prt_social_{$key}", $info[1]);
        if (! empty($url)) {
            $out[] = [
                'key'   => $key,
                'label' => $info[0],
                'url'   => $url,
                'icon'  => prt_social_icon_name($key),
            ];
        }
    }
    return $out;
}

/**
 * Resolved popout panel appearance (solid or gradient background + text
 * color), built from Customizer values. Single source of truth consumed by
 * both the popout markup and the prt-theme-vars CSS variable block in
 * footer-content.php.
 *
 * @return array{bg:string,text:string}
 */
function prt_popout()
{
    $type = get_theme_mod('prt_popout_bgtype', 'solid');
    $c1   = get_theme_mod('prt_popout_bg', '#17151F');
    $c2   = get_theme_mod('prt_popout_grad2', '#6C4CF1');
    $ang  = absint(get_theme_mod('prt_popout_angle', 160));
    $bg   = $type === 'gradient' ? "linear-gradient({$ang}deg, {$c1}, {$c2})" : $c1;

    return [
        'bg'   => $bg,
        'text' => get_theme_mod('prt_popout_text', '#ffffff'),
    ];
}

/**
 * Which breakpoints show the hamburger instead of the inline nav.
 *
 * Three-state (auto/on/off), not a plain checkbox, so leaving this at its
 * default doesn't change anything: the theme's baseline single-breakpoint
 * behavior (inline nav above 768px, hamburger at/under it — see the
 * unconditional rules in resources/css/page-templates.css) keeps working
 * untouched. Only an explicit "on" (force hamburger) or "off" (force inline
 * nav) for a given tier emits a CSS override for it. This used to be plain
 * checkboxes wired to body classes (prt-ham-desktop/tablet/mobile) matched
 * against .nav-primary/.banner selectors that don't exist in this theme's
 * markup, so it never actually did anything — replaced with a real emitter
 * below targeting the current .header-nav / .header-social / .btn-hire /
 * .menu-toggle markup.
 *
 * Hooked to the custom prt_head_end action (fired from the header template)
 * rather than wp_head so this style tag lands right where the header expects
 * injected CSS, alongside the other Customizer-driven <style> blocks
 * (dark-mode, nav-options, header-layout).
 */
add_action('prt_head_end', function () {
    $tiers = [
        'prt_popout_desktop' => '@media(min-width:1025px)',
        'prt_popout_tablet'  => '@media(min-width:641px) and (max-width:1024px)',
        'prt_popout_mobile'  => '@media(max-width:640px)',
    ];
    $css = '';
    foreach ($tiers as $mod => $mq) {
        $v = get_theme_mod($mod, 'auto');
        if ($v === 'on') {
            $css .= $mq . '{.header-nav,.header-social,.header-actions>.btn-hire{display:none!important;}.menu-toggle{display:inline-flex!important;}}';
        } elseif ($v === 'off') {
            $css .= $mq . '{.header-nav{display:block!important;}.header-social{display:flex!important;}.header-actions>.btn-hire{display:inline-block!important;}.menu-toggle{display:none!important;}}';
        }
    }
    if ($css !== '') {
        echo "\n<style id=\"prt-popout-breakpoints\">" . $css . "</style>\n";
    }
}, 13);

/**
 * Customizer: Menu & Popout. Priority 22 so the shared "Theme Options" panel
 * (added lazily via get_panel/add_panel here and in the other Customizer
 * files) already exists or is created fresh without clobbering a sibling
 * section registered at a different priority.
 */
add_action('customize_register', function ($wp) {
    // Shared guarded helper — see prt_ensure_theme_options_panel() in app/customizer.php.
    prt_ensure_theme_options_panel($wp);

    $wp->add_section('prt_popout_section', [
        'title'       => __('Menu & Popout', 'pressroot'),
        'panel'       => 'prt_theme_options',
        'description' => __('Replace the inline nav with a menu icon that opens an off-canvas panel. Choose where it appears, its colors, and your social icons.', 'pressroot'),
    ]);

    // Breakpoints — auto (default; matches the theme's normal 768px behavior
    // untouched) / on (force hamburger) / off (force inline nav) per tier.
    $bpChoices = ['auto' => __('Auto (theme default)', 'pressroot'), 'on' => __('Menu icon', 'pressroot'), 'off' => __('Inline nav', 'pressroot')];
    foreach ([['prt_popout_desktop', __('Desktop (≥1025px)', 'pressroot')], ['prt_popout_tablet', __('Tablet (641–1024px)', 'pressroot')], ['prt_popout_mobile', __('Mobile (≤640px)', 'pressroot')]] as $bp) {
        $wp->add_setting($bp[0], ['default' => 'auto', 'sanitize_callback' => 'sanitize_key']);
        $wp->add_control($bp[0], ['label' => $bp[1], 'section' => 'prt_popout_section', 'type' => 'select', 'choices' => $bpChoices]);
    }

    // Background type
    $wp->add_setting('prt_popout_bgtype', ['default' => 'solid', 'sanitize_callback' => 'sanitize_key']);
    $wp->add_control('prt_popout_bgtype', ['label' => __('Panel background', 'pressroot'), 'section' => 'prt_popout_section', 'type' => 'select', 'choices' => ['solid' => __('Solid', 'pressroot'), 'gradient' => __('Gradient', 'pressroot')]]);

    $gradientOnly = function ($control) {
        $bgtype = $control->manager->get_setting('prt_popout_bgtype');
        return $bgtype && $bgtype->value() === 'gradient';
    };
    foreach ([['prt_popout_bg', __('Background / gradient start', 'pressroot'), '#17151F'], ['prt_popout_grad2', __('Gradient end', 'pressroot'), '#6C4CF1'], ['prt_popout_text', __('Text / icon color', 'pressroot'), '#ffffff']] as $col) {
        $wp->add_setting($col[0], ['default' => $col[2], 'sanitize_callback' => 'sanitize_hex_color']);
        $args = ['label' => $col[1], 'section' => 'prt_popout_section'];
        if ($col[0] === 'prt_popout_grad2') {
            $args['active_callback'] = $gradientOnly;
        }
        $wp->add_control(new \WP_Customize_Color_Control($wp, $col[0], $args));
    }

    $wp->add_setting('prt_popout_angle', ['default' => 160, 'sanitize_callback' => 'absint']);
    $wp->add_control('prt_popout_angle', [
        'label'           => __('Gradient angle (deg)', 'pressroot'),
        'section'         => 'prt_popout_section',
        'type'            => 'number',
        'input_attrs'     => ['min' => 0, 'max' => 360, 'step' => 5],
        'active_callback' => $gradientOnly,
    ]);

    // Social URL fields — each shows its Blade icon next to the network name.
    foreach (prt_socials_map() as $key => $info) {
        $preview = prt_social_icon($key, 'prt-soc-admin', ['width' => 16, 'height' => 16]);
        $wp->add_setting("prt_social_{$key}", ['default' => $info[1], 'sanitize_callback' => 'esc_url_raw']);
        $wp->add_control("prt_social_{$key}", [
            'label'       => sprintf(__('%s URL', 'pressroot'), $info[0]),
            'description' => '<span class="prt-soc-admin-row" style="display:inline-flex;align-items:center;gap:6px;color:#50575e">' . $preview . esc_html($info[0]) . '</span>',
            'section'     => 'prt_popout_section',
            'type'        => 'url',
        ]);
    }
}, 22);

/**
 * Inline toggle JS (no build needed) for the off-canvas panel: open/close on
 * hamburger click, click-outside (overlay), explicit close button, and Esc.
 * Kept as a plain inline <script> rather than an enqueued asset since it's a
 * few lines with no dependencies and needs to run on every page that has the
 * header, without adding a build step for something this small.
 */
add_action('wp_footer', function () {
    ?>
    <script>
    (function(){
      var t = document.querySelector('.menu-toggle');
      if (!t) return;
      var b = document.body;
      var overlay = document.querySelector('.prt-popout-overlay');
      var closeBtn = document.querySelector('.prt-popout-close');
      function open(){ b.classList.add('prt-popout-open'); t.setAttribute('aria-expanded','true'); var p=document.getElementById('prt-popout'); if(p){var f=p.querySelector('a,button'); if(f) f.focus();} }
      function close(){ b.classList.remove('prt-popout-open'); t.setAttribute('aria-expanded','false'); t.focus(); }
      t.addEventListener('click', function(){ b.classList.contains('prt-popout-open') ? close() : open(); });
      if (overlay) overlay.addEventListener('click', close);
      if (closeBtn) closeBtn.addEventListener('click', close);
      document.addEventListener('keydown', function(e){ if((e.key==='Escape'||e.keyCode===27) && b.classList.contains('prt-popout-open')) close(); });
    })();
    </script>
    <?php
}, 50);
