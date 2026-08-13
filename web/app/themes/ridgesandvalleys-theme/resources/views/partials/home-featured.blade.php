{{-- FEATURED WORK / PROOF --}}
@php
  $homeId = \App\home_page_id() ?: get_the_ID();
  $featuredImg = \App\field('featured_img', '', $homeId);
  $featuredCredit = \App\field('featured_credit', '', $homeId);
  $workHref = \App\cta_href(\App\field('proof_cta2_url', '/work/', $homeId));
  $featured = new WP_Query(['post_type' => 'project', 'posts_per_page' => 1, 'no_found_rows' => true]);
  $hasProject = $featured->have_posts();
  if ($hasProject) {
    $featured->the_post();
    $storyKicker = get_post_meta(get_the_ID(), '_rv_client', true) ?: __('Featured work', 'sage');
    $storyTitleHtml = get_the_title();
    $storyTitlePlain = wp_strip_all_tags(html_entity_decode($storyTitleHtml, ENT_QUOTES, 'UTF-8'));
    $storyText = get_the_excerpt() ?: __('A focused rebuild that made the business easy to reach and easy to trust — shipped in a week.', 'sage');
    $storyHref = get_permalink();
    $storyCta = \App\field('proof_cta_case', __('Read the case study', 'sage'), $homeId);
    $imgSrc = $featuredImg ?: (has_post_thumbnail() ? get_the_post_thumbnail_url(get_the_ID(), 'rv-hero') : \App\stock_image('featured'));
    $imgAlt = sprintf(/* translators: %s: project title */ __('Gettysburg web design case study: %s', 'sage'), $storyTitlePlain);
    wp_reset_postdata();
  } else {
    $storyKicker = \App\field('proof_client', __('Bradley Goldsmith Law · Local Launch', 'sage'), $homeId);
    $storyTitleHtml = \App\field('proof_story_title', __('A clearer path from visitor to consultation.', 'sage'), $homeId);
    $storyTitlePlain = $storyTitleHtml;
    $storyText = \App\field('proof_story_text', __('A focused five-page rebuild that made the firm easy to reach and easy to trust — shipped in a week.', 'sage'), $homeId);
    $storyHref = \App\cta_href(\App\field('proof_cta_url', '/work/', $homeId));
    $storyCta = \App\field('proof_cta', __('See the work', 'sage'), $homeId);
    $imgSrc = $featuredImg ?: \App\stock_image('featured');
    $imgAlt = __('Gettysburg-area farm buildings — featured Ridges & Valleys web design work', 'sage');
  }
@endphp
<section class="rv-band rv-band-alt" aria-labelledby="rv-proof-heading">
  <div class="rv-shell">
    <header class="rv-proof-head">
      {!! \App\eyebrow(\App\field('proof_eyebrow', __('Proof, not promises', 'sage'), $homeId)) !!}
      <h2 id="rv-proof-heading" class="rv-section-title">{{ \App\field('proof_title', __('Gettysburg web design', 'sage'), $homeId) }} <em class="rv-accent">{{ \App\field('proof_accent', __('results.', 'sage'), $homeId) }}</em></h2>
      <p class="rv-page-intro">{{ \App\field('proof_lede', __('Real work for Adams County businesses — not stock screenshots and a pitch deck. Here’s what a focused rebuild actually looks like.', 'sage'), $homeId) }}</p>
    </header>

    <article class="rv-proof">
      <div class="rv-proof-visual rv-media-photo">
        <img src="{{ $imgSrc }}" alt="{{ $imgAlt }}" loading="lazy" onerror="this.style.display='none'">
        @if (trim($featuredCredit) !== '')<div class="rv-img-credit">{!! nl2br(e($featuredCredit)) !!}</div>@endif
      </div>
      <div class="rv-proof-body">
        {!! \App\eyebrow($storyKicker) !!}
        <h3 class="rv-proof-title">{{ $storyTitlePlain }}</h3>
        <p class="rv-feature-text">{{ $storyText }}</p>
        <ul class="rv-scan">
          @foreach (\App\field_rows('proof_points', \App\home_proof_point_defaults(), $homeId) as $i => $pt)
            <li>
              <span class="rv-scan-n" aria-hidden="true">{{ str_pad((string) ($i + 1), 2, '0', STR_PAD_LEFT) }}</span>
              <div>
                <h4>{{ $pt['title'] ?? '' }}</h4>
                <p>{{ $pt['text'] ?? '' }}</p>
              </div>
            </li>
          @endforeach
        </ul>
        <div class="rv-proof-actions">
          <a class="rv-btn rv-btn-primary" href="{{ $storyHref }}">{{ $storyCta }} {!! \App\icon('arrow') !!}</a>
          @if ($hasProject)
            <a class="rv-btn rv-btn-ghost" href="{{ $workHref }}">{{ \App\field('proof_cta2', __('See all work', 'sage'), $homeId) }}</a>
          @endif
        </div>
      </div>
      <ul class="rv-proof-metrics" aria-label="{{ __('What you can count on', 'sage') }}">
        @foreach (\App\field_rows('proof_metrics', \App\home_proof_metric_defaults(), $homeId) as $m)
          <li>
            <span class="rv-metric-v">{{ $m['v'] ?? '' }}</span>
            <span class="rv-metric-l">{{ $m['l'] ?? '' }}</span>
          </li>
        @endforeach
      </ul>
    </article>
  </div>
</section>
