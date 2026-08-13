{{-- INCLUDED IN EVERY BUILD --}}
@php
  $homeId = \App\home_page_id() ?: get_the_ID();
@endphp
<section class="rv-band" aria-labelledby="rv-incl-heading">
  <div class="rv-shell">
    <article class="rv-incl">
      <span class="rv-stripe rv-stripe-thin" aria-hidden="true"></span>
      <div class="rv-incl-copy">
        {!! \App\eyebrow(\App\field('incl_eyebrow', __('No surprises', 'sage'), $homeId)) !!}
        <h2 id="rv-incl-heading" class="rv-section-title">{{ \App\field('incl_h2', __('Included in every', 'sage'), $homeId) }} <em class="rv-accent">{{ \App\field('incl_h2_accent', __('build.', 'sage'), $homeId) }}</em></h2>
        <p class="rv-page-intro">{{ \App\field('incl_lede', __('Every Gettysburg web design package — Rescue, Local Launch, or Growth — ships with the same baseline. The price changes with scope. This list does not.', 'sage'), $homeId) }}</p>
        <p class="rv-incl-close">{{ \App\field('incl_close', __('The full list, with packages, is on the services page.', 'sage'), $homeId) }}</p>
        <div class="rv-incl-actions">
          <a class="rv-btn rv-btn-primary" href="{{ \App\services_href(\App\field('incl_cta_url', \App\services_path() . '#included', $homeId)) }}">{{ \App\field('incl_cta', __('Compare packages', 'sage'), $homeId) }} {!! \App\icon('arrow') !!}</a>
          <a class="rv-btn rv-btn-ghost" href="{{ \App\cta_href(\App\field('incl_cta2_url', '/contact/', $homeId)) }}">{{ \App\field('incl_cta2', __('Get a quote', 'sage'), $homeId) }}</a>
        </div>
      </div>
      <div class="rv-incl-panel">
        <ul class="rv-incl-list">
          @foreach (\App\field_rows('incl_points', \App\svc_incl_point_defaults(), $homeId) as $inc)
            <li>
              <h3>{{ $inc['title'] ?? '' }}</h3>
              <p>{{ $inc['text'] ?? '' }}</p>
            </li>
          @endforeach
        </ul>
        <div class="rv-incl-bounds">
          @foreach (\App\field_rows('incl_bounds', \App\svc_bound_defaults(), $homeId) as $b)
            <article class="rv-incl-bound">
              <h3>{{ $b['title'] ?? '' }}</h3>
              <p>{{ $b['text'] ?? '' }}</p>
            </article>
          @endforeach
        </div>
      </div>
    </article>
  </div>
</section>
