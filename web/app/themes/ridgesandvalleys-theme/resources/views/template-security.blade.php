{{--
  Template Name: Security Checker
--}}
@extends('layouts.app')

@section('content')
  @php($ctaHref = \App\cta_href(get_theme_mod('rv_cta_url', '/contact/')))

  {{-- HERO --}}
  <section class="rv-hero">
    <span class="rv-stripe" aria-hidden="true"></span>
    <div class="rv-shell rv-hero-inner">
      {!! \App\eyebrow(\App\field('hero_eyebrow', __('Free security checker', 'sage'))) !!}
      <h1 class="rv-hero-title">{{ \App\field('hero_title', __('Is your website', 'sage')) }} <em class="rv-accent">{{ \App\field('hero_accent', __('locked up?', 'sage')) }}</em></h1>
      <p class="rv-hero-sub">{{ \App\field('hero_sub', __('A plain-English security check of any page — HTTPS and HSTS, the modern browser security headers, information leaks, cookie safety, and front-end risks. Every result explains the risk and why it matters for your business.', 'sage')) }}</p>
      <div class="rv-hero-actions">
        <a class="rv-btn rv-btn-primary" href="#security">{{ __('Check my site', 'sage') }}</a>
        <a class="rv-btn rv-btn-ghost" href="{{ $ctaHref }}">{{ __('Talk to me', 'sage') }}</a>
      </div>
    </div>
  </section>

  {{-- LIVE SECURITY CHECKER --}}
  <section id="security" class="rv-shell rv-band">
    {!! \App\eyebrow(__('Instant security scan', 'sage')) !!}
    <h2 class="rv-section-title">{{ __('Scan a page for', 'sage') }} <em class="rv-accent">{{ __('safety.', 'sage') }}</em></h2>
    <p class="rv-page-intro">{{ __('Enter a URL for a fast hygiene check of what browsers (and attackers) see in your page and its response headers. Free, no email. It\'s a first-pass check, not a penetration test.', 'sage') }}</p>

    <div class="rv-scan" id="rv-sec" data-endpoint="{{ esc_url(rest_url('rv-tools/v1/security')) }}" style="margin-top:2rem" aria-label="{{ __('Security checker', 'sage') }}">
      <div class="rv-scan-head">
        <div class="rv-scan-head-row">
          <h3 class="rv-scan-title">{{ __('Security report', 'sage') }}</h3>
          <span class="rv-scan-count" id="rv-sec-count"></span>
        </div>
        <form class="rv-audit-form" id="rv-sec-form">
          <label class="screen-reader-text" for="rv-sec-url">{{ __('Website URL to scan', 'sage') }}</label>
          <input type="url" id="rv-sec-url" name="url" placeholder="https://your-site.com" required>
          <button type="submit" class="rv-btn rv-btn-primary" id="rv-sec-run">{{ __('Check security', 'sage') }}</button>
        </form>
        <div class="rv-scan-bar"><span class="rv-scan-bar-fill" id="rv-sec-fill"></span></div>
        <p class="rv-scan-status" id="rv-sec-status" role="status" aria-live="polite">
          <span class="rv-scan-live">{{ __('Enter a URL to run the security scan.', 'sage') }}</span>
        </p>
      </div>
      <div class="rv-scan-body" id="rv-sec-results" hidden></div>
      <div class="rv-scan-actions">
        <button type="button" class="rv-btn rv-btn-ghost" id="rv-sec-reset">{{ __('Reset', 'sage') }}</button>
      </div>
    </div>
    <p class="rv-audit-note">{{ __('This checks configuration and headers, not your passwords, plugins, or server internals. Want a real hardening pass and ongoing monitoring?', 'sage') }} <a href="{{ $ctaHref }}">{{ __('Talk to me', 'sage') }}</a>.</p>
  </section>

  {{-- WHAT WE CHECK --}}
  <section class="rv-band rv-band-alt">
    <div class="rv-shell">
      {!! \App\eyebrow(__('What the scan covers', 'sage')) !!}
      <h2 class="rv-section-title">{{ __('Five layers of', 'sage') }} <em class="rv-accent">{{ __('everyday', 'sage') }}</em> {{ __('protection.', 'sage') }}</h2>
      <div class="rv-grid rv-grid-3" style="margin-top:2rem">
        @php($areas = [
          [__('HTTPS & transport', 'sage'), __('HTTPS, an http→https redirect, HSTS, mixed content, and secure form submissions.', 'sage'), __('This is the encryption between your visitor and your site. Without it, data can be read or altered in transit and browsers show a “Not secure” warning.', 'sage')],
          [__('Security headers', 'sage'), __('Content-Security-Policy, X-Content-Type-Options, clickjacking protection, Referrer-Policy, and Permissions-Policy.', 'sage'), __('These headers are free, built-in browser defences against the most common web attacks — most small-business sites simply never turn them on.', 'sage')],
          [__('Information disclosure', 'sage'), __('Whether the server, framework, and CMS quietly advertise their exact versions.', 'sage'), __('Version numbers are a roadmap for attackers — they tell bots precisely which known exploits are worth trying against you.', 'sage')],
          [__('Cookie safety', 'sage'), __('The Secure, HttpOnly, and SameSite flags on any cookies the page sets.', 'sage'), __('Properly flagged cookies can\'t be stolen by injected scripts or replayed from other sites — critical if anyone ever logs in.', 'sage')],
          [__('Front-end risks', 'sage'), __('Tabnabbing-safe links, inline scripts and handlers, and third-party script sprawl.', 'sage'), __('Every external script is code you don\'t control running on your site. Fewer, safer scripts mean a smaller attack surface.', 'sage')],
          [__('Beyond the scan', 'sage'), __('Strong passwords, two-factor login, updates, backups, a firewall, and malware monitoring.', 'sage'), __('Real security is ongoing, not a one-time score. A Care & Grow plan keeps the site patched, backed up, and watched.', 'sage')],
        ])
        @foreach ($areas as $a)
          <article class="rv-card rv-feature">
            <span class="rv-stripe rv-stripe-thin" aria-hidden="true"></span>
            <h3 class="rv-feature-title">{{ $a[0] }}</h3>
            <p class="rv-feature-text" style="margin-bottom:.6rem">{{ $a[1] }}</p>
            <p class="rv-feature-text" style="color:var(--color-muted);font-size:.9rem"><b style="color:var(--color-body)">{{ __('Why it matters:', 'sage') }}</b> {{ $a[2] }}</p>
          </article>
        @endforeach
      </div>
    </div>
  </section>

  {{-- CTA --}}
  <section class="rv-cta-band">
    <div class="rv-shell rv-cta-inner">
      <h2 class="rv-cta-title">{{ \App\field('cta_title', __('Want your site locked down and kept that way?', 'sage')) }}</h2>
      <p class="rv-cta-sub">{{ \App\field('cta_sub', __('I harden the fixable items above and keep sites patched, backed up, and monitored on a Care & Grow plan.', 'sage')) }}</p>
      <a class="rv-btn rv-btn-on-dark" href="{{ $ctaHref }}">{{ \App\field('cta_button', __('Secure my site', 'sage')) }}</a>
    </div>
  </section>

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
      var LABEL = { pass: 'Pass', warn: 'Review', fail: 'Fail' };
      var timers = [];

      function esc(s) {
        return String(s == null ? '' : s).replace(/[&<>"]/g, function (c) {
          return { '&': '&amp;', '<': '&lt;', '>': '&gt;', '"': '&quot;' }[c];
        });
      }
      function clearTimers() { timers.forEach(clearTimeout); timers = []; }
      function reset() {
        clearTimers();
        if (out) { out.hidden = true; out.innerHTML = ''; }
        if (count) count.textContent = '';
        if (fill) fill.style.width = '0';
        if (status) status.classList.remove('is-done', 'is-error');
        if (live) live.textContent = 'Enter a URL to run the security scan.';
      }
      function checkRow(chk) {
        return '<li class="rv-crit is-' + chk.status + '">'
          + '<span class="rv-crit-tick" aria-hidden="true">'
          + (chk.status === 'pass' ? '<svg viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="3" stroke-linecap="round" stroke-linejoin="round"><path d="M20 6L9 17l-5-5"/></svg>' : '')
          + '</span><span class="rv-crit-main">'
          + '<span class="rv-crit-name">' + esc(chk.label) + (chk.detail ? ' <span class="rv-crit-num">· ' + esc(chk.detail) + '</span>' : '') + '</span>'
          + (chk.why ? '<span class="rv-crit-why"><b>Why:</b> ' + esc(chk.why) + '</span>' : '')
          + '</span><span class="rv-crit-side"><span class="rv-crit-state">' + (LABEL[chk.status] || '') + '</span></span></li>';
      }
      function categoryBlock(c) {
        var rows = (c.checks || []).map(checkRow).join('');
        return '<div class="rv-cat"><div class="rv-cat-head">'
          + '<span class="rv-pour-letter rv-code" aria-hidden="true">' + esc(c.code) + '</span>'
          + '<div><h4 class="rv-cat-name">' + esc(c.name) + '</h4><p class="rv-cat-desc">' + esc(c.desc) + '</p></div>'
          + '<span class="rv-cat-score" data-grade="' + esc(c.grade) + '">' + c.score + ' · ' + esc(c.grade) + '</span>'
          + '</div><ul class="rv-crit-list">' + rows + '</ul></div>';
      }
      function overallBlock(d) {
        var m = d.meta || {};
        return '<div class="rv-grade-overall"><div class="rv-grade-dial rv-score" data-grade="' + esc(d.overall.grade) + '">'
          + '<span class="rv-score-num">' + d.overall.score + '</span><span class="rv-score-grade">' + esc(d.overall.grade) + '</span></div>'
          + '<div class="rv-grade-headline"><h3>Security score</h3>'
          + '<p>Scanned ' + esc(d.url) + '.</p>'
          + '<div class="rv-grade-meta"><span>' + (m.https ? 'HTTPS ✓' : 'No HTTPS') + '</span><span>' + (m.headers || 0) + '/6 security headers</span><span>' + (m.mixed || 0) + ' mixed content</span><span>' + (m.thirdparty || 0) + ' third-party scripts</span></div>'
          + '</div></div>';
      }

      if (form) form.addEventListener('submit', function (e) {
        e.preventDefault();
        var u = input.value.trim();
        if (!u) return;
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
            out.hidden = false;
            out.innerHTML = overallBlock(data);
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
