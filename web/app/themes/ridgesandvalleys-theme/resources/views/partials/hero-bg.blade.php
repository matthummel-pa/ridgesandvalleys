{{--
  Full-bleed hero background image, shared by every page hero so they all look
  identical. Source priority (see \App\hero_bg_url): the page's WordPress
  Featured Image → the optional hero_bg custom field → the passed stock fallback.
  Renders nothing when none is available.

  Usage:
    @include('partials.hero-bg', ['fallback' => \App\stock_image('hero-home')])
    @include('partials.hero-bg', ['fallback' => \App\stock_image('process'), 'postId' => $blogId])
--}}
@php($rvHeroBg = \App\hero_bg_url($fallback ?? '', $postId ?? null))
@if ($rvHeroBg)
  <span class="rv-hero-bg" style="background-image:url('{{ $rvHeroBg }}')" aria-hidden="true"></span>
@endif
