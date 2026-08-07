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

  /* Newsletter — closing CTA band: copy left, action right */
  .rv-sr-only{position:absolute;width:1px;height:1px;padding:0;margin:-1px;overflow:hidden;clip:rect(0 0 0 0);white-space:nowrap;border:0}
  .rv-fnews{display:flex;flex-wrap:wrap;align-items:center;justify-content:space-between;gap:1.1rem 2.5rem;
    padding:clamp(1.4rem,3vw,2rem) clamp(1.35rem,3vw,2.1rem);margin-block:clamp(1.1rem,3vw,1.9rem);
    border:1px solid rgba(255,255,255,.14);border-radius:18px;background:rgba(255,255,255,.045);position:relative;overflow:hidden}
  .rv-fnews::before{content:"";position:absolute;left:0;top:0;bottom:0;width:4px;background:linear-gradient(180deg,var(--color-pine),var(--color-clay))}
  .rv-fnews-copy{flex:1 1 20rem;min-width:0}
  .rv-fnews-eyebrow{font-family:var(--font-mono);font-size:.72rem;letter-spacing:.14em;text-transform:uppercase;color:var(--color-wheat);font-weight:600;margin:0 0 .35rem}
  .rv-fnews-title{margin:0;color:#f7f1e6;font-size:clamp(1.3rem,2.3vw,1.8rem);line-height:1.14}
  .rv-fnews-sub{margin:.4rem 0 0;opacity:.85;font-size:.95rem;line-height:1.45;max-width:46ch}
  .rv-fnews-form{flex:0 1 24rem;min-width:min(100%,17rem);display:flex;gap:.6rem;flex-wrap:wrap}
  .rv-fnews-input{flex:1 1 12rem;min-width:0;box-sizing:border-box;padding:.8rem 1rem;border-radius:100px;
    border:1px solid rgba(255,255,255,.28);background:rgba(255,255,255,.08);color:#EAFFF7;font:inherit;line-height:1.2}
  .rv-fnews-input::placeholder{color:rgba(255,255,255,.55)}
  .rv-fnews-input:focus,.rv-fnews-input:focus-visible{outline:2px solid var(--color-wheat);outline-offset:1px;border-color:var(--color-wheat)}
  .rv-fnews-btn{flex:0 0 auto;border:0;cursor:pointer;font:inherit;font-weight:800;padding:.8rem 1.5rem;border-radius:100px;
    background:var(--color-clay);color:#fff;box-shadow:0 .5rem 1.2rem rgba(176,85,58,.32);transition:transform .12s ease,filter .12s ease}
  .rv-fnews-btn:hover{filter:brightness(1.06)}
  .rv-fnews-btn:active{transform:translateY(1px)}
  .rv-fnews-status{flex:1 1 100%;margin:.15rem 0 0;font-size:.9rem;line-height:1.4}
  .rv-fnews-status[data-state="ok"]{color:#7CF3C9}
  .rv-fnews-status[data-state="err"]{color:#FFC9C2}
  .rv-fnews-status[data-state="pending"]{color:var(--color-wheat)}
  @media (max-width:760px){
    .rv-fnews{flex-direction:column;align-items:stretch;gap:.9rem}
    .rv-fnews-form{flex-basis:auto}
    .rv-fnews-btn{width:100%}
  }

</style>

<footer class="rv-footer" role="contentinfo">
  <span class="rv-stripe" aria-hidden="true"></span>

  @if ($rvNewsletter)
    <div class="rv-shell">
      <section class="rv-fnews" aria-labelledby="rv-fnews-title">
        <div class="rv-fnews-copy">
          <p class="rv-fnews-eyebrow">{{ __('The newsletter', 'sage') }}</p>
          <h2 id="rv-fnews-title" class="rv-fnews-title">{{ __('Local, useful, no spam.', 'sage') }}</h2>
          <p class="rv-fnews-sub">{{ __('Occasional notes on getting found and getting work online in South Central PA. Once a month, tops.', 'sage') }}</p>
        </div>
        <form class="rv-fnews-form" id="rv-fnews-form" novalidate>
          <label class="rv-sr-only" for="rv-fnews-email">{{ __('Your email address', 'sage') }}</label>
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
                <a class="rv-contact-link" href="mailto:{!! antispambot($rvEmail) !!}">
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
    <script>
      (function () {
        var form = document.getElementById('rv-fnews-form');
        if (!form) return;
        var emailEl = document.getElementById('rv-fnews-email');
        var statusEl = document.getElementById('rv-fnews-status');
        var btn = form.querySelector('.rv-fnews-btn');
        var endpoint = 'https://api.hsforms.com/submissions/v3/integration/submit/' + @json((string) $hsPortal) + '/' + @json((string) $hsForm);
        function show(msg, state) { statusEl.hidden = false; statusEl.textContent = msg; statusEl.setAttribute('data-state', state); }
        form.addEventListener('submit', function (e) {
          e.preventDefault();
          var email = (emailEl.value || '').trim();
          if (!/^[^@\s]+@[^@\s]+\.[^@\s]+$/.test(email)) { show('Please enter a valid email address.', 'err'); emailEl.focus(); return; }
          btn.disabled = true;
          show('Signing you up…', 'pending');
          fetch(endpoint, {
            method: 'POST',
            headers: { 'Content-Type': 'application/json' },
            body: JSON.stringify({ fields: [{ name: 'email', value: email }], context: { pageUri: location.href, pageName: document.title } })
          }).then(function (r) {
            return r.json().catch(function () { return {}; }).then(function (j) { return { ok: r.ok, j: j }; });
          }).then(function (res) {
            btn.disabled = false;
            if (res.ok) {
              form.reset();
              var m = (res.j && res.j.inlineMessage) ? res.j.inlineMessage.replace(/<[^>]*>/g, '') : 'Thanks — you are on the list.';
              show(m, 'ok');
            } else {
              var msg = 'Something went wrong. Please try again.';
              if (res.j && res.j.errors && res.j.errors[0] && res.j.errors[0].message) msg = res.j.errors[0].message;
              show(msg, 'err');
            }
          }).catch(function () { btn.disabled = false; show('Network error. Please try again.', 'err'); });
        });
      })();
    </script>
  @endif
</footer>
