<!doctype html>
<html @php(language_attributes())>
  <head>
    <meta charset="utf-8">
    <meta name="viewport" content="width=device-width, initial-scale=1">
    <link rel="profile" href="https://gmpg.org/xfn/11">
    <script>
      /* Apply the saved (or system) color theme before first paint to avoid a flash. */
      (function () {
@if (get_theme_mod('rv_dark_toggle_enable', true))
        try {
          var mode = {!! wp_json_encode(get_theme_mod('rv_default_mode', 'auto')) !!};
          var saved = localStorage.getItem('rv-theme');
          var dark;
          if (saved) { dark = saved === 'dark'; }
          else if (mode === 'dark') { dark = true; }
          else if (mode === 'light') { dark = false; }
          else { dark = window.matchMedia('(prefers-color-scheme: dark)').matches; }
          document.documentElement.setAttribute('data-theme', dark ? 'dark' : 'light');
        } catch (e) {
          document.documentElement.setAttribute('data-theme', 'light');
        }
@else
        document.documentElement.setAttribute('data-theme', 'light');
@endif
      })();
    </script>
    @php(do_action('get_header'))
    @php(wp_head())

    @vite(['resources/css/app.css', 'resources/js/app.ts'])

    <style>
      /* Guarantee breathing room between page content and the footer on templates
         that don't end in a full-bleed band (blog posts, pages, contact, etc.). */
      .rv-main > .rv-shell-full:last-child { padding-bottom: clamp(3.5rem, 7vw, 6rem); }
      .rv-main > .rv-single:last-child { padding-bottom: clamp(3.5rem, 7vw, 6rem); }
      /* Keep keyboard focus clearly visible on dark sections — the default clay
         focus ring is below the 3:1 non-text contrast minimum on deep-pine backgrounds. */
      .rv-cta-band :focus-visible,
      .rv-band-pine :focus-visible,
      .rv-footer :focus-visible { outline-color: var(--color-wheat); }
      /* Buttons are <a> elements, so the global dark-mode link color otherwise
         repaints their labels sage. Keep the intended solid-button text colors. */
      html[data-theme="dark"] .rv-btn-primary,
      html[data-theme="dark"] .rv-btn-primary:hover { color: #fff; }
      html[data-theme="dark"] .rv-btn-on-dark,
      html[data-theme="dark"] .rv-btn-on-dark:hover { color: #241d10; }
      /* Dark-mode contrast: a few warm accent labels use mid-tone clay/pine that
         fall below the 4.5:1 AA threshold on dark surfaces. Shift them to the
         theme's dark accents (wheat/sage), which clear AA on card and band
         backgrounds. */
      html[data-theme="dark"] .rv-service-tag,
      html[data-theme="dark"] .rv-pkg-flag,
      html[data-theme="dark"] .rv-plan-for span,
      html[data-theme="dark"] .rv-intro-step-n,
      html[data-theme="dark"] .rv-pain-fix span,
      html[data-theme="dark"] .rv-incl-bound h3,
      html[data-theme="dark"] .rv-toolcard-badge { color: var(--color-wheat); }
      html[data-theme="dark"] .rv-work-more,
      html[data-theme="dark"] .rv-process-note { color: var(--color-sage); }
      /* The italic accent word in headings shipped as solid wheat, which only
         reaches ~1.75:1 on the light section backgrounds (fails even the 3:1
         large-text bar). Use clay on light backgrounds (passes) and keep the
         brand wheat on the dark hero, pine, and CTA bands and in dark mode. */
      .rv-accent { color: var(--color-clay); }
      .rv-hero .rv-accent,
      .rv-band-pine .rv-accent,
      .rv-cta-band .rv-accent { color: var(--color-wheat); }
      html[data-theme="dark"] .rv-accent { color: var(--color-wheat); }
      /* Same wheat-on-light problem on the metric numbers and timeline day labels
         (both sit on light bands). Clay for the large metric, a deeper clay for
         the small day label (needs 4.5:1); wheat stays in dark mode. */
      .rv-metric-v { color: var(--color-clay); }
      .rv-tl-day { color: #9e4a32; }
      html[data-theme="dark"] .rv-metric-v,
      html[data-theme="dark"] .rv-tl-day { color: var(--color-wheat); }
      /* Slightly larger base reading size for comfort (~18px). Headings, nav,
         and buttons keep their own sizes, so only body/paragraph text grows. */
      body { font-size: 1.125rem; }
      /* Closing CTA bands and pull-quotes shipped centered; left-align them to
         the same edge as their heading. Hero alignment lives in app.css. */
      .rv-cta-inner { text-align: left; }
      .rv-cta-sub { margin-left: 0; margin-right: 0; }
      .rv-quote-block { text-align: left; margin-inline-start: 0; }
      /* Left-aligned section header stack (eyebrow + heading + intro) that fills
         the content width, instead of the narrow centered column that read as
         floating in the middle above full-width grids. Children left-align to
         the same edge as the content below them. */
      .rv-headstack { max-width: none; margin-inline: 0; }
    </style>
  </head>

  <body @php(body_class())>
    @php(wp_body_open())

    <div id="page" class="rv-site">
      @include('sections.header')

      <main id="main" class="rv-main">
        @yield('content')
      </main>

      @include('sections.footer')
    </div>

    @php(do_action('get_footer'))
    @php(wp_footer())
  </body>
</html>
