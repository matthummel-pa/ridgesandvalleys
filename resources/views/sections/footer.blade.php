@if (is_singular('projects'))
  @include('partials.cta')
@endif

@php
  $f       = \App\prt_footer();
  $socials = \App\prt_social_links();

  $cols = $f['cols'];
  $activeWidgetCols = [];
  for ($i = 1; $i <= $cols; $i++) {
    if (is_active_sidebar("footer-{$i}")) { $activeWidgetCols[] = $i; }
  }

  $showMenuCol = $f['show_menu'];
  $hasTop      = $f['brand'] || $activeWidgetCols || $showMenuCol;

  $classes = implode(' ', array_filter([
    'content-info',
    'footer--' . $f['width'],
    'footer--pad-' . $f['pad'],
    'footer--cols-' . $f['col_layout'],
    $f['border'] ? '' : 'footer--no-border',
  ]));
@endphp
<footer class="{{ $classes }}">
  <div class="footer-inner">

    @if ($hasTop)
      <div class="footer-top footer-top--w{{ count($activeWidgetCols) + ($showMenuCol ? 1 : 0) }}{{ $f['brand'] ? ' has-brand' : '' }}">

        {{-- Brand column --}}
        @if ($f['brand'])
          <div class="footer-brand">
            <a class="brand" href="{{ home_url('/') }}" rel="home" aria-label="{{ $siteName }} — home">
              <span class="brand-mark" aria-hidden="true">
                <svg viewBox="0 0 120 120" xmlns="http://www.w3.org/2000/svg">
                  <rect width="120" height="120" rx="22"/>
                  <text x="60" y="82" text-anchor="middle">MH</text>
                </svg>
              </span>
              <span class="brand-name">{{ $siteName }}</span>
            </a>
            @if ($f['tagline'])
              <p class="footer-tagline">{!! wp_kses_post($f['tagline']) !!}</p>
            @endif
            @if ($f['show_social'] && $socials)
              <div class="footer-socials">
                @foreach ($socials as $s)
                  <a href="{{ esc_url($s['url']) }}" aria-label="{{ $s['label'] }}" rel="me noopener" target="_blank"{!! \App\prt_social_item_style_attr($s['key']) !!}>{!! \App\prt_social_icon($s['key']) !!}</a>
                @endforeach
              </div>
            @endif
          </div>
        @endif

        {{-- Widget columns --}}
        @foreach ($activeWidgetCols as $i)
          <div class="footer-col">
            @php(dynamic_sidebar("footer-{$i}"))
          </div>
        @endforeach

        {{-- Footer navigation column --}}
        @if ($showMenuCol)
          <nav class="footer-col footer-nav" aria-label="{{ __('Footer navigation', 'pressroot') }}">
            @if ($f['menu_title'])
              <h2 class="footer-widget-title">{{ $f['menu_title'] }}</h2>
            @endif
            @if (has_nav_menu('footer_navigation'))
              {!! wp_nav_menu([
                'theme_location' => 'footer_navigation',
                'menu_class'     => 'footer-nav-list',
                'echo'           => false,
                'container'      => false,
                'depth'          => 1,
              ]) !!}
            @else
              <ul class="footer-nav-list">
                {!! wp_list_pages(['title_li' => '', 'echo' => false, 'depth' => 1, 'number' => 6]) !!}
              </ul>
            @endif
          </nav>
        @endif
      </div>
    @endif

    {{-- Legacy full-width widget area --}}
    @if (is_active_sidebar('sidebar-footer'))
      @php(dynamic_sidebar('sidebar-footer'))
    @endif

    {{-- Bottom bar --}}
    <div class="footer-bottom footer-bottom--{{ $f['bottom_layout'] }}{{ $f['divider'] && $hasTop ? ' has-divider' : '' }}">
      <div class="footer-bottom-copy">
        <p class="footer-copyright">{!! wp_kses_post($f['copyright']) !!}</p>
        @if ($f['credit'])
          <p class="footer-credit">{!! sprintf(
            /* translators: %s: theme author link */
            esc_html__('Pressroot theme by %s.', 'pressroot'),
            '<a href="https://github.com/matthummel-pa" rel="author noopener" target="_blank">matthummel</a>'
          ) !!}</p>
        @endif
      </div>

      @if (has_nav_menu('footer_legal'))
        <nav class="footer-legal" aria-label="{{ __('Legal', 'pressroot') }}">
          {!! wp_nav_menu([
            'theme_location' => 'footer_legal',
            'menu_class'     => 'footer-legal-list',
            'echo'           => false,
            'container'      => false,
            'depth'          => 1,
          ]) !!}
        </nav>
      @endif
    </div>
  </div>
</footer>
