# Architecture

How the theme is put together.

## Foundation

[Sage 11](https://roots.io/sage/) on top of **Acorn** (a Laravel application container inside WordPress). Markup is **Blade**; styles/scripts are bundled by **Vite**; CSS is **Tailwind CSS v4**.

- `functions.php` boots Acorn and registers theme files via `collect(['setup', 'filters', 'customizer', 'contact'])`.
- `@vite([...])` in `resources/views/layouts/app.blade.php` injects the built CSS/JS.

## Design tokens

`resources/css/app.css` defines the design system as Tailwind v4 `@theme` variables
(`--color-*`, `--font-*`, radius) plus `@layer components` for buttons, cards, the post
list, reading prose, the contact form, etc. Components reference `var(--color-â€¦)`, so the
whole site re-skins from these tokens.

## Customizer (Theme Options)

`app/customizer.php` registers a **Theme Options** panel (colors, fonts, layout width,
header button, footer). Chosen values are emitted as a `:root { â€¦ }` override in a
`<style id="prt-customizer">` block via the `prt_head_end` action â€” which fires **after**
`@vite` in the layout, so the overrides win without a rebuild. Non-default fonts are
enqueued from Google Fonts on demand. Header/footer values flow through existing
`pressroot/*` filters.

## Live GitHub engine

`app/Github.php` calls the GitHub REST API server-side (`wp_remote_get`, User-Agent set)
for repo metadata, stars/forks, latest release, and the rendered README intro; results are
cached in a 6-hour transient. `app/filters.php` exposes it as `[prt_github owner="â€¦" repo="â€¦" show="desc,stats,intro"]`, used on single project pages.

## Contact form

`app/contact.php` handles submissions on the `init` hook: verifies a nonce, checks a
honeypot, sanitizes + validates fields, sends via `wp_mail`, and redirects back with a
`?contact=success|error` flag the template reads. No third-party form plugin.

## Bookings (Pressroots Reserve addon)

`app/bookings-addon.php` boots the **Pressroots Reserve** subsystem (`app/Bookings/`)
when the `bookings` addon is enabled — the same Repofolio-style pattern
(`prt_addon_enabled('bookings')`, classes under `Bookings/includes/` loaded with
`require_once`, a `PrtBookings\Plugin` booted on `after_setup_theme`, settings
registered through the `pressroot/settings_tabs` filter). It adds two post types
(`prt_service`, `prt_booking`), a timezone-aware slot engine that stores UTC
timestamps and never double-books, a `prt/booking` block + `[prt_booking]`
shortcode, `.ics` + tokenized-cancel emails, REST routes under `prt-bookings/v1`,
and a **Month/Week/Day/List** admin calendar. Because the addon's boot check runs
at include time, the `bookings` default is declared in `prt_addon_defaults()`
(`app/theme-addons.php`), not via a late `pressroot/addon_defaults` filter. The
same `includes/` + assets ship standalone as the
[pressroots-reserve](https://github.com/matthummel-pa/pressroots-reserve) plugin,
which defines `PRT_BOOKINGS_VERSION` at load so the bundled addon steps aside.
Full detail in docs/PRESSROOTS-RESERVE.md.

## Template hierarchy

Standard WordPress hierarchy, in Blade:

| Request | Template |
|---|---|
| Front page | `front-page.blade.php` |
| Posts page / archives | `index.blade.php` / `archive.blade.php` |
| Single post | `single.blade.php` â†’ `partials/content-single` |
| Page | `page.blade.php` â†’ `partials/content-page` |
| Page using "Contact" template | `template-contact.blade.php` |
| Projects archive / single | `archive-projects` / `single-projects` |
| Search / 404 | `search.blade.php` / `404.blade.php` |

Shared chrome lives in `sections/header.blade.php`, `sections/footer.blade.php`, and `layouts/app.blade.php`.

## Extending this theme (for other developers)

The theme is designed to be extended from a child theme or a small mu-plugin
without editing anything under `app/` directly. Two mechanisms:

**1. Filters** - change data before the theme uses it (fonts list, style kits,
social platforms, default colors, width presets, font weight choices, and
more). Full list with file + description:

```
wp pressroot hooks --type=filters
```

**2. Actions** - run your own code at a specific point in the page (before/after
header, before/after main content, before/after footer, before/after each
post-card in listings):

```
wp pressroot hooks --type=actions
```

Both commands read from `app/hooks-registry.php` (`prt_hook_registry()`) -
the single source of truth for every custom hook this theme fires. Add a new
`apply_filters()`/`do_action()` call anywhere in `app/`? Add a row there too,
so it stays discoverable instead of requiring a grep.

## Settings pages (2026-07 cleanup)

There used to be a full tabbed "Theme Settings" admin page (`app/admin-settings.php`,
`pressroot/admin_schema` filter) mirroring the Customizer's General, Design,
Layout, Header, Footer, and Social Links controls via a separate `set_theme_mod()`
form. It was pure duplication — same theme mods, second UI — so it's gone. The
Customizer (Appearance -> Customize -> Theme Options) is now the only place to
edit those settings.

The one exception was the GitHub/Projects tab (default owner, API token, cache
hours, OAuth Client ID, the "Connect with GitHub" device-flow widget), which had
no Customizer equivalent. That, plus Style Kits/export-import-reset and
Pressroot AI, now live together as tabs on one consolidated admin page —
Appearance -> Pressroot (`app/pressroot-settings.php`) — rather than four
separate Appearance submenu pages. See "Appearance -> Pressroot" below for
the full breakdown. This also folded in the retirement of the old Starter
Sites importer — superseded by Pressroot AI's site-type picker (more
personas, regenerate, live previews, dedicated patterns per type), not
because its own patterns were missing (an earlier version of this note
claimed that; it was wrong — all of Starter Sites' referenced patterns are
still registered, mostly in app/sections-library.php). A placeholder
"Starter" tab briefly explained the retirement, then was removed once
Pressroot AI was reachable directly from its own tab. The standalone Style
Kits tab was retired the same way, once its per-tab "Apply" swatch grid
became redundant with each Site Type already applying its own kit — see
"Appearance -> Pressroot" below.

Appearance -> Local Fonts remains its own small page
(`app/fonts-local.php`) — unrelated in purpose, left as-is.

### Appearance -> Pressroot (`app/pressroot-settings.php`)

One admin page, one branded header, a left-sidebar menu with the active
section's content on the right — replacing both the four separate
Appearance submenu pages this page originally consolidated, and the top
`nav-tab-wrapper` it used at first (`prt_settings_render()` now renders a
`<nav>` list on the left instead; every section still hangs off the same
`prt_settings_tab_url($tab, $extra)` links, so nothing downstream needed to
change for the layout swap). The tab registry (`prt_settings_tabs()`) is
filterable via `pressroot/settings_tabs`, so addons can register sections
without editing the file:

| Tab | Source | What it does |
|---|---|---|
| Setup | `app/setup-wizard.php` (`prt_setup_wizard_tab_html()`) — tab id `setup`, first tab + default landing tab until completed once | Six-step guided onboarding: business info → connections (AI keys, SEO plugin selector, GA4 Measurement ID) → WordPress settings automation → site generation → review → launch (publishes the generated drafts, sets the front page, re-opens search visibility). Resumable state in option `prt_wizard_progress`; full detail in docs/SETUP-WIZARD.md |
| Site Types | `app/ai-assistant.php` (`prt_pressroot_ai_tab_html()`) — tab id `ai`, unchanged internally | Site-type picker (which also applies that type's Style Kit), regenerate, hero-copy generator, plus two collapsed Advanced sections: "Connect more AI models" (AI Connectors) and "Backup & restore settings" (Export/Import/Reset, from `app/settings-io.php`'s `prt_settings_backup_fields_html()`) |
| GitHub | `app/github-settings.php` (`prt_github_tab_html()`) | Default owner, API token, cache hours, OAuth Client ID, Connect with GitHub |
| Support | `app/support-settings.php` (`prt_support_tab_html()`) | Live status (stats, languages, latest releases, open issues) for "this theme's repository", pulled through the existing `App\Github` class, plus a curated list of links to the theme's own documentation. Always visible — not gated by the Pressroot AI addon toggle, since getting help shouldn't depend on an unrelated feature flag. |
| Bookings | `app/bookings-addon.php` (`App\prt_bookings_tab_html()`) — visible only while the Pressroots Reserve addon is booted | Weekly availability, booking rules (minimum notice, booking window, slot interval), blackout dates, auto-confirm vs. pending, and the notification email. Services are edited under Bookings → Services; the visual overview is Bookings → Calendar. Full detail in docs/PRESSROOTS-RESERVE.md |

Site Types was previously two separate tabs: "Style Kits" (a manual swatch
grid to apply a palette/font/radius preset by itself) and "Pressroot AI" (the
site-type picker). The manual Style Kits grid was removed — every Site Type
already applies its matching kit automatically, so picking one by itself was
a redundant second way to do the same thing — and the "Pressroot AI" tab was
renamed **Site Types** to reflect that it's now the primary tab. The Style
Kits data + apply logic (`prt_style_kits()`, `prt_apply_style_kit()` in
`app/settings-io.php`) are unchanged and still power the site-type picker;
only the standalone manual-picker UI and its `admin_post_prt_apply_kit`
handler were removed. Export/Import/Reset weren't dropped, just relocated
into their own collapsed Advanced section on the Site Types tab.

Support's "this theme's repository" (owner + repo slug, `prt_support_repo()`
in `app/support-settings.php`) is a small setting of its own, separate from
the GitHub tab's "Default GitHub owner" (`prt_proj_owner`) — that owner is
just the fallback for individual Projects around the site (each can point at
its own repo), whereas Support's setting is specifically "which one repo IS
this theme," used only to drive its live status card and doc links. It
defaults to the GitHub tab's owner plus `pressroot`, editable inline
via its own "Edit repository" `<details>`, same pattern as the page header's
Docs/Support link editor. Doc links themselves are resolved against that
repo's `blob/main/...` URLs (filterable via `pressroot/support_doc_links`),
so a fork that repoints "this theme's repository" at its own copy gets
correct links without editing PHP.

Every tab's render function used to be a full `prt_..._render()` with its own
`<div class="wrap"><h1>`; those were extracted to `prt_..._tab_html()`
functions with no page chrome, so `prt_settings_render()` can call whichever
one is active. `prt_settings_tab_url($tab, $extra)` is the one place that
knows this page's slug (`prt-settings`) — every admin-post handler across
these files redirects through it rather than hardcoding a URL.

The Site Types tab is gated by the "Enable Pressroot AI" addon toggle
(Theme Options -> Theme Addons, `app/theme-addons.php`) — it simply isn't in
the tab list when that's off. `prt_settings_render()` defaults to this tab
(falling back to the first visible tab if it's hidden) since it's now the
primary one, replacing Style Kits as the previous default.

## Developer CLI (`wp pressroot ...`)

Registered in `app/cli.php`, only loaded when `WP_CLI` is true:

| Command | What it does |
|---|---|
| `wp pressroot settings export [--file=<path>]` | Dump every `prt_*` theme mod to JSON |
| `wp pressroot settings import <file>` | Apply a JSON export |
| `wp pressroot settings reset` | Remove all `prt_*` theme mods (confirms first) |
| `wp pressroot kit list` | List Style Kit presets |
| `wp pressroot kit apply <slug>` | Apply a Style Kit by slug |
| `wp pressroot views clear` | Delete compiled Acorn/Blade view files |
| `wp pressroot hooks [--type=filters\|actions]` | Print the hook registry as a table |

These share their underlying logic with the web UI (`prt_owned_mods()` /
`prt_style_kits()` in `app/settings-io.php`, `prt_clear_compiled_views()` in
`app/setup.php`) so the CLI and the Style Kits tab on Appearance -> Pressroot
can't drift apart.

## Dev Mode / Standard Mode

`app/dev-mode.php` stores one setting, `prt_dev_mode` — `auto` (default),
`on`, or `off` — editable either from Customizer -> Theme Options ->
Developer, or with a single click directly in the admin bar (visible to
`manage_options` users only): click "Standard Mode" to switch on Dev Mode,
click "Dev Mode" to switch back. The admin-bar toggle always sets an
explicit `on`/`off`; only the Customizer select can put it back to `auto`.

In `auto`, it's active whenever `wp_get_environment_type()` isn't
`production` (set `WP_ENVIRONMENT_TYPE` in `wp-config.php` for staging boxes
that don't already report a non-production type). `off` is useful for
seeing the site the way a real visitor would while working locally.

When active, the admin-bar node expands into: environment, resolved
template file, DB query count so far, peak memory, elapsed load time, and
quick links to Appearance -> Pressroot (Style Kits tab) and clearing
compiled views.

## Header/Nav CSS selectors (2026-07 fix)

`app/header-elements.php`, `app/header-layout.php`, `app/header-behaviors.php`,
`app/dark-mode.php`, and the global container-width rule in `app/customizer.php`
previously emitted CSS scoped to `.banner`, `.nav-primary`, `.header-cta`, and
bare `.social` — leftover selectors from an older header markup that no longer
exists. The real header (`resources/views/sections/header.blade.php`) uses
`.site-header` / `.site-header-inner` / `.header-nav` / `.header-actions` /
`.header-social` / `.btn-hire`. All of the above files now target the real
classes, so the Navigation, Header Layout, Header Behaviors, and Dark Mode
Customizer sections actually affect the page again.

The Top Bar section (`app/theme-options.php`) had the same problem plus a
missing piece: `prt_topbar()` assembled the settings but nothing ever echoed
the `.top-bar` markup. `prt_topbar_render()` now does, hooked to
`prt_before_header` at priority 8 (the announcement bar moved to the same
action, priority 5, so both bars are flex children of `#app` — required for
the "Stack order" reorder setting to have any effect).

## Customizer audit (2026-07)

A full sweep of every Customizer section for the same "CSS targets markup
that doesn't exist" bug found in the header/top-bar work above. Fixed:

- **Navigation flexbox** (`app/nav-options.php`) and **Typography (advanced)**
  nav font/weight/case (`app/typography.php`) both targeted `.nav-primary`
  — corrected to `.header-nav-list`.
- **Menu icon on desktop/tablet/mobile** (`app/menu.php`) — the checkboxes
  drove body classes matched against `.nav-primary`/`.banner > .social` in a
  static stylesheet, so they never worked. Replaced with a 3-state (auto/on/off)
  select per breakpoint and a real `prt_head_end` emitter targeting
  `.header-nav`/`.header-social`/`.btn-hire`/`.menu-toggle`. "Auto" (the
  default) is a no-op — the theme's existing 768px split keeps working
  untouched; only an explicit on/off per tier changes anything.
- **Custom Code "Body code"** (`app/integrations.php`) was misrouted into
  `<head>` by the same `get_header`-fires-before-`wp_body_open` hazard
  documented in `app/announcement.php` — fixed to use `wp_body_open` only.
- **Contact intro** (`prt_contact_intro`) was registered but never read;
  wired into `template-contact.blade.php` (default matches the old hardcoded
  copy exactly).
- **Social platform list** was registered twice (`app/social-links.php` +
  `app/menu.php`) with only the latter having real controls. Consolidated:
  `prt_socials_map()` now sources from `prt_social_platforms()`, one list.
- **Hero section** (`app/hero.php`) styles the reusable "Hero" block patterns
  (Inserter -> Patterns -> MH · Heroes) — legitimate, not dead. Its copy
  fields (`prt_hero_eyebrow`/`title`/`subtext`) had no consumer anywhere
  though, since the homepage's own hero (`resources/views/partials/home/hero.blade.php`)
  is a separate hand-built section. Dropped the unused eyebrow field (the
  homepage's badge already has its own working field, `prt_avail_text`), and
  wired title/subtext into the homepage hero with defaults matching its exact
  existing copy.
- Added `active_callback` (previously used nowhere in the theme) so
  parent/child fields hide correctly: footer custom-color pickers, footer
  tagline/social (needs brand column on), footer nav heading (needs nav
  column on), popout gradient end/angle (needs gradient background), cookie
  notice text fields (needs notice enabled), dark mode default (needs dark
  mode enabled).

## Code style

`pint.json` configures [Laravel Pint](https://laravel.com/docs/pint) (already
a dev dependency) for PHP: `composer format` to fix, `composer lint` to check
without writing. `eslint.config.js` covers the hand-written JS in
`resources/js/*.js` (the block-editor files that intentionally have no build
step): `npm install` then `npm run lint:js`.
