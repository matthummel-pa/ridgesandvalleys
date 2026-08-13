{{--
  Template Name: Menu
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
  "url": "{{ home_url('/menu/') }}",
  "telephone": "+1-717-555-0119",
  "email": "table@@fieldandmusket.test",
  "priceRange": "$$",
  "servesCuisine": ["Farm-to-table American", "Seasonal", "Tavern"],
  "menu": "{{ home_url('/menu/') }}",
  "hasMenu": {
    "@@type": "Menu",
    "name": "Field & Musket Seasonal Menu",
    "url": "{{ home_url('/menu/') }}",
    "hasMenuSection": [
      { "@@type": "MenuSection", "name": "Starters" },
      { "@@type": "MenuSection", "name": "From the Hearth" },
      { "@@type": "MenuSection", "name": "Handhelds" },
      { "@@type": "MenuSection", "name": "Sides" },
      { "@@type": "MenuSection", "name": "Desserts" },
      { "@@type": "MenuSection", "name": "Bar & Cellar" }
    ]
  },
  "acceptsReservations": "True",
  "address": {
    "@@type": "PostalAddress",
    "streetAddress": "19 Chambersburg Street",
    "addressLocality": "Gettysburg",
    "addressRegion": "PA",
    "postalCode": "17325",
    "addressCountry": "US"
  },
  "geo": { "@@type": "GeoCoordinates", "latitude": 39.8313, "longitude": -77.2309 },
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
    { "@@type": "ListItem", "position": 2, "name": "Menu", "item": "{{ home_url('/menu/') }}" }
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
        <li><a href="{{ home_url('/menu/') }}" class="is-active" aria-current="page">Menu</a></li>
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
    <a href="{{ home_url('/menu/') }}" class="is-active" aria-current="page">Menu</a>
    <a href="{{ home_url('/story/') }}">Our Story</a>
    <a href="{{ home_url('/visit/') }}">Visit</a>
    <a href="{{ home_url('/reserve/') }}">Reservations</a>
    <a href="tel:+17175550119">Call (717) 555-0119</a>
    <a href="{{ home_url('/reserve/') }}" class="btn btn-primary btn-block">Reserve a Table</a>
  </nav>
</header>

<!-- Breadcrumb -->
<nav class="breadcrumb" aria-label="Breadcrumb">
  <div class="wrap">
    <ol>
      <li><a href="{{ home_url('/') }}">Home</a></li>
      <li class="sep" aria-hidden="true">/</li>
      <li aria-current="page">Menu</li>
    </ol>
  </div>
</nav>

<main id="main">

  <!-- Page hero -->
  <section class="page-hero">
    <div class="wrap">
      <p class="eyebrow">The Menu · Updated Weekly</p>
      <h1>Seasonal &amp; scratch-made, every plate.</h1>
      <p class="lede">Served nightly from 4pm at 19 Chambersburg Street in downtown Gettysburg. The menu shifts weekly with what the Adams County farms send us &mdash; consider this your July edition.</p>
      <div class="chip-row">
        <span class="chip">GF = gluten-free adaptable</span>
        <span class="chip">V = vegetarian</span>
        <span class="chip">Ask about tonight&rsquo;s hearth special</span>
      </div>
    </div>
  </section>

  <!-- Menu (full) -->
  <section class="menu section-pad" id="menu">
    <div class="wrap">
      <div class="menu-categories">

        <div class="menu-cat reveal" id="starters">
          <div class="menu-cat-head"><h3>Starters</h3><span>To Share</span></div>
          <div class="menu-item">
            <div><div class="menu-item-name">Charred Peach &amp; Burrata</div><div class="menu-item-desc">Musser Farms peaches, basil oil, black walnut crumble, aged balsamic. (V, GF)</div></div>
            <div class="menu-item-price">$15</div>
          </div>
          <div class="menu-item">
            <div><div class="menu-item-name">Cast-Iron Cornbread</div><div class="menu-item-desc">Buttermilk cornbread, whipped honey butter, Maryland sea salt.</div></div>
            <div class="menu-item-price">$9</div>
          </div>
          <div class="menu-item">
            <div><div class="menu-item-name">Creek Trout Pâté</div><div class="menu-item-desc">Smoked local trout, crème fraîche, pickled fennel, griddled sourdough.</div></div>
            <div class="menu-item-price">$14</div>
          </div>
          <div class="menu-item">
            <div><div class="menu-item-name">Roasted Bone Marrow</div><div class="menu-item-desc">Herb gremolata, grilled baguette, Marsh Creek Farm shallot jam.</div></div>
            <div class="menu-item-price">$16</div>
          </div>
          <div class="menu-item">
            <div><div class="menu-item-name">Heirloom Tomato Salad</div><div class="menu-item-desc">Adams County tomatoes, whipped ricotta, torn basil, aged sherry vinegar. (V, GF)</div></div>
            <div class="menu-item-price">$13</div>
          </div>
        </div>

        <div class="menu-cat reveal" id="hearth">
          <div class="menu-cat-head"><h3>From the Hearth</h3><span>Mains</span></div>
          <div class="menu-item">
            <div><div class="menu-item-name">Hearth-Roasted Half Chicken</div><div class="menu-item-desc">Free-range Adams County chicken, pan jus, charred scallion, mashed Yukon gold. (GF)</div></div>
            <div class="menu-item-price">$27</div>
          </div>
          <div class="menu-item">
            <div><div class="menu-item-name">Cider-Braised Pork Shank</div><div class="menu-item-desc">Local orchard cider, root vegetable hash, whole grain mustard jus.</div></div>
            <div class="menu-item-price">$29</div>
          </div>
          <div class="menu-item">
            <div><div class="menu-item-name">Dry-Aged Ribeye, 14oz</div><div class="menu-item-desc">Wood-fired, bone marrow butter, roasted fingerlings, charred broccolini. (GF)</div></div>
            <div class="menu-item-price">$46</div>
          </div>
          <div class="menu-item">
            <div><div class="menu-item-name">Creek Trout, Whole Roasted</div><div class="menu-item-desc">Brown butter, capers, charred lemon, farro pilaf.</div></div>
            <div class="menu-item-price">$28</div>
          </div>
          <div class="menu-item">
            <div><div class="menu-item-name">Hearth Vegetable Plate</div><div class="menu-item-desc">Market squash, smoked white beans, charred kale, herbed grain. (V, GF)</div></div>
            <div class="menu-item-price">$22</div>
          </div>
        </div>

        <div class="menu-cat reveal" id="handhelds">
          <div class="menu-cat-head"><h3>Handhelds</h3><span>Tavern Fare</span></div>
          <div class="menu-item">
            <div><div class="menu-item-name">The Musket Burger</div><div class="menu-item-desc">Grass-fed beef, aged cheddar, tavern sauce, brioche, hand-cut fries.</div></div>
            <div class="menu-item-price">$18</div>
          </div>
          <div class="menu-item">
            <div><div class="menu-item-name">Fried Chicken Biscuit</div><div class="menu-item-desc">Buttermilk-fried thigh, hot honey, slaw, black pepper biscuit.</div></div>
            <div class="menu-item-price">$16</div>
          </div>
          <div class="menu-item">
            <div><div class="menu-item-name">Smoked Brisket Melt</div><div class="menu-item-desc">12-hour smoked brisket, caramelized onion, provolone, rye.</div></div>
            <div class="menu-item-price">$17</div>
          </div>
          <div class="menu-item">
            <div><div class="menu-item-name">Field Green Wrap</div><div class="menu-item-desc">Roasted vegetables, whipped feta, herb hummus, warm flatbread. (V)</div></div>
            <div class="menu-item-price">$15</div>
          </div>
        </div>

        <div class="menu-cat reveal" id="sides">
          <div class="menu-cat-head"><h3>Sides</h3><span>Add To Any Plate</span></div>
          <div class="menu-item">
            <div><div class="menu-item-name">Hand-Cut Fries, Rosemary Salt</div><div class="menu-item-desc">Duck fat fried, malt vinegar aioli. (GF)</div></div>
            <div class="menu-item-price">$8</div>
          </div>
          <div class="menu-item">
            <div><div class="menu-item-name">Charred Green Beans</div><div class="menu-item-desc">Chili crunch, toasted almond, lemon. (V, GF)</div></div>
            <div class="menu-item-price">$9</div>
          </div>
          <div class="menu-item">
            <div><div class="menu-item-name">Skillet Mac &amp; Cheese</div><div class="menu-item-desc">Three-cheese blend, toasted breadcrumb.</div></div>
            <div class="menu-item-price">$10</div>
          </div>
          <div class="menu-item">
            <div><div class="menu-item-name">Braised Collard Greens</div><div class="menu-item-desc">Smoked ham hock, apple cider vinegar. (GF)</div></div>
            <div class="menu-item-price">$8</div>
          </div>
        </div>

        <div class="menu-cat reveal" id="desserts">
          <div class="menu-cat-head"><h3>Desserts</h3><span>Sweet Endings</span></div>
          <div class="menu-item">
            <div><div class="menu-item-name">Skillet Peach Cobbler</div><div class="menu-item-desc">Warm, bourbon caramel, vanilla bean ice cream. (V)</div></div>
            <div class="menu-item-price">$11</div>
          </div>
          <div class="menu-item">
            <div><div class="menu-item-name">Black Walnut Cake</div><div class="menu-item-desc">Brown butter frosting, toasted walnut, sea salt. (V)</div></div>
            <div class="menu-item-price">$10</div>
          </div>
          <div class="menu-item">
            <div><div class="menu-item-name">Buttermilk Panna Cotta</div><div class="menu-item-desc">Macerated berries, honeycomb, mint. (V, GF)</div></div>
            <div class="menu-item-price">$10</div>
          </div>
        </div>

        <div class="menu-cat reveal" id="drinks">
          <div class="menu-cat-head"><h3>Drinks</h3><span>Bar &amp; Cellar</span></div>
          <div class="menu-item">
            <div><div class="menu-item-name">Musket Old Fashioned</div><div class="menu-item-desc">Local rye, orchard bitters, smoked cherry.</div></div>
            <div class="menu-item-price">$14</div>
          </div>
          <div class="menu-item">
            <div><div class="menu-item-name">Adams County Sour</div><div class="menu-item-desc">Bourbon, fresh peach, lemon, egg white.</div></div>
            <div class="menu-item-price">$13</div>
          </div>
          <div class="menu-item">
            <div><div class="menu-item-name">Cumberland Valley Cider Flight</div><div class="menu-item-desc">Three local ciders, seasonal rotation.</div></div>
            <div class="menu-item-price">$12</div>
          </div>
          <div class="menu-item">
            <div><div class="menu-item-name">PA Craft Draft List</div><div class="menu-item-desc">Rotating taps from Adams County &amp; South Central PA breweries.</div></div>
            <div class="menu-item-price">$7&ndash;9</div>
          </div>
        </div>

      </div>

      <p class="menu-note reveal">Menu and pricing reflect a July seasonal rotation and change with the harvest. 20% gratuity added for parties of 6 or more. Please inform your server of any allergies.</p>
    </div>
  </section>

  <!-- Weekly specials -->
  <section class="specials section-pad">
    <div class="wrap">
      <div class="section-head reveal">
        <p class="eyebrow">This Week</p>
        <h2>Off the hearth right now.</h2>
        <p>Small-batch, limited-run dishes built around what came off the truck this week from our Adams County growers.</p>
      </div>
      <div class="specials-grid">
        <article class="special-card reveal">
          <div class="special-media tone-a">
            <span class="special-badge">Farm Feature</span>
            <svg viewBox="0 0 64 64" fill="none" aria-hidden="true"><path d="M32 8c-8 8-13 18-13 27a13 13 0 0026 0c0-9-5-19-13-27Z" stroke="currentColor" stroke-width="2"/><path d="M32 35v21" stroke="currentColor" stroke-width="2"/></svg>
          </div>
          <div class="special-body">
            <h3>Round Barn Squash Agnolotti</h3>
            <p>House-rolled pasta stuffed with roasted Round Barn Farm butternut squash, brown butter, sage, and toasted pepitas. Available while the squash lasts.</p>
            <div class="special-foot"><span class="special-price">$26</span><span class="special-tag">Vegetarian &middot; Limited</span></div>
          </div>
        </article>
        <article class="special-card reveal">
          <div class="special-media tone-b">
            <span class="special-badge">Hearth Special</span>
            <svg viewBox="0 0 64 64" fill="none" aria-hidden="true"><path d="M12 44h40M18 44V22a4 4 0 014-4h20a4 4 0 014 4v22" stroke="currentColor" stroke-width="2"/><path d="M24 18V10M40 18V10" stroke="currentColor" stroke-width="2" stroke-linecap="round"/></svg>
          </div>
          <div class="special-body">
            <h3>Whole Roasted Cauliflower</h3>
            <p>Slow-roasted over the open hearth until the edges caramelize, glazed with fermented chili honey, finished with pickled shallot and herb yogurt.</p>
            <div class="special-foot"><span class="special-price">$21</span><span class="special-tag">Vegan-adaptable</span></div>
          </div>
        </article>
        <article class="special-card reveal">
          <div class="special-media tone-c">
            <span class="special-badge">Cellar Pick</span>
            <svg viewBox="0 0 64 64" fill="none" aria-hidden="true"><path d="M22 8h20l-4 20a10 10 0 01-12 0L22 8Z" stroke="currentColor" stroke-width="2" stroke-linejoin="round"/><path d="M32 34v18M24 52h16" stroke="currentColor" stroke-width="2" stroke-linecap="round"/></svg>
          </div>
          <div class="special-body">
            <h3>Smoked Old Fashioned Flight</h3>
            <p>Three riffs on our house Old Fashioned &mdash; classic rye, applewood-smoked, and black walnut bitters &mdash; poured tableside beneath a glass cloche.</p>
            <div class="special-foot"><span class="special-price">$24</span><span class="special-tag">21+ &middot; Serves One</span></div>
          </div>
        </article>
      </div>
    </div>
  </section>

  <!-- Dietary FAQ -->
  <section class="section-pad" style="background:var(--color-surface);">
    <div class="wrap">
      <div class="section-head reveal">
        <p class="eyebrow">Good to Know</p>
        <h2>Dietary &amp; ordering questions.</h2>
      </div>
      <div class="faq reveal">
        <div class="faq-item">
          <h3>Do you have vegetarian and gluten-free options?</h3>
          <p>Yes. Items marked (V) are vegetarian and (GF) are gluten-free adaptable. Our kitchen can adjust most hearth plates &mdash; just tell your server. As a scratch kitchen, we share equipment, so we can accommodate but not guarantee an allergen-free environment.</p>
        </div>
        <div class="faq-item">
          <h3>How often does the menu change?</h3>
          <p>The core menu rotates weekly with the Adams County harvest, and the hearth special changes nightly. Prices shown reflect the current July edition.</p>
        </div>
        <div class="faq-item">
          <h3>Can I see the menu before I reserve?</h3>
          <p>You&rsquo;re looking at it. Weekend brunch (Saturday and Sunday from 10am) runs a separate seasonal card &mdash; call us at <a href="tel:+17175550119">(717) 555-0119</a> for that week&rsquo;s brunch lineup.</p>
        </div>
        <div class="faq-item">
          <h3>Do you take large parties?</h3>
          <p>Absolutely &mdash; parties of six or more are welcome with a reservation, and we build private hearth menus for groups. See <a href="{{ home_url('/reserve/') }}">Reservations</a>.</p>
        </div>
      </div>
    </div>
  </section>

  <!-- CTA band -->
  <section class="cta-band section-pad">
    <div class="wrap">
      <p class="eyebrow">Ready to Eat</p>
      <h2>Bring your appetite to Chambersburg Street.</h2>
      <p>Dinner is served nightly from 4pm, with brunch on weekends. Reserve ahead so your table&rsquo;s waiting when you walk in from Lincoln Square.</p>
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
