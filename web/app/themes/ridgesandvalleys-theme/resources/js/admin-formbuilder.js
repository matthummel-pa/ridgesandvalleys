/**
 * Visual drag-and-drop form builder for the "Contact form — fields" section.
 *
 * Each field is a card in a 6-column grid. Drag the ⠿ handle to reorder; the
 * Full / ½ / ⅓ buttons set the card's column width (and live grid span); the ⚙
 * reveals per-field settings. The cards carry the exact same input names as a
 * normal repeater row (…[i][label], [type], [width], …), and after any change the
 * row indexes are renumbered — so the existing save + live-preview paths work
 * unchanged. A rv:repeater-change event nudges the live preview to refresh.
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
    var fb = box.querySelector('.rv-fb');
    if (!fb) return;

    var canvas = fb.querySelector('.rv-fb-canvas');
    var name = fb.getAttribute('data-rep-name');
    if (!canvas || !name) return;

    function notify() {
      box.dispatchEvent(new CustomEvent('rv:repeater-change', { bubbles: true }));
    }

    // Renumber each card's [name] indexes to match its position in the grid.
    function reindex() {
      var cards = canvas.querySelectorAll(':scope > .rv-fb-card');
      cards.forEach(function (card, i) {
        card.querySelectorAll('[name]').forEach(function (inp) {
          inp.name = inp.name.replace(/^(.*?)\[\d+\]/, name + '[' + i + ']');
        });
      });
    }

    function setWidth(card, w) {
      if (!card) return;
      card.setAttribute('data-width', w);
      card.classList.remove('rv-fb--full', 'rv-fb--half', 'rv-fb--third');
      card.classList.add('rv-fb--' + w);
      var hid = card.querySelector('.rv-fb-width');
      if (hid) {
        hid.value = w;
        // Fire a real change event so the live-preview watcher refreshes — the
        // same path a <select> edit uses. (The custom event alone proved flaky.)
        hid.dispatchEvent(new Event('change', { bubbles: true }));
      }
      card.querySelectorAll('.rv-fb-wbtn').forEach(function (b) {
        b.classList.toggle('is-on', b.getAttribute('data-w') === w);
      });
      notify();
    }

    function addCard() {
      var tpl = fb.querySelector('.rv-fb-tpl');
      if (!tpl) return;
      var html = tpl.innerHTML.replace(/__i__/g, canvas.children.length);
      var tmp = document.createElement('div');
      tmp.innerHTML = html.trim();
      var card = tmp.firstElementChild;
      if (!card) return;
      canvas.appendChild(card);
      reindex();
      var body = card.querySelector('.rv-fb-body');
      if (body) body.hidden = false; // open new cards so they can be filled in
      var lab = card.querySelector('.rv-fb-label');
      if (lab) { try { lab.focus(); } catch (e) {} }
      notify();
    }

    fb.addEventListener('click', function (e) {
      var t = e.target;
      var wbtn = t.closest('.rv-fb-wbtn');
      if (wbtn) { e.preventDefault(); setWidth(wbtn.closest('.rv-fb-card'), wbtn.getAttribute('data-w')); return; }
      if (t.closest('.rv-fb-cog')) {
        e.preventDefault();
        var b = t.closest('.rv-fb-card').querySelector('.rv-fb-body');
        if (b) b.hidden = !b.hidden;
        return;
      }
      if (t.closest('.rv-fb-del')) {
        e.preventDefault();
        var card = t.closest('.rv-fb-card');
        if (card) { card.parentNode.removeChild(card); reindex(); notify(); }
        return;
      }
      if (t.closest('.rv-fb-add')) { e.preventDefault(); addCard(); return; }
    });

    // Drag-to-reorder. jQuery UI sortable is grid-aware enough with a matching
    // placeholder size; we size the placeholder to the dragged card's width.
    if (window.jQuery && jQuery.fn.sortable) {
      jQuery(canvas).sortable({
        handle: '.rv-fb-grip',
        items: '> .rv-fb-card',
        tolerance: 'pointer',
        forcePlaceholderSize: true,
        placeholder: 'rv-fb-ph',
        start: function (e, ui) {
          ui.item.addClass('rv-fb-drag');
          ui.placeholder.height(ui.item.outerHeight());
        },
        stop: function (e, ui) {
          ui.item.removeClass('rv-fb-drag');
          reindex();
          notify();
        }
      });
    }
  });
})();
