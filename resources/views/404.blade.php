@extends('layouts.app')

@section('content')
  <div class="rv-shell rv-404">
    <span class="rv-stripe rv-stripe-block" aria-hidden="true"></span>
    {!! \App\eyebrow(__('Off the map', 'sage')) !!}
    <h1 class="rv-404-title">{{ __('That page took a wrong turn', 'sage') }}</h1>
    <p class="rv-404-text">{{ __('The page you were after isn\'t here. Try a search, or head back to solid ground.', 'sage') }}</p>
    <div class="rv-404-search">@php(get_search_form())</div>
    <p><a class="rv-btn rv-btn-primary" href="{{ home_url('/') }}">{{ __('Back to home', 'sage') }}</a></p>
  </div>
@endsection
