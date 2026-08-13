@extends('layouts.app')

@section('content')
  @if (is_home() && ! is_front_page())

    @php($blogId = (int) get_option('page_for_posts'))
    @php($bid = $blogId ?: null)
    @php($ctaHref = \App\cta_href(\App\field('jnl_cta_url', get_theme_mod('rv_cta_url', '/contact/'), $bid)))

    {{-- HERO — same pattern as Home / About / Work: keyword H1, two CTAs, note, proof. --}}
    <section class="rv-hero" aria-labelledby="rv-jnl-hero-title">
      <span class="rv-stripe" aria-hidden="true"></span>
      @include('partials.hero-bg', ['fallback' => \App\stock_image('process'), 'postId' => $blogId])
      <div class="rv-shell rv-hero-inner">
        {!! \App\eyebrow(\App\field('jnl_kicker', __('Gettysburg web design journal', 'sage'), $bid)) !!}
        <h1 id="rv-jnl-hero-title" class="rv-hero-title">{{ \App\field('jnl_h1', __('Gettysburg web design advice you can', 'sage'), $bid) }} <em class="rv-accent">{{ \App\field('jnl_h1_accent', __('actually use.', 'sage'), $bid) }}</em></h1>
        <p class="rv-hero-sub">{{ \App\field('jnl_lede', __('Honest guides for Gettysburg and Adams County owners — cost, Google Maps, Wix vs a local designer, and whether your site is quietly costing you calls. Read one, then get a quote.', 'sage'), $bid) }}</p>
        <div class="rv-hero-actions">
          <a class="rv-btn rv-btn-primary" href="{{ $ctaHref }}">{{ \App\field('jnl_cta', __('Get a quote', 'sage'), $bid) }}</a>
          <a class="rv-btn rv-btn-ghost" href="#latest">{{ \App\field('jnl_cta2', __('Read the latest', 'sage'), $bid) }} &darr;</a>
        </div>
        <p class="rv-hero-note">{{ \App\field('jnl_note', __('Plain English · Local SEO · No fluff', 'sage'), $bid) }}</p>
      </div>
      @php($jnlProof = \App\journal_proof($bid))
      @if (! empty($jnlProof))
        <div class="rv-hero-proof">
          <div class="rv-shell">
            <ul class="rv-hero-stats" aria-label="{{ __('At a glance', 'sage') }}">
              @foreach ($jnlProof as $pf)
                <li>
                  <span class="rv-hero-stat-v">{{ $pf['v'] ?? '' }}</span>
                  @if (($pf['l'] ?? '') !== '')<span class="rv-hero-stat-l">{{ $pf['l'] }}</span>@endif
                </li>
              @endforeach
            </ul>
          </div>
        </div>
      @endif
    </section>

    {{-- How to use this journal — split funnel under the hero. --}}
    <section class="rv-band rv-band-alt rv-jnl-why" aria-labelledby="rv-jnl-why-title">
      <div class="rv-shell rv-jnl-why-grid">
        <div class="rv-jnl-why-copy">
          {!! \App\eyebrow(\App\field('jnl_why_eyebrow', __('How to use this journal', 'sage'), $bid)) !!}
          <h2 id="rv-jnl-why-title" class="rv-section-title">{{ \App\field('jnl_why_title', __('Not a blog.', 'sage'), $bid) }} <em class="rv-accent">{{ \App\field('jnl_why_accent', __('Buying guides.', 'sage'), $bid) }}</em></h2>
          <p class="rv-page-intro">{!! \App\field('jnl_why_intro', __('<strong>These posts are here to help you decide.</strong> What a site should cost, whether you need one, how to show up on Maps — written for owners around Gettysburg, not developers. Pick a question, read the honest answer, then get a quote if it fits.', 'sage'), $bid) !!}</p>
          <div class="rv-jnl-why-actions">
            <a class="rv-btn rv-btn-ghost" href="#latest">{{ \App\field('jnl_why_jump', __('Browse the guides', 'sage'), $bid) }}</a>
            <a class="rv-jnl-why-jump" href="{{ $ctaHref }}">{{ \App\field('jnl_why_cta', __('Get a quote', 'sage'), $bid) }} {!! \App\icon('arrow') !!}</a>
          </div>
        </div>
        <div class="rv-jnl-why-list">
          @foreach (\App\field_rows('jnl_why_items', \App\journal_why_item_defaults(), $bid) as $v)
            <article class="rv-jnl-why-item">
              @if (($v['kicker'] ?? '') !== '')<span class="rv-jnl-why-kicker">{{ $v['kicker'] }}</span>@endif
              @if (($v['title'] ?? '') !== '')<h3>{{ $v['title'] }}</h3>@endif
              @if (($v['text'] ?? '') !== '')<p>{{ $v['text'] }}</p>@endif
            </article>
          @endforeach
        </div>
      </div>
    </section>

    @php($cats = get_categories(['hide_empty' => true, 'number' => 12, 'orderby' => 'count', 'order' => 'DESC']))
    @if (have_posts())
      <section class="rv-shell rv-band rv-jnl-list" id="latest" style="scroll-margin-top:6rem">
        <div class="rv-headstack">
          {!! \App\eyebrow(\App\field('jnl_list_eyebrow', __('The journal', 'sage'), $bid)) !!}
          <h2 class="rv-section-title">{{ \App\field('jnl_list_title', __('Guides for', 'sage'), $bid) }} <em class="rv-accent">{{ \App\field('jnl_list_accent', __('Adams County owners.', 'sage'), $bid) }}</em></h2>
          <p class="rv-page-intro">{{ \App\field('jnl_list_intro', __('Filter by topic. Read the one that matches the snag you’re in. When you’re ready to stop diagnosing and start building, the quote is one click away.', 'sage'), $bid) }}</p>
        </div>

        @if (! is_paged())
          @php(the_post())
          @php($featuredId = get_the_ID())
          @php($fc = get_the_category())
          @php($fimg = \App\blog_post_image())
          <article class="rv-jnl-feature">
            <a class="rv-jnl-feature-shot {{ $fimg ? '' : 'is-placeholder' }}" href="{{ get_permalink() }}" aria-hidden="true" tabindex="-1">
              @if ($fimg)<img src="{{ $fimg }}" alt="" loading="eager">@endif
              <span class="rv-jnl-feature-badge">{{ __('Start here', 'sage') }}</span>
            </a>
            <div class="rv-jnl-feature-body">
              {!! \App\eyebrow($fc ? html_entity_decode($fc[0]->name) : __('Featured', 'sage')) !!}
              <h3 class="rv-jnl-feature-title"><a href="{{ get_permalink() }}">{!! get_the_title() !!}</a></h3>
              <p class="rv-jnl-feature-excerpt">{{ html_entity_decode(get_the_excerpt()) }}</p>
              <p class="rv-blog-meta">{{ get_the_date() }} · {{ \App\reading_time() }}</p>
              <div class="rv-jnl-feature-actions">
                <a class="rv-btn rv-btn-primary" href="{{ get_permalink() }}">{{ __('Read this guide', 'sage') }}</a>
                <a class="rv-jnl-why-jump" href="{{ $ctaHref }}">{{ __('Get a quote', 'sage') }} {!! \App\icon('arrow') !!}</a>
              </div>
            </div>
          </article>
          @php(rewind_posts())
        @else
          @php($featuredId = 0)
        @endif

        @if ($cats)
          <div class="rv-work-cats rv-work-filters" role="group" aria-label="{{ __('Filter posts by topic', 'sage') }}">
            <span class="rv-work-cats-label">{{ \App\field('jnl_cats_label', __('Show me', 'sage'), $bid) }}</span>
            <button type="button" class="rv-work-cat rv-filter" data-filter="all" aria-pressed="true">{{ __('All guides', 'sage') }}</button>
            @foreach ($cats as $c)
              <button type="button" class="rv-work-cat rv-filter" data-filter="{{ esc_attr($c->slug) }}" aria-pressed="false">{{ html_entity_decode($c->name) }} <span class="rv-work-cat-n">{{ (int) $c->count }}</span></button>
            @endforeach
          </div>
        @endif

        <div class="rv-blog-grid rv-jnl-grid" id="rv-jnl-grid">
          @php($i = 0)
          @while(have_posts()) @php(the_post())
            @if (! is_paged() && get_the_ID() === $featuredId && $i === 0)
              @php($i++)
              @continue
            @endif
            @php($i++)
            @php($cardCatSlugs = implode(' ', array_map(function ($t) { return $t->slug; }, get_the_category() ?: [])))
            @php($cc = get_the_category())
            @php($cimg = \App\blog_post_image())
            <article data-cat="{{ esc_attr($cardCatSlugs) }}" @php(post_class('rv-card rv-blogcard rv-filter-item'))>
              <a class="rv-blogcard-thumb {{ $cimg ? '' : 'is-placeholder' }}" href="{{ get_permalink() }}" tabindex="-1" aria-hidden="true">
                @if ($cimg)<img src="{{ $cimg }}" alt="" loading="lazy">@endif
              </a>
              <div class="rv-blogcard-body">
                @if ($cc)<span class="rv-eyebrow">{{ html_entity_decode($cc[0]->name) }}</span>@endif
                <h3 class="rv-blogcard-title"><a href="{{ get_permalink() }}">{!! get_the_title() !!}</a></h3>
                @if (has_excerpt() || get_the_excerpt())<p class="rv-blogcard-excerpt">{{ html_entity_decode(wp_trim_words(get_the_excerpt(), 22)) }}</p>@endif
                <p class="rv-blog-meta">{{ get_the_date() }} · {{ \App\reading_time() }}</p>
                <a class="rv-blogcard-more" href="{{ get_permalink() }}">{{ __('Read this guide', 'sage') }} {!! \App\icon('arrow') !!}</a>
              </div>
            </article>
          @endwhile
        </div>
        <p class="rv-filter-empty" hidden>{{ __('No guides in that topic on this page — try “All guides” or another topic.', 'sage') }} <button type="button" class="rv-work-empty-all">{{ __('Show all', 'sage') }}</button></p>
        <nav class="rv-pagination" aria-label="{{ __('Posts navigation', 'sage') }}">{!! \App\pagination() !!}</nav>
      </section>
    @else
      <section class="rv-shell rv-band">@include('partials.content-none')</section>
    @endif

    @include('partials.sale-closer', ['blogId' => $blogId, 'ctaHref' => $ctaHref])

    <style>
      .rv-jnl-why{padding-block:clamp(2.25rem,4.5vw,3.5rem)}
      .rv-jnl-why-grid{display:grid;grid-template-columns:minmax(0,1fr) minmax(0,1.08fr);gap:clamp(1.5rem,4vw,2.75rem);align-items:start}
      .rv-jnl-why-copy .rv-section-title{margin:.35rem 0 .8rem}
      .rv-jnl-why-copy .rv-page-intro{margin:0}
      .rv-jnl-why-actions{display:flex;flex-wrap:wrap;align-items:center;gap:.85rem 1.25rem;margin-top:1.25rem}
      .rv-jnl-why-jump{display:inline-flex;align-items:center;gap:.4rem;font-weight:700;font-size:.9rem;color:var(--color-clay);text-decoration:none}
      .rv-jnl-why-jump:hover{color:var(--color-pine)}
      .rv-jnl-why-jump svg{width:15px;height:15px;transition:transform .2s ease}
      .rv-jnl-why-jump:hover svg{transform:translateX(3px)}
      .rv-jnl-why-list{display:grid;gap:.8rem}
      .rv-jnl-why-item{position:relative;background:var(--color-surface);border:1px solid var(--color-line);border-radius:var(--radius-lg,16px);padding:1.1rem 1.3rem 1.1rem 1.5rem;overflow:hidden}
      .rv-jnl-why-item::before{content:"";position:absolute;left:0;top:0;bottom:0;width:4px;background:var(--ridgeline)}
      .rv-jnl-why-kicker{display:block;font-family:var(--font-mono);font-size:.68rem;letter-spacing:.1em;text-transform:uppercase;color:var(--color-clay)}
      .rv-jnl-why-item h3{font-family:var(--font-display);font-size:1.12rem;font-weight:700;color:var(--color-ink);margin:.2rem 0 .35rem;line-height:1.2}
      .rv-jnl-why-item p{margin:0;color:var(--color-body);font-size:.95rem;line-height:1.55}
      @media(max-width:820px){.rv-jnl-why-grid{grid-template-columns:1fr}}
      @media(max-width:480px){.rv-jnl-why-actions{flex-direction:column;align-items:stretch}.rv-jnl-why-actions .rv-btn{justify-content:center}.rv-jnl-why-jump{justify-content:center}}
      .rv-jnl-feature{display:grid;grid-template-columns:1.15fr minmax(0,1fr);gap:0;margin-top:1.75rem;background:var(--color-surface);border:1.5px solid var(--color-line);border-radius:18px;overflow:hidden;box-shadow:var(--shadow-soft)}
      .rv-jnl-feature-shot{display:block;position:relative;min-height:16rem;background:var(--color-cream);text-decoration:none}
      .rv-jnl-feature-shot img{width:100%;height:100%;object-fit:cover;position:absolute;inset:0}
      .rv-jnl-feature-shot.is-placeholder{background:var(--night)}
      .rv-jnl-feature-badge{position:absolute;top:.8rem;left:.8rem;z-index:2;background:var(--color-pine);color:#fff;font-family:var(--font-mono);font-size:.66rem;letter-spacing:.08em;text-transform:uppercase;padding:.3rem .65rem;border-radius:999px}
      .rv-jnl-feature-body{padding:clamp(1.4rem,3vw,2.1rem);display:flex;flex-direction:column}
      .rv-jnl-feature-title{font-family:var(--font-display);font-size:clamp(1.35rem,2.4vw,1.85rem);line-height:1.15;margin:.25rem 0 .6rem}
      .rv-jnl-feature-title a{color:var(--color-ink);text-decoration:none}
      .rv-jnl-feature-title a:hover{color:var(--color-pine)}
      .rv-jnl-feature-excerpt{margin:0;color:var(--color-body);line-height:1.6}
      .rv-jnl-feature-actions{display:flex;flex-wrap:wrap;align-items:center;gap:.85rem 1.2rem;margin-top:auto;padding-top:1.2rem}
      @media(max-width:820px){.rv-jnl-feature{grid-template-columns:1fr}.rv-jnl-feature-shot{min-height:12rem;aspect-ratio:16/10;position:relative}}
      .rv-work-cats{display:flex;flex-wrap:wrap;align-items:center;gap:.55rem;margin-top:1.75rem}
      .rv-work-cats-label{font-family:var(--font-mono);font-size:.72rem;letter-spacing:.08em;text-transform:uppercase;color:var(--color-muted);margin-right:.2rem}
      .rv-work-cat{background:color-mix(in srgb,var(--color-clay) 10%,var(--color-surface));border:1px solid color-mix(in srgb,var(--color-clay) 30%,var(--color-line));border-radius:999px;padding:.45rem .95rem;font-family:var(--font-display);font-weight:700;font-size:.92rem;color:var(--color-ink)}
      .rv-work-cat-n{font-family:var(--font-mono);font-size:.68rem;font-weight:600;opacity:.7;margin-left:.2rem}
      .rv-work-filters{position:sticky;top:5.5rem;z-index:6;padding:.7rem 0;background:color-mix(in srgb,var(--color-paper) 92%,transparent);backdrop-filter:blur(10px)}
      .rv-filter{cursor:pointer;line-height:1.2;-webkit-appearance:none;appearance:none;transition:background-color .18s ease,border-color .18s ease,color .18s ease,transform .15s ease}
      .rv-filter:hover{transform:translateY(-1px);border-color:var(--color-clay);color:var(--color-clay)}
      .rv-filter:focus-visible{outline:2px solid var(--color-clay);outline-offset:2px}
      .rv-filter[aria-pressed="true"]{background:var(--color-pine);border-color:var(--color-pine);color:#fff}
      .rv-filter[aria-pressed="true"]:hover{background:var(--color-pine);border-color:var(--color-pine);color:#fff;transform:none}
      .rv-jnl-grid{display:grid;grid-template-columns:repeat(2,minmax(0,1fr));gap:clamp(1.25rem,3vw,1.75rem);margin-top:1.5rem;transition:opacity .25s ease}
      .rv-jnl-grid.is-filtering{opacity:0}
      .rv-blogcard.is-hidden{display:none}
      .rv-blogcard-more{display:inline-flex;align-items:center;gap:.4rem;margin-top:auto;padding-top:.85rem;font-weight:700;font-size:.85rem;color:var(--color-clay);text-decoration:none}
      .rv-blogcard-more svg{width:15px;height:15px;transition:transform .2s ease}
      .rv-blogcard:hover .rv-blogcard-more svg{transform:translateX(3px)}
      .rv-filter-empty{margin:1.25rem 0 0}
      .rv-work-empty-all{background:none;border:0;padding:0;font:inherit;font-weight:700;color:var(--color-clay);cursor:pointer;text-decoration:underline}
      @media(max-width:820px){.rv-jnl-grid{grid-template-columns:1fr}}
      @media(max-width:700px){.rv-work-filters{top:4.75rem}}
      @media(max-width:640px){.rv-jnl-feature-actions{flex-direction:column;align-items:stretch}.rv-jnl-feature-actions .rv-btn{width:100%;justify-content:center}.rv-jnl-feature-actions .rv-jnl-why-jump{justify-content:center}}
      @media(prefers-reduced-motion:reduce){.rv-jnl-grid{transition:none}.rv-filter{transition:none}.rv-jnl-why-jump svg,.rv-blogcard-more svg{transition:none}}
    </style>

    <script>
      (function () {
        var grid = document.getElementById('rv-jnl-grid');
        var bar = document.querySelector('.rv-work-filters');
        if (!grid || !bar) return;
        var cards = Array.prototype.slice.call(grid.querySelectorAll('.rv-blogcard'));
        var empty = document.querySelector('.rv-filter-empty');
        var emptyAll = empty ? empty.querySelector('.rv-work-empty-all') : null;
        var buttons = Array.prototype.slice.call(bar.querySelectorAll('.rv-filter'));
        var reduce = window.matchMedia && window.matchMedia('(prefers-reduced-motion: reduce)').matches;
        function inCat(el, filter) {
          if (filter === 'all') return true;
          return (el.getAttribute('data-cat') || '').split(/\s+/).indexOf(filter) !== -1;
        }
        function applyFilter(filter) {
          var shown = 0;
          cards.forEach(function (card) {
            var show = inCat(card, filter);
            card.classList.toggle('is-hidden', !show);
            if (show) shown++;
          });
          if (empty) empty.hidden = shown > 0;
        }
        function setActive(active) {
          buttons.forEach(function (b) { b.setAttribute('aria-pressed', b === active ? 'true' : 'false'); });
        }
        function run(btn) {
          if (!btn || btn.getAttribute('aria-pressed') === 'true') return;
          setActive(btn);
          var filter = btn.getAttribute('data-filter');
          if (reduce) { applyFilter(filter); return; }
          grid.classList.add('is-filtering');
          window.setTimeout(function () {
            applyFilter(filter);
            requestAnimationFrame(function () { grid.classList.remove('is-filtering'); });
          }, 250);
        }
        buttons.forEach(function (btn) { btn.addEventListener('click', function () { run(btn); }); });
        if (emptyAll) emptyAll.addEventListener('click', function () { run(bar.querySelector('[data-filter="all"]')); });
      })();
    </script>

  @else

    @php($rvL = \App\entry_layout())
    <div class="rv-shell rv-layout {{ $rvL['sidebar'] !== 'none' ? 'has-sidebar rv-side-'.$rvL['sidebar'] : '' }}">
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
