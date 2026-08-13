{{--
  Template Name: Contact & FAQ
--}}

@extends('layouts.app')

@push('head')
<script type="application/ld+json">
{
  "@@context": "https://schema.org",
  "@@type": "TravelAgency",
  "@@id": "{{ home_url('/') }}#business",
  "name": "Hallowed Ground Battlefield Tours",
  "url": "{{ home_url('/contact/') }}",
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
    "latitude": 39.831,
    "longitude": -77.228
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
      "name": "Contact & FAQ",
      "item": "{{ home_url('/contact/') }}"
    }
  ]
}
</script>
<script type="application/ld+json">
{
  "@@context": "https://schema.org",
  "@@type": "FAQPage",
  "mainEntity": [
    {
      "@@type": "Question",
      "name": "Where do tours meet?",
      "acceptedAnswer": {
        "@@type": "Answer",
        "text": "Walking and bus tours meet at our ticket office at 271 Baltimore Street, Gettysburg, PA 17325, a short walk from Lincoln Square and the Gettysburg National Military Park. The Ghosts of Gettysburg Lantern Walk meets at the flagpole on Lincoln Square downtown. Your confirmation email includes exact directions and parking notes."
      }
    },
    {
      "@@type": "Question",
      "name": "Are tours accessible for guests with mobility limitations?",
      "acceptedAnswer": {
        "@@type": "Answer",
        "text": "The Pickett's Charge Deluxe Bus Tour is ADA-accessible and requires no walking beyond short stops. Our walking tours involve uneven, sometimes sloped terrain and are not wheelchair accessible. Call us at (717) 555-0271 and we're glad to help you choose the right tour or arrange accommodations."
      }
    },
    {
      "@@type": "Question",
      "name": "What happens in bad weather?",
      "acceptedAnswer": {
        "@@type": "Answer",
        "text": "Tours operate rain or shine, including light rain and cold. In the event of severe weather, lightning, or extreme heat advisories, we'll contact you by phone or email at least 2 hours before departure to reschedule or refund."
      }
    },
    {
      "@@type": "Question",
      "name": "What is your cancellation and refund policy?",
      "acceptedAnswer": {
        "@@type": "Answer",
        "text": "Cancel or reschedule up to 24 hours before your tour for a full refund, no questions asked. Cancellations inside 24 hours are eligible for a credit toward a future tour. No-shows are non-refundable."
      }
    },
    {
      "@@type": "Question",
      "name": "Where should I park?",
      "acceptedAnswer": {
        "@@type": "Answer",
        "text": "There is a free 20-minute loading zone in front of the ticket office on Baltimore Street. All-day public lots and metered street parking are within a 2\u20135 minute walk, including the Racehorse Alley parking garage and lots near Lincoln Square."
      }
    },
    {
      "@@type": "Question",
      "name": "Do you offer private and group tours?",
      "acceptedAnswer": {
        "@@type": "Answer",
        "text": "Yes. The Sunrise Private Battlefield Experience is built for up to six guests with a customized route, and we arrange private bus and walking tours for families, reunions, schools, and corporate groups. Call (717) 555-0271 or email tours@@hallowedground.test to plan a custom date."
      }
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
      <ul><li><a href="{{ home_url('/') }}">Home</a></li><li><a href="{{ home_url('/tours/') }}">Tours</a></li><li><a href="{{ home_url('/guides/') }}">Our Guides</a></li><li><a href="{{ home_url('/area/') }}">The Area</a></li><li><a href="{{ home_url('/contact/') }}" class="is-active" aria-current="page">Contact</a></li></ul>
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
  <ul><li><a href="{{ home_url('/') }}">Home</a></li><li><a href="{{ home_url('/tours/') }}">Tours</a></li><li><a href="{{ home_url('/guides/') }}">Our Guides</a></li><li><a href="{{ home_url('/area/') }}">The Area</a></li><li><a href="{{ home_url('/contact/') }}" class="is-active" aria-current="page">Contact</a></li></ul>
  <a href="tel:+17175550271" class="btn btn-outline btn-block" style="color:#fff;">Call (717) 555-0271</a>
  <a href="{{ home_url('/book/') }}" class="btn btn-primary btn-block" style="margin-top:0.8rem;">Book a Tour</a>
</nav>

<main id="main">
<nav class="breadcrumb" aria-label="Breadcrumb"><div class="wrap"><ol><li><a href="{{ home_url('/') }}">Home</a></li><li class="sep" aria-hidden="true">/</li><li aria-current="page">Contact &amp; FAQ</li></ol></div></nav>
  <section class="page-intro">
    <div class="wrap reveal">
      <span class="eyebrow">Contact &amp; FAQ</span>
      <h1>Contact Hallowed Ground Battlefield Tours</h1>
      <p>Questions about a tour, accessibility, or a private group in Gettysburg, PA? Reach us by phone, email, or the form below &mdash; and check the frequently asked questions before you go.</p>
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
      <div class="contact-grid reveal">
        <div>
          <span class="eyebrow">Reach Us</span>
          <h2 style="margin-block:0.4rem 1rem;">Ticket office &amp; guest services</h2>
          <div class="meet-card">
            <h3><svg width="20" height="20" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" aria-hidden="true"><path d="M12 2a8 8 0 00-8 8c0 5.5 8 12 8 12s8-6.5 8-12a8 8 0 00-8-8z"/><circle cx="12" cy="10" r="3"/></svg>Address</h3>
            <address class="nap-block">
              <b>Hallowed Ground Battlefield Tours</b>
              271 Baltimore Street<br>Gettysburg, PA 17325
            </address>
          </div>
          <div class="meet-card">
            <h3><svg width="20" height="20" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" aria-hidden="true"><path d="M22 16.92v3a2 2 0 01-2.18 2 19.79 19.79 0 01-8.63-3.07 19.5 19.5 0 01-6-6 19.79 19.79 0 01-3.07-8.67A2 2 0 014.11 2h3a2 2 0 012 1.72c.127.96.361 1.902.7 2.81a2 2 0 01-.45 2.11L8.09 9.91a16 16 0 006 6l1.27-1.27a2 2 0 012.11-.45c.908.339 1.85.573 2.81.7A2 2 0 0122 16.92z"/></svg>Phone &amp; Email</h3>
            <p><a href="tel:+17175550271" class="nap-block" style="font-weight:600;">(717) 555-0271</a><br><a href="mailto:tours@@hallowedground.test" style="color:var(--slate-700); font-weight:600;">tours@@hallowedground.test</a></p>
          </div>
          <div class="meet-card">
            <h3><svg width="20" height="20" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" aria-hidden="true"><circle cx="12" cy="12" r="9"/><path d="M12 7v5l3 2"/></svg>Office Hours</h3>
            <p>Mon&ndash;Sun, 8:00 AM &ndash; 6:00 PM (April&ndash;November)<br>Thu&ndash;Sun, 9:00 AM &ndash; 4:00 PM (December&ndash;March)</p>
          </div>
        </div>

        <form class="contact-form" id="contactForm" novalidate>
          <span class="eyebrow">Send a Message</span>
          <h2 style="margin-block:0.4rem 1rem;">Ask us anything</h2>
          <div class="form-grid">
            <div class="field full">
              <label for="cName">Your name</label>
              <input type="text" id="cName" name="cName" autocomplete="name" required>
            </div>
            <div class="field full">
              <label for="cEmail">Email address</label>
              <input type="email" id="cEmail" name="cEmail" autocomplete="email" required>
            </div>
            <div class="field full">
              <label for="cMsg">Message</label>
              <textarea id="cMsg" name="cMsg" required></textarea>
            </div>
          </div>
          <span class="demo-flag" style="margin:0.9rem 0;">Demo form &mdash; this concept does not send email</span>
          <button type="submit" class="btn btn-primary btn-block">Send Message</button>
          <p id="contactNote" role="status" style="font-size:0.85rem; color:var(--color-ink-soft); margin-top:0.9rem;"></p>
        </form>
      </div>
    </div>
  </section>

  <section class="section section-alt">
    <div class="wrap">
      <div class="section-head reveal" style="margin-inline:auto; text-align:center;">
        <span class="eyebrow">Good to Know</span>
        <h2>Frequently asked questions.</h2>
      </div>
      <div class="faq-list reveal">
        <div class="faq-item" data-open="false">
          <button class="faq-q" aria-expanded="false">Where do tours meet?
            <span class="plus" aria-hidden="true"></span>
          </button>
          <div class="faq-a"><div class="faq-a-inner">Walking and bus tours meet at our ticket office at 271 Baltimore Street, Gettysburg, PA 17325, a short walk from Lincoln Square and the Gettysburg National Military Park. The Ghosts of Gettysburg Lantern Walk meets at the flagpole on Lincoln Square downtown. Your confirmation email includes exact directions and parking notes.</div></div>
        </div>
        <div class="faq-item" data-open="false">
          <button class="faq-q" aria-expanded="false">Are tours accessible for guests with mobility limitations?
            <span class="plus" aria-hidden="true"></span>
          </button>
          <div class="faq-a"><div class="faq-a-inner">The Pickett&#x27;s Charge Deluxe Bus Tour is ADA-accessible and requires no walking beyond short stops. Our walking tours involve uneven, sometimes sloped terrain and are not wheelchair accessible. Call us at (717) 555-0271 and we&#x27;re glad to help you choose the right tour or arrange accommodations.</div></div>
        </div>
        <div class="faq-item" data-open="false">
          <button class="faq-q" aria-expanded="false">What happens in bad weather?
            <span class="plus" aria-hidden="true"></span>
          </button>
          <div class="faq-a"><div class="faq-a-inner">Tours operate rain or shine, including light rain and cold. In the event of severe weather, lightning, or extreme heat advisories, we&#x27;ll contact you by phone or email at least 2 hours before departure to reschedule or refund.</div></div>
        </div>
        <div class="faq-item" data-open="false">
          <button class="faq-q" aria-expanded="false">What is your cancellation and refund policy?
            <span class="plus" aria-hidden="true"></span>
          </button>
          <div class="faq-a"><div class="faq-a-inner">Cancel or reschedule up to 24 hours before your tour for a full refund, no questions asked. Cancellations inside 24 hours are eligible for a credit toward a future tour. No-shows are non-refundable.</div></div>
        </div>
        <div class="faq-item" data-open="false">
          <button class="faq-q" aria-expanded="false">Where should I park?
            <span class="plus" aria-hidden="true"></span>
          </button>
          <div class="faq-a"><div class="faq-a-inner">There is a free 20-minute loading zone in front of the ticket office on Baltimore Street. All-day public lots and metered street parking are within a 2–5 minute walk, including the Racehorse Alley parking garage and lots near Lincoln Square.</div></div>
        </div>
        <div class="faq-item" data-open="false">
          <button class="faq-q" aria-expanded="false">Do you offer private and group tours?
            <span class="plus" aria-hidden="true"></span>
          </button>
          <div class="faq-a"><div class="faq-a-inner">Yes. The Sunrise Private Battlefield Experience is built for up to six guests with a customized route, and we arrange private bus and walking tours for families, reunions, schools, and corporate groups. Call (717) 555-0271 or email tours@@hallowedground.test to plan a custom date.</div></div>
        </div>
      </div>
    </div>
  </section>

  <section class="section">
    <div class="wrap">
      <div class="book-band reveal">
        <div>
          <span class="eyebrow eyebrow--light">Ready When You Are</span>
          <h2>Ready to book?</h2>
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
<script>
(function(){
  var f = document.getElementById("contactForm");
  if(!f) return;
  f.addEventListener("submit", function(e){
    e.preventDefault();
    var n = document.getElementById("cName").value.trim();
    var em = document.getElementById("cEmail").value.trim();
    var note = document.getElementById("contactNote");
    if(!n || !em){ note.textContent = "Please add your name and email so we can reply."; return; }
    note.textContent = "Thanks, " + n + " — this is a concept demo, so no message was actually sent. In a live site this would reach our guest-services team.";
    f.reset();
  });
})();
</script>
@endsection
