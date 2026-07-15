{{--
  Template Name: Now
  Pattern-first: pattern content (.prt-wrap sections) renders full-bleed;
  plain prose keeps the readable column. Either way the modified date prints
  below the content.
--}}

@extends('layouts.app')

@section('content')
  @php $prtCanvas = str_contains((string) get_the_content(), 'prt-wrap'); @endphp

  @if ($prtCanvas)
    @while(have_posts()) @php(the_post())
      @php(the_content())
    @endwhile
    <div class="container">
      <p class="archive-desc now-updated">{{ sprintf(__('Last updated %s.', 'pressroot'), get_the_modified_date()) }}</p>
    </div>
  @else
    @include('partials.page-header')

    <div class="container">
      <div class="entry-content post-prose">@php(the_content())</div>
      <p class="archive-desc now-updated">{{ sprintf(__('Last updated %s.', 'pressroot'), get_the_modified_date()) }}</p>
    </div>
  @endif
@endsection
