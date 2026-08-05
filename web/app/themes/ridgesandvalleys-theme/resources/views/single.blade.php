@extends('layouts.app')

@section('content')
  @include('partials.entry-hero')

  @php($rvL = \App\entry_layout())
  @if ($rvL['sidebar'] !== 'none')
    <div class="rv-shell rv-layout rv-w-{{ $rvL['width'] }} rv-has-sidebar rv-side-{{ $rvL['sidebar'] }}">
      <div class="rv-content">
        @while(have_posts()) @php(the_post())
          @include('partials.content-single')
        @endwhile
      </div>
      @include('sections.sidebar')
    </div>
  @else
    <div class="rv-shell-full rv-w-{{ $rvL['width'] }}">
      @while(have_posts()) @php(the_post())
        @include('partials.content-single')
      @endwhile
    </div>
  @endif
@endsection
