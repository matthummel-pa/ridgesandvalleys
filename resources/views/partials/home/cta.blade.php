{{-- partials/home/cta.blade.php --}}
@php $contact = get_page_by_path('contact'); @endphp
<section class="prt-wrap" style="margin:60px auto 90px;">
  <div style="position:relative; overflow:hidden; background:linear-gradient(135deg,#6C4CF1 0%,#FF4D9D 55%,#FF7A3D 100%); border-radius:34px; padding:80px 48px; text-align:center; color:#fff;">
    <div style="position:absolute; top:-40px; left:40px; width:140px; height:140px; background:#37E29A; border-radius:50%; opacity:.85; animation:prt-floatA 6s ease-in-out infinite;"></div>
    <div style="position:absolute; bottom:-50px; right:60px; width:170px; height:170px; background:#FF7A3D; opacity:.85; animation:prt-blob 10s ease-in-out infinite;"></div>
    <div style="position:relative;">
      <h2 style="font-family:var(--font-display); font-weight:800; font-size:56px; letter-spacing:-.03em; margin:0 0 16px; line-height:1;">Got a project in mind?</h2>
      <p style="font-size:20px; opacity:.92; margin:0 auto 30px; max-width:34em;">Let's make something fast, useful, and a little bit delightful. I reply within a day.</p>
      <a href="{{ $contact ? get_permalink($contact) : '#contact' }}" class="prt-lift" style="display:inline-flex; text-decoration:none; background:#fff; color:#6C4CF1; padding:18px 36px; border-radius:999px; font-weight:700; font-size:17px; font-family:var(--font-display); box-shadow:0 10px 26px rgba(23,21,31,.22);">Start the conversation →</a>
    </div>
  </div>
</section>
