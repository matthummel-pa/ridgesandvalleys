{{-- ROOTED / LOCAL --}}
@php
  $homeId = \App\home_page_id() ?: get_the_ID();
  $rootedCredit = \App\field('rooted_credit', '', $homeId);
  $rootedRegions = array_filter(array_map('trim', explode(',', \App\field('rooted_regions', 'Gettysburg, Adams County, Cumberland Valley, South Mountain, Michaux', $homeId))));
@endphp
<section class="rv-band" aria-labelledby="rv-rooted-heading">
  <div class="rv-shell">
    <header class="rv-rooted-head">
      {!! \App\eyebrow(\App\field('rooted_kicker', __('A local studio, not a remote agency', 'sage'), $homeId)) !!}
      <h2 id="rv-rooted-heading" class="rv-section-title">{{ \App\field('rooted_h2', __('Built in', 'sage'), $homeId) }} <em class="rv-accent">{{ \App\field('rooted_h2_accent', __('Gettysburg.', 'sage'), $homeId) }}</em> {{ \App\field('rooted_h2_end', __('Supported here.', 'sage'), $homeId) }}</h2>
      <p class="rv-page-intro">{{ \App\field('rooted_lede', __('Family-owned web design in Adams County — in-person when you want it, and still here after launch.', 'sage'), $homeId) }}</p>
    </header>

    <div class="rv-rooted">
      <div class="rv-rooted-copy">
        <ul class="rv-scan">
          @foreach (\App\field_rows('rooted_points', \App\home_rooted_point_defaults(), $homeId) as $i => $pt)
            <li>
              <span class="rv-scan-n" aria-hidden="true">{{ str_pad((string) ($i + 1), 2, '0', STR_PAD_LEFT) }}</span>
              <div>
                <h3>{{ $pt['title'] ?? '' }}</h3>
                <p>{{ $pt['text'] ?? '' }}</p>
              </div>
            </li>
          @endforeach
        </ul>
        @if (count($rootedRegions))
          <div class="rv-rooted-places">
            <p class="rv-rooted-places-label">{{ __('Around here', 'sage') }}</p>
            <div class="rv-chips">
              @foreach ($rootedRegions as $place)
                <span class="rv-mchip">{{ $place }}</span>
              @endforeach
            </div>
          </div>
        @endif
        <div class="rv-rooted-actions">
          <a class="rv-btn rv-btn-primary" href="{{ \App\cta_href(\App\field('rooted_cta_url', '/about/', $homeId)) }}">{{ \App\field('rooted_cta', __('More about us', 'sage'), $homeId) }} {!! \App\icon('arrow') !!}</a>
          <a class="rv-btn rv-btn-ghost" href="{{ \App\cta_href(\App\field('rooted_cta2_url', '/contact/', $homeId)) }}">{{ \App\field('rooted_cta2', __('Get a quote', 'sage'), $homeId) }}</a>
        </div>
      </div>
      <div class="rv-rooted-visual rv-media-photo">
        <img src="{{ \App\field('rooted_img', '', $homeId) ?: \App\stock_image('rooted') }}" alt="{{ __('1935 aerial photograph of Gettysburg set among the farmland and ridges of Adams County', 'sage') }}" loading="lazy" onerror="this.style.display='none'">
        @if (trim($rootedCredit) !== '')<div class="rv-img-credit">{!! nl2br(e($rootedCredit)) !!}</div>@endif
      </div>
    </div>
  </div>
</section>
