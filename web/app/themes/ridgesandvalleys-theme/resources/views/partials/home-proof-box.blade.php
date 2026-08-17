{{-- One homepage proof card. $box from \App\home_proof_box_data(); $cta and $credit from the parent. --}}
<div class="rv-proof-item">
  @if (($box['type'] ?? '') !== '')
    <p class="rv-proof-type">{{ $box['type'] }}</p>
  @endif
  <article class="rv-proof">
    <div class="rv-proof-visual rv-media-photo">
      @if (($box['img'] ?? '') !== '')
        {!! \App\content_img($box['img'], $box['alt'] ?? '', ['size' => 'full']) !!}
      @endif
      @if (! empty($box['show_credit']) && trim($credit ?? '') !== '')<div class="rv-img-credit">{!! nl2br(e($credit)) !!}</div>@endif
    </div>
    <div class="rv-proof-body">
      {!! \App\eyebrow($box['kicker'] ?? '') !!}
      <h3 class="rv-proof-title">{{ $box['title'] ?? '' }}</h3>
      @if (trim($box['where'] ?? '') !== '')
        <p class="rv-proof-where">{{ $box['where'] }}</p>
      @endif
      @if (trim($box['text'] ?? '') !== '')
        <p class="rv-feature-text">{{ $box['text'] }}</p>
      @endif
      <div class="rv-proof-actions">
        <a class="rv-btn rv-btn-primary" href="{{ $box['href'] ?? '#' }}">{{ $cta }} {!! \App\icon('arrow') !!}</a>
      </div>
    </div>
    @if (! empty($box['metrics']))
      <ul class="rv-proof-metrics" aria-label="{{ __('Project numbers', 'sage') }}">
        @foreach ($box['metrics'] as $m)
          <li>
            <span class="rv-metric-v">{{ $m['v'] ?? '' }}</span>
            <span class="rv-metric-l">{{ $m['l'] ?? '' }}</span>
          </li>
        @endforeach
      </ul>
    @endif
  </article>
</div>
