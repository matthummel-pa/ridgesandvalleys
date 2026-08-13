@extends('layouts.app')

@section('content')
  @php
    // Set up the main query post so \App\field() reads homepage meta, without
    // rendering the Gutenberg page body (replaced by the intro partial below).
    if (have_posts()) {
        the_post();
    }
  @endphp

  {{-- HERO — same pattern as services: kicker, keyword H1, one lede, two CTAs.
       Proof stats sit in a ribbon on the seam so the copy stays scannable. --}}
  <section class="rv-hero rv-hero-home" aria-labelledby="rv-home-hero-title">
    <span class="rv-stripe" aria-hidden="true"></span>
    @include('partials.hero-bg', ['fallback' => \App\stock_image('hero-home')])
    @php($rvAsides = \App\hero_asides())
    <div class="rv-shell rv-hero-inner">
      <div class="rv-hero-cols" data-cols="{{ 1 + count($rvAsides) }}">
        <div class="rv-hero-main">
          {!! \App\eyebrow(\App\field('home_kicker', __('Web design & local SEO · Gettysburg', 'sage'))) !!}
          <h1 id="rv-home-hero-title" class="rv-hero-title">{{ \App\field('home_headline', __('Gettysburg web design that gets you', 'sage')) }} <em class="rv-accent">{{ \App\field('home_headline_accent', __('found.', 'sage')) }}</em></h1>
          <p class="rv-hero-sub">{{ \App\field('home_lede', __('Fixed-scope WordPress sites for Adams County — local SEO baked in, a first draft in about seven days, and a site you own.', 'sage')) }}</p>
          <div class="rv-hero-actions">
            <a class="rv-btn {{ \App\hero_btn_class(\App\field('hero_btn1_style', ''), 'rv-btn-primary') }}" href="{{ \App\cta_href(\App\field('home_cta1_url', get_theme_mod('rv_cta_url', '/contact/'))) }}">{{ \App\field('home_cta1', __('Get a quote', 'sage')) }}</a>
            <a class="rv-btn {{ \App\hero_btn_class(\App\field('hero_btn2_style', ''), 'rv-btn-ghost') }}" href="{{ \App\services_href(\App\field('home_cta2_url', \App\services_path() . '#packages')) }}">{{ \App\field('home_cta2', __('See packages', 'sage')) }}</a>
          </div>
          <p class="rv-hero-note">{{ \App\field('home_byline', __('Family-owned in Gettysburg · Led by Matt Hummel', 'sage')) }}</p>
        </div>
        @include('partials.hero-asides', ['asides' => $rvAsides])
      </div>
    </div>
    @php($rvStats = \App\hero_stats())
    @if (count($rvStats))
      <div class="rv-hero-proof">
        <div class="rv-shell">
          <ul class="rv-hero-stats" aria-label="{{ __('At a glance', 'sage') }}">
            @foreach ($rvStats as $rvStat)
              <li>
                <span class="rv-hero-stat-v">{{ $rvStat['value'] }}</span>
                @if (($rvStat['label'] ?? '') !== '')<span class="rv-hero-stat-l">{{ $rvStat['label'] }}</span>@endif
              </li>
            @endforeach
          </ul>
        </div>
      </div>
    @endif
  </section>

  @include('partials.home-intro')

  {{--
    Home page sections render in the order set on the home page editor's
    "Home page layout" box. \App\home_sections() returns the visible section
    keys in the saved order; each maps to resources/views/partials/home-<key>.
    Reorder or hide them from that box — no code edits needed.
  --}}
  @foreach (\App\home_sections() as $rvSection)
    @includeIf('partials.home-' . $rvSection)
  @endforeach

@endsection
