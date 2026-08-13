{{--
  Template Name: Gallery
--}}

@extends('layouts.app')

@push('head')
<script type="application/ld+json">
{
  "@@context": "https://schema.org",
  "@@type": "Hotel",
  "@@id": "{{ home_url('/') }}#hotel",
  "name": "The Lantern & Laurel Inn",
  "url": "{{ home_url('/gallery/') }}",
  "image": "https://commons.wikimedia.org/wiki/Special:FilePath/Two_Buildings_in_the_Gettysburg_Historic_District.jpg?width=1600",
  "description": "Photo gallery of a boutique historic inn on Baltimore Street in downtown Gettysburg, Pennsylvania.",
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
  "geo": { "@@type": "GeoCoordinates", "latitude": 39.8309, "longitude": -77.2313 },
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
    { "@@type": "ListItem", "position": 2, "name": "Gallery", "item": "{{ home_url('/gallery/') }}" }
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
      <a href="{{ home_url('/gallery/') }}" class="is-active" aria-current="page">Gallery</a>
      <a href="{{ home_url('/contact/') }}">Contact</a>
    </nav>

    <div class="nav-actions">
      <a href="{{ home_url('/contact/') }}#book" class="btn btn-brass desktop-only btn-sm">Book a Stay</a>
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
    <a href="{{ home_url('/gallery/') }}" class="is-active" aria-current="page">Gallery</a>
    <a href="{{ home_url('/contact/') }}">Contact</a>
    <a href="{{ home_url('/contact/') }}#book" class="btn btn-brass btn-block">Book a Stay</a>
  </div>
</header>

<div class="page-hero">
  <div class="wrap">
    <span class="eyebrow">Gallery</span>
    <h1>A house that photographs the way it feels.</h1>
    <p class="lead">A look inside the inn and around the block — the parlor, the guest rooms, the breakfast table, and the historic streets of downtown Gettysburg just outside the door.</p>
  </div>
</div>

<nav class="breadcrumb" aria-label="Breadcrumb">
  <div class="wrap">
    <ol>
      <li><a href="{{ home_url('/') }}">Home</a></li>
      <li class="sep" aria-hidden="true">/</li>
      <li aria-current="page">Gallery</li>
    </ol>
  </div>
</nav>

<main id="main">

  <!-- ============ GALLERY: THE INN ============ -->
  <section class="gallery">
    <div class="wrap">
      <div class="section-head reveal">
        <span class="eyebrow">Inside the Inn</span>
        <h2>Rooms &amp; common spaces.</h2>
      </div>

      <div class="gal-grid">
        <div class="gal-tile wide tall g1 reveal"><span class="cap">Front Parlor</span></div>
        <div class="gal-tile g2 reveal"><span class="cap">Chamberlain Room</span></div>
        <div class="gal-tile g3 reveal"><span class="cap">Breakfast Table</span></div>
        <div class="gal-tile reveal" style="position:relative;">
          <img src="https://commons.wikimedia.org/wiki/Special:FilePath/Two_Buildings_in_the_Gettysburg_Historic_District.jpg?width=1600"
               alt="Historic brick storefronts in the Gettysburg Historic District near the inn on Baltimore Street"
               loading="lazy"
               onerror="this.style.display='none'">
          <span class="cap">Historic District</span>
        </div>
        <div class="gal-tile g4 reveal"><span class="cap">Courtyard Garden</span></div>
        <div class="gal-tile wide g5 reveal"><span class="cap">Steinwehr Suite Bath</span></div>
        <div class="gal-tile reveal" style="position:relative;">
          <img src="https://commons.wikimedia.org/wiki/Special:FilePath/Journey_Through_Hallowed_Ground_Byway_-_The_Shriver_House_Museum_-_NARA_-_7719712.jpg?width=1200"
               alt="Historic downtown Gettysburg house near the battlefield, a neighboring landmark to the inn"
               loading="lazy"
               onerror="this.style.display='none'">
          <span class="cap">Neighboring Landmark</span>
        </div>
      </div>
    </div>
  </section>

  <!-- ============ GALLERY: THE NEIGHBORHOOD ============ -->
  <section class="section-paper">
    <div class="wrap">
      <div class="section-head reveal">
        <span class="eyebrow">Around Baltimore Street</span>
        <h2>The neighborhood.</h2>
        <p>Downtown Gettysburg and the battlefield's northern edge, all within a short walk of the front porch.</p>
      </div>

      <div class="gal-grid">
        <div class="gal-tile wide reveal" style="position:relative;">
          <img src="https://commons.wikimedia.org/wiki/Special:FilePath/DowntownGettysburgPA.jpg?width=1600"
               alt="Downtown Gettysburg street near Lincoln Square with historic storefronts"
               loading="lazy"
               onerror="this.style.display='none'">
          <span class="cap">Toward Lincoln Square</span>
        </div>
        <div class="gal-tile reveal" style="position:relative;">
          <img src="https://commons.wikimedia.org/wiki/Special:FilePath/Gettysburg,_Pennsylvania_(6286097408).jpg?width=1200"
               alt="Historic Gettysburg, Pennsylvania streetscape near the inn"
               loading="lazy"
               onerror="this.style.display='none'">
          <span class="cap">Baltimore Street</span>
        </div>
        <div class="gal-tile g3 reveal"><span class="cap">Cemetery Hill Walk</span></div>
        <div class="gal-tile g1 reveal"><span class="cap">Porch at Dusk</span></div>
        <div class="gal-tile g2 reveal"><span class="cap">Autumn on the Square</span></div>
        <div class="gal-tile g4 reveal"><span class="cap">Garden Koi Pond</span></div>
      </div>

      <p style="text-align:center;color:var(--ink-soft);font-size:0.9rem;margin-top:28px;">
        Illustrative imagery for this design concept. Photographs of downtown Gettysburg are courtesy of Wikimedia Commons contributors.
      </p>
    </div>
  </section>

  <!-- ============ CTA BAND ============ -->
  <section class="cta-band">
    <div class="wrap cta-inner">
      <span class="eyebrow">See It In Person</span>
      <h2>Better in person than in pixels.</h2>
      <p>The fireplaces, the biscuits, the courtyard at dusk — come see the house for yourself.</p>
      <div class="cta-row">
        <a href="{{ home_url('/contact/') }}#book" class="btn btn-brass">Check Availability</a>
        <a href="{{ home_url('/rooms/') }}" class="btn btn-outline">See the Rooms</a>
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
@endsection
