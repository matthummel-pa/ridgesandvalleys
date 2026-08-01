{{-- TOWNS SERVED (local SEO) --}}
<section class="rv-band rv-band-alt">
  <div class="rv-shell">
    {!! \App\eyebrow(\App\field('htowns_eyebrow', __('Local, in the real sense', 'sage'))) !!}
    <h2 class="rv-section-title">{{ \App\field('htowns_title', __('Serving Gettysburg & the', 'sage')) }} <em class="rv-accent">{{ \App\field('htowns_accent', __('surrounding', 'sage')) }}</em> {{ \App\field('htowns_title_end', __('towns.', 'sage')) }}</h2>
    <p class="rv-page-intro">{{ \App\field('htowns_intro', __('If your customers are in Adams County or nearby South Central PA, they can find you. Ask about a town-specific page for the places you serve most.', 'sage')) }}</p>
    <div class="rv-chips" style="margin-top:1.25rem">
      @foreach (\App\field_lines('htowns_list', ['Gettysburg', 'Hanover', 'Littlestown', 'New Oxford', 'McSherrystown', 'Biglerville', 'East Berlin', 'Fairfield', 'Cashtown', 'Aspers', 'Abbottstown', 'Bonneauville', 'Carlisle', 'Chambersburg', 'York']) as $town)
        <span class="rv-mchip">{{ $town }}</span>
      @endforeach
    </div>
  </div>
</section>
