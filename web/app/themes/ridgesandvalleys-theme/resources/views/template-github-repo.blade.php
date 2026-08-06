{{-- Single repository detail — rendered for /code/{repo}/ (see app/github.php). --}}
@extends('layouts.app')

@section('content')
  <section class="rv-shell rv-band rv-repo-detail">
    <a class="rv-readmore" href="{{ home_url('/work/') }}">{{ __('Back to work', 'sage') }}</a>

    @if (! empty($repo))
      @php($facts = [])
      @if ($repo['language']) @php($facts[] = $repo['language']) @endif
      @if ($repo['stars'] > 0) @php($facts[] = '★ ' . number_format_i18n($repo['stars']) . ' ' . _n('star', 'stars', $repo['stars'], 'sage')) @endif
      @if ($repo['forks'] > 0) @php($facts[] = '⑂ ' . number_format_i18n($repo['forks']) . ' ' . _n('fork', 'forks', $repo['forks'], 'sage')) @endif
      @if ($repo['license']) @php($facts[] = $repo['license']) @endif
      @if ($repo['updated']) @php($facts[] = sprintf(__('updated %s ago', 'sage'), human_time_diff(strtotime($repo['updated'])))) @endif

      <header class="rv-repo-head">
        {!! \App\eyebrow(__('Open on GitHub', 'sage')) !!}
        <h1 class="rv-section-title">{{ $repo['title'] }}</h1>
        @if ($repo['desc'])
          <p class="rv-page-intro">{{ $repo['desc'] }}</p>
        @endif
        @if (! empty($facts))
          <p class="rv-repo-facts">{{ implode('  ·  ', $facts) }}</p>
        @endif
        @if (! empty($repo['topics']))
          <div class="rv-chips" style="margin-top:.85rem">
            @foreach (array_slice($repo['topics'], 0, 8) as $topic)
              <span class="rv-mchip">{{ $topic }}</span>
            @endforeach
          </div>
        @endif
        <div class="rv-repo-actions">
          <a class="rv-btn rv-btn-primary" href="{{ $repo['url'] }}" target="_blank" rel="noopener noreferrer">{{ __('View on GitHub', 'sage') }} &nearr;</a>
          @if ($repo['homepage'])
            <a class="rv-btn rv-btn-ghost" href="{{ $repo['homepage'] }}" target="_blank" rel="noopener noreferrer">{{ __('Live site', 'sage') }} &nearr;</a>
          @endif
        </div>
      </header>

      <div class="rv-readme">
        @if (! empty($readme))
          {!! $readme !!}
        @else
          <p class="rv-tool-hint">{{ __('This repository doesn’t have a README to show.', 'sage') }}</p>
        @endif
      </div>

      <div class="rv-repo-actions rv-repo-actions-end">
        <a class="rv-btn rv-btn-primary" href="{{ $repo['url'] }}" target="_blank" rel="noopener noreferrer">{{ __('View this project on GitHub', 'sage') }} &nearr;</a>
        <a class="rv-btn rv-btn-ghost" href="{{ home_url('/work/') }}">{{ __('Back to work', 'sage') }}</a>
      </div>
    @else
      <header class="rv-repo-head">
        {!! \App\eyebrow(__('GitHub', 'sage')) !!}
        <h1 class="rv-section-title">{{ __('Repository not found', 'sage') }}</h1>
        <p class="rv-page-intro">{{ __('We couldn’t load this repository right now. It may be private, renamed, or GitHub may be rate-limited. Try again shortly.', 'sage') }}</p>
        <div class="rv-repo-actions">
          <a class="rv-btn rv-btn-primary" href="{{ home_url('/work/') }}">{{ __('Back to work', 'sage') }}</a>
        </div>
      </header>
    @endif
  </section>

  <style>
    .rv-repo-detail{padding-top:6rem;padding-bottom:3rem}
    .rv-repo-head{margin-top:1.25rem;max-width:80ch}
    .rv-repo-facts{margin-top:.7rem;font-family:ui-monospace,SFMono-Regular,Menlo,monospace;font-size:.85rem;opacity:.7}
    .rv-repo-actions{display:flex;flex-wrap:wrap;gap:.75rem;margin-top:1.6rem}
    .rv-repo-actions-end{margin-top:2.5rem;padding-top:2rem;border-top:1px solid color-mix(in srgb,var(--color-ink,#23201B) 12%,transparent)}
    .rv-readme{margin-top:2.5rem;max-width:80ch;line-height:1.7}
    .rv-readme > *:first-child{margin-top:0}
    .rv-readme img{max-width:100%;height:auto}
    .rv-readme h1,.rv-readme h2,.rv-readme h3,.rv-readme h4{margin:1.7rem 0 .6rem;line-height:1.25;font-weight:700}
    .rv-readme h1{font-size:1.75rem}
    .rv-readme h2{font-size:1.4rem;padding-bottom:.3rem;border-bottom:1px solid color-mix(in srgb,var(--color-ink,#23201B) 10%,transparent)}
    .rv-readme h3{font-size:1.15rem}
    .rv-readme p{margin:.8rem 0}
    .rv-readme ul,.rv-readme ol{margin:.8rem 0 .8rem 1.5rem}
    .rv-readme li{margin:.35rem 0}
    .rv-readme a{color:var(--color-clay,#B0553A);text-underline-offset:2px}
    .rv-readme code{background:color-mix(in srgb,var(--color-ink,#23201B) 8%,transparent);padding:.12rem .38rem;border-radius:5px;font-size:.9em;font-family:ui-monospace,SFMono-Regular,Menlo,monospace}
    .rv-readme pre{background:color-mix(in srgb,var(--color-ink,#23201B) 92%,#000);color:#f4f0e8;padding:1rem 1.15rem;border-radius:10px;overflow:auto;margin:1.1rem 0}
    .rv-readme pre code{background:none;padding:0;color:inherit;font-size:.85em}
    .rv-readme table{border-collapse:collapse;margin:1.1rem 0;width:100%;font-size:.95em}
    .rv-readme th,.rv-readme td{border:1px solid color-mix(in srgb,var(--color-ink,#23201B) 15%,transparent);padding:.5rem .75rem;text-align:left}
    .rv-readme blockquote{border-left:3px solid var(--color-clay,#B0553A);margin:1.1rem 0;padding:.3rem 0 .3rem 1rem;opacity:.85}
    .rv-readme hr{border:0;border-top:1px solid color-mix(in srgb,var(--color-ink,#23201B) 12%,transparent);margin:1.6rem 0}
  </style>

  <script>
    /* Hide any README image that fails to load (e.g. a broken relative badge). */
    (function () {
      document.querySelectorAll('.rv-readme img').forEach(function (img) {
        var hide = function () { img.style.display = 'none'; };
        if (img.complete && img.naturalWidth === 0) { hide(); }
        img.addEventListener('error', hide);
      });
    })();
  </script>
@endsection
