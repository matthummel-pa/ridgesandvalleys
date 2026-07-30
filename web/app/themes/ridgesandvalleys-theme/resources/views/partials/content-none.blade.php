<section class="rv-none">
  <h2 class="rv-none-title">{{ __('Nothing here yet', 'sage') }}</h2>
  @if (is_search())
    <p>{{ __('No results matched your search. Try different words.', 'sage') }}</p>
    @php(get_search_form())
  @else
    <p>{{ __('There is no content to show here right now — check back soon.', 'sage') }}</p>
    <p><a class="rv-btn rv-btn-ghost" href="{{ home_url('/') }}">{{ __('Back to home', 'sage') }}</a></p>
  @endif
</section>
