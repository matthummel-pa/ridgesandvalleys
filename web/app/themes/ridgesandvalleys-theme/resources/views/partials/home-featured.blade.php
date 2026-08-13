{{-- FEATURED WORK / PROOF — one project box per concept industry type. --}}
@php
  $homeId = \App\home_page_id() ?: get_the_ID();
  $featuredImg = \App\field('featured_img', '', $homeId);
  $featuredCredit = \App\field('featured_credit', '', $homeId);
  $workHref = \App\cta_href(\App\field('proof_cta2_url', '/work/', $homeId));
  $storyCta = \App\field('proof_cta_case', __('See this project', 'sage'), $homeId);
  $boxes = [];
  foreach (\App\home_proof_type_projects() as $rvProofPost) {
      $boxes[] = \App\home_proof_box_data($rvProofPost, $featuredImg ?: \App\stock_image('featured'));
  }
@endphp
<section class="rv-band rv-band-alt" aria-labelledby="rv-proof-heading">
  <div class="rv-shell">
    <header class="rv-proof-head">
      {!! \App\eyebrow(\App\field('proof_eyebrow', __('Proof, not promises', 'sage'), $homeId)) !!}
      <h2 id="rv-proof-heading" class="rv-section-title">{{ \App\field('proof_title', __('Gettysburg web design', 'sage'), $homeId) }} <em class="rv-accent">{{ \App\field('proof_accent', __('results.', 'sage'), $homeId) }}</em></h2>
      <p class="rv-page-intro">{{ \App\field('proof_lede_types', __('One live concept for each kind of Adams County business — restaurant, inn, shop, tours. Open a demo, then see what the rebuild is built to do.', 'sage'), $homeId) }}</p>
    </header>

    @if (count($boxes))
      <div class="rv-proof-list">
        @foreach ($boxes as $box)
          @include('partials.home-proof-box', ['box' => $box, 'cta' => $storyCta, 'credit' => $featuredCredit])
        @endforeach
      </div>
      <div class="rv-proof-more">
        <a class="rv-btn rv-btn-ghost" href="{{ $workHref }}">{{ \App\field('proof_cta2', __('See all work', 'sage'), $homeId) }}</a>
      </div>
    @else
      @php
        $fallback = [
          'id' => 0, 'type' => '',
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
      @include('partials.home-proof-box', ['box' => $fallback, 'cta' => \App\field('proof_cta', __('See the work', 'sage'), $homeId), 'credit' => $featuredCredit])
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
