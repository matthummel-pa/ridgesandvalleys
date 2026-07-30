{{--
  Template Name: Work
--}}
@extends('layouts.app')

@section('content')
  @php($ctaHref = \App\cta_href(get_theme_mod('rv_cta_url', '/contact/')))

  <section class="rv-hero">
    <span class="rv-stripe" aria-hidden="true"></span>
    <span class="rv-hero-bg" style="background-image:url('{{ \App\field('hero_bg', \App\stock_image('hero-work')) }}')" aria-hidden="true"></span>
    <div class="rv-shell rv-hero-inner">
      {!! \App\eyebrow(\App\field('hero_eyebrow', __('Selected work', 'sage'))) !!}
      <h1 class="rv-hero-title">{{ \App\field('hero_title', __('Business owners buy', 'sage')) }} <em class="rv-accent">{{ \App\field('hero_accent', __('confidence.', 'sage')) }}</em></h1>
      <p class="rv-hero-sub">{{ \App\field('hero_sub', __('Most of what\'s here are concept sites I designed and built myself — clearly labeled, fully clickable demos of how I\'d solve a real business problem. As client projects launch, they\'ll appear here the same way: the problem, the approach, and the result.', 'sage')) }}</p>
      <div class="rv-hero-actions">
        <a class="rv-btn rv-btn-primary" href="{{ \App\cta_href(\App\field('hero_btn1_url', get_theme_mod('rv_cta_url', '/contact/'))) }}">{{ __('Start your project', 'sage') }}</a>
        <a class="rv-btn rv-btn-ghost" href="#case-studies">{{ __('Explore the concepts', 'sage') }} &darr;</a>
      </div>
    </div>
  </section>

  {{-- BOLD PROOF STRIP --}}
  <section class="rv-band rv-band-pine rv-work-stats">
    <div class="rv-shell">
      <div class="rv-work-stats-grid">
        @foreach (\App\field_rows('work_stats', [
          ['value' => '15+', 'unit' => '', 'label' => __('years building for the web', 'sage')],
          ['value' => '10', 'unit' => '', 'label' => __('full concept sites to explore', 'sage')],
          ['value' => '7–10', 'unit' => __('days', 'sage'), 'label' => __('typical launch, start to live', 'sage')],
          ['value' => '100%', 'unit' => '', 'label' => __('yours — domain, hosting, content', 'sage')],
        ]) as $ws)
          <div class="rv-work-stat"><span class="rv-work-stat-v">{{ $ws['value'] ?? '' }}@if(($ws['unit'] ?? '') !== '')<span class="rv-work-stat-u">{{ $ws['unit'] }}</span>@endif</span><span class="rv-work-stat-l">{{ $ws['label'] ?? '' }}</span></div>
        @endforeach
      </div>
    </div>
  </section>

  <section class="rv-shell-full">
    <div class="rv-reading rv-prose" style="padding-top:var(--section-y);padding-bottom:0">
      @while(have_posts()) @php(the_post()) @if (trim(get_the_content())) @php(the_content()) @endif @endwhile
    </div>
  </section>

  {{-- WHY THESE ARE WORTH YOUR TIME --}}
  <section class="rv-shell rv-band">
    <div class="rv-headstack">
      {!! \App\eyebrow(\App\field('work_why_eyebrow', __('Honest by default', 'sage'))) !!}
      <h2 class="rv-section-title">{{ \App\field('work_why_title', __('Real work you can', 'sage')) }} <em class="rv-accent">{{ \App\field('work_why_accent', __('actually click.', 'sage')) }}</em></h2>
      <p class="rv-page-intro">{!! \App\field('work_why_intro', __('<strong>I built these myself — one per industry.</strong> Each is a live, working demo you can click around, so you can see exactly how I think, not just a pretty screenshot. No fake clients, no borrowed templates, no stock mockups dressed up as real projects.', 'sage')) !!}</p>
    </div>
    <div class="rv-vgrid">
      @foreach (\App\field_rows('work_why_items', [
        ['kicker' => __('Get found', 'sage'), 'title' => __('Show up when it counts', 'sage'), 'text' => __('Google Business Profile, local SEO, and fast, clean pages so the right people in Adams County find you first — not three competitors down.', 'sage')],
        ['kicker' => __('Earn trust', 'sage'), 'title' => __('Look as good as you are', 'sage'), 'text' => __('Clear copy, real photos, mobile-first layouts, and accessibility built in — a site that makes a first-time visitor feel safe picking up the phone.', 'sage')],
        ['kicker' => __('Stay in control', 'sage'), 'title' => __('You own everything', 'sage'), 'text' => __('Your domain, your hosting, your content — and a short training video so you can update hours and prices yourself. No lock-in, no ransom.', 'sage')],
      ]) as $i => $v)
        <article class="rv-vcard">
          <span class="rv-vcard-num">{{ sprintf('%02d', $i + 1) }}</span>
          <span class="rv-vcard-kicker">{{ $v['kicker'] ?? '' }}</span>
          <h3 class="rv-vcard-title">{{ $v['title'] ?? '' }}</h3>
          <p>{{ $v['text'] ?? '' }}</p>
        </article>
      @endforeach
    </div>
  </section>

  @php($work = new WP_Query(['post_type' => 'project', 'posts_per_page' => 12, 'no_found_rows' => true]))
  <section class="rv-shell rv-band" id="case-studies">
    @if ($work->have_posts())
      <div class="rv-headstack">
        {!! \App\eyebrow(\App\field('work_cs_eyebrow', __('The work', 'sage'))) !!}
        <h2 class="rv-section-title">{{ \App\field('work_cs_title', __('Concepts, built', 'sage')) }} <em class="rv-accent">{{ \App\field('work_cs_accent', __('in full.', 'sage')) }}</em></h2>
        <p class="rv-page-intro">{{ \App\field('work_cs_intro', __('These are concept sites I designed and coded from scratch — one for each kind of local business I work with. Anything marked “Concept” is my own self-initiated demo, not a client project. Click any one for the problem it solves, my approach, and a live, working preview.', 'sage')) }}</p>
      </div>
      <div class="rv-work-cats">
        <span class="rv-work-cats-label">{{ \App\field('work_cats_label', __('Built for', 'sage')) }}</span>
        @foreach (\App\field_lines('work_cats', [__('Hotels & inns', 'sage'), __('Restaurants', 'sage'), __('Retail & shops', 'sage'), __('Tours', 'sage'), __('Real estate', 'sage')]) as $cat)
          <span class="rv-work-cat">{{ $cat }}</span>
        @endforeach
      </div>
      <div class="rv-grid" style="grid-template-columns:repeat(auto-fit,minmax(320px,1fr));margin-top:2rem">
        @while($work->have_posts()) @php($work->the_post())
          @php($peyebrow = get_post_meta(get_the_ID(), '_rv_eyebrow', true) ?: (get_post_meta(get_the_ID(), '_rv_client', true) ?: __('Case study', 'sage')))
          @php($psummary = get_post_meta(get_the_ID(), '_rv_summary', true) ?: get_the_excerpt())
          @php($ppreview = get_post_meta(get_the_ID(), '_rv_preview', true))
          @php($pconcept = get_post_meta(get_the_ID(), '_rv_is_concept', true) === '1')
          <article @php(post_class('rv-card rv-work-card'))>
            <a class="rv-work-link" href="{{ get_permalink() }}">
              @if (has_post_thumbnail())
                <span class="rv-work-thumb">@php(the_post_thumbnail('rv-card', ['loading' => 'lazy']))@if ($pconcept)<span class="rv-work-badge">{{ __('Concept', 'sage') }}</span>@endif</span>
              @elseif ($ppreview)
                <span class="rv-work-thumb rv-media-photo"><img src="{{ esc_url($ppreview) }}" alt="{{ esc_attr(get_the_title()) }}" loading="lazy" onerror="this.closest('.rv-work-thumb').classList.add('rv-work-thumb-placeholder')">@if ($pconcept)<span class="rv-work-badge">{{ __('Concept', 'sage') }}</span>@endif</span>
              @else
                <span class="rv-work-thumb rv-work-thumb-placeholder" aria-hidden="true">@if ($pconcept)<span class="rv-work-badge">{{ __('Concept', 'sage') }}</span>@endif</span>
              @endif
              <span class="rv-work-body">
                <span class="rv-eyebrow">{{ $peyebrow }}</span>
                <span class="rv-work-title">{!! get_the_title() !!}</span>
                @if ($psummary)<span class="rv-work-excerpt">{{ $psummary }}</span>@endif
                <span class="rv-work-more">{{ __('Read the case study', 'sage') }} {!! \App\icon('arrow') !!}</span>
              </span>
            </a>
          </article>
        @endwhile
      </div>
      @php(wp_reset_postdata())
    @else
      {{-- Static case cards (mockup content) until Projects are added in the dashboard --}}
      <div class="rv-grid" style="grid-template-columns:repeat(auto-fit,minmax(320px,1fr))">
        @php($cases = [
          [__('Concept · Professional services', 'sage'), __('A local law office (concept)', 'sage'), __('A self-initiated concept showing a clearer path from visitor to consultation.', 'sage'), 'work-1', __('Wentz farm buildings near Gettysburg', 'sage')],
          [__('Product · Process', 'sage'), __('Groundwork to Launch', 'sage'), __('The delivery engine behind the studio — a productized website workflow I built.', 'sage'), 'work-2', __('Workspace desk with a laptop', 'sage')],
          [__('Concept · Retail', 'sage'), __('A Gettysburg gift shop (concept)', 'sage'), __('A clearly-labeled concept I designed to show my approach for local retail.', 'sage'), 'work-3', __('Historic brick storefront on a downtown Gettysburg street', 'sage')],
          [__('Concept · Food & drink', 'sage'), __('A local restaurant (concept)', 'sage'), __('A concept refresh — menus, hours, and directions made mobile-first and easy to keep current.', 'sage'), 'work-4', __('Rustic table setting', 'sage')],
        ])
        @foreach ($cases as $c)
          <article class="rv-card rv-work-card">
            <a class="rv-work-link" href="{{ \App\cta_href(get_theme_mod('rv_cta_url', '/contact/')) }}">
              <span class="rv-work-thumb rv-media-photo">
                <img src="{{ \App\stock_image($c[3]) }}" alt="{{ $c[4] }}" loading="lazy" onerror="this.style.display='none'">
              </span>
              <span class="rv-work-body">
                <span class="rv-eyebrow">{{ $c[0] }}</span>
                <span class="rv-work-title">{{ $c[1] }}</span>
                <span class="rv-work-excerpt">{{ $c[2] }}</span>
              </span>
            </a>
          </article>
        @endforeach
      </div>
    @endif
    <p class="rv-tool-hint" style="margin-top:1.5rem">{{ \App\field('work_hint', __('Each write-up follows: Problem → Approach → What it does → Design goals. On concepts these goals are illustrative; on client projects they become measured results.', 'sage')) }}</p>
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
    .rv-work-more{margin-top:.85rem;display:inline-flex;align-items:center;gap:.4rem;font-weight:700;font-size:.85rem;color:var(--color-clay)}
    .rv-work-more svg{width:15px;height:15px;transition:transform .2s ease}
    .rv-work-card:hover .rv-work-more svg{transform:translateX(3px)}
    .rv-work-thumb{position:relative}
    .rv-work-badge{position:absolute;top:.7rem;left:.7rem;z-index:2;background:color-mix(in srgb,var(--color-pine) 90%,transparent);color:var(--color-paper);font-family:var(--font-mono);font-size:.66rem;letter-spacing:.08em;text-transform:uppercase;padding:.3rem .6rem;border-radius:999px}
    .rv-craft-grid{display:grid;grid-template-columns:repeat(auto-fit,minmax(260px,1fr));gap:1.25rem;margin-top:2.5rem}
    .rv-craft-card{background:var(--color-surface);border:1px solid var(--color-line);border-radius:var(--radius-card);padding:1.5rem 1.6rem;position:relative}
    .rv-craft-num{font-family:var(--font-mono);font-size:.85rem;font-weight:700;color:var(--color-clay);letter-spacing:.05em}
    .rv-craft-title{margin:.4rem 0 .5rem;font-family:var(--font-display);font-size:1.2rem;font-weight:700;color:var(--color-ink)}
    .rv-craft-card p{margin:0;color:var(--color-body)}
    .rv-area-tags{display:flex;flex-wrap:wrap;gap:.6rem;margin-top:2.25rem}
    .rv-area-tag{background:var(--color-surface);border:1px solid var(--color-line);border-radius:999px;padding:.5rem 1rem;font-weight:600;font-size:.9rem;color:var(--color-ink)}
    .rv-area-tag:hover{border-color:var(--color-sage)}
    /* Bold proof strip (always-dark pine band) */
    .rv-work-stats{padding-block:clamp(2.25rem,4vw,3.25rem)}
    .rv-work-stats-grid{display:grid;grid-template-columns:repeat(auto-fit,minmax(180px,1fr));gap:1.5rem 2rem;text-align:center}
    .rv-work-stat{display:flex;flex-direction:column;gap:.3rem}
    .rv-work-stat-v{font-family:var(--font-display);font-weight:800;font-size:clamp(2.4rem,5.5vw,3.4rem);line-height:1;color:#fff;letter-spacing:-.02em}
    .rv-work-stat-u{font-size:.42em;font-weight:700;margin-left:.15em;color:var(--color-wheat);letter-spacing:0}
    .rv-work-stat-l{font-family:var(--font-mono);font-size:.76rem;letter-spacing:.05em;text-transform:uppercase;color:#c3cfc0;line-height:1.35}
    /* Built-for range chips */
    .rv-work-cats{display:flex;flex-wrap:wrap;align-items:center;gap:.6rem;margin-top:1.75rem}
    .rv-work-cats-label{font-family:var(--font-mono);font-size:.72rem;letter-spacing:.08em;text-transform:uppercase;color:var(--color-muted);margin-right:.35rem}
    .rv-work-cat{background:color-mix(in srgb,var(--color-clay) 10%,var(--color-surface));border:1px solid color-mix(in srgb,var(--color-clay) 30%,var(--color-line));border-radius:999px;padding:.45rem 1rem;font-family:var(--font-display);font-weight:700;font-size:.92rem;color:var(--color-ink)}
    /* Mid-page CTA */
    .rv-work-midcta-wrap{padding-top:var(--section-y)}
    .rv-work-midcta{display:grid;grid-template-columns:1.5fr auto;gap:2rem;align-items:center;background:var(--color-surface);border:1px solid var(--color-line);border-radius:var(--radius-lg,18px);padding:clamp(1.75rem,3.5vw,2.75rem);position:relative;overflow:hidden}
    .rv-work-midcta::before{content:"";position:absolute;left:0;top:0;bottom:0;width:6px;background:var(--ridgeline)}
    .rv-work-midcta-h{font-family:var(--font-display);font-weight:800;font-size:clamp(1.6rem,3.4vw,2.4rem);line-height:1.08;letter-spacing:-.02em;color:var(--color-ink);margin:.4rem 0 .6rem}
    .rv-work-midcta-copy p{margin:0;color:var(--color-body);max-width:58ch;line-height:1.6}
    .rv-work-midcta-actions{display:flex;flex-direction:column;align-items:flex-start;gap:.6rem}
    .rv-work-midcta-fine{font-family:var(--font-mono);font-size:.72rem;letter-spacing:.03em;color:var(--color-muted)}
    @media(max-width:720px){.rv-work-midcta{grid-template-columns:1fr}}
    /* Bold value cards */
    .rv-vgrid{display:grid;grid-template-columns:repeat(auto-fit,minmax(260px,1fr));gap:1.25rem;margin-top:2.25rem}
    .rv-vcard{position:relative;background:var(--color-surface);border:1px solid var(--color-line);border-radius:var(--radius-lg,18px);padding:1.9rem 1.6rem 1.7rem;overflow:hidden;transition:transform .15s ease,border-color .15s ease,box-shadow .2s ease}
    .rv-vcard:hover{transform:translateY(-3px);border-color:color-mix(in srgb,var(--color-clay) 40%,var(--color-line));box-shadow:var(--shadow-lift)}
    .rv-vcard::before{content:"";position:absolute;left:0;top:0;right:0;height:5px;background:var(--ridgeline)}
    .rv-vcard-num{font-family:var(--font-display);font-weight:800;font-size:2.4rem;line-height:1;color:color-mix(in srgb,var(--color-clay) 55%,transparent);letter-spacing:-.02em}
    .rv-vcard-kicker{display:block;margin-top:.5rem;font-family:var(--font-mono);font-size:.7rem;letter-spacing:.1em;text-transform:uppercase;color:var(--color-clay)}
    .rv-vcard-title{font-family:var(--font-display);font-size:1.35rem;font-weight:800;color:var(--color-ink);margin:.3rem 0 .5rem;line-height:1.12}
    .rv-vcard p{margin:0;color:var(--color-body);line-height:1.6}
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
    .rv-areas2{padding-block:clamp(4.5rem,9vw,7.5rem)}
    .rv-areas2 .rv-shell{max-width:66rem}
    .rv-areas2-head{text-align:left;max-width:52rem;margin:0}
    .rv-areas2-head .rv-eyebrow{display:inline-block}
    .rv-areas2-h{font-family:var(--font-display);font-weight:800;font-size:clamp(2.4rem,6.5vw,4.25rem);line-height:1.02;letter-spacing:-.03em;color:var(--color-ink);margin:.75rem 0 0}
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

  @include('partials.cta')
@endsection
