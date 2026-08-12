{{-- Bespoke single-post sidebar: engagement + local-SEO side content. --}}
<aside class="rv-sidebar rv-post-sidebar" aria-label="{{ __('More resources', 'sage') }}">

  {{-- In this article (auto table of contents, JS-built from the post's H2s) --}}
  <nav class="rv-side-card rv-side-toc" id="rv-toc" aria-label="{{ __('In this article', 'sage') }}" hidden>
    <h2 class="rv-side-title">{{ __('In this article', 'sage') }}</h2>
    <ol class="rv-toc-list"></ol>
  </nav>

  {{-- Free audit CTA --}}
  <div class="rv-side-card rv-side-cta">
    <span class="rv-side-eyebrow">{{ get_theme_mod('rv_side_cta_eyebrow', __('Free · no pressure', 'sage')) }}</span>
    <h2 class="rv-side-title">{{ get_theme_mod('rv_side_cta_title', __('A second set of eyes on your site?', 'sage')) }}</h2>
    <p class="rv-side-text">{{ get_theme_mod('rv_side_cta_text', __("I'll record a free 5-minute video walkthrough of your website — the first things I'd fix. No pitch.", 'sage')) }}</p>
    <a class="rv-btn rv-btn-primary rv-side-btn" href="{{ home_url(get_theme_mod('rv_inline_cta_url', '/contact/')) }}">{{ get_theme_mod('rv_side_cta_btn', __('Get my free audit', 'sage')) }}</a>
  </div>

  {{-- Free website tools (internal links) --}}
  @php($rvSideTools = \App\mod_link_lines('rv_side_tools', [
    'Website Grader | /website-grader/',
    'Local SEO Scorecard | /local-seo-2/',
    'SEO Checker | /seo-checker/',
    'Security Checker | /security-checker/',
    'Email Deliverability Checker | /email-checker/',
  ]))
  <div class="rv-side-card">
    <h2 class="rv-side-title">{{ get_theme_mod('rv_side_tools_title', __('Free website tools', 'sage')) }}</h2>
    <ul class="rv-side-links">
      @foreach ($rvSideTools as $rvTool)
        <li><a href="{{ home_url($rvTool['url']) }}">{{ $rvTool['label'] }}</a></li>
      @endforeach
    </ul>
    <a class="rv-side-more" href="{{ home_url('/free-tools-2/') }}">{{ get_theme_mod('rv_side_tools_more', __('See all free tools', 'sage')) }} &rarr;</a>
  </div>

  {{-- Popular guides (internal links to cornerstone posts) --}}
  @php($rvSideGuides = \App\mod_link_lines('rv_side_guides', [
    'What a small-business website costs in Pennsylvania | /small-business-website-cost-pennsylvania/',
    'How to show up on Google Maps in Gettysburg | /show-up-google-maps-gettysburg-business/',
    'Hire a local web designer or use Wix? | /hire-local-web-designer-or-use-wix/',
  ]))
  <div class="rv-side-card">
    <h2 class="rv-side-title">{{ get_theme_mod('rv_side_guides_title', __('Popular guides', 'sage')) }}</h2>
    <ul class="rv-side-guides">
      @foreach ($rvSideGuides as $rvGuide)
        <li><a href="{{ home_url($rvGuide['url']) }}">{{ $rvGuide['label'] }}</a></li>
      @endforeach
    </ul>
  </div>

  {{-- Areas we serve (local-SEO signal) --}}
  @php($rvSideTowns = array_filter(array_map('trim', explode(',', (string) get_theme_mod('rv_side_towns', 'Gettysburg, Hanover, Littlestown, New Oxford, McSherrystown, Biglerville, East Berlin, Fairfield, Bonneauville, Abbottstown, York Springs')))))
  <div class="rv-side-card rv-side-area">
    <h2 class="rv-side-title">{{ get_theme_mod('rv_side_areas_title', __('Areas we serve', 'sage')) }}</h2>
    <p class="rv-side-text">{{ get_theme_mod('rv_side_areas_text', __('Local web design for Gettysburg and across Adams County & South Central PA:', 'sage')) }}</p>
    <ul class="rv-side-towns">
      @foreach ($rvSideTowns as $rvTown)
        <li>{{ $rvTown }}</li>
      @endforeach
    </ul>
    <a class="rv-side-more" href="{{ home_url('/contact/') }}">{{ get_theme_mod('rv_side_areas_link', __('Serving your town? Get a free quote', 'sage')) }} &rarr;</a>
  </div>

  {{-- About / NAP (local-SEO + trust) --}}
  @php($rvPhone = trim((string) get_theme_mod('rv_contact_phone', '223-340-8098')))
  @php($rvLocation = trim((string) get_theme_mod('rv_contact_location', 'Gettysburg, PA')))
  <div class="rv-side-card rv-side-nap">
    <h2 class="rv-side-title">{{ get_theme_mod('rv_side_nap_title', __('Ridges & Valleys Studio', 'sage')) }}</h2>
    <p class="rv-side-text">{{ get_theme_mod('rv_side_nap_text', __('Family-owned Gettysburg web design — fast, accessible sites for local businesses. One fixed price, and you own everything.', 'sage')) }}</p>
    @if ($rvLocation !== '')<p class="rv-side-nap-line">{{ $rvLocation }} &middot; Adams County</p>@endif
    @if ($rvPhone !== '')<p class="rv-side-nap-line"><a href="tel:+1{{ preg_replace('/\D+/', '', $rvPhone) }}">{{ $rvPhone }}</a></p>@endif
    <a class="rv-side-more" href="{{ home_url('/about/') }}">{{ get_theme_mod('rv_side_about_link', __('About the studio', 'sage')) }} &rarr;</a>
  </div>

</aside>

@verbatim
<script>
(function () {
  var toc = document.getElementById('rv-toc');
  if (!toc) return;
  var heads = document.querySelectorAll('.rv-content .rv-prose h2');
  if (heads.length < 2) return;
  var ol = toc.querySelector('.rv-toc-list');
  Array.prototype.forEach.call(heads, function (h, i) {
    if (!h.id) {
      var s = (h.textContent || '').toLowerCase().replace(/[^a-z0-9]+/g, '-').replace(/^-+|-+$/g, '').slice(0, 40);
      h.id = 'sec-' + (i + 1) + (s ? '-' + s : '');
    }
    var li = document.createElement('li');
    var a = document.createElement('a');
    a.href = '#' + h.id;
    a.textContent = h.textContent;
    li.appendChild(a);
    ol.appendChild(li);
  });
  toc.hidden = false;
})();
</script>
@endverbatim
