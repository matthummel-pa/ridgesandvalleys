{{-- partials/home/brand-marquee.blade.php --}}
@php
  $words = ['WORDPRESS','POWER PLATFORM','SAGE 11','REACT','SEO & GROWTH','ACCESSIBILITY'];
  $marks = ['#37E29A','#FF7A3D','#22CFEE','#6C4CF1'];
@endphp
<section style="background:#17151F; color:#FFF9F5; overflow:hidden; white-space:nowrap; padding:18px 0; transform:rotate(-1.2deg) scale(1.04);">
  <div style="display:inline-block; animation:prt-marq 22s linear infinite; font-family:var(--font-display); font-weight:800; font-size:24px; letter-spacing:-.01em;">
    @for($pass = 0; $pass < 2; $pass++)
      @foreach($words as $i => $w)
        <span style="padding:0 22px;">{{ $w }}</span><span style="color:{{ $marks[$i % count($marks)] }}; padding:0 14px;">✦</span>
      @endforeach
    @endfor
  </div>
</section>
