{{-- FEATURED WORK / PROOF --}}
@php
  $homeId = \App\home_page_id() ?: get_the_ID();
  $featuredImg = \App\field('featured_img', '', $homeId);
  $featuredCredit = \App\field('featured_credit', '', $homeId);
  $showCredit = false;
  $workHref = \App\cta_href(\App\field('proof_cta2_url', '/work/', $homeId));
  $featured = new WP_Query(['post_type' => 'project', 'posts_per_page' => 1, 'no_found_rows' => true]);
  $hasProject = $featured->have_posts();
  $projectMetrics = [];
  $storyWhere = '';
  if ($hasProject) {
    $featured->the_post();
    $projectId = get_the_ID();
    $client = (string) get_post_meta($projectId, '_rv_client', true);
    $eyebrowMeta = (string) get_post_meta($projectId, '_rv_eyebrow', true);
    $storyKicker = $eyebrowMeta !== '' ? $eyebrowMeta : ($client !== '' ? $client : __('Featured work', 'sage'));
    $storyTitleHtml = get_the_title();
    $storyTitlePlain = wp_strip_all_tags(html_entity_decode($storyTitleHtml, ENT_QUOTES, 'UTF-8'));
    $excerpt = trim((string) get_the_excerpt());
    $summary = trim((string) get_post_meta($projectId, '_rv_summary', true));
    $storyText = $excerpt !== '' ? $excerpt : ($summary !== '' ? $summary : __('A focused rebuild that made the business easy to reach and easy to trust — shipped in a week.', 'sage'));
    $storyWhere = (string) get_post_meta($projectId, '_rv_location', true);
    $storyHref = get_permalink();
    $storyCta = \App\field('proof_cta_case', __('See this project', 'sage'), $homeId);
    $projectImg = has_post_thumbnail() ? (string) get_the_post_thumbnail_url($projectId, 'rv-hero') : '';
    if ($projectImg === '') {
      $projectImg = (string) get_post_meta($projectId, '_rv_preview', true);
    }
    $imgSrc = $projectImg !== '' ? $projectImg : ($featuredImg ?: \App\stock_image('featured'));
    $showCredit = $projectImg === '';
    $imgAlt = sprintf(/* translators: %s: project title */ __('Gettysburg web design case study: %s', 'sage'), $storyTitlePlain);
    $projectMetrics = \App\home_proof_project_metrics($projectId);
    wp_reset_postdata();
  } else {
    $storyKicker = \App\field('proof_client', __('Bradley Goldsmith Law · Local Launch', 'sage'), $homeId);
    $storyTitleHtml = \App\field('proof_story_title', __('A clearer path from visitor to consultation.', 'sage'), $homeId);
    $storyTitlePlain = $storyTitleHtml;
    $storyText = \App\field('proof_story_text', __('A focused five-page rebuild that made the firm easy to reach and easy to trust — shipped in a week.', 'sage'), $homeId);
    $storyWhere = \App\field('proof_location', __('Gettysburg, PA · Adams County', 'sage'), $homeId);
    $storyHref = \App\cta_href(\App\field('proof_cta_url', '/work/', $homeId));
    $storyCta = \App\field('proof_cta', __('See the work', 'sage'), $homeId);
    $imgSrc = $featuredImg ?: \App\stock_image('featured');
    $imgAlt = __('Gettysburg-area farm buildings — featured Ridges & Valleys web design work', 'sage');
    $showCredit = true;
  }
  $metrics = $projectMetrics !== [] ? $projectMetrics : \App\field_rows('proof_metrics', \App\home_proof_metric_defaults(), $homeId);
@endphp
<section class="rv-band rv-band-alt" aria-labelledby="rv-proof-heading">
  <div class="rv-shell">
    <header class="rv-proof-head">
      {!! \App\eyebrow(\App\field('proof_eyebrow', __('Proof, not promises', 'sage'), $homeId)) !!}
      <h2 id="rv-proof-heading" class="rv-section-title">{{ \App\field('proof_title', __('Gettysburg web design', 'sage'), $homeId) }} <em class="rv-accent">{{ \App\field('proof_accent', __('results.', 'sage'), $homeId) }}</em></h2>
      <p class="rv-page-intro">{{ \App\field('proof_lede', __('A real project — then the three things every Gettysburg web design rebuild is built to do for Adams County businesses.', 'sage'), $homeId) }}</p>
    </header>

    <article class="rv-proof">
      <div class="rv-proof-visual rv-media-photo">
        <img src="{{ $imgSrc }}" alt="{{ $imgAlt }}" loading="lazy" onerror="this.style.display='none'">
        @if ($showCredit && trim($featuredCredit) !== '')<div class="rv-img-credit">{!! nl2br(e($featuredCredit)) !!}</div>@endif
      </div>
      <div class="rv-proof-body">
        {!! \App\eyebrow($storyKicker) !!}
        <h3 class="rv-proof-title">{{ $storyTitlePlain }}</h3>
        @if (trim($storyWhere) !== '')
          <p class="rv-proof-where">{{ $storyWhere }}</p>
        @endif
        <p class="rv-feature-text">{{ $storyText }}</p>
        <div class="rv-proof-actions">
          <a class="rv-btn rv-btn-primary" href="{{ $storyHref }}">{{ $storyCta }} {!! \App\icon('arrow') !!}</a>
          @if ($hasProject)
            <a class="rv-btn rv-btn-ghost" href="{{ $workHref }}">{{ \App\field('proof_cta2', __('See all work', 'sage'), $homeId) }}</a>
          @endif
        </div>
      </div>
      <ul class="rv-proof-metrics" aria-label="{{ __('Project numbers', 'sage') }}">
        @foreach ($metrics as $m)
          <li>
            <span class="rv-metric-v">{{ $m['v'] ?? '' }}</span>
            <span class="rv-metric-l">{{ $m['l'] ?? '' }}</span>
          </li>
        @endforeach
      </ul>
    </article>

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
