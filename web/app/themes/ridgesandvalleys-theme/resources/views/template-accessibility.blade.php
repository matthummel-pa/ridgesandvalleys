{{--
  Template Name: Accessibility
--}}
@extends('layouts.app')

@php
/* WCAG 2.1 Level A + AA success criteria — the substance of the U.S. federal
   accessibility standards (Section 508 references WCAG 2.0 AA; the ADA Title II
   rule and the courts reference WCAG 2.1 AA). Grouped by the four POUR
   principles. Each row: [number, name, level, plain-language note]. */
$wcag = [
  'Perceivable' => ['P', 'Information and interface must be presentable in ways people can perceive.', [
    ['1.1.1', 'Non-text Content', 'A', 'Every image, icon, and control has a meaningful text alternative.'],
    ['1.2.1', 'Audio-only & Video-only (Prerecorded)', 'A', 'Transcripts for audio; text or audio for silent video.'],
    ['1.2.2', 'Captions (Prerecorded)', 'A', 'Captions on all pre-recorded video with sound.'],
    ['1.2.3', 'Audio Description or Media Alternative', 'A', 'A described or text version of pre-recorded video.'],
    ['1.2.4', 'Captions (Live)', 'AA', 'Captions on live audio content.'],
    ['1.2.5', 'Audio Description (Prerecorded)', 'AA', 'Narration of key visuals in pre-recorded video.'],
    ['1.3.1', 'Info and Relationships', 'A', 'Structure (headings, lists, tables) is coded, not just visual.'],
    ['1.3.2', 'Meaningful Sequence', 'A', 'Reading order makes sense to a screen reader.'],
    ['1.3.3', 'Sensory Characteristics', 'A', 'Instructions don\'t rely on shape, size, or position alone.'],
    ['1.3.4', 'Orientation', 'AA', 'Works in both portrait and landscape.'],
    ['1.3.5', 'Identify Input Purpose', 'AA', 'Common fields (name, email) are programmatically labeled.'],
    ['1.4.1', 'Use of Color', 'A', 'Color is never the only way information is conveyed.'],
    ['1.4.2', 'Audio Control', 'A', 'Auto-playing sound can be paused or stopped.'],
    ['1.4.3', 'Contrast (Minimum)', 'AA', 'Text meets a 4.5:1 contrast ratio (3:1 for large text).'],
    ['1.4.4', 'Resize Text', 'AA', 'Text scales to 200% without breaking the layout.'],
    ['1.4.5', 'Images of Text', 'AA', 'Real text is used instead of pictures of text.'],
    ['1.4.10', 'Reflow', 'AA', 'Content reflows to one column on a phone — no side-scrolling.'],
    ['1.4.11', 'Non-text Contrast', 'AA', 'Buttons, inputs, and icons have sufficient contrast.'],
    ['1.4.12', 'Text Spacing', 'AA', 'Nothing breaks when users increase line or letter spacing.'],
    ['1.4.13', 'Content on Hover or Focus', 'AA', 'Tooltips and pop-ups are dismissible and stable.'],
  ]],
  'Operable' => ['O', 'Interface components and navigation must be operable by everyone.', [
    ['2.1.1', 'Keyboard', 'A', 'Everything works with a keyboard alone — no mouse required.'],
    ['2.1.2', 'No Keyboard Trap', 'A', 'Keyboard focus can always move away from a component.'],
    ['2.1.4', 'Character Key Shortcuts', 'A', 'Single-key shortcuts can be turned off or remapped.'],
    ['2.2.1', 'Timing Adjustable', 'A', 'Time limits can be extended or turned off.'],
    ['2.2.2', 'Pause, Stop, Hide', 'A', 'Moving or auto-updating content can be paused.'],
    ['2.3.1', 'Three Flashes or Below Threshold', 'A', 'Nothing flashes in a way that can trigger seizures.'],
    ['2.4.1', 'Bypass Blocks', 'A', 'A "skip to content" link bypasses repeated navigation.'],
    ['2.4.2', 'Page Titled', 'A', 'Every page has a clear, descriptive title.'],
    ['2.4.3', 'Focus Order', 'A', 'Tabbing moves through the page in a logical order.'],
    ['2.4.4', 'Link Purpose (In Context)', 'A', 'Link text makes its destination clear.'],
    ['2.4.5', 'Multiple Ways', 'AA', 'More than one way to find a page (menu, search, sitemap).'],
    ['2.4.6', 'Headings and Labels', 'AA', 'Headings and form labels are descriptive.'],
    ['2.4.7', 'Focus Visible', 'AA', 'The keyboard focus indicator is always visible.'],
    ['2.5.1', 'Pointer Gestures', 'A', 'Multi-finger or path gestures have a simple alternative.'],
    ['2.5.2', 'Pointer Cancellation', 'A', 'Accidental taps can be aborted before they fire.'],
    ['2.5.3', 'Label in Name', 'A', 'A control\'s visible label matches its accessible name.'],
    ['2.5.4', 'Motion Actuation', 'A', 'Features triggered by motion have a button alternative.'],
  ]],
  'Understandable' => ['U', 'Information and the operation of the interface must be understandable.', [
    ['3.1.1', 'Language of Page', 'A', 'The page\'s language is set so screen readers pronounce it right.'],
    ['3.1.2', 'Language of Parts', 'AA', 'Passages in another language are marked up.'],
    ['3.2.1', 'On Focus', 'A', 'Nothing changes unexpectedly when an element gets focus.'],
    ['3.2.2', 'On Input', 'A', 'Changing a field doesn\'t trigger a surprise action.'],
    ['3.2.3', 'Consistent Navigation', 'AA', 'Navigation stays in the same place across pages.'],
    ['3.2.4', 'Consistent Identification', 'AA', 'The same components are labeled the same way everywhere.'],
    ['3.3.1', 'Error Identification', 'A', 'Form errors are described in text, not just color.'],
    ['3.3.2', 'Labels or Instructions', 'A', 'Every field has a clear label or instruction.'],
    ['3.3.3', 'Error Suggestion', 'AA', 'When we can, we suggest how to fix an error.'],
    ['3.3.4', 'Error Prevention (Legal, Financial, Data)', 'AA', 'Important submissions can be reviewed and corrected.'],
  ]],
  'Robust' => ['R', 'Content must be robust enough for assistive technologies, now and later.', [
    ['4.1.1', 'Parsing', 'A', 'Clean, valid markup that assistive tech can read reliably.'],
    ['4.1.2', 'Name, Role, Value', 'A', 'Custom controls expose their name, role, and state.'],
    ['4.1.3', 'Status Messages', 'AA', 'Status updates are announced without stealing focus.'],
  ]],
];
$total = array_sum(array_map(fn ($g) => count($g[2]), $wcag));
@endphp

@section('content')
  @php($ctaHref = \App\cta_href(get_theme_mod('rv_cta_url', '/contact/')))

  {{-- HERO --}}
  <section class="rv-hero">
    <span class="rv-stripe" aria-hidden="true"></span>
    @include('partials.hero-bg', ['fallback' => ''])
    <div class="rv-shell rv-hero-inner">
      {!! \App\eyebrow(\App\field('hero_eyebrow', __('Accessibility · WCAG 2.1 AA · Section 508', 'sage'))) !!}
      <h1 class="rv-hero-title">{{ \App\field('hero_title', __('A front door that opens for', 'sage')) }} <em class="rv-accent">{{ \App\field('hero_accent', __('everyone.', 'sage')) }}</em></h1>
      <p class="rv-hero-sub">{{ \App\field('hero_sub', __('An accessible website isn\'t a nice-to-have — it\'s how you reach more customers, rank better, and stay on the right side of the law. Here\'s the full federal standard, what it means, and how I build to it on every project.', 'sage')) }}</p>
      <div class="rv-hero-actions">
        <a class="rv-btn rv-btn-primary" href="#standards">{{ \App\field('a11y_hero_btn1', __('Run a live audit', 'sage')) }}</a>
        <a class="rv-btn rv-btn-ghost" href="{{ $ctaHref }}">{{ \App\field('a11y_hero_btn2', __('Talk to me', 'sage')) }}</a>
      </div>
    </div>
  </section>

  {{-- WHY IT MATTERS --}}
  <section class="rv-shell rv-band">
    {!! \App\eyebrow(\App\field('why_eyebrow', __('Why it matters', 'sage'))) !!}
    <h2 class="rv-section-title">{{ \App\field('why_title', __('Accessibility is good business,', 'sage')) }} <em class="rv-accent">{{ \App\field('why_accent', __('not just', 'sage')) }}</em> {{ \App\field('why_title_end', __('good manners.', 'sage')) }}</h2>
    <p class="rv-page-intro">{{ \App\field('why_intro', __('When a site is hard to use, people don\'t complain — they leave, and often they don\'t come back. Building for everyone widens your audience and protects your business.', 'sage')) }}</p>
    <div class="rv-stats" style="margin-top:2rem">
      @foreach (\App\field_rows('why_stats', [
        ['num' => __('1 in 4', 'sage'), 'label' => __('U.S. adults live with a disability — over 70 million people.', 'sage'), 'src' => __('CDC, 2024', 'sage')],
        ['num' => '3,117', 'label' => __('federal website-accessibility lawsuits filed in 2025 — up 27% over 2024.', 'sage'), 'src' => __('Seyfarth ADA Title III, 2026', 'sage')],
        ['num' => '36%', 'label' => __('of all ADA Title III federal suits in 2025 were about websites.', 'sage'), 'src' => __('Seyfarth ADA Title III, 2026', 'sage')],
        ['num' => '$490B', 'label' => __('in annual disposable income is controlled by working-age people with disabilities.', 'sage'), 'src' => __('American Institutes for Research', 'sage')],
      ]) as $stat)
        <div class="rv-stat"><div class="rv-stat-num">{{ $stat['num'] ?? '' }}</div><p class="rv-stat-label">{{ $stat['label'] ?? '' }}</p><p class="rv-stat-src">{{ $stat['src'] ?? '' }}</p></div>
      @endforeach
    </div>
    <p class="rv-legal-note">{{ \App\field('why_legal_note', __('This page is general information, not legal advice. Which rules apply to your business depends on your situation — talk to a qualified attorney about your specific obligations.', 'sage')) }}</p>
  </section>

  {{-- THE LEGAL PICTURE --}}
  <section class="rv-band rv-band-alt">
    <div class="rv-shell">
      {!! \App\eyebrow(\App\field('legal_eyebrow', __('The rules, in plain English', 'sage'))) !!}
      <h2 class="rv-section-title">{{ \App\field('legal_title', __('What the federal standards actually', 'sage')) }} <em class="rv-accent">{{ \App\field('legal_accent', __('say.', 'sage')) }}</em></h2>
      <div class="rv-grid rv-grid-3" style="margin-top:2rem">
        @foreach (\App\field_rows('legal_items', [
          ['title' => __('Section 508', 'sage'), 'text' => __('Requires federal agencies and many of their contractors to build technology to WCAG 2.0 Level AA. It\'s the oldest and most established U.S. digital-accessibility standard.', 'sage')],
          ['title' => __('ADA Title II', 'sage'), 'text' => __('A 2024 federal rule requires state and local governments to meet WCAG 2.1 Level AA. Deadlines were extended in 2026: April 26, 2027 for larger entities (50k+), and April 26, 2028 for smaller ones and special districts.', 'sage')],
          ['title' => __('ADA Title III', 'sage'), 'text' => __('Covers private businesses open to the public. There\'s no single codified web standard yet, but courts overwhelmingly treat WCAG 2.1 Level AA as the benchmark — which is exactly what most website lawsuits are measured against.', 'sage')],
        ]) as $lg)
          <article class="rv-card rv-feature">
            <span class="rv-stripe rv-stripe-thin" aria-hidden="true"></span>
            <h3 class="rv-feature-title">{{ $lg['title'] ?? '' }}</h3>
            <p class="rv-feature-text">{{ $lg['text'] ?? '' }}</p>
          </article>
        @endforeach
      </div>
      <p class="rv-feature-text" style="margin-top:1.75rem;max-width:74ch">{{ \App\field('legal_through', __('The through-line: WCAG 2.1 Level AA is the target. Build to it and you\'re aligned with Section 508, ready for ADA Title II, and measured favorably under Title III. That\'s the standard I build every site to — the full checklist is below.', 'sage')) }}</p>
    </div>
  </section>

  {{-- LIVE AUDIT: URL scanner + full standard in one container --}}
  @php($autoMap = ['3.1.1' => 'lang', '2.4.2' => 'title', '1.1.1' => 'alt', '3.3.2' => 'labels', '1.3.1' => 'h1', '2.4.6' => 'headings', '2.4.4' => 'links', '2.4.1' => 'landmark'])
  <section id="standards" class="rv-shell rv-band">
    {!! \App\eyebrow(\App\field('audit_eyebrow', __('Check your own site', 'sage'))) !!}
    <h2 class="rv-section-title">{{ \App\field('audit_title', __('Run a live', 'sage')) }} <em class="rv-accent">{{ \App\field('audit_accent', __('WCAG 2.1 AA', 'sage')) }}</em> {{ \App\field('audit_title_end', __('audit.', 'sage')) }}</h2>
    <p class="rv-page-intro">{{ \App\field('audit_intro', __('Enter your URL and watch it run. The automated checks verify what a scanner can — 8 of the 50 criteria — and every other item is flagged for the manual review a person has to do. That\'s most of accessibility, and exactly why I audit by hand.', 'sage')) }}</p>

    <div class="rv-scan" id="rv-scan" data-endpoint="{{ esc_url(rest_url('rv-tools/v1/a11y')) }}" style="margin-top:2rem" aria-label="{{ __('WCAG 2.1 AA live audit', 'sage') }}">
      <div class="rv-scan-head">
        <div class="rv-scan-head-row">
          <h3 class="rv-scan-title">{{ __('WCAG 2.1 Level AA — live audit', 'sage') }}</h3>
          <span class="rv-scan-count" id="rv-scan-count">0 / {{ $total }}</span>
        </div>
        <form class="rv-audit-form" id="rv-audit-form">
          <label class="screen-reader-text" for="rv-audit-url">{{ __('Website URL to audit', 'sage') }}</label>
          <input type="url" id="rv-audit-url" name="url" placeholder="https://your-site.com" required>
          <button type="submit" class="rv-btn rv-btn-primary" id="rv-audit-run">{{ __('Run the audit', 'sage') }}</button>
        </form>
        <div class="rv-scan-bar"><span class="rv-scan-bar-fill" id="rv-scan-fill"></span></div>
        <p class="rv-scan-status" id="rv-scan-status" role="status" aria-live="polite">
          <span class="rv-scan-live">{{ __('Enter a URL to run the automated checks.', 'sage') }}</span>
        </p>
        <div class="rv-audit-summary" id="rv-audit-summary" hidden></div>
      </div>
      <div class="rv-scan-body">
        @foreach ($wcag as $name => $group)
          <div class="rv-pour">
            <div class="rv-pour-head">
              <span class="rv-pour-letter" aria-hidden="true">{{ $group[0] }}</span>
              <div>
                <h4 class="rv-pour-title">{{ $name }}</h4>
                <p class="rv-pour-desc">{{ $group[1] }}</p>
              </div>
            </div>
            <ul class="rv-crit-list">
              @foreach ($group[2] as $c)
                @php($auto = $autoMap[$c[0]] ?? '')
                <li class="rv-crit is-pending" data-crit @if ($auto) data-auto="{{ $auto }}" @else data-manual @endif>
                  <span class="rv-crit-tick" aria-hidden="true">
                    <svg viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="3" stroke-linecap="round" stroke-linejoin="round"><path d="M20 6L9 17l-5-5"/></svg>
                  </span>
                  <span class="rv-crit-main">
                    <span class="rv-crit-name">{{ $c[1] }} <span class="rv-crit-num">· {{ $c[0] }}</span></span>
                    <span class="rv-crit-note">{{ $c[3] }}</span>
                    <span class="rv-crit-detail" data-detail></span>
                  </span>
                  <span class="rv-crit-side">
                    <span class="rv-crit-state" data-state></span>
                    <span class="rv-crit-level">{{ $c[2] }}</span>
                  </span>
                </li>
              @endforeach
            </ul>
          </div>
        @endforeach
      </div>
      <div class="rv-scan-actions">
        <button type="button" class="rv-btn rv-btn-ghost" id="rv-scan-rerun">{{ __('Reset', 'sage') }}</button>
      </div>
    </div>
    <p class="rv-audit-note">{{ \App\field('audit_note', __('Automated scans can only verify a fraction of WCAG — no tool catches it all. Want the full manual audit and the fixes?', 'sage')) }} <a href="{{ $ctaHref }}">{{ \App\field('a11y_hero_btn2', __('Talk to me', 'sage')) }}</a>.</p>
  </section>

  {{-- BUSINESS BENEFITS --}}
  <section class="rv-band rv-band-alt">
    <div class="rv-shell">
      {!! \App\eyebrow(\App\field('benefits_eyebrow', __('What you get out of it', 'sage'))) !!}
      <h2 class="rv-section-title">{{ \App\field('benefits_title', __('Accessible sites just', 'sage')) }} <em class="rv-accent">{{ \App\field('benefits_accent', __('perform', 'sage')) }}</em> {{ \App\field('benefits_title_end', __('better.', 'sage')) }}</h2>
      <div class="rv-grid rv-grid-3" style="margin-top:2rem">
        @foreach (\App\field_rows('benefits_items', [
          ['title' => __('A bigger audience', 'sage'), 'text' => __('One in four adults — plus their families and friends — can actually use your site. That\'s customers your competitors are turning away.', 'sage')],
          ['title' => __('Better SEO', 'sage'), 'text' => __('Accessibility and search optimization share the same roots: clean structure, real headings, alt text, and captions. Google reads a site the way a screen reader does.', 'sage')],
          ['title' => __('Works for everyone', 'sage'), 'text' => __('Captions in a noisy room, high contrast in bright sun, big tap targets one-handed. "Accessible" design is just better design for all of us.', 'sage')],
          ['title' => __('Faster, cleaner code', 'sage'), 'text' => __('Building accessibly means lean, semantic markup — which also loads faster and is easier to maintain.', 'sage')],
          ['title' => __('Trust and reputation', 'sage'), 'text' => __('A site that welcomes everyone says something about how you run your business. Exclusion is a bad look; inclusion builds loyalty.', 'sage')],
          ['title' => __('Lower legal risk', 'sage'), 'text' => __('Most website lawsuits target sites with obvious, fixable barriers. Building to the standard is the most reliable way to stay off that list.', 'sage')],
        ]) as $b)
          <article class="rv-card rv-feature">
            <span class="rv-stripe rv-stripe-thin" aria-hidden="true"></span>
            <h3 class="rv-feature-title">{{ $b['title'] ?? '' }}</h3>
            <p class="rv-feature-text">{{ $b['text'] ?? '' }}</p>
          </article>
        @endforeach
      </div>
    </div>
  </section>

  {{-- HOW I BUILD ACCESSIBLE --}}
  <section class="rv-shell rv-band">
    {!! \App\eyebrow(\App\field('how_eyebrow', __('How I build', 'sage'))) !!}
    <h2 class="rv-section-title">{{ \App\field('how_title', __('Accessibility-first, not', 'sage')) }} <em class="rv-accent">{{ \App\field('how_accent', __('bolted-on.', 'sage')) }}</em></h2>
    <p class="rv-page-intro">{{ \App\field('how_intro', __('An accessibility overlay or "widget" won\'t make a site compliant — and can make it worse. Real accessibility is built into the markup from the first line. Here\'s what that looks like on every project.', 'sage')) }}</p>
    <div class="rv-grid rv-grid-3" style="margin-top:2rem">
      @foreach (\App\field_rows('how_items', [
        ['title' => __('Semantic by default', 'sage'), 'text' => __('Proper headings, landmarks, lists, and labels — so assistive tech understands the page without guesswork.', 'sage')],
        ['title' => __('Keyboard tested', 'sage'), 'text' => __('Every menu, form, and control is operated with a keyboard alone, with a focus outline you can always see.', 'sage')],
        ['title' => __('Contrast checked', 'sage'), 'text' => __('Colors are chosen and verified against the 4.5:1 minimum before they ship — not hoped for after.', 'sage')],
        ['title' => __('Screen-reader passes', 'sage'), 'text' => __('Key pages are walked through with a screen reader, because automated tools catch only part of the picture.', 'sage')],
        ['title' => __('Real alt text', 'sage'), 'text' => __('Images get descriptions that convey meaning — written by a person, not auto-generated filler.', 'sage')],
        ['title' => __('Checked before launch', 'sage'), 'text' => __('Every page is audited against this checklist before it goes live — and you can re-check anytime with the free tools.', 'sage')],
      ]) as $h)
        <article class="rv-card rv-feature">
          <span class="rv-stripe rv-stripe-thin" aria-hidden="true"></span>
          <h3 class="rv-feature-title">{{ $h['title'] ?? '' }}</h3>
          <p class="rv-feature-text">{{ $h['text'] ?? '' }}</p>
        </article>
      @endforeach
    </div>
  </section>

  {{-- STATEMENT --}}
  <section class="rv-band rv-band-alt">
    <div class="rv-shell-full">
      <div class="rv-reading">
        {!! \App\eyebrow(\App\field('stmt_eyebrow', __('Our commitment', 'sage'))) !!}
        <h2 class="rv-section-title">{{ \App\field('stmt_title', __('Accessibility statement', 'sage')) }}</h2>
            <div class="rv-prose" style="margin-top:1rem">
              <p>{{ \App\field('stmt_p1', __('Ridges & Valleys Studio is committed to making this website usable for as many people as possible, regardless of ability or technology. I aim to meet WCAG 2.1 Level AA across the site: readable contrast, keyboard-accessible navigation, meaningful text alternatives, clear focus states, semantic structure, and content that works with screen readers.', 'sage')) }}</p>
              <p>{{ \App\field('stmt_p2', __('Accessibility is ongoing work. Some third-party or embedded content may not fully meet this standard; where I find gaps, I work to fix or replace them. If you run into a barrier on this site, please tell me what happened and the page you were on — I\'ll put it right.', 'sage')) }}</p>
            </div>
        <p style="margin-top:1.5rem"><a class="rv-btn rv-btn-primary" href="{{ $ctaHref }}">{{ \App\field('stmt_button', __('Report an accessibility issue', 'sage')) }}</a></p>
      </div>
    </div>
  </section>

  {{-- CTA --}}
  <section class="rv-cta-band">
    <div class="rv-shell rv-cta-inner">
      <h2 class="rv-cta-title">{{ \App\field('a11y_cta_title', __('Want to know where your site stands?', 'sage')) }}</h2>
      <p class="rv-cta-sub">{{ \App\field('a11y_cta_sub', __('Run the free accessibility scan, or send me your URL and I\'ll take a real look — no pressure, no jargon.', 'sage')) }}</p>
      <a class="rv-btn rv-btn-on-dark" href="{{ home_url('/free-tools/') }}">{{ \App\field('a11y_cta_button', __('Run a free scan', 'sage')) }}</a>
    </div>
  </section>

  <script>
    (function () {
      var scan = document.getElementById('rv-scan');
      if (!scan) return;
      var form   = document.getElementById('rv-audit-form');
      var input  = document.getElementById('rv-audit-url');
      var runBtn = document.getElementById('rv-audit-run');
      var reset  = document.getElementById('rv-scan-rerun');
      var count  = document.getElementById('rv-scan-count');
      var fill   = document.getElementById('rv-scan-fill');
      var status = document.getElementById('rv-scan-status');
      var live   = status ? status.querySelector('.rv-scan-live') : null;
      var summary = document.getElementById('rv-audit-summary');
      var endpoint = scan.getAttribute('data-endpoint');
      var crits  = Array.prototype.slice.call(scan.querySelectorAll('[data-crit]'));
      var total  = crits.length;
      var reduce = window.matchMedia('(prefers-reduced-motion: reduce)').matches;
      var LABELS = { pass: 'Pass', warn: 'Review', fail: 'Fail', manual: 'Manual' };
      // Automated findings return in this fixed order — see rv_rest_a11y() in app/tools.php.
      var KEYS = ['lang', 'title', 'alt', 'labels', 'h1', 'headings', 'links', 'landmark'];
      var timers = [];

      function setCount(n) {
        if (count) count.textContent = n + ' / ' + total;
        if (fill)  fill.style.width = (total ? (n / total) * 100 : 0) + '%';
      }
      function clearTimers() { timers.forEach(clearTimeout); timers = []; }
      function resetItems() {
        clearTimers();
        crits.forEach(function (c) {
          c.className = 'rv-crit is-pending';
          var st = c.querySelector('[data-state]'); if (st) st.textContent = '';
          var d  = c.querySelector('[data-detail]'); if (d) d.textContent = '';
        });
        if (status) status.classList.remove('is-done', 'is-error');
        if (summary) { summary.hidden = true; summary.innerHTML = ''; }
        setCount(0);
        if (live) live.textContent = 'Enter a URL to run the automated checks.';
      }
      function applyItem(c, state, detail) {
        c.classList.remove('is-pending');
        c.classList.add('is-' + state);
        var st = c.querySelector('[data-state]'); if (st) st.textContent = LABELS[state] || '';
        if (detail) { var d = c.querySelector('[data-detail]'); if (d) d.textContent = detail; }
      }
      function finish(counts, url) {
        if (status) status.classList.add('is-done');
        if (live) live.textContent = 'Audit complete for ' + url;
        if (summary) {
          var chips = '<div class="rv-badges">'
            + '<span class="rv-badge is-pass">' + counts.pass + ' passed</span>'
            + (counts.warn ? '<span class="rv-badge is-warn">' + counts.warn + ' to review</span>' : '')
            + (counts.fail ? '<span class="rv-badge is-fail">' + counts.fail + ' issue' + (counts.fail > 1 ? 's' : '') + '</span>' : '')
            + '<span class="rv-badge is-manual">' + counts.manual + ' need manual review</span></div>';
          var legend = '<ul class="rv-audit-legend">'
            + '<li><span class="rv-legend-dot is-pass"></span> Pass — automated check passed</li>'
            + '<li><span class="rv-legend-dot is-warn"></span> Review — worth a closer look</li>'
            + '<li><span class="rv-legend-dot is-fail"></span> Fail — the scan found a problem</li>'
            + '<li><span class="rv-legend-dot is-manual"></span> Manual — a person needs to verify this one</li>'
            + '</ul>';
          summary.innerHTML = chips + legend;
          summary.hidden = false;
        }
        if (runBtn) runBtn.disabled = false;
      }
      function walk(resultByKey, url) {
        var i = 0, counts = { pass: 0, warn: 0, fail: 0, manual: 0 };
        function step() {
          if (i >= total) { finish(counts, url); return; }
          var c = crits[i];
          var auto = c.getAttribute('data-auto');
          var state = 'manual', detail = '';
          if (auto && resultByKey[auto]) { state = resultByKey[auto].status; detail = resultByKey[auto].detail; }
          applyItem(c, state, detail);
          counts[state] = (counts[state] || 0) + 1;
          setCount(i + 1);
          var nm = c.querySelector('.rv-crit-name');
          if (live && nm) live.textContent = 'Checking ' + nm.textContent.replace(/\s+/g, ' ').trim() + '…';
          i++;
          if (reduce) { step(); } else { timers.push(setTimeout(step, 42)); }
        }
        step();
      }

      if (form) form.addEventListener('submit', function (e) {
        e.preventDefault();
        var url = input.value.trim();
        if (!url) return;
        if (runBtn) runBtn.disabled = true;
        resetItems();
        if (live) live.textContent = 'Fetching and scanning ' + url + '…';
        if (fill) fill.style.width = '5%';
        fetch(endpoint + '?url=' + encodeURIComponent(url))
          .then(function (r) { return r.json(); })
          .then(function (data) {
            if (!data || !data.ok) {
              if (status) status.classList.add('is-error');
              if (live) live.textContent = (data && data.error) ? data.error : 'Could not scan that URL.';
              if (runBtn) runBtn.disabled = false;
              return;
            }
            var map = {};
            (data.findings || []).forEach(function (f, idx) {
              if (KEYS[idx]) map[KEYS[idx]] = { status: f.status, detail: f.detail || '' };
            });
            walk(map, url);
          })
          .catch(function () {
            if (status) status.classList.add('is-error');
            if (live) live.textContent = 'Network error — please try again.';
            if (runBtn) runBtn.disabled = false;
          });
      });

      if (reset) reset.addEventListener('click', resetItems);
      setCount(0);

      // Arriving via the hero's "Run a live audit" link (#standards): bring the
      // audit into view and put the cursor in the URL field, ready to type.
      function focusIfTargeted() {
        if (window.location.hash !== '#standards' || !input) return;
        scan.scrollIntoView({ behavior: 'smooth', block: 'start' });
        window.setTimeout(function () {
          try { input.focus({ preventScroll: true }); } catch (e) { input.focus(); }
        }, 450);
      }
      focusIfTargeted();
      window.addEventListener('hashchange', focusIfTargeted);
    })();
  </script>
@endsection
