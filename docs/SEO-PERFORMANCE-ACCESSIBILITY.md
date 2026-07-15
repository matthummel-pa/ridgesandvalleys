# Ridges & Valleys — SEO, Performance & Accessibility playbook

The theme ships accessibility-first and performance-minded (self-hosted variable fonts, block
theme, minimal JS). This is the checklist to take it from "good" to "launch-ready" on
ridgesandvalleys.com. Work top to bottom.

## Included in this repo
- `extras/mu-plugins/ridges-valleys-seo.php` — drop into `wp-content/mu-plugins/`. Adds
  LocalBusiness (ProfessionalService) JSON-LD, meta/OpenGraph/Twitter defaults, a theme-color,
  a preloaded display font, emoji/pingback/generator trims, a focus outline, and a skip link.
  If you run a dedicated SEO plugin, add `define('RV_SEO_META', false);` to `wp-config.php` so the
  plugin owns meta output while this keeps the schema + performance + a11y bits.

## SEO
- [ ] Set Site Title + Tagline (Settings → General) and a real **meta description** per key page.
- [ ] Permalinks → **Post name** (`/%postname%/`).
- [ ] Install one SEO plugin — **Rank Math** or **Yoast** — for titles, sitemaps, breadcrumbs,
      and per-page controls. Set `RV_SEO_META` false once it's active.
- [ ] Submit `sitemap_index.xml` to **Google Search Console** + **Bing Webmaster**.
- [ ] Create/claim the **Google Business Profile**; match NAP (name, address, phone) to the
      schema in the mu-plugin and to the footer.
- [ ] Local keywords in H1/title/meta: "Gettysburg web design", "Adams County web designer",
      "South Central PA websites". One primary intent per page.
- [ ] Descriptive, unique `alt` text on every image (also an a11y win).
- [ ] Internal links between Services ↔ Work ↔ Process ↔ Start.
- [ ] `robots.txt` allows crawling; confirm no accidental "Discourage search engines" (Settings → Reading).

## Performance / Core Web Vitals
- [ ] Build assets for production: `composer install --no-dev -o` then `npm ci && npm run build`.
- [ ] Caching + optimization plugin: **WP Rocket** (paid) or **LiteSpeed Cache** / **W3 Total Cache**.
      Enable page cache, minify/combine CSS-JS, and lazy-load.
- [ ] Serve images as **WebP/AVIF** and size them right (hero ≤ 1600px). Plugin: ShortPixel / Imagify,
      or convert before upload. Every `<img>` needs width/height to avoid layout shift (CLS).
- [ ] Preload the LCP hero image on the homepage (add a `<link rel=preload as=image>` for the hero).
- [ ] Use a CDN (Cloudflare free tier is plenty) in front of the site.
- [ ] Keep plugins lean; audit with **Query Monitor**. Avoid heavy page builders (this is a block theme).
- [ ] Target: LCP < 2.5s, CLS < 0.1, INP < 200ms. Test with PageSpeed Insights + WebPageTest.
- [ ] Enable HTTP/2/3 + gzip/brotli at the host.

## Accessibility (WCAG 2.1 AA)
- [ ] Skip link present (mu-plugin adds one; theme may too — keep one).
- [ ] Color contrast: body text on paper and cream ≥ 4.5:1; large text ≥ 3:1. Wheat needs **dark**
      text on it. Never put small body text on Sage without checking.
- [ ] Visible focus states everywhere (mu-plugin enforces a wheat outline).
- [ ] Logical heading order (one H1 per page, no skipped levels).
- [ ] All images have meaningful `alt` (decorative images `alt=""`).
- [ ] Forms: every field has a `<label>`; errors announced; the Start-a-Project form is keyboard-usable.
- [ ] Buttons/links are real `<button>`/`<a>` with descriptive text (not "click here").
- [ ] Test with keyboard only, and with a screen reader (VoiceOver / NVDA). Run **axe DevTools** and
      **WAVE**; fix criticals.
- [ ] Respect reduced motion: gate scroll animations behind `@media (prefers-reduced-motion: no-preference)`.

## Security / hygiene (launch)
- [ ] HTTPS everywhere (host-level cert), HSTS on.
- [ ] Force strong admin passwords + 2FA; unique admin username (not "admin").
- [ ] Limit login attempts; keep WordPress, theme, and plugins updated.
- [ ] Daily backups (host or UpdraftPlus). Test a restore before launch.
- [ ] Pin a known Pressroot release for production; keep a manual fallback for generated components.
