@php($tagline = get_theme_mod('rv_footer_tagline', __('Websites that help South Central PA businesses get found and get work.', 'sage')))
@php($rvPhone = trim(get_theme_mod('rv_contact_phone', '223-340-8098')))
@php($rvEmail = trim(get_theme_mod('rv_contact_email', 'matt@ridgesandvalleys.com')))
@php($rvLocation = trim(get_theme_mod('rv_contact_location', 'Gettysburg, PA')))
@php($rvPhoneTel = $rvPhone ? '+1' . preg_replace('/\D/', '', $rvPhone) : '')

{{-- Newsletter → native HubSpot form (Portal 246820093 · Form a31982d1-52e4-4937-a0d9-f81452f933bd · region na2). --}}
@php($hsPortal = get_theme_mod('rv_hubspot_portal_id', '246820093'))
@php($hsForm = get_theme_mod('rv_hubspot_form_guid', 'a31982d1-52e4-4937-a0d9-f81452f933bd'))
@php($hsRegion = get_theme_mod('rv_hubspot_region', 'na2'))
@php($rvNewsletter = get_theme_mod('rv_footer_newsletter_enable', true))

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

  /* Newsletter strip — full-width in footer, responsive on all devices */
  .rv-footer-news{display:flex;flex-wrap:wrap;gap:1rem 2rem;align-items:center;justify-content:space-between;
    padding-block:clamp(1.25rem,3vw,1.9rem);border-block-end:1px solid rgba(255,255,255,.12)}
  .rv-footer-news-copy{flex:1 1 18rem;min-width:0}
  .rv-footer-news-title{margin:0}
  .rv-footer-news-sub{margin:.35rem 0 0;opacity:.85;font-size:.95rem;line-height:1.45;max-width:46ch}
  .rv-footer-news-form{flex:1 1 20rem;max-width:34rem;min-width:0}
  @media (max-width:720px){
    .rv-footer-news{flex-direction:column;align-items:stretch;gap:.9rem}
    .rv-footer-news-form{max-width:none}
  }

  /* HubSpot embedded form — branded to match the dark footer (css:'' disables HubSpot's default styles) */
  .rv-hs-form .hs-form{max-width:34rem}
  .rv-hs-form .hs-form fieldset{max-width:none;margin:0}
  .rv-hs-form .hs-form .hs-form-field{margin:0 0 .7rem}
  .rv-hs-form .hs-form label{display:block;font-size:.82rem;font-weight:600;opacity:.9;margin:0 0 .3rem;color:#EAFFF7}
  .rv-hs-form .hs-form label .hs-form-required{color:#FF7A66;margin-inline-start:.15rem}
  .rv-hs-form .hs-form .hs-input,
  .rv-hs-form .hs-form input[type=email],
  .rv-hs-form .hs-form input[type=text],
  .rv-hs-form .hs-form input[type=tel]{
    width:100%;box-sizing:border-box;padding:.72rem .95rem;border-radius:.6rem;
    border:1px solid rgba(255,255,255,.28)!important;background:rgba(255,255,255,.08)!important;
    color:#EAFFF7!important;font:inherit;line-height:1.2}
  .rv-hs-form .hs-form .hs-input::placeholder{color:rgba(255,255,255,.55)}
  .rv-hs-form .hs-form .hs-input:focus,
  .rv-hs-form .hs-form .hs-input:focus-visible{outline:2px solid rgba(124,243,201,.6);outline-offset:1px;border-color:rgba(124,243,201,.6)!important}
  .rv-hs-form .hs-form .inputs-list{list-style:none;margin:0;padding:0}
  .rv-hs-form .hs-form .hs-button,
  .rv-hs-form .hs-form input[type=submit]{
    display:inline-block;margin-top:.35rem;background:#FF5A4D!important;color:#fff!important;border:0;cursor:pointer;
    font:inherit;font-weight:800;padding:.8rem 1.6rem;border-radius:100px;
    box-shadow:0 .6rem 1.4rem rgba(255,90,77,.35);transition:transform .12s ease,filter .12s ease}
  .rv-hs-form .hs-form .hs-button:hover{filter:brightness(1.06)}
  .rv-hs-form .hs-form .hs-button:active{transform:translateY(1px)}
  .rv-hs-form .hs-error-msgs,.rv-hs-form .hs-error-msg,.rv-hs-form .hs-form .hs-error-msgs li{color:#FFC9C2;font-size:.82rem;margin:.3rem 0 0;list-style:none;padding:0}
  .rv-hs-form .submitted-message,.rv-hs-form .hs-form__submitted-message{color:#7CF3C9;font-weight:700;font-size:.98rem;line-height:1.4}
  .rv-hs-form .hs-form .legal-consent-container,.rv-hs-form .hs-form .hs-richtext{font-size:.78rem;opacity:.8;margin-top:.4rem}
  .rv-hs-form .hs-form .legal-consent-container a{color:#7CF3C9}
  @media (min-width:721px){ .rv-hs-form .hs-form .hs_submit{display:inline-block} }
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

  @if ($rvNewsletter)
    {{-- Newsletter (moved here from the Journal page) — native HubSpot form --}}
    <div class="rv-shell rv-footer-news">
      <div class="rv-footer-news-copy">
        <h2 class="rv-footer-heading rv-footer-news-title">{{ __('Local, useful, no spam.', 'sage') }}</h2>
        <p class="rv-footer-news-sub">{{ __('Occasional notes on getting found and getting work online in South Central PA — once a month, tops.', 'sage') }}</p>
      </div>
      <div class="rv-footer-news-form">
        <div id="rv-footer-hs-form" class="rv-hs-form"></div>
      </div>
    </div>
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

  @if ($rvNewsletter)
    <script src="//js.hsforms.net/forms/embed/v2.js" charset="utf-8"></script>
    <script>
      (function () {
        function build() {
          if (window.hbspt && window.hbspt.forms) {
            window.hbspt.forms.create({
              portalId: @json((string) $hsPortal),
              formId: @json((string) $hsForm),
              region: @json((string) $hsRegion),
              target: "#rv-footer-hs-form",
              css: ""
            });
          } else {
            setTimeout(build, 250);
          }
        }
        build();
      })();
    </script>
  @endif
</footer>
