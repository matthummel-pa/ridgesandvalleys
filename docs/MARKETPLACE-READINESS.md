---
title: Marketplace readiness
---

# Marketplace readiness — health check, 2026-07-10

Full theme audit (security, accessibility, i18n, licensing, distribution,
future-proofing) against ThemeForest/Envato reviewer standards. Verdict:
**strong foundation, no critical security flaws — but not yet submittable.**
The remaining blockers are identity/packaging/licensing work, not
engineering rewrites.

## Fixed in this pass (2026-07-10)

**Security**
- Settings **export no longer includes API keys** (`prt_ai_key_*`, `*_key/_token/_secret` redacted) — export filename de-branded to `pressroot-settings-*.json` (`app/settings-io.php`).
- Settings **import now sanitizes the code-injection mods** through `prt_sanitize_code()`, closing an unfiltered-HTML bypass (multisite privesc vector) (`app/settings-io.php`).
- Hero **image importer hardened against SSRF**: `wp_http_validate_url()` + `wp_safe_remote_get()` (blocks loopback/private/link-local, unsafe redirects) + `image/*` content-type check (`app/hero-image.php`).
- Contact form: **per-IP rate limit** (30s transient) and the hardcoded `[matthummel.com]` subject replaced with the site name (`app/contact.php`).

**Accessibility**
- Off-canvas menu: closed state is now `inert` + `aria-hidden` (was tab-reachable while invisible), open state gets a real **focus trap**, focus moves into the panel and **returns to the toggle on close**; toggle now also closes (`resources/js/app.js` — run `npm run build`).
- **AI-generated images get real alt text** (`_wp_attachment_image_alt`) on every sideload (`app/ai-builder.php`).
- Cookie notice `role="dialog"` → `role="region"` (passive banner, no false focus contract) (`app/integrations.php`).

**i18n / packaging / disclosure**
- All 21 leftover `'sage'` text-domain strings → `'pressroot'`; `load_theme_textdomain()` added; pot script renamed to `pressroot.pot` (`functions.php`, `app/setup.php`, `app/filters.php`, `app/google-fonts-collection.php`, `app/View/Composers/*`, `package.json`).
- `style.css`: added `Tags:` and `Tested up to:`.
- **`.distignore`** created (excludes node_modules/.git/.playground/docs/dev configs; documents that `vendor/` + `public/build` MUST ship).
- **`resources/fonts/LICENSES.md`** (all three families are SIL OFL 1.1; upstream OFL.txt still to be copied in).
- **`docs/THIRD-PARTY-SERVICES.md`** — full privacy/services inventory; AI privacy note added to the Setup wizard's Connections step.

## Remaining BLOCKERS before submission

1. **Genericize author identity in shipped content.** The author's portfolio still ships as theme content: `matthummel-pa` GitHub-owner fallbacks (`resources/views/single-repofolio_project.blade.php:20`, `template-projects.blade.php:31`, `app/seed-pages.php:354` — which auto-imports the author's real repos), portfolio cards (`partials/home/repos.blade.php`, `work-bento.blade.php`), "Matt Hummel" inside a registered pattern (`app/block-patterns.php:58`), first-person CTA copy (`partials/cta.blade.php`), footer credit link, default social URLs (`app/social-links.php:28,30`), `resources/sample-content/*.html`, contact template GitHub link. *Decision needed: these power matthummel.com today — split "Matt's site" content into a child theme/demo pack, and neutralize the parent.*
2. ~~**Rename the `matthummel/*` hook + pattern namespace to `pressroot/*`.**~~ **DONE 2026-07-10** — every filter and pattern slug renamed across app/, resources/, and the playground mu-plugins; `app/hooks-registry.php` updated; no back-compat shims (pre-release, no external consumers). `composer.json` package name intentionally left for the packaging pass (blocker 3).
3. **Build a real release artifact.** Zip per `.distignore` **including `vendor/` (composer install --no-dev) and `public/build`** — buyers can't run Composer. Add a `npm run dist` script (e.g. WP-CLI `wp dist-archive .`). Set `composer.json` `minimum-stability` → `stable`; rename `matthummel/theme` → `pressroot/pressroot`.
4. **Generate + ship `resources/lang/pressroot.pot`** (`npm run translate:pot` needs WP-CLI).
5. **Copy upstream `OFL.txt` files** into `resources/fonts/` (see LICENSES.md) and decide the license story: keep MIT everywhere consistently, or move to GPLv2+ (WP convention; Envato uses GPL/split licensing for WP items).
6. **Bundle Prism.js locally** instead of the CDN (`app/code-highlight.php`) — Envato flags remote scripts. Same policy question for the live Google Fonts CSS route (self-host default is safest; the local-fonts feature already exists).
7. **AI default consent posture.** Disclosure now ships, but Pollinations still receives prompts keylessly by default. Recommended: first generate action shows a one-time "this sends your business info to a free third-party AI — OK?" confirm, or default `prt_ai_features_on` to off until the wizard's Connections step is saved.
8. **`style.css` Author should be a brand/shop name** (currently a person) and Theme URI/Author URI should point at product pages, not the personal GitHub. README beta/"free domain for testers" language must go before a paid listing.

## SHOULD-FIX (quality bar, not gating)

- **Contrast**: darken `prt_color_action` (or use ink button text) in kits `pink_pop` (#FF4D9D ≈3.2:1), `coral_cream` (#FF7A3D ≈2.7:1), `core_marketing`; check `amber_toast`; reconsider `faint #7C75A8` as a text token; keep `amber/lime/sky` out of the text palette (`app/settings-io.php`, `theme.json`).
- **Heading order in generated patterns**: sections jump h1→h3; add per-section `<h2>` (screen-reader-text ok) across `app/site-type-*.php` (pricing already does this).
- Contact form: `aria-live` on the status region + per-field errors. Newsletter: visible label, drop `novalidate`. TOC → `<nav aria-label>`. Code-copy buttons: `aria-live` "Copied!". Wizard done-steps: visually-hidden "completed".
- Reconcile the unregistered `'projects'` CPT references (`app/seed-pages.php:370`, `app/page-patterns.php`, `app/seo.php:236`).
- Repofolio README HTML (`single-repofolio_project.blade.php:63`) relies on GitHub's sanitization — wrap in `wp_kses_post` for defense in depth.
- Refactor AI image temp-write (`file_put_contents`/`base64_decode` in `app/ai-connectors.php:572`) to `WP_Filesystem` to keep Theme Check quiet.
- Consider lowering `Requires PHP` 8.3 → 8.1 for market reach (currently uses 8.x-safe syntax; needs a pass to confirm nothing is 8.3-only), or own the 8.3 floor in the listing.
- Remove `.DS_Store` files from the repo (already dist-ignored).

## Future-proofing for one-time-sale monetization

1. **Companion plugin split ("Pressroot Core")** — move theme-switch-fragile functionality out of the theme: `repofolio_project` CPT/taxonomy, contact form handler, SEO/schema layer, shortcodes (`prt_newsletter`, `prt_breadcrumbs`). Envato reviewers increasingly require this; buyers keep their content if they switch themes. The existing addon framework + Repofolio's "plugin wins if active" pattern is the template.
2. **Addon API v2** (`app/theme-addons.php`) — promote entries from bare booleans to `{name, version, requires, tier, update_url, activate_cb, deactivate_cb}` and fire `do_action('pressroot/addon_activated', $slug)` / `…deactivated`. Do this while the API is private; it's the hook for selling paid addon packs later.
3. **Version + upgrade router** — define a `PRT_VERSION` constant, store `prt_db_version`, and run migrations on bump (today's one-off `prt_*_v#` flags in seed-pages become cases in one router).
4. **Purchase-code support** — for Envato, add an optional Envato-API purchase-code field on the Support tab gating premium support/addon downloads. Updates themselves ship through the marketplace.
5. **Uninstall documentation** — themes can't hook uninstall; document (or add a Support-tab "remove all Pressroot data" button for) cleaning `prt_*` options/mods and `_prt_*` meta.

## Submission checklist (short form)

- [ ] Blockers 1–8 above cleared
- [ ] `npm run build` + `composer install --no-dev` + dist zip < 50 MB, opens clean on a fresh WP
- [ ] Theme Check + Envato Theme Check plugins pass
- [ ] Demo content exported (XML/WXR) separately from the theme zip
- [ ] Screenshot 1200×900 (present ✓), item preview images per Envato specs
- [ ] Buyer documentation (curated from docs/, minus internal build logs)
- [ ] Privacy/services disclosure linked from the item page (`docs/THIRD-PARTY-SERVICES.md`)
