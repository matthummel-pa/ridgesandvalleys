@extends('layouts.app')

@section('content')
  @php($ctaText = get_theme_mod('rv_cta_text', __('Get a quote', 'sage')))
  @php($ctaHref = \App\cta_href(get_theme_mod('rv_cta_url', '/contact/')))

  {{-- HERO --}}
  <section class="rv-hero">
    <span class="rv-stripe" aria-hidden="true"></span>
    @include('partials.hero-bg', ['fallback' => \App\stock_image('hero-home')])
    {{-- Scoped hero polish: wider title wrap (fewer lines → CTA above the fold),
         a true proof-stat strip, and a readable credibility line replacing the
         old all-caps monospace paragraph. Home page only, fully fluid units. --}}
    <style>
      .home .rv-hero-title{max-width:22ch}
      .rv-hero-stats{list-style:none;margin:clamp(1.5rem,4vw,2.1rem) 0 0;padding:clamp(1rem,2.5vw,1.4rem) 0 0;border-block-start:1px solid rgba(255,255,255,.16);display:flex;flex-wrap:wrap;gap:clamp(.75rem,2vw,1rem) clamp(1.25rem,4vw,2.25rem);max-width:min(100%,40rem)}
      .rv-hero-stats li{display:flex;flex-direction:column;gap:.15rem;min-width:0}
      .rv-hero-stat-v{font-family:var(--font-display,inherit);font-weight:800;font-size:clamp(1.15rem,2.3vw,1.5rem);line-height:1.05;color:#fff}
      .rv-hero-stat-l{font-size:clamp(.72rem,1.4vw,.82rem);line-height:1.25;color:#cdd7c6}
      .rv-hero-note{margin:clamp(.9rem,2.5vw,1.1rem) 0 0;font-size:clamp(.85rem,1.6vw,.95rem);line-height:1.55;color:rgba(247,241,230,.82);max-width:60ch}
    </style>
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

  @while(have_posts()) @php(the_post())
    @if (trim(get_the_content()))
      <section class="rv-shell-full"><div class="rv-reading rv-prose" style="padding-block:var(--section-y) 0">@php(the_content())</div></section>
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
