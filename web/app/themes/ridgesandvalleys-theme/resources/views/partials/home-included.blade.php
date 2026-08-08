{{-- INCLUDED IN EVERY BUILD --}}
<section class="rv-band rv-band-alt">
  <div class="rv-shell">
    {!! \App\eyebrow(\App\field('included_eyebrow', __('No surprises', 'sage'))) !!}
    <h2 class="rv-section-title">{{ \App\field('included_title', __('Included in', 'sage')) }} <em class="rv-accent">{{ \App\field('included_accent', __('every', 'sage')) }}</em> {{ \App\field('included_title_end', __('build.', 'sage')) }}</h2>
    <div class="rv-grid rv-grid-3" style="margin-top:2rem">
      @foreach (\App\field_rows('included_items', [
        ['title' => __('Accessibility-first', 'sage'), 'text' => __('Built to WCAG 2.2 AA — readable contrast, keyboard navigation, and screen-reader-friendly structure on every page.', 'sage')],
        ['title' => __('Mobile-first', 'sage'), 'text' => __('Designed for the phone first, because that\'s where most of your visitors actually are.', 'sage')],
        ['title' => __('You own everything', 'sage'), 'text' => __('Your domain, your hosting account, your site. No lock-in, and you can leave a care plan anytime.', 'sage')],
        ['title' => __('Found locally', 'sage'), 'text' => __('Google Business Profile setup, on-page SEO, and the local foundations that put you on the map.', 'sage')],
        ['title' => __('Fast & secure', 'sage'), 'text' => __('Lean, page-builder-free code, HTTPS, backups, and a hosting setup tuned to load quickly.', 'sage')],
        ['title' => __('A real training handoff', 'sage'), 'text' => __('A short walkthrough (and a video) so you can update the few things you\'ll actually touch.', 'sage')],
      ]) as $inc)
        <article class="rv-card rv-feature">
          <span class="rv-stripe rv-stripe-thin" aria-hidden="true"></span>
          <h3 class="rv-feature-title">{{ $inc['title'] ?? '' }}</h3>
          <p class="rv-feature-text">{{ $inc['text'] ?? '' }}</p>
        </article>
      @endforeach
    </div>
  </div>
</section>
