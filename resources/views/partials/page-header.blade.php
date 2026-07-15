@php
  // Suppress the page title header on designed "pattern" pages — their content
  // already opens with its own hero (sections are wrapped in .prt-wrap).
  $mhSkipHeader = is_page() && str_contains((string) get_the_content(), 'prt-wrap');
@endphp
@unless ($mhSkipHeader)
  <header class="page-header">
    @php
      $mhEyebrow = '';
      if (is_post_type_archive('projects') || is_tax('project_categories')) {
        $mhEyebrow = __('Work', 'pressroot');
      } elseif (is_page()) {
        $mhParent = wp_get_post_parent_id(get_the_ID());
        $mhEyebrow = $mhParent ? get_the_title($mhParent) : '';
      }
    @endphp
    @if ($mhEyebrow)
      <p class="eyebrow">{{ $mhEyebrow }}</p>
    @endif
    <h1 class="display-title is-hero">{!! $title !!}</h1>
  </header>
@endunless
