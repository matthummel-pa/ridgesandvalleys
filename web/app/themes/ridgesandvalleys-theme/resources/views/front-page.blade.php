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
            <a class="rv-btn {{ \App\hero_btn_class(\App\field('hero_btn1_style', ''), 'rv-btn-primary') }}" href="{{ \App\cta_href(\App\field('hero_btn1_url', get_theme_mod('rv_cta_url', '/contact/'))) }}">{{ \App\field('hero_btn1', __('Plan my site', 'sage')) }}</a>
            <a class="rv-btn {{ \App\hero_btn_class(\App\field('hero_btn2_style', ''), 'rv-btn-ghost') }}" href="{{ \App\cta_href(\App\field('hero_btn2_url', '/services/')) }}">{{ \App\field('hero_btn2', __('See the process', 'sage')) }}</a>
          </div>
          {{-- Proof strip — editable from the Home page layout box ("Hero proof
               stats"); \App\hero_stats() supplies true defaults when unset. --}}
          @php($rvStats = \App\hero_stats())
          @if (count($rvStats))
            <ul class="rv-hero-stats">
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

  {{-- INTRO / SEO section: the page's own body content, rendered in a designed,
       wide section (eyebrow, heading, lead, 3-column feature grid, image).
       Styling for .rv-home-intro / .rv-intro-* lives in resources/css/app.css. --}}
  @while(have_posts()) @php(the_post())
    @if (trim(get_the_content()))
      <section class="rv-band rv-home-intro">
        <div class="rv-shell">@php(the_content())</div>
      </section>
    @endif
  @endwhile

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
