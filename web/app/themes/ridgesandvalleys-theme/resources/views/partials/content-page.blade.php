<article @php(post_class('rv-single rv-page'))>
  @unless (\App\entry_hero_enabled())
    <header class="rv-single-hero">
      <div class="rv-entry">
        <h1 class="rv-single-title">{!! get_the_title() !!}</h1>
      </div>
    </header>

    @if (has_post_thumbnail())
      <figure class="rv-single-figure">@php(the_post_thumbnail('rv-hero', ['class' => 'rv-single-image']))</figure>
    @endif
  @endunless

  <div class="rv-entry rv-prose">
    @php(the_content())
    @php(wp_link_pages(['before' => '<nav class="rv-page-links">' . __('Pages:', 'sage'), 'after' => '</nav>']))
  </div>
</article>

@if (comments_open() || get_comments_number())
  <div class="rv-entry">@php(comments_template())</div>
@endif
