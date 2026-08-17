{{--
  Template Name: Contact
--}}
@extends('layouts.app')

@php
$cEmail = \App\contact_display_email();
$cPhone = trim((string) \App\field('contact_phone', ''));
$cPhoneHref = $cPhone ? preg_replace('/[^0-9+]/', '', $cPhone) : '';
$cHours = \App\field('contact_hours', __('Mon–Fri, 9am–5pm · evenings by appointment', 'sage'));
$toolsHref = home_url('/website-grader/');

$svgForm  = '<svg viewBox="0 0 24 24" width="24" height="24" fill="none" stroke="currentColor" stroke-width="1.6" stroke-linecap="round" stroke-linejoin="round" aria-hidden="true"><path d="M8 4H6a2 2 0 0 0-2 2v13a2 2 0 0 0 2 2h12a2 2 0 0 0 2-2V6a2 2 0 0 0-2-2h-2"/><rect x="8" y="2.5" width="8" height="4" rx="1"/><path d="M8 11h8M8 15h5"/></svg>';
$svgMail  = '<svg viewBox="0 0 24 24" width="24" height="24" fill="none" stroke="currentColor" stroke-width="1.6" stroke-linecap="round" stroke-linejoin="round" aria-hidden="true"><rect x="3" y="5" width="18" height="14" rx="2"/><path d="m4 7 8 6 8-6"/></svg>';
$svgPhone = '<svg viewBox="0 0 24 24" width="24" height="24" fill="none" stroke="currentColor" stroke-width="1.6" stroke-linecap="round" stroke-linejoin="round" aria-hidden="true"><path d="M6.6 10.8a12 12 0 0 0 5.6 5.6l1.9-1.9a1 1 0 0 1 1-.24 11 11 0 0 0 3.5.56 1 1 0 0 1 1 1V19a1 1 0 0 1-1 1A16 16 0 0 1 4 5a1 1 0 0 1 1-1h3.2a1 1 0 0 1 1 1c0 1.2.2 2.4.56 3.5a1 1 0 0 1-.24 1z"/></svg>';
$svgPin   = '<svg viewBox="0 0 24 24" width="20" height="20" fill="none" stroke="currentColor" stroke-width="1.6" stroke-linecap="round" stroke-linejoin="round" aria-hidden="true"><path d="M12 21s7-6.3 7-11a7 7 0 1 0-14 0c0 4.7 7 11 7 11z"/><circle cx="12" cy="10" r="2.5"/></svg>';
$svgClock = '<svg viewBox="0 0 24 24" width="20" height="20" fill="none" stroke="currentColor" stroke-width="1.6" stroke-linecap="round" stroke-linejoin="round" aria-hidden="true"><circle cx="12" cy="12" r="9"/><path d="M12 7v5l3 2"/></svg>';
@endphp

@section('content')
  <section class="rv-hero rv-contact-hero" aria-labelledby="rv-contact-hero-title">
    <span class="rv-stripe" aria-hidden="true"></span>
    @include('partials.hero-bg', ['fallback' => ''])
    <div class="rv-shell rv-hero-inner">
      {!! \App\eyebrow(\App\field('cnt_kicker', __('Gettysburg web design · let’s talk', 'sage'))) !!}
      <h1 id="rv-contact-hero-title" class="rv-hero-title">{{ \App\field('cnt_h1', __('Tell me about the', 'sage')) }} <em class="rv-accent">{{ \App\field('cnt_h1_accent', __('business.', 'sage')) }}</em></h1>
      <p class="rv-hero-sub">{{ \App\field('cnt_lede', __('A new site, a second look at the one you have, or just hello — I’ll read it and reply. Usually within a business day. No jargon, no pressure.', 'sage')) }}</p>
      <div class="rv-hero-actions">
        <a class="rv-btn rv-btn-primary" href="#contact-form">{{ \App\field('cnt_cta', __('Request a quote', 'sage')) }}</a>
        <a class="rv-btn rv-btn-ghost" href="mailto:{{ esc_attr($cEmail) }}">{{ \App\field('cnt_cta2', __('Or email me', 'sage')) }}</a>
      </div>
      <p class="rv-hero-note">{{ \App\field('cnt_note', __('A real person answers · Fixed price · You own the site', 'sage')) }}</p>
    </div>
    @php($cntProof = \App\contact_proof())
    @if (! empty($cntProof))
      <div class="rv-hero-proof">
        <div class="rv-shell">
          <ul class="rv-hero-stats" aria-label="{{ __('At a glance', 'sage') }}">
            @foreach ($cntProof as $pf)
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

  {{-- Under-hero: inviting, not pitchy — quote, not-sure, and hello. --}}
  <section class="rv-band rv-band-alt rv-cnt-why" aria-labelledby="rv-cnt-why-title">
    <div class="rv-shell rv-cnt-why-grid">
      <div class="rv-cnt-why-copy">
        {!! \App\eyebrow(\App\field('cnt_why_eyebrow', __('How this works', 'sage'))) !!}
        <h2 id="rv-cnt-why-title" class="rv-section-title">{{ \App\field('cnt_why_title', __('A conversation,', 'sage')) }} <em class="rv-accent">{{ \App\field('cnt_why_accent', __('not a pitch.', 'sage')) }}</em></h2>
        <p class="rv-page-intro">{!! \App\field('cnt_why_intro', __('<strong>I’m a one-person studio in Gettysburg.</strong> Quotes are welcome. So are introductions, partnerships, and “I’m not sure yet.” Fill in what you can — skip what you can’t. I’ll still read it.', 'sage')) !!}</p>
        <div class="rv-cnt-why-actions">
          <a class="rv-btn rv-btn-ghost" href="#contact-form">{{ \App\field('cnt_why_jump', __('Request a quote', 'sage')) }}</a>
          <a class="rv-cnt-why-jump" href="mailto:{{ esc_attr($cEmail) }}">{{ \App\field('cnt_why_email', __('Or just email me', 'sage')) }} {!! \App\icon('arrow') !!}</a>
        </div>
      </div>
      <div class="rv-cnt-why-list">
        @foreach (\App\field_rows('cnt_why_items', \App\contact_why_item_defaults()) as $v)
          <article class="rv-cnt-why-item">
            @if (($v['kicker'] ?? '') !== '')<span class="rv-cnt-why-kicker">{{ $v['kicker'] }}</span>@endif
            @if (($v['title'] ?? '') !== '')<h3>{{ $v['title'] }}</h3>@endif
            @if (($v['text'] ?? '') !== '')<p>{{ $v['text'] }}</p>@endif
          </article>
        @endforeach
      </div>
    </div>
  </section>

  {{-- Paths: quote, email, call or tools. --}}
  <section class="rv-shell rv-cways" aria-label="{{ __('Ways to get in touch', 'sage') }}">
    <a class="rv-cway rv-cway-primary" href="#contact-form">
      <span class="rv-cway-ico">{!! $svgForm !!}</span>
      <span class="rv-cway-body"><span class="rv-cway-t">{{ \App\field('cnt_path_quote', __('Request a quote', 'sage')) }}</span><span class="rv-cway-d">{{ \App\field('cnt_path_quote_d', __('The form below — a few details, a real plan back.', 'sage')) }}</span></span>
    </a>
    <a class="rv-cway" href="mailto:{{ esc_attr($cEmail) }}">
      <span class="rv-cway-ico">{!! $svgMail !!}</span>
      <span class="rv-cway-body"><span class="rv-cway-t">{{ \App\field('cnt_path_email', __('Email me directly', 'sage')) }}</span><span class="rv-cway-d">{{ $cEmail }}</span></span>
    </a>
    @if ($cPhone)
      <a class="rv-cway" href="tel:{{ $cPhoneHref }}">
        <span class="rv-cway-ico">{!! $svgPhone !!}</span>
        <span class="rv-cway-body"><span class="rv-cway-t">{{ \App\field('cnt_path_call', __('Call or text', 'sage')) }}</span><span class="rv-cway-d">{{ $cPhone }}</span></span>
      </a>
    @endif
    <a class="rv-cway" href="{{ $toolsHref }}">
      <span class="rv-cway-ico">{!! $svgClock !!}</span>
      <span class="rv-cway-body"><span class="rv-cway-t">{{ \App\field('cnt_path_tools', __('Grade your site first', 'sage')) }}</span><span class="rv-cway-d">{{ \App\field('cnt_path_tools_d', __('Free, no signup — look around, then decide.', 'sage')) }}</span></span>
    </a>
  </section>

  {{-- Quote form + what happens next --}}
  <section class="rv-shell rv-band rv-contact-main" id="contact-form">
    <div class="rv-contact-grid">
      <div class="rv-contact-formcol">
        @php($formStatus = isset($_GET['contact']) ? sanitize_key(wp_unslash($_GET['contact'])) : '')
        @if ($formStatus !== 'success')
          {!! \App\eyebrow(\App\field('cnt_form_eyebrow', __('Quote request', 'sage'))) !!}
          <h2 class="rv-section-title" style="margin-top:.4rem">{{ \App\field('cnt_form_title', __('A few details.', 'sage')) }} <em class="rv-accent">{{ \App\field('cnt_form_accent', __('That’s enough.', 'sage')) }}</em></h2>
          <p class="rv-page-intro rv-cnt-form-intro">{{ \App\field('cnt_form_intro', __('Skip anything you’re not sure about. Required fields are name, email, what you need, and what a win would look like.', 'sage')) }}</p>
        @endif
        <div class="rv-contact-wrap">@php(\App\contact_form())</div>
        @if ($formStatus !== 'success')
          <p class="rv-cnt-fine">{{ \App\field('cnt_form_fine', __('No mailing list. A real reply from Matt — usually within a business day.', 'sage')) }}</p>
        @endif
      </div>

      <div class="rv-contact-side">
        <div class="rv-cside-block">
          {!! \App\eyebrow(\App\field('cnt_next_eyebrow', __('What happens next', 'sage'))) !!}
          <ol class="rv-cnext">
            @foreach (\App\field_rows('cnt_next', \App\contact_next_defaults()) as $i => $cn)
              <li><span class="rv-cnext-n">{{ $i + 1 }}</span><div><strong>{{ $cn['strong'] ?? '' }}</strong> {{ $cn['text'] ?? '' }}</div></li>
            @endforeach
          </ol>
        </div>

        <div class="rv-cside-block rv-cside-open">
          {!! \App\eyebrow(\App\field('cnt_open_eyebrow', __('Also here for', 'sage'))) !!}
          <p class="rv-cside-open-text">{{ \App\field('cnt_open_text', __('Introductions, referrals, and a coffee around Gettysburg. If you’re another designer or a local group looking to collaborate, say hello — I’m easy to reach.', 'sage')) }}</p>
        </div>

        <div class="rv-cside-block rv-cside-contact">
          {!! \App\eyebrow(\App\field('creach_eyebrow', __('Reach me directly', 'sage'))) !!}
          <ul class="rv-cside-list">
            <li><span class="rv-cside-ico">{!! $svgMail !!}</span><a href="mailto:{{ esc_attr($cEmail) }}">{{ $cEmail }}</a></li>
            @if ($cPhone)<li><span class="rv-cside-ico">{!! $svgPhone !!}</span><a href="tel:{{ $cPhoneHref }}">{{ $cPhone }}</a></li>@endif
            <li><span class="rv-cside-ico">{!! $svgClock !!}</span><span>{{ $cHours }}</span></li>
            <li><span class="rv-cside-ico">{!! $svgPin !!}</span><span>{{ \App\field('creach_location', __('Gettysburg, PA · serving Adams County & South Central PA', 'sage')) }}</span></li>
          </ul>
        </div>
      </div>
    </div>
  </section>

  {{-- LOCAL SEO — SERVICE AREA --}}
  <section class="rv-band rv-band-alt rv-contact-local">
    <div class="rv-shell">
      <div class="rv-headstack">
        {!! \App\eyebrow(\App\field('clocal_eyebrow', __('A Gettysburg web designer', 'sage'))) !!}
        <h2 class="rv-section-title" style="margin-top:.4rem">{{ \App\field('clocal_title', __('Close by, and easy to', 'sage')) }} <em class="rv-accent">{{ \App\field('clocal_accent', __('reach.', 'sage')) }}</em></h2>
        <p class="rv-page-intro">{!! \App\field('clocal_intro', __('<strong>An independent, one-person studio in Gettysburg.</strong> That means real meetings when you want them, quick answers, and a site built by someone who actually knows the towns your customers come from. I work with restaurants, inns, shops, tradespeople, tour operators, realtors, and nonprofits across Adams County and South Central PA.', 'sage')) !!}</p>
      </div>
      <div class="rv-clocal-towns" aria-label="{{ __('Towns served', 'sage') }}">
        @foreach (\App\field_lines('clocal_towns', ['Gettysburg', 'Biglerville', 'Littlestown', 'New Oxford', 'McSherrystown', 'Bonneauville', 'Fairfield', 'Cashtown', 'Abbottstown', 'East Berlin', 'Hanover', 'York Springs']) as $t)<span class="rv-clocal-town">{{ $t }}</span>@endforeach
      </div>
      <p class="rv-page-intro" style="margin-top:1.75rem">{{ \App\field('clocal_note', __('Not right in the neighborhood? No problem — plenty of projects run entirely over a call and a shared screen. Wherever you are, you get the same fixed price, the same accessibility-first build, and full ownership at the end.', 'sage')) }}</p>
      <div style="display:flex;gap:.85rem;flex-wrap:wrap;margin-top:1.5rem">
        <a class="rv-btn rv-btn-primary" href="#contact-form">{{ \App\field('clocal_btn1', __('Request a quote', 'sage')) }}</a>
        <a class="rv-btn rv-btn-ghost" href="mailto:{{ esc_attr($cEmail) }}">{{ \App\field('clocal_btn2', __('Or just email me', 'sage')) }}</a>
      </div>
    </div>
  </section>

  @php($contactSchema = [
    '@context' => 'https://schema.org',
    '@type' => 'ProfessionalService',
    'name' => get_bloginfo('name'),
    'description' => 'Local web design, local SEO, and accessible WordPress websites for small businesses in Gettysburg and Adams County, PA.',
    'url' => home_url('/'),
    'email' => $cEmail,
    'image' => \App\stock_image('about'),
    'priceRange' => '$$',
    'address' => ['@type' => 'PostalAddress', 'addressLocality' => 'Gettysburg', 'addressRegion' => 'PA', 'addressCountry' => 'US'],
    'areaServed' => ['Gettysburg PA', 'Adams County PA', 'South Central Pennsylvania', 'Hanover PA', 'Littlestown PA', 'New Oxford PA', 'Biglerville PA'],
    'knowsAbout' => ['Web design', 'Local SEO', 'WordPress', 'Web accessibility', 'Google Business Profile'],
  ])
  @if ($cPhone)@php($contactSchema['telephone'] = $cPhone)@endif
  <script type="application/ld+json">{!! wp_json_encode($contactSchema, JSON_UNESCAPED_SLASHES | JSON_UNESCAPED_UNICODE) !!}</script>

  <style>
    #contact-form{scroll-margin-top:6rem}
    .rv-cnt-why{padding-block:clamp(2.25rem,4.5vw,3.5rem)}
    .rv-cnt-why-grid{display:grid;grid-template-columns:minmax(0,1fr) minmax(0,1.08fr);gap:clamp(1.5rem,4vw,2.75rem);align-items:start}
    .rv-cnt-why-copy .rv-section-title{margin:.35rem 0 .8rem}
    .rv-cnt-why-copy .rv-page-intro{margin:0}
    .rv-cnt-why-actions{display:flex;flex-wrap:wrap;align-items:center;gap:.85rem 1.25rem;margin-top:1.25rem}
    .rv-cnt-why-jump{display:inline-flex;align-items:center;gap:.4rem;font-weight:700;font-size:.9rem;color:var(--color-clay);text-decoration:none}
    .rv-cnt-why-jump:hover{color:var(--color-pine)}
    .rv-cnt-why-jump svg{width:15px;height:15px;transition:transform .2s ease}
    .rv-cnt-why-jump:hover svg{transform:translateX(3px)}
    .rv-cnt-why-list{display:grid;gap:.8rem}
    .rv-cnt-why-item{position:relative;background:var(--color-surface);border:1px solid var(--color-line);border-radius:var(--radius-lg,16px);padding:1.1rem 1.3rem 1.1rem 1.5rem;overflow:hidden}
    .rv-cnt-why-item::before{content:"";position:absolute;left:0;top:0;bottom:0;width:4px;background:var(--ridgeline)}
    .rv-cnt-why-kicker{display:block;font-family:var(--font-mono);font-size:.68rem;letter-spacing:.1em;text-transform:uppercase;color:var(--color-clay)}
    .rv-cnt-why-item h3{font-family:var(--font-display);font-size:1.12rem;font-weight:700;color:var(--color-ink);margin:.2rem 0 .35rem;line-height:1.2}
    .rv-cnt-why-item p{margin:0;color:var(--color-body);font-size:.95rem;line-height:1.55}
    @media(max-width:820px){.rv-cnt-why-grid{grid-template-columns:1fr}}
    .rv-cways{display:grid;grid-template-columns:repeat(auto-fit,minmax(240px,1fr));gap:1rem;margin-top:clamp(1.5rem,3vw,2.5rem);position:relative;z-index:3}
    .rv-cway{display:flex;align-items:center;gap:1rem;background:var(--color-surface);border:1px solid var(--color-line);border-radius:var(--radius-lg,16px);padding:1.25rem 1.35rem;text-decoration:none;position:relative;overflow:hidden;transition:transform .15s ease,border-color .15s ease,box-shadow .2s ease}
    a.rv-cway:hover{transform:translateY(-3px);border-color:color-mix(in srgb,var(--color-clay) 40%,var(--color-line));box-shadow:var(--shadow-lift)}
    .rv-cway-primary{background:var(--color-pine);border-color:var(--color-pine)}
    .rv-cway-primary .rv-cway-t,.rv-cway-primary .rv-cway-d{color:#fff}
    .rv-cway-primary .rv-cway-ico{background:rgba(255,255,255,.14);color:#fff}
    .rv-cway-ico{flex:none;width:48px;height:48px;border-radius:12px;display:inline-flex;align-items:center;justify-content:center;background:color-mix(in srgb,var(--color-clay) 12%,var(--color-surface));color:var(--color-clay)}
    .rv-cway-body{display:flex;flex-direction:column;gap:.15rem;min-width:0}
    .rv-cway-t{font-family:var(--font-display);font-weight:700;font-size:1.02rem;color:var(--color-ink)}
    .rv-cway-d{font-size:.86rem;color:var(--color-muted);overflow:hidden;text-overflow:ellipsis}
    .rv-contact-grid{display:grid;grid-template-columns:1.55fr 1fr;gap:clamp(2rem,4vw,3.5rem);align-items:start}
    @media(max-width:900px){.rv-contact-grid{grid-template-columns:1fr}}
    .rv-cnt-form-intro{margin:.85rem 0 0;max-width:46rem}
    .rv-contact-wrap{margin-top:1.5rem;padding-bottom:0}
    .rv-form-pkg{margin:0 0 1rem;padding:.85rem 1rem;border-left:3px solid var(--color-pine);background:color-mix(in srgb,var(--color-sage) 18%,var(--color-surface));border-radius:0 12px 12px 0;color:var(--color-body);font-size:.95rem;line-height:1.45}
    .rv-form-pkg strong{color:var(--color-ink);font-weight:700}
    .rv-cnt-fine{margin:1rem 0 0;font-family:var(--font-mono);font-size:.78rem;letter-spacing:.04em;color:var(--color-muted);line-height:1.55}
    .rv-contact-side{display:grid;gap:1.25rem;position:sticky;top:6rem}
    @media(max-width:900px){.rv-contact-side{position:static}}
    .rv-cside-block{background:var(--color-surface);border:1px solid var(--color-line);border-radius:var(--radius-lg,16px);padding:1.5rem 1.6rem;position:relative;overflow:hidden}
    .rv-cside-block::before{content:"";position:absolute;left:0;top:0;bottom:0;width:5px;background:var(--ridgeline)}
    .rv-cnext{list-style:none;margin:1rem 0 0;padding:0;display:grid;gap:1rem}
    .rv-cnext li{display:grid;grid-template-columns:auto 1fr;gap:.9rem;align-items:start}
    .rv-cnext-n{flex:none;width:30px;height:30px;border-radius:50%;display:inline-flex;align-items:center;justify-content:center;background:var(--color-pine);color:#fff;font-family:var(--font-display);font-weight:800;font-size:.9rem}
    .rv-cnext div{color:var(--color-body);line-height:1.5;font-size:.95rem}
    .rv-cnext strong{color:var(--color-ink);display:block;font-weight:700}
    .rv-cside-open-text{margin:1rem 0 0;color:var(--color-body);font-size:.95rem;line-height:1.55}
    .rv-cside-list{list-style:none;margin:1rem 0 0;padding:0;display:grid;gap:.85rem}
    .rv-cside-list li{display:flex;align-items:center;gap:.75rem;color:var(--color-body);font-size:.95rem}
    .rv-cside-ico{flex:none;color:var(--color-clay)}
    .rv-cside-list a{color:var(--color-ink);font-weight:600;text-decoration:none;word-break:break-word}
    .rv-cside-list a:hover{color:var(--color-clay)}
    .rv-clocal-towns{display:flex;flex-wrap:wrap;gap:.6rem;margin-top:2rem}
    .rv-clocal-town{background:var(--color-paper,#fff);border:1px solid var(--color-line);border-radius:999px;padding:.5rem 1rem;font-family:var(--font-display);font-weight:700;font-size:.92rem;color:var(--color-ink)}
    .rv-clocal-town:hover{border-color:var(--color-sage)}
    @media(prefers-reduced-motion:reduce){a.rv-cway:hover{transform:none}.rv-cnt-why-jump svg{transition:none}}
  </style>
@endsection
