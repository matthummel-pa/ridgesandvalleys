@php($tagline = get_theme_mod('rv_footer_tagline', __('Websites that help South Central PA businesses get found and get work.', 'sage')))
@php($rvPhone = trim(get_theme_mod('rv_contact_phone', '223-340-8098')))
@php($rvEmail = trim(get_theme_mod('rv_contact_email', 'matt@ridgesandvalleys.com')))
@php($rvLocation = trim(get_theme_mod('rv_contact_location', 'Gettysburg, PA')))
@php($rvPhoneTel = $rvPhone ? '+1' . preg_replace('/\D/', '', $rvPhone) : '')
@php($ctaText = get_theme_mod('rv_cta_text', __('Get a quote', 'sage')))
@php($ctaUrl = get_theme_mod('rv_cta_url', '/contact/'))
@php($bottomText = get_theme_mod('rv_footer_bottom_text', __('Built local in Adams County, PA.', 'sage')))

{{-- Newsletter → HubSpot forms API (Portal 246820093 · Form a31982d1-52e4-4937-a0d9-f81452f933bd · region na2). --}}
@php($hsPortal = get_theme_mod('rv_hubspot_portal_id', '246820093'))
@php($hsForm = get_theme_mod('rv_hubspot_form_guid', 'a31982d1-52e4-4937-a0d9-f81452f933bd'))
@php($rvNewsletter = get_theme_mod('rv_footer_newsletter_enable', true))
@php($rvArchives = wp_get_archives(['type' => 'monthly', 'limit' => 6, 'echo' => 0]))
@php($rvCats = wp_list_categories(['title_li' => '', 'number' => 8, 'echo' => 0, 'hide_empty' => true]))

<footer class="rv-footer" role="contentinfo">
  <span class="rv-stripe" aria-hidden="true"></span>

  @if ($rvNewsletter)
    <div class="rv-shell rv-footer-nl">
      <section class="rv-fnews" aria-labelledby="rv-fnews-title">
        <div class="rv-fnews-copy">
          <p class="rv-fnews-eyebrow">{{ __('The newsletter', 'sage') }}</p>
          <h2 id="rv-fnews-title" class="rv-fnews-title">{{ __('Local, useful, no spam.', 'sage') }}</h2>
          <p class="rv-fnews-sub">{{ __('Occasional notes on getting found and getting work online in South Central PA. Once a month, tops.', 'sage') }}</p>
        </div>
        <form class="rv-fnews-form" id="rv-fnews-form" data-hs-portal="{{ $hsPortal }}" data-hs-form="{{ $hsForm }}" novalidate>
          <label class="screen-reader-text" for="rv-fnews-email">{{ __('Your email address', 'sage') }}</label>
          <input class="rv-fnews-input" id="rv-fnews-email" type="email" name="email" autocomplete="email" inputmode="email" required placeholder="{{ __('you@yourbusiness.com', 'sage') }}">
          <button class="rv-fnews-btn" type="submit">{{ __('Sign me up', 'sage') }}</button>
          <p class="rv-fnews-status" id="rv-fnews-status" role="status" aria-live="polite" hidden></p>
        </form>
      </section>
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

      <a class="rv-btn rv-btn-on-dark rv-footer-quote" href="{{ \App\cta_href($ctaUrl) }}">{{ $ctaText }}</a>

      @if ($rvPhone || $rvEmail || $rvLocation)
        <div class="rv-footer-contact">
          <h2 class="rv-footer-heading rv-footer-contact-heading">{{ __('Contact', 'sage') }}</h2>
          <ul class="rv-footer-chips">
            @if ($rvPhone)
              <li>
                <a class="rv-footer-chip" href="tel:{{ $rvPhoneTel }}">
                  <span class="rv-footer-chip-ico" aria-hidden="true">
                    <svg viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="1.8" stroke-linecap="round" stroke-linejoin="round" focusable="false"><path d="M22 16.9v3a2 2 0 0 1-2.2 2 19.8 19.8 0 0 1-8.6-3.1 19.5 19.5 0 0 1-6-6 19.8 19.8 0 0 1-3.1-8.7A2 2 0 0 1 4.1 2h3a2 2 0 0 1 2 1.7c.1 1 .4 1.9.7 2.8a2 2 0 0 1-.5 2.1L8.1 9.9a16 16 0 0 0 6 6l1.3-1.3a2 2 0 0 1 2.1-.5c.9.3 1.8.6 2.8.7a2 2 0 0 1 1.7 2z"/></svg>
                  </span>
                  <span class="rv-footer-chip-txt">
                    <span class="rv-footer-chip-k">{{ __('Call', 'sage') }}</span>
                    <span class="rv-footer-chip-v">{{ $rvPhone }}</span>
                  </span>
                </a>
              </li>
            @endif
            @if ($rvEmail)
              <li>
                <a class="rv-footer-chip" href="mailto:{!! antispambot($rvEmail) !!}">
                  <span class="rv-footer-chip-ico" aria-hidden="true">
                    <svg viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="1.8" stroke-linecap="round" stroke-linejoin="round" focusable="false"><rect x="3" y="5" width="18" height="14" rx="2"/><path d="m3 7 9 6 9-6"/></svg>
                  </span>
                  <span class="rv-footer-chip-txt">
                    <span class="rv-footer-chip-k">{{ __('Email', 'sage') }}</span>
                    <span class="rv-footer-chip-v">{!! antispambot($rvEmail) !!}</span>
                  </span>
                </a>
              </li>
            @endif
            @if ($rvLocation)
              <li>
                <a class="rv-footer-chip" href="https://www.google.com/maps/search/?api=1&amp;query={{ rawurlencode($rvLocation) }}" target="_blank" rel="noopener">
                  <span class="rv-footer-chip-ico" aria-hidden="true">
                    <svg viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="1.8" stroke-linecap="round" stroke-linejoin="round" focusable="false"><path d="M21 10c0 7-9 13-9 13s-9-6-9-13a9 9 0 0 1 18 0z"/><circle cx="12" cy="10" r="3"/></svg>
                  </span>
                  <span class="rv-footer-chip-txt">
                    <span class="rv-footer-chip-k">{{ __('Visit', 'sage') }}</span>
                    <span class="rv-footer-chip-v">{{ $rvLocation }}</span>
                  </span>
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
        <details class="rv-footer-acc">
          <summary class="rv-footer-heading">{{ __('Explore', 'sage') }}</summary>
          {!! wp_nav_menu(['theme_location' => 'footer', 'container' => false, 'menu_class' => 'rv-footer-list', 'echo' => false, 'depth' => 1]) !!}
        </details>
      </nav>
    @endif

    @if (has_nav_menu('tools'))
      <nav class="rv-footer-nav" aria-label="{{ __('Free tools', 'sage') }}">
        <details class="rv-footer-acc">
          <summary class="rv-footer-heading">{{ __('Free tools', 'sage') }}</summary>
          {!! wp_nav_menu(['theme_location' => 'tools', 'container' => false, 'menu_class' => 'rv-footer-list', 'echo' => false, 'depth' => 1]) !!}
        </details>
      </nav>
    @endif

    @if ($rvArchives || $rvCats)
      <nav class="rv-footer-nav rv-footer-browse" aria-label="{{ __('Browse', 'sage') }}">
        <details class="rv-footer-acc">
          <summary class="rv-footer-heading">{{ __('Browse', 'sage') }}</summary>
          <div class="rv-footer-acc-body">
            @if ($rvArchives)
              <h3 class="rv-footer-kicker">{{ __('Archives', 'sage') }}</h3>
              <ul class="rv-footer-list">{!! $rvArchives !!}</ul>
            @endif
            @if ($rvCats)
              <h3 class="rv-footer-kicker">{{ __('Categories', 'sage') }}</h3>
              <ul class="rv-footer-list">{!! $rvCats !!}</ul>
            @endif
          </div>
        </details>
      </nav>
    @endif
  </div>

  <div class="rv-shell rv-footer-bottom">
    <div class="rv-footer-bottom-copy">
      <p class="rv-copyright">&copy; {{ date('Y') }} {!! $siteName !!}.</p>
      @if ($bottomText)
        <p class="rv-footer-local">{!! wp_kses_post($bottomText) !!}</p>
      @endif
    </div>
    <a class="rv-footer-top" href="#top">
      {{ __('Back to top', 'sage') }}
      <svg viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2.2" stroke-linecap="round" stroke-linejoin="round" aria-hidden="true" focusable="false"><path d="M12 19V5"/><path d="m5 12 7-7 7 7"/></svg>
    </a>
  </div>
</footer>
