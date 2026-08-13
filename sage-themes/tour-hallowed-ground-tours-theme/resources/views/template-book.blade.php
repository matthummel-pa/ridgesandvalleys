{{--
  Template Name: Book & Pay
--}}

@extends('layouts.app')

@push('head')
<script type="application/ld+json">
{
  "@@context": "https://schema.org",
  "@@type": "TravelAgency",
  "@@id": "{{ home_url('/') }}#business",
  "name": "Hallowed Ground Battlefield Tours",
  "url": "{{ home_url('/book/') }}",
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
    "latitude": 39.827,
    "longitude": -77.232
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
      "name": "Book & Pay",
      "item": "{{ home_url('/book/') }}"
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
      <ul><li><a href="{{ home_url('/') }}">Home</a></li><li><a href="{{ home_url('/tours/') }}">Tours</a></li><li><a href="{{ home_url('/guides/') }}">Our Guides</a></li><li><a href="{{ home_url('/area/') }}">The Area</a></li><li><a href="{{ home_url('/contact/') }}">Contact</a></li></ul>
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
  <ul><li><a href="{{ home_url('/') }}">Home</a></li><li><a href="{{ home_url('/tours/') }}">Tours</a></li><li><a href="{{ home_url('/guides/') }}">Our Guides</a></li><li><a href="{{ home_url('/area/') }}">The Area</a></li><li><a href="{{ home_url('/contact/') }}">Contact</a></li></ul>
  <a href="tel:+17175550271" class="btn btn-outline btn-block" style="color:#fff;">Call (717) 555-0271</a>
  <a href="{{ home_url('/book/') }}" class="btn btn-primary btn-block" style="margin-top:0.8rem;">Book a Tour</a>
</nav>

<main id="main">
<nav class="breadcrumb" aria-label="Breadcrumb"><div class="wrap"><ol><li><a href="{{ home_url('/') }}">Home</a></li><li class="sep" aria-hidden="true">/</li><li aria-current="page">Book &amp; Pay</li></ol></div></nav>
  <section class="page-intro">
    <div class="wrap reveal">
      <span class="eyebrow">Book &amp; Pay</span>
      <h1>Book your Gettysburg battlefield tour</h1>
      <p>Reserve your spot in about three minutes. Pick a tour, choose a date, add tickets, and check out. You'll get a confirmation with your ticket number and meeting-point directions to 271 Baltimore Street, Gettysburg.</p>
    </div>
  </section>

  <section class="section">
    <div class="wrap">
      <div class="booking-inline" id="bookingFlow">
        <div class="modal-head">
          <h2 id="modalTitle">Book Your Tour</h2>
          <span class="demo-flag" style="margin:0;">Demo &mdash; no real payment is processed</span>
        </div>
        <div class="step-indicator" aria-hidden="true">
          <div class="step-dot" data-step-dot="1"></div>
          <div class="step-dot" data-step-dot="2"></div>
          <div class="step-dot" data-step-dot="3"></div>
          <div class="step-dot" data-step-dot="4"></div>
          <div class="step-dot" data-step-dot="5"></div>
        </div>
        <div class="modal-body">
          <div class="step-panel" data-step="1">
            <div class="step-label">Step 1 of 5</div>
            <h3 class="step-title">Choose your tour</h3>
            <div class="pick-list" id="tourPickList"></div>
          </div>
          <div class="step-panel" data-step="2">
            <div class="step-label">Step 2 of 5</div>
            <h3 class="step-title">Pick a date &amp; time</h3>
            <div class="slot-grid" id="slotGrid"></div>
          </div>
          <div class="step-panel" data-step="3">
            <div class="step-label">Step 3 of 5</div>
            <h3 class="step-title">Ticket quantities</h3>
            <div class="ticket-row">
              <div class="ticket-label"><b>Adult</b><span id="priceAdult">$0</span></div>
              <div class="stepper">
                <button type="button" data-tix-minus="adult" aria-label="Decrease adult tickets">&minus;</button>
                <output id="qtyAdult">0</output>
                <button type="button" data-tix-plus="adult" aria-label="Increase adult tickets">+</button>
              </div>
            </div>
            <div class="ticket-row">
              <div class="ticket-label"><b>Child</b><span id="priceChild">Ages 6&ndash;12 &middot; $0</span></div>
              <div class="stepper">
                <button type="button" data-tix-minus="child" aria-label="Decrease child tickets">&minus;</button>
                <output id="qtyChild">0</output>
                <button type="button" data-tix-plus="child" aria-label="Increase child tickets">+</button>
              </div>
            </div>
            <div class="ticket-row">
              <div class="ticket-label"><b>Senior</b><span id="priceSenior">Ages 65+ &middot; $0</span></div>
              <div class="stepper">
                <button type="button" data-tix-minus="senior" aria-label="Decrease senior tickets">&minus;</button>
                <output id="qtySenior">0</output>
                <button type="button" data-tix-plus="senior" aria-label="Increase senior tickets">+</button>
              </div>
            </div>
            <p class="group-note" id="groupNote"></p>
            <div class="total-bar">
              <span class="tb-label">Running total</span>
              <span class="tb-amount" id="liveTotal" aria-live="polite">$0.00</span>
            </div>
          </div>
          <div class="step-panel" data-step="4">
            <div class="step-label">Step 4 of 5</div>
            <h3 class="step-title">Order summary &amp; checkout</h3>
            <div class="summary-card" id="summaryCard"></div>
            <form id="checkoutForm" novalidate>
              <div class="form-grid">
                <div class="field full">
                  <label for="ccName">Full name</label>
                  <input type="text" id="ccName" name="ccName" autocomplete="name" required>
                </div>
                <div class="field full">
                  <label for="ccEmail">Email address</label>
                  <input type="email" id="ccEmail" name="ccEmail" autocomplete="email" required>
                </div>
                <div class="field full">
                  <span class="demo-flag">Demo &mdash; no real payment is processed</span>
                </div>
                <div class="field full">
                  <label for="ccNumber">Card number (demo)</label>
                  <input type="text" id="ccNumber" name="ccNumber" inputmode="numeric" placeholder="4242 4242 4242 4242" maxlength="19" autocomplete="off">
                </div>
                <div class="field">
                  <label for="ccExp">Expiry (demo)</label>
                  <input type="text" id="ccExp" name="ccExp" placeholder="MM / YY" maxlength="7" autocomplete="off">
                </div>
                <div class="field">
                  <label for="ccCvc">CVC (demo)</label>
                  <input type="text" id="ccCvc" name="ccCvc" inputmode="numeric" placeholder="123" maxlength="4" autocomplete="off">
                </div>
              </div>
              <div class="checkout-trust">
                <div class="trust-item">
                  <svg width="18" height="18" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" aria-hidden="true"><rect x="4" y="10" width="16" height="10" rx="2"/><path d="M8 10V7a4 4 0 018 0v3"/></svg>
                  Secure checkout
                </div>
                <div class="trust-item">
                  <svg width="18" height="18" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" aria-hidden="true"><circle cx="12" cy="12" r="9"/><path d="M9 12l2 2 4-4"/></svg>
                  Free cancellation up to 24h
                </div>
                <div class="card-icons" aria-hidden="true">
                  <svg width="30" height="19" viewBox="0 0 34 22"><rect width="34" height="22" rx="4" fill="#1a3b6d"/><text x="17" y="14" font-family="Arial" font-size="8" fill="#fff" text-anchor="middle" font-weight="bold">VISA</text></svg>
                  <svg width="30" height="19" viewBox="0 0 34 22"><rect width="34" height="22" rx="4" fill="#2b2523"/><circle cx="14" cy="11" r="6" fill="#eb001b"/><circle cx="20" cy="11" r="6" fill="#f79e1b" opacity="0.85"/></svg>
                </div>
              </div>
              <button type="submit" class="btn btn-primary btn-block" id="payBtn" style="margin-top:1.3rem;">Pay $0.00</button>
            </form>
          </div>
          <div class="step-panel" data-step="5">
            <div class="confirm-wrap">
              <div class="confirm-icon">
                <svg width="32" height="32" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2.5" aria-hidden="true"><path d="M20 6L9 17l-5-5"/></svg>
              </div>
              <h3>You're booked!</h3>
              <p id="confirmSub">Check your email for your tickets and meeting-point directions.</p>
              <div class="ticket-number" id="ticketNumber" aria-live="polite">HG-000000</div>
              <div class="summary-card" id="confirmSummary" style="text-align:left;"></div>
              <button type="button" class="btn btn-primary" id="doneBtn">Book Another Tour</button>
            </div>
          </div>
        </div>
        <div class="modal-nav" id="flowNav">
          <button type="button" class="btn btn-outline-dark" id="backBtn">Back</button>
          <button type="button" class="btn btn-primary" id="nextBtn">Next</button>
        </div>
      </div>
    </div>
  </section>

  <section class="section section-alt">
    <div class="wrap">
      <div class="section-head reveal" style="margin-inline:auto; text-align:center;">
        <span class="eyebrow">Good to Know</span>
        <h2>Booking, meeting point &amp; cancellation</h2>
      </div>
      <div class="expect-grid reveal">
        <div class="expect-card">
          <div class="ic"><svg width="22" height="22" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" aria-hidden="true"><path d="M12 2a8 8 0 00-8 8c0 5.5 8 12 8 12s8-6.5 8-12a8 8 0 00-8-8z"/><circle cx="12" cy="10" r="3"/></svg></div>
          <h3>Where to meet</h3>
          <p>Day tours check in at our ticket office, 271 Baltimore Street, Gettysburg, PA 17325. Evening lantern walks meet at the Lincoln Square flagpole downtown.</p>
        </div>
        <div class="expect-card">
          <div class="ic"><svg width="22" height="22" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" aria-hidden="true"><circle cx="12" cy="12" r="9"/><path d="M9 12l2 2 4-4"/></svg></div>
          <h3>Free cancellation</h3>
          <p>Cancel or reschedule up to 24 hours before your tour for a full refund. This is a concept demo &mdash; no payment is processed and no email is sent.</p>
        </div>
        <div class="expect-card">
          <div class="ic"><svg width="22" height="22" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" aria-hidden="true"><path d="M22 16.92v3a2 2 0 01-2.18 2 19.79 19.79 0 01-8.63-3.07 19.5 19.5 0 01-6-6 19.79 19.79 0 01-3.07-8.67A2 2 0 014.11 2h3a2 2 0 012 1.72c.127.96.361 1.902.7 2.81a2 2 0 01-.45 2.11L8.09 9.91a16 16 0 006 6l1.27-1.27a2 2 0 012.11-.45c.908.339 1.85.573 2.81.7A2 2 0 0122 16.92z"/></svg></div>
          <h3>Need help?</h3>
          <p>Prefer to book by phone, or planning a group of 10+? Call (717) 555-0271 or email <a href="mailto:tours@@hallowedground.test" style="color:var(--slate-700); font-weight:600;">tours@@hallowedground.test</a>.</p>
        </div>
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
