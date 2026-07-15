{{-- partials/home/services-preview.blade.php --}}
@php
  $services = [
    ['emoji' => '🎨', 'title' => 'Front-End',            'bg' => '#6C4CF1', 'fg' => '#fff',     'desc' => 'Snappy, responsive, accessible interfaces people actually enjoy using.', 'stack' => 'React · Block themes · Tailwind'],
    ['emoji' => '⚙️', 'title' => 'Back-End & Platforms', 'bg' => '#FF7A3D', 'fg' => '#17151F', 'desc' => 'Custom WordPress / Sage, clean APIs, and Power Platform automations that scale.', 'stack' => 'PHP · Power Apps · Dataverse'],
    ['emoji' => '📈', 'title' => 'SEO & Growth',         'bg' => '#37E29A', 'fg' => '#17151F', 'desc' => 'Performance, technical SEO, and content systems that compound over time.', 'stack' => 'Core Web Vitals · Schema · Analytics'],
  ];
@endphp
<section class="prt-wrap" style="padding-top:90px; padding-bottom:30px;">
  <div style="display:flex; align-items:baseline; justify-content:space-between; flex-wrap:wrap; gap:12px; margin-bottom:36px;">
    <h2 style="font-family:var(--font-display); font-weight:800; font-size:clamp(34px,4vw,46px); letter-spacing:-.025em; margin:0; color:var(--color-h2, var(--color-ink));">What I do <span style="font-family:var(--font-serif); font-style:italic; font-weight:400; color:#6C4CF1;">well</span></h2>
    <span style="font-family:var(--font-mono); font-size:13px; color:#7C75A8;">(three things, done right)</span>
  </div>
  <div style="display:grid; grid-template-columns:repeat(3,1fr); gap:22px;">
    @foreach($services as $s)
      <div class="prt-lift" style="background:{{ $s['bg'] }}; color:{{ $s['fg'] }}; border-radius:26px; padding:32px;">
        <div style="font-size:38px;">{{ $s['emoji'] }}</div>
        <h3 style="font-family:var(--font-display); font-weight:700; font-size:24px; margin:18px 0 10px;">{{ $s['title'] }}</h3>
        <p style="font-size:15.5px; line-height:1.55; opacity:.93; margin:0 0 18px;">{{ $s['desc'] }}</p>
        <div style="font-family:var(--font-mono); font-size:12px; opacity:.8;">{{ $s['stack'] }}</div>
      </div>
    @endforeach
  </div>
</section>
