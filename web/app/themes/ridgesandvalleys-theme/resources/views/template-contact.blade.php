{{--
  Template Name: Contact
--}}
@extends('layouts.app')

@php
// Editable in the page fields; sensible defaults so the page is useful out of the box.
// Swap contact_email for a studio address anytime; set contact_phone to show the call/text option.
$cEmail = \App\field('contact_email', 'matthew.r.hummel@gmail.com');
$cPhone = trim((string) \App\field('contact_phone', ''));
$cPhoneHref = $cPhone ? preg_replace('/[^0-9+]/', '', $cPhone) : '';
$cHours = \App\field('contact_hours', __('Mon–Fri, 9am–5pm · evenings by appointment', 'sage'));
$ctaHref = \App\cta_href(get_theme_mod('rv_cta_url', '/contact/'));

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
      {!! \App\eyebrow(\App\field('hero_eyebrow', __('Get in touch', 'sage'))) !!}
      <h1 id="rv-contact-hero-title" class="rv-hero-title">{{ \App\field('hero_title', __('Let\'s build something', 'sage')) }} <em class="rv-accent">{{ \App\field('hero_accent', __('local.', 'sage')) }}</em></h1>
      <p class="rv-hero-sub">{{ \App\field('contact_lede', __('Tell me about your Gettysburg or Adams County business — I’ll come back with a fixed-scope idea, usually within a business day.', 'sage')) }}</p>
      <div class="rv-hero-actions">
        <a class="rv-btn rv-btn-primary" href="#contact-form">{{ \App\field('contact_hero_btn', __('Send a note', 'sage')) }}</a>
      </div>
    </div>
  </section>

  {{-- QUICK WAYS TO GET IN TOUCH --}}
  <section class="rv-shell rv-cways" aria-label="{{ __('Ways to get in touch', 'sage') }}">
    <a class="rv-cway rv-cway-primary" href="#contact-form">
      <span class="rv-cway-ico">{!! $svgForm !!}</span>
      <span class="rv-cway-body"><span class="rv-cway-t">{{ \App\field('cway_form_title', __('Fill out the form', 'sage')) }}</span><span class="rv-cway-d">{{ \App\field('cway_form_desc', __('The fastest way to a fixed-scope quote.', 'sage')) }}</span></span>
    </a>
    <a class="rv-cway" href="mailto:{{ esc_attr($cEmail) }}">
      <span class="rv-cway-ico">{!! $svgMail !!}</span>
      <span class="rv-cway-body"><span class="rv-cway-t">{{ \App\field('cway_email_title', __('Email me directly', 'sage')) }}</span><span class="rv-cway-d">{{ $cEmail }}</span></span>
    </a>
    @if ($cPhone)
      <a class="rv-cway" href="tel:{{ $cPhoneHref }}">
        <span class="rv-cway-ico">{!! $svgPhone !!}</span>
        <span class="rv-cway-body"><span class="rv-cway-t">{{ \App\field('cway_call_title', __('Call or text', 'sage')) }}</span><span class="rv-cway-d">{{ $cPhone }}</span></span>
      </a>
    @else
      <div class="rv-cway rv-cway-static">
        <span class="rv-cway-ico">{!! $svgPin !!}</span>
        <span class="rv-cway-body"><span class="rv-cway-t">{{ \App\field('cway_local_title', __('Local to Gettysburg', 'sage')) }}</span><span class="rv-cway-d">{{ \App\field('cway_local_desc', __('Serving Adams County & South Central PA', 'sage')) }}</span></span>
      </div>
    @endif
  </section>

  {{-- FORM + SIDEBAR --}}
  <section class="rv-shell rv-band rv-contact-main" id="contact-form">
    <div class="rv-contact-grid">
      <div class="rv-contact-formcol">
        {!! \App\eyebrow(\App\field('cform_eyebrow', __('Project inquiry', 'sage'))) !!}
        <h2 class="rv-section-title" style="margin-top:.4rem">{{ \App\field('cform_title', __('Tell me about your', 'sage')) }} <em class="rv-accent">{{ \App\field('cform_accent', __('business.', 'sage')) }}</em></h2>
        <div class="rv-contact-wrap">@php(\App\contact_form())</div>
        <p class="rv-tool-hint" style="margin-top:1.25rem">{{ \App\field('contact_note', __('The project clock starts when this and your assets are complete. Feedback within two business days keeps launch on schedule.', 'sage')) }}</p>
      </div>

      <div class="rv-contact-side">
        <div class="rv-cside-block">
          {!! \App\eyebrow(\App\field('cnext_eyebrow', __('What happens next', 'sage'))) !!}
          <ol class="rv-cnext">
            @foreach (\App\field_rows('cnext_items', [
              ['strong' => __('I reply — usually within a business day.', 'sage'), 'text' => __('A real, personal response, not an auto-reminder.', 'sage')],
              ['strong' => __('A quick, no-pressure chat.', 'sage'), 'text' => __('A short call or email so I understand exactly what you need.', 'sage')],
              ['strong' => __('A clear, fixed-scope plan.', 'sage'), 'text' => __('What I\'d build, what it costs, and how long — in writing.', 'sage')],
            ]) as $i => $cn)
              <li><span class="rv-cnext-n">{{ $i + 1 }}</span><div><strong>{{ $cn['strong'] ?? '' }}</strong> {{ $cn['text'] ?? '' }}</div></li>
            @endforeach
          </ol>
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
        <a class="rv-btn rv-btn-primary" href="#contact-form">{{ \App\field('clocal_btn1', __('Start the conversation', 'sage')) }}</a>
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
    /* Quick ways to get in touch */
    #contact-form{scroll-margin-top:6rem}
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
    .rv-cway-static{cursor:default}
    /* Form + sidebar */
    .rv-contact-grid{display:grid;grid-template-columns:1.55fr 1fr;gap:clamp(2rem,4vw,3.5rem);align-items:start}
    @media(max-width:900px){.rv-contact-grid{grid-template-columns:1fr}}
    .rv-contact-intro-body{margin-top:1rem;max-width:60ch}
    .rv-contact-wrap{margin-top:1.5rem}
    .rv-form-pkg{margin:0 0 1rem;padding:.85rem 1rem;border-left:3px solid var(--color-pine);background:color-mix(in srgb,var(--color-sage) 18%,var(--color-surface));border-radius:0 12px 12px 0;color:var(--color-body);font-size:.95rem;line-height:1.45}
    .rv-form-pkg strong{color:var(--color-ink);font-weight:700}
    .rv-contact-side{display:grid;gap:1.25rem;position:sticky;top:6rem}
    @media(max-width:900px){.rv-contact-side{position:static}}
    .rv-cside-block{background:var(--color-surface);border:1px solid var(--color-line);border-radius:var(--radius-lg,16px);padding:1.5rem 1.6rem;position:relative;overflow:hidden}
    .rv-cside-block::before{content:"";position:absolute;left:0;top:0;bottom:0;width:5px;background:var(--ridgeline)}
    .rv-cnext{list-style:none;margin:1rem 0 0;padding:0;display:grid;gap:1rem}
    .rv-cnext li{display:grid;grid-template-columns:auto 1fr;gap:.9rem;align-items:start}
    .rv-cnext-n{flex:none;width:30px;height:30px;border-radius:50%;display:inline-flex;align-items:center;justify-content:center;background:var(--color-pine);color:#fff;font-family:var(--font-display);font-weight:800;font-size:.9rem}
    .rv-cnext div{color:var(--color-body);line-height:1.5;font-size:.95rem}
    .rv-cnext strong{color:var(--color-ink);display:block;font-weight:700}
    .rv-cside-list{list-style:none;margin:1rem 0 0;padding:0;display:grid;gap:.85rem}
    .rv-cside-list li{display:flex;align-items:center;gap:.75rem;color:var(--color-body);font-size:.95rem}
    .rv-cside-ico{flex:none;color:var(--color-clay)}
    .rv-cside-list a{color:var(--color-ink);font-weight:600;text-decoration:none;word-break:break-word}
    .rv-cside-list a:hover{color:var(--color-clay)}
    /* Local service area */
    .rv-clocal-towns{display:flex;flex-wrap:wrap;gap:.6rem;margin-top:2rem}
    .rv-clocal-town{background:var(--color-paper,#fff);border:1px solid var(--color-line);border-radius:999px;padding:.5rem 1rem;font-family:var(--font-display);font-weight:700;font-size:.92rem;color:var(--color-ink)}
    .rv-clocal-town:hover{border-color:var(--color-sage)}
  </style>
@endsection
