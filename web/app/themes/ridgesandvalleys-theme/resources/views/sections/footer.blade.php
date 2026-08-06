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
  .rv-fnews-title{margin:0;font-size:clamp(1.3rem,2.3vw,1.8rem);line-height:1.14}
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
  @media (max-width:760px){
    .rv-fnews{flex-direction:column;align-items:stretch;gap:.9rem}
    .rv-fnews-form{flex-basis:auto}
    .rv-fnews-btn{width:100%}
  }

  /* Newsletter popup — full HubSpot form on a light card */
  .rv-news-modal[hidden]{display:none}
  .rv-news-modal{position:fixed;inset:0;z-index:1000;display:grid;place-items:center;padding:1.25rem}
  .rv-news-modal__backdrop{position:absolute;inset:0;background:rgba(18,30,25,.6)}
  .rv-news-modal__dialog{position:relative;z-index:1;width:min(31rem,100%);max-height:90vh;overflow:auto;
    background:#fff;color:#23201b;border:1px solid rgba(0,0,0,.08);
    border-radius:20px;box-shadow:0 24px 60px rgba(0,0,0,.4);padding:clamp(1.5rem,3vw,2.1rem)}
  .rv-news-modal__dialog .rv-fnews-eyebrow{color:#9d4a31;margin-bottom:.9rem}
  .rv-news-modal__title{margin:.1rem 0 .3rem;font-family:var(--font-display);font-size:1.5rem;line-height:1.15;color:#1e3a31}
  .rv-news-modal__sub{margin:0 0 1.1rem;color:#4f4a40;font-size:.95rem;line-height:1.45}
  .rv-news-modal__close{position:absolute;top:.7rem;right:.7rem;width:2.2rem;height:2.2rem;display:grid;place-items:center;
    border:0;border-radius:100px;background:rgba(0,0,0,.06);color:#23201b;cursor:pointer;font-size:1.4rem;line-height:1}
  .rv-news-modal__close:hover{background:rgba(0,0,0,.12)}
  .rv-news-modal__close:focus-visible{outline:2px solid var(--color-pine);outline-offset:2px}

  /* HubSpot form inside the popup — branded for the light card (applies when the
     embed renders inline; HubSpot's own iframe styling is used if it doesn't). */
  .rv-news-hs .hs-form{max-width:none}
  .rv-news-hs .hs-form fieldset{max-width:none;margin:0}
  .rv-news-hs .hs-form .hs-form-field{margin:0 0 .8rem}
  .rv-news-hs .hs-form label{display:block;font-size:.85rem;font-weight:600;color:#23201b;margin:0 0 .3rem}
  .rv-news-hs .hs-form label .hs-form-required{color:#b0553a;margin-inline-start:.15rem}
  .rv-news-hs .hs-form .hs-input,
  .rv-news-hs .hs-form input[type=email],
  .rv-news-hs .hs-form input[type=text],
  .rv-news-hs .hs-form input[type=tel]{width:100%;box-sizing:border-box;padding:.7rem .9rem;border-radius:.6rem;
    border:1px solid rgba(0,0,0,.22)!important;background:#fff!important;color:#23201b!important;font:inherit;line-height:1.2}
  .rv-news-hs .hs-form .hs-input:focus,.rv-news-hs .hs-form .hs-input:focus-visible{outline:2px solid var(--color-pine)!important;outline-offset:1px;border-color:var(--color-pine)!important}
  .rv-news-hs .hs-form .inputs-list{list-style:none;margin:0;padding:0}
  .rv-news-hs .hs-form .hs-button,
  .rv-news-hs .hs-form input[type=submit]{display:inline-block;margin-top:.4rem;background:var(--color-clay)!important;color:#fff!important;
    border:0;cursor:pointer;font:inherit;font-weight:800;padding:.8rem 1.6rem;border-radius:100px;box-shadow:0 .5rem 1.2rem rgba(176,85,58,.3)}
  .rv-news-hs .hs-form .hs-button:hover{filter:brightness(1.05)}
  .rv-news-hs .hs-error-msgs li,.rv-news-hs .hs-error-msg{color:#b0553a;font-size:.82rem;list-style:none;margin:.3rem 0 0;padding:0}
  .rv-news-hs .submitted-message,.rv-news-hs .hs-form__submitted-message{color:#2e5245;font-weight:700;font-size:.98rem;line-height:1.4}
  .rv-news-hs .legal-consent-container,.rv-news-hs .hs-richtext{font-size:.78rem;color:#6e6558;margin-top:.4rem}
  .rv-news-hs .legal-consent-container a{color:#2e5245}
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
          <input class="rv-fnews-input" id="rv-fnews-email" type="email" name="email" autocomplete="email" inputmode="email" placeholder="{{ __('you@yourbusiness.com', 'sage') }}">
          <button class="rv-fnews-btn" type="submit">{{ __('Sign me up', 'sage') }}</button>
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
    <div class="rv-news-modal" id="rv-news-modal" hidden>
      <div class="rv-news-modal__backdrop" data-rv-news-close></div>
      <div class="rv-news-modal__dialog" role="dialog" aria-modal="true" aria-label="{{ __('Newsletter signup', 'sage') }}">
        <button type="button" class="rv-news-modal__close" data-rv-news-close aria-label="{{ __('Close', 'sage') }}">&times;</button>
        <p class="rv-fnews-eyebrow rv-news-modal__eyebrow">{{ __('The newsletter', 'sage') }}</p>
        <div id="rv-news-hs-form" class="rv-news-hs"></div>
      </div>
    </div>

    <script src="//js.hsforms.net/forms/embed/v2.js" charset="utf-8"></script>
    <script>
      (function () {
        var modal = document.getElementById('rv-news-modal');
        var form = document.getElementById('rv-fnews-form');
        var emailField = document.getElementById('rv-fnews-email');
        if (!modal || !form) return;
        var built = false, lastFocus = null, prefill = '';

        function buildForm() {
          if (built) return;
          (function make() {
            if (window.hbspt && window.hbspt.forms) {
              built = true;
              window.hbspt.forms.create({
                portalId: @json((string) $hsPortal),
                formId: @json((string) $hsForm),
                region: @json((string) $hsRegion),
                target: '#rv-news-hs-form',
                css: '',
                onFormReady: function () {
                  try {
                    if (!prefill) return;
                    var el = document.querySelector('#rv-news-hs-form input[type=email], #rv-news-hs-form input[name="email"]');
                    if (el) { el.value = prefill; el.dispatchEvent(new Event('input', { bubbles: true })); el.dispatchEvent(new Event('change', { bubbles: true })); }
                  } catch (e) {}
                }
              });
            } else { setTimeout(make, 200); }
          })();
        }

        function openModal() {
          lastFocus = document.activeElement;
          prefill = emailField ? emailField.value.trim() : '';
          buildForm();
          modal.hidden = false;
          document.documentElement.style.overflow = 'hidden';
          var closeBtn = modal.querySelector('.rv-news-modal__close');
          if (closeBtn) closeBtn.focus();
        }
        function closeModal() {
          modal.hidden = true;
          document.documentElement.style.overflow = '';
          if (lastFocus && lastFocus.focus) lastFocus.focus();
        }

        form.addEventListener('submit', function (e) { e.preventDefault(); openModal(); });
        modal.addEventListener('click', function (e) {
          if (e.target.hasAttribute('data-rv-news-close')) closeModal();
        });
        document.addEventListener('keydown', function (e) {
          if (e.key === 'Escape' && !modal.hidden) closeModal();
        });
        modal.addEventListener('keydown', function (e) {
          if (e.key !== 'Tab') return;
          var sel = 'button, [href], input, select, textarea, [tabindex]:not([tabindex="-1"])';
          var items = Array.prototype.slice.call(modal.querySelectorAll(sel)).filter(function (el) { return el.offsetParent !== null; });
          if (!items.length) return;
          var first = items[0], last = items[items.length - 1];
          if (e.shiftKey && document.activeElement === first) { e.preventDefault(); last.focus(); }
          else if (!e.shiftKey && document.activeElement === last) { e.preventDefault(); first.focus(); }
        });
      })();
    </script>
  @endif
</footer>
