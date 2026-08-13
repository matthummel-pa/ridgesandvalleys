{{--
  Template Name: Contact & Schedule a Showing
--}}

@extends('layouts.app')

@push('head')
<script type="application/ld+json">
{
  "@@context": "https://schema.org",
  "@@type": "RealEstateAgent",
  "@@id": "{{ home_url('/') }}#business",
  "name": "Ridgeline Realty",
  "description": "Contact Ridgeline Realty to schedule a showing or reach an agent in Gettysburg and Adams County, Pennsylvania.",
  "image": "{{ home_url('/preview.jpg/') }}",
  "url": "{{ home_url('/contact/') }}",
  "telephone": "+1-717-555-0210",
  "email": "info@@ridgelinerealty.test",
  "priceRange": "$$",
  "address": {
    "@@type": "PostalAddress",
    "streetAddress": "210 Chambersburg Road",
    "addressLocality": "Gettysburg",
    "addressRegion": "PA",
    "postalCode": "17325",
    "addressCountry": "US"
  },
  "geo": { "@@type": "GeoCoordinates", "latitude": 39.8309, "longitude": -77.2311 },
  "openingHoursSpecification": [
    { "@@type": "OpeningHoursSpecification", "dayOfWeek": ["Monday","Tuesday","Wednesday","Thursday","Friday"], "opens": "09:00", "closes": "18:00" },
    { "@@type": "OpeningHoursSpecification", "dayOfWeek": "Saturday", "opens": "10:00", "closes": "15:00" }
  ],
  "areaServed": [
    { "@@type": "City", "name": "Gettysburg, PA" },
    { "@@type": "City", "name": "Biglerville, PA" },
    { "@@type": "City", "name": "Littlestown, PA" },
    { "@@type": "City", "name": "New Oxford, PA" },
    { "@@type": "City", "name": "McSherrystown, PA" },
    { "@@type": "City", "name": "Fairfield, PA" },
    { "@@type": "Place", "name": "Cashtown, PA" },
    { "@@type": "Place", "name": "Straban Township, PA" },
    { "@@type": "Place", "name": "Cumberland Township, PA" },
    { "@@type": "Place", "name": "Mount Joy Township, PA" },
    { "@@type": "AdministrativeArea", "name": "Adams County, PA" }
  ]
}
</script>
<script type="application/ld+json">
{
  "@@context": "https://schema.org",
  "@@type": "BreadcrumbList",
  "itemListElement": [
    { "@@type": "ListItem", "position": 1, "name": "Home", "item": "{{ home_url('/') }}" },
    { "@@type": "ListItem", "position": 2, "name": "Contact", "item": "{{ home_url('/contact/') }}" }
  ]
}
</script>
@endpush

@section('content')
<a href="#main" class="skip-link">Skip to main content</a>

<header class="site-header">
  <div class="nav-wrap">
    <a href="{{ home_url('/') }}" class="brand" aria-label="Ridgeline Realty home">
      <span class="brand-mark" aria-hidden="true">
        <svg viewBox="0 0 24 24" fill="none" stroke="#0c1c2c" stroke-width="2" stroke-linecap="round" stroke-linejoin="round"><path d="M3 20l5-9 4 6 3-5 6 8"/><path d="M3 20h18"/></svg>
      </span>
      <span class="brand-text"><strong>Ridgeline Realty</strong><span>Gettysburg &amp; Adams County</span></span>
    </a>

    <nav class="primary-nav" aria-label="Primary">
      <ul>
        <li><a href="{{ home_url('/listings/') }}">Listings</a></li>
        <li><a href="{{ home_url('/areas/') }}">Areas We Serve</a></li>
        <li><a href="{{ home_url('/sell/') }}">Sell Your Home</a></li>
        <li><a href="{{ home_url('/agents/') }}">Our Agents</a></li>
        <li><a href="{{ home_url('/contact/') }}" class="is-active" aria-current="page">Contact</a></li>
      </ul>
    </nav>

    <div class="header-cta">
      <a class="header-phone" href="tel:+17175550210">(717) 555-0210</a>
      <a class="btn btn-primary btn-sm" href="{{ home_url('/contact/') }}#schedule">Schedule a Showing</a>
    </div>

    <button class="hamburger" id="hamburgerBtn" aria-label="Open menu" aria-expanded="false" aria-controls="mobileNav">
      <span></span>
    </button>
  </div>

  <nav class="mobile-nav" id="mobileNav" aria-label="Mobile">
    <a href="{{ home_url('/listings/') }}">Listings</a>
    <a href="{{ home_url('/areas/') }}">Areas We Serve</a>
    <a href="{{ home_url('/sell/') }}">Sell Your Home</a>
    <a href="{{ home_url('/agents/') }}">Our Agents</a>
    <a href="{{ home_url('/contact/') }}" class="is-active" aria-current="page">Contact</a>
    <div class="mobile-cta">
      <a class="btn btn-primary" style="width:100%" href="{{ home_url('/contact/') }}#schedule">Schedule a Showing</a>
    </div>
  </nav>
</header>

<nav class="breadcrumb" aria-label="Breadcrumb">
  <div class="container">
    <ol>
      <li><a href="{{ home_url('/') }}">Home</a></li>
      <li class="sep" aria-hidden="true">/</li>
      <li aria-current="page">Contact</li>
    </ol>
  </div>
</nav>

<main id="main">

  <!-- ===== PAGE HERO ===== -->
  <section class="hero hero-compact">
    <div class="container hero-inner">
      <span class="eyebrow">Let's Talk</span>
      <h1>Schedule a showing or <em>reach an agent</em></h1>
      <p class="hero-sub">Our office is at 210 Chambersburg Road in Gettysburg. Call, email, or use a form below — a real Ridgeline agent will get back to you the same day.</p>
    </div>
  </section>

  <!-- ===== CONTACT DETAILS ===== -->
  <section class="contact-section">
    <div class="container">
      <div class="contact-detail-grid">
        <div class="contact-detail reveal">
          <h3>Visit or write</h3>
          <address>
            <strong>Ridgeline Realty</strong><br>
            210 Chambersburg Road<br>
            Gettysburg, PA 17325<br>
            <a href="mailto:info@@ridgelinerealty.test">info@@ridgelinerealty.test</a>
          </address>
        </div>
        <div class="contact-detail reveal">
          <h3>Call or text</h3>
          <p><a href="tel:+17175550210">(717) 555-0210</a><br>
          We answer the phone — from first showing to closing.</p>
        </div>
        <div class="contact-detail reveal">
          <h3>Office hours</h3>
          <p>Mon–Fri: 9:00am–6:00pm<br>
          Sat: 10:00am–3:00pm<br>
          Sun: By appointment</p>
        </div>
      </div>

      <div class="section-head reveal">
        <span class="eyebrow">Two Easy Ways</span>
        <h2>Book a tour or send a message</h2>
      </div>

      <div class="forms-grid">
        <div class="form-card reveal" id="schedule">
          <h3>Schedule a showing</h3>
          <form id="showingForm" novalidate>
            <div class="field">
              <label for="showProperty">Property</label>
              <select id="showProperty"></select>
            </div>
            <div class="two-col-fields">
              <div class="field">
                <label for="showDate">Preferred date</label>
                <input type="date" id="showDate" required>
              </div>
              <div class="field">
                <label for="showTime">Preferred time</label>
                <input type="time" id="showTime" required>
              </div>
            </div>
            <div class="two-col-fields">
              <div class="field">
                <label for="showName">Full name</label>
                <input type="text" id="showName" placeholder="Your name" required>
              </div>
              <div class="field">
                <label for="showPhone">Phone</label>
                <input type="tel" id="showPhone" placeholder="(717) 555-0100" required>
              </div>
            </div>
            <button type="submit" class="btn btn-dark">Request Showing</button>
            <p class="form-note">Demo form — no data is submitted or stored.</p>
            <div class="form-confirm" id="showConfirm" role="status" aria-live="polite"></div>
          </form>
        </div>

        <div class="form-card reveal">
          <h3>Contact an agent</h3>
          <form id="contactForm" novalidate>
            <div class="field">
              <label for="cName">Full name</label>
              <input type="text" id="cName" placeholder="Your name" required>
            </div>
            <div class="two-col-fields">
              <div class="field">
                <label for="cEmail">Email</label>
                <input type="email" id="cEmail" placeholder="you@@email.com" required>
              </div>
              <div class="field">
                <label for="cPhone">Phone</label>
                <input type="tel" id="cPhone" placeholder="(717) 555-0100">
              </div>
            </div>
            <div class="field">
              <label for="cMessage">Message</label>
              <textarea id="cMessage" rows="4" placeholder="Tell us what you're looking for..." required></textarea>
            </div>
            <button type="submit" class="btn btn-dark">Send Message</button>
            <p class="form-note">Demo form — no data is submitted or stored.</p>
            <div class="form-confirm" id="contactConfirm" role="status" aria-live="polite"></div>
          </form>
        </div>
      </div>
    </div>
  </section>

  <!-- ===== CTA BAND ===== -->
  <section class="cta-band">
    <div class="container">
      <span class="eyebrow">While You're Here</span>
      <h2>Browse homes or check your home's value</h2>
      <p>See what's on the market across Gettysburg and Adams County, or find out what your own home could sell for today.</p>
      <div class="cta-actions">
        <a href="{{ home_url('/listings/') }}" class="btn btn-primary">Browse Listings</a>
        <a href="{{ home_url('/sell/') }}" class="btn btn-outline">Get a Free Valuation</a>
      </div>
    </div>
  </section>

</main>

<!-- ===== FOOTER ===== -->
<footer class="site-footer">
  <div class="container">
    <div class="footer-grid">
      <div class="footer-brand">
        <a href="{{ home_url('/') }}" class="brand">
          <span class="brand-mark" aria-hidden="true">
            <svg viewBox="0 0 24 24" fill="none" stroke="#0c1c2c" stroke-width="2" stroke-linecap="round" stroke-linejoin="round"><path d="M3 20l5-9 4 6 3-5 6 8"/><path d="M3 20h18"/></svg>
          </span>
          <span class="brand-text"><strong>Ridgeline Realty</strong><span>Gettysburg &amp; Adams County</span></span>
        </a>
        <p>Locally owned and operated, connecting Adams County families with in-town homes, historic properties, and new builds since 2016.</p>
        <div class="social-row">
          <a href="#" aria-label="Ridgeline Realty on Facebook"><svg viewBox="0 0 24 24" stroke-width="1.8"><path d="M14 9h3V6h-3a4 4 0 0 0-4 4v2H8v3h2v6h3v-6h3l1-3h-4v-1a1 1 0 0 1 1-2z"/></svg></a>
          <a href="#" aria-label="Ridgeline Realty on Instagram"><svg viewBox="0 0 24 24" stroke-width="1.8"><rect x="3" y="3" width="18" height="18" rx="5"/><circle cx="12" cy="12" r="4"/><circle cx="17.5" cy="6.5" r="1"/></svg></a>
          <a href="#" aria-label="Ridgeline Realty on LinkedIn"><svg viewBox="0 0 24 24" stroke-width="1.8"><rect x="3" y="3" width="18" height="18" rx="3"/><path d="M7 10v7"/><path d="M7 7v.01"/><path d="M11 17v-4a2 2 0 0 1 4 0v4"/><path d="M11 13v4"/></svg></a>
        </div>
      </div>

      <div class="footer-col">
        <h4>Office</h4>
        <address>
          Ridgeline Realty<br>
          210 Chambersburg Road<br>
          Gettysburg, PA 17325<br>
          <a href="tel:+17175550210">(717) 555-0210</a><br>
          <a href="mailto:info@@ridgelinerealty.test">info@@ridgelinerealty.test</a>
        </address>
      </div>

      <div class="footer-col">
        <h4>Hours</h4>
        <address>
          Mon–Fri: 9:00am–6:00pm<br>
          Sat: 10:00am–3:00pm<br>
          Sun: By appointment
        </address>
        <h4 style="margin-top:22px;">Quick Links</h4>
        <ul>
          <li><a href="{{ home_url('/listings/') }}">Listings</a></li>
          <li><a href="{{ home_url('/sell/') }}">Sell Your Home</a></li>
          <li><a href="{{ home_url('/contact/') }}">Contact</a></li>
        </ul>
      </div>

      <div class="footer-col">
        <h4>Explore</h4>
        <ul>
          <li><a href="{{ home_url('/') }}">Home</a></li>
          <li><a href="{{ home_url('/listings/') }}">Listings</a></li>
          <li><a href="{{ home_url('/areas/') }}">Areas We Serve</a></li>
          <li><a href="{{ home_url('/agents/') }}">Our Agents</a></li>
          <li><a href="{{ home_url('/sell/') }}">Sell Your Home</a></li>
          <li><a href="{{ home_url('/contact/') }}">Contact</a></li>
        </ul>
      </div>
    </div>

    <p class="footer-service-note">Proudly serving Gettysburg plus Biglerville, Littlestown, New Oxford, McSherrystown, Fairfield, Cashtown, and the surrounding Adams County townships, Pennsylvania.</p>

    <div class="footer-bottom">
      <div class="equal-housing">
        <svg viewBox="0 0 24 24" stroke-width="1.8"><path d="M3 11l9-7 9 7"/><path d="M5 10v10h14V10"/><path d="M9 20v-6h6v6"/></svg>
        <span>Equal Housing Opportunity</span>
      </div>
      <p>&copy; <span data-current-year>2026</span> Ridgeline Realty. Design concept by Ridges &amp; Valleys Studio — a Gettysburg, PA web studio — created to show a prospective client their future site. Not a licensed real-estate brokerage. All listings, prices, and testimonials are illustrative.</p>
    </div>
  </div>
</footer>

<!-- ===== CHAT WIDGET ===== -->
<div class="chat-widget">
  <div class="chat-panel" id="chatPanel">
    <div class="chat-head">
      <div><strong>Ridgeline Assistant</strong><br><span>Usually replies in minutes</span></div>
      <button class="chat-close" id="chatCloseBtn" aria-label="Close chat">&times;</button>
    </div>
    <div class="chat-body" id="chatBody">
      <div class="chat-msg">Hi! I'm the Ridgeline assistant (demo). Ask me something below.</div>
    </div>
    <div class="chat-quick">
      <button type="button" data-reply="worth">What's my home worth?</button>
      <button type="button" data-reply="showing">Schedule a showing</button>
      <button type="button" data-reply="agent">Talk to an agent</button>
      <button type="button" data-reply="areas">Areas you serve?</button>
    </div>
  </div>
  <button class="chat-toggle" id="chatToggleBtn" aria-label="Open chat assistant" aria-expanded="false">
    <svg viewBox="0 0 24 24"><path d="M21 11.5a8.38 8.38 0 0 1-.9 3.8 8.5 8.5 0 0 1-7.6 4.7 8.38 8.38 0 0 1-3.8-.9L3 21l1.9-5.7a8.38 8.38 0 0 1-.9-3.8 8.5 8.5 0 0 1 4.7-7.6 8.38 8.38 0 0 1 3.8-.9h.5a8.48 8.48 0 0 1 8 8v.5z"/></svg>
  </button>
</div>

<a href="#" class="concept-badge">Concept &middot; Ridges &amp; Valleys Studio</a>
<script>
(function(){
  "use strict";

  /* ---------- Populate the "property" select for scheduling ---------- */
  var properties = [
    { id:'l1', label:'214 Baltimore Street — Gettysburg, PA 17325' },
    { id:'l2', label:'88 Steinwehr Avenue, Unit 3 — Gettysburg, PA 17325' },
    { id:'l3', label:'1450 Table Rock Road — Cumberland Township, PA 17325' },
    { id:'l4', label:'622 Chambersburg Street — Gettysburg, PA 17325' },
    { id:'l5', label:"77 Herr's Ridge Road — Franklin Township, PA 17325" },
    { id:'l6', label:'12 Cider Press Lane — Biglerville, PA 17307' },
    { id:'l7', label:'305 Bullfrog Road — Straban Township, PA 17325' },
    { id:'l8', label:'45 Springs Avenue — Gettysburg, PA 17325' }
  ];
  var showProperty = document.getElementById('showProperty');
  if(showProperty){
    var general = document.createElement('option');
    general.value = '';
    general.textContent = 'General inquiry / not sure yet';
    showProperty.appendChild(general);
    properties.forEach(function(p){
      var opt = document.createElement('option');
      opt.value = p.id;
      opt.textContent = p.label;
      showProperty.appendChild(opt);
    });
    /* Preselect if arriving from a listing (contact.html?property=l3#schedule) */
    var params = new URLSearchParams(window.location.search);
    var pre = params.get('property');
    if(pre){
      var match = properties.some(function(p){ return p.id === pre; });
      if(match){ showProperty.value = pre; }
    }
  }

  /* ---------- Demo form submissions ---------- */
  function demoSubmit(formId, confirmId, message){
    var form = document.getElementById(formId);
    var confirmBox = document.getElementById(confirmId);
    if(!form || !confirmBox) return;
    form.addEventListener('submit', function(e){
      e.preventDefault();
      confirmBox.textContent = message;
      confirmBox.classList.add('show');
      form.reset();
      setTimeout(function(){ confirmBox.classList.remove('show'); }, 6000);
    });
  }
  demoSubmit('showingForm', 'showConfirm', "Showing requested! This is a concept site, so nothing was actually booked — on the live version, an agent would confirm your time within a few hours.");
  demoSubmit('contactForm', 'contactConfirm', "Message sent! This is a concept site, so nothing was actually delivered — on the live version, an agent would reply directly.");

})();
</script>
@endsection
