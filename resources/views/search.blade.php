@extends('layouts.app')

@section('content')
  <div class="rv-shell rv-layout">
    <div class="rv-content">
      <header class="rv-page-head">
        {!! \App\eyebrow(__('Search', 'sage')) !!}
        <h1 class="rv-page-title">{!! sprintf(__('Results for &ldquo;%s&rdquo;', 'sage'), '<span>' . esc_html(get_search_query()) . '</span>') !!}</h1>
        <div class="rv-page-intro">@php(get_search_form())</div>
      </header>

      @if (have_posts())
        <div class="rv-post-list">
          @while(have_posts()) @php(the_post())
            @include('partials.content-search')
          @endwhile
        </div>
        <nav class="rv-pagination" aria-label="{{ __('Posts navigation', 'sage') }}">{!! \App\pagination() !!}</nav>
      @else
        @include('partials.content-none')
      @endif
    </div>

    @include('sections.sidebar')
  </div>
@endsection
