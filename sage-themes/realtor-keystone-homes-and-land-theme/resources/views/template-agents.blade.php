{{--
  Template Name: Our Agents
--}}

@extends('layouts.app')

@push('head')
<script type="application/ld+json">
{
  "@@context":"https://schema.org",
  "@@type":"RealEstateAgent",
  "@@id":"{{ home_url('/') }}#agency",
  "name":"Keystone Homes & Land",
  "url":"{{ home_url('/agents/') }}",
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
  "geo":{"@@type":"GeoCoordinates","latitude":39.8301,"longitude":-77.2314},
  "openingHoursSpecification":[
    {"@@type":"OpeningHoursSpecification","dayOfWeek":["Monday","Tuesday","Wednesday","Thursday","Friday"],"opens":"08:30","closes":"17:30"},
    {"@@type":"OpeningHoursSpecification","dayOfWeek":"Saturday","opens":"09:00","closes":"13:00"}
  ],
  "employee":[
    {"@@type":"Person","name":"Dale Kuhn","jobTitle":"Broker / Farm & Land Specialist","telephone":"+1-717-555-0461"},
    {"@@type":"Person","name":"Renee Musselman","jobTitle":"Historic & Residential Agent","telephone":"+1-717-555-0468"},
    {"@@type":"Person","name":"Trey Bushey","jobTitle":"Land & New-Buyer Agent","telephone":"+1-717-555-0473"}
  ],
  "areaServed":[
    {"@@type":"City","name":"Gettysburg, PA"},
    {"@@type":"AdministrativeArea","name":"Adams County, PA"},
    {"@@type":"AdministrativeArea","name":"Franklin Township, PA"},
    {"@@type":"AdministrativeArea","name":"Menallen Township, PA"},
    {"@@type":"AdministrativeArea","name":"Butler Township, PA"},
    {"@@type":"AdministrativeArea","name":"Tyrone Township, PA"},
    {"@@type":"AdministrativeArea","name":"Hamiltonban Township, PA"},
    {"@@type":"AdministrativeArea","name":"Liberty Township, PA"}
  ]
}
</script>
<script type="application/ld+json">
{
  "@@context":"https://schema.org",
  "@@type":"BreadcrumbList",
  "itemListElement":[
    {"@@type":"ListItem","position":1,"name":"Home","item":"{{ home_url('/') }}"},
    {"@@type":"ListItem","position":2,"name":"Our Agents","item":"{{ home_url('/agents/') }}"}
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
        <li><a href="{{ home_url('/areas/') }}">Areas We Serve</a></li>
        <li><a href="{{ home_url('/guide/') }}">Land Buyer's Guide</a></li>
        <li><a href="{{ home_url('/agents/') }}" class="is-active" aria-current="page">Our Agents</a></li>
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
    <a href="{{ home_url('/areas/') }}">Areas We Serve</a>
    <a href="{{ home_url('/guide/') }}">Land Buyer's Guide</a>
    <a href="{{ home_url('/agents/') }}" class="is-active" aria-current="page">Our Agents</a>
    <a href="{{ home_url('/contact/') }}">Contact</a>
    <a class="header-phone" href="tel:+17175550455">Call (717) 555-0455</a>
    <a class="btn btn-primary" href="{{ home_url('/guide/') }}#schedule">Book a Call</a>
  </nav>
</header>

<!-- ============================= BREADCRUMB ============================= -->
<nav class="breadcrumb" aria-label="Breadcrumb">
  <ol>
    <li><a href="{{ home_url('/') }}">Home</a></li>
    <li><span aria-current="page">Our Agents</span></li>
  </ol>
</nav>

<!-- ============================= PAGE HERO ============================= -->
<section class="page-hero">
  <div class="page-hero-inner">
    <p class="hero-eyebrow">Meet The Team</p>
    <h1>Local agents who walk <em>the fence lines with you</em></h1>
    <p>Every agent at Keystone Homes &amp; Land is licensed in Pennsylvania and rooted in Adams County. We don't guess at zoning maps from an office two counties away — we grew up on these roads.</p>
  </div>
</section>

<main id="main">
  <!-- ============================= INTRO ============================= -->
  <section class="section">
    <div class="wrap prose reveal">
      <h2>A small, local team by design</h2>
      <p>Keystone Homes &amp; Land was founded in Gettysburg in 2009 around a simple idea: rural property deserves an agent who understands rural property. Farms, orchards, raw land and century homesteads all come with questions a typical residential agent rarely faces — perc tests, well yields, ag easements, clean-and-green rollback, floodplain, mineral rights. Our team lives with those questions every day, across Franklin, Menallen, Butler, Tyrone, Hamiltonban and Liberty Townships and the boroughs around them.</p>
    </div>

    <div class="wrap">
      <div class="agent-grid">
        <div class="agent-card reveal">
          <div class="agent-avatar" style="background:var(--barn-red);">DK</div>
          <h4>Dale Kuhn</h4>
          <p class="agent-title">Broker / Farm &amp; Land Specialist</p>
          <p>28 years walking Adams County fence lines. If it has acreage, Dale has probably sold a piece of it — from Tyrone Township grain farms to Menallen orchards. He handles perc, easements and clean-and-green day in, day out.</p>
          <a class="agent-phone" href="tel:+17175550461">(717) 555-0461</a>
        </div>
        <div class="agent-card reveal">
          <div class="agent-avatar" style="background:var(--slate-dark);">RM</div>
          <h4>Renee Musselman</h4>
          <p class="agent-title">Historic &amp; Residential Agent</p>
          <p>Specializes in century farmhouses and in-town historic homes near the battlefield district. Renee knows stone foundations, log cores and the honest cost of restoring a Franklin Township homestead.</p>
          <a class="agent-phone" href="tel:+17175550468">(717) 555-0468</a>
        </div>
        <div class="agent-card reveal">
          <div class="agent-avatar" style="background:var(--wheat-dark);">TB</div>
          <h4>Trey Bushey</h4>
          <p class="agent-title">Land &amp; New-Buyer Agent</p>
          <p>Grew up on a Straban Township dairy farm — now helps first-time land buyers ask the right questions about wells, septic and access before they fall in love with a view.</p>
          <a class="agent-phone" href="tel:+17175550473">(717) 555-0473</a>
        </div>
      </div>
    </div>
  </section>

  <!-- ============================= HOW WE WORK ============================= -->
  <section class="section section-alt">
    <div class="wrap">
      <div class="section-head left reveal">
        <p class="eyebrow">How We Work</p>
        <h2>What working with Keystone looks like</h2>
        <p>No pressure, no jargon, and a straight answer about the ground under your feet.</p>
      </div>
      <div class="why-list reveal" style="max-width:820px;">
        <div class="why-item">
          <span class="icn"><svg width="20" height="20" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2"><path d="M21 11.5a8.38 8.38 0 01-.9 3.8 8.5 8.5 0 01-7.6 4.7 8.38 8.38 0 01-3.8-.9L3 21l1.9-5.7a8.38 8.38 0 01-.9-3.8 8.5 8.5 0 014.7-7.6 8.38 8.38 0 013.8-.9h.5a8.48 8.48 0 018 8v.5z"/></svg></span>
          <div><h4>We listen first</h4><p>Acreage, a homestead, orchard ground, a mountain cabin, a place to retire — we start by understanding what you actually want, then match it to the right township.</p></div>
        </div>
        <div class="why-item">
          <span class="icn"><svg width="20" height="20" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2"><path d="M4 4l7 7M4 4h5M4 4v5"/><path d="M20 20l-7-7M20 20h-5M20 20v-5"/></svg></span>
          <div><h4>We walk the ground</h4><p>We check access, well and septic feasibility, zoning and easements before you write an offer — not after inspection surprises you.</p></div>
        </div>
        <div class="why-item">
          <span class="icn"><svg width="20" height="20" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2"><path d="M20 6L9 17l-5-5"/></svg></span>
          <div><h4>We stay honest</h4><p>If a parcel has a wet corner, a rollback risk or a shaky right-of-way, we'll tell you. A good fit matters more to us than a fast close.</p></div>
        </div>
      </div>
    </div>
  </section>

  <!-- ============================= CTA BAND ============================= -->
  <section class="section">
    <div class="wrap">
      <div class="cta-band reveal">
        <h2>Talk to a Keystone agent</h2>
        <p>Reach the office at (717) 555-0455, or book a no-pressure call and we'll match you with the agent who knows your corner of Adams County best.</p>
        <div class="cta-actions">
          <a class="btn btn-gold" href="{{ home_url('/guide/') }}#schedule">Book a Call</a>
          <a class="btn btn-outline light" href="{{ home_url('/contact/') }}">Contact the Office</a>
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
