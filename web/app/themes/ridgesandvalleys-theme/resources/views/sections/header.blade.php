@php($topbar = get_theme_mod('rv_topbar_text', __('Gettysburg &amp; South Central PA', 'sage')))
@php($ctaText = get_theme_mod('rv_cta_text', __('Get a quote', 'sage')))
@php($ctaUrl = get_theme_mod('rv_cta_url', '/contact/'))
@php($rvPhone = trim(get_theme_mod('rv_contact_phone', '223-340-8098')))
@php($rvEmail = trim(get_theme_mod('rv_contact_email', 'matt@ridgesandvalleys.com')))
@php($rvPhoneTel = $rvPhone ? '+1' . preg_replace('/\D/', '', $rvPhone) : '')

<style>
  /* Contact block in the mobile off-canvas menu */
  .rv-offcanvas-contact{margin-block-start:1.25rem;padding-block-start:1rem;border-block-start:1px solid color-mix(in srgb, currentColor 15%, transparent)}
  .rv-offcanvas-contact .rv-offcanvas-contact-label{display:block;font-size:.72rem;letter-spacing:.12em;text-transform:uppercase;opacity:.6;margin-block-end:.55rem}
  .rv-offcanvas-contact .rv-contact-list{list-style:none;margin:0;padding:0;display:flex;flex-direction:column;gap:.65rem}
  .rv-offcanvas-contact .rv-contact-link{display:inline-flex;align-items:center;gap:.6rem;color:inherit;text-decoration:none;font-size:1.05rem;line-height:1.4;overflow-wrap:anywhere}
  .rv-offcanvas-contact .rv-contact-link:hover,.rv-offcanvas-contact .rv-contact-link:focus-visible{text-decoration:underline;text-underline-offset:.15em}
  .rv-offcanvas-contact .rv-contact-link svg{flex:0 0 auto;width:1.25em;height:1.25em;opacity:.85}
</style>

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

    @if ($rvPhone || $rvEmail)
      <div class="rv-offcanvas-contact">
        <span class="rv-offcanvas-contact-label">{{ __('Contact', 'sage') }}</span>
        <ul class="rv-contact-list">
          @if ($rvPhone)
            <li>
              <a class="rv-contact-link" href="tel:{{ $rvPhoneTel }}">
                <svg viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="1.8" stroke-linecap="round" stroke-linejoin="round" aria-hidden="true" focusable="false"><path d="M22 16.9v3a2 2 0 0 1-2.2 2 19.8 19.8 0 0 1-8.6-3.1 19.5 19.5 0 0 1-6-6 19.8 19.8 0 0 1-3.1-8.7A2 2 0 0 1 4.1 2h3a2 2 0 0 1 2 1.7c.1 1 .4 1.9.7 2.8a2 2 0 0 1-.5 2.1L8.1 9.9a16 16 0 0 0 6 6l1.3-1.3a2 2 0 0 1 2.1-.5c.9.3 1.8.6 2.8.7a2 2 0 0 1 1.7 2z"/></svg>
                <span>{{ $rvPhone }}</span>
              </a>
            </li>
          @endif
          @if ($rvEmail)
            <li>
              <a class="rv-contact-link" href="mailto:{!! antispambot($rvEmail) !!}">
                <svg viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="1.8" stroke-linecap="round" stroke-linejoin="round" aria-hidden="true" focusable="false"><rect x="3" y="5" width="18" height="14" rx="2"/><path d="m3 7 9 6 9-6"/></svg>
                <span>{!! antispambot($rvEmail) !!}</span>
              </a>
            </li>
          @endif
        </ul>
      </div>
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
        var fit = function () {
          hero.style.paddingTop = '';
          var cssPad = parseFloat(window.getComputedStyle(hero).paddingTop) || 0;
          hero.style.paddingTop = Math.max(banner.offsetHeight + 56, cssPad) + 'px';
        };
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
