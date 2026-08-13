{{--
  Template Name: Contact First Shot Food & History Tours
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
  "url": "{{ home_url('/contact/') }}",
  "telephone": "+1-717-555-0105",
  "email": "book@@firstshottours.test",
  "priceRange": "$$",
  "description": "Contact details for First Shot food and history walking tours in downtown Gettysburg, Pennsylvania.",
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
  "contactPoint": {
    "@@type": "ContactPoint",
    "telephone": "+1-717-555-0105",
    "email": "book@@firstshottours.test",
    "contactType": "reservations",
    "areaServed": "US",
    "availableLanguage": "English"
  }
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
        <li><a href="{{ home_url('/tours/') }}">Tours</a></li>
        <li><a href="{{ home_url('/book/') }}">Book a Date</a></li>
        <li><a href="{{ home_url('/route/') }}">The Route</a></li>
        <li><a href="{{ home_url('/faq/') }}">FAQ</a></li>
        <li><a href="{{ home_url('/contact/') }}" class="is-active" aria-current="page">Contact</a></li>
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
      <li><a href="{{ home_url('/tours/') }}">Tours</a></li>
      <li><a href="{{ home_url('/book/') }}">Book a Date</a></li>
      <li><a href="{{ home_url('/route/') }}">The Route</a></li>
      <li><a href="{{ home_url('/faq/') }}">FAQ</a></li>
      <li><a href="{{ home_url('/contact/') }}" class="is-active" aria-current="page">Contact</a></li>
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
          <li aria-current="page">Contact</li>
        </ol>
      </nav>
      <span class="eyebrow" style="color:var(--mustard);">Contact</span>
      <h1>Come find us <em>on the square</em>.</h1>
      <p>The First Shot booking desk sits at 5 Chambersburg Street, half a block off Lincoln Square in downtown Gettysburg. Call, email, or send a message about tours, private groups, and gift bookings &mdash; a real person answers.</p>
    </div>
  </section>

  <section class="bg-paper">
    <div class="container">
      <div class="contact-grid">
        <div class="reveal">
          <div class="section-head" style="margin-bottom:1.5rem;">
            <span class="eyebrow">Booking Desk</span>
            <h2>How to reach us.</h2>
          </div>

          <div class="contact-detail">
            <svg viewBox="0 0 24 24" fill="none" aria-hidden="true"><path d="M12 21s-7-6.1-7-11a7 7 0 0 1 14 0c0 4.9-7 11-7 11z" stroke="currentColor" stroke-width="1.6"/><circle cx="12" cy="10" r="2.5" stroke="currentColor" stroke-width="1.6"/></svg>
            <div>
              <h3>Address</h3>
              <address>First Shot Food &amp; History Tours<br>5 Chambersburg Street<br>Gettysburg, PA 17325</address>
            </div>
          </div>

          <div class="contact-detail">
            <svg viewBox="0 0 24 24" fill="none" aria-hidden="true"><path d="M5 4h4l2 5-2.5 1.5a11 11 0 0 0 5 5L15 13l5 2v4a2 2 0 0 1-2 2A16 16 0 0 1 3 6a2 2 0 0 1 2-2z" stroke="currentColor" stroke-width="1.6" stroke-linejoin="round"/></svg>
            <div>
              <h3>Phone</h3>
              <p><a href="tel:+17175550105">(717) 555-0105</a></p>
            </div>
          </div>

          <div class="contact-detail">
            <svg viewBox="0 0 24 24" fill="none" aria-hidden="true"><rect x="3" y="5" width="18" height="14" rx="2" stroke="currentColor" stroke-width="1.6"/><path d="M4 7l8 6 8-6" stroke="currentColor" stroke-width="1.6" stroke-linejoin="round"/></svg>
            <div>
              <h3>Email</h3>
              <p><a href="mailto:book@@firstshottours.test">book@@firstshottours.test</a></p>
            </div>
          </div>

          <div class="contact-detail">
            <svg viewBox="0 0 24 24" fill="none" aria-hidden="true"><circle cx="12" cy="12" r="9" stroke="currentColor" stroke-width="1.6"/><path d="M12 7v5l3 2" stroke="currentColor" stroke-width="1.6" stroke-linecap="round"/></svg>
            <div>
              <h3>Hours</h3>
              <p>Booking desk: Mon&ndash;Fri, 9am&ndash;5pm<br>Tours depart: Thu&ndash;Mon, April through November<br>Closed Tue &amp; Wed for guide training and eatery prep.</p>
            </div>
          </div>

          <div class="contact-detail">
            <svg viewBox="0 0 24 24" fill="none" aria-hidden="true"><path d="M4 12h16M4 12l4-4M4 12l4 4" stroke="currentColor" stroke-width="1.6" stroke-linecap="round" stroke-linejoin="round"/></svg>
            <div>
              <h3>Service Area</h3>
              <p>Downtown Gettysburg and the wider Adams County area, including Biglerville, New Oxford, Littlestown, McSherrystown, Fairfield, Cashtown, and Hanover.</p>
            </div>
          </div>
        </div>

        <div class="reveal">
          <div class="booking-shell" style="padding:0;">
            <div class="booking-header">
              <h2>Send a message</h2>
              <p style="color:rgba(255,255,255,0.85); margin-bottom:0;">Questions about a tour, a private group, or a gift booking? Drop us a line.</p>
            </div>
            <div style="padding:2rem;">
              <form class="checkout-form" id="contactForm" novalidate>
                <div class="field">
                  <label for="cName">Full name</label>
                  <input type="text" id="cName" name="cName" required autocomplete="name" placeholder="Jordan Alvarez">
                </div>
                <div class="field">
                  <label for="cEmail">Email</label>
                  <input type="email" id="cEmail" name="cEmail" required autocomplete="email" placeholder="jordan@@example.com">
                </div>
                <div class="field">
                  <label for="cPhone">Phone (optional)</label>
                  <input type="tel" id="cPhone" name="cPhone" autocomplete="tel" placeholder="(717) 555-0105">
                </div>
                <div class="field">
                  <label for="cMessage">Message</label>
                  <input type="text" id="cMessage" name="cMessage" required placeholder="Tell us what you're planning">
                </div>
                <div class="demo-flag">
                  <svg width="14" height="14" viewBox="0 0 24 24" fill="none" aria-hidden="true"><path d="M12 9v4M12 17h.01" stroke="currentColor" stroke-width="2" stroke-linecap="round"/><circle cx="12" cy="12" r="9" stroke="currentColor" stroke-width="1.6"/></svg>
                  Demo form &mdash; this concept doesn't send real email
                </div>
                <button type="submit" class="btn btn-teal btn-block" id="contactSubmit">Send message</button>
                <p class="form-note" id="contactStatus" role="status" aria-live="polite"></p>
              </form>
            </div>
          </div>
        </div>
      </div>
    </div>
  </section>

  <section class="bg-cream-dim">
    <div class="container">
      <div class="location-grid reveal">
        <div>
          <div class="map-visual" aria-hidden="true">
            <svg class="pin" viewBox="0 0 24 24" fill="currentColor"><path d="M12 2C7.6 2 4 5.6 4 10c0 6 8 12 8 12s8-6 8-12c0-4.4-3.6-8-8-8z"/><circle cx="12" cy="10" r="3" fill="var(--cream)"/></svg>
          </div>
        </div>
        <div>
          <div class="location-card">
            <h4>Meeting Point</h4>
            <p><strong>Lincoln Square, Gettysburg</strong><br>Two minutes on foot from our desk at 5 Chambersburg Street. Exact spot is in your confirmation email.</p>
          </div>
          <div class="location-card">
            <h4>Parking</h4>
            <p>Gettysburg Transit Center garage on Carlisle Street, one block north of the square, plus metered street parking around Baltimore and Steinwehr.</p>
          </div>
          <div class="location-card">
            <h4>Getting Here</h4>
            <p>From US-15, take the US-30 / York Street exit into town. From the east or west, US-30 runs straight through Lincoln Square.</p>
          </div>
        </div>
      </div>
    </div>
  </section>

  <section class="cta-band">
    <div class="container">
      <div>
        <h2>Rather just grab a date?</h2>
        <p>Skip the message and reserve your walk on the live booking calendar.</p>
      </div>
      <div class="cta-actions">
        <a class="btn btn-ghost" href="{{ home_url('/tours/') }}">See the tours</a>
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
<script>
/* Contact form — demo only, no network. Confirms inline. */
(function(){
  "use strict";
  var form = document.getElementById('contactForm');
  var status = document.getElementById('contactStatus');
  if(!form) return;
  form.addEventListener('submit', function(e){
    e.preventDefault();
    var name = document.getElementById('cName').value.trim();
    var email = document.getElementById('cEmail').value.trim();
    var msg = document.getElementById('cMessage').value.trim();
    if(!name || !email || !msg){
      status.textContent = 'Please add your name, email, and a short message.';
      status.style.color = 'var(--terracotta-dark)';
      return;
    }
    form.reset();
    status.textContent = 'Thanks, ' + name + '! This is a concept form, so nothing was sent — but on the live site your message would be on its way to the First Shot desk.';
    status.style.color = 'var(--teal-dark)';
  });
})();
</script>
@endsection
