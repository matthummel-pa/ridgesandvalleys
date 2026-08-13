{{-- Front page: concept home --}}

@extends('layouts.app')

@push('head')
<script type="application/ld+json">
{
  "@@context": "https://schema.org",
  "@@type": "Restaurant",
  "@@id": "{{ home_url('/') }}#restaurant",
  "name": "Field & Musket Tavern & Kitchen",
  "image": "{{ home_url('/preview.jpg/') }}",
  "url": "{{ home_url('/') }}",
  "telephone": "+1-717-555-0119",
  "email": "table@@fieldandmusket.test",
  "priceRange": "$$",
  "servesCuisine": ["Farm-to-table American", "Seasonal", "Tavern"],
  "menu": "{{ home_url('/menu/') }}",
  "hasMenu": "{{ home_url('/menu/') }}",
  "acceptsReservations": "True",
  "address": {
    "@@type": "PostalAddress",
    "streetAddress": "19 Chambersburg Street",
    "addressLocality": "Gettysburg",
    "addressRegion": "PA",
    "postalCode": "17325",
    "addressCountry": "US"
  },
  "geo": { "@@type": "GeoCoordinates", "latitude": 39.8312, "longitude": -77.2311 },
  "hasMap": "https://www.google.com/maps?q=19+Chambersburg+Street,+Gettysburg,+PA+17325",
  "openingHoursSpecification": [
    { "@@type": "OpeningHoursSpecification", "dayOfWeek": ["Tuesday", "Wednesday", "Thursday"], "opens": "16:00", "closes": "21:00" },
    { "@@type": "OpeningHoursSpecification", "dayOfWeek": "Friday", "opens": "16:00", "closes": "22:00" },
    { "@@type": "OpeningHoursSpecification", "dayOfWeek": "Saturday", "opens": "10:00", "closes": "22:00" },
    { "@@type": "OpeningHoursSpecification", "dayOfWeek": "Sunday", "opens": "10:00", "closes": "20:00" }
  ],
  "areaServed": [
    { "@@type": "City", "name": "Gettysburg" },
    { "@@type": "AdministrativeArea", "name": "Adams County, Pennsylvania" }
  ]
}
</script>
@endpush

@section('content')
<a href="#main" class="skip-link">Skip to main content</a>

<header class="site-header" id="siteHeader">
  <div class="wrap header-inner">
    <a href="{{ home_url('/') }}" class="wordmark" aria-label="Field &amp; Musket — home">
      <svg width="40" height="40" viewBox="0 0 48 48" fill="none" aria-hidden="true">
        <circle cx="24" cy="24" r="23" stroke="#dfa23f" stroke-width="1.5"/>
        <path d="M24 10c-5 4-8 9-8 15 0 4.4 3.6 8 8 8s8-3.6 8-8c0-6-3-11-8-15Z" stroke="#f4ede1" stroke-width="1.6" fill="none"/>
        <path d="M24 17v16M18 25h12" stroke="#dfa23f" stroke-width="1.4"/>
      </svg>
      <span class="wordmark-text">
        <strong>Field &amp; Musket</strong>
        <span>Tavern &amp; Kitchen</span>
      </span>
    </a>

    <nav class="primary-nav" aria-label="Primary">
      <ul>
        <li><a href="{{ home_url('/menu/') }}">Menu</a></li>
        <li><a href="{{ home_url('/story/') }}">Our Story</a></li>
        <li><a href="{{ home_url('/visit/') }}">Visit</a></li>
        <li><a href="{{ home_url('/reserve/') }}">Reservations</a></li>
      </ul>
    </nav>

    <div class="header-cta">
      <a class="header-phone" href="tel:+17175550119">
        <svg width="16" height="16" viewBox="0 0 24 24" fill="none" aria-hidden="true"><path d="M6.6 10.8c1.4 2.8 3.8 5.2 6.6 6.6l2.2-2.2c.3-.3.7-.4 1-.2 1.1.4 2.3.6 3.6.6.6 0 1 .4 1 1V20c0 .6-.4 1-1 1C10.9 21 3 13.1 3 3c0-.6.4-1 1-1h3.5c.6 0 1 .4 1 1 0 1.3.2 2.5.6 3.6.1.4 0 .8-.2 1L6.6 10.8Z" stroke="currentColor" stroke-width="1.6" stroke-linejoin="round"/></svg>
        (717) 555-0119
      </a>
      <a href="{{ home_url('/reserve/') }}" class="btn btn-primary btn-sm">Reserve a Table</a>
      <button class="hamburger" id="hamburgerBtn" aria-label="Open menu" aria-expanded="false" aria-controls="mobileNav">
        <svg id="hamburgerIcon" width="20" height="20" viewBox="0 0 24 24" fill="none" aria-hidden="true">
          <path d="M3 6h18M3 12h18M3 18h18" stroke="currentColor" stroke-width="2" stroke-linecap="round"/>
        </svg>
      </button>
    </div>
  </div>

  <nav class="mobile-nav" id="mobileNav" aria-label="Mobile">
    <a href="{{ home_url('/') }}">Home</a>
    <a href="{{ home_url('/menu/') }}">Menu</a>
    <a href="{{ home_url('/story/') }}">Our Story</a>
    <a href="{{ home_url('/visit/') }}">Visit</a>
    <a href="{{ home_url('/reserve/') }}">Reservations</a>
    <a href="tel:+17175550119">Call (717) 555-0119</a>
    <a href="{{ home_url('/reserve/') }}" class="btn btn-primary btn-block">Reserve a Table</a>
  </nav>
</header>

<main id="main">

  <!-- ============ HERO ============ -->
  <section class="hero" id="top">
    <div class="hero-media">
      <img src="https://commons.wikimedia.org/wiki/Special:FilePath/DowntownGettysburgPA.jpg?width=1600"
           alt="Historic downtown Gettysburg street near Lincoln Square, lined with brick storefronts on Chambersburg Street"
           loading="eager"
           onerror="this.style.display='none'">
    </div>
    <div class="wrap hero-content">
      <p class="eyebrow eyebrow--light">Downtown Gettysburg, PA · Chambersburg Street</p>
      <h1>Farmhouse fare, forged a stone&rsquo;s throw from Lincoln Square.</h1>
      <p>Field &amp; Musket is a chef-driven, seasonal farm-to-table tavern built on Adams County harvests, hearth-fired mains, and a dining room that feels like coming home. Open nightly for dinner, weekends for brunch.</p>
      <div class="hero-actions">
        <a href="{{ home_url('/reserve/') }}" class="btn btn-primary">Reserve a Table</a>
        <a href="{{ home_url('/menu/') }}" class="btn btn-outline">View the Menu</a>
      </div>
    </div>
    <div class="hero-scroll-cue" aria-hidden="true">Scroll to explore</div>
  </section>

  <!-- ============ TEASERS TO EVERY PAGE ============ -->
  <section class="teasers section-pad">
    <div class="wrap">
      <div class="section-head reveal">
        <p class="eyebrow">Find Your Way In</p>
        <h2>Everything at the tavern, one door at a time.</h2>
        <p>A quick tour of Field &amp; Musket — the kitchen, the roots, the block we call home on Chambersburg Street, and how to save your seat.</p>
      </div>

      <div class="teasers-grid">
        <a class="teaser-card reveal" href="{{ home_url('/menu/') }}">
          <span class="teaser-icon"><svg width="24" height="24" viewBox="0 0 24 24" fill="none" aria-hidden="true"><path d="M6 3v8a3 3 0 006 0V3M8 3v6M4 3v6M18 3c-2 1-3 4-3 7h3v11" stroke="currentColor" stroke-width="1.6" stroke-linecap="round" stroke-linejoin="round"/></svg></span>
          <h3>The Menu</h3>
          <p>Seasonal, scratch-made plates that shift weekly with what the Adams County farms send us — starters, hearth mains, tavern handhelds, and the bar list.</p>
          <span class="teaser-more">See the menu <svg width="14" height="14" viewBox="0 0 24 24" fill="none" aria-hidden="true"><path d="M5 12h14M13 6l6 6-6 6" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round"/></svg></span>
        </a>

        <a class="teaser-card reveal" href="{{ home_url('/story/') }}">
          <span class="teaser-icon"><svg width="24" height="24" viewBox="0 0 24 24" fill="none" aria-hidden="true"><path d="M12 3c-2 4-2 8 0 18M12 3c2 4 2 8 0 18M6 8l6 3 6-3" stroke="currentColor" stroke-width="1.6" stroke-linecap="round" stroke-linejoin="round"/></svg></span>
          <h3>Our Story &amp; Sourcing</h3>
          <p>A restored downtown storefront, an open hearth, and a dozen Adams County growers, orchards, and creameries who fill our walk-in every morning.</p>
          <span class="teaser-more">Meet our farms <svg width="14" height="14" viewBox="0 0 24 24" fill="none" aria-hidden="true"><path d="M5 12h14M13 6l6 6-6 6" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round"/></svg></span>
        </a>

        <a class="teaser-card reveal" href="{{ home_url('/visit/') }}">
          <span class="teaser-icon"><svg width="24" height="24" viewBox="0 0 24 24" fill="none" aria-hidden="true"><path d="M12 21s7-6.5 7-12a7 7 0 10-14 0c0 5.5 7 12 7 12Z" stroke="currentColor" stroke-width="1.6" stroke-linejoin="round"/><circle cx="12" cy="9" r="2.5" stroke="currentColor" stroke-width="1.6"/></svg></span>
          <h3>Visit Us &amp; The Area</h3>
          <p>We&rsquo;re a short walk west of Lincoln Square. Directions from US-15 and US-30, parking notes, hours, and what&rsquo;s nearby in Gettysburg and Adams County.</p>
          <span class="teaser-more">Plan your visit <svg width="14" height="14" viewBox="0 0 24 24" fill="none" aria-hidden="true"><path d="M5 12h14M13 6l6 6-6 6" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round"/></svg></span>
        </a>

        <a class="teaser-card reveal" href="{{ home_url('/reserve/') }}">
          <span class="teaser-icon"><svg width="24" height="24" viewBox="0 0 24 24" fill="none" aria-hidden="true"><rect x="3" y="5" width="18" height="16" rx="2" stroke="currentColor" stroke-width="1.6"/><path d="M3 9h18M8 3v4M16 3v4" stroke="currentColor" stroke-width="1.6" stroke-linecap="round"/></svg></span>
          <h3>Reservations</h3>
          <p>The dining room and heated patio fill fast on summer weekends. Request a table for up to eight and we&rsquo;ll confirm by phone or text within a few hours.</p>
          <span class="teaser-more">Reserve now <svg width="14" height="14" viewBox="0 0 24 24" fill="none" aria-hidden="true"><path d="M5 12h14M13 6l6 6-6 6" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round"/></svg></span>
        </a>

        <a class="teaser-card reveal" href="{{ home_url('/reserve/') }}#private">
          <span class="teaser-icon"><svg width="24" height="24" viewBox="0 0 24 24" fill="none" aria-hidden="true"><path d="M4 20v-1a4 4 0 014-4h2M20 20v-1a4 4 0 00-4-4h-1" stroke="currentColor" stroke-width="1.6" stroke-linecap="round"/><circle cx="9" cy="8" r="3" stroke="currentColor" stroke-width="1.6"/><circle cx="16" cy="9" r="2.5" stroke="currentColor" stroke-width="1.6"/></svg></span>
          <h3>Private &amp; Group Dining</h3>
          <p>Rehearsal dinners, reunion weekends, and small buyouts. Tell us your group size and date and we&rsquo;ll build a hearth menu around the harvest.</p>
          <span class="teaser-more">Plan an event <svg width="14" height="14" viewBox="0 0 24 24" fill="none" aria-hidden="true"><path d="M5 12h14M13 6l6 6-6 6" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round"/></svg></span>
        </a>

        <a class="teaser-card reveal" href="{{ home_url('/menu/') }}#drinks">
          <span class="teaser-icon"><svg width="24" height="24" viewBox="0 0 24 24" fill="none" aria-hidden="true"><path d="M6 4h12l-5 8v6M8 18h8M13 12l5-8" stroke="currentColor" stroke-width="1.6" stroke-linecap="round" stroke-linejoin="round"/></svg></span>
          <h3>The Bar &amp; Cellar</h3>
          <p>Local rye Old Fashioneds, Cumberland Valley cider flights, and a rotating list of Adams County and South Central PA craft drafts.</p>
          <span class="teaser-more">See the bar list <svg width="14" height="14" viewBox="0 0 24 24" fill="none" aria-hidden="true"><path d="M5 12h14M13 6l6 6-6 6" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round"/></svg></span>
        </a>
      </div>
    </div>
  </section>

  <!-- ============ STORY TEASER ============ -->
  <section class="story section-pad">
    <div class="wrap story-grid">
      <div class="story-copy reveal">
        <p class="eyebrow">Our Roots</p>
        <h2>A table set by Adams County farmers.</h2>
        <p>We opened our doors in a restored 1863 storefront on Chambersburg Street with one rule: nothing on the plate travels farther than the orchard, the creamery, or the creek it came from. Our menu changes with the harvest &mdash; because the best meal in Gettysburg starts in the dirt, not the walk-in.</p>
        <p>Every morning our kitchen team drives out past the battlefield to the Adams County growers who&rsquo;ve worked this ground for generations. What comes back becomes that night&rsquo;s specials &mdash; Gettysburg on a plate, cooked over an open hearth.</p>
        <div class="story-stats">
          <div class="story-stat"><strong>12+</strong><span>Local Farm Partners</span></div>
          <div class="story-stat"><strong>1863</strong><span>Building Restored</span></div>
          <div class="story-stat"><strong>7</strong><span>Nights a Week Open</span></div>
        </div>
        <div class="hero-actions" style="margin-top:2rem;">
          <a href="{{ home_url('/story/') }}" class="btn btn-ghost-dark btn-sm">Read our story &amp; sourcing</a>
        </div>
      </div>
      <div class="story-panel reveal" role="img" aria-label="Illustrated wheat-and-musket emblem representing Field &amp; Musket's farm-to-table roots">
        <svg viewBox="0 0 200 200" fill="none" aria-hidden="true">
          <path d="M100 20c-3 30-3 60 0 90m0-90c3 30 3 60 0 90" stroke="currentColor" stroke-width="2"/>
          <g stroke="currentColor" stroke-width="2">
            <path d="M100 35 88 45M100 35 112 45M100 55 86 66M100 55 114 66M100 75 84 87M100 75 116 87M100 95 86 106M100 95 114 106"/>
          </g>
          <circle cx="100" cy="150" r="34" stroke="currentColor" stroke-width="2"/>
          <path d="M100 128v44M84 150h32" stroke="currentColor" stroke-width="2"/>
        </svg>
        <p class="panel-caption">Hearth-forward &middot; farm-forward &middot; Gettysburg-forward</p>
      </div>
    </div>
  </section>

  <!-- ============ SPECIALS ============ -->
  <section class="specials section-pad">
    <div class="wrap">
      <div class="section-head reveal">
        <p class="eyebrow">This Week</p>
        <h2>Chef&rsquo;s specials &amp; seasonal highlights.</h2>
        <p>Small-batch, limited-run dishes built around what came off the truck this week. The full, weekly-updated menu lives on its own page.</p>
      </div>

      <div class="specials-grid">
        <article class="special-card reveal">
          <div class="special-media tone-a">
            <span class="special-badge">Farm Feature</span>
            <svg viewBox="0 0 64 64" fill="none" aria-hidden="true"><path d="M32 8c-8 8-13 18-13 27a13 13 0 0026 0c0-9-5-19-13-27Z" stroke="currentColor" stroke-width="2"/><path d="M32 35v21" stroke="currentColor" stroke-width="2"/></svg>
          </div>
          <div class="special-body">
            <h3>Round Barn Squash Agnolotti</h3>
            <p>House-rolled pasta stuffed with roasted Round Barn Farm butternut squash, brown butter, sage, and toasted pepitas &mdash; finished with a whisper of nutmeg and aged parmesan. Available while the squash lasts.</p>
            <div class="special-foot">
              <span class="special-price">$26</span>
              <span class="special-tag">Vegetarian &middot; Limited</span>
            </div>
          </div>
        </article>

        <article class="special-card reveal">
          <div class="special-media tone-b">
            <span class="special-badge">Hearth Special</span>
            <svg viewBox="0 0 64 64" fill="none" aria-hidden="true"><path d="M12 44h40M18 44V22a4 4 0 014-4h20a4 4 0 014 4v22" stroke="currentColor" stroke-width="2"/><path d="M24 18V10M40 18V10" stroke="currentColor" stroke-width="2" stroke-linecap="round"/></svg>
          </div>
          <div class="special-body">
            <h3>Whole Roasted Cauliflower</h3>
            <p>Slow-roasted over the open hearth until the edges char and caramelize, glazed with fermented chili honey, and finished with pickled shallot and herb yogurt. Enough to share, if you&rsquo;re feeling generous.</p>
            <div class="special-foot">
              <span class="special-price">$21</span>
              <span class="special-tag">Vegan-adaptable</span>
            </div>
          </div>
        </article>

        <article class="special-card reveal">
          <div class="special-media tone-c">
            <span class="special-badge">Cellar Pick</span>
            <svg viewBox="0 0 64 64" fill="none" aria-hidden="true"><path d="M22 8h20l-4 20a10 10 0 01-12 0L22 8Z" stroke="currentColor" stroke-width="2" stroke-linejoin="round"/><path d="M32 34v18M24 52h16" stroke="currentColor" stroke-width="2" stroke-linecap="round"/></svg>
          </div>
          <div class="special-body">
            <h3>Smoked Old Fashioned Flight</h3>
            <p>Three riffs on our house Old Fashioned &mdash; classic rye, applewood-smoked, and black walnut bitters &mdash; poured tableside beneath a glass cloche. A guest favorite since we opened.</p>
            <div class="special-foot">
              <span class="special-price">$24</span>
              <span class="special-tag">21+ &middot; Serves One</span>
            </div>
          </div>
        </article>
      </div>
      <div class="hero-actions reveal" style="margin-top:2.5rem;">
        <a href="{{ home_url('/menu/') }}" class="btn btn-primary">See the full menu</a>
      </div>
    </div>
  </section>

  <!-- ============ GALLERY ============ -->
  <section class="gallery section-pad">
    <div class="wrap">
      <div class="section-head reveal">
        <p class="eyebrow">A Look Inside</p>
        <h2>The dining room, the hearth, the block outside.</h2>
      </div>

      <div class="gallery-grid">
        <div class="gtile big photo reveal">
          <img src="https://commons.wikimedia.org/wiki/Special:FilePath/Two_Buildings_in_the_Gettysburg_Historic_District.jpg?width=1600"
               alt="Historic brick storefronts in Gettysburg's historic district, similar to the tavern's Chambersburg Street building"
               loading="lazy"
               onerror="this.style.display='none'">
          <span class="gtile-label">The Building &middot; Est. 1863</span>
        </div>

        <div class="gtile tone-1 reveal">
          <svg class="gtile-icon" width="26" height="26" viewBox="0 0 24 24" fill="none" aria-hidden="true"><path d="M4 20h16M6 20V10l6-6 6 6v10" stroke="currentColor" stroke-width="1.6"/></svg>
          <span class="gtile-label">The Dining Room</span>
        </div>

        <div class="gtile tone-2 reveal">
          <svg class="gtile-icon" width="26" height="26" viewBox="0 0 24 24" fill="none" aria-hidden="true"><path d="M12 3c-3 4-5 8-5 11a5 5 0 0010 0c0-3-2-7-5-11Z" stroke="currentColor" stroke-width="1.6"/></svg>
          <span class="gtile-label">The Hearth Kitchen</span>
        </div>

        <div class="gtile photo reveal">
          <img src="https://commons.wikimedia.org/wiki/Special:FilePath/Gettysburg,_Wentz_farm_bildings.jpg?width=1600"
               alt="Historic Adams County farm buildings, representative of the local farms Field &amp; Musket sources from"
               loading="lazy"
               onerror="this.style.display='none'">
          <span class="gtile-label">Our Farm Partners</span>
        </div>

        <div class="gtile tone-3 reveal">
          <svg class="gtile-icon" width="26" height="26" viewBox="0 0 24 24" fill="none" aria-hidden="true"><path d="M5 21V9l7-6 7 6v12M9 21v-6h6v6" stroke="currentColor" stroke-width="1.6"/></svg>
          <span class="gtile-label">The Bar</span>
        </div>

        <div class="gtile tone-4 reveal">
          <svg class="gtile-icon" width="26" height="26" viewBox="0 0 24 24" fill="none" aria-hidden="true"><path d="M4 19l5-9 4 6 3-4 4 7H4Z" stroke="currentColor" stroke-width="1.6" stroke-linejoin="round"/></svg>
          <span class="gtile-label">The Patio</span>
        </div>
      </div>
    </div>
  </section>

  <!-- ============ REVIEWS ============ -->
  <section class="reviews section-pad">
    <div class="wrap">
      <div class="section-head center reveal">
        <p class="eyebrow">What Guests Are Saying</p>
        <h2>Word around Lincoln Square.</h2>
        <p>Illustrative guest notes shown as sample content for this concept.</p>
      </div>

      <div class="reviews-grid">
        <article class="review-card reveal">
          <div class="stars" aria-label="5 out of 5 stars">
            <svg width="18" height="18" viewBox="0 0 20 20" fill="currentColor" aria-hidden="true"><path d="M10 1l2.6 5.9 6.4.6-4.8 4.3 1.4 6.3L10 15l-5.6 3.1 1.4-6.3-4.8-4.3 6.4-.6L10 1z"/></svg>
            <svg width="18" height="18" viewBox="0 0 20 20" fill="currentColor" aria-hidden="true"><path d="M10 1l2.6 5.9 6.4.6-4.8 4.3 1.4 6.3L10 15l-5.6 3.1 1.4-6.3-4.8-4.3 6.4-.6L10 1z"/></svg>
            <svg width="18" height="18" viewBox="0 0 20 20" fill="currentColor" aria-hidden="true"><path d="M10 1l2.6 5.9 6.4.6-4.8 4.3 1.4 6.3L10 15l-5.6 3.1 1.4-6.3-4.8-4.3 6.4-.6L10 1z"/></svg>
            <svg width="18" height="18" viewBox="0 0 20 20" fill="currentColor" aria-hidden="true"><path d="M10 1l2.6 5.9 6.4.6-4.8 4.3 1.4 6.3L10 15l-5.6 3.1 1.4-6.3-4.8-4.3 6.4-.6L10 1z"/></svg>
            <svg width="18" height="18" viewBox="0 0 20 20" fill="currentColor" aria-hidden="true"><path d="M10 1l2.6 5.9 6.4.6-4.8 4.3 1.4 6.3L10 15l-5.6 3.1 1.4-6.3-4.8-4.3 6.4-.6L10 1z"/></svg>
          </div>
          <p class="review-quote">&ldquo;The half chicken alone is worth the trip from Baltimore. You can taste that everything is actually local &mdash; not just a menu line.&rdquo;</p>
          <div class="review-author">
            <div class="review-avatar">RM</div>
            <div><strong>Rachel M.</strong><span>Baltimore, MD</span></div>
          </div>
        </article>

        <article class="review-card reveal">
          <div class="stars" aria-label="5 out of 5 stars">
            <svg width="18" height="18" viewBox="0 0 20 20" fill="currentColor" aria-hidden="true"><path d="M10 1l2.6 5.9 6.4.6-4.8 4.3 1.4 6.3L10 15l-5.6 3.1 1.4-6.3-4.8-4.3 6.4-.6L10 1z"/></svg>
            <svg width="18" height="18" viewBox="0 0 20 20" fill="currentColor" aria-hidden="true"><path d="M10 1l2.6 5.9 6.4.6-4.8 4.3 1.4 6.3L10 15l-5.6 3.1 1.4-6.3-4.8-4.3 6.4-.6L10 1z"/></svg>
            <svg width="18" height="18" viewBox="0 0 20 20" fill="currentColor" aria-hidden="true"><path d="M10 1l2.6 5.9 6.4.6-4.8 4.3 1.4 6.3L10 15l-5.6 3.1 1.4-6.3-4.8-4.3 6.4-.6L10 1z"/></svg>
            <svg width="18" height="18" viewBox="0 0 20 20" fill="currentColor" aria-hidden="true"><path d="M10 1l2.6 5.9 6.4.6-4.8 4.3 1.4 6.3L10 15l-5.6 3.1 1.4-6.3-4.8-4.3 6.4-.6L10 1z"/></svg>
            <svg width="18" height="18" viewBox="0 0 20 20" fill="currentColor" aria-hidden="true"><path d="M10 1l2.6 5.9 6.4.6-4.8 4.3 1.4 6.3L10 15l-5.6 3.1 1.4-6.3-4.8-4.3 6.4-.6L10 1z"/></svg>
          </div>
          <p class="review-quote">&ldquo;We&rsquo;ve lived here twelve years and Field &amp; Musket is the first restaurant that actually feels like Gettysburg &mdash; not a tourist trap.&rdquo;</p>
          <div class="review-author">
            <div class="review-avatar">DK</div>
            <div><strong>Dave K.</strong><span>Gettysburg, PA</span></div>
          </div>
        </article>

        <article class="review-card reveal">
          <div class="stars" aria-label="5 out of 5 stars">
            <svg width="18" height="18" viewBox="0 0 20 20" fill="currentColor" aria-hidden="true"><path d="M10 1l2.6 5.9 6.4.6-4.8 4.3 1.4 6.3L10 15l-5.6 3.1 1.4-6.3-4.8-4.3 6.4-.6L10 1z"/></svg>
            <svg width="18" height="18" viewBox="0 0 20 20" fill="currentColor" aria-hidden="true"><path d="M10 1l2.6 5.9 6.4.6-4.8 4.3 1.4 6.3L10 15l-5.6 3.1 1.4-6.3-4.8-4.3 6.4-.6L10 1z"/></svg>
            <svg width="18" height="18" viewBox="0 0 20 20" fill="currentColor" aria-hidden="true"><path d="M10 1l2.6 5.9 6.4.6-4.8 4.3 1.4 6.3L10 15l-5.6 3.1 1.4-6.3-4.8-4.3 6.4-.6L10 1z"/></svg>
            <svg width="18" height="18" viewBox="0 0 20 20" fill="currentColor" aria-hidden="true"><path d="M10 1l2.6 5.9 6.4.6-4.8 4.3 1.4 6.3L10 15l-5.6 3.1 1.4-6.3-4.8-4.3 6.4-.6L10 1z"/></svg>
            <svg width="18" height="18" viewBox="0 0 20 20" fill="currentColor" aria-hidden="true"><path d="M10 1l2.6 5.9 6.4.6-4.8 4.3 1.4 6.3L10 15l-5.6 3.1 1.4-6.3-4.8-4.3 6.4-.6L10 1z"/></svg>
          </div>
          <p class="review-quote">&ldquo;Got the squash agnolotti as a special and I&rsquo;m still thinking about it a month later. Ask what&rsquo;s on the hearth &mdash; just trust them.&rdquo;</p>
          <div class="review-author">
            <div class="review-avatar">SP</div>
            <div><strong>Sarah P.</strong><span>Hanover, PA</span></div>
          </div>
        </article>
      </div>
    </div>
  </section>

  <!-- ============ CTA BAND ============ -->
  <section class="cta-band section-pad">
    <div class="wrap">
      <p class="eyebrow">Reservations</p>
      <h2>Save your table on Chambersburg Street.</h2>
      <p>We hold a few walk-in seats each night, but the dining room and heated patio fill fast on Gettysburg weekends. Request your table and we&rsquo;ll confirm within a few hours.</p>
      <div class="hero-actions">
        <a href="{{ home_url('/reserve/') }}" class="btn btn-primary">Reserve a Table</a>
        <a href="{{ home_url('/visit/') }}" class="btn btn-outline">Directions &amp; Hours</a>
      </div>
    </div>
  </section>

</main>

<footer class="site-footer">
  <div class="wrap">
    <div class="footer-grid">
      <div class="footer-brand">
        <a href="{{ home_url('/') }}" class="wordmark" aria-label="Field &amp; Musket — home">
          <svg width="34" height="34" viewBox="0 0 48 48" fill="none" aria-hidden="true">
            <circle cx="24" cy="24" r="23" stroke="#dfa23f" stroke-width="1.5"/>
            <path d="M24 10c-5 4-8 9-8 15 0 4.4 3.6 8 8 8s8-3.6 8-8c0-6-3-11-8-15Z" stroke="#f4ede1" stroke-width="1.6" fill="none"/>
          </svg>
          <span class="wordmark-text"><strong>Field &amp; Musket</strong><span>Tavern &amp; Kitchen</span></span>
        </a>
        <p>A seasonal farm-to-table tavern in the heart of downtown Gettysburg, serving Adams County harvests over an open hearth from our restored storefront on Chambersburg Street.</p>
        <div class="footer-social">
          <a href="#" aria-label="Field &amp; Musket on Instagram">
            <svg width="17" height="17" viewBox="0 0 24 24" fill="none" aria-hidden="true"><rect x="3" y="3" width="18" height="18" rx="5" stroke="currentColor" stroke-width="1.6"/><circle cx="12" cy="12" r="4" stroke="currentColor" stroke-width="1.6"/><circle cx="17.5" cy="6.5" r="1" fill="currentColor"/></svg>
          </a>
          <a href="#" aria-label="Field &amp; Musket on Facebook">
            <svg width="17" height="17" viewBox="0 0 24 24" fill="none" aria-hidden="true"><path d="M14 9h3V6h-3c-2.2 0-4 1.8-4 4v2H8v3h2v6h3v-6h3l1-3h-4v-2c0-.6.4-1 1-1Z" stroke="currentColor" stroke-width="1.4" stroke-linejoin="round"/></svg>
          </a>
          <a href="#" aria-label="Field &amp; Musket on TripAdvisor">
            <svg width="17" height="17" viewBox="0 0 24 24" fill="none" aria-hidden="true"><circle cx="8" cy="13" r="4" stroke="currentColor" stroke-width="1.4"/><circle cx="16" cy="13" r="4" stroke="currentColor" stroke-width="1.4"/><path d="M2 9h6M16 9h6M8 9c1.5-2 4.5-2 6 0" stroke="currentColor" stroke-width="1.4" stroke-linecap="round"/></svg>
          </a>
        </div>
      </div>

      <div class="footer-col">
        <h4>Visit</h4>
        <p>Field &amp; Musket Tavern &amp; Kitchen<br>19 Chambersburg Street<br>Gettysburg, PA 17325</p>
        <p style="margin-top:0.75rem;"><a href="tel:+17175550119">(717) 555-0119</a><br><a href="mailto:table@@fieldandmusket.test">table@@fieldandmusket.test</a></p>
        <p style="margin-top:0.75rem;">Serving Gettysburg &amp; Adams County — Biglerville, New Oxford, Littlestown, McSherrystown, Fairfield &amp; Cashtown.</p>
      </div>

      <div class="footer-col">
        <h4>Hours</h4>
        <p>Mon: Closed<br>Tue&ndash;Thu: 4&ndash;9pm<br>Fri: 4&ndash;10pm<br>Sat: 10am&ndash;10pm<br>Sun: 10am&ndash;8pm</p>
      </div>

      <div class="footer-col">
        <h4>Explore</h4>
        <ul>
          <li><a href="{{ home_url('/') }}">Home</a></li>
          <li><a href="{{ home_url('/menu/') }}">Menu</a></li>
          <li><a href="{{ home_url('/story/') }}">Our Story &amp; Sourcing</a></li>
          <li><a href="{{ home_url('/visit/') }}">Visit / The Area</a></li>
          <li><a href="{{ home_url('/reserve/') }}">Reservations</a></li>
        </ul>
      </div>
    </div>

    <div class="footer-bottom">
      <span>&copy; <span data-year>2026</span> Field &amp; Musket Tavern &amp; Kitchen · 19 Chambersburg Street, Gettysburg, PA 17325.</span>
      <span>Design concept by Ridges &amp; Valleys Studio &mdash; a fictional business shown for demonstration.</span>
    </div>
  </div>
</footer>

<a href="#" class="concept-badge" onclick="return false;" aria-label="This is a design concept by Ridges and Valleys Studio">
  <svg width="14" height="14" viewBox="0 0 24 24" fill="none" aria-hidden="true"><path d="M12 2 2 7l10 5 10-5-10-5Z" stroke="currentColor" stroke-width="1.4" stroke-linejoin="round"/><path d="M2 12l10 5 10-5M2 17l10 5 10-5" stroke="currentColor" stroke-width="1.4" stroke-linejoin="round"/></svg>
  <span class="long">Concept &middot; Ridges &amp; Valleys Studio</span>
</a>
@endsection
