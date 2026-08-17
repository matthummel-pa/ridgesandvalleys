{{-- FEATURED WORK / PROOF — filterable three-column project cards (max three shown). --}}
@php
  $homeId = \App\home_page_id() ?: get_the_ID();
  $featuredImg = \App\field('featured_img', '', $homeId);
  $featuredCredit = \App\field('featured_credit', '', $homeId);
  $workHref = \App\cta_href(\App\field('proof_cta2_url', '/work/', $homeId));
  $storyCta = \App\field('proof_cta_case', __('See this project', 'sage'), $homeId);
  $proofPosts = \App\home_proof_type_projects();
  $proofFilters = \App\work_filter_categories($proofPosts);
  $proofLimit = \App\home_proof_grid_limit();
  $boxes = [];
  foreach ($proofPosts as $rvProofPost) {
      $boxes[] = \App\home_proof_box_data($rvProofPost, $featuredImg ?: \App\stock_image('featured'));
  }
  $proofDefault = \App\field(
      'proof_lede_types',
      __('Filter by industry. Three live concepts at a time — screenshot on top, the story underneath.', 'sage'),
      $homeId
  );
@endphp
<section class="rv-band rv-band-alt" aria-labelledby="rv-proof-heading" data-rv-home-proof>
  <div class="rv-shell">
    <header class="rv-proof-head">
      {!! \App\eyebrow(\App\field('proof_eyebrow', __('Proof, not promises', 'sage'), $homeId)) !!}
      <h2 id="rv-proof-heading" class="rv-section-title">{{ \App\field('proof_title', __('Gettysburg web design', 'sage'), $homeId) }} <em class="rv-accent">{{ \App\field('proof_accent', __('results.', 'sage'), $homeId) }}</em></h2>
      <p class="rv-page-intro">{{ $proofDefault }}</p>
    </header>

    @if (count($boxes))
      @if (count($proofFilters) > 1)
        <div class="rv-work-cats rv-work-filters rv-proof-filters" data-rv-home-proof-filters role="group" aria-label="{{ __('Filter projects by industry', 'sage') }}">
          <span class="rv-work-cats-label">{{ \App\field('proof_cats_label', __('Show me', 'sage'), $homeId) }}</span>
          <button type="button" class="rv-work-cat rv-filter" data-filter="all" aria-pressed="true">{{ __('Show all', 'sage') }} <span class="rv-work-cat-n">{{ count($boxes) }}</span></button>
          @foreach ($proofFilters as $rvSlug => $rvRow)
            <button type="button" class="rv-work-cat rv-filter" data-filter="{{ esc_attr($rvSlug) }}" aria-pressed="false">{{ $rvRow['label'] }} <span class="rv-work-cat-n">{{ $rvRow['count'] }}</span></button>
          @endforeach
        </div>
      @endif
      <p class="rv-proof-count" data-rv-home-proof-count aria-live="polite">{{ sprintf(
        _n('Showing %d project', 'Showing %d projects', min($proofLimit, count($boxes)), 'sage'),
        min($proofLimit, count($boxes))
      ) }}</p>
      <div class="rv-proof-list" data-rv-home-proof-grid data-limit="{{ (int) $proofLimit }}">
        @foreach ($boxes as $i => $box)
          @include('partials.home-proof-box', [
            'box' => $box,
            'cta' => $storyCta,
            'credit' => $featuredCredit,
            'hidden' => $i >= $proofLimit,
          ])
        @endforeach
      </div>
      <p class="rv-proof-empty" data-rv-home-proof-empty hidden>{{ __('Nothing in this category yet.', 'sage') }} <button type="button" class="rv-work-empty-all">{{ __('Show all', 'sage') }}</button></p>
      <div class="rv-proof-more">
        <a class="rv-btn rv-btn-ghost" href="{{ $workHref }}">{{ \App\field('proof_cta2', __('See all work', 'sage'), $homeId) }}</a>
      </div>
    @else
      @php
        $fallback = [
          'id' => 0, 'cat' => '', 'type' => '',
          'kicker' => \App\field('proof_client', __('Bradley Goldsmith Law · Local Launch', 'sage'), $homeId),
          'title' => \App\field('proof_story_title', __('A clearer path from visitor to consultation.', 'sage'), $homeId),
          'where' => \App\field('proof_location', __('Gettysburg, PA · Adams County', 'sage'), $homeId),
          'text' => \App\field('proof_story_text', __('A focused five-page rebuild that made the firm easy to reach and easy to trust — shipped in a week.', 'sage'), $homeId),
          'href' => \App\cta_href(\App\field('proof_cta_url', '/work/', $homeId)),
          'img' => $featuredImg ?: \App\stock_image('featured'),
          'alt' => __('Gettysburg-area farm buildings — featured Ridges & Valleys web design work', 'sage'),
          'show_credit' => true,
          'metrics' => \App\field_rows('proof_metrics', \App\home_proof_metric_defaults(), $homeId),
        ];
      @endphp
      <div class="rv-proof-list rv-proof-list--fallback">
        @include('partials.home-proof-box', ['box' => $fallback, 'cta' => \App\field('proof_cta', __('See the work', 'sage'), $homeId), 'credit' => $featuredCredit, 'hidden' => false])
      </div>
    @endif

    <div class="rv-proof-outcomes">
      <p class="rv-proof-outcomes-label">{{ \App\field('proof_outcomes_label', __('What the site has to do', 'sage'), $homeId) }}</p>
      <ul class="rv-proof-outcomes-grid">
        @foreach (\App\field_rows('proof_points', \App\home_proof_point_defaults(), $homeId) as $pt)
          <li class="rv-card rv-proof-outcome">
            <span class="rv-stripe rv-stripe-thin" aria-hidden="true"></span>
            <h3>{{ $pt['title'] ?? '' }}</h3>
            <p>{{ $pt['text'] ?? '' }}</p>
          </li>
        @endforeach
      </ul>
    </div>
  </div>
</section>
