{{--
  Front page.

  Two modes, chosen automatically:

  1. BLOCK-EDITABLE — if the Home page has block content of its own, that content
     is rendered and is fully editable in the block editor. Populate it in one
     click: edit the Home page → Patterns → Pressroot → "Home — Full page".

  2. DESIGNED FALLBACK — if the page is empty, the designed homepage is composed
     from the partials/home/* sections (dynamic: work grid → projects CPT,
     "Latest writing" → recent posts). Edit copy in the PHP arrays at the top of
     each partial, or switch to mode 1 for visual editing.
--}}
@extends('layouts.app')

@section('content')
  @php $mhHasBlocks = false; @endphp

  @while(have_posts())
    @php the_post(); $mhHasBlocks = trim(get_the_content()) !== ''; @endphp
    @if ($mhHasBlocks)
      <article class="{{ implode(' ', get_post_class('page-home')) }}" aria-label="{{ get_the_title() }}">
        @php the_content(); @endphp
      </article>
    @endif
  @endwhile

  @unless ($mhHasBlocks)
    @include('partials.home.hero')
    @include('partials.home.brand-marquee')
    @include('partials.home.services-preview')
    @include('partials.home.work-bento')
    @include('partials.home.repos')
    @include('partials.home.why-me')
    @include('partials.home.risk-reversal')
    @include('partials.home.posts')
    @include('partials.home.cta')
  @endunless
@endsection
