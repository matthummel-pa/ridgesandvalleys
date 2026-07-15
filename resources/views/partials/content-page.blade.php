@php
  // Pattern-driven pages (sections wrapped in .prt-wrap) render full-bleed so
  // the blocks control their own width; plain prose keeps the readable column.
  $prtCanvas = str_contains((string) get_the_content(), 'prt-wrap');
@endphp
@if ($prtCanvas)
  <article @php(post_class('prt-canvas'))>
    @php(the_content())
  </article>
@else
  <article @php(post_class())>
    <div class="entry-content post-prose">
      @php(the_content())
    </div>
  </article>
@endif
