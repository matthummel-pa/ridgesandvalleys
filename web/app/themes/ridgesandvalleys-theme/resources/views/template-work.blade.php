{{--
  Template Name: Work
--}}
@extends('layouts.app')

@section('content')
  @php($ctaHref = \App\cta_href(get_theme_mod('rv_cta_url', '/contact/')))
  @php($heroCtaHref = \App\cta_href(\App\field('work_cta_url', get_theme_mod('rv_cta_url', '/contact/'))))

  {{-- HERO — same pattern as Home / About: keyword H1, two CTAs, note, proof ribbon. --}}
  <section class="rv-hero" aria-labelledby="rv-work-hero-title">
    <span class="rv-stripe" aria-hidden="true"></span>
    @include('partials.hero-bg', ['fallback' => \App\stock_image('hero-work')])
    <div class="rv-shell rv-hero-inner">
      {!! \App\eyebrow(\App\field('work_kicker', __('Gettysburg web design work', 'sage'))) !!}
      <h1 id="rv-work-hero-title" class="rv-hero-title">{{ \App\field('work_h1', __('Gettysburg web design you can', 'sage')) }} <em class="rv-accent">{{ \App\field('work_h1_accent', __('actually click.', 'sage')) }}</em></h1>
      <p class="rv-hero-sub">{{ \App\field('work_lede', __('Live concept sites for restaurants, inns, shops, and tours around Gettysburg and Adams County. Filter by industry, click the working demo, then get a quote.', 'sage')) }}</p>
      <div class="rv-hero-actions">
        <a class="rv-btn rv-btn-primary" href="{{ $heroCtaHref }}">{{ \App\field('work_cta', __('Get a quote', 'sage')) }}</a>
        <a class="rv-btn rv-btn-ghost" href="#case-studies">{{ \App\field('work_cta2', __('Browse the work', 'sage')) }} &darr;</a>
      </div>
      <p class="rv-hero-note">{{ \App\field('work_note', __('Honest concepts · Live demos · No fake clients', 'sage')) }}</p>
    </div>
    @php($workStats = \App\work_stats())
    @if (! empty($workStats))
      <div class="rv-hero-proof">
        <div class="rv-shell">
          <ul class="rv-hero-stats" aria-label="{{ __('At a glance', 'sage') }}">
            @foreach ($workStats as $ws)
              <li>
                <span class="rv-hero-stat-v">{{ $ws['v'] ?? '' }}</span>
                @if (($ws['l'] ?? '') !== '')<span class="rv-hero-stat-l">{{ $ws['l'] }}</span>@endif
              </li>
            @endforeach
          </ul>
        </div>
      </div>
    @endif
  </section>

  {{-- How to use this page — split: honesty on the left, funnel steps on the right. --}}
  <section class="rv-band rv-band-alt rv-work-why" aria-labelledby="rv-work-why-title">
    <div class="rv-shell rv-work-why-grid">
      <div class="rv-work-why-copy">
        {!! \App\eyebrow(\App\field('wwhy_eyebrow', __('How to use this page', 'sage'))) !!}
        <h2 id="rv-work-why-title" class="rv-section-title">{{ \App\field('wwhy_title', __('Pick your industry.', 'sage')) }} <em class="rv-accent">{{ \App\field('wwhy_accent', __('Click the demo.', 'sage')) }}</em></h2>
        <p class="rv-page-intro">{!! \App\field('wwhy_intro', __('<strong>I built these myself — one live site per industry.</strong> Filter to a business like yours, click around the working demo, then get a quote if it feels like a fit. No fake clients, no borrowed templates, no stock mockups dressed up as case studies.', 'sage')) !!}</p>
        <div class="rv-work-why-actions">
          <a class="rv-btn rv-btn-ghost" href="#case-studies">{{ \App\field('wwhy_jump', __('Browse the concepts', 'sage')) }}</a>
          <a class="rv-work-why-jump" href="{{ $ctaHref }}">{{ \App\field('wwhy_cta', __('Get a quote', 'sage')) }} {!! \App\icon('arrow') !!}</a>
        </div>
      </div>
      <div class="rv-work-why-list">
        @foreach (\App\field_rows('wwhy_items', \App\work_why_item_defaults()) as $v)
          <article class="rv-work-why-item">
            @if (($v['kicker'] ?? '') !== '')<span class="rv-work-why-kicker">{{ $v['kicker'] }}</span>@endif
            @if (($v['title'] ?? '') !== '')<h3>{{ $v['title'] }}</h3>@endif
            @if (($v['text'] ?? '') !== '')<p>{{ $v['text'] }}</p>@endif
          </article>
        @endforeach
      </div>
    </div>
  </section>

  @php($work = new WP_Query(\App\project_list_query_args(['posts_per_page' => 24])))
  <section class="rv-shell rv-band rv-work-index" id="case-studies" style="scroll-margin-top:6rem">
    @if ($work->have_posts())
      {!! \App\work_itemlist_jsonld($work->posts) !!}
      <div class="rv-headstack">
        {!! \App\eyebrow(\App\field('wcs_eyebrow', __('The work', 'sage'))) !!}
        <h2 class="rv-section-title">{{ \App\field('wcs_title', __('Concepts for', 'sage')) }} <em class="rv-accent">{{ \App\field('wcs_accent', __('Adams County businesses.', 'sage')) }}</em></h2>
        <p class="rv-page-intro">{{ \App\field('wcs_intro', __('Filter by industry, open a live demo, then read the problem → approach → result. Anything marked “Concept” is my own self-initiated demo, not a client project — so you can click the real site, not a screenshot.', 'sage')) }}</p>
      </div>
      @php($rvOrdered = \App\work_filter_categories($work->posts))
      @php($rvTotal = count($work->posts))

      @if (count($rvOrdered) > 1)
        <div class="rv-work-cats rv-work-filters" role="group" aria-label="{{ __('Filter work by industry', 'sage') }}">
          <span class="rv-work-cats-label">{{ \App\field('wcs_cats_label', __('Show me', 'sage')) }}</span>
          <button type="button" class="rv-work-cat rv-filter" data-filter="all" aria-pressed="true">{{ __('Show all', 'sage') }} <span class="rv-work-cat-n">{{ $rvTotal }}</span></button>
          @foreach ($rvOrdered as $rvSlug => $rvRow)
            <button type="button" class="rv-work-cat rv-filter" data-filter="{{ esc_attr($rvSlug) }}" aria-pressed="false">{{ $rvRow['label'] }} <span class="rv-work-cat-n">{{ $rvRow['count'] }}</span></button>
          @endforeach
        </div>
      @endif
      <p class="rv-work-count" id="rv-work-count" aria-live="polite">{{ sprintf(_n('Showing %d concept', 'Showing %d concepts', $rvTotal, 'sage'), $rvTotal) }}</p>
      <p class="rv-work-honest">{{ \App\field('wcs_honest', __('Concept means I built it to show my approach — not a paid client. That’s the point: you can click the working site.', 'sage')) }}</p>
      <div class="rv-grid rv-work-grid" id="rv-work-grid">
        @php($rvI = 0)
        @while($work->have_posts()) @php($work->the_post())
          @php($pid = (int) get_the_ID())
          @php($p = \App\work_card_data($pid))
          @php($psummary = $p['summary'] !== '' ? $p['summary'] : get_the_excerpt())
          <article data-cat="{{ esc_attr($p['cat']['slug']) }}" @php(post_class('rv-card rv-work-card' . ($rvI === 0 ? ' rv-work-card--lead' : '')))>
            <a class="rv-work-shot" href="{{ get_permalink() }}">
              @if (has_post_thumbnail())
                <span class="rv-work-thumb">@php(the_post_thumbnail('rv-card', ['loading' => 'lazy']))</span>
              @elseif ($p['preview'])
                <span class="rv-work-thumb rv-media-photo"><img src="{{ esc_url($p['preview']) }}" alt="{{ esc_attr(get_the_title()) }}" loading="lazy" onerror="this.closest('.rv-work-thumb').classList.add('rv-work-thumb-placeholder')"></span>
              @else
                <span class="rv-work-thumb rv-work-thumb-placeholder" aria-hidden="true"></span>
              @endif
              <span class="rv-work-badges">
                @if ($p['concept'])<span class="rv-work-badge">{{ __('Concept', 'sage') }}</span>@endif
                @if ($p['url'] !== '')<span class="rv-work-badge rv-work-badge-live">{{ __('Live demo', 'sage') }}</span>@endif
              </span>
            </a>
            <div class="rv-work-body">
              <span class="rv-work-meta">
                <span class="rv-work-industry">{{ $p['industry'] }}</span>
                @if ($p['location'] !== '')<span class="rv-work-loc">{{ $p['location'] }}</span>@endif
              </span>
              <span class="rv-eyebrow">{{ $p['eyebrow'] }}</span>
              <a class="rv-work-title" href="{{ get_permalink() }}">{!! get_the_title() !!}</a>
              @if ($psummary)<p class="rv-work-excerpt">{{ $psummary }}</p>@endif
              @if ($p['metric'] !== '')<p class="rv-work-metric">{{ $p['metric'] }}</p>@endif
              <div class="rv-work-actions">
                <a class="rv-work-more" href="{{ get_permalink() }}">{{ __('Read the story', 'sage') }} {!! \App\icon('arrow') !!}</a>
                @if ($p['url'] !== '')
                  <a class="rv-work-live" href="{{ esc_url($p['url']) }}" target="_blank" rel="noopener noreferrer">{{ $p['concept'] ? __('Open live demo', 'sage') : __('Visit live site', 'sage') }} &nearr;</a>
                @endif
              </div>
            </div>
          </article>
          @php($rvI++)
        @endwhile
      </div>
      <p class="rv-work-empty" id="rv-work-empty" hidden>{{ __('Nothing in this category yet.', 'sage') }} <button type="button" class="rv-work-empty-all">{{ __('Show all', 'sage') }}</button> {{ __('or', 'sage') }} <a href="{{ $ctaHref }}">{{ __('tell me about your business', 'sage') }}</a>.</p>
      @php(wp_reset_postdata())
    @else
      {{-- Static case cards (mockup content) until Projects are added in the dashboard --}}
      <div class="rv-grid rv-work-grid">
        @php($cases = [
          [__('Concept · Professional services', 'sage'), __('A local law office (concept)', 'sage'), __('A self-initiated concept showing a clearer path from visitor to consultation.', 'sage'), 'work-1', __('Wentz farm buildings near Gettysburg', 'sage')],
          [__('Product · Process', 'sage'), __('Groundwork to Launch', 'sage'), __('The delivery engine behind the studio — a productized website workflow I built.', 'sage'), 'work-2', __('Workspace desk with a laptop', 'sage')],
          [__('Concept · Retail', 'sage'), __('A Gettysburg gift shop (concept)', 'sage'), __('A clearly-labeled concept I designed to show my approach for local retail.', 'sage'), 'work-3', __('Historic brick storefront on a downtown Gettysburg street', 'sage')],
          [__('Concept · Food & drink', 'sage'), __('A local restaurant (concept)', 'sage'), __('A concept refresh — menus, hours, and directions made mobile-first and easy to keep current.', 'sage'), 'work-4', __('Rustic table setting', 'sage')],
        ])
        @foreach ($cases as $c)
          <article class="rv-card rv-work-card">
            <a class="rv-work-shot" href="{{ $ctaHref }}">
              <span class="rv-work-thumb rv-media-photo">
                <img src="{{ \App\stock_image($c[3]) }}" alt="{{ $c[4] }}" loading="lazy" onerror="this.style.display='none'">
              </span>
            </a>
            <div class="rv-work-body">
              <span class="rv-eyebrow">{{ $c[0] }}</span>
              <a class="rv-work-title" href="{{ $ctaHref }}">{{ $c[1] }}</a>
              <p class="rv-work-excerpt">{{ $c[2] }}</p>
            </div>
          </article>
        @endforeach
      </div>
    @endif
    <p class="rv-tool-hint rv-work-hint">{{ \App\field('wcs_hint', __('Each write-up follows Problem → Approach → Result. On concepts those goals are illustrative; on client projects they become measured results.', 'sage')) }}</p>
    <div class="rv-work-midcta">
      <div class="rv-work-midcta-copy">
        {!! \App\eyebrow(\App\field('wcs_cta_kicker', __('Your turn', 'sage'))) !!}
        <h3 class="rv-work-midcta-h">{{ \App\field('wcs_cta_h', __('See something close to your business?', 'sage')) }}</h3>
        <p>{{ \App\field('wcs_cta_p', __('Tell me what you do in Adams County. I’ll point you to the closest concept and a fixed price — usually a reply within a business day.', 'sage')) }}</p>
      </div>
      <div class="rv-work-midcta-actions">
        <a class="rv-btn rv-btn-primary" href="{{ $ctaHref }}">{{ \App\field('wcs_cta_btn', __('Get a quote', 'sage')) }}</a>
        <span class="rv-work-midcta-fine">{{ __('No jargon, no pressure.', 'sage') }}</span>
      </div>
    </div>
  </section>

  {{-- DON'T SEE YOUR BUSINESS? (bigger, more info) --}}
  <section class="rv-shell rv-band rv-morebiz">
    <div class="rv-headstack">
      {!! \App\eyebrow(\App\field('morebiz_eyebrow', __('Your business next', 'sage'))) !!}
      <h2 class="rv-section-title">{{ \App\field('morebiz_title', __('Don\'t see your', 'sage')) }} <em class="rv-accent">{{ \App\field('morebiz_accent', __('kind of business?', 'sage')) }}</em></h2>
      <p class="rv-page-intro">{!! \App\field('morebiz_intro', __('<strong>The approach works for almost any local business.</strong> The concepts above are just a starting set. If people around Gettysburg search for what you do, I can build you a site that helps them find you, trust you, and reach you. A few of the others I love building for:', 'sage')) !!}</p>
    </div>

    <div class="rv-morebiz-grid">
      @foreach (\App\field_lines('morebiz_list', [
        __('Tradespeople & contractors', 'sage'), __('Salons, barbers & spas', 'sage'), __('Dentists & medical', 'sage'),
        __('Auto shops & repair', 'sage'), __('Fitness & yoga studios', 'sage'), __('Landscaping & lawn care', 'sage'),
        __('Lawyers & accountants', 'sage'), __('Cafés & bakeries', 'sage'), __('Event & wedding venues', 'sage'),
        __('Photographers & creatives', 'sage'), __('Cleaning & home services', 'sage'), __('Nonprofits & churches', 'sage'),
      ]) as $b)<span class="rv-morebiz-item">{{ $b }}</span>@endforeach
    </div>

    <div class="rv-morebiz-panel">
      <div class="rv-morebiz-panel-copy">
        {!! \App\eyebrow(\App\field('morebiz_panel_eyebrow', __('The foundation every site gets', 'sage'))) !!}
        <h3 class="rv-morebiz-panel-h">{{ \App\field('morebiz_panel_h', __('Whatever you do, we start from the same solid ground.', 'sage')) }}</h3>
        <a class="rv-btn rv-btn-primary" href="{{ $ctaHref }}">{{ \App\field('morebiz_panel_btn', __('Get a free quote', 'sage')) }}</a>
        <p class="rv-morebiz-fine">{{ \App\field('morebiz_panel_fine', __('Tell me about your business — no jargon, no pressure, and usually a reply within a business day.', 'sage')) }}</p>
      </div>
      <ul class="rv-check-list rv-morebiz-check">
        @foreach (\App\field_lines('morebiz_check', [
          __('Local SEO and a properly set-up Google Business Profile', 'sage'),
          __('A fast, mobile-first design that works on any phone', 'sage'),
          __('WCAG-minded accessibility, so everyone can use it', 'sage'),
          __('Full ownership — your domain, hosting, and content', 'sage'),
          __('A fixed price agreed up front, with no surprises', 'sage'),
          __('Real training and a plain-English handoff', 'sage'),
        ]) as $ck)<li>{{ $ck }}</li>@endforeach
      </ul>
    </div>
  </section>

  {{-- HOW IT GOES (bold dark band) --}}
  <section class="rv-band rv-band-pine rv-work-flow">
    <div class="rv-shell">
      <div class="rv-headstack">
        {!! \App\eyebrow(\App\field('flow_eyebrow', __('How it goes', 'sage'))) !!}
        <h2 class="rv-section-title">{{ \App\field('flow_title', __('First call to launch in about', 'sage')) }} <em class="rv-accent">{{ \App\field('flow_accent', __('a week.', 'sage')) }}</em></h2>
        <p class="rv-page-intro">{{ \App\field('flow_intro', __('Most small-business sites stall for months because the process is vague. Mine isn\'t. Here\'s exactly how a build goes — and where you\'re involved so it sounds like you, not a template.', 'sage')) }}</p>
      </div>

      <ol class="rv-flow">
        @foreach (\App\field_rows('flow_items', [
          ['title' => __('Talk it through', 'sage'), 'text' => __('A short, no-pressure call about your business, your customers, and what a win looks like. You\'ll leave knowing exactly what I\'d do and what it costs — fixed, in writing.', 'sage')],
          ['title' => __('I build the draft', 'sage'), 'text' => __('I move fast on structure and first-draft copy, then do the judgment work by hand — the facts, the voice, the accessibility, the details that make it yours.', 'sage')],
          ['title' => __('You review', 'sage'), 'text' => __('You see the real site early, on your phone, and tell me what to sharpen. Clear rounds of feedback, not endless ping-pong.', 'sage')],
          ['title' => __('We launch', 'sage'), 'text' => __('I handle the technical launch — domain, SSL, speed, search setup — so it goes live clean and gets found from day one.', 'sage')],
          ['title' => __('You\'re trained', 'sage'), 'text' => __('A short walkthrough video and a real handoff, so you can update hours, prices, and photos yourself. I\'m still a call away when you want a hand.', 'sage')],
        ]) as $i => $step)
          <li class="rv-flow-step">
            <span class="rv-flow-num">{{ sprintf('%02d', $i + 1) }}</span>
            <h3 class="rv-flow-title">{{ $step['title'] ?? '' }}</h3>
            <p>{{ $step['text'] ?? '' }}</p>
          </li>
        @endforeach
      </ol>

      <div class="rv-flow-outcome">
        {!! \App\eyebrow(\App\field('flow_outcome_eyebrow', __('What you walk away with', 'sage'))) !!}
        <ul class="rv-check-list rv-work-deliverables">
          @foreach (\App\field_lines('flow_deliverables', [
            __('A fast, mobile-first website that reads clearly and loads quickly', 'sage'),
            __('Google Business Profile and local SEO set up so you get found', 'sage'),
            __('WCAG-minded, accessible pages that work for everyone', 'sage'),
            __('Full ownership of your domain, hosting, and content', 'sage'),
            __('A training video and plain-English handoff — no lock-in', 'sage'),
          ]) as $dl)<li>{{ $dl }}</li>@endforeach
        </ul>
        <p class="rv-work-reassure">{{ \App\field('flow_reassure', __('Fixed price agreed up front · Built here in Adams County · A real person answers when you call.', 'sage')) }}</p>
      </div>
    </div>
  </section>

  {{-- THE DESIGN PROCESS (the craft, not just the logistics) --}}
  <section class="rv-shell rv-band">
    <div class="rv-headstack">
      {!! \App\eyebrow(\App\field('craft_eyebrow', __('Under the hood', 'sage'))) !!}
      <h2 class="rv-section-title">{{ \App\field('craft_title', __('What actually goes into', 'sage')) }} <em class="rv-accent">{{ \App\field('craft_accent', __('the design.', 'sage')) }}</em></h2>
      <p class="rv-page-intro">{{ \App\field('craft_intro', __('A good small-business site isn\'t a template with your logo dropped in. Every project moves through the same design craft — so the result fits your business, your customers, and the way people actually find you.', 'sage')) }}</p>
    </div>

    <div class="rv-craft-grid">
      @foreach (\App\field_rows('craft_items', [
        ['title' => __('Listen & research', 'sage'), 'text' => __('I start with your customers, not your competitors\' websites — who they are, what they\'re trying to do, and the words they actually use. That research shapes the structure before a single pixel is placed.', 'sage')],
        ['title' => __('Structure & wireframe', 'sage'), 'text' => __('I map the path from “never heard of you” to “ready to call” and lay out each page around that one job. Fewer dead ends, clearer next steps, nothing decorative getting in the way.', 'sage')],
        ['title' => __('Voice & words', 'sage'), 'text' => __('I draft copy that sounds like you — plain, warm, and specific — then refine it by hand. No jargon, no filler, no “we are a premier provider of solutions.”', 'sage')],
        ['title' => __('Look & feel', 'sage'), 'text' => __('Type, color, spacing, and real photography chosen to feel like your business and your place — not a stock kit. Design that earns trust in the first three seconds.', 'sage')],
        ['title' => __('Build for speed & access', 'sage'), 'text' => __('Hand-built, mobile-first pages that load fast on a phone on Main Street and meet accessibility standards, so every visitor — and Google — has a good experience.', 'sage')],
        ['title' => __('Test, launch & hand off', 'sage'), 'text' => __('Cross-device testing, a clean technical launch, and a real training handoff so you can run it yourself. The site is yours, top to bottom.', 'sage')],
      ]) as $i => $c)
        <div class="rv-craft-card">
          <span class="rv-craft-num">{{ sprintf('%02d', $i + 1) }}</span>
          <h3 class="rv-craft-title">{{ $c['title'] ?? '' }}</h3>
          <p>{{ $c['text'] ?? '' }}</p>
        </div>
      @endforeach
    </div>

    <div style="margin-top:2.5rem">
      <p class="rv-page-intro">{{ \App\field('craft_close', __('Working together is refreshingly low-drama: one point of contact (me), fixed rounds of feedback instead of endless revisions, and plain-English updates so you always know where things stand. You\'re never left guessing, and you\'re never locked in.', 'sage')) }}</p>
    </div>
  </section>

  {{-- AREAS SERVED (bold, vivid, airy) --}}
  <section class="rv-band rv-areas2">
    <div class="rv-shell">
      <div class="rv-areas2-head">
        {!! \App\eyebrow(\App\field('wareas_eyebrow', __('Serving South Central PA', 'sage'))) !!}
        <h2 class="rv-areas2-h">{{ \App\field('wareas_title', __('A web designer', 'sage')) }}<br><span class="rv-areas2-accent">{{ \App\field('wareas_accent', __('based in Gettysburg.', 'sage')) }}</span></h2>
        <p class="rv-areas2-lede">{{ \App\field('wareas_lede', __('A local, independent web designer working with small businesses across Gettysburg and Adams County — restaurants, inns, shops, tour companies, tradespeople, realtors, and nonprofits. Being nearby means real meetings, real accountability, and a site built by someone who knows the towns your customers come from.', 'sage')) }}</p>
      </div>

      <div class="rv-areas2-towns" aria-label="{{ __('Towns served', 'sage') }}">
        @foreach (\App\field_lines('wareas_towns', [
          __('Gettysburg', 'sage'), __('Biglerville', 'sage'), __('Littlestown', 'sage'), __('New Oxford', 'sage'),
          __('McSherrystown', 'sage'), __('Bonneauville', 'sage'), __('East Berlin', 'sage'), __('Abbottstown', 'sage'),
          __('Fairfield', 'sage'), __('Cashtown', 'sage'), __('Arendtsville', 'sage'), __('Aspers', 'sage'),
          __('Bendersville', 'sage'), __('Orrtanna', 'sage'), __('Hanover', 'sage'), __('York Springs', 'sage'),
        ]) as $area)<span class="rv-areas2-town">{{ $area }}</span>@endforeach
      </div>

      <div class="rv-areas2-foot">
        <p class="rv-areas2-note">{{ \App\field('wareas_note', __('Plus the surrounding townships — Cumberland, Straban, Mount Joy, Franklin, Menallen, and Hamiltonban — and small businesses throughout Adams, York, and Franklin counties. Not in the neighborhood? Most of my work happens over a call and a shared screen, so distance is rarely a dealbreaker.', 'sage')) }}</p>
        <p class="rv-areas2-tagline">{!! implode(' <span>·</span> ', array_map('esc_html', \App\field_lines('wareas_tagline', [__('Local', 'sage'), __('Independent', 'sage'), __('Fixed price', 'sage'), __('Built for how South Central PA searches', 'sage')]))) !!}</p>
      </div>
    </div>
  </section>

  <style>
    .rv-steps{list-style:none;counter-reset:none;margin:2.5rem 0 0;padding:0;display:grid;grid-template-columns:repeat(auto-fit,minmax(260px,1fr));gap:1.25rem}
    .rv-step{background:var(--color-surface);border:1px solid var(--color-line);border-radius:var(--radius-card);padding:1.4rem 1.5rem}
    .rv-step-title{font-family:var(--font-mono);font-size:.9rem;letter-spacing:.02em;color:var(--color-pine);margin-bottom:.5rem}
    .rv-work-deliverables{margin-top:.9rem}
    .rv-check-list{list-style:none;margin:0;padding:0}
    .rv-check-list li{position:relative;padding-left:1.6rem;margin:.55rem 0}
    .rv-check-list li::before{content:"";position:absolute;left:0;top:.55em;width:.6rem;height:.6rem;border-radius:50%;background:var(--ridgeline)}
    .rv-work-reassure{margin-top:1.5rem;font-family:var(--font-mono);font-size:.8rem;letter-spacing:.03em;color:var(--color-muted)}
    .rv-work-why-actions{display:flex;flex-wrap:wrap;align-items:center;gap:.85rem 1.25rem;margin-top:1.25rem}
    .rv-work-why-jump{display:inline-flex;align-items:center;gap:.4rem;font-weight:700;font-size:.9rem;color:var(--color-clay);text-decoration:none}
    .rv-work-why-jump:hover{color:var(--color-pine)}
    .rv-work-why-jump svg{width:15px;height:15px;transition:transform .2s ease}
    .rv-work-why-jump:hover svg{transform:translateX(3px)}
    .rv-work-why{padding-block:clamp(2.25rem,4.5vw,3.5rem)}
    .rv-work-why-grid{display:grid;grid-template-columns:minmax(0,1fr) minmax(0,1.08fr);gap:clamp(1.5rem,4vw,2.75rem);align-items:start}
    .rv-work-why-copy .rv-section-title{margin:.35rem 0 .8rem}
    .rv-work-why-copy .rv-page-intro{margin:0}
    .rv-work-why-list{display:grid;gap:.8rem}
    .rv-work-why-item{position:relative;background:var(--color-surface);border:1px solid var(--color-line);border-radius:var(--radius-lg,16px);padding:1.1rem 1.3rem 1.1rem 1.5rem;overflow:hidden}
    .rv-work-why-item::before{content:"";position:absolute;left:0;top:0;bottom:0;width:4px;background:var(--ridgeline)}
    .rv-work-why-kicker{display:block;font-family:var(--font-mono);font-size:.68rem;letter-spacing:.1em;text-transform:uppercase;color:var(--color-clay)}
    .rv-work-why-item h3{font-family:var(--font-display);font-size:1.12rem;font-weight:700;color:var(--color-ink);margin:.2rem 0 .35rem;line-height:1.2}
    .rv-work-why-item p{margin:0;color:var(--color-body);font-size:.95rem;line-height:1.55}
    @media(max-width:820px){.rv-work-why-grid{grid-template-columns:1fr}}
    @media(prefers-reduced-motion:reduce){.rv-work-why-jump svg,.rv-work-more svg{transition:none}}
    /* Project grid */
    .rv-work-index .rv-headstack{margin-bottom:0}
    .rv-work-cats{display:flex;flex-wrap:wrap;align-items:center;gap:.55rem;margin-top:1.5rem}
    .rv-work-cats-label{font-family:var(--font-mono);font-size:.72rem;letter-spacing:.08em;text-transform:uppercase;color:var(--color-muted);margin-right:.2rem}
    .rv-work-cat{background:color-mix(in srgb,var(--color-clay) 10%,var(--color-surface));border:1px solid color-mix(in srgb,var(--color-clay) 30%,var(--color-line));border-radius:999px;padding:.45rem .95rem;font-family:var(--font-display);font-weight:700;font-size:.92rem;color:var(--color-ink)}
    .rv-work-cat-n{font-family:var(--font-mono);font-size:.68rem;font-weight:600;letter-spacing:.02em;opacity:.7;margin-left:.2rem}
    .rv-filter[aria-pressed="true"] .rv-work-cat-n{opacity:.85}
    .rv-work-filters{position:static!important;top:auto!important;bottom:auto!important;z-index:auto;margin-top:1.5rem;padding:.7rem 0;background:transparent;-webkit-backdrop-filter:none;backdrop-filter:none}
    .rv-filter{cursor:pointer;line-height:1.2;-webkit-appearance:none;appearance:none;transition:background-color .18s ease,border-color .18s ease,color .18s ease,transform .15s ease}
    .rv-filter:hover{transform:translateY(-1px);border-color:var(--color-clay);color:var(--color-clay)}
    .rv-filter:focus-visible{outline:2px solid var(--color-clay);outline-offset:2px}
    .rv-filter[aria-pressed="true"]{background:var(--color-pine);border-color:var(--color-pine);color:#fff}
    .rv-filter[aria-pressed="true"]:hover{background:var(--color-pine);border-color:var(--color-pine);color:#fff;transform:none}
    .rv-work-count{margin:.9rem 0 0;font-family:var(--font-mono);font-size:.72rem;letter-spacing:.04em;text-transform:uppercase;color:var(--color-muted)}
    .rv-work-honest{margin:.45rem 0 0;max-width:58ch;font-size:.92rem;line-height:1.55;color:var(--color-body)}
    .rv-work-grid{display:grid;grid-template-columns:repeat(2,minmax(0,1fr));gap:clamp(1.25rem,3vw,1.75rem);margin-top:1.5rem;transition:opacity .25s ease}
    .rv-work-grid.is-filtering{opacity:0}
    .rv-work-card.is-hidden{display:none}
    .rv-work-card{display:flex;flex-direction:column}
    .rv-work-shot{display:block;position:relative;text-decoration:none;color:inherit}
    .rv-work-shot::after{content:"";position:absolute;inset:auto 0 0;height:42%;background:linear-gradient(transparent,rgba(12,15,11,.4));pointer-events:none}
    .rv-work-badges{position:absolute;top:.7rem;left:.7rem;z-index:2;display:flex;flex-wrap:wrap;gap:.35rem}
    .rv-work-badge{position:static;background:color-mix(in srgb,var(--color-pine) 90%,transparent);color:var(--color-paper);font-family:var(--font-mono);font-size:.66rem;letter-spacing:.08em;text-transform:uppercase;padding:.3rem .6rem;border-radius:999px}
    .rv-work-badge-live{background:color-mix(in srgb,var(--color-clay) 92%,transparent)}
    .rv-work-body{display:flex;flex-direction:column;flex:1 1 auto;padding:1.2rem 1.35rem 1.4rem}
    .rv-work-meta{display:flex;flex-wrap:wrap;gap:.35rem .7rem;margin-bottom:.35rem;font-family:var(--font-mono);font-size:.68rem;letter-spacing:.06em;text-transform:uppercase;color:var(--color-clay)}
    .rv-work-title{display:block;text-decoration:none;color:var(--color-ink)}
    .rv-work-title:hover{color:var(--color-pine)}
    .rv-work-excerpt{margin:.45rem 0 0;color:var(--color-muted);font-size:.95rem;line-height:1.55}
    .rv-work-metric{margin:.55rem 0 0;font-family:var(--font-mono);font-size:.78rem;letter-spacing:.03em;color:var(--color-pine);font-weight:600}
    .rv-work-actions{display:flex;flex-wrap:wrap;align-items:center;gap:.35rem 1.1rem;margin-top:auto;padding-top:.95rem}
    .rv-work-more{display:inline-flex;align-items:center;gap:.4rem;font-weight:700;font-size:.85rem;color:var(--color-clay);text-decoration:none}
    .rv-work-more svg{width:15px;height:15px;transition:transform .2s ease}
    .rv-work-card:hover .rv-work-more svg{transform:translateX(3px)}
    .rv-work-live{font-weight:700;font-size:.85rem;color:var(--color-pine);text-decoration:none}
    .rv-work-live:hover{color:var(--color-clay)}
    .rv-work-card--lead{grid-column:1/-1;display:grid;grid-template-columns:1.15fr minmax(0,1fr);align-items:stretch}
    .rv-work-card--lead .rv-work-shot{height:100%}
    .rv-work-card--lead .rv-work-thumb{height:100%;min-height:16rem;aspect-ratio:16/10}
    .rv-work-card--lead .rv-work-body{padding:clamp(1.4rem,3vw,2.1rem)}
    .rv-work-card--lead .rv-work-title{font-size:clamp(1.35rem,2.4vw,1.75rem)}
    .rv-work-empty{margin:1.25rem 0 0;color:var(--color-body)}
    .rv-work-empty[hidden]{display:none}
    .rv-work-empty-all{background:none;border:0;padding:0;font:inherit;font-weight:700;color:var(--color-clay);cursor:pointer;text-decoration:underline}
    .rv-work-hint{margin-top:1.5rem}
    .rv-work-midcta{margin-top:clamp(1.75rem,3.5vw,2.5rem);display:grid;grid-template-columns:1.5fr auto;gap:2rem;align-items:center;background:var(--color-surface);border:1px solid var(--color-line);border-radius:var(--radius-lg,18px);padding:clamp(1.75rem,3.5vw,2.75rem);position:relative;overflow:hidden}
    .rv-work-midcta::before{content:"";position:absolute;left:0;top:0;bottom:0;width:6px;background:var(--ridgeline)}
    .rv-work-midcta-h{font-family:var(--font-display);font-weight:800;font-size:clamp(1.6rem,3.4vw,2.4rem);line-height:1.08;letter-spacing:-.02em;color:var(--color-ink);margin:.4rem 0 .6rem}
    .rv-work-midcta-copy p{margin:0;color:var(--color-body);max-width:58ch;line-height:1.6}
    .rv-work-midcta-actions{display:flex;flex-direction:column;align-items:flex-start;gap:.6rem}
    .rv-work-midcta-fine{font-family:var(--font-mono);font-size:.72rem;letter-spacing:.03em;color:var(--color-muted)}
    @media(max-width:820px){.rv-work-grid{grid-template-columns:1fr}.rv-work-card--lead{grid-template-columns:1fr}.rv-work-card--lead .rv-work-thumb{min-height:12rem;aspect-ratio:3/2}}
    @media(max-width:720px){.rv-work-midcta{grid-template-columns:1fr}}
    @media(prefers-reduced-motion:reduce){.rv-work-grid{transition:none}.rv-filter{transition:none}}
    .rv-craft-grid{display:grid;grid-template-columns:repeat(auto-fit,minmax(260px,1fr));gap:1.25rem;margin-top:2.5rem}
    .rv-craft-card{background:var(--color-surface);border:1px solid var(--color-line);border-radius:var(--radius-card);padding:1.5rem 1.6rem;position:relative}
    .rv-craft-num{font-family:var(--font-mono);font-size:.85rem;font-weight:700;color:var(--color-clay);letter-spacing:.05em}
    .rv-craft-title{margin:.4rem 0 .5rem;font-family:var(--font-display);font-size:1.2rem;font-weight:700;color:var(--color-ink)}
    .rv-craft-card p{margin:0;color:var(--color-body)}
    .rv-area-tags{display:flex;flex-wrap:wrap;gap:.6rem;margin-top:2.25rem}
    .rv-area-tag{background:var(--color-surface);border:1px solid var(--color-line);border-radius:999px;padding:.5rem 1rem;font-weight:600;font-size:.9rem;color:var(--color-ink)}
    .rv-area-tag:hover{border-color:var(--color-sage)}
    /* Bold dark flow (how it goes) */
    .rv-work-flow .rv-flow{list-style:none;margin:2.5rem 0 0;padding:0;display:grid;grid-template-columns:repeat(auto-fit,minmax(220px,1fr));gap:1.1rem}
    .rv-flow-step{position:relative;background:rgba(255,255,255,.055);border:1px solid rgba(255,255,255,.13);border-radius:var(--radius-lg,16px);padding:1.5rem 1.4rem;transition:transform .15s ease,background-color .15s ease}
    .rv-flow-step:hover{transform:translateY(-3px);background:rgba(255,255,255,.09)}
    .rv-flow-num{font-family:var(--font-display);font-weight:800;font-size:1.9rem;line-height:1;color:var(--color-wheat);letter-spacing:-.02em}
    .rv-flow-title{font-family:var(--font-display);font-size:1.15rem;font-weight:700;color:#fff;margin:.5rem 0 .5rem}
    .rv-flow-step p{margin:0;color:#c8d3c5;font-size:.95rem;line-height:1.55}
    .rv-flow-outcome{margin-top:2.75rem;padding-top:2rem;border-top:1px solid rgba(255,255,255,.14)}
    .rv-work-flow .rv-work-deliverables li{color:#e7ecdf}
    .rv-work-flow .rv-work-reassure{color:#aebca9}
    /* Bolder craft cards */
    .rv-craft-grid .rv-craft-card{padding:1.7rem 1.6rem 1.6rem;border-radius:var(--radius-lg,18px);overflow:hidden;transition:transform .15s ease,border-color .15s ease}
    .rv-craft-grid .rv-craft-card::before{content:"";position:absolute;left:0;top:0;bottom:0;width:5px;background:var(--ridgeline)}
    .rv-craft-grid .rv-craft-card:hover{transform:translateY(-3px);border-color:color-mix(in srgb,var(--color-clay) 35%,var(--color-line))}
    .rv-craft-grid .rv-craft-num{font-family:var(--font-display);font-weight:800;font-size:1.9rem;color:color-mix(in srgb,var(--color-clay) 60%,transparent);letter-spacing:-.02em}
    .rv-craft-grid .rv-craft-title{font-size:1.3rem;margin-top:.35rem}
    /* Areas served — bold, vivid, airy, left-aligned */
    .rv-areas2{padding-block:clamp(3.25rem,6vw,5.25rem)}
    .rv-areas2 .rv-shell{max-width:66rem}
    .rv-areas2-head{text-align:left;max-width:52rem;margin:0}
    .rv-areas2-head .rv-eyebrow{display:inline-block}
    .rv-areas2-h{font-family:var(--font-display);font-weight:800;font-size:clamp(1.85rem,4.2vw,2.85rem);line-height:1.08;letter-spacing:-.03em;color:var(--color-ink);margin:.45rem 0 0;max-width:18ch}
    .rv-areas2-accent{background:var(--ridgeline);-webkit-background-clip:text;background-clip:text;-webkit-text-fill-color:transparent;color:transparent}
    .rv-areas2-lede{margin:1.4rem 0 0;max-width:46rem;font-size:1.12rem;line-height:1.75;color:var(--color-body)}
    .rv-areas2-towns{display:flex;flex-wrap:wrap;justify-content:flex-start;gap:.7rem;margin:clamp(2.5rem,5vw,3.75rem) 0 0;max-width:58rem}
    .rv-areas2-town{background:var(--color-paper,#fff);border:1.5px solid var(--color-line);border-radius:999px;padding:.6rem 1.2rem;font-family:var(--font-display);font-weight:700;font-size:.95rem;color:var(--color-ink);transition:transform .15s ease,border-color .15s ease,color .15s ease,box-shadow .15s ease}
    .rv-areas2-town:hover{transform:translateY(-2px);border-color:var(--color-clay);color:var(--color-clay);box-shadow:0 6px 16px color-mix(in srgb,var(--color-clay) 18%,transparent)}
    .rv-areas2-foot{text-align:left;max-width:52rem;margin:clamp(2.25rem,4.5vw,3.25rem) 0 0}
    .rv-areas2-note{margin:0;color:var(--color-muted);line-height:1.7}
    .rv-areas2-tagline{margin:1.6rem 0 0;font-family:var(--font-mono);font-size:.78rem;letter-spacing:.06em;text-transform:uppercase;color:var(--color-pine);font-weight:600}
    .rv-areas2-tagline span{color:var(--color-clay);margin:0 .35rem}
    html[data-theme="dark"] .rv-areas2-tagline{color:var(--color-sage)}
    /* Don't-see-your-business — bigger section */
    .rv-morebiz-grid{display:flex;flex-wrap:wrap;gap:.7rem;margin-top:2rem}
    .rv-morebiz-item{background:color-mix(in srgb,var(--color-clay) 8%,var(--color-surface));border:1px solid var(--color-line);border-radius:999px;padding:.55rem 1.1rem;font-family:var(--font-display);font-weight:700;font-size:.92rem;color:var(--color-ink);transition:transform .15s ease,border-color .15s ease,color .15s ease}
    .rv-morebiz-item:hover{transform:translateY(-2px);border-color:var(--color-clay);color:var(--color-clay)}
    .rv-morebiz-panel{margin-top:clamp(2.25rem,4vw,3.25rem);display:grid;grid-template-columns:1fr 1fr;gap:clamp(1.75rem,4vw,3rem);align-items:center;background:var(--color-surface);border:1px solid var(--color-line);border-radius:var(--radius-lg,18px);padding:clamp(1.9rem,4vw,3rem);position:relative;overflow:hidden}
    .rv-morebiz-panel::before{content:"";position:absolute;left:0;top:0;bottom:0;width:6px;background:var(--ridgeline)}
    .rv-morebiz-panel-h{font-family:var(--font-display);font-weight:800;font-size:clamp(1.35rem,2.7vw,2rem);line-height:1.14;letter-spacing:-.01em;color:var(--color-ink);margin:.45rem 0 1.1rem;max-width:22ch}
    .rv-morebiz-fine{margin:.8rem 0 0;font-size:.85rem;color:var(--color-muted);max-width:34ch;line-height:1.55}
    .rv-morebiz-check{margin:0}
    @media(max-width:720px){.rv-morebiz-panel{grid-template-columns:1fr;gap:1.5rem}}
  </style>

  <script>
    (function () {
      var grid = document.getElementById('rv-work-grid');
      var bar = document.querySelector('.rv-work-filters');
      if (!grid || !bar) return;

      var cards = Array.prototype.slice.call(grid.querySelectorAll('.rv-work-card'));
      var buttons = Array.prototype.slice.call(bar.querySelectorAll('.rv-filter'));
      var countEl = document.getElementById('rv-work-count');
      var emptyEl = document.getElementById('rv-work-empty');
      var emptyAll = emptyEl ? emptyEl.querySelector('.rv-work-empty-all') : null;
      var reduce = window.matchMedia && window.matchMedia('(prefers-reduced-motion: reduce)').matches;
      var total = cards.length;

      function noun(n) {
        return n === 1 ? 'concept' : 'concepts';
      }

      function applyFilter(filter) {
        var shown = 0;
        cards.forEach(function (card) {
          var show = filter === 'all' || card.getAttribute('data-cat') === filter;
          card.classList.toggle('is-hidden', !show);
          if (show) shown += 1;
        });
        if (countEl) {
          countEl.textContent = 'Showing ' + shown + ' ' + noun(shown);
        }
        if (emptyEl) emptyEl.hidden = shown !== 0;
      }

      function setActive(active) {
        buttons.forEach(function (b) {
          b.setAttribute('aria-pressed', b === active ? 'true' : 'false');
        });
      }

      function run(btn) {
        if (!btn || btn.getAttribute('aria-pressed') === 'true') return;
        setActive(btn);
        var filter = btn.getAttribute('data-filter');
        if (reduce) { applyFilter(filter); return; }
        grid.classList.add('is-filtering');
        window.setTimeout(function () {
          applyFilter(filter);
          requestAnimationFrame(function () {
            grid.classList.remove('is-filtering');
          });
        }, 250);
      }

      buttons.forEach(function (btn) {
        btn.addEventListener('click', function () { run(btn); });
      });
      if (emptyAll) {
        emptyAll.addEventListener('click', function () {
          var all = bar.querySelector('[data-filter="all"]');
          if (all) run(all);
        });
      }
      if (countEl) countEl.textContent = 'Showing ' + total + ' ' + noun(total);
    })();
  </script>

@endsection
