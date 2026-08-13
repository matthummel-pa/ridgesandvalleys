{{--
  Template Name: Order Online
--}}

@extends('layouts.app')

@push('head')
<script type="application/ld+json">
{
  "@@context": "https://schema.org",
  "@@type": "CafeOrCoffeeShop",
  "@@id": "{{ home_url('/') }}#business",
  "name": "Cannon & Crumb",
  "description": "All-day cafe and scratch bakery on Lincoln Square in downtown Gettysburg, PA, with online ordering for pickup and local delivery.",
  "image": "{{ home_url('/preview.jpg/') }}",
  "url": "{{ home_url('/order/') }}",
  "telephone": "+1-717-555-0107",
  "email": "hello@@cannonandcrumb.test",
  "priceRange": "$$",
  "servesCuisine": ["Cafe", "Bakery", "Breakfast", "Brunch", "Coffee", "American"],
  "menu": "{{ home_url('/menu/') }}",
  "hasMenu": "{{ home_url('/menu/') }}",
  "acceptsReservations": "False",
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
    { "@@type": "ListItem", "position": 2, "name": "Order Online", "item": "{{ home_url('/order/') }}" }
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
      <a href="{{ home_url('/order/') }}" class="is-active" aria-current="page">Order Online</a>
      <a href="{{ home_url('/catering/') }}">Catering</a>
      <a href="{{ home_url('/visit/') }}">Visit</a>
      <a href="{{ home_url('/contact/') }}">Contact</a>
    </nav>

    <div class="header-actions">
      <a class="icon-btn" id="cart-btn" href="{{ home_url('/order/') }}" aria-haspopup="dialog" aria-controls="order-panel" aria-label="Open your order, 0 items">
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
    <li aria-current="page">Order Online</li>
  </ol>
</nav>

<main id="main">

  <section class="page-hero">
    <div class="container">
      <span class="eyebrow">Order Online</span>
      <h1>Order ahead. Skip the line on the Square.</h1>
      <p>Add your cafe &amp; bakery favorites below, choose pickup at 7 Lincoln Square or delivery around downtown Gettysburg, and watch your total update as you go. Tap the cart any time to review and check out.</p>
      <div class="hero-actions" style="margin-top:1.75rem;">
        <a href="#order-menu" class="btn btn-primary">Start Your Order</a>
        <a href="tel:+17175550107" class="btn btn-outline" style="color:#fff; border-color:rgba(255,255,255,0.7);">Call (717) 555-0107</a>
      </div>
    </div>
  </section>

  <!-- HOW IT WORKS -->
  <section>
    <div class="container">
      <div class="section-head reveal">
        <span class="eyebrow">How It Works</span>
        <h2>Three taps to breakfast</h2>
      </div>
      <div class="info-grid">
        <div class="info-card reveal">
          <h3><svg viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round"><path d="M9 11l3 3L22 4"/><path d="M21 12v7a2 2 0 0 1-2 2H5a2 2 0 0 1-2-2V5a2 2 0 0 1 2-2h11"/></svg>1. Add your items</h3>
          <p>Pick from breakfast &amp; brunch, the bakery case, lunch, and coffee. Adjust quantities right on each card.</p>
        </div>
        <div class="info-card reveal">
          <h3><svg viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round"><path d="M3 6h18M3 12h18M3 18h18"/></svg>2. Pickup or delivery</h3>
          <p>Toggle between pickup on Lincoln Square and delivery around downtown Gettysburg. Tax and any delivery fee are calculated live.</p>
        </div>
        <div class="info-card reveal">
          <h3><svg viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round"><circle cx="12" cy="12" r="9"/><path d="M12 7v5l3 2"/></svg>3. Check out</h3>
          <p>Review your running total and place your order. We'll have it ready — most pickups in about 15 minutes.</p>
        </div>
      </div>
    </div>
  </section>

  <!-- ORDER MENU -->
  <section class="menu-section" id="order-menu">
    <div class="container">
      <div class="section-head reveal">
        <span class="eyebrow">Build Your Order</span>
        <h2>What are we making you today?</h2>
        <p>Add anything below to your cart — filter by diet, then tap the cart to review pickup or delivery and check out.</p>
      </div>

      <div class="menu-tabs" role="tablist" aria-label="Menu categories" id="menu-tabs">
        <button class="menu-tab" role="tab" aria-selected="true" id="tab-breakfast" aria-controls="panel-breakfast" data-tab="breakfast">Breakfast &amp; Brunch</button>
        <button class="menu-tab" role="tab" aria-selected="false" id="tab-bakery" aria-controls="panel-bakery" data-tab="bakery">Bakery</button>
        <button class="menu-tab" role="tab" aria-selected="false" id="tab-lunch" aria-controls="panel-lunch" data-tab="lunch">Lunch</button>
        <button class="menu-tab" role="tab" aria-selected="false" id="tab-drinks" aria-controls="panel-drinks" data-tab="drinks">Coffee &amp; Drinks</button>
      </div>

      <div class="menu-filters" role="group" aria-label="Dietary filters">
        <button class="filter-chip" data-filter="veg" aria-pressed="false">Vegetarian</button>
        <button class="filter-chip" data-filter="vegan" aria-pressed="false">Vegan</button>
        <button class="filter-chip" data-filter="gf" aria-pressed="false">Gluten-free</button>
      </div>

      <!-- BREAKFAST -->
      <div class="menu-panel active" id="panel-breakfast" role="tabpanel" aria-labelledby="tab-breakfast">
        <div class="menu-grid">
          <article class="menu-card" data-tags="veg gf" data-id="b1" data-name="Battlefield Benedict" data-price="14.50">
            <div class="card-top"><h3>Battlefield Benedict</h3><span class="card-price mono">$14.50</span></div>
            <p class="card-desc">Two poached eggs, Adams County ham, herbed hollandaise, on a toasted English muffin with home fries.</p>
            <div class="card-tags"><span class="tag gf">Gluten-free option</span></div>
            <div class="card-actions">
              <div class="stepper" data-stepper="b1"><button type="button" class="step-minus" aria-label="Decrease quantity">−</button><span aria-hidden="true">1</span><button type="button" class="step-plus" aria-label="Increase quantity">+</button></div>
              <button class="add-btn" data-add="b1">Add to order</button>
            </div>
          </article>
          <article class="menu-card" data-tags="veg" data-id="b2" data-name="Cannon's Golden Waffle" data-price="12.00">
            <div class="card-top"><h3>Cannon's Golden Waffle</h3><span class="card-price mono">$12.00</span></div>
            <p class="card-desc">Buttermilk waffle, whipped honey butter, Adams County apple compote, pure maple syrup.</p>
            <div class="card-tags"><span class="tag veg">Vegetarian</span></div>
            <div class="card-actions">
              <div class="stepper" data-stepper="b2"><button type="button" class="step-minus" aria-label="Decrease quantity">−</button><span aria-hidden="true">1</span><button type="button" class="step-plus" aria-label="Increase quantity">+</button></div>
              <button class="add-btn" data-add="b2">Add to order</button>
            </div>
          </article>
          <article class="menu-card" data-tags="vegan gf" data-id="b3" data-name="Orchard Overnight Oats" data-price="8.50">
            <div class="card-top"><h3>Orchard Overnight Oats</h3><span class="card-price mono">$8.50</span></div>
            <p class="card-desc">Steel-cut oats, oat milk, roasted local apples, cinnamon, toasted walnuts, maple drizzle.</p>
            <div class="card-tags"><span class="tag vgn">Vegan</span><span class="tag gf">Gluten-free</span></div>
            <div class="card-actions">
              <div class="stepper" data-stepper="b3"><button type="button" class="step-minus" aria-label="Decrease quantity">−</button><span aria-hidden="true">1</span><button type="button" class="step-plus" aria-label="Increase quantity">+</button></div>
              <button class="add-btn" data-add="b3">Add to order</button>
            </div>
          </article>
          <article class="menu-card" data-tags="" data-id="b4" data-name="Farmhouse Skillet" data-price="13.75">
            <div class="card-top"><h3>Farmhouse Skillet</h3><span class="card-price mono">$13.75</span></div>
            <p class="card-desc">Crispy potatoes, sausage, peppers, cheddar, two eggs any style, sourdough toast.</p>
            <div class="card-tags"></div>
            <div class="card-actions">
              <div class="stepper" data-stepper="b4"><button type="button" class="step-minus" aria-label="Decrease quantity">−</button><span aria-hidden="true">1</span><button type="button" class="step-plus" aria-label="Increase quantity">+</button></div>
              <button class="add-btn" data-add="b4">Add to order</button>
            </div>
          </article>
          <article class="menu-card" data-tags="vegan" data-id="b5" data-name="Seminary Ridge Tofu Scramble" data-price="11.50">
            <div class="card-top"><h3>Seminary Ridge Scramble</h3><span class="card-price mono">$11.50</span></div>
            <p class="card-desc">Turmeric-scrambled tofu, roasted mushrooms, spinach, salsa verde, corn tortillas.</p>
            <div class="card-tags"><span class="tag vgn">Vegan</span></div>
            <div class="card-actions">
              <div class="stepper" data-stepper="b5"><button type="button" class="step-minus" aria-label="Decrease quantity">−</button><span aria-hidden="true">1</span><button type="button" class="step-plus" aria-label="Increase quantity">+</button></div>
              <button class="add-btn" data-add="b5">Add to order</button>
            </div>
          </article>
          <article class="menu-card" data-tags="veg" data-id="b6" data-name="Crumb's Big Bagel Plate" data-price="10.75">
            <div class="card-top"><h3>Crumb's Big Bagel Plate</h3><span class="card-price mono">$10.75</span></div>
            <p class="card-desc">Everything bagel, whipped chive cream cheese, tomato, cucumber, capers, red onion.</p>
            <div class="card-tags"><span class="tag veg">Vegetarian</span></div>
            <div class="card-actions">
              <div class="stepper" data-stepper="b6"><button type="button" class="step-minus" aria-label="Decrease quantity">−</button><span aria-hidden="true">1</span><button type="button" class="step-plus" aria-label="Increase quantity">+</button></div>
              <button class="add-btn" data-add="b6">Add to order</button>
            </div>
          </article>
        </div>
        <p class="empty-note">No items match your current filters. Try clearing a filter above.</p>
      </div>

      <!-- BAKERY -->
      <div class="menu-panel" id="panel-bakery" role="tabpanel" aria-labelledby="tab-bakery" hidden>
        <div class="menu-grid">
          <article class="menu-card" data-tags="veg" data-id="k1" data-name="Raspberry Almond Croissant" data-price="5.25">
            <div class="card-top"><h3>Raspberry Almond Croissant</h3><span class="card-price mono">$5.25</span></div>
            <p class="card-desc">Laminated butter croissant, house raspberry jam, toasted almond frangipane.</p>
            <div class="card-tags"><span class="tag veg">Vegetarian</span></div>
            <div class="card-actions">
              <div class="stepper" data-stepper="k1"><button type="button" class="step-minus" aria-label="Decrease quantity">−</button><span aria-hidden="true">1</span><button type="button" class="step-plus" aria-label="Increase quantity">+</button></div>
              <button class="add-btn" data-add="k1">Add to order</button>
            </div>
          </article>
          <article class="menu-card" data-tags="vegan gf" data-id="k2" data-name="Flourless Chocolate Cookie" data-price="3.75">
            <div class="card-top"><h3>Flourless Chocolate Cookie</h3><span class="card-price mono">$3.75</span></div>
            <p class="card-desc">Fudgy dark chocolate cookie made with almond flour and flaky sea salt.</p>
            <div class="card-tags"><span class="tag vgn">Vegan</span><span class="tag gf">Gluten-free</span></div>
            <div class="card-actions">
              <div class="stepper" data-stepper="k2"><button type="button" class="step-minus" aria-label="Decrease quantity">−</button><span aria-hidden="true">1</span><button type="button" class="step-plus" aria-label="Increase quantity">+</button></div>
              <button class="add-btn" data-add="k2">Add to order</button>
            </div>
          </article>
          <article class="menu-card" data-tags="veg" data-id="k3" data-name="Apple Cider Donut" data-price="3.25">
            <div class="card-top"><h3>Apple Cider Donut</h3><span class="card-price mono">$3.25</span></div>
            <p class="card-desc">Cake donut made with local cider, rolled in cinnamon sugar — an Adams County classic.</p>
            <div class="card-tags"><span class="tag veg">Vegetarian</span></div>
            <div class="card-actions">
              <div class="stepper" data-stepper="k3"><button type="button" class="step-minus" aria-label="Decrease quantity">−</button><span aria-hidden="true">1</span><button type="button" class="step-plus" aria-label="Increase quantity">+</button></div>
              <button class="add-btn" data-add="k3">Add to order</button>
            </div>
          </article>
          <article class="menu-card" data-tags="veg" data-id="k4" data-name="Cannon Sticky Bun" data-price="5.50">
            <div class="card-top"><h3>Cannon Sticky Bun</h3><span class="card-price mono">$5.50</span></div>
            <p class="card-desc">Slow-proofed brioche, brown butter caramel, toasted pecans. Our best-seller since day one.</p>
            <div class="card-tags"><span class="tag veg">Vegetarian</span></div>
            <div class="card-actions">
              <div class="stepper" data-stepper="k4"><button type="button" class="step-minus" aria-label="Decrease quantity">−</button><span aria-hidden="true">1</span><button type="button" class="step-plus" aria-label="Increase quantity">+</button></div>
              <button class="add-btn" data-add="k4">Add to order</button>
            </div>
          </article>
          <article class="menu-card" data-tags="vegan" data-id="k5" data-name="Blueberry Oat Muffin" data-price="4.25">
            <div class="card-top"><h3>Blueberry Oat Muffin</h3><span class="card-price mono">$4.25</span></div>
            <p class="card-desc">Dairy-free muffin loaded with local blueberries and a crackly oat streusel top.</p>
            <div class="card-tags"><span class="tag vgn">Vegan</span></div>
            <div class="card-actions">
              <div class="stepper" data-stepper="k5"><button type="button" class="step-minus" aria-label="Decrease quantity">−</button><span aria-hidden="true">1</span><button type="button" class="step-plus" aria-label="Increase quantity">+</button></div>
              <button class="add-btn" data-add="k5">Add to order</button>
            </div>
          </article>
          <article class="menu-card" data-tags="gf" data-id="k6" data-name="Peach Cobbler Bar" data-price="4.75">
            <div class="card-top"><h3>Peach Cobbler Bar</h3><span class="card-price mono">$4.75</span></div>
            <p class="card-desc">Gluten-free oat crust, roasted Adams County peaches, brown sugar crumble.</p>
            <div class="card-tags"><span class="tag gf">Gluten-free</span></div>
            <div class="card-actions">
              <div class="stepper" data-stepper="k6"><button type="button" class="step-minus" aria-label="Decrease quantity">−</button><span aria-hidden="true">1</span><button type="button" class="step-plus" aria-label="Increase quantity">+</button></div>
              <button class="add-btn" data-add="k6">Add to order</button>
            </div>
          </article>
        </div>
        <p class="empty-note">No items match your current filters. Try clearing a filter above.</p>
      </div>

      <!-- LUNCH -->
      <div class="menu-panel" id="panel-lunch" role="tabpanel" aria-labelledby="tab-lunch" hidden>
        <div class="menu-grid">
          <article class="menu-card" data-tags="" data-id="l1" data-name="Cannon Club Sandwich" data-price="13.50">
            <div class="card-top"><h3>Cannon Club Sandwich</h3><span class="card-price mono">$13.50</span></div>
            <p class="card-desc">Roast turkey, applewood bacon, Adams County apple slaw, herb aioli, toasted sourdough.</p>
            <div class="card-tags"></div>
            <div class="card-actions">
              <div class="stepper" data-stepper="l1"><button type="button" class="step-minus" aria-label="Decrease quantity">−</button><span aria-hidden="true">1</span><button type="button" class="step-plus" aria-label="Increase quantity">+</button></div>
              <button class="add-btn" data-add="l1">Add to order</button>
            </div>
          </article>
          <article class="menu-card" data-tags="veg gf" data-id="l2" data-name="Roasted Beet &amp; Chevre Salad" data-price="12.75">
            <div class="card-top"><h3>Roasted Beet &amp; Chevre Salad</h3><span class="card-price mono">$12.75</span></div>
            <p class="card-desc">Local beets, goat cheese, candied walnuts, arugula, cider vinaigrette.</p>
            <div class="card-tags"><span class="tag veg">Vegetarian</span><span class="tag gf">Gluten-free</span></div>
            <div class="card-actions">
              <div class="stepper" data-stepper="l2"><button type="button" class="step-minus" aria-label="Decrease quantity">−</button><span aria-hidden="true">1</span><button type="button" class="step-plus" aria-label="Increase quantity">+</button></div>
              <button class="add-btn" data-add="l2">Add to order</button>
            </div>
          </article>
          <article class="menu-card" data-tags="vegan" data-id="l3" data-name="Roasted Veggie Grain Bowl" data-price="12.25">
            <div class="card-top"><h3>Roasted Veggie Grain Bowl</h3><span class="card-price mono">$12.25</span></div>
            <p class="card-desc">Farro, roasted squash, chickpeas, kale, pickled onion, tahini-lemon dressing.</p>
            <div class="card-tags"><span class="tag vgn">Vegan</span></div>
            <div class="card-actions">
              <div class="stepper" data-stepper="l3"><button type="button" class="step-minus" aria-label="Decrease quantity">−</button><span aria-hidden="true">1</span><button type="button" class="step-plus" aria-label="Increase quantity">+</button></div>
              <button class="add-btn" data-add="l3">Add to order</button>
            </div>
          </article>
          <article class="menu-card" data-tags="" data-id="l4" data-name="Cornfield Chicken Panini" data-price="12.95">
            <div class="card-top"><h3>Cornfield Chicken Panini</h3><span class="card-price mono">$12.95</span></div>
            <p class="card-desc">Grilled chicken, provolone, roasted peppers, basil pesto, pressed ciabatta.</p>
            <div class="card-tags"></div>
            <div class="card-actions">
              <div class="stepper" data-stepper="l4"><button type="button" class="step-minus" aria-label="Decrease quantity">−</button><span aria-hidden="true">1</span><button type="button" class="step-plus" aria-label="Increase quantity">+</button></div>
              <button class="add-btn" data-add="l4">Add to order</button>
            </div>
          </article>
          <article class="menu-card" data-tags="gf" data-id="l5" data-name="Harvest Tomato Soup Cup" data-price="6.50">
            <div class="card-top"><h3>Harvest Tomato Soup</h3><span class="card-price mono">$6.50</span></div>
            <p class="card-desc">Roasted local tomatoes, cream, basil oil. Served with a slice of grilled sourdough.</p>
            <div class="card-tags"><span class="tag gf">GF without bread</span></div>
            <div class="card-actions">
              <div class="stepper" data-stepper="l5"><button type="button" class="step-minus" aria-label="Decrease quantity">−</button><span aria-hidden="true">1</span><button type="button" class="step-plus" aria-label="Increase quantity">+</button></div>
              <button class="add-btn" data-add="l5">Add to order</button>
            </div>
          </article>
          <article class="menu-card" data-tags="veg" data-id="l6" data-name="Caprese Melt" data-price="11.75">
            <div class="card-top"><h3>Caprese Melt</h3><span class="card-price mono">$11.75</span></div>
            <p class="card-desc">Fresh mozzarella, heirloom tomato, basil, balsamic glaze, pressed focaccia.</p>
            <div class="card-tags"><span class="tag veg">Vegetarian</span></div>
            <div class="card-actions">
              <div class="stepper" data-stepper="l6"><button type="button" class="step-minus" aria-label="Decrease quantity">−</button><span aria-hidden="true">1</span><button type="button" class="step-plus" aria-label="Increase quantity">+</button></div>
              <button class="add-btn" data-add="l6">Add to order</button>
            </div>
          </article>
        </div>
        <p class="empty-note">No items match your current filters. Try clearing a filter above.</p>
      </div>

      <!-- DRINKS -->
      <div class="menu-panel" id="panel-drinks" role="tabpanel" aria-labelledby="tab-drinks" hidden>
        <div class="menu-grid">
          <article class="menu-card" data-tags="vegan gf" data-id="d1" data-name="Cannon Pour-Over" data-price="4.50">
            <div class="card-top"><h3>Cannon Pour-Over</h3><span class="card-price mono">$4.50</span></div>
            <p class="card-desc">Single-origin beans, roasted small-batch, brewed fresh to order.</p>
            <div class="card-tags"><span class="tag vgn">Vegan</span><span class="tag gf">Gluten-free</span></div>
            <div class="card-actions">
              <div class="stepper" data-stepper="d1"><button type="button" class="step-minus" aria-label="Decrease quantity">−</button><span aria-hidden="true">1</span><button type="button" class="step-plus" aria-label="Increase quantity">+</button></div>
              <button class="add-btn" data-add="d1">Add to order</button>
            </div>
          </article>
          <article class="menu-card" data-tags="veg gf" data-id="d2" data-name="Honey Lavender Latte" data-price="5.25">
            <div class="card-top"><h3>Honey Lavender Latte</h3><span class="card-price mono">$5.25</span></div>
            <p class="card-desc">Espresso, steamed whole milk, local honey, culinary lavender.</p>
            <div class="card-tags"><span class="tag veg">Vegetarian</span><span class="tag gf">Gluten-free</span></div>
            <div class="card-actions">
              <div class="stepper" data-stepper="d2"><button type="button" class="step-minus" aria-label="Decrease quantity">−</button><span aria-hidden="true">1</span><button type="button" class="step-plus" aria-label="Increase quantity">+</button></div>
              <button class="add-btn" data-add="d2">Add to order</button>
            </div>
          </article>
          <article class="menu-card" data-tags="vegan gf" data-id="d3" data-name="Iced Oat Milk Shaken Espresso" data-price="5.00">
            <div class="card-top"><h3>Iced Oat Shaken Espresso</h3><span class="card-price mono">$5.00</span></div>
            <p class="card-desc">Double espresso, shaken hard over ice, poured over oat milk with vanilla.</p>
            <div class="card-tags"><span class="tag vgn">Vegan</span><span class="tag gf">Gluten-free</span></div>
            <div class="card-actions">
              <div class="stepper" data-stepper="d3"><button type="button" class="step-minus" aria-label="Decrease quantity">−</button><span aria-hidden="true">1</span><button type="button" class="step-plus" aria-label="Increase quantity">+</button></div>
              <button class="add-btn" data-add="d3">Add to order</button>
            </div>
          </article>
          <article class="menu-card" data-tags="vegan gf" data-id="d4" data-name="Fresh Apple Cider" data-price="4.00">
            <div class="card-top"><h3>Fresh Apple Cider</h3><span class="card-price mono">$4.00</span></div>
            <p class="card-desc">Pressed at a local Adams County orchard, served chilled or warm with cinnamon.</p>
            <div class="card-tags"><span class="tag vgn">Vegan</span><span class="tag gf">Gluten-free</span></div>
            <div class="card-actions">
              <div class="stepper" data-stepper="d4"><button type="button" class="step-minus" aria-label="Decrease quantity">−</button><span aria-hidden="true">1</span><button type="button" class="step-plus" aria-label="Increase quantity">+</button></div>
              <button class="add-btn" data-add="d4">Add to order</button>
            </div>
          </article>
          <article class="menu-card" data-tags="veg" data-id="d5" data-name="Chai Latte" data-price="4.95">
            <div class="card-top"><h3>Chai Latte</h3><span class="card-price mono">$4.95</span></div>
            <p class="card-desc">House-steeped spiced chai, steamed whole milk, dusted cinnamon.</p>
            <div class="card-tags"><span class="tag veg">Vegetarian</span></div>
            <div class="card-actions">
              <div class="stepper" data-stepper="d5"><button type="button" class="step-minus" aria-label="Decrease quantity">−</button><span aria-hidden="true">1</span><button type="button" class="step-plus" aria-label="Increase quantity">+</button></div>
              <button class="add-btn" data-add="d5">Add to order</button>
            </div>
          </article>
          <article class="menu-card" data-tags="vegan gf" data-id="d6" data-name="Matcha Tonic" data-price="5.50">
            <div class="card-top"><h3>Matcha Tonic</h3><span class="card-price mono">$5.50</span></div>
            <p class="card-desc">Ceremonial matcha, fresh lime, tonic water, served over ice.</p>
            <div class="card-tags"><span class="tag vgn">Vegan</span><span class="tag gf">Gluten-free</span></div>
            <div class="card-actions">
              <div class="stepper" data-stepper="d6"><button type="button" class="step-minus" aria-label="Decrease quantity">−</button><span aria-hidden="true">1</span><button type="button" class="step-plus" aria-label="Increase quantity">+</button></div>
              <button class="add-btn" data-add="d6">Add to order</button>
            </div>
          </article>
        </div>
        <p class="empty-note">No items match your current filters. Try clearing a filter above.</p>
      </div>

    </div>
  </section>

  <!-- ORDER FAQ -->
  <section class="hours-section">
    <div class="container">
      <div class="section-head reveal">
        <span class="eyebrow">Ordering FAQ</span>
        <h2>Pickup, delivery &amp; timing</h2>
      </div>
      <div class="faq-list">
        <div class="faq-item reveal">
          <h3>Where do I pick up my order?</h3>
          <p>At our counter inside 7 Lincoln Square, Gettysburg, PA 17325 — right on the Square. We'll have your name and order number ready.</p>
        </div>
        <div class="faq-item reveal">
          <h3>How does delivery work?</h3>
          <p>Choose Delivery in your cart and we'll bring your order to addresses around downtown Gettysburg for a flat $3.50 fee. Estimated delivery times show at checkout.</p>
        </div>
        <div class="faq-item reveal">
          <h3>How long until my order is ready?</h3>
          <p>Most pickup orders are ready in about 12–20 minutes; delivery generally runs 35–50 minutes depending on the day. Your confirmation shows an estimated time.</p>
        </div>
        <div class="faq-item reveal">
          <h3>Is this a real checkout?</h3>
          <p>This site is a design concept, so checkout is a demo — no payment is processed and no order is actually sent. On a live site this connects to your payment and kitchen systems.</p>
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

<!-- ORDER PANEL -->
<div class="order-overlay" id="order-overlay"></div>
<aside class="order-panel" id="order-panel" role="dialog" aria-modal="true" aria-labelledby="order-panel-title" aria-hidden="true">
  <div class="order-header">
    <h2 id="order-panel-title">Your Order</h2>
    <button class="order-close" id="order-close" aria-label="Close order panel">
      <svg width="18" height="18" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2.4" stroke-linecap="round"><path d="M18 6 6 18M6 6l12 12"/></svg>
    </button>
  </div>

  <div class="order-body" id="order-body">
    <div class="order-empty" id="order-empty">
      <svg viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="1.5"><circle cx="9" cy="21" r="1"/><circle cx="20" cy="21" r="1"/><path d="M1 1h4l2.68 13.39a2 2 0 0 0 2 1.61h9.72a2 2 0 0 0 2-1.61L23 6H6"/></svg>
      <p>Your order is empty. Add something delicious from the menu.</p>
    </div>

    <div class="fulfillment-toggle" id="fulfillment-toggle" style="display:none;">
      <button type="button" data-mode="pickup" aria-pressed="true">Pickup</button>
      <button type="button" data-mode="delivery" aria-pressed="false">Delivery</button>
    </div>

    <div id="order-items" aria-live="polite"></div>
  </div>

  <div class="order-summary" id="order-summary" style="display:none;">
    <div class="summary-row"><span>Subtotal</span><span class="mono" id="sum-subtotal">$0.00</span></div>
    <div class="summary-row"><span>Tax (6%)</span><span class="mono" id="sum-tax">$0.00</span></div>
    <div class="summary-row" id="sum-delivery-row" style="display:none;"><span>Delivery fee</span><span class="mono" id="sum-delivery">$3.50</span></div>
    <div class="summary-row total"><span>Total</span><span class="mono" id="sum-total" aria-live="polite">$0.00</span></div>
    <button class="btn btn-primary checkout-btn" id="checkout-btn">Checkout (Demo)</button>
  </div>
</aside>

<div class="visually-hidden" aria-live="polite" id="cart-announcer"></div>
@endsection
