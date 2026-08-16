{{--
  Template Name: Free Tools
  Field-driven hub. WordPress block / post content is not rendered.
--}}
@extends('layouts.app')

@php
$ctaHref = \App\cta_href(get_theme_mod('rv_cta_url', '/contact/'));
$pid     = get_the_ID();

$proof = \App\field_rows('tools_proof', \App\tools_proof_defaults(), $pid);
$picks = \App\field_rows('pick_items', \App\tools_pick_defaults(), $pid);
$filters = \App\field_rows('chk_filters', \App\tools_filter_defaults(), $pid);
$checkers = \App\field_rows('chk_items', \App\tools_checker_defaults(), $pid);
$how = \App\field_rows('howt_items', \App\tools_how_defaults(), $pid);
$privacy = \App\field_rows('priv_items', \App\tools_privacy_defaults(), $pid);
$next = \App\field_rows('nxt_items', \App\tools_next_defaults(), $pid);
$faqRows = \App\field_rows('tfq_items', \App\tools_faq_defaults(), $pid);

$faqs = [];
foreach ($faqRows as $row) {
  $q = \App\strip_field_markers((string) ($row['q'] ?? ''));
  $a = \App\strip_field_markers((string) ($row['a'] ?? ''));
  if ($q !== '' && $a !== '') {
    $faqs[] = [$q, $a];
  }
}

$openLabel = \App\field('chk_open', __('Open the tool', 'sage'));
$whyLabel  = \App\field('chk_why_label', __('Why it matters:', 'sage'));
@endphp

@section('content')
  <section class="rv-hero rv-hero--tools" aria-labelledby="rv-tools-hero-title">
    <span class="rv-stripe" aria-hidden="true"></span>
    @include('partials.hero-bg', ['fallback' => ''])
    <div class="rv-shell rv-hero-inner">
      {!! \App\eyebrow(\App\field('hero_eyebrow', __('Free website tools · No email required', 'sage'))) !!}
      <h1 id="rv-tools-hero-title" class="rv-hero-title">{{ \App\field('hero_title', __('Free tools to check your', 'sage')) }} <em class="rv-accent">{{ \App\field('hero_accent', __('website.', 'sage')) }}</em></h1>
      <p class="rv-hero-sub">{{ \App\field('hero_lede', __('Grade your site, check SEO, accessibility, and security — free, in seconds, no signup.', 'sage')) }}</p>
      <div class="rv-hero-actions">
        <a class="rv-btn {{ \App\hero_btn_class(\App\field('hero_btn1_style',''), 'rv-btn-primary') }}" href="#tools">{{ \App\field('hero_btn1', __('Browse the tools', 'sage')) }}</a>
        <a class="rv-btn {{ \App\hero_btn_class(\App\field('hero_btn2_style',''), 'rv-btn-ghost') }}" href="{{ $ctaHref }}">{{ \App\field('hero_btn2', __('Talk to me', 'sage')) }}</a>
      </div>
      <p class="rv-hero-note">{{ \App\field('hero_note', __('No account · No email · Instant results · Built in Gettysburg', 'sage')) }}</p>
    </div>
    @if (! empty($proof))
      <div class="rv-hero-proof">
        <div class="rv-shell">
          <ul class="rv-hero-stats" aria-label="{{ __('What you can count on', 'sage') }}">
            @foreach ($proof as $pf)
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

  <section class="rv-shell rv-band rv-tools-intro" aria-labelledby="rv-tools-intro-title">
    <h2 id="rv-tools-intro-title" class="screen-reader-text">{{ __('Why these tools exist', 'sage') }}</h2>
    <div class="rv-tools-intro-copy">
      <p>{{ \App\field('intro_p1', __('Your website is working for you around the clock — but is it actually doing its job? Most small-business owners have no easy way to tell whether their site loads fast enough, shows up on Google, works on a phone, or keeps visitors\' information safe.', 'sage')) }}</p>
      <p>{{ \App\field('intro_p2', __('These tools answer those questions in plain English, so you can see exactly where your site stands before you spend a dollar fixing it. No catch: every checker runs instantly, with no account and no sales call required.', 'sage')) }}</p>
    </div>
  </section>

  <section class="rv-band rv-band-alt rv-tools-pick" aria-labelledby="rv-tools-pick-title">
    <div class="rv-shell">
      <div class="rv-headstack">
        {!! \App\eyebrow(\App\field('pick_eyebrow', __('Not sure where to start?', 'sage'))) !!}
        <h2 id="rv-tools-pick-title" class="rv-section-title">{{ \App\field('pick_title', __('Pick the check that', 'sage')) }} <em class="rv-accent">{{ \App\field('pick_accent', __('matches the snag.', 'sage')) }}</em></h2>
        <p class="rv-page-intro">{{ \App\field('pick_intro', __('Three doors. One minute to choose. If you only run one thing today, make it the Website Grader.', 'sage')) }}</p>
      </div>
      <div class="rv-tools-pick-grid">
        @foreach ($picks as $i => $pick)
          <a class="rv-card rv-tools-pick-card" href="{{ \App\cta_href(\App\strip_field_markers((string) ($pick['url'] ?? '/website-grader/'))) }}">
            <span class="rv-tools-pick-n" aria-hidden="true">{{ $i + 1 }}</span>
            @if (($pick['kicker'] ?? '') !== '')<span class="rv-toolcard-badge">{{ $pick['kicker'] }}</span>@endif
            <h3>{{ $pick['title'] ?? '' }}</h3>
            <p>{{ $pick['text'] ?? '' }}</p>
            <span class="rv-readmore">{{ $pick['cta'] ?? $openLabel }} {!! \App\icon('arrow') !!}</span>
          </a>
        @endforeach
      </div>
    </div>
  </section>

  <section id="tools" class="rv-shell rv-band rv-tools-hub" aria-labelledby="rv-tools-hub-title">
    <div class="rv-headstack">
      {!! \App\eyebrow(\App\field('chk_eyebrow', __('The checkers', 'sage'))) !!}
      <h2 id="rv-tools-hub-title" class="rv-section-title">{{ \App\field('chk_title', __('Six instant', 'sage')) }} <em class="rv-accent">{{ \App\field('chk_accent', __('report cards.', 'sage')) }}</em></h2>
      <p class="rv-page-intro">{{ \App\field('chk_intro', __('Enter your URL and get a clear, color-coded grade with every check explained — and why it matters for your business.', 'sage')) }}</p>
      <ul class="rv-score-legend" aria-label="{{ __('How scores are colored', 'sage') }}">
        <li data-tone="pass">{{ \App\field('chk_legend_pass', __('Pass — already working', 'sage')) }}</li>
        <li data-tone="review">{{ \App\field('chk_legend_review', __('Review — worth a look', 'sage')) }}</li>
        <li data-tone="fail">{{ \App\field('chk_legend_fail', __('Fail — fix this first', 'sage')) }}</li>
      </ul>
    </div>

    @if (count($filters) > 1)
      <div class="rv-tool-filters" role="toolbar" aria-label="{{ __('Filter tools', 'sage') }}" data-rv-tool-filters>
        @foreach ($filters as $i => $f)
          @php($fkey = \App\strip_field_markers((string) ($f['key'] ?? 'all')))
          <button type="button" class="rv-mchip{{ $i === 0 ? ' is-on' : '' }}" data-filter="{{ $fkey }}" aria-pressed="{{ $i === 0 ? 'true' : 'false' }}">{{ $f['label'] ?? $fkey }}</button>
        @endforeach
      </div>
    @endif

    <div class="rv-toolhub" data-rv-toolhub>
      @foreach ($checkers as $t)
        <a class="rv-card rv-feature rv-toolcard{{ ((string) ($t['featured'] ?? '') === '1') ? ' is-featured' : '' }}" href="{{ \App\cta_href(\App\strip_field_markers((string) ($t['url'] ?? '/free-tools/'))) }}" data-group="{{ \App\strip_field_markers((string) ($t['group'] ?? 'search')) }}">
          <span class="rv-stripe rv-stripe-thin" aria-hidden="true"></span>
          <span class="rv-toolcard-meta">
            @if (($t['flag'] ?? '') !== '')<span class="rv-toolcard-badge">{{ $t['flag'] }}</span>@endif
            @if (($t['time'] ?? '') !== '')<span class="rv-toolcard-time">{{ $t['time'] }}</span>@endif
          </span>
          <h3 class="rv-feature-title">{{ $t['title'] ?? '' }}</h3>
          <p class="rv-feature-text">{{ $t['text'] ?? '' }}</p>
          @if (($t['why'] ?? '') !== '')
            <p class="rv-toolcard-why"><b>{{ $whyLabel }}</b> {{ $t['why'] }}</p>
          @endif
          <span class="rv-readmore">{{ $openLabel }} {!! \App\icon('arrow') !!}</span>
        </a>
      @endforeach
    </div>
  </section>

  <section id="quick" class="rv-band rv-band-alt" aria-labelledby="rv-tools-quick-title">
    <div class="rv-shell">
      <div class="rv-headstack">
        {!! \App\eyebrow(\App\field('quick_eyebrow', __('Quick tools', 'sage'))) !!}
        <h2 id="rv-tools-quick-title" class="rv-section-title">{{ \App\field('quick_title', __('Handy calculators &', 'sage')) }} <em class="rv-accent">{{ \App\field('quick_accent', __('checks.', 'sage')) }}</em></h2>
        <p class="rv-page-intro">{{ \App\field('quick_intro', __('No URL needed — these run right here on the page. Test a color combination, ballpark a project, or estimate a care plan.', 'sage')) }}</p>
      </div>
      <div class="rv-tools-grid">
        {!! \App\rv_render_contrast() !!}
        {!! \App\rv_render_estimator() !!}
        {!! \App\rv_render_calculator() !!}
      </div>
    </div>
  </section>

  <section class="rv-shell rv-band rv-tools-how" aria-labelledby="rv-tools-how-title">
    <div class="rv-headstack">
      {!! \App\eyebrow(\App\field('howt_eyebrow', __('Getting the most from them', 'sage'))) !!}
      <h2 id="rv-tools-how-title" class="rv-section-title">{{ \App\field('howt_title', __('How to use these', 'sage')) }} <em class="rv-accent">{{ \App\field('howt_accent', __('tools.', 'sage')) }}</em></h2>
      <p class="rv-page-intro">{{ \App\field('howt_intro', __('A four-step loop. You can stop after step one and still walk away smarter.', 'sage')) }}</p>
    </div>
    <ol class="rv-process" aria-label="{{ __('How to use these tools', 'sage') }}">
      @foreach ($how as $i => $step)
        <li class="rv-process-step">
          <span class="rv-process-node" aria-hidden="true">{{ $i + 1 }}</span>
          <h3 class="rv-process-t">{{ $step['title'] ?? '' }}</h3>
          <p class="rv-process-d">{{ $step['text'] ?? '' }}</p>
        </li>
      @endforeach
    </ol>
  </section>

  <section class="rv-band rv-band-alt rv-sec-cover" aria-labelledby="rv-tools-lim-title">
    <div class="rv-shell">
      {!! \App\eyebrow(\App\field('lim_eyebrow', __('Honest limits', 'sage'))) !!}
      <h2 id="rv-tools-lim-title" class="rv-section-title">{{ \App\field('lim_title', __('What a machine can', 'sage')) }} <em class="rv-accent">{{ \App\field('lim_accent', __('and can’t see.', 'sage')) }}</em></h2>
      <p class="rv-page-intro">{{ \App\field('lim_intro', __('Automated checks catch the concrete stuff that holds a local site back. They cannot judge whether the site says the right thing to the right person. A perfect technical score still loses if the message is wrong — that’s the work I finish by hand.', 'sage')) }}</p>
      <div class="rv-sec-bounds">
        <div class="rv-sec-bound">
          <h3>{{ \App\field('lim_yes_title', __('These tools look at', 'sage')) }}</h3>
          <ul>
            @foreach (\App\field_lines('lim_yes', \App\tools_cover_yes_defaults()) as $line)
              <li>{{ $line }}</li>
            @endforeach
          </ul>
        </div>
        <div class="rv-sec-bound rv-sec-bound-no">
          <h3>{{ \App\field('lim_no_title', __('They do not judge', 'sage')) }}</h3>
          <ul>
            @foreach (\App\field_lines('lim_no', \App\tools_cover_no_defaults()) as $line)
              <li>{{ $line }}</li>
            @endforeach
          </ul>
        </div>
      </div>
    </div>
  </section>

  <section class="rv-shell rv-band" aria-labelledby="rv-tools-priv-title">
    <div class="rv-headstack">
      {!! \App\eyebrow(\App\field('priv_eyebrow', __('No catch', 'sage'))) !!}
      <h2 id="rv-tools-priv-title" class="rv-section-title">{{ \App\field('priv_title', __('Built for local businesses,', 'sage')) }} <em class="rv-accent">{{ \App\field('priv_accent', __('given away for free.', 'sage')) }}</em></h2>
      <p class="rv-page-intro">{{ \App\field('priv_intro', __('I’m Matt Hummel, a Gettysburg-based web developer with 15+ years of experience. I built these because most small businesses in Adams County are paying for websites that quietly underperform — and they have no easy way to see it. A clear checkup shouldn’t cost anything.', 'sage')) }}</p>
    </div>
    <div class="rv-grid rv-grid-3 rv-tools-privacy">
      @foreach ($privacy as $item)
        <article class="rv-card rv-feature">
          <span class="rv-stripe rv-stripe-thin" aria-hidden="true"></span>
          <h3 class="rv-feature-title">{{ $item['title'] ?? '' }}</h3>
          <p class="rv-feature-text">{{ $item['text'] ?? '' }}</p>
        </article>
      @endforeach
    </div>
  </section>

  <section class="rv-band rv-band-alt" aria-labelledby="rv-tools-nxt-title">
    <div class="rv-shell">
      <div class="rv-headstack">
        {!! \App\eyebrow(\App\field('nxt_eyebrow', __('After the score', 'sage'))) !!}
        <h2 id="rv-tools-nxt-title" class="rv-section-title">{{ \App\field('nxt_title', __('Want the fixes, not just', 'sage')) }} <em class="rv-accent">{{ \App\field('nxt_accent', __('the report?', 'sage')) }}</em></h2>
        <p class="rv-page-intro">{{ \App\field('nxt_intro', __('Keep the results and DIY, or hand me the URL. Either way you already know more than you did ten minutes ago.', 'sage')) }}</p>
      </div>
      <div class="rv-grid rv-grid-3">
        @foreach ($next as $item)
          <a class="rv-card rv-feature rv-toolcard" href="{{ \App\cta_href(\App\strip_field_markers((string) ($item['url'] ?? '/contact/'))) }}">
            <span class="rv-stripe rv-stripe-thin" aria-hidden="true"></span>
            <h3 class="rv-feature-title">{{ $item['title'] ?? '' }}</h3>
            <p class="rv-feature-text">{{ $item['text'] ?? '' }}</p>
            <span class="rv-readmore">{{ $item['cta'] ?? __('Learn more', 'sage') }} {!! \App\icon('arrow') !!}</span>
          </a>
        @endforeach
      </div>
    </div>
  </section>

  <section class="rv-shell rv-band" aria-labelledby="rv-tools-faq-title">
    <div class="rv-reading">
      {!! \App\eyebrow(\App\field('tfq_eyebrow', __('Common questions', 'sage'))) !!}
      <h2 id="rv-tools-faq-title" class="rv-section-title" style="text-align:left">{{ \App\field('tfq_title', __('Free tools,', 'sage')) }} <em class="rv-accent">{{ \App\field('tfq_accent', __('answered.', 'sage')) }}</em></h2>
      <div class="rv-faq" style="margin-top:1.75rem">
        @foreach ($faqRows as $f)
          @if (($f['q'] ?? '') !== '')
            <details class="rv-faq-item">
              <summary>{{ $f['q'] }}</summary>
              <div class="rv-faq-answer"><p>{{ $f['a'] ?? '' }}</p></div>
            </details>
          @endif
        @endforeach
      </div>
    </div>
  </section>

  <section class="rv-cta-band">
    <div class="rv-shell rv-cta-inner">
      <h2 class="rv-cta-title">{{ \App\field('cta_title', __('Want a hand with what the tools turned up?', 'sage')) }}</h2>
      <p class="rv-cta-sub">{{ \App\field('cta_sub', __('I fix these for Gettysburg and South Central PA businesses every week. Tell me your site and I’ll take a real look.', 'sage')) }}</p>
      <a class="rv-btn rv-btn-on-dark" href="{{ $ctaHref }}">{{ \App\field('cta_button', get_theme_mod('rv_cta_text', __('Get a quote', 'sage'))) }}</a>
    </div>
  </section>

  {!! \App\faq_schema($faqs) !!}
@endsection
