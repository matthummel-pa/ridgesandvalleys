{{--
  Optional hero side columns (columns 2 and 3), rendered inside .rv-hero-cols
  after .rv-hero-main. Populated from the per-page "Hero column 2/3" fields via
  \App\hero_asides(); renders nothing when none are set.
--}}
@foreach ($asides ?? [] as $rvAside)
  <aside class="rv-hero-aside">
    @if (trim(\App\strip_field_markers($rvAside['title'] ?? '')) !== '')
      <h2 class="rv-hero-aside-title">{{ $rvAside['title'] }}</h2>
    @endif
    @if (trim(\App\strip_field_markers($rvAside['body'] ?? '')) !== '')
      <div class="rv-hero-aside-body">{!! wp_kses_post($rvAside['body']) !!}</div>
    @endif
  </aside>
@endforeach
