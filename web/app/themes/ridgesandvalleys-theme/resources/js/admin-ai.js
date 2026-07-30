/**
 * "Edit with AI" for the theme's custom fields (pages + projects).
 *
 * Adds a small button to each editable field that asks the site's connected AI
 * provider (WordPress 7.0 Connectors API) to improve, shorten, expand, fix, or
 * rewrite the text — or follow a custom instruction. All requests go through the
 * theme's own capability-checked REST endpoint (rv/v1/ai-edit).
 */
(function () {
  'use strict';

  var cfg = window.rvAI;
  if (!cfg) return;

  var BOX = cfg.postType === 'project' ? '#rv_project_case' : '#rv_page_content';
  var FIELD = cfg.postType === 'project' ? '[name^="_rv_"]' : '[name^="rv_f_"]';

  var pop = null, popField = null, popBtn = null;

  ready(function () {
    var box = document.querySelector(BOX);
    if (!box) return;
    injectStyles();
    addStatusPill(box);

    if (!cfg.ready) return;

    var fields = [].slice.call(box.querySelectorAll(FIELD)).filter(function (el) {
      return el.tagName === 'TEXTAREA' || (el.tagName === 'INPUT' && (el.type === 'text' || el.type === ''));
    });
    fields.forEach(addButton);
  });

  function addStatusPill(box) {
    var target = box.querySelector('.rv-fields, .rv-mb') || box;
    var pill;
    if (cfg.ready) {
      pill = document.createElement('span');
      pill.className = 'rv-ai-pill rv-ai-pill-on';
      pill.innerHTML = '<span class="rv-ai-dot"></span>' + esc(cfg.i18n.connected) +
        (cfg.providerLabel ? ' · ' + esc(cfg.providerLabel) : '');
    } else {
      pill = document.createElement('a');
      pill.className = 'rv-ai-pill rv-ai-pill-off';
      pill.href = cfg.settings;
      pill.title = cfg.i18n.notready || '';
      pill.innerHTML = '<span class="rv-ai-dot"></span>' + esc(cfg.i18n.disconnected);
    }
    target.insertBefore(pill, target.firstChild);
  }

  function addButton(field) {
    if (field.dataset.rvAi) return;
    field.dataset.rvAi = '1';
    var btn = document.createElement('button');
    btn.type = 'button';
    btn.className = 'rv-ai-btn';
    btn.innerHTML = '<span class="rv-ai-spark">✦</span> ' + esc(cfg.i18n.button);
    btn.addEventListener('click', function (e) {
      e.preventDefault();
      if (pop && popField === field) { closePopover(); return; }
      openPopover(field, btn);
    });
    field.insertAdjacentElement('afterend', btn);
  }

  function openPopover(field, btn) {
    closePopover();
    popField = field; popBtn = btn;

    pop = document.createElement('div');
    pop.className = 'rv-ai-pop';
    var acts = [
      ['improve', cfg.i18n.improve], ['shorten', cfg.i18n.shorten],
      ['expand', cfg.i18n.expand], ['fix', cfg.i18n.fix], ['rewrite', cfg.i18n.rewrite]
    ];
    var html = '<div class="rv-ai-pop-row">';
    acts.forEach(function (a) { html += '<button type="button" class="rv-ai-act" data-act="' + a[0] + '">' + esc(a[1]) + '</button>'; });
    html += '</div><div class="rv-ai-pop-custom"><input type="text" class="rv-ai-inp" placeholder="' + esc(cfg.i18n.custom) + '"><button type="button" class="rv-ai-apply">' + esc(cfg.i18n.apply) + '</button></div><div class="rv-ai-msg" hidden></div>';
    pop.innerHTML = html;
    document.body.appendChild(pop);
    position(pop, btn);

    pop.querySelectorAll('.rv-ai-act').forEach(function (b) {
      b.addEventListener('click', function () { run({ action: b.getAttribute('data-act') }); });
    });
    var inp = pop.querySelector('.rv-ai-inp');
    pop.querySelector('.rv-ai-apply').addEventListener('click', function () {
      var v = inp.value.trim(); if (!v) { inp.focus(); return; } run({ instruction: v });
    });
    inp.addEventListener('keydown', function (e) {
      if (e.key === 'Enter') { e.preventDefault(); var v = inp.value.trim(); if (v) run({ instruction: v }); }
      if (e.key === 'Escape') { closePopover(); }
    });
    setTimeout(function () { document.addEventListener('mousedown', outside); }, 0);
  }

  function outside(e) {
    if (pop && !pop.contains(e.target) && e.target !== popBtn && !(popBtn && popBtn.contains(e.target))) closePopover();
  }

  function closePopover() {
    if (pop) { pop.remove(); pop = null; popField = null; popBtn = null; }
    document.removeEventListener('mousedown', outside);
  }

  function position(el, btn) {
    var r = btn.getBoundingClientRect();
    el.style.top = (window.scrollY + r.bottom + 6) + 'px';
    var left = window.scrollX + r.left;
    var max = window.scrollX + document.documentElement.clientWidth - el.offsetWidth - 12;
    el.style.left = Math.max(12, Math.min(left, max)) + 'px';
  }

  function run(opts) {
    if (!pop || !popField) return;
    var field = popField;
    var msg = pop.querySelector('.rv-ai-msg');
    msg.hidden = true;
    var btns = [].slice.call(pop.querySelectorAll('button'));
    btns.forEach(function (b) { b.disabled = true; });
    var restore = popBtn.innerHTML;
    popBtn.classList.add('is-loading');
    popBtn.innerHTML = '<span class="rv-ai-spin"></span> ' + esc(cfg.i18n.thinking);

    fetch(cfg.endpoint, {
      method: 'POST',
      credentials: 'same-origin',
      headers: { 'Content-Type': 'application/json', 'X-WP-Nonce': cfg.nonce },
      body: JSON.stringify({
        text: field.value || '',
        action: opts.action || '',
        instruction: opts.instruction || '',
        label: labelFor(field)
      })
    }).then(function (r) {
      return r.json().then(function (j) { return { ok: r.ok, j: j }; }, function () { return { ok: false, j: null }; });
    }).then(function (res) {
      popBtn.classList.remove('is-loading');
      popBtn.innerHTML = restore;
      if (!res.ok || !res.j || typeof res.j.text !== 'string') {
        msg.textContent = (res.j && res.j.message) ? res.j.message : cfg.i18n.error;
        msg.hidden = false;
        btns.forEach(function (b) { b.disabled = false; });
        return;
      }
      applyEdit(field, res.j.text);
      closePopover();
    }).catch(function () {
      popBtn.classList.remove('is-loading');
      popBtn.innerHTML = restore;
      msg.textContent = cfg.i18n.error;
      msg.hidden = false;
      btns.forEach(function (b) { b.disabled = false; });
    });
  }

  function applyEdit(field, text) {
    var prev = field.value;
    field.value = text;
    field.dispatchEvent(new Event('input', { bubbles: true }));
    field.dispatchEvent(new Event('change', { bubbles: true }));
    markDirty();
    flash(field);
    showUndo(field, prev);
  }

  function showUndo(field, prev) {
    var btn = field.nextElementSibling && field.nextElementSibling.classList.contains('rv-ai-btn') ? field.nextElementSibling : null;
    var chip = document.createElement('span');
    chip.className = 'rv-ai-chip';
    chip.innerHTML = esc(cfg.i18n.edited) + ' <a href="#" class="rv-ai-undo">' + esc(cfg.i18n.undo) + '</a>';
    if (btn) btn.insertAdjacentElement('afterend', chip); else field.insertAdjacentElement('afterend', chip);
    chip.querySelector('.rv-ai-undo').addEventListener('click', function (e) {
      e.preventDefault();
      field.value = prev;
      field.dispatchEvent(new Event('input', { bubbles: true }));
      field.dispatchEvent(new Event('change', { bubbles: true }));
      chip.remove();
    });
    setTimeout(function () { if (chip.parentNode) chip.remove(); }, 15000);
  }

  function markDirty() {
    try {
      if (window.wp && wp.data && wp.data.dispatch('core/editor')) {
        wp.data.dispatch('core/editor').editPost({ meta: {} });
      }
    } catch (e) {}
  }

  function flash(field) {
    field.classList.add('rv-ai-flash');
    setTimeout(function () { field.classList.remove('rv-ai-flash'); }, 900);
  }

  function labelFor(field) {
    var l = field.id ? document.querySelector('label[for="' + cssEsc(field.id) + '"]') : null;
    return l ? l.textContent.trim() : '';
  }

  function ready(fn) {
    if (document.readyState !== 'loading') fn();
    else document.addEventListener('DOMContentLoaded', fn);
  }
  function esc(s) { var d = document.createElement('div'); d.textContent = s == null ? '' : String(s); return d.innerHTML; }
  function cssEsc(s) { return (window.CSS && CSS.escape) ? CSS.escape(s) : s; }

  function injectStyles() {
    if (document.getElementById('rv-ai-css')) return;
    var css = [
      '.rv-ai-btn{display:inline-flex;align-items:center;gap:.4em;margin:.35rem 0 0;font-size:11px;font-weight:600;line-height:1;color:#3858e9;background:#f0f3ff;border:1px solid #c9d3ff;border-radius:999px;padding:.42em .75em;cursor:pointer}',
      '.rv-ai-btn:hover{background:#e4ebff;border-color:#a9baff}',
      '.rv-ai-btn.is-loading{opacity:.75;cursor:default}',
      '.rv-ai-spark{font-size:11px;line-height:1}',
      '.rv-ai-spin{display:inline-block;width:10px;height:10px;border:2px solid #b9c6ff;border-top-color:#3858e9;border-radius:50%;animation:rvaispin .7s linear infinite}',
      '@keyframes rvaispin{to{transform:rotate(360deg)}}',
      '.rv-ai-pop{position:absolute;z-index:100010;background:#fff;border:1px solid #dcdcde;border-radius:8px;box-shadow:0 8px 30px rgba(0,0,0,.18);padding:.6rem;width:288px;font-size:12px}',
      '.rv-ai-pop-row{display:flex;flex-wrap:wrap;gap:.35rem;margin-bottom:.5rem}',
      '.rv-ai-act{flex:1 0 auto;font-size:12px;font-weight:600;color:#1d2327;background:#f6f7f7;border:1px solid #dcdcde;border-radius:6px;padding:.42em .6em;cursor:pointer}',
      '.rv-ai-act:hover:not(:disabled){background:#eef0f1;border-color:#c3c4c7}',
      '.rv-ai-act:disabled,.rv-ai-apply:disabled{opacity:.5;cursor:default}',
      '.rv-ai-pop-custom{display:flex;gap:.35rem}',
      '.rv-ai-inp{flex:1;font-size:12px;padding:.42em .5em;border:1px solid #dcdcde;border-radius:6px}',
      '.rv-ai-apply{font-size:12px;font-weight:600;color:#fff;background:#3858e9;border:1px solid #3858e9;border-radius:6px;padding:.42em .75em;cursor:pointer}',
      '.rv-ai-msg{margin-top:.5rem;color:#b32d2e;line-height:1.4}',
      '.rv-ai-chip{display:inline-block;margin:.35rem 0 0 .5rem;font-size:11px;color:#2a6a2f;background:#edf7ed;border:1px solid #cfe6cf;border-radius:6px;padding:.32em .55em;vertical-align:middle}',
      '.rv-ai-chip a{color:#3858e9}',
      '.rv-ai-flash{outline:2px solid #3858e9;outline-offset:1px}',
      '.rv-ai-note{background:#f0f3ff;border:1px solid #c9d3ff;border-radius:8px;padding:.7rem .9rem;margin:0 0 1rem;color:#2c3338;line-height:1.5}',
      '.rv-ai-note a{color:#3858e9}',
      '.rv-ai-pill{display:inline-flex;align-items:center;gap:.45em;font-size:11px;font-weight:600;line-height:1;border-radius:999px;padding:.42em .78em;margin:0 0 .85rem;text-decoration:none;vertical-align:middle}',
      '.rv-ai-pill-on{background:#e6f4ea;color:#0a6b2e;border:1px solid #b7e0c2}',
      '.rv-ai-pill-off{background:#fbeaea;color:#8a1f1f;border:1px solid #f0c9c9}',
      'a.rv-ai-pill-off:hover{background:#f7dede;color:#8a1f1f}',
      '.rv-ai-dot{width:7px;height:7px;border-radius:50%;background:currentColor;display:inline-block}'
    ].join('');
    var s = document.createElement('style'); s.id = 'rv-ai-css'; s.textContent = css; document.head.appendChild(s);
  }
})();
