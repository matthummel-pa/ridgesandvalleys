{{--
  Contact-style closer: three paths + what happens next.
  Used under the Journal grid and under each article so the page can close a sale.
  Reads copy from the posts page (Journal) so one editor controls both.
--}}
@php($blogId = isset($blogId) ? (int) $blogId : (int) get_option('page_for_posts'))
@php($bid = $blogId ?: null)
@php($ctaHref = $ctaHref ?? \App\cta_href(get_theme_mod('rv_cta_url', '/contact/')))
@php($cEmail = 'matthew.r.hummel@gmail.com')
@php($toolsHref = home_url('/website-grader/'))

<section class="rv-band rv-band-alt rv-closer" aria-labelledby="rv-closer-title">
  <div class="rv-shell">
    <div class="rv-closer-head">
      {!! \App\eyebrow(\App\field('jnl_close_eyebrow', __('Your next step', 'sage'), $bid)) !!}
      <h2 id="rv-closer-title" class="rv-section-title">{{ \App\field('jnl_close_title', __('Done reading.', 'sage'), $bid) }} <em class="rv-accent">{{ \App\field('jnl_close_accent', __('Let’s build yours.', 'sage'), $bid) }}</em></h2>
      <p class="rv-page-intro">{{ \App\field('jnl_close_intro', __('Tell me about your Gettysburg or Adams County business. I’ll come back with a fixed-scope idea — usually within a business day. No jargon, no pressure.', 'sage'), $bid) }}</p>
    </div>

    <div class="rv-closer-paths" aria-label="{{ __('Ways to get in touch', 'sage') }}">
      <a class="rv-cway rv-cway-primary" href="{{ $ctaHref }}">
        <span class="rv-cway-ico" aria-hidden="true"><svg viewBox="0 0 24 24" width="24" height="24" fill="none" stroke="currentColor" stroke-width="1.6" stroke-linecap="round" stroke-linejoin="round"><path d="M8 4H6a2 2 0 0 0-2 2v13a2 2 0 0 0 2 2h12a2 2 0 0 0 2-2V6a2 2 0 0 0-2-2h-2"/><rect x="8" y="2.5" width="8" height="4" rx="1"/><path d="M8 11h8M8 15h5"/></svg></span>
        <span class="rv-cway-body"><span class="rv-cway-t">{{ \App\field('jnl_close_quote', __('Get a fixed-price quote', 'sage'), $bid) }}</span><span class="rv-cway-d">{{ \App\field('jnl_close_quote_d', __('The fastest way from this page to a real plan.', 'sage'), $bid) }}</span></span>
      </a>
      <a class="rv-cway" href="mailto:{{ esc_attr($cEmail) }}">
        <span class="rv-cway-ico" aria-hidden="true"><svg viewBox="0 0 24 24" width="24" height="24" fill="none" stroke="currentColor" stroke-width="1.6" stroke-linecap="round" stroke-linejoin="round"><rect x="3" y="5" width="18" height="14" rx="2"/><path d="m4 7 8 6 8-6"/></svg></span>
        <span class="rv-cway-body"><span class="rv-cway-t">{{ \App\field('jnl_close_email_t', __('Email me directly', 'sage'), $bid) }}</span><span class="rv-cway-d">{{ $cEmail }}</span></span>
      </a>
      <a class="rv-cway" href="{{ $toolsHref }}">
        <span class="rv-cway-ico" aria-hidden="true"><svg viewBox="0 0 24 24" width="24" height="24" fill="none" stroke="currentColor" stroke-width="1.6" stroke-linecap="round" stroke-linejoin="round"><circle cx="12" cy="12" r="9"/><path d="M12 7v5l3 2"/></svg></span>
        <span class="rv-cway-body"><span class="rv-cway-t">{{ \App\field('jnl_close_tools_t', __('Grade your site first', 'sage'), $bid) }}</span><span class="rv-cway-d">{{ \App\field('jnl_close_tools_d', __('Free, no signup — see where you stand.', 'sage'), $bid) }}</span></span>
      </a>
    </div>

    <div class="rv-closer-split">
      <div class="rv-cside-block">
        {!! \App\eyebrow(\App\field('jnl_close_next_eyebrow', __('What happens next', 'sage'), $bid)) !!}
        <ol class="rv-cnext">
          @foreach (\App\field_rows('jnl_close_next', \App\journal_next_defaults(), $bid) as $i => $cn)
            <li><span class="rv-cnext-n">{{ $i + 1 }}</span><div><strong>{{ $cn['strong'] ?? '' }}</strong> {{ $cn['text'] ?? '' }}</div></li>
          @endforeach
        </ol>
      </div>
      <p class="rv-closer-fine">{{ \App\field('jnl_close_fine', __('Fixed price agreed up front · You own the site · A real person answers.', 'sage'), $bid) }}</p>
    </div>
  </div>
</section>

<style>
  .rv-closer{padding-block:clamp(2.5rem,5vw,4rem)}
  .rv-closer-head{max-width:46rem}
  .rv-closer-head .rv-section-title{margin:.35rem 0 .8rem}
  .rv-closer-paths{display:grid;grid-template-columns:repeat(3,minmax(0,1fr));gap:1rem;margin-top:clamp(1.5rem,3vw,2.25rem)}
  .rv-closer .rv-cway{display:flex;align-items:center;gap:1rem;background:var(--color-surface);border:1px solid var(--color-line);border-radius:var(--radius-lg,16px);padding:1.25rem 1.35rem;text-decoration:none;position:relative;overflow:hidden;transition:transform .15s ease,border-color .15s ease,box-shadow .2s ease}
  .rv-closer a.rv-cway:hover{transform:translateY(-3px);border-color:color-mix(in srgb,var(--color-clay) 40%,var(--color-line));box-shadow:var(--shadow-lift)}
  .rv-closer .rv-cway-primary{background:var(--color-pine);border-color:var(--color-pine)}
  .rv-closer .rv-cway-primary .rv-cway-t,.rv-closer .rv-cway-primary .rv-cway-d{color:#fff}
  .rv-closer .rv-cway-primary .rv-cway-ico{background:rgba(255,255,255,.14);color:#fff}
  .rv-closer .rv-cway-ico{flex:none;width:48px;height:48px;border-radius:12px;display:inline-flex;align-items:center;justify-content:center;background:color-mix(in srgb,var(--color-clay) 12%,var(--color-surface));color:var(--color-clay)}
  .rv-closer .rv-cway-body{display:flex;flex-direction:column;gap:.15rem;min-width:0}
  .rv-closer .rv-cway-t{font-family:var(--font-display);font-weight:700;font-size:1.02rem;color:var(--color-ink)}
  .rv-closer .rv-cway-d{font-size:.86rem;color:var(--color-muted);overflow-wrap:anywhere;word-break:break-word}
  .rv-closer-split{margin-top:clamp(1.5rem,3vw,2.25rem);display:grid;grid-template-columns:minmax(0,1.4fr) minmax(0,.8fr);gap:clamp(1.25rem,3vw,2rem);align-items:end}
  .rv-closer .rv-cside-block{background:var(--color-surface);border:1px solid var(--color-line);border-radius:var(--radius-lg,16px);padding:1.5rem 1.6rem;position:relative;overflow:hidden}
  .rv-closer .rv-cside-block::before{content:"";position:absolute;left:0;top:0;bottom:0;width:5px;background:var(--ridgeline)}
  .rv-closer .rv-cnext{list-style:none;margin:1rem 0 0;padding:0;display:grid;gap:1rem}
  .rv-closer .rv-cnext li{display:grid;grid-template-columns:auto 1fr;gap:.9rem;align-items:start}
  .rv-closer .rv-cnext-n{flex:none;width:30px;height:30px;border-radius:50%;display:inline-flex;align-items:center;justify-content:center;background:var(--color-pine);color:#fff;font-family:var(--font-display);font-weight:800;font-size:.9rem}
  .rv-closer .rv-cnext div{color:var(--color-body);line-height:1.5;font-size:.95rem}
  .rv-closer .rv-cnext strong{color:var(--color-ink);display:block;font-weight:700}
  .rv-closer-fine{margin:0;font-family:var(--font-mono);font-size:.78rem;letter-spacing:.04em;color:var(--color-muted);line-height:1.55;overflow-wrap:anywhere}
  @media(max-width:900px){.rv-closer-paths{grid-template-columns:1fr}.rv-closer-split{grid-template-columns:1fr;align-items:start}.rv-closer-fine{margin-top:.35rem}}
  @media(prefers-reduced-motion:reduce){.rv-closer a.rv-cway:hover{transform:none}}
</style>
