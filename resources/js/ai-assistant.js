/* AI Setup Assistant — starter hero copy generator.
 * Routes through the server-side prt_ai_generate_copy AJAX endpoint
 * (app/ai-connectors.php) rather than calling any AI provider directly from
 * the browser — Pollinations needs no key so calling it client-side used to
 * be simplest, but the optional connectors (Gemini/Groq/OpenRouter) need a
 * secret API key that must never reach the browser, so ALL models now go
 * through the same PHP proxy for one consistent, secure code path. The
 * dropdown (#prt-ai-copy-model) lists Pollinations plus any connector with a
 * saved key (see Appearance -> AI Connectors). We ask the model to answer in
 * a fixed "HEADLINE: ... / SUBHEAD: ..." shape so it's easy to split back
 * into the two fields the UI shows; if it ever ignores that shape, we fall
 * back to just showing the raw text as the headline so nothing silently
 * breaks.
 */
(function () {
  var input   = document.getElementById('prt-ai-copy-input');
  var go      = document.getElementById('prt-ai-copy-go');
  var modelEl = document.getElementById('prt-ai-copy-model');
  var note    = document.getElementById('prt-ai-copy-note');
  var result  = document.getElementById('prt-ai-copy-result');
  var headEl  = document.getElementById('prt-ai-copy-headline');
  var subEl   = document.getElementById('prt-ai-copy-subhead');
  var regen   = document.getElementById('prt-ai-copy-regen');
  if (!input || !go) return; // Not on the AI Setup Assistant screen.

  var lastPrompt = '';

  function setNote(text) {
    note.textContent = text || '';
  }

  function parse(text) {
    var headline = '';
    var subhead  = '';
    var hMatch = text.match(/HEADLINE:\s*(.+)/i);
    var sMatch = text.match(/SUBHEAD:\s*(.+)/i);
    if (hMatch) headline = hMatch[1].trim();
    if (sMatch) subhead = sMatch[1].trim();
    if (!headline) {
      // Model didn't follow the format — show whatever it said rather than
      // failing silently, so the owner still gets something to work with.
      headline = text.trim().split('\n')[0] || '';
    }
    return { headline: headline, subhead: subhead };
  }

  function generate(description) {
    lastPrompt = description;
    setNote('Generating…');
    result.style.display = 'none';

    if (!window.prtAI) {
      setNote('Generation is unavailable on this screen.');
      return;
    }

    var body = new URLSearchParams();
    body.set('action', 'prt_ai_generate_copy');
    body.set('nonce', window.prtAI.nonce);
    body.set('description', description);
    body.set('model', modelEl ? modelEl.value : 'pollinations');

    fetch(window.prtAI.ajaxUrl, { method: 'POST', credentials: 'same-origin', body: body })
      .then(function (r) { return r.json(); })
      .then(function (json) {
        if (!json.success) {
          setNote((json.data && json.data.message) || 'Generation failed — try again.');
          return;
        }
        var parsed = parse(json.data.text || '');
        headEl.textContent = parsed.headline;
        subEl.textContent = parsed.subhead;
        result.style.display = parsed.headline ? 'block' : 'none';
        setNote(parsed.headline ? 'Generated. Copy what you like into your Hero pattern, or Regenerate for another take.' : 'Got an empty response — try again or rephrase.');
      })
      .catch(function () {
        setNote('Generation failed — check your connection and try again.');
      });
  }

  go.addEventListener('click', function () {
    var description = (input.value || '').trim();
    if (!description) {
      setNote('Describe your business first.');
      return;
    }
    generate(description);
  });

  if (regen) {
    regen.addEventListener('click', function () {
      if (lastPrompt) generate(lastPrompt);
    });
  }

  result.addEventListener('click', function (e) {
    var btn = e.target.closest('[data-copy]');
    if (!btn) return;
    var field = btn.getAttribute('data-copy') === 'headline' ? headEl : subEl;
    var text = field.textContent || '';
    if (!text) return;
    navigator.clipboard.writeText(text).then(function () {
      var original = btn.textContent;
      btn.textContent = 'Copied!';
      setTimeout(function () { btn.textContent = original; }, 1200);
    });
  });
})();
