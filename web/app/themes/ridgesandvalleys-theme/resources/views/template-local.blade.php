{{--
  Template Name: Local SEO Scorecard
--}}
@extends('layouts.app')

@section('content')
  @php($ctaHref = \App\cta_href(get_theme_mod('rv_cta_url', '/contact/')))

  {{-- HERO --}}
  <section class="rv-hero">
    <span class="rv-stripe" aria-hidden="true"></span>
    @include('partials.hero-bg', ['fallback' => ''])
    <div class="rv-shell rv-hero-inner">
      {!! \App\eyebrow(\App\field('hero_eyebrow', __('Free local SEO scorecard', 'sage'))) !!}
      <h1 class="rv-hero-title">{{ \App\field('hero_title', __('Do nearby customers', 'sage')) }} <em class="rv-accent">{{ \App\field('hero_accent', __('find you?', 'sage')) }}</em></h1>
      <p class="rv-hero-sub">{{ \App\field('hero_sub', __('A scorecard for the signals that get a local business into Google\'s map pack and turn searchers into calls — your name, address, phone, hours, LocalBusiness schema, maps, and reviews. Built for Gettysburg and South Central PA businesses.', 'sage')) }}</p>
      <div class="rv-hero-actions">
        <a class="rv-btn {{ \App\hero_btn_class(\App\field('hero_btn1_style',''), 'rv-btn-primary') }}" href="#local">{{ \App\field('hero_btn1', __('Score my page', 'sage')) }}</a>
        <a class="rv-btn {{ \App\hero_btn_class(\App\field('hero_btn2_style',''), 'rv-btn-ghost') }}" href="{{ $ctaHref }}">{{ \App\field('hero_btn2', __('Talk to me', 'sage')) }}</a>
      </div>
    </div>
  </section>

  {{-- LIVE SCORECARD --}}
  <section id="local" class="rv-shell rv-band">
    {!! \App\eyebrow(\App\field('scan_eyebrow', __('Instant local audit', 'sage'))) !!}
    <h2 class="rv-section-title">{{ \App\field('scan_title', __('Score your page for', 'sage')) }} <em class="rv-accent">{{ \App\field('scan_accent', __('local search.', 'sage')) }}</em></h2>
    <p class="rv-page-intro">{{ \App\field('scan_intro', __('Enter your homepage or contact page URL. This reads the local signals on the page — it can\'t see your Google Business Profile or review counts, which are named below.', 'sage')) }}</p>

    <div class="rv-scan" id="rv-local" data-endpoint="{{ esc_url(rest_url('rv-tools/v1/local')) }}" style="margin-top:2rem" aria-label="{{ __('Local SEO scorecard', 'sage') }}">
      <div class="rv-scan-head">
        <div class="rv-scan-head-row">
          <h3 class="rv-scan-title">{{ __('Local SEO scorecard', 'sage') }}</h3>
          <span class="rv-scan-count" id="rv-local-count"></span>
        </div>
        <form class="rv-audit-form" id="rv-local-form">
          <label class="screen-reader-text" for="rv-local-url">{{ __('Website URL to score', 'sage') }}</label>
          <input type="url" id="rv-local-url" name="url" placeholder="https://your-site.com" required>
          <button type="submit" class="rv-btn rv-btn-primary" id="rv-local-run">{{ __('Score my page', 'sage') }}</button>
        </form>
        <div class="rv-scan-bar"><span class="rv-scan-bar-fill" id="rv-local-fill"></span></div>
        <p class="rv-scan-status" id="rv-local-status" role="status" aria-live="polite">
          <span class="rv-scan-live">{{ __('Enter a URL to score it for local search.', 'sage') }}</span>
        </p>
      </div>
      <div class="rv-scan-body" id="rv-local-results" hidden></div>
      <div class="rv-scan-actions">
        <button type="button" class="rv-btn rv-btn-ghost" id="rv-local-reset">{{ __('Reset', 'sage') }}</button>
      </div>
    </div>
    <p class="rv-audit-note">{{ \App\field('scan_note', __('Local ranking also depends on your Google Business Profile, reviews, and citations across the web — the parts a page scan can\'t see. Want the full local-SEO setup?', 'sage')) }} <a href="{{ $ctaHref }}">{{ \App\field('scan_note_link', __('Talk to me', 'sage')) }}</a>.</p>
  </section>

  {{-- WHAT WE CHECK --}}
  <section class="rv-band rv-band-alt">
    <div class="rv-shell">
      {!! \App\eyebrow(\App\field('areas_eyebrow', __('What the scorecard covers', 'sage'))) !!}
      <h2 class="rv-section-title">{{ \App\field('areas_title', __('How local customers', 'sage')) }} <em class="rv-accent">{{ \App\field('areas_accent', __('actually', 'sage')) }}</em> {{ \App\field('areas_title_end', __('reach you.', 'sage')) }}</h2>
      <div class="rv-grid rv-grid-3" style="margin-top:2rem">
        @foreach (\App\field_rows('areas_items', [
          ['title' => __('Contact essentials (NAP)', 'sage'), 'text' => __('A tap-to-call phone number, a visible address, and your business hours.', 'sage'), 'why' => __('Nearby, ready-to-buy customers want to call or visit now. Missing hours or a non-clickable number sends them straight to a competitor.', 'sage')],
          ['title' => __('LocalBusiness structured data', 'sage'), 'text' => __('Machine-readable schema with your address, phone, hours, and map coordinates.', 'sage'), 'why' => __('This is how you hand Google your business details in a format it trusts — the backbone of appearing in the map pack and knowledge panel.', 'sage')],
          ['title' => __('On the map & getting directions', 'sage'), 'text' => __('Your town in the page title, an embedded map, a Google Business Profile link, and one-tap directions.', 'sage'), 'why' => __('These connect your website to your physical location and your Google listing, and remove friction for someone deciding to come in.', 'sage')],
          ['title' => __('Reviews & trust', 'sage'), 'text' => __('Review/rating schema and links to your Facebook, Yelp, or Instagram.', 'sage'), 'why' => __('Star ratings in search results and active profiles are often what tips a searcher toward you over the shop next door.', 'sage')],
          ['title' => __('Mobile-first', 'sage'), 'text' => __('A fast, secure, responsive page — because local search is overwhelmingly on phones.', 'sage'), 'why' => __('“Near me” searches happen in the moment, on a phone, on the go. If the page fights the phone, you lose the customer.', 'sage')],
          ['title' => __('Beyond the page', 'sage'), 'text' => __('A claimed Google Business Profile, a steady flow of reviews, and consistent listings across the web.', 'sage'), 'why' => __('Much of local ranking lives off your website. A page scan can\'t see it — but it\'s where a lot of the real work (and traffic) is, and it\'s part of every local build I do.', 'sage')],
        ]) as $a)
          <article class="rv-card rv-feature">
            <span class="rv-stripe rv-stripe-thin" aria-hidden="true"></span>
            <h3 class="rv-feature-title">{{ $a['title'] ?? '' }}</h3>
            <p class="rv-feature-text" style="margin-bottom:.6rem">{{ $a['text'] ?? '' }}</p>
            <p class="rv-feature-text" style="color:var(--color-muted);font-size:.9rem"><b style="color:var(--color-body)">{{ __('Why it matters:', 'sage') }}</b> {{ $a['why'] ?? '' }}</p>
          </article>
        @endforeach
      </div>
    </div>
  </section>

  {{-- CTA --}}
  <section class="rv-cta-band">
    <div class="rv-shell rv-cta-inner">
      <h2 class="rv-cta-title">{{ \App\field('cta_title', __('Want to own “near me” in Adams County?', 'sage')) }}</h2>
      <p class="rv-cta-sub">{{ \App\field('cta_sub', __('I set up local SEO end to end — on-page signals, Google Business Profile, and town-by-town pages — for Gettysburg and South Central PA businesses.', 'sage')) }}</p>
      <a class="rv-btn rv-btn-on-dark" href="{{ $ctaHref }}">{{ \App\field('cta_button', __('Get found locally', 'sage')) }}</a>
    </div>
  </section>

  <script>
    (function () {
      var root = document.getElementById('rv-local');
      if (!root) return;
      var form   = document.getElementById('rv-local-form');
      var input  = document.getElementById('rv-local-url');
      var runBtn = document.getElementById('rv-local-run');
      var resetB = document.getElementById('rv-local-reset');
      var count  = document.getElementById('rv-local-count');
      var fill   = document.getElementById('rv-local-fill');
      var status = document.getElementById('rv-local-status');
      var live   = status ? status.querySelector('.rv-scan-live') : null;
      var out    = document.getElementById('rv-local-results');
      var endpoint = root.getAttribute('data-endpoint');
      var reduce = window.matchMedia('(prefers-reduced-motion: reduce)').matches;
      var LABEL = { pass: 'Pass', warn: 'Review', fail: 'Fail' };
      var timers = [];

      function esc(s) { return String(s == null ? '' : s).replace(/[&<>"]/g, function (c) { return { '&': '&amp;', '<': '&lt;', '>': '&gt;', '"': '&quot;' }[c]; }); }
      function clearTimers() { timers.forEach(clearTimeout); timers = []; }
      function reset() {
        clearTimers();
        if (out) { out.hidden = true; out.innerHTML = ''; }
        if (count) count.textContent = '';
        if (fill) fill.style.width = '0';
        if (status) status.classList.remove('is-done', 'is-error');
        if (live) live.textContent = 'Enter a URL to score it for local search.';
      }
      function checkRow(chk) {
        return '<li class="rv-crit is-' + chk.status + '"><span class="rv-crit-tick" aria-hidden="true">'
          + (chk.status === 'pass' ? '<svg viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="3" stroke-linecap="round" stroke-linejoin="round"><path d="M20 6L9 17l-5-5"/></svg>' : '')
          + '</span><span class="rv-crit-main"><span class="rv-crit-name">' + esc(chk.label) + (chk.detail ? ' <span class="rv-crit-num">· ' + esc(chk.detail) + '</span>' : '') + '</span>'
          + (chk.why ? '<span class="rv-crit-why"><b>Why:</b> ' + esc(chk.why) + '</span>' : '')
          + '</span><span class="rv-crit-side"><span class="rv-crit-state">' + (LABEL[chk.status] || '') + '</span></span></li>';
      }
      function categoryBlock(c) {
        var rows = (c.checks || []).map(checkRow).join('');
        return '<div class="rv-cat"><div class="rv-cat-head"><span class="rv-pour-letter rv-code" aria-hidden="true">' + esc(c.code) + '</span>'
          + '<div><h4 class="rv-cat-name">' + esc(c.name) + '</h4><p class="rv-cat-desc">' + esc(c.desc) + '</p></div>'
          + '<span class="rv-cat-score" data-grade="' + esc(c.grade) + '">' + c.score + ' · ' + esc(c.grade) + '</span></div>'
          + '<ul class="rv-crit-list">' + rows + '</ul></div>';
      }
      function overallBlock(d) {
        var m = d.meta || {};
        return '<div class="rv-grade-overall"><div class="rv-grade-dial rv-score" data-grade="' + esc(d.overall.grade) + '"><span class="rv-score-num">' + d.overall.score + '</span><span class="rv-score-grade">' + esc(d.overall.grade) + '</span></div>'
          + '<div class="rv-grade-headline"><h3>Local SEO score</h3><p>Scored ' + esc(d.url) + (m.locality ? ' · ' + esc(m.locality) : '') + '.</p>'
          + '<div class="rv-grade-meta"><span>LocalBusiness schema ' + (m.schema ? '✓' : '✗') + '</span><span>' + (m.tel || 0) + ' tap-to-call</span><span>reviews ' + (m.reviews ? '✓' : '—') + '</span></div></div></div>';
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
        if (live) live.textContent = 'Scoring ' + u + '…';

        fetch(endpoint + '?url=' + encodeURIComponent(u)).then(function (r) { return r.json(); }).then(function (data) {
          if (!data || !data.ok) {
            if (status) status.classList.add('is-error');
            if (live) live.textContent = (data && data.error) ? data.error : 'Could not score that URL.';
            if (runBtn) runBtn.disabled = false;
            return;
          }
          out.hidden = false;
          out.innerHTML = overallBlock(data);
          var cats = data.categories || [], total = cats.length, i = 0;
          function revealNext() {
            if (i >= total) {
              if (status) status.classList.add('is-done');
              if (live) live.textContent = 'Scorecard ready — local grade ' + data.overall.grade + ' (' + data.overall.score + '/100).';
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
        if (window.location.hash !== '#local' || !input) return;
        root.scrollIntoView({ behavior: 'smooth', block: 'start' });
        window.setTimeout(function () { try { input.focus({ preventScroll: true }); } catch (e) { input.focus(); } }, 450);
      }
      focusIfTargeted();
      window.addEventListener('hashchange', focusIfTargeted);
    })();
  </script>
@endsection
