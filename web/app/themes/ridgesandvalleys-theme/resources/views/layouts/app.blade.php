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
      /* Everything reads left-aligned for scannability. Heroes, closing CTA
         bands, and pull-quotes shipped centered; left-align their text and drop
         the auto side-margins so each block starts at the same left edge as its
         heading. Intro paragraphs (rv-hero-sub) are auto-centered by app.css —
         reset them everywhere, including inside the hero. */
      .rv-hero-sub,
      .rv-band .rv-hero-sub,
      .rv-band-alt .rv-hero-sub,
      .rv-band-pine .rv-hero-sub,
      .rv-section .rv-hero-sub,
      .rv-shell .rv-hero-sub,
      .rv-shell-full .rv-hero-sub { margin-left: 0; margin-right: 0; text-align: left; }
      .rv-hero-inner { text-align: left; }
      /* Tighten interior-page heroes: the transparent header already reserves top
         clearance, so the hero's own large top padding doubled up and pushed the
         headline, subhead, and CTA low on the page. Pull them up (the home hero
         keeps its own tuned spacing via the .home scope). */
      body:not(.home) .rv-hero-inner { padding-block: clamp(1.25rem, 3vw, 2.25rem) clamp(2rem, 5vw, 3.25rem); }
      /* Uniform interior-hero height so every page's Featured-Image background
         reads the same size (sized to the Services hero); content is vertically
         centered within the band. */
      body:not(.home) .rv-hero { min-height: clamp(380px, 50vh, 480px); display: grid; align-content: center; }
      /* Compact the headline so long titles (e.g. About) don't tower over the band. */
      body:not(.home) .rv-hero-title { font-size: clamp(2.25rem, 4.5vw, 3.5rem); max-width: 26ch; }
      .rv-hero-title { margin-inline: 0; }
      .rv-hero-actions { justify-content: flex-start; }
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
