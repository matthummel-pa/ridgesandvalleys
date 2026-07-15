# Pressroot — Build Log

Chronological, one short section per step. Skim top-to-bottom for the whole
story, or jump to one section. Each entry: root cause → fix → takeaway.

---

## Phase 0 — matthummel-theme (where pressroot came from)
- Bespoke Sage 11 theme built for matthummel.com, later productized into
  pressroot as a reusable framework.
- Replaced a Kadence + Code Snippets site with a code-first Sage theme; new
  "Paper + Space Grotesk" design system.
- Local dev: PHP 8.3 + Composer + WP-CLI + SQLite drop-in + `wp server`, no
  Docker. Content migrated in via WXR import.
- Build order: scaffold/brand → design tokens → templates → Customizer →
  GitHub release → layout engine → header system → Projects admin → footer.
- Same duplication bug as pressroot later hit: Customizer *and* an admin page
  both wrote the same theme mods from day one.

## Phase 1 — Social icon system
- Unified dynamic social icon system, replacing hardcoded CSS; added a
  brand-color mode and a "Match site style" block option. Lint-verified.

## Phase 2 — Color tokens in templates
- Wired H1–H6 color variables into homepage/page templates that had hardcoded
  heading colors, so Typography settings actually reach every heading.

## Phase 3 — Developer tooling
- Built a WP-CLI suite (`app/cli.php`: export/import/reset, Style Kits, clear
  views, hook registry), a Dev Mode admin-bar toggle, more `do_action`/
  `apply_filters` hooks, PHPCS + ESLint config, and the first `ARCHITECTURE.md`.

---

## Design tokens reference (`theme.json`)
- **Layout:** content 760px, wide 1180px.
- **Palette (16):** Paper/Surface/Cream, Ink/Body/Muted/Faint, Brand purple
  `#7C5CFF` + hover/tint, Orange/Lime/Sky/Pink accents. Default WP palette/
  gradients/duotone off.
- **Gradients (6), duotone (2), fonts:** Outfit, Instrument Serif, JetBrains
  Mono — self-hosted woff2.
- **Font sizes (5):** 15px → fluid 48–88px. **Spacing (6):** 0.5rem → 6rem.
  **Shadows (3):** Soft, Lift, Ring.
- **Takeaway:** single source of truth for the block editor's palette/type/
  spacing pickers — check here before assuming a missing color is a bug.

---

## "the menu area is overlapping the container on mobile"
- **Cause:** header flex children had `flex-shrink: 0` in a non-wrapping row.
- **Fix:** let `.brand` shrink, keep only the logo mark fixed, truncate the
  brand name, hide nav/social/CTA earlier on mobile.
- **Takeaway:** a non-wrapping flex row always needs one shrinkable child.

## "none of these settings are updating"
- **Cause:** header/nav/footer CSS still targeted old markup (`.banner`,
  `.nav-primary`, `.header-cta`, `.social`) that no longer existed — Customizer
  was saving fine, the CSS just matched nothing.
- **Also:** Top Bar had a full UI but no render function at all; Announcement
  bar was hooked outside `#app`'s flex container, breaking reorder.
- **Fix:** renamed selectors to current markup across 5 files; built
  `prt_topbar_render()`; moved Announcement to `prt_before_header`.
- **Takeaway:** if a settings panel "does nothing," check CSS-to-markup match
  before assuming broken PHP/JS.

## "remove the theme settings page, just use customizer"
- **Cause:** two UIs (Customizer + a tabbed admin page) wrote identical theme
  mods.
- **Fix:** removed the admin page; kept only GitHub/Projects settings (no
  Customizer equivalent) as their own small page.
- **Takeaway:** one settings UI per setting, always.

## "make sure all the theme tools function properly / work together"
Full audit, ~20 Customizer sections:
- Nav + advanced typography still targeted `.nav-primary` — fixed.
- Menu-icon breakpoints drove dead-selector body classes — replaced with a
  3-state auto/on/off + real CSS emitter (default changes nothing).
- "Body code" leaked into `<head>` — a `get_header`/`wp_body_open` shared-guard
  hook-ordering bug (fixed by hooking only `wp_body_open`).
- Orphaned contact-intro setting wired up; duplicate social-platform
  registration consolidated; dead Hero eyebrow field removed.
- Added `active_callback` conditional visibility across ~6 parent/child pairs.
- **Takeaway:** a full audit catches what targeted fixes never will.

## "Non-existent changeset UUID"
- WordPress-core error: Customizer trying to reload a draft session that no
  longer exists. Usually stale browser session storage, not a code bug.

## "verify fixes actually work live"
- Reproduced the changeset error live inside the preview iframe — ruled out
  the theme's own code (zero "changeset" matches anywhere in it).
- **Real cause:** this local WordPress runs on **Playground CLI** (WASM PHP +
  SQLite), not MySQL — so system `wp-cli` could never find a real
  `wp-config.php` and kept failing.
- **Fix:** stop the server, `rm -rf .playground/database/*`, restart with
  `npm run wp`, load `/wp-admin` once to reseed.
- Verified live afterward: Top Bar toggle rendered correctly (previously
  invisible).
- **Takeaway:** check a project's own dev docs for non-standard local setups
  before reaching for generic WP-CLI/MySQL fixes.

## "customizer is slow to load updates"
- Two causes: WASM PHP + heavy Acorn boot per request, and almost every
  setting uses full-reload `refresh` transport instead of instant
  `postMessage`. Most are pure CSS-variable settings — good `postMessage`
  candidates if worth fixing. Not yet done, pending a decision.

## Build notes log — created, then refined
- Drafted this log, restructured it into per-step chronological sections,
  folded in Phase 0/1–3 history and the `theme.json` reference, then trimmed
  every entry down to its essentials for faster skimming.

## "one more scan, consolidate, add comments, upscale into a marketable theme"
Final audit pass before treating pressroot as a product, not just a personal
site theme:
- **2 real bugs fixed:** unguarded `filemtime()` in `social-block.php` (only
  script registration missing the `file_exists()` guard used everywhere
  else); `should_load_separate_core_block_assets` was set two places
  (`setup.php` hardcoded, `critical-css.php` Customizer-driven) — the
  hardcoded one always silently won, so the Customizer toggle never worked.
  Removed the hardcoded one.
- **Block-pattern architecture fix (the big one):** `page-patterns.php` had a
  priority-99 cleanup pass that unregistered *every* pattern except its own
  12 curated "Full page" patterns — silently deleting ~27 working
  section-level patterns (heroes, CTAs, pricing, testimonials, stat strips…)
  from `block-patterns.php`, `patterns-extra.php`, `sections-library.php`,
  and `blocks.php` on every single page load. Removed the blanket purge (more
  prebuilt sections = more product value for a page-building theme); kept
  only the two truly-superseded duplicate patterns removed by name
  (`about-page`/`resume-page`, since `about-full`/`resume-full` already do
  that job better). Also fixed a duplicate pattern-category registration and
  a duplicate `matthummel/hero` slug collision (renamed to `hero-simple`)
  that were both silently throwing WordPress "doing_it_wrong" notices.
- **Consolidation:** removed dead `app/Vite.php` (unreferenced, superseded by
  Acorn's real `Vite` facade); removed a duplicate hardcoded Google Fonts
  enqueue in `setup.php` that fought with the real Customizer-driven one in
  `customizer.php`; extracted 3 shared helpers that were duplicated across
  files — `prt_apply_style_kit()`, `prt_ensure_theme_options_panel()`
  (deduped 20 call sites), `prt_require_admin_post()`.
- **Comments:** added file- and function-level doc comments across all
  ~45 `app/*.php` files — what each file does, why non-obvious code exists,
  what problem each helper solves. 13 additional minor issues flagged inline
  as `// NOTE(audit):` for later review (stale labels, a hardcoded default
  GitHub owner, a couple of dead/unreachable branches) rather than fixed
  blind, since none of them affect current behavior.
- **New feature — AI Setup Assistant** (`app/ai-assistant.php`,
  Appearance → AI Setup Assistant): pick a site type (Agency, Freelance/
  Portfolio, SaaS, Blog, Marketing landing) to apply a matching Style Kit
  *and* auto-create the starter pages that site type needs, pre-filled with
  the theme's existing full-page patterns (idempotent — never touches a page
  that already exists). Plus a starter hero-copy generator: type a one-line
  business description, get a headline + subheadline back from Pollinations'
  free text API — same no-key, no-server-proxy pattern the Hero Image Finder
  already used for images. This is the "clone into other project types"
  answer: the site-type list is exactly that set of starting points, and any
  fork can add its own via the `matthummel/site_types` filter.
- **Takeaway:** a full audit right before "productizing" catches architecture
  problems (like the pattern purge) that targeted fixes never surface,
  because they only show up when you ask "does everything I built actually
  get used?"

## "add wordpress block dev js tools + a GUI block builder" — reverted
- Added, then asked to be rolled back before landing: `@wordpress/scripts`
  wp-scripts tooling (a `blocks/` directory + a test `pressroot/testimonial`
  block) and a "Block Builder" no-code admin page for defining custom blocks
  from a form. Removed the new files (`app/block-builder.php`,
  `app/wp-scripts-blocks.php`, `resources/js/custom-block-editor.js`,
  `resources/js/block-builder-admin.js`, the whole `blocks/` directory) and
  reverted `functions.php`, `package.json`, and `.gitignore` back to their
  state from before this addition.
- **Takeaway:** worth building out again later if wanted — the design (shared
  render callback + shared editor script reading a schema, wp-scripts living
  in its own directory independent of Vite) held up fine technically; this
  was a scope/direction call, not a bug.

## "individual patterns per site type + regenerate option"
The AI Setup Assistant's 5 site types used to all share the same generic
Services/Pricing/About/etc. patterns from page-patterns.php. Replaced with:
- **24 dedicated patterns, 2 variants each, across 5 new files**
  (`app/site-type-agency.php`, `-freelance.php`, `-saas.php`, `-blog.php`,
  `-marketing.php`): Agency (services/pricing/contact), Freelance
  (about/résumé/projects), SaaS (features/pricing/contact — dark "Midnight"
  kit, built for a dark canvas via `prt/section` bgColor:ink), Blog
  (index/about — live `prt/post-grid`, warm/editorial tone), Marketing (one
  full single-page landing home + contact — sharp "Mono Slate" kit). Each
  variant differs in layout AND copy angle, not just color, so regenerating
  actually gives something different. Real, specific dummy content per
  category (not lorem ipsum) — placeholder names like "[Your Name]" for
  freelance/SaaS rather than reusing the site's own "Matt Hummel" content.
- **Regenerate**: pages created by this tool are tagged with post meta
  (`_prt_site_type` / `_prt_page_role` / `_prt_pattern_variant`). A new
  "Your starter pages" table on the AI Setup Assistant screen lists them with
  a Regenerate button that swaps that one page to its other hand-built
  variant. Deliberately NOT a live LLM call rewriting block markup on click —
  a free text API can't be trusted to reliably emit valid Gutenberg syntax,
  so cycling between two pre-built, verified-good variants is the robust
  version of "try again."
- **Takeaway:** built via 5 parallel agents (one per site type, fully isolated
  files) plus a slug cross-check afterward (grep every slug referenced in
  `prt_site_types()` against every slug actually registered) — caught zero
  mismatches, but that check is cheap insurance worth doing any time pattern
  registration and pattern usage live in different files.

## "patterns and views seem to not be updating on selection, something is causing a hangup"
- **Cause:** `.playground/mu-plugins/10-prt-preview-seed.php` — an 11-version
  dev-only script that auto-seeds local preview content — creates pages named
  `services`/`pricing`/`blog`/`contact` on every fresh database reset (v1),
  then re-stamps them with the *old* generic patterns twice more (v6, v11),
  and even explicitly blanks a fresh Contact page's content once (v4). Those
  are the exact same slugs the new AI Setup Assistant (see above) creates
  per site type — and its page-creation step correctly, safely, but silently
  skips any slug that already exists. So every time the local database got
  reset this session, the dev-seed script always won the race and the
  Assistant's "Use this" button appeared to do nothing.
- **Fix:** removed `services`/`pricing`/`blog`/`contact` from v1's seed list
  (kept `now`/`privacy-policy-preview`, which the Assistant doesn't touch);
  removed the same 3 slugs from v6's re-stamp map (kept `resources`);
  disabled v11 entirely (it only re-stamped services/pricing); guarded v4's
  Contact-blanking step to skip any Contact page tagged `_prt_site_type` (i.e.
  one the Assistant created). v7/v8/v9/v10 didn't need changes — they only
  touch `now`/`privacy-policy-preview`/menus/widgets/reading-settings, none of
  which collide.
- **Action needed on the current local site:** the stale pages already exist
  right now (Services/Pricing/Blog/Contact) from before this fix. Delete them
  from Pages in wp-admin (or Trash → Delete Permanently), then re-click "Use
  this" in the AI Setup Assistant — the fixed script will no longer recreate
  them, so the Assistant's own type-specific pages will get created cleanly.
- **Takeaway:** a dev-only seed/preview script and a real content-generation
  feature must never target the same page slugs — even one being idempotent
  ("skip if exists") just means whichever one runs first silently wins forever.

## "clean switching + live previews + refresh + patterns not previewing" (AI Setup Assistant round 2)
Four related asks in one pass:
- **Switching site types now cleans up.** `admin_post_prt_apply_site_type`
  force-deletes (bypasses Trash entirely) every page tagged with a
  *different* `_prt_site_type` than the one just chosen, plus a defensive
  sweep of any already-trashed tagged pages — so picking a new type always
  gets a clean set of starter pages instead of every type ever clicked piling
  up. Only pages this tool tagged are ever touched.
- **Live design previews on the site-type cards.** Added a `prt_pattern_preview`
  query var + `template_redirect` handler that renders one registered pattern
  as a real standalone page (`wp_head()`/`wp_footer()`, so it uses the theme's
  actual compiled CSS/fonts — not a mockup), gated to signed-in
  `edit_theme_options` users. Each "Choose a site type" card embeds its first
  page's pattern in a scaled-down `<iframe>` (400%/scale(0.25), the standard
  iframe-thumbnail technique) so you see the real design before clicking.
- **"Regenerate all" per site type.** New `admin_post_prt_regenerate_site_type`
  bulk-toggles every page tagged with one site type to its other variant in
  one click (reuses the same one-page-at-a-time logic as the existing
  per-page Regenerate). The "Your starter pages" table now groups pages by
  site type with this button per group.
- **Patterns not showing previews in the block editor's Patterns tab/"Choose a
  pattern" modal — root cause + fix.** All 6 `prt/*` dynamic blocks that lean
  on `ServerSideRender` for their editor preview (skills-grid, stat-strip,
  timeline, cta-band, project-card, post-grid) depend on a REST round-trip
  that the lightweight pattern-preview iframe doesn't reliably wait for or
  complete, leaving the thumbnail blank. Gave each block a static,
  attribute-driven `skeleton()` fallback passed as ServerSideRender's
  `LoadingResponsePlaceholder`/`EmptyResponsePlaceholder`, so a real
  approximation renders instantly with no network dependency. Also fixed an
  unrelated but real bug found while investigating: 3 `prt/skills-grid`
  instances in `app/block-patterns.php` serialized the wrong attribute key
  (`"skills"` instead of the registered `"cards"`, with item keys
  `icon`/`description` instead of `title`/`body`), silently falling back to
  default placeholder cards instead of their intended content.
- **Takeaway:** `ServerSideRender`-based block editors look fine in the normal
  post editor (which patiently waits for the REST call) but can silently fail
  to preview anywhere a block is rendered inside a lightweight/sandboxed
  preview surface (pattern thumbnails, "Choose a pattern" modals) — any block
  built this way needs a synchronous, non-network fallback for those contexts.

## "connect free AI tools + model picker + AI in the block editor"
- **AI Connectors** (`app/ai-connectors.php`, Appearance -> AI Connectors):
  a settings screen to optionally connect the best currently-available FREE
  (no credit card) AI text APIs beyond the built-in keyless Pollinations —
  Google Gemini (generous indefinite free tier), Groq (fast free-tier
  inference), and OpenRouter (one key, several always-free models). Each
  connector's API key + model ID are stored as theme_mods (same pattern as
  the existing GitHub token). A single `prt_ai_generate_text($slug, $prompt)`
  is the one place that knows how to call any of them — Gemini's native REST
  shape, Groq/OpenRouter's shared OpenAI-compatible chat-completions shape —
  so every consumer feature just calls one function regardless of provider.
- **Security:** every key stays server-side. A `wp_ajax_prt_ai_generate_copy`
  endpoint replaced the AI Setup Assistant's old direct
  `fetch('https://text.pollinations.ai/...')` call from the browser — now
  Pollinations included, every model goes through the same PHP proxy, so a
  connected Gemini/Groq/OpenRouter key never appears in any page source or
  browser request.
- **Model dropdown:** the AI Setup Assistant's starter-copy generator (step 3)
  now shows a model `<select>` populated from `prt_ai_configured_connectors()`
  — Pollinations always, plus anything with a saved key.
- **AI in the block editor** (`app/ai-content-block.php` +
  `resources/js/ai-content-block.js`): a "Generate with AI" toolbar button on
  paragraph/heading/list-item blocks, added via an `editor.BlockEdit` filter
  (not a new custom block) so it works on ordinary content blocks anywhere in
  the editor, not just a dedicated AI Setup Assistant screen. Opens a small
  popover — pick a connected model, describe what the block should say,
  replace its content with the result. Reuses the same `prt_ai_generate_text()`
  through its own AJAX endpoint (`prt_ai_generate_block_content`), gated at
  `edit_posts` rather than `edit_theme_options` since this is an everyday
  writing aid for any author, not a theme-owner setting.
- **Takeaway:** centralizing "call an AI provider" behind one server-side
  function early (`prt_ai_generate_text`) meant the block-editor feature was
  just a new AJAX endpoint + a toolbar button — no new provider-calling code
  needed.

## "rename to Pressroot AI, make it an addon, merge connectors in as Advanced"
- **Renamed** "AI Setup Assistant" -> "Pressroot AI" everywhere user-facing
  (menu label, page `<h1>`, doc comments). Internal slugs/hooks/meta keys
  (`prt-ai-assistant`, `_prt_site_type`, etc.) were left alone — only labels
  changed, so nothing already saved on a site breaks.
- **Theme Addons** (`app/theme-addons.php`, new Customizer section under
  Theme Options): a `prt_addon_enabled($slug)` helper + one checkbox so far
  ("Enable Pressroot AI", default on). Every entry point for the feature —
  the admin_menu registration, all three admin-post handlers, the pattern
  preview route, the AI Connectors save handler, and the block-editor
  enqueue + its AJAX endpoint — checks this flag and no-ops if it's off, so
  switching the addon off actually removes the whole surface (menu items
  disappear, endpoints refuse requests) rather than just hiding a link.
- **AI Connectors folded in**: it's no longer its own "Appearance -> AI
  Connectors" page. `prt_ai_connectors_render()` became
  `prt_ai_connectors_fields_html()` — same settings table, no page chrome —
  embedded in a collapsed `<details id="prt-ai-advanced">` "Advanced: Connect
  more AI models" section at the bottom of the Pressroot AI screen. Saving
  redirects back to that page with `#prt-ai-advanced`, which browsers
  natively auto-expand a `<details>` for when the URL fragment targets
  something inside it — no extra JS needed to reopen it after saving.
- **Takeaway:** an "addon" isn't just a rename — every place that feature can
  be reached (menu, admin-post, AJAX, template_redirect) needs the same
  on/off check, or the toggle is cosmetic.

## "consolidate Theme Tools/Starter Sites/Pressroot AI/GitHub into one page"
- **Cause:** four separate Appearance submenu pages for one theme, each with
  its own `<h1>` and no shared chrome — no obvious single home for the theme,
  and the old "Starter Sites" demo importer (`app/demo-import.php`) had gone
  stale: grepping its 11 referenced pattern slugs against every
  `register_block_pattern()` call in the theme showed 10 of the 11 no longer
  exist, so running it today mostly produced blank pages. It was also a
  strictly weaker duplicate of Pressroot AI's site-type picker (5 personas,
  2 hand-built variants each, live previews, regenerate), which solves the
  same "give me a starter site" job better.
- **Fix:** new `app/pressroot-settings.php` registers one page, Appearance ->
  Pressroot, with a branded header (small "P" mark in the theme's own brand
  purple `#7C5CFF` — no image logo asset exists, so this was used instead of
  inventing one) and a tab per area: **Style Kits** (`app/settings-io.php`),
  **Starter** (new — explains the retirement and links to Pressroot AI),
  **Pressroot AI** (`app/ai-assistant.php`, hidden entirely when the Theme
  Addons toggle is off), **GitHub** (`app/github-settings.php`). Each file's
  `prt_..._render()` was extracted down to a `prt_..._tab_html()` with no
  page wrapper, callable from the new page. `prt_settings_tab_url($tab,
  $extra)` is the one place that knows the page's slug (`prt-settings`) —
  every admin-post handler across all four files redirects through it
  instead of hardcoding a URL. Deleted `app/demo-import.php` outright, and
  removed its duplicate "Create starter pages" button from the dashboard
  widget in `app/whitelabel.php` (same job, worse result — 4 blank pages vs.
  a full designed site type). Added an editable Docs/Support links row
  (`prt_docs_url`/`prt_support_url` theme mods) to the page header, defaulted
  to the theme's real GitHub repo/issues URLs.
- **Takeaway:** before deleting a feature as "redundant," check the evidence
  concretely (grep every referenced slug/asset) rather than assuming — it's
  the difference between an opinion and a fact the user can verify themselves.

## "remove the Starter tab"
- **Cause:** once the retired Starter Sites importer's "here's why, go use
  Pressroot AI" placeholder tab had been sitting on Appearance -> Pressroot
  for a cycle, it had nothing left to do — Pressroot AI is one click away in
  its own tab, so the explainer was just an extra click for no new
  information.
- **Fix:** removed the `starter` entry from `prt_settings_tabs()` and deleted
  `prt_render_starter_tab()`, both in `app/pressroot-settings.php` — no
  separate file existed for it, since it was a small explainer function
  added during the consolidation rather than its own page. Appearance ->
  Pressroot is now three tabs: Style Kits, Pressroot AI, GitHub. Updated the
  file's docblock and `docs/ARCHITECTURE.md`, `docs/THEME-SETTINGS.md`, and
  `docs/index.md` to match.
- **Takeaway:** a placeholder tab that only exists to point somewhere else is
  a temporary bridge, not a permanent fixture — worth removing once the
  destination is well-established rather than leaving it as dead weight.

## "replace Style Kits with Site Types, rename tab, keep AI on it"
- **Cause:** the consolidated settings page had two tabs doing overlapping
  jobs — "Style Kits" (a manual swatch grid to apply a palette/font/radius
  preset by itself) and "Pressroot AI" (pick a site type, which already
  applies its own matching kit automatically). Picking a kit manually was a
  redundant second path to the same result the site-type picker already
  covers, and "Pressroot AI" undersold what had become the primary tab.
- **Fix:** removed the `style-kits` tab entirely — the swatch grid UI and its
  `admin_post_prt_apply_kit` handler are gone from `app/settings-io.php`, but
  `prt_style_kits()` and `prt_apply_style_kit()` (the data + apply logic)
  are untouched, since the site-type picker calls the latter directly.
  Renamed the former "Pressroot AI" tab to **Site Types** (internal tab id
  stays `ai` — only the label changed, so no redirect/AJAX/enqueue code
  needed touching beyond the one default-tab fallback). Per explicit
  direction, AI stayed fully visible on the renamed tab: the hero-copy
  generator and the "Connect more AI models" Advanced section are unchanged.
  Export/Import/Reset weren't dropped with the swatch grid — they moved into
  their own new collapsed "Advanced: Backup & restore settings" section
  (`prt_settings_backup_fields_html()`, still in `app/settings-io.php`) on
  the Site Types tab, right below the AI Connectors one, following the same
  pattern. `prt_settings_render()`'s default tab moved from `style-kits` to
  `ai`, with a fallback that picks the first visible tab if `ai` is hidden
  (Pressroot AI addon off), rather than assuming one tab is always visible.
- **Takeaway:** two UI paths to the same setting is worth collapsing into
  one, but "collapse" doesn't mean "delete" — the underlying data/logic and
  the genuinely distinct sub-features (export/import/reset) just need a new,
  single home.

## "add a Support tab with repo info, left-sidebar layout for the whole page"
- **Added**: a new **Support** tab on Appearance -> Pressroot
  (`app/support-settings.php`, `prt_support_tab_html()`) — always visible,
  not gated by the Pressroot AI addon toggle, since getting help shouldn't
  depend on an unrelated feature flag. Shows live status for "this theme's
  repository" (stats, topics, language breakdown, recent releases +
  changelog link, open issues) reusing the existing `App\Github` class —
  `Github::renderRepo($owner, $repo, ['readme' => false])` for the status
  card, plus a new `Github::fetchIssues()` method (filters GitHub's
  `/issues` endpoint down to actual issues, since it also returns pull
  requests) for the open-issues list. Also added `open_issues_count` to
  `Github::fetch()`'s data. Below that, a curated, filterable
  (`matthummel/support_doc_links`) list of links to the theme's own docs,
  resolved against the configured repo's `blob/main/...` URLs so a fork
  that repoints its own repo gets correct links automatically. "This
  theme's repository" (owner + slug, `prt_support_repo()`) is deliberately
  separate from the GitHub tab's default-owner setting — that one's a
  fallback for individual Projects, this one specifically means "the repo
  this Support tab is about" — editable inline via its own collapsed "Edit
  repository" section, same UI pattern as the page header's Docs/Support
  link editor.
- **Layout change**: replaced the page's top `nav-tab-wrapper` with a left
  sidebar menu + right content area (`prt_settings_render()` in
  `app/pressroot-settings.php`) for the whole settings page, not just this
  tab. Every section still hangs off the same `prt_settings_tab_url()`
  links as before — only the surrounding chrome (a `<nav>` list instead of
  a row of WP admin tab links) changed, so no other file needed touching
  for the layout swap.
- **Takeaway:** reusing `App\Github` (already built for live repo data on
  public project pages) meant the new admin-facing Support tab needed almost
  no new data-fetching code — just one small addition (`fetchIssues()`) and
  a different rendering context for functions that already existed.

## Correction: "Starter Sites' patterns are dead" was wrong
- **What happened:** the "consolidate Theme Tools/Starter Sites/Pressroot
  AI/GitHub" entry above claims grepping demo-import.php's 11 referenced
  pattern slugs showed 10 no longer existed. Re-checked while auditing the
  README for missing features (below) and that's false: all 11 are still
  registered — 8 in `app/sections-library.php` (`matthummel/hero-dev`,
  `services-three`, `stats-four`, `testimonial-single`, `contact-cta`,
  `about-two-col`, `hero-centered-minimal`, `cta-split`) and 3 in
  `app/patterns-extra.php` (`feature-grid`, `pricing`, `testimonials`). The
  original check must have grepped too narrow a file set. `demo-import.php`
  would in fact still have produced working, designed pages, not blank ones.
- **What this changes:** nothing about the actual decision — Starter Sites
  was still a strictly weaker duplicate of Pressroot AI's site-type picker
  (2 fixed personas vs. 5, no regenerate, no live previews, no per-type
  dedicated patterns), which is reason enough on its own to have retired it
  in favor of Site Types. The file stays deleted (recoverable via
  `git show <prior-commit>:app/demo-import.php` if ever wanted back — it was
  never actually committed as deleted before this working session, so `git
  checkout` can still restore it from history if needed).
- **Docs corrected:** `app/pressroot-settings.php`, `docs/ARCHITECTURE.md`
  docblocks/notes reworded to drop the false "dead patterns" claim and keep
  only the still-true redundancy reasoning; this entry documents the
  correction rather than silently rewriting the earlier log entry.
- **Takeaway:** the irony of the original takeaway ("check the evidence
  concretely... rather than assuming") stands — the mistake here was an
  insufficiently thorough grep, not a lack of trying to verify. Worth
  re-verifying a "the evidence shows X" claim with a *broader* search
  (all of `app/*.php`, not an assumed subset) before it hardens into
  permanent documentation.

## "install repofolio" -> "package repofolio into pressroot as a theme addon"
- Repofolio (the standalone GitHub-portfolio plugin this theme's old GitHub
  subsystem was extracted into) came back INTO the theme as a Theme Addon:
  classes under `app/Repofolio/includes/` (namespace, option keys, and block
  name unchanged), booted by `app/repofolio-addon.php` behind
  `prt_addon_enabled('repofolio')` with a `REPOFOLIO_THEME_MODE` constant for
  the three theme-mode differences (settings tab, asset URLs, no plugin-only
  wiring). Standalone plugin still wins if activated.
- Restored the two dangling references the extraction left behind:
  `prt_github_get()` and the `App\Github` facade (Support tab, repo seeder)
  now sit on top of `Repofolio\GitHub_Client`, degrading to empty results
  when the addon is off.
- **Takeaway:** when a subsystem moves out, grep ALL of app/ for stragglers
  the same day — two files kept calling functions that no longer existed.

## "update theme colors/design to match repofolio branding"
- Full token swap to the Repofolio palette (docs/BRAND.md in the repofolio
  repo): Iris `#6C4CF1`, Ink `#17151F`, Paper `#FFF9F5`, Pink/Coral/Amber/
  Lime/Cyan spectrum. Token SLUGS kept (`--color-green` is still the brand
  slug) so ~35 files updated by value only.
- Added the two brand gradients as tokens + theme.json presets, the 8px
  spectrum "language bar" on every page, spectrum-topped cards site-wide,
  and gradient pill buttons across core block buttons, `.btn`, and every
  inline pattern/view button. Appearance -> Pressroot got the docs-site
  chrome (dark radial hero, gradient headline, pill nav) via a static
  `resources/css/admin-settings.css` — no build step.
- **Takeaway:** the earlier "colors only" pass looked unfinished because
  patterns/views carry inline styles — a rebrand here isn't done until the
  pattern PHP and blade partials are swept too.

## "regenerate whole new themes per category" (Site Types -> design generator)
- Apply now REFRESHES existing pages (overwrites content + meta) instead of
  skipping them — old baked-in designs can't linger. Every apply/refresh
  deals a random pattern variant per page and re-deals a random STYLE KIT
  from a per-type pool (6 new Repofolio-family kits joined the original 6),
  then clears stale critical CSS so the new design paints immediately.
- `app/site-type-remix.php` generates variants C + D for every page of all
  eight site types (~50 patterns) from seeded hero/feature/CTA section
  pools; a filter appends the `pattern_c`/`pattern_d` slugs so no site-type
  file needed editing. Three new categories shipped hand-built A/B patterns:
  Affiliate Marketing, Restaurant/Café, Real Estate.
- New Brand tab (questionnaire: name, one-liner, color, light/dark, vibe)
  steers the generator — kit pools filter by mode/vibe, brand color
  overrides each dealt kit's accent, and the AI hero-copy prompt reads the
  profile + active site type and demands a fresh angle per run.
- `npm run refresh` = ESLint + Pint + Vite build in one shot.
- **Takeaway:** "regenerate" reads as *random and whole-theme* to users —
  variant toggling plus a fixed kit felt broken even though it worked.

## "generic hero + tagline + screenshot" (base-theme marketing pass)
- Homepage hero fully de-personalized: every string is a theme_mod with a
  neutral default; tagline "Your brand in. Your site out." (sibling of
  Repofolio's "Repos in. Portfolio out.") wired into the hero, style.css
  description, and a regenerated screenshot.png (drawn with the theme's own
  woff2 fonts converted to ttf).
- **Takeaway:** the two-tone headline needed to be editable in PIECES
  (opening/gradient/serif/suffix) — a single text field can't express it.

## "powered by AI or not" + easy mode
- prt_ai_features_enabled() = addon toggle AND a plain-language Brand-tab
  switch. It gates ONLY genuine AI calls (copy gen, connectors, editor AI
  button, AI image tab) — the design generator is local and stays on.
- Brand tab became the non-technical control panel: 3-step quick start,
  site title/tagline sync checkbox, and later the full questionnaire.
- **Takeaway:** "turn off AI" must not take the design tooling with it;
  separating module toggle from network-call toggle solved it.

## "review the settings page live + trends + AI page builder"
- Preview iframes were rendering WITHOUT the design system: the standalone
  preview page never fired prt_head_end, so Customizer palette vars never
  landed. Fixed + added ?prt_preview_kit= so each site type's card previews
  all four variants in its OWN kit, with nocache headers + versioned URLs.
- Design trends: six CSS-only body-class layers (bento/glass/brutalist/
  editorial/minimal/retro_pop) dealt alongside kits, brand-filtered.
- AI Builder: per-page/per-type "Write with AI" — extracts text segments,
  asks the selected connector for a same-count JSON array, swaps text only
  (markup never AI-touched, honoring the v1.4 reliability decision); AI
  brand image via Pollinations sideloaded to the Media Library.
- Core Marketing kit reserved for the Marketing type only; branding
  re-asserted (prt_refresh_branding) after every complete setup.
- **Takeaway:** when a preview looks "uncached but wrong", check which
  head hooks the standalone route actually fires before blaming caching.

## v1.6.0 — "one brief, whole sites" (core instructions + site chrome)
- **Core AI instructions:** every Theme Settings + Brand answer compiles into
  one saved CORE SITE BRIEF (option `prt_core_ai_instructions`) prepended to
  EVERY AI call; a viewer on the settings page shows exactly what the model
  receives. AI instructions field became a WYSIWYG (1,000-word cap, live
  counter, server-side trim) + uploadable `.md` instruction files stored in
  one option and appended to the brief (per-doc word trim keeps prompts sized).
- **Site chrome builder** (`prt_build_site_chrome()`): generated "Pressroot
  Menu" (Home + starter pages, only ever manages its OWN menu), goal-driven
  header CTA (leads/sell/book/read → Get a quote/Shop now/Book now/Subscribe,
  URL prefers reservations → contact → top-picks), footer tagline + light/dark
  ground from brand answers. Runs on apply, 🎲 refresh-all, and auto-build.
  Live-verified: brewery test site's "Hire Me" became "Shop now" (goal=sell).
- **Design generated in the backend:** kit/colors/fonts/corners/hero controls
  hidden behind a collapsed "Advanced fine-tuning" panel (absent entirely
  before first build); save handler guards hidden checkboxes so an absent
  field can't silently reset a mod (prt_avail_open bug caught pre-ship).
- **Takeaway:** when form sections become conditional, audit every
  `!empty($_POST[...])` checkbox write — absent fields read as "off".

## Docs website + beta program (marketable project pass)
- Replaced the stock Jekyll/Cayman docs page with a 5-page static site in the
  theme's own design language (docs/index+documentation+testers+collaborate+
  feedback .html + shared assets/pressroot.css). Kept Jekyll rendering for
  the existing .md pages (THEME-SETTINGS, recipes) — plain .html passes
  through untouched, so both coexist in docs/ on Pages.
- Tester funnel: friends & family concierge path (free domain, keep the
  site), feedback form → FormSubmit AJAX (emails directly, no backend on
  GitHub Pages) + prefilled GitHub issue for public tracking, branded
  1200×630 og:image on every page, Beta badge in the nav.
- Attribution: style.css Author URI → github.com/matthummel-pa + MIT
  copyright block; footer credit "Pressroot theme by matthummel"
  (respects prt_footer_credit toggle); settings-hero byline.
- **Takeaway:** GitHub Pages + FormSubmit + issue-URL prefill = a full
  feedback pipeline with zero servers; remember FormSubmit's one-time
  activation email before announcing.

## "Could not insert post into the database" (Playground corruption)
- Symptom: every wp_posts INSERT failed while reads worked fine — even
  wp-admin's Add New Page died on its auto-draft insert.
- Root cause (via a temp `$wpdb->insert` probe printing `last_error`):
  `SQLSTATE[HY000]: General error 11 — database disk image is malformed`.
  The Playground's SQLite file (.playground/database/.ht.sqlite) was
  corrupted, likely by a worker file-lock deadlock mid-write (the CLI warns
  below 6 workers).
- Fix: python sqlite3 iterdump → rebuild (all 39 posts + 283 options kept,
  integrity_check ok), swap with server stopped, restart. Corrupt original
  kept as .ht.sqlite.corrupt-backup.
- **Takeaway:** "Could not insert post" with working reads = check the DB
  file itself before the code; SQLite `PRAGMA integrity_check` answers in
  one line, and iterdump-rebuild recovers without data loss.

---

## Pressroots Reserve — bookings & reservations addon (Claude Cowork)
- Built a full appointments/reservations subsystem as a Repofolio-style Theme
  Addon: Services CPT, availability engine, front-end booking widget (block +
  shortcode), `.ics` + tokenized-cancel emails, and a multi-view admin calendar.
  Restarted mid-build — four classes (Settings/Services/Engine/Emails) already
  existed and were complete; the missing pieces were class-rest/class-block/
  class-admin/class-plugin, the JS/CSS assets, the theme wiring, and the wizard
  opt-in. Full reference: docs/PRESSROOTS-RESERVE.md.
- Naming kept deliberately split: user-facing brand **Pressroots Reserve** vs.
  stable internal `PrtBookings` namespace + `prt_service`/`prt_booking` CPTs +
  `prt_bookings_options` option — renaming the brand never churns code.

## Bookings addon booted but never appeared
- **Cause:** `app/bookings-addon.php` checks `prt_addon_enabled('bookings')` at
  include time, at the top of the file — but the
  `add_filter('pressroot/addon_defaults', …)` that would register the `bookings`
  default sat *lower* in the same file, so it hadn't run yet.
  `prt_addon_enabled()` treats unknown slugs as disabled, so the addon skipped
  its own boot on every request.
- **Fix:** declare the `bookings` default directly in `prt_addon_defaults()`
  (`app/theme-addons.php`) alongside `pressroot_ai`/`repofolio`, and drop the
  late filter. Register `'bookings-addon'` in the `functions.php` `collect()`.
- **Takeaway:** a boot-time capability check can't depend on a filter registered
  later in the same file — put addon defaults in the shared registry, not in the
  addon's own late `add_filter`.

## No double-booking without a real lock
- **Cause:** vanilla WP post storage has no row lock, so two visitors can pass
  the "seats left" check for the same slot at once and oversell it.
- **Fix:** optimistic guard — `Engine::slots()` subtracts booked seats from
  capacity, `create()` re-validates the posted timestamp against the freshly
  computed slot list, and after insert it re-counts seats; if the slot is now
  oversold, the highest-ID booking withdraws itself. The race window shrinks
  from "however long the visitor fills the form" to milliseconds around insert.
- **Takeaway:** with no DB lock, validate → insert → recheck (loser self-removes)
  is the pragmatic no-double-book strategy the big booking apps use.

## Cancel links a mail scanner can't trigger
- **Cause:** a plain GET cancel URL in a confirmation email gets fetched by
  link-preview bots and corporate mail scanners — which would silently cancel
  the booking.
- **Fix:** the tokenized cancel link (`?prt_bk=cancel&bk_token=…`, handled on
  `template_redirect`) only *renders a confirm screen* on GET; the actual
  cancellation happens on the nonce-guarded POST that screen submits.
- **Takeaway:** never let a GET perform a state change reachable from email —
  gate it behind a POST + nonce confirm.

## Multi-view admin calendar, no library, no build step
- Bookings → Calendar is a dependency-free vanilla-JS calendar
  (Month/Week/Day/List) over an `edit_theme_options`-gated REST feed, matching
  the theme's "hand-written admin JS, no build" convention. Times are read as
  the site's wall-clock from the feed's ISO strings, so the view matches the
  site timezone regardless of the browser's zone; the Week/Day time-grid lays
  overlapping bookings into lanes.

## Release & GitHub-over-browser gotchas (Cowork session)
- **v1.7.0 tagged + published** off `main`. The sandbox GitHub token is scoped
  to already-configured repos: reads (clone / ls-remote) work everywhere, but
  `POST /user/repos` and pushes to a *new* repo return 403 /
  "Invalid username or token". Creating the standalone `pressroots-reserve` repo
  and publishing the release were therefore done through the browser.
- **GitHub web editor, for next time:** the file editor is a CodeMirror6
  contenteditable `<div>` (not a `<textarea>`, so a form-set can't populate it);
  typing a large markdown blob triggers auto-continuation of `>` blockquote and
  `-` list markers, corrupting a full paste — do *surgical* edits (single-word
  swaps, table rows that start with `|`) instead. A stray `/` when focus isn't
  in the editor opens the global search. The release-notes field, by contrast,
  is a real `<textarea>` and accepts a full paste cleanly.
- **Takeaway:** for GitHub-over-browser, prefer real `<textarea>` fields and
  surgical edits; never paste large list/quote-heavy markdown into the
  CodeMirror file editor.

---

## Recurring bug patterns
- **Dead selector rot** — renamed markup breaks every CSS file still
  targeting the old class names, silently.
- **Hook-ordering hazards** — `get_header` (head) vs. `wp_body_open` (body);
  never share one guard between them.
- **Duplicate sources of truth** — two UIs writing one option confuses users.
- **Save ≠ working** — a setting can persist fine and still do nothing if the
  template/CSS never reads it. Trace to the render, not just `get_theme_mod()`.
- **Non-standard local envs** — check project docs before generic tooling.

## Takeaways for next time
- Check CSS selector drift first when "settings don't do anything."
- Build `active_callback`-style conditional visibility in from day one.
- Decide `postMessage` vs. `refresh` per setting, deliberately.
- Document non-standard local dev setups prominently.
- Do a full audit pass periodically, not just targeted fixes.

---

*Keep adding to this in the same short, per-step format.*
