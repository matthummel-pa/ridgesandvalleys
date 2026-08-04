{{--
  Template Name: Website Grader
--}}
@extends('layouts.app')

@section('content')
  @php($ctaHref = \App\cta_href(get_theme_mod('rv_cta_url', '/contact/')))

  {{-- HERO --}}
  <section class="rv-hero">
    <span class="rv-stripe" aria-hidden="true"></span>
    @include('partials.hero-bg', ['fallback' => ''])
    <div class="rv-shell rv-hero-inner">
      {!! \App\eyebrow(\App\field('hero_eyebrow', __('Free website grader', 'sage'))) !!}
      <h1 class="rv-hero-title">{{ \App\field('hero_title', __('How good is your website,', 'sage')) }} <em class="rv-accent">{{ \App\field('hero_accent', __('really?', 'sage')) }}</em></h1>
      <p class="rv-hero-sub">{{ \App\field('hero_sub', __('Enter your URL for an instant, plain-English report card across seven areas — SEO, speed, mobile, readability, security, technical health, and social sharing. Every check explains what it found and why it matters for your business.', 'sage')) }}</p>
      <div class="rv-hero-actions">
        <a class="rv-btn {{ \App\hero_btn_class(\App\field('hero_btn1_style',''), 'rv-btn-primary') }}" href="#grader">{{ \App\field('hero_btn1', __('Grade my site', 'sage')) }}</a>
        <a class="rv-btn {{ \App\hero_btn_class(\App\field('hero_btn2_style',''), 'rv-btn-ghost') }}" href="{{ $ctaHref }}">{{ \App\field('hero_btn2', __('Talk to me', 'sage')) }}</a>
      </div>
    </div>
  </section>

  {{-- LIVE GRADER --}}
  <section id="grader" class="rv-shell rv-band">
    {!! \App\eyebrow(__('Instant report card', 'sage')) !!}
    <h2 class="rv-section-title">{{ __('Grade any', 'sage') }} <em class="rv-accent">{{ __('website.', 'sage') }}</em></h2>
    <p class="rv-page-intro">{{ __('A fast, honest read of the signals in your page and its server responses — free, no email, no signup. It complements a hands-on audit rather than replacing it.', 'sage') }}</p>

    <div class="rv-scan" id="rv-grader" data-endpoint="{{ esc_url(rest_url('rv-tools/v1/audit')) }}" style="margin-top:2rem" aria-label="{{ __('Website grader', 'sage') }}">
      <div class="rv-scan-head">
        <div class="rv-scan-head-row">
          <h3 class="rv-scan-title">{{ __('Website report card', 'sage') }}</h3>
          <span class="rv-scan-count" id="rv-grade-count"></span>
        </div>
        <form class="rv-audit-form" id="rv-grade-form">
          <label class="screen-reader-text" for="rv-grade-url">{{ __('Website URL to grade', 'sage') }}</label>
          <input type="url" id="rv-grade-url" name="url" placeholder="https://your-site.com" required>
          <button type="submit" class="rv-btn rv-btn-primary" id="rv-grade-run">{{ __('Grade my site', 'sage') }}</button>
        </form>
        <div class="rv-scan-bar"><span class="rv-scan-bar-fill" id="rv-grade-fill"></span></div>
        <p class="rv-scan-status" id="rv-grade-status" role="status" aria-live="polite">
          <span class="rv-scan-live">{{ __('Enter a URL to grade it across seven areas.', 'sage') }}</span>
        </p>
      </div>
      <div class="rv-scan-body" id="rv-grade-results" hidden></div>
      <div class="rv-scan-actions">
        <button type="button" class="rv-btn rv-btn-ghost" id="rv-grade-reset">{{ __('Reset', 'sage') }}</button>
      </div>
    </div>
    <p class="rv-audit-note">{{ __('An automated read can\'t catch everything a person can. Want a full audit with the fixes done for you?', 'sage') }} <a href="{{ $ctaHref }}">{{ __('Talk to me', 'sage') }}</a>.</p>
  </section>

  {{-- WHAT WE GRADE & WHY --}}
  <section class="rv-band rv-band-alt">
    <div class="rv-shell">
      {!! \App\eyebrow(__('What the grade covers', 'sage')) !!}
      <h2 class="rv-section-title">{{ __('Seven areas, and why', 'sage') }} <em class="rv-accent">{{ __('each one', 'sage') }}</em> {{ __('matters.', 'sage') }}</h2>
      <div class="rv-grid rv-grid-3" style="margin-top:2rem">
        @php($areas = [
          [__('SEO — search visibility', 'sage'), __('Titles, meta descriptions, headings, alt text, canonical tags, schema, and whether the page is even indexable.', 'sage'), __('This is how customers find you on Google without paying for ads. Small on-page fixes are often the highest-return work a local site can get.', 'sage')],
          [__('Page speed', 'sage'), __('Server response time, page weight, compression, caching, request count, and image sizing.', 'sage'), __('Slow pages lose visitors before they ever see your offer, and speed is a direct Google ranking factor — especially on mobile.', 'sage')],
          [__('Mobile & responsive', 'sage'), __('Viewport, pinch-zoom, responsive images, and whether the layout fits a phone screen.', 'sage'), __('Most local searches happen on a phone. A site that fights the phone is a site that sends customers to the next result.', 'sage')],
          [__('Readability & content', 'sage'), __('How much real content there is, how clearly it reads, link text quality, and content-to-code ratio.', 'sage'), __('Thin or dense pages don\'t rank and don\'t convert. Clear, useful writing is what earns trust and answers a buyer\'s question.', 'sage')],
          [__('Security & trust', 'sage'), __('HTTPS, mixed content, HSTS, and the security headers browsers look for.', 'sage'), __('Browsers now label unsafe sites “Not secure.” Trust signals protect both your customers and your reputation.', 'sage')],
          [__('Technical foundation', 'sage'), __('Doctype, character encoding, language, favicon, and a healthy HTTP response.', 'sage'), __('The quiet fundamentals that keep a site rendering correctly across every browser and device — the things that break silently.', 'sage')],
          [__('Social & sharing', 'sage'), __('Open Graph tags, a share image, Twitter/X cards, and touch icons.', 'sage'), __('When someone shares your link, these decide whether it looks like a polished preview or a broken, ignorable text link.', 'sage')],
          [__('Accessibility', 'sage'), __('Covered in depth on its own page — alt text, labels, contrast, keyboard use, and the full WCAG 2.1 AA standard.', 'sage'), __('An accessible site reaches more customers and reduces legal risk. It overlaps heavily with SEO and good UX.', 'sage')],
          [__('And the human layer', 'sage'), __('Brand fit, message clarity, conversion path, trust, and the dozens of judgment calls no scanner can make.', 'sage'), __('A perfect technical score still loses if the site doesn\'t say the right thing to the right person. That\'s the part I finish by hand.', 'sage')],
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

  {{-- HOW TO READ IT --}}
  <section class="rv-shell rv-band">
    {!! \App\eyebrow(__('Reading your report', 'sage')) !!}
    <h2 class="rv-section-title">{{ __('A score is a', 'sage') }} <em class="rv-accent">{{ __('starting point,', 'sage') }}</em> {{ __('not a verdict.', 'sage') }}</h2>
    <div class="rv-grid rv-grid-3" style="margin-top:2rem">
      <article class="rv-card rv-feature"><span class="rv-stripe rv-stripe-thin" aria-hidden="true"></span><h3 class="rv-feature-title">{{ __('Green, amber, red', 'sage') }}</h3><p class="rv-feature-text">{{ __('Each check passes, needs a look, or fails — with the exact value found (your title length, load time, image count) so nothing is a mystery.', 'sage') }}</p></article>
      <article class="rv-card rv-feature"><span class="rv-stripe rv-stripe-thin" aria-hidden="true"></span><h3 class="rv-feature-title">{{ __('Why, in plain English', 'sage') }}</h3><p class="rv-feature-text">{{ __('Every item explains what it means for your business — not jargon, but the cost or opportunity behind the check.', 'sage') }}</p></article>
      <article class="rv-card rv-feature"><span class="rv-stripe rv-stripe-thin" aria-hidden="true"></span><h3 class="rv-feature-title">{{ __('Automated ≠ complete', 'sage') }}</h3><p class="rv-feature-text">{{ __('This reads signals in your code and server responses. It won\'t judge your design, copy, or strategy — that\'s where a person comes in.', 'sage') }}</p></article>
    </div>
  </section>

  {{-- CTA --}}
  <section class="rv-cta-band">
    <div class="rv-shell rv-cta-inner">
      <h2 class="rv-cta-title">{{ \App\field('cta_title', __('Want the fixes, not just the grade?', 'sage')) }}</h2>
      <p class="rv-cta-sub">{{ \App\field('cta_sub', __('Send me your URL and I\'ll turn the report into a short, prioritized plan — and I can do the work for you.', 'sage')) }}</p>
      <a class="rv-btn rv-btn-on-dark" href="{{ $ctaHref }}">{{ \App\field('cta_button', __('Get my plan', 'sage')) }}</a>
    </div>
  </section>

  <script>
    (function () {
      var root = document.getElementById('rv-grader');
      if (!root) return;
      var form   = document.getElementById('rv-grade-form');
      var input  = document.getElementById('rv-grade-url');
      var runBtn = document.getElementById('rv-grade-run');
      var resetB = document.getElementById('rv-grade-reset');
      var count  = document.getElementById('rv-grade-count');
      var fill   = document.getElementById('rv-grade-fill');
      var status = document.getElementById('rv-grade-status');
      var live   = status ? status.querySelector('.rv-scan-live') : null;
      var out    = document.getElementById('rv-grade-results');
      var endpoint = root.getAttribute('data-endpoint');
      var reduce = window.matchMedia('(prefers-reduced-motion: reduce)').matches;
      var ICON = { pass: '✓', warn: '!', fail: '✗' };
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
        if (live) live.textContent = 'Enter a URL to grade it across seven areas.';
      }
      function checkRow(chk) {
        return '<li class="rv-crit is-' + chk.status + '">'
          + '<span class="rv-crit-tick" aria-hidden="true">'
          + (chk.status === 'pass'
              ? '<svg viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="3" stroke-linecap="round" stroke-linejoin="round"><path d="M20 6L9 17l-5-5"/></svg>'
              : '')
          + '</span>'
          + '<span class="rv-crit-main">'
          + '<span class="rv-crit-name">' + esc(chk.label) + (chk.detail ? ' <span class="rv-crit-num">· ' + esc(chk.detail) + '</span>' : '') + '</span>'
          + (chk.why ? '<span class="rv-crit-why"><b>Why:</b> ' + esc(chk.why) + '</span>' : '')
          + '</span>'
          + '<span class="rv-crit-side"><span class="rv-crit-state">' + (LABEL[chk.status] || '') + '</span></span>'
          + '</li>';
      }
      function categoryBlock(c) {
        var rows = (c.checks || []).map(checkRow).join('');
        return '<div class="rv-cat">'
          + '<div class="rv-cat-head">'
          + '<span class="rv-pour-letter rv-code" aria-hidden="true">' + esc(c.code) + '</span>'
          + '<div><h4 class="rv-cat-name">' + esc(c.name) + '</h4><p class="rv-cat-desc">' + esc(c.desc) + '</p></div>'
          + '<span class="rv-cat-score" data-grade="' + esc(c.grade) + '">' + c.score + ' · ' + esc(c.grade) + '</span>'
          + '</div>'
          + '<ul class="rv-crit-list">' + rows + '</ul>'
          + '</div>';
      }
      function overallBlock(data) {
        var m = data.meta || {};
        return '<div class="rv-grade-overall">'
          + '<div class="rv-grade-dial rv-score" data-grade="' + esc(data.overall.grade) + '">'
          + '<span class="rv-score-num">' + data.overall.score + '</span>'
          + '<span class="rv-score-grade">' + esc(data.overall.grade) + '</span></div>'
          + '<div class="rv-grade-headline"><h3>Overall grade</h3>'
          + '<p>Graded ' + esc(data.url) + ' across ' + (data.categories || []).length + ' areas.</p>'
          + '<div class="rv-grade-meta"><span>' + (m.ms || 0) + ' ms</span><span>' + (m.kb || 0) + ' KB</span><span>~' + (m.requests || 0) + ' requests</span><span>' + (m.words || 0) + ' words</span><span>HTTP ' + (m.status || 0) + '</span></div>'
          + '</div></div>';
      }

      if (form) form.addEventListener('submit', function (e) {
        e.preventDefault();
        var url = input.value.trim();
        if (!url) return;
        if (runBtn) runBtn.disabled = true;
        clearTimers();
        if (out) { out.hidden = true; out.innerHTML = ''; }
        if (status) status.classList.remove('is-done', 'is-error');
        if (fill) fill.style.width = '6%';
        if (live) live.textContent = 'Fetching and grading ' + url + '…';

        fetch(endpoint + '?url=' + encodeURIComponent(url))
          .then(function (r) { return r.json(); })
          .then(function (data) {
            if (!data || !data.ok) {
              if (status) status.classList.add('is-error');
              if (live) live.textContent = (data && data.error) ? data.error : 'Could not grade that URL.';
              if (runBtn) runBtn.disabled = false;
              return;
            }
            out.hidden = false;
            out.innerHTML = overallBlock(data);
            var cats = data.categories || [];
            var total = cats.length;
            var i = 0;
            function revealNext() {
              if (i >= total) {
                if (status) status.classList.add('is-done');
                if (live) live.textContent = 'Report ready — overall grade ' + data.overall.grade + ' (' + data.overall.score + '/100).';
                if (count) count.textContent = data.overall.score + ' / 100';
                if (runBtn) runBtn.disabled = false;
                return;
              }
              out.insertAdjacentHTML('beforeend', categoryBlock(cats[i]));
              i++;
              if (fill) fill.style.width = (i / total * 100) + '%';
              if (live) live.textContent = 'Scoring ' + cats[i - 1].name + '…';
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
        if (window.location.hash !== '#grader' || !input) return;
        root.scrollIntoView({ behavior: 'smooth', block: 'start' });
        window.setTimeout(function () {
          try { input.focus({ preventScroll: true }); } catch (e) { input.focus(); }
        }, 450);
      }
      focusIfTargeted();
      window.addEventListener('hashchange', focusIfTargeted);
    })();
  </script>
@endsection
