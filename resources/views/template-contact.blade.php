{{--
  Template Name: Contact
  Paper + Space contact form. Form logic (nonce, prt_contact action, field names,
  honeypot, status messages) is unchanged. Kept compile-safe: inline @php() only,
  styles in a <style> block (no @php variable blocks).
--}}
@extends('layouts.app')

@section('content')
@php($mhStatus = isset($_GET['contact']) ? sanitize_key($_GET['contact']) : '')
@php($mhContactIntro = get_theme_mod('prt_contact_intro', "Tell me a little about your project and I'll reply within a day. Fixed-price quotes, no surprises."))

<style>
  .prt-contact-label { display:block; font-family:var(--font-display); font-weight:600; font-size:14px; color:#17151F; margin-bottom:7px; }
  .prt-contact-input { width:100%; font-family:var(--font-display); font-size:16px; color:#17151F; background:#FFF9F5; border:1.5px solid #ECE6FB; border-radius:14px; padding:13px 15px; box-sizing:border-box; }
  .prt-contact-input:focus { outline:none; border-color:#6C4CF1; box-shadow:0 0 0 3px rgba(124,92,255,.15); }
</style>

<section class="prt-wrap" style="padding-top:70px; padding-bottom:80px;">
  <div class="prt-grid-2" style="display:grid; grid-template-columns:0.85fr 1.15fr; gap:48px; align-items:start;">

    <div>
      <div style="font-family:var(--font-mono); font-size:13px; color:var(--color-eyebrow, var(--color-green)); letter-spacing:.1em; margin-bottom:18px;">CONTACT</div>
      <h1 style="font-family:var(--font-display); font-weight:800; font-size:clamp(40px,6vw,64px); letter-spacing:-.035em; line-height:1; margin:0 0 18px; color:var(--color-h1, var(--color-ink));">Let&rsquo;s build something <span class="prt-serif" style="color:#6C4CF1;">delightful</span>.</h1>
      <p style="font-size:19px; line-height:1.55; color:var(--color-body); margin:0 0 28px;">{!! wp_kses_post($mhContactIntro) !!}</p>

      @if (trim(get_the_content()))
        <div class="entry-content post-prose" style="margin-top:16px;">@php(the_content())</div>
      @endif

      <div style="margin-top:28px; display:flex; flex-direction:column; gap:12px; font-family:var(--font-mono); font-size:14px; color:#5A5676;">
        <span>&bull; Based in Gettysburg, PA</span>
        <span>&bull; Replies within ~1 business day</span>
        <a href="https://github.com/matthummel-pa" target="_blank" rel="noopener" style="color:#6C4CF1; text-decoration:none;">&bull; matthummel-pa on GitHub &#8599;</a>
      </div>
    </div>

    <div style="background:#fff; border:1.5px solid #ECE6FB; border-radius:28px; padding:36px; box-shadow:0 18px 40px rgba(23,21,31,.06);">
      @if ($mhStatus === 'success')
        <p class="form-success" style="background:#EEE8FE; color:#17151F; border-radius:14px; padding:16px 18px; margin:0 0 22px; font-weight:600;">Thanks &mdash; your message has been sent. I&rsquo;ll get back to you soon.</p>
      @elseif ($mhStatus === 'error')
        <p class="form-error" style="background:#FFE9E0; color:#7a2e00; border-radius:14px; padding:16px 18px; margin:0 0 22px; font-weight:600;">Sorry, something went wrong. Please check the fields and try again.</p>
      @endif

      <form class="contact-form" method="post" action="">
        @php(wp_nonce_field('prt_contact', 'prt_contact_nonce'))
        <input type="hidden" name="action" value="prt_contact">
        <p class="hp" style="position:absolute; left:-9999px;"><label>Leave this field empty <input type="text" name="prt_hp" tabindex="-1" autocomplete="off"></label></p>

        <div class="field" style="margin-bottom:18px;">
          <label for="cf-name" class="prt-contact-label">Name</label>
          <input id="cf-name" class="prt-contact-input" type="text" name="prt_name" required>
        </div>
        <div class="field" style="margin-bottom:18px;">
          <label for="cf-email" class="prt-contact-label">Email</label>
          <input id="cf-email" class="prt-contact-input" type="email" name="prt_email" required>
        </div>
        <div class="field" style="margin-bottom:18px;">
          <label for="cf-subject" class="prt-contact-label">Subject</label>
          <input id="cf-subject" class="prt-contact-input" type="text" name="prt_subject">
        </div>
        <div class="field" style="margin-bottom:24px;">
          <label for="cf-message" class="prt-contact-label">Message</label>
          <textarea id="cf-message" class="prt-contact-input" name="prt_message" rows="6" required style="resize:vertical;"></textarea>
        </div>
        <button class="btn prt-lift" type="submit" style="border:0; cursor:pointer; background:linear-gradient(135deg,#6C4CF1 0%,#FF4D9D 55%,#FF7A3D 100%); color:#fff; font-family:var(--font-display); font-weight:700; font-size:16px; padding:16px 30px; border-radius:999px;">Send message &rarr;</button>
      </form>
    </div>
  </div>
</section>
@endsection
