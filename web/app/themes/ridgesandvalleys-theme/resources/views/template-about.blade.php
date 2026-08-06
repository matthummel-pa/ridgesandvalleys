{{--
  Template Name: About
--}}
@extends('layouts.app')

@section('content')
  @php($ctaHref = \App\cta_href(get_theme_mod('rv_cta_url', '/contact/')))

  {{-- HERO (standard full-bleed hero with the page's Featured Image behind it) --}}
  <section class="rv-hero">
    <span class="rv-stripe" aria-hidden="true"></span>
    @include('partials.hero-bg', ['fallback' => \App\stock_image('about')])
    <div class="rv-shell rv-hero-inner">
      {!! \App\eyebrow(\App\field('hero_eyebrow', __('Ridges & Valleys Studio', 'sage'))) !!}
      <h1 class="rv-hero-title">{{ \App\field('hero_title', __('A family-owned web design studio for', 'sage')) }} <em class="rv-accent">{{ \App\field('hero_accent', __('South Central PA.', 'sage')) }}</em></h1>
      <p class="rv-hero-sub">{{ \App\field('about_intro', __('We\'re a family-owned web studio in Gettysburg, building fast, accessible websites and local SEO for small businesses across Adams County and South Central PA — from Hanover and New Oxford to Littlestown, York, Chambersburg, and every town in between. If you\'re a local business, we\'d love to work with you.', 'sage')) }}</p>
      @php($aboutInvite = \App\field('about_invite', __('We\'re just as happy to team up with other marketing and design studios, freelancers, and photographers on a project. We\'re new to the neighborhood and all in — so come say hello, and let\'s build something good, right here at home.', 'sage')))
      @if ($aboutInvite)<p class="rv-hero-sub rv-hero-invite">{{ $aboutInvite }}</p>@endif
      <div class="rv-hero-actions">
        <a class="rv-btn rv-btn-primary" href="{{ \App\cta_href(\App\field('hero_btn_url', get_theme_mod('rv_cta_url', '/contact/'))) }}">{{ \App\field('hero_btn', __('Work with us', 'sage')) }}</a>
      </div>
      <p class="rv-hero-trust">{{ \App\field('about_meta', __('Family-owned · Accessibility-first · Serving Gettysburg & South Central PA', 'sage')) }}</p>
      <ul class="rv-about-proof" aria-label="{{ __('What you can count on', 'sage') }}">
        @foreach (\App\field_rows('about_proof', [
          ['v' => __('15+ yrs', 'sage'), 'l' => __('building for the web', 'sage')],
          ['v' => __('~7 days', 'sage'), 'l' => __('to your first draft', 'sage')],
          ['v' => __('WCAG 2.1 AA', 'sage'), 'l' => __('on every page', 'sage')],
          ['v' => __('You own it', 'sage'), 'l' => __('site, domain, hosting', 'sage')],
        ]) as $pf)
          <li><b>{{ $pf['v'] ?? '' }}</b><span>{{ $pf['l'] ?? '' }}</span></li>
        @endforeach
      </ul>
    </div>
  </section>

  {{-- THE STUDIO --}}
  <section class="rv-shell rv-band rv-about-bio">
    {!! \App\eyebrow(\App\field('bio_eyebrow', __('The studio', 'sage'))) !!}
    <h2 class="rv-section-title">{{ \App\field('bio_title', __('Small studio.', 'sage')) }} <em class="rv-accent">{{ \App\field('bio_accent', __('Serious craft.', 'sage')) }}</em></h2>
    <div class="rv-about-bio-grid">
      <div class="rv-reading rv-prose rv-about-bio-body">
        <p>{!! \App\field('bio_p1', __('<strong>Ridges & Valleys is an independent, family-owned studio.</strong> No account managers, no page-builder bloat, no months of meetings — just websites that load fast, read clearly, and help local businesses get found and get work.', 'sage')) !!}</p>
        <p>{!! \App\field('bio_p2', __('<strong>Every site is accessibility-first.</strong> Clean, standards-based WordPress, strong Core Web Vitals, and WCAG-minded builds, so the result works for everyone, on every device — and tends to rank better while it\'s at it.', 'sage')) !!}</p>
        <p>{!! \App\field('bio_p3', __('<strong>Modern tools for speed, human hands for the craft.</strong> AI helps with research and first drafts; the design decisions, the words, the accessibility, and the final build are done by a person. And when a project needs more than a website — a form that talks to your systems, an app, an automation — we can build that too.', 'sage')) !!}</p>
        <p>{!! \App\field('bio_p4', __('<strong>Fixed pricing, full ownership, no lock-in.</strong> You own your domain, your hosting, and your site. Take the keys and run it yourself, or keep us on a Care & Grow plan — either way, it\'s yours.', 'sage')) !!}</p>
      </div>
      <div class="rv-about-bio-side">
        @foreach (\App\field_rows('bio_side', [
          ['title' => __('Build', 'sage'), 'text' => __('Custom WordPress, no page-builder bloat — semantic, fast, and accessible.', 'sage')],
          ['title' => __('Get found', 'sage'), 'text' => __('Local SEO, Google Business Profile, and map-ready details baked in.', 'sage')],
          ['title' => __('Go further', 'sage'), 'text' => __('Power Platform apps and automations when a site needs to do more.', 'sage')],
        ]) as $hl)
          <div class="rv-about-hl">
            <h3>{{ $hl['title'] ?? '' }}</h3>
            <p>{{ $hl['text'] ?? '' }}</p>
          </div>
        @endforeach
      </div>
    </div>
  </section>

  {{-- MEET THE TEAM --}}
  <section class="rv-band rv-band-alt rv-about-team">
    <div class="rv-shell">
      {!! \App\eyebrow(\App\field('team_eyebrow', __('Who runs the studio', 'sage'))) !!}
      <h2 class="rv-section-title">{{ \App\field('team_title', __('A small,', 'sage')) }} <em class="rv-accent">{{ \App\field('team_accent', __('family team.', 'sage')) }}</em></h2>
      <p class="rv-page-intro">{{ \App\field('team_intro', __('Ridges & Valleys is family-owned and run — small on purpose, so every project gets real attention from the people whose name is on it.', 'sage')) }}</p>

      <div class="rv-team-grid">
        @foreach (\App\field_rows('team_members', [
          ['name' => __('Matt Hummel', 'sage'), 'role' => __('Founder & Lead Developer', 'sage'), 'bio' => __('Fifteen years building for the web — first as a WordPress developer in a university marketing department, now splitting his days between the Microsoft Power Platform and the studio. A problem-solver first and a developer second: accessibility-first WordPress, performance, local SEO, and the occasional app or automation. Gettysburg-based, and here to stay.', 'sage'), 'photo' => ''],
          ['name' => '', 'role' => __('Studio Assistant & Client Care', 'sage'), 'bio' => __('Keeps projects moving and clients looked after — scheduling, communication, and the behind-the-scenes details that keep every build on track and on time.', 'sage'), 'photo' => ''],
        ]) as $m)
          @php($name = trim($m['name'] ?? ''))
          <article class="rv-team-card">
            @if (!empty($m['photo']))
              <img class="rv-team-photo" src="{{ $m['photo'] }}" alt="{{ $name }}" loading="lazy" onerror="this.style.display='none'">
            @else
              <span class="rv-team-avatar" aria-hidden="true"><svg viewBox="0 0 24 24" fill="currentColor" focusable="false"><circle cx="12" cy="8.2" r="4.2"/><path d="M3.8 20.5a8.2 8.2 0 0 1 16.4 0z"/></svg></span>
            @endif
            @if ($name !== '')<h3 class="rv-team-name">{{ $name }}</h3>@endif
            @if (!empty($m['role']))<p class="rv-team-role">{{ $m['role'] }}</p>@endif
            @if (!empty($m['bio']))<p class="rv-team-bio">{{ $m['bio'] }}</p>@endif
          </article>
        @endforeach
      </div>

      @php($teamNote = \App\field('team_note', __('As the studio grows, we bring in trusted local specialists — photographers, writers, and more — project by project.', 'sage')))
      @if ($teamNote)<p class="rv-post-meta rv-about-team-note">{{ $teamNote }}</p>@endif
    </div>
  </section>

  {{-- HOW WE WORK / BELIEFS --}}
  <section class="rv-shell rv-band">
    {!! \App\eyebrow(\App\field('beliefs_eyebrow', __('How we work', 'sage'))) !!}
    <h2 class="rv-section-title">{{ \App\field('beliefs_title', __('A few things we', 'sage')) }} <em class="rv-accent">{{ \App\field('beliefs_accent', __('believe.', 'sage')) }}</em></h2>
    <div class="rv-grid rv-grid-3" style="margin-top:2rem">
      @foreach (\App\field_rows('beliefs_items', [
        ['title' => __('Outcomes over features', 'sage'), 'text' => __('Your customers don\'t care about the tech. They care about finding you, calling you, booking you.', 'sage')],
        ['title' => __('Accessible by default', 'sage'), 'text' => __('A fast, accessible site isn\'t a luxury — it\'s your front door. Everyone should get through it.', 'sage')],
        ['title' => __('Local and accountable', 'sage'), 'text' => __('You get real people in the area, not a ticket queue — proud of the place and the history that comes with it.', 'sage')],
      ]) as $b)
        <article class="rv-card rv-feature">
          <span class="rv-stripe rv-stripe-thin" aria-hidden="true"></span>
          <h3 class="rv-feature-title">{{ $b['title'] ?? '' }}</h3>
          <p class="rv-feature-text">{{ $b['text'] ?? '' }}</p>
        </article>
      @endforeach
    </div>
  </section>

  {{-- WHAT'S UNDER THE HOOD / CREDENTIALS --}}
  <section class="rv-band rv-band-alt">
    <div class="rv-shell">
      {!! \App\eyebrow(\App\field('creds_eyebrow', __('What\'s under the hood', 'sage'))) !!}
      <h2 class="rv-section-title">{{ \App\field('creds_title', __('Built on real', 'sage')) }} <em class="rv-accent">{{ \App\field('creds_accent', __('range.', 'sage')) }}</em></h2>
      <div class="rv-grid rv-grid-3" style="margin-top:2rem">
        @foreach (\App\field_rows('creds_items', [
          ['title' => __('WordPress, done right', 'sage'), 'text' => __('Custom themes and clean, page-builder-free builds — the platform that keeps you in control of your own site.', 'sage')],
          ['title' => __('Accessibility & front-end', 'sage'), 'text' => __('WCAG 2.1 AA, semantic HTML, performance, and modern CSS/JavaScript that holds up on real devices.', 'sage')],
          ['title' => __('Business platforms', 'sage'), 'text' => __('Experience with the Microsoft Power Platform and integrations — useful when a website needs to talk to the rest of your operation.', 'sage')],
        ]) as $sk)
          <article class="rv-card rv-feature">
            <span class="rv-stripe rv-stripe-thin" aria-hidden="true"></span>
            <h3 class="rv-feature-title">{{ $sk['title'] ?? '' }}</h3>
            <p class="rv-feature-text">{{ $sk['text'] ?? '' }}</p>
          </article>
        @endforeach
      </div>
    </div>
  </section>

  {{-- ROOTED LOCALLY --}}
  <section class="rv-shell rv-band rv-about-local">
    <div class="rv-about-local-grid">
      <div>
        {!! \App\eyebrow(\App\field('local_eyebrow', __('Gettysburg & Adams County', 'sage'))) !!}
        <h2 class="rv-about-local-h">{{ \App\field('local_title', __('A studio that knows', 'sage')) }} <em class="rv-accent">{{ \App\field('local_accent', __('the ground.', 'sage')) }}</em></h2>
        <p class="rv-feature-text">{!! \App\field('local_p1', __('<strong>Based right here in Gettysburg</strong> — minutes from Lincoln Square and the edge of the National Military Park. That local knowledge shapes every build: we know the difference between the summer tourist rush on Steinwehr Avenue and a year-round Main Street shop up in Biglerville, and we build your site to speak to the customers you actually get.', 'sage')) !!}</p>
        <p class="rv-feature-text">{!! \App\field('local_p2', __('<strong>This isn\'t a market picked off a map.</strong> After fifteen years away in Virginia, our founder moved his family home to Pennsylvania and put down roots in the Gettysburg area — it\'s where the family lives, and where the studio is staying. When we say we want local businesses here to do well, we mean it about our own neighbors.', 'sage')) !!}</p>
        <p class="rv-feature-text">{!! \App\field('local_p3', __('<strong>South Central PA runs on small business</strong> — from the orchards of the Adams County fruit belt to the inns, taverns, and tour companies downtown. We build fast, accessible, mobile-first websites that win local search, so when someone nearby reaches for their phone to decide where to eat, stay, shop, or call, you\'re the one they find.', 'sage')) !!}</p>
        <div class="rv-hero-actions" style="justify-content:flex-start;margin-top:1.25rem">
          <a class="rv-btn rv-btn-primary" href="{{ $ctaHref }}">{{ \App\field('local_button', __('Get found in Gettysburg', 'sage')) }}</a>
        </div>
      </div>
      <div class="rv-about-highlights">
        @foreach (\App\field_rows('local_highlights', [
          ['title' => __('Real people, not a ticket queue', 'sage'), 'text' => __('Locals you can actually reach — real meetings across Adams County and the towns around it.', 'sage')],
          ['title' => __('Built for how people search here', 'sage'), 'text' => __('Google Business Profile, local SEO, and map-ready details so you show up for the “near me” searches that matter.', 'sage')],
          ['title' => __('Tourists and locals, covered', 'sage'), 'text' => __('Sites tuned for both the visitor economy and the neighbors who keep you busy all year.', 'sage')],
        ]) as $h)
          <div class="rv-about-hl">
            <h3>{{ $h['title'] ?? '' }}</h3>
            <p>{{ $h['text'] ?? '' }}</p>
          </div>
        @endforeach
      </div>
    </div>
  </section>

  {{-- WHO WE BUILD FOR + AREAS SERVED --}}
  <section class="rv-shell rv-band rv-about-serve">
    {!! \App\eyebrow(\App\field('serve_eyebrow', __('Local businesses', 'sage'))) !!}
    <h2 class="rv-section-title">{{ \App\field('serve_title', __('Made for South Central PA', 'sage')) }} <em class="rv-accent">{{ \App\field('serve_accent', __('Main Street.', 'sage')) }}</em></h2>
    <p class="rv-page-intro">{{ \App\field('serve_intro', __('Every kind of local business needs a website that gets found and earns trust. These are the ones we love building for around Gettysburg and Adams County:', 'sage')) }}</p>

    <div class="rv-about-ind">
      @foreach (\App\field_rows('serve_items', [
        ['title' => __('Inns & B&Bs', 'sage'), 'text' => __('Direct bookings and a first impression worth the drive.', 'sage')],
        ['title' => __('Restaurants & taverns', 'sage'), 'text' => __('Menus, hours, and reservations that stay current.', 'sage')],
        ['title' => __('Farm markets & orchards', 'sage'), 'text' => __('Seasons, hours, and pick-your-own, front and center.', 'sage')],
        ['title' => __('Tour operators', 'sage'), 'text' => __('Book-and-pay flows for the battlefield and beyond.', 'sage')],
        ['title' => __('Shops & boutiques', 'sage'), 'text' => __('A downtown storefront that also sells online.', 'sage')],
        ['title' => __('Trades & contractors', 'sage'), 'text' => __('Quote requests and service areas done right.', 'sage')],
        ['title' => __('Realtors & agencies', 'sage'), 'text' => __('Listings and lead capture that keep buyers on your site.', 'sage')],
        ['title' => __('Nonprofits & churches', 'sage'), 'text' => __('Clear, accessible sites that welcome everyone.', 'sage')],
      ]) as $ind)
        <article>
          <h3>{{ $ind['title'] ?? '' }}</h3>
          <p>{{ $ind['text'] ?? '' }}</p>
        </article>
      @endforeach
    </div>

    <div class="rv-about-areas-wrap">
      {!! \App\eyebrow(\App\field('serve_areas_eyebrow', __('Areas served', 'sage'))) !!}
      <p class="rv-feature-text" style="margin-top:.5rem">{{ \App\field('serve_areas_intro', __('Based in Gettysburg, working with businesses across:', 'sage')) }}</p>
      <div class="rv-about-towns" aria-label="{{ __('Towns served', 'sage') }}">
        @foreach (\App\field_lines('serve_towns', ['Gettysburg', 'Biglerville', 'Littlestown', 'New Oxford', 'McSherrystown', 'Bonneauville', 'Abbottstown', 'Fairfield', 'Cashtown', 'Arendtsville', 'East Berlin', 'York Springs', 'Aspers', 'Hanover']) as $t)<span class="rv-about-town">{{ $t }}</span>@endforeach
      </div>
      <p class="rv-post-meta" style="margin-top:1.25rem">{{ \App\field('serve_note', __('Plus the surrounding townships and small businesses throughout Adams, York, and Franklin counties. Not in the neighborhood? Most of our work happens over a call and a shared screen — distance is rarely a dealbreaker.', 'sage')) }}</p>
    </div>
  </section>

  {{-- ONE-LINE QUOTE --}}
  <section class="rv-band rv-band-pine">
    <div class="rv-shell">
      <div class="rv-quote-block">
        <p class="rv-quote-q" style="color:#fff">{{ \App\field('quote_text', __('“The craft is human. The busywork isn\'t.”', 'sage')) }}</p>
        <p class="rv-quote-who">{{ \App\field('quote_who', __('The studio, in one line', 'sage')) }}</p>
      </div>
    </div>
  </section>

  {{-- TRY US BEFORE YOU HIRE US: THE SIX FREE TOOLS --}}
  <section class="rv-shell rv-band rv-about-tools">
    {!! \App\eyebrow(\App\field('tools_eyebrow', __('Try us before you hire us', 'sage'))) !!}
    <h2 class="rv-section-title">{{ \App\field('tools_title', __('Six free tools.', 'sage')) }} <em class="rv-accent">{{ \App\field('tools_accent', __('No account, no email.', 'sage')) }}</em></h2>
    <p class="rv-page-intro">{{ \App\field('tools_intro', __('Every checker on this site is free and open to anyone. Put in your address, get a plain-English report card, and fix what you like — with us or without us. It is the easiest way to see how we think before you spend a dollar.', 'sage')) }}</p>
    <div class="rv-about-ind">
      @foreach (\App\field_rows('tools_items', [
        ['title' => __('Website Grader', 'sage'), 'url' => '/website-grader/', 'text' => __('Your whole site graded across seven areas, so you know what to fix first.', 'sage')],
        ['title' => __('SEO Checker', 'sage'), 'url' => '/seo-checker/', 'text' => __('Shows exactly why you are — or are not — showing up in search.', 'sage')],
        ['title' => __('Accessibility Checker', 'sage'), 'url' => '/accessibility/', 'text' => __('Scans a page against WCAG 2.1 AA so more customers can use your site.', 'sage')],
        ['title' => __('Security Checker', 'sage'), 'url' => '/security-checker/', 'text' => __('HTTPS, headers, and leaks — the things that protect your customers\' trust.', 'sage')],
        ['title' => __('Email Deliverability Checker', 'sage'), 'url' => '/email-checker/', 'text' => __('SPF, DKIM, and DMARC, so your invoices stop landing in junk.', 'sage')],
        ['title' => __('Local SEO Scorecard', 'sage'), 'url' => '/local-seo/', 'text' => __('Scores the local signals that get you into Google\'s map pack.', 'sage')],
      ]) as $tl)
        <article>
          <h3><a href="{{ home_url($tl['url'] ?? '/free-tools/') }}">{{ $tl['title'] ?? '' }}</a></h3>
          <p>{{ $tl['text'] ?? '' }}</p>
        </article>
      @endforeach
    </div>
    <p class="rv-about-tools-cta">
      <a class="rv-btn rv-btn-ghost" href="{{ home_url('/free-tools/') }}">{{ \App\field('tools_button', __('See all six free tools', 'sage')) }}</a>
    </p>
  </section>

  {{-- HOW PRICING WORKS + HOW TO REACH US (NAP) --}}
  <section class="rv-band rv-band-alt rv-about-reach">
    <div class="rv-shell rv-about-reach-grid">
      <div>
        {!! \App\eyebrow(\App\field('price_eyebrow', __('How pricing works', 'sage'))) !!}
        <h2 class="rv-about-local-h">{{ \App\field('price_title', __('One fixed price,', 'sage')) }} <em class="rv-accent">{{ \App\field('price_accent', __('agreed up front.', 'sage')) }}</em></h2>
        <p class="rv-feature-text">{!! \App\field('price_p1', __('<strong>No hourly meter, no surprise invoices.</strong> You get the number before the work starts, and it does not move unless you ask for something new — and then we agree on that price first, too.', 'sage')) !!}</p>
        <ul class="rv-about-prices">
          @foreach (\App\field_rows('price_items', [
            ['title' => __('Website Rescue', 'sage'), 'text' => __('$950–$1,500 — fix what you already have.', 'sage')],
            ['title' => __('Local Launch', 'sage'), 'text' => __('$2,750–$3,750 — a new site built to get found locally.', 'sage')],
            ['title' => __('Growth Site', 'sage'), 'text' => __('$4,500+ — bigger builds with more pages and moving parts.', 'sage')],
            ['title' => __('Care & Grow', 'sage'), 'text' => __('$179–$349/mo — updates, backups, and steady improvements.', 'sage')],
          ]) as $pr)
            <li><b>{{ $pr['title'] ?? '' }}</b> <span>{{ $pr['text'] ?? '' }}</span></li>
          @endforeach
        </ul>
      </div>
      <div>
        {!! \App\eyebrow(\App\field('reach_eyebrow', __('How to reach us', 'sage'))) !!}
        <h2 class="rv-about-local-h">{{ \App\field('reach_title', __('Talk to a', 'sage')) }} <em class="rv-accent">{{ \App\field('reach_accent', __('real person.', 'sage')) }}</em></h2>
        @php($rvPhone = trim(get_theme_mod('rv_contact_phone', '223-340-8098')))
        @php($rvEmail = trim(get_theme_mod('rv_contact_email', 'matt@ridgesandvalleys.com')))
        @php($rvLocation = trim(get_theme_mod('rv_contact_location', 'Gettysburg, PA')))
        <ul class="rv-about-nap">
          <li><span class="rv-about-nap-l">{{ __('Studio', 'sage') }}</span> <b>{{ __('Ridges & Valleys Studio', 'sage') }}</b></li>
          @if ($rvPhone)<li><span class="rv-about-nap-l">{{ __('Phone', 'sage') }}</span> <a href="tel:+1{{ preg_replace('/\D+/', '', $rvPhone) }}">{{ $rvPhone }}</a></li>@endif
          @if ($rvEmail)<li><span class="rv-about-nap-l">{{ __('Email', 'sage') }}</span> <a href="mailto:{{ $rvEmail }}">{{ $rvEmail }}</a></li>@endif
          @if ($rvLocation)<li><span class="rv-about-nap-l">{{ __('Based in', 'sage') }}</span> <b>{{ $rvLocation }}</b></li>@endif
          <li><span class="rv-about-nap-l">{{ __('Serving', 'sage') }}</span> <b>{{ __('Adams County & South Central PA', 'sage') }}</b></li>
        </ul>
        <p class="rv-post-meta rv-about-nap-note">{{ \App\field('reach_note', __('We work from a home studio and meet clients at their place of business, so we do not publish a street address. Call, email, or ask for a free quote — you will hear back from Matt, not a call center.', 'sage')) }}</p>
        <div class="rv-hero-actions" style="justify-content:flex-start;margin-top:1.1rem">
          <a class="rv-btn rv-btn-primary" href="{{ $ctaHref }}">{{ \App\field('reach_button', __('Get a free quote', 'sage')) }}</a>
        </div>
      </div>
    </div>
  </section>

  @include('partials.cta')

  <style>
    .rv-hero-invite{font-size:1.02rem;opacity:.9;margin-top:.6rem}
    /* Proof strip (hero) */
    .rv-about-proof{list-style:none;display:flex;flex-wrap:wrap;gap:.6rem .75rem;margin:1.1rem 0 0;padding:0}
    .rv-about-proof li{display:flex;flex-direction:column;gap:.1rem;background:color-mix(in srgb,#fff 12%,transparent);border:1px solid color-mix(in srgb,#fff 26%,transparent);border-radius:12px;padding:.55rem .9rem}
    .rv-about-proof b{font-family:var(--font-display);font-size:1.02rem;font-weight:800;line-height:1.1}
    .rv-about-proof span{font-family:var(--font-mono);font-size:.68rem;letter-spacing:.1em;text-transform:uppercase;opacity:.85}
    /* Free tools band */
    .rv-about-tools .rv-about-ind h3 a{color:inherit;text-decoration:none}
    .rv-about-tools .rv-about-ind h3 a:hover{text-decoration:underline}
    .rv-about-tools-cta{margin:1.5rem 0 0}
    /* Pricing + NAP band */
    .rv-about-reach-grid{display:grid;grid-template-columns:1fr 1fr;gap:clamp(2rem,4vw,3.5rem);align-items:start}
    @media(max-width:820px){.rv-about-reach-grid{grid-template-columns:1fr}}
    .rv-about-prices{list-style:none;margin:1.25rem 0 0;padding:0;display:grid;gap:.7rem}
    .rv-about-prices li{position:relative;padding:.15rem 0 .15rem .95rem}
    .rv-about-prices li::before{content:"";position:absolute;left:0;top:.15rem;bottom:.15rem;width:4px;border-radius:2px;background:var(--ridgeline)}
    .rv-about-prices b{font-family:var(--font-display);color:var(--color-ink);display:block;font-size:1.02rem}
    .rv-about-prices span{color:var(--color-body);font-size:.95rem;line-height:1.5}
    .rv-about-nap{list-style:none;margin:1.25rem 0 0;padding:0;display:grid;gap:.6rem}
    .rv-about-nap li{display:grid;grid-template-columns:6.5rem 1fr;gap:.75rem;align-items:baseline;font-size:1rem;color:var(--color-ink)}
    .rv-about-nap-l{font-family:var(--font-mono);font-size:.68rem;letter-spacing:.11em;text-transform:uppercase;color:var(--color-clay);font-weight:600}
    .rv-about-nap a{color:var(--color-ink)}
    .rv-about-nap-note{margin-top:1.1rem;max-width:52ch}
    @media(max-width:480px){.rv-about-nap li{grid-template-columns:1fr;gap:.15rem}}
    .rv-about-local-grid{display:grid;grid-template-columns:1.25fr .9fr;gap:clamp(2rem,4vw,3.5rem);align-items:start}
    @media(max-width:820px){.rv-about-local-grid{grid-template-columns:1fr}}
    .rv-about-local-h{font-family:var(--font-display);font-weight:800;font-size:clamp(1.9rem,4.4vw,3.1rem);line-height:1.04;letter-spacing:-.02em;color:var(--color-ink);margin:.45rem 0 1.1rem}
    .rv-about-local .rv-feature-text{font-size:1.08rem;line-height:1.7;color:var(--color-body);margin:0 0 1rem;max-width:60ch}
    .rv-about-highlights{display:grid;gap:1rem}
    .rv-about-hl{position:relative;background:var(--color-surface);border:1px solid var(--color-line);border-radius:var(--radius-lg,16px);padding:1.2rem 1.35rem 1.2rem 1.6rem;overflow:hidden}
    .rv-about-hl::before{content:"";position:absolute;left:0;top:0;bottom:0;width:5px;background:var(--ridgeline)}
    .rv-about-hl h3{font-family:var(--font-display);font-size:1.1rem;font-weight:700;color:var(--color-ink);margin:0 0 .35rem}
    .rv-about-hl p{margin:0;color:var(--color-body);font-size:.95rem;line-height:1.55}
    .rv-about-ind{display:grid;grid-template-columns:repeat(auto-fit,minmax(230px,1fr));gap:1rem;margin-top:2rem}
    .rv-about-ind article{background:var(--color-surface);border:1px solid var(--color-line);border-radius:var(--radius-lg,16px);padding:1.2rem 1.35rem;transition:border-color .15s ease,transform .15s ease}
    .rv-about-ind article:hover{border-color:var(--color-sage);transform:translateY(-2px)}
    .rv-about-ind h3{font-family:var(--font-display);font-size:1.05rem;font-weight:700;color:var(--color-ink);margin:0 0 .3rem}
    .rv-about-ind p{margin:0;color:var(--color-muted);font-size:.92rem;line-height:1.5}
    .rv-about-areas-wrap{margin-top:2.75rem;padding-top:2rem;border-top:1px solid var(--color-line)}
    .rv-about-towns{display:flex;flex-wrap:wrap;gap:.55rem;margin:1rem 0 0}
    .rv-about-town{background:var(--color-surface);border:1px solid var(--color-line);border-radius:999px;padding:.45rem .9rem;font-weight:600;font-size:.9rem;color:var(--color-ink)}
    .rv-about-town:hover{border-color:var(--color-sage)}
    .rv-about-bio-grid{display:grid;grid-template-columns:1.4fr .8fr;gap:clamp(2rem,4vw,3.5rem);align-items:start;margin-top:1.75rem}
    @media(max-width:820px){.rv-about-bio-grid{grid-template-columns:1fr}}
    .rv-about-bio-body{max-width:none;margin:0;padding-bottom:0;font-size:1.08rem;line-height:1.75}
    .rv-about-bio-body p{margin:0 0 1.1rem}
    .rv-about-bio-side{display:grid;gap:1rem}
    /* Team */
    .rv-about-team .rv-page-intro{max-width:60ch}
    .rv-team-grid{display:grid;grid-template-columns:repeat(auto-fit,minmax(280px,1fr));gap:1.25rem;margin-top:2rem}
    .rv-team-card{background:var(--color-surface);border:1px solid var(--color-line);border-radius:var(--radius-lg,16px);padding:1.6rem 1.5rem;text-align:center}
    .rv-team-photo{width:96px;height:96px;border-radius:50%;object-fit:cover;margin:0 auto .9rem;display:block;border:3px solid var(--color-line)}
    .rv-team-avatar{width:96px;height:96px;border-radius:50%;margin:0 auto .9rem;display:flex;align-items:center;justify-content:center;background:color-mix(in srgb,var(--color-sage) 22%,var(--color-surface));border:3px solid var(--color-line);color:color-mix(in srgb,var(--color-ink) 45%,transparent)}
    .rv-team-avatar svg{width:58px;height:58px;display:block}
    .rv-team-name{font-family:var(--font-display);font-size:1.3rem;font-weight:800;color:var(--color-ink);margin:0}
    .rv-team-role{font-family:var(--font-mono);font-size:.72rem;letter-spacing:.12em;text-transform:uppercase;color:var(--color-clay);font-weight:600;margin:.35rem 0 .8rem}
    .rv-team-bio{color:var(--color-body);font-size:.98rem;line-height:1.6;margin:0}
    .rv-about-team-note{text-align:center;margin-top:1.75rem}
  </style>
@endsection
