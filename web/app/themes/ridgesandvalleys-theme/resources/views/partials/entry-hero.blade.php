{{--
  Optional full-bleed hero band for the default page / single templates.
  Renders only when the entry's resolved layout enables the hero (per-entry
  "Layout & Hero" box → Customizer default). Background priority: Featured
  Image → per-entry "Hero background image". Matches the bespoke page heroes.
--}}
@php($rvHero = \App\entry_layout())
@if ($rvHero['hero'])
  <section class="rv-hero rv-entry-hero">
    <span class="rv-stripe" aria-hidden="true"></span>
    @if ($rvHero['hero_bg'])
      <span class="rv-hero-bg" style="background-image:url('{{ $rvHero['hero_bg'] }}')" aria-hidden="true"></span>
    @endif
    <div class="rv-shell rv-hero-inner">
      <h1 class="rv-hero-title">{!! wp_kses_post($rvHero['hero_title']) !!}</h1>
      @if (trim($rvHero['hero_sub']) !== '')
        <p class="rv-hero-sub">{{ $rvHero['hero_sub'] }}</p>
      @endif
    </div>
  </section>
@endif
