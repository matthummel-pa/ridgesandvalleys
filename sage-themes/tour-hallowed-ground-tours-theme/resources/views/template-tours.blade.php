{{--
  Template Name: Gettysburg Battlefield Tours & Tickets
--}}

@extends('layouts.app')

@push('head')
<script type="application/ld+json">
{
  "@@context": "https://schema.org",
  "@@type": "TravelAgency",
  "@@id": "{{ home_url('/') }}#business",
  "name": "Hallowed Ground Battlefield Tours",
  "url": "{{ home_url('/tours/') }}",
  "image": "https://commons.wikimedia.org/wiki/Special:FilePath/Pennsylvania_-_Gettysburg_-_NARA_-_68148252_(cropped).jpg?width=1600",
  "description": "Licensed-guide walking, bus, and evening lantern tours of the Gettysburg battlefield in Gettysburg, Pennsylvania.",
  "telephone": "+17175550271",
  "email": "tours@@hallowedground.test",
  "priceRange": "$$",
  "address": {
    "@@type": "PostalAddress",
    "streetAddress": "271 Baltimore Street",
    "addressLocality": "Gettysburg",
    "addressRegion": "PA",
    "postalCode": "17325",
    "addressCountry": "US"
  },
  "geo": {
    "@@type": "GeoCoordinates",
    "latitude": 39.829,
    "longitude": -77.23
  },
  "openingHoursSpecification": [
    {
      "@@type": "OpeningHoursSpecification",
      "dayOfWeek": [
        "Monday",
        "Tuesday",
        "Wednesday",
        "Thursday",
        "Friday",
        "Saturday",
        "Sunday"
      ],
      "opens": "08:00",
      "closes": "18:00",
      "validFrom": "2026-04-01",
      "validThrough": "2026-11-30"
    },
    {
      "@@type": "OpeningHoursSpecification",
      "dayOfWeek": [
        "Thursday",
        "Friday",
        "Saturday",
        "Sunday"
      ],
      "opens": "09:00",
      "closes": "16:00",
      "validFrom": "2026-12-01",
      "validThrough": "2027-03-31"
    }
  ],
  "areaServed": [
    {
      "@@type": "City",
      "name": "Gettysburg, Pennsylvania"
    },
    {
      "@@type": "AdministrativeArea",
      "name": "Adams County, Pennsylvania"
    },
    {
      "@@type": "City",
      "name": "Biglerville, Pennsylvania"
    },
    {
      "@@type": "City",
      "name": "Littlestown, Pennsylvania"
    },
    {
      "@@type": "City",
      "name": "New Oxford, Pennsylvania"
    },
    {
      "@@type": "City",
      "name": "McSherrystown, Pennsylvania"
    },
    {
      "@@type": "City",
      "name": "Fairfield, Pennsylvania"
    },
    {
      "@@type": "City",
      "name": "Cashtown, Pennsylvania"
    },
    {
      "@@type": "City",
      "name": "Hanover, Pennsylvania"
    }
  ],
  "sameAs": [
    "https://www.facebook.com/",
    "https://www.instagram.com/",
    "https://www.tripadvisor.com/"
  ]
}
</script>
<script type="application/ld+json">
{
  "@@context": "https://schema.org",
  "@@type": "BreadcrumbList",
  "itemListElement": [
    {
      "@@type": "ListItem",
      "position": 1,
      "name": "Home",
      "item": "{{ home_url('/') }}"
    },
    {
      "@@type": "ListItem",
      "position": 2,
      "name": "Tours",
      "item": "{{ home_url('/tours/') }}"
    }
  ]
}
</script>
@endpush

@section('content')
<a href="#main" class="skip-link">Skip to main content</a>
<header class="site-header">
  <div class="wrap header-inner">
    <a href="{{ home_url('/') }}" class="brand">
      <svg class="brand-mark" width="34" height="34" viewBox="0 0 34 34" fill="none" aria-hidden="true"><path d="M2 26L10 12L15 19L20 9L32 26H2Z" fill="currentColor" opacity="0.9"/><circle cx="26" cy="7" r="3" fill="currentColor"/></svg>
      <span class="brand-text">
        <span class="brand-name">Hallowed Ground</span>
        <span class="brand-sub">Battlefield Tours</span>
      </span>
    </a>
    <nav class="main-nav" aria-label="Primary">
      <ul><li><a href="{{ home_url('/') }}">Home</a></li><li><a href="{{ home_url('/tours/') }}" class="is-active" aria-current="page">Tours</a></li><li><a href="{{ home_url('/guides/') }}">Our Guides</a></li><li><a href="{{ home_url('/area/') }}">The Area</a></li><li><a href="{{ home_url('/contact/') }}">Contact</a></li></ul>
    </nav>
    <div class="header-actions">
      <a href="tel:+17175550271" class="btn btn-ghost btn-sm" style="color:#fff;">(717) 555-0271</a>
      <a href="{{ home_url('/book/') }}" class="btn btn-primary btn-sm">Book a Tour</a>
      <button type="button" class="hamburger" id="hamburgerBtn" aria-expanded="false" aria-controls="mobileNav" aria-label="Open menu">
        <span></span><span></span><span></span>
      </button>
    </div>
  </div>
</header>

<nav class="mobile-nav" id="mobileNav" aria-label="Mobile">
  <ul><li><a href="{{ home_url('/') }}">Home</a></li><li><a href="{{ home_url('/tours/') }}" class="is-active" aria-current="page">Tours</a></li><li><a href="{{ home_url('/guides/') }}">Our Guides</a></li><li><a href="{{ home_url('/area/') }}">The Area</a></li><li><a href="{{ home_url('/contact/') }}">Contact</a></li></ul>
  <a href="tel:+17175550271" class="btn btn-outline btn-block" style="color:#fff;">Call (717) 555-0271</a>
  <a href="{{ home_url('/book/') }}" class="btn btn-primary btn-block" style="margin-top:0.8rem;">Book a Tour</a>
</nav>

<main id="main">
<nav class="breadcrumb" aria-label="Breadcrumb"><div class="wrap"><ol><li><a href="{{ home_url('/') }}">Home</a></li><li class="sep" aria-hidden="true">/</li><li aria-current="page">Tours</li></ol></div></nav>
  <section class="page-intro">
    <div class="wrap reveal">
      <span class="eyebrow">Tours &amp; Tickets</span>
      <h1>Gettysburg battlefield tours &amp; tickets</h1>
      <p>Choose from five licensed-guide experiences across the Gettysburg National Military Park — walking tours of Cemetery Ridge, a hike to Little Round Top and Devil's Den, an ADA-accessible bus loop, an evening lantern walk downtown, and a private sunrise tour.</p>
    </div>
  </section>

  <div class="info-strip">
    <div class="wrap">
      <div class="info-item">
        <svg width="20" height="20" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" aria-hidden="true"><path d="M12 15a4 4 0 100-8 4 4 0 000 8z"/><path d="M12 2a8 8 0 00-8 8c0 5.5 8 12 8 12s8-6.5 8-12a8 8 0 00-8-8z"/></svg>
        271 Baltimore Street, Gettysburg, PA
      </div>
      <div class="info-item">
        <svg width="20" height="20" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" aria-hidden="true"><circle cx="12" cy="12" r="9"/><path d="M12 7v5l3 2"/></svg>
        Tours depart daily, rain or shine
      </div>
      <div class="info-item">
        <svg width="20" height="20" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" aria-hidden="true"><path d="M20 21v-2a4 4 0 00-4-4H8a4 4 0 00-4 4v2"/><circle cx="12" cy="7" r="4"/></svg>
        Association-licensed guides
      </div>
      <div class="info-item">
        <svg width="20" height="20" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" aria-hidden="true"><path d="M22 16.92v3a2 2 0 01-2.18 2 19.79 19.79 0 01-8.63-3.07 19.5 19.5 0 01-6-6 19.79 19.79 0 01-3.07-8.67A2 2 0 014.11 2h3a2 2 0 012 1.72c.127.96.361 1.902.7 2.81a2 2 0 01-.45 2.11L8.09 9.91a16 16 0 006 6l1.27-1.27a2 2 0 012.11-.45c.908.339 1.85.573 2.81.7A2 2 0 0122 16.92z"/></svg>
        (717) 555-0271
      </div>
    </div>
  </div>


  <section class="section">
    <div class="wrap">
      <div class="section-head reveal">
        <span class="eyebrow">Choose Your Tour</span>
        <h2>Five ways to walk the field.</h2>
        <p>Every tour is led by an Association-licensed guide and capped at a small group size. Prices are per person; children are ages 6&ndash;12 and seniors are 65+. Select a tour to check live dates and book.</p>
      </div>
      <div class="tour-grid reveal">
        <article class="tour-card">
          <div class="tour-banner tb-1"><svg viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="1.8"><circle cx="12" cy="5" r="2"/><path d="M8 21l2-8-3-2 2-5 3 2 2-2 4 1-1 4-3-1-1 3 3 3-1 5"/></svg></div>
          <div class="tour-body">
            <div class="tour-meta">
              <span class="chip">2 hours</span>
              <span class="chip">Up to 15 guests</span>
              <span class="chip chip-moderate">Moderate · 2 mi walking</span>
            </div>
            <h3>Battlefield Highlights Walking Tour</h3>
            <p class="desc">A guided walk across Cemetery Ridge and McPherson Ridge covering the turning points of all three days of the battle.</p>
            <div class="tour-price-row"><span class="tour-price">$38<small> /adult</small></span></div>
            <a href="book.html?tour=highlights" class="btn btn-primary btn-block">Book This Tour</a>
          </div>
        </article>
        <article class="tour-card">
          <div class="tour-banner tb-2"><svg viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="1.8"><rect x="2" y="7" width="20" height="10" rx="2"/><circle cx="7" cy="19" r="1.6"/><circle cx="17" cy="19" r="1.6"/><path d="M2 12h20M7 7V4h10v3"/></svg></div>
          <div class="tour-body">
            <div class="tour-meta">
              <span class="chip">3.5 hours</span>
              <span class="chip">Up to 24 guests</span>
              <span class="chip chip-easy">Easy · Seated, ADA accessible</span>
            </div>
            <h3>Pickett&#x27;s Charge Deluxe Bus Tour</h3>
            <p class="desc">A narrated motorcoach loop of the full Gettysburg battlefield with two guided stops, including the High Water Mark.</p>
            <div class="tour-price-row"><span class="tour-price">$65<small> /adult</small></span></div>
            <a href="book.html?tour=bus" class="btn btn-primary btn-block">Book This Tour</a>
          </div>
        </article>
        <article class="tour-card">
          <div class="tour-banner tb-3"><svg viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="1.8"><path d="M2 20l6-11 4 6 3-5 7 10z"/><circle cx="18" cy="4" r="2"/></svg></div>
          <div class="tour-body">
            <div class="tour-meta">
              <span class="chip">2.5 hours</span>
              <span class="chip">Up to 12 guests</span>
              <span class="chip chip-strenuous">Strenuous · Rocky, hills</span>
            </div>
            <h3>Little Round Top &amp; Devil&#x27;s Den Hike</h3>
            <p class="desc">A rugged hike to the most fought-over high ground of Day Two, with time to explore the boulders of Devil&#x27;s Den.</p>
            <div class="tour-price-row"><span class="tour-price">$44<small> /adult</small></span></div>
            <a href="book.html?tour=hike" class="btn btn-primary btn-block">Book This Tour</a>
          </div>
        </article>
        <article class="tour-card">
          <div class="tour-banner tb-4"><svg viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="1.8"><path d="M9 3h6l1 4H8z"/><rect x="7" y="7" width="10" height="10" rx="1"/><path d="M10 21h4M12 17v4"/></svg></div>
          <div class="tour-body">
            <div class="tour-meta">
              <span class="chip">90 minutes</span>
              <span class="chip">Up to 20 guests</span>
              <span class="chip chip-easy">Easy · Evening, level ground</span>
            </div>
            <h3>Ghosts of Gettysburg Lantern Walk</h3>
            <p class="desc">An after-dark walking tour through downtown Gettysburg pairing real wartime accounts with candlelit storytelling.</p>
            <div class="tour-price-row"><span class="tour-price">$28<small> /adult</small></span></div>
            <a href="book.html?tour=lantern" class="btn btn-primary btn-block">Book This Tour</a>
          </div>
        </article>
        <article class="tour-card">
          <div class="tour-banner tb-5"><svg viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="1.8"><circle cx="12" cy="12" r="4"/><path d="M12 2v2M12 20v2M2 12h2M20 12h2M4.9 4.9l1.4 1.4M17.7 17.7l1.4 1.4M19.1 4.9l-1.4 1.4M6.3 17.7l-1.4 1.4"/></svg></div>
          <div class="tour-body">
            <div class="tour-meta">
              <span class="chip">3 hours</span>
              <span class="chip">Up to 6 guests</span>
              <span class="chip chip-moderate">Moderate · Customized route</span>
            </div>
            <h3>Sunrise Private Battlefield Experience</h3>
            <p class="desc">A private, small-group dawn tour with a senior guide, customized to your interests — for up to six guests.</p>
            <div class="tour-price-row"><span class="tour-price">$89<small> /adult</small></span></div>
            <a href="book.html?tour=private" class="btn btn-primary btn-block">Book This Tour</a>
          </div>
        </article>
      </div>
    </div>
  </section>

  <section class="section section-alt">
    <div class="wrap">
      <div class="section-head reveal">
        <span class="eyebrow">Before You Go</span>
        <h2>What to expect on tour.</h2>
        <p>A little preparation goes a long way on the battlefield.</p>
      </div>
      <div class="expect-grid reveal">
        <div class="expect-card">
          <div class="ic"><svg width="22" height="22" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" aria-hidden="true"><path d="M12 2l3 7h7l-5.5 4.5L18.5 21 12 16.5 5.5 21l2-7.5L2 9h7z"/></svg></div>
          <h3>Dress for the field</h3>
          <p>Closed-toe walking shoes, sun protection, and a water bottle. Walking tours cover uneven, sometimes rocky terrain.</p>
        </div>
        <div class="expect-card">
          <div class="ic"><svg width="22" height="22" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" aria-hidden="true"><path d="M17.7 7.7a2.5 2.5 0 10-3.5-3.5"/><path d="M6.3 16.3a2.5 2.5 0 103.5 3.5"/><path d="M3 21l6-6M21 3l-6 6M14 4l6 6-9 9-6-6z"/></svg></div>
          <h3>Rain or shine</h3>
          <p>Tours run in light rain and most weather. We'll call or email you directly if conditions require a reschedule.</p>
        </div>
        <div class="expect-card">
          <div class="ic"><svg width="22" height="22" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" aria-hidden="true"><path d="M20 21v-2a4 4 0 00-4-4H8a4 4 0 00-4 4v2"/><circle cx="12" cy="7" r="4"/></svg></div>
          <h3>Small groups</h3>
          <p>Most tours cap at 12&ndash;24 guests. You'll always be close enough to hear your guide without a headset.</p>
        </div>
        <div class="expect-card">
          <div class="ic"><svg width="22" height="22" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" aria-hidden="true"><rect x="3" y="4" width="18" height="18" rx="2"/><path d="M16 2v4M8 2v4M3 10h18"/></svg></div>
          <h3>Arrive 15 minutes early</h3>
          <p>Check in at our 271 Baltimore Street ticket office, or the posted meeting point for evening tours, before departure time.</p>
        </div>
      </div>
    </div>
  </section>

  <section class="section">
    <div class="wrap">
      <div class="book-band reveal">
        <div>
          <span class="eyebrow eyebrow--light">Ready When You Are</span>
          <h2>Ready to pick your date?</h2>
          <p>Pick a tour, choose a date, and check out securely. You'll get a confirmation with your ticket number by email.</p>
          <div class="trust-strip">
            <div class="trust-item">
              <svg width="20" height="20" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" aria-hidden="true"><rect x="4" y="10" width="16" height="10" rx="2"/><path d="M8 10V7a4 4 0 018 0v3"/></svg>
              Secure checkout
            </div>
            <div class="trust-item">
              <svg width="20" height="20" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" aria-hidden="true"><circle cx="12" cy="12" r="9"/><path d="M9 12l2 2 4-4"/></svg>
              Free cancellation up to 24h
            </div>
            <div class="card-icons" aria-hidden="true">
              <svg width="34" height="22" viewBox="0 0 34 22"><rect width="34" height="22" rx="4" fill="#1a3b6d"/><text x="17" y="14" font-family="Arial" font-size="8" fill="#fff" text-anchor="middle" font-weight="bold">VISA</text></svg>
              <svg width="34" height="22" viewBox="0 0 34 22"><rect width="34" height="22" rx="4" fill="#2b2523"/><circle cx="14" cy="11" r="6" fill="#eb001b"/><circle cx="20" cy="11" r="6" fill="#f79e1b" opacity="0.85"/></svg>
              <svg width="34" height="22" viewBox="0 0 34 22"><rect width="34" height="22" rx="4" fill="#2e77bc"/><text x="17" y="14" font-family="Arial" font-size="6.5" fill="#fff" text-anchor="middle" font-weight="bold">AMEX</text></svg>
              <svg width="34" height="22" viewBox="0 0 34 22"><rect width="34" height="22" rx="4" fill="#f4f0e6"/><text x="17" y="14" font-family="Arial" font-size="6" fill="#a3341f" text-anchor="middle" font-weight="bold">DISC</text></svg>
            </div>
          </div>
        </div>
        <a href="{{ home_url('/book/') }}" class="btn btn-primary" style="flex-shrink:0;">Start Booking</a>
      </div>
    </div>
  </section>
</main>

<footer class="site-footer">
  <div class="wrap">
    <div class="footer-grid">
      <div class="footer-brand">
        <a href="{{ home_url('/') }}" class="brand" style="color:#fff;">
          <svg class="brand-mark" width="34" height="34" viewBox="0 0 34 34" fill="none" aria-hidden="true"><path d="M2 26L10 12L15 19L20 9L32 26H2Z" fill="currentColor" opacity="0.9"/><circle cx="26" cy="7" r="3" fill="currentColor"/></svg>
          <span class="brand-text"><span class="brand-name">Hallowed Ground</span><span class="brand-sub">Battlefield Tours</span></span>
        </a>
        <p>Licensed-guide battlefield tours in Gettysburg, PA. Walking, bus, and evening lantern experiences for individuals, families, and groups.</p>
        <div class="social-row">
          <a href="#" aria-label="Hallowed Ground Battlefield Tours on Facebook"><svg width="17" height="17" viewBox="0 0 24 24" fill="currentColor" aria-hidden="true"><path d="M22 12a10 10 0 10-11.6 9.9v-7H7.9V12h2.5V9.8c0-2.5 1.5-3.9 3.8-3.9 1.1 0 2.2.2 2.2.2v2.5h-1.3c-1.2 0-1.6.8-1.6 1.6V12h2.8l-.4 2.9h-2.4v7A10 10 0 0022 12z"/></svg></a>
          <a href="#" aria-label="Hallowed Ground Battlefield Tours on Instagram"><svg width="17" height="17" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" aria-hidden="true"><rect x="3" y="3" width="18" height="18" rx="5"/><circle cx="12" cy="12" r="4"/><circle cx="17.5" cy="6.5" r="1"/></svg></a>
          <a href="#" aria-label="Hallowed Ground Battlefield Tours on TripAdvisor"><svg width="17" height="17" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" aria-hidden="true"><circle cx="8" cy="14" r="3.2"/><circle cx="16" cy="14" r="3.2"/><path d="M2 9h6M16 9h6M12 6.5c-2.5 0-4.7 1-6.2 2.5M12 6.5c2.5 0 4.7 1 6.2 2.5"/></svg></a>
        </div>
      </div>
      <div>
        <h4>Explore</h4>
        <ul>
          <li><a href="{{ home_url('/') }}">Home</a></li>
          <li><a href="{{ home_url('/tours/') }}">All Tours</a></li>
          <li><a href="{{ home_url('/guides/') }}">Our Guides</a></li>
          <li><a href="{{ home_url('/area/') }}">The Area</a></li>
          <li><a href="{{ home_url('/book/') }}">Book &amp; Pay</a></li>
          <li><a href="{{ home_url('/contact/') }}">Contact &amp; FAQ</a></li>
        </ul>
      </div>
      <div>
        <h4>Hours</h4>
        <ul class="footer-hours">
          <li><b style="color:#fff;">Apr&ndash;Nov</b></li>
          <li>Mon&ndash;Sun, 8:00 AM&ndash;6:00 PM</li>
          <li style="margin-top:0.5rem;"><b style="color:#fff;">Dec&ndash;Mar</b></li>
          <li>Thu&ndash;Sun, 9:00 AM&ndash;4:00 PM</li>
        </ul>
      </div>
      <div>
        <h4>Contact</h4>
        <ul>
          <li>Hallowed Ground Battlefield Tours</li>
          <li>271 Baltimore Street<br>Gettysburg, PA 17325</li>
          <li><a href="tel:+17175550271">(717) 555-0271</a></li>
          <li><a href="mailto:tours@@hallowedground.test">tours@@hallowedground.test</a></li>
        </ul>
      </div>
    </div>
    <p class="footer-service">Proudly serving Gettysburg and the surrounding Adams County communities of Biglerville, Littlestown, New Oxford, McSherrystown, Fairfield, Cashtown, and Hanover.</p>
    <div class="footer-bottom">
      <span>&copy; <span data-year>2026</span> Hallowed Ground Battlefield Tours, Gettysburg PA. All rights reserved.</span>
      <span>Design concept by Ridges &amp; Valleys Studio.</span>
    </div>
  </div>
</footer>

<a href="#" class="concept-badge" aria-label="This is a design concept by Ridges and Valleys Studio">
  <span class="dot" aria-hidden="true"></span> Concept &middot; Ridges &amp; Valleys Studio
</a>
@endsection
