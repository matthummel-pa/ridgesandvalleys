{{-- partials/home/work-bento.blade.php
     Featured builds. Pulls the `projects` custom post type (featured first);
     falls back to a static array so the homepage renders before any projects
     exist. Bento spans are assigned by position to recreate the layout. --}}
@php
  // Bento layout recipe, applied in order to whatever items we render.
  $layout = [
    ['span' => 'span 4', 'row' => 'span 2', 'bg' => 'linear-gradient(135deg,#6C4CF1 0%,#FF4D9D 55%,#FF7A3D 100%)', 'fg' => '#fff',     'big' => true,  'stripe' => true],
    ['span' => 'span 2', 'row' => 'span 2', 'bg' => '#FF7A3D', 'fg' => '#17151F', 'big' => false, 'stripe' => false],
    ['span' => 'span 3', 'row' => 'span 1', 'bg' => '#37E29A', 'fg' => '#17151F', 'big' => false, 'stripe' => false],
    ['span' => 'span 3', 'row' => 'span 1', 'bg' => '#17151F', 'fg' => '#fff',     'big' => false, 'stripe' => false],
  ];

  // Try the CPT first (featured projects, then most recent), capped at 4.
  $builds = [];
  if (post_type_exists('projects')) {
      $q = new \WP_Query([
          'post_type'      => 'projects',
          'posts_per_page' => 4,
          'post_status'    => 'publish',
          'meta_key'       => '_prt_featured',
          'orderby'        => ['meta_value_num' => 'DESC', 'date' => 'DESC'],
      ]);
      foreach ($q->posts as $p) {
          $builds[] = [
              'title' => get_the_title($p),
              'label' => strtoupper(get_post_meta($p->ID, '_prt_eyebrow', true) ?: 'PROJECT'),
              'desc'  => wp_trim_words(get_the_excerpt($p), 18),
              'url'   => get_permalink($p),
              'blank' => false,
          ];
      }
      wp_reset_postdata();
  }

  // Fallback: real open-source builds.
  if (empty($builds)) {
      $builds = [
          ['title' => 'pressroot',     'label' => 'WORDPRESS · SAGE 11 (ROOTS)', 'desc' => 'A premium Sage 11 theme — Blade templates, a deep Customizer framework & a Gutenberg block + pattern library.', 'url' => 'https://github.com/matthummel-pa/pressroot', 'blank' => true],
          ['title' => 'tocflow',              'label' => 'WORDPRESS PLUGIN',             'desc' => 'Accessible, server-rendered table-of-contents block.', 'url' => 'https://github.com/matthummel-pa/tocflow', 'blank' => true],
          ['title' => 'keepary',              'label' => 'FULL-STACK APP',               'desc' => '', 'url' => 'https://github.com/matthummel-pa/keepary', 'blank' => true],
          ['title' => 'matthummel-portfolio', 'label' => 'HEADLESS · NEXT.JS',           'desc' => '', 'url' => 'https://github.com/matthummel-pa/matthummel-portfolio', 'blank' => true],
      ];
  }

  $allLink = get_post_type_archive_link('projects') ?: '#work';
@endphp

<section id="work" class="prt-wrap" style="padding-top:80px; padding-bottom:30px;">
  <div style="display:flex; align-items:flex-end; justify-content:space-between; flex-wrap:wrap; gap:12px; margin-bottom:36px;">
    <div>
      <h2 style="font-family:var(--font-display); font-weight:800; font-size:clamp(34px,4vw,46px); letter-spacing:-.025em; margin:0 0 8px; color:var(--color-h2, var(--color-ink));">Selected builds</h2>
      <p style="margin:0; font-size:16px; color:#7C75A8;">Real, open-source code you can read line by line — themes, plugins &amp; apps.</p>
    </div>
    <a href="{{ $allLink }}" style="text-decoration:none; font-weight:700; color:#6C4CF1; font-size:16px;">View all projects →</a>
  </div>
  <div class="prt-bento" style="display:grid; grid-template-columns:repeat(6,1fr); grid-auto-rows:200px; gap:18px;">
    @foreach($builds as $i => $b)
      @php $L = $layout[$i % count($layout)]; @endphp
      <a href="{{ $b['url'] }}" @if(!empty($b['blank'])) target="_blank" rel="noopener" @endif class="prt-lift" style="text-decoration:none; grid-column:{{ $L['span'] }}; grid-row:{{ $L['row'] }}; border-radius:26px; overflow:hidden; position:relative; background:{{ $L['bg'] }}; padding:{{ $L['big'] ? '32px' : '24px' }}; color:{{ $L['fg'] }}; display:flex; flex-direction:column; justify-content:flex-end;">
        @if($L['stripe'])<div style="position:absolute; inset:0; background:repeating-linear-gradient(45deg,rgba(255,255,255,.06) 0 18px,transparent 18px 36px);"></div>@endif
        <div style="position:relative;">
          <div style="font-family:var(--font-mono); font-size:12px; opacity:.85; margin-bottom:8px;">{{ $b['label'] }}</div>
          <h3 style="font-family:var(--font-display); font-weight:800; font-size:{{ $L['big'] ? '32px' : '22px' }}; margin:0 0 6px; letter-spacing:-.02em;">{{ $b['title'] }}</h3>
          @if($b['desc'])<p style="margin:0; opacity:.9; font-size:{{ $L['big'] ? '15px' : '14px' }};">{{ $b['desc'] }}</p>@endif
        </div>
      </a>
    @endforeach
  </div>
</section>
