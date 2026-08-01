{{-- ROOTED / LOCAL (split with photo) --}}
<section class="rv-shell rv-band">
  <div class="rv-split rv-split-reverse">
    <div class="rv-split-media rv-media-photo">
      <img src="{{ \App\field('rooted_img') ?: \App\stock_image('rooted') }}" alt="{{ __('1935 aerial photograph of Gettysburg set among the farmland and ridges of Adams County', 'sage') }}" loading="lazy" onerror="this.style.display='none'">
    </div>
    <div class="rv-split-body">
      {!! \App\eyebrow(\App\field('rooted_eyebrow', __('Rooted in the ridges & valleys', 'sage'))) !!}
      <h2 class="rv-section-title" style="margin-top:.3rem">{{ \App\field('rooted_title', __('Built here.', 'sage')) }} <em class="rv-accent">{{ \App\field('rooted_accent', __('Supported', 'sage')) }}</em> {{ __('here.', 'sage') }}</h2>
      <p class="rv-feature-text" style="margin-top:.75rem">{{ \App\field('rooted_text', __('From the Cumberland Valley to South Mountain, Michaux, and the fields around Gettysburg — this is home. I build for the businesses that make this place what it is, and I\'m proud of the history that comes with the address.', 'sage')) }}</p>
      <div class="rv-chips">
        @php($rootedChips = array_filter(array_map('trim', explode(',', \App\field('rooted_chips', 'Gettysburg, Adams County, Cumberland Valley, South Mountain, Michaux')))))
        @foreach ($rootedChips as $place)
          <span class="rv-mchip">{{ $place }}</span>
        @endforeach
      </div>
    </div>
  </div>
</section>
