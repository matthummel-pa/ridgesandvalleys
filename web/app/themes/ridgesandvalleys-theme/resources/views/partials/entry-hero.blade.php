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
      {!! \App\hero_bg_markup() !!}
      {!! \App\hero_credit_markup() !!}
    @endif
    <div class="rv-shell rv-hero-inner">
      @if (is_singular('post'))
        @php($rvSummary = \App\post_summary())
        @php($rvCats = get_the_category())
        <div class="rv-entry-hero-copy">
          <h1 class="rv-hero-title">{!! wp_kses_post($rvHero['hero_title']) !!}</h1>
          @if ($rvSummary !== '')
            <p class="rv-entry-hero-dek">{{ $rvSummary }}</p>
          @endif
          <div class="rv-entry-hero-info">
            @if (! empty($rvCats))
              <a class="rv-entry-hero-category" href="{{ get_category_link($rvCats[0]->term_id) }}">{{ $rvCats[0]->name }}</a>
            @endif
            <ul class="rv-entry-hero-facts" aria-label="{{ __('Article details', 'sage') }}">
              <li><time datetime="{{ get_the_date(DATE_W3C) }}">{{ get_the_date() }}</time></li>
              <li>{{ \App\reading_time() }}</li>
            </ul>
          </div>
          {!! \App\breadcrumbs() !!}
        </div>
      @else
        <h1 class="rv-hero-title">{!! wp_kses_post($rvHero['hero_title']) !!}</h1>
        @if (trim($rvHero['hero_sub'] ?? '') !== '')
          <p class="rv-hero-sub">{{ $rvHero['hero_sub'] }}</p>
        @endif
      @endif
    </div>
  </section>
@endif
