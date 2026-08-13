{{--
  Template Name: Contact Ridgeline Outfitters
--}}

@extends('layouts.app')

@push('head')
<script type="application/ld+json">
{
  "@@context": "https://schema.org",
  "@@type": "SportingGoodsStore",
  "@@id": "{{ home_url('/') }}#store",
  "name": "Ridgeline Outfitters",
  "description": "Contact page for Ridgeline Outfitters, an outdoor gear shop in Gettysburg, PA.",
  "image": "{{ home_url('/preview.jpg/') }}",
  "url": "{{ home_url('/contact/') }}",
  "telephone": "+1-717-555-0120",
  "email": "gear@@ridgelineoutfitters.test",
  "priceRange": "$$",
  "contactPoint": {
    "@@type": "ContactPoint",
    "telephone": "+1-717-555-0120",
    "email": "gear@@ridgelineoutfitters.test",
    "contactType": "customer service",
    "areaServed": "US",
    "availableLanguage": "English"
  },
  "address": {
    "@@type": "PostalAddress",
    "streetAddress": "120 Buford Avenue",
    "addressLocality": "Gettysburg",
    "addressRegion": "PA",
    "postalCode": "17325",
    "addressCountry": "US"
  },
  "geo": { "@@type": "GeoCoordinates", "latitude": 39.8339, "longitude": -77.2380 },
  "openingHoursSpecification": [
    { "@@type": "OpeningHoursSpecification", "dayOfWeek": ["Monday","Tuesday","Wednesday","Thursday"], "opens": "10:00", "closes": "18:00" },
    { "@@type": "OpeningHoursSpecification", "dayOfWeek": ["Friday","Saturday"], "opens": "09:00", "closes": "20:00" },
    { "@@type": "OpeningHoursSpecification", "dayOfWeek": "Sunday", "opens": "11:00", "closes": "17:00" }
  ],
  "areaServed": [
    { "@@type": "City", "name": "Gettysburg" },
    { "@@type": "AdministrativeArea", "name": "Adams County, Pennsylvania" },
    "Biglerville, PA", "Littlestown, PA", "New Oxford, PA", "McSherrystown, PA", "Fairfield, PA", "Cashtown, PA", "Hanover, PA"
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

<!-- ===================== HEADER ===================== -->
<header class="site-header">
  <div class="container header-inner">
    <a href="{{ home_url('/') }}" class="brand" aria-label="Ridgeline Outfitters home">
      <svg class="brand-mark" viewBox="0 0 48 48" fill="none" aria-hidden="true">
        <circle cx="24" cy="24" r="22" fill="#1a2e23" stroke="#ff5a1f" stroke-width="2"/>
        <path d="M12 32L22 14L27 24L32 16L38 32H12Z" fill="#f8f6f0"/>
        <circle cx="24" cy="24" r="22" stroke="#efe9db" stroke-width="0.5" opacity="0.3"/>
      </svg>
      <span>Ridgeline Outfitters<small>Gettysburg, PA · Buford Ave</small></span>
    </a>

    <nav class="main-nav" aria-label="Primary">
      <a href="{{ home_url('/') }}">Home</a>
      <a href="{{ home_url('/shop/') }}">Shop Gear</a>
      <a href="{{ home_url('/guides/') }}">Gear Guides</a>
      <a href="{{ home_url('/about/') }}">About</a>
      <a href="{{ home_url('/visit/') }}">Visit Us</a>
      <a href="{{ home_url('/contact/') }}" class="is-active" aria-current="page">Contact</a>
    </nav>

    <div class="header-actions">
      <a class="icon-btn" href="{{ home_url('/shop/') }}" aria-label="View wishlist on the Shop page">
        <svg viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2"><path d="M12 21s-7.5-4.6-10-9.3C.4 8 2 4.5 5.6 4.1c2-.2 3.7.8 4.9 2.5 1.2-1.7 2.9-2.7 4.9-2.5C19 4.5 20.6 8 20 11.7 17.5 16.4 12 21 12 21z"/></svg>
      </a>
      <a class="icon-btn" href="{{ home_url('/shop/') }}" aria-label="View cart on the Shop page">
        <svg viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2"><path d="M3 3h2l.4 2M7 13h10l3-8H5.4M7 13L5.4 5M7 13l-1.7 6.4A1 1 0 006.3 20H18M10 22a1 1 0 100-2 1 1 0 000 2zM18 22a1 1 0 100-2 1 1 0 000 2z"/></svg>
      </a>
      <button class="icon-btn hamburger" id="menuToggle" aria-label="Open menu" aria-haspopup="true" aria-expanded="false" aria-controls="mobileMenu">
        <svg viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2"><path d="M3 6h18M3 12h18M3 18h18"/></svg>
      </button>
    </div>
  </div>

  <nav id="mobileMenu" class="mobile-menu" aria-label="Mobile" hidden>
    <a href="{{ home_url('/') }}">Home</a>
    <a href="{{ home_url('/shop/') }}">Shop Gear</a>
    <a href="{{ home_url('/guides/') }}">Gear Guides</a>
    <a href="{{ home_url('/about/') }}">About</a>
    <a href="{{ home_url('/visit/') }}">Visit Us</a>
    <a href="{{ home_url('/contact/') }}" class="is-active" aria-current="page">Contact</a>
  </nav>
</header>

<!-- ===================== BREADCRUMB ===================== -->
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

  <!-- ===================== PAGE HERO ===================== -->
  <section class="hero page-hero" id="top">
    <div class="container hero-grid">
      <div class="hero-copy">
        <span class="eyebrow on-dark">Contact · Gettysburg, PA</span>
        <h1>Questions about gear? <em>Talk to a hiker.</em></h1>
        <p>Fit help, gear repair, a special order, or checking whether something's in stock before you drive in from across Adams County — reach the crew directly and a real person on Buford Avenue will get back to you.</p>
        <div class="hero-ctas">
          <a href="tel:+17175550120" class="btn btn-primary">Call (717) 555-0120</a>
          <a href="mailto:gear@@ridgelineoutfitters.test" class="btn btn-outline on-dark">Email the shop</a>
        </div>
      </div>
      <div class="hero-visual">
        <div class="visual-overlay"></div>
        <div class="compass-badge">
          <svg viewBox="0 0 24 24" fill="none" stroke="#ff8654" stroke-width="1.6" aria-hidden="true"><circle cx="12" cy="12" r="9"/><path d="M15 9l-2 5-5 2 2-5z" fill="#ff8654" stroke="none"/></svg>
        </div>
        <div class="visual-tag">
          <span class="eyebrow">Real people</span>
          <p>"Five locals, one counter, on Buford Ave."</p>
        </div>
      </div>
    </div>
  </section>

  <!-- ===================== CONTACT ===================== -->
  <section id="reach-us">
    <div class="container">
      <div class="contact-grid">
        <div class="contact-form-wrap">
          <div class="section-head reveal" style="margin-bottom:24px;">
            <span class="eyebrow">Send us a note</span>
            <h2>Message the shop</h2>
            <p>We answer messages during shop hours, usually same day. For urgent stock questions, calling is fastest.</p>
          </div>
          <form class="contact-form reveal" id="contactForm" novalidate>
            <div class="form-row two">
              <div class="form-field">
                <label for="cName">Name</label>
                <input type="text" id="cName" name="name" autocomplete="name" required>
              </div>
              <div class="form-field">
                <label for="cEmail">Email</label>
                <input type="email" id="cEmail" name="email" autocomplete="email" required>
              </div>
            </div>
            <div class="form-row two">
              <div class="form-field">
                <label for="cPhone">Phone (optional)</label>
                <input type="tel" id="cPhone" name="phone" autocomplete="tel">
              </div>
              <div class="form-field">
                <label for="cTopic">Topic</label>
                <select id="cTopic" name="topic">
                  <option>Fit &amp; gear advice</option>
                  <option>Special order</option>
                  <option>Gear repair</option>
                  <option>In-store pickup / online order</option>
                  <option>Something else</option>
                </select>
              </div>
            </div>
            <div class="form-row">
              <div class="form-field">
                <label for="cMessage">How can we help?</label>
                <textarea id="cMessage" name="message" placeholder="Tell us about your trip, your gear question, or what you're looking for…" required></textarea>
              </div>
            </div>
            <button type="submit" class="btn btn-primary btn-block">Send Message</button>
            <p class="form-success" id="contactSuccess" role="status">Thanks — your message is ready to send. (Demo form: nothing is actually transmitted or stored.)</p>
            <p class="form-note">Demo form — no data is sent or stored. Prefer to talk? Call (717) 555-0120.</p>
          </form>
        </div>

        <aside class="visit-card reveal" aria-label="Shop contact details">
          <h3>Reach us directly</h3>
          <div class="visit-row">
            <svg viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" aria-hidden="true"><path d="M12 21s-7-6.5-7-11a7 7 0 1114 0c0 4.5-7 11-7 11z"/><circle cx="12" cy="10" r="2.5"/></svg>
            <p>120 Buford Avenue<br>Gettysburg, PA 17325</p>
          </div>
          <div class="visit-row">
            <svg viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" aria-hidden="true"><path d="M22 16.9v3a2 2 0 01-2.2 2 19.8 19.8 0 01-8.6-3.1 19.5 19.5 0 01-6-6A19.8 19.8 0 012.1 4.2 2 2 0 014.1 2h3a2 2 0 012 1.7c.1.9.3 1.8.6 2.7a2 2 0 01-.4 2.1L8.1 9.6a16 16 0 006.3 6.3l1.1-1.1a2 2 0 012.1-.4c.9.3 1.8.5 2.7.6a2 2 0 011.7 2.1z"/></svg>
            <p><a href="tel:+17175550120">(717) 555-0120</a></p>
          </div>
          <div class="visit-row">
            <svg viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" aria-hidden="true"><rect x="3" y="5" width="18" height="16" rx="2"/><path d="M16 3v4M8 3v4M3 10h18"/></svg>
            <p><a href="mailto:gear@@ridgelineoutfitters.test">gear@@ridgelineoutfitters.test</a></p>
          </div>
          <div class="visit-row">
            <svg viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" aria-hidden="true"><circle cx="12" cy="12" r="9"/><path d="M12 7v5l3 3"/></svg>
            <table class="hours-table">
              <tbody>
                <tr><td>Mon – Thu</td><td>10:00 – 6:00</td></tr>
                <tr><td>Fri – Sat</td><td>9:00 – 8:00</td></tr>
                <tr><td>Sunday</td><td>11:00 – 5:00</td></tr>
              </tbody>
            </table>
          </div>
          <div class="visit-pills">
            <span class="visit-pill">Fit help</span>
            <span class="visit-pill">Gear repair</span>
            <span class="visit-pill">Special orders</span>
          </div>
          <p style="margin-top:18px;"><a href="{{ home_url('/visit/') }}" class="btn btn-outline btn-sm">Directions &amp; parking</a></p>
        </aside>
      </div>
    </div>
  </section>

  <!-- ===================== CTA ===================== -->
  <section class="collections">
    <div class="container">
      <div class="cta-band reveal">
        <span class="eyebrow on-dark">Rather browse first?</span>
        <h2>Start with the gear wall</h2>
        <p>Filter apparel, footwear, packs and camp gear, then message us about anything you want to try on or special-order.</p>
        <div class="hero-ctas">
          <a href="{{ home_url('/shop/') }}" class="btn btn-primary">Shop the Gear Wall</a>
          <a href="{{ home_url('/guides/') }}" class="btn btn-outline on-dark">Read the trail guides</a>
        </div>
      </div>
    </div>
  </section>

</main>

<!-- ===================== FOOTER ===================== -->
<footer class="site-footer">
  <div class="container">
    <div class="footer-grid">
      <div class="footer-brand">
        <a href="{{ home_url('/') }}" class="brand">
          <svg class="brand-mark" viewBox="0 0 48 48" fill="none" aria-hidden="true">
            <circle cx="24" cy="24" r="22" fill="#1a2e23" stroke="#ff5a1f" stroke-width="2"/>
            <path d="M12 32L22 14L27 24L32 16L38 32H12Z" fill="#f8f6f0"/>
          </svg>
          <span>Ridgeline Outfitters</span>
        </a>
        <p>Outdoor &amp; adventure gear for the trails, ridgelines, and battlefield paths of Adams County, PA.</p>
        <div class="social-row">
          <a href="#" onclick="return false;" aria-label="Ridgeline Outfitters on Instagram"><svg viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="1.7"><rect x="3" y="3" width="18" height="18" rx="5"/><circle cx="12" cy="12" r="4"/><circle cx="17.2" cy="6.8" r="1"/></svg></a>
          <a href="#" onclick="return false;" aria-label="Ridgeline Outfitters on Facebook"><svg viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="1.7"><path d="M15 8h2V4h-2a4 4 0 00-4 4v2H9v4h2v6h4v-6h2.5l.5-4H15V8z"/></svg></a>
        </div>
      </div>
      <div class="footer-col">
        <h4>Explore</h4>
        <ul>
          <li><a href="{{ home_url('/') }}">Home</a></li>
          <li><a href="{{ home_url('/shop/') }}">Shop Gear</a></li>
          <li><a href="{{ home_url('/guides/') }}">Gear Guides</a></li>
          <li><a href="{{ home_url('/about/') }}">About</a></li>
          <li><a href="{{ home_url('/visit/') }}">Visit Us</a></li>
          <li><a href="{{ home_url('/contact/') }}">Contact</a></li>
        </ul>
      </div>
      <div class="footer-col">
        <h4>Visit the Shop</h4>
        <ul>
          <li><a href="{{ home_url('/visit/') }}">120 Buford Avenue<br>Gettysburg, PA 17325</a></li>
          <li><a href="tel:+17175550120">(717) 555-0120</a></li>
          <li><a href="mailto:gear@@ridgelineoutfitters.test">gear@@ridgelineoutfitters.test</a></li>
        </ul>
      </div>
      <div class="footer-col">
        <h4>Hours</h4>
        <table class="hours-table">
          <tbody>
            <tr><td>Mon – Thu</td><td>10:00 – 6:00</td></tr>
            <tr><td>Fri – Sat</td><td>9:00 – 8:00</td></tr>
            <tr><td>Sunday</td><td>11:00 – 5:00</td></tr>
          </tbody>
        </table>
        <p style="color:var(--stone-300); font-size:0.82rem; margin-top:14px;">Proudly serving Gettysburg, Biglerville, Littlestown, New Oxford, McSherrystown, Fairfield, Cashtown &amp; Hanover.</p>
      </div>
    </div>
    <div class="footer-bottom">
      <span>© <span data-year>2026</span> Ridgeline Outfitters. All rights reserved.</span>
      <span class="mono">120 Buford Ave, Gettysburg, PA 17325 · (717) 555-0120</span>
    </div>
    <p class="footer-fine">Design concept by Ridges &amp; Valleys Studio to demonstrate a future website for Ridgeline Outfitters, a fictional business. Product names, prices, inventory, and reviews shown are illustrative only. No transactions are processed on this site.</p>
  </div>
</footer>

<!-- ===================== CONCEPT BADGE ===================== -->
<a href="#" class="concept-badge" onclick="return false;">
  <span class="dot" aria-hidden="true"></span>
  Concept · Ridges &amp; Valleys Studio
</a>
@endsection
