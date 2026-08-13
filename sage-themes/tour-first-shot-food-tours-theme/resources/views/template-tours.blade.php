{{--
  Template Name: Gettysburg Food & History Tours
--}}

@extends('layouts.app')

@push('head')
<script type="application/ld+json">
{
  "@@context": "https://schema.org",
  "@@type": "TravelAgency",
  "@@id": "{{ home_url('/') }}#business",
  "name": "First Shot Food & History Tours",
  "image": "{{ home_url('/preview.jpg/') }}",
  "url": "{{ home_url('/tours/') }}",
  "telephone": "+1-717-555-0105",
  "email": "book@@firstshottours.test",
  "priceRange": "$$",
  "description": "Four small-group food and history walking tours through downtown Gettysburg, Pennsylvania.",
  "address": {
    "@@type": "PostalAddress",
    "streetAddress": "5 Chambersburg Street",
    "addressLocality": "Gettysburg",
    "addressRegion": "PA",
    "postalCode": "17325",
    "addressCountry": "US"
  },
  "geo": { "@@type": "GeoCoordinates", "latitude": 39.8313, "longitude": -77.2311 },
  "openingHoursSpecification": [
    { "@@type": "OpeningHoursSpecification", "dayOfWeek": ["Monday","Tuesday","Wednesday","Thursday","Friday"], "opens": "09:00", "closes": "17:00" }
  ],
  "areaServed": [
    { "@@type": "City", "name": "Gettysburg" },
    { "@@type": "AdministrativeArea", "name": "Adams County, Pennsylvania" }
  ],
  "makesOffer": [
    { "@@type": "Offer", "name": "First Shot Founders Walk", "price": "69", "priceCurrency": "USD", "category": "Food and history walking tour" },
    { "@@type": "Offer", "name": "Twilight Taps & Tales", "price": "79", "priceCurrency": "USD", "category": "Evening tavern history tour" },
    { "@@type": "Offer", "name": "Sweet Street Stroll", "price": "49", "priceCurrency": "USD", "category": "Family bakery walking tour" },
    { "@@type": "Offer", "name": "Farm-to-Table Founders Tour", "price": "89", "priceCurrency": "USD", "category": "Extended food and history tour" }
  ]
}
</script>
<script type="application/ld+json">
{
  "@@context": "https://schema.org",
  "@@type": "BreadcrumbList",
  "itemListElement": [
    { "@@type": "ListItem", "position": 1, "name": "Home", "item": "{{ home_url('/') }}" },
    { "@@type": "ListItem", "position": 2, "name": "Tours", "item": "{{ home_url('/tours/') }}" }
  ]
}
</script>
@endpush

@section('content')
<a class="skip-link" href="#main">Skip to main content</a>

<header class="site">
  <div class="container nav-wrap">
    <a class="brand" href="{{ home_url('/') }}" aria-label="First Shot Food &amp; History Tours, home">
      <svg class="brand-mark" viewBox="0 0 48 48" fill="none" aria-hidden="true">
        <circle cx="24" cy="24" r="22" fill="#C1592C"/>
        <path d="M24 10 L30 22 L24 20 L18 22 Z" fill="#FBF2E4"/>
        <rect x="22.5" y="20" width="3" height="16" rx="1.5" fill="#FBF2E4"/>
        <circle cx="24" cy="34" r="3.4" fill="#E3A93F"/>
      </svg>
      <span class="brand-text"><strong>First Shot</strong><span>Food &amp; History Tours</span></span>
    </a>
    <nav class="primary-nav" aria-label="Primary">
      <ul>
        <li><a href="{{ home_url('/') }}">Home</a></li>
        <li><a href="{{ home_url('/tours/') }}" class="is-active" aria-current="page">Tours</a></li>
        <li><a href="{{ home_url('/book/') }}">Book a Date</a></li>
        <li><a href="{{ home_url('/route/') }}">The Route</a></li>
        <li><a href="{{ home_url('/faq/') }}">FAQ</a></li>
        <li><a href="{{ home_url('/contact/') }}">Contact</a></li>
      </ul>
    </nav>
    <div class="header-actions">
      <a class="btn btn-primary btn-sm" href="{{ home_url('/book/') }}">Book a Date</a>
      <button class="hamburger" id="hamburgerBtn" aria-expanded="false" aria-controls="mobileMenu" aria-label="Open menu">
        <span></span><span></span><span></span>
      </button>
    </div>
  </div>
</header>

<div class="mobile-menu" id="mobileMenu">
  <nav aria-label="Mobile">
    <ul>
      <li><a href="{{ home_url('/') }}">Home</a></li>
      <li><a href="{{ home_url('/tours/') }}" class="is-active" aria-current="page">Tours</a></li>
      <li><a href="{{ home_url('/book/') }}">Book a Date</a></li>
      <li><a href="{{ home_url('/route/') }}">The Route</a></li>
      <li><a href="{{ home_url('/faq/') }}">FAQ</a></li>
      <li><a href="{{ home_url('/contact/') }}">Contact</a></li>
    </ul>
    <a class="btn btn-primary btn-block" href="{{ home_url('/book/') }}">Book a Date</a>
  </nav>
</div>

<main id="main">

  <section class="page-hero">
    <div class="container">
      <nav class="breadcrumb" aria-label="Breadcrumb">
        <ol>
          <li><a href="{{ home_url('/') }}">Home</a></li>
          <li class="sep" aria-hidden="true">/</li>
          <li aria-current="page">Tours</li>
        </ol>
      </nav>
      <span class="eyebrow" style="color:var(--mustard);">The Tours</span>
      <h1>Four ways to taste <em>downtown Gettysburg</em>.</h1>
      <p>Every First Shot tour starts on or near Lincoln Square and threads the Gettysburg Historic District on foot &mdash; the difference is the length, the crowd, and what's on the plate. All routes are led by a certified Adams County history interpreter and capped at twelve guests.</p>
    </div>
  </section>

  <section class="bg-paper">
    <div class="container">
      <div class="section-head reveal">
        <span class="eyebrow">Compare the Routes</span>
        <h2>Pick the walk that fits your day.</h2>
        <p>Adult pricing shown below; child tickets (ages 5&ndash;12) are available on all family-friendly routes. Choose a tour and carry it straight into the booking calendar.</p>
      </div>
      <div class="tour-grid reveal">
        <article class="tour-card">
          <span class="theme-tag">Flagship history + tastings</span>
          <h3>First Shot Founders Walk</h3>
          <p class="tour-desc">Our original route: Lincoln Square to the Historic District, tracing the town's three days under occupation with four full tastings along the way. The best all-round introduction to the town and its food.</p>
          <p class="tour-stops"><strong>4 stops</strong> &middot; about 1.5 miles</p>
          <div class="tour-meta-row"><span>2.5 hours</span><span class="tour-price">$69<small> / adult &middot; $39 child</small></span></div>
          <a class="btn btn-teal btn-block" href="{{ home_url('/book/') }}">Book this tour</a>
        </article>
        <article class="tour-card">
          <span class="theme-tag">Evening tavern history (21+)</span>
          <h3>Twilight Taps &amp; Tales</h3>
          <p class="tour-desc">An after-dark walk through the taverns that billeted soldiers on both sides, with three tastings including a local cider flight. Lantern-lit stories along Baltimore Street and Steinwehr Avenue.</p>
          <p class="tour-stops"><strong>3 stops</strong> &middot; adults only</p>
          <div class="tour-meta-row"><span>2 hours</span><span class="tour-price">$79<small> / adult</small></span></div>
          <a class="btn btn-teal btn-block" href="{{ home_url('/book/') }}">Book this tour</a>
        </article>
        <article class="tour-card">
          <span class="theme-tag">Family-friendly bakery route</span>
          <h3>Sweet Street Stroll</h3>
          <p class="tour-desc">A shorter, kid-friendly loop built around Gettysburg's bakeries and creamery &mdash; lighter history, heavier on dessert. Strollers welcome and the pace is gentle.</p>
          <p class="tour-stops"><strong>3 stops</strong> &middot; about 0.9 miles</p>
          <div class="tour-meta-row"><span>1.5 hours</span><span class="tour-price">$49<small> / adult &middot; $29 child</small></span></div>
          <a class="btn btn-teal btn-block" href="{{ home_url('/book/') }}">Book this tour</a>
        </article>
        <article class="tour-card">
          <span class="theme-tag">Extended history + Adams County farms</span>
          <h3>Farm-to-Table Founders Tour</h3>
          <p class="tour-desc">Our longest walk adds a stop featuring produce from nearby Adams County orchards and farm stands alongside the full downtown history route. Best in apple season, September through November.</p>
          <p class="tour-stops"><strong>5 stops</strong> &middot; about 1.8 miles</p>
          <div class="tour-meta-row"><span>3 hours</span><span class="tour-price">$89<small> / adult &middot; $49 child</small></span></div>
          <a class="btn btn-teal btn-block" href="{{ home_url('/book/') }}">Book this tour</a>
        </article>
      </div>
    </div>
  </section>

  <section class="bg-ink">
    <div class="container">
      <div class="section-head reveal">
        <span class="eyebrow" style="color:var(--mustard);">What's Included</span>
        <h2 style="color:#fff;">Every ticket includes the full walk and the full plate.</h2>
        <p class="ink-soft">No hidden add-ons required to enjoy the tour &mdash; the optional extras at checkout are genuine upgrades, not gatekeeping.</p>
      </div>
      <div class="include-list reveal">
        <div class="include-item">
          <svg viewBox="0 0 24 24" fill="none" aria-hidden="true"><path d="M12 3l7 4v5c0 5-3.4 7.7-7 9-3.6-1.3-7-4-7-9V7l7-4z" stroke="currentColor" stroke-width="1.6"/></svg>
          <div><strong style="color:#fff;">Certified local guide</strong><span>Adams County history interpreter, licensed for street-walking tours in Gettysburg.</span></div>
        </div>
        <div class="include-item">
          <svg viewBox="0 0 24 24" fill="none" aria-hidden="true"><path d="M4 21h16M6 21V9l6-5 6 5v12M10 21v-6h4v6" stroke="currentColor" stroke-width="1.6" stroke-linejoin="round"/></svg>
          <div><strong style="color:#fff;">3&ndash;5 tasting stops</strong><span>Full-size tastings, not samples &mdash; you'll want to skip the big lunch beforehand.</span></div>
        </div>
        <div class="include-item">
          <svg viewBox="0 0 24 24" fill="none" aria-hidden="true"><rect x="3" y="4" width="18" height="14" rx="2" stroke="currentColor" stroke-width="1.6"/><path d="M3 9h18" stroke="currentColor" stroke-width="1.6"/></svg>
          <div><strong style="color:#fff;">Printable route map</strong><span>Sent after your tour so you can revisit favorite stops on your own.</span></div>
        </div>
        <div class="include-item">
          <svg viewBox="0 0 24 24" fill="none" aria-hidden="true"><path d="M5 13l4 4L19 7" stroke="currentColor" stroke-width="1.8" stroke-linecap="round" stroke-linejoin="round"/></svg>
          <div><strong style="color:#fff;">Dietary accommodation</strong><span>Vegetarian and gluten-aware swaps available at every stop with 24 hours' notice.</span></div>
        </div>
      </div>
    </div>
  </section>

  <section class="bg-cream-dim">
    <div class="container">
      <div class="section-head reveal">
        <span class="eyebrow">Optional Add-ons</span>
        <h2>Make it your own.</h2>
        <p>Add any of these when you book &mdash; they're priced per booking and update your total live in the calendar.</p>
      </div>
      <div class="info-grid reveal">
        <div class="info-card">
          <h3>Self-guided audio companion &mdash; +$8</h3>
          <p>Bonus stories and stop-by-stop narration you keep on your phone after the tour ends, so you can revisit the route on your own time.</p>
        </div>
        <div class="info-card">
          <h3>Extra tasting course &mdash; +$12</h3>
          <p>One additional stop, chef's choice, from a partner eatery along the route. A favorite on the Founders and Farm-to-Table walks.</p>
        </div>
        <div class="info-card">
          <h3>Private group upgrade &mdash; +$150</h3>
          <p>Just your party on the walk, with a flexible departure time. Ideal for birthdays, reunions, and corporate outings in Gettysburg.</p>
        </div>
      </div>
    </div>
  </section>

  <section class="cta-band">
    <div class="container">
      <div>
        <h2>Found your tour? Grab a date.</h2>
        <p>Your tour choice carries over to the calendar automatically &mdash; just pick a day and time.</p>
      </div>
      <div class="cta-actions">
        <a class="btn btn-ghost" href="{{ home_url('/route/') }}">See the full route</a>
        <a class="btn btn-primary" href="{{ home_url('/book/') }}">Book a date</a>
      </div>
    </div>
  </section>

</main>

<footer class="site">
  <div class="container">
    <div class="footer-grid">
      <div class="footer-brand">
        <a class="brand" href="{{ home_url('/') }}" style="color:#fff;">
          <svg class="brand-mark" viewBox="0 0 48 48" fill="none" aria-hidden="true">
            <circle cx="24" cy="24" r="22" fill="#C1592C"/>
            <path d="M24 10 L30 22 L24 20 L18 22 Z" fill="#FBF2E4"/>
            <rect x="22.5" y="20" width="3" height="16" rx="1.5" fill="#FBF2E4"/>
            <circle cx="24" cy="34" r="3.4" fill="#E3A93F"/>
          </svg>
          <span class="brand-text"><strong style="color:#fff;">First Shot</strong><span style="color:var(--teal-light);">Food &amp; History Tours</span></span>
        </a>
        <p>Small-group walking tours through downtown Gettysburg &mdash; real history, real food, real local eateries. Est. 2018.</p>
        <div class="social-row">
          <a href="#" aria-label="First Shot Tours on Instagram"><svg viewBox="0 0 24 24" fill="none"><rect x="3" y="3" width="18" height="18" rx="5" stroke="currentColor" stroke-width="1.6"/><circle cx="12" cy="12" r="4" stroke="currentColor" stroke-width="1.6"/><circle cx="17.3" cy="6.7" r="1" fill="currentColor"/></svg></a>
          <a href="#" aria-label="First Shot Tours on Facebook"><svg viewBox="0 0 24 24" fill="none"><path d="M14 9h3V6h-3c-2 0-3.5 1.5-3.5 3.5V11H8v3h2.5v6h3v-6H16l.5-3h-3V9.8c0-.5.2-.8.7-.8z" fill="currentColor"/></svg></a>
          <a href="#" aria-label="First Shot Tours on TripAdvisor"><svg viewBox="0 0 24 24" fill="none"><circle cx="8" cy="13" r="3.2" stroke="currentColor" stroke-width="1.6"/><circle cx="16" cy="13" r="3.2" stroke="currentColor" stroke-width="1.6"/><path d="M8 7h8M12 13a4 4 0 0 1 0-6M12 13a4 4 0 0 0 0-6" stroke="currentColor" stroke-width="1.4"/></svg></a>
        </div>
      </div>
      <div>
        <h4>Explore</h4>
        <ul>
          <li><a href="{{ home_url('/') }}">Home</a></li>
          <li><a href="{{ home_url('/tours/') }}">Tours</a></li>
          <li><a href="{{ home_url('/book/') }}">Book a date</a></li>
          <li><a href="{{ home_url('/route/') }}">The route &amp; local eats</a></li>
          <li><a href="{{ home_url('/faq/') }}">FAQ</a></li>
          <li><a href="{{ home_url('/contact/') }}">Contact</a></li>
        </ul>
      </div>
      <div>
        <h4>Visit / Book</h4>
        <address class="footer-nap">
          <strong style="color:#fff;">First Shot Food &amp; History Tours</strong><br>
          5 Chambersburg Street<br>
          Gettysburg, PA 17325<br>
          <a href="tel:+17175550105">(717) 555-0105</a><br>
          <a href="mailto:book@@firstshottours.test">book@@firstshottours.test</a>
        </address>
      </div>
      <div>
        <h4>Hours</h4>
        <ul>
          <li>Booking desk: Mon&ndash;Fri, 9am&ndash;5pm</li>
          <li>Tours depart: Thu&ndash;Mon</li>
          <li>Season: April&ndash;November</li>
          <li>Closed Tue &amp; Wed</li>
        </ul>
      </div>
    </div>
    <p style="font-family:var(--font-mono); font-size:0.75rem; color:rgba(251,242,228,0.55); margin-top:2rem;">Serving Gettysburg and the wider Adams County area &mdash; Biglerville, New Oxford, Littlestown, McSherrystown, Fairfield, Cashtown, and Hanover.</p>
    <div class="footer-bottom">
      <span>&copy; <span id="footerYear"></span> First Shot Food &amp; History Tours. All rights reserved.</span>
      <span>Design concept by Ridges &amp; Valleys Studio, Gettysburg PA &mdash; not a live booking system.</span>
    </div>
  </div>
</footer>

<a class="concept-badge" href="#" onclick="event.preventDefault();" aria-label="This is a design concept by Ridges and Valleys Studio">
  <span class="dot" aria-hidden="true"></span> Concept &middot; Ridges &amp; Valleys Studio
</a>
@endsection
