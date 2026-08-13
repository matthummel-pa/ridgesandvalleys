{{--
  Template Name: About
--}}
@extends('layouts.app')

@section('content')
  @php($ctaHref = \App\cta_href(get_theme_mod('rv_cta_url', '/contact/')))
  @php($pkgHref = \App\services_href(\App\field('about_cta2_url', \App\services_path() . '#packages')))
  @php($heroCtaHref = \App\cta_href(\App\field('about_cta_url', get_theme_mod('rv_cta_url', '/contact/'))))

  {{-- HERO — keyword H1, one lede, two CTAs; proof in a ribbon. --}}
  <section class="rv-hero" aria-labelledby="rv-about-hero-title">
    <span class="rv-stripe" aria-hidden="true"></span>
    @include('partials.hero-bg', ['fallback' => \App\stock_image('about')])
    <div class="rv-shell rv-hero-inner">
      {!! \App\eyebrow(\App\field('about_kicker', __('Gettysburg web designer', 'sage'))) !!}
      <h1 id="rv-about-hero-title" class="rv-hero-title">{{ \App\field('about_h1', __('A Gettysburg web designer for', 'sage')) }} <em class="rv-accent">{{ \App\field('about_h1_accent', __('South Central PA.', 'sage')) }}</em></h1>
      <p class="rv-hero-sub">{{ \App\field('about_lede', __('You work with Matt — a family studio in town — on a fast, accessible WordPress site with local SEO baked in. One fixed price. You keep the keys.', 'sage')) }}</p>
      <div class="rv-hero-actions">
        <a class="rv-btn rv-btn-primary" href="{{ $heroCtaHref }}">{{ \App\field('about_cta', __('Get a quote', 'sage')) }}</a>
        <a class="rv-btn rv-btn-ghost" href="{{ $pkgHref }}">{{ \App\field('about_cta2', __('See packages', 'sage')) }}</a>
      </div>
      <p class="rv-hero-note">{{ \App\field('about_meta', __('Family-owned in Gettysburg · Accessibility-first · Adams County & South Central PA', 'sage')) }}</p>
    </div>
    @php($aboutProof = \App\about_proof())
    @if (! empty($aboutProof))
      <div class="rv-hero-proof">
        <div class="rv-shell">
          <ul class="rv-hero-stats" aria-label="{{ __('What you can count on', 'sage') }}">
            @foreach ($aboutProof as $pf)
              <li>
                <span class="rv-hero-stat-v">{{ $pf['v'] ?? '' }}</span>
                @if (($pf['l'] ?? '') !== '')<span class="rv-hero-stat-l">{{ $pf['l'] }}</span>@endif
              </li>
            @endforeach
          </ul>
        </div>
      </div>
    @endif
  </section>

  {{-- WHY THIS STUDIO --}}
  <section class="rv-shell rv-band rv-about-bio" aria-labelledby="rv-about-studio-title">
    <div class="rv-headstack">
      {!! \App\eyebrow(\App\field('bio_eyebrow', __('Why this studio', 'sage'))) !!}
      <h2 id="rv-about-studio-title" class="rv-section-title">{{ \App\field('bio_title', __('Small on purpose.', 'sage')) }} <em class="rv-accent">{{ \App\field('bio_accent', __('Serious about results.', 'sage')) }}</em></h2>
      <p class="rv-page-intro">{{ \App\field('studio_lede', __('Ridges & Valleys is a family-owned Gettysburg web design studio. No account managers, no months of meetings — just a site that loads fast, reads clearly, and helps a local business get found and get work.', 'sage')) }}</p>
    </div>
    <div class="rv-about-promises">
      @foreach (\App\field_rows('about_promises', \App\about_promise_defaults()) as $hl)
        <article class="rv-about-hl">
          <h3>{{ $hl['title'] ?? '' }}</h3>
          <p>{{ $hl['text'] ?? '' }}</p>
        </article>
      @endforeach
    </div>
  </section>

  {{-- TEAM --}}
  <section class="rv-band rv-band-alt rv-about-team" aria-labelledby="rv-about-team-title">
    <div class="rv-shell">
      <div class="rv-headstack">
        {!! \App\eyebrow(\App\field('about_team_kicker', __('Who you’ll work with', 'sage'))) !!}
        <h2 id="rv-about-team-title" class="rv-section-title">{{ \App\field('about_team_h', __('A family studio in', 'sage')) }} <em class="rv-accent">{{ \App\field('about_team_accent', __('Gettysburg.', 'sage')) }}</em></h2>
        <p class="rv-page-intro">{{ \App\field('about_team_lede', __('Small on purpose, so every project gets real attention from the people whose name is on it.', 'sage')) }}</p>
      </div>

      <div class="rv-team-grid">
        @foreach (\App\field_rows('about_people', \App\about_people_defaults()) as $m)
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

      @php($teamNote = \App\field('about_team_note', __('When a project needs a photographer or a writer, we bring in trusted local specialists — project by project.', 'sage')))
      @if (trim(\App\strip_field_markers($teamNote)) !== '')<p class="rv-post-meta rv-about-team-note">{{ $teamNote }}</p>@endif
      @php($aboutInvite = \App\field('about_invite', __('Other marketing studios, freelancers, and photographers: we’re glad to collaborate. Come say hello.', 'sage')))
      @if (trim(\App\strip_field_markers($aboutInvite)) !== '')<p class="rv-post-meta rv-about-invite">{{ $aboutInvite }}</p>@endif
    </div>
  </section>

  {{-- HOW WE WORK --}}
  <section class="rv-shell rv-band" aria-labelledby="rv-about-how-title">
    <div class="rv-headstack">
      {!! \App\eyebrow(\App\field('about_how_kicker', __('How we work', 'sage'))) !!}
      <h2 id="rv-about-how-title" class="rv-section-title">{{ \App\field('about_how_h', __('What it’s like to', 'sage')) }} <em class="rv-accent">{{ \App\field('about_how_accent', __('hire us.', 'sage')) }}</em></h2>
    </div>
    <div class="rv-grid rv-grid-3 rv-about-values">
      @foreach (\App\field_rows('about_values', \App\about_value_defaults()) as $b)
        <article class="rv-card rv-feature">
          <span class="rv-stripe rv-stripe-thin" aria-hidden="true"></span>
          <h3 class="rv-feature-title">{{ $b['title'] ?? '' }}</h3>
          <p class="rv-feature-text">{{ $b['text'] ?? '' }}</p>
        </article>
      @endforeach
    </div>
  </section>

  {{-- ROOTED LOCALLY --}}
  <section class="rv-shell rv-band rv-about-local" aria-labelledby="rv-about-local-title">
    <div class="rv-about-local-grid">
      <div>
        <div class="rv-headstack">
          {!! \App\eyebrow(\App\field('about_local_kicker', __('Gettysburg & Adams County', 'sage'))) !!}
          <h2 id="rv-about-local-title" class="rv-about-local-h">{{ \App\field('about_local_h', __('This isn’t a market we', 'sage')) }} <em class="rv-accent">{{ \App\field('about_local_accent', __('picked off a map.', 'sage')) }}</em></h2>
        </div>
        <p class="rv-feature-text">{!! \App\field('about_local_lede', __('<strong>I\'m originally from Pennsylvania.</strong> After fifteen years in Virginia, I wanted to come home — and I chose Gettysburg to raise my family. The studio is staying. We know the summer rush on Steinwehr from a year-round shop in Biglerville, and we build your site for the customers you actually get.', 'sage')) !!}</p>
        <div class="rv-hero-actions rv-about-inline-actions">
          <a class="rv-btn rv-btn-primary" href="{{ $ctaHref }}">{{ \App\field('local_button', __('Get a quote', 'sage')) }}</a>
        </div>
      </div>
      <div class="rv-about-highlights">
        @foreach (\App\field_rows('local_highlights', [
          ['title' => __('In person, not a ticket queue', 'sage'), 'text' => __('A real local you can reach — a call, a screen share, or a meeting around Adams County.', 'sage')],
          ['title' => __('Built for how people search here', 'sage'), 'text' => __('Google Business Profile, local SEO, and map-ready details for the “near me” searches that matter.', 'sage')],
          ['title' => __('Visitors and neighbors, covered', 'sage'), 'text' => __('Tuned for the visitor economy and the locals who keep you busy all year.', 'sage')],
        ]) as $h)
          <div class="rv-about-hl">
            <h3>{{ $h['title'] ?? '' }}</h3>
            <p>{{ $h['text'] ?? '' }}</p>
          </div>
        @endforeach
      </div>
    </div>
  </section>

  {{-- WHO WE BUILD FOR + AREAS --}}
  <section class="rv-shell rv-band rv-about-serve" aria-labelledby="rv-about-fit-title">
    <div class="rv-headstack">
      {!! \App\eyebrow(\App\field('about_fit_kicker', __('Local businesses', 'sage'))) !!}
      <h2 id="rv-about-fit-title" class="rv-section-title">{{ \App\field('about_fit_h', __('Made for South Central PA', 'sage')) }} <em class="rv-accent">{{ \App\field('about_fit_accent', __('Main Street.', 'sage')) }}</em></h2>
      <p class="rv-page-intro">{{ \App\field('about_fit_lede', __('If people nearby search for what you do, we can help them find you, trust you, and reach you.', 'sage')) }}</p>
    </div>

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
      {!! \App\eyebrow(\App\field('about_areas_kicker', __('Areas served', 'sage'))) !!}
      <p class="rv-feature-text rv-about-towns-intro">{{ \App\field('about_towns_intro', __('Based in Gettysburg, working with businesses across Adams County and nearby:', 'sage')) }}</p>
      <div class="rv-about-towns" aria-label="{{ __('Towns served', 'sage') }}">
        @foreach (\App\field_lines('serve_towns', ['Gettysburg', 'Biglerville', 'Littlestown', 'New Oxford', 'McSherrystown', 'Bonneauville', 'Abbottstown', 'Fairfield', 'Cashtown', 'Arendtsville', 'East Berlin', 'York Springs', 'Aspers', 'Hanover']) as $t)<span class="rv-about-town">{{ $t }}</span>@endforeach
      </div>
      <p class="rv-post-meta rv-about-towns-note">{{ \App\field('about_towns_note', __('Plus townships across Adams, York, and Franklin counties. Not next door? Most work happens on a call and a shared screen.', 'sage')) }}</p>
    </div>
  </section>

  {{-- WHAT HAPPENS NEXT --}}
  <section class="rv-band rv-band-alt rv-about-next" aria-labelledby="rv-about-next-title">
    <div class="rv-shell">
      <div class="rv-headstack">
        {!! \App\eyebrow(\App\field('next_eyebrow', __('What happens next', 'sage'))) !!}
        <h2 id="rv-about-next-title" class="rv-section-title">{{ \App\field('next_title', __('From hello to a site', 'sage')) }} <em class="rv-accent">{{ \App\field('next_accent', __('you own.', 'sage')) }}</em></h2>
      </div>
      <ol class="rv-about-steps">
        @foreach (\App\field_rows('about_next', \App\about_next_defaults()) as $st)
          <li>
            <span class="rv-about-step-n" aria-hidden="true">{{ $st['n'] ?? '' }}</span>
            <h3>{{ $st['title'] ?? '' }}</h3>
            <p>{{ $st['text'] ?? '' }}</p>
          </li>
        @endforeach
      </ol>
    </div>
  </section>

  {{-- FREE TOOLS --}}
  <section class="rv-shell rv-band rv-about-tools" aria-labelledby="rv-about-tools-title">
    <div class="rv-headstack">
      {!! \App\eyebrow(\App\field('tools_eyebrow', __('Try us before you hire us', 'sage'))) !!}
      <h2 id="rv-about-tools-title" class="rv-section-title">{{ \App\field('tools_title', __('Six free tools.', 'sage')) }} <em class="rv-accent">{{ \App\field('tools_accent', __('No account, no email.', 'sage')) }}</em></h2>
      <p class="rv-page-intro">{{ \App\field('tools_intro', __('Put in your address, get a plain-English report, and fix what you like — with us or without us. The easiest way to see how we think before you spend a dollar.', 'sage')) }}</p>
    </div>
    <div class="rv-about-ind">
      @foreach (\App\field_rows('tools_items', [
        ['title' => __('Website Grader', 'sage'), 'url' => '/website-grader/', 'text' => __('Your whole site graded across seven areas, so you know what to fix first.', 'sage')],
        ['title' => __('SEO Checker', 'sage'), 'url' => '/seo-checker/', 'text' => __('Shows exactly why you are — or are not — showing up in search.', 'sage')],
        ['title' => __('Accessibility Checker', 'sage'), 'url' => '/accessibility/', 'text' => __('Scans a page against WCAG 2.2 AA so more customers can use your site.', 'sage')],
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

  {{-- PRICING + NAP --}}
  <section class="rv-band rv-band-alt rv-about-reach" aria-labelledby="rv-about-price-title">
    <div class="rv-shell rv-about-reach-grid">
      <div>
        {!! \App\eyebrow(\App\field('price_eyebrow', __('How pricing works', 'sage'))) !!}
        <h2 id="rv-about-price-title" class="rv-about-local-h">{{ \App\field('price_title', __('One fixed price,', 'sage')) }} <em class="rv-accent">{{ \App\field('price_accent', __('agreed up front.', 'sage')) }}</em></h2>
        <p class="rv-feature-text">{!! \App\field('price_p1', __('<strong>No hourly meter, no surprise invoices.</strong> You get the number before work starts. If you ask for something new, we agree on that price first, too.', 'sage')) !!}</p>
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
        <p class="rv-about-price-link"><a href="{{ $pkgHref }}">{{ \App\field('price_link', __('Compare packages', 'sage')) }} {!! \App\icon('arrow') !!}</a></p>
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
        <p class="rv-post-meta rv-about-nap-note">{{ \App\field('reach_note', __('We work from a home studio and meet at your place of business, so we don’t publish a street address. Call, email, or ask for a quote — you’ll hear back from Matt, not a call center.', 'sage')) }}</p>
        <div class="rv-hero-actions rv-about-inline-actions">
          <a class="rv-btn rv-btn-primary" href="{{ $ctaHref }}">{{ \App\field('reach_button', __('Get a free quote', 'sage')) }}</a>
        </div>
      </div>
    </div>
  </section>

  {{-- CLOSING CTA --}}
  <section class="rv-cta-band" aria-labelledby="rv-about-band-title">
    <div class="rv-shell rv-cta-inner">
      <h2 id="rv-about-band-title" class="rv-cta-title">{{ \App\field('about_band_title', __('Ready when you are.', 'sage')) }}</h2>
      <p class="rv-cta-sub">{{ \App\field('about_band_sub', __('Tell me about the business. I’ll come back with a clear, fixed-scope idea — usually within a business day.', 'sage')) }}</p>
      <a class="rv-btn rv-btn-on-dark" href="{{ $ctaHref }}">{{ \App\field('about_band_btn', __('Get a quote', 'sage')) }}</a>
    </div>
  </section>

  <style>
    .rv-about-inline-actions{justify-content:flex-start;margin-top:1.25rem}
    .rv-about-promises{display:grid;grid-template-columns:repeat(3,minmax(0,1fr));gap:1rem;margin-top:1.75rem}
    @media(max-width:820px){.rv-about-promises{grid-template-columns:1fr}}
    .rv-about-values{margin-top:1.75rem}
    .rv-about-tools .rv-about-ind h3 a{color:inherit;text-decoration:none}
    .rv-about-tools .rv-about-ind h3 a:hover{text-decoration:underline}
    .rv-about-tools-cta{margin:1.5rem 0 0}
    .rv-about-reach-grid{display:grid;grid-template-columns:1fr 1fr;gap:clamp(2rem,4vw,3.5rem);align-items:start}
    @media(max-width:820px){.rv-about-reach-grid{grid-template-columns:1fr}}
    .rv-about-prices{list-style:none;margin:1.25rem 0 0;padding:0;display:grid;gap:.7rem}
    .rv-about-prices li{position:relative;padding:.15rem 0 .15rem .95rem}
    .rv-about-prices li::before{content:"";position:absolute;left:0;top:.15rem;bottom:.15rem;width:4px;border-radius:2px;background:var(--ridgeline)}
    .rv-about-prices b{font-family:var(--font-display);color:var(--color-ink);display:block;font-size:1.02rem}
    .rv-about-prices span{color:var(--color-body);font-size:.95rem;line-height:1.5}
    .rv-about-price-link{margin:1.15rem 0 0}
    .rv-about-price-link a{display:inline-flex;align-items:center;gap:.35rem;font-weight:700;text-decoration:none}
    .rv-about-nap{list-style:none;margin:1.25rem 0 0;padding:0;display:grid;gap:.6rem}
    .rv-about-nap li{display:grid;grid-template-columns:6.5rem 1fr;gap:.75rem;align-items:baseline;font-size:1rem;color:var(--color-ink)}
    .rv-about-nap-l{font-family:var(--font-mono);font-size:.68rem;letter-spacing:.11em;text-transform:uppercase;color:var(--color-clay);font-weight:600}
    .rv-about-nap a{color:var(--color-ink)}
    .rv-about-nap-note{margin-top:1.1rem;max-width:52ch}
    @media(max-width:480px){.rv-about-nap li{grid-template-columns:1fr;gap:.15rem}}
    .rv-about-local-grid{display:grid;grid-template-columns:1.15fr .95fr;gap:clamp(2rem,4vw,3.5rem);align-items:start}
    @media(max-width:820px){.rv-about-local-grid{grid-template-columns:1fr}}
    .rv-about-local-h{font-family:var(--font-display);font-weight:800;font-size:clamp(1.75rem,3.5vw,2.5rem);line-height:1.08;letter-spacing:-.03em;color:var(--color-ink);margin:.45rem 0 1.1rem;max-width:18ch}
    .rv-about-local .rv-feature-text{font-size:1.08rem;line-height:1.7;color:var(--color-body);margin:0 0 1rem;max-width:48ch}
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
    .rv-about-towns-intro{margin-top:.5rem}
    .rv-about-towns-note{margin-top:1.25rem}
    .rv-about-towns{display:flex;flex-wrap:wrap;gap:.55rem;margin:1rem 0 0}
    .rv-about-town{background:var(--color-surface);border:1px solid var(--color-line);border-radius:999px;padding:.45rem .9rem;font-weight:600;font-size:.9rem;color:var(--color-ink)}
    .rv-about-steps{list-style:none;margin:1.75rem 0 0;padding:0;display:grid;grid-template-columns:repeat(3,minmax(0,1fr));gap:1.25rem}
    @media(max-width:820px){.rv-about-steps{grid-template-columns:1fr}}
    .rv-about-steps li{background:var(--color-surface);border:1px solid var(--color-line);border-radius:var(--radius-lg,16px);padding:1.35rem 1.4rem}
    .rv-about-step-n{font-family:var(--font-mono);font-size:.72rem;letter-spacing:.14em;text-transform:uppercase;color:var(--color-clay);font-weight:600;display:block;margin-bottom:.45rem}
    .rv-about-steps h3{font-family:var(--font-display);font-size:1.12rem;font-weight:700;color:var(--color-ink);margin:0 0 .35rem}
    .rv-about-steps p{margin:0;color:var(--color-body);font-size:.95rem;line-height:1.55}
    .rv-team-grid{display:grid;grid-template-columns:repeat(auto-fit,minmax(280px,1fr));gap:1.25rem;margin-top:2rem}
    .rv-team-card{background:var(--color-surface);border:1px solid var(--color-line);border-radius:var(--radius-lg,16px);padding:1.6rem 1.5rem;text-align:left}
    .rv-team-photo{width:96px;height:96px;border-radius:50%;object-fit:cover;margin:0 0 .9rem;display:block;border:3px solid var(--color-line)}
    .rv-team-avatar{width:96px;height:96px;border-radius:50%;margin:0 0 .9rem;display:flex;align-items:center;justify-content:center;background:color-mix(in srgb,var(--color-sage) 22%,var(--color-surface));border:3px solid var(--color-line);color:color-mix(in srgb,var(--color-ink) 45%,transparent)}
    .rv-team-avatar svg{width:58px;height:58px;display:block}
    .rv-team-name{font-family:var(--font-display);font-size:1.3rem;font-weight:800;color:var(--color-ink);margin:0}
    .rv-team-role{font-family:var(--font-mono);font-size:.72rem;letter-spacing:.12em;text-transform:uppercase;color:var(--color-clay);font-weight:600;margin:.35rem 0 .8rem}
    .rv-team-bio{color:var(--color-body);font-size:.98rem;line-height:1.6;margin:0}
    .rv-about-team-note,.rv-about-invite{text-align:left;margin-top:1.25rem;max-width:62ch}
    .rv-about-invite{margin-top:.65rem}
  </style>
@endsection
