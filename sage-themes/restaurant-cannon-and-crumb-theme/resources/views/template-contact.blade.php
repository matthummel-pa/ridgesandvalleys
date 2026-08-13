{{--
  Template Name: Contact
--}}

@extends('layouts.app')

@push('head')
<script type="application/ld+json">
{
  "@@context": "https://schema.org",
  "@@type": "CafeOrCoffeeShop",
  "@@id": "{{ home_url('/') }}#business",
  "name": "Cannon & Crumb",
  "description": "All-day cafe and scratch bakery on Lincoln Square in downtown Gettysburg, PA. Contact us about visits, allergens, large orders, and catering.",
  "image": "{{ home_url('/preview.jpg/') }}",
  "url": "{{ home_url('/contact/') }}",
  "telephone": "+1-717-555-0107",
  "email": "hello@@cannonandcrumb.test",
  "priceRange": "$$",
  "servesCuisine": ["Cafe", "Bakery", "Breakfast", "Brunch", "Coffee", "American"],
  "menu": "{{ home_url('/menu/') }}",
  "hasMenu": "{{ home_url('/menu/') }}",
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
    { "@@type": "ListItem", "position": 2, "name": "Contact", "item": "{{ home_url('/contact/') }}" }
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
      <a href="{{ home_url('/visit/') }}">Visit</a>
      <a href="{{ home_url('/contact/') }}" class="is-active" aria-current="page">Contact</a>
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
    <li aria-current="page">Contact</li>
  </ol>
</nav>

<main id="main">

  <section class="page-hero">
    <div class="container">
      <span class="eyebrow">Contact</span>
      <h1>Say hello to Cannon &amp; Crumb.</h1>
      <p>Questions about hours, allergens, a big order, or catering in the Gettysburg area? Call, email, or drop us a note below — we're happy to help and usually reply within a business day.</p>
    </div>
  </section>

  <!-- CONTACT GRID -->
  <section>
    <div class="container">
      <div class="contact-grid">

        <!-- FORM -->
        <div class="reveal">
          <div class="section-head" style="text-align:left; margin:0 0 1.5rem;">
            <span class="eyebrow">Send a Message</span>
            <h2 style="font-size:clamp(1.7rem,3.5vw,2.3rem);">We'd love to hear from you</h2>
          </div>
          <form class="contact-form" id="contact-form" novalidate>
            <div class="form-field">
              <label for="cf-name">Name</label>
              <input type="text" id="cf-name" name="name" autocomplete="name" required>
            </div>
            <div class="form-field">
              <label for="cf-email">Email</label>
              <input type="email" id="cf-email" name="email" autocomplete="email" required>
            </div>
            <div class="form-field">
              <label for="cf-topic">What's this about?</label>
              <select id="cf-topic" name="topic">
                <option>General question</option>
                <option>Hours &amp; visiting</option>
                <option>Allergens &amp; dietary</option>
                <option>Large / group order</option>
                <option>Catering &amp; events</option>
                <option>Something else</option>
              </select>
            </div>
            <div class="form-field">
              <label for="cf-message">Message</label>
              <textarea id="cf-message" name="message" required></textarea>
            </div>
            <button type="submit" class="btn btn-primary" style="width:100%;">Send Message</button>
            <p class="form-note">This is a design concept — the form shows a confirmation but no message is actually sent.</p>
            <div class="form-success" id="form-success" role="status">Thanks! This is a demo form, so nothing was sent — on a live site your message would land in our inbox and we'd reply within a business day.</div>
          </form>
        </div>

        <!-- DETAILS -->
        <div class="reveal">
          <div class="hours-card">
            <h3>Reach us directly</h3>
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

            <h3 style="margin-top:2rem;">Hours</h3>
            <table class="hours-table">
              <tbody>
                <tr><td>Mon – Thu</td><td>7:00 AM – 3:00 PM</td></tr>
                <tr><td>Friday</td><td>7:00 AM – 8:00 PM</td></tr>
                <tr><td>Saturday</td><td>7:00 AM – 8:00 PM</td></tr>
                <tr><td>Sunday</td><td>8:00 AM – 2:00 PM</td></tr>
              </tbody>
            </table>

            <div style="margin-top:1.5rem;">
              <a href="{{ home_url('/visit/') }}" class="btn btn-outline btn-sm">Directions &amp; parking →</a>
            </div>
          </div>

          <div class="map-card reveal" role="img" aria-label="Stylized map showing Cannon & Crumb at Lincoln Square in downtown Gettysburg" style="margin-top:1.5rem; min-height:240px;">
            <div class="map-road h" aria-hidden="true"></div>
            <div class="map-road v" aria-hidden="true"></div>
            <div class="map-pin">
              <svg viewBox="0 0 24 24" fill="currentColor" aria-hidden="true"><path d="M12 2C7.6 2 4 5.6 4 10c0 6 8 12 8 12s8-6 8-12c0-4.4-3.6-8-8-8zm0 11a3 3 0 1 1 0-6 3 3 0 0 1 0 6z"/></svg>
              <span class="pin-label">7 Lincoln Square</span>
            </div>
          </div>
        </div>

      </div>
    </div>
  </section>

  <!-- CONTACT FAQ -->
  <section class="hours-section">
    <div class="container">
      <div class="section-head reveal">
        <span class="eyebrow">Quick Answers</span>
        <h2>Before you write</h2>
      </div>
      <div class="faq-list">
        <div class="faq-item reveal">
          <h3>Do you take reservations?</h3>
          <p>We're a walk-in cafe, so no reservations needed. For groups or a guaranteed spread, see <a href="{{ home_url('/catering/') }}">catering</a> or <a href="{{ home_url('/order/') }}">order ahead</a> for pickup.</p>
        </div>
        <div class="faq-item reveal">
          <h3>How do I place a large or group order?</h3>
          <p>For under ~8 people, our <a href="{{ home_url('/order/') }}">online ordering</a> works great. For bigger groups, use the form above or call and we'll set you up.</p>
        </div>
        <div class="faq-item reveal">
          <h3>Where are you located?</h3>
          <p>Right on the Square at 7 Lincoln Square, Gettysburg, PA 17325 — see our <a href="{{ home_url('/visit/') }}">Visit page</a> for parking and directions.</p>
        </div>
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
<script>
  (function(){
    "use strict";
    var form = document.getElementById('contact-form');
    var success = document.getElementById('form-success');
    if(!form) return;
    form.addEventListener('submit', function(e){
      e.preventDefault();
      if(!form.checkValidity()){ form.reportValidity(); return; }
      success.classList.add('show');
      form.querySelectorAll('input, textarea, select').forEach(function(el){ el.value = el.tagName === 'SELECT' ? el.options[0].value : ''; });
      success.focus && success.focus();
      success.scrollIntoView({behavior:'smooth', block:'center'});
    });
  })();
</script>
@endsection
