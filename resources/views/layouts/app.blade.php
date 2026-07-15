<!doctype html>
<html @php(language_attributes())>
  <head>
    <meta charset="utf-8">
    <meta name="viewport" content="width=device-width, initial-scale=1">
    @php(do_action('get_header'))
    @php(wp_head())

    @vite(['resources/css/app.css', 'resources/js/app.js'])
    @php(do_action('prt_head_end'))
  </head>

  <body @php(body_class())>
    @php(wp_body_open())

    <div id="app">
      <a class="sr-only focus:not-sr-only" href="#main">
        {{ __('Skip to content', 'pressroot') }}
      </a>

      @php(do_action('prt_before_header'))
      @include('sections.header')
      @php(do_action('prt_after_header'))

      @php($mhLayout = \App\prt_active_layout())
      <div class="main-wrap @if ($mhLayout['sidebar']) main-wrap--sidebar @endif">
        <main id="main" class="main">
          @php(do_action('prt_before_content'))
          @yield('content')
          @php(do_action('prt_after_content'))
        </main>

        @if ($mhLayout['sidebar'])
          <aside class="prt-sidebar-area" aria-label="{{ __('Sidebar', 'pressroot') }}">
            @if (is_active_sidebar('sidebar-primary'))
              @php(dynamic_sidebar('sidebar-primary'))
            @else
              <p class="prt-sidebar-empty">{{ __('Add widgets in Appearance → Widgets (Primary Sidebar).', 'pressroot') }}</p>
            @endif
          </aside>
        @endif
      </div>

      @hasSection('sidebar')
        <aside class="sidebar">
          @yield('sidebar')
        </aside>
      @endif

      @php(do_action('prt_before_footer'))
      @include('sections.footer')
      @php(do_action('prt_after_footer'))
    </div>

    @php(do_action('get_footer'))
    @php(wp_footer())
  </body>
</html>
