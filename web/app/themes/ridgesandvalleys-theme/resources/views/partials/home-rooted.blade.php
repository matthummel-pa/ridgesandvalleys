{{-- ROOTED / LOCAL --}}
@php
  $homeId = \App\home_page_id() ?: get_the_ID();
  $rootedCredit = \App\field('rooted_credit', '', $homeId);
  $rootedPin = \App\field('rooted_pin', __('Gettysburg, Pennsylvania', 'sage'), $homeId);
  $rootedRegions = array_filter(array_map('trim', explode(',', \App\field('rooted_regions', 'Gettysburg, Adams County, Cumberland Valley, South Mountain, Michaux', $homeId))));
@endphp
<section class="rv-band" aria-labelledby="rv-rooted-heading">
  <div class="rv-shell">
    <header class="rv-rooted-head">
      {!! \App\eyebrow(\App\field('rooted_kicker', __('A local studio, not a remote agency', 'sage'), $homeId)) !!}
      <h2 id="rv-rooted-heading" class="rv-section-title">{{ \App\field('rooted_h2', __('Built in', 'sage'), $homeId) }} <em class="rv-accent">{{ \App\field('rooted_h2_accent', __('Gettysburg.', 'sage'), $homeId) }}</em> {{ \App\field('rooted_h2_end', __('Supported here.', 'sage'), $homeId) }}</h2>
      <p class="rv-page-intro">{{ \App\field('rooted_lede', __('A family-owned Gettysburg web design studio. Meet in town when that’s easier, and get the same person after launch — not a ticket queue.', 'sage'), $homeId) }}</p>
    </header>

    <div class="rv-rooted">
      <div class="rv-rooted-copy">
        <ul class="rv-reasons">
          @foreach (\App\field_rows('rooted_points', \App\home_rooted_point_defaults(), $homeId) as $pt)
            <li>
              <h3>{{ $pt['title'] ?? '' }}</h3>
              <p>{{ $pt['text'] ?? '' }}</p>
            </li>
          @endforeach
        </ul>
        @if (count($rootedRegions))
          <div class="rv-rooted-places">
            <p class="rv-rooted-places-intro">{{ \App\field('rooted_places_intro', __('Web design for Gettysburg, Adams County, and the ridges around South Mountain.', 'sage'), $homeId) }}</p>
            <p class="rv-rooted-places-label">{{ \App\field('rooted_places_label', __('Adams County & nearby', 'sage'), $homeId) }}</p>
            <ul class="rv-chips">
              @foreach ($rootedRegions as $place)
                <li class="rv-mchip">{{ $place }}</li>
              @endforeach
            </ul>
          </div>
        @endif
        <div class="rv-rooted-actions">
          <a class="rv-btn rv-btn-primary" href="{{ \App\about_href(\App\field('rooted_cta_url', '', $homeId)) }}">{{ \App\field('rooted_cta', __('More about us', 'sage'), $homeId) }} {!! \App\icon('arrow') !!}</a>
          <a class="rv-btn rv-btn-ghost" href="{{ \App\cta_href(\App\field('rooted_cta2_url', '/contact/', $homeId)) }}">{{ \App\field('rooted_cta2', __('Get a quote', 'sage'), $homeId) }}</a>
        </div>
      </div>
      <div class="rv-rooted-visual rv-media-photo">
        {!! \App\content_img(\App\field('rooted_img', '', $homeId) ?: \App\stock_image('rooted'), __('Gettysburg set among the farmland and ridges of Adams County — home of Ridges & Valleys web design', 'sage')) !!}
        @if (trim($rootedPin) !== '')<p class="rv-rooted-pin">{{ $rootedPin }}</p>@endif
        @if (trim($rootedCredit) !== '')<div class="rv-img-credit">{!! nl2br(e($rootedCredit)) !!}</div>@endif
      </div>
    </div>
  </div>
</section>
