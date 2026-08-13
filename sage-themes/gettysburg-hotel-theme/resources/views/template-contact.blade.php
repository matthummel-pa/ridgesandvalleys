{{--
  Template Name: Contact & Booking
--}}

@extends('layouts.app')

@push('head')
<script type="application/ld+json">
{
  "@@context": "https://schema.org",
  "@@type": "Hotel",
  "@@id": "{{ home_url('/') }}#hotel",
  "name": "The Lantern & Laurel Inn",
  "url": "{{ home_url('/contact/') }}",
  "image": "https://commons.wikimedia.org/wiki/Special:FilePath/Gettysburg,_Pennsylvania_(6286097408).jpg?width=1600",
  "description": "Contact and direct booking for a boutique historic inn on Baltimore Street in downtown Gettysburg, Pennsylvania.",
  "telephone": "+1-717-555-0142",
  "email": "stay@@lanternandlaurel.test",
  "priceRange": "$179–$309",
  "numberOfRooms": 9,
  "petsAllowed": false,
  "checkinTime": "15:00",
  "checkoutTime": "11:00",
  "address": {
    "@@type": "PostalAddress",
    "streetAddress": "42 Baltimore Street",
    "addressLocality": "Gettysburg",
    "addressRegion": "PA",
    "postalCode": "17325",
    "addressCountry": "US"
  },
  "geo": { "@@type": "GeoCoordinates", "latitude": 39.8309, "longitude": -77.2311 },
  "hasMap": "https://www.openstreetmap.org/?mlat=39.8309&mlon=-77.2311#map=17/39.8309/-77.2311",
  "openingHoursSpecification": [
    {
      "@@type": "OpeningHoursSpecification",
      "dayOfWeek": ["Monday","Tuesday","Wednesday","Thursday","Friday","Saturday","Sunday"],
      "opens": "07:00",
      "closes": "22:00"
    }
  ],
  "areaServed": [
    { "@@type": "City", "name": "Gettysburg, PA" },
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
    { "@@type": "ListItem", "position": 2, "name": "Contact & Booking", "item": "{{ home_url('/contact/') }}" }
  ]
}
</script>
@endpush

@section('content')
<a href="#main" class="skip-link">Skip to main content</a>

<header class="site">
  <div class="nav-bar">
    <a href="{{ home_url('/') }}" class="brand" aria-label="The Lantern &amp; Laurel Inn — home">
      <span class="brand-mark" aria-hidden="true">
        <svg width="22" height="22" viewBox="0 0 24 24" fill="none" stroke="#d9ad5f" stroke-width="1.4">
          <path d="M12 3c-3 3-3 6-3 9a3 3 0 0 0 6 0c0-3 0-6-3-9Z"/>
          <path d="M12 15v6"/>
          <path d="M8 21h8"/>
        </svg>
      </span>
      <span class="brand-word">
        <span class="name">Lantern &amp; Laurel</span>
        <span class="place">Gettysburg, Pennsylvania</span>
      </span>
    </a>

    <nav class="primary" aria-label="Primary">
      <a href="{{ home_url('/') }}">Home</a>
      <a href="{{ home_url('/rooms/') }}">Rooms</a>
      <a href="{{ home_url('/amenities/') }}">Amenities</a>
      <a href="{{ home_url('/area/') }}">The Area</a>
      <a href="{{ home_url('/gallery/') }}">Gallery</a>
      <a href="{{ home_url('/contact/') }}" class="is-active" aria-current="page">Contact</a>
    </nav>

    <div class="nav-actions">
      <a href="#book" class="btn btn-brass desktop-only btn-sm">Book a Stay</a>
      <button class="hamburger" id="hamburgerBtn" aria-expanded="false" aria-controls="mobilePanel" aria-label="Open menu">
        <span></span><span></span><span></span>
      </button>
    </div>
  </div>

  <div class="mobile-panel" id="mobilePanel">
    <a href="{{ home_url('/') }}">Home</a>
    <a href="{{ home_url('/rooms/') }}">Rooms</a>
    <a href="{{ home_url('/amenities/') }}">Amenities</a>
    <a href="{{ home_url('/area/') }}">The Area</a>
    <a href="{{ home_url('/gallery/') }}">Gallery</a>
    <a href="{{ home_url('/contact/') }}" class="is-active" aria-current="page">Contact</a>
    <a href="#book" class="btn btn-brass btn-block">Book a Stay</a>
  </div>
</header>

<div class="page-hero">
  <div class="wrap">
    <span class="eyebrow">Contact &amp; Booking</span>
    <h1>Book direct on Baltimore Street.</h1>
    <p class="lead">Check availability below, or reach the front desk any day from 7am to 10pm. We're a small house in downtown Gettysburg — every message is answered by the people who run it.</p>
  </div>
</div>

<nav class="breadcrumb" aria-label="Breadcrumb">
  <div class="wrap">
    <ol>
      <li><a href="{{ home_url('/') }}">Home</a></li>
      <li class="sep" aria-hidden="true">/</li>
      <li aria-current="page">Contact &amp; Booking</li>
    </ol>
  </div>
</nav>

<main id="main">

  <!-- ============ BOOKING TOOL ============ -->
  <section class="section-cream" id="book">
    <div class="wrap">
      <div class="section-head reveal">
        <span class="eyebrow">Check Availability</span>
        <h2>Tell us your dates.</h2>
        <p>Enter your stay and party size and we'll confirm what's open — we hold your room and reply within one business day.</p>
      </div>

      <form class="booking-bar reveal" id="bookingForm" action="#" aria-label="Check availability" style="max-width:920px;">
        <div class="booking-field">
          <label for="checkin">Check-in</label>
          <input type="date" id="checkin" name="checkin" required>
        </div>
        <div class="booking-field">
          <label for="checkout">Check-out</label>
          <input type="date" id="checkout" name="checkout" required>
        </div>
        <div class="booking-field">
          <label for="guests">Guests</label>
          <select id="guests" name="guests">
            <option>1 Guest</option>
            <option selected>2 Guests</option>
            <option>3 Guests</option>
            <option>4 Guests</option>
          </select>
        </div>
        <button type="submit" class="btn btn-forest">Check Availability</button>
        <div class="booking-msg" id="bookingMsg" role="status" aria-live="polite">
          <svg width="18" height="18" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" aria-hidden="true"><path d="M20 6 9 17l-5-5"/></svg>
          <span>Thank you! We'll confirm availability for your dates by email within one business day.</span>
        </div>
      </form>
    </div>
  </section>

  <!-- ============ CONTACT INFO ============ -->
  <section class="section-paper">
    <div class="wrap">
      <div class="section-head reveal">
        <span class="eyebrow">Reach Us</span>
        <h2>By phone, email, or in person.</h2>
      </div>

      <div class="info-grid">
        <div class="info-card reveal">
          <h3>Call the Front Desk</h3>
          <p><a href="tel:+17175550142">(717) 555-0142</a></p>
          <p>Open 7:00am&ndash;10:00pm daily. Best for same-week dates and questions about specific rooms.</p>
        </div>
        <div class="info-card reveal">
          <h3>Email Us</h3>
          <p><a href="mailto:stay@@lanternandlaurel.test">stay@@lanternandlaurel.test</a></p>
          <p>We reply within one business day — great for special requests and multi-night stays.</p>
        </div>
        <div class="info-card reveal">
          <h3>Visit</h3>
          <address>
            The Lantern &amp; Laurel Inn<br>
            42 Baltimore Street<br>
            Gettysburg, PA 17325
          </address>
          <p style="margin-top:8px;">Free gated parking behind the inn.</p>
        </div>
        <div class="info-card reveal">
          <h3>Hours</h3>
          <p><b>Front desk</b> 7:00am&ndash;10:00pm daily</p>
          <p><b>Breakfast</b> 7:30&ndash;10:00am</p>
          <p><b>Check-in</b> 3:00pm &middot; <b>Check-out</b> 11:00am</p>
        </div>
      </div>
    </div>
  </section>

  <!-- ============ MESSAGE FORM + DIRECTIONS ============ -->
  <section class="section-cream">
    <div class="wrap split">
      <div class="reveal">
        <div class="section-head">
          <span class="eyebrow">Send a Message</span>
          <h2>Ask us anything.</h2>
          <p>Planning a special occasion, traveling with a group, or need a quiet room for battlefield research? Tell us what you're after.</p>
        </div>

        <form id="messageForm" action="#" aria-label="Send a message">
          <div style="display:grid;grid-template-columns:1fr 1fr;gap:16px;">
            <div class="booking-field">
              <label for="name">Name</label>
              <input type="text" id="name" name="name" autocomplete="name" required>
            </div>
            <div class="booking-field">
              <label for="email">Email</label>
              <input type="email" id="email" name="email" autocomplete="email" required>
            </div>
          </div>
          <div class="booking-field" style="margin-top:16px;">
            <label for="message">Message</label>
            <textarea id="message" name="message" rows="5" required
                      style="font-family:var(--f-head);font-size:0.95rem;padding:12px;border-radius:var(--radius-sm);border:1.5px solid var(--line);background:var(--paper);color:var(--ink);width:100%;"></textarea>
          </div>
          <button type="submit" class="btn btn-forest" style="margin-top:18px;">Send Message</button>
          <div class="booking-msg" id="messageMsg" role="status" aria-live="polite" style="margin-top:16px;">
            <svg width="18" height="18" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" aria-hidden="true"><path d="M20 6 9 17l-5-5"/></svg>
            <span>Thank you! Your message is on its way — we'll reply within one business day.</span>
          </div>
        </form>
      </div>

      <div class="reveal">
        <div class="aside-card aside-sticky">
          <h3>Getting Here</h3>
          <p><b>From US-15:</b> exit at Steinwehr Avenue and follow Baltimore Street up Cemetery Hill toward Lincoln Square.</p>
          <p><b>From US-30:</b> come into Lincoln Square and turn south onto Baltimore Street; we're a short block down.</p>
          <p style="margin-bottom:0;">Full walking distances and driving directions are on <a href="{{ home_url('/area/') }}" style="color:var(--ember);">The Area</a> page.</p>
          <a href="https://www.openstreetmap.org/?mlat=39.8309&mlon=-77.2311#map=17/39.8309/-77.2311"
             class="btn btn-forest btn-sm btn-block" style="margin-top:18px;" target="_blank" rel="noopener">Open map</a>
        </div>
      </div>
    </div>
  </section>

  <!-- ============ CTA BAND ============ -->
  <section class="cta-band">
    <div class="wrap cta-inner">
      <span class="eyebrow">We'll Save You a Room</span>
      <h2>Nine rooms, one Baltimore Street.</h2>
      <p>Anniversary weekends in July and November fill first — reach out early and we'll make sure you have a place to come home to each night.</p>
      <div class="cta-row">
        <a href="#book" class="btn btn-brass">Check Availability</a>
        <a href="tel:+17175550142" class="btn btn-outline">Call (717) 555-0142</a>
      </div>
    </div>
  </section>

</main>

<!-- ============ SHARED FOOTER ============ -->
<footer class="site">
  <div class="wrap">
    <div class="foot-grid">
      <div class="foot-brand">
        <a href="{{ home_url('/') }}" class="brand" aria-label="The Lantern &amp; Laurel Inn — home">
          <span class="brand-mark" aria-hidden="true">
            <svg width="20" height="20" viewBox="0 0 24 24" fill="none" stroke="#d9ad5f" stroke-width="1.4">
              <path d="M12 3c-3 3-3 6-3 9a3 3 0 0 0 6 0c0-3 0-6-3-9Z"/>
              <path d="M12 15v6"/>
              <path d="M8 21h8"/>
            </svg>
          </span>
          <span class="brand-word"><span class="name">Lantern &amp; Laurel</span></span>
        </a>
        <p>A nine-room historic inn on Baltimore Street, five minutes from Lincoln Square and the Gettysburg battlefield.</p>
        <div class="social-row">
          <a href="#" aria-label="Lantern &amp; Laurel Inn on Instagram">
            <svg viewBox="0 0 24 24"><rect x="3" y="3" width="18" height="18" rx="5"/><circle cx="12" cy="12" r="4"/><circle cx="17.5" cy="6.5" r="1"/></svg>
          </a>
          <a href="#" aria-label="Lantern &amp; Laurel Inn on Facebook">
            <svg viewBox="0 0 24 24"><path d="M15 8h2V4h-2a5 5 0 0 0-5 5v2H8v4h2v7h4v-7h3l1-4h-4V9a1 1 0 0 1 1-1Z"/></svg>
          </a>
          <a href="#" aria-label="Lantern &amp; Laurel Inn on Pinterest">
            <svg viewBox="0 0 24 24"><circle cx="12" cy="12" r="9"/><path d="M9 18c1-3 1.5-5.5 1.5-7.5a2.5 2.5 0 0 1 5 0c0 2-1.2 4-3 4"/></svg>
          </a>
        </div>
      </div>

      <div class="foot-col">
        <h4>Explore</h4>
        <ul>
          <li><a href="{{ home_url('/rooms/') }}">Rooms &amp; Rates</a></li>
          <li><a href="{{ home_url('/amenities/') }}">Amenities</a></li>
          <li><a href="{{ home_url('/area/') }}">The Area</a></li>
          <li><a href="{{ home_url('/gallery/') }}">Gallery</a></li>
          <li><a href="{{ home_url('/contact/') }}">Contact &amp; Booking</a></li>
        </ul>
      </div>

      <div class="foot-col">
        <h4>Visit</h4>
        <address>
          <p>The Lantern &amp; Laurel Inn</p>
          <p>42 Baltimore Street</p>
          <p>Gettysburg, PA 17325</p>
        </address>
        <p style="margin-top:10px;">Check-in 3:00pm</p>
        <p>Check-out 11:00am</p>
      </div>

      <div class="foot-col">
        <h4>Contact &amp; Hours</h4>
        <p><a href="tel:+17175550142">(717) 555-0142</a></p>
        <p><a href="mailto:stay@@lanternandlaurel.test">stay@@lanternandlaurel.test</a></p>
        <p style="margin-top:10px;">Front desk 7:00am&ndash;10:00pm daily</p>
        <p>Breakfast 7:30&ndash;10:00am</p>
        <p style="margin-top:10px;">Serving Gettysburg &amp; Adams County — Biglerville, New Oxford, Littlestown &amp; Fairfield.</p>
      </div>
    </div>

    <div class="foot-bottom">
      <span>&copy; <span class="js-year">2026</span> The Lantern &amp; Laurel Inn, Gettysburg PA. Concept for demonstration.</span>
      <span>Design concept by Ridges &amp; Valleys Studio</span>
    </div>
  </div>
</footer>

<a href="#" class="concept-badge" aria-label="This is a design concept by Ridges &amp; Valleys Studio">
  <span class="dot" aria-hidden="true"></span>
  Concept &middot; Ridges &amp; Valleys Studio
</a>
<script>
/* Page-specific tools: booking availability + message form (demo submit + default dates). */
(function () {
  "use strict";

  function wireDemoForm(form, msg) {
    if (form && msg) {
      form.addEventListener('submit', function (e) {
        e.preventDefault();
        msg.classList.add('show');
        msg.scrollIntoView({ behavior: 'smooth', block: 'nearest' });
      });
    }
  }

  wireDemoForm(document.getElementById('bookingForm'), document.getElementById('bookingMsg'));
  wireDemoForm(document.getElementById('messageForm'), document.getElementById('messageMsg'));

  // Sensible default dates in the booking bar.
  var ci = document.getElementById('checkin');
  var co = document.getElementById('checkout');
  if (ci && co) {
    var today = new Date();
    var inDate = new Date(today.getTime() + 14 * 24 * 60 * 60 * 1000);
    var outDate = new Date(today.getTime() + 16 * 24 * 60 * 60 * 1000);
    function fmt(d) {
      var m = String(d.getMonth() + 1).padStart(2, '0');
      var day = String(d.getDate()).padStart(2, '0');
      return d.getFullYear() + '-' + m + '-' + day;
    }
    ci.min = fmt(today);
    co.min = fmt(today);
    ci.value = fmt(inDate);
    co.value = fmt(outDate);
    ci.addEventListener('change', function () {
      if (ci.value) { co.min = ci.value; }
    });
  }
})();
</script>
@endsection
