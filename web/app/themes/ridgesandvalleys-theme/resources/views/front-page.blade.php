@extends('layouts.app')

@section('content')
  @php($ctaText = get_theme_mod('rv_cta_text', __('Get a quote', 'sage')))
  @php($ctaHref = \App\cta_href(get_theme_mod('rv_cta_url', '/contact/')))

  {{-- HERO --}}
  <section class="rv-hero">
    <span class="rv-stripe" aria-hidden="true"></span>
    @include('partials.hero-bg', ['fallback' => \App\stock_image('hero-home')])
    {{-- Scoped home-page polish: hero proof strip + credibility line, and the
         intro/SEO section under the hero. Home page only, fully fluid units. --}}
    <style>
      .home .rv-hero-title{max-width:22ch}
      .rv-hero-stats{list-style:none;margin:clamp(1.5rem,4vw,2.1rem) 0 0;padding:clamp(1rem,2.5vw,1.4rem) 0 0;border-block-start:1px solid rgba(255,255,255,.16);display:flex;flex-wrap:wrap;gap:clamp(.75rem,2vw,1rem) clamp(1.25rem,4vw,2.25rem);max-width:min(100%,40rem)}
      .rv-hero-stats li{display:flex;flex-direction:column;gap:.15rem;min-width:0}
      .rv-hero-stat-v{font-family:var(--font-display,inherit);font-weight:800;font-size:clamp(1.15rem,2.3vw,1.5rem);line-height:1.05;color:#fff}
      .rv-hero-stat-l{font-size:clamp(.72rem,1.4vw,.82rem);line-height:1.25;color:#cdd7c6}
      .rv-hero-note{margin:clamp(.9rem,2.5vw,1.1rem) 0 0;font-size:clamp(.85rem,1.6vw,.95rem);line-height:1.55;color:rgba(247,241,230,.82);max-width:60ch}

      /* Intro / SEO section under the hero */
      .rv-home-intro{text-align:center}
      .rv-home-intro .rv-intro-eyebrow{font-family:var(--font-mono,ui-monospace,monospace);font-size:clamp(.7rem,1.4vw,.8rem);letter-spacing:.14em;text-transform:uppercase;color:var(--color-clay);margin:0 0 .7rem}
      .rv-home-intro .rv-intro-title{font-family:var(--font-display,inherit);font-weight:800;font-size:clamp(1.7rem,3.8vw,2.55rem);line-height:1.12;margin:0 auto;max-width:22ch;color:var(--color-ink)}
      .rv-home-intro .rv-intro-lead{font-size:clamp(1.05rem,1.8vw,1.28rem);line-height:1.6;color:var(--color-muted);max-width:60ch;margin:1.1rem auto 0}
      .rv-home-intro .rv-intro-cols{display:grid;grid-template-columns:repeat(3,minmax(0,1fr));gap:clamp(1.5rem,3.5vw,2.75rem);margin:clamp(2rem,5vw,3.25rem) auto 0;text-align:start;max-width:min(100%,66rem)}
      .rv-home-intro .rv-intro-cols .wp-block-column{margin:0}
      .rv-home-intro .rv-intro-cols h3{font-family:var(--font-display,inherit);font-weight:700;font-size:clamp(1.1rem,1.7vw,1.28rem);line-height:1.25;margin:0 0 .55rem;padding-block-start:.9rem;border-block-start:2px solid var(--color-clay);color:var(--color-ink)}
      .rv-home-intro .rv-intro-cols p{font-size:1rem;line-height:1.65;color:var(--color-muted);margin:0}
      .rv-home-intro .rv-intro-cols a{color:var(--color-pine);text-decoration:underline;text-underline-offset:.15em}
      .rv-home-intro figure.wp-block-image{margin:clamp(2rem,5vw,3.25rem) auto 0;max-width:min(100%,66rem)}
      .rv-home-intro figure.wp-block-image img{display:block;width:100%;height:auto;border-radius:16px;box-shadow:var(--shadow-soft,0 14px 34px rgba(20,25,20,.14))}
      .rv-home-intro .rv-intro-close{font-size:clamp(1rem,1.6vw,1.15rem);line-height:1.6;color:var(--color-ink);max-width:62ch;margin:clamp(1.75rem,4vw,2.5rem) auto 0}
      .rv-home-intro .rv-intro-close a{color:var(--color-pine);text-decoration:underline;text-underline-offset:.15em}
      @media(max-width:820px){.rv-home-intro .rv-intro-cols{grid-template-columns:1fr;gap:clamp(1.5rem,6vw,2rem)}}
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

  {{-- INTRO / SEO section: the page's own body content, rendered in a designed,
       wide section (eyebrow, heading, lead, 3-column feature grid, image).
       Styling for the .rv-intro-* classes lives in the scoped <style> above. --}}
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
