{{--
  Template Name: Shop Hiking & Camp Gear
--}}

@extends('layouts.app')

@push('head')
<script type="application/ld+json">
{
  "@@context": "https://schema.org",
  "@@type": "SportingGoodsStore",
  "@@id": "{{ home_url('/') }}#store",
  "name": "Ridgeline Outfitters",
  "description": "Shop hiking, camping and battlefield-trail gear in Gettysburg, PA — apparel, footwear, packs and camp essentials.",
  "image": "{{ home_url('/preview.jpg/') }}",
  "url": "{{ home_url('/shop/') }}",
  "telephone": "+1-717-555-0120",
  "email": "gear@@ridgelineoutfitters.test",
  "priceRange": "$$",
  "currenciesAccepted": "USD",
  "paymentAccepted": "Cash, Visa, Mastercard, American Express, Apple Pay",
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
  "department": [
    { "@@type": "SportingGoodsStore", "name": "Apparel & Layers" },
    { "@@type": "SportingGoodsStore", "name": "Footwear" },
    { "@@type": "SportingGoodsStore", "name": "Packs" },
    { "@@type": "SportingGoodsStore", "name": "Camp & Cook" }
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
    { "@@type": "ListItem", "position": 2, "name": "Shop Gear", "item": "{{ home_url('/shop/') }}" }
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
      <a href="{{ home_url('/shop/') }}" class="is-active" aria-current="page">Shop Gear</a>
      <a href="{{ home_url('/guides/') }}">Gear Guides</a>
      <a href="{{ home_url('/about/') }}">About</a>
      <a href="{{ home_url('/visit/') }}">Visit Us</a>
      <a href="{{ home_url('/contact/') }}">Contact</a>
    </nav>

    <div class="header-actions">
      <button class="icon-btn" id="wishlistHeaderBtn" aria-label="View wishlist (0 items)">
        <svg viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2"><path d="M12 21s-7.5-4.6-10-9.3C.4 8 2 4.5 5.6 4.1c2-.2 3.7.8 4.9 2.5 1.2-1.7 2.9-2.7 4.9-2.5C19 4.5 20.6 8 20 11.7 17.5 16.4 12 21 12 21z"/></svg>
        <span class="count-badge" id="wishlistCount" aria-hidden="true">0</span>
      </button>
      <button class="icon-btn" id="cartOpenBtn" aria-label="Open cart, 0 items" aria-haspopup="dialog" aria-controls="cartDrawer">
        <svg viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2"><path d="M3 3h2l.4 2M7 13h10l3-8H5.4M7 13L5.4 5M7 13l-1.7 6.4A1 1 0 006.3 20H18M10 22a1 1 0 100-2 1 1 0 000 2zM18 22a1 1 0 100-2 1 1 0 000 2z"/></svg>
        <span class="count-badge" id="cartCount" aria-hidden="true">0</span>
      </button>
      <button class="icon-btn hamburger" id="menuToggle" aria-label="Open menu" aria-haspopup="true" aria-expanded="false" aria-controls="mobileMenu">
        <svg viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2"><path d="M3 6h18M3 12h18M3 18h18"/></svg>
      </button>
    </div>
  </div>

  <nav id="mobileMenu" class="mobile-menu" aria-label="Mobile" hidden>
    <a href="{{ home_url('/') }}">Home</a>
    <a href="{{ home_url('/shop/') }}" class="is-active" aria-current="page">Shop Gear</a>
    <a href="{{ home_url('/guides/') }}">Gear Guides</a>
    <a href="{{ home_url('/about/') }}">About</a>
    <a href="{{ home_url('/visit/') }}">Visit Us</a>
    <a href="{{ home_url('/contact/') }}">Contact</a>
  </nav>
</header>

<!-- ===================== BREADCRUMB ===================== -->
<nav class="breadcrumb" aria-label="Breadcrumb">
  <div class="container">
    <ol>
      <li><a href="{{ home_url('/') }}">Home</a></li>
      <li class="sep" aria-hidden="true">/</li>
      <li aria-current="page">Shop Gear</li>
    </ol>
  </div>
</nav>

<main id="main">

  <!-- ===================== PAGE HERO ===================== -->
  <section class="hero page-hero" id="top">
    <div class="container hero-grid">
      <div class="hero-copy">
        <span class="eyebrow on-dark">The Gear Wall · Gettysburg, PA</span>
        <h1>Shop trail-ready gear, <em>tested locally</em>.</h1>
        <p>Filter by category, tap a size, save favorites to your wishlist, and add straight to your cart. Every piece is staff-tested on the Adams County trails before it hits the floor. Free shipping over $75 — or free pickup on Buford Avenue.</p>
        <div class="hero-ctas">
          <a href="#shop" class="btn btn-primary">Jump to the gear</a>
          <a href="{{ home_url('/guides/') }}" class="btn btn-outline on-dark">Not sure what you need?</a>
        </div>
      </div>
      <div class="hero-visual">
        <div class="visual-overlay"></div>
        <div class="compass-badge">
          <svg viewBox="0 0 24 24" fill="none" stroke="#ff8654" stroke-width="1.6" aria-hidden="true"><circle cx="12" cy="12" r="9"/><path d="M15 9l-2 5-5 2 2-5z" fill="#ff8654" stroke="none"/></svg>
        </div>
        <div class="visual-tag">
          <span class="eyebrow">Field Notes</span>
          <p>"If we haven't hiked in it, it's not on the wall."</p>
        </div>
      </div>
    </div>
  </section>

  <!-- ===================== COLLECTIONS ===================== -->
  <section class="collections" id="collections">
    <div class="container">
      <div class="section-head reveal">
        <span class="eyebrow">Shop by Kit</span>
        <h2>Four ways to gear up</h2>
        <p>Tap a kit to filter the wall below, or browse everything.</p>
      </div>
      <div class="collection-grid">
        <button class="collection-tile reveal" data-jump="Apparel">
          <span>Apparel<small>Layers &amp; flannel</small></span>
        </button>
        <button class="collection-tile reveal" data-jump="Footwear">
          <span>Footwear<small>Boots &amp; trail runners</small></span>
        </button>
        <button class="collection-tile reveal" data-jump="Packs">
          <span>Packs<small>Day to overnight</small></span>
        </button>
        <button class="collection-tile reveal" data-jump="Camp">
          <span>Camp<small>Cook &amp; shelter</small></span>
        </button>
      </div>
    </div>
  </section>

  <!-- ===================== SHOP / GEAR WALL ===================== -->
  <section id="shop">
    <div class="container">
      <div class="section-head reveal">
        <span class="eyebrow">The Gear Wall</span>
        <h2>Shop trail-ready favorites</h2>
        <p>Filter by category, tap a size, and add straight to your cart. Every piece is trail-tested by our staff on the local Gettysburg-area trails before it hits the floor.</p>
      </div>

      <div class="shop-toolbar" role="tablist" aria-label="Filter products by category" id="filterTabs">
        <button class="filter-tab" role="tab" aria-pressed="true" data-filter="All">All Gear</button>
        <button class="filter-tab" role="tab" aria-pressed="false" data-filter="Apparel">Apparel</button>
        <button class="filter-tab" role="tab" aria-pressed="false" data-filter="Footwear">Footwear</button>
        <button class="filter-tab" role="tab" aria-pressed="false" data-filter="Packs">Packs</button>
        <button class="filter-tab" role="tab" aria-pressed="false" data-filter="Camp">Camp</button>
      </div>

      <div class="product-grid" id="productGrid"><!-- injected by shop.js --></div>
    </div>
  </section>

  <!-- ===================== NEW ARRIVALS ===================== -->
  <section class="arrivals" id="arrivals">
    <div class="container">
      <div class="section-head reveal">
        <span class="eyebrow on-dark">Fresh off the truck</span>
        <h2>New arrivals</h2>
        <p>Restocked weekly — first look at what just landed on the gear wall.</p>
      </div>
      <div class="arrivals-track" id="arrivalsTrack" tabindex="0" aria-label="New arrivals, scroll horizontally"><!-- injected by shop.js --></div>
    </div>
  </section>

  <!-- ===================== PICKUP / SHIPPING NOTE ===================== -->
  <section>
    <div class="container">
      <div class="cta-band reveal">
        <span class="eyebrow on-dark">Buy online, pick up local</span>
        <h2>Free in-store pickup on Buford Avenue</h2>
        <p>Order online and grab it in person at 120 Buford Avenue, Gettysburg — usually ready within the hour during shop hours. Free shipping over $75, and free returns within 30 days by mail or in store.</p>
        <div class="hero-ctas">
          <a href="{{ home_url('/visit/') }}" class="btn btn-primary">Directions &amp; hours</a>
          <a href="{{ home_url('/contact/') }}" class="btn btn-outline on-dark">Ask about a special order</a>
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

<!-- ===================== CART DRAWER ===================== -->
<div class="overlay" id="overlay"></div>

<aside class="cart-drawer" id="cartDrawer" role="dialog" aria-modal="true" aria-labelledby="cartTitle" aria-hidden="true">
  <div class="cart-head">
    <h2 id="cartTitle">Your Cart</h2>
    <button class="icon-btn" id="cartCloseBtn" aria-label="Close cart">
      <svg viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2"><path d="M6 6l12 12M18 6L6 18"/></svg>
    </button>
  </div>
  <div class="cart-body" id="cartBody"><!-- injected by shop.js --></div>
  <div class="cart-foot" id="cartFoot" hidden>
    <div class="cart-subtotal"><span>Subtotal</span><span class="mono" id="cartSubtotal">$0.00</span></div>
    <button class="btn btn-primary btn-block" id="checkoutBtn">Checkout</button>
  </div>
</aside>

<!-- ===================== QUICK VIEW MODAL ===================== -->
<div class="modal" id="quickViewModal" role="dialog" aria-modal="true" aria-labelledby="qvName" aria-hidden="true">
  <div class="modal-panel">
    <div class="modal-photo" id="qvPhoto"></div>
    <div class="modal-info">
      <button class="modal-close" id="qvCloseBtn" aria-label="Close quick view">
        <svg viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" width="18" height="18"><path d="M6 6l12 12M18 6L6 18"/></svg>
      </button>
      <span class="eyebrow" id="qvCat">Category</span>
      <h3 id="qvName">Product Name</h3>
      <p class="product-price" id="qvPrice">$0.00</p>
      <p class="desc" id="qvDesc">Description.</p>
      <span class="field-label">Select size</span>
      <div class="size-row" id="qvSizeRow"></div>
      <button class="btn btn-primary btn-block" id="qvAddBtn">
        <svg width="16" height="16" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" aria-hidden="true" style="margin-right:6px;"><path d="M7 13h10l3-8H5.4M7 13L5.4 5M7 13l-1.7 6.4A1 1 0 006.3 20H18"/></svg>
        Add to Cart
      </button>
    </div>
  </div>
</div>

<!-- ===================== TOAST ===================== -->
<div class="toast" id="toast" role="status" aria-live="polite">
  <svg viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" aria-hidden="true"><path d="M20 6L9 17l-5-5"/></svg>
  <span id="toastMsg">Added to cart</span>
</div>

<!-- aria-live region for cart count announcements -->
<div class="visually-hidden" aria-live="polite" id="cartAnnounce"></div>

<!-- ===================== CONCEPT BADGE ===================== -->
<a href="#" class="concept-badge" onclick="return false;">
  <span class="dot" aria-hidden="true"></span>
  Concept · Ridges &amp; Valleys Studio
</a>
@endsection
