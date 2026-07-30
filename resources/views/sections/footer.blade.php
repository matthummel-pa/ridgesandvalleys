@php($tagline = get_theme_mod('rv_footer_tagline', __('Websites that help South Central PA businesses get found and get work.', 'sage')))

<footer class="rv-footer" role="contentinfo">
  <span class="rv-stripe" aria-hidden="true"></span>
  @if (get_theme_mod('rv_footer_cta_enable', true))
    <section class="rv-fcta rv-fcta--{{ get_theme_mod('rv_footer_cta_style', 'pine') }}">
      <div class="rv-shell rv-fcta-inner">
        <div class="rv-fcta-text">
          {!! \App\eyebrow(get_theme_mod('rv_footer_cta_eyebrow', __('Let’s build something', 'sage'))) !!}
          <h2 class="rv-fcta-title">{{ get_theme_mod('rv_footer_cta_title', __('Ready for a website that works as hard as you do?', 'sage')) }}</h2>
          @php($fctaSub = get_theme_mod('rv_footer_cta_sub', __('Tell me about your business and I’ll show you what’s possible — no pressure, no jargon.', 'sage')))
          @if ($fctaSub)<p class="rv-fcta-sub">{{ $fctaSub }}</p>@endif
        </div>
        <a class="rv-btn rv-fcta-btn" href="{{ \App\cta_href(get_theme_mod('rv_footer_cta_url', get_theme_mod('rv_cta_url', '/contact/')) ?: '/contact/') }}">{{ get_theme_mod('rv_footer_cta_btn', __('Start your project', 'sage')) }}</a>
      </div>
    </section>
  @endif
  <div class="rv-shell rv-footer-inner">
    <div class="rv-footer-brand">
      @if (\App\has_brand_logo())
        {!! \App\brand_logo('footer') !!}
      @endif
      <p class="rv-footer-name">{!! $siteName !!}</p>
      @if ($tagline)
        <p class="rv-footer-tagline">{!! wp_kses_post($tagline) !!}</p>
      @endif
      {!! \App\social_links('rv-social rv-footer-social') !!}
    </div>

    @if (has_nav_menu('footer'))
      <nav class="rv-footer-nav" aria-label="{{ __('Explore', 'sage') }}">
        <h2 class="rv-footer-heading">{{ __('Explore', 'sage') }}</h2>
        {!! wp_nav_menu(['theme_location' => 'footer', 'container' => false, 'menu_class' => 'rv-footer-list', 'echo' => false, 'depth' => 1]) !!}
      </nav>
    @endif

    @if (has_nav_menu('tools'))
      <nav class="rv-footer-nav" aria-label="{{ __('Free tools', 'sage') }}">
        <h2 class="rv-footer-heading">{{ __('Free tools', 'sage') }}</h2>
        {!! wp_nav_menu(['theme_location' => 'tools', 'container' => false, 'menu_class' => 'rv-footer-list', 'echo' => false, 'depth' => 1]) !!}
      </nav>
    @endif

    @php($rvArchives = wp_get_archives(['type' => 'monthly', 'limit' => 6, 'echo' => 0]))
    @php($rvCats = wp_list_categories(['title_li' => '', 'number' => 8, 'echo' => 0, 'hide_empty' => true]))
    @if ($rvArchives || $rvCats)
      <div class="rv-footer-nav rv-footer-browse">
        @if ($rvArchives)
          <h2 class="rv-footer-heading">{{ __('Archives', 'sage') }}</h2>
          <ul class="rv-footer-list">{!! $rvArchives !!}</ul>
        @endif
        @if ($rvCats)
          <h2 class="rv-footer-heading rv-footer-subheading">{{ __('Categories', 'sage') }}</h2>
          <ul class="rv-footer-list">{!! $rvCats !!}</ul>
        @endif
      </div>
    @endif
  </div>

  <div class="rv-shell rv-footer-bottom">
    <p class="rv-copyright">&copy; {{ date('Y') }} {!! $siteName !!}. {!! wp_kses_post(get_theme_mod('rv_footer_bottom_text', __('Built local in Adams County, PA.', 'sage'))) !!}</p>
  </div>
</footer>
