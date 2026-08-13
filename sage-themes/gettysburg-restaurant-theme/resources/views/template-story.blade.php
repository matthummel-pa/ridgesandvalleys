{{--
  Template Name: Our Story & Sourcing
--}}

@extends('layouts.app')

@push('head')
<script type="application/ld+json">
{
  "@@context": "https://schema.org",
  "@@type": "Restaurant",
  "@@id": "{{ home_url('/') }}#restaurant",
  "name": "Field & Musket Tavern & Kitchen",
  "image": "{{ home_url('/preview.jpg/') }}",
  "url": "{{ home_url('/story/') }}",
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
  "geo": { "@@type": "GeoCoordinates", "latitude": 39.8311, "longitude": -77.2312 },
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
<script type="application/ld+json">
{
  "@@context": "https://schema.org",
  "@@type": "BreadcrumbList",
  "itemListElement": [
    { "@@type": "ListItem", "position": 1, "name": "Home", "item": "{{ home_url('/') }}" },
    { "@@type": "ListItem", "position": 2, "name": "Our Story & Sourcing", "item": "{{ home_url('/story/') }}" }
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
        <li><a href="{{ home_url('/story/') }}" class="is-active" aria-current="page">Our Story</a></li>
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
    <a href="{{ home_url('/story/') }}" class="is-active" aria-current="page">Our Story</a>
    <a href="{{ home_url('/visit/') }}">Visit</a>
    <a href="{{ home_url('/reserve/') }}">Reservations</a>
    <a href="tel:+17175550119">Call (717) 555-0119</a>
    <a href="{{ home_url('/reserve/') }}" class="btn btn-primary btn-block">Reserve a Table</a>
  </nav>
</header>

<nav class="breadcrumb" aria-label="Breadcrumb">
  <div class="wrap">
    <ol>
      <li><a href="{{ home_url('/') }}">Home</a></li>
      <li class="sep" aria-hidden="true">/</li>
      <li aria-current="page">Our Story &amp; Sourcing</li>
    </ol>
  </div>
</nav>

<main id="main">

  <section class="page-hero">
    <div class="wrap">
      <p class="eyebrow">Our Story &amp; Sourcing</p>
      <h1>Gettysburg on a plate, cooked over an open hearth.</h1>
      <p class="lede">We opened in a restored 1863 storefront on Chambersburg Street with one rule: nothing on the plate travels farther than the orchard, the creamery, or the creek it came from.</p>
    </div>
  </section>

  <!-- Roots story -->
  <section class="story section-pad">
    <div class="wrap story-grid">
      <div class="story-copy reveal">
        <p class="eyebrow">Our Roots</p>
        <h2>A table set by Adams County farmers.</h2>
        <p>Field &amp; Musket started with a simple frustration: you could stand in the middle of some of Pennsylvania&rsquo;s richest farm country &mdash; orchards, dairies, and truck farms in every direction &mdash; and still struggle to find a Gettysburg dinner that actually tasted like it. So we took a long-empty storefront a few doors west of Lincoln Square, rebuilt the hearth at its heart, and started cooking what the county grows.</p>
        <p>Our menu changes with the harvest, not the calendar. In high summer that means heirloom tomatoes, stone fruit, and sweet corn; by autumn it&rsquo;s squash, cider, and braises. Every morning our kitchen team drives out past the battlefield to the growers who&rsquo;ve worked this ground for generations, and what comes back becomes that night&rsquo;s specials.</p>
        <div class="story-stats">
          <div class="story-stat"><strong>12+</strong><span>Local Farm Partners</span></div>
          <div class="story-stat"><strong>1863</strong><span>Building Restored</span></div>
          <div class="story-stat"><strong>~25mi</strong><span>Average Ingredient Radius</span></div>
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

  <!-- Sourcing prose + suppliers -->
  <section class="section-pad" style="background:var(--bone-100);">
    <div class="wrap">
      <div class="section-head reveal">
        <p class="eyebrow">Where It Comes From</p>
        <h2>Our sourcing, farm by farm.</h2>
        <p>These grower profiles are illustrative examples written for this concept &mdash; a picture of how a Gettysburg farm-to-table kitchen sources across Adams County.</p>
      </div>

      <div class="supplier-grid reveal">
        <div class="supplier-card">
          <span class="supplier-tag">Produce &middot; Biglerville</span>
          <h3>Round Barn Farm Market</h3>
          <p>Heirloom tomatoes, squash, sweet corn, and greens from the fruit-belt country north of town toward Biglerville. Our squash agnolotti special starts here.</p>
        </div>
        <div class="supplier-card">
          <span class="supplier-tag">Orchard &amp; Cider &middot; Adams County</span>
          <h3>Ridge Orchard &amp; Press</h3>
          <p>Peaches and apples in season, plus the pressed cider behind our braised pork shank and the Cumberland Valley cider flight on the bar list.</p>
        </div>
        <div class="supplier-card">
          <span class="supplier-tag">Pasture &amp; Meat &middot; Fairfield</span>
          <h3>Marsh Creek Farm</h3>
          <p>Pastured pork and free-range chicken raised west toward Fairfield. Their shallots become the jam alongside our roasted bone marrow.</p>
        </div>
        <div class="supplier-card">
          <span class="supplier-tag">Creamery &middot; New Oxford</span>
          <h3>Conewago Creamery</h3>
          <p>Whipped ricotta, aged cheddar, and cultured butter from the dairy country out toward New Oxford and McSherrystown.</p>
        </div>
        <div class="supplier-card">
          <span class="supplier-tag">Fishery &middot; Marsh Creek</span>
          <h3>Local Creek Trout</h3>
          <p>Cold-water trout smoked in-house for our pâté and roasted whole over the hearth &mdash; a nod to the creeks that thread through the county.</p>
        </div>
        <div class="supplier-card">
          <span class="supplier-tag">Grain &amp; Spirits &middot; South Central PA</span>
          <h3>Regional Rye &amp; Grain</h3>
          <p>Local rye for the Musket Old Fashioned, milled grain for the cornbread and biscuits, and rotating drafts from Adams County breweries.</p>
        </div>
      </div>
    </div>
  </section>

  <!-- The building / hearth prose -->
  <section class="section-pad" style="background:var(--color-surface);">
    <div class="wrap info-two">
      <div class="prose reveal">
        <h3>The building: a restored 1863 storefront</h3>
        <p>Our home on Chambersburg Street sits inside the Gettysburg National Register Historic District, in a brick storefront that dates to the year of the battle. We kept the bones &mdash; the tall front windows, the original masonry, the worn plank floors &mdash; and built a modern open kitchen and wood-fired hearth into the back, so the dining room fills with the smell of the fire the moment you walk in from the sidewalk.</p>
        <h3>The hearth: how we cook</h3>
        <p>Nearly everything crosses the hearth. Whole chickens and trout roast against the coals, vegetables char on the grate, and the cast-iron cornbread bakes in the residual heat. Wood-fire cooking is unforgiving and seasonal by nature, which suits an ever-changing farm menu: we let the ingredient lead and the fire finish it.</p>
        <h3>Why farm-to-table, honestly</h3>
        <p>&ldquo;Farm-to-table&rdquo; gets used loosely, so here&rsquo;s what it means for us: a short ingredient radius (most of what you eat is grown or raised within about 25 miles of Lincoln Square), menus that move with the season, and relationships with named growers rather than a distributor catalog. It costs more and takes more planning &mdash; and it&rsquo;s the whole reason the food tastes like Gettysburg.</p>
        <p>Want to taste the current harvest? <a href="{{ home_url('/menu/') }}">See this week&rsquo;s menu</a> or <a href="{{ home_url('/reserve/') }}">reserve a table</a>.</p>
      </div>

      <aside class="side-card reveal">
        <h3>Visit the Tavern</h3>
        <dl>
          <div><dt>Address</dt><dd>19 Chambersburg Street<br>Gettysburg, PA 17325</dd></div>
          <div><dt>Phone</dt><dd><a href="tel:+17175550119">(717) 555-0119</a></dd></div>
          <div><dt>Email</dt><dd><a href="mailto:table@@fieldandmusket.test">table@@fieldandmusket.test</a></dd></div>
          <div><dt>Hours</dt><dd>Mon: Closed · Tue&ndash;Thu 4&ndash;9pm · Fri 4&ndash;10pm · Sat 10am&ndash;10pm · Sun 10am&ndash;8pm</dd></div>
        </dl>
        <div class="side-actions">
          <a href="{{ home_url('/reserve/') }}" class="btn btn-primary btn-sm btn-block">Reserve a Table</a>
          <a href="{{ home_url('/visit/') }}" class="btn btn-ghost-dark btn-sm btn-block">Directions &amp; Parking</a>
        </div>
      </aside>
    </div>
  </section>

  <!-- CTA band -->
  <section class="cta-band section-pad">
    <div class="wrap">
      <p class="eyebrow">Come Taste the Harvest</p>
      <h2>The best meal in Gettysburg starts in the dirt.</h2>
      <p>Join us on Chambersburg Street for a dinner built around what our Adams County growers picked this week.</p>
      <div class="hero-actions">
        <a href="{{ home_url('/reserve/') }}" class="btn btn-primary">Reserve a Table</a>
        <a href="{{ home_url('/menu/') }}" class="btn btn-outline">View the Menu</a>
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
          <a href="#" aria-label="Field &amp; Musket on Instagram"><svg width="17" height="17" viewBox="0 0 24 24" fill="none" aria-hidden="true"><rect x="3" y="3" width="18" height="18" rx="5" stroke="currentColor" stroke-width="1.6"/><circle cx="12" cy="12" r="4" stroke="currentColor" stroke-width="1.6"/><circle cx="17.5" cy="6.5" r="1" fill="currentColor"/></svg></a>
          <a href="#" aria-label="Field &amp; Musket on Facebook"><svg width="17" height="17" viewBox="0 0 24 24" fill="none" aria-hidden="true"><path d="M14 9h3V6h-3c-2.2 0-4 1.8-4 4v2H8v3h2v6h3v-6h3l1-3h-4v-2c0-.6.4-1 1-1Z" stroke="currentColor" stroke-width="1.4" stroke-linejoin="round"/></svg></a>
          <a href="#" aria-label="Field &amp; Musket on TripAdvisor"><svg width="17" height="17" viewBox="0 0 24 24" fill="none" aria-hidden="true"><circle cx="8" cy="13" r="4" stroke="currentColor" stroke-width="1.4"/><circle cx="16" cy="13" r="4" stroke="currentColor" stroke-width="1.4"/><path d="M2 9h6M16 9h6M8 9c1.5-2 4.5-2 6 0" stroke="currentColor" stroke-width="1.4" stroke-linecap="round"/></svg></a>
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
