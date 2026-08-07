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

    {{-- CATEGORY FILTERS: same look + in-place filtering as the Work page pills.
         Category archives stay internally linked via each post card's eyebrow,
         so converting this row to filter buttons keeps the SEO internal links. --}}
    @php($cats = get_categories(['hide_empty' => true, 'number' => 12, 'orderby' => 'count', 'order' => 'DESC']))
    @if ($cats)
      <section class="rv-shell rv-band" style="padding-bottom:0">
        <div class="rv-work-cats rv-work-filters" role="group" aria-label="{{ __('Filter posts by category', 'sage') }}">
          <span class="rv-work-cats-label">{{ __('Show me', 'sage') }}</span>
          <button type="button" class="rv-work-cat rv-filter" data-filter="all" aria-pressed="true">{{ __('All posts', 'sage') }}</button>
          @foreach ($cats as $c)
            <button type="button" class="rv-work-cat rv-filter" data-filter="{{ esc_attr($c->slug) }}" aria-pressed="false">{{ html_entity_decode($c->name) }}</button>
          @endforeach
        </div>
      </section>
    @endif

    @if (have_posts())
      {{-- FEATURED POST (first post, page 1 only): sticky-aware via the main query order --}}
      @if (! is_paged())
        @php(the_post())
        @php($featuredId = get_the_ID())
        @php($fCatSlugs = implode(' ', array_map(function ($t) { return $t->slug; }, get_the_category() ?: [])))
        <section class="rv-shell rv-band rv-featured-post rv-filter-item" data-cat="{{ esc_attr($fCatSlugs) }}">
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
            @php($cardCatSlugs = implode(' ', array_map(function ($t) { return $t->slug; }, get_the_category() ?: [])))
            <article data-cat="{{ esc_attr($cardCatSlugs) }}" @php(post_class('rv-card rv-blogcard rv-filter-item'))>
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
        <p class="rv-filter-empty" hidden>{{ __('No posts in that category on this page — try “All posts” or another category.', 'sage') }}</p>
        <nav class="rv-pagination" aria-label="{{ __('Posts navigation', 'sage') }}">{!! \App\pagination() !!}</nav>
      </section>
    @else
      <section class="rv-shell rv-band" style="padding-top:0">@include('partials.content-none')</section>
    @endif

    {{-- Journal category pills: match the Work page pills exactly (look + in-place
         filtering with a crossfade). These styles/behaviours mirror the inline
         block in template-work.blade.php; kept inline here so the Journal page
         is self-contained and needs no CSS/JS build step to pick them up. --}}
    <style>
      /* Pills — identical to the Work page's built-for range chips + filter tabs */
      .rv-blog-filterbar.rv-work-cats,
      .rv-work-cats{display:flex;flex-wrap:wrap;align-items:center;gap:.6rem;margin-top:1.75rem}
      .rv-work-cats-label{font-family:var(--font-mono);font-size:.72rem;letter-spacing:.08em;text-transform:uppercase;color:var(--color-muted);margin-right:.35rem}
      .rv-work-cat{background:color-mix(in srgb,var(--color-clay) 10%,var(--color-surface));border:1px solid color-mix(in srgb,var(--color-clay) 30%,var(--color-line));border-radius:999px;padding:.45rem 1rem;font-family:var(--font-display);font-weight:700;font-size:.92rem;color:var(--color-ink)}
      .rv-work-filters{margin-top:1.75rem}
      .rv-filter{cursor:pointer;line-height:1.2;-webkit-appearance:none;appearance:none;transition:background-color .18s ease,border-color .18s ease,color .18s ease,transform .15s ease}
      .rv-filter:hover{transform:translateY(-1px);border-color:var(--color-clay);color:var(--color-clay)}
      .rv-filter:focus-visible{outline:2px solid var(--color-clay);outline-offset:2px}
      .rv-filter[aria-pressed="true"]{background:var(--color-pine);border-color:var(--color-pine);color:#fff}
      .rv-filter[aria-pressed="true"]:hover{background:var(--color-pine);border-color:var(--color-pine);color:#fff;transform:none}
      /* Crossfade + hide, matching the Work grid */
      .rv-blog-grid{transition:opacity .25s ease}
      .rv-blog-grid.is-filtering{opacity:0}
      .rv-blogcard.is-hidden,
      .rv-featured-post.is-hidden{display:none}
      @media(prefers-reduced-motion:reduce){.rv-blog-grid{transition:none}.rv-filter{transition:none}}
    </style>

    <script>
      (function () {
        var grid = document.querySelector('.rv-blog-grid');
        var bar = document.querySelector('.rv-work-filters');
        if (!grid || !bar) return;

        var cards = Array.prototype.slice.call(grid.querySelectorAll('.rv-blogcard'));
        var featured = document.querySelector('.rv-featured-post');
        var empty = document.querySelector('.rv-filter-empty');
        var buttons = Array.prototype.slice.call(bar.querySelectorAll('.rv-filter'));
        var reduce = window.matchMedia && window.matchMedia('(prefers-reduced-motion: reduce)').matches;

        function inCat(el, filter) {
          if (filter === 'all') return true;
          var cats = (el.getAttribute('data-cat') || '').split(/\s+/);
          return cats.indexOf(filter) !== -1;
        }

        function applyFilter(filter) {
          var shown = 0;
          cards.forEach(function (card) {
            var show = inCat(card, filter);
            card.classList.toggle('is-hidden', !show);
            if (show) shown++;
          });
          if (featured) {
            featured.classList.toggle('is-hidden', !inCat(featured, filter));
          }
          if (empty) {
            var featShown = featured && !featured.classList.contains('is-hidden');
            empty.hidden = (shown > 0 || featShown);
          }
        }

        function setActive(active) {
          buttons.forEach(function (b) {
            b.setAttribute('aria-pressed', b === active ? 'true' : 'false');
          });
        }

        buttons.forEach(function (btn) {
          btn.addEventListener('click', function () {
            if (btn.getAttribute('aria-pressed') === 'true') return;
            setActive(btn);
            var filter = btn.getAttribute('data-filter');
            if (reduce) { applyFilter(filter); return; }
            // Fade the grid out, swap which cards show, then fade back in.
            grid.classList.add('is-filtering');
            window.setTimeout(function () {
              applyFilter(filter);
              requestAnimationFrame(function () {
                grid.classList.remove('is-filtering');
              });
            }, 250);
          });
        });
      })();
    </script>

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
