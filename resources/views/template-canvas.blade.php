{{--
  Template Name: Canvas (full-width blocks)
  Outputs the page's block content full-bleed (no prose/container wrapper) so the
  designed .prt-wrap sections control their own width — which follows the
  Customizer content width via --prt-content-width.
--}}
@extends('layouts.app')

@section('content')
  @while(have_posts()) @php(the_post())
    @php(the_content())
  @endwhile
@endsection
