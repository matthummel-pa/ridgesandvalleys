<form role="search" method="get" class="rv-searchform" action="{{ home_url('/') }}">
  <label for="rv-search" class="screen-reader-text">{{ __('Search for:', 'sage') }}</label>
  <input type="search" id="rv-search" class="rv-search-input" placeholder="{{ __('Search…', 'sage') }}" value="{{ get_search_query() }}" name="s">
  <button type="submit" class="rv-btn rv-btn-primary">{{ __('Search', 'sage') }}</button>
</form>
