{{--
  Template Name: Areas We Serve
--}}

@extends('layouts.app')

@push('head')
<script type="application/ld+json">
{
  "@@context": "https://schema.org",
  "@@type": "RealEstateAgent",
  "@@id": "{{ home_url('/') }}#business",
  "name": "Ridgeline Realty",
  "description": "Residential real estate agency serving Gettysburg and the towns and townships of Adams County, Pennsylvania.",
  "image": "{{ home_url('/preview.jpg/') }}",
  "url": "{{ home_url('/areas/') }}",
  "telephone": "+1-717-555-0210",
  "email": "info@@ridgelinerealty.test",
  "priceRange": "$$",
  "address": {
    "@@type": "PostalAddress",
    "streetAddress": "210 Chambersburg Road",
    "addressLocality": "Gettysburg",
    "addressRegion": "PA",
    "postalCode": "17325",
    "addressCountry": "US"
  },
  "geo": { "@@type": "GeoCoordinates", "latitude": 39.8309, "longitude": -77.2311 },
  "openingHoursSpecification": [
    { "@@type": "OpeningHoursSpecification", "dayOfWeek": ["Monday","Tuesday","Wednesday","Thursday","Friday"], "opens": "09:00", "closes": "18:00" },
    { "@@type": "OpeningHoursSpecification", "dayOfWeek": "Saturday", "opens": "10:00", "closes": "15:00" }
  ],
  "areaServed": [
    { "@@type": "City", "name": "Gettysburg, PA" },
    { "@@type": "City", "name": "Biglerville, PA" },
    { "@@type": "City", "name": "Littlestown, PA" },
    { "@@type": "City", "name": "New Oxford, PA" },
    { "@@type": "City", "name": "McSherrystown, PA" },
    { "@@type": "City", "name": "Fairfield, PA" },
    { "@@type": "Place", "name": "Cashtown, PA" },
    { "@@type": "Place", "name": "Straban Township, PA" },
    { "@@type": "Place", "name": "Cumberland Township, PA" },
    { "@@type": "Place", "name": "Mount Joy Township, PA" },
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
    { "@@type": "ListItem", "position": 2, "name": "Areas We Serve", "item": "{{ home_url('/areas/') }}" }
  ]
}
</script>
@endpush

@section('content')
<a href="#main" class="skip-link">Skip to main content</a>

<header class="site-header">
  <div class="nav-wrap">
    <a href="{{ home_url('/') }}" class="brand" aria-label="Ridgeline Realty home">
      <span class="brand-mark" aria-hidden="true">
        <svg viewBox="0 0 24 24" fill="none" stroke="#0c1c2c" stroke-width="2" stroke-linecap="round" stroke-linejoin="round"><path d="M3 20l5-9 4 6 3-5 6 8"/><path d="M3 20h18"/></svg>
      </span>
      <span class="brand-text"><strong>Ridgeline Realty</strong><span>Gettysburg &amp; Adams County</span></span>
    </a>

    <nav class="primary-nav" aria-label="Primary">
      <ul>
        <li><a href="{{ home_url('/listings/') }}">Listings</a></li>
        <li><a href="{{ home_url('/areas/') }}" class="is-active" aria-current="page">Areas We Serve</a></li>
        <li><a href="{{ home_url('/sell/') }}">Sell Your Home</a></li>
        <li><a href="{{ home_url('/agents/') }}">Our Agents</a></li>
        <li><a href="{{ home_url('/contact/') }}">Contact</a></li>
      </ul>
    </nav>

    <div class="header-cta">
      <a class="header-phone" href="tel:+17175550210">(717) 555-0210</a>
      <a class="btn btn-primary btn-sm" href="{{ home_url('/contact/') }}#schedule">Schedule a Showing</a>
    </div>

    <button class="hamburger" id="hamburgerBtn" aria-label="Open menu" aria-expanded="false" aria-controls="mobileNav">
      <span></span>
    </button>
  </div>

  <nav class="mobile-nav" id="mobileNav" aria-label="Mobile">
    <a href="{{ home_url('/listings/') }}">Listings</a>
    <a href="{{ home_url('/areas/') }}" class="is-active" aria-current="page">Areas We Serve</a>
    <a href="{{ home_url('/sell/') }}">Sell Your Home</a>
    <a href="{{ home_url('/agents/') }}">Our Agents</a>
    <a href="{{ home_url('/contact/') }}">Contact</a>
    <div class="mobile-cta">
      <a class="btn btn-primary" style="width:100%" href="{{ home_url('/contact/') }}#schedule">Schedule a Showing</a>
    </div>
  </nav>
</header>

<nav class="breadcrumb" aria-label="Breadcrumb">
  <div class="container">
    <ol>
      <li><a href="{{ home_url('/') }}">Home</a></li>
      <li class="sep" aria-hidden="true">/</li>
      <li aria-current="page">Areas We Serve</li>
    </ol>
  </div>
</nav>

<main id="main">

  <!-- ===== PAGE HERO ===== -->
  <section class="hero hero-compact">
    <div class="container hero-inner">
      <span class="eyebrow">Gettysburg &amp; Adams County, PA</span>
      <h1>The communities we call <em>home</em></h1>
      <p class="hero-sub">Ridgeline Realty works the whole of Adams County — from the historic core of Gettysburg to the orchard towns, the farm townships, and the crossroads villages in between. Here's a neighbor's-eye view of where we sell.</p>
    </div>
  </section>

  <!-- ===== INTRO / LANDMARKS PROSE ===== -->
  <section>
    <div class="container">
      <div class="prose reveal">
        <h2>Living in Gettysburg, Pennsylvania</h2>
        <p>Gettysburg sits at the center of Adams County, a borough of about 7,500 people wrapped around <strong>Lincoln Square</strong>, where Chambersburg, Carlisle, Baltimore, and York Streets meet. Our office is at 210 Chambersburg Road, a short drive from the square and minutes from the <strong>Gettysburg National Military Park</strong>, whose 6,000 acres border town on nearly every side. For many buyers, that park is the deciding factor: a home here means morning walks past <strong>Seminary Ridge</strong>, weekend drives out to <strong>Little Round Top</strong>, and a downtown that never fully empties because visitors come year-round.</p>
        <p>The historic district is the heart of the in-town market. Along <strong>Baltimore Street</strong> and <strong>Steinwehr Avenue</strong> you'll find Federal townhomes, brick rowhouses, and Victorians within walking distance of the <strong>David Wills House</strong>, the Wills House being where Lincoln finished the Gettysburg Address. Buying or renovating in this district usually means a Certificate of Appropriateness from the Historic Architectural Review Board — a process our agents have walked clients through dozens of times. A few blocks north, <strong>Gettysburg College</strong> anchors a leafy neighborhood of porches and shade trees that stays in steady demand from faculty, staff, and families who want to walk to town.</p>

        <h2>Getting here, parking, and the lay of the land</h2>
        <p>Gettysburg is easy to reach. From the north or south, <strong>US-15</strong> runs right past the east edge of town; take the Steinwehr Avenue / Business 15 exit and you're at the Visitor Center in a couple of minutes. From the east or west, <strong>US-30 (Lincoln Highway / Chambersburg Street)</strong> carries you straight into Lincoln Square. Downtown parking is a mix of metered on-street spaces and the Racehorse Alley parking garage off Stratton Street; most of our historic-district listings are an easy walk from both. West of town, US-30 climbs toward <strong>Cashtown</strong> and the South Mountain gap; north, PA-34 rolls up through apple country to <strong>Biglerville</strong>; and southeast, PA-97 and PA-116 thread out toward <strong>Littlestown</strong>, <strong>McSherrystown</strong>, and the Hanover line. Knowing those roads — and the commute times they really produce — is half of what we do when we match a family to a town.</p>
        <p>One more local landmark worth the drive: <strong>Sachs Covered Bridge</strong>, tucked along Waterworks Road southwest of town in Cumberland Township. It's a favorite of ours for showing buyers just how quickly Gettysburg gives way to open, rolling countryside. Below, a closer look at each community we serve.</p>
      </div>
    </div>
  </section>

  <!-- ===== AREA CARDS ===== -->
  <section class="listings">
    <div class="container">
      <div class="section-head reveal">
        <span class="eyebrow">Town by Town</span>
        <h2>Where Ridgeline sells</h2>
        <p>Every Adams County community has its own feel, price point, and commute. Here's how we'd describe each one to a friend.</p>
      </div>

      <div class="area-grid">
        <article class="area-card reveal">
          <span class="area-kicker">Borough · 17325</span>
          <h3>Gettysburg</h3>
          <p>The historic core: Federal townhomes, brick rowhouses, and Victorians along Baltimore Street and near Gettysburg College. Walkable to Lincoln Square, restaurants, and the National Military Park. Expect historic-district review on many properties — and steady demand from buyers who want to live in the middle of it all.</p>
          <a class="area-tag" href="{{ home_url('/listings/') }}">See Gettysburg listings &rarr;</a>
        </article>

        <article class="area-card reveal">
          <span class="area-kicker">Township · adjoins Gettysburg</span>
          <h3>Cumberland Township</h3>
          <p>The ring of countryside wrapping the borough's south and west, home to Sachs Covered Bridge and much of the battlefield's edge. New-construction subdivisions on one- and two-acre lots sit minutes from downtown, making it a favorite for buyers who want space without a long drive.</p>
          <a class="area-tag" href="{{ home_url('/listings/') }}">Explore Cumberland Twp &rarr;</a>
        </article>

        <article class="area-card reveal">
          <span class="area-kicker">Township · northeast</span>
          <h3>Straban Township</h3>
          <p>Just northeast of town along US-30 and PA-394, Straban mixes working farmland, buildable acreage, and newer homes near the outlets and the hospital corridor. A practical pick for buyers who want land and an easy shot into both Gettysburg and York County.</p>
          <a class="area-tag" href="{{ home_url('/listings/') }}">Browse Straban land &amp; homes &rarr;</a>
        </article>

        <article class="area-card reveal">
          <span class="area-kicker">Township · southwest</span>
          <h3>Mount Joy Township</h3>
          <p>Rolling ground southeast of Gettysburg toward the Two Taverns area, Mount Joy is farms, ranch homes, and quiet lots off PA-97. Buyers come here for elbow room, lower density, and a straightforward commute to town or down toward Littlestown.</p>
          <a class="area-tag" href="{{ home_url('/contact/') }}">Ask about Mount Joy &rarr;</a>
        </article>

        <article class="area-card reveal">
          <span class="area-kicker">Borough · 17307 · north</span>
          <h3>Biglerville</h3>
          <p>The capital of Adams County apple country, ten minutes north of Gettysburg on PA-34. Home to the National Apple Museum and surrounded by orchards, Biglerville offers small-town living, newer craftsman builds, and a tight, family-friendly school community.</p>
          <a class="area-tag" href="{{ home_url('/listings/') }}">See Biglerville homes &rarr;</a>
        </article>

        <article class="area-card reveal">
          <span class="area-kicker">Borough · 17340 · southeast</span>
          <h3>Littlestown</h3>
          <p>A growing borough on PA-97 near the Maryland line, popular with commuters heading toward Westminster and the Baltimore suburbs. Littlestown pairs affordable single-family homes and newer developments with a walkable Main Street and its own school district.</p>
          <a class="area-tag" href="{{ home_url('/contact/') }}">Ask about Littlestown &rarr;</a>
        </article>

        <article class="area-card reveal">
          <span class="area-kicker">Borough · 17350 · east</span>
          <h3>New Oxford</h3>
          <p>Known for its circular town square and antique shops, New Oxford sits east of Gettysburg along US-30 toward Hanover. It's a charming, convenient base for buyers who want small-town character with quick access to Hanover's shopping and jobs.</p>
          <a class="area-tag" href="{{ home_url('/contact/') }}">Ask about New Oxford &rarr;</a>
        </article>

        <article class="area-card reveal">
          <span class="area-kicker">Borough · 17344 · southeast</span>
          <h3>McSherrystown</h3>
          <p>A compact, close-knit borough near the Hanover line, McSherrystown offers some of the most attainable price points in the county — twins, rowhomes, and modest single-family houses on tidy streets, within the Conewago Valley school district.</p>
          <a class="area-tag" href="{{ home_url('/contact/') }}">Ask about McSherrystown &rarr;</a>
        </article>

        <article class="area-card reveal">
          <span class="area-kicker">Borough · 17320 · west</span>
          <h3>Fairfield</h3>
          <p>A small borough southwest of Gettysburg on PA-116, gateway to the Carroll Valley and Ski Liberty. Fairfield draws buyers who want mountain scenery, larger lots, and a rural pace while staying inside the Fairfield Area School District, twenty minutes from town.</p>
          <a class="area-tag" href="{{ home_url('/contact/') }}">Ask about Fairfield &rarr;</a>
        </article>

        <article class="area-card reveal">
          <span class="area-kicker">Village · 17310 · west</span>
          <h3>Cashtown</h3>
          <p>A historic crossroads village on US-30 west of town, at the foot of South Mountain and home to the well-known Cashtown Inn. Expect farmhouses, acreage, and cabin-style properties for buyers chasing views, quiet, and a true country setting.</p>
          <a class="area-tag" href="{{ home_url('/listings/') }}">See Cashtown-area homes &rarr;</a>
        </article>
      </div>
    </div>
  </section>

  <!-- ===== FAQ ===== -->
  <section>
    <div class="container">
      <div class="section-head reveal">
        <span class="eyebrow">Good to Know</span>
        <h2>Adams County buyer questions</h2>
      </div>
      <div class="faq-list">
        <div class="faq-item reveal">
          <h3>How far is Gettysburg from the major highways?</h3>
          <p>US-15 runs along the east side of the borough (a couple of minutes to downtown via the Steinwehr Avenue exit), and US-30 passes straight through Lincoln Square. Harrisburg is about 40 minutes north on US-15, and the Maryland line is roughly 15 minutes south.</p>
        </div>
        <div class="faq-item reveal">
          <h3>What should I know about buying in the historic district?</h3>
          <p>Exterior changes to homes in Gettysburg's historic district typically need a Certificate of Appropriateness from the Historic Architectural Review Board. It's very manageable — our agents guide buyers and sellers through the process on nearly every in-town transaction.</p>
        </div>
        <div class="faq-item reveal">
          <h3>Which towns are best for commuters?</h3>
          <p>Littlestown, McSherrystown, and New Oxford are popular with buyers heading toward Hanover, York, or the Baltimore suburbs, while Cumberland and Straban Townships keep you within minutes of downtown Gettysburg. Tell us your commute and we'll map realistic drive times.</p>
        </div>
        <div class="faq-item reveal">
          <h3>Do you help with land and new construction?</h3>
          <p>Yes. We list buildable lots in the townships and work with buyers on new construction in areas like Cumberland Township and around Biglerville. We can also refer trusted local builders.</p>
        </div>
      </div>
    </div>
  </section>

  <!-- ===== CTA BAND ===== -->
  <section class="cta-band">
    <div class="container">
      <span class="eyebrow">Not Sure Where to Look?</span>
      <h2>Let's find your corner of Adams County</h2>
      <p>Tell us how you live — the commute, the schools, the porch you're picturing — and we'll point you to the right towns and the right homes.</p>
      <div class="cta-actions">
        <a href="{{ home_url('/contact/') }}" class="btn btn-primary">Talk to an Agent</a>
        <a href="{{ home_url('/listings/') }}" class="btn btn-outline">Browse Listings</a>
      </div>
    </div>
  </section>

</main>

<!-- ===== FOOTER ===== -->
<footer class="site-footer">
  <div class="container">
    <div class="footer-grid">
      <div class="footer-brand">
        <a href="{{ home_url('/') }}" class="brand">
          <span class="brand-mark" aria-hidden="true">
            <svg viewBox="0 0 24 24" fill="none" stroke="#0c1c2c" stroke-width="2" stroke-linecap="round" stroke-linejoin="round"><path d="M3 20l5-9 4 6 3-5 6 8"/><path d="M3 20h18"/></svg>
          </span>
          <span class="brand-text"><strong>Ridgeline Realty</strong><span>Gettysburg &amp; Adams County</span></span>
        </a>
        <p>Locally owned and operated, connecting Adams County families with in-town homes, historic properties, and new builds since 2016.</p>
        <div class="social-row">
          <a href="#" aria-label="Ridgeline Realty on Facebook"><svg viewBox="0 0 24 24" stroke-width="1.8"><path d="M14 9h3V6h-3a4 4 0 0 0-4 4v2H8v3h2v6h3v-6h3l1-3h-4v-1a1 1 0 0 1 1-2z"/></svg></a>
          <a href="#" aria-label="Ridgeline Realty on Instagram"><svg viewBox="0 0 24 24" stroke-width="1.8"><rect x="3" y="3" width="18" height="18" rx="5"/><circle cx="12" cy="12" r="4"/><circle cx="17.5" cy="6.5" r="1"/></svg></a>
          <a href="#" aria-label="Ridgeline Realty on LinkedIn"><svg viewBox="0 0 24 24" stroke-width="1.8"><rect x="3" y="3" width="18" height="18" rx="3"/><path d="M7 10v7"/><path d="M7 7v.01"/><path d="M11 17v-4a2 2 0 0 1 4 0v4"/><path d="M11 13v4"/></svg></a>
        </div>
      </div>

      <div class="footer-col">
        <h4>Office</h4>
        <address>
          Ridgeline Realty<br>
          210 Chambersburg Road<br>
          Gettysburg, PA 17325<br>
          <a href="tel:+17175550210">(717) 555-0210</a><br>
          <a href="mailto:info@@ridgelinerealty.test">info@@ridgelinerealty.test</a>
        </address>
      </div>

      <div class="footer-col">
        <h4>Hours</h4>
        <address>
          Mon–Fri: 9:00am–6:00pm<br>
          Sat: 10:00am–3:00pm<br>
          Sun: By appointment
        </address>
        <h4 style="margin-top:22px;">Quick Links</h4>
        <ul>
          <li><a href="{{ home_url('/listings/') }}">Listings</a></li>
          <li><a href="{{ home_url('/sell/') }}">Sell Your Home</a></li>
          <li><a href="{{ home_url('/contact/') }}">Contact</a></li>
        </ul>
      </div>

      <div class="footer-col">
        <h4>Explore</h4>
        <ul>
          <li><a href="{{ home_url('/') }}">Home</a></li>
          <li><a href="{{ home_url('/listings/') }}">Listings</a></li>
          <li><a href="{{ home_url('/areas/') }}">Areas We Serve</a></li>
          <li><a href="{{ home_url('/agents/') }}">Our Agents</a></li>
          <li><a href="{{ home_url('/sell/') }}">Sell Your Home</a></li>
          <li><a href="{{ home_url('/contact/') }}">Contact</a></li>
        </ul>
      </div>
    </div>

    <p class="footer-service-note">Proudly serving Gettysburg plus Biglerville, Littlestown, New Oxford, McSherrystown, Fairfield, Cashtown, and the surrounding Adams County townships, Pennsylvania.</p>

    <div class="footer-bottom">
      <div class="equal-housing">
        <svg viewBox="0 0 24 24" stroke-width="1.8"><path d="M3 11l9-7 9 7"/><path d="M5 10v10h14V10"/><path d="M9 20v-6h6v6"/></svg>
        <span>Equal Housing Opportunity</span>
      </div>
      <p>&copy; <span data-current-year>2026</span> Ridgeline Realty. Design concept by Ridges &amp; Valleys Studio — a Gettysburg, PA web studio — created to show a prospective client their future site. Not a licensed real-estate brokerage. All listings, prices, and testimonials are illustrative.</p>
    </div>
  </div>
</footer>

<!-- ===== CHAT WIDGET ===== -->
<div class="chat-widget">
  <div class="chat-panel" id="chatPanel">
    <div class="chat-head">
      <div><strong>Ridgeline Assistant</strong><br><span>Usually replies in minutes</span></div>
      <button class="chat-close" id="chatCloseBtn" aria-label="Close chat">&times;</button>
    </div>
    <div class="chat-body" id="chatBody">
      <div class="chat-msg">Hi! I'm the Ridgeline assistant (demo). Ask me something below.</div>
    </div>
    <div class="chat-quick">
      <button type="button" data-reply="worth">What's my home worth?</button>
      <button type="button" data-reply="showing">Schedule a showing</button>
      <button type="button" data-reply="agent">Talk to an agent</button>
      <button type="button" data-reply="areas">Areas you serve?</button>
    </div>
  </div>
  <button class="chat-toggle" id="chatToggleBtn" aria-label="Open chat assistant" aria-expanded="false">
    <svg viewBox="0 0 24 24"><path d="M21 11.5a8.38 8.38 0 0 1-.9 3.8 8.5 8.5 0 0 1-7.6 4.7 8.38 8.38 0 0 1-3.8-.9L3 21l1.9-5.7a8.38 8.38 0 0 1-.9-3.8 8.5 8.5 0 0 1 4.7-7.6 8.38 8.38 0 0 1 3.8-.9h.5a8.48 8.48 0 0 1 8 8v.5z"/></svg>
  </button>
</div>

<a href="#" class="concept-badge">Concept &middot; Ridges &amp; Valleys Studio</a>
@endsection
