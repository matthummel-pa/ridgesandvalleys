---
title: Setup wizard
---

# Setup wizard — six steps from blank install to launched site

`app/setup-wizard.php` · the **Setup** tab on **Appearance → Pressroot** (first tab, and the default landing tab until the wizard has been completed once). A dismissible notice on the Dashboard and Themes screens points new installs at it.

The wizard is a **front door, not a second store**: every field writes the same theme mods and options the Customizer, Theme Settings tab, and generators already read, so nothing can drift apart. Its job is sequencing — the theme's existing engines (brand questionnaire, AI connectors, site-type generator, AI writer) plus the pieces that were genuinely missing before it existed (business contact/hours/mission fields, GA4, SEO plugin selection, WordPress settings automation, a review screen, and a launch action).

## Status bars

Two layers of progress feedback, both in the `prt_build_status_bar()` visual language:

- **Overall progress** — a spectrum bar under the stepper (`prt_wizard_progress_bar()`) showing completed steps out of six.
- **Per-form working bar** — every wizard form renders a hidden status bar (`prt_wizard_saving_bar($id, $title, $steps)`) and arms it with `onsubmit="prtWizStart(id)"` (one shared driver, printed once by `prt_wizard_saving_bar_js()`). The moment the form posts, the bar appears sticky at the top and walks through step-specific labels — e.g. stage B shows "Sending your brief to the model → Writing page copy in your voice → Placing text back into your layouts" — parking at 92% until the server redirect lands, so it never claims done early. This matters most on the long-running actions (site generation, AI-write-all, image generation).

## State & resume

Progress lives in one option, `prt_wizard_progress` (`['done' => [step => timestamp]]`). Reopening the tab resumes at the first not-yet-completed step; every step stays revisitable afterwards — the stepper is navigation, not a one-way gate. `prt_wizard_is_complete()` reports whether all six steps have been finished at least once (this also flips the settings page's default tab from Setup back to Site Types).

## The steps

### 1 · Business info (`prt_wizard_save_business`)

Identity (site title + tagline, mirrored to `prt_brand_name` / `prt_brand_desc` exactly like Theme Settings), a **business/website type** dropdown (the site-type registry; remembered in option `prt_wizard_site_type` and applied in step 4), industry (shared `prt_brand_industries()` list, `pressroot/brand_industries` filter), brand basics (mode, vibe, goal, audience, tone), colors + fonts (same mods as the Customizer/kits), logo upload (`custom_logo` + `prt_seo_logo`), and a multi-file photo/video upload straight to the Media Library.

New business-fact fields (all theme mods): `prt_biz_mission`, `prt_biz_about`, `prt_biz_email`, `prt_biz_phone`, `prt_biz_address`, `prt_biz_hours` (per-day array, `mon`–`sun`). These are compiled into the **CORE SITE BRIEF** (`prt_core_ai_instructions()` in `app/site-type-remix.php`) so every AI call states real facts instead of inventing them. Saving recompiles the brief.

### 2 · Connections (`prt_wizard_save_connect`, `manage_options`)

- **AI providers** — the same key fields as the AI Models tab (`prt_ai_key_{slug}`; blank keeps the stored key). Pollinations stays the keyless default.
- **SEO plugin selector** — cards for the built-in SEO layer (default), Yoast, Rank Math, and All in One SEO, with live installed/active status, one-click install/activate links (capability-gated), and a collapsible "New to SEO?" primer. Choice stored as `prt_seo_choice`; `app/seo.php` keeps auto-deferring to whichever plugin actually activates.
- **Google Analytics** — a validated **GA4 Measurement ID** field (`prt_ga4_id`, `G-XXXXXXXXXX`). When set, the official gtag.js snippet is injected on `wp_head` (skipped if the same ID already appears in the manual head-code field, so nothing double-counts). Two step-by-step walkthroughs ship in the screen: creating a GA4 property, and registering the business on Google Business Profile. No OAuth — a theme can't ship hosted Google credentials.
- **Addons** — Pressroot AI and Repofolio toggles, linking to the GitHub tab for account connection.

### 3 · WordPress settings (`prt_wizard_save_wpsettings`, `manage_options`)

Explains and applies the core WP settings in one click: timezone (`timezone_string`, validated identifier), pretty permalinks (`/%postname%/` + rewrite flush), site icon upload (`site_icon`), search-engine visibility (recommended **hidden while building** — step 6 re-enables it at launch), and default comment status. A collapsible map links every native Settings screen for the long tail.

### 4 · Generate your website

Three stages, all running the existing engines and returning to the wizard via posted `prt_return_tab` / `prt_return_step` (see `prt_settings_return_url()`):

- **A. Design & pages** — `prt_apply_site_type` (kit + trend deal, draft pages with a full designed layout and sample content in every element, site chrome).
- **B. AI copy** — `prt_ai_fill_all` rewrites every sample text segment in the brand voice; model picker offers the configured connectors. The screen also points at the per-block ✨ toolbar button and the Pressroot AI editor panel for hand-tuning.
- **C. Imagery** — `prt_ai_brand_image` (homepage hero), plus pointers to the step-1 uploads and the per-page AI image buttons on the Site Types tab. Video generation is registered but ships in a future milestone.

### 5 · Review

A homepage iframe preview, a table of every generated page (status, preview link, edit link — drafts use `get_preview_post_link()`), and a "want to change something? here's where" map: words (editor / ✨ per block), images (Media Library replace / per-page AI), whole design (re-deal in step 4 or 🎲 per page on Site Types), and chrome (Customizer deep links).

### 6 · Launch (`prt_wizard_launch`, `manage_options`)

A pre-flight checklist (name/tagline, pages generated, drafts remaining, menu, logo, GA, site icon — each with a Fix → link), then the launch action: publish every generated draft, promote a `home` page to static front page if none is set, flip `blog_public` back on, and stamp `prt_wizard_launched`. Nothing forces a launch — drafts stay drafts until the owner clicks.

## Integration points

- Tab registered first in `prt_settings_tabs()` (`app/pressroot-settings.php`), which is now filterable: `pressroot/settings_tabs`.
- `prt_settings_return_url(string $defaultTab, array $args)` — shared helper; the generation handlers in `app/ai-assistant.php` / `app/ai-builder.php` use it so wizard-launched runs come back to step 4 while classic-tab runs keep their old redirects.
- `prt_brand_industries()` — shared with the Theme Settings tab (which previously hardcoded the list).
- Styles: the `.prt-wiz-*` block appended to `resources/css/admin-settings.css` (same no-build-step convention).

## New keys reference

| Key | Type | What |
|---|---|---|
| `prt_wizard_progress` | option | `['done' => [step => ts]]` |
| `prt_wizard_site_type` | option | site type chosen in step 1, applied in step 4 |
| `prt_wizard_launched` | option | launch timestamp |
| `prt_biz_mission` / `prt_biz_about` | theme mod | mission statement / what the business does |
| `prt_biz_email` / `prt_biz_phone` / `prt_biz_address` | theme mod | public contact facts |
| `prt_biz_hours` | theme mod | per-day hours array (`mon`–`sun`, free text) |
| `prt_ga4_id` | theme mod | GA4 Measurement ID (validated `G-…`) |
| `prt_seo_choice` | theme mod | `builtin` / `yoast` / `rankmath` / `aioseo` |
