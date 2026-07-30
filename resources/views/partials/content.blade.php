<article @php(post_class('rv-post-card'))>
  @if (has_post_thumbnail())
    <a class="rv-post-thumb" href="{{ get_permalink() }}" tabindex="-1" aria-hidden="true">
      @php(the_post_thumbnail('rv-card', ['loading' => 'lazy']))
    </a>
  @endif
  <div class="rv-post-body">
    {!! \App\post_meta() !!}
    <h2 class="rv-post-title"><a href="{{ get_permalink() }}">{!! get_the_title() !!}</a></h2>
    <div class="rv-post-excerpt">@php(the_excerpt())</div>
    <a class="rv-readmore" href="{{ get_permalink() }}">
      {{ __('Read more', 'sage') }} {!! \App\icon('arrow') !!}
      <span class="screen-reader-text">{!! get_the_title() !!}</span>
    </a>
  </div>
</article>
