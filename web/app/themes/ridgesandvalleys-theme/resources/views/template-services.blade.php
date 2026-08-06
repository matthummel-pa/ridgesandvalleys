{{--
  Template Name: Services
--}}
@extends('layouts.app')

@section('content')
  @php($ctaHref = \App\cta_href(get_theme_mod('rv_cta_url', '/contact/')))

  <section class="rv-hero">
    <span class="rv-stripe" aria-hidden="true"></span>
    @include('partials.hero-bg', ['fallback' => \App\stock_image('hero-services')])
    <div class="rv-shell rv-hero-inner">
      {!! \App\eyebrow(\App\field('hero_eyebrow', __('Web design & local SEO · Gettysburg', 'sage'))) !!}
      <h1 class="rv-hero-title">{{ \App\field('hero_title', __('Gettysburg web design that earns its', 'sage')) }} <em class="rv-accent">{{ \App\field('hero_accent', __('keep.', 'sage')) }}</em></h1>
      <p class="rv-hero-sub">{{ \App\field('hero_sub', __('Web design and local SEO packages for Gettysburg and Adams County businesses — each one built around a single outcome: more calls, more bookings, easier-to-find hours. Fixed scope, honest pricing, no jargon.', 'sage')) }}</p>
      <div class="rv-hero-actions">
        <a class="rv-btn rv-btn-primary" href="{{ $ctaHref }}">{{ \App\field('hero_btn1', __('Get a quote', 'sage')) }}</a>
        <a class="rv-btn rv-btn-ghost" href="#packages">{{ \App\field('hero_btn2', __('See the packages', 'sage')) }}</a>
      </div>
    </div>
  </section>

  {{-- PACKAGES --}}
  <section id="packages" class="rv-shell rv-band" style="scroll-margin-top:6rem">
    <div class="rv-svc-plans-head">
      {!! \App\eyebrow(\App\field('plans_eyebrow', __('Simple, fixed pricing', 'sage'))) !!}
      <h2 class="rv-section-title">{{ \App\field('plans_title', __('Pick the plan that fits', 'sage')) }} <em class="rv-accent">{{ \App\field('plans_accent', __('where you are.', 'sage')) }}</em></h2>
      <p class="rv-page-intro">{{ \App\field('plans_intro', __('Every package is one fixed price, agreed up front — no hourly meter, no surprise invoices. Start with a rescue, launch a full local site, or keep things growing after. Not sure which fits? Tell me about your business and I\'ll point you to the right one.', 'sage')) }}</p>
    </div>
    <div class="rv-svc-plans">
      @foreach (\App\field_rows('packages_items', [
        ['name' => __('Website Rescue', 'sage'), 'price' => __('$950–$1,500', 'sage'), 'flag' => '', 'desc' => __('For a site that\'s mostly there. Audit, content cleanup, broken links, mobile fixes, speed and SEO improvements.', 'sage'), 'features' => [__('Speed + mobile fixes', 'sage'), __('Refreshed design + content', 'sage'), __('Accessibility + SEO cleanup', 'sage')]],
        ['name' => __('Local Launch', 'sage'), 'price' => __('$2,750–$3,750', 'sage'), 'flag' => __('Most popular', 'sage'), 'desc' => __('Up to 5 pages, contact form, local SEO foundations, analytics, accessibility review, one revision.', 'sage'), 'features' => [__('Custom design + copy', 'sage'), __('Google Business Profile setup', 'sage'), __('Launch in 7–10 days', 'sage')]],
        ['name' => __('Growth Site', 'sage'), 'price' => __('$4,500+', 'sage'), 'flag' => '', 'desc' => __('8–12 pages, content migration, booking or ecommerce integration, advanced forms and stronger SEO.', 'sage'), 'features' => [__('Everything in Local Launch', 'sage'), __('Nearby-town pages', 'sage'), __('Blog + lead capture', 'sage')]],
        ['name' => __('Care & Grow', 'sage'), 'price' => __('$179–$349/mo', 'sage'), 'flag' => '', 'desc' => __('Updates, backups, security checks, small content changes, reporting, priority support.', 'sage'), 'features' => [__('Managed hosting + backups', 'sage'), __('Monthly edits + updates', 'sage'), __('Ongoing SEO + reporting', 'sage')]],
      ]) as $s)
        @php($isFeatured = trim(\App\strip_field_markers((string) ($s['flag'] ?? ''))) !== '')
        <article class="rv-card rv-service rv-plan{{ $isFeatured ? ' is-featured' : '' }}">
          <span class="rv-stripe rv-stripe-thin" aria-hidden="true"></span>
          @if ($isFeatured)<span class="rv-plan-flag">{{ $s['flag'] }}</span>@endif
          <div class="rv-service-body">
            <h2 class="rv-service-name">{{ $s['name'] ?? '' }}</h2>
            <p class="rv-service-tag">{{ $s['price'] ?? '' }}</p>
            <p class="rv-service-desc">{{ $s['desc'] ?? '' }}</p>
            <ul class="rv-service-list">
              @foreach (\App\lines($s['features'] ?? []) as $item)<li>{{ $item }}</li>@endforeach
            </ul>
            <a class="rv-btn {{ $isFeatured ? 'rv-btn-primary' : 'rv-btn-ghost' }} rv-plan-btn" href="{{ $ctaHref }}">{{ __('Get a quote', 'sage') }}</a>
          </div>
        </article>
      @endforeach
    </div>

    <div class="rv-svc-included-head">
      {!! \App\eyebrow(\App\field('svc_incl_eyebrow', __('The fine print, up front', 'sage'))) !!}
      <h2 class="rv-section-title">{{ \App\field('svc_incl_title', __('What every build', 'sage')) }} <em class="rv-accent">{{ \App\field('svc_incl_accent', __('includes.', 'sage')) }}</em></h2>
    </div>
    <div class="rv-svc-included">
      @foreach (\App\field_rows('svc_detail_items', [
        ['title' => __('What\'s always included', 'sage'), 'text' => __('Accessibility-first build, mobile-first layout, local SEO basics, analytics, and a training handoff.', 'sage')],
        ['title' => __('What we don\'t do (yet)', 'sage'), 'text' => __('No paid ads, no full social management, no giant content retainers. We do websites, well.', 'sage')],
        ['title' => __('The guardrails', 'sage'), 'text' => __('One decision-maker, one consolidated revision round, feedback within two business days, final payment before launch.', 'sage')],
      ]) as $i => $d)
        <article class="rv-card rv-feature rv-incl">
          <span class="rv-incl-num" aria-hidden="true">{{ sprintf('%02d', $i + 1) }}</span>
          <h3 class="rv-feature-title">{{ $d['title'] ?? '' }}</h3>
          <p class="rv-feature-text">{{ $d['text'] ?? '' }}</p>
        </article>
      @endforeach
    </div>
  </section>

  {{-- LOCAL SEO (bold, dark) --}}
  <section class="rv-band rv-band-pine rv-svc-seo">
    <div class="rv-shell">
      {!! \App\eyebrow(\App\field('seo_eyebrow', __('Get found in Gettysburg', 'sage'))) !!}
      <h2 class="rv-section-title">{{ \App\field('seo_title', __('Local SEO that puts you', 'sage')) }} <em class="rv-accent">{{ \App\field('seo_accent', __('on the map.', 'sage')) }}</em></h2>
      <p class="rv-hero-sub" style="max-width:74ch">{!! \App\field('seo_intro', __('<strong>Local SEO decides whether they find you.</strong> When a neighbor or visitor reaches for their phone and searches “web designer near me,” “best breakfast in Gettysburg,” or “plumber in Adams County,” you either show up — or the competitor three listings up does. It\'s the difference between a website that just exists and one that actually brings people through your door.', 'sage')) !!}</p>

      <div class="rv-svc-seo-grid">
        @foreach (\App\field_rows('seo_items', [
          ['title' => __('Google Business Profile', 'sage'), 'text' => __('Claimed, verified, and optimized — categories, hours, photos, and posts that help you show up in Maps and the local pack.', 'sage')],
          ['title' => __('Local keyword research', 'sage'), 'text' => __('The exact terms people around here actually type — by town, by service, by season — built right into your pages.', 'sage')],
          ['title' => __('Location & service-area pages', 'sage'), 'text' => __('Dedicated, indexable pages for Gettysburg and the nearby towns you serve, so you can rank in more than one place.', 'sage')],
          ['title' => __('LocalBusiness schema', 'sage'), 'text' => __('Structured data that tells Google exactly what you are, where you are, and what you offer.', 'sage')],
          ['title' => __('NAP consistency', 'sage'), 'text' => __('Your name, address, and phone matched across your site and listings — the signal local search trusts most.', 'sage')],
          ['title' => __('Reviews that show up', 'sage'), 'text' => __('A simple way to earn reviews and display them, so new customers see the proof before they ever call.', 'sage')],
          ['title' => __('Built for the map pack', 'sage'), 'text' => __('Optimized to compete for the coveted top-three local results — not buried on page two.', 'sage')],
          ['title' => __('Fast & mobile-first', 'sage'), 'text' => __('Local search happens on phones. A site that loads instantly keeps you in the running.', 'sage')],
        ]) as $item)
          <div class="rv-svc-seo-item">
            <h3>{{ $item['title'] ?? '' }}</h3>
            <p>{{ $item['text'] ?? '' }}</p>
          </div>
        @endforeach
      </div>
      <p class="rv-svc-seo-note">{{ \App\field('seo_note', __('Baked into every Local Launch and Growth Site — and available as a standalone tune-up for a site you already have.', 'sage')) }}</p>
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

  {{-- HELPFUL TO KNOW (FAQ) --}}
  <section class="rv-shell rv-band rv-svc-faq">
    {!! \App\eyebrow(\App\field('sfaq_eyebrow', __('Helpful to know', 'sage'))) !!}
    <h2 class="rv-section-title">{{ \App\field('sfaq_title', __('Answers before you', 'sage')) }} <em class="rv-accent">{{ \App\field('sfaq_accent', __('even ask.', 'sage')) }}</em></h2>
    <div class="rv-svc-faq-grid">
      @foreach (\App\field_rows('sfaq_items', [
        ['q' => __('Do I own my website?', 'sage'), 'a' => __('Completely. The domain, the hosting, and every word and pixel are in your name. Want to move it or hand it to someone else someday? It\'s yours to take.', 'sage')],
        ['q' => __('How fast can it launch?', 'sage'), 'a' => __('Most local sites go live in 7–10 days once I have your content and assets. Bigger builds take a little longer — and you\'ll know the timeline before we start.', 'sage')],
        ['q' => __('What if I need changes later?', 'sage'), 'a' => __('You get one consolidated revision round during the build. After launch, a Care & Grow plan covers ongoing edits, or you can request changes as you need them.', 'sage')],
        ['q' => __('Do you handle hosting and domains?', 'sage'), 'a' => __('Yes — I set everything up in your name and can manage it for you, or hand you the keys. Either way, you\'re never locked in.', 'sage')],
        ['q' => __('What areas do you serve?', 'sage'), 'a' => __('Gettysburg, Adams County, and across South Central PA — Biglerville, Littlestown, New Oxford, Hanover, and beyond. Farther out? Most of the work happens over a call and a shared screen.', 'sage')],
        ['q' => __('How does payment work?', 'sage'), 'a' => __('A fixed price agreed up front, a deposit to start, and the balance before launch. No surprise invoices, no hourly meter running.', 'sage')],
        ['q' => __('Will my site be accessible?', 'sage'), 'a' => __('Always. Every build is WCAG-minded and tested on real devices, so it works for everyone — and accessible sites tend to rank better, too.', 'sage')],
        ['q' => __('Can you fix my current site instead?', 'sage'), 'a' => __('Absolutely. That\'s the Website Rescue — an audit and targeted fixes for speed, mobile, accessibility, and SEO without a full rebuild.', 'sage')],
      ]) as $f)
        <div class="rv-svc-faq-item">
          <h3>{{ $f['q'] ?? '' }}</h3>
          <p>{{ $f['a'] ?? '' }}</p>
        </div>
      @endforeach
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

  <style>
    /* Plans — pricing grid (4-up, no orphan card) */
    .rv-svc-plans{display:grid;grid-template-columns:repeat(4,1fr);gap:1.15rem;margin-top:2.25rem;align-items:stretch}
    @media(max-width:1120px){.rv-svc-plans{grid-template-columns:repeat(2,1fr)}}
    @media(max-width:560px){.rv-svc-plans{grid-template-columns:1fr}}
    .rv-svc-plans .rv-plan{position:relative;display:flex;flex-direction:column;padding:0;overflow:hidden;border-radius:var(--radius-lg,18px);transition:transform .18s ease,box-shadow .2s ease,border-color .18s ease}
    .rv-svc-plans .rv-plan:hover{transform:translateY(-4px);box-shadow:var(--shadow-lift)}
    .rv-svc-plans .rv-service-body{display:flex;flex-direction:column;flex:1 1 auto;padding:1.7rem 1.55rem 1.6rem}
    .rv-svc-plans .rv-service-name{font-family:var(--font-display);font-weight:800;font-size:1.22rem;color:var(--color-ink);margin:0;line-height:1.15}
    .rv-svc-plans .rv-service-tag{font-family:var(--font-display);font-weight:800;font-size:1.5rem;letter-spacing:-.02em;color:var(--color-clay);margin:.3rem 0 0;text-transform:none}
    .rv-svc-plans .rv-service-desc{color:var(--color-body);font-size:.92rem;line-height:1.55;margin:1rem 0 0;padding-top:1rem;border-top:1px solid var(--color-line)}
    .rv-svc-plans .rv-service-list{list-style:none;margin:1rem 0 1.6rem;padding:0;display:grid;gap:.55rem}
    .rv-svc-plans .rv-service-list li{position:relative;padding-left:1.65rem;font-size:.92rem;color:var(--color-body);line-height:1.4}
    .rv-svc-plans .rv-service-list li::before{content:"";position:absolute;left:0;top:.05em;width:1.1rem;height:1.1rem;border-radius:50%;background:color-mix(in srgb,var(--color-sage) 26%,transparent)}
    .rv-svc-plans .rv-service-list li::after{content:"";position:absolute;left:.34rem;top:.4em;width:.42rem;height:.22rem;border-left:2px solid var(--color-pine);border-bottom:2px solid var(--color-pine);transform:rotate(-45deg)}
    .rv-plan-btn{margin-top:auto;width:100%;justify-content:center;text-align:center}
    /* Featured plan */
    .rv-svc-plans .rv-plan.is-featured{border:1.5px solid var(--color-pine);box-shadow:0 12px 34px color-mix(in srgb,var(--color-pine) 20%,transparent)}
    .rv-svc-plans .rv-plan.is-featured:hover{transform:translateY(-6px)}
    .rv-plan-flag{position:absolute;top:0;right:0;z-index:3;background:var(--color-pine);color:#fff;font-family:var(--font-mono);font-size:.64rem;letter-spacing:.09em;text-transform:uppercase;font-weight:700;padding:.35rem .7rem;border-bottom-left-radius:12px}
    html[data-theme="dark"] .rv-svc-plans .rv-plan.is-featured{border-color:var(--color-sage)}
    /* What every build includes */
    .rv-svc-included-head{margin-top:clamp(3rem,6vw,4.75rem)}
    .rv-svc-included{display:grid;grid-template-columns:repeat(3,1fr);gap:1.15rem;margin-top:1.75rem}
    @media(max-width:820px){.rv-svc-included{grid-template-columns:1fr}}
    .rv-svc-included .rv-incl{position:relative;padding:1.7rem 1.6rem 1.55rem;border-radius:var(--radius-lg,16px);overflow:hidden;transition:transform .15s ease,box-shadow .2s ease,border-color .15s ease}
    .rv-svc-included .rv-incl::before{content:"";position:absolute;left:0;top:0;bottom:0;width:5px;background:var(--ridgeline)}
    .rv-svc-included .rv-incl:hover{transform:translateY(-3px);box-shadow:var(--shadow-soft);border-color:color-mix(in srgb,var(--color-sage) 55%,var(--color-line))}
    .rv-svc-included .rv-incl-num{font-family:var(--font-display);font-weight:800;font-size:1.05rem;color:color-mix(in srgb,var(--color-clay) 60%,transparent);letter-spacing:.02em}
    .rv-svc-included .rv-feature-title{margin:.35rem 0 .5rem;font-size:1.16rem}
    .rv-svc-included .rv-feature-text{margin:0;color:var(--color-body);line-height:1.6}
    /* Local SEO (on the always-dark pine band) */
    .rv-svc-seo-grid{display:grid;grid-template-columns:repeat(auto-fit,minmax(240px,1fr));gap:1rem;margin-top:2.25rem}
    .rv-svc-seo-item{position:relative;background:rgba(255,255,255,.055);border:1px solid rgba(255,255,255,.13);border-radius:var(--radius-lg,16px);padding:1.3rem 1.35rem 1.3rem 1.6rem;overflow:hidden;transition:transform .15s ease,background-color .15s ease}
    .rv-svc-seo-item:hover{transform:translateY(-2px);background:rgba(255,255,255,.08)}
    .rv-svc-seo-item::before{content:"";position:absolute;left:0;top:0;bottom:0;width:5px;background:var(--ridgeline)}
    .rv-svc-seo-item h3{font-family:var(--font-display);font-size:1.05rem;font-weight:700;color:#fff;margin:0 0 .35rem}
    .rv-svc-seo-item p{margin:0;color:#d3ddcf;font-size:.93rem;line-height:1.55}
    .rv-svc-seo-note{margin-top:1.6rem;font-family:var(--font-mono);font-size:.8rem;letter-spacing:.03em;color:var(--color-wheat)}
    /* Helpful-to-know FAQ (light band, flips in dark mode) */
    .rv-svc-faq-grid{display:grid;grid-template-columns:repeat(auto-fit,minmax(300px,1fr));gap:1.1rem;margin-top:2rem}
    .rv-svc-faq-item{background:var(--color-surface);border:1px solid var(--color-line);border-radius:var(--radius-lg,16px);padding:1.35rem 1.5rem;transition:border-color .15s ease}
    .rv-svc-faq-item:hover{border-color:var(--color-sage)}
    .rv-svc-faq-item h3{font-family:var(--font-display);font-size:1.1rem;font-weight:700;color:var(--color-ink);margin:0 0 .4rem}
    .rv-svc-faq-item p{margin:0;color:var(--color-body);font-size:.96rem;line-height:1.6}
    /* Founding offer — inclusions as a scannable checklist (on the dark pine band) */
    .rv-founding-list{list-style:none;display:flex;flex-wrap:wrap;gap:.6rem;margin:1.5rem 0 0;padding:0}
    .rv-founding-list li{position:relative;background:rgba(255,255,255,.06);border:1px solid rgba(255,255,255,.15);border-radius:999px;padding:.5rem 1.05rem .5rem 2.1rem;color:#fff;font-weight:600;font-size:.92rem}
    .rv-founding-list li::before{content:"✓";position:absolute;left:.95rem;top:50%;transform:translateY(-50%);color:var(--color-wheat);font-weight:800}
    .rv-founding-after{margin:1.4rem 0 0;color:#d3ddcf;font-size:.95rem}
  </style>
@endsection
