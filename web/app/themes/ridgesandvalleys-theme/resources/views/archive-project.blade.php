@extends('layouts.app')

@section('content')
  <div class="rv-shell rv-work-archive" style="padding-block: var(--section-y)">
    <header class="rv-page-head">
      {!! \App\eyebrow(__('Portfolio', 'sage')) !!}
      <h1 class="rv-page-title">{{ __('Selected work', 'sage') }}</h1>
      <p class="rv-page-intro">{{ __('A few sites built for real South Central PA businesses — fast, accessible, and made to be found.', 'sage') }}</p>
    </header>

    @if (have_posts())
      <div class="rv-grid rv-grid-3">
        @while(have_posts()) @php(the_post())
          @php($client = get_post_meta(get_the_ID(), '_rv_client', true))
          <article @php(post_class('rv-card rv-work-card'))>
            <a class="rv-work-link" href="{{ get_permalink() }}">
              @if (has_post_thumbnail())
                <span class="rv-work-thumb">@php(the_post_thumbnail('rv-card', ['loading' => 'lazy']))</span>
              @endif
              <span class="rv-work-body">
                @if ($client)<span class="rv-eyebrow">{{ $client }}</span>@endif
                <span class="rv-work-title">{!! get_the_title() !!}</span>
                @if (has_excerpt())<span class="rv-work-excerpt">{{ get_the_excerpt() }}</span>@endif
              </span>
            </a>
          </article>
        @endwhile
      </div>
      <nav class="rv-pagination" aria-label="{{ __('Posts navigation', 'sage') }}">{!! \App\pagination() !!}</nav>
    @else
      @include('partials.content-none')
    @endif
  </div>
@endsection
