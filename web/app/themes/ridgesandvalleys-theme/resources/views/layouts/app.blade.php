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
    {!! \App\lcp_preload_tags() !!}
    @vite(['resources/js/app.ts'])
    @if (\App\needs_tools_assets())
      @vite(['resources/js/tools.ts'])
    @endif
    @php(wp_head())
  </head>

  <body @php(body_class())>
    @php(wp_body_open())
    <span id="top"></span>

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
