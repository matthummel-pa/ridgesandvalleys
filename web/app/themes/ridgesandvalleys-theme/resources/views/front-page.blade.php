@extends('layouts.app')

@section('content')
  @php($ctaText = get_theme_mod('rv_cta_text', __('Get a quote', 'sage')))
  @php($ctaHref = \App\cta_href(get_theme_mod('rv_cta_url', '/contact/')))

  {{-- HERO --}}
  <section class="rv-hero">
    <span class="rv-stripe" aria-hidden="true"></span>
    @include('partials.hero-bg', ['fallback' => \App\stock_image('hero-home')])
    @php($rvAsides = \App\hero_asides())
    <div class="rv-shell rv-hero-inner">
      <div class="rv-hero-cols" data-cols="{{ 1 + count($rvAsides) }}">
        <div class="rv-hero-main">
          {!! \App\eyebrow(\App\field('hero_eyebrow', __('Gettysburg · Web design · Local growth', 'sage'))) !!}
          <h1 class="rv-hero-title">{{ \App\field('hero_title', __('A better website, without the agency', 'sage')) }} <em class="rv-accent">{{ \App\field('hero_accent', __('drag.', 'sage')) }}</em></h1>
          <p class="rv-hero-sub">{{ \App\field('hero_sub', __('Fast, accessible WordPress websites for Gettysburg, Adams County, and South Central PA businesses — planned with AI, refined by an experienced local developer, and launched without months of meetings.', 'sage')) }}</p>
          <div class="rv-hero-actions">
            <a class="rv-btn rv-btn-primary" href="{{ \App\cta_href(\App\field('hero_btn1_url', get_theme_mod('rv_cta_url', '/contact/'))) }}">{{ __('Plan my site', 'sage') }}</a>
            <a class="rv-btn rv-btn-ghost" href="{{ \App\cta_href(\App\field('hero_btn2_url', '/services/')) }}">{{ __('See the process', 'sage') }}</a>
          </div>
          <p class="rv-hero-trust">{{ \App\field('hero_trust', __('15+ yrs building for the web · Accessibility-first · WordPress · Local support', 'sage')) }}</p>
        </div>
        @include('partials.hero-asides', ['asides' => $rvAsides])
      </div>
    </div>
  </section>

  @while(have_posts()) @php(the_post())
    @if (trim(get_the_content()))
      <section class="rv-shell-full"><div class="rv-reading rv-prose" style="padding-block:var(--section-y) 0">@php(the_content())</div></section>
    @endif
  @endwhile

  {{-- PROBLEMS --}}
  <section class="rv-band rv-band-alt">
    <div class="rv-shell">
      {!! \App\eyebrow(\App\field('problems_eyebrow', __('If this sounds familiar', 'sage'))) !!}
      <h2 class="rv-section-title">{{ \App\field('problems_title', __('Your site should', 'sage')) }} <em class="rv-accent">{{ \App\field('problems_accent', __('help', 'sage')) }}</em> {{ \App\field('problems_after', __('people act.', 'sage')) }}</h2>
      <div class="rv-grid rv-grid-3" style="margin-top:2.25rem">
        @php($problems = [
          ['01', \App\field('problem1_title', __('Hard to find the basics', 'sage')), \App\field('problem1_text', __('Hours, directions, and prices are buried — or living on Facebook where you don\'t control them.', 'sage'))],
          ['02', \App\field('problem2_title', __('Dated on a phone', 'sage')), \App\field('problem2_text', __('Most visitors show up on mobile, and the current site fights them the whole way.', 'sage'))],
          ['03', \App\field('problem3_title', __('Risky to update', 'sage')), \App\field('problem3_text', __('Changing a price or a photo feels like it might break the whole thing.', 'sage'))],
        ])
        @foreach ($problems as $p)
          <article class="rv-card rv-feature">
            <span class="rv-stripe rv-stripe-thin" aria-hidden="true"></span>
            <span class="rv-metric-v" style="font-size:2rem">{{ $p[0] }}</span>
            <h3 class="rv-feature-title" style="margin-top:.4rem">{{ $p[1] }}</h3>
            <p class="rv-feature-text">{{ $p[2] }}</p>
          </article>
        @endforeach
      </div>
    </div>
  </section>

  {{-- PACKAGES --}}
  <section class="rv-shell rv-band">
    {!! \App\eyebrow(\App\field('pkg_eyebrow', __('Clear scope. Fast build. No mystery.', 'sage'))) !!}
    <h2 class="rv-section-title">{{ \App\field('pkg_title', __('Three ways to', 'sage')) }} <em class="rv-accent">{{ \App\field('pkg_accent', __('start.', 'sage')) }}</em></h2>
    <div class="rv-packages" style="margin-top:2.25rem">
      @php($packages = [
        ['', \App\field('pkg1_name', __('Website Rescue', 'sage')), \App\field('pkg1_price', '$950'), '+', \App\field('pkg1_desc', __('Audit, cleanup, broken links, mobile, speed & SEO fixes.', 'sage'))],
        [\App\field('pkg2_flag', __('Most popular', 'sage')), \App\field('pkg2_name', __('Local Launch', 'sage')), \App\field('pkg2_price', '$2,750'), '+', \App\field('pkg2_desc', __('Up to 5 pages, local SEO, analytics, accessibility, one revision.', 'sage'))],
        ['', \App\field('pkg3_name', __('Growth Site', 'sage')), \App\field('pkg3_price', '$4,500'), '+', \App\field('pkg3_desc', __('8–12 pages, migration, booking or ecommerce, advanced forms.', 'sage'))],
        ['', \App\field('pkg4_name', __('Care & Grow', 'sage')), \App\field('pkg4_price', '$179'), '/mo', \App\field('pkg4_desc', __('Updates, backups, security, small changes, reporting.', 'sage'))],
      ])
      @foreach ($packages as $pk)
        <article class="rv-pkg {{ $pk[0] ? 'rv-pkg-feat' : '' }}">
          <span class="rv-stripe rv-stripe-thin" aria-hidden="true"></span>
          <div class="rv-pkg-in">
            @if ($pk[0])<span class="rv-pkg-flag">{{ $pk[0] }}</span>@endif
            <span class="rv-pkg-name">{{ $pk[1] }}</span>
            <span class="rv-pkg-price">{{ $pk[2] }}<span>{{ $pk[3] }}</span></span>
            <p class="rv-pkg-desc">{{ $pk[4] }}</p>
          </div>
        </article>
      @endforeach
    </div>
    <div style="margin-top:1.5rem"><a class="rv-btn rv-btn-ghost" href="{{ \App\cta_href(\App\field('pkg_cta_url', '/services/')) }}">{{ \App\field('pkg_cta', __('Compare all services', 'sage')) }} {!! \App\icon('arrow') !!}</a></div>
  </section>

  {{-- INCLUDED IN EVERY BUILD --}}
  <section class="rv-band rv-band-alt">
    <div class="rv-shell">
      {!! \App\eyebrow(\App\field('included_eyebrow', __('No surprises', 'sage'))) !!}
      <h2 class="rv-section-title">{{ \App\field('included_title', __('Included in', 'sage')) }} <em class="rv-accent">{{ \App\field('included_accent', __('every', 'sage')) }}</em> {{ \App\field('included_title_end', __('build.', 'sage')) }}</h2>
      <div class="rv-grid rv-grid-3" style="margin-top:2rem">
        @foreach (\App\field_rows('included_items', [
          ['title' => __('Accessibility-first', 'sage'), 'text' => __('Built to WCAG 2.1 AA — readable contrast, keyboard navigation, and screen-reader-friendly structure on every page.', 'sage')],
          ['title' => __('Mobile-first', 'sage'), 'text' => __('Designed for the phone first, because that\'s where most of your visitors actually are.', 'sage')],
          ['title' => __('You own everything', 'sage'), 'text' => __('Your domain, your hosting account, your site. No lock-in, and you can leave a care plan anytime.', 'sage')],
          ['title' => __('Found locally', 'sage'), 'text' => __('Google Business Profile setup, on-page SEO, and the local foundations that put you on the map.', 'sage')],
          ['title' => __('Fast & secure', 'sage'), 'text' => __('Lean, page-builder-free code, HTTPS, backups, and a hosting setup tuned to load quickly.', 'sage')],
          ['title' => __('A real training handoff', 'sage'), 'text' => __('A short walkthrough (and a video) so you can update the few things you\'ll actually touch.', 'sage')],
        ]) as $inc)
          <article class="rv-card rv-feature">
            <span class="rv-stripe rv-stripe-thin" aria-hidden="true"></span>
            <h3 class="rv-feature-title">{{ $inc['title'] ?? '' }}</h3>
            <p class="rv-feature-text">{{ $inc['text'] ?? '' }}</p>
          </article>
        @endforeach
      </div>
    </div>
  </section>

  {{-- PROCESS TIMELINE --}}
  <section class="rv-shell rv-band">
    {!! \App\eyebrow(\App\field('htime_eyebrow', __('Groundwork to launch', 'sage'))) !!}
    <h2 class="rv-section-title">{{ \App\field('htime_title', __('First draft in', 'sage')) }} <em class="rv-accent">{{ \App\field('htime_accent', __('days,', 'sage')) }}</em> {{ \App\field('htime_title_end', __('not months.', 'sage')) }}</h2>
    <div class="rv-timeline" style="margin-top:2rem">
      @foreach (\App\field_rows('htime_items', [
        ['day' => __('Before Day 1', 'sage'), 'title' => __('Fit & scope', 'sage'), 'text' => __('Call, signed scope, deposit.', 'sage')],
        ['day' => __('Day 1', 'sage'), 'title' => __('One intake form', 'sage'), 'text' => __('You send info + assets.', 'sage')],
        ['day' => __('Day 3', 'sage'), 'title' => __('Working draft', 'sage'), 'text' => __('Staging site + walkthrough.', 'sage')],
        ['day' => __('Day 6', 'sage'), 'title' => __('One revision', 'sage'), 'text' => __('Consolidated feedback.', 'sage')],
        ['day' => __('Days 7–10', 'sage'), 'title' => __('Launch', 'sage'), 'text' => __('Handoff + training.', 'sage')],
      ]) as $t)
        <div class="rv-tl-step">
          <span class="rv-tl-day">{{ $t['day'] ?? '' }}</span>
          <h3 class="rv-tl-title">{{ $t['title'] ?? '' }}</h3>
          <p>{{ $t['text'] ?? '' }}</p>
        </div>
      @endforeach
    </div>
  </section>

  {{-- FEATURED CASE --}}
  @php($featured = new WP_Query(['post_type' => 'project', 'posts_per_page' => 1, 'no_found_rows' => true]))
  <section class="rv-band rv-band-alt">
    <div class="rv-shell">
      {!! \App\eyebrow(__('Featured work', 'sage')) !!}
      <h2 class="rv-section-title">{{ __('Proof, not', 'sage') }} <em class="rv-accent">{{ __('promises.', 'sage') }}</em></h2>
      <div class="rv-split" style="margin-top:1.75rem">
        @if ($featured->have_posts()) @php($featured->the_post())
          <div class="rv-split-media rv-media-photo">
            @if (has_post_thumbnail())
              <img src="{{ get_the_post_thumbnail_url(get_the_ID(), 'rv-hero') }}" alt="{{ get_the_title() }}" loading="lazy" onerror="this.style.display='none'">
            @else
              <img src="{{ \App\stock_image('featured') }}" alt="{{ __('Adams County farmland near Gettysburg', 'sage') }}" loading="lazy" onerror="this.style.display='none'">
            @endif
          </div>
          <div class="rv-split-body">
            {!! \App\eyebrow(get_post_meta(get_the_ID(), '_rv_client', true) ?: __('Local Launch', 'sage')) !!}
            <h3 class="rv-split-title">{!! get_the_title() !!}</h3>
            <p class="rv-feature-text">{{ get_the_excerpt() ?: __('A focused rebuild that made the business easy to reach and easy to trust — shipped in a week.', 'sage') }}</p>
            <a class="rv-readmore" href="{{ get_permalink() }}" style="margin-top:1.25rem">{{ __('Read the case study', 'sage') }} {!! \App\icon('arrow') !!}</a>
          </div>
          @php(wp_reset_postdata())
        @else
          <div class="rv-split-media rv-media-photo">
            <img src="{{ \App\stock_image('featured') }}" alt="{{ __('Wentz farm buildings near Gettysburg', 'sage') }}" loading="lazy" onerror="this.style.display='none'">
          </div>
          <div class="rv-split-body">
            {!! \App\eyebrow(__('Bradley Goldsmith Law · Local Launch', 'sage')) !!}
            <h3 class="rv-split-title">{{ __('A clearer path from visitor to consultation.', 'sage') }}</h3>
            <p class="rv-feature-text">{{ __('A focused five-page rebuild that made the firm easy to reach and easy to trust — shipped in a week.', 'sage') }}</p>
            <div class="rv-metric">
              <div><div class="rv-metric-v">{{ __('7 days', 'sage') }}</div><div class="rv-metric-l">{{ __('design to launch', 'sage') }}</div></div>
              <div><div class="rv-metric-v">{{ __('AA', 'sage') }}</div><div class="rv-metric-l">{{ __('accessibility', 'sage') }}</div></div>
              <div><div class="rv-metric-v">{{ __('100%', 'sage') }}</div><div class="rv-metric-l">{{ __('mobile-ready', 'sage') }}</div></div>
            </div>
            <a class="rv-readmore" href="{{ home_url('/work/') }}" style="margin-top:1.25rem">{{ __('See the work', 'sage') }} {!! \App\icon('arrow') !!}</a>
          </div>
        @endif
      </div>
    </div>
  </section>

  {{-- ROOTED / LOCAL (split with photo) --}}
  <section class="rv-shell rv-band">
    <div class="rv-split rv-split-reverse">
      <div class="rv-split-media rv-media-photo">
        <img src="{{ \App\stock_image('rooted') }}" alt="{{ __('1935 aerial photograph of Gettysburg set among the farmland and ridges of Adams County', 'sage') }}" loading="lazy" onerror="this.style.display='none'">
      </div>
      <div class="rv-split-body">
        {!! \App\eyebrow(\App\field('rooted_eyebrow', __('Rooted in the ridges & valleys', 'sage'))) !!}
        <h2 class="rv-section-title" style="margin-top:.3rem">{{ \App\field('rooted_title', __('Built here.', 'sage')) }} <em class="rv-accent">{{ \App\field('rooted_accent', __('Supported', 'sage')) }}</em> {{ __('here.', 'sage') }}</h2>
        <p class="rv-feature-text" style="margin-top:.75rem">{{ \App\field('rooted_text', __('From the Cumberland Valley to South Mountain, Michaux, and the fields around Gettysburg — this is home. I build for the businesses that make this place what it is, and I\'m proud of the history that comes with the address.', 'sage')) }}</p>
        <div class="rv-chips">
          @php($rootedChips = array_filter(array_map('trim', explode(',', \App\field('rooted_chips', 'Gettysburg, Adams County, Cumberland Valley, South Mountain, Michaux')))))
          @foreach ($rootedChips as $place)
            <span class="rv-mchip">{{ $place }}</span>
          @endforeach
        </div>
      </div>
    </div>
  </section>

  {{-- TOWNS SERVED (local SEO) --}}
  <section class="rv-band rv-band-alt">
    <div class="rv-shell">
      {!! \App\eyebrow(\App\field('htowns_eyebrow', __('Local, in the real sense', 'sage'))) !!}
      <h2 class="rv-section-title">{{ \App\field('htowns_title', __('Serving Gettysburg & the', 'sage')) }} <em class="rv-accent">{{ \App\field('htowns_accent', __('surrounding', 'sage')) }}</em> {{ \App\field('htowns_title_end', __('towns.', 'sage')) }}</h2>
      <p class="rv-page-intro">{{ \App\field('htowns_intro', __('If your customers are in Adams County or nearby South Central PA, they can find you. Ask about a town-specific page for the places you serve most.', 'sage')) }}</p>
      <div class="rv-chips" style="margin-top:1.25rem">
        @foreach (\App\field_lines('htowns_list', ['Gettysburg', 'Hanover', 'Littlestown', 'New Oxford', 'McSherrystown', 'Biglerville', 'East Berlin', 'Fairfield', 'Cashtown', 'Aspers', 'Abbottstown', 'Bonneauville', 'Carlisle', 'Chambersburg', 'York']) as $town)
          <span class="rv-mchip">{{ $town }}</span>
        @endforeach
      </div>
    </div>
  </section>

@endsection
