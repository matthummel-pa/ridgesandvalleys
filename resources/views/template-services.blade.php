{{--
  Template Name: Services
  Pattern-first: when the page has block content (e.g. the "Services — Full
  page" pattern) it renders full-bleed. The hardcoded card grid remains only
  as a fallback for empty pages.
--}}

@extends('layouts.app')

@section('content')
  @if (trim(wp_strip_all_tags(get_the_content())) !== '' || str_contains((string) get_the_content(), 'prt-wrap'))
    @while(have_posts()) @php(the_post())
      @php(the_content())
    @endwhile
  @else
    @include('partials.page-header')

    <div class="container">
      <div class="card-grid services-grid">
        <div class="service-card">
          <h3>{{ __('Web Development', 'pressroot') }}</h3>
          <p>{{ __('Fast, accessible sites and web apps — modern front-end, performance, and SEO.', 'pressroot') }}</p>
        </div>
        <div class="service-card">
          <h3>{{ __('WordPress Development', 'pressroot') }}</h3>
          <p>{{ __('Custom themes, Gutenberg blocks, and code-first Sage builds without page-builder bloat.', 'pressroot') }}</p>
        </div>
        <div class="service-card">
          <h3>{{ __('Power Platform', 'pressroot') }}</h3>
          <p>{{ __('Power Apps, Power Automate, and Dataverse solutions across Microsoft 365.', 'pressroot') }}</p>
        </div>
      </div>
    </div>

    @include('partials.cta')
  @endif
@endsection
