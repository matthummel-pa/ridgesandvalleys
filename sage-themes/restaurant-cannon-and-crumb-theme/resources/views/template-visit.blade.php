{{--
  Template Name: Visit Us on Lincoln Square
--}}

@extends('layouts.app')

@push('head')
<script type="application/ld+json">
{
  "@@context": "https://schema.org",
  "@@type": "CafeOrCoffeeShop",
  "@@id": "{{ home_url('/') }}#business",
  "name": "Cannon & Crumb",
  "description": "All-day cafe and scratch bakery on Lincoln Square in downtown Gettysburg, PA — steps from the Gettysburg National Military Park and the town's historic core.",
  "image": "{{ home_url('/preview.jpg/') }}",
  "url": "{{ home_url('/visit/') }}",
  "telephone": "+1-717-555-0107",
  "email": "hello@@cannonandcrumb.test",
  "priceRange": "$$",
  "servesCuisine": ["Cafe", "Bakery", "Breakfast", "Brunch", "Coffee", "American"],
  "menu": "{{ home_url('/menu/') }}",
  "hasMenu": "{{ home_url('/menu/') }}",
  "hasMap": "https://www.openstreetmap.org/?mlat=39.8312&mlon=-77.2310#map=18/39.8312/-77.2310",
  "address": {
    "@@type": "PostalAddress",
    "streetAddress": "7 Lincoln Square",
    "addressLocality": "Gettysburg",
    "addressRegion": "PA",
    "postalCode": "17325",
    "addressCountry": "US"
  },
  "geo": { "@@type": "GeoCoordinates", "latitude": 39.8312, "longitude": -77.2310 },
  "openingHoursSpecification": [
    { "@@type": "OpeningHoursSpecification", "dayOfWeek": ["Monday", "Tuesday", "Wednesday", "Thursday"], "opens": "07:00", "closes": "15:00" },
    { "@@type": "OpeningHoursSpecification", "dayOfWeek": ["Friday", "Saturday"], "opens": "07:00", "closes": "20:00" },
    { "@@type": "OpeningHoursSpecification", "dayOfWeek": "Sunday", "opens": "08:00", "closes": "14:00" }
  ],
  "areaServed": [
    { "@@type": "City", "name": "Gettysburg" },
    { "@@type": "City", "name": "Biglerville" },
    { "@@type": "City", "name": "New Oxford" },
    { "@@type": "City", "name": "Littlestown" },
    { "@@type": "City", "name": "McSherrystown" },
    { "@@type": "City", "name": "Fairfield" },
    { "@@type": "City", "name": "Hanover" },
    { "@@type": "AdministrativeArea", "name": "Adams County, Pennsylvania" }
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

<header class="site-header">
  <div class="header-inner">
    <a href="{{ home_url('/') }}" class="brand" aria-label="Cannon & Crumb home">
      <span class="brand-mark" aria-hidden="true">
        <svg viewBox="0 0 24 24" fill="none" stroke="#fdf6ea" stroke-width="2" stroke-linecap="round" stroke-linejoin="round">
          <path d="M4 12h16M4 12a8 8 0 0 1 16 0M4 12v6a1 1 0 0 0 1 1h14a1 1 0 0 0 1-1v-6"/>
          <path d="M9 21v-3M15 21v-3"/>
        </svg>
      </span>
      <span class="brand-name">Cannon <span class="amp">&amp;</span> Crumb</span>
    </a>

    <nav class="main-nav" id="main-nav" aria-label="Primary">
      <a href="{{ home_url('/menu/') }}">Menu</a>
      <a href="{{ home_url('/order/') }}">Order Online</a>
      <a href="{{ home_url('/catering/') }}">Catering</a>
      <a href="{{ home_url('/visit/') }}" class="is-active" aria-current="page">Visit</a>
      <a href="{{ home_url('/contact/') }}">Contact</a>
    </nav>

    <div class="header-actions">
      <a class="icon-btn" id="cart-btn" href="{{ home_url('/order/') }}" aria-label="Order online">
        <svg viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round" aria-hidden="true">
          <circle cx="9" cy="21" r="1"/><circle cx="20" cy="21" r="1"/>
          <path d="M1 1h4l2.68 13.39a2 2 0 0 0 2 1.61h9.72a2 2 0 0 0 2-1.61L23 6H6"/>
        </svg>
        <span class="count-badge" id="cart-count" aria-hidden="true">0</span>
      </a>
      <button class="hamburger" id="hamburger" aria-expanded="false" aria-controls="main-nav" aria-label="Toggle menu">
        <span></span><span></span><span></span>
      </button>
    </div>
  </div>
</header>

<nav class="breadcrumb" aria-label="Breadcrumb">
  <ol>
    <li><a href="{{ home_url('/') }}">Home</a></li>
    <li class="sep" aria-hidden="true">/</li>
    <li aria-current="page">Visit Us</li>
  </ol>
</nav>

<main id="main">

  <section class="page-hero">
    <div class="container">
      <span class="eyebrow">Visit Us / The Area</span>
      <h1>Right on Lincoln Square, in the heart of Gettysburg.</h1>
      <p>You'll find Cannon &amp; Crumb at 7 Lincoln Square — the historic center of Gettysburg where Baltimore Street, Carlisle Street, Chambersburg Street, and York Street all meet. Walkable, easy to find, and steps from the town's biggest sights.</p>
    </div>
  </section>

  <!-- HOURS + MAP -->
  <section class="hours-section" id="hours">
    <div class="container">
      <div class="section-head reveal">
        <span class="eyebrow">Hours &amp; Location</span>
        <h2>When and where to find us</h2>
        <p>Open daily on the Square, with later hours Friday and Saturday.</p>
      </div>
      <div class="hours-grid">
        <div class="hours-card reveal">
          <h3>Hours</h3>
          <table class="hours-table">
            <tbody>
              <tr><td>Monday</td><td>7:00 AM – 3:00 PM</td></tr>
              <tr><td>Tuesday</td><td>7:00 AM – 3:00 PM</td></tr>
              <tr><td>Wednesday</td><td>7:00 AM – 3:00 PM</td></tr>
              <tr><td>Thursday</td><td>7:00 AM – 3:00 PM</td></tr>
              <tr><td>Friday</td><td>7:00 AM – 8:00 PM</td></tr>
              <tr><td>Saturday</td><td>7:00 AM – 8:00 PM</td></tr>
              <tr><td>Sunday</td><td>8:00 AM – 2:00 PM</td></tr>
            </tbody>
          </table>
          <div class="contact-line">
            <svg viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" aria-hidden="true"><path d="M21 10c0 7-9 13-9 13s-9-6-9-13a9 9 0 0 1 18 0z"/><circle cx="12" cy="10" r="3"/></svg>
            <span>7 Lincoln Square, Gettysburg, PA 17325</span>
          </div>
          <div class="contact-line">
            <svg viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" aria-hidden="true"><path d="M22 16.92v3a2 2 0 0 1-2.18 2 19.79 19.79 0 0 1-8.63-3.07 19.5 19.5 0 0 1-6-6 19.79 19.79 0 0 1-3.07-8.67A2 2 0 0 1 4.11 2h3a2 2 0 0 1 2 1.72c.127.96.361 1.903.7 2.81a2 2 0 0 1-.45 2.11L8.09 9.91a16 16 0 0 0 6 6l1.27-1.27a2 2 0 0 1 2.11-.45c.907.339 1.85.573 2.81.7A2 2 0 0 1 22 16.92z"/></svg>
            <span><a href="tel:+17175550107">(717) 555-0107</a></span>
          </div>
          <div class="contact-line">
            <svg viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" aria-hidden="true"><path d="M4 4h16v16H4zM4 4l8 7 8-7"/></svg>
            <span><a href="mailto:hello@@cannonandcrumb.test">hello@@cannonandcrumb.test</a></span>
          </div>
        </div>
        <div class="map-card reveal" role="img" aria-label="Stylized map showing Cannon & Crumb's location at Lincoln Square, where Baltimore, Carlisle, Chambersburg, and York Streets meet in downtown Gettysburg">
          <div class="map-road h" aria-hidden="true"></div>
          <div class="map-road v" aria-hidden="true"></div>
          <div class="map-pin">
            <svg viewBox="0 0 24 24" fill="currentColor" aria-hidden="true"><path d="M12 2C7.6 2 4 5.6 4 10c0 6 8 12 8 12s8-6 8-12c0-4.4-3.6-8-8-8zm0 11a3 3 0 1 1 0-6 3 3 0 0 1 0 6z"/></svg>
            <span class="pin-label">7 Lincoln Square</span>
          </div>
        </div>
      </div>
    </div>
  </section>

  <!-- LOCAL CONTENT (prose) -->
  <section class="story">
    <div class="container">
      <div class="prose reveal">
        <span class="eyebrow">The Area</span>
        <h2 style="font-size:clamp(1.9rem,4vw,2.6rem);">Your morning stop in downtown Gettysburg</h2>
        <p>Cannon &amp; Crumb sits right on <strong>Lincoln Square</strong>, the roundabout at the center of Gettysburg where the town's four main streets come together. If you're staying at one of the inns along <strong>Baltimore Street</strong> or <strong>Carlisle Street</strong>, we're an easy, flat walk — usually five minutes or less. It's the kind of spot you can roll out of bed and reach before your first cup of coffee wears off.</p>

        <h3>Walkable to the history you came for</h3>
        <p>Much of what makes Gettysburg special is within a short stroll of our front door. The <strong>David Wills House</strong>, where Abraham Lincoln finished the Gettysburg Address, is right on the Square — about a one-minute walk. Head south on Baltimore Street and you'll reach the <strong>Gettysburg National Military Park Museum &amp; Visitor Center</strong> area and the shops and eateries of <strong>Steinwehr Avenue</strong> in roughly 15–20 minutes on foot. The <strong>Soldiers' National Cemetery</strong> and the edges of the battlefield are a comfortable walk or a two-minute drive. Grab a boxed lunch or a coffee to go and take the history with you.</p>
        <p>For those exploring further, <strong>Seminary Ridge</strong> and the Lutheran Theological Seminary sit just west of downtown, while <strong>Little Round Top</strong>, <strong>the Peach Orchard</strong>, and <strong>Sachs Covered Bridge</strong> are short drives out along the park roads — all great destinations to fuel up for before you head out for the day.</p>

        <h3>Driving in &amp; where to park</h3>
        <p>Getting here is simple. From the north or south, take <strong>US-15</strong> to the <strong>US-30 (York Street / Chambersburg Street)</strong> exit and follow it straight into town — Lincoln Square is where you'll naturally end up. Coming from the east (Hanover, New Oxford) or west (Cashtown, Chambersburg), <strong>US-30</strong> runs right through the Square. From <strong>US-15</strong> you're about five minutes from our tables.</p>
        <p>Downtown Gettysburg uses metered on-street parking around the Square and along the main streets, plus the <strong>Racehorse Alley parking garage</strong> a block off the Square on Stratton Street for longer stays. Mornings are the easiest time to find a spot — one more reason to make us your first stop of the day.</p>

        <h3>Nearby towns we love (and serve)</h3>
        <p>We're proud to be part of the wider Adams County community. Whether you're visiting the apple country around <strong>Biglerville</strong>, antiquing in <strong>New Oxford</strong>, or coming in from <strong>Littlestown</strong>, <strong>McSherrystown</strong>, <strong>Fairfield</strong>, <strong>Cashtown</strong>, or <strong>Hanover</strong>, you're a short drive from a fresh-baked morning on the Square. Ask us about <a href="{{ home_url('/catering/') }}">catering</a> if you'd like us to bring Cannon &amp; Crumb to your town instead.</p>
      </div>

      <div class="town-list" aria-label="Nearby towns we serve">
        <span class="town-chip">Gettysburg</span>
        <span class="town-chip">Biglerville</span>
        <span class="town-chip">New Oxford</span>
        <span class="town-chip">Littlestown</span>
        <span class="town-chip">McSherrystown</span>
        <span class="town-chip">Fairfield</span>
        <span class="town-chip">Cashtown</span>
        <span class="town-chip">Hanover</span>
      </div>
    </div>
  </section>

  <!-- GETTING HERE CARDS -->
  <section>
    <div class="container">
      <div class="section-head reveal">
        <span class="eyebrow">Plan Your Trip</span>
        <h2>Everything you need to get here</h2>
      </div>
      <div class="info-grid">
        <div class="info-card reveal">
          <h3><svg viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round"><path d="M3 12h18M12 3v18"/><circle cx="12" cy="12" r="9"/></svg>Directions</h3>
          <p>From US-15, exit at US-30 and follow it into town to Lincoln Square. US-30 runs east–west straight through the Square.</p>
        </div>
        <div class="info-card reveal">
          <h3><svg viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round"><rect x="3" y="11" width="18" height="10" rx="2"/><path d="M7 11V7a5 5 0 0 1 10 0v4"/></svg>Parking</h3>
          <p>Metered on-street parking around the Square, plus the Racehorse Alley garage on Stratton Street a block away for longer visits.</p>
        </div>
        <div class="info-card reveal">
          <h3><svg viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round"><path d="M13 5v6l4 2M5 3l-2 4M19 3l2 4M12 21a7 7 0 1 0 0-14 7 7 0 0 0 0 14z"/></svg>On Foot</h3>
          <p>One minute to the David Wills House, ~15–20 minutes to Steinwehr Avenue and the park visitor-center area, all flat and walkable.</p>
        </div>
        <div class="info-card reveal">
          <h3><svg viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round"><circle cx="12" cy="12" r="9"/><path d="M12 7v5l3 2"/></svg>Best Time</h3>
          <p>Mornings are calmest for both parking and a quiet table. We open at 7am (8am Sundays) — beat the tour buses.</p>
        </div>
      </div>
    </div>
  </section>

  <!-- CTA BAND -->
  <section class="cta-band">
    <div class="container">
      <h2>See you on the Square</h2>
      <p>Order ahead so it's ready when you arrive, or browse the menu before you head downtown.</p>
      <div class="hero-actions">
        <a href="{{ home_url('/order/') }}" class="btn btn-primary" style="background:var(--espresso);">Order Ahead</a>
        <a href="{{ home_url('/menu/') }}" class="btn btn-outline">See the Menu</a>
      </div>
    </div>
  </section>

</main>

<!--#footer-->
<footer class="site-footer">
  <div class="container">
    <div class="footer-grid cols-4">
      <div>
        <div class="footer-brand">
          <span class="brand-mark" aria-hidden="true">
            <svg viewBox="0 0 24 24" fill="none" stroke="#fdf6ea" stroke-width="2" stroke-linecap="round" stroke-linejoin="round">
              <path d="M4 12h16M4 12a8 8 0 0 1 16 0M4 12v6a1 1 0 0 0 1 1h14a1 1 0 0 0 1-1v-6"/>
              <path d="M9 21v-3M15 21v-3"/>
            </svg>
          </span>
          Cannon &amp; Crumb
        </div>
        <p>An all-day cafe &amp; bakery in the heart of downtown Gettysburg. Scratch pastries, local sourcing, and coffee worth the walk to the Square.</p>
        <div class="social-row">
          <a href="#" aria-label="Cannon & Crumb on Instagram">
            <svg viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2"><rect x="2" y="2" width="20" height="20" rx="5"/><circle cx="12" cy="12" r="4"/><circle cx="17.5" cy="6.5" r="1"/></svg>
          </a>
          <a href="#" aria-label="Cannon & Crumb on Facebook">
            <svg viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2"><path d="M18 2h-3a5 5 0 0 0-5 5v3H7v4h3v8h4v-8h3l1-4h-4V7a1 1 0 0 1 1-1h3z"/></svg>
          </a>
          <a href="#" aria-label="Cannon & Crumb on TikTok">
            <svg viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2"><path d="M9 12a4 4 0 1 0 4 4V4a5 5 0 0 0 5 5"/></svg>
          </a>
        </div>
      </div>
      <div>
        <h4>Visit</h4>
        <address class="footer-nap">
          <p>Cannon &amp; Crumb</p>
          <p>7 Lincoln Square<br>Gettysburg, PA 17325</p>
          <a href="tel:+17175550107">(717) 555-0107</a>
          <a href="mailto:hello@@cannonandcrumb.test">hello@@cannonandcrumb.test</a>
        </address>
      </div>
      <div>
        <h4>Hours</h4>
        <ul class="footer-hours">
          <li><span>Mon–Thu</span><span>7a–3p</span></li>
          <li><span>Fri</span><span>7a–8p</span></li>
          <li><span>Sat</span><span>7a–8p</span></li>
          <li><span>Sun</span><span>8a–2p</span></li>
        </ul>
      </div>
      <div>
        <h4>Explore</h4>
        <ul>
          <li><a href="{{ home_url('/menu/') }}">Menu</a></li>
          <li><a href="{{ home_url('/order/') }}">Order Online</a></li>
          <li><a href="{{ home_url('/catering/') }}">Catering &amp; Events</a></li>
          <li><a href="{{ home_url('/visit/') }}">Visit Us / The Area</a></li>
          <li><a href="{{ home_url('/contact/') }}">Contact</a></li>
        </ul>
      </div>
    </div>
    <p class="footer-service-area">Proudly serving Gettysburg and the surrounding Adams County communities — Biglerville, New Oxford, Littlestown, McSherrystown, Fairfield, Cashtown, and Hanover.</p>
    <div class="footer-bottom">
      <span>© <span data-year>2026</span> Cannon &amp; Crumb. All rights reserved.</span>
      <span>Design concept by Ridges &amp; Valleys Studio, Gettysburg PA.</span>
    </div>
  </div>
</footer>

<a href="#" class="concept-badge" id="concept-badge">Concept · Ridges &amp; Valleys Studio</a>
@endsection
