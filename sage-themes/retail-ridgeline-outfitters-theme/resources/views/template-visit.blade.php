{{--
  Template Name: Visit Us in Gettysburg, PA
--}}

@extends('layouts.app')

@push('head')
<script type="application/ld+json">
{
  "@@context": "https://schema.org",
  "@@type": "SportingGoodsStore",
  "@@id": "{{ home_url('/') }}#store",
  "name": "Ridgeline Outfitters",
  "description": "Outdoor gear shop at 120 Buford Avenue in downtown Gettysburg, PA — directions, parking and hours for visitors from across Adams County.",
  "image": "{{ home_url('/preview.jpg/') }}",
  "url": "{{ home_url('/visit/') }}",
  "telephone": "+1-717-555-0120",
  "email": "gear@@ridgelineoutfitters.test",
  "priceRange": "$$",
  "hasMap": "https://www.openstreetmap.org/search?query=120%20Buford%20Avenue%20Gettysburg%20PA%2017325",
  "address": {
    "@@type": "PostalAddress",
    "streetAddress": "120 Buford Avenue",
    "addressLocality": "Gettysburg",
    "addressRegion": "PA",
    "postalCode": "17325",
    "addressCountry": "US"
  },
  "geo": { "@@type": "GeoCoordinates", "latitude": 39.8339, "longitude": -77.2380 },
  "openingHoursSpecification": [
    { "@@type": "OpeningHoursSpecification", "dayOfWeek": ["Monday","Tuesday","Wednesday","Thursday"], "opens": "10:00", "closes": "18:00" },
    { "@@type": "OpeningHoursSpecification", "dayOfWeek": ["Friday","Saturday"], "opens": "09:00", "closes": "20:00" },
    { "@@type": "OpeningHoursSpecification", "dayOfWeek": "Sunday", "opens": "11:00", "closes": "17:00" }
  ],
  "areaServed": [
    { "@@type": "City", "name": "Gettysburg" },
    { "@@type": "AdministrativeArea", "name": "Adams County, Pennsylvania" },
    "Biglerville, PA", "Littlestown, PA", "New Oxford, PA", "McSherrystown, PA", "Fairfield, PA", "Cashtown, PA", "Hanover, PA"
  ]
}
</script>
<script type="application/ld+json">
{
  "@@context": "https://schema.org",
  "@@type": "BreadcrumbList",
  "itemListElement": [
    { "@@type": "ListItem", "position": 1, "name": "Home", "item": "{{ home_url('/') }}" },
    { "@@type": "ListItem", "position": 2, "name": "Visit Us", "item": "{{ home_url('/visit/') }}" }
  ]
}
</script>
@endpush

@section('content')
<a href="#main" class="skip-link">Skip to main content</a>

<!-- ===================== HEADER ===================== -->
<header class="site-header">
  <div class="container header-inner">
    <a href="{{ home_url('/') }}" class="brand" aria-label="Ridgeline Outfitters home">
      <svg class="brand-mark" viewBox="0 0 48 48" fill="none" aria-hidden="true">
        <circle cx="24" cy="24" r="22" fill="#1a2e23" stroke="#ff5a1f" stroke-width="2"/>
        <path d="M12 32L22 14L27 24L32 16L38 32H12Z" fill="#f8f6f0"/>
        <circle cx="24" cy="24" r="22" stroke="#efe9db" stroke-width="0.5" opacity="0.3"/>
      </svg>
      <span>Ridgeline Outfitters<small>Gettysburg, PA · Buford Ave</small></span>
    </a>

    <nav class="main-nav" aria-label="Primary">
      <a href="{{ home_url('/') }}">Home</a>
      <a href="{{ home_url('/shop/') }}">Shop Gear</a>
      <a href="{{ home_url('/guides/') }}">Gear Guides</a>
      <a href="{{ home_url('/about/') }}">About</a>
      <a href="{{ home_url('/visit/') }}" class="is-active" aria-current="page">Visit Us</a>
      <a href="{{ home_url('/contact/') }}">Contact</a>
    </nav>

    <div class="header-actions">
      <a class="icon-btn" href="{{ home_url('/shop/') }}" aria-label="View wishlist on the Shop page">
        <svg viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2"><path d="M12 21s-7.5-4.6-10-9.3C.4 8 2 4.5 5.6 4.1c2-.2 3.7.8 4.9 2.5 1.2-1.7 2.9-2.7 4.9-2.5C19 4.5 20.6 8 20 11.7 17.5 16.4 12 21 12 21z"/></svg>
      </a>
      <a class="icon-btn" href="{{ home_url('/shop/') }}" aria-label="View cart on the Shop page">
        <svg viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2"><path d="M3 3h2l.4 2M7 13h10l3-8H5.4M7 13L5.4 5M7 13l-1.7 6.4A1 1 0 006.3 20H18M10 22a1 1 0 100-2 1 1 0 000 2zM18 22a1 1 0 100-2 1 1 0 000 2z"/></svg>
      </a>
      <button class="icon-btn hamburger" id="menuToggle" aria-label="Open menu" aria-haspopup="true" aria-expanded="false" aria-controls="mobileMenu">
        <svg viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2"><path d="M3 6h18M3 12h18M3 18h18"/></svg>
      </button>
    </div>
  </div>

  <nav id="mobileMenu" class="mobile-menu" aria-label="Mobile" hidden>
    <a href="{{ home_url('/') }}">Home</a>
    <a href="{{ home_url('/shop/') }}">Shop Gear</a>
    <a href="{{ home_url('/guides/') }}">Gear Guides</a>
    <a href="{{ home_url('/about/') }}">About</a>
    <a href="{{ home_url('/visit/') }}" class="is-active" aria-current="page">Visit Us</a>
    <a href="{{ home_url('/contact/') }}">Contact</a>
  </nav>
</header>

<!-- ===================== BREADCRUMB ===================== -->
<nav class="breadcrumb" aria-label="Breadcrumb">
  <div class="container">
    <ol>
      <li><a href="{{ home_url('/') }}">Home</a></li>
      <li class="sep" aria-hidden="true">/</li>
      <li aria-current="page">Visit Us</li>
    </ol>
  </div>
</nav>

<main id="main">

  <!-- ===================== PAGE HERO ===================== -->
  <section class="hero page-hero" id="top">
    <div class="container hero-grid">
      <div class="hero-copy">
        <span class="eyebrow on-dark">Visit us · Downtown Gettysburg, PA</span>
        <h1>Find us at <em>120 Buford Avenue</em>, Gettysburg.</h1>
        <p>We're an easy stop on your way to the battlefield or the trailhead — just west of Lincoln Square in downtown Gettysburg, minutes from US-30 and the national park. Here's how to get here, where to park, and what's nearby.</p>
        <div class="hero-ctas">
          <a href="#directions" class="btn btn-primary">Get directions</a>
          <a href="tel:+17175550120" class="btn btn-outline on-dark">Call (717) 555-0120</a>
        </div>
      </div>
      <div class="hero-visual">
        <img src="https://commons.wikimedia.org/wiki/Special:FilePath/Lincoln_Square_Gettysburg.jpg?width=1600" alt="Lincoln Square in downtown Gettysburg, PA, a short walk from Ridgeline Outfitters on Buford Avenue" loading="lazy" onerror="this.style.display='none'">
        <div class="visual-overlay"></div>
        <div class="compass-badge">
          <svg viewBox="0 0 24 24" fill="none" stroke="#ff8654" stroke-width="1.6" aria-hidden="true"><circle cx="12" cy="12" r="9"/><path d="M15 9l-2 5-5 2 2-5z" fill="#ff8654" stroke="none"/></svg>
        </div>
        <div class="visual-tag">
          <span class="eyebrow">Downtown</span>
          <p>"Just west of Lincoln Square."</p>
        </div>
      </div>
    </div>
  </section>

  <!-- ===================== ADDRESS + MAP CARDS ===================== -->
  <section id="find-us">
    <div class="container">
      <div class="visit-grid">
        <div class="visit-card reveal">
          <h3>Ridgeline Outfitters</h3>
          <div class="visit-row">
            <svg viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" aria-hidden="true"><path d="M12 21s-7-6.5-7-11a7 7 0 1114 0c0 4.5-7 11-7 11z"/><circle cx="12" cy="10" r="2.5"/></svg>
            <p>120 Buford Avenue<br>Gettysburg, PA 17325</p>
          </div>
          <div class="visit-row">
            <svg viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" aria-hidden="true"><path d="M22 16.9v3a2 2 0 01-2.2 2 19.8 19.8 0 01-8.6-3.1 19.5 19.5 0 01-6-6A19.8 19.8 0 012.1 4.2 2 2 0 014.1 2h3a2 2 0 012 1.7c.1.9.3 1.8.6 2.7a2 2 0 01-.4 2.1L8.1 9.6a16 16 0 006.3 6.3l1.1-1.1a2 2 0 012.1-.4c.9.3 1.8.5 2.7.6a2 2 0 011.7 2.1z"/></svg>
            <p><a href="tel:+17175550120">(717) 555-0120</a></p>
          </div>
          <div class="visit-row">
            <svg viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" aria-hidden="true"><rect x="3" y="5" width="18" height="16" rx="2"/><path d="M16 3v4M8 3v4M3 10h18"/></svg>
            <p><a href="mailto:gear@@ridgelineoutfitters.test">gear@@ridgelineoutfitters.test</a></p>
          </div>
          <div class="visit-row">
            <svg viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" aria-hidden="true"><circle cx="12" cy="12" r="9"/><path d="M12 7v5l3 3"/></svg>
            <table class="hours-table">
              <tbody>
                <tr><td>Mon – Thu</td><td>10:00 – 6:00</td></tr>
                <tr><td>Fri – Sat</td><td>9:00 – 8:00</td></tr>
                <tr><td>Sunday</td><td>11:00 – 5:00</td></tr>
              </tbody>
            </table>
          </div>
          <div class="visit-pills">
            <span class="visit-pill">In-store shopping</span>
            <span class="visit-pill">Free in-store pickup</span>
            <span class="visit-pill">Gear repair</span>
          </div>
        </div>
        <div class="map-visual reveal">
          <div class="pin">
            <svg viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="1.6" aria-hidden="true"><path d="M12 21s-7-6.5-7-11a7 7 0 1114 0c0 4.5-7 11-7 11z"/><circle cx="12" cy="10" r="2.5"/></svg>
            <p>120 Buford Avenue<br>Gettysburg, PA 17325</p>
          </div>
        </div>
      </div>
    </div>
  </section>

  <!-- ===================== DIRECTIONS PROSE ===================== -->
  <section class="collections" id="directions">
    <div class="container">
      <div class="layout-split">
        <div class="prose reveal">
          <span class="eyebrow">Getting here</span>
          <h2>Directions to the shop</h2>
          <p>Ridgeline Outfitters sits on <strong>Buford Avenue</strong>, the western continuation of Chambersburg Street, just a few blocks from <strong>Lincoln Square</strong> at the center of downtown Gettysburg. If you can find the square, you're ninety seconds away.</p>

          <h3>From US-30 (Lincoln Highway)</h3>
          <ol class="directions-list">
            <li>Follow US-30 into Gettysburg. From the east it becomes York Street; from the west it becomes Chambersburg Street / Buford Avenue.</li>
            <li>Head for Lincoln Square in the center of town — all four highways meet here.</li>
            <li>From the square, take Chambersburg Street west; it becomes Buford Avenue as you leave the square.</li>
            <li>We're a short block up on Buford Avenue. Watch for our pine-and-blaze sign.</li>
          </ol>

          <h3>From US-15</h3>
          <p>Exit US-15 at the Gettysburg / US-30 interchange and follow US-30 west into town, then continue to Lincoln Square as above. From the square it's a two-minute drive to Buford Avenue.</p>

          <h3>Parking</h3>
          <p>There's metered on-street parking along Buford Avenue and the surrounding downtown blocks, plus the public <strong>Race Horse Alley parking garage</strong> a short walk east near Lincoln Square. On a busy battlefield weekend, the garage is your best bet — then it's a pleasant few-block walk to the shop past the downtown storefronts.</p>

          <h3>Walking &amp; the battlefield</h3>
          <p>From our door you can walk to Lincoln Square, the shops and restaurants of Steinwehr Avenue and Baltimore Street, and the north end of <strong>Gettysburg National Military Park</strong> in a matter of minutes. The main battlefield auto tour, <strong>Seminary Ridge</strong>, and the <strong>Little Round Top</strong> trailheads are a short drive south — grab your gear here first, then head straight to the trail.</p>
        </div>
        <aside class="side-card reveal" aria-label="Quick facts">
          <h3>Quick facts</h3>
          <ul>
            <li><a href="tel:+17175550120">📞 (717) 555-0120</a></li>
            <li><a href="mailto:gear@@ridgelineoutfitters.test">✉ gear@@ridgelineoutfitters.test</a></li>
          </ul>
          <p style="color:var(--ink-soft); font-size:0.9rem; margin:0 0 16px;">120 Buford Avenue, Gettysburg, PA 17325 — a short block west of Lincoln Square.</p>
          <a href="{{ home_url('/shop/') }}" class="btn btn-primary btn-block">Shop before you visit</a>
        </aside>
      </div>
    </div>
  </section>

  <!-- ===================== NEARBY TOWNS ===================== -->
  <section id="area">
    <div class="container">
      <div class="section-head reveal">
        <span class="eyebrow">The area we serve</span>
        <h2>Coming from elsewhere in Adams County?</h2>
        <p>We're the closest full outdoor-gear shop for a lot of southern Pennsylvania. Approximate drive times to our Buford Avenue door:</p>
      </div>
      <div class="town-grid">
        <div class="town-card reveal"><span class="town-dist">~10 min NW</span><h3>Biglerville</h3><p>North on PA-34 through apple country — quick run down for boots before a Michaux weekend.</p></div>
        <div class="town-card reveal"><span class="town-dist">~15 min E</span><h3>New Oxford</h3><p>Straight in on US-30 east. Antiques district by day, trail gear on the way through.</p></div>
        <div class="town-card reveal"><span class="town-dist">~15 min SE</span><h3>Littlestown</h3><p>Up PA-97 from the Maryland line — an easy stop en route to the battlefield's south end.</p></div>
        <div class="town-card reveal"><span class="town-dist">~20 min E</span><h3>Hanover</h3><p>US-116 west brings Hanover hikers to the largest gear selection in the county.</p></div>
        <div class="town-card reveal"><span class="town-dist">~18 min SE</span><h3>McSherrystown</h3><p>A short hop from the Hanover side of Adams County via PA-116.</p></div>
        <div class="town-card reveal"><span class="town-dist">~15 min W</span><h3>Fairfield &amp; Cashtown</h3><p>West toward the mountains and the Appalachian Trail — gear up before you climb.</p></div>
      </div>
    </div>
  </section>

  <!-- ===================== FAQ ===================== -->
  <section class="collections" id="faq">
    <div class="container">
      <div class="section-head reveal">
        <span class="eyebrow">Before you come</span>
        <h2>Visitor questions</h2>
      </div>
      <div class="faq-list">
        <div class="faq-item reveal">
          <h3>Where exactly are you located?</h3>
          <p>We're at 120 Buford Avenue, Gettysburg, PA 17325 — a short block west of Lincoln Square in downtown Gettysburg, minutes from US-30 and the national park.</p>
        </div>
        <div class="faq-item reveal">
          <h3>What are your hours?</h3>
          <p>Monday through Thursday 10:00–6:00, Friday and Saturday 9:00–8:00, and Sunday 11:00–5:00. Holiday hours may vary — call ahead at (717) 555-0120 if you're making a special trip.</p>
        </div>
        <div class="faq-item reveal">
          <h3>Is there parking nearby?</h3>
          <p>Yes — metered on-street parking along Buford Avenue and downtown, plus the Race Horse Alley public garage a short walk away near Lincoln Square. The garage is easiest on busy battlefield weekends.</p>
        </div>
        <div class="faq-item reveal">
          <h3>Can I order online and pick up in store?</h3>
          <p>Absolutely. Order from the <a href="{{ home_url('/shop/') }}">gear wall</a> and choose in-store pickup — orders are usually ready within the hour during shop hours. Free returns within 30 days, in store or by mail.</p>
        </div>
      </div>
    </div>
  </section>

  <!-- ===================== CTA ===================== -->
  <section>
    <div class="container">
      <div class="cta-band reveal">
        <span class="eyebrow on-dark">See you soon</span>
        <h2>Planning a trip to Gettysburg?</h2>
        <p>Shop ahead online, or just walk in — the crew on Buford Avenue is happy to help you gear up for whatever's next.</p>
        <div class="hero-ctas">
          <a href="{{ home_url('/shop/') }}" class="btn btn-primary">Shop the Gear Wall</a>
          <a href="{{ home_url('/guides/') }}" class="btn btn-outline on-dark">Read the trail guides</a>
        </div>
      </div>
    </div>
  </section>

</main>

<!-- ===================== FOOTER ===================== -->
<footer class="site-footer">
  <div class="container">
    <div class="footer-grid">
      <div class="footer-brand">
        <a href="{{ home_url('/') }}" class="brand">
          <svg class="brand-mark" viewBox="0 0 48 48" fill="none" aria-hidden="true">
            <circle cx="24" cy="24" r="22" fill="#1a2e23" stroke="#ff5a1f" stroke-width="2"/>
            <path d="M12 32L22 14L27 24L32 16L38 32H12Z" fill="#f8f6f0"/>
          </svg>
          <span>Ridgeline Outfitters</span>
        </a>
        <p>Outdoor &amp; adventure gear for the trails, ridgelines, and battlefield paths of Adams County, PA.</p>
        <div class="social-row">
          <a href="#" onclick="return false;" aria-label="Ridgeline Outfitters on Instagram"><svg viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="1.7"><rect x="3" y="3" width="18" height="18" rx="5"/><circle cx="12" cy="12" r="4"/><circle cx="17.2" cy="6.8" r="1"/></svg></a>
          <a href="#" onclick="return false;" aria-label="Ridgeline Outfitters on Facebook"><svg viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="1.7"><path d="M15 8h2V4h-2a4 4 0 00-4 4v2H9v4h2v6h4v-6h2.5l.5-4H15V8z"/></svg></a>
        </div>
      </div>
      <div class="footer-col">
        <h4>Explore</h4>
        <ul>
          <li><a href="{{ home_url('/') }}">Home</a></li>
          <li><a href="{{ home_url('/shop/') }}">Shop Gear</a></li>
          <li><a href="{{ home_url('/guides/') }}">Gear Guides</a></li>
          <li><a href="{{ home_url('/about/') }}">About</a></li>
          <li><a href="{{ home_url('/visit/') }}">Visit Us</a></li>
          <li><a href="{{ home_url('/contact/') }}">Contact</a></li>
        </ul>
      </div>
      <div class="footer-col">
        <h4>Visit the Shop</h4>
        <ul>
          <li><a href="{{ home_url('/visit/') }}">120 Buford Avenue<br>Gettysburg, PA 17325</a></li>
          <li><a href="tel:+17175550120">(717) 555-0120</a></li>
          <li><a href="mailto:gear@@ridgelineoutfitters.test">gear@@ridgelineoutfitters.test</a></li>
        </ul>
      </div>
      <div class="footer-col">
        <h4>Hours</h4>
        <table class="hours-table">
          <tbody>
            <tr><td>Mon – Thu</td><td>10:00 – 6:00</td></tr>
            <tr><td>Fri – Sat</td><td>9:00 – 8:00</td></tr>
            <tr><td>Sunday</td><td>11:00 – 5:00</td></tr>
          </tbody>
        </table>
        <p style="color:var(--stone-300); font-size:0.82rem; margin-top:14px;">Proudly serving Gettysburg, Biglerville, Littlestown, New Oxford, McSherrystown, Fairfield, Cashtown &amp; Hanover.</p>
      </div>
    </div>
    <div class="footer-bottom">
      <span>© <span data-year>2026</span> Ridgeline Outfitters. All rights reserved.</span>
      <span class="mono">120 Buford Ave, Gettysburg, PA 17325 · (717) 555-0120</span>
    </div>
    <p class="footer-fine">Design concept by Ridges &amp; Valleys Studio to demonstrate a future website for Ridgeline Outfitters, a fictional business. Product names, prices, inventory, and reviews shown are illustrative only. No transactions are processed on this site.</p>
  </div>
</footer>

<!-- ===================== CONCEPT BADGE ===================== -->
<a href="#" class="concept-badge" onclick="return false;">
  <span class="dot" aria-hidden="true"></span>
  Concept · Ridges &amp; Valleys Studio
</a>
@endsection
