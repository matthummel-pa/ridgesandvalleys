{{--
  Template Name: Email Deliverability Checker
--}}
@extends('layouts.app')

@section('content')
  @php($ctaHref = \App\cta_href(get_theme_mod('rv_cta_url', '/contact/')))

  {{-- HERO --}}
  <section class="rv-hero">
    <span class="rv-stripe" aria-hidden="true"></span>
    @include('partials.hero-bg', ['fallback' => ''])
    <div class="rv-shell rv-hero-inner">
      {!! \App\eyebrow(\App\field('hero_eyebrow', __('Free email deliverability checker', 'sage'))) !!}
      <h1 class="rv-hero-title">{{ \App\field('hero_title', __('Is your email landing in', 'sage')) }} <em class="rv-accent">{{ \App\field('hero_accent', __('spam?', 'sage')) }}</em></h1>
      <p class="rv-hero-sub">{{ \App\field('hero_lede', __('Check SPF, DKIM, and DMARC — and whether anyone can send mail as you. Plain English, no signup.', 'sage')) }}</p>
      <div class="rv-hero-actions">
        <a class="rv-btn {{ \App\hero_btn_class(\App\field('hero_btn1_style',''), 'rv-btn-primary') }}" href="#email">{{ \App\field('hero_btn1', __('Check my domain', 'sage')) }}</a>
        <a class="rv-btn {{ \App\hero_btn_class(\App\field('hero_btn2_style',''), 'rv-btn-ghost') }}" href="{{ $ctaHref }}">{{ \App\field('hero_btn2', __('Talk to me', 'sage')) }}</a>
      </div>
    </div>
  </section>

  {{-- LIVE CHECKER --}}
  <section id="email" class="rv-shell rv-band">
    {!! \App\eyebrow(__('Instant DNS check', 'sage')) !!}
    <h2 class="rv-section-title">{{ __('Check your email', 'sage') }} <em class="rv-accent">{{ __('setup.', 'sage') }}</em></h2>
    <p class="rv-page-intro">{{ __('Enter your domain (like yourbusiness.com — not your full email address). This reads your public DNS records; it never touches your inbox or your password.', 'sage') }}</p>

    <div class="rv-scan" id="rv-email" data-endpoint="{{ esc_url(rest_url('rv-tools/v1/email')) }}" style="margin-top:2rem" aria-label="{{ __('Email deliverability checker', 'sage') }}">
      <div class="rv-scan-head">
        <div class="rv-scan-head-row">
          <h3 class="rv-scan-title">{{ __('Deliverability report', 'sage') }}</h3>
          <span class="rv-scan-count" id="rv-email-count"></span>
        </div>
        <form class="rv-audit-form" id="rv-email-form">
          <label class="screen-reader-text" for="rv-email-url">{{ __('Your domain', 'sage') }}</label>
          <input type="text" id="rv-email-url" name="url" placeholder="yourbusiness.com" required>
          <button type="submit" class="rv-btn rv-btn-primary" id="rv-email-run">{{ __('Check domain', 'sage') }}</button>
        </form>
        <div class="rv-scan-bar"><span class="rv-scan-bar-fill" id="rv-email-fill"></span></div>
        <p class="rv-scan-status" id="rv-email-status" role="status" aria-live="polite">
          <span class="rv-scan-live">{{ __('Enter your domain to check its email setup.', 'sage') }}</span>
        </p>
      </div>
      <div class="rv-scan-body" id="rv-email-results" hidden></div>
      <div class="rv-scan-actions">
        <button type="button" class="rv-btn rv-btn-ghost" id="rv-email-reset">{{ __('Reset', 'sage') }}</button>
      </div>
    </div>
    <p class="rv-audit-note">{{ __('SPF, DKIM, and DMARC are fiddly to set up and easy to get wrong. Want me to configure them for you the right way?', 'sage') }} <a href="{{ $ctaHref }}">{{ __('Talk to me', 'sage') }}</a>.</p>
  </section>

  {{-- WHAT WE CHECK --}}
  <section class="rv-band rv-band-alt">
    <div class="rv-shell">
      {!! \App\eyebrow(__('What the check covers', 'sage')) !!}
      <h2 class="rv-section-title">{{ __('The three records that', 'sage') }} <em class="rv-accent">{{ __('protect', 'sage') }}</em> {{ __('your email.', 'sage') }}</h2>
      <div class="rv-grid rv-grid-3" style="margin-top:2rem">
        @php($areas = [
          [__('Can you receive mail? (MX)', 'sage'), __('Whether your domain has mail servers set up to accept email at all.', 'sage'), __('If MX records are missing or wrong, email sent to your business simply bounces — customers assume you ignored them.', 'sage')],
          [__('SPF — who can send as you', 'sage'), __('The record listing which servers are allowed to send email from your domain.', 'sage'), __('Without SPF, anyone can forge email from your address, and legitimate mail from your real provider is more likely to be flagged as spam.', 'sage')],
          [__('DKIM — a tamper-proof signature', 'sage'), __('A cryptographic signature that proves a message genuinely came from you.', 'sage'), __('DKIM lets inbox providers verify your mail is authentic and unaltered — a big factor in whether you land in the inbox or the junk folder.', 'sage')],
          [__('DMARC — the anti-phishing policy', 'sage'), __('The rule that ties SPF and DKIM together and tells inboxes what to do with mail that fails.', 'sage'), __('DMARC is what actually stops scammers from sending phishing emails “from” your business to your customers and suppliers.', 'sage')],
          [__('Why this hits small businesses', 'sage'), __('Many local businesses use email that was set up years ago and never configured for authentication.', 'sage'), __('The result is quiet damage: invoices and quotes landing in spam, and no protection if someone impersonates your brand.', 'sage')],
          [__('The good news', 'sage'), __('These are one-time DNS fixes — set them correctly once and they keep working.', 'sage'), __('Getting SPF, DKIM, and DMARC right improves inbox placement and shuts the door on spoofing. It\'s among the highest-value, lowest-cost fixes there is.', 'sage')],
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
      <h2 class="rv-cta-title">{{ \App\field('cta_title', __('Want your email trusted and spoof-proof?', 'sage')) }}</h2>
      <p class="rv-cta-sub">{{ \App\field('cta_sub', __('I\'ll set up SPF, DKIM, and DMARC correctly so your mail reaches inboxes and nobody can impersonate you.', 'sage')) }}</p>
      <a class="rv-btn rv-btn-on-dark" href="{{ $ctaHref }}">{{ \App\field('cta_button', __('Fix my email setup', 'sage')) }}</a>
    </div>
  </section>

  <script>
    (function () {
      var root = document.getElementById('rv-email');
      if (!root) return;
      var form   = document.getElementById('rv-email-form');
      var input  = document.getElementById('rv-email-url');
      var runBtn = document.getElementById('rv-email-run');
      var resetB = document.getElementById('rv-email-reset');
      var count  = document.getElementById('rv-email-count');
      var fill   = document.getElementById('rv-email-fill');
      var status = document.getElementById('rv-email-status');
      var live   = status ? status.querySelector('.rv-scan-live') : null;
      var out    = document.getElementById('rv-email-results');
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
        if (live) live.textContent = 'Enter your domain to check its email setup.';
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
          + '<div class="rv-grade-headline"><h3>Deliverability score</h3><p>Checked ' + esc(d.url) + '.</p>'
          + '<div class="rv-grade-meta"><span>' + (m.mx || 0) + ' mail servers</span><span>SPF ' + (m.spf ? '✓' : '✗') + '</span><span>DKIM ' + (m.dkim ? '✓' : '?') + '</span><span>DMARC ' + esc(m.dmarc || 'none') + '</span></div></div></div>';
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
        if (live) live.textContent = 'Looking up DNS for ' + u + '…';

        fetch(endpoint + '?url=' + encodeURIComponent(u)).then(function (r) { return r.json(); }).then(function (data) {
          if (!data || !data.ok) {
            if (status) status.classList.add('is-error');
            if (live) live.textContent = (data && data.error) ? data.error : 'Could not check that domain.';
            if (runBtn) runBtn.disabled = false;
            return;
          }
          out.hidden = false;
          out.innerHTML = overallBlock(data);
          var cats = data.categories || [], total = cats.length, i = 0;
          function revealNext() {
            if (i >= total) {
              if (status) status.classList.add('is-done');
              if (live) live.textContent = 'Report ready — deliverability grade ' + data.overall.grade + ' (' + data.overall.score + '/100).';
              if (count) count.textContent = data.overall.score + ' / 100';
              if (runBtn) runBtn.disabled = false;
              return;
            }
            out.insertAdjacentHTML('beforeend', categoryBlock(cats[i]));
            i++;
            if (fill) fill.style.width = (i / total * 100) + '%';
            if (live) live.textContent = 'Checking ' + cats[i - 1].name + '…';
            if (count) count.textContent = i + ' / ' + total + ' areas';
            if (reduce) { revealNext(); } else { timers.push(setTimeout(revealNext, 300)); }
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
        if (window.location.hash !== '#email' || !input) return;
        root.scrollIntoView({ behavior: 'smooth', block: 'start' });
        window.setTimeout(function () { try { input.focus({ preventScroll: true }); } catch (e) { input.focus(); } }, 450);
      }
      focusIfTargeted();
      window.addEventListener('hashchange', focusIfTargeted);
    })();
  </script>
@endsection
