{{--
  3 project cards + Care & Grow full-width bar.
  $projects and $care are package arrays from svc_project_packages() / svc_care_packages().
--}}
<div class="rv-svc-plans">
  @foreach ($projects as $s)
    @php($isFeatured = trim(\App\strip_field_markers((string) ($s['flag'] ?? ''))) !== '')
    <article class="rv-card rv-service rv-plan{{ $isFeatured ? ' is-featured' : '' }}">
      <span class="rv-stripe rv-stripe-thin" aria-hidden="true"></span>
      @if ($isFeatured)<span class="rv-plan-flag">{{ $s['flag'] }}</span>@endif
      <div class="rv-service-body">
        @if (trim(\App\strip_field_markers((string) ($s['for'] ?? ''))) !== '')
          <p class="rv-plan-for"><span>{{ __('Best for', 'sage') }}</span> {{ $s['for'] }}</p>
        @endif
        <h3 class="rv-service-name">{{ $s['name'] ?? '' }}</h3>
        <p class="rv-service-tag">{{ $s['price'] ?? '' }}</p>
        <p class="rv-service-desc">{{ $s['desc'] ?? '' }}</p>
        <ul class="rv-service-list">
          @foreach (\App\lines($s['features'] ?? []) as $item)<li>{{ $item }}</li>@endforeach
        </ul>
        <a class="rv-btn {{ $isFeatured ? 'rv-btn-primary' : 'rv-btn-ghost' }} rv-plan-btn" href="{{ \App\svc_package_href($s) }}">{{ $s['cta'] ?? __('Get a quote', 'sage') }}</a>
      </div>
    </article>
  @endforeach
</div>

@foreach ($care as $i => $s)
  @php($isFeatured = trim(\App\strip_field_markers((string) ($s['flag'] ?? ''))) !== '')
  <article class="rv-card rv-plan rv-plan-care{{ $isFeatured ? ' is-featured' : '' }}" @if ($i === 0) id="care" @endif>
    <span class="rv-stripe rv-stripe-thin" aria-hidden="true"></span>
    <div class="rv-plan-care-copy">
      @if ($isFeatured)<span class="rv-plan-flag rv-plan-flag-inline">{{ $s['flag'] }}</span>@endif
      @if (trim(\App\strip_field_markers((string) ($s['for'] ?? ''))) !== '')
        <p class="rv-plan-for"><span>{{ __('Best for', 'sage') }}</span> {{ $s['for'] }}</p>
      @endif
      <h3 class="rv-service-name">{{ $s['name'] ?? '' }}</h3>
      <p class="rv-service-tag">{{ $s['price'] ?? '' }}</p>
      <p class="rv-service-desc">{{ $s['desc'] ?? '' }}</p>
    </div>
    <ul class="rv-service-list">
      @foreach (\App\lines($s['features'] ?? []) as $item)<li>{{ $item }}</li>@endforeach
    </ul>
    <a class="rv-btn {{ $isFeatured ? 'rv-btn-primary' : 'rv-btn-ghost' }} rv-plan-btn" href="{{ \App\svc_package_href($s) }}">{{ $s['cta'] ?? __('Get a quote', 'sage') }}</a>
  </article>
@endforeach
