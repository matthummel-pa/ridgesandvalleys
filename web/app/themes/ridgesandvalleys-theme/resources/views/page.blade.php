@extends('layouts.app')

@section('content')
  @include('partials.entry-hero')

  @php($rvL = \App\entry_layout())
  @if ($rvL['sidebar'] !== 'none')
    <div class="rv-shell rv-layout rv-has-sidebar rv-side-{{ $rvL['sidebar'] }}">
      <div class="rv-content">
        @while(have_posts()) @php(the_post())
          @include('partials.content-page')
        @endwhile
      </div>
      @include('sections.sidebar')
    </div>
  @else
    <div class="rv-pagewrap rv-pw-{{ $rvL['page_width'] }} rv-pa-{{ $rvL['page_align'] }}">
      @while(have_posts()) @php(the_post())
        @include('partials.content-page')
      @endwhile
    </div>
  @endif
@endsection
