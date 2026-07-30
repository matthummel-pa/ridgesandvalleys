@php($topbar = get_theme_mod('rv_topbar_text', __('Gettysburg &amp; South Central PA', 'sage')))
@php($ctaText = get_theme_mod('rv_cta_text', __('Get a quote', 'sage')))
@php($ctaUrl = get_theme_mod('rv_cta_url', '/contact/'))

<header class="rv-banner" role="banner">
  <a class="rv-skip-link" href="#main">{{ __('Skip to content', 'sage') }}</a>
@php($topbarSocial = get_theme_mod('rv_topbar_social', true) ? \App\social_links('rv-social rv-topbar-social') : '')
@if ($topbar || $topbarSocial)
  <div class="rv-topbar">
    <div class="rv-shell rv-topbar-inner">
      @if ($topbar)<p class="rv-topbar-note">{!! wp_kses_post($topbar) !!}</p>@else<span aria-hidden="true"></span>@endif
      {!! $topbarSocial !!}
    </div>
  </div>
@endif

<div class="rv-header">
  <div class="rv-shell rv-header-inner">
    <div class="rv-brand">
      {!! \App\brand_logo('header') !!}
    </div>

    <nav class="rv-nav" aria-label="{{ __('Primary', 'sage') }}">
      @if (has_nav_menu('primary'))
        {!! wp_nav_menu(['theme_location' => 'primary', 'container' => false, 'menu_class' => 'rv-nav-list', 'echo' => false, 'depth' => 2]) !!}
      @endif
    </nav>

    <div class="rv-header-actions">
      @if (get_theme_mod('rv_dark_toggle_enable', true))
      <button type="button" class="rv-theme-toggle rv-theme-toggle--{{ get_theme_mod('rv_toggle_style', 'button') }}" aria-pressed="false" aria-label="{{ __('Toggle dark mode', 'sage') }}" title="{{ __('Toggle dark mode', 'sage') }}">
        {!! \App\theme_toggle_icons(get_theme_mod('rv_dark_toggle_icon', 'sun-moon')) !!}
        <span class="screen-reader-text">{{ __('Toggle dark mode', 'sage') }}</span>
      </button>
      @endif
      @if ($ctaText)
        <a class="rv-btn rv-btn-primary rv-btn-cta" href="{{ \App\cta_href($ctaUrl) }}">{{ $ctaText }}</a>
      @endif
      <button type="button" class="rv-menu-toggle" aria-expanded="false" aria-controls="rv-offcanvas">
        {!! \App\icon('menu') !!}
        <span class="screen-reader-text">{{ __('Menu', 'sage') }}</span>
      </button>
    </div>
  </div>
  </div>{{-- .rv-header (sticky) --}}
</header>

<div id="rv-offcanvas" class="rv-offcanvas" hidden>
  <div class="rv-offcanvas-panel" role="dialog" aria-modal="true" aria-label="{{ __('Site menu', 'sage') }}">
    <button type="button" class="rv-offcanvas-close" aria-label="{{ __('Close menu', 'sage') }}">{!! \App\oc_close_svg() !!}</button>
    @if (has_nav_menu('primary'))
      {!! wp_nav_menu(['theme_location' => 'primary', 'container' => false, 'menu_class' => 'rv-offcanvas-list', 'echo' => false, 'depth' => 2]) !!}
    @endif
    {!! \App\social_links('rv-social rv-offcanvas-social') !!}
  </div>
</div>

@if (get_theme_mod('rv_header_transparent', false) || get_theme_mod('rv_topbar_hide_on_scroll', false))
<script>
(function () {
  document.addEventListener('DOMContentLoaded', function () {
    var body = document.body;
    var banner = document.querySelector('.rv-banner');
    // Transparent header: overlay the hero and pad the content below it.
    if (body.classList.contains('rv-nav-transparent')) {
      var hero = document.querySelector('.rv-hero');
      if (!hero || !banner) {
        body.classList.remove('rv-nav-transparent'); // no hero — fall back to solid
      } else {
        var fit = function () { hero.style.paddingTop = (banner.offsetHeight + 24) + 'px'; };
        fit();
        window.addEventListener('resize', fit, { passive: true });
      }
    }
    // Shared scroll flag — drives the transparent solidify + hide-top-bar-on-scroll.
    var onScroll = function () {
      body.classList.toggle('rv-scrolled', (window.scrollY || window.pageYOffset) > 30);
    };
    window.addEventListener('scroll', onScroll, { passive: true });
    onScroll();
  });
})();
</script>
@endif
