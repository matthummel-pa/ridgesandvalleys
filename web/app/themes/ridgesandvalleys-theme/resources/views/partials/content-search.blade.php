<article @php(post_class('rv-search-item'))>
  <h2 class="rv-search-title"><a href="{{ get_permalink() }}">{!! get_the_title() !!}</a></h2>
  <p class="rv-search-type">{{ get_post_type_object(get_post_type())->labels->singular_name }}</p>
  <div class="rv-search-excerpt">@php(the_excerpt())</div>
</article>
