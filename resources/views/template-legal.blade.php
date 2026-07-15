{{--
  Template Name: Legal / Policy
  Used for: Privacy Policy, Accessibility Statement, and similar pages.
  Pattern-first: pattern content (.prt-wrap sections) renders full-bleed with
  its own hero; plain prose keeps the classic single-column legal layout.
--}}

@extends('layouts.app')

@section('content')
  @php $prtCanvas = str_contains((string) get_the_content(), 'prt-wrap'); @endphp

  @if ($prtCanvas)
    @while(have_posts()) @php(the_post())
      @php(the_content())
    @endwhile
    <div class="container">
      <p class="archive-desc now-updated">
        {{ __('Last updated:', 'pressroot') }}
        <time datetime="{{ get_the_modified_date('c') }}">{{ get_the_modified_date() }}</time>
      </p>
    </div>
  @else
    <article class="legal-page container" data-anim="fade-up">
      <header class="legal-header">
        <h1 class="display-title is-page">{{ get_the_title() }}</h1>
        <p class="post-meta">
          {{ __('Last updated:', 'pressroot') }}
          <time datetime="{{ get_the_modified_date('c') }}">{{ get_the_modified_date() }}</time>
        </p>
      </header>

      <div class="entry-content post-prose">
        @php(the_content())
      </div>
    </article>
  @endif
@endsection
