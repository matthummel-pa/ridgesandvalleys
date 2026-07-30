{{-- Single project = a full case study, built as a bold marketing funnel with local SEO. --}}
@extends('layouts.app')

@section('content')
  @while(have_posts()) @php(the_post())
    @php($id = get_the_ID())
    @php($m = fn ($k) => trim((string) get_post_meta($id, $k, true)))

    @php($isConcept = $m('_rv_is_concept') === '1')
    @php($preview = $m('_rv_preview'))
    @php($eyebrow = $m('_rv_eyebrow') ?: ($m('_rv_client') ?: __('Case study', 'sage')))
    @php($summary = $m('_rv_summary'))
    @php($url = $m('_rv_url'))
    @php($services = array_filter(array_map('trim', explode(',', $m('_rv_services')))))
    @php($tech = array_filter(array_map('trim', explode(',', $m('_rv_tech')))))
    @php($deliverables = array_filter(array_map('trim', preg_split('/\r\n|\r|\n/', $m('_rv_deliverables')))))
    @php($facts = array_filter([
      ($isConcept ? __('Concept brand', 'sage') : __('Client', 'sage')) => $m('_rv_client'),
      __('Designed & built by', 'sage') => ($isConcept ? __('Matt Hummel · Ridges & Valleys Studio', 'sage') : $m('_rv_designer')),
      __('Industry', 'sage') => $m('_rv_industry'),
      __('Location', 'sage') => $m('_rv_location'),
      __('Timeline', 'sage') => $m('_rv_timeline'),
    ]))
    @php($challenge = $m('_rv_challenge'))
    @php($approach = $m('_rv_approach'))
    @php($result = $m('_rv_result'))
    @php($story = array_values(array_filter([
      [__('The challenge', 'sage'), $challenge],
      [__('The approach', 'sage'), $approach],
      [__('The result', 'sage'), $result],
    ], fn ($s) => trim($s[1]) !== '')))
    @php($metrics = array_values(array_filter([
      [$m('_rv_m1_value'), $m('_rv_m1_label')],
      [$m('_rv_m2_value'), $m('_rv_m2_label')],
      [$m('_rv_m3_value'), $m('_rv_m3_label')],
    ], fn ($x) => $x[0] !== '')))
    @php($quote = $m('_rv_quote'))
    @php($ctaText = $m('_rv_cta_text') ?: __('Start a project', 'sage'))
    @php($ctaUrl = \App\cta_href($m('_rv_cta_url') ?: get_theme_mod('rv_cta_url', '/contact/')))

    {{-- Derive a clean industry noun for local-SEO copy --}}
    @php($catParts = array_values(array_filter(array_map('trim', explode('·', $m('_rv_industry'))))))
    @php($seoType = $catParts[0] ?? ($m('_rv_industry') ?: __('small business', 'sage')))
    @php($seoTypeLower = function_exists('mb_strtolower') ? mb_strtolower($seoType) : strtolower($seoType))
    @php($towns = ['Gettysburg', 'Biglerville', 'Littlestown', 'New Oxford', 'McSherrystown', 'Bonneauville', 'Fairfield', 'Cashtown', 'East Berlin', 'Hanover'])

    <article @php(post_class('rv-single rv-cs'))>

      {{-- ===== BOLD HERO ===== --}}
      <header class="rv-cs-hero">
        <div class="rv-shell rv-cs-hero-inner">
          <span class="rv-cs-kicker">{{ $eyebrow }}</span>
          <h1 class="rv-cs-title">{!! get_the_title() !!}</h1>
          @if ($summary)
            <p class="rv-cs-lede">{{ $summary }}</p>
          @endif
          <div class="rv-cs-meta">
            @if ($m('_rv_industry'))<span>{{ $m('_rv_industry') }}</span>@endif
            <span>{{ $m('_rv_location') ?: __('Gettysburg, PA', 'sage') }}</span>
            @if ($isConcept)<span>{{ __('Self-initiated concept', 'sage') }}</span>@endif
          </div>
          <div class="rv-cs-actions">
            @if ($url)
              <a class="rv-btn rv-btn-on-dark" href="{{ esc_url($url) }}" target="_blank" rel="noopener noreferrer">{{ $isConcept ? __('Open the live demo', 'sage') : __('Visit the live site', 'sage') }} &nearr;</a>
            @endif
            <a class="rv-btn rv-btn-ghost rv-btn-ghost-dark" href="{{ $ctaUrl }}">{{ $isConcept ? __('Get a site like this', 'sage') : __('Start a project like this', 'sage') }}</a>
          </div>
        </div>
      </header>

      {{-- ===== SHOWCASE (framed, lifted screenshot overlapping the hero) ===== --}}
      @php($shot = has_post_thumbnail() ? get_the_post_thumbnail_url($id, 'rv-hero') : $preview)
      @if ($shot)
        <div class="rv-shell rv-cs-shot">
          <figure class="rv-cs-frame">
            <div class="rv-cs-frame-bar" aria-hidden="true">
              <i></i><i></i><i></i>
              <span class="rv-cs-frame-url">{{ $isConcept ? __('concept preview', 'sage') : parse_url($url ?: home_url(), PHP_URL_HOST) }}</span>
            </div>
            @if ($url)
              <a href="{{ esc_url($url) }}" target="_blank" rel="noopener noreferrer" class="rv-cs-frame-link">
                <img src="{{ esc_url($shot) }}" alt="{{ esc_attr(get_the_title()) }} — {{ $isConcept ? __('concept homepage design', 'sage') : __('finished website', 'sage') }} {{ __('by a Gettysburg web designer', 'sage') }}" loading="eager">
                <span class="rv-cs-frame-tag">{{ $isConcept ? __('Explore the live concept', 'sage') : __('Visit the live site', 'sage') }} &nearr;</span>
              </a>
            @else
              <img src="{{ esc_url($shot) }}" alt="{{ esc_attr(get_the_title()) }} — {{ __('website by a Gettysburg web designer', 'sage') }}" loading="eager">
            @endif
          </figure>
          @if ($isConcept)
            <figcaption class="rv-cs-shot-cap">{{ __('A working, interactive concept — every button, menu, and tool is clickable.', 'sage') }}</figcaption>
          @endif
        </div>
      @endif

      {{-- ===== AT A GLANCE (bold fact strip) ===== --}}
      @if (! empty($facts) || ! empty($services))
        <section class="rv-shell rv-cs-glance">
          <div class="rv-cs-facts">
            @foreach ($facts as $label => $value)
              <div class="rv-cs-fact"><span class="rv-cs-fact-k">{{ $label }}</span><span class="rv-cs-fact-v">{{ $value }}</span></div>
            @endforeach
          </div>
          @if (! empty($services))
            <div class="rv-cs-services">
              <span class="rv-cs-fact-k">{{ __('Services', 'sage') }}</span>
              <div class="rv-chips">@foreach ($services as $s)<span class="rv-mchip">{{ $s }}</span>@endforeach</div>
            </div>
          @endif
        </section>
      @endif

      {{-- ===== CONCEPT DISCLOSURE (honest) ===== --}}
      @if ($isConcept)
        <section class="rv-shell">
          <div class="rv-concept-note" role="note">
            <span class="rv-concept-note-tag">{{ __('Concept', 'sage') }}</span>
            <p>{!! sprintf(
              __('I designed and built this myself as a self-initiated concept — a fully working demo to show how I\'d approach a %s site. It\'s <strong>not a paid client project</strong>: the business name, brand, copy, and numbers are illustrative, not a real company or real results. The live demo is genuinely clickable, and I can adapt the whole thing to your business.', 'sage'),
              esc_html($seoTypeLower ?: __('local business', 'sage'))
            ) !!}</p>
          </div>
        </section>
      @endif

      {{-- ===== THE STORY (bold, numbered) ===== --}}
      @if (! empty($story))
        <section class="rv-shell rv-cs-story">
          <div class="rv-cs-story-head">
            {!! \App\eyebrow(__('How it came together', 'sage')) !!}
            <h2 class="rv-cs-h2">{{ __('The problem, the plan,', 'sage') }} <em class="rv-accent">{{ __('the payoff.', 'sage') }}</em></h2>
          </div>
          <div class="rv-cs-story-rows">
            @foreach ($story as $step)
              <div class="rv-cs-story-row">
                <div class="rv-cs-story-num">{{ sprintf('%02d', $loop->iteration) }}</div>
                <div class="rv-cs-story-main">
                  <h3 class="rv-cs-story-h">{{ $step[0] }}</h3>
                  <div class="rv-prose rv-cs-prose">{!! wpautop(esc_html($step[1])) !!}</div>
                </div>
              </div>
            @endforeach
          </div>
        </section>
      @endif

      {{-- ===== METRICS (bold dark band) ===== --}}
      @if (! empty($metrics))
        <section class="rv-cs-metrics">
          <div class="rv-shell">
            <div class="rv-cs-metrics-grid">
              @foreach ($metrics as $metric)
                <div class="rv-cs-metric">
                  <div class="rv-cs-metric-v">{{ $metric[0] }}</div>
                  @if ($metric[1])<div class="rv-cs-metric-l">{{ $metric[1] }}</div>@endif
                </div>
              @endforeach
            </div>
            @if ($isConcept)
              <p class="rv-cs-metrics-note">{{ __('Design goals this concept is built to hit — illustrative, not measured client results.', 'sage') }}</p>
            @endif
          </div>
        </section>
      @endif

      {{-- ===== EDITOR WRITE-UP ===== --}}
      @if (trim(get_the_content()))
        <section class="rv-shell-full">
          <div class="rv-reading rv-prose rv-cs-body">@php(the_content())</div>
        </section>
      @endif

      {{-- ===== UNDER THE HOOD ===== --}}
      @if (! empty($deliverables) || ! empty($tech))
        <section class="rv-shell rv-cs-hood">
          <div class="rv-cs-hood-grid">
            @if (! empty($deliverables))
              <div>
                {!! \App\eyebrow(__('What was delivered', 'sage')) !!}
                <ul class="rv-check-list">@foreach ($deliverables as $d)<li>{{ $d }}</li>@endforeach</ul>
              </div>
            @endif
            @if (! empty($tech))
              <div>
                {!! \App\eyebrow(__('Built with', 'sage')) !!}
                <div class="rv-chips" style="margin-top:.75rem">@foreach ($tech as $t)<span class="rv-mchip">{{ $t }}</span>@endforeach</div>
              </div>
            @endif
          </div>
        </section>
      @endif

      {{-- ===== LOCAL SEO COPY ===== --}}
      <section class="rv-shell rv-cs-local">
        <div class="rv-cs-local-grid">
          <div class="rv-cs-local-head">
            {!! \App\eyebrow(__('Local web design', 'sage')) !!}
            <h2 class="rv-cs-local-h">{{ ucfirst($seoTypeLower) }} {{ __('web design in', 'sage') }} <em class="rv-accent">{{ __('Gettysburg & Adams County.', 'sage') }}</em></h2>
          </div>
          <div class="rv-cs-local-body">
            <p>{!! sprintf(
              __('This is the kind of site I build for %1$s owners right here in south-central Pennsylvania. Whether you\'re in Gettysburg, Biglerville, Littlestown, New Oxford, McSherrystown, Fairfield, Cashtown, or Hanover, your customers reach for their phones long before they call — and a fast, clearly-written, mobile-first website is what turns a %2$s "near me" search into a booking, an order, or a visit through your door.', 'sage'),
              esc_html($seoTypeLower ?: __('local business', 'sage')),
              esc_html($seoTypeLower ?: __('local', 'sage'))
            ) !!}</p>
            <p>{{ __('Every build ships with the local-SEO fundamentals that actually help you get found in Adams County: a Google Business Profile set up properly, location and service-area pages, LocalBusiness structured data so search engines understand exactly where and what you are, and copy written around how real people around here search. You own the domain, the hosting, and the words — and when you need a change, you get a real person a short drive away, not a ticket queue.', 'sage') }}</p>
            <div class="rv-cs-local-towns" aria-label="{{ __('Towns served', 'sage') }}">
              @foreach ($towns as $t)<span class="rv-cs-town">{{ $t }}</span>@endforeach
            </div>
            <a class="rv-cs-local-link" href="{{ $ctaUrl }}">{{ __('Get found in your town', 'sage') }} {!! \App\icon('arrow') !!}</a>
          </div>
        </div>
      </section>

      {{-- ===== TESTIMONIAL ===== --}}
      @if ($quote)
        <section class="rv-shell rv-cs-quote-wrap">
          <blockquote class="rv-cs-quote">
            <p>&ldquo;{{ $quote }}&rdquo;</p>
            @if ($m('_rv_quote_author'))
              <cite>{{ $m('_rv_quote_author') }}@if ($m('_rv_quote_role'))<span> — {{ $m('_rv_quote_role') }}</span>@endif</cite>
            @endif
          </blockquote>
        </section>
      @endif

      {{-- ===== USE THIS TEMPLATE (concepts only) ===== --}}
      @if ($isConcept)
        <section class="rv-shell rv-cs-template-wrap">
          <div class="rv-cs-template">
            <div class="rv-cs-template-copy">
              {!! \App\eyebrow(__('Like what you see?', 'sage')) !!}
              <h2 class="rv-cs-template-title">{{ __('Use this template on your site.', 'sage') }}</h2>
              <p class="rv-cs-template-sub">{{ __('This is a concept I designed and coded myself — a real, working starting point, not a template I bought. I\'ll rebuild it around your business: your name, your photos, your colors, and the exact tools you need. You get a fixed price up front and a site you can actually run yourself.', 'sage') }}</p>
              <ul class="rv-check-list rv-cs-template-list">
                <li>{{ __('Built around this layout — customized to your brand', 'sage') }}</li>
                <li>{{ __('Fixed-scope quote before any work begins', 'sage') }}</li>
                <li>{{ __('Real training and a clean handoff — no lock-in', 'sage') }}</li>
              </ul>
            </div>
            <div class="rv-cs-template-actions">
              <a class="rv-btn rv-btn-primary" href="{{ $ctaUrl }}">{{ __('Get started with this template', 'sage') }}</a>
              @if ($url)
                <a class="rv-btn rv-btn-ghost" href="{{ esc_url($url) }}" target="_blank" rel="noopener noreferrer">{{ __('Preview the live concept', 'sage') }} &nearr;</a>
              @endif
              <p class="rv-cs-template-fine">{{ __('Based in Gettysburg · Serving Adams County & South Central PA', 'sage') }}</p>
            </div>
          </div>
        </section>
      @endif

      {{-- ===== RELATED WORK ===== --}}
      @php($related = new WP_Query(['post_type' => 'project', 'posts_per_page' => 3, 'post__not_in' => [$id], 'orderby' => 'rand', 'no_found_rows' => true]))
      @if ($related->have_posts())
        <section class="rv-shell rv-cs-related">
          {!! \App\eyebrow(__('More work', 'sage')) !!}
          <h2 class="rv-cs-h2">{{ __('See another', 'sage') }} <em class="rv-accent">{{ __('build.', 'sage') }}</em></h2>
          <div class="rv-grid" style="grid-template-columns:repeat(auto-fit,minmax(300px,1fr));margin-top:1.75rem">
            @while($related->have_posts()) @php($related->the_post())
              @php($rpreview = get_post_meta(get_the_ID(), '_rv_preview', true))
              <article class="rv-card rv-work-card">
                <a class="rv-work-link" href="{{ get_permalink() }}">
                  @if (has_post_thumbnail())
                    <span class="rv-work-thumb">@php(the_post_thumbnail('rv-card', ['loading' => 'lazy']))</span>
                  @elseif ($rpreview)
                    <span class="rv-work-thumb rv-media-photo"><img src="{{ esc_url($rpreview) }}" alt="{{ esc_attr(get_the_title()) }}" loading="lazy" onerror="this.closest('.rv-work-thumb').classList.add('rv-work-thumb-placeholder')"></span>
                  @else
                    <span class="rv-work-thumb rv-work-thumb-placeholder" aria-hidden="true"></span>
                  @endif
                  <span class="rv-work-body">
                    <span class="rv-eyebrow">{{ get_post_meta(get_the_ID(), '_rv_client', true) ?: __('Case study', 'sage') }}</span>
                    <span class="rv-work-title">{!! get_the_title() !!}</span>
                  </span>
                </a>
              </article>
            @endwhile
          </div>
          @php(wp_reset_postdata())
        </section>
      @endif
    </article>

    {{-- ===== FUNNEL CTA ===== --}}
    <section class="rv-cta-band">
      <div class="rv-shell rv-cta-inner">
        <h2 class="rv-cta-title">{{ __('Want a site like this for your business?', 'sage') }}</h2>
        <p class="rv-cta-sub">{{ __('Tell me about your business and I\'ll tell you exactly what I\'d do — a fast, fixed-scope build with no jargon and no pressure. Built here in Gettysburg for Adams County and South Central PA.', 'sage') }}</p>
        <a class="rv-btn rv-btn-on-dark" href="{{ $ctaUrl }}">{{ $ctaText }}</a>
        <p class="rv-cta-fine">{{ __('Built here in Adams County · Fixed price · Real training handoff', 'sage') }}</p>
      </div>
    </section>
  @endwhile

  <style>
    /* ============ BOLD CASE-STUDY LAYOUT ============ */
    .rv-cs{--cs-dark:var(--color-pine-deep,#213D33);--cs-ink:#f5efe2}
    /* Hero sits flush under the site header (no big gap above the dark hero);
       add breathing room below the content so the funnel CTA band isn't cramped. */
    .rv-single.rv-cs{padding-top:0;padding-bottom:clamp(3.5rem,6vw,5.5rem)}
    /* Visible keyboard focus on this page's dark sections */
    .rv-cs-hero :focus-visible,.rv-cs-metrics :focus-visible,.rv-cs-template :focus-visible{outline-color:var(--color-wheat);outline-offset:3px}

    /* HERO */
    .rv-cs-hero{position:relative;background:var(--cs-dark);color:var(--cs-ink);overflow:hidden;padding:clamp(4.5rem,9vw,7.5rem) 0 clamp(6.5rem,11vw,9.5rem)}
    .rv-cs-hero::before{content:"";position:absolute;left:0;right:0;top:0;height:5px;background:var(--ridgeline)}
    .rv-cs-hero::after{content:"";position:absolute;inset:0;background:radial-gradient(120% 85% at 88% -10%,color-mix(in srgb,var(--color-clay) 26%,transparent),transparent 58%);pointer-events:none}
    .rv-cs-hero-inner{position:relative;z-index:2;max-width:62rem}
    .rv-cs-kicker{display:inline-flex;align-items:center;font-family:var(--font-mono);font-size:.72rem;letter-spacing:.16em;text-transform:uppercase;color:var(--color-wheat);border:1px solid color-mix(in srgb,var(--color-wheat) 45%,transparent);padding:.42rem .85rem;border-radius:999px}
    .rv-cs-title{font-family:var(--font-display);font-weight:800;line-height:1.02;letter-spacing:-.025em;font-size:clamp(2.6rem,7vw,5rem);margin:1.3rem 0 0;color:#fff}
    .rv-cs-lede{font-family:var(--font-serif);font-style:italic;font-size:clamp(1.25rem,2.6vw,1.95rem);line-height:1.4;color:color-mix(in srgb,var(--cs-ink) 90%,transparent);margin:1.25rem 0 0;max-width:42ch}
    .rv-cs-meta{display:flex;flex-wrap:wrap;align-items:center;gap:.35rem 0;margin-top:1.75rem;font-family:var(--font-mono);font-size:.76rem;letter-spacing:.04em;color:color-mix(in srgb,var(--cs-ink) 72%,transparent)}
    .rv-cs-meta span+span::before{content:"·";margin:0 .7rem;color:var(--color-wheat)}
    .rv-cs-actions{display:flex;flex-wrap:wrap;gap:.8rem;margin-top:2rem}
    .rv-btn-ghost-dark{background:transparent;border-color:color-mix(in srgb,#fff 55%,transparent);color:#fff}
    .rv-btn-ghost-dark:hover{background:color-mix(in srgb,#fff 12%,transparent);color:#fff;border-color:#fff}

    /* SHOWCASE */
    .rv-cs-shot{position:relative;z-index:5;margin-top:clamp(-6rem,-8vw,-4rem)}
    .rv-cs-frame{margin:0;border-radius:16px;overflow:hidden;background:#fff;box-shadow:0 44px 90px -34px rgba(0,0,0,.55),0 0 0 1px var(--color-line)}
    .rv-cs-frame-bar{display:flex;align-items:center;gap:.45rem;padding:.7rem .95rem;background:color-mix(in srgb,var(--cs-dark) 90%,#000)}
    .rv-cs-frame-bar i{width:11px;height:11px;border-radius:50%;background:#e2664a;display:block}
    .rv-cs-frame-bar i:nth-child(2){background:var(--color-wheat)}
    .rv-cs-frame-bar i:nth-child(3){background:var(--color-sage,#97A88E)}
    .rv-cs-frame-url{margin-left:.7rem;font-family:var(--font-mono);font-size:.7rem;letter-spacing:.03em;color:color-mix(in srgb,#fff 55%,transparent)}
    .rv-cs-frame-link{display:block;position:relative}
    .rv-cs-frame-link img,.rv-cs-frame>img{display:block;width:100%}
    .rv-cs-frame-link img{transition:transform .5s ease}
    .rv-cs-frame-link:hover img{transform:scale(1.01)}
    .rv-cs-frame-tag{position:absolute;left:1rem;bottom:1rem;background:color-mix(in srgb,#23201B 84%,transparent);color:var(--cs-ink);font-family:var(--font-mono);font-size:.72rem;letter-spacing:.05em;text-transform:uppercase;padding:.5rem .85rem;border-radius:999px;backdrop-filter:blur(4px)}
    .rv-cs-shot-cap{margin:.9rem 0 0;text-align:left;font-size:.88rem;color:var(--color-muted);font-style:italic}

    /* AT A GLANCE */
    .rv-cs-glance{margin-top:clamp(2.5rem,5vw,3.5rem)}
    .rv-cs-facts{display:grid;grid-template-columns:repeat(auto-fit,minmax(180px,1fr));gap:1px;background:var(--color-line);border:1px solid var(--color-line);border-radius:var(--radius-lg);overflow:hidden}
    .rv-cs-fact{background:var(--color-surface);padding:1.35rem 1.4rem;display:flex;flex-direction:column;gap:.45rem}
    .rv-cs-fact-k{font-family:var(--font-mono);font-size:.68rem;letter-spacing:.09em;text-transform:uppercase;color:var(--color-clay)}
    .rv-cs-fact-v{font-family:var(--font-display);font-weight:700;font-size:1.05rem;color:var(--color-ink);line-height:1.25}
    .rv-cs-services{margin-top:1.4rem;display:flex;flex-wrap:wrap;align-items:center;gap:.75rem 1.25rem}
    .rv-cs-services .rv-cs-fact-k{color:var(--color-faint)}

    /* STORY */
    .rv-cs-story{padding-top:var(--section-y)}
    .rv-cs-h2{font-family:var(--font-display);font-weight:800;font-size:clamp(1.9rem,4.2vw,3rem);line-height:1.05;letter-spacing:-.02em;color:var(--color-ink);margin:.4rem 0 0}
    .rv-cs-story-rows{margin-top:2.5rem;display:grid;gap:1.25rem}
    .rv-cs-story-row{display:grid;grid-template-columns:auto 1fr;gap:1.5rem 2.25rem;align-items:start;background:var(--color-surface);border:1px solid var(--color-line);border-radius:var(--radius-lg);padding:clamp(1.5rem,3vw,2.5rem);position:relative;overflow:hidden}
    .rv-cs-story-row::before{content:"";position:absolute;left:0;top:0;bottom:0;width:5px;background:var(--ridgeline)}
    .rv-cs-story-num{font-family:var(--font-serif);font-size:clamp(2.4rem,6vw,4rem);line-height:.9;color:var(--color-clay);font-style:italic}
    .rv-cs-story-h{font-family:var(--font-mono);font-size:.8rem;letter-spacing:.1em;text-transform:uppercase;color:var(--color-pine);margin:.35rem 0 .6rem}
    .rv-cs-prose{max-width:60ch;color:var(--color-body)}
    .rv-cs-prose p{font-size:1.08rem;line-height:1.65;margin:0 0 .8rem}
    @media(max-width:640px){.rv-cs-story-row{grid-template-columns:1fr;gap:.4rem}.rv-cs-story-num{font-size:2.4rem}}

    /* METRICS */
    .rv-cs-metrics{margin-top:var(--section-y);background:var(--cs-dark);color:var(--cs-ink);padding:clamp(3rem,6vw,5rem) 0;position:relative;overflow:hidden}
    .rv-cs-metrics::before{content:"";position:absolute;left:0;right:0;top:0;height:4px;background:var(--ridgeline)}
    .rv-cs-metrics-grid{display:grid;grid-template-columns:repeat(auto-fit,minmax(180px,1fr));gap:2rem;text-align:center}
    .rv-cs-metric-v{font-family:var(--font-display);font-weight:800;font-size:clamp(2.6rem,7vw,4.5rem);line-height:1;color:#fff;letter-spacing:-.02em}
    .rv-cs-metric-l{margin-top:.6rem;font-family:var(--font-mono);font-size:.78rem;letter-spacing:.06em;text-transform:uppercase;color:var(--color-wheat)}
    .rv-cs-metrics-note{margin:2rem 0 0;text-align:left;font-size:.85rem;font-style:italic;color:color-mix(in srgb,var(--cs-ink) 65%,transparent)}

    /* EDITOR BODY */
    .rv-cs-body{padding-top:var(--section-y)}

    /* UNDER THE HOOD */
    .rv-cs-hood{padding-top:var(--section-y)}
    .rv-cs-hood-grid{display:grid;grid-template-columns:1.2fr 1fr;gap:2.5rem;background:var(--color-surface);border:1px solid var(--color-line);border-radius:var(--radius-lg);padding:clamp(1.75rem,3vw,2.5rem)}
    @media(max-width:640px){.rv-cs-hood-grid{grid-template-columns:1fr}}
    .rv-check-list{list-style:none;margin:.75rem 0 0;padding:0}
    .rv-check-list li{position:relative;padding-left:1.6rem;margin:.5rem 0}
    .rv-check-list li::before{content:"";position:absolute;left:0;top:.55em;width:.6rem;height:.6rem;border-radius:50%;background:var(--ridgeline)}

    /* LOCAL SEO */
    .rv-cs-local{padding-top:var(--section-y)}
    .rv-cs-local-grid{display:grid;grid-template-columns:.85fr 1.15fr;gap:2.5rem 3rem;background:linear-gradient(135deg,color-mix(in srgb,var(--color-wheat) 14%,var(--color-surface)),var(--color-surface));border:1px solid color-mix(in srgb,var(--color-wheat) 38%,var(--color-line));border-radius:var(--radius-lg);padding:clamp(1.9rem,4vw,3.25rem)}
    @media(max-width:760px){.rv-cs-local-grid{grid-template-columns:1fr;gap:1.25rem}}
    .rv-cs-local-h{font-family:var(--font-display);font-weight:800;font-size:clamp(1.7rem,3.6vw,2.6rem);line-height:1.06;letter-spacing:-.02em;color:var(--color-ink);margin:.45rem 0 0}
    .rv-cs-local-body p{font-size:1.05rem;line-height:1.7;color:var(--color-body);margin:0 0 1rem;max-width:60ch}
    .rv-cs-local-towns{display:flex;flex-wrap:wrap;gap:.5rem;margin:1.25rem 0 0}
    .rv-cs-town{background:var(--color-paper,#fff);border:1px solid var(--color-line);border-radius:999px;padding:.4rem .85rem;font-size:.82rem;font-weight:600;color:var(--color-ink)}
    .rv-cs-local-link{display:inline-flex;align-items:center;gap:.45rem;margin-top:1.5rem;font-weight:700;color:var(--color-clay);text-decoration:none}
    .rv-cs-local-link svg{width:15px;height:15px;transition:transform .2s ease}
    .rv-cs-local-link:hover svg{transform:translateX(3px)}

    /* TESTIMONIAL */
    .rv-cs-quote-wrap{padding-top:var(--section-y)}
    .rv-cs-quote{margin:0 auto;max-width:var(--reading);border-left:4px solid var(--color-clay);padding:.5rem 0 .5rem 1.75rem}
    .rv-cs-quote p{font-family:var(--font-serif);font-size:clamp(1.5rem,3.2vw,2.15rem);line-height:1.34;color:var(--color-ink);font-style:italic;margin:0}
    .rv-cs-quote cite{display:block;margin-top:1rem;font-style:normal;font-weight:600;color:var(--color-pine)}
    .rv-cs-quote cite span{color:var(--color-muted);font-weight:400}

    /* USE THIS TEMPLATE */
    .rv-cs-template-wrap{padding-top:var(--section-y)}
    .rv-cs-template{display:grid;grid-template-columns:1.4fr 1fr;gap:2.5rem;align-items:center;background:var(--cs-dark);color:var(--cs-ink);border-radius:var(--radius-lg);padding:clamp(2rem,4vw,3rem);position:relative;overflow:hidden}
    .rv-cs-template::before{content:"";position:absolute;left:0;top:0;bottom:0;width:5px;background:var(--ridgeline)}
    .rv-cs-template .rv-eyebrow{color:var(--color-wheat)}
    .rv-cs-template-title{font-family:var(--font-display);font-size:clamp(1.7rem,3.4vw,2.5rem);font-weight:800;color:#fff;line-height:1.08;margin:.5rem 0 .75rem}
    .rv-cs-template-sub{color:color-mix(in srgb,var(--cs-ink) 88%,transparent);max-width:54ch;margin:0;line-height:1.6}
    .rv-cs-template-list{margin-top:1.1rem}
    .rv-cs-template-list li{color:color-mix(in srgb,var(--cs-ink) 90%,transparent)}
    .rv-cs-template-actions{display:flex;flex-direction:column;align-items:flex-start;gap:.75rem}
    .rv-cs-template-fine{margin-top:.35rem;font-family:var(--font-mono);font-size:.72rem;letter-spacing:.04em;color:color-mix(in srgb,var(--cs-ink) 65%,transparent)}
    @media(max-width:720px){.rv-cs-template{grid-template-columns:1fr;padding:1.85rem}}

    /* RELATED + shared */
    .rv-cs-related{padding-top:var(--section-y)}
    .rv-cta-fine{margin-top:1rem;font-family:var(--font-mono);font-size:.75rem;letter-spacing:.04em;opacity:.7}
    .rv-concept-note{margin-top:2rem;display:flex;gap:1rem;align-items:flex-start;background:color-mix(in srgb,var(--color-wheat) 16%,var(--color-surface));border:1px solid color-mix(in srgb,var(--color-wheat) 45%,var(--color-line));border-radius:var(--radius-card);padding:1.15rem 1.35rem}
    .rv-concept-note-tag{flex:none;background:var(--color-pine);color:var(--cs-ink);font-family:var(--font-mono);font-size:.66rem;letter-spacing:.08em;text-transform:uppercase;padding:.35rem .6rem;border-radius:999px;margin-top:.1rem}
    .rv-concept-note p{margin:0;color:var(--color-body);font-size:.95rem;line-height:1.6}

    /* Dark mode: flip the pine/clay accent labels (which don't change hue) to
       wheat/sage so they stay legible on the dark surfaces. */
    html[data-theme="dark"] .rv-cs-story-h{color:var(--color-wheat)}
    html[data-theme="dark"] .rv-cs-fact-k{color:var(--color-wheat)}
    html[data-theme="dark"] .rv-cs-local-link{color:var(--color-sage)}
    html[data-theme="dark"] .rv-cs-story-num{color:var(--color-wheat)}
    html[data-theme="dark"] .rv-cs-quote cite{color:var(--color-wheat)}
    html[data-theme="dark"] .rv-btn-ghost-dark{background:transparent;color:#fff;border-color:color-mix(in srgb,#fff 55%,transparent)}
    html[data-theme="dark"] .rv-btn-ghost-dark:hover{background:color-mix(in srgb,#fff 12%,transparent);color:#fff;border-color:#fff}
  </style>
@endsection
