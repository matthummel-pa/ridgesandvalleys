{{-- Bespoke single-post sidebar: engagement + local-SEO side content. --}}
<aside class="rv-sidebar rv-post-sidebar" aria-label="{{ __('More resources', 'sage') }}">

  {{-- Quote CTA — same primary as the Journal closer. --}}
  <div class="rv-side-card rv-side-cta">
    <span class="rv-side-eyebrow">{{ __('Fixed price · no pressure', 'sage') }}</span>
    <h2 class="rv-side-title">{{ __('Ready to build yours?', 'sage') }}</h2>
    <p class="rv-side-text">{{ __('Tell me about the Gettysburg or Adams County business. I’ll come back with a fixed-scope idea — usually within a business day.', 'sage') }}</p>
    <a class="rv-btn rv-btn-primary rv-side-btn" href="{{ \App\cta_href(get_theme_mod('rv_cta_url', '/contact/')) }}">{{ __('Get a quote', 'sage') }}</a>
  </div>

  {{-- Free website tools (internal links) --}}
  <div class="rv-side-card">
    <h2 class="rv-side-title">{{ __('Free website tools', 'sage') }}</h2>
    <ul class="rv-side-links">
      <li><a href="{{ home_url('/website-grader/') }}">{{ __('Website Grader', 'sage') }}</a></li>
      <li><a href="{{ home_url('/local-seo-2/') }}">{{ __('Local SEO Scorecard', 'sage') }}</a></li>
      <li><a href="{{ home_url('/seo-checker/') }}">{{ __('SEO Checker', 'sage') }}</a></li>
      <li><a href="{{ home_url('/security-checker/') }}">{{ __('Security Checker', 'sage') }}</a></li>
      <li><a href="{{ home_url('/email-checker/') }}">{{ __('Email Deliverability Checker', 'sage') }}</a></li>
    </ul>
    <a class="rv-side-more" href="{{ home_url('/free-tools-2/') }}">{{ __('See all free tools', 'sage') }} &rarr;</a>
  </div>

  {{-- Popular guides (internal links to cornerstone posts) --}}
  <div class="rv-side-card">
    <h2 class="rv-side-title">{{ __('Popular guides', 'sage') }}</h2>
    <ul class="rv-side-guides">
      <li><a href="{{ home_url('/small-business-website-cost-pennsylvania/') }}">{{ __('What a small-business website costs in Pennsylvania', 'sage') }}</a></li>
      <li><a href="{{ home_url('/show-up-google-maps-gettysburg-business/') }}">{{ __('How to show up on Google Maps in Gettysburg', 'sage') }}</a></li>
      <li><a href="{{ home_url('/hire-local-web-designer-or-use-wix/') }}">{{ __('Hire a local web designer or use Wix?', 'sage') }}</a></li>
    </ul>
  </div>

  {{-- Areas we serve (local-SEO signal) --}}
  <div class="rv-side-card rv-side-area">
    <h2 class="rv-side-title">{{ __('Areas we serve', 'sage') }}</h2>
    <p class="rv-side-text">{{ __('Local web design for Gettysburg and across Adams County & South Central PA:', 'sage') }}</p>
    <ul class="rv-side-towns">
      @foreach (['Gettysburg','Hanover','Littlestown','New Oxford','McSherrystown','Biglerville','East Berlin','Fairfield','Bonneauville','Abbottstown','York Springs'] as $rvTown)
        <li>{{ $rvTown }}</li>
      @endforeach
    </ul>
    <a class="rv-side-more" href="{{ home_url('/contact/') }}">{{ __('Serving your town? Get a free quote', 'sage') }} &rarr;</a>
  </div>

  {{-- About / NAP (local-SEO + trust) --}}
  <div class="rv-side-card rv-side-nap">
    <h2 class="rv-side-title">{{ __('Ridges & Valleys Studio', 'sage') }}</h2>
    <p class="rv-side-text">{{ __('Family-owned Gettysburg web design — fast, accessible sites for local businesses. One fixed price, and you own everything.', 'sage') }}</p>
    <p class="rv-side-nap-line">Gettysburg, PA &middot; Adams County</p>
    <p class="rv-side-nap-line"><a href="tel:+12233408098">(223) 340-8098</a></p>
    <a class="rv-side-more" href="{{ home_url('/about/') }}">{{ __('About the studio', 'sage') }} &rarr;</a>
  </div>

</aside>
