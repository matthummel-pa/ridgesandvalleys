@extends('layouts.app')

@section('content')
  <div class="rv-shell-full">
    @while(have_posts()) @php(the_post())
      @include('partials.content-single')
    @endwhile
  </div>
@endsection
