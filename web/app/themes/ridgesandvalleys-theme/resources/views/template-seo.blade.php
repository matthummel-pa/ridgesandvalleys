{{--
  Template Name: SEO Checker
--}}
@extends('layouts.app')

@section('content')
  @php($ctaHref = \App\cta_href(get_theme_mod('rv_cta_url', '/contact/')))

  {{-- HERO --}}
  <section class="rv-hero">
    <span class="rv-stripe" aria-hidden="true"></span>
    <div class="rv-shell rv-hero-inner">
      {!! \App\eyebrow(\App\field('hero_eyebrow', __('Free SEO checker', 'sage'))) !!}
      <h1 class="rv-hero-title">{{ \App\field('hero_title', __('Can Google actually', 'sage')) }} <em class="rv-accent">{{ \App\field('hero_accent', __('find you?', 'sage')) }}</em></h1>
      <p class="rv-hero-sub">{{ \App\field('hero_sub', __('A deep, plain-English SEO audit of any page — snippet preview, crawlability, keyword usage, structured data, links, and the technical fundamentals. Add a target keyword to see how well the page is built around it.', 'sage')) }}</p>
      <div class="rv-hero-actions">
        <a class="rv-btn rv-btn-primary" href="#seo">{{ __('Check my SEO', 'sage') }}</a>
        <a class="rv-btn rv-btn-ghost" href="{{ $ctaHref }}">{{ __('Talk to me', 'sage') }}</a>
      </div>
    </div>
  </section>

  {{-- LIVE SEO CHECKER --}}
  <section id="seo" class="rv-shell rv-band">
    {!! \App\eyebrow(__('Instant SEO audit', 'sage')) !!}
    <h2 class="rv-section-title">{{ __('Grade a page for', 'sage') }} <em class="rv-accent">{{ __('search.', 'sage') }}</em></h2>
    <p class="rv-page-intro">{{ __('Enter a URL — and, optionally, the phrase you want to rank for. Free, no email. This reads the page and its robots.txt to check what search engines see.', 'sage') }}</p>

    <div class="rv-scan" id="rv-seo" data-endpoint="{{ esc_url(rest_url('rv-tools/v1/seo')) }}" style="margin-top:2rem" aria-label="{{ __('SEO checker', 'sage') }}">
      <div class="rv-scan-head">
        <div class="rv-scan-head-row">
          <h3 class="rv-scan-title">{{ __('SEO report', 'sage') }}</h3>
          <span class="rv-scan-count" id="rv-seo-count"></span>
        </div>
        <form class="rv-audit-form" id="rv-seo-form">
          <label class="screen-reader-text" for="rv-seo-url">{{ __('Website URL', 'sage') }}</label>
          <input type="url" id="rv-seo-url" name="url" placeholder="https://your-site.com" required>
          <label class="screen-reader-text" for="rv-seo-kw">{{ __('Target keyword (optional)', 'sage') }}</label>
          <input type="text" id="rv-seo-kw" name="keyword" class="rv-kw" placeholder="{{ __('target keyword (optional)', 'sage') }}">
          <button type="submit" class="rv-btn rv-btn-primary" id="rv-seo-run">{{ __('Check SEO', 'sage') }}</button>
        </form>
        <div class="rv-scan-bar"><span class="rv-scan-bar-fill" id="rv-seo-fill"></span></div>
        <p class="rv-scan-status" id="rv-seo-status" role="status" aria-live="polite">
          <span class="rv-scan-live">{{ __('Enter a URL to run the SEO audit.', 'sage') }}</span>
        </p>
      </div>
      <div class="rv-scan-body" id="rv-seo-results" hidden></div>
      <div class="rv-scan-actions">
        <button type="button" class="rv-btn rv-btn-ghost" id="rv-seo-reset">{{ __('Reset', 'sage') }}</button>
      </div>
    </div>
    <p class="rv-audit-note">{{ __('This audits on-page and technical SEO. It can\'t see your backlinks or Google rankings — want a full picture and a plan?', 'sage') }} <a href="{{ $ctaHref }}">{{ __('Talk to me', 'sage') }}</a>.</p>
  </section>

  {{-- WHAT WE CHECK --}}
  <section class="rv-band rv-band-alt">
    <div class="rv-shell">
      {!! \App\eyebrow(__('What the audit covers', 'sage')) !!}
      <h2 class="rv-section-title">{{ __('The SEO that a', 'sage') }} <em class="rv-accent">{{ __('local business', 'sage') }}</em> {{ __('can control.', 'sage') }}</h2>
      <div class="rv-grid rv-grid-3" style="margin-top:2rem">
        @php($areas = [
          [__('Search snippet & meta', 'sage'), __('Title tag, meta description, lengths, and canonical URL.', 'sage'), __('This is your listing in Google — the headline and summary that decide whether someone clicks you or the shop next door.', 'sage')],
          [__('Indexability & crawlability', 'sage'), __('noindex/nofollow, robots.txt, XML sitemap, clean URLs, and HTTPS.', 'sage'), __('If Google can\'t crawl or is told not to index a page, nothing else matters — it simply won\'t show up.', 'sage')],
          [__('Content & keywords', 'sage'), __('Content depth, heading structure, alt text — and, with a keyword, its use in the title, H1, intro, URL, and density.', 'sage'), __('Search is about matching intent. Pages built clearly around a real phrase rank for it; vague pages rank for nothing.', 'sage')],
          [__('Structured data & social', 'sage'), __('JSON-LD schema, breadcrumbs, Open Graph, and Twitter cards.', 'sage'), __('Schema can win rich results (ratings, hours, FAQs) and social tags make shared links look clickable instead of broken.', 'sage')],
          [__('Link profile', 'sage'), __('Internal links, outbound links, and anchor-text quality.', 'sage'), __('Internal links pass ranking strength to your key pages and help visitors (and Google) navigate your site.', 'sage')],
          [__('Technical SEO', 'sage'), __('Mobile viewport, response speed, page weight, language, and encoding.', 'sage'), __('Google indexes the mobile version first and rewards fast pages. The fundamentals quietly gate everything above.', 'sage')],
          [__('Local SEO (beyond the page)', 'sage'), __('Google Business Profile, consistent name/address/phone, reviews, and town-specific pages.', 'sage'), __('For a local business this is where much of the real traffic comes from — I set it up as part of every build.', 'sage')],
          [__('Off-page (not shown here)', 'sage'), __('Backlinks, domain authority, and how others link to you.', 'sage'), __('This audit reads your page; it can\'t see the wider web. Off-page strength is the other half of ranking — ask me about it.', 'sage')],
          [__('The strategy layer', 'sage'), __('Which keywords are worth chasing, search intent, and content that answers real questions.', 'sage'), __('A technically perfect page targeting the wrong phrase still loses. Picking the right battles is the human part.', 'sage')],
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
      <h2 class="rv-cta-title">{{ \App\field('cta_title', __('Want to actually rank, not just score well?', 'sage')) }}</h2>
      <p class="rv-cta-sub">{{ \App\field('cta_sub', __('Send me your URL and the phrase you want to be found for. I\'ll turn this into a prioritized plan — and do the work.', 'sage')) }}</p>
      <a class="rv-btn rv-btn-on-dark" href="{{ $ctaHref }}">{{ \App\field('cta_button', __('Get my SEO plan', 'sage')) }}</a>
    </div>
  </section>

  <script>
    (function () {
      var root = document.getElementById('rv-seo');
      if (!root) return;
      var form   = document.getElementById('rv-seo-form');
      var url    = document.getElementById('rv-seo-url');
      var kw     = document.getElementById('rv-seo-kw');
      var runBtn = document.getElementById('rv-seo-run');
      var resetB = document.getElementById('rv-seo-reset');
      var count  = document.getElementById('rv-seo-count');
      var fill   = document.getElementById('rv-seo-fill');
      var status = document.getElementById('rv-seo-status');
      var live   = status ? status.querySelector('.rv-scan-live') : null;
      var out    = document.getElementById('rv-seo-results');
      var endpoint = root.getAttribute('data-endpoint');
      var reduce = window.matchMedia('(prefers-reduced-motion: reduce)').matches;
      var LABEL = { pass: 'Pass', warn: 'Review', fail: 'Fail' };
      var timers = [];

      function esc(s) {
        return String(s == null ? '' : s).replace(/[&<>"]/g, function (c) {
          return { '&': '&amp;', '<': '&lt;', '>': '&gt;', '"': '&quot;' }[c];
        });
      }
      function clip(s, n) { s = String(s || ''); return s.length > n ? esc(s.slice(0, n)) + '<span class="rv-serp-cut">…</span>' : esc(s); }
      function clearTimers() { timers.forEach(clearTimeout); timers = []; }
      function reset() {
        clearTimers();
        if (out) { out.hidden = true; out.innerHTML = ''; }
        if (count) count.textContent = '';
        if (fill) fill.style.width = '0';
        if (status) status.classList.remove('is-done', 'is-error');
        if (live) live.textContent = 'Enter a URL to run the SEO audit.';
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
      function serpBlock(p) {
        return '<div style="padding:1.6rem 1.6rem 0"><p class="rv-serp-label">How your result looks in Google</p>'
          + '<div class="rv-serp"><div class="rv-serp-url">' + esc(p.url) + '</div>'
          + '<div class="rv-serp-title">' + clip(p.title, 60) + '</div>'
          + '<p class="rv-serp-desc">' + clip(p.description, 160) + '</p></div>'
          + (p.titleCut || p.descCut ? '<p class="rv-audit-note">' + (p.titleCut ? 'Your title is longer than Google shows and will be cut off. ' : '') + (p.descCut ? 'Your description will be truncated.' : '') + '</p>' : '')
          + '</div>';
      }
      function overallBlock(d) {
        var m = d.meta || {};
        return '<div class="rv-grade-overall"><div class="rv-grade-dial rv-score" data-grade="' + esc(d.overall.grade) + '">'
          + '<span class="rv-score-num">' + d.overall.score + '</span><span class="rv-score-grade">' + esc(d.overall.grade) + '</span></div>'
          + '<div class="rv-grade-headline"><h3>SEO score</h3>'
          + '<p>Audited ' + esc(d.url) + (d.keyword ? ' for “' + esc(d.keyword) + '”' : '') + '.</p>'
          + '<div class="rv-grade-meta"><span>' + (m.words || 0) + ' words</span><span>' + (m.internal || 0) + ' internal links</span><span>' + (m.external || 0) + ' external</span><span>' + (m.ms || 0) + ' ms</span><span>' + (m.kb || 0) + ' KB</span></div>'
          + '</div></div>';
      }

      if (form) form.addEventListener('submit', function (e) {
        e.preventDefault();
        var u = url.value.trim();
        if (!u) return;
        if (runBtn) runBtn.disabled = true;
        clearTimers();
        if (out) { out.hidden = true; out.innerHTML = ''; }
        if (status) status.classList.remove('is-done', 'is-error');
        if (fill) fill.style.width = '6%';
        if (live) live.textContent = 'Fetching and auditing ' + u + '…';

        var q = endpoint + '?url=' + encodeURIComponent(u) + (kw.value.trim() ? '&keyword=' + encodeURIComponent(kw.value.trim()) : '');
        fetch(q).then(function (r) { return r.json(); }).then(function (data) {
          if (!data || !data.ok) {
            if (status) status.classList.add('is-error');
            if (live) live.textContent = (data && data.error) ? data.error : 'Could not audit that URL.';
            if (runBtn) runBtn.disabled = false;
            return;
          }
          out.hidden = false;
          out.innerHTML = (data.preview ? serpBlock(data.preview) : '') + overallBlock(data);
          var cats = data.categories || [], total = cats.length, i = 0;
          function revealNext() {
            if (i >= total) {
              if (status) status.classList.add('is-done');
              if (live) live.textContent = 'SEO audit ready — score ' + data.overall.grade + ' (' + data.overall.score + '/100).';
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
        }).catch(function () {
          if (status) status.classList.add('is-error');
          if (live) live.textContent = 'Network error — please try again.';
          if (runBtn) runBtn.disabled = false;
        });
      });

      if (resetB) resetB.addEventListener('click', reset);

      function focusIfTargeted() {
        if (window.location.hash !== '#seo' || !url) return;
        root.scrollIntoView({ behavior: 'smooth', block: 'start' });
        window.setTimeout(function () { try { url.focus({ preventScroll: true }); } catch (e) { url.focus(); } }, 450);
      }
      focusIfTargeted();
      window.addEventListener('hashchange', focusIfTargeted);
    })();
  </script>
@endsection
