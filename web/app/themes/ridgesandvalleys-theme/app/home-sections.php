<?php

/**
 * Home page layout — reorder / show-hide sections, and set the content photos.
 *
 * The front page renders its marketing sections through \App\home_sections(),
 * which returns the visible section keys in the order saved on the home page.
 * Each key maps to a partial resources/views/partials/home-<key>.blade.php.
 *
 * A "Home page layout" box is added to the home page editor (Pages → the page
 * set as the front page) with:
 *   - a sortable list (drag, or ↑/↓ buttons) to reorder sections
 *   - a show/hide checkbox per section
 *   - image pickers for the two content photos (Featured work, Rooted / local)
 *   - the hero proof-stat lines shown under the hero buttons
 *
 * Everything is stored as post meta on the front page, so it travels with the
 * page and needs no code edits. Nothing here touches the database on deploy.
 */

namespace App;

/**
 * Canonical home sections in their default order: key => human label.
 * The key maps to the partial resources/views/partials/home-<key>.blade.php.
 */
function home_sections_all(): array
{
    return [
        'problems' => __('Problems — “If this sounds familiar”', 'sage'),
        'packages' => __('Packages — pricing', 'sage'),
        'included' => __('Included in every build', 'sage'),
        'process'  => __('Process timeline', 'sage'),
        'featured' => __('Featured work (photo)', 'sage'),
        'rooted'   => __('Rooted / local (photo)', 'sage'),
        'towns'    => __('Towns served', 'sage'),
    ];
}

/** The page ID set as the static front page (0 if none). */
function home_page_id(): int
{
    return (int) get_option('page_on_front');
}

/**
 * Full saved order of section keys (visible + hidden), always covering every
 * known section. Unknown/renamed keys are dropped; new sections are appended.
 */
function home_section_order(): array
{
    $all = array_keys(home_sections_all());
    $pid = home_page_id();
    $saved = $pid ? (string) get_post_meta($pid, 'rv_home_order', true) : '';
    $order = array_values(array_intersect(
        array_filter(array_map('trim', explode(',', $saved))),
        $all
    ));
    foreach ($all as $k) {
        if (! in_array($k, $order, true)) {
            $order[] = $k;
        }
    }
    return $order;
}

/** Section keys the owner has hidden. */
function home_hidden_sections(): array
{
    $pid = home_page_id();
    $saved = $pid ? (string) get_post_meta($pid, 'rv_home_hidden', true) : '';
    return array_values(array_filter(array_map('trim', explode(',', $saved))));
}

/** Visible section keys in the saved order — used by front-page.blade.php. */
function home_sections(): array
{
    $hidden = home_hidden_sections();
    return array_values(array_filter(
        home_section_order(),
        fn ($k) => ! in_array($k, $hidden, true)
    ));
}

/**
 * Hero proof-stat rows for the strip under the hero buttons. Returns a list of
 * ['value' => ..., 'label' => ...]; reads the per-page "value | label" lines,
 * falling back to the built-in true defaults when none are set.
 */
function hero_stats(?int $post_id = null): array
{
    $post_id = $post_id ?: home_page_id();
    $raw = $post_id ? (string) get_post_meta($post_id, 'rv_f_hero_stats', true) : '';

    $lines = array_filter(array_map('trim', preg_split('/\r\n|\r|\n/', $raw) ?: []));
    if (! $lines) {
        $lines = [
            __('15+ yrs | building for the web', 'sage'),
            __('~7 days | to your first draft', 'sage'),
            __('WCAG 2.1 AA | accessibility, built in', 'sage'),
            __('You own it | domain, hosting & site', 'sage'),
        ];
    }

    $out = [];
    foreach ($lines as $line) {
        $parts = array_map('trim', explode('|', $line, 2));
        if ($parts[0] === '') {
            continue;
        }
        $out[] = ['value' => $parts[0], 'label' => $parts[1] ?? ''];
    }
    return $out;
}

/* -------------------------------------------------------------------------
 * Editor meta box (front page only)
 * ---------------------------------------------------------------------- */

add_action('add_meta_boxes_page', function ($post) {
    if (! $post || home_page_id() !== (int) $post->ID) {
        return;
    }
    add_meta_box(
        'rv_home_layout',
        __('Home page layout', 'sage'),
        __NAMESPACE__ . '\\home_layout_box',
        'page',
        'normal',
        'high'
    );
});

/** Load the WP media picker on the front page editor. */
add_action('admin_enqueue_scripts', function ($hook) {
    if ($hook !== 'post.php' && $hook !== 'post-new.php') {
        return;
    }
    $pid = isset($_GET['post']) ? (int) $_GET['post'] : 0;
    if ($pid && $pid === home_page_id()) {
        wp_enqueue_media();
    }
});

function home_layout_box($post): void
{
    $labels = home_sections_all();
    $order  = home_section_order();
    $hidden = home_hidden_sections();
    $featured_img    = (string) get_post_meta($post->ID, 'rv_f_featured_img', true);
    $rooted_img      = (string) get_post_meta($post->ID, 'rv_f_rooted_img', true);
    $featured_credit = (string) get_post_meta($post->ID, 'rv_f_featured_credit', true);
    $rooted_credit   = (string) get_post_meta($post->ID, 'rv_f_rooted_credit', true);
    $hero_stats      = (string) get_post_meta($post->ID, 'rv_f_hero_stats', true);
    $hero_credit     = (string) get_post_meta($post->ID, 'rv_f_hero_credit', true);

    wp_nonce_field('rv_home_layout', 'rv_home_layout_nonce');
    ?>
    <style>
      .rv-hl-note{color:#646970;margin:.2em 0 1em}
      .rv-hl-list{margin:0;padding:0;list-style:none;max-width:40rem}
      .rv-hl-item{display:flex;align-items:center;gap:.6em;padding:.55em .7em;margin:0 0 .4em;
        border:1px solid #dcdcde;border-radius:6px;background:#fff}
      .rv-hl-item.rv-hl-hidden{opacity:.55;background:#f6f7f7}
      .rv-hl-grip{cursor:grab;color:#a7aaad;font-size:1.1em;line-height:1;user-select:none}
      .rv-hl-name{flex:1;font-weight:600}
      .rv-hl-btn{cursor:pointer;border:1px solid #c3c4c7;background:#f6f7f7;border-radius:4px;
        width:1.7em;height:1.7em;line-height:1;font-size:14px;padding:0}
      .rv-hl-btn:disabled{opacity:.4;cursor:default}
      .rv-hl-vis{display:flex;align-items:center;gap:.35em;color:#50575e;white-space:nowrap}
      .rv-hl-images{display:flex;flex-wrap:wrap;gap:1.5em;margin-top:1.4em;
        padding-top:1.2em;border-top:1px solid #e0e0e0}
      .rv-hl-img{flex:1;min-width:15rem}
      .rv-hl-img h4{margin:.2em 0 .5em}
      .rv-hl-thumb{display:block;max-width:100%;height:auto;max-height:8rem;border:1px solid #dcdcde;
        border-radius:6px;margin-bottom:.5em;background:#f6f7f7}
      .rv-hl-thumb:not([src]),.rv-hl-thumb[src=""]{display:none}
      .rv-hl-stats{margin-top:1.4em;padding-top:1.2em;border-top:1px solid #e0e0e0}
      .rv-hl-stats h4{margin:.2em 0 .4em}
      .rv-hl-hero{margin-top:1.4em;padding-top:1.2em;border-top:1px solid #e0e0e0}
      .rv-hl-hero h4{margin:.2em 0 .4em}
    </style>

    <p class="rv-hl-note"><?php esc_html_e('Drag a section, or use the ↑ / ↓ buttons, to change the order it appears on the home page. Untick a section to hide it. Changes save when you press Update.', 'sage'); ?></p>

    <ul class="rv-hl-list" id="rv-hl-list">
      <?php foreach ($order as $key) :
          $is_hidden = in_array($key, $hidden, true); ?>
        <li class="rv-hl-item<?php echo $is_hidden ? ' rv-hl-hidden' : ''; ?>" draggable="true" data-key="<?php echo esc_attr($key); ?>">
          <span class="rv-hl-grip" aria-hidden="true">⠿</span>
          <span class="rv-hl-name"><?php echo esc_html($labels[$key] ?? $key); ?></span>
          <button type="button" class="rv-hl-btn rv-hl-up" title="<?php esc_attr_e('Move up', 'sage'); ?>">↑</button>
          <button type="button" class="rv-hl-btn rv-hl-down" title="<?php esc_attr_e('Move down', 'sage'); ?>">↓</button>
          <label class="rv-hl-vis">
            <input type="checkbox" name="rv_home_visible[]" value="<?php echo esc_attr($key); ?>" <?php checked(! $is_hidden); ?>>
            <?php esc_html_e('Show', 'sage'); ?>
          </label>
        </li>
      <?php endforeach; ?>
    </ul>
    <input type="hidden" name="rv_home_order" id="rv-home-order" value="<?php echo esc_attr(implode(',', $order)); ?>">

    <div class="rv-hl-hero">
      <h4><?php esc_html_e('Hero image (banner)', 'sage'); ?></h4>
      <label for="rv_f_hero_credit"><strong><?php esc_html_e('Description shown on the photo (lower-right)', 'sage'); ?></strong></label>
      <input type="text" id="rv_f_hero_credit" name="rv_f_hero_credit" class="widefat" value="<?php echo esc_attr($hero_credit); ?>" placeholder="<?php esc_attr_e('e.g. Cumberland Valley, South Central PA', 'sage'); ?>">
      <p class="rv-hl-note"><?php esc_html_e('Caption over the lower-right of the hero banner image, explaining what it shows. Leave blank to hide it. Font, colour and size: Theme Options → Image credits.', 'sage'); ?></p>
    </div>

    <div class="rv-hl-images">
      <?php
      $imgs = [
          'featured_img' => [
              'label'      => __('Featured work photo', 'sage'),
              'value'      => $featured_img,
              'help'       => __('Shown when no Project post has its own image.', 'sage'),
              'credit_key' => 'featured_credit',
              'credit'     => $featured_credit,
          ],
          'rooted_img' => [
              'label'      => __('Rooted / local photo', 'sage'),
              'value'      => $rooted_img,
              'help'       => __('The photo in the “Built here. Supported here.” band.', 'sage'),
              'credit_key' => 'rooted_credit',
              'credit'     => $rooted_credit,
          ],
      ];
      foreach ($imgs as $name => $cfg) : ?>
        <div class="rv-hl-img" data-img="<?php echo esc_attr($name); ?>">
          <h4><?php echo esc_html($cfg['label']); ?></h4>
          <img class="rv-hl-thumb" src="<?php echo esc_url($cfg['value']); ?>" alt="">
          <input type="hidden" name="rv_f_<?php echo esc_attr($name); ?>" class="rv-hl-img-input" value="<?php echo esc_url($cfg['value']); ?>">
          <p>
            <button type="button" class="button rv-hl-pick"><?php esc_html_e('Choose image', 'sage'); ?></button>
            <button type="button" class="button-link rv-hl-clear" style="<?php echo $cfg['value'] ? '' : 'display:none'; ?>"><?php esc_html_e('Remove', 'sage'); ?></button>
          </p>
          <p class="rv-hl-note"><?php echo esc_html($cfg['help']); ?></p>
          <p style="margin:.5em 0 .2em">
            <label for="rv_f_<?php echo esc_attr($cfg['credit_key']); ?>"><strong><?php esc_html_e('Description shown on the photo', 'sage'); ?></strong></label>
          </p>
          <input type="text" id="rv_f_<?php echo esc_attr($cfg['credit_key']); ?>" name="rv_f_<?php echo esc_attr($cfg['credit_key']); ?>" class="widefat" value="<?php echo esc_attr($cfg['credit']); ?>" placeholder="<?php esc_attr_e('e.g. 1935 aerial view of Gettysburg', 'sage'); ?>">
          <p class="rv-hl-note"><?php esc_html_e('Caption over the lower-right of this photo, explaining what it shows. Leave blank to hide it. Font, colour and size: Theme Options → Image credits.', 'sage'); ?></p>
        </div>
      <?php endforeach; ?>
    </div>

    <div class="rv-hl-stats">
      <h4><?php esc_html_e('Hero proof stats (under the buttons)', 'sage'); ?></h4>
      <label for="rv_f_hero_stats" class="screen-reader-text"><?php esc_html_e('Hero proof stats', 'sage'); ?></label>
      <textarea id="rv_f_hero_stats" name="rv_f_hero_stats" rows="4" class="widefat" placeholder="15+ yrs | building for the web&#10;~7 days | to your first draft&#10;WCAG 2.1 AA | accessibility, built in&#10;You own it | domain, hosting &amp; site"><?php echo esc_textarea($hero_stats); ?></textarea>
      <p class="rv-hl-note"><?php esc_html_e('One stat per line, formatted “value | label”. Leave blank to use the built-in defaults. Keep these to true, verifiable claims — no invented ratings or client counts.', 'sage'); ?></p>
    </div>

    <script>
    (function () {
      var list = document.getElementById('rv-hl-list');
      var orderInput = document.getElementById('rv-home-order');
      if (!list || !orderInput) return;

      function sync() {
        var keys = [];
        list.querySelectorAll('.rv-hl-item').forEach(function (li) { keys.push(li.getAttribute('data-key')); });
        orderInput.value = keys.join(',');
        var items = list.querySelectorAll('.rv-hl-item');
        items.forEach(function (li, i) {
          li.querySelector('.rv-hl-up').disabled = (i === 0);
          li.querySelector('.rv-hl-down').disabled = (i === items.length - 1);
        });
      }

      list.addEventListener('click', function (e) {
        var btn = e.target.closest('.rv-hl-up, .rv-hl-down');
        if (!btn) return;
        var li = btn.closest('.rv-hl-item');
        if (btn.classList.contains('rv-hl-up') && li.previousElementSibling) {
          li.parentNode.insertBefore(li, li.previousElementSibling);
        } else if (btn.classList.contains('rv-hl-down') && li.nextElementSibling) {
          li.parentNode.insertBefore(li.nextElementSibling, li);
        }
        sync();
      });

      list.addEventListener('change', function (e) {
        if (!e.target.matches('input[name="rv_home_visible[]"]')) return;
        e.target.closest('.rv-hl-item').classList.toggle('rv-hl-hidden', !e.target.checked);
      });

      // Drag to reorder
      var dragging = null;
      list.addEventListener('dragstart', function (e) {
        dragging = e.target.closest('.rv-hl-item');
        if (dragging) { dragging.style.opacity = '.4'; }
      });
      list.addEventListener('dragend', function () {
        if (dragging) { dragging.style.opacity = ''; dragging = null; sync(); }
      });
      list.addEventListener('dragover', function (e) {
        e.preventDefault();
        var over = e.target.closest('.rv-hl-item');
        if (!over || over === dragging || !dragging) return;
        var rect = over.getBoundingClientRect();
        var after = (e.clientY - rect.top) > rect.height / 2;
        list.insertBefore(dragging, after ? over.nextElementSibling : over);
      });

      sync();

      // Image pickers
      list.parentNode.querySelectorAll('.rv-hl-img').forEach(function (box) {
        var input = box.querySelector('.rv-hl-img-input');
        var thumb = box.querySelector('.rv-hl-thumb');
        var pick  = box.querySelector('.rv-hl-pick');
        var clear = box.querySelector('.rv-hl-clear');
        var frame = null;

        function setVal(url) {
          input.value = url || '';
          if (url) { thumb.src = url; thumb.style.display = ''; clear.style.display = ''; }
          else { thumb.removeAttribute('src'); thumb.style.display = 'none'; clear.style.display = 'none'; }
        }

        pick.addEventListener('click', function (e) {
          e.preventDefault();
          if (!window.wp || !wp.media) return;
          if (frame) { frame.open(); return; }
          frame = wp.media({ title: pick.textContent, multiple: false, library: { type: 'image' } });
          frame.on('select', function () {
            var a = frame.state().get('selection').first().toJSON();
            var url = (a.sizes && a.sizes.large) ? a.sizes.large.url : a.url;
            setVal(url);
          });
          frame.open();
        });
        clear.addEventListener('click', function (e) { e.preventDefault(); setVal(''); });
      });
    })();
    </script>
    <?php
}

/* -------------------------------------------------------------------------
 * Save
 * ---------------------------------------------------------------------- */

add_action('save_post_page', function ($post_id) {
    if (! isset($_POST['rv_home_layout_nonce'])
        || ! wp_verify_nonce(sanitize_key($_POST['rv_home_layout_nonce']), 'rv_home_layout')) {
        return;
    }
    if (defined('DOING_AUTOSAVE') && DOING_AUTOSAVE) {
        return;
    }
    if (! current_user_can('edit_page', $post_id)) {
        return;
    }

    $all = array_keys(home_sections_all());

    // Order
    $posted = isset($_POST['rv_home_order']) ? (string) wp_unslash($_POST['rv_home_order']) : '';
    $order  = array_values(array_intersect(
        array_filter(array_map('trim', explode(',', $posted))),
        $all
    ));
    foreach ($all as $k) {
        if (! in_array($k, $order, true)) {
            $order[] = $k;
        }
    }
    update_post_meta($post_id, 'rv_home_order', implode(',', $order));

    // Visibility
    $visible = isset($_POST['rv_home_visible']) && is_array($_POST['rv_home_visible'])
        ? array_map('sanitize_key', wp_unslash($_POST['rv_home_visible']))
        : [];
    $hidden = array_values(array_diff($all, $visible));
    update_post_meta($post_id, 'rv_home_hidden', implode(',', $hidden));

    // Content photos
    foreach (['featured_img', 'rooted_img'] as $key) {
        $raw = isset($_POST['rv_f_' . $key]) ? esc_url_raw(trim((string) wp_unslash($_POST['rv_f_' . $key]))) : '';
        if ($raw === '') {
            delete_post_meta($post_id, 'rv_f_' . $key);
        } else {
            update_post_meta($post_id, 'rv_f_' . $key, $raw);
        }
    }

    // On-photo description captions (rendered as .rv-img-credit overlays).
    foreach (['featured_credit', 'rooted_credit'] as $key) {
        $val = isset($_POST['rv_f_' . $key]) ? sanitize_text_field(wp_unslash($_POST['rv_f_' . $key])) : '';
        if ($val === '') {
            delete_post_meta($post_id, 'rv_f_' . $key);
        } else {
            update_post_meta($post_id, 'rv_f_' . $key, $val);
        }
    }

    // Hero proof stats — one "value | label" per line, shown under the buttons.
    $stats = isset($_POST['rv_f_hero_stats']) ? sanitize_textarea_field(wp_unslash($_POST['rv_f_hero_stats'])) : '';
    if ($stats === '') {
        delete_post_meta($post_id, 'rv_f_hero_stats');
    } else {
        update_post_meta($post_id, 'rv_f_hero_stats', $stats);
    }

    // Hero banner image caption (lower-right overlay). Drives rv_f_hero_credit,
    // which app/hero-credit.php renders into .rv-hero-credit. On the front page
    // this is the single source for the hero caption (hero-credit.php defers).
    // Blank hides it.
    if (isset($_POST['rv_f_hero_credit'])) {
        $hc = sanitize_text_field(wp_unslash($_POST['rv_f_hero_credit']));
        if ($hc === '') {
            delete_post_meta($post_id, 'rv_f_hero_credit');
            delete_post_meta($post_id, 'rv_f_hero_credit_show');
        } else {
            update_post_meta($post_id, 'rv_f_hero_credit', $hc);
            update_post_meta($post_id, 'rv_f_hero_credit_show', '1');
        }
    }
});
