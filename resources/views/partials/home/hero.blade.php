{{-- partials/home/hero.blade.php

     GENERIC BASE-THEME HERO. Every visible string is a theme_mod (Customizer
     -> Theme Options -> Hero) with a neutral default — no personal copy is
     hardcoded, so this works as a starting point for any site built on
     Pressroot. If a Brand profile is saved (Appearance -> Pressroot ->
     Brand), the business name/description flow in as smarter defaults.

     Design: the Repofolio docs-site hero treatment — dark #201B3A -> #15122a
     ground with iris/pink radial glows, mono uppercase eyebrow, gradient
     display headline (opening line + gradient word + serif word + closing
     phrase, all editable), gradient + ghost pill CTAs, floaty visual column
     with optional portrait and two accent chips. --}}
@php
  $brand     = function_exists('App\\prt_brand_profile') ? \App\prt_brand_profile() : ['name' => '', 'desc' => ''];

  $avail     = (bool) get_theme_mod('prt_avail_open', true);
  $eyebrow   = get_theme_mod('prt_avail_text', $brand['name'] !== '' ? $brand['name'] : __('Available for new projects', 'pressroot'));

  $headline  = get_theme_mod('prt_hero_title', __('Your brand in.', 'pressroot'));
  $accent    = get_theme_mod('prt_hero_accent', __('Your site', 'pressroot'));
  $serif     = get_theme_mod('prt_hero_serif', __('out.', 'pressroot'));
  $suffix    = get_theme_mod('prt_hero_suffix', '');

  $sub       = get_theme_mod('prt_hero_subtext', $brand['desc'] !== ''
                  ? $brand['desc']
                  : __('Deep enough for developers, sharp enough for marketers, simple enough for any business owner to run solo. Pick a site type, deal a design, make it yours.', 'pressroot'));

  $btn1Text  = get_theme_mod('prt_hero_btn1_text', __('See the work →', 'pressroot'));
  $btn1Url   = get_theme_mod('prt_hero_btn1_url', '') ?: (get_post_type_archive_link('repofolio_project') ?: (get_post_type_archive_link('projects') ?: '#work'));
  $btn2Text  = get_theme_mod('prt_hero_btn2_text', __("Let's talk", 'pressroot'));
  $btn2Url   = get_theme_mod('prt_hero_btn2_url', '') ?: (($contactPage = get_page_by_path('contact')) ? get_permalink($contactPage) : '#contact');

  $chip1     = get_theme_mod('prt_hero_chip1', __('⚡ Fast by default', 'pressroot'));
  $chip2     = get_theme_mod('prt_hero_chip2', __('♿ Accessible first', 'pressroot'));
  $portrait  = get_theme_mod('prt_hero_portrait'); // attachment URL

  // Real-photo background (Customizer → Hero, or the setup wizard's Design
  // step). A dark overlay is layered over the image so the white headline
  // stays WCAG-AA readable on any photo; the gradient ground remains the
  // no-image fallback. Overlay is clamped to a floor of 35% whenever an
  // image is present — below that, white-on-photo contrast can't be
  // guaranteed on bright images.
  $heroBg      = get_theme_mod('prt_home_hero_bg', '');
  $heroOverlay = min(90, max($heroBg ? 35 : 0, absint(get_theme_mod('prt_home_hero_overlay', 60))));
  $heroGround  = $heroBg
      ? 'linear-gradient(180deg, rgba(16,12,32,' . ($heroOverlay / 100) . '), rgba(16,12,32,' . min(0.95, $heroOverlay / 100 + 0.12) . ")), url('" . esc_url($heroBg) . "') center/cover no-repeat"
      : 'radial-gradient(1200px 500px at 80% -10%, rgba(108,76,241,.35), transparent 60%), radial-gradient(900px 500px at 10% 10%, rgba(255,77,157,.22), transparent 55%), linear-gradient(180deg,#201B3A,#15122a)';

  // Transparent-overlay header ("nav over image"): when active on the front
  // page the fixed header floats above this hero, so give the hero enough
  // top padding that the headline clears the bar instead of hiding under it.
  $trMode     = get_theme_mod('prt_header_transparent', 'none');
  $overlayNav = $trMode === 'all' || ($trMode === 'front' && is_front_page());
  $padTop     = $overlayNav ? 140 : 70;
@endphp

<section style="position:relative; overflow:hidden; padding:{{ $padTop }}px 32px 80px; color:#fff; background:{!! $heroGround !!};">
  <div class="prt-wrap" style="position:relative; padding:0;">
    <div style="display:grid; grid-template-columns:1.3fr 0.9fr; gap:40px; align-items:center;">
      <div style="animation:prt-fadeUp .7s ease both;">
        @if($avail && $eyebrow !== '')
          <div style="display:inline-flex; align-items:center; gap:9px; font-family:var(--font-mono); letter-spacing:2px; text-transform:uppercase; font-size:13px; font-weight:600; color:#b9a7ff; margin-bottom:26px;">
            <span style="width:8px; height:8px; border-radius:50%; background:#37E29A; animation:prt-bob 1.6s ease-in-out infinite;"></span> {{ $eyebrow }}
          </div>
        @endif
        <h1 style="font-family:var(--font-display); font-weight:800; font-size:clamp(48px,7vw,88px); line-height:.98; letter-spacing:-.03em; margin:0 0 24px; color:#fff;">
          {{ $headline }}<br>
          @if($accent !== '')<span style="background:linear-gradient(90deg,#C9B8FF,#FF9DC4,#FFC08A); -webkit-background-clip:text; background-clip:text; -webkit-text-fill-color:transparent; color:transparent;">{{ $accent }}</span>@endif
          @if($serif !== '')<span style="font-family:var(--font-serif); font-style:italic; font-weight:400;">{{ $serif }}</span>@endif
          {{ $suffix }}
        </h1>
        <p style="font-family:var(--font-display); font-size:20px; line-height:1.55; max-width:30em; color:#e2ddf5; margin:0 0 34px;">{{ $sub }}</p>
        <div style="display:flex; gap:14px; flex-wrap:wrap;">
          @if($btn1Text !== '')
            <a href="{{ esc_url($btn1Url) }}" class="prt-lift prt-btn-grad" style="padding:17px 30px; font-size:16px; font-family:var(--font-display);">{{ $btn1Text }}</a>
          @endif
          @if($btn2Text !== '')
            <a href="{{ esc_url($btn2Url) }}" class="prt-lift prt-btn-ghost-dark" style="padding:17px 30px; font-size:16px; font-family:var(--font-display);">{{ $btn2Text }}</a>
          @endif
        </div>
      </div>

      <div style="position:relative; height:440px;">
        <div style="position:absolute; inset:0; margin:auto; width:300px; height:380px; border:2px solid rgba(255,255,255,.22); border-radius:28px; overflow:hidden; display:flex; align-items:flex-end; padding:18px; animation:prt-blob 12s ease-in-out infinite; {{ $portrait ? "background:url('".esc_url($portrait)."') center/cover;" : "background:repeating-linear-gradient(135deg,rgba(108,76,241,.30) 0 16px,rgba(255,77,157,.18) 16px 32px);" }}">
          @unless($portrait)<span style="font-family:var(--font-mono); font-size:12px; color:#b9a7ff;">{{ __('[ add an image in Customizer → Hero ]', 'pressroot') }}</span>@endunless
        </div>
        @if($chip1 !== '')
          <div style="position:absolute; top:8px; right:14px; background:#37E29A; color:#17151F; padding:12px 16px; border-radius:16px; font-weight:800; font-size:15px; transform:rotate(8deg); box-shadow:0 8px 22px rgba(0,0,0,.35); animation:prt-floatA 5s ease-in-out infinite; font-family:var(--font-display);">{{ $chip1 }}</div>
        @endif
        @if($chip2 !== '')
          {{-- Dark ink text on the orange chip: white-on-#FF7A3D was ~2.5:1,
               well under WCAG AA; ink (#17151F) on the same orange is ~7:1. --}}
          <div style="position:absolute; bottom:24px; left:0; background:#FF7A3D; color:#17151F; padding:12px 16px; border-radius:16px; font-weight:800; font-size:15px; transform:rotate(-6deg); box-shadow:0 8px 22px rgba(0,0,0,.35); animation:prt-floatB 6s ease-in-out infinite; font-family:var(--font-display);">{{ $chip2 }}</div>
        @endif
        <div style="position:absolute; bottom:90px; right:-6px; width:54px; height:54px; border-radius:50%; background:#22CFEE; animation:prt-floatA 7s ease-in-out infinite;"></div>
      </div>
    </div>
  </div>
</section>
