{{--
  Template Name: Services
--}}
@extends('layouts.app')

@section('content')
  @php($ctaHref = \App\cta_href(get_theme_mod('rv_cta_url', '/contact/')))

  <section class="rv-hero" aria-labelledby="rv-svc-hero-title">
    <span class="rv-stripe" aria-hidden="true"></span>
    @include('partials.hero-bg', ['fallback' => \App\stock_image('hero-services')])
    <div class="rv-shell rv-hero-inner">
      {!! \App\eyebrow(\App\field('hero_eyebrow', __('Web design & local SEO · Gettysburg', 'sage'))) !!}
      <h1 id="rv-svc-hero-title" class="rv-hero-title">{{ \App\field('hero_title', __('Gettysburg web design that earns its', 'sage')) }} <em class="rv-accent">{{ \App\field('hero_accent', __('keep.', 'sage')) }}</em></h1>
      <p class="rv-hero-sub">{{ \App\field('hero_lede', __('Fixed-scope packages for Gettysburg and Adams County — more calls, clearer hours, a site you own. Honest pricing, no jargon.', 'sage')) }}</p>
      <div class="rv-hero-actions">
        <a class="rv-btn rv-btn-primary" href="{{ $ctaHref }}">{{ \App\field('hero_btn1', __('Get a quote', 'sage')) }}</a>
        <a class="rv-btn rv-btn-ghost" href="#packages">{{ \App\field('hero_btn2', __('See the packages', 'sage')) }} &darr;</a>
      </div>
    </div>
    @php($svcProof = \App\svc_proof())
    @if (! empty($svcProof))
      <div class="rv-hero-proof">
        <div class="rv-shell">
          <ul class="rv-hero-stats" aria-label="{{ __('At a glance', 'sage') }}">
            @foreach ($svcProof as $pf)
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

  {{-- What you're paying for — split: the pitch on the left, the three standards on the right. --}}
  <section class="rv-band rv-band-alt rv-svc-value" aria-labelledby="rv-svc-value-title">
    <div class="rv-shell rv-svc-value-grid">
      <div class="rv-svc-value-copy">
        {!! \App\eyebrow(\App\field('svcvalue_eyebrow', __('Before the pricing', 'sage'))) !!}
        <h2 id="rv-svc-value-title" class="rv-section-title">{{ \App\field('svcvalue_title', __('What you\'re really', 'sage')) }} <em class="rv-accent">{{ \App\field('svcvalue_accent', __('paying for.', 'sage')) }}</em></h2>
        <p class="rv-page-intro">{{ \App\field('svcvalue_intro', __('Every package below buys the same three things, whatever your budget. The price changes with scope — the standards never do.', 'sage')) }}</p>
        <a class="rv-svc-value-jump" href="#packages">{{ __('See the packages', 'sage') }} {!! \App\icon('arrow') !!}</a>
      </div>
      <div class="rv-svc-value-list">
        @foreach (\App\field_rows('svcvalue_items', \App\svcvalue_item_defaults()) as $v)
          <article class="rv-svc-value-item">
            @if (($v['kicker'] ?? '') !== '')<span class="rv-svc-value-kicker">{{ $v['kicker'] }}</span>@endif
            @if (($v['title'] ?? '') !== '')<h3>{{ $v['title'] }}</h3>@endif
            @if (($v['text'] ?? '') !== '')<p>{{ $v['text'] }}</p>@endif
          </article>
        @endforeach
      </div>
    </div>
  </section>

  {{-- PACKAGES --}}
  <section id="packages" class="rv-shell rv-band" style="scroll-margin-top:6rem">
    <div class="rv-svc-plans-head">
      {!! \App\eyebrow(\App\field('plans_eyebrow', __('Gettysburg web design packages', 'sage'))) !!}
      <h2 class="rv-section-title">{{ \App\field('plans_title', __('Pick the plan that fits', 'sage')) }} <em class="rv-accent">{{ \App\field('plans_accent', __('where you are.', 'sage')) }}</em></h2>
      <p class="rv-page-intro">{{ \App\field('plans_intro', __('Every Gettysburg web design package is one fixed price, agreed up front — no hourly meter, no surprise invoices. Rescue a site you already have, launch a new one for Adams County, or keep it growing after. Not sure which fits? Tell me about your business and I\'ll point you to the right one.', 'sage')) }}</p>
    </div>
    @include('partials.package-plans', [
      'projects' => \App\svc_project_packages(),
      'care'     => \App\svc_care_packages(),
    ])
  </section>

  {{-- WHAT EVERY BUILD INCLUDES --}}
  <section id="included" class="rv-band rv-band-alt rv-svc-incl-band" aria-labelledby="rv-incl-heading">
    <div class="rv-shell">
      <div class="rv-svc-incl-head">
        {!! \App\eyebrow(\App\field('svc_incl_eyebrow', __('Same baseline, every package', 'sage'))) !!}
        <h2 id="rv-incl-heading" class="rv-section-title">{{ \App\field('svc_incl_title', __('What every build', 'sage')) }} <em class="rv-accent">{{ \App\field('svc_incl_accent', __('includes.', 'sage')) }}</em></h2>
        <p class="rv-page-intro">{{ \App\field('svc_incl_intro', __('Rescue, Local Launch, or Growth Site — the price changes with scope. Accessibility, local SEO, and a site you own do not.', 'sage')) }}</p>
      </div>
      <ul class="rv-svc-incl">
        @foreach (\App\field_rows('svc_incl_points', \App\svc_incl_point_defaults()) as $d)
          <li class="rv-card rv-svc-incl-item">
            <h3>{{ $d['title'] ?? '' }}</h3>
            <p>{{ $d['text'] ?? '' }}</p>
          </li>
        @endforeach
      </ul>
      <div class="rv-svc-bounds">
        @foreach (\App\field_rows('svc_bound_items', \App\svc_bound_defaults()) as $b)
          <article class="rv-svc-bound">
            <h3>{{ $b['title'] ?? '' }}</h3>
            <p>{{ $b['text'] ?? '' }}</p>
          </article>
        @endforeach
      </div>
    </div>
  </section>

  {{-- LOCAL SEO (bold, dark) --}}
  <section id="local-seo" class="rv-band rv-band-pine rv-svc-seo" aria-labelledby="rv-seo-heading">
    <div class="rv-shell">
      <div class="rv-svc-seo-head">
        {!! \App\eyebrow(\App\field('seo_eyebrow', __('Get found in Gettysburg', 'sage'))) !!}
        <h2 id="rv-seo-heading" class="rv-section-title">{{ \App\field('seo_title', __('Local SEO that puts you', 'sage')) }} <em class="rv-accent">{{ \App\field('seo_accent', __('on the map.', 'sage')) }}</em></h2>
        <p class="rv-svc-seo-lead">{!! \App\field('svc_seo_intro', __('<strong>Local SEO decides whether they find you.</strong> When someone in Adams County searches “web designer near me” or “best breakfast in Gettysburg,” you either show up — or the shop three listings up does.', 'sage')) !!}</p>
      </div>
      <ul class="rv-svc-seo-list">
        @foreach (\App\field_rows('svc_seo_points', \App\svc_seo_point_defaults()) as $item)
          <li class="rv-svc-seo-item">
            <h3>{{ $item['title'] ?? '' }}</h3>
            <p>{{ $item['text'] ?? '' }}</p>
          </li>
        @endforeach
      </ul>
      <div class="rv-svc-seo-bounds">
        @foreach (\App\field_rows('svc_seo_bounds', \App\svc_seo_bound_defaults()) as $b)
          <article class="rv-svc-seo-bound">
            <h3>{{ $b['title'] ?? '' }}</h3>
            <p>{{ $b['text'] ?? '' }}</p>
          </article>
        @endforeach
      </div>
    </div>
  </section>

  {{-- PROCESS --}}
  <section class="rv-band rv-band-alt">
    <div class="rv-shell">
      {!! \App\eyebrow(\App\field('sproc_eyebrow', __('Groundwork to launch', 'sage'))) !!}
      <h2 class="rv-section-title">{{ \App\field('sproc_title', __('Fast comes from a', 'sage')) }} <em class="rv-accent">{{ \App\field('sproc_accent', __('process,', 'sage')) }}</em> {{ \App\field('sproc_title_end', __('not corner-cutting.', 'sage')) }}</h2>
      <div class="rv-timeline" style="margin-top:2rem">
        @foreach (\App\field_rows('sproc_items', [
          ['day' => __('Day 1', 'sage'), 'title' => __('Compile the brief', 'sage'), 'text' => __('One structured intake form + your assets become the core site brief.', 'sage')],
          ['day' => __('Day 2–3', 'sage'), 'title' => __('Draft & build', 'sage'), 'text' => __('Sitemap, style direction, first copy, and a working staging site.', 'sage')],
          ['day' => __('Day 4–5', 'sage'), 'title' => __('Human QA', 'sage'), 'text' => __('Fact-check, mobile, accessibility, SEO, performance, forms.', 'sage')],
          ['day' => __('Day 6', 'sage'), 'title' => __('One revision', 'sage'), 'text' => __('Consolidated feedback, applied in a single round.', 'sage')],
          ['day' => __('Day 7–10', 'sage'), 'title' => __('Launch', 'sage'), 'text' => __('Domain, analytics, search settings, backups, and handoff.', 'sage')],
          ['day' => __('Day 30', 'sage'), 'title' => __('Warranty', 'sage'), 'text' => __('Workmanship warranty, plus an optional care plan.', 'sage')],
        ]) as $st)
          <div class="rv-tl-step">
            <span class="rv-tl-day">{{ $st['day'] ?? '' }}</span>
            <h3 class="rv-tl-title">{{ $st['title'] ?? '' }}</h3>
            <p>{{ $st['text'] ?? '' }}</p>
          </div>
        @endforeach
      </div>
    </div>
  </section>

  {{-- AI-ASSISTED, HUMAN-FINISHED --}}
  <section class="rv-shell rv-band">
    {!! \App\eyebrow(\App\field('aisplit_eyebrow', __('AI-assisted, human-finished', 'sage'))) !!}
    <h2 class="rv-section-title">{{ \App\field('aisplit_title', __('The', 'sage')) }} <em class="rv-accent">{{ \App\field('aisplit_accent', __('honest', 'sage')) }}</em> {{ \App\field('aisplit_title_end', __('split.', 'sage')) }}</h2>
    <div class="rv-grid" style="grid-template-columns:repeat(auto-fit,minmax(280px,1fr));margin-top:1.75rem">
      @foreach (\App\field_rows('aisplit_items', [
        ['title' => __('AI and modern tools handle the blank page', 'sage'), 'text' => __('Summarizes intake, proposes a sitemap, drafts page copy and metadata, suggests image concepts and alt text, and builds QA checklists.', 'sage')],
        ['title' => __('Matt handles the judgment', 'sage'), 'text' => __('Confirms the real business goal, verifies every fact, rewrites the voice, chooses search intent, tests everything, and approves what goes public.', 'sage')],
      ]) as $ai)
        <article class="rv-card rv-feature">
          <span class="rv-stripe rv-stripe-thin" aria-hidden="true"></span>
          <h3 class="rv-feature-title">{{ $ai['title'] ?? '' }}</h3>
          <p class="rv-feature-text">{{ $ai['text'] ?? '' }}</p>
        </article>
      @endforeach
    </div>
    <p class="rv-tool-hint" style="margin-top:1.25rem;max-width:70ch">{{ \App\field('aisplit_note', __('Your agreement discloses that AI may assist with drafts while all work is reviewed. Confidential information is never placed into a third-party model without permission.', 'sage')) }}</p>
  </section>

  {{-- AFTER LAUNCH --}}
  <section class="rv-band rv-band-alt">
    <div class="rv-shell">
      {!! \App\eyebrow(\App\field('after_eyebrow', __('After launch', 'sage'))) !!}
      <h2 class="rv-section-title">{{ \App\field('after_title', __('Launch day isn\'t', 'sage')) }} <em class="rv-accent">{{ \App\field('after_accent', __('goodbye.', 'sage')) }}</em></h2>
      <div class="rv-grid rv-grid-3" style="margin-top:2rem">
        @foreach (\App\field_rows('after_items', [
          ['title' => __('A workmanship warranty', 'sage'), 'text' => __('If something I built breaks in the first 30 days, I fix it — no charge, no debate.', 'sage')],
          ['title' => __('Optional care plans', 'sage'), 'text' => __('Updates, backups, security, small edits, and reporting from $179/mo — cancel anytime, and you keep the site.', 'sage')],
          ['title' => __('You\'re never stuck', 'sage'), 'text' => __('You own the domain, hosting, and site. Want to take it in-house or hand it to someone else? It\'s yours to move.', 'sage')],
        ]) as $a)
          <article class="rv-card rv-feature">
            <span class="rv-stripe rv-stripe-thin" aria-hidden="true"></span>
            <h3 class="rv-feature-title">{{ $a['title'] ?? '' }}</h3>
            <p class="rv-feature-text">{{ $a['text'] ?? '' }}</p>
          </article>
        @endforeach
      </div>
    </div>
  </section>

  {{-- FAQ — buying objections, one at a time, then an invite to ask. --}}
  <section class="rv-band rv-svc-faq" aria-labelledby="rv-svc-faq-title">
    <div class="rv-shell rv-svc-faq-layout">
      <div class="rv-svc-faq-copy">
        {!! \App\eyebrow(\App\field('sfaq_eyebrow', __('Before you pick a package', 'sage'))) !!}
        <h2 id="rv-svc-faq-title" class="rv-section-title">{{ \App\field('sfaq_title', __('Answers before you', 'sage')) }} <em class="rv-accent">{{ \App\field('sfaq_accent', __('even ask.', 'sage')) }}</em></h2>
        <p class="rv-page-intro">{{ \App\field('sfaq_lede', __('These are the things Gettysburg owners actually ask before they hire me. Open the one that’s on your mind — and if yours isn’t here, write. I answer myself.', 'sage')) }}</p>
        <p class="rv-svc-faq-close">{{ \App\field('sfaq_close', __('Still chewing on something? Ask. You’ll hear back from me — not a form letter.', 'sage')) }}</p>
        <div class="rv-svc-faq-actions">
          <a class="rv-btn rv-btn-primary" href="{{ $ctaHref }}">{{ \App\field('sfaq_close_btn', __('Ask me', 'sage')) }}</a>
          <a class="rv-svc-faq-more" href="{{ \App\cta_href(\App\field('sfaq_more_url', '/faq/')) }}">{{ \App\field('sfaq_more', __('More questions, answered', 'sage')) }} {!! \App\icon('arrow') !!}</a>
        </div>
      </div>
      <div class="rv-faq">
        @foreach (\App\field_rows('sfaq_qs', \App\svc_faq_item_defaults()) as $i => $f)
          <details class="rv-faq-item" name="svc-faq" @if ($i === 0) open @endif>
            <summary>{{ $f['q'] ?? '' }}</summary>
            <div class="rv-faq-answer"><p>{{ $f['a'] ?? '' }}</p></div>
          </details>
        @endforeach
      </div>
    </div>
  </section>

  {{-- FOUNDING OFFER --}}
  <section class="rv-band rv-band-pine">
    <div class="rv-shell">
      {!! \App\eyebrow(\App\field('founding_eyebrow', __('Founding offer · only 3 spots', 'sage'))) !!}
      <h2 class="rv-section-title">{{ \App\field('founding_title', __('Founding Local Launch —', 'sage')) }} <em class="rv-accent">{{ \App\field('founding_accent', __('$2,250.', 'sage')) }}</em></h2>
      <p class="rv-hero-sub" style="max-width:66ch">{{ \App\field('founding_body', __('The first three local clients get the full Local Launch at a founding rate — in exchange for a case study and an honest testimonial after a successful launch.', 'sage')) }}</p>
      <ul class="rv-founding-list" aria-label="{{ __('What\'s included', 'sage') }}">
        @foreach (\App\field_lines('founding_list', [
          __('Five pages', 'sage'), __('Contact form', 'sage'), __('Local SEO', 'sage'), __('Analytics', 'sage'),
          __('Accessibility review', 'sage'), __('One revision', 'sage'), __('Launch + training video', 'sage'), __('30 days of support', 'sage'),
        ]) as $inc)
          <li>{{ $inc }}</li>
        @endforeach
      </ul>
      <p class="rv-founding-after">{{ \App\field('founding_after', __('After that, it moves to the normal $3,250+ starting price.', 'sage')) }}</p>
      <div class="rv-hero-actions" style="justify-content:flex-start;margin-top:1.5rem">
        <a class="rv-btn rv-btn-on-dark" href="{{ $ctaHref }}">{{ \App\field('founding_button', __('Claim a founding spot', 'sage')) }}</a>
      </div>
    </div>
  </section>

  {!! \App\faq_schema(\App\svc_faq_pairs()) !!}
  {!! \App\offer_schema(\App\svc_packages(), $ctaHref) !!}

  <style>
    /* What you're paying for — split under the hero */
    .rv-svc-value{padding-block:clamp(2.25rem,4.5vw,3.5rem)}
    .rv-svc-value-grid{display:grid;grid-template-columns:minmax(0,1fr) minmax(0,1.08fr);gap:clamp(1.5rem,4vw,2.75rem);align-items:start}
    .rv-svc-value-copy .rv-section-title{margin:.35rem 0 .8rem}
    .rv-svc-value-copy .rv-page-intro{margin:0}
    .rv-svc-value-jump{display:inline-flex;align-items:center;gap:.4rem;margin-top:1.2rem;font-weight:700;font-size:.9rem;color:var(--color-clay);text-decoration:none}
    .rv-svc-value-jump:hover{color:var(--color-pine)}
    .rv-svc-value-jump svg{width:15px;height:15px;transition:transform .2s ease}
    .rv-svc-value-jump:hover svg{transform:translateX(3px)}
    .rv-svc-value-list{display:grid;gap:.8rem}
    .rv-svc-value-item{position:relative;background:var(--color-surface);border:1px solid var(--color-line);border-radius:var(--radius-lg,16px);padding:1.1rem 1.3rem 1.1rem 1.5rem;overflow:hidden}
    .rv-svc-value-item::before{content:"";position:absolute;left:0;top:0;bottom:0;width:4px;background:var(--ridgeline)}
    .rv-svc-value-kicker{display:block;font-family:var(--font-mono);font-size:.68rem;letter-spacing:.1em;text-transform:uppercase;color:var(--color-clay)}
    .rv-svc-value-item h3{font-family:var(--font-display);font-size:1.12rem;font-weight:700;color:var(--color-ink);margin:.2rem 0 .35rem;line-height:1.2}
    .rv-svc-value-item p{margin:0;color:var(--color-body);font-size:.95rem;line-height:1.55}
    @media(max-width:820px){.rv-svc-value-grid{grid-template-columns:1fr}}
    @media(prefers-reduced-motion:reduce){.rv-svc-value-jump svg{transition:none}}
    /* What every build includes: scannable checklist, then quieter boundaries */
    .rv-svc-incl{display:grid;grid-template-columns:repeat(3,1fr);gap:1rem;margin:2rem 0 0;padding:0;list-style:none}
    @media(max-width:900px){.rv-svc-incl{grid-template-columns:repeat(2,1fr)}}
    @media(max-width:620px){.rv-svc-incl{grid-template-columns:1fr}}
    .rv-svc-incl-item{position:relative;margin:0;padding:1.4rem 1.35rem 1.3rem;border-radius:var(--radius-lg,16px);overflow:hidden;transition:transform .15s ease,box-shadow .2s ease,border-color .15s ease}
    .rv-svc-incl-item:hover{transform:translateY(-3px);box-shadow:var(--shadow-soft);border-color:color-mix(in srgb,var(--color-sage) 55%,var(--color-line))}
    .rv-svc-incl-item h3{position:relative;font-family:var(--font-display);font-weight:800;font-size:1.12rem;color:var(--color-ink);margin:0;padding-left:1.85rem;line-height:1.25}
    .rv-svc-incl-item h3::before{content:"";position:absolute;left:0;top:.12em;width:1.1rem;height:1.1rem;border-radius:50%;background:color-mix(in srgb,var(--color-sage) 28%,transparent)}
    .rv-svc-incl-item h3::after{content:"";position:absolute;left:.34rem;top:.45em;width:.42rem;height:.22rem;border-left:2px solid var(--color-pine);border-bottom:2px solid var(--color-pine);transform:rotate(-45deg)}
    .rv-svc-incl-item p{margin:.5rem 0 0;padding-left:1.85rem;color:var(--color-body);font-size:.94rem;line-height:1.55}
    .rv-svc-bounds{display:grid;grid-template-columns:1fr 1fr;gap:1rem;margin-top:1.15rem}
    @media(max-width:720px){.rv-svc-bounds{grid-template-columns:1fr}}
    .rv-svc-bound{padding:1.35rem 1.45rem;border:1px solid var(--color-line);border-radius:var(--radius-lg,16px);background:color-mix(in srgb,var(--color-ink) 3.5%,var(--color-surface))}
    .rv-svc-bound h3{font-family:var(--font-mono);font-size:.68rem;letter-spacing:.08em;text-transform:uppercase;color:var(--color-clay);font-weight:700;margin:0 0 .5rem}
    .rv-svc-bound p{margin:0;color:var(--color-body);font-size:.92rem;line-height:1.55}
    /* Local SEO: same scan pattern as “what every build includes”, on pine */
    .rv-svc-seo-lead{max-width:68ch;margin:.85rem 0 0;color:#d3ddcf;font-size:1.05rem;line-height:1.6}
    .rv-svc-seo-lead strong{color:#fff}
    .rv-svc-seo-list{display:grid;grid-template-columns:repeat(3,1fr);gap:1rem;margin:2rem 0 0;padding:0;list-style:none}
    @media(max-width:900px){.rv-svc-seo-list{grid-template-columns:repeat(2,1fr)}}
    @media(max-width:620px){.rv-svc-seo-list{grid-template-columns:1fr}}
    .rv-svc-seo-item{position:relative;margin:0;background:rgba(255,255,255,.055);border:1px solid rgba(255,255,255,.13);border-radius:var(--radius-lg,16px);padding:1.4rem 1.35rem 1.3rem;overflow:hidden;transition:transform .15s ease,background-color .15s ease}
    .rv-svc-seo-item:hover{transform:translateY(-2px);background:rgba(255,255,255,.08)}
    .rv-svc-seo-item h3{position:relative;font-family:var(--font-display);font-size:1.12rem;font-weight:800;color:#fff;margin:0;padding-left:1.85rem;line-height:1.25}
    .rv-svc-seo-item h3::before{content:"";position:absolute;left:0;top:.12em;width:1.1rem;height:1.1rem;border-radius:50%;background:color-mix(in srgb,var(--color-wheat) 28%,transparent)}
    .rv-svc-seo-item h3::after{content:"";position:absolute;left:.34rem;top:.45em;width:.42rem;height:.22rem;border-left:2px solid var(--color-wheat);border-bottom:2px solid var(--color-wheat);transform:rotate(-45deg)}
    .rv-svc-seo-item p{margin:.5rem 0 0;padding-left:1.85rem;color:#d3ddcf;font-size:.94rem;line-height:1.55}
    .rv-svc-seo-bounds{display:grid;grid-template-columns:1fr 1fr;gap:1rem;margin-top:1.15rem}
    @media(max-width:720px){.rv-svc-seo-bounds{grid-template-columns:1fr}}
    .rv-svc-seo-bound{padding:1.35rem 1.45rem;border:1px solid rgba(255,255,255,.16);border-radius:var(--radius-lg,16px);background:rgba(255,255,255,.04)}
    .rv-band-pine .rv-svc-seo-bound h3{font-family:var(--font-mono);font-size:.68rem;letter-spacing:.08em;text-transform:uppercase;color:var(--color-wheat);font-weight:700;margin:0 0 .5rem}
    .rv-svc-seo-bound p{margin:0;color:#d3ddcf;font-size:.92rem;line-height:1.55}
    /* FAQ — split invite + accordion (buying-order questions) */
    .rv-svc-faq-layout{display:grid;grid-template-columns:minmax(0,.92fr) minmax(0,1.18fr);gap:clamp(1.75rem,4vw,3rem);align-items:start}
    .rv-svc-faq-copy .rv-section-title{margin:.35rem 0 .8rem}
    .rv-svc-faq-copy .rv-page-intro{margin:0}
    .rv-svc-faq-close{margin:1.1rem 0 0;max-width:36ch;color:var(--color-ink);font-size:1.02rem;line-height:1.5}
    .rv-svc-faq-actions{display:flex;flex-wrap:wrap;align-items:center;gap:.85rem 1.15rem;margin-top:1.35rem}
    .rv-svc-faq-more{display:inline-flex;align-items:center;gap:.4rem;font-weight:700;font-size:.9rem;color:var(--color-clay);text-decoration:none}
    .rv-svc-faq-more:hover{color:var(--color-pine)}
    .rv-svc-faq-more svg{width:15px;height:15px;transition:transform .2s ease}
    .rv-svc-faq-more:hover svg{transform:translateX(3px)}
    .rv-svc-faq .rv-faq{margin:0}
    .rv-svc-faq .rv-faq-item{border-radius:var(--radius-lg,16px)}
    .rv-svc-faq .rv-faq-item[open]{border-color:color-mix(in srgb,var(--color-clay) 38%,var(--color-line));box-shadow:var(--shadow-soft)}
    .rv-svc-faq .rv-faq-item summary{font-size:1.02rem;line-height:1.35;text-wrap:pretty}
    .rv-svc-faq .rv-faq-answer{font-size:1rem;line-height:1.65;max-width:52ch}
    @media(max-width:820px){.rv-svc-faq-layout{grid-template-columns:1fr}}
    @media(prefers-reduced-motion:reduce){.rv-svc-faq-more svg{transition:none}}
    /* Founding offer — inclusions as a scannable checklist (on the dark pine band) */
    .rv-founding-list{list-style:none;display:flex;flex-wrap:wrap;gap:.6rem;margin:1.5rem 0 0;padding:0}
    .rv-founding-list li{position:relative;background:rgba(255,255,255,.06);border:1px solid rgba(255,255,255,.15);border-radius:999px;padding:.5rem 1.05rem .5rem 2.1rem;color:#fff;font-weight:600;font-size:.92rem}
    .rv-founding-list li::before{content:"✓";position:absolute;left:.95rem;top:50%;transform:translateY(-50%);color:var(--color-wheat);font-weight:800}
    .rv-founding-after{margin:1.4rem 0 0;color:#d3ddcf;font-size:.95rem}
  </style>
@endsection
