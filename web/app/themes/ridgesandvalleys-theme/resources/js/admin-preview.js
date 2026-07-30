/**
 * Live preview for the Page-content editor.
 *
 * Watches every rv_f_* field (including repeater rows added on the fly); a short
 * debounce after a change it builds a form (on <body>, because a form inside the
 * meta box would be an invalid nested form and get stripped) and submits the
 * current values into the preview <iframe>, which re-renders the real page
 * through the theme templates with those draft values. Scroll position is
 * preserved across refreshes so the preview stays put.
 *
 * Clicking an element in the preview jumps to — and flashes — the matching input
 * in the editor, including individual repeater cells.
 */
(function () {
  'use strict';

  function ready(fn) {
    if (document.readyState !== 'loading') fn();
    else document.addEventListener('DOMContentLoaded', fn);
  }

  ready(function () {
    var box = document.getElementById('rv_page_content');
    if (!box) return;

    var wrap  = box.querySelector('.rv-pf-preview');
    var frame = document.getElementById('rv_pf_frame');
    if (!wrap || !frame) return;

    var action = wrap.getAttribute('data-pf-action');
    var nonce  = wrap.getAttribute('data-pf-nonce');
    if (!action || !nonce) return;

    var status = box.querySelector('.rv-pf-status');

    var timer = null;
    var lastScroll = 0;
    var first = true;

    function hidden(name, value) {
      var i = document.createElement('input');
      i.type = 'hidden';
      i.name = name;
      i.value = value;
      return i;
    }

    // rv_f_hero_title            -> rv_pf[hero_title]
    // rv_f_beliefs[0][title]     -> rv_pf[beliefs][0][title]
    function toPreviewName(fieldName) {
      var raw = fieldName.replace(/^rv_f_/, '');
      var m = raw.match(/^([^\[]+)(.*)$/);
      if (!m) return 'rv_pf[' + raw + ']';
      return 'rv_pf[' + m[1] + ']' + m[2];
    }

    function submitPreview() {
      // Re-query every time — repeater rows are added and removed dynamically.
      var fields = [].slice.call(box.querySelectorAll('[name^="rv_f_"]'));
      if (!fields.length) return;

      try { lastScroll = frame.contentWindow ? (frame.contentWindow.scrollY || 0) : 0; } catch (e) { lastScroll = 0; }

      var form = document.createElement('form');
      form.method = 'post';
      form.action = action;
      form.target = 'rv_pf_frame';
      form.style.display = 'none';
      form.appendChild(hidden('rv_preview', '1'));
      form.appendChild(hidden('rv_preview_nonce', nonce));
      fields.forEach(function (f) {
        // An unchecked checkbox shouldn't post its "1" — let the paired hidden
        // input carry the real 0 (otherwise required/opt-in always look checked).
        if (f.type === 'checkbox' && !f.checked) return;
        form.appendChild(hidden(toPreviewName(f.name), f.value));
      });

      document.body.appendChild(form);
      if (status) status.textContent = first ? 'Loading…' : 'Updating…';
      form.submit();
      document.body.removeChild(form);
    }

    function schedule() {
      clearTimeout(timer);
      timer = setTimeout(submitPreview, 600);
    }

    // Delegation so inputs created later (new repeater rows) are covered too.
    box.addEventListener('input', function (e) {
      if (e.target && /^rv_f_/.test(e.target.name || '')) schedule();
    });
    box.addEventListener('change', function (e) {
      if (e.target && /^rv_f_/.test(e.target.name || '')) schedule();
    });
    // Adding/removing/reordering repeater rows asks for a refresh directly.
    box.addEventListener('rv:repeater-change', schedule);

    frame.addEventListener('load', function () {
      if (status) status.textContent = '';
      first = false;
      try {
        if (lastScroll && frame.contentWindow) frame.contentWindow.scrollTo(0, lastScroll);
      } catch (e) {}
      wireClicks();
    });

    // Click an element in the preview → jump to its field in the editor.
    function wireClicks() {
      var doc;
      try { doc = frame.contentDocument; } catch (e) { return; }
      if (!doc || doc.__rvWired) return;
      doc.__rvWired = true;

      if (doc.head && !doc.getElementById('rv-pf-clickcss')) {
        var st = doc.createElement('style');
        st.id = 'rv-pf-clickcss';
        st.textContent = '[data-rv-field]{cursor:pointer} [data-rv-field]:hover{outline:2px dashed rgba(56,88,233,.75);outline-offset:2px;border-radius:2px}';
        doc.head.appendChild(st);
      }

      doc.addEventListener('click', function (e) {
        var tagged = e.target.closest('[data-rv-field]');
        var nav = e.target.closest('a, button');
        if (nav) e.preventDefault();            // keep the preview from navigating away
        if (tagged) { e.preventDefault(); focusField(tagged.getAttribute('data-rv-field')); }
      }, true);
    }

    // hero_title           -> [name="rv_f_hero_title"]
    // beliefs.0.title      -> [name="rv_f_beliefs[0][title]"]
    function fieldSelector(key) {
      var name;
      if (key.indexOf('.') === -1) {
        name = 'rv_f_' + key;
      } else {
        var p = key.split('.');
        name = 'rv_f_' + p[0];
        for (var i = 1; i < p.length; i++) name += '[' + p[i] + ']';
      }
      return '[name="' + name.replace(/"/g, '\\"') + '"]';
    }

    function focusField(key) {
      if (!key) return;
      var f = box.querySelector(fieldSelector(key));
      if (!f) return;
      f.scrollIntoView({ block: 'center', behavior: 'smooth' });
      f.classList.add('rv-pf-flash');
      setTimeout(function () { f.classList.remove('rv-pf-flash'); }, 1300);
      // Also flash the enclosing repeater row, if any.
      var row = f.closest('.rv-rep-row');
      if (row) {
        row.classList.add('rv-rep-flash');
        setTimeout(function () { row.classList.remove('rv-rep-flash'); }, 1300);
      }
      if (f.type !== 'hidden') { try { f.focus({ preventScroll: true }); } catch (e) { try { f.focus(); } catch (e2) {} } }
    }

    // Initial render. The block editor re-parents meta boxes shortly after load,
    // and moving an <iframe> reloads it — so fire a couple of delayed attempts
    // once the DOM has settled, and the last one sticks.
    function initialLoad() {
      setTimeout(submitPreview, 1000);
      setTimeout(submitPreview, 2600);
    }
    if (document.readyState === 'complete') initialLoad();
    else window.addEventListener('load', initialLoad);
  });
})();
