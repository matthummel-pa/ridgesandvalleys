{{--
  Template Name: Security Checker
--}}
@extends('layouts.app')

@section('content')
  @php($ctaHref = \App\cta_href(get_theme_mod('rv_cta_url', '/contact/')))
  @php($careHref = \App\cta_href(\App\field('sec_lock_care_url', \App\services_path() . '#care')))

  {{-- HERO --}}
  <section class="rv-hero">
    <span class="rv-stripe" aria-hidden="true"></span>
    @include('partials.hero-bg', ['fallback' => ''])
    <div class="rv-shell rv-hero-inner">
      {!! \App\eyebrow(\App\field('hero_eyebrow', __('Free security checker', 'sage'))) !!}
      <h1 class="rv-hero-title">{{ \App\field('hero_title', __('Is your website', 'sage')) }} <em class="rv-accent">{{ \App\field('hero_accent', __('locked up?', 'sage')) }}</em></h1>
      <p class="rv-hero-sub">{{ \App\field('hero_lede', __('HTTPS, headers, leaks, cookies — each result in plain English, and why it matters.', 'sage')) }}</p>
      <div class="rv-hero-actions">
        <a class="rv-btn {{ \App\hero_btn_class(\App\field('hero_btn1_style',''), 'rv-btn-primary') }}" href="#security">{{ \App\field('hero_btn1', __('Check my site', 'sage')) }}</a>
        <a class="rv-btn {{ \App\hero_btn_class(\App\field('hero_btn2_style',''), 'rv-btn-ghost') }}" href="{{ $ctaHref }}">{{ \App\field('hero_btn2', __('Talk to me', 'sage')) }}</a>
      </div>
    </div>
  </section>

  {{-- LIVE SECURITY CHECKER --}}
  <section id="security" class="rv-shell rv-band">
    {!! \App\eyebrow(__('Instant security scan', 'sage')) !!}
    <h2 class="rv-section-title">{{ __('Scan a page for', 'sage') }} <em class="rv-accent">{{ __('safety.', 'sage') }}</em></h2>
    <p class="rv-page-intro">{{ __('Paste your site — yourbusiness.com is fine. Free, no email. A first-pass check of what browsers see, not a penetration test.', 'sage') }}</p>

    <div class="rv-scan" id="rv-sec" data-endpoint="{{ esc_url(rest_url('rv-tools/v1/security')) }}" data-cta="{{ esc_url($ctaHref) }}" style="margin-top:2rem" aria-label="{{ __('Security checker', 'sage') }}">
      <div class="rv-scan-head">
        <div class="rv-scan-head-row">
          <h3 class="rv-scan-title">{{ __('Security report', 'sage') }}</h3>
          <span class="rv-scan-count" id="rv-sec-count"></span>
        </div>
        <form class="rv-audit-form" id="rv-sec-form">
          <label class="screen-reader-text" for="rv-sec-url">{{ __('Website to scan', 'sage') }}</label>
          <input type="text" id="rv-sec-url" name="url" inputmode="url" autocomplete="url" spellcheck="false" placeholder="yourbusiness.com" required>
          <button type="submit" class="rv-btn rv-btn-primary" id="rv-sec-run">{{ __('Check security', 'sage') }}</button>
        </form>
        <div class="rv-scan-bar"><span class="rv-scan-bar-fill" id="rv-sec-fill"></span></div>
        <p class="rv-scan-status" id="rv-sec-status" role="status" aria-live="polite">
          <span class="rv-scan-live">{{ __('Enter a site to run the security scan.', 'sage') }}</span>
        </p>
      </div>
      <div class="rv-scan-body" id="rv-sec-results" hidden></div>
      <div class="rv-scan-actions">
        <button type="button" class="rv-btn rv-btn-ghost" id="rv-sec-reset">{{ __('Reset', 'sage') }}</button>
      </div>
    </div>
    <p class="rv-audit-note">{{ __('This checks configuration and headers, not your passwords, plugins, or server internals. Want a real hardening pass and ongoing monitoring?', 'sage') }} <a href="{{ $ctaHref }}">{{ __('Talk to me', 'sage') }}</a>.</p>
  </section>

  {{-- COVERS / DOESN'T COVER --}}
  <section class="rv-band rv-band-alt rv-sec-cover" aria-labelledby="rv-sec-cover-title">
    <div class="rv-shell">
      {!! \App\eyebrow(\App\field('sec_cover_eyebrow', __('What this scan covers', 'sage'))) !!}
      <h2 id="rv-sec-cover-title" class="rv-section-title">{{ \App\field('sec_cover_title', __('A hygiene check,', 'sage')) }} <em class="rv-accent">{{ \App\field('sec_cover_accent', __('not a pentest.', 'sage')) }}</em></h2>
      <p class="rv-page-intro">{{ \App\field('sec_cover_lede', __('It reads what browsers — and attackers — see in your page and its headers. Free, no email. The closer below is for the work this scan cannot do.', 'sage')) }}</p>
      <div class="rv-sec-bounds">
        <div class="rv-sec-bound">
          <h3>{{ \App\field('sec_cover_yes_title', __('This scan looks at', 'sage')) }}</h3>
          <ul>
            @foreach (\App\field_lines('sec_cover_yes', \App\security_cover_yes_defaults()) as $line)
              <li>{{ $line }}</li>
            @endforeach
          </ul>
        </div>
        <div class="rv-sec-bound rv-sec-bound-no">
          <h3>{{ \App\field('sec_cover_no_title', __('It does not check', 'sage')) }}</h3>
          <ul>
            @foreach (\App\field_lines('sec_cover_no', \App\security_cover_no_defaults()) as $line)
              <li>{{ $line }}</li>
            @endforeach
          </ul>
        </div>
      </div>
    </div>
  </section>

  {{-- LOCKDOWN CLOSER --}}
  <section class="rv-cta-band rv-sec-lock" aria-labelledby="rv-sec-lock-title">
    <div class="rv-shell rv-sec-lock-inner">
      <div class="rv-sec-lock-copy">
        {!! \App\eyebrow(\App\field('sec_lock_eyebrow', __('After the scan', 'sage'))) !!}
        <h2 id="rv-sec-lock-title" class="rv-cta-title">{{ \App\field('sec_lock_title', __('Want it locked down.', 'sage')) }} <em class="rv-accent">{{ \App\field('sec_lock_accent', __('And kept that way.', 'sage')) }}</em></h2>
        <p class="rv-cta-sub">{{ \App\field('sec_lock_lede', __('The checker finds the gaps. I close them — HTTPS, headers, backups, updates — and keep an eye on the site so it stays that way. Optional Care & Grow from $179 a month. Cancel anytime. You keep the keys.', 'sage')) }}</p>
        <div class="rv-hero-actions rv-sec-lock-actions">
          <a class="rv-btn rv-btn-on-dark" href="{{ $ctaHref }}">{{ \App\field('sec_lock_btn', __('Secure my site', 'sage')) }}</a>
          <a class="rv-btn rv-btn-ghost rv-sec-lock-ghost" href="{{ $careHref }}">{{ \App\field('sec_lock_btn2', __('See Care & Grow', 'sage')) }}</a>
        </div>
        <p class="rv-sec-lock-fine">{{ \App\field('sec_lock_fine', __('Fixed price · You own the site · A real person answers', 'sage')) }}</p>
      </div>
      <ul class="rv-sec-lock-list">
        @foreach (\App\field_rows('sec_lock_items', \App\security_lock_item_defaults()) as $item)
          <li class="rv-sec-lock-item">
            @if (($item['kicker'] ?? '') !== '')<span class="rv-sec-lock-kicker">{{ $item['kicker'] }}</span>@endif
            @if (($item['title'] ?? '') !== '')<h3>{{ $item['title'] }}</h3>@endif
            @if (($item['text'] ?? '') !== '')<p>{{ $item['text'] }}</p>@endif
          </li>
        @endforeach
      </ul>
    </div>
  </section>
  <style>
    .rv-sec-lock{position:relative;overflow:hidden;background:linear-gradient(105deg,rgba(18,21,15,.84) 0%,rgba(33,61,51,.58) 48%,rgba(176,85,58,.38) 100%),var(--ridgeline)}
    .rv-sec-lock::before{content:"";position:absolute;left:0;right:0;top:0;height:5px;background:var(--stripe);z-index:1}
    .rv-sec-lock-inner{position:relative;z-index:1;display:grid;grid-template-columns:minmax(0,1.08fr) minmax(0,.92fr);gap:clamp(1.75rem,4vw,3rem);align-items:center;padding-block:clamp(2.75rem,6vw,4.5rem)}
    .rv-sec-lock .rv-eyebrow{color:var(--color-wheat)}
    .rv-sec-lock .rv-cta-title{margin:.2rem 0 .9rem;max-width:22ch;font-size:clamp(1.9rem,3.8vw,2.85rem);line-height:1.08}
    .rv-sec-lock .rv-cta-sub{max-width:46ch;margin:0;color:rgba(255,255,255,.9);font-size:1.05rem;line-height:1.6}
    .rv-sec-lock-actions{justify-content:flex-start;margin-top:1.5rem}
    .rv-sec-lock-ghost{background:rgba(255,255,255,.08);color:#fff;border-color:rgba(255,255,255,.42)}
    .rv-sec-lock-ghost:hover{border-color:#fff;background:rgba(255,255,255,.16);color:#fff}
    .rv-sec-lock-fine{margin:1rem 0 0;font-family:var(--font-mono);font-size:.75rem;letter-spacing:.04em;color:rgba(247,241,230,.72)}
    .rv-sec-lock-list{list-style:none;margin:0;padding:0;display:grid;gap:.8rem}
    .rv-sec-lock-item{position:relative;background:rgba(255,255,255,.08);border:1px solid rgba(255,255,255,.16);border-radius:var(--radius-lg,16px);padding:1.1rem 1.25rem 1.1rem 1.45rem;overflow:hidden}
    .rv-sec-lock-item::before{content:"";position:absolute;left:0;top:0;bottom:0;width:4px;background:var(--color-wheat)}
    .rv-sec-lock-kicker{display:block;font-family:var(--font-mono);font-size:.68rem;letter-spacing:.1em;text-transform:uppercase;color:var(--color-wheat);font-weight:600}
    .rv-sec-lock-item h3{font-family:var(--font-display);font-size:1.08rem;font-weight:700;color:#fff;margin:.2rem 0 .3rem;line-height:1.2}
    .rv-sec-lock-item p{margin:0;color:rgba(255,255,255,.84);font-size:.92rem;line-height:1.5}
    @media(max-width:820px){.rv-sec-lock-inner{grid-template-columns:1fr}.rv-sec-lock .rv-cta-title{max-width:none}}
    @media(prefers-reduced-motion:reduce){.rv-sec-lock-ghost:hover{transform:none}}
    .rv-scan{scroll-margin-top:6rem}
    .rv-sec-cover .rv-page-intro{max-width:54ch}
    .rv-sec-bounds{display:grid;grid-template-columns:1fr 1fr;gap:1rem;margin-top:1.35rem}
    .rv-sec-bound{padding:1.35rem 1.45rem;border:1px solid var(--color-line);border-radius:var(--radius-lg,16px);background:var(--color-surface)}
    .rv-sec-bound h3{font-family:var(--font-mono);font-size:.68rem;letter-spacing:.08em;text-transform:uppercase;color:var(--color-clay);font-weight:700;margin:0 0 .7rem}
    .rv-sec-bound ul{list-style:none;margin:0;padding:0;display:grid;gap:.45rem}
    .rv-sec-bound li{position:relative;padding-left:1.45rem;color:var(--color-body);font-size:.95rem;line-height:1.45}
    .rv-sec-bound li::before{content:"";position:absolute;left:0;top:.35em;width:.55rem;height:.55rem;border-radius:50%;background:var(--color-pine)}
    .rv-sec-bound-no li::before{background:var(--color-clay)}
    .rv-sec-first{padding:1.35rem 1.6rem 1.2rem;border-bottom:1.5px solid var(--color-line);background:var(--color-paper)}
    .rv-sec-first h3{margin:0;font-size:1.15rem}
    .rv-sec-first-lead{margin:.35rem 0 0;color:var(--color-muted);font-size:.95rem}
    .rv-sec-first .rv-crit-list{padding:0;margin-top:.85rem}
    .rv-sec-next{display:grid;grid-template-columns:minmax(0,1fr) auto;gap:1rem 1.5rem;align-items:center;padding:1.35rem 1.6rem;border-bottom:1.5px solid var(--color-line);background:color-mix(in srgb,var(--color-pine) 9%,var(--color-surface))}
    .rv-sec-next h3{margin:0 0 .3rem;font-size:1.12rem}
    .rv-sec-next p{margin:0;color:var(--color-body);font-size:.95rem;line-height:1.5;max-width:46ch}
    .rv-sec-next .rv-btn{white-space:nowrap}
    @media(max-width:720px){.rv-sec-bounds{grid-template-columns:1fr}.rv-sec-next{grid-template-columns:1fr}.rv-sec-next .rv-btn{width:100%;justify-content:center}}
    .rv-crit.is-na .rv-crit-tick{background:var(--color-slate);border-color:var(--color-slate)}
    .rv-crit.is-na .rv-crit-tick::after{content:"–";color:#fff;font-size:.95rem}
    .rv-crit.is-na .rv-crit-state{background:#e6ebed;color:#33454a}
    .rv-cat-score[data-grade="N/A"],.rv-cat-score[data-grade="Info"]{background:var(--color-cream);color:var(--color-muted);border:1px solid var(--color-line)}
  </style>

  <script>
    (function () {
      var root = document.getElementById('rv-sec');
      if (!root) return;
      var form   = document.getElementById('rv-sec-form');
      var input  = document.getElementById('rv-sec-url');
      var runBtn = document.getElementById('rv-sec-run');
      var resetB = document.getElementById('rv-sec-reset');
      var count  = document.getElementById('rv-sec-count');
      var fill   = document.getElementById('rv-sec-fill');
      var status = document.getElementById('rv-sec-status');
      var live   = status ? status.querySelector('.rv-scan-live') : null;
      var out    = document.getElementById('rv-sec-results');
      var endpoint = root.getAttribute('data-endpoint');
      var reduce = window.matchMedia('(prefers-reduced-motion: reduce)').matches;
      var LABEL = { pass: 'Pass', warn: 'Review', fail: 'Fail', na: 'N/A' };
      var timers = [];
      var ctaHref = root.getAttribute('data-cta') || '/contact/';

      function esc(s) {
        return String(s == null ? '' : s).replace(/[&<>"]/g, function (c) {
          return { '&': '&amp;', '<': '&lt;', '>': '&gt;', '"': '&quot;' }[c];
        });
      }
      function normalizeUrl(u) {
        u = String(u || '').trim().replace(/\s+/g, '');
        if (!u) return u;
        if (!/^[a-z][a-z0-9+.-]*:/i.test(u)) u = 'https://' + u.replace(/^\/+/, '');
        return u;
      }
      function clearTimers() { timers.forEach(clearTimeout); timers = []; }
      function reset() {
        clearTimers();
        if (out) { out.hidden = true; out.innerHTML = ''; }
        if (count) count.textContent = '';
        if (fill) fill.style.width = '0';
        if (status) status.classList.remove('is-done', 'is-error');
        if (live) live.textContent = 'Enter a site to run the security scan.';
      }
      function checkRow(chk, alwaysWhy) {
        var why = chk.why && (alwaysWhy || chk.status === 'fail' || chk.status === 'warn' || chk.status === 'na')
          ? '<span class="rv-crit-why"><b>Why:</b> ' + esc(chk.why) + '</span>'
          : '';
        return '<li class="rv-crit is-' + chk.status + '">'
          + '<span class="rv-crit-tick" aria-hidden="true">'
          + (chk.status === 'pass' ? '<svg viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="3" stroke-linecap="round" stroke-linejoin="round"><path d="M20 6L9 17l-5-5"/></svg>' : '')
          + '</span><span class="rv-crit-main">'
          + '<span class="rv-crit-name">' + esc(chk.label) + (chk.detail ? ' <span class="rv-crit-num">· ' + esc(chk.detail) + '</span>' : '') + '</span>'
          + why
          + '</span><span class="rv-crit-side"><span class="rv-crit-state">' + (LABEL[chk.status] || '') + '</span></span></li>';
      }
      function firstIssues(d) {
        var items = [];
        (d.categories || []).forEach(function (c) {
          if (c.key === 'wordpress') return;
          (c.checks || []).forEach(function (chk) {
            if (chk.status === 'fail' || chk.status === 'warn') {
              items.push({
                status: chk.status,
                label: chk.label,
                detail: chk.detail || '',
                why: chk.why || '',
                weight: chk.weight || 0
              });
            }
          });
        });
        items.sort(function (a, b) {
          if (a.status !== b.status) return a.status === 'fail' ? -1 : 1;
          return (b.weight || 0) - (a.weight || 0);
        });
        return items.slice(0, 3);
      }
      function firstBlock(issues) {
        var hasFail = issues.some(function (i) { return i.status === 'fail'; });
        var lead = issues.length === 0
          ? 'This page looks clean on the checks I can run from here.'
          : (hasFail
            ? 'Start here — these are the ones that actually fail or need a look.'
            : 'Nothing is on fire. These are the gaps I would close first.');
        var title = issues.length === 0 ? 'Nothing urgent on this page.' : 'Fix these first.';
        var rows = issues.map(function (chk) { return checkRow(chk, true); }).join('');
        return '<div class="rv-sec-first"><h3>' + title + '</h3>'
          + '<p class="rv-sec-first-lead">' + lead + '</p>'
          + (rows ? '<ul class="rv-crit-list">' + rows + '</ul>' : '')
          + '</div>';
      }
      function nextStepBlock(hasIssues) {
        var title = hasIssues ? 'Want me to close these?' : 'Want it kept that way?';
        var sub = hasIssues
          ? 'I fix the headers and hygiene, then watch the site on a Care & Grow plan. You still own the keys.'
          : 'A clean scan is a start. Updates, backups, and a human watching are what keep it that way.';
        return '<div class="rv-sec-next"><div class="rv-sec-next-copy"><h3>' + title + '</h3><p>' + sub + '</p></div>'
          + '<a class="rv-btn rv-btn-primary" href="' + esc(ctaHref) + '">Secure my site</a></div>';
      }
      function categoryBlock(c) {
        var rows = (c.checks || []).map(function (chk) { return checkRow(chk, false); }).join('');
        var grade = (c.grade === 'N/A' || c.grade === 'Info') ? esc(c.grade) : (c.score + ' · ' + esc(c.grade));
        return '<div class="rv-cat"><div class="rv-cat-head">'
          + '<span class="rv-pour-letter rv-code" aria-hidden="true">' + esc(c.code) + '</span>'
          + '<div><h4 class="rv-cat-name">' + esc(c.name) + '</h4><p class="rv-cat-desc">' + esc(c.desc) + '</p></div>'
          + '<span class="rv-cat-score" data-grade="' + esc(c.grade) + '">' + grade + '</span>'
          + '</div><ul class="rv-crit-list">' + rows + '</ul></div>';
      }
      function overallBlock(d) {
        var m = d.meta || {};
        var scripts = m.scripts || [];
        var scriptLine = scripts.length
          ? scripts.slice(0, 3).map(esc).join(', ') + (scripts.length > 3 ? ' +' + (scripts.length - 3) : '')
          : ((m.thirdparty || 0) + ' third-party scripts');
        return '<div class="rv-grade-overall"><div class="rv-grade-dial rv-score" data-grade="' + esc(d.overall.grade) + '">'
          + '<span class="rv-score-num">' + d.overall.score + '</span><span class="rv-score-grade">' + esc(d.overall.grade) + '</span></div>'
          + '<div class="rv-grade-headline"><h3>Security score</h3>'
          + '<p>Scanned ' + esc(d.url) + '.</p>'
          + '<div class="rv-grade-meta"><span>' + (m.https ? 'HTTPS ✓' : 'No HTTPS') + '</span><span>' + (m.headers || 0) + '/6 security headers</span><span>' + scriptLine + '</span>'
          + (m.wordpress ? '<span>Looks like WordPress</span>' : '')
          + '</div></div></div>';
      }

      if (form) form.addEventListener('submit', function (e) {
        e.preventDefault();
        var u = normalizeUrl(input.value);
        if (!u) return;
        input.value = u;
        if (runBtn) runBtn.disabled = true;
        clearTimers();
        if (out) { out.hidden = true; out.innerHTML = ''; }
        if (status) status.classList.remove('is-done', 'is-error');
        if (fill) fill.style.width = '6%';
        if (live) live.textContent = 'Scanning ' + u + '…';

        fetch(endpoint + '?url=' + encodeURIComponent(u))
          .then(function (r) { return r.json(); })
          .then(function (data) {
            if (!data || !data.ok) {
              if (status) status.classList.add('is-error');
              if (live) live.textContent = (data && data.error) ? data.error : 'Could not scan that URL.';
              if (runBtn) runBtn.disabled = false;
              return;
            }
            var issues = firstIssues(data);
            out.hidden = false;
            out.innerHTML = overallBlock(data) + firstBlock(issues) + nextStepBlock(issues.length > 0);
            try { root.scrollIntoView({ behavior: reduce ? 'auto' : 'smooth', block: 'start' }); } catch (err) {}
            var cats = data.categories || [], total = cats.length, i = 0;
            function revealNext() {
              if (i >= total) {
                if (status) status.classList.add('is-done');
                if (live) live.textContent = 'Security scan ready — score ' + data.overall.grade + ' (' + data.overall.score + '/100).';
                if (count) count.textContent = data.overall.score + ' / 100';
                if (runBtn) runBtn.disabled = false;
                return;
              }
              out.insertAdjacentHTML('beforeend', categoryBlock(cats[i]));
              i++;
              if (fill) fill.style.width = (i / total * 100) + '%';
              if (live) live.textContent = 'Checking ' + cats[i - 1].name + '…';
              if (count) count.textContent = i + ' / ' + total + ' areas';
              if (reduce) { revealNext(); } else { timers.push(setTimeout(revealNext, 260)); }
            }
            revealNext();
          })
          .catch(function () {
            if (status) status.classList.add('is-error');
            if (live) live.textContent = 'Network error — please try again.';
            if (runBtn) runBtn.disabled = false;
          });
      });

      if (resetB) resetB.addEventListener('click', reset);

      function focusIfTargeted() {
        if (window.location.hash !== '#security' || !input) return;
        root.scrollIntoView({ behavior: 'smooth', block: 'start' });
        window.setTimeout(function () { try { input.focus({ preventScroll: true }); } catch (e) { input.focus(); } }, 450);
      }
      focusIfTargeted();
      window.addEventListener('hashchange', focusIfTargeted);
    })();
  </script>
@endsection
