@extends('layouts.app')

@section('content')
  @php
    // Set up the main query post so \App\field() reads homepage meta, without
    // rendering the Gutenberg page body (replaced by the intro partial below).
    if (have_posts()) {
        the_post();
    }
  @endphp

  {{-- HERO --}}
  <section class="rv-hero">
    <span class="rv-stripe" aria-hidden="true"></span>
    @include('partials.hero-bg', ['fallback' => \App\stock_image('hero-home')])
    @php($rvAsides = \App\hero_asides())
    <div class="rv-shell rv-hero-inner">
      <div class="rv-hero-cols" data-cols="{{ 1 + count($rvAsides) }}">
        <div class="rv-hero-main">
          {!! \App\eyebrow(\App\field('hero_eyebrow', __('Gettysburg Web Design Studio · Local Business Growth', 'sage'))) !!}
          <h1 class="rv-hero-title">{{ \App\field('hero_title', __('Websites that help Gettysburg businesses', 'sage')) }} <em class="rv-accent">{{ \App\field('hero_accent', __('get found.', 'sage')) }}</em></h1>
          <p class="rv-hero-sub">{{ \App\field('hero_sub', __('Get a fast, accessible website designed to help your Gettysburg business get found and win more customers. Local SEO, clear pricing, and a first draft in about seven days. Serving Gettysburg and all of Adams County.', 'sage')) }}</p>
          <div class="rv-hero-actions">
            <a class="rv-btn {{ \App\hero_btn_class(\App\field('hero_btn1_style', ''), 'rv-btn-primary') }}" href="{{ \App\cta_href(\App\field('hero_btn1_url', get_theme_mod('rv_cta_url', '/contact/'))) }}">{{ \App\field('hero_btn1', __('Plan my site', 'sage')) }}</a>
            <a class="rv-btn {{ \App\hero_btn_class(\App\field('hero_btn2_style', ''), 'rv-btn-ghost') }}" href="{{ \App\services_href(\App\field('hero_btn2_url', \App\services_path())) }}">{{ \App\field('hero_btn2', __('See the process', 'sage')) }}</a>
          </div>
          @php($rvStats = \App\hero_stats())
          @if (count($rvStats))
            <ul class="rv-hero-stats" aria-label="{{ __('At a glance', 'sage') }}">
              @foreach ($rvStats as $rvStat)
                <li>
                  <span class="rv-hero-stat-v">{{ $rvStat['value'] }}</span>
                  @if (($rvStat['label'] ?? '') !== '')<span class="rv-hero-stat-l">{{ $rvStat['label'] }}</span>@endif
                </li>
              @endforeach
            </ul>
          @endif
          <p class="rv-hero-note">{{ \App\field('hero_trust', __('Family-owned in Gettysburg, serving Adams County · Led by Matt Hummel', 'sage')) }}</p>
        </div>
        @include('partials.hero-asides', ['asides' => $rvAsides])
      </div>
    </div>
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
