{{--
  Template Name: Free Tools
--}}
@extends('layouts.app')

@section('content')
  @php($ctaHref = \App\cta_href(get_theme_mod('rv_cta_url', '/contact/')))
  @php($faqs = \App\field_rows('tfaq_items', [
    ['q' => __('Are these tools really free?', 'sage'), 'a' => __('Yes — completely. There\'s no account to create, no email to hand over, and no credit card. Run them as often as you like, on any site you like.', 'sage')],
    ['q' => __('Do I have to give you my email or sign up?', 'sage'), 'a' => __('No. Enter a URL (and, for the SEO checker, an optional keyword) and you get your results on the spot. Nothing is gated behind a form.', 'sage')],
    ['q' => __('How accurate are the results?', 'sage'), 'a' => __('The URL tools read your page\'s actual code and its server responses, so they\'re reliable for the things a machine can measure — HTTPS, meta tags, headings, security headers, and the like. Judgment calls like design, message clarity, and deep accessibility still need a human. That\'s the part I finish by hand.', 'sage')],
    ['q' => __('What\'s the difference between the Website Grader and the SEO Checker?', 'sage'), 'a' => __('The Website Grader is a broad, seven-area report card — a quick overall health check. The SEO Checker goes deep on search specifically, with a Google snippet preview, keyword analysis, crawlability (robots.txt and sitemap), and structured data. Use the grader for the big picture, the SEO checker to dig into rankings.', 'sage')],
    ['q' => __('Will you try to sell me something?', 'sage'), 'a' => __('Only if you ask. The tools work with no strings attached. If you\'d like the issues fixed for you, I\'m happy to help — but that\'s your call, not a catch.', 'sage')],
    ['q' => __('Can you fix what the tools find?', 'sage'), 'a' => __('Yes. Send me your URL and I\'ll turn the results into a short, prioritized plan in plain English — and I can do the work, from a quick Website Rescue to a full rebuild.', 'sage')],
    ['q' => __('Do you work with businesses in my town?', 'sage'), 'a' => __('I serve Gettysburg, Adams County, and the surrounding South Central PA area — Hanover, Littlestown, New Oxford, McSherrystown, Biglerville, Fairfield, and nearby. If you\'re local, we\'re a fit.', 'sage')],
    ['q' => __('Do the tools store my website\'s data?', 'sage'), 'a' => __('They fetch the page you enter in order to analyze it, and they don\'t save your results. Each run is a fresh, on-the-spot check.', 'sage')],
  ]))

  {{-- HERO --}}
  <section class="rv-hero">
    <span class="rv-stripe" aria-hidden="true"></span>
    @include('partials.hero-bg', ['fallback' => ''])
    <div class="rv-shell rv-hero-inner">
      {!! \App\eyebrow(\App\field('hero_eyebrow', __('Free website tools · No email required', 'sage'))) !!}
      <h1 class="rv-hero-title">{{ \App\field('hero_title', __('Free tools to check your', 'sage')) }} <em class="rv-accent">{{ \App\field('hero_accent', __('website.', 'sage')) }}</em></h1>
      <p class="rv-hero-sub">{{ \App\field('hero_sub', __('Grade your site, audit your SEO, test accessibility, and scan your security — in seconds, for free, with no signup. Built by a local developer for Gettysburg and South Central PA businesses who want to know where they really stand online.', 'sage')) }}</p>
      <div class="rv-hero-actions">
        <a class="rv-btn {{ \App\hero_btn_class(\App\field('hero_btn1_style',''), 'rv-btn-primary') }}" href="#tools">{{ \App\field('hero_btn1', __('Browse the tools', 'sage')) }}</a>
        <a class="rv-btn {{ \App\hero_btn_class(\App\field('hero_btn2_style',''), 'rv-btn-ghost') }}" href="{{ $ctaHref }}">{{ \App\field('hero_btn2', __('Talk to me', 'sage')) }}</a>
      </div>
    </div>
  </section>

  {{-- INTRO (SEO content) --}}
  <section class="rv-shell-full">
    <div class="rv-reading rv-prose" style="padding-block:var(--section-y) 0">
          <p>{{ \App\field('tools_intro_p1', __('Your website is working for you around the clock — but is it actually doing its job? Most small-business owners have no easy way to tell whether their site loads fast enough, shows up on Google, works on a phone, or keeps visitors\' information safe. These free tools answer those questions in plain English, so you can see exactly where your site stands before you spend a dollar fixing it.', 'sage')) }}</p>
          <p>{{ \App\field('tools_intro_p2', __('There\'s no catch. Every tool below runs instantly in your browser or against your live page — no account, no email, no sales call required. Enter your address, read your results, and decide what to do next. If you\'d like a hand with what turns up, that\'s what the studio is here for.', 'sage')) }}</p>
    </div>
  </section>

  {{-- THE CHECKERS --}}
  <section id="tools" class="rv-shell rv-band">
    {!! \App\eyebrow(\App\field('checkers_eyebrow', __('The checkers', 'sage'))) !!}
    <h2 class="rv-section-title">{{ \App\field('checkers_title', __('Six instant', 'sage')) }} <em class="rv-accent">{{ \App\field('checkers_accent', __('report cards.', 'sage')) }}</em></h2>
    <p class="rv-page-intro">{{ \App\field('checkers_intro', __('Enter your URL and get a clear, color-coded grade with every check explained — and why it matters for your business.', 'sage')) }}</p>
    <div class="rv-grid rv-grid-3" style="margin-top:2rem">
      @foreach (\App\field_rows('checkers_items', [
        ['badge' => __('Most popular', 'sage'), 'title' => __('Website Grader', 'sage'), 'url' => '/website-grader/', 'text' => __('Your whole site, graded across seven areas — SEO, page speed, mobile, readability, security, technical health, and social sharing.', 'sage'), 'why' => __('The fastest way to see where you stand and what to fix first.', 'sage')],
        ['badge' => '', 'title' => __('SEO Checker', 'sage'), 'url' => '/seo-checker/', 'text' => __('A deep search audit with a live Google snippet preview, target-keyword analysis, crawlability (robots.txt + sitemap), and structured data.', 'sage'), 'why' => __('Shows exactly why you are — or aren\'t — showing up in search.', 'sage')],
        ['badge' => '', 'title' => __('Accessibility Checker', 'sage'), 'url' => '/accessibility/', 'text' => __('Scan any page against WCAG 2.2 AA — the latest W3C standard — with a live, running checklist.', 'sage'), 'why' => __('Reach more customers and lower your legal risk.', 'sage')],
        ['badge' => '', 'title' => __('Security Checker', 'sage'), 'url' => '/security-checker/', 'text' => __('HTTPS and HSTS, the modern browser security headers, cookie safety, information leaks, and front-end risks.', 'sage'), 'why' => __('Protect your customers\' trust — and your reputation.', 'sage')],
        ['badge' => '', 'title' => __('Email Deliverability Checker', 'sage'), 'url' => '/email-checker/', 'text' => __('Checks the DNS records — SPF, DKIM, and DMARC — that decide whether your email is trusted or lands in spam.', 'sage'), 'why' => __('Stop your invoices landing in junk, and stop scammers spoofing you.', 'sage')],
        ['badge' => '', 'title' => __('Local SEO Scorecard', 'sage'), 'url' => '/local-seo/', 'text' => __('Scores the local signals — NAP, LocalBusiness schema, maps, and reviews — that get you into Google\'s map pack.', 'sage'), 'why' => __('Where much of a local business\'s real traffic comes from.', 'sage')],
      ]) as $t)
        <a class="rv-card rv-feature rv-toolcard" href="{{ home_url($t['url'] ?? '/') }}">
          <span class="rv-stripe rv-stripe-thin" aria-hidden="true"></span>
          @if (($t['badge'] ?? '') !== '')<span class="rv-toolcard-badge">{{ $t['badge'] }}</span>@endif
          <h3 class="rv-feature-title">{{ $t['title'] ?? '' }}</h3>
          <p class="rv-feature-text">{{ $t['text'] ?? '' }}</p>
          <p class="rv-toolcard-why"><b>{{ __('Why it matters:', 'sage') }}</b> {{ $t['why'] ?? '' }}</p>
          <span class="rv-readmore">{{ \App\field('checkers_open', __('Open the tool', 'sage')) }} {!! \App\icon('arrow') !!}</span>
        </a>
      @endforeach
    </div>
  </section>

  {{-- QUICK TOOLS (embedded, client-side) --}}
  <section class="rv-band rv-band-alt">
    <div class="rv-shell">
      {!! \App\eyebrow(\App\field('quick_eyebrow', __('Quick tools', 'sage'))) !!}
      <h2 class="rv-section-title">{{ \App\field('quick_title', __('Handy calculators &', 'sage')) }} <em class="rv-accent">{{ \App\field('quick_accent', __('checks.', 'sage')) }}</em></h2>
      <p class="rv-page-intro">{{ \App\field('quick_intro', __('No URL needed — these run right here on the page. Test a color combination, ballpark a project, or estimate a care plan.', 'sage')) }}</p>
      <div class="rv-tools-grid" style="margin-top:2rem">
        {!! \App\rv_render_contrast() !!}
        {!! \App\rv_render_estimator() !!}
        {!! \App\rv_render_calculator() !!}
      </div>
    </div>
  </section>

  {{-- HOW TO GET THE MOST --}}
  <section class="rv-shell rv-band">
    {!! \App\eyebrow(\App\field('howto_eyebrow', __('Getting the most from them', 'sage'))) !!}
    <h2 class="rv-section-title">{{ \App\field('howto_title', __('How to use these', 'sage')) }} <em class="rv-accent">{{ \App\field('howto_accent', __('tools.', 'sage')) }}</em></h2>
    <div class="rv-grid rv-grid-3" style="margin-top:2rem">
      @foreach (\App\field_rows('howto_items', [
        ['title' => __('Start with the grader', 'sage'), 'text' => __('Run the Website Grader first for the big picture, then dig into whichever area scored lowest with the dedicated SEO, accessibility, or security checker.', 'sage')],
        ['title' => __('Check your competitors too', 'sage'), 'text' => __('These work on any public URL. Grade a competitor\'s site to see where you can leapfrog them — often it\'s speed, mobile, or local SEO.', 'sage')],
        ['title' => __('Re-check after changes', 'sage'), 'text' => __('Made a fix? Run the tool again to confirm it worked. The scores update instantly, so you can see progress as you go.', 'sage')],
        ['title' => __('Focus on reds first', 'sage'), 'text' => __('A red “Fail” usually means a real problem a visitor or search engine hits today. Amber “Review” items are worth a look; greens are already working.', 'sage')],
        ['title' => __('Read the “why”', 'sage'), 'text' => __('Every check explains the business impact, not just the technical detail — so you can decide what\'s actually worth your time and money.', 'sage')],
        ['title' => __('Bring me the results', 'sage'), 'text' => __('Stuck on what a result means or how to fix it? Send it over. I translate these into a short, prioritized plan for local businesses every week.', 'sage')],
      ]) as $tip)
        <article class="rv-card rv-feature">
          <span class="rv-stripe rv-stripe-thin" aria-hidden="true"></span>
          <h3 class="rv-feature-title">{{ $tip['title'] ?? '' }}</h3>
          <p class="rv-feature-text">{{ $tip['text'] ?? '' }}</p>
        </article>
      @endforeach
    </div>
  </section>

  {{-- WHY FREE / LOCAL (SEO content) --}}
  <section class="rv-band rv-band-alt">
    <div class="rv-shell-full">
      <div class="rv-reading rv-prose">
        {!! \App\eyebrow(\App\field('whyfree_eyebrow', __('Why give these away?', 'sage'))) !!}
        <h2>{{ \App\field('whyfree_title', __('Built for local businesses, given away for free', 'sage')) }}</h2>
        <p>{{ \App\field('whyfree_p1', __('I\'m Matt Hummel, a Gettysburg-based web developer with 15+ years of experience, and I built these tools for the same reason I built this studio: most small businesses in Adams County are paying for websites that quietly underperform, and they have no easy way to see it. A clear, honest checkup shouldn\'t cost anything.', 'sage')) }}</p>
        <p>{{ \App\field('whyfree_p2', __('These tools are also the best way to understand how I work. They\'re fast, accessible, and jargon-free — exactly like the sites I build. If you like the way they explain things, you\'ll like working together. And if the results show your site is in good shape, wonderful; you\'ve lost nothing but a few minutes.', 'sage')) }}</p>
        <h2>{{ \App\field('whyfree_title2', __('What these tools can — and can\'t — tell you', 'sage')) }}</h2>
        <p>{{ \App\field('whyfree_p3', __('Automated checks are excellent at measuring the concrete things: whether you serve over HTTPS, whether your titles and meta descriptions are set, whether your pages are fast and mobile-ready, whether search engines are allowed to index them. They\'ll catch the majority of the quick, costly problems that hold a local site back.', 'sage')) }}</p>
        <p>{{ \App\field('whyfree_p4', __('What they can\'t judge is the human layer — whether your message lands, whether the path from visitor to phone call is obvious, whether your content answers the questions your customers actually ask, and the parts of accessibility that require real testing. A perfect technical score still loses if the site says the wrong thing to the wrong person. That\'s where an experienced set of eyes earns its keep, and it\'s the work I finish by hand on every project.', 'sage')) }}</p>
        <p>{{ \App\field('whyfree_close_before', __('When you\'re ready to turn a report into results, ', 'sage')) }}<a href="{{ home_url('/services/') }}">{{ \App\field('whyfree_close_services', __('take a look at the services', 'sage')) }}</a>{{ \App\field('whyfree_close_or', __(' or ', 'sage')) }}<a href="{{ $ctaHref }}">{{ \App\field('whyfree_close_contact', __('tell me about your business', 'sage')) }}</a>{{ \App\field('whyfree_close_after', __('. No pressure, no jargon — just a straight answer about what I\'d do and what it would cost.', 'sage')) }}</p>
      </div>
    </div>
  </section>

  {{-- FAQ --}}
  <section class="rv-shell rv-band">
    <div class="rv-reading">
      {!! \App\eyebrow(\App\field('tfaq_eyebrow', __('Common questions', 'sage'))) !!}
      <h2 class="rv-section-title" style="text-align:left">{{ \App\field('tfaq_title', __('Free tools,', 'sage')) }} <em class="rv-accent">{{ \App\field('tfaq_accent', __('answered.', 'sage')) }}</em></h2>
      <div class="rv-faq" style="margin-top:1.75rem">
        @foreach ($faqs as $f)
          <details class="rv-faq-item">
            <summary>{{ $f['q'] ?? '' }}</summary>
            <div class="rv-faq-answer"><p>{{ $f['a'] ?? '' }}</p></div>
          </details>
        @endforeach
      </div>
    </div>
  </section>

  {{-- CTA --}}
  <section class="rv-cta-band">
    <div class="rv-shell rv-cta-inner">
      <h2 class="rv-cta-title">{{ \App\field('cta_title', __('Want a hand with what the tools turned up?', 'sage')) }}</h2>
      <p class="rv-cta-sub">{{ \App\field('cta_sub', __('I fix these for Gettysburg and South Central PA businesses every week. Tell me your site and I\'ll take a real look.', 'sage')) }}</p>
      <a class="rv-btn rv-btn-on-dark" href="{{ $ctaHref }}">{{ \App\field('cta_button', get_theme_mod('rv_cta_text', __('Get a quote', 'sage'))) }}</a>
    </div>
  </section>

  {!! \App\faq_schema($faqs) !!}
@endsection
