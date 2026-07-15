@php
  $mhSoc    = \App\prt_social_links();
  $socIcons = get_theme_mod('prt_social_style', 'icons') === 'icons';
@endphp

<header class="site-header" id="site-header">
  <div class="site-header-inner">

    {{-- Brand --}}
    <a class="brand" href="{{ home_url('/') }}" rel="home" aria-label="{{ $siteName }} — home">
      <span class="brand-mark" aria-hidden="true">
        <svg viewBox="0 0 120 120" xmlns="http://www.w3.org/2000/svg">
          <rect width="120" height="120" rx="22"/>
          <text x="60" y="82" text-anchor="middle">MH</text>
        </svg>
      </span>
      <span class="brand-name">{{ $siteName }}</span>
    </a>

    {{-- Primary navigation --}}
    @if (has_nav_menu('primary_navigation'))
      <nav class="header-nav" aria-label="Primary navigation">
        {!! wp_nav_menu([
          'theme_location' => 'primary_navigation',
          'menu_class'     => 'header-nav-list',
          'echo'           => false,
          'container'      => false,
          'depth'          => 1,
        ]) !!}
      </nav>
    @endif

    {{-- Right cluster --}}
    <div class="header-actions">
      @if ($mhSoc)
        <ul class="header-social" aria-label="Social links">
          @foreach ($mhSoc as $s)
            <li>
              <a href="{{ esc_url($s['url']) }}" aria-label="{{ $s['label'] }}" rel="me noopener" target="_blank"{!! \App\prt_social_item_style_attr($s['key']) !!}>
                {!! \App\prt_social_icon($s['key']) !!}
              </a>
            </li>
          @endforeach
        </ul>
      @endif

      @if (get_theme_mod('prt_dark_enable', true))
        <button class="prt-theme-toggle" type="button" aria-label="{{ __('Toggle dark mode', 'pressroot') }}" aria-pressed="false">
          {!! \App\prt_icon('heroicon-o-moon', 'prt-icon-dark') !!}
          {!! \App\prt_icon('heroicon-o-sun', 'prt-icon-light') !!}
        </button>
      @endif

      @php($prtCta = function_exists('App\prt_header_cta') ? \App\prt_header_cta() : ['text' => __('Get a quote', 'pressroot'), 'url' => home_url('/contact')])
      <a class="btn btn-hire" href="{{ esc_url($prtCta['url']) }}">
        {{ $prtCta['text'] }}
      </a>

      <button class="menu-toggle" aria-expanded="false" aria-controls="prt-popout" aria-label="{{ __('Open menu', 'pressroot') }}">
        <span class="bars" aria-hidden="true"></span>
      </button>
    </div>
  </div>
</header>

<div class="prt-popout-overlay" tabindex="-1"></div>
<aside id="prt-popout" class="prt-popout" aria-label="{{ __('Menu', 'pressroot') }}">
  <button class="prt-popout-close" aria-label="{{ __('Close menu', 'pressroot') }}">&times;</button>

  @php($prtCta2 = function_exists('App\prt_header_cta') ? \App\prt_header_cta() : ['text' => __('Get a quote', 'pressroot'), 'url' => home_url('/contact')])
  <a class="btn btn-hire prt-popout-cta" href="{{ esc_url($prtCta2['url']) }}">
    {{ $prtCta2['text'] }}
  </a>

  @if (has_nav_menu('primary_navigation'))
    <nav aria-label="{{ __('Popout menu', 'pressroot') }}">
      {!! wp_nav_menu([
        'theme_location' => 'primary_navigation',
        'menu_class'     => 'prt-popout-menu',
        'echo'           => false,
        'container'      => false,
      ]) !!}
    </nav>
  @endif

  @if ($mhSoc)
    <div class="prt-popout-socials">
      @foreach ($mhSoc as $s)
        <a href="{{ esc_url($s['url']) }}" aria-label="{{ $s['label'] }}" rel="me noopener" target="_blank"{!! \App\prt_social_item_style_attr($s['key']) !!}>
          {!! \App\prt_social_icon($s['key']) !!}
        </a>
      @endforeach
    </div>
  @endif
</aside>
