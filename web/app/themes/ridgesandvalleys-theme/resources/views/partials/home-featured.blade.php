{{-- FEATURED CASE --}}
@php($featured = new WP_Query(['post_type' => 'project', 'posts_per_page' => 1, 'no_found_rows' => true]))
@php($featuredImg = \App\field('featured_img'))
@php($featuredCredit = \App\field('featured_credit'))
<section class="rv-band rv-band-alt">
  <div class="rv-shell">
    {!! \App\eyebrow(\App\field('featured_eyebrow', __('Featured work', 'sage'))) !!}
    <h2 class="rv-section-title">{{ \App\field('featured_title', __('Proof, not', 'sage')) }} <em class="rv-accent">{{ \App\field('featured_accent', __('promises.', 'sage')) }}</em></h2>
    <div class="rv-split" style="margin-top:1.75rem">
      @if ($featured->have_posts()) @php($featured->the_post())
        <div class="rv-split-media rv-media-photo">
          <img src="{{ $featuredImg ?: (has_post_thumbnail() ? get_the_post_thumbnail_url(get_the_ID(), 'rv-hero') : \App\stock_image('featured')) }}" alt="{{ get_the_title() }}" loading="lazy" onerror="this.style.display='none'">
          @if (trim($featuredCredit) !== '')<div class="rv-img-credit">{!! nl2br(e($featuredCredit)) !!}</div>@endif
        </div>
        <div class="rv-split-body">
          {!! \App\eyebrow(get_post_meta(get_the_ID(), '_rv_client', true) ?: __('Local Launch', 'sage')) !!}
          <h3 class="rv-split-title">{!! get_the_title() !!}</h3>
          <p class="rv-feature-text">{{ get_the_excerpt() ?: \App\field('featured_excerpt', __('A focused rebuild that made the business easy to reach and easy to trust — shipped in a week.', 'sage')) }}</p>
          <a class="rv-readmore" href="{{ get_permalink() }}" style="margin-top:1.25rem">{{ \App\field('featured_btn', __('Read the case study', 'sage')) }} {!! \App\icon('arrow') !!}</a>
        </div>
        @php(wp_reset_postdata())
      @else
        <div class="rv-split-media rv-media-photo">
          <img src="{{ $featuredImg ?: \App\stock_image('featured') }}" alt="{{ \App\field('featured_empty_title', __('A clearer path from visitor to consultation.', 'sage')) }}" loading="lazy" onerror="this.style.display='none'">
          @if (trim($featuredCredit) !== '')<div class="rv-img-credit">{!! nl2br(e($featuredCredit)) !!}</div>@endif
        </div>
        <div class="rv-split-body">
          {!! \App\eyebrow(\App\field('featured_empty_eyebrow', __('Bradley Goldsmith Law · Local Launch', 'sage'))) !!}
          <h3 class="rv-split-title">{{ \App\field('featured_empty_title', __('A clearer path from visitor to consultation.', 'sage')) }}</h3>
          <p class="rv-feature-text">{{ \App\field('featured_empty_text', __('A focused five-page rebuild that made the firm easy to reach and easy to trust — shipped in a week.', 'sage')) }}</p>
          <div class="rv-metric">
            @foreach (\App\field_rows('featured_metrics', [
              ['value' => __('7 days', 'sage'), 'label' => __('design to launch', 'sage')],
              ['value' => __('AA', 'sage'), 'label' => __('accessibility', 'sage')],
              ['value' => __('100%', 'sage'), 'label' => __('mobile-ready', 'sage')],
            ]) as $m)
              <div><div class="rv-metric-v">{{ $m['value'] ?? '' }}</div><div class="rv-metric-l">{{ $m['label'] ?? '' }}</div></div>
            @endforeach
          </div>
          <a class="rv-readmore" href="{{ home_url('/work/') }}" style="margin-top:1.25rem">{{ \App\field('featured_empty_btn', __('See the work', 'sage')) }} {!! \App\icon('arrow') !!}</a>
        </div>
      @endif
    </div>
  </div>
</section>
