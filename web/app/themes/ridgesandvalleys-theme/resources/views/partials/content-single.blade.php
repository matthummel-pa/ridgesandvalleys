<div class="rv-progress" aria-hidden="true"><span class="rv-progress-bar"></span></div>

<article @php(post_class('rv-single'))>
  <header class="rv-single-hero">
    <div class="rv-entry">
      {!! \App\breadcrumbs() !!}
      @php($cats = get_the_category())
      @if (! empty($cats))
        {!! \App\eyebrow($cats[0]->name) !!}
      @endif
      @unless (\App\entry_hero_enabled())<h1 class="rv-single-title">{!! get_the_title() !!}</h1>@endunless
      {!! \App\post_meta() !!}
    </div>
  </header>

  @if (! \App\entry_hero_enabled() && has_post_thumbnail())
    <figure class="rv-single-figure">@php(the_post_thumbnail('rv-hero', ['class' => 'rv-single-image', 'loading' => 'eager', 'fetchpriority' => 'high', 'decoding' => 'async']))</figure>
  @elseif (! \App\entry_hero_enabled())
    @php($heroImg = \App\blog_post_image())
    @if ($heroImg)
      <figure class="rv-single-figure"><img class="rv-single-image" src="{{ $heroImg }}" alt="{{ get_the_title() }}" width="1600" height="900" loading="eager" fetchpriority="high" decoding="async"></figure>
    @endif
  @endif

  @php($rvToc = \App\post_toc_data())
  @php($rvSummary = \App\post_summary())
  @if ($rvSummary || count($rvToc['items']) > 1)
    <div class="rv-entry">
      <aside class="rv-tldr" aria-label="{{ __('Article summary and contents', 'sage') }}">
        @if ($rvSummary)
          <div class="rv-tldr-summary">
            <span class="rv-tldr-eyebrow">{{ __('The short version', 'sage') }}</span>
            <p>{{ $rvSummary }}</p>
          </div>
        @endif
        @if (count($rvToc['items']) > 1)
          <nav class="rv-tldr-toc" aria-label="{{ __('In this article', 'sage') }}">
            <span class="rv-tldr-eyebrow">{{ __('In this article', 'sage') }}</span>
            <ol class="rv-tldr-list">
              @foreach ($rvToc['items'] as $rvItem)
                <li><a href="#{{ $rvItem['id'] }}">{{ $rvItem['text'] }}</a></li>
              @endforeach
            </ol>
          </nav>
        @endif
      </aside>
    </div>
  @endif

  <div class="rv-entry rv-prose">
    {!! \App\content_add_inline_cta($rvToc['content']) !!}
    @php(wp_link_pages(['before' => '<nav class="rv-page-links">' . __('Pages:', 'sage'), 'after' => '</nav>']))
    @php($tags = get_the_tag_list('<ul class="rv-tags"><li>', '</li><li>', '</li></ul>'))
    @if ($tags)
      <div class="rv-single-tags">{!! $tags !!}</div>
    @endif
  </div>

  @php($rvShareOn = (bool) get_theme_mod('rv_share_enable', true))
  @php($rvHelpfulOn = (bool) get_theme_mod('rv_helpful_enable', true))
  @if ($rvShareOn || $rvHelpfulOn)
    <div class="rv-entry rv-single-actions">
      @if ($rvShareOn){!! \App\share_links() !!}@else<span></span>@endif
      @if ($rvHelpfulOn)
        <div class="rv-helpful" data-cta="{{ \App\cta_href(get_theme_mod('rv_cta_url', '/contact/')) }}">
          <span class="rv-helpful-q">{{ __('Was this helpful?', 'sage') }}</span>
          <div class="rv-helpful-btns">
            <button type="button" class="rv-helpful-btn" data-v="yes">{{ __('Yes', 'sage') }}</button>
            <button type="button" class="rv-helpful-btn" data-v="no">{{ __('Not really', 'sage') }}</button>
          </div>
          <p class="rv-helpful-thanks" role="status" hidden></p>
        </div>
      @endif
    </div>
  @endif

  {{-- Trust, not a competing CTA — the full closer sits full-width under the article. --}}
  @php($authorName = (string) get_the_author_meta('display_name'))
  @php($authorName = ($authorName !== '' && ! str_contains($authorName, '@')) ? $authorName : __('Matt Hummel', 'sage'))
  <div class="rv-entry rv-single-trust">
    <div class="rv-author">
      <span class="rv-author-avatar" aria-hidden="true"></span>
      <div>
        <span class="rv-author-kicker">{{ __('Written by a Gettysburg web designer', 'sage') }}</span>
        <strong>{{ $authorName }}</strong>
        <span class="rv-author-bio">{{ get_theme_mod('rv_post_author_bio', __('Founder of Ridges & Valleys Studio. 15 years as a WordPress developer, now building fast, accessible websites for Gettysburg and South Central PA.', 'sage')) }}</span>
        <a class="rv-author-more" href="{{ home_url('/gettysburg-web-design/') }}">{{ __('About the studio', 'sage') }} {!! \App\icon('arrow') !!}</a>
      </div>
    </div>
  </div>
</article>

@if (get_theme_mod('rv_float_cta_enable', true))
  @php($rvCtaHref = \App\cta_href(get_theme_mod('rv_cta_url', '/contact/')))
  <aside class="rv-floating-cta" id="rv-floating-cta" hidden>
    <button type="button" class="rv-floating-close" aria-label="{{ __('Dismiss', 'sage') }}">&times;</button>
    <p class="rv-floating-title">{{ get_theme_mod('rv_float_cta_title', __('Ready to stop guessing?', 'sage')) }}</p>
    <a class="rv-btn rv-btn-primary rv-floating-btn" href="{{ $rvCtaHref }}">{{ get_theme_mod('rv_float_cta_btn', __('Get a quote', 'sage')) }}</a>
  </aside>
@endif

<script>
(function () {
  var article = document.querySelector('article.rv-single');

  /* Reading progress bar */
  var bar = document.querySelector('.rv-progress-bar');
  if (bar && article) {
    var upd = function () {
      var total = article.offsetHeight - window.innerHeight;
      var scrolled = Math.min(Math.max(-article.getBoundingClientRect().top, 0), Math.max(total, 1));
      bar.style.width = (total > 0 ? (scrolled / total) * 100 : 0) + '%';
    };
    window.addEventListener('scroll', upd, { passive: true });
    window.addEventListener('resize', upd); upd();
  }

  /* Floating CTA: appear after the first screen, hide near the footer, dismissible */
  var fc = document.getElementById('rv-floating-cta');
  if (fc) {
    var off = false;
    var x = fc.querySelector('.rv-floating-close');
    if (x) x.addEventListener('click', function () { off = true; fc.hidden = true; });
    var fcUpd = function () {
      if (off) return;
      var y = window.scrollY || window.pageYOffset;
      var nearBottom = (window.innerHeight + y) > (document.body.offsetHeight - 900);
      fc.hidden = !(y > 700 && !nearBottom);
    };
    window.addEventListener('scroll', fcUpd, { passive: true }); fcUpd();
  }

  /* Was this helpful? -> reveal a soft CTA */
  var helpful = document.querySelector('.rv-helpful');
  if (helpful) {
    var thanks = helpful.querySelector('.rv-helpful-thanks');
    var cta = helpful.getAttribute('data-cta') || '/contact/';
    helpful.querySelectorAll('.rv-helpful-btn').forEach(function (b) {
      b.addEventListener('click', function () {
        var yes = b.getAttribute('data-v') === 'yes';
        var btns = helpful.querySelector('.rv-helpful-btns');
        if (btns) btns.style.display = 'none';
        if (thanks) {
          thanks.hidden = false;
          thanks.innerHTML = yes
            ? 'Glad it helped. If you\'d like this handled for you, <a href="' + cta + '">get a quote</a>.'
            : 'Thanks for the honesty — <a href="' + cta + '">tell me what was missing</a> and I\'ll improve it.';
        }
      });
    });
  }

  /* Copy-link share button */
  var copyBtn = document.querySelector('.rv-share-copy');
  if (copyBtn) {
    copyBtn.addEventListener('click', function () {
      var url = copyBtn.getAttribute('data-url');
      var done = function () { copyBtn.classList.add('is-copied'); setTimeout(function () { copyBtn.classList.remove('is-copied'); }, 1600); };
      if (navigator.clipboard && navigator.clipboard.writeText) {
        navigator.clipboard.writeText(url).then(done, done);
      } else {
        var t = document.createElement('textarea'); t.value = url; document.body.appendChild(t); t.select();
        try { document.execCommand('copy'); } catch (e) {}
        document.body.removeChild(t); done();
      }
    });
  }
})();
</script>
