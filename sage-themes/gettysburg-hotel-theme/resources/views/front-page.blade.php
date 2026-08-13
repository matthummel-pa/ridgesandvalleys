{{-- Front page: concept home --}}

@extends('layouts.app')

@push('head')
<script type="application/ld+json">
{
  "@@context": "https://schema.org",
  "@@type": "Hotel",
  "@@id": "{{ home_url('/') }}#hotel",
  "name": "The Lantern & Laurel Inn",
  "url": "{{ home_url('/') }}",
  "image": "https://commons.wikimedia.org/wiki/Special:FilePath/Gettysburg,_Pennsylvania_(6286097408).jpg?width=1600",
  "description": "A nine-room boutique historic inn on Baltimore Street in downtown Gettysburg, Pennsylvania, a short walk from Lincoln Square and Gettysburg National Military Park.",
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
  "openingHoursSpecification": [
    {
      "@@type": "OpeningHoursSpecification",
      "dayOfWeek": ["Monday","Tuesday","Wednesday","Thursday","Friday","Saturday","Sunday"],
      "opens": "07:00",
      "closes": "22:00"
    }
  ],
  "amenityFeature": [
    { "@@type": "LocationFeatureSpecification", "name": "Full country breakfast", "value": true },
    { "@@type": "LocationFeatureSpecification", "name": "Free private parking", "value": true },
    { "@@type": "LocationFeatureSpecification", "name": "Free WiFi", "value": true },
    { "@@type": "LocationFeatureSpecification", "name": "Working fireplaces", "value": true }
  ],
  "areaServed": [
    { "@@type": "City", "name": "Gettysburg, PA" },
    { "@@type": "AdministrativeArea", "name": "Adams County, PA" }
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
      <a href="{{ home_url('/') }}" class="is-active" aria-current="page">Home</a>
      <a href="{{ home_url('/rooms/') }}">Rooms</a>
      <a href="{{ home_url('/amenities/') }}">Amenities</a>
      <a href="{{ home_url('/area/') }}">The Area</a>
      <a href="{{ home_url('/gallery/') }}">Gallery</a>
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
    <a href="{{ home_url('/') }}" class="is-active" aria-current="page">Home</a>
    <a href="{{ home_url('/rooms/') }}">Rooms</a>
    <a href="{{ home_url('/amenities/') }}">Amenities</a>
    <a href="{{ home_url('/area/') }}">The Area</a>
    <a href="{{ home_url('/gallery/') }}">Gallery</a>
    <a href="{{ home_url('/contact/') }}">Contact</a>
    <a href="{{ home_url('/contact/') }}#book" class="btn btn-brass btn-block">Book a Stay</a>
  </div>
</header>

<main id="main">

  <!-- ============ HERO ============ -->
  <section id="top" class="hero">
    <div class="hero-bg-fallback" aria-hidden="true"></div>
    <div class="hero-bg" aria-hidden="true"></div>
    <div class="hero-content">
      <div class="wrap">
        <div class="hero-inner">
          <span class="eyebrow">Est. 1863 &middot; Baltimore Street</span>
          <h1>A candlelit welcome, five minutes from Lincoln Square.</h1>
          <p class="sub">Nine restored rooms in a brick townhouse on the edge of the Gettysburg historic district — wide-plank floors, working fireplaces, and a wraparound porch built for slow mornings.</p>
          <div class="hero-cta-row">
            <a href="#book" class="btn btn-brass">Check Availability</a>
            <a href="{{ home_url('/rooms/') }}" class="btn btn-outline">View Rooms</a>
          </div>
        </div>

        <form class="booking-bar" id="book" action="contact.html" aria-label="Check availability">
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
    </div>
  </section>

  <!-- ============ WELCOME / STORY ============ -->
  <section class="story" id="story">
    <div class="wrap story-grid">
      <div class="story-art reveal">
        <div class="laurel">
          <span class="yr">1863</span>
          <span class="cap">Original brick &middot; restored 2019</span>
        </div>
      </div>
      <div class="story-copy reveal">
        <span class="eyebrow">Our Story</span>
        <p class="lede">"We didn't want a museum. We wanted a house you'd actually want to stay in."</p>
        <p>The building at 42 Baltimore Street has stood since before the battle — a merchant's home, then a boarding house, then, for a long stretch, nothing at all. In 2019 we spent fourteen months bringing it back: reglazed windows, exposed brick, and a kitchen that now turns out the same buttermilk biscuits every morning at 7:30.</p>
        <p>Every room keeps something original — a mantel, a floorboard, a transom window — paired with a genuinely comfortable bed, blackout drapes, and Wi-Fi that actually reaches the third floor. We're a two-minute walk from Lincoln Square in downtown Gettysburg and a short stroll from the battlefield's Baltimore Street entrance.</p>
        <div class="story-stats">
          <div class="stat"><b>9</b><span>Guest Rooms</span></div>
          <div class="stat"><b>1863</b><span>Building Built</span></div>
          <div class="stat"><b>0.3mi</b><span>To Lincoln Square</span></div>
        </div>
      </div>
    </div>
  </section>

  <!-- ============ TEASERS ============ -->
  <section class="section-cream" id="explore">
    <div class="wrap">
      <div class="section-head center reveal">
        <span class="eyebrow">Explore the Inn</span>
        <h2>Everything you need for a Gettysburg stay.</h2>
        <p>Five short walks from the front porch to the heart of the historic district — and a whole small town in between.</p>
      </div>

      <div class="teaser-grid">
        <a class="teaser reveal" href="{{ home_url('/rooms/') }}">
          <div class="amen-icon"><svg viewBox="0 0 24 24" fill="none" stroke-width="1.6"><path d="M8 3v4M16 3v4M3 9h18"/><rect x="3" y="5" width="18" height="16" rx="2"/></svg></div>
          <h3>Rooms &amp; Rates</h3>
          <p>Nine individually restored rooms, from a cozy single to the skylit Steinwehr Suite. See rates, beds, and what's included.</p>
          <span class="more">View rooms &rarr;</span>
        </a>
        <a class="teaser reveal" href="{{ home_url('/amenities/') }}">
          <div class="amen-icon"><svg viewBox="0 0 24 24" fill="none" stroke-width="1.6"><path d="M4 13h16v3a5 5 0 0 1-5 5H9a5 5 0 0 1-5-5v-3Z"/><path d="M4 13a8 8 0 0 1 16 0"/><path d="M2 21h20"/></svg></div>
          <h3>Amenities &amp; Services</h3>
          <p>Full country breakfast, working fireplaces, guided battlefield walks, private parking, and a walled courtyard garden.</p>
          <span class="more">See what's included &rarr;</span>
        </a>
        <a class="teaser reveal" href="{{ home_url('/area/') }}">
          <div class="amen-icon"><svg viewBox="0 0 24 24" fill="none" stroke-width="1.6"><path d="M12 22s7-6.5 7-12a7 7 0 0 0-14 0c0 5.5 7 12 7 12Z"/><circle cx="12" cy="10" r="2.5"/></svg></div>
          <h3>The Area</h3>
          <p>What's walkable from Baltimore Street, directions from US-15 and US-30, parking notes, and the nearby Adams County towns.</p>
          <span class="more">Plan your visit &rarr;</span>
        </a>
        <a class="teaser reveal" href="{{ home_url('/gallery/') }}">
          <div class="amen-icon"><svg viewBox="0 0 24 24" fill="none" stroke-width="1.6"><rect x="3" y="4" width="18" height="16" rx="2"/><circle cx="9" cy="10" r="2"/><path d="m21 16-5-5L5 20"/></svg></div>
          <h3>Gallery</h3>
          <p>The parlor, the guest rooms, the breakfast table, and the courtyard at dusk — a house that photographs the way it feels.</p>
          <span class="more">Browse photos &rarr;</span>
        </a>
        <a class="teaser reveal" href="{{ home_url('/contact/') }}">
          <div class="amen-icon"><svg viewBox="0 0 24 24" fill="none" stroke-width="1.6"><path d="M4 4h16v12H7l-3 3V4Z"/></svg></div>
          <h3>Contact &amp; Booking</h3>
          <p>Check availability, reach the front desk, or ask us anything about your dates. We answer every message ourselves.</p>
          <span class="more">Book your dates &rarr;</span>
        </a>
      </div>
    </div>
  </section>

  <div class="divider-quote reveal" style="padding:70px 0;">
    <p>&ldquo;Some inns give you a room. Lantern &amp; Laurel gives you a reason to slow down.&rdquo;</p>
  </div>

  <!-- ============ LOCATION TEASER ============ -->
  <section class="location" id="location">
    <div class="wrap loc-grid">
      <div class="loc-photo reveal">
        <img src="https://commons.wikimedia.org/wiki/Special:FilePath/DowntownGettysburgPA.jpg?width=1600"
             alt="Downtown Gettysburg street view near Lincoln Square, with historic storefronts and brick buildings"
             loading="lazy"
             onerror="this.style.display='none'">
      </div>
      <div class="reveal">
        <div class="section-head on-dark" style="margin-bottom:30px;">
          <span class="eyebrow">Location</span>
          <h2>Everything worth walking to.</h2>
          <p>42 Baltimore Street sits between Lincoln Square and the battlefield's northern entrance — most everything in Gettysburg is a ten-minute walk.</p>
        </div>
        <ul class="loc-list">
          <li class="loc-item">
            <span class="mi">0.4 mi</span>
            <div><h4>Gettysburg National Military Park</h4><p>Walk straight down Baltimore Street to the Soldiers' National Cemetery entrance.</p></div>
          </li>
          <li class="loc-item">
            <span class="mi">0.3 mi</span>
            <div><h4>Lincoln Square Shops &amp; Cafes</h4><p>Bookstores, galleries, and coffee around the square where Lincoln once stayed the night.</p></div>
          </li>
          <li class="loc-item">
            <span class="mi">0.5 mi</span>
            <div><h4>Steinwehr Avenue &amp; Visitor Center</h4><p>Restaurants, the Cyclorama, and the museum — a flat fifteen-minute walk south.</p></div>
          </li>
        </ul>
        <a href="{{ home_url('/area/') }}" class="btn btn-brass btn-sm" style="margin-top:24px;">Full area guide &amp; directions</a>
      </div>
    </div>
  </section>

  <!-- ============ REVIEWS TEASER ============ -->
  <section class="reviews">
    <div class="wrap">
      <div class="section-head center reveal" style="margin-left:auto;margin-right:auto;">
        <span class="eyebrow">Guest Reviews</span>
        <h2>What guests are saying.</h2>
        <p style="font-size:0.85rem;color:var(--ink-soft);">Illustrative sample reviews for this design concept.</p>
      </div>

      <div class="review-grid">
        <article class="review-card reveal">
          <div class="stars" aria-hidden="true">
            <svg viewBox="0 0 20 20"><path d="M10 1l2.6 5.7 6.2.6-4.7 4.2 1.4 6.1L10 14.7 4.5 17.6l1.4-6.1L1.2 7.3l6.2-.6L10 1z"/></svg>
            <svg viewBox="0 0 20 20"><path d="M10 1l2.6 5.7 6.2.6-4.7 4.2 1.4 6.1L10 14.7 4.5 17.6l1.4-6.1L1.2 7.3l6.2-.6L10 1z"/></svg>
            <svg viewBox="0 0 20 20"><path d="M10 1l2.6 5.7 6.2.6-4.7 4.2 1.4 6.1L10 14.7 4.5 17.6l1.4-6.1L1.2 7.3l6.2-.6L10 1z"/></svg>
            <svg viewBox="0 0 20 20"><path d="M10 1l2.6 5.7 6.2.6-4.7 4.2 1.4 6.1L10 14.7 4.5 17.6l1.4-6.1L1.2 7.3l6.2-.6L10 1z"/></svg>
            <svg viewBox="0 0 20 20"><path d="M10 1l2.6 5.7 6.2.6-4.7 4.2 1.4 6.1L10 14.7 4.5 17.6l1.4-6.1L1.2 7.3l6.2-.6L10 1z"/></svg>
          </div>
          <blockquote>&ldquo;We stayed in the Chamberlain Room for our anniversary. The fireplace, the biscuits, the walk to the cemetery at sunrise — it's the kind of trip you plan the next one from.&rdquo;</blockquote>
          <div class="review-who">
            <div class="review-avatar" aria-hidden="true">RM</div>
            <div><div class="name">Rebecca M.</div><div class="loc">Arlington, VA</div></div>
          </div>
        </article>

        <article class="review-card reveal">
          <div class="stars" aria-hidden="true">
            <svg viewBox="0 0 20 20"><path d="M10 1l2.6 5.7 6.2.6-4.7 4.2 1.4 6.1L10 14.7 4.5 17.6l1.4-6.1L1.2 7.3l6.2-.6L10 1z"/></svg>
            <svg viewBox="0 0 20 20"><path d="M10 1l2.6 5.7 6.2.6-4.7 4.2 1.4 6.1L10 14.7 4.5 17.6l1.4-6.1L1.2 7.3l6.2-.6L10 1z"/></svg>
            <svg viewBox="0 0 20 20"><path d="M10 1l2.6 5.7 6.2.6-4.7 4.2 1.4 6.1L10 14.7 4.5 17.6l1.4-6.1L1.2 7.3l6.2-.6L10 1z"/></svg>
            <svg viewBox="0 0 20 20"><path d="M10 1l2.6 5.7 6.2.6-4.7 4.2 1.4 6.1L10 14.7 4.5 17.6l1.4-6.1L1.2 7.3l6.2-.6L10 1z"/></svg>
            <svg viewBox="0 0 20 20"><path d="M10 1l2.6 5.7 6.2.6-4.7 4.2 1.4 6.1L10 14.7 4.5 17.6l1.4-6.1L1.2 7.3l6.2-.6L10 1z"/></svg>
          </div>
          <blockquote>&ldquo;I was in town for battlefield research and stayed a full week in the Culp Hill Room. Quiet, honest, well-kept — and the innkeepers know more history than half the tour guides.&rdquo;</blockquote>
          <div class="review-who">
            <div class="review-avatar" aria-hidden="true">DT</div>
            <div><div class="name">David T.</div><div class="loc">Richmond, VA</div></div>
          </div>
        </article>

        <article class="review-card reveal">
          <div class="stars" aria-hidden="true">
            <svg viewBox="0 0 20 20"><path d="M10 1l2.6 5.7 6.2.6-4.7 4.2 1.4 6.1L10 14.7 4.5 17.6l1.4-6.1L1.2 7.3l6.2-.6L10 1z"/></svg>
            <svg viewBox="0 0 20 20"><path d="M10 1l2.6 5.7 6.2.6-4.7 4.2 1.4 6.1L10 14.7 4.5 17.6l1.4-6.1L1.2 7.3l6.2-.6L10 1z"/></svg>
            <svg viewBox="0 0 20 20"><path d="M10 1l2.6 5.7 6.2.6-4.7 4.2 1.4 6.1L10 14.7 4.5 17.6l1.4-6.1L1.2 7.3l6.2-.6L10 1z"/></svg>
            <svg viewBox="0 0 20 20"><path d="M10 1l2.6 5.7 6.2.6-4.7 4.2 1.4 6.1L10 14.7 4.5 17.6l1.4-6.1L1.2 7.3l6.2-.6L10 1z"/></svg>
            <svg viewBox="0 0 20 20"><path d="M10 1l2.6 5.7 6.2.6-4.7 4.2 1.4 6.1L10 14.7 4.5 17.6l1.4-6.1L1.2 7.3l6.2-.6L10 1z"/></svg>
          </div>
          <blockquote>&ldquo;Booked the Garden Room on a whim after our hotel fell through. Best mistake of the trip. The courtyard at dusk with a glass of wine is worth the drive from Baltimore alone.&rdquo;</blockquote>
          <div class="review-who">
            <div class="review-avatar" aria-hidden="true">JK</div>
            <div><div class="name">Julia K.</div><div class="loc">Baltimore, MD</div></div>
          </div>
        </article>
      </div>
    </div>
  </section>

  <!-- ============ CTA BAND ============ -->
  <section class="cta-band">
    <div class="wrap cta-inner">
      <span class="eyebrow">Plan Your Stay</span>
      <h2>Baltimore Street is waiting for you.</h2>
      <p>Nine rooms fill quickly on battlefield anniversary weekends (July and November) — reserve early, or call us directly and we'll walk you through what's open.</p>
      <div class="cta-row">
        <a href="{{ home_url('/contact/') }}#book" class="btn btn-brass">Check Availability</a>
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
/* Page-specific tool: booking bar (availability demo + default dates). */
(function () {
  "use strict";
  var bookingForm = document.querySelector('.booking-bar');
  var bookingMsg = document.getElementById('bookingMsg');
  if (bookingForm && bookingMsg) {
    bookingForm.addEventListener('submit', function (e) {
      e.preventDefault();
      bookingMsg.classList.add('show');
      bookingMsg.scrollIntoView({ behavior: 'smooth', block: 'nearest' });
    });
  }
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
