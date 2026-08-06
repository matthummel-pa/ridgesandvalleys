@php($tagline = get_theme_mod('rv_footer_tagline', __('Websites that help South Central PA businesses get found and get work.', 'sage')))
@php($rvPhone = trim(get_theme_mod('rv_contact_phone', '223-340-8098')))
@php($rvEmail = trim(get_theme_mod('rv_contact_email', 'matt@ridgesandvalleys.com')))
@php($rvLocation = trim(get_theme_mod('rv_contact_location', 'Gettysburg, PA')))
@php($rvPhoneTel = $rvPhone ? '+1' . preg_replace('/\D/', '', $rvPhone) : '')

<style>
  /* Contact block — footer first column + mobile off-canvas (fluid, inherits color) */
  .rv-contact-list{list-style:none;margin:0;padding:0;display:flex;flex-direction:column;gap:.45rem}
  .rv-contact-link{display:inline-flex;align-items:center;gap:.55rem;color:inherit;text-decoration:none;line-height:1.4;overflow-wrap:anywhere}
  .rv-contact-link:hover,.rv-contact-link:focus-visible{text-decoration:underline;text-underline-offset:.15em}
  .rv-contact-link svg{flex:0 0 auto;width:1.15em;height:1.15em;opacity:.85}
  .rv-footer-contact{margin-block:clamp(1rem,2.5vw,1.35rem)}
  .rv-footer-contact .rv-contact-list{margin-block-start:.5rem}
  .rv-footer-contact .rv-contact-link{font-size:.95rem;opacity:.92}
  .rv-footer-contact .rv-contact-link:hover,.rv-footer-contact .rv-contact-link:focus-visible{opacity:1}
</style>

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

      @if ($rvPhone || $rvEmail || $rvLocation)
        <div class="rv-footer-contact">
          <h2 class="rv-footer-heading rv-footer-contact-heading">{{ __('Contact', 'sage') }}</h2>
          <ul class="rv-contact-list">
            @if ($rvLocation)
              <li>
                <a class="rv-contact-link" href="https://www.google.com/maps/search/?api=1&amp;query={{ rawurlencode($rvLocation) }}" target="_blank" rel="noopener">
                  <svg viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="1.8" stroke-linecap="round" stroke-linejoin="round" aria-hidden="true" focusable="false"><path d="M21 10c0 7-9 13-9 13s-9-6-9-13a9 9 0 0 1 18 0z"/><circle cx="12" cy="10" r="3"/></svg>
                  <span>{{ $rvLocation }}</span>
                </a>
              </li>
            @endif
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
                <a class="rv-contact-link" href="mailto:{{ antispambot($rvEmail) }}">
                  <svg viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="1.8" stroke-linecap="round" stroke-linejoin="round" aria-hidden="true" focusable="false"><rect x="3" y="5" width="18" height="14" rx="2"/><path d="m3 7 9 6 9-6"/></svg>
                  <span>{!! antispambot($rvEmail) !!}</span>
                </a>
              </li>
            @endif
          </ul>
        </div>
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
