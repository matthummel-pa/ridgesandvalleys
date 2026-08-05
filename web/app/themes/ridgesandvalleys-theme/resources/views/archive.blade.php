@extends('layouts.app')

@section('content')
  @php($rvL = \App\entry_layout())
  <div class="rv-shell rv-layout {{ $rvL['sidebar'] !== 'none' ? 'rv-has-sidebar rv-side-'.$rvL['sidebar'] : '' }}">
    <div class="rv-content">
      <header class="rv-page-head">
        {!! \App\eyebrow(__('Archive', 'sage')) !!}
        <h1 class="rv-page-title">@php(the_archive_title())</h1>
        @php($desc = get_the_archive_description())
        @if ($desc)
          <div class="rv-page-intro">{!! wp_kses_post($desc) !!}</div>
        @endif
      </header>

      @if (have_posts())
        <div class="rv-post-list">
          @while(have_posts()) @php(the_post())
            @includeFirst(['partials.content-' . get_post_type(), 'partials.content'])
          @endwhile
        </div>
        <nav class="rv-pagination" aria-label="{{ __('Posts navigation', 'sage') }}">{!! \App\pagination() !!}</nav>
      @else
        @include('partials.content-none')
      @endif
    </div>

    @if ($rvL['sidebar'] !== 'none')@include('sections.sidebar')@endif
  </div>
@endsection
