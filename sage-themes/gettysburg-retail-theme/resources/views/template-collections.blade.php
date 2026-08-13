{{--
  Template Name: Collections
--}}

@extends('layouts.app')

@push('head')
<script type="application/ld+json">
{
  "@@context": "https://schema.org",
  "@@type": "Store",
  "@@id": "{{ home_url('/') }}#store",
  "name": "Diamond & Ridge Mercantile",
  "description": "Downtown Gettysburg gift shop and boutique stocked by local Adams County makers — candles, apparel, home goods and locally made gifts, available in-store and online.",
  "image": "{{ home_url('/preview.jpg/') }}",
  "url": "{{ home_url('/collections/') }}",
  "telephone": "+1-717-555-0133",
  "email": "shop@@diamondandridge.test",
  "priceRange": "$$",
  "currenciesAccepted": "USD",
  "paymentAccepted": "Cash, Visa, Mastercard, American Express, Apple Pay",
  "address": {
    "@@type": "PostalAddress",
    "streetAddress": "33 York Street",
    "addressLocality": "Gettysburg",
    "addressRegion": "PA",
    "postalCode": "17325",
    "addressCountry": "US"
  },
  "geo": { "@@type": "GeoCoordinates", "latitude": 39.8312, "longitude": -77.2299 },
  "hasMap": "https://www.google.com/maps?q=33+York+Street+Gettysburg+PA+17325",
  "openingHoursSpecification": [
    { "@@type": "OpeningHoursSpecification", "dayOfWeek": ["Monday","Tuesday","Wednesday","Thursday"], "opens": "10:00", "closes": "18:00" },
    { "@@type": "OpeningHoursSpecification", "dayOfWeek": ["Friday","Saturday"], "opens": "10:00", "closes": "20:00" },
    { "@@type": "OpeningHoursSpecification", "dayOfWeek": "Sunday", "opens": "11:00", "closes": "17:00" }
  ],
  "department": [
    { "@@type": "Store", "name": "Candles & Soap" },
    { "@@type": "Store", "name": "Apparel" },
    { "@@type": "Store", "name": "Home Goods" },
    { "@@type": "Store", "name": "Local Gifts & Pantry" }
  ],
  "areaServed": [
    { "@@type": "City", "name": "Gettysburg" },
    { "@@type": "AdministrativeArea", "name": "Adams County, Pennsylvania" },
    { "@@type": "City", "name": "Biglerville" },
    { "@@type": "City", "name": "New Oxford" },
    { "@@type": "City", "name": "Littlestown" },
    { "@@type": "City", "name": "McSherrystown" }
  ]
}
</script>
<script type="application/ld+json">
{
  "@@context": "https://schema.org",
  "@@type": "BreadcrumbList",
  "itemListElement": [
    { "@@type": "ListItem", "position": 1, "name": "Home", "item": "{{ home_url('/') }}" },
    { "@@type": "ListItem", "position": 2, "name": "Collections", "item": "{{ home_url('/collections/') }}" }
  ]
}
</script>
@endpush

@section('content')
<a href="#main" class="skip-link">Skip to main content</a>

<!-- ============ HEADER ============ -->
<header class="site-header">
  <div class="wrap header-inner">
    <a href="{{ home_url('/') }}" class="brand" aria-label="Diamond & Ridge Mercantile home">
      <span class="brand-mark" aria-hidden="true">D&amp;R</span>
      <span class="brand-word">
        <strong>Diamond &amp; Ridge</strong>
        <span>Mercantile Co.</span>
      </span>
    </a>
    <nav class="main-nav" aria-label="Primary">
      <ul>
        <li><a href="{{ home_url('/shop/') }}">Shop</a></li>
        <li><a href="{{ home_url('/collections/') }}" class="is-active" aria-current="page">Collections</a></li>
        <li><a href="{{ home_url('/about/') }}">About</a></li>
        <li><a href="{{ home_url('/visit/') }}">Visit</a></li>
        <li><a href="{{ home_url('/contact/') }}">Contact</a></li>
      </ul>
    </nav>
    <div class="header-actions">
      <a href="{{ home_url('/shop/') }}" class="btn btn-primary btn-sm header-cta">Shop online</a>
      <button class="icon-btn" id="cartToggle" aria-label="Open cart, 0 items" aria-haspopup="dialog" aria-controls="cartDrawer">
        <svg viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="1.8" stroke-linecap="round" stroke-linejoin="round" aria-hidden="true">
          <circle cx="9" cy="21" r="1.3" fill="currentColor" stroke="none"/>
          <circle cx="18" cy="21" r="1.3" fill="currentColor" stroke="none"/>
          <path d="M3 4h2l2.2 11.4a2 2 0 0 0 2 1.6h7.6a2 2 0 0 0 2-1.6L20.5 8H6.2"/>
        </svg>
        <span class="cart-badge" id="cartBadge" aria-hidden="true">0</span>
      </button>
      <button class="icon-btn hamburger" id="menuToggle" aria-label="Open menu" aria-haspopup="true" aria-expanded="false" aria-controls="mobileMenu">
        <svg viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="1.8" stroke-linecap="round" aria-hidden="true"><path d="M4 7h16M4 12h16M4 17h16"/></svg>
      </button>
    </div>
  </div>
</header>

<!-- Mobile menu -->
<div class="scrim" id="scrim"></div>
<nav class="mobile-menu" id="mobileMenu" aria-label="Mobile" aria-hidden="true">
  <div class="mobile-menu-head">
    <span class="brand-word"><strong>Menu</strong></span>
    <button class="icon-btn" id="menuClose" aria-label="Close menu">
      <svg viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="1.8" stroke-linecap="round" aria-hidden="true"><path d="M6 6l12 12M18 6L6 18"/></svg>
    </button>
  </div>
  <ul>
    <li><a href="{{ home_url('/shop/') }}" class="mobile-link">Shop</a></li>
    <li><a href="{{ home_url('/collections/') }}" class="mobile-link is-active">Collections</a></li>
    <li><a href="{{ home_url('/about/') }}" class="mobile-link">About / Local Makers</a></li>
    <li><a href="{{ home_url('/visit/') }}" class="mobile-link">Visit Us</a></li>
    <li><a href="{{ home_url('/contact/') }}" class="mobile-link">Contact</a></li>
  </ul>
</nav>

<!-- Cart drawer -->
<aside class="cart-drawer" id="cartDrawer" role="dialog" aria-modal="true" aria-labelledby="cartTitle" aria-hidden="true">
  <div class="cart-head">
    <h2 id="cartTitle">Your Bag</h2>
    <button class="icon-btn" id="cartClose" aria-label="Close cart">
      <svg viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="1.8" stroke-linecap="round" aria-hidden="true"><path d="M6 6l12 12M18 6L6 18"/></svg>
    </button>
  </div>
  <div class="cart-items" id="cartItems">
    <p class="cart-empty" id="cartEmptyMsg">Your bag is empty — browse the shop to add something local &amp; lovely.</p>
  </div>
  <div class="cart-foot">
    <div class="cart-total-row"><span>Subtotal</span><span id="cartTotal">$0.00</span></div>
    <button class="btn btn-primary btn-block" id="checkoutBtn">Checkout</button>
    <p class="cart-checkout-msg" id="checkoutMsg" aria-live="polite"></p>
  </div>
</aside>

<!-- Toast -->
<div class="toast" id="toast" role="status" aria-live="polite">
  <svg viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round" aria-hidden="true"><path d="M20 6L9 17l-5-5"/></svg>
  <span id="toastText">Added to cart</span>
</div>

<main id="main">
  <nav class="breadcrumb wrap" aria-label="Breadcrumb">
    <ol>
      <li><a href="{{ home_url('/') }}">Home</a></li>
      <li class="sep" aria-hidden="true">/</li>
      <li aria-current="page">Collections</li>
    </ol>
  </nav>

  <section class="page-hero">
    <div class="wrap">
      <span class="eyebrow">Browse by Collection</span>
      <h1>Shop the whole store, <em>by category</em></h1>
      <p>Four collections, each named for a corner of Gettysburg and Adams County. Jump to a category below, then add anything to your bag — the cart works right here.</p>
    </div>
  </section>

  <section>
    <div class="wrap">
      <div class="collections-grid">
        <a class="collection-tile ct-1 reveal" href="#candles"><span>24 pieces</span><h3>Candles</h3></a>
        <a class="collection-tile ct-2 reveal" href="#apparel"><span>18 pieces</span><h3>Apparel</h3></a>
        <a class="collection-tile ct-3 reveal" href="#home"><span>31 pieces</span><h3>Home Goods</h3></a>
        <a class="collection-tile ct-4 reveal" href="#gifts"><span>27 pieces</span><h3>Local Gifts</h3></a>
      </div>
    </div>
  </section>

  <div id="candles">
  <section>
    <div class="wrap">
      <div class="section-head reveal"><span class="eyebrow">Collection 01 · Candles</span><h2>Candles &amp; Soap, poured in Adams County</h2><p style="color:var(--ink-soft);">Small-batch soy candles and goat-milk soaps scented after the ridges, orchards and back roads around Gettysburg.</p></div>
      <div class="product-grid">
        <article class="product-card reveal">
          <div class="product-media pm-1"><svg viewBox="0 0 200 200" aria-hidden="true"><ellipse cx="100" cy="150" rx="55" ry="10" fill="rgba(0,0,0,0.15)"/><rect x="60" y="70" width="80" height="90" rx="8" fill="rgba(255,253,249,0.92)"/><ellipse cx="100" cy="70" rx="40" ry="10" fill="rgba(255,253,249,0.98)"/><path d="M100 40 Q108 55 100 60 Q92 55 100 40Z" fill="#e8a180"/></svg></div>
          <div class="product-body">
            <span class="product-cat">Candles</span>
            <h3 class="product-name">Round Top Ridge Soy Candle</h3>
            <div class="product-foot">
              <span class="product-price price">$26.00</span>
              <button class="add-btn" data-name="Round Top Ridge Soy Candle" data-price="26.00"><svg viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" aria-hidden="true"><path d="M12 5v14M5 12h14"/></svg> Add</button>
            </div>
          </div>
        </article>
        <article class="product-card reveal">
          <div class="product-media pm-1"><svg viewBox="0 0 200 200" aria-hidden="true"><ellipse cx="100" cy="150" rx="55" ry="10" fill="rgba(0,0,0,0.15)"/><rect x="60" y="70" width="80" height="90" rx="8" fill="rgba(255,253,249,0.92)"/><ellipse cx="100" cy="70" rx="40" ry="10" fill="rgba(255,253,249,0.98)"/><path d="M100 40 Q108 55 100 60 Q92 55 100 40Z" fill="#e8a180"/></svg></div>
          <div class="product-body">
            <span class="product-cat">Candles</span>
            <h3 class="product-name">Sachs Bridge Balsam Candle</h3>
            <div class="product-foot">
              <span class="product-price price">$26.00</span>
              <button class="add-btn" data-name="Sachs Bridge Balsam Candle" data-price="26.00"><svg viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" aria-hidden="true"><path d="M12 5v14M5 12h14"/></svg> Add</button>
            </div>
          </div>
        </article>
        <article class="product-card reveal">
          <div class="product-media pm-5"><svg viewBox="0 0 200 200" aria-hidden="true"><rect x="60" y="75" width="80" height="50" rx="10" fill="rgba(255,253,249,0.92)"/></svg></div>
          <div class="product-body">
            <span class="product-cat">Candles & Soap</span>
            <h3 class="product-name">Sachs Bridge Goat-Milk Soap</h3>
            <div class="product-foot">
              <span class="product-price price">$9.00</span>
              <button class="add-btn" data-name="Sachs Bridge Goat-Milk Soap" data-price="9.00"><svg viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" aria-hidden="true"><path d="M12 5v14M5 12h14"/></svg> Add</button>
            </div>
          </div>
        </article>
        <article class="product-card reveal">
          <div class="product-media pm-6"><svg viewBox="0 0 200 200" aria-hidden="true"><ellipse cx="100" cy="150" rx="55" ry="10" fill="rgba(0,0,0,0.15)"/><rect x="60" y="70" width="80" height="90" rx="8" fill="rgba(255,253,249,0.92)"/><ellipse cx="100" cy="70" rx="40" ry="10" fill="rgba(255,253,249,0.98)"/><path d="M100 40 Q108 55 100 60 Q92 55 100 40Z" fill="#e8a180"/></svg></div>
          <div class="product-body">
            <span class="product-cat">Candles</span>
            <h3 class="product-name">Cashtown Woodsmoke Candle</h3>
            <div class="product-foot">
              <span class="product-price price">$28.00</span>
              <button class="add-btn" data-name="Cashtown Woodsmoke Candle" data-price="28.00"><svg viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" aria-hidden="true"><path d="M12 5v14M5 12h14"/></svg> Add</button>
            </div>
          </div>
        </article>
      </div>
    </div>
  </section></div>
  <div id="apparel" style="background:var(--cream-deep);border-block:1px solid var(--line);">
  <section>
    <div class="wrap">
      <div class="section-head reveal"><span class="eyebrow">Collection 02 · Apparel</span><h2>Apparel, printed on Chambersburg Street</h2><p style="color:var(--ink-soft);">Heavyweight tees, crewnecks and canvas bags screen-printed by a shop a few doors down from the Diamond.</p></div>
      <div class="product-grid">
        <article class="product-card reveal">
          <div class="product-media pm-2"><svg viewBox="0 0 200 200" aria-hidden="true"><rect x="55" y="55" width="90" height="110" rx="4" fill="rgba(255,253,249,0.9)"/><rect x="70" y="80" width="60" height="8" rx="4" fill="#5c6a51"/><rect x="70" y="98" width="40" height="8" rx="4" fill="#5c6a51"/></svg></div>
          <div class="product-body">
            <span class="product-cat">Apparel</span>
            <h3 class="product-name">Diamond Field Heavyweight Tee</h3>
            <div class="product-foot">
              <span class="product-price price">$34.00</span>
              <button class="add-btn" data-name="Diamond Field Heavyweight Tee" data-price="34.00"><svg viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" aria-hidden="true"><path d="M12 5v14M5 12h14"/></svg> Add</button>
            </div>
          </div>
        </article>
        <article class="product-card reveal">
          <div class="product-media pm-8"><svg viewBox="0 0 200 200" aria-hidden="true"><rect x="55" y="70" width="90" height="65" rx="10" fill="rgba(255,253,249,0.9)"/><path d="M80 70a20 20 0 0 1 40 0" fill="none" stroke="rgba(255,253,249,0.9)" stroke-width="6"/></svg></div>
          <div class="product-body">
            <span class="product-cat">Apparel</span>
            <h3 class="product-name">Taneytown Rd Canvas Weekender</h3>
            <div class="product-foot">
              <span class="product-price price">$58.00</span>
              <button class="add-btn" data-name="Taneytown Rd Canvas Weekender" data-price="58.00"><svg viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" aria-hidden="true"><path d="M12 5v14M5 12h14"/></svg> Add</button>
            </div>
          </div>
        </article>
        <article class="product-card reveal">
          <div class="product-media pm-5"><svg viewBox="0 0 200 200" aria-hidden="true"><rect x="55" y="55" width="90" height="110" rx="4" fill="rgba(255,253,249,0.9)"/><rect x="70" y="80" width="60" height="8" rx="4" fill="#5c6a51"/><rect x="70" y="98" width="40" height="8" rx="4" fill="#5c6a51"/></svg></div>
          <div class="product-body">
            <span class="product-cat">Apparel</span>
            <h3 class="product-name">Seminary Ridge Crewneck</h3>
            <div class="product-foot">
              <span class="product-price price">$52.00</span>
              <button class="add-btn" data-name="Seminary Ridge Crewneck" data-price="52.00"><svg viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" aria-hidden="true"><path d="M12 5v14M5 12h14"/></svg> Add</button>
            </div>
          </div>
        </article>
        <article class="product-card reveal">
          <div class="product-media pm-3"><svg viewBox="0 0 200 200" aria-hidden="true"><rect x="55" y="55" width="90" height="110" rx="4" fill="rgba(255,253,249,0.9)"/><rect x="70" y="80" width="60" height="8" rx="4" fill="#5c6a51"/><rect x="70" y="98" width="40" height="8" rx="4" fill="#5c6a51"/></svg></div>
          <div class="product-body">
            <span class="product-cat">Apparel</span>
            <h3 class="product-name">Adams County Ball Cap</h3>
            <div class="product-foot">
              <span class="product-price price">$28.00</span>
              <button class="add-btn" data-name="Adams County Ball Cap" data-price="28.00"><svg viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" aria-hidden="true"><path d="M12 5v14M5 12h14"/></svg> Add</button>
            </div>
          </div>
        </article>
      </div>
    </div>
  </section></div>
  <div id="home">
  <section>
    <div class="wrap">
      <div class="section-head reveal"><span class="eyebrow">Collection 03 · Home Goods</span><h2>Home Goods for the mantel and the table</h2><p style="color:var(--ink-soft);">Stoneware thrown in a Biglerville studio, wool throws, and printed tea towels for the kitchen.</p></div>
      <div class="product-grid">
        <article class="product-card reveal">
          <div class="product-media pm-3"><svg viewBox="0 0 200 200" aria-hidden="true"><path d="M70 70 h60 v70 a30 30 0 0 1 -60 0 z" fill="rgba(255,253,249,0.92)"/><path d="M130 90 q25 0 25 22 q0 22 -25 22" fill="none" stroke="rgba(255,253,249,0.92)" stroke-width="8"/></svg></div>
          <div class="product-body">
            <span class="product-cat">Ceramics & Home</span>
            <h3 class="product-name">Seminary Ridge Stoneware Mug</h3>
            <div class="product-foot">
              <span class="product-price price">$29.00</span>
              <button class="add-btn" data-name="Seminary Ridge Stoneware Mug" data-price="29.00"><svg viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" aria-hidden="true"><path d="M12 5v14M5 12h14"/></svg> Add</button>
            </div>
          </div>
        </article>
        <article class="product-card reveal">
          <div class="product-media pm-7"><svg viewBox="0 0 200 200" aria-hidden="true"><rect x="45" y="70" width="110" height="80" rx="6" fill="none" stroke="rgba(87,74,56,0.5)" stroke-width="4"/><path d="M45 110h110M85 70v80M125 70v80" stroke="rgba(87,74,56,0.35)" stroke-width="3"/></svg></div>
          <div class="product-body">
            <span class="product-cat">Home</span>
            <h3 class="product-name">Fieldstone Wool Throw</h3>
            <div class="product-foot">
              <span class="product-price price">$72.00</span>
              <button class="add-btn" data-name="Fieldstone Wool Throw" data-price="72.00"><svg viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" aria-hidden="true"><path d="M12 5v14M5 12h14"/></svg> Add</button>
            </div>
          </div>
        </article>
        <article class="product-card reveal">
          <div class="product-media pm-5"><svg viewBox="0 0 200 200" aria-hidden="true"><rect x="55" y="65" width="90" height="70" rx="6" fill="rgba(255,253,249,0.9)"/><path d="M65 75h70M65 90h70M65 105h50M65 120h60" stroke="#5c6a51" stroke-width="4" stroke-linecap="round"/></svg></div>
          <div class="product-body">
            <span class="product-cat">Home</span>
            <h3 class="product-name">Pickett's Charge Trail Tea Towel Set</h3>
            <div class="product-foot">
              <span class="product-price price">$19.00</span>
              <button class="add-btn" data-name="Pickett's Charge Trail Tea Towel Set" data-price="19.00"><svg viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" aria-hidden="true"><path d="M12 5v14M5 12h14"/></svg> Add</button>
            </div>
          </div>
        </article>
        <article class="product-card reveal">
          <div class="product-media pm-3"><svg viewBox="0 0 200 200" aria-hidden="true"><ellipse cx="100" cy="100" rx="40" ry="55" fill="rgba(255,253,249,0.9)"/></svg></div>
          <div class="product-body">
            <span class="product-cat">Ceramics & Home</span>
            <h3 class="product-name">Cross Keys Ceramic Bud Vase</h3>
            <div class="product-foot">
              <span class="product-price price">$22.00</span>
              <button class="add-btn" data-name="Cross Keys Ceramic Bud Vase" data-price="22.00"><svg viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" aria-hidden="true"><path d="M12 5v14M5 12h14"/></svg> Add</button>
            </div>
          </div>
        </article>
      </div>
    </div>
  </section></div>
  <div id="gifts" style="background:var(--cream-deep);border-block:1px solid var(--line);">
  <section>
    <div class="wrap">
      <div class="section-head reveal"><span class="eyebrow">Collection 04 · Local Gifts</span><h2>Local Gifts &amp; Pantry from Adams County</h2><p style="color:var(--ink-soft);">Wildflower honey, apple butter from the orchards along Route 34, cold brew and books about the town itself.</p></div>
      <div class="product-grid">
        <article class="product-card reveal">
          <div class="product-media pm-4"><svg viewBox="0 0 200 200" aria-hidden="true"><path d="M100 55 C130 80 145 105 130 130 C118 150 82 150 70 130 C55 105 70 80 100 55 Z" fill="rgba(255,253,249,0.92)"/></svg></div>
          <div class="product-body">
            <span class="product-cat">Local Gifts</span>
            <h3 class="product-name">Round Barn Wildflower Honey</h3>
            <div class="product-foot">
              <span class="product-price price">$13.00</span>
              <button class="add-btn" data-name="Round Barn Wildflower Honey" data-price="13.00"><svg viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" aria-hidden="true"><path d="M12 5v14M5 12h14"/></svg> Add</button>
            </div>
          </div>
        </article>
        <article class="product-card reveal">
          <div class="product-media pm-6"><svg viewBox="0 0 200 200" aria-hidden="true"><rect x="80" y="60" width="40" height="90" rx="10" fill="rgba(255,253,249,0.92)"/><rect x="90" y="50" width="20" height="14" rx="4" fill="rgba(255,253,249,0.92)"/></svg></div>
          <div class="product-body">
            <span class="product-cat">Local Gifts</span>
            <h3 class="product-name">Cannonball Cold Brew Concentrate</h3>
            <div class="product-foot">
              <span class="product-price price">$16.00</span>
              <button class="add-btn" data-name="Cannonball Cold Brew Concentrate" data-price="16.00"><svg viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" aria-hidden="true"><path d="M12 5v14M5 12h14"/></svg> Add</button>
            </div>
          </div>
        </article>
        <article class="product-card reveal">
          <div class="product-media pm-4"><svg viewBox="0 0 200 200" aria-hidden="true"><path d="M100 55 C130 80 145 105 130 130 C118 150 82 150 70 130 C55 105 70 80 100 55 Z" fill="rgba(255,253,249,0.92)"/></svg></div>
          <div class="product-body">
            <span class="product-cat">Local Gifts</span>
            <h3 class="product-name">Orchard Row Apple Butter</h3>
            <div class="product-foot">
              <span class="product-price price">$11.00</span>
              <button class="add-btn" data-name="Orchard Row Apple Butter" data-price="11.00"><svg viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" aria-hidden="true"><path d="M12 5v14M5 12h14"/></svg> Add</button>
            </div>
          </div>
        </article>
        <article class="product-card reveal">
          <div class="product-media pm-8"><svg viewBox="0 0 200 200" aria-hidden="true"><rect x="60" y="55" width="40" height="90" rx="3" fill="rgba(255,253,249,0.92)" transform="rotate(-6 80 100)"/><rect x="95" y="60" width="40" height="90" rx="3" fill="rgba(255,253,249,0.75)" transform="rotate(4 115 105)"/></svg></div>
          <div class="product-body">
            <span class="product-cat">Books</span>
            <h3 class="product-name">Hallowed Ground Reader — 3-Book Set</h3>
            <div class="product-foot">
              <span class="product-price price">$48.00</span>
              <button class="add-btn" data-name="Hallowed Ground Reader — 3-Book Set" data-price="48.00"><svg viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" aria-hidden="true"><path d="M12 5v14M5 12h14"/></svg> Add</button>
            </div>
          </div>
        </article>
      </div>
    </div>
  </section></div>

  <section>
    <div class="wrap">
      <div class="cta-band reveal">
        <h2>Building a gift box?</h2>
        <p>Mix candles, pantry goods and a mug into one box and we'll wrap it. Tell us the occasion and we'll help you choose.</p>
        <div class="hero-ctas"><a href="{{ home_url('/contact/') }}" class="btn btn-light">Ask about gift boxes</a><a href="{{ home_url('/shop/') }}" class="btn btn-outline" style="color:var(--cream);border-color:var(--cream);">Shop everything</a></div>
      </div>
    </div>
  </section>

</main>

<!-- ============ FOOTER ============ -->
<footer class="site-footer">
  <div class="wrap">
    <div class="footer-grid">
      <div>
        <div class="footer-brand">
          <span class="brand-mark" aria-hidden="true" style="width:36px;height:36px;font-size:1.1rem;">D&amp;R</span>
          <strong>Diamond &amp; Ridge Mercantile</strong>
        </div>
        <p class="footer-desc">Locally-made candles, apparel, home goods &amp; gifts from a storefront on York Street in downtown Gettysburg, PA.</p>
        <div class="payment-icons" aria-label="Accepted payment methods">
          <svg viewBox="0 0 48 32" role="img" aria-label="Visa"><rect width="48" height="32" rx="4" fill="#1a1f71"/><text x="24" y="21" font-family="Arial, sans-serif" font-size="12" font-weight="700" fill="#fff" text-anchor="middle">VISA</text></svg>
          <svg viewBox="0 0 48 32" role="img" aria-label="Mastercard"><rect width="48" height="32" rx="4" fill="#252525"/><circle cx="20" cy="16" r="9" fill="#eb001b"/><circle cx="28" cy="16" r="9" fill="#f79e1b" fill-opacity="0.9"/></svg>
          <svg viewBox="0 0 48 32" role="img" aria-label="American Express"><rect width="48" height="32" rx="4" fill="#2e77bc"/><text x="24" y="21" font-family="Arial, sans-serif" font-size="10" font-weight="700" fill="#fff" text-anchor="middle">AMEX</text></svg>
          <svg viewBox="0 0 48 32" role="img" aria-label="Apple Pay"><rect width="48" height="32" rx="4" fill="#000"/><text x="24" y="21" font-family="Arial, sans-serif" font-size="9" font-weight="600" fill="#fff" text-anchor="middle"> Pay</text></svg>
        </div>
      </div>
      <div>
        <h4>Explore</h4>
        <ul>
          <li><a href="{{ home_url('/shop/') }}">Shop</a></li>
          <li><a href="{{ home_url('/collections/') }}">Collections</a></li>
          <li><a href="{{ home_url('/about/') }}">Local Makers</a></li>
          <li><a href="{{ home_url('/visit/') }}">Visit Us</a></li>
          <li><a href="{{ home_url('/contact/') }}">Contact</a></li>
        </ul>
      </div>
      <div>
        <h4>Store</h4>
        <ul>
          <li><a href="{{ home_url('/visit/') }}">Hours &amp; Directions</a></li>
          <li><a href="{{ home_url('/visit/') }}">The Area</a></li>
          <li><a href="{{ home_url('/contact/') }}">Shipping &amp; Returns</a></li>
          <li><a href="{{ home_url('/contact/') }}">Wholesale &amp; Gift Boxes</a></li>
        </ul>
        <p style="margin-top:1rem;font-size:0.85rem;color:#b7a98c;">Free shipping over $75. Returns accepted within 30 days on unused items — in-store or by mail.</p>
      </div>
      <div>
        <h4>Find Us</h4>
        <address class="footer-nap">
          Diamond &amp; Ridge Mercantile<br>
          33 York Street<br>
          Gettysburg, PA 17325<br>
          <a href="tel:+17175550133">(717) 555-0133</a><br>
          <a href="mailto:shop@@diamondandridge.test">shop@@diamondandridge.test</a>
        </address>
        <div class="social-row">
          <a href="#" aria-label="Diamond & Ridge Mercantile on Instagram"><svg viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="1.6" aria-hidden="true"><rect x="3" y="3" width="18" height="18" rx="5"/><circle cx="12" cy="12" r="4"/><circle cx="17.5" cy="6.5" r="1"/></svg></a>
          <a href="#" aria-label="Diamond & Ridge Mercantile on Facebook"><svg viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="1.6" aria-hidden="true"><path d="M14 9h3V6h-3a4 4 0 0 0-4 4v2H7v3h3v6h3v-6h3l1-3h-4v-2a1 1 0 0 1 1-1z"/></svg></a>
          <a href="#" aria-label="Diamond & Ridge Mercantile on Pinterest"><svg viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="1.6" aria-hidden="true"><circle cx="12" cy="12" r="9"/><path d="M9 17c1-4 1.5-6.5 1.5-8a2.5 2.5 0 0 1 5 0c0 1.5-1 4.5-1.5 6a2 2 0 0 0 4 .5"/></svg></a>
        </div>
      </div>
    </div>
    <p style="margin-top:2rem;font-size:0.85rem;color:#b7a98c;max-width:60ch;">Proudly serving Gettysburg and the towns of Adams County — Biglerville, New Oxford, Littlestown, McSherrystown, Fairfield, Cashtown and Hanover nearby.</p>
    <div class="footer-bottom">
      <span>© <span class="js-year">2026</span> Diamond &amp; Ridge Mercantile. Design concept by Ridges &amp; Valleys Studio.</span>
      <span>Concept for demonstration — not a live store.</span>
    </div>
  </div>
</footer>

<!-- Concept badge -->
<a href="#" class="concept-badge">
  <svg viewBox="0 0 24 24" fill="currentColor" aria-hidden="true"><path d="M12 2l2.4 7.2H22l-6 4.6 2.3 7.2L12 16.5 5.7 21l2.3-7.2-6-4.6h7.6z"/></svg>
  Concept · Ridges &amp; Valleys Studio
</a>
@endsection
