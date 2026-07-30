/**
 * Repeater controls for the Page-content editor.
 *
 * Each .rv-rep holds a list of rows plus a <template> for a fresh, empty row.
 * You can add, remove, and reorder rows; after any change the rows are renumbered
 * so their input names stay sequential (rv_f_key[0][...], rv_f_key[1][...], …),
 * which is the order that gets saved and previewed. A custom rv:repeater-change
 * event tells the live preview to refresh.
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

    function notifyChange() {
      box.dispatchEvent(new CustomEvent('rv:repeater-change', { bubbles: true }));
    }

    // Rewrite each row's field-name indexes to match its position in the list.
    function reindex(rep) {
      var name = rep.getAttribute('data-rep-name'); // e.g. rv_f_beliefs
      var rows = rep.querySelectorAll(':scope > .rv-rep-rows > .rv-rep-row');
      rows.forEach(function (row, i) {
        row.querySelectorAll('[name]').forEach(function (input) {
          // name looks like rv_f_beliefs[<idx>][sub]; swap the first [...] group.
          input.name = input.name.replace(
            /^(.*?)\[\d+\]/,
            name + '[' + i + ']'
          );
        });
      });
    }

    function addRow(rep) {
      var tpl = rep.querySelector(':scope > .rv-rep-tpl');
      var rowsWrap = rep.querySelector(':scope > .rv-rep-rows');
      if (!tpl || !rowsWrap) return;

      var html = tpl.innerHTML.replace(/__i__/g, rowsWrap.children.length);
      var tmp = document.createElement('div');
      tmp.innerHTML = html.trim();
      var row = tmp.firstElementChild;
      if (!row) return;
      rowsWrap.appendChild(row);
      reindex(rep);
      var firstInput = row.querySelector('input, textarea');
      if (firstInput) { try { firstInput.focus(); } catch (e) {} }
      notifyChange();
    }

    function removeRow(row) {
      var rep = row.closest('.rv-rep');
      row.remove();
      if (rep) reindex(rep);
      notifyChange();
    }

    function moveRow(row, dir) {
      var rep = row.closest('.rv-rep');
      if (dir < 0 && row.previousElementSibling) {
        row.parentNode.insertBefore(row, row.previousElementSibling);
      } else if (dir > 0 && row.nextElementSibling) {
        row.parentNode.insertBefore(row.nextElementSibling, row);
      } else {
        return;
      }
      if (rep) reindex(rep);
      notifyChange();
    }

    box.addEventListener('click', function (e) {
      var t = e.target;
      if (t.closest('.rv-rep-add')) {
        e.preventDefault();
        addRow(t.closest('.rv-rep'));
      } else if (t.closest('.rv-rep-del')) {
        e.preventDefault();
        removeRow(t.closest('.rv-rep-row'));
      } else if (t.closest('.rv-rep-up')) {
        e.preventDefault();
        moveRow(t.closest('.rv-rep-row'), -1);
      } else if (t.closest('.rv-rep-down')) {
        e.preventDefault();
        moveRow(t.closest('.rv-rep-row'), 1);
      }
    });
  });
})();
