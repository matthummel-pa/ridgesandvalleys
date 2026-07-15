# Changelog

All notable changes to Pressroot are documented here.
The format is based on [Keep a Changelog](https://keepachangelog.com/en/1.1.0/),
and this project adheres to [Semantic Versioning](https://semver.org/spec/v2.0.0.html).

## [Unreleased]

### Added — Pressroots Reserve (bookings & reservations addon)
- **New Theme Addon: Pressroots Reserve** (`app/bookings-addon.php` + `app/Bookings/`) — appointments and table/seat reservations for restaurants, hotels, and meetings, bundled the same way as Repofolio: gated on the Add-ons toggle (Customizer → Theme Options → Theme Addons), classes under `app/Bookings/includes/` with plain `require_once` loading, booted through a `PrtBookings\Plugin` object, and settings surfaced as the **Bookings** tab on Appearance → Pressroot via the `pressroot/settings_tabs` filter. Built end to end in Claude Cowork. Full reference in `docs/PRESSROOTS-RESERVE.md`.
  - **Services** CPT (`prt_service`): duration, buffer, price label, and capacity — capacity `1` behaves like a one-on-one appointment (Calendly-style), capacity `>1` behaves like seats-per-slot with a party-size field (OpenTable-style).
  - **Availability engine** (`Bookings/includes/class-engine.php`): weekly schedule + blackout dates + minimum notice + booking window, all computed in the **site timezone** and stored as UTC timestamps so DST/timezone edits can't corrupt bookings. Slot lists subtract booked seats from capacity and re-check at insert time, so a slot can never double-book.
  - **Front-end booking form** — a `prt/booking` block **and** a `[prt_booking]` shortcode: service → date → time → details flow, protected by a nonce, honeypot, and per-IP rate limit (mirrors `app/contact.php`). Availability is never computed in the browser — the widget only renders what the engine returns.
  - **Emails** — customer confirmation with an `.ics` calendar attachment and a tokenized cancel link that opens a confirm screen (so mail scanners can't auto-cancel), plus owner notifications on book and cancel.
  - **Admin calendar** — a **Month / Week / Day / List** calendar (Bookings → Calendar) driven by a REST feed, plus a bookings list with one-click confirm/cancel row actions and a per-booking detail panel.
  - **Opt-in in the Setup wizard** — an addon checkbox in the Connections step writes `prt_addon_bookings_enabled`.
  - Also ships as a standalone WordPress plugin — [pressroots-reserve](https://github.com/matthummel-pa/pressroots-reserve) — sharing the same `includes/` and assets; the plugin defines `PRT_BOOKINGS_VERSION` at load so the bundled theme addon steps aside, exactly like Repofolio's dual-mode arrangement. Option keys (`prt_bookings_options`) and post types (`prt_service`, `prt_booking`) are identical either way, so data carries over.

### Fixed
- **Bookings addon boot order**: the addon's boot check (`prt_addon_enabled('bookings')`) runs at include time — *before* any `add_filter('pressroot/addon_defaults', …)` later in the same file would register. So the `bookings` default is declared directly in `prt_addon_defaults()` (`app/theme-addons.php`), not via the filter; declared via the filter it would read as unknown and the addon would silently never boot on the first request. Also registered `'bookings-addon'` in the `functions.php` module `collect()` list.

## [1.7.0] - 2026-07-13

### Added — Header & Footer designer (Kadence-style presets)
- **`app/design-presets.php`** — new module: six header layout presets (Classic bar, Top bar + nav, three-row Banner stack, Centered logo banner, Transparent over hero, Minimal) and four footer presets (Column grid, Centered, Mega, Minimal strip), each a batch of existing theme mods with schematic SVG preview cards. Presets are applied from a shared designer UI surfaced in two places: a new **"Design" step 4 in the Setup wizard** and a standalone **Appearance → Pressroot → Header & Footer** tab. Picking a header preset live-syncs the fine-tune fields (sticky, scrim, transparent scope, text scheme) so stale values can't override the preset on save.
- **Transparent overlay header** is now a true overlay: fixed positioning over the hero (admin-bar aware), light text with AA-safe hover states while see-through, optional scrim gradient for readability on any hero image, and a palette-derived solid bar on scroll (replacing the previously hardcoded warm-white).
- **Homepage hero real-image support** — `prt_home_hero_bg` + `prt_home_hero_overlay` mods (Customizer → Hero, wizard Design step with Media Library picker): a photo background behind the hero with a dark overlay clamped to ≥35% so the white headline keeps WCAG AA contrast on any image; gradient ground remains the no-image fallback.
- New Customizer controls in Header Layout: header text scheme (auto/dark/light), scrim toggle, centered-logo banner, minimal header.

### Fixed — WCAG AA contrast guard (light-on-light repair)
- **Palette contrast guard** (`prt_head_end` priority 17): heading/body colors that fail 4.5:1 on the actual page background are overridden at render time with a readable color — auto-repairing broken palettes from kits, AI generation, or bad saves without touching the database.
- **Header bar follows the palette**: bar background now derives from `prt_color_paper` (was hardcoded warm-white) and its text color is always derived from the real bar background; explicitly-set nav colors failing 3:1 are ignored.
- **Block palette utilities follow the palette**: `has-surface/paper/cream-background-color` (baked as fixed light hex by theme.json) are re-derived on dark-paper palettes, and flipped to dark equivalents in dark mode (`resources/css/app.css`) — fixes light headings on white cards in both modes.
- **Top bar / footer pairs contrast-checked**: bg/text combinations that miss AA get their text variable overridden; footer brand name pinned to the footer text variable (was invisible on light footers under inverted palettes).
- **Setup wizard step 1**: the four color pickers now carry per-field defaults (background/headings/body no longer display the brand purple), preventing an untouched save from collapsing the palette into same-on-same.
- Homepage hero orange chip: dark ink text (was white at ~2.5:1; now ~7:1).
- Fine-grained heading/link color mods (`prt_color_h1..h6/link/eyebrow`) guarded against the page background.

### Changed
- Setup wizard is now **seven steps** (Design inserted at 4; Generate/Review/Launch shifted to 5/6/7) with a one-time progress migration so previously completed steps stay completed.

### Security
- Settings export no longer includes API keys (`prt_ai_key_*` and all `*_key/_token/_secret` mods redacted); export filename de-branded to `pressroot-settings-*.json`.
- Settings import now runs the code-injection mods (`prt_code_head/body/footer`) through `prt_sanitize_code()`, closing an unfiltered-HTML bypass.
- Hero image importer hardened against SSRF: `wp_http_validate_url()` + `wp_safe_remote_get()` + `image/*` content-type check.
- Contact form: per-IP rate limit (30s), and the hardcoded `[matthummel.com]` subject prefix now uses the site name.

### Accessibility
- Off-canvas menu: `inert`/`aria-hidden` when closed, focus trap while open, focus returned to the toggle on close; toggle button now toggles closed too.
- AI-generated images (per-page + brand hero) get real alt text via `_wp_attachment_image_alt`.
- Cookie notice corrected from `role="dialog"` to `role="region"`.

### Added
- **Marketplace readiness**: full security/accessibility/marketplace audit report (`docs/MARKETPLACE-READINESS.md`), third-party services & privacy disclosure (`docs/THIRD-PARTY-SERVICES.md`) with an AI privacy note in the Setup wizard, `.distignore` for release packaging, and bundled-font license inventory (`resources/fonts/LICENSES.md`).
- **Setup wizard** (`app/setup-wizard.php`): six-step guided onboarding on Appearance → Pressroot — Business info → Connections → WordPress settings → Generate → Review → Launch. First tab and default landing tab until completed once; resumable per-step progress (`prt_wizard_progress`); dashboard/themes welcome notice. Full reference in `docs/SETUP-WIZARD.md`.
  - **Business info step**: business type + industry dropdowns, brand & voice, colors/fonts, logo + photo/video uploads to the Media Library, and new business-fact fields — mission, what-the-business-does, public email/phone/address, per-day business hours (`prt_biz_*` mods) — all compiled into the CORE SITE BRIEF so the AI quotes real facts instead of inventing them.
  - **Connections step**: AI provider keys inline, an SEO plugin selector (built-in / Yoast / Rank Math / All in One SEO, `prt_seo_choice`) with live install/active status, one-click install/activate links, and a beginner's SEO primer; **Google Analytics via GA4 Measurement ID** (`prt_ga4_id`, validated, official gtag.js auto-injected, double-count guard against the manual head-code field) with step-by-step GA and Google Business Profile walkthroughs; addon toggles.
  - **WordPress settings step**: timezone, pretty permalinks, site icon, search-engine visibility (hidden while building), and comment defaults — explained in plain English and applied in one click.
  - **Generate step**: the site-type generator, "AI-write all pages", and brand-image generation sequenced as labeled stages, all returning to the wizard.
  - **Review step**: homepage preview, per-page preview/edit table, and a "where to change what" map (words / images / whole design / chrome).
  - **Launch step**: pre-flight checklist with Fix→ links, then one click publishes every generated draft, promotes a home page to static front page if none is set, and re-opens the site to search engines (`prt_wizard_launched`).
  - **Status bars everywhere**: an overall completion bar under the stepper, plus a per-form animated "working…" bar with step-specific labels on every wizard submit (business save, connections, WP settings, design/pages, AI-write-all, brand image, launch) — sticky, spectrum-filled, parks at 92% until the server finishes, so long AI runs never look frozen.
- `prt_settings_tabs()` registry is now filterable (`pressroot/settings_tabs`); generation handlers honor posted `prt_return_tab`/`prt_return_step` via the new `prt_settings_return_url()` so any surface can reuse them and get its users back.
- Industry list extracted to a shared `prt_brand_industries()` (same `pressroot/brand_industries` filter) used by both the wizard and the Theme Settings tab.

- **Beta program on the docs site**: Friends & Family concierge path (free domain for testers, site free to keep), tester feedback form (`docs/feedback.html`) that emails directly via FormSubmit and generates a pre-filled GitHub tracker issue, branded 1200×630 `og:image` share card on every page, and a Beta badge in the site nav.
- README rewritten around the v1.6 product story: AI site-builder positioning, docs-site links, beta program, and the full model/provider matrix.
- **Design documentation with theme previews** (`docs/DESIGN-SYSTEM.md`): the current Repofolio-iris design language transcribed from the Pressroot Design System Claude Design project — voice, palette, gradients, language bar, type, card anatomy, motion, iconography — plus two SVG preview boards so users can see how the theme looks before installing: `docs/brand/design-language-sheet.svg` (palette/gradients/type/components) and `docs/mockups/theme-previews.svg` (marketing site · generated business site · Setup wizard). Old "Paper + green" docs (BRAND-DESIGN-SYSTEM.md, MOCKUPS.md) banner-marked as historical; linked from README and the docs site.
- Documentation refresh across the board: README gains Setup-wizard and hardening/extensibility feature sections plus links to the new docs; the docs site (`documentation.html`) gains a full Setup-wizard walkthrough with a privacy note; `DEVELOPMENT.md` gains dist-zip packaging (`.distignore` / `wp dist-archive`) and an Extending section documenting the `pressroot/*` namespace; `THEME-SETTINGS.md` intro reflects the six-tab settings page.

### Changed
- **BREAKING (pre-release): the entire public hook + pattern namespace renamed `matthummel/*` → `pressroot/*`** — every `apply_filters()` (site_types, style_kits, fonts, brand_industries, settings_tabs, addon_defaults, cta_*, social_platforms, github_owner, design_trends, …) and every registered pattern slug (`pressroot/home-full` etc.). `app/hooks-registry.php` remains the canonical index. Any child theme/mu-plugin hooking the old names must update; done now, before first sale, precisely because this API becomes permanent once buyers exist.

### Fixed
- All 21 remaining `'sage'` text-domain strings switched to `'pressroot'`; `load_theme_textdomain('pressroot')` now loads `resources/lang/`; pot script renamed `sage.pot` → `pressroot.pot`.
- `style.css` header: added `Tags:` and `Tested up to:`.
- Documented the Playground SQLite corruption recovery ("Could not insert post into the database" → `database disk image is malformed` → iterdump rebuild) in BUILD-NOTES.

## [1.6.0] - 2026-07-08

**One brief, whole sites.** Theme Settings becomes the single prompting surface for the entire site, and builds now generate the full shell — navigation, header, and footer — not just content.

### Added
- **Core AI instructions**: every Theme Settings + Brand answer compiles into one saved "CORE SITE BRIEF" (`prt_core_ai_instructions`) prepended to every AI call; viewer on the settings page shows exactly what the model receives.
- **Site chrome builder** (`prt_build_site_chrome()`): generated "Pressroot Menu" (Home + starter pages) assigned to the primary location, goal-driven header CTA (Get a quote / Shop now / Book now / Subscribe) targeting the most relevant page, brand-driven footer (description tagline, light/dark ground). Runs on apply, 🎲 refresh-all, and Theme Settings auto-build; hand-made menus are never touched.
- **Theme Settings tab** as the owner's front door: Identity + full brand questionnaire (industry dropdown), consolidated with site title/tagline/brand color; auto-build on save with a real-step status bar; sticky saving bar.
- **AI instructions upgrades**: WYSIWYG editor with a live counter and 1,000-word cap, plus uploadable `.md` instruction files (stored server-side, appended to the brief as reference docs, trimmed to prompt-safe length).
- **AI Models tab**: per-provider model dropdowns (incl. `claude-opus-4-8`), validated on save and read; image + video connector registries; keys masked and never sent to the browser.
- **Core-blocks-only generation** (default on): remixed C/D page variants built purely from core Gutenberg blocks + new `prt/smart-hero` and `prt/smart-cta` server-rendered theme blocks with auto-generated copy; no Custom HTML blocks.
- **Edit-screen AI tools**: AI-write / AI-image / new-design actions plus per-role suggested blocks on page and post edit screens.
- **✨ Generate my brand with AI**: drafts voice, audience, goal, and personality from just the name + one-liner.
- **Docs website**: full marketing site in Pressroot branding at matthummel-pa.github.io/pressroot (landing, documentation, become-a-tester, collaborate) plus a restaurant build recipe (`docs/BUILD-RECIPE-RESTAURANT.md`).
- **Attribution**: theme credit ("Pressroot theme by matthummel") in the footer credit line, settings hero byline, and `style.css` copyright header.

### Changed
- Design settings (kit, colors, fonts, corners, hero copy) are now **generated in the backend** from brand answers; manual controls are hidden until the first build, then live in a collapsed fine-tuning panel.
- Site-type previews are gated until the first Theme Settings save, always render the owner's design (bare/brand/kit modes), and bust caches on every build.
- Tab order: AI Models → Theme Settings → Site Types → GitHub → Support; legacy Brand tab merged into Theme Settings.

## [1.5.0] - 2026-07-08

**"Your brand in. Your site out."** Pressroot becomes an AI-assisted design generator wearing the Repofolio brand, with Repofolio itself bundled as a theme addon.

### Added
- **Repofolio theme addon**: the Repofolio plugin bundled under `app/Repofolio/` — GitHub tab on Appearance → Pressroot, OAuth + PAT fallback, repo grid block, `repofolio_project` case studies. The standalone plugin takes over automatically if activated; option keys are shared. Restored `prt_github_get()` and the `App\Github` facade.
- **Brand tab**: a 12-question, plain-language brand questionnaire (identity, color, light/dark, personality, audience, industry, voice, goal, imagery, density, trend) that steers all generation; opt-in sync to site title/tagline.
- **Design generator**: applying/refreshing a Site Type now deals a random pattern variant per page, a random Style Kit from a per-type pool, and a random design trend — brand-filtered, never repeating the current deal; existing pages are refreshed (not skipped) so old designs never linger; branding is re-asserted after every deal.
- **Remix engine** (`app/site-type-remix.php`): programmatically generated C/D variants for every page of every site type (~50 patterns) from seeded hero/feature/CTA pools.
- **Three new Site Types**: Affiliate Marketing, Restaurant/Café, Real Estate (hand-built A/B patterns each).
- **Six new Repofolio-family Style Kits** + a reserved **Core Marketing** kit exclusive to the Marketing type.
- **Six design trends** as CSS-only layers: Bento spectrum, Glassmorphism, Neo-brutalist, Editorial serif, Swiss minimal, Retro pop.
- **✨ AI Builder** (`app/ai-builder.php`): per-page / per-type "Write with AI" fills Gutenberg pages with brand-profile copy (text-only replacement — markup is never AI-generated); AI brand-image generation into the Media Library; "Edit blocks" shortcuts.
- **"Powered by AI — or not"**: one Brand-tab switch (`prt_ai_features_enabled()`) disables every AI-calling feature while the generator keeps working.
- **Site Type preview upgrades**: cards preview all four designs per page, each rendered in the type's own kit (`prt_preview_kit`), with no-cache headers + cache-busted URLs.
- `npm run refresh` (ESLint + Pint + Vite build), `npm run lint`, `npm run lint:php`.

### Changed
- **Full Repofolio rebrand**: token swap to Iris/Ink/Paper + spectrum accents, brand + spectrum gradients, 8px spectrum bar site-wide, spectrum-topped cards, gradient pill buttons (core block buttons included), dark radial homepage hero, dark footer defaults, docs-site chrome for Appearance → Pressroot.
- **Generic base-theme hero**: every hero string is now a Customizer mod with neutral defaults (tagline: "Your brand in. Your site out."); Brand profile supplies smarter defaults. Personal copy removed.
- New `screenshot.png` in the Repofolio design; `style.css` description leads with the tagline.
- AI copy prompt is now site-type + brand aware and takes a fresh angle per run.

### Fixed
- Pattern previews render the real design system (the preview route now fires `prt_head_end`) and are never served stale (nocache headers, versioned URLs, `filemtime`-versioned admin CSS, critical-CSS flush on every deal/brand save).

## [1.4.0] - 2026-07-05

**Pressroot AI, Site Types, and a consolidated settings page.** The theme's admin experience moved from four separate Appearance pages to one, and its AI-assisted setup grew from a one-off "generate some copy" tool into the theme's primary way to start a new site.

### Added
- **Pressroot AI — Site Types**: pick a business category (Agency/Studio, Freelance/Portfolio, SaaS/Startup, Blog/Content site, Marketing/Landing page) to apply its matching Style Kit and create starter pages together, each pre-filled with one of two hand-built pattern variants. Live, scaled-down previews of each type's first page render before you commit.
- **Regenerate**: swap any starter page — or every page in a site type at once — to its other hand-built variant with one click.
- **Starter hero copy generator**: a one-line business description in, a draft headline + subheadline out.
- **AI Connectors**: optional free API keys for Google Gemini, Groq, and OpenRouter, alongside the always-on, keyless Pollinations default, selectable per-generation from a model dropdown. All generation is proxied server-side — API keys never reach the browser.
- **AI in the block editor**: a "Generate with AI" toolbar button on paragraph, heading, and list blocks for everyday content editing, not just initial setup.
- **Theme Addons**: a new Customizer section to switch the entire Pressroot AI feature (menu entry, endpoints, admin-post actions) off if a site doesn't want the AI surface at all.
- **Appearance → Pressroot**: Theme Tools, Starter Sites, Pressroot AI, and GitHub — four separate admin pages — are now one page with a left-sidebar menu and the active section's content on the right.
- **Support tab**: live status for the theme's own GitHub repository (stats, languages, latest releases, open issues) via the existing GitHub data engine, plus a curated, filterable list of links to the theme's documentation. Always visible, independent of the Pressroot AI addon toggle.
- **Editable Docs/Support links** in the settings page header, defaulted to the theme's real repo.
- **Export / Import / Reset** moved into a collapsed "Advanced" section on the Site Types tab (same tool, new home).

### Changed
- The former "Pressroot AI" tab is renamed **Site Types**, reflecting its role as the primary "set up your site" tab.
- The standalone **Style Kits** tab is retired — every Site Type already applies its own matching kit automatically, so a separate manual picker was a second way to do the same thing. The underlying Style Kit data/apply logic is unchanged and still powers Site Types.
- The **Starter Sites** demo importer is retired in favor of Site Types (more personas, regenerate, live previews, dedicated per-type patterns); its dashboard "Create starter pages" button (blank pages, no design) is removed the same way.
- README and the settings reference now document Pressroot AI, Site Types, the consolidated settings page, WP-CLI, Dev Mode, Reading UX, Pattern Library, and Google Fonts Collection — all previously undocumented — and the stale "17 starter patterns" count is corrected to the actual 22 general-purpose + 26 Site Type patterns.
- Repository references corrected from `matthummel-pa/matthummel-theme` to `matthummel-pa/pressroot` throughout the code and docs.
- Leftover "Matt Hummel"/"Matthummel" branding in user-facing strings (pattern category labels, the dashboard widget title, Pattern Library help text) now reads "Pressroot".
- README, the Support doc, and the in-admin Support tab now include a short origin story: Pressroot started as the author's personal portfolio theme and was generalized into a rebrandable framework, with Site Types as the piece aimed at multiple business categories.

### Fixed
- A dev-seed mu-plugin collided with Pressroot AI's page slugs, corrupting preview content on some installs.
- Switching site types now force-deletes the previous type's pages (bypassing Trash) instead of leaving them to accumulate.
- A `prt/skills-grid` pattern attribute mismatch (wrong key/shape) that silently fell back to placeholder card content.
- Block-editor pattern/thumbnail previews that depend on a `ServerSideRender` REST round-trip now show an instant static skeleton instead of staying blank inside lightweight preview surfaces (pattern thumbnails, the "Choose a pattern" modal).
- Corrected an inaccurate claim in the docs that the Starter Sites importer's patterns were mostly dead — they weren't; the decision to retire it in favor of Site Types stands on its own merits (see BUILD-NOTES.md for the full correction).

## [1.1.0] - 2026-07-01

Imported upstream matthummel-theme v1.2.0–v2.0.0 framework updates, adapted to
Pressroot naming (`prt_` / `prt-` / `prt/`) and the existing Sage 11 + Roots + Vite
structure. Bud/Tailwind-config files, personal site content, and dev-only scripts
were intentionally not imported.

### Added
- **Bespoke blocks** — `app/blocks-bespoke.php` registers six editor blocks (`prt/stat-strip`, `prt/skills-grid`, `prt/timeline`, `prt/resource-group`, `prt/cta-band`, `prt/project-card`) with per-block editor scripts in `resources/js/prt-*-editor.js`.
- **Section block** — `app/block-section.php` (`prt/section`) generic section wrapper with editor script.
- **Block patterns** — `app/block-patterns.php`: 12 full-page/section patterns under the Pressroot category, built from the bespoke blocks and `design-language.css` classes.
- **Pattern Library admin page** — `app/pattern-library.php` (Appearance → Pattern Library) with copy-pattern-name helper.
- **Social Links Customizer section + Quick Setup panel** — `app/social-links.php`, `app/quick-setup.php`.
- **Page templates** — `template-about`, `template-blog`, `template-legal`, `template-projects`, `template-resources`, `template-resume` Blade templates.
- **Projects experience** — redesigned `template-projects.blade.php` (dark hero with live GitHub stat strip, category filter pills, live repo grid via `Github::fetchRepos()`, featured manual projects) and `single-projects.blade.php` (dark hero, tech-stack pills, GitHub stats band, related projects). GitHub owner resolves from the `prt_proj_owner` theme mod.
- **Projects admin** — `_prt_tech_stack` and `_prt_featured` meta fields; REST endpoint `GET /wp-json/prt/v1/github-repos` proxying `Github::fetchRepos()` with transient caching.
- **Reading progress JS** — `resources/js/reading-progress.js` (progress bar, auto TOC, code copy buttons) driving new markup in `partials/content-single.blade.php`; `app/reading.php` slimmed to the read-time badge only to avoid duplicate bars/TOCs.
- **CSS** — `page-templates.css`, `blocks.css`, `design-language.css`, imported from `app.css`.
- **Expanded Google Fonts library** — grouped font catalog in `app/customizer.php` behind a `matthummel/fonts` filter.
- **Redesigned blog views** — `archive.blade.php` editorial post list, `content-single.blade.php` single-post layout, refreshed `sections/header.blade.php`.
- `config/view.php` — compiled Blade views moved to `wp-content/uploads/acorn-views` (fixes Windows file-permission conflicts).
- `.wp-now.json` for the wp-now local dev workflow.

### Changed
- `projects` CPT: `has_archive` → `false` so the Projects page template is served at `/projects` instead of the CPT archive.
- `functions.php` loads the six new app modules.

### Fixed (upstream v1.9.x–v2.0.0 audit)
- `app.css`: defined missing `--color-paper` token; dark-mode overrides for `.form-error`, `.btn-outline`, sticky-header glass effect.
- `page-templates.css`: dark-mode backgrounds for project heroes, blog hero image, and filter pills.
- Blade `@php(...)` inline-syntax parse errors in projects templates (block form used throughout).

## [1.0.0] - 2026-06-26

### Added
- Initial Pressroot release — a rebranded, namespaced (`prt_` / `prt-` / `pressroot`) productization of the Sage 11 theme framework.
- Design system + Style Kits, dark mode, `theme.json` controls.
- Hero builder (editable copy, 1–3 columns, flexbox, side/background images), built-in image finder (Openverse / Unsplash / Pexels / AI), and site-wide on-scroll animations.
- Header layout + flexbox navigation + off-canvas popout, Social Icons, top bar, scheduled announcement bar, footer builder, and per-device responsive controls.
- Dynamic blocks + 17 starter patterns, live GitHub project pages, plugin-free contact form, newsletter, cookie notice, code injection.
- Performance suite (bloat control, self-hosted fonts, split/critical CSS), SEO + JSON-LD, and white-label / onboarding.
