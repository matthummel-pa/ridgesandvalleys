<?php

/**
 * Menu item icons + "mega" columns dropdown, both driven by CSS classes you add
 * in Appearance -> Menus (the item's "CSS Classes" field):
 *   - prt-ic-<icon>   e.g. "prt-ic-si-github" or "prt-ic-heroicon-o-rocket" prepends that Blade icon.
 *   - prt-mega        makes that item's submenu a wide, multi-column "mega" panel.
 *
 * NOTE on `.nav li.prt-mega` below: this only matches the `prt/bar-nav` block's
 * wp_nav_menu() call (menu_class: 'nav', full depth, in app/bar-blocks.php,
 * prt_block_bar_nav()) — the only nav render in this theme that both (a) uses
 * the `.nav` menu_class and (b) doesn't cap `depth`, so it's the only one that
 * outputs `.sub-menu` markup for this selector to target. The two other menu
 * renders (header.blade.php's `header-nav-list` primary nav, which also passes
 * `depth: 1` so it never emits submenu markup at all, and the popout's
 * `prt-popout-menu`) are NOT affected by this class. If you're not using the
 * "Bar · Navigation menu" block anywhere, adding `prt-mega` to a menu item's
 * CSS Classes field will have no visible effect.
 */

namespace App;

add_filter('nav_menu_item_title', function ($title, $item) {
    if (empty($item->classes) || ! is_array($item->classes)) {
        return $title;
    }
    foreach ($item->classes as $cls) {
        if (strpos($cls, 'prt-ic-') === 0) {
            $name = substr($cls, 6);
            $svg  = prt_icon($name, 'prt-menu-ic');
            if ($svg) {
                return '<span class="prt-menu-ic-wrap">' . $svg . '</span>' . $title;
            }
        }
    }
    return $title;
}, 10, 2);

add_action('prt_head_end', function () {
    echo "\n<style id=\"prt-menu-icons\">"
        . '.prt-menu-ic-wrap{display:inline-flex;vertical-align:-2px;margin-right:7px;}'
        . '.prt-menu-ic-wrap svg{width:16px;height:16px;fill:currentColor;}'
        . '.nav li.prt-mega{position:static;}'
        . '.nav li.prt-mega > .sub-menu{display:flex;flex-wrap:wrap;gap:6px 32px;min-width:min(680px,90vw);padding:20px 24px;}'
        . '.nav li.prt-mega > .sub-menu > li{flex:0 0 auto;}'
        . "</style>\n";
}, 16);
