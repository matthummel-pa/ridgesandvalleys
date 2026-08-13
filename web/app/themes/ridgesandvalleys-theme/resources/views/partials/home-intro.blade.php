{{-- INTRO — Who we are / How we work. Custom fields, not the page body. --}}
<section class="rv-band rv-home-intro" aria-labelledby="rv-intro-heading">
  <div class="rv-shell">
    {!! \App\eyebrow(\App\field('intro_eyebrow', __('A local studio', 'sage'))) !!}
    <h2 id="rv-intro-heading" class="rv-section-title">{{ \App\field('intro_title', __('Gettysburg web design that', 'sage')) }} <em class="rv-accent">{{ \App\field('intro_accent', __('brings in customers.', 'sage')) }}</em></h2>
    <div class="rv-intro-pair">
      <article class="rv-card rv-intro-card">
        <span class="rv-stripe rv-stripe-thin" aria-hidden="true"></span>
        <h3 class="rv-intro-card-title">{{ \App\field('intro_who_title', __('Who we are', 'sage')) }}</h3>
        <p class="rv-intro-card-text">{{ \App\field('intro_who_text', __('Ridges & Valleys Studio builds fast, accessible WordPress websites for small businesses across Gettysburg, Adams County, and South Central Pennsylvania. Led by Matt Hummel, a local developer with more than 15 years of experience, every site is mobile-first, built toward WCAG 2.2 AA, optimized for local search, and fully owned by you.', 'sage')) }}</p>
        <ul class="rv-intro-points">
          @foreach (\App\field_lines('intro_who_points', \App\home_intro_point_defaults()) as $point)
            <li>{{ $point }}</li>
          @endforeach
        </ul>
      </article>
      <article class="rv-card rv-intro-card">
        <span class="rv-stripe rv-stripe-thin" aria-hidden="true"></span>
        <h3 class="rv-intro-card-title">{{ \App\field('intro_how_title', __('How we work', 'sage')) }}</h3>
        <ol class="rv-intro-steps">
          @foreach (\App\field_rows('intro_steps', \App\home_intro_step_defaults()) as $i => $step)
            <li>
              <span class="rv-intro-step-n" aria-hidden="true">{{ str_pad((string) ($i + 1), 2, '0', STR_PAD_LEFT) }}</span>
              <div>
                <strong>{{ $step['title'] ?? '' }}</strong>
                @if (trim(\App\strip_field_markers((string) ($step['text'] ?? ''))) !== '')
                  <span>{{ $step['text'] }}</span>
                @endif
              </div>
            </li>
          @endforeach
        </ol>
        <p class="rv-intro-card-text">{{ \App\field('intro_how_text', __('Clear pricing, straightforward communication, and a site designed to turn visitors into calls.', 'sage')) }}</p>
        <div class="rv-intro-links">
          <a class="rv-btn rv-btn-ghost" href="{{ \App\services_href(\App\field('intro_link1_url', \App\services_path())) }}">{{ \App\field('intro_link1', __('See web design services', 'sage')) }} {!! \App\icon('arrow') !!}</a>
          <a class="rv-btn rv-btn-primary" href="{{ \App\cta_href(\App\field('intro_link2_url', '/contact/')) }}">{{ \App\field('intro_link2', __('Request a quote', 'sage')) }}</a>
        </div>
      </article>
    </div>
  </div>
</section>
