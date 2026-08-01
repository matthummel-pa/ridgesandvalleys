{{-- PROCESS TIMELINE --}}
<section class="rv-shell rv-band">
  {!! \App\eyebrow(\App\field('htime_eyebrow', __('Groundwork to launch', 'sage'))) !!}
  <h2 class="rv-section-title">{{ \App\field('htime_title', __('First draft in', 'sage')) }} <em class="rv-accent">{{ \App\field('htime_accent', __('days,', 'sage')) }}</em> {{ \App\field('htime_title_end', __('not months.', 'sage')) }}</h2>
  <div class="rv-timeline" style="margin-top:2rem">
    @foreach (\App\field_rows('htime_items', [
      ['day' => __('Before Day 1', 'sage'), 'title' => __('Fit & scope', 'sage'), 'text' => __('Call, signed scope, deposit.', 'sage')],
      ['day' => __('Day 1', 'sage'), 'title' => __('One intake form', 'sage'), 'text' => __('You send info + assets.', 'sage')],
      ['day' => __('Day 3', 'sage'), 'title' => __('Working draft', 'sage'), 'text' => __('Staging site + walkthrough.', 'sage')],
      ['day' => __('Day 6', 'sage'), 'title' => __('One revision', 'sage'), 'text' => __('Consolidated feedback.', 'sage')],
      ['day' => __('Days 7–10', 'sage'), 'title' => __('Launch', 'sage'), 'text' => __('Handoff + training.', 'sage')],
    ]) as $t)
      <div class="rv-tl-step">
        <span class="rv-tl-day">{{ $t['day'] ?? '' }}</span>
        <h3 class="rv-tl-title">{{ $t['title'] ?? '' }}</h3>
        <p>{{ $t['text'] ?? '' }}</p>
      </div>
    @endforeach
  </div>
</section>
