{{--
  Template Name: Areas We Serve
--}}

@extends('layouts.app')

@push('head')
<script type="application/ld+json">
{
  "@@context":"https://schema.org",
  "@@type":"RealEstateAgent",
  "@@id":"{{ home_url('/') }}#agency",
  "name":"Keystone Homes & Land",
  "url":"{{ home_url('/areas/') }}",
  "image":"{{ home_url('/preview.jpg/') }}",
  "telephone":"+1-717-555-0455",
  "email":"land@@keystonehomesandland.test",
  "priceRange":"$$",
  "address":{
    "@@type":"PostalAddress",
    "streetAddress":"455 Old Harrisburg Road",
    "addressLocality":"Gettysburg",
    "addressRegion":"PA",
    "postalCode":"17325",
    "addressCountry":"US"
  },
  "geo":{"@@type":"GeoCoordinates","latitude":39.8305,"longitude":-77.2325},
  "openingHoursSpecification":[
    {"@@type":"OpeningHoursSpecification","dayOfWeek":["Monday","Tuesday","Wednesday","Thursday","Friday"],"opens":"08:30","closes":"17:30"},
    {"@@type":"OpeningHoursSpecification","dayOfWeek":"Saturday","opens":"09:00","closes":"13:00"}
  ],
  "areaServed":[
    {"@@type":"City","name":"Gettysburg, PA"},
    {"@@type":"AdministrativeArea","name":"Adams County, PA"},
    {"@@type":"AdministrativeArea","name":"Franklin Township, PA"},
    {"@@type":"AdministrativeArea","name":"Menallen Township, PA"},
    {"@@type":"AdministrativeArea","name":"Butler Township, PA"},
    {"@@type":"AdministrativeArea","name":"Tyrone Township, PA"},
    {"@@type":"AdministrativeArea","name":"Hamiltonban Township, PA"},
    {"@@type":"AdministrativeArea","name":"Liberty Township, PA"},
    {"@@type":"City","name":"Biglerville, PA"},
    {"@@type":"City","name":"Fairfield, PA"},
    {"@@type":"City","name":"Cashtown, PA"},
    {"@@type":"City","name":"New Oxford, PA"},
    {"@@type":"City","name":"Littlestown, PA"}
  ]
}
</script>
<script type="application/ld+json">
{
  "@@context":"https://schema.org",
  "@@type":"BreadcrumbList",
  "itemListElement":[
    {"@@type":"ListItem","position":1,"name":"Home","item":"{{ home_url('/') }}"},
    {"@@type":"ListItem","position":2,"name":"Areas We Serve","item":"{{ home_url('/areas/') }}"}
  ]
}
</script>
@endpush

@section('content')
<a href="#main" class="skip-link">Skip to main content</a>

<!-- ============================= HEADER ============================= -->
<header class="site-header">
  <div class="header-inner">
    <a href="{{ home_url('/') }}" class="brand" aria-label="Keystone Homes & Land, home">
      <svg class="brand-mark" viewBox="0 0 48 48" fill="none" aria-hidden="true">
        <path d="M24 4 L44 20 V44 H4 V20 Z" fill="#9a3324"/>
        <path d="M24 4 L44 20 H4 Z" fill="#6f2015"/>
        <rect x="19" y="28" width="10" height="16" fill="#f2d98f"/>
        <circle cx="24" cy="18" r="3.4" fill="#f2d98f"/>
      </svg>
      <span class="brand-text">
        <strong>Keystone Homes &amp; Land</strong>
        <span>Gettysburg, PA</span>
      </span>
    </a>

    <nav class="main-nav" aria-label="Primary">
      <ul>
        <li><a href="{{ home_url('/') }}">Home</a></li>
        <li><a href="{{ home_url('/listings/') }}">Listings</a></li>
        <li><a href="{{ home_url('/areas/') }}" class="is-active" aria-current="page">Areas We Serve</a></li>
        <li><a href="{{ home_url('/guide/') }}">Land Buyer's Guide</a></li>
        <li><a href="{{ home_url('/agents/') }}">Our Agents</a></li>
        <li><a href="{{ home_url('/contact/') }}">Contact</a></li>
      </ul>
    </nav>

    <div class="header-cta">
      <a class="header-phone" href="tel:+17175550455">(717) 555-0455</a>
      <a class="btn btn-primary btn-sm" href="{{ home_url('/guide/') }}#schedule">Book a Call</a>
    </div>

    <button class="hamburger" id="hamburgerBtn" aria-expanded="false" aria-controls="mobileNav" aria-label="Open menu">
      <span></span><span></span><span></span>
    </button>
  </div>

  <nav class="mobile-nav" id="mobileNav" aria-label="Mobile">
    <a href="{{ home_url('/') }}">Home</a>
    <a href="{{ home_url('/listings/') }}">Listings</a>
    <a href="{{ home_url('/areas/') }}" class="is-active" aria-current="page">Areas We Serve</a>
    <a href="{{ home_url('/guide/') }}">Land Buyer's Guide</a>
    <a href="{{ home_url('/agents/') }}">Our Agents</a>
    <a href="{{ home_url('/contact/') }}">Contact</a>
    <a class="header-phone" href="tel:+17175550455">Call (717) 555-0455</a>
    <a class="btn btn-primary" href="{{ home_url('/guide/') }}#schedule">Book a Call</a>
  </nav>
</header>

<!-- ============================= BREADCRUMB ============================= -->
<nav class="breadcrumb" aria-label="Breadcrumb">
  <ol>
    <li><a href="{{ home_url('/') }}">Home</a></li>
    <li><span aria-current="page">Areas We Serve</span></li>
  </ol>
</nav>

<!-- ============================= PAGE HERO ============================= -->
<section class="page-hero">
  <div class="page-hero-inner">
    <p class="hero-eyebrow">Rural Adams County, PA</p>
    <h1>The townships &amp; land markets <em>we know by heart</em></h1>
    <p>Keystone Homes &amp; Land works the rural corners of Adams County — the orchard country north of Gettysburg, the mountain edge to the west, and the farm valleys in between. Here's what to know before you buy ground in each one.</p>
  </div>
</section>

<main id="main">
  <!-- ============================= INTRO PROSE ============================= -->
  <section class="section">
    <div class="wrap prose reveal">
      <h2>Land, farms &amp; homesteads across Adams County</h2>
      <p>Our office sits at 455 Old Harrisburg Road on the north edge of Gettysburg, a few minutes from Lincoln Square and the Gettysburg National Military Park. From that spot you can be standing on tillable ground in almost any direction within twenty minutes — US-15 runs north toward the orchards, US-30 (the Lincoln Highway) runs west toward the mountains and east toward New Oxford, and PA-116 carries you down to Fairfield and the Maryland line. Most of the property we sell isn't in town at all; it's the acreage, farms and country homesteads that ring Gettysburg on those roads.</p>
      <p>Rural Adams County is fruit-belt country. The ridges north and west of town hold some of the most productive apple and peach orchards in the eastern United States, and the valley floors are strong Class II and III farmland. That agricultural character shapes everything a land buyer cares about: which parcels are enrolled in clean-and-green (Act 319) tax deferment, where agricultural-security areas limit nuisance complaints, how easements and rights-of-way run, and whether a lot will perc for a conventional septic system. We serve the whole county, but the six townships below are where we spend most of our boot leather.</p>
    </div>
  </section>

  <!-- ============================= TOWNSHIP CARDS ============================= -->
  <section class="section section-alt">
    <div class="wrap">
      <div class="section-head left reveal">
        <p class="eyebrow">Township by Township</p>
        <h2>Where we work</h2>
        <p>A quick, honest read on six rural Adams County townships — what the ground is like, what tends to come up for sale, and what a buyer should watch for.</p>
      </div>

      <div class="area-grid">
        <div class="area-card reveal">
          <p class="area-meta">Northwest of Gettysburg · off US-30 &amp; Herr's Ridge Rd</p>
          <h3><span class="pin"><svg viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2"><path d="M21 10c0 7-9 13-9 13s-9-6-9-13a9 9 0 0118 0z"/><circle cx="12" cy="10" r="3"/></svg></span>Franklin Township</h3>
          <p>Franklin Township runs from Herr's Ridge out toward Cashtown and the base of South Mountain, and it's some of our favorite ground to sell. You'll find century stone homesteads, working orchards along the York Road corridor, and wooded building lots with long views toward the mountain. It's about 10 minutes northwest of downtown Gettysburg on US-30.</p>
          <p>Watch for: steep, wooded parcels that need careful perc siting, and orchard land that may be enrolled in Act 319 clean-and-green — a wonderful tax break, but one with rollback consequences if you change the use.</p>
        </div>

        <div class="area-card reveal">
          <p class="area-meta">North of Gettysburg · Biglerville &amp; the fruit belt · US-15 / PA-34</p>
          <h3><span class="pin"><svg viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2"><path d="M21 10c0 7-9 13-9 13s-9-6-9-13a9 9 0 0118 0z"/><circle cx="12" cy="10" r="3"/></svg></span>Menallen Township</h3>
          <p>Menallen wraps around Biglerville and is the heart of Adams County's apple country — home to packing houses, the fruit research station, and mile after mile of orchard. Land here trades on tree health, irrigation and frost drainage as much as acreage. It's a 15-minute drive north of Gettysburg up US-15 or PA-34.</p>
          <p>Watch for: existing orchard leases and spray-easement histories, plus older farmsteads where the house and the productive ground may be worth splitting or preserving separately.</p>
        </div>

        <div class="area-card reveal">
          <p class="area-meta">Northeast · around Biglerville &amp; Table Rock · PA-34</p>
          <h3><span class="pin"><svg viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2"><path d="M21 10c0 7-9 13-9 13s-9-6-9-13a9 9 0 0118 0z"/><circle cx="12" cy="10" r="3"/></svg></span>Butler Township</h3>
          <p>Butler Township, north and east toward Table Rock and Bendersville, mixes orchard ground with open rolling farmland and quiet residential lots. It tends to offer a little more value per acre than the townships hugging the battlefield, which makes it popular with buyers who want a homestead or a few acres of pasture without a Gettysburg-address premium.</p>
          <p>Watch for: shared farm lanes and undefined rights-of-way on older parcels — we always confirm legal access in writing before closing.</p>
        </div>

        <div class="area-card reveal">
          <p class="area-meta">North-central · Idaville &amp; Bendersville · PA-234</p>
          <h3><span class="pin"><svg viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2"><path d="M21 10c0 7-9 13-9 13s-9-6-9-13a9 9 0 0118 0z"/><circle cx="12" cy="10" r="3"/></svg></span>Tyrone Township</h3>
          <p>Tyrone Township, up around Idaville and Bendersville on PA-234, is working-farm country: larger tracts of tillable and orchard ground, grain operations, and the occasional dairy. Buyers here are usually after real acreage — 20, 40, 60 acres — rather than a single homesite, and financing often runs through a farm-credit lender rather than a conventional mortgage.</p>
          <p>Watch for: soil-class and drainage differences across a single tract, and whether tillable ground carries an existing crop lease you'll inherit at closing.</p>
        </div>

        <div class="area-card reveal">
          <p class="area-meta">West · South Mountain, Fairfield &amp; Cashtown · PA-116</p>
          <h3><span class="pin"><svg viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2"><path d="M21 10c0 7-9 13-9 13s-9-6-9-13a9 9 0 0118 0z"/><circle cx="12" cy="10" r="3"/></svg></span>Hamiltonban Township</h3>
          <p>Hamiltonban is the county's mountain township — Fairfield, Carroll Valley and the wooded slopes of South Mountain toward the Michaux State Forest and the Appalachian Trail. It's the place for cabins, wooded acreage, hunting ground and recreational parcels, about 20 minutes southwest of Gettysburg on PA-116. The scenery is the draw and the terrain is the challenge.</p>
          <p>Watch for: slope and access on mountain lots, well-yield questions in rocky ground, and seasonal roads — all things we check before you commit.</p>
        </div>

        <div class="area-card reveal">
          <p class="area-meta">Southwest · Fairfield &amp; the Maryland line · PA-116 / Bullfrog Rd</p>
          <h3><span class="pin"><svg viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2"><path d="M21 10c0 7-9 13-9 13s-9-6-9-13a9 9 0 0118 0z"/><circle cx="12" cy="10" r="3"/></svg></span>Liberty Township</h3>
          <p>Liberty Township sits in the county's southwest corner, running down to the Mason-Dixon line below Fairfield. It's a rural mix of small farms, pasture and wooded homesteads, popular with buyers who want land within an easy commute of both Gettysburg and Frederick, Maryland. Sachs Covered Bridge and the quiet back roads along Marsh Creek are just up the way.</p>
          <p>Watch for: floodplain along the creeks and cross-border considerations for buyers relocating from Maryland — we'll walk you through both.</p>
        </div>
      </div>
    </div>
  </section>

  <!-- ============================= NEARBY MARKETS ============================= -->
  <section class="section">
    <div class="wrap">
      <div class="section-head left reveal">
        <p class="eyebrow">Nearby Land Markets</p>
        <h2>Towns &amp; markets just beyond the townships</h2>
        <p>We also list and sell property in the boroughs and market towns that ring rural Adams County — each an easy drive from our Gettysburg office.</p>
      </div>
      <div class="nearby-grid reveal">
        <div class="nearby-card">
          <h4>Biglerville</h4>
          <p>The capital of the fruit belt, 9 miles north on US-15/PA-34. Orchard farms, packing houses and in-town homes near the National Apple Museum.</p>
        </div>
        <div class="nearby-card">
          <h4>Cashtown</h4>
          <p>Historic crossroads west on US-30 at the foot of the mountain. Country homes, small acreage and the landmark Cashtown Inn nearby.</p>
        </div>
        <div class="nearby-card">
          <h4>Fairfield</h4>
          <p>A walkable mountain borough 8 miles southwest on PA-116. Gateway to Carroll Valley, ski country and wooded recreational land.</p>
        </div>
        <div class="nearby-card">
          <h4>New Oxford</h4>
          <p>Antiques town 8 miles east on US-30. Farmland and family homes with quick access toward Hanover and York.</p>
        </div>
        <div class="nearby-card">
          <h4>Littlestown</h4>
          <p>Southeast on PA-97 toward the Maryland line. Open farm ground, newer subdivisions and value per acre for commuting buyers.</p>
        </div>
        <div class="nearby-card">
          <h4>McSherrystown &amp; Hanover</h4>
          <p>The county's busier eastern edge. Denser boroughs with nearby farmland — handy for buyers who want acreage close to services and employers.</p>
        </div>
      </div>
    </div>
  </section>

  <!-- ============================= GETTYSBURG ANCHOR ============================= -->
  <section class="section section-alt">
    <div class="wrap prose reveal">
      <h2>Getting here &amp; getting around</h2>
      <p>Gettysburg is the hub of it all. Our office at 455 Old Harrisburg Road is about a mile north of Lincoln Square — from US-15, take the Gettysburg exits and head toward town; from US-30, you're minutes away whether you're coming from Chambersburg to the west or York to the east. Downtown parking is easiest in the lots off Race Horse Alley and along Stratton Street, and most of the historic district around Steinwehr Avenue, Baltimore Street and the David Wills House is walkable once you're in.</p>
      <p>Even buyers hunting pure farmland like to know what's close, so here's the short list: the Gettysburg National Military Park and its landmarks — Seminary Ridge, Little Round Top, the Eternal Light Peace Memorial — sit right at the edge of town; Sachs Covered Bridge spans Marsh Creek down toward Liberty Township; and the orchards, wineries and farm stands of the fruit belt spread north toward Biglerville. When we show you a parcel, we'll tell you honestly how far it really is from a grocery store, a school and a hospital — not just how pretty the view is.</p>
      <p>Ready to narrow it down? Browse our <a href="{{ home_url('/listings/') }}">current listings</a>, read the <a href="{{ home_url('/guide/') }}">Land Buyer's Guide</a>, or <a href="{{ home_url('/contact/') }}">reach out to the office</a> and we'll point you toward the townships that fit what you're after.</p>
    </div>
  </section>

  <!-- ============================= CTA BAND ============================= -->
  <section class="section">
    <div class="wrap">
      <div class="cta-band reveal">
        <h2>Not sure which township fits you?</h2>
        <p>Tell a Keystone agent what you're after — acreage, a homestead, orchard ground, a mountain cabin — and we'll steer you to the right corner of Adams County.</p>
        <div class="cta-actions">
          <a class="btn btn-gold" href="{{ home_url('/guide/') }}#schedule">Book a Call</a>
          <a class="btn btn-outline light" href="{{ home_url('/listings/') }}">Browse Listings</a>
        </div>
      </div>
    </div>
  </section>
</main>

<!-- ============================= FOOTER (SHARED) ============================= -->
<footer class="site-footer">
  <div class="wrap">
    <div class="footer-grid">
      <div>
        <div class="footer-brand">
          <svg width="34" height="34" viewBox="0 0 48 48" fill="none" aria-hidden="true">
            <path d="M24 4 L44 20 V44 H4 V20 Z" fill="#9a3324"/>
            <path d="M24 4 L44 20 H4 Z" fill="#6f2015"/>
            <rect x="19" y="28" width="10" height="16" fill="#f2d98f"/>
            <circle cx="24" cy="18" r="3.4" fill="#f2d98f"/>
          </svg>
          <strong>Keystone Homes &amp; Land</strong>
        </div>
        <p>Gettysburg's farm, land &amp; historic property specialists since 2009. Rooted in rural Adams County, PA.</p>
        <div class="social-row">
          <a href="#" aria-label="Keystone Homes & Land on Facebook"><svg viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2"><path d="M18 2h-3a5 5 0 00-5 5v3H7v4h3v8h4v-8h3l1-4h-4V7a1 1 0 011-1h3z"/></svg></a>
          <a href="#" aria-label="Keystone Homes & Land on Instagram"><svg viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2"><rect x="3" y="3" width="18" height="18" rx="5"/><circle cx="12" cy="12" r="4"/><circle cx="17.5" cy="6.5" r="1"/></svg></a>
          <a href="#" aria-label="Keystone Homes & Land on YouTube"><svg viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2"><rect x="2" y="5" width="20" height="14" rx="3"/><path d="M10 9l6 3-6 3z"/></svg></a>
        </div>
      </div>
      <div>
        <h5>Office</h5>
        <address>
          Keystone Homes &amp; Land<br>
          455 Old Harrisburg Road<br>
          Gettysburg, PA 17325<br>
          <a href="tel:+17175550455">(717) 555-0455</a><br>
          <a href="mailto:land@@keystonehomesandland.test">land@@keystonehomesandland.test</a>
        </address>
      </div>
      <div>
        <h5>Hours</h5>
        <p>Mon–Fri: 8:30am – 5:30pm<br>Saturday: 9:00am – 1:00pm<br>Sunday: By appointment</p>
      </div>
      <div>
        <h5>Quick Links</h5>
        <ul class="footer-links">
          <li><a href="{{ home_url('/') }}">Home</a></li>
          <li><a href="{{ home_url('/listings/') }}">Listings</a></li>
          <li><a href="{{ home_url('/areas/') }}">Areas We Serve</a></li>
          <li><a href="{{ home_url('/guide/') }}">Land Buyer's Guide</a></li>
          <li><a href="{{ home_url('/agents/') }}">Our Agents</a></li>
          <li><a href="{{ home_url('/contact/') }}">Contact</a></li>
        </ul>
      </div>
    </div>
    <p class="footer-service-area">Proudly serving Gettysburg and rural Adams County — including Franklin, Menallen, Butler, Tyrone, Hamiltonban, Liberty, Cumberland and Straban Townships, plus Biglerville, Fairfield, Cashtown, New Oxford and Littlestown.</p>
    <div class="footer-bottom">
      <div class="equal-housing">
        <svg viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" aria-hidden="true"><path d="M3 11l9-8 9 8"/><path d="M5 10v10h14V10"/><path d="M9 21v-6h6v6"/></svg>
        <span>Equal Housing Opportunity</span>
      </div>
      <p>&copy; <span data-year>2026</span> Keystone Homes &amp; Land · Design concept by Ridges &amp; Valleys Studio. Property data is illustrative, not a live MLS feed.</p>
    </div>
  </div>
</footer>

<!-- ============================= CHAT WIDGET (SHARED) ============================= -->
<button class="chat-fab" id="chatFab" aria-label="Open chat assistant" aria-expanded="false" aria-controls="chatWidget">
  <svg viewBox="0 0 24 24" stroke-width="2"><path d="M21 11.5a8.38 8.38 0 01-.9 3.8 8.5 8.5 0 01-7.6 4.7 8.38 8.38 0 01-3.8-.9L3 21l1.9-5.7a8.38 8.38 0 01-.9-3.8 8.5 8.5 0 014.7-7.6 8.38 8.38 0 013.8-.9h.5a8.48 8.48 0 018 8v.5z"/></svg>
</button>
<div class="chat-widget" id="chatWidget" role="dialog" aria-label="Keystone chat assistant" aria-hidden="true">
  <div class="chat-head">
    <div><strong>Keystone Assistant</strong><br><span>Usually replies in minutes</span></div>
    <button class="chat-close" id="chatCloseBtn" aria-label="Close chat">
      <svg width="16" height="16" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2"><line x1="18" y1="6" x2="6" y2="18"/><line x1="6" y1="6" x2="18" y2="18"/></svg>
    </button>
  </div>
  <div class="chat-body" id="chatBody">
    <div class="chat-msg bot">Hi! I'm the Keystone concept assistant. Ask me about listings, financing, or booking a tour.</div>
  </div>
  <div class="chat-quick" id="chatQuick">
    <button type="button" data-q="land">Land prices?</button>
    <button type="button" data-q="historic">Historic homes?</button>
    <button type="button" data-q="tour">Book a tour</button>
    <button type="button" data-q="financing">Financing help?</button>
  </div>
</div>

<a href="#" class="concept-badge">Concept · Ridges &amp; Valleys Studio</a>
@endsection
