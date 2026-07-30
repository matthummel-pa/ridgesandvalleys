/**
 * Image picker for the Page-content image fields.
 *
 * Wires the "Choose image" / "Remove" buttons to the WordPress media library.
 * The chosen image URL is written into the field's hidden input and an `input`
 * event is fired, so the live preview updates just like a text change.
 */
(function () {
  'use strict';

  function ready(fn) {
    if (document.readyState !== 'loading') fn();
    else document.addEventListener('DOMContentLoaded', fn);
  }

  ready(function () {
    var box = document.getElementById('rv_page_content');
    if (!box || !window.wp || !wp.media) return;

    box.addEventListener('click', function (e) {
      var choose = e.target.closest('.rv-img-choose');
      var clear  = e.target.closest('.rv-img-clear');
      if (choose) { e.preventDefault(); openPicker(choose.closest('.rv-imgfield')); }
      if (clear)  { e.preventDefault(); setImage(clear.closest('.rv-imgfield'), ''); }
    });
  });

  function openPicker(field) {
    if (!field) return;
    var frame = wp.media({
      title: 'Choose image',
      button: { text: 'Use this image' },
      library: { type: 'image' },
      multiple: false
    });
    frame.on('select', function () {
      var att = frame.state().get('selection').first().toJSON();
      var url = (att.sizes && att.sizes.large) ? att.sizes.large.url : att.url;
      setImage(field, url);
    });
    frame.open();
  }

  function setImage(field, url) {
    if (!field) return;
    var input = field.querySelector('input[type=hidden]');
    var prev  = field.querySelector('.rv-imgprev');
    var clear = field.querySelector('.rv-img-clear');
    if (!input) return;

    input.value = url || '';
    if (url) {
      if (prev) { prev.style.backgroundImage = "url('" + url + "')"; prev.classList.add('has'); }
      if (clear) clear.hidden = false;
    } else {
      if (prev) { prev.style.backgroundImage = ''; prev.classList.remove('has'); }
      if (clear) clear.hidden = true;
    }
    // Mirror text-field behaviour so the live preview refreshes.
    input.dispatchEvent(new Event('input', { bubbles: true }));
  }
})();
