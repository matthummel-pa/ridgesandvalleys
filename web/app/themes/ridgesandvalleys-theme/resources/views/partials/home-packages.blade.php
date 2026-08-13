{{-- PACKAGES — same 3+1 cards as the services page (one source of truth) --}}
@php($pkgId = \App\services_page_id() ?: (\App\home_page_id() ?: null))
<section class="rv-shell rv-band">
  {!! \App\eyebrow(\App\field('pkg_eyebrow', __('Clear scope. Fast build. No mystery.', 'sage'))) !!}
  <h2 class="rv-section-title">{{ \App\field('pkg_title', __('Three ways to', 'sage')) }} <em class="rv-accent">{{ \App\field('pkg_accent', __('start.', 'sage')) }}</em></h2>
  @include('partials.package-plans', [
    'projects' => \App\svc_project_packages($pkgId),
    'care'     => \App\svc_care_packages($pkgId),
  ])
  <div style="margin-top:1.5rem"><a class="rv-btn rv-btn-ghost" href="{{ \App\cta_href(\App\field('pkg_compare_url', '/gettysburg-web-design-services/#packages')) }}">{{ \App\field('pkg_cta', __('Compare all services', 'sage')) }} {!! \App\icon('arrow') !!}</a></div>
</section>
