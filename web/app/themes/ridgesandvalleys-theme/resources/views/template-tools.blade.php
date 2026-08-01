{{--
  Template Name: Free Tools
--}}
@extends('layouts.app')

@php
$faqs = [
  [__('Are these tools really free?', 'sage'), __('Yes — completely. There\'s no account to create, no email to hand over, and no credit card. Run them as often as you like, on any site you like.', 'sage')],
  [__('Do I have to give you my email or sign up?', 'sage'), __('No. Enter a URL (and, for the SEO checker, an optional keyword) and you get your results on the spot. Nothing is gated behind a form.', 'sage')],
  [__('How accurate are the results?', 'sage'), __('The URL tools read your page\'s actual code and its server responses, so they\'re reliable for the things a machine can measure — HTTPS, meta tags, headings, security headers, and the like. Judgment calls like design, message clarity, and deep accessibility still need a human. That\'s the part I finish by hand.', 'sage')],
  [__('What\'s the difference between the Website Grader and the SEO Checker?', 'sage'), __('The Website Grader is a broad, seven-area report card — a quick overall health check. The SEO Checker goes deep on search specifically, with a Google snippet preview, keyword analysis, crawlability (robots.txt and sitemap), and structured data. Use the grader for the big picture, the SEO checker to dig into rankings.', 'sage')],
  [__('Will you try to sell me something?', 'sage'), __('Only if you ask. The tools work with no strings attached. If you\'d like the issues fixed for you, I\'m happy to help — but that\'s your call, not a catch.', 'sage')],
  [__('Can you fix what the tools find?', 'sage'), __('Yes. Send me your URL and I\'ll turn the results into a short, prioritized plan in plain English — and I can do the work, from a quick Website Rescue to a full rebuild.', 'sage')],
  [__('Do you work with businesses in my town?', 'sage'), __('I serve Gettysburg, Adams County, and the surrounding South Central PA area — Hanover, Littlestown, New Oxford, McSherrystown, Biglerville, Fairfield, and nearby. If you\'re local, we\'re a fit.', 'sage')],
  [__('Do the tools store my website\'s data?', 'sage'), __('They fetch the page you enter in order to analyze it, and they don\'t save your results. Each run is a fresh, on-the-spot check.', 'sage')],
];
@endphp

@section('content')
  @php($ctaHref = \App\cta_href(get_theme_mod('rv_cta_url', '/contact/')))

  {{-- HERO --}}
  <section class="rv-hero">
    <span class="rv-stripe" aria-hidden="true"></span>
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
      @while(have_posts()) @php(the_post())
        @if (trim(get_the_content()))
          @php(the_content())
        @else
          <p>{{ __('Your website is working for you around the clock — but is it actually doing its job? Most small-business owners have no easy way to tell whether their site loads fast enough, shows up on Google, works on a phone, or keeps visitors\' information safe. These free tools answer those questions in plain English, so you can see exactly where your site stands before you spend a dollar fixing it.', 'sage') }}</p>
          <p>{{ __('There\'s no catch. Every tool below runs instantly in your browser or against your live page — no account, no email, no sales call required. Enter your address, read your results, and decide what to do next. If you\'d like a hand with what turns up, that\'s what the studio is here for.', 'sage') }}</p>
        @endif
      @endwhile
    </div>
  </section>

  {{-- THE CHECKERS --}}
  <section id="tools" class="rv-shell rv-band">
    {!! \App\eyebrow(__('The checkers', 'sage')) !!}
    <h2 class="rv-section-title">{{ __('Six instant', 'sage') }} <em class="rv-accent">{{ __('report cards.', 'sage') }}</em></h2>
    <p class="rv-page-intro">{{ __('Enter your URL and get a clear, color-coded grade with every check explained — and why it matters for your business.', 'sage') }}</p>
    <div class="rv-grid rv-grid-3" style="margin-top:2rem">
      @php($checkers = [
        [__('Most popular', 'sage'), __('Website Grader', 'sage'), '/website-grader/', __('Your whole site, graded across seven areas — SEO, page speed, mobile, readability, security, technical health, and social sharing.', 'sage'), __('The fastest way to see where you stand and what to fix first.', 'sage')],
        ['', __('SEO Checker', 'sage'), '/seo-checker/', __('A deep search audit with a live Google snippet preview, target-keyword analysis, crawlability (robots.txt + sitemap), and structured data.', 'sage'), __('Shows exactly why you are — or aren\'t — showing up in search.', 'sage')],
        ['', __('Accessibility Checker', 'sage'), '/accessibility/', __('Scan any page against WCAG 2.1 AA — the standard the federal rules point to — with a live, running checklist.', 'sage'), __('Reach more customers and lower your legal risk.', 'sage')],
        ['', __('Security Checker', 'sage'), '/security-checker/', __('HTTPS and HSTS, the modern browser security headers, cookie safety, information leaks, and front-end risks.', 'sage'), __('Protect your customers\' trust — and your reputation.', 'sage')],
        ['', __('Email Deliverability Checker', 'sage'), '/email-checker/', __('Checks the DNS records — SPF, DKIM, and DMARC — that decide whether your email is trusted or lands in spam.', 'sage'), __('Stop your invoices landing in junk, and stop scammers spoofing you.', 'sage')],
        ['', __('Local SEO Scorecard', 'sage'), '/local-seo/', __('Scores the local signals — NAP, LocalBusiness schema, maps, and reviews — that get you into Google\'s map pack.', 'sage'), __('Where much of a local business\'s real traffic comes from.', 'sage')],
      ])
      @foreach ($checkers as $t)
        <a class="rv-card rv-feature rv-toolcard" href="{{ home_url($t[2]) }}">
          <span class="rv-stripe rv-stripe-thin" aria-hidden="true"></span>
          @if ($t[0])<span class="rv-toolcard-badge">{{ $t[0] }}</span>@endif
          <h3 class="rv-feature-title">{{ $t[1] }}</h3>
          <p class="rv-feature-text">{{ $t[3] }}</p>
          <p class="rv-toolcard-why"><b>{{ __('Why it matters:', 'sage') }}</b> {{ $t[4] }}</p>
          <span class="rv-readmore">{{ __('Open the tool', 'sage') }} {!! \App\icon('arrow') !!}</span>
        </a>
      @endforeach
    </div>
  </section>

  {{-- QUICK TOOLS (embedded, client-side) --}}
  <section class="rv-band rv-band-alt">
    <div class="rv-shell">
      {!! \App\eyebrow(__('Quick tools', 'sage')) !!}
      <h2 class="rv-section-title">{{ __('Handy calculators &', 'sage') }} <em class="rv-accent">{{ __('checks.', 'sage') }}</em></h2>
      <p class="rv-page-intro">{{ __('No URL needed — these run right here on the page. Test a color combination, ballpark a project, or estimate a care plan.', 'sage') }}</p>
      <div class="rv-tools-grid" style="margin-top:2rem">
        {!! \App\rv_render_contrast() !!}
        {!! \App\rv_render_estimator() !!}
        {!! \App\rv_render_calculator() !!}
      </div>
    </div>
  </section>

  {{-- HOW TO GET THE MOST --}}
  <section class="rv-shell rv-band">
    {!! \App\eyebrow(__('Getting the most from them', 'sage')) !!}
    <h2 class="rv-section-title">{{ __('How to use these', 'sage') }} <em class="rv-accent">{{ __('tools.', 'sage') }}</em></h2>
    <div class="rv-grid rv-grid-3" style="margin-top:2rem">
      @php($tips = [
        [__('Start with the grader', 'sage'), __('Run the Website Grader first for the big picture, then dig into whichever area scored lowest with the dedicated SEO, accessibility, or security checker.', 'sage')],
        [__('Check your competitors too', 'sage'), __('These work on any public URL. Grade a competitor\'s site to see where you can leapfrog them — often it\'s speed, mobile, or local SEO.', 'sage')],
        [__('Re-check after changes', 'sage'), __('Made a fix? Run the tool again to confirm it worked. The scores update instantly, so you can see progress as you go.', 'sage')],
        [__('Focus on reds first', 'sage'), __('A red “Fail” usually means a real problem a visitor or search engine hits today. Amber “Review” items are worth a look; greens are already working.', 'sage')],
        [__('Read the “why”', 'sage'), __('Every check explains the business impact, not just the technical detail — so you can decide what\'s actually worth your time and money.', 'sage')],
        [__('Bring me the results', 'sage'), __('Stuck on what a result means or how to fix it? Send it over. I translate these into a short, prioritized plan for local businesses every week.', 'sage')],
      ])
      @foreach ($tips as $tip)
        <article class="rv-card rv-feature">
          <span class="rv-stripe rv-stripe-thin" aria-hidden="true"></span>
          <h3 class="rv-feature-title">{{ $tip[0] }}</h3>
          <p class="rv-feature-text">{{ $tip[1] }}</p>
        </article>
      @endforeach
    </div>
  </section>

  {{-- WHY FREE / LOCAL (SEO content) --}}
  <section class="rv-band rv-band-alt">
    <div class="rv-shell-full">
      <div class="rv-reading rv-prose">
        {!! \App\eyebrow(__('Why give these away?', 'sage')) !!}
        <h2>{{ __('Built for local businesses, given away for free', 'sage') }}</h2>
        <p>{{ __('I\'m Matt Hummel, a Gettysburg-based web developer with 15+ years of experience, and I built these tools for the same reason I built this studio: most small businesses in Adams County are paying for websites that quietly underperform, and they have no easy way to see it. A clear, honest checkup shouldn\'t cost anything.', 'sage') }}</p>
        <p>{{ __('These tools are also the best way to understand how I work. They\'re fast, accessible, and jargon-free — exactly like the sites I build. If you like the way they explain things, you\'ll like working together. And if the results show your site is in good shape, wonderful; you\'ve lost nothing but a few minutes.', 'sage') }}</p>
        <h2>{{ __('What these tools can — and can\'t — tell you', 'sage') }}</h2>
        <p>{{ __('Automated checks are excellent at measuring the concrete things: whether you serve over HTTPS, whether your titles and meta descriptions are set, whether your pages are fast and mobile-ready, whether search engines are allowed to index them. They\'ll catch the majority of the quick, costly problems that hold a local site back.', 'sage') }}</p>
        <p>{{ __('What they can\'t judge is the human layer — whether your message lands, whether the path from visitor to phone call is obvious, whether your content answers the questions your customers actually ask, and the parts of accessibility that require real testing. A perfect technical score still loses if the site says the wrong thing to the wrong person. That\'s where an experienced set of eyes earns its keep, and it\'s the work I finish by hand on every project.', 'sage') }}</p>
        <p>{{ __('When you\'re ready to turn a report into results, ', 'sage') }}<a href="{{ home_url('/services/') }}">{{ __('take a look at the services', 'sage') }}</a>{{ __(' or ', 'sage') }}<a href="{{ $ctaHref }}">{{ __('tell me about your business', 'sage') }}</a>{{ __('. No pressure, no jargon — just a straight answer about what I\'d do and what it would cost.', 'sage') }}</p>
      </div>
    </div>
  </section>

  {{-- FAQ --}}
  <section class="rv-shell rv-band">
    <div class="rv-reading">
      {!! \App\eyebrow(__('Common questions', 'sage')) !!}
      <h2 class="rv-section-title" style="text-align:left">{{ __('Free tools,', 'sage') }} <em class="rv-accent">{{ __('answered.', 'sage') }}</em></h2>
      <div class="rv-faq" style="margin-top:1.75rem">
        @foreach ($faqs as $f)
          <details class="rv-faq-item">
            <summary>{{ $f[0] }}</summary>
            <div class="rv-faq-answer"><p>{{ $f[1] }}</p></div>
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
