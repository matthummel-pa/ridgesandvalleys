@extends('layouts.app')

@section('content')
  @if (is_home() && ! is_front_page())

    {{-- ============================ LOCAL-SEO BLOG LANDING ============================ --}}
    @php($blogId = (int) get_option('page_for_posts'))
    @php($introHtml = $blogId ? trim(get_post_field('post_content', $blogId)) : '')
    @php($ctaHref = \App\cta_href(get_theme_mod('rv_cta_url', '/contact/')))

    {{-- HEADER: bold hero (matches the other pages) --}}
    <section class="rv-hero">
      <span class="rv-stripe" aria-hidden="true"></span>
      @include('partials.hero-bg', ['fallback' => \App\stock_image('process'), 'postId' => $blogId])
      <div class="rv-shell rv-hero-inner">
        {!! \App\eyebrow(__('The Journal', 'sage')) !!}
        <h1 class="rv-hero-title">{{ __('Get found.', 'sage') }} <em class="rv-accent">{{ __('Get chosen.', 'sage') }}</em></h1>
        @if ($introHtml)
          <div class="rv-hero-sub">{!! apply_filters('the_content', $introHtml) !!}</div>
        @else
          <p class="rv-hero-sub">{{ __('Practical web design and local SEO tips for Gettysburg, Adams County, and South Central PA business owners — plain-English guidance on turning your website into more calls, bookings, and walk-ins, from a local developer who builds these sites every week.', 'sage') }}</p>
        @endif
        <div class="rv-hero-actions">
          <a class="rv-btn {{ \App\hero_btn_class(\App\field('hero_btn1_style',''), 'rv-btn-primary') }}" href="#latest">{{ \App\field('hero_btn1', __('Read the latest', 'sage')) }}</a>
          <a class="rv-btn {{ \App\hero_btn_class(\App\field('hero_btn2_style',''), 'rv-btn-ghost') }}" href="{{ home_url('/free-tools/') }}">{{ \App\field('hero_btn2', __('Try the free tools', 'sage')) }}</a>
        </div>
      </div>
    </section>

    {{-- CATEGORY LINKS (internal linking) --}}
    @php($cats = get_categories(['hide_empty' => true, 'number' => 12, 'orderby' => 'count', 'order' => 'DESC']))
    @if ($cats)
      <section class="rv-shell rv-band" style="padding-bottom:0">
        <div class="rv-chips">
          <a class="rv-mchip" href="{{ $blogId ? get_permalink($blogId) : home_url('/') }}">{{ __('All posts', 'sage') }}</a>
          @foreach ($cats as $c)
            <a class="rv-mchip" href="{{ get_category_link($c->term_id) }}">{{ html_entity_decode($c->name) }}</a>
          @endforeach
        </div>
      </section>
    @endif

    @if (have_posts())
      {{-- FEATURED POST (first post, page 1 only): sticky-aware via the main query order --}}
      @if (! is_paged())
        @php(the_post())
        @php($featuredId = get_the_ID())
        <section class="rv-shell rv-band rv-featured-post">
          {!! \App\eyebrow(__('Featured', 'sage')) !!}
          <div class="rv-split" style="margin-top:1rem">
            @php($fimg = \App\blog_post_image())
            <a class="rv-split-media rv-media-photo {{ $fimg ? '' : 'is-placeholder' }}" href="{{ get_permalink() }}" aria-label="{{ get_the_title() }}">
              @if ($fimg)
                <img src="{{ $fimg }}" alt="{{ get_the_title() }}" loading="lazy" onerror="this.style.display='none'">
              @endif
            </a>
            <div class="rv-split-body">
              @php($fc = get_the_category())
              {!! \App\eyebrow($fc ? $fc[0]->name : __('Latest', 'sage')) !!}
              <h2 class="rv-split-title"><a href="{{ get_permalink() }}">{!! get_the_title() !!}</a></h2>
              <p class="rv-feature-text">{{ html_entity_decode(get_the_excerpt()) }}</p>
              <p class="rv-blog-meta">{{ get_the_date() }} · {{ \App\reading_time() }}</p>
              <a class="rv-readmore" href="{{ get_permalink() }}" style="margin-top:1rem">{{ __('Read the post', 'sage') }} {!! \App\icon('arrow') !!}</a>
            </div>
          </div>
        </section>
        @php(rewind_posts())
      @else
        @php($featuredId = 0)
      @endif

      {{-- POST GRID --}}
      <section id="latest" class="rv-shell rv-band" style="padding-top:0;scroll-margin-top:6rem">
        <h2 class="rv-section-title">{{ __('Latest from the', 'sage') }} <em class="rv-accent">{{ __('Journal', 'sage') }}</em></h2>
        <div class="rv-blog-grid" style="margin-top:2rem">
          @php($i = 0)
          @while(have_posts()) @php(the_post())
            @if (! is_paged() && get_the_ID() === $featuredId && $i === 0)
              @php($i++)
              @continue
            @endif
            @php($i++)
            <article @php(post_class('rv-card rv-blogcard'))>
              @php($cimg = \App\blog_post_image())
              <a class="rv-blogcard-thumb {{ $cimg ? '' : 'is-placeholder' }}" href="{{ get_permalink() }}" tabindex="-1" aria-hidden="true">
                @if ($cimg)<img src="{{ $cimg }}" alt="{{ get_the_title() }}" loading="lazy">@endif
              </a>
              <div class="rv-blogcard-body">
                @php($cc = get_the_category())
                @if ($cc)<span class="rv-eyebrow"><a href="{{ get_category_link($cc[0]->term_id) }}" style="color:inherit;text-decoration:none">{{ html_entity_decode($cc[0]->name) }}</a></span>@endif
                <h3 class="rv-blogcard-title"><a href="{{ get_permalink() }}">{!! get_the_title() !!}</a></h3>
                @if (has_excerpt() || get_the_excerpt())<p class="rv-blogcard-excerpt">{{ html_entity_decode(wp_trim_words(get_the_excerpt(), 22)) }}</p>@endif
                <p class="rv-blog-meta">{{ get_the_date() }} · {{ \App\reading_time() }}</p>
              </div>
            </article>
          @endwhile
        </div>
        <nav class="rv-pagination" aria-label="{{ __('Posts navigation', 'sage') }}">{!! \App\pagination() !!}</nav>
      </section>
    @else
      <section class="rv-shell rv-band" style="padding-top:0">@include('partials.content-none')</section>
    @endif

    {{-- NEWSLETTER: moved to the site footer (sections/footer.blade.php) --}}

  @else

    {{-- ============================ GENERIC LISTING (fallback) ============================ --}}
    @php($rvL = \App\entry_layout())
    <div class="rv-shell rv-layout {{ $rvL['sidebar'] !== 'none' ? 'rv-has-sidebar rv-side-'.$rvL['sidebar'] : '' }}">
      <div class="rv-content">
        @if (have_posts())
          <div class="rv-post-list">
            @while(have_posts()) @php(the_post())
              @includeFirst(['partials.content-' . get_post_type(), 'partials.content'])
            @endwhile
          </div>
          <nav class="rv-pagination" aria-label="{{ __('Posts navigation', 'sage') }}">{!! \App\pagination() !!}</nav>
        @else
          @include('partials.content-none')
        @endif
      </div>
      @if ($rvL['sidebar'] !== 'none')@include('sections.sidebar')@endif
    </div>

  @endif
@endsection
