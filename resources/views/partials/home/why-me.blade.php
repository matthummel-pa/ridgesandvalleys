{{-- partials/home/why-me.blade.php --}}
@php
  $points = [
    ['big' => '15+',  'emoji' => null, 'title' => 'Years of experience',   'desc' => 'Building accessible websites and web apps across the full stack.'],
    ['big' => null,   'emoji' => '🏢', 'title' => 'Senior MS consultant',   'desc' => 'By day I architect Power Platform solutions at Saliense Consulting.'],
    ['big' => null,   'emoji' => '♿', 'title' => 'Accessibility-first',     'desc' => 'Every build meets WCAG / Section 508 — never bolted on at the end.'],
    ['big' => null,   'emoji' => '🌐', 'title' => 'Open-source proof',       'desc' => 'My code is public — read the theme, plugin & app I ship in the open.'],
  ];
@endphp
<section class="prt-wrap" style="padding-top:80px; padding-bottom:10px;">
  <div style="margin-bottom:36px;">
    <h2 style="font-family:var(--font-display); font-weight:800; font-size:clamp(34px,4vw,46px); letter-spacing:-.025em; margin:0 0 8px; color:var(--color-h2, var(--color-ink));">Why work <span style="font-family:var(--font-serif); font-style:italic; font-weight:400; color:#6C4CF1;">with me</span></h2>
    <p style="margin:0; font-size:17px; color:#7C75A8; max-width:40em;">No giant agency, no account managers — just a senior developer who's been shipping for 15+ years and cares about the details.</p>
  </div>
  <div style="display:grid; grid-template-columns:repeat(4,1fr); gap:18px;">
    @foreach($points as $p)
      <div class="prt-spec-card" style="background:#fff; border:1.5px solid #ece6fb; border-radius:18px; padding:28px;">
        @if($p['big'])
          <div style="font-family:var(--font-display); font-weight:900; font-size:42px; letter-spacing:-.03em; color:#6C4CF1; line-height:1;">{{ $p['big'] }}</div>
        @else
          <div style="font-size:38px;">{{ $p['emoji'] }}</div>
        @endif
        <h3 style="font-family:var(--font-display); font-weight:700; font-size:19px; margin:14px 0 8px; color:var(--color-h3, var(--color-ink));">{{ $p['title'] }}</h3>
        <p style="font-size:14.5px; color:#5A5676; line-height:1.55; margin:0;">{{ $p['desc'] }}</p>
      </div>
    @endforeach
  </div>
</section>
