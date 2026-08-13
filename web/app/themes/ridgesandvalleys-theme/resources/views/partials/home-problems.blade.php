{{-- PROBLEMS / “If this sounds familiar” --}}
@php
  $homeId = \App\home_page_id() ?: get_the_ID();
@endphp
<section class="rv-band rv-band-alt" aria-labelledby="rv-pain-heading">
  <div class="rv-shell">
    <header class="rv-pain-head">
      {!! \App\eyebrow(\App\field('pain_eyebrow', __('If this sounds familiar', 'sage'), $homeId)) !!}
      <h2 id="rv-pain-heading" class="rv-section-title">{{ \App\field('pain_h2', __('When Gettysburg customers', 'sage'), $homeId) }} <em class="rv-accent">{{ \App\field('pain_h2_accent', __('can’t find you.', 'sage'), $homeId) }}</em></h2>
      <p class="rv-page-intro">{{ \App\field('pain_lede', __('Hours on Facebook, a site that fights a phone, a page you’re afraid to edit — that’s what we hear from Adams County shops and firms. Gettysburg web design should fix those, not add to them.', 'sage'), $homeId) }}</p>
    </header>

    <ul class="rv-pain-grid">
      @foreach (\App\field_rows('pain_items', \App\home_pain_defaults(), $homeId) as $pain)
        <li class="rv-card rv-pain-card">
          <span class="rv-stripe rv-stripe-thin" aria-hidden="true"></span>
          <h3>{{ $pain['title'] ?? '' }}</h3>
          <p>{{ $pain['text'] ?? '' }}</p>
          @if (trim(\App\strip_field_markers((string) ($pain['fix'] ?? ''))) !== '')
            <p class="rv-pain-fix"><span>{{ __('The fix', 'sage') }}</span> {{ $pain['fix'] }}</p>
          @endif
        </li>
      @endforeach
    </ul>

    <div class="rv-pain-foot">
      <p class="rv-pain-close">{{ \App\field('pain_close', __('If two of these are true, it’s a rebuild — not another plugin.', 'sage'), $homeId) }}</p>
      <div class="rv-pain-actions">
        <a class="rv-btn rv-btn-primary" href="{{ \App\services_href(\App\field('pain_cta_url', \App\services_path(), $homeId)) }}">{{ \App\field('pain_cta', __('See how we fix this', 'sage'), $homeId) }} {!! \App\icon('arrow') !!}</a>
        <a class="rv-btn rv-btn-ghost" href="{{ \App\cta_href(\App\field('pain_cta2_url', '/contact/', $homeId)) }}">{{ \App\field('pain_cta2', __('Get a quote', 'sage'), $homeId) }}</a>
      </div>
    </div>
  </div>
</section>
