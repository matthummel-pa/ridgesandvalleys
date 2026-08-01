{{-- PROBLEMS --}}
<section class="rv-band rv-band-alt">
  <div class="rv-shell">
    {!! \App\eyebrow(\App\field('problems_eyebrow', __('If this sounds familiar', 'sage'))) !!}
    <h2 class="rv-section-title">{{ \App\field('problems_title', __('Your site should', 'sage')) }} <em class="rv-accent">{{ \App\field('problems_accent', __('help', 'sage')) }}</em> {{ \App\field('problems_after', __('people act.', 'sage')) }}</h2>
    <div class="rv-grid rv-grid-3" style="margin-top:2.25rem">
      @php($problems = [
        ['01', \App\field('problem1_title', __('Hard to find the basics', 'sage')), \App\field('problem1_text', __('Hours, directions, and prices are buried — or living on Facebook where you don\'t control them.', 'sage'))],
        ['02', \App\field('problem2_title', __('Dated on a phone', 'sage')), \App\field('problem2_text', __('Most visitors show up on mobile, and the current site fights them the whole way.', 'sage'))],
        ['03', \App\field('problem3_title', __('Risky to update', 'sage')), \App\field('problem3_text', __('Changing a price or a photo feels like it might break the whole thing.', 'sage'))],
      ])
      @foreach ($problems as $p)
        <article class="rv-card rv-feature">
          <span class="rv-stripe rv-stripe-thin" aria-hidden="true"></span>
          <span class="rv-metric-v" style="font-size:2rem">{{ $p[0] }}</span>
          <h3 class="rv-feature-title" style="margin-top:.4rem">{{ $p[1] }}</h3>
          <p class="rv-feature-text">{{ $p[2] }}</p>
        </article>
      @endforeach
    </div>
  </div>
</section>
