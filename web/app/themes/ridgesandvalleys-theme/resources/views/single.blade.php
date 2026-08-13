@extends('layouts.app')

@section('content')
  @include('partials.entry-hero')

  @php($blogId = (int) get_option('page_for_posts'))
  @php($ctaHref = \App\cta_href(\App\field('jnl_cta_url', get_theme_mod('rv_cta_url', '/contact/'), $blogId ?: null)))
  @php($singleId = 0)

  <div class="rv-shell rv-layout rv-has-sidebar rv-side-right">
    <div class="rv-content">
      @while(have_posts()) @php(the_post())
        @php($singleId = (int) get_the_ID())
        @include('partials.content-single')
      @endwhile
    </div>
    @include('partials.post-sidebar')
  </div>

  {{-- Full-width contact-style closer — the sale, not another blog widget. --}}
  @include('partials.sale-closer', ['blogId' => $blogId, 'ctaHref' => $ctaHref])

  @php($related = $singleId ? \App\related_posts(3, $singleId) : [])
  @if (! empty($related))
    <section class="rv-shell rv-band rv-single-related" aria-labelledby="rv-related-title">
      <div class="rv-headstack">
        {!! \App\eyebrow(__('Keep reading', 'sage')) !!}
        <h2 id="rv-related-title" class="rv-section-title">{{ __('More guides for', 'sage') }} <em class="rv-accent">{{ __('Adams County owners.', 'sage') }}</em></h2>
        <p class="rv-page-intro">{{ __('Same honest answers — cost, Maps, Wix vs a local designer. When you’re ready, the quote is still one click up.', 'sage') }}</p>
      </div>
      <div class="rv-blog-grid rv-jnl-related">
        @foreach ($related as $rp)
          <article class="rv-card rv-blogcard">
            @php($rimg = \App\blog_post_image($rp))
            <a class="rv-blogcard-thumb {{ $rimg ? '' : 'is-placeholder' }}" href="{{ get_permalink($rp) }}" tabindex="-1" aria-hidden="true">
              @if ($rimg)<img src="{{ $rimg }}" alt="" loading="lazy">@endif
            </a>
            <div class="rv-blogcard-body">
              @php($rcat = get_the_category($rp->ID))
              @if ($rcat)<span class="rv-eyebrow">{{ html_entity_decode($rcat[0]->name) }}</span>@endif
              <h3 class="rv-blogcard-title"><a href="{{ get_permalink($rp) }}">{!! get_the_title($rp) !!}</a></h3>
              <p class="rv-blog-meta">{{ get_the_date('', $rp) }} · {{ \App\reading_time($rp->ID) }}</p>
              <a class="rv-blogcard-more" href="{{ get_permalink($rp) }}">{{ __('Read this guide', 'sage') }} {!! \App\icon('arrow') !!}</a>
            </div>
          </article>
        @endforeach
      </div>
    </section>
  @endif

  <div class="rv-shell">
    <footer class="rv-single-nav">
      @php(the_post_navigation(['prev_text' => '<span class="rv-nav-label">' . __('Previous', 'sage') . '</span> <span class="rv-nav-title">%title</span>', 'next_text' => '<span class="rv-nav-label">' . __('Next', 'sage') . '</span> <span class="rv-nav-title">%title</span>']))
    </footer>
  </div>

  @if (comments_open() || get_comments_number())
    <div class="rv-shell rv-entry">@php(comments_template())</div>
  @endif

  <style>
    .rv-single-trust{margin-top:2.25rem}
    .rv-author{align-items:flex-start}
    .rv-author-kicker{display:block;font-family:var(--font-mono);font-size:.68rem;letter-spacing:.1em;text-transform:uppercase;color:var(--color-clay);margin-bottom:.25rem}
    .rv-author strong{display:block;margin-bottom:.25rem}
    .rv-author-more{display:inline-flex;align-items:center;gap:.4rem;margin-top:.7rem;font-weight:700;font-size:.85rem;color:var(--color-clay);text-decoration:none}
    .rv-author-more:hover{color:var(--color-pine)}
    .rv-author-more svg{width:15px;height:15px;transition:transform .2s ease}
    .rv-author-more:hover svg{transform:translateX(3px)}
    .rv-single-related{padding-block:clamp(2.25rem,4.5vw,3.5rem)}
    .rv-jnl-related{margin-top:1.5rem}
    .rv-blogcard-more{display:inline-flex;align-items:center;gap:.4rem;margin-top:auto;padding-top:.85rem;font-weight:700;font-size:.85rem;color:var(--color-clay);text-decoration:none}
    .rv-blogcard-more svg{width:15px;height:15px;transition:transform .2s ease}
    .rv-blogcard:hover .rv-blogcard-more svg{transform:translateX(3px)}
    @media(prefers-reduced-motion:reduce){.rv-author-more svg,.rv-blogcard-more svg{transition:none}}
  </style>
@endsection
