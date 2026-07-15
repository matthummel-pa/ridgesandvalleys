{{--
  Single project landing page (Repofolio plugin's repofolio_project post type;
  this file replaced single-projects.blade.php when the theme's own projects
  CPT + GitHub engine moved into the plugin).

  Uses the plugin's "featured repo" profile (repofolio_repo_profile()) as the
  template: it pulls the repo's description, tags, stats, languages, version
  notes and README straight onto the page, with a clean title header + repo
  link on top. Manual (non-GitHub) projects fall back to their own content.
  This template can only be reached when the plugin's CPT exists, but the
  template tags are still guarded so a stale permalink never fatals.
--}}
@extends('layouts.app')

@section('content')
@php
  if (have_posts()) { the_post(); }
  $pid     = get_the_ID();
  $hasRepofolio = function_exists('Repofolio\\repofolio_repo_profile');
  $owner   = get_post_meta($pid, '_repofolio_gh_owner', true) ?: apply_filters('pressroot/github_owner', 'matthummel-pa');
  $repo    = get_post_meta($pid, '_repofolio_gh_repo', true) ?: get_post_field('post_name');
  $demoUrl = get_post_meta($pid, '_repofolio_url', true);
  $tech    = get_post_meta($pid, '_repofolio_stack', true);
  $pills   = $tech ? array_map('trim', explode(',', $tech)) : [];
  $terms   = get_the_terms($pid, 'repofolio_project_type');
  $cat     = ($terms && ! is_wp_error($terms)) ? $terms[0]->name : 'Project';
  $repoProfile = $hasRepofolio ? \Repofolio\repofolio_repo_profile($owner, $repo) : '';
  $isRepo  = $repoProfile !== '';
  $ghLink  = 'https://github.com/' . $owner . '/' . $repo;
  $title   = get_the_title();
  $desc    = get_the_excerpt();
  $bodyHtml    = trim(get_the_content()) ? apply_filters('the_content', get_the_content()) : '';
  $related = [];
  foreach (get_posts(['post_type' => 'repofolio_project', 'posts_per_page' => 3, 'post__not_in' => [$pid]]) as $rp) {
      $related[] = ['title' => get_the_title($rp), 'url' => get_permalink($rp), 'excerpt' => wp_trim_words(get_the_excerpt($rp), 16)];
  }
@endphp

{{-- Title header --}}
<section class="prt-wrap" style="padding-top:70px; padding-bottom:24px;">
  <div style="font-family:var(--font-mono); font-size:13px; color:var(--color-eyebrow, var(--color-green)); letter-spacing:.1em; margin-bottom:16px;">{{ strtoupper($cat) }}</div>
  <h1 style="font-family:var(--font-display); font-weight:800; font-size:clamp(38px,6vw,64px); letter-spacing:-.03em; line-height:1.02; margin:0 0 18px; color:var(--color-h1, var(--color-ink));">{{ $title }}</h1>
  @unless ($isRepo)
    @if ($desc)
      <p style="font-size:20px; line-height:1.5; color:var(--color-body); max-width:40em; margin:0 0 22px;">{{ $desc }}</p>
    @endif
    @if ($pills)
      <div style="display:flex; flex-wrap:wrap; gap:8px; margin-bottom:22px;">
        @foreach ($pills as $pill)<span class="tech-pill">{{ $pill }}</span>@endforeach
      </div>
    @endif
  @endunless
  <div style="display:flex; gap:14px; flex-wrap:wrap;">
    @if ($demoUrl)
      <a href="{{ esc_url($demoUrl) }}" target="_blank" rel="noopener" class="prt-lift" style="text-decoration:none; background:#6C4CF1; color:#fff; padding:14px 26px; border-radius:999px; font-weight:700; font-family:var(--font-display);">Live site &#8599;</a>
    @endif
    <a href="{{ esc_url($ghLink) }}" target="_blank" rel="noopener" class="prt-lift prt-btn-grad" style="padding:14px 26px; font-family:var(--font-display);">View repository on GitHub &#8599;</a>
  </div>
</section>

{{-- The featured repo profile IS the page content for GitHub projects --}}
@if ($isRepo)
  <section class="prt-wrap" style="padding-top:8px; padding-bottom:60px;">{!! $repoProfile !!}</section>
@elseif ($bodyHtml)
  <section class="prt-wrap" style="padding-top:8px; padding-bottom:60px;">
    <div class="entry-content post-prose" style="max-width:760px;">{!! $bodyHtml !!}</div>
  </section>
@endif

@if ($related)
<section class="prt-wrap" style="padding-bottom:60px;">
  <h2 style="font-family:var(--font-display); font-weight:800; font-size:clamp(28px,3vw,34px); letter-spacing:-.02em; margin:0 0 24px; color:var(--color-h2, var(--color-ink));">More projects</h2>
  <div class="prt-grid-3" style="display:grid; grid-template-columns:repeat(3,1fr); gap:18px;">
    @foreach ($related as $r)
      <a href="{{ $r['url'] }}" class="prt-lift" style="text-decoration:none; color:inherit; background:#fff; border:1.5px solid #ECE6FB; border-radius:20px; padding:24px; display:block;">
        <h3 style="font-family:var(--font-display); font-weight:700; font-size:19px; margin:0 0 8px; color:var(--color-h3, var(--color-ink));">{!! $r['title'] !!}</h3>
        <p style="font-size:14.5px; color:#5A5676; line-height:1.5; margin:0;">{{ $r['excerpt'] }}</p>
      </a>
    @endforeach
  </div>
</section>
@endif

@include('partials.home.cta')
@endsection
