@extends('layouts.app')

@section('content')
  @include('partials.entry-hero')

  <div class="rv-shell rv-layout rv-has-sidebar rv-side-right">
    <div class="rv-content">
      @while(have_posts()) @php(the_post())
        @include('partials.content-single')
      @endwhile
    </div>
    @include('partials.post-sidebar')
  </div>
@endsection
