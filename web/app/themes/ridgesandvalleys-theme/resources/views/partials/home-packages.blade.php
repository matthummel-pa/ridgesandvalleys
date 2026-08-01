{{-- PACKAGES --}}
<section class="rv-shell rv-band">
  {!! \App\eyebrow(\App\field('pkg_eyebrow', __('Clear scope. Fast build. No mystery.', 'sage'))) !!}
  <h2 class="rv-section-title">{{ \App\field('pkg_title', __('Three ways to', 'sage')) }} <em class="rv-accent">{{ \App\field('pkg_accent', __('start.', 'sage')) }}</em></h2>
  <div class="rv-packages" style="margin-top:2.25rem">
    @php($packages = [
      ['', \App\field('pkg1_name', __('Website Rescue', 'sage')), \App\field('pkg1_price', '$950'), '+', \App\field('pkg1_desc', __('Audit, cleanup, broken links, mobile, speed & SEO fixes.', 'sage'))],
      [\App\field('pkg2_flag', __('Most popular', 'sage')), \App\field('pkg2_name', __('Local Launch', 'sage')), \App\field('pkg2_price', '$2,750'), '+', \App\field('pkg2_desc', __('Up to 5 pages, local SEO, analytics, accessibility, one revision.', 'sage'))],
      ['', \App\field('pkg3_name', __('Growth Site', 'sage')), \App\field('pkg3_price', '$4,500'), '+', \App\field('pkg3_desc', __('8–12 pages, migration, booking or ecommerce, advanced forms.', 'sage'))],
      ['', \App\field('pkg4_name', __('Care & Grow', 'sage')), \App\field('pkg4_price', '$179'), '/mo', \App\field('pkg4_desc', __('Updates, backups, security, small changes, reporting.', 'sage'))],
    ])
    @foreach ($packages as $pk)
      <article class="rv-pkg {{ $pk[0] ? 'rv-pkg-feat' : '' }}">
        <span class="rv-stripe rv-stripe-thin" aria-hidden="true"></span>
        <div class="rv-pkg-in">
          @if ($pk[0])<span class="rv-pkg-flag">{{ $pk[0] }}</span>@endif
          <span class="rv-pkg-name">{{ $pk[1] }}</span>
          <span class="rv-pkg-price">{{ $pk[2] }}<span>{{ $pk[3] }}</span></span>
          <p class="rv-pkg-desc">{{ $pk[4] }}</p>
        </div>
      </article>
    @endforeach
  </div>
  <div style="margin-top:1.5rem"><a class="rv-btn rv-btn-ghost" href="{{ \App\cta_href(\App\field('pkg_cta_url', '/services/')) }}">{{ \App\field('pkg_cta', __('Compare all services', 'sage')) }} {!! \App\icon('arrow') !!}</a></div>
</section>
