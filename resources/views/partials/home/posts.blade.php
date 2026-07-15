{{-- partials/home/posts.blade.php
     Pulls your 3 most recent posts. Falls back to nothing if none exist. --}}
@php
  $recent = get_posts(['numberposts' => 3, 'post_status' => 'publish']);
  $tints  = ['#6C4CF1', '#FF7A3D', '#22CFEE'];
@endphp
@if($recent)
<section class="prt-wrap" style="padding-top:80px; padding-bottom:30px;">
  <div style="display:flex; align-items:baseline; justify-content:space-between; flex-wrap:wrap; gap:12px; margin-bottom:32px;">
    <h2 style="font-family:var(--font-display); font-weight:800; font-size:clamp(34px,4vw,46px); letter-spacing:-.025em; margin:0; color:var(--color-h2, var(--color-ink));">Latest writing</h2>
    <a href="{{ get_permalink(get_option('page_for_posts')) ?: '#blog' }}" style="text-decoration:none; font-weight:700; color:#6C4CF1; font-size:16px;">All posts →</a>
  </div>
  <div style="display:grid; grid-template-columns:repeat(3,1fr); gap:20px;">
    @foreach($recent as $i => $post)
      @php setup_postdata($post); $cat = get_the_category($post->ID); @endphp
      <a href="{{ get_permalink($post) }}" style="text-decoration:none; color:inherit;">
        <div style="aspect-ratio:16/10; border-radius:18px; border:1.5px solid #ECE6FB; margin-bottom:14px; display:flex; align-items:flex-end; padding:12px; {{ has_post_thumbnail($post) ? "background:url('".esc_url(get_the_post_thumbnail_url($post,'large'))."') center/cover;" : "background:repeating-linear-gradient(135deg,#EEE8FE 0 14px,#f7f3ff 14px 28px);" }}">
          @unless(has_post_thumbnail($post))<span style="font-family:var(--font-mono); font-size:11px; color:#9A93C2;">[ cover.jpg ]</span>@endunless
        </div>
        <div style="font-family:var(--font-mono); font-size:12px; color:{{ $tints[$i % 3] }}; margin-bottom:8px;">
          {{ $cat ? strtoupper($cat[0]->name) : 'POST' }} · {{ strtoupper(get_the_date('M Y', $post)) }}
        </div>
        <h3 style="font-family:var(--font-display); font-weight:700; font-size:20px; margin:0 0 8px; line-height:1.25; color:var(--color-h3, var(--color-ink));">{{ get_the_title($post) }}</h3>
        <p style="font-size:14.5px; color:#5A5676; line-height:1.5; margin:0;">{{ wp_trim_words(get_the_excerpt($post), 16) }}</p>
      </a>
    @endforeach
    @php wp_reset_postdata(); @endphp
  </div>
</section>
@endif
